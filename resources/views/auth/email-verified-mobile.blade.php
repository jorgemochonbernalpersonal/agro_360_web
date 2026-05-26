<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email verificado · Agro365</title>
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
        p  { font-size: 15px; color: #6b7c6b; line-height: 1.6; margin-bottom: 8px; }
        .highlight { color: #4a7c59; font-weight: 600; }
        .divider { border: none; border-top: 1px solid #f0fdf4; margin: 24px 0; }
        .hint { font-size: 13px; color: #9ca39c; }
        .btn {
            display: inline-block; margin-top: 20px;
            background: #4a7c59; color: #fff;
            padding: 14px 32px; border-radius: 12px;
            font-size: 15px; font-weight: 600; text-decoration: none;
            cursor: pointer; border: none; width: 100%;
        }
        .btn:hover { background: #3a6347; }
    </style>
    <script>
        setTimeout(function() { window.location = 'agro365://email-verified'; }, 1500);
    </script>
</head>
<body>
    <div class="card">
        <img src="{{ rtrim(config('app.url'), '/') }}/images/logo.png" alt="Agro365" class="logo">

        <div class="icon-wrap">
            <svg viewBox="0 0 24 24" fill="none" stroke="#4a7c59" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 6L9 17l-5-5"/>
            </svg>
        </div>

        <h1>{{ __('¡Email verificado!') }}</h1>
        <p>{{ __('Tu cuenta de') }} <span class="highlight">{{ __('Agro365') }}</span> {{ __('está activada.') }}</p>
        <p>{{ __('Ya puedes iniciar sesión desde la app.') }}</p>

        <hr class="divider">

        <p class="hint">{{ __('Abriendo la app automáticamente…') }}</p>
        <a href="agro365://email-verified" class="btn">{{ __('Abrir Agro365') }}</a>
    </div>
</body>
</html>
