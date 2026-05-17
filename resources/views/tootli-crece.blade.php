<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Tootli Crece</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <style>
    *{
      margin:0;
      padding:0;
      box-sizing:border-box;
    }

    body{
      font-family:'Inter',sans-serif;
      background:#041f16;
      color:white;
      overflow-x:hidden;
    }

    a{
      text-decoration:none;
    }

    .bg-glow{
      position:fixed;
      width:700px;
      height:700px;
      background:radial-gradient(circle, rgba(0,255,140,.15) 0%, rgba(0,0,0,0) 70%);
      top:-250px;
      right:-200px;
      pointer-events:none;
      z-index:0;
    }

    .container{
      width:90%;
      max-width:1200px;
      margin:auto;
      position:relative;
      z-index:2;
    }

    header{
      display:flex;
      justify-content:space-between;
      align-items:center;
      padding:30px 0;
    }

    .logo{
      font-size:30px;
      font-weight:800;
      color:#7dff87;
    }

    nav{
      display:flex;
      gap:25px;
    }

    nav a{
      color:rgba(255,255,255,.75);
      transition:.3s;
    }

    nav a:hover{
      color:#7dff87;
    }

    .hero{
      min-height:90vh;
      display:grid;
      grid-template-columns:repeat(auto-fit,minmax(320px,1fr));
      gap:60px;
      align-items:center;
      padding:60px 0;
    }

    .badge{
      display:inline-flex;
      align-items:center;
      gap:10px;
      background:rgba(125,255,135,.08);
      border:1px solid rgba(125,255,135,.15);
      padding:12px 18px;
      border-radius:999px;
      color:#7dff87;
      margin-bottom:30px;
      font-size:14px;
      font-weight:600;
    }

    h1{
      font-size:74px;
      line-height:.95;
      margin-bottom:25px;
      font-weight:800;
    }

    h1 span{
      color:#7dff87;
    }

    .description{
      color:rgba(255,255,255,.72);
      font-size:19px;
      line-height:1.8;
      margin-bottom:35px;
      max-width:620px;
    }

    .buttons{
      display:flex;
      gap:18px;
      flex-wrap:wrap;
    }

    .btn-primary{
      background:#7dff87;
      color:#041f16;
      padding:17px 28px;
      border-radius:18px;
      font-weight:700;
      transition:.3s;
    }

    .btn-primary:hover{
      transform:translateY(-3px);
      box-shadow:0 20px 40px rgba(125,255,135,.18);
    }

    .btn-secondary{
      border:1px solid rgba(255,255,255,.12);
      padding:17px 28px;
      border-radius:18px;
      color:white;
      transition:.3s;
    }

    .btn-secondary:hover{
      border-color:#7dff87;
      color:#7dff87;
    }

    .phone{
      display:flex;
      justify-content:center;
      position:relative;
    }

    .phone-card{
      width:360px;
      background:#0c2c20;
      border-radius:42px;
      padding:18px;
      border:1px solid rgba(255,255,255,.06);
      box-shadow:0 40px 120px rgba(0,0,0,.5);
    }

    .screen{
      background:linear-gradient(180deg,#071912,#103327);
      border-radius:30px;
      min-height:720px;
      padding:28px;
    }

    .screen-title{
      font-size:26px;
      font-weight:700;
      margin-bottom:30px;
    }

    .mini-card{
      background:rgba(255,255,255,.04);
      border:1px solid rgba(255,255,255,.05);
      border-radius:24px;
      padding:22px;
      margin-bottom:18px;
    }

    .mini-card h3{
      margin-bottom:10px;
      font-size:18px;
    }

    .mini-card p{
      color:rgba(255,255,255,.7);
      line-height:1.6;
      font-size:14px;
    }

    .mini-tag{
      display:inline-block;
      margin-top:16px;
      background:rgba(125,255,135,.1);
      color:#7dff87;
      padding:8px 12px;
      border-radius:12px;
      font-size:13px;
      font-weight:600;
    }

    section{
      padding:100px 0;
    }

    .section-title{
      text-align:center;
      margin-bottom:70px;
    }

    .section-title h2{
      font-size:52px;
      margin-bottom:15px;
    }

    .section-title p{
      color:rgba(255,255,255,.7);
      max-width:760px;
      margin:auto;
      line-height:1.8;
    }

    .grid{
      display:grid;
      grid-template-columns:repeat(auto-fit,minmax(260px,1fr));
      gap:25px;
    }

    .card{
      background:rgba(255,255,255,.04);
      border:1px solid rgba(255,255,255,.05);
      border-radius:28px;
      padding:32px;
      transition:.3s;
    }

    .card:hover{
      transform:translateY(-6px);
      border-color:rgba(125,255,135,.25);
    }

    .icon{
      width:70px;
      height:70px;
      border-radius:20px;
      background:rgba(125,255,135,.08);
      display:flex;
      align-items:center;
      justify-content:center;
      font-size:32px;
      margin-bottom:24px;
    }

    .card h3{
      margin-bottom:14px;
      font-size:24px;
    }

    .card p{
      color:rgba(255,255,255,.7);
      line-height:1.8;
    }

    .cta{
      background:linear-gradient(135deg, rgba(125,255,135,.12), rgba(125,255,135,.03));
      border:1px solid rgba(125,255,135,.12);
      border-radius:40px;
      padding:70px;
      text-align:center;
    }

    .cta h2{
      font-size:56px;
      margin-bottom:20px;
    }

    .cta p{
      max-width:760px;
      margin:auto auto 35px;
      color:rgba(255,255,255,.72);
      line-height:1.8;
      font-size:18px;
    }

    footer{
      border-top:1px solid rgba(255,255,255,.06);
      padding:40px 0;
      text-align:center;
      color:rgba(255,255,255,.5);
      margin-top:80px;
    }

    @media(max-width:768px){

      h1{
        font-size:48px;
      }

      .section-title h2,
      .cta h2{
        font-size:38px;
      }

      nav{
        display:none;
      }

      .phone-card{
        width:100%;
        max-width:340px;
      }

      .cta{
        padding:40px 25px;
      }
    }

  </style>
</head>

<body>

<div class="bg-glow"></div>

<header class="container">

  <div class="logo">
    tootli crece
  </div>

  <nav>
    <a href="#modelo">Modelo</a>
    <a href="#beneficios">Beneficios</a>
    <a href="#contacto">Contacto</a>
  </nav>

</header>

<section class="hero container">

  <div>

    <div class="badge">
      🇲🇽 Tu super app mexicana
    </div>

    <h1>
      Opera <span>Tootli</span><br>
      en tu ciudad.
    </h1>

    <p class="description">
      Lleva la tecnología de Tootli a tu ciudad y construye el ecosistema local de delivery, supermercado y logística con una plataforma moderna y lista para operar.
    </p>

    <div class="buttons">
      <a href="#contacto" class="btn-primary">
        Quiero información
      </a>

      <a href="#beneficios" class="btn-secondary">
        Ver beneficios
      </a>
    </div>

  </div>

  <div class="phone">

    <div class="phone-card">

      <div class="screen">

        <div class="screen-title">
          Tootli Crece
        </div>

        <div class="mini-card">
          <h3>Tu app desde $15,000</h3>

          <p>
            Opera tu ciudad usando el ecosistema Tootli y genera ingresos recurrentes con delivery y logística local.
          </p>

          <div class="mini-tag">
            Operador local
          </div>
        </div>

        <div class="mini-card">
          <h3>Tootli Direct</h3>

          <p>
            Permite que negocios locales soliciten repartidores bajo demanda desde WhatsApp o sus propios canales.
          </p>
        </div>

        <div class="mini-card">
          <h3>Marketplace + logística</h3>

          <p>
            Comida, supermercado, mandados y entregas locales en una sola plataforma.
          </p>
        </div>

        <div class="mini-card">
          <h3>Hecho en México 🇲🇽</h3>

          <p>
            Tecnología diseñada para ciudades mexicanas y negocios locales.
          </p>
        </div>

      </div>

    </div>

  </div>

</section>

<section id="modelo">

  <div class="container">

    <div class="section-title">

      <h2>
        Un modelo listo para crecer
      </h2>

      <p>
        Tootli combina tecnología centralizada con operadores locales para expandir el ecosistema de delivery y logística en México.
      </p>

    </div>

    <div class="grid">

      <div class="card">
        <div class="icon">📱</div>

        <h3>Apps listas</h3>

        <p>
          Aplicaciones para usuarios, repartidores, negocios y panel administrativo totalmente funcionales.
        </p>
      </div>

      <div class="card">
        <div class="icon">🛵</div>

        <h3>Logística local</h3>

        <p>
          Delivery, mandados y Tootli Direct para conectar negocios y repartidores.
        </p>
      </div>

      <div class="card">
        <div class="icon">🏪</div>

        <h3>Super app</h3>

        <p>
          Comida, supermercado, farmacias, tiendas y mucho más desde una sola plataforma.
        </p>
      </div>

      <div class="card">
        <div class="icon">🇲🇽</div>

        <h3>Marca mexicana</h3>

        <p>
          Un ecosistema moderno enfocado en negocios y ciudades mexicanas.
        </p>
      </div>

    </div>

  </div>

</section>

<section id="beneficios">

  <div class="container">

    <div class="section-title">

      <h2>
        ¿Qué incluye Tootli?
      </h2>

    </div>

    <div class="grid">

      <div class="card">
        <div class="icon">⚙️</div>

        <h3>Tecnología</h3>

        <p>
          Backend, apps, tracking en tiempo real y sistema administrativo.
        </p>
      </div>

      <div class="card">
        <div class="icon">🎨</div>

        <h3>Branding</h3>

        <p>
          Identidad visual premium y presencia profesional para tu ciudad.
        </p>
      </div>

      <div class="card">
        <div class="icon">📈</div>

        <h3>Modelo escalable</h3>

        <p>
          Crecimiento por zonas con enfoque hiperlocal y expansión progresiva.
        </p>
      </div>

      <div class="card">
        <div class="icon">💰</div>

        <h3>Ingresos recurrentes</h3>

        <p>
          Genera ingresos con comisiones, logística y negocios afiliados.
        </p>
      </div>

    </div>

  </div>

</section>

<section>

  <div class="container">

    <div class="cta">

      <h2>
        Tú operas.<br>
        Tootli impulsa tu ciudad.
      </h2>

      <p>
        Buscamos operadores para expandir Tootli en los estados de México y construir la red local de delivery y logística más fuerte del país.
      </p>

      <a href="mailto:hola@tootli.mx" class="btn-primary">
        hola@tootli.mx
      </a>

    </div>

  </div>

</section>

<footer id="contacto">
  © 2026 Tootli Crece • Tu super app mexicana 🇲🇽
</footer>

</body>
</html>
