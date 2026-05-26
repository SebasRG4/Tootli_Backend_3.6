@extends('layouts.admin.app')

@section('title', 'Importador Express de Menú (IA)')

@section('content')
    <div class="content container-fluid">
        <!-- Encabezado -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{ asset('assets/admin/img/items.png') }}" class="w--22" alt="">
                </span>
                <span>Importador Express de Menú con IA</span>
            </h1>
            <p class="text-muted mt-1">Sube la fotografía de un menú físico o carta impresa. Nuestra IA extraerá automáticamente platillos, descripciones, precios, sugerirá categorías y detectará posibles duplicados al instante.</p>
        </div>

        <!-- Fila Principal de Carga -->
        <div class="row g-3" id="setup-section">
            <div class="col-12">
                <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                    <div class="card-body p-4">
                        <form id="menu-upload-form" enctype="multipart/form-data">
                            @csrf
                            <div class="row g-4">
                                <!-- Seleccionar Tienda -->
                                <div class="col-md-6">
                                    <label class="input-label font-weight-bold text-dark"><i class="tio-shop"></i> Seleccionar Restaurante / Tienda</label>
                                    <select name="store_id" id="store_id" class="form-control js-select2-custom" required>
                                        <option value="" selected disabled>Seleccione un restaurante de la lista</option>
                                        @foreach($stores as $store)
                                            <option value="{{ $store->id }}">{{ $store->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Seleccionar Imagen -->
                                <div class="col-md-6">
                                    <label class="input-label font-weight-bold text-dark"><i class="tio-image"></i> Fotografía del Menú o Carta</label>
                                    <input type="file" name="menu_image" id="menu_image" class="d-none" accept="image/jpeg,image/png,image/jpg,image/webp" required>
                                    <div id="dropzone" class="border border-dashed d-flex flex-column align-items-center justify-content-center p-4 text-center cursor-pointer bg-light" style="border-radius: 12px; min-height: 140px; transition: all 0.3s ease;">
                                        <i class="tio-cloud-upload-outlined text-primary" style="font-size: 40px;"></i>
                                        <span class="mt-2 text-dark font-weight-medium" id="upload-text">Arrastra y suelta tu imagen aquí o haz clic para buscar</span>
                                        <small class="text-muted">Soporta JPG, PNG, WEBP (Máximo 10MB)</small>
                                    </div>
                                    <div id="image-preview-container" class="mt-3 d-none text-center">
                                        <img id="image-preview" src="#" alt="Previsualización" class="img-thumbnail" style="max-height: 250px; border-radius: 8px;">
                                        <div class="mt-2">
                                            <button type="button" class="btn btn-sm btn-outline-danger" id="remove-image-btn"><i class="tio-delete"></i> Quitar imagen</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                <button type="submit" class="btn btn--primary btn-lg" id="extract-btn"><i class="tio-wand"></i> Extraer Menú con IA</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pantalla de Procesamiento e IA -->
        <div id="loading-overlay" class="d-none text-center my-5 p-5">
            <div class="spinner-border text-primary" role="status" style="width: 4rem; height: 4rem;">
                <span class="sr-only">Procesando...</span>
            </div>
            <h3 class="mt-4 text-primary font-weight-bold">🤖 Extrayendo menú con Inteligencia Artificial...</h3>
            <p class="text-muted max-w-500 mx-auto">Nuestra visión por computadora está leyendo la imagen, extrayendo platillos, identificando precios y descripciones, sugiriendo categorías y buscando duplicados. Esto puede tomar de 10 a 20 segundos...</p>
            <div class="progress max-w-500 mx-auto mt-3" style="height: 10px; border-radius: 10px;">
                <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 100%; border-radius: 10px;"></div>
            </div>
        </div>

        <!-- Sección de Vista Previa y Edición (Review Grid) -->
        <div class="row g-3 d-none mt-4" id="preview-section">
            <div class="col-12">
                <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                    <div class="card-header bg-white border-0 p-4 pb-0 d-flex flex-wrap justify-content-between align-items-center">
                        <div>
                            <h3 class="card-title font-weight-bold text-dark mb-0"><i class="tio-table"></i> Revisar y Validar Platillos Extraídos</h3>
                            <p class="text-muted mb-0">Revisa la información extraída por la IA, edita precios o descripciones sobre la tabla y selecciona las categorías adecuadas antes de realizar la importación final.</p>
                        </div>
                        <div class="d-flex align-items-center gap-2 mt-2 mt-sm-0">
                            <span class="badge badge-success-light p-2 font-weight-bold" id="total-extracted-badge">0 Platillos Extraídos</span>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <form id="import-submit-form">
                            @csrf
                            <input type="hidden" name="store_id" id="submit_store_id">
                            
                            <!-- Sección de Imagen Global Masiva (Opcional) -->
                            <div class="bg-light p-3 mb-4 border d-flex align-items-center justify-content-between flex-wrap gap-3" style="border-radius: 12px; border-color: #e2e8f0 !important;">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="icon-avatar bg-soft-primary p-3" style="border-radius: 10px; color: var(--primary, #0066cc);">
                                        <i class="tio-image" style="font-size: 24px;"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-1 text-dark font-weight-bold" style="font-size: 14px;">Establecer Imagen Masiva (Opcional)</h5>
                                        <p class="text-muted mb-0" style="font-size: 12px;">Sube una sola imagen para aplicarla automáticamente a TODOS los platillos que no tengan una imagen personalizada.</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-3">
                                    <input type="file" id="global_image_input" name="global_image" class="d-none" accept="image/*">
                                    <div id="global-image-preview-container" class="d-none align-items-center gap-2 bg-white p-2 border" style="border-radius: 8px;">
                                        <img id="global-image-preview" src="" class="img-thumbnail mr-2" style="width: 40px; height: 40px; object-fit: cover; border-radius: 6px;">
                                        <button type="button" class="btn btn-xs btn-outline-danger" id="remove-global-image-btn" style="border-radius: 6px; padding: 4px 8px;">
                                            <i class="tio-delete"></i> Quitar
                                        </button>
                                    </div>
                                    <button type="button" class="btn btn-outline-primary font-weight-bold" id="upload-global-image-btn" style="border-radius: 8px; font-size: 13px; padding: 8px 16px;">
                                        <i class="tio-upload-to-cloud"></i> Cargar Imagen Masiva
                                    </button>
                                </div>
                            </div>

                            <div class="table-responsive border" style="border-radius: 12px; overflow-x: auto;">
                                <table class="table table-hover table-striped mb-0 text-dark align-middle">
                                    <thead class="bg-light font-weight-bold text-secondary">
                                        <tr>
                                            <th class="text-center" width="50">
                                                <input type="checkbox" id="select-all" checked style="transform: scale(1.2);">
                                            </th>
                                            <th class="text-center" width="80">Imagen</th>
                                            <th width="200">Nombre del Platillo</th>
                                            <th width="240">Descripción / Ingredientes</th>
                                            <th width="100">Precio ($)</th>
                                            <th width="180">Categoría Asignada</th>
                                            <th width="140">Horario Disp.</th>
                                            <th class="text-center" width="130">Variantes</th>
                                            <th class="text-center" width="120">Estado / Alerta</th>
                                        </tr>
                                    </thead>
                                    <tbody id="extracted-items-rows">
                                        <!-- Filas cargadas dinámicamente por JS -->
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-4 flex-wrap gap-3">
                                <button type="button" class="btn btn-outline-secondary" id="back-btn"><i class="tio-back-button"></i> Subir otra foto</button>
                                <button type="submit" class="btn btn-success btn-lg" id="final-import-btn"><i class="tio-save"></i> Importar Platillos Seleccionados</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para Ver Detalles del Platillo Existente -->
        <div class="modal fade" id="matchedItemModal" tabindex="-1" role="dialog" aria-labelledby="matchedItemModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
                    <div class="modal-header bg-light border-0 p-4 pb-0">
                        <h4 class="modal-title font-weight-bold text-dark d-flex align-items-center" id="matchedItemModalLabel">
                            <i class="tio-restaurant text-primary mr-2" style="font-size: 24px;"></i> Detalles del Platillo Existente
                        </h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="font-size: 24px; color: #999; border: none; background: transparent; outline: none;">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-4">
                        <!-- Imagen del Platillo -->
                        <div class="text-center mb-4 d-none" id="modal-item-image-container">
                            <img id="modal-item-image" src="" alt="Platillo" class="img-fluid rounded-lg shadow-sm" style="max-height: 180px; object-fit: cover; width: 100%; border-radius: 12px;">
                        </div>
                        
                        <div class="d-flex flex-column gap-3">
                            <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                                <span class="text-muted font-weight-medium">Nombre:</span>
                                <span class="text-dark font-weight-bold" id="modal-item-name">-</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                                <span class="text-muted font-weight-medium">Categoría:</span>
                                <span class="badge badge-info p-2 font-weight-bold" id="modal-item-category" style="font-size: 12px; border-radius: 6px;">-</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                                <span class="text-muted font-weight-medium">Precio Actual:</span>
                                <span class="text-success font-weight-bold" style="font-size: 18px;" id="modal-item-price">$0.00</span>
                            </div>
                            <div class="d-flex flex-column">
                                <span class="text-muted font-weight-medium mb-1">Descripción / Ingredientes:</span>
                                <p class="text-dark mb-0 bg-light p-3 rounded" style="font-size: 13px; min-height: 50px; border-radius: 8px; border: 1px solid #e7eaf3;" id="modal-item-description">-</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-secondary btn-block" style="border-radius: 8px;" data-dismiss="modal">Cerrar Detalles</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para Edición de Variantes -->
        <div class="modal fade" id="variationsModal" tabindex="-1" role="dialog" aria-labelledby="variationsModalLabel" aria-hidden="true" style="overflow-y: auto;">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
                    <div class="modal-header bg-light border-0 p-4 pb-2">
                        <h4 class="modal-title font-weight-bold text-dark d-flex align-items-center" id="variationsModalLabel">
                            <i class="tio-edit text-primary mr-2" style="font-size: 24px;"></i> Variantes y Opciones del Platillo
                        </h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="font-size: 24px; color: #999; border: none; background: transparent; outline: none;">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-4" style="max-height: 60vh; overflow-y: auto; background-color: #f8fafc;">
                        <p class="text-muted" style="font-size: 13px;">Agrega opciones personalizables como tamaños (ej: Chico/Grande), proteínas, salsas o ingredientes extra para este platillo. Define costos adicionales correspondientes.</p>
                        
                        <div id="variations-container" class="d-flex flex-column gap-3">
                            <!-- Los grupos de variantes se cargarán dinámicamente aquí -->
                        </div>

                        <div class="text-center mt-4">
                            <button type="button" class="btn btn-outline-primary px-4" id="add-group-btn" style="border-radius: 8px; font-weight: 600;">
                                <i class="tio-add"></i> Agregar Grupo de Variantes (Ej. Tamaño, Adicionales)
                            </button>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-secondary" style="border-radius: 8px;" data-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary px-4" id="save-variations-btn" style="border-radius: 8px; font-weight: 600;">Guardar Variantes</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
    <script>
        let categoriesList = [];

        // Inicializar Dropzone interactivo
        const dropzone = document.getElementById('dropzone');
        const fileInput = document.getElementById('menu_image');
        const uploadText = document.getElementById('upload-text');
        const imagePreviewContainer = document.getElementById('image-preview-container');
        const imagePreview = document.getElementById('image-preview');
        const removeImageBtn = document.getElementById('remove-image-btn');

        dropzone.addEventListener('click', () => fileInput.click());

        dropzone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropzone.classList.add('bg-primary-light');
            dropzone.style.borderColor = 'var(--primary)';
        });

        dropzone.addEventListener('dragleave', () => {
            dropzone.classList.remove('bg-primary-light');
            dropzone.style.borderColor = '#ccc';
        });

        dropzone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropzone.classList.remove('bg-primary-light');
            dropzone.style.borderColor = '#ccc';
            
            if (e.dataTransfer.files.length) {
                fileInput.files = e.dataTransfer.files;
                showImagePreview(e.dataTransfer.files[0]);
            }
        });

        fileInput.addEventListener('change', () => {
            if (fileInput.files.length) {
                showImagePreview(fileInput.files[0]);
            }
        });

        removeImageBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            fileInput.value = '';
            imagePreview.src = '#';
            imagePreviewContainer.classList.add('d-none');
            dropzone.classList.remove('d-none');
        });

        function showImagePreview(file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.src = e.target.result;
                imagePreviewContainer.classList.remove('d-none');
                dropzone.classList.add('d-none');
            }
            reader.readAsDataURL(file);
        }

        // Selección de todos los Checkboxes
        $('#select-all').on('change', function() {
            $('.item-import-checkbox').prop('checked', this.checked);
        });

        // Enviar Formulario de Carga / Parsear Imagen
        $('#menu-upload-form').on('submit', function(e) {
            e.preventDefault();

            let storeId = $('#store_id').val();
            if (!storeId) {
                toastr.error('Por favor selecciona un restaurante.');
                return;
            }

            let formData = new FormData(this);

            $('#setup-section').addClass('d-none');
            $('#loading-overlay').removeClass('d-none');

            $.ajax({
                url: '{{ route("admin.item.express-import-parse") }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#loading-overlay').addClass('d-none');
                    if (response.success && response.items.length > 0) {
                        categoriesList = response.categories;
                        renderPreviewGrid(response.items, storeId);
                    } else {
                        toastr.error('No se pudieron extraer platillos de la imagen. Intenta con una foto más nítida.');
                        $('#setup-section').removeClass('d-none');
                    }
                },
                error: function(xhr) {
                    $('#loading-overlay').addClass('d-none');
                    $('#setup-section').removeClass('d-none');
                    let message = xhr.responseJSON?.error || 'Error al procesar la imagen con Inteligencia Artificial.';
                    toastr.error(message);
                }
            });
        });

        // Utility to escape HTML strings safely for data-attributes
        function escapeHtml(text) {
            if (!text) return '';
            return text
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        // Click handler to display the details of similar/duplicate matched items
        $(document).on('click', '.matched-item-trigger', function() {
            let name = $(this).attr('data-name');
            let price = $(this).attr('data-price');
            let desc = $(this).attr('data-desc');
            let image = $(this).attr('data-image');
            let cat = $(this).attr('data-cat');

            $('#modal-item-name').text(name);
            $('#modal-item-price').text(price);
            $('#modal-item-description').text(desc);
            $('#modal-item-category').text(cat);

            if (image && image !== 'null' && image !== '') {
                $('#modal-item-image').attr('src', image);
                $('#modal-item-image-container').removeClass('d-none');
            } else {
                $('#modal-item-image-container').addClass('d-none');
            }

            $('#matchedItemModal').modal('show');
        });

        // Global variables for variations editor
        let editingItemIndex = null;
        let currentVariations = [];

        // Click handler to open variations editor modal
        $(document).on('click', '.edit-variations-btn', function() {
            editingItemIndex = $(this).attr('data-index');
            let rawVal = $(`#var-input-${editingItemIndex}`).val();
            currentVariations = rawVal ? JSON.parse(rawVal) : [];
            
            renderVariationsModal();
            $('#variationsModal').modal('show');
        });

        // Add variation group
        $('#add-group-btn').on('click', function() {
            let groupIndex = $('.variation-group-card').length;
            let groupHtml = buildGroupHtml({
                name: '',
                type: 'single',
                required: 'off',
                values: []
            }, groupIndex);
            
            $('#variations-container').append(groupHtml);
            
            // Add a default option row inside the new group
            addOptionRow(groupIndex);
        });

        // Add option row to a group
        $(document).on('click', '.add-option-btn', function() {
            let groupIndex = $(this).attr('data-group-index');
            addOptionRow(groupIndex);
        });

        // Delete group
        $(document).on('click', '.delete-group-btn', function() {
            $(this).closest('.variation-group-card').remove();
        });

        // Delete option row
        $(document).on('click', '.delete-option-btn', function() {
            $(this).closest('.variation-value-row').remove();
        });

        function addOptionRow(groupIndex, value = {label: '', optionPrice: 0}) {
            let valueIndex = $(`.value-row-group-${groupIndex}`).length;
            let rowHtml = `
                <div class="row g-2 align-items-center mb-2 variation-value-row value-row-group-${groupIndex}">
                    <div class="col-6">
                        <input type="text" class="form-control express-input-field value-label-input" value="${escapeHtml(value.label)}" placeholder="Ej. Chico, Extra Queso" style="font-size: 13px; padding: 6px 10px !important;" required>
                    </div>
                    <div class="col-4">
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light" style="border: 1px solid #cbd5e1; border-radius: 6px 0 0 6px;">$</span>
                            </div>
                            <input type="number" class="form-control express-input-field value-price-input" value="${value.optionPrice}" step="0.01" min="0" placeholder="0.00" style="font-size: 13px; padding: 6px 10px !important; border-radius: 0 6px 6px 0 !important;" required>
                        </div>
                    </div>
                    <div class="col-2 text-right">
                        <button type="button" class="btn btn-outline-danger btn-sm delete-option-btn" style="border-radius: 6px; padding: 4px 8px;">
                            <i class="tio-delete"></i>
                        </button>
                    </div>
                </div>
            `;
            $(`#options-list-${groupIndex}`).append(rowHtml);
        }

        function buildGroupHtml(group, groupIndex) {
            let isSingle = group.type === 'single';
            let isRequired = group.required === 'on';
            
            return `
                <div class="card border border-light shadow-sm mb-3 variation-group-card" style="border-radius: 12px; overflow: hidden;" id="group-card-${groupIndex}">
                    <div class="card-header bg-white py-3 px-4 border-bottom-0 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2 flex-grow-1 mr-3">
                            <span class="badge badge-soft-primary p-2" style="border-radius: 6px;"><i class="tio-settings-outlined"></i></span>
                            <input type="text" class="form-control express-input-field font-weight-bold group-name-input" value="${escapeHtml(group.name)}" placeholder="Nombre del Grupo (ej. Elige tu proteína, Tamaño)" style="font-size: 14px;" required>
                        </div>
                        <button type="button" class="btn btn-outline-danger btn-sm delete-group-btn" style="border-radius: 6px; padding: 6px 10px;">
                            <i class="tio-delete-outlined"></i> Eliminar
                        </button>
                    </div>
                    <div class="card-body py-2 px-4">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="input-label font-weight-bold mb-1 text-dark" style="font-size: 12px;">Tipo de Selección</label>
                                <select class="form-control express-input-field group-type-select" style="font-size: 13px; padding: 6px 10px !important;">
                                    <option value="single" ${isSingle ? 'selected' : ''}>Selección Única (Radio Button)</option>
                                    <option value="multi" ${!isSingle ? 'selected' : ''}>Selección Múltiple (Checkbox)</option>
                                </select>
                            </div>
                            <div class="col-md-6 d-flex align-items-end pb-2">
                                <div class="form-check">
                                    <input class="form-check-input group-required-checkbox" type="checkbox" id="req-check-${groupIndex}" ${isRequired ? 'checked' : ''} style="transform: scale(1.1); margin-top: 2px;">
                                    <label class="form-check-label font-weight-bold ml-1 text-dark" for="req-check-${groupIndex}" style="font-size: 13px;">
                                        ¿Es obligatorio seleccionar uno?
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="border-top pt-3">
                            <h6 class="font-weight-bold text-dark mb-2" style="font-size: 13px;"><i class="tio-menu-hamburger"></i> Opciones de Selección</h6>
                            <div id="options-list-${groupIndex}">
                                <!-- Las opciones se agregarán aquí -->
                            </div>
                            <div class="text-left mt-2 mb-3">
                                <button type="button" class="btn btn-outline-success btn-xs add-option-btn" style="border-radius: 6px;" data-group-index="${groupIndex}">
                                    <i class="tio-add"></i> Agregar Opción (Ej. Grande)
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        // Render variations inside the modal
        function renderVariationsModal() {
            let container = $('#variations-container');
            container.empty();
            
            if (currentVariations.length === 0) {
                // If empty, add a default Tamaño variation group to help the user get started
                let groupHtml = buildGroupHtml({
                    name: 'Tamaño',
                    type: 'single',
                    required: 'on',
                    values: []
                }, 0);
                container.append(groupHtml);
                addOptionRow(0, {label: 'Chico', optionPrice: 0});
                addOptionRow(0, {label: 'Grande', optionPrice: 0});
                return;
            }
            
            currentVariations.forEach((group, groupIndex) => {
                let groupHtml = buildGroupHtml(group, groupIndex);
                container.append(groupHtml);
                
                group.values.forEach(val => {
                    addOptionRow(groupIndex, val);
                });
            });
        }

        // Save variations inside the modal
        $('#save-variations-btn').on('click', function() {
            let variations = [];
            let isValid = true;
            
            $('.variation-group-card').each(function() {
                let groupCard = $(this);
                let name = groupCard.find('.group-name-input').val().trim();
                if (!name) {
                    toastr.error('Por favor completa el nombre de todos los grupos de variantes.');
                    isValid = false;
                    return false;
                }
                
                let type = groupCard.find('.group-type-select').val();
                let required = groupCard.find('.group-required-checkbox').is(':checked') ? 'on' : 'off';
                
                let values = [];
                groupCard.find('.variation-value-row').each(function() {
                    let valRow = $(this);
                    let label = valRow.find('.value-label-input').val().trim();
                    let price = parseFloat(valRow.find('.value-price-input').val());
                    if (isNaN(price)) price = 0;
                    
                    if (label) {
                        values.push({
                            label: label,
                            optionPrice: price
                        });
                    }
                });
                
                if (values.length === 0) {
                    toastr.error(`Por favor agrega al menos una opción para el grupo "${name}".`);
                    isValid = false;
                    return false;
                }
                
                variations.push({
                    name: name,
                    type: type,
                    min: type === 'single' && required === 'on' ? 1 : 0,
                    max: type === 'single' ? 1 : values.length,
                    required: required,
                    values: values
                });
            });
            
            if (!isValid) return;
            
            // Save back to hidden input
            $(`#var-input-${editingItemIndex}`).val(JSON.stringify(variations));
            // Update the count badge
            $(`#var-count-${editingItemIndex}`).html(`<i class="tio-edit"></i> ${variations.length} Variantes`);
            
            $('#variationsModal').modal('hide');
            toastr.success('Variantes guardadas temporalmente.');
        });

        // Renderizar Cuadrícula / Tabla de Edición
        function renderPreviewGrid(items, storeId) {
            $('#submit_store_id').val(storeId);
            $('#total-extracted-badge').text(`${items.length} Platillos Extraídos`);
            
            let tbody = $('#extracted-items-rows');
            tbody.empty();

            items.forEach((item, index) => {
                // Estatus Badge
                let statusBadge = '';
                let isChecked = 'checked';
                
                let escDesc = escapeHtml(item.matched_description || 'Sin descripción');
                let escImage = item.matched_image || '';
                let escCat = escapeHtml(item.matched_category || 'Otros');

                if (item.status === 'duplicate') {
                    statusBadge = `
                        <span class="badge badge-soft-danger p-2 cursor-pointer matched-item-trigger w-100" 
                              style="font-size:11px; border-radius: 6px;"
                              data-name="${escapeHtml(item.matched_name)}"
                              data-price="$${item.matched_price}"
                              data-desc="${escDesc}"
                              data-image="${escImage}"
                              data-cat="${escCat}">
                            <i class="tio-warning"></i> Duplicado
                        </span>
                        <br>
                        <small class="text-danger">Parecido a: <a href="javascript:void(0)" class="font-weight-bold text-underline matched-item-trigger" 
                              data-name="${escapeHtml(item.matched_name)}"
                              data-price="$${item.matched_price}"
                              data-desc="${escDesc}"
                              data-image="${escImage}"
                              data-cat="${escCat}">${item.matched_name}</a></small>
                    `;
                    isChecked = ''; // Desmarcar por seguridad si es duplicado idéntico
                } else if (item.status === 'similar') {
                    statusBadge = `
                        <span class="badge badge-soft-warning p-2 cursor-pointer matched-item-trigger w-100" 
                              style="font-size:11px; border-radius: 6px;"
                              data-name="${escapeHtml(item.matched_name)}"
                              data-price="$${item.matched_price}"
                              data-desc="${escDesc}"
                              data-image="${escImage}"
                              data-cat="${escCat}">
                            <i class="tio-warning-outlined"></i> Nombre Similar
                        </span>
                        <br>
                        <small class="text-warning">Parecido a: <a href="javascript:void(0)" class="font-weight-bold text-underline matched-item-trigger" 
                              data-name="${escapeHtml(item.matched_name)}"
                              data-price="$${item.matched_price}"
                              data-desc="${escDesc}"
                              data-image="${escImage}"
                              data-cat="${escCat}">${item.matched_name}</a></small>
                    `;
                } else {
                    statusBadge = `<span class="badge badge-soft-success p-2 w-100" style="font-size:11px; border-radius: 6px;"><i class="tio-checkmark-circle"></i> Nuevo</span>`;
                }

                // Armar selector de Categorías
                let categoryOptions = `<option value="" disabled>Seleccione Categoría</option>`;
                let matchedCategory = false;

                categoriesList.forEach(cat => {
                    let selected = (cat.id == item.category_id) ? 'selected' : '';
                    if (selected) matchedCategory = true;
                    categoryOptions += `<option value="${cat.id}" ${selected}>${cat.name}</option>`;
                });

                // Si no hizo match con ninguna, sugerir "Crear Categoría" por defecto con el nombre sugerido por la IA
                categoryOptions += `<option value="new" ${!matchedCategory ? 'selected' : ''}>+ Crear Nueva Categoría: "${item.suggested_category}"</option>`;

                // Slice times to HH:MM format for HTML input
                let timeStarts = (item.available_time_starts || '00:00:00').slice(0, 5);
                let timeEnds = (item.available_time_ends || '23:59:59').slice(0, 5);
                let varsCount = (item.variations || []).length;

                let row = `
                    <tr class="align-middle">
                        <td class="text-center">
                            <input type="checkbox" name="items[${index}][import]" value="1" ${isChecked} class="item-import-checkbox" style="transform: scale(1.2);">
                        </td>
                        <td class="text-center">
                            <div class="item-image-wrapper position-relative mx-auto" style="width: 50px; height: 50px;">
                                <input type="file" name="items[${index}][image]" class="item-image-input d-none" accept="image/*" data-index="${index}">
                                <div class="item-image-preview-box border cursor-pointer d-flex align-items-center justify-content-center bg-white" 
                                     style="width: 50px; height: 50px; border-radius: 8px; overflow: hidden; transition: all 0.2s ease;" 
                                     id="image-box-${index}" 
                                     data-index="${index}">
                                    <i class="tio-camera text-muted" style="font-size: 18px;" id="camera-icon-${index}"></i>
                                    <img src="" class="d-none w-100 h-100" style="object-fit: cover;" id="image-preview-${index}">
                                </div>
                                <button type="button" class="btn btn-danger btn-xs delete-item-image-btn d-none position-absolute" 
                                        style="border-radius: 50%; width: 16px; height: 16px; padding: 0; font-size: 10px; top: -5px; right: -5px; line-height: 1; display: flex; align-items: center; justify-content: center; z-index: 10;" 
                                        id="delete-img-btn-${index}" 
                                        data-index="${index}">&times;</button>
                            </div>
                        </td>
                        <td>
                            <input type="text" name="items[${index}][name]" value="${item.name}" class="form-control font-weight-bold express-input-field" required>
                        </td>
                        <td>
                            <textarea name="items[${index}][description]" class="form-control express-input-field express-textarea-field" rows="2" style="resize: none;" required>${item.description}</textarea>
                        </td>
                        <td>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light border-right-0" style="border-radius: 8px 0 0 8px; border: 1px solid #e7eaf3;">$</span>
                                </div>
                                <input type="number" name="items[${index}][price]" value="${item.price}" step="0.01" min="0" class="form-control express-input-field" required style="min-width: 80px; border-radius: 0 8px 8px 0 !important;">
                            </div>
                        </td>
                        <td>
                            <select name="items[${index}][category_id]" class="form-control category-select express-input-field js-select2-custom" data-index="${index}">
                                ${categoryOptions}
                            </select>
                            <input type="text" name="items[${index}][new_category_name]" value="${item.suggested_category}" class="form-control mt-2 new-category-input express-input-field ${matchedCategory ? 'd-none' : ''}" placeholder="Nombre de categoría nueva" style="font-size: 13px;">
                        </td>
                        <td>
                            <div class="d-flex flex-column gap-1">
                                <div class="d-flex align-items-center gap-1">
                                    <small class="text-muted" style="min-width: 40px; font-size: 10px;">Desde:</small>
                                    <input type="time" name="items[${index}][available_time_starts]" value="${timeStarts}" class="form-control express-input-field p-1" style="font-size: 11px; height: auto;">
                                </div>
                                <div class="d-flex align-items-center gap-1">
                                    <small class="text-muted" style="min-width: 40px; font-size: 10px;">Hasta:</small>
                                    <input type="time" name="items[${index}][available_time_ends]" value="${timeEnds}" class="form-control express-input-field p-1" style="font-size: 11px; height: auto;">
                                </div>
                            </div>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-outline-info btn-sm edit-variations-btn px-2 w-100" style="border-radius: 8px; font-size: 12px; font-weight: 600;" data-index="${index}">
                                <i class="tio-edit"></i> <span id="var-count-${index}">${varsCount} Variantes</span>
                            </button>
                            <input type="hidden" name="items[${index}][variations]" id="var-input-${index}" value="${escapeHtml(JSON.stringify(item.variations || []))}">
                        </td>
                        <td class="text-center font-weight-medium">
                            ${statusBadge}
                        </td>
                    </tr>
                `;
                tbody.append(row);
            });

            // Escuchar cambios en los selectores de categorías para mostrar/ocultar input de crear nueva
            $('.category-select').on('change', function() {
                let index = $(this).data('index');
                let newCategoryInput = $(`input[name="items[${index}][new_category_name]"]`);
                if ($(this).val() === 'new') {
                    newCategoryInput.removeClass('d-none');
                } else {
                    newCategoryInput.addClass('d-none');
                }
            });

            $('#preview-section').removeClass('d-none');
        }

        // Botón "Subir otra foto" / Regresar
        $('#back-btn').on('click', function() {
            $('#preview-section').addClass('d-none');
            $('#setup-section').removeClass('d-none');
        });

        // --- MANEJO DE IMÁGENES INDIVIDUALES Y MASIVAS ---

        // Abrir selector de archivos individual
        $(document).on('click', '.item-image-preview-box', function() {
            let index = $(this).data('index');
            $(`.item-image-input[data-index="${index}"]`).click();
        });

        // Mostrar previsualización local al cargar imagen individual
        $(document).on('change', '.item-image-input', function() {
            let index = $(this).data('index');
            if (this.files && this.files[0]) {
                let reader = new FileReader();
                reader.onload = function(e) {
                    $(`#image-preview-${index}`).attr('src', e.target.result).removeClass('d-none');
                    $(`#camera-icon-${index}`).addClass('d-none');
                    $(`#delete-img-btn-${index}`).removeClass('d-none');
                }
                reader.readAsDataURL(this.files[0]);
            }
        });

        // Quitar imagen individual (restaura la global si existe)
        $(document).on('click', '.delete-item-image-btn', function(e) {
            e.stopPropagation();
            let index = $(this).data('index');
            $(`.item-image-input[data-index="${index}"]`).val('');
            
            let globalInput = document.getElementById('global_image_input');
            if (globalInput && globalInput.files && globalInput.files[0]) {
                let globalReader = new FileReader();
                globalReader.onload = function(ev) {
                    $(`#image-preview-${index}`).attr('src', ev.target.result).removeClass('d-none');
                    $(`#camera-icon-${index}`).addClass('d-none');
                    $(`#delete-img-btn-${index}`).removeClass('d-none');
                }
                globalReader.readAsDataURL(globalInput.files[0]);
            } else {
                $(`#image-preview-${index}`).attr('src', '').addClass('d-none');
                $(`#camera-icon-${index}`).removeClass('d-none');
                $(`#delete-img-btn-${index}`).addClass('d-none');
            }
        });

        // Abrir selector de archivos global
        $('#upload-global-image-btn').on('click', function() {
            $('#global_image_input').click();
        });

        // Mostrar previsualización global y replicar a todos los platillos sin imagen personalizada
        $('#global_image_input').on('change', function() {
            if (this.files && this.files[0]) {
                let reader = new FileReader();
                reader.onload = function(e) {
                    $('#global-image-preview').attr('src', e.target.result);
                    $('#global-image-preview-container').removeClass('d-none').addClass('d-flex');
                    $('#upload-global-image-btn').addClass('d-none');

                    $('.item-image-input').each(function() {
                        let index = $(this).data('index');
                        if (!this.files || this.files.length === 0) {
                            $(`#image-preview-${index}`).attr('src', e.target.result).removeClass('d-none');
                            $(`#camera-icon-${index}`).addClass('d-none');
                            $(`#delete-img-btn-${index}`).removeClass('d-none');
                        }
                    });
                }
                reader.readAsDataURL(this.files[0]);
            }
        });

        // Quitar imagen global
        $('#remove-global-image-btn').on('click', function() {
            $('#global_image_input').val('');
            $('#global-image-preview-container').addClass('d-none').removeClass('d-flex');
            $('#upload-global-image-btn').removeClass('d-none');

            $('.item-image-input').each(function() {
                let index = $(this).data('index');
                if (!this.files || this.files.length === 0) {
                    $(`#image-preview-${index}`).attr('src', '').addClass('d-none');
                    $(`#camera-icon-${index}`).removeClass('d-none');
                    $(`#delete-img-btn-${index}`).addClass('d-none');
                }
            });
        });

        // Enviar Formulario de Importación Final (Multipart/FormData)
        $('#import-submit-form').on('submit', function(e) {
            e.preventDefault();

            // Verificar si hay al menos un platillo seleccionado
            let anyChecked = $('.item-import-checkbox:checked').length > 0;
            if (!anyChecked) {
                toastr.error('Por favor selecciona al menos un platillo para importar.');
                return;
            }

            $('#final-import-btn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status"></span> Guardando platillos...');

            // Usar FormData para enviar archivos subidos (individuales y global)
            let formData = new FormData(this);

            $.ajax({
                url: '{{ route("admin.item.express-import-save") }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        setTimeout(() => {
                            window.location.href = '{{ route("admin.item.list") }}';
                        }, 1500);
                    }
                },
                error: function(xhr) {
                    $('#final-import-btn').prop('disabled', false).html('<i class="tio-save"></i> Importar Platillos Seleccionados');
                    let message = xhr.responseJSON?.error || 'Error al guardar los platillos en la base de datos.';
                    toastr.error(message);
                }
            });
        });
    </script>
