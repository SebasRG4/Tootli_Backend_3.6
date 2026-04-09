<div class="d-flex flex-row cart--table-scroll pos-grill-cart-scroll">
    <table class="table table-bordered pos-grill-cart-table">
        <thead class="text-muted thead-light pos-grill-cart-thead">
            <tr class="text-center">
                <th class="border-bottom-0" scope="col">{{ translate('messages.item') }}</th>
                <th class="border-bottom-0" scope="col">{{ translate('messages.qty') }}</th>
                <th class="border-bottom-0" scope="col">{{ translate('messages.price') }}</th>
                <th class="border-bottom-0" scope="col">{{ translate('messages.delete') }}</th>
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

            <dt class="col-6 font-regular">{{ translate('messages.addon') }}:</dt>
            <dd class="col-6 text-right">{{ \App\CentralLogics\Helpers::format_currency($addon_price) }}</dd>

            <dt class="col-6 font-regular">{{ translate('messages.subtotal') }}
                @if ($tax_included ==  1)
                ({{ translate('messages.TAX_Included') }})
                @endif
                :</dt>
            <dd class="col-6 text-right">{{ \App\CentralLogics\Helpers::format_currency($subtotal + $addon_price) }}</dd>


            <dt class="col-6 font-regular">{{ translate('messages.discount') }} :</dt>
            <dd class="col-6 text-right">-
                {{ \App\CentralLogics\Helpers::format_currency(round($discount_on_product, 2)) }}</dd>
            <dt class="col-6 font-regular">{{ translate('messages.delivery_fee') }} :</dt>
            <dd class="col-6 text-right" id="delivery_price">
                {{ \App\CentralLogics\Helpers::format_currency($delivery_fee) }}</dd>

            <dt class="col-6 font-regular">{{ translate('messages.extra_discount') }} :</dt>
            <dd class="col-6 text-right"><button class="btn btn-sm" type="button" data-toggle="modal"
                    data-target="#add-discount"><i class="tio-edit"></i></button>-
                {{ \App\CentralLogics\Helpers::format_currency(round($discount_amount, 2)) }}</dd>
            @if ($tax_included !=  1)
                <dt class="col-6 font-regular">{{ translate('messages.tax') }} : </dt>
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
            <dt class="col-6 font-regular">{{ translate('Total') }}: </dt>
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
                                <span>{{ translate('Efectivo al repartidor') }}</span>
                            </label>
                        </li>
                        <li>
                            <label>
                                <input type="radio" name="type" value="card_tootli_direct" hidden>
                                <span>{{ translate('Tarjeta') }}</span>
                            </label>
                        </li>
                        <li>
                            <label>
                                <input type="radio" name="type" value="paid_at_restaurant" hidden>
                                <span>{{ translate('Pagado al restaurante') }}</span>
                            </label>
                        </li>
                    @endif
                @else
                    <li id="payment_cash">
                        <label>
                            <input type="radio" name="type" value="cash" hidden="" checked>
                            <span>{{ translate('Efectivo') }}</span>
                        </label>
                    </li>
                    <li id="payment_card">
                        <label>
                            <input type="radio" name="type" value="card_in_store" hidden="">
                            <span>{{ translate('Tarjeta') }}</span>
                        </label>
                    </li>
                    <li id="payment_transfer">
                        <label>
                            <input type="radio" name="type" value="bank_transfer_in_store" hidden="">
                            <span>{{ translate('Transferencia') }}</span>
                        </label>
                    </li>
                @endif

            </ul>
        </div>
        <div id="card-fee-box" class="border rounded p-2 mb-3 d-none">
            <div class="row g-2">
                <div class="col-md-6">
                    <label class="input-label">{{ translate('Comisión tarjeta (%)') }}</label>
                    <input type="number" step="0.01" min="0" class="form-control" name="card_fee_percent" id="card_fee_percent" value="0">
                </div>
                <div class="col-md-6">
                    <label class="input-label">{{ translate('IVA sobre comisión (%)') }}</label>
                    <input type="number" step="0.01" min="0" class="form-control" name="card_fee_vat_percent" id="card_fee_vat_percent" value="0">
                </div>
                <div class="col-md-12">
                    <label class="input-label">{{ translate('Monto cobrado') }} ({{ \App\CentralLogics\Helpers::currency_symbol() }})</label>
                    <input type="number" step="0.01" min="0" class="form-control" name="card_gross_amount" id="card_gross_amount" value="{{ round($total + $tax_amount, 2) }}">
                </div>
                <div class="col-md-12">
                    <small class="text-muted d-block">
                        {{ translate('Monto después de comisión') }}:
                        <strong id="card_net_amount">{{ \App\CentralLogics\Helpers::format_currency(round($total + $tax_amount, 2)) }}</strong>
                    </small>
                </div>
            </div>
        </div>
        @if (!$add)
            <div class="pos--payment-options mt-3 mb-3">
                <h5 class="mb-3">{{ translate('messages.order_type') }}</h5>
                <ul>
                    <li>
                        <label>
                            <input type="radio" name="service_type" value="take_away" hidden checked>
                            <span>{{ translate('messages.take_away') }}</span>
                        </label>
                    </li>
                    <li>
                        <label>
                            <input type="radio" name="service_type" value="dine_in" hidden>
                            <span>{{ translate('Comer en restaurante') }}</span>
                        </label>
                    </li>
                </ul>
            </div>

            <div id="paid_section">
                <div class="mt-4 d-flex justify-content-between pos--payable-amount">
                    <label class="m-0">{{ translate('Paid Amount') }} :</label>
                    <div>
                        <span data-toggle="modal" data-target="#insertPayableAmount" class="text-body"><i
                                class="tio-edit"></i></span>
                        <span>{{ \App\CentralLogics\Helpers::format_currency($paid) }}</span>
                        <input type="hidden" name="amount" value="{{ $paid }}">
                    </div>
                </div>
                <div class="mt-4 d-flex justify-content-between pos--payable-amount">
                    <label class="m-0">{{ translate('Change Amount') }} :</label>
                    <div>
                        <span>{{ \App\CentralLogics\Helpers::format_currency($change) }}</span>
                        <input type="hidden" value="{{ $change }}">
                    </div>
                </div>
            </div>
        @endif
        <div class="row button--bottom-fixed g-1 bg-white">
            <div class="col-sm-6">
                <button type="submit" class="btn  btn--primary btn-sm btn-block place-order-submit">{{ translate('place_order') }}
                </button>
            </div>
            <div class="col-sm-6">
                <a href="#" class="btn btn--reset btn-sm btn-block empty-Cart"
                    >{{ translate('Clear Cart') }}</a>
            </div>
        </div>
    </div>
