<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\TootliDirectTrackingToken;
use Illuminate\Http\Request;
class TootliDirectPublicTrackingController extends Controller
{
    /**
     * Página pública de seguimiento (Tootli Directo / domicilio POS).
     */
    public function show(Request $request, string $token)
    {
        $token = $this->normalizeToken($token);
        if ($token === '') {
            abort(404);
        }

        return response()->view('tootli_direct.track', [
            'token' => $token,
            'pollUrl' => url('/ratreo-orden/tootli-directo/'.$token.'/datos'),
        ]);
    }

    /**
     * JSON para actualización en tiempo casi real (polling desde el navegador).
     */
    public function data(Request $request, string $token)
    {
        $token = $this->normalizeToken($token);
        if ($token === '') {
            return response()->json(['ok' => false, 'error' => 'not_found'], 404);
        }

        $payload = $this->buildPayload($token);

        if ($payload === null) {
            return response()->json(['ok' => false, 'error' => 'not_found'], 404);
        }

        return response()->json($payload);
    }

    private function normalizeToken(string $token): string
    {
        $token = trim($token);

        return preg_match('/^[A-Za-z0-9_-]{20,64}$/', $token) ? $token : '';
    }

    /**
     * @return array<string, mixed>|null
     */
    private function buildPayload(string $token): ?array
    {
        $row = TootliDirectTrackingToken::where('token', $token)->first();
        if (! $row) {
            return null;
        }
        if ($row->expires_at && $row->expires_at->isPast()) {
            return null;
        }

        $order = Order::withoutGlobalScopes()
            ->with(['store', 'delivery_man', 'module'])
            ->find($row->order_id);

        if (! $order || ! (int) $order->tootli_direct || $order->order_type !== 'delivery') {
            return null;
        }

        $addr = is_string($order->delivery_address)
            ? json_decode($order->delivery_address, true)
            : (array) $order->delivery_address;

        $dm = $order->delivery_man;
        $dmName = $dm ? trim(($dm->f_name ?? '').' '.($dm->l_name ?? '')) : null;

        return [
            'ok' => true,
            'order_id' => $order->id,
            'order_status' => $order->order_status,
            'order_status_label' => $this->statusLabelEs($order->order_status),
            'payment_status' => $order->payment_status,
            'store_name' => $order->store?->name,
            'module_type' => $order->module?->module_type,
            'address' => $this->formatAddressLine($addr),
            'delivery_man' => $dmName ? [
                'name' => $dmName,
                'image' => $dm->image_full_url ?? null,
            ] : null,
            'otp' => $order->order_status === 'picked_up' ? (string) ($order->otp ?? '') : null,
            'updated_at' => $order->updated_at?->toIso8601String(),
        ];
    }

    /**
     * @param  array<string, mixed>|null  $addr
     */
    private function formatAddressLine(?array $addr): ?string
    {
        if (! $addr) {
            return null;
        }
        $parts = array_filter([
            $addr['address'] ?? null,
            $addr['floor'] ?? null,
            $addr['road'] ?? null,
        ], fn ($v) => is_string($v) && trim($v) !== '');

        $line = implode(', ', $parts);

        return $line !== '' ? $line : null;
    }

    private function statusLabelEs(?string $status): string
    {
        return match ($status) {
            'pending' => 'Pendiente de confirmación',
            'accepted' => 'Aceptada',
            'confirmed' => 'Confirmada',
            'processing' => 'En preparación',
            'handover' => 'Lista para el repartidor',
            'picked_up' => 'En camino',
            'delivered' => 'Entregada',
            'canceled', 'cancelled' => 'Cancelada',
            'failed' => 'Fallida',
            'refunded' => 'Reembolsada',
            'partial_delivered' => 'Entrega parcial',
            default => $status ? ucfirst(str_replace('_', ' ', $status)) : '—',
        };
    }
}
