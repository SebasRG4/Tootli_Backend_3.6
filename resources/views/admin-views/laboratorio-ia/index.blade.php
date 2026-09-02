@extends('layouts.admin.app')

@section('title', 'Laboratorio IA — Tootli')

@push('css_or_js')
    <style>
        /* ── Variables ── */
        :root {
            --lab-purple: #7c3aed;
            --lab-purple-light: #a78bfa;
            --lab-indigo: #4f46e5;
            --lab-pink: #ec4899;
            --lab-cyan: #06b6d4;
            --lab-dark: #0f0f1a;
            --lab-card: #1a1a2e;
            --lab-border: rgba(124, 58, 237, 0.25);
            --lab-glow: rgba(124, 58, 237, 0.15);
        }

        /* ── Header ── */
        .lab-header {
            background: linear-gradient(135deg, #0f0f1a 0%, #1a1230 50%, #0d1117 100%);
            border-radius: 16px;
            padding: 2rem 2.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid var(--lab-border);
            position: relative;
            overflow: hidden;
        }
        .lab-header::before {
            content: '';
            position: absolute;
            top: -60px; right: -60px;
            width: 220px; height: 220px;
            background: radial-gradient(circle, rgba(124,58,237,0.3) 0%, transparent 70%);
            pointer-events: none;
        }
        .lab-header::after {
            content: '';
            position: absolute;
            bottom: -40px; left: 30%;
            width: 160px; height: 160px;
            background: radial-gradient(circle, rgba(6,182,212,0.2) 0%, transparent 70%);
            pointer-events: none;
        }
        .lab-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: linear-gradient(135deg, rgba(124,58,237,0.3), rgba(79,70,229,0.2));
            border: 1px solid rgba(124,58,237,0.4);
            border-radius: 50px;
            padding: 4px 14px;
            font-size: 0.72rem;
            font-weight: 600;
            color: var(--lab-purple-light);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 0.75rem;
        }
        .lab-badge .dot {
            width: 6px; height: 6px;
            background: #22c55e;
            border-radius: 50%;
            animation: pulse-dot 2s infinite;
        }
        @keyframes pulse-dot {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(0.8); }
        }
        .lab-title {
            font-size: 2rem;
            font-weight: 800;
            color: #fff;
            margin: 0;
            line-height: 1.2;
        }
        .lab-title span {
            background: linear-gradient(135deg, var(--lab-purple-light), var(--lab-cyan));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .lab-subtitle {
            color: rgba(255,255,255,0.55);
            font-size: 0.9rem;
            margin-top: 0.4rem;
        }
        .lab-status-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .lab-status-pill.configured {
            background: rgba(34,197,94,0.15);
            border: 1px solid rgba(34,197,94,0.3);
            color: #4ade80;
        }
        .lab-status-pill.not-configured {
            background: rgba(234,179,8,0.15);
            border: 1px solid rgba(234,179,8,0.3);
            color: #facc15;
        }

        /* ── Tabs ── */
        .lab-tabs {
            display: flex;
            gap: 6px;
            margin-bottom: 1.5rem;
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.07);
            border-radius: 12px;
            padding: 5px;
        }
        .lab-tab-btn {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 20px;
            border: none;
            border-radius: 9px;
            background: transparent;
            color: rgba(255,255,255,0.45);
            font-weight: 600;
            font-size: 0.88rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .lab-tab-btn:hover {
            color: rgba(255,255,255,0.75);
            background: rgba(255,255,255,0.05);
        }
        .lab-tab-btn.active {
            background: linear-gradient(135deg, var(--lab-purple), var(--lab-indigo));
            color: #fff;
            box-shadow: 0 4px 15px rgba(124,58,237,0.35);
        }

        /* ── Cards ── */
        .lab-card {
            background: linear-gradient(145deg, #16162a, #1e1e35);
            border: 1px solid var(--lab-border);
            border-radius: 14px;
            padding: 1.5rem;
            margin-bottom: 1.25rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .lab-card:hover {
            border-color: rgba(124,58,237,0.45);
            box-shadow: 0 0 25px var(--lab-glow);
        }
        .lab-card-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 1.1rem;
        }
        .lab-card-icon {
            width: 40px; height: 40px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
        }
        .lab-card-icon.purple { background: rgba(124,58,237,0.2); }
        .lab-card-icon.cyan   { background: rgba(6,182,212,0.2); }
        .lab-card-icon.pink   { background: rgba(236,72,153,0.2); }
        .lab-card-icon.green  { background: rgba(34,197,94,0.2); }
        .lab-card-title {
            font-size: 1rem;
            font-weight: 700;
            color: #e5e7eb;
            margin: 0;
        }
        .lab-card-desc {
            font-size: 0.8rem;
            color: rgba(255,255,255,0.45);
            margin: 0;
        }

        /* ── Form Controls ── */
        .lab-label {
            font-size: 0.8rem;
            font-weight: 600;
            color: rgba(255,255,255,0.6);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.4rem;
            display: block;
        }
        .lab-input {
            width: 100%;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 10px;
            padding: 10px 14px;
            color: #e5e7eb;
            font-size: 0.9rem;
            transition: border-color 0.2s, box-shadow 0.2s;
            outline: none;
        }
        .lab-input:focus {
            border-color: var(--lab-purple-light);
            box-shadow: 0 0 0 3px rgba(124,58,237,0.2);
        }
        .lab-input::placeholder { color: rgba(255,255,255,0.25); }
        .lab-select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23a78bfa' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
            padding-right: 36px !important;
        }
        .lab-textarea {
            min-height: 100px;
            resize: vertical;
        }
        .lab-input-group {
            position: relative;
        }
        .lab-input-group .lab-input { padding-right: 44px; }
        .lab-input-toggle {
            position: absolute;
            right: 12px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none;
            color: rgba(255,255,255,0.4);
            cursor: pointer;
            font-size: 1rem;
            padding: 4px;
            transition: color 0.2s;
        }
        .lab-input-toggle:hover { color: var(--lab-purple-light); }

        /* ── Buttons ── */
        .lab-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 22px;
            border-radius: 10px;
            border: none;
            font-weight: 700;
            font-size: 0.88rem;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
        }
        .lab-btn-primary {
            background: linear-gradient(135deg, var(--lab-purple), var(--lab-indigo));
            color: #fff;
            box-shadow: 0 4px 15px rgba(124,58,237,0.3);
        }
        .lab-btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(124,58,237,0.45);
            color: #fff;
        }
        .lab-btn-outline {
            background: transparent;
            border: 1px solid var(--lab-border);
            color: var(--lab-purple-light);
        }
        .lab-btn-outline:hover {
            background: rgba(124,58,237,0.1);
            border-color: var(--lab-purple-light);
            color: var(--lab-purple-light);
        }
        .lab-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none !important;
        }

        /* ── Tags / Pills de selección ── */
        .lab-pill-group {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 0.4rem;
        }
        .lab-pill {
            padding: 6px 14px;
            border-radius: 50px;
            border: 1px solid rgba(255,255,255,0.1);
            background: rgba(255,255,255,0.05);
            color: rgba(255,255,255,0.55);
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            user-select: none;
        }
        .lab-pill:hover, .lab-pill.active {
            background: rgba(124,58,237,0.25);
            border-color: var(--lab-purple);
            color: var(--lab-purple-light);
        }

        /* ── Output ── */
        .lab-output {
            display: none;
            margin-top: 1rem;
            padding: 1.25rem;
            background: rgba(0,0,0,0.3);
            border: 1px solid rgba(124,58,237,0.3);
            border-radius: 12px;
            position: relative;
        }
        .lab-output.show { display: block; }
        .lab-output-text {
            color: #e5e7eb;
            font-size: 0.9rem;
            line-height: 1.65;
            white-space: pre-wrap;
            margin: 0;
        }
        .lab-copy-btn {
            position: absolute;
            top: 10px; right: 10px;
            background: rgba(124,58,237,0.2);
            border: 1px solid rgba(124,58,237,0.3);
            border-radius: 7px;
            color: var(--lab-purple-light);
            padding: 4px 10px;
            font-size: 0.75rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        .lab-copy-btn:hover {
            background: rgba(124,58,237,0.4);
        }

        /* ── Template cards ── */
        .template-card {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 10px;
            padding: 1rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        .template-card:hover {
            border-color: var(--lab-purple);
            background: rgba(124,58,237,0.08);
        }
        .template-card .template-icon { font-size: 1.4rem; margin-bottom: 6px; }
        .template-card .template-name { font-weight: 700; color: #e5e7eb; font-size: 0.88rem; }
        .template-card .template-fmt  { font-size: 0.75rem; color: rgba(255,255,255,0.4); }

        /* ── Métricas ── */
        .metric-chip {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 10px;
            padding: 1rem 1.25rem;
            text-align: center;
        }
        .metric-chip .val { font-size: 1.6rem; font-weight: 800; color: var(--lab-purple-light); }
        .metric-chip .lbl { font-size: 0.75rem; color: rgba(255,255,255,0.45); margin-top: 2px; }

        /* ── Spinner ── */
        .lab-spinner {
            display: none;
            width: 18px; height: 18px;
            border: 2px solid rgba(255,255,255,0.2);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* ── Coming soon ── */
        .coming-soon-banner {
            background: linear-gradient(135deg, rgba(79,70,229,0.15), rgba(124,58,237,0.1));
            border: 1px dashed rgba(124,58,237,0.4);
            border-radius: 14px;
            padding: 3rem;
            text-align: center;
            color: rgba(255,255,255,0.5);
        }
        .coming-soon-banner .cs-icon { font-size: 3rem; margin-bottom: 1rem; }
        .coming-soon-banner .cs-title { font-size: 1.1rem; font-weight: 700; color: rgba(255,255,255,0.7); }

        /* ── Config fields ── */
        .config-hint {
            font-size: 0.78rem;
            color: rgba(255,255,255,0.35);
            margin-top: 5px;
        }
        .config-hint a { color: var(--lab-purple-light); text-decoration: none; }
        .config-hint a:hover { text-decoration: underline; }

        /* Responsive */
        @media (max-width: 768px) {
            .lab-tabs { flex-direction: column; }
            .lab-title { font-size: 1.5rem; }
        }
    </style>
@endpush

@section('content')
<div class="content container-fluid">

    {{-- ── Header ── --}}
    <div class="lab-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="lab-badge">
                    <span class="dot"></span>
                    Tootli Inteligencia Artificial
                </div>
                <h1 class="lab-title">
                    Laboratorio <span>IA</span>
                </h1>
                <p class="lab-subtitle">
                    Potencia tu marketing con IA · Conectado a Modal.com
                </p>
            </div>
            <div class="col-md-4 text-md-right mt-3 mt-md-0">
                @if($apiKeyConfigurada)
                    <div class="lab-status-pill configured">
                        <span>⚡</span>
                        Modal.com Configurado
                    </div>
                @else
                    <div class="lab-status-pill not-configured">
                        <span>⚠️</span>
                        Modal.com sin configurar
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ── Tabs ── --}}
    <div class="lab-tabs">
        <button class="lab-tab-btn {{ request('tab', 'laboratorio') === 'laboratorio' ? 'active' : '' }}"
                onclick="switchTab('laboratorio')" id="tab-btn-laboratorio">
            🧪 Laboratorio de Marketing
        </button>
        <button class="lab-tab-btn {{ request('tab') === 'configuracion' ? 'active' : '' }}"
                onclick="switchTab('configuracion')" id="tab-btn-configuracion">
            ⚙️ Configuración
        </button>
        <button class="lab-tab-btn {{ request('tab') === 'avatar' ? 'active' : '' }}"
                onclick="switchTab('avatar')" id="tab-btn-avatar">
            🗣️ Avatar Hablante
        </button>
        <button class="lab-tab-btn {{ request('tab') === 'fastlane' ? 'active' : '' }}"
                onclick="switchTab('fastlane')" id="tab-btn-fastlane">
            🚀 Creador Fastlane
        </button>
    </div>

    {{-- ══════════════════════════════════════════════ --}}
    {{-- TAB: LABORATORIO                               --}}
    {{-- ══════════════════════════════════════════════ --}}
    <div id="panel-laboratorio" class="{{ request('tab') === 'configuracion' ? 'd-none' : '' }}">

        <div class="row">

            {{-- ── Generador de UGC ── --}}
            <div class="col-md-6">
                <div class="lab-card">
                    <div class="lab-card-header d-flex justify-content-between align-items-center">
                        <div class="d-flex">
                            <div class="lab-card-icon purple">✨</div>
                            <div>
                                <p class="lab-card-title">Generador de Contenido (UGC)</p>
                                <p class="lab-card-desc">Captions, posts y copies listos para publicar</p>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="obtenerTendencias()" id="btn-tendencias">
                            <i class="tio-trending-up"></i> 🔥 Tendencias
                        </button>
                    </div>

                    <form id="ugcForm">
                        @csrf
                        <div class="mb-3">
                            <label class="lab-label">Producto / Servicio / Restaurante</label>
                            <input type="text" name="producto" class="lab-input"
                                placeholder="Ej: Tacos El Güero, Pizza Hawaiana, Delivery Express..."
                                required>
                        </div>

                        <div class="mb-3">
                            <label class="lab-label">Plataforma</label>
                            <div class="lab-pill-group" id="plataformaGroup">
                                <span class="lab-pill active" data-val="instagram">📸 Instagram</span>
                                <span class="lab-pill" data-val="tiktok">🎵 TikTok</span>
                                <span class="lab-pill" data-val="facebook">👍 Facebook</span>
                                <span class="lab-pill" data-val="twitter">🐦 Twitter</span>
                                <span class="lab-pill" data-val="email">✉️ Email</span>
                            </div>
                            <input type="hidden" name="plataforma" id="plataformaInput" value="instagram">
                        </div>

                        <div class="mb-3">
                            <label class="lab-label">Tono de voz</label>
                            <div class="lab-pill-group" id="tonoGroup">
                                <span class="lab-pill active" data-val="casual">😊 Casual</span>
                                <span class="lab-pill" data-val="profesional">💼 Profesional</span>
                                <span class="lab-pill" data-val="divertido">🎉 Divertido</span>
                                <span class="lab-pill" data-val="emotivo">❤️ Emotivo</span>
                                <span class="lab-pill" data-val="urgente">⚡ Urgente</span>
                            </div>
                            <input type="hidden" name="tono" id="tonoInput" value="casual">
                        </div>

                        <div class="mb-3">
                            <label class="lab-label">Objetivo / Contexto <small style="opacity:.5">(opcional)</small></label>
                            <textarea name="objetivo" class="lab-input lab-textarea"
                                placeholder="Ej: Lanzamiento de nueva sucursal, promoción 2x1, temporada navideña..."></textarea>
                        </div>

                        <button type="submit" class="lab-btn lab-btn-primary" id="ugcSubmitBtn">
                            <span class="lab-spinner" id="ugcSpinner"></span>
                            <span id="ugcBtnText">✨ Generar Contenido</span>
                        </button>
                    </form>

                    <div class="lab-output" id="ugcOutput">
                        <button class="lab-copy-btn" onclick="copyOutput('ugcResult')">📋 Copiar</button>
                        <p class="lab-output-text" id="ugcResult"></p>
                    </div>
                </div>
            </div>

            {{-- ── Generador de Prompts de Marketing ── --}}
            <div class="col-md-6">
                <div class="lab-card">
                    <div class="lab-card-header">
                        <div class="lab-card-icon cyan">🎯</div>
                        <div>
                            <p class="lab-card-title">Prompts de Marketing</p>
                            <p class="lab-card-desc">Templates listos para usar con cualquier IA</p>
                        </div>
                    </div>

                    <form id="promptForm">
                        @csrf
                        <div class="mb-3">
                            <label class="lab-label">Tipo de Campaña</label>
                            <select name="tipo_campana" class="lab-input lab-select" required>
                                <option value="">Seleccionar...</option>
                                <option value="lanzamiento">🚀 Lanzamiento de producto</option>
                                <option value="oferta">⚡ Oferta / Descuento</option>
                                <option value="fidelizacion">❤️ Fidelización de clientes</option>
                                <option value="temporada">🎄 Campaña de temporada</option>
                                <option value="branding">✨ Branding / Imagen de marca</option>
                                <option value="reactivacion">📣 Reactivación de usuarios</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="lab-label">Describe tu producto o servicio</label>
                            <textarea name="descripcion" class="lab-input lab-textarea"
                                placeholder="Ej: App de delivery de comida para el mercado mexicano con repartidores independientes..."
                                required></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="lab-label">Audiencia objetivo <small style="opacity:.5">(opcional)</small></label>
                            <input type="text" name="audiencia" class="lab-input"
                                placeholder="Ej: Jóvenes de 18-35 años, madres de familia, emprendedores...">
                        </div>

                        <button type="submit" class="lab-btn lab-btn-primary" id="promptSubmitBtn">
                            <span class="lab-spinner" id="promptSpinner"></span>
                            <span id="promptBtnText">🎯 Generar Prompts</span>
                        </button>
                    </form>

                    <div class="lab-output" id="promptOutput">
                        <p style="font-size:.8rem;color:rgba(255,255,255,.4);margin-bottom:.75rem;">
                            Haz clic en un template para copiarlo al portapapeles
                        </p>
                        <div id="promptTemplates" class="row g-2"></div>
                    </div>
                </div>

                {{-- ── Generador de Video (Coming Soon) ── --}}
                <div class="lab-card">
                    <div class="lab-card-header">
                        <div class="lab-card-icon pink">🎬</div>
                        <div>
                            <p class="lab-card-title">Generador de Videos IA</p>
                            <p class="lab-card-desc">Powered by Modal.com · Próximamente</p>
                        </div>
                    </div>
                    <div class="coming-soon-banner">
                        <div class="cs-icon">🎬</div>
                        <div class="cs-title">Generación de Videos con IA</div>
                        <p style="font-size:.83rem;margin-top:.5rem;">
                            Configura tu API key de Modal.com para activar<br>
                            la generación de videos y contenido visual automatizado.
                        </p>
                        <button class="lab-btn lab-btn-outline mt-2" onclick="switchTab('configuracion')">
                            ⚙️ Configurar Modal.com
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- ══════════════════════════════════════════════ --}}
    {{-- TAB: CONFIGURACIÓN                             --}}
    {{-- ══════════════════════════════════════════════ --}}
    <div id="panel-configuracion" class="{{ request('tab') !== 'configuracion' ? 'd-none' : '' }}">

        <div class="row">
            <div class="col-md-8">

                {{-- ── Modal.com Setup ── --}}
                <div class="lab-card">
                    <div class="lab-card-header">
                        <div class="lab-card-icon purple">🔑</div>
                        <div>
                            <p class="lab-card-title">Credenciales de Modal.com</p>
                            <p class="lab-card-desc">Plataforma serverless para correr modelos de IA en la nube</p>
                        </div>
                    </div>

                    <form action="{{ route('admin.laboratorio-ia.configuracion.guardar') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="lab-label">API Key de Modal.com</label>
                            <div class="lab-input-group">
                                <input type="password"
                                    name="modal_api_key"
                                    id="modalApiKeyInput"
                                    class="lab-input"
                                    value="{{ old('modal_api_key', $modalApiKey) }}"
                                    placeholder="ak-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
                                    autocomplete="off">
                                <button type="button" class="lab-input-toggle" onclick="toggleApiKey()">
                                    <span id="apiKeyEye">👁</span>
                                </button>
                            </div>
                            <p class="config-hint">
                                Obtenla en <a href="https://modal.com/settings/tokens" target="_blank">modal.com/settings/tokens</a>
                            </p>
                        </div>

                        <div class="mb-4">
                            <label class="lab-label">Workspace / Organización</label>
                            <input type="text"
                                name="modal_workspace"
                                class="lab-input"
                                value="{{ old('modal_workspace', $modalWorkspace) }}"
                                placeholder="tootli-ai">
                            <p class="config-hint">
                                El nombre del workspace en tu cuenta de Modal.com
                            </p>
                        </div>

                        <button type="submit" class="lab-btn lab-btn-primary">
                            💾 Guardar Configuración
                        </button>
                    </form>
                </div>

                {{-- ── Cómo conectar Modal.com ── --}}
                <div class="lab-card">
                    <div class="lab-card-header">
                        <div class="lab-card-icon cyan">📖</div>
                        <div>
                            <p class="lab-card-title">¿Cómo conectar Modal.com?</p>
                            <p class="lab-card-desc">Pasos rápidos para activar la integración</p>
                        </div>
                    </div>
                    <ol style="color:rgba(255,255,255,.65);font-size:.88rem;line-height:2;padding-left:1.2rem;">
                        <li>Crea una cuenta en <a href="https://modal.com" target="_blank" style="color:var(--lab-purple-light)">modal.com</a></li>
                        <li>Ve a <strong style="color:#e5e7eb">Settings → Tokens</strong> y genera un nuevo token</li>
                        <li>Copia el token y pégalo en el campo <strong style="color:#e5e7eb">API Key</strong> de arriba</li>
                        <li>Escribe el nombre de tu <strong style="color:#e5e7eb">Workspace</strong> (aparece en la URL de Modal)</li>
                        <li>Haz clic en <strong style="color:#e5e7eb">Guardar Configuración</strong></li>
                        <li>¡Listo! El Generador de Videos se activará automáticamente</li>
                    </ol>
                </div>

            </div>

            <div class="col-md-4">

                {{-- ── Estado del servicio ── --}}
                <div class="lab-card">
                    <div class="lab-card-header">
                        <div class="lab-card-icon green">📊</div>
                        <div>
                            <p class="lab-card-title">Estado del Servicio</p>
                            <p class="lab-card-desc">Servicios de IA activos</p>
                        </div>
                    </div>

                    <div style="display:flex;flex-direction:column;gap:10px;">
                        <div style="display:flex;align-items:center;justify-content:space-between;padding:10px;background:rgba(255,255,255,.03);border-radius:8px;">
                            <span style="font-size:.85rem;color:rgba(255,255,255,.65)">🤖 Gemini AI</span>
                            <span style="font-size:.75rem;font-weight:700;color:#4ade80;background:rgba(34,197,94,.15);padding:3px 10px;border-radius:50px;">Activo</span>
                        </div>
                        <div style="display:flex;align-items:center;justify-content:space-between;padding:10px;background:rgba(255,255,255,.03);border-radius:8px;">
                            <span style="font-size:.85rem;color:rgba(255,255,255,.65)">🔍 Embeddings</span>
                            <span style="font-size:.75rem;font-weight:700;color:#4ade80;background:rgba(34,197,94,.15);padding:3px 10px;border-radius:50px;">Activo</span>
                        </div>
                        <div style="display:flex;align-items:center;justify-content:space-between;padding:10px;background:rgba(255,255,255,.03);border-radius:8px;">
                            <span style="font-size:.85rem;color:rgba(255,255,255,.65)">⚡ Modal.com</span>
                            @if($apiKeyConfigurada)
                                <span style="font-size:.75rem;font-weight:700;color:#4ade80;background:rgba(34,197,94,.15);padding:3px 10px;border-radius:50px;">Configurado</span>
                            @else
                                <span style="font-size:.75rem;font-weight:700;color:#facc15;background:rgba(234,179,8,.15);padding:3px 10px;border-radius:50px;">Pendiente</span>
                            @endif
                        </div>
                        <div style="display:flex;align-items:center;justify-content:space-between;padding:10px;background:rgba(255,255,255,.03);border-radius:8px;">
                            <span style="font-size:.85rem;color:rgba(255,255,255,.65)">🖼 Remove BG</span>
                            <span style="font-size:.75rem;font-weight:700;color:#4ade80;background:rgba(34,197,94,.15);padding:3px 10px;border-radius:50px;">Activo</span>
                        </div>
                    </div>
                </div>

                {{-- ── Métricas de uso ── --}}
                <div class="lab-card">
                    <div class="lab-card-header">
                        <div class="lab-card-icon pink">📈</div>
                        <div>
                            <p class="lab-card-title">Uso del Laboratorio</p>
                            <p class="lab-card-desc">Este mes</p>
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="metric-chip">
                                <div class="val" id="metricUGC">—</div>
                                <div class="lbl">Contenidos generados</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="metric-chip">
                                <div class="val" id="metricPrompts">—</div>
                                <div class="lbl">Prompts creados</div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="metric-chip">
                                <div class="val" style="font-size:1.1rem;color:rgba(255,255,255,.4)">Próximamente</div>
                                <div class="lbl">Métricas avanzadas de Modal.com</div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    </div>

    {{-- ══════════════════════════════════════════════ --}}
    {{-- TAB: AVATAR HABLANTE                           --}}
    {{-- ══════════════════════════════════════════════ --}}
    <div id="panel-avatar" class="{{ request('tab') !== 'avatar' ? 'd-none' : '' }}">
        <div class="row">
            <div class="col-md-6 offset-md-3">
                <div class="lab-card">
                    <div class="lab-card-header">
                        <div class="lab-card-icon purple">🗣️</div>
                        <div>
                            <p class="lab-card-title">Avatar Hablante</p>
                            <p class="lab-card-desc">Sube una foto, escribe un guion, y genera un video narrado.</p>
                        </div>
                    </div>
                    <form id="avatarForm" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="lab-label">Foto del Avatar (PNG/JPG, max 5MB)</label>
                            <input type="file" name="imagen_avatar" class="lab-input" accept="image/png, image/jpeg" required>
                        </div>
                        <div class="mb-3">
                            <label class="lab-label">Guion (Lo que va a decir el avatar)</label>
                            <textarea name="guion" class="lab-input" rows="4" placeholder="Ej: Hola, bienvenidos a Tootli..." required></textarea>
                        </div>
                        <button type="submit" class="btn-generar" id="avatarSubmitBtn">
                            <i class="tio-video-camera"></i> <span id="avatarBtnText">🎬 Generar Video</span>
                            <div class="lab-spinner" id="avatarSpinner"></div>
                        </button>
                    </form>
                    
                    <div class="lab-result-card mt-3" id="avatarOutput">
                        <p class="lab-label text-success mb-2"><i class="tio-checkmark-circle"></i> ¡Video Generado!</p>
                        <video id="avatarPlayer" controls width="100%" style="border-radius: 12px; display: none;"></video>
                        <div id="avatarResult" class="text-danger mt-2" style="font-size: 14px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════ --}}
    {{-- TAB: CREADOR FASTLANE                          --}}
    {{-- ══════════════════════════════════════════════ --}}
    <div id="panel-fastlane" class="{{ request('tab') !== 'fastlane' ? 'd-none' : '' }}">
        <div class="row">
            <div class="col-md-6 offset-md-3">
                <div class="lab-card">
                    <div class="lab-card-header">
                        <div class="lab-card-icon" style="background: rgba(255, 107, 107, 0.2); color: #ff6b6b;">🚀</div>
                        <div>
                            <p class="lab-card-title">Fastlane Creator (BETA)</p>
                            <p class="lab-card-desc">Convierte cualquier URL en un video narrado por tu Avatar.</p>
                        </div>
                    </div>
                    
                    <!-- Paso 1: Ingresar URL -->
                    <form id="fastlaneUrlForm">
                        @csrf
                        <div class="mb-3">
                            <label class="lab-label">Pega el link (URL) de tu producto/negocio</label>
                            <input type="url" name="url" class="lab-input" placeholder="Ej: https://tootli.mx/restaurantes/burger-king" required>
                        </div>
                        <button type="submit" class="btn-generar" id="flUrlBtn">
                            <i class="tio-search"></i> <span id="flUrlBtnText">Analizar URL y Generar Hooks</span>
                            <div class="lab-spinner" id="flUrlSpinner"></div>
                        </button>
                    </form>

                    <!-- Paso 2: Elegir Hook y Generar Video -->
                    <div id="flHooksSection" class="mt-4 d-none">
                        <h5 class="text-white mb-3"><i class="tio-format-points"></i> Hooks Generados</h5>
                        <form id="fastlaneAvatarForm" enctype="multipart/form-data">
                            @csrf
                            <div id="flHooksContainer" class="mb-3">
                                <!-- Tarjetas de radio buttons inyectadas por JS -->
                            </div>
                            
                            <hr style="border-color: rgba(255,255,255,0.1);">
                            <h5 class="text-white mb-3 mt-3"><i class="tio-face"></i> Configuración del Avatar</h5>
                            
                            <div class="mb-3">
                                <label class="lab-label">Foto del Avatar (PNG/JPG)</label>
                                <input type="file" name="imagen_avatar" class="lab-input" accept="image/png, image/jpeg" required>
                            </div>
                            
                            <button type="submit" class="btn-generar" id="flAvatarBtn" style="background: linear-gradient(135deg, #ff6b6b 0%, #ff8e53 100%);">
                                <i class="tio-video-camera"></i> <span id="flAvatarBtnText">Generar Video Final</span>
                                <div class="lab-spinner" id="flAvatarSpinner"></div>
                            </button>
                        </form>
                    </div>

                    <!-- Resultado Video -->
                    <div class="lab-result-card mt-3" id="flOutput">
                        <p class="lab-label text-success mb-2"><i class="tio-checkmark-circle"></i> ¡Video Fastlane Generado!</p>
                        <video id="flPlayer" controls width="100%" style="border-radius: 12px; display: none;"></video>
                        <div id="flResult" class="text-danger mt-2" style="font-size: 14px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('script_2')
