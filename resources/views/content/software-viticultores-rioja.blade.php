<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Software para Viticultores en DOCa Rioja | Agro365</title>
    <meta name="description" content="Software de gestión agrícola especializado para viticultores de DOCa Rioja. Cuaderno de campo digital, control SIGPAC, rendimientos por variedad. Tempranillo, Garnacha, Viura.">
    <meta name="keywords" content="viticultores rioja, software rioja, DOCa Rioja, cuaderno campo rioja, gestión viñedo rioja, tempranillo, garnacha, viura, software bodega rioja">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url('/software-viticultores-rioja') }}">
    <meta property="og:title" content="Software para Viticultores en Rioja - Agro365">
    <meta property="og:description" content="Gestión agrícola especializada para DOCa Rioja.">
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
                <span class="text-gray-700">Rioja</span>
            </nav>

            <div class="mb-12">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-red-100 border border-red-300 mb-6">
                    <span class="text-lg">🍷</span>
                    <span class="text-sm font-semibold text-red-800">DOCa Rioja</span>
                </div>
                <h1 class="text-5xl lg:text-6xl font-bold text-[var(--color-agro-green-dark)] mb-6">
                    Software para Viticultores en La Rioja
                </h1>
                <p class="text-xl text-gray-600 leading-relaxed">
                    Gestión agrícola <strong>especializada para DOCa Rioja</strong>. Control de variedades autóctonas, rendimientos máximos permitidos y cumplimiento normativo específico de la denominación.
                </p>
            </div>

            <article class="prose prose-lg max-w-none">
                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">Variedades de La Rioja</h2>
                    <div class="grid md:grid-cols-2 gap-6 mb-6">
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-red-700 mb-2">🔴 Tintas</h3>
                            <ul class="text-gray-700 text-sm space-y-1">
                                <li>• <strong>Tempranillo</strong> (Tinta del País)</li>
                                <li>• Garnacha Tinta</li>
                                <li>• Graciano</li>
                                <li>• Mazuelo (Cariñena)</li>
                                <li>• Maturana Tinta</li>
                            </ul>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-amber-700 mb-2">⚪ Blancas</h3>
                            <ul class="text-gray-700 text-sm space-y-1">
                                <li>• <strong>Viura</strong> (Macabeo)</li>
                                <li>• Malvasía</li>
                                <li>• Garnacha Blanca</li>
                                <li>• Tempranillo Blanco</li>
                                <li>• Maturana Blanca</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">Rendimientos DOCa Rioja</h2>
                    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 mb-6">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left py-2 text-[var(--color-agro-green-dark)]">Tipo</th>
                                    <th class="text-right py-2 text-[var(--color-agro-green-dark)]">Límite kg/ha</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-700">
                                <tr class="border-b"><td class="py-2">Variedades tintas</td><td class="text-right font-bold">6.500 kg/ha</td></tr>
                                <tr><td class="py-2">Variedades blancas</td><td class="text-right font-bold">9.000 kg/ha</td></tr>
                            </tbody>
                        </table>
                        <p class="text-gray-600 text-sm mt-4">Agro365 calcula automáticamente tus <a href="{{ url('/rendimientos-cosecha-viñedo') }}" class="text-[var(--color-agro-green)] hover:underline">rendimientos</a> y te alerta si superas los límites.</p>
                    </div>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">Funcionalidades para Rioja</h2>
                    <ul class="list-disc list-inside space-y-3 text-gray-700 mb-6 ml-4">
                        <li><a href="{{ route('content.cuaderno-digital-viticultores') }}" class="text-[var(--color-agro-green)] hover:underline">Cuaderno de campo digital</a> adaptado a normativa DOCa</li>
                        <li>Control de <a href="{{ url('/registro-fitosanitarios') }}" class="text-[var(--color-agro-green)] hover:underline">tratamientos fitosanitarios</a></li>
                        <li>Gestión de <a href="{{ url('/gestion-vendimia') }}" class="text-[var(--color-agro-green)] hover:underline">vendimia</a> con trazabilidad completa</li>
                        <li>Códigos <a href="{{ route('content.sigpac') }}" class="text-[var(--color-agro-green)] hover:underline">SIGPAC</a> de La Rioja integrados</li>
                        <li><a href="{{ url('/informes-oficiales-agricultura') }}" class="text-[var(--color-agro-green)] hover:underline">Informes oficiales</a> para Consejo Regulador</li>
                    </ul>
                </section>

                <section class="mb-12">
                    <div class="bg-gradient-to-r from-red-50 to-red-100/30 p-8 rounded-xl border border-red-200">
                        <h3 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mb-4">🍷 Gestiona tu Viñedo en Rioja</h3>
                        <p class="text-gray-700 mb-6">
                            Software especializado para viticultores de DOCa Rioja. Cumple con la normativa del Consejo Regulador. <strong>6 meses gratis</strong>.
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
            {"@type": "ListItem", "position": 3, "name": "Rioja", "item": "{{ url('/software-viticultores-rioja') }}"}
        ]
    }
    </script>
</body>
</html>

