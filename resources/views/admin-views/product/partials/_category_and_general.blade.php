<div class="col-lg-12">
    <div class="general_wrapper">
        <div class="outline-wrapper">
            <div class="card shadow--card-2 border-0 bg-animate">
                <div class="card-header">
                    <h5 class="card-title">
                        <span class="card-header-icon mr-2">
                            <i class="tio-tune-horizontal"></i>
                        </span>
                        <span> {{ 'Información de tienda y categoría' }} </span>
                    </h5>
                    @if (isset($openai_config) && data_get($openai_config, 'status') == 1)
                        <button type="button"
                            class="btn bg-white text-primary opacity-1 generate_btn_wrapper p-0 mb-2 general_setup_auto_fill"
                            id="general_setup_auto_fill"
                            data-route="{{ route('admin.product.general-setup-auto-fill') }}"
                            data-error="{{ 'Proporcione un nombre y una descripción del elemento para que la IA pueda generar datos adecuados.' }}"
                            data-restaurant-id=""
                            data-lang="en">
                            <div class="btn-svg-wrapper">
                                <img width="18" height="18" class=""
                                    src="{{ asset('assets/admin/img/svg/blink-right-small.svg') }}"
                                    alt="">
                            </div>
                            <span class="ai-text-animation d-none" role="status">
                                {{ 'Un momento' }}
                            </span>
                            <span class="btn-text">{{ 'Generar' }}</span>
                        </button>
                    @endif

                </div>
                <div class="card-body">
                    <div class="row g-2">
                        @php
                            $column = 4;
                            $default_store = null;
                            if (request()->query('is_abastos') == 1) {
                                $default_store = \App\Models\Store::withoutGlobalScopes()
                                    ->whereHas('module', function ($q) { $q->where('module_type', 'grocery'); })
                                    ->first();

                            }
                        @endphp
                        @if (Auth::guard('admin')->check())
                            <div class="col-sm-6 col-lg-3">

                                <div class="form-group mb-0 error-wrapper">
                                    <label class="input-label" for="store_id">{{ 'Negocio' }} <span
                                            class="form-label-secondary text-danger" data-toggle="tooltip"
                                            data-placement="right"
                                            data-original-title="{{ 'Requerido.' }}"> *
                                        </span><span class="input-label-secondary"></span></label>
                                    <select name="store_id" id="store_id"
                                        title="{{ 'seleccionar tienda' }}"
                                        {{ isset(request()->product_gellary) == false ? 'required' : '' }}
                                        data-placeholder="{{ 'seleccionar tienda' }}"
                                        class="js-data-example-ajax form-control">
                                        @if (isset($product->store) && request()->product_gellary != 1)
                                            <option value="{{ $product->store_id }}" selected="selected">
                                                {{ $product->store->name }}</option>
                                        @elseif ($default_store)
                                            <option value="{{ $default_store->id }}" selected="selected">
                                                {{ $default_store->name }}</option>
                                        @endif
                                    </select>
                                </div>


                            </div>
                            @php($column = 3)
                            <div class="col-sm-6 col-lg-{{ $column }}">
                                <div class="form-group mb-0 error-wrapper">
                                    <label class="input-label"
                                        for="exampleFormControlSelect1">{{ 'categoría' }}<span
                                            class="form-label-secondary text-danger" data-toggle="tooltip"
                                            data-placement="right"
                                            data-original-title="{{ 'Requerido.' }}"> *
                                        </span></label>
                                    <select name="category_id" id="category_id"
                                        data-placeholder="{{ 'Seleccionar categoría' }}"
                                        @if (!Auth::guard('admin')->check()) data-url="{{ url('/') }}/vendor-panel/item/get-categories?parent_id=" data-id="sub-categories" @endif
                                        class="form-control js-data-example-ajax get-request" required>

                                        @if (isset($category))
                                            <option selected value="{{ $category['id'] }}">{{ $category['name'] }}
                                            </option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                        @else
                            <div class="col-sm-6 col-lg-4">
                                <div class="form-group mb-0 error-wrapper">
                                    <label class="input-label"
                                        for="exampleFormControlSelect1">{{ 'categoría' }}<span
                                            class="input-label-secondary">*</span></label>
                                    <select name="category_id" id="category_id"
                                        class="form-control js-select2-custom get-request"
                                        data-url="{{ url('/') }}/vendor-panel/item/get-categories?parent_id="
                                        data-id="sub-categories">
                                        <option value="">---{{ 'seleccionar' }}---</option>
                                        @foreach($categories as $category)
                                                <option
                                                    value="{{$category['id']}}" {{ isset($product) && $category->id==$product_category[0]->id ? 'selected' : ''}} >{{$category['name']}}</option>
                                            @endforeach
                                    </select>
                                </div>
                            </div>

                        @endif



                        <div class="col-sm-6 col-lg-{{ $column }}">


                            <div class="form-group mb-0 error-wrapper">
                                <label class="input-label"
                                    for="exampleFormControlSelect1">{{ 'subcategoría' }}<span
                                        class="form-label-secondary" data-toggle="tooltip" data-placement="right"
                                        data-original-title="{{ 'categoría requerida advertencia' }}"><img
                                            src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                            alt="{{ 'categoría requerida advertencia' }}"></span></label>



                                <select name="sub_category_id"
                                    data-placeholder="{{ 'Seleccionar subcategoría' }}"
                                    class="js-data-example-ajax form-control" id="sub-categories">
                                    @if (isset($sub_category))
                                        <option value="{{ $sub_category['id'] }}">{{ $sub_category['name'] }}
                                        </option>
                                    @endif
                                </select>
                            </div>


                        </div>

                        <div class="col-sm-6 col-lg-{{ $column }}">
                            <div class="form-group mb-0 error-wrapper">
                                <label class="input-label" for="priority">{{ 'prioridad' }}</label>
                                <input type="number" min="0" name="priority" id="priority" class="form-control" 
                                    value="{{ old('priority', $product->priority ?? 0) }}" placeholder="{{ '0' }}">
                            </div>
                        </div>

                        <div class="col-sm-6 col-lg-{{ $column }}">
                            <div class="form-group mb-0 error-wrapper">
                                <label class="input-label" for="barcode">{{ 'Código de Barras' }}</label>
                                <div class="input-group">
                                    <input type="text" name="barcode" id="barcode" class="form-control" 
                                        value="{{ old('barcode', $product->barcode ?? '') }}" placeholder="{{ 'Ej. 7501011110022' }}">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-secondary" onclick="openBarcodeScanner('barcode')" title="{{ 'Escanear con cámara' }}">
                                            <i class="tio-camera"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if (Config::get('module.current_module_type') == 'food')
                            <div class="col-sm-6 col-lg-{{ $column }}" id="veg_input">
                                <div class="form-group mb-0 error-wrapper">
                                    <label class="input-label"
                                        for="exampleFormControlInput1">{{ 'tipo de artículo' }} <span
                                            class="form-label-secondary text-danger" data-toggle="tooltip"
                                            data-placement="right"
                                            data-original-title="{{ 'Requerido.' }}"> *
                                        </span></label>
                                    <select name="veg" id="veg" class="form-control js-select2-custom"
                                        required>
                                        <option {{ isset($product) && $product->veg == 1 ? 'selected' : '' }}
                                            value="1">{{ 'verduras' }}</option>
                                        <option {{ isset($product) && $product->veg == 0 ? 'selected' : '' }}
                                            value="0">{{ 'no vegetariano' }}</option>
                                    </select>
                                </div>
                            </div>
                        @endif




                        @if (Config::get('module.current_module_type') == 'pharmacy')

                            <div class="col-sm-6 col-lg-{{ $column }}" id="condition_input">

                                <div class="form-group mb-0 error-wrapper">
                                    <label class="input-label"
                                        for="condition_id">{{ 'Adecuado para' }}<span
                                            class="input-label-secondary"></span></label>
                                    <select name="condition_id" id="condition_id"
                                        data-placeholder="{{ 'Seleccionar condición' }}"
                                        class="js-data-example-ajax form-control">

                                        @if (isset($product?->pharmacy_item_details?->common_condition_id))
                                            <option value="{{ $product->pharmacy_item_details->common_condition_id }}"
                                                selected="selected">
                                                {{ $product->pharmacy_item_details?->common_condition->name }}</option>
                                        @elseif(isset($temp_product) && $temp_product == 1 && $product->common_condition_id)
                                            <option value="{{ $product->common_condition_id }}" selected="selected">
                                                {{ $product->common_condition->name }}</option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                        @endif

                        @if (Config::get('module.current_module_type') == 'ecommerce')

                            <div class="col-sm-6 col-lg-{{ $column }}" id="brand_input">
                                <div class="form-group mb-0 error-wrapper">
                                    <label class="input-label" for="brand_id">{{ 'Marca' }}<span
                                            class="input-label-secondary"></span></label>
                                    <select name="brand_id" id="brand_id"
                                        data-placeholder="{{ 'Seleccionar marca' }}"
                                        class="js-data-example-ajax form-control">
                                        @if (isset($product->ecommerce_item_details?->brand_id))
                                            <option value="{{ $product->ecommerce_item_details->brand_id }}"
                                                selected="selected">
                                                {{ $product->ecommerce_item_details?->brand->name }}</option>
                                        @elseif(isset($temp_product) && $temp_product == 1 && $product->brand_id)
                                            <option value="{{ $product->brand_id }}" selected="selected">
                                                {{ $product->brand->name }}</option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                        @endif
                        @if (Config::get('module.current_module_type') != 'food')

                            <div class="col-sm-6 col-lg-{{ $column }}" id="unit_input">
                                <div class="form-group mb-0 error-wrapper">
                                    <label class="input-label text-capitalize"
                                        for="unit">{{ 'unidad' }}</label>
                                    <select name="unit" id="unit"
                                        data-placeholder="{{ 'seleccionar unidad' }}"
                                        class="form-control js-select2-custom">
                                        @foreach (\App\Models\Unit::get(['id', 'unit']) as $unit)
                                            <option value="{{ $unit->id }}"
                                                {{ isset($product) && $unit->id == $product->unit_id ? 'selected' : '' }}>
                                                {{ $unit->unit }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endif




                        @if (Config::get('module.current_module_type') == 'grocery' || Config::get('module.current_module_type') == 'food')
                            @if (isset($temp_product) && $temp_product == 1)
                                @php($product_nutritions = \App\Models\Nutrition::whereIn('id', json_decode($product?->nutrition_ids))->pluck('id'))
                                @php($product_allergies = \App\Models\Allergy::whereIn('id', json_decode($product?->allergy_ids))->pluck('id'))
                            @else
                                @php($product_nutritions = isset($product) ? $product->nutritions->pluck('id') : null)
                                @php($product_allergies = isset($product) ? $product->allergies->pluck('id') : null)
                            @endif

                            <div class="col-sm-6 col-lg-6 error-wrapper" id="nutrition">
                                <label class="input-label" for="">
                                    {{ 'Nutrición' }}
                                    <span class="input-label-secondary"
                                        title="{{ 'Especifique las palabras clave necesarias relacionadas con los valores energéticos del artículo, escriba este contenido y presione Intro.' }}"
                                        data-toggle="tooltip">
                                        <i class="tio-info-outined"></i>
                                    </span>
                                </label>
                                <select name="nutritions[]" id="nutritions_input"
                                    class="form-control multiple-select2"
                                    data-placeholder="{{ 'Escribe tu contenido y presiona enter.' }}"
                                    multiple>
                                    @php($nutritions = \App\Models\Nutrition::select(['id','nutrition'])->get() ?? [])
                                    @foreach ($nutritions as $nutrition)
                                        <option
                                            {{ $product_nutritions && $product_nutritions->contains($nutrition->id) ? 'selected' : '' }}
                                            value="{{ $nutrition->nutrition }}">{{ $nutrition->nutrition }}</option>
                                    @endforeach
                                </select>
                            </div>


                            <div class="col-sm-6 col-lg-6 error-wrapper" id="allergy">
                                <label class="input-label" for="">
                                    {{ 'Ingredientes Allegren' }}
                                    <span class="input-label-secondary"
                                        title="{{ 'Especifique los ingredientes del artículo que pueden provocar una reacción como alérgeno, escriba este contenido y presione Intro.' }}"
                                        data-toggle="tooltip">
                                        <i class="tio-info-outined"></i>
                                    </span>
                                </label>
                                <select name="allergies[]" class="form-control multiple-select2" id="allergy_input"
                                    data-placeholder="{{ 'Escribe tu contenido y presiona enter.' }}"
                                    multiple>
                                    @php($allergies = \App\Models\Allergy::select(['id','allergy'])->get() ?? [])

                                    @foreach ($allergies as $allergy)
                                        <option
                                            {{ $product_allergies && $product_allergies->contains($allergy->id) ? 'selected' : '' }}
                                            value="{{ $allergy->allergy }}">{{ $allergy->allergy }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif



                        @if (Config::get('module.current_module_type') == 'grocery' || request()->query('is_abastos') == 1 || (isset($product) && $product->is_abastos == 1))
                        <div class="col-sm-6 col-lg-4 error-wrapper" id="is_abastos_div">
                            <div class="form-check mb-sm-2 pb-sm-1">
                                <input class="form-check-input" name="is_abastos" type="checkbox" value="1"
                                    id="is_abastos"
                                    {{ (isset($product) && $product->is_abastos == 1) || request()->query('is_abastos') == 1 ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_abastos">
                                    {{ 'exclusivo para tootli abastos' }}
                                </label>
                            </div>
                            @if(request()->query('is_abastos') == 1)
                                <input type="hidden" name="is_abastos" value="1">
                            @endif
                        </div>
                        @endif

                        @if (Config::get('module.current_module_type') == 'grocery' || Config::get('module.current_module_type') == 'food')
                            <div class="col-sm-6 col-lg-4 error-wrapper" id="halal">
                                <div class="form-check mb-sm-2 pb-sm-1">
                                    <input class="form-check-input" name="is_halal" type="checkbox" value="1"
                                        id="is_halal"
                                        {{ isset($product) && $product->is_halal == 1 ? 'checked' : (isset($temp_product) && $temp_product == 1 && $product->is_halal == 1 ? 'checked' : '') }}>
                                    <label class="form-check-label" for="is_halal">
                                        {{ '¿Es halal?' }}
                                    </label>
                                </div>
                            </div>
                        @endif
                        @if (Config::get('module.current_module_type') == 'food')
                            <div class="col-sm-6 col-lg-4 error-wrapper" id="promotional">
                                <div class="form-check mb-sm-2 pb-sm-1">
                                    <input class="form-check-input" name="is_promotional" type="checkbox" value="1"
                                        id="is_promotional"
                                        {{ isset($product) && $product->is_promotional == 1 ? 'checked' : (isset($temp_product) && $temp_product == 1 && $product->is_promotional == 1 ? 'checked' : '') }}>
                                    <label class="form-check-label" for="is_promotional">
                                        {{ 'Promocional' }}
                                    </label>
                                </div>
                            </div>
                        @endif
                        @if (Config::get('module.current_module_type') == 'pharmacy')
                            <div class="col-sm-6 col-lg--6 error-wrapper" id="generic_name">
                                <label class="input-label" for="sub-categories">
                                    {{ 'nombre genérico' }}
                                    <span class="input-label-secondary"
                                        title="{{ 'Especificar el ingrediente activo del medicamento que lo hace funcionar.' }}"
                                        data-toggle="tooltip">
                                        <i class="tio-info-outined"></i>
                                    </span>
                                </label>
                                <div class="dropdown suggestion_dropdown">
                                    <input type="text" id="generic_name_input"
                                        value="{{ (isset($temp_product) && $temp_product == 1 ? \App\Models\GenericName::where('id', json_decode($product?->generic_ids))->first()?->generic_name : isset($product)) ? $product?->generic->pluck('generic_name')->first() : '' }}"
                                        class="form-control" name="generic_name" autocomplete="off">
                                    @php($generic_names = \App\Models\GenericName::select(['id','generic_name'])->get() ?? [])
                                    @if (count($generic_names) > 0)
                                        <div class="dropdown-menu">
                                            @foreach ($generic_names ?? [] as $generic_name)
                                                <div class="dropdown-item">{{ $generic_name->generic_name }}</div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="col-sm-6 col-lg-4 error-wrapper" id="basic">
                                <div class="form-check mb-sm-2 pb-sm-1">
                                    <input class="form-check-input" name="basic" type="checkbox" value="1"
                                        id="is_basic_medicine"
                                        {{ isset($product) && $product->pharmacy_item_details?->is_basic == 1 ? 'checked' : (isset($temp_product) && $temp_product == 1 && $product->basic == 1 ? 'checked' : '') }}>
                                    <label class="form-check-label" for="is_basic_medicine">
                                        {{ 'es la medicina basica' }}
                                    </label>
                                </div>
                            </div>

                            <div class="col-sm-6 col-lg-4 error-wrapper" id="is_prescription_required">
                                <div class="form-check mb-sm-2 pb-sm-1">
                                    <input class="form-check-input" name="is_prescription_required" type="checkbox"
                                        value="1" id="prescription_required"
                                        {{ isset($product) && $product->pharmacy_item_details?->is_prescription_required == 1 ? 'checked' : (isset($temp_product) && $temp_product == 1 && $product->is_prescription_required == 1 ? 'checked' : '') }}>
                                    <label class="form-check-label" for="prescription_required">
                                        {{ '¿Se requiere receta médica?' }}
                                    </label>
                                </div>
                            </div>
                        @endif
                        @if (Config::get('module.current_module_type') == 'grocery')
                            <div class="col-sm-6 col-lg-4 error-wrapper" id="organic">
                                <div class="form-check mb-sm-2 pb-sm-1">
                                    <input class="form-check-input" name="organic" type="checkbox" value="1"
                                        id="is_organic"
                                        {{ isset($product) && $product->organic == 1 ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_organic">
                                        {{ 'es organico' }}
                                    </label>
                                </div>
                            </div>
                        @endif

                        @if (Config::get('module.current_module_type') == 'grocery' || Config::get('module.current_module_type') == 'ecommerce')
                            <div class="col-sm-6 col-lg-4 error-wrapper">
                                <div class="form-group mb-0">
                                    <label class="input-label" for="delivery_time_type">{{ 'tipo de tiempo de entrega' }}</label>
                                    <select name="delivery_time_type" id="delivery_time_type" class="form-control js-select2-custom">
                                        <option value="standard" {{ isset($product) && $product->delivery_time_type == 'standard' ? 'selected' : '' }}>{{ 'estándar' }}</option>
                                        <option value="minutes" {{ isset($product) && $product->delivery_time_type == 'minutes' ? 'selected' : '' }}>{{ 'minutos' }}</option>
                                        <option value="next_day" {{ isset($product) && $product->delivery_time_type == 'next_day' ? 'selected' : '' }}>{{ 'día siguiente' }}</option>
                                    </select>
                                </div>
                            </div>
                        @endif

                        @if (Config::get('module.current_module_type') != 'food')
                            <div class="col-sm-6 col-lg-3 error-wrapper">
                                <div class="form-group mb-0">
                                    <label class="input-label" for="length">{{ 'Longitud (cm)' }}</label>
                                    <input type="number" min="0" step="0.1" name="length" id="length" class="form-control" 
                                        value="{{ old('length', $product->length ?? 0) }}" placeholder="{{ '0' }}">
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-3 error-wrapper">
                                <div class="form-group mb-0">
                                    <label class="input-label" for="width">{{ 'Ancho (cm)' }}</label>
                                    <input type="number" min="0" step="0.1" name="width" id="width" class="form-control" 
                                        value="{{ old('width', $product->width ?? 0) }}" placeholder="{{ '0' }}">
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-3 error-wrapper">
                                <div class="form-group mb-0">
                                    <label class="input-label" for="height">{{ 'alto (cm)' }}</label>
                                    <input type="number" min="0" step="0.1" name="height" id="height" class="form-control" 
                                        value="{{ old('height', $product->height ?? 0) }}" placeholder="{{ '0' }}">
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-3 error-wrapper">
                                <div class="form-group mb-0">
                                    <label class="input-label" for="requires_large_vehicle">{{ 'Envío Pesado' }}</label>
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" name="requires_large_vehicle" type="checkbox" value="1"
                                            id="requires_large_vehicle"
                                            {{ isset($product) && $product->requires_large_vehicle == 1 ? 'checked' : '' }}>
                                        <label class="form-check-label" for="requires_large_vehicle">
                                            {{ '¿Necesitas auto/camioneta?' }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        @endif


                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@if (Config::get('module.current_module_type') == 'food')
    @if (Auth::guard('admin')->check())
        <div class="col-lg-6" id="addon_input">
            <div class="general_wrapper">
                <div class="outline-wrapper">
                    <div class="card shadow--card-2 border-0 bg-animate">
                        <div class="card-header">
                            <h5 class="card-title">
                                <span class="card-header-icon mr-2">
                                    <i class="tio-dashboard-outlined"></i>
                                </span>
                                <span>{{ 'Añadir' }}</span>
                            </h5>
                        </div>
                        <div class="card-body error-wrapper">
                            <label class="input-label"
                                for="exampleFormControlSelect1">{{ 'Seleccionar complemento' }}<span
                                    class="input-label-secondary" data-toggle="tooltip" data-placement="right"
                                    data-original-title="{{ 'Los complementos seleccionados se mostrarán en los detalles de esta comida.' }}"><img
                                        src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                        alt="{{ 'Los complementos seleccionados se mostrarán en los detalles de esta comida.' }}"></span></label>
                            <select name="addon_ids[]" class="form-control border js-select2-custom"
                                multiple="multiple" id="add_on">

                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="col-lg-6">
            <div class="general_wrapper">
                <div class="outline-wrapper">
                    <div class="card shadow--card-2 border-0 bg-animate">
                        <div class="card-header">
                            <h5 class="card-title">
                                <span class="card-header-icon"><i class="tio-puzzle"></i></span>
                                <span>{{ 'Añadir' }}</span>
                            </h5>
                        </div>
                        <div class="card-body pb-0">
                            <div class="row g-2">
                                <div class="col-12">
                                    <div class="form-group error-wrapper">
                                        <label class="input-label"
                                            for="exampleFormControlSelect1">{{ 'Añadir' }}<span
                                                class="input-label-secondary"></span></label>
                                        <select name="addon_ids[]" class="form-control js-select2-custom" id="add_on"
                                            multiple="multiple">
                                            @foreach (\App\Models\AddOn::where('store_id', \App\CentralLogics\Helpers::get_store_id())->orderBy('name')->get() as $addon)
                                                <option value="{{ $addon['id'] }}"
                                                    {{ isset($product) && in_array($addon->id, json_decode($product['add_ons'], true)) ? 'selected' : '' }}>
                                                    {{ $addon['name'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="col-lg-6" id="time_input">
        <div class="general_wrapper">
            <div class="outline-wrapper">
                <div class="card shadow--card-2 border-0 bg-animate">
                    <div class="card-header">
                        <h5 class="card-title">
                            <span class="card-header-icon mr-2"><i class="tio-date-range"></i></span>
                            <span>{{ 'horario' }}</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-sm-6">
                                <div class="form-group mb-0 error-wrapper">
                                    <label class="input-label"
                                        for="exampleFormControlInput1">{{ 'comienza el tiempo disponible' }}<span
                                            class="form-label-secondary text-danger" data-toggle="tooltip"
                                            data-placement="right"
                                            data-original-title="{{ 'Requerido.' }}">
                                            *
                                        </span></label>
                                    <input type="time" name="available_time_starts"
                                        value="{{ isset($product) ? $product?->available_time_starts : old('available_time_starts') }}"
                                        class="form-control" id="available_time_starts"
                                        placeholder="{{ 'Ej: 10:30 am' }} " required>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group mb-0 error-wrapper">
                                    <label class="input-label"
                                        for="exampleFormControlInput1">{{ 'finaliza el tiempo disponible' }}<span
                                            class="form-label-secondary text-danger" data-toggle="tooltip"
                                            data-placement="right"
                                            data-original-title="{{ 'Requerido.' }}">
                                            *
                                        </span></label>
                                    <input type="time" name="available_time_ends" class="form-control"
                                        value="{{ isset($product) ? $product?->available_time_ends : old('available_time_ends') }}"
                                        id="available_time_ends" placeholder="5:45 pm" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif


<div class="col-lg-12">
    <div class="general_wrapper">
        <div class="outline-wrapper">
            <div class="card shadow--card-2 border-0 bg-animate">
                <div class="card-header">
                    <h5 class="card-title">
                        <span class="card-header-icon mr-2"><i class="tio-label"></i></span>
                        <span>{{ 'Etiquetas de búsqueda' }}</span>
                    </h5>
                </div>
                <div class="card-body">


                    @if (isset($temp_product) && $temp_product == 1)
                        <div class="form-group error-wrapper">
                            @php($tags = \App\Models\Tag::whereIn('id', json_decode($product?->tag_ids))->get('tag'))
                            <input type="text" class="form-control" id="tags" name="tags"
                                placeholder="{{ 'etiquetas de búsqueda' }}"
                                value="@foreach ($tags as $c) {{ $c->tag . ',' }} @endforeach"
                                data-role="tagsinput">
                        </div>
                    @else
                        <div class="form-group error-wrapper">
                            <input type="text" class="form-control" id="tags" name="tags"
                                placeholder="{{ 'etiquetas de búsqueda' }}"
                                @if (isset($product)) value="@foreach ($product->tags as $c) {{ $c->tag . ',' }} @endforeach" @endif
                                data-role="tagsinput">
                        </div>
                    @endif


                </div>
            </div>
        </div>
    </div>
</div>

@include('layouts.admin.partials._barcode_scanner')
