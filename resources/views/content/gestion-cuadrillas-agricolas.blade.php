<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestión de Cuadrillas Agrícolas: Software para Viticultores | Agro365</title>
    <meta name="description" content="Software para gestión de cuadrillas agrícolas. Control de personal, equipos de trabajo, horas y costes por parcela. Optimiza tu mano de obra en viñedos.">
    <meta name="keywords" content="cuadrillas agrícolas, gestión personal agrícola, control cuadrillas viñedo, equipos agrícolas, mano de obra viñedo, gestión trabajadores campo, software cuadrillas, personal vendimia">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url('/gestion-cuadrillas-agricolas') }}">
    <meta property="og:title" content="Gestión de Cuadrillas Agrícolas - Agro365">
    <meta property="og:description" content="Control de personal y equipos de trabajo en viñedos.">
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
                <span class="text-gray-900">Gestión de Cuadrillas</span>
            </nav>

            <div class="mb-12">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-orange-100 border border-orange-300 mb-6">
                    <span class="text-lg">👥</span>
                    <span class="text-sm font-semibold text-orange-800">Personal Agrícola</span>
                </div>
                <h1 class="text-5xl lg:text-6xl font-bold text-[var(--color-agro-green-dark)] mb-6">
                    Gestión de Cuadrillas Agrícolas
                </h1>
                <p class="text-xl text-gray-600 leading-relaxed">
                    <strong>Organiza tus equipos de trabajo</strong> en el viñedo. Control de cuadrillas, asignación de tareas por parcela, seguimiento de horas y análisis de costes de mano de obra.
                </p>
            </div>

            <article class="prose prose-lg max-w-none">
                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">¿Por Qué Gestionar Cuadrillas Digitalmente?</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        La <strong>mano de obra</strong> es uno de los mayores costes en viticultura. Sin un control adecuado, es difícil saber cuánto cuesta realmente cada labor en cada parcela.
                    </p>
                    <ul class="list-disc list-inside space-y-2 text-gray-700 mb-6 ml-4">
                        <li><strong>Control de horas:</strong> Registra las horas trabajadas por cuadrilla y parcela</li>
                        <li><strong>Asignación de tareas:</strong> Asigna trabajos específicos a cada equipo</li>
                        <li><strong>Costes reales:</strong> Calcula el coste de mano de obra por hectárea y labor</li>
                        <li><strong>Rendimiento:</strong> Analiza la productividad de cada cuadrilla</li>
                    </ul>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">Funcionalidades</h2>
                    <div class="grid md:grid-cols-2 gap-6 mb-6">
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <div class="text-3xl mb-3">👷</div>
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">Gestión de Personal</h3>
                            <p class="text-gray-700">Registra trabajadores con datos de contacto, cualificaciones y coste por hora.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <div class="text-3xl mb-3">🏷️</div>
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">Cuadrillas</h3>
                            <p class="text-gray-700">Agrupa trabajadores en cuadrillas con un encargado asignado.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <div class="text-3xl mb-3">📋</div>
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">Asignación de Tareas</h3>
                            <p class="text-gray-700">Asigna cuadrillas a parcelas y tipos de trabajo (poda, vendimia, etc).</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <div class="text-3xl mb-3">📊</div>
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">Análisis de Costes</h3>
                            <p class="text-gray-700">Coste por parcela, por labor, por variedad. Compara campañas.</p>
                        </div>
                    </div>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">Integración con Cuaderno de Campo</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        Cada actividad registrada en el <a href="{{ route('content.cuaderno-digital-viticultores') }}" class="text-[var(--color-agro-green)] hover:underline">cuaderno de campo digital</a> se puede vincular a una cuadrilla:
                    </p>
                    <ul class="list-disc list-inside space-y-2 text-gray-700 mb-6 ml-4">
                        <li>Quién realizó la poda</li>
                        <li>Qué cuadrilla aplicó el <a href="{{ url('/registro-fitosanitarios') }}" class="text-[var(--color-agro-green)] hover:underline">tratamiento fitosanitario</a></li>
                        <li>Horas de trabajo en <a href="{{ url('/gestion-vendimia') }}" class="text-[var(--color-agro-green)] hover:underline">vendimia</a></li>
                    </ul>
                </section>

                <section class="mb-12">
                    <div class="bg-gradient-to-r from-[var(--color-agro-green-bg)] to-[var(--color-agro-green-light)]/30 p-8 rounded-xl border border-[var(--color-agro-green)]/20">
                        <h3 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mb-4">👥 Optimiza tu Mano de Obra</h3>
                        <p class="text-gray-700 mb-6">
                            Controla cuadrillas, analiza costes y mejora la productividad. <strong>6 meses gratis</strong>.
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
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">¿Puedo crear varias cuadrillas?</h3>
                            <p class="text-gray-700">Sí, puedes crear todas las cuadrillas que necesites. Cada una con su encargado, miembros y coste por hora diferente.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">¿Cómo se calculan los costes?</h3>
                            <p class="text-gray-700">Se multiplican las horas trabajadas por el coste/hora de cada trabajador. El sistema suma automáticamente el coste total por parcela y labor.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">¿Se puede asignar la misma cuadrilla a varias parcelas?</h3>
                            <p class="text-gray-700">Sí, una cuadrilla puede trabajar en múltiples parcelas. El sistema rastrea las horas por parcela de forma separada.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">¿Necesito registrar el carnet ROPO de los trabajadores?</h3>
                            <p class="text-gray-700">Sí, si realizan tratamientos fitosanitarios. El sistema valida que los aplicadores tengan ROPO válido.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">¿Puedo ver qué cuadrilla hizo cada actividad?</h3>
                            <p class="text-gray-700">Sí, cada actividad del cuaderno de campo queda vinculada a la cuadrilla que la realizó para trazabilidad completa.</p>
                        </div>
                    </div>
                </section>
            </article>
        </div>
    </div>
    @include('partials.footer-seo')

    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": [
            {"@type": "Question", "name": "¿Puedo crear varias cuadrillas?", "acceptedAnswer": {"@type": "Answer", "text": "Sí, puedes crear todas las cuadrillas que necesites. Cada una con su encargado, miembros y coste por hora diferente."}},
            {"@type": "Question", "name": "¿Cómo se calculan los costes?", "acceptedAnswer": {"@type": "Answer", "text": "Se multiplican las horas trabajadas por el coste/hora de cada trabajador. El sistema suma automáticamente el coste total por parcela y labor."}},
            {"@type": "Question", "name": "¿Se puede asignar la misma cuadrilla a varias parcelas?", "acceptedAnswer": {"@type": "Answer", "text": "Sí, una cuadrilla puede trabajar en múltiples parcelas. El sistema rastrea las horas por parcela de forma separada."}},
            {"@type": "Question", "name": "¿Necesito registrar el carnet ROPO de los trabajadores?", "acceptedAnswer": {"@type": "Answer", "text": "Sí, si realizan tratamientos fitosanitarios. El sistema valida que los aplicadores tengan ROPO válido."}},
            {"@type": "Question", "name": "¿Puedo ver qué cuadrilla hizo cada actividad?", "acceptedAnswer": {"@type": "Answer", "text": "Sí, cada actividad del cuaderno de campo queda vinculada a la cuadrilla que la realizó para trazabilidad completa."}}
        ]
    }
    </script>
</body>
</html>
