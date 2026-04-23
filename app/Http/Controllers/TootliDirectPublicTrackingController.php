<?php

namespace App\Http\Controllers;

use App\Models\DeliveryMan;
use App\Models\DMVehicle;
use App\Models\Order;
use App\Models\TootliDirectTrackingToken;
use App\Services\MapboxDirectionsService;
use Carbon\Carbon;
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
            'pollUrl' => url('/rastreo-orden/tootli-directo/'.$token.'/datos'),
            'chatUrl' => url('/rastreo-orden/tootli-directo/'.$token.'/chat'),
            'mapboxPublicToken' => $this->mapboxPublicToken(),
            'courierMarkerUrl' => asset('assets/tootli/delivery_man_marker.png'),
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
     * Token pk.… para Mapbox GL en el navegador. No exponer sk.… al cliente.
     */
    private function mapboxPublicToken(): ?string
    {
        $pub = (string) config('services.mapbox.public_token', '');
        if ($pub !== '') {
            return $pub;
        }
        $main = (string) config('services.mapbox.access_token', '');

        return str_starts_with($main, 'pk.') ? $main : null;
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
            ->with(['store', 'delivery_man.last_location', 'module'])
            ->find($row->order_id);

        if (! $order || ! $order->isTootliDirectTrackable()) {
            return null;
        }

        $addr = is_string($order->delivery_address)
            ? json_decode($order->delivery_address, true)
            : (array) $order->delivery_address;

        $dm = $order->delivery_man;
        $dmName = $dm ? trim(($dm->f_name ?? '').' '.($dm->l_name ?? '')) : null;
        $dmPhone = $this->deliveryManPhoneForTracking($dm);

        $pickLat = $this->parseCoord($order->store?->latitude);
        $pickLng = $this->parseCoord($order->store?->longitude);
        $dropLat = $this->parseCoord($addr['latitude'] ?? null);
        $dropLng = $this->parseCoord($addr['longitude'] ?? null);

        $courierLat = null;
        $courierLng = null;
        if ($dm && $dm->relationLoaded('last_location') && $dm->last_location) {
            $courierLat = $this->parseCoord($dm->last_location->latitude);
            $courierLng = $this->parseCoord($dm->last_location->longitude);
        }

        $vehicleLine = null;
        if ($dm && $dm->vehicle_id) {
            $v = DMVehicle::withoutGlobalScopes()->find($dm->vehicle_id);
            if ($v) {
                $vehicleLine = trim(($v->brand ?? '').' '.($v->model ?? ''));
                $vehicleLine = $vehicleLine !== '' ? $vehicleLine : null;
            }
        }

        $contact = $this->contactFromDeliveryAddress($addr);

        $eta = $this->computeEtaMinutes($order->order_status, $pickLat, $pickLng, $dropLat, $dropLng, $courierLat, $courierLng);
        $tz = config('app.timezone') ?: 'UTC';
        $etaClock = null;
        if ($eta !== null && $eta > 0) {
            $etaClock = Carbon::now($tz)->addMinutes((int) $eta)->format('H:i');
        }

        return [
            'ok' => true,
            'order_id' => $order->id,
            'order_status' => $order->order_status,
            'order_status_label' => $this->statusLabelEs($order->order_status),
            'headline' => $this->headlineEs($order->order_status, $dmName),
            'progress_filled' => $this->progressFilled($order->order_status),
            'payment_status' => $order->payment_status,
            'store_name' => $order->store?->name,
            'module_type' => $order->module?->module_type,
            'address' => $this->formatAddressLine($addr),
            'contact' => $contact,
            'pickup' => [
                'lat' => $pickLat,
                'lng' => $pickLng,
                'label' => $order->store?->name,
            ],
            'dropoff' => [
                'lat' => $dropLat,
                'lng' => $dropLng,
            ],
            'courier' => ($courierLat !== null && $courierLng !== null) ? [
                'lat' => $courierLat,
                'lng' => $courierLng,
            ] : null,
            'delivery_man' => $dmName ? [
                'name' => $dmName,
                'phone' => $dmPhone,
                'image' => $dm->image_full_url ?? null,
                'vehicle' => $vehicleLine,
            ] : null,
            'eta_minutes' => $eta,
            'estimated_arrival_clock' => $etaClock,
            'otp' => $order->order_status === 'picked_up' ? (string) ($order->otp ?? '') : null,
            'updated_at' => $order->updated_at?->toIso8601String(),
        ];
    }

    /**
     * @param  array<string, mixed>  $addr
     * @return array{name: ?string, phone: ?string}
     */
    private function contactFromDeliveryAddress(array $addr): array
    {
        $phone = trim((string) ($addr['contact_person_number'] ?? $addr['phone'] ?? ''));
        $name = trim((string) ($addr['contact_person_name'] ?? ''));

        return [
            'name' => $name !== '' ? $name : null,
            'phone' => $phone !== '' ? $phone : null,
        ];
    }

    private function parseCoord(mixed $v): ?float
    {
        if ($v === null || $v === '') {
            return null;
        }
        $f = (float) $v;
        if (! is_finite($f)) {
            return null;
        }

        return $f;
    }

    private function progressFilled(string $status): int
    {
        return match ($status) {
            'pending' => 1,
            'accepted', 'confirmed' => 2,
            'processing' => 3,
            'handover' => 4,
            'picked_up' => 4,
            'delivered', 'partial_delivered' => 5,
            'canceled', 'cancelled', 'failed', 'refunded' => 0,
            default => 1,
        };
    }

    private function headlineEs(string $status, ?string $dmName): string
    {
        $who = $dmName ? $dmName : 'Tu repartidor';

        return match ($status) {
            'pending' => 'Estamos confirmando tu pedido con la tienda.',
            'accepted', 'confirmed' => 'La tienda confirmó tu pedido.',
            'processing' => 'Tu pedido se está preparando.',
            'handover' => 'Listo: pronto lo recoge el repartidor.',
            'picked_up' => $who.' va en camino a tu domicilio.',
            'delivered', 'partial_delivered' => 'Pedido entregado. ¡Gracias!',
            'canceled', 'cancelled' => 'Este pedido fue cancelado.',
            'failed' => 'Hubo un inconveniente con el pedido.',
            'refunded' => 'Pedido reembolsado.',
            default => 'Seguimiento de tu pedido Tootli Directo.',
        };
    }

    private function computeEtaMinutes(
        string $status,
        ?float $pickLat,
        ?float $pickLng,
        ?float $dropLat,
        ?float $dropLng,
        ?float $courierLat,
        ?float $courierLng
    ): ?int {
        if ($dropLat === null || $dropLng === null) {
            return null;
        }
        $token = (string) config('services.mapbox.access_token', '');
        if ($token === '') {
            return null;
        }

        $route = null;
        if ($courierLat !== null && $courierLng !== null && in_array($status, ['picked_up', 'handover'], true)) {
            $route = app(MapboxDirectionsService::class)->drivingTrafficRoute(
                $courierLng,
                $courierLat,
                $dropLng,
                $dropLat
            );
        } elseif ($pickLat !== null && $pickLng !== null && in_array($status, ['pending', 'accepted', 'confirmed', 'processing', 'handover'], true)) {
            $route = app(MapboxDirectionsService::class)->drivingTrafficRoute(
                $pickLng,
                $pickLat,
                $dropLng,
                $dropLat
            );
        }

        return isset($route['duration_minutes']) ? (int) $route['duration_minutes'] : null;
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

    /**
     * Teléfono del repartidor para el JSON de seguimiento (lectura explícita por si el modelo en memoria no trae la columna).
     */
    private function deliveryManPhoneForTracking(?DeliveryMan $dm): ?string
    {
        if (! $dm || ! $dm->id) {
            return null;
        }
        $raw = trim((string) ($dm->getRawOriginal('phone') ?? $dm->phone ?? ''));
        if ($raw !== '') {
            return $raw;
        }
        $fromDb = DeliveryMan::withoutGlobalScopes()
            ->where('id', $dm->id)
            ->value('phone');
        $p = trim((string) ($fromDb ?? ''));

        return $p !== '' ? $p : null;
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
