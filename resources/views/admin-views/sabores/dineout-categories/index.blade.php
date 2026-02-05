@extends('layouts.admin.app')

@section('title', translate('Dineout Categories'))

@push('css_or_js')
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <i class="tio-category"></i>
                </span>
                <span>
                    {{ translate('Dineout Categories') }}
                </span>
            </h1>
            <a href="{{ route('admin.sabores.dineout-categories.create') }}" class="btn btn--primary">
                <i class="tio-add"></i> {{ translate('Add New Category') }}
            </a>
        </div>
        <!-- End Page Header -->

        <div class="card mt-3">
            <div class="card-header py-2 border-0">
                <div class="search--button-wrapper">
                    <h5 class="card-title">{{ translate('messages.category_list') }}<span
                            class="badge badge-soft-dark ml-2" id="itemCount">{{ $categories->total() }}</span></h5>

                    <form class="search-form w-340-lg">
                        <!-- Search -->
                        <div class="input-group input--group">
                            <input type="search" name="search" value="{{ request()?->search ?? null }}"
                                class="form-control h-40" placeholder="{{ translate('Search categories') }}"
                                aria-label="{{ translate('Search categories') }}">
                            <button type="submit" class="btn btn--primary h-40"><i class="tio-search"></i></button>
                        </div>
                        <!-- End Search -->
                    </form>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive datatable-custom">
                    <table id="columnSearchDatatable"
                        class="table table-borderless table-thead-bordered table-align-middle"
                        data-hs-datatables-options='{
                            "isResponsive": false,
                            "isShowPaging": false,
                            "paging":false,
                        }'>
                        <thead class="bg-table-head">
                            <tr>
                                <th class=" text-title border-0">{{ translate('sl') }}</th>
                                <th class=" text-title border-0 w--1">{{ translate('messages.name') }}</th>
                                <th class=" text-title border-0 text-center">{{ translate('Icon') }}</th>
                                <th class=" text-title border-0 text-center">{{ translate('Stores') }}</th>
                                <th class=" text-title border-0 text-center">{{ translate('Position') }}</th>
                                <th class=" text-title border-0 text-center">{{ translate('messages.status') }}</th>
                                <th class=" text-title border-0 text-center">{{ translate('messages.action') }}</th>
                            </tr>
                        </thead>

                        <tbody id="table-div">
                            @foreach ($categories as $key => $category)
                                <tr>
                                    <td>{{ $key + $categories->firstItem() }}</td>
                                    <td>
                                        <span class="d-block fs-14 text-title">
                                            {{ $category['name'] }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="fs-20">{{ $category->image }}</span>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.sabores.dineout-categories.stores', $category->id) }}" class="badge badge-soft-info">
                                            {{ $category->stores()->count() }} {{ translate('Stores') }}
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        {{ $category->position }}
                                    </td>
                                    <td class="text-center">
                                        <label class="toggle-switch toggle-switch-sm" for="statusCheckbox{{ $category->id }}">
                                            <input type="checkbox"
                                                onclick="location.href='{{ route('admin.sabores.dineout-categories.toggle-status', $category->id) }}'"
                                                class="toggle-switch-input"
                                                id="statusCheckbox{{ $category->id }}"
                                                {{ $category->status ? 'checked' : '' }}>
                                            <span class="toggle-switch-label mx-auto">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                    </td>
                                    <td>
                                        <div class="btn--container justify-content-center">
                                            <a class="btn action-btn btn-outline-info"
                                                href="{{ route('admin.sabores.dineout-categories.stores', $category->id) }}"
                                                title="{{ translate('Assign Stores') }}">
                                                <i class="tio-shop"></i>
                                            </a>
                                            <a class="btn action-btn btn-outline-primary"
                                                href="{{ route('admin.sabores.dineout-categories.edit', $category->id) }}"
                                                title="{{ translate('Edit') }}">
                                                <i class="tio-edit"></i>
                                            </a>
                                            <a class="btn action-btn btn--danger btn-outline-danger"
                                                href="javascript:"
                                                onclick="form_alert('category-{{ $category['id'] }}','{{ translate('Want to delete this category?') }}')"
                                                title="{{ translate('Delete') }}">
                                                <i class="tio-delete-outlined"></i>
                                            </a>
                                            <form action="{{ route('admin.sabores.dineout-categories.delete', $category->id) }}"
                                                method="post" id="category-{{ $category['id'] }}">
                                                @csrf @method('delete')
                                            </form>
                                        </div>
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
                        {!! $categories->withQueryString()->links() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
