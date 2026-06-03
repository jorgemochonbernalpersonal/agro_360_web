<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- SEO Meta Tags -->
    <title>Comparativa Software Agrícola para Viñedos 2027 | Agro365 vs Competencia</title>
    <meta name="description" content="Comparativa de software agrícola 2025: Agro365 vs otras soluciones. Cuaderno digital, SIGPAC, teledetección NDVI, Verifactu, trazabilidad y precios. Viticultor básico gratis · desde 9€/mes.">
    <meta name="keywords" content="comparativa software agrícola, mejor software viñedos, software agrícola comparativa, software gestión viñedos, comparar software agrícola, software viticultura España">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <meta name="author" content="Agro365">
    <meta name="language" content="Spanish">
    <meta name="geo.region" content="ES">
    <meta name="revisit-after" content="7 days">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="{{ url('/comparativa-software-agricola') }}">
    
    <!-- Open Graph -->
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ url('/comparativa-software-agricola') }}">
    <meta property="og:title" content="Comparativa Software Agrícola para Viñedos 2027">
    <meta property="og:description" content="Compara Agro365 con otras soluciones de software agrícola. Cuaderno digital, SIGPAC, informes oficiales y más.">
    <meta property="og:image" content="{{ asset('images/dashboard-preview.png') }}">
    <meta property="og:locale" content="es_ES">
    <meta property="og:site_name" content="Agro365">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="{{ url('/comparativa-software-agricola') }}">
    <meta name="twitter:title" content="Comparativa Software Agrícola para Viñedos">
    <meta name="twitter:description" content="Compara Agro365 con otras soluciones de software agrícola para viñedos.">
    <meta name="twitter:image" content="{{ asset('images/dashboard-preview.png') }}">
    
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
            <nav class="mb-8 text-sm text-gray-600">
                <a href="{{ url('/') }}" class="hover:text-[var(--color-agro-green)]">Inicio</a>
                <span class="mx-2">/</span>
                <span class="text-gray-900">{{ __('Comparativa Software Agrícola') }}</span>
            </nav>

            <!-- Header -->
            <div class="mb-12">
                <h1 class="text-5xl lg:text-6xl font-bold text-[var(--color-agro-green-dark)] mb-6">{{ __('Comparativa de Software Agrícola para Viñedos 2027') }}</h1>
                <p class="text-xl text-gray-600 leading-relaxed">
                    Compara <strong>{{ __('Agro365') }}</strong> con otras soluciones de software agrícola. Descubre por qué Agro365 es la mejor opción para viticultores profesionales que buscan cumplir con la normativa 2027.
                </p>
            </div>

            <!-- Content -->
            <article class="prose prose-lg max-w-none">
                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">{{ __('¿Qué Buscar en un Software Agrícola para Viñedos?') }}</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">{{ __('Antes de comparar, es importante saber qué características son esenciales en un software agrícola para viñedos en 2027:') }}</p>
                    <ul class="list-disc list-inside space-y-2 text-gray-700 mb-6 ml-4">
                        <li><strong>{{ __('Cuaderno de campo digital obligatorio:') }}</strong> Debe cumplir con todos los requisitos normativos 2027</li>
                        <li><strong>{{ __('Integración SIGPAC completa:') }}</strong> Asociación automática de actividades a códigos SIGPAC</li>
                        <li><strong>{{ __('Informes oficiales con firma electrónica:') }}</strong> Generación de informes certificados SHA-256</li>
                        <li><strong>{{ __('Dashboard de cumplimiento PAC:') }}</strong> Validación automática y detección de errores</li>
                        <li><strong>{{ __('Gestión de cosechas:') }}</strong> Control de vendimia, contenedores y rendimientos</li>
                        <li><strong>{{ __('Facturación integrada:') }}</strong> Desde la cosecha hasta la factura en un solo sistema</li>
                        <li><strong>{{ __('Uso móvil:') }}</strong> Registro de actividades desde el viñedo</li>
                        <li><strong>{{ __('Precio asequible:') }}</strong> Accesible para pequeños y medianos viticultores</li>
                    </ul>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">{{ __('Comparativa: Agro365 vs Otras Soluciones') }}</h2>
                    
                    <div class="overflow-x-auto mb-8">
                        <table class="min-w-full bg-white border border-gray-200 rounded-lg shadow-sm">
                            <thead class="bg-[var(--color-agro-green-bg)]">
                                <tr>
                                    <th class="px-6 py-4 text-left text-sm font-bold text-[var(--color-agro-green-dark)]">{{ __('Característica') }}</th>
                                    <th class="px-6 py-4 text-center text-sm font-bold text-[var(--color-agro-green-dark)]">{{ __('Agro365') }}</th>
                                    <th class="px-6 py-4 text-center text-sm font-bold text-gray-700">{{ __('Otras Soluciones') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr>
                                    <td class="px-6 py-4 text-sm text-gray-700 font-medium">{{ __('Cuaderno Digital Obligatorio 2027') }}</td>
                                    <td class="px-6 py-4 text-center"><span class="text-[var(--color-agro-green)] font-bold">{{ __('✓ Completo') }}</span></td>
                                    <td class="px-6 py-4 text-center text-gray-500">{{ __('Parcial o incompleto') }}</td>
                                </tr>
                                <tr class="bg-gray-50">
                                    <td class="px-6 py-4 text-sm text-gray-700 font-medium">{{ __('Integración SIGPAC') }}</td>
                                    <td class="px-6 py-4 text-center"><span class="text-[var(--color-agro-green)] font-bold">{{ __('✓ Completa') }}</span></td>
                                    <td class="px-6 py-4 text-center text-gray-500">{{ __('Limitada o manual') }}</td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 text-sm text-gray-700 font-medium">{{ __('Informes Oficiales con Firma SHA-256') }}</td>
                                    <td class="px-6 py-4 text-center"><span class="text-[var(--color-agro-green)] font-bold">{{ __('✓ 7 Tipos') }}</span></td>
                                    <td class="px-6 py-4 text-center text-gray-500">{{ __('Limitados o sin firma') }}</td>
                                </tr>
                                <tr class="bg-gray-50">
                                    <td class="px-6 py-4 text-sm text-gray-700 font-medium">{{ __('Dashboard Cumplimiento PAC') }}</td>
                                    <td class="px-6 py-4 text-center"><span class="text-[var(--color-agro-green)] font-bold">{{ __('✓ Automático') }}</span></td>
                                    <td class="px-6 py-4 text-center text-gray-500">{{ __('Manual o inexistente') }}</td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 text-sm text-gray-700 font-medium">{{ __('Gestión de Cosechas') }}</td>
                                    <td class="px-6 py-4 text-center"><span class="text-[var(--color-agro-green)] font-bold">{{ __('✓ Completa') }}</span></td>
                                    <td class="px-6 py-4 text-center text-gray-500">{{ __('Básica o inexistente') }}</td>
                                </tr>
                                <tr class="bg-gray-50">
                                    <td class="px-6 py-4 text-sm text-gray-700 font-medium">{{ __('Facturación Integrada') }}</td>
                                    <td class="px-6 py-4 text-center"><span class="text-[var(--color-agro-green)] font-bold">{{ __('✓ Sí') }}</span></td>
                                    <td class="px-6 py-4 text-center text-gray-500">{{ __('No o externa') }}</td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 text-sm text-gray-700 font-medium">{{ __('Uso Móvil Optimizado') }}</td>
                                    <td class="px-6 py-4 text-center"><span class="text-[var(--color-agro-green)] font-bold">{{ __('✓ 100%') }}</span></td>
                                    <td class="px-6 py-4 text-center text-gray-500">{{ __('Limitado') }}</td>
                                </tr>
                                <tr class="bg-gray-50">
                                    <td class="px-6 py-4 text-sm text-gray-700 font-medium">{{ __('Precio Mensual') }}</td>
                                    <td class="px-6 py-4 text-center"><span class="text-[var(--color-agro-green)] font-bold">{{ __('Gratis · 9€ · 14€') }}</span></td>
                                    <td class="px-6 py-4 text-center text-gray-500">{{ __('€20-50/mes o más') }}</td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 text-sm text-gray-700 font-medium">{{ __('Prueba Gratis') }}</td>
                                    <td class="px-6 py-4 text-center"><span class="text-[var(--color-agro-green)] font-bold">{{ __('3 meses') }}</span></td>
                                    <td class="px-6 py-4 text-center text-gray-500">{{ __('7-30 días') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">{{ __('Ventajas de Agro365') }}</h2>
                    <div class="grid md:grid-cols-2 gap-6 mb-6">
                        <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-[var(--color-agro-green)]">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">{{ __('✅ Cumplimiento 100% Normativa 2027') }}</h3>
                            <p class="text-gray-700">{{ __('Agro365 está diseñado específicamente para cumplir con todos los requisitos de la normativa 2027 desde el primer día.') }}</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-[var(--color-agro-green)]">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">{{ __('💰 Precio Justo por Perfil') }}</h3>
                            <p class="text-gray-700">{{ __('Básico gratis (invitado por bodega), completo desde 9€/mes o 14€/mes independiente. La opción más asequible del mercado.') }}</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-[var(--color-agro-green)]">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">{{ __('🎁 3 Meses Gratis') }}</h3>
                            <p class="text-gray-700">{{ __('Prueba completa durante 3 meses sin compromiso. Sin tarjeta requerida.') }}</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-[var(--color-agro-green)]">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">{{ __('📱 100% Móvil') }}</h3>
                            <p class="text-gray-700">{{ __('Diseñado para móviles desde el principio. Registra actividades directamente desde el viñedo.') }}</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-[var(--color-agro-green)]">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">{{ __('🔧 Todo en Uno') }}</h3>
                            <p class="text-gray-700">{{ __('Cuaderno digital, SIGPAC, informes, cosechas y facturación en una sola plataforma.') }}</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-[var(--color-agro-green)]">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">{{ __('⚡ Configuración Rápida') }}</h3>
                            <p class="text-gray-700">{{ __('Configuración en menos de 30 minutos. Sin conocimientos técnicos avanzados necesarios.') }}</p>
                        </div>
                    </div>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">{{ __('¿Por Qué Elegir Agro365?') }}</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">{{ __('Agro365 es la mejor opción para viticultores profesionales porque:') }}</p>
                    <div class="bg-gradient-to-r from-[var(--color-agro-green-bg)] to-[var(--color-agro-green-light)]/30 p-8 rounded-xl border border-[var(--color-agro-green)]/20 mb-6">
                        <ul class="space-y-4 text-gray-800">
                            <li class="flex items-start gap-3">
                                <svg class="w-6 h-6 text-[var(--color-agro-green)] flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span><strong>{{ __('Cumplimiento garantizado:') }}</strong> Diseñado específicamente para cumplir con la normativa 2027</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="w-6 h-6 text-[var(--color-agro-green)] flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span><strong>{{ __('Precio justo:') }}</strong> La solución más completa al mejor precio del mercado</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="w-6 h-6 text-[var(--color-agro-green)] flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span><strong>{{ __('Fácil de usar:') }}</strong> Interfaz intuitiva diseñada para viticultores, no para informáticos</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="w-6 h-6 text-[var(--color-agro-green)] flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span><strong>{{ __('Soporte en español:') }}</strong> Atención al cliente en español, de viticultores para viticultores</span>
                            </li>
                        </ul>
                    </div>
                </section>
            </article>

            <!-- CTA Section -->
            <div class="mt-16 pt-12 border-t border-gray-200 text-center">
                <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">{{ __('Prueba Agro365 Gratis Durante 6 Meses') }}</h2>
                <p class="text-gray-600 mb-8 text-lg">{{ __('Compara tú mismo. Prueba Agro365 durante 3 meses completamente gratis y descubre por qué es la mejor opción para tu viñedo.') }}</p>
                <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-8 py-4 rounded-xl bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:from-[var(--color-agro-green)] hover:to-[var(--color-agro-green-dark)] transition-all duration-300 shadow-lg hover:shadow-xl font-semibold text-lg">
                    Comenzar Gratis
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
        ['name' => 'Comparativa Software Agrícola', 'url' => url('/comparativa-software-agricola')]
    ]) !!}
    </script>

    <!-- Article Schema -->
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "Article",
        "headline": "Comparativa Software Agrícola para Viñedos 2027",
        "description": "Compara Agro365 con otras soluciones de software agrícola. Cuaderno digital, SIGPAC, informes oficiales y más.",
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
            "@id": "{{ url('/comparativa-software-agricola') }}"
        }
    }
    </script>
</body>
</html>

