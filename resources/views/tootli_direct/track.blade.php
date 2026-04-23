<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#f8fafc">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Seguimiento — Tootli Directo</title>
    @if(!empty($mapboxPublicToken))
    <link href="https://api.mapbox.com/mapbox-gl-js/v3.4.0/mapbox-gl.css" rel="stylesheet">
    <script src="https://api.mapbox.com/mapbox-gl-js/v3.4.0/mapbox-gl.js"></script>
    @endif
    <style>
        :root {
            --green: #22c55e;
            --green-dark: #16a34a;
            --green-soft: #ecfdf5;
            --ink: #0f172a;
            --muted: #64748b;
            --line: #e2e8f0;
            --sheet: #f8fafc;
            --card: #f1f5f9;
        }
        * { box-sizing: border-box; }
        html, body { height: 100%; margin: 0; }
        body {
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: #eef2f6;
            color: var(--ink);
            min-height: 100%;
        }
        #app {
            max-width: 480px;
            margin: 0 auto;
            min-height: 100%;
            display: flex;
            flex-direction: column;
            background: #fff;
            box-shadow: 0 0 40px rgba(15, 23, 42, 0.06);
        }
        .td-header {
            background: #fff;
            padding: 8px 12px 10px;
            border-bottom: 1px solid var(--line);
        }
        .td-header__row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }
        .td-icon-btn {
            border: none;
            background: transparent;
            width: 40px;
            height: 40px;
            border-radius: 12px;
            font-size: 22px;
            line-height: 1;
            color: var(--muted);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .td-icon-btn:active { background: #f1f5f9; }
        .td-header__title {
            font-size: 15px;
            font-weight: 600;
            color: var(--ink);
            letter-spacing: -0.02em;
        }
        .td-header__brand {
            text-align: center;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.12em;
            color: #94a3b8;
            margin-top: 2px;
        }
        .td-hero {
            padding: 14px 18px 12px;
            background: #fff;
        }
        .td-hero__row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
        }
        .td-hero__time {
            font-size: 2.75rem;
            font-weight: 700;
            letter-spacing: -0.03em;
            line-height: 1;
            font-variant-numeric: tabular-nums;
            color: var(--ink);
        }
        .td-hero__meta {
            padding-top: 6px;
            text-align: right;
            flex: 1;
        }
        .td-hero__meta-line {
            font-size: 13px;
            color: var(--muted);
            font-weight: 500;
            line-height: 1.35;
        }
        .td-hero__meta-line strong {
            color: var(--ink);
            font-weight: 600;
        }
        .td-headline {
            margin: 0;
            padding: 0 18px 14px;
            background: #fff;
            font-size: 15px;
            font-weight: 600;
            line-height: 1.45;
            color: var(--ink);
        }
        .td-headline .td-spot {
            color: var(--green-dark);
            font-weight: 700;
        }
        .td-steps {
            display: flex;
            padding: 0 10px 16px;
            background: #fff;
            gap: 4px;
        }
        .td-step {
            flex: 1;
            text-align: center;
            min-width: 0;
        }
        .td-step__bar {
            height: 4px;
            border-radius: 999px;
            background: #e2e8f0;
            margin-bottom: 8px;
            transition: background 0.25s ease;
        }
        .td-step__bar.on { background: var(--green); }
        .td-step__label {
            font-size: 9px;
            font-weight: 600;
            color: #94a3b8;
            line-height: 1.2;
            text-transform: none;
            letter-spacing: 0;
        }
        .td-step--active .td-step__label { color: var(--green-dark); }
        #map-wrap {
            flex: 1;
            min-height: 260px;
            position: relative;
            background: #e8eef3;
        }
        #map { position: absolute; inset: 0; }
        .map-fallback {
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
        .td-sheet {
            background: var(--sheet);
            border-radius: 22px 22px 0 0;
            box-shadow: 0 -10px 40px rgba(15, 23, 42, 0.08);
            padding: 8px 16px calc(18px + env(safe-area-inset-bottom, 0));
            margin-top: -12px;
            position: relative;
            z-index: 2;
        }
        .td-sheet-handle {
            width: 40px;
            height: 4px;
            border-radius: 999px;
            background: #cbd5e1;
            margin: 6px auto 14px;
        }
        .td-dm-card {
            display: flex;
            align-items: center;
            gap: 12px;
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 12px 14px;
            margin-bottom: 12px;
        }
        .td-dm-card__avatar-wrap {
            flex-shrink: 0;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            padding: 3px;
            background: linear-gradient(145deg, var(--green), var(--green-dark));
        }
        .td-dm-card__avatar {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            background: #e2e8f0;
            display: block;
        }
        .td-dm-card__avatar.ph {
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            background: #e2e8f0;
        }
        .td-dm-card__body { flex: 1; min-width: 0; }
        .td-dm-card__name {
            margin: 0 0 4px;
            font-size: 16px;
            font-weight: 700;
            letter-spacing: -0.02em;
        }
        .td-dm-card__sub {
            margin: 0;
            font-size: 12px;
            color: var(--muted);
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }
        .td-dm-card__sub .td-star { color: #eab308; font-size: 13px; }
        .td-dm-card__phone {
            margin-top: 6px;
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 14px;
            font-weight: 600;
            color: var(--green-dark);
        }
        .td-dm-card__phone a {
            color: inherit;
            text-decoration: none;
        }
        .td-dm-card__actions {
            display: flex;
            flex-direction: column;
            gap: 8px;
            flex-shrink: 0;
        }
        .td-dm-card__btn {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            text-decoration: none;
            transition: transform 0.12s ease, opacity 0.12s;
        }
        .td-dm-card__btn:active { transform: scale(0.96); }
        .td-dm-card__btn--call {
            background: var(--green);
            color: #fff;
            box-shadow: 0 4px 14px rgba(34, 197, 94, 0.35);
        }
        .td-dm-card__btn--profile {
            background: #fff;
            border: 1px solid var(--line);
            color: #3b82f6;
        }
        .td-otp {
            margin-bottom: 12px;
            padding: 12px 14px;
            border-radius: 14px;
            background: var(--green-soft);
            border: 1px solid #bbf7d0;
            font-size: 14px;
        }
        .td-otp strong {
            font-size: 20px;
            letter-spacing: 0.12em;
            color: #166534;
        }
        .td-chat {
            background: #fff;
            border-radius: 18px;
            border: 1px solid var(--line);
            overflow: hidden;
        }
        .td-chat__head {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 14px;
            border-bottom: 1px solid var(--line);
        }
        .td-chat__dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--green);
            flex-shrink: 0;
        }
        .td-chat__title {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.08em;
            color: #94a3b8;
            text-transform: uppercase;
        }
        .td-chat__log {
            max-height: 240px;
            overflow-y: auto;
            padding: 12px 12px 8px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            background: #fafafa;
        }
        .td-msg {
            max-width: 88%;
            padding: 10px 12px 8px;
            border-radius: 16px;
            font-size: 14px;
            line-height: 1.4;
            position: relative;
        }
        .td-msg--dm {
            align-self: flex-start;
            background: #fff;
            border: 1px solid var(--line);
            color: var(--ink);
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
        }
        .td-msg--me {
            align-self: flex-end;
            background: var(--green);
            color: #fff;
            border: none;
        }
        .td-msg__time {
            display: block;
            text-align: right;
            font-size: 10px;
            font-weight: 600;
            margin-top: 6px;
            opacity: 0.65;
        }
        .td-msg--me .td-msg__time { color: rgba(255,255,255,0.9); }
        .td-quick {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            padding: 0 12px 10px;
            background: #fafafa;
        }
        .td-quick button {
            border: 1px solid #bbf7d0;
            background: #ecfdf5;
            color: #166534;
            font-size: 12px;
            font-weight: 600;
            padding: 8px 12px;
            border-radius: 999px;
            cursor: pointer;
        }
        .td-quick button:active { opacity: 0.85; }
        .td-chat__form {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px 12px;
            background: #fff;
            border-top: 1px solid var(--line);
        }
        .td-chat__form input {
            flex: 1;
            min-width: 0;
            border: 1px solid var(--line);
            border-radius: 999px;
            padding: 12px 16px;
            font-size: 15px;
            background: #fff;
        }
        .td-chat__form input::placeholder { color: #94a3b8; }
        .td-chat__send {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            border: none;
            background: var(--green);
            color: #fff;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(34, 197, 94, 0.35);
        }
        .td-chat__send:active { transform: scale(0.95); }
        .td-chat__note {
            margin: 0;
            padding: 8px 14px 10px;
            font-size: 11px;
            color: var(--muted);
            line-height: 1.4;
            background: #fff;
        }
        .err-page {
            padding: 48px 20px;
            text-align: center;
            color: var(--muted);
            font-size: 15px;
        }
        /* Map pins */
        .td-map-pin {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px solid #fff;
            box-shadow: 0 3px 12px rgba(15, 23, 42, 0.2);
        }
        .td-map-pin--pickup {
            background: linear-gradient(145deg, #4ade80, #16a34a);
        }
        .td-map-pin--pickup svg { width: 20px; height: 20px; color: #fff; }
        .td-map-pin--dest {
            background: #0f172a;
            border-color: #0f172a;
        }
        .td-map-pin--dest svg { width: 22px; height: 22px; display: block; }
        .td-map-pin--courier-wrap {
            width: 52px;
            height: 52px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .td-map-pin--courier {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: #fff;
            border: 3px solid var(--green);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 3px 14px rgba(34, 197, 94, 0.25);
        }
        .td-map-pin--courier img {
            width: 30px;
            height: 30px;
            object-fit: contain;
        }
        .td-map-pin--courier .td-scooter {
            font-size: 24px;
            line-height: 1;
        }
    </style>
</head>
<body>
<div id="app">
    <header class="td-header">
        <div class="td-header__row">
            <button type="button" class="td-icon-btn" onclick="history.length > 1 ? history.back() : (window.location.href='https://tootli.mx')" aria-label="Cerrar">×</button>
            <span class="td-header__title">tootli-tracking</span>
            <button type="button" class="td-icon-btn" id="btn-more" aria-label="Más opciones">⋯</button>
        </div>
        <div class="td-header__brand">TOOTLI DIRECTO</div>
    </header>

    <div id="main" style="display:none;flex-direction:column;flex:1;">
        <section class="td-hero">
            <div class="td-hero__row">
                <div class="td-hero__time" id="eta-clock">—:—</div>
                <div class="td-hero__meta">
                    <div class="td-hero__meta-line" id="eta-meta">Llegada estimada</div>
                </div>
            </div>
        </section>
        <p class="td-headline" id="headline"></p>
        <div class="td-steps" id="progress"></div>

        <div id="map-wrap">
            <div id="map"></div>
            <div id="map-fallback" class="map-fallback" style="display:none;"></div>
        </div>

        <div class="td-sheet">
            <div class="td-sheet-handle" aria-hidden="true"></div>

            <div class="td-dm-card" id="dm-block" style="display:none;">
                <div class="td-dm-card__avatar-wrap">
                    <img class="td-dm-card__avatar" id="dm-img" alt="" style="display:none;">
                    <div class="td-dm-card__avatar ph" id="dm-ph">👤</div>
                </div>
                <div class="td-dm-card__body">
                    <h2 class="td-dm-card__name" id="dm-name"></h2>
                    <p class="td-dm-card__sub" id="dm-sub"></p>
                    <div class="td-dm-card__phone" id="dm-phone-row" style="display:none;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        <a id="dm-phone" href="#"></a>
                    </div>
                </div>
                <div class="td-dm-card__actions">
                    <a class="td-dm-card__btn td-dm-card__btn--call" id="dm-call-btn" href="#" aria-label="Llamar">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    </a>
                    <button type="button" class="td-dm-card__btn td-dm-card__btn--profile" id="dm-focus-chat" aria-label="Ir al chat">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </button>
                </div>
            </div>
            <div id="no-dm" style="padding:10px 4px 14px;color:var(--muted);font-size:14px;text-align:center;">
                Cuando se asigne un repartidor, verás su datos y podrás chatear aquí.
            </div>

            <div id="otp-wrap" class="td-otp" style="display:none;">
                Código de entrega: <strong id="otp-val"></strong>
            </div>

            <div class="td-chat" id="chat-box">
                <div class="td-chat__head">
                    <span class="td-chat__dot"></span>
                    <span class="td-chat__title">Chat con tu repartidor</span>
                </div>
                <div class="td-chat__log" id="chat-log" aria-live="polite"></div>
                <div class="td-quick" id="chat-quick"></div>
                <form class="td-chat__form" id="chat-form" action="#" method="post">
                    <input type="text" id="chat-input" maxlength="2000" placeholder="Escribe un mensaje..." autocomplete="off">
                    <button type="submit" class="td-chat__send" aria-label="Enviar">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
                    </button>
                </form>
                <p class="td-chat__note">El repartidor responde desde la app Tootli Repartidor. Puedes usar los atajos o escribir abajo.</p>
            </div>
        </div>
    </div>
    <div id="err" class="err-page" style="display:none;"></div>
</div>
<script>
(function () {
    const pollUrl = @json($pollUrl);
    const chatUrl = @json($chatUrl ?? '');
    const mapboxToken = @json($mapboxPublicToken ?? '');
    const courierMarkerUrl = @json($courierMarkerUrl ?? '');
    const csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';

    const STEP_LABELS = ['Confirmado', 'Asignado', 'Preparando', 'En camino', 'Entregado'];
    const QUICK_REPLIES = ['¿Dónde estás?', 'Estoy en casa 🏠', 'Ok, gracias', '¿Cuánto tardas?'];

    const els = {
        main: document.getElementById('main'),
        err: document.getElementById('err'),
        eta: document.getElementById('eta-clock'),
        etaMeta: document.getElementById('eta-meta'),
        headline: document.getElementById('headline'),
        progress: document.getElementById('progress'),
        mapEl: document.getElementById('map'),
        mapFb: document.getElementById('map-fallback'),
        dmBlock: document.getElementById('dm-block'),
        noDm: document.getElementById('no-dm'),
        dmImg: document.getElementById('dm-img'),
        dmPh: document.getElementById('dm-ph'),
        dmName: document.getElementById('dm-name'),
        dmSub: document.getElementById('dm-sub'),
        otpWrap: document.getElementById('otp-wrap'),
        otpVal: document.getElementById('otp-val'),
        dmPhoneRow: document.getElementById('dm-phone-row'),
        dmPhone: document.getElementById('dm-phone'),
        dmCallBtn: document.getElementById('dm-call-btn'),
        chatLog: document.getElementById('chat-log'),
        chatQuick: document.getElementById('chat-quick'),
        chatForm: document.getElementById('chat-form'),
        chatInput: document.getElementById('chat-input'),
        btnMore: document.getElementById('btn-more'),
        dmFocusChat: document.getElementById('dm-focus-chat'),
    };

    let map = null;
    let markers = { pickup: null, dropoff: null, courier: null };
    let lastChatMessageId = 0;
    let chatPollStarted = false;
    let routeLayerReady = false;

    function escapeHtml(s) {
        return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function setHeadline(d) {
        const h = d.headline || '';
        const spot = (d.headline_highlight || '').trim();
        if (spot && h.indexOf(spot) !== -1) {
            const i = h.indexOf(spot);
            els.headline.innerHTML = escapeHtml(h.slice(0, i)) + '<span class="td-spot">' + escapeHtml(spot) + '</span>' + escapeHtml(h.slice(i + spot.length));
        } else {
            els.headline.textContent = h;
        }
    }

    function buildSteps(n) {
        els.progress.innerHTML = '';
        const filled = Math.min(5, Math.max(0, n || 0));
        for (let i = 0; i < 5; i++) {
            const step = document.createElement('div');
            step.className = 'td-step' + (i === filled - 1 && filled > 0 ? ' td-step--active' : '');
            const bar = document.createElement('div');
            bar.className = 'td-step__bar' + (i < filled ? ' on' : '');
            const lab = document.createElement('div');
            lab.className = 'td-step__label';
            lab.textContent = STEP_LABELS[i];
            step.appendChild(bar);
            step.appendChild(lab);
            els.progress.appendChild(step);
        }
    }

    function formatChatTime(iso) {
        if (!iso) return '';
        try {
            const t = new Date(iso);
            if (isNaN(t.getTime())) return '';
            return t.toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit', hour12: false });
        } catch (e) { return ''; }
    }

    function renderChatMessages(list) {
        if (!els.chatLog) return;
        list.forEach(function (m) {
            if (m.id <= lastChatMessageId) return;
            const isDm = m.sender === 'delivery_man';
            const row = document.createElement('div');
            row.className = 'td-msg ' + (isDm ? 'td-msg--dm' : 'td-msg--me');
            row.textContent = m.body || '';
            const tm = document.createElement('span');
            tm.className = 'td-msg__time';
            tm.textContent = formatChatTime(m.created_at);
            row.appendChild(tm);
            els.chatLog.appendChild(row);
            lastChatMessageId = m.id;
        });
        els.chatLog.scrollTop = els.chatLog.scrollHeight;
    }

    async function fetchChat() {
        if (!chatUrl) return;
        try {
            var r = await fetch(chatUrl, { headers: { 'Accept': 'application/json' }, cache: 'no-store' });
            if (!r.ok) return;
            var j = await r.json();
            if (!j.ok || !Array.isArray(j.messages)) return;
            renderChatMessages(j.messages);
        } catch (e) { console.warn(e); }
    }

    function startChatPolling() {
        if (!chatUrl || chatPollStarted) return;
        chatPollStarted = true;
        fetchChat();
        setInterval(fetchChat, 4000);
    }

    function pickupSvg() {
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m2 7 4-4 4 4"/><path d="M22 7v11a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V7"/><path d="M6 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16"/></svg>';
    }
    function destSvg() {
        return '<svg viewBox="0 0 24 24" fill="none"><path d="M12 21s7-4.35 7-11a7 7 0 1 0-14 0c0 6.65 7 11 7 11z" fill="#ef4444"/><circle cx="12" cy="10" r="2.5" fill="#fff"/></svg>';
    }

    function buildMarkerElement(kind) {
        if (kind === 'courier') {
            const wrap = document.createElement('div');
            wrap.className = 'td-map-pin--courier-wrap';
            const inner = document.createElement('div');
            inner.className = 'td-map-pin td-map-pin--courier';
            if (courierMarkerUrl) {
                const img = document.createElement('img');
                img.src = courierMarkerUrl;
                img.alt = '';
                inner.appendChild(img);
            } else {
                const sp = document.createElement('span');
                sp.className = 'td-scooter';
                sp.textContent = '🛵';
                inner.appendChild(sp);
            }
            wrap.appendChild(inner);
            return wrap;
        }
        if (kind === 'pickup') {
            const el = document.createElement('div');
            el.className = 'td-map-pin td-map-pin--pickup';
            el.innerHTML = pickupSvg();
            return el;
        }
        const el = document.createElement('div');
        el.className = 'td-map-pin td-map-pin--dest';
        el.innerHTML = destSvg();
        return el;
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
        map.on('load', function () {
            try {
                if (!map.getSource('td-route')) {
                    map.addSource('td-route', {
                        type: 'geojson',
                        data: { type: 'Feature', properties: {}, geometry: { type: 'LineString', coordinates: [[0, 0], [0, 0]] } },
                    });
                    map.addLayer({
                        id: 'td-route-line',
                        type: 'line',
                        source: 'td-route',
                        layout: { visibility: 'none', 'line-cap': 'round', 'line-join': 'round' },
                        paint: {
                            'line-color': '#22c55e',
                            'line-width': 3,
                            'line-dasharray': [1.2, 2],
                            'line-opacity': 0.9,
                        },
                    });
                }
            } catch (e) { console.warn(e); }
            routeLayerReady = true;
            if (window.__tdRoutePayload) {
                updateRouteLine(window.__tdRoutePayload);
            }
        });
    }

    function normalizeRouteCoords(raw) {
        if (!Array.isArray(raw) || raw.length < 2) return null;
        var out = [];
        for (var i = 0; i < raw.length; i++) {
            var p = raw[i];
            if (!Array.isArray(p) || p.length < 2) continue;
            var lng = Number(p[0]);
            var lat = Number(p[1]);
            if (!isFinite(lng) || !isFinite(lat)) continue;
            out.push([lng, lat]);
        }
        return out.length >= 2 ? out : null;
    }

    function updateRouteLine(d) {
        window.__tdRoutePayload = d;
        if (!map || !routeLayerReady || !map.getSource('td-route')) return;
        var coords = normalizeRouteCoords(d.route_coordinates);
        var c = d.courier;
        var drop = d.dropoff;
        var pick = d.pickup;
        if (!coords && c && drop && c.lng != null && c.lat != null && drop.lng != null && drop.lat != null) {
            coords = [[c.lng, c.lat], [drop.lng, drop.lat]];
        }
        if (!coords && pick && drop && pick.lng != null && pick.lat != null && drop.lng != null && drop.lat != null) {
            coords = [[pick.lng, pick.lat], [drop.lng, drop.lat]];
        }
        if (coords) {
            map.getSource('td-route').setData({
                type: 'Feature',
                properties: {},
                geometry: { type: 'LineString', coordinates: coords },
            });
            try {
                map.setLayoutProperty('td-route-line', 'visibility', 'visible');
            } catch (e2) {}
        } else {
            try {
                map.setLayoutProperty('td-route-line', 'visibility', 'none');
            } catch (e3) {}
        }
    }

    function setMarker(key, lng, lat) {
        if (!map || lng == null || lat == null) return;
        if (markers[key]) {
            markers[key].setLngLat([lng, lat]);
            return;
        }
        const kind = key === 'pickup' ? 'pickup' : (key === 'courier' ? 'courier' : 'dropoff');
        const el = buildMarkerElement(kind);
        markers[key] = new mapboxgl.Marker({ element: el, anchor: 'center' })
            .setLngLat([lng, lat])
            .addTo(map);
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
            map.fitBounds(bounds, { padding: 52, maxZoom: 15, duration: 600 });
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
            els.etaMeta.textContent = 'Pedido entregado';
        } else {
            els.eta.textContent = d.estimated_arrival_clock || '—:—';
            let meta = 'Llegada estimada';
            if (d.eta_minutes != null && d.eta_minutes > 0) {
                meta += ' · ~' + Math.round(d.eta_minutes) + ' min';
            }
            els.etaMeta.textContent = meta;
        }

        setHeadline(d);
        buildSteps(d.progress_filled || 0);

        if (mapboxToken && typeof mapboxgl !== 'undefined') {
            els.mapFb.style.display = 'none';
            els.mapEl.style.display = 'block';
            ensureMap();
            if (d.pickup) setMarker('pickup', d.pickup.lng, d.pickup.lat);
            if (d.dropoff) setMarker('dropoff', d.dropoff.lng, d.dropoff.lat);
            if (d.courier) setMarker('courier', d.courier.lng, d.courier.lat);
            fitMap(d.pickup, d.dropoff, d.courier);
            updateRouteLine(d);
            map && map.resize();
        } else {
            els.mapEl.style.display = 'none';
            els.mapFb.style.display = 'flex';
            els.mapFb.textContent = 'Para ver el mapa en vivo, configura MAPBOX_PUBLIC_ACCESS_TOKEN (token público pk.…) en el servidor.';
        }

        if (d.delivery_man && d.delivery_man.name) {
            els.dmBlock.style.display = 'flex';
            els.noDm.style.display = 'none';
            els.dmName.textContent = d.delivery_man.name;
            els.dmSub.innerHTML = '';
            const base = document.createTextNode((d.delivery_man.vehicle || 'Repartidor Tootli'));
            els.dmSub.appendChild(base);
            if (d.delivery_man.rating_avg) {
                els.dmSub.appendChild(document.createTextNode(' · '));
                const st = document.createElement('span');
                st.className = 'td-star';
                st.textContent = '★';
                els.dmSub.appendChild(st);
                els.dmSub.appendChild(document.createTextNode(' ' + d.delivery_man.rating_avg));
            }
            if (d.delivery_man.phone) {
                els.dmPhoneRow.style.display = 'flex';
                els.dmPhone.textContent = d.delivery_man.phone;
                const tel = 'tel:' + String(d.delivery_man.phone).replace(/[^\d+]/g, '');
                els.dmPhone.setAttribute('href', tel);
                els.dmCallBtn.style.display = '';
                els.dmCallBtn.setAttribute('href', tel);
            } else {
                els.dmPhoneRow.style.display = 'none';
                els.dmCallBtn.style.display = 'none';
                els.dmCallBtn.setAttribute('href', '#');
            }
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

        startChatPolling();

        if (d.otp) {
            els.otpWrap.style.display = 'block';
            els.otpVal.textContent = d.otp;
        } else {
            els.otpWrap.style.display = 'none';
        }
    }

    function buildQuickReplies() {
        if (!els.chatQuick || els.chatQuick.dataset.ready) return;
        els.chatQuick.dataset.ready = '1';
        QUICK_REPLIES.forEach(function (txt) {
            const b = document.createElement('button');
            b.type = 'button';
            b.textContent = txt;
            b.addEventListener('click', function () {
                els.chatInput.value = txt;
                els.chatInput.focus();
            });
            els.chatQuick.appendChild(b);
        });
    }
    buildQuickReplies();

    if (els.dmFocusChat) {
        els.dmFocusChat.addEventListener('click', function () {
            els.chatInput && els.chatInput.focus();
        });
    }
    if (els.btnMore) {
        els.btnMore.addEventListener('click', async function () {
            try {
                await navigator.clipboard.writeText(window.location.href);
                alert('Enlace copiado al portapapeles.');
            } catch (e) {
                prompt('Copia este enlace:', window.location.href);
            }
        });
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

    if (els.chatForm) {
        els.chatForm.addEventListener('submit', async function (ev) {
            ev.preventDefault();
            var t = (els.chatInput.value || '').trim();
            if (!t || !chatUrl) return;
            try {
                var r = await fetch(chatUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ message: t, _token: csrf }),
                });
                if (r.ok) {
                    els.chatInput.value = '';
                    await fetchChat();
                }
            } catch (e) { console.warn(e); }
        });
    }

    tick();
    setInterval(tick, 5000);
})();
</script>
</body>
</html>
