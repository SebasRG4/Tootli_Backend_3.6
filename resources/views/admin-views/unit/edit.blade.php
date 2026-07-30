@extends('layouts.admin.app')

@section('title','Unidad de actualización')

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('assets/admin/img/edit.png')}}" class="w--20" alt="">
                </span>
                <span>
                    {{'actualización de la unidad'}}
                </span>
            </h1>
        </div>
        <!-- End Page Header -->
        <div class="card">
            <div class="card-body">
                <form action="{{route('admin.unit.update',[$unit['id']])}}" method="post">
                    @csrf
                    @method('PUT')
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
                                <div class="form-group">
                                    <label class="input-label" for="default_title">{{'nombre'}} ({{'por defecto'}}) <span class="form-label-secondary text-danger"
                                        data-toggle="tooltip" data-placement="right"
                                        data-original-title="{{ 'Requerido.'}}"> *
                                        </span>
                                    </label>
                                    <input type="text" name="unit[]" id="default_title" class="form-control" placeholder="{{'nombre de la unidad'}}" value="{{$unit?->getRawOriginal('unit')}}" maxlength="191"  >
                                </div>
                                <input type="hidden" name="lang[]" value="default">
                            </div>
                            @foreach($language as $lang)
                                <?php
                                    if(count($unit['translations'])){
                                        $translate = [];
                                        foreach($unit['translations'] as $t)
                                        {
                                            if($t->locale == $lang && $t->key=="unit"){
                                                $translate[$lang]['unit'] = $t->value;
                                            }
                                        }
                                    }
                                ?>
                                <div class="d-none lang_form" id="{{$lang}}-form">
                                    <div class="form-group">
                                        <label class="input-label" for="{{$lang}}_title">{{'nombre'}} ({{strtoupper($lang)}})</label>
                                        <input type="text" name="unit[]" id="{{$lang}}_title" class="form-control" placeholder="{{'nombre de la unidad'}}" value="{{$translate[$lang]['unit']??''}}" maxlength="191"  >
                                    </div>
                                    <input type="hidden" name="lang[]" value="{{$lang}}">
                                </div>
                            @endforeach
                        @else
                        <div id="default-form">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{'nombre'}} ({{ 'por defecto' }})</label>
                                <input type="text" name="unit[]" class="form-control" placeholder="{{'nombre de la unidad'}}" value="{{$unit['unit']}}" maxlength="191" required>
                            </div>
                            <input type="hidden" name="lang[]" value="default">
                        </div>
                        @endif
                    <div class="btn--container justify-content-end">
                        <button type="reset" class="btn btn--reset">{{'reiniciar'}}</button>
                        <button type="submit" class="btn btn--primary">{{'actualizar'}}</button>
                    </div>
                </form>
            </div>
            <!-- End Table -->
        </div>
    </div>

@endsection

@push('script_2')

@endpush
