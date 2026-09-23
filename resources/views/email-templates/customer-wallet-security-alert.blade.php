<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>{{ $title }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style type="text/css">
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 0;
            color: #333333;
        }
        .container {
            max-width: 600px;
            margin: 30px auto;
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
        }
        .header {
            background-color: #107954;
            padding: 28px 24px;
            text-align: center;
            color: #ffffff;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        .badge {
            display: inline-block;
            background-color: rgba(255, 255, 255, 0.2);
            color: #ffffff;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            margin-top: 8px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .content {
            padding: 30px 28px;
        }
        .greeting {
            font-size: 17px;
            font-weight: 600;
            margin-bottom: 12px;
            color: #1f2937;
        }
        .message {
            font-size: 15px;
            line-height: 1.6;
            color: #4b5563;
            margin-bottom: 24px;
        }
        .details-card {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 24px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #f3f4f6;
            font-size: 14px;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            color: #6b7280;
            font-weight: 500;
        }
        .detail-value {
            color: #111827;
            font-weight: 600;
            text-align: right;
        }
        .warning-box {
            background-color: #fef2f2;
            border-left: 4px solid #ef4444;
            padding: 14px 16px;
            border-radius: 8px;
            margin-bottom: 24px;
        }
        .warning-title {
            color: #991b1b;
            font-weight: 700;
            font-size: 13px;
            margin-bottom: 4px;
        }
        .warning-text {
            color: #b91c1c;
            font-size: 13px;
            line-height: 1.5;
            margin: 0;
        }
        .footer {
            background-color: #f9fafb;
            border-top: 1px solid #e5e7eb;
            padding: 20px 24px;
            text-align: center;
            font-size: 12px;
            color: #9ca3af;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🛡️ {{ $companyName }} Wallet</h1>
            <div class="badge">Alerta de Seguridad Bancaria</div>
        </div>

        <div class="content">
            <div class="greeting">Hola, {{ $userName }}</div>
            <p class="message">{{ $alertMessage }}</p>

            @if(!empty($details))
                <div class="details-card">
                    @foreach($details as $key => $val)
                        <table style="width: 100%; margin-bottom: 6px;">
                            <tr>
                                <td style="color: #6b7280; font-size: 14px; font-weight: 500; text-align: left;">{{ $key }}:</td>
                                <td style="color: #111827; font-size: 14px; font-weight: 600; text-align: right;">{{ $val }}</td>
                            </tr>
                        </table>
                    @endforeach
                </div>
            @endif

            <div class="warning-box">
                <div class="warning-title">⚠️ ¿No fuiste tú quien realizó esta acción?</div>
                <p class="warning-text">
                    Si no reconoces este movimiento, es posible que alguien haya accedido a tu cuenta. Por tu protección, comunícate de inmediato con el equipo de soporte de {{ $companyName }} o ingresa a tu app para cambiar tu contraseña y bloquear retiros.
                </p>
            </div>
        </div>

        <div class="footer">
            Este es un correo automático de seguridad generado por el sistema de pagos de {{ $companyName }}.<br>
            © {{ date('Y') }} {{ $companyName }}. Todos los derechos reservados.
        </div>
    </div>
</body>
</html>
