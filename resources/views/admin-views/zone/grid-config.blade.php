@extends('layouts.admin.app')

@section('title', 'Configurar la cuadrícula de entrega')

@push('css_or_js')
    <style>
        #map {
            height: 600px;
            width: 100%;
        }

        .brush-panel {
            background: white;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .brush-option {
            cursor: pointer;
            padding: 10px;
            border: 2px solid transparent;
            border-radius: 5px;
            margin-right: 10px;
            display: inline-block;
        }

        .brush-option.active {
            border-color: #3f51b5;
            background: #e8eaf6;
        }

        .brush-color {
            width: 20px;
            height: 20px;
            display: inline-block;
            vertical-align: middle;
            margin-right: 5px;
            border-radius: 50%;
        }

        .btn-minutes {
            background: #4caf50;
            color: white;
        }

        .btn-standard {
            background: #2196f3;
            color: white;
        }

        .btn-next-day {
            background: #ff9800;
            color: white;
        }

        .btn-eraser {
            background: #f44336;
            color: white;
        }

        .legend-item {
            margin-right: 15px;
            font-size: 14px;
        }
    </style>
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('assets/admin/img/zone.png')}}" class="w--26" alt="">
                </span>
                <span>{{'Configurar la cuadrícula de entrega'}} - {{ $zone->name }} ({{ $module->module_name }})</span>
            </h1>
        </div>

        <div class="brush-panel d-flex align-items-center justify-content-between">
            <div>
                <div class="brush-option active" data-type="minutes">
                    <span class="brush-color" style="background: #4caf50;"></span>
                    {{ 'Minutos (rápido)' }}
                </div>
                <div class="brush-option" data-type="standard">
                    <span class="brush-color" style="background: #2196f3;"></span>
                    {{ 'Estándar' }}
                </div>
                <div class="brush-option" data-type="next_day">
                    <span class="brush-color" style="background: #ff9800;"></span>
                    {{ 'día siguiente' }}
                </div>
                <div class="brush-option" data-type="no_coverage">
                    <span class="brush-color" style="background: #000000;"></span>
                    {{ 'Sin cobertura' }}
                </div>
                <div class="brush-option" data-type="none">
                    <span class="brush-color" style="background: #f44336;"></span>
                    {{ 'Borrador' }}
                </div>
            </div>
            <button class="btn btn-primary" id="save-grid">
                <i class="tio-save"></i> {{ 'Guardar cambios' }}
            </button>
        </div>

        <div class="card">
            <div class="card-body p-0">
                <div id="map"></div>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
    <script
        src="https://maps.googleapis.com/maps/api/js?key={{ \App\Models\BusinessSetting::where('key', 'map_api_key')->first()->value }}&libraries=drawing,places"></script>
    <script>
        const GRID_SIZE = 0.005; // Matches PHP H3Helper::GRID_SIZE
        let currentBrush = 'minutes';
        let gridData = {!! json_encode((object)$grids->pluck('delivery_type', 'hexagon_id')->toArray()) !!};
        let zoneCoords = @json($formatted_coords);
        let map;
        let hexPolygons = {};

        function latLngToHex(lat, lng) {
            const size = GRID_SIZE;
            let q = (2 / 3 * lng) / size;
            let r = (-1 / 3 * lng + Math.sqrt(3) / 3 * lat) / size;

            let x = q;
            let z = r;
            let y = -x - z;

            let rx = Math.round(x);
            let ry = Math.round(y);
            let rz = Math.round(z);

            const dx = Math.abs(rx - x);
            const dy = Math.abs(ry - y);
            const dz = Math.abs(rz - z);

            if (dx > dy && dx > dz) {
                rx = -ry - rz;
            } else if (dy > dz) {
                ry = -rx - rz;
            } else {
                rz = -rx - ry;
            }

            // Generate ID matching PHP: hex_%x_%x
            // Since JS doesn't have sprintf %x easily, we use toString(16)
            // But PHP sprintf %x uses unsigned. We add 1000000 to keep it positive/consistent
            const id = "hex_" + (Math.floor(rx) + 1000000).toString(16) + "_" + (Math.floor(rz) + 1000000).toString(16);
            return { id, rx: Math.floor(rx), rz: Math.floor(rz) };
        }

        function getHexCorners(rx, rz) {
            const size = GRID_SIZE;
            const corners = [];
            // Basic axial to world lat/lng (inverse of latLngToHex)
            // lng = q * size * 3/2
            // lat = (r * size + 1/3 * lng) * 3/sqrt(3)

            const centerLng = rx * size * 1.5;
            const centerLat = (rz * size + rx * 0.5) * Math.sqrt(3);

            for (let i = 0; i < 6; i++) {
                const angle_deg = 60 * i;
                const angle_rad = Math.PI / 180 * angle_deg;

                // Flat top hex corners
                const pLng = centerLng + size * Math.cos(angle_rad) * 1.5;
                const pLat = centerLat + size * Math.sin(angle_rad) * Math.sqrt(3);

                // Note: This is an approximation. For exact tiling we should use the axial math directly.
                // But this should be enough for the UI.
            }
            // Re-think: A better way is to fixed the corners based on axial coords to ensure perfect tiling.
        }

        function drawHex(hexId, rx, rz, type) {
            if (hexPolygons[hexId]) {
                hexPolygons[hexId].setMap(null);
            }

            if (type === 'none') {
                delete hexPolygons[hexId];
                return;
            }

            const size = GRID_SIZE;
            const centerLng = rx * size * 1.5;
            const centerLat = (rz + 0.5 * rx) * size * Math.sqrt(3);

            const corners = [];
            const hexOffsets = [
                { dq: 2 / 3, dr: -1 / 3 },
                { dq: 1 / 3, dr: 1 / 3 },
                { dq: -1 / 3, dr: 2 / 3 },
                { dq: -2 / 3, dr: 1 / 3 },
                { dq: -1 / 3, dr: -1 / 3 },
                { dq: 1 / 3, dr: -2 / 3 }
            ];

            hexOffsets.forEach(offset => {
                corners.push({
                    lat: centerLat + (offset.dr + offset.dq * 0.5) * size * Math.sqrt(3),
                    lng: centerLng + offset.dq * size * 1.5
                });
            });

            const colors = {
                'minutes': '#4caf50',
                'standard': '#2196f3',
                'next_day': '#ff9800',
                'no_coverage': '#000000'
            };

            const poly = new google.maps.Polygon({
                paths: corners,
                strokeColor: colors[type],
                strokeOpacity: 0.8,
                strokeWeight: 2,
                fillColor: colors[type],
                fillOpacity: 0.5,
                map: map,
                zIndex: 1000
            });

            poly.addListener('click', () => {
                paintHex(hexId, rx, rz);
            });

            hexPolygons[hexId] = poly;
        }

        function paintHex(hexId, rx, rz) {
            console.log('Painting hex:', hexId, 'type:', currentBrush);
            gridData[hexId] = currentBrush;
            drawHex(hexId, rx, rz, currentBrush);
        }

        function initMap() {
            const center = zoneCoords.length > 0 ? zoneCoords[0] : { lat: 0, lng: 0 };
            console.log('Initializing map with center:', center);
            map = new google.maps.Map(document.getElementById('map'), {
                zoom: 14,
                center: center,
            });

            const zonePolygon = new google.maps.Polygon({
                paths: zoneCoords,
                strokeColor: "#000",
                strokeOpacity: 0.5,
                strokeWeight: 2,
                fillColor: "#000",
                fillOpacity: 0.1,
                map: map,
                clickable: false,
                zIndex: 1
            });

            // Handle clicking on map to paint
            map.addListener('click', (e) => {
                const hex = latLngToHex(e.latLng.lat(), e.latLng.lng());
                console.log('Map clicked at:', e.latLng.lat(), e.latLng.lng(), 'Hex:', hex.id);
                paintHex(hex.id, hex.rx, hex.rz);
            });

            // Draw existing grids
            console.log('Drawing existing grids:', Object.keys(gridData).length);
            Object.keys(gridData).forEach(id => {
                const parts = id.split('_');
                const rx = parseInt(parts[1], 16) - 1000000;
                const rz = parseInt(parts[2], 16) - 1000000;
                drawHex(id, rx, rz, gridData[id]);
            });
        }

        $('.brush-option').on('click', function () {
            $('.brush-option').removeClass('active');
            $(this).addClass('active');
            currentBrush = $(this).data('type');
        });

        $('#save-grid').on('click', function () {
            $(this).prop('disabled', true).html('<i class="tio-running"></i> {{'Ahorro...'}}');

            $.ajax({
                url: '{{ route("admin.business-settings.zone.grid-config-update") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    zone_id: '{{ $zone->id }}',
                    module_id: '{{ $module->id }}',
                    grids: gridData
                },
                success: function (res) {
                    toastr.success(res.message);
                    $('#save-grid').prop('disabled', false).html('<i class="tio-save"></i> {{'Guardar cambios'}}');
                },
                error: function (xhr) {
                    let message = '{{'Error al guardar la cuadrícula'}}';
                    if (xhr.status === 419) {
                        message += ' (Session Expired/CSRF Error)';
                    } else if (xhr.status === 422) {
                        message += ' (Validation Error): ' + JSON.stringify(xhr.responseJSON.errors);
                    } else if (xhr.responseJSON && xhr.responseJSON.error) {
                        message += ': ' + xhr.responseJSON.error;
                    } else {
                        message += ' (Status: ' + xhr.status + ')';
                    }
                    toastr.error(message);
                    console.error('Grid Save Error:', xhr);
                    $('#save-grid').prop('disabled', false).html('<i class="tio-save"></i> {{'Guardar cambios'}}');
                }
            });
        });

        $(document).on('ready', function () {
            initMap();
        });
    </script>
@endpush