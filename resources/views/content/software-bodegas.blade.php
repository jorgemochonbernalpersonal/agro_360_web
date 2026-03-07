<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- SEO Meta Tags -->
    <title>Software para Bodegas | Gestión Profesional de Bodegas - Agro365</title>
    <meta name="description" content="Software profesional para bodegas en España. Trazabilidad integral, gestión de vendimia, control de depósitos y cumplimiento normativo. Bodega independiente 14€/mes. Gratis si perteneces a una DO.">
    <meta name="keywords" content="software bodegas, software bodega, gestión bodegas, software producción vino, gestión bodega, software enología, software bodegas España">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <meta name="author" content="Agro365">
    <meta name="language" content="Spanish">
    <meta name="geo.region" content="ES">
    <meta name="revisit-after" content="7 days">

    <!-- Canonical URL -->
    <link rel="canonical" href="{{ url('/software-bodegas') }}">

    <!-- Open Graph -->
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ url('/software-bodegas') }}">
    <meta property="og:title" content="Software para Bodegas - Gestión Profesional · Agro365">
    <meta property="og:description" content="Trazabilidad integral, gestión de vendimia, control de depósitos y cumplimiento normativo. Bodega independiente 14€/mes. Gratis si perteneces a una DO.">
    <meta property="og:image" content="{{ asset('images/logo.png') }}">
    <meta property="og:locale" content="es_ES">
    <meta property="og:site_name" content="Agro365">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="{{ url('/software-bodegas') }}">
    <meta name="twitter:title" content="Software para Bodegas - Agro365">
    <meta name="twitter:description" content="Trazabilidad integral desde la uva hasta la botella. Bodega independiente 14€/mes. Gratis si perteneces a una DO.">
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
                    <a href="{{ url('/') }}" class="text-gray-600 hover:text-[var(--color-agro-green)] transition-colors text-sm">Inicio</a>
                    <a href="{{ url('/precios') }}" class="text-gray-600 hover:text-[var(--color-agro-green)] transition-colors text-sm">Precios</a>
                    <a href="{{ route('faqs') }}" class="text-gray-600 hover:text-[var(--color-agro-green)] transition-colors text-sm">FAQs</a>
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}" class="text-gray-600 hover:text-[var(--color-agro-green)] transition-colors text-sm">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="text-gray-600 hover:text-[var(--color-agro-green)] transition-colors text-sm">Entrar</a>
                            <a href="{{ route('register') }}" class="px-4 py-2 rounded-lg bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:shadow-lg transition-all text-sm font-semibold">
                                Comenzar Gratis
                            </a>
                        @endauth
                    @endif
                </div>
            </div>
        </nav>
    </header>

    <main class="min-h-screen">

        <!-- Hero -->
        <section class="py-16 bg-gradient-to-b from-red-50 to-white">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <nav class="mb-8 text-sm text-zinc-500" itemscope itemtype="https://schema.org/BreadcrumbList">
                    <ol class="flex items-center space-x-2">
                        <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                            <a href="{{ url('/') }}" class="hover:text-[var(--color-agro-green)]" itemprop="item"><span itemprop="name">Inicio</span></a>
                            <meta itemprop="position" content="1" />
                        </li>
                        <span class="mx-2">/</span>
                        <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                            <span class="text-zinc-900 font-medium" itemprop="name">Software para Bodegas</span>
                            <meta itemprop="position" content="2" />
                        </li>
                    </ol>
                </nav>

                <h1 class="text-4xl lg:text-5xl font-bold text-[var(--color-agro-green-dark)] mb-6">
                    Software para Bodegas Profesionales
                </h1>
                <p class="text-xl text-zinc-600 leading-relaxed">
                    Control total desde la recepción de uva hasta el embotellado. Trazabilidad integral, gestión de depósitos, control de calidad y cumplimiento normativo para Denominaciones de Origen.
                </p>
            </div>
        </section>

        <!-- Contenido principal -->
        <section class="py-12">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <article class="prose prose-lg max-w-none">

                    <!-- La transformación digital -->
                    <div class="mb-12">
                        <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">La Transformación Digital de tu Bodega</h2>
                        <p class="text-zinc-700 leading-relaxed mb-4">
                            En una bodega, la información es tan valiosa como el vino. Agro365 te permite digitalizar todos los procesos críticos de tu bodega, garantizando una trazabilidad perfecta que aporta valor y seguridad a tu marca.
                        </p>
                        <p class="text-zinc-700 leading-relaxed">
                            Nuestra plataforma conecta el campo con la bodega, integrando los datos de vendimia directamente en tu sistema de gestión de producción.
                        </p>
                    </div>

                    <!-- Funcionalidades -->
                    <div class="mb-12">
                        <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-6">Funcionalidades para la Gestión Vinícola</h2>
                        <div class="grid md:grid-cols-2 gap-6 not-prose">
                            @foreach([
                                ['📥', 'Recepción y Pesaje', 'Registro rápido de entradas de uva por socio o parcela, control de grados baumé y estado sanitario.'],
                                ['🏭', 'Gestión de Depósitos', 'Control visual de tu parque de depósitos, inventario en tiempo real y movimientos entre envases.'],
                                ['🔍', 'Trazabilidad de Origen', 'Conoce exactamente de qué parcela procede cada litro de vino. Cumple con los requisitos de los Consejos Reguladores.'],
                                ['📦', 'Escandallos y Costes', 'Análisis detallado de costes de producción, desde la materia prima hasta los materiales auxiliares.'],
                                ['📄', 'Informes Oficiales', 'Genera informes AICA, INFOVI y libros de bodega en minutos, no en días. Con firma electrónica.'],
                                ['💰', 'Facturación Integrada', 'Facturación de uva, vino a granel y productos embotellados. Compatible con Verifactu.'],
                            ] as [$icon, $title, $desc])
                            <div class="bg-white p-6 rounded-xl border border-zinc-200 shadow-sm">
                                <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">{{ $icon }} {{ $title }}</h3>
                                <p class="text-zinc-600 text-sm leading-relaxed">{{ $desc }}</p>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Conecta con tus viticultores -->
                    <div class="mb-12 not-prose">
                        <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">Conecta con tus Viticultores Proveedores</h2>
                        <p class="text-zinc-700 leading-relaxed mb-6">
                            El diferenciador clave de Agro365 es el ecosistema. Invita a tus viticultores proveedores con un enlace simple: ellos acceden a su cuaderno de campo gratis, y tú ves sus datos en tiempo real en tu panel de bodega. Sin fricciones, sin negociaciones.
                        </p>
                        <div class="bg-[var(--color-agro-green-bg)] rounded-2xl p-8 border border-[var(--color-agro-green-light)]/30">
                            <div class="grid md:grid-cols-3 gap-6 text-center">
                                <div>
                                    <div class="text-3xl mb-2">📨</div>
                                    <h3 class="font-bold text-[var(--color-agro-green-dark)] mb-1">Invita con un enlace</h3>
                                    <p class="text-zinc-600 text-sm">El viticultor se registra en menos de 5 minutos. Sin instalaciones.</p>
                                </div>
                                <div>
                                    <div class="text-3xl mb-2">🌿</div>
                                    <h3 class="font-bold text-[var(--color-agro-green-dark)] mb-1">Ellos acceden gratis</h3>
                                    <p class="text-zinc-600 text-sm">Cuaderno de campo básico sin coste. Si quieren funciones avanzadas, pagan 9€/mes por su cuenta.</p>
                                </div>
                                <div>
                                    <div class="text-3xl mb-2">📊</div>
                                    <h3 class="font-bold text-[var(--color-agro-green-dark)] mb-1">Tú ves todo en tiempo real</h3>
                                    <p class="text-zinc-600 text-sm">Parcelas, tratamientos, rendimientos estimados y datos de campaña de todos tus proveedores.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Seguridad normativa -->
                    <div class="mb-12">
                        <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">Seguridad y Rigor Normativo</h2>
                        <p class="text-zinc-700 leading-relaxed mb-4">
                            Cumplir con los libros de bodega y las declaraciones obligatorias (AICA, INFOVI) puede ser una tarea tediosa. Agro365 automatiza la recopilación de datos para que generar estos informes sea cuestión de minutos, no de días.
                        </p>
                        <div class="bg-zinc-50 rounded-xl p-6 border border-zinc-200 not-prose">
                            <div class="flex items-center gap-4 mb-4">
                                <div class="text-4xl">⚡</div>
                                <div>
                                    <p class="font-bold text-zinc-800 text-lg">Optimización de Procesos</p>
                                    <p class="text-zinc-500 text-sm">Los usuarios de Agro365 reportan una reducción media del 40% en el tiempo dedicado a la gestión administrativa de la trazabilidad.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                </article>
            </div>
        </section>

        <!-- Precios -->
        <section class="py-12 bg-zinc-50">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-2">Precios para Bodegas</h2>
                <p class="text-zinc-600 mb-8">Dos situaciones, dos precios. Sin letra pequeña.</p>
                <div class="grid md:grid-cols-2 gap-6">

                    <!-- Bodega en DO -->
                    <div class="bg-white rounded-2xl p-8 border-2 border-amber-200 shadow-sm">
                        <div class="text-3xl mb-3">🏛️</div>
                        <h3 class="text-xl font-bold text-zinc-800 mb-1">Bodega dentro de una DO</h3>
                        <p class="text-zinc-500 text-sm mb-4">Si tu Denominación de Origen tiene contrato con Agro365, tu acceso está cubierto.</p>
                        <div class="text-4xl font-bold text-amber-700 mb-1">Gratis</div>
                        <p class="text-zinc-500 text-sm mb-6">Cubierta por el paquete de tu Denominación de Origen.</p>
                        <ul class="space-y-2 text-sm text-zinc-600">
                            @foreach(['Acceso completo a todas las funcionalidades', 'Soporte incluido en el paquete DO', 'Sin cuota individual']) as $item)
                            <li class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-amber-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                {{ $item }}
                            </li>
                            @endforeach
                        </ul>
                        <a href="mailto:info@agro365.es?subject=Consulta%20DO%20Agro365" class="mt-6 block text-center px-6 py-3 rounded-xl border-2 border-amber-400 text-amber-800 hover:bg-amber-500 hover:text-white transition-all font-semibold text-sm">
                            Consultar si mi DO tiene acuerdo
                        </a>
                    </div>

                    <!-- Bodega independiente -->
                    <div class="bg-white rounded-2xl p-8 border-2 border-red-400 shadow-xl relative overflow-hidden">
                        <div class="absolute top-0 right-0">
                            <div class="bg-gradient-to-r from-red-600 to-red-500 text-white px-4 py-1.5 rounded-bl-2xl font-semibold text-xs">
                                Más popular
                            </div>
                        </div>
                        <div class="text-3xl mb-3 pt-4">🍷</div>
                        <h3 class="text-xl font-bold text-zinc-800 mb-1">Bodega independiente</h3>
                        <p class="text-zinc-500 text-sm mb-4">Sin DO o con DO que aún no está en Agro365.</p>
                        <div class="flex items-end gap-1 mb-1">
                            <span class="text-4xl font-bold text-red-700">14€</span>
                            <span class="text-zinc-500 mb-1">/mes</span>
                        </div>
                        <p class="text-zinc-500 text-sm mb-6">o <strong>130€/año</strong> · Onboarding personalizado incluido.</p>
                        <ul class="space-y-2 text-sm text-zinc-600">
                            @foreach([
                                'Gestión completa de vendimia y producción',
                                'Trazabilidad desde cepa hasta botella',
                                'Gestión de depósitos y movimientos',
                                'Facturación + Verifactu',
                                'Panel de viticultores proveedores',
                                'Informes AICA, INFOVI y libros de bodega',
                                'Onboarding personalizado incluido',
                                'Soporte prioritario 24h',
                            ] as $item)
                            <li class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-red-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                {{ $item }}
                            </li>
                            @endforeach
                        </ul>
                        <a href="{{ route('register') }}" class="mt-6 block text-center px-6 py-3 rounded-xl bg-gradient-to-r from-red-600 to-red-500 text-white hover:from-red-700 hover:to-red-600 transition-all font-bold shadow-lg">
                            Empezar Gratis — Bodega
                        </a>
                        <p class="text-center text-xs text-zinc-400 mt-2">Sin tarjeta requerida · Cancela cuando quieras</p>
                    </div>

                </div>

                <p class="text-center text-zinc-500 text-sm mt-6">
                    ¿Tu Denominación de Origen todavía no está en Agro365?
                    <a href="mailto:info@agro365.es?subject=DO%20Agro365" class="text-[var(--color-agro-green)] hover:underline font-semibold">Cuéntanos y lo tramitamos</a> — cuanto más grande sea la DO, más les interesa el precio del paquete.
                </p>
            </div>
        </section>

        <!-- CTA final -->
        <section class="py-16 bg-gradient-to-br from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)]">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-3xl lg:text-4xl font-bold text-white mb-4">
                    Lleva tu bodega al siguiente nivel
                </h2>
                <p class="text-green-100 text-lg mb-8">
                    Sin tarjeta de crédito. Onboarding personalizado incluido. Migración de datos gratuita.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('register') }}" class="inline-flex items-center justify-center gap-2 px-8 py-4 rounded-xl bg-white text-[var(--color-agro-green-dark)] hover:bg-green-50 transition-all font-bold text-lg shadow-lg">
                        Empezar Gratis — Bodega
                    </a>
                    <a href="mailto:info@agro365.es?subject=Demo%20Bodega%20Agro365" class="inline-flex items-center justify-center gap-2 px-8 py-4 rounded-xl border-2 border-white/70 text-white hover:bg-white/10 transition-all font-semibold text-lg">
                        Solicitar Demo
                    </a>
                </div>
            </div>
        </section>

    </main>

    @include('partials.footer-seo')

    <!-- Breadcrumb Schema -->
    <script type="application/ld+json">
    {!! \App\Helpers\SeoHelper::breadcrumbSchema([
        ['name' => 'Inicio', 'url' => url('/')],
        ['name' => 'Software para Bodegas', 'url' => url('/software-bodegas')]
    ]) !!}
    </script>
</body>
</html>
