@extends('layouts.admin.app')

@section('title', 'Editar Espacio')

@section('content')
<div class="content container-fluid">
    <div class="page-header">
        <h1 class="page-header-title"><i class="tio-edit"></i> Editar Espacio</h1>
    </div>

    <form action="{{ route('admin.espacios.update', [$listing->id]) }}" method="post" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <div class="card">
                    <div class="card-body">
                        
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="input-label" for="store_id">Vendedor (Store) <span class="input-label-secondary text-danger">*</span></label>
                                <select name="store_id" id="store_id" class="form-control js-select2-custom" required>
                                    <option value="" disabled>Seleccione un vendedor</option>
                                    @foreach($stores as $store)
                                        <option value="{{ $store->id }}" {{ $listing->store_id == $store->id ? 'selected' : '' }}>{{ $store->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-md-6 form-group">
                                <label class="input-label" for="title">Título del Espacio <span class="input-label-secondary text-danger">*</span></label>
                                <input type="text" name="title" class="form-control" placeholder="Ej: Hermosa casa en el centro" value="{{ $listing->title }}" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="input-label" for="description">Descripción <span class="input-label-secondary text-danger">*</span></label>
                            <textarea name="description" class="form-control" rows="4" required>{{ $listing->description }}</textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label class="input-label" for="type">Tipo de Espacio</label>
                                <select name="type" class="form-control" required>
                                    <option value="casa" {{ $listing->type == 'casa' ? 'selected' : '' }}>Casa</option>
                                    <option value="departamento" {{ $listing->type == 'departamento' ? 'selected' : '' }}>Departamento</option>
                                    <option value="habitacion" {{ $listing->type == 'habitacion' ? 'selected' : '' }}>Habitación</option>
                                    <option value="oficina" {{ $listing->type == 'oficina' ? 'selected' : '' }}>Oficina</option>
                                    <option value="sala_eventos" {{ $listing->type == 'sala_eventos' ? 'selected' : '' }}>Sala de eventos</option>
                                </select>
                            </div>

                            <div class="col-md-4 form-group">
                                <label class="input-label" for="city">Ciudad <span class="input-label-secondary text-danger">*</span></label>
                                <input type="text" name="city" class="form-control" value="{{ $listing->city }}" required>
                            </div>

                            <div class="col-md-4 form-group">
                                <label class="input-label" for="price_per_night">Precio por Noche <span class="input-label-secondary text-danger">*</span></label>
                                <input type="number" step="0.01" name="price_per_night" class="form-control" value="{{ $listing->price_per_night }}" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 form-group">
                                <label class="input-label" for="address">Dirección Completa <span class="input-label-secondary text-danger">*</span></label>
                                <input type="text" name="address" class="form-control" value="{{ $listing->address }}" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label class="input-label" for="max_guests">Máx. Huéspedes</label>
                                <input type="number" name="max_guests" class="form-control" value="{{ $listing->max_guests }}" min="1">
                            </div>
                            <div class="col-md-4 form-group">
                                <label class="input-label" for="num_rooms">Número de Cuartos</label>
                                <input type="number" name="num_rooms" class="form-control" value="{{ $listing->num_rooms }}" min="0">
                            </div>
                            <div class="col-md-4 form-group">
                                <label class="input-label" for="num_bathrooms">Número de Baños</label>
                                <input type="number" name="num_bathrooms" class="form-control" value="{{ $listing->num_bathrooms }}" min="0">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 form-group">
                                <label class="input-label" for="amenities">Amenidades</label>
                                @php($listing_amenities = $listing->amenities->pluck('id')->toArray())
                                <select name="amenities[]" class="form-control js-select2-custom" multiple="multiple">
                                    @foreach($amenities as $amenity)
                                        <option value="{{ $amenity->id }}" {{ in_array($amenity->id, $listing_amenities) ? 'selected' : '' }}>{{ $amenity->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 form-group">
                                <h4 class="mb-1">Ubicación en el mapa</h4>
                                <p class="mb-2 fs-12">Marque la ubicación exacta del espacio.</p>
                                <div class="map-for-vndor map_custom-controls position-relative">
                                    <input id="pac-input" class="controls rounded initial-8" style="margin-top: 10px; margin-left: 10px; z-index: 100; position: absolute;" title="busca tu ubicación aquí" type="text" placeholder="buscar aquí"/>
                                    <div id="map" style="height: 300px; border-radius: 10px; background-color: #eee;"></div>
                                    <div class="d-flex align-items-center gap-2 mt-2">
                                        <input type="text" id="latitude" name="lat" class="form-control" placeholder="Latitud" value="{{ old('lat', $listing->lat ?? '19.4326') }}">
                                        <input type="text" id="longitude" name="lng" class="form-control" placeholder="Longitud" value="{{ old('lng', $listing->lng ?? '-99.1332') }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <h4>Qué debes saber</h4>
                                <p class="mb-2 fs-12">Información importante para los huéspedes.</p>
                            </div>
                            <div class="col-md-4 form-group">
                                <label class="input-label" for="cancellation_policy">Política de cancelación</label>
                                <textarea name="cancellation_policy" class="form-control" rows="4" placeholder="Ej. Flexible: Reembolso completo 1 día antes...">{{ old('cancellation_policy', $listing->cancellation_policy) }}</textarea>
                            </div>
                            <div class="col-md-4 form-group">
                                <label class="input-label" for="house_rules">Reglas de la casa</label>
                                <textarea name="house_rules" class="form-control" rows="4" placeholder="Ej. No se admiten mascotas, prohibido fumar...">{{ old('house_rules', $listing->house_rules) }}</textarea>
                            </div>
                            <div class="col-md-4 form-group">
                                <label class="input-label" for="safety_property">Seguridad y propiedad</label>
                                <textarea name="safety_property" class="form-control" rows="4" placeholder="Ej. Cámara de seguridad en la entrada...">{{ old('safety_property', $listing->safety_property) }}</textarea>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-6 form-group">
                                <label class="input-label" for="cover_image">Imagen Principal (Cover)</label>
                                <input type="file" name="cover_image" class="form-control-file" accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                @if($listing->cover_image)
                                    <div class="mt-2">
                                        <img src="{{ asset('storage/espacios/' . $listing->cover_image) }}" alt="Cover Image" style="height: 60px; border-radius: 5px;">
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="input-label" for="images">Agregar a la Galería (Múltiples)</label>
                                <input type="file" name="images[]" class="form-control-file" multiple accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                @if($listing->images->count() > 0)
                                    <div class="mt-2 d-flex flex-wrap gap-2">
                                        @foreach($listing->images as $img)
                                            <img src="{{ asset('storage/espacios/gallery/' . $img->image_path) }}" alt="Gallery Image" style="height: 40px; border-radius: 5px; margin-right: 5px;">
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        
        <div class="btn--container justify-content-end mt-3">
            <button type="reset" class="btn btn--reset">Restablecer</button>
            <button type="submit" class="btn btn--primary">Actualizar Espacio</button>
        </div>
    </form>
</div>
@endsection

@push('script_2')
    <script src="{{ asset('assets/admin/js/view-pages/map-functionality.js') }}"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key={{ \App\CentralLogics\Helpers::get_business_settings('map_api_key') }}&libraries=drawing,places,marker,geometry&v=3.61&language={{ str_replace('_', '-', app()->getLocale()) }}&callback=initMap" async defer></script>
    <script>
        window.mapConfig = {
            defaultLocation: { lat: {{ $listing->lat ?? 19.4326 }}, lng: {{ $listing->lng ?? -99.1332 }} },
            oldLat: parseFloat("{{ old('lat', $listing->lat) }}") || 19.4326,
            oldLng: parseFloat("{{ old('lng', $listing->lng) }}") || -99.1332,
            translations: {
                clickMap: "¡Haga clic en el mapa para obtener Lat/Lng!"
            }
        };
        
        $(document).ready(function() {
            $('.js-select2-custom').select2({
                placeholder: "Selecciona amenidades"
            });
        });
    </script>
@endpush
