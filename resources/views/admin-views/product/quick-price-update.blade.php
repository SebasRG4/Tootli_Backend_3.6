@extends('layouts.admin.app')

@section('title', 'Actualización Rápida de Precios')

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('assets/admin/img/items.png')}}" class="w--22" alt="">
                </span>
                <span>
                    {{'Actualización Rápida de Precios'}}
                </span>
            </h1>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <h4 class="mb-3">{{ 'Instrucciones' }}</h4>
                <ul>
                    <li>{{ 'Pega el mensaje de WhatsApp en el cuadro de texto.' }}</li>
                    <li>{{ 'El formato debe ser:' }} <strong>- Producto $Precio</strong> {{ 'oh' }} <strong>- Producto (Variación) $Precio</strong></li>
                    <li>{{ 'Ejemplo:' }} <br>
                        - Jitomate $50 <br>
                        - Pizza (Pequeña) $80
                    </li>
                </ul>

                <form id="parse-text-form">
                    @csrf
                    <div class="form-group">
                        <label for="whatsapp_text">{{ 'Texto de WhatsApp' }}</label>
                        <textarea name="whatsapp_text" id="whatsapp_text" class="form-control" rows="10" placeholder="- Aguacate $20&#10;- Refresco (600ml) $15" required></textarea>
                    </div>
                    <div class="btn--container justify-content-end">
                        <button type="submit" class="btn btn--primary" id="btn-parse">{{ 'Analizar Texto' }}</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card d-none" id="preview-section">
            <div class="card-header">
                <h5 class="card-title">{{ 'Vista previa de actualización' }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.item.quick-price-update-store') }}" method="POST" id="update-prices-form">
                    @csrf
                    <div id="preview-content"></div>
                    
                    <div class="btn--container justify-content-end mt-4">
                        <button type="button" class="btn btn--reset" id="btn-cancel">{{ 'Cancelar' }}</button>
                        <button type="submit" class="btn btn--primary" id="btn-confirm">{{ 'Confirmar y Actualizar Precios' }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
<script>
    $('#parse-text-form').on('submit', function(e) {
        e.preventDefault();
        $('#btn-parse').attr('disabled', true).text('{{'Analizando...'}}');

        $.ajax({
            url: '{{ route("admin.item.quick-price-update-parse") }}',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                $('#preview-content').html(response.view);
                $('#preview-section').removeClass('d-none');
                $('#btn-parse').attr('disabled', false).text('{{'Analizar Texto'}}');
                
                // Check if any valid item is found to enable/disable confirm button
                if($('.update-row.found').length > 0) {
                    $('#btn-confirm').attr('disabled', false);
                } else {
                    $('#btn-confirm').attr('disabled', true);
                }
            },
            error: function() {
                toastr.error('{{'Ocurrió un error al analizar el texto.'}}');
                $('#btn-parse').attr('disabled', false).text('{{'Analizar Texto'}}');
            }
        });
    });

    $('#btn-cancel').on('click', function() {
        $('#preview-section').addClass('d-none');
        $('#preview-content').html('');
    });

    $('#update-prices-form').on('submit', function(e) {
        if($('.update-row.found').length === 0) {
            e.preventDefault();
            toastr.warning('{{'No hay artículos válidos para actualizar.'}}');
        }
    });
</script>
@endpush
