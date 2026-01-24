@extends('layouts.admin.app')

@section('title', translate('Reservation Details'))

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">
                        <span class="page-header-icon"><i class="tio-calendar"></i></span>
                        <span>{{ translate('Reservation') }} #{{ $reservation->confirmation_code }}</span>
                    </h1>
                </div>
                <div class="col-sm-auto">
                    <a class="btn btn-icon btn-sm btn-ghost-secondary rounded-circle mr-1"
                        href="{{ route('admin.sabores.reservations') }}" title="{{ translate('Back') }}">
                        <i class="tio-arrow-backward"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <!-- Reservation Info Card -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h4 class="card-header-title">{{ translate('Reservation Information') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <h6 class="text-cap">{{ translate('Confirmation Code') }}</h6>
                                <span class="h3">{{ $reservation->confirmation_code }}</span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <h6 class="text-cap">{{ translate('Status') }}</h6>
                                @if($reservation->status == 'pending')
                                    <span class="badge badge-soft-warning badge-pill">{{ translate('Pending') }}</span>
                                @elseif($reservation->status == 'confirmed')
                                    <span class="badge badge-soft-success badge-pill">{{ translate('Confirmed') }}</span>
                                @elseif($reservation->status == 'completed')
                                    <span class="badge badge-soft-primary badge-pill">{{ translate('Completed') }}</span>
                                @else
                                    <span class="badge badge-soft-danger badge-pill">{{ translate('Cancelled') }}</span>
                                @endif
                            </div>
                            <div class="col-md-6 mb-3">
                                <h6 class="text-cap">{{ translate('Date') }}</h6>
                                <p>{{ \Carbon\Carbon::parse($reservation->reservation_date)->format('l, F d, Y') }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <h6 class="text-cap">{{ translate('Time') }}</h6>
                                <p>{{ \Carbon\Carbon::parse($reservation->reservation_time)->format('h:i A') }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <h6 class="text-cap">{{ translate('Party Size') }}</h6>
                                <p><i class="tio-user"></i> {{ $reservation->party_size }} {{ translate('people') }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <h6 class="text-cap">{{ translate('Created At') }}</h6>
                                <p>{{ $reservation->created_at->format('M d, Y h:i A') }}</p>
                            </div>
                            @if($reservation->special_requests)
                                <div class="col-12">
                                    <h6 class="text-cap">{{ translate('Special Requests') }}</h6>
                                    <p class="text-muted">{{ $reservation->special_requests }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Customer Info Card -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h4 class="card-header-title">{{ translate('Customer Information') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="media align-items-center">
                            <div class="avatar avatar-xl avatar-circle mr-3">
                                <img class="avatar-img"
                                    src="{{ $reservation->user->image_full_url ?? asset('assets/admin/img/160x160/img1.jpg') }}"
                                    onerror="this.src='{{ asset('assets/admin/img/160x160/img1.jpg') }}'"
                                    alt="{{ $reservation->user->f_name }}">
                            </div>
                            <div class="media-body">
                                <h3 class="mb-1">{{ $reservation->user->f_name }} {{ $reservation->user->l_name }}</h3>
                                <div class="text-body">
                                    <i class="tio-email mr-2"></i>{{ $reservation->user->email }}
                                </div>
                                <div class="text-body">
                                    <i class="tio-call-talking mr-2"></i>{{ $reservation->user->phone ?? translate('N/A') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Restaurant Info Card -->
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-header-title">{{ translate('Restaurant Information') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="media align-items-center">
                            <div class="avatar avatar-xl avatar-circle mr-3">
                                <img class="avatar-img" src="{{ $reservation->store->logo_full_url }}"
                                    onerror="this.src='{{ asset('assets/admin/img/160x160/img2.jpg') }}'"
                                    alt="{{ $reservation->store->name }}">
                            </div>
                            <div class="media-body">
                                <h3 class="mb-1">{{ $reservation->store->name }}</h3>
                                <div class="text-body">
                                    <i class="tio-poi mr-2"></i>{{ $reservation->store->address }}
                                </div>
                                <div class="text-body">
                                    <i class="tio-call-talking mr-2"></i>{{ $reservation->store->phone }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Actions Card -->
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-header-title">{{ translate('Actions') }}</h4>
                    </div>
                    <div class="card-body">
                        @if($reservation->status == 'pending')
                            <form action="{{ route('admin.sabores.reservations.update-status', $reservation->id) }}"
                                method="POST">
                                @csrf
                                <input type="hidden" name="status" value="confirmed">
                                <button type="submit" class="btn btn-success btn-block mb-2">
                                    <i class="tio-checkmark-circle"></i> {{ translate('Confirm Reservation') }}
                                </button>
                            </form>
                            <form action="{{ route('admin.sabores.reservations.update-status', $reservation->id) }}"
                                method="POST">
                                @csrf
                                <input type="hidden" name="status" value="cancelled">
                                <button type="submit" class="btn btn-danger btn-block">
                                    <i class="tio-clear"></i> {{ translate('Cancel Reservation') }}
                                </button>
                            </form>
                        @elseif($reservation->status == 'confirmed')
                            <form action="{{ route('admin.sabores.reservations.update-status', $reservation->id) }}"
                                method="POST">
                                @csrf
                                <input type="hidden" name="status" value="completed">
                                <button type="submit" class="btn btn-primary btn-block mb-2">
                                    <i class="tio-done"></i> {{ translate('Mark as Completed') }}
                                </button>
                            </form>
                        @else
                            <div class="alert alert-soft-secondary">
                                {{ translate('No actions available for this reservation') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection