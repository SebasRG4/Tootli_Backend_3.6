@extends('layouts.admin.app')

@section('title', translate('Restaurants'))

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon"><i class="tio-restaurant"></i></span>
                <span>{{ translate('Restaurants Management') }}</span>
            </h1>
        </div>

        <!-- Card -->
        <div class="card">
            <!-- Header -->
            <div class="card-header">
                <form action="{{ route('admin.sabores.restaurants') }}" method="GET">
                    <div class="input-group input-group-merge input-group-flush">
                        <div class="input-group-prepend">
                            <div class="input-group-text"><i class="tio-search"></i></div>
                        </div>
                        <input type="search" name="search" class="form-control"
                            placeholder="{{ translate('Search restaurants') }}" value="{{ $search }}">
                        <button type="submit" class="btn btn-primary">{{ translate('Search') }}</button>
                    </div>
                </form>
            </div>

            <!-- Table -->
            <div class="table-responsive datatable-custom">
                <table
                    class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                    <thead class="thead-light">
                        <tr>
                            <th>{{ translate('ID') }}</th>
                            <th>{{ translate('Restaurant') }}</th>
                            <th>{{ translate('Address') }}</th>
                            <th>{{ translate('Average Ticket') }}</th>
                            <th>{{ translate('Accepts Reservations') }}</th>
                            <th>{{ translate('Total Reservations') }}</th>
                            <th class="text-center">{{ translate('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($restaurants as $restaurant)
                            <tr>
                                <td>{{ $restaurant->id }}</td>
                                <td>
                                    <div class="media align-items-center">
                                        <div class="avatar avatar-circle mr-3">
                                            <img class="avatar-img" src="{{ $restaurant->logo_full_url }}"
                                                onerror="this.src='{{ asset('assets/admin/img/160x160/img2.jpg') }}'"
                                                alt="{{ $restaurant->name }}">
                                        </div>
                                        <div class="media-body">
                                            <h5 class="text-hover-primary mb-0">{{ $restaurant->name }}</h5>
                                            <span class="d-block font-size-sm text-body">{{ $restaurant->phone }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ Str::limit($restaurant->address, 40) }}</td>
                                <td>
                                    @if($restaurant->average_ticket)
                                        <span
                                            class="badge badge-soft-success">${{ number_format($restaurant->average_ticket, 2) }}</span>
                                    @else
                                        <span class="text-muted">{{ translate('Not set') }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($restaurant->accepts_reservations)
                                        <span class="badge badge-soft-success">
                                            <i class="tio-checkmark-circle"></i> {{ translate('Yes') }}
                                        </span>
                                    @else
                                        <span class="badge badge-soft-secondary">
                                            <i class="tio-clear"></i> {{ translate('No') }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-soft-info">
                                        {{ $restaurant->reservations_count ?? 0 }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a class="btn btn-sm btn-white"
                                        href="{{ route('admin.sabores.restaurants.edit', $restaurant->id) }}">
                                        <i class="tio-edit"></i> {{ translate('Edit') }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">
                                    <img class="mb-3 w-160" src="{{ asset('assets/admin/svg/illustrations/sorry.svg') }}"
                                        alt="">
                                    <p class="mb-0">{{ translate('No restaurants found') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Footer -->
            <div class="card-footer">
                {!! $restaurants->links() !!}
            </div>
        </div>
    </div>
@endsection