<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#0f172a">
    <title>Seguimiento de pedido — Tootli Directo</title>
    <style>
        :root {
            --bg: #0f172a;
            --card: #1e293b;
            --muted: #94a3b8;
            --text: #f8fafc;
            --accent: #38bdf8;
            --ok: #4ade80;
            --warn: #fbbf24;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: radial-gradient(1200px 600px at 50% -10%, #1d4ed8 0%, transparent 55%), var(--bg);
            color: var(--text);
            min-height: 100vh;
            padding: 20px 16px 32px;
        }
        .wrap { max-width: 440px; margin: 0 auto; }
        .brand {
            font-size: 13px;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 4px;
        }
        h1 { font-size: 1.35rem; font-weight: 700; margin: 0 0 16px; }
        .card {
            background: rgba(30, 41, 59, 0.92);
            border: 1px solid rgba(148, 163, 184, 0.15);
            border-radius: 16px;
            padding: 18px 16px;
            margin-bottom: 14px;
            backdrop-filter: blur(8px);
        }
        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 14px;
            font-weight: 600;
            background: rgba(56, 189, 248, 0.15);
            color: var(--accent);
            margin-bottom: 10px;
        }
        .dot {
            width: 8px; height: 8px; border-radius: 50%;
            background: var(--accent);
            animation: pulse 1.4s ease-in-out infinite;
        }
        @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.35} }
        .muted { color: var(--muted); font-size: 13px; line-height: 1.45; }
        .steps { margin-top: 14px; }
        .step {
            display: flex;
            gap: 10px;
            align-items: flex-start;
            padding: 10px 0;
            border-bottom: 1px solid rgba(148, 163, 184, 0.12);
        }
        .step:last-child { border-bottom: 0; }
        .step-ic {
            width: 28px; height: 28px; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 14px; flex-shrink: 0;
            background: rgba(148, 163, 184, 0.12);
        }
        .step.done .step-ic { background: rgba(74, 222, 128, 0.2); }
        .step.active .step-ic { background: rgba(56, 189, 248, 0.25); box-shadow: 0 0 0 2px rgba(56,189,248,.35); }
        .step-t { font-size: 14px; font-weight: 600; }
        .step-d { font-size: 12px; color: var(--muted); margin-top: 2px; }
        .dm {
            display: flex; align-items: center; gap: 12px;
            margin-top: 8px;
        }
        .dm img {
            width: 48px; height: 48px; border-radius: 12px; object-fit: cover;
            background: rgba(148,163,184,.2);
        }
        .otp {
            margin-top: 12px;
            padding: 12px;
            border-radius: 12px;
            background: rgba(251, 191, 36, 0.12);
            border: 1px solid rgba(251, 191, 36, 0.35);
            font-size: 14px;
        }
        .otp strong { font-size: 22px; letter-spacing: .2em; color: var(--warn); }
        .err {
            text-align: center;
            padding: 40px 12px;
            color: var(--muted);
        }
        .live {
            font-size: 11px;
            color: var(--muted);
            margin-top: 10px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
    </style>
</head>
<body>
<div class="wrap">
    <div class="brand">Tootli Directo</div>
    <h1>Seguimiento de tu pedido</h1>
    <div id="root">
        <div class="card">
            <div class="muted" style="text-align:center;padding:12px;">Cargando…</div>
        </div>
    </div>
</div>
<script>
(function () {
    const pollUrl = @json($pollUrl);
    const steps = [
        { key: 'received', labels: ['Pedido recibido', 'La tienda confirmó tu pedido.'] },
        { key: 'kitchen', labels: ['En preparación', 'Tu orden se está preparando.'] },
        { key: 'handover', labels: ['Listo para reparto', 'El repartidor recogerá tu pedido en la tienda.'] },
        { key: 'trip', labels: ['En camino', 'El repartidor va hacia tu domicilio.'] },
        { key: 'done', labels: ['Entregado', '¡Gracias por tu preferencia!'] },
    ];

    function statusStep(s) {
        const m = {
            pending: 0, confirmed: 0, accepted: 0,
            processing: 1,
            handover: 2,
            picked_up: 3,
            delivered: 4, partial_delivered: 4,
            canceled: -1, cancelled: -1, failed: -1, refunded: -1,
        };
        return m[s] !== undefined ? m[s] : 0;
    }

    function render(data) {
        const root = document.getElementById('root');
        if (!data || !data.ok) {
            root.innerHTML = '<div class="card err">No encontramos este enlace o ya expiró. Si necesitas ayuda, contacta al restaurante.</div>';
            return;
        }
        const si = statusStep(data.order_status);
        const canceled = si < 0;
        let html = '<div class="card">';
        if (canceled) {
            html += '<div class="status-pill" style="background:rgba(248,113,113,.15);color:#fecaca;"><span class="dot" style="background:#f87171"></span> Pedido cancelado o cerrado</div>';
        } else {
            html += '<div class="status-pill"><span class="dot"></span> ' + escapeHtml(data.order_status_label || '') + '</div>';
        }
        html += '<div class="muted">Orden #' + escapeHtml(String(data.order_id)) + '</div>';
        if (data.store_name) {
            html += '<div style="margin-top:8px;font-weight:600;font-size:15px;">' + escapeHtml(data.store_name) + '</div>';
        }
        if (data.address) {
            html += '<div class="muted" style="margin-top:6px;">' + escapeHtml(data.address) + '</div>';
        }
        if (data.delivery_man && data.delivery_man.name) {
            html += '<div class="dm"><div>';
            if (data.delivery_man.image) {
                html += '<img src="' + escapeHtml(data.delivery_man.image) + '" alt="">';
            }
            html += '</div><div><div style="font-weight:600;">' + escapeHtml(data.delivery_man.name) + '</div>';
            html += '<div class="muted">Repartidor</div></div></div>';
        }
        if (data.otp) {
            html += '<div class="otp">Código de entrega (díselo al repartidor): <strong>' + escapeHtml(data.otp) + '</strong></div>';
        }
        html += '<div class="steps">';
        steps.forEach((st, idx) => {
            let cls = 'step';
            if (!canceled) {
                if (idx < si) cls += ' done';
                if (idx === si) cls += ' active';
            }
            html += '<div class="' + cls + '"><div class="step-ic">' + (idx < si && !canceled ? '✓' : (idx + 1)) + '</div>';
            html += '<div><div class="step-t">' + escapeHtml(st.labels[0]) + '</div>';
            html += '<div class="step-d">' + escapeHtml(st.labels[1]) + '</div></div></div>';
        });
        html += '</div>';
        html += '<div class="live"><span class="dot"></span> Actualización automática cada pocos segundos</div>';
        html += '</div>';
        root.innerHTML = html;
    }

    function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, function (c) {
            return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]);
        });
    }

    async function tick() {
        try {
            const r = await fetch(pollUrl, { headers: { 'Accept': 'application/json' }, cache: 'no-store' });
            const j = await r.json();
            render(j);
        } catch (e) {
            console.warn(e);
        }
    }

    tick();
    setInterval(tick, 6000);
})();
</script>
</body>
</html>
