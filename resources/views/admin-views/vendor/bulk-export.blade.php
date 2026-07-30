@extends('layouts.admin.app')

@section('title','exportación a granel para restaurantes')

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('assets/admin/img/resturant.png')}}" class="w--20" alt="">
                </span>
                <span>
                    {{'tiendas de exportación'}}
                </span>
            </h1>
        </div>
        <div class="card rest-part">
            <div class="card-body p-2">
                <div class="export-steps-2">
                    <div class="row g-4">
                        <div class="col-sm-6 col-lg-4">
                            <div class="export-steps-item-2 h-100">
                                <div class="top">
                                    <div>
                                        <h3 class="fs-20">{{'Paso 1'}}</h3>
                                        <div>
                                            {{'Seleccionar tipo de datos'}}
                                        </div>
                                    </div>
                                    <img src="{{asset('assets/admin/img/bulk-export-1.png')}}" alt="">
                                </div>
                                <h4>{{ 'Instrucción' }}</h4>
                                <ul class="m-0 pl-4">
                                    <li>
                                       {{ 'Seleccione el tipo de datos en el orden en que desea que se ordenen sus datos durante la descarga.' }}
                                    </li>


                                </ul>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-4">
                            <div class="export-steps-item-2 h-100">
                                <div class="top">
                                    <div>
                                        <h3 class="fs-20">{{'Paso 2'}}</h3>
                                        <div>
                                            {{'Seleccione Rango de datos por fecha o ID y exporte'}}
                                        </div>
                                    </div>
                                    <img src="{{asset('assets/admin/img/bulk-export-2.png')}}" alt="">
                                </div>
                                <h4>{{ 'Instrucción' }}</h4>
                                <ul class="m-0 pl-4">

                                    <li>
                                        {{ 'El archivo se descargará en formato .xls.' }}
                                    </li>
                                    <li>
                                        {{ 'Haga clic en restablecer si desea borrar sus cambios y desea descargar los datos ordenados de forma predeterminada' }}
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <form class="product-form px-3 pb-3" action="{{route('admin.store.bulk-export')}}" method="POST"
                        enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlSelect1">{{'tipo'}}<span
                                        class="input-label-secondary"></span></label>
                                <select name="type" id="type" data-placeholder="{{'seleccione tipo'}}" class="form-control" required title="Select Type">
                                    <option value="all">{{'todos los datos'}}</option>
                                    <option value="date_wise">{{'fecha sabia'}}</option>
                                    <option value="id_wise">{{'identificación sabia'}}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group id_wise">
                                <label class="input-label" for="exampleFormControlSelect1">{{'identificación de inicio'}}<span
                                        class="input-label-secondary"></span></label>
                                <input type="number" name="start_id" class="form-control">
                            </div>
                            <div class="form-group date_wise">
                                <label class="input-label" for="exampleFormControlSelect1">{{'desde la fecha'}}<span
                                        class="input-label-secondary"></span></label>
                                <input type="date" name="from_date" id="date_from" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group id_wise">
                                <label class="input-label" for="exampleFormControlSelect1">{{'identificación final'}}<span
                                        class="input-label-secondary"></span></label>
                                <input type="number" name="end_id" class="form-control">
                            </div>
                            <div class="form-group date_wise">
                                <label class="input-label text-capitalize" for="exampleFormControlSelect1">{{'hasta la fecha'}}<span
                                        class="input-label-secondary"></span></label>
                                <input type="date" name="to_date" id="date_to" class="form-control">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="btn--container justify-content-end">
                                <button class="btn btn--reset" id="reset-btn" type="reset">{{'claro'}}</button>
                                <button class="btn btn--primary" type="submit">{{'exportar'}}</button>
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
    $(document).on('ready', function (){
        $('#date_from').attr('max',(new Date()).toISOString().split('T')[0]);
        $('#date_to').attr('max',(new Date()).toISOString().split('T')[0]);
        $('.id_wise').hide();
        $('.date_wise').hide();
        $('#type').on('change', function()
        {
            $('.id_wise').hide();
            $('.date_wise').hide();
            $('.'+$(this).val()).show();
        })
        $('#reset-btn').on('click', function()
        {
            $('.id_wise').hide();
            $('.date_wise').hide();
        })
    });
</script>
@endpush
