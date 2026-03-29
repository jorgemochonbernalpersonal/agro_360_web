{{-- Vista Visual Winery: full-height, elimina padding del layout --}}
<style>
</style>
<div class="-mx-4 lg:-mx-8 -my-4 lg:-my-8 bg-white relative" style="height: calc(100vh - 4rem);">
<div x-data="{ tab: @js($activeTab) }" @set-active-tab.window="tab = $event.detail.tab" class="flex flex-col h-full overflow-hidden">

    {{-- ══ Top bar: tabs descriptivos + volver ══ --}}
    @php
        $kgFmt      = $dashboardStats['kg_received'] >= 1000
            ? number_format($dashboardStats['kg_received']/1000,1).'t'
            : number_format($dashboardStats['kg_received'],0).'kg';
        $totalHa    = collect($mapPlots)->sum(fn($p) => (float)($p['area'] ?? 0));
        $haFmt      = $totalHa > 0 ? number_format($totalHa,1).' ha' : count($mapPlots).' parc.';
        $wineryAlert = $containerStats['full'] + $containerStats['critical'];
    @endphp
    <div class="shrink-0 flex items-stretch bg-white border-b border-zinc-200">

        {{-- Tab: Resumen --}}
        <button @click="tab = 'dashboard'"
                :class="tab === 'dashboard' ? 'bg-agro-50' : 'hover:bg-zinc-50'"
                class="group flex items-center gap-3 px-5 py-3.5 transition-colors relative">
            <span x-show="tab === 'dashboard'" class="absolute bottom-0 inset-x-0 h-0.5 bg-agro-500 rounded-t"></span>
            <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0"
                 :class="tab === 'dashboard' ? 'bg-agro-100' : 'bg-zinc-100 group-hover:bg-zinc-200'">
                <span :class="tab === 'dashboard' ? 'text-agro-600' : 'text-zinc-400'"><flux:icon icon="chart-bar" class="size-4" /></span>
            </div>
            <div class="text-left hidden md:block">
                <p class="text-sm font-semibold leading-tight" :class="tab === 'dashboard' ? 'text-agro-700' : 'text-zinc-600'">Resumen</p>
                <p class="text-[10px] leading-tight mt-0.5 text-zinc-400">{{ $kgFmt }} · {{ $dashboardStats['campaign_year'] }}</p>
            </div>
        </button>

        {{-- Tab: Mapa de parcelas --}}
        <button @click="tab = 'plots'; $dispatch('plots-activated')"
                :class="tab === 'plots' ? 'bg-blue-50' : 'hover:bg-zinc-50'"
                class="group flex items-center gap-3 px-5 py-3.5 transition-colors relative">
            <span x-show="tab === 'plots'" class="absolute bottom-0 inset-x-0 h-0.5 bg-blue-500 rounded-t"></span>
            <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0"
                 :class="tab === 'plots' ? 'bg-blue-100' : 'bg-zinc-100 group-hover:bg-zinc-200'">
                <span :class="tab === 'plots' ? 'text-blue-600' : 'text-zinc-400'"><flux:icon icon="map-pin" class="size-4" /></span>
            </div>
            <div class="text-left hidden md:block">
                <p class="text-sm font-semibold leading-tight" :class="tab === 'plots' ? 'text-blue-700' : 'text-zinc-600'">Mapa de parcelas</p>
                <p class="text-[10px] leading-tight mt-0.5 text-zinc-400">{{ count($mapPlots) }} parcelas · {{ $haFmt }}</p>
            </div>
        </button>

        {{-- Tab: Bodega --}}
        <button @click="tab = 'containers'"
                :class="tab === 'containers' ? 'bg-violet-50' : 'hover:bg-zinc-50'"
                class="group flex items-center gap-3 px-5 py-3.5 transition-colors relative">
            <span x-show="tab === 'containers'" class="absolute bottom-0 inset-x-0 h-0.5 bg-violet-500 rounded-t"></span>
            <div class="relative w-8 h-8 rounded-xl flex items-center justify-center shrink-0"
                 :class="tab === 'containers' ? 'bg-violet-100' : 'bg-zinc-100 group-hover:bg-zinc-200'">
                <span :class="tab === 'containers' ? 'text-violet-600' : 'text-zinc-400'"><flux:icon icon="beaker" class="size-4" /></span>
                @if($wineryAlert > 0)
                <span class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 text-white text-[9px] font-black rounded-full flex items-center justify-center">{{ $wineryAlert }}</span>
                @endif
            </div>
            <div class="text-left hidden md:block">
                <p class="text-sm font-semibold leading-tight" :class="tab === 'containers' ? 'text-violet-700' : 'text-zinc-600'">Bodega</p>
                <p class="text-[10px] leading-tight mt-0.5 text-zinc-400">
                    {{ $containerStats['total'] }} depósitos ·
                    @if($wineryAlert > 0)<span class="text-red-400 font-semibold">{{ $wineryAlert }} alertas</span>@else{{ $containerStats['used_pct'] }}% ocupado @endif
                </p>
            </div>
        </button>

        {{-- Separador + Volver --}}
        <div class="ml-auto flex items-center px-4 border-l border-zinc-100">
            <a href="{{ route('winery.dashboard') }}" wire:navigate
               class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs text-zinc-500 hover:text-zinc-800 hover:bg-zinc-100 transition-colors border border-zinc-200 whitespace-nowrap">
                <flux:icon icon="bars-3" class="size-3.5 shrink-0" />
                Vista Sidebar
            </a>
        </div>

    </div>

    {{-- ══════════════════════════════════════════════════════ --}}
    {{--                   TAB: PARCELAS                       --}}
    {{-- ══════════════════════════════════════════════════════ --}}
    <div x-show="tab === 'plots'" style="{{ $activeTab !== 'plots' ? 'display:none' : '' }}"
         class="flex flex-1 overflow-hidden"
         @keydown.escape.window="tab === 'plots' && $wire.set('selectedPlotId', null)">
    <div wire:ignore
         x-data="visualPlotsMap(@js($mapPlots), @js($mapPolygons), @js($filterOptions), '{{ $mapTileMode }}', {{ $mapShowList ? 'true' : 'false' }}, {{ $selectedPlotId ?? 'null' }})"
         class="flex flex-col flex-1 overflow-hidden">

        {{-- Barra de filtros --}}
        @if(count($mapPlots) > 0)
        <div class="shrink-0 flex items-center gap-3 px-6 py-3 bg-white border-b border-zinc-100 z-10 flex-wrap">
            {{-- Búsqueda --}}
            <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                    <flux:icon icon="magnifying-glass" class="size-4 text-zinc-400" />
                </div>
                <input x-model="search"
                       @input.debounce.250ms="updateMapData()"
                       type="text"
                       placeholder="Buscar parcela..."
                       class="pl-9 pr-4 py-2 bg-zinc-50 border border-zinc-200 rounded-xl text-sm w-44 placeholder:text-zinc-400 focus:outline-none focus:ring-2 focus:ring-agro-500 focus:border-transparent transition" />
            </div>
            {{-- CCAA --}}
            <select x-model="communityId"
                    @change="provinceId = ''; municipalityId = ''; updateMapData()"
                    x-show="filterOptions.communities.length > 1"
                    class="px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-xl text-sm text-zinc-700 focus:outline-none focus:ring-2 focus:ring-agro-500 focus:border-transparent transition max-w-[160px]">
                <option value="">Todas las CCAA</option>
                <template x-for="c in filterOptions.communities" :key="c.id">
                    <option :value="c.id" x-text="c.name"></option>
                </template>
            </select>
            {{-- Provincia --}}
            <select x-model="provinceId"
                    @change="municipalityId = ''; updateMapData()"
                    x-show="availableProvinces.length > 1"
                    :disabled="availableProvinces.length === 0"
                    class="px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-xl text-sm text-zinc-700 focus:outline-none focus:ring-2 focus:ring-agro-500 focus:border-transparent transition max-w-[160px] disabled:opacity-40">
                <option value="">Todas las prov.</option>
                <template x-for="p in availableProvinces" :key="p.id">
                    <option :value="p.id" x-text="p.name"></option>
                </template>
            </select>
            {{-- Municipio --}}
            <select x-model="municipalityId"
                    @change="updateMapData()"
                    x-show="availableMunicipalities.length > 1"
                    :disabled="availableMunicipalities.length === 0"
                    class="px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-xl text-sm text-zinc-700 focus:outline-none focus:ring-2 focus:ring-agro-500 focus:border-transparent transition max-w-[160px] disabled:opacity-40">
                <option value="">Todos los mun.</option>
                <template x-for="m in availableMunicipalities" :key="m.id">
                    <option :value="m.id" x-text="m.name"></option>
                </template>
            </select>
            {{-- Limpiar --}}
            <button x-show="search || communityId || provinceId || municipalityId"
                    x-cloak
                    @click="clearFilters()"
                    class="flex items-center gap-1.5 text-xs text-zinc-400 hover:text-zinc-700 transition-colors px-2 py-1.5 rounded-lg hover:bg-zinc-100">
                <flux:icon icon="x-mark" class="size-3.5" />
                Limpiar
            </button>
            {{-- Toggle lista lateral --}}
            <button @click="showList = !showList; $wire.saveShowList(showList)"
                    title="Mostrar lista de parcelas"
                    class="flex items-center gap-1.5 px-2.5 py-2 rounded-xl border text-xs font-medium transition-all"
                    :class="showList ? 'bg-agro-50 border-agro-300 text-agro-700' : 'bg-zinc-50 border-zinc-200 text-zinc-500 hover:text-zinc-700 hover:bg-zinc-100'">
                <flux:icon icon="list-bullet" class="size-4 shrink-0" />
                <span class="hidden sm:inline">Lista</span>
            </button>
            <span class="ml-auto text-xs text-zinc-400 whitespace-nowrap">
                <span x-text="filteredCount"></span> parcelas
                <template x-if="filteredArea > 0">
                    <span>· <span x-text="filteredArea"></span> ha</span>
                </template>
            </span>
        </div>
        @endif

        {{-- Mapa + lista --}}
        <div class="flex flex-1 overflow-hidden">

        {{-- Lista lateral colapsable --}}
        <div x-show="showList" x-cloak
             class="w-56 shrink-0 border-r border-zinc-200 bg-white flex flex-col overflow-hidden">
            <div class="px-3 py-2.5 border-b border-zinc-100 flex items-center justify-between">
                <p class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest">
                    <span x-text="filteredCount"></span> parcelas
                </p>
                <button @click="showList = false; $wire.saveShowList(false)" class="text-zinc-300 hover:text-zinc-500 transition-colors">
                    <flux:icon icon="x-mark" class="size-3.5" />
                </button>
            </div>
            <div class="flex-1 overflow-y-auto py-1.5">
                <template x-for="plot in filteredPlots" :key="plot.id">
                    <button @click="$wire.selectPlot(plot.id)"
                            :data-plot-id="plot.id"
                            class="w-full text-left flex items-center gap-2 px-3 py-2 transition-colors"
                            :class="selectedPlotId === plot.id
                                ? 'bg-agro-50 text-agro-700'
                                : 'text-zinc-700 hover:bg-zinc-50'">
                        <span class="w-2.5 h-2.5 rounded-sm shrink-0 ring-1 ring-black/10"
                              :style="`background:${getPlotColor(plot.id)}`"></span>
                        <span class="truncate text-xs font-medium" x-text="plot.name"></span>
                        <span class="ml-auto text-[10px] text-zinc-400 shrink-0"
                              x-text="plot.area ? parseFloat(plot.area).toFixed(1)+'ha' : ''"></span>
                    </button>
                </template>
            </div>
        </div>

        {{-- Mapa Leaflet --}}
        <div class="flex-1 relative" data-map-wrap>
            @if(count($mapPlots) > 0)
                <div wire:ignore
                     x-init="initMap()"
                     class="w-full h-full">
                    <div id="visual-plots-map" class="w-full h-full"></div>
                </div>


                {{-- Botones flotantes top-right (fullscreen + tile toggle) --}}
                <div class="absolute top-3 right-3 z-[1000] flex items-center gap-1.5">
                    <button @click="toggleFullscreen()"
                            title="Pantalla completa"
                            class="flex items-center justify-center w-8 h-8 rounded-xl text-xs font-semibold shadow-lg border transition-all bg-white/90 backdrop-blur-sm border-zinc-200 text-zinc-600 hover:bg-white hover:shadow-xl">
                        <template x-if="!isFullscreen">
                            <flux:icon icon="arrows-pointing-out" class="size-4" />
                        </template>
                        <template x-if="isFullscreen">
                            <flux:icon icon="arrows-pointing-in" class="size-4" />
                        </template>
                    </button>
                    <button @click="toggleTile()"
                            :title="tileMode === 'satellite' ? 'Cambiar a callejero' : 'Cambiar a satélite'"
                            class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold shadow-lg border transition-all
                                   bg-white/90 backdrop-blur-sm border-zinc-200 text-zinc-700 hover:bg-white hover:shadow-xl">
                        <template x-if="tileMode === 'satellite'">
                            <span class="flex items-center gap-1.5">
                                <flux:icon icon="globe-europe-africa" class="size-3.5 text-agro-500" />
                                <span>Satélite</span>
                                <flux:icon icon="arrows-right-left" class="size-3 text-zinc-400" />
                            </span>
                        </template>
                        <template x-if="tileMode === 'street'">
                            <span class="flex items-center gap-1.5">
                                <flux:icon icon="map" class="size-3.5 text-zinc-500" />
                                <span>Callejero</span>
                                <flux:icon icon="arrows-right-left" class="size-3 text-zinc-400" />
                            </span>
                        </template>
                    </button>
                </div>
            @else
                <div class="flex flex-col items-center justify-center h-full bg-zinc-50 gap-4">
                    <div class="w-16 h-16 bg-zinc-100 rounded-2xl flex items-center justify-center">
                        <flux:icon icon="map" class="size-8 text-zinc-300" />
                    </div>
                    <div class="text-center">
                        <p class="font-semibold text-zinc-500">Sin coordenadas disponibles</p>
                        <p class="text-sm text-zinc-400 mt-1">Las parcelas necesitan municipio con coordenadas para aparecer en el mapa</p>
                    </div>
                </div>
            @endif
        </div>

        </div>{{-- /Mapa + lista --}}
    </div>{{-- /visualPlotsMap x-data --}}

        {{-- Panel derecho: detalle parcela --}}
        @if ($selectedPlot)
        <div wire:key="plot-panel-{{ $selectedPlot->id }}" class="w-80 shrink-0 border-l border-zinc-200 bg-white overflow-y-auto flex flex-col shadow-lg">

            {{-- Header --}}
            <div class="sticky top-0 bg-white border-b border-zinc-100 px-4 py-3 flex items-start justify-between z-10">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 bg-agro-100 rounded-lg flex items-center justify-center shrink-0">
                            <flux:icon icon="map-pin" class="size-3.5 text-agro-600" />
                        </div>
                        <h3 class="font-bold text-zinc-900 text-sm truncate">{{ $selectedPlot->name }}</h3>
                    </div>
                    <p class="text-xs text-zinc-400 mt-1 pl-9">
                        {{ $selectedPlot->municipality?->name }}{{ $selectedPlot->province ? ', ' . $selectedPlot->province->name : '' }}
                    </p>
                </div>
                <button wire:click="selectPlot({{ $selectedPlot->id }})"
                        class="ml-2 shrink-0 w-6 h-6 flex items-center justify-center rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors">
                    <flux:icon icon="x-mark" class="size-3.5" />
                </button>
            </div>

            <div class="flex-1 p-4 space-y-4">

                {{-- KPIs + viticulturist --}}
                <div class="space-y-2">
                    <div class="grid grid-cols-2 gap-2">
                        @if($selectedPlot->area)
                        <div class="bg-agro-50 rounded-xl p-3">
                            <p class="text-[9px] font-semibold text-agro-400 uppercase tracking-widest">Superficie</p>
                            <p class="text-xl font-black text-agro-700 mt-0.5">
                                {{ number_format($selectedPlot->area, 2) }}
                                <span class="text-xs font-normal text-agro-400">ha</span>
                            </p>
                        </div>
                        @endif
                        @php $varieties = $selectedPlot->plantings->pluck('grapeVariety')->filter()->unique('id'); @endphp
                        @if($varieties->isNotEmpty())
                        <div class="bg-amber-50 rounded-xl p-3">
                            <p class="text-[9px] font-semibold text-amber-400 uppercase tracking-widest mb-1.5">Variedades</p>
                            <div class="flex flex-wrap gap-1">
                                @foreach($varieties->take(3) as $v)
                                <span class="text-[9px] font-bold text-amber-700 bg-amber-100 rounded-md px-1.5 py-0.5 leading-tight">{{ $v->name }}</span>
                                @endforeach
                                @if($varieties->count() > 3)
                                <span class="text-[9px] text-amber-400 self-center">+{{ $varieties->count() - 3 }}</span>
                                @endif
                            </div>
                        </div>
                        @elseif($selectedPlot->sigpacCodes->isNotEmpty())
                        <div class="bg-amber-50 rounded-xl p-3">
                            <p class="text-[9px] font-semibold text-amber-400 uppercase tracking-widest">SIGPAC</p>
                            <p class="text-xl font-black text-amber-700 mt-0.5">
                                {{ $selectedPlot->sigpacCodes->count() }}
                                <span class="text-xs font-normal text-amber-400">rec.</span>
                            </p>
                        </div>
                        @endif
                    </div>

                    {{-- Última actividad --}}
                    @if($selectedPlotLastActivity)
                    @php
                        $actLabels = ['phytosanitary' => 'Tratamiento', 'fertilization' => 'Fertilización',
                                      'irrigation' => 'Riego', 'cultural' => 'Labor', 'observation' => 'Observación',
                                      'harvest' => 'Vendimia', 'post_harvest' => 'Post-vendimia'];
                        $actColors = ['phytosanitary' => 'text-orange-600 bg-orange-50',
                                      'fertilization' => 'text-green-600 bg-green-50',
                                      'irrigation' => 'text-blue-600 bg-blue-50',
                                      'cultural' => 'text-zinc-600 bg-zinc-100',
                                      'observation' => 'text-violet-600 bg-violet-50',
                                      'harvest' => 'text-agro-600 bg-agro-50',
                                      'post_harvest' => 'text-amber-600 bg-amber-50'];
                        $actType = $selectedPlotLastActivity->activity_type;
                    @endphp
                    <div class="flex items-center gap-2 px-3 py-2 bg-zinc-50 rounded-xl">
                        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded {{ $actColors[$actType] ?? 'text-zinc-500 bg-zinc-100' }}">
                            {{ $actLabels[$actType] ?? $actType }}
                        </span>
                        <span class="text-xs text-zinc-500 flex-1 truncate">
                            {{ $selectedPlotLastActivity->activity_date?->diffForHumans() }}
                        </span>
                    </div>
                    @endif

                    {{-- Chip viticulturist: enlaza a su ficha --}}
                    @if($selectedPlot->viticulturist)
                    <a href="{{ route('winery.viticulturists.show', $selectedPlot->viticulturist) }}" wire:navigate
                       class="flex items-center gap-2.5 text-sm text-zinc-600 bg-zinc-50 hover:bg-zinc-100 rounded-xl px-3 py-2.5 transition-colors group">
                        <div class="w-6 h-6 bg-zinc-200 group-hover:bg-zinc-300 rounded-full flex items-center justify-center shrink-0 transition-colors">
                            <flux:icon icon="user" class="size-3 text-zinc-500" />
                        </div>
                        <span class="truncate flex-1">{{ $selectedPlot->viticulturist->name }}</span>
                        <flux:icon icon="chevron-right" class="size-3.5 text-zinc-300 group-hover:text-zinc-500 shrink-0 transition-colors" />
                    </a>
                    @endif
                </div>

                {{-- ── Registrar actividad (solo productor) ── --}}
                @if(auth()->user()->isProducer())
                <div>
                    <p class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest mb-1.5 px-1">Registrar actividad</p>
                    <div class="grid grid-cols-2 gap-1.5">
                        <a href="{{ route('viticulturist.digital-notebook.treatment.create', ['plot_id' => $selectedPlot->id]) }}" wire:navigate
                           class="flex flex-col items-center gap-1 px-2 py-2.5 rounded-xl bg-orange-50 hover:bg-orange-100 text-orange-700 transition-colors text-center">
                            <flux:icon icon="shield-exclamation" class="size-4" />
                            <span class="text-[10px] font-semibold leading-tight">Tratamiento</span>
                        </a>
                        <a href="{{ route('viticulturist.digital-notebook.irrigation.create', ['plot_id' => $selectedPlot->id]) }}" wire:navigate
                           class="flex flex-col items-center gap-1 px-2 py-2.5 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 transition-colors text-center">
                            <flux:icon icon="beaker" class="size-4" />
                            <span class="text-[10px] font-semibold leading-tight">Riego</span>
                        </a>
                        <a href="{{ route('viticulturist.digital-notebook.cultural.create', ['plot_id' => $selectedPlot->id]) }}" wire:navigate
                           class="flex flex-col items-center gap-1 px-2 py-2.5 rounded-xl bg-green-50 hover:bg-green-100 text-green-700 transition-colors text-center">
                            <flux:icon icon="wrench-screwdriver" class="size-4" />
                            <span class="text-[10px] font-semibold leading-tight">Trab. campo</span>
                        </a>
                        <a href="{{ route('viticulturist.digital-notebook.observation.create', ['plot_id' => $selectedPlot->id]) }}" wire:navigate
                           class="flex flex-col items-center gap-1 px-2 py-2.5 rounded-xl bg-zinc-100 hover:bg-zinc-200 text-zinc-700 transition-colors text-center">
                            <flux:icon icon="eye" class="size-4" />
                            <span class="text-[10px] font-semibold leading-tight">Observación</span>
                        </a>
                    </div>
                </div>
                @endif

                {{-- ── Esta parcela ── --}}
                <div>
                    <p class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest mb-1.5 px-1">Esta parcela</p>
                    <div class="space-y-0.5">
                        <a href="{{ route('winery.plots.show', $selectedPlot) }}?from=visual" wire:navigate
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-zinc-700 hover:bg-agro-50 hover:text-agro-700 transition-colors group">
                            <flux:icon icon="eye" class="size-4 text-zinc-400 group-hover:text-agro-600 shrink-0" />
                            Ver ficha completa
                        </a>
                        <a href="{{ route('winery.plots.edit', $selectedPlot) }}" wire:navigate
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-zinc-700 hover:bg-zinc-50 hover:text-zinc-900 transition-colors group">
                            <flux:icon icon="pencil-square" class="size-4 text-zinc-400 group-hover:text-zinc-600 shrink-0" />
                            Editar
                        </a>
                        <a href="{{ route('winery.plots.plantings.create', $selectedPlot) }}" wire:navigate
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-zinc-700 hover:bg-zinc-50 transition-colors group">
                            <flux:icon icon="scissors" class="size-4 text-zinc-400 shrink-0" />
                            Nueva plantación
                        </a>
                        @php $hasMap = $selectedPlot->multiplePlotSigpacs->filter(fn($m) => $m->plotGeometry !== null)->isNotEmpty(); @endphp
                        @if($hasMap)
                        <a href="{{ route('map', ['id' => $selectedPlot->id, 'return' => 'plots']) }}" wire:navigate
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-zinc-700 hover:bg-amber-50 hover:text-amber-700 transition-colors group">
                            <flux:icon icon="map" class="size-4 text-zinc-400 group-hover:text-amber-600 shrink-0" />
                            Mapa SIGPAC detallado
                        </a>
                        @endif
                    </div>
                </div>

                <div class="border-t border-zinc-100"></div>

                {{-- ── Vendimia ── --}}
                <div>
                    <p class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest mb-2 px-1">Vendimia</p>
                    <button wire:click="openModalGrapeReception({{ $selectedPlot->viticulturist?->id ?? 'null' }})"
                            class="flex items-center justify-center gap-2 w-full px-3 py-2.5 mb-2 rounded-xl text-sm font-semibold bg-agro-600 text-white hover:bg-agro-700 transition-colors shadow-sm">
                        <flux:icon icon="archive-box-arrow-down" class="size-4 shrink-0" />
                        Recibir uva
                    </button>
                    @if($selectedPlot->viticulturist)
                    <a href="{{ route('winery.grape-reception.index', ['viticulturist_id' => $selectedPlot->viticulturist->id]) }}" wire:navigate
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-zinc-700 hover:bg-zinc-50 transition-colors group">
                        <flux:icon icon="clipboard-document-list" class="size-4 text-zinc-400 shrink-0" />
                        Recepciones de {{ $selectedPlot->viticulturist->name }}
                    </a>
                    @else
                    <a href="{{ route('winery.grape-reception.index') }}" wire:navigate
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-zinc-700 hover:bg-zinc-50 transition-colors group">
                        <flux:icon icon="clipboard-document-list" class="size-4 text-zinc-400 shrink-0" />
                        Ver recepciones
                    </a>
                    @endif
                </div>

                <div class="border-t border-zinc-100"></div>

                {{-- ── Cerrar (sticky bottom) ── --}}
                <button wire:click="$set('selectedPlotId', null)"
                        class="w-full flex items-center justify-center gap-2 px-3 py-2.5 rounded-xl text-sm text-zinc-400 hover:text-zinc-700 hover:bg-zinc-50 border border-zinc-100 transition-colors">
                    <flux:icon icon="x-mark" class="size-4 shrink-0" />
                    Cerrar panel
                </button>

                {{-- ── Explorar ── --}}
                <div x-data="{ open: false }">
                    <button @click="open = !open"
                            class="flex items-center justify-between w-full px-1 mb-1 group">
                        <p class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest group-hover:text-zinc-500 transition-colors">Explorar</p>
                        <flux:icon icon="chevron-down" class="size-3.5 text-zinc-300 transition-transform duration-200"
                                   ::class="open ? 'rotate-180' : ''" />
                    </button>
                    <div x-show="open" x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0"
                         style="display:none;"
                         class="space-y-0.5">
                        <a href="{{ route('winery.field-activities.index') . '?plot_id=' . $selectedPlot->id }}" wire:navigate
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-zinc-700 hover:bg-zinc-50 transition-colors group">
                            <flux:icon icon="pencil-square" class="size-4 text-zinc-400 shrink-0" />
                            Actividades de esta parcela
                        </a>
                        <a href="{{ route('remote-sensing.dashboard') }}" wire:navigate
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-zinc-700 hover:bg-zinc-50 transition-colors group">
                            <flux:icon icon="globe-alt" class="size-4 text-zinc-400 shrink-0" />
                            Teledetección
                        </a>
                    </div>
                </div>

            </div>
        </div>

        @else
        {{-- Sin selección --}}
        <div wire:key="plot-panel-empty" class="w-72 shrink-0 border-l border-zinc-100 bg-zinc-50/80 flex flex-col items-center justify-center gap-4 text-zinc-400 p-6">
            <div class="w-14 h-14 bg-zinc-100 rounded-2xl flex items-center justify-center">
                <flux:icon icon="map-pin" class="size-7 text-zinc-300" />
            </div>
            <div class="text-center">
                <p class="text-sm font-semibold text-zinc-500">Selecciona una parcela</p>
                <p class="text-xs mt-1 leading-relaxed">Pulsa en un polígono del mapa para ver todas las opciones disponibles</p>
            </div>
        </div>
        @endif

