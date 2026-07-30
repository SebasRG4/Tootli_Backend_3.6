@extends('layouts.admin.app')

@section('title', 'Editar reseña')

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title"><i class="tio-edit"></i> {{ 'Editar reseña' }}</h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('admin.sabores.reviews.update', [$review['id']]) }}" method="post">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="input-label"
                                            for="exampleFormControlInput1">{{ 'Artículo' }}</label>
                                        <input type="text" class="form-control"
                                            value="{{ $review->item ? $review->item->name : 'Item Not Found' }}" disabled>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="input-label"
                                            for="exampleFormControlInput1">{{ 'Cliente' }}</label>
                                        <input type="text" class="form-control"
                                            value="{{ $review->customer ? $review->customer->f_name . ' ' . $review->customer->l_name : 'Customer Not Found' }}"
                                            disabled>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="input-label"
                                            for="exampleFormControlInput1">{{ 'Clasificación' }}</label>
                                        <input type="text" class="form-control" value="{{ $review->rating }}" disabled>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="input-label">{{ 'Estado' }}</label>
                                        <select name="status" class="form-control">
                                            <option value="1" {{ $review->status == 1 ? 'selected' : '' }}>
                                                {{ 'Activo' }}</option>
                                            <option value="0" {{ $review->status == 0 ? 'selected' : '' }}>
                                                {{ 'Obstruido' }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="input-label">{{ 'Revisar' }}</label>
                                <textarea name="comment" class="form-control" rows="5">{{ $review->comment }}</textarea>
                            </div>

                            <div class="btn--container justify-content-end">
                                <button type="reset" class="btn btn--reset">{{ 'reiniciar' }}</button>
                                <button type="submit" class="btn btn--primary">{{ 'entregar' }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection