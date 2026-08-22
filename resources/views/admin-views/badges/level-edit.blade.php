@extends('layouts.admin.app')

@section('title', 'Editar Nivel — ' . $level->name . ' ' . $level->sub_level)

@section('content')
<div class="content container-fluid">
    <div class="page-header">
        <h1 class="page-header-title">
            <span>Editar Nivel {{ $level->name }} {{ $level->sub_level }}</span>
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.badges.list') }}">Insignias</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.badge-levels.list') }}">Niveles</a></li>
                <li class="breadcrumb-item active">Editar</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-md-7">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.badge-levels.update', $level->id) }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="input-label">Tier (nombre) <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control"
                                        value="{{ old('name', $level->name) }}" required maxlength="100">
                                    <small class="text-muted">Ej: Pochteca, Jaguar, Águila Real</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="input-label">Sub-nivel <span class="text-danger">*</span></label>
                                    <select name="sub_level" class="form-control">
                                        @foreach(['I','II','III'] as $sub)
                                        <option value="{{ $sub }}" {{ $level->sub_level === $sub ? 'selected' : '' }}>{{ $sub }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="input-label">XP requerido <span class="text-danger">*</span></label>
                                    <input type="number" name="xp_required" class="form-control"
                                        value="{{ old('xp_required', $level->xp_required) }}" required min="0">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="input-label">Color gradiente inicio</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text p-1">
                                                <input type="color" id="color_from_picker" value="{{ $level->color_from }}"
                                                    style="width:32px;height:32px;border:none;padding:0;cursor:pointer">
                                            </span>
                                        </div>
                                        <input type="text" name="color_from" id="color_from" class="form-control"
                                            value="{{ old('color_from', $level->color_from) }}" required maxlength="7">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="input-label">Color gradiente fin</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text p-1">
                                                <input type="color" id="color_to_picker" value="{{ $level->color_to }}"
                                                    style="width:32px;height:32px;border:none;padding:0;cursor:pointer">
                                            </span>
                                        </div>
                                        <input type="text" name="color_to" id="color_to" class="form-control"
                                            value="{{ old('color_to', $level->color_to) }}" required maxlength="7">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 d-flex align-items-end pb-3">
                                <div id="level-preview" style="
                                    background: linear-gradient(135deg, {{ $level->color_from }}, {{ $level->color_to }});
                                    color: #fff;
                                    border-radius: 10px;
                                    padding: 12px 20px;
                                    font-weight: bold;
                                    width: 100%;
                                    text-align: center;
                                ">{{ $level->name }} {{ $level->sub_level }}</div>
                            </div>
                        </div>

                        <div class="btn--container justify-content-end mt-3">
                            <a href="{{ route('admin.badge-levels.list') }}" class="btn btn--reset">Cancelar</a>
                            <button type="submit" class="btn btn--primary">Guardar cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    function updateLevelPreview() {
        const from = $('#color_from').val();
        const to   = $('#color_to').val();
        $('#level-preview').css('background', `linear-gradient(135deg, ${from}, ${to})`);
    }
    $('#color_from_picker').on('input', function () { $('#color_from').val(this.value); updateLevelPreview(); });
    $('#color_from').on('input', function () { if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) { $('#color_from_picker').val(this.value); updateLevelPreview(); } });
    $('#color_to_picker').on('input', function () { $('#color_to').val(this.value); updateLevelPreview(); });
    $('#color_to').on('input', function () { if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) { $('#color_to_picker').val(this.value); updateLevelPreview(); } });
</script>
@endpush
