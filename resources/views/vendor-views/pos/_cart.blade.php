<div class="d-flex flex-row cart--table-scroll pos-grill-cart-scroll">
    <table class="table table-bordered pos-grill-cart-table">
        <thead class="text-muted thead-light pos-grill-cart-thead">
            <tr class="text-center">
                <th class="border-bottom-0" scope="col">{{ 'Producto' }}</th>
                <th class="border-bottom-0" scope="col">{{ 'cantidad' }}</th>
                <th class="border-bottom-0" scope="col">{{ 'precio' }}</th>
                <th class="border-bottom-0" scope="col">{{ 'borrar' }}</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $subtotal = 0;
            $addon_price = 0;
            $tax = session()->get('tax_amount');
            $discount = 0;
            $discount_type = 'amount';
            $discount_on_product = 0;
            $variation_price = 0;
            ?>
            @if (session()->has('cart') && count(session()->get('cart')) > 0)
                <?php
                $cart = session()->get('cart');
                if (isset($cart['discount'])) {
                    $discount = $cart['discount'];
                    $discount_type = $cart['discount_type'];
                }
                ?>
                @foreach (session()->get('cart') as $key => $cartItem)
                    @if (is_array($cartItem))
                        <?php
                        $variation_price +=  $cartItem['variation_price'] ?? 0;
                        $product_subtotal = $cartItem['price'] * $cartItem['quantity'];
                        $discount_on_product += $cartItem['discount'] * $cartItem['quantity'];
                        $subtotal += $product_subtotal;
                        $addon_price += $cartItem['addon_price'];
                        ?>
                        <tr>
                            <td class="media align-items-center cursor-pointer quick-View-Cart-Item"
                                data-product-id="{{$cartItem['id']}}" data-item-key="{{$key}}">
                                <img class="avatar avatar-sm mr-1 onerror-image"
                                     data-onerror-image="{{ asset('assets/admin/img/100x100/2.png') }}"
                                     src="{{ $cartItem['image_full_url'] }}"

                                    alt="{{ $cartItem['name'] }} image">
                                <div class="media-body">
                                    <h5 class="text-hover-primary mb-0">{{ Str::limit($cartItem['name'], 10) }}</h5>
                                    <small>{{ Str::limit($cartItem['variant'], 20) }}</small>
                                </div>
                            </td>
                            <td class="text-center middle-align">
                                <input type="number" data-key="{{ $key }}"
                                    class="amount--input form-control text-center  update-Quantity" value="{{ $cartItem['quantity'] }}"
                                    min="1" max="{{$cartItem['maximum_cart_quantity']?? '9999999999'}}" >
                            </td>
                            <td class="text-center px-0 py-1">
                                <div class="btn">
                                    {{ \App\CentralLogics\Helpers::format_currency($product_subtotal) }}
                                </div>
                            </td>
                            <td class="align-items-center text-center ">
                                <a href="javascript:"  data-product-id="{{$key}}"
                                    class="btn btn-sm btn-outline-danger remove-From-Cart"> <i class="tio-delete-outlined"></i></a>
                            </td>
                        </tr>
                    @endif
                @endforeach
            @endif
        </tbody>
    </table>
</div>

<?php
    $add = false;
    if (session()->has('address') && count(session()->get('address')) > 0) {
        $add = true;
        $delivery_fee = session()->get('address')['delivery_fee'];
    } else {
        $delivery_fee = 0;
    }

    $total = $subtotal + $addon_price;

    if ($discount_type == 'percent' && $discount > 0) {
        $discount_amount = (($total - $discount_on_product) * $discount) / 100;
    } else {
        $discount_amount = $discount;
    }

    $total -= $discount_amount + $discount_on_product;

    $tax_amount = session()->get('tax_amount');
    $tax_included = session()->get('tax_included');

    if ($tax_included ==  1){
        $tax_amount = 0;
    }

    $total += $delivery_fee;

    if (isset($cart['paid'])) {
        $paid = $cart['paid'];
        $change = $total + $tax_amount - $paid;
    } else {
        $paid = $total + $tax_amount;
        $change = 0;
    }
