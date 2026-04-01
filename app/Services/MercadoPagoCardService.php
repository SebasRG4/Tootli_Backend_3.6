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
            return $existing->mp_customer_id;
        }

        // 2. Buscar en MP por email
        $searchResponse = Http::withToken($this->accessToken)
            ->get('https://api.mercadopago.com/v1/customers/search', [
                'email' => $user->email,
            ]);

        if ($searchResponse->successful()) {
            $results = $searchResponse->json('results', []);
            if (count($results) > 0) {
                return $results[0]['id'];
            }
        }

        // 3. Crear nuevo Customer en MP
        $createResponse = Http::withToken($this->accessToken)
            ->post('https://api.mercadopago.com/v1/customers', [
                'email'      => $user->email,
                'first_name' => $user->f_name ?? '',
                'last_name'  => $user->l_name  ?? '',
                // Para México, identification NO es obligatorio
            ]);

        if (!$createResponse->successful()) {
            Log::error('[MercadoPago] Error creando customer', [
                'user_id'  => $user->id,
                'response' => $createResponse->json(),
            ]);
            throw new Exception('No se pudo crear el customer en MercadoPago: ' . $createResponse->body());
        }

        return $createResponse->json('id');
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
            'mp_card_id'       => $card->id,
            'last_four_digits' => $card->last_four_digits,
            'payment_method_id'=> $card->payment_method_id ?? '',
            'payment_type_id'  => $card->payment_type_id ?? null,
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
            $payment = $client->create([
                'token'              => $cardToken,           // token re-generado (incluye CVV)
                'issuer_id'          => null,
                'payment_method_id'  => $savedCard->payment_method_id,
                'transaction_amount' => (float) $amount,
                'installments'       => 1,
                'external_reference' => $externalRef,
                'payer'              => [
                    'email'       => $email,
                    'type'        => 'customer',
                    'id'          => $savedCard->mp_customer_id,
                ],
            ], $requestOptions);
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
}
