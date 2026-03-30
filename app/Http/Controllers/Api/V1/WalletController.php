<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\BusinessSetting;
use App\Models\ExternalConfiguration;
use App\Models\User;
use App\Models\WalletBonus;
use App\Models\WalletPayment;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Library\Payer;
use App\Traits\Payment;
use App\Library\Receiver;
use App\Library\Payment as PaymentInfo;
use Illuminate\Support\Str;

class WalletController extends Controller
{
    public function transactions(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required',
            'offset' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $paginator = WalletTransaction::where('user_id', $request->user()->id)
            ->when($request['type'] && $request['type'] == 'order', function ($query) {
                $query->whereIn('transaction_type', ['order_place', 'order_refund', 'partial_payment']);
            })
            ->when($request['type'] && $request['type'] == 'loyalty_point', function ($query) {
                $query->whereIn('transaction_type', ['loyalty_point']);
            })
            ->when($request['type'] && $request['type'] == 'add_fund', function ($query) {
                $query->whereIn('transaction_type', ['add_fund']);
            })
            ->when($request['type'] && $request['type'] == 'referrer', function ($query) {
                $query->whereIn('transaction_type', ['referrer']);
            })
            ->when($request['type'] && $request['type'] == 'CashBack', function ($query) {
                $query->whereIn('transaction_type', ['CashBack']);
            })
            ->latest()->paginate($request->limit, ['*'], 'page', $request->offset);

        $total_qr_payment = WalletTransaction::where('user_id', $request->user()->id)
            ->where('transaction_type', 'qr_payment')
            ->sum('debit');

        $data = [
            'total_size' => $paginator->total(),
            'limit' => $request->limit,
            'offset' => $request->offset,
            'total_qr_payment' => (float) $total_qr_payment,
            'data' => $paginator->items()
        ];
        return response()->json($data, 200);
    }

    public function add_fund(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $digital_payment = Helpers::get_business_settings('digital_payment');
        if ($digital_payment['status'] == 0) {
            return response()->json(['errors' => ['message' => 'digital_payment_is_disable']], 403);
        }

        $customer = User::find($request->user()->id);

        $wallet = new WalletPayment();
        $wallet->user_id = $customer->id;
        $wallet->amount = $request->amount;
        $wallet->payment_status = 'pending';
        $wallet->payment_method = $request->payment_method;
        $wallet->save();

        $wallet_amount = $request->amount;

        if (!isset($customer)) {
            return response()->json(['errors' => ['message' => 'Customer not found']], 403);
        }

        if (!isset($wallet_amount)) {
            return response()->json(['errors' => ['message' => 'Amount not found']], 403);
        }

        if (!$request->has('payment_method')) {
            return response()->json(['errors' => ['message' => 'Payment not found']], 403);
        }

        $payer = new Payer(
            $customer->f_name . ' ' . $customer->l_name,
            $customer->email,
            $customer->phone,
            ''
        );

        $currency = BusinessSetting::where(['key' => 'currency'])->first()->value;
        $store_logo = BusinessSetting::where(['key' => 'logo'])->first();
        $additional_data = [
            'business_name' => BusinessSetting::where(['key' => 'business_name'])->first()?->value,
            'business_logo' => \App\CentralLogics\Helpers::get_full_url('business', $store_logo?->value, $store_logo?->storage[0]?->value ?? 'public')
        ];
        $payment_info = new PaymentInfo(
            success_hook: 'wallet_success',
            failure_hook: 'wallet_failed',
            currency_code: $currency,
            payment_method: $request->payment_method,
            payment_platform: $request->payment_platform,
            payer_id: $customer->id,
            receiver_id: '100',
            additional_data: $additional_data,
            payment_amount: $wallet_amount,
            external_redirect_link: $request->has('callback') ? $request['callback'] : session('callback'),
            attribute: 'wallet_payments',
            attribute_id: $wallet->id
        );

        $receiver_info = new Receiver('receiver_name', 'example.png');

        $redirect_link = Payment::generate_link($payer, $payment_info, $receiver_info);

        $data = [
            'redirect_link' => $redirect_link,
        ];
        return response()->json($data, 200);

    }

    public function get_bonus()
    {
        $bonuses = WalletBonus::Active()->Running()->latest()->get();
        return response()->json($bonuses ?? [], 200);
    }

    #handshake

