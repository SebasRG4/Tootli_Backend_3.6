@extends('layouts.admin.app')

@section('title', 'Editar restaurante')

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">
                        <span class="page-header-icon"><i class="tio-edit"></i></span>
                        <span>{{ 'Editar restaurante' }} - {{ $restaurant->name }}</span>
                    </h1>
                </div>
                <div class="col-sm-auto">
                    <a class="btn btn-icon btn-sm btn-ghost-secondary rounded-circle mr-1" 
                       href="{{ route('admin.sabores.restaurants') }}">
                        <i class="tio-arrow-backward"></i>
                    </a>
                </div>
            </div>
        </div>

        <form action="{{ route('admin.sabores.restaurants.update', $restaurant->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-header-title">{{ 'Ajustes de Sabores de la Ciudad' }}</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                    <label class="input-label" for="cuisine_names">{{ 'Nombres de cocina' }}</label>
                                    <div class="form-group">
                                        <div class="d-flex flex-wrap border rounded p-2" id="tags-container" style="background-color: #fff;">
                                            <!-- Tags will be injected here -->
                                            <input type="text" id="tag-input" class="border-0 flex-grow-1" style="outline: none; min-width: 150px;" placeholder="{{ 'Escribe y presiona Enter' }}">
                                        </div>
                                        @php
                                            $cuisineNames = old('cuisine_names', $restaurant->cuisine_names);
                                            if (is_array($cuisineNames)) {
                                                $cuisineNames = implode(',', $cuisineNames);
                                            }
                                        @endphp
                                        <input type="hidden" name="cuisine_names" id="hidden_cuisine_names" value="{{ $cuisineNames }}">
                                    </div>

                                    @push('script_2')
                                    <script>
                                        $(document).ready(function() {
                                            const $container = $('#tags-container');
                                            const $input = $('#tag-input');
                                            const $hiddenInput = $('#hidden_cuisine_names');
                                            
                                            let tags = $hiddenInput.val() ? $hiddenInput.val().split(',').map(t => t.trim()).filter(t => t) : [];

                                            function renderTags() {
                                                $container.find('.tag-item').remove();
                                                tags.forEach((tag, index) => {
                                                    const tagHtml = `
                                                        <span class="tag-item badge badge-primary m-1 p-2" style="font-size: 14px;">
                                                            ${tag}
                                                            <span class="ml-2 cursor-pointer remove-tag" data-index="${index}" style="cursor: pointer;">&times;</span>
                                                        </span>
                                                    `;
                                                    $input.before(tagHtml);
                                                });
                                                $hiddenInput.val(tags.join(','));
                                            }

                                            $input.on('keydown', function(e) {
                                                if (e.key === 'Enter' || e.key === ',') {
                                                    e.preventDefault();
                                                    
                                                    // MAX TAGS CHECK
                                                    if (tags.length >= 3) {
                                                        toastr.warning('{{ 'Máximo 3 etiquetas permitidas' }}', {
                                                            CloseButton: true,
                                                            ProgressBar: true
                                                        });
                                                        $(this).val('');
                                                        return;
                                                    }

                                                    const val = $(this).val().trim();
                                                    if (val && !tags.includes(val)) {
                                                        tags.push(val);
                                                        renderTags();
                                                    }
                                                    $(this).val('');
                                                } else if (e.key === 'Backspace' && !$(this).val() && tags.length > 0) {
                                                    tags.pop();
                                                    renderTags();
                                                }
                                            });

                                            $container.on('click', '.remove-tag', function() {
                                                const index = $(this).data('index');
                                                tags.splice(index, 1);
                                                renderTags();
                                            });

                                            // Initial render
                                            renderTags();

                                            // Focus input when clicking container
                                            $container.on('click', function(e) {
                                                if (e.target === this) {
                                                    $input.focus();
                                                }
                                            });
                                        });
                                    </script>
                                    @endpush

                                <!-- Icono del pin en mapa (app Sabores) -->
                                <div class="col-md-6 mb-3">
                                    <label class="input-label" for="sabores_map_emoji">{{ 'Emoji marcador de mapa (Sabores)' }}</label>
                                    <select name="sabores_map_emoji" id="sabores_map_emoji" class="form-control"
                                            style="font-size: 1.35rem; line-height: 2rem;">
                                        <option value="">{{ 'Automático por cocina o nombre' }}</option>
                                        @foreach(config('sabores.map_marker_emojis', []) as $mapEmoji)
                                            <option value="{{ $mapEmoji }}"
                                                @selected(old('sabores_map_emoji', $restaurant->sabores_map_emoji) === $mapEmoji)>
                                                {{ $mapEmoji }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted d-block mt-1">{{ 'Se muestra en el pin del mapa de la ciudad de este restaurante.' }}</small>
                                </div>

                                <!-- Average Ticket -->
                                <div class="col-md-6 mb-3">
                                    <label class="input-label" for="average_ticket">
                                        {{ 'Boleto promedio' }} ({{ \App\CentralLogics\Helpers::currency_symbol() }})
                                        <span class="input-label-secondary" title="{{ 'Costo promedio por persona' }}">
                                            <i class="tio-info-outined"></i>
                                        </span>
                                    </label>
                                    <input type="number" step="0.01" min="0" name="average_ticket" 
                                           class="form-control" id="average_ticket" 
                                           value="{{ old('average_ticket', $restaurant->average_ticket) }}"
                                           placeholder="{{ 'por ejemplo, 25.00' }}">
                                </div>

                                <!-- Serves Alcohol -->
                                <div class="col-md-6 mb-3">
                                    <label class="input-label d-block">{{ 'Sirve alcohol' }}</label>
                                    <label class="toggle-switch toggle-switch-sm" for="serves_alcohol">
                                        <input type="checkbox" class="toggle-switch-input" 
                                               id="serves_alcohol" name="serves_alcohol" 
                                               {{ $restaurant->serves_alcohol ? 'checked' : '' }} value="1">
                                        <span class="toggle-switch-label">
                                            <span class="toggle-switch-indicator"></span>
                                        </span>
                                    </label>
                                </div>

                                <!-- Accepts Reservations -->
                                <div class="col-md-6 mb-3">
                                    <label class="input-label d-block">{{ 'Acepta Reservas' }}</label>
                                    <label class="toggle-switch toggle-switch-sm" for="accepts_reservations">
                                        <input type="checkbox" class="toggle-switch-input" 
                                               id="accepts_reservations" name="accepts_reservations" 
                                               {{ $restaurant->accepts_reservations ? 'checked' : '' }} value="1">
                                        <span class="toggle-switch-label">
                                            <span class="toggle-switch-indicator"></span>
                                        </span>
                                    </label>
                                </div>

                                <!-- Exclude from Sabores de la Ciudad -->
                                <div class="col-md-6 mb-3">
                                    <label class="input-label d-block">{{ 'Excluir de Sabores de la Ciudad (por ejemplo, Dark Kitchens)' }}</label>
                                    <label class="toggle-switch toggle-switch-sm" for="exclude_from_sabores">
                                        <input type="checkbox" class="toggle-switch-input" 
                                               id="exclude_from_sabores" name="exclude_from_sabores" 
                                               {{ $restaurant->exclude_from_sabores ? 'checked' : '' }} value="1">
                                        <span class="toggle-switch-label">
                                            <span class="toggle-switch-indicator"></span>
                                        </span>
                                    </label>
                                </div>

                                <!-- Event Settings -->
                                <div class="col-md-12 mt-4 mb-2">
                                    <h4 class="text-primary"><i class="tio-calendar"></i> {{ 'Configuración del evento Sabores' }}</h4>
                                    <hr class="mt-1 mb-3">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="input-label" for="event_title">{{ 'Título del evento' }}</label>
                                    <input type="text" name="event_title" class="form-control" id="event_title" 
                                           value="{{ old('event_title', $restaurant->event_title) }}"
                                           placeholder="{{ 'por ejemplo, Taco Fest, Noche de Jazz en Vivo' }}">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="input-label" for="event_date">{{ 'Fecha del evento' }}</label>
                                    <input type="date" name="event_date" class="form-control" id="event_date" 
                                           value="{{ old('event_date', $restaurant->event_date ? \Carbon\Carbon::parse($restaurant->event_date)->format('Y-m-d') : '') }}">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="input-label">{{ 'Etiqueta adhesiva del mapa del evento (se recomienda fondo PNG transparente)' }}</label>
                                    <div class="custom-file">
                                        <input type="file" name="event_image" id="customFileEg1" class="custom-file-input"
                                               accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                        <label class="custom-file-label" for="customFileEg1">{{'Elija archivo'}}</label>
                                    </div>
                                    <div class="mt-2">
                                        <img id="viewerEvent" src="{{ $restaurant->event_image ? $restaurant->event_image_full_url : asset('assets/admin/img/400x400/img2.jpg') }}" alt="Event Sticker Image" class="img-thumbnail" style="max-height: 150px; object-fit: cover;">
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="input-label">{{ 'Foto de fondo de tarjeta de evento' }}</label>
                                    <div class="custom-file">
                                        <input type="file" name="event_card_image" id="customFileEg2" class="custom-file-input"
                                               accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                        <label class="custom-file-label" for="customFileEg2">{{'Elija archivo'}}</label>
                                    </div>
                                    <div class="mt-2">
                                        <img id="viewerEventCard" src="{{ $restaurant->event_card_image ? $restaurant->event_card_image_full_url : asset('assets/admin/img/400x400/img2.jpg') }}" alt="Event Card Image" class="img-thumbnail" style="max-height: 150px; object-fit: cover;">
                                    </div>
                                </div>

                                <!-- Google Address -->
                                <div class="col-md-12 mb-3">
                                    <label class="input-label" for="google_address">
                                        {{ 'Dirección de Google' }}
                                        <span class="input-label-secondary" title="{{ 'Dirección de Google Maps' }}">
                                            <i class="tio-info-outined"></i>
                                        </span>
                                    </label>
                                    <input type="text" name="google_address" class="form-control" id="google_address" 
                                           value="{{ old('google_address', $restaurant->google_address) }}"
                                           placeholder="{{ 'por ejemplo, 1600 Amphitheatre Parkway, Mountain View, CA' }}">
                                           
                                    <input type="hidden" name="google_place_id" id="google_place_id" value="{{ old('google_place_id', $restaurant->google_place_id) }}">
                                </div>

                                <!-- Infrastructure Images -->
                                <div class="col-12">
                                     <div class="form-group mb-0">
                                        <label class="input-label">{{ 'Imágenes de infraestructura' }} ({{ 'Relación 1:1' }})</label>
                                        <div class="row" id="infrastructure_images">
                                            @if(isset($restaurant->infrastructure_images_full_url) && count($restaurant->infrastructure_images_full_url) > 0)
                                                @foreach($restaurant->infrastructure_images_full_url as $key => $img)
                                                    @php
                                                        $rawImage = $restaurant->infrastructure_images[$key] ?? null;
                                                        $imageName = is_array($rawImage) ? $rawImage['img'] : $rawImage;
                                                    @endphp
                                                    <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-4 sortable-img" id="infrastructure_image_{{$key}}" data-filename="{{basename($imageName)}}">
                                                        <div class="img_box" style="position: relative; aspect-ratio: 1; border: 1px dashed #e2e2e2; border-radius: 5px; overflow: hidden; display: flex; align-items: center; justify-content: center; cursor: move;">
                                                            <img src="{{$img}}" style="width: 100%; height: 100%; object-fit: cover;">
                                                            <div class="remove_btn" onclick="removeInfrastructureImage({{$key}}, '{{basename($imageName)}}')" style="position: absolute; top: 0; right: 0; background: #ff4d4d; color: white; border-radius: 0 0 0 5px; cursor: pointer; padding: 2px 6px; z-index: 10;">
                                                                <i class="tio-clear"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>
                                        <input type="hidden" id="removedImageKeys" name="removedImageKeys" value="">
                                    </div>

                                    <!-- Menu Images -->
                                    <div class="col-12 mt-4">
                                        <label class="input-label">{{ 'Imágenes del menú' }} ({{ 'Relación 1:1' }})</label>
                                        <div class="row" id="menu_images">
                                            @if(isset($restaurant->menu_images_full_url) && count($restaurant->menu_images_full_url) > 0)
                                                @foreach($restaurant->menu_images_full_url as $key => $img)
                                                    @php
                                                        $rawImage = $restaurant->menu_images[$key] ?? null;
                                                        $imageName = is_array($rawImage) ? $rawImage['img'] : $rawImage;
                                                    @endphp
                                                    <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-4" id="menu_image_{{$key}}">
                                                        <div class="img_box" style="position: relative; aspect-ratio: 1; border: 1px dashed #e2e2e2; border-radius: 5px; overflow: hidden; display: flex; align-items: center; justify-content: center;">
                                                            <img src="{{$img}}" style="width: 100%; height: 100%; object-fit: cover;">
                                                            <div class="remove_btn" onclick="removeMenuImage({{$key}}, '{{basename($imageName)}}')" style="position: absolute; top: 0; right: 0; background: #ff4d4d; color: white; border-radius: 0 0 0 5px; cursor: pointer; padding: 2px 6px; z-index: 10;">
                                                                <i class="tio-clear"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>
                                        <input type="hidden" id="removedMenuImageKeys" name="removedMenuImageKeys" value="">
                                    </div>
                                    </div>

                                <!-- Operating Hours -->
                                <div class="col-12 mt-4">
                                    <h4 class="mb-3">{{ 'Horas de funcionamiento' }}</h4>
                                    <div class="row">
                                        @php
                                            $days = [
                                                0 => 'Sunday',
                                                1 => 'Monday',
                                                2 => 'Tuesday',
                                                3 => 'Wednesday',
                                                4 => 'Thursday',
                                                5 => 'Friday',
                                                6 => 'Saturday'
                                            ];
                                        @endphp
                                        @foreach($days as $key => $day)
                                            @php
                                                $schedule = $restaurant->schedules->where('day', $key)->first();
                                                $open = $schedule ? \Carbon\Carbon::parse($schedule->opening_time)->format('H:i') : '';
                                                $close = $schedule ? \Carbon\Carbon::parse($schedule->closing_time)->format('H:i') : '';
                                            @endphp
                                            <div class="col-md-6 mb-3">
                                                <div class="card p-3 border">
                                                    <h5 class="mb-2">{{ translate($day) }}</h5>
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <div class="form-group mb-0 mr-2 w-100">
                                                            <label>{{ 'Hora de apertura' }}</label>
                                                            <input type="time" name="schedules[{{$key}}][opening_time]" class="form-control" value="{{ $open }}">
                                                        </div>
                                                        <div class="form-group mb-0 w-100">
                                                            <label>{{ 'Hora de cierre' }}</label>
                                                            <input type="time" name="schedules[{{$key}}][closing_time]" class="form-control" value="{{ $close }}">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="tio-save"></i> {{ 'Guardar cambios' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('script_2')
    <script src="{{ asset('assets/admin/js/spartan-multi-image-picker.js') }}"></script>
    <script src="https://code.jquery.com/ui/1.13.0/jquery-ui.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize Sortable
            $("#infrastructure_images").sortable({
                items: ".sortable-img",
                cursor: "move",
                opacity: 0.7,
                update: function(event, ui) {
                    let imageOrder = [];
                    $(".sortable-img").each(function() {
                        imageOrder.push($(this).data('filename'));
                    });

                    // Send AJAX request
                    $.ajax({
                        url: "{{ route('admin.sabores.restaurants.update-images-order', $restaurant->id) }}",
                        method: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            images: imageOrder
                        },
                        success: function(response) {
                            toastr.success(response.message);
                        },
                        error: function(xhr) {
                            toastr.error('{{ 'No se pudo actualizar el orden de las imágenes' }}');
                        }
                    });
                }
            });

            $("#infrastructure_images").spartanMultiImagePicker({
                fieldName: 'infrastructure_images[]',
                maxCount: 10,
                rowHeight: '120px',
                groupClassName: 'col-6 col-sm-4 col-md-3 col-lg-2',
                maxFileSize: '',
                placeholderImage: {
                    image: "{{ asset('assets/admin/img/400x400/img2.jpg') }}",
                    width: '100%'
                },
                dropFileLabel: "Drop Here",
                onAddRow: function(index, file) {

                },
                onRenderedPreview: function(index) {

                },
                onRemoveRow: function(index) {

                },
                onExtensionErr: function(index, file) {
                    toastr.error('{{ 'Por favor ingrese solo archivos tipo png o jpg' }}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                },
                onSizeErr: function(index, file) {
                    toastr.error('{{ 'tamaño de archivo demasiado grande' }}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }
            });
        });

        function removeInfrastructureImage(index, key) {
            $('#infrastructure_image_' + index).remove();
            let removedImageKeys = $('#removedImageKeys').val();
            if (removedImageKeys === '') {
                removedImageKeys = key;
            } else {
                removedImageKeys += ',' + key;
            }
            $('#removedImageKeys').val(removedImageKeys);
        }

        function removeMenuImage(index, key) {
            $('#menu_image_' + index).remove();
            let removedImageKeys = $('#removedMenuImageKeys').val();
            if (removedImageKeys === '') {
                removedImageKeys = key;
            } else {
                removedImageKeys += ',' + key;
            }
            $('#removedMenuImageKeys').val(removedImageKeys);
        }

        $(document).ready(function() {
            $("#menu_images").spartanMultiImagePicker({
                fieldName: 'menu_images[]',
                maxCount: 20,
                rowHeight: '120px',
                groupClassName: 'col-6 col-sm-4 col-md-3 col-lg-2',
                maxFileSize: '',
                placeholderImage: {
                    image: "{{ asset('assets/admin/img/400x400/img2.jpg') }}",
                    width: '100%'
                },
                dropFileLabel: "Drop Here",
                onAddRow: function(index, file) {},
                onRenderedPreview: function(index) {},
                onRemoveRow: function(index) {},
                onExtensionErr: function(index, file) {
                    toastr.error('{{ 'Por favor ingrese solo archivos tipo png o jpg' }}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                },
                onSizeErr: function(index, file) {
                    toastr.error('{{ 'tamaño de archivo demasiado grande' }}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }
            });
        });

        function readURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    $('#viewerEvent').attr('src', e.target.result);
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
        $("#customFileEg1").change(function () {
            readURL(this);
        });

        function readURLCard(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    $('#viewerEventCard').attr('src', e.target.result);
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
        $("#customFileEg2").change(function () {
            readURLCard(this);
        });
    </script>
@endpush
