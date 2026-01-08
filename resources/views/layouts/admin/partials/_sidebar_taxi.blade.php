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
                    <!-- Dashboards -->
                    <li
                        class="navbar-vertical-aside-has-menu {{ Request::is('admin/taxi/dashboard') ? 'show active' : '' }}">
                        <a class="js-navbar-vertical-aside-menu-link nav-link"
                            href="{{ route('admin.taxi.dashboard') }}" title="{{ translate('messages.dashboard') }}">
                            <i class="tio-home-vs-1-outlined nav-icon"></i>
                            <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                {{ translate('messages.dashboard') }}
                            </span>
                        </a>
                    </li>
                    <!-- End Dashboards -->

                    <!-- Taxi Management Section -->
                    <li class="nav-item">
                        <small class="nav-subtitle"
                            title="{{ translate('Taxi Management') }}">{{ translate('Taxi Management') }}</small>
                        <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                    </li>

                    <!-- Rides -->
                    <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/taxi/rides*') ? 'active' : '' }}">
                        <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:"
                            title="{{ translate('Rides') }}">
                            <i class="tio-route nav-icon"></i>
                            <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                {{ translate('Rides') }}
                            </span>
                        </a>
                        <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                            style="display:{{ Request::is('admin/taxi/rides*') ? 'block' : 'none' }}">
                            <li
                                class="nav-item {{ Request::is('admin/taxi/rides') && !request('status') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('admin.taxi.rides') }}"
                                    title="{{ translate('All Rides') }}">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate sidebar--badge-container">
                                        {{ translate('All Rides') }}
                                        <span class="badge badge-soft-info badge-pill ml-1">
                                            {{ \App\Models\TaxiRide::count() }}
                                        </span>
                                    </span>
                                </a>
                            </li>
                            <li class="nav-item {{ request('status') == 'pending' ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('admin.taxi.rides', ['status' => 'pending']) }}"
                                    title="{{ translate('Pending') }}">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate sidebar--badge-container">
                                        {{ translate('Pending') }}
                                        <span class="badge badge-soft-warning badge-pill ml-1">
                                            {{ \App\Models\TaxiRide::where('status', 'pending')->count() }}
                                        </span>
                                    </span>
                                </a>
                            </li>
                            <li class="nav-item {{ request('status') == 'in_progress' ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('admin.taxi.rides', ['status' => 'in_progress']) }}"
                                    title="{{ translate('In Progress') }}">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate sidebar--badge-container">
                                        {{ translate('In Progress') }}
                                        <span class="badge badge-soft-primary badge-pill ml-1">
                                            {{ \App\Models\TaxiRide::whereIn('status', ['accepted', 'arriving', 'arrived', 'in_progress'])->count() }}
                                        </span>
                                    </span>
                                </a>
                            </li>
                            <li class="nav-item {{ request('status') == 'completed' ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('admin.taxi.rides', ['status' => 'completed']) }}"
                                    title="{{ translate('Completed') }}">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate sidebar--badge-container">
                                        {{ translate('Completed') }}
                                        <span class="badge badge-soft-success badge-pill ml-1">
                                            {{ \App\Models\TaxiRide::where('status', 'completed')->count() }}
                                        </span>
                                    </span>
                                </a>
                            </li>
                            <li class="nav-item {{ request('status') == 'cancelled' ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('admin.taxi.rides', ['status' => 'cancelled']) }}"
                                    title="{{ translate('Cancelled') }}">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate sidebar--badge-container">
                                        {{ translate('Cancelled') }}
                                        <span class="badge badge-soft-danger badge-pill ml-1">
                                            {{ \App\Models\TaxiRide::where('status', 'cancelled')->count() }}
                                        </span>
                                    </span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <!-- End Rides -->

                    <!-- Drivers -->
                    <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/taxi/drivers*') ? 'active' : '' }}">
                        <a class="js-navbar-vertical-aside-menu-link nav-link" href="{{ route('admin.taxi.drivers') }}"
                            title="{{ translate('Drivers') }}">
                            <i class="tio-user nav-icon"></i>
                            <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                {{ translate('Drivers') }}
                            </span>
                        </a>
                    </li>
                    <!-- End Drivers -->

                    <!-- Vehicles -->
                    <li
                        class="navbar-vertical-aside-has-menu {{ Request::is('admin/taxi/vehicles*') ? 'active' : '' }}">
                        <a class="js-navbar-vertical-aside-menu-link nav-link" href="{{ route('admin.taxi.vehicles') }}"
                            title="{{ translate('Vehicles') }}">
                            <i class="tio-car nav-icon"></i>
                            <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                {{ translate('Vehicles') }}
                            </span>
                        </a>
                    </li>
                    <!-- End Vehicles -->

                    <!-- Fare Configuration -->
                    <li
                        class="navbar-vertical-aside-has-menu {{ Request::is('admin/taxi/fare-config*') ? 'active' : '' }}">
                        <a class="js-navbar-vertical-aside-menu-link nav-link"
                            href="{{ route('admin.taxi.fare-config') }}" title="{{ translate('Fare Configuration') }}">
                            <i class="tio-dollar nav-icon"></i>
                            <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                {{ translate('Fare Configuration') }}
                            </span>
                        </a>
                    </li>
                    <!-- End Fare Configuration -->

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