    public function transferMartToDrivemondWallet(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $customer = Auth::user();
        if ($customer->wallet_balance < $request->amount) {
            $errors = [];
            array_push($errors, ['code' => 'insufficient_fund_403', 'message' => translate('messages.You have insufficient balance on wallet')]);
            return response()->json([
                'errors' => $errors
            ], 403);
        }
        if (Helpers::checkSelfExternalConfiguration()) {
            $currencyCode = Helpers::currency_code();
            $driveMondBaseUrl = ExternalConfiguration::where('key', 'drivemond_base_url')->first()?->value;
            $driveMondToken = ExternalConfiguration::where('key', 'drivemond_token')->first()?->value;
            $systemSelfToken = ExternalConfiguration::where('key', 'system_self_token')->first()?->value;
            $response = Http::post(
                $driveMondBaseUrl . '/api/customer/wallet/transfer-drivemond-from-mart',
                [
                    'bearer_token' => $request->bearerToken(),
                    'currency' => $currencyCode,
                    'amount' => $request->amount,
                    'token' => $driveMondToken,
                    'external_base_url' => url('/'),
                    'external_token' => $systemSelfToken,
                ]
            );
            if ($response->successful()) {
                $drivemondCustomerResponse = $response->json();
                if (array_key_exists('status', $drivemondCustomerResponse) && $drivemondCustomerResponse['status']) {
                    $drivemondCustomer = $drivemondCustomerResponse['data'];
                    $user = User::where(['phone' => $drivemondCustomer['phone']])->first();
                    if ($user) {
                        $user = User::find($request->user()->id);
                        $user->wallet_balance -= $request->amount;
                        $user->save();
                        $wallet_transaction = new WalletTransaction();
                        $wallet_transaction->user_id = $user->id;
                        $wallet_transaction->transaction_id = Str::uuid();
                        $wallet_transaction->transaction_type = 'wallet_transfer_mart_to_drivemond';
                        $wallet_transaction->debit = $request->amount;
                        $wallet_transaction->balance = $user->wallet_balance;
                        $wallet_transaction->created_at = now();
                        $wallet_transaction->updated_at = now();
                        $wallet_transaction->save();
                        $data = [
                            'status' => true,
                            'data' => $user
                        ];
                        return response()->json($data);
                    }
                }
                $drivemondCustomer = $drivemondCustomerResponse['data'];
                if (array_key_exists('error_code', $drivemondCustomer) && $drivemondCustomer['error_code'] == 405) {
                    $errors = [];
                    array_push($errors, ['code' => 'currency_not_match_403', 'message' => translate('messages.Currency not matched, Please contact support')]);
                    return response()->json([
                        'errors' => $errors
                    ], 403);
                }
            } else {
                $errors = [];
                array_push($errors, ['code' => 'account_not_found_403', 'message' => translate('messages.drivemond account not found')]);
                return response()->json([
                    'errors' => $errors
                ], 403);
            }


        }
        $errors = [];
        array_push($errors, ['code' => 'account_not_found_403', 'message' => translate('messages.drivemond account not found')]);
        return response()->json([
            'errors' => $errors
        ], 403);

    }

