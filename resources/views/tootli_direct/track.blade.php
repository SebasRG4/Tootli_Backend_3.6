<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#ffffff">
    <title>Seguimiento — Tootli Directo</title>
    @if(!empty($mapboxPublicToken))
    <link href="https://api.mapbox.com/mapbox-gl-js/v3.4.0/mapbox-gl.css" rel="stylesheet">
    <script src="https://api.mapbox.com/mapbox-gl-js/v3.4.0/mapbox-gl.js"></script>
    @endif
    <style>
        :root {
            --green: #22c55e;
            --green-soft: #dcfce7;
            --ink: #0f172a;
            --muted: #64748b;
            --line: #e2e8f0;
            --sheet: #ffffff;
        }
        * { box-sizing: border-box; }
        html, body { height: 100%; margin: 0; }
        body {
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: #f8fafc;
            color: var(--ink);
            display: flex;
            flex-direction: column;
            min-height: 100%;
        }
        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 14px 6px;
            background: #fff;
            border-bottom: 1px solid var(--line);
        }
        .topbar button {
            border: none;
            background: transparent;
            font-size: 22px;
            line-height: 1;
            padding: 6px 10px;
            color: var(--muted);
            cursor: pointer;
            border-radius: 10px;
        }
        .topbar button:active { background: #f1f5f9; }
        .topbar span {
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0.04em;
            color: var(--muted);
        }
        .eta-block {
            background: #fff;
            padding: 16px 18px 12px;
        }
        .eta-row {
            display: flex;
            align-items: baseline;
            gap: 12px;
        }
        .eta-clock {
            font-size: 2.5rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            line-height: 1;
            font-variant-numeric: tabular-nums;
        }
        .eta-label {
            font-size: 13px;
            color: var(--muted);
            font-weight: 500;
        }
        .headline {
            background: #fff;
            padding: 0 18px 14px;
            font-size: 15px;
            font-weight: 600;
            line-height: 1.35;
        }
        .progress {
            display: flex;
            gap: 5px;
            padding: 0 18px 14px;
            background: #fff;
        }
        .progress span {
            flex: 1;
            height: 4px;
            border-radius: 999px;
            background: #e2e8f0;
            transition: background 0.25s ease;
        }
        .progress span.on {
            background: var(--green);
        }
        #map-wrap {
            flex: 1;
            min-height: 280px;
            position: relative;
            background: #e2e8f0;
        }
        #map { position: absolute; inset: 0; }
        .map-fallback, .map-msg {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 24px;
            font-size: 14px;
            color: var(--muted);
            background: #f1f5f9;
        }
        .sheet {
            background: var(--sheet);
            border-radius: 20px 20px 0 0;
            box-shadow: 0 -8px 32px rgba(15, 23, 42, 0.08);
            padding: 10px 18px 22px;
            max-width: 560px;
            margin: 0 auto;
            width: 100%;
        }
        .sheet-handle {
            width: 36px;
            height: 4px;
            border-radius: 999px;
            background: #cbd5e1;
            margin: 4px auto 14px;
        }
        .dm-row {
            display: flex;
            gap: 14px;
            align-items: center;
        }
        .dm-avatar {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            object-fit: cover;
            background: #e2e8f0;
            flex-shrink: 0;
        }
        .dm-avatar.placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            color: var(--muted);
        }
        .dm-meta h2 {
            margin: 0 0 4px;
            font-size: 17px;
            font-weight: 700;
        }
        .dm-meta p {
            margin: 0;
            font-size: 13px;
            color: var(--muted);
            line-height: 1.35;
        }
        .otp-box {
            margin-top: 14px;
            padding: 12px 14px;
            border-radius: 12px;
            background: var(--green-soft);
            border: 1px solid #bbf7d0;
            font-size: 14px;
        }
        .otp-box strong {
            font-size: 22px;
            letter-spacing: 0.15em;
            color: #166534;
        }
        .err-page {
            padding: 40px 20px;
            text-align: center;
            color: var(--muted);
        }
    </style>
</head>
<body>
<div id="app" style="display:flex;flex-direction:column;min-height:100%;">
    <div class="topbar">
        <button type="button" onclick="history.length > 1 ? history.back() : (window.location.href='https://tootli.mx')" aria-label="Cerrar">×</button>
        <span>TOOTLI DIRECTO</span>
        <span style="width:40px"></span>
    </div>
    <div id="main" style="display:none;flex-direction:column;flex:1;">
        <div class="eta-block">
            <div class="eta-row">
                <div class="eta-clock" id="eta-clock">—:—</div>
                <div class="eta-label">Llegada estimada</div>
            </div>
        </div>
        <div class="headline" id="headline"></div>
        <div class="progress" id="progress"></div>
        <div id="map-wrap">
            <div id="map"></div>
            <div id="map-fallback" class="map-fallback" style="display:none;"></div>
        </div>
        <div class="sheet">
            <div class="sheet-handle"></div>
            <div class="dm-row" id="dm-block" style="display:none;">
                <img class="dm-avatar" id="dm-img" alt="" style="display:none;">
                <div class="dm-avatar placeholder" id="dm-ph">🚴</div>
                <div class="dm-meta">
                    <h2 id="dm-name"></h2>
                    <p id="dm-vehicle"></p>
                </div>
            </div>
            <div id="no-dm" class="dm-meta" style="padding:8px 0;color:var(--muted);font-size:14px;">
                Cuando se asigne un repartidor, verás su nombre y ubicación en el mapa.
            </div>
            <div id="otp-wrap" class="otp-box" style="display:none;">
                Código de entrega: <strong id="otp-val"></strong>
            </div>
        </div>
    </div>
    <div id="err" class="err-page" style="display:none;"></div>
