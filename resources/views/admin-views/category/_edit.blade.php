<form action="{{ route('admin.category.update', [$category['id']]) }}" method="post" enctype="multipart/form-data"
      class="d-flex flex-column h-100">
    @method('post')
    @csrf
    <input type="hidden" name="parent_id" value="{{ $category->parent_id }}">
    <div>
        <div class="custom-offcanvas-header bg--secondary d-flex justify-content-between align-items-center px-3 py-3">
            <h3 class="mb-0">{{ $category->position == 0 ? 'Editar categoría' : 'Editar subcategoría' }}</h2>
                <button type="button"
                        class="btn-close w-25px h-25px border rounded-circle d-center bg--secondary text-dark offcanvas-close fz-15px p-0"
                        aria-label="Close">&times;
                </button>
        </div>
        <div class="custom-offcanvas-body p-20">
            <div class="bg--secondary rounded p-20 mb-20">
                <div class="mb-15">
                    <h4 class="mb-0">{{ 'Disponibilidad' }}</h4>
                    <p class="fz-12px">
                        {{ 'Si desactivas este estado esta categoría no estará disponible' }}
                    </p>
                </div>
                <label class="border d-flex align-items-center bg-white-n justify-content-between rounded p-10px px-3">
                    {{ 'Estado' }}
                    <div class="toggle-switch ml-auto justify-content-end toggle-switch-sm" for="status">
                        <input type="checkbox" name="status" value="1" {{ $category['status'] ? 'checked' : '' }}
                        class="toggle-switch-input" id="status">
                        <span class="toggle-switch-label">
                            <span class="toggle-switch-indicator"></span>
                        </span>
                    </div>
                </label>
            </div>


            <div class="bg--secondary rounded p-20 mb-20">

                @if ($language)
                    <ul class="nav nav-tabs mb-4 border-0">
                        <li class="nav-item">
                            <a class="nav-link lang_link1 active" href="#"
                               id="default-link">{{ 'por defecto' }}</a>
                        </li>
                        @foreach ($language as $lang)
                            <li class="nav-item">
                                <a class="nav-link lang_link1" href="#"
                                   id="{{ $lang }}-link">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                            </li>
                        @endforeach
                    </ul>
                @endif
                <div class="row">
                    <div class="col-12">
                        @if ($language)
                            <div class="form-group lang_form1" id="default-form1">
                                <label class="input-label"
                                       for="exampleFormControlInput1">{{ $category->position == 0 ? 'Nombre de categoría' : 'Nombre de subcategoría' }}
                                    ({{ 'por defecto' }})
                                    <span class="form-label-secondary text-danger" data-toggle="tooltip"
                                          data-placement="right"
                                          data-original-title="{{ 'Requerido.' }}"> *
                                    </span>

                                </label>
                                <input type="text" name="name[]" value="{{ $category?->getRawOriginal('name') }}"
                                       class="form-control" placeholder="{{ 'nueva categoría' }}"
                                       maxleng="255">
                            </div>
                            <input type="hidden" name="lang[]" value="default">
                            @foreach ($language as $key => $lang)
                                    <?php
                                    if (count($category['translations'])) {
                                        $translate = [];
                                        foreach ($category['translations'] as $t) {
                                            if ($t->locale == $lang && $t->key == 'name') {
                                                $translate[$lang]['name'] = $t->value;
                                            }
                                        }
                                    }
                                    ?>

                                <div class="form-group d-none lang_form1" id="{{ $lang }}-form1">
                                    <label class="input-label"
                                           for="exampleFormControlInput1">{{ $category->position == 0 ? 'Nombre de categoría' : 'Nombre de subcategoría' }}
                                        ({{ strtoupper($lang) }})
                                    </label>
                                    <input type="text" name="name[]" value="{{ $translate[$lang]['name'] ?? '' }}"
                                           class="form-control"
                                           placeholder="{{ 'Tipo Nombre de categoría' }}" maxlength="191">
                                </div>
                                <input type="hidden" name="lang[]" value="{{ $lang }}">
                            @endforeach
                        @else
                            <div class="form-group">
                                <label class="input-label"
                                       for="exampleFormControlInput1">{{ $category->position == 0 ? 'Nombre de categoría' : 'Nombre de subcategoría' }}</label>
                                <input type="text" name="name" class="form-control"
                                       placeholder="{{ 'nueva categoría' }}"
                                       value="{{ $category?->getRawOriginal('name') }}" maxlength="191">
                            </div>
                            <input type="hidden" name="lang[]" value="default">
                        @endif

                    </div>
                </div>

                @if ($category->position == 1)
                    <div class="form-group mb-3">
                        <label class="input-label" for="parent_id_select">{{ 'categoría principal' }} <span class="text-danger">*</span></label>
                        <select name="parent_id" id="parent_id_select" class="form-control" required>
                            <option value="" disabled {{ $category->parent_id == 0 ? 'selected' : '' }}>{{ 'Seleccionar categoría principal' }}</option>
                            @foreach($mainCategories as $mainCat)
                                <option value="{{ $mainCat->id }}" {{ $category->parent_id == $mainCat->id ? 'selected' : '' }}>
                                    {{ $mainCat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <input type="hidden" name="parent_id" value="0">
                @endif

                <div class="form-group mb-3">
                    <label class="input-label" for="">
                        {{ 'Prioridad' }}
                    </label>
                    <select required name="priority" data-original-title="{{ 'Seleccionar prioridad' }}"
                            class="custom-select">
                        <option {{ $category['priority'] == 0 ? 'selected' : '' }} value="0">
                            {{ 'Normal' }}</option>
                        <option {{ $category['priority'] == 1 ? 'selected' : '' }} value="1">
                            {{ 'Medio' }}</option>
                        <option {{ $category['priority'] == 2 ? 'selected' : '' }} value="2">
                            {{ 'Alto' }}</option>
                    </select>
                </div>

                <div class="form-group mb-3">
                    <label class="toggle-switch toggle-switch-sm d-flex justify-content-between align-items-center" for="is_abastos_edit">
                        <span class="input-label mb-0 text-capitalize">{{ 'Tootli Abastos' }}</span>
                        <input type="checkbox" name="is_abastos" value="1" {{ $category['is_abastos'] ? 'checked' : '' }}
                        class="toggle-switch-input" id="is_abastos_edit">
                        <span class="toggle-switch-label mx-auto">
                            <span class="toggle-switch-indicator"></span>
                        </span>
                    </label>
                </div>

                @if ($category->position == 0 && $categoryWiseTax)
                    <div class="row">

                        <div class="col-12">
                            <span class="mb-2 d-block title-clr fw-normal">{{ 'Seleccionar tasa impositiva' }}</span>
                            <select name="tax_ids[]" required id="" class="form-control js-select2-custom1"
                                    multiple="multiple" placeholder="Type & Select Tax Rate">
                                @foreach ($taxVats as $taxVat)
                                    <option {{ in_array($taxVat->id, $taxVatIds) ? 'selected' : '' }}
                                            value="{{ $taxVat->id }}"> {{ $taxVat->name }}
                                        ({{ $taxVat->tax_rate }}%)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                @endif
                    <div class="col-md-12">
                        <div class="bg--secondary rounded p-20 mb-20">

                            <div class="h-100 d-flex align-items-center flex-column">
                                <label class="mb-4 text-center text-title">
                                    {{ 'imagen' }}
                                    <small
                                        class="color-656566 d-block"> {{ 'Subir imagen' }}</small>
                                </label>
                                <label class="text-center my-auto position-relative d-inline-block">
                                    <img class="img--176 border--dashed rounded viewer_img" id=""
                                         src="{{ $category['image_full_url'] }}"
                                         data-onerror-image="{{ asset('assets/admin/img/upload-img.png') }}"
                                         alt=""/>
                                    <div class="icon-file-group">
                                        <div class="icon-file">
                                            <input type="file" name="image" id=""
                                                   class="custom-file-input custom__FileEg read-url"
                                                   accept=".webp, .jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                            <i class="tio-edit"></i>
                                        </div>
                                    </div>
                                </label>
                            </div>

                        </div>
                    </div>
            </div>

        </div>
    </div>
    <div
        class="align-items-center bg-white bottom-0 d-flex gap-3 justify-content-center mt-auto offcanvas-footer p-3 position-sticky">
        <button type="button"
                class="btn w-100 btn--secondary offcanvas-close h--40px">{{ 'Cancelar' }}</button>
        <button type="submit" class="btn w-100 btn--primary h--40px">{{ 'Actualizar' }}</button>
    </div>
</form>
