{{-- Vista Visual Winery: full-height, elimina padding del layout --}}
<div class="-mx-4 lg:-mx-8 -my-4 lg:-my-8 flex flex-col bg-white relative" style="height: calc(100vh - 4rem);">

    {{-- ══ Top bar: tabs + stats + volver ══ --}}
    <div class="shrink-0 flex items-center gap-4 px-6 py-3 bg-white border-b border-zinc-200 z-20">

        {{-- Tabs --}}
        <div class="flex gap-1 bg-zinc-100 rounded-xl p-1 shrink-0">
            <button wire:click="switchTab('dashboard')"
                class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm font-medium transition-all
                       {{ $activeTab === 'dashboard' ? 'bg-white shadow-sm text-zinc-800' : 'text-zinc-500 hover:text-zinc-700' }}">
                <flux:icon icon="chart-bar" class="size-4" />
                <span class="hidden sm:inline">Resumen</span>
            </button>
            <button wire:click="switchTab('plots')"
                class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm font-medium transition-all
                       {{ $activeTab === 'plots' ? 'bg-white shadow-sm text-zinc-800' : 'text-zinc-500 hover:text-zinc-700' }}">
                <flux:icon icon="map" class="size-4" />
                Parcelas
                <span class="text-[10px] font-semibold text-zinc-400">{{ count($mapPlots) }}</span>
            </button>
            <button wire:click="switchTab('containers')"
                class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm font-medium transition-all
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
                    <p class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest mb-2 px-1">Vendimia</p>
                    <a href="{{ route('winery.grape-reception.create') }}" wire:navigate
                       class="flex items-center justify-center gap-2 w-full px-3 py-2.5 mb-2 rounded-xl text-sm font-semibold bg-agro-600 text-white hover:bg-agro-700 transition-colors shadow-sm">
                        <flux:icon icon="archive-box-arrow-down" class="size-4 shrink-0" />
                        Recibir uva
                    </a>
                    <div class="space-y-0.5">
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
                <div class="grid gap-3"
                     style="grid-template-columns: repeat(auto-fill, minmax(90px, 1fr));">
                    @foreach($containers as $container)
                        @php
                            $pct        = $container->getOccupancyPercentage();
                            $fillColor  = $pct >= 90 ? '#ef4444' : ($pct >= 75 ? '#f59e0b' : ($pct > 0 ? '#4a7c59' : 'transparent'));
                            $borderColor = $pct >= 90 ? '#ef4444' : ($pct >= 75 ? '#f59e0b' : ($pct > 0 ? '#4a7c59' : '#d4d4d8'));
                            $textColor  = $pct > 55 ? '#ffffff' : '#374151';
                            $isSelected = $selectedContainerId === $container->id;
                        @endphp
                        <button
                            wire:click="selectContainer({{ $container->id }})"
                            wire:key="vis-container-{{ $container->id }}"
                            title="{{ $container->name }} — {{ round($pct) }}%"
                            class="flex flex-col items-center gap-2 p-2.5 rounded-2xl transition-all duration-150 cursor-pointer group
                                   {{ $isSelected ? 'bg-agro-50 ring-2 ring-agro-400 shadow-md' : 'hover:bg-zinc-50 hover:shadow-sm' }}"
                        >
                            {{-- Depósito visual --}}
                            <div class="relative rounded-xl overflow-hidden flex-shrink-0"
                                 style="width: 52px; height: 88px; background: #f4f4f5; border: 2px solid {{ $isSelected ? '#4ade80' : $borderColor }}; transition: border-color 0.3s;">
                                {{-- Tapón superior --}}
                                <div class="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-px"
                                     style="width: 20px; height: 5px; background: {{ $isSelected ? '#4ade80' : $borderColor }}; border-radius: 3px 3px 0 0; transition: background 0.3s;"></div>
                                {{-- Nivel de llenado --}}
                                @if($pct > 0)
                                <div class="absolute bottom-0 left-0 right-0"
                                     style="height: {{ min($pct, 100) }}%; background-color: {{ $fillColor }}; opacity: 0.75; transition: height 0.8s ease-out;">
                                </div>
                                @endif
                                {{-- Líneas de escala --}}
                                <div class="absolute inset-x-1.5 inset-y-0 flex flex-col justify-evenly pointer-events-none">
                                    <div class="border-b border-zinc-400/20"></div>
                                    <div class="border-b border-zinc-400/20"></div>
                                    <div class="border-b border-zinc-400/20"></div>
                                </div>
                                {{-- Porcentaje --}}
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <span class="text-[9px] font-black leading-none select-none drop-shadow-sm"
                                          style="color: {{ $textColor }};">{{ round($pct) }}%</span>
                                </div>
                            </div>
                            {{-- Nombre --}}
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

    {{-- ══════════════════════════════════════════════════════ --}}
    {{--                   TAB: DASHBOARD                      --}}
    {{-- ══════════════════════════════════════════════════════ --}}
    @if ($activeTab === 'dashboard')
    <div class="flex-1 overflow-y-auto">
        <div class="max-w-5xl mx-auto p-6 space-y-6">

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
                        <span class="text-xs text-zinc-400">Campaña activa</span>
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
                        <div class="flex items-center gap-3 px-5 py-3">
                            <div class="w-7 h-7 rounded-lg bg-agro-50 flex items-center justify-center shrink-0">
                                <flux:icon icon="beaker" class="size-3.5 text-agro-600" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-zinc-800 truncate">{{ $ctrl->wine?->name ?? '—' }}</p>
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
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>

            </div>
        </div>
    </div>
    @endif

    {{-- ── FAB acciones rápidas (plots + containers) ── --}}
    @if ($activeTab !== 'dashboard')
    <div class="absolute bottom-6 right-6 z-30"
         x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
        {{-- Menú desplegable --}}
        <div x-show="open"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 scale-95 translate-y-2"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-2"
             class="absolute bottom-14 right-0 w-56 bg-white rounded-2xl shadow-xl border border-zinc-100 py-2 origin-bottom-right">
            <a href="{{ route('winery.grape-reception.create') }}" wire:navigate
               class="flex items-center gap-3 px-4 py-2.5 text-sm text-zinc-700 hover:bg-agro-50 hover:text-agro-800 transition-colors">
                <span class="w-7 h-7 bg-amber-100 rounded-lg flex items-center justify-center shrink-0">
                    <flux:icon icon="archive-box-arrow-down" class="size-4 text-amber-600" />
                </span>
                Recibir uva
            </a>
            <a href="{{ route('winery.wine-transfers.create') }}" wire:navigate
               class="flex items-center gap-3 px-4 py-2.5 text-sm text-zinc-700 hover:bg-blue-50 hover:text-blue-800 transition-colors">
                <span class="w-7 h-7 bg-blue-100 rounded-lg flex items-center justify-center shrink-0">
                    <flux:icon icon="arrows-right-left" class="size-4 text-blue-600" />
                </span>
                Traslado de vino
            </a>
            <a href="{{ route('winery.wine-losses.create') }}" wire:navigate
               class="flex items-center gap-3 px-4 py-2.5 text-sm text-zinc-700 hover:bg-orange-50 hover:text-orange-800 transition-colors">
                <span class="w-7 h-7 bg-orange-100 rounded-lg flex items-center justify-center shrink-0">
                    <flux:icon icon="minus-circle" class="size-4 text-orange-500" />
                </span>
                Registrar merma
            </a>
            <div class="my-1 border-t border-zinc-100"></div>
            <a href="{{ roleRoute('containers.create') }}" wire:navigate
               class="flex items-center gap-3 px-4 py-2.5 text-sm text-zinc-700 hover:bg-zinc-50 transition-colors">
                <span class="w-7 h-7 bg-zinc-100 rounded-lg flex items-center justify-center shrink-0">
                    <flux:icon icon="beaker" class="size-4 text-zinc-500" />
                </span>
                Nuevo contenedor
            </a>
        </div>
        {{-- Botón FAB --}}
        <button @click="open = !open"
                class="flex items-center gap-2 pl-3 pr-4 py-3 rounded-2xl shadow-lg transition-all duration-200 font-semibold text-sm text-white
                       {{ 'bg-agro-600 hover:bg-agro-700 hover:shadow-xl' }}"
                :class="open ? 'bg-agro-700 shadow-xl' : ''">
            <flux:icon icon="plus" class="size-5 transition-transform duration-200" x-bind:class="open ? 'rotate-45' : ''" />
            Registrar
        </button>
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

            this.map = L.map('visual-plots-map', { zoomControl: false }).setView([40.0, -3.5], 6);

            // Satellite (Esri World Imagery)
            L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                attribution: 'Tiles &copy; Esri &mdash; Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP',
                maxZoom: 19,
            }).addTo(this.map);
            // Labels overlay (carreteras, municipios, etc.)
            L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/Reference/World_Boundaries_and_Places/MapServer/tile/{z}/{y}/{x}', {
                attribution: '',
                maxZoom: 19,
                opacity: 0.75,
            }).addTo(this.map);

            // Zoom control bottom-left
            L.control.zoom({ position: 'bottomleft' }).addTo(this.map);

            const makeIcon = (selected) => L.divIcon({
                html: `<div style="width:${selected ? 20 : 14}px;height:${selected ? 20 : 14}px;background:${selected ? '#f59e0b' : '#4ade80'};border:2.5px solid #fff;border-radius:50%;box-shadow:0 2px 10px rgba(0,0,0,.5);transition:all .2s;"></div>`,
                className: '',
                iconSize: selected ? [20, 20] : [14, 14],
                iconAnchor: selected ? [10, 10] : [7, 7],
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
