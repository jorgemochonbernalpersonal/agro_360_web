<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Software para Viticultores en DO Penedès | Agro365</title>
    <meta name="description" content="Software de gestión agrícola para viticultores de DO Penedès y DO Cava. Control de Xarel·lo, Macabeo, Parellada. Cuaderno de campo y trazabilidad.">
    <meta name="keywords" content="viticultores penedes, software penedes, DO Penedès, DO Cava, cuaderno campo penedes, gestión viñedo catalunya, xarello, macabeo, parellada, software bodega cava">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url('/software-viticultores-penedes') }}">
    <meta property="og:title" content="Software para Viticultores en Penedès - Agro365">
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
                <span class="text-gray-700">Penedès</span>
            </nav>

            <div class="mb-12">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-yellow-100 border border-yellow-300 mb-6">
                    <span class="text-lg">🍾</span>
                    <span class="text-sm font-semibold text-yellow-800">DO Penedès / DO Cava</span>
                </div>
                <h1 class="text-5xl lg:text-6xl font-bold text-[var(--color-agro-green-dark)] mb-6">
                    Software para Viticultores en Penedès
                </h1>
                <p class="text-xl text-gray-600 leading-relaxed">
                    Gestión agrícola <strong>especializada para DO Penedès y DO Cava</strong>. Control de variedades catalanas, rendimientos y trazabilidad para productores de vino y cava.
                </p>
            </div>

            <article class="prose prose-lg max-w-none">
                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">Variedades DO Penedès</h2>
                    <div class="grid md:grid-cols-2 gap-6 mb-6">
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-yellow-700 mb-2">⚪ Blancas (Cava)</h3>
                            <ul class="text-gray-700 text-sm space-y-1">
                                <li>• <strong>Xarel·lo</strong></li>
                                <li>• <strong>Macabeo</strong> (Viura)</li>
                                <li>• <strong>Parellada</strong></li>
                                <li>• Chardonnay</li>
                            </ul>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-red-700 mb-2">🔴 Tintas</h3>
                            <ul class="text-gray-700 text-sm space-y-1">
                                <li>• Garnacha</li>
                                <li>• Monastrell</li>
                                <li>• Cabernet Sauvignon</li>
                                <li>• Merlot</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">Funcionalidades para Penedès</h2>
                    <ul class="list-disc list-inside space-y-3 text-gray-700 mb-6 ml-4">
                        <li><a href="{{ route('content.cuaderno-digital-viticultores') }}" class="text-[var(--color-agro-green)] hover:underline">Cuaderno de campo digital</a> para DO Penedès y DO Cava</li>
                        <li>Parcelas SIGPAC de Barcelona y Tarragona</li>
                        <li>Control de producción de base para Cava</li>
                        <li><a href="{{ url('/trazabilidad-vino-origen') }}" class="text-[var(--color-agro-green)] hover:underline">Trazabilidad</a> para Consejo Regulador del Cava</li>
                    </ul>
                </section>

                <section class="mb-12">
                    <div class="bg-gradient-to-r from-yellow-50 to-yellow-100/30 p-8 rounded-xl border border-yellow-200">
                        <h3 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mb-4">🍾 Gestiona tu Viñedo en Penedès</h3>
                        <p class="text-gray-700 mb-6">
                            Software para viticultores de DO Penedès y productores de Cava. <strong>6 meses gratis</strong>.
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
            {"@type": "ListItem", "position": 3, "name": "Penedès", "item": "{{ url('/software-viticultores-penedes') }}"}
        ]
    }
    </script>
</body>
</html>

