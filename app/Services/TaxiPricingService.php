<?php

namespace App\Services;

use App\Models\DeliveryMan;
use App\Models\Module;
use App\Models\SurgePrice;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Taxi\Models\TaxiFareConfig;
use Modules\Taxi\Models\TaxiRide;

class TaxiPricingService
{
    /**
     * Multiplicador máximo estricto para proteger al usuario (Tope Tootli: x2.0)
     */
    const MAX_SURGE_MULTIPLIER = 2.0;

    protected WeatherService $weatherService;
    protected FareIntelligenceService $fareIntelligenceService;

    public function __construct(
        WeatherService $weatherService,
        FareIntelligenceService $fareIntelligenceService
    ) {
        $this->weatherService = $weatherService;
        $this->fareIntelligenceService = $fareIntelligenceService;
    }

    /**
     * Calcula la tarifa dinámica automatizada con desglose completo y transparente.
     *
     * @param TaxiFareConfig|null $fareConfig Configuración de tarifa de zona/vehículo
     * @param int|null $zoneId ID de la zona
     * @param float $distanceKm Distancia del trayecto en kilómetros
     * @param int $durationMin Duración estimada del trayecto en minutos
     * @param float $pickupLat Latitud del punto de partida
     * @param float $pickupLng Longitud del punto de partida
     * @param string $vehicleTypeSlug Slug del tipo de vehículo (economy, comfort, etc.)
     * @return array Desglose completo de tarifa, multiplicadores y motivos
     */
    public function calculateFare(
        ?TaxiFareConfig $fareConfig,
        ?int $zoneId,
        float $distanceKm,
        int $durationMin,
        float $pickupLat,
        float $pickupLng,
        string $vehicleTypeSlug
    ): array {
        // 1. Tarifa Base y Tarifas por Km / Minuto (Estrategia Agresiva de Penetración)
        if ($fareConfig) {
            $baseFare = (float) $fareConfig->base_fare;
            $perKmRate = (float) $fareConfig->per_km_rate;
            $perMinRate = (float) $fareConfig->per_min_rate;
            $minFare = (float) $fareConfig->minimum_fare;
        } else {
            // Fallbacks calibrados para competir agresivamente contra Uber y DiDi
            switch (strtolower($vehicleTypeSlug)) {
                case 'comfort':
                    $baseFare = 22.00;
                    $perKmRate = 9.20;
                    $perMinRate = 2.20;
                    $minFare = 44.00;
                    break;
                case 'premium':
                case 'xl':
                    $baseFare = 32.00;
                    $perKmRate = 12.50;
                    $perMinRate = 2.80;
                    $minFare = 60.00;
                    break;
                case 'economy':
                default:
                    $baseFare = 15.00;
                    $perKmRate = 7.20;
                    $perMinRate = 1.70;
                    $minFare = 32.00;
                    break;
            }
        }

        $distanceCharge = round($distanceKm * $perKmRate, 2);
        $timeCharge = round($durationMin * $perMinRate, 2);
        $subtotal = round($baseFare + $distanceCharge + $timeCharge, 2);

        // 2. Métricas de Oferta y Demanda en tiempo real
        $metrics = $this->getZoneMetrics($zoneId, $vehicleTypeSlug);
        $availableDrivers = $metrics['available_drivers'];
        $pendingRides = $metrics['pending_rides'];
        $activeRides = $metrics['active_rides'];

        // 3. Multiplicador de Clima (OpenWeatherMap)
        $weatherInfo = $this->weatherService->getWeatherInfo($pickupLat, $pickupLng);
        $weatherMultiplier = (float) ($weatherInfo['multiplier'] ?? 1.0);

        // 4. Multiplicador de Hora Pico y Nocturno con Candado de Demanda Real
        $timezone = (function_exists('config') && app()->bound('config'))
            ? config('app.timezone', 'America/Mexico_City')
            : 'America/Mexico_City';
        $localTime = Carbon::now($timezone ?: 'America/Mexico_City');
        $peakSurgeInfo = $this->evaluatePeakAndNightSurge(
            $localTime,
            $availableDrivers,
            $pendingRides,
            $activeRides
        );

        // 5. Multiplicador de Oferta / Demanda (Go Worker + Fallback DB)
        $demandSurge = $this->calculateDemandSurge($zoneId, $availableDrivers, $pendingRides);

        // 6. Integración con reglas de surge_prices (días festivos / eventos especiales)
        $surgePriceRule = $this->getSurgePricesRule($zoneId, $localTime);

        // 7. Consolidación de Factores y Razones
        $surgeReasons = [];
        $additionalSurge = 0.0;

        // Factores Clima
        if ($weatherMultiplier > 1.0) {
            $additionalSurge += ($weatherMultiplier - 1.0);
            $condition = $weatherInfo['condition'] ?? 'Lluvia';
            $surgeReasons[] = [
                'type' => 'weather',
                'title' => 'Clima (' . ucfirst($condition) . ')',
                'description' => 'Mayor tiempo de traslado debido a condiciones de lluvia.',
                'multiplier' => round($weatherMultiplier, 2),
                'icon' => strtolower($condition) === 'tormenta' ? 'thunderstorm' : 'rain',
            ];
        }

        // Factores Hora Pico / Noche
        if ($peakSurgeInfo !== null) {
            $additionalSurge += ($peakSurgeInfo['multiplier'] - 1.0);
            $surgeReasons[] = [
                'type' => $peakSurgeInfo['type'],
                'title' => $peakSurgeInfo['title'],
                'description' => 'Horario de alta concurrencia con viajes activos en la zona.',
                'multiplier' => round($peakSurgeInfo['multiplier'], 2),
                'icon' => $peakSurgeInfo['type'] === 'night_rate' ? 'night' : 'peak',
            ];
        }

        // Factores Alta Demanda (Oferta/Demanda)
        if ($demandSurge > 1.0) {
            $additionalSurge += ($demandSurge - 1.0);
            $surgeReasons[] = [
                'type' => 'high_demand',
                'title' => 'Alta Demanda',
                'description' => 'Muchos usuarios solicitando viajes simultáneamente.',
                'multiplier' => round($demandSurge, 2),
                'icon' => 'surge',
            ];
        }

        // Factores Días Festivos / Eventos Programados (surge_prices)
        if ($surgePriceRule['active']) {
            $holidayMult = 1.0;
            if ($surgePriceRule['price_type'] === 'percent' && $surgePriceRule['price'] > 0) {
                $holidayMult = 1.0 + ($surgePriceRule['price'] / 100.0);
                $additionalSurge += ($holidayMult - 1.0);
            }
            $surgeReasons[] = [
                'type' => 'holiday_event',
                'title' => $surgePriceRule['title'],
                'description' => $surgePriceRule['customer_note'] ?: 'Tarifa dinámica aplicada por fecha especial o festivo.',
                'multiplier' => round($holidayMult, 2),
                'icon' => 'calendar',
            ];
        }

        // 8. Aplicar Límite Máximo Estricto (Tope x2.0)
        $totalMultiplier = 1.0 + $additionalSurge;
        $finalMultiplier = min(round($totalMultiplier, 2), self::MAX_SURGE_MULTIPLIER);

        // Si no hay factores activos o el resultado es menor o igual a 1.0
        if ($finalMultiplier <= 1.0) {
            $finalMultiplier = 1.0;
            $surgeReasons = [];
        }

        // 9. Cálculo Final de Tarifas
        $surgeAmount = round(max(0, ($subtotal * $finalMultiplier) - $subtotal), 2);
        $totalCalculated = round($subtotal * $finalMultiplier);
        $finalTotal = (float) max($totalCalculated, $minFare);

        // Título y nota principal de surge para el frontend
        $primarySurgeTitle = null;
        $primarySurgeNote = null;
        if (!empty($surgeReasons)) {
            $primaryReason = $surgeReasons[0];
            $primarySurgeTitle = $primaryReason['title'];
            $primarySurgeNote = $primaryReason['description'];
        }

        return [
            'base_fare' => round($baseFare, 2),
            'distance_charge' => round($distanceCharge, 2),
            'time_charge' => round($timeCharge, 2),
            'subtotal' => round($subtotal, 2),
            'surge_multiplier' => $finalMultiplier,
            'surge_amount' => $surgeAmount,
            'is_surge_active' => $finalMultiplier > 1.0,
            'surge_title' => $primarySurgeTitle,
            'surge_note' => $primarySurgeNote,
            'surge_reasons' => $surgeReasons,
            'total' => round($finalTotal),
            'minimum_fare' => round($minFare, 2),
            'available_drivers' => $availableDrivers,
            'pending_rides' => $pendingRides,
            'active_rides' => $activeRides,
            'weather' => $weatherInfo,
        ];
    }

