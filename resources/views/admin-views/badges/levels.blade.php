@extends('layouts.admin.app')

@section('title', 'Niveles de Repartidor')

@push('css_or_js')
<style>
    .level-card {
        border-radius: 12px;
        padding: 16px;
        color: #fff;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .level-tier-badge {
        font-size: 11px;
        padding: 2px 8px;
        border-radius: 20px;
        background: rgba(255,255,255,0.25);
        font-weight: 600;
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
            <span>Niveles de Repartidor</span>
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.badges.list') }}">Insignias</a></li>
                <li class="breadcrumb-item active">Niveles</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Escala de niveles — Pochteca → Jaguar → Águila Real</h5>
                </div>
                <div class="card-body">
                    @foreach($levels->groupBy('name') as $tierName => $tierLevels)
                    <h6 class="text-uppercase font-weight-bold text-muted mt-3 mb-2">
                        {{ $tierName }}
                    </h6>
                    @foreach($tierLevels as $level)
                    <div class="level-card" style="background: linear-gradient(135deg, {{ $level->color_from }}, {{ $level->color_to }})">
                        <div>
                            <div class="font-weight-bold" style="font-size:18px">
                                Nivel {{ $level->level_index }} — {{ $level->name }} {{ $level->sub_level }}
                            </div>
                            <div style="opacity:.8; font-size:13px">
                                {{ number_format($level->xp_required) }} XP requeridos
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="level-tier-badge">{{ $level->name }} {{ $level->sub_level }}</span>
                            <a href="{{ route('admin.badge-levels.edit', $level->id) }}"
                                class="btn btn-sm" style="background:rgba(255,255,255,.25); color:#fff; border:none">
                                <i class="tio-edit"></i> Editar
                            </a>
                        </div>
                    </div>
                    @endforeach
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">ℹ️ Cómo funciona el XP</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Cada repartidor acumula <strong>XP</strong> al desbloquear insignias.</p>
                    <ul class="text-muted" style="padding-left: 16px">
                        <li>Cada insignia otorga una cantidad de XP configurada</li>
                        <li>El nivel del repartidor depende de su XP total acumulado</li>
                        <li>Los sub-niveles I, II, III van subiendo conforme acumula XP</li>
                        <li>La racha de días activos también contribuye al desbloqueo de insignias</li>
                    </ul>
                    <div class="alert alert-soft-info mt-3">
                        <strong>Sugerencia:</strong> Los niveles más altos deberían tener incrementos de XP progresivos para mantener el reto.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
