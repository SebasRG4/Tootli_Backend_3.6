<ul class="nav nav-tabs page-header-tabs flex-wrap __nav-tabs-menu">
    <li class="nav-item">
        <a class="nav-link  {{ Request::is('admin/business-settings/landing-page-settings/index') ? 'active' : '' }}"
            href="{{ route('admin.business-settings.landing-page-settings', 'index') }}">{{ 'texto' }}</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ Request::is('admin/business-settings/landing-page-settings/links') ? 'active' : '' }}"
            href="{{ route('admin.business-settings.landing-page-settings', 'links') }}"
            aria-disabled="true">{{ 'enlaces de botones' }}</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ Request::is('admin/business-settings/landing-page-settings/speciality') ? 'active' : '' }}"
            href="{{ route('admin.business-settings.landing-page-settings', 'speciality') }}"
            aria-disabled="true">{{ 'especialidad' }}</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ Request::is('admin/business-settings/landing-page-settings/joinas') ? 'active' : '' }}"
            href="{{ route('admin.business-settings.landing-page-settings', 'joinas') }}"
            aria-disabled="true">{{ 'unirse como' }}</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ Request::is('admin/business-settings/landing-page-settings/download-section') ? 'active' : '' }}"
            href="{{ route('admin.business-settings.landing-page-settings', 'download-section') }}"
            aria-disabled="true">{{ 'descargar la sección de aplicaciones' }}</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ Request::is('admin/business-settings/landing-page-settings/promotion-banner') ? 'active' : '' }}"
            href="{{ route('admin.business-settings.landing-page-settings', 'promotion-banner') }}"
            aria-disabled="true">{{ 'pancarta de promoción' }}</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ Request::is('admin/business-settings/landing-page-settings/testimonial') ? 'active' : '' }}"
            href="{{ route('admin.business-settings.landing-page-settings', 'testimonial') }}"
            aria-disabled="true">{{ 'testimonial' }}</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ Request::is('admin/business-settings/landing-page-settings/feature') ? 'active' : '' }}"
            href="{{ route('admin.business-settings.landing-page-settings', 'feature') }}"
            aria-disabled="true">{{ 'característica' }}</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ Request::is('admin/business-settings/landing-page-settings/image') ? 'active' : '' }}"
            href="{{ route('admin.business-settings.landing-page-settings', 'image') }}"
            aria-disabled="true">{{ 'imagen' }}</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ Request::is('admin/business-settings/landing-page-settings/background-change') ? 'active' : '' }}"
            href="{{ route('admin.business-settings.landing-page-settings', 'background-change') }}"
            aria-disabled="true">{{ 'bandera' }}</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ Request::is('admin/business-settings/landing-page-settings/web-app') ? 'active' : '' }}"
            href="{{ route('admin.business-settings.landing-page-settings', 'web-app') }}"
            aria-disabled="true">{{ 'aplicación web' }}</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ Request::is('admin/business-settings/landing-page-settings/react') ? 'active' : '' }}"
            href="{{ route('admin.business-settings.landing-page-settings', 'react') }}"
            aria-disabled="true">{{ 'reaccionar página de inicio' }}</a>
    </li>
</ul>