@endpush

@push('css_or_js')
    <style>
        .cursor-pointer {
            cursor: pointer;
        }
        .gap-2 {
            gap: 8px;
        }
        .gap-3 {
            gap: 12px;
        }
        #dropzone:hover {
            background-color: #f1f8ff !important;
            border-color: var(--primary) !important;
        }
        .bg-primary-light {
            background-color: #e6f2ff !important;
        }
        .max-w-500 {
            max-width: 500px;
        }
        .badge-success-light {
            background-color: #d4edda;
            color: #155724;
            border-radius: 8px;
        }
        
        /* Premium Table Grid & Spacing */
        .table-responsive {
            background: transparent !important;
            border: none !important;
            overflow-x: auto !important;
        }
        .table {
            border-collapse: separate !important;
            border-spacing: 0 12px !important;
            margin-top: -12px;
        }
        .table thead th {
            border: none !important;
            padding: 8px 16px !important;
            background-color: transparent !important;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #64748b !important;
            font-weight: 700;
        }
        .table tbody tr {
            transition: all 0.2s ease;
        }
        .table tbody tr td {
            border-top: 1px solid #e2e8f0 !important;
            border-bottom: 1px solid #e2e8f0 !important;
            padding: 18px 14px !important;
            background-color: #ffffff;
            vertical-align: middle !important;
            box-shadow: none;
        }
        .table tbody tr td:first-child {
            border-left: 1px solid #e2e8f0 !important;
            border-top-left-radius: 12px !important;
            border-bottom-left-radius: 12px !important;
        }
        .table tbody tr td:last-child {
            border-right: 1px solid #e2e8f0 !important;
            border-top-right-radius: 12px !important;
            border-bottom-right-radius: 12px !important;
        }
        .table tbody tr:hover td {
            background-color: #f8fafc !important;
            border-color: #cbd5e1 !important;
        }
        .table tbody tr:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(148, 163, 184, 0.08);
        }

        /* Modern Premium Inputs & Selects */
        .express-input-field {
            border: 1px solid #cbd5e1 !important;
            border-radius: 8px !important;
            font-size: 14px !important;
            padding: 10px 12px !important;
            background-color: #f8fafc !important;
            color: #1e293b !important;
            transition: all 0.2s ease !important;
            box-shadow: none !important;
        }
        .express-input-field:focus {
            border-color: var(--primary, #0066cc) !important;
            box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.1) !important;
            background-color: #ffffff !important;
            outline: none !important;
        }
        .express-textarea-field {
            line-height: 1.5 !important;
            padding: 8px 12px !important;
            height: auto !important;
        }
        
        /* Soft Modern Badges */
        .badge-soft-success {
            background-color: rgba(40, 167, 69, 0.08) !important;
            color: #28a745 !important;
            border: 1px solid rgba(40, 167, 69, 0.15) !important;
        }
        .badge-soft-warning {
            background-color: rgba(255, 193, 7, 0.08) !important;
            color: #d39e00 !important;
            border: 1px solid rgba(255, 193, 7, 0.15) !important;
        }
        .badge-soft-danger {
            background-color: rgba(220, 53, 69, 0.08) !important;
            color: #dc3545 !important;
            border: 1px solid rgba(220, 53, 69, 0.15) !important;
        }
        
        /* Underline matched names */
        .text-underline {
            text-decoration: underline !important;
            color: inherit !important;
        }
        .text-underline:hover {
            color: #0056b3 !important;
        }

        /* Dynamic Item Image Preview */
        .item-image-preview-box {
            border: 1px dashed #cbd5e1 !important;
            background-color: #f8fafc !important;
            border-radius: 10px !important;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }
        .item-image-preview-box:hover {
            border-color: var(--primary, #0066cc) !important;
            background-color: #f1f8ff !important;
        }
        .item-image-preview-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 9px;
        }
        .bg-soft-primary {
            background-color: rgba(0, 102, 204, 0.08) !important;
        }
    </style>
@endpush
