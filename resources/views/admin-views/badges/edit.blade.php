@extends('layouts.admin.app')

@section('title', 'Editar Insignia — ' . $badge->title)

@push('css_or_js')
<style>
    .icon-option {
        cursor: pointer;
        padding: 8px;
        border: 2px solid transparent;
        border-radius: 8px;
        text-align: center;
        transition: all .2s;
    }
    .icon-option:hover, .icon-option.selected {
        border-color: #5d78ff;
        background: #f0f2ff;
    }
    .icon-option small { display: block; font-size: 10px; margin-top: 4px; }
    .icon-preview {
        width: 56px; height: 56px;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 24px;
        border: 2px solid #dee2e6;
        transition: all .2s;
    }
</style>
@endpush

@section('content')
<div class="content container-fluid">
    <div class="page-header">
        <h1 class="page-header-title">
            <span class="page-header-icon">
                <img src="{{ asset('assets/admin/img/condition.png') }}" class="w--26" alt="">
            </span>
            <span>Editar Insignia</span>
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.badges.list') }}">Insignias</a></li>
                <li class="breadcrumb-item active">{{ $badge->title }}</li>
            </ol>
        </nav>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.badges.update', $badge->id) }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="input-label" for="key">Clave única <span class="text-danger">*</span></label>
                            <input type="text" name="key" id="key" class="form-control @error('key') is-invalid @enderror"
                                value="{{ old('key', $badge->key) }}" required maxlength="100">
                            @error('key')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="input-label" for="title">Título <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror"
                                value="{{ old('title', $badge->title) }}" required maxlength="191">
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="input-label" for="sort_order">Orden</label>
                            <input type="number" name="sort_order" id="sort_order" class="form-control"
                                value="{{ old('sort_order', $badge->sort_order) }}" min="0">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="input-label" for="description">Descripción</label>
                    <textarea name="description" id="description" class="form-control" rows="2">{{ old('description', $badge->description) }}</textarea>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="input-label" for="condition_type">Tipo de condición <span class="text-danger">*</span></label>
                            <select name="condition_type" id="condition_type" class="form-control js-select2-custom" required>
                                @foreach([
                                    'trips'          => 'Viajes totales',
                                    'food_deliveries'=> 'Entregas de comida',
                                    'rating'         => 'Calificación promedio',
                                    'streak'         => 'Racha de días consecutivos',
                                    'tips'           => 'Propinas consecutivas',
                                    'night_trips'    => 'Entregas nocturnas',
                                    'weekend_trips'  => 'Entregas en fin de semana',
                                    'earnings'       => 'Ganancias semanales ($)',
                                    'perfect_week'   => 'Semana perfecta',
                                ] as $value => $label)
                                    <option value="{{ $value }}" {{ old('condition_type', $badge->condition_type) === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="input-label" for="condition_value">Valor umbral <span class="text-danger">*</span></label>
                            <input type="number" name="condition_value" id="condition_value" class="form-control"
                                value="{{ old('condition_value', $badge->condition_value) }}" required min="1">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="input-label" for="xp_reward">XP que otorga <span class="text-danger">*</span></label>
                            <input type="number" name="xp_reward" id="xp_reward" class="form-control"
                                value="{{ old('xp_reward', $badge->xp_reward) }}" required min="0">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="input-label">Color de fondo</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text p-1">
                                        <input type="color" id="color_picker_bg" value="{{ old('color_hex', $badge->color_hex) }}"
                                            style="width:32px;height:32px;border:none;padding:0;cursor:pointer">
                                    </span>
                                </div>
                                <input type="text" name="color_hex" id="color_hex" class="form-control"
                                    value="{{ old('color_hex', $badge->color_hex) }}" required maxlength="7">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="input-label">Color de ícono</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text p-1">
                                        <input type="color" id="color_picker_icon" value="{{ old('icon_color_hex', $badge->icon_color_hex) }}"
                                            style="width:32px;height:32px;border:none;padding:0;cursor:pointer">
                                    </span>
                                </div>
                                <input type="text" name="icon_color_hex" id="icon_color_hex" class="form-control"
                                    value="{{ old('icon_color_hex', $badge->icon_color_hex) }}" required maxlength="7">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 d-flex align-items-end pb-3">
                        <div>
                            <label class="input-label d-block">Vista previa</label>
                            <div id="badge-preview" class="icon-preview"
                                style="background: {{ $badge->color_hex }}; color: {{ $badge->icon_color_hex }}">★</div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="input-label">Ícono</label>
                    <input type="hidden" name="icon" id="icon_value" value="{{ old('icon', $badge->icon) }}" required>
                    <div class="row g-2" id="icon-selector">
                        @foreach([
                            'bullseye'=>['symbol'=>'🎯','label'=>'Diana'],
                            'laurel'=>['symbol'=>'⭐','label'=>'Laurel'],
                            'car'=>['symbol'=>'🚗','label'=>'Auto'],
                            'trophy'=>['symbol'=>'🏆','label'=>'Trofeo'],
                            'bag'=>['symbol'=>'🛍️','label'=>'Bolsa'],
                            'pizza'=>['symbol'=>'🍕','label'=>'Pizza'],
                            'calendar'=>['symbol'=>'📅','label'=>'Calendario'],
                            'star'=>['symbol'=>'⭐','label'=>'Estrella'],
                            'coins'=>['symbol'=>'🪙','label'=>'Monedas'],
                            'moon'=>['symbol'=>'🌙','label'=>'Luna'],
                            'lightning'=>['symbol'=>'⚡','label'=>'Rayo'],
                            'trending_up'=>['symbol'=>'📈','label'=>'Tendencia'],
                        ] as $iconKey => $icon)
                        <div class="col-2 col-md-1">
                            <div class="icon-option {{ old('icon', $badge->icon) === $iconKey ? 'selected' : '' }}"
                                data-icon="{{ $iconKey }}" title="{{ $icon['label'] }}">
                                <span style="font-size:24px">{{ $icon['symbol'] }}</span>
                                <small>{{ $icon['label'] }}</small>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="btn--container justify-content-end mt-3">
                    <a href="{{ route('admin.badges.list') }}" class="btn btn--reset">Cancelar</a>
                    <button type="submit" class="btn btn--primary">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    $('#color_picker_bg').on('input', function () { $('#color_hex').val(this.value); updatePreview(); });
    $('#color_hex').on('input', function () { if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) { $('#color_picker_bg').val(this.value); updatePreview(); } });
    $('#color_picker_icon').on('input', function () { $('#icon_color_hex').val(this.value); updatePreview(); });
    $('#icon_color_hex').on('input', function () { if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) { $('#color_picker_icon').val(this.value); updatePreview(); } });
    function updatePreview() {
        $('#badge-preview').css({ background: $('#color_hex').val(), color: $('#icon_color_hex').val() });
    }
    $(document).on('click', '.icon-option', function () {
        $('.icon-option').removeClass('selected');
        $(this).addClass('selected');
        $('#icon_value').val($(this).data('icon'));
    });
</script>
@endpush
