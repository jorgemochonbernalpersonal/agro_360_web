<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- SEO Meta Tags -->
    <title>¿Qué es el SIGPAC? | Cómo Consultar Parcelas y Códigos 2027</title>
    <meta name="description" content="Guía completa sobre el SIGPAC. Aprende a consultar recintos, parcelas y códigos oficiales. Integración con el cuaderno de campo digital Agro365.">
    <meta name="keywords" content="qué es SIGPAC, SIGPAC digital, gestión SIGPAC, códigos SIGPAC, parcelas SIGPAC, SIGPAC viñedos, sistema SIGPAC, SIGPAC España, gestión parcelas agrícolas">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <meta name="author" content="Agro365">
    <meta name="language" content="Spanish">
    <meta name="geo.region" content="ES">
    <meta name="revisit-after" content="7 days">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="{{ url('/que-es-sigpac') }}">
    
    <!-- Open Graph -->
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ url('/que-es-sigpac') }}">
    <meta property="og:title" content="Qué es SIGPAC y Cómo Funciona - Guía Completa 2027">
    <meta property="og:description" content="Descubre todo sobre SIGPAC, el sistema oficial de gestión de parcelas agrícolas. Cómo funciona y cómo integrarlo con tu software agrícola.">
    <meta property="og:image" content="{{ asset('images/logo.png') }}">
    <meta property="og:locale" content="es_ES">
    <meta property="og:site_name" content="Agro365">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="{{ url('/que-es-sigpac') }}">
    <meta name="twitter:title" content="Qué es SIGPAC y Cómo Funciona - Guía Completa">
    <meta name="twitter:description" content="Guía completa sobre SIGPAC para viticultores. Cómo gestionar parcelas con códigos SIGPAC.">
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
            <nav class="mb-8 text-sm text-gray-600" itemscope itemtype="https://schema.org/BreadcrumbList">
                <ol class="flex items-center space-x-2">
                    <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                        <a href="{{ url('/') }}" class="hover:text-[var(--color-agro-green)]" itemprop="item">
                            <span itemprop="name">Inicio</span>
                        </a>
                        <meta itemprop="position" content="1" />
                    </li>
                    <span class="mx-2">/</span>
                    <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                        <span class="text-gray-900" itemprop="name">Qué es SIGPAC</span>
                        <meta itemprop="position" content="2" />
                    </li>
                </ol>
            </nav>

            <!-- Header -->
            <div class="mb-12">
                <h1 class="text-5xl lg:text-6xl font-bold text-[var(--color-agro-green-dark)] mb-6">
                    ¿Qué es SIGPAC y Cómo Funciona?
                </h1>
                <p class="text-xl text-gray-600 leading-relaxed">
                    Guía completa sobre el <strong>Sistema de Información Geográfica de Parcelas Agrícolas (SIGPAC)</strong> para viticultores y agricultores en España. Descubre cómo gestionar tus parcelas con códigos SIGPAC y cumplir con la normativa PAC.
                </p>
            </div>

            <!-- Content -->
            <article class="prose prose-lg max-w-none">
                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">¿Qué es SIGPAC?</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        <strong>SIGPAC</strong> (Sistema de Información Geográfica de Parcelas Agrícolas) es el sistema oficial del Ministerio de Agricultura, Pesca y Alimentación de España que gestiona la información geográfica de todas las parcelas agrícolas del país.
                    </p>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        Cada parcela agrícola tiene un <strong>código SIGPAC único</strong> que la identifica de forma oficial. Este código es obligatorio para todas las solicitudes de ayudas PAC (Política Agraria Común) y para el cumplimiento normativo agrícola.
                    </p>
                    <div class="bg-[var(--color-agro-green-bg)] border-l-4 border-[var(--color-agro-green)] p-6 rounded-r-lg mb-6">
                        <p class="text-gray-800 font-semibold mb-2">💡 Importante:</p>
                        <p class="text-gray-700">
                            Desde 2027, el cuaderno de campo digital debe estar asociado a códigos SIGPAC. Sin la integración SIGPAC, no podrás cumplir con la normativa europea.
                        </p>
                    </div>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">¿Cómo Funciona SIGPAC?</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        SIGPAC funciona mediante un sistema de códigos alfanuméricos que identifican cada recinto agrícola. Cada código incluye:
                    </p>
                    <ul class="list-disc list-inside space-y-2 text-gray-700 mb-6 ml-4">
                        <li><strong>Código de municipio:</strong> Identifica el municipio donde se encuentra la parcela</li>
                        <li><strong>Código de polígono:</strong> Identifica el polígono dentro del municipio</li>
                        <li><strong>Código de parcela:</strong> Identifica la parcela específica dentro del polígono</li>
                        <li><strong>Código de recinto:</strong> Identifica el recinto específico dentro de la parcela</li>
                    </ul>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        Ejemplo de código SIGPAC: <code class="bg-gray-100 px-2 py-1 rounded">ES123456789012</code>
                    </p>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">¿Por Qué es Importante SIGPAC para Viticultores?</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        Para viticultores profesionales, SIGPAC es esencial porque:
                    </p>
                    <div class="grid md:grid-cols-2 gap-6 mb-6">
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">📋 Cumplimiento Normativo</h3>
                            <p class="text-gray-700">Es obligatorio para solicitar ayudas PAC y cumplir con la normativa europea de trazabilidad.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">🗺️ Gestión Precisa</h3>
                            <p class="text-gray-700">Permite gestionar cada viñedo con precisión, asociando actividades a parcelas específicas.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">📊 Informes Oficiales</h3>
                            <p class="text-gray-700">Los informes oficiales deben incluir códigos SIGPAC para ser válidos en inspecciones.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">🔍 Trazabilidad</h3>
                            <p class="text-gray-700">Garantiza la trazabilidad completa desde la parcela hasta la cosecha y facturación.</p>
                        </div>
                    </div>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">Cómo Gestionar SIGPAC con Agro365</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        <strong>Agro365</strong> integra SIGPAC completamente. Si eres <a href="{{ content_route('content.software-viticultores') }}" class="text-[var(--color-agro-green)] hover:underline">viticultor profesional</a>, nuestra <a href="{{ content_route('content.app-agricultura') }}" class="text-[var(--color-agro-green)] hover:underline">app de agricultura</a> te permite:
                    </p>
                    <ul class="list-disc list-inside space-y-3 text-gray-700 mb-6 ml-4">
                        <li><strong>Importar códigos SIGPAC:</strong> Añade tus códigos SIGPAC directamente desde el sistema oficial</li>
                        <li><strong>Visualizar parcelas en mapa:</strong> Ve tus viñedos en un mapa interactivo con geometrías GeoJSON</li>
                        <li><strong>Gestión multiparcela:</strong> Gestiona múltiples recintos dentro de una misma parcela SIGPAC</li>
                        <li><strong>Asociación automática:</strong> Todas tus actividades se asocian automáticamente al código SIGPAC correcto</li>
                        <li><strong>Informes con SIGPAC:</strong> Los informes oficiales incluyen automáticamente los códigos SIGPAC</li>
                        <li><strong>Cumplimiento PAC:</strong> El dashboard de cumplimiento valida automáticamente tus datos SIGPAC</li>
                    </ul>
                    <div class="bg-gradient-to-r from-[var(--color-agro-green-bg)] to-[var(--color-agro-green-light)]/30 p-8 rounded-xl border border-[var(--color-agro-green)]/20">
                        <h3 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mb-4">¿Listo para Digitalizar tu Gestión SIGPAC?</h3>
                        <p class="text-gray-700 mb-6">
                            Comienza a gestionar tus parcelas SIGPAC de forma profesional con Agro365. <strong>3 meses gratis</strong> para beta testers.
                        </p>
                        @guest
                            <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-lg bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:shadow-lg transition-all font-semibold">
                                Comenzar Gratis
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                </svg>
                            </a>
                        @else
                            <a href="{{ route(auth()->user()->role . '.dashboard') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-lg bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:shadow-lg transition-all font-semibold">
                                Ir al Dashboard
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                </svg>
                            </a>
                        @endguest
                    </div>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">Preguntas Frecuentes sobre SIGPAC</h2>
                    <div class="space-y-6">
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">¿Dónde encuentro mi código SIGPAC?</h3>
                            <p class="text-gray-700">Puedes consultar tus códigos SIGPAC en el <a href="https://sigpac.mapama.gob.es/fega/visor/" target="_blank" rel="noopener" class="text-[var(--color-agro-green)] hover:underline">visor oficial de SIGPAC</a> del Ministerio de Agricultura.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">¿Puedo tener múltiples recintos en una parcela?</h3>
                            <p class="text-gray-700">Sí, una parcela SIGPAC puede tener múltiples recintos. Agro365 permite gestionar cada recinto de forma independiente.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">¿Es obligatorio usar SIGPAC?</h3>
                            <p class="text-gray-700">Sí, desde 2027 es obligatorio asociar el cuaderno de campo digital a códigos SIGPAC para cumplir con la normativa europea.</p>
                        </div>
                    </div>
                </section>
            </article>

            <!-- CTA Section -->
            <div class="mt-16 pt-12 border-t border-gray-200 text-center">
                <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">
                    Gestiona tus Parcelas SIGPAC con Agro365
                </h2>
                <p class="text-gray-600 mb-8 text-lg">
                    Software profesional de gestión agrícola con integración completa SIGPAC. Prueba gratis 3 meses.
                </p>
                @guest
                    <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-8 py-4 rounded-xl bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:from-[var(--color-agro-green)] hover:to-[var(--color-agro-green-dark)] transition-all duration-300 shadow-lg hover:shadow-xl font-semibold text-lg">
                        Comenzar Gratis
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                @else
                    <a href="{{ route(auth()->user()->role . '.dashboard') }}" class="inline-flex items-center gap-2 px-8 py-4 rounded-xl bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:from-[var(--color-agro-green)] hover:to-[var(--color-agro-green-dark)] transition-all duration-300 shadow-lg hover:shadow-xl font-semibold text-lg">
                        Ir al Dashboard
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                @endguest
            </div>
        </div>
    </div>

    <!-- ✅ SEO: Enlaces relacionados para mejorar link juice interno -->
    @include('components.related-links')

    <!-- Footer -->
    @include('partials.footer-seo')

    <!-- Breadcrumb Schema -->
    <script type="application/ld+json">
    {!! \App\Helpers\SeoHelper::breadcrumbSchema([
        ['name' => 'Inicio', 'url' => url('/')],
        ['name' => 'Qué es SIGPAC', 'url' => url('/que-es-sigpac')]
    ]) !!}
    </script>

    <!-- Article Schema -->
    <script type="application/ld+json">
    @php
        $articleSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => 'Qué es SIGPAC y Cómo Funciona - Guía Completa 2027',
            'description' => 'Guía completa sobre SIGPAC: qué es, cómo funciona y cómo gestionar parcelas agrícolas con códigos SIGPAC.',
            'author' => [
                '@type' => 'Organization',
                'name' => 'Agro365'
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => 'Agro365',
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => asset('images/logo.png')
                ]
            ],
            'datePublished' => '2024-01-01',
            'dateModified' => now()->toIso8601String(),
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => url('/que-es-sigpac')
            ]
        ];
    @endphp
    {!! json_encode($articleSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
    </script>
</body>
</html>
