<div class="space-y-6 animate-fade-in" x-data="{ showPlotAuditModal: false, currentPlotId: null }" 
     @open-plot-audit-modal.window="showPlotAuditModal = true; currentPlotId = $event.detail.plotId"
     @close-plot-audit-modal.window="showPlotAuditModal = false; currentPlotId = null">
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

    {{-- Tabs --}}
    <x-resource-view-tabs 
        :activeCount="$stats['active']"
        :inactiveCount="$stats['inactive']"
        :currentTab="$currentTab"
        onSwitch="switchTab"
    />

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
                            <button wire:click="$set('search', ''); $set('filterAutonomousCommunity', ''); $set('filterProvince', ''); $set('filterMunicipality', '')" 
                                    class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm font-medium transition">
                                Limpiar Filtros
                            </button>
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
                                @php
                                    // Obtener primera parcela del municipio para la URL
                                    $firstPlotForMap = \App\Models\Plot::forUser(auth()->user())
                                        ->where('municipality_id', $filterMunicipality)
                                        ->first();
                                @endphp
                                
                                @if($firstPlotForMap)
                                    <a
                                        href="{{ route('map', ['id' => $firstPlotForMap->id, 'municipality' => $filterMunicipality, 'return' => 'plots']) }}"
                                        class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-lg hover:from-green-700 hover:to-green-800 transition-all font-semibold shadow-lg hover:shadow-xl"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        Ver Todos los Mapas
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

    {{-- Grid de Parcelas --}}
    @php
        $plotIconPath = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>';
    @endphp
    
    <x-resource-grid 
        :items="$plots"
        emptyMessage="No hay parcelas registradas"
        emptyDescription="Comienza agregando tu primera parcela al sistema"
        :emptyIcon="$plotIconPath"
    >
            @foreach($plots as $plot)
                @php
                    $wineryName = '-';
                    if ($plot->viticulturist && $plot->viticulturist->wineries->isNotEmpty()) {
                        $wineryName = $plot->viticulturist->wineries->first()->name;
                    }
                    
                    $hasMap = \App\Models\MultipartPlotSigpac::where('plot_id', $plot->id)
                        ->whereNotNull('plot_geometry_id')
                        ->exists();
                @endphp

                <x-resource-card 
                    :title="$plot->name"
                    :subtitle="($plot->province ? $plot->province->name : '') . ($plot->municipality ? ', ' . $plot->municipality->name : '')"
                    :badge="$plot->active ? 'Activa' : 'Inactiva'"
                    :badgeColor="$plot->active ? 'green' : 'gray'"
                    hoverBorderColor="[var(--color-agro-green-light)]"
                >
                    <x-slot:content>
                        {{-- Información de Recintos SIGPAC --}}
                        <div class="mb-3">
                            @if($plot->sigpacCodes->isNotEmpty())
                                <p class="text-xs text-gray-600">
                                    <span class="font-semibold">{{ $plot->sigpacCodes->count() }}</span> recinto(s) SIGPAC
                                </p>
                            @else
                                <p class="text-xs text-gray-400 italic">Sin recintos SIGPAC</p>
                            @endif
                            @if($plot->description)
                                <p class="text-xs text-gray-500 mt-1">{{ \Illuminate\Support\Str::limit($plot->description, 60) }}</p>
                            @endif
                        </div>

                        {{-- Información de Gestión --}}
                        <div class="space-y-2 mb-4">
                            <div class="flex items-center gap-2 text-sm">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                                <span class="text-gray-600">{{ $wineryName }}</span>
                            </div>
                            <div class="flex items-center gap-2 text-sm">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <span class="text-gray-600">
                                    {{ $plot->viticulturist?->name }}
                                    @if($plot->viticulturist && $plot->viticulturist->id === auth()->id())
                                        <span class="text-[var(--color-agro-green-dark)] font-semibold">(Yo)</span>
                                    @endif
                                </span>
                            </div>
                        </div>

                        {{-- Barra de Área --}}
                        <x-progress-bar 
                            :percentage="$plot->area && $maxPlotArea > 0 ? ($plot->area / $maxPlotArea) * 100 : 0"
                            label="Área"
                            :currentValue="$plot->area"
                            :maxValue="$maxPlotArea"
                            unit="ha"
                        />
                    </x-slot:content>

                    <x-slot:actions>
                        <div class="flex flex-wrap gap-1 justify-center">
                            {{-- Ver Parcela --}}
                            <x-action-button 
                                variant="view" 
                                href="{{ route('plots.show', $plot) }}"
                            />
                            
                            {{-- Ver/Generar Mapa --}}
                            @if($hasMap)
                                <x-action-button 
                                    variant="map" 
                                    href="{{ route('map', ['id' => $plot->id, 'return' => 'plots']) }}"
                                />
                            @elseif($plot->sigpacCodes->isNotEmpty())
                                @can('update', $plot)
                                    <x-action-button 
                                        variant="generate" 
                                        wireClick="generateMap(null, {{ $plot->id }})"
                                    />
                                @endcan
                            @endif
                            
                            {{-- Historial --}}
                            <x-action-button 
                                variant="history" 
                                @click="$dispatch('open-plot-audit-modal', { plotId: {{ $plot->id }} })"
                            />
                            
                            @can('update', $plot)
                                {{-- Añadir Plantación --}}
                                <x-action-button 
                                    variant="planting" 
                                    href="{{ route('plots.plantings.create', $plot) }}"
                                />
                                
                                {{-- Editar --}}
                                <x-action-button 
                                    variant="edit" 
                                    href="{{ route('plots.edit', $plot) }}" 
                                />
                                
                                {{-- Activar/Desactivar --}}
                                <x-action-button 
                                    :variant="$plot->active ? 'deactivate' : 'activate'" 
                                    wireClick="toggleActive({{ $plot->id }})"
                                />
                            @endcan
                        </div>
                    </x-slot:actions>
                </x-resource-card>
            @endforeach
        
        <x-slot:pagination>
            {{ $plots->links() }}
        </x-slot:pagination>
        
        @can('create', \App\Models\Plot::class)
            <x-slot:emptyAction>
                <a href="{{ route('plots.create') }}" 
                   class="inline-flex items-center px-4 py-2 bg-[var(--color-agro-green)] hover:bg-[var(--color-agro-green-dark)] text-white font-medium rounded-lg transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Crear mi primera parcela
                </a>
            </x-slot:emptyAction>
        @endcan
    </x-resource-grid>
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
    
    {{-- Modal de Historial de Auditoría (dentro del div raíz) --}}
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
                                            @livewire('viticulturist.plots.plot-audit-history', ['plot' => $plot], key($plot->id))
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
