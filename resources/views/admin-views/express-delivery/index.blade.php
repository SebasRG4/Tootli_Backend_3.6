@extends('layouts.admin.app')

@section('title', 'Crear Envío Express WhatsApp')

@push('css_or_js')
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
    .step-card {
        border-radius: 12px;
        border: 1px solid #E2E8F0;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
        transition: all 0.2s ease;
    }
    .step-card:hover {
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
    }
    .badge-step {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        font-weight: 700;
        font-size: 14px;
        color: #fff;
    }
    .badge-pickup { background: #10B981; }
    .badge-delivery { background: #EF4444; }
    .badge-summary { background: #3B82F6; }
    #expressMap {
        width: 100%;
        height: 420px;
        border-radius: 12px;
        border: 1px solid #CBD5E1;
    }
    .route-info-box {
        background: #F8FAFC;
        border-left: 4px solid #3B82F6;
        padding: 12px 16px;
        border-radius: 6px;
    }
    .payment-pill {
        cursor: pointer;
        border: 2px solid #E2E8F0;
        border-radius: 10px;
        padding: 10px 14px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 600;
        transition: all 0.2s;
    }
    .payment-pill input {
        display: none;
    }
    .payment-pill.active {
        border-color: #2563EB;
        background: #EFF6FF;
        color: #1E40AF;
    }
</style>
@endpush

@section('content')
<div class="content container-fluid">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h1 class="page-header-title d-flex align-items-center gap-2 mb-1">
                <i class="tio-bike text-success" style="font-size: 1.75rem;"></i>
                <span>Nuevo Envío Express / WhatsApp</span>
            </h1>
            <p class="text-muted mb-0">Crea envíos punto a punto para restaurantes no registrados o pedidos recibidos por WhatsApp y despáchalos directo a los repartidores.</p>
        </div>
        <div>
            <a href="{{ route('admin.order.list', ['all']) }}" class="btn btn-outline-secondary">
                <i class="tio-history mr-1"></i> Ver Todos los Pedidos
            </a>
        </div>
    </div>

    <form id="expressDeliveryForm" action="{{ route('admin.express-delivery.store') }}" method="POST">
        @csrf
        <div class="row g-3">
            <!-- Columna Izquierda: Origen y Destino -->
            <div class="col-lg-7">
                <!-- Paso 1: Recogida (Restaurante / Negocio) -->
                <div class="card step-card mb-4">
                    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge-step badge-pickup">A</span>
                            <h5 class="mb-0 font-weight-bold">Punto de Recogida (Restaurante / Negocio)</h5>
                        </div>
                        <span class="badge badge-soft-success">Origen</span>
                    </div>
                    <div class="card-body">
                        <!-- Selector rápido si es tienda existente -->
                        <div class="form-group mb-3">
                            <label class="form-label text-muted small mb-1">
                                <i class="tio-shop mr-1"></i> ¿Es un restaurante ya registrado? (Opcional):
                            </label>
                            <select id="registeredStoreSelect" class="form-control select2">
                                <option value="">-- Escribir datos manualmente (No registrado) --</option>
                                @foreach($stores as $st)
                                    <option value="{{ $st->id }}" 
                                        data-name="{{ $st->name }}" 
                                        data-phone="{{ $st->phone }}" 
                                        data-address="{{ $st->address }}" 
                                        data-lat="{{ $st->latitude }}" 
                                        data-lng="{{ $st->longitude }}">
                                        {{ $st->name }} ({{ $st->phone ?? 'Sin tel' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row g-2">
                            <div class="col-md-7">
                                <div class="form-group mb-3">
                                    <label class="form-label font-weight-bold">Nombre del Negocio / Restaurante <span class="text-danger">*</span></label>
                                    <input type="text" name="pickup_name" id="pickup_name" class="form-control" placeholder="Ej. Taquería El Paisa" value="{{ old('pickup_name') }}" required>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group mb-3">
                                    <label class="form-label font-weight-bold">Teléfono del Negocio <span class="text-danger">*</span></label>
                                    <input type="tel" name="pickup_phone" id="pickup_phone" class="form-control" placeholder="Ej. 7771234567" value="{{ old('pickup_phone') }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-2">
                            <label class="form-label font-weight-bold">Dirección de Recogida <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="tio-poi text-success"></i></span>
                                </div>
                                <input type="text" name="pickup_address" id="pickup_address" class="form-control" placeholder="Buscar dirección en Google Maps o escribir calle y número..." value="{{ old('pickup_address') }}" required autocomplete="off">
                            </div>
                            <small class="form-text text-muted">Escribe la dirección y selecciona la sugerencia de Google Maps o muévela en el mapa.</small>
                        </div>

                        <input type="hidden" name="pickup_latitude" id="pickup_latitude" value="{{ old('pickup_latitude', $default_location['lat'] ?? '18.9261') }}">
                        <input type="hidden" name="pickup_longitude" id="pickup_longitude" value="{{ old('pickup_longitude', $default_location['lng'] ?? '-99.2307') }}">
                    </div>
                </div>

                <!-- Paso 2: Entrega (Cliente Final de WhatsApp) -->
                <div class="card step-card mb-4">
                    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge-step badge-delivery">B</span>
                            <h5 class="mb-0 font-weight-bold">Punto de Entrega (Cliente Final de WhatsApp)</h5>
                        </div>
                        <span class="badge badge-soft-danger">Destino</span>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-md-7">
                                <div class="form-group mb-3">
                                    <label class="form-label font-weight-bold">Nombre de quien recibe <span class="text-danger">*</span></label>
                                    <input type="text" name="receiver_name" id="receiver_name" class="form-control" placeholder="Ej. Juan Pérez" value="{{ old('receiver_name') }}" required>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group mb-3">
                                    <label class="form-label font-weight-bold">Teléfono / WhatsApp <span class="text-danger">*</span></label>
                                    <input type="tel" name="receiver_phone" id="receiver_phone" class="form-control" placeholder="Ej. 7779876543" value="{{ old('receiver_phone') }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-2">
                            <label class="form-label font-weight-bold">Dirección de Entrega <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="tio-poi-outlined text-danger"></i></span>
                                </div>
                                <input type="text" name="receiver_address" id="receiver_address" class="form-control" placeholder="Buscar dirección de entrega del cliente..." value="{{ old('receiver_address') }}" required autocomplete="off">
                            </div>
                            <small class="form-text text-muted">Escribe la colonia, calle y número del cliente o selecciónala en el mapa.</small>
                        </div>

                        <input type="hidden" name="receiver_latitude" id="receiver_latitude" value="{{ old('receiver_latitude', $default_location['lat'] ?? '18.9261') }}">
                        <input type="hidden" name="receiver_longitude" id="receiver_longitude" value="{{ old('receiver_longitude', $default_location['lng'] ?? '-99.2307') }}">
                    </div>
                </div>

                <!-- Mapa interactivo de ruta -->
                <div class="card step-card">
                    <div class="card-header bg-white py-2 d-flex align-items-center justify-content-between">
                        <span class="font-weight-bold text-muted small"><i class="tio-map mr-1"></i> Mapa de Ruta y Cobertura</span>
                        <span class="badge badge-soft-info" id="zoneDisplay">Zona: Detectando...</span>
                    </div>
                    <div class="card-body p-2">
                        <div id="expressMap"></div>
                    </div>
                </div>
            </div>

            <!-- Columna Derecha: Finanzas, Despacho y Envío -->
            <div class="col-lg-5">
                <div class="card step-card sticky-top" style="top: 20px;">
                    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge-step badge-summary"><i class="tio-receipt"></i></span>
                            <h5 class="mb-0 font-weight-bold">Cobro y Despacho</h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Detalles del producto -->
                        <div class="form-group mb-3">
                            <label class="form-label font-weight-bold">Descripción del Pedido (Opcional)</label>
                            <input type="text" name="item_description" class="form-control" placeholder="Ej. 2 Hamburguesas dobles + 1 refresco" value="{{ old('item_description') }}">
                            <small class="text-muted">El repartidor verá esta nota al recoger la comida.</small>
                        </div>

                        <!-- Montos -->
                        <div class="bg-light p-3 rounded-lg mb-3">
                            <div class="form-group mb-3">
                                <label class="form-label font-weight-bold text-dark d-flex justify-content-between">
                                    <span>Valor de la Comida / Mercancía ($)</span>
                                    <small class="text-muted">Si ya pagó por transf: $0</small>
                                </label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                                    <input type="number" step="0.5" min="0" name="item_price" id="item_price" class="form-control font-weight-bold text-right" placeholder="0.00" value="{{ old('item_price', '0') }}">
                                </div>
                            </div>

                            <div class="form-group mb-2">
                                <label class="form-label font-weight-bold text-dark d-flex justify-content-between">
                                    <span>Costo del Flete / Envío ($)</span>
                                    <small class="text-primary font-weight-bold" id="distKmBadge">Distancia: 0 km</small>
                                </label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                                    <input type="number" step="0.5" min="0" name="delivery_fee" id="delivery_fee" class="form-control font-weight-bold text-right" placeholder="0.00" value="{{ old('delivery_fee', $minShippingCharge) }}" required>
                                </div>
                                <small class="text-muted">Calculado por distancia. Puedes modificar el precio libremente si acordaron otra tarifa.</small>
                            </div>
                        </div>

                        <!-- Método de Pago -->
                        <div class="form-group mb-3">
                            <label class="form-label font-weight-bold">Método de Pago <span class="text-danger">*</span></label>
                            <div class="d-flex flex-column gap-2">
                                <label class="payment-pill active" id="pill_cod">
                                    <input type="radio" name="payment_method" value="cash_on_delivery" checked>
                                    <span class="font-size-lg">💵</span>
                                    <div>
                                        <div>Efectivo (Cobrar al entregar)</div>
                                        <small class="text-muted font-weight-normal">El repartidor cobra al cliente en destino</small>
                                    </div>
                                </label>
                                <label class="payment-pill" id="pill_spei">
                                    <input type="radio" name="payment_method" value="offline_payment">
                                    <span class="font-size-lg">📲</span>
                                    <div>
                                        <div>Transferencia / SPEI (Pagado)</div>
                                        <small class="text-muted font-weight-normal">El cliente ya le transfirió al restaurante o admin</small>
                                    </div>
                                </label>
                                <label class="payment-pill" id="pill_card">
                                    <input type="radio" name="payment_method" value="digital_payment">
                                    <span class="font-size-lg">💳</span>
                                    <div>
                                        <div>Tarjeta (Digital)</div>
                                        <small class="text-muted font-weight-normal">Pagado previamente con tarjeta</small>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Quién paga el envío -->
                        <div class="form-group mb-3">
                            <label class="form-label font-weight-bold small text-muted">¿Quién asume el costo de envío?</label>
                            <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                                <label class="btn btn-outline-primary active w-50">
                                    <input type="radio" name="charge_payer" value="receiver" checked> Cliente (Receptor)
                                </label>
                                <label class="btn btn-outline-primary w-50">
                                    <input type="radio" name="charge_payer" value="sender"> Restaurante (Origen)
                                </label>
                            </div>
                        </div>

                        <!-- Asignación de Repartidor -->
                        <div class="form-group mb-3">
                            <label class="form-label font-weight-bold">
                                <i class="tio-user-big mr-1 text-primary"></i> Asignar Repartidor:
                            </label>
                            <select name="delivery_man_id" id="delivery_man_id" class="form-control select2">
                                <option value="0">🔔 Notificar a todos los repartidores cercanos (Automático)</option>
                                @foreach($deliveryMen as $dm)
                                    <option value="{{ $dm->id }}">
                                        🛵 {{ $dm->f_name }} {{ $dm->l_name }} ({{ $dm->phone }}) - {{ $dm->current_orders > 0 ? $dm->current_orders . ' pedidos activos' : 'Libre' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Instrucciones de entrega -->
                        <div class="form-group mb-3">
                            <label class="form-label font-weight-bold small text-muted">Instrucciones o Notas de Entrega</label>
                            <textarea name="delivery_instruction" class="form-control" rows="2" placeholder="Ej. Tocar timbre blanco, casa con portón negro..."></textarea>
                        </div>

                        <!-- Total en Mano -->
                        <div class="route-info-box mb-3 d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted small">Total a cobrar en Efectivo:</div>
                                <div class="h4 mb-0 text-primary font-weight-bold" id="totalCashDisplay">$0.00</div>
                            </div>
                            <div class="text-right small text-muted" id="totalCashExplanation">
                                Al entregar al cliente
                            </div>
                        </div>

                        <!-- Botón de Envío -->
                        <button type="submit" id="btnSubmitExpress" class="btn btn-primary btn-block btn-lg shadow-sm py-3 font-weight-bold">
                            <i class="tio-send mr-1"></i> Despachar Pedido a Repartidores
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('script_2')
<script src="https://maps.googleapis.com/maps/api/js?key={{ $map_api_key }}&libraries=places&callback=initExpressMap" async defer></script>

<script>
    "use strict";

    let expressMap, pickupMarker, receiverMarker, directionsService, directionsRenderer;
    const defaultLat = parseFloat("{{ $default_location['lat'] ?? 18.9261 }}");
    const defaultLng = parseFloat("{{ $default_location['lng'] ?? -99.2307 }}");
    const perKmRate = parseFloat("{{ $perKmCharge ?? 15 }}");
    const minChargeRate = parseFloat("{{ $minShippingCharge ?? 35 }}");

    function initExpressMap() {
        const centerLatLng = { lat: defaultLat, lng: defaultLng };

        expressMap = new google.maps.Map(document.getElementById("expressMap"), {
            zoom: 13,
            center: centerLatLng,
            streetViewControl: false,
            mapTypeControl: false,
        });

        directionsService = new google.maps.DirectionsService();
        directionsRenderer = new google.maps.DirectionsRenderer({
            map: expressMap,
            suppressMarkers: true,
            polylineOptions: {
                strokeColor: "#2563EB",
                strokeWeight: 5,
                strokeOpacity: 0.8
            }
        });

        // Marcador A (Recogida / Restaurante) - Verde
        pickupMarker = new google.maps.Marker({
            position: centerLatLng,
            map: expressMap,
            draggable: true,
            title: "Punto de Recogida (Restaurante)",
            icon: {
                url: "https://maps.google.com/mapfiles/ms/icons/green-dot.png"
            }
        });

        // Marcador B (Entrega / Cliente) - Rojo
        receiverMarker = new google.maps.Marker({
            position: { lat: defaultLat + 0.015, lng: defaultLng + 0.015 },
            map: expressMap,
            draggable: true,
            title: "Punto de Entrega (Cliente)",
            icon: {
                url: "https://maps.google.com/mapfiles/ms/icons/red-dot.png"
            }
        });

        // Eventos drag en marcadores
        pickupMarker.addListener("dragend", function (e) {
            document.getElementById("pickup_latitude").value = e.latLng.lat();
            document.getElementById("pickup_longitude").value = e.latLng.lng();
            geocodeLatLng(e.latLng, "pickup_address");
            calculateAndDisplayRoute();
        });

        receiverMarker.addListener("dragend", function (e) {
            document.getElementById("receiver_latitude").value = e.latLng.lat();
            document.getElementById("receiver_longitude").value = e.latLng.lng();
            geocodeLatLng(e.latLng, "receiver_address");
            calculateAndDisplayRoute();
        });

        // Autocomplete Google Places para Recogida
        const pickupInput = document.getElementById("pickup_address");
        const pickupAutocomplete = new google.maps.places.Autocomplete(pickupInput);
        pickupAutocomplete.bindTo("bounds", expressMap);
        pickupAutocomplete.addListener("place_changed", function () {
            const place = pickupAutocomplete.getPlace();
            if (!place.geometry || !place.geometry.location) return;

            pickupMarker.setPosition(place.geometry.location);
            document.getElementById("pickup_latitude").value = place.geometry.location.lat();
            document.getElementById("pickup_longitude").value = place.geometry.location.lng();
            calculateAndDisplayRoute();
        });

        // Autocomplete Google Places para Entrega
        const receiverInput = document.getElementById("receiver_address");
        const receiverAutocomplete = new google.maps.places.Autocomplete(receiverInput);
        receiverAutocomplete.bindTo("bounds", expressMap);
        receiverAutocomplete.addListener("place_changed", function () {
            const place = receiverAutocomplete.getPlace();
            if (!place.geometry || !place.geometry.location) return;

            receiverMarker.setPosition(place.geometry.location);
            document.getElementById("receiver_latitude").value = place.geometry.location.lat();
            document.getElementById("receiver_longitude").value = place.geometry.location.lng();
            calculateAndDisplayRoute();
        });

        // Trazar ruta inicial
        calculateAndDisplayRoute();
    }

    function geocodeLatLng(latLng, targetInputId) {
        const geocoder = new google.maps.Geocoder();
        geocoder.geocode({ location: latLng }, function (results, status) {
            if (status === "OK" && results[0]) {
                document.getElementById(targetInputId).value = results[0].formatted_address;
            }
        });
    }

    function calculateAndDisplayRoute() {
        const origin = pickupMarker.getPosition();
        const destination = receiverMarker.getPosition();

        if (!origin || !destination) return;

        directionsService.route(
            {
                origin: origin,
                destination: destination,
                travelMode: google.maps.TravelMode.DRIVING,
            },
            function (response, status) {
                if (status === "OK") {
                    directionsRenderer.setDirections(response);
                    const route = response.routes[0];
                    if (route && route.legs && route.legs[0]) {
                        const distKm = (route.legs[0].distance.value / 1000).toFixed(1);
                        document.getElementById("distKmBadge").innerText = `Distancia: ${distKm} km`;
                        
                        // Consultar backend para cálculo exacto de tarifa y zona
                        syncFeeWithBackend(origin.lat(), origin.lng(), destination.lat(), destination.lng(), distKm);
                    }
                } else {
                    // Fallback si no hay ruta por carretera
                    const pLat = origin.lat();
                    const pLng = origin.lng();
                    const rLat = destination.lat();
                    const rLng = destination.lng();
                    syncFeeWithBackend(pLat, pLng, rLat, rLng);
                }
            }
        );
    }

    function syncFeeWithBackend(pLat, pLng, rLat, rLng, fallbackKm = null) {
        $.ajax({
            url: "{{ route('admin.express-delivery.calculate-fee') }}",
            type: "POST",
            data: {
                _token: $('meta[name="csrf-token"]').attr("content"),
                pickup_lat: pLat,
                pickup_lng: pLng,
                delivery_lat: rLat,
                delivery_lng: rLng,
            },
            success: function (res) {
                if (res.success) {
                    $('#delivery_fee').val(res.delivery_fee);
                    $('#distKmBadge').text(`Distancia: ${res.distance} km`);
                    $('#zoneDisplay').text(`Zona: ${res.zone_name}`);
                    updateTotals();
                }
            },
            error: function () {
                // Cálculo fallback local
                if (fallbackKm) {
                    const fee = Math.max(minChargeRate, Math.round(fallbackKm * perKmRate));
                    $('#delivery_fee').val(fee);
                    updateTotals();
                }
            }
        });
    }

    // Actualización de totales de cobro
    function updateTotals() {
        const itemPrice = parseFloat($('#item_price').val()) || 0;
        const deliveryFee = parseFloat($('#delivery_fee').val()) || 0;
        const paymentMethod = $('input[name="payment_method"]:checked').val();
        const chargePayer = $('input[name="charge_payer"]:checked').val();

        let totalToCollect = 0;
        let explanation = "";

        if (paymentMethod === 'cash_on_delivery') {
            if (chargePayer === 'sender') {
                totalToCollect = itemPrice;
                explanation = "El cliente solo paga la comida (Restaurante cubre el flete)";
            } else {
                totalToCollect = itemPrice + deliveryFee;
                explanation = `Comida ($${itemPrice.toFixed(2)}) + Envío ($${deliveryFee.toFixed(2)})`;
            }
        } else {
            totalToCollect = 0;
            explanation = "Marcado como ya PAGADO por transferencia/tarjeta";
        }

        $('#totalCashDisplay').text(`$${totalToCollect.toFixed(2)}`);
        $('#totalCashExplanation').text(explanation);
    }

    $(document).ready(function () {
        // Escuchar cambios de montos y radios
        $('#item_price, #delivery_fee').on('input change', updateTotals);
        $('input[name="payment_method"]').on('change', function () {
            $('.payment-pill').removeClass('active');
            $(this).closest('.payment-pill').addClass('active');
            updateTotals();
        });
        $('input[name="charge_payer"]').on('change', updateTotals);

        // Selector de tienda registrada
        $('#registeredStoreSelect').on('change', function () {
            const selected = $(this).find(':selected');
            if (!selected.val()) return;

            const name = selected.data('name');
            const phone = selected.data('phone');
            const address = selected.data('address');
            const lat = parseFloat(selected.data('lat'));
            const lng = parseFloat(selected.data('lng'));

            if (name) $('#pickup_name').val(name);
            if (phone) $('#pickup_phone').val(phone);
            if (address) $('#pickup_address').val(address);

            if (lat && lng && pickupMarker) {
                const pos = new google.maps.LatLng(lat, lng);
                pickupMarker.setPosition(pos);
                $('#pickup_latitude').val(lat);
                $('#pickup_longitude').val(lng);
                calculateAndDisplayRoute();
            }
        });

        // Prevenir doble envío
        $('#expressDeliveryForm').on('submit', function () {
            $('#btnSubmitExpress').prop('disabled', true).html('<span class="spinner-border spinner-border-sm mr-2"></span> Enviando a repartidores...');
        });

        updateTotals();
    });
</script>
@endpush
