<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Tootli Crece - Opera en tu ciudad</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

  <style>
    /* Reset & Base Variables */
    :root {
      --bg-dark: #020a07;
      --bg-card: rgba(255, 255, 255, 0.02);
      --bg-card-hover: rgba(255, 255, 255, 0.05);
      --primary: #7dff87;
      --primary-glow: rgba(125, 255, 135, 0.4);
      --text-main: #ffffff;
      --text-muted: rgba(255, 255, 255, 0.65);
      --border-color: rgba(255, 255, 255, 0.08);
      --border-hover: rgba(125, 255, 135, 0.3);
      --font-main: 'Inter', sans-serif;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: var(--font-main);
      background: var(--bg-dark);
      color: var(--text-main);
      overflow-x: hidden;
      scroll-behavior: smooth;
    }

    a {
      text-decoration: none;
    }

    /* Animated Ambient Backgrounds */
    .ambient-bg {
      position: fixed;
      top: 0;
      left: 0;
      width: 100vw;
      height: 100vh;
      overflow: hidden;
      z-index: 0;
      pointer-events: none;
    }

    .ambient-blob {
      position: absolute;
      filter: blur(120px);
      border-radius: 50%;
      opacity: 0.4;
      animation: drift 20s infinite alternate ease-in-out;
    }

    .blob-1 {
      width: 600px;
      height: 600px;
      background: rgba(125, 255, 135, 0.15);
      top: -200px;
      right: -100px;
    }

    .blob-2 {
      width: 500px;
      height: 500px;
      background: rgba(0, 200, 150, 0.1);
      bottom: -100px;
      left: -200px;
      animation-delay: -5s;
    }

    @keyframes drift {
      0% { transform: translate(0, 0) scale(1); }
      100% { transform: translate(-50px, 50px) scale(1.1); }
    }

    /* Container & Layout */
    .container {
      width: 90%;
      max-width: 1200px;
      margin: auto;
      position: relative;
      z-index: 2;
    }

    /* Header */
    header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 30px 0;
      border-bottom: 1px solid rgba(255,255,255,0.03);
    }

    .logo {
      font-size: 26px;
      font-weight: 800;
      letter-spacing: -0.5px;
      color: var(--text-main);
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .logo span {
      color: var(--primary);
    }

    nav {
      display: flex;
      gap: 30px;
      background: rgba(255, 255, 255, 0.03);
      padding: 12px 30px;
      border-radius: 100px;
      backdrop-filter: blur(10px);
      border: 1px solid var(--border-color);
    }

    nav a {
      color: var(--text-muted);
      font-size: 14px;
      font-weight: 500;
      transition: 0.3s ease;
    }

    nav a:hover {
      color: var(--primary);
    }

    /* Hero Section */
    .hero {
      min-height: 85vh;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 60px;
      align-items: center;
      padding: 80px 0;
    }

    .badge {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: rgba(125, 255, 135, 0.08);
      border: 1px solid rgba(125, 255, 135, 0.2);
      padding: 10px 16px;
      border-radius: 100px;
      color: var(--primary);
      margin-bottom: 25px;
      font-size: 13px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    h1 {
      font-size: 72px;
      line-height: 1.05;
      margin-bottom: 25px;
      font-weight: 800;
      letter-spacing: -2px;
    }

    h1 span {
      background: linear-gradient(135deg, #ffffff 0%, var(--primary) 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      display: inline-block;
    }

    .description {
      color: var(--text-muted);
      font-size: 19px;
      line-height: 1.7;
      margin-bottom: 40px;
      font-weight: 400;
      max-width: 540px;
    }

    .buttons {
      display: flex;
      gap: 20px;
      flex-wrap: wrap;
    }

    .btn {
      padding: 18px 32px;
      border-radius: 100px;
      font-weight: 600;
      font-size: 16px;
      transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      cursor: pointer;
    }

    .btn-primary {
      background: var(--primary);
      color: #020a07;
      box-shadow: 0 10px 30px rgba(125, 255, 135, 0.25);
    }

    .btn-primary:hover {
      transform: translateY(-4px);
      box-shadow: 0 15px 35px rgba(125, 255, 135, 0.4);
    }

    .btn-secondary {
      background: transparent;
      border: 1px solid var(--border-color);
      color: var(--text-main);
      backdrop-filter: blur(5px);
    }

    .btn-secondary:hover {
      border-color: var(--primary);
      background: rgba(125, 255, 135, 0.05);
      transform: translateY(-4px);
    }

    /* Floating Phone Mockup */
    @keyframes floatPhone {
      0% { transform: translateY(0) rotate(0deg); }
      50% { transform: translateY(-20px) rotate(1deg); }
      100% { transform: translateY(0) rotate(0deg); }
    }

    .phone-wrapper {
      display: flex;
      justify-content: flex-end;
      position: relative;
    }

    .phone-card {
      width: 360px;
      background: rgba(255, 255, 255, 0.02);
      backdrop-filter: blur(30px);
      -webkit-backdrop-filter: blur(30px);
      border-radius: 45px;
      padding: 12px;
      border: 1px solid rgba(255, 255, 255, 0.1);
      box-shadow: 
        0 40px 100px rgba(0, 0, 0, 0.6),
        inset 0 1px 1px rgba(255, 255, 255, 0.2);
      animation: floatPhone 7s ease-in-out infinite;
      position: relative;
    }

    .phone-card::before {
      content: '';
      position: absolute;
      top: 0; left: 20%; right: 20%;
      height: 1px;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
    }

    .screen {
      background: linear-gradient(160deg, #0a1c14 0%, #030d09 100%);
      border-radius: 35px;
      min-height: 720px;
      padding: 35px 25px;
      border: 1px solid rgba(255, 255, 255, 0.03);
      position: relative;
      overflow: hidden;
    }

    .screen::after {
      content: '';
      position: absolute;
      top: 10px;
      left: 50%;
      transform: translateX(-50%);
      width: 100px;
      height: 25px;
      background: #020a07;
      border-radius: 20px;
      z-index: 10;
    }

    .screen-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 30px;
      margin-top: 15px;
    }

    .screen-title {
      font-size: 22px;
      font-weight: 700;
      color: var(--text-main);
    }

    .user-avatar {
      width: 40px; height: 40px;
      border-radius: 50%;
      background: var(--primary);
      display: flex; align-items: center; justify-content: center;
      color: #000; font-weight: bold;
    }

    .mini-card {
      background: rgba(255, 255, 255, 0.03);
      border: 1px solid rgba(255, 255, 255, 0.05);
      border-radius: 20px;
      padding: 20px;
      margin-bottom: 15px;
      transition: 0.3s ease;
    }

    .mini-card:hover {
      background: rgba(255, 255, 255, 0.06);
      transform: scale(1.02);
      border-color: rgba(125, 255, 135, 0.2);
    }

    .mini-card h3 {
      margin-bottom: 8px;
      font-size: 16px;
      font-weight: 600;
    }

    .mini-card p {
      color: var(--text-muted);
      line-height: 1.5;
      font-size: 13px;
    }

    .mini-tag {
      display: inline-block;
      margin-top: 12px;
      background: rgba(125, 255, 135, 0.1);
      color: var(--primary);
      padding: 6px 10px;
      border-radius: 8px;
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    /* Generic Sections */
    section {
      padding: 120px 0;
      position: relative;
    }

    .section-title {
      text-align: center;
      margin-bottom: 80px;
    }

    .section-title h2 {
      font-size: 48px;
      margin-bottom: 20px;
      font-weight: 800;
      letter-spacing: -1px;
    }

    .section-title p {
      color: var(--text-muted);
      max-width: 600px;
      margin: auto;
      line-height: 1.7;
      font-size: 18px;
    }

    /* Grid & Cards */
    .grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 30px;
    }

    .card {
      background: var(--bg-card);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      border: 1px solid var(--border-color);
      border-radius: 30px;
      padding: 45px 35px;
      transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      position: relative;
      overflow: hidden;
    }

    .card::before {
      content: '';
      position: absolute;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: radial-gradient(circle at top right, rgba(125,255,135,0.05), transparent 60%);
      pointer-events: none;
    }

    .card:hover {
      transform: translateY(-10px);
      border-color: var(--border-hover);
      background: var(--bg-card-hover);
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4), 0 0 40px rgba(125, 255, 135, 0.05);
    }

    .icon-box {
      width: 64px;
      height: 64px;
      border-radius: 20px;
      background: linear-gradient(135deg, rgba(125, 255, 135, 0.15), rgba(125, 255, 135, 0.02));
      border: 1px solid rgba(125, 255, 135, 0.2);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 28px;
      margin-bottom: 25px;
      box-shadow: inset 0 2px 5px rgba(255,255,255,0.1);
    }

    .card h3 {
      margin-bottom: 15px;
      font-size: 22px;
      font-weight: 700;
    }

    .card p {
      color: var(--text-muted);
      line-height: 1.7;
      font-size: 15px;
    }

    /* CTA Section */
    .cta {
      background: linear-gradient(145deg, rgba(125, 255, 135, 0.08) 0%, rgba(2, 10, 7, 1) 100%);
      border: 1px solid rgba(125, 255, 135, 0.15);
      border-radius: 40px;
      padding: 80px 40px;
      text-align: center;
      position: relative;
      overflow: hidden;
    }

    .cta::before {
      content: '';
      position: absolute;
      top: -50%; left: -50%;
      width: 200%; height: 200%;
      background: radial-gradient(circle, rgba(125,255,135,0.05) 0%, transparent 60%);
      animation: rotate 30s linear infinite;
    }

    @keyframes rotate {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    .cta-content {
      position: relative;
      z-index: 2;
    }

    .cta h2 {
      font-size: 52px;
      margin-bottom: 25px;
      font-weight: 800;
      letter-spacing: -1px;
    }

    .cta p {
      max-width: 650px;
      margin: auto auto 40px;
      color: var(--text-muted);
      line-height: 1.8;
      font-size: 18px;
    }

    /* Footer */
    footer {
      border-top: 1px solid var(--border-color);
      padding: 40px 0;
      text-align: center;
      color: var(--text-muted);
      font-size: 14px;
      margin-top: 40px;
    }

    footer span {
      color: var(--primary);
      font-weight: 600;
    }

    /* Responsive Design */
    @media (max-width: 992px) {
      .hero {
        grid-template-columns: 1fr;
        text-align: center;
        gap: 80px;
      }
      
      .description {
        margin: 0 auto 40px;
      }
      
      .buttons {
        justify-content: center;
      }

      .phone-wrapper {
        justify-content: center;
      }
    }

    @media (max-width: 768px) {
      h1 { font-size: 52px; }
      .section-title h2 { font-size: 38px; }
      .cta h2 { font-size: 36px; }
      
      nav { display: none; }
      
      .phone-card {
        width: 100%;
        max-width: 340px;
      }
      
      .cta { padding: 50px 25px; }
      
      .grid { grid-template-columns: 1fr; }
    }
  </style>
</head>

<body>

<!-- Background Ambient Elements -->
<div class="ambient-bg">
  <div class="ambient-blob blob-1"></div>
  <div class="ambient-blob blob-2"></div>
</div>

<!-- Header -->
<header class="container">
  <div class="logo">
    tootli <span>crece</span>
  </div>

  <nav>
    <a href="#modelo">El Modelo</a>
    <a href="#beneficios">Beneficios</a>
    <a href="#contacto">Únete</a>
  </nav>
</header>

<!-- Hero -->
<section class="hero container">
  <div>
    <div class="badge">
      <span style="font-size:16px;">🚀</span> Tu super app regional
    </div>

    <h1>
      Opera <span>Tootli</span><br>
      en tu ciudad.
    </h1>

    <p class="description">
      Lleva la tecnología de Tootli a tu región y construye el ecosistema local de delivery, comercio y logística con una plataforma moderna, robusta y lista para arrancar.
    </p>

    <div class="buttons">
      <a href="#contacto" class="btn btn-primary">
        Quiero información
      </a>
      <a href="#modelo" class="btn btn-secondary">
        Descubrir modelo
      </a>
    </div>
  </div>

  <div class="phone-wrapper">
    <div class="phone-card">
      <div class="screen">
        <div class="screen-header">
          <div class="screen-title">Resumen</div>
          <div class="user-avatar">T</div>
        </div>

        <div class="mini-card">
          <h3>Inversión Inicial Baja</h3>
          <p>Opera tu ciudad usando el ecosistema Tootli y genera ingresos recurrentes con delivery y logística local desde $15,000.</p>
          <div class="mini-tag">Operador local</div>
        </div>

        <div class="mini-card">
          <h3>Tootli Direct</h3>
          <p>Permite que negocios locales soliciten repartidores bajo demanda desde WhatsApp o sus propios canales.</p>
        </div>

        <div class="mini-card">
          <h3>Marketplace + Logística</h3>
          <p>Comida, supermercado, mandados y paquetería en una sola plataforma unificada.</p>
        </div>

        <div class="mini-card" style="border-color: rgba(125,255,135,0.2); background: rgba(125,255,135,0.03);">
          <h3>Hecho en México 🇲🇽</h3>
          <p>Tecnología diseñada exclusivamente para las dinámicas de ciudades mexicanas.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Features Grid -->
<section id="modelo">
  <div class="container">
    <div class="section-title">
      <h2>Un ecosistema completo</h2>
      <p>Tootli combina tecnología de punta centralizada con el poder de los operadores locales para expandir el delivery hiperlocal en todo México.</p>
    </div>

    <div class="grid">
      <div class="card">
        <div class="icon-box">📱</div>
        <h3>Suite de Apps Listas</h3>
        <p>Entregamos aplicaciones nativas para usuarios, repartidores y negocios, respaldadas por un potente panel de administración en la nube.</p>
      </div>

      <div class="card">
        <div class="icon-box">🛵</div>
        <h3>Logística Integral</h3>
        <p>No solo comida. Ofrece envíos de paquetes, compras en supermercado, farmacias y mandados exprés en tu ciudad.</p>
      </div>

      <div class="card">
        <div class="icon-box">🏪</div>
        <h3>Gestión de Flotillas</h3>
        <p>Sistema inteligente de asignación de repartidores, rastreo en vivo y manejo de ganancias totalmente automatizado.</p>
      </div>

      <div class="card">
        <div class="icon-box">🤝</div>
        <h3>Aliado Tecnológico</h3>
        <p>Nosotros nos encargamos del código, los servidores y el mantenimiento. Tú te encargas de sumar negocios y crecer el mercado.</p>
      </div>
    </div>
  </div>
</section>

<!-- Benefits -->
<section id="beneficios">
  <div class="container">
    <div class="section-title">
      <h2>Todo lo que necesitas para triunfar</h2>
    </div>

    <div class="grid">
      <div class="card">
        <div class="icon-box">⚙️</div>
        <h3>Infraestructura</h3>
        <p>Servidores escalables, pasarelas de pago integradas y algoritmos de ruteo optimizados listos para procesar miles de pedidos.</p>
      </div>

      <div class="card">
        <div class="icon-box">🎨</div>
        <h3>Material de Marca</h3>
        <p>Identidad visual premium, assets publicitarios y presencia profesional para posicionarte como el líder de tu ciudad.</p>
      </div>

      <div class="card">
        <div class="icon-box">💰</div>
        <h3>Múltiples Ingresos</h3>
        <p>Monetiza a través de comisiones por venta, cargos por envío, suscripciones de negocios y publicidad dentro de la app.</p>
      </div>
    </div>
  </div>
</section>

<!-- CTA -->
<section>
  <div class="container">
    <div class="cta">
      <div class="cta-content">
        <h2>Tú operas el negocio.<br>Nosotros la tecnología.</h2>
        <p>Buscamos emprendedores audaces para expandir Tootli en las mejores ciudades de México y construir la red de logística más fuerte del país.</p>
        <a href="mailto:hola@tootli.mx" class="btn btn-primary" style="font-size: 18px; padding: 20px 40px;">
          Contáctanos: hola@tootli.mx
        </a>
      </div>
    </div>
  </div>
</section>

<!-- Footer -->
<footer id="contacto" class="container">
  <p>© 2026 <span>Tootli</span> Crece • Tu super app mexicana 🇲🇽</p>
</footer>

</body>
</html>
