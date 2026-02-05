@extends('layouts.admin.app')

@section('title', translate('Sabores Campaigns'))

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title"><i class="tio-layers-outlined"></i> {{translate('Sabores Campaigns')}}
                        <span class="badge badge-soft-dark ml-2" id="itemCount">{{$campaigns->total()}}</span>
                    </h1>
                </div>

                <div class="col-sm-auto">
                    <a class="btn btn--primary" href="{{route('admin.campaign.add-new', 'basic')}}">
                        <i class="tio-add"></i> {{translate('Add New Campaign')}}
                    </a>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <!-- Card -->
                <div class="card">
                    <!-- Header -->
                    <div class="card-header">
                        <div class="search--button-wrapper justify-content-end">
                            <form action="{{url()->current()}}" method="GET">
                                <div class="input-group input--group">
                                    <input id="datatableSearch_" type="search" name="search" class="form-control"
                                        placeholder="{{translate('Search by title')}}" aria-label="Search"
                                        value="{{$search}}" required>
                                    <button type="submit" class="btn btn--secondary"><i class="tio-search"></i></button>
                                </div>
                            </form>
                        </div>
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
                                    <th>{{translate('sl')}}</th>
                                    <th style="width: 15%">{{translate('Title')}}</th>
                                    <th style="width: 15%">{{translate('Image')}}</th>
                                    <th>{{translate('Date')}}</th>
                                    <th>{{translate('Time')}}</th>
                                    <th>{{translate('Status')}}</th>
                                    <th class="text-center">{{translate('Action')}}</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($campaigns as $key => $campaign)
                                    <tr>
                                        <td>{{$campaigns->firstItem() + $key}}</td>
                                        <td>
                                            <span class="d-block font-size-sm text-body">
                                                {{Str::limit($campaign['title'], 25, '...')}}
                                            </span>
                                        </td>
                                        <td>
                                            <div style="height: 60px; width: 60px; overflow-x: hidden;overflow-y: hidden">
                                                <img src="{{$campaign['image_full_url']}}"
                                                    style="height: 100%; width: 100%; object-fit: cover"
                                                    onerror="this.src='{{asset('assets/admin/img/160x160/img2.jpg')}}'"
                                                    class="img--vertical" alt="Campaign image">
                                            </div>
                                        </td>
                                        <td>
                                            <span
                                                class="bg-gradient-light text-dark">{{$campaign->start_date ? $campaign->start_date->format('d M, Y') . ' - ' . $campaign->end_date->format('d M, Y') : 'N/A'}}</span>
                                        </td>
                                        <td>
                                            <span
                                                class="bg-gradient-light text-dark">{{$campaign->start_time ? \Carbon\Carbon::parse($campaign->start_time)->format(config('timeformat')) . ' - ' . \Carbon\Carbon::parse($campaign->end_time)->format(config('timeformat')) : 'N/A'}}</span>
                                        </td>
                                        <td>
                                            <label class="toggle-switch toggle-switch-sm" for="stocksCheckbox{{$campaign->id}}">
                                                <input type="checkbox"
                                                    onclick="location.href='{{route('admin.campaign.status', ['basic', $campaign['id'], $campaign->status ? 0 : 1])}}'"
                                                    class="toggle-switch-input" id="stocksCheckbox{{$campaign->id}}"
                                                    {{$campaign->status ? 'checked' : ''}}>
                                                <span class="toggle-switch-label">
                                                    <span class="toggle-switch-indicator"></span>
                                                </span>
                                            </label>
                                        </td>
                                        <td>
                                            <div class="btn--container justify-content-center">
                                                <a class="btn action-btn btn--primary btn-outline-primary"
                                                    href="{{route('admin.campaign.view', ['basic', $campaign['id']])}}"
                                                    title="{{translate('view')}}"><i class="tio-visible"></i>
                                                </a>
                                                <a class="btn action-btn btn--primary btn-outline-primary"
                                                    href="{{route('admin.campaign.edit', ['basic', $campaign['id']])}}"
                                                    title="{{translate('edit')}}"><i class="tio-edit"></i>
                                                </a>
                                                <a class="btn action-btn btn--danger btn-outline-danger" href="javascript:"
                                                    onclick="form_alert('campaign-{{$campaign['id']}}','{{translate('Want to delete this item ?')}}')"
                                                    title="{{translate('delete')}}"><i class="tio-delete-outlined"></i>
                                                </a>
                                                <form action="{{route('admin.campaign.delete', [$campaign['id']])}}"
                                                    method="post" id="campaign-{{$campaign['id']}}">
                                                    @csrf @method('delete')
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @if(count($campaigns) === 0)
                            <div class="empty--data">
                                <img src="{{asset('/assets/admin/img/empty.png')}}" alt="public">
                                <h5>
                                    {{translate('no_data_found')}}
                                </h5>
                            </div>
                        @endif
                    </div>
                    <!-- End Table -->

                    <div class="card-footer page-area">
                        <!-- Pagination -->
                        <div class="row justify-content-center justify-content-sm-between align-items-sm-center">
                            <div class="col-sm-auto">
                                <div class="d-flex justify-content-center justify-content-sm-end">
                                    <!-- Pagination -->
                                    {!! $campaigns->links() !!}
                                </div>
                            </div>
                        </div>
                        <!-- End Pagination -->
                    </div>
                </div>
                <!-- End Card -->
            </div>
        </div>
    </div>
@endsection