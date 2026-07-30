@extends('layouts.admin.app')

@section('title','Zona de actualización')

@push('css_or_js')

@endpush

@section('content')

    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('assets/admin/img/edit.png')}}" class="w--26" alt="">
                </span>
                <span>
                   {{ 'editar zona'}}
                </span>
            </h1>
        </div>
        <!-- End Page Header -->
        <form action="{{route('admin.business-settings.zone.update', $zone->id)}}" method="post" id="zone_form" class="shadow--card">
            @csrf
            <div class="row">
                <div class="col-md-5">
                    <div class="zone-setup-instructions">
                        <div class="zone-setup-top">
                            <h6 class="subtitle">{{ 'Instrucciones' }}</h6>
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
                <div class="col-md-6 col-xl-7 zone-setup">
                    <div class="form-group">
                        @if($language)
                            <ul class="nav nav-tabs mb-4">
                                <li class="nav-item">
                                    <a class="nav-link lang_link active"
                                    href="#"
                                    id="default-link">{{'por defecto'}}</a>
                                </li>
                                @foreach ($language as $lang)
                                    <li class="nav-item">
                                        <a class="nav-link lang_link"
                                            href="#"
                                            id="{{ $lang }}-link">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>

                    <div class="pl-xl-5 pl-xxl-0">
                        @if($language)
                            <div class="row lang_form" id="default-form">
                                <div class="form-group col-6">
                                    <label class="input-label" for="exampleFormControlInput1">{{'nombre'}} ({{ 'por defecto' }})</label>
                                    <input type="text" name="name[]" class="form-control" placeholder="{{'nueva zona'}}" maxlength="191" value="{{$zone?->getRawOriginal('name')}}"  >
                                </div>
                                <div class="form-group col-6">
                                    <label class="input-label" for="exampleFormControlInput1">{{'nombre para mostrar'}} ({{ 'por defecto' }})</label>
                                    <input type="text" name="display_name[]" class="form-control" placeholder="{{'nombre para mostrar'}}" maxlength="191" value="{{$zone?->getRawOriginal('display_name')}}"  >
                                </div>
                                <div class="form-group col-6">
                                    <label class="input-label" for="max_delivery_radius">
                                        {{'radio máximo de entrega'}} (km) *
                                        <span class="form-label-secondary" data-toggle="tooltip" data-placement="right"
                                            data-original-title="{{'Distancia máxima para la visibilidad del restaurante.'}}">
                                            <i class="tio-info-outined"></i>
                                        </span>
                                    </label>
                                    <input type="number" step="0.1" min="0.1" name="max_delivery_radius"
                                        class="form-control" required
                                        placeholder="{{'introduzca el radio en km'}}"
                                        value="{{$zone->max_delivery_radius ?? 5}}">
                                </div>
                                <input type="hidden" name="lang[]" value="default">
                            </div>
                                @foreach($language as $lang)
                                    <?php
                                        if(count($zone['translations'])){
                                            $translate = [];
                                            foreach($zone['translations'] as $t)
                                            {
                                                if($t->locale == $lang && $t->key=="name"){
                                                    $translate[$lang]['name'] = $t->value;
                                                }
                                                if($t->locale == $lang && $t->key=="display_name"){
                                                    $translate[$lang]['display_name'] = $t->value;
                                                }
                                            }
                                        }
                                    ?>
                                <div class="row lang_form d-none" id="{{$lang}}-form">
                                    <div class="form-group col-6">
                                        <label class="input-label" for="exampleFormControlInput1">{{'nombre'}} ({{strtoupper($lang)}})</label>
                                        <input type="text" name="name[]" class="form-control" placeholder="{{'nueva zona'}}" maxlength="191" value="{{$translate[$lang]['name']??''}}"  >
                                    </div>
                                    <div class="form-group col-6">
                                        <label class="input-label" for="exampleFormControlInput1">{{'nombre para mostrar'}} ({{strtoupper($lang)}})</label>
                                        <input type="text" name="display_name[]" class="form-control" placeholder="{{'nombre para mostrar'}}" maxlength="191" value="{{$translate[$lang]['display_name']??''}}"  >
                                    </div>
                                    <input type="hidden" name="lang[]" value="{{$lang}}">
                                </div>
                                @endforeach
                            @endif
                        <div class="form-group d-none">
                            <label class="input-label" for="exampleFormControlInput1">{{ 'Coordenadas' }}
                                <span class="form-label-secondary" data-toggle="tooltip" data-placement="right" data-original-title="{{'dibuja tu zona en el mapa'}}">
                                    {{'dibuja tu zona en el mapa'}}
                                </span>
                            </label>
                                <textarea type="text" rows="8" name="coordinates" id="coordinates" class="form-control" readonly>@foreach($zone->coordinates[0]->toArray()['coordinates'] as $key=>$coords)<?php if(count($zone->coordinates[0]->toArray()['coordinates']) != $key+1) {if($key != 0) echo(','); ?>({{$coords[1]}}, {{$coords[0]}})<?php } ?>@endforeach</textarea>
                        </div>


                        <div class="map-warper rounded mt-0">
                            <input id="pac-input" class="controls rounded initial--33" title="{{'busca tu ubicación aquí'}}" type="text" placeholder="{{'buscar aquí'}}"/>
                            <div id="map-canvas" class="initial--34"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="btn--container mt-3 justify-content-end">
                <button id="reset_btn" type="reset" class="btn btn--reset">{{'reiniciar'}}</button>
                <button type="submit" class="btn btn--primary">{{'Guardar cambios'}}</button>
            </div>
        </form>
    </div>

