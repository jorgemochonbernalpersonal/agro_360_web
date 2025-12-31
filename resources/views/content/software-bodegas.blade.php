<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- SEO Meta Tags -->
    <title>Software para Bodegas | Gestión Profesional de Bodegas - Agro365</title>
    <meta name="description" content="Software profesional para bodegas en España. Gestión de producción, trazabilidad, control de vendimia, registro de movimientos y cumplimiento normativo. Prueba gratis 6 meses.">
    <meta name="keywords" content="software bodegas, software bodega, gestión bodegas, software producción vino, gestión bodega, software enología, software bodegas España">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <meta name="author" content="Agro365">
    <meta name="language" content="Spanish">
    <meta name="geo.region" content="ES">
    <meta name="revisit-after" content="7 days">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="{{ url('/software-bodegas') }}">
    
    <!-- Open Graph -->
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ url('/software-bodegas') }}">
    <meta property="og:title" content="Software para Bodegas - Gestión Profesional">
    <meta property="og:description" content="Software profesional para bodegas con trazabilidad, control de vendimia y gestión de producción. Prueba gratis 6 meses.">
    <meta property="og:image" content="{{ asset('images/logo.png') }}">
    <meta property="og:locale" content="es_ES">
    <meta property="og:site_name" content="Agro365">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="{{ url('/software-bodegas') }}">
    <meta name="twitter:title" content="Software para Bodegas - Agro365">
    <meta name="twitter:description" content="Software profesional para bodegas. Prueba gratis 6 meses.">
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
                        <span class="text-gray-900" itemprop="name">Software para Bodegas</span>
                        <meta itemprop="position" content="2" />
                    </li>
                </ol>
            </nav>

            <!-- Header -->
            <div class="mb-12">
                <h1 class="text-5xl lg:text-6xl font-bold text-[var(--color-agro-green-dark)] mb-6">
                    Software para Bodegas Profesionales
                </h1>
                <p class="text-xl text-gray-600 leading-relaxed">
                    <strong>Software especializado para bodegas</strong> en España. Gestión de producción, control de vendimia, trazabilidad completa, registro de movimientos y cumplimiento normativo. Diseñado específicamente para bodegas y enólogos.
                </p>
            </div>

            <!-- Content -->
            <article class="prose prose-lg max-w-none">
                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">Software Especializado para Bodegas</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        Las <strong>bodegas</strong> requieren herramientas especializadas para gestionar la producción, controlar la vendimia, registrar movimientos, mantener la trazabilidad y cumplir con todas las normativas. Un <strong>software para bodegas</strong> debe integrar todas estas funcionalidades en una sola plataforma.
                    </p>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        <strong>Agro365</strong> es el software de gestión diseñado específicamente para bodegas, con todas las funcionalidades necesarias para gestionar tu producción de forma profesional y cumplir con las normativas vigentes.
                    </p>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">Funcionalidades del Software para Bodegas</h2>
                    <div class="grid md:grid-cols-2 gap-6 mb-6">
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">🍷 Control de Vendimia</h3>
                            <p class="text-gray-700">Registra recepciones de uva, control de calidad, pesajes y trazabilidad desde la parcela.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">📦 Gestión de Producción</h3>
                            <p class="text-gray-700">Control de procesos de vinificación, fermentación, crianza y embotellado.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">🔍 Trazabilidad Completa</h3>
                            <p class="text-gray-700">Trazabilidad desde la parcela hasta la botella. Registro completo de movimientos y transformaciones.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">📊 Registro de Movimientos</h3>
                            <p class="text-gray-700">Registra entradas, salidas, transformaciones y ventas de productos.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">📄 Informes Oficiales</h3>
                            <p class="text-gray-700">Genera informes oficiales con firma electrónica para inspecciones y cumplimiento normativo.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">💰 Facturación</h3>
                            <p class="text-gray-700">Facturación integrada de productos, control de precios y gestión de clientes.</p>
                        </div>
                    </div>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">Agro365: Software Completo para Bodegas</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        <strong>Agro365</strong> es el <strong>software para bodegas</strong> diseñado específicamente para bodegas profesionales en España. Nuestra plataforma integra todas las herramientas necesarias para gestionar tu producción de forma profesional.
                    </p>
                    <div class="bg-gradient-to-r from-[var(--color-agro-green-bg)] to-[var(--color-agro-green-light)]/30 p-8 rounded-xl border border-[var(--color-agro-green)]/20 mb-6">
                        <h3 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mb-4">Ventajas de Agro365 para Bodegas</h3>
                        <ul class="space-y-3 text-gray-800">
                            <li class="flex items-start gap-3">
                                <svg class="w-6 h-6 text-[var(--color-agro-green)] flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span><strong>Control de vendimia:</strong> Registra recepciones de uva con trazabilidad desde la parcela</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="w-6 h-6 text-[var(--color-agro-green)] flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span><strong>Trazabilidad completa:</strong> Desde la parcela hasta la botella</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="w-6 h-6 text-[var(--color-agro-green)] flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span><strong>Gestión de producción:</strong> Control de procesos de vinificación y crianza</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="w-6 h-6 text-[var(--color-agro-green)] flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span><strong>Registro de movimientos:</strong> Entradas, salidas, transformaciones y ventas</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="w-6 h-6 text-[var(--color-agro-green)] flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span><strong>Cumplimiento normativo:</strong> Cumple con todas las normativas de seguridad alimentaria</span>
                            </li>
                        </ul>
                    </div>
                </section>
            </article>

            <!-- CTA Section -->
            <div class="mt-16 pt-12 border-t border-gray-200 text-center">
                <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">
                    Software para Bodegas Profesional
                </h2>
                <p class="text-gray-600 mb-8 text-lg">
                    Gestiona tu bodega con Agro365. Software especializado para bodegas con trazabilidad, control de vendimia y gestión de producción. Prueba gratis 6 meses.
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
        ['name' => 'Software para Bodegas', 'url' => url('/software-bodegas')]
    ]) !!}
    </script>
</body>
</html>

