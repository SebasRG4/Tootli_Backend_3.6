@extends('layouts.admin.app')

@section('title','módulos')

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">{{'tipo de módulo'}}</h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <div class="card">
            <div class="card-header"><h5>{{'agregar nuevo módulo'}}</h5></div>
            <div class="card-body">
                <form action="{{route('admin.module.create')}}" method="get" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label class="input-label" for="exampleFormControlInput1">{{'tipo'}}</label>
                        <input type="text" name="module_type" class="form-control" placeholder="{{'nueva categoría'}}" value="{{old('name')}}" required maxlength="191">
                    </div>

                    <div class="form-group">
                        <label class="input-label" for="exampleFormControlInput1">{{'descripción'}}</label>
                        <textarea class="ckeditor form-control" name="module_description"></textarea>
                    </div>

                    <div class="form-group pt-2">
                        <button type="submit" class="btn btn-primary">{{'agregar'}}</button>
                    </div>

                </form>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header pb-0">
                <h5>{{'lista de tipos de módulos'}}</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive datatable-custom">
                    <table id="columnSearchDatatable"
                        class="table table-borderless table-thead-bordered table-align-middle" data-hs-datatables-options='{
                            "isResponsive": false,
                            "isShowPaging": false,
                            "paging":false,
                        }'>
                        <thead class="thead-light">
                            <tr>
                                <th>{{'tipo de módulo'}}</th>
                                <th>{{'descripción'}}</th>
                            </tr>
                        </thead>

                        <tbody id="table-div">
                        @foreach($module_type as $key=>$module)
                            <tr>
                                <td>
                                    <span class="d-block font-size-sm text-body">
                                        {{Str::limit($module['module_type'], 20,'...')}}
                                    </span>
                                </td>
                                <td>
                                    {!! $module->description !!}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

@endsection

@push('script_2')
    <script src="{{asset('assets/admin/ckeditor/ckeditor.js')}}"></script>
    <script>
        "use strict";
        $(document).ready(function () {
            $('.ckeditor').ckeditor();
        });
    </script>
@endpush
