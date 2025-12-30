<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Normativa PAC | Cambios y Cumplimiento - Agro365</title>
    <meta name="description" content="Normativa PAC: cambios, requisitos y cumplimiento.">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url('/normativa-pac') }}">
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-white">
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <a href="{{ url('/') }}" class="flex items-center gap-3">
                    <img src="{{ asset('images/logo.png') }}" alt="Agro365" class="h-10 w-auto">
                    <span class="text-xl font-bold text-[var(--color-agro-green-dark)]">Agro365</span>
                </a>
                @guest
                    <a href="{{ route('register') }}" class="px-4 py-2 rounded-lg bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white">Comenzar Gratis</a>
                @else
                    <a href="{{ route(auth()->user()->role . '.dashboard') }}" class="px-4 py-2 rounded-lg bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white">Ir al Dashboard</a>
                @endguest
            </div>
        </nav>
    </header>

    <div class="min-h-screen bg-gradient-to-b from-white to-gray-50 py-20">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="text-sm text-gray-500 mb-6">
                <a href="{{ url('/') }}" class="hover:text-[var(--color-agro-green)]">Inicio</a> - 
                <span class="text-gray-700">Normativa PAC</span>
            </nav>

            <h1 class="text-5xl font-bold text-[var(--color-agro-green-dark)] mb-6">
                Normativa PAC: Cambios y Cumplimiento
            </h1>
            <p class="text-xl text-gray-600 leading-relaxed mb-12">
                Guia completa sobre la Politica Agraria Comun (PAC). Descubre los cambios normativos y como Agro365 te ayuda a cumplir automaticamente.
            </p>

            <article class="prose prose-lg max-w-none">
                <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">Que es la PAC?</h2>
                <p class="text-gray-700 mb-6">
                    La Politica Agraria Comun (PAC) es el sistema de ayudas y normativas de la Union Europea para el sector agricola.
                </p>
                
                <ul class="list-disc list-inside space-y-2 text-gray-700 mb-6 ml-4">
                    <li>Cuaderno de campo digital obligatorio</li>
                    <li>Integracion obligatoria con codigos SIGPAC</li>
                    <li>Informes oficiales con firma electronica</li>
                    <li>Trazabilidad completa de actividades</li>
                </ul>

                <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">Cambios en la Normativa PAC</h2>
                <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-[var(--color-agro-green)] mb-6">
                    <h3 class="font-bold text-xl text-[var(--color-agro-green-dark)] mb-2">1. Cuaderno de Campo Digital Obligatorio</h3>
                    <p class="text-gray-700">El cuaderno de campo en papel ya no sera valido. Todas las explotaciones deben tener un cuaderno digital.</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-[var(--color-agro-green)] mb-6">
                    <h3 class="font-bold text-xl text-[var(--color-agro-green-dark)] mb-2">2. Integracion SIGPAC Obligatoria</h3>
                    <p class="text-gray-700">Todas las actividades deben estar asociadas a codigos SIGPAC validos.</p>
                </div>

                <div class="bg-gradient-to-r from-green-50 to-green-100/30 p-8 rounded-xl border border-green-200 mt-12">
                    <h3 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mb-4">Cumple con la Normativa PAC</h3>
                    <p class="text-gray-700 mb-6">
                        Agro365 te ayuda a cumplir automaticamente con todos los requisitos PAC. 6 meses gratis.
                    </p>
                    @guest
                        <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-lg bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:shadow-lg transition-all font-semibold">
                            Comenzar Gratis
                        </a>
                    @else
                        <a href="{{ route(auth()->user()->role . '.dashboard') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-lg bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:shadow-lg transition-all font-semibold">
                            Ir al Dashboard
                        </a>
                    @endguest
                </div>
            </article>
        </div>
    </div>


    <!-- ✅ SEO: Enlaces relacionados para mejorar link juice interno -->
    @include('components.related-links')

    @include('partials.footer-seo')
</body>
</html>
