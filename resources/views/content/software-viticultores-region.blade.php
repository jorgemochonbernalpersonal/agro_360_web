<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Software para Viticultores en {{ $region['name'] }} - Cuaderno de Campo Digital | Agro365</title>
    <meta name="description" content="{{ $region['description'] }}">
    <meta name="keywords" content="{{ $region['keywords'] }}">
    <meta name="robots" content="index, follow">
    <meta name="author" content="Agro365">

    <link rel="canonical" href="{{ url('/' . $slug) }}">

    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ url('/' . $slug) }}">
    <meta property="og:title" content="Software para Viticultores en {{ $region['name'] }} - Agro365">
    <meta property="og:description" content="{{ $region['description'] }}">
    <meta property="og:image" content="{{ asset('images/dashboard-preview.png') }}">
    <meta property="og:locale" content="es_ES">
    <meta property="og:site_name" content="Agro365">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Software Viticultores {{ $region['name'] }} - Agro365">
    <meta name="twitter:description" content="{{ $region['description'] }}">

    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "BreadcrumbList",
        "itemListElement": [
            {"@@type": "ListItem", "position": 1, "name": "Inicio", "item": "{{ url('/') }}"},
            {"@@type": "ListItem", "position": 2, "name": "Software Viticultores", "item": "{{ url('/software-para-viticultores') }}"},
            {"@@type": "ListItem", "position": 3, "name": "{{ $region['name'] }}", "item": "{{ url('/' . $slug) }}"}
        ]
    }
    </script>
