
<!DOCTYPE html>
<?php
    $landing_site_direction = session()->get('landing_site_direction');
    $country= \App\CentralLogics\Helpers::get_business_settings('country')  ;
    $countryCode= strtolower($country??'auto');
   $metaData=  \App\Models\DataSetting::where('type','admin_landing_page')->whereIn('key',['meta_title','meta_description','meta_image'])->get()->keyBy('key')??[];
   $business_name = \App\CentralLogics\Helpers::get_business_settings('business_name');
?>
<html dir="{{ $landing_site_direction }}" lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title')</title>
    @include('layouts.landing._seo')

    <link rel="stylesheet" href="{{ asset('assets/landing/css/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/landing/css/customize-animate.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/landing/css/odometer.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/landing/css/owl.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/admin/css/toastr.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/landing/css/main.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/landing/css/tootli-theme.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/landing/css/tootli-landing-2024.css') }}?v={{ filemtime(public_path('assets/landing/css/tootli-landing-2024.css')) }}"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>

    <link rel="stylesheet" href="{{asset('assets/admin/intltelinput/css/intlTelInput.css')}}">


    <link rel="icon" type="image/x-icon" href="{{\App\CentralLogics\Helpers::iconFullUrl()}}">
    @stack('css_or_js')
     @php($backgroundChange = \App\CentralLogics\Helpers::get_business_settings('backgroundChange')??[])
    @if (isset($backgroundChange['primary_1_hex']) && isset($backgroundChange['primary_2_hex']))
        <style>
            :root {
                --base-1: <?php echo $backgroundChange['primary_1_hex']; ?>;
                --base-rgb: <?php echo $backgroundChange['primary_1_rgb']; ?>;
                --base-2: <?php echo $backgroundChange['primary_2_hex']; ?>;
                --base-rgb-2:<?php echo $backgroundChange['primary_2_rgb']; ?>;
            }
        </style>
    @endif
</head>

