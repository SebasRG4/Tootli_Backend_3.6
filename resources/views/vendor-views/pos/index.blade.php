@extends('layouts.vendor.app')

@section('title', translate('messages.POS Orders'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/vendor-pos-minimal.css') }}?v=2.1">
@endpush

@section('content')
    @php($store_data = \App\CentralLogics\Helpers::get_store_data())
    <section class="section-content pos-grill-shell">
        <div class="content container-fluid pos-grill-fluid">
            <div class="pos-grill-layout">
                <div class="pos-grill-main order--pos-left">
                    <div class="pos-grill-panel">
                        <header class="pos-grill-main-header">
                            <div class="pos-grill-brand">
                                <span class="pos-grill-date">{{ now()->translatedFormat('F j, Y') }}</span>
                                <h1 class="pos-grill-store-name">{{ Str::limit($store_data->name, 48) }}</h1>
                            </div>
                            <form id="search-form" class="pos-grill-search-form search-form m-0" autocomplete="off">
                                <span class="pos-grill-search-icon" aria-hidden="true"><i class="tio-search"></i></span>
                                <input id="datatableSearch" type="search" value="{{ $keyword ?? '' }}" name="search"
                                    class="pos-grill-search-input"
                                    placeholder="{{ translate('messages.pos_shell_search_placeholder') }}"
                                    aria-label="{{ translate('messages.search_here') }}">
                                <button class="pos-grill-search-submit" type="submit" aria-label="{{ translate('messages.search_here') }}">
                                    <i class="tio-filter-list"></i>
                                </button>
                            </form>
                        </header>

                        <div class="pos-grill-section-head">
                            <h2 class="pos-grill-section-title">{{ translate('messages.pos_shell_find_food') }}</h2>
                        </div>

                        <div class="pos-grill-category-scroll">
                            <div class="pos-grill-pills" role="tablist" aria-label="{{ translate('messages.select_category') }}">
                                <button type="button" role="tab"
                                    class="pos-grill-pill {{ (int) $category === 0 ? 'active' : '' }}"
                                    data-category-id="">{{ translate('messages.all_categories') }}</button>
                                @foreach ($categories as $item)
                                    <button type="button" role="tab"
                                        class="pos-grill-pill {{ (int) $category === (int) $item->id ? 'active' : '' }}"
                                        data-category-id="{{ $item->id }}">{{ Str::limit($item->name, 22) }}</button>
                                @endforeach
                            </div>
                        </div>

                        <select name="category" id="category" class="pos-grill-category-native" title="{{ translate('messages.select_category') }}"
                            data-pos-ajax-category="1" tabindex="-1" aria-hidden="true">
                            <option value="">{{ translate('messages.all_categories') }}</option>
                            @foreach ($categories as $item)
                                <option value="{{ $item->id }}" {{ (int) $category === (int) $item->id ? 'selected' : '' }}>
                                    {{ Str::limit($item->name, 40) }}</option>
                            @endforeach
                        </select>

                        <div class="pos-grill-products card-body d-flex flex-column" id="items">
                            @include('vendor-views.pos._products_grid', ['products' => $products, 'store_data' => $store_data])
                        </div>
                    </div>
                </div>
                <aside class="pos-grill-order-col order--pos-right">
                    <div class="pos-grill-panel pos-grill-order-panel card">
                        <div class="pos-grill-order-header card-header border-0 d-flex flex-wrap align-items-center justify-content-between gap-2">
                            <h2 class="pos-grill-order-title mb-0">{{ translate('messages.pos_shell_my_order') }}</h2>
                            <span class="badge badge-soft-dark">{{ translate('messages.tootli_direct_order_badge') }}</span>
                        </div>
                        <div class="w-100">
                            <div class="d-flex flex-wrap flex-row p-2 add--customer-btn">
                                <select id='customer' name="customer_id"
                                    data-placeholder="{{ translate('messages.pos_internal_customer_placeholder') }}"
                                    class="js-data-example-ajax form-control"></select>
                                <button class="btn btn--primary" type="button" data-toggle="modal"
                                    data-target="#add-internal-customer">{{ translate('messages.add_internal_customer') }}</button>
                            </div>
                            <div class="pos--delivery-options">
                                <div class="d-flex justify-content-between">
                                    <h5 class="card-title">
                                        <span class="card-title-icon">
                                            <i class="tio-user"></i>
                                        </span>
                                        <span>{{ translate('messages.pos_delivery_information') }}</span>
                                    </h5>
                                    <span class="delivery--edit-icon text-primary" id="delivery_address"
                                        data-toggle="modal" data-target="#paymentModal"><i class="tio-edit"></i></span>
                                </div>
                                @if ($store_data->sub_self_delivery != 1)
                                    <p class="small text-muted mb-2">{{ translate('messages.tootli_direct_pos_delivery_hint') }}</p>
                                @endif
                                <div class="pos--delivery-options-info d-flex flex-wrap" id="del-add">
                                    @include('vendor-views.pos._address')
                                </div>
                            </div>
                        </div>


                        <div class="w-100 pos-grill-cart-host" id="cart">
                            @include('vendor-views.pos._cart')
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </section>

    <div class="modal fade" id="add-internal-customer" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ translate('messages.add_internal_customer') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('vendor.pos.internal-customer-store') }}" method="post">
                        @csrf
                        <div class="form-group">
                            <label class="input-label">{{ translate('first_name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="f_name" class="form-control" required maxlength="100">
                        </div>
                        <div class="form-group">
                            <label class="input-label">{{ translate('last_name') }}</label>
                            <input type="text" name="l_name" class="form-control" maxlength="100">
                        </div>
                        <div class="form-group">
                            <label class="input-label">{{ translate('phone') }} <span class="text-danger">*</span></label>
                            <input type="text" name="phone" class="form-control" required maxlength="20">
                        </div>
                        <p class="small text-muted mb-0">{{ translate('messages.internal_customer_help') }}</p>
                        <div class="btn--container justify-content-end mt-3">
                            <button type="submit" class="btn btn--primary">{{ translate('submit') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- End Content -->
    <div class="modal fade" id="quick-view" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" id="quick-view-modal">

            </div>
        </div>
    </div>

    @php($order = \App\Models\Order::find(session('last_order')))
    @if ($order)
        @php(session(['last_order' => false]))
        <div class="modal fade" id="print-invoice" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ translate('messages.print_invoice') }}
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body row ff-emoji">
                        {{-- <div class="col-md-12">
                            <div class="text-center">
                                <input type="button" class="btn btn--primary non-printable text-white print-Div"
                                    value="Proceed, If thermal printer is ready." />
                                <a href="{{ url()->previous() }}" class="btn btn-danger non-printable">{{translate('messages.back')}}</a>
                            </div>
                            <hr class="non-printable">
                        </div> --}}
                        <div class="row m-auto" id="print-modal-content">
                            @include('vendor-views.pos.invoice')
                        </div>

                    </div>
                </div>
            </div>
        </div>
    @endif


@endsection

@push('script_2')
    <script
        src="https://maps.googleapis.com/maps/api/js?key={{ \App\Models\BusinessSetting::where('key', 'map_api_key')->first()->value }}&libraries=places,marker&callback=initMap&v=3.61">
    </script>

    <script src="{{asset('assets/admin/js/view-pages/pos.js')}}"></script>
    <script>
        "use strict";
        $(document).on('click', '.place-order-submit', function (event) {
            event.preventDefault();
            let sel = document.getElementById('customer');
            let val = (sel && sel.value) ? String(sel.value) : '';
            let userHidden = document.getElementById('customer_id');
            let internalHidden = document.getElementById('internal_customer_id');
            if (userHidden) {
                userHidden.value = '';
            }
            if (internalHidden) {
                internalHidden.value = '';
            }
            if (val && val !== 'false') {
                if (val.indexOf('internal:') === 0 && internalHidden) {
                    internalHidden.value = val.replace(/^internal:/, '');
                } else if (userHidden) {
                    userHidden.value = val;
                }
            }
            document.getElementById('order_place').submit();
        });




        function initMap() {
        const mapId = "{{ \App\Models\BusinessSetting::where('key', 'map_api_key')->first()->value }}"

            let map = new google.maps.Map(document.getElementById("map"), {
                zoom: 13,
                center: {
                    lat: {{ $store_data ? $store_data['latitude'] : '23.757989' }},
                    lng: {{ $store_data ? $store_data['longitude'] : '90.360587' }}
                },
                mapId: mapId
            });

            let zonePolygon = null;

            //get current location block
            let infoWindow = new google.maps.InfoWindow();
            // Try HTML5 geolocation.

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                      let  myLatlng = {
                            lat: position.coords.latitude,
                            lng: position.coords.longitude,
                        };
                        infoWindow.setPosition(myLatlng);
                        infoWindow.setContent("Location found.");
                        infoWindow.open(map);
                        map.setCenter(myLatlng);
                    },
                    () => {
                        handleLocationError(true, infoWindow, map.getCenter());
                    }
                );
            } else {
                // Browser doesn't support Geolocation
                handleLocationError(false, infoWindow, map.getCenter());
            }
            //-----end block------
            const input = document.getElementById("pac-input");
            const searchBox = new google.maps.places.SearchBox(input);
            map.controls[google.maps.ControlPosition.TOP_CENTER].push(input);
            let markers = [];
            const bounds = new google.maps.LatLngBounds();
            searchBox.addListener("places_changed", () => {
                const places = searchBox.getPlaces();

                if (places.length === 0) {
                    return;
                }
                // Clear out the old markers.
                markers.forEach((marker) => {
                    marker.setMap(null);
                });
                markers = [];
                // For each place, get the icon, name and location.
                places.forEach((place) => {
                    if (!place.geometry || !place.geometry.location) {

                        return;
                    }
                    if (!google.maps.geometry.poly.containsLocation(
                            place.geometry.location,
                            zonePolygon
                        )) {
                        toastr.error('{{ translate('messages.out_of_coverage') }}', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                        return false;
                    }

                    document.getElementById('latitude').value = place.geometry.location.lat();
                    document.getElementById('longitude').value = place.geometry.location.lng();
                    const { AdvancedMarkerElement } = google.maps.marker;
                    
                    // Create a marker for each place.
                    markers.push(
                        new AdvancedMarkerElement({
                            map,
                            title: place.name,
                            position: place.geometry.location,
                        })
                    );

                    if (place.geometry.viewport) {
                        // Only geocodes have viewport.
                        bounds.union(place.geometry.viewport);
                    } else {
                        bounds.extend(place.geometry.location);
                    }
                });
                map.fitBounds(bounds);
            });
            @if ($store_data)
                $.get({
                    url: '{{ url('/') }}/admin/zone/get-coordinates/{{ $store_data->zone_id }}',
                    dataType: 'json',
                    success: function(data) {
                        zonePolygon = new google.maps.Polygon({
                            paths: data.coordinates,
                            strokeColor: "#FF0000",
                            strokeOpacity: 0.8,
                            strokeWeight: 2,
                            fillColor: 'white',
                            fillOpacity: 0,
                        });
                        zonePolygon.setMap(map);
                        zonePolygon.getPaths().forEach(function(path) {
                            path.forEach(function(latlng) {
                                bounds.extend(latlng);
                                map.fitBounds(bounds);
                            });
                        });
                        map.setCenter(data.center);
                        google.maps.event.addListener(zonePolygon, 'click', function(mapsMouseEvent) {
                            infoWindow.close();
                            // Create a new InfoWindow.
                            infoWindow = new google.maps.InfoWindow({
                                position: mapsMouseEvent.latLng,
                                content: JSON.stringify(mapsMouseEvent.latLng.toJSON(), null,
                                    2),
                            });
                            let coordinates;
                             coordinates = JSON.stringify(mapsMouseEvent.latLng.toJSON(), null, 2);
                             coordinates = JSON.parse(coordinates);

                            document.getElementById('latitude').value = coordinates['lat'];
                            document.getElementById('longitude').value = coordinates['lng'];
                            infoWindow.open(map);

                            let geocoder  = new google.maps.Geocoder();
                            let latlng = new google.maps.LatLng(coordinates['lat'], coordinates['lng']);

                            geocoder.geocode({
                                'latLng': latlng
                            }, function(results, status) {
                                if (status === google.maps.GeocoderStatus.OK) {
                                    if (results[1]) {
                                        let address = results[1].formatted_address;
                                        // initialize services
                                        const geocoder = new google.maps.Geocoder();
                                        const service = new google.maps.DistanceMatrixService();
                                        // build request
                                        const origin1 = {
                                            lat: {{ $store_data['latitude'] }},
                                            lng: {{ $store_data['longitude'] }}
                                        };
                                        const origin2 = "{{ $store_data->address }}";
                                        const destinationA = address;
                                        const destinationB = {
                                            lat: coordinates['lat'],
                                            lng: coordinates['lng']
                                        };
                                        const request = {
                                            origins: [origin1, origin2],
                                            destinations: [destinationA, destinationB],
                                            travelMode: google.maps.TravelMode.DRIVING,
                                            unitSystem: google.maps.UnitSystem.METRIC,
                                            avoidHighways: false,
                                            avoidTolls: false,
                                        };

                                        // get distance matrix response
                                        service.getDistanceMatrix(request).then((response) => {
                                            // put response
                                            let distancMeter = response.rows[0]
                                                .elements[0].distance['value'];

                                            let distanceMile = distancMeter / 1000;
                                            let distancMileResult = Math.round((
                                                    distanceMile + Number.EPSILON) *
                                                100) / 100;

                                            document.getElementById('distance').value = distancMileResult;
                                            document.getElementById('address').value =response.destinationAddresses[1];


                                        <?php
                                        $module_wise_delivery_charge = $store_data->zone->modules()->where('modules.id', $store_data->module_id)->first();

                                        if($store_data->sub_self_delivery ){
                                                $per_km_shipping_charge = $store_data?->per_km_shipping_charge ?? 0;
                                                $minimum_shipping_charge = $store_data?->minimum_shipping_charge ?? 0;
                                                $maximum_shipping_charge = $store_data?->maximum_shipping_charge?? 0;

                                                $self_delivery_status = 1;
                                        } else{
                                                $self_delivery_status = 0;

                                            if ($module_wise_delivery_charge) {
                                                $per_km_shipping_charge = $module_wise_delivery_charge->pivot->delivery_charge_type == 'distance' ? $module_wise_delivery_charge->pivot->per_km_shipping_charge ?? 0 : $module_wise_delivery_charge->pivot->fixed_shipping_charge ?? 0;
                                                $minimum_shipping_charge = $module_wise_delivery_charge->pivot->delivery_charge_type == 'distance' ? $module_wise_delivery_charge->pivot->minimum_shipping_charge ?? 0 : $module_wise_delivery_charge->pivot->fixed_shipping_charge ?? 0;
                                                $maximum_shipping_charge = $module_wise_delivery_charge->pivot->delivery_charge_type == 'distance' ? $module_wise_delivery_charge->pivot->maximum_shipping_charge ?? 0 : $module_wise_delivery_charge->pivot->fixed_shipping_charge ?? 0;
                                            } else {
                                                $per_km_shipping_charge = (float)\App\Models\BusinessSetting::where(['key' => 'per_km_shipping_charge'])->first()->value;
                                                $minimum_shipping_charge = (float)\App\Models\BusinessSetting::where(['key' => 'minimum_shipping_charge'])->first()->value;
                                                $maximum_shipping_charge = 0;
                                            }
                                        }


                                        ?>

                                        $.get({
                                                url: '{{ route('vendor.pos.extra_charge') }}',
                                                dataType: 'json',
                                                data: {
                                                    distancMileResult: distancMileResult,
                                                    self_delivery_status: {{ $self_delivery_status }},
                                                },
                                                success: function(data) {
                                                   let extra_charge = data;
                                                    let original_delivery_charge =  (distancMileResult * {{$per_km_shipping_charge}} > {{$minimum_shipping_charge}}) ? distancMileResult * {{$per_km_shipping_charge}} : {{$minimum_shipping_charge}};
                                                    let delivery_amount = ({{ $maximum_shipping_charge }} > {{ $minimum_shipping_charge }} && original_delivery_charge + extra_charge > {{ $maximum_shipping_charge }} ? {{ $maximum_shipping_charge }} : original_delivery_charge + extra_charge);
                                                    let delivery_charge =Math.round(( delivery_amount + Number.EPSILON) * 100) / 100;
                                                document.getElementById('delivery_fee').value = delivery_charge;
                                                $('#delivery_fee').siblings('strong').html(delivery_charge + '{{ \App\CentralLogics\Helpers::currency_symbol() }}');
                                                var _odf = document.getElementById('original_delivery_fee');
                                                if (_odf) { _odf.value = delivery_charge; }
                                                var _cdf = document.getElementById('customer_delivery_fee');
                                                if (_cdf) { _cdf.value = delivery_charge; }

                                                },
                                                error:function(){
                                                    let original_delivery_charge =  (distancMileResult * {{$per_km_shipping_charge}} > {{$minimum_shipping_charge}}) ? distancMileResult * {{$per_km_shipping_charge}} : {{$minimum_shipping_charge}};

                                                    let delivery_charge =Math.round((
                                                ({{ $maximum_shipping_charge }} > {{ $minimum_shipping_charge }} && original_delivery_charge  > {{ $maximum_shipping_charge }} ? {{ $maximum_shipping_charge }} : original_delivery_charge)
                                                + Number.EPSILON) * 100) / 100;
                                                document.getElementById('delivery_fee').value = delivery_charge;
                                                $('#delivery_fee').siblings('strong').html(delivery_charge + '{{ \App\CentralLogics\Helpers::currency_symbol() }}');
                                                var _odf2 = document.getElementById('original_delivery_fee');
                                                if (_odf2) { _odf2.value = delivery_charge; }
                                                var _cdf2 = document.getElementById('customer_delivery_fee');
                                                if (_cdf2) { _cdf2.value = delivery_charge; }
                                                }
                                            });
                                        });

                                    }
                                }
                            });
                        });
                    },
                });
            @endif

        }

        $(document).on('ready', function() {
            @if ($order)
                $('#print-invoice').modal('show');
            @endif
        });


        const posProductsGridUrl = @json(route('vendor.pos.products-grid'));

        function posBindImageFallback(container) {
            $(container).find('.onerror-image').each(function () {
                const $img = $(this);
                const def = $img.data('onerror-image');
                $img.off('error.pos').on('error.pos', function () {
                    if (def) {
                        $(this).attr('src', def);
                    }
                });
                const src = $img.attr('src');
                if (src && src.endsWith('/')) {
                    $img.attr('src', def);
                }
            });
        }

        function loadPosProducts(page) {
            page = page || 1;
            const keyword = ($('#datatableSearch').val() || '').trim();
            const categoryId = $('#category').val() || '';
            $('#loading').show();
            $.get(posProductsGridUrl, {
                keyword: keyword,
                category_id: categoryId,
                page: page,
            }).done(function (res) {
                if (res.success && res.html) {
                    $('#items').html(res.html);
                    posBindImageFallback('#items');
                    try {
                        const u = new URL(window.location.href);
                        if (keyword) {
                            u.searchParams.set('keyword', keyword);
                        } else {
                            u.searchParams.delete('keyword');
                        }
                        if (categoryId) {
                            u.searchParams.set('category_id', categoryId);
                        } else {
                            u.searchParams.delete('category_id');
                        }
                        if (page > 1) {
                            u.searchParams.set('page', String(page));
                        } else {
                            u.searchParams.delete('page');
                        }
                        history.replaceState({}, '', u);
                    } catch (e) { /* ignore */ }
                    syncPosCategoryPills();
                }
            }).always(function () {
                $('#loading').hide();
            });
        }

        function syncPosCategoryPills() {
            var v = String($('#category').val() || '');
            $('.pos-grill-pill').removeClass('active');
            $('.pos-grill-pill').each(function () {
                var id = String($(this).attr('data-category-id') || '');
                if (id === v) {
                    $(this).addClass('active');
                }
            });
        }

        $(document).on('click', '.pos-grill-pill', function () {
            var id = String($(this).attr('data-category-id') || '');
            $('#category').val(id).trigger('change');
        });

        $(document).on('click', '.pos-grill-card-add-btn', function (e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).closest('.quick-View').trigger('click');
        });

        $(document).on('keydown', '.pos-grill-card-add-btn', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                e.stopPropagation();
                $(this).closest('.quick-View').trigger('click');
            }
        });

        syncPosCategoryPills();

        $('#search-form').on('submit', function (e) {
            e.preventDefault();
            loadPosProducts(1);
        });

        $('#category').on('change', function () {
            loadPosProducts(1);
        });

        let posSearchDebounce;
        $('#datatableSearch').on('input', function () {
            clearTimeout(posSearchDebounce);
            posSearchDebounce = setTimeout(function () {
                loadPosProducts(1);
            }, 450);
        });

        $(document).on('click', '#items .page-area a[href]', function (e) {
            const href = $(this).attr('href');
            if (!href || href === '#' || href.indexOf('javascript:') === 0) {
                return;
            }
            e.preventDefault();
            let p = 1;
            try {
                p = parseInt(new URL(href, window.location.origin).searchParams.get('page') || '1', 10) || 1;
            } catch (err) {
                p = 1;
            }
            loadPosProducts(p);
        });


        $(document).on('click', '.quick-View', function () {
            $.get({
                url: '{{ route('vendor.pos.quick-view') }}',
                dataType: 'json',
                data: {
                    product_id: $(this).data('id')
                },
                beforeSend: function() {
                    $('#loading').show();
                },
                success: function(data) {
                    $('#quick-view').modal('show');
                    $('#quick-view-modal').empty().html(data.view);
                },
                complete: function() {
                    $('#loading').hide();
                },
            });
        });

        $(document).on('click', '.quick-View-Cart-Item', function () {
            $.get({
                url: '{{ route('vendor.pos.quick-view-cart-item') }}',
                dataType: 'json',
                data: {
                    product_id:  $(this).data('product-id'),
                    item_key:  $(this).data('item-key'),
                },
                beforeSend: function() {
                    $('#loading').show();
                },
                success: function(data) {
                    $('#quick-view').modal('show');
                    $('#quick-view-modal').empty().html(data.view);
                },
                complete: function() {
                    $('#loading').hide();
                },
            });
        });

        function checkAddToCartValidity() {
            let names = {};
            $('#add-to-cart-form input:radio').each(function() {
                names[$(this).attr('name')] = true;
            });
            let count = 0;
            $.each(names, function() {
                count++;
            });
            if ($('input:radio:checked').length === count) {
                return true;
            }
            return true;
        }

        function getVariantPrice() {
            if ($('#add-to-cart-form input[name=quantity]').val() > 0 && checkAddToCartValidity()) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                    }
                });
                $.ajax({
                    type: "POST",
                    url: '{{ route('vendor.pos.variant_price') }}',
                    data: $('#add-to-cart-form').serializeArray(),
                    success: function(data) {
                        $('#add-to-cart-form #chosen_price_div').removeClass('d-none');
                        $('#add-to-cart-form #chosen_price_div #chosen_price').html(data.price);
                    }
                });
            }
        }

        $(document).on('click', '.add-To-Cart', function () {
            if (checkAddToCartValidity()) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                    }
                });
                let form_id = 'add-to-cart-form'
                $.post({
                    url: '{{ route('vendor.pos.add-to-cart') }}',
                    data: $('#' + form_id).serializeArray(),
                    beforeSend: function() {
                        $('#loading').show();
                    },
                    success: function(data) {

                        if (data.data === 1) {
                            Swal.fire({
                                icon: 'info',
                                title: 'Cart',
                                text: "{{ translate('messages.product_already_added_in_cart') }}"
                            });
                            return false;
                        } else if (data.data === 2) {
                            updateCart();
                            Swal.fire({
                                icon: 'info',
                                title: 'Cart',
                                text: "{{ translate('messages.product_has_been_updated_in_cart') }}"
                            });

                            return false;
                        } else if (data.data === 0) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Cart',
                                text: '{{ translate('messages.Sorry, product out of stock') }}'
                            });
                            return false;
                        } else if (data.data === 'letiation_error') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Cart',
                                text: data.message
                            });
                            return false;
                        }
                        $('.call-when-done').click();

                        toastr.success('{{ translate('messages.product_has_been_added_in_cart') }}', {
                            CloseButton: true,
                            ProgressBar: true
                        });

                        updateCart();
                    },
                    complete: function() {
                        $('#loading').hide();
                    }
                });
            } else {
                Swal.fire({
                    type: 'info',
                    title: '{{translate('Cart')}}',
                    text: '{{ translate('Please choose all the options') }}'
                });
            }

        });

        $(document).on('click', '.remove-From-Cart', function () {
            let key=  $(this).data('product-id');
            $.post('{{ route('vendor.pos.remove-from-cart') }}', {
                _token: '{{ csrf_token() }}',
                key: key
            }, function(data) {
                if (data.errors) {
                    for (let i = 0; i < data.errors.length; i++) {
                        toastr.error(data.errors[i].message, {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    }
                } else {
                    updateCart();
                    toastr.info('{{ translate('messages.item_has_been_removed_from_cart') }}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }

            });
        });

        $(document).on('click', '.empty-Cart', function () {
            $.post('{{ route('vendor.pos.emptyCart') }}', {
                _token: '{{ csrf_token() }}'
            }, function() {
                $('#del-add').empty();
                updateCart();
                toastr.info('{{ translate('messages.item_has_been_removed_from_cart') }}', {
                    CloseButton: true,
                    ProgressBar: true
                });
            });
        });

        window.__posPendingDeliveryForm = null;

        function posApplyDeliveryFormValues(f) {
            if (!f || typeof f !== 'object') {
                return;
            }
            var $f = $('#delivery_address_store');
            if (!$f.length) {
                return;
            }
            if (f.contact_person_name !== undefined) {
                $('#contact_person_name').val(f.contact_person_name);
            }
            if (f.contact_person_number !== undefined) {
                $('#contact_person_number').val(f.contact_person_number);
            }
            if (f.road !== undefined) {
                $('#road').val(f.road);
            }
            if (f.house !== undefined) {
                $('#house').val(f.house);
            }
            if (f.floor !== undefined) {
                $('#floor').val(f.floor);
            }
            if (f.longitude !== undefined) {
                $('#longitude').val(f.longitude);
            }
            if (f.latitude !== undefined) {
                $('#latitude').val(f.latitude);
            }
            if (f.address !== undefined) {
                $('#address').val(f.address);
            }
            if (f.delivery_fee !== undefined) {
                $('#delivery_fee').val(f.delivery_fee);
                var sym = '{{ \App\CentralLogics\Helpers::currency_symbol() }}';
                $('#delivery_fee').siblings('strong').html(f.delivery_fee + sym);
            }
            if (f.original_delivery_fee !== undefined) {
                $('#original_delivery_fee').val(f.original_delivery_fee);
            } else if (f.delivery_fee !== undefined) {
                $('#original_delivery_fee').val(f.delivery_fee);
            }
            if (f.distance !== undefined) {
                $('#distance').val(f.distance);
            }
            if (f.delivery_fee !== undefined && $('#customer_delivery_fee').length) {
                $('#customer_delivery_fee').val(f.delivery_fee);
            }
        }

        function updateCart() {
            $.post('<?php echo e(route('vendor.pos.cart_items')); ?>', {
                _token: '<?php echo e(csrf_token()); ?>'
            }, function(data) {
                $('#cart').empty().html(data);
                syncPosCustomerHiddenFields($('#customer'));
                if (window.__posPendingDeliveryForm) {
                    posApplyDeliveryFormValues(window.__posPendingDeliveryForm);
                    window.__posPendingDeliveryForm = null;
                }
            });
        }

        window.posOnCustomerChanged = function (val) {
            var internalId = '';
            if (val && String(val).indexOf('internal:') === 0) {
                internalId = String(val).replace(/^internal:/, '');
            }
            var csrf =
                $('meta[name="csrf-token"]').attr('content') ||
                $('meta[name="_token"]').attr('content') ||
                '';
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': csrf,
                },
            });
            $.post({
                url: '{{ route('vendor.pos.internal-customer-address') }}',
                data: {
                    _token: '<?php echo e(csrf_token()); ?>',
                    internal_customer_id: internalId,
                },
                success: function (res) {
                    if (res.view !== undefined) {
                        $('#del-add').html(res.view);
                    }
                    window.__posPendingDeliveryForm = res.form || null;
                    updateCart();
                },
            });
        };

        function posCurrentInternalCustomerId() {
            var v = $('#customer').val();
            if (v && String(v).indexOf('internal:') === 0) {
                return String(v).replace(/^internal:/, '');
            }
            var h = $('#internal_customer_id').val();
            return h ? String(h) : '';
        }

        $('#paymentModal').on('shown.bs.modal', function () {
            var iid = posCurrentInternalCustomerId();
            if (!iid) {
                return;
            }
            var n = ($('#contact_person_name').val() || '').trim();
            var p = ($('#contact_person_number').val() || '').trim();
            if (n !== '' && p !== '') {
                return;
            }
            $.post({
                url: '{{ route('vendor.pos.internal-customer-address') }}',
                data: {
                    _token: '<?php echo e(csrf_token()); ?>',
                    internal_customer_id: iid,
                    prefill_contact_only: 1,
                },
                success: function (res) {
                    if (res.form) {
                        posApplyDeliveryFormValues(res.form);
                    }
                },
            });
        });

        $(document).on('click', '.delivery-Address-Store', function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                }
            });
            let form_id = 'delivery_address_store';
            $.post({
                url: '{{ route('vendor.pos.add-delivery-info') }}',
                data: $('#' + form_id).serializeArray(),
                beforeSend: function() {
                    $('#loading').show();
                },
                success: function(data) {
                    if (data.errors) {
                        for (let i = 0; i < data.errors.length; i++) {
                            toastr.error(data.errors[i].message, {
                                CloseButton: true,
                                ProgressBar: true
                            });
                        }
                    } else {
                        $('#del-add').empty().html(data.view);
                    }
                    updateCart();
                    $('.call-when-done').click();
                },
                complete: function() {
                    $('#loading').hide();
                    $('#paymentModal').modal('hide');
                }
            });
        });

        $(document).on('click', '.payable-amount', function (event) {
           let form_id = 'payable_store_amount';

                if($('#paid').val() < 0){
                    toastr.error('{{ translate('Amount_must_be_grater_then_0') }}', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                        event.preventDefault();
                        return;
                }
                if($('#paid').val() < $('#total_order_amount').val() ){
                    toastr.error('{{ translate('This_amount_must_grater_then_order_amount') }}', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                        event.preventDefault();
                        return;
                }

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                }
            });
            $.post({
                url: '{{ route('vendor.pos.paid') }}',
                data: $('#' + form_id).serializeArray(),
                beforeSend: function() {
                    $('#loading').show();
                },
                success: function() {

                    updateCart();
                    $('.call-when-done').click();
                },
                complete: function() {
                    $('#loading').hide();
                    $('#insertPayableAmount').modal('hide');
                }
            });

        });

        $(function() {
            $(document).on('click', 'input[type=number]', function() {
                this.select();
            });
        });

        $(document).on('change', '.update-Quantity', function (e) {
            let element = $(e.target);
            let minValue = parseInt(element.attr('min'));
            let maxValue = parseInt(element.attr('max'));
            let valueCurrent = parseInt(element.val());
            let key = element.data('key');


            if (valueCurrent >= minValue && valueCurrent <= maxValue) {
                $.post('{{ route('vendor.pos.updateQuantity') }}', {
                    _token: '{{ csrf_token() }}',
                    key: key,
                    quantity: valueCurrent
                }, function() {
                    updateCart();
                });
            } else if(valueCurrent > maxValue){
                Swal.fire({
                    icon: 'error',
                    title: 'Cart',
                    text: 'Sorry, cart limit exceeded.'
                });
                element.val(element.data('oldValue'));
            }
            else {
                Swal.fire({
                    icon: 'error',
                    title: 'Cart',
                    text: '{{ translate('Sorry, the minimum value was reached') }}'
                });
                element.val(element.data('oldValue'));
            }

            // Allow: backspace, delete, tab, escape, enter and .
            if (e.type === 'keydown') {
                if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 190]) !== -1 ||
                    // Allow: Ctrl+A
                    (e.keyCode === 65 && e.ctrlKey === true) ||
                    // Allow: home, end, left, right
                    (e.keyCode >= 35 && e.keyCode <= 39)) {
                    // let it happen, don't do anything
                    return;
                }
                // Ensure that it is a number and stop the keypress
                if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                    e.preventDefault();
                }
            }

        });

        $('.js-data-example-ajax').select2({
            ajax: {
                url: '{{ route('vendor.pos.customers') }}',
                data: function(params) {
                    return {
                        q: params.term,
                        page: params.page
                    };
                },
                processResults: function(data) {
                    return {
                        results: data
                    };
                },
                __port: function(params, success, failure) {
                    let $request = $.ajax(params);

                    $request.then(success);
                    $request.fail(failure);

                    return $request;
                }
            }
        });

        @php($posPreselectInternal = session('pos_preselect_internal_customer'))
        @if (!empty($posPreselectInternal) && is_array($posPreselectInternal))
        (function () {
            var pre = @json($posPreselectInternal);
            var internalLabel = @json(translate('messages.store_internal_customer'));
            if (!pre || !pre.id) return;
            var optId = 'internal:' + pre.id;
            var name = (pre.f_name || '') + ' ' + (pre.l_name || '');
            name = name.trim();
            var text = name + ' (' + (pre.phone || '') + ') — ' + internalLabel;
            var $sel = $('#customer');
            if (!$sel.length) return;
            $sel.find('option').filter(function () { return this.value === optId; }).remove();
            $sel.append(new Option(text, optId, true, true));
            $sel.trigger('change');
        })();
        @endif

    </script>

@endpush
