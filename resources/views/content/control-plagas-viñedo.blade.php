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
    
    <link rel="canonical" href="{{ url('/control-plagas-viñedo') }}">
    
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ url('/control-plagas-viñedo') }}">
    <meta property="og:title" content="Control de Plagas en Viñedo - Guía Completa">
    <meta property="og:description" content="Identifica y controla las principales plagas y enfermedades del viñedo.">
    <meta property="og:image" content="{{ asset('images/logo.png') }}">
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
                    <span class="text-xl font-bold text-[var(--color-agro-green-dark)]">Agro365</span>
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
                    <li class="text-gray-900">Control de Plagas en Viñedo</li>
                </ol>
            </nav>

            <div class="mb-12">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-red-100 border border-red-300 mb-6">
                    <span class="text-lg">🐛</span>
                    <span class="text-sm font-semibold text-red-800">Gestión de Plagas</span>
                </div>
                <h1 class="text-5xl lg:text-6xl font-bold text-[var(--color-agro-green-dark)] mb-6">
                    Control de Plagas en Viñedo: Guía Completa
                </h1>
                <p class="text-xl text-gray-600 leading-relaxed">
                    Identifica y controla las <strong>principales plagas y enfermedades del viñedo</strong>: mildiu, oídio, polilla del racimo, araña roja y más. Con <strong>registro digital de tratamientos</strong> para cumplir con la normativa.
                </p>
            </div>

            <article class="prose prose-lg max-w-none">
                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">Principales Plagas y Enfermedades del Viñedo</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        El viñedo es susceptible a numerosas plagas y enfermedades que pueden afectar significativamente la producción. Un <strong>control integrado de plagas</strong> es esencial para mantener la sanidad del cultivo.
                    </p>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">🦠 Enfermedades Fúngicas</h2>
                    
                    <div class="space-y-6 mb-6">
                        <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-blue-500">
                            <h3 class="font-bold text-xl text-[var(--color-agro-green-dark)] mb-2">Mildiu (Plasmopara viticola)</h3>
                            <p class="text-gray-700 mb-3">Una de las enfermedades más devastadoras del viñedo. Afecta hojas, brotes y racimos.</p>
                            <div class="grid md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <strong class="text-gray-800">Síntomas:</strong>
                                    <ul class="text-gray-700 mt-1">
                                        <li>• Manchas de aceite en hojas</li>
                                        <li>• Pelusilla blanca en envés</li>
                                        <li>• Racimos secos y marrones</li>
                                    </ul>
                                </div>
                                <div>
                                    <strong class="text-gray-800">Control:</strong>
                                    <ul class="text-gray-700 mt-1">
                                        <li>• Cobre (preventivo)</li>
                                        <li>• Fungicidas sistémicos</li>
                                        <li>• Tratamientos tras lluvia</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-yellow-500">
                            <h3 class="font-bold text-xl text-[var(--color-agro-green-dark)] mb-2">Oídio (Erysiphe necator)</h3>
                            <p class="text-gray-700 mb-3">Enfermedad fúngica que prospera en climas secos y cálidos.</p>
                            <div class="grid md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <strong class="text-gray-800">Síntomas:</strong>
                                    <ul class="text-gray-700 mt-1">
                                        <li>• Polvo blanco-grisáceo</li>
                                        <li>• Hojas enrolladas</li>
                                        <li>• Granos agrietados</li>
                                    </ul>
                                </div>
                                <div>
                                    <strong class="text-gray-800">Control:</strong>
                                    <ul class="text-gray-700 mt-1">
                                        <li>• Azufre (preventivo)</li>
                                        <li>• Fungicidas IBE</li>
                                        <li>• Ventilación del follaje</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-gray-500">
                            <h3 class="font-bold text-xl text-[var(--color-agro-green-dark)] mb-2">Botritis (Botrytis cinerea)</h3>
                            <p class="text-gray-700 mb-3">Podredumbre gris que afecta especialmente durante la maduración.</p>
                            <div class="grid md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <strong class="text-gray-800">Síntomas:</strong>
                                    <ul class="text-gray-700 mt-1">
                                        <li>• Moho gris en racimos</li>
                                        <li>• Granos podridos</li>
                                        <li>• Olor característico</li>
                                    </ul>
                                </div>
                                <div>
                                    <strong class="text-gray-800">Control:</strong>
                                    <ul class="text-gray-700 mt-1">
                                        <li>• Deshojado</li>
                                        <li>• Anti-botríticos</li>
                                        <li>• Aclareo de racimos</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">🐛 Plagas de Insectos</h2>
                    
                    <div class="space-y-6 mb-6">
                        <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-purple-500">
                            <h3 class="font-bold text-xl text-[var(--color-agro-green-dark)] mb-2">Polilla del Racimo (Lobesia botrana)</h3>
                            <p class="text-gray-700 mb-3">Principal plaga del viñedo. Tres generaciones anuales afectan flores y racimos.</p>
                            <div class="grid md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <strong class="text-gray-800">Daños:</strong>
                                    <ul class="text-gray-700 mt-1">
                                        <li>• Destrucción de flores</li>
                                        <li>• Galerías en granos</li>
                                        <li>• Entrada de botritis</li>
                                    </ul>
                                </div>
                                <div>
                                    <strong class="text-gray-800">Control:</strong>
                                    <ul class="text-gray-700 mt-1">
                                        <li>• Confusión sexual</li>
                                        <li>• Bacillus thuringiensis</li>
                                        <li>• Insecticidas IGR</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-red-500">
                            <h3 class="font-bold text-xl text-[var(--color-agro-green-dark)] mb-2">Araña Roja (Tetranychus urticae)</h3>
                            <p class="text-gray-700 mb-3">Ácaro que prolifera en condiciones de calor y sequía.</p>
                            <div class="grid md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <strong class="text-gray-800">Daños:</strong>
                                    <ul class="text-gray-700 mt-1">
                                        <li>• Hojas bronceadas</li>
                                        <li>• Reducción fotosíntesis</li>
                                        <li>• Defoliación prematura</li>
                                    </ul>
                                </div>
                                <div>
                                    <strong class="text-gray-800">Control:</strong>
                                    <ul class="text-gray-700 mt-1">
                                        <li>• Acaricidas específicos</li>
                                        <li>• Control biológico</li>
                                        <li>• Azufre mojable</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">📅 Calendario de Tratamientos</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        El momento de aplicación es crucial para la eficacia del tratamiento:
                    </p>
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
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">Gestión Digital de Plagas con Agro365</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        Agro365 incluye un <strong>módulo completo de gestión de plagas</strong>:
                    </p>
                    <div class="grid md:grid-cols-2 gap-6 mb-6">
                        <div class="bg-green-50 p-6 rounded-lg border border-green-200">
                            <div class="text-3xl mb-3">📋</div>
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">Catálogo de Plagas</h3>
                            <p class="text-gray-700">Base de datos de plagas y enfermedades con síntomas, fotos y tratamientos recomendados.</p>
                        </div>
                        <div class="bg-green-50 p-6 rounded-lg border border-green-200">
                            <div class="text-3xl mb-3">📝</div>
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">Registro de Observaciones</h3>
                            <p class="text-gray-700">Registra observaciones de plagas por parcela con fecha, severidad y fotos.</p>
                        </div>
                        <div class="bg-green-50 p-6 rounded-lg border border-green-200">
                            <div class="text-3xl mb-3">🧪</div>
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">Vinculación Tratamientos</h3>
                            <p class="text-gray-700">Vincula cada <a href="{{ url('/registro-fitosanitarios') }}" class="text-[var(--color-agro-green)] hover:underline">tratamiento fitosanitario</a> a la plaga objetivo.</p>
                        </div>
                        <div class="bg-green-50 p-6 rounded-lg border border-green-200">
                            <div class="text-3xl mb-3">📊</div>
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">Histórico y Análisis</h3>
                            <p class="text-gray-700">Analiza qué plagas afectan más a cada parcela y la eficacia de los tratamientos.</p>
                        </div>
                    </div>

                    <div class="bg-gradient-to-r from-[var(--color-agro-green-bg)] to-[var(--color-agro-green-light)]/30 p-8 rounded-xl border border-[var(--color-agro-green)]/20">
                        <h3 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mb-4">🛡️ Control de Plagas Digitalizado</h3>
                        <p class="text-gray-700 mb-6">
                            Gestiona plagas, tratamientos y cumple con la normativa de <a href="{{ url('/registro-fitosanitarios') }}" class="text-[var(--color-agro-green)] hover:underline">registro de fitosanitarios</a>. <strong>3 meses gratis</strong>.
                        </p>
                        <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-lg bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:shadow-lg transition-all font-semibold">
                            Comenzar Gratis
                        </a>
                    </div>
                </section>
            </article>

            <div class="mt-16 pt-12 border-t border-gray-200 text-center">
                <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">Protege tu Viñedo con Agro365</h2>
                <p class="text-gray-600 mb-8 text-lg">Gestión integral de plagas con registro de tratamientos y cumplimiento normativo.</p>
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
