@php
    use App\Helpers\NavigationHelper;
    $menu = NavigationHelper::getMenu();
    $user = auth()->user();
@endphp

<!-- Sidebar Colapsable Premium -->
<aside 
    id="sidebar" 
    class="fixed left-0 top-0 h-full bg-gradient-to-b from-white via-white to-[var(--color-agro-green-bg)]/30 shadow-2xl border-r border-[var(--color-agro-green-light)]/40 z-40 transition-all duration-300 ease-in-out
           w-72 lg:w-72
           -translate-x-full lg:translate-x-0
           flex flex-col"
    data-collapsed="false"
>
    <!-- Logo Section -->
    <div class="h-20 flex items-center justify-between px-6 border-b border-[var(--color-agro-green-light)]/30 bg-gradient-to-r from-[var(--color-agro-green-bg)]/50 to-transparent">
        <a href="{{ route($user->role . '.dashboard') }}" class="flex items-center space-x-3 group overflow-hidden">
            <div class="relative flex-shrink-0">
                <!-- Icon Logo SVG -->
                <svg class="h-10 w-10 text-[var(--color-agro-green-dark)] transition-transform duration-300 group-hover:scale-110" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2L4 6v6c0 5.25 3.5 10.16 8 12 4.5-1.84 8-6.75 8-12V6l-8-4z" fill="currentColor" opacity="0.2"/>
                    <path d="M12 2L4 6v6c0 5.25 3.5 10.16 8 12 4.5-1.84 8-6.75 8-12V6l-8-4z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M9 12l2 2 4-4" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="flex flex-col sidebar-text">
                <span class="text-2xl font-bold bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] bg-clip-text text-transparent whitespace-nowrap">
                    Agro365
                </span>
                <span class="text-xs text-gray-500 font-medium whitespace-nowrap">{{ NavigationHelper::getRoleName($user->role) }}</span>
            </div>
        </a>
        
        <!-- Toggle Button Desktop -->
        <button 
            onclick="toggleSidebarCollapse()"
            class="hidden lg:flex items-center justify-center w-8 h-8 rounded-lg text-[var(--color-agro-green-dark)] hover:bg-[var(--color-agro-green-bg)] transition-all duration-200"
            aria-label="Toggle sidebar"
        >
            <svg id="collapse-icon" class="w-5 h-5 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
            </svg>
        </button>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto py-6 px-4 space-y-2">
        <!-- Main Section -->
        <div class="mb-6">
            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-3 mb-3 sidebar-text">Principal</h3>
            @foreach($menu['main'] as $item)
                <div class="mb-1">
                    <a 
                        href="{{ route($item['route']) }}" 
                        class="flex items-center space-x-4 px-4 py-3 rounded-xl transition-all duration-200 group
                               {{ $item['active'] 
                                   ? 'bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white shadow-lg shadow-[var(--color-agro-green)]/30' 
                                   : 'text-gray-700 hover:bg-[var(--color-agro-green-bg)] hover:text-[var(--color-agro-green-dark)]' 
                               }}"
                        title="{{ $item['label'] }}"
                    >
                        <span class="flex-shrink-0 w-8 text-center">
                            {!! $item['icon_svg'] ?? $item['icon'] !!}
                        </span>
                        <span class="font-semibold flex-1 sidebar-text whitespace-nowrap overflow-hidden">{{ $item['label'] }}</span>
                        @if($item['active'])
                            <div class="w-2 h-2 rounded-full bg-white sidebar-indicator"></div>
                        @endif
                    </a>
                    
                    @if(isset($item['submenu']) && $item['active'])
                        <div class="ml-12 mt-2 space-y-1 sidebar-submenu">
                            @foreach($item['submenu'] as $subitem)
                                @php
                                    // Determinar qué policy usar según la ruta
                                    $canShow = true;
                                    if (str_contains($subitem['route'], 'digital-notebook.treatment.create') || 
                                        str_contains($subitem['route'], 'digital-notebook.fertilization.create') ||
                                        str_contains($subitem['route'], 'digital-notebook.irrigation.create') ||
                                        str_contains($subitem['route'], 'digital-notebook.cultural.create') ||
                                        str_contains($subitem['route'], 'digital-notebook.observation.create')) {
                                        $canShow = auth()->user()->can('create', \App\Models\AgriculturalActivity::class);
                                    } elseif (str_contains($subitem['route'], 'digital-notebook')) {
                                        $canShow = auth()->user()->can('viewAny', \App\Models\AgriculturalActivity::class);
                                    }
                                @endphp
                                @if($canShow)
                                    <a 
                                        href="{{ route($subitem['route']) }}" 
                                        class="flex items-center px-4 py-2 rounded-lg text-sm transition-all duration-200
                                               {{ $subitem['active'] 
                                                   ? 'bg-[var(--color-agro-green-bg)] text-[var(--color-agro-green-dark)] font-medium border-l-4 border-[var(--color-agro-green-dark)]' 
                                                   : 'text-gray-600 hover:bg-gray-100 hover:text-[var(--color-agro-green-dark)]' 
                                               }}"
                                    >
                                        <span class="w-1.5 h-1.5 rounded-full bg-current mr-3 opacity-50"></span>
                                        {{ $subitem['label'] }}
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <!-- Separator -->
        <div class="my-6 px-3">
            <div class="h-px bg-gradient-to-r from-transparent via-[var(--color-agro-green-light)]/30 to-transparent"></div>
        </div>

        <!-- System Section -->
        <div class="mb-6">
            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-3 mb-3 sidebar-text">Sistema</h3>
            @foreach($menu['system'] as $item)
                <a 
                    href="{{ route($item['route']) }}" 
                    class="flex items-center space-x-4 px-4 py-3 rounded-xl transition-all duration-200 group mb-1
                           {{ $item['active'] 
                               ? 'bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white shadow-lg shadow-[var(--color-agro-green)]/30' 
                               : 'text-gray-700 hover:bg-[var(--color-agro-green-bg)] hover:text-[var(--color-agro-green-dark)]' 
                           }}"
                    title="{{ $item['label'] }}"
                >
                    <span class="flex-shrink-0 w-8 text-center">
                        {!! $item['icon_svg'] ?? $item['icon'] !!}
                    </span>
                    <span class="font-semibold flex-1 sidebar-text whitespace-nowrap overflow-hidden">{{ $item['label'] }}</span>
                    @if($item['active'])
                        <div class="w-2 h-2 rounded-full bg-white sidebar-indicator"></div>
                    @endif
                </a>
            @endforeach
        </div>
    </nav>

    <!-- User Section -->
    <div class="border-t border-[var(--color-agro-green-light)]/30 bg-gradient-to-r from-[var(--color-agro-green-bg)]/30 to-transparent p-4">
        <!-- User Info -->
        <div class="flex items-center space-x-3 mb-4 px-2">
            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)] flex items-center justify-center shadow-lg flex-shrink-0">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0 sidebar-text">
                <p class="text-sm font-bold text-[var(--color-agro-green-dark)] truncate">{{ $user->name }}</p>
                <p class="text-xs text-gray-500 truncate">{{ NavigationHelper::getRoleName($user->role) }}</p>
            </div>
        </div>

        <!-- User Menu -->
        <div class="space-y-1 mb-3 sidebar-text">
            @foreach($menu['user'] as $item)
                <a 
                    href="{{ route($item['route']) }}" 
                    class="flex items-center space-x-3 px-3 py-2 rounded-lg transition-all duration-200
                           {{ $item['active'] 
                               ? 'bg-[var(--color-agro-green-bg)] text-[var(--color-agro-green-dark)] font-medium' 
                               : 'text-gray-700 hover:bg-[var(--color-agro-green-bg)] hover:text-[var(--color-agro-green-dark)]' 
                           }}"
                >
                    <span class="flex-shrink-0">{!! $item['icon_svg'] ?? $item['icon'] !!}</span>
                    <span class="text-sm font-medium">{{ $item['label'] }}</span>
                </a>
            @endforeach
        </div>

        <!-- Logout Button -->
        <form method="POST" action="{{ route('logout') }}" class="w-full">
            @csrf
            <button 
                type="submit" 
                class="w-full flex items-center justify-center space-x-2 px-4 py-3 rounded-xl bg-gradient-to-r from-[var(--color-agro-brown)] to-[var(--color-agro-brown-light)] text-white hover:from-[var(--color-agro-brown-light)] hover:to-[var(--color-agro-brown)] transition-all duration-200 font-semibold shadow-lg hover:shadow-xl transform hover:scale-[1.02]"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                <span class="sidebar-text">Cerrar Sesión</span>
            </button>
        </form>
    </div>
