<div class="d-flex flex-wrap justify-content-between align-items-center mb-5 mt-4 __gap-12px">
    <div class="js-nav-scroller hs-nav-scroller-horizontal mt-2">
        <!-- Nav -->
        <ul class="nav nav-tabs border-0 nav--tabs nav--pills">
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/email-setup/dm/registration') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.email-setup', ['dm','registration']) }}">
                {{'Nuevo Registro de Repartidor'}}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/email-setup/dm/approve') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.email-setup', ['dm','approve']) }}">
                {{'Aprobación de nuevo repartidor'}}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/email-setup/dm/deny') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.email-setup', ['dm','deny']) }}">
                {{'Rechazo del nuevo repartidor'}}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/email-setup/dm/suspend') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.email-setup', ['dm','suspend']) }}">
                    {{'Suspensión de cuenta'}}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/email-setup/dm/unsuspend') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.email-setup', ['dm','unsuspend']) }}">
                    {{'Dessuspensión de cuenta'}}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/email-setup/dm/cash-collect') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.email-setup', ['dm','cash-collect']) }}">
                    {{'Cobro en efectivo'}}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/email-setup/dm/forgot-password') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.email-setup', ['dm','forgot-password']) }}">
                    {{'Has olvidado tu contraseña'}}
                </a>
            </li>
              <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/email-setup/dm/withdraw-approve') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.email-setup', ['dm','withdraw-approve']) }}">
                    {{'Retirar aprobación'}}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/email-setup/dm/withdraw-deny') ? 'active' : '' }}"
                href="{{ route('admin.business-settings.email-setup', ['dm','withdraw-deny']) }}">
                    {{'Retirar el rechazo'}}
                </a>
            </li>
        </ul>
        <!-- End Nav -->
    </div>
</div>
