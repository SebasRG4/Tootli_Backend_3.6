<div id="sidebarMain" class="d-none">
    <aside class="js-navbar-vertical-aside navbar navbar-vertical-aside navbar-vertical navbar-vertical-fixed navbar-expand-xl navbar-bordered  ">
        <div class="navbar-vertical-container">
            <div class="navbar-brand-wrapper justify-content-between">
                <!-- Logo -->
                @php($store_logo = \App\Models\BusinessSetting::where(['key' => 'logo'])->first())
                <a class="navbar-brand" href="{{ route('admin.dashboard') }}" aria-label="Front">
                       <img class="navbar-brand-logo initial--36 onerror-image onerror-image" data-onerror-image="{{ asset('assets/admin/img/160x160/img2.jpg') }}"
                    src="{{\App\CentralLogics\Helpers::get_full_url('business', $store_logo?->value?? '', $store_logo?->storage[0]?->value ?? 'public','favicon')}}"
                    alt="Logo">
                    <img class="navbar-brand-logo-mini initial--36 onerror-image onerror-image" data-onerror-image="{{ asset('assets/admin/img/160x160/img2.jpg') }}"
                    src="{{\App\CentralLogics\Helpers::get_full_url('business', $store_logo?->value?? '', $store_logo?->storage[0]?->value ?? 'public','favicon')}}"
                    alt="Logo">
                </a>
                <!-- End Logo -->

                <!-- Navbar Vertical Toggle -->
                <button type="button" class="js-navbar-vertical-aside-toggle-invoker navbar-vertical-aside-toggle btn btn-icon btn-xs btn-ghost-dark">
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
                <form autocomplete="off"   class="sidebar--search-form">
                    <div class="search--form-group">
                        <button type="button" class="btn"><i class="tio-search"></i></button>
                        <input  autocomplete="false" name="qq" type="text" class="form-control form--control" placeholder="{{ 'Menú de búsqueda...' }}" id="search">

                        <div id="search-suggestions" class="flex-wrap mt-1"></div>
                    </div>
                </form>

                <ul class="navbar-nav navbar-nav-lg nav-tabs">
                    <!-- Dashboards -->
                    <li class="navbar-vertical-aside-has-menu {{ Request::is('admin') ? 'show active' : '' }}">
                        <a class="js-navbar-vertical-aside-menu-link nav-link" href="{{ route('admin.dashboard') }}?module_id={{Config::get('module.current_module_id')}}" title="{{ 'Panel de Control' }}">
                            <i class="tio-home-vs-1-outlined nav-icon"></i>
                            <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                {{ 'Panel de Control' }}
                            </span>
                        </a>
                    </li>
                    <!-- End Dashboards -->
                    <!-- Marketing section -->
                    @if(\App\CentralLogics\Helpers::module_permission_check('pos'))
                    <li class="nav-item">
                        <small class="nav-subtitle" title="{{ 'mango de empleado' }}">{{ 'sección pos' }}</small>
                        <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                    </li>
                    <!-- Pos -->
                    <li class="navbar-vertical-aside-has-menu {{Request::is('admin/pos*')?'active':''}}">
                        <a class="js-navbar-vertical-aside-menu-link nav-link " href="{{route('admin.pos.index')}}" title="{{'Nueva venta'}}">
                            <i class="tio-shopping-basket-outlined nav-icon"></i>
                            <span class="text-truncate">{{'Nueva venta'}}</span>
                        </a>
                    </li>
                    @endif
                    <!-- Pos -->

                                        <!-- Orders -->
                    @if (\App\CentralLogics\Helpers::module_permission_check('order'))
                    <li class="nav-item">
                        <small class="nav-subtitle">{{ 'gestión de pedidos' }}</small>
                        <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                    </li>

                    <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/order') ? 'active' : '' }}">
                        <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:" title="{{ 'Pedidos' }}">
                            <i class="tio-shopping-cart nav-icon"></i>
                            <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                {{ 'Pedidos' }}
                            </span>
                        </a>
                        <ul class="js-navbar-vertical-aside-submenu nav nav-sub" style="display:{{ Request::is('admin/order*') ? 'block' : 'none' }}">
                            <li class="nav-item {{ Request::is('admin/order/list/all') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('admin.order.list', ['all']) }}" title="{{ 'todos los pedidos' }}">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate sidebar--badge-container">
                                        {{ 'todo' }}
                                        <span class="badge badge-soft-info badge-pill ml-1">
                                            {{ \App\Models\Order::StoreOrder()->module(Config::get('module.current_module_id'))->count() }}
                                        </span>
                                    </span>
                                </a>
                            </li>
                            <li class="nav-item {{ Request::is('admin/order/list/scheduled') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('admin.order.list', ['scheduled']) }}" title="{{ 'pedidos programados' }}">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate sidebar--badge-container">
                                        {{ 'programado' }}
                                        <span class="badge badge-soft-info badge-pill ml-1">
                                            {{ \App\Models\Order::Scheduled()->StoreOrder()->module(Config::get('module.current_module_id'))->count() }}
                                        </span>
                                    </span>
                                </a>
                            </li>
                            <li class="nav-item {{ Request::is('admin/order/list/pending') ? 'active' : '' }}">
                                <a class="nav-link " href="{{ route('admin.order.list', ['pending']) }}" title="{{ 'pedidos pendientes' }}">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate sidebar--badge-container">
                                        {{ 'Pendiente' }}
                                        <span class="badge badge-soft-info badge-pill ml-1">
                                            {{ \App\Models\Order::Pending()->OrderScheduledIn(30)->StoreOrder()->module(Config::get('module.current_module_id'))->count() }}
                                        </span>
                                    </span>
                                </a>
                            </li>

                            <li class="nav-item {{ Request::is('admin/order/list/accepted') ? 'active' : '' }}">
                                <a class="nav-link " href="{{ route('admin.order.list', ['accepted']) }}" title="{{ 'pedidos aceptados' }}">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate sidebar--badge-container">
                                        {{ 'aceptado' }}
                                        <span class="badge badge-soft-success badge-pill ml-1">
                                            {{ \App\Models\Order::AccepteByDeliveryman()->OrderScheduledIn(30)->StoreOrder()->module(Config::get('module.current_module_id'))->count() }}
                                        </span>
                                    </span>
                                </a>
                            </li>
                            <li class="nav-item {{ Request::is('admin/order/list/processing') ? 'active' : '' }}">
                                <a class="nav-link " href="{{ route('admin.order.list', ['processing']) }}" title="{{ 'procesando pedidos' }}">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate sidebar--badge-container">
                                        {{ 'tratamiento' }}
                                        <span class="badge badge-soft-warning badge-pill ml-1">
                                            {{ \App\Models\Order::Preparing()->OrderScheduledIn(30)->StoreOrder()->module(Config::get('module.current_module_id'))->count() }}
                                        </span>
                                    </span>
                                </a>
                            </li>
                            <li class="nav-item {{ Request::is('admin/order/list/item_on_the_way') ? 'active' : '' }}">
                                <a class="nav-link text-capitalize" href="{{ route('admin.order.list', ['item_on_the_way']) }}" title="{{ 'orden en el camino' }}">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate sidebar--badge-container">
                                        {{ 'orden en el camino' }}
                                        <span class="badge badge-soft-warning badge-pill ml-1">
                                            {{ \App\Models\Order::ItemOnTheWay()->OrderScheduledIn(30)->StoreOrder()->module(Config::get('module.current_module_id'))->count() }}
                                        </span>
                                    </span>
                                </a>
                            </li>
                            <li class="nav-item {{ Request::is('admin/order/list/delivered') ? 'active' : '' }}">
                                <a class="nav-link " href="{{ route('admin.order.list', ['delivered']) }}" title="{{ 'pedidos entregados' }}">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate sidebar--badge-container">
                                        {{ 'Entregado' }}
                                        <span class="badge badge-soft-success badge-pill ml-1">
                                            {{ \App\Models\Order::Delivered()->StoreOrder()->module(Config::get('module.current_module_id'))->count() }}
                                        </span>
                                    </span>
                                </a>
                            </li>
                            <li class="nav-item {{ Request::is('admin/order/list/canceled') ? 'active' : '' }}">
                                <a class="nav-link " href="{{ route('admin.order.list', ['canceled']) }}" title="{{ 'pedidos cancelados' }}">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate sidebar--badge-container">
                                        {{ 'Cancelado' }}
                                        <span class="badge badge-soft-warning bg-light badge-pill ml-1">
                                            {{ \App\Models\Order::Canceled()->StoreOrder()->module(Config::get('module.current_module_id'))->count() }}
                                        </span>
                                    </span>
                                </a>
                            </li>
                            <li class="nav-item {{ Request::is('admin/order/list/failed') ? 'active' : '' }}">
                                <a class="nav-link " href="{{ route('admin.order.list', ['failed']) }}" title="{{ 'pedidos fallidos en el pago' }}">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate sidebar--badge-container text-capitalize">
                                        {{ 'pago fallido' }}
                                        <span class="badge badge-soft-danger bg-light badge-pill ml-1">
                                            {{ \App\Models\Order::failed()->StoreOrder()->module(Config::get('module.current_module_id'))->count() }}
                                        </span>
                                    </span>
                                </a>
                            </li>
                            <li class="nav-item {{ Request::is('admin/order/list/refunded') ? 'active' : '' }}">
                                <a class="nav-link " href="{{ route('admin.order.list', ['refunded']) }}" title="{{ 'pedidos reembolsados' }}">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate sidebar--badge-container">
                                        {{ 'Reembolsado' }}
                                        <span class="badge badge-soft-danger bg-light badge-pill ml-1">
                                            {{ \App\Models\Order::Refunded()->StoreOrder()->module(Config::get('module.current_module_id'))->count() }}
                                        </span>
                                    </span>
                                </a>
                            </li>

                            <li class="nav-item {{ Request::is('admin/order/offline/payment/list*') ? 'active' : '' }}">
                                <a class="nav-link " href="{{ route('admin.order.offline_verification_list', ['all']) }}" title="{{ 'Pagos sin conexión' }}">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate sidebar--badge-container">
                                        {{ 'Pagos sin conexión' }}
                                        <span class="badge badge-soft-danger bg-light badge-pill ml-1">
                                            {{ \App\Models\Order::where('payment_method', 'offline_payment')->whereHas('offline_payments')->StoreOrder()->module(Config::get('module.current_module_id'))->count() }}
                                        </span>
                                    </span>
                                </a>
                            </li>

                        </ul>
                    </li>

                    <!-- Order refund -->
                    <li
                    class="navbar-vertical-aside-has-menu {{ Request::is('admin/refund/*') ? 'active' : '' }}">
                    <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:"
                        title="{{ 'Reembolsos de pedidos' }}">
                        <i class="tio-receipt nav-icon"></i>
                        <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                            {{ 'Reembolsos de pedidos' }}
                        </span>
                    </a>
                    <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                        style="display: {{ Request::is('admin/refund*') ? 'block' : 'none' }}">

                        <li class="nav-item {{ Request::is('admin/refund/requested') ||  Request::is('admin/refund/rejected') ||Request::is('admin/refund/refunded') ? 'active' : '' }}">
                            <a class="nav-link "
                                href="{{ route('admin.refund.refund_attr', ['requested']) }}"
                                title="{{ 'Solicitudes de reembolso' }} ">
                                <span class="tio-circle nav-indicator-icon"></span>
                                <span class="text-truncate sidebar--badge-container">
                                    {{ 'Solicitudes de reembolso' }}
                                    <span class="badge badge-soft-danger badge-pill ml-1">
                                        {{ \App\Models\Order::Refund_requested()->StoreOrder()->module(Config::get('module.current_module_id'))->count() }}
                                    </span>
                                </span>
                            </a>
                        </li>

                        {{--  {{-- <li class="nav-item {{ Request::is('admin/refund/settings') ? 'active' : '' }}">
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
                    @endif
                    <!-- End Orders -->


                <!-- Marketing section -->
                    @if(\App\CentralLogics\Helpers::module_permission_check('campaign')||
                        \App\CentralLogics\Helpers::module_permission_check('banner') ||
                        \App\CentralLogics\Helpers::module_permission_check('coupon')||
                        \App\CentralLogics\Helpers::module_permission_check('notification') ||
                        \App\CentralLogics\Helpers::module_permission_check('advertisement')
                        )
                <li class="nav-item">
                    <small class="nav-subtitle" title="{{ 'Gestión de promociones' }}">{{ 'Gestión de promociones' }}</small>
                    <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                </li>
                <!-- Campaign -->
                @if (\App\CentralLogics\Helpers::module_permission_check('campaign'))
                <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/campaign') ? 'active' : '' }}">
                    <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:" title="{{ 'campañas' }}">
                        <i class="tio-layers-outlined nav-icon"></i>
                        <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ 'campañas' }}</span>
                    </a>
                    <ul class="js-navbar-vertical-aside-submenu nav nav-sub" style="display:{{ Request::is('admin/campaign*') ? 'block' : 'none' }}">

                        <li class="nav-item {{ Request::is('admin/campaign/basic/*') ? 'active' : '' }}">
                            <a class="nav-link " href="{{ route('admin.campaign.list', 'basic') }}" title="{{ 'campañas básicas' }}">
                                <span class="tio-circle nav-indicator-icon"></span>
                                <span class="text-truncate">{{ 'campañas básicas' }}</span>
                            </a>
                        </li>
                        <li class="nav-item {{ Request::is('admin/campaign/item/*') ? 'active' : '' }}">
                            <a class="nav-link " href="{{ route('admin.campaign.list', 'item') }}" title="{{ 'campañas alimentarias' }}">
                                <span class="tio-circle nav-indicator-icon"></span>
                                <span class="text-truncate">{{ 'campañas alimentarias' }}</span>
                            </a>
                        </li>
                    </ul>
                </li>
                @endif
                <!-- End Campaign -->
                <!-- Banner -->
                @if (\App\CentralLogics\Helpers::module_permission_check('banner'))
                <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/banner*') ? 'active' : '' }}">
                    <a class="js-navbar-vertical-aside-menu-link nav-link" href="{{ route('admin.banner.add-new') }}" title="{{ 'pancartas' }}">
                        <i class="tio-image nav-icon"></i>
                        <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ 'pancartas' }}</span>
                    </a>
                </li>
                <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/promotional-banner*') ? 'active' : '' }}">
                    <a class="js-navbar-vertical-aside-menu-link nav-link" href="{{ route('admin.promotional-banner.add-new') }}" title="{{ 'otras pancartas' }}">
                        <i class="tio-image nav-icon"></i>
                        <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ 'otras pancartas' }}</span>
                    </a>
                </li>
                @endif
                <!-- End Banner -->
                <!-- Coupon -->
                @if (\App\CentralLogics\Helpers::module_permission_check('coupon'))
                <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/coupon*') ? 'active' : '' }}">
                    <a class="js-navbar-vertical-aside-menu-link nav-link" href="{{ route('admin.coupon.add-new') }}" title="{{ 'cupones' }}">
                        <i class="tio-gift nav-icon"></i>
                        <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ 'cupones' }}</span>
                    </a>
                </li>
                @endif
                <!-- End Coupon -->
                <!-- Notification -->
                @if (\App\CentralLogics\Helpers::module_permission_check('notification'))
                <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/notification*') ? 'active' : '' }}">
                    <a class="js-navbar-vertical-aside-menu-link nav-link" href="{{ route('admin.notification.add-new') }}" title="{{ 'notificación push' }}">
                        <i class="tio-notifications nav-icon"></i>
                        <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                            {{ 'notificación push' }}
                        </span>
                    </a>
                </li>
                @endif
                <!-- End Notification -->

            <!-- advertisement -->

            @if (\App\CentralLogics\Helpers::module_permission_check('advertisement'))
                <li
                    class="navbar-vertical-aside-has-menu  @yield('advertisement')">
                    <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:"
                        title="{{ 'anuncio' }}">
                        <i class="tio-tv-old nav-icon"></i>
                        <span
                            class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ 'anuncio' }}</span>
                    </a>
                    <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                        style="display: {{ Request::is('admin/advertisement*') ? 'block' : 'none' }}">

                        <li class="nav-item @yield('advertisement_create')">
                            <a class="nav-link " href="{{ route('admin.advertisement.create') }}"
                                title="{{ 'Nuevo anuncio' }}">
                                <span class="tio-circle nav-indicator-icon"></span>
                                <span class="text-truncate">{{ 'Nuevo anuncio' }}</span>
                            </a>
                        </li>
                        <li class="nav-item @yield('advertisement_request')">
                            <a class="nav-link " href="{{ route('admin.advertisement.requestList') }}"
                                title="{{ 'Solicitudes de anuncios' }}">
                                <span class="tio-circle nav-indicator-icon"></span>
                                <span class="text-truncate">{{ 'Solicitudes de anuncios' }}</span>
                            </a>
                        </li>
                        <li class="nav-item @yield('advertisement_list')">
                            <a class="nav-link " href="{{ route('admin.advertisement.index') }}"
                                title="{{ 'Lista de anuncios' }}">
                                <span class="tio-circle nav-indicator-icon"></span>
                                <span class="text-truncate">{{ 'Lista de anuncios' }}</span>
                            </a>
                        </li>
                    </ul>
                </li>
            @endif
            <!-- End advertisement -->
                    @endif
                <!-- End marketing section -->

                    @if(\App\CentralLogics\Helpers::module_permission_check('category')||
                        \App\CentralLogics\Helpers::module_permission_check('addon')||
                        \App\CentralLogics\Helpers::module_permission_check('item')
                        )
                        <li class="nav-item">
                            <small class="nav-subtitle" title="{{ 'sección de artículos' }}">{{ 'gestión de alimentos' }}</small>
                            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                        </li>

                        <!-- Category -->
                        @if (\App\CentralLogics\Helpers::module_permission_check('category'))
                            <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/category*') ? 'active' : '' }}">
                                <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:" title="{{ 'categorias' }}">
                                    <i class="tio-category nav-icon"></i>
                                    <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ 'categorias' }}</span>
                                </a>
                                <ul class="js-navbar-vertical-aside-submenu nav nav-sub"  style="display:{{ Request::is('admin/category*') ? 'block' : 'none' }}">
                                    <li class="nav-item  @yield('main_category') {{ request()->input('position') == 0 && Request::is('admin/category/add') ? 'active' : '' }}">
                                        <a class="nav-link " href="{{ route('admin.category.add',['position'=>0]) }}" title="{{ 'categoría' }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{ 'categoría' }}</span>
                                        </a>
                                    </li>

                                    <li class="nav-item   @yield('sub_category') {{ request()->input('position') == 1 && Request::is('admin/category/add') ? 'active' : '' }}">
                                        <a class="nav-link " href="{{ route('admin.category.add',['position'=>1]) }}" title="{{ 'subcategoría' }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{ 'subcategoría' }}</span>
                                        </a>
                                    </li>

                                    <li class="nav-item {{ Request::is('admin/category/bulk-import') ? 'active' : '' }}">
                                        <a class="nav-link " href="{{ route('admin.category.bulk-import') }}" title="{{ 'importación a granel' }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate text-capitalize">{{ 'importación a granel' }}</span>
                                        </a>
                                    </li>
                                    <li class="nav-item {{ Request::is('admin/category/bulk-export') ? 'active' : '' }}">
                                        <a class="nav-link " href="{{ route('admin.category.bulk-export-index') }}" title="{{ 'exportación a granel' }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate text-capitalize">{{ 'exportación a granel' }}</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @endif
                        <!-- End Category -->

                        <!-- Attributes -->
                        {{-- @if (\App\CentralLogics\Helpers::module_permission_check('attribute'))
                        <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/attribute*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link" href="{{ route('admin.attribute.add-new') }}" title="{{ 'atributos' }}">
                                <i class="tio-apps nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ 'atributos' }}
                                </span>
                            </a>
                        </li>
                        @endif --}}
                        <!-- End Attributes -->

                        <!-- Unit -->
                        {{-- @if (\App\CentralLogics\Helpers::module_permission_check('unit'))
                        <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/unit*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link" href="{{ route('admin.unit.index') }}" title="{{ 'unidades' }}">
                                <i class="tio-ruler nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate text-capitalize">
                                    {{ 'unidades' }}
                                </span>
                            </a>
                        </li>
                        @endif --}}
                        <!-- End Unit -->

                        <!-- AddOn -->
                        @if (\App\CentralLogics\Helpers::module_permission_check('addon'))
                            <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/addon*') ? 'active' : '' }}">
                                <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:" title="{{ 'complementos' }}">
                                    <i class="tio-add-circle-outlined nav-icon"></i>
                                    <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ 'complementos' }}</span>
                                </a>
                                <ul class="js-navbar-vertical-aside-submenu nav nav-sub" style="display:{{ Request::is('admin/addon*') ? 'block' : 'none' }}">
                                    <li class="nav-item {{ Request::is('admin/addon/addon-category') ? 'active' : '' }}">
                                        <a class="nav-link " href="{{ route('admin.addon.addon-category') }}" title="{{ 'Categoría de complemento' }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{ 'Categoría de complemento' }}</span>
                                        </a>
                                    </li>
                                    <li class="nav-item {{ Request::is('admin/addon/add-new') ? 'active' : '' }}">
                                        <a class="nav-link " href="{{ route('admin.addon.add-new') }}" title="{{ 'lista de complementos' }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{ 'lista' }}</span>
                                        </a>
                                    </li>

                                    <li class="nav-item {{ Request::is('admin/addon/bulk-import') ? 'active' : '' }}">
                                        <a class="nav-link " href="{{ route('admin.addon.bulk-import') }}" title="{{ 'importación a granel' }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate text-capitalize">{{ 'importación a granel' }}</span>
                                        </a>
                                    </li>
                                    <li class="nav-item {{ Request::is('admin/addon/bulk-export') ? 'active' : '' }}">
                                        <a class="nav-link " href="{{ route('admin.addon.bulk-export-index') }}" title="{{ 'exportación a granel' }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate text-capitalize">{{ 'exportación a granel' }}</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @endif
                        <!-- End AddOn -->
                        <!-- Food -->
                        @if (\App\CentralLogics\Helpers::module_permission_check('item'))
                            <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/item*') ? 'active' : '' }}">
                                <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:" title="{{ 'Configuración de alimentos' }}">
                                    <i class="tio-premium-outlined nav-icon"></i>
                                    <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate text-capitalize">{{ 'Configuración de alimentos' }}</span>
                                </a>
                                <ul class="js-navbar-vertical-aside-submenu nav nav-sub" style="display:{{ Request::is('admin/item*') ? 'block' : 'none' }}">
                                    <li class="nav-item {{ Request::is('admin/item/add-new') || (Request::is('admin/item/edit/*') && strpos(request()->fullUrl(), 'product_gellary=1') !== false  )  ? 'active' : '' }}">
                                        <a class="nav-link " href="{{ route('admin.item.add-new') }}" title="{{ 'agregar nuevo' }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{ 'agregar nuevo' }}</span>
                                        </a>
                                    </li>
                                    <li class="nav-item {{ Request::is('admin/item/list') || (Request::is('admin/item/edit/*') && (strpos(request()->fullUrl(), 'temp_product=1') == false && strpos(request()->fullUrl(), 'product_gellary=1') == false  ) ) ? 'active' : '' }}">
                                        <a class="nav-link " href="{{ route('admin.item.list') }}" title="{{ 'lista de alimentos' }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{ 'lista' }}</span>
                                        </a>
                                    </li>
                                    {{-- @if (\App\CentralLogics\Helpers::get_mail_status('product_gallery')) --}}
                                    <li class="nav-item {{  Request::is('admin/item/product-gallery') ? 'active' : '' }}">
                                        <a class="nav-link " href="{{ route('admin.item.product_gallery') }}" title="{{ 'Galería de productos' }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{ 'Galería de comida' }}</span>
                                        </a>
                                    </li>
                                    {{-- @endif --}}
                                    @if (\App\CentralLogics\Helpers::get_mail_status('product_approval'))
                                        <li class="nav-item {{  Request::is('admin/item/requested/item/view/*') || Request::is('admin/item/new/item/list') || (Request::is('admin/item/edit/*') && strpos(request()->fullUrl(), 'temp_product=1') !== false  ) ? 'active' : '' }}">
                                            <a class="nav-link " href="{{ route('admin.item.approval_list') }}" title="{{ 'Solicitud de nuevo artículo' }}">
                                                <span class="tio-circle nav-indicator-icon"></span>
                                                <span class="text-truncate">{{ 'Nueva Solicitud de Alimentos' }}</span>
                                            </a>
                                        </li>
                                    @endif
                                    <li class="nav-item {{ Request::is('admin/item/reviews') ? 'active' : '' }}">
                                        <a class="nav-link " href="{{ route('admin.item.reviews') }}" title="{{ 'lista de revisión' }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate">{{ 'revisar' }}</span>
                                        </a>
                                    </li>
                                    <li class="nav-item {{ Request::is('admin/item/bulk-import') ? 'active' : '' }}">
                                        <a class="nav-link " href="{{ route('admin.item.bulk-import') }}" title="{{ 'importación a granel' }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate text-capitalize">{{ 'importación a granel' }}</span>
                                        </a>
                                    </li>
                                    <li class="nav-item {{ Request::is('admin/item/bulk-export') ? 'active' : '' }}">
                                        <a class="nav-link " href="{{ route('admin.item.bulk-export-index') }}" title="{{ 'exportación a granel' }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate text-capitalize">{{ 'exportación a granel' }}</span>
                                        </a>
                                    </li>
                                    <li class="nav-item {{ Request::is('admin/item/quick-price-update') ? 'active' : '' }}">
                                        <a class="nav-link " href="{{ route('admin.item.quick-price-update') }}"
                                            title="{{ 'Actualización de Precios' }}">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate text-capitalize">{{ 'Actualización de Precios' }}</span>
                                        </a>
                                    </li>
                                    <li class="nav-item {{ Request::is('admin/item/bulk-import-text') ? 'active' : '' }}">
                                        <a class="nav-link " href="{{ route('admin.item.bulk-import-text') }}"
                                            title="Carga Rápida (Texto)">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate text-capitalize">Carga Rápida (Texto)</span>
                                        </a>
                                    </li>
                                    <li class="nav-item {{ Request::is('admin/item/express-import') ? 'active' : '' }}">
                                        <a class="nav-link " href="{{ route('admin.item.express-import') }}"
                                            title="Carga Menú con Imagen">
                                            <span class="tio-circle nav-indicator-icon"></span>
                                            <span class="text-truncate text-capitalize">Carga Menú con Imagen</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @endif
                        <!-- End Food -->
                    @endif


                <!-- Store Store -->
                    @if (\App\CentralLogics\Helpers::module_permission_check('store'))

                <li class="nav-item">
                    <small class="nav-subtitle" title="{{ 'sección de restaurante' }}">{{ 'gestión de restaurantes' }}</small>
                    <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                </li>

                <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/store/pending-requests') ? 'active' : '' }}">
                    <a class="js-navbar-vertical-aside-menu-link nav-link" href="{{ route('admin.store.pending-requests') }}" title="{{ 'nuevos restaurantes' }}">
                        <span class="tio-calendar-note nav-icon"></span>
                        <span class="text-truncate position-relative overflow-visible">
                            {{ 'nuevos restaurantes' }}
                            @php($new_str = \App\Models\Store::whereHas('vendor', function($query){
                                return $query->where('status', null);
                            })->module(Config::get('module.current_module_id'))->get())
                            @if (count($new_str)>0)

                            <span class="btn-status btn-status-danger border-0 size-8px"></span>
                            @endif
                        </span>
                    </a>
                </li>
                <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/store/add') ? 'active' : '' }}">
                    <a class="js-navbar-vertical-aside-menu-link nav-link" href="{{ route('admin.store.add') }}" title="{{ 'agregar nuevo restaurante' }}">
                        <span class="tio-add-circle nav-icon"></span>
                        <span class="text-truncate position-relative overflow-visible">
                            {{ 'agregar nuevo restaurante' }}
                        </span>
                    </a>
                </li>
                <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/store/list') ||  Request::is('admin/store/view/*') ? 'active' : '' }}">
                    <a class="js-navbar-vertical-aside-menu-link nav-link" href="{{ route('admin.store.list') }}" title="{{ 'lista de restaurantes' }}">
                        <span class="tio-layout nav-icon"></span>
                        <span class="text-truncate">{{ 'restaurantes' }}
                            {{ 'lista' }}</span>
                    </a>
                </li>

                <li class="navbar-item {{ Request::is('admin/store/recommended-store') ? 'active' : '' }}">
                    <a class="js-navbar-vertical-aside-menu-link nav-link" href="{{ route('admin.store.recommended_store') }}" title="{{ 'solicitudes pendientes' }}">
                        <span class="tio-hot  nav-icon"></span>
                        <span class="text-truncate text-capitalize">{{ 'Restaurantes recomendados' }}</span>
                    </a>
                </li>
                <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/store/bulk-import') ? 'active' : '' }}">
                    <a class="nav-link " href="{{ route('admin.store.bulk-import') }}" title="{{ 'importación a granel' }}">
                        <span class="tio-publish nav-icon"></span>
                        <span class="text-truncate text-capitalize">{{ 'importación a granel' }}</span>
                    </a>
                </li>
                <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/store/bulk-export') ? 'active' : '' }}">
                    <a class="nav-link " href="{{ route('admin.store.bulk-export-index') }}" title="{{ 'exportación a granel' }}">
                        <span class="tio-download-to nav-icon"></span>
                        <span class="text-truncate text-capitalize">{{ 'exportación a granel' }}</span>
                    </a>
                </li>
                @endif
                <!-- End Store -->

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
    $(window).on('load' , function() {
        if($(".navbar-vertical-content li.active").length) {
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
            {{--const suggestions = ['{{strtolower('Pedido')  }}', '{{ strtolower('campaña')  }}', '{{ strtolower('categoría') }}', '{{ strtolower('alimento') }}','{{ strtolower('Añadir') }}','{{ strtolower('restaurante') }}'  ];--}}
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
