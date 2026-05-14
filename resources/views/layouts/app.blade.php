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

        {{-- Impersonation banner --}}
        @if(session('impersonating'))
            <div class="fixed top-16 left-0 right-0 z-50 bg-amber-500 text-amber-950 shadow-md lg:left-16"
                 x-data="{ elapsed: '' }"
                 x-init="
                    const start = {{ session('impersonation_started_at', now()->timestamp) }};
                    setInterval(() => {
                        const diff = Math.floor(Date.now() / 1000) - start;
                        const m = Math.floor(diff / 60);
                        const s = diff % 60;
                        elapsed = m + ':' + String(s).padStart(2, '0');
                    }, 1000);
                 "
            >
                <div class="px-4 py-2 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3 min-w-0">
                        <flux:icon icon="eye" class="size-4 flex-shrink-0" />
                        <span class="text-sm font-medium truncate">
                            Viendo como <strong>{{ auth()->user()->name }}</strong>
                            <span class="hidden sm:inline text-amber-800">({{ auth()->user()->email }})</span>
                        </span>
                        <span class="text-xs font-mono bg-amber-600/30 rounded px-1.5 py-0.5 flex-shrink-0" x-text="elapsed"></span>
                    </div>
                    <form method="POST" action="{{ route('admin.users.stop-impersonate') }}">
                        @csrf
                        <button type="submit" class="flex items-center gap-1.5 px-3 py-1.5 text-sm font-semibold bg-white text-amber-900 rounded-lg hover:bg-amber-50 transition-colors shadow-sm">
                            <flux:icon icon="arrow-uturn-left" class="size-3.5" />
                            Volver a Admin
                        </button>
                    </form>
                </div>
            </div>
        @endif
    @endauth

    <main class="min-h-screen transition-all duration-300 @auth @if(session('impersonating')) pt-24 @else pt-16 @endif lg:pl-16 @endauth" id="main-content">
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
