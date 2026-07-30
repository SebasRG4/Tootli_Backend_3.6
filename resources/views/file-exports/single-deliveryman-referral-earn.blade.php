<div class="row">
    <div class="col-lg-12 text-center ">
        <h3>{{ 'referencia del repartidor y ganar historial' }}</h3>
    </div>
    <div class="col-lg-12">



        <table>
            <thead>
                <tr>
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
                    <th></th>
                    <th></th>

                    <th> </th>
                </tr>

                <tr>
                    <th>{{ 'SL' }}</th>
                    <th>{{'ID de transacción'}}</th>
                    <th>{{'Fecha'}}</th>
                    <th>{{'Cantidad'}}</th>
                    <th>{{'Referencia'}}</th>

            </thead>
            <tbody>
                @foreach($data['histories'] as $key => $referralEarning)
                    <tr>
                                    <td class="text-center">{{ $key + 1}}</td>
                                    <td>
                                        <div class="text-wrap line--limit-1  max-w--220px min-w-160 text-title">
                                            {{ $referralEarning->transaction_id }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-wrap line--limit-1  max-w--220px min-w-160 text-title">
                                            {{ \App\CentralLogics\Helpers::date_format($referralEarning->created_at) }}
                                        </div>
                                         @if ($referralEarning->refer_type == 'referrerBonus')
                                            <div>
                                                <span class="text--title">({{ 'Bono por recomendación' }})</span>
                                            </div>
                                            @endif
                                    </td>
                                    <td>
                                        <div class="text-center text-title">
                                            {{ \App\CentralLogics\Helpers::format_currency($referralEarning->amount) }}
                                        </div>
                                    </td>
                                    <td>
                                        {{ $referralEarning->reference ?? 'N / A' }}
                                    </td>
                                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
