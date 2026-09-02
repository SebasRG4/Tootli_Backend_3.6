@extends('layouts.admin.app')

@section('title', 'To Do List')

@push('css_or_js')
<style>
/* ── Fonts & base ──────────────────────────────────────── */
:root {
    --todo-radius: 16px;
    --todo-shadow: 0 4px 24px rgba(0,0,0,.09);
    --sidebar-w: 300px;
}

/* ── Layout ─────────────────────────────────────────────── */
.todo-layout {
    display: grid;
    grid-template-columns: var(--sidebar-w) 1fr;
    gap: 1.5rem;
    align-items: start;
}
@media(max-width:991px){ .todo-layout { grid-template-columns: 1fr; } }

/* ── Category sidebar ───────────────────────────────────── */
.cat-sidebar { position: sticky; top: 80px; }

.cat-card {
    border-radius: var(--todo-radius);
    border: none;
    overflow: hidden;
    cursor: pointer;
    transition: transform .2s, box-shadow .2s;
    margin-bottom: .85rem;
    text-decoration: none !important;
}
.cat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 28px rgba(0,0,0,.15); }
.cat-card.active-cat { box-shadow: 0 0 0 3px rgba(65,84,241,.5), 0 8px 28px rgba(0,0,0,.12); }

.cat-card-header {
    padding: 1rem 1.2rem .65rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.cat-card-icon {
    width: 46px; height: 46px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem;
    background: rgba(255,255,255,.25);
    color: #fff;
    flex-shrink: 0;
}
.cat-card-meta { flex: 1; padding-left: .85rem; }
.cat-card-name { font-size: 1rem; font-weight: 700; color: #fff; line-height: 1.2; }
.cat-card-sub  { font-size: .78rem; color: rgba(255,255,255,.8); margin-top: 2px; }
.cat-card-pct  { font-size: 1.25rem; font-weight: 800; color: #fff; }

.cat-progress-wrap { padding: 0 1.2rem 1rem; }
.cat-progress-bar {
    height: 6px;
    border-radius: 99px;
    background: rgba(255,255,255,.3);
    overflow: hidden;
}
.cat-progress-fill {
    height: 100%;
    border-radius: 99px;
    background: rgba(255,255,255,.85);
    transition: width .5s ease;
}

.cat-actions { display: flex; gap: 4px; }
.cat-action-btn {
    width: 28px; height: 28px;
    border-radius: 8px;
    border: none;
    background: rgba(255,255,255,.2);
    color: #fff;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer;
    font-size: .8rem;
    transition: background .15s;
}
.cat-action-btn:hover { background: rgba(255,255,255,.4); }

/* All-tasks card */
.cat-all-card {
    border-radius: var(--todo-radius);
    border: 2px dashed #dee2e6;
    padding: .9rem 1.2rem;
    display: flex; align-items: center;
    cursor: pointer;
    text-decoration: none !important;
    color: #495057;
    font-weight: 600;
    margin-bottom: .85rem;
    transition: all .2s;
    background: #fff;
}
.cat-all-card:hover,
.cat-all-card.active-cat { border-color: #4154f1; color: #4154f1; background: #eef0ff; }

/* ── Task column ─────────────────────────────────────────── */
.todo-col-header {
    display: flex; align-items: center;
    justify-content: space-between;
    margin-bottom: 1rem;
    flex-wrap: wrap; gap: .5rem;
}
.todo-col-title { font-size: 1.25rem; font-weight: 700; color: #212529; }

/* ── Task card ───────────────────────────────────────────── */
.task-card {
    border-radius: 14px;
    border: none;
    box-shadow: 0 2px 10px rgba(0,0,0,.06);
    margin-bottom: .75rem;
    border-left: 5px solid transparent;
    transition: transform .18s, box-shadow .18s;
}
.task-card:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,.11); }
.task-card.priority-high   { border-left-color: #dc3545; }
.task-card.priority-medium { border-left-color: #ffc107; }
.task-card.priority-low    { border-left-color: #28a745; }

.task-title { font-size: .97rem; font-weight: 600; }
.task-desc  { font-size: .84rem; color: #6c757d; }

/* ── Stats strip ─────────────────────────────────────────── */
.stats-strip { display: flex; gap: .6rem; flex-wrap: wrap; margin-bottom: 1.2rem; }
.stat-pill {
    display: flex; align-items: center; gap: .4rem;
    padding: .35rem .85rem;
    border-radius: 99px;
    font-size: .82rem; font-weight: 600;
    cursor: pointer; text-decoration: none !important;
    transition: all .15s;
    border: 2px solid transparent;
}
.stat-pill:hover { transform: translateY(-1px); }
.stat-pill.active-pill { border-color: currentColor !important; box-shadow: 0 2px 10px rgba(0,0,0,.12); }

/* ── Filter bar ──────────────────────────────────────────── */
.filter-row { display: flex; gap: .6rem; flex-wrap: wrap; align-items: center; margin-bottom: 1rem; }
.filter-row .form-control,
.filter-row .custom-select { height: 36px; font-size: .86rem; }

/* ── New category modal ──────────────────────────────────── */
.icon-picker { display: flex; flex-wrap: wrap; gap: 6px; max-height: 200px; overflow-y: auto; padding: 4px; }
.icon-opt {
    width: 38px; height: 38px;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.15rem;
    border: 2px solid #dee2e6;
    cursor: pointer;
    transition: all .15s;
}
.icon-opt:hover, .icon-opt.selected { border-color: #4154f1; background: #eef0ff; color: #4154f1; }

/* ── Empty state ─────────────────────────────────────────── */
.empty-state { text-align: center; padding: 3rem 1rem; }
.empty-state i { font-size: 4rem; color: #dee2e6; display: block; margin-bottom: 1rem; }
</style>
@endpush

@section('content')
<div class="content container-fluid">

    {{-- Page Header --}}
    <div class="page-header">
        <h1 class="page-header-title mr-3">
            <span class="page-header-icon">
                <i class="tio-format-bullets" style="font-size:1.5rem;color:#4154f1;"></i>
            </span>
            <span>To Do List</span>
        </h1>
        @include('admin-views.business-settings.partials.nav-menu')
    </div>

    {{-- Main layout --}}
    <div class="todo-layout">

        {{-- ═══ LEFT: Category Sidebar ═════════════════════════════════════ --}}
        <aside class="cat-sidebar">

            {{-- All tasks --}}
            <a href="{{ url('admin/business-settings/to-do-list') }}"
               class="cat-all-card d-flex {{ $categoryId === 'all' ? 'active-cat' : '' }}">
                <i class="tio-format-bullets mr-2" style="font-size:1.2rem;"></i>
                Todas las tareas
                <span class="badge badge-soft-primary ml-auto">{{ $counts['all'] }}</span>
            </a>

            {{-- Category cards --}}
            @foreach($categories as $cat)
            @php
                $pct = $cat->completion_percentage;
            @endphp
            <div class="position-relative">
                <a href="{{ url('admin/business-settings/to-do-list?category='.$cat->id) }}"
                   class="cat-card d-block {{ $categoryId == $cat->id ? 'active-cat' : '' }}"
                   style="background: linear-gradient(135deg, {{ $cat->color }}ee, {{ $cat->color }}aa);">
                    <div class="cat-card-header">
                        <div class="cat-card-icon">
                            <i class="{{ $cat->icon }}"></i>
                        </div>
                        <div class="cat-card-meta">
                            <div class="cat-card-name">{{ $cat->name }}</div>
                            <div class="cat-card-sub">{{ $cat->task_count }} tareas</div>
                        </div>
                        <div class="d-flex flex-column align-items-end">
                            <span class="cat-card-pct">{{ $pct }}%</span>
                            <div class="cat-actions mt-1">
                                <button type="button" class="cat-action-btn"
                                        data-toggle="modal" data-target="#editCatModal{{ $cat->id }}"
                                        onclick="event.preventDefault();" title="Editar">
                                    <i class="tio-edit"></i>
                                </button>
                                <button type="button" class="cat-action-btn"
                                        data-toggle="modal" data-target="#deleteCatModal{{ $cat->id }}"
                                        onclick="event.preventDefault();" title="Eliminar">
                                    <i class="tio-delete"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="cat-progress-wrap">
                        <div class="d-flex justify-content-between mb-1" style="font-size:.74rem;color:rgba(255,255,255,.8);">
                            <span>{{ $cat->completed_count }} completadas</span>
                            <span>{{ $cat->pending_count }} pendientes</span>
                        </div>
                        <div class="cat-progress-bar">
                            <div class="cat-progress-fill" style="width:{{ $pct }}%;"></div>
                        </div>
                    </div>
                </a>
            </div>

            {{-- Edit category modal --}}
            <div class="modal fade" id="editCatModal{{ $cat->id }}" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Editar carpeta</h5>
                            <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                        </div>
                        <form action="{{ route('admin.business-settings.todo.categories.update', $cat->id) }}" method="POST">
                            @csrf @method('PUT')
                            <div class="modal-body">
                                @include('admin-views.business-settings.todo.partials.category-form', ['catItem' => $cat])
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-primary">Guardar cambios</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Delete category confirm modal --}}
            <div class="modal fade" id="deleteCatModal{{ $cat->id }}" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered modal-sm">
                    <div class="modal-content">
                        <div class="modal-body text-center py-4">
                            <i class="tio-delete" style="font-size:2.5rem;color:#dc3545;"></i>
                            <p class="mt-3 mb-1 font-weight-bold">¿Eliminar carpeta?</p>
                            <p class="text-muted small">Las tareas de esta carpeta quedarán sin categoría.</p>
                        </div>
                        <div class="modal-footer justify-content-center border-0">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                            <form action="{{ route('admin.business-settings.todo.categories.destroy', $cat->id) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger">Sí, eliminar</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach

            {{-- No category filter --}}
            @if($uncategorizedCount > 0)
            <a href="{{ url('admin/business-settings/to-do-list?category=none') }}"
               class="cat-all-card {{ $categoryId === 'none' ? 'active-cat' : '' }}">
                <i class="tio-folder-remove mr-2" style="font-size:1.1rem;"></i>
                Sin categoría
                <span class="badge badge-soft-secondary ml-auto">{{ $uncategorizedCount }}</span>
            </a>
            @endif

            {{-- New category button --}}
            <button type="button" class="btn btn-outline-primary btn-block mt-2"
                    data-toggle="modal" data-target="#newCatModal"
                    style="border-radius:12px; border-style:dashed;">
                <i class="tio-add mr-1"></i> Nueva carpeta
            </button>
        </aside>

        {{-- ═══ RIGHT: Tasks Column ════════════════════════════════════════ --}}
        <section>

            {{-- Header --}}
            <div class="todo-col-header">
                <div>
                    <div class="todo-col-title">
                        @if($activeCategory)
                            <span style="display:inline-block;width:14px;height:14px;border-radius:4px;background:{{ $activeCategory->color }};vertical-align:middle;margin-right:6px;"></span>
                            {{ $activeCategory->name }}
                        @elseif($categoryId === 'none')
                            Sin categoría
                        @else
                            Todas las tareas
                        @endif
                    </div>
                    <div class="text-muted small">{{ $todos->total() }} {{ $todos->total() === 1 ? 'tarea' : 'tareas' }}</div>
                </div>
                <button type="button" class="btn btn-primary btn-sm px-3"
                        data-toggle="modal" data-target="#newTaskModal"
                        style="border-radius:10px;">
                    <i class="tio-add mr-1"></i> Nueva tarea
                </button>
            </div>

            {{-- Stats pills --}}
            <div class="stats-strip">
                @foreach([
                    ['label' => 'Todas',       'key' => 'all',         'bg' => '#eef0ff', 'color' => '#4154f1', 'count' => $counts['all']],
                    ['label' => 'Pendientes',  'key' => 'pending',     'bg' => '#f4f5f7', 'color' => '#6c757d', 'count' => $counts['pending']],
                    ['label' => 'En progreso', 'key' => 'in_progress', 'bg' => '#e8f0fe', 'color' => '#0d6efd', 'count' => $counts['in_progress']],
                    ['label' => 'Completadas', 'key' => 'completed',   'bg' => '#e8f5e9', 'color' => '#198754', 'count' => $counts['completed']],
                ] as $s)
                <a href="{{ request()->fullUrlWithQuery(['status' => $s['key']]) }}"
                   class="stat-pill {{ $status === $s['key'] ? 'active-pill' : '' }}"
                   style="background:{{ $s['bg'] }};color:{{ $s['color'] }};">
                    {{ $s['count'] }} {{ $s['label'] }}
                </a>
                @endforeach
            </div>

            {{-- Filter bar --}}
            <form method="GET" action="{{ url('admin/business-settings/to-do-list') }}" class="filter-row">
                <input type="hidden" name="status"   value="{{ $status }}">
                <input type="hidden" name="category" value="{{ $categoryId }}">
                <input type="text" name="search" class="form-control"
                       style="max-width:200px;"
                       placeholder="🔍 Buscar..." value="{{ $search }}">
                <select name="priority" class="custom-select" style="max-width:160px;" onchange="this.form.submit()">
                    <option value="all" {{ $priority=='all' ? 'selected':'' }}>Todas las prioridades</option>
                    <option value="high"   {{ $priority=='high'   ? 'selected':'' }}>🔴 Alta</option>
                    <option value="medium" {{ $priority=='medium' ? 'selected':'' }}>🟡 Media</option>
                    <option value="low"    {{ $priority=='low'    ? 'selected':'' }}>🟢 Baja</option>
                </select>
                <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
                @if($search || $priority !== 'all')
                <a href="{{ url('admin/business-settings/to-do-list?category='.$categoryId.'&status='.$status) }}"
                   class="btn btn-outline-secondary btn-sm">Limpiar</a>
                @endif
            </form>

            {{-- Task list --}}
            @forelse($todos as $todo)
            <div class="card task-card priority-{{ $todo->priority }}">
                <div class="card-body py-3 px-4">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="flex-grow-1 mr-3">

                            {{-- Category tag --}}
                            @if($todo->category)
                            <div class="mb-1">
                                <span style="
                                    display:inline-flex; align-items:center; gap:4px;
                                    background:{{ $todo->category->color }}22;
                                    color:{{ $todo->category->color }};
                                    border:1px solid {{ $todo->category->color }}55;
                                    border-radius:99px; padding:1px 8px; font-size:.74rem; font-weight:600;">
                                    <i class="{{ $todo->category->icon }}" style="font-size:.8rem;"></i>
                                    {{ $todo->category->name }}
                                </span>
                            </div>
                            @endif

                            <div class="d-flex align-items-center flex-wrap gap-1">
                                <span class="task-title {{ $todo->status === 'completed' ? 'text-muted' : '' }}"
                                      style="{{ $todo->status === 'completed' ? 'text-decoration:line-through;' : '' }}">
                                    {{ $todo->title }}
                                </span>
                                <span class="badge badge-soft-{{ $todo->priority_color }} ml-1" style="font-size:.73rem;">
                                    {{ $todo->priority_label }}
                                </span>
                                <span class="badge badge-soft-{{ $todo->status_color }} ml-1" style="font-size:.73rem;">
                                    {{ $todo->status_label }}
                                </span>
                                @if($todo->due_date)
                                    @php $overdue = $todo->due_date->isPast() && $todo->status !== 'completed'; @endphp
                                    <span class="badge ml-1" style="font-size:.72rem;
                                        background:{{ $overdue ? '#ffe0e0' : '#e8f0fe' }};
                                        color:{{ $overdue ? '#c0392b' : '#0d6efd' }};">
                                        📅 {{ $todo->due_date->format('d/m/Y') }}{{ $overdue ? ' · Vencida' : '' }}
                                    </span>
                                @endif
                            </div>

                            @if($todo->description)
                            <p class="task-desc mt-1 mb-0">{{ Str::limit($todo->description, 120) }}</p>
                            @endif
                        </div>

                        {{-- Actions --}}
                        <div class="d-flex align-items-center gap-1">
                            @if($todo->status !== 'completed')
                            <button class="btn btn-sm btn-icon btn-outline-success btn-quick-complete"
                                    data-id="{{ $todo->id }}" title="Completar">
                                <i class="tio-checkmark"></i>
                            </button>
                            @endif
                            <button class="btn btn-sm btn-icon btn-outline-primary"
                                    data-toggle="modal" data-target="#editTaskModal{{ $todo->id }}"
                                    title="Editar">
                                <i class="tio-edit"></i>
                            </button>
                            <form action="{{ route('admin.business-settings.todo.destroy', $todo->id) }}"
                                  method="POST" class="d-inline"
                                  onsubmit="return confirm('¿Eliminar tarea?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-icon btn-outline-danger" title="Eliminar">
                                    <i class="tio-delete"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Edit task modal --}}
            <div class="modal fade" id="editTaskModal{{ $todo->id }}" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Editar tarea</h5>
                            <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                        </div>
                        <form action="{{ route('admin.business-settings.todo.update', $todo->id) }}" method="POST">
                            @csrf @method('PUT')
                            <div class="modal-body">
                                @include('admin-views.business-settings.todo.partials.form', ['item' => $todo])
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-primary">Guardar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @empty
            <div class="empty-state">
                <i class="tio-format-bullets"></i>
                <p class="font-weight-semibold text-muted mb-1">No hay tareas aquí</p>
                <p class="text-muted small">Crea una nueva tarea con el botón de arriba.</p>
            </div>
            @endforelse

            {{-- Pagination --}}
            <div class="mt-3">{{ $todos->links() }}</div>
        </section>
    </div>{{-- /todo-layout --}}
</div>

{{-- ═══════════════════════════════════════════════════════════════════════════
     MODALS
════════════════════════════════════════════════════════════════════════════ --}}

{{-- New Task Modal --}}
<div class="modal fade" id="newTaskModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="tio-add-circle mr-2" style="color:#4154f1;"></i>Nueva tarea</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="{{ route('admin.business-settings.todo.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    @php $preselectedCategory = ($categoryId !== 'all' && $categoryId !== 'none') ? $categoryId : null; @endphp
                    @include('admin-views.business-settings.todo.partials.form')
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="tio-add mr-1"></i> Crear tarea
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- New Category Modal --}}
<div class="modal fade" id="newCatModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="tio-folder mr-2" style="color:#4154f1;"></i>Nueva carpeta</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="{{ route('admin.business-settings.todo.categories.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    @include('admin-views.business-settings.todo.partials.category-form')
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="tio-add mr-1"></i> Crear carpeta
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
// Quick-complete via AJAX
document.querySelectorAll('.btn-quick-complete').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        fetch('/admin/business-settings/to-do-list/' + id + '/status', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ status: 'completed' })
        }).then(r => r.json()).then(d => { if (d.success) location.reload(); });
    });
});

// Color preview in category form
document.addEventListener('input', function(e) {
    if (e.target.name === 'color') {
        const preview = e.target.closest('form').querySelector('.color-preview');
        if (preview) preview.style.background = e.target.value;
    }
});

// Icon picker selection
document.addEventListener('click', function(e) {
    const opt = e.target.closest('.icon-opt');
    if (!opt) return;
    const picker = opt.closest('.icon-picker-wrap');
    picker.querySelectorAll('.icon-opt').forEach(o => o.classList.remove('selected'));
    opt.classList.add('selected');
    picker.querySelector('input[name="icon"]').value = opt.dataset.icon;
});
</script>
@endpush
