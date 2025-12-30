<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- SEO Meta Tags -->
    <title>Cuaderno Digital para Viticultores | Obligatorio 2027 - Agro365</title>
    <meta name="description" content="Cuaderno de campo digital para viticultores obligatorio desde 2027. Gestión de tratamientos, SIGPAC, cumplimiento normativo y informes oficiales. Prueba gratis 6 meses.">
    <meta name="keywords" content="cuaderno digital viticultores, cuaderno campo viticultores, cuaderno digital viñedos, cuaderno campo digital viticultura, cuaderno digital obligatorio viticultores, cuaderno campo 2027 viticultores, gestión cuaderno digital viñedos, software cuaderno campo viticultores">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <meta name="author" content="Agro365">
    <meta name="language" content="Spanish">
    <meta name="geo.region" content="ES">
    <meta name="revisit-after" content="7 days">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="{{ url('/cuaderno-digital-viticultores') }}">
    
    <!-- Open Graph -->
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ url('/cuaderno-digital-viticultores') }}">
    <meta property="og:title" content="Cuaderno Digital para Viticultores - Obligatorio 2027">
    <meta property="og:description" content="Cuaderno de campo digital para viticultores obligatorio desde 2027. SIGPAC, cumplimiento normativo e informes oficiales.">
    <meta property="og:image" content="{{ asset('images/logo.png') }}">
    <meta property="og:locale" content="es_ES">
    <meta property="og:site_name" content="Agro365">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="{{ url('/cuaderno-digital-viticultores') }}">
    <meta name="twitter:title" content="Cuaderno Digital para Viticultores - Obligatorio 2027">
    <meta name="twitter:description" content="Cuaderno de campo digital para viticultores con SIGPAC y cumplimiento normativo.">
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
                        <span class="text-gray-900" itemprop="name">Cuaderno Digital para Viticultores</span>
                        <meta itemprop="position" content="2" />
                    </li>
                </ol>
            </nav>

            <!-- Header -->
            <div class="mb-12">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-red-100 border border-red-300 mb-4">
                    <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
                    <span class="text-sm font-semibold text-red-700">OBLIGATORIO DESDE 2027</span>
                </div>
                <h1 class="text-5xl lg:text-6xl font-bold text-[var(--color-agro-green-dark)] mb-6">
                    Cuaderno Digital para Viticultores
                </h1>
                <p class="text-xl text-gray-600 leading-relaxed">
                    <strong>Cuaderno de campo digital obligatorio para viticultores</strong> desde 2027 según normativa europea. Gestión completa de tratamientos fitosanitarios, riegos, fertilizaciones y labores culturales. Integración SIGPAC y cumplimiento normativo PAC garantizado.
                </p>
            </div>

            <!-- Content -->
            <article class="prose prose-lg max-w-none">
                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">¿Por Qué los Viticultores Necesitan un Cuaderno Digital?</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        Los <strong>viticultores profesionales</strong> están obligados a mantener un <strong>cuaderno de campo digital</strong> desde 2027 según la normativa europea. Este cuaderno digital debe registrar todas las actividades realizadas en el viñedo: tratamientos fitosanitarios, riegos, fertilizaciones, labores culturales y cosechas.
                    </p>
                    <div class="bg-red-50 border-l-4 border-red-500 p-6 rounded-r-lg mb-6">
                        <p class="text-red-800 font-semibold mb-2">⚠️ Importante para Viticultores:</p>
                        <p class="text-red-700">
                            A partir de 2027, el cuaderno de campo en papel NO será válido para viticultores profesionales. Debes tener un cuaderno digital que cumpla con todos los requisitos normativos, incluyendo integración SIGPAC y firma electrónica.
                        </p>
                    </div>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        Un <strong>cuaderno digital para viticultores</strong> no solo cumple con la normativa, sino que también te permite gestionar tu viñedo de forma más eficiente, con trazabilidad completa y control total de todas las actividades.
                    </p>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">¿Qué Debe Registrar el Cuaderno Digital para Viticultores?</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        El <strong>cuaderno de campo digital para viticultores</strong> debe registrar obligatoriamente:
                    </p>
                    <div class="grid md:grid-cols-2 gap-6 mb-6">
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">🌿 Tratamientos Fitosanitarios</h3>
                            <p class="text-gray-700">Producto utilizado, dosis, fecha, parcela SIGPAC, condiciones meteorológicas, plazo de seguridad y variedad tratada.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">💧 Riegos</h3>
                            <p class="text-gray-700">Fecha, cantidad de agua, método de riego, parcela, condiciones aplicadas y variedad regada.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">🌱 Fertilizaciones</h3>
                            <p class="text-gray-700">Tipo de fertilizante, dosis, fecha, parcela SIGPAC, método de aplicación y variedad fertilizada.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">🔧 Labores Culturales</h3>
                            <p class="text-gray-700">Tipo de labor, fecha, parcela, maquinaria utilizada, cuadrilla responsable y variedad trabajada.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">🍷 Cosechas y Vendimia</h3>
                            <p class="text-gray-700">Fecha de cosecha, rendimiento por parcela, variedad, calidad de uva y destino de la cosecha.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">📊 Variedades y Parcelas</h3>
                            <p class="text-gray-700">Gestión de variedades de uva, sistemas de conducción, densidad de plantación y características de cada parcela SIGPAC.</p>
                        </div>
                    </div>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">Requisitos del Cuaderno Digital para Viticultores</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        Para cumplir con la normativa, tu <strong>cuaderno digital para viticultores</strong> debe cumplir estos requisitos obligatorios:
                    </p>
                    <ul class="list-disc list-inside space-y-3 text-gray-700 mb-6 ml-4">
                        <li><strong>Registro en tiempo real:</strong> Las actividades deben registrarse inmediatamente después de realizarse</li>
                        <li><strong>Asociación SIGPAC obligatoria:</strong> Cada actividad debe estar asociada a un código SIGPAC válido</li>
                        <li><strong>Inmutabilidad:</strong> Los registros no pueden modificarse una vez guardados (solo añadir correcciones documentadas)</li>
                        <li><strong>Firma electrónica SHA-256:</strong> Los informes oficiales deben incluir firma electrónica para inspecciones</li>
                        <li><strong>Trazabilidad completa:</strong> Desde la parcela SIGPAC hasta la cosecha y facturación</li>
                        <li><strong>Gestión de variedades:</strong> Control de variedades de uva, sistemas de conducción y características específicas</li>
                        <li><strong>Acceso para inspecciones:</strong> Debe poder generarse un informe oficial en cualquier momento</li>
                        <li><strong>Almacenamiento seguro:</strong> Los datos deben estar protegidos y respaldados automáticamente</li>
                    </ul>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">Agro365: Cuaderno Digital Completo para Viticultores</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        <strong>Agro365</strong> es el <strong>cuaderno digital para viticultores</strong> más completo del mercado. Nuestra plataforma está diseñada específicamente para viticultores profesionales y cumple con todos los requisitos de la normativa 2027.
                    </p>
                    <div class="bg-gradient-to-r from-[var(--color-agro-green-bg)] to-[var(--color-agro-green-light)]/30 p-8 rounded-xl border border-[var(--color-agro-green)]/20 mb-6">
                        <h3 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mb-4">Ventajas del Cuaderno Digital Agro365 para Viticultores</h3>
                        <ul class="space-y-3 text-gray-800">
                            <li class="flex items-start gap-3">
                                <svg class="w-6 h-6 text-[var(--color-agro-green)] flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span><strong>Cumplimiento normativo 2027:</strong> Cumple con todos los requisitos del cuaderno digital obligatorio</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="w-6 h-6 text-[var(--color-agro-green)] flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span><strong>Integración SIGPAC completa:</strong> Asocia automáticamente actividades a códigos SIGPAC oficiales</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="w-6 h-6 text-[var(--color-agro-green)] flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span><strong>Gestión de variedades:</strong> Control completo de variedades de uva, sistemas de conducción y características</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="w-6 h-6 text-[var(--color-agro-green)] flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span><strong>Registro desde el campo:</strong> Registra actividades directamente desde el viñedo con tu móvil</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="w-6 h-6 text-[var(--color-agro-green)] flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span><strong>Informes oficiales con firma SHA-256:</strong> Genera informes certificados listos para inspecciones</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="w-6 h-6 text-[var(--color-agro-green)] flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span><strong>Control de vendimia:</strong> Registra cosechas, rendimientos y calidad de uva por parcela y variedad</span>
                            </li>
                        </ul>
                    </div>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">Preguntas Frecuentes sobre Cuaderno Digital para Viticultores</h2>
                    <div class="space-y-6">
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">¿Es obligatorio el cuaderno digital para viticultores?</h3>
                            <p class="text-gray-700">Sí, desde 2027 es obligatorio para todos los viticultores profesionales tener un cuaderno de campo digital que cumpla con la normativa europea, incluyendo integración SIGPAC y firma electrónica.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">¿Qué diferencia a un cuaderno digital para viticultores de uno genérico?</h3>
                            <p class="text-gray-700">Un cuaderno digital para viticultores incluye funcionalidades específicas como gestión de variedades de uva, sistemas de conducción, control de vendimia y características específicas del sector vitícola.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">¿Puedo registrar actividades desde el campo?</h3>
                            <p class="text-gray-700">Sí, con Agro365 puedes registrar todas las actividades directamente desde el viñedo usando tu smartphone o tablet, sin necesidad de estar en la oficina.</p>
                        </div>
                    </div>
                </section>
            </article>

            <!-- CTA Section -->
            <div class="mt-16 pt-12 border-t border-gray-200 text-center">
                <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">
                    Cuaderno Digital para Viticultores Obligatorio 2027
                </h2>
                <p class="text-gray-600 mb-8 text-lg">
                    Cumple con la normativa desde el primer día con Agro365. Cuaderno digital, SIGPAC, gestión de variedades y cumplimiento normativo. Prueba gratis 6 meses.
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

    <!-- ✅ SEO: Enlaces relacionados para mejorar link juice interno -->
    @include('components.related-links')

    <!-- Footer -->
    @include('partials.footer-seo')

    <!-- Breadcrumb Schema -->
    <script type="application/ld+json">
    {!! \App\Helpers\SeoHelper::breadcrumbSchema([
        ['name' => 'Inicio', 'url' => url('/')],
        ['name' => 'Cuaderno Digital para Viticultores', 'url' => url('/cuaderno-digital-viticultores')]
    ]) !!}
    </script>

    <!-- Article Schema -->
    <script type="application/ld+json">
    @php
        $articleSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => 'Cuaderno Digital para Viticultores - Obligatorio 2027',
            'description' => 'Cuaderno de campo digital para viticultores obligatorio desde 2027. SIGPAC, cumplimiento normativo e informes oficiales.',
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
                '@id' => url('/cuaderno-digital-viticultores')
            ]
        ];
    @endphp
    {!! json_encode($articleSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
    </script>
</body>
</html>

