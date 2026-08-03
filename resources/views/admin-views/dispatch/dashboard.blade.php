@extends('layouts.admin.app')

@section('title', 'Panel de Administración y Analítica')

@push('css_or_js')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <style>
        .kpi-card {
            background: #ffffff;
            border: 1px solid var(--t-border, #e2e8f0);
            border-radius: 16px;
            padding: 1.25rem 1.5rem;
            transition: all 0.2s ease;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.05);
        }
        .kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
            border-color: #cbd5e1;
        }
        .kpi-icon-wrapper {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            flex-shrink: 0;
        }
        .kpi-title {
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            margin-bottom: 0.25rem;
        }
        .kpi-value {
            font-size: 1.65rem;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.2;
        }
        .chart-card {
            background: #ffffff;
            border: 1px solid var(--t-border, #e2e8f0);
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.05);
        }
        .chart-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.25rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid #f1f5f9;
        }
        .chart-card-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
        }
    </style>
@endpush

@section('content')
<div class="content container-fluid">
    <!-- Header de Bienvenida -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="page-header-title font-bold text-dark fs-24 mb-1">
                👋 {{ 'Panel General & Analítica' }}
            </h1>
            <p class="text-muted fs-14 m-0">
                {{ 'Resumen de ventas, comisiones de administración, órdenes activas y rendimiento por módulo.' }}
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge badge-soft-success px-3 py-2 fs-12 rounded-pill font-semibold">
                🟢 {{ 'Servicio Operativo' }}
            </span>
        </div>
    </div>

    <!-- ── 1. TARJETAS KPI PRINCIPALES (4 Bloques) ────────────────────────── -->
    <div class="row g-3 mb-4">
        <!-- 1. Total Vendido -->
        <div class="col-sm-6 col-lg-3">
            <div class="kpi-card">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="kpi-title">{{ 'Total Vendido' }}</div>
                        <div class="kpi-value text-dark">
                            {{\App\CentralLogics\Helpers::format_currency($total_sold)}}
                        </div>
                    </div>
                    <div class="kpi-icon-wrapper bg-soft-success text-success">
                        <i class="tio-money-vs"></i>
                    </div>
                </div>
                <div class="mt-3 fs-12 text-muted d-flex align-items-center gap-1">
                    <span class="text-success font-semibold">✓ {{ 'Ventas confirmadas' }}</span>
                </div>
            </div>
        </div>

        <!-- 2. Total Ganado Admin (Día, Semana, Mes) -->
        <div class="col-sm-6 col-lg-3">
            <div class="kpi-card">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="kpi-title">{{ 'Ganancia Admin' }}</div>
                    <div class="kpi-icon-wrapper bg-soft-primary text-primary" style="width: 38px; height: 38px; font-size: 1.1rem;">
                        <i class="tio-wallet"></i>
                    </div>
                </div>
                <div class="d-flex flex-column gap-1 mt-1">
                    <div class="d-flex align-items-center justify-content-between fs-13 border-bottom pb-1">
                        <span class="text-muted font-medium">📅 {{ 'Día:' }}</span>
                        <span class="font-bold text-dark fs-14">{{\App\CentralLogics\Helpers::format_currency($admin_earnings_today)}}</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between fs-13 border-bottom py-1">
                        <span class="text-muted font-medium">🗓️ {{ 'Semana:' }}</span>
                        <span class="font-bold text-primary fs-14">{{\App\CentralLogics\Helpers::format_currency($admin_earnings_week)}}</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between fs-13 pt-1">
                        <span class="text-muted font-medium">📊 {{ 'Mes:' }}</span>
                        <span class="font-bold text-success fs-14">{{\App\CentralLogics\Helpers::format_currency($admin_earnings_month)}}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Órdenes Activas -->
        <div class="col-sm-6 col-lg-3">
            <div class="kpi-card">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="kpi-title">{{ 'Órdenes Activas' }}</div>
                        <div class="kpi-value text-warning">
                            {{ $active_orders }}
                        </div>
                    </div>
                    <div class="kpi-icon-wrapper bg-soft-warning text-warning">
                        <i class="tio-shopping-cart"></i>
                    </div>
                </div>
                <div class="mt-3 fs-12 text-muted d-flex align-items-center gap-1">
                    <span class="text-warning font-semibold">⚡ {{ 'En curso / Despacho' }}</span>
                </div>
            </div>
        </div>

        <!-- 4. Tickets Abiertos (Feature) -->
        <div class="col-sm-6 col-lg-3">
            <div class="kpi-card">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="kpi-title d-flex align-items-center gap-1">
                            {{ 'Tickets Abiertos' }}
                            <span class="badge badge-soft-info fs-10 px-1.5 py-0.5 rounded">{{ 'Feature' }}</span>
                        </div>
                        <div class="kpi-value text-info">
                            {{ $open_tickets }}
                        </div>
                    </div>
                    <div class="kpi-icon-wrapper bg-soft-info text-info">
                        <i class="tio-headset"></i>
                    </div>
                </div>
                <div class="mt-3 fs-12 text-muted d-flex align-items-center gap-1">
                    <span class="text-info font-semibold">🎧 {{ 'Módulo de Soporte QA' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ── 2. GRÁFICAS Y ANALÍTICA DE VENTAS ────────────────────────────── -->
    <div class="row g-3 mb-4">

        <!-- Gráfica 1: Ventas por Día de la Semana -->
        <div class="col-lg-7">
            <div class="chart-card h-100">
                <div class="chart-card-header">
                    <div>
                        <h3 class="chart-card-title">📈 {{ 'Ventas por Día de la Semana' }}</h3>
                        <span class="fs-12 text-muted">{{ 'Comportamiento de ventas en los últimos 7 días' }}</span>
                    </div>
                    <span class="badge badge-soft-primary px-2.5 py-1 rounded fs-12 font-medium">Últimos 7 días</span>
                </div>
                <div id="chart-daily-sales" style="min-height: 320px;"></div>
            </div>
        </div>

        <!-- Gráfica 2: Módulos que Más Venden -->
        <div class="col-lg-5">
            <div class="chart-card h-100">
                <div class="chart-card-header">
                    <div>
                        <h3 class="chart-card-title">🛍️ {{ 'Módulos que Más Venden' }}</h3>
                        <span class="fs-12 text-muted">{{ 'Distribución de ingresos por módulo de negocio' }}</span>
                    </div>
                </div>
                <div id="chart-module-sales" style="min-height: 320px;"></div>
            </div>
        </div>

    </div>

    <!-- ── 3. RESUMEN DE MÓDULOS EN TABLA ───────────────────────────────── -->
    <div class="card border-0 shadow-sm rounded-16 overflow-hidden">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
            <h4 class="card-title font-bold fs-16 m-0 text-dark">
                📋 {{ 'Desglose de Ventas por Módulo' }}
            </h4>
            <span class="text-muted fs-13">{{ count($module_sales) }} {{ 'Módulos con actividad' }}</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle m-0">
                <thead class="bg-light">
                    <tr>
                        <th class="py-3 px-4 text-muted fs-12 font-bold uppercase">{{ 'Módulo' }}</th>
                        <th class="py-3 px-4 text-muted fs-12 font-bold uppercase text-center">{{ 'Tipo' }}</th>
                        <th class="py-3 px-4 text-muted fs-12 font-bold uppercase text-center">{{ 'Órdenes Completadas' }}</th>
                        <th class="py-3 px-4 text-muted fs-12 font-bold uppercase text-right">{{ 'Total Vendido' }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($module_sales as $item)
                        <tr class="border-bottom-faint">
                            <td class="py-3 px-4 font-bold text-dark fs-14">
                                {{ $item['name'] }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="badge badge-soft-secondary text-capitalize px-2.5 py-1 fs-12">
                                    {{ $item['type'] }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center font-semibold text-dark fs-14">
                                {{ $item['count'] }}
                            </td>
                            <td class="py-3 px-4 text-right font-bold text-success fs-15">
                                {{\App\CentralLogics\Helpers::format_currency($item['amount'])}}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">
                                {{ 'No hay datos de ventas registrados aún en los módulos.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('script_2')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // ── 1. Gráfica de Ventas por Día ────────────────────────────
        var dailySalesData = @json($daily_sales);
        var dailyCategories = dailySalesData.map(function(item) { return item.day; });
        var dailyAmounts = dailySalesData.map(function(item) { return item.amount; });

        var optionsDaily = {
            series: [{
                name: 'Ventas Total ($)',
                data: dailyAmounts
            }],
            chart: {
                type: 'area',
                height: 310,
                toolbar: { show: false },
                fontFamily: 'Plus Jakarta Sans, sans-serif'
            },
            colors: ['#006837'],
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.45,
                    opacityTo: 0.05,
                    stops: [0, 90, 100]
                }
            },
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 3 },
            xaxis: {
                categories: dailyCategories,
                labels: { style: { colors: '#64748b', fontSize: '12px' } }
            },
            yaxis: {
                labels: {
                    style: { colors: '#64748b', fontSize: '12px' },
                    formatter: function (value) { return '$' + value.toLocaleString(); }
                }
            },
            tooltip: {
                y: { formatter: function (val) { return '$' + val.toLocaleString(); } }
            },
            grid: { borderColor: '#f1f5f9' }
        };

        var chartDaily = new ApexCharts(document.querySelector("#chart-daily-sales"), optionsDaily);
        chartDaily.render();

        // ── 2. Gráfica de Módulos que más Venden ───────────────────
        var moduleSalesData = @json($module_sales);
        var moduleNames = moduleSalesData.map(function(item) { return item.name; });
        var moduleAmounts = moduleSalesData.map(function(item) { return item.amount; });

        if (moduleAmounts.length === 0 || moduleAmounts.every(v => v === 0)) {
            moduleNames = ['Abastos', 'Comida', 'Paquetería', 'Taxi'];
            moduleAmounts = [0, 0, 0, 0];
        }

        var optionsModule = {
            series: moduleAmounts,
            labels: moduleNames,
            chart: {
                type: 'donut',
                height: 310,
                fontFamily: 'Plus Jakarta Sans, sans-serif'
            },
            colors: ['#006837', '#0284c7', '#f59e0b', '#8b5cf6', '#ec4899', '#10b981'],
            legend: {
                position: 'bottom',
                fontSize: '13px',
                labels: { colors: '#334155' }
            },
            dataLabels: { enabled: true },
            plotOptions: {
                pie: {
                    donut: {
                        size: '68%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: 'Total Módulos',
                                formatter: function (w) {
                                    var sum = w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                    return '$' + sum.toLocaleString();
                                }
                            }
                        }
                    }
                }
            },
            tooltip: {
                y: { formatter: function (val) { return '$' + val.toLocaleString(); } }
            }
        };

        var chartModule = new ApexCharts(document.querySelector("#chart-module-sales"), optionsModule);
        chartModule.render();
    });
</script>
@endpush
