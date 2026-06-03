<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rendimientos de Cosecha en Viñedo: Análisis y Comparativa | Agro365</title>
    <meta name="description" content="Análisis de rendimientos de cosecha en viñedo: kg/ha por parcela, comparativa anual, rendimiento real vs estimado. Optimiza la producción de tu viñedo.">
    <meta name="keywords" content="rendimientos viñedo, kg por hectárea viña, producción viñedo, rendimiento uva, cosecha por parcela, análisis rendimientos, comparativa producción, estimación cosecha, productividad viñedo">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url('/rendimientos-cosecha-vinedo') }}">
    <meta property="og:title" content="Rendimientos de Cosecha en Viñedo - Agro365">
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
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-12">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-amber-100 border border-amber-300 mb-6">
                    <span class="text-lg">📊</span>
                    <span class="text-sm font-semibold text-amber-800">{{ __('Análisis de Producción') }}</span>
                </div>
                <h1 class="text-5xl lg:text-6xl font-bold text-[var(--color-agro-green-dark)] mb-6">{{ __('Rendimientos de Cosecha en Viñedo') }}</h1>
                <p class="text-xl text-gray-600 leading-relaxed">
                    <strong>{{ __('Analiza la producción') }}</strong> de tu viñedo: kg por hectárea, rendimiento por parcela, comparativa entre campañas y análisis de variaciones.
                </p>
            </div>

            <article class="prose prose-lg max-w-none">
                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">{{ __('¿Por Qué Analizar Rendimientos?') }}</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        El <strong>{{ __('rendimiento por hectárea') }}</strong> es el indicador clave de productividad en viticultura. Analizar rendimientos te permite:
                    </p>
                    <ul class="list-disc list-inside space-y-2 text-gray-700 mb-6 ml-4">
                        <li>Identificar <strong>{{ __('parcelas de alto rendimiento') }}</strong> vs bajo rendimiento</li>
                        <li>Comparar <strong>{{ __('rendimiento real vs estimado') }}</strong></li>
                        <li>Analizar <strong>{{ __('tendencias año a año') }}</strong></li>
                        <li>Optimizar decisiones de <strong>{{ __('replantación') }}</strong></li>
                        <li>Cumplir con límites de <strong>{{ __('Denominación de Origen') }}</strong></li>
                    </ul>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">{{ __('Métricas de Rendimiento') }}</h2>
                    <div class="grid md:grid-cols-3 gap-4 mb-6">
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 text-center">
                            <div class="text-4xl font-bold text-[var(--color-agro-green)]">kg/ha</div>
                            <div class="text-sm text-gray-600 mt-2">Rendimiento por hectárea</div>
                            <div class="text-xs text-gray-500 mt-1">Métrica principal</div>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 text-center">
                            <div class="text-4xl font-bold text-amber-600">%</div>
                            <div class="text-sm text-gray-600 mt-2">Real vs Estimado</div>
                            <div class="text-xs text-gray-500 mt-1">Precisión de estimaciones</div>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 text-center">
                            <div class="text-4xl font-bold text-blue-600">Δ</div>
                            <div class="text-sm text-gray-600 mt-2">Variación Anual</div>
                            <div class="text-xs text-gray-500 mt-1">Tendencia interanual</div>
                        </div>
                    </div>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">{{ __('Rendimientos Típicos por DO') }}</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">{{ __('Las Denominaciones de Origen limitan el rendimiento máximo para garantizar calidad:') }}</p>
                    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 mb-6">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left py-2 text-[var(--color-agro-green-dark)]">{{ __('DO/DOCa') }}</th>
                                    <th class="text-right py-2 text-[var(--color-agro-green-dark)]">{{ __('Límite Tinto') }}</th>
                                    <th class="text-right py-2 text-[var(--color-agro-green-dark)]">{{ __('Límite Blanco') }}</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-700">
                                <tr class="border-b"><td class="py-2">{{ __('DOCa Rioja') }}</td><td class="text-right">{{ __('6.500 kg/ha') }}</td><td class="text-right">{{ __('9.000 kg/ha') }}</td></tr>
                                <tr class="border-b"><td class="py-2">{{ __('DO Ribera del Duero') }}</td><td class="text-right">{{ __('7.000 kg/ha') }}</td><td class="text-right">-</td></tr>
                                <tr class="border-b"><td class="py-2">{{ __('DO Rueda') }}</td><td class="text-right">-</td><td class="text-right">{{ __('10.000 kg/ha') }}</td></tr>
                                <tr><td class="py-2">{{ __('DO La Mancha') }}</td><td class="text-right">{{ __('8.000 kg/ha') }}</td><td class="text-right">{{ __('10.000 kg/ha') }}</td></tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">{{ __('Análisis de Rendimientos en Agro365') }}</h2>
                    <div class="grid md:grid-cols-2 gap-6 mb-6">
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">{{ __('📈 Estimación Pre-Vendimia') }}</h3>
                            <p class="text-gray-700">{{ __('Registra estimaciones antes de vendimia basadas en conteo de racimos.') }}</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">{{ __('📊 Comparativa Real vs Estimado') }}</h3>
                            <p class="text-gray-700">Compara la <a href="{{ url('/gestion-vendimia') }}" class="text-[var(--color-agro-green)] hover:underline">cosecha real</a> con las estimaciones.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">{{ __('📅 Histórico por Campaña') }}</h3>
                            <p class="text-gray-700">{{ __('Analiza rendimientos de campañas anteriores y detecta tendencias.') }}</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">{{ __('🗺️ Rendimiento por Parcela') }}</h3>
                            <p class="text-gray-700">Visualiza rendimientos por parcela <a href="{{ content_route('content.que-es-sigpac') }}" class="text-[var(--color-agro-green)] hover:underline">SIGPAC</a>.</p>
                        </div>
                    </div>
                </section>

                <section class="mb-12">
                    <div class="bg-gradient-to-r from-[var(--color-agro-green-bg)] to-[var(--color-agro-green-light)]/30 p-8 rounded-xl border border-[var(--color-agro-green)]/20">
                        <h3 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mb-4">{{ __('📊 Maximiza tus Rendimientos') }}</h3>
                        <p class="text-gray-700 mb-6">
                            Analiza producción por parcela, compara campañas y optimiza decisiones. <strong>{{ __('3 meses gratis') }}</strong>.
                        </p>
                        <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-lg bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:shadow-lg transition-all font-semibold">
                            Comenzar Gratis
                        </a>
                    </div>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">{{ __('Preguntas Frecuentes') }}</h2>
                    <div class="space-y-6">
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">{{ __('¿Cómo se calcula el rendimiento por hectárea?') }}</h3>
                            <p class="text-gray-700">{{ __('Dividiendo los kilogramos cosechados entre la superficie de la parcela en hectáreas.') }}</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">{{ __('¿Puedo comparar rendimientos entre campañas?') }}</h3>
                            <p class="text-gray-700">{{ __('Sí, el sistema guarda el histórico de rendimientos por parcela permitiendo comparar año a año.') }}</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">{{ __('¿Qué es el rendimiento estimado?') }}</h3>
                            <p class="text-gray-700">{{ __('Es una predicción pre-vendimia basada en conteo de racimos y peso medio estimado por racimo.') }}</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">{{ __('¿Se tienen en cuenta los límites de DO?') }}</h3>
                            <p class="text-gray-700">{{ __('Sí, puedes configurar el límite de rendimiento de tu DO y el sistema te alertará si lo superas.') }}</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">{{ __('¿Se puede exportar el análisis de rendimientos?') }}</h3>
                            <p class="text-gray-700">{{ __('Sí, puedes generar informes PDF con el análisis detallado por parcela y campaña.') }}</p>
                        </div>
                    </div>
                </section>
            </article>
        </div>
    </div>
    @include('partials.footer-seo')

    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "FAQPage",
        "mainEntity": [
            {"@@type": "Question", "name": "¿Cómo se calcula el rendimiento por hectárea?", "acceptedAnswer": {"@@type": "Answer", "text": "Dividiendo los kilogramos cosechados entre la superficie de la parcela en hectáreas."}},
            {"@@type": "Question", "name": "¿Puedo comparar rendimientos entre campañas?", "acceptedAnswer": {"@@type": "Answer", "text": "Sí, el sistema guarda el histórico de rendimientos por parcela permitiendo comparar año a año."}},
            {"@@type": "Question", "name": "¿Qué es el rendimiento estimado?", "acceptedAnswer": {"@@type": "Answer", "text": "Es una predicción pre-vendimia basada en conteo de racimos y peso medio estimado por racimo."}},
            {"@@type": "Question", "name": "¿Se tienen en cuenta los límites de DO?", "acceptedAnswer": {"@@type": "Answer", "text": "Sí, puedes configurar el límite de rendimiento de tu DO y el sistema te alertará si lo superas."}},
            {"@@type": "Question", "name": "¿Se puede exportar el análisis de rendimientos?", "acceptedAnswer": {"@@type": "Answer", "text": "Sí, puedes generar informes PDF con el análisis detallado por parcela y campaña."}}
        ]
    }
    </script>
</body>
</html>
