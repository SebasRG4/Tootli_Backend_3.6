<div class="d-flex flex-wrap justify-content-between align-items-center mb-5 mt-4 __gap-12px">
    <div class="js-nav-scroller hs-nav-scroller-horizontal mt-2">
        <!-- Nav -->
        <ul class="nav nav-tabs border-0 nav--tabs nav--pills">
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/email-setup/admin/forgot-password') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.email-setup', ['admin','forgot-password']) }}">
                    {{'Has olvidado tu contraseña'}}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/email-setup/admin/store-registration') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.email-setup', ['admin','store-registration']) }}">
                    {{'Registro de nueva tienda'}}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/email-setup/admin/dm-registration') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.email-setup', ['admin','dm-registration']) }}">
                    {{'Registro de nuevo repartidor'}}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/email-setup/admin/withdraw-request') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.email-setup', ['admin','withdraw-request']) }}">
                    {{'Solicitud de retiro'}}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/email-setup/admin/dm-withdraw-request') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.email-setup', ['admin','dm-withdraw-request']) }}">
                    {{'Solicitud de retiro del repartidor'}}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/email-setup/admin/campaign-request') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.email-setup', ['admin','campaign-request']) }}">
                    {{'Solicitud de unión a la campaña'}}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/email-setup/admin/refund-request') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.email-setup', ['admin','refund-request']) }}">
                    {{'Solicitud de reembolso'}}
                </a>
            </li>
            {{-- <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/email-setup/admin/login') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.email-setup', ['admin','login']) }}">
                    {{'Correo de inicio de sesión'}}
                </a>
            </li> --}}
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/email-setup/admin/new-advertisement') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.email-setup', ['admin','new-advertisement']) }}">
                    {{'Nueva solicitud de publicidad'}}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/email-setup/admin/update-advertisement') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.email-setup', ['admin','update-advertisement']) }}">
                    {{'Solicitud de actualización de anuncio'}}
                </a>
            </li>
        </ul>
        <!-- End Nav -->
    </div>
</div>
