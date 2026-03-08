<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Firma Digital en Agricultura: Documentos Certificados | Agro365</title>
    <meta name="description" content="Firma electrónica para documentos agrícolas: informes oficiales, cuaderno de campo y certificados con hash SHA-256 y verificación QR. Validez legal.">
    <meta name="keywords" content="firma digital agricultura, firma electrónica cuaderno campo, documentos certificados agricultura, hash SHA-256, verificación QR, firma digital PAC, certificar documentos agrícolas">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url('/firma-digital-agricultura') }}">
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
                    <span class="text-lg">🔐</span>
                    <span class="text-sm font-semibold text-indigo-800">Seguridad Documental</span>
                </div>
                <h1 class="text-5xl lg:text-6xl font-bold text-[var(--color-agro-green-dark)] mb-6">
                    Firma Digital en Agricultura
                </h1>
                <p class="text-xl text-gray-600 leading-relaxed">
                    <strong>Documentos certificados</strong> con firma electrónica SHA-256 y código QR de verificación. Garantiza la integridad de tus <a href="{{ url('/informes-oficiales-agricultura') }}" class="text-[var(--color-agro-green)] hover:underline">informes oficiales</a> para inspecciones y auditorías.
                </p>
            </div>

            <article class="prose prose-lg max-w-none">
                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">¿Qué es la Firma Digital SHA-256?</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        La firma <strong>SHA-256</strong> es un algoritmo criptográfico que genera un código único (hash) a partir del contenido del documento. Cualquier modificación, por mínima que sea, cambia completamente el hash.
                    </p>
                    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 mb-6">
                        <h3 class="font-bold text-[var(--color-agro-green-dark)] mb-4">Ejemplo de Hash SHA-256</h3>
                        <code class="block bg-gray-100 p-4 rounded text-sm break-all">
                            a3f2b8c1d4e5f6a7b8c9d0e1f2a3b4c5d6e7f8a9b0c1d2e3f4a5b6c7d8e9f0a1
                        </code>
                        <p class="text-gray-600 text-sm mt-3">Este código único identifica el documento exacto. Si cambia una coma, el hash sería completamente diferente.</p>
                    </div>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">Componentes de Seguridad</h2>
                    <div class="grid md:grid-cols-2 gap-6 mb-6">
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <div class="text-3xl mb-3">🔐</div>
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">Hash SHA-256</h3>
                            <p class="text-gray-700">Código criptográfico único que certifica que el documento no ha sido alterado.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <div class="text-3xl mb-3">📱</div>
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">Código QR</h3>
                            <p class="text-gray-700">Escanea para verificar la autenticidad del documento en línea.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <div class="text-3xl mb-3">⏰</div>
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">Timestamp</h3>
                            <p class="text-gray-700">Fecha y hora exacta de generación del documento.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <div class="text-3xl mb-3">📋</div>
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">Datos del Titular</h3>
                            <p class="text-gray-700">Nombre, NIF y datos de la explotación agrícola.</p>
                        </div>
                    </div>
                </section>

                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-[var(--color-agro-green-dark)] mb-4">Documentos que se Firman</h2>
                    <ul class="list-disc list-inside space-y-2 text-gray-700 mb-6 ml-4">
                        <li><a href="{{ url('/informes-oficiales-agricultura') }}" class="text-[var(--color-agro-green)] hover:underline">Informes oficiales</a> de cuaderno de campo</li>
                        <li>Informes de <a href="{{ url('/registro-fitosanitarios') }}" class="text-[var(--color-agro-green)] hover:underline">tratamientos fitosanitarios</a></li>
                        <li>Certificados de parcelas <a href="{{ content_route('content.que-es-sigpac') }}" class="text-[var(--color-agro-green)] hover:underline">SIGPAC</a></li>
                        <li>Informes de <a href="{{ url('/gestion-vendimia') }}" class="text-[var(--color-agro-green)] hover:underline">cosecha</a> y rendimientos</li>
                        <li>Informes de campaña completa</li>
                    </ul>
                </section>

                <section class="mb-12">
                    <div class="bg-gradient-to-r from-[var(--color-agro-green-bg)] to-[var(--color-agro-green-light)]/30 p-8 rounded-xl border border-[var(--color-agro-green)]/20">
                        <h3 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mb-4">🔐 Documentos Verificables</h3>
                        <p class="text-gray-700 mb-6">
                            Genera documentos con firma digital SHA-256. Verificables por inspectores. <strong>3 meses gratis</strong>.
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
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">¿Qué es SHA-256?</h3>
                            <p class="text-gray-700">Es un algoritmo criptográfico que genera un código único de 64 caracteres a partir del contenido del documento.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">¿Tiene validez legal la firma?</h3>
                            <p class="text-gray-700">Sí, la firma electrónica SHA-256 proporciona integridad documental aceptada en inspecciones oficiales.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">¿Cómo verifico un documento firmado?</h3>
                            <p class="text-gray-700">Escanea el código QR o introduce el hash en nuestra página de verificación para comprobar la autenticidad.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">¿Se puede falsificar la firma?</h3>
                            <p class="text-gray-700">No, cualquier modificación del documento cambiaría el hash, lo que invalidaría la firma automáticamente.</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg text-[var(--color-agro-green-dark)] mb-2">¿Necesito un certificado digital personal?</h3>
                            <p class="text-gray-700">No, Agro365 firma los documentos automáticamente con su propio certificado, vinculado a tu cuenta.</p>
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
            {"@@type": "Question", "name": "¿Qué es SHA-256?", "acceptedAnswer": {"@@type": "Answer", "text": "Es un algoritmo criptográfico que genera un código único de 64 caracteres a partir del contenido del documento."}},
            {"@@type": "Question", "name": "¿Tiene validez legal la firma?", "acceptedAnswer": {"@@type": "Answer", "text": "Sí, la firma electrónica SHA-256 proporciona integridad documental aceptada en inspecciones oficiales."}},
            {"@@type": "Question", "name": "¿Cómo verifico un documento firmado?", "acceptedAnswer": {"@@type": "Answer", "text": "Escanea el código QR o introduce el hash en nuestra página de verificación para comprobar la autenticidad."}},
            {"@@type": "Question", "name": "¿Se puede falsificar la firma?", "acceptedAnswer": {"@@type": "Answer", "text": "No, cualquier modificación del documento cambiaría el hash, lo que invalidaría la firma automáticamente."}},
            {"@@type": "Question", "name": "¿Necesito un certificado digital personal?", "acceptedAnswer": {"@@type": "Answer", "text": "No, Agro365 firma los documentos automáticamente con su propio certificado, vinculado a tu cuenta."}}
        ]
    }
    </script>
</body>
</html>
