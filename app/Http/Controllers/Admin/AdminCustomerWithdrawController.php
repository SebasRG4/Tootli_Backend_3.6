<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\CustomerLogic;
use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\CustomerWithdrawRequest;
use App\Models\User;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AdminCustomerWithdrawController extends Controller
{
    /**
     * List customer bank withdrawal requests with filters and stats
     */
    public function index(Request $request)
    {
        $status = $request->query('status', 'all');
        $search = $request->query('search', '');

        $query = CustomerWithdrawRequest::with(['user', 'bankAccount', 'processor']);

        if ($status !== 'all' && in_array($status, ['pending', 'approved', 'transferred', 'rejected', 'cancelled'])) {
            $query->where('status', $status);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('id', $search)
                  ->orWhere('spei_tracking_key', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($u) use ($search) {
                      $u->where('f_name', 'like', "%{$search}%")
                        ->orWhere('l_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  })
                  ->orWhereHas('bankAccount', function ($b) use ($search) {
                      $b->where('bank_name', 'like', "%{$search}%")
                        ->orWhere('account_holder', 'like', "%{$search}%")
                        ->orWhere('clabe_last4', 'like', "%{$search}%");
                  });
            });
        }

        // Summary Counters
        $counters = [
            'all' => CustomerWithdrawRequest::count(),
            'pending' => CustomerWithdrawRequest::where('status', 'pending')->count(),
            'pending_amount' => CustomerWithdrawRequest::where('status', 'pending')->sum('amount'),
            'approved' => CustomerWithdrawRequest::where('status', 'approved')->count(),
            'transferred' => CustomerWithdrawRequest::where('status', 'transferred')->count(),
            'transferred_amount' => CustomerWithdrawRequest::where('status', 'transferred')->sum('amount'),
            'rejected' => CustomerWithdrawRequest::where('status', 'rejected')->count(),
            'cancelled' => CustomerWithdrawRequest::where('status', 'cancelled')->count(),
        ];

        $withdraws = $query->orderBy('id', 'desc')->paginate(config('default_pagination', 25));

        return view('admin-views.tootli-wallet.withdraws.index', compact('withdraws', 'status', 'search', 'counters'));
    }

    /**
     * Show withdrawal request details and full CLABE for SPEI processing
     */
    public function show($id)
    {
        $withdraw = CustomerWithdrawRequest::with(['user', 'bankAccount', 'processor'])->findOrFail($id);
        $fullClabe = $withdraw->bankAccount ? $withdraw->bankAccount->getDecryptedClabe() : null;

        return view('admin-views.tootli-wallet.withdraws.show', compact('withdraw', 'fullClabe'));
    }

    /**
     * Move request to approved (in process)
     */
    public function approve(Request $request, $id)
    {
        $withdraw = CustomerWithdrawRequest::findOrFail($id);

        if ($withdraw->status !== 'pending') {
            Toastr::warning('Solo se pueden aprobar solicitudes en estado pendiente.');
            return back();
        }

        $withdraw->status = 'approved';
        $withdraw->processed_by = auth('admin')->id();
        $withdraw->save();

        Toastr::success('Solicitud de retiro aprobada para dispersión SPEI.');
        return back();
    }

    /**
     * Mark withdrawal as transferred via SPEI
     */
    public function markTransferred(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'spei_tracking_key' => 'required|string|max:100',
            'spei_proof' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                Toastr::error($error);
            }
            return back();
        }

        $withdraw = CustomerWithdrawRequest::findOrFail($id);

        if (!in_array($withdraw->status, ['pending', 'approved'])) {
            Toastr::warning('Esta solicitud ya fue procesada anteriormente.');
            return back();
        }

        $proofUrl = null;
        if ($request->hasFile('spei_proof')) {
            $proofUrl = Helpers::upload('customer_withdraws/', $request->file('spei_proof')->getClientOriginalExtension(), $request->file('spei_proof'));
        }

        $withdraw->status = 'transferred';
        $withdraw->spei_tracking_key = trim($request->spei_tracking_key);
        if ($proofUrl) {
            $withdraw->spei_proof_url = $proofUrl;
        }
        $withdraw->processed_by = auth('admin')->id();
        $withdraw->processed_at = now();
        $withdraw->save();

        Toastr::success('Transferencia SPEI marcada como exitosa y registrada con clave de rastreo.');
        return redirect()->route('admin.customer-withdraw.show', $withdraw->id);
    }

    /**
     * Reject withdrawal and atomically refund user's wallet balance
     */
    public function reject(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string|min:5|max:500',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                Toastr::error($error);
            }
            return back();
        }

        try {
            DB::transaction(function () use ($id, $request) {
                $withdraw = CustomerWithdrawRequest::where('id', $id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (!in_array($withdraw->status, ['pending', 'approved'])) {
                    throw new \Exception('INVALID_STATUS');
                }

                $withdraw->status = 'rejected';
                $withdraw->rejection_reason = trim($request->rejection_reason);
                $withdraw->processed_by = auth('admin')->id();
                $withdraw->processed_at = now();
                $withdraw->save();

                // Atomically refund customer wallet
                $refund = CustomerLogic::create_wallet_transaction(
                    $withdraw->user_id,
                    $withdraw->amount,
                    'withdraw_rejected_refund',
                    "Reembolso por retiro SPEI rechazado (#{$withdraw->id}): {$withdraw->rejection_reason}"
                );

                if (!$refund) {
                    throw new \Exception('REFUND_FAILED');
                }
            });

            Toastr::success('Solicitud de retiro rechazada. El saldo de $' . number_format(CustomerWithdrawRequest::find($id)->amount, 2) . ' fue reembolsado a la billetera del cliente.');
            return redirect()->route('admin.customer-withdraw.show', $id);

        } catch (\Exception $e) {
            if ($e->getMessage() === 'INVALID_STATUS') {
                Toastr::error('Esta solicitud ya no se encuentra en estado procesable.');
            } else {
                Toastr::error('Error al procesar el rechazo y reembolso: ' . $e->getMessage());
            }
            return back();
        }
    }
}
