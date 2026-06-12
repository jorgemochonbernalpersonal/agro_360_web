<div class="space-y-6 animate-fade-in">

    {{-- Header --}}
    <x-agro.page-header
        :title="__('Gestión de Parcelas')"
        :description="__('Administra y visualiza todas tus parcelas agrícolas')"
    />

    {{-- Stats (colapsables) --}}
    <x-agro.stats-section key="plots">
        <x-agro.stat-card
            :label="__('Total parcelas')"
            :value="$stats['total']"
            :description="$stats['active'] . ' ' . __('activas') . ' · ' . $stats['inactive'] . ' ' . __('inactivas')"
            icon="map-pin"
            color="agro"
        />
        <x-agro.stat-card
            :label="__('Superficie total')"
            :value="number_format($stats['total_area'], 2) . ' ha'"
            :description="__('Área declarada')"
            icon="square-2-stack"
            color="blue"
        />
        <x-agro.stat-card
            :label="__('Con SIGPAC')"
            :value="$stats['with_sigpac']"
            :description="($stats['total'] - $stats['with_sigpac']) . ' ' . __('sin código')"
            icon="rectangle-group"
            color="orange"
            :link="$stats['with_geometry'] > 0 ? route('plots.map') : null"
            :linkLabel="__('Ver en mapa')"
        />
        <x-agro.stat-card
            :label="__('Inactivas')"
            :value="$stats['inactive']"
            :description="$stats['inactive'] > 0 ? __('Archivadas') : __('Todas activas')"
            icon="archive-box"
            color="zinc"
        />
    </x-agro.stats-section>

    {{-- Tabs: Activas / Inactivas --}}
    <x-agro.tabs :tabs="[
        'active'   => ['label' => __('Activas'),   'count' => $stats['active']],
        'inactive' => ['label' => __('Inactivas'), 'count' => $stats['inactive']],
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
        <x-agro.search-input wire:model.live.debounce.300ms="search" :placeholder="__('Buscar parcela por nombre...')" />

        {{-- Filtros --}}
        <x-agro.filter-button modal="plot-filters" :count="$filterCount" />

        {{-- Separador --}}
        <div class="w-px h-8 bg-zinc-200 shrink-0"></div>

        {{-- Nueva Parcela --}}
        @can('create', \App\Models\Plot::class)
            <flux:button href="{{ roleRoute('plots.create') }}" variant="primary" icon="plus">
                {{ __('Nueva') }}
            </flux:button>
        @endcan

        {{-- Ver Plantaciones --}}
        <flux:button href="{{ route('plots.plantings.index') }}" variant="outline" icon="scissors">
            {{ __('Plantaciones') }}
        </flux:button>

        {{-- Explorador de Mapas --}}
        <flux:button href="{{ route('plots.map') }}" variant="outline" icon="map">
            {{ __('Mapa') }}
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
                {{ __('Limpiar todo') }}
            </button>
        </div>

        {{-- Hint: si hay CA+provincia pero falta municipio, sugiere completar para ver el mapa --}}
        @if($filterAutonomousCommunity && $filterProvince && !$filterMunicipality)
            <p class="text-xs text-blue-600 flex items-center gap-1">
                <flux:icon icon="map" variant="micro" class="shrink-0" />
                {{ __('Selecciona también un municipio para poder ver todas sus parcelas en el mapa.') }}
            </p>
        @endif
    @endif

    {{-- Acciones masivas para municipio --}}
    @if ($filterAutonomousCommunity && $filterProvince && $filterMunicipality && $this->municipalityHasGeometry)
        <x-agro.card>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-lg bg-agro-50 flex items-center justify-center">
                        <flux:icon icon="map" class="size-6 text-agro-600" />
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-zinc-900">
                            {{ __('Acciones para') }} {{ $this->municipalities[$filterMunicipality] ?? __('Municipio') }}
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
                        <span wire:loading.remove wire:target="generateAllMapsForMunicipality">{{ __('Generar Todos los Mapas') }}</span>
                        <span wire:loading wire:target="generateAllMapsForMunicipality">{{ __('Generando...') }}</span>
                    </flux:button>
                    <flux:button
                        href="{{ route('map.municipality', $filterMunicipality) }}"
                        variant="primary"
                        icon="eye"
                    >
                        {{ __('Ver Todos los Mapas') }}
                    </flux:button>
                </div>
            </div>
        </x-agro.card>
    @endif

    {{-- Grid de Parcelas —— skeleton durante carga --}}
    <x-agro.loading-grid target="switchTab, search, filterAutonomousCommunity, filterProvince, filterMunicipality, nextPage, previousPage, gotoPage" />

    {{-- Grid de Parcelas —— contenido real --}}
    <div
        wire:loading.remove
        wire:target="switchTab, search, filterAutonomousCommunity, filterProvince, filterMunicipality, nextPage, previousPage, gotoPage"
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
                                    <p class="text-[10px] font-semibold text-agro-400 uppercase tracking-widest mb-1">{{ __('Superficie') }}</p>
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
                                    <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest mb-1">{{ __('SIGPAC') }}</p>
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
                            <div class="space-y-1.5 text-sm">
                                @if ($plot->code_parcel)
                                    <div class="flex items-center gap-2 text-zinc-600">
                                        <flux:icon icon="hashtag" class="size-4 text-zinc-400 shrink-0" />
                                        <span class="truncate text-xs font-mono">{{ $plot->code_parcel }}</span>
                                    </div>
                                @endif
                                @if ($plot->soilType)
                                    <div class="flex items-center gap-2 text-zinc-600">
                                        <flux:icon icon="beaker" class="size-4 text-zinc-400 shrink-0" />
                                        <span class="truncate">{{ __($plot->soilType->name) }}</span>
                                    </div>
                                @endif
                                @if ($plot->orientation || $plot->slope)
                                    <div class="flex items-center gap-2 text-zinc-600">
                                        <flux:icon icon="arrow-trending-up" class="size-4 text-zinc-400 shrink-0" />
                                        <span class="truncate">
                                            {{ $plot->orientation?->name ?? '' }}{{ $plot->orientation && $plot->slope ? ' · ' : '' }}{{ $plot->slope ? $plot->slope . '% ' . __('pend.') : '' }}
                                        </span>
                                    </div>
                                @endif
                                @if ($plot->total_vines || $plot->oldest_planting_year)
                                    <div class="flex items-center gap-2 text-zinc-600">
                                        <flux:icon icon="sparkles" class="size-4 text-zinc-400 shrink-0" />
                                        <span class="truncate">
                                            {{ $plot->total_vines ? number_format($plot->total_vines, 0, ',', '.') . ' ' . __('cepas') : '' }}{{ $plot->total_vines && $plot->oldest_planting_year ? ' · ' : '' }}{{ $plot->oldest_planting_year ? __('Plantación') . ' ' . $plot->oldest_planting_year : '' }}
                                        </span>
                                    </div>
                                @endif
                                @if ($plot->active_plantings_count > 0)
                                    <div class="flex items-center gap-2 text-zinc-600">
                                        <flux:icon icon="book-open" class="size-4 text-zinc-400 shrink-0" />
                                        <span class="truncate">
                                            {{ $plot->active_plantings_count }} {{ $plot->active_plantings_count === 1 ? __('plantación') : __('plantaciones') }}
                                            @php $varieties = $plot->plantings->pluck('grapeVariety.name')->filter()->unique()->take(3); @endphp
                                            @if ($varieties->isNotEmpty())
                                                · {{ $varieties->implode(', ') }}{{ $plot->plantings->pluck('grapeVariety.name')->filter()->unique()->count() > 3 ? '…' : '' }}
                                            @endif
                                        </span>
                                    </div>
                                @endif
                                <div class="flex items-center gap-2 text-zinc-600">
                                    <flux:icon icon="user" class="size-4 text-zinc-400 shrink-0" />
                                    <span class="truncate">
                                        {{ $plot->viticulturist?->name }}
                                        @if ($plot->viticulturist && $plot->viticulturist->id === auth()->id())
                                            <span class="text-agro-600 font-semibold">({{ __('Yo') }})</span>
                                        @endif
                                    </span>
                                </div>
                            </div>

                        </div>

                        {{-- Footer: acciones --}}
                        <x-slot:footer>
                            <div class="flex items-center justify-between">
                                {{-- Izquierda: navegar --}}
                                <div class="flex items-center gap-0.5">
                                    <x-agro.action-button variant="view" href="{{ route('plots.show', $plot) }}" :title="__('Ver parcela')" />
                                    @if ($hasMap)
                                        <x-agro.action-button variant="map" href="{{ route('map', ['id' => $plot->id, 'return' => 'plots']) }}" :title="__('Ver mapa')" />
                                    @elseif($plot->sigpacCodes->isNotEmpty())
                                        @can('update', $plot)
                                            <x-agro.action-button icon="cpu-chip" variant="default" wire:click="generateMap(null, {{ $plot->id }})" :title="__('Generar mapa desde SIGPAC')" />
                                        @endcan
                                    @elseif($plot->code_parcel)
                                        @can('update', $plot)
                                            <x-agro.action-button icon="map" variant="default" wire:click="generateMapFromCatastro({{ $plot->id }})" :title="__('Generar mapa desde catastro')" />
                                        @endcan
                                    @endif
                                    @can('update', $plot)
                                        <x-agro.action-button icon="scissors" variant="default" href="{{ route('plots.plantings.create', $plot) }}" :title="__('Nueva plantación')" />
                                    @endcan
                                    <x-agro.action-button icon="list-bullet" variant="default" wire:click="selectAuditPlot({{ $plot->id }})" :title="__('Historial de cambios')" />
                                </div>

                                {{-- Separador --}}
                                <div class="w-px h-5 bg-zinc-200 mx-1"></div>

                                {{-- Derecha: gestionar --}}
                                <div class="flex items-center gap-0.5">
                                    @can('update', $plot)
                                        <x-agro.action-button variant="edit" href="{{ route('plots.edit', $plot) }}" :title="__('Editar')" />
                                        @if ($plot->active)
                                            <x-agro.action-button variant="deactivate" wire:click="toggleActive({{ $plot->id }})" :title="__('Desactivar')" />
                                        @else
                                            <x-agro.action-button variant="activate" wire:click="toggleActive({{ $plot->id }})" :title="__('Activar')" />
                                        @endif
                                    @endcan
                                </div>
                            </div>
                        </x-slot:footer>
                    </x-agro.card>
                @endforeach
            </div>

            <x-agro.pagination :paginator="$plots" />
        @else
            <x-agro.empty-state
                :message="__('No hay parcelas') . ' ' . ($currentTab === 'active' ? __('activas') : __('inactivas'))"
                :description="$currentTab === 'active' ? __('Comienza agregando tu primera parcela al sistema') : __('Todas las parcelas están actualmente activas')"
                icon="inbox"
            >
                @if ($currentTab === 'active')
                    @can('create', \App\Models\Plot::class)
                        <x-slot name="action">
                            <flux:button href="{{ roleRoute('plots.create') }}" variant="primary" icon="plus">
                                {{ __('Crear mi primera parcela') }}
                            </flux:button>
                        </x-slot>
                    @endcan
                @endif
            </x-agro.empty-state>
        @endif
    </div>

    {{-- Modal: Filtros --}}
    <x-agro.filter-modal
        name="plot-filters"
        :hasActiveFilters="(bool) ($filterAutonomousCommunity || $filterProvince || $filterMunicipality)"
        clearAction="$set('filterAutonomousCommunity', ''); $set('filterProvince', ''); $set('filterMunicipality', '')"
    >
        <div>
            <x-agro.field-label>{{ __('Comunidad Autónoma') }}</x-agro.field-label>
            <flux:select wire:model.live="filterAutonomousCommunity">
                <option value="">{{ __('Todas las comunidades') }}</option>
                @foreach ($this->autonomousCommunities as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </flux:select>
        </div>
        @if ($filterAutonomousCommunity)
            <div>
                <x-agro.field-label>{{ __('Provincia') }}</x-agro.field-label>
                <flux:select wire:model.live="filterProvince">
                    <option value="">{{ __('Todas las provincias') }}</option>
                    @foreach ($this->provinces as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </flux:select>
            </div>
        @endif
        @if ($filterProvince)
            <div>
                <x-agro.field-label>{{ __('Municipio') }}</x-agro.field-label>
                <flux:select wire:model.live="filterMunicipality">
                    <option value="">{{ __('Todos los municipios') }}</option>
                    @foreach ($this->municipalities as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </flux:select>
            </div>
        @endif
    </x-agro.filter-modal>


    {{-- Modal: Historial de Auditoría --}}
    <x-agro.modal name="plot-audit" maxWidth="3xl">
        <div class="bg-zinc-50 px-6 py-4 border-b border-zinc-200 rounded-t-xl">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-zinc-900">{{ __('Historial de Auditoría') }}</h3>
                <flux:button x-on:click="$dispatch('close-modal', 'plot-audit')" variant="ghost" size="sm" icon="x-mark" />
            </div>
        </div>
        <div class="px-6 py-4 max-h-[65vh] overflow-y-auto">
            @if ($auditPlot)
                @livewire('viticulturist.plots.plot-audit-history', ['plot' => $auditPlot], key('audit-' . $auditPlot->id))
            @else
                <div class="py-8 text-center text-zinc-500">
                    <flux:icon icon="clock" class="size-10 mx-auto mb-2 text-zinc-300" />
                    <p>{{ __('Selecciona una parcela para ver su historial') }}</p>
                </div>
            @endif
        </div>
        <div class="bg-zinc-50 px-6 py-3 border-t border-zinc-200 rounded-b-xl flex justify-end">
            <flux:button x-on:click="$dispatch('close-modal', 'plot-audit')" variant="outline">{{ __('Cerrar') }}</flux:button>
        </div>
    </x-agro.modal>

</div>
