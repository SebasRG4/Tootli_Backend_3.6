<div class="col-sm-6 col-lg-3">
    <!-- Card -->
    <a class="resturant-card dashboard--card card--bg-1" href="{{route('vendor.order.list',['confirmed'])}}">
       <h4 class="title">{{$data['confirmed']}}</h4>
       <span class="subtitle">{{'confirmado'}}</span>
       <img src="{{asset('assets/admin/img/dashboard/1.png')}}" alt="img" class="resturant-icon">
    </a>
    <!-- End Card -->
</div>

<div class="col-sm-6 col-lg-3">
    <!-- Card -->
    <a class="resturant-card dashboard--card card--bg-2" href="{{route('vendor.order.list',['cooking'])}}">
        @php($store_data=\App\CentralLogics\Helpers::get_store_data())
       <h4 class="title">{{$data['cooking']}}</h4>
        @if($store_data->module->module_type == 'food')
       <span class="subtitle">{{'cocinando'}}</span>
        @else
       <span class="subtitle">{{'tratamiento'}}</span>
        @endif
       <img src="{{asset('assets/admin/img/dashboard/2.png')}}" alt="img" class="resturant-icon">
    </a>
    <!-- End Card -->
</div>

<div class="col-sm-6 col-lg-3">
    <!-- Card -->
    <a class="resturant-card dashboard--card card--bg-3" href="{{route('vendor.order.list',['ready_for_delivery'])}}">
       <h4 class="title">{{$data['ready_for_delivery']}}</h4>
       <span class="subtitle">{{'listo para la entrega'}}</span>
       <img src="{{asset('assets/admin/img/dashboard/3.png')}}" alt="img" class="resturant-icon">
    </a>
    <!-- End Card -->
</div>

<div class="col-sm-6 col-lg-3">
    <!-- Card -->
    <a class="resturant-card dashboard--card card--bg-4" href="{{route('vendor.order.list',['item_on_the_way'])}}">
       <h4 class="title">{{$data['item_on_the_way']}}</h4>
       <span class="subtitle">{{'artículo en camino'}}</span>
       <img src="{{asset('assets/admin/img/dashboard/4.png')}}" alt="img" class="resturant-icon">
    </a>
    <!-- End Card -->
</div>


<div class="col-12">
    <div class="row g-2">
        <div class="col-sm-6 col-lg-3">
            <a class="order--card h-100" href="{{route('vendor.order.list',['delivered'])}}">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                        <img src="{{asset('assets/admin/img/dashboard/statistics/1.png')}}" alt="dashboard" class="oder--card-icon">
                        <span>{{'Entregado'}}</span>
                    </h6>
                    <span class="card-title text-success">
                        {{$data['delivered']}}
                    </span>
                </div>
            </a>
        </div>

        <div class="col-sm-6 col-lg-3">
            <a class="order--card h-100" href="{{route('vendor.order.list',['refunded'])}}">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                        <img src="{{asset('assets/admin/img/dashboard/statistics/2.png')}}" alt="dashboard" class="oder--card-icon">
                        <span>{{'Reembolsado'}}</span>
                    </h6>
                    <span class="card-title text-danger">
                        {{$data['refunded']}}
                    </span>
                </div>
            </a>
        </div>

        <div class="col-sm-6 col-lg-3">
            <a class="order--card h-100" href="{{route('vendor.order.list',['scheduled'])}}">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                        <img src="{{asset('assets/admin/img/dashboard/statistics/3.png')}}" alt="dashboard" class="oder--card-icon">
                        <span>{{'programado'}}</span>
                    </h6>
                    <span class="card-title text-primary">
                        {{$data['scheduled']}}
                    </span>
                </div>
            </a>
        </div>

        <div class="col-sm-6 col-lg-3">
            <a class="order--card h-100" href="{{route('vendor.order.list',['all'])}}">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                        <img src="{{asset('assets/admin/img/dashboard/statistics/4.png')}}" alt="dashboard" class="oder--card-icon">
                        <span>{{'todo'}}</span>
                    </h6>
                    <span class="card-title text-info">
                        {{$data['all']}}
                    </span>
                </div>
            </a>
        </div>
    </div>
</div>
