<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Informes Oficiales de Agricultura con Firma Digital | Agro365</title>
    <meta name="description" content="Genera informes oficiales de agricultura certificados con firma electrónica SHA-256 y código QR de verificación. 7 tipos de informes para inspecciones PAC.">
    <meta name="keywords" content="informes oficiales agricultura, informes PAC, firma electrónica agricultura, informe cuaderno campo, informes fitosanitarios, certificado digital agricultura, informes inspección, documentos oficiales agricultura">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url('/informes-oficiales-agricultura') }}">
    <meta property="og:title" content="Informes Oficiales de Agricultura - Agro365">
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
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-indigo-100 border border-indigo-300 mb-6">
                    <span class="text-lg">📄</span>
                    <span class="text-sm font-semibold text-indigo-800">Documentación Oficial</span>
                </div>
                <h1 class="text-5xl lg:text-6xl font-bold text-[var(--color-agro-green-dark)] mb-6">
                    Informes Oficiales de Agricultura con Firma Electrónica
                </h1>
                <p class="text-xl text-gray-600 leading-relaxed">
                    Genera <strong>informes certificados</strong> con firma electrónica SHA-256 y código QR de verificación. Válidos para inspecciones PAC y auditorías.
                </p>
            </div>

            <article class="prose prose-lg max-w-none">
                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">7 Tipos de Informes Oficiales</h2>
                    <div class="grid md:grid-cols-2 gap-4 mb-6">
                        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                            <div class="flex items-center gap-3 mb-2">
                                <span class="text-2xl">📋</span>
                                <h3 class="font-bold text-[var(--color-agro-green-dark)]">1. Cuaderno de Campo Completo</h3>
                            </div>
                            <p class="text-gray-700 text-sm">Todas las actividades del <a href="{{ route('content.cuaderno-digital-viticultores') }}" class="text-[var(--color-agro-green)] hover:underline">cuaderno de campo</a>.</p>
                        </div>
                        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                            <div class="flex items-center gap-3 mb-2">
                                <span class="text-2xl">🧪</span>
                                <h3 class="font-bold text-[var(--color-agro-green-dark)]">2. Tratamientos Fitosanitarios</h3>
                            </div>
                            <p class="text-gray-700 text-sm">Detalle de <a href="{{ url('/registro-fitosanitarios') }}" class="text-[var(--color-agro-green)] hover:underline">tratamientos</a> con productos y dosis.</p>
                        </div>
                        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                            <div class="flex items-center gap-3 mb-2">
                                <span class="text-2xl">🗺️</span>
                                <h3 class="font-bold text-[var(--color-agro-green-dark)]">3. Parcelas SIGPAC</h3>
                            </div>
                            <p class="text-gray-700 text-sm">Listado de parcelas con códigos <a href="{{ route('content.sigpac') }}" class="text-[var(--color-agro-green)] hover:underline">SIGPAC</a>.</p>
                        </div>
                        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                            <div class="flex items-center gap-3 mb-2">
                                <span class="text-2xl">🍇</span>
                                <h3 class="font-bold text-[var(--color-agro-green-dark)]">4. Informe de Cosecha</h3>
                            </div>
                            <p class="text-gray-700 text-sm">Rendimientos y datos de <a href="{{ url('/gestion-vendimia') }}" class="text-[var(--color-agro-green)] hover:underline">vendimia</a>.</p>
                        </div>
                        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                            <div class="flex items-center gap-3 mb-2">
                                <span class="text-2xl">💧</span>
                                <h3 class="font-bold text-[var(--color-agro-green-dark)]">5. Informe de Riegos</h3>
                            </div>
                            <p class="text-gray-700 text-sm">Consumos de agua con fuentes y caudales.</p>
                        </div>
                        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                            <div class="flex items-center gap-3 mb-2">
                                <span class="text-2xl">🌿</span>
                                <h3 class="font-bold text-[var(--color-agro-green-dark)]">6. Fertilizaciones</h3>
                            </div>
                            <p class="text-gray-700 text-sm">Registro completo de abonados.</p>
                        </div>
                        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 md:col-span-2">
                            <div class="flex items-center gap-3 mb-2">
                                <span class="text-2xl">📊</span>
                                <h3 class="font-bold text-[var(--color-agro-green-dark)]">7. Informe de Campaña Completa</h3>
                            </div>
                            <p class="text-gray-700 text-sm">Resumen ejecutivo de toda la campaña con actividades, cosecha y análisis.</p>
                        </div>
                    </div>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">Características de Seguridad</h2>
                    <div class="grid md:grid-cols-2 gap-6 mb-6">
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <div class="text-3xl mb-3">🔐</div>
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">Firma Electrónica SHA-256</h3>
                            <p class="text-gray-700">Cada informe está firmado digitalmente con hash SHA-256 que garantiza su integridad.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <div class="text-3xl mb-3">📱</div>
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">Código QR de Verificación</h3>
                            <p class="text-gray-700">Escanea el QR para verificar la autenticidad del documento en línea.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <div class="text-3xl mb-3">📅</div>
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">Fecha y Hora de Generación</h3>
                            <p class="text-gray-700">Timestamp oficial que certifica cuándo se generó el informe.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <div class="text-3xl mb-3">👤</div>
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">Datos del Titular</h3>
                            <p class="text-gray-700">Nombre, NIF y datos fiscales del agricultor titular.</p>
                        </div>
                    </div>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">Validez para Inspecciones PAC</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        Los informes generados por Agro365 son válidos para:
                    </p>
                    <ul class="list-disc list-inside space-y-2 text-gray-700 mb-6 ml-4">
                        <li>Inspecciones de cumplimiento <a href="{{ url('/subvenciones-pac-2024') }}" class="text-[var(--color-agro-green)] hover:underline">PAC</a></li>
                        <li>Auditorías de Denominación de Origen</li>
                        <li>Certificaciones ecológicas</li>
                        <li>Controles fitosanitarios</li>
                        <li>Requerimientos legales</li>
                    </ul>
                    <div class="bg-green-50 border-l-4 border-green-500 p-6 rounded-r-lg mb-6">
                        <p class="text-gray-700">
                            <strong>✅ Importante:</strong> La firma electrónica SHA-256 garantiza que el documento no ha sido modificado después de su generación. Cualquier alteración invalidaría la firma.
                        </p>
                    </div>
                </section>

                <section class="mb-12">
                    <div class="bg-gradient-to-r from-[var(--color-agro-green-bg)] to-[var(--color-agro-green-light)]/30 p-8 rounded-xl border border-[var(--color-agro-green)]/20">
                        <h3 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mb-4">📄 Documentación Oficial en Segundos</h3>
                        <p class="text-gray-700 mb-6">
                            Genera informes certificados con firma digital. Listos para inspección. <strong>6 meses gratis</strong>.
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
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">¿Son válidos para inspecciones PAC?</h3>
                            <p class="text-gray-700">Sí, los informes incluyen firma electrónica SHA-256 y código QR de verificación aceptados en inspecciones.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">¿Cómo verifico un informe?</h3>
                            <p class="text-gray-700">Escaneando el código QR del documento o introduciendo el hash en nuestra web de verificación.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">¿Puedo generar informes de campañas anteriores?</h3>
                            <p class="text-gray-700">Sí, puedes generar informes oficiales de cualquier campaña almacenada en el sistema.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">¿Qué pasa si modifico datos después de generar un informe?</h3>
                            <p class="text-gray-700">El informe generado no se modifica. Puedes generar un nuevo informe actualizado que tendrá un hash diferente.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">¿Cuántos informes puedo generar?</h3>
                            <p class="text-gray-700">Ilimitados. Puedes generar todos los informes que necesites sin coste adicional.</p>
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
            {"@type": "Question", "name": "¿Son válidos para inspecciones PAC?", "acceptedAnswer": {"@type": "Answer", "text": "Sí, los informes incluyen firma electrónica SHA-256 y código QR de verificación aceptados en inspecciones."}},
            {"@type": "Question", "name": "¿Cómo verifico un informe?", "acceptedAnswer": {"@type": "Answer", "text": "Escaneando el código QR del documento o introduciendo el hash en nuestra web de verificación."}},
            {"@type": "Question", "name": "¿Puedo generar informes de campañas anteriores?", "acceptedAnswer": {"@type": "Answer", "text": "Sí, puedes generar informes oficiales de cualquier campaña almacenada en el sistema."}},
            {"@type": "Question", "name": "¿Qué pasa si modifico datos después de generar un informe?", "acceptedAnswer": {"@type": "Answer", "text": "El informe generado no se modifica. Puedes generar un nuevo informe actualizado que tendrá un hash diferente."}},
            {"@type": "Question", "name": "¿Cuántos informes puedo generar?", "acceptedAnswer": {"@type": "Answer", "text": "Ilimitados. Puedes generar todos los informes que necesites sin coste adicional."}}
        ]
    }
    </script>
</body>
</html>
