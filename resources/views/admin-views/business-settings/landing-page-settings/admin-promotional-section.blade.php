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
            <form action="{{ route('admin.business-settings.admin-landing-page-settings', 'promotional-section') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="card mb-3">
                    <div class="card-body">
                        @if ($language)
                            <div class="row g-3 lang_form" id="default-form">
                                <div class="col-sm-6">
                                    <label for="title" class="form-label">{{'Título'}} ({{ 'por defecto' }})<span class="form-label-secondary" data-toggle="tooltip" data-placement="right" data-original-title="{{ 'Escribe el título dentro de 20 caracteres.' }}">
                                                <img src="{{asset('assets/admin/img/info-circle.svg')}}" alt="">
                                            </span>
                                        <span class="form-label-secondary text-danger"
                                              data-toggle="tooltip" data-placement="right"
                                              data-original-title="{{ 'Requerido.'}}"> *
                                                </span></label>
                                        <input id="title" required type="text"  maxlength="20" name="title[]" class="form-control" placeholder="{{'título aquí...'}}">
                                </div>
                                <div class="col-sm-6">
                                    <label for="sub_title" class="form-label">{{'Subtítulo'}} ({{ 'por defecto' }})<span class="form-label-secondary" data-toggle="tooltip" data-placement="right" data-original-title="{{ 'Escribe el título dentro de 80 caracteres.' }}">
                                                <img src="{{asset('assets/admin/img/info-circle.svg')}}" alt="">
                                            </span>
                                        <span class="form-label-secondary text-danger"
                                              data-toggle="tooltip" data-placement="right"
                                              data-original-title="{{ 'Requerido.'}}"> *
                                                </span></label>
                                        <input id="sub_title" required type="text"  maxlength="80" name="sub_title[]" class="form-control" placeholder="{{'subtítulo aquí...'}}">
                                </div>
                            </div>
                            <input type="hidden" name="lang[]" value="default">
                                @foreach(json_decode($language) as $lang)
                                    <div class="row g-3 d-none lang_form" id="{{$lang}}-form">
                                        <div class="col-sm-6">
                                            <label for="title{{$lang}}" class="form-label">{{'Título'}} ({{strtoupper($lang)}})<span class="form-label-secondary" data-toggle="tooltip" data-placement="right" data-original-title="{{ 'Escribe el título dentro de 20 caracteres.' }}">
                                                <img src="{{asset('assets/admin/img/info-circle.svg')}}" alt="">
                                            </span></label>
                                        <input id="title{{$lang}}" type="text"  maxlength="20" name="title[]" class="form-control" placeholder="{{'título aquí...'}}">
                                        </div>
                                        <div class="col-sm-6">
                                            <label for="sub_title{{$lang}}" class="form-label">{{'Subtítulo'}} ({{strtoupper($lang)}})<span class="form-label-secondary" data-toggle="tooltip" data-placement="right" data-original-title="{{ 'Escribe el título dentro de 80 caracteres.' }}">
                                                <img src="{{asset('assets/admin/img/info-circle.svg')}}" alt="">
                                            </span></label>
                                        <input type="text"  id="sub_title{{$lang}}" maxlength="80" name="sub_title[]" class="form-control" placeholder="{{'subtítulo aquí...'}}">
                                        </div>
                                    </div>
                                    <input type="hidden" name="lang[]" value="{{$lang}}">
                                @endforeach
                            @else
                                <div class="row g-3">
                                    <div class="col-sm-6">
                                        <label  for="title" class="form-label">{{'Título'}}</label>
                                        <input id="title" type="text" name="title[]" class="form-control" placeholder="{{'título aquí...'}}">
                                    </div>
                                    <div class="col-sm-6">
                                        <label for="sub_title" class="form-label">{{'Subtítulo'}}</label>
                                        <input  id="sub_title" type="text" name="sub_title[]" class="form-control" placeholder="{{'subtítulo aquí...'}}">
                                    </div>
                                </div>
                                <input type="hidden" name="lang[]" value="default">
                            @endif
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label class="form-label d-block mb-3">
                                    {{'Bandera'}}  <span class="text--primary">{{'(tamaño: 3:1)'}}</span>
                                    <span class="form-label-secondary text-danger"
                                          data-toggle="tooltip" data-placement="right"
                                          data-original-title="{{ 'Requerido.'}}"> *
                                                </span>
                                    <div class="fs-12 opacity-70">
                                        {{ translate(IMAGE_FORMAT.' ' . 'Less Than 2MB') }}
                                    </div>
                                </label>

                                <label class="upload-img-3 m-0 d-block">
                                    <div class="img">
                                        <img src="{{asset('assets/admin/img/upload-4.png')}}" data-onerror-image="{{asset('assets/admin/img/upload-4.png')}}" class="vertical-img mw-100 vertical onerror-image" alt="">
                                    </div>
                                        <input accept="{{IMAGE_EXTENSION}}" class="upload-file__input single_file_input"  type="file" name="image" hidden>
                                </label>
                            </div>
                        </div>
                        <div class="btn--container justify-content-end mt-20">
                            <button type="reset" class="btn btn--reset mb-2">{{'Reiniciar'}}</button>
                            <button type="submit" class="btn btn--primary mb-2">{{'Agregar'}}</button>
                        </div>
                    </div>
                </div>
            </form>
            @php($banners=\App\Models\AdminPromotionalBanner::all())
            <div class="card">
                <div class="card-header py-2">
                    <div class="search--button-wrapper">
                        <h5 class="card-title">{{'Lista de banners promocionales'}}
                            {{-- <span class="badge badge-secondary ml-1">5</span>  --}}
                        </h5>
                    </div>
                </div>
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
                                <th class="border-0">{{'Subtítulo'}}</th>
                                <th class="border-0">{{'Imagen'}}</th>
                                <th class="border-0">{{'Estado'}}</th>
                                <th class="text-center border-0">{{'acción'}}</th>
                            </tr>
                            </thead>
                            <tbody>
                                @foreach($banners as $key=>$banner)
                                <tr>
                                    <td>{{ $key+1 }}</td>
                                    <td>
                                        <div class="text--title">
                                        {{ $banner->title }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="d-block font-size-sm text-body">
                                            {{ $banner->sub_title }}
                                         </span>
                                    </td>
                                    <td>
                                        <img
                                        src="{{ $banner->image_full_url ?? asset('assets/admin/img/upload-3.png') }}"
                                        data-onerror-image="{{asset('assets/admin/img/upload-3.png')}}" class="__size-105 onerror-image" alt="">
                                    </td>
                                    <td>
                                        <label class="toggle-switch toggle-switch-sm">
                                            <input type="checkbox"
                                                   data-id="status-{{$banner->id}}"
                                                   data-type="status"
                                                   data-image-on="{{ asset('assets/admin/img/modal/promotional-on.png') }}"
                                                   data-image-off="{{ asset('assets/admin/img/modal/promotional-off.png') }}"
                                                   data-title-on="{{ 'Activando la sección de banner promocional' }}"
                                                   data-title-off="{{ 'Desactivando la sección de banners promocionales' }}"
                                                   data-text-on="<p>{{ 'Se habilitará el banner promocional. Podrás ver la actividad promocional.' }}</p>"
                                                   data-text-off="<p>{{ 'El banner promocional estará deshabilitado. No podrás ver la actividad promocional.' }}</p>"
                                                   class="status toggle-switch-input dynamic-checkbox"
                                                   id="status-{{$banner->id}}" {{$banner->status?'checked':''}}>
                                            <span class="toggle-switch-label">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                        <form action="{{route('admin.business-settings.promotional-status',[$banner->id,$banner->status?0:1])}}" method="get" id="status-{{$banner->id}}_form">
                                        </form>
                                    </td>

                                    <td>
                                        <div class="btn--container justify-content-center">
                                            <a class="btn action-btn btn--primary btn-outline-primary" href="{{route('admin.business-settings.promotional-edit',[$banner['id']])}}">
                                                <i class="tio-edit"></i>
                                            </a>
                                            <a class="btn action-btn btn--danger btn-outline-danger form-alert" href="javascript:"
                                               data-id="banner-{{$banner['id']}}"
                                               data-message="{{ '¿Quieres eliminar este banner?' }}"
                                               title="{{'eliminar banner'}}"><i class="tio-delete-outlined"></i>
                                            </a>
                                            <form action="{{route('admin.business-settings.promotional-delete',[$banner['id']])}}" method="post" id="banner-{{$banner['id']}}">
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
                @if(count($banners) === 0)
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
</div>
    <!-- How it Works -->
    @include('admin-views.business-settings.landing-page-settings.partial.how-it-work')
@endsection
