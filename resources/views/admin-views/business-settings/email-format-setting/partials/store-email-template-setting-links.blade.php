<div class="d-flex flex-wrap justify-content-between align-items-center mb-5 mt-4 __gap-12px">
    <div class="js-nav-scroller hs-nav-scroller-horizontal mt-2">
        <!-- Nav -->
        <ul class="nav nav-tabs border-0 nav--tabs nav--pills">
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/email-setup/store/registration') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.email-setup', ['store','registration']) }}">
                    {{'Registro de nueva tienda'}}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/email-setup/store/approve') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.email-setup', ['store','approve']) }}">
                    {{'Aprobación de nueva tienda'}}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/email-setup/store/deny') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.email-setup', ['store','deny']) }}">
                    {{'Rechazo de nueva tienda'}}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/email-setup/store/suspend') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.email-setup', ['store','suspend']) }}">
                    {{'Suspensión de cuenta'}}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/email-setup/store/unsuspend') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.email-setup', ['store','unsuspend']) }}">
                    {{'Cuenta suspendida'}}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/email-setup/store/withdraw-approve') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.email-setup', ['store','withdraw-approve']) }}">
                    {{'Retirar aprobación'}}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/email-setup/store/withdraw-deny') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.email-setup', ['store','withdraw-deny']) }}">
                    {{'Retirar el rechazo'}}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/email-setup/store/campaign-request') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.email-setup', ['store','campaign-request']) }}">
                    {{'Solicitud de unión a la campaña'}}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/email-setup/store/campaign-approve') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.email-setup', ['store','campaign-approve']) }}">
                    {{'Aprobación de unirse a la campaña'}}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/email-setup/store/campaign-deny') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.email-setup', ['store','campaign-deny']) }}">
                    {{'Rechazo de unirse a la campaña'}}
                </a>
            </li>

            @if (\App\CentralLogics\Helpers::get_mail_status('product_approval'))
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/email-setup/store/product-approved') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.email-setup', ['store','product-approved']) }}">
                    {{'Producto aprobado'}}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/email-setup/store/product-deny') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.email-setup', ['store','product-deny']) }}">
                    {{'Rechazo de producto'}}
                </a>
            </li>
            @endif

            @if (\App\CentralLogics\Helpers::subscription_check())
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/email-setup/store/subscription-successful') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.email-setup', ['store','subscription-successful']) }}">
                    {{'Suscripción exitosa'}}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/email-setup/store/subscription-renew') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.email-setup', ['store','subscription-renew']) }}">
                    {{'Renovación de suscripción'}}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/email-setup/store/subscription-shift') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.email-setup', ['store','subscription-shift']) }}">
                    {{'Cambio de suscripción'}}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/email-setup/store/subscription-cancel') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.email-setup', ['store','subscription-cancel']) }}">
                    {{'Cancelar suscripción'}}
                </a>
            </li>
            {{-- <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/email-setup/store/subscription-deadline') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.email-setup', ['store','subscription-deadline']) }}">
                    {{'Advertencia de fecha límite de suscripción'}}
                </a>
            </li> --}}
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/email-setup/store/subscription-plan_upadte') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.email-setup', ['store','subscription-plan_upadte']) }}">
                    {{'Actualización del plan de suscripción'}}
                </a>
            </li>
            @endif

            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/email-setup/store/advertisement-create') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.email-setup', ['store','advertisement-create']) }}">
                    {{'Anuncio creado por administrador'}}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/email-setup/store/advertisement-approved') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.email-setup', ['store','advertisement-approved']) }}">
                    {{'Aprobación del anuncio'}}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/email-setup/store/advertisement-deny') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.email-setup', ['store','advertisement-deny']) }}">
                    {{'Denegar anuncio'}}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/email-setup/store/advertisement-resume') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.email-setup', ['store','advertisement-resume']) }}">
                    {{'Currículum publicitario'}}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/email-setup/store/advertisement-pause') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.email-setup', ['store','advertisement-pause']) }}">
                    {{'Pausa publicitaria'}}
                </a>
            </li>
        </ul>
        <!-- End Nav -->
    </div>
</div>