    /**
     * Evalúa franjas de hora pico y nocturnas con Candado de Demanda Real.
     * Si no hay demanda real y sobran conductores, aplica Fallback (x1.0 tarifa regular).
     */
    protected function evaluatePeakAndNightSurge(
        Carbon $localTime,
        int $availableDrivers,
        int $pendingRides,
        int $activeRides
    ): ?array {
        $hourMin = $localTime->format('H:i');

        $isMorningPeak = ($hourMin >= '07:00' && $hourMin <= '09:30');
        $isEveningPeak = ($hourMin >= '17:30' && $hourMin <= '20:00');
        $isNightRate   = ($hourMin >= '22:00' || $hourMin <= '05:00');

        if (!$isMorningPeak && !$isEveningPeak && !$isNightRate) {
            return null; // Horario regular valle
        }

        // 🛡️ CANDADO DE DEMANDA REAL / FALLBACK:
        // Solo se cobra recargo de hora pico o nocturno si la flota tiene presión real.
        $hasDemandPressure = false;

        if ($pendingRides >= 1) {
            // Hay usuarios esperando asignación
            $hasDemandPressure = true;
        } elseif ($activeRides >= 2) {
            // Varios conductores están en viaje ocupados
            $hasDemandPressure = true;
        } elseif ($availableDrivers <= 1) {
            // Escasez crítica de conductores en la zona
            $hasDemandPressure = true;
        } else {
            // Ocupación de la flota superior al 70%
            $occupancyRatio = ($pendingRides + $activeRides) / max($availableDrivers, 1);
            if ($occupancyRatio >= 0.70) {
                $hasDemandPressure = true;
            }
        }

        // Fallback: Si no hay presión de demanda (muchos conductores libres y 0 viajes esperando),
        // mantenemos tarifa regular (x1.0) para incentivar al cliente.
        if (!$hasDemandPressure) {
            return null;
        }

        if ($isEveningPeak) {
            return [
                'type' => 'peak_hours',
                'title' => 'Hora Pico Vespertina',
                'multiplier' => 1.25,
            ];
        }

        if ($isMorningPeak) {
            return [
                'type' => 'peak_hours',
                'title' => 'Hora Pico Matutina',
                'multiplier' => 1.20,
            ];
        }

        if ($isNightRate) {
            return [
                'type' => 'night_rate',
                'title' => 'Tarifa Nocturna',
                'multiplier' => 1.15,
            ];
        }

        return null;
    }

