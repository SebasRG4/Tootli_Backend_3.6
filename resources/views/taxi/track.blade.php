@extends('layouts.blank')

@section('title', translate('Trip Tracking'))

@push('css_or_js')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            min-height: 100vh;
        }

        .tracking-container {
            max-width: 500px;
            margin: 0 auto;
            background: white;
            min-height: 100vh;
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.1);
        }

        .header {
            background: linear-gradient(135deg, #00a651, #009645);
            color: white;
            padding: 20px;
            text-align: center;
        }

        .header h1 {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .header p {
            font-size: 13px;
            opacity: 0.9;
        }

        .map-container {
            height: 300px;
            position: relative;
        }

        #map {
            width: 100%;
            height: 100%;
        }

        .status-banner {
            padding: 15px 20px;
            background: #fff8e1;
            border-bottom: 1px solid #ffe082;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .status-banner.active {
            background: #e8f5e9;
            border-color: #a5d6a7;
        }

        .status-banner.completed {
            background: #e3f2fd;
            border-color: #90caf9;
        }

        .status-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .status-icon.active {
            background: #00a651;
            color: white;
        }

        .status-icon.pending {
            background: #ffc107;
            color: white;
        }

        .status-icon.completed {
            background: #2196f3;
            color: white;
        }

        .status-text h3 {
            font-size: 16px;
            color: #333;
            margin-bottom: 3px;
        }

        .status-text p {
            font-size: 13px;
            color: #666;
        }

        .info-section {
            padding: 20px;
        }

        .info-card {
            background: #f9f9f9;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 15px;
        }

        .info-card h4 {
            font-size: 12px;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-row .label {
            color: #666;
            font-size: 14px;
        }

        .info-row .value {
            color: #333;
            font-weight: 500;
            font-size: 14px;
            text-align: right;
            max-width: 60%;
        }

        .call-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: #00a651;
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            margin-top: 10px;
        }

        .call-btn:hover {
            background: #009645;
            color: white;
            text-decoration: none;
        }

        .last-update {
            text-align: center;
            padding: 15px;
            font-size: 12px;
            color: #888;
            border-top: 1px solid #eee;
        }

        .expired-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 80vh;
            padding: 40px;
            text-align: center;
        }

        .expired-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }

        .expired-container h2 {
            color: #333;
            margin-bottom: 10px;
        }

        .expired-container p {
            color: #666;
        }
    </style>
@endpush

@section('content')
    <div class="tracking-container">
        @if($expired)
            <div class="expired-container">
                <div class="expired-icon">🔒</div>
                <h2>{{ translate('Link Expired') }}</h2>
                <p>{{ translate('This tracking link is no longer available. The ride may have ended.') }}</p>
            </div>
        @else
            <!-- Header -->
            <div class="header">
                <h1>🚗 {{ translate('Live Trip Tracking') }}</h1>
                <p>{{ translate('Shared by a Tootli user') }}</p>
            </div>

            <!-- Map -->
            <div class="map-container">
                <div id="map"></div>
            </div>

            <!-- Status Banner -->
            <div
                class="status-banner {{ $ride->status == 'completed' ? 'completed' : ($ride->status == 'in_progress' ? 'active' : '') }}">
                <div
                    class="status-icon {{ $ride->status == 'completed' ? 'completed' : ($ride->status == 'in_progress' ? 'active' : 'pending') }}">
                    @if($ride->status == 'completed')
                        ✓
                    @elseif($ride->status == 'in_progress')
                        🚗
                    @else
                        ⏳
                    @endif
                </div>
                <div class="status-text">
                    <h3>
                        @if($ride->status == 'completed')
                            {{ translate('Trip Completed') }}
                        @elseif($ride->status == 'in_progress')
                            {{ translate('Trip in Progress') }}
                        @elseif($ride->status == 'arrived')
                            {{ translate('Driver Arrived') }}
                        @else
                            {{ translate('Driver on the way') }}
                        @endif
                    </h3>
                    <p>
                        @if($ride->eta_minutes)
                            {{ translate('ETA') }}: {{ $ride->eta_minutes }} {{ translate('min') }}
                        @else
                            {{ $ride->status }}
                        @endif
                    </p>
                </div>
            </div>

            <!-- Trip Info -->
            <div class="info-section">
                <!-- Route -->
                <div class="info-card">
                    <h4>📍 {{ translate('Route') }}</h4>
                    <div class="info-row">
                        <span class="label">{{ translate('Pickup') }}</span>
                        <span class="value">{{ Str::limit($ride->pickup_address, 40) }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">{{ translate('Destination') }}</span>
                        <span class="value">{{ Str::limit($ride->dropoff_address, 40) }}</span>
                    </div>
                </div>

                <!-- Driver Info -->
                @if($ride->driver)
                    <div class="info-card">
                        <h4>👤 {{ translate('Driver') }}</h4>
                        <div class="info-row">
                            <span class="label">{{ translate('Name') }}</span>
                            <span class="value">{{ $ride->driver->f_name }} {{ $ride->driver->l_name }}</span>
                        </div>
                        <div class="info-row">
                            <span class="label">{{ translate('Phone') }}</span>
                            <span class="value">{{ $ride->driver->phone }}</span>
                        </div>
                        @if($ride->driver->vehicle)
                            <div class="info-row">
                                <span class="label">{{ translate('Vehicle') }}</span>
                                <span class="value">{{ $ride->driver->vehicle->plate ?? 'N/A' }}</span>
                            </div>
                        @endif
                        <a href="tel:{{ $ride->driver->phone }}" class="call-btn">
                            📞 {{ translate('Call Driver') }}
                        </a>
                    </div>
                @endif
            </div>

            <div class="last-update">
                🔄 {{ translate('Last update') }}: <span id="last-update-time">{{ now()->format('H:i:s') }}</span>
            </div>
        @endif
    </div>
@endsection

@if(!$expired)
    @push('script')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script>
            // Initialize map
            var lat = {{ $ride->driver_current_lat ?? $ride->pickup_lat ?? 19.43 }};
            var lng = {{ $ride->driver_current_lng ?? $ride->pickup_lng ?? -99.13 }};

            var map = L.map('map').setView([lat, lng], 15);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap'
            }).addTo(map);

            // Add marker for current location
            var marker = L.marker([lat, lng]).addTo(map);
            marker.bindPopup('📍 {{ translate("Current Location") }}').openPopup();

            // Auto-refresh every 10 seconds
            setInterval(function () {
                fetch(window.location.href + '?json=1')
                    .then(response => response.json())
                    .then(data => {
                        if (data.location) {
                            marker.setLatLng([data.location.lat, data.location.lng]);
                            map.panTo([data.location.lat, data.location.lng]);
                        }
                        document.getElementById('last-update-time').textContent = new Date().toLocaleTimeString();
                    })
                    .catch(err => console.log('Update failed'));
            }, 10000);
        </script>
    @endpush
@endif