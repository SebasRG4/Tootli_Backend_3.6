@php
    $catItem  = $catItem ?? null;
    $catIcons = [
        'tio-folder'           => 'Carpeta',
        'tio-star'             => 'Estrella',
        'tio-rocket'           => 'Cohete',
        'tio-settings'         => 'Ajustes',
        'tio-shopping-cart'    => 'Carrito',
        'tio-user'             => 'Usuario',
        'tio-chart-bar'        => 'Gráfica',
        'tio-laptop'           => 'Laptop',
        'tio-document-text'    => 'Documento',
        'tio-calendar'         => 'Calendario',
        'tio-email'            => 'Email',
        'tio-notification'     => 'Notificación',
        'tio-lock'             => 'Seguridad',
        'tio-globe'            => 'Global',
        'tio-store'            => 'Tienda',
        'tio-truck'            => 'Envío',
        'tio-bag'              => 'Bolsa',
        'tio-flag'             => 'Bandera',
        'tio-world'            => 'Mundo',
        'tio-time'             => 'Tiempo',
        'tio-search'           => 'Buscar',
        'tio-filter'           => 'Filtro',
        'tio-tag'              => 'Etiqueta',
        'tio-format-bullets'   => 'Lista',
    ];

    $catColors = [
        '#4154f1', '#6c5ce7', '#a29bfe', '#fd79a8',
        '#e84393', '#d63031', '#e17055', '#fdcb6e',
        '#55efc4', '#00b894', '#0984e3', '#74b9ff',
        '#2d3436', '#636e72', '#b2bec3', '#fab1a0',
    ];

    $currentIcon  = old('icon',  $catItem->icon  ?? 'tio-folder');
    $currentColor = old('color', $catItem->color ?? '#4154f1');
@endphp

{{-- Name --}}
<div class="form-group mb-3">
    <label class="font-weight-semibold">Nombre <span class="text-danger">*</span></label>
    <input type="text" name="name" class="form-control" required maxlength="80"
           placeholder="Ej: Desarrollo, Marketing…"
           value="{{ old('name', $catItem->name ?? '') }}">
</div>

{{-- Color picker --}}
<div class="form-group mb-3">
    <label class="font-weight-semibold">Color</label>
    <div class="d-flex align-items-center gap-3 flex-wrap">
        @foreach($catColors as $hex)
        <label class="mb-0" style="cursor:pointer;">
            <input type="radio" name="color" value="{{ $hex }}" class="d-none color-radio"
                   {{ $currentColor === $hex ? 'checked' : '' }}>
            <span class="color-swatch" style="
                display:inline-block; width:28px; height:28px; border-radius:8px;
                background:{{ $hex }};
                box-shadow: {{ $currentColor === $hex ? '0 0 0 3px #fff, 0 0 0 5px '.$hex : '0 1px 3px rgba(0,0,0,.2)' }};
                transition: box-shadow .15s;"></span>
        </label>
        @endforeach
        {{-- Custom color --}}
        <div class="d-flex align-items-center gap-2 ml-auto">
            <div class="color-preview" style="width:28px;height:28px;border-radius:8px;background:{{ $currentColor }};border:2px solid #dee2e6;"></div>
            <input type="color" name="color_custom" value="{{ $currentColor }}"
                   class="form-control p-0 border-0" style="width:36px;height:28px;cursor:pointer;"
                   title="Color personalizado">
        </div>
    </div>
    {{-- Hidden field that stores the final color --}}
    <input type="hidden" name="color" id="colorFinalInput_{{ $catItem->id ?? 'new' }}" value="{{ $currentColor }}">
</div>

{{-- Icon picker --}}
<div class="form-group mb-0">
    <label class="font-weight-semibold">Icono</label>
    <div class="icon-picker-wrap">
        <input type="hidden" name="icon" value="{{ $currentIcon }}">
        <div class="icon-picker">
            @foreach($catIcons as $cls => $label)
            <div class="icon-opt {{ $currentIcon === $cls ? 'selected' : '' }}"
                 data-icon="{{ $cls }}" title="{{ $label }}">
                <i class="{{ $cls }}"></i>
            </div>
            @endforeach
        </div>
    </div>
</div>

<script>
(function() {
    // Color swatch click → sync hidden field
    document.querySelectorAll('.color-radio').forEach(function(radio) {
        radio.addEventListener('change', function() {
            syncColorField(this.closest('form'), this.value);
            updateSwatchShadows(this.closest('form'), this.value);
        });
    });

    // Custom color picker
    document.querySelectorAll('input[name="color_custom"]').forEach(function(picker) {
        picker.addEventListener('input', function() {
            const form = this.closest('form');
            syncColorField(form, this.value);
            updateSwatchShadows(form, this.value);
        });
    });

    function syncColorField(form, val) {
        const hidden = form.querySelector('input[id^="colorFinalInput_"]') || form.querySelector('input[name="color"]');
        if (hidden) hidden.value = val;
        const preview = form.querySelector('.color-preview');
        if (preview) preview.style.background = val;
    }

    function updateSwatchShadows(form, val) {
        form.querySelectorAll('.color-radio').forEach(r => {
            const swatch = r.nextElementSibling;
            if (!swatch) return;
            const hex = r.value;
            swatch.style.boxShadow = (hex === val)
                ? `0 0 0 3px #fff, 0 0 0 5px ${hex}`
                : '0 1px 3px rgba(0,0,0,.2)';
        });
    }
})();
</script>
