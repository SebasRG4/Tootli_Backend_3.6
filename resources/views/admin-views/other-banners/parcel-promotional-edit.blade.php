@extends('layouts.admin.app')

@section('title','bandera')

@section('content')
<div class="content container-fluid">
    <div class="page-header">
        <h1 class="page-header-title">
            <span class="page-header-icon">
                <img src="{{asset('assets/admin/img/3rd-party.png')}}" class="w--26" alt="">
            </span>
            <span>
                {{'Configuración de otro contenido promocional'}}
            </span>
        </h1>
    </div>
    <div class="mb-20 mt-2">
        <div class="js-nav-scroller hs-nav-scroller-horizontal">
            @include('admin-views.other-banners.partial.parcel-links')
        </div>
    </div>
    <div class="tab-content">
        <div class="tab-pane fade show active">
            <div class="row g-3">
                <div class="col-lg-12 mb-3 mb-lg-2">
                    <div class="card h-100">
                        <form action="{{ route('admin.promotional-banner.update',[$banner['id']]) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="text" name="key" value="promotional_banner"  hidden>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-12 d-flex justify-content-between">
                                            <span class="d-flex g-1">
                                                <img src="{{asset('assets/admin/img/other-banner.png')}}" class="h-85" alt="">
                                                <h3 class="form-label d-block mb-2">
                                                    {{'Banner promocional Editar'}}
                                                </h3>
                                            </span>
                                        </div>
                                        <div class="col-12">
                                            <label class="__upload-img aspect-4-1 m-auto d-block">
                                                <div class="img">
                                                    <img class="onerror-image"

                                                        src="{{ $banner->value_full_url ?? asset('assets/admin/img/upload-placeholder.png')}}" data-onerror-image="{{asset('assets/admin/img/upload-placeholder.png')}}" alt="">
                                                </div>
                                                    <input type="file" name="image"  hidden>
                                            </label>
                                            <div class="text-center mt-5">
                                                <h3 class="form-label d-block mt-2">
                                                {{'Proporción de imagen de banner 4:1'}}
                                            </h3>
                                            <p>{{'formato de imagen: jpg, png, jpeg | tamaño máximo: 2 MB'}}</p>

                                            </div>
                                        </div>
                                    </div>
                                    <div class="btn--container justify-content-end mt-20">
                                        <button type="submit" class="btn btn--primary mb-2">{{'Actualizar'}}</button>
                                    </div>
                                </div>
                            </form>
                        </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('script_2')
    <script src="{{asset('assets/admin')}}/js/view-pages/other-banners.js"></script>
        <script>
            $('#reset_btn').click(function(){
                $('#viewer').attr('src','{{asset('assets/admin/img/upload-placeholder.png')}}');
            })
        </script>
@endpush

