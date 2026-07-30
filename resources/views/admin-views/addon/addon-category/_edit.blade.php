


<form action="{{ route('admin.addon.addon-category-update', [$addonCategory['id']]) }}" method="post" class="d-flex flex-column h-100">
    @method('PUT')
    @csrf
    <div>
        <div class="custom-offcanvas-header bg--secondary d-flex justify-content-between align-items-center px-3 py-3">
            <h3 class="mb-0">{{ 'Editar categoría de complemento' }}</h2>
                <button type="button"
                    class="btn-close w-25px h-25px border rounded-circle d-center bg--secondary text-dark offcanvas-close fz-15px p-0"
                    aria-label="Close">&times;</button>
        </div>
        <div class="custom-offcanvas-body p-20">
            <div class="bg--secondary rounded p-20 mb-20">
                <div class="mb-15">
                    <h4 class="mb-0">{{ 'Disponibilidad' }}</h4>
                    <p class="fz-12px">{{ 'Si desactiva este estado, esta categoría de complemento no estará disponible' }}
                    </p>
                </div>
                <label class="border d-flex align-items-center bg-white-n justify-content-between rounded p-10px px-3">
                    {{ 'Estado' }}
                    <div class="toggle-switch ml-auto justify-content-end toggle-switch-sm" for="status">
                        <input type="checkbox" name="status" value="1" {{ $addonCategory['status'] ? 'checked' : '' }}
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
                                    for="exampleFormControlInput1">{{ 'Nombre de categoría' }}
                                    ({{ 'por defecto' }})
                                    <span class="form-label-secondary text-danger" data-toggle="tooltip"
                                        data-placement="right"
                                        data-original-title="{{ 'Requerido.' }}"> *
                                    </span>

                                </label>
                                <input type="text" name="name[]"
                                    value="{{ $addonCategory?->getRawOriginal('name') }}" class="form-control"
                                    placeholder="{{ 'nueva categoría' }}" maxleng="255">
                            </div>
                            <input type="hidden" name="lang[]" value="default">
                            @foreach ($language as $key => $lang)
                                <?php
                                if (count($addonCategory['translations'])) {
                                    $translate = [];
                                    foreach ($addonCategory['translations'] as $t) {
                                        if ($t->locale == $lang && $t->key == 'name') {
                                            $translate[$lang]['name'] = $t->value;
                                        }
                                    }
                                }
                                ?>

                                <div class="form-group d-none lang_form1" id="{{ $lang }}-form1">
                                    <label class="input-label"
                                        for="exampleFormControlInput1">{{ 'Nombre de categoría' }}
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
                                    for="exampleFormControlInput1">{{ 'Nombre de categoría' }}</label>
                                <input type="text" name="name" class="form-control"
                                    placeholder="{{ 'nueva categoría' }}"
                                    value="{{ $addonCategory?->getRawOriginal('name') }}" maxlength="191">
                            </div>
                            <input type="hidden" name="lang[]" value="default">
                        @endif

                    </div>

                </div>
                @if ($categoryWiseTax)
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
            </div>

        </div>
    </div>
    <div class="align-items-center bg-white bottom-0 d-flex gap-3 justify-content-center mt-auto offcanvas-footer p-3 position-sticky">
        <button type="button" class="btn w-100 btn--secondary offcanvas-close h--40px">{{ 'Cancelar' }}</button>
        <button type="submit" class="btn w-100 btn--primary h--40px">{{ 'Actualizar' }}</button>
    </div>
</form>

