<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Blog Agro365 - Noticias y Consejos para Viticultores</title>
    <meta name="description" content="Blog de agricultura y viticultura: novedades PAC, consejos para viticultores, calendario de labores, normativa agrícola y más.">
    <meta name="keywords" content="blog viticultura, noticias agricultura, consejos viticultores, PAC 2025, cuaderno campo, normativa agrícola">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url('/blog') }}">
    <meta property="og:title" content="Blog Agro365 - Noticias para Viticultores">
    <meta property="og:image" content="{{ asset('images/logo.png') }}">
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
                @endguest
            </div>
        </nav>
    </header>

    <div class="min-h-screen bg-gradient-to-b from-white to-gray-50 py-20">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <h1 class="text-5xl font-bold text-[var(--color-agro-green-dark)] mb-4">
                    Blog Agro365
                </h1>
                <p class="text-xl text-gray-600">
                    Noticias, consejos y novedades para viticultores profesionales
                </p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Artículo 1 -->
                <article class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow">
                    <div class="h-48 bg-gradient-to-br from-green-100 to-green-50 flex items-center justify-center">
                        <span class="text-6xl">📋</span>
                    </div>
                    <div class="p-6">
                        <div class="text-sm text-gray-500 mb-2">Diciembre 2024</div>
                        <h2 class="text-xl font-bold text-[var(--color-agro-green-dark)] mb-3">
                            <a href="{{ url('/blog/novedades-pac-2025') }}" class="hover:text-[var(--color-agro-green)]">
                                Novedades PAC 2025: Lo que necesitas saber
                            </a>
                        </h2>
                        <p class="text-gray-600 text-sm mb-4">
                            Las principales novedades de la PAC para 2025: cuaderno digital obligatorio, condicionalidad reforzada y nuevos requisitos.
                        </p>
                        <a href="{{ url('/blog/novedades-pac-2025') }}" class="text-[var(--color-agro-green)] font-semibold text-sm hover:underline">
                            Leer más →
                        </a>
                    </div>
                </article>

                <!-- Artículo 2 -->
                <article class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow">
                    <div class="h-48 bg-gradient-to-br from-amber-100 to-amber-50 flex items-center justify-center">
                        <span class="text-6xl">⚠️</span>
                    </div>
                    <div class="p-6">
                        <div class="text-sm text-gray-500 mb-2">Diciembre 2024</div>
                        <h2 class="text-xl font-bold text-[var(--color-agro-green-dark)] mb-3">
                            <a href="{{ url('/blog/errores-cuaderno-campo') }}" class="hover:text-[var(--color-agro-green)]">
                                5 Errores Comunes en el Cuaderno de Campo
                            </a>
                        </h2>
                        <p class="text-gray-600 text-sm mb-4">
                            Evita sanciones: los errores más frecuentes que cometen los viticultores al llevar el cuaderno de campo digital.
                        </p>
                        <a href="{{ url('/blog/errores-cuaderno-campo') }}" class="text-[var(--color-agro-green)] font-semibold text-sm hover:underline">
                            Leer más →
                        </a>
                    </div>
                </article>

                <!-- Artículo 3 -->
                <article class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow">
                    <div class="h-48 bg-gradient-to-br from-purple-100 to-purple-50 flex items-center justify-center">
                        <span class="text-6xl">📅</span>
                    </div>
                    <div class="p-6">
                        <div class="text-sm text-gray-500 mb-2">Diciembre 2024</div>
                        <h2 class="text-xl font-bold text-[var(--color-agro-green-dark)] mb-3">
                            <a href="{{ url('/blog/calendario-viticola-2025') }}" class="hover:text-[var(--color-agro-green)]">
                                Calendario Vitícola 2025: Mes a Mes
                            </a>
                        </h2>
                        <p class="text-gray-600 text-sm mb-4">
                            Planifica tu campaña 2025: todas las labores del viñedo organizadas por mes con fechas orientativas.
                        </p>
                        <a href="{{ url('/blog/calendario-viticola-2025') }}" class="text-[var(--color-agro-green)] font-semibold text-sm hover:underline">
                            Leer más →
                        </a>
                    </div>
                </article>
            </div>

            <div class="mt-16 text-center">
                <div class="bg-gradient-to-r from-[var(--color-agro-green-bg)] to-[var(--color-agro-green-light)]/30 p-8 rounded-xl border border-[var(--color-agro-green)]/20 inline-block">
                    <h3 class="text-xl font-bold text-[var(--color-agro-green-dark)] mb-4">📧 Suscríbete al Newsletter</h3>
                    <p class="text-gray-700 mb-4">Recibe las últimas noticias y consejos en tu email.</p>
                    <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-lg bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:shadow-lg transition-all font-semibold">
                        Comenzar Gratis
                    </a>
                </div>
            </div>
        </div>
    </div>
    @include('partials.footer-seo')
</body>
</html>
