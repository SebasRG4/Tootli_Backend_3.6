@extends('layouts.admin.app')

@section('title','Detalles del cliente')

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="d-print-none pb-3">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title mb-1">{{'identificación del cliente'}} #{{$customer['id']}}</h1>
                    <span class="fs-12">
                        {{'se unió a'}} : {{date('d M Y '.config('timeformat'),strtotime($customer['created_at']))}}
                    </span>

                </div>
            </div>
        </div>
        @if (addon_published_status('Rental'))
            @php($id = request()->user_id)
            <!-- Nav Menus -->
            <ul class="nav nav-tabs border-0 nav--tabs nav--pills mb-4">
                <li class="nav-item">
                    <a class="nav-link {{ request()->module != 1 ? 'active' : '' }}   " href="{{ route('admin.users.customer.view', $id)}}">{{ 'Todo el módulo' }}</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->module == 1 ?'active' : '' }} " href="{{ route('admin.users.customer.rental.view',['module'=> true,'user_id'=>$id])  }}">{{ 'Módulo de Alquiler' }}</a>
                </li>
            </ul>
        @endif
        <!-- End Page Header -->
        @if ($customer['f_name'])
        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="d-flex gap-2 align-items-center">
                        <img src="{{asset('assets/admin/img/icons/coupon-icon.png')}}" width="16" height="16" alt="">
                        <p class="mb-0">{{ 'Si desea crear un CUPÓN personalizado para este cliente, haga clic en el botón Crear cupón e influya en que compre más en su tienda.' }}</p>
                    </div>

                    <a href="{{ route('admin.coupon.add-new',['customer' => $customer['id']]) }}" class="btn btn-warning text-white font-semibold">
                        <i class="tio-add"></i>
                        {{'crear cupón'}}
                    </a>
                </div>
            </div>
        </div>
        @endif

        <div class="row mb-3 g-2">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-3">
                            <div class="color-card flex-column align-items-center justify-content-center color-2 flex-grow-1">
                                <div class="img-box">
                                    <img class="resturant-icon w--30" src="{{asset('assets/admin/img/icons/order-icon-1.png')}}" alt="">
                                </div>
                                <div class="d-flex flex-column align-items-center">
                                    <h2 class="title"> {{ $trips->total() }} </h2>
                                    <div class="subtitle">
                                        {{ 'viaje total' }}
                                    </div>
                                </div>
                            </div>
                            <div class="color-card flex-column align-items-center justify-content-center color-5 flex-grow-1">
                                <div class="img-box">
                                    <img class="resturant-icon w--30" src="{{asset('assets/admin/img/icons/order-icon-2.png')}}" alt="">
                                </div>
                                <div class="d-flex flex-column align-items-center">
                                    <h2 class="title"> {{ \App\CentralLogics\Helpers::format_currency($total_trips_amount[0]->total_trip_amount) }} </h2>
                                    <div class="subtitle">
                                        {{ 'importe total del viaje' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-3">
                            <div class="color-card flex-column align-items-center justify-content-center color-7 flex-grow-1">
                                <div class="img-box">
                                    <img class="resturant-icon w--30" src="{{asset('assets/admin/img/icons/order-icon-3.png')}}" alt="transactions">
                                </div>
                                <div class="d-flex flex-column align-items-center">
                                    <h2 class="title"> {{$customer->wallet_balance??0}} </h2>
                                    <div class="subtitle">
                                        {{'saldo de billetera'}}
                                    </div>
                                </div>
                            </div>
                            <div class="color-card flex-column align-items-center justify-content-center color-4 flex-grow-1">
                                <div class="img-box">
                                    <img class="resturant-icon w--30" src="{{asset('assets/admin/img/icons/order-icon-4.png')}}" alt="transactions">
                                </div>
                                <div class="d-flex flex-column align-items-center">
                                    <h2 class="title"> {{$customer->loyalty_point??0}} </h2>
                                    <div class="subtitle">
                                        {{'punto de fidelidad'}}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row" id="printableArea">
            <div class="col-lg-8 mb-3 mb-lg-0">
                <div class="card">
                    <div class="card-header border-0 py-2 d-flex flex-wrap gap-2">
                        <div class="search--button-wrapper">
                            <h5 class="card-title d-flex gap-2 align-items-center">
                                {{'lista de viaje'}}
                                <span class="badge badge-soft-secondary">{{ $trips->total() }}</span>
                            </h5>

                            <div class="min--260">
                                <form class="search-form theme-style">
                                    <div class="input-group input--group">
                                        <input  type="search" name="search" class="form-control"
                                        placeholder="{{'ej: buscar por ID de viaje'}}" aria-label="{{'buscar'}}" value="{{request()?->search}}" >
                                        <button type="submit" class="btn btn--secondary"><i class="tio-search"></i></button>
                                    </div>
                                </form>

                            </div>
                            @if(request()->get('search'))
                                 <button type="reset" class="btn btn--primary ml-2 location-reload-to-base" data-url="{{url()->full()}}">{{'reiniciar'}}</button>
                                 @endif
                        </div>
                    <!-- Unfold -->
                    <div class="hs-unfold mr-2">
                        <a class="js-hs-unfold-invoker btn btn-sm btn-white dropdown-toggle min-height-40" href="javascript:;"
                            data-hs-unfold-options='{
                                    "target": "#usersExportDropdown",
                                    "type": "css-animation"
                                }'>
                            <i class="tio-download-to mr-1"></i> {{ 'exportar' }}
                        </a>

                        <div id="usersExportDropdown"
                            class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-sm-right">
                            <span class="dropdown-header">{{ 'opciones de descarga' }}</span>
                            <a id="export-excel" class="dropdown-item" href="{{route('admin.customer.trip-export', ['type'=>'excel','id'=>$customer->id,request()->getQueryString()])}}">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                    src="{{ asset('assets/admin') }}/svg/components/excel.svg"
                                    alt="Image Description">
                                {{ 'sobresalir' }}
                            </a>
                            <a id="export-csv" class="dropdown-item" href="{{route('admin.customer.trip-export', ['type'=>'csv','id'=>$customer->id,request()->getQueryString()])}}">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                    src="{{ asset('assets/admin') }}/svg/components/placeholder-csv-format.svg"
                                    alt="Image Description">
                                .{{ 'csv' }}
                            </a>
                        </div>
                    </div>
                    <!-- End Unfold -->
                    </div>

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
                                    <th class="border-0 pl-4">{{'SL'}}</th>
                                    <th class="border-0">{{'ID de viaje'}}</th>
                                    <th class="border-0">{{'proveedor'}}</th>
                                    <th class="border-0 ">{{'estado'}}</th>
                                    <th class="border-0 text-center ">{{'vehículo total'}}</th>
                                    <th class="border-0 ">{{'cantidad total'}}</th>
                                    <th class="border-0 ">{{'fecha de viaje'}}</th>
                                    <th class="border-0 text-center">{{'acción'}}</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($trips as $key=>$trip)
                                    <tr>
                                        <td>
                                            <div class="pl-2">
                                                {{$key+$trips->firstItem()}}
                                            </div>
                                        </td>
                                        <td>
                                            <a class="text-dark" href="{{route('admin.rental.trip.details', $trip->id)}}">{{$trip['id']}}</a>
                                        </td>
                                        <th>
                                            @if ($trip->provider)
                                            <div><a  class="text--title" href="{{route('admin.rental.provider.details', $trip->provider_id)}}">{{Str::limit($trip->provider?$trip->provider->name:'tienda eliminada!',20,'...')}}</a></div>
                                            @else
                                                <div>{{Str::limit('extraviado',20,'...')}}</div>
                                            @endif
                                        </th>
                                        <td class="text-capitalize ">
                                            @if($trip['trip_status']=='pending')
                                                <span class="badge badge-soft-info">
                                                  {{'Pendiente'}}
                                                </span>
                                                        @elseif($trip['trip_status']=='confirmed')
                                                            <span class="badge badge-soft-info">
                                                  {{'confirmado'}}
                                                </span>
                                                        @elseif($trip['trip_status']=='ongoing')
                                                            <span class="badge badge-soft-warning">
                                                  {{'en curso'}}
                                                </span>
                                                        @elseif($trip['trip_status']=='completed')
                                                            <span class="badge badge-soft-success">
                                                  {{'terminado'}}
                                                </span>
                                                        @elseif($trip['trip_status']=='payment_failed')
                                                            <span class="badge badge-soft-danger">
                                                  {{'pago fallido'}}
                                                </span>
                                                        @elseif($trip['trip_status']=='canceled')
                                                            <span class="badge badge-soft-danger">
                                                  {{'Cancelado'}}
                                                </span>
                                                        @else
                                                            <span class="badge badge-soft-danger">
                                                  {{str_replace('_',' ',$trip['trip_status'])}}
                                                </span>
                                            @endif

                                        </td>
                                        <td>
                                            <div class="text-center mw--85px mx-auto">
                                                {{ $trip?->trip_details_count != 0  ?  $trip?->trip_details_count: 'N / A' }}
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                {{\App\CentralLogics\Helpers::format_currency($trip['trip_amount'])}}
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <div>
                                                    {{ \App\CentralLogics\Helpers::date_format($trip->created_at) }}
                                                </div>
                                                <div class="d-block text-uppercase">
                                                    {{ \App\CentralLogics\Helpers::time_format($trip->created_at) }}
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="btn--container justify-content-center">
                                                <a class="btn action-btn btn--warning btn-outline-warning" href="{{route('admin.rental.trip.details', $trip->id)}}" title="{{'vista'}} "><i class="tio-visible"></i></a>
                                                <a class="btn action-btn btn--primary btn-outline-primary" target="_blank" href="{{route('admin.rental.trip.generate-invoice',["id" => $trip->id])}}" title="{{'descargar'}}">
                                                    <i class="tio-download-to"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if(count($trips) !== 0)
                    <hr>
                    @endif
                    <div class="page-area">
                        {!! $trips->links() !!}
                    </div>
                    @if(count($trips) === 0)
                    <div class="empty--data">
                        <img src="{{asset('assets/admin/svg/illustrations/sorry.svg')}}" alt="public">
                        <h5>
                            {{'no se encontraron datos'}}
                        </h5>
                    </div>
                    @endif
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <!-- Header -->
                    <div class="card-header">
                        <h4 class="card-title d-flex flex-wrap align-items-center gap-2">
                            <div class="d-flex align-items-center gap-1">
                                <span class="card-header-icon">
                                    <i class="tio-user"></i>
                                </span>
                                <span class=""> {{ 'información del cliente' }}</span>
                            </div>
                            <span class="badge badge-soft-info">{{ 'viaje total' }}: {{ $trips->total() }}</span>
                        </h4>
                    </div>
                    <!-- End Header -->

                    <!-- Body -->
                    @if($customer)
                        <div class="card-body">
                            <div class="media gap-3 flex-wrap">
                                <div class="avatar avatar-circle avatar-70">
                                    <img class="avatar-img onerror-image" width="70" height="70" data-onerror-image="{{asset('assets/admin/img/160x160/img1.jpg')}}" src="{{ $customer->image_full_url }}"
                                    alt="Image Description">
                                </div>
                                <div class="media-body">
                                    <div class="key-value-list d-flex flex-column gap-2 text-dark" style="--min-width: 60px">
                                        <div class="key-val-list-item d-flex gap-3">
                                            <div>{{ 'nombre' }}</div>:
                                            <div class="font-semibold">{{$customer['f_name']? $customer['f_name'].' '.$customer['l_name'] : 'Perfil incompleto'}}</div>
                                        </div>
                                        <div class="key-val-list-item d-flex gap-3">
                                            <div>{{ 'contacto' }}</div>:
                                            <a href="tel:{{ $customer['phone'] }}" class="text-dark font-semibold">{{$customer['phone'] ?? 'N / A'}}</a>
                                        </div>
                                        <div class="key-val-list-item d-flex gap-3">
                                            <div>{{ 'correo electrónico' }}</div>:
                                            <a href="mailto:{{ $customer['email'] }}" class="text-dark font-semibold">{{$customer['email'] ?? 'N / A'}}</a>
                                        </div>
                                        @foreach($customer->addresses as $address)
                                            <div class="key-val-list-item d-flex gap-3">
                                                <div>{{ 'DIRECCIÓN' }}</div>:
                                                <a href="https://www.google.com/maps/search/?api=1&query={{ data_get($address,'latitude',0)}},{{ data_get($address,'longitude',0)}}" target="_blank">{{ $address['address'] }}</a>
                                            </div>
                                        @endforeach
                                    </div>

                                    {{-- <ul class="list-unstyled m-0">
                                        <li class="pb-1 d-flex align-items-center">
                                            <i class="tio-shopping-basket-outlined mr-2"></i>
                                            <span>{{$customer->order_count}} {{'Pedidos completados'}}</span>
                                        </li>
                                    </ul> --}}
                                </div>
                            </div>


                            {{-- @foreach($customer->addresses as $address)
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5>{{'direcciones'}}</h5>
                                </div>
                                <ul class="list-unstyled list-unstyled-py-2">
                                    <li class="d-flex align-items-center">
                                        <i class="tio-tab mr-2"></i>
                                        <span>{{translate($address['address_type'])}}</span>
                                    </li>
                                    @if($address['contact_person_umber'])
                                    <li class="d-flex align-items-center">
                                        <i class="tio-android-phone-vs mr-2"></i>
                                        <span>{{$address['contact_person_number']}}</span>
                                    </li>
                                    @endif
                                    <li>
                                        <a target="_blank" href="http://maps.google.com/maps?z=12&t=m&q=loc:{{$address['latitude']}}+{{$address['longitude']}}" class="d-flex align-items-center">
                                            <i class="tio-poi mr-2"></i>
                                            {{$address['address']}}
                                        </a>
                                    </li>
                                </ul>
                                <hr>
                            @endforeach --}}

                        </div>
                @endif
                <!-- End Body -->
                </div>
                <!-- End Card -->
            </div>
        </div>
        <!-- End Row -->
    </div>
@endsection

@push('script_2')

    <script>
        $(document).on('ready', function () {
            // INITIALIZATION OF DATATABLES
            // =======================================================
            let datatable = $.HSCore.components.HSDatatables.init($('#columnSearchDatatable'));

            $('#column1_search').on('keyup', function () {
                datatable
                    .columns(1)
                    .search(this.value)
                    .draw();
            });


            $('#column3_search').on('change', function () {
                datatable
                    .columns(2)
                    .search(this.value)
                    .draw();
            });


            // INITIALIZATION OF SELECT2
            // =======================================================
            $('.js-select2-custom').each(function () {
                let select2 = $.HSCore.components.HSSelect2.init($(this));
            });
        });
    </script>
@endpush
