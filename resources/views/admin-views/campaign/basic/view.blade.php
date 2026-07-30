@extends('layouts.admin.app')

@section('title', 'Vista de campaña')

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
<div class="content container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <h1 class="page-header-title text-break">{{$campaign->title}}</h1>
    </div>
    <!-- End Page Header -->
    <!-- Card -->
    <div class="card mb-3 mb-lg-5">
        <!-- Body -->
        <div class="card-body">
            <div class="row align-items-md-center gx-md-5">
                <div class="col-md-4 mb-3 mb-md-0">
                    <img class="rounded initial--5 onerror-image" src="{{ $campaign->image_full_url }}"
                        data-onerror-image="{{asset('assets/admin/img/160x160/img2.jpg')}}" alt="Image Description">
                </div>

                <div class="col-md-8">
                    <h4>{{'breve descripción'}} : </h4>
                    <p>{{$campaign->description}}</p>
                    <form action="{{route('admin.campaign.addstore', $campaign->id)}}" id="store-add-form" method="POST">
                        @csrf
                        <!-- Search -->
                        <div class="d-flex flex-wrap g-2">
                            <div class="flex-grow-1">
                                @php
                                    $allstores = App\Models\Store::Active()->whereHas('module', function ($q) use ($campaign) {
                                        if ($campaign->module->module_type == 'sabores') {
                                            $q->whereIn('module_type', ['food', 'sabores']);
                                        } else {
                                            $q->where('id', $campaign->module_id);
                                        }
                                    })->get();
                                @endphp
                                <select name="store_id" id="store_id" class="form-control">
                                    @forelse($allstores as $store)
                                        @if(!in_array($store->id, $store_ids))
                                            <option value="{{$store->id}}">{{$store->name}}</option>
                                        @endif
                                    @empty
                                        <option value="">{{ 'no se encontraron datos' }}</option>
                                    @endforelse
                                </select>
                            </div>
                            <div>
                                <button type="submit" class="btn btn--primary font-weight-regular h--45px"><i
                                        class="tio-add-circle-outlined"></i>
                                    {{'agregar tienda'}}</button>
                            </div>
                        </div>
                        <!-- End Search -->
                    </form>
                </div>

            </div>
        </div>
        <!-- End Body -->
    </div>
    <!-- End Card -->
    <!-- Card -->
    <div class="card">
        <div class="card-header py-2 border-0">
            <div class="search--button-wrapper">
                <span class="card-title"></span>
            </div>
        </div>
        <!-- Table -->
        <div class="table-responsive datatable-custom">
            <table id="columnSearchDatatable"
                class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                data-hs-datatables-options='{
                            "order": [],
                            "orderCellsTop": true
                        }'>
                <thead class="thead-light">
                    <tr>
                        <th class="border-0">{{ 'SL' }}</th>
                        <th class="border-0 w--15">{{'logo'}}</th>
                        <th class="border-0 w--2">{{'Negocio'}}</th>
                        <th class="border-0 w--25">{{'dueño'}}</th>
                        <th class="border-0">{{'correo electrónico'}}</th>
                        <th class="border-0">{{'teléfono'}}</th>
                        <th class="border-0">{{'estado'}}</th>
                        <th class="border-0">{{'acción'}}</th>
                    </tr>
                </thead>

                <tbody id="set-rows">
                    @foreach($stores as $key => $store)
                    <tr>
                        <td>{{$key + 1}}</td>
                        <td>
                            <img width="45" class="img--circle onerror-image"
                                data-onerror-image="{{asset('assets/admin/img/160x160/img1.jpg')}}"
                                src="{{ $store['logo_full_url'] }}">
                        </td>
                        <td>
                            <a href="{{route('admin.store.view', $store->id)}}" title="{{$store->name}}"
                                class="d-block font-size-sm text-body">
                                {{$store->name}}
                            </a>
                        </td>
                        <td>
                            <span title=" {{$store->vendor->f_name . ' ' . $store->vendor->l_name}}"
                                class="d-block font-size-sm text-body">
                                {{$store->vendor->f_name . ' ' . $store->vendor->l_name}}
                            </span>
                        </td>
                        <td title="{{$store->email}}">
                            <a href="mailto:{{$store->email}}">
                                {{$store->email}}</a>
                        </td>
                        <td title="{{$store['phone']}}">
                            <a href="tel:{{$store['phone']}}">
                                {{$store['phone']}}
                            </a>
                        </td>
                        @php($status = $store->pivot ? $store->pivot->campaign_status : 'extraviado')
                        <td class="text-capitalize">
                            @if ($status == 'pending')
                                <span class="badge badge-soft-info">
                                    {{ 'no aprobado' }}
                                </span>
                            @elseif($status == 'confirmed')
                                <span class="badge badge-soft-success">
                                    {{ 'confirmado' }}
                                </span>
                            @elseif($status == 'rejected')
                                <span class="badge badge-soft-danger">
                                    {{ 'rechazado' }}
                                </span>
                            @else
                                <span class="badge badge-soft-info">
                                    {{ translate(str_replace('_', ' ', $status)) }}
                                </span>
                            @endif

                        </td>
                        <td>
                            @if ($store->pivot && $store->pivot->campaign_status == 'pending')
                                <div class="btn--container justify-content-center">
                                    <a class="btn btn-sm btn--primary btn-outline-primary action-btn status-change-alert"
                                        data-url="{{ route('admin.campaign.store_confirmation', [$campaign->id, $store->id, 'confirmed']) }}"
                                        data-message="{{ 'Quieres confirmar esta tienda' }}"
                                        class="toggle-switch-input" data-toggle="tooltip" data-placement="top"
                                        title="{{'Aprobar'}}">
                                        <i class="tio-done font-weight-bold"></i>
                                    </a>
                                    <a class="btn btn-sm btn--danger btn-outline-danger action-btn status-change-alert"
                                        href="javascript:"
                                        data-url="{{ route('admin.campaign.store_confirmation', [$campaign->id, $store->id, 'rejected']) }}"
                                        data-message="{{ 'quieres rechazar esta tienda' }}"
                                        data-toggle="tooltip" data-placement="top" title="{{'Denegar'}}">
                                        <i class="tio-clear font-weight-bold"></i>
                                    </a>
                                    <div></div>
                                </div>
                            @elseif ($store->pivot && $store->pivot->campaign_status == 'rejected')

                                <div class="btn--container justify-content-center">
                                    <a class="btn btn-sm btn--primary btn-outline-primary action-btn status-change-alert"
                                        data-url="{{ route('admin.campaign.store_confirmation', [$campaign->id, $store->id, 'confirmed']) }}"
                                        data-message="{{ 'Quieres confirmar esta tienda' }}"
                                        class="toggle-switch-input" data-toggle="tooltip" data-placement="top"
                                        title="{{'Aprobar'}}">
                                        <i class="tio-done font-weight-bold"></i>
                                    </a>

                                </div>
                            @else
                                <div class="btn--container justify-content-center">
                                    <a class="btn btn--danger btn-outline-danger action-btn form-alert" href="javascript:"
                                        data-id="campaign-{{$store->id}}"
                                        data-message="{{'quiero eliminar la tienda'}}"
                                        title="{{'eliminar campaña'}}"><i
                                            class="tio-delete-outlined"></i>
                                    </a>

                                    <form action="{{route('admin.campaign.remove-store', [$campaign->id, $store['id']])}}"
                                        method="GET" id="campaign-{{$store->id}}">
                                        @csrf
                                    </form>
                                </div>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <hr>

            <div class="page-area">
                <table>
                    <tfoot>
                        {!! $stores->links() !!}
                    </tfoot>
                </table>
            </div>

        </div>
        <!-- End Table -->
    </div>
    <!-- End Card -->
</div>

@endsection

@push('script_2')
    <script>
        "use strict";
        $('.status-change-alert').on('click', function (event) {
            let url = $(this).data('url');
            let message = $(this).data('message');
            event.preventDefault();
            Swal.fire({
                title: '{{ '¿Está seguro?' }}',
                text: message,
                type: 'warning',
                showCancelButton: true,
                cancelButtonColor: 'default',
                confirmButtonColor: '#FC6A57',
                cancelButtonText: '{{'No'}}',
                confirmButtonText: '{{'Sí'}}',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    location.href = url;
                }
            })
        })
    </script>
@endpush