<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Precios Agro365 | Viticultor y Bodega €9/mes · Denominación de Origen</title>
    <meta name="description" content="Precios claros para cada rol. Plan Viticultor y Bodega desde €9/mes (6 meses gratis). Denominación de Origen: contacta con nosotros. Sin sorpresas.">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url('/precios') }}">
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Schema.org Pricing -->
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "SoftwareApplication",
        "name": "Agro365",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": "Web, iOS, Android",
        "offers": [
            {
                "@@type": "Offer",
                "name": "Plan Viticultor Mensual",
                "price": "9.00",
                "priceCurrency": "EUR",
                "description": "Cuaderno de campo digital, SIGPAC, informes PAC, app móvil"
            },
            {
                "@@type": "Offer",
                "name": "Plan Viticultor Anual",
                "price": "90.00",
                "priceCurrency": "EUR",
                "description": "Plan anual con 2 meses gratis incluidos"
            }
        ]
    }
    </script>

    <!-- FAQ Schema -->
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "FAQPage",
        "mainEntity": [
            {
                "@@type": "Question",
                "name": "¿El viticultor paga si su bodega ya está en Agro365?",
                "acceptedAnswer": {
                    "@@type": "Answer",
                    "text": "No. Si un viticultor accede solo a través de su bodega, la bodega cubre su acceso. Solo paga si quiere su cuenta independiente con todas las funciones."
                }
            },
            {
                "@@type": "Question",
                "name": "¿Cuánto cuesta el plan Viticultor?",
                "acceptedAnswer": {
                    "@@type": "Answer",
                    "text": "€9/mes o €90/año. Durante la fase Beta, los primeros 50 viticultores obtienen 6 meses gratis y 25% de descuento permanente."
                }
            },
            {
                "@@type": "Question",
                "name": "¿Qué precio tiene el plan para Bodegas?",
                "acceptedAnswer": {
                    "@@type": "Answer",
                    "text": "El plan Bodega cuesta €9/mes, igual que el plan Viticultor. Incluye gestión de vendimia, trazabilidad, facturación agrícola y panel de viticultores."
                }
            }
        ]
    }
    </script>
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
                    <a href="{{ url('/') }}" class="text-gray-600 hover:text-[var(--color-agro-green)] transition-colors text-sm">Inicio</a>
                    <a href="{{ url('/#ecosistema') }}" class="text-gray-600 hover:text-[var(--color-agro-green)] transition-colors text-sm">Cómo funciona</a>
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

        <!-- Hero de precios -->
        <section class="py-16 bg-gradient-to-b from-[var(--color-agro-green-bg)] to-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <nav class="mb-8 text-sm text-zinc-500" itemscope itemtype="https://schema.org/BreadcrumbList">
                    <ol class="flex items-center justify-center space-x-2">
                        <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                            <a href="{{ url('/') }}" class="hover:text-[var(--color-agro-green)]" itemprop="item"><span itemprop="name">Inicio</span></a>
                            <meta itemprop="position" content="1" />
                        </li>
                        <span class="mx-2">/</span>
                        <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                            <span class="text-zinc-900 font-medium" itemprop="name">Precios</span>
                            <meta itemprop="position" content="2" />
                        </li>
                    </ol>
                </nav>

                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-gradient-to-r from-[var(--color-agro-green)] to-[var(--color-agro-green-light)] text-white text-sm font-bold mb-6">
                    ⚡ Oferta Beta: primeros 50 viticultores → 6 meses gratis + 25% OFF permanente
                </div>

                <h1 class="text-4xl lg:text-5xl font-bold text-[var(--color-agro-green-dark)] mb-4">
                    Empieza gratis. Paga solo cuando crezcas.
                </h1>
                <p class="text-xl text-zinc-600 max-w-2xl mx-auto">
                    Precio claro para cada rol. Sin sorpresas. Sin llamadas para el viticultor.
                </p>
            </div>
        </section>

        <!-- Tres columnas de precios -->
        <section class="py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid md:grid-cols-3 gap-8">

                    <!-- Viticultor -->
                    <div class="rounded-2xl p-8 border-2 border-[var(--color-agro-green)] relative overflow-hidden shadow-xl bg-white">
                        <div class="absolute top-0 right-0">
                            <div class="bg-gradient-to-r from-[var(--color-agro-green)] to-[var(--color-agro-green-light)] text-white px-5 py-1.5 rounded-bl-2xl font-semibold text-sm">
                                Más popular
                            </div>
                        </div>
                        <div class="mb-6 pt-6">
                            <div class="text-4xl mb-2">🌿</div>
                            <h2 class="text-2xl font-bold text-[var(--color-agro-green-dark)]">Viticultor</h2>
                            <p class="text-zinc-500 text-sm mt-1">Decisión individual · Autoservicio · Sin llamadas</p>
                        </div>

                        <div class="mb-6 p-4 bg-[var(--color-agro-green-bg)] rounded-xl">
                            <div class="inline-block px-3 py-1 bg-[var(--color-agro-green)] text-white rounded-lg font-bold text-sm mb-3">
                                6 MESES GRATIS
                            </div>
                            <div class="flex items-end gap-1.5 mb-1">
                                <span class="text-zinc-500">Después:</span>
                                <span class="text-4xl font-bold text-[var(--color-agro-green-dark)]">€9</span>
                                <span class="text-zinc-500 mb-1">/mes</span>
                            </div>
                            <p class="text-sm text-zinc-600">o <strong>€90/año</strong> — equivale a <strong>€7,50/mes</strong></p>
                            <p class="text-xs text-[var(--color-agro-green)] font-semibold mt-1.5">⚡ Primeros 50: 25% OFF permanente → €6,75/mes o €67,50/año</p>
                        </div>

                        <ul class="space-y-3 mb-8 text-sm">
                            @foreach([
                                'Parcelas ilimitadas',
                                'Cuaderno de campo digital (obligatorio 2027)',
                                'Gestión SIGPAC completa',
                                'Informes oficiales con firma electrónica SHA-256',
                                'Dashboard cumplimiento PAC en tiempo real',
                                'Teledetección NDVI satelital',
                                'App móvil (funciona sin conexión)',
                                'Conexión a 1 bodega incluida',
                                'Bodegas adicionales: +€2/mes cada una',
                                'Soporte por email (48h)',
                            ] as $feature)
                            <li class="flex items-start gap-2.5">
                                <svg class="w-5 h-5 text-[var(--color-agro-green)] flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-zinc-700">{{ $feature }}</span>
                            </li>
                            @endforeach
                        </ul>

                        <a href="{{ route('register') }}" class="block w-full text-center px-6 py-4 rounded-xl bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:from-[var(--color-agro-green)] hover:to-[var(--color-agro-green-dark)] transition-all duration-300 shadow-lg font-bold text-lg">
                            Comenzar Gratis →
                        </a>
                        <p class="text-center text-xs text-zinc-400 mt-3">Sin tarjeta requerida · Cancela cuando quieras</p>
                    </div>

                    <!-- Bodega -->
                    <div class="rounded-2xl p-8 border-2 border-red-200 hover:border-red-400 transition-all duration-300 bg-white shadow-md">
                        <div class="mb-6">
                            <div class="text-4xl mb-2">🍷</div>
                            <h2 class="text-2xl font-bold text-[var(--color-agro-green-dark)]">Bodega</h2>
                            <p class="text-zinc-500 text-sm mt-1">Precio único · Demo gratuita · Onboarding incluido</p>
                        </div>

                        <div class="mb-6 p-4 bg-red-50 rounded-xl border border-red-100">
                            <div class="inline-block px-3 py-1 bg-red-500 text-white rounded-lg font-bold text-sm mb-3">
                                6 MESES GRATIS
                            </div>
                            <div class="flex items-end gap-1.5 mb-1">
                                <span class="text-zinc-500">Después:</span>
                                <span class="text-4xl font-bold text-red-700">€9</span>
                                <span class="text-zinc-500 mb-1">/mes</span>
                            </div>
                            <p class="text-sm text-zinc-600">o <strong>€90/año</strong> — equivale a <strong>€7,50/mes</strong></p>
                            <p class="text-xs text-red-600 font-semibold mt-1.5">🎁 Oferta lanzamiento: onboarding incluido + migración gratuita</p>
                        </div>

                        <ul class="space-y-3 mb-8 text-sm">
                            @foreach([
                                'Todo del plan Viticultor',
                                'Panel de viticultores en tiempo real',
                                'Gestión completa de vendimia',
                                'Trazabilidad desde la cepa hasta la botella',
                                'Facturación agrícola integrada',
                                'Comparativa rendimientos real vs estimado',
                                'Informes consolidados de bodega',
                                'Invitación a viticultores (enlace simple)',
                                'Soporte prioritario (24h)',
                                'Onboarding personalizado incluido',
                            ] as $feature)
                            <li class="flex items-start gap-2.5">
                                <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-zinc-700">{{ $feature }}</span>
                            </li>
                            @endforeach
                        </ul>

                        <a href="{{ route('register') }}" class="block w-full text-center px-6 py-4 rounded-xl bg-gradient-to-r from-red-600 to-red-500 text-white hover:from-red-700 hover:to-red-600 transition-all duration-300 shadow-lg font-bold text-lg">
                            Comenzar Gratis →
                        </a>
                        <p class="text-center text-xs text-zinc-400 mt-3">Sin tarjeta requerida · Cancela cuando quieras</p>
                    </div>

                    <!-- DO -->
                    <div class="rounded-2xl p-8 border-2 border-amber-200 hover:border-amber-400 transition-all duration-300 bg-white shadow-md">
                        <div class="mb-6">
                            <div class="text-4xl mb-2">🏛️</div>
                            <h2 class="text-2xl font-bold text-[var(--color-agro-green-dark)]">Denominación de Origen</h2>
                            <p class="text-zinc-500 text-sm mt-1">Solución a medida · Contrato anual · Account manager dedicado</p>
                        </div>

                        <div class="mb-6 p-4 bg-amber-50 rounded-xl border border-amber-100">
                            <div class="text-center py-4">
                                <div class="text-5xl mb-3">🤝</div>
                                <p class="text-lg font-bold text-amber-800 mb-2">Precio a medida</p>
                                <p class="text-sm text-zinc-600">Cada DO tiene su dimensión, sus bodegas y sus necesidades. Cuéntanos el tuyo y te preparamos una propuesta sin compromiso.</p>
                            </div>
                        </div>

                        <ul class="space-y-3 mb-8 text-sm">
                            @foreach([
                                'Todo del plan Bodega',
                                'Alta y gestión de bodegas adscritas',
                                'Panel de supervisión centralizado',
                                'Vista de viticultores de todas las bodegas',
                                'Alertas automáticas de incumplimiento',
                                'Informes consolidados por denominación y campaña',
                                'Firma electrónica para validación de informes',
                                'Integración con sistemas existentes (API)',
                                'Account manager dedicado',
                                'SLA 99,9% uptime garantizado',
                                'Soporte telefónico directo',
                            ] as $feature)
                            <li class="flex items-start gap-2.5">
                                <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-zinc-700">{{ $feature }}</span>
                            </li>
                            @endforeach
                        </ul>

                        <a href="mailto:info@agro365.es?subject=Consulta%20DO%20Agro365" class="block w-full text-center px-6 py-4 rounded-xl border-2 border-amber-500 text-amber-800 hover:bg-amber-500 hover:text-white transition-all duration-300 font-bold text-lg">
                            Contactar con Ventas →
                        </a>
                        <p class="text-center text-xs text-zinc-400 mt-3">Solución a medida para tu denominación</p>
                    </div>
                </div>

                <!-- Nota viticultores en bodegas -->
                <div class="mt-10 max-w-3xl mx-auto text-center p-6 bg-[var(--color-agro-green-bg)] rounded-2xl border border-[var(--color-agro-green-light)]/30">
                    <p class="text-zinc-700 text-sm">
                        <strong>Nota importante para Bodegas:</strong> los viticultores que conectas
                        <strong>no pagan plan propio</strong> para que puedas ver sus datos.
                        La bodega cubre su acceso. Solo pagan si quieren su cuenta independiente completa (€9/mes).
                        Esto elimina la fricción de adopción — el viticultor no tiene que pagar nada para que su bodega lo vea.
                    </p>
                </div>
            </div>
        </section>

        <!-- Tabla comparativa -->
        <section class="py-16 bg-zinc-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] text-center mb-10">
                    Comparativa completa
                </h2>
                <div class="overflow-x-auto rounded-2xl border border-zinc-200 shadow-sm">
                    <table class="w-full text-sm bg-white">
                        <thead>
                            <tr class="bg-zinc-50 border-b border-zinc-200">
                                <th class="text-left px-6 py-4 font-semibold text-zinc-600 w-2/5">Funcionalidad</th>
                                <th class="text-center px-4 py-4 font-bold text-[var(--color-agro-green-dark)]">🌿 Viticultor<br><span class="font-normal text-xs text-zinc-400">€9/mes</span></th>
                                <th class="text-center px-4 py-4 font-bold text-red-700">🍷 Bodega<br><span class="font-normal text-xs text-zinc-400">€9/mes</span></th>
                                <th class="text-center px-4 py-4 font-bold text-amber-700">🏛️ DO<br><span class="font-normal text-xs text-zinc-400">Consultar</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                            @php
                            $rows = [
                                ['Cuaderno de campo digital', '✅', '✅', '✅'],
                                ['Gestión SIGPAC', '✅', '✅', '✅'],
                                ['Informes oficiales PAC', '✅', '✅', '✅'],
                                ['Teledetección NDVI', '✅', '✅', '✅'],
                                ['App móvil offline', '✅', '✅', '✅'],
                                ['Panel de viticultores', '❌', '✅', '✅'],
                                ['Gestión de vendimia', '❌', '✅', '✅'],
                                ['Trazabilidad bodega', '❌', '✅', '✅'],
                                ['Facturación agrícola', '❌', '✅', '✅'],
                                ['Comparativa rendimientos', '❌', '✅', '✅'],
                                ['Panel supervisión DO', '❌', '❌', '✅'],
                                ['Alertas de incumplimiento', '❌', '❌', '✅'],
                                ['Informes consolidados', '❌', 'Bodega', 'DO completa'],
                                ['API integración', '❌', '❌', '✅'],
                                ['Account manager', '❌', '❌', '✅'],
                                ['Soporte', 'Email 48h', 'Prioritario 24h', 'Teléfono directo'],
                                ['Onboarding', 'Self-service', '✅ incluido', '✅ + formación'],
                            ];
                            @endphp
                            @foreach($rows as $i => $row)
                            <tr class="{{ $i % 2 === 0 ? 'bg-white' : 'bg-zinc-50/50' }}">
                                <td class="px-6 py-3 font-medium text-zinc-700">{{ $row[0] }}</td>
                                <td class="px-4 py-3 text-center text-zinc-600">{{ $row[1] }}</td>
                                <td class="px-4 py-3 text-center text-zinc-600">{{ $row[2] }}</td>
                                <td class="px-4 py-3 text-center text-zinc-600">{{ $row[3] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- FAQs de precios -->
        <section class="py-16 bg-white">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] text-center mb-10">
                    Preguntas frecuentes sobre precios
                </h2>
                <div class="space-y-4">
                    @php
                    $faqs = [
                        ['¿El viticultor paga si su bodega ya está en Agro365?', 'No. Si un viticultor accede solo a través de su bodega (sin cuenta propia independiente), la bodega cubre su acceso. Solo paga si quiere su cuenta independiente con todas las funciones.'],
                        ['¿El precio de la Bodega incluye todos los viticultores conectados?', 'Sí. Con €9/mes la bodega puede gestionar a todos sus viticultores. Los viticultores que acceden solo a través de su bodega no pagan nada aparte.'],
                        ['¿Cómo se calcula el precio para una Denominación de Origen?', 'Cada DO tiene dimensiones y necesidades diferentes. Contáctanos y te preparamos una propuesta personalizada sin compromiso.'],
                        ['¿Hay descuento para cooperativas?', 'Las cooperativas con más de 100 socios viticultores tienen condiciones especiales equivalentes al plan Bodega Pro. Contáctanos.'],
                        ['¿Se puede cambiar de plan en cualquier momento?', 'Sí, siempre hacia arriba. Los cambios a plan inferior se aplican al siguiente período de facturación.'],
                        ['¿Qué pasa con mis datos si cancelo?', 'Tus datos son siempre tuyos. Puedes exportarlos en formato estándar en cualquier momento antes o después de cancelar. Nunca retenemos información.'],
                    ];
                    @endphp
                    @foreach($faqs as $faq)
                    <details class="border border-zinc-200 rounded-xl overflow-hidden group">
                        <summary class="flex items-center justify-between px-6 py-4 cursor-pointer bg-white hover:bg-zinc-50 transition-colors font-semibold text-zinc-800 text-sm">
                            {{ $faq[0] }}
                            <svg class="w-5 h-5 text-zinc-400 group-open:rotate-180 transition-transform flex-shrink-0 ml-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </summary>
                        <div class="px-6 py-4 bg-zinc-50 text-sm text-zinc-600 border-t border-zinc-100">
                            {{ $faq[1] }}
                        </div>
                    </details>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- CTA final -->
        <section class="py-16 bg-gradient-to-br from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)]">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-3xl lg:text-4xl font-bold text-white mb-4">
                    La normativa no espera. Tu viñedo tampoco.
                </h2>
                <p class="text-green-100 text-lg mb-8">
                    El cuaderno de campo digital es obligatorio desde 2027. Agro365 está listo hoy.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('register') }}" class="inline-flex items-center justify-center gap-2 px-8 py-4 rounded-xl bg-white text-[var(--color-agro-green-dark)] hover:bg-green-50 transition-all font-bold text-lg shadow-lg">
                        🌿 Empezar como Viticultor — Gratis
                    </a>
                    <a href="mailto:info@agro365.es?subject=Consulta%20Denominaci%C3%B3n%20de%20Origen" class="inline-flex items-center justify-center gap-2 px-8 py-4 rounded-xl border-2 border-white text-white hover:bg-white/10 transition-all font-semibold text-lg">
                        🏛️ Contactar para Denominación de Origen
                    </a>
                </div>
            </div>
        </section>

    </main>

    @include('partials.footer-seo')
</body>
</html>
