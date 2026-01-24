@extends('layouts.admin.app')

@section('title', translate('Edit Restaurant'))

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">
                        <span class="page-header-icon"><i class="tio-edit"></i></span>
                        <span>{{ translate('Edit Restaurant') }} - {{ $restaurant->name }}</span>
                    </h1>
                </div>
                <div class="col-sm-auto">
                    <a class="btn btn-icon btn-sm btn-ghost-secondary rounded-circle mr-1" 
                       href="{{ route('admin.sabores.restaurants') }}">
                        <i class="tio-arrow-backward"></i>
                    </a>
                </div>
            </div>
        </div>

        <form action="{{ route('admin.sabores.restaurants.update', $restaurant->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-header-title">{{ translate('Sabores de la Ciudad Settings') }}</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Average Ticket -->
                                <div class="col-md-6 mb-3">
                                    <label class="input-label" for="average_ticket">
                                        {{ translate('Average Ticket') }} ($)
                                        <span class="input-label-secondary" title="{{ translate('Average cost per person') }}">
                                            <i class="tio-info-outined"></i>
                                        </span>
                                    </label>
                                    <input type="number" step="0.01" min="0" name="average_ticket" 
                                           class="form-control" id="average_ticket" 
                                           value="{{ old('average_ticket', $restaurant->average_ticket) }}"
                                           placeholder="{{ translate('e.g., 25.00') }}">
                                </div>

                                <!-- Accepts Reservations -->
                                <div class="col-md-6 mb-3">
                                    <label class="input-label d-block">{{ translate('Accepts Reservations') }}</label>
                                    <label class="toggle-switch toggle-switch-sm" for="accepts_reservations">
                                        <input type="checkbox" class="toggle-switch-input" 
                                               id="accepts_reservations" name="accepts_reservations" 
                                               {{ $restaurant->accepts_reservations ? 'checked' : '' }}>
                                        <span class="toggle-switch-label">
                                            <span class="toggle-switch-indicator"></span>
                                        </span>
                                    </label>
                                </div>

                                <!-- Infrastructure Images -->
                                <div class="col-12">
                                    <label class="input-label">
                                        {{ translate('Infrastructure Images') }}
                                        <span class="input-label-secondary" title="{{ translate('Upload photos of restaurant interior, exterior, etc.') }}">
                                            <i class="tio-info-outined"></i>
                                        </span>
                                    </label>
                                    
                                    <!-- Current Images -->
                                    @if($restaurant->infrastructure_images && count($restaurant->infrastructure_images) > 0)
                                        <div class="row mb-3">
                                            @foreach($restaurant->infrastructure_images_full_url as $image)
                                                <div class="col-md-3 mb-2">
                                                    <img src="{{ $image }}" class="img-fluid rounded" alt="Infrastructure">
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    <!-- Upload New Images -->
                                    <div class="custom-file">
                                        <input type="file" name="infrastructure_images[]" class="custom-file-input" 
                                               id="infrastructure_images" accept="image/*" multiple>
                                        <label class="custom-file-label" for="infrastructure_images">
                                            {{ translate('Choose files') }}
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">
                                        {{ translate('You can select multiple images. Max 2MB per image.') }}
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="tio-save"></i> {{ translate('Save Changes') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('script_2')
<script>
    // Update file input label with selected files count
    document.getElementById('infrastructure_images').addEventListener('change', function(e) {
        const fileCount = e.target.files.length;
        const label = document.querySelector('label[for="infrastructure_images"]');
        if (fileCount > 0) {
            label.textContent = fileCount + ' {{ translate("file(s) selected") }}';
        }
    });
</script>
@endpush
