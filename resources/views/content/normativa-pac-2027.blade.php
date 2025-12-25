<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- SEO Meta Tags -->
    <title>Normativa PAC 2027 | Cambios y Cumplimiento - Guía Completa - Agro365</title>
    <meta name="description" content="Normativa PAC 2027: cambios, requisitos y cumplimiento. Cómo Agro365 te ayuda a cumplir con todas las normativas PAC automáticamente.">
    <meta name="keywords" content="normativa PAC 2027, cumplimiento PAC, requisitos PAC, ayudas PAC, política agraria común, normativa agrícola 2027, cumplimiento normativo PAC">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <meta name="author" content="Agro365">
    <meta name="language" content="Spanish">
    <meta name="geo.region" content="ES">
    <meta name="revisit-after" content="7 days">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="{{ url('/normativa-pac-2027') }}">
    
    <!-- Open Graph -->
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ url('/normativa-pac-2027') }}">
    <meta property="og:title" content="Normativa PAC 2027 - Cambios y Cumplimiento">
    <meta property="og:description" content="Guía completa sobre la normativa PAC 2027. Cambios, requisitos y cómo cumplir automáticamente con Agro365.">
    <meta property="og:image" content="{{ asset('images/logo.png') }}">
    <meta property="og:locale" content="es_ES">
    <meta property="og:site_name" content="Agro365">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="{{ url('/normativa-pac-2027') }}">
    <meta name="twitter:title" content="Normativa PAC 2027 - Cambios y Cumplimiento">
    <meta name="twitter:description" content="Guía completa sobre la normativa PAC 2027 y cómo cumplir automáticamente.">
    <meta name="twitter:image" content="{{ asset('images/logo.png') }}">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link rel="dns-prefetch" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&display=swap" rel="stylesheet" />
    
    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-white">
    <!-- Header/Navbar -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center gap-3">
                    <a href="{{ url('/') }}" class="flex items-center gap-3">
                        <img src="{{ asset('images/logo.png') }}" alt="Agro365" width="120" height="40" loading="eager" fetchpriority="high" decoding="async" class="h-10 w-auto">
                        <span class="text-xl font-bold text-[var(--color-agro-green-dark)]">Agro365</span>
                    </a>
                    <span class="px-2 py-1 text-xs font-bold rounded-full bg-blue-100 text-blue-700 border border-blue-300">BETA</span>
                </div>
                <div class="flex items-center gap-4">
                    <a href="{{ url('/') }}" class="text-gray-600 hover:text-[var(--color-agro-green)] transition-colors">Inicio</a>
                    <a href="{{ route('faqs') }}" class="text-gray-600 hover:text-[var(--color-agro-green)] transition-colors">FAQs</a>
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}" class="text-gray-600 hover:text-[var(--color-agro-green)] transition-colors">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="text-gray-600 hover:text-[var(--color-agro-green)] transition-colors">Entrar</a>
                            <a href="{{ route('register') }}" class="px-4 py-2 rounded-lg bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:shadow-lg transition-all">
                                Comenzar Gratis
                            </a>
                        @endauth
                    @endif
                </div>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <div class="min-h-screen bg-gradient-to-b from-white to-gray-50 py-20">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Breadcrumb -->
            <nav class="mb-8 text-sm text-gray-600">
                <a href="{{ url('/') }}" class="hover:text-[var(--color-agro-green)]">Inicio</a>
                <span class="mx-2">/</span>
                <span class="text-gray-900">Normativa PAC 2027</span>
            </nav>

            <!-- Header -->
            <div class="mb-12">
                <h1 class="text-5xl lg:text-6xl font-bold text-[var(--color-agro-green-dark)] mb-6">
                    Normativa PAC 2027: Cambios y Cumplimiento
                </h1>
                <p class="text-xl text-gray-600 leading-relaxed">
                    Guía completa sobre la <strong>Política Agraria Común (PAC) 2027</strong>. Descubre los cambios normativos, requisitos de cumplimiento y cómo Agro365 te ayuda a cumplir automáticamente con todas las normativas PAC.
                </p>
            </div>

            <!-- Content -->
            <article class="prose prose-lg max-w-none">
                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">¿Qué es la PAC?</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        La <strong>Política Agraria Común (PAC)</strong> es el sistema de ayudas y normativas de la Unión Europea para el sector agrícola. La PAC establece requisitos obligatorios que todos los agricultores y viticultores deben cumplir para recibir ayudas y mantener sus explotaciones en regla.
                    </p>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        Desde 2027, la PAC introduce cambios significativos que afectan especialmente a:
                    </p>
                    <ul class="list-disc list-inside space-y-2 text-gray-700 mb-6 ml-4">
                        <li>Cuaderno de campo digital obligatorio</li>
                        <li>Integración obligatoria con códigos SIGPAC</li>
                        <li>Informes oficiales con firma electrónica</li>
                        <li>Trazabilidad completa de actividades</li>
                        <li>Cumplimiento ambiental</li>
                    </ul>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">Cambios en la Normativa PAC 2027</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        Los principales cambios que entran en vigor en 2027 incluyen:
                    </p>
                    <div class="space-y-6 mb-6">
                        <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-[var(--color-agro-green)]">
                            <h3 class="font-bold text-xl text-[var(--color-agro-green-dark)] mb-2">1. Cuaderno de Campo Digital Obligatorio</h3>
                            <p class="text-gray-700">El cuaderno de campo en papel ya no será válido. Todas las explotaciones deben tener un cuaderno digital que cumpla con requisitos específicos.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-[var(--color-agro-green)]">
                            <h3 class="font-bold text-xl text-[var(--color-agro-green-dark)] mb-2">2. Integración SIGPAC Obligatoria</h3>
                            <p class="text-gray-700">Todas las actividades deben estar asociadas a códigos SIGPAC válidos. Sin SIGPAC, no podrás cumplir con la normativa.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-[var(--color-agro-green)]">
                            <h3 class="font-bold text-xl text-[var(--color-agro-green-dark)] mb-2">3. Informes Oficiales con Firma Electrónica</h3>
                            <p class="text-gray-700">Los informes oficiales deben incluir firma electrónica SHA-256 y código QR de verificación para ser válidos en inspecciones.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-[var(--color-agro-green)]">
                            <h3 class="font-bold text-xl text-[var(--color-agro-green-dark)] mb-2">4. Trazabilidad Completa</h3>
                            <p class="text-gray-700">Debe existir trazabilidad completa desde la parcela hasta la cosecha, incluyendo todos los tratamientos y actividades realizadas.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-[var(--color-agro-green)]">
                            <h3 class="font-bold text-xl text-[var(--color-agro-green-dark)] mb-2">5. Validación Automática</h3>
                            <p class="text-gray-700">Los sistemas deben validar automáticamente el cumplimiento de requisitos y detectar errores antes de las inspecciones.</p>
                        </div>
                    </div>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">Requisitos de Cumplimiento PAC</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        Para cumplir con la normativa PAC 2027, tu explotación debe cumplir estos requisitos:
                    </p>
                    <div class="grid md:grid-cols-2 gap-6 mb-6">
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">📋 Registro Completo</h3>
                            <p class="text-gray-700">Todas las actividades agrícolas deben estar registradas: tratamientos, riegos, fertilizaciones y labores.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">🗺️ Códigos SIGPAC</h3>
                            <p class="text-gray-700">Cada actividad debe estar asociada a un código SIGPAC válido y actualizado.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">🔒 Firma Electrónica</h3>
                            <p class="text-gray-700">Los informes oficiales deben incluir firma electrónica SHA-256 inmutable.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">✅ Validación</h3>
                            <p class="text-gray-700">El sistema debe validar automáticamente el cumplimiento y detectar errores.</p>
                        </div>
                    </div>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">¿Qué Pasa si No Cumplo con la Normativa PAC?</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        El incumplimiento de la normativa PAC puede resultar en:
                    </p>
                    <div class="bg-red-50 border-l-4 border-red-500 p-6 rounded-r-lg mb-6">
                        <ul class="list-disc list-inside space-y-2 text-red-800 ml-4">
                            <li><strong>Pérdida de ayudas PAC:</strong> Puedes perder el derecho a recibir ayudas de la PAC</li>
                            <li><strong>Multas económicas:</strong> Sanciones por incumplimiento normativo</li>
                            <li><strong>Problemas en inspecciones:</strong> Las inspecciones pueden resultar en sanciones adicionales</li>
                            <li><strong>Exclusión de programas:</strong> Puedes quedar excluido de programas de ayudas futuros</li>
                        </ul>
                    </div>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">Cómo Agro365 Te Ayuda a Cumplir con la PAC 2027</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        <strong>Agro365</strong> está diseñado específicamente para cumplir automáticamente con todos los requisitos de la normativa PAC 2027:
                    </p>
                    <div class="bg-gradient-to-r from-[var(--color-agro-green-bg)] to-[var(--color-agro-green-light)]/30 p-8 rounded-xl border border-[var(--color-agro-green)]/20 mb-6">
                        <h3 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mb-4">Dashboard de Cumplimiento PAC en Tiempo Real</h3>
                        <p class="text-gray-700 mb-4">
                            Agro365 incluye un <strong>dashboard de cumplimiento PAC</strong> que:
                        </p>
                        <ul class="space-y-3 text-gray-800 mb-6">
                            <li class="flex items-start gap-3">
                                <svg class="w-6 h-6 text-[var(--color-agro-green)] flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span><strong>Detecta errores automáticamente:</strong> Identifica problemas de cumplimiento antes de las inspecciones</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="w-6 h-6 text-[var(--color-agro-green)] flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span><strong>Valida datos SIGPAC:</strong> Verifica que todos los códigos SIGPAC sean válidos y estén actualizados</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="w-6 h-6 text-[var(--color-agro-green)] flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span><strong>Genera informes oficiales:</strong> Crea informes certificados con firma SHA-256 en segundos</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="w-6 h-6 text-[var(--color-agro-green)] flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span><strong>Prepara para inspecciones:</strong> Te muestra exactamente qué documentos necesitas para cada inspección</span>
                            </li>
                        </ul>
                    </div>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">Preguntas Frecuentes sobre Normativa PAC</h2>
                    <div class="space-y-6">
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">¿Cuándo entran en vigor los cambios de 2027?</h3>
                            <p class="text-gray-700">Los cambios de la normativa PAC 2027 entran en vigor el 1 de enero de 2027. Es recomendable prepararse con antelación.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">¿Qué pasa si ya tengo un cuaderno digital pero no cumple todos los requisitos?</h3>
                            <p class="text-gray-700">Si tu cuaderno digital no cumple con todos los requisitos (SIGPAC, firma electrónica, etc.), puede no ser válido y resultar en sanciones. Es importante verificar que tu sistema cumple con todos los requisitos.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">¿Agro365 cumple con todos los requisitos PAC 2027?</h3>
                            <p class="text-gray-700">Sí, Agro365 está diseñado específicamente para cumplir con todos los requisitos de la normativa PAC 2027, incluyendo cuaderno digital, SIGPAC, firma electrónica y validación automática.</p>
                        </div>
                    </div>
                </section>
            </article>

            <!-- CTA Section -->
            <div class="mt-16 pt-12 border-t border-gray-200 text-center">
                <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">
                    Cumple Automáticamente con la Normativa PAC 2027
                </h2>
                <p class="text-gray-600 mb-8 text-lg">
                    Agro365 te ayuda a cumplir con todos los requisitos PAC automáticamente. Dashboard de cumplimiento, validación automática y informes oficiales. Prueba gratis 6 meses.
                </p>
                <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-8 py-4 rounded-xl bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:from-[var(--color-agro-green)] hover:to-[var(--color-agro-green-dark)] transition-all duration-300 shadow-lg hover:shadow-xl font-semibold text-lg">
                    Comenzar Gratis - 6 Meses
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    @include('partials.footer-seo')

    <!-- Breadcrumb Schema -->
    <script type="application/ld+json">
    {!! \App\Helpers\SeoHelper::breadcrumbSchema([
        ['name' => 'Inicio', 'url' => url('/')],
        ['name' => 'Normativa PAC 2027', 'url' => url('/normativa-pac-2027')]
    ]) !!}
    </script>

    <!-- Article Schema -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Article",
        "headline": "Normativa PAC 2027 - Cambios y Cumplimiento",
        "description": "Guía completa sobre la normativa PAC 2027. Cambios, requisitos y cómo cumplir automáticamente.",
        "author": {
            "@type": "Organization",
            "name": "Agro365"
        },
        "publisher": {
            "@type": "Organization",
            "name": "Agro365",
            "logo": {
                "@type": "ImageObject",
                "url": "{{ asset('images/logo.png') }}"
            }
        },
        "datePublished": "2024-01-01",
        "dateModified": "{{ now()->toIso8601String() }}",
        "mainEntityOfPage": {
            "@type": "WebPage",
            "@id": "{{ url('/normativa-pac-2027') }}"
        }
    }
    </script>
</body>
</html>

