@extends('layouts.admin.app')

@section('title', 'Agregar nueva zona')

@push('css_or_js')

@endpush

@section('content')
@php($zone_instruction = session()?->get('zone-instruction') ?? '0')
<div class="content container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <h1 class="page-header-title">

            <span>
                {{'Configuración de zona'}}
            </span>
        </h1>
    </div>

    <div class="bg-opacity-primary-10 rounded py-2 px-3 d-flex flex-wrap gap-1 align-items-center mb-20">
        <div class="gap-1 d-flex align-items-center">
            <i class="tio-light-on theme-clr-dark fs-16"></i>
            <p class="m-0 fs-12">{{ 'Después de crear una nueva zona, use esto' }}</p>
        </div>
        <p class="m-0">
            <img src="{{asset('assets/admin/img/icons/path-icon.svg')}}" alt=""> {{ 'botón para.' }} <a
                href="#0" class="font-semibold text-title">{{ 'Conectar módulo.' }}</a>
            {{ 'Si no conecta un módulo, no se mostrará en la zona' }}.
        </p>
    </div>

    <!-- End Page Header -->
    <div class="row g-3">
        <div class="col-12">
            <form action="javascript:" method="post" id="zone_form" class="shadow--card">
                <div class="card-header flex-wrap gap-1 pt-0 mb-20">
                    <h4 class="mb-0">{{'Agregar nueva zona'}}</h4>
                    <a href="#0"
                        class="border-primary py-2 px-3 border d-flex align-items-center gap-2 fs-14 font-semibold theme-clr-dark bg-opacity-primary-10 rounded-pill offcanvas-trigger"
                        data-target="#instruction__customBtn2">
                        {{ 'Ver demostración' }} <i class="tio-info-outined"></i>
                    </a>
                </div>
                @csrf
                <div class="row g-3 justify-content-between">
                    <div class="col-md-6">
                        <div class="bg-light rounded p-20">
                            @if($language)
                                <ul class="nav nav-tabs mb-4">
                                    <li class="nav-item">
                                        <a class="nav-link lang_link active" href="#"
                                            id="default-link">{{'por defecto'}}</a>
                                    </li>
                                    @foreach ($language as $lang)
                                        <li class="nav-item">
                                            <a class="nav-link lang_link" href="#"
                                                id="{{ $lang }}-link">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                                        </li>
                                    @endforeach
                                    <span class="form-label-secondary text-danger" data-toggle="tooltip"
                                        data-placement="right"
                                        data-original-title="{{ 'Elija su idioma preferido y establezca el nombre de su zona.' }}"><img
                                            src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                            alt="{{ 'vegetales no vegetales' }}"></span>
                                </ul>
                                <div class="tab-content">
                                    <div class="row g-3 lang_form" id="default-form">
                                        <div class="form-group col-12 mb-0">
                                            <label class="input-label"
                                                for="exampleFormControlInput1">{{ 'nombre de la zona comercial'}}
                                                ({{ 'por defecto' }})</label>
                                            <input type="text" name="name[]" class="form-control"
                                                placeholder="{{'Escriba un nuevo nombre de zona comercial'}}"
                                                maxlength="191">
                                        </div>
                                        <div class="form-group col-12 mb-0">
                                            <label class="input-label"
                                                for="exampleFormControlInput1">{{ 'nombre para mostrar'}}
                                                ({{ 'por defecto' }})</label>
                                            <input type="text" name="display_name[]" class="form-control"
                                                placeholder="{{'Escribir un nuevo nombre de zona de visualización'}}"
                                                maxlength="191">
                                        </div>
                                        <div class="form-group col-12 mb-0">
                                            <label class="input-label" for="max_delivery_radius">
                                                {{'radio máximo de entrega'}} (km) *
                                                <span class="form-label-secondary" data-toggle="tooltip"
                                                    data-placement="right"
                                                    data-original-title="{{'Distancia máxima para la visibilidad del restaurante.'}}">
                                                    <i class="tio-info-outined"></i>
                                                </span>
                                            </label>
                                            <input type="number" step="0.1" min="0.1" name="max_delivery_radius"
                                                class="form-control" required
                                                placeholder="{{'introduzca el radio en km'}}" value="5">
                                        </div>
                                        <input type="hidden" name="lang[]" value="default">
                                    </div>
                                    @foreach($language as $lang)
                                        <div class="row g-3 lang_form d-none" id="{{$lang}}-form">
                                            <div class="form-group col-12 mb-0">
                                                <label class="input-label"
                                                    for="exampleFormControlInput1">{{ 'nombre de la zona comercial'}}
                                                    ({{strtoupper($lang)}})</label>
                                                <input type="text" name="name[]" class="form-control"
                                                    placeholder="{{'Escriba un nuevo nombre de zona comercial'}}"
                                                    maxlength="191">
                                            </div>
                                            <div class="form-group col-12 mb-0">
                                                <label class="input-label"
                                                    for="exampleFormControlInput1">{{ 'nombre para mostrar'}}
                                                    ({{strtoupper($lang)}})</label>
                                                <input type="text" name="display_name[]" class="form-control"
                                                    placeholder="{{'Escribir un nuevo nombre de zona de visualización'}}"
                                                    maxlength="191">
                                            </div>
                                            <input type="hidden" name="lang[]" value="{{$lang}}">
                                        </div>
                                    @endforeach
                            @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-15">
                            <h5 class="mb-0">{{ 'Seleccionar área' }}</h5>
                            <p class="fs-12 m-0">
                                {{ 'Para seleccionar un área, haga clic en el mapa y conecte los puntos.' }}
                            </p>
                        </div>
                        <div class="form-group mb-3 d-none">
                            <label class="input-label"
                                for="exampleFormControlInput1">{{ 'Coordenadas' }}<span
                                    class="form-label-secondary" data-toggle="tooltip" data-placement="right"
                                    data-original-title="{{'dibuja tu zona en el mapa'}}">{{'dibuja tu zona en el mapa'}}</span></label>
                            <textarea type="text" rows="8" name="coordinates" id="coordinates" class="form-control"
                                readonly></textarea>
                        </div>
                        <div class="map-warper map-controler rounded mt-0">
                            <input id="pac-input" class="controls rounded"
                                title="{{'busca tu ubicación aquí'}}" type="text"
                                placeholder="{{'buscar aquí'}}" />
                            <div id="map-canvas" class="rounded"></div>
                        </div>
                    </div>
                </div>
                <div class="btn--container mt-3 justify-content-end">
                    <button id="reset_btn" type="reset"
                        class="btn min-w-120 btn--reset">{{'reiniciar'}}</button>
                    <button type="submit" class="btn min-w-120 btn--primary">{{'entregar'}}</button>
                </div>
            </form>
        </div>



        <div class="col-12">
            <div class="card">
                <div class="card-header py-2 border-0">
                    <div class="search--button-wrapper">
                        <h5 class="card-title">
                            {{'lista de zonas'}}<span class="badge badge-soft-dark ml-2"
                                id="itemCount">{{$zones->total()}}</span>
                        </h5>
                        <form class="search-form">
                            <!-- Search -->
                            <div class="input-group input--group">
                                <input id="datatableSearch_" type="search" name="search" class="form-control"
                                    placeholder="{{'Buscar zona comercial'}}"
                                    value="{{ request()?->search ?? null }}"
                                    aria-label="{{'buscar'}}" required>
                                <button type="submit" class="btn btn--secondary"><i class="tio-search"></i></button>
                            </div>
                            <!-- End Search -->
                        </form>
                        <!-- Unfold -->
                        <div class="hs-unfold mr-2">
                            <a class="js-hs-unfold-invoker btn btn-sm btn-white dropdown-toggle min-height-40"
                                href="javascript:;" data-hs-unfold-options='{
                                            "target": "#usersExportDropdown",
                                            "type": "css-animation"
                                        }'>
                                <i class="tio-download-to mr-1"></i> {{ 'exportar' }}
                            </a>
                            <div id="usersExportDropdown"
                                class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-sm-right">
                                <span class="dropdown-header">{{ 'opciones de descarga' }}</span>
                                <a id="export-excel" class="dropdown-item"
                                    href="{{route('admin.business-settings.zone.export', ['type' => 'excel', request()->getQueryString()])}}">
                                    <img class="avatar avatar-xss avatar-4by3 mr-2"
                                        src="{{ asset('assets/admin') }}/svg/components/excel.svg"
                                        alt="Image Description">
                                    {{ 'sobresalir' }}
                                </a>
                                <a id="export-csv" class="dropdown-item"
                                    href="{{route('admin.business-settings.zone.export', ['type' => 'csv', request()->getQueryString()])}}">
                                    <img class="avatar avatar-xss avatar-4by3 mr-2"
                                        src="{{ asset('assets/admin') }}/svg/components/placeholder-csv-format.svg"
                                        alt="Image Description">
                                    .{{ 'csv' }}
                                </a>
                            </div>
                        </div>
                        <!-- End Unfold -->
                    </div>
                </div>
                <!-- Table -->
                @includeIf('admin-views.zone.partials._table', ['zones' => $zones])
            </div>
        </div>
        <!-- End Table -->
    </div>