<script>
/* ── Tab switcher ── */
function switchTab(name) {
    document.getElementById('panel-laboratorio').classList.toggle('d-none', name !== 'laboratorio');
    document.getElementById('panel-configuracion').classList.toggle('d-none', name !== 'configuracion');
    
    const panelAvatar = document.getElementById('panel-avatar');
    if(panelAvatar) panelAvatar.classList.toggle('d-none', name !== 'avatar');
    
    const panelFastlane = document.getElementById('panel-fastlane');
    if(panelFastlane) panelFastlane.classList.toggle('d-none', name !== 'fastlane');
    
    document.getElementById('tab-btn-laboratorio').classList.toggle('active', name === 'laboratorio');
    document.getElementById('tab-btn-configuracion').classList.toggle('active', name === 'configuracion');
    
    const tabAvatar = document.getElementById('tab-btn-avatar');
    if(tabAvatar) tabAvatar.classList.toggle('active', name === 'avatar');
    
    const tabFastlane = document.getElementById('tab-btn-fastlane');
    if(tabFastlane) tabFastlane.classList.toggle('active', name === 'fastlane');
    
    history.replaceState(null, '', '?tab=' + name);
}

/* ── Pill selectors ── */
function initPillGroup(groupId, hiddenId) {
    const pills = document.querySelectorAll('#' + groupId + ' .lab-pill');
    pills.forEach(pill => {
        pill.addEventListener('click', () => {
            pills.forEach(p => p.classList.remove('active'));
            pill.classList.add('active');
            document.getElementById(hiddenId).value = pill.dataset.val;
        });
    });
}
initPillGroup('plataformaGroup', 'plataformaInput');
initPillGroup('tonoGroup', 'tonoInput');

