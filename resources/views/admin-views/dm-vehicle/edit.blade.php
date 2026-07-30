@extends('layouts.admin.app')

@section('title','actualizar categoría de vehículo')

@push('css_or_js')

@endpush

@section('content')
<div class="content container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-sm mb-2 mb-sm-0">
                <h1 class="page-header-title">
                    <div class="page-header-icon"><i class="tio-add-circle-outlined"></i></div>
                    {{'actualizar categoría de vehículo'}}
                </h1>
            </div>
        </div>
    </div>
    <!-- End Page Header -->
    <div class="card">
        <div class="card-body">
            <form method="post" enctype="multipart/form-data" id="vehicle-form">
                @csrf
                @if($language)
                <ul class="nav nav-tabs mb-4">
                    <li class="nav-item">
                        <a class="nav-link lang_link active" href="#"
                            id="default-link">{{'por defecto'}}</a>
                    </li>
                    @foreach ($language as $lang)
                    <li class="nav-item">
                        <a class="nav-link lang_link" href="#" id="{{ $lang }}-link">{{
                            \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                    </li>
                    @endforeach
                </ul>
                @endif
                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-6">
                                @if ($language)
                                <div class="form-group lang_form" id="default-form">
                                    <label class="input-label text-capitalize"
                                        for="title">{{'tipo de vehículo'}} ({{
                                        'por defecto' }}) <span class="form-label-secondary text-danger"
                                            data-toggle="tooltip" data-placement="right"
                                            data-original-title="{{ 'Requerido.'}}"> *
                                        </span>
                                    </label>
                                    <input type="text" name="type[]" class="form-control h--45px"
                                        placeholder="{{'ej: bicicleta'}}" maxlength="191"
                                        value="{{$vehicle?->getRawOriginal('type')}}" required>
                                </div>
                                <input type="hidden" name="lang[]" value="default">
                                @foreach($language as $lang)
                                <?php
                                            if(count($vehicle['translations'])){
                                                $translate = [];
                                                foreach($vehicle['translations'] as $t)
                                                {
                                                    if($t->locale == $lang && $t->key=="type"){
                                                        $translate[$lang]['type'] = $t->value;
                                                    }
                                                }
                                            }
                                        ?>
                                <div class="form-group d-none lang_form" id="{{$lang}}-form">
                                    <label class="input-label text-capitalize"
                                        for="title">{{'tipo de vehículo'}}
                                        ({{strtoupper($lang)}})</label>
                                    <input type="text" name="type[]" class="form-control h--45px"
                                        placeholder="{{'ej: bicicleta'}}" maxlength="191"
                                        value="{{$translate[$lang]['type']??''}}">
                                </div>
                                <input type="hidden" name="lang[]" value="{{$lang}}">
                                @endforeach
                                @else
                                <div class="form-group">
                                    <label class="input-label text-capitalize"
                                        for="title">{{'tipo de vehículo'}}</label>
                                    <input type="text" name="type" class="form-control h--45px"
                                        placeholder="{{'ej: bicicleta'}}" required maxlength="191"
                                        value="{{$vehicle['type']}}">
                                </div>
                                <input type="hidden" name="lang[]" value="default">
                                @endif
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="input-label text-capitalize"
                                        for="title">{{'cargos extra'}} ({{
                                        \App\CentralLogics\Helpers::currency_symbol() }}) <span
                                            class="input-label-secondary" data-toggle="tooltip" data-placement="right"
                                            data-original-title="{{'A este importe se le añadirá el cargo de envío.'}}"><img
                                                src="{{asset('assets/admin/img/info-circle.svg')}}"
                                                alt="public/img"></span><span class="form-label-secondary text-danger"
                                            data-toggle="tooltip" data-placement="right"
                                            data-original-title="{{ 'Requerido.'}}"> *
                                        </span>
                                    </label>
                                    <input type="number" step="0.001" id="extra_charges" class="form-control h--45px"
                                        value="{{ $vehicle->extra_charges }}" min="0" required name="extra_charges">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="input-label text-capitalize"
                                        for="title">{{'área de cobertura inicial'}} ({{
                                        'kilómetros' }})<span class="input-label-secondary"
                                            data-toggle="tooltip" data-placement="right"
                                            data-original-title="{{'El área de cobertura inicial representa el lugar donde se realizan las entregas.'}}"><img
                                                src="{{asset('assets/admin/img/info-circle.svg')}}"
                                                alt="public/img"></span><span class="form-label-secondary text-danger"
                                            data-toggle="tooltip" data-placement="right"
                                            data-original-title="{{ 'Requerido.'}}"> *
                                        </span>
                                    </label>
                                    <input type="number" step="0.001" id="starting_coverage_area"
                                        class="form-control h--45px" value="{{ $vehicle->starting_coverage_area }}"
                                        min="0" required name="starting_coverage_area">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="input-label text-capitalize"
                                        for="title">{{'área de cobertura máxima'}} ({{
                                        'kilómetros' }})<span class="input-label-secondary"
                                            data-toggle="tooltip" data-placement="right"
                                            data-original-title="{{'el área de cobertura máxima representa la extensión más lejana o amplia hasta la cual se pueden realizar entregas'}}"><img
                                                src="{{asset('assets/admin/img/info-circle.svg')}}"
                                                alt="public/img"></span><span class="form-label-secondary text-danger"
                                            data-toggle="tooltip" data-placement="right"
                                            data-original-title="{{ 'Requerido.'}}"> *
                                        </span>
                                    </label>
                                    <input type="number" step="0.001" id="maximum_coverage_area"
                                        class="form-control h--45px" value="{{ $vehicle->maximum_coverage_area }}"
                                        min="0" required name="maximum_coverage_area">
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="btn--container justify-content-end">
                    <button type="reset" id="reset_btn" class="btn btn--reset">{{'reiniciar'}}</button>
                    <button type="submit" class="btn btn--primary">{{'entregar'}}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('script_2')
<script src="{{asset('assets/admin')}}/js/view-pages/dm-vehichle.js"></script>
<script>
    "use strict";
        $('#vehicle-form').on('submit', function (e) {
            e.preventDefault();
            let formData = new FormData(this);
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: '{{route('admin.users.delivery-man.vehicle.update',$vehicle->id)}}',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                success: function (data) {
                    if (data.errors) {
                        for (let i = 0; i < data.errors.length; i++) {
                            toastr.error(data.errors[i].message, {
                                CloseButton: true,
                                ProgressBar: true
                            });
                        }
                    } else {
                        toastr.success('{{ 'Categoría de vehículo actualizada' }}', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                        setTimeout(function () {
                            location.href = '{{route('admin.users.delivery-man.vehicle.list')}}';
                        }, 1000);
                    }
                }
            });
        });

        $('#reset_btn').click(function(){
            $('#choice_item').val(null).trigger('change');
            $('#viewer').attr('src','{{asset('assets/admin/img/900x400/img1.jpg')}}');
        })
</script>
@endpush
