<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\CustomerLogic;
use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\CustomerBankAccount;
use App\Models\CustomerWithdrawRequest;
use App\Models\User;
use App\Services\Fintech\BanxicoClabeValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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
                'daily_available' => max(0, $limits['daily'] - $dailyUsed),
                'monthly_available' => max(0, $limits['monthly'] - $monthlyUsed),
            ],
            'cooling_off_period_hours' => 24,
            'wallet_balance' => (float) $user->wallet_balance,
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
     * Add a bank account (validated against Banxico CLABE standard)
     */
    public function addBankAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'clabe' => 'required|string',
            'account_holder' => 'required|string|min:3|max:150',
            'pin' => 'required|digits:6',
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

        // 2. Validate CLABE format and Modulo 10 checksum
        $clabe = trim($request->clabe);
        $validation = BanxicoClabeValidator::validate($clabe);

        if (!$validation['isValid']) {
            return response()->json([
                'errors' => [['code' => 'clabe-invalid', 'message' => $validation['errorMessage']]]
            ], 422);
        }

        // 3. Deduplication check via HMAC-SHA256
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

        // 4. Save account with AES-256 encryption and 24h cooling-off period
        $bankAccount = CustomerBankAccount::create([
            'user_id' => $user->id,
            'bank_code' => $validation['bankCode'],
            'bank_name' => $validation['bankName'],
            'account_holder' => trim($request->account_holder),
            'clabe_encrypted' => Crypt::encryptString($clabe),
            'clabe_last4' => substr($clabe, -4),
            'clabe_hash' => $clabeHash,
            'cooling_off_until' => now()->addHours(24),
            'is_verified' => true,
            'status' => 'active',
        ]);

        return response()->json([
            'message' => 'Cuenta bancaria registrada exitosamente. Por seguridad bancaria antifraude, entrará en período de protección de 24 horas antes de permitir retiros.',
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

        // 1. Idempotency Check
        if ($request->filled('idempotency_key')) {
            $existingRequest = CustomerWithdrawRequest::where('idempotency_key', $request->idempotency_key)
                ->where('user_id', $user->id)
                ->first();

            if ($existingRequest) {
                return response()->json([
                    'message' => 'Solicitud de retiro procesada previamente.',
                    'withdraw_request' => $existingRequest,
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
        try {
            $withdrawRequest = DB::transaction(function () use ($user, $bankAccount, $amount, $request) {
                // Pessimistic Lock on User Record
                $lockedUser = User::where('id', $user->id)->lockForUpdate()->first();

                if ($lockedUser->wallet_balance < $amount) {
                    throw new \Exception('INSUFFICIENT_FUNDS');
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
                    'idempotency_key' => $request->idempotency_key,
                    'audit_metadata' => $auditData,
                ]);
            });

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

            return response()->json([
                'errors' => [['code' => 'system-error', 'message' => 'Ocurrió un error al procesar la solicitud de retiro. Intente más tarde.']]
            ], 500);
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
}
