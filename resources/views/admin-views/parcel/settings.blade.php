@extends('layouts.admin.app')

@section('title','configuración de paquete')

 @section('parcel_settings')
 active
 @endsection
@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('assets/admin/img/parcel.png')}}" class="w--26" alt="">
                </span>
                <span>
                    {{'configuración de paquete'}}
                </span>
            </h1>
        </div>
        <!-- End Page Header -->

        <div class="card">
            <div class="card-body">
                <form action="{{route('admin.parcel.update.settings')}}" method="post"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 col-lg-4">
                            <div class="form-group">
                                <label  class="input-label text-capitalize">{{'costo de envío por km'}}  ({{ \App\CentralLogics\Helpers::currency_symbol() }})</label>
                                <input type="number" min="0" step=".01" placeholder="{{'costo de envío por km'}}" class="form-control" name="parcel_per_km_shipping_charge"
                                    value="{{$parcelPerKmShippingCharge??''}}">
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <div class="form-group">
                                <label class="input-label text-capitalize">{{'cargo mínimo de envío'}} ({{ \App\CentralLogics\Helpers::currency_symbol() }})</label>
                                <input type="number" min="0" step=".01" placeholder="{{'cargo mínimo de envío'}}" class="form-control" name="parcel_minimum_shipping_charge"
                                    value="{{$parcelMinimumShippingCharge??''}}">
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4">
                            <div class="form-group">
                                <label class="input-label text-capitalize">{{'comisión de repartidor'}} (%)</label>
                                <input type="number" min="0" step=".01" placeholder="{{'comisión de repartidor'}}" class="form-control" name="parcel_commission_dm" max="100" value="{{$parcelCommissionDm??''}}">
                            </div>
                        </div>
                    </div>
                    <div class="btn--container justify-content-end">
                        <button type="reset" class="btn btn--reset">{{'reiniciar'}}</button>
                        <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}"  class="btn btn--primary call-demo">{{'entregar'}}</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-12 pt-sm-3">
            <div class="report-card-inner mb-4 pt-3 mw-100">
                <form action="{{route('admin.parcel.instruction')}}" method="post">
                    @csrf
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-md-0 mb-3">
                        <div class="mx-1">
                            <h5 class="form-label mb-0">
                                {{'Agregar una instrucción de entrega'}}
                            </h5>
                        </div>
                    </div>

                    @if($language)
                        <ul class="nav nav-tabs nav--tabs mt-3 mb-3 ">
                            <li class="nav-item">
                                <a class="nav-link lang_link1 active"
                                   href="#"
                                   id="default-link1">{{ 'Por defecto' }}</a>
                            </li>
                            @foreach ($language as $lang)
                                <li class="nav-item">
                                    <a class="nav-link lang_link1"
                                       href="#"
                                       id="{{ $lang }}-link1">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                    <div class="row align-items-end">



                        <div class="col-md-10 lang_form1 default-form1">
                            <label class="form-label">{{'Instrucción'}} ({{ 'Por defecto' }})<span class="form-label-secondary" data-toggle="tooltip" data-placement="right" data-original-title="{{ 'Escriba la instrucción dentro de 191 caracteres.' }}">
                                                    <img src="{{asset('assets/admin/img/info-circle.svg')}}" alt="">
                                                </span></label>
                            <input type="text" class="form-control h--45px" maxlength="191" name="instruction[]"
                                   placeholder="{{ 'Ej: el paquete contiene un documento' }}">
                            <input type="hidden" name="lang[]" value="default">
                        </div>

                        @if ($language)
                            @foreach($language as $lang)
                                <div class="col-md-10 d-none lang_form1" id="{{$lang}}-form1">
                                    <label class="form-label">{{'Instrucción'}} ({{strtoupper($lang)}})<span class="form-label-secondary" data-toggle="tooltip" data-placement="right" data-original-title="{{ 'Escriba la instrucción dentro de 191 caracteres.' }}">
                                                    <img src="{{asset('assets/admin/img/info-circle.svg')}}" alt="">
                                                </span></label>
                                    <input type="text" class="form-control h--45px" maxlength="191" name="instruction[]"
                                           placeholder="{{ 'Ej: el paquete contiene un documento' }}">
                                    <input type="hidden" name="lang[]" value="{{$lang}}">
                                </div>
                            @endforeach
                        @endif


                        <div class="col-md-auto">
                            <button type="submit" class="btn btn--primary h--45px btn-block">{{'Añadir ahora'}}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="card">
            <div class="card-body mb-3">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-md-0 mb-3">
                    <div class="mx-1">
                        <h5 class="form-label mb-5">
                            {{'Lista de instrucciones de entrega'}}
                        </h5>
                    </div>
                </div>




                <!-- Table -->
                <div class="card-body p-0">
                    <div class="table-responsive datatable-custom">
                        <table id="columnSearchDatatable"
                               class="table table-borderless table-thead-bordered table-align-middle" data-hs-datatables-options='{
                        "isResponsive": false,
                        "isShowPaging": false,
                        "paging":false,
                    }'>
                            <thead class="thead-light">
                            <tr>
                                <th class="border-0">{{ 'SL' }}</th>
                                <th class="border-0">{{'Instrucción'}}</th>
                                <th class="border-0">{{'estado'}}</th>
                                <th class="border-0 text-center">{{'acción'}}</th>
                            </tr>
                            </thead>

                            <tbody id="table-div">
                            @foreach($instructions as $key=>$instruction)
                                <tr>
                                    <td>{{$key+$instructions->firstItem()}}</td>

                                    <td>
                                <span class="d-block font-size-sm text-body" title="{{ $instruction->instruction }}">
                                    {{Str::limit($instruction->instruction, 50,'...')}}
                                </span>
                                    </td>
                                    <td>
                                        <label class="toggle-switch toggle-switch-sm" for="stocksCheckbox{{$instruction->id}}">
                                            <input type="checkbox" data-url="{{route('admin.parcel.instruction_status',[$instruction['id'],$instruction->status?0:1])}}" class="toggle-switch-input redirect-url" id="stocksCheckbox{{$instruction->id}}" {{$instruction->status?'checked':''}}>
                                            <span class="toggle-switch-label">
                                        <span class="toggle-switch-indicator"></span>
                                    </span>
                                        </label>
                                    </td>

                                    <td>
                                        <div class="btn--container justify-content-center">
                                            <a class="btn btn-sm btn--primary btn-outline-primary action-btn edit-instruction"
                                               title="{{ 'editar' }}" data-id="{{$instruction['id']}}"
                                               data-toggle="modal"   data-target="#add_update_instruction_{{$instruction->id}}"
                                            ><i class="tio-edit"></i>
                                            </a>


                                            <a class="btn btn-sm btn--danger btn-outline-danger action-btn form-alert" href="javascript:"
                                               data-id="instruction-{{$instruction['id']}}" data-message="{{ '¿Quieres eliminar esta instrucción?' }}"
                                               title="{{'borrar'}}">
                                                <i class="tio-delete-outlined"></i>
                                            </a>
                                            <form action="{{route('admin.parcel.instruction_delete',[$instruction['id']])}}"
                                                  method="post" id="instruction-{{$instruction['id']}}">
                                                @csrf @method('delete')
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <!-- Modal -->
                                <div class="modal fade" id="add_update_instruction_{{$instruction->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                                     aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="exampleModalLabel">{{ 'Actualización de instrucciones' }}</label></h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <form action="{{ route('admin.parcel.instruction_edit') }}" method="post">
                                                    @csrf
                                                    @method('put')

                                                    @php($instruction=  \App\Models\ParcelDeliveryInstruction::withoutGlobalScope('translate')->with('translations')->find($instruction->id))

                                                    <ul class="nav nav-tabs nav--tabs mb-3 border-0">
                                                        <li class="nav-item">
                                                            <a class="nav-link update-lang_link add_active active"
                                                               href="#"
                                                               id="default-link">{{ 'Por defecto' }}</a>
                                                        </li>
                                                        @if($language)
                                                            @foreach ($language as $lang)
                                                                <li class="nav-item">
                                                                    <a class="nav-link update-lang_link"
                                                                       href="#"
                                                                       data-reason-id="{{$instruction->id}}"
                                                                       id="{{ $lang }}-link">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                                                                </li>
                                                            @endforeach
                                                        @endif
                                                    </ul>
                                                    <input type="hidden" name="instruction_id"  value="{{$instruction->id}}" />

                                                    <div class="form-group mb-3 add_active_2  update-lang_form" id="default-form_{{$instruction->id}}">
                                                        <label class="form-label">{{'Instrucción'}} ({{'por defecto'}}) </label>
                                                        <input class="form-control" name='instruction[]' maxlength="191" value="{{$instruction?->getRawOriginal('instruction')}}" type="text">
                                                        <input type="hidden" name="lang1[]" value="default">
                                                    </div>
                                                    @if($language)
                                                        @forelse($language as $lang)
                                                                <?php
                                                                if($instruction?->translations){
                                                                    $translate = [];
                                                                    foreach($instruction?->translations as $t)
                                                                    {
                                                                        if($t->locale == $lang && $t->key=="instruction"){
                                                                            $translate[$lang]['instruction'] = $t->value;
                                                                        }
                                                                    }
                                                                }
                                                                ?>
                                                            <div class="form-group mb-3 d-none update-lang_form" id="{{$lang}}-langform_{{$instruction->id}}">
                                                                <label class="form-label">{{'Instrucción'}} ({{strtoupper($lang)}})</label>
                                                                <input class="form-control" name='instruction[]' maxlength="191" value="{{ $translate[$lang]['instruction'] ?? null }}"  type="text">
                                                                <input type="hidden" name="lang1[]" value="{{$lang}}">
                                                            </div>
                                                @empty
                                                @endforelse
                                                @endif

                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ 'Cerca' }}</button>
                                                <button type="submit" class="btn btn-primary">{{ 'Guardar cambios' }}</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            </tbody>
                        </table>
                        @if(count($instructions) === 0)
                            <div class="empty--data">
                                <img src="{{asset('assets/admin/svg/illustrations/sorry.svg')}}" alt="public">
                                <h5>
                                    {{'no se encontraron datos'}}
                                </h5>
                            </div>
                        @endif
                    </div>
                    <div class="card-footer pt-0 border-0">
                        <div class="page-area px-4 pb-3">
                            <div class="d-flex align-items-center justify-content-end">
                                <div>
                                    {!! $instructions->links() !!}
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
<script src="{{asset('assets/admin/js/view-pages/parcel_delivery_setup.js')}}"></script>
@endpush
