<div id="sidebarMain" class="d-none">
    <aside
        class="js-navbar-vertical-aside navbar navbar-vertical-aside navbar-vertical navbar-vertical-fixed navbar-expand-xl navbar-bordered  ">
        <div class="navbar-vertical-container">
            <div class="navbar-brand-wrapper justify-content-between">
                <!-- Logo -->
                @php
                    $store_logo = \App\Models\BusinessSetting::where(['key' => 'logo'])->first();
                @endphp
                <a class="navbar-brand" href="{{ route('admin.dispatch.dashboard') }}" aria-label="Front">
                    <img class="navbar-brand-logo initial--36 onerror-image onerror-image"
                        data-onerror-image="{{ asset('assets/admin/img/160x160/img2.jpg') }}"
                        src="{{\App\CentralLogics\Helpers::get_full_url('business', $store_logo?->value ?? '', $store_logo?->storage[0]?->value ?? 'public', 'favicon')}}"
                        alt="Logo">
                    <img class="navbar-brand-logo-mini initial--36 onerror-image onerror-image"
                        data-onerror-image="{{ asset('assets/admin/img/160x160/img2.jpg') }}"
                        src="{{\App\CentralLogics\Helpers::get_full_url('business', $store_logo?->value ?? '', $store_logo?->storage[0]?->value ?? 'public', 'favicon')}}"
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
                <form class="sidebar--search-form">
                    <div class="search--form-group">
                        <button type="button" class="btn"><i class="tio-search"></i></button>
                        <input type="text" class="form-control form--control"
                            placeholder="{{ 'Búsqueda' }}" id="search-sidebar-menu">
                    </div>
                </form>
                <ul class="navbar-nav navbar-nav-lg nav-tabs">
                    <!-- Dashboards -->
                    <li class="navbar-vertical-aside-has-menu {{ Request::is('admin') ? 'show active' : '' }}">
                        <a class="js-navbar-vertical-aside-menu-link nav-link"
                            href="{{ route('admin.dashboard') }}?module_id={{Config::get('module.current_module_id')}}"
                            title="{{ 'Panel de Control' }}">
                            <i class="tio-home-vs-1-outlined nav-icon"></i>
                            <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                {{ 'Panel de Control' }}
                            </span>
                        </a>
                    </li>
                    <!-- End Dashboards -->

                    <!-- Marketing section -->
                    <li class="nav-item">
                        <small class="nav-subtitle"
                            title="{{ 'mango de empleado' }}">{{ 'sección pos' }}</small>
                        <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                    </li>
                    <!-- Pos -->
                    @if(\App\CentralLogics\Helpers::module_permission_check('pos'))
                        <li class="navbar-vertical-aside-has-menu {{Request::is('admin/pos*') ? 'active' : ''}}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link " href="{{route('admin.pos.index')}}"
                                title="{{'Nueva venta'}}">
                                <i class="tio-shopping-basket-outlined nav-icon"></i>
                                <span class="text-truncate">{{'Nueva venta'}}</span>
                            </a>
                        </li>
                    @endif
                    <!-- Pos -->

                    <li class="nav-item">
                        <small class="nav-subtitle"
                            title="{{ 'sección del módulo' }}">{{ 'gestión de módulos' }}</small>
                        <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                    </li>

                    @if (\App\CentralLogics\Helpers::module_permission_check('zone'))
                        <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/zone*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link" href="{{ route('admin.zone.home') }}"
                                title="{{ 'configuración de zona' }}">
                                <i class="tio-city nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ 'configuración de zona' }} </span>
                            </a>
                        </li>
                    @endif

                    @if (\App\CentralLogics\Helpers::module_permission_check('module'))
                        <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/module') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:"
                                title="{{ 'configuración del módulo del sistema' }}">
                                <i class="tio-globe nav-icon"></i>
                                <span class="text-truncate">{{ 'configuración del módulo del sistema' }}</span>
                            </a>
                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                style="display:{{ Request::is('admin/module*') ? 'block' : 'none' }}">
                                <li
                                    class="navbar-vertical-aside-has-menu {{ Request::is('admin/module/create') ? 'active' : '' }}">
                                    <a class="js-navbar-vertical-aside-menu-link nav-link"
                                        href="{{ route('admin.module.create') }}"
                                        title="{{ 'agregar módulo' }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">
                                            {{ 'agregar módulo' }}
                                        </span>
                                    </a>
                                </li>
                                <li
                                    class="navbar-vertical-aside-has-menu {{ Request::is('admin/module') ? 'active' : '' }}">
                                    <a class="js-navbar-vertical-aside-menu-link nav-link"
                                        href="{{ route('admin.module.index') }}"
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

                    <!-- Marketing section -->
                    <li class="nav-item">
                        <small class="nav-subtitle"
                            title="{{ 'mango de empleado' }}">{{ 'Promociones' }}</small>
                        <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                    </li>
                    <!-- Campaign -->
                    @if (\App\CentralLogics\Helpers::module_permission_check('campaign'))
                        <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/campaign') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:"
                                title="{{ 'campañas' }}">
                                <i class="tio-layers-outlined nav-icon"></i>
                                <span
                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ 'campañas' }}</span>
                            </a>
                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                style="display:{{ Request::is('admin/campaign*') ? 'block' : 'none' }}">

                                <li class="nav-item {{ Request::is('admin/campaign/basic/*') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.campaign.list', 'basic') }}"
                                        title="{{ 'campañas básicas' }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{ 'campañas básicas' }}</span>
                                    </a>
                                </li>
                                <li class="nav-item {{ Request::is('admin/campaign/item/*') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.campaign.list', 'item') }}"
                                        title="{{ 'campañas de artículos' }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{ 'campañas de artículos' }}</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endif
                    <!-- End Campaign -->
                    <!-- Banner -->
                    @if (\App\CentralLogics\Helpers::module_permission_check('banner'))
                        <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/banner*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                                href="{{ route('admin.banner.add-new') }}" title="{{ 'pancartas' }}">
                                <i class="tio-image nav-icon"></i>
                                <span
                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ 'pancartas' }}</span>
                            </a>
                        </li>
                    @endif
                    <!-- End Banner -->
                    <!-- Coupon -->
                    @if (\App\CentralLogics\Helpers::module_permission_check('coupon'))
                        <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/coupon*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                                href="{{ route('admin.coupon.add-new') }}" title="{{ 'cupones' }}">
                                <i class="tio-gift nav-icon"></i>
                                <span
                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ 'cupones' }}</span>
                            </a>
                        </li>
                    @endif
                    <!-- End Coupon -->
                    @if (\App\CentralLogics\Helpers::module_permission_check('cashback'))
                        <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/cashback*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                                href="{{ route('admin.cashback.add-new') }}" title="{{ 'reembolso' }}">
                                <i class="tio-settings-back nav-icon"></i>
                                <span
                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ 'reembolso' }}</span>
                            </a>
                        </li>
                    @endif
                    <!-- Notification -->
                    @if (\App\CentralLogics\Helpers::module_permission_check('notification'))
                        <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/notification*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                                href="{{ route('admin.notification.add-new') }}"
                                title="{{ 'notificación push' }}">
                                <i class="tio-notifications nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ 'notificación push' }}
                                </span>
                            </a>
                        </li>
                    @endif
                    <!-- End Notification -->

                    <!-- Tootli Abastos -->
                    @if (\App\CentralLogics\Helpers::module_permission_check('order'))
                        <li class="nav-item">
                            <small class="nav-subtitle">Tootli Abastos</small>
                            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                        </li>

                        <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/abastos*') || (Request::is('admin/item*') && request()->query('is_abastos') == 1) || (Request::is('admin/category*') && request()->query('is_abastos') == 1) ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:"
                                title="Tootli Abastos">
                                <i class="tio-shopping-basket nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    Tootli Abastos
                                </span>
                            </a>
                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                style="display:{{ Request::is('admin/abastos*') || (Request::is('admin/item*') && request()->query('is_abastos') == 1) || (Request::is('admin/category*') && request()->query('is_abastos') == 1) ? 'block' : 'none' }}">
                                <li class="nav-item {{ Request::is('admin/abastos/order/list/all') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('admin.abastos.order.list', ['all']) }}"
                                        title="Todos los Pedidos">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            Todos
                                            <span class="badge badge-soft-info badge-pill ml-1">
                                                {{ \App\Models\Order::where('is_abastos', 1)->count() }}
                                            </span>
                                        </span>
                                    </a>
                                </li>
                                <li class="nav-item {{ Request::is('admin/abastos/order/list/pending') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('admin.abastos.order.list', ['pending']) }}"
                                        title="Pendientes">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            Pendientes
                                            <span class="badge badge-soft-info badge-pill ml-1">
                                                {{ \App\Models\Order::where('is_abastos', 1)->where('order_status', 'pending')->count() }}
                                            </span>
                                        </span>
                                    </a>
                                </li>
                                <li class="nav-item {{ Request::is('admin/abastos/order/list/processing') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('admin.abastos.order.list', ['processing']) }}"
                                        title="En Preparación">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            En Preparación
                                            <span class="badge badge-soft-warning badge-pill ml-1">
                                                {{ \App\Models\Order::where('is_abastos', 1)->whereIn('order_status', ['confirmed', 'processing', 'handover'])->count() }}
                                            </span>
                                        </span>
                                    </a>
                                </li>
                                <li class="nav-item {{ Request::is('admin/abastos/order/list/delivered') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('admin.abastos.order.list', ['delivered']) }}"
                                        title="Entregados">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            Entregados
                                            <span class="badge badge-soft-success badge-pill ml-1">
                                                {{ \App\Models\Order::where('is_abastos', 1)->where('order_status', 'delivered')->count() }}
                                            </span>
                                        </span>
                                    </a>
                                </li>
                                <li class="nav-item {{ Request::is('admin/abastos/order/list/canceled') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('admin.abastos.order.list', ['canceled']) }}"
                                        title="Cancelados">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            Cancelados
                                            <span class="badge badge-soft-danger badge-pill ml-1">
                                                {{ \App\Models\Order::where('is_abastos', 1)->where('order_status', 'canceled')->count() }}
                                            </span>
                                        </span>
                                    </a>
                                </li>

                                <!-- Separador Catálogo -->
                                <li class="nav-item-header" style="padding: 0.5rem 1rem 0.25rem; font-size: 0.75rem; text-transform: uppercase; color: #9ca3af; font-weight: 600;">
                                    Catálogo de Insumos
                                </li>
                                <li class="nav-item {{ Request::is('admin/category/add*') && request()->query('is_abastos') == 1 && request()->query('position') == 0 ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('admin.category.add', ['position' => 0, 'is_abastos' => 1]) }}"
                                        title="Categorías">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">Categorías</span>
                                    </a>
                                </li>
                                <li class="nav-item {{ Request::is('admin/category/add*') && request()->query('is_abastos') == 1 && request()->query('position') == 1 ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('admin.category.add', ['position' => 1, 'is_abastos' => 1]) }}"
                                        title="Sub Categorías">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">Sub Categorías</span>
                                    </a>
                                </li>
                                <li class="nav-item {{ Request::is('admin/item/add-new') && request()->query('is_abastos') == 1 ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('admin.item.add-new', ['is_abastos' => 1]) }}"
                                        title="Nuevo Insumo">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">Nuevo Insumo</span>
                                    </a>
                                </li>
                                <li class="nav-item {{ Request::is('admin/item/list') && request()->query('is_abastos') == 1 ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('admin.item.list', ['is_abastos' => 1]) }}"
                                        title="Lista de Insumos">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">Lista de Insumos</span>
                                    </a>
                                </li>

                                <!-- Configuración -->
                                <li class="nav-item-header" style="padding: 0.5rem 1rem 0.25rem; font-size: 0.75rem; text-transform: uppercase; color: #9ca3af; font-weight: 600;">
                                    Configuración
                                </li>
                                <li class="nav-item {{ Request::is('admin/abastos/schedule') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('admin.abastos.schedule') }}"
                                        title="Horarios de Entrega">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">Horarios de Entrega</span>
                                    </a>
                                </li>
                                <li class="nav-item {{ Request::is('admin/abastos/shipping-setup') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('admin.abastos.shipping-setup') }}"
                                        title="Configuración de Envío">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">Configuración de Envío</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endif

                    <!-- End marketing section -->
                    <!-- Orders -->
                    @if (\App\CentralLogics\Helpers::module_permission_check('order'))
                        <li class="nav-item">
                            <small class="nav-subtitle">{{ 'gestión de pedidos' }}</small>
                            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                        </li>

                        <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/order') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:"
                                title="{{ 'Pedidos' }}">
                                <i class="tio-shopping-cart nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ 'Pedidos' }}
                                </span>
                            </a>
                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                style="display:{{ Request::is('admin/order*') ? 'block' : 'none' }}">
                                <li class="nav-item {{ Request::is('admin/order/list/all') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('admin.order.list', ['all']) }}"
                                        title="{{ 'todos los pedidos' }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            {{ 'todo' }}
                                            <span class="badge badge-soft-info badge-pill ml-1">
                                                {{ \App\Models\Order::StoreOrder()->count() }}
                                            </span>
                                        </span>
                                    </a>
                                </li>
                                <li class="nav-item {{ Request::is('admin/order/list/scheduled') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('admin.order.list', ['scheduled']) }}"
                                        title="{{ 'pedidos programados' }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            {{ 'programado' }}
                                            <span class="badge badge-soft-info badge-pill ml-1">
                                                {{ \App\Models\Order::Scheduled()->StoreOrder()->count() }}
                                            </span>
                                        </span>
                                    </a>
                                </li>
                                <li class="nav-item {{ Request::is('admin/order/list/pending') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.order.list', ['pending']) }}"
                                        title="{{ 'pedidos pendientes' }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            {{ 'Pendiente' }}
                                            <span class="badge badge-soft-info badge-pill ml-1">
                                                {{ \App\Models\Order::Pending()->OrderScheduledIn(30)->StoreOrder()->count() }}
                                            </span>
                                        </span>
                                    </a>
                                </li>

                                <li class="nav-item {{ Request::is('admin/order/list/accepted') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.order.list', ['accepted']) }}"
                                        title="{{ 'pedidos aceptados' }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            {{ 'aceptado' }}
                                            <span class="badge badge-soft-success badge-pill ml-1">
                                                {{ \App\Models\Order::AccepteByDeliveryman()->OrderScheduledIn(30)->StoreOrder()->count() }}
                                            </span>
                                        </span>
                                    </a>
                                </li>
                                <li class="nav-item {{ Request::is('admin/order/list/processing') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.order.list', ['processing']) }}"
                                        title="{{ 'procesando pedidos' }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            {{ 'tratamiento' }}
                                            <span class="badge badge-soft-warning badge-pill ml-1">
                                                {{ \App\Models\Order::Preparing()->OrderScheduledIn(30)->StoreOrder()->count() }}
                                            </span>
                                        </span>
                                    </a>
                                </li>
                                <li class="nav-item {{ Request::is('admin/order/list/item_on_the_way') ? 'active' : '' }}">
                                    <a class="nav-link text-capitalize"
                                        href="{{ route('admin.order.list', ['item_on_the_way']) }}"
                                        title="{{ 'orden en el camino' }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            {{ 'orden en el camino' }}
                                            <span class="badge badge-soft-warning badge-pill ml-1">
                                                {{ \App\Models\Order::ItemOnTheWay()->OrderScheduledIn(30)->StoreOrder()->count() }}
                                            </span>
                                        </span>
                                    </a>
                                </li>
                                <li class="nav-item {{ Request::is('admin/order/list/delivered') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.order.list', ['delivered']) }}"
                                        title="{{ 'pedidos entregados' }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            {{ 'Entregado' }}
                                            <span class="badge badge-soft-success badge-pill ml-1">
                                                {{ \App\Models\Order::Delivered()->StoreOrder()->count() }}
                                            </span>
                                        </span>
                                    </a>
                                </li>
                                <li class="nav-item {{ Request::is('admin/order/list/canceled') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.order.list', ['canceled']) }}"
                                        title="{{ 'pedidos cancelados' }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            {{ 'Cancelado' }}
                                            <span class="badge badge-soft-warning bg-light badge-pill ml-1">
                                                {{ \App\Models\Order::Canceled()->StoreOrder()->count() }}
                                            </span>
                                        </span>
                                    </a>
                                </li>
                                <li class="nav-item {{ Request::is('admin/order/list/failed') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.order.list', ['failed']) }}"
                                        title="{{ 'pedidos fallidos en el pago' }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container text-capitalize">
                                            {{ 'pago fallido' }}
                                            <span class="badge badge-soft-danger bg-light badge-pill ml-1">
                                                {{ \App\Models\Order::failed()->StoreOrder()->count() }}
                                            </span>
                                        </span>
                                    </a>
                                </li>
                                <li class="nav-item {{ Request::is('admin/order/list/refunded') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.order.list', ['refunded']) }}"
                                        title="{{ 'pedidos reembolsados' }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            {{ 'Reembolsado' }}
                                            <span class="badge badge-soft-danger bg-light badge-pill ml-1">
                                                {{ \App\Models\Order::Refunded()->StoreOrder()->count() }}
                                            </span>
                                        </span>
                                    </a>
                                </li>
                                <li class="nav-item {{ Request::is('admin/order/strike-review-queue*') ? 'active' : '' }}">
                                    @php
                                        $pendingStrikeReviews = 0;
                                        try {
                                            $pendingStrikeReviews = \App\Models\OrderStrikeReviewQueue::query()
                                                ->where('status', \App\Models\OrderStrikeReviewQueue::STATUS_PENDING)
                                                ->count();
                                        } catch (\Throwable $e) {
                                            $pendingStrikeReviews = 0;
                                        }
                                    @endphp
                                    <a class="nav-link" href="{{ route('admin.order.strike-review-queue.index') }}"
                                        title="{{ 'título de la cola de revisión de huelga de pedido' }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            {{ 'menú de cola de revisión de huelga de pedidos' }}
                                            <span class="badge badge-soft-warning bg-light badge-pill ml-1">
                                                {{ $pendingStrikeReviews }}
                                            </span>
                                        </span>
                                    </a>
                                </li>
                                <li class="nav-item {{ Request::is('admin/order/offline/payment/list*') ? 'active' : '' }}">
                                    <a class="nav-link "
                                        href="{{ route('admin.order.offline_verification_list', ['all']) }}"
                                        title="{{ 'Pagos sin conexión' }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            {{ 'Pagos sin conexión' }}
                                            <span class="badge badge-soft-danger bg-light badge-pill ml-1">
                                                {{ \App\Models\Order::where('payment_method', 'offline_payments')->whereHas('offline_payment')->StoreOrder()->module(Config::get('module.current_module_id'))->count() }}
                                            </span>
                                        </span>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- Order refund -->
                        <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/refund/*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:"
                                title="{{ 'Reembolsos de pedidos' }}">
                                <i class="tio-receipt nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ 'Reembolsos de pedidos' }}
                                </span>
                            </a>
                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                style="display: {{ Request::is('admin/refund*') ? 'block' : 'none' }}">

                                <li
                                    class="nav-item {{ Request::is('admin/refund/requested') || Request::is('admin/refund/rejected') || Request::is('admin/refund/refunded') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.refund.refund_attr', ['requested']) }}"
                                        title="{{ 'Solicitudes de reembolso' }} ">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            {{ 'Solicitudes de reembolso' }}
                                            <span class="badge badge-soft-danger badge-pill ml-1">
                                                {{ \App\Models\Order::Refund_requested()->StoreOrder()->count() }}
                                            </span>
                                        </span>
                                    </a>
                                </li>

                                {{-- <li class="nav-item {{ Request::is('admin/refund/settings') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.refund.refund_settings') }}"
                                        title="{{ 'configuración de reembolso' }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            {{ 'configuración de reembolso' }}

                                        </span>
                                    </a>
                                </li> --}}
                            </ul>
                        </li>
                        <!-- Order refund End-->

                        <!-- Order dispachment -->
                        <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/dispatch/*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:"
                                title="{{ 'despacho' }}">
                                <i class="tio-clock nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ 'despacho' }}
                                </span>
                            </a>
                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                style="{{ Request::is('admin/dispatch*') ? 'display-block' : 'display-none' }}">
                                <li
                                    class="nav-item {{ Request::is('admin/dispatch/list/searching_for_deliverymen') ? 'active' : '' }}">
                                    <a class="nav-link "
                                        href="{{ route('admin.dispatch.list', ['searching_for_deliverymen']) }}"
                                        title="{{ 'Pedidos Sin Asignar' }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            {{'Pedidos Sin Asignar'}}
                                            <span class="badge badge-soft-info badge-pill ml-1">
                                                {{ \App\Models\Order::SearchingForDeliveryman()->OrderScheduledIn(30)->StoreOrder()->count() }}
                                            </span>
                                        </span>
                                    </a>
                                </li>
                                <li class="nav-item {{ Request::is('admin/dispatch/list/on_going') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.dispatch.list', ['on_going']) }}"
                                        title="{{ 'Pedidos en curso' }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            {{ 'Pedidos en curso' }}
                                            <span class="badge badge-soft-light badge-pill ml-1">
                                                {{ \App\Models\Order::Ongoing()->OrderScheduledIn(30)->StoreOrder()->count() }}
                                            </span>
                                        </span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <!-- Order dispachment End-->
                    @endif
                    <!-- End Orders -->



                    <!-- Parcel Section -->
                    <li class="nav-item">
                        <small class="nav-subtitle"
                            title="{{ 'sección de paquetería' }}">{{ 'gestión de paquetes' }}</small>
                        <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                    </li>

                    @if (\App\CentralLogics\Helpers::module_permission_check('parcel'))
                        <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/parcel*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:"
                                title="{{ 'parcela' }}">
                                <i class="tio-bus nav-icon"></i>
                                <span
                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ 'parcela' }}</span>
                            </a>
                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                style="display:{{ Request::is('admin/parcel*') ? 'block' : 'none' }}">
                                <li
                                    class="navbar-vertical-aside-has-menu {{ Request::is('admin/parcel/category') ? 'active' : '' }}">
                                    <a class="js-navbar-vertical-aside-menu-link nav-link"
                                        href="{{ route('admin.parcel.category.index') }}"
                                        title="{{ 'categoría de paquete' }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">
                                            {{ 'categoría de paquete' }}
                                        </span>
                                    </a>
                                </li>
                                <li
                                    class="navbar-vertical-aside-has-menu {{ Request::is('admin/parcel/orders*') ? 'active' : '' }}">
                                    <a class="js-navbar-vertical-aside-menu-link nav-link"
                                        href="{{ route('admin.parcel.orders') }}"
                                        title="{{ 'pedidos de paquetes' }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">
                                            {{ 'pedidos de paquetes' }}
                                        </span>
                                    </a>
                                </li>

                                <li
                                    class="navbar-vertical-aside-has-menu {{ Request::is('admin/parcel/settings') ? 'active' : '' }}">
                                    <a class="js-navbar-vertical-aside-menu-link nav-link"
                                        href="{{ route('admin.parcel.settings') }}"
                                        title="{{ 'configuración de paquete' }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">
                                            {{ 'configuración de paquete' }}
                                        </span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endif
                    <!--End Parcel Section -->

                    <li class="nav-item">
                        <small class="nav-subtitle"
                            title="{{ 'sección de artículos' }}">{{ 'gestión de artículos' }}</small>
                        <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                    </li>

                    <!-- Category -->
                    @if (\App\CentralLogics\Helpers::module_permission_check('category'))
                        <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/category*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:"
                                title="{{ 'categorias' }}">
                                <i class="tio-category nav-icon"></i>
                                <span
                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ 'categorias' }}</span>
                            </a>
                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                style="display:{{ Request::is('admin/category*') ? 'block' : 'none' }}">
                                <li
                                    class="nav-item @yield('main_category') {{ Request::is('admin/category/add') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.category.add') }}"
                                        title="{{ 'categoría' }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{ 'categoría' }}</span>
                                    </a>
                                </li>

                                <li
                                    class="nav-item @yield('sub_category') {{ Request::is('admin/category/add-sub-category') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.category.add-sub-category') }}"
                                        title="{{ 'subcategoría' }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{ 'subcategoría' }}</span>
                                    </a>
                                </li>

                                {{-- <li
                                    class="nav-item {{Request::is('admin/category/add-sub-sub-category')?'active':''}}">
                                    <a class="nav-link " href="{{route('admin.category.add-sub-sub-category')}}"
                                        title="add new sub sub category">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">Sub-Sub-Category</span>
                                    </a>
                                </li> --}}
                                <li class="nav-item {{ Request::is('admin/category/bulk-import') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.category.bulk-import') }}"
                                        title="{{ 'importación a granel' }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span
                                            class="text-truncate text-capitalize">{{ 'importación a granel' }}</span>
                                    </a>
                                </li>
                                <li class="nav-item {{ Request::is('admin/category/bulk-export') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.category.bulk-export-index') }}"
                                        title="{{ 'exportación a granel' }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span
                                            class="text-truncate text-capitalize">{{ 'exportación a granel' }}</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endif
                    <!-- End Category -->

                    <!-- Attributes -->
                    @if (\App\CentralLogics\Helpers::module_permission_check('attribute'))
                        <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/attribute*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                                href="{{ route('admin.attribute.add-new') }}"
                                title="{{ 'atributos' }}">
                                <i class="tio-apps nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ 'atributos' }}
                                </span>
                            </a>
                        </li>
                    @endif
                    <!-- End Attributes -->

                    <!-- Unit -->
                    @if (\App\CentralLogics\Helpers::module_permission_check('unit'))
                        <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/unit*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link" href="{{ route('admin.unit.index') }}"
                                title="{{ 'unidades' }}">
                                <i class="tio-ruler nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate text-capitalize">
                                    {{ 'unidades' }}
                                </span>
                            </a>
                        </li>
                    @endif
                    <!-- End Unit -->

                    <!-- AddOn -->
                    @if (\App\CentralLogics\Helpers::module_permission_check('addon'))
                        <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/addon*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:"
                                title="{{ 'complementos' }}">
                                <i class="tio-add-circle-outlined nav-icon"></i>
                                <span
                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ 'complementos' }}</span>
                            </a>
                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                style="display:{{ Request::is('admin/addon*') ? 'block' : 'none' }}">
                                <li class="nav-item {{ Request::is('admin/addon/add-new') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.addon.add-new') }}"
                                        title="{{ 'lista de complementos' }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{ 'lista' }}</span>
                                    </a>
                                </li>

                                <li class="nav-item {{ Request::is('admin/addon/bulk-import') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.addon.bulk-import') }}"
                                        title="{{ 'importación a granel' }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span
                                            class="text-truncate text-capitalize">{{ 'importación a granel' }}</span>
                                    </a>
                                </li>
                                <li class="nav-item {{ Request::is('admin/addon/bulk-export') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.addon.bulk-export-index') }}"
                                        title="{{ 'exportación a granel' }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span
                                            class="text-truncate text-capitalize">{{ 'exportación a granel' }}</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endif
                    <!-- End AddOn -->
                    <!-- Food -->
                    @if (\App\CentralLogics\Helpers::module_permission_check('item'))
                        <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/item*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:"
                                title="{{ 'Productos' }}">
                                <i class="tio-premium-outlined nav-icon"></i>
                                <span
                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate text-capitalize">{{ 'Productos' }}</span>
                            </a>
                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                style="display:{{ Request::is('admin/item*') ? 'block' : 'none' }}">
                                <li
                                    class="nav-item {{ Request::is('admin/item/add-new') || (Request::is('admin/item/edit/*') && strpos(request()->fullUrl(), 'product_gellary=1') !== false) ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.item.add-new') }}"
                                        title="{{ 'agregar nuevo' }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{ 'agregar nuevo' }}</span>
                                    </a>
                                </li>
                                <li
                                    class="nav-item {{ Request::is('admin/item/list') || (Request::is('admin/item/edit/*') && (strpos(request()->fullUrl(), 'temp_product=1') == false && strpos(request()->fullUrl(), 'product_gellary=1') == false)) ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.item.list') }}"
                                        title="{{ 'lista de alimentos' }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{ 'lista' }}</span>
                                    </a>
                                </li>
                                {{-- @if (\App\CentralLogics\Helpers::get_mail_status('product_gallery')) --}}
                                <li class="nav-item {{  Request::is('admin/item/product-gallery') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.item.product_gallery') }}"
                                        title="{{ 'Galería de productos' }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{ 'Galería de productos' }}</span>
                                    </a>
                                </li>
                                {{-- @endif --}}
                                @if (\App\CentralLogics\Helpers::get_mail_status('product_approval'))
                                    <li
                                        class="nav-item {{ Request::is('admin/item/new/item/list') || (Request::is('admin/item/edit/*') && strpos(request()->fullUrl(), 'temp_product=1') !== false) ? 'active' : '' }}">
                                        <a class="nav-link " href="{{ route('admin.item.approval_list') }}"
                                            title="{{ 'Solicitud de nuevo artículo' }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{ 'Solicitud de nuevo artículo' }}</span>
                                        </a>
                                    </li>
                                @endif
                                <li class="nav-item {{ Request::is('admin/item/reviews') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.item.reviews') }}"
                                        title="{{ 'lista de revisión' }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{ 'revisar' }}</span>
                                    </a>
                                </li>
                                <li class="nav-item {{ Request::is('admin/item/bulk-import') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.item.bulk-import') }}"
                                        title="{{ 'importación a granel' }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span
                                            class="text-truncate text-capitalize">{{ 'importación a granel' }}</span>
                                    </a>
                                </li>
                                <li class="nav-item {{ Request::is('admin/item/bulk-export') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.item.bulk-export-index') }}"
                                        title="{{ 'exportación a granel' }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span
                                            class="text-truncate text-capitalize">{{ 'exportación a granel' }}</span>
                                    </a>
                                </li>
                                <li class="nav-item {{ Request::is('admin/item/quick-price-update') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.item.quick-price-update') }}"
                                        title="{{ 'Actualización de Precios' }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span
                                            class="text-truncate text-capitalize">{{ 'Actualización de Precios' }}</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endif
                    <!-- End Food -->

                    <!-- Store Store -->
                    <li class="nav-item">
                        <small class="nav-subtitle"
                            title="{{ 'sección de tienda' }}">{{ 'gestión de tienda' }}</small>
                        <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                    </li>

                    @if (\App\CentralLogics\Helpers::module_permission_check('store'))
                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/store*') && !Request::is('admin/store/withdraw_list') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:"
                                title="{{ 'Negocios' }}">
                                <i class="tio-filter-list nav-icon"></i>
                                <span
                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ 'Negocios' }}</span>
                            </a>
                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                style="display:{{ Request::is('admin/store*') ? 'block' : 'none' }}">
                                <li
                                    class="navbar-vertical-aside-has-menu {{ Request::is('admin/store/add') ? 'active' : '' }}">
                                    <a class="js-navbar-vertical-aside-menu-link nav-link"
                                        href="{{ route('admin.store.add') }}" title="{{ 'agregar tienda' }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">
                                            {{ 'agregar tienda' }}
                                        </span>
                                    </a>
                                </li>

                                <li
                                    class="navbar-item {{ Request::is('admin/store/list') || Request::is('admin/store/view/*') ? 'active' : '' }}">
                                    <a class="js-navbar-vertical-aside-menu-link nav-link"
                                        href="{{ route('admin.store.list') }}"
                                        title="{{ 'lista de tiendas' }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{ 'Negocios' }}
                                            {{ 'lista' }}</span>
                                    </a>
                                </li>
                                <li class="navbar-item {{ Request::is('admin/store/pending-requests') ? 'active' : '' }}">
                                    <a class="js-navbar-vertical-aside-menu-link nav-link"
                                        href="{{ route('admin.store.pending-requests') }}"
                                        title="{{ 'solicitudes pendientes' }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span
                                            class="text-truncate text-capitalize">{{ 'nuevas solicitudes de unión' }}</span>
                                    </a>
                                </li>

                                <li class="navbar-item {{ Request::is('admin/store/recommended-store') ? 'active' : '' }}">
                                    <a class="js-navbar-vertical-aside-menu-link nav-link"
                                        href="{{ route('admin.store.recommended_store') }}"
                                        title="{{ 'solicitudes pendientes' }}">
                                        <span class="tio-hot"></span>
                                        <span
                                            class="text-truncate text-capitalize">{{ 'Tienda recomendada' }}</span>
                                    </a>
                                </li>


                                <li class="nav-item {{ Request::is('admin/store/bulk-import') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.store.bulk-import') }}"
                                        title="{{ 'importación a granel' }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span
                                            class="text-truncate text-capitalize">{{ 'importación a granel' }}</span>
                                    </a>
                                </li>
                                <li class="nav-item {{ Request::is('admin/store/bulk-export') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.store.bulk-export-index') }}"
                                        title="{{ 'exportación a granel' }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span
                                            class="text-truncate text-capitalize">{{ 'exportación a granel' }}</span>
                                    </a>
                                </li>

                            </ul>
                        </li>
                    @endif
                    <!-- End Store -->
                    <!-- DeliveryMan -->
                    @if (\App\CentralLogics\Helpers::module_permission_check('deliveryman'))
                        <li class="nav-item">
                            <small class="nav-subtitle"
                                title="{{ 'sección de repartidor' }}">{{ 'gestión de repartidor' }}</small>
                            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                        </li>
                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/delivery-man/add') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                                href="{{ route('admin.delivery-man.add') }}"
                                title="{{ 'agregar repartidor' }}">
                                <i class="tio-running nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ 'agregar repartidor' }}
                                </span>
                            </a>
                        </li>

                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/delivery-man/new') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link text-capitalize"
                                href="{{ route('admin.delivery-man.new') }}"
                                title="{{ 'nuevas solicitudes de unión' }}">
                                <i class="tio-man nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ 'nuevas solicitudes de unión' }}
                                </span>
                            </a>
                        </li>


                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/delivery-man/list') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                                href="{{ route('admin.delivery-man.list') }}"
                                title="{{ 'lista de repartidor' }}">
                                <i class="tio-filter-list nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ 'lista de repartidor' }}
                                </span>
                            </a>
                        </li>

                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::routeIs('admin.message.list.delivery-support') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                                href="{{ route('admin.message.list.delivery-support') }}"
                                title="{{ 'menú de chat de soporte de entrega de administrador' }}">
                                <i class="tio-chat nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ 'menú de chat de soporte de entrega de administrador' }}
                                </span>
                            </a>
                        </li>

                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/delivery-man/reviews/list') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                                href="{{ route('admin.delivery-man.reviews.list') }}"
                                title="{{ 'opiniones' }}">
                                <i class="tio-star-outlined nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ 'opiniones' }}
                                </span>
                            </a>
                        </li>

                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/business-settings/cash-settings*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                                href="{{ route('admin.business-settings.cash-settings') }}"
                                title="Configuración de Efectivo">
                                <i class="tio-wallet nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    Configuración de Efectivo
                                </span>
                            </a>
                        </li>

                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/delivery-man/cash-deposit-list*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                                href="{{ route('admin.delivery-man.cash-deposit-list') }}"
                                title="Auditoría de Depósitos">
                                <i class="tio-money nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    Auditoría de Depósitos
                                </span>
                            </a>
                        </li>

                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/delivery-man/cash-heatmap*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                                href="{{ route('admin.delivery-man.cash-heatmap') }}"
                                title="Mapa de Calor de Efectivo">
                                <i class="tio-map nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    Mapa de Calor de Efectivo
                                </span>
                            </a>
                        </li>

                    @endif
                    <!-- End DeliveryMan -->

                    <!-- Customer Section -->
                    @if (\App\CentralLogics\Helpers::module_permission_check('customer_management'))
                        <li class="nav-item">
                            <small class="nav-subtitle"
                                title="{{ 'sección de clientes' }}">{{ 'gestión de clientes' }}</small>
                            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                        </li>
                        <!-- Custommer -->

                        <li
                            class="navbar-vertical-aside-has-menu {{ (Request::is('admin/customer/list') || Request::is('admin/customer/view*')) ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link" href="{{ route('admin.customer.list') }}"
                                title="{{ 'Clientes' }}">
                                <i class="tio-poi-user nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ 'Clientes' }}
                                </span>
                            </a>
                        </li>

                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/customer/wallet*') ? 'active' : '' }}">

                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:"
                                title="{{ 'billetera del cliente' }}">
                                <i class="tio-wallet nav-icon"></i>
                                <span
                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate  text-capitalize">
                                    {{ 'billetera del cliente' }}
                                </span>
                            </a>

                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                style="display:{{ Request::is('admin/customer/wallet*') ? 'block' : 'none' }}">
                                <li class="nav-item {{ Request::is('admin/customer/wallet/add-fund') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.customer.wallet.add-fund') }}"
                                        title="{{ 'agregar fondo' }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span
                                            class="text-truncate text-capitalize">{{ 'agregar fondo' }}</span>
                                    </a>
                                </li>

                                <li class="nav-item {{ Request::is('admin/customer/wallet/report*') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.customer.wallet.report') }}"
                                        title="{{ 'informe' }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span
                                            class="text-truncate text-capitalize">{{ 'informe' }}</span>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/customer/loyalty-point*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link  nav-link-toggle" href="javascript:"
                                title="{{ 'punto de fidelización del cliente' }}">
                                <i class="tio-medal nav-icon"></i>
                                <span
                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate  text-capitalize">
                                    {{ 'punto de fidelización del cliente' }}
                                </span>
                            </a>

                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                style="display:{{ Request::is('admin/customer/loyalty-point*') ? 'block' : 'none' }}">
                                <li
                                    class="nav-item {{ Request::is('admin/customer/loyalty-point/report*') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.customer.loyalty-point.report') }}"
                                        title="{{ 'informe' }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span
                                            class="text-truncate text-capitalize">{{ 'informe' }}</span>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- End Custommer -->
                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/customer/subscribed') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                                href="{{ route('admin.customer.subscribed') }}" title="{{'correos electrónicos suscritos'}}">
                                <i class="tio-email-outlined nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ 'lista de correo suscrita' }}
                                </span>
                            </a>
                        </li>
                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/contact/contact-list') ? 'active' : '' }}">
                            <a class="nav-link " href="{{ route('admin.contact.contact-list') }}"
                                title="{{ 'mensajes de contacto' }}">
                                <span class="tio-message nav-icon"></span>
                                <span class="text-truncate">{{ 'mensajes de contacto' }}</span>
                            </a>
                        </li>
                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/customer/settings') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                                href="{{ route('admin.customer.settings') }}"
                                title="{{ 'Configuración del cliente' }}">
                                <i class="tio-settings nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ 'Configuración del cliente' }}
                                </span>
                            </a>
                        </li>
                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::routeIs('admin.message.list') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link" href="{{ route('admin.message.list') }}"
                                title="{{ 'charla con el cliente' }}">
                                <i class="tio-chat nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ 'Chat con el cliente' }}
                                </span>
                            </a>
                        </li>
                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::routeIs('admin.message.list.delivery-support') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                                href="{{ route('admin.message.list.delivery-support') }}"
                                title="{{ 'menú de chat de soporte de entrega de administrador' }}">
                                <i class="tio-support nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ 'menú de chat de soporte de entrega de administrador' }}
                                </span>
                            </a>
                        </li>
                    @endif
                    <!-- End customer Section -->

                    <!-- Business Section-->
                    <li class="nav-item">
                        <small class="nav-subtitle"
                            title="{{ 'sección de negocios' }}">{{ 'gestión empresarial' }}</small>
                        <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                    </li>

                    <!-- withdraw -->
                    @if (\App\CentralLogics\Helpers::module_permission_check('withdraw_list'))
                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/store/withdraw*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                                href="{{ route('admin.store.withdraw_list') }}"
                                title="{{ 'retiros en tienda' }}">
                                <i class="tio-table nav-icon"></i>
                                <span
                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ 'retiros en tienda' }}</span>
                            </a>
                        </li>
                    @endif
                    <!-- End withdraw -->
                    <!-- account -->
                    @if (\App\CentralLogics\Helpers::module_permission_check('collect_cash'))
                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/account-transaction*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                                href="{{ route('admin.account-transaction.index') }}"
                                title="{{ 'recoger dinero en efectivo' }}">
                                <i class="tio-money nav-icon"></i>
                                <span
                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ 'recoger dinero en efectivo' }}</span>
                            </a>
                        </li>
                    @endif
                    <!-- End account -->

                    <!-- provide_dm_earning -->
                    @if (\App\CentralLogics\Helpers::module_permission_check('provide_dm_earning'))
                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/provide-deliveryman-earnings*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                                href="{{ route('admin.provide-deliveryman-earnings.index') }}"
                                title="{{ 'repartidores ganando proporcionar' }}">
                                <i class="tio-send nav-icon"></i>
                                <span
                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ 'repartidores ganando proporcionar' }}</span>
                            </a>
                        </li>
                    @endif
                    <!-- End provide_dm_earning -->

                    <!-- Business Settings -->
                    @if (\App\CentralLogics\Helpers::module_permission_check('settings'))
                        <li class="nav-item">
                            <small class="nav-subtitle"
                                title="{{ 'entornos empresariales' }}">{{ 'entornos empresariales' }}</small>
                            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                        </li>
                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/business-settings/business-setup') ? 'active' : '' }}">
                            <a class="nav-link " href="{{ route('admin.business-settings.business-setup') }}"
                                title="{{ 'configuración de negocios' }}">
                                <span class="tio-settings nav-icon"></span>
                                <span class="text-truncate">{{ 'configuración de negocios' }}</span>
                            </a>
                        </li>
                        <li
                            class="navbar-vertical-aside-has-menu {{Request::is('admin/business-settings/social-media') ? 'active' : ''}}">
                            <a class="nav-link " href="{{route('admin.business-settings.social-media.index')}}"
                                title="{{'Redes Sociales'}}">
                                <span class="tio-facebook nav-icon"></span>
                                <span class="text-truncate">{{'Redes Sociales'}}</span>
                            </a>
                        </li>
                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/business-settings/payment-method') ? 'active' : '' }}">
                            <a class="nav-link " href="{{ route('admin.business-settings.payment-method') }}"
                                title="{{ 'métodos de pago' }}">
                                <span class="tio-atm nav-icon"></span>
                                <span class="text-truncate">{{ 'métodos de pago' }}</span>
                            </a>
                        </li>
                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/business-settings/mail-config') ? 'active' : '' }}">
                            <a class="nav-link " href="{{ route('admin.business-settings.mail-config') }}"
                                title="{{ 'configuración de correo' }}">
                                <span class="tio-email nav-icon"></span>
                                <span class="text-truncate">{{ 'configuración de correo' }}</span>
                            </a>
                        </li>
                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/business-settings/business-setup/dynamic-pricing') ? 'active' : '' }}">
                            <a class="nav-link "
                                href="{{ route('admin.business-settings.business-setup', ['tab' => 'dynamic-pricing']) }}"
                                title="Tarifa dinámica">
                                <span class="tio-chart-bar-4 nav-icon"></span>
                                <span class="text-truncate">Tarifa dinámica</span>
                            </a>
                        </li>
                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/business-settings/sms-module') ? 'active' : '' }}">
                            <a class="nav-link " href="{{ route('admin.business-settings.sms-module') }}"
                                title="{{ 'módulo del sistema sms' }}">
                                <span class="tio-message nav-icon"></span>
                                <span class="text-truncate">{{ 'módulo del sistema sms' }}</span>
                            </a>
                        </li>

                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/business-settings/fcm-index') ? 'active' : '' }}">
                            <a class="nav-link " href="{{ route('admin.business-settings.fcm-index') }}"
                                title="{{ 'configuración de notificaciones' }}">
                                <span class="tio-notifications nav-icon"></span>
                                <span class="text-truncate">{{ 'configuración de notificaciones' }}</span>
                            </a>
                        </li>


                    @endif
                    <!-- End Business Settings -->

                    <!-- web & adpp Settings -->
                    @if (\App\CentralLogics\Helpers::module_permission_check('settings'))
                        <li class="nav-item">
                            <small class="nav-subtitle"
                                title="{{ 'entornos empresariales' }}">{{ 'configuración web y de aplicaciones' }}</small>
                            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                        </li>
                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/business-settings/app-settings*') ? 'active' : '' }}">
                            <a class="nav-link " href="{{ route('admin.business-settings.app-settings') }}"
                                title="{{ 'configuración de la aplicación' }}">
                                <span class="tio-android nav-icon"></span>
                                <span class="text-truncate">{{ 'configuración de la aplicación' }}</span>
                            </a>
                        </li>
                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/business-settings/landing-page-settings*') ? 'active' : '' }}">
                            <a class="nav-link "
                                href="{{ route('admin.business-settings.landing-page-settings', 'index') }}"
                                title="{{ 'configuración de la página de destino' }}">
                                <span class="tio-website nav-icon"></span>
                                <span class="text-truncate">{{ 'configuración de la página de destino' }}</span>
                            </a>
                        </li>
                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/business-settings/config*') ? 'active' : '' }}">
                            <a class="nav-link " href="{{ route('admin.business-settings.config-setup') }}"
                                title="{{ 'API de terceros' }}">
                                <span class="tio-key nav-icon"></span>
                                <span class="text-truncate">{{ 'API de terceros' }}</span>
                            </a>
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
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/business-settings/pages*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:"
                                title="{{ 'configuración de páginas' }}">
                                <i class="tio-pages nav-icon"></i>
                                <span
                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ 'configuración de páginas' }}</span>
                            </a>
                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                style="display:{{ Request::is('admin/business-settings/pages*') ? 'block' : 'none' }}">

                                <li
                                    class="nav-item {{ Request::is('admin/business-settings/pages/terms-and-conditions') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.business-settings.terms-and-conditions') }}"
                                        title="{{ 'términos y condiciones' }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{ 'términos y condiciones' }}</span>
                                    </a>
                                </li>

                                <li
                                    class="nav-item {{ Request::is('admin/business-settings/pages/privacy-policy') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.business-settings.privacy-policy') }}"
                                        title="{{ 'política de privacidad' }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{ 'política de privacidad' }}</span>
                                    </a>
                                </li>

                                <li
                                    class="nav-item {{ Request::is('admin/business-settings/pages/about-us') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.business-settings.about-us') }}"
                                        title="{{ 'sobre nosotros' }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{ 'sobre nosotros' }}</span>
                                    </a>
                                </li>
                                <li
                                    class="nav-item {{ Request::is('admin/business-settings/pages/refund') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.business-settings.refund') }}"
                                        title="{{ 'Política de reembolso' }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{ 'Política de reembolso' }}</span>
                                    </a>
                                </li>

                                <li
                                    class="nav-item {{ Request::is('admin/business-settings/pages/cancelation') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.business-settings.cancelation') }}"
                                        title="{{ 'Política de Cancelación' }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{ 'Política de Cancelación' }}</span>
                                    </a>
                                </li>


                                <li
                                    class="nav-item {{ Request::is('admin/business-settings/pages/shipping-policy') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.business-settings.shipping-policy') }}"
                                        title="{{ 'política de envío' }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{ 'Política de envío' }}</span>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/file-manager*') ? 'active' : '' }}">
                            <a class="nav-link " href="{{ route('admin.file-manager.index') }}"
                                title="{{ 'galería' }}">
                                <span class="tio-album nav-icon"></span>
                                <span class="text-truncate text-capitalize">{{ 'galería' }}</span>
                            </a>
                        </li>

                        <li
                            class="navbar-vertical-aside-has-menu {{Request::is('admin/social-login/view') ? 'active' : ''}}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                                href="{{route('admin.social-login.view')}}">
                                <i class="tio-twitter nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{'inicio de sesión social'}}
                                </span>
                            </a>
                        </li>

                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/business-settings/recaptcha*') ? 'active' : '' }}">
                            <a class="nav-link " href="{{ route('admin.business-settings.recaptcha_index') }}"
                                title="{{ 'reCaptcha' }}">
                                <span class="tio-top-security-outlined nav-icon"></span>
                                <span class="text-truncate">{{ 'reCaptcha' }}</span>
                            </a>
                        </li>

                        <li
                            class="navbar-vertical-aside-has-menu {{Request::is('admin/business-settings/db-index') ? 'active' : ''}}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                                href="{{route('admin.business-settings.db-index')}}"
                                title="{{'limpiar base de datos'}}">
                                <i class="tio-cloud nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{'limpiar base de datos'}}
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
                    <!-- End web & adpp Settings -->

                    <!-- Report -->
                    @if (\App\CentralLogics\Helpers::module_permission_check('report'))
                        <li class="nav-item">
                            <small class="nav-subtitle"
                                title="{{ 'informe y análisis' }}">{{ 'informe y análisis' }}</small>
                            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                        </li>

                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/report/item-wise-report') ? 'active' : '' }}">
                            <a class="nav-link " href="{{ route('admin.report.item-wise-report') }}"
                                title="{{ 'informe del artículo' }}">
                                <span class="tio-chart-bar-1 nav-icon"></span>
                                <span class="text-truncate">{{ 'informe del artículo' }}</span>
                            </a>
                        </li>
                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/report/stock-report') ? 'active' : '' }}">
                            <a class="nav-link " href="{{ route('admin.report.stock-report') }}"
                                title="{{ 'artículo de stock limitado' }}">
                                <span class="tio-chart-bar-4 nav-icon"></span>
                                <span
                                    class="text-truncate text-capitalize">{{ 'artículo de stock limitado' }}</span>
                            </a>
                        </li>


                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/report/store-wise-report') ? 'active' : '' }}">
                            <a class="nav-link " href="{{ route('admin.report.store-summary-report') }}"
                                title="{{ 'informe de tienda' }}">
                                <span class="tio-home nav-icon"></span>
                                <span class="text-truncate">{{ 'informe de la tienda' }}</span>
                            </a>
                        </li>


                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/report/order-report') ? 'active' : '' }}">
                            <a class="nav-link " href="{{ route('admin.report.order-report') }}"
                                title="{{ 'informe de pedido' }}">
                                <span class="tio-voice nav-icon"></span>
                                <span class="text-truncate text-capitalize">{{ 'informe de pedido' }}</span>
                            </a>
                        </li>

                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/report/transaction-report') ? 'active' : '' }}">
                            <a class="nav-link " href="{{ route('admin.report.transaction-report') }}"
                                title="{{ 'informe de transacciones' }}">
                                <span class="tio-chart-pie-1 nav-icon"></span>
                                <span class="text-truncate">{{ 'informe de transacciones' }}</span>
                            </a>
                        </li>


                        <li
                            class="navbar-vertical-aside-has-menu {{ Request::is('admin/report/expense-report') ? 'active' : '' }}">
                            <a class="nav-link " href="{{ route('admin.report.expense-report') }}"
                                title="{{ 'informe de gastos' }}">
                                <span class="tio-money nav-icon"></span>
                                <span class="text-truncate">{{ 'informe de gastos' }}</span>
                            </a>
                        </li>

                    @endif

                    <!-- Employee-->

                    <li class="nav-item">
                        <small class="nav-subtitle"
                            title="{{ 'mango de empleado' }}">{{ 'empleado' }}
                            {{ 'gestión' }}</small>
                        <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                    </li>

                    @if (\App\CentralLogics\Helpers::module_permission_check('custom_role'))
                        <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/custom-role*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                                href="{{ route('admin.custom-role.create') }}"
                                title="{{ 'Rol del empleado' }}">
                                <i class="tio-incognito nav-icon"></i>
                                <span
                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ 'Rol del empleado' }}</span>
                            </a>
                        </li>
                    @endif

                    @if (\App\CentralLogics\Helpers::module_permission_check('employee'))
                        <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/employee*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:"
                                title="{{ 'Empleado' }}">
                                <i class="tio-user nav-icon"></i>
                                <span
                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ 'empleados' }}</span>
                            </a>
                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                style="display:{{ Request::is('admin/employee*') ? 'block' : 'none' }}">
                                <li class="nav-item {{ Request::is('admin/employee/add-new') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.employee.add-new') }}"
                                        title="{{ 'agregar nuevo empleado' }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{ 'agregar nuevo' }}</span>
                                    </a>
                                </li>
                                <li class="nav-item {{ Request::is('admin/employee/list') ? 'active' : '' }}">
                                    <a class="nav-link " href="{{ route('admin.employee.list') }}"
                                        title="{{ 'lista de empleados' }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{ 'lista' }}</span>
                                    </a>
                                </li>

                            </ul>
                        </li>
                    @endif
                    <!-- End Employee -->


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
        $(window).on('load', function () {
            if ($(".navbar-vertical-content li.active").length) {
                $('.navbar-vertical-content').animate({
                    scrollTop: $(".navbar-vertical-content li.active").offset().top - 150
                }, 10);
            }
        });

        var $rows = $('#navbar-vertical-content li');
        $('#search-sidebar-menu').keyup(function () {
            var val = $.trim($(this).val()).replace(/ +/g, ' ').toLowerCase();

            $rows.show().filter(function () {
                var text = $(this).text().replace(/\s+/g, ' ').toLowerCase();
                return !~text.indexOf(val);
            }).hide();
        });
    </script>
@endpush