@endsection

@push('script_2')
<script src="https://maps.googleapis.com/maps/api/js?v=3.45.8&key={{\App\Models\BusinessSetting::where('key', 'map_api_key')->first()->value}}&libraries=drawing,places,marker&v=3.61"></script>
<script>
    "use strict";
    auto_grow();
    function auto_grow() {
        let element = document.getElementById("coordinates");
        element.style.height = "5px";
        element.style.height = (element.scrollHeight)+"px";
    }

    let map; // Global declaration of the map
    let lat_longs = new Array();
    let drawingManager;
    let lastpolygon = null;
    let bounds = new google.maps.LatLngBounds();
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
        let myLatlng = new google.maps.LatLng({{trim(explode(' ',$zone->center)[1], 'POINT()')}}, {{trim(explode(' ',$zone->center)[0], 'POINT()')}});
        const mapId = "{{ \App\Models\BusinessSetting::where('key', 'map_api_key')->first()->value }}"

        let myOptions = {
            zoom: 13,
            center: myLatlng,
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            mapId:mapId
        };
        map = new google.maps.Map(document.getElementById("map-canvas"), myOptions);

        const polygonCoords = [

            @foreach($area['coordinates'] as $coords)
             { lat: {{$coords[1]}}, lng: {{$coords[0]}} },
            @endforeach
        ];

        let zonePolygon = new google.maps.Polygon({
            paths: polygonCoords,
            strokeColor: "#050df2",
            strokeOpacity: 0.8,
            strokeWeight: 2,
            fillOpacity: 0,
        });

        zonePolygon.setMap(map);

        zonePolygon.getPaths().forEach(function(path) {
            path.forEach(function(latlng) {
                bounds.extend(latlng);
                map.fitBounds(bounds);
            });
        });


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

        google.maps.event.addListener(drawingManager, "overlaycomplete", function(event) {
            let newShape = event.overlay;
            newShape.type = event.type;
        });

        google.maps.event.addListener(drawingManager, "overlaycomplete", function(event) {
            if(lastpolygon)
                {
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
                const icon = {
                    url: place.icon,
                    size: new google.maps.Size(71, 71),
                    origin: new google.maps.Point(0, 0),
                    anchor: new google.maps.Point(17, 34),
                    scaledSize: new google.maps.Size(25, 25),
                };
                const { AdvancedMarkerElement } = google.maps.marker;

                // Create a marker for each place.
                markers.push(
                    new AdvancedMarkerElement({
                    map,
                    icon,
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
    google.maps.event.addDomListener(window, 'load', initialize);

    function set_all_zones()
    {
        $.get({
            url: '{{route('admin.zone.zoneCoordinates')}}/{{$zone->id}}',
            dataType: 'json',
            success: function (data) {

                console.log(data);
                for(let i=0; i<data.length;i++)
                {
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
    $(document).on('ready', function(){
        set_all_zones();
        $("#zone_form").on('keydown', function(e){
            if (e.keyCode === 13) {
                e.preventDefault();
            }
        })
    });

    $('#reset_btn').click(function(){
        location.reload(true);
    })

</script>
@endpush
