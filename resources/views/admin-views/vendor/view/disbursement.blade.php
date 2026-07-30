@extends('layouts.admin.app')

@section('title',$store->name."'s ".'conversación')

@push('css_or_js')
    <!-- Custom styles for this page -->
    <link href="{{asset('assets/admin/css/croppie.css')}}" rel="stylesheet">

@endpush

@section('content')
<div class="content container-fluid">
    @include('admin-views.vendor.view.partials._header',['store'=>$store])
    <!-- Page Heading -->
    <div class="tab-content">
        <div class="tab-pane fade show active" id="product">
            <div class="card">
                <div class="card-header border-0 py-2">
                    <div class="search--button-wrapper">
                        <h5 class="card-title">
                            {{ 'Desembolsos totales' }} <span class="badge badge-soft-secondary ml-2" id="countItems">{{ $disbursements->total() }}</span>
                        </h5>
                        <form class="search-form">
                            <!-- Search -->
                            <div class="input--group input-group input-group-merge input-group-flush">
                                <input class="form-control" value="{{ request()?->search  ?? null }}" placeholder="{{ 'buscar por información de la tienda' }}" name="search">
                                <button type="submit" class="btn btn--secondary"><i class="tio-search"></i></button>
                            </div>
                            <!-- End Search -->
                        </form>
                        <!-- Static Export Button -->
                        <div class="hs-unfold ml-3">
                            <a class="js-hs-unfold-invoker btn btn-sm btn-white dropdown-toggle btn export-btn btn-outline-primary btn--primary font--sm" href="javascript:;"
                               data-hs-unfold-options='{
                            "target": "#usersExportDropdown",
                            "type": "css-animation"
                        }'>
                                <i class="tio-download-to mr-1"></i> {{'exportar'}}
                            </a>
                            <div id="usersExportDropdown"
                                 class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-sm-right">
                                <span class="dropdown-header">{{'opciones de descarga'}}</span>
                                <a id="export-excel" class="dropdown-item" href="{{route('admin.store.disbursement-export', ['id'=>$store->id,'type'=>'excel'])}}">
                                    <img class="avatar avatar-xss avatar-4by3 mr-2" src="{{asset('assets/admin')}}/svg/components/excel.svg" alt="Image Description">
                                    {{'sobresalir'}}
                                </a>
                                <a id="export-csv" class="dropdown-item" href="{{route('admin.store.disbursement-export', ['id'=>$store->id,'type'=>'csv'])}}">
                                    <img class="avatar avatar-xss avatar-4by3 mr-2" src="{{asset('assets/admin')}}/svg/components/placeholder-csv-format.svg" alt="Image Description">
                                    {{'csv'}}
                                </a>
                            </div>
                        </div>
                        <!-- Static Export Button -->

                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-thead-bordered table-align-middle card-table">
                            <thead>
                            <tr>
                                <th>{{ 'SL' }}</th>
                                <th>{{ 'identificación' }}</th>
                                <th>{{ 'Monto desembolsado' }}</th>
                                <th>{{ 'Método de pago' }}</th>
                                <th>{{ 'estado' }}</th>
                                <th>
                                    <div class="text-center">
                                        {{ 'acción' }}
                                    </div>
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($disbursements as $key => $store)
                                <tr>
                                    <td>
                                        <span class="font-weight-bold">{{$key+$disbursements->firstItem()  }}</span>
                                    </td>
                                    <td>
                                        #{{ $store->disbursement_id }}
                                    </td>
                                    <td>
                                        {{\App\CentralLogics\Helpers::format_currency($store['disbursement_amount'])}}
                                    </td>
                                    <td>
                                        <div>
                                            {{$store->withdraw_method->method_name}}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-soft-primary">{{$store->status}}</span>
                                    </td>
                                    <td>
                                        <div class="btn--container justify-content-center">
                                            <a class="btn btn-sm btn--primary btn-outline-primary action-btn" data-toggle="modal" data-target="#payment-info-{{$store->id}}" title="View Details">
                                                <i class="tio-visible"></i>
                                            </a>
                                        </div>
                                    </td>
                                    <div class="modal fade" id="payment-info-{{$store->id}}">
                                        <div class="modal-dialog modal-xl">
                                            <div class="modal-content">
                                                <div class="modal-header pb-4">
                                                    <button type="button" class="payment-modal-close btn-close border-0 outline-0 bg-transparent" data-dismiss="modal">
                                                        <i class="tio-clear"></i>
                                                    </button>
                                                    <div class="w-100 text-center">
                                                        <h2 class="mb-2">{{ 'Información de pago' }}</h2>
                                                        <div>
                                                            <span class="mr-2">{{ 'ID de desembolso' }}</span>
                                                            <strong>#{{$store->disbursement_id}}</strong>
                                                        </div>
                                                        <div class="mt-2">
                                                            <span class="mr-2">{{ 'estado' }}</span>
                                                            <span class="badge badge-soft-primary">{{$store->status}}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="card shadow--card-2">
                                                        <div class="card-body">
                                                            <div class="d-flex flex-wrap payment-info-modal-info p-xl-4">
                                                                <div class="item">
                                                                    <h5>{{ 'Información del restaurante' }}</h5>
                                                                    <ul class="item-list">
                                                                        <li class="d-flex flex-wrap">
                                                                            <span class="name">{{ 'nombre' }}</span>
                                                                            <span>:</span>
                                                                            <strong>{{$store?->store?->name}}</strong>
                                                                        </li>
                                                                        <li class="d-flex flex-wrap">
                                                                            <span class="name">{{ 'contacto' }}</span>
                                                                            <span>:</span>
                                                                            <strong>{{$store?->store?->phone}}</strong>
                                                                        </li>
                                                                    </ul>
                                                                </div>
                                                                <div class="item">
                                                                    <h5>{{ 'Información del propietario' }}</h5>
                                                                    <ul class="item-list">
                                                                        <li class="d-flex flex-wrap">
                                                                            <span class="name">{{ 'nombre' }}</span>
                                                                            <span>:</span>
                                                                            <strong>{{$store->store->vendor->f_name}} {{$store->store->vendor->l_name}}</strong>
                                                                        </li>
                                                                        <li class="d-flex flex-wrap">
                                                                            <span class="name">{{ 'correo electrónico' }}</span>
                                                                            <span>:</span>
                                                                            <strong>{{$store->store->vendor->email}}</strong>
                                                                        </li>
                                                                    </ul>
                                                                </div>
                                                                <div class="item w-100">
                                                                    <h5>{{ 'Información de la cuenta' }}</h5>
                                                                    <ul class="item-list">
                                                                        <li class="d-flex flex-wrap">
                                                                            <span class="name">{{ 'método de pago' }}</span><strong>{{$store->withdraw_method->method_name}}</strong>
                                                                        </li>
                                                                        <li class="d-flex flex-wrap">
                                                                            <span class="name">{{ 'cantidad' }}</span>
                                                                            <strong>{{\App\CentralLogics\Helpers::format_currency($store['disbursement_amount'])}}</strong>
                                                                        </li>
                                                                        @forelse(json_decode($store->withdraw_method->method_fields, true) as $key=> $item)
                                                                            <li class="d-flex flex-wrap">
                                                                                <span class="name">{{  translate($key) }}</span>
                                                                                <strong>{{$item}}</strong>
                                                                            </li>
                                                                        @empty

                                                                        @endforelse

                                                                    </ul>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                @if(count($disbursements) === 0)

                            <div class="empty--data">
                                <img src="{{asset('assets/admin/svg/illustrations/sorry.svg')}}" alt="public">
                                <h5>
                                    {{'no se encontraron datos'}}
                                </h5>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="page-area px-4 pb-3">
                    <div class="d-flex align-items-center justify-content-end">
                        <div>
                            {!!$disbursements->links()!!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script_2')
@endpush
