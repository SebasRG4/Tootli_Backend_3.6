@extends('layouts.admin.app')

@section('title', translate('Edit Dineout Category'))

@push('css_or_js')
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <i class="tio-edit"></i>
                </span>
                <span>
                    {{ translate('Edit Dineout Category') }}
                </span>
            </h1>
        </div>
        <!-- End Page Header -->

        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.sabores.dineout-categories.update', $category->id) }}" method="post"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="input-label" for="name">{{ translate('messages.name') }}</label>
                                <input type="text" name="name" class="form-control" value="{{ $category->name }}"
                                    placeholder="{{ translate('Ex: Fine Dining') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="input-label" for="image">{{ translate('Icon (Emoji)') }}</label>
                                <input type="text" name="image" class="form-control" value="{{ $category->image }}"
                                    placeholder="{{ translate('Ex: 🍽️') }}" required>
                                <small
                                    class="text-muted">{{ translate('Copy and paste an emoji here to use as an icon') }}</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="input-label" for="position">{{ translate('Position') }}</label>
                                <input type="number" name="position" class="form-control" value="{{ $category->position }}"
                                    placeholder="{{ translate('Ex: 1') }}">
                            </div>
                        </div>
                    </div>

                    <div class="btn--container justify-content-end mt-3">
                        <button type="reset" class="btn btn--reset">{{ translate('messages.reset') }}</button>
                        <button type="submit" class="btn btn--primary">{{ translate('messages.update') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection