<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserSavedCard;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use MercadoPago\Client\Customer\CustomerCardClient;
use MercadoPago\Client\Common\RequestOptions;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\MercadoPagoConfig;

class MercadoPagoCardService
{
    private string $accessToken;
    private string $publicKey;

    public function __construct()
    {
        // Las credenciales se leen desde .env
        $this->accessToken = config('services.mercadopago.access_token');
        $this->publicKey   = config('services.mercadopago.public_key');

        MercadoPagoConfig::setAccessToken($this->accessToken);
    }

    // -------------------------------------------------------------------------
    // Public Key
    // -------------------------------------------------------------------------

    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    // -------------------------------------------------------------------------
    // Customer Management (México – sin identification obligatorio)
    // -------------------------------------------------------------------------

    /**
     * Busca o crea un Customer en MercadoPago para el usuario dado.
     * Guarda el mp_customer_id en la tabla correspondiente (buscamos en saved cards).
     */
    public function getOrCreateMpCustomer(User $user): string
    {
        // 1. ¿Ya tenemos el customer_id guardado de alguna tarjeta anterior?
        $existing = UserSavedCard::where('user_id', $user->id)->first();
        if ($existing) {
            // Verificar que el customer aún existe en MP antes de reutilizarlo
            $verifyResponse = Http::withToken($this->accessToken)
                ->get("https://api.mercadopago.com/v1/customers/{$existing->mp_customer_id}");

            if ($verifyResponse->successful()) {
                return $existing->mp_customer_id;
            }

            // El customer ya no existe en MP (limpieza de sandbox u otro motivo).
            // Eliminamos las tarjetas guardadas del usuario para forzar recreación.
            Log::warning('[MercadoPago] Customer no encontrado en MP, eliminando tarjetas y recreando', [
                'user_id'        => $user->id,
                'mp_customer_id' => $existing->mp_customer_id,
            ]);
            UserSavedCard::where('user_id', $user->id)->delete();
        }

        // 2. Buscar en MP por email (puede existir en MP aunque no esté en nuestra DB)
        $searchResponse = Http::withToken($this->accessToken)
            ->get('https://api.mercadopago.com/v1/customers/search', [
                'email' => $user->email,
            ]);

        if ($searchResponse->successful()) {
            $results = $searchResponse->json('results', []);
            foreach ($results as $result) {
                // Verificar que el customer encontrado realmente sea accesible
                $verifyFound = Http::withToken($this->accessToken)
                    ->get("https://api.mercadopago.com/v1/customers/{$result['id']}");
                if ($verifyFound->successful()) {
                    Log::info('[MercadoPago] Customer encontrado y verificado por search', [
                        'email'       => $user->email,
                        'customer_id' => $result['id'],
                    ]);
                    return $result['id'];
                }
            }
        }

        // 3. Crear nuevo Customer en MP
        $createResponse = Http::withToken($this->accessToken)
            ->post('https://api.mercadopago.com/v1/customers', [
                'email'      => $user->email,
                'first_name' => $user->f_name ?? '',
                'last_name'  => $user->l_name  ?? '',
            ]);

        if (!$createResponse->successful()) {
            Log::error('[MercadoPago] Error creando customer', [
                'user_id'  => $user->id,
                'response' => $createResponse->json(),
            ]);
            throw new Exception('No se pudo crear el customer en MercadoPago: ' . $createResponse->body());
        }

        $newCustomerId = $createResponse->json('id');
        Log::info('[MercadoPago] Nuevo customer creado', [
            'user_id'     => $user->id,
            'customer_id' => $newCustomerId,
        ]);

        return $newCustomerId;
    }

    // -------------------------------------------------------------------------
    // Card Management
    // -------------------------------------------------------------------------

