<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <!-- SEO Meta Tags -->
    <title>Eliminación de Cuenta - Agro365 | Software de Gestión Agrícola</title>
    <meta name="description" content="Solicita la eliminación de tu cuenta y datos asociados en Agro365. Información sobre el proceso, los datos eliminados y los periodos de retención.">
    <meta name="robots" content="index, follow">
    <meta name="author" content="Agro365">

    <!-- Canonical URL -->
    <link rel="canonical" href="{{ url()->current() }}">

    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="Eliminación de Cuenta - Agro365">
    <meta property="og:description" content="Solicita la eliminación de tu cuenta y datos en Agro365.">
    <meta property="og:image" content="{{ asset('images/logo.png') }}">
    <meta property="og:locale" content="es_ES">
    <meta property="og:site_name" content="Agro365">

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
                <h1 class="text-4xl font-bold text-gray-900">Eliminación de Cuenta</h1>
                <p class="mt-2 text-gray-600">Agro365 - Software de Gestión Agrícola</p>
            </div>

            <!-- Content -->
            <div class="bg-white rounded-lg shadow-sm p-8 space-y-8">

                <section>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">1. Cómo solicitar la eliminación de tu cuenta</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        En <strong>Agro365</strong> puedes solicitar la eliminación completa de tu cuenta y datos asociados de las siguientes maneras:
                    </p>

                    <div class="space-y-6">
                        <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                            <h3 class="font-semibold text-lg text-green-800 mb-2">Opción A: Desde la aplicación móvil</h3>
                            <ol class="list-decimal list-inside space-y-2 text-gray-700 ml-2">
                                <li>Abre la app <strong>Agro365</strong> e inicia sesión con tu cuenta.</li>
                                <li>Ve a <strong>Perfil</strong> (icono de usuario en la barra inferior).</li>
                                <li>Pulsa en <strong>"Eliminar cuenta"</strong>.</li>
                                <li>Introduce tu contraseña actual para confirmar tu identidad.</li>
                                <li>Confirma la eliminación. Tu cuenta y datos se eliminarán de forma permanente.</li>
                            </ol>
                        </div>

                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                            <h3 class="font-semibold text-lg text-blue-800 mb-2">Opción B: Por correo electrónico</h3>
                            <ol class="list-decimal list-inside space-y-2 text-gray-700 ml-2">
                                <li>Envía un correo a <a href="mailto:info@agro365.es" class="text-[var(--color-agro-green-dark)] font-semibold hover:underline">info@agro365.es</a> con el asunto <strong>"Solicitud de eliminación de cuenta"</strong>.</li>
                                <li>Incluye la dirección de correo electrónico asociada a tu cuenta.</li>
                                <li>Verificaremos tu identidad y procesaremos la solicitud en un plazo máximo de <strong>30 días</strong>, conforme al RGPD.</li>
                            </ol>
                        </div>
                    </div>
                </section>

                <section>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">2. Datos que se eliminan</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        Al eliminar tu cuenta, se borran de forma permanente e irreversible los siguientes datos:
                    </p>
                    <ul class="list-disc list-inside space-y-2 text-gray-700 ml-4">
                        <li><strong>Datos de cuenta:</strong> nombre, correo electrónico, contraseña, DNI/NIF, rol, imagen de perfil y preferencias.</li>
                        <li><strong>Datos de perfil:</strong> dirección, ciudad, código postal, provincia, teléfono.</li>
                        <li><strong>Datos agrícolas:</strong> parcelas, actividades del cuaderno de campo (tratamientos, riegos, labores culturales, fertilizaciones, observaciones, podas, vendimias), rendimientos estimados.</li>
                        <li><strong>Datos de bodega:</strong> contenedores, lotes de vino, transferencias, historial de operaciones.</li>
                        <li><strong>Facturas y documentos:</strong> facturas, informes oficiales, certificaciones.</li>
                        <li><strong>Tokens de acceso:</strong> todos los tokens de sesión y API se revocan inmediatamente.</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">3. Datos que pueden conservarse</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        De acuerdo con la legislación vigente, podemos retener cierta información limitada durante los periodos legalmente establecidos:
                    </p>
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse border border-gray-200 text-sm">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="border border-gray-200 px-4 py-3 text-left font-semibold text-gray-900">Tipo de dato</th>
                                    <th class="border border-gray-200 px-4 py-3 text-left font-semibold text-gray-900">Periodo de retención</th>
                                    <th class="border border-gray-200 px-4 py-3 text-left font-semibold text-gray-900">Base legal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="border border-gray-200 px-4 py-3 text-gray-700">Registros de facturación</td>
                                    <td class="border border-gray-200 px-4 py-3 text-gray-700">4 años</td>
                                    <td class="border border-gray-200 px-4 py-3 text-gray-700">Ley General Tributaria (art. 66)</td>
                                </tr>
                                <tr class="bg-gray-50">
                                    <td class="border border-gray-200 px-4 py-3 text-gray-700">Logs de seguridad (anonimizados)</td>
                                    <td class="border border-gray-200 px-4 py-3 text-gray-700">12 meses</td>
                                    <td class="border border-gray-200 px-4 py-3 text-gray-700">Ley 34/2002 (LSSI-CE, art. 12)</td>
                                </tr>
                                <tr>
                                    <td class="border border-gray-200 px-4 py-3 text-gray-700">Cuaderno de campo digital</td>
                                    <td class="border border-gray-200 px-4 py-3 text-gray-700">3 años</td>
                                    <td class="border border-gray-200 px-4 py-3 text-gray-700">Normativa PAC / Reglamento (UE) 2021/2116</td>
                                </tr>
                                <tr class="bg-gray-50">
                                    <td class="border border-gray-200 px-4 py-3 text-gray-700">Registros SILICIE / INFOVI</td>
                                    <td class="border border-gray-200 px-4 py-3 text-gray-700">5 años</td>
                                    <td class="border border-gray-200 px-4 py-3 text-gray-700">Normativa vitivinícola estatal</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p class="text-gray-600 text-sm mt-3">
                        Los datos retenidos por obligación legal se conservan de forma anonimizada o seudonimizada y no se utilizan para ningún otro fin. Una vez transcurrido el periodo de retención, se eliminan automáticamente.
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">4. Consecuencias de la eliminación</h2>
                    <ul class="list-disc list-inside space-y-2 text-gray-700 ml-4">
                        <li>La eliminación es <strong>permanente e irreversible</strong>. No podremos recuperar tu cuenta ni tus datos una vez completada.</li>
                        <li>Perderás acceso a todas las funcionalidades de la plataforma.</li>
                        <li>Si eres viticultor vinculado a una bodega, se eliminará la asociación.</li>
                        <li>Podrás crear una nueva cuenta con el mismo correo electrónico en el futuro.</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">5. Tus derechos (RGPD)</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        Conforme al Reglamento General de Protección de Datos (UE) 2016/679, tienes derecho a:
                    </p>
                    <ul class="list-disc list-inside space-y-2 text-gray-700 ml-4">
                        <li><strong>Acceso:</strong> solicitar una copia de tus datos personales.</li>
                        <li><strong>Rectificación:</strong> corregir datos inexactos o incompletos.</li>
                        <li><strong>Supresión:</strong> solicitar la eliminación de tus datos (derecho al olvido).</li>
                        <li><strong>Portabilidad:</strong> recibir tus datos en un formato estructurado y legible.</li>
                        <li><strong>Oposición:</strong> oponerte al tratamiento de tus datos.</li>
                        <li><strong>Limitación:</strong> solicitar la restricción del tratamiento.</li>
                    </ul>
                    <p class="text-gray-700 leading-relaxed mt-4">
                        Para ejercer cualquiera de estos derechos, contacta con nosotros en
                        <a href="mailto:info@agro365.es" class="text-[var(--color-agro-green-dark)] font-semibold hover:underline">info@agro365.es</a>.
                    </p>
                </section>

                <section class="border-t border-gray-200 pt-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">6. Contacto</h2>
                    <ul class="space-y-2 text-gray-700">
                        <li><strong>Email:</strong> <a href="mailto:info@agro365.es" class="text-[var(--color-agro-green-dark)] hover:underline">info@agro365.es</a></li>
                        <li><strong>Aplicación:</strong> Agro365</li>
                        <li><strong>Desarrollador:</strong> Agro365</li>
                    </ul>
                    <p class="text-gray-500 text-sm mt-4">Última actualización: abril 2026</p>
                </section>

            </div>

            <!-- Footer links -->
            <div class="mt-8 flex flex-wrap gap-4 text-sm text-gray-500">
                <a href="{{ route('privacy') }}" class="hover:text-gray-700 hover:underline">Política de Privacidad</a>
                <a href="{{ route('terms') }}" class="hover:text-gray-700 hover:underline">Términos y Condiciones</a>
                <a href="{{ route('cookies') }}" class="hover:text-gray-700 hover:underline">Política de Cookies</a>
                <a href="{{ route('aviso-legal') }}" class="hover:text-gray-700 hover:underline">Aviso Legal</a>
            </div>
        </div>
    </div>
</body>
</html>
