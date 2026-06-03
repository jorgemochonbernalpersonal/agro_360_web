<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- SEO Meta Tags -->
    <title>Control de Plagas en Viñedo: Guía Completa 2024 | Agro365</title>
    <meta name="description" content="Guía completa de control de plagas en viñedo: mildiu, oídio, polilla del racimo, araña roja y más. Software de gestión de plagas para viticultores.">
    <meta name="keywords" content="plagas viñedo, control plagas vid, mildiu viña, oidio viña, polilla racimo, araña roja vid, tratamientos viñedo, enfermedades viña, botritis vid, gestión plagas viticultura, software plagas, cuaderno tratamientos">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <meta name="author" content="Agro365">
    <meta name="language" content="Spanish">
    <meta name="geo.region" content="ES">
    
    <link rel="canonical" href="{{ url('/control-plagas-vinedo') }}">
    
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ url('/control-plagas-vinedo') }}">
    <meta property="og:title" content="Control de Plagas en Viñedo - Guía Completa">
    <meta property="og:description" content="Identifica y controla las principales plagas y enfermedades del viñedo.">
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
                    <li class="text-gray-900">{{ __('Control de Plagas en Viñedo') }}</li>
                </ol>
            </nav>

            <div class="mb-12">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-red-100 border border-red-300 mb-6">
                    <span class="text-lg">🐛</span>
                    <span class="text-sm font-semibold text-red-800">{{ __('Gestión de Plagas') }}</span>
                </div>
                <h1 class="text-5xl lg:text-6xl font-bold text-[var(--color-agro-green-dark)] mb-6">{{ __('Control de Plagas en Viñedo: Guía Completa') }}</h1>
                <p class="text-xl text-gray-600 leading-relaxed">
                    Identifica y controla las <strong>{{ __('principales plagas y enfermedades del viñedo') }}</strong>: mildiu, oídio, polilla del racimo, araña roja y más. Con <strong>{{ __('registro digital de tratamientos') }}</strong> para cumplir con la normativa.
                </p>
            </div>

            <article class="prose prose-lg max-w-none">
                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">{{ __('Principales Plagas y Enfermedades del Viñedo') }}</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        El viñedo es susceptible a numerosas plagas y enfermedades que pueden afectar significativamente la producción. Un <strong>{{ __('control integrado de plagas') }}</strong> es esencial para mantener la sanidad del cultivo.
                    </p>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">{{ __('🦠 Enfermedades Fúngicas') }}</h2>
                    
                    <div class="space-y-6 mb-6">
                        <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-blue-500">
                            <h3 class="font-bold text-xl text-[var(--color-agro-green-dark)] mb-2">{{ __('Mildiu (Plasmopara viticola)') }}</h3>
                            <p class="text-gray-700 mb-3">{{ __('Una de las enfermedades más devastadoras del viñedo. Afecta hojas, brotes y racimos.') }}</p>
                            <div class="grid md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <strong class="text-gray-800">{{ __('Síntomas:') }}</strong>
                                    <ul class="text-gray-700 mt-1">
                                        <li>{{ __('• Manchas de aceite en hojas') }}</li>
                                        <li>{{ __('• Pelusilla blanca en envés') }}</li>
                                        <li>{{ __('• Racimos secos y marrones') }}</li>
                                    </ul>
                                </div>
                                <div>
                                    <strong class="text-gray-800">{{ __('Control:') }}</strong>
                                    <ul class="text-gray-700 mt-1">
                                        <li>{{ __('• Cobre (preventivo)') }}</li>
                                        <li>{{ __('• Fungicidas sistémicos') }}</li>
                                        <li>{{ __('• Tratamientos tras lluvia') }}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-yellow-500">
                            <h3 class="font-bold text-xl text-[var(--color-agro-green-dark)] mb-2">{{ __('Oídio (Erysiphe necator)') }}</h3>
                            <p class="text-gray-700 mb-3">{{ __('Enfermedad fúngica que prospera en climas secos y cálidos.') }}</p>
                            <div class="grid md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <strong class="text-gray-800">{{ __('Síntomas:') }}</strong>
                                    <ul class="text-gray-700 mt-1">
                                        <li>{{ __('• Polvo blanco-grisáceo') }}</li>
                                        <li>{{ __('• Hojas enrolladas') }}</li>
                                        <li>{{ __('• Granos agrietados') }}</li>
                                    </ul>
                                </div>
                                <div>
                                    <strong class="text-gray-800">{{ __('Control:') }}</strong>
                                    <ul class="text-gray-700 mt-1">
                                        <li>{{ __('• Azufre (preventivo)') }}</li>
                                        <li>{{ __('• Fungicidas IBE') }}</li>
                                        <li>{{ __('• Ventilación del follaje') }}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-gray-500">
                            <h3 class="font-bold text-xl text-[var(--color-agro-green-dark)] mb-2">{{ __('Botritis (Botrytis cinerea)') }}</h3>
                            <p class="text-gray-700 mb-3">{{ __('Podredumbre gris que afecta especialmente durante la maduración.') }}</p>
                            <div class="grid md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <strong class="text-gray-800">{{ __('Síntomas:') }}</strong>
                                    <ul class="text-gray-700 mt-1">
                                        <li>{{ __('• Moho gris en racimos') }}</li>
                                        <li>{{ __('• Granos podridos') }}</li>
                                        <li>{{ __('• Olor característico') }}</li>
                                    </ul>
                                </div>
                                <div>
                                    <strong class="text-gray-800">{{ __('Control:') }}</strong>
                                    <ul class="text-gray-700 mt-1">
                                        <li>{{ __('• Deshojado') }}</li>
                                        <li>{{ __('• Anti-botríticos') }}</li>
                                        <li>{{ __('• Aclareo de racimos') }}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">{{ __('🐛 Plagas de Insectos') }}</h2>
                    
                    <div class="space-y-6 mb-6">
                        <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-purple-500">
                            <h3 class="font-bold text-xl text-[var(--color-agro-green-dark)] mb-2">{{ __('Polilla del Racimo (Lobesia botrana)') }}</h3>
                            <p class="text-gray-700 mb-3">{{ __('Principal plaga del viñedo. Tres generaciones anuales afectan flores y racimos.') }}</p>
                            <div class="grid md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <strong class="text-gray-800">{{ __('Daños:') }}</strong>
                                    <ul class="text-gray-700 mt-1">
                                        <li>{{ __('• Destrucción de flores') }}</li>
                                        <li>{{ __('• Galerías en granos') }}</li>
                                        <li>{{ __('• Entrada de botritis') }}</li>
                                    </ul>
                                </div>
                                <div>
                                    <strong class="text-gray-800">{{ __('Control:') }}</strong>
                                    <ul class="text-gray-700 mt-1">
                                        <li>{{ __('• Confusión sexual') }}</li>
                                        <li>{{ __('• Bacillus thuringiensis') }}</li>
                                        <li>{{ __('• Insecticidas IGR') }}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-red-500">
                            <h3 class="font-bold text-xl text-[var(--color-agro-green-dark)] mb-2">{{ __('Araña Roja (Tetranychus urticae)') }}</h3>
                            <p class="text-gray-700 mb-3">{{ __('Ácaro que prolifera en condiciones de calor y sequía.') }}</p>
                            <div class="grid md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <strong class="text-gray-800">{{ __('Daños:') }}</strong>
                                    <ul class="text-gray-700 mt-1">
                                        <li>{{ __('• Hojas bronceadas') }}</li>
                                        <li>{{ __('• Reducción fotosíntesis') }}</li>
                                        <li>{{ __('• Defoliación prematura') }}</li>
                                    </ul>
                                </div>
                                <div>
                                    <strong class="text-gray-800">{{ __('Control:') }}</strong>
                                    <ul class="text-gray-700 mt-1">
                                        <li>{{ __('• Acaricidas específicos') }}</li>
                                        <li>{{ __('• Control biológico') }}</li>
                                        <li>{{ __('• Azufre mojable') }}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">{{ __('📅 Calendario de Tratamientos') }}</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">{{ __('El momento de aplicación es crucial para la eficacia del tratamiento:') }}</p>
                    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 mb-6">
                        <div class="space-y-3">
                            <div class="flex items-center gap-4 pb-3 border-b">
                                <div class="w-28 text-center bg-green-100 text-green-800 font-bold py-2 px-3 rounded text-sm">Brotación</div>
                                <div class="text-gray-700 text-sm">Primer tratamiento preventivo mildiu/oídio</div>
                            </div>
                            <div class="flex items-center gap-4 pb-3 border-b">
                                <div class="w-28 text-center bg-pink-100 text-pink-800 font-bold py-2 px-3 rounded text-sm">Floración</div>
                                <div class="text-gray-700 text-sm">Control polilla 1ª generación + botritis</div>
                            </div>
                            <div class="flex items-center gap-4 pb-3 border-b">
                                <div class="w-28 text-center bg-amber-100 text-amber-800 font-bold py-2 px-3 rounded text-sm">Cuajado</div>
                                <div class="text-gray-700 text-sm">Refuerzo mildiu/oídio tras lluvias</div>
                            </div>
                            <div class="flex items-center gap-4 pb-3 border-b">
                                <div class="w-28 text-center bg-purple-100 text-purple-800 font-bold py-2 px-3 rounded text-sm">Envero</div>
                                <div class="text-gray-700 text-sm">Control polilla 2ª-3ª generación + botritis</div>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="w-28 text-center bg-red-100 text-red-800 font-bold py-2 px-3 rounded text-sm">Pre-vendimia</div>
                                <div class="text-gray-700 text-sm">Respetar plazos seguridad (15-21 días)</div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">{{ __('Gestión Digital de Plagas con Agro365') }}</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        Agro365 incluye un <strong>{{ __('módulo completo de gestión de plagas') }}</strong>:
                    </p>
                    <div class="grid md:grid-cols-2 gap-6 mb-6">
                        <div class="bg-green-50 p-6 rounded-lg border border-green-200">
                            <div class="text-3xl mb-3">📋</div>
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">{{ __('Catálogo de Plagas') }}</h3>
                            <p class="text-gray-700">{{ __('Base de datos de plagas y enfermedades con síntomas, fotos y tratamientos recomendados.') }}</p>
                        </div>
                        <div class="bg-green-50 p-6 rounded-lg border border-green-200">
                            <div class="text-3xl mb-3">📝</div>
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">{{ __('Registro de Observaciones') }}</h3>
                            <p class="text-gray-700">{{ __('Registra observaciones de plagas por parcela con fecha, severidad y fotos.') }}</p>
                        </div>
                        <div class="bg-green-50 p-6 rounded-lg border border-green-200">
                            <div class="text-3xl mb-3">🧪</div>
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">{{ __('Vinculación Tratamientos') }}</h3>
                            <p class="text-gray-700">Vincula cada <a href="{{ url('/registro-fitosanitarios') }}" class="text-[var(--color-agro-green)] hover:underline">tratamiento fitosanitario</a> a la plaga objetivo.</p>
                        </div>
                        <div class="bg-green-50 p-6 rounded-lg border border-green-200">
                            <div class="text-3xl mb-3">📊</div>
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">{{ __('Histórico y Análisis') }}</h3>
                            <p class="text-gray-700">{{ __('Analiza qué plagas afectan más a cada parcela y la eficacia de los tratamientos.') }}</p>
                        </div>
                    </div>

                    <div class="bg-gradient-to-r from-[var(--color-agro-green-bg)] to-[var(--color-agro-green-light)]/30 p-8 rounded-xl border border-[var(--color-agro-green)]/20">
                        <h3 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mb-4">{{ __('🛡️ Control de Plagas Digitalizado') }}</h3>
                        <p class="text-gray-700 mb-6">
                            Gestiona plagas, tratamientos y cumple con la normativa de <a href="{{ url('/registro-fitosanitarios') }}" class="text-[var(--color-agro-green)] hover:underline">registro de fitosanitarios</a>. <strong>{{ __('3 meses gratis') }}</strong>.
                        </p>
                        <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-lg bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:shadow-lg transition-all font-semibold">
                            Comenzar Gratis
                        </a>
                    </div>
                </section>
            </article>

            <div class="mt-16 pt-12 border-t border-gray-200 text-center">
                <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">{{ __('Protege tu Viñedo con Agro365') }}</h2>
                <p class="text-gray-600 mb-8 text-lg">{{ __('Gestión integral de plagas con registro de tratamientos y cumplimiento normativo.') }}</p>
                <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-8 py-4 rounded-xl bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white shadow-lg font-semibold text-lg">
                    Comenzar Gratis
                </a>
            </div>
        </div>
    </div>

    @include('partials.footer-seo')

    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "Article",
        "headline": "Control de Plagas en Viñedo: Guía Completa 2024",
        "description": "Guía completa de control de plagas en viñedo: mildiu, oídio, polilla del racimo, araña roja.",
        "author": {"@@type": "Organization", "name": "Agro365"},
        "publisher": {"@@type": "Organization", "name": "Agro365"},
        "datePublished": "2024-01-01",
        "dateModified": "{{ now()->toIso8601String() }}"
    }
    </script>
</body>
</html>
