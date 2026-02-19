<div class="space-y-6 animate-fade-in">
    <!-- Header -->
    <x-agro.page-header
        title="Códigos SIGPAC"
        description="Gestiona los códigos de identificación SIGPAC"
    >
        <x-slot:actions>
            <flux:button href="{{ route('sigpac.codes.create') }}" variant="primary" icon="plus">
                Crear Código SIGPAC
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    <!-- Búsqueda y Filtros -->
    <x-agro.filter-bar>
        <x-agro.filter-input
            wire:model.live.debounce.300ms="search"
            placeholder="Buscar por código..."
        />
        <x-agro.filter-select wire:model.live="filterAutonomousCommunity">
            <option value="">Todas las Comunidades Autónomas</option>
            @foreach($this->autonomousCommunities as $id => $name)
                <option value="{{ $id }}">{{ $name }}</option>
            @endforeach
        </x-agro.filter-select>
        @if($filterAutonomousCommunity)
            <x-agro.filter-select wire:model.live="filterProvince">
                <option value="">Todas las Provincias</option>
                @foreach($this->provinces as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </x-agro.filter-select>
        @endif
        @if($filterProvince)
            <x-agro.filter-select wire:model.live="filterMunicipality">
                <option value="">Todos los Municipios</option>
                @foreach($this->municipalities as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </x-agro.filter-select>
        @endif
        @if($search || $filterAutonomousCommunity || $filterProvince || $filterMunicipality)
            <flux:button wire:click="clearFilters" variant="ghost" size="sm">
                Limpiar Filtros
            </flux:button>
        @endif
    </x-agro.filter-bar>

    <!-- Acciones Masivas para Municipio -->
    @if($filterAutonomousCommunity && $filterProvince && $filterMunicipality && $this->municipalityHasSigpacCodes)
        <x-agro.card>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-agro-50 flex items-center justify-center">
                        <flux:icon icon="map" class="size-5 text-agro-600" />
                    </div>
                    <div>
                        <div class="font-bold text-zinc-900">
                            Acciones para {{ $this->municipalities[$filterMunicipality] ?? 'Municipio' }}
                        </div>
                        <div class="text-sm text-zinc-600">
                            {{ $this->provinces[$filterProvince] ?? '' }}, {{ $this->autonomousCommunities[$filterAutonomousCommunity] ?? '' }}
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <!-- Botón Generar Todos los Mapas -->
                    <flux:button
                        wire:click="generateAllMapsForMunicipality"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-50 cursor-not-allowed"
                        variant="primary"
                        icon="plus"
                    >
                        <span wire:loading.remove wire:target="generateAllMapsForMunicipality">Generar Todos los Mapas</span>
                        <span wire:loading wire:target="generateAllMapsForMunicipality">Generando...</span>
                    </flux:button>

                    <!-- Botón Ver Todos los Mapas -->
                    @php
                        $firstPlot = \App\Models\Plot::forUser(auth()->user())
                            ->where('municipality_id', $filterMunicipality)
                            ->first();
                    @endphp

                    @if($firstPlot)
                        <flux:button
                            href="{{ route('map', ['id' => $firstPlot->id, 'municipality' => $filterMunicipality, 'return' => 'sigpac']) }}"
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

    <!-- Tabla de códigos -->
    <x-agro.data-table
        :headers="['Nombre Parcela', 'CA', 'Provincia', 'Municipio', 'Agregado', 'Zona', 'Polígono', 'Parcela', 'Recinto', 'Com. Autónoma', 'Provincia', 'Municipio', 'Acciones']"
        empty-message="No se encontraron códigos"
        empty-description="{{ $search ? 'Intenta con otro término de búsqueda' : 'No hay códigos SIGPAC registrados' }}"
    >
        @if($codes->count() > 0)
            @foreach($codes as $code)
                @php
                    $firstPlot = $code->plots->first();
                @endphp
                <x-agro.table-row>
                    <!-- Nombre Parcela -->
                    <x-agro.table-cell>
                        <span class="text-sm font-semibold text-zinc-900">
                            {{ $firstPlot ? $firstPlot->name : 'Sin parcela asociada' }}
                        </span>
                    </x-agro.table-cell>
                    <!-- CA (Comunidad Autónoma) -->
                    <x-agro.table-cell>
                        <span class="text-sm text-zinc-700 font-mono">
                            {{ $code->code_autonomous_community ?? '-' }}
                        </span>
                    </x-agro.table-cell>
                    <!-- Provincia -->
                    <x-agro.table-cell>
                        <span class="text-sm text-zinc-700 font-mono">
                            {{ $code->code_province ?? '-' }}
                        </span>
                    </x-agro.table-cell>
                    <!-- Municipio -->
                    <x-agro.table-cell>
                        <span class="text-sm text-zinc-700 font-mono">
                            {{ $code->code_municipality ?? '-' }}
                        </span>
                    </x-agro.table-cell>
                    <!-- Agregado -->
                    <x-agro.table-cell>
                        <span class="text-sm text-zinc-700 font-mono">
                            {{ $code->code_aggregate ?? '-' }}
                        </span>
                    </x-agro.table-cell>
                    <!-- Zona -->
                    <x-agro.table-cell>
                        <span class="text-sm text-zinc-700 font-mono">
                            {{ $code->code_zone ?? '-' }}
                        </span>
                    </x-agro.table-cell>
                    <!-- Polígono -->
                    <x-agro.table-cell>
                        <span class="text-sm text-zinc-700 font-mono">
                            {{ $code->code_polygon ?? '-' }}
                        </span>
                    </x-agro.table-cell>
                    <!-- Parcela -->
                    <x-agro.table-cell>
                        <span class="text-sm text-zinc-700 font-mono">
                            {{ $code->code_plot ?? '-' }}
                        </span>
                    </x-agro.table-cell>
                    <!-- Recinto -->
                    <x-agro.table-cell>
                        <span class="text-sm text-zinc-700 font-mono">
                            {{ $code->code_enclosure ?? '-' }}
                        </span>
                    </x-agro.table-cell>
                    <!-- Comunidad Autónoma -->
                    <x-agro.table-cell>
                        <span class="text-sm text-zinc-700">
                            {{ $firstPlot && $firstPlot->autonomousCommunity ? $firstPlot->autonomousCommunity->name : '-' }}
                        </span>
                    </x-agro.table-cell>
                    <!-- Provincia -->
                    <x-agro.table-cell>
                        <span class="text-sm text-zinc-700">
                            {{ $firstPlot && $firstPlot->province ? $firstPlot->province->name : '-' }}
                        </span>
                    </x-agro.table-cell>
                    <!-- Municipio -->
                    <x-agro.table-cell>
                        <span class="text-sm text-zinc-700">
                            {{ $firstPlot && $firstPlot->municipality ? $firstPlot->municipality->name : '-' }}
                        </span>
                    </x-agro.table-cell>
                    <x-agro.table-cell align="right">
                        <div class="flex items-center gap-1 justify-end">
                            <x-agro.action-button
                                variant="view"
                                href="{{ route('plots.index', ['sigpac_code' => $code->id]) }}"
                            />
                            <x-agro.action-button
                                variant="edit"
                                href="{{ route('sigpac.codes.edit', $code->id) }}"
                            />
                            @php
                                $hasPlots = $code->plots_count > 0;
                                $firstPlot = $code->plots->first();
                                $hasGeometry = false;
                                $multipartId = null;
                                if ($hasPlots && $firstPlot && $code->multiplePlotSigpacs) {
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

                            @if($hasPlots && $firstPlot)
                                @if($hasGeometry && $multipartId)
                                    {{-- Si tiene geometría, mostrar "Ver Mapa" --}}
                                    <flux:button
                                        href="/map/{{ $firstPlot->id }}?recinto={{ $multipartId }}&return=sigpac"
                                        variant="ghost"
                                        size="sm"
                                        icon="map"
                                    >
                                        Ver Mapa
                                    </flux:button>
                                @else
                                    {{-- Si no tiene geometría, mostrar "Generar Mapa" --}}
                                    <flux:button
                                        wire:click="generateMap({{ $code->id }}, {{ $firstPlot->id }})"
                                        wire:loading.attr="disabled"
                                        variant="ghost"
                                        size="sm"
                                        icon="map"
                                    >
                                        <span wire:loading.remove wire:target="generateMap({{ $code->id }}, {{ $firstPlot->id }})">Generar Mapa</span>
                                        <span wire:loading wire:target="generateMap({{ $code->id }}, {{ $firstPlot->id }})">Generando...</span>
                                    </flux:button>
                                @endif
                            @endif
                        </div>
                    </x-agro.table-cell>
                </x-agro.table-row>
            @endforeach
            <x-slot name="pagination">
                {{ $codes->links() }}
            </x-slot>
        @endif
    </x-agro.data-table>
</div>
