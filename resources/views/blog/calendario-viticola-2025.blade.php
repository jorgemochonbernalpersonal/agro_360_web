<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Calendario Vitícola 2025: Mes a Mes | Blog Agro365</title>
    <meta name="description" content="Calendario vitícola 2025 completo: todas las labores del viñedo organizadas por mes. Poda, tratamientos, floración, envero, vendimia y más.">
    <meta name="keywords" content="calendario viticola 2025, labores viñedo mes, poda viñedo cuando, tratamientos viña calendario, vendimia 2025, fechas viticultura">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url('/blog/calendario-viticola-2025') }}">
    <meta property="og:title" content="Calendario Vitícola 2025 - Blog Agro365">
    <meta property="og:type" content="article">
    <meta property="og:image" content="{{ asset('images/logo.png') }}">
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
                    <img src="{{ asset('images/logo.png') }}" alt="Agro365" class="h-10 w-auto">
                    <span class="text-xl font-bold text-[var(--color-agro-green-dark)]">Agro365</span>
                </a>
                @guest
                    <a href="{{ route('register') }}" class="px-4 py-2 rounded-lg bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white">Comenzar Gratis</a>
                @endguest
            </div>
        </nav>
    </header>

    <div class="min-h-screen bg-gradient-to-b from-white to-gray-50 py-20">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="text-sm text-gray-500 mb-8">
                <a href="{{ url('/blog') }}" class="hover:text-[var(--color-agro-green)]">Blog</a> → 
                <span>Calendario Vitícola 2025</span>
            </nav>

            <article class="prose prose-lg max-w-none">
                <div class="mb-8">
                    <span class="text-sm text-gray-500">Diciembre 2024</span>
                    <h1 class="text-4xl lg:text-5xl font-bold text-[var(--color-agro-green-dark)] mt-2">
                        Calendario Vitícola 2025: Mes a Mes
                    </h1>
                </div>

                <p class="text-xl text-gray-600 leading-relaxed mb-8">
                    Planifica tu campaña 2025 con este <a href="{{ url('/calendario-viticola') }}" class="text-[var(--color-agro-green)] hover:underline">calendario vitícola</a> completo. Fechas orientativas que pueden variar según zona y clima.
                </p>

                <div class="space-y-6">
                    <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-blue-400">
                        <h3 class="font-bold text-xl text-[var(--color-agro-green-dark)]">❄️ Enero - Febrero</h3>
                        <p class="text-gray-600 text-sm mb-2">Reposo invernal</p>
                        <ul class="text-gray-700 space-y-1">
                            <li>• <strong>Poda en seco</strong> - Principal labor del invierno</li>
                            <li>• Reparación de estructuras (espalderas, alambres)</li>
                            <li>• Análisis de suelo</li>
                            <li>• Planificación de la campaña</li>
                        </ul>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-green-400">
                        <h3 class="font-bold text-xl text-[var(--color-agro-green-dark)]">🌱 Marzo - Abril</h3>
                        <p class="text-gray-600 text-sm mb-2">Brotación</p>
                        <ul class="text-gray-700 space-y-1">
                            <li>• Lloro de la vid (savia en heridas de poda)</li>
                            <li>• <strong>Brotación</strong> - Inicio del ciclo vegetativo</li>
                            <li>• Primeros <a href="{{ url('/registro-fitosanitarios') }}" class="text-[var(--color-agro-green)] hover:underline">tratamientos preventivos</a> (mildiu, oídio)</li>
                            <li>• Laboreo del suelo</li>
                        </ul>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-pink-400">
                        <h3 class="font-bold text-xl text-[var(--color-agro-green-dark)]">🌸 Mayo - Junio</h3>
                        <p class="text-gray-600 text-sm mb-2">Floración y cuajado</p>
                        <ul class="text-gray-700 space-y-1">
                            <li>• <strong>Floración</strong> - Fase crítica para producción</li>
                            <li>• <strong>Cuajado</strong> - Formación de racimos</li>
                            <li>• Control de <a href="{{ url('/control-plagas-viñedo') }}" class="text-[var(--color-agro-green)] hover:underline">polilla del racimo</a> (1ª generación)</li>
                            <li>• Espergura y desniete (poda en verde)</li>
                            <li>• Primera estimación de <a href="{{ url('/rendimientos-cosecha-viñedo') }}" class="text-[var(--color-agro-green)] hover:underline">cosecha</a></li>
                        </ul>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-amber-400">
                        <h3 class="font-bold text-xl text-[var(--color-agro-green-dark)]">☀️ Julio - Agosto</h3>
                        <p class="text-gray-600 text-sm mb-2">Envero y maduración</p>
                        <ul class="text-gray-700 space-y-1">
                            <li>• <strong>Envero</strong> - Cambio de color de las bayas</li>
                            <li>• Despunte y deshojado</li>
                            <li>• Control de estrés hídrico (riegos si procede)</li>
                            <li>• Tratamientos contra botritis</li>
                            <li>• Monitorización con <a href="{{ url('/ndvi-viñedo-teledeteccion') }}" class="text-[var(--color-agro-green)] hover:underline">NDVI satelital</a></li>
                        </ul>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-purple-400">
                        <h3 class="font-bold text-xl text-[var(--color-agro-green-dark)]">🍇 Septiembre - Octubre</h3>
                        <p class="text-gray-600 text-sm mb-2">Vendimia</p>
                        <ul class="text-gray-700 space-y-1">
                            <li>• Control de maduración (Baumé, acidez, pH)</li>
                            <li>• <strong><a href="{{ url('/gestion-vendimia') }}" class="text-[var(--color-agro-green)] hover:underline">Vendimia</a></strong> - Manual o mecánica</li>
                            <li>• Gestión de contenedores y <a href="{{ url('/trazabilidad-vino-origen') }}" class="text-[var(--color-agro-green)] hover:underline">trazabilidad</a></li>
                            <li>• <a href="{{ url('/facturacion-agricola') }}" class="text-[var(--color-agro-green)] hover:underline">Facturación</a> a bodegas</li>
                        </ul>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-orange-400">
                        <h3 class="font-bold text-xl text-[var(--color-agro-green-dark)]">🍂 Noviembre - Diciembre</h3>
                        <p class="text-gray-600 text-sm mb-2">Post-vendimia</p>
                        <ul class="text-gray-700 space-y-1">
                            <li>• Caída de hoja</li>
                            <li>• Pre-poda (opcional)</li>
                            <li>• Abonado de fondo</li>
                            <li>• Análisis de <a href="{{ url('/rendimientos-cosecha-viñedo') }}" class="text-[var(--color-agro-green)] hover:underline">rendimientos</a> de la campaña</li>
                            <li>• <a href="{{ url('/informes-oficiales-agricultura') }}" class="text-[var(--color-agro-green)] hover:underline">Informes oficiales</a> de campaña</li>
                        </ul>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-[var(--color-agro-green-bg)] to-[var(--color-agro-green-light)]/30 p-8 rounded-xl border border-[var(--color-agro-green)]/20 mt-12">
                    <h3 class="text-xl font-bold text-[var(--color-agro-green-dark)] mb-4">📅 Organiza tu campaña 2025</h3>
                    <p class="text-gray-700 mb-6">
                        Calendario integrado con cuaderno de campo y recordatorios. <strong>6 meses gratis</strong>.
                    </p>
                    <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-lg bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:shadow-lg transition-all font-semibold">
                        Comenzar Gratis
                    </a>
                </div>
            </article>

            <div class="mt-12 pt-8 border-t border-gray-200">
                <a href="{{ url('/blog') }}" class="text-[var(--color-agro-green)] font-semibold hover:underline">← Volver al Blog</a>
            </div>
        </div>
    </div>
    @include('partials.footer-seo')
</body>
</html>
