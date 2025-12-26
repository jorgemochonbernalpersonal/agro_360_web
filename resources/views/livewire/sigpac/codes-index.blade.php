<div class="space-y-6 animate-fade-in">
    <!-- Header -->
    @php
        $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>';
    @endphp
    <x-page-header
        :icon="$icon"
        title="Códigos SIGPAC"
        description="Gestiona los códigos de identificación SIGPAC"
        icon-color="from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]"
    >
        <x-slot:actionButton>
            <a 
                href="{{ route('sigpac.codes.create') }}"
                class="px-4 py-2 rounded-xl bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:from-[var(--color-agro-green)] hover:to-[var(--color-agro-green-dark)] transition-all font-semibold"
            >
                + Crear Código SIGPAC
            </a>
        </x-slot:actionButton>
    </x-page-header>

    <!-- Búsqueda y Filtros -->
    <x-filter-section title="Filtros de Búsqueda" color="green">
        <x-filter-input 
            wire:model.live.debounce.300ms="search" 
            placeholder="Buscar por código..."
        />
        <x-filter-select wire:model.live="filterAutonomousCommunity">
            <option value="">Todas las Comunidades Autónomas</option>
            @foreach($this->autonomousCommunities as $id => $name)
                <option value="{{ $id }}">{{ $name }}</option>
            @endforeach
        </x-filter-select>
        @if($filterAutonomousCommunity)
            <x-filter-select wire:model.live="filterProvince">
                <option value="">Todas las Provincias</option>
                @foreach($this->provinces as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </x-filter-select>
        @endif
        @if($filterProvince)
            <x-filter-select wire:model.live="filterMunicipality">
                <option value="">Todos los Municipios</option>
                @foreach($this->municipalities as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </x-filter-select>
        @endif
        <x-slot:actions>
            @if($search || $filterAutonomousCommunity || $filterProvince || $filterMunicipality)
                <x-button wire:click="clearFilters" variant="ghost" size="sm">
                    Limpiar Filtros
                </x-button>
            @endif
        </x-slot:actions>
    </x-filter-section>

    <!-- ✅ Acciones Masivas para Municipio -->
    @if($filterAutonomousCommunity && $filterProvince && $filterMunicipality && $this->municipalityHasSigpacCodes)
        <div class="glass-card rounded-xl p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-green-400 to-green-600 flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">
                            Acciones para {{ $this->municipalities[$filterMunicipality] ?? 'Municipio' }}
                        </h3>
                        <p class="text-sm text-gray-600">
                            {{ $this->provinces[$filterProvince] ?? '' }}, {{ $this->autonomousCommunities[$filterAutonomousCommunity] ?? '' }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <!-- Botón Generar Todos los Mapas -->
                    <button
                        wire:click="generateAllMapsForMunicipality"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all font-semibold shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span wire:loading.remove wire:target="generateAllMapsForMunicipality">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                        </span>
                        <span wire:loading wire:target="generateAllMapsForMunicipality">
                            <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                        <span wire:loading.remove wire:target="generateAllMapsForMunicipality">Generar Todos los Mapas</span>
                        <span wire:loading wire:target="generateAllMapsForMunicipality">Generando...</span>
                    </button>

                    <!-- Botón Ver Todos los Mapas -->
                    <a
                        href="{{ route('sigpac.municipality-map', ['municipality' => $filterMunicipality]) }}"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-lg hover:from-green-700 hover:to-green-800 transition-all font-semibold shadow-lg hover:shadow-xl"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        Ver Todos los Mapas
                    </a>
                </div>
            </div>
        </div>
    @endif

    <!-- Tabla de códigos -->
    @php
        $headers = [
            ['label' => 'Nombre Parcela', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>'],
            ['label' => 'CA', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>'],
            ['label' => 'Provincia', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>'],
            ['label' => 'Municipio', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>'],
            ['label' => 'Agregado', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>'],
            ['label' => 'Zona', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>'],
            ['label' => 'Polígono', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>'],
            ['label' => 'Parcela', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>'],
            ['label' => 'Recinto', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>'],
            ['label' => 'Comunidad Autónoma', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>'],
            ['label' => 'Provincia', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>'],
            ['label' => 'Municipio', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>'],
            'Acciones',
        ];
    @endphp

    <x-data-table 
        :headers="$headers" 
        empty-message="No se encontraron códigos" 
        empty-description="{{ $search ? 'Intenta con otro término de búsqueda' : 'No hay códigos SIGPAC registrados' }}"
        color="green"
    >
        @if($codes->count() > 0)
            @foreach($codes as $code)
                @php
                    $firstPlot = $code->plots->first();
                @endphp
                <x-table-row>
                    <!-- Nombre Parcela -->
                    <x-table-cell>
                        <span class="text-sm font-semibold text-gray-900">
                            {{ $firstPlot ? $firstPlot->name : 'Sin parcela asociada' }}
                        </span>
                    </x-table-cell>
                    <!-- CA (Comunidad Autónoma) -->
                    <x-table-cell>
                        <span class="text-sm text-gray-700 font-mono">
                            {{ $code->code_autonomous_community ?? '-' }}
                        </span>
                    </x-table-cell>
                    <!-- Provincia -->
                    <x-table-cell>
                        <span class="text-sm text-gray-700 font-mono">
                            {{ $code->code_province ?? '-' }}
                        </span>
                    </x-table-cell>
                    <!-- Municipio -->
                    <x-table-cell>
                        <span class="text-sm text-gray-700 font-mono">
                            {{ $code->code_municipality ?? '-' }}
                        </span>
                    </x-table-cell>
                    <!-- Agregado -->
                    <x-table-cell>
                        <span class="text-sm text-gray-700 font-mono">
                            {{ $code->code_aggregate ?? '-' }}
                        </span>
                    </x-table-cell>
                    <!-- Zona -->
                    <x-table-cell>
                        <span class="text-sm text-gray-700 font-mono">
                            {{ $code->code_zone ?? '-' }}
                        </span>
                    </x-table-cell>
                    <!-- Polígono -->
                    <x-table-cell>
                        <span class="text-sm text-gray-700 font-mono">
                            {{ $code->code_polygon ?? '-' }}
                        </span>
                    </x-table-cell>
                    <!-- Parcela -->
                    <x-table-cell>
                        <span class="text-sm text-gray-700 font-mono">
                            {{ $code->code_plot ?? '-' }}
                        </span>
                    </x-table-cell>
                    <!-- Recinto -->
                    <x-table-cell>
                        <span class="text-sm text-gray-700 font-mono">
                            {{ $code->code_enclosure ?? '-' }}
                        </span>
                    </x-table-cell>
                    <!-- Comunidad Autónoma -->
                    <x-table-cell>
                        <span class="text-sm text-gray-700">
                            {{ $firstPlot && $firstPlot->autonomousCommunity ? $firstPlot->autonomousCommunity->name : '-' }}
                        </span>
                    </x-table-cell>
                    <!-- Provincia -->
                    <x-table-cell>
                        <span class="text-sm text-gray-700">
                            {{ $firstPlot && $firstPlot->province ? $firstPlot->province->name : '-' }}
                        </span>
                    </x-table-cell>
                    <!-- Municipio -->
                    <x-table-cell>
                        <span class="text-sm text-gray-700">
                            {{ $firstPlot && $firstPlot->municipality ? $firstPlot->municipality->name : '-' }}
                        </span>
                    </x-table-cell>
                    <x-table-actions align="right">
                        <x-action-button 
                            variant="view" 
                            href="{{ route('plots.index', ['sigpac_code' => $code->id]) }}"
                            title="Ver parcelas"
                        />
                        <x-action-button 
                            variant="edit" 
                            href="{{ route('sigpac.codes.edit', $code->id) }}"
                            title="Editar código"
                        />
                        @php
                            // Verificar si tiene parcelas asociadas
                            $hasPlots = $code->plots_count > 0;
                            
                            // Obtener primera parcela asociada
                            $firstPlot = $code->plots->first();
                            
                            // Verificar si ya tiene geometría: existe MultipartPlotSigpac con plot_geometry_id para este código y parcela
                            $hasGeometry = false;
                            if ($hasPlots && $firstPlot && $code->multiplePlotSigpacs) {
                                $hasGeometry = $code->multiplePlotSigpacs
                                    ->where('plot_id', $firstPlot->id)
                                    ->where('sigpac_code_id', $code->id)
                                    ->whereNotNull('plot_geometry_id')
                                    ->isNotEmpty();
                            }
                        @endphp
                        
                        @if($hasPlots && $firstPlot)
                            @if($hasGeometry)
                                {{-- Si tiene geometría, mostrar "Ver Mapa" --}}
                                <a href="/map/{{ $firstPlot->id }}?return=sigpac"
                                   class="inline-flex items-center justify-center px-3 py-2 text-sm font-semibold text-green-600 bg-green-50 rounded-lg hover:bg-green-100 transition-colors duration-200"
                                   title="Ver Mapa">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                    </svg>
                                    <span class="ml-1">Ver Mapa</span>
                                </a>
                            @else
                                {{-- Si no tiene geometría, mostrar "Generar Mapa" --}}
                                <button
                                    wire:click="generateMap({{ $code->id }}, {{ $firstPlot->id }})"
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center justify-center px-3 py-2 text-sm font-semibold text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors duration-200 disabled:opacity-50"
                                    title="Generar Mapa">
                                    <span wire:loading.remove wire:target="generateMap({{ $code->id }}, {{ $firstPlot->id }})">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                        </svg>
                                        <span class="ml-1">Generar Mapa</span>
                                    </span>
                                    <span wire:loading wire:target="generateMap({{ $code->id }}, {{ $firstPlot->id }})" class="flex items-center gap-2">
                                        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Generando...
                                    </span>
                                </button>
                            @endif
                        @endif
                    </x-table-actions>
                </x-table-row>
            @endforeach
            <x-slot name="pagination">
                {{ $codes->links() }}
            </x-slot>
        @endif
    </x-data-table>
</div>

