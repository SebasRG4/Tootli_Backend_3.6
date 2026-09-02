@php $item = $item ?? null; @endphp

<div class="form-group mb-3">
    <label class="font-weight-semibold">Título <span class="text-danger">*</span></label>
    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
           placeholder="Título de la tarea"
           value="{{ old('title', $item->title ?? '') }}" required maxlength="255">
    @error('title')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group mb-3">
    <label class="font-weight-semibold">Descripción</label>
    <textarea name="description" class="form-control @error('description') is-invalid @enderror"
              rows="3" placeholder="Descripción opcional..." maxlength="2000">{{ old('description', $item->description ?? '') }}</textarea>
    @error('description')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group mb-3">
    <label class="font-weight-semibold">Carpeta / Categoría</label>
    <select name="category_id" class="custom-select">
        <option value="">— Sin categoría —</option>
        @foreach($categories as $cat)
            <option value="{{ $cat->id }}"
                {{ old('category_id', $item->category_id ?? ($preselectedCategory ?? '')) == $cat->id ? 'selected' : '' }}
                style="color:{{ $cat->color }};">
                {{ $cat->name }}
            </option>
        @endforeach
    </select>
</div>

<div class="row">
    <div class="col-6">
        <div class="form-group mb-3">
            <label class="font-weight-semibold">Prioridad <span class="text-danger">*</span></label>
            <select name="priority" class="custom-select" required>
                <option value="low"    {{ old('priority', $item->priority ?? '') == 'low'    ? 'selected' : '' }}>🟢 Baja</option>
                <option value="medium" {{ old('priority', $item->priority ?? 'medium') == 'medium' ? 'selected' : '' }}>🟡 Media</option>
                <option value="high"   {{ old('priority', $item->priority ?? '') == 'high'   ? 'selected' : '' }}>🔴 Alta</option>
            </select>
        </div>
    </div>
    <div class="col-6">
        <div class="form-group mb-3">
            <label class="font-weight-semibold">Estado <span class="text-danger">*</span></label>
            <select name="status" class="custom-select" required>
                <option value="pending"     {{ old('status', $item->status ?? 'pending') == 'pending'     ? 'selected' : '' }}>⏳ Pendiente</option>
                <option value="in_progress" {{ old('status', $item->status ?? '') == 'in_progress' ? 'selected' : '' }}>🔄 En progreso</option>
                <option value="completed"   {{ old('status', $item->status ?? '') == 'completed'   ? 'selected' : '' }}>✅ Completado</option>
            </select>
        </div>
    </div>
</div>

<div class="form-group mb-0">
    <label class="font-weight-semibold">Fecha límite</label>
    <input type="date" name="due_date" class="form-control @error('due_date') is-invalid @enderror"
           value="{{ old('due_date', isset($item->due_date) ? $item->due_date->format('Y-m-d') : '') }}"
           min="{{ date('Y-m-d') }}">
    @error('due_date')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
