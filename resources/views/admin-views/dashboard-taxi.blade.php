@extends('layouts.admin.app')

@section('title', \App\Models\BusinessSetting::where(['key' => 'business_name'])->first()->value ?? translate('messages.dashboard'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
<div class="content container-fluid">
    @if(auth('admin')->user()->role_id == 1)
    @php($mod = \App\Models\Module::find(Config::get('module.current_module_id')))
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center py-2">
                <div class="col-sm mb-2 mb-sm-0">
                    <div class="d-flex align-items-center">
                        <img class="onerror-image" data-onerror-image="{{asset('assets/admin/img/grocery.svg')}}"
                            src="{{$mod->icon_full_url }}" width="38" alt="img">
                        <div class="w-0 flex-grow pl-2">
                            <h1 class="page-header-title mb-0">{{translate($mod->module_name)}}
                                {{translate('messages.Dashboard')}}.</h1>
                            <p class="page-header-text m-0">{{translate('Hello, Here You Can Manage Your')}}
                                {{translate($mod->module_name)}} {{translate('services.')}}</p>
                        </div>
                    </div>
                </div>

                <div class="col-sm-auto min--280">
                    <select name="zone_id" class="form-control js-select2-custom fetch_data_zone_wise">
                        <option value="all">{{ translate('messages.All_Zones') }}</option>
                        @foreach(\App\Models\Zone::orderBy('name')->get() as $zone)
                            <option value="{{$zone['id']}}" {{isset($params) && $params['zone_id'] == $zone['id'] ? 'selected' : ''}}>
                                {{$zone['name']}}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- Quick Actions for Taxi -->
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="card-header-title">{{ translate('Taxi Management') }}</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <a href="{{ route('admin.taxi.dashboard') }}" class="btn btn-primary btn-block">
                            <i class="tio-dashboard"></i> {{ translate('Taxi Dashboard') }}
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="{{ route('admin.taxi.drivers') }}" class="btn btn-info btn-block">
                            <i class="tio-user"></i> {{ translate('Manage Drivers') }}
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="{{ route('admin.taxi.vehicles') }}" class="btn btn-success btn-block">
                            <i class="tio-car"></i> {{ translate('Manage Vehicles') }}
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="{{ route('admin.taxi.fare-config') }}" class="btn btn-warning btn-block">
                            <i class="tio-dollar"></i> {{ translate('Fare Config') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="card mb-3">
            <div class="card-body pt-0">
                <div class="row g-2" id="order_stats">
                    @php
                        $totalDrivers = \Modules\Taxi\Models\TaxiDriver::count();
                        $onlineDrivers = \Modules\Taxi\Models\TaxiDriver::where('status', 'available')->count();
                        $totalVehicles = \Modules\Taxi\Models\TaxiVehicle::count();
                        $totalRides = \Modules\Taxi\Models\TaxiRide::count();
                        $pendingRides = \Modules\Taxi\Models\TaxiRide::where('status', 'pending')->count();
                        $activeRides = \Modules\Taxi\Models\TaxiRide::whereIn('status', ['accepted', 'arriving', 'arrived', 'in_progress'])->count();
                        $completedRides = \Modules\Taxi\Models\TaxiRide::where('status', 'completed')->count();
                        $cancelledRides = \Modules\Taxi\Models\TaxiRide::where('status', 'cancelled')->count();
                        $totalEarnings = \Modules\Taxi\Models\TaxiRide::where('status', 'completed')->sum('final_fare');
                    @endphp
                    <div class="col-sm-6 col-lg-3">
                        <div class="__dashboard-card-2">
                            <img src="{{asset('assets/admin/img/dashboard/grocery/customers.svg')}}" alt="dashboard">
                            <h6 class="name">{{ translate('Drivers') }}</h6>
                            <h3 class="count">{{ $totalDrivers }}</h3>
                            <div class="subtxt text-success">{{ $onlineDrivers }} {{ translate('online') }}</div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="__dashboard-card-2">
                            <img src="{{asset('assets/admin/img/dashboard/food/orders.svg')}}" alt="dashboard">
                            <h6 class="name">{{ translate('Total Rides') }}</h6>
                            <h3 class="count">{{ $totalRides }}</h3>
                            <div class="subtxt">{{ $activeRides }} {{ translate('active now') }}</div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="__dashboard-card-2">
                            <img src="{{asset('assets/admin/img/dashboard/food/stores.svg')}}" alt="dashboard">
                            <h6 class="name">{{ translate('Vehicles') }}</h6>
                            <h3 class="count">{{ $totalVehicles }}</h3>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="__dashboard-card-2">
                            <img src="{{asset('assets/admin/img/dashboard/grocery/items.svg')}}" alt="dashboard">
                            <h6 class="name">{{ translate('Earnings') }}</h6>
                            <h3 class="count">{{\App\CentralLogics\Helpers::format_currency($totalEarnings)}}</h3>
                            <div class="subtxt">{{ $completedRides }} {{ translate('trips') }}</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="row g-2">
                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100"
                                    href="{{ route('admin.taxi.rides', ['status' => 'pending']) }}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/grocery/unassigned.svg')}}"
                                                alt="dashboard" class="oder--card-icon">
                                            <span>{{translate('Pending Rides')}}</span>
                                        </h6>
                                        <span class="card-title text-warning">
                                            {{ $pendingRides }}
                                        </span>
                                    </div>
                                </a>
                            </div>

                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100"
                                    href="{{ route('admin.taxi.rides', ['status' => 'in_progress']) }}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/grocery/out-for.svg')}}"
                                                alt="dashboard" class="oder--card-icon">
                                            <span>{{translate('Active Rides')}}</span>
                                        </h6>
                                        <span class="card-title text-primary">
                                            {{ $activeRides }}
                                        </span>
                                    </div>
                                </a>
                            </div>

                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100"
                                    href="{{ route('admin.taxi.rides', ['status' => 'completed']) }}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/grocery/delivered.svg')}}"
                                                alt="dashboard" class="oder--card-icon">
                                            <span>{{translate('Completed')}}</span>
                                        </h6>
                                        <span class="card-title text-success">
                                            {{ $completedRides }}
                                        </span>
                                    </div>
                                </a>
                            </div>

                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100"
                                    href="{{ route('admin.taxi.rides', ['status' => 'cancelled']) }}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/order-status/canceled.svg')}}"
                                                alt="dashboard" class="oder--card-icon">
                                            <span>{{translate('Cancelled')}}</span>
                                        </h6>
                                        <span class="card-title text-danger">
                                            {{ $cancelledRides }}
                                        </span>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <!-- End Stats -->

        <!-- Recent Rides -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-header-title">{{ translate('Recent Rides') }}</h5>
                <a href="{{ route('admin.taxi.rides') }}"
                    class="btn btn-sm btn-outline-primary">{{ translate('View All') }}</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-borderless table-thead-bordered table-align-middle">
                        <thead class="thead-light">
                            <tr>
                                <th>{{ translate('ID') }}</th>
                                <th>{{ translate('User') }}</th>
                                <th>{{ translate('Driver') }}</th>
                                <th>{{ translate('Status') }}</th>
                                <th>{{ translate('Fare') }}</th>
                                <th>{{ translate('Date') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $recentRides = \Modules\Taxi\Models\TaxiRide::with(['user', 'driver.user'])
                                    ->orderBy('created_at', 'desc')
                                    ->take(10)
                                    ->get();
                            @endphp
                            @forelse($recentRides as $ride)
                                <tr>
                                    <td>#{{ $ride->id }}</td>
                                    <td>{{ $ride->user->f_name ?? 'N/A' }} {{ $ride->user->l_name ?? '' }}</td>
                                    <td>{{ $ride->driver->user->f_name ?? 'Pending' }}</td>
                                    <td>
                                        @php
                                            $statusColors = [
                                                'pending' => 'secondary',
                                                'accepted' => 'info',
                                                'arriving' => 'primary',
                                                'arrived' => 'primary',
                                                'in_progress' => 'warning',
                                                'completed' => 'success',
                                                'cancelled' => 'danger',
                                            ];
                                        @endphp
                                        <span class="badge badge-soft-{{ $statusColors[$ride->status] ?? 'secondary' }}">
                                            {{ ucfirst(str_replace('_', ' ', $ride->status)) }}
                                        </span>
                                    </td>
                                    <td>{{\App\CentralLogics\Helpers::format_currency($ride->final_fare ?? $ride->estimated_fare)}}
                                    </td>
                                    <td>{{ $ride->created_at->format('M d, H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">{{ translate('No rides yet') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    @else
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-sm mb-2 mb-sm-0">
                <h1 class="page-header-title">{{translate('messages.welcome')}}, {{auth('admin')->user()->f_name}}.</h1>
                <p class="page-header-text">{{translate('messages.employee_welcome_message')}}</p>
            </div>
        </div>
    </div>
    <!-- End Page Header -->
    @endif
</div>
@endsection

@push('script')
    <script src="{{asset('assets/admin')}}/vendor/chart.js/dist/Chart.min.js"></script>
@endpush