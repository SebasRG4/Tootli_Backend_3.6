@extends('layouts.admin.app')

@section('title', 'Monitoreo de registros')

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
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .spin-icon {
            animation: spin 1s linear infinite;
            display: inline-block;
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
                    <span>{{ 'Registros del sistema' }}</span>
                </h1>
                
                <!-- Clear Logs Action -->
                <div id="clear-logs-container">
                    @if(count($parsedLogs) > 0)
                        <form action="{{ route('admin.logs.clear') }}" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas limpiar el historial de registros? Esta acción no se puede deshacer.');">
                            @csrf
                            <button type="submit" class="btn btn-danger">
                                <i class="tio-delete-outlined mr-1"></i> {{ 'Limpiar troncos' }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- Filters Card -->
        <div class="card mb-3">
            <div class="card-body">
                <form action="{{ route('admin.logs.index') }}" method="GET">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label">{{ 'Buscar' }}</label>
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" 
                                       placeholder="{{ 'Buscar por palabra clave (ej. Géminis, Excepción, error)...' }}" 
                                       value="{{ $search }}">
                                @if(!empty($search))
                                    <div class="input-group-append">
                                        <a href="{{ route('admin.logs.index', ['limit' => $limit]) }}" class="btn btn-outline-secondary" title="{{ 'Limpiar búsqueda' }}">
                                            <i class="tio-clear"></i>
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label">{{ 'Límite de líneas' }}</label>
                            <select name="limit" class="form-control">
                                <option value="100" {{ $limit == 100 ? 'selected' : '' }}>Últimas 100 líneas</option>
                                <option value="500" {{ $limit == 500 ? 'selected' : '' }}>Últimas 500 líneas</option>
                                <option value="1000" {{ $limit == 1000 ? 'selected' : '' }}>Últimas 1000 líneas</option>
                                <option value="2000" {{ $limit == 2000 ? 'selected' : '' }}>Últimas 2000 líneas</option>
                            </select>
                        </div>

                        <div class="col-md-4 d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-primary mr-2">
                                <i class="tio-filter-list mr-1"></i> {{ 'Filtrar' }}
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
            <div class="card-header border-0 py-3 d-flex flex-wrap justify-content-between align-items-center">
                <h4 class="card-title">
                    {{ 'Registros de Registros Recientes' }}
                    <span class="badge badge-soft-dark ml-2" id="log-count-badge">{{ count($parsedLogs) }} encontrados</span>
                </h4>
                <div class="d-flex align-items-center flex-wrap" style="gap: 12px;">
                    <div class="custom-control custom-switch mr-3">
                        <input type="checkbox" class="custom-control-input" id="autoRefreshSwitch">
                        <label class="custom-control-label cursor-pointer mb-0" for="autoRefreshSwitch">{{ 'Auto-refrescar (5s)' }}</label>
                    </div>
                    <button type="button" id="refreshBtn" class="btn btn-outline-secondary btn-sm">
                        <i class="tio-sync mr-1" id="refreshIcon"></i> {{ 'Refrescar' }}
                    </button>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-thead-bordered table-align-middle card-table">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 180px;">{{ 'Tiempo' }}</th>
                                <th style="width: 120px;">{{ 'Nivel' }}</th>
                                <th>{{ 'mensaje' }}</th>
                            </tr>
                        </thead>
                        <tbody id="log-table-body">
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
                                                        <i class="tio-chevron-right mr-1"></i>{{ 'Ver detalles / Traza completa' }}
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
                                            <h5 class="text-muted">{{ 'No se encontraron registros de registros.' }}</h5>
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

@push('script_2')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let refreshInterval = null;
        const autoRefreshSwitch = document.getElementById('autoRefreshSwitch');
        const refreshBtn = document.getElementById('refreshBtn');
        const refreshIcon = document.getElementById('refreshIcon');
        const logTableBody = document.getElementById('log-table-body');
        const logCountBadge = document.getElementById('log-count-badge');
        const clearLogsContainer = document.getElementById('clear-logs-container');

        function fetchLogs() {
            if (refreshIcon) refreshIcon.classList.add('spin-icon');
            if (refreshBtn) refreshBtn.disabled = true;

            fetch(window.location.href, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.text();
            })
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');

                const newTableBody = doc.getElementById('log-table-body');
                const newCountBadge = doc.getElementById('log-count-badge');
                const newClearLogs = doc.getElementById('clear-logs-container');

                if (newTableBody && logTableBody) {
                    logTableBody.innerHTML = newTableBody.innerHTML;
                }
                if (newCountBadge && logCountBadge) {
                    logCountBadge.innerHTML = newCountBadge.innerHTML;
                }
                if (newClearLogs && clearLogsContainer) {
                    clearLogsContainer.innerHTML = newClearLogs.innerHTML;
                }
            })
            .catch(error => {
                console.error('Error fetching logs:', error);
            })
            .finally(() => {
                if (refreshIcon) refreshIcon.classList.remove('spin-icon');
                if (refreshBtn) refreshBtn.disabled = false;
            });
        }

        function startAutoRefresh() {
            stopAutoRefresh();
            refreshInterval = setInterval(fetchLogs, 5000);
        }

        function stopAutoRefresh() {
            if (refreshInterval) {
                clearInterval(refreshInterval);
                refreshInterval = null;
            }
        }

        if (autoRefreshSwitch) {
            // Initialize state from localStorage
            const storedState = localStorage.getItem('logs_auto_refresh');
            if (storedState === 'true') {
                autoRefreshSwitch.checked = true;
                startAutoRefresh();
            } else {
                autoRefreshSwitch.checked = false;
            }

            autoRefreshSwitch.addEventListener('change', function() {
                const isChecked = this.checked;
                localStorage.setItem('logs_auto_refresh', isChecked);
                if (isChecked) {
                    startAutoRefresh();
                    fetchLogs();
                } else {
                    stopAutoRefresh();
                }
            });
        }

        if (refreshBtn) {
            refreshBtn.addEventListener('click', function() {
                fetchLogs();
            });
        }
    });
</script>
@endpush
