{{-- Vista Visual Winery: full-height, elimina padding del layout --}}
<div class="-mx-4 lg:-mx-8 -my-4 lg:-my-8 flex flex-col bg-white" style="height: calc(100vh - 4rem);">

    {{-- ══ Top bar: tabs + stats + volver ══ --}}
    <div class="shrink-0 flex items-center gap-4 px-6 py-3 bg-white border-b border-zinc-200 z-20">

        {{-- Tabs --}}
        <div class="flex gap-1 bg-zinc-100 rounded-xl p-1 shrink-0">
            <button wire:click="switchTab('plots')"
                class="flex items-center gap-2 px-4 py-1.5 rounded-lg text-sm font-medium transition-all
                       {{ $activeTab === 'plots' ? 'bg-white shadow-sm text-zinc-800' : 'text-zinc-500 hover:text-zinc-700' }}">
                <flux:icon icon="map" class="size-4" />
                Parcelas
                <span class="text-[10px] font-semibold {{ $activeTab === 'plots' ? 'text-zinc-400' : 'text-zinc-400' }}">
                    {{ count($mapPlots) }}
                </span>
            </button>
            <button wire:click="switchTab('containers')"
                class="flex items-center gap-2 px-4 py-1.5 rounded-lg text-sm font-medium transition-all
                       {{ $activeTab === 'containers' ? 'bg-white shadow-sm text-zinc-800' : 'text-zinc-500 hover:text-zinc-700' }}">
                <flux:icon icon="beaker" class="size-4" />
                Bodega
                @if($containerStats['full'] > 0)
                    <span class="inline-flex items-center justify-center w-4 h-4 bg-red-500 text-white text-[9px] font-bold rounded-full">
                        {{ $containerStats['full'] }}
                    </span>
                @else
                    <span class="text-[10px] font-semibold text-zinc-400">{{ $containerStats['total'] }}</span>
                @endif
            </button>
        </div>

        {{-- Stats inline --}}
        @if ($activeTab === 'containers' && $containerStats['total'] > 0)
            <div class="hidden sm:flex items-center gap-3 text-xs">
                @if($containerStats['full'] > 0)
                    <span class="flex items-center gap-1 text-red-600 font-medium">
                        <span class="w-2 h-2 rounded-full bg-red-500"></span>
                        {{ $containerStats['full'] }} llenos
                    </span>
                @endif
                @if($containerStats['critical'] > 0)
                    <span class="flex items-center gap-1 text-amber-600 font-medium">
                        <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                        {{ $containerStats['critical'] }} críticos
                    </span>
                @endif
                @if($containerStats['empty'] > 0)
                    <span class="flex items-center gap-1 text-zinc-400">
                        <span class="w-2 h-2 rounded-full bg-zinc-300"></span>
                        {{ $containerStats['empty'] }} vacíos
                    </span>
                @endif
            </div>
        @endif

        {{-- Volver a vista sidebar --}}
        <a href="{{ route('winery.dashboard') }}" wire:navigate
           class="ml-auto flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm text-zinc-500 hover:text-zinc-800 hover:bg-zinc-100 transition-colors shrink-0">
            <flux:icon icon="bars-3" class="size-4" />
            <span class="hidden sm:inline">Vista Sidebar</span>
        </a>
    </div>

    {{-- ══════════════════════════════════════════════════════ --}}
    {{--                   TAB: PARCELAS                       --}}
    {{-- ══════════════════════════════════════════════════════ --}}
    @if ($activeTab === 'plots')
    <div class="flex flex-1 overflow-hidden">

        {{-- Mapa Leaflet --}}
        <div class="flex-1 relative">
            @if(count($mapPlots) > 0)
                <div wire:ignore
                     x-data="visualPlotsMap(@js($mapPlots))"
                     x-init="init()"
                     class="w-full h-full">
                    <div id="visual-plots-map" class="w-full h-full"></div>
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

        {{-- Panel derecho: detalle parcela --}}
        @if ($selectedPlot)
        <div class="w-80 shrink-0 border-l border-zinc-200 bg-white overflow-y-auto flex flex-col shadow-lg">

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

            <div class="flex-1 p-4 space-y-5">

                {{-- KPIs --}}
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
                    @if($selectedPlot->sigpacCodes->isNotEmpty())
                    <div class="bg-amber-50 rounded-xl p-3">
                        <p class="text-[9px] font-semibold text-amber-400 uppercase tracking-widest">SIGPAC</p>
                        <p class="text-xl font-black text-amber-700 mt-0.5">
                            {{ $selectedPlot->sigpacCodes->count() }}
                            <span class="text-xs font-normal text-amber-400">rec.</span>
                        </p>
                    </div>
                    @endif
                </div>

                @if($selectedPlot->viticulturist)
                <div class="flex items-center gap-2.5 text-sm text-zinc-600 bg-zinc-50 rounded-xl px-3 py-2.5">
                    <flux:icon icon="user" class="size-4 text-zinc-400 shrink-0" />
                    <span>{{ $selectedPlot->viticulturist->name }}</span>
                </div>
                @endif

                {{-- ── Parcela ── --}}
                <div>
                    <p class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest mb-1.5 px-1">Parcela</p>
                    <div class="space-y-0.5">
                        <a href="{{ route('winery.plots.show', $selectedPlot) }}" wire:navigate
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-zinc-700 hover:bg-agro-50 hover:text-agro-700 transition-colors group">
                            <flux:icon icon="eye" class="size-4 text-zinc-400 group-hover:text-agro-600 shrink-0" />
                            Ver parcela completa
                        </a>
                        <a href="{{ route('winery.plots.edit', $selectedPlot) }}" wire:navigate
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-zinc-700 hover:bg-zinc-50 hover:text-zinc-900 transition-colors group">
                            <flux:icon icon="pencil-square" class="size-4 text-zinc-400 group-hover:text-zinc-600 shrink-0" />
                            Editar parcela
                        </a>
                        <a href="{{ route('plots.plantings.index') }}" wire:navigate
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-zinc-700 hover:bg-zinc-50 transition-colors group">
                            <flux:icon icon="scissors" class="size-4 text-zinc-400 shrink-0" />
                            Plantaciones
                        </a>
                        @if($selectedPlot->sigpacCodes->isNotEmpty())
                        <a href="{{ route('sigpac.codes') }}" wire:navigate
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-zinc-700 hover:bg-amber-50 hover:text-amber-700 transition-colors group">
                            <flux:icon icon="rectangle-group" class="size-4 text-zinc-400 group-hover:text-amber-600 shrink-0" />
                            Códigos SIGPAC
                        </a>
                        @endif
                        @php $hasMap = $selectedPlot->multiplePlotSigpacs->filter(fn($m) => $m->plotGeometry !== null)->isNotEmpty(); @endphp
                        @if($hasMap)
                        <a href="{{ route('map', ['id' => $selectedPlot->id, 'return' => 'plots']) }}" wire:navigate
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-zinc-700 hover:bg-agro-50 hover:text-agro-700 transition-colors group">
                            <flux:icon icon="map" class="size-4 text-zinc-400 group-hover:text-agro-600 shrink-0" />
                            Ver mapa SIGPAC
                        </a>
                        @endif
                        <a href="{{ route('plots.territory') }}" wire:navigate
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-zinc-700 hover:bg-zinc-50 transition-colors group">
                            <flux:icon icon="globe-europe-africa" class="size-4 text-zinc-400 shrink-0" />
                            Gestión Territorial
                        </a>
                        <a href="{{ route('remote-sensing.dashboard') }}" wire:navigate
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-zinc-700 hover:bg-zinc-50 transition-colors group">
                            <flux:icon icon="globe-alt" class="size-4 text-zinc-400 shrink-0" />
                            Teledetección
                        </a>
                        <a href="{{ route('winery.field-activities.index') }}" wire:navigate
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-zinc-700 hover:bg-zinc-50 transition-colors group">
                            <flux:icon icon="pencil-square" class="size-4 text-zinc-400 shrink-0" />
                            Actividades de Campo
                        </a>
                    </div>
                </div>

                <div class="border-t border-zinc-100"></div>

                {{-- ── Vendimia ── --}}
                <div>
                    <p class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest mb-1.5 px-1">Vendimia</p>
                    <div class="space-y-0.5">
                        <a href="{{ route('winery.grape-reception.create') }}" wire:navigate
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-zinc-700 hover:bg-pink-50 hover:text-pink-700 transition-colors group">
                            <flux:icon icon="archive-box-arrow-down" class="size-4 text-zinc-400 group-hover:text-pink-600 shrink-0" />
                            Nueva recepción de uva
                        </a>
                        <a href="{{ route('winery.grape-reception.index') }}" wire:navigate
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-zinc-700 hover:bg-zinc-50 transition-colors group">
                            <flux:icon icon="clipboard-document-list" class="size-4 text-zinc-400 shrink-0" />
                            Ver recepciones
                        </a>
                        <a href="{{ route('winery.harvest-quality.index') }}" wire:navigate
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-zinc-700 hover:bg-zinc-50 transition-colors group">
                            <flux:icon icon="beaker" class="size-4 text-zinc-400 shrink-0" />
                            Análisis de calidad
                        </a>
                        <a href="{{ route('winery.harvest-forecasts.index') }}" wire:navigate
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-zinc-700 hover:bg-zinc-50 transition-colors group">
                            <flux:icon icon="clipboard-document-list" class="size-4 text-zinc-400 shrink-0" />
                            Previsiones
                        </a>
                        <a href="{{ route('winery.vitic-estimates.index') }}" wire:navigate
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-zinc-700 hover:bg-zinc-50 transition-colors group">
                            <flux:icon icon="calculator" class="size-4 text-zinc-400 shrink-0" />
                            Aforos viticultores
                        </a>
                        <a href="{{ route('winery.harvest-summary.index') }}" wire:navigate
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-zinc-700 hover:bg-zinc-50 transition-colors group">
                            <flux:icon icon="chart-bar" class="size-4 text-zinc-400 shrink-0" />
                            Cuadro de mando
                        </a>
                    </div>
                </div>

                <div class="border-t border-zinc-100"></div>

                {{-- ── Viticultor ── --}}
                @if($selectedPlot->viticulturist)
                <div>
                    <p class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest mb-1.5 px-1">Viticultor</p>
                    <div class="space-y-0.5">
                        <a href="{{ route('winery.viticulturists.index') }}" wire:navigate
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-zinc-700 hover:bg-zinc-50 transition-colors group">
                            <flux:icon icon="user-group" class="size-4 text-zinc-400 shrink-0" />
                            Mis Viticultores
                        </a>
                    </div>
                </div>
                @endif

            </div>
        </div>

        @else
        {{-- Sin selección --}}
        <div class="w-72 shrink-0 border-l border-zinc-100 bg-zinc-50/80 flex flex-col items-center justify-center gap-4 text-zinc-400 p-6">
            <div class="w-14 h-14 bg-zinc-100 rounded-2xl flex items-center justify-center">
                <flux:icon icon="cursor-arrow-rays" class="size-7 text-zinc-300" />
            </div>
            <div class="text-center">
                <p class="text-sm font-semibold text-zinc-500">Selecciona una parcela</p>
                <p class="text-xs mt-1 text-center leading-relaxed">Pulsa en un marcador del mapa para ver todas las acciones disponibles</p>
            </div>
        </div>
        @endif

    </div>
    @endif

    {{-- ══════════════════════════════════════════════════════ --}}
    {{--                  TAB: BODEGA/CONTENEDORES             --}}
    {{-- ══════════════════════════════════════════════════════ --}}
    @if ($activeTab === 'containers')
    <div class="flex flex-1 overflow-hidden">

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
                <a href="{{ roleRoute('containers.create') }}" wire:navigate class="ml-auto">
                    <flux:button variant="primary" icon="plus" size="sm">Nuevo contenedor</flux:button>
                </a>
            </div>

            {{-- Grid scrollable --}}
            <div class="flex-1 overflow-y-auto p-6"
                 wire:loading.class="opacity-50 pointer-events-none"
                 wire:target="containerSearch, containerTypeFilter">
                @if($containers->count() > 0)
                <div class="grid gap-2"
                     style="grid-template-columns: repeat(auto-fill, minmax(72px, 1fr));">
                    @foreach($containers as $container)
                        @php
                            $pct        = $container->getOccupancyPercentage();
                            $fillColor  = $pct >= 90 ? '#ef4444' : ($pct >= 75 ? '#f59e0b' : ($pct > 0 ? '#22c55e' : 'transparent'));
                            $textColor  = $pct > 55 ? '#ffffff' : '#374151';
                            $isSelected = $selectedContainerId === $container->id;
                        @endphp
                        <button
                            wire:click="selectContainer({{ $container->id }})"
                            wire:key="vis-container-{{ $container->id }}"
                            title="{{ $container->name }} — {{ round($pct) }}%"
                            class="flex flex-col items-center gap-1.5 p-2 rounded-xl transition-all duration-150 cursor-pointer group
                                   {{ $isSelected ? 'bg-agro-50 ring-2 ring-agro-400 shadow-md' : 'hover:bg-zinc-50 hover:shadow-sm' }}"
                        >
                            {{-- Depósito visual --}}
                            <div class="relative rounded-lg overflow-hidden flex-shrink-0 transition-all"
                                 style="width: 40px; height: 68px; background: #f4f4f5; border: 2px solid {{ $isSelected ? '#4ade80' : '#d4d4d8' }};">
                                {{-- Nivel de llenado --}}
                                @if($pct > 0)
                                <div class="absolute bottom-0 left-0 right-0"
                                     style="height: {{ min($pct, 100) }}%; background-color: {{ $fillColor }}; opacity: 0.8; transition: height 0.7s ease-out;">
                                </div>
                                @endif
                                {{-- Líneas de escala --}}
                                <div class="absolute inset-0 flex flex-col justify-around pointer-events-none opacity-20">
                                    <div class="border-b border-zinc-500 mx-1"></div>
                                    <div class="border-b border-zinc-500 mx-1"></div>
                                    <div class="border-b border-zinc-500 mx-1"></div>
                                </div>
                                {{-- Porcentaje --}}
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <span class="text-[8px] font-black leading-none select-none"
                                          style="color: {{ $textColor }};">{{ round($pct) }}%</span>
                                </div>
                            </div>
                            {{-- Nombre --}}
                            <p class="text-[8px] font-semibold text-zinc-700 text-center leading-tight w-full truncate px-0.5">
                                {{ $container->name }}
                            </p>
                            @if($container->containerRoom)
                            <p class="text-[7px] text-zinc-400 text-center w-full truncate">
                                {{ $container->containerRoom->name }}
                            </p>
                            @endif
                        </button>
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
                        <a href="{{ roleRoute('containers.show', $selectedContainer) }}" wire:navigate
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

                {{-- ── Elaboración ── --}}
                <div>
                    <p class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest mb-1.5 px-1">Elaboración</p>
                    <div class="space-y-0.5">
                        <a href="{{ roleRoute('containers.show', $selectedContainer) }}" wire:navigate
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-zinc-700 hover:bg-violet-50 hover:text-violet-700 transition-colors group">
                            <flux:icon icon="beaker" class="size-4 text-zinc-400 group-hover:text-violet-600 shrink-0" />
                            Controles de fermentación
                        </a>
                        <a href="{{ roleRoute('containers.show', $selectedContainer) }}" wire:navigate
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-zinc-700 hover:bg-blue-50 hover:text-blue-700 transition-colors group">
                            <flux:icon icon="arrows-right-left" class="size-4 text-zinc-400 group-hover:text-blue-600 shrink-0" />
                            Traslados de vino
                        </a>
                        <a href="{{ roleRoute('containers.show', $selectedContainer) }}" wire:navigate
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-zinc-700 hover:bg-zinc-50 transition-colors group">
                            <flux:icon icon="magnifying-glass" class="size-4 text-zinc-400 shrink-0" />
                            Análisis de laboratorio
                        </a>
                        <a href="{{ roleRoute('containers.show', $selectedContainer) }}" wire:navigate
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-zinc-700 hover:bg-zinc-50 transition-colors group">
                            <flux:icon icon="wrench-screwdriver" class="size-4 text-zinc-400 shrink-0" />
                            Mantenimiento
                        </a>
                        <a href="{{ roleRoute('containers.show', $selectedContainer) }}" wire:navigate
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-zinc-700 hover:bg-zinc-50 transition-colors group">
                            <flux:icon icon="archive-box" class="size-4 text-zinc-400 shrink-0" />
                            Aditivos
                        </a>
                    </div>
                </div>

                <div class="border-t border-zinc-100"></div>

                {{-- ── Bodega (global) ── --}}
                <div>
                    <p class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest mb-1.5 px-1">Bodega</p>
                    <div class="space-y-0.5">
                        <a href="{{ roleRoute('containers.index') }}" wire:navigate
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-zinc-700 hover:bg-zinc-50 transition-colors group">
                            <flux:icon icon="beaker" class="size-4 text-zinc-400 shrink-0" />
                            Todos los contenedores
                        </a>
                        <a href="{{ roleRoute('containers.map') }}" wire:navigate
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-zinc-700 hover:bg-zinc-50 transition-colors group">
                            <flux:icon icon="map" class="size-4 text-zinc-400 shrink-0" />
                            Mapa de Bodega
                        </a>
                        <a href="{{ roleRoute('container-rooms.index') }}" wire:navigate
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-zinc-700 hover:bg-zinc-50 transition-colors group">
                            <flux:icon icon="home-modern" class="size-4 text-zinc-400 shrink-0" />
                            Salas de Bodega
                        </a>
                        <a href="{{ roleRoute('wines.index') }}" wire:navigate
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-zinc-700 hover:bg-zinc-50 transition-colors group">
                            <flux:icon icon="arrows-right-left" class="size-4 text-zinc-400 shrink-0" />
                            Vinos
                        </a>
                        <a href="{{ roleRoute('wines.timeline') }}" wire:navigate
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-zinc-700 hover:bg-zinc-50 transition-colors group">
                            <flux:icon icon="queue-list" class="size-4 text-zinc-400 shrink-0" />
                            Timeline de Vinos
                        </a>
                        <a href="{{ roleRoute('wine-analysis.index') }}" wire:navigate
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-zinc-700 hover:bg-zinc-50 transition-colors group">
                            <flux:icon icon="magnifying-glass" class="size-4 text-zinc-400 shrink-0" />
                            Análisis de Laboratorio
                        </a>
                        <a href="{{ roleRoute('cellar-operations.index') }}" wire:navigate
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-zinc-700 hover:bg-zinc-50 transition-colors group">
                            <flux:icon icon="calendar-days" class="size-4 text-zinc-400 shrink-0" />
                            Operaciones de Bodega
                        </a>
                        <a href="{{ roleRoute('external-grape.index') }}" wire:navigate
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-zinc-700 hover:bg-zinc-50 transition-colors group">
                            <flux:icon icon="archive-box" class="size-4 text-zinc-400 shrink-0" />
                            Uva / Mosto externo
                        </a>
                    </div>
                </div>

                <div class="border-t border-zinc-100"></div>

                {{-- ── Salida ── --}}
                <div>
                    <p class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest mb-1.5 px-1">Salida</p>
                    <div class="space-y-0.5">
                        <a href="{{ roleRoute('product-lots.index') }}" wire:navigate
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-zinc-700 hover:bg-zinc-50 transition-colors group">
                            <flux:icon icon="archive-box" class="size-4 text-zinc-400 shrink-0" />
                            Productos
                        </a>
                        <a href="{{ roleRoute('bottling.index') }}" wire:navigate
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-zinc-700 hover:bg-zinc-50 transition-colors group">
                            <flux:icon icon="archive-box-arrow-down" class="size-4 text-zinc-400 shrink-0" />
                            Embotellado y Etiquetado
                        </a>
                        <a href="{{ roleRoute('traceability.index') }}" wire:navigate
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-zinc-700 hover:bg-zinc-50 transition-colors group">
                            <flux:icon icon="magnifying-glass-circle" class="size-4 text-zinc-400 shrink-0" />
                            Trazabilidad
                        </a>
                        <a href="{{ roleRoute('subproducts.index') }}" wire:navigate
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-zinc-700 hover:bg-zinc-50 transition-colors group">
                            <flux:icon icon="archive-box-x-mark" class="size-4 text-zinc-400 shrink-0" />
                            Subproductos
                        </a>
                    </div>
                </div>

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

    </div>
    @endif