</div>
@if ($zone_instruction == '0')

    <div class="modal fade" id="warning-modal">
        <div class="modal-dialog modal-lg warning-modal">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <h3 class="modal-title mb-3">{{'¡Nueva zona comercial creada con éxito!'}}</h3>
                        <span class="text-danger">
                            {{ 'PRÓXIMO PASO IMPORTANTE:' }}
                        </span>
                        {{ 'Necesitas seleccionar' }} <span class="text-dark text-bold text-capitalize">
                            ‘{{ 'Método de pago' }}’
                        </span> <span class="text-lowercase"> {{ 'y agregar' }}</span>
                        <span class="text-dark text-capitalize text-bold">‘{{ 'Módulos de Negocios' }}’ </span>
                        <span class="text-lowercase">
                            {{ 'con otros detalles del.' }}
                        </span>
                        <a href="#" class="" id="module-setup-modal-button-2">{{ 'Conectar módulo' }} </a>.
                        <span>
                            {{ 'Si no finaliza la configuración, la zona que creó no funcionará correctamente.' }}
                        </span>
                    </div>
                    <img src="{{asset('assets/admin/img/zone-settings-popup-arro.gif')}}" alt="admin/img" class="w-100">
                    <div class="mt-3 d-flex flex-wrap align-items-center justify-content-between">
                        <label class="form-check form--check m-0">
                            <input type="checkbox" class="form-check-input rounded redirect-url"
                                data-url="{{route('admin.business-settings.zone.instruction')}}">
                            <span class="form-check-label">{{'No muestres esto más'}}</span>
                        </label>
                        <div class="btn--container justify-content-end">
                            <button id="reset_btn" type="reset" class="btn btn--reset"
                                data-dismiss="modal">{{'lo haré más tarde'}}</button>
                            <a id="module-setup-modal-button"
                                data-url="{{route('admin.business-settings.zone.go-module-setup')}}"
                                class="btn btn--primary redirect-url">{{'Ir a Configuración de zona'}}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

