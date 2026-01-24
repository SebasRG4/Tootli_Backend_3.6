@extends('layouts.admin.app')

@section('title', translate('Reservations'))

@push('css_or_js')
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <i class="tio-calendar"></i>
                </span>
                <span>{{ translate('Reservations Management') }}</span>
            </h1>
        </div>
        <!-- End Page Header -->

        <!-- Card -->
        <div class="card">
            <!-- Header -->
            <div class="card-header">
                <div class="row justify-content-between align-items-center flex-grow-1">
                    <div class="col-lg-6 mb-3 mb-lg-0">
                        <form action="{{ route('admin.sabores.reservations') }}" method="GET">
                            <div class="input-group input-group-merge input-group-flush">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">
                                        <i class="tio-search"></i>
                                    </div>
                                </div>
                                <input type="search" name="search" class="form-control"
                                    placeholder="{{ translate('Search by code, customer, or restaurant') }}"
                                    value="{{ $search }}">
                                <button type="submit" class="btn btn-primary">{{ translate('Search') }}</button>
                            </div>
                        </form>
                    </div>
                    <div class="col-lg-6">
                        <div class="d-flex gap-3 justify-content-lg-end">
                            <!-- Filter by Store -->
                            <select name="store_id" class="form-control"
                                onchange="location.href='{{ route('admin.sabores.reservations') }}?store_id=' + this.value + '&status={{ $status }}&search={{ $search }}'">
                                <option value="">{{ translate('All Restaurants') }}</option>
                                @foreach($stores as $store)
                                    <option value="{{ $store->id }}" {{ $store_id == $store->id ? 'selected' : '' }}>
                                        {{ $store->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Header -->

            <!-- Table -->
            <div class="table-responsive datatable-custom">
                <table
                    class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                    <thead class="thead-light">
                        <tr>
                            <th>{{ translate('ID') }}</th>
                            <th>{{ translate('Code') }}</th>
                            <th>{{ translate('Customer') }}</th>
                            <th>{{ translate('Restaurant') }}</th>
                            <th>{{ translate('Date & Time') }}</th>
                            <th>{{ translate('Party Size') }}</th>
                            <th>{{ translate('Status') }}</th>
                            <th>{{ translate('Created') }}</th>
                            <th class="text-center">{{ translate('Actions') }}</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($reservations as $reservation)
                            <tr>
                                <td>{{ $reservation->id }}</td>
                                <td>
                                    <a href="{{ route('admin.sabores.reservations.details', $reservation->id) }}"
                                        class="font-weight-bold">
                                        {{ $reservation->confirmation_code }}
                                    </a>
                                </td>
                                <td>
                                    <div class="media align-items-center">
                                        <div class="avatar avatar-circle mr-3">
                                            <img class="avatar-img"
                                                src="{{ $reservation->user->image_full_url ?? asset('assets/admin/img/160x160/img1.jpg') }}"
                                                onerror="this.src='{{ asset('assets/admin/img/160x160/img1.jpg') }}'"
                                                alt="{{ $reservation->user->f_name }}">
                                        </div>
                                        <div class="media-body">
                                            <h5 class="text-hover-primary mb-0">
                                                {{ $reservation->user->f_name }} {{ $reservation->user->l_name }}
                                            </h5>
                                            <span class="d-block font-size-sm text-body">{{ $reservation->user->email }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="media align-items-center">
                                        <div class="avatar avatar-sm avatar-circle mr-2">
                                            <img class="avatar-img" src="{{ $reservation->store->logo_full_url }}"
                                                onerror="this.src='{{ asset('assets/admin/img/160x160/img2.jpg') }}'"
                                                alt="{{ $reservation->store->name }}">
                                        </div>
                                        <span>{{ $reservation->store->name }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <i class="tio-date-range"></i>
                                        {{ \Carbon\Carbon::parse($reservation->reservation_date)->format('M d, Y') }}
                                    </div>
                                    <div class="text-muted">
                                        <i class="tio-time"></i>
                                        {{ \Carbon\Carbon::parse($reservation->reservation_time)->format('h:i A') }}
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-soft-dark">
                                        <i class="tio-user"></i> {{ $reservation->party_size }}
                                    </span>
                                </td>
                                <td>
                                    @if($reservation->status == 'pending')
                                        <span class="badge badge-soft-warning">
                                            <i class="tio-time"></i> {{ translate('Pending') }}
                                        </span>
                                    @elseif($reservation->status == 'confirmed')
                                        <span class="badge badge-soft-success">
                                            <i class="tio-checkmark-circle"></i> {{ translate('Confirmed') }}
                                        </span>
                                    @elseif($reservation->status == 'completed')
                                        <span class="badge badge-soft-primary">
                                            <i class="tio-done"></i> {{ translate('Completed') }}
                                        </span>
                                    @else
                                        <span class="badge badge-soft-danger">
                                            <i class="tio-clear"></i> {{ translate('Cancelled') }}
                                        </span>
                                    @endif
                                </td>
                                <td>{{ $reservation->created_at->diffForHumans() }}</td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <a class="btn btn-sm btn-white"
                                            href="{{ route('admin.sabores.reservations.details', $reservation->id) }}"
                                            title="{{ translate('View Details') }}">
                                            <i class="tio-visible-outlined"></i>
                                        </a>
                                        @if($reservation->status == 'pending')
                                            <button type="button" class="btn btn-sm btn-white"
                                                onclick="updateStatus({{ $reservation->id }}, 'confirmed')"
                                                title="{{ translate('Confirm') }}">
                                                <i class="tio-checkmark-circle text-success"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-white"
                                                onclick="updateStatus({{ $reservation->id }}, 'cancelled')"
                                                title="{{ translate('Cancel') }}">
                                                <i class="tio-clear text-danger"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">
                                    <img class="mb-3 w-160" src="{{ asset('assets/admin/svg/illustrations/sorry.svg') }}"
                                        alt="Image Description">
                                    <p class="mb-0">{{ translate('No reservations found') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <!-- End Table -->

            <!-- Footer -->
            <div class="card-footer">
                <div class="row justify-content-center justify-content-sm-between align-items-sm-center">
                    <div class="col-sm-auto">
                        <div class="d-flex justify-content-center justify-content-sm-end">
                            {!! $reservations->links() !!}
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Footer -->
        </div>
        <!-- End Card -->
    </div>

    <!-- Update Status Form -->
    <form id="status-form" action="" method="POST" style="display: none;">
        @csrf
        <input type="hidden" name="status" id="status-input">
    </form>
@endsection

@push('script_2')
    <script>
        function updateStatus(reservationId, status) {
            if (confirm('{{ translate("Are you sure you want to update this reservation status?") }}')) {
                const form = document.getElementById('status-form');
                form.action = '{{ route("admin.sabores.reservations.update-status", ":id") }}'.replace(':id', reservationId);
                document.getElementById('status-input').value = status;
                form.submit();
            }
        }
    </script>
@endpush