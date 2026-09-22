<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\CustomerLogic;
use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\ProtectedTransaction;
use App\Models\TootliDispute;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProtectedTransactionController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $transactions = ProtectedTransaction::with(['buyer', 'seller', 'dispute'])
            ->where(function ($query) use ($user) {
                $query->where('buyer_id', $user->id)
                    ->orWhere('seller_id', $user->id)
                    ->orWhere('target_email_or_phone', $user->email)
                    ->orWhere('target_email_or_phone', $user->phone);
            })
            ->orderBy('id', 'desc')
            ->get();

        return response()->json($transactions, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'item_name' => 'required|string',
            'amount' => 'required|numeric|gt:0',
            'target_email_or_phone' => 'required|string',
            'creator_role' => 'required|in:buyer,seller',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $user = $request->user();
        $amount = $request->amount;
        $protectionFee = round($amount * 0.05, 2); // 5% platform fee
        $totalAmount = $amount + $protectionFee;

        // Try to match target user
        $targetUser = User::where('email', $request->target_email_or_phone)
            ->orWhere('phone', $request->target_email_or_phone)
            ->first();

        $buyerId = null;
        $sellerId = null;

        if ($request->creator_role === 'buyer') {
            $buyerId = $user->id;
            if ($targetUser) {
                $sellerId = $targetUser->id;
            }
        } else {
            $sellerId = $user->id;
            if ($targetUser) {
                $buyerId = $targetUser->id;
            }
        }

        $transaction = ProtectedTransaction::create([
            'buyer_id' => $buyerId,
            'seller_id' => $sellerId,
            'target_email_or_phone' => $request->target_email_or_phone,
            'item_name' => $request->item_name,
            'amount' => $amount,
            'protection_fee' => $protectionFee,
            'total_amount' => $totalAmount,
            'creator_role' => $request->creator_role,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Transacción creada exitosamente',
            'transaction' => $transaction->load(['buyer', 'seller', 'dispute'])
        ], 201);
    }

    public function pay(Request $request, $id)
    {
        $user = $request->user();
        $transaction = ProtectedTransaction::findOrFail($id);

        // Check if user is the buyer
        if ($transaction->buyer_id !== $user->id && $transaction->target_email_or_phone !== $user->email && $transaction->target_email_or_phone !== $user->phone) {
            return response()->json(['message' => 'No tienes permiso para pagar esta transacción'], 403);
        }

        if ($transaction->status !== 'pending') {
            return response()->json(['message' => 'Esta transacción ya no se encuentra pendiente de pago'], 400);
        }

        $paymentMethod = $request->payment_method ?? 'wallet';

        if ($paymentMethod === 'wallet') {
            if ($user->wallet_balance < $transaction->total_amount) {
                return response()->json(['message' => 'Saldo insuficiente en tu wallet'], 400);
            }

            DB::transaction(function () use ($user, $transaction) {
                // Deduct from buyer's wallet
                $user->wallet_balance -= $transaction->total_amount;
                $user->save();

                // If buyer was not set previously, bind them now
                if (!$transaction->buyer_id) {
                    $transaction->buyer_id = $user->id;
                }

                $transaction->status = 'paid';
                $transaction->payment_method = 'wallet';
                $transaction->save();
            });

            return response()->json([
                'message' => 'Pago realizado con éxito, fondos retenidos en garantía',
                'transaction' => $transaction->load(['buyer', 'seller', 'dispute'])
            ], 200);
        }

        // Simulating card payment
        DB::transaction(function () use ($user, $transaction, $paymentMethod) {
            if (!$transaction->buyer_id) {
                $transaction->buyer_id = $user->id;
            }
            $transaction->status = 'paid';
            $transaction->payment_method = $paymentMethod;
            $transaction->save();
        });

        return response()->json([
            'message' => 'Pago procesado exitosamente con tarjeta, fondos retenidos en garantía',
            'transaction' => $transaction->load(['buyer', 'seller', 'dispute'])
        ], 200);
    }

    public function complete(Request $request, $id)
    {
        $user = $request->user();
        $transaction = ProtectedTransaction::findOrFail($id);

        // Only buyer can complete and release funds
        if ($transaction->buyer_id !== $user->id) {
            return response()->json(['message' => 'Solo el comprador puede liberar los fondos'], 403);
        }

        if ($transaction->status === 'disputed') {
            return response()->json(['message' => 'No se pueden liberar fondos mientras la transacción esté en disputa'], 400);
        }

        if ($transaction->status !== 'paid') {
            return response()->json(['message' => 'La transacción debe estar en estado pagado para ser completada'], 400);
        }

        DB::transaction(function () use ($transaction) {
            $transaction->status = 'completed';
            $transaction->save();

            // Transfer amount (monto neto a recibir) to seller
            if ($transaction->seller_id) {
                $seller = User::find($transaction->seller_id);
                if ($seller) {
                    $seller->wallet_balance += $transaction->amount;
                    $seller->save();

                    // Register wallet transaction log
                    CustomerLogic::create_wallet_transaction(
                        $seller->id,
                        $transaction->amount,
                        'add_fund_by_admin',
                        'payout_protected_tx_' . $transaction->id
                    );
                }
            }
        });

        return response()->json([
            'message' => 'Fondos liberados exitosamente al vendedor',
            'transaction' => $transaction->load(['buyer', 'seller', 'dispute'])
        ], 200);
    }

    public function cancel(Request $request, $id)
    {
        $user = $request->user();
        $transaction = ProtectedTransaction::findOrFail($id);

        if ($transaction->status !== 'pending') {
            return response()->json(['message' => 'Solo se pueden cancelar transacciones pendientes'], 400);
        }

        $transaction->status = 'cancelled';
        $transaction->save();

        return response()->json([
            'message' => 'Transacción cancelada',
            'transaction' => $transaction->load(['buyer', 'seller', 'dispute'])
        ], 200);
    }

    /**
     * Abrir una disputa para una transacción protegida en custodia (Tootli Protector).
     */
    public function dispute(Request $request, $id)
    {
        $user = $request->user();
        $transaction = ProtectedTransaction::with('dispute')->findOrFail($id);

        // Solo participantes de la transacción pueden abrir disputa (típicamente el comprador)
        $isParticipant = ($transaction->buyer_id === $user->id || $transaction->seller_id === $user->id ||
            $transaction->target_email_or_phone === $user->email || $transaction->target_email_or_phone === $user->phone);

        if (!$isParticipant) {
            return response()->json(['message' => 'No tienes permiso para disputar esta transacción'], 403);
        }

        if ($transaction->status !== 'paid') {
            return response()->json(['message' => 'Solo se pueden disputar transacciones con fondos en garantía (pagadas)'], 400);
        }

        if ($transaction->dispute && in_array($transaction->dispute->status, ['open', 'under_review'])) {
            return response()->json(['message' => 'Ya existe una disputa activa para esta transacción'], 400);
        }

        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:100',
            'description' => 'required|string|min:10|max:1000',
            'requested_solution' => 'nullable|in:full_refund,partial_refund,service_correction',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Subida de evidencias fotográficas
        $evidencePhotos = [];
        if ($request->has('images')) {
            $images = $request->file('images');
            if (is_array($images)) {
                foreach ($images as $img) {
                    $evidencePhotos[] = Helpers::upload('disputes/', 'png', $img);
                }
            } elseif ($request->file('images')) {
                $evidencePhotos[] = Helpers::upload('disputes/', 'png', $request->file('images'));
            }
        }

        // Determinar contraparte (defendant)
        $defendantId = ($user->id === $transaction->buyer_id) ? $transaction->seller_id : $transaction->buyer_id;

        DB::transaction(function () use ($transaction, $user, $defendantId, $request, $evidencePhotos) {
            $dispute = TootliDispute::create([
                'disputable_type' => ProtectedTransaction::class,
                'disputable_id' => $transaction->id,
                'claimant_id' => $user->id,
                'defendant_id' => $defendantId,
                'reason' => $request->reason,
                'description' => $request->description,
                'evidence_photos' => $evidencePhotos,
                'requested_solution' => $request->requested_solution ?? 'full_refund',
                'status' => 'open',
            ]);

            $transaction->status = 'disputed';
            $transaction->save();
        });

        return response()->json([
            'message' => 'Disputa abierta exitosamente bajo la garantía Tootli Protector. Los fondos permanecen congelados hasta la resolución.',
            'transaction' => $transaction->fresh()->load(['buyer', 'seller', 'dispute'])
        ], 201);
    }

    /**
     * Obtener el detalle de la disputa de una transacción.
     */
    public function getDispute(Request $request, $id)
    {
        $transaction = ProtectedTransaction::findOrFail($id);
        $dispute = TootliDispute::where('disputable_type', ProtectedTransaction::class)
            ->where('disputable_id', $transaction->id)
            ->with(['claimant', 'defendant'])
            ->latest()
            ->first();

        if (!$dispute) {
            return response()->json(['message' => 'No se encontró disputa para esta transacción'], 404);
        }

        return response()->json($dispute, 200);
    }

    /**
     * Cancelar voluntariamente una disputa (el demandante llegó a un acuerdo directo).
     */
    public function cancelDispute(Request $request, $id)
    {
        $user = $request->user();
        $transaction = ProtectedTransaction::findOrFail($id);
        $dispute = TootliDispute::where('disputable_type', ProtectedTransaction::class)
            ->where('disputable_id', $transaction->id)
            ->where('status', 'open')
            ->latest()
            ->first();

        if (!$dispute) {
            return response()->json(['message' => 'No hay disputa abierta para cancelar'], 404);
        }

        if ($dispute->claimant_id !== $user->id) {
            return response()->json(['message' => 'Solo la persona que inició la disputa puede cancelarla'], 403);
        }

        DB::transaction(function () use ($transaction, $dispute) {
            $dispute->status = 'cancelled';
            $dispute->save();

            // Los fondos regresan al estado seguro en custodia para que puedan ser liberados
            $transaction->status = 'paid';
            $transaction->save();
        });

        return response()->json([
            'message' => 'Disputa cancelada exitosamente. La transacción vuelve a estar en custodia.',
            'transaction' => $transaction->fresh()->load(['buyer', 'seller', 'dispute'])
        ], 200);
    }

    /**
     * Resolver disputa (reembolso comprador, pago a vendedor, o acuerdo parcial).
     */
    public function resolveDispute(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'resolution' => 'required|in:refund_buyer,payout_seller,partial_refund',
            'refund_amount' => 'required_if:resolution,partial_refund|numeric|min:0',
            'resolution_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $transaction = ProtectedTransaction::findOrFail($id);
        $dispute = TootliDispute::where('disputable_type', ProtectedTransaction::class)
            ->where('disputable_id', $transaction->id)
            ->whereIn('status', ['open', 'under_review'])
            ->latest()
            ->first();

        if (!$dispute) {
            return response()->json(['message' => 'No hay disputa abierta para resolver'], 404);
        }

        $resolution = $request->resolution;
        $notes = $request->resolution_notes ?? 'Resolución emitida por Tootli Protector';

        DB::transaction(function () use ($transaction, $dispute, $resolution, $notes, $request) {
            $dispute->resolution_notes = $notes;
            $dispute->resolved_at = now();

            if ($resolution === 'refund_buyer') {
                // Reembolsar monto total al comprador
                $dispute->status = 'resolved_buyer_refund';
                $dispute->refund_amount = $transaction->total_amount;
                $dispute->save();

                if ($transaction->buyer_id) {
                    $buyer = User::find($transaction->buyer_id);
                    if ($buyer) {
                        $buyer->wallet_balance += $transaction->total_amount;
                        $buyer->save();

                        CustomerLogic::create_wallet_transaction(
                            $buyer->id,
                            $transaction->total_amount,
                            'order_refund',
                            'dispute_refund_tx_' . $transaction->id
                        );
                    }
                }

                $transaction->status = 'cancelled';
                $transaction->save();
            } elseif ($resolution === 'payout_seller') {
                // Liberar fondos al vendedor
                $dispute->status = 'resolved_seller_payout';
                $dispute->save();

                if ($transaction->seller_id) {
                    $seller = User::find($transaction->seller_id);
                    if ($seller) {
                        $seller->wallet_balance += $transaction->amount;
                        $seller->save();

                        CustomerLogic::create_wallet_transaction(
                            $seller->id,
                            $transaction->amount,
                            'add_fund_by_admin',
                            'dispute_payout_tx_' . $transaction->id
                        );
                    }
                }

                $transaction->status = 'completed';
                $transaction->save();
            } elseif ($resolution === 'partial_refund') {
                // Reembolso parcial
                $refundAmt = min($request->refund_amount, $transaction->amount);
                $sellerAmt = $transaction->amount - $refundAmt;

                $dispute->status = 'resolved_partial';
                $dispute->refund_amount = $refundAmt;
                $dispute->save();

                if ($transaction->buyer_id && $refundAmt > 0) {
                    $buyer = User::find($transaction->buyer_id);
                    if ($buyer) {
                        $buyer->wallet_balance += $refundAmt;
                        $buyer->save();

                        CustomerLogic::create_wallet_transaction(
                            $buyer->id,
                            $refundAmt,
                            'order_refund',
                            'dispute_partial_refund_tx_' . $transaction->id
                        );
                    }
                }

                if ($transaction->seller_id && $sellerAmt > 0) {
                    $seller = User::find($transaction->seller_id);
                    if ($seller) {
                        $seller->wallet_balance += $sellerAmt;
                        $seller->save();

                        CustomerLogic::create_wallet_transaction(
                            $seller->id,
                            $sellerAmt,
                            'add_fund_by_admin',
                            'dispute_partial_payout_tx_' . $transaction->id
                        );
                    }
                }

                $transaction->status = 'completed';
                $transaction->save();
            }
        });

        return response()->json([
            'message' => 'Disputa resuelta exitosamente',
            'dispute' => $dispute->fresh(),
            'transaction' => $transaction->fresh()->load(['buyer', 'seller', 'dispute'])
        ], 200);
    }
}
