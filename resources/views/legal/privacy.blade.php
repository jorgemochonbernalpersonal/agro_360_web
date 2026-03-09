<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <!-- SEO Meta Tags -->
    <title>Política de Privacidad y Cookies - Agro365 | Software de Gestión Agrícola</title>
    <meta name="description" content="Política de privacidad y cookies de Agro365 - Software de gestión agrícola. Protección de datos RGPD, cookies técnicas y seguridad de la información.">
    <meta name="robots" content="index, follow">
    <meta name="author" content="Agro365">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="{{ url()->current() }}">
    
    <!-- Hreflang -->
    <link rel="alternate" hreflang="es" href="{{ url()->current() }}">
    <link rel="alternate" hreflang="es-ES" href="{{ url()->current() }}">
    <link rel="alternate" hreflang="x-default" href="{{ url()->current() }}">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="Política de Privacidad y Cookies - Agro365">
    <meta property="og:description" content="Política de privacidad y cookies de Agro365 - Software de gestión agrícola para viticultores en España.">
    <meta property="og:image" content="{{ asset('images/logo.png') }}">
    <meta property="og:locale" content="es_ES">
    <meta property="og:site_name" content="Agro365">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:url" content="{{ url()->current() }}">
    <meta name="twitter:title" content="Política de Privacidad y Cookies - Agro365">
    <meta name="twitter:description" content="Política de privacidad y cookies de Agro365 - Software de gestión agrícola para viticultores.">
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
                <h1 class="text-4xl font-bold text-gray-900">Política de Privacidad y Cookies</h1>
                <p class="mt-2 text-gray-600">Última actualización: 09/03/2026</p>
            </div>

            <!-- Content -->
            <div class="bg-white rounded-lg shadow-sm p-8 space-y-6">

                <section>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">1. Responsable del Tratamiento</h2>
                    <ul class="space-y-2 text-gray-700">
                        <li><strong>Denominación social:</strong> Agro365</li>
                        <li><strong>Domicilio:</strong> Calle Toledo 172, Madrid, España</li>
                        <li><strong>Email:</strong> <a href="mailto:info@agro365.es" class="text-[var(--color-agro-green-dark)] hover:underline">info@agro365.es</a></li>
                        <li><strong>Actividad:</strong> Software de gestión agrícola</li>
                    </ul>
                    <p class="text-gray-500 text-sm mt-3 italic">(NIF se añadirá al formalizarse el alta)</p>
                </section>

                <section>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">2. Información General</h2>
                    <p class="text-gray-700 leading-relaxed">
                        En <strong>Agro365</strong> nos tomamos muy en serio la privacidad de nuestros usuarios. Esta política describe cómo recopilamos, usamos y protegemos tu información personal cuando utilizas nuestra plataforma, de conformidad con el Reglamento (UE) 2016/679 (RGPD) y la Ley Orgánica 3/2018 (LOPDGDD).
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">3. Datos que Recopilamos</h2>
                    <div class="space-y-4 text-gray-700">
                        <div>
                            <h3 class="font-semibold text-lg mb-2">3.1 Información de Cuenta</h3>
                            <ul class="list-disc list-inside space-y-1 ml-4">
                                <li>Nombre y apellidos</li>
                                <li>Dirección de correo electrónico</li>
                                <li>Contraseña (almacenada con hash bcrypt, nunca en texto plano)</li>
                                <li>Rol dentro de la plataforma (viticultor, bodega, DO)</li>
                            </ul>
                        </div>
                        <div>
                            <h3 class="font-semibold text-lg mb-2">3.2 Información de Gestión Agrícola</h3>
                            <ul class="list-disc list-inside space-y-1 ml-4">
                                <li>Parcelas y ubicaciones SIGPAC</li>
                                <li>Actividades agrícolas y tratamientos fitosanitarios</li>
                                <li>Cuadrillas y personal asignado</li>
                                <li>Maquinaria y productos utilizados</li>
                            </ul>
                        </div>
                        <div>
                            <h3 class="font-semibold text-lg mb-2">3.3 Información Técnica</h3>
                            <ul class="list-disc list-inside space-y-1 ml-4">
                                <li>Dirección IP</li>
                                <li>Tipo de navegador y dispositivo</li>
                                <li>Fecha y hora de acceso</li>
                                <li>Logs de actividad para seguridad del servicio</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <section>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">4. Base Legal del Tratamiento</h2>
                    <ul class="space-y-2 text-gray-700">
                        <li><strong>Datos de cuenta y gestión agrícola</strong> → Ejecución del contrato (art. 6.1.b RGPD)</li>
                        <li><strong>Cumplimiento normativo</strong> (cuaderno de campo, PAC, informes) → Obligación legal (art. 6.1.c RGPD)</li>
                        <li><strong>Datos técnicos</strong> → Interés legítimo (art. 6.1.f RGPD)</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">5. Uso de tus Datos</h2>
                    <p class="text-gray-700 leading-relaxed mb-3">Utilizamos tu información exclusivamente para:</p>
                    <ul class="list-disc list-inside space-y-1 text-gray-700 ml-4">
                        <li>Proporcionar y mantener el servicio</li>
                        <li>Gestionar tu cuenta y suscripción</li>
                        <li>Generar informes oficiales con validez normativa</li>
                        <li>Enviar notificaciones importantes sobre el servicio</li>
                        <li>Mejorar la funcionalidad de la plataforma</li>
                        <li>Cumplir con obligaciones legales agrícolas</li>
                    </ul>
                    <p class="text-gray-700 mt-4 font-medium">No cedemos ni vendemos tus datos a terceros con fines comerciales o publicitarios.</p>
                </section>

                <section>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">6. Conservación de los Datos</h2>
                    <p class="text-gray-700 leading-relaxed">
                        Tus datos se conservarán mientras mantengas tu cuenta activa. Tras la cancelación, se conservarán durante un máximo de <strong>5 años</strong> para el cumplimiento de obligaciones legales, tras lo cual serán eliminados o anonimizados. Puedes exportar tus datos en cualquier momento desde la plataforma.
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">7. Transferencias Internacionales</h2>
                    <p class="text-gray-700 leading-relaxed">
                        Agro365 utiliza infraestructura alojada en servidores dentro del Espacio Económico Europeo. No realizamos transferencias de datos fuera de la UE/EEE.
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">8. Seguridad</h2>
                    <ul class="list-disc list-inside space-y-2 text-gray-700 ml-4">
                        <li>Cifrado SSL/TLS en todas las conexiones</li>
                        <li>Contraseñas hasheadas con bcrypt</li>
                        <li>Protección contra CSRF, XSS y clickjacking</li>
                        <li>Copias de seguridad regulares</li>
                        <li>Acceso restringido basado en roles</li>
                        <li>Firma electrónica SHA-256 en documentos oficiales</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">9. Tus Derechos (RGPD)</h2>
                    <p class="text-gray-700 leading-relaxed">
                        Tienes derecho a acceso, rectificación, supresión, portabilidad, oposición y limitación del tratamiento. Para ejercerlos escríbenos a <a href="mailto:info@agro365.es" class="text-[var(--color-agro-green-dark)] hover:underline font-semibold">info@agro365.es</a>. Responderemos en un máximo de 30 días. Si lo consideras necesario, puedes reclamar ante la AEPD en <a href="https://www.aepd.es" target="_blank" rel="noopener noreferrer" class="text-[var(--color-agro-green-dark)] hover:underline">www.aepd.es</a>.
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">10. Cambios en esta Política</h2>
                    <p class="text-gray-700 leading-relaxed">
                        Te notificaremos cualquier cambio significativo por email o mediante aviso en la plataforma con al menos 15 días de antelación.
                    </p>
                </section>

                <section class="bg-gray-50 p-6 rounded-lg">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">11. Contacto</h2>
                    <p class="text-gray-700">
                        📧 <a href="mailto:info@agro365.es" class="text-[var(--color-agro-green-dark)] hover:underline font-semibold">info@agro365.es</a>
                        <span class="text-gray-500 text-sm ml-2">— respondemos en menos de 48 horas</span>
                    </p>
                    <p class="text-gray-600 text-sm mt-3">
                        Para más información sobre el uso de cookies, consulta nuestra
                        <a href="{{ route('cookies') }}" class="text-[var(--color-agro-green-dark)] hover:underline">Política de Cookies</a>.
                    </p>
                </section>

            </div>

            <!-- Footer Links -->
            <div class="mt-8 flex flex-wrap gap-4 justify-center text-sm">
                <a href="{{ route('aviso-legal') }}" class="text-[var(--color-agro-green-dark)] hover:underline">Aviso Legal</a>
                <span class="text-gray-400">•</span>
                <a href="{{ route('terms') }}" class="text-[var(--color-agro-green-dark)] hover:underline">Términos y Condiciones</a>
                <span class="text-gray-400">•</span>
                <a href="{{ route('cookies') }}" class="text-[var(--color-agro-green-dark)] hover:underline">Política de Cookies</a>
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
            "name": "Política de Privacidad",
            "item": "{{ url()->current() }}"
        }]
    }
    </script>

    @include('partials.footer-seo')
</body>
</html>
