@extends('layouts.admin.app')

@section('title', translate('messages.edit_dynamic_section'))

@push('css_or_js')
<link href="{{asset('assets/admin/css/select2.min.css')}}" rel="stylesheet" />
@endpush

@section('content')
<div class="content container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <h1 class="page-header-title">
            <span class="page-header-icon">
                <img src="{{asset('assets/admin/img/banner.png')}}" class="w--26" alt="">
            </span>
            <span>
                {{translate('messages.edit_dynamic_section')}}
            </span>
        </h1>
    </div>
    <!-- End Page Header -->

    <!-- Edit Form -->
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.dynamic-section.update', $section->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label" for="title">{{translate('messages.title')}} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" 
                                value="{{$section->title}}" placeholder="{{translate('messages.enter_title')}}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label" for="subtitle">{{translate('messages.subtitle')}}</label>
                            <input type="text" class="form-control" id="subtitle" name="subtitle" 
                                value="{{$section->subtitle}}" placeholder="{{translate('messages.enter_subtitle')}}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">{{translate('messages.background_image')}}</label>
                            <label class="__upload-img aspect-4-1 m-auto d-block">
                                <div class="img">
                                    <img class="onerror-image" id="viewer"
                                        src="{{$section->background_image_full_url ?? asset('assets/admin/img/upload-placeholder.png')}}"
                                        data-onerror-image="{{asset('assets/admin/img/upload-placeholder.png')}}"
                                        alt="">
                                </div>
                                <input type="file" name="background_image" accept="image/*" hidden 
                                    onchange="document.getElementById('viewer').src = window.URL.createObjectURL(this.files[0])">
                            </label>
                            <p class="text-center mt-2">{{translate('messages.image_ratio_4:1')}}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">{{translate('messages.select_items')}}</label>
                            <select name="items[]" class="form-control select2-items" multiple data-placeholder="{{translate('messages.select_items')}}">
                                @foreach($items as $item)
                                    <option value="{{$item->id}}" {{in_array($item->id, $selectedItems) ? 'selected' : ''}}>{{$item->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="btn--container justify-content-end">
                    <a href="{{route('admin.dynamic-section.add-new')}}" class="btn btn--reset">{{translate('messages.back')}}</a>
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
    $(document).ready(function() {
        $('.select2-items').select2({
            placeholder: "{{translate('messages.select_items')}}",
            allowClear: true
        });
    });
</script>
@endpush
