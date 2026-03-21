@php
    use App\Helpers\NavigationHelper;
    $menu = NavigationHelper::getMenu();
    $user = auth()->user();

    // Paleta de colores por capítulo (accent RGB para usar en CSS inline)
    $chapterColors = [
        'campana'   => ['accent' => '#4ade80', 'bg' => 'rgba(74,222,128,0.12)',  'border' => 'rgba(74,222,128,0.5)'],   // verde
        'cuaderno'  => ['accent' => '#c2855a', 'bg' => 'rgba(194,133,90,0.12)', 'border' => 'rgba(194,133,90,0.5)'],   // tierra
        'registros' => ['accent' => '#60a5fa', 'bg' => 'rgba(96,165,250,0.12)', 'border' => 'rgba(96,165,250,0.5)'],   // azul
        'parcelas'  => ['accent' => '#22d3ee', 'bg' => 'rgba(34,211,238,0.12)', 'border' => 'rgba(34,211,238,0.5)'],   // cyan
        'recursos'  => ['accent' => '#fb923c', 'bg' => 'rgba(251,146,60,0.12)',  'border' => 'rgba(251,146,60,0.5)'],   // naranja
        'normativa' => ['accent' => '#c084fc', 'bg' => 'rgba(192,132,252,0.12)', 'border' => 'rgba(192,132,252,0.5)'],  // violeta
        'pac'       => ['accent' => '#f59e0b', 'bg' => 'rgba(245,158,11,0.12)',  'border' => 'rgba(245,158,11,0.5)'],   // ámbar
        'negocio'   => ['accent' => '#2dd4bf', 'bg' => 'rgba(45,212,191,0.12)',  'border' => 'rgba(45,212,191,0.5)'],   // teal
        'sistema'   => ['accent' => '#94a3b8', 'bg' => 'rgba(148,163,184,0.12)', 'border' => 'rgba(148,163,184,0.5)'],  // slate
        // do
        'do_censo'        => ['accent' => '#818cf8', 'bg' => 'rgba(129,140,248,0.12)', 'border' => 'rgba(129,140,248,0.5)'],  // indigo
        'do_viticultores' => ['accent' => '#34d399', 'bg' => 'rgba(52,211,153,0.12)',  'border' => 'rgba(52,211,153,0.5)'],   // emerald
        'do_campanas'     => ['accent' => '#fbbf24', 'bg' => 'rgba(251,191,36,0.12)',  'border' => 'rgba(251,191,36,0.5)'],   // amber
        'do_sup_bodegas'  => ['accent' => '#60a5fa', 'bg' => 'rgba(96,165,250,0.12)',  'border' => 'rgba(96,165,250,0.5)'],   // blue
        'do_sup_vitic'    => ['accent' => '#22d3ee', 'bg' => 'rgba(34,211,238,0.12)',  'border' => 'rgba(34,211,238,0.5)'],   // cyan
        'do_calificacion' => ['accent' => '#facc15', 'bg' => 'rgba(250,204,21,0.12)',  'border' => 'rgba(250,204,21,0.5)'],   // yellow
        'do_etiquetas'    => ['accent' => '#fb7185', 'bg' => 'rgba(251,113,133,0.12)', 'border' => 'rgba(251,113,133,0.5)'],  // rose
        'do_control'      => ['accent' => '#f87171', 'bg' => 'rgba(248,113,113,0.12)', 'border' => 'rgba(248,113,113,0.5)'],  // red
        'do_normativa_do' => ['accent' => '#c084fc', 'bg' => 'rgba(192,132,252,0.12)', 'border' => 'rgba(192,132,252,0.5)'],  // violet
        'do_territorio'   => ['accent' => '#2dd4bf', 'bg' => 'rgba(45,212,191,0.12)',  'border' => 'rgba(45,212,191,0.5)'],   // teal
        'do_estadisticas' => ['accent' => '#a78bfa', 'bg' => 'rgba(167,139,250,0.12)', 'border' => 'rgba(167,139,250,0.5)'],  // purple
        'do_negocio_do'   => ['accent' => '#4ade80', 'bg' => 'rgba(74,222,128,0.12)',  'border' => 'rgba(74,222,128,0.5)'],   // green
        'do_sistema'      => ['accent' => '#94a3b8', 'bg' => 'rgba(148,163,184,0.12)', 'border' => 'rgba(148,163,184,0.5)'],  // slate
        // winery
        'vendimia'      => ['accent' => '#f472b6', 'bg' => 'rgba(244,114,182,0.12)', 'border' => 'rgba(244,114,182,0.5)'],  // rosa
        'bodega_elab'   => ['accent' => '#f87171', 'bg' => 'rgba(248,113,113,0.12)', 'border' => 'rgba(248,113,113,0.5)'],  // rojo vino
        'bodega_salida' => ['accent' => '#d97706', 'bg' => 'rgba(217,119,6,0.12)',   'border' => 'rgba(217,119,6,0.5)'],    // ámbar/marrón
        'territorio'    => ['accent' => '#60a5fa', 'bg' => 'rgba(96,165,250,0.12)',  'border' => 'rgba(96,165,250,0.5)'],   // azul
    ];

    $viticulturistChapters = [
        ['key' => 'campana',   'icon' => 'pencil-square',            'label' => 'Campaña',              'sections' => ['operations']],
        ['key' => 'cuaderno',  'icon' => 'document-text',            'label' => 'Cuaderno de Campo',    'sections' => ['cuaderno_inputs']],
        ['key' => 'registros', 'icon' => 'clipboard-document-check', 'label' => 'Registros Oficiales',  'sections' => ['registros_oficiales']],
        ['key' => 'parcelas',  'icon' => 'map',                      'label' => 'Parcelas',             'sections' => ['plots_analysis']],
        ['key' => 'recursos',  'icon' => 'wrench-screwdriver',       'label' => 'Recursos',             'sections' => ['resources']],
        ['key' => 'normativa', 'icon' => 'shield-check',             'label' => 'Normativa',            'sections' => ['compliance']],
        ['key' => 'pac',       'icon' => 'document-chart-bar',       'label' => 'PAC',                  'sections' => ['pac']],
        ['key' => 'negocio',   'icon' => 'calculator',               'label' => 'Negocio',              'sections' => ['billing']],
    ];

    $wineryChapters = [
        ['key' => 'vendimia',      'icon' => 'archive-box-arrow-down', 'label' => 'Vendimia',          'sections' => ['harvest']],
        ['key' => 'bodega_elab',   'icon' => 'beaker',                 'label' => 'Bodega — Elab.',    'sections' => ['cellar_elab']],
        ['key' => 'bodega_salida', 'icon' => 'cube',                   'label' => 'Bodega — Salida',   'sections' => ['cellar_salida']],
        ['key' => 'territorio',    'icon' => 'map',                    'label' => 'Territorio',        'sections' => ['territory']],
        ['key' => 'normativa',     'icon' => 'shield-check',           'label' => 'Normativa',         'sections' => ['winery_normativa']],
        ['key' => 'negocio',       'icon' => 'calculator',             'label' => 'Negocio',           'sections' => ['billing']],
        ['key' => 'sistema',       'icon' => 'cog-6-tooth',            'label' => 'Sistema',           'sections' => ['resources', 'compliance', 'system']],
    ];

    // Capítulos winery específicos para el producer (sin territorio — cubierto por parcelas de campo)
    $producerWineryChapters = [
        ['key' => 'vendimia',      'icon' => 'archive-box-arrow-down', 'label' => 'Vendimia',          'sections' => ['harvest']],
        ['key' => 'bodega_elab',   'icon' => 'beaker',                 'label' => 'Bodega — Elab.',    'sections' => ['cellar_elab']],
        ['key' => 'bodega_salida', 'icon' => 'cube',                   'label' => 'Bodega — Salida',   'sections' => ['cellar_salida']],
        ['key' => 'normativa_w',   'icon' => 'shield-check',           'label' => 'Normativa Bodega',  'sections' => ['winery_normativa']],
        ['key' => 'negocio_w',     'icon' => 'calculator',             'label' => 'Negocio Bodega',    'sections' => ['winery_billing']],
    ];
    $chapterColors['normativa_w'] = $chapterColors['normativa'];
    $chapterColors['negocio_w']   = $chapterColors['negocio'];
    // bodega_elab y bodega_salida ya están definidos en $chapterColors

    $doChapters = [
        ['key' => 'do_censo',        'icon' => 'users',              'label' => 'Censo',              'sections' => ['do_census']],
        ['key' => 'do_viticultores', 'icon' => 'user-group',         'label' => 'Viticultores DO',    'sections' => ['do_growers']],
        ['key' => 'do_campanas',     'icon' => 'flag',               'label' => 'Campañas',           'sections' => ['do_campaigns']],
        ['key' => 'do_sup_bodegas',  'icon' => 'building-office-2',  'label' => 'Bodegas',            'sections' => ['do_oversight_wineries']],
        ['key' => 'do_sup_vitic',    'icon' => 'eye',                'label' => 'Viticultores',       'sections' => ['do_oversight_growers']],
        ['key' => 'do_calificacion', 'icon' => 'star',               'label' => 'Calificación',       'sections' => ['do_qualification']],
        ['key' => 'do_etiquetas',    'icon' => 'tag',                'label' => 'Contraetiquetas',    'sections' => ['do_labels']],
        ['key' => 'do_control',      'icon' => 'shield-check',       'label' => 'Control',            'sections' => ['do_inspection']],
        ['key' => 'do_normativa_do', 'icon' => 'document-text',      'label' => 'Normativa',          'sections' => ['do_regulation']],
        ['key' => 'do_territorio',   'icon' => 'map',                'label' => 'Territorio',         'sections' => ['do_territory']],
        ['key' => 'do_estadisticas', 'icon' => 'chart-bar',          'label' => 'Estadísticas',       'sections' => ['do_statistics']],
        ['key' => 'do_negocio_do',   'icon' => 'calculator',         'label' => 'Negocio DO',         'sections' => ['do_finance']],
        ['key' => 'do_sistema',      'icon' => 'cog-6-tooth',        'label' => 'Sistema',            'sections' => ['do_settings']],
    ];

    $chapters = match($user->role) {
        'viticulturist' => $viticulturistChapters,
        'winery'        => $wineryChapters,
        'producer'      => array_merge($viticulturistChapters, $producerWineryChapters), // para flyouts
        'supervisor'    => $doChapters,
        default         => [],
    };

    $dashboardRoute = match($user->role) {
        'winery'    => 'winery.dashboard',
        'producer'  => 'producer.dashboard',
        'supervisor' => 'supervisor.dashboard',
        default     => 'viticulturist.dashboard',
    };

    // Detectar capítulo activo
    $activeChapterKey = null;
    foreach ($chapters as $chapter) {
        foreach ($chapter['sections'] as $sectionKey) {
            if (!isset($menu[$sectionKey])) continue;
            foreach ($menu[$sectionKey] as $item) {
                if ($item['active'] ?? false) { $activeChapterKey = $chapter['key']; break 3; }
            }
        }
    }

    // Para producer: detectar en qué tab (vineyard/bodega) está el capítulo activo
    $activeProducerTab = 'vineyard';
    if ($user->role === 'producer') {
        $bodegaKeys = array_column($producerWineryChapters, 'key');
        if ($activeChapterKey && in_array($activeChapterKey, $bodegaKeys)) {
            $activeProducerTab = 'bodega';
        }
    }

    $mainItems = $menu['main'] ?? [];
