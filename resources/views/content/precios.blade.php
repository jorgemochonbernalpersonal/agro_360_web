<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Precios Agro365 | Viticultor gratis · Productor 19€ · Bodega desde 19€ · Denominación de Origen</title>
    <meta name="description" content="Precios claros para cada perfil. Viticultor básico gratis para siempre. Capa Completa: invitado 9€, independiente 14€, productor 19€. Bodega desde 19€ (escala con red). DO desde 149€/mes.">
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
                "name": "Viticultor invitado — Capa Completa",
                "price": "9.00",
                "priceCurrency": "EUR",
                "description": "SIGPAC, teledetección, PAC, facturación, Verifactu — viticultor invitado por bodega"
            },
            {
                "@@type": "Offer",
                "name": "Viticultor independiente — Mensual",
                "price": "14.00",
                "priceCurrency": "EUR",
                "description": "Capa Completa para viticultor sin bodega asociada"
            },
            {
                "@@type": "Offer",
                "name": "Viticultor independiente — Anual",
                "price": "130.00",
                "priceCurrency": "EUR",
                "description": "Plan anual viticultor independiente"
            },
            {
                "@@type": "Offer",
                "name": "Productor (viñedo + bodega) — Mensual",
                "price": "19.00",
                "priceCurrency": "EUR",
                "description": "Plan combinado para quien cultiva y elabora su propio vino"
            },
            {
                "@@type": "Offer",
                "name": "Productor (viñedo + bodega) — Anual",
                "price": "180.00",
                "priceCurrency": "EUR",
                "description": "Plan anual productor"
            },
            {
                "@@type": "Offer",
                "name": "Bodega — solo su vino",
                "price": "19.00",
                "priceCurrency": "EUR",
                "description": "Bodega sin viticultores externos gestionados"
            },
            {
                "@@type": "Offer",
                "name": "Bodega — red 1-10 viticultores",
                "price": "29.00",
                "priceCurrency": "EUR",
                "description": "Bodega con hasta 10 viticultores gestionados en la plataforma"
            },
            {
                "@@type": "Offer",
                "name": "Bodega — red 11-30 viticultores",
                "price": "49.00",
                "priceCurrency": "EUR",
                "description": "Bodega con 11-30 viticultores gestionados"
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
                    "text": "No necesariamente. El cuaderno de campo, parcelas, SIGPAC, fenología y plantaciones son gratis para siempre. Si el viticultor quiere módulos avanzados (teledetección, PAC, Verifactu, facturación...) paga 9€/mes si está vinculado a una bodega, o 14€/mes si es independiente."
                }
            },
            {
                "@@type": "Question",
                "name": "¿Cuánto paga una bodega que gestiona viticultores proveedores?",
                "acceptedAnswer": {
                    "@@type": "Answer",
                    "text": "El precio de la bodega escala con el número de viticultores que gestiona: 19€/mes (solo su vino), 29€/mes (1-10 viticultores), 49€/mes (11-30), 79€/mes (31-60), 119€/mes (61-100). Las bodegas dentro de una DO no pagan cuota individual."
                }
            },
            {
                "@@type": "Question",
                "name": "¿Qué es el plan Productor?",
                "acceptedAnswer": {
                    "@@type": "Answer",
                    "text": "El plan Productor es para quien cultiva su propio viñedo y elabora su propio vino en la misma explotación. Une las funciones de viticultor y bodega en una sola cuenta por 19€/mes o 180€/año."
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
                        <span class="text-xl font-bold text-[var(--color-agro-green-dark)]">{{ __('Agro365') }}</span>
                    </a>
                </div>
                <div class="flex items-center gap-4">
                    <a href="{{ url('/') }}" class="text-gray-600 hover:text-[var(--color-agro-green)] transition-colors text-sm">Inicio</a>
                    <a href="{{ url('/#ecosistema') }}" class="text-gray-600 hover:text-[var(--color-agro-green)] transition-colors text-sm">Como funciona</a>
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
                            <a href="{{ url('/') }}" class="hover:text-[var(--color-agro-green)]" itemprop="item"><span itemprop="name">{{ __('Inicio') }}</span></a>
                            <meta itemprop="position" content="1" />
                        </li>
                        <span class="mx-2">/</span>
                        <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                            <span class="text-zinc-900 font-medium" itemprop="name">{{ __('Precios') }}</span>
                            <meta itemprop="position" content="2" />
                        </li>
                    </ol>
                </nav>

                <h1 class="text-4xl lg:text-5xl font-bold text-[var(--color-agro-green-dark)] mb-4">{{ __('Precio justo para cada perfil') }}</h1>
                <p class="text-xl text-zinc-600 max-w-2xl mx-auto">{{ __('La operacion diaria es gratis para siempre. Solo pagas por lo que te ahorra tiempo o te abre mercado.') }}</p>
            </div>
        </section>

        <!-- Cuatro columnas de precios -->
        <section class="py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid md:grid-cols-2 xl:grid-cols-4 gap-6">

                    <!-- Viticultor -->
                    <div class="rounded-2xl p-7 border-2 border-[var(--color-agro-green)] relative overflow-hidden shadow-xl bg-white">
                        <div class="absolute top-0 right-0">
                            <div class="bg-gradient-to-r from-[var(--color-agro-green)] to-[var(--color-agro-green-light)] text-white px-4 py-1.5 rounded-bl-2xl font-semibold text-xs">
                                Mas popular
                            </div>
                        </div>
                        <div class="mb-5 pt-5">
                            <div class="text-3xl mb-2">🌿</div>
                            <h2 class="text-xl font-bold text-[var(--color-agro-green-dark)]">{{ __('Viticultor') }}</h2>
                            <p class="text-zinc-500 text-xs mt-1">{{ __('Autoservicio · Sin llamadas · Cancela cuando quieras') }}</p>
                        </div>

                        <!-- Free permanente -->
                        <div class="mb-3 p-3 bg-zinc-50 rounded-xl border border-zinc-200">
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-semibold text-zinc-700 text-xs">{{ __('Operacion diaria') }}</span>
                                <span class="text-base font-bold text-zinc-800">{{ __('Gratis') }}</span>
                            </div>
                            <p class="text-xs text-zinc-500">{{ __('Cuaderno, parcelas, fenologia, plagas, plantaciones — para siempre.') }}</p>
                        </div>

                        <!-- Capa A invitado -->
                        <div class="mb-3 p-3 bg-[var(--color-agro-green-bg)] rounded-xl border border-[var(--color-agro-green-light)]/40">
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-semibold text-[var(--color-agro-green-dark)] text-xs">{{ __('Capa Completa (invitado)') }}</span>
                                <span class="text-base font-bold text-[var(--color-agro-green-dark)]">{{ __('9€/mes') }}</span>
                            </div>
                            <p class="text-xs text-zinc-600">o <strong>{{ __('85€/ano') }}</strong> · SIGPAC, teledeteccion, PAC, Verifactu.</p>
                        </div>

                        <!-- Capa A independiente -->
                        <div class="mb-5 p-3 bg-[var(--color-agro-green-bg)] rounded-xl border-2 border-[var(--color-agro-green)]">
                            <div class="inline-block px-2 py-0.5 bg-[var(--color-agro-green)] text-white rounded font-bold text-xs mb-1.5">
                                INDEPENDIENTE
                            </div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-semibold text-[var(--color-agro-green-dark)] text-xs">{{ __('Sin bodega asociada') }}</span>
                                <span class="text-xl font-bold text-[var(--color-agro-green-dark)]">{{ __('14€/mes') }}</span>
                            </div>
                            <p class="text-xs text-zinc-600">o <strong>{{ __('130€/ano') }}</strong> · acceso completo.</p>
                        </div>

                        <ul class="space-y-2 mb-6 text-xs">
                            @foreach([
                                'Cuaderno de campo digital (obligatorio 2027)',
                                'SIGPAC y gestion de parcelas',
                                'Teledeteccion NDVI satelital',
                                'PAC y normativa vigente',
                                'Facturacion agricola + Verifactu',
                                'Vendimias y plantaciones',
                                'App movil (funciona sin conexion)',
                                'Soporte por email (48h)',
                            ] as $feature)
                            <li class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-[var(--color-agro-green)] flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-zinc-700">{{ $feature }}</span>
                            </li>
                            @endforeach
                        </ul>

                        <div class="mb-4 bg-amber-50 border border-amber-200 rounded-xl px-3 py-2.5 text-center">
                            <p class="text-xs font-semibold text-amber-800">{{ __('🎁 3 meses gratis al registrarte') }}</p>
                            <p class="text-xs text-amber-600 mt-0.5">{{ __('Sin tarjeta · Sin compromiso') }}</p>
                        </div>

                        <a href="{{ route('register') }}" class="block w-full text-center px-4 py-3 rounded-xl bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:from-[var(--color-agro-green)] hover:to-[var(--color-agro-green-dark)] transition-all duration-300 shadow-lg font-bold text-sm">
                            Comenzar Gratis
                        </a>
                        <p class="text-center text-xs text-zinc-400 mt-2">{{ __('Sin tarjeta · Cancela cuando quieras') }}</p>
                    </div>

                    <!-- Productor -->
                    <div class="rounded-2xl p-7 border-2 border-violet-200 hover:border-violet-400 transition-all duration-300 bg-white shadow-md">
                        <div class="mb-5">
                            <div class="text-3xl mb-2">🌾</div>
                            <h2 class="text-xl font-bold text-[var(--color-agro-green-dark)]">{{ __('Productor') }}</h2>
                            <p class="text-zinc-500 text-xs mt-1">{{ __('Vinedo + bodega propios en una sola cuenta') }}</p>
                        </div>

                        <!-- Free permanente -->
                        <div class="mb-3 p-3 bg-zinc-50 rounded-xl border border-zinc-200">
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-semibold text-zinc-700 text-xs">{{ __('Operacion completa') }}</span>
                                <span class="text-base font-bold text-zinc-800">{{ __('Gratis') }}</span>
                            </div>
                            <p class="text-xs text-zinc-500">{{ __('Cuaderno, parcelas, depositos, vendimias, elaboracion, facturacion basica.') }}</p>
                        </div>

                        <!-- Capa A -->
                        <div class="mb-5 p-3 bg-violet-50 rounded-xl border-2 border-violet-400">
                            <div class="inline-block px-2 py-0.5 bg-violet-500 text-white rounded font-bold text-xs mb-1.5">
                                PLAN COMPLETO
                            </div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-semibold text-violet-800 text-xs">{{ __('Telede. + Verifactu + PAC') }}</span>
                                <span class="text-xl font-bold text-violet-700">{{ __('19€/mes') }}</span>
                            </div>
                            <p class="text-xs text-zinc-600">o <strong>{{ __('180€/ano') }}</strong> · plan combinado vinedo+bodega.</p>
                        </div>

                        <ul class="space-y-2 mb-6 text-xs">
                            @foreach([
                                'Todo lo del viticultor independiente',
                                'Gestion de depositos y vinos',
                                'Recepcion de vendimia propia',
                                'Elaboracion y tratamientos de vino',
                                'Facturacion de su propio vino',
                                'Lotes y etiquetado',
                                'Sin cuota de red (solo su vino)',
                                'Soporte por email (48h)',
                            ] as $feature)
                            <li class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-violet-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-zinc-700">{{ $feature }}</span>
                            </li>
                            @endforeach
                        </ul>

                        <div class="mb-4 bg-amber-50 border border-amber-200 rounded-xl px-3 py-2.5 text-center">
                            <p class="text-xs font-semibold text-amber-800">{{ __('🎁 3 meses gratis al registrarte') }}</p>
                            <p class="text-xs text-amber-600 mt-0.5">{{ __('Sin tarjeta · Sin compromiso') }}</p>
                        </div>

                        <a href="{{ route('register', ['role' => 'producer']) }}" class="block w-full text-center px-4 py-3 rounded-xl bg-gradient-to-r from-violet-700 to-violet-500 text-white hover:from-violet-800 hover:to-violet-600 transition-all duration-300 shadow-lg font-bold text-sm">
                            Comenzar como Productor
                        </a>
                        <p class="text-center text-xs text-zinc-400 mt-2">{{ __('Sin tarjeta · Cancela cuando quieras') }}</p>
                    </div>

                    <!-- Bodega -->
                    <div class="rounded-2xl p-7 border-2 border-red-200 hover:border-red-400 transition-all duration-300 bg-white shadow-md">
                        <div class="mb-5">
                            <div class="text-3xl mb-2">🍷</div>
                            <h2 class="text-xl font-bold text-[var(--color-agro-green-dark)]">{{ __('Bodega') }}</h2>
                            <p class="text-zinc-500 text-xs mt-1">{{ __('Demo gratuita · Onboarding incluido') }}</p>
                        </div>

                        <!-- Bodega en DO -->
                        <div class="mb-3 p-3 bg-zinc-50 rounded-xl border border-zinc-200">
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-semibold text-zinc-700 text-xs">{{ __('Dentro de una DO') }}</span>
                                <span class="text-base font-bold text-zinc-800">{{ __('Gratis') }}</span>
                            </div>
                            <p class="text-xs text-zinc-500">{{ __('Cubierta por el paquete de la Denominacion de Origen.') }}</p>
                        </div>

                        <!-- Tramos de red -->
                        <div class="mb-5 rounded-xl border border-red-200 overflow-hidden">
                            <div class="bg-red-50 border-b border-red-200 px-3 py-2">
                                <span class="text-xs font-bold text-red-700">INDEPENDIENTE — precio por red</span>
                            </div>
                            <table class="w-full text-xs">
                                <tbody class="divide-y divide-red-100 bg-white">
                                    @php
                                    $wineryTiers = [
                                        ['Solo su vino', '19€/mes', '180€/año'],
                                        ['1–10 viticultores', '29€/mes', '290€/año'],
                                        ['11–30 viticultores', '49€/mes', '490€/año'],
                                        ['31–60 viticultores', '79€/mes', '790€/año'],
                                        ['61–100 viticultores', '119€/mes', '1.190€/año'],
                                        ['+100 viticultores', 'A negociar', '—'],
                                    ];
                                    @endphp
                                    @foreach($wineryTiers as $tier)
                                    <tr>
                                        <td class="px-3 py-1.5 text-zinc-700">{{ $tier[0] }}</td>
                                        <td class="px-3 py-1.5 text-right font-semibold text-red-700">{{ $tier[1] }}</td>
                                        <td class="px-3 py-1.5 text-right text-zinc-500">{{ $tier[2] }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <ul class="space-y-2 mb-6 text-xs">
                            @foreach([
                                'Panel de viticultores en tiempo real',
                                'Gestion completa de vendimia',
                                'Trazabilidad cepa → botella',
                                'Facturacion agricola integrada',
                                'Gestion de vinos y elaboracion',
                                'Informes consolidados de bodega',
                                'Invitacion a viticultores (enlace simple)',
                                'Onboarding personalizado incluido',
                                'Soporte prioritario (24h)',
                            ] as $feature)
                            <li class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-red-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-zinc-700">{{ $feature }}</span>
                            </li>
                            @endforeach
                        </ul>

                        <div class="mb-4 bg-amber-50 border border-amber-200 rounded-xl px-3 py-2.5 text-center">
                            <p class="text-xs font-semibold text-amber-800">{{ __('🎁 3 meses gratis al registrarte') }}</p>
                            <p class="text-xs text-amber-600 mt-0.5">{{ __('Demo gratuita · Onboarding incluido') }}</p>
                        </div>

                        <a href="{{ route('register', ['role' => 'winery']) }}" class="block w-full text-center px-4 py-3 rounded-xl bg-gradient-to-r from-red-600 to-red-500 text-white hover:from-red-700 hover:to-red-600 transition-all duration-300 shadow-lg font-bold text-sm">
                            Comenzar como Bodega
                        </a>
                        <p class="text-center text-xs text-zinc-400 mt-2">{{ __('Sin tarjeta requerida · Cancela cuando quieras') }}</p>
                    </div>

                    <!-- DO -->
                    <div class="rounded-2xl p-7 border-2 border-amber-200 hover:border-amber-400 transition-all duration-300 bg-white shadow-md">
                        <div class="mb-5">
                            <div class="text-3xl mb-2">🏛️</div>
                            <h2 class="text-xl font-bold text-[var(--color-agro-green-dark)]">{{ __('Denominacion de Origen') }}</h2>
                            <p class="text-zinc-500 text-xs mt-1">{{ __('Solucion a medida · Contrato anual · Account manager dedicado') }}</p>
                        </div>

                        <!-- Tabla de escala DO -->
                        <div class="mb-5 rounded-xl border border-amber-200 overflow-hidden">
                            <table class="w-full text-xs">
                                <thead>
                                    <tr class="bg-amber-50 border-b border-amber-200">
                                        <th class="text-left px-3 py-2 font-semibold text-amber-800">{{ __('Bodegas') }}</th>
                                        <th class="text-right px-3 py-2 font-semibold text-amber-800">{{ __('Mensual') }}</th>
                                        <th class="text-right px-3 py-2 font-semibold text-amber-800">{{ __('Anual') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-amber-100 bg-white">
                                    @php
                                    $doTiers = [
                                        ['Hasta 25', '149€', '1.400€'],
                                        ['26 – 50', '249€', '2.350€'],
                                        ['51 – 75', '349€', '3.300€'],
                                        ['76 – 100', '449€', '4.250€'],
                                        ['+100', 'A negociar', 'A negociar'],
                                    ];
                                    @endphp
                                    @foreach($doTiers as $tier)
                                    <tr>
                                        <td class="px-3 py-1.5 text-zinc-700">{{ $tier[0] }} bodegas</td>
                                        <td class="px-3 py-1.5 text-right font-semibold text-amber-800">{{ $tier[1] }}</td>
                                        <td class="px-3 py-1.5 text-right text-zinc-600">{{ $tier[2] }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <p class="text-xs text-zinc-500 mb-5 text-center">{{ __('Las bodegas incluidas en el paquete DO no pagan cuota individual.') }}</p>

                        <ul class="space-y-2 mb-6 text-xs">
                            @foreach([
                                'Acceso completo para todas las bodegas adscritas',
                                'Panel de supervision centralizado',
                                'Vista de viticultores de todas las bodegas',
                                'Alertas automaticas de incumplimiento',
                                'Informes agregados por denominacion',
                                'Trazabilidad denominacion completa',
                                'Firma electronica para validacion',
                                'Integracion API con sistemas existentes',
                                'Account manager dedicado',
                                'SLA 99,9% uptime garantizado',
                            ] as $feature)
                            <li class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-amber-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-zinc-700">{{ $feature }}</span>
                            </li>
                            @endforeach
                        </ul>

                        <a href="https://wa.me/34684217167?text=Hola%2C%20soy%20una%20Denominaci%C3%B3n%20de%20Origen%20y%20me%20interesa%20Agro365" target="_blank" rel="noopener noreferrer" class="flex items-center justify-center gap-2 w-full text-center px-4 py-3 rounded-xl bg-amber-500 hover:bg-amber-600 text-white transition-all duration-300 font-bold text-sm">
                            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                            Hablar por WhatsApp
                        </a>
                        <a href="mailto:info@agro365.es?subject=Consulta%20DO%20Agro365" class="flex items-center justify-center gap-2 w-full text-center px-4 py-2.5 rounded-xl border border-amber-300 text-amber-800 hover:bg-amber-50 transition-all duration-300 font-medium text-xs mt-2">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            Enviar un email
                        </a>
                        <p class="text-center text-xs text-zinc-400 mt-2">{{ __('Propuesta sin compromiso para tu denominacion') }}</p>
                    </div>
                </div>

                <!-- Nota modelo -->
                <div class="mt-10 max-w-3xl mx-auto text-center p-6 bg-[var(--color-agro-green-bg)] rounded-2xl border border-[var(--color-agro-green-light)]/30">
                    <p class="text-zinc-700 text-sm">
                        <strong>{{ __('Como funciona el modelo de precios:') }}</strong>
                        la <strong>{{ __('operacion diaria es gratis para siempre') }}</strong> (cuaderno, parcelas, depositos, vendimias, elaboracion, facturacion basica).
                        Solo pagas por la <strong>{{ __('Capa Completa') }}</strong> (teledeteccion, PAC, Verifactu, estadisticas) y por la <strong>{{ __('Capa de Red') }}</strong>
                        en bodegas que gestionan viticultores proveedores. El precio de la bodega crece con su red, no con su facturacion.
                    </p>
                </div>
            </div>
        </section>

        <!-- Tabla comparativa -->
        <section class="py-16 bg-zinc-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] text-center mb-10">{{ __('Comparativa completa por perfil') }}</h2>
                <div class="overflow-x-auto rounded-2xl border border-zinc-200 shadow-sm">
                    <table class="w-full text-sm bg-white">
                        <thead>
                            <tr class="bg-zinc-50 border-b border-zinc-200">
                                <th class="text-left px-5 py-4 font-semibold text-zinc-600 w-1/4">{{ __('Funcionalidad') }}</th>
                                <th class="text-center px-2 py-4 font-bold text-zinc-500">🌿 Vit. Basico<br><span class="font-normal text-xs text-zinc-400">Gratis</span></th>
                                <th class="text-center px-2 py-4 font-bold text-[var(--color-agro-green-dark)]">🌿 Vit. Completo<br><span class="font-normal text-xs text-zinc-400">9-14€/mes</span></th>
                                <th class="text-center px-2 py-4 font-bold text-violet-700">🌾 Productor<br><span class="font-normal text-xs text-zinc-400">19€/mes</span></th>
                                <th class="text-center px-2 py-4 font-bold text-red-700">🍷 Bodega<br><span class="font-normal text-xs text-zinc-400">desde 19€/mes</span></th>
                                <th class="text-center px-2 py-4 font-bold text-amber-700">🏛️ DO<br><span class="font-normal text-xs text-zinc-400">desde 149€/mes</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                            @php
                            $rows = [
                                ['Cuaderno de campo',           '✅', '✅', '✅', '✅', '—'],
                                ['Parcelas + SIGPAC',           '✅', '✅', '✅', '✅', '—'],
                                ['Mapas',                       '✅', '✅', '✅', '✅', '—'],
                                ['Plantaciones / fenologia',    '✅', '✅', '✅', '✅', '—'],
                                ['Depositos y elaboracion',     '❌', '❌', '✅', '✅', '—'],
                                ['Recepcion de vendimia',       '❌', '❌', '✅', '✅', '—'],
                                ['Facturacion basica',          '✅', '✅', '✅', '✅', '—'],
                                ['Teledeteccion satelital',     '❌', '✅', '✅', '✅', '—'],
                                ['PAC y normativa',             '❌', '✅', '✅', '💶', '—'],
                                ['Verifactu (sello AEAT)',      '❌', '✅', '✅', '✅', '—'],
                                ['Panel de viticultores',       '❌', '❌', '❌', '✅', '✅'],
                                ['Informes consolidados',       '❌', '❌', '❌', '✅', '✅'],
                                ['Supervision DO',              '❌', '❌', '❌', '❌', '✅'],
                            ];
                            @endphp
                            @foreach($rows as $i => $row)
                            <tr class="{{ $i % 2 === 0 ? 'bg-white' : 'bg-zinc-50/50' }}">
                                <td class="px-5 py-3 font-medium text-zinc-700">{{ $row[0] }}</td>
                                <td class="px-2 py-3 text-center text-zinc-500 text-xs">{{ $row[1] }}</td>
                                <td class="px-2 py-3 text-center text-zinc-600 text-xs">{{ $row[2] }}</td>
                                <td class="px-2 py-3 text-center text-zinc-600 text-xs">{{ $row[3] }}</td>
                                <td class="px-2 py-3 text-center text-zinc-600 text-xs">{{ $row[4] }}</td>
                                <td class="px-2 py-3 text-center text-zinc-600 text-xs">{{ $row[5] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="text-center text-xs text-zinc-400 mt-3">💶 = disponible como modulo de pago adicional · ❌ = no disponible en ese perfil · — = no aplica</p>
            </div>
        </section>

        <!-- Resumen ejecutivo -->
        <section class="py-12 bg-white">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-2xl font-bold text-[var(--color-agro-green-dark)] text-center mb-8">{{ __('Resumen de precios') }}</h2>
                <div class="overflow-x-auto rounded-2xl border border-zinc-200 shadow-sm">
                    <table class="w-full text-sm bg-white">
                        <thead>
                            <tr class="bg-zinc-50 border-b border-zinc-200">
                                <th class="text-left px-6 py-3 font-semibold text-zinc-600">{{ __('Perfil') }}</th>
                                <th class="text-right px-6 py-3 font-semibold text-zinc-600">{{ __('Mensual') }}</th>
                                <th class="text-right px-6 py-3 font-semibold text-zinc-600">{{ __('Anual') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                            <tr class="bg-white">
                                <td class="px-6 py-3 text-zinc-700">{{ __('Viticultor — operacion diaria (todos)') }}</td>
                                <td class="px-6 py-3 text-right font-semibold text-zinc-800">{{ __('Gratis') }}</td>
                                <td class="px-6 py-3 text-right text-zinc-500">{{ __('Gratis') }}</td>
                            </tr>
                            <tr class="bg-zinc-50/50">
                                <td class="px-6 py-3 text-zinc-700">{{ __('Viticultor invitado por bodega — Capa Completa') }}</td>
                                <td class="px-6 py-3 text-right font-semibold text-[var(--color-agro-green-dark)]">{{ __('9€/mes') }}</td>
                                <td class="px-6 py-3 text-right text-zinc-600">{{ __('85€/ano') }}</td>
                            </tr>
                            <tr class="bg-white">
                                <td class="px-6 py-3 text-zinc-700">{{ __('Viticultor independiente — Capa Completa') }}</td>
                                <td class="px-6 py-3 text-right font-semibold text-[var(--color-agro-green-dark)]">{{ __('14€/mes') }}</td>
                                <td class="px-6 py-3 text-right text-zinc-600">{{ __('130€/ano') }}</td>
                            </tr>
                            <tr class="bg-zinc-50/50">
                                <td class="px-6 py-3 text-zinc-700">{{ __('Productor (vinedo + bodega propios)') }}</td>
                                <td class="px-6 py-3 text-right font-semibold text-violet-700">{{ __('19€/mes') }}</td>
                                <td class="px-6 py-3 text-right text-zinc-600">{{ __('180€/ano') }}</td>
                            </tr>
                            <tr class="bg-white">
                                <td class="px-6 py-3 text-zinc-700">{{ __('Bodega dentro de una DO') }}</td>
                                <td class="px-6 py-3 text-right font-semibold text-zinc-800">{{ __('Gratis') }}</td>
                                <td class="px-6 py-3 text-right text-zinc-500">{{ __('Gratis') }}</td>
                            </tr>
                            <tr class="bg-zinc-50/50">
                                <td class="px-6 py-3 text-zinc-700">{{ __('Bodega independiente — solo su vino') }}</td>
                                <td class="px-6 py-3 text-right font-semibold text-red-700">{{ __('19€/mes') }}</td>
                                <td class="px-6 py-3 text-right text-zinc-600">{{ __('180€/ano') }}</td>
                            </tr>
                            <tr class="bg-white">
                                <td class="px-6 py-3 text-zinc-700">{{ __('Bodega independiente — red 1–10 viticultores') }}</td>
                                <td class="px-6 py-3 text-right font-semibold text-red-700">{{ __('29€/mes') }}</td>
                                <td class="px-6 py-3 text-right text-zinc-600">{{ __('290€/ano') }}</td>
                            </tr>
                            <tr class="bg-zinc-50/50">
                                <td class="px-6 py-3 text-zinc-700">{{ __('Bodega independiente — red 11–30 viticultores') }}</td>
                                <td class="px-6 py-3 text-right font-semibold text-red-700">{{ __('49€/mes') }}</td>
                                <td class="px-6 py-3 text-right text-zinc-600">{{ __('490€/ano') }}</td>
                            </tr>
                            <tr class="bg-white">
                                <td class="px-6 py-3 text-zinc-700">{{ __('Bodega independiente — red 31–60 viticultores') }}</td>
                                <td class="px-6 py-3 text-right font-semibold text-red-700">{{ __('79€/mes') }}</td>
                                <td class="px-6 py-3 text-right text-zinc-600">{{ __('790€/ano') }}</td>
                            </tr>
                            <tr class="bg-zinc-50/50">
                                <td class="px-6 py-3 text-zinc-700">{{ __('Bodega independiente — red 61–100 viticultores') }}</td>
                                <td class="px-6 py-3 text-right font-semibold text-red-700">{{ __('119€/mes') }}</td>
                                <td class="px-6 py-3 text-right text-zinc-600">{{ __('1.190€/ano') }}</td>
                            </tr>
                            <tr class="bg-white">
                                <td class="px-6 py-3 text-zinc-700">{{ __('Denominacion de Origen (hasta 25 bodegas)') }}</td>
                                <td class="px-6 py-3 text-right font-semibold text-amber-700">{{ __('149€/mes') }}</td>
                                <td class="px-6 py-3 text-right text-zinc-600">{{ __('1.400€/ano') }}</td>
                            </tr>
                            <tr class="bg-zinc-50/50">
                                <td class="px-6 py-3 text-zinc-700">{{ __('Denominacion de Origen (+100 bodegas)') }}</td>
                                <td class="px-6 py-3 text-right font-semibold text-amber-700">{{ __('A negociar') }}</td>
                                <td class="px-6 py-3 text-right text-zinc-500">{{ __('A negociar') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- FAQs de precios -->
        <section class="py-16 bg-zinc-50">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] text-center mb-10">{{ __('Preguntas frecuentes sobre precios') }}</h2>
                <div class="space-y-4">
                    @php
                    $faqs = [
                        [
                            '¿La operacion diaria es realmente gratis?',
                            'Si, para siempre. El cuaderno de campo digital, parcelas, SIGPAC, mapas, fenologia, depositos, vendimias, elaboracion de vino y la facturacion basica (sin sello Verifactu) son gratis para todos los perfiles. Solo pagas por modulos avanzados: teledeteccion satelital, Verifactu (sello AEAT), PAC, estadisticas financieras, o el panel de red de bodegas.'
                        ],
                        [
                            '¿Cuanto paga una bodega que gestiona viticultores proveedores?',
                            'El precio escala con el numero de viticultores que gestiona en la plataforma: 19€/mes (0 viticultores, solo su vino), 29€/mes (1-10), 49€/mes (11-30), 79€/mes (31-60), 119€/mes (61-100). Mas de 100 viticultores requiere un plan enterprise a negociar. Las bodegas dentro de una DO no pagan cuota individual.'
                        ],
                        [
                            '¿Que es el plan Productor y en que se diferencia del Viticultor?',
                            'El plan Productor es para quien cultiva su propio vinedo y elabora su propio vino en la misma explotacion. Une en una sola cuenta las funciones del viticultor (cuaderno de campo, SIGPAC, fenologia) y las de la bodega (depositos, vendimia, elaboracion, lotes). Cuesta 19€/mes o 180€/ano. Si en el futuro compra uva de terceros, puede activar el panel de red adicional.'
                        ],
                        [
                            '¿El viticultor paga si su bodega ya esta en Agro365?',
                            'La operacion diaria (cuaderno, parcelas, fenologia) es gratis siempre. Si el viticultor quiere modulos avanzados (teledeteccion, PAC, Verifactu, estadisticas financieras) paga 9€/mes si esta invitado por una bodega, o 14€/mes si es independiente. La bodega no asume ese coste — el viticultor decide si amplía su plan.'
                        ],
                        [
                            '¿Como se calcula el precio para una Denominacion de Origen?',
                            'El precio escala segun el numero de bodegas adscritas: hasta 25 bodegas son 149€/mes; 26-50 bodegas 249€/mes; 51-75 bodegas 349€/mes; 76-100 bodegas 449€/mes; mas de 100 bodegas se negocia. El paquete DO cubre el acceso de todas sus bodegas sin coste adicional para ellas.'
                        ],
                        [
                            '¿Hay descuento para cooperativas?',
                            'Las cooperativas con un gran numero de socios viticultores tienen condiciones equivalentes al plan Bodega con tramo de red. Contactanos y te preparamos una propuesta.'
                        ],
                        [
                            '¿Se puede cambiar de plan en cualquier momento?',
                            'Si. Las bodegas que incorporan mas viticultores suben automaticamente de tramo al siguiente ciclo de facturacion. Los cambios a un tramo inferior se aplican al siguiente periodo.'
                        ],
                        [
                            '¿Que pasa con mis datos si cancelo?',
                            'Tus datos son siempre tuyos. Puedes exportarlos en formato estandar en cualquier momento antes o despues de cancelar. Nunca retenemos informacion.'
                        ],
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
                <h2 class="text-3xl lg:text-4xl font-bold text-white mb-4">{{ __('La normativa no espera. Tu vinedo tampoco.') }}</h2>
                <p class="text-green-100 text-lg mb-8">{{ __('El cuaderno de campo digital es obligatorio desde 2027. Agro365 esta listo hoy — y la operacion basica es gratis para siempre.') }}</p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('register') }}" class="inline-flex items-center justify-center gap-2 px-8 py-4 rounded-xl bg-white text-[var(--color-agro-green-dark)] hover:bg-green-50 transition-all font-bold text-lg shadow-lg">
                        Empezar Gratis
                    </a>
                    <a href="https://wa.me/34684217167?text=Hola%2C%20soy%20una%20Denominaci%C3%B3n%20de%20Origen%20y%20me%20interesa%20Agro365" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center gap-2 px-8 py-4 rounded-xl border-2 border-white text-white hover:bg-white/10 transition-all font-semibold text-lg">
                        <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        Hablar por WhatsApp — DO
                    </a>
                </div>
            </div>
        </section>

    </main>

    @include('partials.footer-seo')
</body>
</html>
