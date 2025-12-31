<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Software para Bodegas | Gestión Profesional de Bodegas - Agro365</title>
    <meta name="description" content="Software profesional para bodegas en España. Gestión de producción, trazabilidad, control de vendimia y cumplimiento normativo. Prueba gratis 6 meses.">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url('/bodegas') }}">
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-white">
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center gap-3">
                    <a href="{{ url('/') }}" class="flex items-center gap-3">
                        <img src="{{ asset('images/logo.png') }}" alt="Agro365" width="120" height="40" loading="eager" class="h-10 w-auto">
                        <span class="text-xl font-bold text-[var(--color-agro-green-dark)]">Agro365</span>
                    </a>
                    <span class="px-2 py-1 text-xs font-bold rounded-full bg-blue-100 text-blue-700 border border-blue-300">BETA</span>
                </div>
                <div class="flex items-center gap-4">
                    <a href="{{ url('/') }}" class="text-gray-600 hover:text-[var(--color-agro-green)] transition-colors">Inicio</a>
                    <a href="{{ route('faqs') }}" class="text-gray-600 hover:text-[var(--color-agro-green)] transition-colors">FAQs</a>
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}" class="text-gray-600 hover:text-[var(--color-agro-green)] transition-colors">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="text-gray-600 hover:text-[var(--color-agro-green)] transition-colors">Entrar</a>
                            <a href="{{ route('register') }}" class="px-4 py-2 rounded-lg bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:shadow-lg transition-all">
                                Comenzar Gratis
                            </a>
                        @endauth
                    @endif
                </div>
            </div>
        </nav>
    </header>
    <div class="min-h-screen bg-gradient-to-b from-white to-gray-50 py-20">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="mb-8 text-sm text-gray-600" itemscope itemtype="https://schema.org/BreadcrumbList">
                <ol class="flex items-center space-x-2">
                    <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                        <a href="{{ url('/') }}" class="hover:text-[var(--color-agro-green)]" itemprop="item"><span itemprop="name">Inicio</span></a>
                        <meta itemprop="position" content="1" />
                    </li>
                    <span class="mx-2">/</span>
                    <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                        <span class="text-gray-900" itemprop="name">Bodegas</span>
                        <meta itemprop="position" content="2" />
                    </li>
                </ol>
            </nav>
            <!-- Header -->
            <div class="mb-12">
                <h1 class="text-5xl lg:text-6xl font-bold text-[var(--color-agro-green-dark)] mb-6">
                    Software para Bodegas Profesionales
                </h1>
                <p class="text-xl text-gray-600 leading-relaxed">
                    Control total desde la recepción de uva hasta el embotellado. <strong>Trazabilidad integral</strong>, gestión de depósitos, control de calidad y cumplimiento normativo para Denominaciones de Origen.
                </p>
            </div>

            <!-- Content -->
            <article class="prose prose-lg max-w-none">
                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">La Transformación Digital de tu Bodega</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        En una <strong>bodega</strong>, la información es tan valiosa como el vino. Agro365 te permite digitalizar todos los procesos críticos de tu bodega, garantizando una trazabilidad perfecta que aporta valor y seguridad a tu marca.
                    </p>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        Nuestra plataforma conecta el campo con la bodega, integrando los datos de vendimia directamente en tu sistema de gestión de producción.
                    </p>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-6">Funcionalidades para la Gestión Vinícola</h2>
                    <div class="grid md:grid-cols-2 gap-6 not-prose">
                        <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">📥 Recepción y Pesaje</h3>
                            <p class="text-gray-600 text-sm">Registro rápido de entradas de uva por socio o parcela, control de grados baumé y estado sanitario.</p>
                        </div>
                        <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">🏭 Gestión de Depósitos</h3>
                            <p class="text-gray-600 text-sm">Control visual de tu parque de depósitos, inventario en tiempo real y movimientos entre envases.</p>
                        </div>
                        <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">🔍 Trazabilidad de Origen</h3>
                            <p class="text-gray-600 text-sm">Conoce exactamente de qué parcela procede cada litro de vino. Cumple con los requisitos de los Consejos Reguladores.</p>
                        </div>
                        <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">📦 Escandallos y Costes</h3>
                            <p class="text-gray-600 text-sm">Análisis detallado de costes de producción, desde la materia prima hasta los materiales auxiliares.</p>
                        </div>
                    </div>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">Seguridad y Rigor Normativo</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        Cumplir con los libros de bodega y las declaraciones obligatorias (AICA, INFOVI) puede ser una tarea tediosa. Agro365 automatiza la recopilación de datos para que generar estos informes sea cuestión de minutos, no de días.
                    </p>
                    <div class="bg-red-50 p-6 rounded-xl border border-red-100">
                        <h4 class="font-bold text-red-900 mb-2">Optimización de Procesos</h4>
                        <p class="text-red-800 text-sm italic">
                            Los usuarios de Agro365 reportan una reducción media del 40% en el tiempo dedicado a la gestión administrativa de la trazabilidad.
                        </p>
                    </div>
                </section>

                <div class="mt-16 pt-12 border-t border-gray-200 text-center">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-6">Lleva tu bodega al siguiente nivel</h2>
                    <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-8 py-4 rounded-xl bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:from-[var(--color-agro-green)] hover:to-[var(--color-agro-green-dark)] transition-all duration-300 shadow-lg hover:shadow-xl font-semibold text-lg">
                        Probar Agro365 Gratis
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                    <p class="mt-4 text-sm text-gray-500">Prueba gratuita de 6 meses para las primeras 50 bodegas</p>
                </div>
            </article>
        </div>
    </div>
    @include('partials.footer-seo')
</body>
</html>