@endphp

<div
    x-data="{ producerTab: '{{ $activeProducerTab }}', mobileOpen: false, mobileChapter: '{{ $activeChapterKey }}' }"
    @keydown.escape.window="$store.nav.close(); mobileOpen = false"
    @mobile-nav-toggle.window="mobileOpen = !mobileOpen"
>
    {{-- ═══════════════════ RAIL 64px (solo lg+) ═══════════════════ --}}
    <aside
        class="hidden lg:flex fixed left-0 top-0 h-full w-16 z-50 flex-col items-center py-3 gap-1"
        style="background: linear-gradient(180deg, #0f2508 0%, #1a3a0e 40%, #2d5016 100%);"
    >
        {{-- Logo --}}
        <a href="{{ route($dashboardRoute) }}" wire:navigate
           class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center mb-2 hover:bg-white/20 transition-all group flex-shrink-0"
           title="Agro365">
            <img src="{{ asset('images/logo.png') }}" alt="Agro365" width="26" height="26"
                 class="object-contain group-hover:scale-110 transition-transform">
        </a>

        <div class="w-8 border-t border-white/10 mb-1"></div>

        {{-- Main items (Dashboard, Calendario) --}}
        @foreach($mainItems as $item)
            <a href="{{ route($item['route']) }}" wire:navigate
               title="{{ $item['label'] }}"
               class="relative group flex items-center justify-center w-11 h-11 rounded-xl transition-all duration-150
                      {{ ($item['active'] ?? false) ? 'bg-white/20 text-white' : 'text-white/45 hover:bg-white/10 hover:text-white' }}">
                <flux:icon icon="{{ $item['icon'] }}" class="w-5 h-5" />
                @if(isset($item['new']) && $item['new'])
                    <span class="absolute top-1 right-1 w-2 h-2 bg-amber-400 rounded-full ring-1 ring-black/20"></span>
                @elseif(!empty($item['badge']) && $item['badge'] > 0)
                    <span class="absolute -top-0.5 -right-0.5 w-4 h-4 bg-red-500 text-white text-[9px] font-bold rounded-full flex items-center justify-center">{{ $item['badge'] }}</span>
                @endif
                {{-- Tooltip --}}
                <span class="rail-tooltip">{{ $item['label'] }}</span>
            </a>
        @endforeach

        <div class="w-8 border-t border-white/10 my-1"></div>

        @if($user->role === 'producer' && !empty($menu['main_shortcuts']))
        {{-- Producer: accesos directos comunes (viñedo + bodega) --}}
        @foreach($menu['main_shortcuts'] as $item)
            <a href="{{ route($item['route']) }}" wire:navigate
               title="{{ $item['label'] }}"
               class="relative group flex items-center justify-center w-11 h-11 rounded-xl transition-all duration-150
                      {{ ($item['active'] ?? false) ? 'bg-white/20 text-white' : 'text-white/45 hover:bg-white/10 hover:text-white' }}">
                <flux:icon icon="{{ $item['icon'] }}" class="w-5 h-5" />
                <span class="rail-tooltip">{{ $item['label'] }}</span>
            </a>
        @endforeach
        <div class="w-8 border-t border-white/10 my-1"></div>
        @endif

        @if($user->role === 'producer')
        {{-- Producer: tab switcher Viñedo / Bodega --}}
        <div class="flex w-full px-2 gap-1 mb-0.5 flex-shrink-0">
            <button type="button"
                x-on:click="producerTab = 'vineyard'; $store.nav.close()"
                :class="producerTab === 'vineyard' ? 'bg-green-500/20 text-green-400 ring-1 ring-inset ring-green-500/40' : 'text-white/40 hover:text-white/70 hover:bg-white/10'"
                class="flex-1 h-7 rounded-lg text-[10px] font-bold transition-all leading-none"
                title="Viñedo">🌿</button>
            <button type="button"
                x-on:click="producerTab = 'bodega'; $store.nav.close()"
                :class="producerTab === 'bodega' ? 'bg-red-500/20 text-red-400 ring-1 ring-inset ring-red-500/40' : 'text-white/40 hover:text-white/70 hover:bg-white/10'"
                class="flex-1 h-7 rounded-lg text-[10px] font-bold transition-all leading-none"
                title="Bodega">🏛</button>
        </div>
        {{-- Producer: Viñedo chapters --}}
        <div x-show="producerTab === 'vineyard'"
             class="flex-1 flex flex-col items-center gap-1 overflow-y-auto overflow-x-hidden w-full py-0.5 rail-chapters-scroll">
        @foreach($viticulturistChapters as $ch)
            @php $color = $chapterColors[$ch['key']] ?? $chapterColors['sistema']; $isActive = ($activeChapterKey === $ch['key']); @endphp
            <button type="button" x-on:click="$store.nav.toggle('{{ $ch['key'] }}')" title="{{ $ch['label'] }}"
                class="notebook-tab flex-shrink-0 relative group flex items-center justify-center w-11 h-11 rounded-xl transition-all duration-200"
                :class="$store.nav.open === '{{ $ch['key'] }}' ? 'tab-open' : ''"
                data-key="{{ $ch['key'] }}" data-active="{{ $isActive ? 'true' : 'false' }}"
                style="--tab-accent: {{ $color['accent'] }}; --tab-bg: {{ $color['bg'] }}; --tab-border: {{ $color['border'] }};">
                <flux:icon icon="{{ $ch['icon'] }}" class="w-5 h-5 tab-icon transition-colors duration-150" />
                <span class="rail-tooltip" style="border-left: 2px solid {{ $color['accent'] }}">{{ $ch['label'] }}</span>
            </button>
        @endforeach
        </div>
        {{-- Producer: Bodega chapters --}}
        <div x-show="producerTab === 'bodega'"
             class="flex-1 flex flex-col items-center gap-1 overflow-y-auto overflow-x-hidden w-full py-0.5 rail-chapters-scroll">
        @foreach($producerWineryChapters as $ch)
            @php $color = $chapterColors[$ch['key']] ?? $chapterColors['sistema']; $isActive = ($activeChapterKey === $ch['key']); @endphp
            <button type="button" x-on:click="$store.nav.toggle('{{ $ch['key'] }}')" title="{{ $ch['label'] }}"
                class="notebook-tab flex-shrink-0 relative group flex items-center justify-center w-11 h-11 rounded-xl transition-all duration-200"
                :class="$store.nav.open === '{{ $ch['key'] }}' ? 'tab-open' : ''"
                data-key="{{ $ch['key'] }}" data-active="{{ $isActive ? 'true' : 'false' }}"
                style="--tab-accent: {{ $color['accent'] }}; --tab-bg: {{ $color['bg'] }}; --tab-border: {{ $color['border'] }};">
                <flux:icon icon="{{ $ch['icon'] }}" class="w-5 h-5 tab-icon transition-colors duration-150" />
                <span class="rail-tooltip" style="border-left: 2px solid {{ $color['accent'] }}">{{ $ch['label'] }}</span>
            </button>
        @endforeach
        </div>
        @else
        {{-- Otros roles: capítulos directos --}}
        <div class="flex-1 flex flex-col items-center gap-1 overflow-y-auto overflow-x-hidden w-full py-0.5 rail-chapters-scroll">
        @foreach($chapters as $ch)
            @php $color = $chapterColors[$ch['key']] ?? $chapterColors['sistema']; $isActive = ($activeChapterKey === $ch['key']); @endphp
            <button type="button" x-on:click="$store.nav.toggle('{{ $ch['key'] }}')" title="{{ $ch['label'] }}"
                class="notebook-tab flex-shrink-0 relative group flex items-center justify-center w-11 h-11 rounded-xl transition-all duration-200"
                :class="$store.nav.open === '{{ $ch['key'] }}' ? 'tab-open' : ''"
                data-key="{{ $ch['key'] }}" data-active="{{ $isActive ? 'true' : 'false' }}"
                style="--tab-accent: {{ $color['accent'] }}; --tab-bg: {{ $color['bg'] }}; --tab-border: {{ $color['border'] }};">
                <flux:icon icon="{{ $ch['icon'] }}" class="w-5 h-5 tab-icon transition-colors duration-150" />
                <span class="rail-tooltip" style="border-left: 2px solid {{ $color['accent'] }}">{{ $ch['label'] }}</span>
            </button>
        @endforeach
        </div>
        @endif

        {{-- Rail bottom: Config + Soporte (viticulturist) --}}
        @if(!empty($menu['rail_bottom']))
            <div class="w-8 border-t border-white/10 mb-1"></div>
            @foreach($menu['rail_bottom'] as $item)
                <a href="{{ route($item['route']) }}" wire:navigate
                   title="{{ $item['label'] }}"
                   class="relative group flex items-center justify-center w-11 h-11 rounded-xl transition-all duration-150
                          {{ ($item['active'] ?? false) ? 'bg-white/20 text-white' : 'text-white/45 hover:bg-white/10 hover:text-white' }}">
                    <flux:icon icon="{{ $item['icon'] }}" class="w-5 h-5" />
                    @if(!empty($item['badge']) && $item['badge'] > 0)
                        <span class="absolute -top-0.5 -right-0.5 w-4 h-4 bg-red-500 text-white text-[9px] font-bold rounded-full flex items-center justify-center">
                            {{ $item['badge'] }}
                        </span>
                    @endif
                    <span class="rail-tooltip">{{ $item['label'] }}</span>
                </a>
            @endforeach
        @endif
    </aside>

    {{-- ═══════════════════ FLYOUT PANELS (solo lg+) ═══════════════════ --}}
    <div class="hidden lg:block">
    @foreach($chapters as $ch)
        @php
            $color = $chapterColors[$ch['key']] ?? $chapterColors['sistema'];
            $chapterItems = [];
            foreach ($ch['sections'] as $sectionKey) {
                if (!isset($menu[$sectionKey])) continue;
                foreach ($menu[$sectionKey] as $item) { $chapterItems[] = $item; }
            }
        @endphp

        <div
            x-show="$store.nav.open === '{{ $ch['key'] }}'"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-x-2"
            x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-x-0"
            x-transition:leave-end="opacity-0 -translate-x-2"
            class="fixed left-16 top-0 h-full w-64 z-40 flex flex-col shadow-2xl"
            style="
                background: rgba(236, 246, 230, 0.96);
                backdrop-filter: blur(14px);
                -webkit-backdrop-filter: blur(14px);
                border-right: 1px solid {{ $color['border'] }};
            "
        >
            {{-- Header --}}
            <div class="h-16 flex items-center gap-3 px-4 flex-shrink-0 border-b border-zinc-300/50">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center"
                     style="background: {{ $color['bg'] }}; box-shadow: 0 0 0 1px {{ $color['border'] }};">
                    <flux:icon icon="{{ $ch['icon'] }}" class="w-4 h-4" style="color: {{ $color['accent'] }}" />
                </div>
                <span class="text-sm font-semibold text-zinc-800 tracking-wide">{{ $ch['label'] }}</span>
                <button type="button" x-on:click="$store.nav.close()"
                        class="ml-auto w-7 h-7 flex items-center justify-center rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-200/60 transition-colors">
                    <flux:icon icon="x-mark" class="w-4 h-4" />
                </button>
            </div>

            {{-- Línea de margen (cuaderno) --}}
            <div class="flex flex-1 overflow-hidden">
                <div class="w-px flex-shrink-0 ml-5 my-2" style="background: {{ $color['accent'] }}; opacity: 0.4;"></div>

                <nav class="flex-1 overflow-y-auto py-2 sidebar-scrollbar notebook-lines-light">
                    @foreach($chapterItems as $item)
                        @if(isset($item['divider']) && $item['divider'])
                            <div class="mx-4 my-1.5 border-t border-zinc-300/50"></div>
                        @else
                            <a
                                href="{{ route($item['route']) }}"
                                wire:navigate
                                x-on:click="$store.nav.close()"
                                class="notebook-item flex items-center gap-3 px-3 py-2.5 mx-2 transition-all duration-150 group
                                       {{ ($item['active'] ?? false) ? 'notebook-item-active' : 'text-zinc-500 hover:text-zinc-900' }}"
                                @if($item['active'] ?? false)
                                style="background: {{ $color['bg'] }}; box-shadow: inset 2px 0 0 {{ $color['accent'] }}; border-radius: 0 12px 12px 0; margin-left: 0; padding-left: 1.25rem;"
                                @endif
                            >
                                @if(isset($item['icon']))
                                    <flux:icon icon="{{ $item['icon'] }}" class="w-4 h-4 flex-shrink-0 transition-colors duration-150"
                                        style="{{ ($item['active'] ?? false) ? 'color:' . $color['accent'] . ';' : 'color: #6b7280;' }}" />
                                @endif
                                <span class="text-sm font-medium flex-1 leading-tight">{{ $item['label'] }}</span>
                                @if(isset($item['badge']) && $item['badge'] > 0)
                                    <span class="px-1.5 py-0.5 text-[10px] font-bold bg-red-500 text-white rounded-full">{{ $item['badge'] }}</span>
                                @elseif(isset($item['new']) && $item['new'])
                                    <span class="px-1.5 py-0.5 text-[9px] font-bold bg-amber-400 text-white rounded-full whitespace-nowrap">Nuevo</span>
                                @elseif(isset($item['wip']) && $item['wip'])
                                    <span class="px-1.5 py-0.5 text-[9px] font-bold bg-amber-100 text-amber-600 rounded-full whitespace-nowrap">Pronto</span>
                                @elseif($item['active'] ?? false)
                                    <div class="w-1.5 h-1.5 rounded-full flex-shrink-0" style="background: {{ $color['accent'] }};"></div>
                                @endif
                            </a>

                            @if(isset($item['submenu']) && count($item['submenu']) > 0)
                                <div class="ml-10 mb-1 space-y-0.5">
                                    @foreach($item['submenu'] as $sub)
                                        <a href="{{ route($sub['route']) }}" wire:navigate x-on:click="close()"
                                           class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs transition-all duration-150
                                                  {{ ($sub['active'] ?? false) ? 'font-semibold' : 'text-zinc-400 hover:text-zinc-700 hover:bg-zinc-200/50' }}"
                                           @if($sub['active'] ?? false) style="color: {{ $color['accent'] }};" @endif>
                                            <span class="w-1 h-1 rounded-full flex-shrink-0"
                                                  style="{{ ($sub['active'] ?? false) ? 'background:' . $color['accent'] . ';' : 'background: #d1d5db;' }}"></span>
                                            {{ $sub['label'] }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        @endif
                    @endforeach
                </nav>
            </div>

            {{-- Footer --}}
            <div class="px-4 py-2.5 border-t border-zinc-300/50 flex-shrink-0">
                <p class="text-[10px] font-semibold tracking-widest uppercase" style="color: {{ $color['accent'] }};">
                    {{ $ch['label'] }}
                </p>
            </div>
        </div>
    @endforeach

    {{-- Backdrop flyout --}}
    <div
        x-show="$store.nav.open !== null"
        x-cloak
        x-on:click="$store.nav.close()"
        style="display:none"
        class="fixed inset-0 z-30 cursor-default"
    ></div>
    </div>{{-- /hidden lg:block --}}

    {{-- ═══════════════════ MOBILE DRAWER (solo < lg) ═══════════════════ --}}
    <div class="lg:hidden">

        {{-- Backdrop --}}
        <div
            x-show="mobileOpen"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="mobileOpen = false"
            class="fixed inset-0 z-50 bg-black/60"
        ></div>

        {{-- Panel --}}
        <div
            x-show="mobileOpen"
            x-cloak
            x-transition:enter="transition-transform ease-out duration-250"
            x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition-transform ease-in duration-200"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full"
            class="fixed left-0 top-0 h-full w-72 max-w-[85vw] z-50 flex flex-col overflow-hidden shadow-2xl"
            style="background: linear-gradient(180deg, #0f2508 0%, #1a3a0e 40%, #2d5016 100%);"
        >
            {{-- Header --}}
            <div class="h-16 flex items-center gap-3 px-4 flex-shrink-0 border-b border-white/10">
                <a href="{{ route($dashboardRoute) }}" wire:navigate @click="mobileOpen = false"
                   class="w-9 h-9 rounded-xl bg-white/10 flex items-center justify-center flex-shrink-0">
                    <img src="{{ asset('images/logo.png') }}" alt="Agro365" width="22" height="22" class="object-contain">
                </a>
                <span class="text-white font-semibold text-sm tracking-wide flex-1">Agro365</span>
                <button type="button" @click="mobileOpen = false"
                        class="w-8 h-8 flex items-center justify-center rounded-lg text-white/50 hover:text-white hover:bg-white/10 transition">
                    <flux:icon icon="x-mark" class="w-4 h-4" />
                </button>
            </div>

            {{-- Main items (Dashboard, Calendario) --}}
            @foreach($mainItems as $item)
            <a href="{{ route($item['route']) }}" wire:navigate @click="mobileOpen = false"
               class="flex items-center gap-3 px-4 py-3 border-b border-white/5 transition
                      {{ ($item['active'] ?? false) ? 'bg-white/15 text-white' : 'text-white/60 hover:text-white hover:bg-white/8' }}">
                <flux:icon icon="{{ $item['icon'] }}" class="w-5 h-5 flex-shrink-0" />
                <span class="text-sm font-medium">{{ $item['label'] }}</span>
            </a>
            @endforeach

            {{-- Producer: tab switcher --}}
            @if($user->role === 'producer')
            <div class="flex px-3 py-2 gap-2 border-b border-white/10 flex-shrink-0">
                <button type="button"
                    @click="producerTab = 'vineyard'; mobileChapter = null"
                    :class="producerTab === 'vineyard' ? 'bg-green-500/20 text-green-400 ring-1 ring-inset ring-green-500/40' : 'text-white/40 hover:text-white/70 hover:bg-white/8'"
                    class="flex-1 py-1.5 rounded-lg text-xs font-bold transition">🌿 Viñedo</button>
                <button type="button"
                    @click="producerTab = 'bodega'; mobileChapter = null"
                    :class="producerTab === 'bodega' ? 'bg-red-500/20 text-red-400 ring-1 ring-inset ring-red-500/40' : 'text-white/40 hover:text-white/70 hover:bg-white/8'"
                    class="flex-1 py-1.5 rounded-lg text-xs font-bold transition">🏛 Bodega</button>
            </div>
            @endif

            {{-- Chapters accordion --}}
            <nav class="flex-1 overflow-y-auto py-1">
                @foreach($chapters as $ch)
                    @php
                        $color  = $chapterColors[$ch['key']] ?? $chapterColors['sistema'];
                        $chItems = [];
                        foreach ($ch['sections'] as $sk) {
                            if (!isset($menu[$sk])) continue;
                            foreach ($menu[$sk] as $itm) { $chItems[] = $itm; }
                        }
                        $chTab = 'vineyard';
                        if ($user->role === 'producer') {
                            $bodegaKeys = array_column($producerWineryChapters, 'key');
                            if (in_array($ch['key'], $bodegaKeys)) $chTab = 'bodega';
                        }
                    @endphp

                    @if($user->role === 'producer')
                    <div x-show="producerTab === '{{ $chTab }}'">
                    @endif

                    {{-- Chapter header --}}
                    <button type="button"
                        @click="mobileChapter = (mobileChapter === '{{ $ch['key'] }}') ? null : '{{ $ch['key'] }}'"
                        class="w-full flex items-center gap-3 px-4 py-2.5 text-left transition hover:bg-white/5"
                        :class="mobileChapter === '{{ $ch['key'] }}' ? 'bg-white/8' : ''">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0"
                             style="background: {{ $color['bg'] }}; box-shadow: 0 0 0 1px {{ $color['border'] }};">
                            <flux:icon icon="{{ $ch['icon'] }}" class="w-3.5 h-3.5" style="color: {{ $color['accent'] }}" />
                        </div>
                        <span class="text-sm font-medium flex-1"
                              :class="mobileChapter === '{{ $ch['key'] }}' ? 'text-white' : 'text-white/70'">
                            {{ $ch['label'] }}
                        </span>
                        <span class="transition-transform duration-200"
                              :class="mobileChapter === '{{ $ch['key'] }}' ? 'rotate-90' : ''">
                            <flux:icon icon="chevron-right" class="w-4 h-4 text-white/30" />
                        </span>
                    </button>

                    {{-- Chapter items --}}
                    <div x-show="mobileChapter === '{{ $ch['key'] }}'" x-cloak class="pb-1">
                        <div class="ml-4 border-l pl-3" style="border-color: {{ $color['accent'] }}50;">
                            @foreach($chItems as $item)
                                @if(isset($item['divider']) && $item['divider'])
                                    <div class="my-1 border-t border-white/10"></div>
                                @else
                                    <a href="{{ route($item['route']) }}" wire:navigate
                                       @click="mobileOpen = false"
                                       class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm transition my-0.5
                                              {{ ($item['active'] ?? false) ? 'text-white font-medium' : 'text-white/55 hover:text-white/90 hover:bg-white/5' }}"
                                       @if($item['active'] ?? false) style="background: {{ $color['bg'] }};" @endif>
                                        @if(isset($item['icon']))
                                            <flux:icon icon="{{ $item['icon'] }}" class="w-4 h-4 flex-shrink-0"
                                                style="{{ ($item['active'] ?? false) ? 'color:' . $color['accent'] . ';' : 'color: rgba(255,255,255,0.35);' }}" />
                                        @endif
                                        <span class="flex-1 leading-tight">{{ $item['label'] }}</span>
                                        @if(isset($item['badge']) && $item['badge'] > 0)
                                            <span class="px-1.5 py-0.5 text-[10px] font-bold bg-red-500 text-white rounded-full">{{ $item['badge'] }}</span>
                                        @elseif(isset($item['new']) && $item['new'])
                                            <span class="px-1.5 py-0.5 text-[9px] font-bold bg-amber-400 text-white rounded-full">Nuevo</span>
                                        @elseif(isset($item['wip']) && $item['wip'])
                                            <span class="px-1.5 py-0.5 text-[9px] font-bold bg-amber-100 text-amber-600 rounded-full">Pronto</span>
                                        @endif
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    @if($user->role === 'producer')
                    </div>
                    @endif
                @endforeach
            </nav>

            {{-- Rail bottom items --}}
            @if(!empty($menu['rail_bottom']))
            <div class="border-t border-white/10 py-1 flex-shrink-0">
                @foreach($menu['rail_bottom'] as $item)
                <a href="{{ route($item['route']) }}" wire:navigate @click="mobileOpen = false"
                   class="flex items-center gap-3 px-4 py-2.5 transition
                          {{ ($item['active'] ?? false) ? 'text-white bg-white/10' : 'text-white/50 hover:text-white hover:bg-white/5' }}">
                    <flux:icon icon="{{ $item['icon'] }}" class="w-4 h-4 flex-shrink-0" />
                    <span class="text-sm">{{ $item['label'] }}</span>
                </a>
                @endforeach
            </div>
            @endif
        </div>{{-- /panel --}}
    </div>{{-- /mobile drawer --}}

</div>

<style>
    /* Scrollbar flyout */
    .sidebar-scrollbar::-webkit-scrollbar { width: 3px; }

    /* Scrollbar oculto en la zona de capítulos del rail */
    .rail-chapters-scroll::-webkit-scrollbar { display: none; }
    .rail-chapters-scroll { scrollbar-width: none; -ms-overflow-style: none; }
    .sidebar-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .sidebar-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.12); border-radius: 4px; }

    /* Tooltip del rail */
    .rail-tooltip {
        pointer-events: none;
        position: absolute;
        left: calc(100% + 12px);
        top: 50%;
        transform: translateY(-50%);
        padding: 4px 10px;
        font-size: 11px;
        font-weight: 500;
        background: #18181b;
        color: #fff;
        border-radius: 8px;
        opacity: 0;
        white-space: nowrap;
        transition: opacity 150ms ease;
        box-shadow: 0 4px 12px rgba(0,0,0,0.4);
        z-index: 9999;
        border-left-width: 2px;
        border-left-style: solid;
        border-left-color: rgba(255,255,255,0.2);
    }
    .group:hover .rail-tooltip { opacity: 1; }

    /* Tab del rail */
    .notebook-tab {
        color: rgba(255,255,255,0.45);
    }
    /* Hover */
    .notebook-tab:hover {
        background: var(--tab-bg);
        box-shadow: inset 3px 0 0 var(--tab-accent);
        color: #fff;
    }
    .notebook-tab:hover .tab-icon {
        color: #fff;
    }
    /* Página activa */
    .notebook-tab[data-active="true"] {
        background: var(--tab-bg);
        box-shadow: inset 3px 0 0 var(--tab-accent);
    }
    .notebook-tab[data-active="true"] .tab-icon {
        color: var(--tab-accent);
    }
    /* Flyout abierto */
    .notebook-tab.tab-open {
        background: var(--tab-bg) !important;
        box-shadow: inset 3px 0 0 var(--tab-accent) !important;
    }
    .notebook-tab.tab-open .tab-icon {
        color: var(--tab-accent) !important;
    }

    /* Líneas de cuaderno en el flyout — fondo salvia claro */
    .notebook-lines-light {
        background-image: repeating-linear-gradient(
            to bottom,
            transparent,
            transparent 39px,
            rgba(0, 0, 0, 0.05) 39px,
            rgba(0, 0, 0, 0.05) 40px
        );
    }

    /* Hover en items del flyout */
    .notebook-item:hover:not(.notebook-item-active) {
        background: rgba(0, 0, 0, 0.05);
        box-shadow: inset 2px 0 0 rgba(0, 0, 0, 0.15);
        border-radius: 0 12px 12px 0;
        margin-left: 0 !important;
        padding-left: 1.25rem !important;
    }
</style>

{{-- Alpine.store('nav') initialized in resources/js/sidebar.js (via app.js) --}}

<script>
    function toggleSidebar() {
        window.dispatchEvent(new CustomEvent('mobile-nav-toggle'));
    }
</script>
