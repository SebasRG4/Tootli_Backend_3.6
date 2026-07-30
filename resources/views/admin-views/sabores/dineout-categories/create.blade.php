@extends('layouts.admin.app')

@section('title', 'Agregar categoría para cenar fuera')

@push('css_or_js')
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <i class="tio-add-circle-outlined"></i>
                </span>
                <span>
                    {{ 'Agregar nueva categoría para cenar fuera' }}
                </span>
            </h1>
        </div>
        <!-- End Page Header -->

        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.sabores.dineout-categories.store') }}" method="post"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="input-label" for="name">{{ 'nombre' }}</label>
                                <input type="text" name="name" class="form-control"
                                    placeholder="{{ 'Ej: buena comida' }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="input-label" for="image">{{ 'Icono (Emoji)' }}</label>
                                <input type="text" name="image" class="form-control"
                                    placeholder="{{ 'Ej: 🍽️' }}" required>
                                <small
                                    class="text-muted">{{ 'Copie y pegue un emoji aquí para usarlo como ícono' }}</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="input-label" for="position">{{ 'Posición' }}</label>
                                <input type="number" name="position" class="form-control"
                                    placeholder="{{ 'Ej: 1' }}">
                            </div>
                        </div>
                    </div>

                    <div class="btn--container justify-content-end mt-3">
                        <button type="reset" class="btn btn--reset">{{ 'reiniciar' }}</button>
                        <button type="submit" class="btn btn--primary">{{ 'entregar' }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection