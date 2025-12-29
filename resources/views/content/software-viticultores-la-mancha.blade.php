<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Software para Viticultores en DO La Mancha | Agro365</title>
    <meta name="description" content="Software de gestión agrícola para viticultores de DO La Mancha, la mayor región vitícola del mundo. Control de Airén, Tempranillo, Cencibel. Cuaderno y trazabilidad.">
    <meta name="keywords" content="viticultores la mancha, software la mancha, DO La Mancha, cuaderno campo mancha, gestión viñedo mancha, airén, cencibel, tempranillo mancha, software bodega mancha">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url('/software-viticultores-la-mancha') }}">
    <meta property="og:title" content="Software para Viticultores en La Mancha - Agro365">
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
                <span class="text-gray-700">La Mancha</span>
            </nav>

            <div class="mb-12">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-orange-100 border border-orange-300 mb-6">
                    <span class="text-lg">🏜️</span>
                    <span class="text-sm font-semibold text-orange-800">DO La Mancha</span>
                </div>
                <h1 class="text-5xl lg:text-6xl font-bold text-[var(--color-agro-green-dark)] mb-6">
                    Software para Viticultores en La Mancha
                </h1>
                <p class="text-xl text-gray-600 leading-relaxed">
                    Gestión agrícola para <strong>DO La Mancha</strong>, la mayor denominación de origen del mundo por superficie. Control de grandes extensiones con eficiencia.
                </p>
            </div>

            <article class="prose prose-lg max-w-none">
                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">La Mancha en Cifras</h2>
                    <div class="grid md:grid-cols-3 gap-4 mb-6">
                        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 text-center">
                            <div class="text-3xl font-bold text-orange-600">160.000</div>
                            <div class="text-sm text-gray-600">hectáreas</div>
                        </div>
                        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 text-center">
                            <div class="text-3xl font-bold text-orange-600">4</div>
                            <div class="text-sm text-gray-600">provincias</div>
                        </div>
                        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 text-center">
                            <div class="text-3xl font-bold text-orange-600">182</div>
                            <div class="text-sm text-gray-600">municipios</div>
                        </div>
                    </div>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">Variedades DO La Mancha</h2>
                    <div class="grid md:grid-cols-2 gap-6 mb-6">
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-amber-700 mb-2">⚪ Blancas</h3>
                            <ul class="text-gray-700 text-sm space-y-1">
                                <li>• <strong>Airén</strong> - La más plantada del mundo</li>
                                <li>• Macabeo (Viura)</li>
                                <li>• Chardonnay</li>
                                <li>• Sauvignon Blanc</li>
                            </ul>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-red-700 mb-2">🔴 Tintas</h3>
                            <ul class="text-gray-700 text-sm space-y-1">
                                <li>• <strong>Cencibel</strong> (Tempranillo)</li>
                                <li>• Garnacha</li>
                                <li>• Cabernet Sauvignon</li>
                                <li>• Syrah</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">Rendimientos DO La Mancha</h2>
                    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 mb-6">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left py-2 text-[var(--color-agro-green-dark)]">Tipo</th>
                                    <th class="text-right py-2 text-[var(--color-agro-green-dark)]">Límite</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-700">
                                <tr class="border-b"><td class="py-2">Tintas</td><td class="text-right font-bold">8.000 kg/ha</td></tr>
                                <tr><td class="py-2">Blancas</td><td class="text-right font-bold">10.000 kg/ha</td></tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">Funcionalidades para La Mancha</h2>
                    <ul class="list-disc list-inside space-y-3 text-gray-700 mb-6 ml-4">
                        <li><a href="{{ route('content.cuaderno-digital-viticultores') }}" class="text-[var(--color-agro-green)] hover:underline">Cuaderno de campo</a> para gestionar grandes extensiones</li>
                        <li>SIGPAC de Ciudad Real, Toledo, Cuenca y Albacete</li>
                        <li>Control de múltiples parcelas con eficiencia</li>
                        <li><a href="{{ url('/facturacion-agricola') }}" class="text-[var(--color-agro-green)] hover:underline">Facturación</a> optimizada para cooperativas</li>
                    </ul>
                </section>

                <section class="mb-12">
                    <div class="bg-gradient-to-r from-orange-50 to-orange-100/30 p-8 rounded-xl border border-orange-200">
                        <h3 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mb-4">🏜️ Gestiona tu Viñedo en La Mancha</h3>
                        <p class="text-gray-700 mb-6">
                            Software para gestionar grandes extensiones en DO La Mancha. <strong>6 meses gratis</strong>.
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
            {"@type": "ListItem", "position": 3, "name": "La Mancha", "item": "{{ url('/software-viticultores-la-mancha') }}"}
        ]
    }
    </script>
</body>
</html>

