<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verificar Email - Agro365</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxAppearance
</head>
<body class="bg-agro-50 min-h-screen">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-md bg-white rounded-xl shadow-xs border border-zinc-200 p-8">
            <div class="text-center mb-6">
                <img src="{{ asset('images/logo.png') }}" alt="Agro365" class="h-16 mx-auto mb-4 object-contain">
                <h2 class="text-2xl font-semibold text-zinc-800">Verifica tu Email</h2>
            </div>

            @if(session('status') == 'verification-link-sent')
                <flux:callout variant="success" class="mb-4">
                    <flux:callout.text>Se ha enviado un nuevo enlace de verificación a tu dirección de correo.</flux:callout.text>
                </flux:callout>
            @else
                <p class="text-zinc-600 text-sm mb-6 text-center">
                    Gracias por registrarte. Verifica tu email haciendo clic en el enlace que te enviamos a
                    <strong>{{ auth()->user()->email }}</strong>.
                </p>
            @endif

            <form method="POST" action="{{ route('verification.send') }}" class="space-y-4">
                @csrf
                <flux:button type="submit" variant="primary" class="w-full">
                    Reenviar Email de Verificación
                </flux:button>
            </form>

            <div class="mt-6 text-center">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-zinc-500 hover:text-zinc-700 underline">Cerrar Sesión</button>
                </form>
                <p class="text-xs text-zinc-400 mt-4">¿No recibiste el email? Revisa tu carpeta de spam.</p>
            </div>
        </div>
    </div>
</body>
</html>
