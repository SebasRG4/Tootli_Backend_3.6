<div class="col-lg-12">
    <div class="price_wrapper">
        <div class="outline-wrapper">
            <div class="card shadow--card-2 border-0 bg-animate">
                <div class="card-header">
                    <h5 class="card-title">
                        <span class="card-header-icon mr-2"><i class="tio-dollar-outlined"></i></span>
                        <span>{{ 'Información de precios' }}</span>
                    </h5>
                    @if (isset($openai_config) && data_get($openai_config, 'status') == 1)
                        <button type="button"
                            class="btn bg-white text-primary opacity-1 generate_btn_wrapper p-0 mb-2 price_others_auto_fill"
                            id="price_others_auto_fill" data-route="{{ route('admin.product.price-others-auto-fill') }}"
                            data-error="{{ 'Proporcione un nombre y una descripción del elemento para que la IA pueda generar datos adecuados.' }}"
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
                        <div class="col-md-3">
                            <div class="form-group mb-0 error-wrapper">
                                <label class="input-label"
                                    for="exampleFormControlInput1">{{ 'Precio unitario' }}
                                    {{ \App\CentralLogics\Helpers::currency_symbol() }}<span
                                        class="form-label-secondary text-danger" data-toggle="tooltip"
                                        data-placement="right"
                                        data-original-title="{{ 'Requerido.' }}"> *
                                    </span>
                                    <span class="form-label-secondary" data-toggle="tooltip"
                                        data-placement="right"
                                        data-original-title="{{ 'sugerencia de precio unitario de la aplicación' }}">
                                        <i class="tio-info-outined"></i>
                                    </span></label>
                                <input type="number" id="unit_price" min="0" max="999999999999.999"
                                    step="0.001" value="{{ $product?->price ?? (old('price') ?? 0) }}" name="price"
                                    class="form-control" placeholder="{{ 'Ej: 100' }}" required>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group mb-0 error-wrapper">
                                <label class="input-label" for="menu_price">
                                    {{ 'precio del menú directo de tootli' }}
                                    {{ \App\CentralLogics\Helpers::currency_symbol() }}
                                    <span class="form-label-secondary" data-toggle="tooltip"
                                        data-placement="right"
                                        data-original-title="{{ 'sugerencia de precio del menú directo de tootli' }}">
                                        <i class="tio-info-outined"></i>
                                    </span>
                                </label>
                                <input type="number" id="menu_price" min="0" max="999999999999.999"
                                    step="0.001"
                                    value="{{ $product?->menu_price ?? old('menu_price') }}"
                                    name="menu_price"
                                    class="form-control"
                                    placeholder="{{ 'opcional' }}">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group mb-0 error-wrapper">
                                <label class="input-label" for="abastos_price">
                                    Precio Abastos (Insumos)
                                    {{ \App\CentralLogics\Helpers::currency_symbol() }}
                                    <span class="form-label-secondary" data-toggle="tooltip"
                                        data-placement="right"
                                        data-original-title="Precio especial para la sección de compras de tiendas Tootli Abastos">
                                        <i class="tio-info-outined"></i>
                                    </span>
                                </label>
                                <input type="number" id="abastos_price" min="0" max="999999999999.999"
                                    step="0.001"
                                    value="{{ $product?->abastos_price ?? old('abastos_price') }}"
                                    name="abastos_price"
                                    class="form-control"
                                    placeholder="{{ 'opcional' }}">
                            </div>
                        </div>

                        @if ($productWiseTax)
                            <div class="col-md-3">
                                <div class="form-group pickup-zone-tag mb-0 error-wrapper">
                                    <label class="input-label"
                                        for="exampleFormControlInput1">{{ 'Seleccionar tasa impositiva' }}
                                    </label>
                                    <select name="tax_ids[]" id="" class="form-control multiple-select2"
                                        multiple="multiple" data-placeholder="{{ '--Seleccione tasa impositiva--' }}">
                                        @foreach ($taxVats as $taxVat)
                                            <option
                                                {{ isset($taxVatIds) && in_array($taxVat->id, $taxVatIds) ? 'selected' : '' }}
                                                value="{{ $taxVat->id }}"> {{ $taxVat->name }}
                                                ({{ $taxVat->tax_rate }}%)
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endif

                        <div class="col-md-3">
                            <div class="form-group mb-0 error-wrapper">
                                <label class="input-label"
                                    for="exampleFormControlInput1">{{ 'tipo de descuento' }}

                                </label>
                                <select name="discount_type" id="discount_type" class="form-control js-select2-custom">
                                    <option
                                        {{ isset($product) && $product->discount_type == 'percent' ? 'selected' : '' }}
                                        value="percent">{{ 'por ciento' . ' (%)' }}</option>
                                    <option
                                        {{ isset($product) && $product->discount_type == 'amount' ? 'selected' : '' }}
                                        value="amount">
                                        {{ 'cantidad' . ' (' . \App\CentralLogics\Helpers::currency_symbol() . ')' }}
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-0 error-wrapper">
                                <label class="input-label"
                                    for="exampleFormControlInput1">{{ 'descuento' }}
                                    <span class="form-label-secondary text-danger" data-toggle="tooltip"
                                        data-placement="right"
                                        data-original-title="{{ 'Requerido.' }}"> *
                                    </span>
                                    <span class="input-label-secondary text--title" data-toggle="tooltip"
                                        data-placement="right"
                                        data-original-title="{{ 'Actualmente necesitas gestionar el descuento con el Restaurante.' }}">
                                        <i class="tio-info-outined"></i>
                                    </span>
                                </label>
                                <input type="number" min="0" max="999999999999999"
                                    value="{{ isset($product) ? $product->discount : old('discount', 0) }}"
                                    id="discount" name="discount" class="form-control"
                                    placeholder="{{ 'Ej: 100' }} ">
                            </div>
                        </div>
                        <div class="col-md-3" id="maximum_cart_quantity">
                            <div class="form-group mb-0 error-wrapper">
                                <label class="input-label"
                                    for="maximum_cart_quantity">{{ 'Límite de cantidad máxima de compra' }}
                                    <span class="input-label-secondary text--title" data-toggle="tooltip"
                                        data-placement="right"
                                        data-original-title="{{ 'Si se supera este límite, los clientes no podrán adquirir el alimento en una sola compra.' }}">
                                        <i class="tio-info-outined"></i>
                                    </span>
                                </label>
                                <input type="number"
                                    value="{{ isset($product) ? $product->maximum_cart_quantity : old('maximum_cart_quantity') }}"
                                    placeholder="{{ 'Ej: 10' }}" class="form-control"
                                    name="maximum_cart_quantity" min="0" id="cart_quantity">
                            </div>
                        </div>

                        @if (Config::get('module.current_module_type') != 'food')
                            <div class="col-sm-6 col-lg-3" id="stock_input">
                                <div class="form-group mb-0 error-wrapper">
                                    <label class="input-label"
                                        for="total_stock">{{ 'existencias totales' }}</label>
                                    <input type="number" class="form-control" name="current_stock" min="0"
                                        value="{{ isset($product) ? $product->stock : '' }}" id="quantity">
                                </div>
                            </div>
                        @endif

                        <div class="col-sm-6 col-lg-3" id="weight_input">
                            <div class="form-group mb-0 error-wrapper">
                                <label class="input-label"
                                    for="weight">{{ 'peso' }} (kg)</label>
                                <input type="number" class="form-control" name="weight" min="0" step="0.01"
                                    value="{{ isset($product) ? $product->weight : old('weight', 0) }}" id="weight">
                            </div>
                        </div>


                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
