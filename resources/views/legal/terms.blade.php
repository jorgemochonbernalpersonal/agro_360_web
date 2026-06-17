<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- SEO Meta Tags -->
    <title>Términos y Condiciones - Agro365 | Software de Gestión Agrícola</title>
    <meta name="description" content="Términos y condiciones de uso de Agro365 - Software de gestión agrícola para viticultores, bodegas y Denominaciones de Origen. Precios, uso del servicio y protección de datos.">
    <meta name="robots" content="index, follow">
    <meta name="author" content="Agro365">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="{{ url()->current() }}">
    
    <!-- Hreflang -->
    <link rel="alternate" hreflang="es" href="{{ url()->current() }}">
    <link rel="alternate" hreflang="es-ES" href="{{ url()->current() }}">
    <link rel="alternate" hreflang="x-default" href="{{ url()->current() }}">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="Términos y Condiciones - Agro365">
    <meta property="og:description" content="Términos y condiciones de uso de Agro365 - Software de gestión agrícola para viticultores en España.">
    <meta property="og:image" content="{{ asset('images/logo.png') }}">
    <meta property="og:locale" content="es_ES">
    <meta property="og:site_name" content="Agro365">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:url" content="{{ url()->current() }}">
    <meta name="twitter:title" content="Términos y Condiciones - Agro365">
    <meta name="twitter:description" content="Términos y condiciones de uso de Agro365 - Software de gestión agrícola para viticultores.">
    <meta name="twitter:image" content="{{ asset('images/logo.png') }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link rel="dns-prefetch" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-br from-[var(--color-agro-green-bg)] via-white to-[var(--color-agro-green-bright)]/30 min-h-screen">
    
    <!-- Navigation -->
    <nav class="glass-card border-b border-gray-200/50 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <a href="{{ url('/') }}" class="flex items-center">
                    <img 
                        src="{{ asset('images/logo.png') }}" 
                        alt="Agro365" 
                        width="200"
                        height="80"
                        loading="eager"
                        fetchpriority="high"
                        decoding="async"
                        class="h-20 w-auto object-contain"
                    >
                </a>
                <div class="flex items-center gap-4">
                    <a href="{{ route('login') }}" class="text-[var(--color-agro-green-dark)] hover:text-[var(--color-agro-green)] font-semibold transition-colors">
                        Iniciar Sesión
                    </a>
                    <a href="{{ route('register') }}" class="px-6 py-3 rounded-xl bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:from-[var(--color-agro-green)] hover:to-[var(--color-agro-green-dark)] transition-all shadow-lg hover:shadow-xl font-semibold">
                        Comenzar Gratis
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Content -->
    <main class="py-20">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="glass-card rounded-2xl p-8 lg:p-12">
                <h1 class="text-4xl font-bold text-[var(--color-agro-green-dark)] mb-4">{{ __('Términos y Condiciones') }}</h1>
                <p class="text-gray-500 mb-8">{{ __('Última actualización: 09/03/2026') }}</p>

                <div class="prose prose-lg max-w-none space-y-8">
                    <section>
                        <h2 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mb-4">{{ __('1. Aceptación de los Términos') }}</h2>
                        <p class="text-gray-700 leading-relaxed">{{ __('Al acceder y utilizar Agro365 ("el Servicio"), usted acepta estar sujeto a estos Términos y Condiciones. Si no está de acuerdo con alguno de estos términos, no debe utilizar el Servicio.') }}</p>
                        <p class="text-gray-700 leading-relaxed mt-2">{{ __('Agro365 es una plataforma de gestión agrícola diseñada para viticultores, bodegas y Denominaciones de Origen.') }}</p>
                    </section>

                    <section>
                        <h2 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mb-4">{{ __('2. Descripción del Servicio') }}</h2>
                        <p class="text-gray-700 leading-relaxed">{{ __('Agro365 ofrece herramientas de gestión agrícola digital incluyendo cuaderno de campo, gestión de parcelas SIGPAC, teledetección NDVI, facturación y trazabilidad vitivinícola. El servicio puede evolucionar con nuevas funcionalidades que serán comunicadas a los usuarios con antelación.') }}</p>
                    </section>

                    <section>
                        <h2 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mb-4">{{ __('3. Uso del Servicio') }}</h2>
                        <p class="text-gray-700 leading-relaxed mb-3">{{ __('Usted se compromete a:') }}</p>
                        <ul class="list-disc list-inside text-gray-700 space-y-2 ml-4">
                            <li>{{ __('Proporcionar información precisa y veraz durante el registro') }}</li>
                            <li>{{ __('Mantener la seguridad de su cuenta y contraseña') }}</li>
                            <li>{{ __('No compartir su cuenta con terceros') }}</li>
                            <li>{{ __('Utilizar el Servicio únicamente para fines legales y agrícolas') }}</li>
                            <li>{{ __('No intentar acceder a áreas restringidas del sistema') }}</li>
                        </ul>
                    </section>

                    <section>
                        <h2 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mb-4">{{ __('4. Propiedad Intelectual') }}</h2>
                        <p class="text-gray-700 leading-relaxed">{{ __('Todo el contenido del Servicio, incluyendo textos, gráficos, logos, iconos, imágenes, software y compilaciones de datos, es propiedad de Agro365 y está protegido por las leyes de propiedad intelectual.') }}</p>
                        <p class="text-gray-700 leading-relaxed mt-3">{{ __('Los datos que usted introduce (parcelas, actividades, cosechas, etc.) son de su propiedad y permanecerán confidenciales. Puede exportarlos en cualquier momento en formato estándar.') }}</p>
                    </section>

                    <section>
                        <h2 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mb-4">{{ __('5. Protección de Datos') }}</h2>
                        <p class="text-gray-700 leading-relaxed">
                            Nos comprometemos a proteger sus datos personales de acuerdo con el RGPD y la LOPDGDD. Para más información, consulte nuestra <a href="{{ route('privacy') }}" class="text-[var(--color-agro-green-dark)] underline hover:text-[var(--color-agro-green)]">Política de Privacidad</a>.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mb-4">{{ __('6. Limitación de Responsabilidad') }}</h2>
                        <p class="text-gray-700 leading-relaxed mb-3">{{ __('Agro365 se proporciona sin garantías de ningún tipo. No garantizamos que:') }}</p>
                        <ul class="list-disc list-inside text-gray-700 space-y-2 ml-4">
                            <li>{{ __('El servicio esté libre de errores o interrupciones') }}</li>
                            <li>{{ __('Los datos calculados (como rendimientos estimados) sean 100% precisos') }}</li>
                            <li>{{ __('El servicio cumpla con todos sus requisitos específicos') }}</li>
                        </ul>
                        <p class="text-gray-700 leading-relaxed mt-4">{{ __('Usted es responsable de mantener copias de seguridad de sus datos críticos.') }}</p>
                    </section>

                    <section>
                        <h2 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mb-4">{{ __('7. Precios y Pagos') }}</h2>
                        <p class="text-gray-700 leading-relaxed mb-3">{{ __('Los precios actuales son:') }}</p>
                        <ul class="list-disc list-inside text-gray-700 space-y-2 ml-4">
                            <li><strong>{{ __('Viticultor básico') }}</strong> (invitado por bodega): gratis</li>
                            <li><strong>{{ __('Viticultor completo') }}</strong> (invitado por bodega): 9€/mes o 85€/año</li>
                            <li><strong>{{ __('Viticultor independiente') }}</strong>: 14€/mes o 130€/año</li>
                            <li><strong>{{ __('Bodega dentro de una DO asociada') }}</strong>: gratis</li>
                            <li><strong>{{ __('Bodega independiente') }}</strong>: desde 19€/mes (escala con el número de viticultores gestionados)</li>
                            <li><strong>{{ __('Denominación de Origen') }}</strong>: desde 149€/mes según número de bodegas</li>
                        </ul>
                        <p class="text-gray-700 leading-relaxed mt-4">{{ __('Nos reservamos el derecho de modificar los precios con 30 días de aviso previo.') }}</p>
                    </section>

                    <section>
                        <h2 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mb-4">{{ __('8. Cancelación') }}</h2>
                        <p class="text-gray-700 leading-relaxed">{{ __('Puede cancelar su suscripción en cualquier momento desde su panel de control o contactando con nosotros. No se realizan reembolsos por períodos parciales ya pagados.') }}</p>
                    </section>

                    <section>
                        <h2 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mb-4">{{ __('9. Legislación Aplicable y Jurisdicción') }}</h2>
                        <p class="text-gray-700 leading-relaxed">{{ __('Estos términos se rigen por la legislación española vigente. Para la resolución de cualquier controversia, las partes se someterán a los Juzgados y Tribunales del domicilio del usuario.') }}</p>
                    </section>

                    <section>
                        <h2 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mb-4">{{ __('10. Modificaciones') }}</h2>
                        <p class="text-gray-700 leading-relaxed">{{ __('Nos reservamos el derecho de modificar estos términos en cualquier momento. Le notificaremos los cambios significativos por email al menos 15 días antes de que entren en vigor.') }}</p>
                    </section>

                    <section>
                        <h2 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mb-4">{{ __('11. Contacto') }}</h2>
                        <p class="text-gray-700 leading-relaxed">
                            Para cualquier duda sobre estos términos: 📧 <a href="mailto:info@agro365.es" class="text-[var(--color-agro-green-dark)] underline">info@agro365.es</a>
                        </p>
                    </section>
                </div>
            </div>
        </div>
    </main>

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
            "name": "Términos y Condiciones",
            "item": "{{ url()->current() }}"
        }]
    }
    </script>

    @include('partials.footer-seo')
</body>
</html>
