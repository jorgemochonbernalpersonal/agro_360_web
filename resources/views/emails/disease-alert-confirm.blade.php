<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Confirma tu suscripción') }}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; margin: 0; padding: 0; background: #f4f4f5; }
        .wrapper { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .header { background: #16a34a; padding: 32px 40px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 22px; font-weight: 700; }
        .header p { color: rgba(255,255,255,0.8); margin: 4px 0 0; font-size: 13px; }
        .body { padding: 40px; }
        .message { font-size: 15px; line-height: 1.7; color: #374151; }
        .button { display: inline-block; margin-top: 24px; padding: 14px 28px; background: #16a34a; color: #ffffff !important; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 15px; }
        .fallback { margin-top: 24px; font-size: 12px; color: #9ca3af; word-break: break-all; }
        .footer { padding: 24px 40px; background: #f9fafb; border-top: 1px solid #e5e7eb; text-align: center; }
        .footer p { color: #9ca3af; font-size: 12px; margin: 0; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>{{ __('Agro365') }}</h1>
            <p>{{ __('Alertas gratuitas de mildiu y oídio') }}</p>
        </div>
        <div class="body">
            <p class="message">
                {{ __('Alguien (esperamos que hayas sido tú) ha solicitado recibir avisos gratuitos de riesgo de mildiu y oídio para :municipality en esta dirección de email.', ['municipality' => $municipalityName]) }}
            </p>
            <p class="message">
                {{ __('Para empezar a recibir las alertas, confirma tu suscripción pulsando el siguiente botón. Si no has sido tú, puedes ignorar este email: no se enviará ningún aviso sin confirmar.') }}
            </p>
            <div style="text-align: center;">
                <a href="{{ $confirmUrl }}" class="button">{{ __('Confirmar suscripción') }}</a>
            </div>
            <p class="fallback">{{ __('Si el botón no funciona, copia y pega este enlace en tu navegador:') }}<br>{{ $confirmUrl }}</p>
        </div>
        <div class="footer">
            <p>{{ __('Recibes este email porque se ha solicitado una suscripción a las alertas públicas de Agro365 con tu dirección.') }}</p>
            <p style="margin-top:4px;">© {{ date('Y') }} Agro365 — Todos los derechos reservados</p>
        </div>
    </div>
</body>
</html>
