<div class="d-flex flex-wrap justify-content-between align-items-center mb-5 mt-4 __gap-12px">
    <div class="js-nav-scroller hs-nav-scroller-horizontal mt-2">
        <!-- Nav -->
        <ul class="nav nav-tabs border-0 nav--tabs nav--pills">
            <li class="nav-item">
                <a class="nav-link   {{ Request::is('admin/business-settings/third-party/payment-method') ? 'active' : '' }}" href="{{ route('admin.business-settings.third-party.payment-method') }}"   aria-disabled="true">{{'Métodos de pago'}}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/third-party/sms-module') ? 'active' : '' }}" href="{{ route('admin.business-settings.third-party.sms-module') }}"  aria-disabled="true">{{'Módulo SMS'}}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/third-party/mail-config') || Request::is('admin/business-settings/third-party/test-mail')  ? 'active' : '' }}" href="{{ route('admin.business-settings.third-party.mail-config') }}"  aria-disabled="true">{{'Configuración de correo'}}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/third-party/config-setup') ?'active':'' }}" href="{{ route('admin.business-settings.third-party.config-setup') }}"  aria-disabled="true">{{'API de mapas'}}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{Request::is('admin/business-settings/third-party/social-login/view')?'active':''}}" href="{{route('admin.business-settings.third-party.social-login.view')}}"  aria-disabled="true">{{'Inicios de sesión sociales'}}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/third-party/recaptcha*') ? 'active' : '' }}" href="{{route('admin.business-settings.third-party.recaptcha_index')}}"  aria-disabled="true">{{'recaptcha'}}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/third-party/firebase-otp*') ? 'active' : '' }}" href="{{route('admin.business-settings.third-party.firebase_otp_index')}}"  aria-disabled="true">{{'OTP de base de fuego'}}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/third-party/storage-connection*') ? 'active' : '' }}" href="{{route('admin.business-settings.third-party.storage_connection_index')}}"  aria-disabled="true">{{'Conexión de almacenamiento'}}</a>
            </li>
        </ul>
        <!-- End Nav -->
    </div>
</div>
