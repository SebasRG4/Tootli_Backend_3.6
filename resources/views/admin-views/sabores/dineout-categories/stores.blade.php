@extends('layouts.admin.app')

@section('title', translate('Assign Stores to Category'))

@push('css_or_js')
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <i class="tio-shop"></i>
                </span>
                <span>
                    {{ translate('Assign Stores to') }}: {{ $category->name }} {{ $category->image }}
                </span>
            </h1>
            <a href="{{ route('admin.sabores.dineout-categories.index') }}" class="btn btn--secondary">
                <i class="tio-back-ui"></i> {{ translate('Back') }}
            </a>
        </div>
        <!-- End Page Header -->

        <div class="row">
            <div class="col-md-5">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">{{ translate('Available Stores') }}</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.sabores.dineout-categories.assign-store', $category->id) }}"
                            method="post">
                            @csrf
                            <div class="form-group">
                                <label class="input-label" for="store_id">{{ translate('Select Store') }}</label>
                                <select name="store_id" id="store_id" class="form-control js-select2-custom" required>
                                    <option value="" selected disabled>{{ translate('Select Store') }}</option>
                                    @foreach($availableStores as $store)
                                        <option value="{{ $store->id }}">{{ $store->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="btn--container justify-content-end mt-3">
                                <button type="submit" class="btn btn--primary">{{ translate('Add Store') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-7">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">{{ translate('Assigned Stores') }} <span
                                class="badge badge-soft-info ml-2">{{ $assignedStores->total() }}</span></h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive datatable-custom">
                            <table class="table table-borderless table-thead-bordered table-align-middle">
                                <thead class="bg-table-head">
                                    <tr>
                                        <th class="border-0">{{ translate('sl') }}</th>
                                        <th class="border-0 w--1">{{ translate('messages.name') }}</th>
                                        <th class="border-0 text-center">{{ translate('messages.action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($assignedStores as $key => $store)
                                        <tr>
                                            <td>{{ $key + $assignedStores->firstItem() }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img class="avatar avatar-lg mr-3"
                                                        onerror="this.src='{{ asset('assets/admin/img/160x160/img1.jpg') }}'"
                                                        src="{{ \App\CentralLogics\Helpers::get_full_url('store', $store->logo, $store->storage[0]->value ?? 'public', 'logo') }}"
                                                        alt="{{ $store->name }}">
                                                    <div class="media-body">
                                                        <span class="d-block text-hover-primary mb-0">{{ $store->name }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <a class="btn action-btn btn--danger btn-outline-danger" href="javascript:"
                                                    onclick="form_alert('store-{{ $store->id }}','{{ translate('Want to remove this store?') }}')"
                                                    title="{{ translate('Remove') }}">
                                                    <i class="tio-delete-outlined"></i>
                                                </a>
                                                <form
                                                    action="{{ route('admin.sabores.dineout-categories.remove-store', [$category->id, $store->id]) }}"
                                                    method="post" id="store-{{ $store->id }}">
                                                    @csrf @method('delete')
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="page-area px-4 pb-3">
                        <div class="d-flex align-items-center justify-content-end">
                            <div>
                                {!! $assignedStores->links() !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
    <script>
        $(document).ready(function () {
            $('.js-select2-custom').select2();
        });
    </script>
@endpush