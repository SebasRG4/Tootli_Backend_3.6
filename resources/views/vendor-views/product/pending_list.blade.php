@extends('layouts.vendor.app')

@section('title','lista de artículos')

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
@php($store_data=\App\CentralLogics\Helpers::get_store_data())
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="btn--container align-items-center mb-0">
                <div class="mr-auto">
                    <h1 class="page-header-title"><i class="tio-filter-list"></i> {{'Productos pendientes de aprobación'}}<span class="badge badge-soft-dark ml-2" id="itemCount">{{$items->total()}}</span></h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- Card -->
        <div class="card">
            <!-- Header -->
            <div class="card-header py-2  border-0">
                <h4>{{ 'Lista de artículos' }}</h4>
                <div class="search--button-wrapper justify-content-end">
                    <form class="search-form">

                        <!-- Search -->
                        <div class="input-group input--group">
                            <input id="datatableSearch" type="search"  value="{{ request()?->search ?? null }}" name="search" class="form-control" placeholder="{{'ex nombre de búsqueda'}}" aria-label="{{'buscar aquí'}}">
                            <button type="submit" class="btn btn--secondary"><i class="tio-search"></i></button>
                        </div>
                        <!-- End Search -->
                    </form>
                </div>
            </div>
            <!-- End Header -->


            <!-- Table -->
            <div class="table-responsive datatable-custom">
                <table id="datatable" class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                    data-hs-datatables-options='{
                        "columnDefs": [{
                            "targets": [],
                            "width": "5%",
                            "orderable": false
                        }],
                        "order": [],
                        "info": {
                        "totalQty": "#datatableWithPaginationInfoTotalQty"
                        },

                        "entries": "#datatableEntries",
                        "isResponsive": false,
                        "isShowPaging": false,
                            "paging":false
                    }'>
                    <thead class="thead-light">
                        <tr>
                            <th class="border-0">{{'#'}}</th>
                            <th class="border-0 w-20p">{{'nombre'}}</th>
                            <th class="border-0 w-20p">{{'categoría'}}</th>
                            <th class="border-0">{{'precio'}}</th>
                            <th class="border-0 ">{{'estado'}}</th>
                            <th class="border-0 text-center">{{'acción'}}</th>
                        </tr>
                    </thead>

                    <tbody id="set-rows">
                    @foreach($items as $key=>$item)
                        <tr>
                            <td>{{$key+$items->firstItem()}}</td>
                            <td>
                                <a class="media align-items-center" href="{{route('vendor.item.requested_item_view',['id'=> $item['id']])}}">
                                    <img class="avatar avatar-lg mr-3 onerror-image" src="{{ $item['image_full_url'] }}"
                                         data-onerror-image="{{asset('assets/admin/img/160x160/img2.jpg')}}" alt="{{$item->name}} image">
                                    <div class="media-body">
                                        <h5 class="text-hover-primary mb-0">{{Str::limit($item['name'],20,'...')}}</h5>
                                    </div>
                                </a>
                            </td>
                            <td>
                            {{Str::limit($item->category?$item->category->name:'categoría eliminada',20,'...')}}
                            </td>
                            <td>
                                <div class="mw--85px">
                                    {{\App\CentralLogics\Helpers::format_currency($item['price'])}}
                                </div>
                            </td>
                            <td>
                                    @if ($item->is_rejected == 1)
                                    <span class="badge badge-soft-danger text-capitalize">
                                        {{ 'rechazado' }}
                                    </span>
                                    @else
                                    <span class="badge badge-soft-info text-capitalize">
                                        {{ 'Pendiente' }}
                                    </span>
                                    @endif
                            </td>
                            <td>
                                <div class="btn--container justify-content-center">
                                    @if ($item->is_rejected == 1)
                                    <a class="btn btn-sm btn--primary btn-outline-primary action-btn"
                                        href="{{route('vendor.item.edit',[$item['id'] , 'temp_product' => true])}}" title="{{'editar elemento'}}"><i class="tio-edit"></i>
                                    </a>
                                    @endif
                                    <a class="btn btn-sm btn--danger btn-outline-danger action-btn form-alert" href="javascript:"
                                        data-id="food-{{$item['id']}}" data-message="{{ '¿Quieres eliminar este elemento?' }}" title="{{'eliminar elemento'}}"><i class="tio-delete-outlined"></i>
                                    </a>
                                </div>
                                <form action="{{route('vendor.item.delete',[$item['id']])}}"
                                        method="post" id="food-{{$item['id']}}">
                                    @csrf @method('delete')
                                    <input type="hidden" value="1" name="temp_product" >
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <hr>
                <div class="page-area">
                    <table>
                        <tfoot class="border-top">
                        {!! $items->links() !!}
                        </tfoot>
                    </table>
                </div>
                @if(count($items) === 0)
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
    </div>

@endsection

@push('script_2')
    <script>
        "use strict";
        $(document).on('ready', function () {
            // INITIALIZATION OF DATATABLES
            // =======================================================
            let datatable = $.HSCore.components.HSDatatables.init($('#datatable'), {
          select: {
            style: 'multi',
            classMap: {
              checkAll: '#datatableCheckAll',
              counter: '#datatableCounter',
              counterInfo: '#datatableCounterInfo'
            }
          },
          language: {
            zeroRecords: '<div class="text-center p-4">' +
                '<img class="w-7rem mb-3" src="{{asset('assets/admin/svg/illustrations/sorry.svg')}}" alt="Image Description">' +

                '</div>'
          }
        });

        $('#datatableSearch').on('mouseup', function (e) {
          let $input = $(this),
            oldValue = $input.val();

          if (oldValue == "") return;

          setTimeout(function(){
            let newValue = $input.val();

            if (newValue == ""){
              // Gotcha
              datatable.search('').draw();
            }
          }, 1);
        });

        $('#toggleColumn_index').change(function (e) {
          datatable.columns(0).visible(e.target.checked)
        })
        $('#toggleColumn_name').change(function (e) {
          datatable.columns(1).visible(e.target.checked)
        })

        $('#toggleColumn_type').change(function (e) {
          datatable.columns(2).visible(e.target.checked)
        })

        $('#toggleColumn_status').change(function (e) {
          datatable.columns(4).visible(e.target.checked)
        })
        $('#toggleColumn_price').change(function (e) {
          datatable.columns(3).visible(e.target.checked)
        })
        $('#toggleColumn_action').change(function (e) {
          datatable.columns(5).visible(e.target.checked)
        })


            // INITIALIZATION OF SELECT2
            // =======================================================
            $('.js-select2-custom').each(function () {
                let select2 = $.HSCore.components.HSSelect2.init($(this));
            });
        });

        $('#category').select2({
            ajax: {
                url: '{{route("vendor.category.get-all")}}',
                data: function (params) {
                    return {
                        q: params.term, // search term
                        all:true,
                        page: params.page
                    };
                },
                processResults: function (data) {
                    return {
                    results: data
                    };
                },
                __port: function (params, success, failure) {
                    let $request = $.ajax(params);

                    $request.then(success);
                    $request.fail(failure);

                    return $request;
                }
            }
        });
    </script>

@endpush
