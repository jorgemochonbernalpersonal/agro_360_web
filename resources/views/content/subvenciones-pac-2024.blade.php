<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- SEO Meta Tags -->
    <title>Subvenciones PAC 2024: Ayudas Agrícolas y Requisitos | Agro365</title>
    <meta name="description" content="Guía completa de subvenciones PAC 2024 para agricultores y viticultores en España. Requisitos, plazos, cuantías y cómo cumplir para recibir ayudas agrícolas.">
    <meta name="keywords" content="subvenciones PAC, ayudas PAC, PAC 2024, ayudas agrícolas, subvenciones agricultura, eco-esquemas, pago básico, requisitos PAC, condicionalidad PAC, ayudas viticultores, PAC España">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <meta name="author" content="Agro365">
    <meta name="language" content="Spanish">
    <meta name="geo.region" content="ES">
    
    <link rel="canonical" href="{{ url('/subvenciones-pac-2024') }}">
    
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ url('/subvenciones-pac-2024') }}">
    <meta property="og:title" content="Subvenciones PAC 2024 - Guía de Ayudas Agrícolas">
    <meta property="og:description" content="Todo sobre las ayudas PAC para agricultores. Requisitos, plazos y cómo cumplir.">
    <meta property="og:image" content="{{ asset('images/logo.png') }}">
    <meta property="og:locale" content="es_ES">
    
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
                    <img src="{{ asset('images/logo.png') }}" alt="Agro365" width="120" height="40" loading="eager" class="h-10 w-auto">
                    <span class="text-xl font-bold text-[var(--color-agro-green-dark)]">Agro365</span>
                </a>
                <div class="flex items-center gap-4">
                    <a href="{{ url('/') }}" class="text-gray-600 hover:text-[var(--color-agro-green)]">Inicio</a>
                    @guest
                        <a href="{{ route('register') }}" class="px-4 py-2 rounded-lg bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white">Comenzar Gratis</a>
                    @endguest
                </div>
            </div>
        </nav>
    </header>

    <div class="min-h-screen bg-gradient-to-b from-white to-gray-50 py-20">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="mb-8 text-sm text-gray-600">
                <ol class="flex items-center space-x-2">
                    <li><a href="{{ url('/') }}" class="hover:text-[var(--color-agro-green)]">Inicio</a></li>
                    <span class="mx-2">/</span>
                    <li class="text-gray-900">Subvenciones PAC 2024</li>
                </ol>
            </nav>

            <div class="mb-12">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-blue-100 border border-blue-300 mb-6">
                    <span class="text-lg">💶</span>
                    <span class="text-sm font-semibold text-blue-800">Ayudas PAC 2024</span>
                </div>
                <h1 class="text-5xl lg:text-6xl font-bold text-[var(--color-agro-green-dark)] mb-6">
                    Subvenciones PAC 2024: Guía Completa de Ayudas Agrícolas
                </h1>
                <p class="text-xl text-gray-600 leading-relaxed">
                    Todo lo que necesitas saber sobre las <strong>ayudas de la PAC</strong> en 2024. Requisitos de condicionalidad, eco-esquemas, pago básico y cómo cumplir con la normativa para recibir tus subvenciones.
                </p>
            </div>

            <article class="prose prose-lg max-w-none">
                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">¿Qué es la PAC?</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        La <strong>PAC (Política Agraria Común)</strong> es el sistema de subvenciones agrícolas de la Unión Europea. Es la principal fuente de ingresos para muchos agricultores y viticultores en España.
                    </p>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        En 2024, la PAC incluye varios tipos de ayudas:
                    </p>
                    <ul class="list-disc list-inside space-y-2 text-gray-700 mb-6 ml-4">
                        <li><strong>Pago Básico:</strong> Ayuda directa por hectárea cultivada</li>
                        <li><strong>Pago Verde (Eco-esquemas):</strong> Ayuda por prácticas beneficiosas para el medio ambiente</li>
                        <li><strong>Ayudas Asociadas:</strong> Ayudas específicas por tipo de cultivo</li>
                        <li><strong>Pago Joven Agricultor:</strong> Complemento para menores de 40 años</li>
                    </ul>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">Requisitos de Condicionalidad</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        Para recibir las ayudas PAC, debes cumplir con los <strong>requisitos de condicionalidad</strong>:
                    </p>
                    <div class="grid md:grid-cols-2 gap-4 mb-6">
                        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-[var(--color-agro-green-dark)] mb-2">📋 Cuaderno de Campo</h3>
                            <p class="text-gray-700 text-sm">
                                <a href="{{ content_route('content.cuaderno-digital-viticultores') }}" class="text-[var(--color-agro-green)] hover:underline">Cuaderno de campo digital</a> obligatorio desde 2027 con todas las actividades registradas.
                            </p>
                        </div>
                        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-[var(--color-agro-green-dark)] mb-2">🗺️ SIGPAC Correcto</h3>
                            <p class="text-gray-700 text-sm">
                                Parcelas con códigos <a href="{{ content_route('content.que-es-sigpac') }}" class="text-[var(--color-agro-green)] hover:underline">SIGPAC</a> correctamente declarados.
                            </p>
                        </div>
                        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-[var(--color-agro-green-dark)] mb-2">🧪 Fitosanitarios</h3>
                            <p class="text-gray-700 text-sm">
                                <a href="{{ url('/registro-fitosanitarios') }}" class="text-[var(--color-agro-green)] hover:underline">Registro de tratamientos</a> con productos autorizados y dosis correctas.
                            </p>
                        </div>
                        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-[var(--color-agro-green-dark)] mb-2">🌿 Buenas Prácticas</h3>
                            <p class="text-gray-700 text-sm">Cumplimiento de normas medioambientales y sanitarias establecidas.</p>
                        </div>
                    </div>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">Cuantías de las Ayudas PAC 2024</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        Las cuantías varían según la región y el tipo de cultivo:
                    </p>
                    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 mb-6">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left py-2 text-[var(--color-agro-green-dark)]">Tipo de Ayuda</th>
                                    <th class="text-right py-2 text-[var(--color-agro-green-dark)]">Cuantía Aproximada</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-700">
                                <tr class="border-b"><td class="py-2">Pago Básico (viñedo)</td><td class="text-right">~150-300€/ha</td></tr>
                                <tr class="border-b"><td class="py-2">Eco-esquema básico</td><td class="text-right">~50-100€/ha</td></tr>
                                <tr class="border-b"><td class="py-2">Eco-esquema avanzado</td><td class="text-right">~100-200€/ha</td></tr>
                                <tr><td class="py-2">Pago joven agricultor</td><td class="text-right">+25% sobre básico</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="bg-yellow-50 border-l-4 border-yellow-500 p-6 rounded-r-lg mb-6">
                        <p class="text-gray-700">
                            <strong>💡 Nota:</strong> Las cuantías exactas dependen de la región (Comunidad Autónoma) y el histórico de derechos de pago.
                        </p>
                    </div>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">Plazos Importantes PAC 2024</h2>
                    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 mb-6">
                        <div class="space-y-4">
                            <div class="flex items-center gap-4">
                                <div class="w-24 text-center bg-green-100 text-green-800 font-bold py-2 px-3 rounded">Febrero</div>
                                <div class="text-gray-700">Inicio del plazo de solicitud</div>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="w-24 text-center bg-amber-100 text-amber-800 font-bold py-2 px-3 rounded">30 Abril</div>
                                <div class="text-gray-700">Fin del plazo ordinario</div>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="w-24 text-center bg-red-100 text-red-800 font-bold py-2 px-3 rounded">25 Mayo</div>
                                <div class="text-gray-700">Fin plazo con penalización (1% por día)</div>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="w-24 text-center bg-blue-100 text-blue-800 font-bold py-2 px-3 rounded">Diciembre</div>
                                <div class="text-gray-700">Pago de ayudas</div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">Cómo Cumplir con la PAC usando Agro365</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        <a href="{{ content_route('content.software-viticultores') }}" class="text-[var(--color-agro-green)] hover:underline">Agro365</a> incluye un <strong>Dashboard de Cumplimiento PAC</strong> que valida automáticamente:
                    </p>
                    <ul class="list-disc list-inside space-y-2 text-gray-700 mb-6 ml-4">
                        <li>✅ Códigos SIGPAC correctamente declarados</li>
                        <li>✅ Cuaderno de campo completo y sin errores</li>
                        <li>✅ Tratamientos fitosanitarios con productos autorizados</li>
                        <li>✅ Actividades registradas con fechas correctas</li>
                        <li>✅ Informes oficiales listos para inspección</li>
                    </ul>
                    <div class="bg-gradient-to-r from-[var(--color-agro-green-bg)] to-[var(--color-agro-green-light)]/30 p-8 rounded-xl border border-[var(--color-agro-green)]/20">
                        <h3 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mb-4">📊 Dashboard PAC en Tiempo Real</h3>
                        <p class="text-gray-700 mb-6">
                            Sabe en todo momento si cumples con los requisitos PAC. Detecta errores antes de las inspecciones. <strong>6 meses gratis</strong>.
                        </p>
                        <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-lg bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:shadow-lg transition-all font-semibold">
                            Comenzar Gratis
                        </a>
                    </div>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">Preguntas Frecuentes sobre PAC</h2>
                    <div class="space-y-6">
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">¿Qué pasa si no cumplo los requisitos?</h3>
                            <p class="text-gray-700">Puedes perder parcial o totalmente las ayudas PAC. Las penalizaciones van del 1% al 100% según la gravedad.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">¿Es obligatorio el cuaderno digital?</h3>
                            <p class="text-gray-700">Sí, desde 2027 será obligatorio. <a href="{{ content_route('content.cuaderno-digital-viticultores') }}" class="text-[var(--color-agro-green)] hover:underline">Más información aquí</a>.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">¿Cómo me preparo para una inspección?</h3>
                            <p class="text-gray-700">Mantén el cuaderno de campo actualizado, SIGPAC correcto y tratamientos registrados. Agro365 valida todo automáticamente.</p>
                        </div>
                    </div>
                </section>
            </article>

            <div class="mt-16 pt-12 border-t border-gray-200 text-center">
                <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">Cumple con la PAC sin Complicaciones</h2>
                <p class="text-gray-600 mb-8 text-lg">Dashboard de cumplimiento en tiempo real. No pierdas tus ayudas por errores evitables.</p>
                <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-8 py-4 rounded-xl bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white shadow-lg font-semibold text-lg">
                    Comenzar Gratis - 6 Meses
                </a>
            </div>
        </div>
    </div>

    @include('partials.footer-seo')

    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "Article",
        "headline": "Subvenciones PAC 2024: Guía Completa de Ayudas Agrícolas",
        "description": "Todo sobre las ayudas PAC para agricultores en España. Requisitos, plazos y cómo cumplir.",
        "author": {"@@type": "Organization", "name": "Agro365"},
        "publisher": {"@@type": "Organization", "name": "Agro365"},
        "datePublished": "2024-01-01",
        "dateModified": "{{ now()->toIso8601String() }}"
    }
    </script>

    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "FAQPage",
        "mainEntity": [
            {"@@type": "Question", "name": "¿Qué pasa si no cumplo los requisitos PAC?", "acceptedAnswer": {"@@type": "Answer", "text": "Puedes perder parcial o totalmente las ayudas PAC. Las penalizaciones van del 1% al 100% según la gravedad."}},
            {"@@type": "Question", "name": "¿Es obligatorio el cuaderno digital?", "acceptedAnswer": {"@@type": "Answer", "text": "Sí, desde 2027 será obligatorio para todos los agricultores."}},
            {"@@type": "Question", "name": "¿Cómo me preparo para una inspección?", "acceptedAnswer": {"@@type": "Answer", "text": "Mantén el cuaderno de campo actualizado, SIGPAC correcto y tratamientos registrados."}}
        ]
    }
    </script>
</body>
</html>
