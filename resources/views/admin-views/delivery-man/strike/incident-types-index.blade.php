@extends('layouts.admin.app')

@section('title', translate('messages.dm_strike_incident_types_title'))

@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <h1 class="page-header-title">{{ translate('messages.dm_strike_incident_types_title') }}</h1>
        </div>
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="card-title mb-0">{{ translate('messages.dm_strike_incident_type_add') }}</h5>
            </div>
            <div class="card-body">
                <form method="post" action="{{ route('admin.users.delivery-man.strike.incident-types.store') }}">
                    @csrf
                    <div class="row g-2">
                        <div class="col-md-3">
                            <label class="input-label">{{ translate('messages.dm_strike_code') }}</label>
                            <input type="text" name="code" class="form-control" value="{{ old('code') }}" required maxlength="64">
                        </div>
                        <div class="col-md-4">
                            <label class="input-label">{{ translate('messages.Name') }}</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required maxlength="191">
                        </div>
                        <div class="col-md-2">
                            <label class="input-label">{{ translate('messages.dm_strike_weight') }}</label>
                            <input type="number" name="weight" class="form-control" value="{{ old('weight', 1) }}" min="0" required>
                        </div>
                        <div class="col-md-2">
                            <label class="input-label">{{ translate('messages.dm_strike_sort') }}</label>
                            <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', 0) }}" min="0">
                        </div>
                        <div class="col-md-6 d-flex align-items-end flex-wrap gap-3">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="gen_strike" name="generates_strike" value="1" checked>
                                <label class="custom-control-label" for="gen_strike">{{ translate('messages.dm_strike_generates_strike') }}</label>
                            </div>
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="active_t" name="active" value="1" checked>
                                <label class="custom-control-label" for="active_t">{{ translate('messages.Active') }}</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn--primary">{{ translate('submit') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        @foreach ($types as $t)
            <div class="card mb-2">
                <div class="card-body">
                    <form method="post" action="{{ route('admin.users.delivery-man.strike.incident-types.update', $t->id) }}">
                        @csrf
                        <div class="row g-2 align-items-end">
                            <div class="col-md-2">
                                <label class="input-label text-muted">{{ translate('messages.dm_strike_code') }}</label>
                                <p class="mb-0 font-weight-bold font-monospace">{{ $t->code }}</p>
                            </div>
                            <div class="col-md-4">
                                <label class="input-label">{{ translate('messages.Name') }}</label>
                                <input type="text" name="name" class="form-control" value="{{ $t->name }}" required maxlength="191">
                            </div>
                            <div class="col-md-2">
                                <label class="input-label">{{ translate('messages.dm_strike_weight') }}</label>
                                <input type="number" name="weight" class="form-control" value="{{ $t->weight }}" min="0" required>
                            </div>
                            <div class="col-md-2">
                                <label class="input-label">{{ translate('messages.dm_strike_sort') }}</label>
                                <input type="number" name="sort_order" class="form-control" value="{{ $t->sort_order }}" min="0">
                            </div>
                            <div class="col-md-2">
                                <div class="custom-control custom-checkbox mb-2">
                                    <input type="checkbox" class="custom-control-input" id="gs_{{ $t->id }}" name="generates_strike" value="1" @checked($t->generates_strike)>
                                    <label class="custom-control-label" for="gs_{{ $t->id }}">{{ translate('messages.dm_strike_generates_strike') }}</label>
                                </div>
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="ac_{{ $t->id }}" name="active" value="1" @checked($t->active)>
                                    <label class="custom-control-label" for="ac_{{ $t->id }}">{{ translate('messages.Active') }}</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-sm btn-outline-primary">{{ translate('messages.update') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
@endsection
