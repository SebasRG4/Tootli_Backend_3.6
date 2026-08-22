@extends('layouts.admin.app')

@section('title', 'Nueva Insignia')

@push('css_or_js')
<style>
    .icon-preview {
        width: 56px; height: 56px;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 24px;
        border: 2px solid #dee2e6;
        transition: all .2s;
    }
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
</style>
@endpush

@section('content')
<div class="content container-fluid">
    <div class="page-header">
        <h1 class="page-header-title">
            <span class="page-header-icon">
                <img src="{{ asset('assets/admin/img/condition.png') }}" class="w--26" alt="">
            </span>
            <span>Nueva Insignia</span>
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.badges.list') }}">Insignias</a></li>
                <li class="breadcrumb-item active">Nueva</li>
            </ol>
        </nav>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.badges.store') }}" method="POST">
                @csrf

                <div class="row">
                    <!-- Clave única -->
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="input-label" for="key">Clave única <span class="text-danger">*</span></label>
                            <input type="text" name="key" id="key" class="form-control @error('key') is-invalid @enderror"
                                placeholder="ej: first_ride" value="{{ old('key') }}" required maxlength="100">
                            <small class="form-text text-muted">Identificador único en snake_case. No se puede repetir.</small>
                            @error('key')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <!-- Título -->
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="input-label" for="title">Título <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror"
                                placeholder="ej: Primer Viaje" value="{{ old('title') }}" required maxlength="191">
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <!-- Orden -->
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="input-label" for="sort_order">Orden de visualización</label>
                            <input type="number" name="sort_order" id="sort_order" class="form-control"
                                placeholder="0" value="{{ old('sort_order', 0) }}" min="0">
                        </div>
                    </div>
                </div>

                <!-- Descripción -->
                <div class="form-group">
                    <label class="input-label" for="description">Descripción (opcional)</label>
                    <textarea name="description" id="description" class="form-control" rows="2"
                        placeholder="Describe cómo se obtiene la insignia">{{ old('description') }}</textarea>
                </div>

                <div class="row">
                    <!-- Tipo de condición -->
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
                                    <option value="{{ $value }}" {{ old('condition_type') === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <!-- Valor de condición -->
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="input-label" for="condition_value">Valor umbral <span class="text-danger">*</span></label>
                            <input type="number" name="condition_value" id="condition_value"
                                class="form-control @error('condition_value') is-invalid @enderror"
                                placeholder="ej: 100" value="{{ old('condition_value', 1) }}" required min="1">
                            <small class="form-text text-muted">El repartidor debe alcanzar este valor para desbloquear.</small>
                            @error('condition_value')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <!-- XP reward -->
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="input-label" for="xp_reward">XP que otorga <span class="text-danger">*</span></label>
                            <input type="number" name="xp_reward" id="xp_reward"
                                class="form-control @error('xp_reward') is-invalid @enderror"
                                placeholder="ej: 50" value="{{ old('xp_reward', 10) }}" required min="0">
                            @error('xp_reward')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Color de fondo -->
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="input-label" for="color_hex">Color de fondo <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text p-1">
                                        <input type="color" id="color_picker_bg" value="{{ old('color_hex', '#E8F0FE') }}"
                                            style="width:32px;height:32px;border:none;padding:0;cursor:pointer">
                                    </span>
                                </div>
                                <input type="text" name="color_hex" id="color_hex" class="form-control"
                                    value="{{ old('color_hex', '#E8F0FE') }}" required maxlength="7" placeholder="#E8F0FE">
                            </div>
                        </div>
                    </div>
                    <!-- Color de ícono -->
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="input-label" for="icon_color_hex">Color de ícono <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text p-1">
                                        <input type="color" id="color_picker_icon" value="{{ old('icon_color_hex', '#1A73E8') }}"
                                            style="width:32px;height:32px;border:none;padding:0;cursor:pointer">
                                    </span>
                                </div>
                                <input type="text" name="icon_color_hex" id="icon_color_hex" class="form-control"
                                    value="{{ old('icon_color_hex', '#1A73E8') }}" required maxlength="7" placeholder="#1A73E8">
                            </div>
                        </div>
                    </div>
                    <!-- Preview -->
                    <div class="col-md-3 d-flex align-items-end pb-3">
                        <div>
                            <label class="input-label d-block">Vista previa</label>
                            <div id="badge-preview" class="icon-preview"
                                style="background: {{ old('color_hex', '#E8F0FE') }}; color: {{ old('icon_color_hex', '#1A73E8') }}">
                                ★
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Selector de ícono -->
                <div class="form-group">
                    <label class="input-label">Ícono <span class="text-danger">*</span></label>
                    <input type="hidden" name="icon" id="icon_value" value="{{ old('icon', 'star') }}" required>
                    <div class="row g-2" id="icon-selector">
                        @foreach([
                            'bullseye'   => ['symbol' => '🎯', 'label' => 'Diana'],
                            'laurel'     => ['symbol' => '⭐', 'label' => 'Laurel'],
                            'car'        => ['symbol' => '🚗', 'label' => 'Auto'],
                            'trophy'     => ['symbol' => '🏆', 'label' => 'Trofeo'],
                            'bag'        => ['symbol' => '🛍️', 'label' => 'Bolsa'],
                            'pizza'      => ['symbol' => '🍕', 'label' => 'Pizza'],
                            'calendar'   => ['symbol' => '📅', 'label' => 'Calendario'],
                            'star'       => ['symbol' => '⭐', 'label' => 'Estrella'],
                            'coins'      => ['symbol' => '🪙', 'label' => 'Monedas'],
                            'moon'       => ['symbol' => '🌙', 'label' => 'Luna'],
                            'lightning'  => ['symbol' => '⚡', 'label' => 'Rayo'],
                            'trending_up'=> ['symbol' => '📈', 'label' => 'Tendencia'],
                        ] as $iconKey => $icon)
                        <div class="col-2 col-md-1">
                            <div class="icon-option {{ old('icon', 'star') === $iconKey ? 'selected' : '' }}"
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
                    <button type="submit" class="btn btn--primary">Guardar insignia</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    // Color pickers sync
    $('#color_picker_bg').on('input', function () {
        $('#color_hex').val(this.value);
        updatePreview();
    });
    $('#color_hex').on('input', function () {
        if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) {
            $('#color_picker_bg').val(this.value);
            updatePreview();
        }
    });
    $('#color_picker_icon').on('input', function () {
        $('#icon_color_hex').val(this.value);
        updatePreview();
    });
    $('#icon_color_hex').on('input', function () {
        if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) {
            $('#color_picker_icon').val(this.value);
            updatePreview();
        }
    });

    function updatePreview() {
        const bg   = $('#color_hex').val();
        const icon = $('#icon_color_hex').val();
        $('#badge-preview').css({ background: bg, color: icon });
    }

    // Icon selector
    $(document).on('click', '.icon-option', function () {
        $('.icon-option').removeClass('selected');
        $(this).addClass('selected');
        $('#icon_value').val($(this).data('icon'));
    });
</script>
@endpush
