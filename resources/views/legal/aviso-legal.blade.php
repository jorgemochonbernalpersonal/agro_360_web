<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Aviso Legal - Agro365</title>
    <meta name="description" content="Aviso legal de Agro365 - Software de gestión agrícola para viñedos y bodegas">
    <meta name="robots" content="noindex, follow">
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
                <h1 class="text-4xl font-bold text-gray-900">Aviso Legal</h1>
                <p class="mt-2 text-gray-600">Última actualización: {{ now()->format('d/m/Y') }}</p>
            </div>

            <!-- Content -->
            <div class="bg-white rounded-lg shadow-sm p-8 space-y-6">
                <section>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">1. Datos Identificativos</h2>
                    <p class="text-gray-700 leading-relaxed">
                        En cumplimiento del artículo 10 de la Ley 34/2002, de 11 de julio, de Servicios de la Sociedad de la Información y Comercio Electrónico, se informa a los usuarios de los datos identificativos del titular del sitio web:
                    </p>
                    <ul class="mt-4 space-y-2 text-gray-700">
                        <li><strong>Denominación social:</strong> Agro365</li>
                        <li><strong>Dominio:</strong> agro365.es</li>
                        <li><strong>Email de contacto:</strong> info@agro365.es</li>
                        <li><strong>Actividad:</strong> Software de gestión agrícola</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">2. Objeto</h2>
                    <p class="text-gray-700 leading-relaxed">
                        El presente aviso legal regula el uso del sitio web <strong>agro365.es</strong> (en adelante, el "Sitio Web"), del que es titular Agro365.
                    </p>
                    <p class="text-gray-700 leading-relaxed mt-2">
                        La navegación por el Sitio Web atribuye la condición de usuario del mismo e implica la aceptación plena y sin reservas de todas y cada una de las disposiciones incluidas en este Aviso Legal.
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">3. Condiciones de Uso</h2>
                    <p class="text-gray-700 leading-relaxed">
                        El acceso y uso del Sitio Web se rige por las siguientes condiciones:
                    </p>
                    <ul class="mt-4 space-y-2 text-gray-700 list-disc list-inside">
                        <li>El uso del Sitio Web es responsabilidad exclusiva del usuario.</li>
                        <li>El usuario se compromete a hacer un uso adecuado de los contenidos y servicios.</li>
                        <li>Queda prohibido el uso del Sitio Web con fines ilícitos o lesivos.</li>
                        <li>El usuario no podrá realizar actividades publicitarias o de explotación comercial sin autorización previa.</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">4. Propiedad Intelectual e Industrial</h2>
                    <p class="text-gray-700 leading-relaxed">
                        Todos los contenidos del Sitio Web, incluyendo textos, fotografías, gráficos, imágenes, iconos, tecnología, software, así como su diseño gráfico y códigos fuente, constituyen una obra cuya propiedad pertenece a Agro365, sin que puedan entenderse cedidos al usuario ninguno de los derechos de explotación sobre los mismos.
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">5. Exclusión de Garantías y Responsabilidad</h2>
                    <p class="text-gray-700 leading-relaxed">
                        Agro365 no se hace responsable, en ningún caso, de los daños y perjuicios de cualquier naturaleza que pudieran ocasionar, a título enunciativo: errores u omisiones en los contenidos, falta de disponibilidad del portal o la transmisión de virus o programas maliciosos o lesivos en los contenidos.
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">6. Modificaciones</h2>
                    <p class="text-gray-700 leading-relaxed">
                        Agro365 se reserva el derecho de efectuar sin previo aviso las modificaciones que considere oportunas en su portal, pudiendo cambiar, suprimir o añadir tanto los contenidos y servicios que se presten a través de la misma como la forma en la que éstos aparezcan presentados o localizados.
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">7. Enlaces</h2>
                    <p class="text-gray-700 leading-relaxed">
                        En el caso de que en el Sitio Web se dispusiesen enlaces o hipervínculos hacia otros sitios de Internet, Agro365 no ejercerá ningún tipo de control sobre dichos sitios y contenidos. En ningún caso Agro365 asumirá responsabilidad alguna por los contenidos de algún enlace perteneciente a un sitio web ajeno.
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">8. Protección de Datos</h2>
                    <p class="text-gray-700 leading-relaxed">
                        Para más información sobre el tratamiento de datos personales, consulte nuestra 
                        <a href="{{ route('privacy') }}" class="text-[var(--color-agro-green-dark)] hover:underline font-semibold">Política de Privacidad</a>.
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">9. Legislación Aplicable y Jurisdicción</h2>
                    <p class="text-gray-700 leading-relaxed">
                        La relación entre Agro365 y el usuario se regirá por la normativa española vigente. Para la resolución de cualquier controversia las partes se someterán a los Juzgados y Tribunales del domicilio del usuario.
                    </p>
                </section>
            </div>

            <!-- Footer Links -->
            <div class="mt-8 flex flex-wrap gap-4 justify-center text-sm">
                <a href="{{ route('privacy') }}" class="text-[var(--color-agro-green-dark)] hover:underline">Política de Privacidad</a>
                <span class="text-gray-400">•</span>
                <a href="{{ route('terms') }}" class="text-[var(--color-agro-green-dark)] hover:underline">Términos y Condiciones</a>
                <span class="text-gray-400">•</span>
                <a href="{{ route('cookies') }}" class="text-[var(--color-agro-green-dark)] hover:underline">Política de Cookies</a>
            </div>
        </div>
    </div>
</body>
</html>
