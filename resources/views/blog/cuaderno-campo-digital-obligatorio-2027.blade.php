<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cuaderno Digital Obligatorio 2027: Fechas, Quién Está Obligado y Cómo Cumplir | Blog Agro365</title>
    <meta name="description" content="El cuaderno de campo digital será obligatorio el 1 de enero de 2027 para el registro electrónico de fitosanitarios. Te explicamos las fechas, a quién afecta y cómo cumplir sin papel.">
    <meta name="keywords" content="cuaderno digital obligatorio 2027, cuaderno de campo digital, registro electrónico fitosanitarios, CUE, RD 1054/2022, normativa PAC 2027, cuaderno explotación agrícola">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url('/blog/cuaderno-campo-digital-obligatorio-2027') }}">
    <meta property="og:title" content="Cuaderno Digital Obligatorio 2027 - Blog Agro365">
    <meta property="og:description" content="Fechas confirmadas, a quién afecta y cómo cumplir con el cuaderno de campo digital obligatorio en 2027.">
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
                <span>{{ __('Cuaderno Digital Obligatorio 2027') }}</span>
            </nav>

            <article class="prose prose-lg max-w-none">
                <div class="mb-8">
                    <span class="text-sm text-gray-500">{{ __('Junio 2026') }}</span>
                    <h1 class="text-4xl lg:text-5xl font-bold text-[var(--color-agro-green-dark)] mt-2">{{ __('Cuaderno Digital Obligatorio 2027: Fechas, Quién Está Obligado y Cómo Cumplir') }}</h1>
                </div>

                <p class="text-xl text-gray-600 leading-relaxed mb-8">
                    El <strong>{{ __('1 de enero de 2027') }}</strong> el registro de productos fitosanitarios deberá hacerse en <strong>{{ __('formato electrónico interoperable') }}</strong>. Es decir: el <a href="{{ content_route('cuaderno-digital-viticultores') }}" class="text-[var(--color-agro-green)] hover:underline">cuaderno de campo digital</a> deja de ser una opción y pasa a ser una obligación. Te lo resumimos sin tecnicismos.
                </p>

                <h2 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mt-8 mb-4">{{ __('Las fechas que debes marcar en el calendario') }}</h2>
                <p class="text-gray-700 leading-relaxed mb-4">
                    La normativa no entra de golpe, sino en <strong>{{ __('dos fases') }}</strong> definidas por el Real Decreto 1054/2022 (modificado por el RD 34/2025):
                </p>
                <ul class="list-disc list-inside space-y-2 text-gray-700 mb-6 ml-4">
                    <li><strong>{{ __('1 de enero de 2026') }}</strong> &mdash; {{ __('obligatorio registrar la fertilización (nutrientes y materia orgánica) en el cuaderno de explotación. Aún se admite papel o formato digital.') }}</li>
                    <li><strong>{{ __('1 de enero de 2027') }}</strong> &mdash; {{ __('los registros de productos fitosanitarios deben realizarse en formato electrónico interoperable. Aquí el cuaderno digital pasa a ser obligatorio de facto.') }}</li>
                </ul>

                <h2 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mt-8 mb-4">{{ __('¿A quién afecta?') }}</h2>
                <p class="text-gray-700 leading-relaxed mb-4">{{ __('Depende de la fase:') }}</p>
                <ul class="list-disc list-inside space-y-2 text-gray-700 mb-6 ml-4">
                    <li>{{ __('Registro de fertilización (2026): explotaciones con más de 5 ha de cultivos permanentes y tierras de cultivo (sin contar pastos temporales), o más de 1 ha de regadío.') }}</li>
                    <li>{{ __('Registro electrónico de fitosanitarios (2027): cualquier usuario profesional que aplique productos fitosanitarios, independientemente del tamaño de la explotación.') }}</li>
                </ul>
                <p class="text-gray-700 leading-relaxed mb-4">
                    {{ __('Traducido al viñedo: si tratas con fitosanitarios —y prácticamente toda explotación vitícola lo hace— en 2027 estarás obligado a llevar el registro en digital.') }}
                </p>

                <h2 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mt-8 mb-4">{{ __('Qué debe registrar el cuaderno digital') }}</h2>
                <ul class="list-disc list-inside space-y-2 text-gray-700 mb-6 ml-4">
                    <li><a href="{{ content_route('registro-fitosanitarios') }}" class="text-[var(--color-agro-green)] hover:underline">Tratamientos fitosanitarios</a>: producto, dosis, fecha, plaga y plazo de seguridad</li>
                    <li>{{ __('Fertilización: tipo, cantidad y fecha de aplicación') }}</li>
                    <li>Vinculación de cada labor a la parcela <a href="{{ content_route('que-es-sigpac') }}" class="text-[var(--color-agro-green)] hover:underline">SIGPAC</a> correspondiente</li>
                    <li>{{ __('Plazo de registro tras la actividad y trazabilidad completa') }}</li>
                </ul>

                <h2 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mt-8 mb-4">{{ __('No esperes a diciembre de 2026') }}</h2>
                <p class="text-gray-700 leading-relaxed mb-4">
                    {{ __('El error más común es dejarlo para el final. El registro digital exige tener las parcelas dadas de alta, los tratamientos históricos ordenados y el flujo de trabajo rodado. Empezar con la campaña de 2026 te da margen para llegar a 2027 sin sustos.') }}
                    {{ __('Puedes leer más en nuestra guía completa del') }} <a href="{{ content_route('cuaderno-campo-digital-2027') }}" class="text-[var(--color-agro-green)] hover:underline">{{ __('cuaderno de campo digital 2027') }}</a>.
                </p>

                <div class="bg-gradient-to-r from-[var(--color-agro-green-bg)] to-[var(--color-agro-green-light)]/30 p-8 rounded-xl border border-[var(--color-agro-green)]/20 mt-12">
                    <h3 class="text-xl font-bold text-[var(--color-agro-green-dark)] mb-4">{{ __('📋 Llega a 2027 preparado') }}</h3>
                    <p class="text-gray-700 mb-6">
                        Cuaderno digital, parcelas SIGPAC y registro de fitosanitarios en una sola plataforma. <strong>{{ __('Viticultor básico gratis para siempre') }}</strong>.
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
        "headline": "Cuaderno Digital Obligatorio 2027: Fechas, Quién Está Obligado y Cómo Cumplir",
        "description": "El cuaderno de campo digital será obligatorio el 1 de enero de 2027 para el registro electrónico de fitosanitarios. Fechas, a quién afecta y cómo cumplir sin papel.",
        "image": "{{ asset('images/dashboard-preview.png') }}",
        "url": "{{ url('/blog/cuaderno-campo-digital-obligatorio-2027') }}",
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
            {"@@type": "ListItem", "position": 3, "name": "Cuaderno Digital Obligatorio 2027", "item": "{{ url('/blog/cuaderno-campo-digital-obligatorio-2027') }}"}
        ]
    }
    </script>
</body>
</html>