    /**
     * Guarda una tarjeta en MercadoPago y la persiste en la DB.
     *
     * @param  User   $user      Usuario Tootli
     * @param  string $cardToken Token generado por el SDK de MP en Flutter
     * @return UserSavedCard
     * @throws Exception
     */
    public function saveCard(User $user, string $cardToken): UserSavedCard
    {
        $customerId = $this->getOrCreateMpCustomer($user);

        $client = new CustomerCardClient();

        try {
            $requestOptions = new RequestOptions();
            $requestOptions->setCustomHeaders([
                'x-idempotency-key' => uniqid('save_card_', true),
            ]);

            $card = $client->create($customerId, ['token' => $cardToken], $requestOptions);
        } catch (Exception $e) {
            Log::error('[MercadoPago] Error guardando tarjeta', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);
            throw new Exception('No se pudo guardar la tarjeta: ' . $e->getMessage());
        }

        // Si es la primera tarjeta del usuario, marcamos como predeterminada
        $isFirst = !UserSavedCard::where('user_id', $user->id)->exists();

        $savedCard = UserSavedCard::create([
            'user_id'          => $user->id,
            'mp_customer_id'   => $customerId,
            'mp_card_id'       => (string)$card->id,
            'last_four_digits' => (string)$card->last_four_digits,
            'payment_method_id'=> $card->payment_method->id ?? ($card->payment_method_id ?? ''),
            'payment_type_id'  => $card->payment_method->payment_type_id ?? ($card->payment_type_id ?? null),
            'cardholder_name'  => $card->cardholder->name ?? null,
            'expiration_month' => $card->expiration_month,
            'expiration_year'  => $card->expiration_year,
            'is_default'       => $isFirst,
        ]);

        return $savedCard;
    }

