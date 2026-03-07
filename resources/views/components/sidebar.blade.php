@php
    use App\Helpers\NavigationHelper;
    $menu = NavigationHelper::getMenu();
    $user = auth()->user();

    $activeSections = [];
    foreach(['operations', 'plots_analysis', 'harvest', 'cellar', 'viticulturists', 'resources', 'billing', 'clients', 'compliance', 'cuaderno_official', 'pac', 'system'] as $section) {
        if (isset($menu[$section])) {
            foreach ($menu[$section] as $item) {
                if ($item['active'] ?? false) { $activeSections[] = $section; break; }
            }
        }
    }

    $sections = [
        // Viticulturist
        'operations'        => 'Campaña y Cuaderno',
        'plots_analysis'    => 'Parcelas y Territorio',
        // Winery
        'harvest'           => 'Vendimia',
        'cellar'            => 'Bodega',
        'viticulturists'    => 'Viticultores',
        // Shared
        'resources'         => 'Recursos',
        'billing'           => 'Facturación',
        'clients'           => 'Clientes',
        // Viticulturist compliance (split)
        'compliance'        => 'Normativa',
        'cuaderno_official' => 'Cuaderno Oficial',
        'pac'               => 'PAC',
        // Winery compliance
        'system'            => 'Sistema',
    ];
@endphp

<aside
    id="sidebar"
    class="fixed left-0 top-0 h-full z-40 transition-all duration-300 ease-in-out w-72 lg:w-72 -translate-x-full lg:translate-x-0 flex flex-col"
    style="background: linear-gradient(180deg, #0f2508 0%, #1a3a0e 40%, #2d5016 100%);"
    data-collapsed="false"
    x-data="{
        openSections: JSON.parse(localStorage.getItem('sidebarSections') || '{{ json_encode($activeSections) }}'),
        toggle(s) { this.openSections.includes(s) ? this.openSections = this.openSections.filter(x => x !== s) : this.openSections.push(s); localStorage.setItem('sidebarSections', JSON.stringify(this.openSections)); },
        isOpen(s) { return this.openSections.includes(s); }
    }"
>
    {{-- Logo --}}
    <div class="h-16 flex items-center justify-between px-5 border-b border-white/10">
        <a href="{{ route($user->role . '.dashboard') }}" class="flex items-center gap-3 group" data-cy="sidebar-logo-link">
            <div class="w-9 h-9 rounded-xl bg-white/15 flex items-center justify-center shadow-lg group-hover:bg-white/20 transition-all duration-200 flex-shrink-0">
                <img src="{{ asset('images/logo.png') }}" alt="Agro365" width="28" height="28" class="object-contain group-hover:scale-110 transition-transform duration-200">
            </div>
            <div class="sidebar-text">
                <span class="text-base font-bold text-white tracking-wide">Agro365</span>
                <span class="block text-xs text-white/50 font-medium">{{ NavigationHelper::getRoleName($user->role) }}</span>
            </div>
        </a>
        <button onclick="toggleSidebarCollapse()" class="hidden lg:flex items-center justify-center w-8 h-8 rounded-lg text-white/40 hover:text-white hover:bg-white/10 transition-all duration-200" aria-label="Toggle sidebar" data-cy="sidebar-toggle-desktop">
            <flux:icon id="collapse-icon" icon="chevron-double-left" variant="micro" class="transition-transform duration-300" />
        </button>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1 sidebar-scrollbar">
        @if(isset($menu['main']))
            <div class="mb-2">
                @foreach($menu['main'] as $item)
                    @include('components.sidebar-item', ['item' => $item])
                @endforeach
            </div>
            <div class="mx-2 border-t border-white/10 my-2"></div>
        @endif

        @foreach($sections as $key => $label)
            @if(isset($menu[$key]))
                <div class="mt-2">
                    <button
                        @click="toggle('{{ $key }}')"
                        class="w-full flex items-center justify-between px-3 py-1.5 text-[10px] font-bold text-white/35 uppercase tracking-widest hover:text-white/60 rounded-lg transition-colors duration-200 sidebar-text"
                    >
                        <span>{{ $label }}</span>
                        <flux:icon icon="chevron-down" variant="micro" class="transition-transform duration-200 opacity-50" ::class="isOpen('{{ $key }}') ? 'rotate-180' : ''" />
                    </button>
                    <div x-show="isOpen('{{ $key }}')" x-collapse class="mt-1 space-y-0.5">
                        @foreach($menu[$key] as $item)
                            @include('components.sidebar-item', ['item' => $item])
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach
    </nav>

    {{-- User footer --}}
    <div class="p-3 border-t border-white/10">
        <div class="flex items-center gap-2 px-3 py-2">
            <div class="w-7 h-7 rounded-full bg-agro-500 flex items-center justify-center flex-shrink-0">
                <span class="text-xs font-bold text-white">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
            </div>
            <div class="sidebar-text min-w-0 flex-1">
                <p class="text-xs font-semibold text-white truncate">{{ auth()->user()->name }}</p>
                <p class="text-[10px] text-white/40 truncate">{{ auth()->user()->email }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="sidebar-text shrink-0">
                @csrf
                <button type="submit"
                        title="Cerrar sesión"
                        class="w-7 h-7 flex items-center justify-center rounded-lg text-white/40 hover:text-white hover:bg-white/10 transition-colors duration-200">
                    <flux:icon icon="arrow-right-start-on-rectangle" class="size-4" />
                </button>
            </form>
        </div>
    </div>
</aside>

{{-- Mobile overlay --}}
<div id="sidebar-overlay" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-30 lg:hidden hidden transition-opacity" onclick="toggleSidebar()"></div>

{{-- Mobile toggle --}}
<button id="sidebar-toggle" onclick="toggleSidebar()" class="fixed top-4 left-4 z-50 lg:hidden bg-agro-900 p-2.5 rounded-xl shadow-lg border border-white/10 hover:bg-agro-800 transition-all duration-200" aria-label="Toggle sidebar" data-cy="sidebar-toggle-mobile">
    <flux:icon icon="bars-3" class="text-white" />
</button>

<style>
    #sidebar[data-collapsed="true"] .sidebar-text,
    #sidebar[data-collapsed="true"] .sidebar-indicator,
    #sidebar[data-collapsed="true"] .sidebar-submenu { opacity: 0; pointer-events: none; }
    #sidebar { transition: width 300ms cubic-bezier(0.4, 0, 0.2, 1); }

    /* Sidebar scrollbar styling */
    .sidebar-scrollbar::-webkit-scrollbar { width: 4px; }
    .sidebar-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .sidebar-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); border-radius: 4px; }
    .sidebar-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.25); }
</style>
