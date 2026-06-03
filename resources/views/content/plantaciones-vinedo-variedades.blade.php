<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Plantaciones de Viñedo: Variedades de Uva y Gestión | Agro365</title>
    <meta name="description" content="Gestión de plantaciones de viñedo: variedades de uva, sistemas de conducción, certificaciones y datos agronómicos. Software profesional para viticultores.">
    <meta name="keywords" content="plantaciones viñedo, variedades uva, tempranillo, garnacha, sistemas conducción vid, espaldera, vaso, gestión viñedo, datos agronómicos vid, certificación plantaciones">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url('/plantaciones-vinedo-variedades') }}">
    <meta property="og:title" content="Plantaciones de Viñedo - Gestión de Variedades">
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
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-purple-100 border border-purple-300 mb-6">
                    <span class="text-lg">🍇</span>
                    <span class="text-sm font-semibold text-purple-800">{{ __('Variedades y Plantaciones') }}</span>
                </div>
                <h1 class="text-5xl lg:text-6xl font-bold text-[var(--color-agro-green-dark)] mb-6">{{ __('Gestión de Plantaciones y Variedades de Viñedo') }}</h1>
                <p class="text-xl text-gray-600 leading-relaxed">
                    <strong>{{ __('Registra cada plantación') }}</strong> con su variedad, año de plantación, sistema de conducción, marco de plantación y datos agronómicos completos.
                </p>
            </div>

            <article class="prose prose-lg max-w-none">
                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">{{ __('Datos de Plantación') }}</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">{{ __('Cada parcela puede tener múltiples plantaciones. Registra:') }}</p>
                    <div class="grid md:grid-cols-2 gap-4 mb-6">
                        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-[var(--color-agro-green-dark)] mb-2">{{ __('🍇 Variedad') }}</h3>
                            <ul class="text-gray-700 text-sm space-y-1">
                                <li>{{ __('• Tempranillo, Garnacha, Viura...') }}</li>
                                <li>{{ __('• Clon y portainjerto') }}</li>
                                <li>{{ __('• Año de plantación') }}</li>
                            </ul>
                        </div>
                        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-[var(--color-agro-green-dark)] mb-2">{{ __('📐 Marco de Plantación') }}</h3>
                            <ul class="text-gray-700 text-sm space-y-1">
                                <li>{{ __('• Distancia entre cepas') }}</li>
                                <li>{{ __('• Distancia entre filas') }}</li>
                                <li>{{ __('• Densidad (cepas/ha)') }}</li>
                            </ul>
                        </div>
                        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-[var(--color-agro-green-dark)] mb-2">{{ __('🌱 Sistema de Conducción') }}</h3>
                            <ul class="text-gray-700 text-sm space-y-1">
                                <li>{{ __('• Espaldera, Vaso, Parral') }}</li>
                                <li>{{ __('• Tipo de poda') }}</li>
                                <li>{{ __('• Orientación de filas') }}</li>
                            </ul>
                        </div>
                        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-[var(--color-agro-green-dark)] mb-2">{{ __('📋 Certificaciones') }}</h3>
                            <ul class="text-gray-700 text-sm space-y-1">
                                <li>{{ __('• DO, DOCa, IGP') }}</li>
                                <li>{{ __('• Ecológico, Biodinámica') }}</li>
                                <li>{{ __('• Certificados origen') }}</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">{{ __('Variedades más Comunes en España') }}</h2>
                    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 mb-6">
                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="font-bold text-red-600 mb-2">{{ __('🔴 Tintas') }}</h3>
                                <ul class="text-gray-700 text-sm space-y-1">
                                    <li>{{ __('• Tempranillo (Tinta del País)') }}</li>
                                    <li>{{ __('• Garnacha Tinta') }}</li>
                                    <li>{{ __('• Bobal') }}</li>
                                    <li>{{ __('• Monastrell') }}</li>
                                    <li>{{ __('• Mencía') }}</li>
                                    <li>{{ __('• Cabernet Sauvignon') }}</li>
                                </ul>
                            </div>
                            <div>
                                <h3 class="font-bold text-amber-600 mb-2">{{ __('⚪ Blancas') }}</h3>
                                <ul class="text-gray-700 text-sm space-y-1">
                                    <li>{{ __('• Airén') }}</li>
                                    <li>{{ __('• Viura (Macabeo)') }}</li>
                                    <li>{{ __('• Verdejo') }}</li>
                                    <li>{{ __('• Albariño') }}</li>
                                    <li>{{ __('• Palomino') }}</li>
                                    <li>{{ __('• Chardonnay') }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">{{ __('Integración con SIGPAC') }}</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        Cada plantación se vincula a un recinto <a href="{{ content_route('content.que-es-sigpac') }}" class="text-[var(--color-agro-green)] hover:underline">SIGPAC</a>. Los datos de plantación son fundamentales para:
                    </p>
                    <ul class="list-disc list-inside space-y-2 text-gray-700 mb-6 ml-4">
                        <li>Solicitudes de <a href="{{ url('/subvenciones-pac-2024') }}" class="text-[var(--color-agro-green)] hover:underline">ayudas PAC</a></li>
                        <li>{{ __('Certificación de Denominación de Origen') }}</li>
                        <li>Cálculo de <a href="{{ url('/rendimientos-cosecha-vinedo') }}" class="text-[var(--color-agro-green)] hover:underline">rendimientos esperados</a></li>
                        <li>Trazabilidad de <a href="{{ url('/gestion-vendimia') }}" class="text-[var(--color-agro-green)] hover:underline">vendimia</a></li>
                    </ul>
                </section>

                <section class="mb-12">
                    <div class="bg-gradient-to-r from-[var(--color-agro-green-bg)] to-[var(--color-agro-green-light)]/30 p-8 rounded-xl border border-[var(--color-agro-green)]/20">
                        <h3 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mb-4">{{ __('🍇 Gestiona tus Plantaciones') }}</h3>
                        <p class="text-gray-700 mb-6">
                            Registra variedades, sistemas de conducción y certificaciones. <strong>{{ __('3 meses gratis') }}</strong>.
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
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">{{ __('¿Puedo tener varias variedades en la misma parcela?') }}</h3>
                            <p class="text-gray-700">{{ __('Sí, cada parcela puede tener múltiples plantaciones con diferentes variedades, sistemas de conducción y años de plantación.') }}</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">{{ __('¿Qué es el marco de plantación?') }}</h3>
                            <p class="text-gray-700">{{ __('Es la distancia entre cepas y entre filas que determina la densidad de plantación (cepas por hectárea).') }}</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">{{ __('¿Cómo registro el sistema de conducción?') }}</h3>
                            <p class="text-gray-700">{{ __('Selecciona entre espaldera, vaso, parral u otro, indicando además el tipo de poda y la orientación de las filas.') }}</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">{{ __('¿Puedo indicar la DO de cada plantación?') }}</h3>
                            <p class="text-gray-700">{{ __('Sí, cada plantación puede tener asociada su certificación de DO, DOCa, IGP o cultivo ecológico.') }}</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">{{ __('¿Se vincula con el código SIGPAC?') }}</h3>
                            <p class="text-gray-700">{{ __('Sí, cada plantación está vinculada a un recinto SIGPAC para trazabilidad completa y cumplimiento PAC.') }}</p>
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
            {"@@type": "Question", "name": "¿Puedo tener varias variedades en la misma parcela?", "acceptedAnswer": {"@@type": "Answer", "text": "Sí, cada parcela puede tener múltiples plantaciones con diferentes variedades, sistemas de conducción y años de plantación."}},
            {"@@type": "Question", "name": "¿Qué es el marco de plantación?", "acceptedAnswer": {"@@type": "Answer", "text": "Es la distancia entre cepas y entre filas que determina la densidad de plantación (cepas por hectárea)."}},
            {"@@type": "Question", "name": "¿Cómo registro el sistema de conducción?", "acceptedAnswer": {"@@type": "Answer", "text": "Selecciona entre espaldera, vaso, parral u otro, indicando además el tipo de poda y la orientación de las filas."}},
            {"@@type": "Question", "name": "¿Puedo indicar la DO de cada plantación?", "acceptedAnswer": {"@@type": "Answer", "text": "Sí, cada plantación puede tener asociada su certificación de DO, DOCa, IGP o cultivo ecológico."}},
            {"@@type": "Question", "name": "¿Se vincula con el código SIGPAC?", "acceptedAnswer": {"@@type": "Answer", "text": "Sí, cada plantación está vinculada a un recinto SIGPAC para trazabilidad completa y cumplimiento PAC."}}
        ]
    }
    </script>
</body>
</html>