/* ── Toggle API Key visibility ── */
function toggleApiKey() {
    const inp = document.getElementById('modalApiKeyInput');
    const eye = document.getElementById('apiKeyEye');
    if (inp.type === 'password') { inp.type = 'text'; eye.textContent = '🙈'; }
    else { inp.type = 'password'; eye.textContent = '👁'; }
}

/* ── Copy to clipboard ── */
function copyOutput(id) {
    const text = document.getElementById(id).textContent;
    navigator.clipboard.writeText(text).then(() => {
        toastr.success('¡Copiado al portapapeles!', '', {timeOut: 1500});
    });
}

/* ── Contador local de uso ── */
let usageUGC = parseInt(localStorage.getItem('lab_ugc') || '0');
let usagePrompts = parseInt(localStorage.getItem('lab_prompts') || '0');
document.getElementById('metricUGC').textContent = usageUGC;
document.getElementById('metricPrompts').textContent = usagePrompts;

/* ── UGC Form ── */
let ugcGeneraciones = 0;
document.getElementById('ugcForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn   = document.getElementById('ugcSubmitBtn');
    const spin  = document.getElementById('ugcSpinner');
    const txt   = document.getElementById('ugcBtnText');
    const out   = document.getElementById('ugcOutput');
    const res   = document.getElementById('ugcResult');

    btn.disabled = true;
    spin.style.display = 'block';
    txt.textContent = 'Generando...';
    out.classList.remove('show');

    const data = new FormData(this);
    try {
        const resp = await fetch('{{ route("admin.laboratorio-ia.generar-ugc") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('[name=_token]').value,
                'Accept': 'application/json',
            },
            body: data
        });
        const json = await resp.json();
        if (json.success) {
            res.textContent = json.content;
            out.classList.add('show');
            usageUGC++;
            localStorage.setItem('lab_ugc', usageUGC);
            document.getElementById('metricUGC').textContent = usageUGC;
        }
    } catch(err) {
        toastr.error('Error al conectar con el servicio de IA');
    } finally {
        btn.disabled = false;
        spin.style.display = 'none';
        txt.textContent = '✨ Generar Contenido';
    }
});

