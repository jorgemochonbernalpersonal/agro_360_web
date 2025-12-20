<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- SEO Meta Tags -->
    <title>Agro365 - Software de Gestión Agrícola para Viñedos y Bodegas | Cuaderno Digital SIGPAC</title>
    <meta name="description" content="Software de gestión agrícola profesional para viticultores y bodegas. Cuaderno de campo digital, control de parcelas SIGPAC, gestión de actividades y cumplimiento normativo. 6 meses gratis.">
    <meta name="keywords" content="gestión agrícola, software viñedos, cuaderno digital, cuaderno de campo, SIGPAC, gestión parcelas, software viticultura, app para agricultores, gestión bodega, software para bodegas">
    <meta name="author" content="Agro365">
    <meta name="robots" content="index, follow">
    <meta name="language" content="Spanish">
    <meta name="revisit-after" content="7 days">
    
    <!-- Canonical URL -->
    <meta name="canonical" href="<?php echo e(url('/')); ?>">
    <link rel="canonical" href="<?php echo e(url('/')); ?>">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo e(url('/')); ?>">
    <meta property="og:title" content="Agro365 - Gestión Agrícola Profesional para Viñedos">
    <meta property="og:description" content="Digitaliza tu cuaderno de campo, gestiona parcelas SIGPAC y controla todas las actividades de tu viñedo. Prueba gratis 6 meses.">
    <meta property="og:image" content="<?php echo e(asset('images/logo.png')); ?>">
    <meta property="og:locale" content="es_ES">
    <meta property="og:site_name" content="Agro365">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="<?php echo e(url('/')); ?>">
    <meta name="twitter:title" content="Agro365 - Software de Gestión Agrícola para Viñedos">
    <meta name="twitter:description" content="Cuaderno digital, SIGPAC, control de parcelas. 6 meses gratis para beta testers.">
    <meta name="twitter:image" content="<?php echo e(asset('images/logo.png')); ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo e(asset('images/logo.png')); ?>">
    <link rel="apple-touch-icon" href="<?php echo e(asset('images/logo.png')); ?>">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    
    <!-- Styles -->
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    
    <!-- JSON-LD Structured Data for SEO -->
    <script type="application/ld+json">
    <?php
        $structuredData = [
            '@context' => 'https://schema.org',
            '@type' => 'SoftwareApplication',
            'name' => 'Agro365',
            'applicationCategory' => 'BusinessApplication',
            'offers' => [
                '@type' => 'Offer',
                'price' => '9.00',
                'priceCurrency' => 'EUR',
                'priceValidUntil' => '2025-12-31'
            ],
            'description' => 'Software de gestión agrícola profesional para viticultores y bodegas con cuaderno de campo digital, SIGPAC e integración completa',
            'operatingSystem' => 'Web',
            'aggregateRating' => [
                '@type' => 'AggregateRating',
                'ratingValue' => '4.9',
                'ratingCount' => '50'
            ]
        ];
    ?>
    <?php echo json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT); ?>

    </script>
    
    <!-- Organization Schema -->
    <script type="application/ld+json">
    <?php
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
                'email' => 'soporte@agro365.com',
                'contactType' => 'customer service',
                'availableLanguage' => ['Spanish']
            ],
            'sameAs' => []
        ];
    ?>
    <?php echo json_encode($organizationData, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT); ?>

    </script>
