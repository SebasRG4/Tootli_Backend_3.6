@extends('layouts.admin.app')

@section('title', 'Simulador de Conductor de Taxi')

@push('css_or_js')
    <style>
        .simulator-container {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        #simulator-map {
            height: 480px;
            width: 100%;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        .trip-card {
            cursor: pointer;
            transition: all 0.25s ease;
            border-radius: 12px;
            border: 1px solid #e9ecef;
            position: relative;
            overflow: hidden;
        }

        .trip-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
            border-color: #005555;
        }

        .trip-card.selected {
            border: 2px solid #005555;
            background-color: #f4faf8;
            box-shadow: 0 6px 20px rgba(0, 85, 85, 0.15);
        }

        .trip-card.selected::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 5px;
            background: #005555;
        }

        .pulse-pending {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #ff9800;
            box-shadow: 0 0 0 0 rgba(255, 152, 0, 0.7);
            animation: pulse 1.5s infinite;
            margin-right: 6px;
        }

        .pulse-live {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #10b981;
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            animation: pulse 2s infinite;
            margin-right: 6px;
        }

        @keyframes pulse {
            0% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(255, 152, 0, 0.7);
            }
            70% {
                transform: scale(1);
                box-shadow: 0 0 0 8px rgba(255, 152, 0, 0);
            }
            100% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(255, 152, 0, 0);
            }
        }

        .badge-status {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            padding: 4px 10px;
            border-radius: 20px;
            letter-spacing: 0.5px;
        }

        .badge-pending { background: #fff3e0; color: #e65100; border: 1px solid #ffe0b2; }
        .badge-accepted { background: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; }
        .badge-arriving { background: #e1f5fe; color: #0277bd; border: 1px solid #b3e5fc; }
        .badge-arrived { background: #ede7f6; color: #512da8; border: 1px solid #d1c4e9; }
        .badge-in_progress { background: #e0f2f1; color: #00695c; border: 1px solid #b2dfdb; }
        .badge-completed { background: #f5f5f5; color: #424242; border: 1px solid #e0e0e0; }
        .badge-cancelled { background: #ffebee; color: #c62828; border: 1px solid #ffcdd2; }

        .control-panel {
            background: #ffffff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
            border: 1px solid #edf2f7;
        }

        .step-timeline {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            position: relative;
        }

        .step-timeline::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 20px;
            right: 20px;
            height: 3px;
            background: #e2e8f0;
            z-index: 1;
        }

        .step-node {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1;
        }

        .step-circle {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #ffffff;
            border: 2px solid #cbd5e1;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 700;
            color: #64748b;
            transition: all 0.3s;
        }

        .step-node.active .step-circle {
            background: #005555;
            border-color: #005555;
            color: #ffffff;
            box-shadow: 0 0 0 4px rgba(0, 85, 85, 0.2);
        }

        .step-node.passed .step-circle {
            background: #10b981;
            border-color: #10b981;
            color: #ffffff;
        }

        .step-label {
            font-size: 11px;
            font-weight: 600;
            color: #64748b;
            margin-top: 6px;
            text-align: center;
        }

        .step-node.active .step-label {
            color: #005555;
            font-weight: 700;
        }

        .action-card {
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            padding: 16px;
            background: #f8fafc;
            margin-bottom: 16px;
        }
    </style>
@endpush

@section('content')
    <div class="content container-fluid simulator-container">
        <!-- Page Header -->
        <div class="page-header mb-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <div>
                    <h1 class="page-header-title d-flex align-items-center mb-1">
                        <span class="page-header-icon mr-2">
                            <i class="tio-car text-primary" style="font-size: 24px;"></i>
                        </span>
                        <span>Simulador de Conductor de Taxi</span>
                    </h1>
                    <div class="d-flex align-items-center">
                        <span class="pulse-live"></span>
                        <span class="text-muted small">Escuchando viajes en vivo cada 4s (Modo Pruebas)</span>
                    </div>
                </div>
                <div class="d-flex mt-2 mt-md-0">
                    <button class="btn btn-outline-secondary btn-sm mr-2" onclick="refreshTrips(true)">
                        <i class="tio-refresh"></i> Actualizar
                    </button>
                    <button class="btn btn-primary btn-sm" onclick="createTestTrip()">
                        <i class="tio-add"></i> Crear Viaje de Prueba
                    </button>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Left Panel: Active Trips List -->
            <div class="col-lg-4 mb-3">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-header py-3 bg-white d-flex justify-content-between align-items-center border-bottom">
                        <h5 class="card-title mb-0 font-weight-bold text-dark">
                            <i class="tio-route mr-1 text-primary"></i> Solicitudes Activas
                        </h5>
                        <span class="badge badge-pill badge-primary" id="trip-count-badge">
                            {{ $activeTrips->count() }}
                        </span>
                    </div>

                    <div class="card-body p-3" id="trips-container" style="max-height: 760px; overflow-y: auto;">
                        @forelse($activeTrips as $trip)
                            <div class="trip-card card mb-3 {{ $loop->first ? 'selected' : '' }}" 
                                 data-trip-id="{{ $trip->id }}"
                                 onclick="selectTrip({{ $trip->id }})">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <span class="font-weight-bold text-dark">Viaje #{{ $trip->id }}</span>
                                            @if($trip->status === 'pending')
                                                <span class="pulse-pending ml-1"></span>
                                            @endif
                                        </div>
                                        <span class="badge-status badge-{{ $trip->status }}">
                                            {{ ucfirst($trip->status) }}
                                        </span>
                                    </div>

                                    <div class="d-flex align-items-center mb-2">
                                        <div class="avatar avatar-xs avatar-circle mr-2 bg-soft-primary text-primary font-weight-bold d-flex align-items-center justify-content-center">
                                            {{ strtoupper(substr($trip->user->f_name ?? 'U', 0, 1)) }}
                                        </div>
                                        <div class="small">
                                            <span class="font-weight-bold text-dark">{{ $trip->user->f_name ?? 'Usuario' }} {{ $trip->user->l_name ?? '' }}</span>
                                            <span class="text-muted ml-1">({{ $trip->user->phone ?? 'Sin tel' }})</span>
                                        </div>
                                    </div>

                                    <div class="small text-muted mb-2">
                                        <div class="text-truncate mb-1">
                                            <i class="tio-record-outlined text-success mr-1"></i>
                                            <strong>Origen:</strong> {{ Str::limit($trip->pickup_address, 34) }}
                                        </div>
                                        <div class="text-truncate">
                                            <i class="tio-poi text-danger mr-1"></i>
                                            <strong>Destino:</strong> {{ Str::limit($trip->dropoff_address, 34) }}
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center pt-2 border-top small">
                                        <span class="font-weight-bold text-primary">
                                            ${{ number_format($trip->estimated_fare, 2) }} MXN
                                        </span>
                                        <span class="text-muted font-weight-bold">
                                            {{ $trip->vehicle_type ?? 'Standard' }}
                                        </span>
                                        <span class="text-muted">
                                            {{ $trip->created_at->diffForHumans() }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted py-5" id="empty-state">
                                <div class="mb-3">
                                    <i class="tio-car-outlined text-muted" style="font-size: 54px; opacity: 0.4;"></i>
                                </div>
                                <h6 class="font-weight-bold text-dark">Sin solicitudes activas</h6>
                                <p class="small text-muted mb-3 px-3">
                                    Pide un viaje desde la app de usuario o presiona <strong>"Crear Viaje de Prueba"</strong> para simular uno inmediatamente.
                                </p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Right Panel: Map & Action Controls -->
            <div class="col-lg-8">
                <!-- Google Map -->
                <div class="card mb-3 shadow-sm border-0">
                    <div class="card-body p-0 position-relative">
                        <div id="simulator-map"></div>
                        <!-- Map Floating Legend -->
                        <div class="position-absolute bg-white px-3 py-2 rounded shadow-sm small" 
                             style="bottom: 16px; left: 16px; z-index: 5; border: 1px solid #e2e8f0;">
                            <span class="mr-3"><span style="color: #10b981; font-weight: bold;">●</span> Recogida</span>
                            <span class="mr-3"><span style="color: #ef4444; font-weight: bold;">●</span> Destino</span>
                            <span><span style="color: #005555; font-weight: bold;">🚗</span> Conductor</span>
                        </div>
                    </div>
                </div>

                <!-- Control Panel -->
                <div class="control-panel" id="control-panel" style="display: none;">
                    <!-- Step Timeline -->
                    <div class="step-timeline" id="step-timeline">
                        <div class="step-node" id="step-node-pending">
                            <div class="step-circle">1</div>
                            <div class="step-label">Solicitado</div>
                        </div>
                        <div class="step-node" id="step-node-accepted">
                            <div class="step-circle">2</div>
                            <div class="step-label">Aceptado</div>
                        </div>
                        <div class="step-node" id="step-node-arriving">
                            <div class="step-circle">3</div>
                            <div class="step-label">En Camino</div>
                        </div>
                        <div class="step-node" id="step-node-arrived">
                            <div class="step-circle">4</div>
                            <div class="step-label">En Espera</div>
                        </div>
                        <div class="step-node" id="step-node-in_progress">
                            <div class="step-circle">5</div>
                            <div class="step-label">En Viaje</div>
                        </div>
                        <div class="step-node" id="step-node-completed">
                            <div class="step-circle">6</div>
                            <div class="step-label">Completado</div>
                        </div>
                    </div>

                    <!-- Selected Trip Summary Card -->
                    <div class="action-card d-flex flex-wrap justify-content-between align-items-center mb-3" id="trip-header-summary">
                        <div>
                            <span class="badge-status" id="panel-status-badge">PENDING</span>
                            <h5 class="mb-1 font-weight-bold text-dark mt-1" id="panel-trip-title">Viaje #</h5>
                            <div class="small text-muted" id="panel-trip-sub">Pasajero: -</div>
                        </div>
                        <div class="text-right mt-2 mt-md-0">
                            <div class="h4 font-weight-bold text-primary mb-0" id="panel-trip-fare">$0.00 MXN</div>
                            <div class="small text-muted" id="panel-trip-eta">ETA: Calculando...</div>
                        </div>
                    </div>

                    <!-- PHASE 1: ACCEPT TRIP (Pending) -->
                    <div id="section-accept" style="display: none;">
                        <div class="card border-primary p-3 mb-3 bg-soft-primary">
                            <h6 class="font-weight-bold text-dark mb-2">
                                <i class="tio-checkmark-circle-outlined text-primary mr-1"></i> Asignar Conductor y Aceptar Viaje
                            </h6>
                            <p class="small text-muted mb-3">
                                Selecciona el conductor para simular la aceptación. Puedes aceptar directamente y el sistema ubicará al conductor a ~500m con ruta real hacia el pasajero, o hacer clic en el mapa para ubicarlo manualmente.
                            </p>

                            <div class="row align-items-end">
                                <div class="col-md-7 mb-2 mb-md-0">
                                    <label class="font-weight-bold small text-dark mb-1">Conductor de Taxi:</label>
                                    <select class="form-control" id="driver-select">
                                        @foreach($allDrivers as $driver)
                                            <option value="{{ $driver->id }}" {{ $loop->first ? 'selected' : '' }}>
                                                {{ $driver->f_name }} {{ $driver->l_name }} 
                                                @if($driver->vehicle)
                                                    ({{ $driver->vehicle->brand }} {{ $driver->vehicle->model }})
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <button class="btn btn-success btn-block" id="btn-quick-accept" onclick="acceptTrip()">
                                        <i class="tio-done-vs mr-1"></i> Aceptar Viaje
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PHASE 2: IN-FLIGHT CONTROLS (Accepted, Arriving, Arrived, In Progress) -->
                    <div id="section-actions" style="display: none;">
                        <!-- Step Specific Action Prompts -->
                        <div class="action-card mb-3" id="step-action-box">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="font-weight-bold text-dark mb-0" id="step-action-title">Control del Conductor</h6>
                                <span class="badge badge-soft-dark" id="step-action-badge">Fase Activa</span>
                            </div>
                            <p class="small text-muted mb-3" id="step-action-desc">
                                Usa los controles a continuación para avanzar al conductor y cambiar de fase.
                            </p>

                            <!-- Primary Workflow Quick Action Buttons -->
                            <div class="d-flex flex-wrap gap-2 mb-3">
                                <!-- When Accepted or Arriving -->
                                <button class="btn btn-outline-info mr-2 mb-2" id="btn-step-move" onclick="moveDriverStep()">
                                    <i class="tio-arrow-forward mr-1"></i> Avanzar 1 Paso
                                </button>
                                <button class="btn btn-primary mr-2 mb-2" id="btn-autopilot" onclick="toggleAutoPilot()">
                                    <i class="tio-play-circle mr-1" id="autopilot-icon"></i> <span id="autopilot-text">Piloto Automático</span>
                                </button>
                                <button class="btn btn-success mr-2 mb-2" id="btn-mark-arrived" onclick="changeStatus('arrived')" style="display: none;">
                                    <i class="tio-poi mr-1"></i> Llegó al Pasajero (Arrived)
                                </button>

                                <!-- When Arrived -->
                                <button class="btn btn-warning text-dark font-weight-bold mr-2 mb-2" id="btn-start-trip" onclick="startTripWithOtpPrompt()" style="display: none;">
                                    <i class="tio-lock mr-1"></i> Iniciar Viaje con PIN (4 dígitos)
                                </button>

                                <!-- When In Progress -->
                                <button class="btn btn-dark mr-2 mb-2" id="btn-complete-trip" onclick="changeStatus('completed')" style="display: none;">
                                    <i class="tio-checkmark-square mr-1"></i> Finalizar y Completar Viaje
                                </button>
                            </div>

                            <!-- Speed Setting for Autopilot -->
                            <div class="row align-items-center pt-2 border-top">
                                <div class="col-auto small font-weight-bold text-muted">
                                    Velocidad del Piloto:
                                </div>
                                <div class="col-auto">
                                    <div class="btn-group btn-group-sm btn-group-toggle" data-toggle="buttons">
                                        <label class="btn btn-outline-secondary">
                                            <input type="radio" name="speed" value="slow" id="speed-slow"> Lento
                                        </label>
                                        <label class="btn btn-outline-secondary active">
                                            <input type="radio" name="speed" value="normal" id="speed-normal" checked> Normal
                                        </label>
                                        <label class="btn btn-outline-secondary">
                                            <input type="radio" name="speed" value="fast" id="speed-fast"> Rápido
                                        </label>
                                    </div>
                                </div>
                                <div class="col text-right">
                                    <button class="btn btn-outline-danger btn-sm" onclick="cancelTripModal()">
                                        <i class="tio-clear"></i> Cancelar Viaje
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PHASE 3: COMPLETED TRIP VIEW -->
                    <div id="section-completed" style="display: none;">
                        <div class="alert alert-success d-flex align-items-center p-3 mb-0">
                            <i class="tio-checkmark-circle mr-3" style="font-size: 32px;"></i>
                            <div>
                                <h6 class="font-weight-bold mb-1">¡Viaje Finalizado Exitosamente!</h6>
                                <p class="small mb-0">
                                    El viaje ha sido completado. La app de usuario ha recibido el resumen de cobro y la solicitud de calificación.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
    <script src="https://maps.googleapis.com/maps/api/js?key={{ $mapApiKey }}&libraries=places,geometry"></script>
    <script>
        let map;
        let selectedTrip = null;
        let driverMarker = null;
        let originMarker = null;
        let destinationMarker = null;
        let routePolyline = null;
        let autopilotInterval = null;
        let customDriverLocation = null;
        let pollTimer = null;
        let previousPendingCount = {{ $activeTrips->where('status', 'pending')->count() }};

        // Initialize Google Map
        function initMap() {
            const defaultCenter = { lat: 19.432608, lng: -99.133209 }; // CDMX
            map = new google.maps.Map(document.getElementById('simulator-map'), {
                center: defaultCenter,
                zoom: 13,
                mapTypeControl: false,
                streetViewControl: false,
                fullscreenControl: true,
                styles: [
                    { featureType: "poi", elementType: "labels", stylers: [{ visibility: "off" }] }
                ]
            });

            // Map click listener: allow positioning driver manually
            map.addListener('click', function(event) {
                if (!selectedTrip) return;

                const lat = event.latLng.lat();
                const lng = event.latLng.lng();

                if (selectedTrip.status === 'pending') {
                    customDriverLocation = { lat, lng };
                    placeDriverMarker(customDriverLocation, 'Ubicación inicial seleccionada');
                    toastr.info('Ubicación inicial fijada en el mapa. Haz clic en "Aceptar Viaje".');
                } else if (['accepted', 'arriving', 'in_progress'].includes(selectedTrip.status)) {
                    updateDriverLocation(lat, lng);
                }
            });

            // If there's already an active trip, select it
            @if($activeTrips->isNotEmpty())
                selectTrip({{ $activeTrips->first()->id }});
            @endif

            // Start live polling
            startLivePolling();
        }

        // Live polling every 4 seconds
        function startLivePolling() {
            if (pollTimer) clearInterval(pollTimer);
            pollTimer = setInterval(() => {
                refreshTrips(false);
            }, 4000);
        }

        // Refresh trips from server
        function refreshTrips(manual = false) {
            fetch('{{ route('admin.taxi.simulator.active-trips') }}')
                .then(r => r.json())
                .then(data => {
                    if (!data.success) return;

                    const trips = data.trips;
                    document.getElementById('trip-count-badge').textContent = data.count;

                    // Check for new pending trips to alert user
                    const pendingCount = trips.filter(t => t.status === 'pending').length;
                    if (pendingCount > previousPendingCount) {
                        playChimeSound();
                        toastr.success('¡Nueva solicitud de viaje entrante desde la app de usuario!');
                    }
                    previousPendingCount = pendingCount;

                    renderTripCards(trips);

                    // If currently selected trip was updated, update its UI
                    if (selectedTrip) {
                        const updated = trips.find(t => t.id === selectedTrip.id);
                        if (updated && (updated.status !== selectedTrip.status || updated.driver_current_lat !== selectedTrip.driver_current_lat)) {
                            selectedTrip = updated;
                            updateControlPanel();
                            updateMapMarkers();
                        }
                    } else if (trips.length > 0 && manual) {
                        selectTrip(trips[0].id);
                    }

                    if (manual) toastr.info('Lista de viajes actualizada');
                })
                .catch(err => console.error('Error polling trips:', err));
        }

        // Render trip cards dynamically in the left panel
        function renderTripCards(trips) {
            const container = document.getElementById('trips-container');
            if (!trips || trips.length === 0) {
                container.innerHTML = `
                    <div class="text-center text-muted py-5" id="empty-state">
                        <div class="mb-3">
                            <i class="tio-car-outlined text-muted" style="font-size: 54px; opacity: 0.4;"></i>
                        </div>
                        <h6 class="font-weight-bold text-dark">Sin solicitudes activas</h6>
                        <p class="small text-muted mb-3 px-3">
                            Pide un viaje desde la app de usuario o presiona <strong>"Crear Viaje de Prueba"</strong>.
                        </p>
                    </div>`;
                return;
            }

            let html = '';
            trips.forEach(trip => {
                const isSelected = selectedTrip && selectedTrip.id === trip.id;
                const userName = (trip.user?.f_name || 'Usuario') + ' ' + (trip.user?.l_name || '');
                const userInitial = (trip.user?.f_name || 'U').charAt(0).toUpperCase();
                const userPhone = trip.user?.phone || 'Sin tel';
                const fare = parseFloat(trip.estimated_fare || 0).toFixed(2);
                const isPending = trip.status === 'pending';

                html += `
                    <div class="trip-card card mb-3 ${isSelected ? 'selected' : ''}" 
                         data-trip-id="${trip.id}"
                         onclick="selectTrip(${trip.id})">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <span class="font-weight-bold text-dark">Viaje #${trip.id}</span>
                                    ${isPending ? '<span class="pulse-pending ml-1"></span>' : ''}
                                </div>
                                <span class="badge-status badge-${trip.status}">
                                    ${trip.status}
                                </span>
                            </div>

                            <div class="d-flex align-items-center mb-2">
                                <div class="avatar avatar-xs avatar-circle mr-2 bg-soft-primary text-primary font-weight-bold d-flex align-items-center justify-content-center">
                                    ${userInitial}
                                </div>
                                <div class="small">
                                    <span class="font-weight-bold text-dark">${userName}</span>
                                    <span class="text-muted ml-1">(${userPhone})</span>
                                </div>
                            </div>

                            <div class="small text-muted mb-2">
                                <div class="text-truncate mb-1">
                                    <i class="tio-record-outlined text-success mr-1"></i>
                                    <strong>Origen:</strong> ${trip.pickup_address || ''}
                                </div>
                                <div class="text-truncate">
                                    <i class="tio-poi text-danger mr-1"></i>
                                    <strong>Destino:</strong> ${trip.dropoff_address || ''}
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center pt-2 border-top small">
                                <span class="font-weight-bold text-primary">$${fare} MXN</span>
                                <span class="text-muted font-weight-bold">${trip.vehicle_type || 'Standard'}</span>
                                <span class="text-muted">${trip.created_at ? formatTimeAgo(trip.created_at) : ''}</span>
                            </div>
                        </div>
                    </div>`;
            });

            container.innerHTML = html;
        }

        // Select Trip
        function selectTrip(tripId) {
            fetch(`/admin/taxi/simulator/trip/${tripId}`)
                .then(r => r.json())
                .then(data => {
                    if (!data.success) return;
                    selectedTrip = data.trip;
                    customDriverLocation = null;

                    // Update left panel card selection styles
                    document.querySelectorAll('.trip-card').forEach(c => c.classList.remove('selected'));
                    const activeCard = document.querySelector(`.trip-card[data-trip-id="${tripId}"]`);
                    if (activeCard) activeCard.classList.add('selected');

                    // Show and update control panel
                    document.getElementById('control-panel').style.display = 'block';
                    updateControlPanel();
                    updateMapMarkers();
                });
        }

        // Update control panel elements based on selectedTrip
        function updateControlPanel() {
            if (!selectedTrip) return;

            const st = selectedTrip.status;
            const fare = parseFloat(selectedTrip.estimated_fare || 0).toFixed(2);
            const userName = (selectedTrip.user?.f_name || 'Usuario') + ' ' + (selectedTrip.user?.l_name || '');

            // Header summary
            document.getElementById('panel-trip-title').textContent = `Viaje #${selectedTrip.id} (${selectedTrip.vehicle_type || 'Standard'})`;
            document.getElementById('panel-trip-sub').textContent = `Pasajero: ${userName} | Tel: ${selectedTrip.user?.phone || 'N/A'}`;
            document.getElementById('panel-trip-fare').textContent = `$${fare} MXN`;

            const etaBadge = document.getElementById('panel-trip-eta');
            if (selectedTrip.eta_minutes) {
                etaBadge.textContent = `ETA: ${selectedTrip.eta_minutes} min (~${selectedTrip.distance_to_pickup_km || 0} km)`;
            } else {
                etaBadge.textContent = st === 'completed' ? 'Completado' : 'ETA: En espera';
            }

            const badge = document.getElementById('panel-status-badge');
            badge.textContent = st.toUpperCase();
            badge.className = `badge-status badge-${st}`;

            // Update step timeline nodes
            const steps = ['pending', 'accepted', 'arriving', 'arrived', 'in_progress', 'completed'];
            const currentIndex = steps.indexOf(st);

            steps.forEach((step, idx) => {
                const node = document.getElementById(`step-node-${step}`);
                if (!node) return;
                node.classList.remove('active', 'passed');
                if (idx === currentIndex) {
                    node.classList.add('active');
                } else if (idx < currentIndex) {
                    node.classList.add('passed');
                }
            });

            // Sections toggle
            const secAccept = document.getElementById('section-accept');
            const secActions = document.getElementById('section-actions');
            const secCompleted = document.getElementById('section-completed');

            secAccept.style.display = 'none';
            secActions.style.display = 'none';
            secCompleted.style.display = 'none';

            if (st === 'pending') {
                secAccept.style.display = 'block';
            } else if (st === 'completed') {
                secCompleted.style.display = 'block';
                stopAutoPilot();
            } else {
                secActions.style.display = 'block';
                configureActionButtons(st);
            }
        }

        // Configure dynamic action buttons based on status
        function configureActionButtons(status) {
            const btnStepMove = document.getElementById('btn-step-move');
            const btnAutopilot = document.getElementById('btn-autopilot');
            const btnMarkArrived = document.getElementById('btn-mark-arrived');
            const btnStartTrip = document.getElementById('btn-start-trip');
            const btnCompleteTrip = document.getElementById('btn-complete-trip');
            const actionTitle = document.getElementById('step-action-title');
            const actionDesc = document.getElementById('step-action-desc');

            btnMarkArrived.style.display = 'none';
            btnStartTrip.style.display = 'none';
            btnCompleteTrip.style.display = 'none';

            if (status === 'accepted' || status === 'arriving') {
                actionTitle.textContent = 'En camino al punto de recogida';
                actionDesc.textContent = 'Avanza hacia el pasajero con el piloto automático o marca llegada directa.';
                btnMarkArrived.style.display = 'inline-block';
                btnStepMove.style.display = 'inline-block';
                btnAutopilot.style.display = 'inline-block';
            } else if (status === 'arrived') {
                actionTitle.textContent = 'Conductor en el punto de recogida (Esperando Pasajero)';
                actionDesc.textContent = 'El conductor ha llegado. Para iniciar el viaje, solicita al pasajero su PIN de 4 dígitos e ingrésalo a continuación para verificar su identidad.';
                btnStartTrip.style.display = 'inline-block';
                btnStepMove.style.display = 'none';
                btnAutopilot.style.display = 'none';
                stopAutoPilot();
            } else if (status === 'in_progress') {
                actionTitle.textContent = 'Viaje en curso hacia el destino';
                actionDesc.textContent = 'El pasajero está a bordo. Avanza hacia el destino o finaliza el viaje.';
                btnStepMove.style.display = 'inline-block';
                btnAutopilot.style.display = 'inline-block';
                btnCompleteTrip.style.display = 'inline-block';
            }
        }

        // Map updates
        function updateMapMarkers() {
            if (!selectedTrip || !map) return;

            // Clear previous markers
            if (originMarker) originMarker.setMap(null);
            if (destinationMarker) destinationMarker.setMap(null);
            if (driverMarker) driverMarker.setMap(null);
            if (routePolyline) routePolyline.setMap(null);

            const bounds = new google.maps.LatLngBounds();

            // Pickup Origin Marker
            const originPos = {
                lat: parseFloat(selectedTrip.pickup_lat),
                lng: parseFloat(selectedTrip.pickup_lng)
            };
            originMarker = new google.maps.Marker({
                position: originPos,
                map: map,
                icon: {
                    path: google.maps.SymbolPath.CIRCLE,
                    scale: 9,
                    fillColor: '#10b981',
                    fillOpacity: 1,
                    strokeColor: '#ffffff',
                    strokeWeight: 2
                },
                title: 'Recogida: ' + selectedTrip.pickup_address
            });
            bounds.extend(originPos);

            // Dropoff Destination Marker
            const destPos = {
                lat: parseFloat(selectedTrip.dropoff_lat),
                lng: parseFloat(selectedTrip.dropoff_lng)
            };
            destinationMarker = new google.maps.Marker({
                position: destPos,
                map: map,
                icon: {
                    path: google.maps.SymbolPath.CIRCLE,
                    scale: 9,
                    fillColor: '#ef4444',
                    fillOpacity: 1,
                    strokeColor: '#ffffff',
                    strokeWeight: 2
                },
                title: 'Destino: ' + selectedTrip.dropoff_address
            });
            bounds.extend(destPos);

            // Driver Marker (if driver is located)
            if (selectedTrip.driver_current_lat && selectedTrip.driver_current_lng) {
                const driverPos = {
                    lat: parseFloat(selectedTrip.driver_current_lat),
                    lng: parseFloat(selectedTrip.driver_current_lng)
                };
                placeDriverMarker(driverPos, 'Conductor asignado');
                bounds.extend(driverPos);

                // Draw connecting line to target
                const targetPos = selectedTrip.status === 'in_progress' ? destPos : originPos;
                routePolyline = new google.maps.Polyline({
                    path: [driverPos, targetPos],
                    geodesic: true,
                    strokeColor: selectedTrip.status === 'in_progress' ? '#0284c7' : '#005555',
                    strokeOpacity: 0.8,
                    strokeWeight: 4,
                    map: map
                });
            }

            map.fitBounds(bounds, { top: 40, right: 40, bottom: 40, left: 40 });
        }

        // Place or move driver marker
        function placeDriverMarker(pos, title = 'Conductor') {
            if (driverMarker) {
                driverMarker.setPosition(pos);
            } else {
                driverMarker = new google.maps.Marker({
                    position: pos,
                    map: map,
                    draggable: true,
                    icon: {
                        path: google.maps.SymbolPath.FORWARD_CLOSED_ARROW,
                        scale: 6,
                        fillColor: '#005555',
                        fillOpacity: 1,
                        strokeColor: '#ffffff',
                        strokeWeight: 2,
                        rotation: 0
                    },
                    title: title
                });

                // Marker drag listener
                driverMarker.addListener('dragend', function(e) {
                    if (selectedTrip && selectedTrip.status !== 'pending') {
                        updateDriverLocation(e.latLng.lat(), e.latLng.lng());
                    } else if (selectedTrip && selectedTrip.status === 'pending') {
                        customDriverLocation = { lat: e.latLng.lat(), lng: e.latLng.lng() };
                    }
                });
            }
        }

        // Action: Accept Trip
        function acceptTrip() {
            if (!selectedTrip) return;

            const driverId = document.getElementById('driver-select').value;
            const btn = document.getElementById('btn-quick-accept');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm mr-1"></span> Aceptando...';

            const payload = {
                driver_id: driverId,
                initial_lat: customDriverLocation ? customDriverLocation.lat : null,
                initial_lng: customDriverLocation ? customDriverLocation.lng : null
            };

            fetch(`/admin/taxi/simulator/trip/${selectedTrip.id}/accept`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(payload)
            })
            .then(r => r.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = '<i class="tio-done-vs mr-1"></i> Aceptar Viaje';

                if (data.success) {
                    toastr.success(data.message);
                    selectedTrip = data.trip;
                    updateControlPanel();
                    updateMapMarkers();
                    refreshTrips(false);
                } else {
                    toastr.error(data.message || 'Error al aceptar viaje');
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = '<i class="tio-done-vs mr-1"></i> Aceptar Viaje';
                toastr.error('Error de red al aceptar viaje');
            });
        }

        // Action: Move Driver Step
        function moveDriverStep() {
            if (!selectedTrip) return;
            const speed = document.querySelector('input[name="speed"]:checked')?.value || 'normal';

            fetch(`/admin/taxi/simulator/trip/${selectedTrip.id}/simulate-movement`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ speed })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    selectedTrip = data.trip;
                    updateControlPanel();
                    updateMapMarkers();
                    if (data.arrived) {
                        stopAutoPilot();
                        toastr.success(data.trip.status === 'completed' ? '¡Llegó al destino! Viaje completado.' : '¡Llegó al punto de recogida!');
                    }
                }
            });
        }

        // Action: Toggle Autopilot
        function toggleAutoPilot() {
            if (autopilotInterval) {
                stopAutoPilot();
            } else {
                startAutoPilot();
            }
        }

        function startAutoPilot() {
            const btn = document.getElementById('btn-autopilot');
            const icon = document.getElementById('autopilot-icon');
            const text = document.getElementById('autopilot-text');

            btn.className = 'btn btn-danger mr-2 mb-2';
            icon.className = 'tio-pause-circle mr-1';
            text.textContent = 'Pausar Piloto';

            autopilotInterval = setInterval(() => {
                moveDriverStep();
            }, 2000);

            toastr.info('Piloto automático iniciado (actualizando cada 2s)');
        }

        function stopAutoPilot() {
            if (autopilotInterval) {
                clearInterval(autopilotInterval);
                autopilotInterval = null;
            }
            const btn = document.getElementById('btn-autopilot');
            const icon = document.getElementById('autopilot-icon');
            const text = document.getElementById('autopilot-text');

            if (btn) {
                btn.className = 'btn btn-primary mr-2 mb-2';
                icon.className = 'tio-play-circle mr-1';
                text.textContent = 'Piloto Automático';
            }
        }

        // Action: Start trip with passenger OTP
        function startTripWithOtpPrompt() {
            if (!selectedTrip) return;
            const otp = prompt('🔒 Ingresa el código PIN de 4 dígitos proporcionado por el pasajero al abordar para iniciar el viaje:');
            if (otp === null) return;
            if (!otp.trim()) {
                toastr.warning('Debes ingresar el PIN de 4 dígitos para iniciar el viaje');
                return;
            }
            changeStatus('in_progress', otp.trim());
        }

        // Action: Change Status
        function changeStatus(status, otp = null) {
            if (!selectedTrip) return;

            const payload = { status: status };
            if (otp) payload.otp = otp;

            fetch(`/admin/taxi/simulator/trip/${selectedTrip.id}/change-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(payload)
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    toastr.success(data.message);
                    selectedTrip = data.trip;
                    updateControlPanel();
                    updateMapMarkers();
                    refreshTrips(false);
                } else {
                    toastr.error(data.message || 'Error al cambiar estado');
                }
            });
        }

        // Action: Update Driver Location manually
        function updateDriverLocation(lat, lng) {
            if (!selectedTrip) return;

            fetch(`/admin/taxi/simulator/trip/${selectedTrip.id}/update-location`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ lat, lng })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    selectedTrip = data.trip;
                    updateControlPanel();
                    updateMapMarkers();
                }
            });
        }

        // Action: Create Test Trip
        function createTestTrip() {
            fetch('{{ route('admin.taxi.simulator.create-test-trip') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    toastr.success(data.message);
                    refreshTrips(true);
                    selectTrip(data.trip.id);
                } else {
                    toastr.error(data.message || 'Error al crear viaje de prueba');
                }
            });
        }

        // Action: Cancel Trip Modal
        function cancelTripModal() {
            if (!selectedTrip) return;
            const reason = prompt('Motivo de cancelación:', 'Cancelado por conductor desde simulador');
            if (reason !== null) {
                fetch(`/admin/taxi/simulator/trip/${selectedTrip.id}/change-status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ status: 'cancelled', reason: reason })
                })
                .then(r => r.json())
                .then(data => {
                    toastr.warning('Viaje cancelado.');
                    selectedTrip = data.trip;
                    updateControlPanel();
                    updateMapMarkers();
                    refreshTrips(false);
                });
            }
        }

        // Audio notification helper
        function playChimeSound() {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.type = 'sine';
                osc.frequency.setValueAtTime(587.33, ctx.currentTime); // D5
                osc.frequency.setValueAtTime(880.00, ctx.currentTime + 0.15); // A5
                gain.gain.setValueAtTime(0.2, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.4);
                osc.start();
                osc.stop(ctx.currentTime + 0.4);
            } catch (e) {}
        }

        // Format relative time helper
        function formatTimeAgo(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diffSec = Math.floor((now - date) / 1000);
            if (diffSec < 60) return 'Hace un momento';
            if (diffSec < 3600) return `Hace ${Math.floor(diffSec / 60)} min`;
            return `Hace ${Math.floor(diffSec / 3600)} h`;
        }

        window.addEventListener('load', initMap);
    </script>
@endpush