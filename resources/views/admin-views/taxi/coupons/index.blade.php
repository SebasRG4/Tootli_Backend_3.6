@extends('layouts.admin.app')

@section('title', translate('Taxi Coupons'))

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">
                        <i class="tio-ticket"></i> {{ translate('Coupons') }}
                        <span class="badge badge-soft-dark ml-2">{{ $coupons->total() }}</span>
                    </h1>
                </div>
                <div class="col-sm-auto">
                    <a href="{{ route('admin.taxi.coupons.create') }}" class="btn btn-primary">
                        <i class="tio-add"></i> {{ translate('Add Coupon') }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Filter -->
        <div class="card mb-3">
            <div class="card-body">
                <form action="{{ route('admin.taxi.coupons.index') }}" method="GET">
                    <div class="row">
                        <div class="col-md-6">
                            <input type="text" name="search" class="form-control"
                                placeholder="{{ translate('Search by title or code') }}" value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary btn-block">{{ translate('Search') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Coupons Table -->
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-borderless table-thead-bordered table-align-middle">
                        <thead class="thead-light">
                            <tr>
                                <th>{{ translate('ID') }}</th>
                                <th>{{ translate('Title') }}</th>
                                <th>{{ translate('Code') }}</th>
                                <th>{{ translate('Discount') }}</th>
                                <th>{{ translate('Min Purchase') }}</th>
                                <th>{{ translate('Validity') }}</th>
                                <th>{{ translate('Limit') }}</th>
                                <th>{{ translate('Status') }}</th>
                                <th>{{ translate('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($coupons as $coupon)
                                <tr>
                                    <td>{{ $coupon->id }}</td>
                                    <td>{{ $coupon->title }}</td>
                                    <td><code>{{ $coupon->code }}</code></td>
                                    <td>
                                        @if($coupon->discount_type == 'percent')
                                            {{ $coupon->discount }}%
                                        @else
                                            ${{ number_format($coupon->discount, 2) }}
                                        @endif
                                        <small class="text-muted d-block">Max:
                                            ${{ number_format($coupon->max_discount, 2) }}</small>
                                    </td>
                                    <td>${{ number_format($coupon->min_purchase, 2) }}</td>
                                    <td>
                                        <small>{{ date('M d, Y', strtotime($coupon->start_date)) }}</small>
                                        <small
                                            class="d-block text-muted">{{ date('M d, Y', strtotime($coupon->expire_date)) }}</small>
                                    </td>
                                    <td>{{ $coupon->limit ?? translate('Unlimited') }}</td>
                                    <td>
                                        <a href="{{ route('admin.taxi.coupons.status', [$coupon->id, $coupon->status ? 0 : 1]) }}"
                                            class="badge badge-{{ $coupon->status ? 'success' : 'danger' }}">
                                            {{ $coupon->status ? translate('Active') : translate('Inactive') }}
                                        </a>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button"
                                                data-toggle="dropdown">
                                                <i class="tio-more-vertical"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                <a class="dropdown-item"
                                                    href="{{ route('admin.taxi.coupons.edit', $coupon->id) }}">
                                                    <i class="tio-edit"></i> {{ translate('Edit') }}
                                                </a>
                                                <form action="{{ route('admin.taxi.coupons.delete', $coupon->id) }}"
                                                    method="POST" onsubmit="return confirm('{{ translate('Are you sure?') }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="tio-delete"></i> {{ translate('Delete') }}
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4">{{ translate('No coupons found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                {{ $coupons->links() }}
            </div>
        </div>
    </div>
@endsection