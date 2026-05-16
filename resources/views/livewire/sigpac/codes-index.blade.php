<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Códigos SIGPAC"
        description="Gestiona los códigos de identificación SIGPAC de tus parcelas"
    />

    {{-- Toolbar --}}
    <div class="space-y-3">
        <div class="flex items-center gap-3">

            <div class="flex-1 relative">
                <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                    <flux:icon icon="magnifying-glass" class="size-4 text-zinc-400" />
                </div>
                <input
                    wire:model.live.debounce.300ms="search"
                    type="text"
                    placeholder="Buscar por código..."
                    class="w-full pl-9 pr-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm placeholder:text-zinc-400 shadow-sm focus:outline-none focus:ring-2 focus:ring-agro-500 focus:border-transparent transition"
                />
            </div>

            @php
                $filterCount = ($filterAutonomousCommunity ? 1 : 0)
                    + ($filterProvince ? 1 : 0)
                    + ($filterMunicipality ? 1 : 0);
            @endphp
            <button
                x-on:click="$dispatch('open-modal', 'sigpac-filters')"
                class="relative inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm font-medium text-zinc-700 hover:bg-zinc-50 shadow-sm transition-colors"
            >
                <flux:icon icon="adjustments-horizontal" class="size-4 text-zinc-500" />
                Filtros
                @if($filterCount > 0)
                    <span class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-agro-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center leading-none">
                        {{ $filterCount }}
                    </span>
                @endif
            </button>

            <div class="w-px h-8 bg-zinc-200 shrink-0"></div>

            <flux:button href="{{ route('sigpac.codes.create') }}" variant="primary" icon="plus">
                Crear Código SIGPAC
            </flux:button>

        </div>

        {{-- Active filter chips --}}
        @if($search || $filterAutonomousCommunity || $filterProvince || $filterMunicipality)
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs text-zinc-400">Filtros activos:</span>

                @if($search)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                        <flux:icon icon="magnifying-glass" class="size-3" />
                        "{{ $search }}"
                        <button wire:click="$set('search', '')" class="hover:text-agro-900 ml-0.5">
                            <flux:icon icon="x-mark" class="size-3" />
                        </button>
                    </span>
                @endif

                @if($filterAutonomousCommunity)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                        CA: {{ $this->autonomousCommunities[$filterAutonomousCommunity] ?? $filterAutonomousCommunity }}
                        <button wire:click="$set('filterAutonomousCommunity', '')" class="hover:text-agro-900 ml-0.5">
                            <flux:icon icon="x-mark" class="size-3" />
                        </button>
                    </span>
                @endif

                @if($filterProvince)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                        Prov: {{ $this->provinces[$filterProvince] ?? $filterProvince }}
                        <button wire:click="$set('filterProvince', '')" class="hover:text-agro-900 ml-0.5">
                            <flux:icon icon="x-mark" class="size-3" />
                        </button>
                    </span>
                @endif

                @if($filterMunicipality)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                        Mun: {{ $this->municipalities[$filterMunicipality] ?? $filterMunicipality }}
                        <button wire:click="$set('filterMunicipality', '')" class="hover:text-agro-900 ml-0.5">
                            <flux:icon icon="x-mark" class="size-3" />
                        </button>
                    </span>
                @endif

                <button wire:click="clearFilters" class="text-xs text-zinc-400 hover:text-zinc-600 underline">
                    Limpiar todo
                </button>
            </div>
        @endif
    </div>

    {{-- Acción masiva de municipio --}}
    @if($filterAutonomousCommunity && $filterProvince && $filterMunicipality && $this->municipalityHasSigpacCodes)
        <x-agro.card>
            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-agro-50 rounded-lg flex items-center justify-center shrink-0">
                        <flux:icon icon="map" class="size-5 text-agro-600" />
                    </div>
                    <div>
                        <p class="font-semibold text-zinc-900 text-sm">{{ $this->municipalities[$filterMunicipality] ?? 'Municipio' }}</p>
                        <p class="text-xs text-zinc-500">
                            {{ $this->provinces[$filterProvince] ?? '' }}, {{ $this->autonomousCommunities[$filterAutonomousCommunity] ?? '' }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <flux:button
                        wire:click="generateAllMapsForMunicipality"
                        wire:loading.attr="disabled"
                        variant="primary"
                        size="sm"
                        icon="map"
                    >
                        <span wire:loading.remove wire:target="generateAllMapsForMunicipality">Generar todos los mapas</span>
                        <span wire:loading wire:target="generateAllMapsForMunicipality">Generando...</span>
                    </flux:button>

                    @php
                        $firstPlot = \App\Models\Plot::forUser(auth()->user())
                            ->where('municipality_id', $filterMunicipality)
                            ->first();
                    @endphp
                    @if($firstPlot)
                        <flux:button
                            href="{{ route('map', ['id' => $firstPlot->id, 'municipality' => $filterMunicipality, 'return' => 'sigpac']) }}"
                            variant="outline"
                            size="sm"
                            icon="eye"
                        >
                            Ver todos los mapas
                        </flux:button>
                    @endif
                </div>
            </div>
        </x-agro.card>
    @endif

    {{-- Card grid --}}
    @if($codes->count() > 0)
        <div
            class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6"
            wire:loading.class="opacity-60 pointer-events-none"
            wire:target="search, filterAutonomousCommunity, filterProvince, filterMunicipality, clearFilters"
        >
            @foreach($codes as $i => $code)
                @php
                    $firstPlot  = $code->plots->first();
                    $hasGeometry = false;
                    $multipartId = null;
                    if ($firstPlot && $code->multiplePlotSigpacs ?? null) {
                        $multipart = $code->multiplePlotSigpacs
                            ->where('plot_id', $firstPlot->id)
                            ->where('sigpac_code_id', $code->id)
                            ->whereNotNull('plot_geometry_id')
                            ->first();
                        if ($multipart) {
                            $hasGeometry = true;
                            $multipartId = $multipart->id;
                        }
                    }
                @endphp

                <x-agro.card
                    wire:key="sigpac-{{ $code->id }}"
                    class="animate-fade-in-up hover:-translate-y-1"
                    style="animation-delay: {{ min($i * 50, 400) }}ms"
                >
                    <x-slot:header>
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 bg-agro-50 rounded-full flex items-center justify-center shrink-0">
                                <flux:icon icon="map" class="size-4 text-agro-600" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-zinc-900 text-sm truncate leading-tight">
                                    {{ $firstPlot ? $firstPlot->name : 'Sin parcela asociada' }}
                                </p>
                                <p class="text-xs text-zinc-400 leading-tight mt-0.5 font-mono">
                                    {{ implode('/', array_filter([
                                        $code->code_province,
                                        $code->code_municipality,
                                        $code->code_polygon,
                                        $code->code_plot,
                                        $code->code_enclosure,
                                    ])) }}
                                </p>
                            </div>
                            @if($hasGeometry)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-agro-100 text-agro-700 shrink-0">
                                    Con mapa
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-zinc-100 text-zinc-500 shrink-0">
                                    Sin mapa
                                </span>
                            @endif
                        </div>
                    </x-slot:header>

                    {{-- Ubicación --}}
                    @if($firstPlot)
                        <div class="flex items-center gap-2 mb-3">
                            <flux:icon icon="map-pin" class="size-3.5 text-zinc-400 shrink-0" />
                            <span class="text-xs text-zinc-600 truncate">
                                {{ implode(', ', array_filter([
                                    $firstPlot->municipality?->name,
                                    $firstPlot->province?->name,
                                    $firstPlot->autonomousCommunity?->name,
                                ])) ?: 'Sin ubicación' }}
                            </span>
                        </div>
                    @endif

                    {{-- Códigos SIGPAC --}}
                    <div class="grid grid-cols-3 gap-1.5">
                        @foreach([
                            ['CA',  $code->code_autonomous_community],
                            ['Pr',  $code->code_province],
                            ['Mu',  $code->code_municipality],
                            ['Po',  $code->code_polygon],
                            ['Pa',  $code->code_plot],
                            ['Re',  $code->code_enclosure],
                        ] as [$label, $value])
                            <div class="bg-zinc-50 rounded-lg p-1.5 text-center">
                                <p class="text-[9px] text-zinc-400 font-medium uppercase">{{ $label }}</p>
                                <p class="text-xs font-mono font-semibold text-zinc-700">{{ $value ?? '—' }}</p>
                            </div>
                        @endforeach
                    </div>

                    <x-slot:footer>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-1">
                                <x-agro.action-button variant="view" href="{{ route('plots.index', ['sigpac_code' => $code->id]) }}" title="Ver parcelas" />
                                <x-agro.action-button variant="edit" href="{{ route('sigpac.codes.edit', $code->id) }}" title="Editar" />
                            </div>

                            @if($firstPlot)
                                @if($hasGeometry && $multipartId)
                                    <a href="/map/{{ $firstPlot->id }}?recinto={{ $multipartId }}&return=sigpac"
                                       class="inline-flex items-center gap-1.5 text-xs font-medium text-agro-600 hover:text-agro-700 transition-colors">
                                        <flux:icon icon="map" class="size-3.5" />
                                        Ver mapa
                                    </a>
                                @else
                                    <flux:button
                                        wire:click="generateMap({{ $code->id }}, {{ $firstPlot->id }})"
                                        wire:loading.attr="disabled"
                                        variant="ghost"
                                        size="sm"
                                        icon="map"
                                    >
                                        <span wire:loading.remove wire:target="generateMap({{ $code->id }}, {{ $firstPlot->id }})">Generar mapa</span>
                                        <span wire:loading wire:target="generateMap({{ $code->id }}, {{ $firstPlot->id }})">...</span>
                                    </flux:button>
                                @endif
                            @endif
                        </div>
                    </x-slot:footer>
                </x-agro.card>
            @endforeach
        </div>

        @if($codes->hasPages())
            <div class="flex justify-center">{{ $codes->links() }}</div>
        @endif

    @else
        <x-agro.empty-state
            icon="map"
            message="No se encontraron códigos SIGPAC"
            description="{{ $search || $filterAutonomousCommunity || $filterProvince || $filterMunicipality ? 'Ningún código coincide con los filtros aplicados.' : 'No hay códigos SIGPAC registrados.' }}"
        >
            @if($search || $filterAutonomousCommunity || $filterProvince || $filterMunicipality)
                <x-slot:action>
                    <flux:button wire:click="clearFilters" variant="outline" icon="x-mark">Limpiar filtros</flux:button>
                </x-slot:action>
            @else
                <x-slot:action>
                    <flux:button href="{{ route('sigpac.codes.create') }}" variant="primary" icon="plus">
                        Crear Código SIGPAC
                    </flux:button>
                </x-slot:action>
            @endif
        </x-agro.empty-state>
    @endif

    {{-- Modal Filtros --}}
    <x-agro.modal name="sigpac-filters" maxWidth="sm">
        <div class="px-6 py-4 border-b border-zinc-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-agro-100 rounded-lg flex items-center justify-center">
                        <flux:icon icon="adjustments-horizontal" class="size-4 text-agro-600" />
                    </div>
                    <h3 class="text-base font-semibold text-zinc-900">Filtros</h3>
                </div>
                <flux:button x-on:click="$dispatch('close-modal', 'sigpac-filters')" variant="ghost" size="sm" icon="x-mark" />
            </div>
        </div>

        <div class="px-6 py-5 space-y-4">
            <x-agro.filter-select label="Comunidad Autónoma" wire:model.live="filterAutonomousCommunity" placeholder="Todas las Comunidades">
                @foreach($this->autonomousCommunities as $id => $name)
                    <flux:select.option value="{{ $id }}">{{ $name }}</flux:select.option>
                @endforeach
            </x-agro.filter-select>

            @if($filterAutonomousCommunity)
                <x-agro.filter-select label="Provincia" wire:model.live="filterProvince" placeholder="Todas las Provincias">
                    @foreach($this->provinces as $id => $name)
                        <flux:select.option value="{{ $id }}">{{ $name }}</flux:select.option>
                    @endforeach
                </x-agro.filter-select>
            @endif

            @if($filterProvince)
                <x-agro.filter-select label="Municipio" wire:model.live="filterMunicipality" placeholder="Todos los Municipios">
                    @foreach($this->municipalities as $id => $name)
                        <flux:select.option value="{{ $id }}">{{ $name }}</flux:select.option>
                    @endforeach
                </x-agro.filter-select>
            @endif
        </div>

        <div class="px-6 py-4 bg-zinc-50 border-t border-zinc-200 flex items-center justify-between rounded-b-2xl">
            <button wire:click="clearFilters" x-on:click="$dispatch('close-modal', 'sigpac-filters')"
                    class="text-sm text-zinc-500 hover:text-zinc-700 transition-colors">
                Limpiar filtros
            </button>
            <flux:button x-on:click="$dispatch('close-modal', 'sigpac-filters')" variant="primary" size="sm">
                Aplicar
            </flux:button>
        </div>
    </x-agro.modal>

</div>
