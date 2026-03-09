<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <!-- SEO Meta Tags -->
    <title>Política de Cookies - Agro365 | Software de Gestión Agrícola</title>
    <meta name="description" content="Política de cookies de Agro365. Solo usamos cookies técnicas estrictamente necesarias. Sin publicidad, sin analítica, sin seguimiento de terceros.">
    <meta name="robots" content="index, follow">
    <meta name="author" content="Agro365">

    <!-- Canonical URL -->
    <link rel="canonical" href="{{ url()->current() }}">

    <!-- Hreflang -->
    <link rel="alternate" hreflang="es" href="{{ url()->current() }}">
    <link rel="alternate" hreflang="es-ES" href="{{ url()->current() }}">
    <link rel="alternate" hreflang="x-default" href="{{ url()->current() }}">

    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="Política de Cookies - Agro365">
    <meta property="og:description" content="Política de cookies de Agro365. Solo cookies técnicas necesarias, sin seguimiento.">
    <meta property="og:image" content="{{ asset('images/logo.png') }}">
    <meta property="og:locale" content="es_ES">
    <meta property="og:site_name" content="Agro365">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="Política de Cookies - Agro365">
    <meta name="twitter:description" content="Solo cookies técnicas necesarias. Sin publicidad ni seguimiento.">
    <meta name="twitter:image" content="{{ asset('images/logo.png') }}">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-50">
    <div class="min-h-screen py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="mb-8">
                <a href="{{ url('/') }}" class="inline-flex items-center text-[var(--color-agro-green-dark)] hover:text-[var(--color-agro-green)] mb-4">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Volver a Inicio
                </a>
                <h1 class="text-4xl font-bold text-gray-900">Política de Cookies</h1>
                <p class="mt-2 text-gray-600">Última actualización: 09/03/2026</p>
            </div>

            <!-- Content -->
            <div class="bg-white rounded-lg shadow-sm p-8 space-y-6">

                <section>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">¿Qué son las cookies?</h2>
                    <p class="text-gray-700 leading-relaxed">
                        Las cookies son pequeños archivos de texto que los sitios web almacenan en tu dispositivo para que funcionen correctamente. Agro365 utiliza únicamente cookies técnicas estrictamente necesarias.
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">¿Qué cookies utiliza Agro365?</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full border border-gray-300 text-sm">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="border border-gray-300 px-4 py-2 text-left font-semibold">Nombre</th>
                                    <th class="border border-gray-300 px-4 py-2 text-left font-semibold">Propósito</th>
                                    <th class="border border-gray-300 px-4 py-2 text-left font-semibold">Duración</th>
                                    <th class="border border-gray-300 px-4 py-2 text-left font-semibold">Tipo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="border border-gray-300 px-4 py-2 font-mono text-xs">agro365_session</td>
                                    <td class="border border-gray-300 px-4 py-2">Mantener tu sesión activa</td>
                                    <td class="border border-gray-300 px-4 py-2">3 horas</td>
                                    <td class="border border-gray-300 px-4 py-2">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Esencial</span>
                                    </td>
                                </tr>
                                <tr class="bg-gray-50">
                                    <td class="border border-gray-300 px-4 py-2 font-mono text-xs">XSRF-TOKEN</td>
                                    <td class="border border-gray-300 px-4 py-2">Protección contra ataques CSRF</td>
                                    <td class="border border-gray-300 px-4 py-2">Sesión del navegador</td>
                                    <td class="border border-gray-300 px-4 py-2">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Seguridad</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="border border-gray-300 px-4 py-2 font-mono text-xs">remember_web_*</td>
                                    <td class="border border-gray-300 px-4 py-2">Recordar sesión (si seleccionas "Recuérdame")</td>
                                    <td class="border border-gray-300 px-4 py-2">2 semanas</td>
                                    <td class="border border-gray-300 px-4 py-2">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Funcional</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">¿Necesito aceptar las cookies?</h2>
                    <div class="border-l-4 border-[var(--color-agro-green-dark)] bg-[var(--color-agro-green-bg)]/40 p-4 rounded-r-lg">
                        <p class="font-semibold text-[var(--color-agro-green-dark)]">No.</p>
                        <p class="text-gray-700 mt-1">
                            Todas las cookies que utilizamos son estrictamente necesarias para el funcionamiento del servicio y quedan exentas del requisito de consentimiento según el RGPD y la LSSI.
                        </p>
                    </div>
                </section>

                <section>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Cookies que NO utilizamos</h2>
                    <ul class="space-y-2 text-gray-700 ml-4">
                        <li>❌ Cookies de publicidad</li>
                        <li>❌ Cookies de analítica (Google Analytics u otras)</li>
                        <li>❌ Cookies de redes sociales</li>
                        <li>❌ Cookies de terceros para seguimiento</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">¿Puedo desactivarlas?</h2>
                    <p class="text-gray-700 leading-relaxed">
                        Puedes configurar tu navegador para bloquear o eliminar cookies, aunque esto afectará al funcionamiento correcto de la plataforma, ya que las cookies de sesión son necesarias para la autenticación.
                    </p>
                </section>

                <section class="bg-gray-50 p-6 rounded-lg">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Contacto</h2>
                    <p class="text-gray-700">
                        Para cualquier consulta sobre el uso de cookies:
                        📧 <a href="mailto:info@agro365.es" class="text-[var(--color-agro-green-dark)] hover:underline font-semibold">info@agro365.es</a>
                    </p>
                    <p class="text-gray-600 text-sm mt-3">
                        Para más información sobre cómo tratamos tus datos, consulta nuestra
                        <a href="{{ route('privacy') }}" class="text-[var(--color-agro-green-dark)] hover:underline">Política de Privacidad</a>.
                    </p>
                </section>

            </div>

            <!-- Footer Links -->
            <div class="mt-8 flex flex-wrap gap-4 justify-center text-sm">
                <a href="{{ route('aviso-legal') }}" class="text-[var(--color-agro-green-dark)] hover:underline">Aviso Legal</a>
                <span class="text-gray-400">•</span>
                <a href="{{ route('privacy') }}" class="text-[var(--color-agro-green-dark)] hover:underline">Política de Privacidad</a>
                <span class="text-gray-400">•</span>
                <a href="{{ route('terms') }}" class="text-[var(--color-agro-green-dark)] hover:underline">Términos y Condiciones</a>
            </div>
        </div>
    </div>

    <!-- Breadcrumb Schema.org -->
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "BreadcrumbList",
        "itemListElement": [{
            "@@type": "ListItem",
            "position": 1,
            "name": "Inicio",
            "item": "{{ url('/') }}"
        },{
            "@@type": "ListItem",
            "position": 2,
            "name": "Política de Cookies",
            "item": "{{ url()->current() }}"
        }]
    }
    </script>

    @include('partials.footer-seo')
</body>
</html>
