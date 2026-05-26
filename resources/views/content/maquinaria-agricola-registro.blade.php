<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registro de Maquinaria Agrícola: Control de Equipos | Agro365</title>
    <meta name="description" content="Software para registro y control de maquinaria agrícola. Gestiona tractores, atomizadores, cosechadoras y equipos. Mantenimiento, horas de uso y costes.">
    <meta name="keywords" content="maquinaria agrícola, registro maquinaria, control tractores, atomizador viñedo, equipos agrícolas, mantenimiento maquinaria, software maquinaria, gestión equipos, costes maquinaria">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url('/maquinaria-agricola-registro') }}">
    <meta property="og:title" content="Registro de Maquinaria Agrícola - Agro365">
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
                <a href="{{ url('/') }}" class="hover:text-[var(--color-agro-green)]">Inicio</a> / 
                <span class="text-gray-900">{{ __('Maquinaria Agrícola') }}</span>
            </nav>

            <div class="mb-12">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-gray-100 border border-gray-300 mb-6">
                    <span class="text-lg">🚜</span>
                    <span class="text-sm font-semibold text-gray-800">{{ __('Equipos y Maquinaria') }}</span>
                </div>
                <h1 class="text-5xl lg:text-6xl font-bold text-[var(--color-agro-green-dark)] mb-6">{{ __('Registro de Maquinaria Agrícola') }}</h1>
                <p class="text-xl text-gray-600 leading-relaxed">
                    <strong>{{ __('Control total de tu maquinaria') }}</strong>: tractores, atomizadores, cosechadoras. Registra horas de uso, mantenimiento preventivo y calcula costes por hectárea.
                </p>
            </div>

            <article class="prose prose-lg max-w-none">
                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">{{ __('¿Por Qué Registrar la Maquinaria?') }}</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        La maquinaria es una <strong>{{ __('inversión significativa') }}</strong>. Sin un control adecuado:
                    </p>
                    <ul class="list-disc list-inside space-y-2 text-gray-700 mb-6 ml-4">
                        <li>{{ __('No sabes el coste real de cada operación') }}</li>
                        <li>{{ __('Pierdes mantenimientos preventivos') }}</li>
                        <li>No puedes justificar uso en <a href="{{ url('/registro-fitosanitarios') }}" class="text-[var(--color-agro-green)] hover:underline">tratamientos fitosanitarios</a></li>
                        <li>{{ __('Dificultad en inspecciones PAC') }}</li>
                    </ul>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">{{ __('Tipos de Maquinaria') }}</h2>
                    <div class="grid md:grid-cols-3 gap-4 mb-6">
                        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 text-center">
                            <div class="text-4xl mb-2">🚜</div>
                            <h3 class="font-bold text-[var(--color-agro-green-dark)]">{{ __('Tractores') }}</h3>
                            <p class="text-sm text-gray-600">{{ __('Potencia, matrícula, fecha ITV') }}</p>
                        </div>
                        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 text-center">
                            <div class="text-4xl mb-2">💨</div>
                            <h3 class="font-bold text-[var(--color-agro-green-dark)]">{{ __('Atomizadores') }}</h3>
                            <p class="text-sm text-gray-600">{{ __('Capacidad, boquillas, calibración') }}</p>
                        </div>
                        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 text-center">
                            <div class="text-4xl mb-2">🍇</div>
                            <h3 class="font-bold text-[var(--color-agro-green-dark)]">{{ __('Vendimiadoras') }}</h3>
                            <p class="text-sm text-gray-600">{{ __('Modelo, horas motor, revisiones') }}</p>
                        </div>
                    </div>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">{{ __('Funcionalidades') }}</h2>
                    <div class="grid md:grid-cols-2 gap-6 mb-6">
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">{{ __('📝 Ficha de Equipo') }}</h3>
                            <p class="text-gray-700">{{ __('Datos técnicos, fotos, documentación (ITV, seguro, ROMA).') }}</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">{{ __('⏱️ Control de Horas') }}</h3>
                            <p class="text-gray-700">{{ __('Registra horas de uso por actividad y parcela.') }}</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">{{ __('🔧 Mantenimiento') }}</h3>
                            <p class="text-gray-700">{{ __('Alertas de mantenimiento preventivo (aceite, filtros, etc).') }}</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">{{ __('💰 Costes') }}</h3>
                            <p class="text-gray-700">{{ __('Calcula coste por hora y por hectárea trabajada.') }}</p>
                        </div>
                    </div>
                </section>

                <section class="mb-12">
                    <div class="bg-gradient-to-r from-[var(--color-agro-green-bg)] to-[var(--color-agro-green-light)]/30 p-8 rounded-xl border border-[var(--color-agro-green)]/20">
                        <h3 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mb-4">{{ __('🚜 Control Total de tu Maquinaria') }}</h3>
                        <p class="text-gray-700 mb-6">
                            Registra equipos, controla horas y optimiza costes. <strong>{{ __('3 meses gratis') }}</strong>.
                        </p>
                        <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-lg bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:shadow-lg transition-all font-semibold">
                            Comenzar Gratis
                        </a>
                    </div>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">{{ __('Preguntas Frecuentes') }}</h2>
                    <div class="space-y-6">
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">{{ __('¿Qué datos debo registrar de cada máquina?') }}</h3>
                            <p class="text-gray-700">{{ __('Matrícula, potencia, marca, modelo, fecha de compra, ITV, seguro y ROMA si aplica. También puedes subir fotos y documentos.') }}</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">{{ __('¿Cómo configuro las alertas de mantenimiento?') }}</h3>
                            <p class="text-gray-700">{{ __('Indica las horas de intervalo para cada tipo de mantenimiento (cambio aceite, filtros, etc). El sistema te avisará cuando se cumplan.') }}</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">{{ __('¿Puedo registrar maquinaria alquilada?') }}</h3>
                            <p class="text-gray-700">{{ __('Sí, puedes indicar si la maquinaria es propia, alquilada o de un servicio externo. Esto afecta al cálculo de costes.') }}</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">{{ __('¿Es necesario para inspecciones PAC?') }}</h3>
                            <p class="text-gray-700">{{ __('Sí, las inspecciones pueden requerir documentación de la maquinaria usada en tratamientos fitosanitarios como atomizadores.') }}</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">{{ __('¿Se calcula el coste por hectárea automáticamente?') }}</h3>
                            <p class="text-gray-700">{{ __('Sí, dividiendo el coste horario entre las hectáreas trabajadas según el consumo y horas registradas.') }}</p>
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
            {"@@type": "Question", "name": "¿Qué datos debo registrar de cada máquina?", "acceptedAnswer": {"@@type": "Answer", "text": "Matrícula, potencia, marca, modelo, fecha de compra, ITV, seguro y ROMA si aplica. También puedes subir fotos y documentos."}},
            {"@@type": "Question", "name": "¿Cómo configuro las alertas de mantenimiento?", "acceptedAnswer": {"@@type": "Answer", "text": "Indica las horas de intervalo para cada tipo de mantenimiento (cambio aceite, filtros, etc). El sistema te avisará cuando se cumplan."}},
            {"@@type": "Question", "name": "¿Puedo registrar maquinaria alquilada?", "acceptedAnswer": {"@@type": "Answer", "text": "Sí, puedes indicar si la maquinaria es propia, alquilada o de un servicio externo. Esto afecta al cálculo de costes."}},
            {"@@type": "Question", "name": "¿Es necesario para inspecciones PAC?", "acceptedAnswer": {"@@type": "Answer", "text": "Sí, las inspecciones pueden requerir documentación de la maquinaria usada en tratamientos fitosanitarios como atomizadores."}},
            {"@@type": "Question", "name": "¿Se calcula el coste por hectárea automáticamente?", "acceptedAnswer": {"@@type": "Answer", "text": "Sí, dividiendo el coste horario entre las hectáreas trabajadas según el consumo y horas registradas."}}
        ]
    }
    </script>
</body>
</html>
