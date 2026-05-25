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
                            <div class="table-responsive border" style="border-radius: 12px; overflow: hidden;">
                                <table class="table table-hover table-striped mb-0 text-dark align-middle">
                                    <thead class="bg-light font-weight-bold text-secondary">
                                        <tr>
                                            <th class="text-center" width="50">
                                                <input type="checkbox" id="select-all" checked style="transform: scale(1.2);">
                                            </th>
                                            <th width="240">Nombre del Platillo</th>
                                            <th width="320">Descripción / Ingredientes</th>
                                            <th width="120">Precio ($)</th>
                                            <th width="240">Categoría Asignada</th>
                                            <th class="text-center" width="160">Estado / Alerta</th>
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
                if (item.status === 'duplicate') {
                    statusBadge = `<span class="badge badge-danger p-2" style="font-size:11px;"><i class="tio-warning"></i> Duplicado</span><br><small class="text-danger">Ya existe este nombre</small>`;
                    isChecked = ''; // Desmarcar por seguridad si es duplicado idéntico
                } else if (item.status === 'similar') {
                    statusBadge = `<span class="badge badge-warning p-2" style="font-size:11px;"><i class="tio-warning-outlined"></i> Nombre Similar</span><br><small class="text-warning">Parecido a: <b>${item.matched_name}</b></small>`;
                } else {
                    statusBadge = `<span class="badge badge-success p-2" style="font-size:11px;"><i class="tio-checkmark-circle"></i> Nuevo</span>`;
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

                let row = `
                    <tr class="align-middle">
                        <td class="text-center">
                            <input type="checkbox" name="items[${index}][import]" value="1" ${isChecked} class="item-import-checkbox" style="transform: scale(1.2);">
                        </td>
                        <td>
                            <input type="text" name="items[${index}][name]" value="${item.name}" class="form-control font-weight-bold" required>
                        </td>
                        <td>
                            <textarea name="items[${index}][description]" class="form-control" rows="2" style="resize: none;" required>${item.description}</textarea>
                        </td>
                        <td>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="number" name="items[${index}][price]" value="${item.price}" step="0.01" min="0" class="form-control" required style="min-width: 80px;">
                            </div>
                        </td>
                        <td>
                            <select name="items[${index}][category_id]" class="form-control category-select js-select2-custom" data-index="${index}">
                                ${categoryOptions}
                            </select>
                            <input type="text" name="items[${index}][new_category_name]" value="${item.suggested_category}" class="form-control mt-2 new-category-input ${matchedCategory ? 'd-none' : ''}" placeholder="Nombre de categoría nueva" style="font-size: 13px;">
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

        // Enviar Formulario de Importación Final
        $('#import-submit-form').on('submit', function(e) {
            e.preventDefault();

            // Verificar si hay al menos un platillo seleccionado
            let anyChecked = $('.item-import-checkbox:checked').length > 0;
            if (!anyChecked) {
                toastr.error('Por favor selecciona al menos un platillo para importar.');
                return;
            }

            $('#final-import-btn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status"></span> Guardando platillos...');

            $.ajax({
                url: '{{ route("admin.item.express-import-save") }}',
                type: 'POST',
                data: $(this).serialize(),
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
    </style>
@endpush
