<!DOCTYPE html>
<html lang="es" itemscope itemtype="https://schema.org/WebSite">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, user-scalable=yes">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <!-- SEO Meta Tags -->
    <title>Agro365 | Cuaderno de Campo Digital Obligatorio 2027 · Software Viticultura y Bodegas</title>
    <meta name="description" content="Cuaderno de campo digital obligatorio desde 2027. Software de gestión para viticultores, bodegas y Denominaciones de Origen. Cumplimiento PAC, SIGPAC y trazabilidad completa. Viticultor básico gratis, completo desde 9€/mes.">
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
    <meta property="og:title" content="Agro365 | Cuaderno de Campo Digital Obligatorio 2027">
    <meta property="og:description" content="La plataforma que conecta viticultores, bodegas y Denominaciones de Origen. Cuaderno digital obligatorio 2027, gestión SIGPAC y trazabilidad completa.">
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
    <meta name="twitter:title" content="Agro365 | Cuaderno de Campo Digital Obligatorio 2027">
    <meta name="twitter:description" content="La plataforma que conecta viticultores, bodegas y Denominaciones de Origen. Viticultor básico gratis, completo desde 9€/mes.">
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
                    'name' => 'Viticultor completo (invitado por bodega)',
                    'price' => '9.00',
                    'priceCurrency' => 'EUR',
                    'availability' => 'https://schema.org/InStock',
                    'description' => 'Plan completo para viticultor invitado: SIGPAC, teledetección, PAC, facturación'
                ],
                [
                    '@type' => 'Offer',
                    'name' => 'Viticultor independiente — Mensual',
                    'price' => '14.00',
                    'priceCurrency' => 'EUR',
                    'availability' => 'https://schema.org/InStock',
                    'description' => 'Plan completo para viticultor independiente sin bodega asociada'
                ],
                [
                    '@type' => 'Offer',
                    'name' => 'Bodega independiente — Mensual',
                    'price' => '14.00',
                    'priceCurrency' => 'EUR',
                    'availability' => 'https://schema.org/InStock',
                    'description' => 'Gestión completa de bodega sin Denominación de Origen'
                ]
            ],
            'description' => 'Software de gestión agrícola para viticultores, bodegas y Denominaciones de Origen. Cuaderno de campo digital obligatorio 2027.',
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
            'priceRange' => '€0-€14',
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
                "text": "Crea tu cuenta en Agro365. No se requiere tarjeta de crédito. El uso básico es completamente gratis.",
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
                "url": "{{ content_route('content.que-es-sigpac') }}"
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
                
                <!-- Nav links + Auth -->
                <div class="flex items-center gap-6">

                    <a href="{{ route('login') }}" rel="nofollow" class="text-[var(--color-agro-green-dark)] hover:text-[var(--color-agro-green)] font-semibold transition-colors duration-300 text-sm">
                        Iniciar Sesión
                    </a>
                    <a href="{{ route('register') }}" rel="nofollow" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:from-[var(--color-agro-green)] hover:to-[var(--color-agro-green-dark)] transition-all duration-300 shadow-lg hover:shadow-xl font-semibold text-sm">
                        Comenzar Gratis →
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
                    <div class="flex flex-wrap gap-2">
                        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-[var(--color-agro-green-bg)] border border-[var(--color-agro-green-light)]/30">
                            <span class="w-2 h-2 rounded-full bg-[var(--color-agro-green-light)] animate-pulse"></span>
                            <span class="text-sm font-semibold text-[var(--color-agro-green-dark)]">Viticultor básico gratis · Completo desde 9€/mes</span>
                        </div>
                        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-amber-50 border border-amber-300">
                            <svg class="w-3.5 h-3.5 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-sm font-semibold text-amber-800">⚡ Obligatorio en <span id="days-counter">{{ now()->lt('2027-01-01') ? (int) now()->startOfDay()->diffInDays('2027-01-01') : 0 }}</span> días</span>
                        </div>
                    </div>
                    
                    <h1 class="text-5xl lg:text-6xl font-bold text-[var(--color-agro-green-dark)] leading-tight">
                        <span class="block">Cuaderno de Campo Digital</span>
                        <span class="block text-3xl lg:text-4xl text-[var(--color-agro-green)] mt-2">Fitosanitarios obligatorios en 2027 · Cuaderno completo en 2028</span>
                    </h1>

                    <p class="text-xl text-gray-600 leading-relaxed">
                        La plataforma que conecta <strong>viticultores</strong>, <strong>bodegas</strong> y <strong>Denominaciones de Origen</strong>.
                        Lleva tu cuaderno de campo digital, cumple con la normativa <a href="{{ content_route('content.normativa-pac') }}" class="text-[var(--color-agro-green)] hover:underline">PAC</a>,
                        gestiona tus parcelas <a href="{{ content_route('content.que-es-sigpac') }}" class="text-[var(--color-agro-green)] hover:underline">SIGPAC</a>
                        y lleva la trazabilidad completa desde el viñedo hasta la bodega.
                    </p>
                    
                    <!-- 3 CTAs por rol -->
                    <div class="flex flex-col sm:flex-row gap-3">
                        <a href="{{ route('register') }}" rel="nofollow" class="group inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:from-[var(--color-agro-green)] hover:to-[var(--color-agro-green-dark)] transition-all duration-300 shadow-lg hover:shadow-xl font-semibold text-base">
                            <span>🌿</span> Soy Viticultor
                            <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                        </a>
                        <a href="{{ content_route('content.bodegas') }}" class="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl border-2 border-[var(--color-agro-green-dark)] text-[var(--color-agro-green-dark)] hover:bg-[var(--color-agro-green-dark)] hover:text-white transition-all duration-300 font-semibold text-base">
                            <span>🍷</span> Soy Bodega
                        </a>
                        <a href="#ecosistema" class="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl border-2 border-[var(--color-agro-green-light)]/60 text-[var(--color-agro-green-dark)] hover:border-[var(--color-agro-green-dark)] transition-all duration-300 font-semibold text-base">
                            <span>🏛️</span> Soy DO
                        </a>
                    </div>

                    <div class="flex flex-wrap items-center gap-4 pt-2">
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-[var(--color-agro-green)]" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-gray-600 text-sm font-medium">Básico gratis vía bodega</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-[var(--color-agro-green)]" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-gray-600 text-sm font-medium">Completo 9€</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-[var(--color-agro-green)]" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-gray-600 text-sm font-medium">Independiente 14€</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-[var(--color-agro-green)]" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-gray-600 text-sm font-medium">Sin tarjeta</span>
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

    <!-- Ecosistema Conectado -->
    <section id="ecosistema" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 space-y-4">
                <h2 class="text-4xl lg:text-5xl font-bold text-[var(--color-agro-green-dark)]">
                    Una sola plataforma. Tres roles. Todo conectado.
                </h2>
                <p class="text-xl text-zinc-600 max-w-3xl mx-auto">
                    Agro365 es la plataforma española que conecta toda la cadena del vino en un ecosistema compartido.
                    Cada actor ve solo lo que le corresponde. Los datos fluyen solos.
                    <strong>Sin Excel. Sin papel. Sin duplicidades.</strong>
                </p>
            </div>

            <!-- Diagrama visual del ecosistema -->
            <div class="flex justify-center mb-16">
                <div class="relative max-w-2xl w-full">
                    <!-- DO en la cima -->
                    <div class="flex justify-center mb-6">
                        <div class="bg-gradient-to-br from-amber-50 to-amber-100 border-2 border-amber-300 rounded-2xl p-6 text-center w-72 shadow-md">
                            <div class="text-3xl mb-2">🏛️</div>
                            <div class="font-bold text-amber-900 text-lg">Denominación de Origen</div>
                            <div class="text-amber-700 text-sm mt-1">Supervisa bodegas y cumplimiento normativo</div>
                        </div>
                    </div>
                    <!-- Línea DO → Bodegas -->
                    <div class="flex justify-center mb-1">
                        <div class="w-px h-8 bg-zinc-300"></div>
                    </div>
                    <div class="flex justify-center gap-2 mb-1">
                        <div class="flex-1 max-w-[200px] h-px bg-zinc-300 self-center"></div>
                        <div class="w-px h-0 self-center"></div>
                        <div class="flex-1 max-w-[200px] h-px bg-zinc-300 self-center"></div>
                    </div>
                    <!-- Bodegas -->
                    <div class="flex justify-center gap-6 mb-1">
                        <div class="flex flex-col items-center">
                            <div class="w-px h-6 bg-zinc-300"></div>
                        </div>
                        <div class="flex flex-col items-center">
                            <div class="w-px h-6 bg-zinc-300"></div>
                        </div>
                    </div>
                    <div class="flex justify-center gap-6 mb-6">
                        <div class="bg-gradient-to-br from-red-50 to-red-100 border-2 border-red-300 rounded-2xl p-4 text-center w-44 shadow-md">
                            <div class="text-2xl mb-1">🍷</div>
                            <div class="font-bold text-red-900">Bodega</div>
                            <div class="text-red-700 text-xs mt-1">Gestión propia</div>
                        </div>
                        <div class="bg-gradient-to-br from-red-50 to-red-100 border-2 border-red-300 rounded-2xl p-4 text-center w-44 shadow-md">
                            <div class="text-2xl mb-1">🍷</div>
                            <div class="font-bold text-red-900">Bodega</div>
                            <div class="text-red-700 text-xs mt-1">Gestión propia</div>
                        </div>
                    </div>
                    <!-- Líneas Bodegas → Viticultores -->
                    <div class="flex justify-center gap-6 mb-1">
                        <div class="flex gap-3 justify-center w-44">
                            <div class="w-px h-6 bg-zinc-300"></div>
                            <div class="w-px h-6 bg-zinc-300"></div>
                        </div>
                        <div class="flex gap-3 justify-center w-44">
                            <div class="w-px h-6 bg-zinc-300"></div>
                            <div class="w-px h-6 bg-zinc-300"></div>
                        </div>
                    </div>
                    <!-- Viticultores -->
                    <div class="flex justify-center gap-3 flex-wrap">
                        <div class="bg-gradient-to-br from-[var(--color-agro-green-bg)] to-green-100 border-2 border-[var(--color-agro-green-light)]/60 rounded-xl p-3 text-center w-28 shadow-sm">
                            <div class="text-xl mb-1">🌿</div>
                            <div class="font-semibold text-[var(--color-agro-green-dark)] text-sm">Viticultor</div>
                        </div>
                        <div class="bg-gradient-to-br from-[var(--color-agro-green-bg)] to-green-100 border-2 border-[var(--color-agro-green-light)]/60 rounded-xl p-3 text-center w-28 shadow-sm">
                            <div class="text-xl mb-1">🌿</div>
                            <div class="font-semibold text-[var(--color-agro-green-dark)] text-sm">Viticultor</div>
                        </div>
                        <div class="bg-gradient-to-br from-[var(--color-agro-green-bg)] to-green-100 border-2 border-[var(--color-agro-green-light)]/60 rounded-xl p-3 text-center w-28 shadow-sm">
                            <div class="text-xl mb-1">🌿</div>
                            <div class="font-semibold text-[var(--color-agro-green-dark)] text-sm">Viticultor</div>
                        </div>
                        <div class="bg-gradient-to-br from-[var(--color-agro-green-bg)] to-green-100 border-2 border-[var(--color-agro-green-light)]/60 rounded-xl p-3 text-center w-28 shadow-sm">
                            <div class="text-xl mb-1">🌿</div>
                            <div class="font-semibold text-[var(--color-agro-green-dark)] text-sm">Viticultor</div>
                        </div>
                        <div class="bg-gradient-to-br from-blue-50 to-blue-100 border-2 border-blue-200 rounded-xl p-3 text-center w-28 shadow-sm">
                            <div class="text-xl mb-1">🌿</div>
                            <div class="font-semibold text-blue-700 text-xs">Viticultor independiente</div>
                        </div>
                    </div>
                    <p class="text-center text-xs text-zinc-400 mt-3">El viticultor independiente no necesita bodega ni DO para empezar</p>
                </div>
            </div>

            <!-- Tres cards de rol -->
            <div class="grid md:grid-cols-3 gap-8">
                <!-- Viticultor -->
                <div class="glass-card rounded-2xl p-8 border-2 border-[var(--color-agro-green-light)]/30 hover:border-[var(--color-agro-green-light)] hover-lift transition-all duration-300">
                    <div class="w-14 h-14 rounded-xl bg-[var(--color-agro-green-bg)] flex items-center justify-center mb-5 text-3xl">
                        🌿
                    </div>
                    <h3 class="text-xl font-bold text-[var(--color-agro-green-dark)] mb-3">Viticultor</h3>
                    <p class="text-zinc-600 text-sm mb-2 font-semibold">Tu cuaderno, tus datos, siempre tuyos</p>
                    <p class="text-zinc-600 text-sm mb-5">
                        Registra tratamientos, riegos y labores desde el móvil.
                        Trabaja de forma independiente o comparte tu cuaderno con una o varias bodegas.
                        <strong>Aunque cambies de bodega, tus datos siguen siendo tuyos.</strong>
                    </p>
                    <a href="{{ route('register') }}" rel="nofollow" class="inline-flex items-center gap-1.5 text-[var(--color-agro-green)] font-semibold text-sm hover:underline">
                        Empezar gratis — 3 meses sin coste
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>

                <!-- Bodega -->
                <div class="glass-card rounded-2xl p-8 border-2 border-red-200/50 hover:border-red-300 hover-lift transition-all duration-300">
                    <div class="w-14 h-14 rounded-xl bg-red-50 flex items-center justify-center mb-5 text-3xl">
                        🍷
                    </div>
                    <h3 class="text-xl font-bold text-[var(--color-agro-green-dark)] mb-3">Bodega</h3>
                    <p class="text-zinc-600 text-sm mb-2 font-semibold">Trazabilidad completa desde la cepa</p>
                    <p class="text-zinc-600 text-sm mb-5">
                        Conecta con tus viticultores y recibe sus cuadernos en tiempo real. Gestiona vendimia,
                        producciones y facturación. <strong>Invita a tus viticultores</strong> — acceden en modo básico gratis
                        o completo por 9€/mes.
                    </p>
                    <a href="{{ content_route('content.bodegas') }}" class="inline-flex items-center gap-1.5 text-red-600 font-semibold text-sm hover:underline">
                        Empezar gratis — 3 meses sin coste
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>

                <!-- DO -->
                <div class="glass-card rounded-2xl p-8 border-2 border-amber-200/50 hover:border-amber-300 hover-lift transition-all duration-300">
                    <div class="w-14 h-14 rounded-xl bg-amber-50 flex items-center justify-center mb-5 text-3xl">
                        🏛️
                    </div>
                    <h3 class="text-xl font-bold text-[var(--color-agro-green-dark)] mb-3">Denominación de Origen</h3>
                    <p class="text-zinc-600 text-sm mb-2 font-semibold">Control total de tu ecosistema</p>
                    <p class="text-zinc-600 text-sm mb-5">
                        Registra tus bodegas adscritas y supervisa su actividad y cumplimiento normativo
                        desde un panel centralizado. <strong>Tus bodegas acceden gratis incluidas en tu plan.</strong>
                        Solo pagas tú por toda la red.
                    </p>
                    <div class="flex flex-col gap-2">
                        <a href="https://wa.me/34684217167?text=Hola%2C%20soy%20una%20Denominaci%C3%B3n%20de%20Origen%20y%20me%20interesa%20Agro365" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 text-amber-700 font-semibold text-sm hover:underline">
                            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                            Hablar por WhatsApp
                        </a>
                        <a href="mailto:info@agro365.es?subject=Consulta%20Denominaci%C3%B3n%20de%20Origen%20Agro365" class="inline-flex items-center gap-1.5 text-amber-600 font-medium text-sm hover:underline">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            Enviar un email
                        </a>
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
                    Todo lo que incluye el plan Viticultor
                </h2>
                <div class="w-20 h-1 bg-[var(--color-agro-green-light)] mx-auto mt-4 rounded-full"></div>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Card 1: Cuaderno Digital -->
                <a href="{{ content_route('content.cuaderno-digital') }}" class="group bg-white rounded-2xl p-6 shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 hover:border-[var(--color-agro-green-light)]/30">
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
                <a href="{{ content_route('content.que-es-sigpac') }}" class="group bg-white rounded-2xl p-6 shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 hover:border-[var(--color-agro-green-light)]/30">
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
                <a href="{{ content_route('content.ndvi-teledeteccion') }}" class="group bg-white rounded-2xl p-6 shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 hover:border-[var(--color-agro-green-light)]/30">
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
                <a href="{{ content_route('content.facturacion-agricola') }}" class="group bg-white rounded-2xl p-6 shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 hover:border-[var(--color-agro-green-light)]/30">
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

                <!-- Card 5: Vendimia -->
                <a href="{{ content_route('content.gestion-vendimia') }}" class="group bg-white rounded-2xl p-6 shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 hover:border-[var(--color-agro-green-light)]/30">
                    <div class="w-14 h-14 rounded-xl bg-red-50 flex items-center justify-center text-red-600 mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">Gestión de Vendimia</h3>
                    <p class="text-gray-600 text-sm leading-relaxed mb-4">Control de cosecha por parcela y viticultor, pesaje, calidades y entrega directa a bodega.</p>
                    <span class="text-[var(--color-agro-green)] text-sm font-semibold flex items-center gap-1 group-hover:translate-x-1 transition-transform">
                        Saber más
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                </a>

                <!-- Card 6: Trazabilidad -->
                <a href="{{ content_route('content.trazabilidad-agricola') }}" class="group bg-white rounded-2xl p-6 shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 hover:border-[var(--color-agro-green-light)]/30">
                    <div class="w-14 h-14 rounded-xl bg-teal-50 flex items-center justify-center text-teal-600 mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">Trazabilidad Completa</h3>
                    <p class="text-gray-600 text-sm leading-relaxed mb-4">Desde la parcela hasta la botella. Cada lote sabe de dónde viene. Auditorías y DO en segundos.</p>
                    <span class="text-[var(--color-agro-green)] text-sm font-semibold flex items-center gap-1 group-hover:translate-x-1 transition-transform">
                        Saber más
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                </a>
            </div>
        </div>
    </section>

    <!-- Bodega & DO Bridge Section -->
    <section class="py-16 bg-gradient-to-b from-zinc-50 to-white border-t border-zinc-100">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <p class="text-center text-zinc-400 text-sm font-semibold uppercase tracking-widest mb-8">¿No eres viticultor?</p>
            <div class="grid md:grid-cols-2 gap-6">

                <!-- Bodega -->
                <div class="bg-white rounded-2xl p-8 border-2 border-red-100 hover:border-red-300 transition-all duration-300 shadow-sm">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="text-3xl">🍷</span>
                        <div>
                            <h3 class="font-bold text-xl text-[var(--color-agro-green-dark)]">¿Eres una bodega?</h3>
                            <p class="text-xs text-red-600 font-semibold">Gratis si estás en una DO · 14€/mes independiente</p>
                        </div>
                    </div>
                    <ul class="space-y-2 text-sm text-zinc-600 mb-6">
                        <li class="flex items-center gap-2"><span class="text-red-400">✓</span> Recepción de uva y control de pesaje</li>
                        <li class="flex items-center gap-2"><span class="text-red-400">✓</span> Gestión de depósitos y pipeline de elaboración</li>
                        <li class="flex items-center gap-2"><span class="text-red-400">✓</span> AICA, INFOVI y libros de bodega automáticos</li>
                        <li class="flex items-center gap-2"><span class="text-red-400">✓</span> Facturación + Verifactu integrado</li>
                        <li class="flex items-center gap-2"><span class="text-red-400">✓</span> Panel de viticultores proveedores en tiempo real</li>
                        <li class="flex items-center gap-2"><span class="text-red-400">✓</span> Teledetección NDVI de las parcelas de tus proveedores</li>
                    </ul>
                    <a href="{{ url('/software-bodegas') }}" class="inline-flex items-center gap-2 text-red-600 font-semibold text-sm hover:underline">
                        Ver todo lo que incluye para bodegas
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>

                <!-- DO -->
                <div class="bg-white rounded-2xl p-8 border-2 border-amber-100 hover:border-amber-300 transition-all duration-300 shadow-sm">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="text-3xl">🏛️</span>
                        <div>
                            <h3 class="font-bold text-xl text-[var(--color-agro-green-dark)]">¿Gestionas una DO?</h3>
                            <p class="text-xs text-amber-700 font-semibold">Desde 149€/mes · Todas tus bodegas incluidas gratis</p>
                        </div>
                    </div>
                    <ul class="space-y-2 text-sm text-zinc-600 mb-6">
                        <li class="flex items-center gap-2"><span class="text-amber-500">✓</span> Panel centralizado de todas tus bodegas adscritas</li>
                        <li class="flex items-center gap-2"><span class="text-amber-500">✓</span> Alertas automáticas de incumplimiento normativo</li>
                        <li class="flex items-center gap-2"><span class="text-amber-500">✓</span> Informes consolidados por denominación</li>
                        <li class="flex items-center gap-2"><span class="text-amber-500">✓</span> Firma electrónica SHA-256 en todos los documentos</li>
                        <li class="flex items-center gap-2"><span class="text-amber-500">✓</span> Tus bodegas acceden gratis — solo pagas tú</li>
                        <li class="flex items-center gap-2"><span class="text-amber-500">✓</span> Account manager dedicado + SLA 99,9%</li>
                    </ul>
                    <div class="flex items-center gap-4">
                        <a href="https://wa.me/34684217167?text=Hola%2C%20soy%20una%20Denominaci%C3%B3n%20de%20Origen%20y%20me%20interesa%20Agro365" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 text-amber-700 font-semibold text-sm hover:underline">
                            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                            Hablar por WhatsApp
                        </a>
                        <a href="mailto:info@agro365.es?subject=Consulta%20Denominaci%C3%B3n%20de%20Origen%20Agro365" class="text-amber-600 text-sm hover:underline">o enviar email</a>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Solutions by Role Section -->
    <section id="solutions" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12 space-y-4">
                <h2 class="text-4xl lg:text-5xl font-bold text-[var(--color-agro-green-dark)]">
                    Diseñado para los tres roles del vino
                </h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                    Un ecosistema conectado. Cada rol ve lo que necesita, cuando lo necesita.
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <!-- Viticultor -->
                <a href="{{ content_route('content.viticultores') }}" class="group glass-card rounded-2xl p-8 hover-lift border-2 border-[var(--color-agro-green-light)]/30 hover:border-[var(--color-agro-green-light)] transition-all duration-300">
                    <div class="text-4xl mb-5">🌿</div>
                    <h3 class="font-bold text-xl text-[var(--color-agro-green-dark)] mb-2">Viticultor</h3>
                    <p class="text-zinc-500 text-xs font-semibold mb-3 uppercase tracking-wide">Básico gratis · Completo 9€ · Independiente 14€</p>
                    <p class="text-gray-600 text-sm mb-5 leading-relaxed">Cuaderno de campo digital, SIGPAC, teledetección NDVI, PAC, vendimia y facturación. Tus datos son siempre tuyos aunque cambies de bodega.</p>
                    <ul class="space-y-1.5 text-xs text-zinc-500 mb-5">
                        <li class="flex items-center gap-1.5"><span class="text-[var(--color-agro-green)]">✓</span> Cuaderno obligatorio 2027</li>
                        <li class="flex items-center gap-1.5"><span class="text-[var(--color-agro-green)]">✓</span> SIGPAC y parcelas</li>
                        <li class="flex items-center gap-1.5"><span class="text-[var(--color-agro-green)]">✓</span> Teledetección satelital</li>
                        <li class="flex items-center gap-1.5"><span class="text-[var(--color-agro-green)]">✓</span> Facturación + Verifactu</li>
                    </ul>
                    <span class="text-[var(--color-agro-green)] text-sm font-semibold flex items-center gap-1 group-hover:translate-x-1 transition-transform">
                        Ver más sobre Viticultores
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </span>
                </a>

                <!-- Bodega -->
                <a href="{{ url('/software-bodegas') }}" class="group glass-card rounded-2xl p-8 hover-lift border-2 border-red-200/50 hover:border-red-300 transition-all duration-300">
                    <div class="text-4xl mb-5">🍷</div>
                    <h3 class="font-bold text-xl text-[var(--color-agro-green-dark)] mb-2">Bodega</h3>
                    <p class="text-zinc-500 text-xs font-semibold mb-3 uppercase tracking-wide">Gratis en DO · 14€/mes independiente</p>
                    <p class="text-gray-600 text-sm mb-5 leading-relaxed">Recepción de uva, depósitos, elaboración, aditivos, informes AICA/INFOVI y facturación. Conecta con tus viticultores proveedores en un clic.</p>
                    <ul class="space-y-1.5 text-xs text-zinc-500 mb-5">
                        <li class="flex items-center gap-1.5"><span class="text-red-500">✓</span> Recepción y pesaje</li>
                        <li class="flex items-center gap-1.5"><span class="text-red-500">✓</span> Gestión de depósitos</li>
                        <li class="flex items-center gap-1.5"><span class="text-red-500">✓</span> AICA, INFOVI, libros de bodega</li>
                        <li class="flex items-center gap-1.5"><span class="text-red-500">✓</span> Panel de viticultores proveedores</li>
                    </ul>
                    <span class="text-red-600 text-sm font-semibold flex items-center gap-1 group-hover:translate-x-1 transition-transform">
                        Ver más sobre Bodegas
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </span>
                </a>

                <!-- DO -->
                <a href="#precios" class="group glass-card rounded-2xl p-8 hover-lift border-2 border-amber-200/50 hover:border-amber-300 transition-all duration-300">
                    <div class="text-4xl mb-5">🏛️</div>
                    <h3 class="font-bold text-xl text-[var(--color-agro-green-dark)] mb-2">Denominación de Origen</h3>
                    <p class="text-zinc-500 text-xs font-semibold mb-3 uppercase tracking-wide">Desde 149€/mes · Bodegas incluidas gratis</p>
                    <p class="text-gray-600 text-sm mb-5 leading-relaxed">Panel centralizado de supervisión. Registra bodegas adscritas, controla cumplimiento normativo y accede a informes consolidados de toda tu red. Tus bodegas acceden sin coste adicional.</p>
                    <ul class="space-y-1.5 text-xs text-zinc-500 mb-5">
                        <li class="flex items-center gap-1.5"><span class="text-amber-600">✓</span> Panel de supervisión centralizado</li>
                        <li class="flex items-center gap-1.5"><span class="text-amber-600">✓</span> Alertas de incumplimiento</li>
                        <li class="flex items-center gap-1.5"><span class="text-amber-600">✓</span> Informes consolidados DO</li>
                        <li class="flex items-center gap-1.5"><span class="text-amber-600">✓</span> Account manager dedicado</li>
                    </ul>
                    <span class="text-amber-700 text-sm font-semibold flex items-center gap-1 group-hover:translate-x-1 transition-transform">
                        Ver precios para DO
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
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
                    ¿Por qué elegir Agro365?
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Diseñado para <strong><a href="{{ content_route('content.viticultores') }}" class="text-[var(--color-agro-green)] hover:underline">viticultores</a></strong>,
                    <strong><a href="{{ content_route('content.bodegas') }}" class="text-[var(--color-agro-green)] hover:underline">bodegas</a></strong>
                    y Denominaciones de Origen en España. Cumplimiento normativo PAC,
                    <a href="{{ content_route('content.cuaderno-digital-viticultores') }}" class="text-[var(--color-agro-green)] hover:underline">cuaderno de campo digital</a>
                    obligatorio 2027 y trazabilidad completa desde el viñedo hasta la botella.
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
    <section id="precios" class="py-20 bg-zinc-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 space-y-4">
                <h2 class="text-4xl lg:text-5xl font-bold text-[var(--color-agro-green-dark)]">
                    Precio justo para cada perfil
                </h2>
                <p class="text-xl text-zinc-600 max-w-2xl mx-auto">
                    Desde gratis para el viticultor básico hasta planes escalados para Denominaciones de Origen. Sin sorpresas.
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-8 max-w-6xl mx-auto">

                <!-- Viticultor -->
                <div class="glass-card rounded-2xl p-8 hover-lift border-2 border-[var(--color-agro-green)] relative overflow-hidden transition-all duration-300 shadow-xl">
                    <div class="absolute top-0 right-0">
                        <div class="bg-gradient-to-r from-[var(--color-agro-green)] to-[var(--color-agro-green-light)] text-white px-5 py-1.5 rounded-bl-2xl font-semibold text-sm">
                            Más popular
                        </div>
                    </div>
                    <div class="mb-5 pt-6">
                        <div class="text-3xl mb-2">🌿</div>
                        <h3 class="text-2xl font-bold text-[var(--color-agro-green-dark)]">Viticultor</h3>
                        <p class="text-zinc-500 text-sm mt-1">Autoservicio · Sin llamadas · Cancela cuando quieras</p>
                    </div>
                    <div class="mb-6 space-y-2">
                        <!-- Tier básico gratuito -->
                        <div class="p-3 bg-zinc-50 rounded-xl border border-zinc-200">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-zinc-600">Básico (invitado por bodega)</span>
                                <span class="text-base font-bold text-zinc-800">Gratis</span>
                            </div>
                            <p class="text-xs text-zinc-400 mt-0.5">Cuaderno de campo básico</p>
                        </div>
                        <!-- Tier completo invitado -->
                        <div class="p-3 bg-[var(--color-agro-green-bg)] rounded-xl border border-[var(--color-agro-green-light)]/40">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-[var(--color-agro-green-dark)]">Completo (invitado por bodega)</span>
                                <span class="text-base font-bold text-[var(--color-agro-green-dark)]">9€/mes</span>
                            </div>
                            <p class="text-xs text-zinc-500 mt-0.5">o 85€/año — SIGPAC, PAC, teledetección...</p>
                        </div>
                        <!-- Tier independiente -->
                        <div class="p-3 bg-[var(--color-agro-green-bg)] rounded-xl border-2 border-[var(--color-agro-green)]">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-[var(--color-agro-green-dark)]">Independiente (sin bodega)</span>
                                <span class="text-xl font-bold text-[var(--color-agro-green-dark)]">14€/mes</span>
                            </div>
                            <p class="text-xs text-zinc-500 mt-0.5">o 130€/año — acceso completo</p>
                        </div>
                    </div>
                    <ul class="space-y-3 mb-8 text-sm">
                        @foreach(['Cuaderno de campo digital (obligatorio 2027)', 'SIGPAC y gestión de parcelas', 'Teledetección NDVI satelital', 'PAC y normativa vigente', 'Facturación agrícola + Verifactu', 'Vendimias y plantaciones', 'App móvil (funciona sin conexión)', 'Soporte por email (48h)'] as $feature)
                        <li class="flex items-start gap-2.5">
                            <svg class="w-5 h-5 text-[var(--color-agro-green)] flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-zinc-700">{{ $feature }}</span>
                        </li>
                        @endforeach
                    </ul>
                    <a href="{{ route('register') }}" class="block w-full text-center px-6 py-4 rounded-xl bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:from-[var(--color-agro-green)] hover:to-[var(--color-agro-green-dark)] transition-all duration-300 shadow-lg font-semibold">
                        Comenzar Gratis
                    </a>
                    <p class="text-center text-xs text-zinc-400 mt-3">Sin tarjeta requerida</p>
                </div>

                <!-- Bodega -->
                <div class="glass-card rounded-2xl p-8 hover-lift border-2 border-red-200 hover:border-red-400 transition-all duration-300">
                    <div class="mb-5">
                        <div class="text-3xl mb-2">🍷</div>
                        <h3 class="text-2xl font-bold text-[var(--color-agro-green-dark)]">Bodega</h3>
                        <p class="text-zinc-500 text-sm mt-1">Demo gratuita · Onboarding incluido</p>
                    </div>
                    <div class="mb-6 space-y-2">
                        <!-- Bodega en DO -->
                        <div class="p-3 bg-zinc-50 rounded-xl border border-zinc-200">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-zinc-600">Dentro de una Denominación de Origen</span>
                                <span class="text-base font-bold text-zinc-800">Gratis</span>
                            </div>
                            <p class="text-xs text-zinc-400 mt-0.5">Cubierta por el paquete de la DO</p>
                        </div>
                        <!-- Bodega independiente -->
                        <div class="p-3 bg-red-50 rounded-xl border-2 border-red-400">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-red-700">Independiente (sin DO)</span>
                                <span class="text-xl font-bold text-red-700">14€/mes</span>
                            </div>
                            <p class="text-xs text-zinc-500 mt-0.5">o 130€/año · Onboarding incluido + migración gratuita</p>
                        </div>
                    </div>
                    <ul class="space-y-3 mb-8 text-sm">
                        @foreach(['Panel de viticultores en tiempo real', 'Gestión completa de vendimia', 'Trazabilidad desde cepa hasta botella', 'Facturación agrícola integrada', 'Gestión de vinos y elaboración', 'Comparativa rendimientos real vs estimado', 'Soporte prioritario (24h)', 'Onboarding personalizado incluido'] as $feature)
                        <li class="flex items-start gap-2.5">
                            <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-zinc-700">{{ $feature }}</span>
                        </li>
                        @endforeach
                    </ul>
                    <a href="{{ route('register') }}" class="block w-full text-center px-6 py-4 rounded-xl bg-gradient-to-r from-red-600 to-red-500 text-white hover:from-red-700 hover:to-red-600 transition-all duration-300 shadow-lg font-semibold">
                        Comenzar Gratis
                    </a>
                    <p class="text-center text-xs text-zinc-400 mt-3">Sin tarjeta requerida</p>
                </div>

                <!-- DO -->
                <div class="glass-card rounded-2xl p-8 hover-lift border-2 border-amber-200 hover:border-amber-400 transition-all duration-300">
                    <div class="mb-5">
                        <div class="text-3xl mb-2">🏛️</div>
                        <h3 class="text-2xl font-bold text-[var(--color-agro-green-dark)]">Denominación de Origen</h3>
                        <p class="text-zinc-500 text-sm mt-1">Solución a medida · Contrato anual</p>
                    </div>
                    <div class="mb-6 rounded-xl border border-amber-200 overflow-hidden">
                        <table class="w-full text-xs">
                            <thead>
                                <tr class="bg-amber-50 border-b border-amber-200">
                                    <th class="text-left px-3 py-2 font-semibold text-amber-800">Bodegas</th>
                                    <th class="text-right px-3 py-2 font-semibold text-amber-800">Mensual</th>
                                    <th class="text-right px-3 py-2 font-semibold text-amber-800">Anual</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-amber-100 bg-white">
                                <tr><td class="px-3 py-1.5 text-zinc-700">Hasta 25</td><td class="px-3 py-1.5 text-right font-semibold text-amber-800">149€</td><td class="px-3 py-1.5 text-right text-zinc-500">1.400€/año</td></tr>
                                <tr><td class="px-3 py-1.5 text-zinc-700">26 – 50</td><td class="px-3 py-1.5 text-right font-semibold text-amber-800">249€</td><td class="px-3 py-1.5 text-right text-zinc-500">2.350€/año</td></tr>
                                <tr><td class="px-3 py-1.5 text-zinc-700">51 – 75</td><td class="px-3 py-1.5 text-right font-semibold text-amber-800">349€</td><td class="px-3 py-1.5 text-right text-zinc-500">3.300€/año</td></tr>
                                <tr><td class="px-3 py-1.5 text-zinc-700">76 – 100</td><td class="px-3 py-1.5 text-right font-semibold text-amber-800">449€</td><td class="px-3 py-1.5 text-right text-zinc-500">4.250€/año</td></tr>
                                <tr><td class="px-3 py-1.5 text-zinc-700">+100</td><td class="px-3 py-1.5 text-right font-semibold text-amber-800" colspan="2">A negociar</td></tr>
                            </tbody>
                        </table>
                        <p class="text-xs text-zinc-400 text-center py-2 bg-amber-50 border-t border-amber-100">Bodegas adscritas sin coste adicional</p>
                    </div>
                    <ul class="space-y-3 mb-8 text-sm">
                        @foreach(['Todo del plan Bodega', 'Alta y gestión de bodegas adscritas', 'Panel de supervisión centralizado', 'Alertas automáticas de incumplimiento', 'Informes consolidados por denominación', 'Firma electrónica SHA-256', 'API para integración con sistemas actuales', 'Account manager dedicado', 'SLA 99,9% uptime garantizado'] as $feature)
                        <li class="flex items-start gap-2.5">
                            <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-zinc-700">{{ $feature }}</span>
                        </li>
                        @endforeach
                    </ul>
                    <a href="https://wa.me/34684217167?text=Hola%2C%20soy%20una%20Denominaci%C3%B3n%20de%20Origen%20y%20me%20interesa%20Agro365" target="_blank" rel="noopener noreferrer" class="flex items-center justify-center gap-2 w-full text-center px-6 py-4 rounded-xl bg-amber-500 hover:bg-amber-600 text-white transition-all duration-300 font-semibold">
                        <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        Hablar por WhatsApp
                    </a>
                    <a href="mailto:info@agro365.es?subject=Consulta%20Denominaci%C3%B3n%20de%20Origen%20Agro365" class="flex items-center justify-center gap-2 w-full text-center px-6 py-3 rounded-xl border border-amber-300 text-amber-800 hover:bg-amber-50 transition-all duration-300 font-medium text-sm mt-2">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        Enviar un email
                    </a>
                    <p class="text-center text-xs text-zinc-400 mt-3">Propuesta sin compromiso en 24h</p>
                </div>
            </div>

            <!-- Nota importante sobre viticultores en bodegas -->
            <div class="mt-10 max-w-3xl mx-auto text-center p-5 bg-[var(--color-agro-green-bg)] rounded-2xl border border-[var(--color-agro-green-light)]/30">
                <p class="text-zinc-700 text-sm">
                    <strong>Cómo funciona con viticultores invitados:</strong> la bodega invita a sus proveedores.
                    El viticultor accede en modo <strong>básico gratis</strong> o activa el <strong>plan completo por 9€/mes</strong>
                    (SIGPAC, teledetección, PAC, facturación...). La bodega no paga por ello — el viticultor decide.
                </p>
            </div>

            <p class="text-center text-zinc-500 mt-6 text-sm">
                ¿Tienes dudas? <a href="mailto:info@agro365.es" class="text-[var(--color-agro-green)] hover:underline font-semibold">Escríbenos</a> — respondemos en menos de 24h.
            </p>
        </div>
    </section>


    <!-- FAQ Section (Schema markup para rich snippets en Google) -->
    <section class="py-20 bg-white">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl lg:text-4xl font-bold text-[var(--color-agro-green-dark)]">
                    Preguntas frecuentes
                </h2>
                <p class="text-zinc-500 mt-3">Las dudas más habituales antes de empezar</p>
            </div>
            <div class="space-y-3" itemscope itemtype="https://schema.org/FAQPage">
                @php
                $faqs = [
                    [
                        '¿El cuaderno de campo digital es realmente obligatorio desde 2027?',
                        'Sí, en dos fases. Desde enero de 2027 es obligatorio el registro básico de tratamientos en soporte digital. Desde 2028 entra en vigor el cuaderno completo con todos los campos exigidos por el Reglamento de Ejecución (UE) 2022/1441. Agro365 cubre ambas fases desde hoy.',
                    ],
                    [
                        '¿Cuánto cuesta Agro365 para un viticultor?',
                        'Depende del perfil. Si tu bodega ya está en Agro365 y te invita, el uso básico (cuaderno de campo) es gratis. Si quieres funciones completas (SIGPAC, teledetección, PAC, facturación...) pagas 9€/mes o 85€/año. Si eres viticultor independiente sin bodega asociada, el plan completo cuesta 14€/mes o 130€/año. No se requiere tarjeta de crédito para empezar.',
                    ],
                    [
                        '¿Puedo ser viticultor independiente y al mismo tiempo pertenecer a una bodega?',
                        'Sí. En Agro365 puedes gestionar tu cuaderno de campo de forma completamente independiente y compartirlo con una o varias bodegas simultáneamente. Tus datos son siempre tuyos: si te desconectas de una bodega, tu histórico permanece intacto.',
                    ],
                    [
                        '¿Los viticultores de mi bodega tienen que pagar su propio plan?',
                        'No para el uso básico. El viticultor invitado accede al cuaderno de campo gratis. Si quiere funciones avanzadas (SIGPAC, teledetección, PAC, facturación...) paga 9€/mes por su cuenta — la bodega no asume ese coste. Tú como bodega solo pagas tu cuota fija (14€/mes si eres independiente, o gratis si estás dentro de una DO).',
                    ],
                    [
                        '¿Los informes que genera Agro365 son válidos para inspecciones PAC?',
                        'Sí. Los informes se generan con firma electrónica SHA-256 y código QR de verificación, cumpliendo con los requisitos de las inspecciones PAC y con la normativa de cuaderno de campo digital 2027.',
                    ],
                    [
                        '¿Funciona sin conexión a internet en el campo?',
                        'Sí. La app móvil permite registrar actividades sin conexión. Los datos se sincronizan automáticamente cuando recuperas cobertura.',
                    ],
                    [
                        '¿Cuánto tiempo lleva configurar Agro365?',
                        'Para un viticultor, menos de 5 minutos: creas tu cuenta, importas tus parcelas SIGPAC y empiezas a registrar. Para una bodega o DO, ofrecemos onboarding personalizado incluido en el plan.',
                    ],
                    [
                        '¿Qué pasa con mis datos si cancelo la suscripción?',
                        'Tus datos son siempre tuyos. Puedes exportarlos en formato estándar en cualquier momento antes o después de cancelar. Nunca retenemos información.',
                    ],
                ];
                @endphp

                @foreach($faqs as $faq)
                <div itemscope itemprop="mainEntity" itemtype="https://schema.org/Question"
                     class="border border-zinc-200 rounded-xl overflow-hidden group">
                    <details>
                        <summary class="flex items-center justify-between px-6 py-4 cursor-pointer bg-white hover:bg-zinc-50 transition-colors font-semibold text-zinc-800 text-sm list-none" itemprop="name">
                            {{ $faq[0] }}
                            <svg class="w-5 h-5 text-zinc-400 flex-shrink-0 ml-4 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </summary>
                        <div itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer"
                             class="px-6 py-4 bg-zinc-50 text-sm text-zinc-600 border-t border-zinc-100">
                            <span itemprop="text">{{ $faq[1] }}</span>
                        </div>
                    </details>
                </div>
                @endforeach
            </div>
            <p class="text-center text-sm text-zinc-400 mt-8">
                ¿Más dudas? <a href="{{ route('faqs') }}" class="text-[var(--color-agro-green)] hover:underline font-medium">Ver todas las preguntas frecuentes →</a>
            </p>
        </div>
    </section>

    <!-- CTA Final -->
    <section class="py-20 bg-gradient-to-br from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)]">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/15 text-white text-sm font-semibold mb-6">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>
                </svg>
                Básico obligatorio 2027 · Completo 2028
            </div>
            <h2 class="text-4xl lg:text-5xl font-bold text-white mb-4">
                La normativa no espera.<br>Tu viñedo tampoco.
            </h2>
            <p class="text-green-100 text-xl mb-10 max-w-2xl mx-auto">
                Cuaderno básico obligatorio en 2027, completo en 2028.
                Viticultor básico gratis · Bodega 14€/mes · DO desde 149€/mes.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('register') }}" rel="nofollow" class="inline-flex items-center justify-center gap-2 px-8 py-4 rounded-xl bg-white text-[var(--color-agro-green-dark)] hover:bg-green-50 transition-all font-bold text-lg shadow-lg">
                    🌿 Empezar como Viticultor — Gratis
                </a>
                <a href="{{ route('register') }}" class="inline-flex items-center justify-center gap-2 px-8 py-4 rounded-xl border-2 border-white/70 text-white hover:bg-white/10 transition-all font-semibold text-lg">
                    🍷 Empezar Gratis — Bodega
                </a>
                <a href="https://wa.me/34684217167?text=Hola%2C%20soy%20una%20Denominaci%C3%B3n%20de%20Origen%20y%20me%20interesa%20Agro365" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center gap-2 px-8 py-4 rounded-xl border-2 border-white/40 text-white/90 hover:bg-white/10 transition-all font-semibold text-lg">
                    🏛️ Solicitar Demo — DO
                </a>
            </div>
            <p class="text-green-200/70 text-sm mt-8">
                Sin tarjeta requerida · Configuración en 5 minutos · Soporte en español
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
        "offers": [
            {"@@type": "Offer", "name": "Viticultor básico (invitado)", "price": "0", "priceCurrency": "EUR"},
            {"@@type": "Offer", "name": "Viticultor completo (invitado)", "price": "9.00", "priceCurrency": "EUR"},
            {"@@type": "Offer", "name": "Viticultor independiente", "price": "14.00", "priceCurrency": "EUR"},
            {"@@type": "Offer", "name": "Bodega independiente", "price": "14.00", "priceCurrency": "EUR"}
        ],
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