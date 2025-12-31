<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Software para Ingenieros Agrónomos | Gestión Profesional - Agro365</title>
    <meta name="description" content="Software profesional para ingenieros agrónomos en España. Gestión de explotaciones, asesoramiento técnico, cuaderno digital y cumplimiento normativo. Prueba gratis 6 meses.">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url('/ingenieros-agronomos') }}">
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
                        <span class="text-gray-900" itemprop="name">Ingenieros Agrónomos</span>
                        <meta itemprop="position" content="2" />
                    </li>
                </ol>
            </nav>
            <!-- Header -->
            <div class="mb-12">
                <h1 class="text-5xl lg:text-6xl font-bold text-[var(--color-agro-green-dark)] mb-6">
                    Software para Ingenieros y Asesores Agrónomos
                </h1>
                <p class="text-xl text-gray-600 leading-relaxed">
                    La herramienta técnica definitiva para el asesoramiento profesional. <strong>Gestión multi-explotación</strong>, validación de tratamientos, firma electrónica SHA-256 y cumplimiento GIP.
                </p>
            </div>

            <!-- Content -->
            <article class="prose prose-lg max-w-none">
                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">Eficiencia Técnica para Asesores GIP</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        Como <strong>ingeniero agrónomo</strong> o asesor, tu responsabilidad es garantizar la sostenibilidad y legalidad de las explotaciones que gestionas. Agro365 te proporciona el rigor técnico necesario para ejercer tu labor con total seguridad.
                    </p>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        Nuestra plataforma está diseñada para que puedas gestionar decenas de clientes simultáneamente, manteniendo un control exhaustivo sobre cada recomendación y tratamiento.
                    </p>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-6">Funcionalidades de Asesoramiento Avanzado</h2>
                    <div class="grid md:grid-cols-2 gap-6 not-prose">
                        <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">🏢 Panel Multi-Cliente</h3>
                            <p class="text-gray-600 text-sm">Gestiona todas tus explotaciones desde un único acceso. Cambia entre clientes de forma instantánea y segura.</p>
                        </div>
                        <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">🖋️ Firma de Prescripciones</h3>
                            <p class="text-gray-600 text-sm">Genera y firma electrónicamente recetas fitosanitarias e informes de asesoramiento con plena validez legal.</p>
                        </div>
                        <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">🛡️ Validación Vademécum</h3>
                            <p class="text-gray-600 text-sm">Sistema de alertas que verifica si los productos y dosis recomendados cumplen con el registro oficial de fitosanitarios.</p>
                        </div>
                        <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">📊 Informes de Sostenibilidad</h3>
                            <p class="text-gray-600 text-sm">Cruce de datos masivo para generar estadísticas de uso de insumos, indicadores de impacto y cumplimiento de Eco-regímenes.</p>
                        </div>
                    </div>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">Rigor Técnico y Legal</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        Agro365 no es solo una base de datos; es un sistema experto que asiste al ingeniero en la toma de decisiones. Gracias a la integración con el <strong>Vademécum oficial</strong>, garantizamos que tus asesoramientos siempre estén dentro del marco legal vigente.
                    </p>
                    <div class="bg-amber-50 p-6 rounded-xl border border-amber-100">
                        <h4 class="font-bold text-amber-900 mb-2">Alianza con Profesionales</h4>
                        <p class="text-amber-800 text-sm italic">
                            Colaboramos estrechamente con ingenieros agrónomos para integrar las necesidades reales del asesoramiento GIP (Gestión Integrada de Plagas) en nuestra hoja de ruta tecnológica.
                        </p>
                    </div>
                </section>

                <div class="mt-16 pt-12 border-t border-gray-200 text-center">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-6">Optimiza tu asesoramiento profesional</h2>
                    <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-8 py-4 rounded-xl bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:from-[var(--color-agro-green)] hover:to-[var(--color-agro-green-dark)] transition-all duration-300 shadow-lg hover:shadow-xl font-semibold text-lg">
                        Prueba Gratis como Asesor
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                    <p class="mt-4 text-sm text-gray-500">Acceso especial para despachos técnicos e ingenieros independientes</p>
                </div>
            </article>
        </div>
    </div>
    @include('partials.footer-seo')
</body>
</html>