    /**
     * Calcula la sobretasa por oferta/demanda pura consultando Go Worker o DB.
     */
    protected function calculateDemandSurge(
        ?int $zoneId,
        int $availableDrivers,
        int $pendingRides
    ): float {
        // 1. Intentar consultar Go Worker (latencia sub-milisegundo en RAM)
        if ($zoneId) {
            $goSurge = $this->getGoWorkerSurge($zoneId);
            if ($goSurge !== null && $goSurge > 1.0) {
                return $goSurge;
            }
        }

        // 2. Fallback con conteos de base de datos
        if ($availableDrivers == 0 && $pendingRides > 0) {
            return 1.30;
        }

        if ($availableDrivers > 0 && $pendingRides > 0) {
            $ratio = $pendingRides / $availableDrivers;
            if ($ratio >= 1.2) {
                $surge = 1.0 + (($ratio - 1.0) * 0.35);
                return min(round($surge, 2), self::MAX_SURGE_MULTIPLIER);
            }
        }

        return 1.0;
    }

    /**
     * Consulta Go Worker para el multiplicador de calor de zona
     */
    protected function getGoWorkerSurge(int $zoneId): ?float
    {
        try {
            $goWorkerUrl = (function_exists('config') && app()->bound('config'))
                ? config('services.go_worker.url', 'http://127.0.0.1:8080')
                : 'http://127.0.0.1:8080';
            $response = Http::timeout(0.5)->get($goWorkerUrl . '/api/v1/surge/calculate', [
                'zone_id' => $zoneId,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['surge_multiplier']) && (float) $data['surge_multiplier'] > 1.0) {
                    return (float) $data['surge_multiplier'];
                }
            }
        } catch (\Exception $e) {
            // Silencioso para fallback inmediato a DB
        }

