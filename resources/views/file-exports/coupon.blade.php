
<div class="row">
    <div class="col-lg-12 text-center "><h1 > {{'Lista de cupones'}}
    </h1></div>
    <div class="col-lg-12">

    <table>
        <thead>
            <tr>
                <th>{{ 'Criterios de búsqueda' }}</th>
                <th></th>
                <th></th>
                <th>
                    {{ 'Contenido de la barra de búsqueda'  }}: {{ $data['search'] ??'N / A' }}
                </th>
                <th> </th>
                <th></th>
                <th></th>
                <th></th>
                </tr>


        <tr>
            <th>{{ 'SL' }}</th>
            <th>{{ 'Título del cupón' }}</th>
            <th>{{ 'Código de cupón' }}</th>
            <th>{{ 'Módulo' }}</th>
            <th>{{ 'Tipo de cupón' }}</th>
            <th>{{ 'Número de usos' }}</th>
            <th>{{ 'Monto mínimo de compra' }}</th>
            <th>{{ 'Monto máximo de descuento' }} </th>
            <th>{{ 'Tipo de descuento' }} </th>
            <th>{{ 'Descuento' }} </th>
            <th>{{ 'Fecha de inicio' }} </th>
            <th>{{ 'Fecha de finalización' }} </th>
        </thead>
        <tbody>
        @foreach($data['data'] as $key => $coupon)
            <tr>
        <td>{{ $loop->index+1}}</td>
        <td>{{ $coupon->title }}</td>
        <td>{{ $coupon->code }}</td>
        <td>{{ translate($coupon->module->module_name) }}</td>
        <td>{{ translate($coupon->coupon_type) }}</td>
        <td>{{ $coupon->total_uses }}</td>
        <td>{{ $coupon->min_purchase }}</td>
        <td>{{ $coupon->max_discount }}</td>
        <td>{{ $coupon->discount }}</td>
        <td>{{ translate($coupon->discount_type) }}</td>
        <td>{{ \Carbon\Carbon::parse($coupon->start_date)->format('d M Y') }}</td>
        <td>{{ \Carbon\Carbon::parse($coupon->expire_date)->format('d M Y') }}</td>

            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
