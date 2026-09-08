@extends('layouts.admin.app')
@section('title', 'Agregar Lugar de Directorio')
@section('content')
<div class="content container-fluid">
    <div class="page-header">
        <h1 class="page-header-title">Agregar Lugar (Sin dueño)</h1>
    </div>
    <form action="{{route('admin.sabores.directory-places.store')}}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="card-title">Información del lugar</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-7">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="input-label" for="name">Nombre</label>
                                    <input type="text" name="name" class="form-control" placeholder="Nombre" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="input-label" for="zone_id">Zona</label>
                                    <select name="zone_id" id="choice_zones" class="form-control js-select2-custom" required>
                                        <option value="" selected disabled>Seleccionar Zona</option>
                                        @foreach(\App\Models\Zone::active()->get(['id', 'name']) as $zone)
                                            <option value="{{$zone->id}}">{{$zone->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="input-label" for="sabores_map_emoji">Emoji (Opcional)</label>
                                    <input type="text" name="sabores_map_emoji" class="form-control" placeholder="Ej. 🌳 o 🍷" maxlength="5">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="input-label" for="directory_description">Descripción para turismo</label>
                                    <textarea name="directory_description" class="form-control" rows="4" placeholder="Breve descripción del lugar..."></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="input-label">Logo (1:1)</label>
                                    <input type="file" name="logo" class="form-control-file" accept=".jpg, .png, .jpeg" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="input-label">Foto de portada (2:1)</label>
                                    <input type="file" name="cover_photo" class="form-control-file" accept=".jpg, .png, .jpeg">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-5">
                        <div class="bg-light2 rounded p-3">
                            <div class="mb-3">
                                <h4 class="mb-1">Establecer ubicación en el mapa</h4>
                            </div>
                            <div class="map-for-vndor map_custom-controls position-relative">
                                <input id="pac-input" class="controls rounded initial-8" title="buscar aquí" type="text" placeholder="buscar aquí"/>
                                <div id="map" style="height: 300px;"></div>
                                
                                <div class="d-flex bg-white align-items-center gap-1 laglng-controller mt-2 p-2 rounded">
                                    <div id="latlng" class="d-flex">
                                        <input type="text" id="latitude" name="latitude" class="border-0 p-0 m-0 text-center outline-0" placeholder="Latitud" required readonly>
                                        <span class="text-gray1">|</span>
                                        <input type="text" name="longitude" class="border-0 p-0 m-0 text-center outline-0" placeholder="Longitud" id="longitude" required readonly>
                                    </div>
                                </div>
                                <div id="outOfZone" class="map-alert bg-dark d-flex align-items-center rounded-8 py-2 px-2 fs-12 text-white mb-2 mt-2" style="display: none;">
                                    <img src="{{asset('assets/admin/img/icons/warning-cus.png')}}" alt="img"> Por favor coloque el marcador dentro de la zona disponible.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="btn--container mt-4">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('script_2')
    <script>
        window.mapConfig = {
            oldLat: parseFloat("{{ old('latitude') ?? 23.757989 }}"),
            oldLng: parseFloat("{{ old('longitude') ?? 90.360587 }}"),
            oldZoneId: "{{ old('zone_id') }}",
            oldAddress: "",
            translations: {
                selectedLocation: "{{ 'Ubicación seleccionada' }}",
                clickMap: "{{ '¡Haga clic en el mapa dentro del área marcada en rojo para obtener Lat/Lng!' }}",
                selectZone: "{{ 'Seleccione zona en el menú desplegable' }}",
                geolocationError: "{{ 'Error: Su navegador no admite geolocalización.' }}",
                outOfZone: "{{ 'fuera de cobertura' }}",
            },
            urls: {
                zoneCoordinates: "{{ route('admin.zone.get-coordinates', ['id' => ':coordinatesZoneId']) }}",
                zoneGetZone: "{{ route('admin.zone.get-zone') }}",
            }
        };
    </script>
    <script src="{{ asset('assets/admin/js/view-pages/map-functionality.js') }}"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key={{ \App\CentralLogics\Helpers::get_business_settings('map_api_key') }}&libraries=drawing,places,marker,geometry&v=3.61&language={{ str_replace('_', '-', app()->getLocale()) }}&callback=initMap" async defer></script>
    <script>
        $(document).on('ready', function() {
            $('#choice_zones').select2();
            $('#choice_zones').on('change', function() {
                var zone_id = $(this).val();
                if (zone_id) {
                    window.mapConfig.oldZoneId = zone_id;
                    // trigger map update for zone if map-functionality handles it
                }
            });
        });
    </script>
@endpush
