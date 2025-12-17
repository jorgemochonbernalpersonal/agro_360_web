@php
    use App\Helpers\NavigationHelper;
    $user = auth()->user();
@endphp

<!-- Top Bar Premium -->
<header class="fixed top-0 right-0 left-0 lg:left-72 h-16 bg-white/95 backdrop-blur-md shadow-md border-b-2 border-[var(--color-agro-green-light)]/30 z-30 transition-all duration-300" id="top-bar">
    <div class="h-full flex items-center justify-between px-4 lg:px-8">
        <!-- Page Title -->
        <div class="flex-1">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-[var(--color-agro-green-light)]/20 to-[var(--color-agro-green)]/20 flex items-center justify-center hidden lg:flex">
                    @if(request()->routeIs('*.dashboard'))
                        <svg class="w-5 h-5 text-[var(--color-agro-green-dark)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    @elseif(request()->routeIs('plots.*'))
                        <svg class="w-5 h-5 text-[var(--color-agro-green-dark)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                        </svg>
                    @elseif(request()->routeIs('sigpac.*'))
                        <svg class="w-5 h-5 text-[var(--color-agro-green-dark)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    @elseif(request()->routeIs('config.*'))
                        <svg class="w-5 h-5 text-[var(--color-agro-green-dark)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    @elseif(request()->routeIs('profile.*'))
                        <svg class="w-5 h-5 text-[var(--color-agro-green-dark)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    @endif
                </div>
                <h1 class="text-lg lg:text-xl font-bold text-[var(--color-agro-green-dark)]">
                    @if(request()->routeIs('*.dashboard'))
                        Dashboard
                    @elseif(request()->routeIs('plots.*'))
                        Parcelas
                    @elseif(request()->routeIs('sigpac.*'))
                        SIGPACs
                    @elseif(request()->routeIs('config.*'))
                        Configuración
                    @elseif(request()->routeIs('profile.*'))
                        Mi Perfil
                    @else
                        Agro365
                    @endif
                </h1>
            </div>
        </div>

        <!-- User Menu -->
        <div class="flex items-center space-x-3 lg:space-x-6">
            <!-- Notifications (Future feature) -->
            <button class="relative p-2 rounded-lg text-gray-600 hover:bg-[var(--color-agro-green-bg)] transition-all duration-200 hidden lg:block">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <!-- Badge -->
                <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
            </button>

            <!-- User Info Desktop -->
            <div class="hidden md:flex items-center space-x-3 px-3 py-2 rounded-lg hover:bg-[var(--color-agro-green-bg)] transition-all duration-200 cursor-pointer">
                <div class="text-right">
                    <p class="text-sm font-semibold text-[var(--color-agro-green-dark)]">{{ $user->name }}</p>
                    <p class="text-xs text-gray-500">{{ NavigationHelper::getRoleName($user->role) }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)] flex items-center justify-center shadow-md">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
            </div>

            <!-- Mobile User Icon -->
            <div class="md:hidden w-10 h-10 rounded-full bg-gradient-to-br from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)] flex items-center justify-center shadow-md">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
        </div>
    </div>
</header>

<script>
    // Escuchar cambios en el sidebar para ajustar el top-bar
    if (typeof window.sidebarObserver === 'undefined') {
        window.sidebarObserver = setInterval(() => {
            const sidebar = document.getElementById('sidebar');
            const topBar = document.getElementById('top-bar');
            
            if (sidebar && topBar && window.innerWidth >= 1024) {
                const isCollapsed = sidebar.getAttribute('data-collapsed') === 'true';
                
                if (isCollapsed) {
                    topBar.classList.remove('lg:left-72');
                    topBar.classList.add('lg:left-20');
                } else {
                    topBar.classList.remove('lg:left-20');
                    topBar.classList.add('lg:left-72');
                }
            }
        }, 100);
    }
</script>
