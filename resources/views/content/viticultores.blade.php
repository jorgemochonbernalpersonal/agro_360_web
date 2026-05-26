<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Software para Viticultores | Gestión de Viñedos y Cuaderno Digital</title>
    <meta name="description" content="La solución definitiva para viticultores. Gestiona tus viñedos, cumple con la PAC y digitaliza tu cuaderno de campo con Agro365. Prueba 3 meses gratis.">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url('/viticultores') }}">
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-white">
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center gap-3">
                    <a href="{{ url('/') }}" class="flex items-center gap-3">
                        <img src="{{ asset('images/logo.png') }}" alt="Agro365" width="120" height="40" loading="eager" class="h-10 w-auto">
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
    <div class="min-h-screen bg-gradient-to-b from-white to-gray-50 py-20">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="mb-8 text-sm text-gray-600" itemscope itemtype="https://schema.org/BreadcrumbList">
                <ol class="flex items-center space-x-2">
                    <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                        <a href="{{ url('/') }}" class="hover:text-[var(--color-agro-green)]" itemprop="item"><span itemprop="name">{{ __('Inicio') }}</span></a>
                        <meta itemprop="position" content="1" />
                    </li>
                    <span class="mx-2">/</span>
                    <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                        <span class="text-gray-900" itemprop="name">{{ __('Viticultores') }}</span>
                        <meta itemprop="position" content="2" />
                    </li>
                </ol>
            </nav>
            <!-- Header -->
            <div class="mb-12">
                <h1 class="text-5xl lg:text-6xl font-bold text-[var(--color-agro-green-dark)] mb-6">{{ __('Software para Viticultores Profesionales') }}</h1>
                <p class="text-xl text-gray-600 leading-relaxed">
                    Gestiona tu explotación vitícola con herramientas de precisión. <strong>{{ __('Cuaderno de campo digital') }}</strong>, gestión de parcelas <strong>{{ __('SIGPAC') }}</strong>, control de vendimia y cumplimiento normativo PAC en una sola plataforma.
                </p>
            </div>

            <!-- Content -->
            <article class="prose prose-lg max-w-none">
                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">{{ __('La Digitalización al Servicio del Viticultor') }}</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">{{ __('En Agro365 entendemos que la viticultura no es solo agricultura; es un arte que requiere precisión técnica y un control exhaustivo de cada detalle, desde la poda hasta la entrega de la uva en bodega.') }}</p>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        Nuestro <strong>{{ __('software para viticultores') }}</strong> ha sido diseñado para simplificar las tareas administrativas más pesadas, permitiéndote centrarte en lo que realmente importa: la calidad de tu cosecha.
                    </p>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-6">{{ __('Funcionalidades Específicas para Viticultura') }}</h2>
                    <div class="grid md:grid-cols-2 gap-6 not-prose">
                        <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">{{ __('🍇 Variedades y Portainjertos') }}</h3>
                            <p class="text-gray-600 text-sm">{{ __('Control detallado de cada parcela: sistema de conducción, densidad de plantación, año de plantación y clones.') }}</p>
                        </div>
                        <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">{{ __('📋 Cuaderno Digital Inteligente') }}</h3>
                            <p class="text-gray-600 text-sm">{{ __('Registro de tratamientos fitosanitarios con validación automática de dosis y periodos de seguridad.') }}</p>
                        </div>
                        <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">{{ __('🗺️ Integración SIGPAC Real') }}</h3>
                            <p class="text-gray-600 text-sm">{{ __('Importa tus parcelas directamente desde el SIGPAC. Visualiza tus recintos en mapas de alta resolución.') }}</p>
                        </div>
                        <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">{{ __('📊 Control de Rendimientos') }}</h3>
                            <p class="text-gray-600 text-sm">{{ __('Monitoriza la producción por hectárea para cumplir con los límites de rendimiento de tu Denominación de Origen.') }}</p>
                        </div>
                    </div>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">{{ __('Cumplimiento PAC sin Estrés') }}</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        Con el nuevo <strong>{{ __('cuaderno de campo digital obligatorio') }}</strong>, el cumplimiento de la normativa PAC se ha vuelto más complejo. Agro365 automatiza gran parte de este proceso, detectando errores de coherencia antes de que se conviertan en problemas ante una inspección.
                    </p>
                    <div class="bg-[var(--color-agro-green-bg)] p-6 rounded-xl border border-[var(--color-agro-green-light)]/20">
                        <h4 class="font-bold text-[var(--color-agro-green-dark)] mb-2">{{ __('¿Sabías qué?') }}</h4>
                        <p class="text-gray-700 text-sm italic">{{ __('Agro365 genera informes oficiales con firma electrónica SHA-256, lo que otorga validez legal a tus registros frente a cualquier administración pública.') }}</p>
                    </div>
                </section>

                <div class="mt-16 pt-12 border-t border-gray-200 text-center">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-6">{{ __('Empieza a digitalizar tu viñedo hoy mismo') }}</h2>
                    <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-8 py-4 rounded-xl bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:from-[var(--color-agro-green)] hover:to-[var(--color-agro-green-dark)] transition-all duration-300 shadow-lg hover:shadow-xl font-semibold text-lg">
                        Comenzar Gratis
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                    <p class="mt-4 text-sm text-gray-500">{{ __('Sin tarjeta de crédito · Configuración en 5 minutos') }}</p>
                </div>
            </article>
        </div>
    </div>
    @include('partials.footer-seo')
</body>
</html>

