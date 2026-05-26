<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $broadcastSubject }}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; margin: 0; padding: 0; background: #f4f4f5; }
        .wrapper { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .header { background: #16a34a; padding: 32px 40px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 22px; font-weight: 700; }
        .header p { color: rgba(255,255,255,0.8); margin: 4px 0 0; font-size: 13px; }
        .body { padding: 40px; }
        .message { font-size: 15px; line-height: 1.7; color: #374151; white-space: pre-wrap; }
        .footer { padding: 24px 40px; background: #f9fafb; border-top: 1px solid #e5e7eb; text-align: center; }
        .footer p { color: #9ca3af; font-size: 12px; margin: 0; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>{{ __('Agro365') }}</h1>
            <p>{{ __('Comunicación del equipo') }}</p>
        </div>
        <div class="body">
            <div class="message">{{ $broadcastMessage }}</div>
        </div>
        <div class="footer">
            <p>{{ __('Has recibido este email porque tienes una cuenta en Agro365.') }}</p>
            <p style="margin-top:4px;">© {{ date('Y') }} Agro365 — Todos los derechos reservados</p>
        </div>
    </div>
</body>
</html>