<div class="modal fade" id="status-warning-modal">
    <div class="modal-dialog status-warning-modal">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true" class="tio-clear"></span>
                </button>
            </div>
            <div class="modal-body pt-0">
                <div class="text-center mb-20">
                    <img src="{{asset('assets/admin/img/zone-status-on.png')}}" alt="" class="mb-20">
                    <h5 class="modal-title">
                        {{'Al cambiar el estado a "ON", esta zona y todas las funciones de esta zona se activarán'}}
                    </h5>
                    <p class="txt">
                        {{'En la aplicación y el sitio web del usuario, todas las tiendas y productos ya asignados en esta zona se mostrarán a los clientes.'}}
                    </p>
                </div>
                <div class="btn--container justify-content-center">
                    <button type="submit" class="btn btn--primary min-w-120"
                        data-dismiss="modal">{{'De acuerdo'}}</button>
                    <button id="reset_btn" type="reset" class="btn btn--cancel min-w-120"
                        data-dismiss="modal">{{'Cancelar'}}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="offcanvasOverlay" class="offcanvas-overlay"></div>
<div id="instruction__customBtn2" class="custom-offcanvas d-flex flex-column justify-content-between">
    <div class="offcanvas-inner">
        <div class="custom-offcanvas-header bg--secondary d-flex justify-content-between align-items-center px-3 py-3">
            <h3 class="mb-0 theme-clr-dark">{{ 'Instrucciones' }}</h2>
                <button type="button"
                    class="btn-close w-25px h-25px border rounded-circle d-center bg--secondary text-dark offcanvas-close fz-15px p-0"
                    aria-label="Close">&times;</button>
        </div>
        <div class="custom-offcanvas-body p-20">
            <div class="zone-setup-instructions">
                <div class="zone-setup-top">
                    <p>
                        {{ 'Cree y conecte puntos en un área específica del mapa para agregar una nueva zona comercial.' }}
                    </p>
                </div>
                <div class="zone-setup-item">
                    <div class="zone-setup-icon">
                        <i class="tio-hand-draw"></i>
                    </div>
                    <div class="info">
                        {{ 'Utilice esta "herramienta manual" para encontrar su zona objetivo.' }}
                    </div>
                </div>
                <div class="zone-setup-item">
                    <div class="zone-setup-icon">
                        <i class="tio-free-transform"></i>
                    </div>
                    <div class="info">
                        {{ 'Utilice esta \'Herramienta de forma\' para señalar las áreas y conectar los puntos. Se requieren un mínimo de 3 puntos/puntos.' }}
                    </div>
                </div>
                <div class="instructions-image mt-4">
                    <img src="{{asset('assets/admin/img/instructions.gif')}}" alt="instructions">
                </div>
            </div>
        </div>
    </div>
    <div class="offcanvas-footer p-3 d-flex align-items-center justify-content-center gap-3">

    </div>
