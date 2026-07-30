@extends('layouts.admin.app')

@section('title', 'Alertas de seguridad')

@push('css_or_js')
    <style>
        .alert-card {
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }

        .alert-card.emergency {
            border-left-color: #dc3545;
            background-color: #fff5f5;
        }

        .alert-card.insecure {
            border-left-color: #fd7e14;
            background-color: #fff8f0;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-contacted {
            background: #cce5ff;
            color: #004085;
        }

        .status-resolved {
            background: #d4edda;
            color: #155724;
        }

        .status-escalated {
            background: #f8d7da;
            color: #721c24;
        }

        .stat-card {
            border-radius: 12px;
            padding: 20px;
            text-align: center;
        }

        .stat-card.danger {
            background: linear-gradient(135deg, #ff6b6b, #ee5a5a);
            color: white;
        }

        .stat-card.warning {
            background: linear-gradient(135deg, #ffa500, #ff8c00);
            color: white;
        }

        .stat-card.info {
            background: linear-gradient(135deg, #4ecdc4, #44a3aa);
            color: white;
        }

        .pulse-animation {
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }

            100% {
                transform: scale(1);
            }
        }
    </style>
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <i class="tio-shield-check mr-2"></i>
                {{ 'Alertas de seguridad' }}
            </h1>
        </div>

        <!-- Stats Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="stat-card danger {{ $stats['emergency'] > 0 ? 'pulse-animation' : '' }}">
                    <i class="tio-warning text-white" style="font-size: 32px;"></i>
                    <h2 class="mb-0 mt-2">{{ $stats['emergency'] }}</h2>
                    <p class="mb-0">{{ 'Alertas de emergencia' }}</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card warning">
                    <i class="tio-time text-white" style="font-size: 32px;"></i>
                    <h2 class="mb-0 mt-2">{{ $stats['pending'] }}</h2>
                    <p class="mb-0">{{ 'Alertas pendientes' }}</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card info">
                    <i class="tio-calendar text-white" style="font-size: 32px;"></i>
                    <h2 class="mb-0 mt-2">{{ $stats['today'] }}</h2>
                    <p class="mb-0">{{ 'Alertas de hoy' }}</p>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">{{ 'Estado' }}</label>
                        <select name="status" class="form-control" onchange="this.form.submit()">
                            <option value="all">{{ 'Todo' }}</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>
                                {{ 'Pendiente' }}
                            </option>
                            <option value="contacted" {{ request('status') == 'contacted' ? 'selected' : '' }}>
                                {{ 'Contactado' }}
                            </option>
                            <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>
                                {{ 'Resuelto' }}
                            </option>
                            <option value="escalated" {{ request('status') == 'escalated' ? 'selected' : '' }}>
                                {{ 'escalado' }}
                            </option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ 'Tipo' }}</label>
                        <select name="type" class="form-control" onchange="this.form.submit()">
                            <option value="all">{{ 'Todo' }}</option>
                            <option value="insecure" {{ request('type') == 'insecure' ? 'selected' : '' }}>
                                {{ 'Inseguro' }}
                            </option>
                            <option value="emergency" {{ request('type') == 'emergency' ? 'selected' : '' }}>
                                {{ 'Emergencia' }}
                            </option>
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <!-- Alerts Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ 'Lista de alertas' }}</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>{{ 'IDENTIFICACIÓN' }}</th>
                                <th>{{ 'Tipo' }}</th>
                                <th>{{ 'Usuario' }}</th>
                                <th>{{ 'Conducir' }}</th>
                                <th>{{ 'Estado' }}</th>
                                <th>{{ 'Creado' }}</th>
                                <th>{{ 'Comportamiento' }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($alerts as $alert)
                                <tr class="alert-card {{ $alert->alert_type }}">
                                    <td>#{{ $alert->id }}</td>
                                    <td>
                                        @if($alert->alert_type == 'emergency')
                                            <span class="badge badge-danger">🆘 EMERGENCIA</span>
                                        @else
                                            <span class="badge badge-warning">🛡️ Inseguro</span>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>{{ $alert->user->f_name ?? '' }} {{ $alert->user->l_name ?? '' }}</strong>
                                        <br><small>{{ $alert->user->phone ?? 'N/A' }}</small>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.taxi.rides.details', $alert->taxi_ride_id) }}"
                                            class="text-primary">
                                            #{{ $alert->taxi_ride_id }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="status-badge status-{{ $alert->status }}">
                                            {{ ucfirst($alert->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ $alert->created_at->diffForHumans() }}
                                        <br><small>{{ $alert->created_at->format('d/m/Y H:i') }}</small>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.taxi.safety.show', $alert->id) }}"
                                            class="btn btn-sm btn-primary">
                                            <i class="tio-visible"></i> {{ 'Vista' }}
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <i class="tio-shield-check" style="font-size: 48px; color: #ccc;"></i>
                                        <p class="mt-2 mb-0">{{ 'No se encontraron alertas de seguridad' }}</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($alerts->hasPages())
                <div class="card-footer">
                    {{ $alerts->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

@push('script_2')
    <script>
        // Auto-refresh pending alerts every 30 seconds
        setInterval(function () {
            fetch('{{ route('admin.taxi.safety.pending') }}')
                .then(response => response.json())
                .then(data => {
                    if (data.emergency_count > 0) {
                        // Play alert sound for emergencies
                        // new Audio('/sounds/alert.mp3').play();

                        // Show notification
                        if (Notification.permission === 'granted') {
                            new Notification('🆘 Emergency Alert!', {
                                body: `${data.emergency_count} emergency alert(s) pending`,
                                icon: '/favicon.ico'
                            });
                        }
                    }
                });
        }, 30000);

        // Request notification permission
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }
    </script>
@endpush