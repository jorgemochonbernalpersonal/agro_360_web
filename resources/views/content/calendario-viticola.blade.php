<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Calendario Vitícola: Planificación de Trabajos en Viñedo | Agro365</title>
    <meta name="description" content="Calendario vitícola profesional: planificación de labores, tratamientos, poda, vendimia y todas las tareas del viñedo ordenadas por mes. Software de gestión.">
    <meta name="keywords" content="calendario vitícola, calendario viñedo, labores viñedo, planificación viticultura, trabajos viñedo mes, poda viñedo cuando, calendario tratamientos vid, tareas viñedo">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url('/calendario-viticola') }}">
    <meta property="og:title" content="Calendario Vitícola - Planificación de Trabajos">
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
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-12">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-blue-100 border border-blue-300 mb-6">
                    <span class="text-lg">📅</span>
                    <span class="text-sm font-semibold text-blue-800">Planificación Anual</span>
                </div>
                <h1 class="text-5xl lg:text-6xl font-bold text-[var(--color-agro-green-dark)] mb-6">
                    Calendario Vitícola: Labores por Mes
                </h1>
                <p class="text-xl text-gray-600 leading-relaxed">
                    <strong>Planifica todas las labores</strong> de tu viñedo a lo largo del año. Poda, tratamientos, labores culturales, vendimia y más. Organiza tu trabajo con el calendario integrado de Agro365.
                </p>
            </div>

            <article class="prose prose-lg max-w-none">
                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">Calendario Anual del Viñedo</h2>
                    <div class="space-y-4">
                        <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-blue-400">
                            <h3 class="font-bold text-[var(--color-agro-green-dark)]">❄️ Enero - Febrero: Reposo Invernal</h3>
                            <ul class="text-gray-700 text-sm mt-2 space-y-1">
                                <li>• <strong>Poda en seco</strong> de formación y producción</li>
                                <li>• Reparación y mantenimiento de estructuras</li>
                                <li>• Análisis de suelo</li>
                            </ul>
                        </div>
                        <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-green-400">
                            <h3 class="font-bold text-[var(--color-agro-green-dark)]">🌱 Marzo - Abril: Brotación</h3>
                            <ul class="text-gray-700 text-sm mt-2 space-y-1">
                                <li>• Lloro de la vid y brotación</li>
                                <li>• Primeros <a href="{{ url('/registro-fitosanitarios') }}" class="text-[var(--color-agro-green)] hover:underline">tratamientos preventivos</a> (mildiu, oídio)</li>
                                <li>• Laboreo del suelo</li>
                            </ul>
                        </div>
                        <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-pink-400">
                            <h3 class="font-bold text-[var(--color-agro-green-dark)]">🌸 Mayo - Junio: Floración y Cuajado</h3>
                            <ul class="text-gray-700 text-sm mt-2 space-y-1">
                                <li>• Floración y cuajado de racimos</li>
                                <li>• <a href="{{ url('/control-plagas-viñedo') }}" class="text-[var(--color-agro-green)] hover:underline">Control de polilla</a> 1ª generación</li>
                                <li>• Espergura y desniete</li>
                                <li>• Estimación de cosecha</li>
                            </ul>
                        </div>
                        <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-amber-400">
                            <h3 class="font-bold text-[var(--color-agro-green-dark)]">☀️ Julio - Agosto: Envero y Maduración</h3>
                            <ul class="text-gray-700 text-sm mt-2 space-y-1">
                                <li>• Envero (cambio de color)</li>
                                <li>• Despunte y deshojado</li>
                                <li>• Control de estrés hídrico</li>
                                <li>• Tratamientos contra botritis</li>
                            </ul>
                        </div>
                        <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-purple-400">
                            <h3 class="font-bold text-[var(--color-agro-green-dark)]">🍇 Septiembre - Octubre: Vendimia</h3>
                            <ul class="text-gray-700 text-sm mt-2 space-y-1">
                                <li>• Control de maduración (Baumé, acidez)</li>
                                <li>• <a href="{{ url('/gestion-vendimia') }}" class="text-[var(--color-agro-green)] hover:underline">Vendimia</a> manual o mecánica</li>
                                <li>• Gestión de contenedores</li>
                                <li>• <a href="{{ url('/facturacion-agricola') }}" class="text-[var(--color-agro-green)] hover:underline">Facturación</a> a bodegas</li>
                            </ul>
                        </div>
                        <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-orange-400">
                            <h3 class="font-bold text-[var(--color-agro-green-dark)]">🍂 Noviembre - Diciembre: Post-vendimia</h3>
                            <ul class="text-gray-700 text-sm mt-2 space-y-1">
                                <li>• Caída de hoja y entrada en reposo</li>
                                <li>• Pre-poda</li>
                                <li>• Abonado de fondo</li>
                                <li>• Planificación de próxima campaña</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">Calendario Digital en Agro365</h2>
                    <div class="grid md:grid-cols-2 gap-6 mb-6">
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">📆 Vista de Calendario</h3>
                            <p class="text-gray-700">Visualiza todas las actividades programadas en vista mensual, semanal o diaria.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">🔔 Recordatorios</h3>
                            <p class="text-gray-700">Alertas de tareas pendientes y tratamientos programados.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">📋 Vinculación al Cuaderno</h3>
                            <p class="text-gray-700">Cada tarea completada se registra automáticamente en el <a href="{{ content_route('content.cuaderno-digital-viticultores') }}" class="text-[var(--color-agro-green)] hover:underline">cuaderno de campo</a>.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">👥 Asignación a Cuadrillas</h3>
                            <p class="text-gray-700">Asigna tareas a <a href="{{ url('/gestion-cuadrillas-agricolas') }}" class="text-[var(--color-agro-green)] hover:underline">cuadrillas</a> específicas.</p>
                        </div>
                    </div>
                </section>

                <section class="mb-12">
                    <div class="bg-gradient-to-r from-[var(--color-agro-green-bg)] to-[var(--color-agro-green-light)]/30 p-8 rounded-xl border border-[var(--color-agro-green)]/20">
                        <h3 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mb-4">📅 Organiza tu Viñedo</h3>
                        <p class="text-gray-700 mb-6">
                            Calendario integrado con cuaderno de campo, tratamientos y gestión de personal. <strong>6 meses gratis</strong>.
                        </p>
                        <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-lg bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:shadow-lg transition-all font-semibold">
                            Comenzar Gratis
                        </a>
                    </div>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">Preguntas Frecuentes</h2>
                    <div class="space-y-6">
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">¿Cuándo debo iniciar la poda?</h3>
                            <p class="text-gray-700">La poda en seco se realiza durante el reposo invernal, generalmente entre enero y febrero.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">¿Cuándo aplicar tratamientos preventivos?</h3>
                            <p class="text-gray-700">Los primeros tratamientos preventivos contra mildiu y oídio se aplican tras la brotación en marzo-abril.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">¿Qué es el envero?</h3>
                            <p class="text-gray-700">Es el cambio de color de las uvas tintas (de verde a rojo/negro) que marca el inicio de la maduración, en julio-agosto.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">¿Puedo crear tareas recurrentes?</h3>
                            <p class="text-gray-700">Sí, el calendario permite programar tareas que se repiten cada año o cada ciertos días.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">¿Se sincroniza con mi móvil?</h3>
                            <p class="text-gray-700">La aplicación web es responsive y accesible desde cualquier dispositivo móvil.</p>
                        </div>
                    </div>
                </section>
            </article>
        </div>
    </div>
    @include('partials.footer-seo')

    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "FAQPage",
        "mainEntity": [
            {"@@type": "Question", "name": "¿Cuándo debo iniciar la poda?", "acceptedAnswer": {"@@type": "Answer", "text": "La poda en seco se realiza durante el reposo invernal, generalmente entre enero y febrero."}},
            {"@@type": "Question", "name": "¿Cuándo aplicar tratamientos preventivos?", "acceptedAnswer": {"@@type": "Answer", "text": "Los primeros tratamientos preventivos contra mildiu y oídio se aplican tras la brotación en marzo-abril."}},
            {"@@type": "Question", "name": "¿Qué es el envero?", "acceptedAnswer": {"@@type": "Answer", "text": "Es el cambio de color de las uvas tintas (de verde a rojo/negro) que marca el inicio de la maduración, en julio-agosto."}},
            {"@@type": "Question", "name": "¿Puedo crear tareas recurrentes?", "acceptedAnswer": {"@@type": "Answer", "text": "Sí, el calendario permite programar tareas que se repiten cada año o cada ciertos días."}},
            {"@@type": "Question", "name": "¿Se sincroniza con mi móvil?", "acceptedAnswer": {"@@type": "Answer", "text": "La aplicación web es responsive y accesible desde cualquier dispositivo móvil."}}
        ]
    }
    </script>
</body>
</html>
