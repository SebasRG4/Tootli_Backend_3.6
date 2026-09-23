<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\CustomerLogic;
use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Mail\CustomerWalletSecurityAlertMail;
use App\Models\CustomerBankAccount;
use App\Models\CustomerWithdrawRequest;
use App\Models\User;
use App\Services\Fintech\BanxicoClabeValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class CustomerWalletWithdrawController extends Controller
{
    /**
     * KYC Tier Limits (in MXN)
     */
    private const LIMITS = [
        'unverified' => [
            'per_transaction' => 3000.00,
            'daily' => 3000.00,
            'monthly' => 10000.00,
        ],
        'verified' => [
            'per_transaction' => 25000.00,
            'daily' => 25000.00,
            'monthly' => 100000.00,
        ],
    ];

    /**
     * Get user's wallet security status and withdrawal limits
     */
    public function getSecurityStatus(Request $request)
    {
        $user = $request->user();
        $isVerified = ($user->identity_verified === 'approved');
        $tier = $isVerified ? 'verified' : 'unverified';
        $limits = self::LIMITS[$tier];

        // Calculate usage today and this month
        $todayStart = now()->startOfDay();
        $monthStart = now()->startOfMonth();

        $dailyUsed = (float) CustomerWithdrawRequest::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'approved', 'transferred'])
            ->where('created_at', '>=', $todayStart)
            ->sum('amount');

        $monthlyUsed = (float) CustomerWithdrawRequest::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'approved', 'transferred'])
            ->where('created_at', '>=', $monthStart)
            ->sum('amount');

        $isLocked = ($user->wallet_locked_until && $user->wallet_locked_until->isFuture());
        $lockMinutesRemaining = $isLocked ? (int) now()->diffInMinutes($user->wallet_locked_until, false) : 0;

        $balances = self::calculateWalletBalances($user->id, $user->wallet_balance);

        return response()->json([
            'has_pin' => !empty($user->wallet_pin),
            'is_locked' => $isLocked,
            'lock_minutes_remaining' => max(0, $lockMinutesRemaining),
            'failed_pin_attempts' => (int) $user->failed_pin_attempts,
            'kyc_status' => $user->identity_verified ?? 'none',
            'is_kyc_verified' => $isVerified,
            'limits' => [
                'tier' => $tier,
                'per_transaction_limit' => $limits['per_transaction'],
                'daily_limit' => $limits['daily'],
                'monthly_limit' => $limits['monthly'],
                'daily_used' => $dailyUsed,
                'monthly_used' => $monthlyUsed,
                'daily_available' => max(0, min($limits['daily'] - $dailyUsed, $balances['withdrawable_balance'])),
                'monthly_available' => max(0, min($limits['monthly'] - $monthlyUsed, $balances['withdrawable_balance'])),
            ],
            'cooling_off_period_hours' => 0,
            'wallet_balance' => $balances['total_balance'],
            'withdrawable_balance' => $balances['withdrawable_balance'],
            'promotional_balance' => $balances['promotional_balance'],
        ], 200);
    }

    /**
     * Setup a 6-digit Wallet Security PIN
     */
    public function setupPin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pin' => 'required|digits:6',
            'pin_confirmation' => 'required|same:pin',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $user = $request->user();

        // Must verify user account password before setting PIN
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'errors' => [['code' => 'auth-001', 'message' => 'La contraseña de su cuenta Tootli es incorrecta.']]
            ], 401);
        }

        // Avoid common trivial PINs like 123456, 000000, 111111
        $trivialPins = ['123456', '654321', '000000', '111111', '222222', '333333', '444444', '555555', '666666', '777777', '888888', '999999'];
        if (in_array($request->pin, $trivialPins)) {
            return response()->json([
                'errors' => [['code' => 'pin-weak', 'message' => 'Por su seguridad bancaria, elija un PIN más seguro (no use números repetidos ni secuencias consecutivas).']]
            ], 422);
        }

        $user->wallet_pin = Hash::make($request->pin);
        $user->wallet_pin_set_at = now();
        $user->failed_pin_attempts = 0;
        $user->wallet_locked_until = null;
        $user->save();

        $this->sendSecurityNotification(
            $user,
            'PIN de Seguridad Configurado',
            'Has configurado exitosamente el PIN de 6 dígitos para proteger las operaciones de tu billetera Tootli.',
            [
                'Acción' => 'Configuración inicial de PIN',
                'Fecha' => now()->format('d/m/Y H:i') . ' hrs',
                'Dirección IP' => $request->ip() ?? 'N/A',
            ],
            'pin_setup'
        );

        return response()->json([
            'message' => 'PIN de seguridad de Tootli Wallet configurado exitosamente.',
            'has_pin' => true,
        ], 200);
    }

    /**
     * Change Wallet Security PIN
     */
    public function changePin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_pin' => 'required|digits:6',
            'new_pin' => 'required|digits:6|different:current_pin',
            'new_pin_confirmation' => 'required|same:new_pin',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $user = $request->user();

        $pinCheck = $this->verifyPinInternal($user, $request->current_pin);
        if (!$pinCheck['success']) {
            return response()->json([
                'errors' => [['code' => 'pin-invalid', 'message' => $pinCheck['message']]]
            ], 403);
        }

        $user->wallet_pin = Hash::make($request->new_pin);
        $user->wallet_pin_set_at = now();
        $user->failed_pin_attempts = 0;
        $user->wallet_locked_until = null;
        $user->save();

        $this->sendSecurityNotification(
            $user,
            'PIN de Seguridad Actualizado',
            'Tu PIN de seguridad para retiros y movimientos de billetera ha sido modificado exitosamente.',
            [
                'Acción' => 'Actualización de PIN',
                'Fecha' => now()->format('d/m/Y H:i') . ' hrs',
                'Dirección IP' => $request->ip() ?? 'N/A',
            ],
            'pin_changed'
        );

        return response()->json([
            'message' => 'PIN de seguridad actualizado exitosamente.',
        ], 200);
    }

    /**
     * List user's registered bank accounts
     */
    public function getBankAccounts(Request $request)
    {
        $user = $request->user();
        $accounts = CustomerBankAccount::where('user_id', $user->id)
            ->where('status', 'active')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json($accounts, 200);
    }

    /**
     * Send 6-digit OTP code to user's email for Bank Account verification
     */
    public function sendBankAccountOtp(Request $request)
    {
        $user = $request->user();

        if (empty($user->email)) {
            return response()->json([
                'errors' => [['code' => 'email-missing', 'message' => 'Su cuenta no tiene un correo electrónico registrado para recibir el código de seguridad.']]
            ], 422);
        }

        $otp = (string) rand(100000, 999999);
        if (env('APP_MODE') == 'test') {
            $otp = '123456';
        }

        Cache::put("clabe_otp_{$user->id}", [
            'otp' => $otp,
            'email' => $user->email,
            'created_at' => now(),
        ], now()->addMinutes(10));

        $this->sendSecurityNotification(
            $user,
            'Código de Verificación Bancaria',
            "Tu código de seguridad para vincular y activar tu cuenta bancaria en Tootli Wallet es: {$otp}. Este código expira en 10 minutos. No lo compartas con nadie.",
            [
                'Código OTP' => $otp,
                'Válido por' => '10 minutos',
                'Acción' => 'Vinculación de cuenta bancaria',
                'Fecha' => now()->format('d/m/Y H:i') . ' hrs',
            ],
            'bank_account_otp'
        );

        return response()->json([
            'message' => 'Código de verificación enviado exitosamente a ' . $user->email,
            'email' => $user->email,
        ], 200);
    }

    /**
     * Add a bank account (validated with Banxico CLABE, Wallet PIN, Email OTP and Facial Selfie)
     */
    public function addBankAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'clabe' => 'required|digits:18',
            'account_holder' => 'required|string|min:3|max:150',
            'pin' => 'required|digits:6',
            'email_otp' => 'required|digits:6',
            'selfie' => 'required|image|mimes:jpeg,jpg,png|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $user = $request->user();

        // 1. Verify Wallet PIN
        $pinCheck = $this->verifyPinInternal($user, $request->pin);
        if (!$pinCheck['success']) {
            return response()->json([
                'errors' => [['code' => 'pin-invalid', 'message' => $pinCheck['message']]]
            ], 403);
        }

        // 2. Validate Email OTP
        $cachedOtpData = Cache::get("clabe_otp_{$user->id}");
        $isTestOtp = (env('APP_MODE') == 'test' && $request->email_otp === '123456');

        if (!$isTestOtp && (!$cachedOtpData || $cachedOtpData['otp'] !== $request->email_otp)) {
            return response()->json([
                'errors' => [['code' => 'otp-invalid', 'message' => 'El código de verificación por correo es incorrecto o ha expirado. Solicite un nuevo código.']]
            ], 422);
        }
        Cache::forget("clabe_otp_{$user->id}");

        // 3. Validate CLABE format and Modulo 10 checksum
        $clabe = trim($request->clabe);
        $validation = BanxicoClabeValidator::validate($clabe);

        if (!$validation['isValid']) {
            return response()->json([
                'errors' => [['code' => 'clabe-invalid', 'message' => $validation['errorMessage']]]
            ], 422);
        }

        // 4. Deduplication check via HMAC-SHA256
        $clabeHash = hash_hmac('sha256', $clabe, config('app.key'));
        $existing = CustomerBankAccount::where('user_id', $user->id)
            ->where('clabe_hash', $clabeHash)
            ->where('status', 'active')
            ->first();

        if ($existing) {
            return response()->json([
                'errors' => [['code' => 'clabe-exists', 'message' => 'Esta cuenta CLABE ya se encuentra registrada en su billetera.']]
            ], 422);
        }

        // 5. Store Selfie image for Biometric Facial Proof
        $selfieImageName = null;
        if ($request->hasFile('selfie')) {
            $selfieImageName = Helpers::upload('customer/bank_accounts/selfies/', 'png', $request->file('selfie'));
        }

        // 6. Save account with Immediate Activation (No 24h cooling off thanks to Selfie + Email OTP)
        $bankAccount = CustomerBankAccount::create([
            'user_id' => $user->id,
            'bank_code' => $validation['bankCode'],
            'bank_name' => $validation['bankName'],
            'account_holder' => trim($request->account_holder),
            'clabe_encrypted' => Crypt::encryptString($clabe),
            'clabe_last4' => substr($clabe, -4),
            'clabe_hash' => $clabeHash,
            'selfie_image' => $selfieImageName,
            'cooling_off_until' => null, // Immediate activation!
            'is_verified' => true,
            'email_verified_at' => now(),
            'status' => 'active',
        ]);

        $this->sendSecurityNotification(
            $user,
            'Cuenta Bancaria Activada Inmediatamente',
            "Tu cuenta bancaria ({$validation['bankName']}) ha sido vinculada y verificada exitosamente mediante validación de correo y registro facial de identidad. Ya puedes realizar retiros inmediatos.",
            [
                'Banco' => $validation['bankName'],
                'Titular' => trim($request->account_holder),
                'Cuenta' => '•••• ' . substr($clabe, -4),
                'Estatus' => 'Verificada y Activa para Retiros',
                'Fecha' => now()->format('d/m/Y H:i') . ' hrs',
                'Dirección IP' => $request->ip() ?? 'N/A',
            ],
            'bank_account_verified'
        );

        return response()->json([
            'message' => 'Cuenta bancaria verificada y activada exitosamente. Ya puedes realizar retiros a esta cuenta de forma inmediata.',
            'bank_account' => $bankAccount,
        ], 201);
    }

    /**
     * Delete/deactivate a bank account
     */
    public function deleteBankAccount(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'pin' => 'required|digits:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $user = $request->user();

        // Verify PIN
        $pinCheck = $this->verifyPinInternal($user, $request->pin);
        if (!$pinCheck['success']) {
            return response()->json([
                'errors' => [['code' => 'pin-invalid', 'message' => $pinCheck['message']]]
            ], 403);
        }

        $bankAccount = CustomerBankAccount::where('user_id', $user->id)
            ->where('id', $id)
            ->where('status', 'active')
            ->first();

        if (!$bankAccount) {
            return response()->json([
                'errors' => [['code' => 'not-found', 'message' => 'Cuenta bancaria no encontrada.']]
            ], 404);
        }

        // Check if there are active pending withdraw requests with this bank account
        $hasPending = CustomerWithdrawRequest::where('customer_bank_account_id', $bankAccount->id)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($hasPending) {
            return response()->json([
                'errors' => [['code' => 'account-in-use', 'message' => 'No es posible eliminar esta cuenta bancaria porque tiene solicitudes de retiro en proceso.']]
            ], 422);
        }

        $bankAccount->status = 'deleted';
        $bankAccount->save();

        return response()->json([
            'message' => 'Cuenta bancaria eliminada exitosamente.',
        ], 200);
    }

    /**
     * Request a bank withdrawal (SPEI)
     */
    public function requestWithdraw(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bank_account_id' => 'required|integer',
            'amount' => 'required|numeric|min:50',
            'pin' => 'required|digits:6',
            'idempotency_key' => 'nullable|string|max:64',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $user = $request->user();
        $amount = round((float) $request->amount, 2);
        $idempotencyKey = $request->header('X-Idempotency-Key') ?? $request->idempotency_key;

        // 1. Idempotency Check
        if (!empty($idempotencyKey)) {
            $existingRequest = CustomerWithdrawRequest::where('idempotency_key', $idempotencyKey)
                ->where('user_id', $user->id)
                ->first();

            if ($existingRequest) {
                return response()->json([
                    'message' => 'Solicitud de retiro procesada previamente.',
                    'withdraw_request' => $existingRequest->load('bankAccount'),
                    'remaining_balance' => (float) User::find($user->id)->wallet_balance,
                ], 200);
            }
        }

        // 2. Verify Wallet PIN
        $pinCheck = $this->verifyPinInternal($user, $request->pin);
        if (!$pinCheck['success']) {
            return response()->json([
                'errors' => [['code' => 'pin-invalid', 'message' => $pinCheck['message']]]
            ], 403);
        }

        // 3. Bank Account Validation
        $bankAccount = CustomerBankAccount::where('id', $request->bank_account_id)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if (!$bankAccount) {
            return response()->json([
                'errors' => [['code' => 'account-not-found', 'message' => 'La cuenta bancaria seleccionada no es válida o fue eliminada.']]
            ], 404);
        }

        // 4. Anti-Account-Takeover: 24h Cooling-Off Rule
        if ($bankAccount->is_in_cooling_off) {
            $hoursLeft = ceil($bankAccount->cooling_off_remaining_minutes / 60);
            return response()->json([
                'errors' => [[
                    'code' => 'cooling-off-active',
                    'message' => "Por seguridad bancaria de Tootli, las cuentas recién agregadas tienen un período de protección de 24 horas. Podrá retirar a esta cuenta en {$hoursLeft} hora(s)."
                ]]
            ], 422);
        }

        // 5. KYC Tier Limit Check
        $isVerified = ($user->identity_verified === 'approved');
        $tier = $isVerified ? 'verified' : 'unverified';
        $limits = self::LIMITS[$tier];

        if ($amount > $limits['per_transaction']) {
            $msg = $isVerified
                ? "El monto máximo por retiro es de $" . number_format($limits['per_transaction'], 2) . " MXN."
                : "El monto máximo sin verificar identidad es de $" . number_format($limits['per_transaction'], 2) . " MXN. Verifique su identidad en su perfil para ampliar su límite a $" . number_format(self::LIMITS['verified']['per_transaction'], 2) . " MXN.";

            return response()->json([
                'errors' => [['code' => 'limit-exceeded', 'message' => $msg]]
            ], 422);
        }

        $todayStart = now()->startOfDay();
        $dailyUsed = (float) CustomerWithdrawRequest::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'approved', 'transferred'])
            ->where('created_at', '>=', $todayStart)
            ->sum('amount');

        if (($dailyUsed + $amount) > $limits['daily']) {
            $availableDaily = max(0, $limits['daily'] - $dailyUsed);
            return response()->json([
                'errors' => [[
                    'code' => 'daily-limit-exceeded',
                    'message' => "Ha superado su límite diario de retiro ($" . number_format($limits['daily'], 2) . " MXN). Límite disponible para hoy: $" . number_format($availableDaily, 2) . " MXN."
                ]]
            ], 422);
        }

        $monthStart = now()->startOfMonth();
        $monthlyUsed = (float) CustomerWithdrawRequest::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'approved', 'transferred'])
            ->where('created_at', '>=', $monthStart)
            ->sum('amount');

        if (($monthlyUsed + $amount) > $limits['monthly']) {
            $availableMonthly = max(0, $limits['monthly'] - $monthlyUsed);
            return response()->json([
                'errors' => [[
                    'code' => 'monthly-limit-exceeded',
                    'message' => "Ha superado su límite mensual de retiro ($" . number_format($limits['monthly'], 2) . " MXN). Límite disponible este mes: $" . number_format($availableMonthly, 2) . " MXN."
                ]]
            ], 422);
        }

        // 6. Concurrency Protection & Atomic Balance Deduction
        $lockKey = "withdraw_idem_{$user->id}_" . ($idempotencyKey ?: uniqid('w_', true));
        $lock = Cache::lock($lockKey, 15);

        if (!$lock->get()) {
            return response()->json([
                'errors' => [['code' => 'request-in-progress', 'message' => 'Hay una transacción en curso. Por favor espere unos segundos.']]
            ], 429);
        }

        try {
            $withdrawRequest = DB::transaction(function () use ($user, $bankAccount, $amount, $request, $idempotencyKey) {
                // Pessimistic Lock on User Record
                $lockedUser = User::where('id', $user->id)->lockForUpdate()->first();

                if ($lockedUser->wallet_balance < $amount) {
                    throw new \Exception('INSUFFICIENT_FUNDS');
                }

                $balances = self::calculateWalletBalances($lockedUser->id, $lockedUser->wallet_balance);
                if ($amount > $balances['withdrawable_balance']) {
                    throw new \Exception('EXCEEDS_WITHDRAWABLE_BALANCE');
                }

                // Create ledger debit transaction
                $reference = "Retiro SPEI a {$bankAccount->bank_name} ({$bankAccount->clabe_last4})";
                $walletTx = CustomerLogic::create_wallet_transaction($lockedUser->id, $amount, 'bank_withdraw', $reference);

                if (!$walletTx) {
                    throw new \Exception('WALLET_TRANSACTION_FAILED');
                }

                // Audit metadata
                $auditData = [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'balance_before' => (float) $lockedUser->wallet_balance,
                    'balance_after' => (float) ($lockedUser->wallet_balance - $amount),
                    'bank_code' => $bankAccount->bank_code,
                    'bank_name' => $bankAccount->bank_name,
                    'clabe_last4' => $bankAccount->clabe_last4,
                    'account_holder' => $bankAccount->account_holder,
                    'requested_at' => now()->toIso8601String(),
                ];

                return CustomerWithdrawRequest::create([
                    'user_id' => $lockedUser->id,
                    'customer_bank_account_id' => $bankAccount->id,
                    'amount' => $amount,
                    'fee' => 0.00,
                    'net_amount' => $amount,
                    'status' => 'pending',
                    'idempotency_key' => $idempotencyKey,
                    'audit_metadata' => $auditData,
                ]);
            });

            // 7. Send Security Notification (Push + Email)
            $this->sendSecurityNotification(
                $user,
                'Solicitud de Retiro SPEI Recibida',
                "Se ha registrado una solicitud de retiro por $" . number_format($amount, 2) . " MXN hacia tu cuenta {$bankAccount->bank_name}.",
                [
                    'Monto Solicitado' => '$' . number_format($amount, 2) . ' MXN',
                    'Banco Destino' => $bankAccount->bank_name,
                    'Cuenta Destino' => '•••• ' . $bankAccount->clabe_last4,
                    'Titular' => $bankAccount->account_holder,
                    'Folio / ID' => '#' . $withdrawRequest->id,
                    'Estatus' => 'En proceso de dispersión',
                    'Fecha' => now()->format('d/m/Y H:i') . ' hrs',
                    'Dirección IP' => $request->ip() ?? 'N/A',
                ],
                'withdraw_requested'
            );

            return response()->json([
                'message' => 'Solicitud de retiro recibida con éxito. Nuestro equipo financiero procesará su transferencia SPEI.',
                'withdraw_request' => $withdrawRequest->load('bankAccount'),
                'remaining_balance' => (float) User::find($user->id)->wallet_balance,
            ], 201);

        } catch (\Exception $e) {
            if ($e->getMessage() === 'INSUFFICIENT_FUNDS') {
                return response()->json([
                    'errors' => [['code' => 'insufficient-funds', 'message' => 'Saldo insuficiente en su billetera para realizar este retiro.']]
                ], 422);
            }

            if ($e->getMessage() === 'EXCEEDS_WITHDRAWABLE_BALANCE') {
                return response()->json([
                    'errors' => [[
                        'code' => 'promotional-balance-not-withdrawable',
                        'message' => 'El monto supera su saldo retirable a cuenta bancaria. Los saldos promocionales, Cashback y bonos Tootli solo pueden ser utilizados para compras en la app o pagos con código QR en comercios.'
                    ]]
                ], 422);
            }

            info("Withdraw Request Error (User {$user->id}): " . $e->getMessage());
            return response()->json([
                'errors' => [['code' => 'system-error', 'message' => 'Ocurrió un error al procesar la solicitud de retiro. Intente más tarde.']]
            ], 500);
        } finally {
            $lock->release();
        }
    }

    /**
     * List user's withdrawal requests
     */
    public function getWithdrawRequests(Request $request)
    {
        $user = $request->user();
        $limit = $request->get('limit', 15);
        $offset = $request->get('offset', 1);

        $query = CustomerWithdrawRequest::with('bankAccount')
            ->where('user_id', $user->id)
            ->orderBy('id', 'desc');

        $totalSize = $query->count();
        $requests = $query->skip(($offset - 1) * $limit)->take($limit)->get();

        return response()->json([
            'total_size' => $totalSize,
            'limit' => (int) $limit,
            'offset' => (int) $offset,
            'withdraw_requests' => $requests,
        ], 200);
    }

    /**
     * Cancel a pending withdrawal request
     */
    public function cancelWithdrawRequest(Request $request, $id)
    {
        $user = $request->user();

        try {
            $result = DB::transaction(function () use ($user, $id) {
                $withdraw = CustomerWithdrawRequest::where('id', $id)
                    ->where('user_id', $user->id)
                    ->lockForUpdate()
                    ->first();

                if (!$withdraw) {
                    return ['status' => 404, 'error' => 'Solicitud de retiro no encontrada.'];
                }

                if ($withdraw->status !== 'pending') {
                    return ['status' => 422, 'error' => 'Solo es posible cancelar retiros en estado pendiente.'];
                }

                // Mark cancelled
                $withdraw->status = 'cancelled';
                $withdraw->rejection_reason = 'Cancelado por el usuario.';
                $withdraw->save();

                // Refund to wallet balance
                $refund = CustomerLogic::create_wallet_transaction(
                    $user->id,
                    $withdraw->amount,
                    'withdraw_rejected_refund',
                    "Reembolso por cancelación de retiro SPEI #{$withdraw->id}"
                );

                if (!$refund) {
                    throw new \Exception('REFUND_FAILED');
                }

                return ['status' => 200, 'withdraw' => $withdraw];
            });

            if ($result['status'] !== 200) {
                return response()->json([
                    'errors' => [['code' => 'cancel-error', 'message' => $result['error']]]
                ], $result['status']);
            }

            return response()->json([
                'message' => 'Solicitud de retiro cancelada y saldo reembolsado a su billetera.',
                'withdraw_request' => $result['withdraw'],
                'current_balance' => (float) User::find($user->id)->wallet_balance,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'errors' => [['code' => 'cancel-error', 'message' => 'Error al cancelar la solicitud. Intente más tarde.']]
            ], 500);
        }
    }

    /**
     * Internal PIN verification with anti-brute-force rate limiting
     *
     * @param User $user
     * @param string $pin
     * @return array{success: bool, message: ?string}
     */
    private function verifyPinInternal(User $user, string $pin): array
    {
        // 1. Check if user has set a PIN
        if (empty($user->wallet_pin)) {
            return [
                'success' => false,
                'message' => 'Debe configurar un PIN de seguridad en su billetera antes de realizar esta operación.'
            ];
        }

        // 2. Check if wallet is currently locked
        if ($user->wallet_locked_until && $user->wallet_locked_until->isFuture()) {
            $minutesLeft = (int) now()->diffInMinutes($user->wallet_locked_until, false);
            return [
                'success' => false,
                'message' => "Billetera bloqueada por seguridad debido a múltiples intentos incorrectos. Intente de nuevo en {$minutesLeft} minuto(s)."
            ];
        }

        // 3. Verify PIN Hash
        if (!Hash::check($pin, $user->wallet_pin)) {
            $user->increment('failed_pin_attempts');

            if ($user->failed_pin_attempts >= 5) {
                $user->wallet_locked_until = now()->addMinutes(30);
                $user->save();

                return [
                    'success' => false,
                    'message' => 'Ha superado el número máximo de intentos permitidos (5). Su billetera ha sido bloqueada temporalmente por 30 minutos.'
                ];
            }

            $user->save();
            $remaining = 5 - $user->failed_pin_attempts;

            return [
                'success' => false,
                'message' => "PIN de billetera incorrecto. Le quedan {$remaining} intento(s) antes del bloqueo de seguridad."
            ];
        }

        // 4. On successful verification, reset failed attempts
        if ($user->failed_pin_attempts > 0 || $user->wallet_locked_until) {
            $user->failed_pin_attempts = 0;
            $user->wallet_locked_until = null;
            $user->save();
        }

        return ['success' => true, 'message' => null];
    }

    /**
     * Calculate dual balance: Withdrawable Cash (SPEI) vs Promotional/Cashback Balance (In-App/QR)
     *
     * Rule: Promotional credits (CashBack, loyalty points converted, referral bonuses, admin deposit bonuses)
     * are consumed FIRST by in-app orders, trips, and QR payments (FIFO / Priority Consumption).
     *
     * @param int $userId
     * @param float $currentWalletBalance
     * @return array{total_balance: float, withdrawable_balance: float, promotional_balance: float}
     */
    public static function calculateWalletBalances($userId, $currentWalletBalance): array
    {
        $currentWalletBalance = (float) $currentWalletBalance;

        // Total promotional credits ever received
        $promoCredits = (float) (DB::table('wallet_transactions')
            ->where('user_id', $userId)
            ->where(function ($q) {
                $q->whereIn('transaction_type', ['CashBack', 'loyalty_point', 'referrer'])
                  ->orWhere('admin_bonus', '>', 0);
            })
            ->selectRaw('SUM(CASE WHEN transaction_type IN ("CashBack", "loyalty_point", "referrer") THEN credit ELSE 0 END + admin_bonus) as total_promo')
            ->value('total_promo') ?? 0.0);

        // Total debits (money spent by customer in app/QR)
        $totalDebits = (float) (DB::table('wallet_transactions')
            ->where('user_id', $userId)
            ->where('debit', '>', 0)
            ->sum('debit') ?? 0.0);

        // Remaining promotional balance (cannot exceed current balance)
        $remainingPromo = max(0.0, $promoCredits - $totalDebits);
        $promotionalBalance = min($currentWalletBalance, $remainingPromo);

        // Liquid Withdrawable Balance (real fiat from sales, services, refunds)
        $withdrawableBalance = max(0.0, $currentWalletBalance - $promotionalBalance);

        return [
            'total_balance' => $currentWalletBalance,
            'withdrawable_balance' => round($withdrawableBalance, 2),
            'promotional_balance' => round($promotionalBalance, 2),
        ];
    }

    /**
     * Dispatch out-of-band security alerts (Push Notification & Email)
     */
    private function sendSecurityNotification(User $user, string $title, string $message, array $details = [], string $eventType = 'general'): void
    {
        // 1. Firebase Push Notification
        try {
            if (!empty($user->cm_firebase_token)) {
                $pushData = [
                    'title' => '🛡️ ' . $title,
                    'description' => $message,
                    'order_id' => '',
                    'image' => '',
                    'type' => 'wallet_security',
                ];
                Helpers::send_push_notif_to_device($user->cm_firebase_token, $pushData);
            }
        } catch (\Throwable $e) {
            info("Wallet Security Push Error (User {$user->id}): " . $e->getMessage());
        }

        // 2. Transactional Security Email
        try {
            if (!empty($user->email)) {
                $userName = $user->f_name ?? 'Usuario Tootli';
                Mail::to($user->email)->send(
                    new CustomerWalletSecurityAlertMail($userName, $title, $message, $details, $eventType)
                );
            }
        } catch (\Throwable $e) {
            info("Wallet Security Mail Error (User {$user->id}): " . $e->getMessage());
        }
    }
}
