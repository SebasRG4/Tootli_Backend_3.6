<div class="table-responsive">
    <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
        <thead class="thead-light">
            <tr>
                <th>{{ translate('Texto Original') }}</th>
                <th>{{ translate('Artículo Detectado') }}</th>
                <th>{{ translate('Precio Anterior') }}</th>
                <th>{{ translate('Nuevo Precio') }}</th>
                <th>{{ translate('Estado') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($previewData as $index => $data)
                <tr class="update-row {{ $data['match_status'] }}">
                    <td>{{ $data['original_line'] }}</td>
                    <td>
                        @if($data['match_status'] == 'found')
                            <strong>{{ $data['db_item_name'] }}</strong>
                            @if($data['parsed_variation'])
                                <br><small class="text-muted">{{ translate('Variación:') }} {{ $data['parsed_variation'] }}</small>
                            @endif
                            <input type="hidden" name="updates[{{$index}}][item_id]" value="{{ $data['db_item_id'] }}">
                            <input type="hidden" name="updates[{{$index}}][variation]" value="{{ $data['parsed_variation'] }}">
                            <input type="hidden" name="updates[{{$index}}][new_price]" value="{{ $data['parsed_price'] }}">
                        @else
                            <span class="text-muted">{{ $data['parsed_name'] }} {{ $data['parsed_variation'] ? '('.$data['parsed_variation'].')' : '' }}</span>
                        @endif
                    </td>
                    <td>
                        @if($data['match_status'] == 'found')
                            {{ \App\CentralLogics\Helpers::format_currency($data['old_price']) }}
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        @if($data['parsed_price'])
                            {{ \App\CentralLogics\Helpers::format_currency($data['parsed_price']) }}
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        @if($data['match_status'] == 'found')
                            <span class="badge badge-soft-success">{{ translate('Encontrado (Listo para actualizar)') }}</span>
                        @elseif($data['match_status'] == 'multiple_found')
                            <span class="badge badge-soft-warning">{{ translate('Múltiples artículos con el mismo nombre. Usa un nombre más específico.') }}</span>
                        @elseif($data['match_status'] == 'not_found')
                            <span class="badge badge-soft-danger">{{ translate('No encontrado en la base de datos') }}</span>
                        @elseif($data['match_status'] == 'invalid_format')
                            <span class="badge badge-soft-secondary">{{ translate('Formato inválido') }}</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

@if(count($previewData) == 0)
    <div class="text-center p-4">
        <p class="mb-0">{{ translate('No se pudo extraer información del texto.') }}</p>
    </div>
@endif
