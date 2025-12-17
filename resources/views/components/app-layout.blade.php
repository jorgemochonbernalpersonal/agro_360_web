<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Agro365</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/logo.png') }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    
    <!-- Styles / Scripts -->
    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-br from-[var(--color-agro-green-bg)] via-white to-[var(--color-agro-green-bright)]/30 min-h-screen">
    @auth
        <!-- Sidebar -->
        <x-sidebar />
        
        <!-- Top Bar -->
        <x-top-bar />
    @endauth
    
    <!-- Main Content -->
    <main class="min-h-screen pt-16 lg:pl-72 transition-all duration-300" id="main-content">
        <div class="p-4 lg:p-8">
            {{ $slot }}
        </div>
    </main>
    
    @livewireScripts
</body>
</html>
