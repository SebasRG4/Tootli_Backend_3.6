<?php

namespace App\Http\Controllers;

use App\Models\DeliveryHistory;
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
            'echoScripts' => $this->echoScriptsEnabledForPublicTracking(),
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

        return response()
            ->json($payload)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
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
            ->with(['store', 'delivery_man.rating', 'module'])
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
        $dmRating = $this->deliveryManRatingAvg($dm);

        $pickLat = $this->parseCoord($order->store?->latitude);
        $pickLng = $this->parseCoord($order->store?->longitude);
        $dropLat = $this->parseCoord($addr['latitude'] ?? null);
        $dropLng = $this->parseCoord($addr['longitude'] ?? null);

        $courierLat = null;
        $courierLng = null;
        if ($dm && $dm->id) {
            $hist = DeliveryHistory::query()
                ->where('delivery_man_id', $dm->id)
                ->orderByDesc('id')
                ->first();
            if ($hist) {
                $courierLat = $this->parseCoord($hist->latitude);
                $courierLng = $this->parseCoord($hist->longitude);
            }
        }

        $vehicleLine = null;
        if ($dm && $dm->vehicle_id) {
            $v = DMVehicle::withoutGlobalScopes()->find($dm->vehicle_id);
            if ($v) {
                $vehicleLine = trim(($v->brand ?? '').' '.($v->model ?? ''));
                $vehicleLine = $vehicleLine !== '' ? $vehicleLine : null;
            }
        }

        $mapRoute = $this->resolveMapRouteForTracking($order->order_status, $pickLat, $pickLng, $dropLat, $dropLng, $courierLat, $courierLng);
        $eta = $mapRoute['eta_minutes'];
        $routeCoordinates = $mapRoute['coordinates'];
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
            'headline_highlight' => $this->headlineHighlightEs($order->order_status),
            'progress_filled' => $this->progressFilled($order->order_status),
            'payment_status' => $order->payment_status,
            'store_name' => $order->store?->name,
            'module_type' => $order->module?->module_type,
            'address' => $this->formatAddressLine($addr),
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
                'rating_avg' => $dmRating,
            ] : null,
            'eta_minutes' => $eta,
            'estimated_arrival_clock' => $etaClock,
            /** @var list<array{0: float, 1: float}>|null Polilínea [lng,lat]… por calles (Mapbox Directions). */
            'route_coordinates' => $routeCoordinates,
            'otp' => $order->order_status === 'picked_up' ? (string) ($order->otp ?? '') : null,
            'tracking_chat_open' => $order->isTootliDirectPublicTrackingChatOpen(),
            'updated_at' => $order->updated_at?->toIso8601String(),
            'live_ws' => $this->buildLiveLocationWebsocketConfig($dm?->id),
        ];
    }

    /**
     * Carga Pusher + Echo en la vista de rastreo si hay credenciales de broadcast (Reverb/Pusher).
     */
    private function echoScriptsEnabledForPublicTracking(): bool
    {
        $d = (string) config('broadcasting.default', 'null');
        if (! in_array($d, ['reverb', 'pusher'], true)) {
            return false;
        }
        $key = (string) (config("broadcasting.connections.{$d}.key") ?? '');
        $opts = config("broadcasting.connections.{$d}.options", []);

        if ($key === '') {
            return false;
        }
        if ($d === 'reverb') {
            return ($opts['host'] ?? '') !== '';
        }

        return ($opts['cluster'] ?? '') !== '';
    }

    /**
     * @return array<string, mixed>|null
     */
    private function buildLiveLocationWebsocketConfig(?int $deliveryManId): ?array
    {
        if (! $deliveryManId) {
            return null;
        }
        $driver = (string) config('broadcasting.default', 'null');
        if ($driver === 'reverb') {
            $c = config('broadcasting.connections.reverb');
            $key = (string) ($c['key'] ?? '');
            $opts = $c['options'] ?? [];
            $host = (string) ($opts['host'] ?? '');
            if ($key === '' || $host === '') {
                return null;
            }
            $scheme = (string) ($opts['scheme'] ?? 'https');
            $port = (int) ($opts['port'] ?? 443);
            $useTls = ($opts['useTLS'] ?? ($scheme === 'https')) === true || $scheme === 'https';
            $channel = 'dm_location_'.$deliveryManId;

            return [
                'driver' => 'reverb',
                'key' => $key,
                'wsHost' => $host,
                'wsPort' => $useTls ? 443 : 80,
                'wssPort' => $port,
                'forceTLS' => $useTls,
                'cluster' => 'mt1',
                'channel' => $channel,
                'listen_as' => '.'.$channel,
            ];
        }
        if ($driver === 'pusher') {
            $c = config('broadcasting.connections.pusher');
            $key = (string) ($c['key'] ?? '');
            $opts = $c['options'] ?? [];
            $cluster = (string) ($opts['cluster'] ?? '');
            if ($key === '' || $cluster === '') {
                return null;
            }
            $host = (string) ($opts['host'] ?? '127.0.0.1');
            $port = (int) ($opts['port'] ?? 6001);
            $scheme = (string) ($opts['scheme'] ?? 'http');
            $useTls = ($opts['encrypted'] ?? false) === true || ($opts['useTLS'] ?? false) === true || $scheme === 'https';
            $channel = 'dm_location_'.$deliveryManId;

            return [
                'driver' => 'pusher',
                'key' => $key,
                'cluster' => $cluster,
                'wsHost' => $host,
                'wsPort' => $useTls ? 443 : $port,
                'wssPort' => $useTls ? $port : 443,
                'forceTLS' => $useTls,
                'channel' => $channel,
                'listen_as' => '.'.$channel,
            ];
        }

        return null;
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

    /**
     * Palabra o frase corta dentro de {@see headlineEs} para resaltar en verde en la UI.
     */
    private function headlineHighlightEs(string $status): ?string
    {
        return match ($status) {
            'pending' => 'confirmando',
            'accepted', 'confirmed' => 'confirmó',
            'processing' => 'preparando',
            'handover' => 'pronto',
            'picked_up' => 'en camino',
            'delivered', 'partial_delivered' => 'entregado',
            'canceled', 'cancelled' => 'cancelado',
            'failed' => 'inconveniente',
            'refunded' => 'reembolsado',
            default => null,
        };
    }

    private function deliveryManRatingAvg(?DeliveryMan $dm): ?float
    {
        if (! $dm) {
            return null;
        }
        $row = $dm->rating()->first();
        if (! $row || ! isset($row->average) || (float) $row->average <= 0) {
            return null;
        }

        return round((float) $row->average, 1);
    }

    /**
     * ETA y geometría de ruta por GPS (misma petición Mapbox).
     *
     * @return array{eta_minutes: ?int, coordinates: list<array{0: float, 1: float}>|null}
     */
    private function resolveMapRouteForTracking(
        string $status,
        ?float $pickLat,
        ?float $pickLng,
        ?float $dropLat,
        ?float $dropLng,
        ?float $courierLat,
        ?float $courierLng
    ): array {
        if ($dropLat === null || $dropLng === null) {
            return ['eta_minutes' => null, 'coordinates' => null];
        }
        $token = (string) config('services.mapbox.access_token', '');
        if ($token === '') {
            return ['eta_minutes' => null, 'coordinates' => null];
        }

        $route = null;
        if ($courierLat !== null && $courierLng !== null && in_array($status, ['picked_up', 'handover'], true)) {
            $route = app(MapboxDirectionsService::class)->drivingTrafficRoute(
                $courierLng,
                $courierLat,
                $dropLng,
                $dropLat
            );
        } elseif ($pickLat !== null && $pickLng !== null && in_array($status, ['pending', 'accepted', 'confirmed', 'processing', 'handover', 'picked_up'], true)) {
            $route = app(MapboxDirectionsService::class)->drivingTrafficRoute(
                $pickLng,
                $pickLat,
                $dropLng,
                $dropLat
            );
        }

        if ($route === null) {
            return ['eta_minutes' => null, 'coordinates' => null];
        }

        $coords = $route['coordinates'] ?? null;
        if (! is_array($coords) || count($coords) < 2) {
            $coords = null;
        }

        return [
            'eta_minutes' => isset($route['duration_minutes']) ? (int) $route['duration_minutes'] : null,
            'coordinates' => $coords,
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