/* ── Prompts Form ── */
document.getElementById('promptForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn   = document.getElementById('promptSubmitBtn');
    const spin  = document.getElementById('promptSpinner');
    const txt   = document.getElementById('promptBtnText');
    const out   = document.getElementById('promptOutput');
    const cont  = document.getElementById('promptTemplates');

    btn.disabled = true;
    spin.style.display = 'block';
    txt.textContent = 'Generando...';
    out.classList.remove('show');

    const data = new FormData(this);
    try {
        const resp = await fetch('{{ route("admin.laboratorio-ia.generar-prompt-marketing") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('[name=_token]').value,
                'Accept': 'application/json',
            },
            body: data
        });
        const json = await resp.json();
        if (json.success) {
            cont.innerHTML = json.templates.map(t => `
                <div class="col-6">
                    <div class="template-card" onclick="copyPrompt(this, \`${t.prompt}\`)">
                        <div class="template-icon">${t.icono}</div>
                        <div class="template-name">${t.nombre}</div>
                        <div class="template-fmt">${t.formato}</div>
                    </div>
                </div>
            `).join('');
            out.classList.add('show');
            usagePrompts++;
            localStorage.setItem('lab_prompts', usagePrompts);
            document.getElementById('metricPrompts').textContent = usagePrompts;
        }
    } catch(err) {
        toastr.error('Error al generar prompts');
    } finally {
        btn.disabled = false;
        spin.style.display = 'none';
        txt.textContent = '🎯 Generar Prompts';
    }
});

