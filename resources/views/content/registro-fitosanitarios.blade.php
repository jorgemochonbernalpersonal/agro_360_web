<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- SEO Meta Tags -->
    <title>Registro de Productos Fitosanitarios | Software de Tratamientos</title>
    <meta name="description" content="Registro digital obligatorio de productos fitosanitarios. Cumple con la normativa oficial y digitaliza tus tratamientos agrícolas con Agro365.">
    <meta name="keywords" content="registro fitosanitarios, productos fitosanitarios, tratamientos fitosanitarios, cuaderno fitosanitarios, registro tratamientos, ROPO, registro oficial fitosanitarios, aplicador fitosanitarios, normativa fitosanitarios, control fitosanitarios, software fitosanitarios, app tratamientos, registro plagas">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <meta name="author" content="Agro365">
    <meta name="language" content="Spanish">
    <meta name="geo.region" content="ES">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="{{ url('/registro-fitosanitarios') }}">
    
    <!-- Open Graph -->
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ url('/registro-fitosanitarios') }}">
    <meta property="og:title" content="Registro de Productos Fitosanitarios - Cumple con la Normativa">
    <meta property="og:description" content="Registro digital obligatorio de tratamientos fitosanitarios. Software profesional para viticultores.">
    <meta property="og:image" content="{{ asset('images/dashboard-preview.png') }}">
    <meta property="og:locale" content="es_ES">
    <meta property="og:site_name" content="Agro365">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    
    <!-- Fonts & Styles -->
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-white">
    <!-- Header -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <a href="{{ url('/') }}" class="flex items-center gap-3">
                    <img src="{{ asset('images/logo.png') }}" alt="Agro365" width="120" height="40" loading="eager" class="h-10 w-auto">
                    <span class="text-xl font-bold text-[var(--color-agro-green-dark)]">{{ __('Agro365') }}</span>
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

    <!-- Main Content -->
    <div class="min-h-screen bg-gradient-to-b from-white to-gray-50 py-20">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Breadcrumb -->
            <nav class="mb-8 text-sm text-gray-600" itemscope itemtype="https://schema.org/BreadcrumbList">
                <ol class="flex items-center space-x-2">
                    <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                        <a href="{{ url('/') }}" class="hover:text-[var(--color-agro-green)]" itemprop="item"><span itemprop="name">{{ __('Inicio') }}</span></a>
                        <meta itemprop="position" content="1" />
                    </li>
                    <span class="mx-2">/</span>
                    <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                        <span class="text-gray-900" itemprop="name">{{ __('Registro de Fitosanitarios') }}</span>
                        <meta itemprop="position" content="2" />
                    </li>
                </ol>
            </nav>

            <!-- Header -->
            <div class="mb-12">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-red-100 border border-red-300 mb-6">
                    <span class="text-lg">⚠️</span>
                    <span class="text-sm font-semibold text-red-800">{{ __('Registro Obligatorio') }}</span>
                </div>
                <h1 class="text-5xl lg:text-6xl font-bold text-[var(--color-agro-green-dark)] mb-6">{{ __('Registro de Productos Fitosanitarios: Guía Completa') }}</h1>
                <p class="text-xl text-gray-600 leading-relaxed">
                    El <strong>{{ __('registro de tratamientos fitosanitarios') }}</strong> es obligatorio para todos los agricultores. Conoce la normativa, qué datos debes registrar y cómo cumplir automáticamente con Agro365.
                </p>
            </div>

            <!-- Content -->
            <article class="prose prose-lg max-w-none">
                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">{{ __('¿Por Qué es Obligatorio el Registro de Fitosanitarios?') }}</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        La <strong>{{ __('normativa europea') }}</strong> y española obliga a todos los agricultores a mantener un registro detallado de todos los <strong>{{ __('productos fitosanitarios') }}</strong> aplicados en sus parcelas. Este registro es parte esencial del <a href="{{ content_route('content.cuaderno-digital-viticultores') }}" class="text-[var(--color-agro-green)] hover:underline">cuaderno de campo digital</a>.
                    </p>
                    <div class="bg-red-50 border-l-4 border-red-500 p-6 rounded-r-lg mb-6">
                        <p class="text-gray-800 font-semibold mb-2">{{ __('⚠️ Importante:') }}</p>
                        <p class="text-gray-700">{{ __('El incumplimiento del registro de fitosanitarios puede suponer sanciones de hasta 60.000€ y la pérdida de ayudas PAC.') }}</p>
                    </div>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">{{ __('Datos Obligatorios en el Registro de Tratamientos') }}</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">{{ __('Cada aplicación de productos fitosanitarios debe incluir:') }}</p>
                    <div class="grid md:grid-cols-2 gap-4 mb-6">
                        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-[var(--color-agro-green-dark)] mb-2">{{ __('📅 Datos del Tratamiento') }}</h3>
                            <ul class="text-gray-700 text-sm space-y-1">
                                <li>{{ __('• Fecha y hora de aplicación') }}</li>
                                <li>• Parcela tratada (código <a href="{{ content_route('content.que-es-sigpac') }}" class="text-[var(--color-agro-green)]">SIGPAC</a>)</li>
                                <li>{{ __('• Superficie tratada (ha)') }}</li>
                                <li>{{ __('• Plaga, enfermedad o mala hierba') }}</li>
                            </ul>
                        </div>
                        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-[var(--color-agro-green-dark)] mb-2">{{ __('🧪 Datos del Producto') }}</h3>
                            <ul class="text-gray-700 text-sm space-y-1">
                                <li>{{ __('• Nombre comercial del producto') }}</li>
                                <li>{{ __('• Número de registro fitosanitario') }}</li>
                                <li>{{ __('• Dosis aplicada (l/ha o kg/ha)') }}</li>
                                <li>{{ __('• Volumen total de caldo') }}</li>
                            </ul>
                        </div>
                        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-[var(--color-agro-green-dark)] mb-2">{{ __('👤 Datos del Aplicador') }}</h3>
                            <ul class="text-gray-700 text-sm space-y-1">
                                <li>{{ __('• Nombre del aplicador') }}</li>
                                <li>{{ __('• Número de carnet ROPO') }}</li>
                                <li>{{ __('• Nivel de cualificación') }}</li>
                                <li>{{ __('• Empresa aplicadora (si aplica)') }}</li>
                            </ul>
                        </div>
                        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-[var(--color-agro-green-dark)] mb-2">{{ __('⏰ Plazos de Seguridad') }}</h3>
                            <ul class="text-gray-700 text-sm space-y-1">
                                <li>{{ __('• Plazo de seguridad (días)') }}</li>
                                <li>{{ __('• Plazo de reentrada') }}</li>
                                <li>{{ __('• Fecha límite de recolección') }}</li>
                                <li>{{ __('• Condiciones meteorológicas') }}</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">{{ __('¿Qué es el ROPO?') }}</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        El <strong>{{ __('ROPO') }}</strong> (Registro Oficial de Productores y Operadores de medios de defensa fitosanitaria) es el registro obligatorio para:
                    </p>
                    <ul class="list-disc list-inside space-y-2 text-gray-700 mb-6 ml-4">
                        <li><strong>{{ __('Agricultores:') }}</strong> Usuarios profesionales de productos fitosanitarios</li>
                        <li><strong>{{ __('Aplicadores:') }}</strong> Personal que realiza tratamientos fitosanitarios</li>
                        <li><strong>{{ __('Distribuidores:') }}</strong> Empresas que venden productos fitosanitarios</li>
                        <li><strong>{{ __('Asesores:') }}</strong> Técnicos que recomiendan tratamientos</li>
                    </ul>
                    <p class="text-gray-700 leading-relaxed mb-4">{{ __('En Agro365, puedes registrar el número ROPO de cada operario y el sistema valida automáticamente que todos los tratamientos tengan un aplicador autorizado.') }}</p>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">{{ __('Cómo Cumplir con Agro365') }}</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        Con Agro365, el registro de fitosanitarios es <strong>{{ __('automático y sin errores') }}</strong>:
                    </p>
                    <div class="grid md:grid-cols-2 gap-6 mb-6">
                        <div class="bg-green-50 p-6 rounded-lg border border-green-200">
                            <div class="text-3xl mb-3">📋</div>
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">{{ __('Base de Datos de Productos') }}</h3>
                            <p class="text-gray-700">{{ __('Base de datos actualizada de productos fitosanitarios autorizados con dosis y plazos.') }}</p>
                        </div>
                        <div class="bg-green-50 p-6 rounded-lg border border-green-200">
                            <div class="text-3xl mb-3">⚠️</div>
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">{{ __('Alertas Automáticas') }}</h3>
                            <p class="text-gray-700">{{ __('Alertas de plazo de seguridad antes de cosechar. Aviso si falta algún dato obligatorio.') }}</p>
                        </div>
                        <div class="bg-green-50 p-6 rounded-lg border border-green-200">
                            <div class="text-3xl mb-3">📱</div>
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">{{ __('Registro desde Campo') }}</h3>
                            <p class="text-gray-700">{{ __('Registra tratamientos desde el móvil mientras estás en la parcela.') }}</p>
                        </div>
                        <div class="bg-green-50 p-6 rounded-lg border border-green-200">
                            <div class="text-3xl mb-3">📄</div>
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">{{ __('Informes Oficiales') }}</h3>
                            <p class="text-gray-700">{{ __('Genera informes de tratamientos con firma digital para inspecciones.') }}</p>
                        </div>
                    </div>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">{{ __('Sanciones por Incumplimiento') }}</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">{{ __('Las sanciones por no llevar el registro de fitosanitarios correctamente pueden ser:') }}</p>
                    <div class="bg-red-50 p-6 rounded-lg border border-red-200 mb-6">
                        <ul class="space-y-3 text-gray-700">
                            <li class="flex items-center gap-3">
                                <span class="font-bold text-red-600">{{ __('Leve:') }}</span> 
                                <span>{{ __('Multa de 1.001€ a 10.000€') }}</span>
                            </li>
                            <li class="flex items-center gap-3">
                                <span class="font-bold text-red-600">{{ __('Grave:') }}</span> 
                                <span>{{ __('Multa de 10.001€ a 60.000€') }}</span>
                            </li>
                            <li class="flex items-center gap-3">
                                <span class="font-bold text-red-600">{{ __('Muy Grave:') }}</span> 
                                <span>{{ __('Multa de 60.001€ a 600.000€ + pérdida PAC') }}</span>
                            </li>
                        </ul>
                    </div>
                </section>

                <section class="mb-12">
                    <div class="bg-gradient-to-r from-[var(--color-agro-green-bg)] to-[var(--color-agro-green-light)]/30 p-8 rounded-xl border border-[var(--color-agro-green)]/20">
                        <h3 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mb-4">{{ __('🛡️ Cumple con la Normativa Fácilmente') }}</h3>
                        <p class="text-gray-700 mb-6">
                            Registra todos tus tratamientos fitosanitarios con Agro365. Base de datos actualizada, alertas automáticas y <strong>{{ __('3 meses gratis') }}</strong>.
                        </p>
                        <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-lg bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:shadow-lg transition-all font-semibold">
                            Comenzar Gratis
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                        </a>
                    </div>
                </section>
            </article>
        </div>
    </div>

    <!-- ✅ SEO: Enlaces relacionados para mejorar link juice interno -->
    @include('components.related-links')

    @include('partials.footer-seo')

    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "Article",
        "headline": "Registro de Productos Fitosanitarios Obligatorio - Guía Completa",
        "description": "Registro digital obligatorio de productos fitosanitarios y tratamientos en agricultura.",
        "author": {"@@type": "Organization", "name": "Agro365"},
        "publisher": {"@@type": "Organization", "name": "Agro365", "logo": {"@@type": "ImageObject", "url": "{{ asset('images/logo.png') }}"}},
        "datePublished": "2024-01-01",
        "dateModified": "{{ now()->toIso8601String() }}"
    }
    </script>
</body>
</html>
