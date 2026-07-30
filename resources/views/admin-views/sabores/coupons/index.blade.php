@extends('layouts.admin.app')

@section('title', 'Cupones')

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon"><i class="tio-gift"></i></span>
                <span>{{ 'Cupones de restaurante' }}</span>
            </h1>
        </div>

        <!-- Card -->
        <div class="card">
            <!-- Header -->
            <div class="card-header">
                <div class="row justify-content-between align-items-center flex-grow-1">
                    <div class="col-lg-6 mb-3 mb-lg-0">
                        <form action="{{ route('admin.sabores.coupons') }}" method="GET">
                            <div class="input-group input-group-merge input-group-flush">
                                <div class="input-group-prepend">
                                    <div class="input-group-text"><i class="tio-search"></i></div>
                                </div>
                                <input type="search" name="search" class="form-control"
                                    placeholder="{{ 'Buscar por título o código' }}" value="{{ $search }}">
                                <button type="submit" class="btn btn-primary">{{ 'Buscar' }}</button>
                            </div>
                        </form>
                    </div>
                    <div class="col-lg-4">
                        <select name="store_id" class="form-control"
                            onchange="location.href='{{ route('admin.sabores.coupons') }}?store_id=' + this.value + '&search={{ $search }}'">
                            <option value="">{{ 'Todos los restaurantes' }}</option>
                            @foreach($stores as $store)
                                <option value="{{ $store->id }}" {{ $store_id == $store->id ? 'selected' : '' }}>
                                    {{ $store->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive datatable-custom">
                <table
                    class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                    <thead class="thead-light">
                        <tr>
                            <th>{{ 'IDENTIFICACIÓN' }}</th>
                            <th>{{ 'Título' }}</th>
                            <th>{{ 'Código' }}</th>
                            <th>{{ 'Restaurante' }}</th>
                            <th>{{ 'Descuento' }}</th>
                            <th>{{ 'Período válido' }}</th>
                            <th>{{ 'Estado' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($coupons as $coupon)
                            <tr>
                                <td>{{ $coupon->id }}</td>
                                <td>{{ $coupon->title }}</td>
                                <td><code>{{ $coupon->code }}</code></td>
                                <td>
                                    <div class="media align-items-center">
                                        <div class="avatar avatar-sm avatar-circle mr-2">
                                            <img class="avatar-img" src="{{ $coupon->store->logo_full_url }}"
                                                onerror="this.src='{{ asset('assets/admin/img/160x160/img2.jpg') }}'"
                                                alt="{{ $coupon->store->name }}">
                                        </div>
                                        <span>{{ $coupon->store->name }}</span>
                                    </div>
                                </td>
                                <td>
                                    @if($coupon->coupon_type == 'default')
                                        <span class="badge badge-soft-success">${{ $coupon->discount }}</span>
                                    @else
                                        <span class="badge badge-soft-info">{{ $coupon->discount }}%</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="text-body">
                                        <i class="tio-date-range"></i>
                                        {{ \Carbon\Carbon::parse($coupon->start_date)->format('M d') }} -
                                        {{ \Carbon\Carbon::parse($coupon->expire_date)->format('M d, Y') }}
                                    </div>
                                </td>
                                <td>
                                    @if($coupon->status && $coupon->expire_date >= now())
                                        <span class="badge badge-soft-success">{{ 'Activo' }}</span>
                                    @else
                                        <span class="badge badge-soft-secondary">{{ 'Inactivo' }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">
                                    <img class="mb-3 w-160" src="{{ asset('assets/admin/svg/illustrations/sorry.svg') }}"
                                        alt="">
                                    <p class="mb-0">{{ 'No se encontraron cupones' }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Footer -->
            <div class="card-footer">
                {!! $coupons->links() !!}
            </div>
        </div>
    </div>
@endsection