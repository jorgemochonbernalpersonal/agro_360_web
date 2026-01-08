<!DOCTYPE html>
<html lang="es" itemscope itemtype="https://schema.org/WebSite">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, user-scalable=yes">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <!-- SEO Meta Tags -->
    <title>Agro365 | Software de Gestión Agrícola y Cuaderno Digital 2027</title>
    <meta name="description" content="La plataforma líder en gestión agrícola para viticultores y bodegas. Cuaderno de campo digital obligatorio 2027, gestión SIGPAC, teledetección NDVI y facturación. ¡Prueba 6 meses gratis!">
    <meta name="keywords" content="software gestión agrícola, cuaderno digital campo, software viñedos España, SIGPAC, gestión parcelas agrícolas, app viticultores, software bodega, cuaderno campo digital, gestión viticultura, software agricultura, control parcelas, normativa PAC, cuaderno campo 2027, digitalización agrícola, trazabilidad viñedos, gestión vendimia, facturación agrícola, informes oficiales agricultura, firma electrónica agrícola, gestión cosechas, control fitosanitarios, software para viticultores, app agricultura, cuaderno digital viticultores, software agricultura España, gestión agrícola digital, app campo, software viñedos, digitalización campo, agricultura 4.0, viticultura digital, aplicación agrícola, software viticultura, gestión viñedos, app gestión agrícola, software agrícola móvil, cuaderno campo digital viticultores, software viticultores profesional, aplicación campo digital, app SIGPAC, software agrícola viticultura">
    <meta name="author" content="Agro365">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <meta name="language" content="Spanish">
    <meta name="geo.region" content="ES">
    <meta name="geo.placename" content="España">
    <meta name="revisit-after" content="7 days">
    <meta name="distribution" content="global">
    <meta name="rating" content="general">
    
    <!-- Canonical URL -->
    <meta name="canonical" href="{{ url('/') }}">
    <link rel="canonical" href="{{ url('/') }}">
    
    <!-- Hreflang for Spain -->
    <link rel="alternate" hreflang="es" href="{{ url('/') }}">
    <link rel="alternate" hreflang="es-ES" href="{{ url('/') }}">
    <link rel="alternate" hreflang="x-default" href="{{ url('/') }}">
    
    <!-- Additional SEO Meta Tags -->
    <meta name="author" content="Agro365">
    <meta name="publisher" content="Agro365">
    <meta name="theme-color" content="#10b981">
    <meta name="format-detection" content="telephone=no">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Agro365">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:title" content="Agro365 - Gestión Agrícola Profesional para Viñedos">
    <meta property="og:description" content="Digitaliza tu cuaderno de campo, gestiona parcelas SIGPAC y controla todas las actividades de tu viñedo. Prueba gratis 6 meses.">
    <meta property="og:image" content="{{ asset('images/logo.png') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="Agro365 - Software de Gestión Agrícola para Viñedos">
    <meta property="og:locale" content="es_ES">
    <meta property="og:site_name" content="Agro365">
    <meta property="article:author" content="Agro365">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="{{ url('/') }}">
    <meta name="twitter:title" content="Agro365 - Software de Gestión Agrícola para Viñedos">
    <meta name="twitter:description" content="Cuaderno digital, SIGPAC, control de parcelas. 6 meses gratis para beta testers.">
    <meta name="twitter:image" content="{{ asset('images/logo.png') }}">
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/logo.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/logo.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/logo.png') }}">
    <meta name="msapplication-TileImage" content="{{ asset('images/logo.png') }}">
    <meta name="msapplication-TileColor" content="#10b981">
    
    <!-- Fonts - Optimized for Performance -->
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link rel="dns-prefetch" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&display=swap" rel="stylesheet" />
    
    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- JSON-LD Structured Data for SEO -->
    <script type="application/ld+json">
    @php
        $structuredData = [
            '@context' => 'https://schema.org',
            '@type' => 'SoftwareApplication',
            'name' => 'Agro365',
            'applicationCategory' => 'BusinessApplication',
            'applicationSubCategory' => 'Agricultural Management Software',
            'offers' => [
                [
                    '@type' => 'Offer',
                    'name' => 'Plan Mensual',
                    'price' => '9.00',
                    'priceCurrency' => 'EUR',
                    'priceValidUntil' => '2025-12-31',
                    'availability' => 'https://schema.org/InStock',
                    'description' => 'Plan mensual de Agro365 con 6 meses gratis para beta testers'
                ],
                [
                    '@type' => 'Offer',
                    'name' => 'Plan Anual',
                    'price' => '90.00',
                    'priceCurrency' => 'EUR',
                    'priceValidUntil' => '2025-12-31',
                    'availability' => 'https://schema.org/InStock',
                    'description' => 'Plan anual de Agro365 con 6 meses gratis para beta testers'
                ]
            ],
            'description' => 'Software de gestión agrícola profesional para viticultores y bodegas con cuaderno de campo digital, SIGPAC e integración completa',
            'operatingSystem' => ['Web', 'iOS', 'Android'],
            'releaseNotes' => 'Versión Beta - En desarrollo activo',
            'screenshot' => asset('images/dashboard-preview.png'),
            // ✅ SEO: Rating solo si hay reviews reales (comentado por ahora)
            // 'aggregateRating' => [
            //     '@type' => 'AggregateRating',
            //     'ratingValue' => '4.8',
            //     'ratingCount' => '150',
            //     'bestRating' => '5',
            //     'worstRating' => '1'
            // ],
            'featureList' => [
                'Cuaderno de campo digital',
                'Gestión de parcelas SIGPAC',
                'Informes oficiales con firma electrónica',
                'Dashboard de cumplimiento PAC en tiempo real',
                'Control de cosechas y rendimientos',
                'Facturación integrada',
                'Gestión de cuadrillas y maquinaria'
            ]
        ];
    @endphp
    {!! json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    </script>
    
    <!-- Organization Schema -->
    <script type="application/ld+json">
    @php
        $organizationData = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => 'Agro365',
            'url' => url('/'),
            'logo' => asset('images/logo.png'),
            'description' => 'Plataforma de gestión agrícola profesional para viticultores y bodegas',
            'foundingDate' => '2024',
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'email' => 'info@agro365.es',
                'contactType' => 'customer service',
                'availableLanguage' => ['Spanish'],
                'areaServed' => 'ES'
            ],
            'sameAs' => [
                'https://instagram.com/agro365',
                'https://youtube.com/@agro365',
                'https://linkedin.com/company/agro365',
                'https://twitter.com/agro365'
            ]
        ];
    @endphp
    {!! json_encode($organizationData, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    </script>
    
    <!-- LocalBusiness Schema for Spain SEO -->
    <script type="application/ld+json">
    @php
        $localBusinessData = [
            '@context' => 'https://schema.org',
            '@type' => 'LocalBusiness',
            'name' => 'Agro365',
            'description' => 'Software de gestión agrícola para viticultores profesionales',
            'url' => url('/'),
            'logo' => asset('images/logo.png'),
            'address' => [
                '@type' => 'PostalAddress',
                'addressCountry' => 'ES',
                'addressRegion' => 'España'
            ],
            'areaServed' => [
                '@type' => 'Country',
                'name' => 'España'
            ],
            'availableLanguage' => 'Spanish',
            'priceRange' => '€9-€90',
            'email' => 'info@agro365.es'
        ];
    @endphp
    {!! json_encode($localBusinessData, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    </script>
    
    <!-- WebSite Schema with SearchAction -->
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "WebSite",
        "name": "Agro365",
        "url": "{{ url('/') }}",
        "potentialAction": {
            "@@type": "SearchAction",
            "target": {
                "@@type": "EntryPoint",
                "urlTemplate": "{{ url('/') }}?s={search_term_string}"
            },
            "query-input": "required name=search_term_string"
        }
    }
    </script>
    
    <!-- BreadcrumbList Schema for Homepage -->
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "BreadcrumbList",
        "itemListElement": [{
            "@@type": "ListItem",
            "position": 1,
            "name": "Inicio",
            "item": "{{ url('/') }}"
        }]
    }
    </script>
    
    <!-- Service Schema - Describes services offered -->
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "Service",
        "serviceType": "Software de Gestión Agrícola",
        "provider": {
            "@@type": "Organization",
            "name": "Agro365",
            "url": "{{ url('/') }}"
        },
        "areaServed": {
            "@@type": "Country",
            "name": "España"
        },
        "hasOfferCatalog": {
            "@@type": "OfferCatalog",
            "name": "Servicios de Agro365",
            "itemListElement": [
                {
                    "@@type": "Offer",
                    "itemOffered": {
                        "@@type": "Service",
                        "name": "Cuaderno de Campo Digital",
                        "description": "Registro digital de todas las actividades agrícolas cumpliendo normativa"
                    }
                },
                {
                    "@@type": "Offer",
                    "itemOffered": {
                        "@@type": "Service",
                        "name": "Gestión de Parcelas SIGPAC",
                        "description": "Integración completa con SIGPAC para gestión de parcelas agrícolas"
                    }
                },
                {
                    "@@type": "Offer",
                    "itemOffered": {
                        "@@type": "Service",
                        "name": "Informes Oficiales con Firma Electrónica",
                        "description": "Generación de informes oficiales certificados con firma SHA-256"
                    }
                },
                {
                    "@@type": "Offer",
                    "itemOffered": {
                        "@@type": "Service",
                        "name": "Control de Cosechas",
                        "description": "Gestión completa de vendimia, contenedores y rendimientos"
                    }
                },
                {
                    "@@type": "Offer",
                    "itemOffered": {
                        "@@type": "Service",
                        "name": "Facturación Integrada",
                        "description": "Sistema de facturación integrado con gestión de clientes y stock"
                    }
                }
            ]
        }
    }
    </script>
    
    <!-- ItemList Schema for Features -->
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "ItemList",
        "name": "Características de Agro365",
        "description": "Funcionalidades principales del software de gestión agrícola",
        "itemListElement": [
            {
                "@@type": "ListItem",
                "position": 1,
                "name": "Cuaderno de Campo Digital",
                "description": "Registro de tratamientos fitosanitarios, riegos, fertilizaciones y labores"
            },
            {
                "@@type": "ListItem",
                "position": 2,
                "name": "Integración SIGPAC",
                "description": "Gestión de parcelas con códigos SIGPAC y visualización en mapa"
            },
            {
                "@@type": "ListItem",
                "position": 3,
                "name": "Informes Oficiales",
                "description": "7 tipos de informes con firma electrónica y código QR de verificación"
            },
            {
                "@@type": "ListItem",
                "position": 4,
                "name": "Control de Cosechas",
                "description": "Registro de vendimia, contenedores y análisis de rendimientos"
            },
            {
                "@@type": "ListItem",
                "position": 5,
                "name": "Facturación",
                "description": "Sistema completo de facturación con gestión de clientes y stock"
            },
            {
                "@@type": "ListItem",
                "position": 6,
                "name": "Gestión de Recursos",
                "description": "Control de cuadrillas, maquinaria y productos fitosanitarios"
            }
        ]
    }
    </script>
    
    <!-- ✅ SEO: HowTo Schema - Tutorial paso a paso para configurar Agro365 -->
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "HowTo",
        "name": "Cómo configurar Agro365 en 5 minutos",
        "description": "Guía paso a paso para configurar tu cuenta de Agro365 y empezar a gestionar tu viñedo digitalmente",
        "image": "{{ asset('images/dashboard-preview.png') }}",
        "totalTime": "PT5M",
        "estimatedCost": {
            "@@type": "MonetaryAmount",
            "currency": "EUR",
            "value": "0"
        },
        "step": [
            {
                "@@type": "HowToStep",
                "position": 1,
                "name": "Regístrate gratis",
                "text": "Crea tu cuenta en Agro365. No se requiere tarjeta de crédito. Obtendrás 6 meses gratis.",
                "url": "{{ route('register') }}"
            },
            {
                "@@type": "HowToStep",
                "position": 2,
                "name": "Verifica tu email",
                "text": "Confirma tu dirección de correo electrónico para activar tu cuenta."
            },
            {
                "@@type": "HowToStep",
                "position": 3,
                "name": "Crea tu primera parcela",
                "text": "Añade tus parcelas con códigos SIGPAC. Puedes importar desde SIGPAC o crear manualmente.",
                "url": "{{ route('content.que-es-sigpac') }}"
            },
            {
                "@@type": "HowToStep",
                "position": 4,
                "name": "Registra tu primera actividad",
                "text": "Comienza a usar el cuaderno digital registrando tratamientos, riegos o fertilizaciones."
            },
            {
                "@@type": "HowToStep",
                "position": 5,
                "name": "Genera tu primer informe",
                "text": "Crea informes oficiales con firma electrónica para cumplir con normativa PAC."
            }
        ]
    }
    </script>
    
    <!-- Preload critical resources -->
    <link rel="preload" href="{{ asset('images/logo.png') }}" as="image">
    <link rel="preload" href="{{ asset('images/dashboard-preview.png') }}" as="image" fetchpriority="high">
    
    <!-- Preconnect to external domains for performance -->
    <link rel="preconnect" href="https://www.google-analytics.com" crossorigin>
    <link rel="preconnect" href="https://www.googletagmanager.com" crossorigin>
    
    <!-- DNS Prefetch for faster loading -->
    <link rel="dns-prefetch" href="https://fonts.bunny.net">
    <link rel="dns-prefetch" href="https://www.google-analytics.com">
    <link rel="dns-prefetch" href="https://www.googletagmanager.com">