</div>
<script>
(function () {
    const pollUrl = @json($pollUrl);
    const mapboxToken = @json($mapboxPublicToken ?? '');

    const els = {
        main: document.getElementById('main'),
        err: document.getElementById('err'),
        eta: document.getElementById('eta-clock'),
        headline: document.getElementById('headline'),
        progress: document.getElementById('progress'),
        mapEl: document.getElementById('map'),
        mapFb: document.getElementById('map-fallback'),
        dmBlock: document.getElementById('dm-block'),
        noDm: document.getElementById('no-dm'),
        dmImg: document.getElementById('dm-img'),
        dmPh: document.getElementById('dm-ph'),
        dmName: document.getElementById('dm-name'),
        dmVehicle: document.getElementById('dm-vehicle'),
        otpWrap: document.getElementById('otp-wrap'),
        otpVal: document.getElementById('otp-val'),
    };

    let map = null;
    let markers = { pickup: null, dropoff: null, courier: null };

    function buildProgress(n) {
        els.progress.innerHTML = '';
        for (let i = 0; i < 5; i++) {
            const s = document.createElement('span');
            if (i < n) s.classList.add('on');
            els.progress.appendChild(s);
        }
    }

    function ensureMap() {
        if (map || !mapboxToken || typeof mapboxgl === 'undefined') return;
        mapboxgl.accessToken = mapboxToken;
        map = new mapboxgl.Map({
            container: 'map',
            style: 'mapbox://styles/mapbox/light-v11',
            center: [-99.13, 19.43],
            zoom: 12,
            attributionControl: true,
        });
        map.addControl(new mapboxgl.NavigationControl({ showCompass: false }), 'top-right');
    }

    function setMarker(key, lng, lat, color) {
        if (!map || lng == null || lat == null) return;
        if (markers[key]) {
            markers[key].setLngLat([lng, lat]);
            return;
        }
        const el = document.createElement('div');
        el.style.width = '18px';
        el.style.height = '18px';
        el.style.borderRadius = '50%';
        el.style.background = color;
        el.style.border = '2px solid #fff';
        el.style.boxShadow = '0 1px 4px rgba(0,0,0,.25)';
        markers[key] = new mapboxgl.Marker({ element: el }).setLngLat([lng, lat]).addTo(map);
    }

    function fitMap(pickup, dropoff, courier) {
        if (!map) return;
        const b = [];
        if (pickup && pickup.lng != null && pickup.lat != null) b.push([pickup.lng, pickup.lat]);
        if (dropoff && dropoff.lng != null && dropoff.lat != null) b.push([dropoff.lng, dropoff.lat]);
        if (courier && courier.lng != null && courier.lat != null) b.push([courier.lng, courier.lat]);
        if (b.length >= 2) {
            const bounds = new mapboxgl.LngLatBounds(b[0], b[0]);
            for (let i = 1; i < b.length; i++) bounds.extend(b[i]);
            map.fitBounds(bounds, { padding: 56, maxZoom: 15, duration: 600 });
        } else if (b.length === 1) {
            map.jumpTo({ center: b[0], zoom: 14 });
        }
    }

    function apply(d) {
        if (!d || !d.ok) {
            els.err.style.display = 'block';
            els.err.textContent = 'No encontramos este enlace o expiró.';
            els.main.style.display = 'none';
            return;
        }
        els.main.style.display = 'flex';
        els.err.style.display = 'none';

        if (d.order_status === 'delivered' || d.order_status === 'partial_delivered') {
            els.eta.textContent = '✓';
        } else {
            els.eta.textContent = d.estimated_arrival_clock || '—:—';
        }
        els.headline.textContent = d.headline || '';
        buildProgress(Math.min(5, Math.max(0, d.progress_filled || 0)));

        if (mapboxToken && typeof mapboxgl !== 'undefined') {
            els.mapFb.style.display = 'none';
            els.mapEl.style.display = 'block';
            ensureMap();
            if (d.pickup) setMarker('pickup', d.pickup.lng, d.pickup.lat, '#16a34a');
            if (d.dropoff) setMarker('dropoff', d.dropoff.lng, d.dropoff.lat, '#0f172a');
            if (d.courier) setMarker('courier', d.courier.lng, d.courier.lat, '#2563eb');
            fitMap(d.pickup, d.dropoff, d.courier);
            map && map.resize();
        } else {
            els.mapEl.style.display = 'none';
            els.mapFb.style.display = 'flex';
            els.mapFb.textContent = 'Para ver el mapa en vivo, configura MAPBOX_PUBLIC_ACCESS_TOKEN (token público pk.…, restricción por dominio) en el servidor.';
        }

        if (d.delivery_man && d.delivery_man.name) {
            els.dmBlock.style.display = 'flex';
            els.noDm.style.display = 'none';
            els.dmName.textContent = d.delivery_man.name;
            els.dmVehicle.textContent = d.delivery_man.vehicle || 'Repartidor Tootli';
            if (d.delivery_man.image) {
                els.dmImg.src = d.delivery_man.image;
                els.dmImg.style.display = 'block';
                els.dmPh.style.display = 'none';
            } else {
                els.dmImg.style.display = 'none';
                els.dmPh.style.display = 'flex';
            }
        } else {
            els.dmBlock.style.display = 'none';
            els.noDm.style.display = 'block';
        }

        if (d.otp) {
            els.otpWrap.style.display = 'block';
            els.otpVal.textContent = d.otp;
        } else {
            els.otpWrap.style.display = 'none';
        }
    }

    async function tick() {
        try {
            const r = await fetch(pollUrl, { headers: { 'Accept': 'application/json' }, cache: 'no-store' });
            const j = await r.json();
            apply(j);
        } catch (e) {
            console.warn(e);
        }
    }

    tick();
    setInterval(tick, 5000);
})();
</script>
</body>
</html>
