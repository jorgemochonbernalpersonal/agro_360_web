<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <!-- SEO Meta Tags -->
    <title>Aviso Legal - Agro365 | Software de Gestión Agrícola</title>
    <meta name="description" content="Aviso legal de Agro365 - Software de gestión agrícola para viñedos y bodegas. Información legal, condiciones de uso y propiedad intelectual.">
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
    <meta property="og:title" content="Aviso Legal - Agro365">
    <meta property="og:description" content="Aviso legal de Agro365 - Software de gestión agrícola para viñedos y bodegas en España.">
    <meta property="og:image" content="{{ asset('images/logo.png') }}">
    <meta property="og:locale" content="es_ES">
    <meta property="og:site_name" content="Agro365">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:url" content="{{ url()->current() }}">
    <meta name="twitter:title" content="Aviso Legal - Agro365">
    <meta name="twitter:description" content="Aviso legal de Agro365 - Software de gestión agrícola para viñedos y bodegas.">
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
                <h1 class="text-4xl font-bold text-gray-900">{{ __('Aviso Legal') }}</h1>
                <p class="mt-2 text-gray-600">{{ __('Última actualización: 09/03/2026') }}</p>
            </div>

            <!-- Content -->
            <div class="bg-white rounded-lg shadow-sm p-8 space-y-6">
                <section>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('1. Datos Identificativos') }}</h2>
                    <p class="text-gray-700 leading-relaxed">{{ __('En cumplimiento del artículo 10 de la Ley 34/2002, de 11 de julio, de Servicios de la Sociedad de la Información y Comercio Electrónico, se informa a los usuarios de los datos identificativos del titular del sitio web:') }}</p>
                    <ul class="mt-4 space-y-2 text-gray-700">
                        <li><strong>{{ __('Denominación social / nombre comercial:') }}</strong> {{ config('app.legal_owner_name') ?: __('Pendiente de formalización del alta') }}</li>
                        <li><strong>{{ __('NIF/CIF:') }}</strong> {{ config('app.legal_owner_dni') ?: __('Pendiente de formalización del alta') }}</li>
                        <li><strong>{{ __('Domicilio:') }}</strong> {{ config('app.legal_owner_address') ?: __('Pendiente de formalización del alta') }}</li>
                        <li><strong>{{ __('Dominio:') }}</strong> agro365.es</li>
                        <li><strong>{{ __('Email de contacto:') }}</strong> <a href="mailto:info@agro365.es" class="text-[var(--color-agro-green-dark)] hover:underline">info@agro365.es</a></li>
                        <li><strong>{{ __('Teléfono de contacto:') }}</strong> <a href="tel:+34684217167" class="text-[var(--color-agro-green-dark)] hover:underline">+34 684 217 167</a></li>
                        <li><strong>{{ __('Actividad:') }}</strong> Software de gestión agrícola</li>
                    </ul>
                    @unless(config('app.legal_owner_name'))
                        <p class="text-amber-600 text-sm mt-3 italic">{{ __('El titular aún no ha formalizado su alta como autónomo o sociedad. Estos datos se completarán en cuanto se constituya la actividad.') }}</p>
                    @endunless
                </section>

                <section>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('2. Objeto') }}</h2>
                    <p class="text-gray-700 leading-relaxed">
                        El presente aviso legal regula el uso del sitio web <strong>{{ __('agro365.es') }}</strong> (en adelante, el "Sitio Web"), del que es titular Agro365. La navegación por el Sitio Web atribuye la condición de usuario del mismo e implica la aceptación plena y sin reservas de todas y cada una de las disposiciones incluidas en este Aviso Legal.
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('3. Condiciones de Uso') }}</h2>
                    <p class="text-gray-700 leading-relaxed">{{ __('El acceso y uso del Sitio Web se rige por las siguientes condiciones:') }}</p>
                    <ul class="mt-4 space-y-2 text-gray-700 list-disc list-inside">
                        <li>{{ __('El uso del Sitio Web es responsabilidad exclusiva del usuario.') }}</li>
                        <li>{{ __('El usuario se compromete a hacer un uso adecuado de los contenidos y servicios.') }}</li>
                        <li>{{ __('Queda prohibido el uso del Sitio Web con fines ilícitos o lesivos.') }}</li>
                        <li>{{ __('El usuario no podrá realizar actividades publicitarias o de explotación comercial sin autorización previa.') }}</li>
                        <li>{{ __('Agro365 se reserva el derecho de denegar o retirar el acceso al Sitio Web a aquellos usuarios que incumplan las presentes condiciones.') }}</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('4. Propiedad Intelectual e Industrial') }}</h2>
                    <p class="text-gray-700 leading-relaxed">{{ __('Todos los contenidos del Sitio Web, incluyendo textos, fotografías, gráficos, imágenes, iconos, tecnología, software, así como su diseño gráfico y códigos fuente, constituyen una obra cuya propiedad pertenece a Agro365, sin que puedan entenderse cedidos al usuario ninguno de los derechos de explotación sobre los mismos.') }}</p>
                    <p class="text-gray-700 leading-relaxed mt-3">{{ __('El usuario se compromete a respetar los derechos de propiedad intelectual e industrial titularidad de Agro365. Cualquier reproducción, distribución, comunicación pública o transformación de los contenidos del Sitio Web requerirá la autorización expresa y por escrito de Agro365.') }}</p>
                </section>

                <section>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('5. Exclusión de Garantías y Responsabilidad') }}</h2>
                    <p class="text-gray-700 leading-relaxed">{{ __('Agro365 no se hace responsable, en ningún caso, de los daños y perjuicios de cualquier naturaleza que pudieran ocasionar, a título enunciativo:') }}</p>
                    <ul class="mt-4 space-y-2 text-gray-700 list-disc list-inside">
                        <li>{{ __('Errores u omisiones en los contenidos.') }}</li>
                        <li>{{ __('Falta de disponibilidad del portal.') }}</li>
                        <li>{{ __('Transmisión de virus o programas maliciosos en los contenidos.') }}</li>
                        <li>{{ __('El uso que los usuarios hagan de los contenidos del Sitio Web.') }}</li>
                        <li>{{ __('La falta de veracidad, exactitud o actualización de los contenidos.') }}</li>
                    </ul>
                    <p class="text-gray-700 leading-relaxed mt-3">{{ __('Agro365 no garantiza la disponibilidad y continuidad del funcionamiento del Sitio Web, aunque realizará sus mejores esfuerzos para mantener el servicio operativo.') }}</p>
                </section>

                <section>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('6. Modificaciones') }}</h2>
                    <p class="text-gray-700 leading-relaxed">{{ __('Agro365 se reserva el derecho de efectuar sin previo aviso las modificaciones que considere oportunas en su portal, pudiendo cambiar, suprimir o añadir tanto los contenidos y servicios que se presten a través de la misma como la forma en la que éstos aparezcan presentados o localizados. La fecha de última actualización indicada al inicio del presente documento refleja la versión vigente en cada momento.') }}</p>
                </section>

                <section>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('7. Enlaces') }}</h2>
                    <p class="text-gray-700 leading-relaxed">{{ __('En el caso de que en el Sitio Web se dispusiesen enlaces o hipervínculos hacia otros sitios de Internet, Agro365 no ejercerá ningún tipo de control sobre dichos sitios y contenidos. En ningún caso Agro365 asumirá responsabilidad alguna por los contenidos de algún enlace perteneciente a un sitio web ajeno, ni garantizará la disponibilidad técnica, calidad, fiabilidad, exactitud, amplitud, veracidad, validez y constitucionalidad de cualquier material o información contenida en ninguno de dichos hipervínculos u otros sitios de Internet.') }}</p>
                </section>

                <section>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('8. Protección de Datos') }}</h2>
                    <p class="text-gray-700 leading-relaxed">
                        En cumplimiento del Reglamento (UE) 2016/679 del Parlamento Europeo y del Consejo (RGPD) y de la Ley Orgánica 3/2018, de 5 de diciembre, de Protección de Datos Personales y garantía de los derechos digitales (LOPDGDD), Agro365 trata los datos personales de los usuarios conforme a lo establecido en la
                        <a href="{{ route('privacy') }}" class="text-[var(--color-agro-green-dark)] hover:underline font-semibold">Política de Privacidad</a>
                        disponible en el Sitio Web. Para más información o para ejercer sus derechos, el usuario puede dirigirse a <a href="mailto:info@agro365.es" class="text-[var(--color-agro-green-dark)] hover:underline">info@agro365.es</a>.
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('9. Cookies') }}</h2>
                    <p class="text-gray-700 leading-relaxed">
                        El Sitio Web puede utilizar cookies propias y de terceros con finalidades técnicas, analíticas y de personalización. Para más información consulte nuestra
                        <a href="{{ route('cookies') }}" class="text-[var(--color-agro-green-dark)] hover:underline font-semibold">Política de Cookies</a>
                        disponible en el Sitio Web.
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('10. Legislación Aplicable y Jurisdicción') }}</h2>
                    <p class="text-gray-700 leading-relaxed">{{ __('La relación entre Agro365 y el usuario se regirá por la normativa española vigente. Para la resolución de cualquier controversia las partes se someterán a los Juzgados y Tribunales del domicilio del usuario, salvo que la legislación aplicable establezca un fuero distinto de carácter imperativo.') }}</p>
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
            "name": "Aviso Legal",
            "item": "{{ url()->current() }}"
        }]
    }
    </script>

    @include('partials.footer-seo')
</body>
</html>
