<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Precios Agro365 | Viticultor desde gratis · Bodega 14€/mes · Denominación de Origen</title>
    <meta name="description" content="Precios claros para cada perfil. Viticultor básico gratis, completo 9€/mes (invitado) o 14€/mes (independiente). Bodega 14€/mes. Denominación de Origen desde 149€/mes. Sin sorpresas.">
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
                "name": "Viticultor invitado — Plan completo",
                "price": "9.00",
                "priceCurrency": "EUR",
                "description": "SIGPAC, teledetección, PAC, facturación, vendimias — viticultor invitado por bodega"
            },
            {
                "@@type": "Offer",
                "name": "Viticultor independiente — Mensual",
                "price": "14.00",
                "priceCurrency": "EUR",
                "description": "Plan completo para viticultor sin bodega asociada"
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
                "name": "Bodega independiente — Mensual",
                "price": "14.00",
                "priceCurrency": "EUR",
                "description": "Gestión completa de bodega sin Denominación de Origen"
            },
            {
                "@@type": "Offer",
                "name": "Bodega independiente — Anual",
                "price": "130.00",
                "priceCurrency": "EUR",
                "description": "Plan anual bodega independiente"
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
                    "text": "Depende. Si solo necesita el acceso básico (cuaderno de campo), accede gratis a través de su bodega. Si quiere funciones completas (SIGPAC, teledetección, PAC, facturación...) paga 9€/mes. Si es viticultor independiente sin bodega asociada, paga 14€/mes."
                }
            },
            {
                "@@type": "Question",
                "name": "¿Cuánto cuesta el plan Viticultor independiente?",
                "acceptedAnswer": {
                    "@@type": "Answer",
                    "text": "14€/mes o 130€/año. Es el plan para viticultores sin bodega asociada que quieren acceso completo a todas las funcionalidades."
                }
            },
            {
                "@@type": "Question",
                "name": "¿Qué precio tiene el plan para Bodegas?",
                "acceptedAnswer": {
                    "@@type": "Answer",
                    "text": "La bodega independiente (sin DO) paga 14€/mes o 130€/año. Las bodegas incluidas en una Denominación de Origen quedan cubiertas por el paquete de la DO y no pagan cuota individual."
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
                <p class="text-xl text-zinc-600 max-w-2xl mx-auto">{{ __('Desde gratis para el viticultor basico hasta planes escalados para Denominaciones de Origen. Sin sorpresas.') }}</p>
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
                                Mas popular
                            </div>
                        </div>
                        <div class="mb-6 pt-6">
                            <div class="text-4xl mb-2">🌿</div>
                            <h2 class="text-2xl font-bold text-[var(--color-agro-green-dark)]">{{ __('Viticultor') }}</h2>
                            <p class="text-zinc-500 text-sm mt-1">{{ __('Autoservicio · Sin llamadas · Cancela cuando quieras') }}</p>
                        </div>

                        <!-- Tier gratuito -->
                        <div class="mb-3 p-4 bg-zinc-50 rounded-xl border border-zinc-200">
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-semibold text-zinc-700 text-sm">{{ __('Basico (invitado por bodega)') }}</span>
                                <span class="text-lg font-bold text-zinc-800">{{ __('Gratis') }}</span>
                            </div>
                            <p class="text-xs text-zinc-500">{{ __('Cuaderno de campo basico. Acceso limitado a traves de tu bodega.') }}</p>
                        </div>

                        <!-- Tier invitado completo -->
                        <div class="mb-3 p-4 bg-[var(--color-agro-green-bg)] rounded-xl border border-[var(--color-agro-green-light)]/40">
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-semibold text-[var(--color-agro-green-dark)] text-sm">{{ __('Completo (invitado por bodega)') }}</span>
                                <div class="text-right">
                                    <span class="text-lg font-bold text-[var(--color-agro-green-dark)]">{{ __('9€/mes') }}</span>
                                </div>
                            </div>
                            <p class="text-xs text-zinc-600">o <strong>{{ __('85€/ano') }}</strong> — SIGPAC, teledeteccion, PAC, facturacion y mas.</p>
                        </div>

                        <!-- Tier independiente -->
                        <div class="mb-6 p-4 bg-[var(--color-agro-green-bg)] rounded-xl border-2 border-[var(--color-agro-green)]">
                            <div class="inline-block px-2 py-0.5 bg-[var(--color-agro-green)] text-white rounded font-bold text-xs mb-2">
                                INDEPENDIENTE
                            </div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-semibold text-[var(--color-agro-green-dark)] text-sm">{{ __('Sin bodega asociada') }}</span>
                                <div class="text-right">
                                    <span class="text-2xl font-bold text-[var(--color-agro-green-dark)]">{{ __('14€/mes') }}</span>
                                </div>
                            </div>
                            <p class="text-xs text-zinc-600">o <strong>{{ __('130€/ano') }}</strong> — acceso completo a todas las funcionalidades.</p>
                        </div>

                        <ul class="space-y-3 mb-8 text-sm">
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
                            <li class="flex items-start gap-2.5">
                                <svg class="w-5 h-5 text-[var(--color-agro-green)] flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-zinc-700">{{ $feature }}</span>
                            </li>
                            @endforeach
                        </ul>

                        <div class="mb-4 bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 text-center">
                            <p class="text-sm font-semibold text-amber-800">{{ __('🎁 3 meses gratis al registrarte') }}</p>
                            <p class="text-xs text-amber-600 mt-0.5">{{ __('Acceso completo sin tarjeta · Sin compromiso') }}</p>
                        </div>

                        <a href="{{ route('register') }}" class="block w-full text-center px-6 py-4 rounded-xl bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:from-[var(--color-agro-green)] hover:to-[var(--color-agro-green-dark)] transition-all duration-300 shadow-lg font-bold text-lg">
                            Comenzar Gratis
                        </a>
                        <p class="text-center text-xs text-zinc-400 mt-3">{{ __('Sin tarjeta requerida · Cancela cuando quieras') }}</p>
                    </div>

                    <!-- Bodega -->
                    <div class="rounded-2xl p-8 border-2 border-red-200 hover:border-red-400 transition-all duration-300 bg-white shadow-md">
                        <div class="mb-6">
                            <div class="text-4xl mb-2">🍷</div>
                            <h2 class="text-2xl font-bold text-[var(--color-agro-green-dark)]">{{ __('Bodega') }}</h2>
                            <p class="text-zinc-500 text-sm mt-1">{{ __('Demo gratuita · Onboarding incluido') }}</p>
                        </div>

                        <!-- Tier bodega en DO -->
                        <div class="mb-3 p-4 bg-zinc-50 rounded-xl border border-zinc-200">
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-semibold text-zinc-700 text-sm">{{ __('Dentro de una Denominacion de Origen') }}</span>
                                <span class="text-lg font-bold text-zinc-800">{{ __('Gratis') }}</span>
                            </div>
                            <p class="text-xs text-zinc-500">{{ __('El paquete DO cubre el acceso de todas sus bodegas adscritas.') }}</p>
                        </div>

                        <!-- Tier bodega independiente -->
                        <div class="mb-6 p-4 bg-red-50 rounded-xl border-2 border-red-400">
                            <div class="inline-block px-2 py-0.5 bg-red-500 text-white rounded font-bold text-xs mb-2">
                                INDEPENDIENTE
                            </div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-semibold text-red-700 text-sm">{{ __('Sin Denominacion de Origen') }}</span>
                                <div class="text-right">
                                    <span class="text-2xl font-bold text-red-700">{{ __('14€/mes') }}</span>
                                </div>
                            </div>
                            <p class="text-sm text-zinc-600">o <strong>{{ __('130€/ano') }}</strong> — gestion completa de bodega.</p>
                            <p class="text-xs text-red-600 font-semibold mt-1.5">{{ __('Onboarding personalizado incluido + migracion gratuita') }}</p>
                        </div>

                        <ul class="space-y-3 mb-8 text-sm">
                            @foreach([
                                'Panel de viticultores en tiempo real',
                                'Gestion completa de vendimia',
                                'Trazabilidad desde la cepa hasta la botella',
                                'Facturacion agricola integrada',
                                'Gestion de vinos y elaboracion',
                                'Comparativa rendimientos real vs estimado',
                                'Informes consolidados de bodega',
                                'Invitacion a viticultores (enlace simple)',
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

                        <div class="mb-4 bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 text-center">
                            <p class="text-sm font-semibold text-amber-800">{{ __('🎁 3 meses gratis al registrarte') }}</p>
                            <p class="text-xs text-amber-600 mt-0.5">{{ __('Demo gratuita · Onboarding incluido') }}</p>
                        </div>

                        <a href="{{ route('register') }}" class="block w-full text-center px-6 py-4 rounded-xl bg-gradient-to-r from-red-600 to-red-500 text-white hover:from-red-700 hover:to-red-600 transition-all duration-300 shadow-lg font-bold text-lg">
                            Comenzar Gratis
                        </a>
                        <p class="text-center text-xs text-zinc-400 mt-3">{{ __('Sin tarjeta requerida · Cancela cuando quieras') }}</p>
                    </div>

                    <!-- DO -->
                    <div class="rounded-2xl p-8 border-2 border-amber-200 hover:border-amber-400 transition-all duration-300 bg-white shadow-md">
                        <div class="mb-6">
                            <div class="text-4xl mb-2">🏛️</div>
                            <h2 class="text-2xl font-bold text-[var(--color-agro-green-dark)]">{{ __('Denominacion de Origen') }}</h2>
                            <p class="text-zinc-500 text-sm mt-1">{{ __('Solucion a medida · Contrato anual · Account manager dedicado') }}</p>
                        </div>

                        <!-- Tabla de escala DO -->
                        <div class="mb-6 rounded-xl border border-amber-200 overflow-hidden">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="bg-amber-50 border-b border-amber-200">
                                        <th class="text-left px-3 py-2 text-xs font-semibold text-amber-800">{{ __('Bodegas') }}</th>
                                        <th class="text-right px-3 py-2 text-xs font-semibold text-amber-800">{{ __('Mensual') }}</th>
                                        <th class="text-right px-3 py-2 text-xs font-semibold text-amber-800">{{ __('Anual') }}</th>
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
                                        <td class="px-3 py-2 text-zinc-700 text-xs">{{ $tier[0] }} bodegas</td>
                                        <td class="px-3 py-2 text-right font-semibold text-amber-800 text-xs">{{ $tier[1] }}</td>
                                        <td class="px-3 py-2 text-right text-zinc-600 text-xs">{{ $tier[2] }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <p class="text-xs text-zinc-500 mb-6 text-center">{{ __('Las bodegas incluidas en el paquete DO no pagan cuota individual.') }}</p>

                        <ul class="space-y-3 mb-8 text-sm">
                            @foreach([
                                'Acceso completo para todas las bodegas adscritas',
                                'Alta y gestion de bodegas',
                                'Panel de supervision centralizado',
                                'Vista de viticultores de todas las bodegas',
                                'Alertas automaticas de incumplimiento',
                                'Informes agregados por denominacion y campana',
                                'Trazabilidad denominacion completa',
                                'Firma electronica para validacion de informes',
                                'Integracion API con sistemas existentes',
                                'Account manager dedicado',
                                'SLA 99,9% uptime garantizado',
                                'Soporte telefonico directo',
                            ] as $feature)
                            <li class="flex items-start gap-2.5">
                                <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-zinc-700">{{ $feature }}</span>
                            </li>
                            @endforeach
                        </ul>

                        <a href="https://wa.me/34684217167?text=Hola%2C%20soy%20una%20Denominaci%C3%B3n%20de%20Origen%20y%20me%20interesa%20Agro365" target="_blank" rel="noopener noreferrer" class="flex items-center justify-center gap-2 w-full text-center px-6 py-4 rounded-xl bg-amber-500 hover:bg-amber-600 text-white transition-all duration-300 font-bold text-lg">
                            <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                            Hablar por WhatsApp
                        </a>
                        <a href="mailto:info@agro365.es?subject=Consulta%20DO%20Agro365" class="flex items-center justify-center gap-2 w-full text-center px-6 py-3 rounded-xl border border-amber-300 text-amber-800 hover:bg-amber-50 transition-all duration-300 font-medium text-sm mt-2">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            Enviar un email
                        </a>
                        <p class="text-center text-xs text-zinc-400 mt-3">{{ __('Propuesta sin compromiso para tu denominacion') }}</p>
                    </div>
                </div>

                <!-- Nota modelo viticultor invitado -->
                <div class="mt-10 max-w-3xl mx-auto text-center p-6 bg-[var(--color-agro-green-bg)] rounded-2xl border border-[var(--color-agro-green-light)]/30">
                    <p class="text-zinc-700 text-sm">
                        <strong>{{ __('Como funciona con viticultores invitados:') }}</strong> la bodega puede invitar a sus viticultores proveedores.
                        El viticultor accede en modo <strong>{{ __('basico gratis') }}</strong> (cuaderno de campo) o puede activar el <strong>{{ __('plan completo por 9€/mes') }}</strong>
                        (SIGPAC, teledeteccion, PAC, facturacion...). La bodega no paga por ello — el viticultor decide si amplia su plan.
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
                                <th class="text-left px-6 py-4 font-semibold text-zinc-600 w-1/3">{{ __('Funcionalidad') }}</th>
                                <th class="text-center px-3 py-4 font-bold text-zinc-500">🌿 Vit. Basico<br><span class="font-normal text-xs text-zinc-400">{{ __('Gratis') }}</span></th>
                                <th class="text-center px-3 py-4 font-bold text-[var(--color-agro-green-dark)]">🌿 Vit. Completo<br><span class="font-normal text-xs text-zinc-400">{{ __('9€/mes (invitado)') }}</span></th>
                                <th class="text-center px-3 py-4 font-bold text-[var(--color-agro-green-dark)]">🌿 Vit. Independiente<br><span class="font-normal text-xs text-zinc-400">{{ __('14€/mes') }}</span></th>
                                <th class="text-center px-3 py-4 font-bold text-red-700">🍷 Bodega<br><span class="font-normal text-xs text-zinc-400">{{ __('14€/mes') }}</span></th>
                                <th class="text-center px-3 py-4 font-bold text-amber-700">🏛️ DO<br><span class="font-normal text-xs text-zinc-400">{{ __('desde 149€/mes') }}</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                            @php
                            $rows = [
                                ['Cuaderno de campo basico', '✅', '✅', '✅', '✅', '—'],
                                ['SIGPAC y parcelas',        '✅', '✅', '✅', '✅', '—'],
                                ['Plantaciones y cultivos',  '✅', '✅', '✅', '✅', '—'],
                                ['Teledeteccion satelital',  '❌', '✅', '✅', '✅', '—'],
                                ['PAC y normativa',          '❌', '✅', '✅', '—',  '—'],
                                ['Facturacion + Verifactu',  '❌', '✅', '✅', '✅', '—'],
                                ['Vendimias y procesos',     '❌', '✅', '✅', '✅', '—'],
                                ['Gestion de vinos',         '❌', '❌', '❌', '✅', '—'],
                            ];
                            @endphp
                            @foreach($rows as $i => $row)
                            <tr class="{{ $i % 2 === 0 ? 'bg-white' : 'bg-zinc-50/50' }}">
                                <td class="px-6 py-3 font-medium text-zinc-700">{{ $row[0] }}</td>
                                <td class="px-3 py-3 text-center text-zinc-500 text-xs">{{ $row[1] }}</td>
                                <td class="px-3 py-3 text-center text-zinc-600 text-xs">{{ $row[2] }}</td>
                                <td class="px-3 py-3 text-center text-zinc-600 text-xs">{{ $row[3] }}</td>
                                <td class="px-3 py-3 text-center text-zinc-600 text-xs">{{ $row[4] }}</td>
                                <td class="px-3 py-3 text-center text-zinc-600 text-xs">{{ $row[5] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
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
                                <td class="px-6 py-3 text-zinc-700">{{ __('Viticultor invitado por bodega — uso basico') }}</td>
                                <td class="px-6 py-3 text-right font-semibold text-zinc-800">{{ __('Gratis') }}</td>
                                <td class="px-6 py-3 text-right text-zinc-500">{{ __('Gratis') }}</td>
                            </tr>
                            <tr class="bg-zinc-50/50">
                                <td class="px-6 py-3 text-zinc-700">{{ __('Viticultor invitado por bodega — plan completo') }}</td>
                                <td class="px-6 py-3 text-right font-semibold text-[var(--color-agro-green-dark)]">{{ __('9€/mes') }}</td>
                                <td class="px-6 py-3 text-right text-zinc-600">{{ __('85€/ano') }}</td>
                            </tr>
                            <tr class="bg-white">
                                <td class="px-6 py-3 text-zinc-700">{{ __('Viticultor independiente (sin bodega)') }}</td>
                                <td class="px-6 py-3 text-right font-semibold text-[var(--color-agro-green-dark)]">{{ __('14€/mes') }}</td>
                                <td class="px-6 py-3 text-right text-zinc-600">{{ __('130€/ano') }}</td>
                            </tr>
                            <tr class="bg-zinc-50/50">
                                <td class="px-6 py-3 text-zinc-700">{{ __('Bodega dentro de una Denominacion de Origen') }}</td>
                                <td class="px-6 py-3 text-right font-semibold text-zinc-800">{{ __('Gratis') }}</td>
                                <td class="px-6 py-3 text-right text-zinc-500">{{ __('Gratis') }}</td>
                            </tr>
                            <tr class="bg-white">
                                <td class="px-6 py-3 text-zinc-700">{{ __('Bodega independiente (sin DO)') }}</td>
                                <td class="px-6 py-3 text-right font-semibold text-red-700">{{ __('14€/mes') }}</td>
                                <td class="px-6 py-3 text-right text-zinc-600">{{ __('130€/ano') }}</td>
                            </tr>
                            <tr class="bg-zinc-50/50">
                                <td class="px-6 py-3 text-zinc-700">{{ __('Denominacion de Origen (hasta 25 bodegas)') }}</td>
                                <td class="px-6 py-3 text-right font-semibold text-amber-700">{{ __('149€/mes') }}</td>
                                <td class="px-6 py-3 text-right text-zinc-600">{{ __('1.400€/ano') }}</td>
                            </tr>
                            <tr class="bg-white">
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
                            '¿El viticultor paga si su bodega ya esta en Agro365?',
                            'Depende de lo que necesite. Si solo usa el cuaderno de campo basico, accede gratis. Si quiere funciones completas (SIGPAC, teledeteccion, PAC, facturacion, Verifactu...) paga 9€/mes. Solo si es viticultor independiente sin bodega asociada paga 14€/mes.'
                        ],
                        [
                            '¿Que diferencia hay entre el plan de 9€/mes y el de 14€/mes para viticultor?',
                            'El plan de 9€/mes es para viticultores invitados por una bodega que ya esta en Agro365. El de 14€/mes es para viticultores independientes sin bodega asociada. Las funcionalidades incluidas son practicamente las mismas.'
                        ],
                        [
                            '¿La bodega paga por cada viticultor que invita?',
                            'No. La bodega paga su cuota fija (14€/mes si es independiente, o queda cubierta por la DO). Los viticultores que invita acceden en modo basico gratis. Si un viticultor quiere el plan completo, paga el solo su 9€/mes — la bodega no asume ese coste.'
                        ],
                        [
                            '¿Como se calcula el precio para una Denominacion de Origen?',
                            'El precio escala segun el numero de bodegas adscritas: hasta 25 bodegas son 149€/mes; 26-50 bodegas 249€/mes; 51-75 bodegas 349€/mes; 76-100 bodegas 449€/mes; mas de 100 bodegas se negocia. El paquete DO cubre el acceso de todas sus bodegas sin coste adicional para ellas.'
                        ],
                        [
                            '¿Las bodegas dentro de una DO pagan algo aparte?',
                            'No. Las bodegas incluidas en el paquete DO no pagan cuota individual. El precio de la DO ya cubre el acceso completo de todas sus bodegas asociadas.'
                        ],
                        [
                            '¿Hay descuento para cooperativas?',
                            'Las cooperativas con un gran numero de socios viticultores tienen condiciones equivalentes al plan Bodega. Contactanos y te preparamos una propuesta.'
                        ],
                        [
                            '¿Se puede cambiar de plan en cualquier momento?',
                            'Si, siempre hacia arriba. Los cambios a plan inferior se aplican al siguiente periodo de facturacion.'
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
                <p class="text-green-100 text-lg mb-8">{{ __('El cuaderno de campo digital es obligatorio desde 2027. Agro365 esta listo hoy.') }}</p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('register') }}" class="inline-flex items-center justify-center gap-2 px-8 py-4 rounded-xl bg-white text-[var(--color-agro-green-dark)] hover:bg-green-50 transition-all font-bold text-lg shadow-lg">
                        Empezar como Viticultor — Gratis
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
