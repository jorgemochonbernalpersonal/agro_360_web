<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Declaración Responsable VeriFactu - Agro365</title>
    <meta name="description" content="Declaración responsable del sistema informático de facturación Agro365, conforme al Real Decreto 1007/2023 y la Orden HAC/1177/2024 (VeriFactu).">
    <meta name="robots" content="index, follow">
    <meta name="author" content="Agro365">

    <link rel="canonical" href="{{ url()->current() }}">

    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-50">
    <div class="min-h-screen py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto">
            <div class="mb-8">
                <a href="{{ url('/') }}" class="inline-flex items-center text-[var(--color-agro-green-dark)] hover:text-[var(--color-agro-green)] mb-4">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    {{ __('Volver a Inicio') }}
                </a>
                <h1 class="text-4xl font-bold text-gray-900">{{ __('Cumplimiento normativo VeriFactu') }}</h1>
                <p class="mt-2 text-gray-600">{{ __('Declaración responsable del sistema informático de facturación Agro365, disponible para clientes y comercializadores.') }}</p>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-8">
                @include('partials.verifactu-declaracion-responsable')
            </div>

            <div class="mt-8 flex flex-wrap gap-4 justify-center text-sm">
                <a href="{{ route('aviso-legal') }}" class="text-[var(--color-agro-green-dark)] hover:underline">{{ __('Aviso Legal') }}</a>
                <span class="text-gray-400">•</span>
                <a href="{{ route('privacy') }}" class="text-[var(--color-agro-green-dark)] hover:underline">{{ __('Política de Privacidad') }}</a>
            </div>
        </div>
    </div>

    @include('partials.footer-seo')
</body>
</html>
