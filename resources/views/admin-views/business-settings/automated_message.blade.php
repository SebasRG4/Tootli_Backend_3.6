@extends('layouts.admin.app')

@section('title', 'Mensaje automatizado')

@push('css_or_js')
<link rel="stylesheet" href="{{asset('assets/admin/css/owl.min.css')}}">
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title mr-3">
                <span class="page-header-icon">
                    <img src="{{ asset('assets/admin/img/business.png') }}" class="w--26" alt="">
                </span>
                <span>
                    {{ 'configuración de negocios' }}
                </span>
            </h1>
            @include('admin-views.business-settings.partials.nav-menu')
        </div>
        <!-- End Page Header -->

        <div class="card mb-3 mt-0">
            <div class="card-body mb-3">


                <div class="report-card-inner mb-4 mw-100">
                    <form action="{{ route('admin.business-settings.automated_message.store') }}" method="post">
                        @csrf
                        @if ($language)
                        <ul class="nav nav-tabs nav--tabs d-block nav-slider owl-theme owl-carousel mb-4">
                            <li class="nav-item">
                                <a class="nav-link lang_link1 active px-0" href="#"
                                    id="default-link1">{{ 'Por defecto' }}</a>
                            </li>
                            @foreach ($language as $lang)
                                <li class="nav-item">
                                    <a class="nav-link lang_link1 px-0" href="#"
                                        id="{{ $lang }}-link1">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                                </li>
                            @endforeach
                        </ul>
                        @endif
                        <div class="row align-items-end">



                            <div class="col-md-12 lang_form1 default-form1">
                                <label for="reason" class="form-label">{{ 'Mensaje/motivo automatizado' }}
                                    ({{ 'Por defecto' }})

                                    <span class="input-label-secondary text--title" data-toggle="tooltip"
                                    data-placement="right"
                                    data-original-title="{{ 'Debe establecer motivos predefinidos para que los clientes seleccionen. Esto los guiará a la hora de elegir un motivo al informar cualquier problema con su pedido.' }}">
                                        <i class="tio-info-outined"></i>
                                    </span>

                                </label>
                                <input id="reason" type="text" class="form-control h--45px" name="message[]" maxlength="255"
                                    placeholder="{{ 'Ej:Ingrese el mensaje' }}">
                                <input type="hidden" name="lang[]" value="default">
                            </div>

                            @if ($language)
                                @foreach ($language as $lang)
                                    <div class="col-md-12 d-none lang_form1" id="{{ $lang }}-form1">
                                        <label for="reason{{ $lang }}"
                                            class="form-label">{{ 'Mensaje/motivo automatizado' }}
                                            ({{ strtoupper($lang) }})</label>
                                        <input id="reason{{ $lang }}" type="text" class="form-control h--45px"
                                            name="message[]" maxlength="255" placeholder="{{ 'Ej:Ingrese el mensaje' }}">
                                        <input type="hidden" name="lang[]" value="{{ $lang }}">
                                    </div>
                                @endforeach
                            @endif

                        </div>
                        <div class="mt-3 btn--container justify-content-end">
                            <button type="reset" id="reset_btn"
                                class="btn btn--reset">{{ 'reiniciar' }}</button>
                            <button type="submit" class="btn btn--primary">{{ 'Entregar' }}</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>


        <div class="card">

            <div class="card-header border-0">
                <div class="mx-1">
                    <h5 class="form-label mb-2">
                        {{ 'mensaje total' }}
                        <span class="badge badge-soft-dark ml-2" id="itemCount">{{$messages->total()}}</span>
                    </h5>
                </div>
                <div class="search--button-wrapper justify-content-end">
                    <form class="search-form">
                        <!-- Search -->
                        <div class="input-group input--group">
                            <input id="datatableSearch" name="search" value="{{ request()?->search ?? null }}"
                                type="search" class="form-control h--40px"
                                placeholder="{{ 'ej:Buscar por mensaje' }}"
                                aria-label="{{ 'buscar aquí' }}">
                            <button type="submit" class="btn btn--secondary h--40px"><i class="tio-search"></i></button>
                        </div>
                        <!-- End Search -->
                    </form>
                    @if (request()->get('search'))
                        <button type="reset" class="btn btn--primary ml-2 location-reload-to-base"
                            data-url="{{ url()->full() }}">{{ 'reiniciar' }}</button>
                    @endif
                </div>
                <!-- End Row -->
            </div>




            <!-- Table -->
            <div class="card-body p-0">
                <div class="table-responsive datatable-custom">
                    <table id="columnSearchDatatable" class="table table-borderless table-thead-bordered table-align-middle"
                        data-hs-datatables-options='{
                        "isResponsive": false,
                        "isShowPaging": false,
                        "paging":false,
                    }'>
                        <thead class="thead-light">
                            <tr>
                                <th class="border-0">{{ 'SL' }}</th>
                                <th class="border-0">{{ 'mensaje' }}</th>
                                <th class="border-0">{{ 'estado' }}</th>
                                <th class="border-0 text-center">{{ 'acción' }}</th>
                            </tr>
                        </thead>

                        <tbody id="table-div">
                            @foreach ($messages as $key => $message)
                                <tr>
                                    <td>{{ $key + $messages->firstItem() }}</td>

                                    <td>
                                        <span class="d-block font-size-sm text-body" title="{{ $message->message }}">
                                            {{ Str::limit($message->message, 50, '...') }}
                                        </span>
                                    </td>
                                    <td>
                                        <label class="toggle-switch toggle-switch-sm"
                                            for="stocksCheckbox{{ $message->id }}">
                                            <input type="checkbox"
                                                data-url="{{ route('admin.business-settings.automated_message.status', [$message['id'], $message->status ? 0 : 1]) }}"
                                                class="toggle-switch-input redirect-url"
                                                id="stocksCheckbox{{ $message->id }}" {{ $message->status ? 'checked' : '' }}>
                                            <span class="toggle-switch-label">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                    </td>

                                    <td>
                                        <div class="btn--container justify-content-center">
                                            <a class="btn btn-sm btn--primary btn-outline-primary action-btn edit-reason"
                                                title="{{ 'editar' }}" data-toggle="modal"
                                                data-target="#add_update_reason_{{ $message->id }}"><i
                                                    class="tio-edit"></i>
                                            </a>

                                            <a class="btn btn-sm btn--danger btn-outline-danger action-btn form-alert"
                                                href="javascript:" data-id="refund_reason-{{ $message['id'] }}"
                                                data-message="{{ '¿Quieres eliminar este mensaje?' }}"
                                                title="{{ 'borrar' }}">
                                                <i class="tio-delete-outlined"></i>
                                            </a>
                                            <form action="{{ route('admin.business-settings.automated_message.destroy', [$message['id']]) }}"
                                                method="post" id="refund_reason-{{ $message['id'] }}">
                                                @csrf @method('delete')
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <!-- Modal -->
                                <div class="modal fade" id="add_update_reason_{{ $message->id }}" tabindex="-1"
                                    role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="exampleModalLabel">
                                                    {{ 'Actualización automática de mensajes/motivos' }}</label></h5>
                                                <button type="button" class="close" data-dismiss="modal"
                                                    aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <form action="{{ route('admin.business-settings.automated_message.update') }}" method="post">
                                                <div class="modal-body">
                                                    @csrf
                                                    @method('put')

                                                    @php($message = \App\Models\AutomatedMessage::withoutGlobalScope('translate')->with('translations')->find($message->id))
                                                <div class="js-nav-scroller hs-nav-scroller-horizontal mb-4">
                                                    <ul class="nav nav-tabs nav--tabs d-block border-0 nav-slider owl-theme owl-carousel mb-4">
                                                        <li class="nav-item">
                                                            <a class="nav-link update-lang_link add_active active px-0"
                                                                href="#"
                                                                id="default-link">{{ 'Por defecto' }}</a>
                                                        </li>
                                                        @if ($language)
                                                            @foreach ($language as $lang)
                                                                <li class="nav-item">
                                                                    <a class="nav-link update-lang_link px-0" href="#"
                                                                        data-reason-id="{{ $message->id }}"
                                                                        id="{{ $lang }}-link">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                                                                </li>
                                                            @endforeach
                                                        @endif
                                                    </ul>
                                                </div>
                                                    <input type="hidden" name="message_id"
                                                        value="{{ $message->id }}" />

                                                    <div class="form-group mb-3 add_active_2  update-lang_form"
                                                        id="default-form_{{ $message->id }}">
                                                        <label for="reason"
                                                            class="form-label">{{ 'Mensaje/motivo automatizado' }}
                                                            ({{ 'por defecto' }}) </label>
                                                        <input id="reason" class="form-control" name='message[]'
                                                            value="{{ $message?->getRawOriginal('message') }}" maxlength="255"
                                                            type="text">
                                                        <input type="hidden" name="lang1[]" value="default">
                                                    </div>
                                                    @if ($language)
                                                        @forelse($language as $lang)
                                                            <?php
                                                            if ($message?->translations) {
                                                                $translate = [];
                                                                foreach ($message?->translations as $t) {
                                                                    if ($t->locale == $lang && $t->key == 'message') {
                                                                        $translate[$lang]['message'] = $t->value;
                                                                    }
                                                                }
                                                            }
                                                            ?>
                                                            <div class="form-group mb-3 d-none update-lang_form"
                                                                id="{{ $lang }}-langform_{{ $message->id }}">
                                                                <label for="reason{{ $lang }}"
                                                                    class="form-label">{{ 'Mensaje/motivo automatizado' }}
                                                                    ({{ strtoupper($lang) }})</label>
                                                                <input id="reason{{ $lang }}"
                                                                    class="form-control" name='message[]' maxlength="255"
                                                                    value="{{ $translate[$lang]['message'] ?? null }}"
                                                                    type="text">
                                                                <input type="hidden" name="lang1[]"
                                                                    value="{{ $lang }}">
                                                            </div>
                                                        @empty
                                                        @endforelse
                                                    @endif

                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-dismiss="modal">{{ 'Cerca' }}</button>
                                                    <button type="submit"
                                                        class="btn btn-primary">{{ 'Guardar cambios' }}</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </tbody>
                    </table>
                    @if (count($messages) === 0)
                        <div class="empty--data">
                            <img src="{{ asset('assets/admin/svg/illustrations/sorry.svg') }}" alt="public">
                            <h5>
                                {{ 'no se encontraron datos' }}
                            </h5>
                        </div>
                    @endif
                </div>
                <div class="card-footer pt-0 border-0">
                    <div class="page-area px-4 pb-3">
                        <div class="d-flex align-items-center justify-content-end">
                            <div>
                                {!! $messages->links() !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Table -->

    </div>
    </div>


@endsection
@push('script_2')
    <script src="{{ asset('assets/admin/js/view-pages/business-settings-refund-reasons-page.js') }}"></script>
    <script src="{{ asset('assets/admin/js/owl.min.js') }}"></script>
    <script>
        $('.nav-slider').owlCarousel({
            margin: 30,
            loop: false,
            autoWidth: true,
            items: 4
        })
    </script>
@endpush