function copyPrompt(card, text) {
    navigator.clipboard.writeText(text).then(() => {
        card.style.borderColor = '#4ade80';
        setTimeout(() => card.style.borderColor = '', 800);
        toastr.success('Prompt copiado — pégalo en ChatGPT, Gemini o Claude', '', {timeOut: 2000});
    });
}

/* ── Avatar Form ── */
const avatarFormEl = document.getElementById('avatarForm');
if (avatarFormEl) {
    avatarFormEl.addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn  = document.getElementById('avatarSubmitBtn');
        const spin = document.getElementById('avatarSpinner');
        const txt  = document.getElementById('avatarBtnText');
        const out  = document.getElementById('avatarOutput');
        const res  = document.getElementById('avatarResult');
        const player = document.getElementById('avatarPlayer');

        btn.disabled = true;
        spin.style.display = 'block';
        txt.textContent = 'Generando video... Esto puede tardar 1 minuto.';
        out.classList.remove('show');
        player.style.display = 'none';
        res.textContent = '';

        const data = new FormData(this);
        try {
            const resp = await fetch('{{ route("admin.laboratorio-ia.generar-avatar") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('[name=_token]').value,
                    'Accept': 'application/json',
                },
                body: data
            });
            const json = await resp.json();
            if (json.success) {
                out.classList.add('show');
                player.src = json.video_url;
                player.style.display = 'block';
                toastr.success('¡Video avatar generado con éxito!');
            } else {
                res.textContent = json.error || 'Error desconocido';
                out.classList.add('show');
            }
        } catch(err) {
            toastr.error('Error de conexión con el servidor');
        } finally {
            btn.disabled = false;
            spin.style.display = 'none';
            txt.textContent = '🎬 Generar Video';
        }
    });
}

