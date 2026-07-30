@extends('layouts.admin.app')

@section('title', 'Agregar categoría de complemento')

@push('css_or_js')
@endpush

@section('content')
    <div id="content-disable" class="content container-fluid ">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{ asset('assets/admin/img/category.png') }}" class="w--20" alt="">
                </span>
                <span>
                    {{ 'agregar nueva categoría de complemento' }}
                </span>
            </h1>
        </div>
        <!-- End Page Header -->

        <div  class="card">
            <div class="card-body">
                <form action="{{ route('admin.addon.addon-category-store') }}" method="post">
                    @csrf
                    @if ($language)
                        <ul class="nav nav-tabs mb-4 border-0">
                            <li class="nav-item">
                                <a class="nav-link lang_link active offcanvas-close" href="#"
                                    id="default-link">{{ 'por defecto' }}</a>
                            </li>
                            @foreach ($language as $lang)
                                <li class="nav-item">
                                    <a class="nav-link lang_link offcanvas-close" href="#"
                                        id="{{ $lang }}-link">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                    <div class="row">
                        <div class="col-md-6">
                            @if ($language)
                                <div class="form-group lang_form" id="default-form">
                                    <label class="input-label"
                                        for="exampleFormControlInput1">{{ 'Nombre de categoría' }}
                                        ({{ 'por defecto' }})
                                        <span class="form-label-secondary text-danger" data-toggle="tooltip"
                                            data-placement="right"
                                            data-original-title="{{ 'Requerido.' }}"> *
                                        </span>

                                    </label>
                                    <input type="text" name="name[]" value="{{ old('name.0') }}" class="form-control"
                                        placeholder="{{ 'nueva categoría' }}" maxlength="255">
                                </div>
                                <input type="hidden" name="lang[]" value="default">
                                @foreach ($language as $key => $lang)
                                    <div class="form-group d-none lang_form" id="{{ $lang }}-form">
                                        <label class="input-label"
                                            for="exampleFormControlInput1">{{ 'Nombre de categoría' }}
                                            ({{ strtoupper($lang) }})
                                        </label>
                                        <input type="text" name="name[]" value="{{ old('name.' . $key + 1) }}"
                                            class="form-control"
                                            placeholder="{{ 'Tipo Nombre de categoría' }}" maxlength="191">
                                    </div>
                                    <input type="hidden" name="lang[]" value="{{ $lang }}">
                                @endforeach
                            @else
                                <div class="form-group">
                                    <label class="input-label"
                                        for="exampleFormControlInput1">{{ 'Nombre de categoría' }}</label>
                                    <input type="text" name="name" class="form-control"
                                        placeholder="{{ 'nueva categoría' }}"
                                        value="{{ old('name') }}" maxlength="191">
                                </div>
                                <input type="hidden" name="lang[]" value="default">
                            @endif

                        </div>
                        <div class="col-md-6">
                            @if ($categoryWiseTax)
                                <span class="mb-2 d-block title-clr fw-normal">{{ 'Seleccionar tasa impositiva' }}</span>
                                <select name="tax_ids[]" required id="tax__rate" class="form-control js-select2-custom"
                                    multiple="multiple" placeholder="Type & Select Tax Rate">
                                    @foreach ($taxVats as $taxVat)
                                        <option value="{{ $taxVat->id }}"> {{ $taxVat->name }}
                                            ({{ $taxVat->tax_rate }}%)
                                        </option>
                                    @endforeach
                                </select>
                            @endif
                        </div>

                    </div>
                    <div class="btn--container justify-content-end mt-20">
                        <button type="reset" id="reset_btn"
                            class="btn btn--reset">{{ 'reiniciar' }}</button>
                        <button type="submit" class="btn btn--primary">{{ 'agregar' }}</button>
                    </div>

                </form>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header py-2 border-0">
                <div class="search--button-wrapper">
                    <h5 class="card-title">{{ 'lista de categorías' }}<span
                            class="badge badge-soft-dark ml-2" id="itemCount">{{ $categories->total() }}</span></h5>

                    <form class="search-form">
                        <!-- Search -->
                        <div class="input-group input--group">
                            <input type="search" name="search" value="{{ request()?->search ?? null }}"
                                class="form-control min-height-45" placeholder="{{ 'buscar aquí' }}"
                                aria-label="{{ 'ej: categorías' }}">
                            <input type="hidden" name="position" value="0">
                            <button type="submit" class="btn btn--secondary min-height-45"><i
                                    class="tio-search"></i></button>
                        </div>
                        <!-- End Search -->
                    </form>

                    <!-- Unfold -->
                    <div class="hs-unfold mr-2">
                        <a class="js-hs-unfold-invoker btn btn-sm btn-white dropdown-toggle min-height-40"
                            href="javascript:;"
                            data-hs-unfold-options='{
                                    "target": "#usersExportDropdown",
                                    "type": "css-animation"
                                }'>
                            <i class="tio-download-to mr-1"></i> {{ 'exportar' }}
                        </a>

                        <div id="usersExportDropdown"
                            class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-sm-right">

                            <span class="dropdown-header">{{ 'opciones de descarga' }}</span>
                            <a id="export-excel" class="dropdown-item"
                                href="{{ route('admin.addon.addon-category-export', ['type' => 'excel', request()->getQueryString()]) }}">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                    src="{{ asset('assets/admin') }}/svg/components/excel.svg"
                                    alt="Image Description">
                                {{ 'sobresalir' }}
                            </a>
                            <a id="export-csv" class="dropdown-item"
                                href="{{ route('admin.addon.addon-category-export', ['type' => 'csv', request()->getQueryString()]) }}">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                    src="{{ asset('assets/admin') }}/svg/components/placeholder-csv-format.svg"
                                    alt="Image Description">
                                .{{ 'csv' }}
                            </a>

                        </div>
                    </div>
                    <!-- End Unfold -->
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
                        <thead class="thead-light">
                            <tr>
                                <th class="border-0">{{ 'SL' }}</th>
                                <th class="border-0">{{ 'identificación' }}</th>
                                <th class="">{{ 'Nombre de categoría' }}</th>
                                @if ($categoryWiseTax)
                                    <th class="border-0 w--1">{{ 'IVA/Impuesto' }}</th>
                                @endif
                                <th class="border-0 text-center">{{ 'estado' }}</th>
                                <th class="border-0 text-center">{{ 'acción' }}</th>
                            </tr>
                        </thead>

                        <tbody id="table-div">
                            @foreach ($categories as $key => $category)
                                <tr>
                                    <td>{{ $key + $categories->firstItem() }}</td>
                                    <td>{{ $category->id }}</td>
                                    <td>
                                        {{ Str::limit($category['name'], 20, '...') }}

                                    </td>

                                    @if ($categoryWiseTax)
                                        <td>
                                            <span class="d-block font-size-sm text-body">

                                                @forelse ($category?->taxVats?->pluck('tax.name', 'tax.tax_rate')->toArray() as $key => $item)
                                                    <span> {{ $item }} : <span class="font-bold">
                                                            ({{ $key }}%)
                                                        </span> </span>
                                                    <br>
                                                @empty
                                                    <span> {{ 'sin impuestos' }} </span>
                                                @endforelse
                                            </span>
                                        </td>
                                    @endif

                                    <td>
                                        <label class="toggle-switch toggle-switch-sm"
                                            for="stocksCheckbox{{ $category->id }}">
                                            <input type="checkbox"
                                                data-url="{{ route('admin.addon.addon-category-status', [$category->id]) }}"
                                                class="toggle-switch-input redirect-url"
                                                id="stocksCheckbox{{ $category->id }}"
                                                {{ $category->status ? 'checked' : '' }}>
                                            <span class="toggle-switch-label mx-auto">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                    </td>

                                    <td>
                                        <div class="btn--container justify-content-center">
                                            <a class="btn btn-sm text-end action-btn info--outline text--info info-hover offcanvas-trigger get_data data-info-show"
                                                data-target="#offcanvas__customBtn3" data-id="{{ $category['id'] }}"
                                                data-url="{{ route('admin.addon.addon-category-edit', [$category['id']]) }}"
                                                href="javascript:" title="{{ 'editar categoría' }}"><i
                                                    class="tio-edit"></i>
                                            </a>
                                            <a class="btn action-btn btn--danger btn-outline-danger form-alert"
                                                href="javascript:" data-id="category-{{ $category['id'] }}"
                                                data-message="{{ 'Quiere eliminar esta categoría' }}"
                                                title="{{ 'eliminar categoría' }}"><i
                                                    class="tio-delete-outlined"></i>
                                            </a>
                                        </div>
                                        <form
                                            action="{{ route('admin.addon.addon-category-delete', [$category['id']]) }}"
                                            method="post" id="category-{{ $category['id'] }}">
                                            @csrf @method('delete')
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @if (count($categories) !== 0)
                <hr>
            @endif
            <div class="page-area">
                {!! $categories->appends($_GET)->links() !!}
            </div>
            @if (count($categories) === 0)
                <div class="empty--data">
                    <img src="{{ asset('assets/admin/svg/illustrations/sorry.svg') }}" alt="public">
                    <h5>
                        {{ 'no se encontraron datos' }}
                    </h5>
                </div>
            @endif
        </div>

    </div>

    <div id="offcanvas__customBtn3" class="custom-offcanvas d-flex flex-column justify-content-between">
        <div id="data-view" class="h-100">
        </div>
    </div>



