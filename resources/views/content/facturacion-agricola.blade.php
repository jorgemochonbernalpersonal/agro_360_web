<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- SEO Meta Tags -->
    <title>Facturación Agrícola | Software para Viticultores y Bodegas</title>
    <meta name="description" content="Facturación agrícola con Verifactu integrado. Genera facturas de cosecha certificadas, controla entregas a bodega y pagos. Cumple con la normativa fiscal 2025. Prueba gratuita 3 meses.">
    <meta name="keywords" content="facturación agrícola, software facturación agricultura, facturación viticultores, facturar cosecha, facturación bodegas, software factura agrícola, control pagos agricultura, gestión clientes bodega, facturación vendimia, factura electrónica agricultura">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <meta name="author" content="Agro365">
    <meta name="language" content="Spanish">
    <meta name="geo.region" content="ES">
    
    <link rel="canonical" href="{{ url('/facturacion-agricola') }}">
    
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ url('/facturacion-agricola') }}">
    <meta property="og:title" content="Facturación Agrícola - Software para Viticultores">
    <meta property="og:description" content="Factura cosechas, gestiona clientes y controla pagos con Agro365.">
    <meta property="og:image" content="{{ asset('images/dashboard-preview.png') }}">
    <meta property="og:locale" content="es_ES">
    
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-white">
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

    <div class="min-h-screen bg-gradient-to-b from-white to-gray-50 py-20">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="mb-8 text-sm text-gray-600">
                <ol class="flex items-center space-x-2">
                    <li><a href="{{ url('/') }}" class="hover:text-[var(--color-agro-green)]">Inicio</a></li>
                    <span class="mx-2">/</span>
                    <li class="text-gray-900">{{ __('Facturación Agrícola') }}</li>
                </ol>
            </nav>

            <div class="mb-12">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-blue-100 border border-blue-300 mb-6">
                    <span class="text-lg">💰</span>
                    <span class="text-sm font-semibold text-blue-800">{{ __('Facturación Integrada') }}</span>
                </div>
                <h1 class="text-5xl lg:text-6xl font-bold text-[var(--color-agro-green-dark)] mb-6">{{ __('Facturación Agrícola para Viticultores') }}</h1>
                <p class="text-xl text-gray-600 leading-relaxed">
                    <strong>{{ __('Factura tus cosechas directamente') }}</strong> desde el registro de vendimia. Gestión de clientes (bodegas), control de pagos, albaranes de entrega y cumplimiento fiscal. Todo integrado en <a href="{{ content_route('content.software-viticultores') }}" class="text-[var(--color-agro-green)] hover:underline">Agro365</a>.
                </p>
            </div>

            <article class="prose prose-lg max-w-none">
                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">{{ __('¿Por Qué un Software de Facturación Agrícola?') }}</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        Como viticultor, tu negocio tiene necesidades específicas de facturación: <strong>{{ __('múltiples entregas por cliente') }}</strong>, <strong>{{ __('facturación por peso') }}</strong>, <strong>{{ __('albaranes de entrega') }}</strong>, y la necesidad de vincular facturas con la <a href="{{ url('/gestion-vendimia') }}" class="text-[var(--color-agro-green)] hover:underline">gestión de vendimia</a>.
                    </p>
                    <p class="text-gray-700 leading-relaxed mb-4">{{ __('Un software de facturación genérico no entiende estas particularidades. Agro365 está diseñado específicamente para viticultores.') }}</p>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">{{ __('Funcionalidades de Facturación') }}</h2>
                    
                    <div class="grid md:grid-cols-2 gap-6 mb-6">
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <div class="text-3xl mb-3">📄</div>
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">{{ __('Facturas desde Vendimia') }}</h3>
                            <p class="text-gray-700">Genera facturas directamente desde los contenedores de <a href="{{ url('/gestion-vendimia') }}" class="text-[var(--color-agro-green)] hover:underline">vendimia</a> registrados. Sin duplicar datos.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <div class="text-3xl mb-3">👥</div>
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">{{ __('Gestión de Clientes') }}</h3>
                            <p class="text-gray-700">{{ __('Base de datos de bodegas y clientes con datos fiscales, direcciones de facturación y entrega.') }}</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <div class="text-3xl mb-3">📋</div>
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">{{ __('Albaranes de Entrega') }}</h3>
                            <p class="text-gray-700">{{ __('Genera albaranes para cada entrega. Múltiples albaranes por factura.') }}</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <div class="text-3xl mb-3">💳</div>
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">{{ __('Control de Pagos') }}</h3>
                            <p class="text-gray-700">{{ __('Seguimiento de facturas pagadas, pendientes y vencidas. Alertas automáticas.') }}</p>
                        </div>
                    </div>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">{{ __('De la Vendimia a la Factura en 1 Clic') }}</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">{{ __('El flujo de trabajo es sencillo:') }}</p>
                    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 mb-6">
                        <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                            <div class="text-center">
                                <div class="w-16 h-16 rounded-full bg-amber-100 flex items-center justify-center mx-auto mb-2">
                                    <span class="text-2xl">🍇</span>
                                </div>
                                <div class="text-sm font-bold text-gray-800">1. Registra Vendimia</div>
                                <div class="text-xs text-gray-600">Contenedores + parcelas</div>
                            </div>
                            <div class="text-2xl text-gray-400">→</div>
                            <div class="text-center">
                                <div class="w-16 h-16 rounded-full bg-blue-100 flex items-center justify-center mx-auto mb-2">
                                    <span class="text-2xl">📋</span>
                                </div>
                                <div class="text-sm font-bold text-gray-800">2. Genera Albarán</div>
                                <div class="text-xs text-gray-600">Automático por entrega</div>
                            </div>
                            <div class="text-2xl text-gray-400">→</div>
                            <div class="text-center">
                                <div class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-2">
                                    <span class="text-2xl">💰</span>
                                </div>
                                <div class="text-sm font-bold text-gray-800">3. Crea Factura</div>
                                <div class="text-xs text-gray-600">1 clic desde albaranes</div>
                            </div>
                            <div class="text-2xl text-gray-400">→</div>
                            <div class="text-center">
                                <div class="w-16 h-16 rounded-full bg-purple-100 flex items-center justify-center mx-auto mb-2">
                                    <span class="text-2xl">✅</span>
                                </div>
                                <div class="text-sm font-bold text-gray-800">4. Controla Pago</div>
                                <div class="text-xs text-gray-600">Seguimiento automático</div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">{{ __('Datos de Facturación') }}</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">{{ __('Cada factura incluye:') }}</p>
                    <div class="grid md:grid-cols-2 gap-4 mb-6">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="font-bold text-[var(--color-agro-green-dark)] mb-2">{{ __('📊 Datos de Factura') }}</h3>
                            <ul class="text-gray-700 text-sm space-y-1">
                                <li>{{ __('• Número de factura (automático)') }}</li>
                                <li>{{ __('• Fecha de emisión y vencimiento') }}</li>
                                <li>{{ __('• Datos fiscales cliente') }}</li>
                                <li>{{ __('• Líneas de detalle personalizables') }}</li>
                                <li>{{ __('• IVA configurado por usuario') }}</li>
                                <li>{{ __('• Retenciones si aplica') }}</li>
                            </ul>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="font-bold text-[var(--color-agro-green-dark)] mb-2">{{ __('🍇 Datos de Vendimia') }}</h3>
                            <ul class="text-gray-700 text-sm space-y-1">
                                <li>{{ __('• Kilos totales entregados') }}</li>
                                <li>{{ __('• Variedad de uva') }}</li>
                                <li>{{ __('• Precio por kilo') }}</li>
                                <li>{{ __('• Parcelas de origen') }}</li>
                                <li>{{ __('• Grado Baumé') }}</li>
                                <li>{{ __('• Fechas de entrega') }}</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">{{ __('Control de Pagos') }}</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">{{ __('Nunca más pierdas el rastro de un pago:') }}</p>
                    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 mb-6">
                        <div class="grid md:grid-cols-3 gap-4 text-center">
                            <div class="p-4 bg-green-50 rounded-lg border border-green-200">
                                <div class="text-3xl font-bold text-green-600">Pagadas</div>
                                <div class="text-sm text-gray-600">Facturas cobradas</div>
                            </div>
                            <div class="p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                                <div class="text-3xl font-bold text-yellow-600">Pendientes</div>
                                <div class="text-sm text-gray-600">Por vencer</div>
                            </div>
                            <div class="p-4 bg-red-50 rounded-lg border border-red-200">
                                <div class="text-3xl font-bold text-red-600">Vencidas</div>
                                <div class="text-sm text-gray-600">Alerta automática</div>
                            </div>
                        </div>
                    </div>
                    <ul class="list-disc list-inside space-y-2 text-gray-700 mb-6 ml-4">
                        <li><strong>{{ __('Dashboard financiero:') }}</strong> Resumen de ingresos por campaña</li>
                        <li><strong>{{ __('Alertas de vencimiento:') }}</strong> Notificación antes de que venzan facturas</li>
                        <li><strong>{{ __('Registro de pagos parciales:') }}</strong> Una factura, múltiples pagos</li>
                        <li><strong>{{ __('Exportación contable:') }}</strong> Exporta datos para tu gestor</li>
                    </ul>
                </section>

                <section class="mb-12">
                    <div class="bg-gradient-to-r from-[var(--color-agro-green-bg)] to-[var(--color-agro-green-light)]/30 p-8 rounded-xl border border-[var(--color-agro-green)]/20">
                        <h3 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mb-4">{{ __('💰 Facturación Agrícola Profesional') }}</h3>
                        <p class="text-gray-700 mb-6">
                            De la vendimia a la factura sin complicaciones. Gestiona clientes, albaranes y controla pagos. <strong>{{ __('3 meses gratis') }}</strong>.
                        </p>
                        <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-lg bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:shadow-lg transition-all font-semibold">
                            Comenzar Gratis
                        </a>
                    </div>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">{{ __('Preguntas Frecuentes') }}</h2>
                    <div class="space-y-6">
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">{{ __('¿Puedo generar facturas en PDF?') }}</h3>
                            <p class="text-gray-700">{{ __('Sí, todas las facturas se pueden descargar en PDF profesional listo para enviar.') }}</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">{{ __('¿Soporta factura electrónica?') }}</h3>
                            <p class="text-gray-700">{{ __('Generamos facturas en formato PDF con todos los datos fiscales requeridos.') }}</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">{{ __('¿Puedo configurar mi IVA?') }}</h3>
                            <p class="text-gray-700">{{ __('Sí, puedes configurar tus tipos de IVA y retenciones en los ajustes de facturación.') }}</p>
                        </div>
                    </div>
                </section>
            </article>

            <div class="mt-16 pt-12 border-t border-gray-200 text-center">
                <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">{{ __('Factura tu Cosecha con Agro365') }}</h2>
                <p class="text-gray-600 mb-8 text-lg">{{ __('Software de facturación agrícola integrado con gestión de vendimia y control de pagos.') }}</p>
                <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-8 py-4 rounded-xl bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white shadow-lg font-semibold text-lg">
                    Comenzar Gratis
                </a>
            </div>
        </div>
    </div>

    <!-- ✅ SEO: Enlaces relacionados para mejorar link juice interno -->
    @include('components.related-links')

    @include('partials.footer-seo')

    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "Article",
        "headline": "Facturación Agrícola para Viticultores - Software Profesional",
        "description": "Software de facturación agrícola para viticultores. Factura cosechas y gestiona clientes.",
        "author": {"@@type": "Organization", "name": "Agro365"},
        "publisher": {"@@type": "Organization", "name": "Agro365"},
        "datePublished": "2024-01-01",
        "dateModified": "{{ now()->toIso8601String() }}"
    }
    </script>
</body>
</html>
