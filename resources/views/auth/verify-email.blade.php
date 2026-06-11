<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-flux-appearance="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Verifica tu Email') }} - Agro365</title>
    <link rel="icon" type="image/png" href="{{ asset('images/icon_512x512.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxAppearance
</head>
<body class="bg-agro-50 min-h-screen flex items-center justify-center py-8 px-4">

    <div class="w-full max-w-md mx-auto">

        {{-- Logo --}}
        <div class="text-center mb-8">
            <a href="{{ url('/') }}" class="inline-block">
                <img src="{{ asset('images/logo-nav.png') }}" alt="Agro365" width="128"
                     class="mx-auto max-h-14 object-contain transition-transform hover:scale-105">
            </a>
            <p class="mt-2 text-sm text-zinc-500">{{ __('Cuaderno de campo digital para viticultores') }}</p>
        </div>

        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="px-6 py-5">

                <div class="text-center mb-6">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-agro-100 mb-4">
                        <svg class="size-6 text-agro-700" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                        </svg>
                    </div>
                    <h1 class="text-xl font-semibold text-zinc-800">{{ __('Verifica tu Email') }}</h1>
                    <p class="mt-1 text-sm text-zinc-500">{{ __('Revisa tu bandeja de entrada') }}</p>
                </div>

                @if(session('status') == 'verification-link-sent')
                    <flux:callout variant="success" icon="check-circle" class="mb-5">
                        <flux:callout.text>
                            {{ __('Se ha enviado un nuevo enlace de verificación a tu correo electrónico.') }}
                        </flux:callout.text>
                    </flux:callout>
                @else
                    <p class="text-sm text-zinc-600 text-center mb-6">
                        {{ __('Gracias por registrarte. Hemos enviado un enlace de verificación a') }}
                        <strong class="text-zinc-800">{{ auth()->user()->email }}</strong>.
                        {{ __('Haz clic en el enlace para activar tu cuenta.') }}
                    </p>
                @endif

                <form method="POST" action="{{ route('verification.send') }}" class="space-y-3">
                    @csrf
                    <flux:button type="submit" variant="primary" class="w-full">
                        {{ __('Reenviar Email de Verificación') }}
                    </flux:button>
                </form>

                <div class="mt-6 pt-5 border-t border-zinc-100 flex items-center justify-between">
                    <p class="text-xs text-zinc-400">{{ __('¿No lo ves? Revisa tu carpeta de spam.') }}</p>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="text-sm text-zinc-500 hover:text-zinc-700 hover:underline font-medium">
                            {{ __('Cerrar Sesión') }}
                        </button>
                    </form>
                </div>

            </div>
        </div>

    </div>

</body>
</html>
