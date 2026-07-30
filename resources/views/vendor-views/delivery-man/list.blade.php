@extends('layouts.vendor.app')

@section('title','repartidores')


@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('assets/admin/img/deliveryman.png')}}" class="w--30" alt="">
                </span>
                <span>
                   {{'Repartidor'}}<span class="badge badge-soft-dark ml-2" id="itemCount">{{$delivery_men->total()}}</span>
                </span>
            </h1>
        </div>
        <!-- End Page Header -->
        <!-- Card -->
        <div class="card">
            <!-- Header -->
            <div class="card-header justify-content-end">
                <form class="search-form" >
                    <div class="input-group input--group">
                        <input  type="search" name="search" class="form-control" value="{{request()?->search ?? ''}}"
                                placeholder="{{'ex nombre de búsqueda'}}" aria-label="{{'ex nombre de búsqueda'}}" >
                        <button type="submit" class="btn btn--secondary"><i class="tio-search"></i></button>
                    </div>
                    <!-- End Search -->
                </form>
            </div>
            <!-- End Header -->

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
                        <th class="border-0 text-capitalize">{{'#'}}</th>
                        <th class="border-0 text-capitalize">{{'nombre'}}</th>
                        <th class="border-0 text-capitalize">{{'estado de disponibilidad'}}</th>
                        <th class="border-0 text-capitalize">{{'teléfono'}}</th>
                        <th class="border-0 text-capitalize text-center">{{'pedidos activos'}}</th>
                        <th class="border-0 text-capitalize text-center">{{'acción'}}</th>
                    </tr>
                    </thead>

                    <tbody id="set-rows">
                    @foreach($delivery_men as $key=>$dm)
                        <tr>
                            <td>{{$key+$delivery_men->firstItem()}}</td>
                            <td>
                                <a class="media align-items-center" href="{{route('vendor.delivery-man.preview',[$dm['id']])}}">
                                    <img class="avatar avatar-lg mr-3 onerror-image"
                                         data-onerror-image="{{asset('assets/admin/img/160x160/img1.jpg')}}"
                                         src="{{ $dm['image_full_url']}}"
                                         alt="{{$dm['f_name']}} {{$dm['l_name']}}">
                                    <div class="media-body">
                                        <h5 class="text-hover-primary mb-0">{{$dm['f_name'].' '.$dm['l_name']}}</h5>
                                        <span class="rating">
                                            <i class="tio-star"></i> {{count($dm->rating)>0?number_format($dm->rating[0]->average, 1, '.', ' '):0}}
                                        </span>
                                    </div>
                                </a>
                            </td>
                            <td>
                                <div>
                                    {{'órdenes actualmente asignadas'}} : {{$dm->current_orders}}
                                </div>
                                <div>
                                    {{'estado activo'}} :
                                    @if($dm->application_status == 'approved')
                                        @if($dm->active)
                                        <strong class="text-capitalize text-success">{{'en línea'}}</strong>
                                        @else
                                        <strong class="text-capitalize text-danger">{{'desconectado'}}</strong>
                                        @endif
                                    @elseif ($dm->application_status == 'denied')
                                        <strong class="text-capitalize text-danger">{{'denegado'}}</strong>
                                    @else
                                        <strong class="text-capitalize text-primary">{{'Pendiente'}}</strong>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <a class="deco-none" href="tel:{{$dm['phone']}}">{{$dm['phone']}}</a>
                            </td>
                            <td class="text-center">
                                {{ $dm->orders ? count($dm->orders):0 }}
                            </td>
                            <td>
                                <div class="btn--container justify-content-center">
                                    <a class="btn action-btn btn--primary btn-outline-primary" href="{{route('vendor.delivery-man.edit',[$dm['id']])}}" title="{{'editar'}}"><i class="tio-edit"></i>
                                    </a>
                                    <a class="btn action-btn btn--danger btn-outline-danger form-alert"
                                       data-id="delivery-man-{{$dm['id']}}"
                                       data-message="{{'¿Quieres eliminar a este repartidor?'}}"
                                       href="javascript:"  title="{{'borrar'}}"><i class="tio-delete-outlined"></i>
                                    </a>
                                </div>
                                <form action="{{route('vendor.delivery-man.delete',[$dm['id']])}}" method="post" id="delivery-man-{{$dm['id']}}">
                                    @csrf @method('delete')
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                @if(count($delivery_men) !== 0)
                <hr>
                @endif
                <div class="page-area">
                    <table>
                        <tfoot>
                        {!! $delivery_men->links() !!}
                        </tfoot>
                    </table>
                </div>
                    @if(count($delivery_men) === 0)
                    <div class="empty--data">
                        <img src="{{asset('assets/admin/svg/illustrations/sorry.svg')}}" alt="public">
                        <h5>
                            {{'no se encontraron datos'}}
                        </h5>
                    </div>
                    @endif

            </div>
            <!-- End Table -->
        </div>
        <!-- End Card -->
    </div>

@endsection

@push('script_2')
    <script src="{{asset('assets/admin/js/view-pages/datatable-search.js')}}"></script>
@endpush
