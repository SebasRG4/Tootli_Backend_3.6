
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
    <link rel="stylesheet" href="{{ asset('assets/landing/css/tootli-landing-2024.css') }}"/>
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
    <header>
        <div class="navbar-bottom">
            <div class="container">
                <div class="navbar-bottom-wrapper">

                    <a href="{{route('home')}}" class="logo">
                        <img class="onerror-image"  data-onerror-image="{{ asset('assets/admin/img/160x160/img2.jpg') }}"

                    src="{{ \App\CentralLogics\Helpers::logoFullUrl()}}"

                    alt="image">
                    </a>
                    <ul class="menu">
                        <li>
                            <a href="#home" class="nav-link-custom"><span>Inicio</span></a>
                        </li>
                        <li>
                            <a href="#beneficios" class="nav-link-custom"><span>Beneficios</span></a>
                        </li>
                        <li>
                            <a href="#categorias" class="nav-link-custom"><span>Categorías</span></a>
                        </li>
                        <li>
                            <a href="#aliados" class="nav-link-custom"><span>Aliados</span></a>
                        </li>
                        <li>
                            <a href="{{route('about-us')}}" class="nav-link-custom"><span>Acerca de Nosotros</span></a>
                        </li>
                    </ul>
                    <div class="nav-toggle d-lg-none ms-auto me-3">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                    @php( $local = session()->has('landing_local')?session('landing_local'):null)
                    @php($lang = \App\CentralLogics\Helpers::get_business_settings('system_language') )
                    @if ($lang)
                        <div class="dropdown--btn-hover position-relative">
                            <a class="dropdown--btn border-0 px-3 header--btn text-capitalize d-flex align-items-center" href="javascript:void(0)">
                                <span class="me-1">ES</span>
                            </a>
                        </div>
                    @endif
                    @if ($fixed_link &&$fixed_link['web_app_url_status'])
                        <div class="me-2 d-none d-lg-block">
                            <a class="cmn--btn me-xl-auto py-2" href="{{ $fixed_link['web_app_url'] }}" target="_blank">{{ translate('messages.browse_web') }}</a>
                        </div>
                    @endif
                    @if (isset($toggle_dm_registration) || isset($toggle_store_registration))
                    <div class="dropdown--btn-hover position-relative">
                        <a class="dropdown--btn header--btn text-capitalize d-flex align-items-center" href="javascript:void(0)">
                            <span class="me-1">Únete</span>
                            <svg width="12" height="7" viewBox="0 0 12 7" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M6.00224 5.46105L1.33333 0.415128C1.21002 0.290383 1 0.0787335 1 0.0787335C1 0.0787335 0.708488 -0.0458817 0.584976 0.0788632L0.191805 0.475841C0.0680976 0.600389 7.43292e-08 0.766881 7.22135e-08 0.9443C7.00978e-08 1.12172 0.0680976 1.28801 0.191805 1.41266L5.53678 6.80682C5.66068 6.93196 5.82624 7.00049 6.00224 7C6.17902 7.00049 6.34439 6.93206 6.46839 6.80682L11.8082 1.41768C11.9319 1.29303 12 1.12674 12 0.949223C12 0.771804 11.9319 0.605509 11.8082 0.480765L11.415 0.0838844C11.1591 -0.174368 10.9225 0.222512 10.6667 0.480765L6.00224 5.46105Z"
                                    fill="rgba(0,0,0,0.6)" />
                            </svg>
                        </a>

                        <ul class="dropdown-list">
                            @if ($toggle_store_registration)
                            <li>
                                <a class="" href="{{ route('restaurant.create') }}">
                                    Registro de Negocio
                                </a>
                            </li>
                            @if ($toggle_dm_registration)
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                            @endif
                        @endif
                        @if ($toggle_dm_registration)
                            <li><a class=""
                                    href="{{ route('deliveryman.create') }}">Registro de Repartidor</a>
                            </li>
                        @endif
                        </ul>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </header>
    <!-- ==== Header Section Ends Here ==== -->
    @yield('content')
    <!-- ======= Footer Section ======= -->

    <footer class="footer-2024">
        <div class="container-custom">
            <div class="row g-4">
                <div class="col-lg-4">
                    <img src="{{ \App\CentralLogics\Helpers::logoFullUrl()}}" alt="Tootli" height="40" class="mb-4" style="filter: brightness(0) invert(1);">
                    <p style="color: #AAA;">{{ $business_name }} - Conectando tu ciudad, un pedido a la vez.</p>
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


</script>

</body>

</html>