</head>
<body class="bg-gradient-to-br from-[var(--color-agro-green-bg)] via-white to-[var(--color-agro-green-bright)]/30 min-h-screen">
    
    <!-- Navigation Header -->
    <nav class="glass-card border-b border-gray-200/50 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Logo -->
                <a href="{{ url('/') }}" class="flex items-center" aria-label="Agro365 - Ir a inicio">
                    <img 
                        src="{{ asset('images/logo.png') }}" 
                        alt="Agro365 - Software de gestión agrícola para viñedos y bodegas" 
                        width="160"
                        height="80"
                        class="h-20 w-auto object-contain"
                        fetchpriority="high"
                        loading="eager"
                        decoding="async"
                    >
                </a>
                
                <!-- Auth Links -->
                <div class="flex items-center gap-4">
                    <a href="{{ route('login') }}" rel="nofollow" class="text-[var(--color-agro-green-dark)] hover:text-[var(--color-agro-green)] font-semibold transition-colors duration-300">
                        Iniciar Sesión
                    </a>
                    <a href="{{ route('register') }}" rel="nofollow" class="px-6 py-3 rounded-xl bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:from-[var(--color-agro-green)] hover:to-[var(--color-agro-green-dark)] transition-all duration-300 shadow-lg hover:shadow-xl font-semibold">
                        Comenzar Gratis
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative py-20 lg:py-32 overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <!-- Hero Content -->
                <div class="space-y-8 animate-fade-in">
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-[var(--color-agro-green-bg)] border border-[var(--color-agro-green-light)]/30">
                        <span class="w-2 h-2 rounded-full bg-[var(--color-agro-green-light)] animate-pulse"></span>
                        <span class="text-sm font-semibold text-[var(--color-agro-green-dark)]">🎉 6 meses GRATIS + 25% OFF de por vida (primeros 50)</span>
                    </div>
                    
                    <h1 class="text-5xl lg:text-6xl font-bold text-[var(--color-agro-green-dark)] leading-tight">
                        Software Profesional de Viticultura y Bodegas
                    </h1>
                    
                    <p class="text-xl text-gray-600 leading-relaxed">
                        <strong><a href="{{ route('content.cuaderno-digital-viticultores') }}" class="text-[var(--color-agro-green)] hover:underline">Cuaderno de campo digital</a></strong> obligatorio desde 2027, <strong><a href="{{ route('content.que-es-sigpac') }}" class="text-[var(--color-agro-green)] hover:underline">gestión de parcelas SIGPAC</a></strong>, control de actividades agrícolas, <strong>informes oficiales con firma electrónica</strong>, facturación de cosechas y cumplimiento normativo. Todo en una plataforma completa diseñada para <strong><a href="{{ route('content.software-viticultores') }}" class="text-[var(--color-agro-green)] hover:underline">viticultores profesionales</a></strong> en España.
                    </p>
                    
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="{{ route('register') }}" rel="nofollow" class="group inline-flex items-center justify-center gap-2 px-8 py-4 rounded-xl bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:from-[var(--color-agro-green)] hover:to-[var(--color-agro-green-dark)] transition-all duration-300 shadow-lg hover:shadow-xl font-semibold text-lg">
                            Comenzar Gratis
                            <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                        </a>
                        <a href="#features" class="inline-flex items-center justify-center gap-2 px-8 py-4 rounded-xl border-2 border-[var(--color-agro-green-dark)] text-[var(--color-agro-green-dark)] hover:bg-[var(--color-agro-green-dark)] hover:text-white transition-all duration-300 font-semibold text-lg">
                            Ver Características del Software Agrícola
                        </a>
                    </div>
                    
                    <div class="flex items-center gap-6 pt-4">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-[var(--color-agro-green)]" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-gray-700 font-medium">Sin tarjeta requerida</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-[var(--color-agro-green)]" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-gray-700 font-medium">Configuración en 5 minutos</span>
                        </div>
                    </div>
                </div>
                
                <!-- Hero Visual - Dashboard Preview -->
                <div class="relative lg:h-[600px] animate-scale-in">
                    <div class="relative h-full flex items-center justify-center">
                        <!-- Browser Mockup Frame -->
                        <div class="glass-card rounded-2xl overflow-hidden shadow-2xl hover-lift w-full max-w-3xl border-4 border-gray-200/50">
                            <!-- Browser Header -->
                            <div class="bg-gray-100 px-4 py-3 flex items-center gap-2 border-b border-gray-200">
                                <div class="flex gap-2">
                                    <div class="w-3 h-3 rounded-full bg-red-500"></div>
                                    <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                                    <div class="w-3 h-3 rounded-full bg-green-500"></div>
                                </div>
                                <div class="flex-1 mx-4">
                                    <div class="bg-white rounded px-3 py-1 text-xs text-gray-500 border border-gray-200">
                                        agro365.app/dashboard
                                    </div>
                                </div>
                            </div>
                            <!-- Dashboard Preview Image -->
                            <img 
                                src="{{ asset('images/dashboard-preview.png') }}" 
                                alt="Demo interactiva de Agro365: Dashboard, Cumplimiento PAC y Gestión Agrícola" 
                                class="w-full h-auto object-cover aspect-video"
                                loading="eager"
                                decoding="async"
                                fetchpriority="high"
                            >
                            <!-- Overlay distintivo -->
                            <div class="absolute bottom-4 right-4 bg-black/70 text-white px-3 py-1.5 rounded-full text-xs font-medium backdrop-blur-md flex items-center gap-2 pointer-events-none border border-white/10 z-10 shadow-lg">
                                <span class="relative flex h-2 w-2">
                                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                  <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                                </span>
                                Demo en Vivo
                            </div>
                        </div>
                        <!-- Decorative Elements -->
                        <div class="absolute -top-4 -right-4 w-24 h-24 rounded-2xl bg-gradient-to-br from-[var(--color-agro-green-light)] to-[var(--color-agro-green)] opacity-20 animate-pulse blur-xl"></div>
                        <div class="absolute -bottom-4 -left-4 w-20 h-20 rounded-full bg-gradient-to-br from-[var(--color-agro-yellow)] to-[var(--color-agro-brown)] opacity-20 animate-pulse blur-xl" style="animation-delay: 1s;"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ✅ SEO: Sección de soluciones comerciales directas -->
    <section class="py-20 bg-gray-50 border-y border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl lg:text-4xl font-bold text-[var(--color-agro-green-dark)]">
                    Soluciones que impulsan tu explotación
                </h2>
                <div class="w-20 h-1 bg-[var(--color-agro-green-light)] mx-auto mt-4 rounded-full"></div>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Card 1: Cuaderno Digital -->
                <a href="{{ route('content.cuaderno-digital') }}" class="group bg-white rounded-2xl p-6 shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 hover:border-[var(--color-agro-green-light)]/30">
                    <div class="w-14 h-14 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">Cuaderno de Campo</h3>
                    <p class="text-gray-600 text-sm leading-relaxed mb-4">Registro oficial de tratamientos, riegos y fertilización 100% conforme con la normativa 2027.</p>
                    <span class="text-[var(--color-agro-green)] text-sm font-semibold flex items-center gap-1 group-hover:translate-x-1 transition-transform">
                        Saber más
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                </a>

                <!-- Card 2: SIGPAC -->
                <a href="{{ route('content.que-es-sigpac') }}" class="group bg-white rounded-2xl p-6 shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 hover:border-[var(--color-agro-green-light)]/30">
                    <div class="w-14 h-14 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">Gestión SIGPAC</h3>
                    <p class="text-gray-600 text-sm leading-relaxed mb-4">Control de parcelas, mapas interactivos y códigos oficiales del ministerio integrados.</p>
                    <span class="text-[var(--color-agro-green)] text-sm font-semibold flex items-center gap-1 group-hover:translate-x-1 transition-transform">
                        Saber más
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                </a>

                <!-- Card 3: NDVI -->
                <a href="{{ route('content.ndvi-teledeteccion') }}" class="group bg-white rounded-2xl p-6 shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 hover:border-[var(--color-agro-green-light)]/30">
                    <div class="w-14 h-14 rounded-xl bg-purple-50 flex items-center justify-center text-purple-600 mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">Teledetección NDVI</h3>
                    <p class="text-gray-600 text-sm leading-relaxed mb-4">Análisis satelital de vigor y estrés hídrico de tus parcelas en tiempo real sin sensores.</p>
                    <span class="text-[var(--color-agro-green)] text-sm font-semibold flex items-center gap-1 group-hover:translate-x-1 transition-transform">
                        Saber más
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                </a>

                <!-- Card 4: Facturación -->
                <a href="{{ route('content.facturacion-agricola') }}" class="group bg-white rounded-2xl p-6 shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 hover:border-[var(--color-agro-green-light)]/30">
                    <div class="w-14 h-14 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600 mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">Facturación Agrícola</h3>
                    <p class="text-gray-600 text-sm leading-relaxed mb-4">De la vendimia a la factura en un clic. Gestión de entregas, cosechas y clientes integrada.</p>
                    <span class="text-[var(--color-agro-green)] text-sm font-semibold flex items-center gap-1 group-hover:translate-x-1 transition-transform">
                        Saber más
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                </a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-white/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 space-y-4">
                <h2 class="text-4xl lg:text-5xl font-bold text-[var(--color-agro-green-dark)]">
                    Software Agrícola Completo: Cuaderno Digital, SIGPAC e Informes Oficiales
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Herramientas profesionales para <strong><a href="{{ route('content.software-viticultores') }}" class="text-[var(--color-agro-green)] hover:underline">viticultores y bodegas</a></strong>: <a href="{{ route('content.cuaderno-digital-viticultores') }}" class="text-[var(--color-agro-green)] hover:underline">cuaderno de campo digital obligatorio</a>, gestión de parcelas SIGPAC, informes oficiales con firma electrónica, control de vendimia y facturación integrada.
                </p>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature 1: Cuaderno Digital (PRIORIDAD #1) -->
                <div class="glass-card rounded-xl p-8 hover-lift border-2 border-transparent hover:border-[var(--color-agro-green-light)]/50 transition-all duration-300">
                    <div class="w-16 h-16 rounded-xl bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center shadow-md mb-6">
                        <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-2xl text-[var(--color-agro-green-dark)] mb-3">Cuaderno de Campo Digital Obligatorio 2027</h3>
                    <p class="text-gray-600 leading-relaxed mb-4">
                        <strong><a href="{{ route('content.cuaderno-digital-viticultores') }}" class="text-[var(--color-agro-green)] hover:underline">Cuaderno de campo digital</a></strong> obligatorio desde 2027 según normativa europea. Registra tratamientos fitosanitarios, riegos, fertilizaciones y labores culturales desde cualquier lugar. Cumplimiento normativo garantizado para inspecciones PAC.
                    </p>
                    <ul class="space-y-2 text-sm text-gray-700">
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-[var(--color-agro-green)] flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>Listo para inspecciones en segundos</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-[var(--color-agro-green)] flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>Cumplimiento normativo garantizado</span>
                        </li>
                    </ul>
                </div>
                
                <!-- Feature 2: Gestión de Parcelas -->
                <div class="glass-card rounded-xl p-8 hover-lift border-2 border-transparent hover:border-[var(--color-agro-green-light)]/50 transition-all duration-300">
                    <div class="w-16 h-16 rounded-xl bg-gradient-to-br from-[var(--color-agro-green-light)] to-[var(--color-agro-green)] flex items-center justify-center shadow-md mb-6">
                        <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-2xl text-[var(--color-agro-green-dark)] mb-3">Gestión de Parcelas SIGPAC con Mapa Interactivo</h3>
                    <p class="text-gray-600 leading-relaxed mb-4">
                        <strong>Integración completa con SIGPAC</strong> (Sistema de Información Geográfica de Parcelas Agrícolas). Gestiona tus viñedos con códigos SIGPAC integrados, visualiza parcelas en mapa interactivo, controla variedades, hectáreas y cumple con normativa PAC.
                    </p>
                    <ul class="space-y-2 text-sm text-gray-700">
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-[var(--color-agro-green)] flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>Mapa interactivo con geometrías SIGPAC</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-[var(--color-agro-green)] flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>Cumplimiento PAC automático</span>
                        </li>
                    </ul>
                </div>
                
                <!-- Feature 3: Informes Oficiales & Cumplimiento PAC -->
                <div class="glass-card rounded-xl p-8 hover-lift border-2 border-transparent hover:border-[var(--color-agro-green-light)]/50 transition-all duration-300">
                    <div class="w-16 h-16 rounded-xl bg-gradient-to-br from-indigo-400 to-indigo-600 flex items-center justify-center shadow-md mb-6">
                        <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-2xl text-[var(--color-agro-green-dark)] mb-3">Informes Oficiales & Cumplimiento PAC</h3>
                    <p class="text-gray-600 leading-relaxed mb-4">
                        Genera <strong>7 tipos de informes oficiales</strong> certificados con <strong>firma electrónica SHA-256</strong> y código QR de verificación. <strong>Dashboard de cumplimiento PAC en tiempo real</strong> que detecta automáticamente errores, valida datos y te prepara para inspecciones. Cumple con normativa 2027.
                    </p>
                    <ul class="space-y-2 text-sm text-gray-700">
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-[var(--color-agro-green)] flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>Dashboard PAC con detección de errores</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-[var(--color-agro-green)] flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>Firma electrónica SHA-256 segura</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-[var(--color-agro-green)] flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>Validación automática de cumplimiento</span>
                        </li>
                    </ul>
                </div>
                
                <!-- Feature 4: Cosechas y Rendimientos -->
                <div class="glass-card rounded-xl p-8 hover-lift border-2 border-transparent hover:border-[var(--color-agro-green-light)]/50 transition-all duration-300">
                    <div class="w-16 h-16 rounded-xl bg-gradient-to-br from-amber-400 to-amber-600 flex items-center justify-center shadow-md mb-6">
                        <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-2xl text-[var(--color-agro-green-dark)] mb-3">Control de Vendimia y Gestión de Cosechas</h3>
                    <p class="text-gray-600 leading-relaxed mb-4">
                        <strong>Gestión completa de vendimia</strong>: controla toda tu cosecha desde la viña hasta la factura. Registra contenedores individuales, compara rendimientos reales vs estimados por parcela, analiza producción por variedad y genera facturación automática de cosechas.
                    </p>
                    <ul class="space-y-2 text-sm text-gray-700">
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-[var(--color-agro-green)] flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>Rendimiento por parcela y variedad</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-[var(--color-agro-green)] flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>De vendimia a factura en 1 click</span>
                        </li>
                    </ul>
                </div>
                
                <!-- Feature 5: Control de Actividades -->
                <div class="glass-card rounded-xl p-8 hover-lift border-2 border-transparent hover:border-[var(--color-agro-green-light)]/50 transition-all duration-300">
                    <div class="w-16 h-16 rounded-xl bg-gradient-to-br from-green-400 to-green-600 flex items-center justify-center shadow-md mb-6">
                        <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-2xl text-[var(--color-agro-green-dark)] mb-3">Control de Actividades</h3>
                    <p class="text-gray-600 leading-relaxed mb-4">
                        Registra podas, tratamientos, labores culturales y maquinaria utilizada. Histórico completo de cada viñedo.
                    </p>
                    <ul class="space-y-2 text-sm text-gray-700">
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-[var(--color-agro-green)] flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>Seguimiento de cuadrillas y equipos</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-[var(--color-agro-green)] flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>Costos reales por parcela</span>
                        </li>
                    </ul>
                </div>
                
                <!-- Feature 6: Facturación -->
                <div class="glass-card rounded-xl p-8 hover-lift border-2 border-transparent hover:border-[var(--color-agro-green-light)]/50 transition-all duration-300">
                    <div class="w-16 h-16 rounded-xl bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center shadow-md mb-6">
                        <svg class="w-9 h-9 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm4 18H6V4h7v5h5v11z"/>
                            <path d="M8 12h8v2H8zm0 4h8v2H8z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-2xl text-[var(--color-agro-green-dark)] mb-3">Facturación Integrada</h3>
                    <p class="text-gray-600 leading-relaxed mb-4">
                        Factura tus cosechas directamente desde la app. Gestión de clientes, control de pagos y cumplimiento fiscal simplificado.
                    </p>
                    <ul class="space-y-2 text-sm text-gray-700">
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-[var(--color-agro-green)] flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>Facturas desde vendimia registrada</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-[var(--color-agro-green)] flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>Control de pagos pendientes</span>
                        </li>
                    </ul>
                </div>

            </div>
        </div>
    </section>

    <!-- Solutions by Sector Section -->
    <section id="solutions" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 space-y-4">
                <h2 class="text-4xl lg:text-5xl font-bold text-[var(--color-agro-green-dark)]">
                    Soluciones Especializadas por Sector
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Software diseñado para las necesidades específicas de cada profesional del sector vitivinícola.
                </p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                <!-- Viticultores -->
                <a href="{{ route('content.viticultores') }}" class="group glass-card rounded-2xl p-8 hover-lift border-2 border-transparent hover:border-[var(--color-agro-green-light)]/30 transition-all duration-300">
                    <div class="w-14 h-14 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-xl text-[var(--color-agro-green-dark)] mb-2">Viticultores</h3>
                    <p class="text-gray-600 text-sm mb-4">Gestión de viñedos, variedad de uva, cuaderno digital obligatorio y control de vendimia.</p>
                    <span class="text-[var(--color-agro-green)] text-sm font-semibold flex items-center gap-1">
                        Saber más
                        <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </span>
                </a>

                <!-- Bodegas -->
                <a href="{{ route('content.bodegas') }}" class="group glass-card rounded-2xl p-8 hover-lift border-2 border-transparent hover:border-[var(--color-agro-green-light)]/30 transition-all duration-300">
                    <div class="w-14 h-14 rounded-xl bg-red-100 text-red-600 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.051.155A2 2 0 004 17.145V19a2 2 0 002 2h12a2 2 0 002-2v-1.572a2 2 0 00-.572-1.428z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11V3m0 0l-3 3m3-3l3 3"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-xl text-[var(--color-agro-green-dark)] mb-2">Bodegas</h3>
                    <p class="text-gray-600 text-sm mb-4">Trazabilidad desde la cepa, recepción de uva, rendimientos por DO e inventario.</p>
                    <span class="text-[var(--color-agro-green)] text-sm font-semibold flex items-center gap-1">
                        Saber más
                        <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </span>
                </a>

                <!-- Cooperativas -->
                <a href="{{ route('content.cooperativas') }}" class="group glass-card rounded-2xl p-8 hover-lift border-2 border-transparent hover:border-[var(--color-agro-green-light)]/30 transition-all duration-300">
                    <div class="w-14 h-14 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2m16-10a4 4 0 01-4 4H9a4 4 0 01-4-4V5a4 4 0 014-4h4a4 4 0 014 4v2z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-xl text-[var(--color-agro-green-dark)] mb-2">Cooperativas</h3>
                    <p class="text-gray-600 text-sm mb-4">Gestión de socios, centralización de cuadernos de campo y control de entregas masivas.</p>
                    <span class="text-[var(--color-agro-green)] text-sm font-semibold flex items-center gap-1">
                        Saber más
                        <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </span>
                </a>

                <!-- Ingenieros -->
                <a href="{{ route('content.ingenieros-agronomos') }}" class="group glass-card rounded-2xl p-8 hover-lift border-2 border-transparent hover:border-[var(--color-agro-green-light)]/30 transition-all duration-300">
                    <div class="w-14 h-14 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-xl text-[var(--color-agro-green-dark)] mb-2">Asesores e Ingenieros</h3>
                    <p class="text-gray-600 text-sm mb-4">Asesoramiento técnico, firma de informes oficiales y validación de tratamientos PAC.</p>
                    <span class="text-[var(--color-agro-green)] text-sm font-semibold flex items-center gap-1">
                        Saber más
                        <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </span>
                </a>
            </div>
        </div>
    </section>

    <!-- Benefits Section -->
    <section class="py-20 bg-gradient-to-br from-[var(--color-agro-green-bg)] to-white/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl lg:text-5xl font-bold text-[var(--color-agro-green-dark)] mb-4">
                    ¿Por qué elegir Agro365 para tu Viñedo?
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Miles de <strong><a href="{{ route('content.software-viticultores') }}" class="text-[var(--color-agro-green)] hover:underline">viticultores y bodegas</a></strong> en España confían en nuestro <strong><a href="{{ route('content.app-agricultura') }}" class="text-[var(--color-agro-green)] hover:underline">software agrícola profesional</a></strong> para gestionar sus explotaciones. Cumplimiento normativo, <a href="{{ route('content.cuaderno-digital-viticultores') }}" class="text-[var(--color-agro-green)] hover:underline">cuaderno digital</a> SIGPAC y control total de parcelas.
                </p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8">
                <!-- Beneficio 1 -->
                <div class="text-center glass-card rounded-xl p-8 hover-lift border-2 border-transparent hover:border-[var(--color-agro-green-light)]/50 transition-all duration-300">
                    <div class="w-20 h-20 rounded-full bg-gradient-to-br from-[var(--color-agro-green)] to-[var(--color-agro-green-light)] flex items-center justify-center mx-auto mb-4 shadow-lg">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-[var(--color-agro-green-dark)] mb-2">Ahorra Tiempo</h3>
                    <p class="text-gray-600">Reduce el tiempo de gestión administrativa en un 70%. Más tiempo para lo que realmente importa: tu viñedo.</p>
                </div>
                
                <!-- Beneficio 2 -->
                <div class="text-center glass-card rounded-xl p-8 hover-lift border-2 border-transparent hover:border-[var(--color-agro-green-light)]/50 transition-all duration-300">
                    <div class="w-20 h-20 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center mx-auto mb-4 shadow-lg">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-[var(--color-agro-green-dark)] mb-2">Cumplimiento Normativo</h3>
                    <p class="text-gray-600">Cumple automáticamente con todas las normativas vigentes. Sin preocupaciones, sin multas.</p>
                </div>
                
                <!-- Beneficio 3 -->
                <div class="text-center glass-card rounded-xl p-8 hover-lift border-2 border-transparent hover:border-[var(--color-agro-green-light)]/50 transition-all duration-300">
                    <div class="w-20 h-20 rounded-full bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center mx-auto mb-4 shadow-lg">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-[var(--color-agro-green-dark)] mb-2">Mejora Rentabilidad</h3>
                    <p class="text-gray-600">Controla ingresos, gastos y optimiza tu rentabilidad. Toma decisiones basadas en datos reales.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section class="py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 space-y-4">
                <div class="space-y-3">
                    <div class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-gradient-to-r from-[var(--color-agro-green)] to-[var(--color-agro-green-light)] text-white shadow-lg">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-base font-bold">6 MESES GRATIS para todos los beta testers</span>
                    </div>
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-amber-100 border border-amber-300 ml-4">
                        <svg class="w-5 h-5 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        <span class="text-sm font-semibold text-amber-800">+ Primeros 50 usuarios: 25% de descuento de por vida</span>
                    </div>
                </div>
                <h2 class="text-4xl lg:text-5xl font-bold text-[var(--color-agro-green-dark)]">
                    Comienza Gratis Hoy
                </h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                    Empieza con 6 meses completamente gratis. Si eres de los primeros 50, también obtienes 25% OFF permanente.
                </p>
            </div>
            
            <div class="grid md:grid-cols-2 gap-8 max-w-4xl mx-auto">
                <!-- Monthly Plan -->
                <div class="glass-card rounded-2xl p-8 hover-lift border-2 border-transparent hover:border-[var(--color-agro-green-light)] transition-all duration-300">
                    <div class="mb-6">
                        <h3 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mb-2">Plan Mensual</h3>
                        <p class="text-gray-600">Pago mes a mes, cancela cuando quieras</p>
                    </div>
                    
                    <div class="mb-8">
                        <div class="inline-block px-4 py-2 bg-gradient-to-r from-[var(--color-agro-green)] to-[var(--color-agro-green-light)] text-white rounded-lg font-bold text-lg mb-4">
                            6 MESES GRATIS
                        </div>
                        
                        <div class="flex items-end gap-2 mb-2">
                            <span class="text-2xl text-gray-500">Después:</span>
                            <span class="text-5xl font-bold text-[var(--color-agro-green-dark)]">€9</span>
                            <span class="text-gray-500 mb-2">/mes</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-gray-400 line-through">€12/mes</span>
                            <span class="px-3 py-1 rounded-full bg-[var(--color-agro-yellow-light)] text-[var(--color-agro-brown)] text-sm font-semibold">
                                25% OFF
                            </span>
                        </div>
                        <p class="text-sm font-semibold text-[var(--color-agro-green)] mt-2">⚡ Solo para los primeros 50 usuarios</p>
                        <p class="text-xs text-gray-500 mt-1">Descuento bloqueado de por vida</p>
                    </div>
                    
                    <ul class="space-y-4 mb-8">
                        <li class="flex items-start gap-3">
                            <svg class="w-6 h-6 text-[var(--color-agro-green)] flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-gray-700">Parcelas ilimitadas</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-6 h-6 text-[var(--color-agro-green)] flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-gray-700">Cuaderno de campo digital</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-6 h-6 text-[var(--color-agro-green)] flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-gray-700">Integración SIGPAC</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-6 h-6 text-[var(--color-agro-green)] flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-gray-700">Soporte por email</span>
                        </li>
                    </ul>
                    
                    <a href="{{ route('register') }}" rel="nofollow" class="block w-full text-center px-6 py-4 rounded-xl border-2 border-[var(--color-agro-green-dark)] text-[var(--color-agro-green-dark)] hover:bg-[var(--color-agro-green-dark)] hover:text-white transition-all duration-300 font-semibold text-lg">
                        Empezar Ahora
                    </a>
                </div>
                
                <!-- Yearly Plan (Recommended) -->
                <div class="glass-card rounded-2xl p-8 hover-lift border-2 border-[var(--color-agro-green)] relative overflow-hidden transition-all duration-300 shadow-xl">
                    <!-- Recommended Badge -->
                    <div class="absolute top-0 right-0">
                        <div class="bg-gradient-to-r from-[var(--color-agro-green)] to-[var(--color-agro-green-light)] text-white px-6 py-2 rounded-bl-2xl font-semibold">
                            Recomendado
                        </div>
                    </div>
                    
                    <div class="mb-6 pt-8">
                        <h3 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mb-2">Plan Anual</h3>
                        <p class="text-gray-600">Ahorra €18 al año (2 meses gratis)</p>
                    </div>
                    
                    <div class="mb-8">
                        <div class="inline-block px-4 py-2 bg-gradient-to-r from-[var(--color-agro-green)] to-[var(--color-agro-green-light)] text-white rounded-lg font-bold text-lg mb-4">
                            6 MESES GRATIS
                        </div>
                        
                        <div class="flex items-end gap-2 mb-2">
                            <span class="text-2xl text-gray-500">Después:</span>
                            <span class="text-5xl font-bold text-[var(--color-agro-green-dark)]">€90</span>
                            <span class="text-gray-500 mb-2">/año</span>
                        </div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-gray-400 line-through">€120/año</span>
                            <span class="px-3 py-1 rounded-full bg-[var(--color-agro-yellow-light)] text-[var(--color-agro-brown)] text-sm font-semibold">
                                25% OFF
                            </span>
                        </div>
                        <p class="text-sm font-semibold text-[var(--color-agro-green)]">Equivale a €7.50/mes</p>
                        <p class="text-sm font-semibold text-[var(--color-agro-green)] mt-1">⚡ Solo para los primeros 50 usuarios</p>
                        <p class="text-xs text-gray-500 mt-1">Descuento bloqueado de por vida</p>
                    </div>
                    
                    <ul class="space-y-4 mb-8">
                        <li class="flex items-start gap-3">
                            <svg class="w-6 h-6 text-[var(--color-agro-green)] flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-gray-700 font-semibold">Todo del plan mensual</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-6 h-6 text-[var(--color-agro-green)] flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-gray-700 font-semibold">Ahorra €12 al año</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-6 h-6 text-[var(--color-agro-green)] flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-gray-700">Soporte prioritario</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-6 h-6 text-[var(--color-agro-green)] flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-gray-700">Nuevas funciones primero</span>
                        </li>
                    </ul>
                    
                    <a href="{{ route('register') }}" class="block w-full text-center px-6 py-4 rounded-xl bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:from-[var(--color-agro-green)] hover:to-[var(--color-agro-green-dark)] transition-all duration-300 shadow-lg hover:shadow-xl font-semibold text-lg">
                        Empezar Ahora
                    </a>
                </div>
            </div>
            
            <p class="text-center text-gray-500 mt-8 text-lg">
                🎁 <span class="font-semibold text-gray-700">6 meses completamente gratis, sin tarjeta requerida.</span> Cancela en cualquier momento.
            </p>
        </div>
    </section>


    @include('partials.footer-seo')

    <!-- Schema.org LocalBusiness for Footer -->
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "SoftwareApplication",
        "name": "Agro365",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": "Web, iOS, Android",
        "offers": {
            "@@type": "Offer",
            "price": "0",
            "priceCurrency": "EUR",
            "description": "6 meses gratis para beta testers"
        },
        "aggregateRating": {
            "@@type": "AggregateRating",
            "ratingValue": "4.8",
            "ratingCount": "150"
        },
        "provider": {
            "@@type": "Organization",
            "name": "Agro365",
            "url": "{{ url('/') }}",
            "logo": "{{ asset('images/logo.png') }}",
            "contactPoint": {
                "@@type": "ContactPoint",
                "telephone": "+34-XXX-XXX-XXX",
                "contactType": "customer service",
                "email": "info@agro365.es",
                "areaServed": "ES",
                "availableLanguage": ["Spanish"]
            },
            "address": {
                "@@type": "PostalAddress",
                "addressCountry": "ES"
            }
        }
    }
    </script>
</body>
</html>

 
 