</div>

@script
<script>
Alpine.data('visualPlotsMap', (plots) => ({
    map: null,
    markers: {},

    init() {
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

            this.map = L.map('visual-plots-map').setView([40.0, -3.5], 6);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                maxZoom: 18,
            }).addTo(this.map);

            const makeIcon = (selected) => L.divIcon({
                html: `<div style="width:${selected ? 18 : 13}px;height:${selected ? 18 : 13}px;background:${selected ? '#2563eb' : '#16a34a'};border:2.5px solid #fff;border-radius:50%;box-shadow:0 2px 8px rgba(0,0,0,.35);transition:all .2s;"></div>`,
                className: '',
                iconSize: selected ? [18, 18] : [13, 13],
                iconAnchor: selected ? [9, 9] : [6, 6],
            });

            const bounds = [];
            plots.forEach(plot => {
                const marker = L.marker([plot.lat, plot.lng], {
                    icon: makeIcon(false),
                    title: plot.name,
                }).addTo(this.map);

                marker.bindTooltip(plot.name, { permanent: false, direction: 'top', offset: [0, -8] });
                marker.on('click', () => $wire.selectPlot(plot.id));

                this.markers[plot.id] = marker;
                bounds.push([plot.lat, plot.lng]);
            });

            if (bounds.length > 0) {
                this.map.fitBounds(bounds, { padding: [50, 50], maxZoom: 13 });
            }

            // Actualizar icono del marcador seleccionado reactivamente
            $wire.$watch('selectedPlotId', (newId) => {
                Object.entries(this.markers).forEach(([id, marker]) => {
                    const selected = parseInt(id) === newId;
                    marker.setIcon(makeIcon(selected));
                    if (selected) marker.openTooltip();
                });
            });
        });
    },
}));
</script>
@endscript
