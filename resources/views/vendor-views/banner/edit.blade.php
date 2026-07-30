@extends('layouts.vendor.app')

@section('title','Actualizar banner')

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('assets/admin/img/edit.png')}}" class="w--26" alt="">
                </span>
                <span>
                    {{'banner de actualización'}}
                </span>
            </h1>
        </div>
        <!-- End Page Header -->
        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <form action="{{route('vendor.banner.update', [$banner->id])}}" method="POST" enctype="multipart/form-data" class="custom-validation">
                    @csrf
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12 d-flex justify-content-between">
                                    <h3 class="form-label d-block mb-2">
                                        {{'Subir banner'}}
                                    </h3>

                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group error-wrapper">

                                        <label for="title" class="form-label">{{'título'}}</label>
                                        <input id="title" type="text" name="title" class="form-control" value="{{ $banner->title }}" placeholder="{{'título aquí...'}}" required>
                                    </div>
                                    <div class="form-group error-wrapper">

                                        <label for="default_link" class="form-label">{{'URL de redirección/enlace'}}</label>
                                        <input id="default_link" type="url" name="default_link" class="form-control" value="{{ $banner->default_link }}" placeholder="{{'Ingrese la URL'}}">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <label class="upload-img-3 m-0 d-block error-wrapper">
                                        <div class="img">
                                            <img src="{{$banner['image_full_url']}}"
                                            id="viewer"
                                                 data-onerror-image="{{asset('assets/admin/img/upload-4.png')}}"
                                                  class="vertical-img mw-100 vertical onerror-image" alt="">
                                        </div>
                                            <input type="file" name="image"  hidden>
                                    </label>
                                    <h3 class="form-label d-block mt-2">
                                        {{'Proporción de imagen de banner 3:1'}}
                                    </h3>
                                    <p>{{'formato de imagen: jpg, png, jpeg | tamaño máximo: 2 MB'}}</p>
                                </div>
                                <div class="col-sm-6">
                                </div>
                            </div>
                            <div class="btn--container justify-content-end mt-20">
                                <button type="reset" id="reset_btn" class="btn btn--reset">{{'Reiniciar'}}</button>
                                <button type="submit" class="btn btn--primary">{{'Actualizar'}}</button>
                            </div>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>

@endsection

@push('script_2')
        <script>
            "use strict";
            $('#reset_btn').click(function(){
                $('#viewer').attr('src','{{asset('storage/app/public/banner')}}/{{$banner['image']}}');
            })
        </script>

@endpush
