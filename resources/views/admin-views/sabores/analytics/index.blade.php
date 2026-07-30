@extends('layouts.admin.app')

@section('title', 'Analítica')

@push('css_or_js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">
                        <span class="page-header-icon"><i class="tio-chart-bar-4"></i></span>
                        <span>{{ 'Análisis de uso' }}</span>
                    </h1>
                </div>
                <div class="col-sm-auto">
                    <select class="form-control"
                        onchange="location.href='{{ route('admin.sabores.analytics') }}?period=' + this.value">
                        <option value="7" {{ $period == 7 ? 'selected' : '' }}>{{ 'últimos 7 días' }}</option>
                        <option value="30" {{ $period == 30 ? 'selected' : '' }}>{{ 'últimos 30 días' }}</option>
                        <option value="90" {{ $period == 90 ? 'selected' : '' }}>{{ 'últimos 90 días' }}</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Stats Row -->
        <div class="row g-2 mb-3">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-subtitle">{{ 'Tamaño promedio del grupo' }}</h6>
                        <h2 class="card-title">{{ number_format($avg_party_size, 1) }}
                            <small>{{ 'gente' }}</small></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-subtitle">{{ 'Tasa de cancelación' }}</h6>
                        <h2 class="card-title">{{ $cancellation_rate }}%</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-subtitle">{{ 'Hora pico de reservas' }}</h6>
                        <h2 class="card-title">
                            @if($peak_times->isNotEmpty())
                                {{ $peak_times->first()->hour }}:00
                            @else
                                N/A
                            @endif
                        </h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-2">
            <!-- Reservations Over Time Chart -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-header-title">{{ 'Reservas a lo largo del tiempo' }}</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="reservationsChart" height="100"></canvas>
                    </div>
                </div>
            </div>

            <!-- Reservations by Status -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-header-title">{{ 'Por estado' }}</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Top Restaurants -->
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-header-title">{{ 'Mejores restaurantes por reservas' }}</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="topRestaurantsChart" height="60"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
    <script>
        // Reservations Over Time Chart
        const reservationsCtx = document.getElementById('reservationsChart').getContext('2d');
        new Chart(reservationsCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($reservations_chart->pluck('date')->map(function ($date) {
        return \Carbon\Carbon::parse($date)->format('M d');
    })) !!},
                datasets: [{
                    label: '{{ 'Reservas' }}',
                    data: {!! json_encode($reservations_chart->pluck('count')) !!},
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.1)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                }
            }
        });

        // Status Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($reservations_by_status->pluck('status')->map(function ($status) {
        return ucfirst($status);
    })) !!},
                datasets: [{
                    data: {!! json_encode($reservations_by_status->pluck('count')) !!},
                    backgroundColor: ['#FFA726', '#66BB6A', '#42A5F5', '#EF5350']
                }]
            }
        });

        // Top Restaurants Chart
        const topRestaurantsCtx = document.getElementById('topRestaurantsChart').getContext('2d');
        new Chart(topRestaurantsCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($top_restaurants_chart->pluck('name')) !!},
                datasets: [{
                    label: '{{ 'Reservas' }}',
                    data: {!! json_encode($top_restaurants_chart->pluck('reservations_count')) !!},
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgb(54, 162, 235)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    </script>
@endpush