<x-guest-layout>
    <div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto bg-white shadow-sm rounded-lg p-8">
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Política de Privacidad y Cookies</h1>
                <p class="mt-2 text-sm text-gray-600">Última actualización: {{ date('d/m/Y') }}</p>
            </div>

            <!-- Introducción -->
            <section class="mb-8">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">1. Información General</h2>
                <p class="text-gray-700 leading-relaxed">
                    En <strong>Agro365</strong>, nos tomamos muy en serio la privacidad de nuestros usuarios. 
                    Esta política describe cómo recopilamos, usamos y protegemos tu información personal 
                    cuando utilizas nuestra plataforma de gestión agrícola.
                </p>
            </section>

            <!-- Datos que recopilamos -->
            <section class="mb-8">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">2. Datos que Recopilamos</h2>
                <div class="space-y-4 text-gray-700">
                    <div>
                        <h3 class="font-semibold text-lg mb-2">2.1 Información de Cuenta</h3>
                        <ul class="list-disc list-inside space-y-1 ml-4">
                            <li>Nombre y apellidos</li>
                            <li>Dirección de correo electrónico</li>
                            <li>Contraseña (encriptada)</li>
                            <li>Rol dentro de la organización</li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="font-semibold text-lg mb-2">2.2 Información de Gestión Agrícola</h3>
                        <ul class="list-disc list-inside space-y-1 ml-4">
                            <li>Parcelas y ubicaciones SIGPAC</li>
                            <li>Actividades agrícolas y tratamientos</li>
                            <li>Cuadrillas y personal asignado</li>
                            <li>Maquinaria y productos fitosanitarios</li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="font-semibold text-lg mb-2">2.3 Información Técnica</h3>
                        <ul class="list-disc list-inside space-y-1 ml-4">
                            <li>Dirección IP</li>
                            <li>Tipo de navegador</li>
                            <li>Fecha y hora de acceso</li>
                        </ul>
                    </div>
                </div>
            </section>

            <!-- Uso de datos -->
            <section class="mb-8">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">3. Uso de tus Datos</h2>
                <p class="text-gray-700 leading-relaxed mb-4">
                    Utilizamos tu información para:
                </p>
                <ul class="list-disc list-inside space-y-2 text-gray-700 ml-4">
                    <li>Proporcionar y mantener el servicio de Agro365</li>
                    <li>Gestionar tu cuenta y suscripción</li>
                    <li>Enviar notificaciones importantes sobre el servicio</li>
                    <li>Mejorar la funcionalidad de la plataforma</li>
                    <li>Cumplir con obligaciones legales y normativas agrícolas</li>
                </ul>
            </section>

            <!-- Cookies -->
            <section class="mb-8 border-l-4 border-green-500 pl-6 bg-green-50 p-6 rounded-r-lg">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">4. Política de Cookies</h2>
                
                <div class="space-y-4 text-gray-700">
                    <p class="leading-relaxed">
                        Agro365 utiliza <strong>únicamente cookies técnicas estrictamente necesarias</strong> 
                        para el funcionamiento del servicio. Estas cookies son esenciales y no requieren 
                        tu consentimiento según el RGPD.
                    </p>

                    <h3 class="font-semibold text-lg mt-4 mb-2">4.1 Cookies que Utilizamos</h3>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full border border-gray-300 text-sm">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="border border-gray-300 px-4 py-2 text-left">Nombre</th>
                                    <th class="border border-gray-300 px-4 py-2 text-left">Propósito</th>
                                    <th class="border border-gray-300 px-4 py-2 text-left">Duración</th>
                                    <th class="border border-gray-300 px-4 py-2 text-left">Tipo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="border border-gray-300 px-4 py-2 font-mono text-xs">agro365_session</td>
                                    <td class="border border-gray-300 px-4 py-2">Mantener tu sesión activa</td>
                                    <td class="border border-gray-300 px-4 py-2">3 horas</td>
                                    <td class="border border-gray-300 px-4 py-2">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Esencial
                                        </span>
                                    </td>
                                </tr>
                                <tr class="bg-gray-50">
                                    <td class="border border-gray-300 px-4 py-2 font-mono text-xs">XSRF-TOKEN</td>
                                    <td class="border border-gray-300 px-4 py-2">Protección contra ataques CSRF</td>
                                    <td class="border border-gray-300 px-4 py-2">Sesión del navegador</td>
                                    <td class="border border-gray-300 px-4 py-2">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Esencial/Seguridad
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="border border-gray-300 px-4 py-2 font-mono text-xs">remember_web_*</td>
                                    <td class="border border-gray-300 px-4 py-2">Recordar sesión (si seleccionas "Recordarme")</td>
                                    <td class="border border-gray-300 px-4 py-2">2 semanas</td>
                                    <td class="border border-gray-300 px-4 py-2">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Funcional
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <h3 class="font-semibold text-lg mt-6 mb-2">4.2 ¿Necesito Aceptar las Cookies?</h3>
                    <div class="bg-white border-l-4 border-green-600 p-4 rounded">
                        <p class="font-semibold text-green-800">No.</p>
                        <p class="text-gray-700 mt-2">
                            Estas cookies son estrictamente necesarias para el funcionamiento del servicio 
                            y no requieren tu consentimiento según la normativa RGPD.
                        </p>
                    </div>

                    <h3 class="font-semibold text-lg mt-6 mb-2">4.3 Cookies que NO Utilizamos</h3>
                    <ul class="list-disc list-inside space-y-1 text-gray-700 ml-4">
                        <li>❌ Cookies de publicidad</li>
                        <li>❌ Cookies de analytics (Google Analytics, etc.)</li>
                        <li>❌ Cookies de redes sociales</li>
                        <li>❌ Cookies de terceros para tracking</li>
                    </ul>

                    <h3 class="font-semibold text-lg mt-6 mb-2">4.4 Gestión de Cookies</h3>
                    <p class="text-gray-700 leading-relaxed">
                        Puedes configurar tu navegador para bloquear o eliminar cookies, pero esto afectará 
                        tu capacidad para usar Agro365 correctamente, ya que las cookies de sesión son 
                        necesarias para mantener tu autenticación.
                    </p>
                </div>
            </section>

            <!-- Seguridad -->
            <section class="mb-8">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">5. Seguridad</h2>
                <p class="text-gray-700 leading-relaxed">
                    Implementamos medidas técnicas y organizativas apropiadas para proteger tus datos:
                </p>
                <ul class="list-disc list-inside space-y-2 text-gray-700 ml-4 mt-4">
                    <li>Encriptación SSL/TLS para todas las conexiones</li>
                    <li>Contraseñas hasheadas con bcrypt</li>
                    <li>Protección contra ataques CSRF, XSS y clickjacking</li>
                    <li>Copias de seguridad regulares</li>
                    <li>Acceso restringido basado en roles</li>
                </ul>
            </section>

            <!-- Tus Derechos -->
            <section class="mb-8">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">6. Tus Derechos (RGPD)</h2>
                <p class="text-gray-700 leading-relaxed mb-4">
                    Según el RGPD, tienes derecho a:
                </p>
                <ul class="list-disc list-inside space-y-2 text-gray-700 ml-4">
                    <li><strong>Acceso:</strong> Solicitar una copia de tus datos personales</li>
                    <li><strong>Rectificación:</strong> Corregir datos inexactos o incompletos</li>
                    <li><strong>Supresión:</strong> Solicitar la eliminación de tus datos</li>
                    <li><strong>Portabilidad:</strong> Recibir tus datos en formato estructurado</li>
                    <li><strong>Oposición:</strong> Oponerte al tratamiento de tus datos</li>
                    <li><strong>Limitación:</strong> Solicitar la limitación del tratamiento</li>
                </ul>
                <p class="text-gray-700 leading-relaxed mt-4">
                    Para ejercer estos derechos, contacta con nosotros en: 
                    <a href="mailto:privacidad@agro365.com" class="text-green-600 hover:text-green-700 font-medium">
                        privacidad@agro365.com
                    </a>
                </p>
            </section>

            <!-- Cambios -->
            <section class="mb-8">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">7. Cambios en esta Política</h2>
                <p class="text-gray-700 leading-relaxed">
                    Nos reservamos el derecho de actualizar esta política. Te notificaremos de cualquier 
                    cambio significativo por correo electrónico o mediante un aviso en la plataforma.
                </p>
            </section>

            <!-- Contacto -->
            <section class="mb-8 bg-gray-50 p-6 rounded-lg">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">8. Contacto</h2>
                <p class="text-gray-700 leading-relaxed mb-4">
                    Si tienes preguntas sobre esta política o sobre el tratamiento de tus datos:
                </p>
                <div class="space-y-2 text-gray-700">
                    <p><strong>Email:</strong> <a href="mailto:info@agro365.es" class="text-green-600 hover:text-green-700">info@agro365.es</a></p>
                </div>
            </section>

            <!-- Back button -->
            <div class="text-center mt-8">
                <a href="{{ route('home') }}" class="inline-flex items-center px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors">
                    ← Volver al inicio
                </a>
            </div>
        </div>
    </div>
</x-guest-layout>