?>
<form action="{{ route('vendor.pos.order') }}" id='order_place' method="post">
    @csrf
    <input type="hidden" name="user_id" id="customer_id">
    <input type="hidden" name="internal_customer_id" id="internal_customer_id" value="">
    <div class="box p-3">
        <dl class="row">

            <dt class="col-6 font-regular">{{ 'Añadir' }}:</dt>
            <dd class="col-6 text-right">{{ \App\CentralLogics\Helpers::format_currency($addon_price) }}</dd>

            <dt class="col-6 font-regular">{{ 'total parcial' }}
                @if ($tax_included ==  1)
                ({{ 'IVA incluido' }})
                @endif
                :</dt>
            <dd class="col-6 text-right">{{ \App\CentralLogics\Helpers::format_currency($subtotal + $addon_price) }}</dd>


            <dt class="col-6 font-regular">{{ 'descuento' }} :</dt>
            <dd class="col-6 text-right">-
                {{ \App\CentralLogics\Helpers::format_currency(round($discount_on_product, 2)) }}</dd>
            <dt class="col-6 font-regular">{{ 'tarifa de entrega' }} :</dt>
            <dd class="col-6 text-right" id="delivery_price">
                {{ \App\CentralLogics\Helpers::format_currency($delivery_fee) }}</dd>

            <dt class="col-6 font-regular">{{ 'descuento adicional' }} :</dt>
            <dd class="col-6 text-right"><button class="btn btn-sm" type="button" data-toggle="modal"
                    data-target="#add-discount"><i class="tio-edit"></i></button>-
                {{ \App\CentralLogics\Helpers::format_currency(round($discount_amount, 2)) }}</dd>
            @if ($tax_included !=  1)
                <dt class="col-6 font-regular">{{ 'impuesto' }} : </dt>
                <dd class="col-6 text-right">
{{--                    <button class="btn btn-sm" type="button" data-toggle="modal"--}}
{{--                    data-target="#add-tax">--}}
{{--                        <i class="tio-edit"></i></button>--}}
                    {{ \App\CentralLogics\Helpers::format_currency(round($tax_amount, 2)) }}
                </dd>
            @endif
            <dd class="col-12">
                <hr class="m-0">
            </dd>
            <input type="hidden" id='total_order_amount' value="{{ round($total + $tax_amount, 2) }}">
            <dt class="col-6 font-regular">{{ 'Total' }}: </dt>
            <dd class="col-6 text-right h4 b">
                {{ \App\CentralLogics\Helpers::format_currency(round($total + $tax_amount, 2)) }} </dd>
        </dl>
        <div class="pos--payment-options mt-3 mb-3">
            <h5 class="mb-3">{{ translate($add ? 'messages.Payment Method' : 'Paid by') }}</h5>
            <ul>
                @if ($add)
                    @php($cod = \App\CentralLogics\Helpers::get_business_settings('cash_on_delivery'))
                    @if ($cod['status'])
                        <li>
                            <label>
                                <input type="radio" name="type" value="cash_on_delivery" hidden checked>
                                <span>{{ 'Efectivo al repartidor' }}</span>
                            </label>
                        </li>
                        <li>
                            <label>
                                <input type="radio" name="type" value="card_tootli_direct" hidden>
                                <span>{{ 'Tarjeta' }}</span>
                            </label>
                        </li>
                        <li>
                            <label>
                                <input type="radio" name="type" value="paid_at_restaurant" hidden>
                                <span>{{ 'Pagado al restaurante' }}</span>
                            </label>
                        </li>
                    @endif
                @else
                    <li id="payment_cash">
                        <label>
                            <input type="radio" name="type" value="cash" hidden="" checked>
                            <span>{{ 'Efectivo' }}</span>
                        </label>
                    </li>
                    <li id="payment_card">
                        <label>
                            <input type="radio" name="type" value="card_in_store" hidden="">
                            <span>{{ 'Tarjeta' }}</span>
                        </label>
                    </li>
                    <li id="payment_transfer">
                        <label>
                            <input type="radio" name="type" value="bank_transfer_in_store" hidden="">
                            <span>{{ 'transferencia' }}</span>
                        </label>
                    </li>
                @endif

            </ul>
        </div>
        <div id="card-fee-box" class="border rounded p-2 mb-3 d-none">
            <div class="row g-2">
                <div class="col-md-6">
                    <label class="input-label">{{ 'Comisión tarjeta (%)' }}</label>
                    <input type="number" step="0.01" min="0" class="form-control" name="card_fee_percent" id="card_fee_percent" value="0">
                </div>
                <div class="col-md-6">
                    <label class="input-label">{{ 'IVA sobre comisión (%)' }}</label>
                    <input type="number" step="0.01" min="0" class="form-control" name="card_fee_vat_percent" id="card_fee_vat_percent" value="0">
                </div>
                <div class="col-md-12">
                    <label class="input-label">{{ 'Monto cobrado' }} ({{ \App\CentralLogics\Helpers::currency_symbol() }})</label>
                    <input type="number" step="0.01" min="0" class="form-control" name="card_gross_amount" id="card_gross_amount" value="{{ round($total + $tax_amount, 2) }}">
                </div>
                <div class="col-md-12">
                    <small class="text-muted d-block">
                        {{ 'Monto después de la comisión' }}:
                        <strong id="card_net_amount">{{ \App\CentralLogics\Helpers::format_currency(round($total + $tax_amount, 2)) }}</strong>
                    </small>
                </div>
            </div>
        </div>
        @if (!$add)
            <div class="pos--payment-options mt-3 mb-3">
                <h5 class="mb-3">{{ 'tipo de orden' }}</h5>
                <ul>
                    <li>
                        <label>
                            <input type="radio" name="service_type" value="take_away" hidden checked>
                            <span>{{ 'llevar' }}</span>
                        </label>
                    </li>
                    <li>
                        <label>
                            <input type="radio" name="service_type" value="dine_in" hidden>
                            <span>{{ 'Comer en restaurante' }}</span>
                        </label>
                    </li>
                </ul>
            </div>

            <div id="paid_section">
                <div class="mt-4 d-flex justify-content-between pos--payable-amount">
                    <label class="m-0">{{ 'Monto pagado' }} :</label>
                    <div>
                        <span data-toggle="modal" data-target="#insertPayableAmount" class="text-body"><i
                                class="tio-edit"></i></span>
                        <span>{{ \App\CentralLogics\Helpers::format_currency($paid) }}</span>
                        <input type="hidden" name="amount" value="{{ $paid }}">
                    </div>
                </div>
                <div class="mt-4 d-flex justify-content-between pos--payable-amount">
                    <label class="m-0">{{ 'Cantidad de cambio' }} :</label>
                    <div>
                        <span>{{ \App\CentralLogics\Helpers::format_currency($change) }}</span>
                        <input type="hidden" value="{{ $change }}">
                    </div>
                </div>
            </div>
        @endif
        <div class="row button--bottom-fixed g-1 bg-white">
            <div class="col-sm-6">
                <button type="submit" class="btn  btn--primary btn-sm btn-block place-order-submit">{{ 'realizar pedido' }}
                </button>
            </div>
            <div class="col-sm-6">
                <a href="#" class="btn btn--reset btn-sm btn-block empty-Cart"
                    >{{ 'Borrar carrito' }}</a>
            </div>
        </div>
    </div>
