<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Novedades PAC 2026: Cuaderno Digital, Fertilización y Condicionalidad | Blog Agro365</title>
    <meta name="description" content="Las novedades de la PAC 2026 para viticultores: registro obligatorio de fertilización, antesala del cuaderno digital de 2027, condicionalidad y eco-esquemas.">
    <meta name="keywords" content="PAC 2026, novedades PAC, registro fertilización 2026, cuaderno digital 2027, condicionalidad PAC, eco-esquemas viñedo">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url('/blog/novedades-pac-2026') }}">
    <meta property="og:title" content="Novedades PAC 2026 - Blog Agro365">
    <meta property="og:description" content="Registro de fertilización obligatorio, antesala del cuaderno digital de 2027, condicionalidad y eco-esquemas para el viñedo.">
    <meta property="og:type" content="article">
    <meta property="og:image" content="{{ asset('images/dashboard-preview.png') }}">
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
                    <span class="text-xl font-bold text-[var(--color-agro-green-dark)]">{{ __('Agro365') }}</span>
                </a>
                @guest
                    <a href="{{ route('register') }}" class="px-4 py-2 rounded-lg bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white">Comenzar Gratis</a>
                @endguest
            </div>
        </nav>
    </header>

    <div class="min-h-screen bg-gradient-to-b from-white to-gray-50 py-20">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="text-sm text-gray-500 mb-8">
                <a href="{{ url('/blog') }}" class="hover:text-[var(--color-agro-green)]">Blog</a> &rarr;
                <span>{{ __('Novedades PAC 2026') }}</span>
            </nav>

            <article class="prose prose-lg max-w-none">
                <div class="mb-8">
                    <span class="text-sm text-gray-500"><span class="font-medium text-gray-700">{{ __('Equipo Agro365') }}</span> · {{ __('Junio 2026') }}</span>
                    <h1 class="text-4xl lg:text-5xl font-bold text-[var(--color-agro-green-dark)] mt-2">{{ __('Novedades PAC 2026: Cuaderno Digital, Fertilización y Condicionalidad') }}</h1>
                </div>

                <p class="text-xl text-gray-600 leading-relaxed mb-8">
                    {{ __('2026 es el año de la antesala: entra en vigor el registro obligatorio de la fertilización y se prepara el terreno para el salto definitivo al cuaderno digital de 2027. Repasamos lo que cambia para el viticultor.') }}
                </p>

                <h2 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mt-8 mb-4">{{ __('1. Registro obligatorio de fertilización') }}</h2>
                <p class="text-gray-700 leading-relaxed mb-4">
                    {{ __('Desde el 1 de enero de 2026 es obligatorio anotar cada aplicación de nutrientes y materia orgánica en el cuaderno de explotación. Todavía se admite papel, pero el digital ya simplifica el trabajo. Afecta a explotaciones con más de 5 ha de cultivos permanentes y tierras de cultivo, o más de 1 ha de regadío.') }}
                </p>

                <h2 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mt-8 mb-4">{{ __('2. Cuenta atrás para el cuaderno digital') }}</h2>
                <p class="text-gray-700 leading-relaxed mb-4">
                    {{ __('La gran fecha es el 1 de enero de 2027, cuando el registro de fitosanitarios deberá hacerse en formato electrónico interoperable. 2026 es el momento de rodar el sistema.') }}
                    {{ __('Lo contamos en detalle en') }} <a href="{{ url('/cuaderno-campo-digital-2027') }}" class="text-[var(--color-agro-green)] hover:underline">{{ __('la guía del Cuaderno Digital Obligatorio 2027') }}</a>.
                </p>

                <h2 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mt-8 mb-4">{{ __('3. Condicionalidad reforzada') }}</h2>
                <p class="text-gray-700 leading-relaxed mb-4">{{ __('Los requisitos de condicionalidad siguen vigentes y se vigilan más de cerca. Para el viñedo, los más relevantes:') }}</p>
                <ul class="list-disc list-inside space-y-2 text-gray-700 mb-6 ml-4">
                    <li>{{ __('BCAM 4: franjas de protección en cursos de agua') }}</li>
                    <li>{{ __('BCAM 6: cubierta del suelo en periodos sensibles') }}</li>
                    <li>{{ __('BCAM 8: superficies y elementos no productivos') }}</li>
                    <li>Vinculación de todas las labores a la parcela <a href="{{ content_route('que-es-sigpac') }}" class="text-[var(--color-agro-green)] hover:underline">SIGPAC</a></li>
                </ul>

                <h2 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mt-8 mb-4">{{ __('4. Eco-esquemas para viñedo') }}</h2>
                <ul class="list-disc list-inside space-y-2 text-gray-700 mb-6 ml-4">
                    <li>{{ __('Cubiertas vegetales espontáneas o sembradas') }}</li>
                    <li>{{ __('Reducción de insumos y agroecología') }}</li>
                    <li>{{ __('Agricultura de precisión y teledetección') }}</li>
                </ul>

                <h2 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mt-8 mb-4">{{ __('¿Cómo prepararte?') }}</h2>
                <p class="text-gray-700 leading-relaxed mb-4">{{ __('Con Agro365 cumples los requisitos de 2026 y llegas listo a 2027:') }}</p>
                <ul class="list-disc list-inside space-y-2 text-gray-700 mb-6 ml-4">
                    <li><a href="{{ content_route('cuaderno-digital-viticultores') }}" class="text-[var(--color-agro-green)] hover:underline">Cuaderno digital</a> con registro de fertilización y fitosanitarios</li>
                    <li>{{ __('Dashboard de cumplimiento PAC en tiempo real') }}</li>
                    <li>{{ __('Alertas de incumplimientos y plazos') }}</li>
                    <li><a href="{{ content_route('registro-fitosanitarios') }}" class="text-[var(--color-agro-green)] hover:underline">Registro de fitosanitarios</a> listo para el formato electrónico de 2027</li>
                </ul>

                <div class="bg-gradient-to-r from-[var(--color-agro-green-bg)] to-[var(--color-agro-green-light)]/30 p-8 rounded-xl border border-[var(--color-agro-green)]/20 mt-12">
                    <h3 class="text-xl font-bold text-[var(--color-agro-green-dark)] mb-4">{{ __('📋 Prepárate para la PAC 2026') }}</h3>
                    <p class="text-gray-700 mb-6">
                        Cuaderno digital, cumplimiento PAC e informes oficiales. <strong>{{ __('Viticultor básico gratis para siempre') }}</strong>.
                    </p>
                    <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-lg bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:shadow-lg transition-all font-semibold">
                        Comenzar Gratis
                    </a>
                </div>
            </article>

            <div class="mt-12 pt-8 border-t border-gray-200">
                <a href="{{ url('/blog') }}" class="text-[var(--color-agro-green)] font-semibold hover:underline">&larr; Volver al Blog</a>
            </div>
        </div>
    </div>
    @include('partials.footer-seo')

    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "BlogPosting",
        "headline": "Novedades PAC 2026: Cuaderno Digital, Fertilización y Condicionalidad",
        "description": "Las novedades de la PAC 2026 para viticultores: registro obligatorio de fertilización, antesala del cuaderno digital de 2027, condicionalidad y eco-esquemas.",
        "image": "{{ asset('images/dashboard-preview.png') }}",
        "url": "{{ url('/blog/novedades-pac-2026') }}",
        "datePublished": "2026-06-03",
        "dateModified": "2026-06-03",
        "author": {"@@type": "Organization", "name": "Agro365", "url": "{{ url('/') }}"},
        "publisher": {"@@type": "Organization", "name": "Agro365", "logo": {"@@type": "ImageObject", "url": "{{ asset('images/logo.png') }}"}}
    }
    </script>

    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "BreadcrumbList",
        "itemListElement": [
            {"@@type": "ListItem", "position": 1, "name": "Inicio", "item": "{{ url('/') }}"},
            {"@@type": "ListItem", "position": 2, "name": "Blog", "item": "{{ url('/blog') }}"},
            {"@@type": "ListItem", "position": 3, "name": "Novedades PAC 2026", "item": "{{ url('/blog/novedades-pac-2026') }}"}
        ]
    }
    </script>
</body>
</html>
