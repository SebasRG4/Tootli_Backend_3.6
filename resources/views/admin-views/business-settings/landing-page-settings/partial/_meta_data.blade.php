<div class="card">
    <div class="card-body">
        <h3 class="mb-1">{{ 'Configuración de metadatos' }}</h3>
        <p class="text--secondary m-0">{{ 'Incluya metatítulo, descripción e imagen para mejorar la visibilidad en los motores de búsqueda y el intercambio en las redes sociales.' }}</p>
    </div>

    <div class="row px-4 mb-4  g-3">
        <div class="col-lg-8">
            <div class="bg--secondary rounded p-xxl-4 p-3">
                <ul class="nav nav-tabs mb-4">
                    <li class="nav-item">
                        <a class="nav-link lang_link active" href="#"
                           id="default-link">{{ 'Por defecto' }}</a>
                    </li>
                    @foreach ($language as $lang)
                        <li class="nav-item">
                            <a class="nav-link lang_link" href="#"
                               id="{{ $lang }}-link">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                        </li>
                    @endforeach
                </ul>
                <div class="lang_form" id="default-form">
                    <div class="form-group mb-2">
                        <label class="input-label" for="default_title">{{ 'Metatítulo' }}
                            ({{ 'Por defecto' }})<span class="form-label-secondary"
                                                                       data-toggle="tooltip" data-placement="right"
                                                                       data-original-title="{{ 'Este título aparece en las pestañas del navegador, en los resultados de búsqueda y en las vistas previas de enlaces. Utilice un título breve, claro y centrado en palabras clave (recomendado: entre 50 y 60 caracteres).' }}">
                                <i class="tio-info color-A7A7A7"></i>
                            </span>
                        </label>
                        <input type="text" name="meta_title[]" id="default_title" maxlength="100" class="form-control"
                               placeholder="{{ 'Metatítulo' }}"
                               value="{{ isset($landingData['meta_title']) ? $landingData['meta_title']->getRawOriginal('value') : '' }}">
                        <span class="text-right text-counting color-A7A7A7 d-block mt-1">0/100</span>
                    </div>
                    <input type="hidden" name="lang[]" value="default">
                    <div class="form-group mb-0">
                        <label class="input-label" for="exampleFormControlInput1">{{ 'Meta descripción' }}
                            ({{ 'por defecto' }})<span class="form-label-secondary"
                                                                       data-toggle="tooltip" data-placement="right"
                                                                       data-original-title="{{ 'Un breve resumen que aparece debajo del título de su página en los resultados de búsqueda. Manténgalo atractivo y relevante (recomendado: 120 a 160 caracteres).' }}">
                                <i class="tio-info color-A7A7A7"></i>
                            </span></label>
                        <textarea type="text" name="meta_description[]" maxlength="200"
                                  placeholder="{{ 'Meta descripción' }}"
                                  class="form-control min-h-90px ckeditor">{{ isset($landingData['meta_description']) ? $landingData['meta_description']->getRawOriginal('value') : '' }}</textarea>
                        <span class="text-right text-counting color-A7A7A7 d-block mt-1">0/200</span>
                    </div>
                </div>
                @foreach ($language as $lang)
                        <?php
                        if (isset($landingData['meta_title']) && isset($landingData['meta_title']->translations) && count($landingData['meta_title']->translations)) {
                            $meta_title = [];
                            foreach ($landingData['meta_title']->translations as $t) {
                                if ($t->locale == $lang && $t->key == 'meta_title') {
                                    $meta_title[$lang]['value'] = $t->value;
                                }
                            }
                        }
                        if (isset($landingData['meta_description']) && isset($landingData['meta_description']->translations) && count($landingData['meta_description']->translations)) {
                            $meta_description = [];
                            foreach ($landingData['meta_description']->translations as $t) {
                                if ($t->locale == $lang && $t->key == 'meta_description') {
                                    $meta_description[$lang]['value'] = $t->value;
                                }
                            }
                        }
                        ?>
                    <div class="d-none lang_form" id="{{ $lang }}-form">
                        <div class="form-group mb-2">
                            <label class="input-label" for="{{ $lang }}_title">{{ 'Metatítulo' }}
                                ({{ strtoupper($lang) }})
                                <span class="form-label-secondary" data-toggle="tooltip" data-placement="right"
                                      data-original-title="{{ 'Este título aparece en las pestañas del navegador, en los resultados de búsqueda y en las vistas previas de enlaces. Utilice un título breve, claro y centrado en palabras clave (recomendado: entre 50 y 60 caracteres).' }}">
                                    <i class="tio-info color-A7A7A7"></i>
                                </span>
                            </label>
                            <input type="text" name="meta_title[]" maxlength="100" id="{{ $lang }}_title"
                                   class="form-control" value="{{ $meta_title[$lang]['value'] ?? '' }}"
                                   placeholder="{{ 'Metatítulo' }}">
                            <span class="text-right text-counting color-A7A7A7 d-block mt-1">0/100</span>
                        </div>
                        <input type="hidden" name="lang[]" value="{{ $lang }}">
                        <div class="form-group mb-0">
                            <label class="input-label"
                                   for="exampleFormControlInput1">{{ 'Meta descripción' }}
                                ({{ strtoupper($lang) }})<span class="form-label-secondary" data-toggle="tooltip"
                                                               data-placement="right"
                                                               data-original-title="{{ 'Un breve resumen que aparece debajo del título de su página en los resultados de búsqueda. Manténgalo atractivo y relevante (recomendado: 120 a 160 caracteres).' }}">
                                    <i class="tio-info color-A7A7A7"></i>
                                </span></label>
                            <textarea type="text" name="meta_description[]" maxlength="200"
                                      placeholder="{{ 'Meta descripción' }}"
                                      class="form-control min-h-90px ckeditor">{{ $meta_description[$lang]['value'] ?? '' }}</textarea>
                            <span class="text-right text-counting color-A7A7A7 d-block mt-1">0/200</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="col-lg-4">
            <!-- <div class="card h-100">
                <div class="card-body d-flex flex-column justify-content-center">
                    <div>
                        <div class="d-flex justify-content-center">
                            <label class="text-dark d-block mb-4">
                                <strong>{{ 'Metaimagen' }}</strong>
                                <small class=""> {{ '(Relación 2:1)' }}</small>
                                <span class="form-label-secondary"
                                    data-toggle="tooltip" data-placement="right"
                                    data-original-title="{{ 'Esta imagen se utiliza como miniatura de vista previa cuando el enlace de la página se comparte en redes sociales o plataformas de mensajería.' }}">
                                    <i class="tio-info color-A7A7A7"></i>
                                </span></label>
                            </label>
                        </div>
                        <div class="d-flex justify-content-center">
                            <label class="text-center position-relative">
                                <img class="img--110 min-height-170px min-width-170px onerror-image image--border"
                                    id="viewer" data-onerror-image="{{ asset('assets/admin/img/upload.png') }}"
                                    src="{{ \App\CentralLogics\Helpers::get_full_url('landing/meta_image', $landingData['meta_image']?->value ?? '', $landingData['meta_image']?->storage[0]?->value ?? 'public', 'upload_image') }}"
                                    alt="logo image" />
                                <div class="icon-file-group">
                                    <div class="icon-file">
                                        <i class="tio-edit"></i>
                                        <input type="file" name="image" id="customFileEg1" class="custom-file-input"
                                            accept=".webp, .jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                    </div>
                                </div>
                            </label>
                        </div>
                        <div class="d-flex justify-content-center">
                            <div class="text-center">
                                <small>{{ 'Sube una imagen rectangular (tamaño recomendado: 800×400 px, formato: JPG o PNG)' }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div> -->
            @php($meta_image = \App\Models\DataSetting::where('type', 'react_landing_page')->where('key', "meta_image")->first())
            <div class="bg--secondary h-100 rounded p-md-4 p-3 d-center">
                <div class="text-center">
                    <div class="mb-30">
                        <h5 class="mb-1">{{ 'Metaimagen' }}</h5>
                        <p class="mb-0 fs-12 gray-dark">{{ 'Sube una imagen rectangular' }}</p>

                    </div>
                    <div class="mx-auto text-center error-wrapper">
                        <div class="upload-file_custom ratio-2-1 h-100px">
                            <input  type="file" name="image" class="upload-file__input single_file_input"
                                   accept="{{IMAGE_EXTENSION}}" {{ $meta_image?->value ? '' : 'required' }}>
                            <label class="upload-file__wrapper w-100 h-100 m-0">
                                <div class="upload-file-textbox text-center"
                                     style="{{ $meta_image?->value ? 'display: none;' : '' }}">
                                    <img width="22" class="svg"
                                         src="{{asset('assets/admin/img/document-upload.svg')}}" alt="img">
                                    <h6 class="mt-1 color-656566 fw-medium fs-10 lh-base text-center">
                                        <span class="theme-clr">Click to upload</span>
                                        <br>
                                        Or drag and drop
                                    </h6>
                                </div>
                                <img class="upload-file-img" loading="lazy" src="{{ $meta_image?->value
    ? \App\CentralLogics\Helpers::get_full_url('landing/meta_image', $meta_image->value, $meta_image->storage[0]?->value ?? 'public', 'aspect_1')
    : '' }}" data-default-src="{{$meta_image?->value
    ? \App\CentralLogics\Helpers::get_full_url('landing/meta_image', $meta_image->value, $meta_image->storage[0]?->value ?? 'public', 'aspect_1')
    : ''}} " alt="" style="display: none;">
                            </label>
                            <div class="overlay">
                                <div class="d-flex gap-1 justify-content-center align-items-center h-100">
                                    <button type="button" class="btn btn-outline-info icon-btn view_btn">
                                        <i class="tio-invisible"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-info icon-btn edit_btn">
                                        <i class="tio-edit"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="fs-12 opacity-70">
                        {{ translate(IMAGE_FORMAT.' ' . 'Less Than 2MB') }}
                    </div>
                </div>
            </div>
        </div>
        {{-- <div class="col-12">
                    <div class="card shadow-none border-0 bg-soft-danger">
                        <div class="card-body d-flex">
                            <i class="mr-2 mt-3 text-danger tio-info-outined"></i>
                            <p class="fs-15 text-dark m-0">
                                <strong>{{ 'Nota:' }}</strong> {{ 'Personalice la sección agregando un título, una breve descripción e imágenes en el' }} <a href="{{ route('admin.business-settings.zone.home') }}" target="_blank" class="text--underline text-006AE5">{{ 'Configuración de zona' }}</a> {{ 'sección. Todas las zonas creadas se mostrarán automáticamente en la' }} <a href="{{route('home')}}" target="_blank" class="text-primary">{{ 'Aterrizaje de administrador' }}</a> {{ 'Página. Las zonas se basarán en el nombre para mostrar de la zona.' }}
                            </p>
                        </div>
                    </div>
                </div> --}}
        <div class="col-12">
            <div class="btn--container justify-content-end">
                <button class="btn btn--reset " type="reset">{{ 'reiniciar' }}</button>
                <button class="btn btn--primary" type="submit">{{ 'Ahorrar' }}</button>
            </div>
        </div>
    </div>
</div>
