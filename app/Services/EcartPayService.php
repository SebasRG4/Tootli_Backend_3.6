<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserSavedCard;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EcartPayService
{
    private string $publicKey;
    private string $privateKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->publicKey  = config('services.ecartpay.public_key');
        $this->privateKey = config('services.ecartpay.private_key');
        $this->baseUrl    = rtrim(config('services.ecartpay.base_url', 'https://sandbox.ecartpay.com'), '/');
    }

    // -------------------------------------------------------------------------
    // Auth – JWT token (1h TTL, cached 55 min)
    // -------------------------------------------------------------------------

    public function getAuthToken(): string
    {
        return Cache::remember('ecartpay_auth_token', 55 * 60, function () {
            $credentials = base64_encode($this->publicKey . ':' . $this->privateKey);

            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . $credentials,
                'Accept'        => 'application/json',
            ])->post($this->baseUrl . '/api/authorizations/token');

            if (!$response->successful()) {
                Log::error('[EcartPay] Error obteniendo auth token', [
                    'status' => $response->status(),
                    'body'   => $response->json(),
                ]);
                throw new Exception('No se pudo obtener el token de EcartPay');
            }

            $token = $response->json('token');
            if (!$token) {
                throw new Exception('EcartPay no devolvió un token válido');
            }

            Log::info('[EcartPay] Auth token obtenido');
            return $token;
        });
    }

    private function api(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::withHeaders([
            'Authorization' => $this->getAuthToken(),
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
        ])->baseUrl($this->baseUrl);
    }

    /**
     * Invalida el token cacheado y reintenta la operación si EcartPay
     * devuelve 401 (token expirado antes de los 55 min de cache).
     */
    private function apiWithRetry(callable $operation)
    {
        try {
            return $operation();
        } catch (Exception $e) {
            if (str_contains($e->getMessage(), '401') || str_contains($e->getMessage(), 'Unauthorized')) {
                Cache::forget('ecartpay_auth_token');
                return $operation();
            }
            throw $e;
        }
    }

    // -------------------------------------------------------------------------
    // Customer Management
    // -------------------------------------------------------------------------

    public function getOrCreateCustomer(User $user): string
    {
        $existing = UserSavedCard::where('user_id', $user->id)
            ->whereNotNull('ecartpay_customer_id')
            ->first();
        if ($existing) {
            return $existing->ecartpay_customer_id;
        }

        return $this->apiWithRetry(function () use ($user) {
            $response = $this->api()->post('/api/customers', [
                'phone'      => $user->phone ?? '',
                'first_name' => $user->f_name ?? '',
                'last_name'  => $user->l_name ?? '',
                'user_id'    => (string) $user->id,
                'email'      => $user->email ?? '',
            ]);

            if ($response->status() === 409) {
                Log::info('[EcartPay] Customer ya existe, buscando por user_id', ['user_id' => $user->id]);
                return $this->findExistingCustomer($user);
            }

            if (!$response->successful()) {
                Log::error('[EcartPay] Error creando customer', [
                    'user_id'  => $user->id,
                    'status'   => $response->status(),
                    'response' => $response->json(),
                ]);
                throw new Exception('No se pudo crear el customer en EcartPay: ' . $response->body());
            }

            $customerId = $response->json('id');
            Log::info('[EcartPay] Customer creado', [
                'user_id'     => $user->id,
                'customer_id' => $customerId,
            ]);

            return $customerId;
        });
    }

    private function findExistingCustomer(User $user): string
    {
        $response = $this->api()->get('/api/customers', [
            'user_id' => (string) $user->id,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $docs = $data['docs'] ?? [];

            if (!empty($docs) && isset($docs[0]['id'])) {
                $customerId = $docs[0]['id'];
                Log::info('[EcartPay] Customer existente encontrado', [
                    'user_id'     => $user->id,
                    'customer_id' => $customerId,
                ]);
                return (string) $customerId;
            }
        }

        Log::error('[EcartPay] No se pudo encontrar el customer existente', [
            'user_id'  => $user->id,
            'status'   => $response->status(),
            'response' => $response->json(),
        ]);
        throw new Exception('El customer ya existe en EcartPay pero no se pudo recuperar su ID');
    }

    // -------------------------------------------------------------------------
    // Card Management
    // -------------------------------------------------------------------------

    public function saveCard(User $user, array $cardData): UserSavedCard
    {
        $customerId = $this->getOrCreateCustomer($user);

        return $this->apiWithRetry(function () use ($user, $customerId, $cardData) {
            $response = $this->api()->post("/api/customers/{$customerId}/cards", [
                'name'      => $cardData['name'],
                'number'    => $cardData['number'],
                'exp_month' => $cardData['exp_month'],
                'exp_year'  => $cardData['exp_year'],
                'cvc'       => $cardData['cvc'],
            ]);

            if (!$response->successful()) {
                Log::error('[EcartPay] Error guardando tarjeta', [
                    'user_id'  => $user->id,
                    'status'   => $response->status(),
                    'response' => $response->json(),
                ]);
                throw new Exception($this->translateCardError(
                    $response->json('code'),
                    $response->json('message'),
                    'guardar'
                ));
            }

            $data = $response->json();
            Log::info('[EcartPay] Tarjeta guardada', [
                'user_id' => $user->id,
                'card_id' => $data['id'] ?? null,
            ]);

            $isFirst = !UserSavedCard::where('user_id', $user->id)->exists();

            $last4 = $data['last4'] ?? substr($cardData['number'], -4);
            $brand = $data['brand'] ?? $this->detectBrand($cardData['number']);

            return UserSavedCard::create([
                'user_id'              => $user->id,
                'ecartpay_customer_id' => $customerId,
                'ecartpay_card_id'     => (string) $data['id'],
                'last_four_digits'     => $last4,
                'payment_method_id'    => $brand,
                'payment_type_id'      => $data['type'] ?? 'credit_card',
                'cardholder_name'      => $cardData['name'],
                'expiration_month'     => (int) $cardData['exp_month'],
                'expiration_year'      => (int) $this->normalizeYear($cardData['exp_year']),
                'is_default'           => $isFirst,
            ]);
        });
    }

    public function deleteCard(UserSavedCard $savedCard): void
    {
        $this->apiWithRetry(function () use ($savedCard) {
            $cid = $savedCard->ecartpay_customer_id;
            $cardId = $savedCard->ecartpay_card_id;

            $response = $this->api()->delete("/api/customers/{$cid}/cards/{$cardId}");

            if (!$response->successful() && $response->status() !== 404) {
                Log::warning('[EcartPay] Error eliminando tarjeta en EcartPay', [
                    'ecartpay_card_id' => $cardId,
                    'status'           => $response->status(),
                    'body'             => $response->json(),
                ]);
            }
        });

        if ($savedCard->is_default) {
            $next = UserSavedCard::where('user_id', $savedCard->user_id)
                ->where('id', '!=', $savedCard->id)
                ->first();
            if ($next) {
                $next->update(['is_default' => true]);
            }
        }

        $savedCard->delete();
    }

    // -------------------------------------------------------------------------
    // Tokenization (para cobro con tarjeta guardada)
    // -------------------------------------------------------------------------

    public function createCardToken(UserSavedCard $savedCard, ?string $cvc = null): string
    {
        return $this->apiWithRetry(function () use ($savedCard, $cvc) {
            $body = ['id' => $savedCard->ecartpay_card_id];
            if (!empty($cvc)) {
                $body['cvc'] = $cvc;
            }

            $response = $this->api()->post('/api/tokens', $body);

            if (!$response->successful()) {
                Log::error('[EcartPay] Error creando token de tarjeta', [
                    'ecartpay_card_id' => $savedCard->ecartpay_card_id,
                    'status'           => $response->status(),
                    'body'             => $response->json(),
                ]);
                throw new Exception($this->translateCardError(
                    $response->json('code'),
                    $response->json('message'),
                    'tokenizar'
                ));
            }

            $token = $response->json('token');
            if (!$token) {
                throw new Exception('EcartPay no devolvió un token válido');
            }

            Log::info('[EcartPay] Token creado para tarjeta guardada', [
                'token'             => $token,
                'ecartpay_card_id'  => $savedCard->ecartpay_card_id,
            ]);

            return $token;
        });
    }

    // -------------------------------------------------------------------------
    // Payment via Orders
    // -------------------------------------------------------------------------

    public function chargeWithSavedCard(
        UserSavedCard $savedCard,
        string $token,
        float $amount,
        string $orderDescription,
        string $externalRef,
        string $notifyUrl = ''
    ): array {
        return $this->apiWithRetry(function () use ($savedCard, $token, $amount, $orderDescription, $externalRef, $notifyUrl) {
            Log::info('[EcartPay] Intentando cobro con tarjeta guardada', [
                'saved_card_db_id'     => $savedCard->id,
                'ecartpay_card_id'     => $savedCard->ecartpay_card_id,
                'ecartpay_customer_id' => $savedCard->ecartpay_customer_id,
                'token'                => $token,
                'amount'               => $amount,
            ]);

            $response = $this->api()->post('/api/orders', [
                'customer_id' => $savedCard->ecartpay_customer_id,
                'currency'    => 'MXN',
                'items'       => [
                    [
                        'name'       => $orderDescription,
                        'quantity'   => 1,
                        'price'      => $amount,
                        'is_service' => true,
                    ],
                ],
                'token'      => $token,
                'notify_url' => $notifyUrl ?: (config('app.url') . '/api/v1/ecartpay/webhook'),
                'send_email' => false,
            ]);

            if (!$response->successful()) {
                $code = $response->json('code');
                $message = $response->json('message');

                Log::error('[EcartPay] Error al crear orden/cobrar', [
                    'card_id'  => $savedCard->id,
                    'status'   => $response->status(),
                    'code'     => $code,
                    'response' => $response->json(),
                ]);

                throw new Exception($this->translatePaymentError($code, $message));
            }

            $data = $response->json();
            $status = $data['status'] ?? 'created';

            Log::info('[EcartPay] Orden creada', [
                'order_id'     => $data['id'] ?? null,
                'order_number' => $data['number'] ?? null,
                'status'       => $status,
            ]);

            if ($status === 'declined' || $status === 'failed') {
                $declineReason = $data['decline_reason'] ?? $data['activity'][0]['decline_reason'] ?? null;
                Log::warning('[EcartPay] Pago rechazado', [
                    'order_id'       => $data['id'] ?? null,
                    'status'         => $status,
                    'decline_reason' => $declineReason,
                ]);
                throw new Exception($this->translateDeclineReason($declineReason, $status));
            }

            $isPaid = $status === 'paid'
                || !empty($data['payments'])
                || ($status === 'created' && isset($data['activity'][0]['status']) && $data['activity'][0]['status'] === 'paid');

            return [
                'status'        => $isPaid ? 'approved' : ($status === 'created' ? 'pending' : 'rejected'),
                'status_detail' => $status,
                'payment_id'    => $data['id'] ?? $data['number'] ?? null,
            ];
        });
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function detectBrand(string $number): string
    {
        $first = substr($number, 0, 1);
        $firstTwo = substr($number, 0, 2);

        return match (true) {
            $first === '4'                         => 'visa',
            in_array($firstTwo, ['51','52','53','54','55']) => 'mastercard',
            in_array($firstTwo, ['34','37'])        => 'amex',
            default                                 => 'card',
        };
    }

    private function normalizeYear(string $year): int
    {
        $y = (int) $year;
        return $y < 100 ? 2000 + $y : $y;
    }

    private function translateCardError(?int $code, ?string $apiMessage, string $action): string
    {
        $mapped = match ($code) {
            200     => 'El número de tarjeta no es válido. Verifica e intenta de nuevo.',
            201     => 'La fecha de vencimiento no es válida.',
            202     => 'El código de seguridad (CVV) no es válido.',
            210     => 'Esta tarjeta no es válida para el entorno actual.',
            301     => 'La tarjeta está vencida. Usa otra tarjeta.',
            302     => 'Tarjeta reportada como robada. Contacta a tu banco.',
            303     => 'Tarjeta restringida. Contacta a tu banco.',
            default => null,
        };

        return $mapped ?? ('No se pudo ' . $action . ' la tarjeta: ' . ($apiMessage ?? 'Error desconocido'));
    }

    private function translatePaymentError(?int $code, ?string $apiMessage): string
    {
        $mapped = match ($code) {
            200     => 'El número de tarjeta no es válido.',
            201     => 'La fecha de vencimiento de la tarjeta no es válida.',
            202     => 'El código de seguridad (CVV) es incorrecto.',
            210     => 'Esta tarjeta no se puede procesar en este momento. Intenta con otra.',
            301     => 'Tu tarjeta está vencida. Por favor usa otra tarjeta.',
            302     => 'Tu tarjeta fue reportada. Contacta a tu banco.',
            303     => 'Tu tarjeta tiene restricciones. Contacta a tu banco.',
            401     => 'Fondos insuficientes. Verifica tu saldo e intenta de nuevo.',
            402     => 'El monto excede el límite de tu tarjeta.',
            501     => 'Tu tarjeta fue rechazada por el banco. Contacta a tu banco para más información.',
            502     => 'Error de comunicación con el banco. Intenta de nuevo en unos minutos.',
            default => null,
        };

        return $mapped ?? ('Error al procesar el pago: ' . ($apiMessage ?? 'Error desconocido. Intenta de nuevo.'));
    }

    private function translateDeclineReason(?string $reason, string $status): string
    {
        if (!$reason) {
            return $status === 'declined'
                ? 'Tu pago fue rechazado. Verifica los datos de tu tarjeta o intenta con otra.'
                : 'No se pudo procesar el pago. Intenta de nuevo.';
        }

        $reason = strtolower($reason);

        return match (true) {
            str_contains($reason, 'insufficient') => 'Fondos insuficientes. Verifica tu saldo e intenta de nuevo.',
            str_contains($reason, 'expired')      => 'Tu tarjeta está vencida. Usa otra tarjeta.',
            str_contains($reason, 'stolen')       => 'Tarjeta reportada. Contacta a tu banco.',
            str_contains($reason, 'restricted')   => 'Tu tarjeta tiene restricciones. Contacta a tu banco.',
            str_contains($reason, 'limit')        => 'Excediste el límite de tu tarjeta. Contacta a tu banco.',
            str_contains($reason, 'cvv') || str_contains($reason, 'cvc') || str_contains($reason, 'security')
                                                   => 'El código de seguridad (CVV) es incorrecto.',
            str_contains($reason, 'fraud')        => 'Tu banco rechazó la transacción por seguridad. Contacta a tu banco.',
            str_contains($reason, 'do not honor') => 'Tu banco rechazó la transacción. Contacta a tu banco para más información.',
            default                                => 'Pago rechazado: ' . $reason . '. Intenta con otra tarjeta.',
        };
    }
}
