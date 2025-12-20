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

    <!-- Estadísticas -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="glass-card rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Estimaciones</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($stats['total']) }}</p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
            </div>
        </div>
        <div class="glass-card rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Confirmadas</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($stats['confirmed']) }}</p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-green-400 to-green-600 flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
        <div class="glass-card rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Con Rendimiento Real</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($stats['with_actual']) }}</p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

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
            <option value="archived">Archivada</option>
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
                <x-table-row>
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
                    <x-table-cell>
                        <div class="flex items-center gap-2">
                            <a 
                                href="{{ route('viticulturist.digital-notebook.estimated-yields.edit', $yield->id) }}"
                                class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-blue-100 text-blue-700 hover:bg-blue-200 transition"
                            >
                                Editar
                            </a>
                        </div>
                    </x-table-cell>
                </x-table-row>
            @endforeach
        @endif
    </x-data-table>

    <!-- Paginación -->
    @if($estimatedYields->hasPages())
        <div class="mt-4">
            {{ $estimatedYields->links() }}
        </div>
    @endif
</div>

