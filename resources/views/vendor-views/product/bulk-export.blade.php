@extends('layouts.vendor.app')

@section('title','Exportación masiva de artículos')

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('assets/admin/img/category.png')}}" class="w--20" alt="">
                </span>
                <span>
                    {{'exportar artículos'}}
                </span>
            </h1>
        </div>
        <div class="card mt-2 rest-part">
            <div class="card-body">
                <div class="export-steps">
                    <div class="export-steps-item">
                        <div class="inner">
                            <h5>{{ 'PASO 1' }}</h5>
                            <p>
                                {{ 'Seleccionar tipo de datos' }}
                            </p>
                        </div>
                    </div>
                    <div class="export-steps-item">
                        <div class="inner">
                            <h5>{{ 'PASO 2' }}</h5>
                            <p>
                                {{ 'Seleccionar rango de datos y exportar' }}
                            </p>
                        </div>
                    </div>
                </div>
                <form class="product-form" action="{{route('vendor.item.bulk-export')}}" method="POST"
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
                                <input type="date" name="from_date" class="form-control">
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
                                <input type="date" name="to_date" class="form-control">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="btn--container justify-content-end">
                                <button class="btn btn--reset" type="reset">{{'reiniciar'}}</button>
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
        $('.id_wise').hide();
        $('.date_wise').hide();
        $('#type').on('change', function()
        {
            $('.id_wise').hide();
            $('.date_wise').hide();
            $('.'+$(this).val()).show();
        })
    });
</script>
@endpush
