<?php

namespace App\Http/Controllers/Api/V1;

use App\Http/Controllers/Controller;
use App\Models\ProtectedTransaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProtectedTransactionController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $transactions = ProtectedTransaction::with(['buyer', 'seller'])
            ->where('buyer_id', $user->id)
            ->orWhere('seller_id', $user->id)
            ->orWhere('target_email_or_phone', $user->email)
            ->orWhere('target_email_or_phone', $user->phone)
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
            'transaction' => $transaction
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

            return response()->json(['message' => 'Pago realizado con éxito, fondos retenidos en garantía', 'transaction' => $transaction], 200);
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

        return response()->json(['message' => 'Pago procesado exitosamente con tarjeta, fondos retenidos en garantía', 'transaction' => $transaction], 200);
    }

    public function complete(Request $request, $id)
    {
        $user = $request->user();
        $transaction = ProtectedTransaction::findOrFail($id);

        // Only buyer can complete and release funds
        if ($transaction->buyer_id !== $user->id) {
            return response()->json(['message' => 'Solo el comprador puede liberar los fondos'], 403);
        }

        if ($transaction->status !== 'paid') {
            return response()->json(['message' => 'La transacción debe estar en estado pagado para ser completada'], 400);
        }

        DB::transaction(function () use ($transaction) {
            $transaction->status = 'completed';
            $transaction->save();

            // Transfer amount (monto a recibir) to seller
            if ($transaction->seller_id) {
                $seller = User::find($transaction->seller_id);
                if ($seller) {
                    $seller->wallet_balance += $transaction->amount;
                    $seller->save();
                }
            }
        });

        return response()->json(['message' => 'Fondos liberados exitosamente al vendedor', 'transaction' => $transaction], 200);
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

        return response()->json(['message' => 'Transacción cancelada', 'transaction' => $transaction], 200);
    }
}