</head>
<body class="bg-gradient-to-br from-[var(--color-agro-green-bg)] via-white to-[var(--color-agro-green-bright)]/30 min-h-screen">
    
    <!-- Navigation Header -->
    <nav class="glass-card border-b border-gray-200/50 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Logo -->
                <a href="<?php echo e(url('/')); ?>" class="flex items-center">
                    <img 
                        src="<?php echo e(asset('images/logo.png')); ?>" 
                        alt="Agro365 - Software de gestión agrícola para viñedos y bodegas" 
                        class="h-20 w-auto object-contain"
                    >
                </a>
                
                <!-- Auth Links -->
                <div class="flex items-center gap-4">
                    <a href="<?php echo e(route('login')); ?>" class="text-[var(--color-agro-green-dark)] hover:text-[var(--color-agro-green)] font-semibold transition-colors duration-300">
                        Iniciar Sesión
                    </a>
                    <a href="<?php echo e(route('register')); ?>" class="px-6 py-3 rounded-xl bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:from-[var(--color-agro-green)] hover:to-[var(--color-agro-green-dark)] transition-all duration-300 shadow-lg hover:shadow-xl font-semibold">
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
                        Gestiona tu viñedo con
                        <span class="bg-gradient-to-r from-[var(--color-agro-green)] to-[var(--color-agro-green-light)] bg-clip-text text-transparent">tecnología</span>
                    </h1>
                    
                    <p class="text-xl text-gray-600 leading-relaxed">
                        Digitaliza tu cuaderno de campo, gestiona parcelas, controla actividades agrícolas y cumple con la normativa. Todo en una plataforma fácil de usar.
                    </p>
                    
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="<?php echo e(route('register')); ?>" class="group inline-flex items-center justify-center gap-2 px-8 py-4 rounded-xl bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:from-[var(--color-agro-green)] hover:to-[var(--color-agro-green-dark)] transition-all duration-300 shadow-lg hover:shadow-xl font-semibold text-lg">
                            Comenzar Gratis
                            <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                        </a>
                        <a href="#features" class="inline-flex items-center justify-center gap-2 px-8 py-4 rounded-xl border-2 border-[var(--color-agro-green-dark)] text-[var(--color-agro-green-dark)] hover:bg-[var(--color-agro-green-dark)] hover:text-white transition-all duration-300 font-semibold text-lg">
                            Ver Características
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
                            <!-- Dashboard Screenshot -->
                            <img 
                                src="<?php echo e(asset('images/dashboard-preview.png')); ?>" 
                                alt="Panel de control de Agro365 mostrando gestión de parcelas, viñedos, actividades agrícolas y cuaderno de campo digital" 
                                class="w-full h-auto object-cover"
                                loading="lazy"
                            >
                        </div>
                        <!-- Decorative Elements -->
                        <div class="absolute -top-4 -right-4 w-24 h-24 rounded-2xl bg-gradient-to-br from-[var(--color-agro-green-light)] to-[var(--color-agro-green)] opacity-20 animate-pulse blur-xl"></div>
                        <div class="absolute -bottom-4 -left-4 w-20 h-20 rounded-full bg-gradient-to-br from-[var(--color-agro-yellow)] to-[var(--color-agro-brown)] opacity-20 animate-pulse blur-xl" style="animation-delay: 1s;"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-white/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 space-y-4">
                <h2 class="text-4xl lg:text-5xl font-bold text-[var(--color-agro-green-dark)]">
                    Todo lo que necesitas para gestionar tu explotación
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Herramientas profesionales diseñadas específicamente para viticultores y agricultores modernos
                </p>
            </div>
            
            <div class="grid md:grid-cols-2 gap-8">
                <!-- Feature 1: Gestión de Parcelas -->
                <div class="glass-card rounded-xl p-8 hover-lift border-2 border-transparent hover:border-[var(--color-agro-green-light)]/50 transition-all duration-300">
                    <div class="w-16 h-16 rounded-xl bg-gradient-to-br from-[var(--color-agro-green-light)] to-[var(--color-agro-green)] flex items-center justify-center shadow-md mb-6">
                        <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-2xl text-[var(--color-agro-green-dark)] mb-3">Gestión de Parcelas</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Organiza y controla todas tus parcelas agrícolas. Registro de áreas, variedades de uva, estados y visualización de mapas interactivos.
                    </p>
                </div>
                
                <!-- Feature 2: Cuaderno Digital -->
                <div class="glass-card rounded-xl p-8 hover-lift border-2 border-transparent hover:border-[var(--color-agro-green-light)]/50 transition-all duration-300">
                    <div class="w-16 h-16 rounded-xl bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center shadow-md mb-6">
                        <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-2xl text-[var(--color-agro-green-dark)] mb-3">Cuaderno de Campo Digital</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Registra tratamientos fitosanitarios, fertilización, riego y actividades culturales. Cumple con la normativa vigente.
                    </p>
                </div>
                
                <!-- Feature 3: SIGPAC -->
                <div class="glass-card rounded-xl p-8 hover-lift border-2 border-transparent hover:border-[var(--color-agro-green-light)]/50 transition-all duration-300">
                    <div class="w-16 h-16 rounded-xl bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center shadow-md mb-6">
                        <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-2xl text-[var(--color-agro-green-dark)] mb-3">Códigos SIGPAC</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Integración completa con SIGPAC para identificación precisa de parcelas y cumplimiento de requisitos administrativos.
                    </p>
                </div>
                
                <!-- Feature 4: Actividades -->
                <div class="glass-card rounded-xl p-8 hover-lift border-2 border-transparent hover:border-[var(--color-agro-green-light)]/50 transition-all duration-300">
                    <div class="w-16 h-16 rounded-xl bg-gradient-to-br from-amber-400 to-amber-600 flex items-center justify-center shadow-md mb-6">
                        <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-2xl text-[var(--color-agro-green-dark)] mb-3">Control de Actividades</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Seguimiento detallado de todas las actividades agrícolas. Histórico completo y reportes personalizados.
                    </p>
                </div>
                
                <!-- Feature 5: Variedades de Uva -->
                <div class="glass-card rounded-xl p-8 hover-lift border-2 border-transparent hover:border-[var(--color-agro-green-light)]/50 transition-all duration-300">
                    <div class="w-16 h-16 rounded-xl bg-gradient-to-br from-rose-400 to-rose-600 flex items-center justify-center shadow-md mb-6">
                        <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-2xl text-[var(--color-agro-green-dark)] mb-3">Gestión de Variedades</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Controla las variedades de uva plantadas, fechas de plantación, áreas y estados de cada plantación.
                    </p>
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
                    
                    <a href="<?php echo e(route('register')); ?>" class="block w-full text-center px-6 py-4 rounded-xl border-2 border-[var(--color-agro-green-dark)] text-[var(--color-agro-green-dark)] hover:bg-[var(--color-agro-green-dark)] hover:text-white transition-all duration-300 font-semibold text-lg">
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
                    
                    <a href="<?php echo e(route('register')); ?>" class="block w-full text-center px-6 py-4 rounded-xl bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:from-[var(--color-agro-green)] hover:to-[var(--color-agro-green-dark)] transition-all duration-300 shadow-lg hover:shadow-xl font-semibold text-lg">
                        Empezar Ahora
                    </a>
                </div>
            </div>
            
            <p class="text-center text-gray-500 mt-8 text-lg">
                🎁 <span class="font-semibold text-gray-700">6 meses completamente gratis, sin tarjeta requerida.</span> Cancela en cualquier momento.
            </p>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-[var(--color-agro-green-dark)] text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-3 gap-8">
                <div>
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-lg bg-white/10 flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                            </svg>
                        </div>
                        <span class="text-xl font-bold">Agro365</span>
                    </div>
                    <p class="text-white/70">
                        Plataforma de gestión agrícola moderna para viticultores profesionales.
                    </p>
                </div>
                
                <div>
                    <h4 class="font-semibold text-lg mb-4">Producto</h4>
                    <ul class="space-y-2 text-white/70">
                        <li><a href="#features" class="hover:text-white transition-colors">Características</a></li>
                        <li><a href="#pricing" class="hover:text-white transition-colors">Precios</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="font-semibold text-lg mb-4">Cuenta</h4>
                    <ul class="space-y-2 text-white/70">
                        <li><a href="<?php echo e(route('login')); ?>" class="hover:text-white transition-colors">Iniciar Sesión</a></li>
                        <li><a href="<?php echo e(route('register')); ?>" class="hover:text-white transition-colors">Registrarse</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-white/10 mt-8 pt-8 text-center text-white/60">
                <p>&copy; <?php echo e(date('Y')); ?> Agro365. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>
</body>
</html>
<?php /**PATH C:\Users\jorge\Desktop\cue\agro365_web\resources\views/welcome.blade.php ENDPATH**/ ?>