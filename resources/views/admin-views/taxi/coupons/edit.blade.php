@extends('layouts.admin.app')

@section('title', 'Editar cupón de taxi')

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">
                        <i class="tio-ticket"></i> {{ 'Editar cupón' }}: {{ $coupon->code }}
                    </h1>
                </div>
                <div class="col-sm-auto">
                    <a href="{{ route('admin.taxi.coupons.index') }}" class="btn btn-secondary">
                        <i class="tio-arrow-left"></i> {{ 'Atrás' }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Form -->
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.taxi.coupons.update', $coupon->id) }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ 'Título' }} *</label>
                                <input type="text" name="title" class="form-control" required
                                    value="{{ old('title', $coupon->title) }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ 'Código' }} *</label>
                                <input type="text" name="code" class="form-control" required
                                    value="{{ old('code', $coupon->code) }}" style="text-transform: uppercase;">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>{{ 'Tipo de descuento' }} *</label>
                                <select name="discount_type" class="form-control" required>
                                    <option value="percent" {{ $coupon->discount_type == 'percent' ? 'selected' : '' }}>
                                        {{ 'Porcentaje' }}
                                    </option>
                                    <option value="amount" {{ $coupon->discount_type == 'amount' ? 'selected' : '' }}>
                                        {{ 'Monto Fijo' }}
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>{{ 'Descuento' }} *</label>
                                <input type="number" name="discount" class="form-control" required
                                    step="0.01" min="0" value="{{ old('discount', $coupon->discount) }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>{{ 'Compra mínima' }} *</label>
                                <input type="number" name="min_purchase" class="form-control" required
                                    step="0.01" min="0" value="{{ old('min_purchase', $coupon->min_purchase) }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>{{ 'Descuento máximo' }} *</label>
                                <input type="number" name="max_discount" class="form-control" required
                                    step="0.01" min="0" value="{{ old('max_discount', $coupon->max_discount) }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>{{ 'Fecha de inicio' }} *</label>
                                <input type="date" name="start_date" class="form-control" required
                                    value="{{ old('start_date', date('Y-m-d', strtotime($coupon->start_date))) }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>{{ 'Fecha de vencimiento' }} *</label>
                                <input type="date" name="expire_date" class="form-control" required
                                    value="{{ old('expire_date', date('Y-m-d', strtotime($coupon->expire_date))) }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>{{ 'Límite de uso' }}</label>
                                <input type="number" name="limit" class="form-control" min="0"
                                    placeholder="{{ 'Ilimitado' }}" value="{{ old('limit', $coupon->limit) }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>{{ 'Tipos de vehículos' }}</label>
                                <small class="text-muted d-block mb-2">{{ 'Dejar vacío para todo tipo de vehículos' }}</small>
                                @php
                                    $selectedTypes = $coupon->vehicle_types ?? [];
                                @endphp
                                <select name="vehicle_types[]" class="form-control js-select2-custom" multiple>
                                    @foreach($vehicleTypes ?? [] as $type)
                                        <option value="{{ $type->slug }}" {{ in_array($type->slug, $selectedTypes) ? 'selected' : '' }}>
                                            {{ $type->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    @php
                        $customerIds = json_decode($coupon->customer_id, true);
                        $isSpecific = is_array($customerIds) && !in_array('all', $customerIds);
                    @endphp

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="input-label">{{ 'Cupón para' }}</label>
                                <select name="coupon_type" class="form-control" onchange="toggleCustomerType(this.value)">
                                    <option value="all" {{ !$isSpecific ? 'selected' : '' }}>{{ 'Todos los clientes' }}</option>
                                    <option value="specific" {{ $isSpecific ? 'selected' : '' }}>{{ 'Clientes específicos' }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12" id="customer_select_div" style="display: {{ $isSpecific ? 'block' : 'none' }};">
                            <div class="form-group">
                                <label class="input-label">{{ 'Seleccionar clientes' }}</label>
                                <select name="customer_ids[]" id="customer_ids" class="form-control js-data-example-ajax" multiple>
                                    @if($isSpecific && !empty($customers))
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}" selected>{{ $customer->f_name }} {{ $customer->l_name }} ({{ $customer->phone }})</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>

                    @push('script_2')
                    <script>
                        function toggleCustomerType(value) {
                            if (value === 'specific') {
                                $('#customer_select_div').show();
                            } else {
                                $('#customer_select_div').hide();
                            }
                        }

                        $(document).ready(function() {
                            $('.js-data-example-ajax').select2({
                                ajax: {
                                    url: '{{ route('admin.customer.select-list') }}',
                                    data: function (params) {
                                        return {
                                            q: params.term, // search term
                                            page: params.page
                                        };
                                    },
                                    processResults: function (data) {
                                        return {
                                            results: data
                                        };
                                    },
                                    __port: function (params, success, failure) {
                                        var $request = $.ajax(params);
                                        $request.then(success);
                                        $request.fail(failure);
                                        return $request;
                                    }
                                }
                            });
                        });
                    </script>
                    @endpush

                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" name="status" class="custom-control-input" id="couponStatus"
                                {{ $coupon->status ? 'checked' : '' }}>
                            <label class="custom-control-label" for="couponStatus">{{ 'Activo' }}</label>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">{{ 'Cupón de actualización' }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