/* ── Fastlane Creator ── */
const flUrlForm = document.getElementById('fastlaneUrlForm');
if (flUrlForm) {
    flUrlForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = document.getElementById('flUrlBtn');
        const spin = document.getElementById('flUrlSpinner');
        const txt = document.getElementById('flUrlBtnText');
        const section = document.getElementById('flHooksSection');
        const container = document.getElementById('flHooksContainer');

        btn.disabled = true;
        spin.style.display = 'block';
        txt.textContent = 'Analizando y extrayendo guiones...';
        section.classList.add('d-none');

        const data = new FormData(this);
        try {
            const resp = await fetch('{{ route("admin.laboratorio-ia.analizar-url") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('[name=_token]').value,
                    'Accept': 'application/json',
                },
                body: data
            });
            const json = await resp.json();
            if (json.success) {
                // Llenar container con los hooks
                container.innerHTML = json.hooks.map((hook, idx) => `
                    <div class="lab-card mb-2" style="cursor:pointer; border: 1px solid rgba(255,255,255,0.1);" onclick="document.getElementById('hook_${idx}').checked = true">
                        <div class="d-flex align-items-center">
                            <input type="radio" name="guion_elegido" id="hook_${idx}" value="${hook.texto}" class="mr-3" required ${idx === 0 ? 'checked' : ''}>
                            <div>
                                <h6 class="text-white mb-1">${hook.titulo}</h6>
                                <p class="text-muted mb-0" style="font-size:13px;">${hook.texto}</p>
                            </div>
                        </div>
                    </div>
                `).join('');
                section.classList.remove('d-none');
                toastr.success('URL analizada con éxito.');
            } else {
                toastr.error(json.error || 'No se pudo analizar la URL');
            }
        } catch(err) {
            toastr.error('Error de conexión');
        } finally {
            btn.disabled = false;
            spin.style.display = 'none';
            txt.textContent = 'Analizar URL y Generar Hooks';
        }
    });
}

