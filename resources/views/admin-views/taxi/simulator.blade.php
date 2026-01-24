@extends('layouts.admin.app')

@section('title', 'Taxi Driver Simulator')

@push('css_or_js')
    <style>
        #simulator-map {
            height: 500px;
            width: 100%;
            border-radius: 8px;
        }

        .trip-card {
            cursor: pointer;
            transition: all 0.3s;
        }

        .trip-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .trip-card.selected {
            border: 2px solid #007bff;
            background-color: #f8f9fa;
        }

        .control-panel {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-pending {
            background: #ffc107;
            color: #000;
        }

        .status-accepted {
            background: #28a745;
            color: #fff;
        }

        .status-driver_on_the_way {
            background: #17a2b8;
            color: #fff;
        }

        .status-arrived_at_origin {
            background: #6f42c1;
            color: #fff;
        }

        .status-in_progress {
            background: #fd7e14;
            color: #fff;
        }
    </style>
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <i class="tio-car"></i>
                </span>
                <span>Taxi Driver Simulator</span>
            </h1>
            <div class="mt-2">
                <span class="badge badge-soft-info">Testing Mode</span>
                <span class="text-muted ml-2">Simulate driver actions without actual driver app</span>
            </div>
        </div>

        <div class="row">
            <!-- Left Panel - Trip List -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Active Trips</h5>
                    </div>
                    <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                        @forelse($pendingTrips as $trip)
                            <div class="trip-card card mb-3" data-trip-id="{{ $trip->id }}"
                                onclick="selectTrip({{ $trip->id }})">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="mb-0">Trip #{{ $trip->id }}</h6>
                                        <span class="status-badge status-{{ $trip->status }}">
                                            {{ ucfirst(str_replace('_', ' ', $trip->status)) }}
                                        </span>
                                    </div>
                                    <div class="text-muted small">
                                        <div><strong>User:</strong> {{ $trip->user->f_name }} {{ $trip->user->l_name }}</div>
                                        @if($trip->driver)
                                            <div><strong>Driver:</strong> {{ $trip->driver->f_name ?? 'N/A' }}
                                                {{ $trip->driver->l_name ?? '' }}
                                            </div>
                                        @endif
                                        <div><strong>From:</strong> {{ Str::limit($trip->pickup_address, 30) }}</div>
                                        <div><strong>Created:</strong> {{ $trip->created_at->diffForHumans() }}</div>
                                        @if($trip->eta_minutes)
                                            <div class="mt-2">
                                                <span class="badge badge-info">ETA: {{ $trip->eta_minutes }} min</span>
                                                <span class="badge badge-secondary">{{ $trip->distance_to_pickup_km }} km</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted py-5">
                                <i class="tio-car" style="font-size: 48px;"></i>
                                <p class="mt-2">No active trips</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Right Panel - Map and Controls -->
            <div class="col-md-8">
                <!-- Map -->
                <div class="card mb-3">
                    <div class="card-body p-0">
                        <div id="simulator-map"></div>
                    </div>
                </div>

                <!-- Control Panel -->
                <div class="control-panel" id="control-panel" style="display: none;">
                    <h5 class="mb-3">Trip Controls</h5>
                    <div id="trip-info" class="mb-3"></div>

                    <!-- Accept Trip Section -->
                    <div id="accept-section" style="display: none;">
                        <h6>Accept Trip as Driver</h6>
                        <div class="form-group">
                            <label>Select Driver</label>
                            <select class="form-control" id="driver-select">
                                <option value="">Choose driver...</option>
                                @foreach($allDrivers as $driver)
                                    <option value="{{ $driver->id }}">
                                        {{ $driver->f_name ?? 'N/A' }} {{ $driver->l_name ?? '' }}
                                        @if($driver->vehicle)
                                            - {{ $driver->vehicle->brand ?? '' }}
                                            {{ $driver->vehicle->model ?? '' }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <p class="text-muted small mb-2">Click on map to set driver's initial location, then click "Accept
                            Trip"</p>
                        <button class="btn btn-success" id="accept-trip-btn" disabled>
                            <i class="tio-done"></i> Accept Trip
                        </button>
                    </div>

                    <!-- Tracking Section -->
                    <div id="tracking-section" style="display: none;">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h6>Driver Actions</h6>
                                <div class="btn-group-vertical w-100">
                                    <button class="btn btn-sm btn-outline-primary mb-2" onclick="changeStatus('arriving')">
                                        <i class="tio-arrow-forward"></i> Start Moving to Pickup
                                    </button>
                                    <button class="btn btn-sm btn-outline-success mb-2" onclick="changeStatus('arrived')">
                                        <i class="tio-checkmark-circle"></i> Arrived at Pickup
                                    </button>
                                    <button class="btn btn-sm btn-outline-info mb-2" onclick="changeStatus('in_progress')">
                                        <i class="tio-play"></i> Start Trip
                                    </button>
                                    <button class="btn btn-sm btn-outline-dark" onclick="changeStatus('completed')">
                                        <i class="tio-done-vs"></i> Complete Trip
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Auto Pilot</h6>
                                <div class="form-group">
                                    <label>Speed</label>
                                    <select class="form-control form-control-sm" id="speed-select">
                                        <option value="slow">Slow</option>
                                        <option value="normal" selected>Normal</option>
                                        <option value="fast">Fast</option>
                                    </select>
                                </div>
                                <button class="btn btn-primary btn-sm w-100 mb-2" id="autopilot-btn"
                                    onclick="startAutoPilot()">
                                    <i class="tio-play-circle"></i> Start Auto Pilot
                                </button>
                                <button class="btn btn-danger btn-sm w-100" id="stop-autopilot-btn"
                                    onclick="stopAutoPilot()" style="display: none;">
                                    <i class="tio-stop-circle"></i> Stop Auto Pilot
                                </button>
                                <p class="text-muted small mt-2 mb-0">Or click on map to manually move driver</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
    <script
        src="https://maps.googleapis.com/maps/api/js?key={{ config('app.google_maps_api_key') }}&libraries=places"></script>
    <script>
        let map;
        let selectedTrip = null;
        let driverMarker = null;
        let originMarker = null;
        let destinationMarker = null;
        let polyline = null;
        let autopilotInterval = null;
        let pendingDriverLocation = null;

        // Initialize map
        function initMap() {
            map = new google.maps.Map(document.getElementById('simulator-map'), {
                center: { lat: 19.432608, lng: -99.133209 }, // Mexico City
                zoom: 13,
                mapTypeControl: false,
                streetViewControl: false
            });

            // Click handler for setting driver location
            map.addListener('click', function (event) {
                if (!selectedTrip) return;

                const lat = event.latLng.lat();
                const lng = event.latLng.lng();

                if (selectedTrip.status === 'pending') {
                    // Setting initial driver location for accepting trip
                    pendingDriverLocation = { lat, lng };

                    if (driverMarker) {
                        driverMarker.setPosition(event.latLng);
                    } else {
                        driverMarker = new google.maps.Marker({
                            position: event.latLng,
                            map: map,
                            icon: {
                                path: google.maps.SymbolPath.CIRCLE,
                                scale: 10,
                                fillColor: '#4285F4',
                                fillOpacity: 1,
                                strokeColor: '#fff',
                                strokeWeight: 2
                            },
                            title: 'Driver (pending acceptance)'
                        });
                    }

                    document.getElementById('accept-trip-btn').disabled = !document.getElementById('driver-select').value;
                } else if (['accepted', 'arriving'].includes(selectedTrip.status)) {
                    // Manually moving driver
                    updateDriverLocation(lat, lng);
                }
            });
        }

        // Select a trip
        function selectTrip(tripId) {
            fetch(`/admin/taxi/simulator/trip/${tripId}`)
                .then(response => response.json())
                .then(data => {
                    selectedTrip = data.trip;

                    // Update UI
                    document.querySelectorAll('.trip-card').forEach(card => {
                        card.classList.remove('selected');
                    });
                    document.querySelector(`.trip-card[data-trip-id="${tripId}"]`).classList.add('selected');

                    // Show control panel
                    document.getElementById('control-panel').style.display = 'block';

                    // Update map
                    updateMap();

                    // Show appropriate controls
                    if (selectedTrip.status === 'pending') {
                        document.getElementById('accept-section').style.display = 'block';
                        document.getElementById('tracking-section').style.display = 'none';
                    } else {
                        document.getElementById('accept-section').style.display = 'none';
                        document.getElementById('tracking-section').style.display = 'block';
                    }

                    // Update trip info
                    updateTripInfo();
                });
        }

        // Update map markers and polyline
        function updateMap() {
            if (!selectedTrip) return;

            // Clear existing markers
            if (driverMarker) driverMarker.setMap(null);
            if (originMarker) originMarker.setMap(null);
            if (destinationMarker) destinationMarker.setMap(null);
            if (polyline) polyline.setMap(null);

            // Origin marker
            const originPos = {
                lat: parseFloat(selectedTrip.pickup_lat),
                lng: parseFloat(selectedTrip.pickup_lng)
            };
            originMarker = new google.maps.Marker({
                position: originPos,
                map: map,
                icon: {
                    url: 'http://maps.google.com/mapfiles/ms/icons/green-dot.png'
                },
                title: 'Origin: ' + selectedTrip.pickup_address
            });

            // Destination marker
            const destPos = {
                lat: parseFloat(selectedTrip.dropoff_lat),
                lng: parseFloat(selectedTrip.dropoff_lng)
            };
            destinationMarker = new google.maps.Marker({
                position: destPos,
                map: map,
                icon: {
                    url: 'http://maps.google.com/mapfiles/ms/icons/red-dot.png'
                },
                title: 'Destination: ' + selectedTrip.dropoff_address
            });

            // Driver marker (if assigned)
            if (selectedTrip.driver_current_lat && selectedTrip.driver_current_lng) {
                const driverPos = {
                    lat: parseFloat(selectedTrip.driver_current_lat),
                    lng: parseFloat(selectedTrip.driver_current_lng)
                };

                driverMarker = new google.maps.Marker({
                    position: driverPos,
                    map: map,
                    icon: {
                        path: google.maps.SymbolPath.CIRCLE,
                        scale: 10,
                        fillColor: '#4285F4',
                        fillOpacity: 1,
                        strokeColor: '#fff',
                        strokeWeight: 2
                    },
                    title: 'Driver'
                });

                // Draw polyline from driver to origin
                polyline = new google.maps.Polyline({
                    path: [driverPos, originPos],
                    geodesic: true,
                    strokeColor: '#4285F4',
                    strokeOpacity: 0.8,
                    strokeWeight: 4,
                    map: map
                });
            }

            // Fit bounds
            const bounds = new google.maps.LatLngBounds();
            bounds.extend(originPos);
            bounds.extend(destPos);
            if (driverMarker) bounds.extend(driverMarker.getPosition());
            map.fitBounds(bounds);
        }

        // Update trip info display
        function updateTripInfo() {
            if (!selectedTrip) return;

            let html = `<div class="alert alert-info">
                                <strong>Trip #${selectedTrip.id}</strong> - ${selectedTrip.status}`;

            if (selectedTrip.eta_minutes) {
                html += `<br><span class="badge badge-primary">ETA: ${selectedTrip.eta_minutes} min</span>`;
            }
            if (selectedTrip.distance_to_pickup_km) {
                html += ` <span class="badge badge-secondary">${selectedTrip.distance_to_pickup_km} km to origin</span>`;
            }
            html += `</div>`;

            document.getElementById('trip-info').innerHTML = html;
        }

        // Accept trip
        document.getElementById('accept-trip-btn')?.addEventListener('click', function () {
            const driverId = document.getElementById('driver-select').value;
            if (!driverId || !pendingDriverLocation) {
                alert('Please select a driver and click on map to set initial location');
                return;
            }

            fetch(`/admin/taxi/simulator/trip/${selectedTrip.id}/accept`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    driver_id: driverId,
                    initial_lat: pendingDriverLocation.lat,
                    initial_lng: pendingDriverLocation.lng
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Trip accepted successfully!');
                        location.reload();
                    }
                });
        });

        // Update driver location
        function updateDriverLocation(lat, lng) {
            fetch(`/admin/taxi/simulator/trip/${selectedTrip.id}/update-location`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ lat, lng })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        selectedTrip = data.trip;
                        updateMap();
                        updateTripInfo();
                    }
                });
        }

        // Start auto pilot
        function startAutoPilot() {
            const speed = document.getElementById('speed-select').value;

            document.getElementById('autopilot-btn').style.display = 'none';
            document.getElementById('stop-autopilot-btn').style.display = 'block';

            autopilotInterval = setInterval(() => {
                fetch(`/admin/taxi/simulator/trip/${selectedTrip.id}/simulate-movement`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ speed })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            selectedTrip = data.trip;
                            updateMap();
                            updateTripInfo();

                            if (data.arrived) {
                                stopAutoPilot();
                                alert('Driver arrived at origin!');
                            }
                        }
                    });
            }, 2000); // Update every 2 seconds
        }

        // Stop auto pilot
        function stopAutoPilot() {
            if (autopilotInterval) {
                clearInterval(autopilotInterval);
                autopilotInterval = null;
            }
            document.getElementById('autopilot-btn').style.display = 'block';
            document.getElementById('stop-autopilot-btn').style.display = 'none';
        }

        // Change trip status
        function changeStatus(status) {
            if (confirm(`Change trip status to: ${status}?`)) {
                fetch(`/admin/taxi/simulator/trip/${selectedTrip.id}/change-status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ status })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Status updated!');
                            location.reload();
                        }
                    });
            }
        }

        // Initialize map when page loads
        window.addEventListener('load', initMap);
    </script>
@endpush