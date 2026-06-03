<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- SEO Meta Tags -->
    <title>Gestión de Vendimia Digital 2024 | Control de Cosechas y Contenedores - Agro365</title>
    <meta name="description" content="Gestión digital de vendimia: control de contenedores, kg por parcela, grados baumé, estado sanitario y facturación Verifactu automática. Conecta viñedo y bodega en tiempo real con Agro365.">
    <meta name="keywords" content="gestión vendimia, software vendimia, control cosecha uva, contenedores vendimia, rendimientos viñedo, vendimia digital, app vendimia, cosecha viñedo, registro vendimia, gestión cosecha vino, software bodega, control vendimia, trazabilidad vendimia, facturación vendimia">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <meta name="author" content="Agro365">
    <meta name="language" content="Spanish">
    <meta name="geo.region" content="ES">
    <meta name="revisit-after" content="7 days">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="{{ url('/gestion-vendimia') }}">
    
    <!-- Open Graph -->
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ url('/gestion-vendimia') }}">
    <meta property="og:title" content="Gestión de Vendimia Digital - Control de Cosechas y Contenedores">
    <meta property="og:description" content="Software profesional para gestión de vendimia: control de cosechas, contenedores y facturación automática.">
    <meta property="og:image" content="{{ asset('images/dashboard-preview.png') }}">
    <meta property="og:locale" content="es_ES">
    <meta property="og:site_name" content="Agro365">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="{{ url('/gestion-vendimia') }}">
    <meta name="twitter:title" content="Gestión de Vendimia Digital - Agro365">
    <meta name="twitter:description" content="Control de cosechas, contenedores y rendimientos para viticultores profesionales.">
    <meta name="twitter:image" content="{{ asset('images/dashboard-preview.png') }}">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
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
                        <img src="{{ asset('images/logo.png') }}" alt="Agro365" width="120" height="40" loading="eager" class="h-10 w-auto">
                        <span class="text-xl font-bold text-[var(--color-agro-green-dark)]">{{ __('Agro365') }}</span>
                    </a>
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
                            <span itemprop="name">{{ __('Inicio') }}</span>
                        </a>
                        <meta itemprop="position" content="1" />
                    </li>
                    <span class="mx-2">/</span>
                    <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                        <span class="text-gray-900" itemprop="name">{{ __('Gestión de Vendimia') }}</span>
                        <meta itemprop="position" content="2" />
                    </li>
                </ol>
            </nav>

            <!-- Header -->
            <div class="mb-12">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-amber-100 border border-amber-300 mb-6">
                    <span class="text-lg">🍇</span>
                    <span class="text-sm font-semibold text-amber-800">{{ __('Vendimia 2024 - Control Total') }}</span>
                </div>
                <h1 class="text-5xl lg:text-6xl font-bold text-[var(--color-agro-green-dark)] mb-6">{{ __('Gestión de Vendimia Digital para Viticultores') }}</h1>
                <p class="text-xl text-gray-600 leading-relaxed">
                    <strong>{{ __('Control completo de tu vendimia') }}</strong> desde la parcela hasta la factura. Registra contenedores, compara rendimientos reales vs estimados por parcela, y genera facturas automáticamente. Todo con <strong>{{ __('trazabilidad total') }}</strong> para cumplir con la normativa.
                </p>
            </div>

            <!-- Content -->
            <article class="prose prose-lg max-w-none">
                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">{{ __('¿Por Qué Digitalizar la Gestión de Vendimia?') }}</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        La <strong>{{ __('vendimia') }}</strong> es el momento más crítico del año para cualquier viticultor. Sin un sistema adecuado de control, es fácil perder el rastro de qué uva viene de qué parcela, cuántos kilos se han recogido, y a qué precio se ha vendido.
                    </p>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        Un <strong>{{ __('software de gestión de vendimia') }}</strong> como Agro365 te permite:
                    </p>
                    <ul class="list-disc list-inside space-y-2 text-gray-700 mb-6 ml-4">
                        <li><strong>{{ __('Registrar cada contenedor') }}</strong> con parcela, variedad, peso y fecha</li>
                        <li><strong>{{ __('Comparar rendimientos') }}</strong> reales vs estimados por parcela</li>
                        <li><strong>{{ __('Trazabilidad completa') }}</strong> desde viña hasta factura</li>
                        <li><strong>{{ __('Generar facturas automáticamente') }}</strong> desde la cosecha registrada</li>
                        <li><strong>{{ __('Cumplir normativa') }}</strong> con informes oficiales de cosecha</li>
                    </ul>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">{{ __('Funcionalidades de Gestión de Vendimia en Agro365') }}</h2>
                    
                    <div class="grid md:grid-cols-2 gap-6 mb-6">
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <div class="text-3xl mb-3">📦</div>
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">{{ __('Registro de Contenedores') }}</h3>
                            <p class="text-gray-700">{{ __('Registra cada contenedor individual con peso, parcela de origen, variedad de uva, grado baumé y observaciones.') }}</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <div class="text-3xl mb-3">📊</div>
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">{{ __('Rendimientos por Parcela') }}</h3>
                            <p class="text-gray-700">{{ __('Compara el rendimiento real de cada parcela con el rendimiento estimado. Identifica parcelas de alto y bajo rendimiento.') }}</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <div class="text-3xl mb-3">🔗</div>
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">{{ __('Trazabilidad Completa') }}</h3>
                            <p class="text-gray-700">Desde el recinto <a href="{{ content_route('content.que-es-sigpac') }}" class="text-[var(--color-agro-green)] hover:underline">SIGPAC</a> hasta la factura final. Cada kilo de uva está identificado.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <div class="text-3xl mb-3">💰</div>
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">{{ __('Facturación Automática') }}</h3>
                            <p class="text-gray-700">{{ __('Genera facturas directamente desde los contenedores registrados. Sin duplicar datos, sin errores.') }}</p>
                        </div>
                    </div>

                    <div class="bg-amber-50 border-l-4 border-amber-500 p-6 rounded-r-lg mb-6">
                        <p class="text-gray-800 font-semibold mb-2">{{ __('🍷 Ejemplo Práctico:') }}</p>
                        <p class="text-gray-700">{{ __('Registras 50 contenedores de Tempranillo de la parcela "La Viña Alta" → El sistema calcula 15.000 kg totales → Comparas con el estimado de 14.500 kg → Generas factura a la bodega con 1 clic.') }}</p>
                    </div>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">{{ __('Control de Contenedores y Stock') }}</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        El sistema de <strong>{{ __('gestión de contenedores') }}</strong> de Agro365 está diseñado específicamente para viticultores profesionales:
                    </p>
                    <ul class="list-disc list-inside space-y-3 text-gray-700 mb-6 ml-4">
                        <li><strong>{{ __('Estados de contenedor:') }}</strong> Vacío, En Campo, En Transporte, Entregado, Facturado</li>
                        <li><strong>{{ __('Historial completo:') }}</strong> Cada contenedor mantiene su historial de movimientos</li>
                        <li><strong>{{ __('Control de stock:') }}</strong> Sabe cuántos contenedores tienes disponibles en cada momento</li>
                        <li><strong>{{ __('Asociación a parcelas:') }}</strong> Cada contenedor se vincula a su <a href="{{ content_route('content.que-es-sigpac') }}" class="text-[var(--color-agro-green)] hover:underline">código SIGPAC</a> de origen</li>
                        <li><strong>{{ __('Datos de calidad:') }}</strong> Registra grado baumé, estado sanitario y observaciones</li>
                    </ul>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">{{ __('Análisis de Rendimientos y Producción') }}</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        Una de las funciones más potentes es el <strong>{{ __('análisis de rendimientos') }}</strong>:
                    </p>
                    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 mb-6">
                        <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-4">{{ __('📈 Métricas de Rendimiento Disponibles') }}</h3>
                        <div class="grid md:grid-cols-3 gap-4 text-center">
                            <div>
                                <div class="text-2xl font-bold text-[var(--color-agro-green)]">kg/ha</div>
                                <div class="text-sm text-gray-600">Rendimiento por hectárea</div>
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-amber-600">%</div>
                                <div class="text-sm text-gray-600">Real vs Estimado</div>
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-blue-600">€/kg</div>
                                <div class="text-sm text-gray-600">Precio medio por variedad</div>
                            </div>
                        </div>
                    </div>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        Obtén informes de producción por <strong>{{ __('parcela, variedad, cliente y campaña') }}</strong>. Histórico de campañas anteriores para análisis interanual.
                    </p>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">{{ __('Integración con Cuaderno de Campo y PAC') }}</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">{{ __('La gestión de vendimia se integra perfectamente con el resto del sistema:') }}</p>
                    <ul class="list-disc list-inside space-y-3 text-gray-700 mb-6 ml-4">
                        <li><strong><a href="{{ content_route('content.cuaderno-digital-viticultores') }}" class="text-[var(--color-agro-green)] hover:underline">Cuaderno de Campo Digital</a>:</strong> Los tratamientos aplicados se vinculan a la cosecha</li>
                        <li><strong><a href="{{ content_route('content.normativa-pac') }}" class="text-[var(--color-agro-green)] hover:underline">Cumplimiento PAC</a>:</strong> Los rendimientos se validan contra los límites de producción</li>
                        <li><strong>{{ __('Informes Oficiales:') }}</strong> Genera informes de cosecha certificados con firma digital</li>
                        <li><strong>{{ __('Facturación:') }}</strong> De vendimia a factura en un solo clic</li>
                    </ul>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">{{ __('Beneficios de Digitalizar la Vendimia') }}</h2>
                    <div class="grid md:grid-cols-3 gap-6 mb-6">
                        <div class="text-center bg-green-50 p-6 rounded-lg border border-green-200">
                            <div class="text-4xl mb-3">⏱️</div>
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">{{ __('70% Menos Tiempo') }}</h3>
                            <p class="text-gray-700 text-sm">{{ __('Reduce el tiempo de administración de la vendimia') }}</p>
                        </div>
                        <div class="text-center bg-blue-50 p-6 rounded-lg border border-blue-200">
                            <div class="text-4xl mb-3">📋</div>
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">{{ __('0 Errores') }}</h3>
                            <p class="text-gray-700 text-sm">{{ __('Sin errores de transcripción o pérdida de datos') }}</p>
                        </div>
                        <div class="text-center bg-amber-50 p-6 rounded-lg border border-amber-200">
                            <div class="text-4xl mb-3">💶</div>
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">{{ __('Control Total') }}</h3>
                            <p class="text-gray-700 text-sm">{{ __('Máximo control sobre ingresos y rendimientos') }}</p>
                        </div>
                    </div>
                </section>

                <section class="mb-12">
                    <div class="bg-gradient-to-r from-[var(--color-agro-green-bg)] to-[var(--color-agro-green-light)]/30 p-8 rounded-xl border border-[var(--color-agro-green)]/20">
                        <h3 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mb-4">{{ __('🍇 ¿Listo para Digitalizar tu Vendimia?') }}</h3>
                        <p class="text-gray-700 mb-6">
                            Prepárate para la próxima vendimia con Agro365. <strong>{{ __('3 meses gratis') }}</strong> al registrarte. Control total de contenedores, rendimientos y facturación.
                        </p>
                        <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-lg bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:shadow-lg transition-all font-semibold">
                            Comenzar Gratis
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                        </a>
                    </div>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">{{ __('Preguntas Frecuentes sobre Gestión de Vendimia') }}</h2>
                    <div class="space-y-6">
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">{{ __('¿Puedo registrar contenedores desde el móvil?') }}</h3>
                            <p class="text-gray-700">Sí, la <a href="{{ content_route('content.app-agricultura') }}" class="text-[var(--color-agro-green)] hover:underline">app de Agro365</a> está optimizada para uso en campo. Registra contenedores directamente desde tu smartphone.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">{{ __('¿Se puede facturar directamente a varias bodegas?') }}</h3>
                            <p class="text-gray-700">{{ __('Sí, gestiona múltiples clientes (bodegas) y genera facturas separadas por cliente desde los mismos contenedores.') }}</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">{{ __('¿Cómo se compara con el rendimiento estimado?') }}</h3>
                            <p class="text-gray-700">{{ __('Introduces el rendimiento estimado por parcela al inicio de campaña. El sistema compara automáticamente con los kilos reales cosechados.') }}</p>
                        </div>
                    </div>
                </section>
            </article>

            <!-- CTA Section -->
            <div class="mt-16 pt-12 border-t border-gray-200 text-center">
                <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">{{ __('Gestiona tu Vendimia con Agro365') }}</h2>
                <p class="text-gray-600 mb-8 text-lg">{{ __('Software profesional de gestión de vendimia con control de contenedores, rendimientos y facturación automática.') }}</p>
                <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-8 py-4 rounded-xl bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:from-[var(--color-agro-green)] hover:to-[var(--color-agro-green-dark)] transition-all duration-300 shadow-lg hover:shadow-xl font-semibold text-lg">
                    Comenzar Gratis
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

    <!-- Article Schema -->
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "Article",
        "headline": "Gestión de Vendimia Digital para Viticultores - Guía Completa 2024",
        "description": "Software profesional para gestión de vendimia: control de cosechas, contenedores, rendimientos y facturación automática.",
        "author": {
            "@@type": "Organization",
            "name": "Agro365"
        },
        "publisher": {
            "@@type": "Organization",
            "name": "Agro365",
            "logo": {
                "@@type": "ImageObject",
                "url": "{{ asset('images/logo.png') }}"
            }
        },
        "datePublished": "2024-01-01",
        "dateModified": "{{ now()->toIso8601String() }}",
        "mainEntityOfPage": {
            "@@type": "WebPage",
            "@id": "{{ url('/gestion-vendimia') }}"
        }
    }
    </script>

    <!-- FAQ Schema -->
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "FAQPage",
        "mainEntity": [
            {
                "@@type": "Question",
                "name": "¿Puedo registrar contenedores desde el móvil?",
                "acceptedAnswer": {
                    "@@type": "Answer",
                    "text": "Sí, la app de Agro365 está optimizada para uso en campo. Registra contenedores directamente desde tu smartphone."
                }
            },
            {
                "@@type": "Question",
                "name": "¿Se puede facturar directamente a varias bodegas?",
                "acceptedAnswer": {
                    "@@type": "Answer",
                    "text": "Sí, gestiona múltiples clientes (bodegas) y genera facturas separadas por cliente desde los mismos contenedores."
                }
            },
            {
                "@@type": "Question",
                "name": "¿Cómo se compara con el rendimiento estimado?",
                "acceptedAnswer": {
                    "@@type": "Answer",
                    "text": "Introduces el rendimiento estimado por parcela al inicio de campaña. El sistema compara automáticamente con los kilos reales cosechados."
                }
            }
        ]
    }
    </script>
</body>
</html>
