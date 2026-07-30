<div class="d-flex flex-wrap justify-content-between align-items-center tabs-slide-wrap mb-20 __gap-12px">
    <div class="js-nav-scroller hs-nav-scroller-horizontal mt-2">
        <!-- Nav -->
        <ul class="nav nav-tabs tabs-inner border-0 nav--tabs nav--pills">
            <li class="nav-item tabs-slide_items">
                <a class="nav-link {{ Request::is('admin/business-settings/pages/react-landing-page-settings/header') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.react-landing-page-settings', 'header') }}">{{'Sección de héroe'}}</a>
            </li>
            <li class="nav-item tabs-slide_items">
                <a class="nav-link {{ Request::is('admin/business-settings/pages/react-landing-page-settings/trust-section') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.react-landing-page-settings', 'trust-section') }}">{{'Sección de confianza'}}</a>
            </li>
            <li class="nav-item tabs-slide_items">
                <a class="nav-link {{ Request::is('admin/business-settings/pages/react-landing-page-settings/available-zone') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.react-landing-page-settings', 'available-zone') }}">{{'zona disponible'}}</a>
            </li>
            <li class="nav-item tabs-slide_items">
                <a class="nav-link {{ Request::is('admin/business-settings/pages/react-landing-page-settings/promotion-banner') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.react-landing-page-settings', 'promotion-banner') }}">{{'pancartas promocionales'}}</a>
            </li>
            <li class="nav-item tabs-slide_items">
                <a class="nav-link {{ Request::is('admin/business-settings/pages/react-landing-page-settings/download-user-app') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.react-landing-page-settings', 'download-user-app') }}">{{'Descarga de la aplicación del usuario'}}</a>
            </li>
            <li class="nav-item tabs-slide_items">
                <a class="nav-link {{ Request::is('admin/business-settings/pages/react-landing-page-settings/popular-clients') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.react-landing-page-settings', 'popular-clients') }}">{{'Clientes populares'}}</a>
            </li>
            <li class="nav-item tabs-slide_items">
                <a class="nav-link {{ Request::is('admin/business-settings/pages/react-landing-page-settings/download-seller-app') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.react-landing-page-settings', 'download-seller-app') }}">{{'Descarga de la aplicación del vendedor'}}</a>
            </li>
            <li class="nav-item tabs-slide_items">
                <a class="nav-link {{ Request::is('admin/business-settings/pages/react-landing-page-settings/download-deliveryman-app') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.react-landing-page-settings', 'download-deliveryman-app') }}">{{'Descarga de la aplicación Repartidor'}}</a>
            </li>
            <li class="nav-item tabs-slide_items">
                <a class="nav-link {{ Request::is('admin/business-settings/pages/react-landing-page-settings/banner-section') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.react-landing-page-settings', 'banner-section') }}">{{'Sección de pancartas'}}</a>
            </li>
            <li class="nav-item tabs-slide_items">
                <a class="nav-link {{ Request::is('admin/business-settings/pages/react-landing-page-settings/testimonials*') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.react-landing-page-settings', 'testimonials') }}">{{'testimonios'}}</a>
            </li>
            <li class="nav-item tabs-slide_items">
                <a class="nav-link {{ Request::is('admin/business-settings/pages/react-landing-page-settings/gallery') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.react-landing-page-settings', 'gallery') }}">{{'Galería'}}</a>
            </li>
            <li class="nav-item tabs-slide_items">
                <a class="nav-link {{ Request::is('admin/business-settings/pages/react-landing-page-settings/highlight-section') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.react-landing-page-settings', 'highlight-section') }}">{{'Sección destacada'}}</a>
            </li>
            <li class="nav-item tabs-slide_items">
                <a class="nav-link {{ Request::is('admin/business-settings/pages/react-landing-page-settings/faq') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.react-landing-page-settings', 'faq') }}">{{'Preguntas frecuentes'}}</a>
            </li>
            <li class="nav-item tabs-slide_items">
                <a class="nav-link {{ Request::is('admin/business-settings/pages/react-landing-page-settings/footer') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.react-landing-page-settings', 'footer') }}">{{'Pie de página'}}</a>
            </li>
{{--            <li class="nav-item tabs-slide_items">--}}
{{--                <a class="nav-link {{ Request::is('admin/business-settings/pages/react-landing-page-settings/company-intro') ? 'active' : '' }}"--}}
{{--                href="{{ route('admin.business-settings.react-landing-page-settings', 'company-intro') }}">{{'Introducción de la empresa'}}</a>--}}
{{--            </li>--}}
{{--            <li class="nav-item tabs-slide_items">--}}
{{--                <a class="nav-link {{ Request::is('admin/business-settings/pages/react-landing-page-settings/earn-money') ? 'active' : '' }}"--}}
{{--                href="{{ route('admin.business-settings.react-landing-page-settings', 'earn-money') }}">{{'ganar dinero'}}</a>--}}
{{--            </li>--}}
{{--            <li class="nav-item tabs-slide_items">--}}
{{--                <a class="nav-link {{ Request::is('admin/business-settings/pages/react-landing-page-settings/business-section') ? 'active' : '' }}"--}}
{{--                href="{{ route('admin.business-settings.react-landing-page-settings', 'business-section') }}">{{'Sección de Negocios'}}</a>--}}
{{--            </li>--}}
            <li class="nav-item tabs-slide_items">
                <a class="nav-link {{ Request::is('admin/business-settings/pages/react-landing-page-settings/meta-data') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.react-landing-page-settings', 'meta-data') }}">{{'metadatos'}}</a>
            </li>
        </ul>
        <!-- End Nav -->
    </div>
    <div class="arrow-area">
        <div class="button-prev align-items-center">
            <button type="button"
                class="btn btn-click-prev mr-auto border-0 btn-primary rounded-circle fs-12 p-2 d-center">
                <i class="tio-chevron-left fs-24"></i>
            </button>
        </div>
        <div class="button-next align-items-center">
            <button type="button"
                class="btn btn-click-next ml-auto border-0 btn-primary rounded-circle fs-12 p-2 d-center">
                <i class="tio-chevron-right fs-24"></i>
            </button>
        </div>
    </div>
</div>
