<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verificar Email - Agro365</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link rel="dns-prefetch" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&display=swap" rel="stylesheet" />
    
    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-[var(--color-agro-green-bg)] via-white to-[var(--color-agro-green-bright)]/30 p-4 sm:p-6 lg:p-8">
        <div class="w-full max-w-md bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
            <div class="text-center mb-6">
                <div class="inline-block mx-auto mb-4">
                    <img 
                        src="{{ asset('images/logo.png') }}" 
                        alt="Agro365 Logo" 
                        class="h-16 w-auto object-contain drop-shadow-lg"
                    >
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Verifica tu Email</h2>
            </div>

            @if (session('status') == 'verification-link-sent')
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                    <p class="text-sm font-medium">Se ha enviado un nuevo enlace de verificación a tu dirección de correo electrónico.</p>
                </div>
            @else
                <p class="text-gray-600 text-sm mb-6 text-center">
                    Gracias por registrarte. Antes de continuar, por favor verifica tu email haciendo clic en el enlace que te enviamos a 
                    <strong>{{ auth()->user()->email }}</strong>.
                </p>
            @endif

            <form method="POST" action="{{ route('verification.send') }}" class="space-y-4">
                @csrf
                <button 
                    type="submit"
                    class="w-full bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white py-3.5 px-4 rounded-lg font-bold hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--color-agro-green-dark)] transition-all transform hover:scale-[1.02] shadow-lg"
                >
                    Reenviar Email de Verificación
                </button>
            </form>

            <div class="mt-6 text-center">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-gray-600 hover:text-gray-900 underline">
                        Cerrar Sesión
                    </button>
                </form>
                <p class="text-xs text-gray-500 mt-4">
                    ¿No recibiste el email? Revisa tu carpeta de spam o solicita un nuevo enlace.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
