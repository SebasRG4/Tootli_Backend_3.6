<div class="d-flex flex-wrap justify-content-between align-items-center mb-20 __gap-12px">
    <div class="js-nav-scroller hs-nav-scroller-horizontal mt-2">
        <!-- Nav -->
        <ul class="nav nav-tabs border-0 nav--tabs nav--pills">
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/pages/admin-landing-page-settings/fixed-data') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.admin-landing-page-settings', 'fixed-data') }}">{{'datos fijos'}}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/pages/admin-landing-page-settings/promotional-section*') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.admin-landing-page-settings', 'promotional-section') }}">{{'sección promocional'}}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/pages/admin-landing-page-settings/feature-list*') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.admin-landing-page-settings', 'feature-list') }}">{{'lista de características'}}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/pages/admin-landing-page-settings/earn-money') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.admin-landing-page-settings', 'earn-money') }}">{{'ganar dinero'}}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/pages/admin-landing-page-settings/why-choose-us*') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.admin-landing-page-settings', 'why-choose-us') }}">{{'¿Por qué elegirnos?'}}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/pages/admin-landing-page-settings/available-zone*') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.admin-landing-page-settings', 'available-zone') }}">{{'zona disponible'}}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/pages/admin-landing-page-settings/download-apps') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.admin-landing-page-settings', 'download-apps') }}">{{'descargar aplicaciones'}}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/pages/admin-landing-page-settings/testimonials*') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.admin-landing-page-settings', 'testimonials') }}">{{'testimonios'}}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/pages/admin-landing-page-settings/contact-us') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.admin-landing-page-settings', 'contact-us') }}">{{'contáctenos página'}}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/pages/admin-landing-page-settings/background-color') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.admin-landing-page-settings', 'background-color') }}">{{'colores de fondo'}}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/pages/admin-landing-page-settings/meta-data') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.admin-landing-page-settings', 'meta-data') }}">{{'metadatos'}}</a>
            </li>
        </ul>
        <!-- End Nav -->
    </div>
</div>
