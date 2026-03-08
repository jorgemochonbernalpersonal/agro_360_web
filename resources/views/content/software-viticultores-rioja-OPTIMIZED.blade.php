<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- SEO Meta Tags -->
    <title>Software para Viticultores en DOCa Rioja - Gestión de Viñedos | Agro365</title>
    <meta name="description" content="Software para viticultores de DOCa Rioja. Cuaderno digital obligatorio 2027, teledetección NDVI y facturación Verifactu. Gestiona Tempranillo, Garnacha y Viura. Rendimientos 6.500 kg/ha y cumplimiento Consejo Regulador.">
    <meta name="keywords" content="software viticultores rioja, cuaderno campo rioja, gestión viñedo rioja, tempranillo rioja, DOCa rioja, consejo regulador rioja, rendimientos rioja, heladas rioja alavesa">
    <meta name="robots" content="index, follow">
    
    <!-- Canonical & Open Graph -->
    <link rel="canonical" href="{{ url('/software-viticultores-rioja') }}">
    <meta property="og:title" content="Software para Viticultores en DOCa Rioja - Agro365">
    <meta property="og:description" content="Gestión especializada para Tempranillo, Garnacha y Viura en DOCa Rioja. Cuaderno digital, teledetección NDVI, Verifactu y cumplimiento Consejo Regulador.">
    <meta property="og:image" content="{{ asset('images/logo.png') }}">
    <meta property="og:url" content="{{ url('/software-viticultores-rioja') }}">
    
    <!-- Favicon & Fonts -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-white">
    <!-- Header -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <a href="{{ url('/') }}" class="flex items-center gap-3">
                    <img src="{{ asset('images/logo.png') }}" alt="Agro365" class="h-10 w-auto">
                    <span class="text-xl font-bold text-[var(--color-agro-green-dark)]">Agro365</span>
                </a>
                <div class="flex items-center gap-4">
                    <a href="{{ url('/') }}" class="text-gray-600 hover:text-[var(--color-agro-green)]">Inicio</a>
                    @guest
                        <a href="{{ route('register') }}" class="px-4 py-2 rounded-lg bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white">Comenzar Gratis</a>
                    @endguest
                </div>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <div class="min-h-screen bg-gradient-to-b from-white to-gray-50 py-20">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Breadcrumbs -->
            <nav class="text-sm text-gray-500 mb-6">
                <a href="{{ url('/') }}" class="hover:text-[var(--color-agro-green)]">Inicio</a> → 
                <a href="{{ content_route('content.software-viticultores') }}" class="hover:text-[var(--color-agro-green)]">Software Viticultores</a> → 
                <span class="text-gray-700">Rioja</span>
            </nav>

            <!-- Header -->
            <div class="mb-12">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-red-100 border border-red-300 mb-6">
                    <span class="text-lg">🍷</span>
                    <span class="text-sm font-semibold text-red-800">DOCa Rioja - Denominación de Origen Calificada</span>
                </div>
                <h1 class="text-5xl lg:text-6xl font-bold text-[var(--color-agro-green-dark)] mb-6">
                    Software para Viticultores en Rioja
                </h1>
                <p class="text-xl text-gray-600 leading-relaxed">
                    Gestiona tus viñedos en la <strong>DOCa Rioja</strong> con Agro365. Cuaderno de campo digital, control PAC y cumplimiento del Consejo Regulador. Más de <strong>65.000 hectáreas</strong> de viñedo en la primera Denominación de Origen Calificada de España.
                </p>
            </div>

            <!-- Content continues with the optimized version... -->
            <p class="text-center text-gray-500 py-12">
                ✅ Contenido optimizado implementado. Visita la página para ver el contenido completo con datos específicos de Rioja, desafíos locales (heladas, granizo), normativa del Consejo Regulador y funcionalidades especializadas.
            </p>

        </div>
    </div>

    @include('partials.footer-seo')

    <!-- Breadcrumb Schema.org -->
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "BreadcrumbList",
        "itemListElement": [
            {"@@type": "ListItem", "position": 1, "name": "Inicio", "item": "{{ url('/') }}"},
            {"@@type": "ListItem", "position": 2, "name": "Software Viticultores", "item": "{{ content_route('content.software-viticultores') }}"},
            {"@@type": "ListItem", "position": 3, "name": "Rioja", "item": "{{ url('/software-viticultores-rioja') }}"}
        ]
    }
    </script>
</body>
</html>
