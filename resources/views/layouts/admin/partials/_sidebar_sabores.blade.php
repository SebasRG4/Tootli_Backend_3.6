<div id="sidebarMain" class="d-none">
    <aside
        class="js-navbar-vertical-aside navbar navbar-vertical-aside navbar-vertical navbar-vertical-fixed navbar-expand-xl navbar-bordered  ">
        <div class="navbar-vertical-container">
            <div class="navbar-brand-wrapper justify-content-between">
                <!-- Logo -->
                @php($store_logo = \App\Models\BusinessSetting::where(['key' => 'logo'])->first())
                <a class="navbar-brand" href="{{ route('admin.dashboard') }}" aria-label="Front">
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
                <form autocomplete="off" class="sidebar--search-form">
                    <div class="search--form-group">
                        <button type="button" class="btn"><i class="tio-search"></i></button>
                        <input autocomplete="false" name="qq" type="text" class="form-control form--control"
                            placeholder="{{ translate('Search Menu...') }}" id="search">
                        <div id="search-suggestions" class="flex-wrap mt-1"></div>
                    </div>
                </form>

                <ul class="navbar-nav navbar-nav-lg nav-tabs">
                    <!-- Dashboard -->
                    <li
                        class="navbar-vertical-aside-has-menu {{ Request::is('admin/sabores/dashboard') ? 'show active' : '' }}">
                        <a class="js-navbar-vertical-aside-menu-link nav-link"
                            href="{{ route('admin.sabores.dashboard') }}" title="{{ translate('messages.dashboard') }}">
                            <i class="tio-home-vs-1-outlined nav-icon"></i>
                            <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                {{ translate('messages.dashboard') }}
                            </span>
                        </a>
                    </li>
                    <!-- End Dashboard -->

                    <!-- Sabores Management Section -->
                    <li class="nav-item">
                        <small class="nav-subtitle"
                            title="{{ translate('Sabores de la Ciudad') }}">{{ translate('Sabores de la Ciudad') }}</small>
                        <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                    </li>

                    <!-- Reservations -->
                    <li
                        class="navbar-vertical-aside-has-menu {{ Request::is('admin/sabores/reservations*') ? 'active' : '' }}">
                        <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:"
                            title="{{ translate('Reservations') }}">
                            <i class="tio-calendar nav-icon"></i>
                            <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                {{ translate('Reservations') }}
                            </span>
                        </a>
                        <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                            style="display:{{ Request::is('admin/sabores/reservations*') ? 'block' : 'none' }}">
                            <li
                                class="nav-item {{ Request::is('admin/sabores/reservations') && !request('status') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('admin.sabores.reservations') }}"
                                    title="{{ translate('All Reservations') }}">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate sidebar--badge-container">
                                        {{ translate('All Reservations') }}
                                        <span class="badge badge-soft-info badge-pill ml-1">
                                            {{ \App\Models\Reservation::count() }}
                                        </span>
                                    </span>
                                </a>
                            </li>
                            <li class="nav-item {{ request('status') == 'pending' ? 'active' : '' }}">
                                <a class="nav-link"
                                    href="{{ route('admin.sabores.reservations', ['status' => 'pending']) }}"
                                    title="{{ translate('Pending') }}">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate sidebar--badge-container">
                                        {{ translate('Pending') }}
                                        <span class="badge badge-soft-warning badge-pill ml-1">
                                            {{ \App\Models\Reservation::where('status', 'pending')->count() }}
                                        </span>
                                    </span>
                                </a>
                            </li>
                            <li class="nav-item {{ request('status') == 'confirmed' ? 'active' : '' }}">
                                <a class="nav-link"
                                    href="{{ route('admin.sabores.reservations', ['status' => 'confirmed']) }}"
                                    title="{{ translate('Confirmed') }}">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate sidebar--badge-container">
                                        {{ translate('Confirmed') }}
                                        <span class="badge badge-soft-success badge-pill ml-1">
                                            {{ \App\Models\Reservation::where('status', 'confirmed')->count() }}
                                        </span>
                                    </span>
                                </a>
                            </li>
                            <li class="nav-item {{ request('status') == 'completed' ? 'active' : '' }}">
                                <a class="nav-link"
                                    href="{{ route('admin.sabores.reservations', ['status' => 'completed']) }}"
                                    title="{{ translate('Completed') }}">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate sidebar--badge-container">
                                        {{ translate('Completed') }}
                                        <span class="badge badge-soft-primary badge-pill ml-1">
                                            {{ \App\Models\Reservation::where('status', 'completed')->count() }}
                                        </span>
                                    </span>
                                </a>
                            </li>
                            <li class="nav-item {{ request('status') == 'cancelled' ? 'active' : '' }}">
                                <a class="nav-link"
                                    href="{{ route('admin.sabores.reservations', ['status' => 'cancelled']) }}"
                                    title="{{ translate('Cancelled') }}">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate sidebar--badge-container">
                                        {{ translate('Cancelled') }}
                                        <span class="badge badge-soft-danger badge-pill ml-1">
                                            {{ \App\Models\Reservation::where('status', 'cancelled')->count() }}
                                        </span>
                                    </span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <!-- End Reservations -->

                    <!-- Restaurants -->
                    <li
                        class="navbar-vertical-aside-has-menu {{ Request::is('admin/sabores/restaurants*') ? 'active' : '' }}">
                        <a class="js-navbar-vertical-aside-menu-link nav-link"
                            href="{{ route('admin.sabores.restaurants') }}" title="{{ translate('Restaurants') }}">
                            <i class="tio-restaurant nav-icon"></i>
                            <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                {{ translate('Restaurants') }}
                            </span>
                        </a>
                    </li>
                    <!-- End Restaurants -->

                    <!-- Dineout Categories -->
                    <li
                        class="navbar-vertical-aside-has-menu {{ Request::is('admin/sabores/dineout-categories*') ? 'active' : '' }}">
                        <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:"
                            title="{{ translate('Dineout Categories') }}">
                            <i class="tio-category nav-icon"></i>
                            <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                {{ translate('Dineout Categories') }}
                            </span>
                        </a>
                        <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                            style="display:{{ Request::is('admin/sabores/dineout-categories*') ? 'block' : 'none' }}">
                            <li
                                class="nav-item {{ Request::is('admin/sabores/dineout-categories') && !Request::is('admin/sabores/dineout-categories/create') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('admin.sabores.dineout-categories.index') }}"
                                    title="{{ translate('All Categories') }}">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate sidebar--badge-container">
                                        {{ translate('All Categories') }}
                                        <span class="badge badge-soft-info badge-pill ml-1">
                                            {{ \App\Models\DineoutCategory::count() }}
                                        </span>
                                    </span>
                                </a>
                            </li>
                            <li
                                class="nav-item {{ Request::is('admin/sabores/dineout-categories/create') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('admin.sabores.dineout-categories.create') }}"
                                    title="{{ translate('Add New') }}">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate">
                                        {{ translate('Add New') }}
                                    </span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <!-- End Dineout Categories -->

                    <!-- Coupons -->
                    <li
                        class="navbar-vertical-aside-has-menu {{ Request::is('admin/sabores/coupons*') ? 'active' : '' }}">
                        <a class="js-navbar-vertical-aside-menu-link nav-link"
                            href="{{ route('admin.sabores.coupons') }}" title="{{ translate('Coupons') }}">
                            <i class="tio-gift nav-icon"></i>
                            <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                {{ translate('Coupons') }}
                            </span>
                        </a>
                    </li>
                    <!-- End Coupons -->

                    <!-- Campaigns -->
                    <li
                        class="navbar-vertical-aside-has-menu {{ Request::is('admin/sabores/campaigns*') ? 'active' : '' }}">
                        <a class="js-navbar-vertical-aside-menu-link nav-link"
                            href="{{ route('admin.sabores.campaigns') }}" title="{{ translate('Campaigns') }}">
                            <i class="tio-layers nav-icon"></i>
                            <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                {{ translate('Campaigns') }}
                            </span>
                        </a>
                    </li>
                    <!-- End Campaigns -->

                    <!-- Security Section -->
                    <li class="nav-item">
                        <small class="nav-subtitle"
                            title="{{ translate('Security') }}">{{ translate('Security') }}</small>
                        <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                    </li>

                    <li
                        class="navbar-vertical-aside-has-menu {{ Request::is('admin/sabores/reviews*') ? 'active' : '' }}">
                        <a class="js-navbar-vertical-aside-menu-link nav-link"
                            href="{{ route('admin.sabores.reviews.list') }}" title="{{ translate('Reviews') }}">
                            <i class="tio-star nav-icon"></i>
                            <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                {{ translate('Reviews') }}
                            </span>
                        </a>
                    </li>
                    <!-- End Security Section -->

                    <!-- Analytics Section -->
                    <li class="nav-item">
                        <small class="nav-subtitle"
                            title="{{ translate('Analytics') }}">{{ translate('Analytics & Reports') }}</small>
                        <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                    </li>

                    <!-- Usage Statistics -->
                    <li
                        class="navbar-vertical-aside-has-menu {{ Request::is('admin/sabores/analytics*') ? 'active' : '' }}">
                        <a class="js-navbar-vertical-aside-menu-link nav-link"
                            href="{{ route('admin.sabores.analytics') }}" title="{{ translate('Usage Statistics') }}">
                            <i class="tio-chart-bar-4 nav-icon"></i>
                            <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                {{ translate('Usage Statistics') }}
                            </span>
                        </a>
                    </li>
                    <!-- End Usage Statistics -->

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
    </script>
@endpush