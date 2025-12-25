<div class="space-y-6 animate-fade-in">
    @php
        $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>';
    @endphp
    <x-page-header
        :icon="$icon"
        title="Rendimientos Estimados"
        description="Gestiona las estimaciones de rendimiento por plantación y campaña"
        icon-color="from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]"
    >
        <x-slot:actionButton>
            <x-button href="{{ route('viticulturist.digital-notebook.estimated-yields.create') }}" variant="primary">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nueva Estimación
            </x-button>
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
                    placeholder="Buscar por parcela, variedad, notas..."
                />
                <x-filter-select wire:model.live="selectedCampaign">
                    <option value="">Todas las campañas</option>
                    @foreach($campaigns as $campaign)
                        <option value="{{ $campaign->id }}">Campaña {{ $campaign->year }}</option>
                    @endforeach
                </x-filter-select>
                <x-filter-select wire:model.live="filterStatus">
                    <option value="">Todos los estados</option>
                    <option value="draft">Borrador</option>
                    <option value="confirmed">Confirmada</option>
                </x-filter-select>
                <x-slot:actions>
                    @if($search || $selectedCampaign || $filterStatus)
                        <x-button wire:click="$set('search', ''); $set('selectedCampaign', ''); $set('filterStatus', '')" variant="ghost" size="sm">
                            Limpiar Filtros
                        </x-button>
                    @endif
                </x-slot:actions>
            </x-filter-section>

    <!-- Tabla de Rendimientos Estimados -->
    @php
        $headers = [
            ['label' => 'Plantación', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>'],
            ['label' => 'Campaña', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>'],
            ['label' => 'Rendimiento Estimado', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>'],
            ['label' => 'Rendimiento Real', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>'],
            ['label' => 'Diferencia', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>'],
            ['label' => 'Estado', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'],
            'Acciones',
        ];
    @endphp

    <x-data-table 
        :headers="$headers" 
        empty-message="No hay rendimientos estimados registrados" 
        empty-description="{{ ($search || $selectedCampaign || $filterStatus) ? 'No se encontraron estimaciones con los filtros seleccionados' : 'Comienza creando tu primera estimación de rendimiento' }}"
        color="green"
    >
        @if($estimatedYields->count() > 0)
            @foreach($estimatedYields as $yield)
                <x-table-row wire:key="yield-{{ $yield->id }}">
                    <x-table-cell>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-[var(--color-agro-green-light)] to-[var(--color-agro-green)] flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform duration-200">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                </svg>
                            </div>
                            <div>
                                <div class="text-sm font-bold text-gray-900">
                                    {{ $yield->plotPlanting->plot->name ?? 'Sin parcela' }}
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ $yield->plotPlanting->grapeVariety->name ?? 'Sin variedad' }}
                                </div>
                            </div>
                        </div>
                    </x-table-cell>
                    <x-table-cell>
                        <div class="text-sm font-medium text-gray-900">
                            {{ $yield->campaign->name ?? 'Sin campaña' }}
                        </div>
                        <div class="text-xs text-gray-500 mt-1">
                            {{ $yield->estimation_date->format('d/m/Y') }}
                        </div>
                    </x-table-cell>
                    <x-table-cell>
                        <div>
                            <div class="text-sm font-bold text-gray-900">
                                {{ number_format($yield->estimated_total_yield, 2) }} kg
                            </div>
                            <div class="text-xs text-gray-500 mt-1">
                                {{ number_format($yield->estimated_yield_per_hectare, 2) }} kg/ha
                            </div>
                        </div>
                    </x-table-cell>
                    <x-table-cell>
                        @if($yield->hasActualYield())
                            <div>
                                <div class="text-sm font-bold text-green-700">
                                    {{ number_format($yield->actual_total_yield, 2) }} kg
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ number_format($yield->actual_yield_per_hectare, 2) }} kg/ha
                                </div>
                            </div>
                        @else
                            <span class="text-sm text-gray-400">Sin datos reales</span>
                        @endif
                    </x-table-cell>
                    <x-table-cell>
                        @if($yield->hasActualYield() && $yield->variance_percentage !== null)
                            <div class="text-sm font-bold {{ $yield->variance_percentage > 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $yield->variance_percentage > 0 ? '+' : '' }}{{ number_format($yield->variance_percentage, 2) }}%
                            </div>
                            <div class="text-xs text-gray-500 mt-1">
                                {{ $yield->variance_percentage > 0 ? 'Mayor' : 'Menor' }} al estimado
                            </div>
                        @else
                            <span class="text-sm text-gray-400">-</span>
                        @endif
                    </x-table-cell>
                    <x-table-cell>
                        @php
                            $statusColors = [
                                'draft' => 'bg-gray-100 text-gray-800',
                                'confirmed' => 'bg-green-100 text-green-800',
                                'archived' => 'bg-blue-100 text-blue-800',
                            ];
                            $statusLabels = [
                                'draft' => 'Borrador',
                                'confirmed' => 'Confirmada',
                                'archived' => 'Archivada',
                            ];
                            $color = $statusColors[$yield->status] ?? 'bg-gray-100 text-gray-800';
                            $label = $statusLabels[$yield->status] ?? ucfirst($yield->status);
                        @endphp
                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $color }}">
                            {{ $label }}
                        </span>
                    </x-table-cell>
                    <x-table-actions>
                        <a 
                            href="{{ route('viticulturist.digital-notebook.estimated-yields.edit', $yield->id) }}"
                            class="p-2 rounded-lg text-blue-600 hover:bg-blue-50 transition"
                            title="Editar estimación"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </a>
                        <button 
                            wire:click="toggleActive({{ $yield->id }})"
                            wire:loading.attr="disabled"
                            wire:target="toggleActive({{ $yield->id }})"
                            class="p-2 rounded-lg transition-all duration-200 group/btn {{ $yield->active ? 'text-orange-600 hover:bg-orange-50' : 'text-green-600 hover:bg-green-50' }} disabled:opacity-50 disabled:cursor-not-allowed"
                            title="{{ $yield->active ? 'Desactivar estimación' : 'Activar estimación' }}"
                        >
                            <span wire:loading.remove wire:target="toggleActive({{ $yield->id }})">
                                @if($yield->active)
                                    <svg class="w-5 h-5 group-hover/btn:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                @else
                                    <svg class="w-5 h-5 group-hover/btn:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                @endif
                            </span>
                            <span wire:loading wire:target="toggleActive({{ $yield->id }})" class="inline-block">
                                <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </span>
                        </button>
                    </x-table-actions>
                </x-table-row>
            @endforeach
        @endif
        <x-slot name="pagination">
            {{ $estimatedYields->links() }}
        </x-slot>
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
                        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6 border border-blue-200">
                            <p class="text-sm font-medium text-blue-700">Total Estimaciones</p>
                            <p class="text-3xl font-bold text-blue-900 mt-1">{{ $stats['total'] }}</p>
                            <p class="text-xs text-blue-600 mt-2">Todas las estimaciones</p>
                        </div>
                        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6 border border-green-200">
                            <p class="text-sm font-medium text-green-700">Confirmadas</p>
                            <p class="text-3xl font-bold text-green-900 mt-1">{{ $stats['confirmed'] }}</p>
                            <p class="text-xs text-green-600 mt-2">Estado confirmado</p>
                        </div>
                        <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-6 border border-purple-200">
                            <p class="text-sm font-medium text-purple-700">Con Rendimiento Real</p>
                            <p class="text-3xl font-bold text-purple-900 mt-1">{{ $stats['with_actual'] }}</p>
                            <p class="text-xs text-purple-600 mt-2">Con datos reales</p>
                        </div>
                        <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl p-6 border border-orange-200">
                            <p class="text-sm font-medium text-orange-700">Diferencia Promedio</p>
                            <p class="text-3xl font-bold text-orange-900 mt-1">{{ number_format($advancedStats['averageVariance'] ?? 0, 1) }}%</p>
                            <p class="text-xs text-orange-600 mt-2">Estimado vs Real</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        {{-- Distribución por Estado --}}
                        <div class="bg-white rounded-xl p-6 border border-gray-200">
                            <h3 class="text-lg font-bold text-gray-900 mb-4">📊 Distribución por Estado</h3>
                            <div class="space-y-4">
                                @php
                                    $total = ($advancedStats['statusStats'] ?? collect())->sum();
                                @endphp
                                @forelse(($advancedStats['statusStats'] ?? []) as $status => $count)
                                    @php
                                        $statusLabels = [
                                            'draft' => 'Borrador',
                                            'confirmed' => 'Confirmada',
                                            'archived' => 'Archivada',
                                        ];
                                        $statusColors = [
                                            'draft' => 'bg-gray-500',
                                            'confirmed' => 'bg-green-500',
                                            'archived' => 'bg-blue-500',
                                        ];
                                        $percentage = $total > 0 ? ($count / $total) * 100 : 0;
                                    @endphp
                                    <div>
                                        <div class="flex justify-between mb-2">
                                            <span class="text-sm font-medium text-gray-700">{{ $statusLabels[$status] ?? ucfirst($status) }}</span>
                                            <span class="text-sm font-bold text-gray-900">{{ $count }} ({{ number_format($percentage, 1) }}%)</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-3">
                                            <div class="{{ $statusColors[$status] ?? 'bg-gray-500' }} h-3 rounded-full" style="width: {{ $percentage }}%"></div>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-gray-500 text-center py-4">No hay datos de estados</p>
                                @endforelse
                            </div>
                        </div>

                        {{-- Distribución por Método --}}
                        <div class="bg-white rounded-xl p-6 border border-gray-200">
                            <h3 class="text-lg font-bold text-gray-900 mb-4">🔬 Distribución por Método</h3>
                            <div class="space-y-4">
                                @php
                                    $methodTotal = ($advancedStats['methodStats'] ?? collect())->sum();
                                @endphp
                                @forelse(($advancedStats['methodStats'] ?? []) as $method => $count)
                                    @php
                                        $methodLabels = [
                                            'visual' => 'Visual',
                                            'sampling' => 'Muestreo',
                                            'historical' => 'Histórico',
                                            'ai' => 'IA',
                                        ];
                                        $percentage = $methodTotal > 0 ? ($count / $methodTotal) * 100 : 0;
                                    @endphp
                                    <div>
                                        <div class="flex justify-between mb-2">
                                            <span class="text-sm font-medium text-gray-700">{{ $methodLabels[$method] ?? ucfirst($method) }}</span>
                                            <span class="text-sm font-bold text-gray-900">{{ $count }} ({{ number_format($percentage, 1) }}%)</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-3">
                                            <div class="bg-[var(--color-agro-green)] h-3 rounded-full" style="width: {{ $percentage }}%"></div>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-gray-500 text-center py-4">No hay datos de métodos</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    {{-- Totales Estimado vs Real --}}
                    <div class="bg-white rounded-xl p-6 border border-gray-200">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">📈 Totales Estimado vs Real ({{ $yearFilter }})</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <p class="text-sm text-gray-600 mb-2">Total Estimado</p>
                                <p class="text-3xl font-bold text-blue-600">{{ number_format($advancedStats['totalEstimated'] ?? 0, 0) }} kg</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 mb-2">Total Real</p>
                                <p class="text-3xl font-bold text-green-600">{{ number_format($advancedStats['totalActual'] ?? 0, 0) }} kg</p>
                            </div>
                        </div>
                        @if(($advancedStats['totalEstimated'] ?? 0) > 0)
                            @php
                                $accuracy = abs(($advancedStats['totalActual'] ?? 0) - ($advancedStats['totalEstimated'] ?? 0)) / ($advancedStats['totalEstimated'] ?? 1) * 100;
                            @endphp
                            <div class="mt-4">
                                <p class="text-sm text-gray-600 mb-2">Precisión</p>
                                <div class="w-full bg-gray-200 rounded-full h-3">
                                    <div class="bg-purple-500 h-3 rounded-full" style="width: {{ min(100, 100 - $accuracy) }}%"></div>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Diferencia: {{ number_format($accuracy, 2) }}%</p>
                            </div>
                        @endif
                    </div>

                    {{-- Top 10 Estimaciones con Mayor Diferencia --}}
                    <div class="bg-white rounded-xl p-6 border border-gray-200">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">🏆 Top 10 Estimaciones con Mayor Diferencia ({{ $yearFilter }})</h3>
                        <div class="space-y-3">
                            @forelse(($advancedStats['topVariances'] ?? []) as $index => $yield)
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                    <div class="flex items-center gap-3">
                                        <span class="flex-shrink-0 w-8 h-8 rounded-full bg-[var(--color-agro-green)] text-white flex items-center justify-center font-bold text-sm">
                                            {{ $index + 1 }}
                                        </span>
                                        <div>
                                            <p class="font-semibold text-gray-900">{{ $yield['plot_name'] }}</p>
                                            <p class="text-xs text-gray-500">{{ $yield['variety'] }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold {{ $yield['variance'] > 0 ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $yield['variance'] > 0 ? '+' : '' }}{{ number_format($yield['variance'], 2) }}%
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            Est: {{ number_format($yield['estimated'], 0) }} kg
                                        </p>
                                    </div>
                                </div>
                            @empty
                                <p class="text-gray-500 text-center py-4">No hay datos de diferencias</p>
                            @endforelse
                        </div>
                    </div>

                    {{-- Estimaciones por Mes --}}
                    <div class="bg-white rounded-xl p-6 border border-gray-200">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">📅 Estimaciones por Mes (Últimos 12 meses)</h3>
                        <div class="h-64 flex items-end justify-between gap-2">
                            @foreach(($advancedStats['yieldsByMonth'] ?? []) as $month)
                                <div class="flex-1 flex flex-col items-center">
                                    <div class="w-full bg-[var(--color-agro-green)] rounded-t-lg transition-all hover:bg-[var(--color-agro-green-dark)]" 
                                        style="height: {{ $month['count'] > 0 ? ($month['count'] / max(collect($advancedStats['yieldsByMonth'] ?? [])->pluck('count')->max(), 1)) * 100 : 5 }}%"
                                        title="{{ $month['count'] }} estimaciones"></div>
                                    <span class="text-xs text-gray-600 mt-2">{{ $month['month'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Distribución por Campaña --}}
                    <div class="bg-white rounded-xl p-6 border border-gray-200">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">📊 Distribución por Campaña ({{ $yearFilter }})</h3>
                        <div class="space-y-4">
                            @forelse(($advancedStats['campaignStats'] ?? []) as $campaign)
                                <div>
                                    <div class="flex justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-700">{{ $campaign['name'] }}</span>
                                        <span class="text-sm font-bold text-gray-900">{{ $campaign['count'] }} estimaciones</span>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4 text-xs text-gray-600">
                                        <div>
                                            <span>Estimado: {{ number_format($campaign['total_estimated'], 0) }} kg</span>
                                        </div>
                                        <div>
                                            <span>Real: {{ number_format($campaign['total_actual'], 0) }} kg</span>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-gray-500 text-center py-4">No hay datos de campañas</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