</form>
<div class="modal fade" id="insertPayableAmount" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-light border-bottom py-3">
                <h5 class="modal-title">{{ translate('messages.payment') }}</h5>
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
                                for="paid">{{ translate('messages.amount') }}({{ \App\CentralLogics\Helpers::currency_symbol() }})</label>
                            <input type="number" class="form-control"  id="paid" name="paid" min="0" step="0.01"
                                value="{{ $paid }}">
                        </div>
                    </div>
                    <div class="form-group col-12 mb-0">
                        <div class="btn--container justify-content-end">
                            <button class="btn btn-sm btn--primary payable-amount" type="button" >
                                {{ translate('messages.submit') }}
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
                <h5 class="modal-title">{{ translate('messages.update_discount') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('vendor.pos.discount') }}" method="post" class="row">
                    @csrf
                    <div class="form-group col-sm-6">
                        <label for="discount_input">{{ translate('messages.discount') }}</label>
                        <input type="number" class="form-control" name="discount" min="0"
                            id="discount_input" value="{{ $discount }}"
                            max="{{ $discount_type == 'percent' ? 100 : 1000000000 }}">
                    </div>
                    <div class="form-group col-sm-6">
                        <label for="discount_input_type">{{ translate('messages.type') }}</label>
                        <select name="type" class="form-control" id="discount_input_type" >
                            <option value="amount" {{ $discount_type == 'amount' ? 'selected' : '' }}>
                                {{ translate('messages.amount') }}({{ \App\CentralLogics\Helpers::currency_symbol() }})
                            </option>
                            <option value="percent" {{ $discount_type == 'percent' ? 'selected' : '' }}>
                                {{ translate('messages.percent') }}(%)</option>
                        </select>
                    </div>
                    <div class="form-group col-sm-12">
                        <button class="btn btn-sm btn--primary"
                            type="submit">{{ translate('messages.submit') }}</button>
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
                <h5 class="modal-title">{{ translate('messages.update_tax') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('vendor.pos.tax') }}" method="POST" class="row" id="order_submit_form">
                    @csrf
                    <div class="form-group col-12">
                        <label for="tax">{{ translate('messages.tax') }}(%)</label>
                        <input type="number" id="tax" class="form-control" name="tax" min="0">
                    </div>

                    <div class="form-group col-sm-12">
                        <button class="btn btn-sm btn--primary"
                            type="submit">{{ translate('messages.submit') }}</button>
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
                <h5 class="modal-title flex-grow-1 text-center">{{ translate('Delivery Information') }}</h5>
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

                    <div class="row g-2" id="delivery_address">
                        <div class="col-md-6">
                            <label class="input-label"
                                for="contact_person_name">{{ translate('messages.contact_person_name') }}<span
                                    class="input-label-secondary text-danger">*</span></label>
                            <input type="text" id="contact_person_name" class="form-control" name="contact_person_name"
                                value="{{ $old ? $old['contact_person_name'] : '' }}"
                                placeholder="{{ translate('Ex: Jhone') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="input-label" for="contact_person_number">{{ translate('Contact Number') }}<span
                                    class="input-label-secondary text-danger">*</span></label>
                            <input type="tel" id="contact_person_number" class="form-control" name="contact_person_number"
                                value="{{ $old ? $old['contact_person_number'] : '' }}"
                                placeholder="{{ translate('Ex: +3264124565') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="input-label" for="road">{{ translate('messages.Road') }}<span
                                    class="input-label-secondary text-danger">*</span></label>
                            <input type="text" id="road" class="form-control" name="road"
                                value="{{ $old ? $old['road'] : '' }}" placeholder="{{ translate('Ex: 4th') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="input-label" for="house">{{ translate('messages.House') }}<span
                                    class="input-label-secondary text-danger">*</span></label>
                            <input type="text" id="house" class="form-control" name="house"
                                value="{{ $old ? $old['house'] : '' }}" placeholder="{{ translate('Ex: 45/C') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="input-label" for="floor">{{ translate('messages.Floor') }}<span
                                    class="input-label-secondary text-danger">*</span></label>
                            <input type="text" id="floor" class="form-control" name="floor"
                                value="{{ $old ? $old['floor'] : '' }}" placeholder="{{ translate('Ex: 1A') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="input-label" for="longitude">{{ translate('messages.longitude') }}<span
                                    class="input-label-secondary text-danger">*</span></label>
                            <input type="text" class="form-control" id="longitude" name="longitude"
                                value="{{ $old ? $old['longitude'] : '' }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="input-label" for="latitude">{{ translate('messages.latitude') }}<span
                                    class="input-label-secondary text-danger">*</span></label>
                            <input type="text" class="form-control" id="latitude" name="latitude"
                                value="{{ $old ? $old['latitude'] : '' }}" readonly>
                        </div>
                        <div class="col-md-12">
                            <label class="input-label" for="address">{{ translate('messages.address') }}</label>
                            <textarea name="address" id="address" class="form-control" cols="30" rows="3"
                                placeholder="{{ translate('Ex: address') }}">{{ $old ? $old['address'] : '' }}</textarea>
                        </div>
                        <input type="hidden" name="original_delivery_fee" id="original_delivery_fee"
                            value="{{ $old ? ($old['original_delivery_fee'] ?? $old['delivery_fee']) : '' }}">
                        <div class="col-md-12 mb-2">
                            <label class="input-label"
                                for="customer_delivery_fee">{{ translate('messages.tootli_direct_customer_pays_delivery') }}</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="customer_delivery_fee"
                                value="{{ $old ? $old['delivery_fee'] : '' }}"
                                placeholder="{{ translate('messages.Ex:_100') }}">
                            <small
                                class="text-muted">{{ translate('messages.tootli_direct_full_fee_stored_hint') }}</small>
                        </div>
                        <div class="col-12">
                            <div class="d-flex justify-content-between">
                                <span class="text-primary">
                                    {{ translate('* pin the address in the map to calculate delivery fee') }}
                                </span>
                                <div>
                                    <span>{{ translate('Delivery fee') }} :</span>
                                    <input type="hidden" name="distance" id="distance">
                                    <input type="hidden" name="delivery_fee" id="delivery_fee"
                                        value="{{ $old ? $old['delivery_fee'] : '' }}">
                                    <strong>{{ $old ? $old['delivery_fee'] : 0 }}
                                        {{ \App\CentralLogics\Helpers::currency_symbol() }}</strong>
                                </div>
                            </div>
                            <input id="pac-input" class="controls rounded initial-8"
                                title="{{ translate('messages.search_your_location_here') }}" type="text"
                                placeholder="{{ translate('messages.search_here') }}" />
                            <div class="mb-2 h-200px" id="map"></div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="btn--container justify-content-end">
                            <button class="btn btn-sm btn--primary w-100 delivery-Address-Store" type="button"
                                >
                                {{ translate('Update_Delivery address') }}
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
