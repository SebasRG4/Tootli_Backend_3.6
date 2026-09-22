<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\CustomerLogic;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\ProtectedTransaction;
use App\Models\ServiceJob;
use App\Models\Store;
use App\Models\TootliDispute;
use App\Models\User;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AdminTootliProtectorController extends Controller
{
    /**
     * Lista de disputas con filtros de estado, tipo y búsqueda.
     */
    public function index(Request $request)
    {
        $status = $request->query('status', 'all');
        $type = $request->query('type', 'all');
        $search = $request->query('search', '');

        $query = TootliDispute::with(['claimant', 'defendant', 'disputable']);

        // Filtro por estado
        if ($status === 'open') {
            $query->where('status', 'open');
        } elseif ($status === 'under_review') {
            $query->where('status', 'under_review');
        } elseif ($status === 'resolved') {
            $query->whereIn('status', ['resolved_buyer_refund', 'resolved_seller_payout', 'resolved_partial']);
        } elseif ($status === 'cancelled') {
            $query->where('status', 'cancelled');
        }

        // Filtro por tipo
        if ($type === 'service') {
            $query->where('disputable_type', ServiceJob::class);
        } elseif ($type === 'transaction') {
            $query->where('disputable_type', ProtectedTransaction::class);
        }

        // Búsqueda
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('id', $search)
                  ->orWhere('reason', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('claimant', function ($c) use ($search) {
                      $c->where('f_name', 'like', "%{$search}%")
                        ->orWhere('l_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                  })
                  ->orWhereHas('defendant', function ($d) use ($search) {
                      $d->where('f_name', 'like', "%{$search}%")
                        ->orWhere('l_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        $disputes = $query->latest()->paginate(config('default_pagination') ?? 20)->withQueryString();

        // Conteos para tabs y métricas
        $counts = [
            'all' => TootliDispute::count(),
            'open' => TootliDispute::where('status', 'open')->count(),
            'under_review' => TootliDispute::where('status', 'under_review')->count(),
            'resolved' => TootliDispute::whereIn('status', ['resolved_buyer_refund', 'resolved_seller_payout', 'resolved_partial'])->count(),
            'cancelled' => TootliDispute::where('status', 'cancelled')->count(),
        ];

        return view('admin-views.tootli-protector.disputes.index', compact('disputes', 'status', 'type', 'search', 'counts'));
    }

    /**
     * Detalle completo de una disputa para mediación de soporte.
     */
    public function show($id)
    {
        $dispute = TootliDispute::with(['claimant', 'defendant', 'disputable', 'resolver'])->findOrFail($id);

        return view('admin-views.tootli-protector.disputes.show', compact('dispute'));
    }

    /**
     * Cambiar estado preliminar (ej. marcar bajo revisión).
     */
    public function updateStatus(Request $request, $id)
    {
        $dispute = TootliDispute::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:open,under_review',
            'notes'  => 'nullable|string',
        ]);

        if ($validator->fails()) {
            Toastr::error($validator->errors()->first());
            return back();
        }

        $dispute->status = $request->status;
        if ($request->filled('notes')) {
            $dispute->resolution_notes = ($dispute->resolution_notes ? $dispute->resolution_notes . "\n---\n" : '') .
                "[" . now()->format('Y-m-d H:i') . " Admin #" . auth('admin')->id() . "]: " . $request->notes;
        }
        $dispute->save();

        Toastr::success('Estado de la disputa actualizado a: ' . ($request->status === 'under_review' ? 'En Mediación / Revisión' : 'Abierta'));
        return back();
    }

    /**
     * Dictamen formal y resolución de la disputa.
     */
    public function resolve(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'resolution'       => 'required|in:refund_buyer,payout_seller,partial_refund',
            'refund_amount'    => 'required_if:resolution,partial_refund|numeric|min:0',
            'resolution_notes' => 'required|string|min:5|max:2000',
        ]);

        if ($validator->fails()) {
            Toastr::error($validator->errors()->first());
            return back()->withInput();
        }

        $dispute = TootliDispute::with(['disputable', 'claimant', 'defendant'])->findOrFail($id);

        if (!in_array($dispute->status, ['open', 'under_review'])) {
            Toastr::error('Esta disputa ya ha sido cerrada o resuelta previamente.');
            return back();
        }

        $resolution = $request->resolution;
        $notes = $request->resolution_notes;
        $adminId = auth('admin')->id();

        DB::transaction(function () use ($dispute, $resolution, $notes, $adminId, $request) {
            $dispute->resolution_notes = $notes;
            $dispute->resolved_by = $adminId;
            $dispute->resolved_at = Carbon::now();

            if ($dispute->disputable_type === ProtectedTransaction::class) {
                $this->resolveProtectedTransaction($dispute, $resolution, $request->refund_amount);
            } elseif ($dispute->disputable_type === ServiceJob::class) {
                $this->resolveServiceJob($dispute, $resolution, $request->refund_amount);
            }
        });

        Toastr::success('La disputa #' . $dispute->id . ' fue resuelta satisfactoriamente y los fondos han sido procesados.');
        return redirect()->route('admin.tootli-protector.disputes.index');
    }

    /**
     * Resolución financiera para transacciones P2P de productos.
     */
    private function resolveProtectedTransaction(TootliDispute $dispute, string $resolution, ?float $partialRefundAmount)
    {
        /** @var ProtectedTransaction $tx */
        $tx = $dispute->disputable;
        $buyer = $tx->buyer;
        $seller = $tx->seller;

        if ($resolution === 'refund_buyer') {
            $dispute->status = 'resolved_buyer_refund';
            $dispute->refund_amount = $tx->total_amount;
            $dispute->save();

            if ($buyer) {
                $buyer->wallet_balance += $tx->total_amount;
                $buyer->save();

                CustomerLogic::create_wallet_transaction(
                    $buyer->id,
                    $tx->total_amount,
                    'order_refund',
                    'dispute_admin_refund_tx_' . $tx->id
                );
            }

            $tx->status = 'cancelled';
            $tx->save();

        } elseif ($resolution === 'payout_seller') {
            $dispute->status = 'resolved_seller_payout';
            $dispute->save();

            if ($seller) {
                $seller->wallet_balance += $tx->amount;
                $seller->save();

                CustomerLogic::create_wallet_transaction(
                    $seller->id,
                    $tx->amount,
                    'add_fund_by_admin',
                    'dispute_admin_payout_tx_' . $tx->id
                );
            }

            $tx->status = 'completed';
            $tx->save();

        } elseif ($resolution === 'partial_refund') {
            $refundAmt = min((float)$partialRefundAmount, $tx->amount);
            $sellerAmt = max(0, $tx->amount - $refundAmt);

            $dispute->status = 'resolved_partial';
            $dispute->refund_amount = $refundAmt;
            $dispute->save();

            if ($buyer && $refundAmt > 0) {
                $buyer->wallet_balance += $refundAmt;
                $buyer->save();

                CustomerLogic::create_wallet_transaction(
                    $buyer->id,
                    $refundAmt,
                    'order_refund',
                    'dispute_admin_partial_refund_tx_' . $tx->id
                );
            }

            if ($seller && $sellerAmt > 0) {
                $seller->wallet_balance += $sellerAmt;
                $seller->save();

                CustomerLogic::create_wallet_transaction(
                    $seller->id,
                    $sellerAmt,
                    'add_fund_by_admin',
                    'dispute_admin_partial_payout_tx_' . $tx->id
                );
            }

            $tx->status = 'completed';
            $tx->save();
        }
    }

    /**
     * Resolución financiera para órdenes de servicios técnicos / profesionales.
     */
    private function resolveServiceJob(TootliDispute $dispute, string $resolution, ?float $partialRefundAmount)
    {
        /** @var ServiceJob $job */
        $job = $dispute->disputable;
        $job->loadMissing(['acceptedBid.store.vendor']);

        $customer = $job->user;
        $store = $job->acceptedBid?->store;
        $totalJobPrice = (float)($job->acceptedBid ? $job->acceptedBid->price : $job->budget);

        if ($resolution === 'refund_buyer') {
            $dispute->status = 'resolved_buyer_refund';
            $dispute->refund_amount = $totalJobPrice;
            $dispute->save();

            if ($customer) {
                $customer->wallet_balance += $totalJobPrice;
                $customer->save();

                CustomerLogic::create_wallet_transaction(
                    $customer->id,
                    $totalJobPrice,
                    'order_refund',
                    'job_admin_dispute_refund_' . $job->id
                );
            }

            $job->status = 'cancelled';
            $job->save();

        } elseif ($resolution === 'payout_seller') {
            $dispute->status = 'resolved_seller_payout';
            $dispute->save();

            if ($store && $store->vendor) {
                $vendorWallet = $store->vendor->wallet;
                if ($vendorWallet) {
                    $vendorWallet->total_earning += $totalJobPrice;
                    $vendorWallet->balance += $totalJobPrice;
                    $vendorWallet->save();
                }
            }

            $job->status = 'delivered';
            $job->save();

        } elseif ($resolution === 'partial_refund') {
            $refundAmt = min((float)$partialRefundAmount, $totalJobPrice);
            $storeAmt = max(0, $totalJobPrice - $refundAmt);

            $dispute->status = 'resolved_partial';
            $dispute->refund_amount = $refundAmt;
            $dispute->save();

            if ($customer && $refundAmt > 0) {
                $customer->wallet_balance += $refundAmt;
                $customer->save();

                CustomerLogic::create_wallet_transaction(
                    $customer->id,
                    $refundAmt,
                    'order_refund',
                    'job_admin_partial_refund_' . $job->id
                );
            }

            if ($store && $store->vendor && $storeAmt > 0) {
                $vendorWallet = $store->vendor->wallet;
                if ($vendorWallet) {
                    $vendorWallet->total_earning += $storeAmt;
                    $vendorWallet->balance += $storeAmt;
                    $vendorWallet->save();
                }
            }

            $job->status = 'delivered';
            $job->save();
        }
    }
}
