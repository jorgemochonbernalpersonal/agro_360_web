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

        @if(!auth()->user()->isAdmin())
            @php
                $announcement = \Illuminate\Support\Facades\Schema::hasTable('admin_announcements')
                    ? \App\Models\AdminAnnouncement::visible()->latest()->first()
                    : null;
            @endphp
            @if($announcement)
                @php
                    $annColors = ['info' => ['bg' => 'bg-blue-50', 'border' => 'border-blue-200', 'text' => 'text-blue-800', 'btn' => 'text-blue-600 hover:text-blue-900'], 'warning' => ['bg' => 'bg-amber-50', 'border' => 'border-amber-200', 'text' => 'text-amber-800', 'btn' => 'text-amber-600 hover:text-amber-900'], 'success' => ['bg' => 'bg-green-50', 'border' => 'border-green-200', 'text' => 'text-green-800', 'btn' => 'text-green-600 hover:text-green-900'], 'danger' => ['bg' => 'bg-red-50', 'border' => 'border-red-200', 'text' => 'text-red-800', 'btn' => 'text-red-600 hover:text-red-900']];
                    $c = $annColors[$announcement->type] ?? $annColors['info'];
                @endphp
                <div x-data="{ open: true }" x-show="open" class="px-4 py-2.5 {{ $c['bg'] }} border-b {{ $c['border'] }} {{ $c['text'] }} flex items-center gap-3 text-sm">
                    <flux:icon icon="{{ $announcement->typeIcon() }}" class="size-4 flex-shrink-0" />
                    <div class="flex-1 min-w-0">
                        <span class="font-semibold">{{ $announcement->title }}:</span>
                        {{ $announcement->message }}
                    </div>
                    <button @click="open = false" class="{{ $c['btn'] }} flex-shrink-0">
                        <flux:icon icon="x-mark" class="size-4" />
                    </button>
                </div>
            @endif
        @endif

        @if(auth()->check() && !auth()->user()->hasVerifiedEmail() && !auth()->user()->isAdmin())
            <div
                x-data="{ open: true }"
                x-show="open"
                x-cloak
                class="px-4 py-2.5 bg-amber-50 border-b border-amber-200 text-amber-800 flex items-center gap-3 text-sm"
            >
                <flux:icon icon="envelope" class="size-4 flex-shrink-0" />
                <div class="flex-1 min-w-0">
                    <span class="font-semibold">{{ __('Verifica tu email') }}</span>
                    — {{ __('Hemos enviado un enlace a') }} <strong>{{ auth()->user()->email }}</strong>.
                    {{ __('Haz clic en él para activar tu cuenta completamente.') }}
                </div>
                <form method="POST" action="{{ route('verification.send') }}" class="flex-shrink-0">
                    @csrf
                    <button type="submit" class="text-amber-700 hover:text-amber-900 underline font-medium text-xs">
                        {{ __('Reenviar email') }}
                    </button>
                </form>
                <button @click="open = false" class="text-amber-600 hover:text-amber-900 flex-shrink-0 ml-1">
                    <flux:icon icon="x-mark" class="size-4" />
                </button>
            </div>
        @endif

        @if(session('impersonating'))
            @php
                $startedAt = session('impersonation_started_at');
                $elapsed = $startedAt ? round((now()->timestamp - $startedAt) / 60) : 0;
                $remaining = 60 - $elapsed;
            @endphp
            <div class="fixed top-0 left-0 right-0 z-[9999] bg-amber-500 text-white text-xs font-semibold text-center py-1.5 px-4 flex items-center justify-center gap-3">
                <flux:icon icon="eye" class="size-3.5 shrink-0" />
                <span>{{ __('Impersonando a') }} <strong>{{ auth()->user()->name }}</strong> ({{ auth()->user()->role }}) — {{ $remaining }} {{ __('min restantes') }}</span>
                <form method="POST" action="{{ route('admin.users.stop-impersonate') }}">
                    @csrf
                    <button type="submit" class="underline hover:no-underline ml-2">{{ __('Salir') }}</button>
                </form>
            </div>
        @endif
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