</head>
<body class="font-sans antialiased bg-white">

    <!-- Header -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center gap-3">
                    <a href="{{ url('/') }}" class="flex items-center gap-3">
                        <img src="{{ asset('images/logo.png') }}" alt="Agro365" class="h-10 w-auto" loading="eager">
                        <span class="text-xl font-bold text-[var(--color-agro-green-dark)]">{{ __('Agro365') }}</span>
                    </a>
                </div>
                <div class="flex items-center gap-4">
                    <a href="{{ url('/') }}" class="text-gray-600 hover:text-[var(--color-agro-green)] transition-colors text-sm">Inicio</a>
                    <a href="{{ url('/precios') }}" class="text-gray-600 hover:text-[var(--color-agro-green)] transition-colors text-sm">Precios</a>
                    <a href="{{ route('faqs') }}" class="text-gray-600 hover:text-[var(--color-agro-green)] transition-colors text-sm">FAQs</a>
                    @guest
                        <a href="{{ route('login') }}" class="text-gray-600 hover:text-[var(--color-agro-green)] transition-colors text-sm">Entrar</a>
                        <a href="{{ route('register') }}" class="px-4 py-2 rounded-lg bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:shadow-lg transition-all text-sm font-semibold">
                            Comenzar Gratis
                        </a>
                    @endguest
                    @auth
                        <a href="{{ url('/dashboard') }}" class="text-gray-600 hover:text-[var(--color-agro-green)] transition-colors text-sm">Dashboard</a>
                    @endauth
                </div>
            </div>
        </nav>
    </header>

    <main class="min-h-screen">

        <!-- Hero -->
        <section class="py-16 bg-gradient-to-b from-[var(--color-agro-green-bg)] to-white">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <nav class="mb-8 text-sm text-zinc-500" itemscope itemtype="https://schema.org/BreadcrumbList">
                    <ol class="flex items-center flex-wrap gap-1">
                        <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                            <a href="{{ url('/') }}" class="hover:text-[var(--color-agro-green)]" itemprop="item"><span itemprop="name">{{ __('Inicio') }}</span></a>
                            <meta itemprop="position" content="1" />
                        </li>
                        <span class="mx-1">/</span>
                        <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                            <a href="{{ url('/software-para-viticultores') }}" class="hover:text-[var(--color-agro-green)]" itemprop="item"><span itemprop="name">{{ __('Software Viticultores') }}</span></a>
                            <meta itemprop="position" content="2" />
                        </li>
                        <span class="mx-1">/</span>
                        <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                            <span class="text-zinc-900 font-medium" itemprop="name">{{ $region['short'] }}</span>
                            <meta itemprop="position" content="3" />
                        </li>
                    </ol>
                </nav>

                @php
                $badgeColors = [
                    'red'    => 'bg-red-100 border-red-300 text-red-800',
                    'amber'  => 'bg-amber-100 border-amber-300 text-amber-800',
                    'yellow' => 'bg-yellow-100 border-yellow-300 text-yellow-800',
                    'green'  => 'bg-green-100 border-green-300 text-green-800',
                    'blue'   => 'bg-blue-100 border-blue-300 text-blue-800',
                    'purple' => 'bg-purple-100 border-purple-300 text-purple-800',
                    'orange' => 'bg-orange-100 border-orange-300 text-orange-800',
                ];
                $badgeClass = $badgeColors[$region['badge']] ?? $badgeColors['green'];
                @endphp

                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full border {{ $badgeClass }} text-sm font-semibold mb-6">
                    🍇 {{ $region['name'] }} — {{ $region['do_type'] }}
                </div>

                <h1 class="text-4xl lg:text-5xl font-bold text-[var(--color-agro-green-dark)] mb-6">
                    Software para Viticultores en {{ $region['short'] }}
                </h1>
                <p class="text-xl text-zinc-600 leading-relaxed">
                    Gestiona tus viñedos en la <strong>{{ $region['name'] }}</strong> con Agro365.
                    Cuaderno de campo digital, control PAC y cumplimiento del Consejo Regulador.
                    <strong>{{ $region['hectares'] }} hectáreas</strong> de viñedo en {{ $region['province'] }}.
                </p>
            </div>
        </section>

        <!-- Stats + Variedades -->
        <section class="py-12">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid md:grid-cols-2 gap-6 mb-12">

                    <!-- Datos de la DO -->
                    <div class="bg-white p-6 rounded-xl border border-zinc-200 shadow-sm">
                        <h2 class="font-bold text-xl text-[var(--color-agro-green-dark)] mb-4">📊 Datos de la {{ $region['do_type'] }}</h2>
                        <ul class="space-y-2 text-zinc-700 text-sm">
                            <li><strong>{{ __('Denominación:') }}</strong> {{ $region['name'] }}</li>
                            <li><strong>{{ __('Provincia / Comunidad:') }}</strong> {{ $region['province'] }}</li>
                            <li><strong>{{ __('Superficie:') }}</strong> {{ $region['hectares'] }} hectáreas</li>
                            <li><strong>{{ __('Viticultores:') }}</strong> {{ $region['viticultores'] }}</li>
                            <li><strong>{{ __('Bodegas:') }}</strong> {{ $region['bodegas'] }}</li>
                            @if($region['rendimiento'])
                            <li><strong>{{ __('Rendimiento máximo:') }}</strong> {{ $region['rendimiento'] }}</li>
                            @endif
                        </ul>
                    </div>

                    <!-- Variedades -->
                    <div class="bg-white p-6 rounded-xl border border-zinc-200 shadow-sm">
                        <h2 class="font-bold text-xl text-[var(--color-agro-green-dark)] mb-4">{{ __('🍇 Variedades Autorizadas') }}</h2>
                        <ul class="space-y-2">
                            @foreach($region['varieties'] as $variety)
                            <li class="flex items-center gap-2 text-sm text-zinc-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-[var(--color-agro-green)] flex-shrink-0"></span>
                                {{ $variety }}
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <!-- Clima y desafío principal -->
                <div class="mb-12 space-y-4">
                    <div class="bg-blue-50 border-l-4 border-blue-400 p-5 rounded-r-xl">
                        <p class="font-semibold text-blue-800 mb-1">{{ __('🌤 Clima') }}</p>
                        <p class="text-zinc-700 text-sm leading-relaxed">{{ $region['climate'] }}</p>
                    </div>
                    <div class="bg-amber-50 border-l-4 border-amber-400 p-5 rounded-r-xl">
                        <p class="font-semibold text-amber-800 mb-1">{{ __('⚠️ Desafío principal de la zona') }}</p>
                        <p class="text-zinc-700 text-sm leading-relaxed">{{ $region['challenge'] }}</p>
                    </div>
                </div>

                <!-- Cómo ayuda Agro365 -->
                <div class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-6">Cómo ayuda Agro365 en {{ $region['short'] }}</h2>
                    @php
                    $features = [
                        ['📱', 'Cuaderno de Campo Digital', 'Obligatorio desde 2027. Registra tratamientos, labores y vendimia por parcela SIGPAC. Cumple con el Consejo Regulador y la PAC desde el móvil, sin conexión.'],
                        ['🗺️', 'Gestión SIGPAC', 'Importa tus parcelas SIGPAC de ' . $region['province'] . '. Visualiza en mapa, calcula superficies y mantén actualizado el registro de la DO.'],
                        ['🍇', 'Control de Vendimia', 'Registra peso, grado y estado sanitario por parcela. Calcula rendimientos en tiempo real para no superar los límites del Consejo Regulador.'],
                        ['🌿', 'Fitosanitarios y ROPO', 'Base de datos de productos autorizados. Registro obligatorio de tratamientos con alertas de plazo de seguridad antes de vendimia.'],
                        ['📄', 'Informes Oficiales', 'Genera informes con firma digital para inspecciones PAC y el Consejo Regulador de ' . $region['short'] . '. Exporta en PDF oficial.'],
                        ['📊', 'Teledetección NDVI', 'Monitoriza el vigor de tus viñedos con imágenes satelitales. Detecta zonas de estrés hídrico o fitosanitario antes de que sean visibles a simple vista.'],
                    ];
                    @endphp
                    <div class="grid md:grid-cols-2 gap-5">
                        @foreach($features as [$icon, $title, $desc])
                        <div class="bg-white p-5 rounded-xl border border-zinc-200 shadow-sm">
                            <h3 class="font-bold text-[var(--color-agro-green-dark)] mb-2">{{ $icon }} {{ $title }}</h3>
                            <p class="text-zinc-600 text-sm leading-relaxed">{{ $desc }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Precio -->
                <div class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-2">Precios para Viticultores de {{ $region['short'] }}</h2>
                    <p class="text-zinc-500 mb-6 text-sm">{{ __('Sin permanencia. Sin tarjeta para empezar.') }}</p>
                    <div class="grid md:grid-cols-3 gap-4">

                        <!-- Básico gratis -->
                        <div class="bg-zinc-50 rounded-xl p-5 border border-zinc-200">
                            <p class="font-bold text-zinc-700 mb-1">{{ __('Básico') }}</p>
                            <p class="text-xs text-zinc-500 mb-3">{{ __('Si tu bodega ya está en Agro365 y te invita') }}</p>
                            <p class="text-2xl font-bold text-zinc-800 mb-2">{{ __('Gratis') }}</p>
                            <ul class="text-xs text-zinc-600 space-y-1">
                                <li>{{ __('✅ Cuaderno de campo básico') }}</li>
                                <li>{{ __('✅ SIGPAC y parcelas') }}</li>
                                <li>{{ __('✅ Plantaciones y cultivos') }}</li>
                            </ul>
                        </div>

                        <!-- Completo invitado -->
                        <div class="bg-[var(--color-agro-green-bg)] rounded-xl p-5 border border-[var(--color-agro-green-light)]/40">
                            <p class="font-bold text-[var(--color-agro-green-dark)] mb-1">{{ __('Completo (invitado)') }}</p>
                            <p class="text-xs text-zinc-500 mb-3">{{ __('Con bodega en Agro365, funciones avanzadas') }}</p>
                            <p class="text-2xl font-bold text-[var(--color-agro-green-dark)] mb-1">9€<span class="text-sm font-normal text-zinc-500">/mes</span></p>
                            <p class="text-xs text-zinc-500 mb-2">{{ __('o 85€/año') }}</p>
                            <ul class="text-xs text-zinc-600 space-y-1">
                                <li>{{ __('✅ Todo lo básico') }}</li>
                                <li>{{ __('✅ Teledetección NDVI') }}</li>
                                <li>{{ __('✅ PAC y normativa') }}</li>
                                <li>{{ __('✅ Facturación + Verifactu') }}</li>
                            </ul>
                        </div>

                        <!-- Independiente -->
                        <div class="bg-[var(--color-agro-green-bg)] rounded-xl p-5 border-2 border-[var(--color-agro-green)]">
                            <div class="inline-block px-2 py-0.5 bg-[var(--color-agro-green)] text-white rounded text-xs font-bold mb-2">INDEPENDIENTE</div>
                            <p class="font-bold text-[var(--color-agro-green-dark)] mb-1">{{ __('Sin bodega asociada') }}</p>
                            <p class="text-xs text-zinc-500 mb-3">{{ __('Acceso completo por tu cuenta') }}</p>
                            <p class="text-2xl font-bold text-[var(--color-agro-green-dark)] mb-1">14€<span class="text-sm font-normal text-zinc-500">/mes</span></p>
                            <p class="text-xs text-zinc-500 mb-2">{{ __('o 130€/año') }}</p>
                            <ul class="text-xs text-zinc-600 space-y-1">
                                <li>{{ __('✅ Todo incluido') }}</li>
                                <li>{{ __('✅ Vendimias y procesos') }}</li>
                                <li>{{ __('✅ App móvil sin conexión') }}</li>
                                <li>{{ __('✅ Soporte email 48h') }}</li>
                            </ul>
                        </div>

                    </div>
                    <p class="text-xs text-zinc-400 mt-4 text-center">
                        ¿Tu bodega es de {{ $region['short'] }} y aún no está en Agro365?
                        <a href="{{ url('/software-bodegas') }}" class="text-[var(--color-agro-green)] hover:underline">Ver plan para bodegas</a>
                    </p>
                </div>

            </div>
        </section>

        <!-- CTA -->
        <section class="py-16 bg-gradient-to-br from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)]">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-3xl lg:text-4xl font-bold text-white mb-4">
                    Gestiona tu viñedo en {{ $region['short'] }} con Agro365
                </h2>
                <p class="text-green-100 text-lg mb-8">{{ __('Cuaderno de campo digital, control PAC y cumplimiento del Consejo Regulador.
                    Básico obligatorio desde 2027 · Completo desde 2028.') }}</p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('register') }}" class="inline-flex items-center justify-center gap-2 px-8 py-4 rounded-xl bg-white text-[var(--color-agro-green-dark)] hover:bg-green-50 transition-all font-bold text-lg shadow-lg">
                        Comenzar Gratis
                    </a>
                    <a href="{{ route('faqs') }}" class="inline-flex items-center justify-center gap-2 px-8 py-4 rounded-xl border-2 border-white/70 text-white hover:bg-white/10 transition-all font-semibold">
                        Ver preguntas frecuentes
                    </a>
                </div>
                <p class="text-green-200/70 text-sm mt-6">{{ __('Sin tarjeta requerida · Configuración en 5 minutos · Soporte en español') }}</p>
            </div>
        </section>

        <!-- Otras regiones -->
        <section class="py-12 bg-zinc-50">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-xl font-bold text-zinc-700 mb-6">{{ __('Software para viticultores en otras denominaciones') }}</h2>
                @php
                $allRegions = [
                    ['DOCa Rioja', '/software-viticultores-rioja'],
                    ['Ribera del Duero', '/software-viticultores-ribera-duero'],
                    ['DO Rueda', '/software-viticultores-rueda'],
                    ['DO Penedès', '/software-viticultores-penedes'],
                    ['DO La Mancha', '/software-viticultores-la-mancha'],
                    ['DOQ Priorat', '/software-viticultores-priorat'],
                    ['Rías Baixas', '/software-viticultores-rias-baixas'],
                    ['DO Toro', '/software-viticultores-toro'],
                    ['DO Jumilla', '/software-viticultores-jumilla'],
                    ['DO Jerez', '/software-viticultores-jerez'],
                    ['DO Cava', '/software-viticultores-cava'],
                    ['DO Valdepeñas', '/software-viticultores-valdepenas'],
                    ['DO Navarra', '/software-viticultores-navarra'],
                    ['DO Somontano', '/software-viticultores-somontano'],
                    ['DO Utiel-Requena', '/software-viticultores-utiel-requena'],
                    ['DO Yecla', '/software-viticultores-yecla'],
                    ['DO Bullas', '/software-viticultores-bullas'],
                    ['DO Monterrei', '/software-viticultores-monterrei'],
                    ['DO Bierzo', '/software-viticultores-bierzo'],
                    ['DO Ribeiro', '/software-viticultores-ribeiro'],
                    ['DO Cigales', '/software-viticultores-cigales'],
                    ['DO Calatayud', '/software-viticultores-calatayud'],
                    ['DO Campo de Borja', '/software-viticultores-campo-de-borja'],
                    ['DO Cariñena', '/software-viticultores-carinena'],
                    ['DO Málaga', '/software-viticultores-malaga'],
                    ['DO Montilla-Moriles', '/software-viticultores-montilla-moriles'],
                    ['DO Terra Alta', '/software-viticultores-terra-alta'],
                    ['DO Costers del Segre', '/software-viticultores-costers-del-segre'],
                ];
                $currentPath = '/' . $slug;
                @endphp
                <div class="flex flex-wrap gap-2">
                    @foreach($allRegions as [$label, $url])
                    @if($url !== $currentPath)
                    <a href="{{ url($url) }}" class="px-3 py-1.5 rounded-lg bg-white border border-zinc-200 text-zinc-600 hover:border-[var(--color-agro-green)] hover:text-[var(--color-agro-green)] transition-all text-xs font-medium">
                        {{ $label }}
                    </a>
                    @endif
                    @endforeach
                </div>
            </div>
        </section>

    </main>

    @include('partials.footer-seo')

</body>
</html>
