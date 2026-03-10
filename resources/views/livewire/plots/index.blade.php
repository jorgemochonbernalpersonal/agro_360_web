<div class="space-y-6 animate-fade-in">

    <x-agro.page-header title="Gestion de Parcelas" description="Administra y visualiza todas tus parcelas agricolas" />

    {{-- Tabs --}}
    <x-agro.tabs :tabs="[
        'active' => ['label' => 'Activas', 'count' => $stats['active']],
        'inactive' => ['label' => 'Inactivas', 'count' => $stats['inactive']],
    ]" :active="$currentTab" wireMethod="switchTab" />

    {{-- ACTIVE / INACTIVE TABS --}}

    @php
        $filterCount =
            (int) !empty($filterAutonomousCommunity) +
            (int) !empty($filterProvince) +
            (int) !empty($filterMunicipality);
    @endphp

    {{-- Toolbar: search + filtros + acciones --}}
    <div class="flex items-center gap-3">

        {{-- Search --}}
        <div class="flex-1 relative">
            <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                <flux:icon icon="magnifying-glass" class="size-4 text-zinc-400" />
            </div>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar parcela por nombre..."
                class="w-full pl-9 pr-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm placeholder:text-zinc-400 shadow-sm focus:outline-none focus:ring-2 focus:ring-agro-500 focus:border-transparent transition" />
        </div>

        {{-- Filtros button --}}
        <button x-on:click="$dispatch('open-modal', 'plot-filters')"
            class="relative inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm font-medium text-zinc-700 hover:bg-zinc-50 shadow-sm transition-colors">
            <flux:icon icon="adjustments-horizontal" class="size-4 text-zinc-500" />
            Filtros
            @if ($filterCount > 0)
                <span
                    class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-agro-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center leading-none">
                    {{ $filterCount }}
                </span>
            @endif
        </button>

        {{-- Separador --}}
        <div class="w-px h-8 bg-zinc-200 shrink-0"></div>

        {{-- Nueva Parcela --}}
        @can('create', \App\Models\Plot::class)
            <flux:button href="{{ route('plots.create') }}" variant="primary" icon="plus">
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
                <span
                    class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                    <flux:icon icon="building-library" class="size-3" />
                    {{ $this->autonomousCommunities[$filterAutonomousCommunity] ?? '' }}
                    <button
                        wire:click="$set('filterAutonomousCommunity', ''); $set('filterProvince', ''); $set('filterMunicipality', '')"
                        class="ml-0.5 p-0.5 rounded-full hover:bg-agro-200 transition-colors">
                        <flux:icon icon="x-mark" class="size-3" />
                    </button>
                </span>
            @endif
            @if ($filterProvince)
                <span
                    class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                    <flux:icon icon="map-pin" class="size-3" />
                    {{ $this->provinces[$filterProvince] ?? '' }}
                    <button wire:click="$set('filterProvince', ''); $set('filterMunicipality', '')"
                        class="ml-0.5 p-0.5 rounded-full hover:bg-agro-200 transition-colors">
                        <flux:icon icon="x-mark" class="size-3" />
                    </button>
                </span>
            @endif
            @if ($filterMunicipality)
                <span
                    class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                    <flux:icon icon="home" class="size-3" />
                    {{ $this->municipalities[$filterMunicipality] ?? '' }}
                    <button wire:click="$set('filterMunicipality', '')"
                        class="ml-0.5 p-0.5 rounded-full hover:bg-agro-200 transition-colors">
                        <flux:icon icon="x-mark" class="size-3" />
                    </button>
                </span>
            @endif
            <button
                wire:click="$set('filterAutonomousCommunity', ''); $set('filterProvince', ''); $set('filterMunicipality', '')"
                class="text-xs text-zinc-400 hover:text-zinc-600 transition-colors">
                Limpiar todo
            </button>
        </div>
    @endif

    {{-- Acciones Masivas para Municipio --}}
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
                    <flux:button wire:click="generateAllMapsForMunicipality" wire:loading.attr="disabled"
                        variant="primary" icon="plus">
                        <span wire:loading.remove wire:target="generateAllMapsForMunicipality">Generar Todos los
                            Mapas</span>
                        <span wire:loading wire:target="generateAllMapsForMunicipality">Generando...</span>
                    </flux:button>

                    @if ($firstPlotForMap)
                        <flux:button
                            href="{{ route('map', ['id' => $firstPlotForMap->id, 'municipality' => $filterMunicipality, 'return' => 'plots']) }}"
                            variant="primary" icon="eye">
                            Ver Todos los Mapas
                        </flux:button>
                    @endif
                </div>
            </div>
        </x-agro.card>
    @endif

    {{-- Grid de Parcelas — skeleton durante carga --}}
    <div wire:loading
        wire:target="switchTab, search, filterAutonomousCommunity, filterProvince, filterMunicipality, nextPage, previousPage, gotoPage">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mt-2">
            @for ($i = 0; $i < 6; $i++)
                <x-agro.skeleton-card />
            @endfor
        </div>
    </div>

    {{-- Grid de Parcelas — contenido real --}}
    <div wire:loading.remove
        wire:target="switchTab, search, filterAutonomousCommunity, filterProvince, filterMunicipality, nextPage, previousPage, gotoPage">
        @if ($plots->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mt-2">
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

                        // Icono dinámico según completitud de datos
                        if (!$plot->active) {
                            $headerIcon = 'archive-box';
                            $headerIconBg = 'bg-zinc-100';
                            $headerIconColor = 'text-zinc-400';
                        } elseif ($hasMap) {
                            $headerIcon = 'map';
                            $headerIconBg = 'bg-agro-100';
                            $headerIconColor = 'text-agro-600';
                        } elseif ($plot->sigpacCodes->isNotEmpty()) {
                            $headerIcon = 'rectangle-group';
                            $headerIconBg = 'bg-amber-100';
                            $headerIconColor = 'text-amber-600';
                        } else {
                            $headerIcon = 'map-pin';
                            $headerIconBg = 'bg-zinc-100';
                            $headerIconColor = 'text-zinc-400';
                        }
                    @endphp

                    <x-agro.card
                        class="animate-fade-in-up flex flex-col hover:-translate-y-1 {{ !$plot->active ? 'opacity-60' : '' }}"
                        style="animation-delay: {{ $delay }}ms;" wire:key="plot-card-{{ $plot->id }}">
                        {{-- Header --}}
                        <x-slot:header>
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 {{ $headerIconBg }} rounded-xl flex items-center justify-center shrink-0">
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

                            {{-- Stats principales: superficie + SIGPAC --}}
                            <div class="grid grid-cols-2 gap-3">
                                <div class="bg-agro-50 rounded-xl p-3">
                                    <p class="text-[10px] font-semibold text-agro-400 uppercase tracking-widest mb-1">
                                        Superficie</p>
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
                                    <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest mb-1">
                                        SIGPAC</p>
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

                        {{-- Footer: iconos de acción en dos grupos --}}
                        <x-slot:footer>
                            @php
                                $btnBase =
                                    'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors';
                                $btnDanger =
                                    'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-red-500 hover:bg-red-50 transition-colors';
                                $btnSuccess =
                                    'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-agro-600 hover:bg-agro-50 transition-colors';
                            @endphp
                            <div class="flex items-center justify-between">
                                {{-- Grupo izquierdo: navegar --}}
                                <div class="flex items-center gap-0.5">
                                    <a href="{{ route('plots.show', $plot) }}" class="{{ $btnBase }}"
                                        title="Ver parcela">
                                        <flux:icon icon="eye" class="size-4" />
                                    </a>

                                    @if ($hasMap)
                                        <a href="{{ route('map', ['id' => $plot->id, 'return' => 'plots']) }}"
                                            class="{{ $btnBase }}" title="Ver mapa">
                                            <flux:icon icon="map" class="size-4" />
                                        </a>
                                    @elseif($plot->sigpacCodes->isNotEmpty())
                                        @can('update', $plot)
                                            <button wire:click="generateMap(null, {{ $plot->id }})"
                                                class="{{ $btnBase }}" title="Generar mapa desde SIGPAC">
                                                <flux:icon icon="cpu-chip" class="size-4" />
                                            </button>
                                        @endcan
                                    @endif

                                    @can('update', $plot)
                                        <a href="{{ route('plots.plantings.create', $plot) }}"
                                            class="{{ $btnBase }}" title="Nueva plantación">
                                            <flux:icon icon="scissors" class="size-4" />
                                        </a>
                                    @endcan

                                    <button wire:click="selectAuditPlot({{ $plot->id }})"
                                        class="{{ $btnBase }}" title="Historial de cambios">
                                        <flux:icon icon="list-bullet" class="size-4" />
                                    </button>
                                </div>

                                {{-- Separador vertical --}}
                                <div class="w-px h-5 bg-zinc-200 mx-1"></div>

                                {{-- Grupo derecho: gestionar --}}
                                <div class="flex items-center gap-0.5">
                                    @can('update', $plot)
                                        <a href="{{ route('plots.edit', $plot) }}" class="{{ $btnBase }}"
                                            title="Editar">
                                            <flux:icon icon="pencil-square" class="size-4" />
                                        </a>

                                        @if ($plot->active)
                                            <button wire:click="toggleActive({{ $plot->id }})"
                                                class="{{ $btnDanger }}" title="Desactivar">
                                                <flux:icon icon="no-symbol" class="size-4" />
                                            </button>
                                        @else
                                            <button wire:click="toggleActive({{ $plot->id }})"
                                                class="{{ $btnSuccess }}" title="Activar">
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
            <x-agro.empty-state message="No hay parcelas registradas"
                description="Comienza agregando tu primera parcela al sistema" icon="inbox">
                @can('create', \App\Models\Plot::class)
                    <x-slot name="action">
                        <flux:button href="{{ route('plots.create') }}" variant="primary" icon="plus">
                            Crear mi primera parcela
                        </flux:button>
                    </x-slot>
                @endcan
            </x-agro.empty-state>
        @endif
    </div>


    {{-- Modal Filtros --}}
    <x-agro.modal name="plot-filters" maxWidth="sm">
        <div class="px-6 py-4 border-b border-zinc-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-agro-100 rounded-lg flex items-center justify-center">
                        <flux:icon icon="adjustments-horizontal" class="size-4 text-agro-600" />
                    </div>
                    <h3 class="text-base font-semibold text-zinc-900">Filtros</h3>
                </div>
                <flux:button x-on:click="$dispatch('close-modal', 'plot-filters')" variant="ghost" size="sm"
                    icon="x-mark" />
            </div>
        </div>

        <div class="px-6 py-5 space-y-5">
            {{-- Comunidad Autónoma --}}
            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">
                    Comunidad Autónoma
                </label>
                <flux:select wire:model.live="filterAutonomousCommunity">
                    <option value="">Todas las comunidades</option>
                    @foreach ($this->autonomousCommunities as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </flux:select>
            </div>

            {{-- Provincia --}}
            @if ($filterAutonomousCommunity)
                <div>
                    <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">
                        Provincia
                    </label>
                    <flux:select wire:model.live="filterProvince">
                        <option value="">Todas las provincias</option>
                        @foreach ($this->provinces as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </flux:select>
                </div>
            @endif

            {{-- Municipio --}}
            @if ($filterProvince)
                <div>
                    <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">
                        Municipio
                    </label>
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
                    class="text-sm text-zinc-400 hover:text-zinc-600 transition-colors">
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

    {{-- Modal Historial de Auditoria --}}
    <x-agro.modal name="plot-audit" maxWidth="3xl">
        <div class="bg-zinc-50 px-6 py-4 border-b border-zinc-200 rounded-t-xl">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-zinc-900">Historial de Auditoria</h3>
                <flux:button x-on:click="$dispatch('close-modal', 'plot-audit')" variant="ghost" size="sm"
                    icon="x-mark" />
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
            <flux:button x-on:click="$dispatch('close-modal', 'plot-audit')" variant="outline">
                Cerrar
            </flux:button>
        </div>
    </x-agro.modal>

</div>
