<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Software para Viticultores en DO Toro | Agro365</title>
    <meta name="description" content="Software de gestión agrícola para viticultores de DO Toro. Especializado en Tinta de Toro (Tempranillo). Cuaderno de campo y trazabilidad Zamora.">
    <meta name="keywords" content="viticultores toro, software toro, DO Toro, cuaderno campo toro, gestión viñedo zamora, tinta de toro, tempranillo toro, software bodega toro">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url('/software-viticultores-toro') }}">
    <meta property="og:title" content="Software para Viticultores en Toro - Agro365">
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
                <span class="text-gray-700">Toro</span>
            </nav>

            <div class="mb-12">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-rose-100 border border-rose-300 mb-6">
                    <span class="text-lg">🐂</span>
                    <span class="text-sm font-semibold text-rose-800">DO Toro</span>
                </div>
                <h1 class="text-5xl lg:text-6xl font-bold text-[var(--color-agro-green-dark)] mb-6">
                    Software para Viticultores en Toro
                </h1>
                <p class="text-xl text-gray-600 leading-relaxed">
                    Gestión agrícola <strong>especializada para DO Toro</strong>. Control de Tinta de Toro, viñedos centenarios y vinos de alta concentración.
                </p>
            </div>

            <article class="prose prose-lg max-w-none">
                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">La Tinta de Toro</h2>
                    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 mb-6">
                        <p class="text-gray-700 leading-relaxed">
                            La <strong>Tinta de Toro</strong> es un clon único de Tempranillo adaptado al clima extremo de Zamora. Viñedos centenarios en pie franco (sin filoxera) producen vinos de extraordinaria concentración.
                        </p>
                    </div>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">Variedades DO Toro</h2>
                    <div class="grid md:grid-cols-2 gap-6 mb-6">
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-red-700 mb-2">🔴 Tintas</h3>
                            <ul class="text-gray-700 text-sm space-y-1">
                                <li>• <strong>Tinta de Toro</strong> (Tempranillo) - Principal</li>
                                <li>• Garnacha Tinta</li>
                            </ul>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-amber-700 mb-2">⚪ Blancas</h3>
                            <ul class="text-gray-700 text-sm space-y-1">
                                <li>• Malvasía</li>
                                <li>• Verdejo</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">Rendimiento DO Toro</h2>
                    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 mb-6">
                        <div class="text-center py-4">
                            <div class="text-5xl font-bold text-rose-600">6.000</div>
                            <div class="text-gray-600 mt-2">kg/ha máximo</div>
                        </div>
                    </div>
                </section>

                <section class="mb-12">
                    <div class="bg-gradient-to-r from-rose-50 to-rose-100/30 p-8 rounded-xl border border-rose-200">
                        <h3 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mb-4">🐂 Gestiona tu Viñedo en Toro</h3>
                        <p class="text-gray-700 mb-6">
                            Software para viñedos de alta concentración en DO Toro. <strong>6 meses gratis</strong>.
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
            {"@type": "ListItem", "position": 3, "name": "Toro", "item": "{{ url('/software-viticultores-toro') }}"}
        ]
    }
    </script>
</body>
</html>
