@extends('layouts.admin.app')

@section('title', 'editar cliente')

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <i class="tio-edit"></i>
                </span>
                <span>
                    {{'editar cliente'}}
                </span>
            </h1>
        </div>
        <!-- End Page Header -->

        <div class="card">
            <div class="card-body">
                <form action="{{route('admin.customer.update', [$customer['id']])}}" method="post">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="input-label">{{'nombre de pila'}}</label>
                                <input type="text" name="f_name" value="{{$customer['f_name']}}" class="form-control"
                                    placeholder="{{'nombre de pila'}}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="input-label">{{'apellido'}}</label>
                                <input type="text" name="l_name" value="{{$customer['l_name']}}" class="form-control"
                                    placeholder="{{'apellido'}}" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="input-label">{{'correo electrónico'}}</label>
                                <input type="email" name="email" value="{{$customer['email']}}" class="form-control"
                                    placeholder="{{'correo electrónico'}}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="input-label">{{'teléfono'}}</label>
                                <input type="text" name="phone" value="{{$customer['phone']}}" class="form-control"
                                    placeholder="{{'teléfono'}}" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="input-label">{{'código de referencia'}}</label>
                                <input type="text" name="ref_code" value="{{$customer['ref_code']}}" class="form-control"
                                    placeholder="{{'código de referencia'}}" required>
                            </div>
                            <small
                                class="text-info">{{'este código se mostrará en la aplicación para este usuario'}}</small>
                        </div>
                    </div>

                    <div class="btn--container justify-content-end mt-3">
                        <button type="reset" class="btn btn--reset">{{'reiniciar'}}</button>
                        <button type="submit" class="btn btn--primary">{{'actualizar'}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection