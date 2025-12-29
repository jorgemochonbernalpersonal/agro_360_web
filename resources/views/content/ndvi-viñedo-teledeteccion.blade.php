<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>NDVI en Viñedo: Teledetección y Análisis Satelital | Agro365</title>
    <meta name="description" content="Análisis NDVI de viñedos por satélite: índice de vegetación, salud del cultivo, detección temprana de estrés. Teledetección gratuita con NASA y Sentinel-2.">
    <meta name="keywords" content="NDVI viñedo, teledetección viñedo, índice vegetación vid, análisis satelital viña, Sentinel-2 agricultura, estrés hídrico viñedo, salud viñedo satélite, agricultura de precisión, NASA earthdata viñedo">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url('/ndvi-viñedo-teledeteccion') }}">
    <meta property="og:title" content="NDVI en Viñedo - Teledetección Satelital">
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
            <div class="mb-12">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-emerald-100 border border-emerald-300 mb-6">
                    <span class="text-lg">🛰️</span>
                    <span class="text-sm font-semibold text-emerald-800">Teledetección Satelital</span>
                </div>
                <h1 class="text-5xl lg:text-6xl font-bold text-[var(--color-agro-green-dark)] mb-6">
                    NDVI en Viñedo: Análisis Satelital
                </h1>
                <p class="text-xl text-gray-600 leading-relaxed">
                    Monitoriza la <strong>salud de tu viñedo desde el espacio</strong>. Análisis NDVI, NDWI, temperatura y humedad del suelo con datos de NASA Earthdata y Sentinel-2. Sin hardware, sin coste adicional.
                </p>
            </div>

            <article class="prose prose-lg max-w-none">
                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">¿Qué es el NDVI?</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        El <strong>NDVI</strong> (Normalized Difference Vegetation Index) es un índice que mide la "verdor" o actividad fotosintética de las plantas. Se calcula a partir de imágenes satelitales comparando la luz roja y la infrarroja cercana.
                    </p>
                    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 mb-6">
                        <h3 class="font-bold text-[var(--color-agro-green-dark)] mb-4">📊 Escala NDVI</h3>
                        <div class="flex items-center gap-2 mb-4">
                            <div class="flex-1 h-4 rounded bg-gradient-to-r from-red-500 via-yellow-500 to-green-600"></div>
                        </div>
                        <div class="grid grid-cols-3 text-sm text-center">
                            <div><span class="font-bold text-red-600">0.2-0.3</span><br>Vegetación escasa</div>
                            <div><span class="font-bold text-yellow-600">0.4-0.5</span><br>Vegetación moderada</div>
                            <div><span class="font-bold text-green-600">0.6-0.9</span><br>Vegetación vigorosa</div>
                        </div>
                    </div>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">Índices Disponibles en Agro365</h2>
                    <div class="grid md:grid-cols-2 gap-4 mb-6">
                        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                            <div class="flex items-center gap-3 mb-2">
                                <span class="text-2xl">🌿</span>
                                <h3 class="font-bold text-[var(--color-agro-green-dark)]">NDVI</h3>
                            </div>
                            <p class="text-gray-700 text-sm">Vigor vegetativo y actividad fotosintética.</p>
                        </div>
                        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                            <div class="flex items-center gap-3 mb-2">
                                <span class="text-2xl">💧</span>
                                <h3 class="font-bold text-[var(--color-agro-green-dark)]">NDWI</h3>
                            </div>
                            <p class="text-gray-700 text-sm">Contenido de agua en la vegetación.</p>
                        </div>
                        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                            <div class="flex items-center gap-3 mb-2">
                                <span class="text-2xl">🌡️</span>
                                <h3 class="font-bold text-[var(--color-agro-green-dark)]">Temperatura</h3>
                            </div>
                            <p class="text-gray-700 text-sm">Temperatura superficial y del aire.</p>
                        </div>
                        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                            <div class="flex items-center gap-3 mb-2">
                                <span class="text-2xl">🏜️</span>
                                <h3 class="font-bold text-[var(--color-agro-green-dark)]">Humedad Suelo</h3>
                            </div>
                            <p class="text-gray-700 text-sm">Contenido de humedad en diferentes profundidades.</p>
                        </div>
                    </div>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">Aplicaciones Prácticas</h2>
                    <ul class="list-disc list-inside space-y-3 text-gray-700 mb-6 ml-4">
                        <li><strong>Detección temprana de estrés:</strong> Identifica zonas con problemas antes de que sean visibles</li>
                        <li><strong>Gestión del riego:</strong> Optimiza el riego basándote en datos reales de humedad</li>
                        <li><strong>Comparativa entre parcelas:</strong> Compara el vigor de diferentes parcelas <a href="{{ route('content.sigpac') }}" class="text-[var(--color-agro-green)] hover:underline">SIGPAC</a></li>
                        <li><strong>Histórico de evolución:</strong> Analiza cómo cambia el viñedo a lo largo del año</li>
                        <li><strong>Decisiones de <a href="{{ url('/registro-fitosanitarios') }}" class="text-[var(--color-agro-green)] hover:underline">tratamiento</a>:</strong> Prioriza zonas que necesitan atención</li>
                    </ul>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">Funcionalidades de Teledetección</h2>
                    <div class="grid md:grid-cols-2 gap-6 mb-6">
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">🗺️ Visualización en Mapa</h3>
                            <p class="text-gray-700">Capas NDVI superpuestas sobre tus parcelas en el mapa interactivo.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">📈 Gráficos Históricos</h3>
                            <p class="text-gray-700">Evolución de índices en los últimos 90 días con tendencias.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">⚖️ Comparación de Parcelas</h3>
                            <p class="text-gray-700">Compara dos parcelas lado a lado con todos los índices.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">🍇 Predicción Cosecha</h3>
                            <p class="text-gray-700">GDD (Grados Día Acumulados) para estimar fecha de <a href="{{ url('/gestion-vendimia') }}" class="text-[var(--color-agro-green)] hover:underline">vendimia</a>.</p>
                        </div>
                    </div>
                </section>

                <section class="mb-12">
                    <div class="bg-gradient-to-r from-[var(--color-agro-green-bg)] to-[var(--color-agro-green-light)]/30 p-8 rounded-xl border border-[var(--color-agro-green)]/20">
                        <h3 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mb-4">🛰️ Teledetección Gratuita con Agro365</h3>
                        <p class="text-gray-700 mb-6">
                            Monitoriza tus viñedos desde el espacio. NDVI, NDWI, temperatura y humedad. Sin coste adicional. <strong>6 meses gratis</strong>.
                        </p>
                        <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-lg bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:shadow-lg transition-all font-semibold">
                            Comenzar Gratis
                        </a>
                    </div>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">Preguntas Frecuentes</h2>
                    <div class="space-y-6">
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">¿Necesito instalar algún sensor?</h3>
                            <p class="text-gray-700">No, los datos se obtienen de satélites NASA y Sentinel-2. No necesitas hardware adicional.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">¿Con qué frecuencia se actualizan los datos?</h3>
                            <p class="text-gray-700">Los satélites pasan cada 5-10 días, pero la frecuencia efectiva depende de la nubosidad.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">¿Qué resolución tienen las imágenes?</h3>
                            <p class="text-gray-700">Sentinel-2 ofrece 10m de resolución, suficiente para detectar variabilidad dentro de parcelas.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">¿Tiene coste adicional la teledetección?</h3>
                            <p class="text-gray-700">No, está incluida en tu suscripción sin coste adicional.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">¿Puedo ver el histórico de NDVI?</h3>
                            <p class="text-gray-700">Sí, guardamos los últimos 90 días de datos con gráficos de evolución temporal.</p>
                        </div>
                    </div>
                </section>
            </article>
        </div>
    </div>
    @include('partials.footer-seo')

    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": [
            {"@type": "Question", "name": "¿Necesito instalar algún sensor?", "acceptedAnswer": {"@type": "Answer", "text": "No, los datos se obtienen de satélites NASA y Sentinel-2. No necesitas hardware adicional."}},
            {"@type": "Question", "name": "¿Con qué frecuencia se actualizan los datos?", "acceptedAnswer": {"@type": "Answer", "text": "Los satélites pasan cada 5-10 días, pero la frecuencia efectiva depende de la nubosidad."}},
            {"@type": "Question", "name": "¿Qué resolución tienen las imágenes?", "acceptedAnswer": {"@type": "Answer", "text": "Sentinel-2 ofrece 10m de resolución, suficiente para detectar variabilidad dentro de parcelas."}},
            {"@type": "Question", "name": "¿Tiene coste adicional la teledetección?", "acceptedAnswer": {"@type": "Answer", "text": "No, está incluida en tu suscripción sin coste adicional."}},
            {"@type": "Question", "name": "¿Puedo ver el histórico de NDVI?", "acceptedAnswer": {"@type": "Answer", "text": "Sí, guardamos los últimos 90 días de datos con gráficos de evolución temporal."}}
        ]
    }
    </script>
</body>
</html>
