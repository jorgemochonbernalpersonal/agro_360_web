<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Abrir en Agro365</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(160deg, #0f2508 0%, #2d5016 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .card {
            background: #fff;
            border-radius: 20px;
            padding: 40px 32px;
            max-width: 420px;
            width: 100%;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .logo { height: 56px; width: auto; margin-bottom: 28px; }
        .icon-wrap {
            width: 72px; height: 72px; border-radius: 50%;
            background: #dcfce7;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 20px;
        }
        .icon-wrap svg { width: 36px; height: 36px; }
        h1 { font-size: 22px; font-weight: 700; color: #1a2e1a; margin-bottom: 10px; }
        p  { font-size: 15px; color: #6b7c6b; line-height: 1.6; margin-bottom: 24px; }
        .btn-app {
            display: block;
            background: #4a7c59;
            color: #fff;
            text-decoration: none;
            font-size: 16px;
            font-weight: 600;
            padding: 14px 24px;
            border-radius: 12px;
            margin-bottom: 16px;
            transition: background 0.2s;
        }
        .btn-app:hover { background: #3a6347; }
        .divider { border: none; border-top: 1px solid #f0fdf4; margin: 4px 0 16px; }
        .link-web {
            font-size: 13px;
            color: #9ca39c;
            text-decoration: none;
        }
        .link-web:hover { color: #4a7c59; text-decoration: underline; }
    </style>
</head>
<body>
    <div class="card">
        <img src="{{ rtrim(config('app.url'), '/') }}/images/logo.png" alt="Agro365" class="logo">

        <div class="icon-wrap">
            <svg viewBox="0 0 24 24" fill="none" stroke="#4a7c59" stroke-width="2.5"
                 stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                <path d="M2 17l10 5 10-5"/>
                <path d="M2 12l10 5 10-5"/>
            </svg>
        </div>

        <h1>Continúa en Agro365</h1>
        <p>Abre la aplicación para ver este contenido, o continúa desde la web.</p>

        <a href="{{ $deepUrl }}" class="btn-app">Abrir en la app</a>

        <div class="divider"></div>

        <a href="{{ $webUrl }}" class="link-web">Continuar en la web →</a>
    </div>
</body>
</html>
