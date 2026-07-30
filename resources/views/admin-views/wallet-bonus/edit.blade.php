@extends('layouts.admin.app')

@section('title','bono de edición')

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('assets/admin/img/edit.png')}}" class="w--26" alt="">
                </span>
                <span>
                    {{'actualización de bonificación de billetera'}}
                </span>
            </h1>
        </div>
        <!-- End Page Header -->
        <div class="card">
            <div class="card-body">
                <form action="{{route('admin.users.customer.wallet.bonus.update',[$bonus['id']])}}" method="post">
                    @csrf
                    <div class="row g-3">
                        <div class="col-12">
                            @if($language)
                                        <ul class="nav nav-tabs mb-4">
                                            <li class="nav-item">
                                                <a class="nav-link lang_link active"
                                                href="#"
                                                id="default-link">{{'por defecto'}}</a>
                                            </li>
                                            @foreach ($language as $lang)
                                                <li class="nav-item">
                                                    <a class="nav-link lang_link"
                                                        href="#"
                                                        id="{{ $lang }}-link">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                                                </li>
                                            @endforeach
                                        </ul>
                                        <div class="lang_form" id="default-form">
                                            <div class="row">
                                                <div class="col-6">
                                                    <div class="form-group">
                                                        <label class="input-label" for="default_title">{{'Título de bonificación'}} ({{'por defecto'}}) <span class="form-label-secondary text-danger"
                                                            data-toggle="tooltip" data-placement="right"
                                                            data-original-title="{{ 'Requerido.'}}"> *
                                                            </span></label>
                                                        <input type="text" name="title[]" id="default_title" class="form-control" placeholder="{{'título'}}" value="{{$bonus?->getRawOriginal('title')}}"  >
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="form-group">
                                                        <label class="input-label" for="default_description">{{'Breve descripción'}} ({{'por defecto'}}) <span class="form-label-secondary text-danger"
                                                            data-toggle="tooltip" data-placement="right"
                                                            data-original-title="{{ 'Requerido.'}}"> *
                                                            </span></label>
                                                        <input type="text" name="description[]" id="default_description" class="form-control" placeholder="{{'descripción'}}" value="{{$bonus?->getRawOriginal('description')}}"  >
                                                    </div>
                                                </div>
                                            </div>
                                            <input type="hidden" name="lang[]" value="default">
                                        </div>
                                        @foreach($language as $lang)
                                            <?php
                                                if(count($bonus['translations'])){
                                                    $translate = [];
                                                    foreach($bonus['translations'] as $t)
                                                    {
                                                        if($t->locale == $lang && $t->key=="title"){
                                                            $translate[$lang]['title'] = $t->value;
                                                        }
                                                        if($t->locale == $lang && $t->key=="description"){
                                                            $translate[$lang]['description'] = $t->value;
                                                        }
                                                    }
                                                }
                                            ?>
                                            <div class="d-none lang_form" id="{{$lang}}-form">
                                                <div class="row">
                                                    <div class="col-6">
                                                        <div class="form-group">
                                                            <label class="input-label" for="{{$lang}}_title">{{'Título de bonificación'}} ({{strtoupper($lang)}})</label>
                                                            <input type="text" name="title[]" id="{{$lang}}_title" class="form-control" placeholder="{{'título'}}" value="{{$translate[$lang]['title']??''}}"  >
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="form-group">
                                                            <label class="input-label" for="{{$lang}}_description">{{'Breve descripción'}} ({{strtoupper($lang)}})</label>
                                                            <input type="text" name="description[]" id="{{$lang}}_description" class="form-control" placeholder="{{'descripción'}}" value="{{$translate[$lang]['description']??''}}"  >
                                                        </div>
                                                    </div>
                                                </div>
                                                <input type="hidden" name="lang[]" value="{{$lang}}">
                                            </div>
                                        @endforeach
                                    @else
                                    <div id="default-form">
                                        <div class="form-group">
                                            <label class="input-label" for="exampleFormControlInput1">{{'Título de bonificación'}} ({{ 'por defecto' }})</label>
                                            <input type="text" name="title[]" class="form-control" placeholder="{{'título'}}" value="{{$bonus['title']}}" maxlength="100">
                                        </div>
                                        <input type="hidden" name="lang[]" value="default">
                                    </div>
                                    @endif
                        </div>
                        <div class="col-md-4 col-lg-4 col-sm-6">
                            <div class="form-group m-0">
                                <label class="input-label" for="bonus_type">{{'Tipo de bonificación'}} <span class="form-label-secondary text-danger"
                                    data-toggle="tooltip" data-placement="right"
                                    data-original-title="{{ 'Requerido.'}}"> *
                                    </span></label>
                                <select name="bonus_type" id="bonus_type" class="form-control">
                                    <option value="amount" {{$bonus['bonus_type']=='amount'?'selected':''}}>{{'cantidad'}} ({{ \App\CentralLogics\Helpers::currency_symbol() }})
                                    </option>
                                    <option value="percentage" {{$bonus['bonus_type']=='percentage'?'selected':''}}>
                                        {{'porcentaje'}} (%)
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-4 col-sm-6">
                            <div class="form-group m-0">
                                <label class="input-label" for="bonus_amount">{{'Monto del bono'}}
                                    <span    class="{{$bonus['bonus_type']=='amount'? '':'d-none'}}" id='cuttency_symbol'>({{ \App\CentralLogics\Helpers::currency_symbol() }})
                                    </span>
                                    <span   class="{{$bonus['bonus_type']=='percentage'? '':'d-none'}}" id="percentage">(%)</span>

                                    <span
                                    class="input-label-secondary text--title" data-toggle="tooltip"
                                    data-placement="right"
                                    data-original-title="{{ 'Establezca el monto/porcentaje de bonificación que recibirá un cliente después de agregar dinero a su billetera.' }}">
                                    <i class="tio-info-outined"></i>
                                </span>
                                <span class="form-label-secondary text-danger"
                                data-toggle="tooltip" data-placement="right"
                                data-original-title="{{ 'Requerido.'}}"> *
                                </span>
                                </label>
                                <input type="number" id="bonus_amount" min="1" max="{{ $bonus['bonus_type'] == 'percentage'? '100' : '999999999999.99' }}" step="0.01" value="{{$bonus['bonus_amount']}}"
                                       name="bonus_amount" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-4 col-sm-6">
                            <div class="form-group m-0">
                                <label class="input-label" for="minimum_add_amount">{{'Monto mínimo de dinero agregado'}}
                                    ({{ \App\CentralLogics\Helpers::currency_symbol() }})
                                            <span
                                            class="input-label-secondary text--title" data-toggle="tooltip"
                                            data-placement="right"
                                            data-original-title="{{ 'Establezca la cantidad mínima de dinero agregado para que un cliente sea elegible para el bono.' }}">
                                            <i class="tio-info-outined"></i>
                                        </span>
                                        <span class="form-label-secondary text-danger"
                                        data-toggle="tooltip" data-placement="right"
                                        data-original-title="{{ 'Requerido.'}}"> *
                                        </span>
                                </label>
                                <input type="number" id="minimum_add_amount" min="1" max="999999999999.99" step="0.01" value="{{$bonus['minimum_add_amount']}}"
                                       name="minimum_add_amount" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-4 col-sm-6">
                            <div class="form-group m-0">
                                <label class="input-label" for="exampleFormControlInput1">
                                    {{'Bonificación máxima'}} ({{ \App\CentralLogics\Helpers::currency_symbol() }})
                                    <span
                                    class="input-label-secondary text--title" data-toggle="tooltip"
                                    data-placement="right"
                                    data-original-title="{{ 'Establezca el monto máximo de bonificación que un cliente puede recibir por agregar dinero a su billetera.' }}">
                                    <i class="tio-info-outined"></i>
                                </span>

                                </label>
                                <input type="number" min="0" max="999999999999.99" step="0.01" value="{{$bonus['maximum_bonus_amount']}}" name="maximum_bonus_amount" id="maximum_bonus_amount" class="form-control" {{$bonus['bonus_type']=='amount'?'readonly="readonly"':''}}>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-4 col-sm-6">
                            <div class="form-group m-0">
                                <label class="input-label" for="date_from">{{'fecha de inicio'}} <span class="form-label-secondary text-danger"
                                                        data-toggle="tooltip" data-placement="right"
                                                        data-original-title="{{ 'Requerido.'}}"> *
                                                        </span></label>
                                <input type="date" name="start_date" class="form-control" id="date_from" placeholder="{{'seleccionar fecha'}}" max="{{date("Y-m-d",strtotime($bonus["end_date"]))}}" value="{{date('Y-m-d',strtotime($bonus['start_date']))}}"                     data-hs-flatpickr-options='{
                                    "dateFormat": "Y-m-d"
                                  }'>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-4 col-sm-6">
                            <div class="form-group m-0">
                                <label class="input-label" for="date_to">{{'fecha de caducidad'}} <span class="form-label-secondary text-danger"
                                                        data-toggle="tooltip" data-placement="right"
                                                        data-original-title="{{ 'Requerido.'}}"> *
                                                        </span></label>
                                <input type="date" name="end_date" class="form-control" placeholder="{{'seleccionar fecha'}}" min="{{date("Y-m-d",strtotime($bonus["start_date"]))}}" id="date_to" value="{{date('Y-m-d',strtotime($bonus['end_date']))}}"
                                       data-hs-flatpickr-options='{
                                     "dateFormat": "Y-m-d"
                                   }'>
                            </div>
                        </div>
                    </div>
                    <div class="btn--container justify-content-end mt-4">
                        <button type="reset" id="reset_btn" class="btn btn--reset">{{'reiniciar'}}</button>
                        <button type="submit" class="btn btn--primary">{{'actualizar'}}</button>
                    </div>
                </form>
            </div>
            <!-- End Table -->
        </div>
    </div>

@endsection

@push('script_2')
    <script src="{{asset('assets/admin')}}/js/view-pages/wallet-bonus-edit.js"></script>
    <script>
        "use strict";
        $(document).on('ready', function () {
            $('#date_from').attr('min',(new Date()).toISOString().split('T')[0]);
            $('#date_from').attr('max','{{date("Y-m-d",strtotime($bonus["end_date"]))}}');
            $('#date_to').attr('min','{{date("Y-m-d",strtotime($bonus["start_date"]))}}');
        });
    </script>
@endpush
