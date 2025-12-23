<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- SEO Meta Tags -->
    <title>Preguntas Frecuentes - Agro365 | Software de Gestión Agrícola</title>
    <meta name="description" content="Respuestas a las preguntas más frecuentes sobre Agro365: informes oficiales, SIGPAC, cuaderno digital, cuadrillas, rendimientos, precios y más.">
    <meta name="keywords" content="faqs agro365, preguntas software agrícola, cuaderno campo digital, SIGPAC, informes oficiales agricultura, preguntas frecuentes viticultura, dudas software agrícola">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <meta name="author" content="Agro365">
    <meta name="language" content="Spanish">
    <meta name="geo.region" content="ES">
    <meta name="revisit-after" content="7 days">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="{{ url('/faqs') }}">
    
    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/faqs') }}">
    <meta property="og:title" content="Preguntas Frecuentes - Agro365">
    <meta property="og:description" content="Todo lo que necesitas saber sobre Agro365, el software profesional de gestión agrícola">
    <meta property="og:image" content="{{ asset('images/logo.png') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="Preguntas Frecuentes - Agro365">
    <meta property="og:locale" content="es_ES">
    <meta property="og:site_name" content="Agro365">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="{{ url('/faqs') }}">
    <meta name="twitter:title" content="Preguntas Frecuentes - Agro365">
    <meta name="twitter:description" content="Todo lo que necesitas saber sobre Agro365, el software profesional de gestión agrícola">
    <meta name="twitter:image" content="{{ asset('images/logo.png') }}">
    
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
                        <img src="{{ asset('images/logo.png') }}" alt="Agro365" class="h-10 w-auto">
                        <span class="text-xl font-bold text-[var(--color-agro-green-dark)]">Agro365</span>
                    </a>
                    <span class="px-2 py-1 text-xs font-bold rounded-full bg-blue-100 text-blue-700 border border-blue-300">BETA</span>
                </div>
                <div class="flex items-center gap-4">
                    <a href="{{ url('/') }}" class="text-gray-600 hover:text-[var(--color-agro-green)] transition-colors">Inicio</a>
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
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="text-center mb-16">
                <h1 class="text-5xl lg:text-6xl font-bold text-[var(--color-agro-green-dark)] mb-6">
                    Preguntas Frecuentes
                </h1>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Encuentra respuestas a las preguntas más comunes sobre Agro365, tu software de gestión agrícola profesional
                </p>
            </div>

            <!-- Expand/Collapse All Button -->
            <div class="text-center mb-8" x-data="{ openIndexes: [] }">
                <button 
                    @click="openIndexes.length === 8 ? openIndexes = [] : openIndexes = [1,2,3,4,5,6,7,8]"
                    class="inline-flex items-center gap-2 px-6 py-3 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium transition-colors"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                    </svg>
                    <span x-text="openIndexes.length === 8 ? 'Colapsar Todas' : 'Expandir Todas'"></span>
                </button>
                
                <!-- 2 Column Grid Layout for FAQs -->
                <div class="grid md:grid-cols-2 gap-6 mt-8">
                    @include('partials.faq-items')
                </div>
            </div>

            <!-- CTA Section -->
            <div class="text-center mt-16 pt-12 border-t border-gray-200">
                <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">
                    ¿Aún tienes dudas?
                </h2>
                <p class="text-gray-600 mb-8 text-lg">
                    Estamos aquí para ayudarte. Comienza tu prueba gratuita de 6 meses hoy mismo.
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

    <!-- Footer -->
    <footer class="bg-[var(--color-agro-green-dark)] text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-4 gap-8">
                <div class="md:col-span-2">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="text-xl font-bold">Agro365</span>
                        <span class="px-2 py-1 text-xs font-bold rounded-full bg-blue-500/20 text-blue-200 border border-blue-400/30">BETA</span>
                    </div>
                    <p class="text-white/70 mb-4">
                        Plataforma de gestión agrícola moderna para viticultores profesionales.
                    </p>
                </div>
                
                <div>
                    <h4 class="font-semibold text-lg mb-4">Producto</h4>
                    <ul class="space-y-2 text-white/70">
                        <li><a href="{{ url('/') }}#features" class="hover:text-white transition-colors">Características</a></li>
                        <li><a href="{{ url('/') }}#pricing" class="hover:text-white transition-colors">Precios</a></li>
                        <li><a href="{{ route('faqs') }}" class="hover:text-white transition-colors">Preguntas Frecuentes</a></li>
                        <li><a href="{{ route('register') }}" class="hover:text-white transition-colors">Prueba Gratis</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="font-semibold text-lg mb-4">Legal</h4>
                    <ul class="space-y-2 text-white/70">
                        <li><a href="{{ route('privacy') }}" class="hover:text-white transition-colors">Privacidad</a></li>
                        <li><a href="{{ route('terms') }}" class="hover:text-white transition-colors">Términos</a></li>
                        <li><a href="{{ route('cookies') }}" class="hover:text-white transition-colors">Cookies</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-white/10 mt-8 pt-8 text-center text-white/70">
                <p>&copy; {{ date('Y') }} Agro365. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- Breadcrumb Schema -->
    <script type="application/ld+json">
    {!! \App\Helpers\SeoHelper::breadcrumbSchema([
        ['name' => 'Inicio', 'url' => url('/')],
        ['name' => 'Preguntas Frecuentes', 'url' => url('/faqs')]
    ]) !!}
    </script>
    
    <!-- FAQ Schema Markup for SEO -->
    <script type="application/ld+json">
    @php
        $faqSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => [
                [
                    '@type' => 'Question',
                    'name' => '¿Cómo funcionan los informes oficiales con firma electrónica?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Agro365 genera 7 tipos de informes oficiales certificados: Tratamientos Fitosanitarios, Riegos, Fertilizaciones, Labores Culturales, Cosechas, PAC y Certificaciones Completas. Cada informe incluye: Firma electrónica SHA-256 única e inmutable, Código QR de verificación pública en cada página, Verificación instantánea en agro365.es/verify-report/CODIGO, Contador de verificaciones para auditoría completa, e Invalidación en 30 días si detectas errores.'
                    ]
                ],
                [
                    '@type' => 'Question',
                    'name' => '¿Qué es SIGPAC y cómo me ayuda Agro365?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'SIGPAC es el Sistema de Información Geográfica de Parcelas Agrícolas oficial del Ministerio. Agro365 integra SIGPAC completamente con códigos multiparcela, geometrías GeoJSON visualizadas en mapa interactivo, variedades y hectáreas exactas por recinto, y asociación automática de actividades al código SIGPAC correcto.'
                    ]
                ],
                [
                    '@type' => 'Question',
                    'name' => '¿Puedo gestionar cuadrillas y maquinaria?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Absolutamente. Agro365 incluye gestión completa de cuadrillas con registro de miembros y roles, asignación a actividades, cálculo de costos laborales por parcela. También incluye control de maquinaria con registro de tractores y equipos, asociación a actividades, historial de uso y análisis de costos.'
                    ]
                ],
                [
                    '@type' => 'Question',
                    'name' => '¿Puedo comparar rendimientos estimados vs reales?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Sí. Agro365 tiene un sistema completo de estimación y análisis de cosechas. Antes de vendimia registras estimaciones por parcela. Durante vendimia registras contenedores individuales con kg reales y estados. Después comparas estimado vs real, identificas parcelas sobre/infra productivas y optimizas la próxima campaña.'
                    ]
                ],
                [
                    '@type' => 'Question',
                    'name' => '¿Cuánto cuesta Agro365 realmente?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Agro365 ofrece 6 meses completamente gratis para todos los usuarios beta. Después: Plan Mensual €9/mes (descuento beta 25%), Plan Anual €90/año (€7.50/mes, ahorra €18 al año). Sin tarjeta requerida para comenzar. Cancela en cualquier momento.'
                    ]
                ],
                [
                    '@type' => 'Question',
                    'name' => '¿Puedo usar Agro365 desde el móvil en el viñedo?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Sí. Agro365 está 100% optimizado para móviles y tablets. Funciona como aplicación web responsive desde cualquier navegador sin instalar apps. Registra tratamientos, riegos y actividades directamente desde el viñedo, incluso con conexión limitada. Los datos se sincronizan automáticamente.'
                    ]
                ],
                [
                    '@type' => 'Question',
                    'name' => '¿Mis datos están seguros en Agro365?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Absolutamente. Datos protegidos con cifrado HTTPS de nivel bancario y almacenados en servidores seguros europeos. Cumplimos con RGPD. Backups automáticos diarios. Solo tú tienes acceso a tus datos, nunca los compartimos. Puedes exportar o eliminar tu información en cualquier momento.'
                    ]
                ],
                [
                    '@type' => 'Question',
                    'name' => '¿Es obligatorio el cuaderno de campo digital?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Sí. El cuaderno de campo digital es obligatorio en España. Desde 2023 obligatorio para explotaciones profesionales, y desde 2027 DEBE estar digitalizado según normativa europea. Es obligatorio registrar tratamientos fitosanitarios, riegos, fertilizaciones y labores. Las inspecciones PAC pueden solicitarlo en cualquier momento. Agro365 cumple 100% con requisitos legales y normativa 2027.'
                    ]
                ]
            ]
        ];
    @endphp
    {!! json_encode($faqSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
    </script>
</body>
</html>
