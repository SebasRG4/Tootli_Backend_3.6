@extends('layouts.admin.app')

@section('title', translate('Driver Verification Details'))

@push('css_or_js')

@endpush

@section('content')
<div class="content container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-sm mb-2 mb-sm-0">
                <h1 class="page-header-title">{{ translate('Verification Details') }}</h1>
            </div>
            <div class="col-sm-auto">
                <a class="btn btn-icon btn-sm btn-ghost-secondary rounded-circle mr-1"
                    href="{{ route('admin.taxi.drivers.verification.index') }}" title="{{ translate('Back') }}">
                    <i class="tio-arrow-backward"></i>
                </a>
            </div>
        </div>
    </div>
    <!-- End Page Header -->

    <div class="row">
        <!-- Driver Info -->
        <div class="col-lg-4 mb-3 mb-lg-0">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title">{{ translate('Driver Information') }}</h5>
                </div>
                <div class="card-body text-center">
                    <img class="avatar avatar-xl mb-3 onerror-image"
                        src="{{ \App\CentralLogics\Helpers::get_full_url('delivery-man', $driver['image'] ?? '', $driver->storage->first()?->value ?? 'public', 'profile') }}"
                        data-onerror-image="{{ asset('assets/admin/img/160x160/img1.jpg') }}" alt="Profile">
                    <h5 class="mb-1">{{ $driver->f_name }} {{ $driver->l_name }}</h5>
                    <p class="text-muted">{{ $driver->email }}</p>
                    <p class="text-muted">{{ $driver->phone }}</p>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <strong>{{ translate('Status') }}:</strong>
                        @if ($driver->taxi_is_verified)
                            <span class="badge badge-success">{{ translate('Verified') }}</span>
                        @else
                            <span class="badge badge-warning">{{ translate('Pending') }}</span>
                        @endif
                    </div>
                    <div class="mt-4">
                        @if(!$driver->taxi_is_verified)
                            <form action="{{ route('admin.taxi.drivers.verification.update', $driver->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="status" value="1">
                                <button type="submit"
                                    class="btn btn-success btn-block">{{ translate('Verify Driver') }}</button>
                            </form>
                        @else
                            <form action="{{ route('admin.taxi.drivers.verification.update', $driver->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="status" value="0">
                                <button type="submit"
                                    class="btn btn-danger btn-block">{{ translate('Revoke Verification') }}</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Documents -->
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title">{{ translate('Submitted Documents') }}</h5>
                </div>
                <div class="card-body">
                    @php($docs = $driver->taxi_documents ?? [])
                    <div class="row">
                        <!-- Selfie -->
                        <div class="col-md-6 mb-4">
                            <h6>{{ translate('Selfie with ID') }}</h6>
                            <div class="card p-2 custom-file-upload-section">
                                @if(isset($docs['selfie_image']))
                                    <a href="{{ \App\CentralLogics\Helpers::get_full_url('delivery-man', $docs['selfie_image'], $driver->storage->first()?->value ?? 'public', 'taxi') }}"
                                        data-lightbox="docs">
                                        <img class="img-fluid rounded onerror-image"
                                            src="{{ \App\CentralLogics\Helpers::get_full_url('delivery-man', $docs['selfie_image'], $driver->storage->first()?->value ?? 'public', 'taxi') }}"
                                            data-onerror-image="{{ asset('assets/admin/img/400x400/img2.jpg') }}"
                                            alt="Selfie">
                                    </a>
                                @else
                                    <div class="text-center p-4 text-muted border rounded border-dashed">
                                        {{ translate('Not Uploaded') }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Identity Cards -->
                        <div class="col-md-12 mb-2">
                            <h6 class="border-bottom pb-2">{{ translate('Official Identification') }}</h6>
                        </div>
                        <!-- Existing Identity Images from DeliveryMan Model -->
                        @php($identity_images = $driver->identity_image ?? [])
                        @foreach($identity_images as $index => $img)
                            <div class="col-md-6 mb-4">
                                <h6>{{ translate('Identity Side') }} {{ $index + 1 }}</h6>
                                <div class="card p-2">
                                    <a href="{{ \App\CentralLogics\Helpers::get_full_url('delivery-man', $img, $driver->storage->first()?->value ?? 'public', 'identity') }}"
                                        data-lightbox="docs">
                                        <img class="img-fluid rounded onerror-image"
                                            src="{{ \App\CentralLogics\Helpers::get_full_url('delivery-man', $img, $driver->storage->first()?->value ?? 'public', 'identity') }}"
                                            data-onerror-image="{{ asset('assets/admin/img/400x400/img2.jpg') }}"
                                            alt="Identity">
                                    </a>
                                </div>
                            </div>
                        @endforeach

                        <div class="col-md-12 mb-2 mt-2">
                            <h6 class="border-bottom pb-2">{{ translate('Circulation Card') }}</h6>
                        </div>
                        <div class="col-md-6 mb-4">
                            <h6>{{ translate('Front') }}</h6>
                            <div class="card p-2">
                                @if(isset($docs['circulation_card_front']))
                                    <a href="{{ \App\CentralLogics\Helpers::get_full_url('delivery-man', $docs['circulation_card_front'], $driver->storage->first()?->value ?? 'public', 'taxi') }}"
                                        data-lightbox="docs">
                                        <img class="img-fluid rounded onerror-image"
                                            src="{{ \App\CentralLogics\Helpers::get_full_url('delivery-man', $docs['circulation_card_front'], $driver->storage->first()?->value ?? 'public', 'taxi') }}"
                                            data-onerror-image="{{ asset('assets/admin/img/400x400/img2.jpg') }}"
                                            alt="Circulation Front">
                                    </a>
                                @else
                                    <div class="text-center p-4 text-muted border rounded border-dashed">
                                        {{ translate('Not Uploaded') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <h6>{{ translate('Back') }}</h6>
                            <div class="card p-2">
                                @if(isset($docs['circulation_card_back']))
                                    <a href="{{ \App\CentralLogics\Helpers::get_full_url('delivery-man', $docs['circulation_card_back'], $driver->storage->first()?->value ?? 'public', 'taxi') }}"
                                        data-lightbox="docs">
                                        <img class="img-fluid rounded onerror-image"
                                            src="{{ \App\CentralLogics\Helpers::get_full_url('delivery-man', $docs['circulation_card_back'], $driver->storage->first()?->value ?? 'public', 'taxi') }}"
                                            data-onerror-image="{{ asset('assets/admin/img/400x400/img2.jpg') }}"
                                            alt="Circulation Back">
                                    </a>
                                @else
                                    <div class="text-center p-4 text-muted border rounded border-dashed">
                                        {{ translate('Not Uploaded') }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-12 mb-2 mt-2">
                            <h6 class="border-bottom pb-2">{{ translate('License Plates') }}</h6>
                        </div>
                        <div class="col-md-6 mb-4">
                            <h6>{{ translate('Front') }}</h6>
                            <div class="card p-2">
                                @if(isset($docs['plate_image_front']))
                                    <a href="{{ \App\CentralLogics\Helpers::get_full_url('delivery-man', $docs['plate_image_front'], $driver->storage->first()?->value ?? 'public', 'taxi') }}"
                                        data-lightbox="docs">
                                        <img class="img-fluid rounded onerror-image"
                                            src="{{ \App\CentralLogics\Helpers::get_full_url('delivery-man', $docs['plate_image_front'], $driver->storage->first()?->value ?? 'public', 'taxi') }}"
                                            data-onerror-image="{{ asset('assets/admin/img/400x400/img2.jpg') }}"
                                            alt="Plate Front">
                                    </a>
                                @else
                                    <div class="text-center p-4 text-muted border rounded border-dashed">
                                        {{ translate('Not Uploaded') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <h6>{{ translate('Back') }}</h6>
                            <div class="card p-2">
                                @if(isset($docs['plate_image_back']))
                                    <a href="{{ \App\CentralLogics\Helpers::get_full_url('delivery-man', $docs['plate_image_back'], $driver->storage->first()?->value ?? 'public', 'taxi') }}"
                                        data-lightbox="docs">
                                        <img class="img-fluid rounded onerror-image"
                                            src="{{ \App\CentralLogics\Helpers::get_full_url('delivery-man', $docs['plate_image_back'], $driver->storage->first()?->value ?? 'public', 'taxi') }}"
                                            data-onerror-image="{{ asset('assets/admin/img/400x400/img2.jpg') }}"
                                            alt="Plate Back">
                                    </a>
                                @else
                                    <div class="text-center p-4 text-muted border rounded border-dashed">
                                        {{ translate('Not Uploaded') }}
                                    </div>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection