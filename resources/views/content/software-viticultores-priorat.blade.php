<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Software para Viticultores en DOQ Priorat | Agro365</title>
    <meta name="description" content="Software de gestión agrícola para viticultores de DOQ Priorat. Control de Garnacha, Cariñena en viñedos de licorella. Cuaderno de campo y trazabilidad.">
    <meta name="keywords" content="viticultores priorat, software priorat, DOQ Priorat, cuaderno campo priorat, gestión viñedo priorat, garnacha priorat, cariñena, licorella, software bodega priorat">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url('/software-viticultores-priorat') }}">
    <meta property="og:title" content="Software para Viticultores en Priorat - Agro365">
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
                <span class="text-gray-700">Priorat</span>
            </nav>

            <div class="mb-12">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-slate-100 border border-slate-300 mb-6">
                    <span class="text-lg">🏔️</span>
                    <span class="text-sm font-semibold text-slate-800">DOQ Priorat</span>
                </div>
                <h1 class="text-5xl lg:text-6xl font-bold text-[var(--color-agro-green-dark)] mb-6">
                    Software para Viticultores en Priorat
                </h1>
                <p class="text-xl text-gray-600 leading-relaxed">
                    Gestión agrícola <strong>especializada para DOQ Priorat</strong>. Control de variedades en viñedos de licorella, pendientes extremas y viticultura heroica.
                </p>
            </div>

            <article class="prose prose-lg max-w-none">
                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">El Terroir del Priorat</h2>
                    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 mb-6">
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <h3 class="font-bold text-slate-700 mb-2">🏔️ Licorella</h3>
                                <p class="text-gray-700 text-sm">Suelo de pizarra que da carácter mineral único a los vinos.</p>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-700 mb-2">📐 Pendientes</h3>
                                <p class="text-gray-700 text-sm">Viñedos en terrazas con pendientes de hasta 60%.</p>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">Variedades DOQ Priorat</h2>
                    <div class="grid md:grid-cols-2 gap-6 mb-6">
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-red-700 mb-2">🔴 Tintas</h3>
                            <ul class="text-gray-700 text-sm space-y-1">
                                <li>• <strong>Garnacha Tinta</strong> (mín. 40%)</li>
                                <li>• <strong>Cariñena</strong> (Samsó)</li>
                                <li>• Cabernet Sauvignon</li>
                                <li>• Syrah</li>
                            </ul>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-amber-700 mb-2">⚪ Blancas</h3>
                            <ul class="text-gray-700 text-sm space-y-1">
                                <li>• Garnacha Blanca</li>
                                <li>• Macabeo</li>
                                <li>• Pedro Ximénez</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">Rendimiento DOQ Priorat</h2>
                    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 mb-6">
                        <div class="text-center py-4">
                            <div class="text-5xl font-bold text-slate-600">6.000</div>
                            <div class="text-gray-600 mt-2">kg/ha máximo</div>
                        </div>
                        <p class="text-gray-600 text-sm mt-4">Uno de los rendimientos más bajos de España, garantizando máxima concentración.</p>
                    </div>
                </section>

                <section class="mb-12">
                    <div class="bg-gradient-to-r from-slate-50 to-slate-100/30 p-8 rounded-xl border border-slate-200">
                        <h3 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mb-4">🏔️ Gestiona tu Viñedo en Priorat</h3>
                        <p class="text-gray-700 mb-6">
                            Software para viticultura heroica en DOQ Priorat. <strong>6 meses gratis</strong>.
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
            {"@type": "ListItem", "position": 3, "name": "Priorat", "item": "{{ url('/software-viticultores-priorat') }}"}
        ]
    }
    </script>
</body>
</html>
