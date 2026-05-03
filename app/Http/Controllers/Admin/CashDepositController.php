<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryManCashDeposit;
use App\Models\DeliveryMan;
use App\Models\DeliveryManWallet;
use App\Models\AccountTransaction;
use App\CentralLogics\Helpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Brian2694\Toastr\Facades\Toastr;

class CashDepositController extends Controller
{
    public function list(Request $request)
    {
        $status = $request->query('status', 'pending');
        $deposits = DeliveryManCashDeposit::with('delivery_man')
            ->where('status', $status)
            ->latest()
            ->paginate(config('default_pagination'));

        return view('admin-views.delivery-man.cash-deposit.list', compact('deposits', 'status'));
    }

    public function update_status(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:delivery_man_cash_deposits,id',
            'status' => 'required|in:approved,denied',
            'admin_note' => 'nullable|string'
        ]);

        $deposit = DeliveryManCashDeposit::with('delivery_man')->findOrFail($request->id);

        if ($deposit->status !== 'pending') {
            Toastr::warning(translate('messages.deposit_already_processed'));
            return back();
        }

        DB::beginTransaction();
        try {
            $dm = $deposit->delivery_man;
            $wallet = DeliveryManWallet::firstOrCreate(['delivery_man_id' => $dm->id]);

            if ($request->status === 'approved') {
                $deposit->status = 'approved';
                $deposit->approved_by = auth('admin')->id();
                $deposit->save();

                // Finalize balance (it was already deducted from collected_cash on reporting)
                // Just decrease pending_deposit_amount
                $dm->pending_deposit_amount -= $deposit->amount;
                if ($dm->pending_deposit_amount < 0) $dm->pending_deposit_amount = 0;
                $dm->last_deposit_at = now();
                $dm->save();

                // Create Account Transaction for history
                $account_transaction = new AccountTransaction();
                $account_transaction->from_type = 'deliveryman';
                $account_transaction->from_id = $dm->id;
                $account_transaction->created_by = 'admin';
                $account_transaction->method = 'cash_deposit';
                $account_transaction->ref = $deposit->id;
                $account_transaction->amount = $deposit->amount;
                $account_transaction->current_balance = $wallet->collected_cash;
                $account_transaction->type = 'collected';
                $account_transaction->save();

                Toastr::success(translate('messages.deposit_approved_successfully'));
                
                // Notify Deliveryman
                Helpers::send_push_notif_to_device($dm->fcm_token, [
                    'title' => translate('messages.deposit_approved'),
                    'description' => translate('messages.your_deposit_of') . ' ' . Helpers::format_currency($deposit->amount) . ' ' . translate('messages.has_been_approved'),
                    'type' => 'deposit_approved',
                ]);

            } else {
                $deposit->status = 'denied';
                $deposit->save();

                // Rollback balance: return to collected_cash
                $wallet->collected_cash += $deposit->amount;
                $wallet->save();

                $dm->pending_deposit_amount -= $deposit->amount;
                if ($dm->pending_deposit_amount < 0) $dm->pending_deposit_amount = 0;
                $dm->save();

                Toastr::info(translate('messages.deposit_denied_successfully'));

                // Notify Deliveryman
                Helpers::send_push_notif_to_device($dm->fcm_token, [
                    'title' => translate('messages.deposit_denied'),
                    'description' => translate('messages.your_deposit_of') . ' ' . Helpers::format_currency($deposit->amount) . ' ' . translate('messages.has_been_denied'),
                    'type' => 'deposit_denied',
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error(translate('messages.failed_to_update_deposit_status'));
        }

        return back();
    }
}