@endsection

@push('script_2')
    <script>
        "use strict";
        document.getElementById('reset_btn').addEventListener('click', function() {
            const select = $('#tax__rate');
            select.val(null).trigger('change');
        });


        $(document).on('click', '.data-info-show', function() {
            let id = $(this).data('id');
            let url = $(this).data('url');
            $('#content-disable').addClass('disabled');
            fetch_data(id, url)
        })

        function fetch_data(id, url) {
            $.ajax({
                url: url,
                type: "get",
                beforeSend: function() {
                    $('#data-view').empty();
                    $('#loading').show()
                },
                success: function(data) {
                    $("#data-view").append(data.view);
                    initLangTabs();
                    initSelect2Dropdowns();
                },
                complete: function() {
                    $('#loading').hide()
                }
            })
        }


        function initLangTabs() {
            const langLinks = document.querySelectorAll(".lang_link1");
            langLinks.forEach(function(langLink) {
                langLink.addEventListener("click", function(e) {
                    e.preventDefault();
                    langLinks.forEach(function(link) {
                        link.classList.remove("active");
                    });
                    this.classList.add("active");
                    document.querySelectorAll(".lang_form1").forEach(function(form) {
                        form.classList.add("d-none");
                    });
                    let form_id = this.id;
                    let lang = form_id.substring(0, form_id.length - 5);
                    $("#" + lang + "-form1").removeClass("d-none");
                    if (lang === "default") {
                        $(".default-form1").removeClass("d-none");
                    }
                });
            });
        }

        function initSelect2Dropdowns() {
            $('.js-select2-custom1').select2({
                placeholder: 'Select tax rate',
                allowClear: true
            });
             $('.offcanvas-close, #offcanvasOverlay').on('click', function () {
        $('.custom-offcanvas').removeClass('open');
        $('#offcanvasOverlay').removeClass('show');
         $('#content-disable').removeClass('disabled');
            });
        }
    </script>
@endpush