<body>

    @php($fixed_link = \App\Models\DataSetting::where(['key'=>'fixed_link','type'=>'admin_landing_page'])->first())
    @php($fixed_link = isset($fixed_link->value)?json_decode($fixed_link->value, true):null)
    <!-- ==== Preloader ==== -->
    <div id="landing-loader"></div>
    <!-- ==== Preloader ==== -->
    <!-- ==== Header Section Starts Here ==== -->
    <header class="header-bolt">
        <div class="navbar-bottom">
            <div class="container">
                <div class="navbar-bottom-wrapper">
                    <!-- Logo -->
                    <a href="{{route('home')}}" class="logo">
                        <img class="onerror-image" data-onerror-image="{{ asset('assets/admin/img/160x160/img2.jpg') }}"
                            src="{{ \App\CentralLogics\Helpers::logoFullUrl()}}" alt="image">
                    </a>

                    <!-- Right Action Elements (Bolt Style) -->
                    <div class="header-right-actions ms-auto">
                        <!-- Idioma (ES con Bandera de México) -->
                        <a href="javascript:void(0)" class="action-link action-link-lang">
                            <span>🇲🇽</span> ES
                        </a>

                        <!-- Soporte (Redirige a contacto) -->
                        <a href="{{ route('contact-us') }}" class="action-link action-link-support">
                            Soporte
                        </a>

                        <!-- Crece con Tootli -->
                        <a href="{{ route('crece') }}" class="action-link action-link-crece" style="color: var(--tl-primary) !important; font-weight: 700;">
                            🚀 Crece con Tootli
                        </a>

                        <!-- Registrarme (Pill Button) -->
                        <a href="javascript:void(0)" class="btn-pill-white" id="btn-registrarme">
                            Registrarme
                        </a>

                        <!-- Botón de Menú Hamburguesa -->
                        <button type="button" class="btn-hamburger" id="tootli-drawer-trigger">
                            <i class="fas fa-bars"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <!-- ==== Header Section Ends Here ==== -->

    <!-- ==== Drawer Backdrop ==== -->
    <div class="tootli-drawer-backdrop" id="tootli-drawer-backdrop"></div>

    <!-- ==== Drawer Menu (Panel Hamburguesa) ==== -->
    <div class="tootli-drawer" id="tootli-drawer">
        <button class="tootli-drawer-close" id="tootli-drawer-close">
            <i class="fas fa-times"></i>
        </button>

        <div class="tootli-drawer-nav">
            <div class="tootli-drawer-logo">
                <img src="{{ \App\CentralLogics\Helpers::logoFullUrl()}}" alt="Tootli" height="40" style="filter: brightness(0) invert(1);">
            </div>

            <ul class="tootli-drawer-menu">
                <li><a href="#home" class="drawer-nav-link">Inicio</a></li>
                <li><a href="#beneficios" class="drawer-nav-link">Beneficios</a></li>
                <li><a href="#categorias" class="drawer-nav-link">Categorías</a></li>
                <li><a href="#aliados" class="drawer-nav-link">Aliados</a></li>
                <li><a href="{{route('about-us')}}">Acerca de Nosotros</a></li>
                <li><a href="{{route('contact-us')}}">Soporte y Ayuda</a></li>
                <li style="margin-top: 10px;">
                    <a href="{{ route('crece') }}" style="color: var(--tl-primary) !important; display: flex; align-items: center; gap: 8px;">
                        🚀 Crece con Tootli
                    </a>
                </li>
                
                <!-- Opciones de Registro Dinámicas -->
                <li style="margin-top: 15px; border-top: 1px solid rgba(255,255,255,0.08); padding-top: 15px;">
                    <a href="{{ route('restaurant.create') }}" style="color: var(--tl-primary) !important;">
                        <i class="fas fa-store me-2"></i> Registro de Negocio
                    </a>
                </li>
                <li>
                    <a href="{{ route('deliveryman.create') }}" style="color: #F8A629 !important;">
                        <i class="fas fa-motorcycle me-2"></i> Registro de Repartidor
                    </a>
                </li>
            </ul>
        </div>

        <div class="tootli-drawer-footer">
            <div class="tootli-drawer-socials">
                @php($social_media = \App\Models\SocialMedia::where('status', 1)->get())
                @foreach ($social_media as $social)
                    <a href="{{ $social->link }}" target="_blank">
                        <i class="fab fa-{{ strtolower($social->name) }}"></i>
                    </a>
                @endforeach
            </div>
            <p class="tootli-drawer-copy">&copy; {{ date('Y') }} {{ $business_name }}. Todos los derechos reservados.</p>
        </div>
    </div>
    @yield('content')
    <!-- ======= Footer Section ======= -->

    <footer class="footer-2024">
        <div class="container-custom">
            <div class="row g-4">
                <div class="col-lg-4">
                    <img src="{{ \App\CentralLogics\Helpers::logoFullUrl()}}" alt="Tootli" height="40" class="mb-4" style="filter: brightness(0) invert(1);">
                    <p style="color: #AAA;">{{ $business_name }} - Lo hecho en México esta bien hecho!.</p>
                    <div class="d-flex gap-3 mt-4">
                        @php($social_media = \App\Models\SocialMedia::where('status', 1)->get())
                        @foreach ($social_media as $social)
                            <a href="{{ $social->link }}" target="_blank" style="color: white; font-size: 20px;">
                                <i class="fab fa-{{ strtolower($social->name) }}"></i>
                            </a>
                        @endforeach
                    </div>
                </div>
                <div class="col-lg-2">
                    <h5 class="text-white mb-4">Compañía</h5>
                    <a href="{{route('about-us')}}" class="footer-link">{{ translate('messages.about_us') }}</a>
                    <a href="{{route('contact-us')}}" class="footer-link">{{ translate('messages.contact_us') }}</a>
                </div>
                <div class="col-lg-2">
                    <h5 class="text-white mb-4">Legal</h5>
                    <a href="{{route('privacy-policy')}}" class="footer-link">{{ translate('messages.privacy_policy') }}</a>
                    <a href="{{route('terms-and-conditions')}}" class="footer-link">{{ translate('messages.terms_and_condition') }}</a>
                    @if (isset($landing_data['refund_policy_status']) && $landing_data['refund_policy_status'] == 1)
                        <a href="{{route('refund')}}" class="footer-link">{{ translate('messages.Refund Policy') }}</a>
                    @endif
                </div>
                <div class="col-lg-4">
                    <h5 class="text-white mb-4">Descarga la App</h5>
                    <div class="d-flex flex-wrap gap-2">
                        @if (isset($landing_page_links['playstore_url_status']))
                            <a href="{{ $landing_page_links['playstore_url'] ?? '#' }}">
                                <img src="{{ asset('assets/landing/img/google.svg') }}" alt="Google Play" height="40">
                            </a>
                        @endif
                        @if (isset($landing_page_links['apple_store_url_status']))
                            <a href="{{ $landing_page_links['apple_store_url'] ?? '#' }}">
                                <img src="{{ asset('assets/landing/img/apple.svg') }}" alt="App Store" height="40">
                            </a>
                        @endif
                    </div>
                </div>
            </div>
            <hr class="mt-5 mb-4" style="border-color: #333;">
            <div class="d-flex flex-wrap justify-content-between align-items-center text-muted">
                <p class="mb-0">&copy; {{ date('Y') }} {{ $business_name }}. Todos los derechos reservados.</p>
                <p class="mb-0">{{ \App\CentralLogics\Helpers::get_settings('address') }}</p>
            </div>
        </div>
    </footer>
    <!-- ======= Footer Section ======= -->
    <script src="{{ asset('assets/landing/js/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('assets/landing/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/landing/js/viewport.jquery.js') }}"></script>
    <script src="{{ asset('assets/landing/js/wow.min.js') }}"></script>
    <script src="{{ asset('assets/landing/js/odometer.min.js') }}"></script>
    <script src="{{ asset('assets/landing/js/owl.min.js') }}"></script>
    <script src="{{ asset('assets/landing/js/main.js') }}"></script>
    <script src="{{ asset('assets/admin/js/toastr.js') }}"></script>
    {!! Toastr::message() !!}
    @if ($errors->any())
        <script>
            @foreach($errors->all() as $error)
            toastr.error('{{$error}}', Error, {
                CloseButton: true,
                ProgressBar: true
            });
            @endforeach
        </script>
    @endif


    @stack('script_2')

    <script>
        "use strict";
 $(".main-category-slider").owlCarousel({
            loop: true,
            nav: false,
            dots: true,
            items: 1,
            margin: 12,
            autoplay: true,
            rtl: {{ $landing_site_direction === 'rtl'?'true':'false' }},
        });
        $(".testimonial-slider").owlCarousel({
            loop: false,
            margin: 15,
            responsiveClass: true,
            nav: false,
            dots: false,
            autoplay: true,
            autoplayTimeout: 2000,
            autoplayHoverPause: true,
            items: 1,
            rtl: {{ $landing_site_direction === 'rtl'?'true':'false' }},
            responsive: {
                768: {
                    items: 2,
                    margin: 20,
                },
                992: {
                    items: 3,
                    margin: 20,
                },
                1200: {
                    items: 3,
                    margin: 22,
                },
            },
        });
        $(".owl-prev").html('<i class="fas fa-angle-left">');
        $(".owl-next").html('<i class="fas fa-angle-right">');
        let sync1 = $("#sync1");
         let sync2 = $("#sync2");
         let thumbnailItemClass = ".owl-item";
         let slides = sync1
            .owlCarousel({
                // startPosition: 12,
                items: 1,
                loop: false,
                margin: 30,
                mouseDrag: true,
                touchDrag: true,
                pullDrag: false,
                scrollPerPage: true,
                autoplayHoverPause: false,
                nav: false,
                dots: false,
                // center: true,
                rtl: {{ $landing_site_direction === 'rtl'?'true':'false' }},
            })
            .on("changed.owl.carousel", syncPosition);

        function syncPosition(el) {
            let  $owl_slider = $(this).data("owl.carousel");
            let loop = $owl_slider.options.loop;
            let current;
            if (loop) {
                let count = el.item.count - 1;
                 current = Math.round(
                    el.item.index - el.item.count / 2 - 0.5
                );
                if (current < 0) {
                    current = count;
                }
                if (current > count) {
                    current = 0;
                }
            } else {
                 current = el.item.index;
            }

            let owl_thumbnail = sync2.data("owl.carousel");
            let itemClass = "." + owl_thumbnail.options.itemClass;

            let thumbnailCurrentItem = sync2
                .find(itemClass)
                .removeClass("synced")
                .eq(current);
            thumbnailCurrentItem.addClass("synced");

            if (!thumbnailCurrentItem.hasClass("active")) {
                let duration = 500;
                sync2.trigger("to.owl.carousel", [current, duration, true]);
            }
        }

        let thumbs = sync2
            .owlCarousel({
                // startPosition: 12,
                items: 2,
                loop: false,
                margin: 0,
                autoplay: false,
                nav: true,
                navText: ["",""],
                dots: false,
                mouseDrag: true,
                touchDrag: true,
                rtl: {{ $landing_site_direction === 'rtl'?'true':'false' }},
                responsive: {
                    400: {
                        items: 3,
                    },
                    768: {
                        items: 6,
                    },
                    1200: {
                        items: 6,
                    },
                },
                onInitialized: function (e) {
                    let thumbnailCurrentItem = $(e.target)
                        .find(thumbnailItemClass)
                        .eq(this._current);
                    thumbnailCurrentItem.addClass("synced");
                },
            })
            .on("click", thumbnailItemClass, function (e) {
                e.preventDefault();
                let duration = 500;
                let itemIndex = $(e.target).parents(thumbnailItemClass).index();
                sync1.trigger("to.owl.carousel", [itemIndex, duration, true]);
            })
            .on("changed.owl.carousel", function (el) {
                let number = el.item.index;
                let  $owl_slider = sync1.data("owl.carousel");
                $owl_slider.to(number, 500, true);
            });
        sync1.owlCarousel();

    </script>
        <script src="{{asset('assets/admin/intltelinput/js/intlTelInput.min.js')}}"></script>

