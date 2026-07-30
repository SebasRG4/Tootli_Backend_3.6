<div id="sidebarMain" class="d-none">
    <aside
        class="js-navbar-vertical-aside navbar navbar-vertical-aside navbar-vertical navbar-vertical-fixed navbar-expand-xl navbar-bordered  ">
        <div class="navbar-vertical-container">
            <div class="navbar-brand-wrapper justify-content-between">
                <!-- Logo -->
                @php($store_logo = \App\Models\BusinessSetting::where(['key' => 'logo'])->first())
                <a class="navbar-brand" href="{{ route('admin.business-settings.business-setup') }}" aria-label="Front">
                    <img class="navbar-brand-logo initial--36 onerror-image onerror-image"
                        data-onerror-image="{{ asset('assets/admin/img/160x160/img2.jpg') }}"
                        src="{{ \App\CentralLogics\Helpers::get_full_url('business', $store_logo?->value ?? '', $store_logo?->storage[0]?->value ?? 'public', 'favicon') }}"
                        alt="Logo">
                    <img class="navbar-brand-logo-mini initial--36 onerror-image onerror-image"
                        data-onerror-image="{{ asset('assets/admin/img/160x160/img2.jpg') }}"
                        src="{{ \App\CentralLogics\Helpers::get_full_url('business', $store_logo?->value ?? '', $store_logo?->storage[0]?->value ?? 'public', 'favicon') }}"
                        alt="Logo">
                </a>
                <!-- End Logo -->

                <!-- Navbar Vertical Toggle -->
                <button type="button"
                    class="js-navbar-vertical-aside-toggle-invoker navbar-vertical-aside-toggle btn btn-icon btn-xs btn-ghost-dark">
                    <i class="tio-clear tio-lg"></i>
                </button>
                <!-- End Navbar Vertical Toggle -->

                <div class="navbar-nav-wrap-content-left">
                    <!-- Navbar Vertical Toggle -->
                    <button type="button" class="js-navbar-vertical-aside-toggle-invoker close">
                        <i class="tio-first-page navbar-vertical-aside-toggle-short-align" data-toggle="tooltip"
                            data-placement="right" title="Collapse"></i>
                        <i class="tio-last-page navbar-vertical-aside-toggle-full-align"
                            data-template='<div class="tooltip d-none d-sm-block" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>'></i>
                    </button>
                    <!-- End Navbar Vertical Toggle -->
                </div>

            </div>

            <!-- Content -->
            <div class="navbar-vertical-content bg--005555" id="navbar-vertical-content">
                <form autocomplete="off" class="sidebar--search-form">
                    <div class="search--form-group">
                        <button type="button" class="btn"><i class="tio-search"></i></button>
                        <input autocomplete="false" name="qq" type="text" class="form-control form--control"
                            placeholder="{{ 'Menú de búsqueda...' }}" id="search">
                        <div id="search-suggestions" class="flex-wrap mt-1"></div>
                    </div>
                </form>
                <ul class="navbar-nav navbar-nav-lg nav-tabs">

                    <!-- Business Settings -->
                    <li class="nav-item">
                        <small class="nav-subtitle"
                            title="{{ 'entornos empresariales' }}">{{ 'gestión empresarial' }}</small>
                        <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                    </li>


                    @if (\App\CentralLogics\Helpers::module_permission_check('zone'))
                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/business-settings/zone*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                                href="{{ route('admin.business-settings.zone.home') }}"
                                title="{{ 'configuración de zona' }}">
                                <i class="tio-city nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ 'configuración de zona' }} </span>
                            </a>
                        </li>
                    @endif

                    @if (\App\CentralLogics\Helpers::module_permission_check('module'))
                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/business-settings/module') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" id="tourb-3"
                                href="javascript:" title="{{ 'configuración del módulo del sistema' }}">
                                <i class="tio-globe nav-icon"></i>
                                <span class="text-truncate">{{ 'configuración del módulo' }}</span>
                            </a>
                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                style="display:{{ Request::is('admin/business-settings/module*') ? 'block' : 'none' }}">
                                <li
                                    class="navbar-vertical-aside-has-menu {{ Request::is('admin/business-settings/module/store') ? 'active' : '' }}">
                                    <a class="js-navbar-vertical-aside-menu-link nav-link"
                                        href="{{ route('admin.business-settings.module.create') }}"
                                        title="{{ 'agregar módulo de negocios' }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">
                                            {{ 'agregar módulo de negocios' }}
                                        </span>
                                    </a>
                                </li>
                                <li
                                    class="navbar-vertical-aside-has-menu {{ Request::is('admin/business-settings/module') ? 'active' : '' }}">
                                    <a class="js-navbar-vertical-aside-menu-link nav-link"
                                        href="{{ route('admin.business-settings.module.index') }}"
                                        title="{{ 'módulos' }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">
                                            {{ 'módulos' }}
                                        </span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endif
                    @if (\App\CentralLogics\Helpers::module_permission_check('settings'))
                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/business-settings/business-setup*') || Request::is('admin/business-settings/language*') ? 'active' : '' }}">
                            <a class="nav-link " href="{{ route('admin.business-settings.business-setup') }}"
                                title="{{ 'configuración de negocios' }}">
                                <span class="tio-settings nav-icon"></span>
                                <span class="text-truncate">{{ 'entornos empresariales' }}</span>
                            </a>
                        </li>
                        @if (addon_published_status('TaxModule'))
                            <li class="navbar-vertical-aside-has-menu @yield('taxmodule')">

                                <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" id="tourb-3"
                                    href="javascript:" title="{{ 'Impuesto del sistema' }}">
                                    <i class="tio-wallet nav-icon"></i>
                                    <span class="text-truncate">{{ 'Impuesto del sistema' }}</span>
                                </a>


                                <ul class="js-navbar-vertical-aside-submenu nav nav-sub" style="display: @yield('taxmoduleDisplay', 'none')">

                                    <li class="navbar-vertical-aside-has-menu @yield('tax_setup')">
                                        <a class="js-navbar-vertical-aside-menu-link nav-link"
                                            href="{{ route('taxvat.index') }}" title="{{ 'Crear impuestos' }}">
                                            <i class="tio-chart-line-up nav-icon"></i>
                                            <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                                {{ 'Crear impuestos' }}
                                            </span>
                                        </a>
                                    </li>
                                    <li class="navbar-vertical-aside-has-menu @yield('tax_system_setup')">
                                        <a class="js-navbar-vertical-aside-menu-link nav-link"
                                            href="{{ route('taxvat.systemTaxvat', ['type' => 'vendor']) }}"
                                            title="{{ 'Impuestos de configuración' }}">
                                            <i class="tio-calculator nav-icon"></i>
                                            <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                                {{ 'Impuestos de configuración' }}
                                            </span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @endif
                    @endif








                    @if (\App\CentralLogics\Helpers::module_permission_check('subscription'))
                        <li class="navbar-vertical-aside-has-menu @yield('subscription')">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" id="tourb-3"
                                href="javascript:" title="{{ 'gestión de suscripciones' }}">
                                <i class="tio-crown nav-icon"></i>
                                <span class="text-truncate">{{ 'gestión de suscripciones' }}</span>
                            </a>
                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                style="display:{{ Request::is('admin/business-settings/subscription*') ? 'block' : 'none' }}">
                                <li class="navbar-vertical-aside-has-menu @yield('subscription_index')">
                                    <a class="js-navbar-vertical-aside-menu-link nav-link"
                                        href="{{ route('admin.business-settings.subscriptionackage.index') }}"
                                        title="{{ 'paquete de suscripción' }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">
                                            {{ 'paquete de suscripción' }}
                                        </span>
                                    </a>
                                </li>
                                <li class="navbar-vertical-aside-has-menu  @yield('subscriberList')">
                                    <a class="js-navbar-vertical-aside-menu-link nav-link"
                                        href="{{ route('admin.business-settings.subscriptionackage.subscriberList') }}"
                                        title="{{ 'Lista de suscriptores' }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">
                                            {{ 'Lista de suscriptores' }}
                                        </span>
                                    </a>
                                </li>
                                <li class="navbar-vertical-aside-has-menu  @yield('subscription_settings')">
                                    <a class="js-navbar-vertical-aside-menu-link nav-link"
                                        href="{{ route('admin.business-settings.subscriptionackage.settings') }}"
                                        title="{{ 'Configuración' }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">
                                            {{ 'Configuración' }}
                                        </span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endif

                    @if (\App\CentralLogics\Helpers::module_permission_check('settings'))

                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/business-settings/pages*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:"
                                title="{{ 'configuración de páginas' }}">
                                <i class="tio-pages nav-icon"></i>
                                <span
                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ 'páginas y redes sociales' }}</span>
                            </a>
                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                style="display:{{ Request::is('admin/business-settings/pages*') ? 'block' : 'none' }}">

                                <li
                                    class="navbar-vertical-aside-has-menu {{ Request::is('admin/business-settings/pages/social-media') ? 'active' : '' }}">
                                    <a class="nav-link "
                                        href="{{ route('admin.business-settings.social-media.index') }}"
                                        title="{{ 'Redes Sociales' }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{ 'Redes Sociales' }}</span>
                                    </a>
                                </li>

                                <li
                                    class="navbar-vertical-aside-has-menu {{ Request::is('admin/business-settings/pages/admin-landing-page-settings*') ? 'active' : '' }}">
                                    <a class="nav-link "
                                        href="{{ route('admin.business-settings.admin-landing-page-settings', 'fixed-data') }}"
                                        title="{{ 'configuración de la página de inicio del administrador' }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span
                                            class="text-truncate">{{ 'página de inicio del administrador' }}</span>
                                    </a>
                                </li>
                                <li
                                    class="navbar-vertical-aside-has-menu {{ Request::is('admin/business-settings/pages/react-landing-page-settings*') ? 'active' : '' }}">
                                    <a class="nav-link "
                                        href="{{ route('admin.business-settings.react-landing-page-settings', 'header') }}"
                                        title="{{ 'reaccionar página de inicio' }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span
                                            class="text-truncate">{{ 'reaccionar página de inicio' }}</span>
                                    </a>
                                </li>
                                <li
                                    class="navbar-vertical-aside-has-menu {{ Request::is('admin/business-settings/pages/flutter-landing-page-settings*') ? 'active' : '' }}">
                                    <a class="nav-link "
                                        href="{{ route('admin.business-settings.flutter-landing-page-settings', 'fixed-data') }}"
                                        title="{{ 'página de inicio de aleteo' }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span
                                            class="text-truncate">{{ 'página de inicio de aleteo' }}</span>
                                    </a>
                                </li>

                                <li
                                    class="navbar-vertical-aside-has-menu {{ Request::is('admin/business-settings/pages/business-page*') ? 'active' : '' }}">
                                    <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle"
                                        href="javascript:" title="{{ 'paginas comerciales' }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span
                                            class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ 'paginas comerciales' }}</span>
                                    </a>
                                    <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                        style="display:{{ Request::is('admin/business-settings/pages/business-page*') ? 'block' : 'none' }}">
                                        <li
                                            class="nav-item {{ Request::is('admin/business-settings/pages/business-page/terms-and-conditions') ? 'active' : '' }}">
                                            <a class="nav-link "
                                                href="{{ route('admin.business-settings.terms-and-conditions') }}"
                                                title="{{ 'términos y condiciones' }}">
                                                <span class="tio-circle nav-indicator-icon"></span>
                                                <span
                                                    class="text-truncate">{{ 'términos y condiciones' }}</span>
                                            </a>
                                        </li>

                                        <li
                                            class="nav-item {{ Request::is('admin/business-settings/pages/business-page/privacy-policy') ? 'active' : '' }}">
                                            <a class="nav-link "
                                                href="{{ route('admin.business-settings.privacy-policy') }}"
                                                title="{{ 'política de privacidad' }}">
                                                <span class="tio-circle nav-indicator-icon"></span>
                                                <span
                                                    class="text-truncate">{{ 'política de privacidad' }}</span>
                                            </a>
                                        </li>

                                        <li
                                            class="nav-item {{ Request::is('admin/business-settings/pages/business-page/about-us') ? 'active' : '' }}">
                                            <a class="nav-link "
                                                href="{{ route('admin.business-settings.about-us') }}"
                                                title="{{ 'sobre nosotros' }}">
                                                <span class="tio-circle nav-indicator-icon"></span>
                                                <span
                                                    class="text-truncate">{{ 'sobre nosotros' }}</span>
                                            </a>
                                        </li>
                                        <li
                                            class="nav-item {{ Request::is('admin/business-settings/pages/business-page/refund') ? 'active' : '' }}">
                                            <a class="nav-link " href="{{ route('admin.business-settings.refund') }}"
                                                title="{{ 'Política de reembolso' }}">
                                                <span class="tio-circle nav-indicator-icon"></span>
                                                <span class="text-truncate">{{ 'Política de reembolso' }}</span>
                                            </a>
                                        </li>

                                        <li
                                            class="nav-item {{ Request::is('admin/business-settings/pages/business-page/cancelation') ? 'active' : '' }}">
                                            <a class="nav-link "
                                                href="{{ route('admin.business-settings.cancelation') }}"
                                                title="{{ 'Política de Cancelación' }}">
                                                <span class="tio-circle nav-indicator-icon"></span>
                                                <span
                                                    class="text-truncate">{{ 'Política de Cancelación' }}</span>
                                            </a>
                                        </li>


                                        <li
                                            class="nav-item {{ Request::is('admin/business-settings/pages/business-page/shipping-policy') ? 'active' : '' }}">
                                            <a class="nav-link "
                                                href="{{ route('admin.business-settings.shipping-policy') }}"
                                                title="{{ 'política de envío' }}">
                                                <span class="tio-circle nav-indicator-icon"></span>
                                                <span class="text-truncate">{{ 'Política de envío' }}</span>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/business-settings/file-manager*') ? 'active' : '' }}">
                            <a class="nav-link " href="{{ route('admin.business-settings.file-manager.index') }}"
                                title="{{ 'galería' }}">
                                <span class="tio-album nav-icon"></span>
                                <span class="text-truncate text-capitalize">{{ 'galería' }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <small class="nav-subtitle"
                                title="{{ 'entornos empresariales' }}">{{ 'gestión del sistema' }}</small>
                            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                        </li>
                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/business-settings/third-party*') || Request::is('admin/business-settings/fcm*') || Request::is('admin/business-settings/offline-payment*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:"
                                title="{{ 'Configuraciones y terceros' }}">
                                <span class="nav-icon tio-account-square-outlined"></span>
                                <span
                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ 'Configuraciones y terceros' }}</span>
                            </a>
                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                style="display:{{ Request::is('admin/business-settings/third-party*') || Request::is('admin/business-settings/fcm*') || Request::is('admin/business-settings/login-url-setup*') || Request::is('admin/business-settings/offline-payment*')|| Request::is('admin/business-settings/marketing/*') || Request::is('admin/business-settings/open-ai') || Request::is('admin/business-settings/open-ai-settings') ? 'block' : 'none' }}">
                                <li
                                    class="navbar-vertical-aside-has-menu {{ Request::is('admin/business-settings/third-party*') ? 'active' : '' }}">
                                    <a class="nav-link "
                                        href="{{ route('admin.business-settings.third-party.payment-method') }}"
                                        title="{{ 'tercero' }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{ 'tercero' }}</span>
                                    </a>
                                </li>
                                <li
                                    class="navbar-vertical-aside-has-menu {{ Request::is('admin/business-settings/fcm*') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.business-settings.fcm-index') }}"
                                        title="{{ 'notificación de base de fuego' }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span
                                            class="text-truncate">{{ 'notificación de base de fuego' }}</span>
                                    </a>
                                </li>

                                @if (\App\CentralLogics\Helpers::get_mail_status('offline_payment_status'))
                                    <li
                                        class="navbar-vertical-aside-has-menu {{ Request::is('admin/business-settings/offline*') ? 'active' : '' }}">
                                        <a class="nav-link " href="{{ route('admin.business-settings.offline') }}"
                                            title="{{ 'Configuración de pago sin conexión' }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span
                                                class="text-truncate">{{ 'Configuración de pago sin conexión' }}</span>
                                        </a>
                                    </li>
                                @endif

                                <li class="nav-item @yield('analytics_Script')">
                                    <a class="nav-link " href="{{ route('admin.business-settings.marketing.analytic') }}"
                                        title="{{ 'Guión de análisis' }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{ 'Guión de análisis' }}</span>
                                    </a>
                                </li>

                                <li class="nav-item @yield('openAI')">
                                    <a class="nav-link " href="{{route('admin.business-settings.openAI')}}"
                                        title="{{ 'Configuración de IA' }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{ 'Configuración de IA' }}</span>
                                    </a>
                                </li>


                            </ul>
                        </li>
                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/business-settings/donation-settings*') ? 'active' : '' }}">
                            <a class="nav-link " href="{{ route('admin.business-settings.donation-settings') }}"
                                title="{{ 'Configuración de donación' }}">
                                <span class="tio-gift nav-icon"></span>
                                <span
                                    class="text-truncate text-capitalize">{{ 'Configuración de donación' }}</span>
                            </a>
                        </li>
                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/business-settings/login-settings*') || Request::is('admin/business-settings/login-url-setup*') ? 'active' : '' }}">
                            <a class="nav-link " href="{{ route('admin.business-settings.login-settings.index') }}"
                                title="{{ 'configuración de inicio de sesión' }}">
                                <span class="tio-devices-apple nav-icon"></span>
                                <span
                                    class="text-truncate text-capitalize">{{ 'configuración de inicio de sesión' }}</span>
                            </a>
                        </li>

                        @if (addon_published_status('Rental'))
                            <li
                                class="navbar-vertical-aside-has-menu {{ Request::is('admin/business-settings/rental-email-setup*') || Request::is('admin/business-settings/email-setup*') ? 'active' : '' }}">
                                <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" id="tourb-3"
                                    href="javascript:" title="{{ 'configuración de correo electrónico' }}">
                                    <i class="tio-email nav-icon"></i>
                                    <span class="text-truncate">{{ 'configuración de correo electrónico' }}</span>
                                </a>
                                <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                    style="display:{{ Request::is('admin/business-settings/rental-email-setup*') || Request::is('admin/business-settings/email-setup*') ? 'block' : 'none' }}">

                                    <li
                                        class="navbar-vertical-aside-has-menu {{ Request::is('admin/business-settings/email-setup*') ? 'active' : '' }}">
                                        <a class="js-navbar-vertical-aside-menu-link nav-link"
                                            href="{{ route('admin.business-settings.email-setup', ['admin', 'forgot-password']) }}"
                                            title="{{ 'Todos los módulos' }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">
                                                {{ 'Todos los módulos' }}
                                            </span>
                                        </a>
                                    </li>
                                    <li
                                        class="navbar-vertical-aside-has-menu  {{ Request::is('admin/business-settings/rental-email-setup*') ? 'active' : '' }}">
                                        <a class="js-navbar-vertical-aside-menu-link nav-link"
                                            href="{{ route('admin.business-settings.rental-email-setup', ['admin', 'provider-registration']) }}"
                                            title="{{ 'Módulo de Alquiler' }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">
                                                {{ 'Módulo de Alquiler' }}
                                            </span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @else
                            <li
                                class="navbar-vertical-aside-has-menu {{ Request::is('admin/business-settings/email-setup*') ? 'active' : '' }}">
                                <a class="nav-link "
                                    href="{{ route('admin.business-settings.email-setup', ['admin', 'forgot-password']) }}"
                                    title="{{ 'plantilla de correo electrónico' }}">
                                    <span class="tio-email nav-icon"></span>
                                    <span class="text-truncate">{{ 'plantilla de correo electrónico' }}</span>
                                </a>
                            </li>
                        @endif

                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/business-settings/app-settings*') ? 'active' : '' }}">
                            <a class="nav-link " href="{{ route('admin.business-settings.app-settings') }}"
                                title="{{ 'configuración de la aplicación' }}">
                                <span class="tio-android nav-icon"></span>
                                <span class="text-truncate">{{ 'configuración de la aplicación' }}</span>
                            </a>
                        </li>

                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/business-settings/addon-activation*') ? 'active' : '' }}">
                            <a class="nav-link "
                                href="{{ route('admin.business-settings.addon-activation.index') }}"
                                title="{{ 'Activación de complementos' }}">
                                <span class="tio-appointment nav-icon"></span>
                                <span class="text-truncate">{{ 'Activación de complementos' }}</span>
                            </a>
                        </li>


                        @if (addon_published_status('Rental'))
                            <li class="navbar-vertical-aside-has-menu @yield('notification_setup_type')">
                                <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" id="tourb-3"
                                    href="javascript:" title="{{ 'configuración de notificación' }}">
                                    <i class="tio-crown nav-icon"></i>
                                    <span class="text-truncate">{{ 'configuración de notificación' }}</span>
                                </a>
                                <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                    style="display:{{ Request::is('admin/business-settings/notification-setup*') ? 'block' : 'none' }}">

                                    <li class="navbar-vertical-aside-has-menu @yield('notification_setup')">
                                        <a class="js-navbar-vertical-aside-menu-link nav-link"
                                            href="{{ route('admin.business-settings.notification_setup') }}"
                                            title="{{ 'Todos los módulos' }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">
                                                {{ 'Todos los módulos' }}
                                            </span>
                                        </a>
                                    </li>
                                    <li class="navbar-vertical-aside-has-menu  @yield('notification_setup_rental')">
                                        <a class="js-navbar-vertical-aside-menu-link nav-link"
                                            href="{{ route('admin.business-settings.notification_setup', ['module' => 'rental']) }}"
                                            title="{{ 'Módulo de Alquiler' }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">
                                                {{ 'Módulo de Alquiler' }}
                                            </span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @else
                            <li class="navbar-vertical-aside-has-menu  @yield('notification_setup')">
                                <a class="nav-link "
                                    href="{{ route('admin.business-settings.notification_setup') }}"
                                    title="{{ 'Canales de notificación' }} ">
                                    <span class="tio-snooze-notification  nav-icon"></span>
                                    <span class="text-truncate">{{ 'Canales de notificación' }}
                                    </span>
                                </a>
                            </li>
                        @endif




                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/business-settings/db-index') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                                href="{{ route('admin.business-settings.db-index') }}"
                                title="{{ 'limpiar base de datos' }}">
                                <i class="tio-cloud nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ 'limpiar base de datos' }}
                                </span>
                            </a>
                        </li>

                        <!-- Seguridad & Logs -->
                        @if (Route::has('admin.logs.index'))
                        <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/logs*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:"
                                title="{{ 'Seguridad' }}">
                                <i class="tio-shield nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ 'Seguridad' }}
                                </span>
                            </a>
                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                style="display:{{ Request::is('admin/logs*') ? 'block' : 'none' }}">
                                <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/logs*') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.logs.index') }}"
                                        title="{{ 'Monitoreo de registros' }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{ 'Monitoreo de registros' }}</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        @endif
                    @endif

                    <!-- Dashboards -->
                    <li
                        class="navbar-vertical-aside-has-menu {{ Request::is('admin/business-settings/system-addon') ? 'show active' : '' }}">
                        <a class="js-navbar-vertical-aside-menu-link nav-link"
                            href="{{ route('admin.business-settings.system-addon.index') }}"
                            title="{{ 'complementos del sistema' }}">
                            <i class="tio-add-circle-outlined nav-icon"></i>
                            <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                {{ 'complementos del sistema' }}
                            </span>
                        </a>
                    </li>
                    <!-- End Dashboards -->


                    @if (count(config('addon_admin_routes')) > 0)
                        <li class="nav-item">
                            <small class="nav-subtitle">{{ 'menús adicionales' }}</small>
                            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                        </li>
                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/payment/configuration/*') || Request::is('admin/sms/configuration/*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:">
                                <i class="tio-puzzle nav-icon"></i>
                                <span
                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ 'Menús complementarios' }}</span>
                            </a>
                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                style="display: {{ Request::is('admin/payment/configuration/*') || Request::is('admin/sms/configuration/*') ? 'block' : 'none' }}">
                                @foreach (config('addon_admin_routes') as $routes)
                                    @foreach ($routes as $route)
                                        <li
                                            class="navbar-vertical-aside-has-menu {{ Request::is($route['path']) ? 'active' : '' }}">
                                            <a class="js-navbar-vertical-aside-menu-link nav-link "
                                                href="{{ $route['url'] }}"
                                                title="{{ translate($route['name']) }}">
                                                <span class="tio-circle nav-indicator-icon"></span>
                                                <span class="text-truncate">{{ translate($route['name']) }}</span>
                                            </a>
                                        </li>
                                    @endforeach
                                @endforeach
                            </ul>
                        </li>
                    @endif
                    <!--addon end-->
                    <!-- End web & adpp Settings -->

                    <li class="nav-item py-5">

                    </li>

                    @includeIf('layouts.admin.partials._logout_modal')
                </ul>
            </div>
            <!-- End Content -->
        </div>
    </aside>
</div>

<div id="sidebarCompact" class="d-none">

</div>


@push('script_2')
    <script>
        $(window).on('load', function() {
            if ($(".navbar-vertical-content li.active").length) {
                $('.navbar-vertical-content').animate({
                    scrollTop: $(".navbar-vertical-content li.active").offset().top - 150
                }, 10);
            }
        });

        var $rows = $('#navbar-vertical-content li');
        $('#search-sidebar-menu').keyup(function() {
            var val = $.trim($(this).val()).replace(/ +/g, ' ').toLowerCase();

            $rows.show().filter(function() {
                var text = $(this).text().replace(/\s+/g, ' ').toLowerCase();
                return !~text.indexOf(val);
            }).hide();
        });


        $(document).ready(function() {
            const $searchInput = $('#search');
            const $suggestionsList = $('#search-suggestions');
            const $rows = $('#navbar-vertical-content li');
            const $subrows = $('#navbar-vertical-content li ul li');
            {{-- const suggestions = ['{{strtolower('zona')  }}', '{{ strtolower('configuración')  }}', '{{ strtolower('paginas') }}', '{{ strtolower('tercero') }}','{{ strtolower('sistema') }}' ]; --}}
            const focusInput = () => updateSuggestions($searchInput.val());
            const hideSuggestions = () => $suggestionsList.slideUp(700);
            const showSuggestions = () => $suggestionsList.slideDown(700);
            let clickSuggestion = function() {
                let suggestionText = $(this).text();
                $searchInput.val(suggestionText);
                hideSuggestions();
                filterItems(suggestionText.toLowerCase());
                updateSuggestions(suggestionText);
            };
            let filterItems = (val) => {
                let unmatchedItems = $rows.show().filter((index, element) => !~$(element).text().replace(
                    /\s+/g, ' ').toLowerCase().indexOf(val));
                let matchedItems = $rows.show().filter((index, element) => ~$(element).text().replace(/\s+/g,
                    ' ').toLowerCase().indexOf(val));
                unmatchedItems.hide();
                matchedItems.each(function() {
                    let $submenu = $(this).find($subrows);
                    let keywordCountInRows = 0;
                    $rows.each(function() {
                        let rowText = $(this).text().toLowerCase();
                        let valLower = val.toLowerCase();
                        let keywordCountRow = rowText.split(valLower).length - 1;
                        keywordCountInRows += keywordCountRow;
                    });
                    if ($submenu.length > 0) {
                        $subrows.show();
                        $submenu.each(function() {
                            let $submenu2 = !~$(this).text().replace(/\s+/g, ' ')
                                .toLowerCase().indexOf(val);
                            if ($submenu2 && keywordCountInRows <= 2) {
                                $(this).hide();
                            }
                        });
                    }
                });
            };
            let updateSuggestions = (val) => {
                $suggestionsList.empty();
                suggestions.forEach(suggestion => {
                    if (suggestion.toLowerCase().includes(val.toLowerCase())) {
                        $suggestionsList.append(
                            `<span class="search-suggestion badge badge-soft-light m-1 fs-14">${suggestion}</span>`
                        );
                    }
                });
                // showSuggestions();
            };
            $searchInput.focus(focusInput);
            $searchInput.on('input', function() {
                updateSuggestions($(this).val());
            });
            $suggestionsList.on('click', '.search-suggestion', clickSuggestion);
            $searchInput.keyup(function() {
                filterItems($(this).val().toLowerCase());
            });
            $searchInput.on('focusout', hideSuggestions);
            $searchInput.on('focus', showSuggestions);
        });
    </script>
@endpush
