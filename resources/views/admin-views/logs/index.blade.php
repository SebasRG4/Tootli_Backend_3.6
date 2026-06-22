@extends('layouts.admin.app')

@section('title', translate('Monitoreo de Logs'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .log-level-badge {
            font-size: 12px;
            font-weight: 600;
            padding: 5px 10px;
            border-radius: 4px;
            text-transform: uppercase;
        }
        .log-message {
            font-family: monospace;
            font-size: 13px;
            word-break: break-all;
            white-space: pre-wrap;
        }
        .log-stacktrace-pre {
            font-family: monospace;
            font-size: 11px;
            max-height: 250px;
            overflow-y: auto;
            white-space: pre-wrap;
            word-break: break-all;
            background-color: #f8f9fa;
            border-left: 4px solid #ed4c78;
        }
        .cursor-pointer {
            cursor: pointer;
        }
        .cursor-pointer:hover {
            text-decoration: underline;
        }
    </style>
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <h1 class="page-header-title">
                    <span class="page-header-icon">
                        <i class="tio-receipt text-primary"></i>
                    </span>
                    <span>{{ translate('messages.System Logs') }}</span>
                </h1>
                
                <!-- Clear Logs Action -->
                @if(count($parsedLogs) > 0)
                    <form action="{{ route('admin.logs.clear') }}" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas limpiar el historial de registros? Esta acción no se puede deshacer.');">
                        @csrf
                        <button type="submit" class="btn btn-danger">
                            <i class="tio-delete-outlined mr-1"></i> {{ translate('Limpiar Logs') }}
                        </button>
                    </form>
                @endif
            </div>
        </div>
        <!-- End Page Header -->

        <!-- Filters Card -->
        <div class="card mb-3">
            <div class="card-body">
                <form action="{{ route('admin.logs.index') }}" method="GET">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label">{{ translate('messages.Search') }}</label>
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" 
                                       placeholder="{{ translate('Buscar por palabra clave (ej. Gemini, Exception, error)...') }}" 
                                       value="{{ $search }}">
                                @if(!empty($search))
                                    <div class="input-group-append">
                                        <a href="{{ route('admin.logs.index', ['limit' => $limit]) }}" class="btn btn-outline-secondary" title="{{ translate('Limpiar búsqueda') }}">
                                            <i class="tio-clear"></i>
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label">{{ translate('Límite de líneas') }}</label>
                            <select name="limit" class="form-control">
                                <option value="100" {{ $limit == 100 ? 'selected' : '' }}>Últimas 100 líneas</option>
                                <option value="500" {{ $limit == 500 ? 'selected' : '' }}>Últimas 500 líneas</option>
                                <option value="1000" {{ $limit == 1000 ? 'selected' : '' }}>Últimas 1000 líneas</option>
                                <option value="2000" {{ $limit == 2000 ? 'selected' : '' }}>Últimas 2000 líneas</option>
                            </select>
                        </div>

                        <div class="col-md-4 d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-primary mr-2">
                                <i class="tio-filter-list mr-1"></i> {{ translate('messages.Filter') }}
                            </button>
                            
                            <!-- Quick Filter Gemini Button -->
                            <a href="{{ route('admin.logs.index', ['search' => 'Gemini', 'limit' => $limit]) }}" 
                               class="btn btn-outline-info">
                                <i class="tio-help-outlined mr-1"></i> Ver Logs de Gemini
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Logs Content Card -->
        <div class="card">
            <div class="card-header border-0 py-3">
                <h4 class="card-title">
                    {{ translate('Registros de Logs Recientes') }}
                    <span class="badge badge-soft-dark ml-2">{{ count($parsedLogs) }} encontrados</span>
                </h4>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-thead-bordered table-align-middle card-table">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 180px;">{{ translate('messages.Time') }}</th>
                                <th style="width: 120px;">{{ translate('Nivel') }}</th>
                                <th>{{ translate('messages.message') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($parsedLogs as $log)
                                @php
                                    $levelClass = 'badge-secondary text-dark';
                                    if (in_array($log['level'], ['ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY'])) {
                                        $levelClass = 'badge-danger text-white';
                                    } elseif ($log['level'] === 'WARNING') {
                                        $levelClass = 'badge-warning text-dark';
                                    } elseif (in_array($log['level'], ['INFO', 'NOTICE'])) {
                                        $levelClass = 'badge-info text-white';
                                    }
                                @endphp
                                <tr>
                                    <td class="text-nowrap">
                                        <span class="text-muted">
                                            <i class="tio-date-range mr-1"></i>{{ $log['timestamp'] }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge log-level-badge {{ $levelClass }}">
                                            {{ $log['level'] }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="log-message text-dark font-weight-bold">{{ $log['message'] }}</div>
                                        
                                        <!-- Collapsible Stacktrace if exists -->
                                        @if(!empty($log['stacktrace']))
                                            <div class="mt-2">
                                                <details class="log-stacktrace-details">
                                                    <summary class="text-primary cursor-pointer font-size-sm">
                                                        <i class="tio-chevron-right mr-1"></i>{{ translate('Ver detalles / Traza completa') }}
                                                    </summary>
                                                    <pre class="log-stacktrace-pre p-3 rounded mt-2">{{ $log['stacktrace'] }}</pre>
                                                </details>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-5">
                                        <div class="empty-state">
                                            <img src="{{ asset('assets/admin/svg/illustrations/sorry.svg') }}" alt="No logs found" class="mb-3" style="width: 150px;">
                                            <h5 class="text-muted">{{ translate('No se encontraron registros de logs.') }}</h5>
                                            @if(!empty($search))
                                                <p class="text-muted font-size-sm">Prueba buscando otra palabra clave o limpiando los filtros.</p>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
