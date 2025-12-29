<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Trazabilidad del Vino: Del Viñedo a la Bodega | Agro365</title>
    <meta name="description" content="Sistema de trazabilidad completa del vino: desde la parcela SIGPAC hasta la factura. Cumple con normativa de trazabilidad alimentaria y Denominación de Origen.">
    <meta name="keywords" content="trazabilidad vino, trazabilidad uva, origen vino, trazabilidad alimentaria, DO vino, certificación origen, vinculación parcela bodega, trazabilidad vendimia, control origen uva">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url('/trazabilidad-vino-origen') }}">
    <meta property="og:title" content="Trazabilidad del Vino - Del Viñedo a la Bodega">
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
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-red-100 border border-red-300 mb-6">
                    <span class="text-lg">🔗</span>
                    <span class="text-sm font-semibold text-red-800">Trazabilidad Completa</span>
                </div>
                <h1 class="text-5xl lg:text-6xl font-bold text-[var(--color-agro-green-dark)] mb-6">
                    Trazabilidad del Vino: Del Viñedo a la Bodega
                </h1>
                <p class="text-xl text-gray-600 leading-relaxed">
                    <strong>Trazabilidad total</strong> de tu uva: desde el recinto <a href="{{ route('content.sigpac') }}" class="text-[var(--color-agro-green)] hover:underline">SIGPAC</a> donde se cultiva hasta la factura que entregas a la bodega. Cumple con la normativa de seguridad alimentaria y certificaciones DO.
                </p>
            </div>

            <article class="prose prose-lg max-w-none">
                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">Cadena de Trazabilidad</h2>
                    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 mb-6">
                        <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                            <div class="text-center">
                                <div class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-2">
                                    <span class="text-2xl">🗺️</span>
                                </div>
                                <div class="text-sm font-bold text-gray-800">Parcela SIGPAC</div>
                                <div class="text-xs text-gray-600">Recinto identificado</div>
                            </div>
                            <div class="text-2xl text-gray-400">→</div>
                            <div class="text-center">
                                <div class="w-16 h-16 rounded-full bg-purple-100 flex items-center justify-center mx-auto mb-2">
                                    <span class="text-2xl">🍇</span>
                                </div>
                                <div class="text-sm font-bold text-gray-800">Variedad/Plantación</div>
                                <div class="text-xs text-gray-600">Tempranillo, Garnacha...</div>
                            </div>
                            <div class="text-2xl text-gray-400">→</div>
                            <div class="text-center">
                                <div class="w-16 h-16 rounded-full bg-amber-100 flex items-center justify-center mx-auto mb-2">
                                    <span class="text-2xl">📦</span>
                                </div>
                                <div class="text-sm font-bold text-gray-800">Contenedor</div>
                                <div class="text-xs text-gray-600">500kg, Grado 12.5°</div>
                            </div>
                            <div class="text-2xl text-gray-400">→</div>
                            <div class="text-center">
                                <div class="w-16 h-16 rounded-full bg-blue-100 flex items-center justify-center mx-auto mb-2">
                                    <span class="text-2xl">💰</span>
                                </div>
                                <div class="text-sm font-bold text-gray-800">Factura</div>
                                <div class="text-xs text-gray-600">Cliente: Bodega X</div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">¿Por Qué es Importante la Trazabilidad?</h2>
                    <ul class="list-disc list-inside space-y-3 text-gray-700 mb-6 ml-4">
                        <li><strong>Denominación de Origen:</strong> Las DO exigen demostrar el origen exacto de cada kilo de uva</li>
                        <li><strong>Seguridad Alimentaria:</strong> Obligación legal de poder rastrear origen en caso de incidencia</li>
                        <li><strong>Certificaciones:</strong> Ecológico, Biodinámica y otras certificaciones requieren trazabilidad</li>
                        <li><strong><a href="{{ url('/subvenciones-pac-2024') }}" class="text-[var(--color-agro-green)] hover:underline">PAC</a>:</strong> Auditorías pueden requerir demostrar producción por parcela</li>
                        <li><strong>Calidad:</strong> Identifica qué parcelas producen la mejor uva</li>
                    </ul>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">Datos Trazables en Agro365</h2>
                    <div class="grid md:grid-cols-2 gap-4 mb-6">
                        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-[var(--color-agro-green-dark)] mb-2">📍 Origen</h3>
                            <ul class="text-gray-700 text-sm space-y-1">
                                <li>• Código SIGPAC completo</li>
                                <li>• Municipio y provincia</li>
                                <li>• Coordenadas GPS</li>
                            </ul>
                        </div>
                        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-[var(--color-agro-green-dark)] mb-2">🍇 Producto</h3>
                            <ul class="text-gray-700 text-sm space-y-1">
                                <li>• Variedad de uva</li>
                                <li>• Año de plantación</li>
                                <li>• Certificaciones (DO, Eco)</li>
                            </ul>
                        </div>
                        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-[var(--color-agro-green-dark)] mb-2">📋 Historial</h3>
                            <ul class="text-gray-700 text-sm space-y-1">
                                <li>• <a href="{{ url('/registro-fitosanitarios') }}" class="text-[var(--color-agro-green)] hover:underline">Tratamientos</a> aplicados</li>
                                <li>• Fechas de cada actividad</li>
                                <li>• Operarios responsables</li>
                            </ul>
                        </div>
                        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-[var(--color-agro-green-dark)] mb-2">📦 Entrega</h3>
                            <ul class="text-gray-700 text-sm space-y-1">
                                <li>• Peso y grado</li>
                                <li>• Fecha y hora de entrega</li>
                                <li>• Cliente/Bodega destino</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <section class="mb-12">
                    <div class="bg-gradient-to-r from-[var(--color-agro-green-bg)] to-[var(--color-agro-green-light)]/30 p-8 rounded-xl border border-[var(--color-agro-green)]/20">
                        <h3 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mb-4">🔗 Trazabilidad Automática</h3>
                        <p class="text-gray-700 mb-6">
                            Cada contenedor vinculado a su parcela de origen. Cumple normativa sin esfuerzo. <strong>6 meses gratis</strong>.
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
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">¿Por qué es obligatoria la trazabilidad?</h3>
                            <p class="text-gray-700">La normativa de seguridad alimentaria exige poder rastrear el origen de productos en caso de incidencia sanitaria.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">¿Cómo vinculo un contenedor a una parcela?</h3>
                            <p class="text-gray-700">Al registrar el contenedor durante la vendimia, seleccionas la parcela de origen y el sistema guarda la relación.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">¿Puedo mezclar uva de varias parcelas?</h3>
                            <p class="text-gray-700">Sí, un contenedor puede tener uva de múltiples parcelas y el sistema mantiene la trazabilidad de cada origen.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">¿Qué información aparece en el informe de trazabilidad?</h3>
                            <p class="text-gray-700">Código SIGPAC, variedad, tratamientos aplicados, fecha de vendimia, peso, grado y cliente destino.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">¿Es válido para certificación de DO?</h3>
                            <p class="text-gray-700">Sí, el sistema de trazabilidad cumple los requisitos de las principales Denominaciones de Origen.</p>
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
            {"@@type": "Question", "name": "¿Por qué es obligatoria la trazabilidad?", "acceptedAnswer": {"@@type": "Answer", "text": "La normativa de seguridad alimentaria exige poder rastrear el origen de productos en caso de incidencia sanitaria."}},
            {"@@type": "Question", "name": "¿Cómo vinculo un contenedor a una parcela?", "acceptedAnswer": {"@@type": "Answer", "text": "Al registrar el contenedor durante la vendimia, seleccionas la parcela de origen y el sistema guarda la relación."}},
            {"@@type": "Question", "name": "¿Puedo mezclar uva de varias parcelas?", "acceptedAnswer": {"@@type": "Answer", "text": "Sí, un contenedor puede tener uva de múltiples parcelas y el sistema mantiene la trazabilidad de cada origen."}},
            {"@@type": "Question", "name": "¿Qué información aparece en el informe de trazabilidad?", "acceptedAnswer": {"@@type": "Answer", "text": "Código SIGPAC, variedad, tratamientos aplicados, fecha de vendimia, peso, grado y cliente destino."}},
            {"@@type": "Question", "name": "¿Es válido para certificación de DO?", "acceptedAnswer": {"@@type": "Answer", "text": "Sí, el sistema de trazabilidad cumple los requisitos de las principales Denominaciones de Origen."}}
        ]
    }
    </script>
</body>
</html>