</div>

@endsection

@push('script_2')
<script async defer
    src="https://maps.googleapis.com/maps/api/js?key={{\App\Models\BusinessSetting::where('key', 'map_api_key')->first()->value}}&callback=initialize&libraries=drawing,places,marker&v=3.61"></script>
<script>
    "use strict";
    $(".popover-wrapper").click(function () {
        $(".popover-wrapper").removeClass("active");
    });

    $('.status_form_alert').on('click', function (event) {
        let id = $(this).data('id');
        let title = $(this).data('title');
        let message = $(this).data('message');
        status_form_alert(id, title, message, event)
    })

    function status_form_alert(id, title, message, e) {
        e.preventDefault();
        Swal.fire({
            title: title,
            text: message,
            type: 'warning',
            showCancelButton: true,
            cancelButtonColor: 'default',
            confirmButtonColor: '#FC6A57',
            cancelButtonText: '{{ 'No' }}',
            confirmButtonText: '{{ 'Sí' }}',
            reverseButtons: true
        }).then((result) => {
            if (result.value) {
                $('#' + id).submit()
            }
        })
    }
    auto_grow();
    function auto_grow() {
        let element = document.getElementById("coordinates");
        element.style.height = "5px";
        element.style.height = (element.scrollHeight) + "px";
    }


    $(document).on('ready', function () {
        // INITIALIZATION OF DATATABLES
        // =======================================================
        let datatable = $.HSCore.components.HSDatatables.init($('#columnSearchDatatable'));

        $('#column1_search').on('keyup', function () {
            datatable
                .columns(1)
                .search(this.value)
                .draw();
        });


        $('#column3_search').on('change', function () {
            datatable
                .columns(2)
                .search(this.value)
                .draw();
        });


        // INITIALIZATION OF SELECT2
        // =======================================================
        $('.js-select2-custom').each(function () {
            let select2 = $.HSCore.components.HSSelect2.init($(this));
        });

        $("#zone_form").on('keydown', function (e) {
            if (e.keyCode === 13) {
                e.preventDefault();
            }
        })
    });

    let map; // Global declaration of the map
    let drawingManager;
    let lastpolygon = null;
    let polygons = [];

    function resetMap(controlDiv) {
        // Set CSS for the control border.
        const controlUI = document.createElement("div");
        controlUI.style.backgroundColor = "#fff";
        controlUI.style.border = "2px solid #fff";
        controlUI.style.borderRadius = "3px";
        controlUI.style.boxShadow = "0 2px 6px rgba(0,0,0,.3)";
        controlUI.style.cursor = "pointer";
        controlUI.style.marginTop = "8px";
        controlUI.style.marginBottom = "22px";
        controlUI.style.textAlign = "center";
        controlUI.title = "Reset map";
        controlDiv.appendChild(controlUI);
        // Set CSS for the control interior.
        const controlText = document.createElement("div");
        controlText.style.color = "rgb(25,25,25)";
        controlText.style.fontFamily = "Roboto,Arial,sans-serif";
        controlText.style.fontSize = "10px";
        controlText.style.lineHeight = "16px";
        controlText.style.paddingLeft = "2px";
        controlText.style.paddingRight = "2px";
        controlText.innerHTML = "X";
        controlUI.appendChild(controlText);
        // Setup the click event listeners: simply set the map to Chicago.
        controlUI.addEventListener("click", () => {
            lastpolygon.setMap(null);
            $('#coordinates').val('');

        });
    }

    function initialize() {
        @php($default_location = \App\Models\BusinessSetting::where('key', 'default_location')->first())
        @php($default_location = $default_location->value ? json_decode($default_location->value, true) : 0)
        let myLatlng = { lat: {{$default_location ? $default_location['lat'] : '23.757989'}}, lng: {{$default_location ? $default_location['lng'] : '90.360587'}} };
        const mapId = "{{ \App\Models\BusinessSetting::where('key', 'map_api_key')->first()->value }}"

        let myOptions = {
            zoom: 13,
            center: myLatlng,
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            mapId: mapId

        }
        map = new google.maps.Map(document.getElementById("map-canvas"), myOptions);
        drawingManager = new google.maps.drawing.DrawingManager({
            drawingMode: google.maps.drawing.OverlayType.POLYGON,
            drawingControl: true,
            drawingControlOptions: {
                position: google.maps.ControlPosition.TOP_CENTER,
                drawingModes: [google.maps.drawing.OverlayType.POLYGON]
            },
            polygonOptions: {
                editable: true
            }
        });
        drawingManager.setMap(map);


        //get current location block
        // infoWindow = new google.maps.InfoWindow();
        // Try HTML5 geolocation.
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const pos = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude,
                    };
                    map.setCenter(pos);
                });
        }

        drawingManager.addListener("overlaycomplete", function (event) {
            if (lastpolygon) {
                lastpolygon.setMap(null);
            }
            $('#coordinates').val(event.overlay.getPath().getArray());
            lastpolygon = event.overlay;
            auto_grow();
        });

        const resetDiv = document.createElement("div");
        resetMap(resetDiv, lastpolygon);
        map.controls[google.maps.ControlPosition.TOP_CENTER].push(resetDiv);

        // Create the search box and link it to the UI element.
        const input = document.getElementById("pac-input");
        const searchBox = new google.maps.places.SearchBox(input);
        map.controls[google.maps.ControlPosition.TOP_CENTER].push(input);
        // Bias the SearchBox results towards current map's viewport.
        map.addListener("bounds_changed", () => {
            searchBox.setBounds(map.getBounds());
        });
        let markers = [];
        // Listen for the event fired when the user selects a prediction and retrieve
        // more details for that place.
        searchBox.addListener("places_changed", () => {
            const places = searchBox.getPlaces();

            if (places.length == 0) {
                return;
            }
            // Clear out the old markers.
            markers.forEach((marker) => {
                marker.setMap(null);
            });
            markers = [];
            // For each place, get the icon, name and location.
            const bounds = new google.maps.LatLngBounds();
            places.forEach((place) => {
                if (!place.geometry || !place.geometry.location) {
                    console.log("Returned place contains no geometry");
                    return;
                }

                const { AdvancedMarkerElement } = google.maps.marker;

                // Create a marker for each place.
                markers.push(
                    new AdvancedMarkerElement({
                        map,
                        title: place.name,
                        position: place.geometry.location,
                    })
                );

                if (place.geometry.viewport) {
                    // Only geocodes have viewport.
                    bounds.union(place.geometry.viewport);
                } else {
                    bounds.extend(place.geometry.location);
                }
            });
            map.fitBounds(bounds);
        });
    }

    // initialize();


    function set_all_zones() {
        $.get({
            url: '{{route('admin.zone.zoneCoordinates')}}',
            dataType: 'json',
            success: function (data) {
                for (let i = 0; i < data.length; i++) {
                    polygons.push(new google.maps.Polygon({
                        paths: data[i],
                        strokeColor: "#FF0000",
                        strokeOpacity: 0.8,
                        strokeWeight: 2,
                        fillColor: "#FF0000",
                        fillOpacity: 0.1,
                    }));
                    polygons[i].setMap(map);
                }

            },
        });
    }
    $(document).on('ready', function () {
        set_all_zones();
    });


    $('#zone_form').on('submit', function () {
        let formData = new FormData(this);
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.post({
            url: '{{route('admin.business-settings.zone.store')}}',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            beforeSend: function () {
                $('#loading').show();
            },
            success: function (data) {
                if (data.errors) {
                    $.each(data.errors, function (index, value) {
                        toastr.error(value.message);
                    });
                }
                else {
                    $('.tab-content').find('input:text').val('');
                    $('input[name="name"]').val(null);
                    lastpolygon.setMap(null);
                    $('#coordinates').val(null);
                    toastr.success("{{ 'zona agregada exitosamente' }}", {
                        CloseButton: true,
                        ProgressBar: true
                    });
                    $('#set-rows').html(data.view);
                    $('#itemCount').html(data.total);
                    $("#module-setup-modal-button").prop("href", '{{url('/')}}/admin/business-settings/zone/module-setup/' + data.id)
                    $("#module-setup-modal-button-2").prop("href", '{{url('/')}}/admin/business-settings/zone/module-setup/' + data.id)
                    $("#warning-modal").modal("show");
                }
            },
            complete: function () {
                $('#loading').hide();
            },
        });
    });

    $('#reset_btn').click(function () {
        $('.tab-content').find('input:text').val('');

        lastpolygon.setMap(null);
        $('#coordinates').val(null);
    })
</script>
@endpush