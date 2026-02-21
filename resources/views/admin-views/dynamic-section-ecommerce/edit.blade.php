@extends('layouts.admin.app')

@section('title', translate('messages.edit_section'))

@push('css_or_js')
    <link href="{{asset('assets/admin/css/select2.min.css')}}" rel="stylesheet" />
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('assets/admin/img/banner.png')}}" class="w--26" alt="">
                </span>
                <span>{{translate('messages.edit_section')}}</span>
            </h1>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.dynamic-section-ecommerce.update', $section->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <!-- Image -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">{{translate('messages.banner_image')}}</label>
                                <label class="__upload-img aspect-4-1 m-auto d-block">
                                    <div class="img">
                                        <img class="onerror-image" id="viewer-edit"
                                            src="{{ $section->image_full_url ?? asset('assets/admin/img/upload-placeholder.png') }}"
                                            data-onerror-image="{{asset('assets/admin/img/upload-placeholder.png')}}"
                                            alt="">
                                    </div>
                                    <input type="file" name="image" accept="image/*" hidden
                                        onchange="document.getElementById('viewer-edit').src = window.URL.createObjectURL(this.files[0])">
                                </label>
                                <p class="text-center mt-2 text-muted">{{translate('messages.recommended_ratio_4_1')}}</p>
                            </div>
                        </div>

                        <!-- Stores -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">{{translate('messages.select_stores')}}</label>
                                <select name="stores[]" class="form-control select2-stores" multiple
                                    data-placeholder="{{translate('messages.select_stores')}}">
                                    @foreach($stores as $store)
                                        <option value="{{$store->id}}"
                                            {{ in_array($store->id, $selectedStores) ? 'selected' : '' }}>
                                            {{$store->name}}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group mt-3">
                                <label class="toggle-switch toggle-switch-sm">
                                    <input type="checkbox" class="toggle-switch-input" name="status"
                                        {{ $section->status ? 'checked' : '' }}>
                                    <span class="toggle-switch-label">
                                        <span class="toggle-switch-indicator"></span>
                                    </span>
                                    <span class="ml-2">{{translate('messages.active')}}</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="btn--container justify-content-end mt-3">
                        <a href="{{ route('admin.dynamic-section-ecommerce.index') }}"
                            class="btn btn--reset">{{translate('messages.cancel')}}</a>
                        <button type="submit" class="btn btn--primary">{{translate('messages.update')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
    <script src="{{asset('assets/admin/js/select2.min.js')}}"></script>
    <script>
        $(document).ready(function () {
            $('.select2-stores').select2({
                placeholder: "{{translate('messages.select_stores')}}",
                allowClear: true
            });
        });
    </script>
@endpush
