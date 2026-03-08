<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestión de Campañas Agrícolas: Control por Año | Agro365</title>
    <meta name="description" content="Gestión de campañas agrícolas por año: organiza actividades, cosechas, tratamientos y facturación por campaña. Compara rendimientos entre años.">
    <meta name="keywords" content="campaña agrícola, gestión campaña viñedo, año agrícola, organización campaña, control anual viñedo, comparativa campañas, histórico campañas, planificación campaña">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url('/gestion-campañas-agricolas') }}">
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
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-teal-100 border border-teal-300 mb-6">
                    <span class="text-lg">📆</span>
                    <span class="text-sm font-semibold text-teal-800">Organización Anual</span>
                </div>
                <h1 class="text-5xl lg:text-6xl font-bold text-[var(--color-agro-green-dark)] mb-6">
                    Gestión de Campañas Agrícolas
                </h1>
                <p class="text-xl text-gray-600 leading-relaxed">
                    <strong>Organiza tu trabajo por campañas</strong>: cada año agrícola con sus actividades, tratamientos, cosecha y facturación separados. Compara rendimientos y resultados entre campañas.
                </p>
            </div>

            <article class="prose prose-lg max-w-none">
                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">¿Qué es una Campaña Agrícola?</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        Una <strong>campaña agrícola</strong> en viticultura típicamente va desde la poda (enero-febrero) hasta la vendimia (septiembre-octubre) del mismo año. En Agro365, cada campaña agrupa:
                    </p>
                    <ul class="list-disc list-inside space-y-2 text-gray-700 mb-6 ml-4">
                        <li>Todas las actividades del <a href="{{ content_route('content.cuaderno-digital-viticultores') }}" class="text-[var(--color-agro-green)] hover:underline">cuaderno de campo</a></li>
                        <li><a href="{{ url('/registro-fitosanitarios') }}" class="text-[var(--color-agro-green)] hover:underline">Tratamientos fitosanitarios</a></li>
                        <li><a href="{{ url('/gestion-vendimia') }}" class="text-[var(--color-agro-green)] hover:underline">Vendimia</a> y contenedores</li>
                        <li><a href="{{ url('/facturacion-agricola') }}" class="text-[var(--color-agro-green)] hover:underline">Facturación</a> a clientes</li>
                        <li><a href="{{ url('/informes-oficiales-agricultura') }}" class="text-[var(--color-agro-green)] hover:underline">Informes oficiales</a></li>
                    </ul>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">Beneficios de la Gestión por Campañas</h2>
                    <div class="grid md:grid-cols-2 gap-6 mb-6">
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <div class="text-3xl mb-3">📊</div>
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">Comparativa Anual</h3>
                            <p class="text-gray-700">Compara <a href="{{ url('/rendimientos-cosecha-viñedo') }}" class="text-[var(--color-agro-green)] hover:underline">rendimientos</a>, costes y resultados entre campañas.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <div class="text-3xl mb-3">📋</div>
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">Informes por Campaña</h3>
                            <p class="text-gray-700">Genera informes de toda la campaña para <a href="{{ url('/subvenciones-pac-2024') }}" class="text-[var(--color-agro-green)] hover:underline">PAC</a> y DO.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <div class="text-3xl mb-3">📁</div>
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">Organización</h3>
                            <p class="text-gray-700">Mantén separados los datos de cada año sin mezclas.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <div class="text-3xl mb-3">📈</div>
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">Análisis Histórico</h3>
                            <p class="text-gray-700">Detecta tendencias a lo largo de los años.</p>
                        </div>
                    </div>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">Funcionalidades</h2>
                    <ul class="list-disc list-inside space-y-3 text-gray-700 mb-6 ml-4">
                        <li><strong>Crear campañas:</strong> Define fecha inicio y fin de cada campaña</li>
                        <li><strong>Campaña activa:</strong> Trabaja siempre en la campaña actual</li>
                        <li><strong>Cambiar entre campañas:</strong> Consulta datos de campañas anteriores</li>
                        <li><strong>Copiar datos:</strong> Copia parcelas y plantaciones a nueva campaña</li>
                        <li><strong>Cerrar campaña:</strong> Bloquea edición de campañas cerradas</li>
                    </ul>
                </section>

                <section class="mb-12">
                    <div class="bg-gradient-to-r from-[var(--color-agro-green-bg)] to-[var(--color-agro-green-light)]/30 p-8 rounded-xl border border-[var(--color-agro-green)]/20">
                        <h3 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mb-4">📆 Organiza por Campañas</h3>
                        <p class="text-gray-700 mb-6">
                            Separa cada año agrícola, compara resultados y genera informes de campaña. <strong>3 meses gratis</strong>.
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
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">¿Cuándo empieza una campaña agrícola?</h3>
                            <p class="text-gray-700">Típicamente con la poda en enero-febrero o cuando tú lo configures según tu explotación.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">¿Puedo tener varias campañas abiertas?</h3>
                            <p class="text-gray-700">Sí, aunque normalmente trabajas en una campaña activa, puedes consultar datos de campañas anteriores.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">¿Qué pasa cuando cierro una campaña?</h3>
                            <p class="text-gray-700">Se bloquea la edición de datos de esa campaña para preservar la integridad de los informes.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">¿Las parcelas se copian a la nueva campaña?</h3>
                            <p class="text-gray-700">Sí, al crear una nueva campaña puedes copiar automáticamente las parcelas y plantaciones.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">¿Puedo comparar rendimientos entre campañas?</h3>
                            <p class="text-gray-700">Sí, el sistema permite comparar datos de producción, costes y rendimientos año a año.</p>
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
            {"@@type": "Question", "name": "¿Cuándo empieza una campaña agrícola?", "acceptedAnswer": {"@@type": "Answer", "text": "Típicamente con la poda en enero-febrero o cuando tú lo configures según tu explotación."}},
            {"@@type": "Question", "name": "¿Puedo tener varias campañas abiertas?", "acceptedAnswer": {"@@type": "Answer", "text": "Sí, aunque normalmente trabajas en una campaña activa, puedes consultar datos de campañas anteriores."}},
            {"@@type": "Question", "name": "¿Qué pasa cuando cierro una campaña?", "acceptedAnswer": {"@@type": "Answer", "text": "Se bloquea la edición de datos de esa campaña para preservar la integridad de los informes."}},
            {"@@type": "Question", "name": "¿Las parcelas se copian a la nueva campaña?", "acceptedAnswer": {"@@type": "Answer", "text": "Sí, al crear una nueva campaña puedes copiar automáticamente las parcelas y plantaciones."}},
            {"@@type": "Question", "name": "¿Puedo comparar rendimientos entre campañas?", "acceptedAnswer": {"@@type": "Answer", "text": "Sí, el sistema permite comparar datos de producción, costes y rendimientos año a año."}}
        ]
    }
    </script>
</body>
</html>