    /**
     * Elimina una tarjeta de MercadoPago y de la DB.
     */
    public function deleteCard(UserSavedCard $savedCard): void
    {
        $client = new CustomerCardClient();

        try {
            $client->delete($savedCard->mp_customer_id, $savedCard->mp_card_id);
        } catch (Exception $e) {
            // Si no existe en MP, igual eliminamos de la DB
            Log::warning('[MercadoPago] Tarjeta ya no existe en MP al eliminar', [
                'mp_card_id' => $savedCard->mp_card_id,
                'error'      => $e->getMessage(),
            ]);
        }

        // Si era predeterminada, asignar la siguiente disponible
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
    // Token de un solo uso para pago con tarjeta guardada
    // -------------------------------------------------------------------------

    /**
     * Crea un token de un solo uso para una tarjeta guardada del customer.
     * Se hace server-side con el ACCESS_TOKEN para garantizar que el token
     * y el cobro usen las mismas credenciales y MP no devuelva "Card not found".
     *
     * @param  UserSavedCard $savedCard
     * @param  string        $securityCode  CVV ingresado por el usuario
     * @return string  Token de un solo uso listo para enviar al pago
     * @throws Exception
     */
    public function createCardToken(UserSavedCard $savedCard, string $securityCode): string
    {
        $response = Http::withToken($this->accessToken)
            ->post('https://api.mercadopago.com/v1/card_tokens', [
                'card_id'       => $savedCard->mp_card_id,
                'security_code' => $securityCode,
            ]);

        if (!$response->successful()) {
            Log::error('[MercadoPago] Error creando token de tarjeta guardada', [
                'mp_card_id' => $savedCard->mp_card_id,
                'status'     => $response->status(),
                'body'       => $response->json(),
            ]);
            $msg = $response->json('message') ?? 'Error al tokenizar la tarjeta';
            throw new Exception($msg);
        }

        $tokenId = $response->json('id');
        if (!$tokenId) {
            throw new Exception('MercadoPago no devolvió un token válido');
        }

        return $tokenId;
    }

    // -------------------------------------------------------------------------
    // Payment
    // -------------------------------------------------------------------------

    /**
     * Procesa un pago con tarjeta guardada.
     * El $cardToken es un token de UN SOLO USO generado en Flutter con el CVV
     * del usuario para re-autenticar el pago (PCI-DSS compliant).
     *
     * @param  UserSavedCard $savedCard
     * @param  string        $cardToken  Token re-generado en Flutter con CVV
     * @param  float         $amount     Monto a cobrar (en pesos MXN)
     * @param  string        $email      Email del pagador
     * @param  string        $externalRef Referencia externa (order ID / payment_request ID)
     * @return array  ['status' => 'approved'|'rejected'|'pending', 'payment_id' => int]
     * @throws Exception
     */
    public function chargeWithSavedCard(
        UserSavedCard $savedCard,
        string $cardToken,
        float $amount,
        string $email,
        string $externalRef
    ): array {
        $client = new PaymentClient();

        $requestOptions = new RequestOptions();
        $requestOptions->setCustomHeaders([
            'x-idempotency-key' => uniqid('pay_', true),
        ]);

        try {
            Log::info('[MercadoPago] Intentando cobro con tarjeta guardada', [
                'saved_card_db_id'  => $savedCard->id,
                'mp_card_id'        => $savedCard->mp_card_id,
                'mp_customer_id'    => $savedCard->mp_customer_id,
                'card_token'        => $cardToken,
                'payment_method_id' => $savedCard->payment_method_id,
                'transaction_amount'=> (float) $amount,
                'payer_email'       => $email,
            ]);

            // Para cobros con tarjeta guardada en MercadoPago, se requiere:
            // - payer.type = 'customer' (indica que es un cliente registrado en MP)
            // - payer.id = mp_customer_id del cliente
            // El token (generado con card_id) ya está vinculado al customer en MP.
            $payment = $client->create([
                'token'              => $cardToken,
                'description'        => 'Pago de pedido Tootli Order #' . $externalRef,
                'transaction_amount' => (float) $amount,
                'installments'       => 1,
                'payment_method_id'  => $savedCard->payment_method_id,
                'external_reference' => $externalRef,
                'payer'              => [
                    'email' => $email,
                ],
            ], $requestOptions);
        } catch (\MercadoPago\Exceptions\MPApiException $e) {
            $content = $e->getApiResponse() ? $e->getApiResponse()->getContent() : $e->getMessage();
            Log::error('[MercadoPago] Error al cobrar API', [
                'card_id' => $savedCard->id,
                'response' => $content,
            ]);

            // Si el customer ya no existe en MP, eliminar la tarjeta de la DB
            // para que el usuario la vuelva a agregar con un customer válido.
            $causes = $content['cause'] ?? [];
            foreach ($causes as $cause) {
                if (($cause['code'] ?? null) === 2002) {
                    Log::warning('[MercadoPago] Customer inválido detectado al cobrar, eliminando tarjeta de DB', [
                        'card_id'        => $savedCard->id,
                        'mp_customer_id' => $savedCard->mp_customer_id,
                    ]);
                    UserSavedCard::where('user_id', $savedCard->user_id)->delete();
                    throw new Exception('Tu tarjeta guardada ha expirado. Por favor agrégala de nuevo.');
                }
            }

            throw new Exception('Error MPApiException: ' . json_encode($content));
        } catch (Exception $e) {
            Log::error('[MercadoPago] Error al cobrar', [
                'card_id' => $savedCard->id,
                'error'   => $e->getMessage(),
            ]);
            throw new Exception('Error al procesar el pago: ' . $e->getMessage());
        }

        return [
            'status'        => $payment->status,            // approved / rejected / pending / in_process
            'status_detail' => $payment->status_detail,
            'payment_id'    => $payment->id,
        ];
    }
    /**
     * Obtiene la lista REAL de tarjetas de un cliente directamente de MercadoPago (para debug).
     */
    public function getCustomerCards(string $customerId): array
    {
        $client         = new \MercadoPago\Client\Customer\CustomerCardClient();
        $requestOptions = new RequestOptions();
        $requestOptions->setAccessToken($this->accessToken);

        try {
            $cards = $client->list($customerId, $requestOptions);
            $result = [];
            foreach ($cards->data as $card) {
                $result[] = [
                    'id'                => $card->id,
                    'payment_method'    => $card->payment_method->id,
                    'last_four_digits'  => $card->last_four_digits,
                    'customer_id'       => $card->customer_id,
                ];
            }
            return $result;
        } catch (Exception $e) {
            Log::error('[MercadoPago] Error consultando tarjetas para debug', ['customer_id' => $customerId, 'error' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }
}
