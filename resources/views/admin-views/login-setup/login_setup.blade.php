@extends('layouts.admin.app')

@section('title','configuración de la página de inicio de sesión')

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header d-flex flex-wrap align-items-center justify-content-between">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('assets/admin/img/app.png')}}" class="w--26" alt="">
                </span>
                <span>
                    {{'configuración de inicio de sesión'}}
                </span>
            </h1>
        </div>
        <!-- End Page Header -->

        <ul class="nav nav-tabs border-0 nav--tabs nav--pills mb-4">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.business-settings.login-settings.index') }}">{{'Inicio de sesión del cliente'}}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="{{ route('admin.business-settings.login_url_page') }}">{{'URL de la página de inicio de sesión del panel'}}</a>
            </li>
        </ul>


        <form action="{{route('admin.business-settings.login_url_update')}}" method="post">
        @csrf
            <h5 class="card-title mb-3 pt-3">
                <span class="card-header-icon mr-2"><i class="tio-settings-outlined"></i></span> <span>{{ 'Página de inicio de sesión de administrador' }}</span>
            </h5>
            <div class="card">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <h5 class="card-title mb-3">
                            </h5>
                            <input type="text" hidden  name="type" value="admin">
                            <div class="__bg-F8F9FC-card">
                                <div class="form-group">
                                    <label  class="form-label">
                                        {{'URL de inicio de sesión de administrador'}}
                                        <span class="input-label-secondary text--title" data-toggle="tooltip"
                                        data-placement="right"
                                        data-original-title="{{ 'Agregue una URL dinámica para proteger el acceso de inicio de sesión del administrador.' }}">
                                            <i class="tio-info-outined"></i>
                                        </span>
                                    </label>
                                    <div class="input-group-prepend">
                                        <div class="input-group-text">{{ url('/') }}/login/</div>
                                        <input type="text" placeholder="{{'URL de inicio de sesión de administrador'}}" class="form-control h--45px" name="admin_login_url"
                                                required value="{{ $data['admin_login_url'] ?? null  }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="btn--container justify-content-end mt-20">
                        <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}"  class="btn btn--primary mb-2 call-demo" >{{'entregar'}}</button>
                    </div>
                </div>
            </div>
        </form>
        <form action="{{route('admin.business-settings.login_url_update')}}" method="post">
            @csrf
            <h5 class="card-title mb-3 pt-3">
                <span class="card-header-icon mr-2"><i class="tio-settings-outlined"></i></span> <span>{{ 'página de inicio de sesión del empleado administrador' }}</span>
            </h5>
            <div class="card">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <h5 class="card-title mb-3">
                            </h5>
                            <input type="text" hidden  name="type" value="admin_employee">

                            <div class="__bg-F8F9FC-card">
                                <div class="form-group">
                                    <label  class="form-label">
                                        {{'URL de inicio de sesión del empleado administrador'}}
                                        <span class="input-label-secondary text--title" data-toggle="tooltip"
                                        data-placement="right"
                                        data-original-title="{{ 'Agregue una URL dinámica para proteger el acceso de inicio de sesión de los empleados administradores.' }}">
                                            <i class="tio-info-outined"></i>
                                        </span>
                                    </label>
                                    <div class="input-group-prepend">
                                        <div class="input-group-text">{{ url('/') }}/login/</div>
                                        <input type="text" placeholder="{{'URL de inicio de sesión del empleado administrador'}}" class="form-control h--45px" name="admin_employee_login_url"
                                                required value="{{ $data['admin_employee_login_url'] ?? null  }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="btn--container justify-content-end mt-20">
                        <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}"  class="btn btn--primary mb-2 call-demo">{{'entregar'}}</button>
                    </div>
                </div>
            </div>
        </form>
        <form action="{{route('admin.business-settings.login_url_update')}}" method="post">
            @csrf
            <h5 class="card-title mb-3 pt-3">
                <span class="card-header-icon mr-2"><i class="tio-settings-outlined"></i></span> <span>{{ 'página de inicio de sesión de la tienda' }}</span>
            </h5>
            <div class="card">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <h5 class="card-title mb-3">
                            </h5>
                            <input type="text" hidden  name="type" value="store">

                            <div class="__bg-F8F9FC-card">
                                <div class="form-group">
                                    <label  class="form-label">
                                        {{'URL de inicio de sesión de la tienda'}}
                                        <span class="input-label-secondary text--title" data-toggle="tooltip"
                                        data-placement="right"
                                        data-original-title="{{ 'Agregue una URL dinámica para proteger el acceso a la tienda.' }}">
                                            <i class="tio-info-outined"></i>
                                        </span>
                                    </label>
                                    <div class="input-group-prepend">
                                        <div class="input-group-text">{{ url('/') }}/login/</div>
                                        <input type="text" placeholder="{{'URL de inicio de sesión de la tienda'}}" class="form-control h--45px" name="store_login_url"
                                        required value="{{ $data['store_login_url'] ?? null  }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="btn--container justify-content-end mt-20">
                        <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}" class="btn btn--primary mb-2 call-demo">{{'entregar'}}</button>
                    </div>
                </div>
            </div>
        </form>
        <form action="{{route('admin.business-settings.login_url_update')}}" method="post">
            @csrf
            <h5 class="card-title mb-3 pt-3">
                <span class="card-header-icon mr-2"><i class="tio-settings-outlined"></i></span> <span>{{ 'página de inicio de sesión del empleado de la tienda' }}</span>
            </h5>
            <div class="card">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <h5 class="card-title mb-3">
                            </h5>
                            <input type="text" hidden  name="type" value="store_employee">

                            <div class="__bg-F8F9FC-card">
                                <div class="form-group">
                                    <label  class="form-label">
                                        {{'URL de inicio de sesión del empleado de la tienda'}}
                                        <span class="input-label-secondary text--title" data-toggle="tooltip"
                                        data-placement="right"
                                        data-original-title="{{ 'Agregue una URL dinámica para proteger el acceso de inicio de sesión de los empleados de la tienda.' }}">
                                            <i class="tio-info-outined"></i>
                                        </span>
                                    </label>
                                    <div class="input-group-prepend">
                                        <div class="input-group-text">{{ url('/') }}/login/</div>
                                        <input type="text" placeholder="{{'URL de inicio de sesión del empleado de la tienda'}}" class="form-control h--45px" name="store_employee_login_url"
                                                required value="{{ $data['store_employee_login_url'] ?? null  }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="btn--container justify-content-end mt-20">
                        <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}" class="btn btn--primary mb-2 call-demo">{{'entregar'}}</button>
                    </div>
                </div>
            </div>
        </form>



    </div>

@endsection

@push('script_2')

@endpush
