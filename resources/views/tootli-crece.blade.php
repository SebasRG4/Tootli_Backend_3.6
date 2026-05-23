@extends('layouts.landing.app')

@section('title', 'Crece con Tootli — Opera en tu ciudad')

@push('css_or_js')
<style>
/* ============================================================
   TOOTLI CRECE — ESTILOS DE PÁGINA (LIGHT-MINIMALIST THEME)
   ============================================================ */

/* ── Hero ── */
.crece-hero {
    min-height: 90vh;
    display: flex;
    align-items: center;
    background: 
        radial-gradient(circle at 80% 20%, rgba(0, 209, 113, 0.08) 0%, transparent 50%),
        radial-gradient(circle at 15% 85%, rgba(0, 209, 113, 0.05) 0%, transparent 40%),
        #FFFFFF;
    padding-top: 120px;
    padding-bottom: 80px;
    position: relative;
    overflow: hidden;
    color: #0F172A;
}

.crece-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image: radial-gradient(circle, rgba(0,209,113,0.12) 1px, transparent 1px);
    background-size: 40px 40px;
    opacity: 0.5;
    pointer-events: none;
}

.crece-hero-inner {
    position: relative;
    z-index: 2;
    max-width: 820px;
    margin: 0 auto;
    text-align: center;
}

.crece-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #E6F9F1;
    border: 1px solid rgba(0,209,113,0.3);
    color: #00A358;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 1px;
    text-transform: uppercase;
    padding: 8px 22px;
    border-radius: 50px;
    margin-bottom: 28px;
}

.crece-hero-title {
    font-size: 72px;
    font-weight: 900;
    line-height: 1.05;
    letter-spacing: -2.5px;
    margin-bottom: 24px;
    color: #0F172A;
}

