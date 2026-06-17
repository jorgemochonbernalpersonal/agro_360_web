<!DOCTYPE html>
<html lang="es" itemscope itemtype="https://schema.org/WebSite">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <!-- SEO Meta Tags -->
    <title>Agro365 | Cuaderno de Campo Digital Obligatorio 2027 · Software Viticultura y Bodegas</title>
    <meta name="description" content="Cuaderno de campo digital obligatorio desde 2027. Software de gestión para viticultores, bodegas y Denominaciones de Origen. Cumplimiento PAC, SIGPAC y trazabilidad completa. Viticultor básico gratis, completo desde 9€/mes.">
    <meta name="author" content="Agro365">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <meta name="language" content="Spanish">
    <meta name="geo.region" content="ES">
    <meta name="geo.placename" content="España">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="{{ url('/') }}">
    <link rel="sitemap" type="application/xml" href="{{ url('/sitemap.xml') }}">
    
    <!-- Hreflang for Spain -->
    <link rel="alternate" hreflang="es" href="{{ url('/') }}">
    <link rel="alternate" hreflang="es-ES" href="{{ url('/') }}">
    <link rel="alternate" hreflang="x-default" href="{{ url('/') }}">
    
    <!-- Additional SEO Meta Tags -->
    <meta name="publisher" content="Agro365">
    <meta name="theme-color" content="#2d5016">
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
    <meta property="og:image" content="{{ asset('images/dashboard-preview-1400.jpg') }}">
    <meta property="og:image:width" content="1400">
    <meta property="og:image:height" content="669">
    <meta property="og:image:alt" content="Agro365 - Software de Gestión Agrícola para Viñedos">
    <meta property="og:locale" content="es_ES">
    <meta property="og:site_name" content="Agro365">
    <meta property="article:author" content="Agro365">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="{{ url('/') }}">
    <meta name="twitter:title" content="Agro365 | Cuaderno de Campo Digital Obligatorio 2027">
    <meta name="twitter:description" content="La plataforma que conecta viticultores, bodegas y Denominaciones de Origen. Viticultor básico gratis, completo desde 9€/mes.">
    <meta name="twitter:image" content="{{ asset('images/dashboard-preview-1400.jpg') }}">
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/icon_512x512.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/icon_512x512.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/icon_512x512.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/icon_512x512.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/icon_512x512.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/icon_512x512.png') }}">
    <meta name="msapplication-TileImage" content="{{ asset('images/icon_512x512.png') }}">
    <meta name="msapplication-TileColor" content="#2d5016">
    
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
                    'name' => 'Viticultor básico',
                    'price' => '0',
                    'priceCurrency' => 'EUR',
                    'availability' => 'https://schema.org/InStock',
                    'description' => 'Acceso básico gratuito para cualquier viticultor (vinculado o independiente): cuaderno de campo, parcelas, SIGPAC y fenología'
                ],
                [
                    '@type' => 'Offer',
                    'name' => 'Viticultor completo (invitado por bodega)',
                    'price' => '9.00',
                    'priceCurrency' => 'EUR',
                    'availability' => 'https://schema.org/InStock',
                    'description' => 'Plan completo para viticultor invitado: SIGPAC, teledetección NDVI, PAC, Verifactu'
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
                    'name' => 'Bodega dentro de DO asociada',
                    'price' => '0',
                    'priceCurrency' => 'EUR',
                    'availability' => 'https://schema.org/InStock',
                    'description' => 'Gestión completa de bodega incluida en el plan de Denominación de Origen'
                ],
                [
                    '@type' => 'Offer',
                    'name' => 'Bodega independiente — desde (solo su vino)',
                    'price' => '19.00',
                    'priceCurrency' => 'EUR',
                    'availability' => 'https://schema.org/InStock',
                    'description' => 'Trazabilidad, depósitos, Verifactu y gestión de vendimia para bodega sin DO. Escala con el número de viticultores gestionados: 19€ (0 viticultores), 29€ (1-10), 49€ (11-30), 79€ (31-60), 119€ (61-100).'
                ],
                [
                    '@type' => 'Offer',
                    'name' => 'Denominación de Origen — desde',
                    'price' => '149.00',
                    'priceCurrency' => 'EUR',
                    'availability' => 'https://schema.org/InStock',
                    'description' => 'Plataforma completa para DO: panel de viticultores, trazabilidad y cuaderno de campo colectivo'
                ],
                [
                    '@type' => 'Offer',
                    'name' => 'Productor — básico',
                    'price' => '0',
                    'priceCurrency' => 'EUR',
                    'availability' => 'https://schema.org/InStock',
                    'description' => 'Acceso básico gratuito para el productor: cuaderno de campo, parcelas, SIGPAC y fenología'
                ],
                [
                    '@type' => 'Offer',
                    'name' => 'Productor — completo (viñedo + bodega)',
                    'price' => '19.00',
                    'priceCurrency' => 'EUR',
                    'availability' => 'https://schema.org/InStock',
                    'description' => 'Plan combinado: todo el plan Viticultor Independiente más todo el plan Bodega (19€/mes o 180€/año frente a 28€/mes por separado). Básico gratis para siempre.'
                ]
            ],
            'description' => 'Software de gestión agrícola para viticultores, bodegas y Denominaciones de Origen. Cuaderno de campo digital obligatorio 2027. Teledetección NDVI, Verifactu y trazabilidad completa.',
            'operatingSystem' => ['Web', 'iOS', 'Android'],
            'releaseNotes' => 'Versión 1.0 - Plataforma en producción',
            'screenshot' => asset('images/foto1.webp'),
            'featureList' => [
                'Cuaderno de campo digital obligatorio 2027',
                'Gestión de parcelas SIGPAC',
                'Teledetección NDVI y análisis de vigor vegetativo',
                'Informes oficiales con firma electrónica SHA-256',
                'Dashboard de cumplimiento PAC en tiempo real',
                'Control de cosechas y rendimientos por parcela',
                'Facturación Verifactu integrada',
                'Gestión de cuadrillas y maquinaria agrícola',
                'Trazabilidad vino-origen: parcela → bodega → botella',
                'Gestión de depósitos y pipeline de elaboración'
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
            'logo' => asset('images/icon_512x512.png'),
            'description' => 'Plataforma de gestión agrícola profesional para viticultores y bodegas',
            'foundingDate' => '2024',
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'email' => 'info@agro365.es',
                'contactType' => 'customer service',
                'availableLanguage' => ['Spanish'],
                'areaServed' => 'ES'
            ],
            // 'sameAs' => [...]  ← re-añadir SOLO cuando existan perfiles sociales reales y verificables.
            //   Declarar perfiles inexistentes perjudica la validación del structured data en Google.
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
            'logo' => asset('images/icon_512x512.png'),
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
            'priceRange' => '€0-€119',
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
        "image": "{{ asset('images/dashboard-preview.webp') }}",
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
    <link rel="preload" href="{{ asset('images/logo-nav.png') }}" as="image">
    <link rel="preload" href="{{ asset('images/dashboard-preview-1400.jpg') }}" as="image" fetchpriority="high">
    
    <!-- DNS Prefetch for faster loading -->
    <link rel="dns-prefetch" href="https://fonts.bunny.net">

    <!-- Analítica cookieless (Plausible) — sin cookies ni datos personales, exenta de consentimiento.
         Requiere dar de alta el dominio en https://plausible.io (o tu instancia self-hosted). -->
    <link rel="dns-prefetch" href="https://plausible.io">
    <script defer data-domain="agro365.es" src="https://plausible.io/js/script.js"></script>