const flAvatarForm = document.getElementById('fastlaneAvatarForm');
if (flAvatarForm) {
    flAvatarForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = document.getElementById('flAvatarBtn');
        const spin = document.getElementById('flAvatarSpinner');
        const txt = document.getElementById('flAvatarBtnText');
        const out = document.getElementById('flOutput');
        const res = document.getElementById('flResult');
        const player = document.getElementById('flPlayer');

        btn.disabled = true;
        spin.style.display = 'block';
        txt.textContent = 'Ensamblando video en JSON2Video... (1 min aprox)';
        out.classList.remove('show');
        player.style.display = 'none';
        res.textContent = '';

        // Copiamos el guion y el file
        const data = new FormData(this);
        data.append('guion', document.querySelector('input[name="guion_elegido"]:checked').value);

        try {
            const resp = await fetch('{{ route("admin.laboratorio-ia.generar-fastlane") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('[name=_token]').value,
                    'Accept': 'application/json',
                },
                body: data
            });
            const json = await resp.json();
            
            if (json.success && json.job_id) {
                // Iniciar Polling (cada 5 segundos)
                const jobId = json.job_id;
                let attempts = 0;
                
                const pollInterval = setInterval(async () => {
                    attempts++;
                    try {
                        const checkResp = await fetch(`/admin/laboratorio-ia/estado-fastlane/${jobId}`);
                        const checkJson = await checkResp.json();
                        
                        if (checkJson.success && checkJson.data) {
                            if (checkJson.data.status === 'done') {
                                clearInterval(pollInterval);
                                out.classList.add('show');
                                player.src = checkJson.data.video_url;
                                player.style.display = 'block';
                                toastr.success('¡Plantilla TikTok generada con éxito!');
                                btn.disabled = false;
                                spin.style.display = 'none';
                                txt.textContent = 'Generar Video Final';
                            } else if (checkJson.data.status === 'error') {
                                clearInterval(pollInterval);
                                res.textContent = checkJson.data.error || 'Error desconocido';
                                out.classList.add('show');
                                btn.disabled = false;
                                spin.style.display = 'none';
                                txt.textContent = 'Generar Video Final';
                            } else {
                                // Sigue processing, actualizamos UI para que el usuario no desespere
                                txt.textContent = `Procesando... (${attempts * 5}s transcurridos)`;
                            }
                        }
                        
                        if (attempts > 60) { // Timeout de 5 mins en front
                            clearInterval(pollInterval);
                            toastr.error('Tiempo de espera agotado. Por favor revisa más tarde.');
                            btn.disabled = false;
                            spin.style.display = 'none';
                            txt.textContent = 'Generar Video Final';
                        }
                    } catch(e) {
                        console.error('Error polling', e);
                    }
                }, 5000);
            } else {
                res.textContent = json.error || 'Error de conexión';
                out.classList.add('show');
                btn.disabled = false;
                spin.style.display = 'none';
                txt.textContent = 'Generar Video Final';
            }
        } catch(err) {
            toastr.error('Error de conexión con el servidor');
            btn.disabled = false;
            spin.style.display = 'none';
            txt.textContent = 'Generar Video Final';
        }
    });
}