.crece-hero-title .accent {
    background: linear-gradient(135deg, #00D171 0%, #00A358 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.crece-hero-sub {
    font-size: 21px;
    color: #475569;
    line-height: 1.65;
    margin-bottom: 44px;
    font-weight: 400;
    max-width: 720px;
    margin-left: auto;
    margin-right: auto;
}

.crece-hero-actions {
    display: flex;
    gap: 16px;
    justify-content: center;
    flex-wrap: wrap;
}

.btn-crece-primary {
    padding: 18px 42px;
    border-radius: 50px;
    font-weight: 800;
    font-size: 16px;
    background: linear-gradient(135deg, #00D171 0%, #00B360 100%);
    color: #FFFFFF !important;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    transition: all 0.35s cubic-bezier(0.16,1,0.3,1);
    box-shadow: 0 10px 25px rgba(0,209,113,0.25);
}

.btn-crece-primary:hover {
    background: linear-gradient(135deg, #00B360 0%, #009E52 100%);
    color: #FFFFFF !important;
    transform: translateY(-3px);
    box-shadow: 0 15px 35px rgba(0,209,113,0.35);
}

.btn-crece-outline {
    padding: 17px 38px;
    border-radius: 50px;
    font-weight: 700;
    font-size: 16px;
    background: transparent;
    border: 2px solid #E2E8F0;
    color: #475569 !important;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    transition: all 0.3s ease;
}

.btn-crece-outline:hover {
    border-color: #00D171;
    color: #00D171 !important;
    transform: translateY(-2px);
    background: rgba(0,209,113,0.02);
}

/* ── Stats bar ── */
.crece-stats-bar {
    background: #FFFFFF;
    border-top: 1px solid #F1F5F9;
    border-bottom: 1px solid #F1F5F9;
    padding: 32px 0;
}

.crece-stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0;
    max-width: 1000px;
    margin: 0 auto;
    text-align: center;
}

.crece-stat-item {
    padding: 0 24px;
    border-right: 1px solid #E2E8F0;
}

.crece-stat-item:last-child { border-right: none; }

.crece-stat-num {
    font-size: 40px;
    font-weight: 900;
    letter-spacing: -1.5px;
    color: #00D171;
    line-height: 1;
    margin-bottom: 6px;
}

.crece-stat-label {
    font-size: 13px;
    color: #64748B;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.8px;
}

/* ── Section base ── */
.crece-section {
    padding: 100px 0;
    background: #FFFFFF;
}

.crece-section-alt {
    background: #F8FAFC;
}

.crece-section-dark {
    background: #F8FAF7; /* Cambiado a blanco/menta minimalista */
    color: #0F172A;
}

.crece-section-header {
    text-align: center;
    margin-bottom: 64px;
}

.crece-section-tag {
    display: inline-block;
    background: #E6F9F1;
    color: #00A358;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    padding: 6px 18px;
    border-radius: 50px;
    margin-bottom: 16px;
}

.crece-section-dark .crece-section-tag {
    background: #E6F9F1;
    color: #00A358;
}

.crece-section-title {
    font-size: 48px;
    font-weight: 900;
    letter-spacing: -1.5px;
    line-height: 1.15;
    margin-bottom: 16px;
    color: #0F172A;
}

.crece-section-dark .crece-section-title { color: #0F172A; }

.crece-section-sub {
    font-size: 18px;
    color: #475569;
    max-width: 620px;
    margin: 0 auto;
    line-height: 1.7;
}

.crece-section-dark .crece-section-sub { color: #475569; }

/* ── Beneficios cards ── */
.crece-benefits-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 28px;
}

.crece-benefit-card {
    background: #FFFFFF;
    border-radius: 24px;
    padding: 40px 36px;
    border: 1px solid #E2E8F0;
    transition: all 0.35s cubic-bezier(0.16,1,0.3,1);
    position: relative;
    overflow: hidden;
}

.crece-benefit-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 4px;
    background: linear-gradient(90deg, #00D171, #00ffaa);
    opacity: 0;
    transition: opacity 0.3s;
}

.crece-benefit-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 45px rgba(0,0,0,0.04);
    border-color: rgba(0,209,113,0.3);
}

.crece-benefit-card:hover::before { opacity: 1; }

.crece-benefit-icon {
    width: 64px;
    height: 64px;
    border-radius: 20px;
    background: #E6F9F1;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    margin-bottom: 24px;
    transition: transform 0.3s;
    color: #00A358;
}

.crece-benefit-card:hover .crece-benefit-icon {
    transform: scale(1.1) rotate(-5deg);
}

.crece-benefit-card h3 {
    font-size: 20px;
    font-weight: 800;
    margin-bottom: 12px;
    color: #0F172A;
}

.crece-benefit-card p {
    font-size: 15px;
    color: #475569;
    line-height: 1.65;
    margin: 0;
}

/* ── Ecosystem tabs ── */
.crece-ecosystem {
    display: grid;
    grid-template-columns: 260px 1fr;
    gap: 0;
    background: #FFFFFF;
    border: 1px solid #E2E8F0;
    border-radius: 32px;
    overflow: hidden;
    min-height: 480px;
    box-shadow: 0 20px 50px rgba(0,0,0,0.03);
}

.crece-eco-tabs {
    padding: 32px 24px;
    border-right: 1px solid #F1F5F9;
    display: flex;
    flex-direction: column;
    gap: 8px;
    background: #FCFDFD;
}

.crece-eco-tab {
    background: transparent;
    border: none;
    color: #64748B;
    padding: 16px 20px;
    border-radius: 16px;
    text-align: left;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    font-family: inherit;
    transition: all 0.25s ease;
    display: flex;
    align-items: center;
    gap: 10px;
}

.crece-eco-tab:hover {
    color: #0F172A;
    background: #F1F5F9;
}

.crece-eco-tab.active {
    background: #E6F9F1;
    color: #00A358;
    font-weight: 700;
}

.crece-eco-panels {
    padding: 48px 48px;
    background: #FFFFFF;
}

.crece-eco-panel {
    display: none;
    animation: fadeSlideIn 0.35s ease forwards;
}

.crece-eco-panel.active { display: block; }

@keyframes fadeSlideIn {
    from { opacity: 0; transform: translateY(12px); }
    to   { opacity: 1; transform: translateY(0); }
}

.crece-eco-panel-emoji {
    font-size: 52px;
    margin-bottom: 20px;
    display: block;
}

.crece-eco-panel h3 {
    font-size: 32px;
    font-weight: 900;
    color: #00A358;
    margin-bottom: 14px;
    letter-spacing: -1px;
}

.crece-eco-panel p {
    font-size: 17px;
    color: #475569;
    line-height: 1.7;
    margin-bottom: 28px;
}

.crece-eco-checklist {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.crece-eco-checklist li {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 15px;
    color: #1E293B;
    font-weight: 500;
}

.crece-eco-checklist li::before {
    content: '✓';
    width: 22px;
    height: 22px;
    background: #E6F9F1;
    color: #00A358;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 700;
    flex-shrink: 0;
}

/* ── Proceso steps ── */
.crece-steps {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0;
    position: relative;
}

.crece-steps::before {
    content: '';
    position: absolute;
    top: 40px;
    left: 12%;
    right: 12%;
    height: 2px;
    background: linear-gradient(90deg, #00D171, #00B360);
    opacity: 0.3;
}

.crece-step {
    text-align: center;
    padding: 0 20px;
    position: relative;
}

.crece-step-number {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: #FFFFFF;
    border: 3px solid #00D171;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    font-weight: 900;
    color: #00D171;
    margin: 0 auto 24px;
    position: relative;
    z-index: 2;
    box-shadow: 0 8px 24px rgba(0,209,113,0.15);
    transition: all 0.3s ease;
}

.crece-step:hover .crece-step-number {
    background: #00D171;
    color: #FFFFFF;
    transform: scale(1.1);
    box-shadow: 0 12px 32px rgba(0,209,113,0.3);
}

.crece-step h4 {
    font-size: 18px;
    font-weight: 800;
    color: #0F172A;
    margin-bottom: 8px;
}

.crece-step p {
    font-size: 14px;
    color: #64748B;
    line-height: 1.6;
}

/* ── Contact Form ── */
.crece-contact-wrap {
    display: grid;
    grid-template-columns: 1fr 1.3fr;
    gap: 60px;
    align-items: start;
}

.crece-contact-info h2 {
    font-size: 42px;
    font-weight: 900;
    letter-spacing: -1.5px;
    margin-bottom: 16px;
    color: #0F172A;
    line-height: 1.15;
}

.crece-contact-info p {
    font-size: 17px;
    color: #475569;
    line-height: 1.7;
    margin-bottom: 36px;
}

.crece-contact-perks {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.crece-contact-perks li {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 15px;
    color: #334155;
    font-weight: 500;
}

.crece-perk-icon {
    width: 36px;
    height: 36px;
    background: #E6F9F1;
    border: 1px solid rgba(0,209,113,0.2);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
}

/* Form card */
.crece-form-card {
    background: #FFFFFF;
    border: 1px solid #E2E8F0;
    border-radius: 28px;
    padding: 48px 44px;
    box-shadow: 0 25px 60px rgba(0, 0, 0, 0.04);
}

.crece-form-group {
    margin-bottom: 20px;
}

.crece-form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.crece-form-label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #475569;
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.crece-form-input,
.crece-form-select,
.crece-form-textarea {
    width: 100%;
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    border-radius: 14px;
    padding: 14px 18px;
    font-size: 15px;
    color: #0F172A;
    font-family: 'Outfit', sans-serif;
    transition: all 0.3s ease;
    outline: none;
}

.crece-form-input::placeholder,
.crece-form-textarea::placeholder {
    color: #94A3B8;
}

.crece-form-input:focus,
.crece-form-select:focus,
.crece-form-textarea:focus {
    border-color: #00D171;
    background: #FFFFFF;
    box-shadow: 0 0 0 4px rgba(0,209,113,0.1);
}

.crece-form-select {
    appearance: none;
    cursor: pointer;
    background-image: url("data:image/svg+xml;charset=UTF-8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23475569' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 18px center;
    background-size: 16px;
    padding-right: 40px;
}

.crece-form-select option { background: #FFFFFF; color: #0F172A; }

.crece-form-textarea {
    min-height: 120px;
    resize: vertical;
}

.crece-form-submit {
    width: 100%;
    padding: 18px;
    border-radius: 50px;
    border: none;
    background: linear-gradient(135deg, #00D171 0%, #00B360 100%);
    color: #FFFFFF;
    font-size: 16px;
    font-weight: 800;
    font-family: 'Outfit', sans-serif;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    margin-top: 8px;
    box-shadow: 0 10px 25px rgba(0,209,113,0.25);
}

.crece-form-submit:hover {
    background: linear-gradient(135deg, #00B360 0%, #009E52 100%);
    transform: translateY(-2px);
    box-shadow: 0 14px 30px rgba(0,209,113,0.35);
}

.crece-form-note {
    font-size: 13px;
    color: #94A3B8;
    text-align: center;
    margin-top: 14px;
}

/* ── Alert states ── */
.crece-alert {
    padding: 14px 20px;
    border-radius: 14px;
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 20px;
    display: none;
}

.crece-alert-success {
    background: #E6F9F1;
    border: 1px solid rgba(0,209,113,0.25);
    color: #00A358;
}

.crece-alert-error {
    background: #FEF2F2;
    border: 1px solid rgba(239,68,68,0.2);
    color: #EF4444;
}

/* ── Responsive ── */
@media (max-width: 1024px) {
    .crece-benefits-grid { grid-template-columns: repeat(2, 1fr); }
    .crece-ecosystem { grid-template-columns: 1fr; }
    .crece-eco-tabs { flex-direction: row; overflow-x: auto; border-right: none; border-bottom: 1px solid #F1F5F9; }
    .crece-contact-wrap { grid-template-columns: 1fr; gap: 40px; }
    .crece-steps { grid-template-columns: repeat(2, 1fr); gap: 40px; }
    .crece-steps::before { display: none; }
}

@media (max-width: 768px) {
    .crece-hero-title { font-size: 48px; letter-spacing: -2px; }
    .crece-benefits-grid { grid-template-columns: 1fr; }
    .crece-section-title { font-size: 36px; }
    .crece-stats-grid { grid-template-columns: repeat(2, 1fr); gap: 20px; }
    .crece-stat-item { border-right: none; padding: 10px 0; }
    .crece-form-row { grid-template-columns: 1fr; }
    .crece-form-card { padding: 32px 24px; }
    .crece-steps { grid-template-columns: 1fr; }
    .crece-hero { padding-top: 120px; }
}
</style>
@endpush

@section('content')

{{-- ══════════ HERO ══════════ --}}
<section class="crece-hero">
    <div class="container-custom crece-hero-inner">
        <div class="crece-badge">
            🚀 Oportunidad de negocio
        </div>
        <h1 class="crece-hero-title">
            Opera <span class="accent">Tootli</span><br>en tu ciudad
        </h1>
        <p class="crece-hero-sub">
            Conviértete en el operador local de Tootli y lleva la super app mexicana a tu región.<br>
            Nosotros ponemos la tecnología, tú construyes el mercado.
        </p>
        <div class="crece-hero-actions">
            <a href="#contacto" class="btn-crece-primary">
                Quiero ser operador <i class="fas fa-arrow-right"></i>
            </a>
            <a href="#beneficios" class="btn-crece-outline">
                Ver beneficios
            </a>
        </div>
    </div>
</section>

{{-- ══════════ STATS BAR ══════════ --}}
<div class="crece-stats-bar">
    <div class="container-custom">
        <div class="crece-stats-grid">
            <div class="crece-stat-item">
                <div class="crece-stat-num">50K+</div>
                <div class="crece-stat-label">Usuarios activos</div>
            </div>
            <div class="crece-stat-item">
                <div class="crece-stat-num">1,200+</div>
                <div class="crece-stat-label">Negocios aliados</div>
            </div>
            <div class="crece-stat-item">
                <div class="crece-stat-num">6+</div>
                <div class="crece-stat-label">Ciudades activas</div>
            </div>
            <div class="crece-stat-item">
                <div class="crece-stat-num">4.8 ⭐</div>
                <div class="crece-stat-label">Calificación App</div>
            </div>
        </div>
    </div>
</div>

{{-- ══════════ BENEFICIOS ══════════ --}}
<section id="beneficios" class="crece-section crece-section-alt">
    <div class="container-custom">
        <div class="crece-section-header">
            <span class="crece-section-tag">¿Por qué Tootli?</span>
            <h2 class="crece-section-title">Todo lo que necesitas<br>para triunfar</h2>
            <p class="crece-section-sub">Operadores de todo México confían en nuestra plataforma para construir negocios rentables.</p>
        </div>

        <div class="crece-benefits-grid">

            <div class="crece-benefit-card">
                <div class="crece-benefit-icon">📱</div>
                <h3>Suite de Apps Lista</h3>
                <p>Apps nativas para usuarios, repartidores y negocios — más el panel admin. Todo listo para arrancar desde el día uno.</p>
            </div>

            <div class="crece-benefit-card">
                <div class="crece-benefit-icon">🛵</div>
                <h3>Logística Multi-vertical</h3>
                <p>No solo comida. Ofrece supermercado, farmacia, paquetería y mandados exprés bajo una sola plataforma.</p>
            </div>

            <div class="crece-benefit-card">
                <div class="crece-benefit-icon">💰</div>
                <h3>Múltiples Fuentes de Ingreso</h3>
                <p>Comisiones por venta, cargos de envío, suscripciones de negocios y publicidad in-app. Varias llaves, un mismo negocio.</p>
            </div>

            <div class="crece-benefit-card">
                <div class="crece-benefit-icon">⚙️</div>
                <h3>Infraestructura Escalable</h3>
                <p>Servidores en la nube, pasarelas de pago integradas y algoritmos de ruteo optimizados para miles de pedidos diarios.</p>
            </div>

            <div class="crece-benefit-card">
                <div class="crece-benefit-icon">🤝</div>
                <h3>Soporte Dedicado</h3>
                <p>Tu equipo de Tootli siempre disponible para capacitación, actualizaciones y resolución de problemas técnicos.</p>
            </div>

            <div class="crece-benefit-card">
                <div class="crece-benefit-icon">🎨</div>
                <h3>Material de Marca</h3>
                <p>Identidad visual premium, assets publicitarios y posicionamiento profesional para liderar tu ciudad desde el inicio.</p>
            </div>

        </div>
    </div>
</section>

{{-- ══════════ ECOSISTEMA ══════════ --}}
<section id="ecosistema" class="crece-section crece-section-dark">
    <div class="container-custom">
        <div class="crece-section-header">
            <span class="crece-section-tag">Lo que incluye</span>
            <h2 class="crece-section-title">Explora el Ecosistema</h2>
            <p class="crece-section-sub">Todo lo que tus usuarios, repartidores y negocios necesitan, conectado en tiempo real.</p>
        </div>

        <div class="crece-ecosystem">
            <div class="crece-eco-tabs">
                <button class="crece-eco-tab active" onclick="showEco('usuarios')">📱 App Usuarios</button>
                <button class="crece-eco-tab" onclick="showEco('repartidor')">🛵 App Repartidor</button>
                <button class="crece-eco-tab" onclick="showEco('negocios')">🏪 Panel Negocios</button>
                <button class="crece-eco-tab" onclick="showEco('direct')">⚡ Tootli Direct</button>
                <button class="crece-eco-tab" onclick="showEco('admin')">💻 Panel Operador</button>
            </div>

            <div class="crece-eco-panels">

                <div id="eco-usuarios" class="crece-eco-panel active">
                    <span class="crece-eco-panel-emoji">📱</span>
                    <h3>La súper app para tus clientes</h3>
                    <p>Pide comida, supermercado, farmacia y envíos locales en una sola app rápida y atractiva.</p>
                    <ul class="crece-eco-checklist">
                        <li>Tracking GPS del repartidor en tiempo real</li>
                        <li>Carrito mixto — varias tiendas en un pedido</li>
                        <li>Historial, favoritos y billetera electrónica</li>
                    </ul>
                </div>

                <div id="eco-repartidor" class="crece-eco-panel">
                    <span class="crece-eco-panel-emoji">🛵</span>
                    <h3>App para Repartidores</h3>
                    <p>Rutas optimizadas, pagos integrados y notificaciones eficientes para mantener tu flotilla moviéndose.</p>
                    <ul class="crece-eco-checklist">
                        <li>Modo oscuro nativo para ahorrar batería</li>
                        <li>Alertas de pedido con sonido prioritario</li>
                        <li>Billetera dinámica y comisiones en vivo</li>
                    </ul>
                </div>

                <div id="eco-negocios" class="crece-eco-panel">
                    <span class="crece-eco-panel-emoji">🏪</span>
                    <h3>Gestor para Restaurantes</h3>
                    <p>Los comercios reciben pedidos, imprimen tickets automáticamente y despachan sin estrés.</p>
                    <ul class="crece-eco-checklist">
                        <li>Impresión térmica Bluetooth automática</li>
                        <li>Panel de campañas y cupones de descuento</li>
                        <li>Carga masiva de inventario y menús</li>
                    </ul>
                </div>

                <div id="eco-direct" class="crece-eco-panel">
                    <span class="crece-eco-panel-emoji">⚡</span>
                    <h3>Tootli Direct — Envíos Express</h3>
                    <p>Cualquier comercio local puede solicitar a tus repartidores sin necesidad de descargar nada.</p>
                    <ul class="crece-eco-checklist">
                        <li>Formulario web, sin apps para el negocio</li>
                        <li>Calculadora dinámica por kilómetro</li>
                        <li>Link de rastreo SMS para el cliente final</li>
                    </ul>
                </div>

                <div id="eco-admin" class="crece-eco-panel">
                    <span class="crece-eco-panel-emoji">💻</span>
                    <h3>Panel Operativo Máster</h3>
                    <p>La torre de control. Diriges toda tu ciudad, vigilas ganancias y configuras tus propias reglas.</p>
                    <ul class="crece-eco-checklist">
                        <li>Mapa global en vivo de todas las zonas</li>
                        <li>Reportes financieros exportables a Excel</li>
                        <li>Control total de comisiones y despachos</li>
                    </ul>
                </div>

            </div>
        </div>
    </div>
</section>

{{-- ══════════ PROCESO ══════════ --}}
<section class="crece-section">
    <div class="container-custom">
        <div class="crece-section-header">
            <span class="crece-section-tag">¿Cómo funciona?</span>
            <h2 class="crece-section-title">De la solicitud al lanzamiento</h2>
            <p class="crece-section-sub">Un proceso simple y acompañado en cada paso.</p>
        </div>

        <div class="crece-steps">
            <div class="crece-step">
                <div class="crece-step-number">1</div>
                <h4>Solicita información</h4>
                <p>Llena el formulario de contacto con los datos de tu ciudad y proyecto.</p>
            </div>
            <div class="crece-step">
                <div class="crece-step-number">2</div>
                <h4>Evaluamos juntos</h4>
                <p>Analizamos el mercado y definimos el modelo ideal para tu zona.</p>
            </div>
            <div class="crece-step">
                <div class="crece-step-number">3</div>
                <h4>Onboarding & Setup</h4>
                <p>Configuramos tu instancia, capacitamos a tu equipo y preparamos el lanzamiento.</p>
            </div>
            <div class="crece-step">
                <div class="crece-step-number">4</div>
                <h4>¡Arrancas!</h4>
                <p>Sales al mercado con soporte continuo de nuestro equipo técnico.</p>
            </div>
        </div>
    </div>
</section>

{{-- ══════════ FORMULARIO ══════════ --}}
<section id="contacto" class="crece-section crece-section-dark">
    <div class="container-custom">
        <div class="crece-contact-wrap">

            {{-- Info izquierda --}}
            <div class="crece-contact-info">
                <div class="crece-section-tag" style="margin-bottom:20px;">Únete al equipo</div>
                <h2>¿Listo para operar Tootli<br>en tu ciudad?</h2>
                <p>Completa el formulario y un miembro de nuestro equipo se pondrá en contacto contigo en menos de 48 horas.</p>

                <ul class="crece-contact-perks">
                    <li>
                        <span class="crece-perk-icon">🚀</span>
                        Sin experiencia previa en tecnología necesaria
                    </li>
                    <li>
                        <span class="crece-perk-icon">💰</span>
                        Inversión inicial desde $15,000 MXN
                    </li>
                    <li>
                        <span class="crece-perk-icon">📊</span>
                        Ingresos recurrentes desde el primer mes
                    </li>
                    <li>
                        <span class="crece-perk-icon">🛠️</span>
                        Soporte técnico y comercial continuo
                    </li>
                    <li>
                        <span class="crece-perk-icon">🇲🇽</span>
                        Tecnología 100% diseñada para México
                    </li>
                </ul>
            </div>

            {{-- Formulario --}}
            <div class="crece-form-card">
                <div class="crece-alert crece-alert-success" id="crece-success">
                    ✅ ¡Gracias! Recibimos tu solicitud. Te contactaremos pronto.
                </div>
                <div class="crece-alert crece-alert-error" id="crece-error">
                    ❌ Hubo un error. Por favor intenta de nuevo.
                </div>

                <form id="crece-form" action="{{ route('crece.contacto') }}" method="POST">
                    @csrf

                    <div class="crece-form-row">
                        <div class="crece-form-group">
                            <label class="crece-form-label">Nombre *</label>
                            <input type="text" name="nombre" class="crece-form-input" placeholder="Tu nombre" required>
                        </div>
                        <div class="crece-form-group">
                            <label class="crece-form-label">Apellido *</label>
                            <input type="text" name="apellido" class="crece-form-input" placeholder="Tu apellido" required>
                        </div>
                    </div>

                    <div class="crece-form-row">
                        <div class="crece-form-group">
                            <label class="crece-form-label">WhatsApp / Teléfono *</label>
                            <input type="tel" name="telefono" class="crece-form-input" placeholder="+52 55 1234 5678" required>
                        </div>
                        <div class="crece-form-group">
                            <label class="crece-form-label">Correo electrónico *</label>
                            <input type="email" name="email" class="crece-form-input" placeholder="hola@tuempresa.com" required>
                        </div>
                    </div>

                    <div class="crece-form-group">
                        <label class="crece-form-label">Ciudad de interés *</label>
                        <input type="text" name="ciudad" class="crece-form-input" placeholder="Ej: Querétaro, Puebla, Monterrey…" required>
                    </div>

                    <div class="crece-form-group">
                        <label class="crece-form-label">¿Cuánto tienes para invertir? *</label>
                        <select name="inversion" class="crece-form-select crece-form-input" required>
                            <option value="" disabled selected>Selecciona un rango</option>
                            <option value="15k-50k">$15,000 – $50,000 MXN</option>
                            <option value="50k-100k">$50,000 – $100,000 MXN</option>
                            <option value="100k-250k">$100,000 – $250,000 MXN</option>
                            <option value="250k+">Más de $250,000 MXN</option>
                        </select>
                    </div>

                    <div class="crece-form-group">
                        <label class="crece-form-label">Cuéntanos sobre ti</label>
                        <textarea name="mensaje" class="crece-form-textarea" placeholder="Experiencia, motivación, tamaño de tu ciudad, etc."></textarea>
                    </div>

                    <button type="submit" class="crece-form-submit" id="crece-submit">
                        <i class="fas fa-paper-plane"></i> Enviar solicitud
                    </button>
                    <p class="crece-form-note">🔒 Tu información es privada y nunca será compartida.</p>
                </form>
            </div>

        </div>
    </div>
</section>

@push('script_2')
<script>
    // ── Ecosystem tabs ──────────────────────────────────────
    function showEco(id) {
        document.querySelectorAll('.crece-eco-tab').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.crece-eco-panel').forEach(p => p.classList.remove('active'));
        event.currentTarget.classList.add('active');
        document.getElementById('eco-' + id).classList.add('active');
    }

    // ── Contact form AJAX ───────────────────────────────────
    document.getElementById('crece-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = document.getElementById('crece-submit');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando…';

        const formData = new FormData(this);

        fetch(this.action, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.getElementById('crece-success').style.display = 'block';
                document.getElementById('crece-error').style.display = 'none';
                document.getElementById('crece-form').reset();
            } else {
                throw new Error(data.message || 'Error');
            }
        })
        .catch(() => {
            document.getElementById('crece-error').style.display = 'block';
            document.getElementById('crece-success').style.display = 'none';
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane"></i> Enviar solicitud';
        });
    });

    // ── Smooth scroll for anchor links ──────────────────────
    document.querySelectorAll('a[href^="#"]').forEach(a => {
        a.addEventListener('click', function(e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
</script>
@endpush

@endsection