</form>
<div class="modal fade" id="insertPayableAmount" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-light border-bottom py-3">
                <h5 class="modal-title">{{ 'pago' }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id='payable_store_amount'>
                    @csrf
                    <div class="row">
                        <div class="form-group col-12">
                            <label class="input-label"
                                for="paid">{{ 'cantidad' }}({{ \App\CentralLogics\Helpers::currency_symbol() }})</label>
                            <input type="number" class="form-control"  id="paid" name="paid" min="0" step="0.01"
                                value="{{ $paid }}">
                        </div>
                    </div>
                    <div class="form-group col-12 mb-0">
                        <div class="btn--container justify-content-end">
                            <button class="btn btn-sm btn--primary payable-amount" type="button" >
                                {{ 'entregar' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="add-discount" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ 'descuento de actualización' }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('vendor.pos.discount') }}" method="post" class="row">
                    @csrf
                    <div class="form-group col-sm-6">
                        <label for="discount_input">{{ 'descuento' }}</label>
                        <input type="number" class="form-control" name="discount" min="0"
                            id="discount_input" value="{{ $discount }}"
                            max="{{ $discount_type == 'percent' ? 100 : 1000000000 }}">
                    </div>
                    <div class="form-group col-sm-6">
                        <label for="discount_input_type">{{ 'tipo' }}</label>
                        <select name="type" class="form-control" id="discount_input_type" >
                            <option value="amount" {{ $discount_type == 'amount' ? 'selected' : '' }}>
                                {{ 'cantidad' }}({{ \App\CentralLogics\Helpers::currency_symbol() }})
                            </option>
                            <option value="percent" {{ $discount_type == 'percent' ? 'selected' : '' }}>
                                {{ 'por ciento' }}(%)</option>
                        </select>
                    </div>
                    <div class="form-group col-sm-12">
                        <button class="btn btn-sm btn--primary"
                            type="submit">{{ 'entregar' }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="add-tax" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ 'actualizar impuesto' }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('vendor.pos.tax') }}" method="POST" class="row" id="order_submit_form">
                    @csrf
                    <div class="form-group col-12">
                        <label for="tax">{{ 'impuesto' }}(%)</label>
                        <input type="number" id="tax" class="form-control" name="tax" min="0">
                    </div>

                    <div class="form-group col-sm-12">
                        <button class="btn btn-sm btn--primary"
                            type="submit">{{ 'entregar' }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-light border-bottom py-3">
                <h5 class="modal-title flex-grow-1 text-center">{{ 'información pos entrega' }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                <?php
                if (session()->has('address')) {
                    $old = session()->get('address');
                } else {
                    $old = null;
                }
                ?>
                <form id='delivery_address_store'>
                    @csrf
                    <input type="hidden" name="internal_customer_id" id="delivery_internal_customer_id" value="">

                    <div class="row g-2" id="delivery_address">
                        <div class="col-md-6">
                            <label class="input-label"
                                for="contact_person_name">{{ 'nombre de contacto pos' }}<span
                                    class="input-label-secondary text-danger">*</span></label>
                            <input type="text" id="contact_person_name" class="form-control" name="contact_person_name"
                                value="{{ $old ? $old['contact_person_name'] : '' }}"
                                placeholder="{{ 'nombre de contacto del marcador de posición pos' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="input-label" for="contact_person_number">{{ 'teléfono de contacto pos' }}<span
                                    class="input-label-secondary text-danger">*</span></label>
                            <input type="tel" id="contact_person_number" class="form-control" name="contact_person_number"
                                value="{{ $old ? $old['contact_person_number'] : '' }}"
                                placeholder="{{ 'pos marcador de posición teléfono de contacto' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="input-label" for="road">{{ 'pos carretera' }}<span
                                    class="input-label-secondary text-danger">*</span></label>
                            <input type="text" id="road" class="form-control" name="road"
                                value="{{ $old ? $old['road'] : '' }}" placeholder="{{ 'camino del marcador de posición pos' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="input-label" for="house">{{ 'pos casa' }}</label>
                            <input type="text" id="house" class="form-control" name="house"
                                value="{{ $old ? $old['house'] : '' }}" placeholder="{{ 'casa de marcador de posición pos' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="input-label" for="floor">{{ 'pos piso' }}</label>
                            <input type="text" id="floor" class="form-control" name="floor"
                                value="{{ $old ? $old['floor'] : '' }}" placeholder="{{ 'pos piso marcador de posición' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="input-label" for="longitude">{{ 'posición longitud' }}<span
                                    class="input-label-secondary text-danger">*</span></label>
                            <input type="text" class="form-control" id="longitude" name="longitude"
                                value="{{ $old ? $old['longitude'] : '' }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="input-label" for="latitude">{{ 'pos latitud' }}<span
                                    class="input-label-secondary text-danger">*</span></label>
                            <input type="text" class="form-control" id="latitude" name="latitude"
                                value="{{ $old ? $old['latitude'] : '' }}" readonly>
                        </div>
                        <div class="col-md-12">
                            <label class="input-label" for="address">{{ 'notas de dirección pos' }}</label>
                            <textarea name="address" id="address" class="form-control" cols="30" rows="3"
                                placeholder="{{ 'dirección de marcador de posición pos' }}">{{ $old ? $old['address'] : '' }}</textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="input-label" for="gmaps_delivery_link">{{ 'Pos pegar enlace de Google Maps' }}</label>
                            <div class="input-group">
                                <input type="url" class="form-control" id="gmaps_delivery_link" autocomplete="off"
                                    inputmode="url"
                                    placeholder="https://maps.google.com/... o https://maps.app.goo.gl/...">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-primary" id="pos_apply_gmaps_link">
                                        {{ 'Pos aplicar enlace de Google Maps.' }}
                                    </button>
                                </div>
                            </div>
                            <small class="text-muted d-block mt-1">{{ 'pos google mapas enlace ayuda' }}</small>
                        </div>
                        <input type="hidden" name="original_delivery_fee" id="original_delivery_fee"
                            value="{{ $old ? ($old['original_delivery_fee'] ?? $old['delivery_fee']) : '' }}">
                        <div class="col-md-12 mb-2">
                            <label class="input-label"
                                for="customer_delivery_fee">{{ 'El cliente directo de Tootli paga la entrega.' }}</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="customer_delivery_fee"
                                value="{{ $old ? $old['delivery_fee'] : '' }}"
                                placeholder="{{ 'Ej: 100' }}">
                            <small
                                class="text-muted">{{ 'pista almacenada de tarifa completa directa de tootli' }}</small>
                        </div>
                        <div class="col-12">
                            <div class="d-flex justify-content-between">
                                <span class="text-primary">
                                    {{ 'sugerencia de pin del mapa pos' }}
                                </span>
                                <div>
                                    <span>{{ 'etiqueta de tarifa de entrega pos' }} :</span>
                                    <input type="hidden" name="distance" id="distance">
                                    <input type="hidden" name="delivery_fee" id="delivery_fee"
                                        value="{{ $old ? $old['delivery_fee'] : '' }}">
                                    <strong>{{ $old ? $old['delivery_fee'] : 0 }}
                                        {{ \App\CentralLogics\Helpers::currency_symbol() }}</strong>
                                </div>
                            </div>
                            <p class="small text-muted mb-2">{{ 'sugerencia de referencia de la tienda de mapas pos' }}</p>
                            <input id="pac-input" class="controls rounded initial-8"
                                title="{{ 'ubicación de búsqueda pos' }}" type="text"
                                placeholder="{{ 'ubicación de búsqueda pos' }}" />
                            <div class="mb-2 h-200px" id="map"></div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="btn--container justify-content-end">
                            <button class="btn btn-sm btn--primary w-100 delivery-Address-Store" type="button"
                                >
                                {{ 'pos guardar entrega' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(function() {
        $('#customer_delivery_fee').on('input change', function() {
            var v = $(this).val();
            $('#delivery_fee').val(v);
            $('#delivery_fee').siblings('strong').html(v + '{{ \App\CentralLogics\Helpers::currency_symbol() }}');
        });

        function isCardMethod() {
            var v = $('input[name="type"]:checked').val();
            return v === 'card_in_store' || v === 'card_tootli_direct';
        }

        function recalcCardNet() {
            var gross = parseFloat($('#card_gross_amount').val() || '0');
            var fee = parseFloat($('#card_fee_percent').val() || '0');
            var iva = parseFloat($('#card_fee_vat_percent').val() || '0');
            var baseRate = fee / 100;
            var ivaRate = iva / 100;
            var effectiveRate = baseRate + (baseRate * ivaRate);
            var feeAmount = gross * effectiveRate;
            var net = gross - feeAmount;
            if (net < 0) net = 0;
            $('#card_net_amount').text(net.toFixed(2) + ' {{ \App\CentralLogics\Helpers::currency_symbol() }}');
        }

        function toggleCardBox() {
            if (isCardMethod()) {
                $('#card-fee-box').removeClass('d-none');
                recalcCardNet();
            } else {
                $('#card-fee-box').addClass('d-none');
            }
        }

        $(document).on('change', 'input[name="type"]', toggleCardBox);
        $('#card_fee_percent, #card_fee_vat_percent, #card_gross_amount').on('input change', recalcCardNet);
        toggleCardBox();
    });
</script>
<script src="{{asset('assets/admin')}}/js/view-pages/common.js"></script>
