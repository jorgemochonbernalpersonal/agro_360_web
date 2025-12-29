<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Software para Viticultores en Ribera del Duero | Agro365</title>
    <meta name="description" content="Software de gestión agrícola para viticultores de DO Ribera del Duero. Cuaderno de campo digital, control de Tinto Fino (Tempranillo), rendimientos y trazabilidad.">
    <meta name="keywords" content="viticultores ribera duero, software ribera, DO Ribera del Duero, cuaderno campo ribera, gestión viñedo ribera, tinto fino, tempranillo ribera, software bodega ribera">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url('/software-viticultores-ribera-duero') }}">
    <meta property="og:title" content="Software para Viticultores en Ribera del Duero - Agro365">
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
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Breadcrumbs -->
            <nav class="text-sm text-gray-500 mb-6">
                <a href="{{ url('/') }}" class="hover:text-[var(--color-agro-green)]">Inicio</a> → 
                <a href="{{ route('content.software-viticultores') }}" class="hover:text-[var(--color-agro-green)]">Software Viticultores</a> → 
                <span class="text-gray-700">Ribera del Duero</span>
            </nav>

            <div class="mb-12">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-purple-100 border border-purple-300 mb-6">
                    <span class="text-lg">🍇</span>
                    <span class="text-sm font-semibold text-purple-800">DO Ribera del Duero</span>
                </div>
                <h1 class="text-5xl lg:text-6xl font-bold text-[var(--color-agro-green-dark)] mb-6">
                    Software para Viticultores en Ribera del Duero
                </h1>
                <p class="text-xl text-gray-600 leading-relaxed">
                    Gestión agrícola <strong>especializada para DO Ribera del Duero</strong>. Control del Tinto Fino (Tempranillo), rendimientos máximos y trazabilidad completa para el Consejo Regulador.
                </p>
            </div>

            <article class="prose prose-lg max-w-none">
                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">Variedades Autorizadas</h2>
                    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 mb-6">
                        <h3 class="font-bold text-lg text-purple-700 mb-2">🔴 Tintas (principales)</h3>
                        <ul class="text-gray-700 text-sm space-y-1 mb-4">
                            <li>• <strong>Tinto Fino</strong> (Tempranillo) - Principal</li>
                            <li>• Cabernet Sauvignon</li>
                            <li>• Merlot</li>
                            <li>• Malbec</li>
                            <li>• Garnacha Tinta</li>
                        </ul>
                        <p class="text-gray-600 text-sm">El Tinto Fino debe representar al menos el 75% del viñedo para vinos con DO.</p>
                    </div>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">Rendimiento DO Ribera del Duero</h2>
                    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 mb-6">
                        <div class="text-center py-4">
                            <div class="text-5xl font-bold text-purple-600">7.000</div>
                            <div class="text-gray-600 mt-2">kg/ha máximo</div>
                        </div>
                        <p class="text-gray-600 text-sm mt-4">Agro365 calcula automáticamente tus <a href="{{ url('/rendimientos-cosecha-viñedo') }}" class="text-[var(--color-agro-green)] hover:underline">rendimientos</a> y te alerta si superas el límite de la DO.</p>
                    </div>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">Funcionalidades para Ribera</h2>
                    <ul class="list-disc list-inside space-y-3 text-gray-700 mb-6 ml-4">
                        <li><a href="{{ route('content.cuaderno-digital-viticultores') }}" class="text-[var(--color-agro-green)] hover:underline">Cuaderno de campo digital</a> adaptado a DO Ribera</li>
                        <li>Parcelas SIGPAC de Burgos, Valladolid, Soria y Segovia</li>
                        <li>Control de <a href="{{ url('/registro-fitosanitarios') }}" class="text-[var(--color-agro-green)] hover:underline">tratamientos</a> y plazos de seguridad</li>
                        <li><a href="{{ url('/trazabilidad-vino-origen') }}" class="text-[var(--color-agro-green)] hover:underline">Trazabilidad</a> para el Consejo Regulador</li>
                    </ul>
                </section>

                <section class="mb-12">
                    <div class="bg-gradient-to-r from-purple-50 to-purple-100/30 p-8 rounded-xl border border-purple-200">
                        <h3 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mb-4">🍇 Gestiona tu Viñedo en Ribera</h3>
                        <p class="text-gray-700 mb-6">
                            Software para viticultores de DO Ribera del Duero. Cumple normativa del Consejo Regulador. <strong>6 meses gratis</strong>.
                        </p>
                        <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-lg bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:shadow-lg transition-all font-semibold">
                            Comenzar Gratis
                        </a>
                    </div>
                </section>
            </article>
        </div>
    </div>
    @include('partials.footer-seo')

    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "BreadcrumbList",
        "itemListElement": [
            {"@type": "ListItem", "position": 1, "name": "Inicio", "item": "{{ url('/') }}"},
            {"@type": "ListItem", "position": 2, "name": "Software Viticultores", "item": "{{ route('content.software-viticultores') }}"},
            {"@type": "ListItem", "position": 3, "name": "Ribera del Duero", "item": "{{ url('/software-viticultores-ribera-duero') }}"}
        ]
    }
    </script>
</body>
</html>