</aside>

<!-- Overlay para móvil -->
<div 
    id="sidebar-overlay" 
    class="fixed inset-0 bg-black/60 backdrop-blur-sm z-30 lg:hidden hidden transition-opacity duration-300"
    onclick="toggleSidebar()"
></div>

<!-- Botón toggle para móvil -->
<button 
    id="sidebar-toggle"
    onclick="toggleSidebar()"
    class="fixed top-4 left-4 z-50 lg:hidden bg-white/95 backdrop-blur-md p-3 rounded-xl shadow-xl border-2 border-[var(--color-agro-green-light)]/30 hover:bg-[var(--color-agro-green-bg)] transition-all transform hover:scale-110"
    aria-label="Toggle sidebar"
>
    <svg class="w-6 h-6 text-[var(--color-agro-green-dark)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
    </svg>
</button>

<script>
    // Toggle sidebar collapse/expand (Desktop)
    function toggleSidebarCollapse() {
        const sidebar = document.getElementById('sidebar');
        const collapseIcon = document.getElementById('collapse-icon');
        const isCollapsed = sidebar.getAttribute('data-collapsed') === 'true';
        
        if (isCollapsed) {
            // Expandir
            sidebar.classList.remove('lg:w-20');
            sidebar.classList.add('lg:w-72');
            sidebar.setAttribute('data-collapsed', 'false');
            collapseIcon.style.transform = 'rotate(0deg)';
            
            // Mostrar textos después de la animación
            setTimeout(() => {
                document.querySelectorAll('.sidebar-text, .sidebar-indicator, .sidebar-submenu').forEach(el => {
                    el.style.opacity = '1';
                    el.style.display = '';
                });
            }, 150);
        } else {
            // Colapsar
            sidebar.classList.remove('lg:w-72');
            sidebar.classList.add('lg:w-20');
            sidebar.setAttribute('data-collapsed', 'true');
            collapseIcon.style.transform = 'rotate(180deg)';
            
            // Ocultar textos
            document.querySelectorAll('.sidebar-text, .sidebar-indicator, .sidebar-submenu').forEach(el => {
                el.style.opacity = '0';
                setTimeout(() => {
                    if (sidebar.getAttribute('data-collapsed') === 'true') {
                        el.style.display = 'none';
                    }
                }, 150);
            });
        }
        
        // Ajustar el main content
        const main = document.querySelector('main');
        if (main) {
            if (isCollapsed) {
                main.classList.remove('lg:pl-20');
                main.classList.add('lg:pl-72');
            } else {
                main.classList.remove('lg:pl-72');
                main.classList.add('lg:pl-20');
            }
        }
    }

    // Toggle sidebar mobile
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        
        sidebar.classList.toggle('-translate-x-full');
        overlay.classList.toggle('hidden');
        
        if (sidebar.classList.contains('-translate-x-full')) {
            document.body.style.overflow = '';
        } else {
            document.body.style.overflow = 'hidden';
        }
    }

    // Cerrar sidebar al hacer clic fuera en móvil
    document.addEventListener('click', function(event) {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        const toggle = document.getElementById('sidebar-toggle');
        
        if (window.innerWidth < 1024) {
            if (!sidebar.contains(event.target) && !toggle.contains(event.target) && !sidebar.classList.contains('-translate-x-full')) {
                toggleSidebar();
            }
        }
    });

    // Ajustar sidebar en resize
    window.addEventListener('resize', function() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        
        if (window.innerWidth >= 1024) {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.add('hidden');
            document.body.style.overflow = '';
        } else {
            sidebar.classList.add('-translate-x-full');
            // Reset collapsed state on mobile
            sidebar.classList.remove('lg:w-20');
            sidebar.classList.add('lg:w-72');
            sidebar.setAttribute('data-collapsed', 'false');
        }
    });

    // Cerrar sidebar con ESC
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            const sidebar = document.getElementById('sidebar');
            if (!sidebar.classList.contains('-translate-x-full') && window.innerWidth < 1024) {
                toggleSidebar();
            }
        }
    });

    // Añadir transiciones suaves a los elementos del sidebar
    document.addEventListener('DOMContentLoaded', function() {
        const sidebarTexts = document.querySelectorAll('.sidebar-text, .sidebar-indicator, .sidebar-submenu');
        sidebarTexts.forEach(el => {
            el.style.transition = 'opacity 150ms ease-in-out';
        });
    });
</script>

<style>
    /* Estilos adicionales para el sidebar colapsado */
    #sidebar[data-collapsed="true"] .sidebar-text,
    #sidebar[data-collapsed="true"] .sidebar-indicator,
    #sidebar[data-collapsed="true"] .sidebar-submenu {
        opacity: 0;
        pointer-events: none;
    }
    
    #sidebar {
        transition: width 300ms cubic-bezier(0.4, 0, 0.2, 1);
    }
</style>
