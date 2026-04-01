<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\UserSavedCard;
use App\Services\MercadoPagoCardService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Gestión de tarjetas guardadas de MercadoPago para el usuario autenticado.
 *
 * Rutas:
 *   GET    /api/v1/customer/cards/mp-public-key   → getPublicKey
 *   GET    /api/v1/customer/cards                  → index
 *   POST   /api/v1/customer/cards/add              → store
 *   DELETE /api/v1/customer/cards/{id}             → destroy
 *   POST   /api/v1/customer/cards/{id}/set-default → setDefault
 */
class SavedCardController extends Controller
{
    private MercadoPagoCardService $mpService;

    public function __construct(MercadoPagoCardService $mpService)
    {
        $this->mpService = $mpService;
    }

    // -------------------------------------------------------------------------
    // GET /customer/cards/mp-public-key
    // -------------------------------------------------------------------------
    /**
     * Devuelve la Public Key de MercadoPago para que Flutter pueda inicializar el SDK.
     */
    public function getPublicKey(Request $request): JsonResponse
    {
        return response()->json([
            'public_key' => $this->mpService->getPublicKey(),
        ], 200);
    }

    // -------------------------------------------------------------------------
    // GET /customer/cards
    // -------------------------------------------------------------------------
    /**
     * Lista todas las tarjetas guardadas del usuario.
     */
    public function index(Request $request): JsonResponse
    {
        $cards = UserSavedCard::where('user_id', $request->user()->id)
            ->orderByDesc('is_default')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($card) => $this->formatCard($card));

        return response()->json([
            'total_size' => $cards->count(),
            'data'       => $cards,
        ], 200);
    }

    // -------------------------------------------------------------------------
    // POST /customer/cards/add
    // -------------------------------------------------------------------------
    /**
     * Agrega una nueva tarjeta al usuario.
     *
     * Body JSON:
     *   - card_token: string  (token generado por SDK de MercadoPago en Flutter)
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'card_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        try {
            $savedCard = $this->mpService->saveCard(
                user:      $request->user(),
                cardToken: $request->card_token
            );

            return response()->json([
                'message' => 'Tarjeta guardada exitosamente.',
                'card'    => $this->formatCard($savedCard),
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'errors' => [['code' => 'mp_card_error', 'message' => $e->getMessage()]],
            ], 422);
        }
    }

    // -------------------------------------------------------------------------
    // DELETE /customer/cards/{id}
    // -------------------------------------------------------------------------
    /**
     * Elimina una tarjeta guardada (del usuario autenticado).
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $card = UserSavedCard::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$card) {
            return response()->json([
                'errors' => [['code' => 'not_found', 'message' => 'Tarjeta no encontrada.']],
            ], 404);
        }

        try {
            $this->mpService->deleteCard($card);
            return response()->json(['message' => 'Tarjeta eliminada.'], 200);
        } catch (Exception $e) {
            return response()->json([
                'errors' => [['code' => 'mp_delete_error', 'message' => $e->getMessage()]],
            ], 422);
        }
    }

    // -------------------------------------------------------------------------
    // POST /customer/cards/{id}/set-default
    // -------------------------------------------------------------------------
    /**
     * Marca una tarjeta como predeterminada y quita ese atributo a las demás.
     */
    public function setDefault(Request $request, int $id): JsonResponse
    {
        $card = UserSavedCard::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$card) {
            return response()->json([
                'errors' => [['code' => 'not_found', 'message' => 'Tarjeta no encontrada.']],
            ], 404);
        }

        // Quitar default a todas las otras
        UserSavedCard::where('user_id', $request->user()->id)
            ->where('id', '!=', $id)
            ->update(['is_default' => false]);

        $card->update(['is_default' => true]);

        return response()->json([
            'message' => 'Tarjeta predeterminada actualizada.',
            'card'    => $this->formatCard($card->fresh()),
        ], 200);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Formatea la tarjeta para la respuesta JSON (oculta datos sensibles).
     */
    private function formatCard(UserSavedCard $card): array
    {
        return [
            'id'                => $card->id,
            'last_four_digits'  => $card->last_four_digits,
            'payment_method_id' => $card->payment_method_id,
            'brand_label'       => $card->brand_label,           // "Visa", "Mastercard"...
            'expiration_month'  => $card->expiration_month,
            'expiration_year'   => $card->expiration_year,
            'expiration_display'=> $card->expiration_display,    // "05/28"
            'cardholder_name'   => $card->cardholder_name,
            'payment_type_id'   => $card->payment_type_id,       // credit_card / debit_card
            'is_default'        => $card->is_default,
            'mp_card_id'        => $card->mp_card_id,            // necesario para re-tokenizar en Flutter
        ];
    }
}
