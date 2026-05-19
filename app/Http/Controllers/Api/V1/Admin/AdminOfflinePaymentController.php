<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryManOfflinePayment;
use App\Models\DeliveryManWallet;
use App\Models\AccountTransaction;
use App\CentralLogics\Helpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AdminOfflinePaymentController extends Controller
{
    public function list(Request $request)
    {
        $status = $request->query('status', 'pending');
        
        $payments = DeliveryManOfflinePayment::with(['delivery_man', 'offline_payment_method'])
            ->where('status', $status)
            ->latest()
            ->get()
            ->map(function($payment) {
                return [
                    'id' => $payment->id,
                    'amount' => (float)$payment->amount,
                    'status' => $payment->status,
                    'admin_note' => $payment->admin_note,
                    'payment_info' => json_decode($payment->payment_info, true) ?? [],
                    'method_name' => $payment->offline_payment_method ? $payment->offline_payment_method->method_name : ($payment->method_id == 0 ? 'Cash / Deposit' : 'Offline Payment'),
                    'created_at' => $payment->created_at->toIso8601String(),
                    'delivery_man' => $payment->delivery_man ? [
                        'id' => $payment->delivery_man->id,
                        'name' => $payment->delivery_man->f_name . ' ' . $payment->delivery_man->l_name,
                        'phone' => $payment->delivery_man->phone,
                        'image' => $payment->delivery_man->image,
                        'cash_in_hand' => (float)($payment->delivery_man->wallet ? $payment->delivery_man->wallet->collected_cash : 0.0),
                    ] : null,
                ];
            });

        return response()->json($payments, 200);
    }

    public function verify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:delivery_man_offline_payments,id',
            'status' => 'required|in:approved,denied',
            'admin_note' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $payment = DeliveryManOfflinePayment::with('delivery_man')->findOrFail($request->id);
        
        if ($payment->status != 'pending') {
            return response()->json([
                'errors' => [
                    ['code' => 'payment_processed', 'message' => 'Esta solicitud de pago ya ha sido procesada anteriormente.']
                ]
            ], 400);
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
                        'title' => 'Pago Aprobado',
                        'description' => 'Tu pago offline por el monto de ' . Helpers::format_currency($payment->amount) . ' ha sido aprobado con éxito.',
                        'order_id' => '',
                        'image' => '',
                        'type' => 'payment_approved',
                    ];
                    Helpers::send_push_notif_to_device($fcm_token, $data);
                }

            } elseif ($request->status == 'denied') {
                $payment->status = 'denied';
                $payment->admin_note = $request->admin_note;
                $payment->save();

                // Push Notification
                $fcm_token = $payment->delivery_man->fcm_token;
                if ($fcm_token) {
                    $data = [
                        'title' => 'Pago Denegado',
                        'description' => 'Tu pago offline por el monto de ' . Helpers::format_currency($payment->amount) . ' ha sido denegado. Revisa la nota del administrador.',
                        'order_id' => '',
                        'image' => '',
                        'type' => 'payment_denied',
                    ];
                    Helpers::send_push_notif_to_device($fcm_token, $data);
                }
            }

            DB::commit();
            return response()->json(['message' => 'Solicitud de pago procesada correctamente.'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'errors' => [
                    ['code' => 'failed', 'message' => 'Error al procesar el pago: ' . $e->getMessage()]
                ]
            ], 500);
        }
    }
}
