<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- SEO Meta Tags -->
    <title>Cómo Digitalizar tu Viñedo | Guía Completa 2027 - Agro365</title>
    <meta name="description" content="Guía completa para digitalizar tu viñedo: pasos, beneficios y herramientas. Software de gestión agrícola para viticultores profesionales.">
    <meta name="keywords" content="digitalizar viñedo, software viñedos, gestión digital agrícola, digitalización agrícola, software viticultura, gestión viñedos digital, app viñedos">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <meta name="author" content="Agro365">
    <meta name="language" content="Spanish">
    <meta name="geo.region" content="ES">
    <meta name="revisit-after" content="7 days">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="{{ url('/digitalizar-viñedo') }}">
    
    <!-- Open Graph -->
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ url('/digitalizar-viñedo') }}">
    <meta property="og:title" content="Cómo Digitalizar tu Viñedo - Guía Completa 2027">
    <meta property="og:description" content="Guía paso a paso para digitalizar tu viñedo. Beneficios, herramientas y software de gestión agrícola profesional.">
    <meta property="og:image" content="{{ asset('images/logo.png') }}">
    <meta property="og:locale" content="es_ES">
    <meta property="og:site_name" content="Agro365">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="{{ url('/digitalizar-viñedo') }}">
    <meta name="twitter:title" content="Cómo Digitalizar tu Viñedo - Guía Completa">
    <meta name="twitter:description" content="Guía paso a paso para digitalizar tu viñedo con software profesional.">
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
                <span class="text-gray-900">Digitalizar Viñedo</span>
            </nav>

            <!-- Header -->
            <div class="mb-12">
                <h1 class="text-5xl lg:text-6xl font-bold text-[var(--color-agro-green-dark)] mb-6">
                    Cómo Digitalizar tu Viñedo: Guía Completa 2027
                </h1>
                <p class="text-xl text-gray-600 leading-relaxed">
                    Guía paso a paso para <strong>digitalizar tu viñedo</strong> y modernizar tu gestión agrícola. Descubre los beneficios, herramientas necesarias y cómo Agro365 puede ayudarte en el proceso.
                </p>
            </div>

            <!-- Content -->
            <article class="prose prose-lg max-w-none">
                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">¿Por Qué Digitalizar tu Viñedo?</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        La digitalización del viñedo no es solo una tendencia, es una <strong>necesidad obligatoria desde 2027</strong> según la normativa europea. Pero además de cumplir con la ley, digitalizar tu viñedo te ofrece beneficios reales:
                    </p>
                    <div class="grid md:grid-cols-2 gap-6 mb-6">
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">⏱️ Ahorro de Tiempo</h3>
                            <p class="text-gray-700">Reduce el tiempo de gestión administrativa en un 70%. Más tiempo para lo que realmente importa: tu viñedo.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">✅ Cumplimiento Normativo</h3>
                            <p class="text-gray-700">Cumple automáticamente con todas las normativas vigentes. Sin preocupaciones, sin multas.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">📊 Decisiones Basadas en Datos</h3>
                            <p class="text-gray-700">Toma decisiones basadas en datos reales. Analiza rendimientos, costos y optimiza tu rentabilidad.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">🔍 Trazabilidad Completa</h3>
                            <p class="text-gray-700">Trazabilidad completa desde la parcela hasta la facturación. Perfecto para certificaciones y calidad.</p>
                        </div>
                    </div>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">Pasos para Digitalizar tu Viñedo</h2>
                    <div class="space-y-6 mb-6">
                        <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-[var(--color-agro-green)]">
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0 w-10 h-10 rounded-full bg-[var(--color-agro-green)] text-white flex items-center justify-center font-bold text-lg">1</div>
                                <div>
                                    <h3 class="font-bold text-xl text-[var(--color-agro-green-dark)] mb-2">Registra tus Parcelas SIGPAC</h3>
                                    <p class="text-gray-700">El primer paso es registrar todas tus parcelas con sus códigos SIGPAC. Esto es obligatorio desde 2027 y es la base de toda la digitalización.</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-[var(--color-agro-green)]">
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0 w-10 h-10 rounded-full bg-[var(--color-agro-green)] text-white flex items-center justify-center font-bold text-lg">2</div>
                                <div>
                                    <h3 class="font-bold text-xl text-[var(--color-agro-green-dark)] mb-2">Configura tu Cuaderno Digital</h3>
                                    <p class="text-gray-700">Configura tu cuaderno de campo digital. Asegúrate de que incluya todos los campos obligatorios: tratamientos, riegos, fertilizaciones y labores.</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-[var(--color-agro-green)]">
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0 w-10 h-10 rounded-full bg-[var(--color-agro-green)] text-white flex items-center justify-center font-bold text-lg">3</div>
                                <div>
                                    <h3 class="font-bold text-xl text-[var(--color-agro-green-dark)] mb-2">Registra Actividades en Tiempo Real</h3>
                                    <p class="text-gray-700">Comienza a registrar todas tus actividades agrícolas en tiempo real. Desde el móvil directamente en el viñedo, sin esperar a llegar a casa.</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-[var(--color-agro-green)]">
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0 w-10 h-10 rounded-full bg-[var(--color-agro-green)] text-white flex items-center justify-center font-bold text-lg">4</div>
                                <div>
                                    <h3 class="font-bold text-xl text-[var(--color-agro-green-dark)] mb-2">Gestiona Cosechas y Rendimientos</h3>
                                    <p class="text-gray-700">Digitaliza la gestión de tus cosechas. Registra contenedores, compara rendimientos estimados vs reales y analiza por parcela y variedad.</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-[var(--color-agro-green)]">
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0 w-10 h-10 rounded-full bg-[var(--color-agro-green)] text-white flex items-center justify-center font-bold text-lg">5</div>
                                <div>
                                    <h3 class="font-bold text-xl text-[var(--color-agro-green-dark)] mb-2">Genera Informes Oficiales</h3>
                                    <p class="text-gray-700">Genera informes oficiales con firma electrónica cuando los necesites. Listos para inspecciones en segundos.</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-[var(--color-agro-green)]">
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0 w-10 h-10 rounded-full bg-[var(--color-agro-green)] text-white flex items-center justify-center font-bold text-lg">6</div>
                                <div>
                                    <h3 class="font-bold text-xl text-[var(--color-agro-green-dark)] mb-2">Integra Facturación</h3>
                                    <p class="text-gray-700">Completa la digitalización integrando la facturación. Desde la cosecha hasta la factura en un solo sistema.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">Herramientas Necesarias para Digitalizar</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        Para digitalizar tu viñedo necesitas:
                    </p>
                    <ul class="list-disc list-inside space-y-3 text-gray-700 mb-6 ml-4">
                        <li><strong>Software de gestión agrícola:</strong> Un sistema completo que incluya cuaderno digital, SIGPAC, informes oficiales y facturación</li>
                        <li><strong>Dispositivo móvil o tablet:</strong> Para registrar actividades directamente en el viñedo</li>
                        <li><strong>Conexión a internet:</strong> Aunque muchos sistemas funcionan offline y sincronizan después</li>
                        <li><strong>Códigos SIGPAC:</strong> Tus códigos SIGPAC oficiales de todas tus parcelas</li>
                    </ul>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">Cómo Agro365 Facilita la Digitalización</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        <strong>Agro365</strong> es la solución completa para digitalizar tu viñedo. Incluye todo lo que necesitas en una sola plataforma:
                    </p>
                    <div class="bg-gradient-to-r from-[var(--color-agro-green-bg)] to-[var(--color-agro-green-light)]/30 p-8 rounded-xl border border-[var(--color-agro-green)]/20 mb-6">
                        <ul class="space-y-4 text-gray-800">
                            <li class="flex items-start gap-3">
                                <svg class="w-6 h-6 text-[var(--color-agro-green)] flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span><strong>Cuaderno de campo digital:</strong> Registra todas tus actividades desde el móvil</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="w-6 h-6 text-[var(--color-agro-green)] flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span><strong>Integración SIGPAC completa:</strong> Gestiona todas tus parcelas con códigos SIGPAC</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="w-6 h-6 text-[var(--color-agro-green)] flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span><strong>Informes oficiales:</strong> Genera informes certificados con firma electrónica</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="w-6 h-6 text-[var(--color-agro-green)] flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span><strong>Gestión de cosechas:</strong> Control completo de vendimia y rendimientos</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="w-6 h-6 text-[var(--color-agro-green)] flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span><strong>Facturación integrada:</strong> Desde la cosecha hasta la factura en un solo sistema</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="w-6 h-6 text-[var(--color-agro-green)] flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span><strong>Dashboard de cumplimiento:</strong> Detecta errores automáticamente y prepárate para inspecciones</span>
                            </li>
                        </ul>
                    </div>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">Preguntas Frecuentes</h2>
                    <div class="space-y-6">
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">¿Cuánto tiempo tarda digitalizar un viñedo?</h3>
                            <p class="text-gray-700">Con Agro365, puedes comenzar a digitalizar tu viñedo en menos de 30 minutos. La configuración inicial es rápida y el sistema te guía paso a paso.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">¿Necesito conocimientos técnicos?</h3>
                            <p class="text-gray-700">No, Agro365 está diseñado para ser intuitivo y fácil de usar. No necesitas conocimientos técnicos avanzados.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">¿Puedo usar el sistema desde el móvil en el viñedo?</h3>
                            <p class="text-gray-700">Sí, Agro365 está 100% optimizado para móviles. Puedes registrar actividades directamente desde el viñedo, incluso con conexión limitada.</p>
                        </div>
                    </div>
                </section>
            </article>

            <!-- CTA Section -->
            <div class="mt-16 pt-12 border-t border-gray-200 text-center">
                <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">
                    Comienza a Digitalizar tu Viñedo Hoy
                </h2>
                <p class="text-gray-600 mb-8 text-lg">
                    Agro365 incluye todo lo que necesitas para digitalizar tu viñedo. Prueba gratis 6 meses sin compromiso.
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
        ['name' => 'Digitalizar Viñedo', 'url' => url('/digitalizar-viñedo')]
    ]) !!}
    </script>

    <!-- Article Schema -->
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "Article",
        "headline": "Cómo Digitalizar tu Viñedo - Guía Completa 2027",
        "description": "Guía paso a paso para digitalizar tu viñedo. Beneficios, herramientas y software de gestión agrícola profesional.",
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
            "@id": "{{ url('/digitalizar-viñedo') }}"
        }
    }
    </script>
</body>
</html>

