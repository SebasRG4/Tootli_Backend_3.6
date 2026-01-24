<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte de Seguridad #{{ $alert->id }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
            padding: 20px;
        }

        .header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 3px solid
                {{ $alert->alert_type == 'emergency' ? '#dc3545' : '#fd7e14' }}
            ;
            margin-bottom: 20px;
        }

        .header h1 {
            color:
                {{ $alert->alert_type == 'emergency' ? '#dc3545' : '#fd7e14' }}
            ;
            font-size: 24px;
            margin-bottom: 5px;
        }

        .header .subtitle {
            font-size: 14px;
            color: #666;
        }

        .alert-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 14px;
            margin-top: 10px;
            color: white;
            background-color:
                {{ $alert->alert_type == 'emergency' ? '#dc3545' : '#fd7e14' }}
            ;
        }

        .section {
            margin-bottom: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #007bff;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .info-row {
            display: table;
            width: 100%;
            margin-bottom: 8px;
        }

        .info-label {
            display: table-cell;
            width: 35%;
            font-weight: bold;
            color: #555;
        }

        .info-value {
            display: table-cell;
            width: 65%;
        }

        .emergency-section {
            border-left-color: #dc3545;
        }

        .emergency-section .section-title {
            color: #dc3545;
        }

        .driver-section {
            border-left-color: #28a745;
        }

        .driver-section .section-title {
            color: #28a745;
        }

        .location-section {
            border-left-color: #6c757d;
        }

        .location-section .section-title {
            color: #6c757d;
        }

        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 10px;
            color: #999;
        }

        .qr-section {
            text-align: center;
            margin-top: 20px;
            padding: 15px;
            background: #fff;
            border: 1px dashed #ccc;
            border-radius: 8px;
        }

        .important-note {
            background:
                {{ $alert->alert_type == 'emergency' ? '#fff5f5' : '#fff8f0' }}
            ;
            border: 1px solid
                {{ $alert->alert_type == 'emergency' ? '#dc3545' : '#fd7e14' }}
            ;
            padding: 10px;
            border-radius: 5px;
            margin-top: 20px;
        }

        .important-note strong {
            color:
                {{ $alert->alert_type == 'emergency' ? '#dc3545' : '#fd7e14' }}
            ;
        }

        table.full-width {
            width: 100%;
            border-collapse: collapse;
        }

        table.full-width td {
            padding: 5px 0;
            vertical-align: top;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <div class="header">
        <h1>🚨 REPORTE DE SEGURIDAD</h1>
        <p class="subtitle">Sistema de Taxi - Tootli</p>
        <div class="alert-badge">
            @if($alert->alert_type == 'emergency')
                🆘 ALERTA DE EMERGENCIA
            @else
                🛡️ USUARIO SE SINTIÓ INSEGURO
            @endif
        </div>
    </div>

    <!-- Alert Info -->
    <div class="section {{ $alert->alert_type == 'emergency' ? 'emergency-section' : '' }}">
        <div class="section-title">📋 Información de la Alerta</div>
        <table class="full-width">
            <tr>
                <td style="width: 50%;">
                    <div class="info-row">
                        <span class="info-label">ID de Alerta:</span>
                        <span class="info-value">#{{ $alert->id }}</span>
                    </div>
                </td>
                <td style="width: 50%;">
                    <div class="info-row">
                        <span class="info-label">Tipo:</span>
                        <span class="info-value">{{ strtoupper($alert->alert_type) }}</span>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="info-row">
                        <span class="info-label">Fecha y Hora:</span>
                        <span class="info-value">{{ $alert->created_at->format('d/m/Y H:i:s') }}</span>
                    </div>
                </td>
                <td>
                    <div class="info-row">
                        <span class="info-label">Estado:</span>
                        <span class="info-value">{{ strtoupper($alert->status) }}</span>
                    </div>
                </td>
            </tr>
            @if($alert->contacted_at)
                <tr>
                    <td colspan="2">
                        <div class="info-row">
                            <span class="info-label">Contactado:</span>
                            <span class="info-value">{{ $alert->contacted_at->format('d/m/Y H:i:s') }}</span>
                        </div>
                    </td>
                </tr>
            @endif
            @if($alert->resolved_at)
                <tr>
                    <td colspan="2">
                        <div class="info-row">
                            <span class="info-label">Resuelto:</span>
                            <span class="info-value">{{ $alert->resolved_at->format('d/m/Y H:i:s') }}</span>
                        </div>
                    </td>
                </tr>
            @endif
        </table>
    </div>

    <!-- User Info -->
    <div class="section">
        <div class="section-title">👤 Información del Pasajero</div>
        <table class="full-width">
            <tr>
                <td style="width: 50%;">
                    <div class="info-row">
                        <span class="info-label">Nombre:</span>
                        <span class="info-value">{{ $alert->user->f_name ?? '' }}
                            {{ $alert->user->l_name ?? '' }}</span>
                    </div>
                </td>
                <td style="width: 50%;">
                    <div class="info-row">
                        <span class="info-label">Teléfono:</span>
                        <span class="info-value">{{ $alert->user->phone ?? 'N/A' }}</span>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <div class="info-row">
                        <span class="info-label">Email:</span>
                        <span class="info-value">{{ $alert->user->email ?? 'N/A' }}</span>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Driver Info -->
    @if($alert->taxiRide->driver)
        <div class="section driver-section">
            <div class="section-title">🚗 Información del Conductor</div>
            <table class="full-width">
                <tr>
                    <td style="width: 50%;">
                        <div class="info-row">
                            <span class="info-label">Nombre:</span>
                            <span class="info-value">{{ $alert->taxiRide->driver->f_name }}
                                {{ $alert->taxiRide->driver->l_name }}</span>
                        </div>
                    </td>
                    <td style="width: 50%;">
                        <div class="info-row">
                            <span class="info-label">Teléfono:</span>
                            <span class="info-value">{{ $alert->taxiRide->driver->phone }}</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <div class="info-row">
                            <span class="info-label">Placa del Vehículo:</span>
                            <span
                                class="info-value"><strong>{{ $alert->taxiRide->driver->vehicle->plate ?? 'N/A' }}</strong></span>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    @endif

    <!-- Ride Info -->
    <div class="section">
        <div class="section-title">📍 Información del Viaje</div>
        <table class="full-width">
            <tr>
                <td style="width: 30%;">
                    <div class="info-row">
                        <span class="info-label">ID Viaje:</span>
                        <span class="info-value">#{{ $alert->taxiRide->id }}</span>
                    </div>
                </td>
                <td style="width: 70%;">
                    <div class="info-row">
                        <span class="info-label">Estado:</span>
                        <span class="info-value">{{ strtoupper($alert->taxiRide->status) }}</span>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <div class="info-row">
                        <span class="info-label">Origen:</span>
                        <span class="info-value">{{ $alert->taxiRide->pickup_address }}</span>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <div class="info-row">
                        <span class="info-label">Destino:</span>
                        <span class="info-value">{{ $alert->taxiRide->dropoff_address }}</span>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Location at Alert Time -->
    @if($alert->user_location_lat && $alert->user_location_lng)
        <div class="section location-section">
            <div class="section-title">📌 Ubicación al Momento de la Alerta</div>
            <table class="full-width">
                <tr>
                    <td style="width: 50%;">
                        <div class="info-row">
                            <span class="info-label">Latitud:</span>
                            <span class="info-value">{{ $alert->user_location_lat }}</span>
                        </div>
                    </td>
                    <td style="width: 50%;">
                        <div class="info-row">
                            <span class="info-label">Longitud:</span>
                            <span class="info-value">{{ $alert->user_location_lng }}</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <div class="info-row">
                            <span class="info-label">Google Maps:</span>
                            <span
                                class="info-value">https://maps.google.com/?q={{ $alert->user_location_lat }},{{ $alert->user_location_lng }}</span>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    @endif

    <!-- Admin Notes -->
    @if($alert->admin_notes)
        <div class="section">
            <div class="section-title">📝 Notas Administrativas</div>
            <p>{{ $alert->admin_notes }}</p>
        </div>
    @endif

    <!-- Important Note -->
    <div class="important-note">
        <strong>⚠️ AVISO IMPORTANTE:</strong>
        Este documento contiene información confidencial relacionada con un incidente de seguridad.
        Su uso está limitado a fines de investigación y coordinación con autoridades competentes.
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Reporte generado el {{ now()->format('d/m/Y H:i:s') }} | Sistema Tootli</p>
        <p>Este documento es válido como soporte para reportes ante autoridades.</p>
    </div>
</body>

</html>