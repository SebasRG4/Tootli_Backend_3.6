<div class="d-flex flex-wrap justify-content-between align-items-center mb-5 mt-4 __gap-12px">
    <div class="js-nav-scroller hs-nav-scroller-horizontal mt-2">
        <!-- Nav -->
        <ul class="nav nav-tabs border-0 nav--tabs nav--pills">
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/email-setup/user/registration') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.email-setup', ['user','registration']) }}">
                    {{'Registro de nuevo cliente'}}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/email-setup/user/pos-registration') ? 'active' : '' }}"
                   href="{{ route('admin.business-settings.email-setup', ['user','pos-registration']) }}">
                    {{'POS Registro de nuevos clientes'}}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/email-setup/user/registration-otp') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.email-setup', ['user','registration-otp']) }}">
                    {{'OTP de registro'}}
                </a>
            </li>
            {{-- <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/email-setup/user/login-otp') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.email-setup', ['user','login-otp']) }}">
                    {{'Iniciar sesión OTP'}}
                </a>
            </li> --}}
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/email-setup/user/forgot-password') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.email-setup', ['user','forgot-password']) }}">
                    {{'Has olvidado tu contraseña'}}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/email-setup/user/order-verification') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.email-setup', ['user','order-verification']) }}">
                    {{'Verificación de entrega'}}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/email-setup/user/new-order') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.email-setup', ['user','new-order']) }}">{{'Colocación de pedidos'}}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/email-setup/user/refund-order') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.email-setup', ['user','refund-order']) }}">{{'orden de reembolso'}}</a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/email-setup/user/refund-request-deny') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.email-setup', ['user','refund-request-deny']) }}">
                    {{'Solicitud de reembolso rechazada'}}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/email-setup/user/add-fund') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.email-setup', ['user','add-fund']) }}">
                    {{'Añadir fondo'}}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/email-setup/user/offline-payment-approve') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.email-setup', ['user','offline-payment-approve']) }}">
                    {{'Aprobación de pago sin conexión'}}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/email-setup/user/offline-payment-deny') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.email-setup', ['user','offline-payment-deny']) }}">
                    {{'Denegación de pago sin conexión'}}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/email-setup/user/suspend') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.email-setup', ['user','suspend']) }}">
                    {{'Suspensión de cuenta'}}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/email-setup/user/unsuspend') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.email-setup', ['user','unsuspend']) }}">
                    {{'Dessuspensión de cuenta'}}
                </a>
            </li>
        </ul>
        <!-- End Nav -->
    </div>
</div>
