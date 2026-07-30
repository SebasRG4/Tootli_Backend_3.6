
    <div class="col-sm-6 col-lg-3">
        <div class="__dashboard-card-2">
            <img src="{{asset('assets/admin/img/dashboard/food/items.svg')}}" alt="dashboard/grocery">
            <h6 class="name">{{ 'alimentos' }}</h6>
            <h3 class="count">{{ $data['total_items'] }}</h3>
            <div class="subtxt">{{ $data['new_items'] }} {{ 'recién agregado' }}</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="__dashboard-card-2">
            <img src="{{asset('assets/admin/img/dashboard/food/orders.svg')}}" alt="dashboard/grocery">
            <h6 class="name">{{ 'Pedidos' }}</h6>
            <h3 class="count">{{ $data['total_orders'] }}</h3>
            <div class="subtxt">{{ $data['new_orders'] }} {{ 'recién agregado' }}</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="__dashboard-card-2">
            <img src="{{asset('assets/admin/img/dashboard/food/stores.svg')}}" alt="dashboard/grocery">
            <h6 class="name">{{ 'restaurantes' }}</h6>
            <h3 class="count">{{ $data['total_stores'] }}</h3>
            <div class="subtxt">{{ $data['new_stores'] }} {{ 'recién agregado' }}</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="__dashboard-card-2">
            <img src="{{asset('assets/admin/img/dashboard/food/customers.svg')}}" alt="dashboard/grocery">
            <h6 class="name">{{ 'Clientes' }}</h6>
            <h3 class="count">{{ $data['total_customers'] }}</h3>
            <div class="subtxt">{{ $data['new_customers'] }} {{ 'recién agregado' }}</div>
        </div>
    </div>
    <div class="col-12">
        <div class="row g-2">
            <div class="col-sm-6 col-lg-3">
                <a class="order--card h-100" href="{{route('admin.order.list',['searching_for_deliverymen'])}}">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                            <img src="{{asset('assets/admin/img/dashboard/grocery/unassigned.svg')}}" alt="dashboard" class="oder--card-icon">
                            <span>{{'Pedidos Sin Asignar'}}</span>
                        </h6>
                        <span class="card-title text-3F8CE8">
                            {{$data['searching_for_dm']}}
                        </span>
                    </div>
                </a>
            </div>

            <div class="col-sm-6 col-lg-3">
                <a class="order--card h-100" href="{{route('admin.order.list',['accepted'])}}">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                            <img src="{{asset('assets/admin/img/dashboard/grocery/accepted.svg')}}" alt="dashboard" class="oder--card-icon">
                            <span>{{'Aceptado por el repartidor'}}</span>
                        </h6>
                        <span class="card-title text-success">
                            {{$data['accepted_by_dm']}}
                        </span>
                    </div>
                </a>
            </div>
            <div class="col-sm-6 col-lg-3">
                <a class="order--card h-100" href="{{route('admin.order.list',['processing'])}}">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                            <img src="{{asset('assets/admin/img/dashboard/grocery/packaging.svg')}}" alt="dashboard" class="oder--card-icon">
                            <span>{{'Cocinando'}}</span>
                        </h6>
                        <span class="card-title text-FFA800">
                            {{$data['preparing_in_rs']}}
                        </span>
                    </div>
                </a>
            </div>

            <div class="col-sm-6 col-lg-3">
                <a class="order--card h-100" href="{{route('admin.order.list',['item_on_the_way'])}}">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                            <img src="{{asset('assets/admin/img/dashboard/grocery/out-for.svg')}}" alt="dashboard" class="oder--card-icon">
                            <span>{{'Fuera de entrega'}}</span>
                        </h6>
                        <span class="card-title text-success">
                            {{$data['picked_up']}}
                        </span>
                    </div>
                </a>
            </div>

            <div class="col-sm-6 col-lg-3">
                <a class="order--card h-100" href="{{route('admin.order.list',['delivered'])}}">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                            <img src="{{asset('assets/admin/img/dashboard/grocery/delivered.svg')}}" alt="dashboard" class="oder--card-icon">
                            <span>{{'Entregado'}}</span>
                        </h6>
                        <span class="card-title text-success">
                            {{$data['delivered']}}
                        </span>
                    </div>
                </a>
            </div>

            <div class="col-sm-6 col-lg-3">
                <a class="order--card h-100" href="{{route('admin.order.list',['canceled'])}}">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                            <img src="{{asset('assets/admin/img/order-status/canceled.svg')}}" alt="dashboard" class="oder--card-icon">
                            <span>{{'Cancelado'}}</span>
                        </h6>
                        <span class="card-title text-danger">
                            {{$data['canceled']}}
                        </span>
                    </div>
                </a>
            </div>

            <div class="col-sm-6 col-lg-3">
                <a class="order--card h-100" href="{{route('admin.order.list',['refunded'])}}">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                            <img src="{{asset('assets/admin/img/order-status/refunded.svg')}}" alt="dashboard" class="oder--card-icon">
                            <span>{{'Reembolsado'}}</span>
                        </h6>
                        <span class="card-title text-danger">
                            {{$data['refunded']}}
                        </span>
                    </div>
                </a>
            </div>

            <div class="col-sm-6 col-lg-3">
                <a class="order--card h-100" href="{{route('admin.order.list',['failed'])}}">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                            <img src="{{asset('assets/admin/img/order-status/payment-failed.svg')}}" alt="dashboard" class="oder--card-icon">
                            <span>{{'pago fallido'}}</span>
                        </h6>
                        <span class="card-title text-danger">
                            {{$data['refund_requested']}}
                        </span>
                    </div>
                </a>
            </div>
        </div>
    </div>

