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
    <meta property="og:image" content="{{ asset('images/dashboard-preview.png') }}">
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
                    <span class="text-xl font-bold text-[var(--color-agro-green-dark)]">{{ __('Agro365') }}</span>
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
                <span>{{ __('Novedades PAC 2025') }}</span>
            </nav>

            <article class="prose prose-lg max-w-none">
                <div class="mb-8">
                    <span class="text-sm text-gray-500">{{ __('Diciembre 2024') }}</span>
                    <h1 class="text-4xl lg:text-5xl font-bold text-[var(--color-agro-green-dark)] mt-2">{{ __('Novedades PAC 2025: Lo que Necesitas Saber') }}</h1>
                </div>

                <p class="text-xl text-gray-600 leading-relaxed mb-8">
                    La <strong>{{ __('Política Agrícola Común (PAC) 2025') }}</strong> trae cambios importantes para los viticultores. Repasamos las principales novedades que afectan al sector.
                </p>

                <h2 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mt-8 mb-4">{{ __('1. Cuaderno Digital Obligatorio') }}</h2>
                <p class="text-gray-700 leading-relaxed mb-4">
                    A partir de 2025, el <a href="{{ content_route('content.cuaderno-digital-viticultores') }}" class="text-[var(--color-agro-green)] hover:underline">cuaderno de campo digital</a> será obligatorio para todas las explotaciones que reciban ayudas PAC. Las principales implicaciones:
                </p>
                <ul class="list-disc list-inside space-y-2 text-gray-700 mb-6 ml-4">
                    <li>{{ __('Registro digital de tratamientos fitosanitarios') }}</li>
                    <li>Vinculación de actividades a parcelas <a href="{{ content_route('content.que-es-sigpac') }}" class="text-[var(--color-agro-green)] hover:underline">SIGPAC</a></li>
                    <li>{{ __('Interoperabilidad con sistemas oficiales') }}</li>
                    <li>{{ __('Plazo de registro máximo: 1 mes desde la actividad') }}</li>
                </ul>

                <h2 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mt-8 mb-4">{{ __('2. Condicionalidad Reforzada') }}</h2>
                <p class="text-gray-700 leading-relaxed mb-4">{{ __('Los requisitos de condicionalidad se endurecen. Los viticultores deberán cumplir:') }}</p>
                <ul class="list-disc list-inside space-y-2 text-gray-700 mb-6 ml-4">
                    <li>{{ __('BCAM 1: Mantenimiento de pastos permanentes') }}</li>
                    <li>{{ __('BCAM 4: Franjas de protección de cursos de agua') }}</li>
                    <li>{{ __('BCAM 7: Rotación de cultivos (adaptar al viñedo)') }}</li>
                    <li>{{ __('BCAM 8: Superficies no productivas (4%)') }}</li>
                </ul>

                <h2 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mt-8 mb-4">{{ __('3. Eco-esquemas para Viñedo') }}</h2>
                <p class="text-gray-700 leading-relaxed mb-4">{{ __('Los eco-esquemas aplicables a viticultura incluyen:') }}</p>
                <ul class="list-disc list-inside space-y-2 text-gray-700 mb-6 ml-4">
                    <li>{{ __('Espacios de biodiversidad: cubiertas vegetales') }}</li>
                    <li>{{ __('Agroecología: reducción de fitosanitarios') }}</li>
                    <li>{{ __('Agricultura de precisión: uso de teledetección') }}</li>
                </ul>

                <h2 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mt-8 mb-4">{{ __('¿Cómo prepararte?') }}</h2>
                <p class="text-gray-700 leading-relaxed mb-4">{{ __('Con Agro365 puedes cumplir automáticamente con los nuevos requisitos:') }}</p>
                <ul class="list-disc list-inside space-y-2 text-gray-700 mb-6 ml-4">
                    <li>{{ __('Cuaderno digital homologado') }}</li>
                    <li>{{ __('Dashboard de cumplimiento PAC en tiempo real') }}</li>
                    <li>{{ __('Alertas de incumplimientos') }}</li>
                    <li><a href="{{ url('/informes-oficiales-agricultura') }}" class="text-[var(--color-agro-green)] hover:underline">Informes oficiales</a> con firma digital</li>
                </ul>

                <div class="bg-gradient-to-r from-[var(--color-agro-green-bg)] to-[var(--color-agro-green-light)]/30 p-8 rounded-xl border border-[var(--color-agro-green)]/20 mt-12">
                    <h3 class="text-xl font-bold text-[var(--color-agro-green-dark)] mb-4">{{ __('📋 Prepárate para la PAC 2025') }}</h3>
                    <p class="text-gray-700 mb-6">
                        Cuaderno digital, dashboard de cumplimiento e informes oficiales. <strong>{{ __('3 meses gratis') }}</strong>.
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
        "@@type": "BlogPosting",
        "headline": "Novedades PAC 2025: Lo que Necesitas Saber",
        "description": "Las principales novedades de la PAC 2025: cuaderno digital obligatorio, condicionalidad reforzada, eco-esquemas y nuevos requisitos para viticultores.",
        "image": "{{ asset('images/dashboard-preview.png') }}",
        "url": "{{ url('/blog/novedades-pac-2025') }}",
        "datePublished": "2024-12-29",
        "dateModified": "2024-12-29",
        "author": {"@@type": "Organization", "name": "Agro365", "url": "{{ url('/') }}"},
        "publisher": {"@@type": "Organization", "name": "Agro365", "logo": {"@@type": "ImageObject", "url": "{{ asset('images/logo.png') }}"}}
    }
    </script>

    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "BreadcrumbList",
        "itemListElement": [
            {"@@type": "ListItem", "position": 1, "name": "Inicio", "item": "{{ url('/') }}"},
            {"@@type": "ListItem", "position": 2, "name": "Blog", "item": "{{ url('/blog') }}"},
            {"@@type": "ListItem", "position": 3, "name": "Novedades PAC 2025", "item": "{{ url('/blog/novedades-pac-2025') }}"}
        ]
    }
    </script>
</body>
</html>