<script>
            "use strict";
            const inputs = document.querySelectorAll('input[type="tel"]');
            inputs.forEach(input => {
                window.intlTelInput(input, {
                    initialCountry: "{{$countryCode}}",
                    utilsScript: "{{ asset('assets/admin/intltelinput/js/utils.js') }}",
                    autoInsertDialCode: true,
                    nationalMode: false,
                    formatOnDisplay: false,
                });
            });


            function keepNumbersAndPlus(inputString) {
                let regex = /[0-9+]/g;
                let filteredString = inputString.match(regex);
            return filteredString ? filteredString.join('') : '';
            }

            $(document).on('keyup', 'input[type="tel"]', function () {
                $(this).val(keepNumbersAndPlus($(this).val()));
            });

            // Hamburger Drawer Controls
            const drawer = document.getElementById('tootli-drawer');
            const backdrop = document.getElementById('tootli-drawer-backdrop');
            const trigger = document.getElementById('tootli-drawer-trigger');
            const closeBtn = document.getElementById('tootli-drawer-close');
            const registrarmeBtn = document.getElementById('btn-registrarme');
            const drawerLinks = document.querySelectorAll('.drawer-nav-link');

            function openDrawer() {
                drawer.classList.add('open');
                backdrop.classList.add('show');
                document.body.style.overflow = 'hidden';
            }

            function closeDrawer() {
                drawer.classList.remove('open');
                backdrop.classList.remove('show');
                document.body.style.overflow = '';
            }

            if (trigger) trigger.addEventListener('click', openDrawer);
            if (closeBtn) closeBtn.addEventListener('click', closeDrawer);
            if (backdrop) backdrop.addEventListener('click', closeDrawer);
            if (registrarmeBtn) registrarmeBtn.addEventListener('click', openDrawer);

            drawerLinks.forEach(link => {
                link.addEventListener('click', closeDrawer);
            });
</script>

</body>

</html>
