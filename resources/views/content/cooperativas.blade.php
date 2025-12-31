<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Software para Cooperativas | Gestión Profesional de Cooperativas Agrícolas - Agro365</title>
    <meta name="description" content="Software profesional para cooperativas agrícolas en España. Gestión de socios, producción, trazabilidad y cumplimiento normativo. Prueba gratis 6 meses.">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url('/cooperativas') }}">
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
                        <span class="text-xl font-bold text-[var(--color-agro-green-dark)]">Agro365</span>
                    </a>
                    <span class="px-2 py-1 text-xs font-bold rounded-full bg-blue-100 text-blue-700 border border-blue-300">BETA</span>
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
                        <a href="{{ url('/') }}" class="hover:text-[var(--color-agro-green)]" itemprop="item"><span itemprop="name">Inicio</span></a>
                        <meta itemprop="position" content="1" />
                    </li>
                    <span class="mx-2">/</span>
                    <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                        <span class="text-gray-900" itemprop="name">Cooperativas</span>
                        <meta itemprop="position" content="2" />
                    </li>
                </ol>
            </nav>
            <!-- Header -->
            <div class="mb-12">
                <h1 class="text-5xl lg:text-6xl font-bold text-[var(--color-agro-green-dark)] mb-6">
                    Software para Cooperativas Agrícolas
                </h1>
                <p class="text-xl text-gray-600 leading-relaxed">
                    Centraliza la gestión de tus socios y garantiza la calidad de la producción. <strong>Gestión masiva de cuadernos de campo</strong>, control de entregas y trazabilidad colectiva eficiente.
                </p>
            </div>

            <!-- Content -->
            <article class="prose prose-lg max-w-none">
                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">Potenciando la Fuerza del Cooperativismo</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        Gestionar una <strong>cooperativa agrícola</strong> implica coordinar cientos de explotaciones individuales. Agro365 ofrece una visión global de la cooperativa mientras mantiene la independencia y el detalle de cada socio.
                    </p>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        Nuestra tecnología permite a los técnicos de la cooperativa supervisar y validar las actividades de campo de forma remota, asegurando que toda la producción cumpla con los estándares exigidos.
                    </p>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-6">Soluciones para la Gestión de Socios</h2>
                    <div class="grid md:grid-cols-2 gap-6 not-prose">
                        <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">👥 Censo de Explotaciones</h3>
                            <p class="text-gray-600 text-sm">Base de datos centralizada de socios, parcelas SIGPAC y cultivos. Olvídate de los excels desactualizados.</p>
                        </div>
                        <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">📝 Supervisión Documental</h3>
                            <p class="text-gray-600 text-sm">Visualiza el estado de los cuadernos de campo de todos tus socios en un solo panel. Detecta omisiones al instante.</p>
                        </div>
                        <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">🚚 Control de Entregas</h3>
                            <p class="text-gray-600 text-sm">Planificación de vendimia y recepción masiva de uva. Asignación automática de lotes por calidad o zona.</p>
                        </div>
                        <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">📢 Comunicación con el Socio</h3>
                            <p class="text-gray-600 text-sm">Envía avisos de tratamientos, alertas meteorológicas o circulares informativas directamente a la app del socio.</p>
                        </div>
                    </div>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">Garantía de Calidad Colectiva</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        La reputación de una cooperativa depende del eslabón más débil. Con Agro365, los asesores técnicos pueden implementar protocolos comunes de cultivo y verificar su cumplimiento, minimizando riesgos sanitarios y maximizando la rentabilidad del grupo.
                    </p>
                    <div class="bg-blue-50 p-6 rounded-xl border border-blue-100">
                        <h4 class="font-bold text-blue-900 mb-2">Visión Global de Producción</h4>
                        <p class="text-blue-800 text-sm italic">
                            Accede a estadísticas agregadas de producción, tratamientos y variedades para tomar decisiones estratégicas basadas en datos de toda la masa social.
                        </p>
                    </div>
                </section>

                <div class="mt-16 pt-12 border-t border-gray-200 text-center">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-6">Digitaliza tu cooperativa con Agro365</h2>
                    <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-8 py-4 rounded-xl bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:from-[var(--color-agro-green)] hover:to-[var(--color-agro-green-dark)] transition-all duration-300 shadow-lg hover:shadow-xl font-semibold text-lg">
                        Solicitar Demo para Cooperativas
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                    <p class="mt-4 text-sm text-gray-500">Prueba gratuita extendida para grupos y cooperativas</p>
                </div>
            </article>
        </div>
    </div>
    @include('partials.footer-seo')
</body>
</html>

