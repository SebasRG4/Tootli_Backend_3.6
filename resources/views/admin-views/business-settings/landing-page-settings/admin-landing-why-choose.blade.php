@extends('layouts.admin.app')

@section('title','página de inicio del administrador')

@section('content')
<div class="content container-fluid">
    <div class="page-header pb-0">
        <div class="d-flex flex-wrap justify-content-between">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('assets/admin/img/landing.png')}}" class="w--20" alt="">
                </span>
                <span>
                    {{ 'páginas de inicio de administración' }}
                </span>
            </h1>
            <div class="text--primary-2 py-1 d-flex flex-wrap align-items-center" type="button" data-toggle="modal" data-target="#how-it-works">
                <strong class="mr-2">{{'¡Mira cómo funciona!'}}</strong>
                <div>
                    <i class="tio-info-outined"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="mb-20 mt-2">
        <div class="js-nav-scroller hs-nav-scroller-horizontal">
            @include('admin-views.business-settings.landing-page-settings.top-menu-links.admin-landing-page-links')
        </div>
    </div>
    @php($why_choose_title=\App\Models\DataSetting::withoutGlobalScope('translate')->where('type','admin_landing_page')->where('key','why_choose_title')->first())
    @php($language=\App\Models\BusinessSetting::where('key','language')->first())
    @php($language = $language->value ?? null)
    @php($defaultLang = str_replace('_', '-', app()->getLocale()))
    @if($language)
        <ul class="nav nav-tabs mb-4 border-0">
            <li class="nav-item">
                <a class="nav-link lang_link active"
                href="#"
                id="default-link">{{'por defecto'}}</a>
            </li>
            @foreach (json_decode($language) as $lang)
                <li class="nav-item">
                    <a class="nav-link lang_link"
                        href="#"
                        id="{{ $lang }}-link">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                </li>
            @endforeach
        </ul>
    @endif
    <div class="tab-content">
        <div class="tab-pane fade show active">
            <form action="{{ route('admin.business-settings.admin-landing-page-settings', 'why-choose-title') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="card mb-3">
                    <div class="card-body">
                        @if ($language)
                            <div class="row g-3 lang_form" id="default-form">
                                <div class="col-sm-12">
                                    <label for="why_choose_title" class="form-label">{{'Título'}} ({{ 'por defecto' }})<span
                                                        class="form-label-secondary" data-toggle="tooltip"
                                                        data-placement="right"
                                                        data-original-title="{{ 'Escribe el título dentro de 80 caracteres.' }}">
                                                        <img src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                            alt="">
                                                    </span><span class="form-label-secondary text-danger"
                                                                 data-toggle="tooltip" data-placement="right"
                                                                 data-original-title="{{ 'Requerido.'}}"> *
                                                </span></label>
                                                <input required id="why_choose_title" type="text" maxlength="80" name="why_choose_title[]" class="form-control" value="{{$why_choose_title?->getRawOriginal('value')}}" placeholder="{{'título aquí...'}}">
                                </div>
                            </div>
                            <input type="hidden" name="lang[]" value="default">
                                @foreach(json_decode($language) as $lang)
                                <?php
                                if(isset($why_choose_title->translations)&&count($why_choose_title->translations)){
                                        $why_choose_title_translate = [];
                                        foreach($why_choose_title->translations as $t)
                                        {
                                            if($t->locale == $lang && $t->key=='why_choose_title'){
                                                $why_choose_title_translate[$lang]['value'] = $t->value;
                                            }
                                        }

                                    }
                                    ?>
                                    <div class="row g-3 d-none lang_form" id="{{$lang}}-form">
                                        <div class="col-sm-12">
                                            <label for="why_choose_title{{$lang}}" class="form-label">{{'Título'}} ({{strtoupper($lang)}})<span
                                                class="form-label-secondary" data-toggle="tooltip"
                                                data-placement="right"
                                                data-original-title="{{ 'Escribe el título dentro de 80 caracteres.' }}">
                                                <img src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                    alt="">
                                            </span></label>
                                                <input id="why_choose_title{{$lang}}" type="text" maxlength="80" name="why_choose_title[]" class="form-control" value="{{ $why_choose_title_translate[$lang]['value']?? '' }}" placeholder="{{'título aquí...'}}">
                                        </div>
                                    </div>
                                    <input type="hidden" name="lang[]" value="{{$lang}}">
                                @endforeach
                            @else
                                <div class="row g-3">
                                    <div class="col-sm-12">
                                        <label for="why_choose_title" class="form-label">{{'Título'}}<span
                                                        class="form-label-secondary" data-toggle="tooltip"
                                                        data-placement="right"
                                                        data-original-title="{{ 'Escribe el título dentro de 80 caracteres.' }}">
                                                        <img src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                            alt="">
                                                    </span></label>
                                                <input id="why_choose_title" type="text" maxlength="80" name="why_choose_title[]" class="form-control" placeholder="{{'título aquí...'}}">
                                    </div>
                                </div>
                                <input type="hidden" name="lang[]" value="default">
                            @endif
                        <div class="btn--container justify-content-end mt-20">
                            <button type="reset" class="btn btn--reset mb-2">{{'Reiniciar'}}</button>
                            <button type="submit"   class="btn btn--primary mb-2">{{'Ahorrar'}}</button>
                        </div>
                    </div>
                </div>
            </form>
                <form action="{{ route('admin.business-settings.admin-landing-page-settings', 'special-criteria-list') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                <h5 class="card-title mb-3 mt-3">
                    <span class="card-header-icon mr-2"><i class="tio-settings-outlined"></i></span> <span>{{'Sección de lista de criterios especiales'}}</span>
                </h5>
                <div class="card mb-3">
                    <div class="card-body">

                            <div class="row g-3">
                                @if ($language)
                                <div class="col-sm-6 lang_form default-form">
                                    <label for="title" class="form-label">{{'Título'}} ({{ 'por defecto' }})<span
                                                        class="form-label-secondary" data-toggle="tooltip"
                                                        data-placement="right"
                                                        data-original-title="{{ 'Escribe el título dentro de 40 caracteres.' }}">
                                                        <img src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                            alt="">
                                                    </span>
                                        <span class="form-label-secondary text-danger"
                                              data-toggle="tooltip" data-placement="right"
                                              data-original-title="{{ 'Requerido.'}}"> *
                                                </span></label>
                                                <input required id="title" type="text" maxlength="40" name="title[]" class="form-control" placeholder="{{'título aquí...'}}">
                                </div>
                                <input type="hidden" name="lang[]" value="default">
                                    @foreach(json_decode($language) as $lang)
                                    <div class="col-sm-6 d-none lang_form" id="{{$lang}}-form1">
                                        <label for="title{{$lang}}" class="form-label">{{'Título'}} ({{strtoupper($lang)}})<span
                                                        class="form-label-secondary" data-toggle="tooltip"
                                                        data-placement="right"
                                                        data-original-title="{{ 'Escribe el título dentro de 40 caracteres.' }}">
                                                        <img src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                            alt="">
                                                    </span></label>
                                                <input  id="title{{$lang}}" type="text" maxlength="40" name="title[]" class="form-control" placeholder="{{'título aquí...'}}">
                                    </div>
                                        <input type="hidden" name="lang[]" value="{{$lang}}">
                                    @endforeach
                                @else
                                <div class="col-sm-6">
                                    <label for="title" class="form-label">{{'Título'}}<span
                                                        class="form-label-secondary" data-toggle="tooltip"
                                                        data-placement="right"
                                                        data-original-title="{{ 'Escribe el título dentro de 40 caracteres.' }}">
                                                        <img src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                            alt="">
                                                    </span></label>
                                                <input id="title" type="text" maxlength="40" name="title[]" class="form-control" placeholder="{{'título aquí...'}}">
                                </div>
                                    <input type="hidden" name="lang[]" value="default">
                                @endif
                                <div class="col-sm-6">
                                    <div>

                                        <label class="form-label mb-3">{{'Icono/imagen de criterios'}}<span class="form-label-secondary" data-toggle="tooltip" data-placement="right" data-original-title="{{ translate(IMAGE_FORMAT.' ' . 'Less Than 2MB') }}">
                                            <img src="{{asset('assets/admin/img/info-circle.svg')}}" alt="">
                                        </span>
                                            <span class="form-label-secondary text-danger"
                                                  data-toggle="tooltip" data-placement="right"
                                                  data-original-title="{{ 'Requerido.'}}"> *
                                                </span><div class="fs-12 opacity-70">
                                                {{ translate(IMAGE_FORMAT.' ' . 'Less Than 2MB') }}
                                            </div></label>
                                    </div>
                                    <label class="upload-img-3 m-0">
                                        <div class="img">
                                            <img src="{{asset('assets/admin/img/aspect-1.png')}}" alt="" class="img__aspect-1 min-w-187px max-w-187px">
                                        </div>
                                          <input class="upload-file__input single_file_input" accept="{{IMAGE_EXTENSION}}" type="file"  name="image" hidden>
                                    </label>
                                </div>
                            </div>
                            <div class="btn--container justify-content-end mt-20">
                                <button type="reset" class="btn btn--reset mb-2">{{'Reiniciar'}}</button>
                                <button type="submit"   class="btn btn--primary mb-2">{{'Agregar'}}</button>
                            </div>
                        </div>
                        </div>
                    </form>
                    @php($criterias=\App\Models\AdminSpecialCriteria::all())
                    <div class="card-body p-0">
                        <!-- Table -->
                        <div class="table-responsive datatable-custom">
                            <table id="columnSearchDatatable"
                                    class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                                    data-hs-datatables-options='{
                                        "order": [],
                                        "orderCellsTop": true,
                                        "paging":false

                                    }'>
                                <thead class="thead-light">
                                <tr>
                                    <th class="border-0">{{'SL'}}</th>
                                    <th class="border-0">{{'Título'}}</th>
                                    <th class="border-0">{{'Imagen'}}</th>
                                    <th class="border-0">{{'Estado'}}</th>
                                    <th class="text-center border-0">{{'acción'}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                    @foreach($criterias as $key=>$criteria)
                                    <tr>
                                        <td>{{ $key+1 }}</td>
                                        <td>
                                            <div class="text--title">
                                            {{ $criteria->title }}
                                            </div>
                                        </td>
                                        <td>
                                            <img  src="{{ $criteria->image_full_url ?? asset('assets/admin/img/upload-3.png') }}"
                                            data-onerror-image="{{asset('assets/admin/img/upload-3.png')}}" class="__size-105 onerror-image" alt="">
                                        </td>
                                        <td>
                                            <label class="toggle-switch toggle-switch-sm">
                                                <input type="checkbox"
                                                      id="status-{{$criteria->id}}"
                                                       data-id="status-{{$criteria->id}}"
                                                       data-type="status"
                                                       data-image-on="{{ asset('assets/admin/img/modal/this-criteria-on.png') }}"
                                                       data-image-off="{{ asset('assets/admin/img/modal/this-criteria-off.png') }}"
                                                       data-title-on="{{ '¿Quieres habilitar esta función?' }}"
                                                       data-title-off="{{ '¿Quieres desactivar esta función?' }}"
                                                       data-text-on="<p>{{ 'Estará disponible en la página de inicio.' }}</p>"
                                                       data-text-off="<p>{{ 'Estará oculto en la página de destino.' }}</p>"
                                                       class="status toggle-switch-input dynamic-checkbox"

                                                    {{$criteria->status?'checked':''}}>
                                                <span class="toggle-switch-label">
                                                    <span class="toggle-switch-indicator"></span>
                                                </span>
                                            </label>
                                            <form action="{{route('admin.business-settings.criteria-status',[$criteria->id,$criteria->status?0:1])}}" method="get" id="status-{{$criteria->id}}_form">
                                            </form>
                                        </td>

                                        <td>
                                            <div class="btn--container justify-content-center">
                                                <a class="btn action-btn btn--primary btn-outline-primary" href="{{route('admin.business-settings.criteria-edit',[$criteria['id']])}}">
                                                    <i class="tio-edit"></i>
                                                </a>
                                                <a class="btn action-btn btn--danger btn-outline-danger form-alert" href="javascript:"
                                                   data-id="criteria-{{$criteria['id']}}"
                                                   data-message="{{ '¿Quieres eliminar este criterio?' }}"
                                                   title="{{'eliminar criterios'}}"><i class="tio-delete-outlined"></i>
                                                </a>
                                                <form action="{{route('admin.business-settings.criteria-delete',[$criteria['id']])}}" method="post" id="criteria-{{$criteria['id']}}">
                                                    @csrf @method('delete')
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>

                        </div>
                        <!-- End Table -->
                    </div>
                    @if(count($criterias) === 0)
                    <div class="empty--data">
                        <img src="{{asset('assets/admin/svg/illustrations/sorry.svg')}}" alt="public">
                        <h5>
                            {{'no se encontraron datos'}}
                        </h5>
                    </div>
                    @endif
                </div>



        </div>
    </div>

    <!-- How it Works -->
    @include('admin-views.business-settings.landing-page-settings.partial.how-it-work')
@endsection

