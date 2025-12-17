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
    <main class="min-h-screen transition-all duration-300 @auth pt-16 lg:pl-72 @endauth" id="main-content">
        <div class="@auth p-4 lg:p-8 @else p-0 @endauth">
            {{ $slot }}
        </div>
    </main>
    
    @livewireScripts
    
    <!-- Script para ajustar el main con el sidebar colapsable -->
    <script>
        if (typeof window.mainContentObserver === 'undefined') {
            window.mainContentObserver = setInterval(() => {
                const sidebar = document.getElementById('sidebar');
                const mainContent = document.getElementById('main-content');
                
                if (sidebar && mainContent && window.innerWidth >= 1024) {
                    const isCollapsed = sidebar.getAttribute('data-collapsed') === 'true';
                    
                    if (isCollapsed) {
                        mainContent.classList.remove('lg:pl-72');
                        mainContent.classList.add('lg:pl-20');
                    } else {
                        mainContent.classList.remove('lg:pl-20');
                        mainContent.classList.add('lg:pl-72');
                    }
                }
            }, 100);
        }
    </script>
</body>
</html>