        return null;
    }

    /**
     * Consulta reglas activas de surge_prices (días festivos, fechas programadas)
     */
    protected function getSurgePricesRule(?int $zoneId, Carbon $localTime): array
    {
        $default = [
            'active' => false,
            'title' => '',
            'customer_note' => '',
            'price' => 0.0,
            'price_type' => 'amount',
        ];

        if (!$zoneId) {
            return $default;
        }

        $dateStr = $localTime->format('Y-m-d');
        $timeStr = $localTime->format('H:i:s');
        $weekday = $localTime->format('l');

        // Obtener ID del módulo taxi
        $module = Module::where('module_type', 'taxi')->first();
        $moduleId = $module ? $module->id : null;

        // 1. Verificar fecha exacta en surge_price_dates
        $surgeDate = DB::table('surge_price_dates')
            ->where('zone_id', $zoneId)
            ->when($moduleId, function ($q) use ($moduleId) {
                $q->where(function ($sub) use ($moduleId) {
                    $sub->where('module_id', $moduleId)
                        ->orWhereNull('module_id');
                });
            })
            ->where('applicable_date', $dateStr)
            ->where(function ($query) use ($timeStr) {
                $query->where(function ($q) use ($timeStr) {
                    $q->where('start_time', '<=', $timeStr)
                        ->where('end_time', '>=', $timeStr);
                })->orWhere(function ($q) {
                    $q->whereNull('start_time')->whereNull('end_time');
                });
            })
            ->where('status', 1)
            ->first();

        if ($surgeDate) {
            $surgePrice = SurgePrice::active()->find($surgeDate->surge_price_id);
            if ($surgePrice) {
                return [
                    'active' => true,
                    'title' => $surgePrice->surge_price_name ?: 'Tarifa Festiva Programada',
                    'customer_note' => $surgePrice->customer_note ?: 'Tarifa especial por fecha festiva.',
                    'price' => (float) $surgePrice->price,
                    'price_type' => $surgePrice->price_type,
                ];
            }
        }

        // 2. Verificar reglas semanales permanentes en surge_prices
        $permanentSurge = SurgePrice::active()
            ->where('zone_id', $zoneId)
            ->where(function ($q) use ($moduleId) {
                $q->whereNull('module_ids')
                    ->orWhereJsonContains('module_ids', (string) $moduleId)
                    ->orWhereJsonContains('module_ids', (int) $moduleId);
            })
            ->where('duration_type', 'weekly')
            ->whereJsonContains('weekly_days', $weekday)
            ->where(function ($query) use ($timeStr) {
                $query->where(function ($q) use ($timeStr) {
                    $q->where('start_time', '<=', $timeStr)
                        ->where('end_time', '>=', $timeStr);
                })->orWhere(function ($q) {
                    $q->whereNull('start_time')->whereNull('end_time');
                });
            })
            ->first();

        if ($permanentSurge) {
            return [
                'active' => true,
                'title' => $permanentSurge->surge_price_name ?: 'Tarifa Especial de Fin de Semana',
                'customer_note' => $permanentSurge->customer_note ?: 'Tarifa especial por horario programado.',
                'price' => (float) $permanentSurge->price,
                'price_type' => $permanentSurge->price_type,
            ];
        }

        // 3. Verificar regla diaria activa
        $dailySurge = SurgePrice::active()
            ->where('zone_id', $zoneId)
            ->where(function ($q) use ($moduleId) {
                $q->whereNull('module_ids')
                    ->orWhereJsonContains('module_ids', (string) $moduleId)
                    ->orWhereJsonContains('module_ids', (int) $moduleId);
            })
            ->where('duration_type', 'daily')
            ->where(function ($query) use ($timeStr) {
                $query->where(function ($q) use ($timeStr) {
                    $q->where('start_time', '<=', $timeStr)
                        ->where('end_time', '>=', $timeStr);
                })->orWhere(function ($q) {
                    $q->whereNull('start_time')->whereNull('end_time');
                });
            })
            ->first();

        if ($dailySurge) {
            return [
                'active' => true,
                'title' => $dailySurge->surge_price_name ?: 'Tarifa Dinámica Especial',
                'customer_note' => $dailySurge->customer_note ?: 'Tarifa especial activa.',
                'price' => (float) $dailySurge->price,
                'price_type' => $dailySurge->price_type,
            ];
        }

        return $default;
    }

    /**
     * Obtiene métricas de la zona (conductores disponibles, viajes pendientes, viajes activos)
     */
    protected function getZoneMetrics(?int $zoneId, string $vehicleTypeSlug): array
    {
        $availableDriversQuery = DeliveryMan::canTaxi()
            ->taxiAvailable();

        if ($zoneId) {
            $availableDriversQuery->where('zone_id', $zoneId);
        }

        if (!empty($vehicleTypeSlug)) {
            $availableDriversQuery->whereHas('vehicle', function ($q) use ($vehicleTypeSlug) {
                $q->where('type', $vehicleTypeSlug);
            });
        }

        $availableDrivers = $availableDriversQuery->count();

        $pendingQuery = TaxiRide::pending()
            ->where('created_at', '>=', now()->subMinutes(15));

        $activeQuery = TaxiRide::active()
            ->where('updated_at', '>=', now()->subHours(1));

        if ($zoneId) {
            $pendingQuery->where('zone_id', $zoneId);
            $activeQuery->where('zone_id', $zoneId);
        }

        return [
            'available_drivers' => $availableDrivers,
            'pending_rides' => $pendingQuery->count(),
            'active_rides' => $activeQuery->count(),
        ];
    }
}
