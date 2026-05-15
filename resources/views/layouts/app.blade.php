<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-flux-appearance="light">
<head>
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-NGTPTZSQ');</script>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @php
        $appUrl = config('app.url');
        $currentUrl = $appUrl . request()->getRequestUri();
        $currentPath = request()->path();
        $pageTitle = $title ?? 'Agro365 - Software de Gestión Agrícola para Viñedos';
        $pageDescription = $description ?? \App\Helpers\SeoHelper::getMetaDescription('/' . $currentPath);
        $pageImage = $image ?? asset('images/logo.png');
    @endphp

    <link rel="alternate" hreflang="es" href="{{ $currentUrl }}">
    <link rel="alternate" hreflang="es-ES" href="{{ $currentUrl }}">
    <link rel="alternate" hreflang="x-default" href="{{ $currentUrl }}">

    <meta name="author" content="Agro365">
    <meta name="theme-color" content="#4a7c59">

    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDescription }}">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ $currentUrl }}">

    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ $currentUrl }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:image" content="{{ $pageImage }}">
    <meta property="og:locale" content="es_ES">
    <meta property="og:site_name" content="Agro365">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $pageDescription }}">
    <meta name="twitter:image" content="{{ $pageImage }}">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600&display=swap" rel="stylesheet" />

    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-zinc-50 min-h-screen">
    <!-- GTM noscript -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NGTPTZSQ" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>

    @auth
        <x-sidebar />
        <x-top-bar />

    @endauth

    <main class="min-h-screen transition-all duration-300 @auth pt-16 lg:pl-16 @endauth" id="main-content">
        <div class="@auth p-4 lg:p-8 @else p-0 @endauth">
            {{ $slot }}
        </div>
    </main>

    @livewireScripts
    @fluxScripts

    {{-- Pass session flash data to toast.js --}}
    <script>
        window.__agro_flashes = {
            message: @json(session('message')),
            error: @json(session('error')),
            info: @json(session('info')),
            warning: @json(session('warning')),
        };
    </script>

    <x-agro.toast />

    @stack('scripts')
</body>
</html>
