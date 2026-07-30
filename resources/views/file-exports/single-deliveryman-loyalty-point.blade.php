<div class="row">
    <div class="col-lg-12 text-center ">
        <h1>{{ 'historial de transacciones de puntos de fidelidad del repartidor' }}</h1>
    </div>
    <div class="col-lg-12">



        <table>
            <thead>
                <tr>
                    <th>{{ 'información del repartidor' }}</th>
                    <th></th>
                    <th></th>
                    <th>
                        {{ 'nombre'  }}- {{ $data['dm']->f_name . ' ' . $data['dm']->l_name}}
                        <br>
                        {{ 'teléfono'  }}- {{ $data['dm']->phone}}
                        <br>
                        {{ 'correo electrónico'  }}- {{ $data['dm']->email}}
                        <br>
                        {{ 'calificación total'  }}- {{ count($data['dm']->rating)}}
                        <br>
                        {{ 'revisión promedio'  }}-
                        {{count($data['dm']->rating) > 0 ? number_format($data['dm']->rating[0]->average, 1, '.', ' ') : 0}}

                    </th>
                    <th> </th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
                {{-- <tr>
                    <th>{{ 'Criterios de búsqueda' }}</th>
                    <th></th>
                    <th></th>
                    <th>
                        {{ 'Contenido de la barra de búsqueda' }}- {{ $data['search'] ??'N / A' }}

                    </th>
                    <th> </th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr> --}}
                <tr>
                    <th>{{ 'SL' }}</th>
                    <th>{{'ID de transacción'}}</th>
                    <th>{{'Fecha'}}</th>
                    <th>{{'Tipo de transacción'}}</th>
                    <th>{{'Punto'}}</th>
                    <th>{{'Referencia'}}</th>

            </thead>
            <tbody>
                @foreach($data['histories'] as $key => $loyalty_point)
                    <tr>
                        <td class="text-center">{{  $key + 1 }}</td>
                        <td>
                            <div class="text-wrap line--limit-1  max-w--220px min-w-160 text-title">
                                {{ $loyalty_point->transaction_id }}
                            </div>
                        </td>
                        <td>
                            <div class="text-wrap line--limit-1  max-w--220px min-w-160 text-title">
                                {{ \App\CentralLogics\Helpers::date_format($loyalty_point->created_at) }}
                            </div>
                        </td>
                        <td>
                            <div class="text-wrap line--limit-1  max-w--220px min-w-160 text-title">
                                {{ translate($loyalty_point->transaction_type) }}
                                {{ $loyalty_point->transaction_type == 'converted_to_wallet' ? '(' . \App\CentralLogics\Helpers::currency_symbol() . ')' : ''}}
                            </div>
                        </td>
                        <td>
                            <div class="text-dark text-right pr-6">
                                {{ $loyalty_point->point_conversion_type == 'credit' ? '+' : '-' }}
                                {{ $loyalty_point->point }} <br>
                                @if ($loyalty_point->point_conversion_type == 'credit')
                                    <span type="button"
                                        class="btn px-3 fs-12 py-1 badge-soft-success">{{ 'crédito' }}</span>
                                @else
                                    <span type="button"
                                        class="btn px-3 fs-12 py-1 badge-soft-danger">{{ 'Débito' }}</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            {{ $loyalty_point->reference ?? 'N / A' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>