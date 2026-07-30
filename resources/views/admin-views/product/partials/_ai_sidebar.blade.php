<!-- AI Assistant Modal -->
<div class="modal fade p-0" id="aiAssistantModal" tabindex="-1" aria-labelledby="aiAssistantModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-slideInRight modal-dialog-scrollable modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title d-flex align-items-center gap-2 aiAssistantModalLabel" id="aiAssistantModalLabel">
                    <span class="square-div">
                        <span class="ai-btn-animation">
                            <span class="gradientCirc"></span>
                        </span>
                        <img class="position-relative z-1" width="15" height="12" src="{{ asset('assets/admin/img/svg/blink-right.svg') }}" alt="">
                    </span>
                    <span id="modalTitle">{{ 'Asistente de IA' }}</span>
                </h5>
                <button type="button" class="close" data-dismiss="modal" ria-label="{{ 'Cerca' }}">
                    <span aria-hidden="true" class="tio-clear"></span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Main AI Assistant Content -->
                <div id="mainAiContent" class="ai-modal-content" style="display: none;">
                    <div class="text-center mb-4">
                        <div class="ai-avatar mb-3">
                            <div class="avatar-circle mx-auto">
                                <span class="ai-btn-animation">
                                    <span class="gradientCirc"></span>
                                </span>
                                <img class="position-relative z-1" width="40" height="34" src="{{ asset('assets/admin/img/svg/blink-right.svg') }}" alt="">
                            </div>
                        </div>

                        <div class="ai-greeting mb-5">
                            <h4 class="text-title">{{ 'Hola' }},</h4>
                            <h2 class="mb-2">{{ 'Estoy aquí para ayudarte' }}</h2>
                            <p class="text-muted">
                                {{ 'Soy tu asistente personal de IA para esta larga tarea. Sonríe. Simplemente seleccione a continuación cómo me da instrucciones para obtener los datos AI de sus artículos.' }}
                            </p>
                        </div>

                        <div class="ai-actions d-grid gap-3">
                            <button type="button" class="btn btn-outline-secondary bg-transparent btn-block d-flex gap-2 mb-3 ai-action-btn"
                                data-action="upload">
                                <img width="18" height="18" src="{{ asset('assets/admin/img/svg/picture.svg') }}" alt="">
                                <span class="text-title">{{ 'Subir imagen' }}</span>
                            </button>
                            <button type="button" class="btn btn-outline-secondary bg-transparent btn-block d-flex gap-2 ai-action-btn"
                                data-action="title">
                                <img width="18" height="18" src="{{ asset('assets/admin/img/svg/text-generate.svg') }}" alt="">
                                <span class="text-title">{{ 'Generar nombre de artículo' }}</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div id="uploadImageContent" class="ai-modal-content" style="display: none;">
                    <div class="mt-10">
                        <div class="mb-4">
                            <h5 class="mb-3 fs-16 font-bold">
                                {{ 'dar el nombre del producto o subir la imagen' }}
                            </h5>
                            <p class="mb-3">{{ 'proporcione el nombre o la imagen adecuados del producto para generar datos completos para su producto' }}</p>
                            <ul class="mb-5 pl-4">
                                <li>{{ 'Intente utilizar una imagen limpia y evite la desenfoque.' }}</li>
                                <li>{{ 'utilizar tan cerca como la imagen de su producto' }}</li>
                            </ul>
                        </div>
                        <div class="text-center mb-4">
                            <label class="upload-zone w-100 mx-auto" id="chooseImageBtn">
                                <input type="file" id="aiImageUpload" class="image-compressor"  hidden class="d-none" accept="image/*">
                                <input type="file" id="aiImageUploadOriginal" hidden accept="image/*">
                                <div class="text-box mx-auto">
                                    <div class="w-100 d-flex flex-column gap-2 justify-content-center align-items-center py-4">
                                        <img width="40" height="40" src="{{ asset('assets/admin/img/svg/image-upload.svg') }}" alt="">
                                        <div class="d-flex gap-2 align-items-center justify-content-center fs-14">
                                            <span class="text-dark">{{ 'arrastra y suelta tu imagen' }}</span>
                                            <span class="text-lowercase">{{ 'o' }}</span>
                                            <span type="button" class="text-primary font-semibold fs-12 text-underline">
                                                <i class="fi fi-rr-cloud-upload-alt"></i>
                                                {{ 'Explorar imagen' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                 <div id="imagePreview" class="mx-auto position-relative" style="display: none;">
                                     <img id="previewImg" src="" alt="{{ 'Avance' }}"
                                         class="upload-zone_img" style="max-height: 200px;">
                                        <div class="d-flex justify-content-center gap-2 flex-wrap">
                                            <button type="button" class="btn btn-danger p-0 square-div z-2 remove_image_btn" id="removeImageBtn" data-toggle="tooltip" title="{{ 'Quitar imagen' }}">
                                                <i class="tio-clear"></i>
                                            </button>
                                        </div>
                                    </div>
                                </label>
                                <div class="mt-4 text-center analyzeImageBtn_wrapper">
                                    <button type="button" class="btn btn-primary mb-3 d-flex align-items-center gap-2 opacity-1 border-0 mx-auto"
                                        id="analyzeImageBtn" data-url="{{ route('admin.product.analyze-image-auto-fill') }}"
                                        data-lang="{{ \App\CentralLogics\Helpers::system_default_language() }}">
                                        <span class="ai-btn-animation d-none">
                                            <span class="gradientRect"></span>
                                        </span>
                                        <span class="position-relative z-1 d-flex gap-2 align-items-center">
                                            <span
                                                class="d-flex align-items-center btn-text">{{ 'Generar descripción del artículo' }}</span>
                                                <img width="17" height="15" src="{{ asset('assets/admin/img/svg/blink-left.svg') }}" alt="">
                                        </span>
                                    </button>
                                </div>
                        </div>

                        {{-- <div class="mt-3">
                            <button type="button" class="btn btn-outline-secondary" id="backToMainBtn">
                                <i class="fi fi-rr-angle-double-small-left"></i>
                                {{ 'Atrás' }}
                            </button>
                        </div> --}}
                    </div>
                </div>

                <div id="giveTitleContent" class="ai-modal-content" style="display: none;">
                    <div class="mb-4">
                        <div class="giveTitleContent_text">
                            <h5 class="mb-3 fs-16 font-bold">
                                {{ '¡excelente!' }}
                                <br>
                                {{ 'Ahora dime qué producto quieres crear. simplemente escríbalo simplemente, como:' }}
                            </h5>
                            <ul class="mb-3 pl-4">
                                <li>{{ 'Necesito detalles del producto para zapatos converse de hombre.' }}</li>
                                <li>{{ 'quiero agregar una camiseta de hombre' }}</li>
                                <li>{{ 'Quiero crear un producto para jeans de mujer.' }}</li>
                            </ul>
                            <p class="mb-4">{{ '¡Siéntete libre de describirlo a tu manera!' }}</p>
                        </div>
                        <div class="generate-text-input-group">
                            <input type="text" class="form-control" id="productKeywords"
                                placeholder="{{ 'Cuéntame sobre tu artículo' }}" data-role="tagsinput">
                                <button type="button" class="btn btn-primary border-0"
                                    id="generateTitleBtn" data-route="{{ route('admin.product.generate-title-suggestions') }}"
                                    data-lang="en">
                                    <span class="ai-loader-animation z-2 d-none">
                                        <span class="loader-circle"></span>
                                        <img width="15" height="15" class="position-relative h-100" src="{{ asset('assets/admin/img/svg/blink-left.svg') }}" alt="">
                                    </span>
                                    <span class="position-rtelative z-1"><i class="tio-arrow-forward"></i></span>
                                </button>
                        </div>

                        {{-- <div class="mb-3">
                            <label for="productKeywords" class="form-label">{{ 'Palabras clave del producto' }}</label>
                            <input type="text" class="form-control" id="productKeywords"
                                placeholder="{{ 'Introduzca palabras clave' }}" data-role="tagsinput">
                            <small
                                class="form-text text-muted">{{ 'Separe las palabras clave con comas' }}</small>
                        </div>

                        <button type="button" class="btn btn-primary mb-3 d-flex align-items-center w-100"
                            id="generateTitleBtn" data-route="{{ route('admin.product.generate-title-suggestions') }}"
                            data-lang="en">
                            <span class="spinner-border spinner-border-sm me-2 d-none" role="status"
                                aria-hidden="true"></span>
                            <i class="tio-magic-wand"></i>
                            <span class="d-flex align-items-center">{{ 'Generar título' }}</span>
                        </button> --}}

                    </div>

                    <div id="generatedTitles" style="display: none;">
                        <div class="text-primary generate_btn_wrapper show_generating_text d-none mb-3">
                            <div class="btn-svg-wrapper">
                                <img width="18" height="18" class="" src="{{ asset('assets/admin/img/svg/blink-right-small.svg') }}"
                                alt="">
                            </div>
                            <span class="ai-text-animation ai-text-animation-visible">
                                {{ 'Un momento' }}
                            </span>
                        </div>
                        <h4 class="mb-2 titlesList_title d-none">{{ 'Sugerir nombre del artículo' }}</h4>
                        <div id="titlesList" class="list-group">
                            <!-- Generated titles will appear here -->
                        </div>
                    </div>

                    {{-- <div class="mt-3">
                        <button type="button" class="btn btn-outline-secondary" id="backToMainBtn2">
                            <i class="fi fi-rr-angle-double-small-left"></i>
                            {{ 'Atrás' }}
                        </button>
                    </div> --}}
                </div>
            </div>
            {{-- <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="tio-clear"></i>
                    {{ 'Cerca' }}
                </button>
            </div> --}}
        </div>
    </div>
</div>

@if (isset($openai_config) && data_get($openai_config, 'status') == 1)
    <!-- Floating AI Assistant Button -->
    <div class="floating-ai-button">
        <button type="button" class="btn btn-lg rounded-circle shadow-lg" data-toggle="modal"
        data-target="#aiAssistantModal" data-action="main" title="AI Assistant">
            <span class="ai-btn-animation">
                <span class="gradientCirc"></span>
            </span>
            <span class="position-relative z-1 text-white d-flex flex-column gap-1 align-items-center">
                <img width="16" height="17" src="{{ asset('assets/admin/img/svg/hexa-ai.svg') }}" alt="">
                <span class="fs-12 font-semibold">{{ 'Usa IA' }}</span>
            </span>
        </button>
        <div class="ai-tooltip">
            <span>{{ 'Asistente de IA' }}</span>
        </div>
    </div>
@endif
