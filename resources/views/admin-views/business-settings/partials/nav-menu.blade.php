<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 mt-3 __gap-12px">
    <div class="js-nav-scroller hs-nav-scroller-horizontal mt-2">
        <!-- Nav -->
        <ul class="nav nav-tabs border-0 nav--tabs nav--pills">
            <li class="nav-item">
                <a class="nav-link  {{ Request::is('admin/business-settings/business-setup') ? 'active' : '' }}"
                    href="{{ route('admin.business-settings.business-setup') }}"
                    aria-disabled="true">{{'información comercial'}}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/business-setup/order') ? 'active' : '' }}"
                    href="{{ route('admin.business-settings.business-setup', ['tab' => 'order']) }}"
                    aria-disabled="true">{{'configuración del pedido'}}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/business-setup/refund-settings') ? 'active' : '' }}"
                    href="{{ route('admin.business-settings.business-setup', ['tab' => 'refund-settings']) }}"
                    aria-disabled="true">{{'configuración de reembolso'}}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/business-setup/store') ? 'active' : '' }}"
                    href="{{ route('admin.business-settings.business-setup', ['tab' => 'store']) }}"
                    aria-disabled="true">{{'Proveedor'}}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/business-setup/deliveryman') ? 'active' : '' }}"
                    href="{{ route('admin.business-settings.business-setup', ['tab' => 'deliveryman']) }}"
                    aria-disabled="true">{{'Repartidor'}}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/business-setup/customer') ? 'active' : '' }}"
                    href="{{ route('admin.business-settings.business-setup', ['tab' => 'customer']) }}"
                    aria-disabled="true">{{'Clientes'}}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/business-setup/priority') ? 'active' : '' }}"
                    href="{{ route('admin.business-settings.business-setup', ['tab' => 'priority']) }}"
                    aria-disabled="true">{{'configuración de prioridad'}}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/language') ? 'active' : '' }}"
                    href="{{route('admin.business-settings.language.index')}}"
                    aria-disabled="true">{{'Idiomas'}}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/business-setup/landing-page') ? 'active' : '' }}"
                    href="{{ route('admin.business-settings.business-setup', ['tab' => 'landing-page']) }}"
                    aria-disabled="true">{{'página de destino'}}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/business-setup/websocket') ? 'active' : '' }}"
                    href="{{ route('admin.business-settings.business-setup', ['tab' => 'websocket']) }}"
                    aria-disabled="true">{{'enchufe web'}}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/business-setup/disbursement') ? 'active' : '' }}"
                    href="{{ route('admin.business-settings.business-setup', ['tab' => 'disbursement']) }}"
                    aria-disabled="true">{{'desembolso'}}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/business-setup/automated-message') ? 'active' : '' }}"
                    href="{{ route('admin.business-settings.business-setup', ['tab' => 'automated-message']) }}"
                    aria-disabled="true">{{'Mensaje automatizado'}}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/business-setup/dynamic-pricing') ? 'active' : '' }}"
                    href="{{ route('admin.business-settings.business-setup', ['tab' => 'dynamic-pricing']) }}"
                    aria-disabled="true">Tarifa dinámica</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('admin/business-settings/business-setup/to-do-list') ? 'active' : '' }}"
                    href="{{ route('admin.business-settings.business-setup', ['tab' => 'to-do-list']) }}"
                    aria-disabled="true">To do list</a>
            </li>
        </ul>
        <!-- End Nav -->
    </div>
    <!-- @if (!(Request::is('admin/business-settings/language') || Request::is('admin/business-settings/business-setup/refund-settings') || Request::is('admin/business-settings/business-setup/automated-message')))
    <div class="d-flex flex-wrap justify-content-end align-items-center flex-grow-1">
        <div class="blinkings active">
            <i class="tio-info-outined"></i>
            <div class="business-notes">
                <h6><img src="{{asset('assets/admin/img/notes.png')}}" alt=""> {{'Nota'}}</h6>
                <div>
                    @if (Request::is('admin/business-settings/business-setup/refund-settings'))
                    {{ '*Si el administrador habilita el "Modo de solicitud de reembolso", los clientes pueden solicitar un reembolso.' }}
                    @else
                    {{'No olvide hacer clic en el botón "Guardar información" a continuación para guardar los cambios.'}}
                    @endif
                    </div>
                </div>
            </div>
        </div>
    @endif -->
</div>