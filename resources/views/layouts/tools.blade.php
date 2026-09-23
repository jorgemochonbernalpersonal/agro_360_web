<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') | Agro365</title>
    <meta name="description" content="@yield('description')">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="@yield('title') - Agro365">
    <meta property="og:description" content="@yield('description')">
    <meta property="og:image" content="{{ asset('images/dashboard-preview.png') }}">
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('schema')
</head>
<body class="font-sans antialiased bg-white">
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <a href="{{ url('/') }}" class="flex items-center gap-3">
                    <img src="{{ asset('images/logo.png') }}" alt="Agro365" class="h-10 w-auto">
                    <span class="text-xl font-bold text-[var(--color-agro-green-dark)]">Agro365</span>
                </a>
                <div class="flex items-center gap-4">
                    <a href="{{ route('tools.index') }}" class="hidden sm:inline text-sm font-medium text-gray-700 hover:text-[var(--color-agro-green-dark)]">Herramientas</a>
                    @guest
                        <a href="{{ route('register') }}" rel="nofollow" class="px-4 py-2 rounded-lg bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white text-sm">Comenzar gratis</a>
                    @endguest
                </div>
            </div>
        </nav>
    </header>

    <main class="min-h-screen bg-gradient-to-b from-white to-gray-50 py-12 sm:py-16">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav aria-label="Migas de pan" class="text-sm text-gray-500 mb-6">
                <a href="{{ url('/') }}" class="hover:text-gray-700">Inicio</a>
                <span class="mx-1">/</span>
                @hasSection('breadcrumb')
                    <a href="{{ route('tools.index') }}" class="hover:text-gray-700">Herramientas</a>
                    <span class="mx-1">/</span>
                    <span class="text-gray-900">@yield('breadcrumb')</span>
                @else
                    <span class="text-gray-900">Herramientas</span>
                @endif
            </nav>

            @yield('content')
        </div>
    </main>

    @include('partials.footer-seo')
    @livewireScripts
</body>
</html>
