@extends('layouts.admin.app')

@section('title', 'POS Distribuidora — Seleccionar Tienda')

@push('css_or_js')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    body { font-family: 'Inter', sans-serif; background: #F8FAFC; }
    .select-card {
        background: #FFFFFF;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        border: 1px solid #E2E8F0;
        padding: 2.5rem;
        max-width: 650px;
        margin: 40px auto;
    }
    .select-header {
        text-align: center;
        margin-bottom: 2rem;
    }
    .select-icon {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        background: #EFF6FF;
        color: #2563EB;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        margin-bottom: 1rem;
    }
    .store-item {
        border: 1px solid #E2E8F0;
        border-radius: 10px;
        padding: 14px 18px;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: all .2s;
        text-decoration: none !important;
        color: #1E293B;
    }
    .store-item:hover {
        border-color: #2563EB;
        background: #F8FAFC;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(37,99,235,0.1);
    }
    .store-name {
        font-weight: 600;
        font-size: 15px;
    }
    .store-btn {
        background: #2563EB;
        color: #fff;
        padding: 6px 16px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
    }
    .search-input {
        border-radius: 10px;
        border: 1px solid #CBD5E1;
        padding: 10px 16px;
        width: 100%;
        margin-bottom: 20px;
        font-size: 14px;
    }
    .search-input:focus {
        border-color: #2563EB;
        outline: none;
        box-shadow: 0 0 0 3px rgba(37,99,235,0.15);
    }
</style>
@endpush

@section('content')
<div class="content container-fluid">
    <div class="select-card">
        <div class="select-header">
            <div class="select-icon">
                <i class="tio-shop"></i>
            </div>
            <h2 class="mb-1" style="font-weight: 700; color: #0F172A;">POS Distribuidora</h2>
            <p class="text-muted" style="font-size: 14px;">Selecciona la tienda o sucursal donde deseas operar el punto de venta</p>
        </div>

        <input type="text" id="storeSearch" class="search-input" placeholder="🔍 Buscar por nombre de tienda..." onkeyup="filterStores()">

        <div id="storesList" style="max-height: 400px; overflow-y: auto; padding-right: 4px;">
            @forelse($stores as $st)
                <a href="{{ route('admin.distributor-pos.index', ['store_id' => $st->id]) }}" class="store-item" data-name="{{ strtolower($st->name) }}">
                    <div>
                        <div class="store-name">{{ $st->name }}</div>
                        <small class="text-muted">ID: #{{ $st->id }}</small>
                    </div>
                    <span class="store-btn">Abrir POS <i class="tio-chevron-right ml-1"></i></span>
                </a>
            @empty
                <div class="text-center py-4 text-muted">
                    <p>No hay tiendas disponibles.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<script>
function filterStores() {
    const query = document.getElementById('storeSearch').value.toLowerCase();
    const items = document.querySelectorAll('#storesList .store-item');
    items.forEach(item => {
        const name = item.getAttribute('data-name');
        if (name.includes(query)) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });
}
</script>
@endsection
