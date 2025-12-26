<div class="space-y-6 animate-fade-in">
    @php
        $plotIcon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>';
    @endphp
    <x-page-header
        :icon="$plotIcon"
        title="Gestión de Parcelas"
        description="Administra y visualiza todas tus parcelas agrícolas"
        icon-color="from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]"
    >
        <x-slot:actionButton>
            <div class="flex items-center gap-3">
                @can('create', \App\Models\Plot::class)
                    <a href="{{ route('plots.create') }}" class="group">
                        <button class="flex items-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:from-[var(--color-agro-green)] hover:to-[var(--color-agro-green-dark)] transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105 font-semibold">
                            <svg class="w-5 h-5 group-hover:rotate-90 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Nueva Parcela
                        </button>
                    </a>
                @endcan

                <a href="{{ route('plots.plantings.index') }}" class="group">
                    <button
                        class="flex items-center gap-2 px-4 py-3 rounded-xl border border-gray-300 text-gray-700 hover:bg-gray-50 transition-all duration-300 shadow-sm hover:shadow-md font-semibold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M5 3v4a1 1 0 001 1h3m10-5v4a1 1 0 01-1 1h-3M5 21v-4a1 1 0 011-1h3m10 5v-4a1 1 0 00-1-1h-3"/>
                        </svg>
                        Ver plantaciones
                    </button>
                </a>
            </div>
        </x-slot:actionButton>
    </x-page-header>

    <!-- Tabs Navigation -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
        <div class="border-b border-gray-200">
            <nav class="flex -mb-px">
                <button 
                    wire:click="switchTab('active')"
                    class="group inline-flex items-center gap-2 px-6 py-4 border-b-2 font-medium text-sm transition-colors
                        {{ $currentTab === 'active' ? 'border-[var(--color-agro-green-dark)] text-[var(--color-agro-green-dark)]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Activas</span>
                    @if($stats['active'] > 0)
                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $currentTab === 'active' ? 'bg-[var(--color-agro-green-dark)] text-white' : 'bg-gray-200 text-gray-700' }}">
                            {{ $stats['active'] }}
                        </span>
                    @endif
                </button>
                
                <button 
                    wire:click="switchTab('inactive')"
                    class="group inline-flex items-center gap-2 px-6 py-4 border-b-2 font-medium text-sm transition-colors
                        {{ $currentTab === 'inactive' ? 'border-[var(--color-agro-green-dark)] text-[var(--color-agro-green-dark)]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Inactivas</span>
                    @if($stats['inactive'] > 0)
                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $currentTab === 'inactive' ? 'bg-[var(--color-agro-green-dark)] text-white' : 'bg-gray-200 text-gray-700' }}">
                            {{ $stats['inactive'] }}
                        </span>
                    @endif
                </button>

                <button 
                    wire:click="switchTab('statistics')"
                    class="group inline-flex items-center gap-2 px-6 py-4 border-b-2 font-medium text-sm transition-colors
                        {{ $currentTab === 'statistics' ? 'border-[var(--color-agro-green-dark)] text-[var(--color-agro-green-dark)]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span>Estadísticas</span>
                </button>
            </nav>
        </div>

        <div class="p-6">
            {{-- ACTIVE/INACTIVE TABS --}}
            @if($currentTab === 'active' || $currentTab === 'inactive')
                <!-- Filtros -->
                <x-filter-section title="Filtros de Búsqueda" color="green">
                    <x-filter-input 
                        wire:model.live.debounce.300ms="search" 
                        placeholder="Buscar por nombre de parcela..."
                    />
                    <x-filter-select wire:model.live="filterAutonomousCommunity">
                        <option value="">Todas las comunidades</option>
                        @foreach($this->autonomousCommunities as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </x-filter-select>
                    @if($filterAutonomousCommunity)
                        <x-filter-select wire:model.live="filterProvince">
                            <option value="">Todas las provincias</option>
                            @foreach($this->provinces as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </x-filter-select>
                    @endif
                    @if($filterProvince)
                        <x-filter-select wire:model.live="filterMunicipality">
                            <option value="">Todos los municipios</option>
                            @foreach($this->municipalities as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </x-filter-select>
                    @endif
                    <x-slot:actions>
                        @if($search || $filterAutonomousCommunity || $filterProvince || $filterMunicipality)
                            <x-button wire:click="$set('search', ''); $set('filterAutonomousCommunity', ''); $set('filterProvince', ''); $set('filterMunicipality', '')" variant="ghost" size="sm">
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

    @php
        $headers = [
            ['label' => 'Nombre', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>'],
            // Bodega ahora se deriva de la(s) winery(s) del viticultor de la parcela
            ['label' => 'Bodega', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>'],
        ];
        
        if(auth()->user()->canSelectViticulturist()) {
            $headers[] = ['label' => 'Viticultor', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>'];
        }
        
        $headers[] = ['label' => 'Área', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>'];
        $headers[] = ['label' => 'Estado', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'];
        $headers[] = ['label' => 'Comunidad Autónoma', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>'];
        $headers[] = ['label' => 'Provincia', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>'];
        $headers[] = ['label' => 'Municipio', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>'];
        $headers[] = 'Acciones';
    @endphp

    <x-data-table :headers="$headers" empty-message="No hay parcelas registradas" empty-description="Comienza agregando tu primera parcela al sistema">
        @if($plots->count() > 0)
            @foreach($plots as $plot)
                <x-table-row>
                    <x-table-cell>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-[var(--color-agro-green-light)] to-[var(--color-agro-green)] flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform duration-200">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                </svg>
                            </div>
                            <div>
                                <div class="text-sm font-bold text-gray-900">{{ $plot->name }}</div>
                                @if($plot->description)
                                    <div class="text-sm text-gray-500 mt-1">{{ \Illuminate\Support\Str::limit($plot->description, 50) }}</div>
                                @endif
                            </div>
                        </div>
                    </x-table-cell>
                    <x-table-cell>
                        @php
                            // La bodega se infiere de las wineries asociadas al viticultor de la parcela
                            $wineryName = '-';
                            if ($plot->viticulturist && $plot->viticulturist->wineries->isNotEmpty()) {
                                $wineryName = $plot->viticulturist->wineries->first()->name;
                            }
                        @endphp
                        <span class="text-sm font-medium text-gray-900">{{ $wineryName }}</span>
                    </x-table-cell>
                    @if(auth()->user()->canSelectViticulturist())
                        <x-table-cell>
                            <span class="text-sm text-gray-700">{{ $plot->viticulturist?->name ?? 'Sin asignar' }}</span>
                        </x-table-cell>
                    @endif
                    <x-table-cell>
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-lg bg-[var(--color-agro-blue-light)] text-[var(--color-agro-blue)] text-sm font-semibold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                            </svg>
                            {{ $plot->area ? number_format($plot->area, 3) . ' ha' : '-' }}
                        </span>
                    </x-table-cell>
                    <x-table-cell>
                        <x-status-badge :active="$plot->active" />
                    </x-table-cell>
                    <!-- Comunidad Autónoma -->
                    <x-table-cell>
                        <span class="text-sm text-gray-700">
                            {{ $plot->autonomousCommunity ? $plot->autonomousCommunity->name : '-' }}
                        </span>
                    </x-table-cell>
                    <!-- Provincia -->
                    <x-table-cell>
                        <span class="text-sm text-gray-700">
                            {{ $plot->province ? $plot->province->name : '-' }}
                        </span>
                    </x-table-cell>
                    <!-- Municipio -->
                    <x-table-cell>
                        <span class="text-sm text-gray-700">
                            {{ $plot->municipality ? $plot->municipality->name : '-' }}
                        </span>
                    </x-table-cell>
                    <x-table-actions align="right">
                        @php
                            $hasSigpacCodes = $plot->sigpacCodes && $plot->sigpacCodes->count() > 0;
                            $hasGeometry = $hasSigpacCodes && $plot->multiplePlotSigpacs && $plot->multiplePlotSigpacs->whereNotNull('plot_geometry_id')->isNotEmpty();
                        @endphp
                        
                        @if($hasSigpacCodes)
                            @if($hasGeometry)
                                {{-- Si tiene geometría, mostrar "Ver Mapa" --}}
                                <a href="/map/{{ $plot->id }}?return=plots"
                                   class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg transition-all duration-200 text-green-600 bg-green-50 hover:bg-green-100 font-medium text-sm"
                                   title="Ver Mapa">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                    </svg>
                                    Ver Mapa
                                </a>
                            @else
                                {{-- Si no tiene geometría, mostrar "Generar Mapa" --}}
                                @can('update', $plot)
                                    <button
                                        wire:click="generateMap({{ $plot->id }})"
                                        wire:loading.attr="disabled"
                                        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg transition-all duration-200 text-blue-600 bg-blue-50 hover:bg-blue-100 font-medium text-sm disabled:opacity-50"
                                        title="Generar Mapa">
                                        <span wire:loading.remove wire:target="generateMap({{ $plot->id }})">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                            </svg>
                                            Generar Mapa
                                        </span>
                                        <span wire:loading wire:target="generateMap({{ $plot->id }})" class="flex items-center gap-2">
                                            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            Generando...
                                        </span>
                                    </button>
                                @endcan
                            @endif
                        @endif
                        
                        {{-- Botones estándar siempre visibles --}}
                        <x-action-button variant="view" href="{{ route('plots.show', $plot) }}" />
                        
                        {{-- Botón de Historial de Auditoría --}}
                        <button
                            @click="$dispatch('open-plot-audit-modal', { plotId: {{ $plot->id }} })"
                            class="p-2 rounded-lg transition-all duration-200 group/btn text-gray-600 hover:bg-gray-100"
                            title="Ver historial de cambios">
                            <svg class="w-5 h-5 group-hover/btn:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </button>
                        
                        @can('update', $plot)

                            {{-- Crear plantación sobre esta parcela --}}
                            <a href="{{ route('plots.plantings.create', $plot) }}"
                               class="p-2 rounded-lg transition-all duration-200 group/btn text-[var(--color-agro-green-dark)] hover:bg-[var(--color-agro-green-bg)]"
                               title="Añadir plantación">
                                <svg class="w-5 h-5 group-hover/btn:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                            </a>

                            <x-action-button variant="edit" href="{{ route('plots.edit', $plot) }}" />
                        @endcan
                        @can('update', $plot)
                            <button 
                                wire:click="toggleActive({{ $plot->id }})"
                                wire:loading.attr="disabled"
                                wire:target="toggleActive({{ $plot->id }})"
                                class="p-2 rounded-lg transition-all duration-200 group/btn {{ $plot->active ? 'text-orange-600 hover:bg-orange-50' : 'text-green-600 hover:bg-green-50' }} disabled:opacity-50 disabled:cursor-not-allowed"
                                title="{{ $plot->active ? 'Desactivar parcela' : 'Activar parcela' }}"
                            >
                                <span wire:loading.remove wire:target="toggleActive({{ $plot->id }})">
                                    @if($plot->active)
                                        <svg class="w-5 h-5 group-hover/btn:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    @else
                                        <svg class="w-5 h-5 group-hover/btn:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    @endif
                                </span>
                                <span wire:loading wire:target="toggleActive({{ $plot->id }})" class="inline-block">
                                    <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </span>
                            </button>
                        @endcan
                    </x-table-actions>
                </x-table-row>
            @endforeach
            <x-slot name="pagination">
                {{ $plots->links() }}
            </x-slot>
        @else
            <x-slot name="emptyAction">
                @can('create', \App\Models\Plot::class)
                    <x-button href="{{ route('plots.create') }}" variant="primary">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Crear mi primera parcela
                    </x-button>
                @endcan
            </x-slot>
        @endif
    </x-data-table>
            @endif

            {{-- STATISTICS TAB --}}
            @if($currentTab === 'statistics')
                <div class="space-y-6">
                    {{-- Filtro de Año --}}
                    <div class="flex justify-end">
                        <select wire:model.live="yearFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[var(--color-agro-green)] focus:border-transparent">
                            @for($year = now()->year; $year >= now()->year - 5; $year--)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endfor
                        </select>
                    </div>

                    {{-- KPIs --}}
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6 border border-green-200">
                            <p class="text-sm font-medium text-green-700">Superficie Total</p>
                            <p class="text-3xl font-bold text-green-900 mt-1">{{ number_format($advancedStats['totalSurface'] ?? 0, 2) }} ha</p>
                            <p class="text-xs text-green-600 mt-2">Todas las parcelas</p>
                        </div>
                        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6 border border-blue-200">
                            <p class="text-sm font-medium text-blue-700">Superficie Elegible PAC</p>
                            <p class="text-3xl font-bold text-blue-900 mt-1">{{ number_format($advancedStats['eligibleSurface'] ?? 0, 2) }} ha</p>
                            <p class="text-xs text-blue-600 mt-2">{{ number_format($advancedStats['eligibilityPercentage'] ?? 0, 1) }}% del total</p>
                        </div>
                        <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-6 border border-purple-200">
                            <p class="text-sm font-medium text-purple-700">Parcelas Activas</p>
                            <p class="text-3xl font-bold text-purple-900 mt-1">{{ $stats['active'] }}</p>
                            <p class="text-xs text-purple-600 mt-2">De {{ $stats['total'] }} totales</p>
                        </div>
                        <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl p-6 border border-orange-200">
                            <p class="text-sm font-medium text-orange-700">Superficie Media</p>
                            <p class="text-3xl font-bold text-orange-900 mt-1">{{ number_format($advancedStats['avgSurfacePerPlot'] ?? 0, 2) }} ha</p>
                            <p class="text-xs text-orange-600 mt-2">Por parcela</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        {{-- Distribución por Régimen de Tenencia --}}
                        <div class="bg-white rounded-xl p-6 border border-gray-200">
                            <h3 class="text-lg font-bold text-gray-900 mb-4">📊 Distribución por Régimen de Tenencia</h3>
                            <div class="space-y-4">
                                @forelse(($advancedStats['tenureStats'] ?? []) as $regime => $data)
                                    @php
                                        $total = ($advancedStats['tenureStats'] ?? [])->sum('count');
                                        $percentage = $total > 0 ? ($data['count'] / $total) * 100 : 0;
                                        $regimeName = match($regime) {
                                            'owned' => 'Propiedad',
                                            'leased' => 'Arrendamiento',
                                            'shared' => 'Compartida',
                                            default => ucfirst($regime),
                                        };
                                    @endphp
                                    <div>
                                        <div class="flex justify-between mb-2">
                                            <span class="text-sm font-medium text-gray-700">{{ $regimeName }}</span>
                                            <span class="text-sm font-bold text-gray-900">{{ $data['count'] }} ({{ number_format($percentage, 1) }}%)</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-3">
                                            <div class="bg-[var(--color-agro-green)] h-3 rounded-full" style="width: {{ $percentage }}%"></div>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1">{{ number_format($data['surface'], 2) }} ha</p>
                                    </div>
                                @empty
                                    <p class="text-gray-500 text-center py-4">No hay datos de régimen de tenencia</p>
                                @endforelse
                            </div>
                        </div>

                        {{-- Estado de Parcelas --}}
                        <div class="bg-white rounded-xl p-6 border border-gray-200">
                            <h3 class="text-lg font-bold text-gray-900 mb-4">🔒 Estado de Parcelas</h3>
                            <div class="space-y-4">
                                <div>
                                    <div class="flex justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-700">Bloqueadas</span>
                                        <span class="text-sm font-bold text-gray-900">{{ $advancedStats['lockedPlots'] ?? 0 }}</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-3">
                                        @php
                                            $lockedPct = $stats['total'] > 0 ? (($advancedStats['lockedPlots'] ?? 0) / $stats['total']) * 100 : 0;
                                        @endphp
                                        <div class="bg-red-500 h-3 rounded-full" style="width: {{ $lockedPct }}%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-700">Desbloqueadas</span>
                                        <span class="text-sm font-bold text-gray-900">{{ $advancedStats['unlockedPlots'] ?? 0 }}</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-3">
                                        @php
                                            $unlockedPct = $stats['total'] > 0 ? (($advancedStats['unlockedPlots'] ?? 0) / $stats['total']) * 100 : 0;
                                        @endphp
                                        <div class="bg-green-500 h-3 rounded-full" style="width: {{ $unlockedPct }}%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-700">Con SIGPAC</span>
                                        <span class="text-sm font-bold text-gray-900">{{ $advancedStats['withSigpac'] ?? 0 }}</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-3">
                                        @php
                                            $sigpacPct = $stats['total'] > 0 ? (($advancedStats['withSigpac'] ?? 0) / $stats['total']) * 100 : 0;
                                        @endphp
                                        <div class="bg-blue-500 h-3 rounded-full" style="width: {{ $sigpacPct }}%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-700">Con Plantaciones</span>
                                        <span class="text-sm font-bold text-gray-900">{{ $advancedStats['withPlantings'] ?? 0 }}</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-3">
                                        @php
                                            $plantingsPct = $stats['total'] > 0 ? (($advancedStats['withPlantings'] ?? 0) / $stats['total']) * 100 : 0;
                                        @endphp
                                        <div class="bg-purple-500 h-3 rounded-full" style="width: {{ $plantingsPct }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Top Provincias --}}
                    <div class="bg-white rounded-xl p-6 border border-gray-200">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">🗺️ Top 10 Provincias por Superficie</h3>
                        <div class="space-y-3">
                            @forelse(($advancedStats['provinceStats'] ?? []) as $index => $province)
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                    <div class="flex items-center gap-3">
                                        <span class="flex-shrink-0 w-8 h-8 rounded-full bg-[var(--color-agro-green)] text-white flex items-center justify-center font-bold text-sm">
                                            {{ $index + 1 }}
                                        </span>
                                        <div>
                                            <p class="font-semibold text-gray-900">{{ $province['province_name'] }}</p>
                                            <p class="text-xs text-gray-500">{{ $province['count'] }} parcelas</p>
                                        </div>
                                    </div>
                                    <span class="font-bold text-[var(--color-agro-green-dark)]">{{ number_format($province['surface'], 2) }} ha</span>
                                </div>
                            @empty
                                <p class="text-gray-500 text-center py-4">No hay datos de provincias</p>
                            @endforelse
                        </div>
                    </div>

                    {{-- Nuevas Parcelas --}}
                    <div class="bg-white rounded-xl p-6 border border-gray-200">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">📈 Nuevas Parcelas (Últimos 12 meses)</h3>
                        <div class="h-64 flex items-end justify-between gap-2">
                            @foreach(($advancedStats['newPlotsByMonth'] ?? []) as $month)
                                <div class="flex-1 flex flex-col items-center">
                                    <div class="w-full bg-[var(--color-agro-green)] rounded-t-lg transition-all hover:bg-[var(--color-agro-green-dark)]" 
                                        style="height: {{ $month['count'] > 0 ? ($month['count'] / max(collect($advancedStats['newPlotsByMonth'] ?? [])->pluck('count')->max(), 1)) * 100 : 5 }}%"
                                        title="{{ $month['count'] }} parcelas"></div>
                                    <span class="text-xs text-gray-600 mt-2">{{ $month['month'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
    
    {{-- Modal de Historial de Auditoría --}}
    <div x-data="{ showPlotAuditModal: false, currentPlotId: null }" 
         @open-plot-audit-modal.window="showPlotAuditModal = true; currentPlotId = $event.detail.plotId"
         @close-plot-audit-modal.window="showPlotAuditModal = false; currentPlotId = null">
        
        {{-- Overlay --}}
        <div x-show="showPlotAuditModal" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity z-40"
             @click="showPlotAuditModal = false"
             style="display: none;">
        </div>

        {{-- Modal --}}
        <div x-show="showPlotAuditModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="fixed inset-0 z-50 overflow-y-auto"
             style="display: none;">
            
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-4xl">
                    {{-- Header --}}
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900">
                                📋 Historial de Auditoría
                            </h3>
                            <button @click="showPlotAuditModal = false" 
                                    class="text-gray-400 hover:text-gray-500 focus:outline-none">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Content --}}
                    <div class="px-6 py-4 max-h-[70vh] overflow-y-auto">
                        <div x-show="currentPlotId">
                            <template x-if="currentPlotId">
                                <div>
                                    @foreach($plots as $plot)
                                        <div x-show="currentPlotId == {{ $plot->id }}" 
                                             x-transition:enter="transition ease-out duration-200"
                                             x-transition:enter-start="opacity-0"
                                             x-transition:enter-end="opacity-100"
                                             style="display: none;">
                                            @livewire('viticulturist.plots.plot-audit-history', ['plot' => $plot], key('plot-audit-' . $plot->id))
                                        </div>
                                    @endforeach
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="bg-gray-50 px-6 py-3 border-t border-gray-200 flex justify-end">
                        <button @click="showPlotAuditModal = false"
                                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