</div>{{-- /x-show plots --}}

    {{-- ══════════════════════════════════════════════════════ --}}
    {{--                  TAB: BODEGA/CONTENEDORES             --}}
    {{-- ══════════════════════════════════════════════════════ --}}
    <div x-show="tab === 'containers'" style="{{ $activeTab !== 'containers' ? 'display:none' : '' }}"
         class="flex flex-1 overflow-hidden">

        {{-- Grid de contenedores --}}
        <div class="flex-1 flex flex-col overflow-hidden">

            {{-- Toolbar fijo --}}
            <div class="shrink-0 flex items-center gap-3 px-6 py-3 bg-white border-b border-zinc-100 z-10">
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                        <flux:icon icon="magnifying-glass" class="size-4 text-zinc-400" />
                    </div>
                    <input wire:model.live.debounce.300ms="containerSearch"
                           type="text"
                           placeholder="Buscar contenedor..."
                           class="pl-9 pr-4 py-2 bg-zinc-50 border border-zinc-200 rounded-xl text-sm w-52 placeholder:text-zinc-400 focus:outline-none focus:ring-2 focus:ring-agro-500 focus:border-transparent transition" />
                </div>
                <flux:select wire:model.live="containerTypeFilter" size="sm" class="w-36">
                    <flux:select.option value="">Todos los tipos</flux:select.option>
                    @foreach($containerTypes as $type)
                        <flux:select.option value="{{ $type->id }}">{{ $type->name }}</flux:select.option>
                    @endforeach
                </flux:select>
                @if($containerSearch || $containerTypeFilter)
                    <button wire:click="$set('containerSearch', ''); $set('containerTypeFilter', '')"
                            class="flex items-center gap-1.5 text-xs text-zinc-400 hover:text-zinc-700 transition-colors px-2 py-1.5 rounded-lg hover:bg-zinc-100">
                        <flux:icon icon="x-mark" class="size-3.5" />
                        Limpiar
                    </button>
                @endif
                {{-- Ordenación --}}
                <div class="flex items-center gap-0.5 bg-zinc-100 rounded-lg p-0.5 text-xs shrink-0">
                    <button wire:click="$set('containerSort', 'name')"
                            title="Ordenar A–Z"
                            class="px-2.5 py-1 rounded-md font-medium transition-all {{ $containerSort === 'name' ? 'bg-white shadow-sm text-zinc-800' : 'text-zinc-400 hover:text-zinc-600' }}">
                        A–Z
                    </button>
                    <button wire:click="$set('containerSort', 'pct_desc')"
                            title="Más llenos primero"
                            class="flex items-center gap-1 px-2.5 py-1 rounded-md font-medium transition-all {{ $containerSort === 'pct_desc' ? 'bg-white shadow-sm text-zinc-800' : 'text-zinc-400 hover:text-zinc-600' }}">
                        <flux:icon icon="arrow-down" class="size-3" />%
                    </button>
                    <button wire:click="$set('containerSort', 'pct_asc')"
                            title="Más vacíos primero"
                            class="flex items-center gap-1 px-2.5 py-1 rounded-md font-medium transition-all {{ $containerSort === 'pct_asc' ? 'bg-white shadow-sm text-zinc-800' : 'text-zinc-400 hover:text-zinc-600' }}">
                        <flux:icon icon="arrow-up" class="size-3" />%
                    </button>
                </div>
                <a href="{{ roleRoute('containers.create') }}" wire:navigate class="ml-auto">
                    <flux:button variant="primary" icon="plus" size="sm">Nuevo contenedor</flux:button>
                </a>
            </div>

            {{-- Barra de capacidad total bodega --}}
            @if($containerStats['total'] > 0 && $containerStats['total_capacity_kg'] > 0)
            <div class="shrink-0 px-6 py-3 border-b border-zinc-100 bg-zinc-50/60">
                <div class="flex items-center justify-between mb-1.5 text-xs">
                    <span class="font-semibold text-zinc-600">Capacidad total bodega</span>
                    <span class="text-zinc-400">
                        <span class="font-bold text-zinc-700">{{ number_format($containerStats['used_pct']) }}%</span>
                        uva ·
                        <span class="font-medium text-amber-600">{{ number_format($containerStats['total_used_kg'], 0) }} kg</span>
                        uva +
                        <span class="font-medium text-violet-600">{{ number_format($containerStats['total_wine_liters'], 0) }} L</span>
                        vino /
                        <span class="text-zinc-500">{{ number_format($containerStats['total_capacity_kg'], 0) }} kg</span>
                    </span>
                </div>
                <div class="h-2.5 bg-zinc-200 rounded-full overflow-hidden flex">
                    @php
                        $uvaBarPct  = min($containerStats['used_pct'], 100);
                        $wineBarPct = $containerStats['total_capacity_kg'] > 0
                            ? min(round($containerStats['total_wine_liters'] / $containerStats['total_capacity_kg'] * 100), 100 - $uvaBarPct)
                            : 0;
                        $barColor   = $uvaBarPct >= 90 ? 'bg-red-500' : ($uvaBarPct >= 75 ? 'bg-amber-400' : 'bg-agro-500');
                    @endphp
                    <div class="{{ $barColor }} h-full transition-all duration-700 rounded-l-full"
                         style="width: {{ $uvaBarPct }}%"></div>
                    @if($wineBarPct > 0)
                    <div class="bg-violet-400 h-full transition-all duration-700"
                         style="width: {{ $wineBarPct }}%"></div>
                    @endif
                </div>
                <div class="flex items-center gap-3 mt-1.5 text-[10px] text-zinc-400">
                    <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full {{ $barColor }}"></span>Uva</span>
                    <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-violet-400"></span>Vino</span>
                    <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-zinc-200"></span>Libre</span>
                </div>
            </div>
            @endif

            {{-- Grid scrollable --}}
            <div class="flex-1 overflow-y-auto p-6"
                 wire:loading.class="opacity-50 pointer-events-none"
                 wire:target="containerSearch, containerTypeFilter">
                @if($containers->count() > 0)
                @php
                    $grouped  = $containers->groupBy(fn($c) => $c->containerRoom?->name ?? '');
                    $hasRooms = $grouped->keys()->filter(fn($k) => $k !== '')->isNotEmpty();
                    // Ordenar grupos: salas con nombre primero (alfabético), sin sala al final
                    $sortedGroups = $grouped->sortKeysUsing(fn($a, $b) =>
                        ($a === '' ? 1 : 0) - ($b === '' ? 1 : 0) ?: strcmp($a, $b)
                    );
                @endphp
                <div class="space-y-5">
                @foreach($sortedGroups as $roomName => $roomContainers)
                    @if($hasRooms)
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <flux:icon icon="{{ $roomName ? 'home-modern' : 'square-3-stack-3d' }}" class="size-3.5 {{ $roomName ? 'text-zinc-400' : 'text-zinc-300' }}" />
                            <p class="text-[10px] font-bold {{ $roomName ? 'text-zinc-500' : 'text-zinc-300' }} uppercase tracking-widest">
                                {{ $roomName ?: 'Sin sala' }}
                            </p>
                            <span class="text-[9px] text-zinc-300 font-medium">{{ $roomContainers->count() }}</span>
                        </div>
                    @endif
                        <div class="grid gap-3"
                             style="grid-template-columns: repeat(auto-fill, minmax(90px, 1fr));">
                            @foreach($roomContainers as $container)
                                @php
                                    $pct         = $container->getOccupancyPercentage();
                                    $fillColor   = $pct >= 90 ? '#ef4444' : ($pct >= 75 ? '#f59e0b' : ($pct > 0 ? '#4a7c59' : 'transparent'));
                                    $borderColor = $pct >= 90 ? '#ef4444' : ($pct >= 75 ? '#f59e0b' : ($pct > 0 ? '#4a7c59' : '#d4d4d8'));
                                    $textColor   = $pct > 55 ? '#ffffff' : '#374151';
                                    $isSelected  = $selectedContainerId === $container->id;
                                @endphp
                                <button
                                    wire:click="selectContainer({{ $container->id }})"
                                    wire:key="vis-container-{{ $container->id }}"
                                    title="{{ $container->name }} — {{ round($pct) }}%"
                                    class="flex flex-col items-center gap-2 p-2.5 rounded-2xl transition-all duration-150 cursor-pointer group
                                           {{ $isSelected ? 'bg-agro-50 ring-2 ring-agro-400 shadow-md' : 'hover:bg-zinc-50 hover:shadow-sm' }}"
                                >
                                    <div class="relative rounded-xl overflow-hidden flex-shrink-0"
                                         style="width: 52px; height: 88px; background: #f4f4f5; border: 2px solid {{ $isSelected ? '#4ade80' : $borderColor }}; transition: border-color 0.3s;">
                                        <div class="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-px"
                                             style="width: 20px; height: 5px; background: {{ $isSelected ? '#4ade80' : $borderColor }}; border-radius: 3px 3px 0 0; transition: background 0.3s;"></div>
                                        @if($pct > 0)
                                        <div class="absolute bottom-0 left-0 right-0"
                                             style="height: {{ min($pct, 100) }}%; background-color: {{ $fillColor }}; opacity: 0.75; transition: height 0.8s ease-out;"></div>
                                        @endif
                                        <div class="absolute inset-x-1.5 inset-y-0 flex flex-col justify-evenly pointer-events-none">
                                            <div class="border-b border-zinc-400/20"></div>
                                            <div class="border-b border-zinc-400/20"></div>
                                            <div class="border-b border-zinc-400/20"></div>
                                        </div>
                                        <div class="absolute inset-0 flex items-center justify-center">
                                            <span class="text-[9px] font-black leading-none select-none drop-shadow-sm"
                                                  style="color: {{ $textColor }};">{{ round($pct) }}%</span>
                                        </div>
                                    </div>
                                    <p class="text-[9px] font-semibold text-zinc-700 text-center leading-tight w-full truncate">
                                        {{ $container->name }}
                                    </p>
                                    @if($container->containerType)
                                    <p class="text-[8px] text-zinc-400 text-center w-full truncate -mt-1">
                                        {{ $container->containerType->name }}
                                    </p>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    @if($hasRooms)
                    </div>
                    @endif
                @endforeach
                </div>
                @else
                <div class="flex flex-col items-center justify-center h-64 text-zinc-400 gap-3">
                    <div class="w-14 h-14 bg-zinc-100 rounded-2xl flex items-center justify-center">
                        <flux:icon icon="beaker" class="size-7 text-zinc-300" />
                    </div>
                    <div class="text-center">
                        <p class="font-semibold text-zinc-500">No hay contenedores</p>
                        <p class="text-sm mt-1">{{ $containerSearch || $containerTypeFilter ? 'Sin resultados para los filtros' : 'Crea tu primer contenedor' }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Panel derecho: detalle contenedor --}}
        @if ($selectedContainer)
        <div class="w-80 shrink-0 border-l border-zinc-200 bg-white overflow-y-auto flex flex-col shadow-lg">

            {{-- Header --}}
            <div class="sticky top-0 bg-white border-b border-zinc-100 px-4 py-3 flex items-start justify-between z-10">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 bg-violet-100 rounded-lg flex items-center justify-center shrink-0">
                            <flux:icon icon="beaker" class="size-3.5 text-violet-600" />
                        </div>
                        <h3 class="font-bold text-zinc-900 text-sm truncate">{{ $selectedContainer->name }}</h3>
                    </div>
                    <p class="text-xs text-zinc-400 mt-1 pl-9">
                        {{ $selectedContainer->containerType?->name }}
                        @if($selectedContainer->containerRoom) · {{ $selectedContainer->containerRoom->name }} @endif
                        @if($selectedContainer->containerMaterial) · {{ $selectedContainer->containerMaterial->name }} @endif
                    </p>
                </div>
                <button wire:click="selectContainer({{ $selectedContainer->id }})"
                        class="ml-2 shrink-0 w-6 h-6 flex items-center justify-center rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors">
                    <flux:icon icon="x-mark" class="size-3.5" />
                </button>
            </div>

            <div class="flex-1 p-4 space-y-5">

                {{-- Stock visual grande --}}
                @php
                    $cpct = $selectedContainer->getOccupancyPercentage();
                    $progressColor = $cpct >= 90 ? 'bg-red-500' : ($cpct >= 75 ? 'bg-amber-400' : 'bg-emerald-500');
                    $textPctColor  = $cpct >= 90 ? 'text-red-600' : ($cpct >= 75 ? 'text-amber-600' : 'text-emerald-600');
                @endphp
                <div class="bg-zinc-50 rounded-2xl p-4">
                    <div class="flex items-end justify-between mb-2">
                        <span class="text-xs text-zinc-500 font-medium">Ocupación</span>
                        <span class="text-3xl font-black {{ $textPctColor }}">
                            {{ round($cpct) }}<span class="text-sm font-normal text-zinc-400">%</span>
                        </span>
                    </div>
                    <div class="h-3 bg-zinc-200 rounded-full overflow-hidden">
                        <div class="{{ $progressColor }} h-full rounded-full transition-all duration-700"
                             style="width: {{ min($cpct, 100) }}%"></div>
                    </div>
                    <div class="grid grid-cols-3 gap-2 mt-4">
                        <div class="bg-amber-50 rounded-xl p-2.5 text-center">
                            <p class="text-[8px] text-amber-400 uppercase font-bold tracking-wider">Uva</p>
                            <p class="text-sm font-black text-amber-700 mt-0.5">{{ number_format($selectedContainer->used_capacity, 0) }}</p>
                            <p class="text-[8px] text-amber-400">kg</p>
                        </div>
                        <div class="bg-violet-50 rounded-xl p-2.5 text-center">
                            <p class="text-[8px] text-violet-400 uppercase font-bold tracking-wider">Vino</p>
                            <p class="text-sm font-black text-violet-700 mt-0.5">{{ number_format($selectedContainer->wine_volume_liters, 0) }}</p>
                            <p class="text-[8px] text-violet-400">L</p>
                        </div>
                        <div class="bg-zinc-100 rounded-xl p-2.5 text-center">
                            <p class="text-[8px] text-zinc-400 uppercase font-bold tracking-wider">Cap.</p>
                            <p class="text-sm font-black text-zinc-600 mt-0.5">{{ number_format($selectedContainer->capacity, 0) }}</p>
                            <p class="text-[8px] text-zinc-400">kg</p>
                        </div>
                    </div>
                    @if($selectedContainer->currentState?->wine)
                    <div class="mt-3 flex items-center gap-2 text-xs text-zinc-600 bg-white rounded-xl px-3 py-2">
                        <flux:icon icon="arrows-right-left" class="size-3.5 text-violet-400 shrink-0" />
                        <span class="truncate">{{ $selectedContainer->currentState->wine->name }}</span>
                    </div>
                    @endif
                </div>

                {{-- ── Acciones rápidas ── --}}
                <div>
                    <p class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest mb-1.5 px-1">Acciones</p>
                    <div class="space-y-0.5">
                        <a href="{{ roleRoute('containers.show', $selectedContainer) }}?from=visual" wire:navigate
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-zinc-700 hover:bg-agro-50 hover:text-agro-700 transition-colors group">
                            <flux:icon icon="eye" class="size-4 text-zinc-400 group-hover:text-agro-600 shrink-0" />
                            Ver detalle completo
                        </a>
                        <a href="{{ roleRoute('containers.edit', $selectedContainer) }}" wire:navigate
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-zinc-700 hover:bg-zinc-50 hover:text-zinc-900 transition-colors group">
                            <flux:icon icon="pencil" class="size-4 text-zinc-400 group-hover:text-zinc-600 shrink-0" />
                            Editar contenedor
                        </a>
                        @if($selectedContainer->wine_volume_liters > 0)
                        <button wire:click="emptyWine({{ $selectedContainer->id }})"
                                wire:confirm="¿Vaciar el vino de «{{ $selectedContainer->name }}»?"
                                class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-zinc-700 hover:bg-orange-50 hover:text-orange-700 transition-colors group">
                            <flux:icon icon="arrow-path" class="size-4 text-zinc-400 group-hover:text-orange-600 shrink-0" />
                            Vaciar vino elaborado
                        </button>
                        @endif
                        <button wire:click="archiveContainer({{ $selectedContainer->id }})"
                                wire:confirm="¿Desactivar «{{ $selectedContainer->name }}»?"
                                class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-zinc-700 hover:bg-red-50 hover:text-red-700 transition-colors group">
                            <flux:icon icon="no-symbol" class="size-4 text-zinc-400 group-hover:text-red-600 shrink-0" />
                            Desactivar contenedor
                        </button>
                    </div>
                </div>

                <div class="border-t border-zinc-100"></div>

                {{-- ── Elaboración (acciones contextuales) ── --}}
                <div>
                    <p class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest mb-1.5 px-1">Elaboración</p>
                    <div class="space-y-0.5">
                        <button wire:click="openModalFermentation({{ $selectedContainer->id }})"
                                class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-zinc-700 hover:bg-violet-50 hover:text-violet-700 transition-colors group">
                            <flux:icon icon="beaker" class="size-4 text-zinc-400 group-hover:text-violet-600 shrink-0" />
                            Nuevo control de fermentación
                        </button>
                        <button wire:click="openModalTransfer({{ $selectedContainer->id }})"
                                class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-zinc-700 hover:bg-blue-50 hover:text-blue-700 transition-colors group">
                            <flux:icon icon="arrows-right-left" class="size-4 text-zinc-400 group-hover:text-blue-600 shrink-0" />
                            Nuevo trasvase de vino
                        </button>
                        <a href="{{ route('winery.wine-analysis.create', ['container_id' => $selectedContainer->id]) }}" wire:navigate
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-zinc-700 hover:bg-zinc-50 transition-colors group">
                            <flux:icon icon="magnifying-glass" class="size-4 text-zinc-400 shrink-0" />
                            Nuevo análisis de laboratorio
                        </a>
                        <a href="{{ roleRoute('containers.maintenance.index', $selectedContainer) }}" wire:navigate
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-zinc-700 hover:bg-zinc-50 transition-colors group">
                            <flux:icon icon="wrench-screwdriver" class="size-4 text-zinc-400 shrink-0" />
                            Mantenimientos
                        </a>
                        <a href="{{ roleRoute('containers.additives.index', $selectedContainer) }}" wire:navigate
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-zinc-700 hover:bg-zinc-50 transition-colors group">
                            <flux:icon icon="archive-box" class="size-4 text-zinc-400 shrink-0" />
                            Aditivos
                        </a>
                    </div>
                </div>

                {{-- ── Cerrar (sticky bottom) ── --}}
                <button wire:click="$set('selectedContainerId', null)"
                        class="w-full flex items-center justify-center gap-2 px-3 py-2.5 rounded-xl text-sm text-zinc-400 hover:text-zinc-700 hover:bg-zinc-50 border border-zinc-100 transition-colors">
                    <flux:icon icon="x-mark" class="size-4 shrink-0" />
                    Cerrar panel
                </button>

            </div>
        </div>

        @else
        {{-- Sin selección --}}
        <div class="w-72 shrink-0 border-l border-zinc-100 bg-zinc-50/80 flex flex-col items-center justify-center gap-4 text-zinc-400 p-6">
            <div class="w-14 h-14 bg-zinc-100 rounded-2xl flex items-center justify-center">
                <flux:icon icon="cursor-arrow-rays" class="size-7 text-zinc-300" />
            </div>
            <div class="text-center">
                <p class="text-sm font-semibold text-zinc-500">Selecciona un contenedor</p>
                <p class="text-xs mt-1 leading-relaxed">Pulsa en un depósito para ver todas las opciones disponibles</p>
            </div>
        </div>
        @endif

    </div>{{-- /x-show containers --}}

    {{-- ══════════════════════════════════════════════════════ --}}
    {{--                   TAB: DASHBOARD                      --}}
    {{-- ══════════════════════════════════════════════════════ --}}
    <div x-show="tab === 'dashboard'" style="{{ $activeTab !== 'dashboard' ? 'display:none' : '' }}"
         class="flex-1 overflow-y-auto">
        <div class="max-w-5xl mx-auto p-6 space-y-6">

            {{-- ── Acciones rápidas ── --}}
            <div class="flex flex-wrap gap-2">
                <button wire:click="openModalGrapeReception()"
                        class="flex items-center gap-2 px-4 py-2 bg-agro-600 hover:bg-agro-700 text-white text-sm font-semibold rounded-xl shadow-sm transition-colors">
                    <flux:icon icon="archive-box-arrow-down" class="size-4 shrink-0" />
                    Recibir uva
                </button>
                <button wire:click="openModalFermentation()"
                        class="flex items-center gap-2 px-4 py-2 bg-white hover:bg-zinc-50 text-zinc-700 text-sm font-medium rounded-xl border border-zinc-200 shadow-sm transition-colors">
                    <flux:icon icon="beaker" class="size-4 shrink-0 text-violet-500" />
                    Nuevo control
                </button>
                <button wire:click="openModalWine()"
                        class="flex items-center gap-2 px-4 py-2 bg-white hover:bg-zinc-50 text-zinc-700 text-sm font-medium rounded-xl border border-zinc-200 shadow-sm transition-colors">
                    <flux:icon icon="plus" class="size-4 shrink-0 text-agro-500" />
                    Nuevo vino
                </button>
                <button wire:click="openModalContainer()"
                        class="flex items-center gap-2 px-4 py-2 bg-white hover:bg-zinc-50 text-zinc-700 text-sm font-medium rounded-xl border border-zinc-200 shadow-sm transition-colors">
                    <flux:icon icon="plus" class="size-4 shrink-0 text-zinc-400" />
                    Nuevo contenedor
                </button>
            </div>

            {{-- ── KPI cards ── --}}
            <div class="grid grid-cols-3 gap-4">
                {{-- Kg recibidos --}}
                <div class="bg-white rounded-2xl border border-zinc-100 shadow-sm p-5">
                    <p class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest mb-3">Uva recibida {{ $dashboardStats['campaign_year'] }}</p>
                    <p class="text-3xl font-black text-zinc-900 leading-none">
                        @if($dashboardStats['kg_received'] >= 1000)
                            {{ number_format($dashboardStats['kg_received'] / 1000, 1) }}<span class="text-lg font-semibold text-zinc-400 ml-1">t</span>
                        @else
                            {{ number_format($dashboardStats['kg_received'], 0) }}<span class="text-lg font-semibold text-zinc-400 ml-1">kg</span>
                        @endif
                    </p>
                    <div class="flex items-center gap-1.5 mt-3">
                        <div class="w-6 h-6 bg-amber-100 rounded-lg flex items-center justify-center">
                            <flux:icon icon="archive-box-arrow-down" class="size-3.5 text-amber-600" />
                        </div>
                        <a href="{{ route('winery.grape-reception.index') }}" wire:navigate class="text-xs text-zinc-400 hover:text-amber-600 transition-colors">Ver recepciones</a>
                    </div>
                </div>

                {{-- Vinos en elaboración --}}
                <div class="bg-white rounded-2xl border border-zinc-100 shadow-sm p-5">
                    <p class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest mb-3">En elaboración</p>
                    <p class="text-3xl font-black text-violet-700 leading-none">
                        {{ $dashboardStats['wines_in_progress'] }}<span class="text-lg font-semibold text-violet-300 ml-1">vinos</span>
                    </p>
                    <div class="flex items-center gap-1.5 mt-3">
                        <div class="w-6 h-6 bg-violet-100 rounded-lg flex items-center justify-center">
                            <flux:icon icon="beaker" class="size-3.5 text-violet-600" />
                        </div>
                        <span class="text-xs text-zinc-400">
                            <a href="{{ roleRoute('wines.index') }}" wire:navigate class="hover:text-violet-600 transition-colors">Ver todos los vinos</a>
                        </span>
                    </div>
                </div>

                {{-- Fermentaciones activas --}}
                <div class="bg-white rounded-2xl border border-zinc-100 shadow-sm p-5">
                    <p class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest mb-3">Fermentaciones activas</p>
                    <p class="text-3xl font-black leading-none {{ $dashboardStats['active_fermentations'] > 0 ? 'text-agro-700' : 'text-zinc-300' }}">
                        {{ $dashboardStats['active_fermentations'] }}<span class="text-lg font-semibold {{ $dashboardStats['active_fermentations'] > 0 ? 'text-agro-400' : 'text-zinc-300' }} ml-1">activas</span>
                    </p>
                    <div class="flex items-center gap-1.5 mt-3">
                        <div class="w-6 h-6 bg-agro-100 rounded-lg flex items-center justify-center">
                            <flux:icon icon="arrow-trending-up" class="size-3.5 text-agro-600" />
                        </div>
                        <span class="text-xs text-zinc-400">Últimos 7 días · brix > 2</span>
                    </div>
                </div>
            </div>

            {{-- ── Campaign hero card ── --}}
            <div class="rounded-2xl p-6 text-white overflow-hidden relative"
                 style="background: linear-gradient(135deg, #2d5a3d 0%, #4a7c59 60%, #6aaa7a 100%);">
                <div class="absolute top-0 right-0 w-48 h-48 rounded-full opacity-10" style="background: radial-gradient(circle, #fff 0%, transparent 70%); transform: translate(30%, -30%);"></div>
                <div class="flex items-start justify-between relative z-10">
                    <div>
                        <p class="text-white/60 text-xs font-semibold uppercase tracking-widest mb-1">Campaña activa</p>
                        <p class="text-4xl font-black">{{ $dashboardStats['campaign_year'] }}</p>
                    </div>
                    <span class="px-3 py-1 bg-white/15 rounded-full text-xs font-bold border border-white/20">EN CURSO</span>
                </div>
                <div class="grid grid-cols-3 gap-4 mt-6 relative z-10">
                    <div>
                        <p class="text-white/50 text-[9px] uppercase tracking-widest font-bold mb-1">Depósitos</p>
                        <p class="text-2xl font-black">{{ $dashboardStats['containers_total'] }}</p>
                        @if($dashboardStats['containers_critical'] > 0)
                        <p class="text-[10px] text-red-300 font-semibold mt-0.5">{{ $dashboardStats['containers_critical'] }} críticos</p>
                        @endif
                    </div>
                    <div>
                        <p class="text-white/50 text-[9px] uppercase tracking-widest font-bold mb-1">Vinos</p>
                        <p class="text-2xl font-black">{{ $dashboardStats['wines_in_progress'] }}</p>
                        <p class="text-[10px] text-white/50 font-semibold mt-0.5">en elaboración</p>
                    </div>
                    <div>
                        <p class="text-white/50 text-[9px] uppercase tracking-widest font-bold mb-1">Ferment.</p>
                        <p class="text-2xl font-black">{{ $dashboardStats['active_fermentations'] }}</p>
                        <p class="text-[10px] text-white/50 font-semibold mt-0.5">activas</p>
                    </div>
                </div>
            </div>

            {{-- ── Dos columnas: depósitos críticos + controles recientes ── --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

                {{-- Depósitos críticos --}}
                <div class="bg-white rounded-2xl border border-zinc-100 shadow-sm overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-zinc-50">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></div>
                            <p class="text-sm font-bold text-zinc-800">Depósitos críticos</p>
                            <span class="text-xs text-zinc-400">(≥ 85%)</span>
                        </div>
                        <a href="{{ roleRoute('containers.index') }}" wire:navigate class="text-xs text-agro-600 hover:text-agro-800 font-medium transition-colors">Ver todos</a>
                    </div>
                    @if($criticalContainers->isEmpty())
                    <div class="flex flex-col items-center justify-center py-10 text-zinc-300 gap-2">
                        <flux:icon icon="check-circle" class="size-8" />
                        <p class="text-sm text-zinc-400">Sin depósitos críticos</p>
                    </div>
                    @else
                    <div class="divide-y divide-zinc-50">
                        @foreach($criticalContainers as $cc)
                        @php $cpct = $cc->getOccupancyPercentage(); @endphp
                        <button wire:click="openContainer({{ $cc->id }})"
                                class="w-full flex items-center gap-3 px-5 py-3 hover:bg-zinc-50 transition-colors text-left group">
                            {{-- Mini depósito --}}
                            <div class="relative rounded-md overflow-hidden flex-shrink-0"
                                 style="width: 18px; height: 30px; background: #f4f4f5; border: 1.5px solid {{ $cpct >= 90 ? '#ef4444' : '#f59e0b' }};">
                                <div class="absolute bottom-0 left-0 right-0"
                                     style="height: {{ min($cpct, 100) }}%; background: {{ $cpct >= 90 ? '#ef4444' : '#f59e0b' }}; opacity: 0.7;"></div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-zinc-800 truncate">{{ $cc->name }}</p>
                                <p class="text-xs text-zinc-400 truncate">{{ $cc->containerType?->name }}</p>
                            </div>
                            <span class="text-sm font-black {{ $cpct >= 90 ? 'text-red-600' : 'text-amber-600' }} shrink-0">
                                {{ round($cpct) }}%
                            </span>
                        </button>
                        @endforeach
                    </div>
                    @endif
                </div>

                {{-- Últimos controles de fermentación --}}
                <div class="bg-white rounded-2xl border border-zinc-100 shadow-sm overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-zinc-50">
                        <p class="text-sm font-bold text-zinc-800">Últimos controles</p>
                        <a href="{{ route('winery.fermentation-controls.index') }}" wire:navigate class="text-xs text-agro-600 hover:text-agro-800 font-medium transition-colors">Ver todos</a>
                    </div>
                    @if($recentControls->isEmpty())
                    <div class="flex flex-col items-center justify-center py-10 text-zinc-300 gap-2">
                        <flux:icon icon="beaker" class="size-8" />
                        <p class="text-sm text-zinc-400">Sin controles registrados</p>
                    </div>
                    @else
                    <div class="divide-y divide-zinc-50">
                        @foreach($recentControls as $ctrl)
                        @php $hasContainer = (bool) $ctrl->container_id; @endphp
                        @if($hasContainer)
                        <button wire:click="openContainer({{ $ctrl->container_id }})"
                                class="w-full flex items-center gap-3 px-5 py-3 hover:bg-zinc-50 transition-colors text-left group">
                        @else
                        <div class="flex items-center gap-3 px-5 py-3">
                        @endif
                            <div class="w-7 h-7 rounded-lg bg-agro-50 flex items-center justify-center shrink-0">
                                <flux:icon icon="beaker" class="size-3.5 text-agro-600" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-zinc-800 truncate {{ $hasContainer ? 'group-hover:text-agro-700' : '' }}">
                                    {{ $ctrl->wine?->name ?? '—' }}
                                </p>
                                <p class="text-xs text-zinc-400 truncate">{{ $ctrl->container?->name }} · {{ $ctrl->control_date?->format('d/m H:i') }}</p>
                            </div>
                            <div class="text-right shrink-0">
                                @if($ctrl->temperature !== null)
                                <p class="text-xs font-bold text-zinc-700">{{ number_format($ctrl->temperature, 1) }}°</p>
                                @endif
                                @if($ctrl->brix_degree !== null)
                                <p class="text-[10px] {{ (float)$ctrl->brix_degree > 2 ? 'text-agro-600 font-bold' : 'text-zinc-400' }}">
                                    {{ number_format($ctrl->brix_degree, 1) }}°Bx
                                </p>
                                @endif
                                @if($hasContainer)
                                <flux:icon icon="chevron-right" class="size-3 text-zinc-200 group-hover:text-zinc-400 mt-0.5 ml-auto" />
                                @endif
                            </div>
                        @if($hasContainer)
                        </button>
                        @else
                        </div>
                        @endif
                        @endforeach
                    </div>
                    @endif
                </div>

            </div>
        </div>
    </div>
    </div>{{-- /x-show dashboard --}}

    {{-- ══════════════════════════════════════════════════════ --}}
    {{--          MODALES DE ACCIÓN RÁPIDA                     --}}
    {{-- ══════════════════════════════════════════════════════ --}}

    {{-- ── Modal: Recibir uva ─────────────────────────────── --}}
    <flux:modal wire:model="modalGrapeReception" name="modal-grape-reception" class="w-full max-w-lg">
        <div class="p-6 space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-agro-100 rounded-xl flex items-center justify-center shrink-0">
                    <flux:icon icon="archive-box-arrow-down" class="size-5 text-agro-600" />
                </div>
                <div>
                    <h3 class="text-base font-bold text-zinc-900">Recibir uva</h3>
                    <p class="text-xs text-zinc-400">Registro rápido de recepción</p>
                </div>
            </div>

            <div class="space-y-3">
                {{-- Viticultor --}}
                <div>
                    <label class="block text-xs font-semibold text-zinc-600 mb-1">Viticultor *</label>
                    <select wire:model.live="gr_viticulturistId"
                            class="w-full px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-agro-500">
                        <option value="">Seleccionar viticultor...</option>
                        @foreach($modalViticulturists as $v)
                            <option value="{{ $v->id }}">{{ $v->name }}</option>
                        @endforeach
                    </select>
                    @error('gr_viticulturistId') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Parcela --}}
                @if($gr_viticulturistId && count($gr_availablePlots))
                <div>
                    <label class="block text-xs font-semibold text-zinc-600 mb-1">Parcela *</label>
                    <select wire:model.live="gr_plotId"
                            class="w-full px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-agro-500">
                        <option value="">Seleccionar parcela...</option>
                        @foreach($gr_availablePlots as $p)
                            <option value="{{ $p['id'] }}">{{ $p['name'] }}</option>
                        @endforeach
                    </select>
                    @error('gr_plotId') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                @endif

                {{-- Plantación --}}
                @if($gr_plotId && count($gr_availablePlantings))
                <div>
                    <label class="block text-xs font-semibold text-zinc-600 mb-1">Variedad / Plantación *</label>
                    <select wire:model="gr_plantingId"
                            class="w-full px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-agro-500">
                        <option value="">Seleccionar plantación...</option>
                        @foreach($gr_availablePlantings as $p)
                            <option value="{{ $p['id'] }}">{{ $p['label'] }}</option>
                        @endforeach
                    </select>
                    @error('gr_plantingId') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                @endif

                <div class="grid grid-cols-2 gap-3">
                    {{-- Fecha --}}
                    <div>
                        <label class="block text-xs font-semibold text-zinc-600 mb-1">Fecha *</label>
                        <input type="date" wire:model="gr_harvestDate"
                               class="w-full px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-agro-500" />
                        @error('gr_harvestDate') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    {{-- Añada --}}
                    <div>
                        <label class="block text-xs font-semibold text-zinc-600 mb-1">Añada *</label>
                        <input type="number" wire:model="gr_vintageYear" min="2000" max="{{ now()->year + 1 }}"
                               class="w-full px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-agro-500" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    {{-- Kg recibidos --}}
                    <div>
                        <label class="block text-xs font-semibold text-zinc-600 mb-1">Kg recibidos *</label>
                        <input type="number" step="0.1" wire:model="gr_totalWeight" placeholder="0.0"
                               class="w-full px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-agro-500" />
                        @error('gr_totalWeight') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    {{-- Depósito --}}
                    <div>
                        <label class="block text-xs font-semibold text-zinc-600 mb-1">Depósito destino *</label>
                        <select wire:model="gr_containerId"
                                class="w-full px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-agro-500">
                            <option value="">Depósito...</option>
                            @foreach($modalContainersKg as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} ({{ number_format($c->capacity - $c->used_capacity, 0) }} kg lib.)</option>
                            @endforeach
                        </select>
                        @error('gr_containerId') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-2 border-t border-zinc-100">
                <flux:button variant="ghost" wire:click="$set('modalGrapeReception', false)">Cancelar</flux:button>
                <flux:button variant="primary" wire:click="saveGrapeReception()" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="saveGrapeReception">Registrar recepción</span>
                    <span wire:loading wire:target="saveGrapeReception">Guardando...</span>
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- ── Modal: Control de fermentación ─────────────────── --}}
    <flux:modal wire:model="modalFermentation" name="modal-fermentation" class="w-full max-w-md">
        <div class="p-6 space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-violet-100 rounded-xl flex items-center justify-center shrink-0">
                    <flux:icon icon="beaker" class="size-5 text-violet-600" />
                </div>
                <div>
                    <h3 class="text-base font-bold text-zinc-900">Control de fermentación</h3>
                    <p class="text-xs text-zinc-400">Registro rápido</p>
                </div>
            </div>

            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-semibold text-zinc-600 mb-1">Vino *</label>
                    <select wire:model="fc_wineId"
                            class="w-full px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-violet-500">
                        <option value="">Seleccionar vino...</option>
                        @foreach($modalWines as $w)
                            <option value="{{ $w->id }}">{{ $w->name }}</option>
                        @endforeach
                    </select>
                    @error('fc_wineId') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-zinc-600 mb-1">Contenedor *</label>
                    <select wire:model="fc_containerId"
                            class="w-full px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-violet-500">
                        <option value="">Seleccionar contenedor...</option>
                        @foreach($modalContainersAll as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                    @error('fc_containerId') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-zinc-600 mb-1">Fecha y hora *</label>
                    <input type="datetime-local" wire:model="fc_controlDate"
                           class="w-full px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-violet-500" />
                    @error('fc_controlDate') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-zinc-600 mb-1">Temperatura (°C)</label>
                        <input type="number" step="0.1" wire:model="fc_temperature" placeholder="—"
                               class="w-full px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-violet-500" />
                        @error('fc_temperature') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-zinc-600 mb-1">Brix</label>
                        <input type="number" step="0.1" wire:model="fc_brix" placeholder="—"
                               class="w-full px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-violet-500" />
                        @error('fc_brix') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-2 border-t border-zinc-100">
                <flux:button variant="ghost" wire:click="$set('modalFermentation', false)">Cancelar</flux:button>
                <flux:button variant="primary" wire:click="saveFermentationControl()" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="saveFermentationControl">Guardar control</span>
                    <span wire:loading wire:target="saveFermentationControl">Guardando...</span>
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- ── Modal: Trasvase ─────────────────────────────────── --}}
    <flux:modal wire:model="modalTransfer" name="modal-transfer" class="w-full max-w-md">
        <div class="p-6 space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-blue-100 rounded-xl flex items-center justify-center shrink-0">
                    <flux:icon icon="arrows-right-left" class="size-5 text-blue-600" />
                </div>
                <div>
                    <h3 class="text-base font-bold text-zinc-900">Nuevo trasvase</h3>
                    <p class="text-xs text-zinc-400">Registro rápido</p>
                </div>
            </div>

            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-semibold text-zinc-600 mb-1">Vino *</label>
                    <select wire:model="tr_wineId"
                            class="w-full px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Seleccionar vino...</option>
                        @foreach($modalWines as $w)
                            <option value="{{ $w->id }}">{{ $w->name }}</option>
                        @endforeach
                    </select>
                    @error('tr_wineId') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-zinc-600 mb-1">Desde contenedor</label>
                        <select wire:model="tr_fromContainerId"
                                class="w-full px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">— Ninguno —</option>
                            @foreach($modalContainersAll as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-zinc-600 mb-1">Hacia contenedor *</label>
                        <select wire:model="tr_toContainerId"
                                class="w-full px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Seleccionar...</option>
                            @foreach($modalContainersAll as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                        @error('tr_toContainerId') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-zinc-600 mb-1">Cantidad *</label>
                        <input type="number" step="0.01" wire:model="tr_quantity" placeholder="0.0"
                               class="w-full px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        @error('tr_quantity') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-zinc-600 mb-1">Unidad *</label>
                        <select wire:model="tr_unitId"
                                class="w-full px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Unidad...</option>
                            @foreach($modalUnits as $u)
                                <option value="{{ $u->id }}">{{ $u->symbol }}</option>
                            @endforeach
                        </select>
                        @error('tr_unitId') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-zinc-600 mb-1">Tipo *</label>
                        <select wire:model="tr_transferType"
                                class="w-full px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @foreach($transferTypes as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-zinc-600 mb-1">Fecha *</label>
                        <input type="date" wire:model="tr_transferDate"
                               class="w-full px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        @error('tr_transferDate') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-2 border-t border-zinc-100">
                <flux:button variant="ghost" wire:click="$set('modalTransfer', false)">Cancelar</flux:button>
                <flux:button variant="primary" wire:click="saveTransfer()" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="saveTransfer">Guardar trasvase</span>
                    <span wire:loading wire:target="saveTransfer">Guardando...</span>
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- ── Modal: Nuevo vino ───────────────────────────────── --}}
    <flux:modal wire:model="modalWine" name="modal-wine" class="w-full max-w-sm">
        <div class="p-6 space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-agro-100 rounded-xl flex items-center justify-center shrink-0">
                    <flux:icon icon="beaker" class="size-5 text-agro-600" />
                </div>
                <h3 class="text-base font-bold text-zinc-900">Nuevo vino</h3>
            </div>

            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-semibold text-zinc-600 mb-1">Nombre *</label>
                    <input type="text" wire:model="wine_name" placeholder="Nombre del vino..."
                           class="w-full px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-agro-500" />
                    @error('wine_name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-zinc-600 mb-1">Tipo *</label>
                    <select wire:model="wine_type"
                            class="w-full px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-agro-500">
                        <option value="">Seleccionar tipo...</option>
                        @foreach($wineTypes as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('wine_type') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-2 border-t border-zinc-100">
                <flux:button variant="ghost" wire:click="$set('modalWine', false)">Cancelar</flux:button>
                <flux:button variant="primary" wire:click="saveWine()" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="saveWine">Crear vino</span>
                    <span wire:loading wire:target="saveWine">Guardando...</span>
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- ── Modal: Nuevo contenedor ────────────────────────── --}}
    <flux:modal wire:model="modalContainer" name="modal-container" class="w-full max-w-sm">
        <div class="p-6 space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-violet-100 rounded-xl flex items-center justify-center shrink-0">
                    <flux:icon icon="archive-box" class="size-5 text-violet-600" />
                </div>
                <h3 class="text-base font-bold text-zinc-900">Nuevo contenedor</h3>
            </div>

            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-semibold text-zinc-600 mb-1">Nombre *</label>
                    <input type="text" wire:model="cont_name" placeholder="Ej: Depósito 101"
                           class="w-full px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-violet-500" />
                    @error('cont_name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-zinc-600 mb-1">Tipo *</label>
                    <select wire:model="cont_typeId"
                            class="w-full px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-violet-500">
                        <option value="">Seleccionar tipo...</option>
                        @foreach($containerTypes as $t)
                            <option value="{{ $t->id }}">{{ $t->name }}</option>
                        @endforeach
                    </select>
                    @error('cont_typeId') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-zinc-600 mb-1">Capacidad *</label>
                        <input type="number" step="1" wire:model="cont_capacity" placeholder="0"
                               class="w-full px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-violet-500" />
                        @error('cont_capacity') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-zinc-600 mb-1">Unidad *</label>
                        <select wire:model="cont_unit"
                                class="w-full px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-violet-500">
                            <option value="litros">Litros</option>
                            <option value="kg">Kg</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-2 border-t border-zinc-100">
                <flux:button variant="ghost" wire:click="$set('modalContainer', false)">Cancelar</flux:button>
                <flux:button variant="primary" wire:click="saveContainer()" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="saveContainer">Crear contenedor</span>
                    <span wire:loading wire:target="saveContainer">Guardando...</span>
                </flux:button>
            </div>
        </div>
    </flux:modal>

</div>{{-- /x-data tab --}}
</div>{{-- /root --}}

@script
<script>
Alpine.data('visualPlotsMap', (allPlots, allPolygons, filterOptions, initialTileMode = 'satellite', initialShowList = false, initialSelectedPlotId = null) => ({
    map: null,
    polygonLayers: {},
    selectedPlotId: initialSelectedPlotId,
    _plotsActivatedHandler: null,
    _fullscreenHandler: null,

    // Registra listeners con referencias para poder limpiarlos en destroy().
    init() {
        this._plotsActivatedHandler = () => {
            this.$nextTick(() => { if (this.map) this.map.invalidateSize(); });
        };
        window.addEventListener('plots-activated', this._plotsActivatedHandler);
    },

    // Limpia listeners y mapa al desmontar el componente (wire:navigate, etc.).
    destroy() {
        if (this._plotsActivatedHandler) {
            window.removeEventListener('plots-activated', this._plotsActivatedHandler);
        }
        if (this._fullscreenHandler) {
            document.removeEventListener('fullscreenchange', this._fullscreenHandler);
        }
        if (this.map) {
            this.map.remove();
            this.map = null;
        }
    },

    tileLayers: {},
    tileMode: initialTileMode,
    showList: initialShowList,
    isFullscreen: false,
    _plotColorMap: {},
    search: '',
    communityId: '',
    provinceId: '',
    municipalityId: '',
    filterOptions: filterOptions,

    // Paleta de colores distinguibles sobre satélite
    PALETTE: [
        '#ef4444','#f97316','#eab308','#22c55e',
        '#06b6d4','#3b82f6','#8b5cf6','#ec4899',
        '#14b8a6','#f59e0b','#84cc16','#6366f1',
    ],

    getPlotColor(plotId) {
        return this._plotColorMap[plotId] ?? this.PALETTE[plotId % this.PALETTE.length];
    },

    get availableProvinces() {
        if (!this.communityId) return this.filterOptions.provinces;
        return this.filterOptions.provinces.filter(p => p.community_id == this.communityId);
    },

    get availableMunicipalities() {
        if (this.provinceId) {
            return this.filterOptions.municipalities.filter(m => m.province_id == this.provinceId);
        }
        if (this.communityId) {
            const pIds = this.availableProvinces.map(p => p.id);
            return this.filterOptions.municipalities.filter(m => pIds.includes(m.province_id));
        }
        return this.filterOptions.municipalities;
    },

    get filteredPlots() {
        return allPlots.filter(p => {
            if (this.search && !p.name.toLowerCase().includes(this.search.toLowerCase())) return false;
            if (this.communityId && p.autonomous_community_id != this.communityId) return false;
            if (this.provinceId && p.province_id != this.provinceId) return false;
            if (this.municipalityId && p.municipality_id != this.municipalityId) return false;
            return true;
        });
    },

    get filteredCount() {
        return this.filteredPlots.length;
    },

    get filteredArea() {
        const sum = this.filteredPlots.reduce((acc, p) => acc + (parseFloat(p.area) || 0), 0);
        return sum > 0 ? sum.toFixed(2) : 0;
    },

    toggleFullscreen() {
        const el = document.getElementById('visual-plots-map')?.closest('[data-map-wrap]');
        if (!document.fullscreenElement) {
            (el || document.documentElement).requestFullscreen?.();
        } else {
            document.exitFullscreen?.();
        }
    },

    toggleTile() {
        if (!this.map) return;
        if (this.tileMode === 'satellite') {
            this.map.removeLayer(this.tileLayers.satellite);
            this.map.removeLayer(this.tileLayers.labels);
            this.map.addLayer(this.tileLayers.street);
            this.tileMode = 'street';
        } else {
            this.map.removeLayer(this.tileLayers.street);
            this.map.addLayer(this.tileLayers.satellite);
            this.map.addLayer(this.tileLayers.labels);
            this.tileMode = 'satellite';
        }
        $wire.saveTileMode(this.tileMode);
    },

    clearFilters() {
        this.search = '';
        this.communityId = '';
        this.provinceId = '';
        this.municipalityId = '';
        this.updateMapData();
    },

    // Devuelve un L.LatLngBounds combinando polígonos visibles
    _visibleBounds(filteredIds) {
        let bounds = null;
        const ext = (b) => { bounds = bounds ? bounds.extend(b) : (b instanceof L.LatLngBounds ? b : L.latLngBounds([b])); };
        Object.entries(this.polygonLayers).forEach(([plotId, layers]) => {
            if (!filteredIds.has(parseInt(plotId))) return;
            layers.forEach(l => ext(l.getBounds()));
        });
        return bounds;
    },

    // Aplica filtros: oculta/muestra polígonos y hace fitBounds
    updateMapData() {
        if (!this.map) return;
        const filteredIds = new Set(this.filteredPlots.map(p => p.id));

        Object.entries(this.polygonLayers).forEach(([plotId, layers]) => {
            const visible = filteredIds.has(parseInt(plotId));
            layers.forEach(layer => {
                if (visible && !this.map.hasLayer(layer)) layer.addTo(this.map);
                else if (!visible && this.map.hasLayer(layer)) this.map.removeLayer(layer);
            });
        });

        const bounds = this._visibleBounds(filteredIds);
        if (bounds && bounds.isValid()) {
            this.map.fitBounds(bounds, { padding: [50, 50], maxZoom: 17 });
        }
    },

    initMap() {
        const loadLeaflet = (cb) => {
            if (window.L) { cb(); return; }
            if (!document.getElementById('leaflet-css')) {
                const l = document.createElement('link');
                l.id = 'leaflet-css'; l.rel = 'stylesheet';
                l.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
                document.head.appendChild(l);
            }
            const s = document.createElement('script');
            s.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
            s.onload = cb;
            document.head.appendChild(s);
        };

        loadLeaflet(() => {
            if (this.map) this.map.remove();

            this.map = L.map('visual-plots-map', { zoomControl: false }).setView([40.0, -3.5], 6);

            this.tileLayers.satellite = L.tileLayer(
                'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
                { attribution: 'Tiles &copy; Esri', maxZoom: 19 }
            );
            this.tileLayers.labels = L.tileLayer(
                'https://server.arcgisonline.com/ArcGIS/rest/services/Reference/World_Boundaries_and_Places/MapServer/tile/{z}/{y}/{x}',
                { attribution: '', maxZoom: 19, opacity: 0.75 }
            );
            this.tileLayers.street = L.tileLayer(
                'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
                { attribution: '&copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a>', maxZoom: 19 }
            );
            // Capa inicial según preferencia guardada
            if (this.tileMode === 'street') {
                this.tileLayers.street.addTo(this.map);
            } else {
                this.tileLayers.satellite.addTo(this.map);
                this.tileLayers.labels.addTo(this.map);
            }

            L.control.zoom({ position: 'bottomleft' }).addTo(this.map);
            L.control.scale({ position: 'bottomright', imperial: false }).addTo(this.map);

            // ── Escuchar fullscreen ────────────────────────────────────────
            this._fullscreenHandler = () => {
                this.isFullscreen = !!document.fullscreenElement;
                setTimeout(() => this.map?.invalidateSize(), 100);
            };
            document.addEventListener('fullscreenchange', this._fullscreenHandler);

            // ── Click en fondo del mapa → cierra panel ─────────────────────
            this.map.on('click', () => $wire.set('selectedPlotId', null));

            // Índice: nombre de parcela por id
            const plotNames = {};
            allPlots.forEach(p => { plotNames[p.id] = p.name; });

            // Índice: área por plot id
            const plotArea = {};
            allPlots.forEach(p => { plotArea[p.id] = p.area ? parseFloat(p.area).toFixed(1) : null; });

            // ── Colores por variedad (mismo color = misma variedad) ─────────
            const varietyColorIdx = {};
            let nextColorIdx = 0;
            allPlots.forEach(p => {
                const key = p.variety_id != null ? `v${p.variety_id}` : `p${p.id}`;
                if (varietyColorIdx[key] === undefined) {
                    varietyColorIdx[key] = nextColorIdx++ % this.PALETTE.length;
                }
                this._plotColorMap[p.id] = this.PALETTE[varietyColorIdx[key]];
            });


            // ── Polígonos SIGPAC (colores por variedad) ─────────────────────
            allPolygons.forEach(poly => {
                if (!poly.coords || poly.coords.length < 3) return;
                const color = this.getPlotColor(poly.plot_id);
                const name  = plotNames[poly.plot_id] || poly.sigpac_code;
                const area  = plotArea[poly.plot_id];
                const layer = L.polygon(poly.coords, {
                    color,
                    fillColor: color,
                    fillOpacity: 0.3,
                    weight: 2,
                    opacity: 0.9,
                });
                layer.addTo(this.map);
                // Hover: iluminar polígono
                layer.on('mouseover', function() {
                    const isSelected = $wire.get('selectedPlotId') === poly.plot_id;
                    if (!isSelected) this.setStyle({ fillOpacity: 0.55, weight: 3.5, opacity: 1 });
                    this.bringToFront();
                });
                layer.on('mouseout', function() {
                    const isSelected = $wire.get('selectedPlotId') === poly.plot_id;
                    if (!isSelected) this.setStyle({ fillOpacity: 0.3, weight: 2, opacity: 0.9 });
                });
                // Click: seleccionar parcela (sin propagar al fondo del mapa)
                layer.on('click', (e) => {
                    L.DomEvent.stopPropagation(e);
                    $wire.selectPlot(poly.plot_id);
                });
                if (!this.polygonLayers[poly.plot_id]) this.polygonLayers[poly.plot_id] = [];
                this.polygonLayers[poly.plot_id].push(layer);
            });


            // fitBounds desde los propios polígonos (+ marcadores fallback)
            const allIds = new Set(allPlots.map(p => p.id));
            const initialBounds = this._visibleBounds(allIds);
            if (initialBounds && initialBounds.isValid()) {
                this.map.fitBounds(initialBounds, { padding: [50, 50], maxZoom: 17 });
            }

            // ── Al seleccionar parcela: resaltar + auto-zoom ────────────────
            $wire.$watch('selectedPlotId', (newId) => {
                this.selectedPlotId = newId;
                // Polígonos + zoom al seleccionado
                let selBounds = null;
                Object.entries(this.polygonLayers).forEach(([plotId, layers]) => {
                    const selected  = parseInt(plotId) === newId;
                    const baseColor = this.getPlotColor(parseInt(plotId));
                    layers.forEach(layer => {
                        layer.setStyle({
                            color:       selected ? '#ffffff' : baseColor,
                            fillColor:   baseColor,
                            fillOpacity: selected ? 0.5 : 0.3,
                            weight:      selected ? 3.5 : 2,
                        });
                        if (selected) {
                            selBounds = selBounds ? selBounds.extend(layer.getBounds()) : layer.getBounds();
                        }
                    });
                });
                if (selBounds && selBounds.isValid()) {
                    this.map.fitBounds(selBounds, { padding: [60, 60], maxZoom: 17, animate: true });
                }
                // Scroll lista lateral hasta la parcela seleccionada
                if (newId && this.showList) {
                    this.$nextTick(() => {
                        const btn = this.$el.querySelector(`[data-plot-id="${newId}"]`);
                        btn?.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
                    });
                }
            });
        });
    },
}));
</script>
@endscript