// ─────────────── TENDENCIAS TIKTOK ───────────────
let tendenciasCargadas = false;

async function obtenerTendencias() {
    $('#tendenciasModal').modal('show');
    
    if (tendenciasCargadas) return;

    const loading = document.getElementById('tendencias-loading');
    const errorDiv = document.getElementById('tendencias-error');
    const listDiv = document.getElementById('tendencias-list');

    loading.classList.remove('d-none');
    errorDiv.classList.add('d-none');
    listDiv.classList.add('d-none');

    try {
        const resp = await fetch('{{ route('admin.laboratorio-ia.tendencias') }}');
        const json = await resp.json();

        if (json.success) {
            tendenciasCargadas = true;
            listDiv.innerHTML = '';
            
            if (json.tendencias && json.tendencias.length > 0) {
                json.tendencias.forEach((trend, index) => {
                    const viewsStr = trend['Video Views'] ? (trend['Video Views'] / 1000000).toFixed(1) + 'M' : 'N/A';
                    const html = `
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-3" style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                            <div>
                                <h4 class="mb-1 text-white" style="font-size:16px;">${index + 1}. ${trend.Hashtag}</h4>
                                <small class="text-muted"><i class="tio-play-circle-outlined"></i> ${viewsStr} vistas en México</small>
                            </div>
                            <button class="btn btn-sm btn-primary rounded-pill px-3" onclick="usarTendencia('${trend.Hashtag}')">Usar</button>
                        </div>
                    `;
                    listDiv.innerHTML += html;
                });
            } else {
                listDiv.innerHTML = '<p class="text-muted text-center">No se encontraron tendencias recientes.</p>';
            }
            
            loading.classList.add('d-none');
            listDiv.classList.remove('d-none');
        } else {
            throw new Error(json.error || 'Error desconocido');
        }
    } catch (err) {
        loading.classList.add('d-none');
        errorDiv.classList.remove('d-none');
        errorDiv.textContent = '❌ Error al cargar tendencias: ' + err.message;
    }
}

function usarTendencia(hashtag) {
    const inputProducto = document.querySelector('input[name="producto"]');
    if (inputProducto) {
        inputProducto.value = (inputProducto.value ? inputProducto.value + ' + ' : '') + 'Trend: ' + hashtag;
        inputProducto.style.transition = 'all 0.3s';
        inputProducto.style.boxShadow = '0 0 10px #7367f0';
        setTimeout(() => {
            inputProducto.style.boxShadow = 'none';
        }, 1000);
    }
    $('#tendenciasModal').modal('hide');
}
</script>
@endpush
