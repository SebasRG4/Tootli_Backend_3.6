<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\UserSavedCard;
use App\Services\EcartPayService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SavedCardController extends Controller
{
    private EcartPayService $ecartpay;

    public function __construct(EcartPayService $ecartpay)
    {
        $this->ecartpay = $ecartpay;
    }

    // -------------------------------------------------------------------------
    // GET /customer/cards
    // -------------------------------------------------------------------------
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
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'card_number' => 'required|string|min:13|max:19',
            'card_name'   => 'required|string|max:100',
            'exp_month'   => 'required|string|min:1|max:2',
            'exp_year'    => 'required|string|min:2|max:4',
            'cvc'         => 'required|string|min:3|max:4',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        try {
            $savedCard = $this->ecartpay->saveCard(
                user: $request->user(),
                cardData: [
                    'number'    => $request->card_number,
                    'name'      => $request->card_name,
                    'exp_month' => $request->exp_month,
                    'exp_year'  => $request->exp_year,
                    'cvc'       => $request->cvc,
                ]
            );

            return response()->json([
                'message' => 'Tarjeta guardada exitosamente.',
                'card'    => $this->formatCard($savedCard),
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'errors' => [['code' => 'card_error', 'message' => $e->getMessage()]],
            ], 422);
        }
    }

    // -------------------------------------------------------------------------
    // DELETE /customer/cards/{id}
    // -------------------------------------------------------------------------
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
            $this->ecartpay->deleteCard($card);
            return response()->json(['message' => 'Tarjeta eliminada.'], 200);
        } catch (Exception $e) {
            return response()->json([
                'errors' => [['code' => 'delete_error', 'message' => $e->getMessage()]],
            ], 422);
        }
    }

    // -------------------------------------------------------------------------
    // POST /customer/cards/{id}/set-default
    // -------------------------------------------------------------------------
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
    // POST /customer/cards/{id}/create-token
    // -------------------------------------------------------------------------
    public function createToken(Request $request, int $id): JsonResponse
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
            $token = $this->ecartpay->createCardToken($card, $request->security_code ?? null);
            return response()->json(['token' => $token], 200);
        } catch (Exception $e) {
            return response()->json([
                'errors' => [['code' => 'token_error', 'message' => $e->getMessage()]],
            ], 422);
        }
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------
    private function formatCard(UserSavedCard $card): array
    {
        return [
            'id'                    => $card->id,
            'last_four_digits'      => $card->last_four_digits,
            'payment_method_id'     => $card->payment_method_id,
            'brand_label'           => $card->brand_label,
            'expiration_month'      => $card->expiration_month,
            'expiration_year'       => $card->expiration_year,
            'expiration_display'    => $card->expiration_display,
            'cardholder_name'       => $card->cardholder_name,
            'payment_type_id'       => $card->payment_type_id,
            'is_default'            => $card->is_default,
            'ecartpay_card_id'      => $card->ecartpay_card_id,
            'ecartpay_customer_id'  => $card->ecartpay_customer_id,
        ];
    }
}
