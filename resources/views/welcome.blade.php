<!DOCTYPE html>
<html lang="es" itemscope itemtype="https://schema.org/WebSite">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, user-scalable=yes">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <!-- SEO Meta Tags -->
    <title>Agro365 | Cuaderno de Campo Digital Obligatorio 2027 · Software Viticultura y Bodegas</title>
    <meta name="description" content="Cuaderno de campo digital obligatorio desde 2027. Software de gestión para viticultores independientes, bodegas y Denominaciones de Origen. Cumplimiento PAC, SIGPAC y trazabilidad completa. Prueba gratis 6 meses.">
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
                            <span class="text-sm font-semibold text-amber-800">⚡ Obligatorio en <span id="days-counter">...</span> días</span>
                        </div>
                    </div>
                    <script>
                    (function() {
                        var deadline = new Date('2027-01-01T00:00:00');
                        var el = document.getElementById('days-counter');
                        if (el) {
                            var diff = Math.ceil((deadline - new Date()) / 86400000);
                            el.textContent = diff > 0 ? diff : 0;
                        }
                    })();
                    </script>
                    
                    <h1 class="text-5xl lg:text-6xl font-bold text-[var(--color-agro-green-dark)] leading-tight">
                        Cuaderno de Campo Digital Obligatorio 2027
                        <span class="block text-3xl lg:text-4xl text-[var(--color-agro-green)] mt-2">— y mucho más</span>
                    </h1>

                    <p class="text-xl text-gray-600 leading-relaxed">
                        La plataforma que conecta <strong>viticultores</strong>, <strong>bodegas</strong> y <strong>Denominaciones de Origen</strong>.
                        Cumple con la normativa <a href="{{ content_route('content.normativa-pac') }}" class="text-[var(--color-agro-green)] hover:underline">PAC</a>,
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
                            <span class="text-gray-600 text-sm font-medium">Uso básico gratis</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-[var(--color-agro-green)]" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-gray-600 text-sm font-medium">Sin tarjeta</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-[var(--color-agro-green)]" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-gray-600 text-sm font-medium">Configuración en 5 minutos</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-amber-700 text-sm font-semibold">Obligatorio desde enero 2027</span>
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
                    Agro365 es la única plataforma española que conecta toda la cadena del vino en un ecosistema compartido.
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
                        Registra tratamientos, riegos y labores desde el móvil. Trabaja de forma independiente
                        o comparte tu cuaderno con una o varias bodegas. Aunque cambies de bodega,
                        <strong>tus datos siguen siendo tuyos</strong>.
                    </p>
                    <a href="{{ route('register') }}" rel="nofollow" class="inline-flex items-center gap-1.5 text-[var(--color-agro-green)] font-semibold text-sm hover:underline">
                        Empezar gratis
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
                        producciones y facturación. <strong>Tus viticultores no están obligados a pagar su propio plan</strong>
                        para que puedas verlos.
                    </p>
                    <a href="{{ content_route('content.bodegas') }}" class="inline-flex items-center gap-1.5 text-red-600 font-semibold text-sm hover:underline">
                        Saber más sobre Bodegas
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
                        desde un panel centralizado. <strong>Las bodegas no están obligadas a usar todas las funciones</strong>,
                        pero la visibilidad es inmediata cuando lo hacen.
                    </p>
                    <a href="#precios" class="inline-flex items-center gap-1.5 text-amber-700 font-semibold text-sm hover:underline">
                        Contactar con Ventas
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
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
                    Herramientas profesionales para <strong><a href="{{ content_route('content.software-viticultores') }}" class="text-[var(--color-agro-green)] hover:underline">viticultores y bodegas</a></strong>: <a href="{{ content_route('content.cuaderno-digital-viticultores') }}" class="text-[var(--color-agro-green)] hover:underline">cuaderno de campo digital obligatorio</a>, gestión de parcelas SIGPAC, informes oficiales con firma electrónica, control de vendimia y facturación integrada.
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
                        <strong><a href="{{ content_route('content.cuaderno-digital-viticultores') }}" class="text-[var(--color-agro-green)] hover:underline">Cuaderno de campo digital</a></strong> obligatorio desde 2027 según normativa europea. Registra tratamientos fitosanitarios, riegos, fertilizaciones y labores culturales desde cualquier lugar. Cumplimiento normativo garantizado para inspecciones PAC.
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
                <a href="{{ content_route('content.viticultores') }}" class="group glass-card rounded-2xl p-8 hover-lift border-2 border-transparent hover:border-[var(--color-agro-green-light)]/30 transition-all duration-300">
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
                <a href="{{ content_route('content.bodegas') }}" class="group glass-card rounded-2xl p-8 hover-lift border-2 border-transparent hover:border-[var(--color-agro-green-light)]/30 transition-all duration-300">
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
                <a href="{{ content_route('content.cooperativas') }}" class="group glass-card rounded-2xl p-8 hover-lift border-2 border-transparent hover:border-[var(--color-agro-green-light)]/30 transition-all duration-300">
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
                <a href="{{ content_route('content.ingenieros-agronomos') }}" class="group glass-card rounded-2xl p-8 hover-lift border-2 border-transparent hover:border-[var(--color-agro-green-light)]/30 transition-all duration-300">
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
                    <a href="mailto:info@agro365.es?subject=Consulta%20Denominaci%C3%B3n%20de%20Origen" class="block w-full text-center px-6 py-4 rounded-xl border-2 border-amber-400 text-amber-800 hover:bg-amber-500 hover:text-white transition-all duration-300 font-semibold">
                        Contactar
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
                        'Sí. El Reglamento de Ejecución (UE) 2022/1441 establece la obligatoriedad del cuaderno de campo digital para todos los agricultores profesionales en España a partir de enero de 2027. Agro365 cumple con todos los requisitos técnicos y legales de esta normativa.',
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
                Obligatorio desde enero 2027
            </div>
            <h2 class="text-4xl lg:text-5xl font-bold text-white mb-4">
                La normativa no espera.<br>Tu viñedo tampoco.
            </h2>
            <p class="text-green-100 text-xl mb-10 max-w-2xl mx-auto">
                El cuaderno de campo digital es obligatorio desde 2027.
                Agro365 está listo hoy. El uso básico es gratis — empieza ahora.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('register') }}" rel="nofollow" class="inline-flex items-center justify-center gap-2 px-8 py-4 rounded-xl bg-white text-[var(--color-agro-green-dark)] hover:bg-green-50 transition-all font-bold text-lg shadow-lg">
                    🌿 Empezar como Viticultor — Gratis
                </a>
                <a href="mailto:info@agro365.es?subject=Demo%20Bodega" class="inline-flex items-center justify-center gap-2 px-8 py-4 rounded-xl border-2 border-white/70 text-white hover:bg-white/10 transition-all font-semibold text-lg">
                    🍷 Solicitar Demo para Bodega
                </a>
                <a href="mailto:info@agro365.es?subject=Consulta%20DO" class="inline-flex items-center justify-center gap-2 px-8 py-4 rounded-xl border-2 border-white/40 text-white/90 hover:bg-white/10 transition-all font-semibold text-lg">
                    🏛️ Contactar — DO
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