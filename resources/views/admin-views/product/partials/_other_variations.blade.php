                <div class="col-md-12" id="attribute_section">
                    <div class="card shadow--card-2 border-0">
                        <div class="card-header">
                            <h5 class="card-title">
                                <span class="card-header-icon"><i class="tio-canvas-text"></i></span>
                                <span>{{ 'atributo' }}</span>
                            </h5>
                        @if (isset($openai_config) && data_get($openai_config, 'status') == 1)
                        <button type="button" class="btn bg-white text-primary opacity-1 generate_btn_wrapper p-0 mb-2 other_variation_setup_auto_fill"
                            id="other_variation_setup_auto_fill" data-route="{{ route('admin.product.generate-other-variation-data') }}"
                            data-error="{{ 'Proporcione un nombre y una descripción del elemento para que la IA pueda generar variaciones adecuadas.' }}"
                            data-lang="en">
                            <div class="btn-svg-wrapper">
                                <img width="18" height="18" class=""
                                    src="{{ asset('assets/admin/img/svg/blink-right-small.svg') }}" alt="">
                            </div>
                            <span class="ai-text-animation d-none" role="status">
                                {{ 'Un momento' }}
                            </span>
                            <span class="btn-text">{{ 'Generar' }}</span>
                        </button>
                        @endif
                        </div>
                        <div class="card-body pb-0">
                            <div class="row g-2">
                                <div class="col-12">
                                    <div class="form-group mb-0 error-wrapper">
                                        <label class="input-label"
                                            for="exampleFormControlSelect1">{{ 'atributo' }}<span
                                                class="input-label-secondary"></span></label>
                                        <select name="attribute_id[]" id="choice_attributes"
                                        data-placeholder="{{ 'Seleccionar atributo' }}"
                                            class="form-control js-select2-custom" multiple="multiple">
                                            @foreach (\App\Models\Attribute::orderBy('name')->get() as $attribute)
                                                <option value="{{ $attribute['id'] }}">{{ $attribute['name'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="table-responsive">
                                        <div class="customer_choice_options d-flex __gap-24px error-wrapper" id="customer_choice_options">

                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="variant_combination" id="variant_combination">

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
