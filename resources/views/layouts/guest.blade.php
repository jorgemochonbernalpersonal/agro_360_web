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
    <title>@if(isset($title)){{ $title }} - Agro365 @else Agro365 @endif</title>

    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&display=swap" rel="stylesheet" />

    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>
<body class="bg-agro-50 min-h-screen">
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NGTPTZSQ" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>

    <main class="min-h-screen" id="main-content">
        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>