</head>
<body class="bg-white min-h-screen text-zinc-800 antialiased">
    @php
        $waBase     = 'https://wa.me/34684217167';
        $waDefault  = $waBase . '?text=Hola%2C%20me%20interesa%20Agro365';
        $waFounders = $waBase . '?text=Hola%2C%20me%20interesa%20el%20programa%20de%20fundadores%20de%20Agro365';
        $waDO       = $waBase . '?text=Hola%2C%20soy%20una%20Denominaci%C3%B3n%20de%20Origen%20y%20me%20interesa%20Agro365';

        $foundersTotal = (int) config('app.founder_max_slots', 25);
        $foundersTaken = \App\Models\User::where('is_founder', true)->count();
        $foundersLeft  = max(0, $foundersTotal - $foundersTaken);
        $foundersPct   = (int) round($foundersTaken / $foundersTotal * 100);

        $daysLeft = now()->lt('2027-01-01') ? (int) now()->startOfDay()->diffInDays('2027-01-01') : 0;
    @endphp
    
    <!-- Navigation Header -->
    <nav class="bg-white/90 backdrop-blur-md border-b border-zinc-200 sticky top-0 z-50" x-data="{ open: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <a href="{{ url('/') }}" class="flex items-center" aria-label="{{ __('Agro365 - Ir a inicio') }}">
                    <img
                        src="{{ asset('images/logo-nav.png') }}"
                        alt="Agro365 - Software de gestión agrícola para viñedos y bodegas"
                        width="128"
                        height="64"
                        class="h-14 w-auto object-contain"
                        fetchpriority="high"
                        loading="eager"
                        decoding="async"
                    >
                </a>

                <!-- Anchor links (desktop) -->
                <div class="hidden lg:flex items-center gap-8">
                    <a href="#como-funciona" class="text-sm font-medium text-zinc-600 hover:text-zinc-900 transition-colors">{{ __('Cómo funciona') }}</a>
                    <a href="#ecosistema" class="text-sm font-medium text-zinc-600 hover:text-zinc-900 transition-colors">{{ __('Para quién') }}</a>
                    <a href="#funcionalidades" class="text-sm font-medium text-zinc-600 hover:text-zinc-900 transition-colors">{{ __('Funcionalidades') }}</a>
                    <a href="#precios" class="text-sm font-medium text-zinc-600 hover:text-zinc-900 transition-colors">{{ __('Precios') }}</a>
                    <a href="#faq" class="text-sm font-medium text-zinc-600 hover:text-zinc-900 transition-colors">{{ __('FAQ') }}</a>
                </div>

                <!-- Auth + hamburger -->
                <div class="flex items-center gap-3">
                    <a href="{{ route('login') }}" rel="nofollow" class="text-zinc-700 hover:text-zinc-900 font-medium transition-colors text-sm">
                        Iniciar sesión
                    </a>
                    <a href="{{ route('register') }}" rel="nofollow" class="px-4 py-2 rounded-lg bg-agro-700 text-white hover:bg-agro-600 transition-colors duration-200 font-semibold text-sm">
                        Empezar gratis
                    </a>
                    <!-- Hamburger (mobile only) -->
                    <button @click="open = !open"
                            class="lg:hidden p-2 rounded-lg text-zinc-600 hover:text-zinc-900 hover:bg-zinc-100 transition-colors"
                            :aria-expanded="open"
                            aria-label="{{ __('Abrir menú') }}">
                        <svg x-show="!open" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                        <svg x-show="open" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile menu -->
        <div x-show="open"
             x-cloak
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 -translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-1"
             class="lg:hidden border-t border-zinc-200 bg-white/95 backdrop-blur-md">
            <div class="max-w-7xl mx-auto px-4 py-3 space-y-1">
                <a href="#como-funciona" @click="open = false" class="block px-3 py-2.5 rounded-lg text-sm font-medium text-zinc-700 hover:text-zinc-900 hover:bg-zinc-50 transition-colors">{{ __('Cómo funciona') }}</a>
                <a href="#ecosistema"    @click="open = false" class="block px-3 py-2.5 rounded-lg text-sm font-medium text-zinc-700 hover:text-zinc-900 hover:bg-zinc-50 transition-colors">{{ __('Para quién') }}</a>
                <a href="#funcionalidades" @click="open = false" class="block px-3 py-2.5 rounded-lg text-sm font-medium text-zinc-700 hover:text-zinc-900 hover:bg-zinc-50 transition-colors">{{ __('Funcionalidades') }}</a>
                <a href="#precios"      @click="open = false" class="block px-3 py-2.5 rounded-lg text-sm font-medium text-zinc-700 hover:text-zinc-900 hover:bg-zinc-50 transition-colors">{{ __('Precios') }}</a>
                <a href="#faq"          @click="open = false" class="block px-3 py-2.5 rounded-lg text-sm font-medium text-zinc-700 hover:text-zinc-900 hover:bg-zinc-50 transition-colors">{{ __('FAQ') }}</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section – full-width animado -->
    <section class="hero-agro">
        {{-- Fondo --}}
        <div class="hero-agro-bg"></div>

        {{-- Cielo SVG --}}
        <svg class="hero-agro-sky" viewBox="0 0 1440 400" preserveAspectRatio="xMidYMid slice" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <defs>
                <linearGradient id="hag-skyg" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%"   stop-color="#0a1a04"/>
                    <stop offset="40%"  stop-color="#1a3d0a"/>
                    <stop offset="100%" stop-color="#2a5a10" stop-opacity="0"/>
                </linearGradient>
            </defs>
            <rect width="1440" height="400" fill="url(#hag-skyg)"/>
            <ellipse cx="1150" cy="110" rx="130" ry="110" fill="#f5c040" opacity="0.06"/>
            <ellipse cx="1150" cy="110" rx="60"  ry="55"  fill="#f5d060" opacity="0.10"/>
            <circle  cx="1150" cy="110" r="22"           fill="#ffe080"  opacity="0.50"/>
            <circle  cx="1150" cy="110" r="12"           fill="#fff4b0"  opacity="0.80"/>
            <path d="M0,320 Q360,270 720,295 Q1080,318 1440,280 L1440,400 L0,400Z" fill="#0d2206" opacity="0.7"/>
            <path d="M0,360 Q480,325 960,342 Q1200,352 1440,335 L1440,400 L0,400Z" fill="#0a1a05" opacity="0.85"/>
        </svg>

        <div class="hero-agro-overlay"></div>
        <div class="hero-agro-ground"></div>

        {{-- Filas de viñedo --}}
        <svg class="hero-agro-vines" width="100%" height="120" viewBox="0 0 1440 120" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <line x1="0" y1="22"  x2="1440" y2="12"  stroke="#1a4008" stroke-width="1.5"/>
            <line x1="0" y1="42"  x2="1440" y2="28"  stroke="#1a4008" stroke-width="2"/>
            <line x1="0" y1="66"  x2="1440" y2="48"  stroke="#1e4a0a" stroke-width="3"/>
            <line x1="0" y1="96"  x2="1440" y2="72"  stroke="#1e4a0a" stroke-width="4.5"/>
            <line x1="0" y1="120" x2="1440" y2="100" stroke="#1e4a0a" stroke-width="6"/>
            <g stroke="#2a5010" stroke-width="1">
                <line x1="160"  y1="22" x2="160"  y2="42"/><line x1="160"  y1="42" x2="160"  y2="66"/>
                <line x1="380"  y1="20" x2="380"  y2="42"/><line x1="380"  y1="42" x2="380"  y2="66"/>
                <line x1="600"  y1="18" x2="600"  y2="40"/><line x1="600"  y1="40" x2="600"  y2="64"/>
                <line x1="820"  y1="16" x2="820"  y2="38"/><line x1="820"  y1="38" x2="820"  y2="62"/>
                <line x1="1040" y1="14" x2="1040" y2="36"/><line x1="1040" y1="36" x2="1040" y2="60"/>
                <line x1="1260" y1="13" x2="1260" y2="35"/>
            </g>
        </svg>

        {{-- Tractor --}}
        <div class="hero-agro-tractor-wrap" aria-hidden="true">
            <div class="hero-agro-tractor-inner">
                <div class="hero-agro-dust">
                    <div class="hero-agro-dp hero-agro-dp1"></div>
                    <div class="hero-agro-dp hero-agro-dp2"></div>
                    <div class="hero-agro-dp hero-agro-dp3"></div>
                </div>
                <svg width="190" height="115" viewBox="0 0 190 115" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <ellipse cx="95" cy="108" rx="78" ry="6" fill="rgba(0,0,0,0.32)"/>
                    <rect x="20" y="54" width="130" height="16" rx="3" fill="#0e2232"/>
                    <rect x="26" y="22" width="80" height="38" rx="6" fill="#5aaa28" stroke="#1C3A4A" stroke-width="1.5"/>
                    <rect x="30" y="25" width="72" height="10" rx="3" fill="rgba(255,255,255,0.1)"/>
                    <rect x="96" y="28" width="50" height="28" rx="4" fill="#4a9020" stroke="#1C3A4A" stroke-width="1.5"/>
                    <rect x="100" y="32" width="42" height="8" rx="2" fill="rgba(0,0,0,0.12)"/>
                    <rect x="100" y="42" width="42" height="4" rx="1" fill="rgba(0,0,0,0.08)"/>
                    <rect x="140" y="12" width="5" height="18" rx="2.5" fill="#1C3A4A"/>
                    <circle cx="142" cy="9" r="4" fill="rgba(210,210,190,0.35)"/>
                    <circle cx="144" cy="4" r="3" fill="rgba(210,210,190,0.2)"/>
                    <circle cx="141" cy="0" r="2" fill="rgba(210,210,190,0.12)"/>
                    <rect x="24" y="10" width="58" height="16" rx="4" fill="#152a38"/>
                    <rect x="30" y="14" width="42" height="20" rx="3" fill="#b8dff0" opacity="0.7"/>
                    <line x1="35" y1="15" x2="40" y2="33" stroke="rgba(255,255,255,0.3)" stroke-width="2"/>
                    <rect x="20" y="6" width="64" height="7" rx="3" fill="#0e1e2c"/>
                    <line x1="68" y1="24" x2="68" y2="58" stroke="rgba(0,0,0,0.2)" stroke-width="1"/>
                    <rect x="15" y="56" width="11" height="4" rx="1" fill="#0a1a28"/>
                    <rect x="15" y="62" width="9"  height="4" rx="1" fill="#0a1a28"/>
                    <g class="hero-agro-wheel">
                        <circle cx="52" cy="82" r="28" fill="#181818" stroke="#080808" stroke-width="1.5"/>
                        <circle cx="52" cy="82" r="20" fill="#242424"/>
                        <circle cx="52" cy="82" r="11" fill="#e5e5dd"/>
                        <circle cx="52" cy="82" r="5.5" fill="#1C3A4A"/>
                        <line x1="52" y1="54"  x2="52" y2="61"  stroke="#4a4a4a" stroke-width="3.5" stroke-linecap="round"/>
                        <line x1="52" y1="103" x2="52" y2="110" stroke="#4a4a4a" stroke-width="3.5" stroke-linecap="round"/>
                        <line x1="24" y1="82"  x2="31" y2="82"  stroke="#4a4a4a" stroke-width="3.5" stroke-linecap="round"/>
                        <line x1="73" y1="82"  x2="80" y2="82"  stroke="#4a4a4a" stroke-width="3.5" stroke-linecap="round"/>
                        <line x1="32" y1="62"  x2="37" y2="67"  stroke="#4a4a4a" stroke-width="3" stroke-linecap="round"/>
                        <line x1="67" y1="97"  x2="72" y2="102" stroke="#4a4a4a" stroke-width="3" stroke-linecap="round"/>
                        <line x1="32" y1="102" x2="37" y2="97"  stroke="#4a4a4a" stroke-width="3" stroke-linecap="round"/>
                        <line x1="67" y1="67"  x2="72" y2="62"  stroke="#4a4a4a" stroke-width="3" stroke-linecap="round"/>
                    </g>
                    <g class="hero-agro-wheel-f">
                        <circle cx="144" cy="86" r="17" fill="#181818" stroke="#080808" stroke-width="1.5"/>
                        <circle cx="144" cy="86" r="11" fill="#242424"/>
                        <circle cx="144" cy="86" r="6"  fill="#e5e5dd"/>
                        <circle cx="144" cy="86" r="2.8" fill="#1C3A4A"/>
                        <line x1="144" y1="69"  x2="144" y2="75"  stroke="#4a4a4a" stroke-width="2.5" stroke-linecap="round"/>
                        <line x1="144" y1="97"  x2="144" y2="103" stroke="#4a4a4a" stroke-width="2.5" stroke-linecap="round"/>
                        <line x1="127" y1="86"  x2="133" y2="86"  stroke="#4a4a4a" stroke-width="2.5" stroke-linecap="round"/>
                        <line x1="155" y1="86"  x2="161" y2="86"  stroke="#4a4a4a" stroke-width="2.5" stroke-linecap="round"/>
                    </g>
                    <rect x="8" y="62" width="16" height="6" rx="2" fill="#0a1820"/>
                </svg>
            </div>
        </div>

        {{-- Contenido --}}
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-20 pb-44 lg:pt-28 lg:pb-52">
            <div class="max-w-2xl space-y-7 animate-fade-in">

                {{-- Badge urgencia --}}
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full border border-white/20 bg-white/10 backdrop-blur-sm max-w-full">
                    <span class="w-2 h-2 rounded-full bg-amber-400 shrink-0"></span>
                    <span class="text-xs font-semibold text-white/80 tracking-wide">{{ __('Cuaderno digital obligatorio desde enero 2027') }} — quedan {{ $daysLeft }} días</span>
                </div>

                {{-- H1 --}}
                <h1 class="text-4xl lg:text-5xl xl:text-[3.4rem] font-bold tracking-tight text-white leading-[1.08]">
                    {{ __('Cuaderno de Campo Digital') }}
                    <span class="block text-agro-action mt-1">{{ __('del viñedo a la botella') }}</span>
                </h1>

                {{-- Descripción --}}
                <p class="text-lg text-white/65 leading-relaxed max-w-xl">
                    La plataforma de gestión para <strong class="font-semibold text-white/85">viticultores, bodegas y Denominaciones de Origen</strong>.
                    Cumple la normativa <a href="{{ content_route('content.normativa-pac') }}" class="text-agro-action font-medium hover:underline">PAC</a>,
                    gestiona tus parcelas <a href="{{ content_route('content.que-es-sigpac') }}" class="text-agro-action font-medium hover:underline">SIGPAC</a>
                    y lleva la trazabilidad completa. {{ __('Sin papel. Sin Excel. Todo en un solo sitio.') }}
                </p>

                {{-- CTAs --}}
                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('register') }}" rel="nofollow" class="group inline-flex items-center justify-center gap-2 px-6 py-3 rounded-lg bg-agro-action text-white hover:bg-agro-action-hover transition-colors duration-200 font-semibold text-base shadow-sm">
                        Empezar gratis
                        <svg class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                    <a href="#precios" class="inline-flex items-center justify-center px-6 py-3 rounded-lg border border-white/30 text-white/85 hover:border-white/60 hover:text-white transition-colors duration-200 font-semibold text-base">
                        Ver planes y precios
                    </a>
                </div>

                {{-- Claims --}}
                <div class="flex flex-wrap items-center gap-x-5 gap-y-2 pt-1">
                    @foreach([
                        'Básico gratis para siempre',
                        $foundersLeft > 0
                            ? "Hasta 1 año gratis como fundador · {$foundersLeft} plazas"
                            : '3 meses del plan Completo gratis al registrarte',
                        'Sin tarjeta de crédito',
                        'Tus datos, siempre tuyos',
                    ] as $claim)
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-agro-action" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-white/60 text-sm">{{ __($claim) }}</span>
                    </div>
                    @endforeach
                </div>

            </div>
        </div>
    </section>

    <!-- Marquee de features -->
    <div class="overflow-hidden bg-[#0a1c04] border-b border-[#1a4008] py-3.5 select-none">
        <div class="marquee-track gap-0">
            @php
            $items = [
                '🌿 Cuaderno de campo digital',
                '📍 Parcelas SIGPAC',
                '🍇 Trazabilidad viñedo→botella',
                '🛰️ Teledetección NDVI',
                '🧾 Facturación Verifactu',
                '📋 Informes PAC oficiales',
                '🏺 Control de depósitos',
                '🌾 Gestión de cosechas',
                '🏛️ Denominaciones de Origen',
                '📱 Sin papel · Sin Excel',
            ];
            @endphp
            @foreach(array_merge($items, $items) as $item)
            <span class="inline-flex items-center px-8 text-sm font-medium text-agro-action/80 whitespace-nowrap">
                {{ $item }}
                <span class="mx-8 text-[#2a5010]">·</span>
            </span>
            @endforeach
        </div>
    </div>

    <!-- Cómo funciona -->
    <section id="como-funciona" class="py-20 bg-zinc-50 border-t border-zinc-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl lg:text-4xl font-bold tracking-tight text-zinc-900">{{ __('Empieza en 5 minutos. Sin papel. Sin complicaciones.') }}</h2>
            </div>
            <div class="grid md:grid-cols-3 gap-8 mb-12">
                <!-- Paso 1 -->
                <div class="bg-white rounded-2xl p-7 border border-gray-100 shadow-sm">
                    <div class="w-10 h-10 rounded-full bg-[var(--color-agro-green-dark)] text-white flex items-center justify-center font-bold text-lg mb-5">1</div>
                    <h3 class="text-lg font-bold text-zinc-900 mb-2">{{ __('Crea tu cuenta gratis') }}</h3>
                    <p class="text-gray-600 text-sm leading-relaxed">{{ __('Sin tarjeta, sin llamadas. Regístrate como viticultor, productor, bodega o DO y accede al panel en segundos.') }}</p>
                </div>
                <!-- Paso 2 -->
                <div class="bg-white rounded-2xl p-7 border border-gray-100 shadow-sm">
                    <div class="w-10 h-10 rounded-full bg-[var(--color-agro-green-dark)] text-white flex items-center justify-center font-bold text-lg mb-5">2</div>
                    <h3 class="text-lg font-bold text-zinc-900 mb-2">{{ __('Importa tus parcelas SIGPAC') }}</h3>
                    <p class="text-gray-600 text-sm leading-relaxed">{{ __('Introduce tu NIF y tus parcelas se importan automáticamente desde el Ministerio. Sin introducir datos a mano.') }}</p>
                </div>
                <!-- Paso 3 -->
                <div class="bg-white rounded-2xl p-7 border border-gray-100 shadow-sm">
                    <div class="w-10 h-10 rounded-full bg-[var(--color-agro-green-dark)] text-white flex items-center justify-center font-bold text-lg mb-5">3</div>
                    <h3 class="text-lg font-bold text-zinc-900 mb-2">{{ __('Registra desde el móvil') }}</h3>
                    <p class="text-gray-600 text-sm leading-relaxed">{{ __('Anota tratamientos, riegos y labores desde cualquier dispositivo. La web está optimizada para móvil — sin instalar nada.') }}</p>
                </div>
            </div>
            <div class="text-center">
                <a href="{{ route('register') }}" rel="nofollow" class="inline-flex items-center gap-2 px-8 py-4 rounded-xl bg-agro-700 text-white hover:bg-agro-600 transition-colors duration-200 shadow-sm font-semibold text-base">
                    Comenzar gratis — sin tarjeta
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- El producto en acción -->
    <section class="py-20 bg-white border-t border-zinc-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12 space-y-3">
                <p class="text-xs font-semibold uppercase tracking-widest text-agro-600">El producto real</p>
                <h2 class="text-3xl lg:text-4xl font-bold tracking-tight text-zinc-900">Así se ve Agro365 por dentro</h2>
                <p class="text-zinc-500 max-w-xl mx-auto">Datos de demostración — el panel real que usarás desde el primer día.</p>
            </div>

            {{-- Tabs --}}
            <div x-data="{ tab: 1 }">
                <div class="flex flex-wrap justify-center gap-2 mb-8">
                    <button @click="tab = 1"
                            :class="tab === 1 ? 'bg-agro-700 text-white shadow-sm' : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200'"
                            class="px-5 py-2 rounded-full text-sm font-semibold transition-colors duration-150">
                        📋 Cuaderno de Campo
                    </button>
                    <button @click="tab = 2"
                            :class="tab === 2 ? 'bg-agro-700 text-white shadow-sm' : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200'"
                            class="px-5 py-2 rounded-full text-sm font-semibold transition-colors duration-150">
                        🗺️ Mapa SIGPAC
                    </button>
                    <button @click="tab = 3"
                            :class="tab === 3 ? 'bg-agro-700 text-white shadow-sm' : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200'"
                            class="px-5 py-2 rounded-full text-sm font-semibold transition-colors duration-150">
                        🍇 Declaraciones de Vendimia
                    </button>
                </div>

                <div class="relative rounded-2xl border border-zinc-200 shadow-2xl shadow-zinc-900/8 overflow-hidden bg-white">
                    {{-- Barra de navegador falsa --}}
                    <div class="flex items-center gap-1.5 px-4 py-2.5 bg-zinc-50 border-b border-zinc-200">
                        <span class="w-2.5 h-2.5 rounded-full bg-red-400"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-green-400"></span>
                        <span class="ml-3 text-xs text-zinc-400 font-mono">app.agro365.es</span>
                    </div>

                    <div x-show="tab === 1" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                        <picture>
                            <source srcset="{{ asset('images/foto1.webp') }}" type="image/webp">
                            <img src="{{ asset('images/foto1.png') }}"
                                 alt="Cuaderno de campo digital Agro365 — tratamientos, fertilización, riegos y cosecha"
                                 class="w-full h-auto block"
                                 style="box-shadow:inset 0 -2px 12px rgba(0,0,0,0.06)"
                                 loading="lazy" decoding="async">
                        </picture>
                    </div>
                    <div x-show="tab === 2" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                        <div class="w-full overflow-hidden" style="height:480px">
                            <picture>
                                <source srcset="{{ asset('images/foto2.webp') }}" type="image/webp">
                                <img src="{{ asset('images/foto2.png') }}"
                                     alt="Mapa SIGPAC de parcelas vitícolas en Agro365"
                                     class="w-full h-full object-cover"
                                     style="object-position:35% 30%;box-shadow:inset 0 -2px 12px rgba(0,0,0,0.06)"
                                     loading="lazy" decoding="async">
                            </picture>
                        </div>
                    </div>
                    <div x-show="tab === 3" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                        <picture>
                            <source srcset="{{ asset('images/foto3.webp') }}" type="image/webp">
                            <img src="{{ asset('images/foto3.png') }}"
                                 alt="Declaraciones de vendimia ante DO en Agro365"
                                 class="w-full h-auto block"
                                 style="box-shadow:inset 0 -2px 12px rgba(0,0,0,0.06)"
                                 loading="lazy" decoding="async">
                        </picture>
                    </div>
                </div>

                {{-- Leyenda por tab --}}
                <div class="mt-4 text-center text-sm text-zinc-400">
                    <span x-show="tab === 1">Cuaderno de Campo · Campaña 2026 · Tratamientos fitosanitarios, fertilización, riegos y cosecha en un solo panel</span>
                    <span x-show="tab === 2">Mapa SIGPAC · Parcelas importadas automáticamente desde el Ministerio con un solo clic</span>
                    <span x-show="tab === 3">Declaraciones de Vendimia · Gestión de declaraciones ante la DO con trazabilidad completa por parcela</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Credibilidad / Confianza -->
    <section class="py-16 bg-zinc-50 border-t border-zinc-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10 space-y-3">
                <p class="text-xs font-semibold uppercase tracking-widest text-agro-600">{{ __('Por qué fiarte de Agro365') }}</p>
                <h2 class="text-2xl lg:text-3xl font-bold tracking-tight text-zinc-900">{{ __('Construido sobre la normativa, no sobre promesas') }}</h2>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Normativa -->
                <div class="bg-white rounded-2xl p-6 border border-zinc-200">
                    <div class="w-11 h-11 rounded-lg bg-agro-50 text-agro-700 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3 class="font-bold text-zinc-900 mb-1.5">{{ __('Conforme a la ley') }}</h3>
                    <p class="text-sm text-zinc-600 leading-relaxed">{{ __('Diseñado según el Reglamento de Ejecución (UE) 2022/1441 y la obligación del cuaderno digital 2027–2028.') }}</p>
                </div>
                <!-- Datos -->
                <div class="bg-white rounded-2xl p-6 border border-zinc-200">
                    <div class="w-11 h-11 rounded-lg bg-agro-50 text-agro-700 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </div>
                    <h3 class="font-bold text-zinc-900 mb-1.5">{{ __('Tus datos, siempre tuyos') }}</h3>
                    <p class="text-sm text-zinc-600 leading-relaxed">{{ __('Cumplimiento RGPD, exportables en cualquier momento y sin permanencia. Si te vas, te los llevas.') }}</p>
                </div>
                <!-- Firma -->
                <div class="bg-white rounded-2xl p-6 border border-zinc-200">
                    <div class="w-11 h-11 rounded-lg bg-agro-50 text-agro-700 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <h3 class="font-bold text-zinc-900 mb-1.5">{{ __('Informes con validez') }}</h3>
                    <p class="text-sm text-zinc-600 leading-relaxed">{{ __('Firma electrónica SHA-256 y código QR de verificación, listos para inspecciones PAC.') }}</p>
                </div>
                <!-- España -->
                <div class="bg-white rounded-2xl p-6 border border-zinc-200">
                    <div class="w-11 h-11 rounded-lg bg-agro-50 text-agro-700 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 10c0 7-9 12-9 12s-9-5-9-12a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    </div>
                    <h3 class="font-bold text-zinc-900 mb-1.5">{{ __('Hecho en España') }}</h3>
                    <p class="text-sm text-zinc-600 leading-relaxed">{{ __('Desarrollado junto a viticultores reales, con soporte en español y trato directo con el equipo.') }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Ecosistema Conectado -->
    <section id="ecosistema" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-14 space-y-4">
                <p class="text-xs font-semibold uppercase tracking-widest text-agro-600">{{ __('Ecosistema conectado') }}</p>
                <h2 class="text-3xl lg:text-4xl font-bold tracking-tight text-zinc-900">{{ __('Una sola plataforma. Cuatro roles. Todo conectado.') }}</h2>
                <p class="text-lg text-zinc-600 max-w-3xl mx-auto">
                    Agro365 conecta toda la cadena del vino en un ecosistema compartido.
                    Cada actor ve solo lo que le corresponde y los datos fluyen solos —
                    {{ __('sin Excel, sin papel, sin duplicidades.') }}
                </p>
            </div>

            <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-6">
                <!-- Viticultor -->
                <div class="bg-white rounded-2xl p-7 border border-zinc-200 hover:border-agro-400 hover:shadow-md transition-all duration-300 flex flex-col">
                    <div class="w-11 h-11 rounded-lg bg-agro-50 text-agro-700 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-zinc-900">{{ __('Viticultor') }}</h3>
                    <p class="text-xs font-semibold text-agro-600 uppercase tracking-wide mt-1 mb-3">{{ __('Básico gratis · Completo 9€ · Independiente 14€') }}</p>
                    <p class="text-sm text-zinc-600 leading-relaxed mb-4">
                        Registra tratamientos, riegos y labores desde el móvil. Trabaja solo o comparte tu cuaderno con una o varias bodegas.
                        <strong class="text-zinc-800">{{ __('Tus datos son siempre tuyos, aunque cambies de bodega.') }}</strong>
                    </p>
                    <ul class="space-y-2 text-sm text-zinc-600 mb-6">
                        @foreach(['Cuaderno obligatorio 2027', 'SIGPAC y gestión de parcelas', 'Teledetección NDVI satelital', 'Facturación + Verifactu'] as $item)
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-agro-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>{{ __($item) }}</li>
                        @endforeach
                    </ul>
                    <div class="mt-auto space-y-2">
                        <a href="{{ route('register', ['role' => 'viticulturist']) }}" rel="nofollow" class="inline-flex items-center gap-1.5 text-agro-700 font-semibold text-sm hover:underline">
                            Empezar gratis
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                        <a href="{{ content_route('content.viticultores') }}" class="block text-zinc-500 text-sm hover:text-zinc-700 hover:underline">Ver más sobre viticultores</a>
                    </div>
                </div>

                <!-- Bodega -->
                <div class="bg-white rounded-2xl p-7 border border-zinc-200 hover:border-agro-400 hover:shadow-md transition-all duration-300 flex flex-col">
                    <div class="w-11 h-11 rounded-lg bg-agro-50 text-agro-700 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 22h8M7 10h10M12 15v7M12 15a5 5 0 0 0 5-5c0-2-.5-4-2-8H9c-1.5 4-2 6-2 8a5 5 0 0 0 5 5Z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-zinc-900">{{ __('Bodega') }}</h3>
                    <p class="text-xs font-semibold text-agro-600 uppercase tracking-wide mt-1 mb-3">{{ __('Gratis dentro de una DO · desde 19€/mes independiente') }}</p>
                    <p class="text-sm text-zinc-600 leading-relaxed mb-4">
                        Conecta con tus viticultores y recibe sus cuadernos en tiempo real.
                        <strong class="text-zinc-800">{{ __('Invita a tus proveedores') }}</strong> — acceden en modo básico gratis o completo por 9€/mes.
                    </p>
                    <ul class="space-y-2 text-sm text-zinc-600 mb-6">
                        @foreach(['Recepción de uva y pesaje', 'Depósitos y elaboración', 'AICA, INFOVI y libros de bodega', 'Panel de viticultores en tiempo real'] as $item)
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-agro-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>{{ __($item) }}</li>
                        @endforeach
                    </ul>
                    <div class="mt-auto space-y-2">
                        <a href="{{ route('register', ['role' => 'winery']) }}" rel="nofollow" class="inline-flex items-center gap-1.5 text-agro-700 font-semibold text-sm hover:underline">
                            Empezar como bodega
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                        <a href="{{ url('/software-bodegas') }}" class="block text-zinc-500 text-sm hover:text-zinc-700 hover:underline">Ver todo lo que incluye para bodegas</a>
                    </div>
                </div>

                <!-- Productor -->
                <div class="bg-white rounded-2xl p-7 border border-zinc-200 hover:border-agro-400 hover:shadow-md transition-all duration-300 flex flex-col">
                    <div class="w-11 h-11 rounded-lg bg-agro-50 text-agro-700 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.83Z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="m22 17.65-9.17 4.16a2 2 0 0 1-1.66 0L2 17.65"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="m22 12.65-9.17 4.16a2 2 0 0 1-1.66 0L2 12.65"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-zinc-900">{{ __('Productor') }}</h3>
                    <p class="text-xs font-semibold text-agro-600 uppercase tracking-wide mt-1 mb-3">{{ __('19€/mes · Viticultor + Bodega en uno') }}</p>
                    <p class="text-sm text-zinc-600 leading-relaxed mb-4">
                        Cultivas tus uvas y elaboras tu vino. Un solo panel con contexto campo y bodega,
                        <strong class="text-zinc-800">{{ __('sin duplicar cuentas ni pagar dos planes por separado.') }}</strong>
                    </p>
                    <ul class="space-y-2 text-sm text-zinc-600 mb-6">
                        @foreach(['Todo del plan Viticultor Independiente', 'Todo del plan Bodega', 'Trazabilidad viñedo → bodega → botella', 'Una sola cuenta, un precio bundle'] as $item)
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-agro-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>{{ __($item) }}</li>
                        @endforeach
                    </ul>
                    <div class="mt-auto space-y-2">
                        <a href="{{ route('register', ['role' => 'producer']) }}" rel="nofollow" class="inline-flex items-center gap-1.5 text-agro-700 font-semibold text-sm hover:underline">
                            Empezar como productor
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                        <a href="#precios" class="block text-zinc-500 text-sm hover:text-zinc-700 hover:underline">Ver el ahorro del bundle</a>
                    </div>
                </div>

                <!-- DO -->
                <div class="bg-white rounded-2xl p-7 border border-zinc-200 hover:border-agro-400 hover:shadow-md transition-all duration-300 flex flex-col">
                    <div class="w-11 h-11 rounded-lg bg-agro-50 text-agro-700 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 17v-7M9.5 17v-7M14.5 17v-7M19 17v-7M12 3 4 8h16L12 3Z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-zinc-900">{{ __('Denominación de Origen') }}</h3>
                    <p class="text-xs font-semibold text-agro-600 uppercase tracking-wide mt-1 mb-3">{{ __('Desde 149€/mes · Bodegas incluidas gratis') }}</p>
                    <p class="text-sm text-zinc-600 leading-relaxed mb-4">
                        Registra tus bodegas adscritas y supervisa actividad y cumplimiento normativo desde un panel centralizado.
                        <strong class="text-zinc-800">{{ __('Tus bodegas acceden gratis — solo pagas tú.') }}</strong>
                    </p>
                    <ul class="space-y-2 text-sm text-zinc-600 mb-6">
                        @foreach(['Panel de supervisión centralizado', 'Alertas de incumplimiento normativo', 'Informes consolidados por DO', 'Account manager + SLA 99,9%'] as $item)
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-agro-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>{{ __($item) }}</li>
                        @endforeach
                    </ul>
                    <div class="mt-auto space-y-2">
                        <a href="{{ $waDO }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 text-agro-700 font-semibold text-sm hover:underline">
                            Hablar por WhatsApp
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                        <a href="mailto:info@agro365.es?subject=Consulta%20Denominaci%C3%B3n%20de%20Origen%20Agro365" class="block text-zinc-500 text-sm hover:text-zinc-700 hover:underline">o enviar un email</a>
                    </div>
                </div>
            </div>

            <p class="text-center text-sm text-zinc-400 mt-8">{{ __('Una bodega puede gestionar múltiples viticultores. Un viticultor puede trabajar con varias bodegas. El viticultor independiente no necesita bodega ni DO para empezar.') }}</p>
        </div>
    </section>

    <!-- ✅ SEO: Sección de soluciones comerciales directas -->
    <section id="funcionalidades" class="py-20 bg-zinc-50 border-y border-zinc-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl lg:text-4xl font-bold tracking-tight text-zinc-900">{{ __('Todo lo que necesitas para cumplir la normativa 2027') }}</h2>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Card 1: Cuaderno Digital -->
                <a href="{{ content_route('content.cuaderno-digital') }}" class="group bg-white rounded-2xl p-6 shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 hover:border-[var(--color-agro-green-light)]/30">
                    <div class="w-14 h-14 rounded-xl bg-agro-50 flex items-center justify-center text-agro-700 mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-lg text-zinc-900 mb-2">{{ __('Cuaderno de Campo') }}</h3>
                    <p class="text-gray-600 text-sm leading-relaxed mb-4">{{ __('Registro oficial de tratamientos, riegos y fertilización 100% conforme con la normativa 2027.') }}</p>
                    <span class="text-[var(--color-agro-green)] text-sm font-semibold flex items-center gap-1 group-hover:translate-x-1 transition-transform">
                        Saber más
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                </a>

                <!-- Card 2: SIGPAC -->
                <a href="{{ content_route('content.que-es-sigpac') }}" class="group bg-white rounded-2xl p-6 shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 hover:border-[var(--color-agro-green-light)]/30">
                    <div class="w-14 h-14 rounded-xl bg-agro-50 flex items-center justify-center text-agro-700 mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-lg text-zinc-900 mb-2">{{ __('Gestión SIGPAC') }}</h3>
                    <p class="text-gray-600 text-sm leading-relaxed mb-4">{{ __('Control de parcelas, mapas interactivos y códigos oficiales del ministerio integrados.') }}</p>
                    <span class="text-[var(--color-agro-green)] text-sm font-semibold flex items-center gap-1 group-hover:translate-x-1 transition-transform">
                        Saber más
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                </a>

                <!-- Card 3: NDVI -->
                <a href="{{ content_route('content.ndvi-teledeteccion') }}" class="group bg-white rounded-2xl p-6 shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 hover:border-[var(--color-agro-green-light)]/30">
                    <div class="w-14 h-14 rounded-xl bg-agro-50 flex items-center justify-center text-agro-700 mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-lg text-zinc-900 mb-2">{{ __('Teledetección NDVI') }}</h3>
                    <p class="text-gray-600 text-sm leading-relaxed mb-4">{{ __('Análisis satelital de vigor y estrés hídrico de tus parcelas en tiempo real sin sensores.') }}</p>
                    <span class="text-[var(--color-agro-green)] text-sm font-semibold flex items-center gap-1 group-hover:translate-x-1 transition-transform">
                        Saber más
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                </a>

                <!-- Card 4: Facturación -->
                <a href="{{ content_route('content.facturacion-agricola') }}" class="group bg-white rounded-2xl p-6 shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 hover:border-[var(--color-agro-green-light)]/30">
                    <div class="w-14 h-14 rounded-xl bg-agro-50 flex items-center justify-center text-agro-700 mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-lg text-zinc-900 mb-2">{{ __('Facturación Agrícola') }}</h3>
                    <p class="text-gray-600 text-sm leading-relaxed mb-4">{{ __('De la vendimia a la factura en un clic. Gestión de entregas, cosechas y clientes integrada.') }}</p>
                    <span class="text-[var(--color-agro-green)] text-sm font-semibold flex items-center gap-1 group-hover:translate-x-1 transition-transform">
                        Saber más
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                </a>

                <!-- Card 5: Vendimia -->
                <a href="{{ content_route('content.gestion-vendimia') }}" class="group bg-white rounded-2xl p-6 shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 hover:border-[var(--color-agro-green-light)]/30">
                    <div class="w-14 h-14 rounded-xl bg-agro-50 flex items-center justify-center text-agro-700 mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-lg text-zinc-900 mb-2">{{ __('Gestión de Vendimia') }}</h3>
                    <p class="text-gray-600 text-sm leading-relaxed mb-4">{{ __('Control de cosecha por parcela y viticultor, pesaje, calidades y entrega directa a bodega.') }}</p>
                    <span class="text-[var(--color-agro-green)] text-sm font-semibold flex items-center gap-1 group-hover:translate-x-1 transition-transform">
                        Saber más
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                </a>

                <!-- Card 6: Trazabilidad -->
                <a href="{{ content_route('content.trazabilidad-agricola') }}" class="group bg-white rounded-2xl p-6 shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 hover:border-[var(--color-agro-green-light)]/30">
                    <div class="w-14 h-14 rounded-xl bg-agro-50 flex items-center justify-center text-agro-700 mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-lg text-zinc-900 mb-2">{{ __('Trazabilidad Completa') }}</h3>
                    <p class="text-gray-600 text-sm leading-relaxed mb-4">{{ __('Desde la parcela hasta la entrega. Cada lote documentado para auditorías y DO.') }}</p>
                    <span class="text-[var(--color-agro-green)] text-sm font-semibold flex items-center gap-1 group-hover:translate-x-1 transition-transform">
                        Saber más
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                </a>
            </div>
        </div>
    </section>



    <!-- Comparativa Excel/papel vs Agro365 -->
    <section class="py-20 bg-white border-t border-zinc-100">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12 space-y-3">
                <h2 class="text-3xl lg:text-4xl font-bold tracking-tight text-zinc-900">{{ __('Excel y papel vs. Agro365') }}</h2>
                <p class="text-zinc-500 max-w-xl mx-auto">{{ __('Lo que cambia cuando dejas la hoja de cálculo y el cuaderno de tapa dura.') }}</p>
            </div>
            <div class="overflow-hidden rounded-2xl border border-zinc-200 shadow-sm">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-zinc-50 border-b border-zinc-200">
                            <th class="text-left px-5 py-4 font-semibold text-zinc-700">{{ __('Tarea') }}</th>
                            <th class="text-center px-4 py-4 font-semibold text-zinc-500">{{ __('Excel / papel') }}</th>
                            <th class="text-center px-4 py-4 font-semibold text-agro-700">{{ __('Agro365') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @php
                        $rows = [
                            'Cumplir el cuaderno digital obligatorio 2027',
                            'Importar parcelas desde SIGPAC por NIF',
                            'Informes válidos para inspección PAC (firma SHA-256)',
                            'Trazabilidad viñedo → bodega → botella',
                            'Teledetección NDVI del estado del viñedo',
                            'Registrar tratamientos desde el campo, en el móvil',
                            'Datos a salvo ante pérdida, robo o café derramado',
                        ];
                        @endphp
                        @foreach($rows as $row)
                        <tr class="bg-white">
                            <td class="px-5 py-3.5 text-zinc-700">{{ __($row) }}</td>
                            <td class="px-4 py-3.5 text-center">
                                <svg class="w-5 h-5 text-zinc-300 inline-block" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <svg class="w-5 h-5 text-agro-500 inline-block" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="text-center text-xs text-zinc-400 mt-4">{{ __('Excel y Google Sheets cubren algunas de estas funciones con configuración manual. La comparativa refleja el caso de uso típico sin desarrollo adicional.') }}</p>
        </div>
    </section>

    <!-- Programa de Fundadores -->
    <section class="py-20 bg-gradient-to-br from-agro-900 via-agro-800 to-agro-700 relative overflow-hidden">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full border border-white/20 bg-white/10 backdrop-blur-sm mb-6">
                <span class="w-2 h-2 rounded-full bg-amber-400 shrink-0"></span>
                <span class="text-xs font-semibold text-white/80 tracking-wide">{{ __('Programa Fundadores · Plazas limitadas') }}</span>
            </div>
            <h2 class="text-3xl lg:text-5xl font-bold tracking-tight text-white mb-5">
                {{ __('Sé uno de los primeros') }} <span class="text-agro-action">{{ __('viticultores fundadores') }}</span>
            </h2>
            <p class="text-agro-100/90 text-lg max-w-2xl mx-auto mb-8">
                {{ __('Los primeros') }} {{ $foundersTotal }} {{ __('en unirse consiguen') }}
                <strong class="text-white">{{ __('1 año del plan Completo gratis') }}</strong>
                <span class="text-agro-200/70 text-base">({{ __('en lugar de los 3 meses estándar') }})</span>,
                {{ __('acceso directo al equipo y precio de fundador para siempre. A cambio: tu opinión real para mejorar el producto.') }}
            </p>

            {{-- Barra de plazas --}}
            <div class="max-w-md mx-auto mb-8">
                <div class="flex items-center justify-between text-sm text-white/70 mb-2">
                    <span>{{ __('Plazas ocupadas') }}</span>
                    <span class="font-semibold text-white">{{ $foundersTaken }} / {{ $foundersTotal }}</span>
                </div>
                <div class="h-2.5 rounded-full bg-white/15 overflow-hidden">
                    <div class="h-full rounded-full bg-agro-action" style="width: {{ max($foundersPct, 4) }}%"></div>
                </div>
                <p class="text-xs text-white/60 mt-2">{{ __('Quedan') }} <span class="font-semibold text-agro-action">{{ $foundersLeft }}</span> {{ __('plazas de fundador') }}</p>
            </div>

            <div class="flex flex-wrap gap-3 justify-center">
                <a href="{{ $waFounders }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-lg bg-agro-action text-white hover:bg-agro-action-hover transition-colors font-semibold text-base shadow-sm">
                    {{ __('Quiero ser fundador') }}
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                </a>
            </div>
            <p class="text-agro-200/70 text-sm mt-6">{{ __('Sin tarjeta · Sin permanencia · Plazas reales, no marketing') }}</p>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="precios" class="py-20 bg-zinc-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 space-y-4">
                <h2 class="text-3xl lg:text-4xl font-bold tracking-tight text-zinc-900">{{ __('Precio justo para cada perfil') }}</h2>
                <p class="text-xl text-zinc-600 max-w-2xl mx-auto">{{ __('Desde gratis para el viticultor básico hasta planes escalados para Denominaciones de Origen. Sin sorpresas.') }}</p>
            </div>

            <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-8 max-w-7xl mx-auto">

                <!-- Viticultor -->
                <div class="bg-white rounded-2xl p-8 border-2 border-agro-600 relative overflow-hidden transition-all duration-300 shadow-lg">
                    <div class="absolute top-0 right-0">
                        <div class="bg-agro-700 text-white px-5 py-1.5 rounded-bl-2xl font-semibold text-sm">
                            Empieza aquí
                        </div>
                    </div>
                    <div class="mb-5 pt-6">
                        <h3 class="text-2xl font-bold text-zinc-900">{{ __('Viticultor') }}</h3>
                        <p class="text-zinc-500 text-sm mt-1">{{ __('Autoservicio · Sin llamadas · Cancela cuando quieras') }}</p>
                    </div>
                    <div class="mb-6 space-y-2">
                        <!-- Tier básico gratuito -->
                        <div class="p-3 bg-zinc-50 rounded-xl border border-zinc-200">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-zinc-600">{{ __('Básico (cualquier viticultor)') }}</span>
                                <span class="text-base font-bold text-zinc-800">{{ __('Gratis') }}</span>
                            </div>
                            <p class="text-xs text-zinc-400 mt-0.5">{{ __('Cuaderno de campo · gratis para siempre') }}</p>
                        </div>
                        <!-- Tier completo invitado -->
                        <div class="p-3 bg-[var(--color-agro-green-bg)] rounded-xl border border-[var(--color-agro-green-light)]/40">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-[var(--color-agro-green-dark)]">{{ __('Completo — Lo pagas tú o te lo cubre tu bodega') }}</span>
                                <span class="text-base font-bold text-[var(--color-agro-green-dark)]">{{ __('9€/mes') }}</span>
                            </div>
                            <p class="text-xs text-zinc-500 mt-0.5">{{ __('o 85€/año — SIGPAC, PAC, teledetección...') }}</p>
                            <p class="text-xs font-semibold text-[var(--color-agro-green-dark)] mt-1">{{ __('3 meses gratis al registrarte') }}</p>
                        </div>
                        <!-- Tier independiente -->
                        <div class="p-3 bg-[var(--color-agro-green-bg)] rounded-xl border-2 border-[var(--color-agro-green)]">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-[var(--color-agro-green-dark)]">{{ __('Independiente (sin bodega)') }}</span>
                                <span class="text-xl font-bold text-[var(--color-agro-green-dark)]">{{ __('14€/mes') }}</span>
                            </div>
                            <p class="text-xs text-zinc-500 mt-0.5">{{ __('o 130€/año — acceso completo') }}</p>
                            <p class="text-xs font-semibold text-[var(--color-agro-green-dark)] mt-1">{{ __('3 meses gratis al registrarte') }}</p>
                        </div>
                    </div>
                    <ul class="space-y-3 mb-8 text-sm">
                        @foreach(['Cuaderno de campo digital (obligatorio 2027)', 'SIGPAC y gestión de parcelas', 'Teledetección NDVI satelital', 'PAC y normativa vigente', 'Facturación agrícola + Verifactu', 'Vendimias y plantaciones', 'Soporte por email (48h)'] as $feature)
                        <li class="flex items-start gap-2.5">
                            <svg class="w-5 h-5 text-[var(--color-agro-green)] flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-zinc-700">{{ $feature }}</span>
                        </li>
                        @endforeach
                    </ul>
                    <a href="{{ route('register', ['role' => 'viticulturist']) }}" class="block w-full text-center px-6 py-4 rounded-xl bg-agro-700 text-white hover:bg-agro-600 transition-colors duration-200 shadow-sm font-semibold">
                        Comenzar Gratis
                    </a>
                    <p class="text-center text-xs text-zinc-400 mt-3">{{ __('Básico gratis siempre · 3 meses gratis en planes de pago · Sin tarjeta') }}</p>
                </div>

                <!-- Bodega -->
                <div class="bg-white rounded-2xl p-8 border border-zinc-200 hover:border-zinc-300 transition-all duration-300 shadow-sm">
                    <div class="mb-5">
                        <h3 class="text-2xl font-bold text-zinc-900">{{ __('Bodega') }}</h3>
                        <p class="text-zinc-500 text-sm mt-1">{{ __('Demo gratuita · Onboarding incluido') }}</p>
                    </div>
                    <div class="mb-6 space-y-2">
                        <!-- Bodega en DO -->
                        <div class="p-3 bg-zinc-50 rounded-xl border border-zinc-200">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-zinc-600">{{ __('Dentro de una Denominación de Origen') }}</span>
                                <span class="text-base font-bold text-zinc-800">{{ __('Gratis') }}</span>
                            </div>
                            <p class="text-xs text-zinc-400 mt-0.5">{{ __('Cubierta por el paquete de la DO') }}</p>
                        </div>
                        <!-- Bodega independiente -->
                        <div class="p-3 bg-agro-50 rounded-xl border-2 border-agro-500">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-agro-700">{{ __('Independiente (sin DO)') }}</span>
                                <span class="text-xl font-bold text-zinc-900">{{ __('desde 19€/mes') }}</span>
                            </div>
                            <p class="text-xs text-zinc-500 mt-0.5">{{ __('Escala con tu red: 19€ (solo tu vino) · 29€ (1-10 viticultores) · 49€ (11-30) · …') }}</p>
                            <p class="text-xs font-semibold text-agro-700 mt-1">{{ __('3 meses gratis al registrarte') }}</p>
                        </div>
                    </div>
                    <ul class="space-y-3 mb-8 text-sm">
                        @foreach(['Panel de viticultores en tiempo real', 'Gestión completa de vendimia', 'Trazabilidad desde cepa hasta botella', 'Facturación agrícola integrada', 'Gestión de vinos y elaboración', 'Comparativa rendimientos real vs estimado', 'Soporte prioritario (24h)', 'Onboarding personalizado incluido'] as $feature)
                        <li class="flex items-start gap-2.5">
                            <svg class="w-5 h-5 text-agro-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-zinc-700">{{ $feature }}</span>
                        </li>
                        @endforeach
                    </ul>
                    <a href="{{ route('register', ['role' => 'winery']) }}" class="block w-full text-center px-6 py-4 rounded-xl bg-agro-700 text-white hover:bg-agro-600 transition-colors duration-200 shadow-sm font-semibold">
                        Comenzar Gratis
                    </a>
                    <p class="text-center text-xs text-zinc-400 mt-3">{{ __('3 meses gratis · Sin tarjeta requerida') }}</p>
                </div>

                <!-- Productor -->
                <div class="bg-white rounded-2xl p-8 border border-zinc-200 hover:border-zinc-300 transition-all duration-300 relative overflow-hidden shadow-sm">
                    <div class="absolute top-0 right-0">
                        <div class="bg-zinc-900 text-white px-5 py-1.5 rounded-bl-2xl font-semibold text-sm">
                            Bundle
                        </div>
                    </div>
                    <div class="mb-5 pt-6">
                        <h3 class="text-2xl font-bold text-zinc-900">{{ __('Productor') }}</h3>
                        <p class="text-zinc-500 text-sm mt-1">{{ __('Cultivas y elaboras tu propio vino') }}</p>
                    </div>
                    <div class="mb-6 space-y-2">
                        <!-- Tier básico gratuito -->
                        <div class="p-3 bg-zinc-50 rounded-xl border border-zinc-200">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-zinc-600">{{ __('Básico') }}</span>
                                <span class="text-base font-bold text-zinc-800">{{ __('Gratis') }}</span>
                            </div>
                            <p class="text-xs text-zinc-400 mt-0.5">{{ __('Cuaderno de campo, parcelas y SIGPAC · gratis para siempre') }}</p>
                        </div>
                        <!-- Precio bundle -->
                        <div class="p-3 bg-agro-50 rounded-xl border-2 border-agro-500">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-agro-700">{{ __('Viticultor + Bodega') }}</span>
                                <span class="text-xl font-bold text-zinc-900">{{ __('19€/mes') }}</span>
                            </div>
                            <p class="text-xs text-zinc-500 mt-0.5">{{ __('o 180€/año — ~15€/mes') }}</p>
                            <p class="text-xs font-semibold text-agro-700 mt-1">{{ __('3 meses gratis al registrarte') }}</p>
                        </div>
                        <!-- Ahorro -->
                        <div class="p-3 bg-zinc-50 rounded-xl border border-zinc-200 text-center">
                            <p class="text-xs text-zinc-500">Por separado: <span class="line-through text-zinc-400 font-semibold">{{ __('33€/mes') }}</span></p>
                            <p class="text-xs font-semibold text-agro-700 mt-0.5">{{ __('Ahorras 14€/mes · 42% de descuento') }}</p>
                        </div>
                    </div>
                    <ul class="space-y-3 mb-8 text-sm">
                        @foreach(['Todo del plan Viticultor Independiente', 'Todo del plan Bodega', 'Trazabilidad viñedo → bodega → botella'] as $feature)
                        <li class="flex items-start gap-2.5">
                            <svg class="w-5 h-5 text-agro-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-zinc-700">{{ $feature }}</span>
                        </li>
                        @endforeach
                    </ul>
                    <a href="{{ route('register', ['role' => 'producer']) }}" class="block w-full text-center px-6 py-4 rounded-xl bg-agro-700 text-white hover:bg-agro-600 transition-colors duration-200 shadow-sm font-semibold">
                        Comenzar Gratis
                    </a>
                    <p class="text-center text-xs text-zinc-400 mt-3">{{ __('3 meses gratis · Sin tarjeta requerida') }}</p>
                </div>

                <!-- DO -->
                <div class="bg-white rounded-2xl p-8 border border-zinc-200 hover:border-zinc-300 transition-all duration-300 shadow-sm">
                    <div class="mb-5">
                        <h3 class="text-2xl font-bold text-zinc-900">{{ __('Denominación de Origen') }}</h3>
                        <p class="text-zinc-500 text-sm mt-1">{{ __('Solución a medida · Contrato anual') }}</p>
                    </div>
                    <div class="mb-6 rounded-xl border border-zinc-200 overflow-hidden">
                        <table class="w-full text-xs">
                            <thead>
                                <tr class="bg-zinc-50 border-b border-zinc-200">
                                    <th class="text-left px-3 py-2 font-semibold text-zinc-700">{{ __('Bodegas') }}</th>
                                    <th class="text-right px-3 py-2 font-semibold text-zinc-700">{{ __('Mensual') }}</th>
                                    <th class="text-right px-3 py-2 font-semibold text-zinc-700">{{ __('Anual') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100 bg-white">
                                <tr><td class="px-3 py-1.5 text-zinc-700">{{ __('Hasta 25') }}</td><td class="px-3 py-1.5 text-right font-semibold text-zinc-900">149€</td><td class="px-3 py-1.5 text-right text-zinc-500">{{ __('1.400€/año') }}</td></tr>
                                <tr><td class="px-3 py-1.5 text-zinc-700">26 – 50</td><td class="px-3 py-1.5 text-right font-semibold text-zinc-900">249€</td><td class="px-3 py-1.5 text-right text-zinc-500">{{ __('2.350€/año') }}</td></tr>
                                <tr><td class="px-3 py-1.5 text-zinc-700">51 – 75</td><td class="px-3 py-1.5 text-right font-semibold text-zinc-900">349€</td><td class="px-3 py-1.5 text-right text-zinc-500">{{ __('3.300€/año') }}</td></tr>
                                <tr><td class="px-3 py-1.5 text-zinc-700">76 – 100</td><td class="px-3 py-1.5 text-right font-semibold text-zinc-900">449€</td><td class="px-3 py-1.5 text-right text-zinc-500">{{ __('4.250€/año') }}</td></tr>
                                <tr><td class="px-3 py-1.5 text-zinc-700">+100</td><td class="px-3 py-1.5 text-right font-semibold text-zinc-900" colspan="2">{{ __('A negociar') }}</td></tr>
                            </tbody>
                        </table>
                        <p class="text-xs text-zinc-400 text-center py-2 bg-zinc-50 border-t border-zinc-100">{{ __('Bodegas adscritas sin coste adicional') }}</p>
                    </div>
                    <ul class="space-y-3 mb-8 text-sm">
                        @foreach(['Todo del plan Bodega', 'Alta y gestión de bodegas adscritas', 'Panel de supervisión centralizado', 'Alertas automáticas de incumplimiento', 'Informes consolidados por denominación', 'Firma electrónica SHA-256', 'API para integración con sistemas actuales', 'Account manager dedicado', 'SLA 99,9% uptime garantizado'] as $feature)
                        <li class="flex items-start gap-2.5">
                            <svg class="w-5 h-5 text-agro-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-zinc-700">{{ $feature }}</span>
                        </li>
                        @endforeach
                    </ul>
                    <a href="{{ $waDO }}" target="_blank" rel="noopener noreferrer" class="flex items-center justify-center gap-2 w-full text-center px-6 py-4 rounded-xl bg-agro-700 hover:bg-agro-600 text-white transition-colors duration-200 font-semibold">
                        <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        Hablar por WhatsApp
                    </a>
                    <a href="mailto:info@agro365.es?subject=Consulta%20Denominaci%C3%B3n%20de%20Origen%20Agro365" class="flex items-center justify-center gap-2 w-full text-center px-6 py-3 rounded-xl border border-zinc-300 text-zinc-700 hover:bg-zinc-50 transition-colors duration-200 font-medium text-sm mt-2">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        Enviar un email
                    </a>
                    <p class="text-center text-xs text-zinc-400 mt-3">{{ __('Propuesta sin compromiso en 24h') }}</p>
                </div>
            </div>

            <!-- Notas explicativas -->
            <div class="mt-10 max-w-4xl mx-auto grid sm:grid-cols-2 gap-4">
                <div class="text-center p-5 bg-zinc-50 rounded-2xl border border-zinc-200">
                    <p class="text-zinc-700 text-sm">
                        <strong>{{ __('Cómo funciona con viticultores invitados:') }}</strong> la bodega invita a sus proveedores.
                        El viticultor accede en modo <strong>{{ __('básico gratis') }}</strong> o activa el <strong>{{ __('plan completo por 9€/mes') }}</strong>
                        (SIGPAC, teledetección, PAC, facturación...). La bodega no paga por ello — el viticultor decide.
                    </p>
                </div>
                <div class="text-center p-5 bg-zinc-50 rounded-2xl border border-zinc-200">
                    <p class="text-zinc-700 text-sm">
                        <strong>{{ __('¿Eres viticultor y bodega a la vez?') }}</strong> El plan Productor incluye
                        <strong>{{ __('todo el cuaderno de campo más toda la gestión de bodega') }}</strong> en una sola cuenta.
                        <span class="text-agro-700 font-semibold">{{ __('19€/mes') }}</span> frente a los 33€/mes que costaría contratar los dos planes por separado.
                    </p>
                </div>
            </div>

            <p class="text-center text-zinc-500 mt-6 text-sm">
                ¿Tienes dudas? <a href="mailto:info@agro365.es" class="text-[var(--color-agro-green)] hover:underline font-semibold">Escríbenos</a> — respondemos en menos de 24h.
            </p>
        </div>
    </section>


    <!-- FAQ Section (Schema markup para rich snippets en Google) -->
    <section id="faq" class="py-20 bg-white">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl lg:text-4xl font-bold tracking-tight text-zinc-900">{{ __('Preguntas frecuentes') }}</h2>
                <p class="text-zinc-500 mt-3">{{ __('Las dudas más habituales antes de empezar') }}</p>
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
                        'Depende del perfil. El cuaderno de campo básico es gratis para cualquier viticultor, esté o no vinculado a una bodega. Si tu bodega te ha invitado y quieres funciones completas (SIGPAC, teledetección, PAC, facturación...) pagas 9€/mes o 85€/año. Si eres viticultor independiente sin bodega asociada, el plan completo cuesta 14€/mes o 130€/año. En ambos casos los primeros 3 meses son gratis al registrarte — sin tarjeta de crédito.',
                    ],
                    [
                        '¿Qué es el plan Productor y para quién es?',
                        'El plan Productor es un bundle diseñado para quien cultiva sus propias uvas y además elabora su propio vino. El cuaderno de campo, parcelas y SIGPAC son gratis para siempre. Para el resto de funcionalidades (teledetección, PAC, vendimia, depósitos, trazabilidad, facturación...) el plan completo cuesta 19€/mes o 180€/año — frente a los 28€/mes que costaría contratar el plan Viticultor Independiente y el plan Bodega por separado. Todo en una sola cuenta con un panel unificado.',
                    ],
                    [
                        '¿Puedo ser viticultor independiente y al mismo tiempo pertenecer a una bodega?',
                        'Sí. En Agro365 puedes gestionar tu cuaderno de campo de forma completamente independiente y compartirlo con una o varias bodegas simultáneamente. Tus datos son siempre tuyos: si te desconectas de una bodega, tu histórico permanece intacto.',
                    ],
                    [
                        '¿Los viticultores de mi bodega tienen que pagar su propio plan?',
                        'No para el uso básico. El viticultor invitado accede al cuaderno de campo gratis. Si quiere funciones avanzadas (SIGPAC, teledetección, PAC, facturación...) paga 9€/mes por su cuenta — la bodega no asume ese coste. Tú como bodega solo pagas tu cuota fija (desde 19€/mes si eres independiente, escala según el número de viticultores gestionados, o gratis si estás dentro de una DO).',
                    ],
                    [
                        '¿Los informes que genera Agro365 son válidos para inspecciones PAC?',
                        'Sí. Los informes se generan con firma electrónica SHA-256 y código QR de verificación, cumpliendo con los requisitos de las inspecciones PAC y con la normativa de cuaderno de campo digital 2027.',
                    ],
                    [
                        '¿Cuánto tiempo lleva configurar Agro365?',
                        'Para un viticultor o productor, menos de 5 minutos: creas tu cuenta, importas tus parcelas SIGPAC y empiezas a registrar. Para una bodega, productor o DO, ofrecemos onboarding personalizado incluido en el plan.',
                    ],
                    [
                        '¿Qué pasa con mis datos si cancelo la suscripción?',
                        'Tus datos son siempre tuyos. Puedes exportarlos en formato estándar en cualquier momento antes o después de cancelar. Nunca retenemos información.',
                    ],
                ];
                @endphp

                @foreach($faqs as $faq)
                <div itemscope itemprop="mainEntity" itemtype="https://schema.org/Question"
                     class="border border-zinc-200 rounded-xl overflow-hidden">
                    <details class="group">
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
    <section class="py-20 bg-agro-800">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <p class="text-agro-300 text-sm font-semibold uppercase tracking-widest mb-5">{{ __('Básico obligatorio 2027 · Completo 2028') }}</p>
            <h2 class="text-3xl lg:text-5xl font-bold tracking-tight text-white mb-5">
                La normativa no espera.<br>Tu viñedo tampoco.
            </h2>
            <p class="text-agro-100/90 text-lg mb-10 max-w-2xl mx-auto">{{ __('Viticultor básico gratis · Bodega desde 19€/mes · Productor 19€/mes · DO desde 149€/mes.') }}</p>
            <div class="flex flex-wrap gap-3 justify-center">
                <a href="{{ route('register') }}" rel="nofollow" class="inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-lg bg-white text-agro-800 hover:bg-agro-50 transition-colors font-semibold text-base shadow-sm">
                    Empezar gratis
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>
                <a href="{{ $waDefault }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-lg border border-white/40 text-white hover:bg-white/10 transition-colors font-semibold text-base">
                    Hablar con nosotros
                </a>
            </div>
            <p class="text-agro-200/70 text-sm mt-8">{{ __('Sin tarjeta requerida · Configuración en 5 minutos · Soporte en español') }}</p>
        </div>
    </section>

    @include('partials.footer-seo')

    <!-- Barra sticky inferior -->
    <div id="sticky-cta"
         class="fixed bottom-0 left-0 right-0 z-40 translate-y-full transition-transform duration-300 bg-agro-800/95 backdrop-blur-md border-t border-agro-700 shadow-[0_-4px_24px_rgba(0,0,0,0.18)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex items-center justify-between gap-4">
            <p class="text-sm text-white/70 hidden sm:block">
                <span class="text-amber-400 font-semibold">Quedan {{ $daysLeft }} días</span>
                · Cuaderno digital obligatorio 2027
            </p>
            <div class="flex items-center gap-3 w-full sm:w-auto justify-center sm:justify-end">
                <a href="{{ route('login') }}" rel="nofollow"
                   class="text-white/60 hover:text-white text-sm font-medium transition-colors">
                    Iniciar sesión
                </a>
                <a href="{{ route('register') }}" rel="nofollow"
                   class="inline-flex items-center gap-1.5 px-5 py-2 rounded-lg bg-agro-action text-white hover:bg-agro-action-hover transition-colors font-semibold text-sm">
                    Empezar gratis →
                </a>
            </div>
        </div>
    </div>

    <!-- Botón flotante WhatsApp -->
    <a href="{{ $waDefault }}"
       target="_blank" rel="noopener noreferrer"
       class="fixed bottom-20 right-5 z-50 w-14 h-14 rounded-full bg-[#25D366] shadow-xl flex items-center justify-center hover:scale-110 transition-transform duration-200"
       aria-label="Contactar por WhatsApp">
        <svg viewBox="0 0 24 24" class="w-7 h-7 fill-white" xmlns="http://www.w3.org/2000/svg">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
        </svg>
    </a>

    <!-- Banner de cookies / privacidad -->
    {{-- Nota: la analítica (Plausible) es cookieless y está exenta de consentimiento.
         Este aviso informa del uso de almacenamiento técnico. Si en el futuro se añaden
         herramientas con cookies (Meta Pixel, GA4...), convertir en aceptar/rechazar y
         GATEAR la carga de esos scripts según la elección guardada. --}}
    <div x-data="{ show: false }"
         x-init="show = ! localStorage.getItem('cookieConsent')"
         x-show="show"
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="fixed bottom-4 left-4 right-4 sm:right-auto sm:max-w-sm z-[60] bg-white rounded-2xl border border-zinc-200 shadow-2xl p-5">
        <p class="text-sm text-zinc-700 leading-relaxed">
            {{ __('Usamos analítica anónima sin cookies para entender cómo se usa la web. No rastreamos datos personales.') }}
            <a href="{{ route('cookies') }}" class="text-agro-700 font-semibold hover:underline">{{ __('Más información') }}</a>.
        </p>
        <div class="flex items-center gap-2 mt-4">
            <button @click="localStorage.setItem('cookieConsent', '1'); show = false"
                    class="flex-1 px-4 py-2.5 rounded-lg bg-agro-700 text-white hover:bg-agro-600 transition-colors font-semibold text-sm">
                {{ __('Entendido') }}
            </button>
            <a href="{{ route('cookies') }}"
               class="px-4 py-2.5 rounded-lg border border-zinc-300 text-zinc-600 hover:bg-zinc-50 transition-colors font-medium text-sm">
                {{ __('Ajustes') }}
            </a>
        </div>
    </div>

    <script>
    // ── Scroll reveal ──────────────────────────────────────────
    (function () {
        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach(e => {
                if (!e.isIntersecting) return;
                e.target.classList.add('revealed');
                revealObserver.unobserve(e.target);
            });
        }, { threshold: 0.1 });

        document.querySelectorAll(
            '#como-funciona .rounded-2xl, ' +
            '#ecosistema .rounded-2xl, #ecosistema .rounded-xl, ' +
            '#funcionalidades .rounded-2xl, #funcionalidades .rounded-xl, ' +
            '#precios .rounded-2xl, ' +
            '#faq details'
        ).forEach((el, i) => {
            el.classList.add('reveal-ready');
            el.style.transitionDelay = (i % 4 * 80) + 'ms';
            revealObserver.observe(el);
        });
    })();

    // ── Barra sticky inferior + pausa animaciones hero ────────
    (function () {
        const bar  = document.getElementById('sticky-cta');
        const hero = document.querySelector('.hero-agro');
        if (!hero) return;
        new IntersectionObserver(([e]) => {
            if (bar) {
                bar.classList.toggle('translate-y-full', e.isIntersecting);
                bar.classList.toggle('translate-y-0',    !e.isIntersecting);
            }
            hero.classList.toggle('animations-paused', !e.isIntersecting);
        }, { threshold: 0 }).observe(hero);
    })();

    </script>

</body>
</html>

 
 