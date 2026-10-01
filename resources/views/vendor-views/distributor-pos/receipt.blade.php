<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket #{{ $sale->folio }} — {{ $sale->store->name ?? 'Distribuidora' }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 13px;
            color: #000;
            background-color: #f3f4f6;
            padding: 20px;
        }
        .ticket-wrapper {
            max-width: 320px;
            margin: 0 auto;
            background: #fff;
            padding: 16px;
            border-radius: 6px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .divider {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }
        .double-divider {
            border-top: 2px dashed #000;
            margin: 8px 0;
        }
        .store-title {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .store-sub {
            font-size: 11px;
            line-height: 1.3;
        }
        .meta-line {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            margin: 2px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin: 6px 0;
        }
        th {
            border-bottom: 1px dashed #000;
            padding: 4px 2px;
            text-align: left;
        }
        td {
            padding: 4px 2px;
            vertical-align: top;
        }
        .totals-table {
            width: 100%;
            font-size: 12px;
        }
        .totals-table td {
            padding: 2px 0;
        }
        .points-box {
            border: 1px dashed #000;
            padding: 6px;
            margin: 8px 0;
            text-align: center;
            font-size: 11px;
        }
        .actions {
            margin-top: 16px;
            display: flex;
            gap: 8px;
        }
        .btn {
            flex: 1;
            padding: 8px 12px;
            font-size: 12px;
            font-weight: bold;
            border-radius: 4px;
            cursor: pointer;
            border: none;
            font-family: inherit;
        }
        .btn-print {
            background: #2563EB;
            color: #fff;
        }
        .btn-close {
            background: #e5e7eb;
            color: #374151;
        }
        @media print {
            body {
                background: none;
                padding: 0;
            }
            .ticket-wrapper {
                max-width: 100%;
                box-shadow: none;
                padding: 0;
            }
            .actions {
                display: none !important;
            }
        }
    </style>
</head>
<body>

<div class="ticket-wrapper">
    {{-- Encabezado Tienda --}}
    <div class="text-center">
        <div class="store-title">{{ $sale->store->name ?? 'DISTRIBUIDORA' }}</div>
        @if($sale->store->address)
            <div class="store-sub">{{ $sale->store->address }}</div>
        @endif
        @if($sale->store->phone)
            <div class="store-sub">Tel: {{ $sale->store->phone }}</div>
        @endif
    </div>

    <div class="divider"></div>

    {{-- Meta Información --}}
    <div class="meta-line">
        <span>Folio: <strong>{{ $sale->folio }}</strong></span>
        <span>{{ $sale->created_at->format('d/m/Y H:i') }}</span>
    </div>
    <div class="meta-line">
        <span>Cajero: {{ $sale->created_by ?? 'Admin' }}</span>
        <span>Tipo: Mostrador</span>
    </div>

    @if($sale->customer)
        <div class="meta-line">
            <span>Cliente: {{ $sale->customer->name }}</span>
            <span>Tel: {{ $sale->customer->phone }}</span>
        </div>
    @else
        <div class="meta-line">
            <span>Cliente: Público General</span>
        </div>
    @endif

    <div class="divider"></div>

    {{-- Detalle de Productos --}}
    <table>
        <thead>
            <tr>
                <th style="width: 15%;">Cant</th>
                <th style="width: 50%;">Desc</th>
                <th style="width: 15%; text-align: right;">P.U.</th>
                <th style="width: 20%; text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $item)
                @php
                    $qty = $item['quantity'] ?? 1;
                    $price = $item['price'] ?? 0;
                    $rowTotal = $qty * $price;
                @endphp
                <tr>
                    <td>{{ $qty }}</td>
                    <td>{{ $item['name'] ?? 'Producto' }}</td>
                    <td class="text-right">${{ number_format($price, 2) }}</td>
                    <td class="text-right">${{ number_format($rowTotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="double-divider"></div>

    {{-- Totales --}}
    <table class="totals-table">
        <tr>
            <td>Subtotal:</td>
            <td class="text-right">${{ number_format($sale->subtotal, 2) }}</td>
        </tr>
        @if($sale->discount_amount > 0)
        <tr>
            <td>Descuento:</td>
            <td class="text-right">-${{ number_format($sale->discount_amount, 2) }}</td>
        </tr>
        @endif
        @if($sale->points_redeemed > 0)
        @php
            $pointVal = (float)($config->distributor_point_value ?? 1);
            $pointsDiscount = $sale->points_redeemed * $pointVal;
        @endphp
        <tr>
            <td>Canje Puntos ({{ number_format($sale->points_redeemed, 2) }} pts):</td>
            <td class="text-right">-${{ number_format($pointsDiscount, 2) }}</td>
        </tr>
        @endif
        <tr class="font-bold" style="font-size: 14px;">
            <td>TOTAL:</td>
            <td class="text-right">${{ number_format($sale->total, 2) }}</td>
        </tr>
    </table>

    <div class="divider"></div>

    {{-- Pago --}}
    @php
        $methodLabels = [
            'cash' => 'Efectivo',
            'card' => 'Tarjeta',
            'transfer' => 'Transferencia',
            'points_redemption' => 'Puntos',
            'mixed' => 'Mixto'
        ];
    @endphp
    <div class="meta-line">
        <span>Forma de Pago:</span>
        <span class="font-bold">{{ $methodLabels[$sale->payment_method] ?? ucfirst($sale->payment_method) }}</span>
    </div>
    @if($sale->cash_received > 0)
    <div class="meta-line">
        <span>Efectivo recibido:</span>
        <span>${{ number_format($sale->cash_received, 2) }}</span>
    </div>
    <div class="meta-line">
        <span>Cambio:</span>
        <span class="font-bold">${{ number_format($sale->change_given, 2) }}</span>
    </div>
    @endif

    {{-- Sección Cashback Puntos --}}
    @if($sale->customer)
        <div class="points-box">
            @if($sale->points_earned > 0)
                <div class="font-bold" style="color: #000;">★ ¡GANASTE {{ number_format($sale->points_earned, 2) }} PUNTOS! ★</div>
            @endif
            <div>Saldo disponible: <strong>{{ number_format($sale->customer->points_balance, 2) }} pts</strong></div>
            <div style="font-size: 10px; color: #444;">(Equivalente a ${{ number_format($sale->customer->points_balance * ($config->distributor_point_value ?? 1), 2) }} en compras)</div>
        </div>
    @endif

    <div class="divider"></div>

    <div class="text-center" style="font-size: 11px; margin-top: 8px;">
        <p>¡GRACIAS POR SU COMPRA!</p>
        <p style="font-size: 10px; color: #555; margin-top: 4px;">Tootli Distribuidora</p>
    </div>

    {{-- Botones de acción (no se imprimen) --}}
    <div class="actions">
        <button class="btn btn-print" onclick="window.print()">🖨️ Imprimir</button>
        <button class="btn btn-close" onclick="window.close()">Cerrar</button>
    </div>
</div>

<script>
    // Auto impresión al abrir si viene con parámetro ?print=1
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('print') === '1') {
        window.addEventListener('load', () => {
            window.print();
        });
    }
</script>
</body>
</html>
