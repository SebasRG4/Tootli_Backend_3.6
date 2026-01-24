@extends('layouts.admin.app')

@section('title', translate('Create Taxi Coupon'))

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">
                        <i class="tio-ticket"></i> {{ translate('Create Coupon') }}
                    </h1>
                </div>
                <div class="col-sm-auto">
                    <a href="{{ route('admin.taxi.coupons.index') }}" class="btn btn-secondary">
                        <i class="tio-arrow-left"></i> {{ translate('Back') }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Form -->
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.taxi.coupons.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ translate('Title') }} *</label>
                                <input type="text" name="title" class="form-control" required
                                    placeholder="{{ translate('Coupon title') }}" value="{{ old('title') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ translate('Code') }} *</label>
                                <input type="text" name="code" class="form-control" required
                                    placeholder="{{ translate('COUPON_CODE') }}" value="{{ old('code') }}"
                                    style="text-transform: uppercase;">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>{{ translate('Discount Type') }} *</label>
                                <select name="discount_type" class="form-control" required>
                                    <option value="percent">{{ translate('Percentage') }}</option>
                                    <option value="amount">{{ translate('Fixed Amount') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>{{ translate('Discount') }} *</label>
                                <input type="number" name="discount" class="form-control" required step="0.01" min="0"
                                    value="{{ old('discount', 0) }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>{{ translate('Min Purchase') }} *</label>
                                <input type="number" name="min_purchase" class="form-control" required step="0.01" min="0"
                                    value="{{ old('min_purchase', 0) }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>{{ translate('Max Discount') }} *</label>
                                <input type="number" name="max_discount" class="form-control" required step="0.01" min="0"
                                    value="{{ old('max_discount', 0) }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>{{ translate('Start Date') }} *</label>
                                <input type="date" name="start_date" class="form-control" required
                                    value="{{ old('start_date', date('Y-m-d')) }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>{{ translate('Expire Date') }} *</label>
                                <input type="date" name="expire_date" class="form-control" required
                                    value="{{ old('expire_date') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>{{ translate('Usage Limit') }}</label>
                                <input type="number" name="limit" class="form-control" min="0"
                                    placeholder="{{ translate('Unlimited') }}" value="{{ old('limit') }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>{{ translate('Vehicle Types') }}</label>
                                <small
                                    class="text-muted d-block mb-2">{{ translate('Leave empty for all vehicle types') }}</small>
                                <select name="vehicle_types[]" class="form-control js-select2-custom" multiple>
                                    @foreach($vehicleTypes ?? [] as $type)
                                        <option value="{{ $type->slug }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="input-label">{{ translate('Coupon For') }}</label>
                                <select name="coupon_type" class="form-control" onchange="toggleCustomerType(this.value)">
                                    <option value="all">{{ translate('All Customers') }}</option>
                                    <option value="specific">{{ translate('Specific Customers') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12" id="customer_select_div" style="display: none;">
                            <div class="form-group">
                                <label class="input-label">{{ translate('Select Customers') }}</label>
                                <select name="customer_ids[]" id="customer_ids" class="form-control js-data-example-ajax"
                                    multiple>
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

                            $(document).ready(function () {
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
                            <input type="checkbox" name="status" class="custom-control-input" id="couponStatus" checked>
                            <label class="custom-control-label" for="couponStatus">{{ translate('Active') }}</label>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">{{ translate('Create Coupon') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection