<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Software para Viticultores en DO Rías Baixas | Agro365</title>
    <meta name="description" content="Software de gestión agrícola para viticultores de DO Rías Baixas. Especializado en Albariño. Cuaderno de campo digital, SIGPAC Galicia y trazabilidad.">
    <meta name="keywords" content="viticultores rias baixas, software galicia, DO Rías Baixas, cuaderno campo galicia, gestión viñedo galicia, albariño, software bodega galicia, viticultura gallega">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url('/software-viticultores-rias-baixas') }}">
    <meta property="og:title" content="Software para Viticultores en Rías Baixas - Agro365">
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
                <span class="text-gray-700">Rías Baixas</span>
            </nav>

            <div class="mb-12">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-cyan-100 border border-cyan-300 mb-6">
                    <span class="text-lg">🌊</span>
                    <span class="text-sm font-semibold text-cyan-800">DO Rías Baixas</span>
                </div>
                <h1 class="text-5xl lg:text-6xl font-bold text-[var(--color-agro-green-dark)] mb-6">
                    Software para Viticultores en Rías Baixas
                </h1>
                <p class="text-xl text-gray-600 leading-relaxed">
                    Gestión agrícola <strong>especializada para DO Rías Baixas</strong>. Control de Albariño, emparrados y viticultura atlántica en Galicia.
                </p>
            </div>

            <article class="prose prose-lg max-w-none">
                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">Subzonas de Rías Baixas</h2>
                    <div class="grid md:grid-cols-2 gap-4 mb-6">
                        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-cyan-700">Val do Salnés</h3>
                            <p class="text-gray-700 text-sm">La subzona más grande. Albariño al 100%.</p>
                        </div>
                        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-cyan-700">Condado do Tea</h3>
                            <p class="text-gray-700 text-sm">Frontera con Portugal. Albariño y Treixadura.</p>
                        </div>
                        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-cyan-700">O Rosal</h3>
                            <p class="text-gray-700 text-sm">Desembocadura del Miño. Clima atlántico.</p>
                        </div>
                        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-cyan-700">Soutomaior y Ribeira do Ulla</h3>
                            <p class="text-gray-700 text-sm">Zonas interiores con microclima propio.</p>
                        </div>
                    </div>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">Variedades DO Rías Baixas</h2>
                    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 mb-6">
                        <h3 class="font-bold text-lg text-amber-700 mb-2">⚪ Blancas (100% de la DO)</h3>
                        <ul class="text-gray-700 text-sm space-y-1">
                            <li>• <strong>Albariño</strong> - Variedad principal (96% de viñedo)</li>
                            <li>• Treixadura</li>
                            <li>• Loureira</li>
                            <li>• Caíño Blanco</li>
                            <li>• Godello</li>
                        </ul>
                    </div>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">Rendimiento DO Rías Baixas</h2>
                    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 mb-6">
                        <div class="text-center py-4">
                            <div class="text-5xl font-bold text-cyan-600">12.000</div>
                            <div class="text-gray-600 mt-2">kg/ha máximo (Albariño)</div>
                        </div>
                    </div>
                </section>

                <section class="mb-12">
                    <div class="bg-gradient-to-r from-cyan-50 to-cyan-100/30 p-8 rounded-xl border border-cyan-200">
                        <h3 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mb-4">🌊 Gestiona tu Viñedo en Rías Baixas</h3>
                        <p class="text-gray-700 mb-6">
                            Software para viticultura atlántica gallega. <strong>6 meses gratis</strong>.
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
            {"@type": "ListItem", "position": 3, "name": "Rías Baixas", "item": "{{ url('/software-viticultores-rias-baixas') }}"}
        ]
    }
    </script>
</body>
</html>
