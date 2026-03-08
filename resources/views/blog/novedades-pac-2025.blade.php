<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Novedades PAC 2025: Lo que Necesitas Saber | Blog Agro365</title>
    <meta name="description" content="Las principales novedades de la PAC 2025: cuaderno digital obligatorio, condicionalidad reforzada, eco-esquemas y nuevos requisitos para viticultores.">
    <meta name="keywords" content="PAC 2025, novedades PAC, cuaderno digital obligatorio, condicionalidad PAC, eco-esquemas, ayudas agrícolas 2025">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url('/blog/novedades-pac-2025') }}">
    <meta property="og:title" content="Novedades PAC 2025 - Blog Agro365">
    <meta property="og:type" content="article">
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
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="text-sm text-gray-500 mb-8">
                <a href="{{ url('/blog') }}" class="hover:text-[var(--color-agro-green)]">Blog</a> → 
                <span>Novedades PAC 2025</span>
            </nav>

            <article class="prose prose-lg max-w-none">
                <div class="mb-8">
                    <span class="text-sm text-gray-500">Diciembre 2024</span>
                    <h1 class="text-4xl lg:text-5xl font-bold text-[var(--color-agro-green-dark)] mt-2">
                        Novedades PAC 2025: Lo que Necesitas Saber
                    </h1>
                </div>

                <p class="text-xl text-gray-600 leading-relaxed mb-8">
                    La <strong>Política Agrícola Común (PAC) 2025</strong> trae cambios importantes para los viticultores. Repasamos las principales novedades que afectan al sector.
                </p>

                <h2 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mt-8 mb-4">1. Cuaderno Digital Obligatorio</h2>
                <p class="text-gray-700 leading-relaxed mb-4">
                    A partir de 2025, el <a href="{{ content_route('content.cuaderno-digital-viticultores') }}" class="text-[var(--color-agro-green)] hover:underline">cuaderno de campo digital</a> será obligatorio para todas las explotaciones que reciban ayudas PAC. Las principales implicaciones:
                </p>
                <ul class="list-disc list-inside space-y-2 text-gray-700 mb-6 ml-4">
                    <li>Registro digital de tratamientos fitosanitarios</li>
                    <li>Vinculación de actividades a parcelas <a href="{{ content_route('content.que-es-sigpac') }}" class="text-[var(--color-agro-green)] hover:underline">SIGPAC</a></li>
                    <li>Interoperabilidad con sistemas oficiales</li>
                    <li>Plazo de registro máximo: 1 mes desde la actividad</li>
                </ul>

                <h2 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mt-8 mb-4">2. Condicionalidad Reforzada</h2>
                <p class="text-gray-700 leading-relaxed mb-4">
                    Los requisitos de condicionalidad se endurecen. Los viticultores deberán cumplir:
                </p>
                <ul class="list-disc list-inside space-y-2 text-gray-700 mb-6 ml-4">
                    <li>BCAM 1: Mantenimiento de pastos permanentes</li>
                    <li>BCAM 4: Franjas de protección de cursos de agua</li>
                    <li>BCAM 7: Rotación de cultivos (adaptar al viñedo)</li>
                    <li>BCAM 8: Superficies no productivas (4%)</li>
                </ul>

                <h2 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mt-8 mb-4">3. Eco-esquemas para Viñedo</h2>
                <p class="text-gray-700 leading-relaxed mb-4">
                    Los eco-esquemas aplicables a viticultura incluyen:
                </p>
                <ul class="list-disc list-inside space-y-2 text-gray-700 mb-6 ml-4">
                    <li>Espacios de biodiversidad: cubiertas vegetales</li>
                    <li>Agroecología: reducción de fitosanitarios</li>
                    <li>Agricultura de precisión: uso de teledetección</li>
                </ul>

                <h2 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mt-8 mb-4">¿Cómo prepararte?</h2>
                <p class="text-gray-700 leading-relaxed mb-4">
                    Con Agro365 puedes cumplir automáticamente con los nuevos requisitos:
                </p>
                <ul class="list-disc list-inside space-y-2 text-gray-700 mb-6 ml-4">
                    <li>Cuaderno digital homologado</li>
                    <li>Dashboard de cumplimiento PAC en tiempo real</li>
                    <li>Alertas de incumplimientos</li>
                    <li><a href="{{ url('/informes-oficiales-agricultura') }}" class="text-[var(--color-agro-green)] hover:underline">Informes oficiales</a> con firma digital</li>
                </ul>

                <div class="bg-gradient-to-r from-[var(--color-agro-green-bg)] to-[var(--color-agro-green-light)]/30 p-8 rounded-xl border border-[var(--color-agro-green)]/20 mt-12">
                    <h3 class="text-xl font-bold text-[var(--color-agro-green-dark)] mb-4">📋 Prepárate para la PAC 2025</h3>
                    <p class="text-gray-700 mb-6">
                        Cuaderno digital, dashboard de cumplimiento e informes oficiales. <strong>3 meses gratis</strong>.
                    </p>
                    <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-lg bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:shadow-lg transition-all font-semibold">
                        Comenzar Gratis
                    </a>
                </div>
            </article>

            <div class="mt-12 pt-8 border-t border-gray-200">
                <a href="{{ url('/blog') }}" class="text-[var(--color-agro-green)] font-semibold hover:underline">← Volver al Blog</a>
            </div>
        </div>
    </div>
    @include('partials.footer-seo')

    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "Article",
        "headline": "Novedades PAC 2025: Lo que Necesitas Saber",
        "datePublished": "2024-12-29",
        "author": {"@@type": "Organization", "name": "Agro365"},
        "publisher": {"@@type": "Organization", "name": "Agro365"}
    }
    </script>
</body>
</html>
