<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryManOfflinePayment;
use App\CentralLogics\Helpers;
use App\Models\DeliveryManWallet;
use App\Models\AccountTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Brian2694\Toastr\Facades\Toastr;

class DeliveryManOfflinePaymentController extends Controller
{
    public function list(Request $request)
    {
        $status = $request->query('status', 'pending');
        
        $payments = DeliveryManOfflinePayment::with(['delivery_man', 'offline_payment_method'])
            ->where('status', $status)
            ->latest()
            ->paginate(config('default_pagination'));

        return view('admin-views.delivery-man.offline-payment.list', compact('payments', 'status'));
    }

    public function verify_status(Request $request)
    {
        $payment = DeliveryManOfflinePayment::with('delivery_man')->findOrFail($request->id);
        
        if ($payment->status != 'pending') {
            Toastr::warning(translate('messages.payment_already_processed'));
            return back();
        }

        DB::beginTransaction();
        try {
            if ($request->status == 'approved') {
                $payment->status = 'approved';
                $payment->admin_note = $request->admin_note;
                $payment->save();

                // Automate debt reduction
                $wallet = DeliveryManWallet::firstOrCreate(['delivery_man_id' => $payment->delivery_man_id]);
                if ($wallet->collected_cash >= $payment->amount) {
                    $wallet->collected_cash -= $payment->amount;
                } else {
                    $wallet->collected_cash = 0;
                }
                $wallet->save();

                // Create Account Transaction
                $account_transaction = new AccountTransaction();
                $account_transaction->from_type = 'deliveryman';
                $account_transaction->from_id = $payment->delivery_man_id;
                $account_transaction->created_by = 'admin';
                $account_transaction->method = $payment->offline_payment_method ? $payment->offline_payment_method->method_name : 'offline_payment';
                $account_transaction->ref = $payment->id;
                $account_transaction->amount = $payment->amount;
                $account_transaction->current_balance = $wallet->collected_cash;
                $account_transaction->type = 'collected';
                $account_transaction->save();
                
                // Push Notification
                $fcm_token = $payment->delivery_man->fcm_token;
                if ($fcm_token) {
                    $data = [
                        'title' => translate('messages.Payment_Approved'),
                        'description' => translate('messages.Your_offline_payment_of_amount') . ' ' . Helpers::format_currency($payment->amount) . ' ' . translate('messages.has_been_approved'),
                        'order_id' => '',
                        'image' => '',
                        'type' => 'payment_approved',
                    ];
                    Helpers::send_push_notif_to_device($fcm_token, $data);
                }

                Toastr::success(translate('messages.payment_approved_successfully'));

            } elseif ($request->status == 'denied') {
                $payment->status = 'denied';
                $payment->admin_note = $request->admin_note;
                $payment->save();

                // Push Notification
                $fcm_token = $payment->delivery_man->fcm_token;
                if ($fcm_token) {
                    $data = [
                        'title' => translate('messages.Payment_Denied'),
                        'description' => translate('messages.Your_offline_payment_of_amount') . ' ' . Helpers::format_currency($payment->amount) . ' ' . translate('messages.has_been_denied'),
                        'order_id' => '',
                        'image' => '',
                        'type' => 'payment_denied',
                    ];
                    Helpers::send_push_notif_to_device($fcm_token, $data);
                }

                Toastr::info(translate('messages.payment_denied_successfully'));
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error(translate('messages.failed_to_process_payment'));
        }

        return back();
    }
}