    public function transferMartFromDrivemondWallet(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'currency' => 'required',
            'amount' => 'required',
            'bearer_token' => 'required',
            'token' => 'required',
            'external_base_url' => 'required',
            'external_token' => 'required',
        ]);
        if ($validator->fails()) {
            $data = [
                'status' => false,
                'data' => Helpers::error_processor($validator),
            ];
            return response()->json($data);
        }
        if (strcasecmp(str_replace('"', '', $request->currency), str_replace('"', '', Helpers::currency_code())) !== 0) {
            $data = [
                'status' => false,
                'data' => ['error_code' => 405, 'message' => "Currency not matched, Please contact support"],
            ];
            return response()->json($data);
        }
        if (Helpers::checkSelfExternalConfiguration() && Helpers::checkExternalConfiguration($request->external_base_url, $request->external_token, $request->token)) {
            $driveMondBaseUrl = ExternalConfiguration::where('key', 'drivemond_base_url')->first()?->value;
            $driveMondToken = ExternalConfiguration::where('key', 'drivemond_token')->first()?->value;
            $systemSelfToken = ExternalConfiguration::where('key', 'system_self_token')->first()?->value;
            $response = Http::withToken($request->bearer_token)->post(
                $driveMondBaseUrl . '/api/customer/get-data',
                [
                    'token' => $driveMondToken,
                    'external_base_url' => url('/'),
                    'external_token' => $systemSelfToken,
                ]
            );
            if ($response->successful()) {
                $drivemondCustomerResponse = $response->json();
                if ($drivemondCustomerResponse['status']) {
                    $drivemondCustomer = $drivemondCustomerResponse['data'];
                    $user = User::where(['phone' => $drivemondCustomer['phone']])->first();
                    if ($user) {
                        $user->wallet_balance += $request->amount;
                        $user->save();
                        $wallet_transaction = new WalletTransaction();
                        $wallet_transaction->user_id = $user->id;
                        $wallet_transaction->transaction_id = Str::uuid();
                        $wallet_transaction->transaction_type = 'wallet_transfer_mart_from_drivemond';
                        $wallet_transaction->credit = $request->amount;
                        $wallet_transaction->balance = $user->wallet_balance;
                        $wallet_transaction->created_at = now();
                        $wallet_transaction->updated_at = now();
                        $wallet_transaction->save();

                        $notificationData = [
                            'title' => translate('wallet_transfer_mart_from_drivemond'),
                            'description' => translate('you_transfer_your_wallet_balance_mart_from_drivemond'),
                            'order_id' => '',
                            'image' => '',
                            'type' => 'wallet_transfer'
                        ];
                        Helpers::send_push_notif_to_device($user->cm_firebase_token, $notificationData);

                        $data = [
                            'status' => true,
                            'data' => $user
                        ];
                        return response()->json($data);
                    }
                }
            }
            $drivemondCustomer = $drivemondCustomerResponse['data'];
            if ($drivemondCustomer['error_code'] == 402) {
                $data = [
                    'status' => false,
                    'data' => ['error_code' => 402, 'message' => "Drivemond user not found"]
                ];
                return response()->json($data);
            }

        }
        $data = [
            'status' => false,
            'data' => ['error_code' => 402, 'message' => "Invalid token"]
        ];
        return response()->json($data);


    }

    public function qr_pay(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'store_id' => 'required',
            'amount' => 'required|numeric|min:0.01',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $user = $request->user();

        // DEBT BARRIER
        if ($user->wallet_balance < 0) {
            return response()->json([
                'errors' => [
                    ['code' => 'debt_unpaid', 'message' => 'Tienes un saldo negativo por ' . \App\CentralLogics\Helpers::format_currency($user->wallet_balance) . '. Ingresa a la sección de Billetera para recargarlo y poder volver a pedir.']
                ]
            ], 403);
        }

        // 1. Double tap prevention (30 seconds Lock) to avoid network-error retries from getting double charged
        $lockKey = "qr_pay_lock_{$user->id}_{$request->store_id}_{$request->amount}";
        if (!\Illuminate\Support\Facades\Cache::add($lockKey, true, 30)) {
            return response()->json([
                'errors' => [
                    ['code' => 'payment_processing', 'message' => 'Transacción en proceso. Por favor, espera unos segundos, o verifica tus movimientos si la red falló.']
                ]
            ], 429);
        }

        try {
            // Find store to get name for notification
            $store = \App\Models\Store::find($request->store_id);
            if (!$store) {
                \Illuminate\Support\Facades\Cache::forget($lockKey);
                return response()->json([
                    'errors' => [
                        ['code' => 'store_not_found', 'message' => 'Restaurante no encontrado']
                    ]
                ], 404);
            }

            // Prepare the payload for the Go worker
            $payload = [
                'user_id' => $user->id,
                'store_id' => (int) $request->store_id,
                'amount' => (float) $request->amount,
            ];

            // Forward to Go worker using configurable URL
            $goWorkerUrl = env('GO_WORKER_URL', 'http://go_worker:8080');
            $response = Http::withHeaders([
                'X-Internal-Secret' => env('INTERNAL_SECRET', 'tootli_internal_secret_key'),
            ])->timeout(20)->post($goWorkerUrl . '/api/v1/user/wallet/qr-pay', $payload);

            if ($response->successful()) {
                // 2. Send Push Notification to USER (Client)
                if ($user->cm_firebase_token) {
                    $push_data = [
                        'title' => 'Pago exitoso',
                        'description' => 'Pagaste ' . \App\CentralLogics\Helpers::format_currency($request->amount) . ' a ' . $store->name,
                        'order_id' => 0,
                        'image' => '',
                        'type' => 'qr_payment'
                    ];
                    \App\CentralLogics\Helpers::send_push_notif_to_device($user->cm_firebase_token, $push_data);
                }

                // 3. Send Push Notification to STORE VENDOR
                if ($store->vendor && $store->vendor->firebase_token) {
                    $vendor_push_data = [
                        'title' => '¡Nuevo Pago Recibido! 💸',
                        'description' => 'Recibiste ' . \App\CentralLogics\Helpers::format_currency($request->amount) . ' de ' . $user->f_name . ' mediante Scan & Pay.',
                        'order_id' => 0,
                        'image' => '',
                        'type' => 'qr_payment'
                    ];
                    \App\CentralLogics\Helpers::send_push_notif_to_device($store->vendor->firebase_token, $vendor_push_data);
                }

                return response()->json($response->json(), 200);
            }

            // If the Go worker fails gracefully (e.g. insufficient funds), we forget the cache so they can top up and retry immediately
            \Illuminate\Support\Facades\Cache::forget($lockKey);
            return response()->json($response->json() ?? ['message' => 'Payment failed'], $response->status());
        } catch (\Exception $e) {
            // Keep the cache lock active if there's a timeout, to prevent the user from rapidly retrying and getting charged twice if Go is actually processing it slowly.
            return response()->json([
                'errors' => [
                    ['code' => 'server_error', 'message' => 'Failed to connect to payment worker: ' . $e->getMessage()]
                ]
            ], 500);
        }
    }
}
