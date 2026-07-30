<div class="d-flex flex-wrap justify-content-between align-items-center mb-20 __gap-12px">
    <div class="js-nav-scroller hs-nav-scroller-horizontal mt-2">
        <!-- Nav -->
        <ul class="nav nav-tabs border-0 nav--tabs nav--pills">
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/pages/flutter-landing-page-settings/fixed-data') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.flutter-landing-page-settings', 'fixed-data') }}">{{'datos fijos'}}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/pages/flutter-landing-page-settings/special-criteria*') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.flutter-landing-page-settings', 'special-criteria') }}">{{'criterios especiales'}}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/pages/flutter-landing-page-settings/available-zone*') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.flutter-landing-page-settings', 'available-zone') }}">{{'zona disponible'}}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/pages/flutter-landing-page-settings/join-as') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.flutter-landing-page-settings', 'join-as') }}">{{'unirse como'}}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/pages/flutter-landing-page-settings/download-apps') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.flutter-landing-page-settings', 'download-apps') }}">{{'descargar aplicaciones'}}</a>
            </li>
        </ul>
        <!-- End Nav -->
    </div>
</div>
