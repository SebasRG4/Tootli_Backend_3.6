@extends('layouts.landing.app')

@php($business_name = \App\CentralLogics\Helpers::get_business_settings('business_name'))
@section('title', translate('messages.home') . ' | ' . ($business_name ?? 'Tootli'))

@section('content')

    {{-- Hero Section Premium --}}
    <section id="home" class="hero-bolt">
        <div class="hero-bolt-particles" id="hero-particles"></div>
        <div class="container-custom hero-bolt-grid">
            {{-- Left: Text Content --}}
            <div class="hero-bolt-content wow fadeInLeft">
                <div class="hero-badge">
                    <span>🇲🇽</span> Hecho en México
                </div>
                <h1 class="hero-bolt-title">
                    Tu <span class="text-gradient-green">super app</span><br>mexicana.
                </h1>
                <p class="hero-bolt-subtitle">
                    Lo hecho en México está bien hecho.<br>
                    Comida, súper, viajes y más — todo en un solo lugar.
                </p>
                <div class="hero-bolt-actions">
                    <a href="https://tootli.mx/descargar" target="_blank" class="btn-bolt">
                        Descargar gratis <i class="fas fa-arrow-right"></i>
                    </a>
                    @if (!empty($hero_links['playstore_url_status']) && $hero_links['playstore_url_status'] == 1)
                    <a href="{{ $hero_links['playstore_url'] ?? 'https://tootli.mx/descargar' }}" target="_blank" class="btn-store-hero">
                        <img src="{{ asset('assets/landing/img/google.svg') }}" alt="Google Play" height="36">
                    </a>
                    @endif
                    @if (!empty($hero_links['apple_store_url_status']) && $hero_links['apple_store_url_status'] == 1)
                    <a href="{{ $hero_links['apple_store_url'] ?? 'https://tootli.mx/descargar' }}" target="_blank" class="btn-store-hero">
                        <img src="{{ asset('assets/landing/img/apple.svg') }}" alt="App Store" height="36">
                    </a>
                    @endif
                </div>
                {{-- Stats Row --}}
                <div class="hero-stats-row">
                    <div class="hero-stat">
                        <span class="hero-stat-number">50K+</span>
                        <span class="hero-stat-label">Usuarios activos</span>
                    </div>
                    <div class="hero-stat-divider"></div>
                    <div class="hero-stat">
                        <span class="hero-stat-number">1,200+</span>
                        <span class="hero-stat-label">Negocios aliados</span>
                    </div>
                    <div class="hero-stat-divider"></div>
                    <div class="hero-stat">
                        <span class="hero-stat-number">4.8 ⭐</span>
                        <span class="hero-stat-label">Calificación App</span>
                    </div>
                </div>
            </div>

            {{-- Right: App Mockup --}}
            <div class="hero-bolt-mockup wow fadeInRight" data-wow-delay="0.2s">
                <div class="hero-mockup-glow"></div>
                <img src="{{ asset('assets/landing/img/hero-app-mockup.png') }}"
                     alt="Tootli App"
                     class="hero-mockup-img">
            </div>
        </div>
        {{-- Wave divider --}}
        <div class="hero-wave">
            <svg viewBox="0 0 1440 80" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
                <path d="M0,40 C360,80 1080,0 1440,40 L1440,80 L0,80 Z" fill="#0a0a0a"/>
            </svg>
        </div>
    </section>

    {{-- Comparison Section --}}
    <section id="beneficios" class="comparison-section section-padding">
        <div class="container-custom">
            <div class="text-center mb-5 wow fadeInUp">
                <h2 class="section-title">¿Por qué elegir <span class="text-gradient-green">Tootli</span>?</h2>
            </div>
            <div class="row g-4">
                <div class="col-md-6 wow fadeInLeft">
                    <div class="comp-card comp-card-red">
                        <h4 class="mb-4">Otras Apps</h4>
                        <div class="comp-item">
                            <div class="comp-icon"><i class="fas fa-clock"></i></div>
                            <div>
                                <strong>Tiempos inciertos</strong>
                                <p class="mb-0 text-muted">Esperas interminables sin saber dónde está tu pedido.</p>
                            </div>
                        </div>
                        <div class="comp-item">
                            <div class="comp-icon"><i class="fas fa-walking"></i></div>
                            <div>
                                <strong>Tienes que salir</strong>
                                <p class="mb-0 text-muted">¿Falta algo? Tienes que ir tú mismo al súper.</p>
                            </div>
                        </div>
                        <div class="comp-item">
                            <div class="comp-icon"><i class="fab fa-whatsapp"></i></div>
                            <div>
                                <strong>Pedidos por WhatsApp</strong>
                                <p class="mb-0 text-muted">Procesos manuales, lentos y propensos a errores.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 wow fadeInRight">
                    <div class="comp-card comp-card-green">
                        <h4 class="mb-4">Tootli</h4>
                        <div class="comp-item">
                            <div class="comp-icon"><i class="fas fa-bolt"></i></div>
                            <div>
                                <strong>Entrega en minutos</strong>
                                <p class="mb-0 text-muted">Tu pedido llega más rápido de lo que imaginas.</p>
                            </div>
                        </div>
                        <div class="comp-item">
                            <div class="comp-icon"><i class="fas fa-shopping-bag"></i></div>
                            <div>
                                <strong>Todo en un solo lugar</strong>
                                <p class="mb-0 text-muted">Desde un antojo hasta la despensa del mes.</p>
                            </div>
                        </div>
                        <div class="comp-item">
                            <div class="comp-icon"><i class="fas fa-map-marker-alt"></i></div>
                            <div>
                                <strong>Rastreo en tiempo real</strong>
                                <p class="mb-0 text-muted">Sigue a tu repartidor en el mapa segundo a segundo.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Categories Grid --}}
    <section id="categorias" class="section-padding bg-light">
        <div class="container-custom">
            <div class="text-center mb-5 wow fadeInUp">
                <h2 class="section-title">Explora nuestras <span class="text-gradient-green">Categorías</span></h2>
                <p class="text-muted">Todo lo que necesitas, a un clic de distancia.</p>
            </div>
            <div class="row g-4">
                {{-- Comida --}}
                <div class="col-6 col-md-4 col-lg-2 wow fadeInUp" data-wow-delay="0.1s">
                    <a href="#" class="category-card">
                        <div class="category-img-wrapper">
                            <i class="fas fa-hamburger fa-3x text-gradient-green"></i>
                        </div>
                        <div class="category-name">Comida</div>
                    </a>
                </div>
                {{-- Súper --}}
                <div class="col-6 col-md-4 col-lg-2 wow fadeInUp" data-wow-delay="0.2s">
                    <a href="#" class="category-card">
                        <div class="category-img-wrapper">
                            <i class="fas fa-shopping-basket fa-3x text-gradient-green"></i>
                        </div>
                        <div class="category-name">Súper</div>
                    </a>
                </div>
                {{-- Farmacia --}}
                <div class="col-6 col-md-4 col-lg-2 wow fadeInUp" data-wow-delay="0.3s">
                    <a href="#" class="category-card">
                        <div class="category-img-wrapper">
                            <i class="fas fa-pills fa-3x text-gradient-green"></i>
                        </div>
                        <div class="category-name">Farmacia</div>
                    </a>
                </div>
                {{-- Paquetería --}}
                <div class="col-6 col-md-4 col-lg-2 wow fadeInUp" data-wow-delay="0.4s">
                    <a href="#" class="category-card">
                        <div class="category-img-wrapper">
                            <i class="fas fa-box fa-3x text-gradient-green"></i>
                        </div>
                        <div class="category-name">Envíos</div>
                    </a>
                </div>
                {{-- Viajes --}}
                <div class="col-6 col-md-4 col-lg-2 wow fadeInUp" data-wow-delay="0.5s">
                    <a href="#" class="category-card">
                        <div class="category-img-wrapper">
                            <i class="fas fa-car fa-3x text-gradient-green"></i>
                        </div>
                        <div class="category-name">Viajes</div>
                    </a>
                </div>
                {{-- Pagos --}}
                <div class="col-6 col-md-4 col-lg-2 wow fadeInUp" data-wow-delay="0.6s">
                    <a href="#" class="category-card">
                        <div class="category-img-wrapper">
                            <i class="fas fa-wallet fa-3x text-gradient-green"></i>
                        </div>
                        <div class="category-name">Pagos</div>
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Why Choose --}}
    <section class="section-padding">
        <div class="container-custom">
            <div class="row g-4">
                <div class="col-md-6 col-lg-3 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="feature-box">
                        <div class="feature-icon-box text-gradient-green"><i class="fas fa-shield-alt fa-2x"></i></div>
                        <div class="feature-text">
                            <h5>Pago Seguro</h5>
                            <p>Tus transacciones están protegidas.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 wow fadeInUp" data-wow-delay="0.2s">
                    <div class="feature-box">
                        <div class="feature-icon-box text-gradient-green"><i class="fas fa-headset fa-2x"></i></div>
                        <div class="feature-text">
                            <h5>Soporte 24/7</h5>
                            <p>Estamos aquí para ayudarte.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 wow fadeInUp" data-wow-delay="0.3s">
                    <div class="feature-box">
                        <div class="feature-icon-box text-gradient-green"><i class="fas fa-star fa-2x"></i></div>
                        <div class="feature-text">
                            <h5>Calidad Total</h5>
                            <p>Solo los mejores establecimientos.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 wow fadeInUp" data-wow-delay="0.4s">
                    <div class="feature-box">
                        <div class="feature-icon-box text-gradient-green"><i class="fas fa-tags fa-2x"></i></div>
                        <div class="feature-text">
                            <h5>Ofertas</h5>
                            <p>Descuentos exclusivos cada día.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Grocery Banner --}}
    <section id="super" class="section-padding">
        <div class="container-custom">
            <div class="grocery-promo wow fadeInUp">
                <div class="row align-items-center">
                    <div class="col-lg-6">
                        <h2 class="mb-4">Tu súper completo,<br>sin salir de casa.</h2>
                        <p class="mb-4">Olvídate de las filas y el tráfico. Recibe frutas, verduras, carnes y más en la puerta de tu hogar.</p>
                        <div class="check-item"><i class="fas fa-check-circle check-icon"></i> Frescura garantizada</div>
                        <div class="check-item"><i class="fas fa-check-circle check-icon"></i> Precios de tienda</div>
                        <div class="check-item"><i class="fas fa-check-circle check-icon"></i> Entrega express</div>
                        <a href="#" class="btn-tootli btn-tootli-primary mt-4">Hacer mi súper ahora</a>
                    </div>
                    <div class="col-lg-6 text-center">
                        <img src="{{ asset('assets/landing/img/grocery-basket-3d.png') }}" alt="Supermarket" class="img-fluid" style="max-height: 400px;">
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- How it Works --}}
    <section class="section-padding">
        <div class="container-custom">
            <div class="text-center mb-5">
                <h2>¿Cómo funciona <span class="text-gradient-green">Tootli</span>?</h2>
            </div>
            <div class="row g-5">
                <div class="col-md-4 step-item wow fadeInUp" data-wow-delay="0.1s">
                    <div class="step-icon-wrapper"><i class="fas fa-mobile-alt fa-4x text-gradient-green"></i></div>
                    <div class="step-number">1</div>
                    <h4>Elige tus productos</h4>
                    <p class="text-muted">Explora miles de opciones en nuestra app o web.</p>
                </div>
                <div class="col-md-4 step-item wow fadeInUp" data-wow-delay="0.2s">
                    <div class="step-icon-wrapper"><i class="fas fa-credit-card fa-4x text-gradient-green"></i></div>
                    <div class="step-number">2</div>
                    <h4>Paga y confirma</h4>
                    <p class="text-muted">Elige tu método de pago favorito y listo.</p>
                </div>
                <div class="col-md-4 step-item wow fadeInUp" data-wow-delay="0.3s">
                    <div class="step-icon-wrapper"><i class="fas fa-motorcycle fa-4x text-gradient-green"></i></div>
                    <div class="step-number">3</div>
                    <h4>Recibe en minutos</h4>
                    <p class="text-muted">Sigue a tu repartidor hasta que llegue a tu puerta.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Partners --}}
    <section id="aliados" class="section-padding bg-light">
        <div class="container-custom">
            <div class="row g-4">
                <div class="col-lg-6 wow fadeInLeft">
                    <div class="partner-card card-business">
                        <div>
                            <h3 class="mb-3">¿Tienes un negocio?</h3>
                            <p class="mb-4">Incrementa tus ventas llegando a miles de nuevos clientes con Tootli.</p>
                            <a href="{{ route('restaurant.create') }}" class="btn-tootli btn-tootli-primary">Registrar mi negocio</a>
                        </div>
                        <img src="{{ asset('assets/landing/img/partners-3d.png') }}" alt="Business" class="img-fluid d-none d-md-block" style="width: 200px;">
                    </div>
                </div>
                <div class="col-lg-6 wow fadeInRight">
                    <div class="partner-card card-courier">
                        <div>
                            <h3 class="mb-3">Sé tu propio jefe</h3>
                            <p class="mb-4">Gana dinero extra repartiendo en tus tiempos libres. ¡Tú decides cuándo!</p>
                            <a href="{{ route('deliveryman.create') }}" class="btn-tootli btn-tootli-primary" style="background-color: #F8A629;">Ser repartidor</a>
                        </div>
                        <i class="fas fa-motorcycle fa-8x" style="color: rgba(248, 166, 41, 0.2); position: absolute; right: 20px; bottom: 20px;"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Download CTA --}}
    <section class="container-custom">
        <div class="download-section-2024 wow zoomIn">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <h2 class="mb-4">Lleva a Tootli en tu bolsillo.</h2>
                    <p class="mb-4">Descarga nuestra app y disfruta de la mejor experiencia de delivery en tu ciudad.</p>
                    <div class="d-flex flex-wrap gap-3">
                        @if (!empty($hero_links['playstore_url_status']) && $hero_links['playstore_url_status'] == 1)
                            <a href="{{ $hero_links['playstore_url'] ?? '#' }}" target="_blank">
                                <img src="{{ asset('assets/landing/img/google.svg') }}" alt="Google Play" height="50">
                            </a>
                        @endif
                        @if (!empty($hero_links['apple_store_url_status']) && $hero_links['apple_store_url_status'] == 1)
                            <a href="{{ $hero_links['apple_store_url'] ?? '#' }}" target="_blank">
                                <img src="{{ asset('assets/landing/img/apple.svg') }}" alt="App Store" height="50">
                            </a>
                        @endif
                    </div>
                </div>
                <div class="col-lg-5 text-center">
                    <img src="{{ asset('assets/landing/img/hero-3d.png') }}" alt="Mockup" class="img-fluid" style="max-height: 300px; transform: scale(1.2);">
                </div>
            </div>
        </div>
    </section>

@section('script_2')
    <script>
        $(document).ready(function() {
            // Smooth scroll for nav links
            $('a.nav-link-custom').on('click', function(event) {
                if (this.hash !== "") {
                    event.preventDefault();
                    var hash = this.hash;
                    $('html, body').animate({
                        scrollTop: $(hash).offset().top - 80
                    }, 800);
                }
            });
        });
    </script>
@endsection
@endsection
