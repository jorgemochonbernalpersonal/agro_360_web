<div class="space-y-6 animate-fade-in">

    {{-- Header --}}
    <x-agro.page-header
        title="Gestión de Parcelas"
        description="Administra y visualiza todas tus parcelas agrícolas"
    />

    {{-- Stats (colapsables) --}}
    <x-agro.stats-section key="plots">
        <x-agro.stat-card
            label="Total parcelas"
            :value="$stats['total']"
            :description="$stats['active'] . ' activas · ' . $stats['inactive'] . ' inactivas'"
            icon="map-pin"
            color="agro"
        />
        <x-agro.stat-card
            label="Superficie total"
            :value="number_format($stats['total_area'], 2) . ' ha'"
            description="Área declarada"
            icon="square-2-stack"
            color="blue"
        />
        <x-agro.stat-card
            label="Con SIGPAC"
            :value="$stats['with_sigpac']"
            :description="($stats['total'] - $stats['with_sigpac']) . ' sin código'"
            icon="rectangle-group"
            color="orange"
        />
        <x-agro.stat-card
            label="Inactivas"
            :value="$stats['inactive']"
            :description="$stats['inactive'] > 0 ? 'Archivadas' : 'Todas activas'"
            icon="archive-box"
            color="zinc"
        />
    </x-agro.stats-section>

    {{-- Tabs: Activas / Inactivas --}}
    <x-agro.tabs :tabs="[
        'active'   => ['label' => 'Activas',   'count' => $stats['active']],
        'inactive' => ['label' => 'Inactivas', 'count' => $stats['inactive']],
    ]" :active="$currentTab" wireMethod="switchTab" />

    {{-- Toolbar: search + filtros + acciones --}}
    @php
        $filterCount =
            (int) !empty($filterAutonomousCommunity) +
            (int) !empty($filterProvince) +
            (int) !empty($filterMunicipality);
    @endphp

    <div class="flex items-center gap-3">

        {{-- Search --}}
        <x-agro.search-input wire:model.live.debounce.300ms="search" placeholder="Buscar parcela por nombre..." />

        {{-- Filtros --}}
        <x-agro.filter-button modal="plot-filters" :count="$filterCount" />

        {{-- Toggle vista lista / mapa --}}
        <div class="flex items-center gap-0.5 bg-zinc-100 rounded-xl p-0.5">
            <button
                wire:click="toggleViewMode"
                title="Vista lista"
                class="inline-flex items-center justify-center w-9 h-9 rounded-[10px] transition-colors {{ $viewMode === 'list' ? 'bg-white shadow-sm text-zinc-800' : 'text-zinc-400 hover:text-zinc-600' }}"
            >
                <flux:icon icon="squares-2x2" class="size-4" />
            </button>
            <button
                wire:click="toggleViewMode"
                title="Vista mapa"
                class="inline-flex items-center justify-center w-9 h-9 rounded-[10px] transition-colors {{ $viewMode === 'map' ? 'bg-white shadow-sm text-agro-700' : 'text-zinc-400 hover:text-zinc-600' }}"
            >
                <flux:icon icon="map" class="size-4" />
            </button>
        </div>

        {{-- Separador --}}
        <div class="w-px h-8 bg-zinc-200 shrink-0"></div>

        {{-- Nueva Parcela --}}
        @can('create', \App\Models\Plot::class)
            <flux:button href="{{ roleRoute('plots.create') }}" variant="primary" icon="plus">
                Nueva
            </flux:button>
        @endcan

        {{-- Ver Plantaciones --}}
        <flux:button href="{{ route('plots.plantings.index') }}" variant="outline" icon="scissors">
            Plantaciones
        </flux:button>

    </div>

    {{-- Chips de filtros activos --}}
    @if ($filterAutonomousCommunity || $filterProvince || $filterMunicipality)
        <div class="flex flex-wrap items-center gap-2">
            @if ($filterAutonomousCommunity)
                <x-agro.filter-chip
                    icon="building-library"
                    :label="$this->autonomousCommunities[$filterAutonomousCommunity] ?? ''"
                    wireRemove="$set('filterAutonomousCommunity', ''); $set('filterProvince', ''); $set('filterMunicipality', '')"
                />
            @endif
            @if ($filterProvince)
                <x-agro.filter-chip
                    icon="map-pin"
                    :label="$this->provinces[$filterProvince] ?? ''"
                    wireRemove="$set('filterProvince', ''); $set('filterMunicipality', '')"
                />
            @endif
            @if ($filterMunicipality)
                <x-agro.filter-chip
                    icon="home"
                    :label="$this->municipalities[$filterMunicipality] ?? ''"
                    wireRemove="$set('filterMunicipality', '')"
                />
            @endif
            <button
                wire:click="$set('filterAutonomousCommunity', ''); $set('filterProvince', ''); $set('filterMunicipality', '')"
                class="text-xs text-zinc-400 hover:text-zinc-600 transition-colors"
            >
                Limpiar todo
            </button>
        </div>
    @endif

    {{-- Acciones masivas para municipio --}}
    @if ($filterAutonomousCommunity && $filterProvince && $filterMunicipality && $this->municipalityHasSigpacCodes)
        <x-agro.card>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-lg bg-agro-50 flex items-center justify-center">
                        <flux:icon icon="map" class="size-6 text-agro-600" />
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-zinc-900">
                            Acciones para {{ $this->municipalities[$filterMunicipality] ?? 'Municipio' }}
                        </h3>
                        <p class="text-sm text-zinc-600">
                            {{ $this->provinces[$filterProvince] ?? '' }},
                            {{ $this->autonomousCommunities[$filterAutonomousCommunity] ?? '' }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <flux:button
                        wire:click="generateAllMapsForMunicipality"
                        wire:loading.attr="disabled"
                        variant="primary"
                        icon="plus"
                    >
                        <span wire:loading.remove wire:target="generateAllMapsForMunicipality">Generar Todos los Mapas</span>
                        <span wire:loading wire:target="generateAllMapsForMunicipality">Generando...</span>
                    </flux:button>
                    @if ($firstPlotForMap)
                        <flux:button
                            href="{{ route('map', ['id' => $firstPlotForMap->id, 'municipality' => $filterMunicipality, 'return' => 'plots']) }}"
                            variant="primary"
                            icon="eye"
                        >
                            Ver Todos los Mapas
                        </flux:button>
                    @endif
                </div>
            </div>
        </x-agro.card>
    @endif

    {{-- Grid de Parcelas —— skeleton durante carga --}}
    @if ($viewMode === 'list')
        <x-agro.loading-grid target="switchTab, search, filterAutonomousCommunity, filterProvince, filterMunicipality, nextPage, previousPage, gotoPage, toggleViewMode" />
    @endif

    {{-- Mapa de Parcelas --}}
    @if ($viewMode === 'map')
        <div
            wire:loading
            wire:target="switchTab, search, filterAutonomousCommunity, filterProvince, filterMunicipality, toggleViewMode"
            class="flex items-center justify-center h-96 bg-zinc-50 rounded-xl border border-zinc-200"
        >
            <div class="flex flex-col items-center gap-3 text-zinc-400">
                <flux:icon icon="map" class="size-10 animate-pulse" />
                <span class="text-sm font-medium">Cargando mapa...</span>
            </div>
        </div>
        <div
            wire:loading.remove
            wire:target="switchTab, search, filterAutonomousCommunity, filterProvince, filterMunicipality, toggleViewMode"
        >
            @if (count($mapData) > 0)
                <div
                    wire:ignore
                    x-data="plotsMap(@js($mapData))"
                    x-init="init()"
                    class="rounded-xl overflow-hidden border border-zinc-200 shadow-sm"
                    style="height: 520px;"
                >
                    <div id="plots-leaflet-map" class="w-full h-full"></div>
                </div>
            @else
                <x-agro.empty-state
                    message="Sin coordenadas disponibles"
                    description="Las parcelas no tienen coordenadas de municipio configuradas todavía"
                    icon="map"
                />
            @endif
        </div>
    @endif

    {{-- Grid de Parcelas —— contenido real --}}
    @if ($viewMode === 'list')
    <div
        wire:loading.remove
        wire:target="switchTab, search, filterAutonomousCommunity, filterProvince, filterMunicipality, nextPage, previousPage, gotoPage, toggleViewMode"
    >
        @if ($plots->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach ($plots as $plot)
                    @php
                        $wineryName = '-';
                        if ($plot->viticulturist && $plot->viticulturist->wineries->isNotEmpty()) {
                            $wineryName = $plot->viticulturist->wineries->first()->name;
                        }
                        $hasMap = $plot->multiplePlotSigpacs
                            ->filter(fn($mps) => $mps->plot_geometry_id !== null)
                            ->isNotEmpty();
                        $delay = min($loop->index * 50, 300);

                        if (!$plot->active) {
                            $headerIcon    = 'archive-box';
                            $headerIconBg  = 'bg-zinc-100';
                            $headerIconColor = 'text-zinc-400';
                        } elseif ($hasMap) {
                            $headerIcon    = 'map';
                            $headerIconBg  = 'bg-agro-100';
                            $headerIconColor = 'text-agro-600';
                        } elseif ($plot->sigpacCodes->isNotEmpty()) {
                            $headerIcon    = 'rectangle-group';
                            $headerIconBg  = 'bg-amber-100';
                            $headerIconColor = 'text-amber-600';
                        } else {
                            $headerIcon    = 'map-pin';
                            $headerIconBg  = 'bg-zinc-100';
                            $headerIconColor = 'text-zinc-400';
                        }
                    @endphp

                    <x-agro.card
                        class="animate-fade-in-up flex flex-col hover:-translate-y-1 {{ !$plot->active ? 'opacity-60' : '' }}"
                        style="animation-delay: {{ $delay }}ms;"
                        wire:key="plot-card-{{ $plot->id }}"
                    >
                        {{-- Header --}}
                        <x-slot:header>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 {{ $headerIconBg }} rounded-xl flex items-center justify-center shrink-0">
                                    <flux:icon :icon="$headerIcon" class="size-5 {{ $headerIconColor }}" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-zinc-900 truncate">{{ $plot->name }}</h3>
                                    <p class="text-xs text-zinc-500 truncate">
                                        {{ $plot->province?->name }}{{ $plot->municipality ? ', ' . $plot->municipality->name : '' }}
                                    </p>
                                </div>
                                <x-agro.status-badge :active="$plot->active" class="shrink-0" />
                            </div>
                        </x-slot:header>

                        {{-- Body --}}
                        <div class="flex-1 space-y-4">

                            {{-- Stats: superficie + SIGPAC --}}
                            <div class="grid grid-cols-2 gap-3">
                                <div class="bg-agro-50 rounded-xl p-3">
                                    <p class="text-[10px] font-semibold text-agro-400 uppercase tracking-widest mb-1">Superficie</p>
                                    @if ($plot->area)
                                        <p class="text-2xl font-bold text-agro-700 leading-none">
                                            {{ number_format($plot->area, 2) }}
                                            <span class="text-xs font-medium text-zinc-400 ml-0.5">ha</span>
                                        </p>
                                    @else
                                        <p class="text-base font-medium text-zinc-300 italic leading-none">—</p>
                                    @endif
                                </div>
                                <div class="bg-zinc-50 rounded-xl p-3">
                                    <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest mb-1">SIGPAC</p>
                                    @if ($plot->sigpacCodes->isNotEmpty())
                                        <p class="text-2xl font-bold text-zinc-600 leading-none">
                                            {{ $plot->sigpacCodes->count() }}
                                            <span class="text-xs font-medium text-zinc-400 ml-0.5">rec.</span>
                                        </p>
                                    @else
                                        <p class="text-base font-medium text-zinc-300 italic leading-none">—</p>
                                    @endif
                                </div>
                            </div>

                            {{-- Métricas secundarias --}}
                            <div class="space-y-2 text-sm">
                                <div class="flex items-center gap-2 text-zinc-600">
                                    <flux:icon icon="building-office" class="size-4 text-zinc-400 shrink-0" />
                                    <span class="truncate">{{ $wineryName }}</span>
                                </div>
                                <div class="flex items-center gap-2 text-zinc-600">
                                    <flux:icon icon="user" class="size-4 text-zinc-400 shrink-0" />
                                    <span class="truncate">
                                        {{ $plot->viticulturist?->name }}
                                        @if ($plot->viticulturist && $plot->viticulturist->id === auth()->id())
                                            <span class="text-agro-600 font-semibold">(Yo)</span>
                                        @endif
                                    </span>
                                </div>
                                @if ($plot->description)
                                    <p class="text-xs text-zinc-400 truncate">{{ $plot->description }}</p>
                                @endif
                            </div>

                        </div>

                        {{-- Footer: acciones --}}
                        <x-slot:footer>
                            @php
                                $btnBase    = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors';
                                $btnDanger  = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-red-500 hover:bg-red-50 transition-colors';
                                $btnSuccess = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-agro-600 hover:bg-agro-50 transition-colors';
                            @endphp
                            <div class="flex items-center justify-between">
                                {{-- Izquierda: navegar --}}
                                <div class="flex items-center gap-0.5">
                                    <a href="{{ route('plots.show', $plot) }}" class="{{ $btnBase }}" title="Ver parcela">
                                        <flux:icon icon="eye" class="size-4" />
                                    </a>
                                    @if ($hasMap)
                                        <a href="{{ route('map', ['id' => $plot->id, 'return' => 'plots']) }}" class="{{ $btnBase }}" title="Ver mapa">
                                            <flux:icon icon="map" class="size-4" />
                                        </a>
                                    @elseif($plot->sigpacCodes->isNotEmpty())
                                        @can('update', $plot)
                                            <button wire:click="generateMap(null, {{ $plot->id }})" class="{{ $btnBase }}" title="Generar mapa desde SIGPAC">
                                                <flux:icon icon="cpu-chip" class="size-4" />
                                            </button>
                                        @endcan
                                    @endif
                                    @can('update', $plot)
                                        <a href="{{ route('plots.plantings.create', $plot) }}" class="{{ $btnBase }}" title="Nueva plantación">
                                            <flux:icon icon="scissors" class="size-4" />
                                        </a>
                                    @endcan
                                    <button wire:click="selectAuditPlot({{ $plot->id }})" class="{{ $btnBase }}" title="Historial de cambios">
                                        <flux:icon icon="list-bullet" class="size-4" />
                                    </button>
                                </div>

                                {{-- Separador --}}
                                <div class="w-px h-5 bg-zinc-200 mx-1"></div>

                                {{-- Derecha: gestionar --}}
                                <div class="flex items-center gap-0.5">
                                    @can('update', $plot)
                                        <a href="{{ route('plots.edit', $plot) }}" class="{{ $btnBase }}" title="Editar">
                                            <flux:icon icon="pencil-square" class="size-4" />
                                        </a>
                                        @if ($plot->active)
                                            <button wire:click="toggleActive({{ $plot->id }})" class="{{ $btnDanger }}" title="Desactivar">
                                                <flux:icon icon="no-symbol" class="size-4" />
                                            </button>
                                        @else
                                            <button wire:click="toggleActive({{ $plot->id }})" class="{{ $btnSuccess }}" title="Activar">
                                                <flux:icon icon="check-circle" class="size-4" />
                                            </button>
                                        @endif
                                    @endcan
                                </div>
                            </div>
                        </x-slot:footer>
                    </x-agro.card>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $plots->links() }}
            </div>
        @else
            <x-agro.empty-state
                message="No hay parcelas {{ $currentTab === 'active' ? 'activas' : 'inactivas' }}"
                description="{{ $currentTab === 'active' ? 'Comienza agregando tu primera parcela al sistema' : 'Todas las parcelas están actualmente activas' }}"
                icon="inbox"
            >
                @if ($currentTab === 'active')
                    @can('create', \App\Models\Plot::class)
                        <x-slot name="action">
                            <flux:button href="{{ roleRoute('plots.create') }}" variant="primary" icon="plus">
                                Crear mi primera parcela
                            </flux:button>
                        </x-slot>
                    @endcan
                @endif
            </x-agro.empty-state>
        @endif
    </div>
    @endif {{-- end viewMode === 'list' --}}


    {{-- Modal: Filtros --}}
    <x-agro.modal name="plot-filters" maxWidth="sm">
        <div class="px-6 py-4 border-b border-zinc-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-agro-100 rounded-lg flex items-center justify-center">
                        <flux:icon icon="adjustments-horizontal" class="size-4 text-agro-600" />
                    </div>
                    <h3 class="text-base font-semibold text-zinc-900">Filtros</h3>
                </div>
                <flux:button x-on:click="$dispatch('close-modal', 'plot-filters')" variant="ghost" size="sm" icon="x-mark" />
            </div>
        </div>

        <div class="px-6 py-5 space-y-5">
            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Comunidad Autónoma</label>
                <flux:select wire:model.live="filterAutonomousCommunity">
                    <option value="">Todas las comunidades</option>
                    @foreach ($this->autonomousCommunities as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </flux:select>
            </div>
            @if ($filterAutonomousCommunity)
                <div>
                    <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Provincia</label>
                    <flux:select wire:model.live="filterProvince">
                        <option value="">Todas las provincias</option>
                        @foreach ($this->provinces as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </flux:select>
                </div>
            @endif
            @if ($filterProvince)
                <div>
                    <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Municipio</label>
                    <flux:select wire:model.live="filterMunicipality">
                        <option value="">Todos los municipios</option>
                        @foreach ($this->municipalities as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </flux:select>
                </div>
            @endif
        </div>

        <div class="px-6 py-4 border-t border-zinc-200 flex items-center justify-between">
            @if ($filterAutonomousCommunity || $filterProvince || $filterMunicipality)
                <button
                    wire:click="$set('filterAutonomousCommunity', ''); $set('filterProvince', ''); $set('filterMunicipality', '')"
                    class="text-sm text-zinc-400 hover:text-zinc-600 transition-colors"
                >
                    Limpiar filtros
                </button>
            @else
                <span></span>
            @endif
            <flux:button x-on:click="$dispatch('close-modal', 'plot-filters')" variant="primary">
                Aplicar
            </flux:button>
        </div>
    </x-agro.modal>


    {{-- Modal: Historial de Auditoría --}}
    <x-agro.modal name="plot-audit" maxWidth="3xl">
        <div class="bg-zinc-50 px-6 py-4 border-b border-zinc-200 rounded-t-xl">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-zinc-900">Historial de Auditoría</h3>
                <flux:button x-on:click="$dispatch('close-modal', 'plot-audit')" variant="ghost" size="sm" icon="x-mark" />
            </div>
        </div>
        <div class="px-6 py-4 max-h-[65vh] overflow-y-auto">
            @if ($auditPlot)
                @livewire('viticulturist.plots.plot-audit-history', ['plot' => $auditPlot], key('audit-' . $auditPlot->id))
            @else
                <div class="py-8 text-center text-zinc-500">
                    <flux:icon icon="clock" class="size-10 mx-auto mb-2 text-zinc-300" />
                    <p>Selecciona una parcela para ver su historial</p>
                </div>
            @endif
        </div>
        <div class="bg-zinc-50 px-6 py-3 border-t border-zinc-200 rounded-b-xl flex justify-end">
            <flux:button x-on:click="$dispatch('close-modal', 'plot-audit')" variant="outline">Cerrar</flux:button>
        </div>
    </x-agro.modal>

</div>

@script
<script>
Alpine.data('plotsMap', (plots) => ({
    map: null,

    init() {
        const loadLeaflet = (callback) => {
            if (window.L) { callback(); return; }
            if (!document.getElementById('leaflet-css')) {
                const link = document.createElement('link');
                link.id = 'leaflet-css';
                link.rel = 'stylesheet';
                link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
                document.head.appendChild(link);
            }
            const script = document.createElement('script');
            script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
            script.onload = callback;
            document.head.appendChild(script);
        };

        loadLeaflet(() => {
            if (this.map) { this.map.remove(); }

            this.map = L.map('plots-leaflet-map').setView([40.0, -3.5], 6);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                maxZoom: 19,
            }).addTo(this.map);

            const activeIcon = L.divIcon({
                html: '<div style="width:12px;height:12px;background:#16a34a;border:2px solid #fff;border-radius:50%;box-shadow:0 1px 4px rgba(0,0,0,.4);"></div>',
                className: '',
                iconSize: [12, 12],
                iconAnchor: [6, 6],
            });
            const inactiveIcon = L.divIcon({
                html: '<div style="width:12px;height:12px;background:#94a3b8;border:2px solid #fff;border-radius:50%;box-shadow:0 1px 4px rgba(0,0,0,.4);"></div>',
                className: '',
                iconSize: [12, 12],
                iconAnchor: [6, 6],
            });

            const bounds = [];
            plots.forEach(plot => {
                const marker = L.marker([plot.lat, plot.lng], {
                    icon: plot.active ? activeIcon : inactiveIcon,
                    title: plot.name,
                }).addTo(this.map);

                marker.bindPopup(
                    `<div style="min-width:150px;">
                        <p style="font-weight:700;margin:0 0 4px;">${plot.name}</p>
                        <a href="${plot.url}" style="color:#16a34a;font-size:12px;text-decoration:none;">
                            Ver parcela →
                        </a>
                    </div>`
                );
                bounds.push([plot.lat, plot.lng]);
            });

            if (bounds.length > 0) {
                this.map.fitBounds(bounds, { padding: [50, 50], maxZoom: 14 });
            }
        });
    },
}));
</script>
@endscript
