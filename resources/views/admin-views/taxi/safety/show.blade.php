@extends('layouts.admin.app')

@section('title', 'Detalles de alerta')

@push('css_or_js')
    <style>
        .info-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            padding: 20px;
            margin-bottom: 20px;
        }

        .info-card h6 {
            color: #6c757d;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #f1f1f1;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .map-container {
            height: 300px;
            border-radius: 12px;
            overflow: hidden;
        }

        .action-buttons .btn {
            margin-right: 10px;
            margin-bottom: 10px;
        }

        .emergency-banner {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .insecure-banner {
            background: linear-gradient(135deg, #fd7e14, #e8690a);
            color: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
        }
    </style>
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Back Button -->
        <div class="mb-3">
            <a href="{{ route('admin.taxi.safety.index') }}" class="btn btn-outline-secondary">
                <i class="tio-arrow-left"></i> {{ 'Volver a Alertas' }}
            </a>
        </div>

        <!-- Alert Banner -->
        @if($alert->alert_type == 'emergency')
            <div class="emergency-banner">
                <h3><i class="tio-warning mr-2"></i> 🆘 EMERGENCY ALERT</h3>
                <p class="mb-0">This user called 911. Immediate attention required!</p>
            </div>
        @else
            <div class="insecure-banner">
                <h3><i class="tio-shield mr-2"></i> 🛡️ User Feels Insecure</h3>
                <p class="mb-0">Please contact the user immediately to verify their safety.</p>
            </div>
        @endif

        <div class="row">
            <!-- Left Column -->
            <div class="col-lg-6">
                <!-- Alert Info -->
                <div class="info-card">
                    <h6><i class="tio-info mr-2"></i>{{ 'Información de alerta' }}</h6>
                    <div class="info-item">
                        <span>{{ 'ID de alerta' }}</span>
                        <strong>#{{ $alert->id }}</strong>
                    </div>
                    <div class="info-item">
                        <span>{{ 'Tipo' }}</span>
                        <span class="badge badge-{{ $alert->alert_type == 'emergency' ? 'danger' : 'warning' }}">
                            {{ strtoupper($alert->alert_type) }}
                        </span>
                    </div>
                    <div class="info-item">
                        <span>{{ 'Estado' }}</span>
                        <span
                            class="badge badge-{{ $alert->status == 'resolved' ? 'success' : ($alert->status == 'pending' ? 'warning' : 'info') }}">
                            {{ strtoupper($alert->status) }}
                        </span>
                    </div>
                    <div class="info-item">
                        <span>{{ 'Creado' }}</span>
                        <strong>{{ $alert->created_at->format('d/m/Y H:i:s') }}</strong>
                    </div>
                    @if($alert->contacted_at)
                        <div class="info-item">
                            <span>{{ 'Contactado en' }}</span>
                            <strong>{{ $alert->contacted_at->format('d/m/Y H:i:s') }}</strong>
                        </div>
                    @endif
                    @if($alert->resolved_at)
                        <div class="info-item">
                            <span>{{ 'Resuelto en' }}</span>
                            <strong>{{ $alert->resolved_at->format('d/m/Y H:i:s') }}</strong>
                        </div>
                    @endif
                </div>

                <!-- User Info -->
                <div class="info-card">
                    <h6><i class="tio-user mr-2"></i>{{ 'Información del usuario' }}</h6>
                    <div class="info-item">
                        <span>{{ 'Nombre' }}</span>
                        <strong>{{ $alert->user->f_name ?? '' }} {{ $alert->user->l_name ?? '' }}</strong>
                    </div>
                    <div class="info-item">
                        <span>{{ 'Teléfono' }}</span>
                        <a href="tel:{{ $alert->user->phone }}" class="text-primary">
                            <i class="tio-call"></i> {{ $alert->user->phone ?? 'N/A' }}
                        </a>
                    </div>
                    <div class="info-item">
                        <span>{{ 'Correo electrónico' }}</span>
                        <span>{{ $alert->user->email ?? 'N/A' }}</span>
                    </div>
                </div>

                <!-- Driver Info -->
                @if($alert->taxiRide->driver)
                    <div class="info-card">
                        <h6><i class="tio-car mr-2"></i>{{ 'Información del conductor' }}</h6>
                        <div class="info-item">
                            <span>{{ 'Nombre' }}</span>
                            <strong>{{ $alert->taxiRide->driver->f_name }} {{ $alert->taxiRide->driver->l_name }}</strong>
                        </div>
                        <div class="info-item">
                            <span>{{ 'Teléfono' }}</span>
                            <a href="tel:{{ $alert->taxiRide->driver->phone }}" class="text-primary">
                                <i class="tio-call"></i> {{ $alert->taxiRide->driver->phone }}
                            </a>
                        </div>
                        <div class="info-item">
                            <span>{{ 'Placa del vehículo' }}</span>
                            <strong>{{ $alert->taxiRide->driver->vehicle->plate ?? 'N/A' }}</strong>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Right Column -->
            <div class="col-lg-6">
                <!-- Map -->
                <div class="info-card">
                    <h6><i class="tio-map mr-2"></i>{{ 'Ubicación' }}</h6>
                    @if($alert->user_location_lat && $alert->user_location_lng)
                        <div class="map-container mb-3">
                            <iframe width="100%" height="100%" frameborder="0"
                                src="https://www.google.com/maps/embed/v1/place?key={{ env('GOOGLE_MAPS_API_KEY') }}&q={{ $alert->user_location_lat }},{{ $alert->user_location_lng }}&zoom=15"
                                allowfullscreen>
                            </iframe>
                        </div>
                        <a href="https://www.google.com/maps?q={{ $alert->user_location_lat }},{{ $alert->user_location_lng }}"
                            target="_blank" class="btn btn-outline-primary btn-block">
                            <i class="tio-map"></i> {{ 'Abrir en Google Maps' }}
                        </a>
                    @else
                        <p class="text-muted">{{ 'No hay datos de ubicación disponibles' }}</p>
                    @endif
                </div>

                <!-- Ride Info -->
                <div class="info-card">
                    <h6><i class="tio-car mr-2"></i>{{ 'Información de viaje' }}</h6>
                    <div class="info-item">
                        <span>{{ 'ID de viaje' }}</span>
                        <a href="{{ route('admin.taxi.rides.details', $alert->taxi_ride_id) }}" class="text-primary">
                            #{{ $alert->taxi_ride_id }}
                        </a>
                    </div>
                    <div class="info-item">
                        <span>{{ 'Estado' }}</span>
                        <span class="badge badge-info">{{ $alert->taxiRide->status }}</span>
                    </div>
                    <div class="info-item">
                        <span>{{ 'Levantar' }}</span>
                        <span class="text-right" style="max-width: 200px;">{{ $alert->taxiRide->pickup_address }}</span>
                    </div>
                    <div class="info-item">
                        <span>{{ 'Dejar' }}</span>
                        <span class="text-right" style="max-width: 200px;">{{ $alert->taxiRide->dropoff_address }}</span>
                    </div>
                </div>

                <!-- Admin Notes -->
                @if($alert->admin_notes)
                    <div class="info-card">
                        <h6><i class="tio-document-text mr-2"></i>{{ 'Notas de administrador' }}</h6>
                        <p class="mb-0">{{ $alert->admin_notes }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="info-card action-buttons">
            <h6><i class="tio-flash mr-2"></i>{{ 'Comportamiento' }}</h6>

            @if($alert->status == 'pending')
                <form action="{{ route('admin.taxi.safety.contact', $alert->id) }}" method="POST" class="d-inline">
                    @csrf
                    <input type="hidden" name="notes" value="Contacted user via phone">
                    <button type="submit" class="btn btn-info">
                        <i class="tio-call"></i> {{ 'Marcar como contactado' }}
                    </button>
                </form>
            @endif

            @if($alert->status != 'resolved')
                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#resolveModal">
                    <i class="tio-checkmark-circle"></i> {{ 'Marcar como resuelto' }}
                </button>
            @endif

            @if($alert->alert_type == 'emergency' && $alert->status != 'escalated')
                <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#escalateModal">
                    <i class="tio-warning"></i> {{ 'Escalar a las autoridades' }}
                </button>
            @endif

            <a href="{{ route('admin.taxi.safety.report', $alert->id) }}" target="_blank" class="btn btn-secondary">
                <i class="tio-download"></i> {{ 'Generar informe' }}
            </a>
        </div>

        <!-- Recordings -->
        @if($recordings->count() > 0)
            <div class="info-card">
                <h6><i class="tio-mic mr-2"></i>{{ 'Grabaciones de audio' }}</h6>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>{{ 'Fecha' }}</th>
                                <th>{{ 'Duración' }}</th>
                                <th>{{ 'Tamaño' }}</th>
                                <th>{{ 'Acción' }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recordings as $recording)
                                <tr>
                                    <td>{{ $recording->created_at->format('d/m/Y H:i') }}</td>
                                    <td>{{ $recording->formatted_duration }}</td>
                                    <td>{{ $recording->file_size_kb }} KB</td>
                                    <td>
                                        <audio controls style="height: 30px;">
                                            <source src="{{ $recording->audio_url }}" type="audio/mpeg">
                                        </audio>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

    <!-- Resolve Modal -->
    <div class="modal fade" id="resolveModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('admin.taxi.safety.resolve', $alert->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">{{ 'Resolver alerta' }}</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>{{ 'Notas de resolución' }}</label>
                            <textarea name="notes" class="form-control" rows="3"
                                placeholder="{{ 'Ingrese los detalles de la resolución...' }}"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            data-dismiss="modal">{{ 'Cancelar' }}</button>
                        <button type="submit" class="btn btn-success">{{ 'Marcar como resuelto' }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Escalate Modal -->
    <div class="modal fade" id="escalateModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('admin.taxi.safety.escalate', $alert->id) }}" method="POST">
                    @csrf
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">{{ 'Escalar a las autoridades' }}</h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger">
                            <strong>{{ '¡Advertencia!' }}</strong>
                            {{ 'Esta acción indica que el caso ha sido denunciado a las autoridades.' }}
                        </div>
                        <div class="form-group">
                            <label>{{ 'Notas de escalada' }}</label>
                            <textarea name="notes" class="form-control" rows="3"
                                placeholder="{{ 'Ingrese detalles sobre la escalada...' }}"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            data-dismiss="modal">{{ 'Cancelar' }}</button>
                        <button type="submit" class="btn btn-danger">{{ 'Escalar' }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection