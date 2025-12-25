<div class="space-y-6 animate-fade-in">
    @php
        $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>';
    @endphp

    <x-page-header
        :icon="$icon"
        title="Plantaciones"
        description="Listado global de plantaciones de variedades en tus parcelas"
        icon-color="from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]"
    >
        <x-slot:actionButton>
            <a href="{{ route('plots.index') }}" class="group">
                <button
                    class="flex items-center gap-2 px-4 py-2 rounded-xl border border-gray-300 text-gray-700 hover:bg-gray-50 transition-all duration-300 shadow-sm hover:shadow-md font-semibold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Ver parcelas
                </button>
            </a>
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
            <x-filter-section title="Filtros de Búsqueda" color="green">
        <x-filter-input
            wire:model.live="search"
            placeholder="Buscar por parcela o variedad..."
        />
        <x-filter-select wire:model.live="status">
            <option value="">Todos los estados</option>
            <option value="active">Activa</option>
            <option value="removed">Arrancada</option>
            <option value="experimental">Experimental</option>
            <option value="replanting">Replantación</option>
        </x-filter-select>
        <x-filter-select wire:model.live="year">
            <option value="">Todos los años</option>
            @foreach ($years as $yearOption)
                <option value="{{ $yearOption }}">{{ $yearOption }}</option>
            @endforeach
        </x-filter-select>
        <x-slot:actions>
            @if($search || $status !== '' || $year !== '')
                <x-button wire:click="clearFilters" variant="ghost" size="sm">
                    Limpiar Filtros
                </x-button>
            @endif
        </x-slot:actions>
    </x-filter-section>

    @php
        $headers = [
            ['label' => 'Nombre', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>'],
            ['label' => 'Parcela', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>'],
            ['label' => 'Variedad', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4a1 1 0 001 1h3m10-5v4a1 1 0 01-1 1h-3M5 21v-4a1 1 0 011-1h3m10 5v-4a1 1 0 00-1-1h-3"/></svg>'],
            ['label' => 'Superficie (ha)', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>'],
            ['label' => 'Año', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5A2 2 0 003 7v12a2 2 0 002 2z"/></svg>'],
            ['label' => 'Estado', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'],
            ['label' => 'Viticultor', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>'],
            'Acciones',
        ];
    @endphp

    <x-data-table :headers="$headers" empty-message="No hay plantaciones registradas" empty-description="Comienza creando una plantación sobre una parcela">
        @if($plantings->count() > 0)
            @foreach($plantings as $planting)
                <x-table-row wire:key="planting-{{ $planting->id }}">
                    <x-table-cell>
                        @if($planting->name)
                            <span class="text-sm font-semibold text-purple-700">
                                {{ $planting->name }}
                            </span>
                        @else
                            <span class="text-sm text-gray-400 italic">—</span>
                        @endif
                    </x-table-cell>

                    <x-table-cell>
                        <div class="flex flex-col">
                            <a href="{{ route('plots.show', $planting->plot) }}"
                               class="text-sm font-bold text-[var(--color-agro-green-dark)] hover:underline">
                                {{ $planting->plot->name }}
                            </a>
                            <span class="text-xs text-gray-500">
                                {{ $planting->plot->municipality?->name ?? '' }}
                            </span>
                        </div>
                    </x-table-cell>

                    <x-table-cell>
                        @if($planting->grapeVariety)
                            <div class="flex flex-col">
                                <span class="text-sm font-medium text-gray-900">
                                    {{ $planting->grapeVariety->name }}
                                    @if($planting->grapeVariety->code)
                                        ({{ $planting->grapeVariety->code }})
                                    @endif
                                </span>
                                @php
                                    $colorMap = [
                                        'red' => ['Tinto', 'bg-red-100 text-red-800'],
                                        'white' => ['Blanco', 'bg-yellow-100 text-yellow-800'],
                                        'rose' => ['Rosado', 'bg-pink-100 text-pink-800'],
                                    ];
                                    $colorInfo = $planting->grapeVariety->color ? ($colorMap[$planting->grapeVariety->color] ?? null) : null;
                                @endphp
                                @if($colorInfo)
                                    <span class="inline-flex mt-1 px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $colorInfo[1] }}">
                                        {{ $colorInfo[0] }}
                                    </span>
                                @endif
                            </div>
                        @else
                            <span class="text-sm text-gray-500">Sin variedad</span>
                        @endif
                    </x-table-cell>

                    <x-table-cell>
                        <span class="text-sm font-medium text-gray-900">
                            {{ number_format($planting->area_planted, 3) }} ha
                        </span>
                    </x-table-cell>

                    <x-table-cell>
                        <div class="flex flex-col">
                            <span class="text-sm text-gray-700">
                                {{ $planting->planting_year ?? '—' }}
                            </span>
                            @if($planting->planting_date)
                                <span class="text-xs text-gray-500">
                                    {{ $planting->planting_date->format('d/m/Y') }}
                                </span>
                            @endif
                        </div>
                    </x-table-cell>

                    <x-table-cell>
                        @php
                            $statusColors = [
                                'active' => 'bg-green-100 text-green-800',
                                'removed' => 'bg-red-100 text-red-800',
                                'experimental' => 'bg-purple-100 text-purple-800',
                                'replanting' => 'bg-yellow-100 text-yellow-800',
                            ];
                            $label = [
                                'active' => 'Activa',
                                'removed' => 'Arrancada',
                                'experimental' => 'Experimental',
                                'replanting' => 'Replantación',
                            ][$planting->status] ?? $planting->status;
                        @endphp
                        <div class="flex flex-col gap-1">
                            <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold {{ $statusColors[$planting->status] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ $label }}
                            </span>
                            @if($planting->irrigated)
                                <span class="inline-flex items-center gap-1 text-[11px] text-[var(--color-agro-blue)]">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2.69l5.66 5.66a6 6 0 11-11.32 0L12 2.69z" />
                                    </svg>
                                    Con riego
                                </span>
                            @endif
                        </div>
                    </x-table-cell>

                    <x-table-cell>
                        <span class="text-sm text-gray-700">
                            {{ $planting->plot->viticulturist?->name ?? 'Sin asignar' }}
                        </span>
                    </x-table-cell>

                    <x-table-actions align="right">
                        <x-action-button variant="view" href="{{ route('plots.show', $planting->plot) }}" />
                        @can('update', $planting->plot)
                            <x-action-button variant="edit" href="{{ route('plots.plantings.edit', $planting) }}" />
                            <button 
                                wire:click="toggleActive({{ $planting->id }})"
                                wire:loading.attr="disabled"
                                wire:target="toggleActive({{ $planting->id }})"
                                class="p-2 rounded-lg transition-all duration-200 group/btn {{ $planting->active ? 'text-orange-600 hover:bg-orange-50' : 'text-green-600 hover:bg-green-50' }} disabled:opacity-50 disabled:cursor-not-allowed"
                                title="{{ $planting->active ? 'Desactivar plantación' : 'Activar plantación' }}"
                            >
                                <span wire:loading.remove wire:target="toggleActive({{ $planting->id }})">
                                    @if($planting->active)
                                        <svg class="w-5 h-5 group-hover/btn:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    @else
                                        <svg class="w-5 h-5 group-hover/btn:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    @endif
                                </span>
                                <span wire:loading wire:target="toggleActive({{ $planting->id }})" class="inline-block">
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
                {{ $plantings->links() }}
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
                        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6 border border-blue-200">
                            <p class="text-sm font-medium text-blue-700">Total Plantaciones</p>
                            <p class="text-3xl font-bold text-blue-900 mt-1">{{ $stats['total'] }}</p>
                            <p class="text-xs text-blue-600 mt-2">Todas las plantaciones</p>
                        </div>
                        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6 border border-green-200">
                            <p class="text-sm font-medium text-green-700">Superficie Total</p>
                            <p class="text-3xl font-bold text-green-900 mt-1">{{ number_format($advancedStats['totalSurface'] ?? 0, 2) }} ha</p>
                            <p class="text-xs text-green-600 mt-2">Hectáreas plantadas</p>
                        </div>
                        <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-6 border border-purple-200">
                            <p class="text-sm font-medium text-purple-700">Plantaciones Activas</p>
                            <p class="text-3xl font-bold text-purple-900 mt-1">{{ $stats['active'] }}</p>
                            <p class="text-xs text-purple-600 mt-2">De {{ $stats['total'] }} totales</p>
                        </div>
                        <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl p-6 border border-orange-200">
                            <p class="text-sm font-medium text-orange-700">Con Riego</p>
                            <p class="text-3xl font-bold text-orange-900 mt-1">{{ $advancedStats['irrigated'] ?? 0 }}</p>
                            <p class="text-xs text-orange-600 mt-2">Plantaciones irrigadas</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        {{-- Distribución por Estado --}}
                        <div class="bg-white rounded-xl p-6 border border-gray-200">
                            <h3 class="text-lg font-bold text-gray-900 mb-4">📊 Distribución por Estado</h3>
                            <div class="space-y-4">
                                @php
                                    $statusLabels = [
                                        'active' => 'Activa',
                                        'removed' => 'Arrancada',
                                        'experimental' => 'Experimental',
                                        'replanting' => 'Replantación',
                                    ];
                                    $total = ($advancedStats['statusStats'] ?? collect())->sum('count');
                                @endphp
                                @forelse(($advancedStats['statusStats'] ?? []) as $status => $data)
                                    @php
                                        $percentage = $total > 0 ? ($data['count'] / $total) * 100 : 0;
                                    @endphp
                                    <div>
                                        <div class="flex justify-between mb-2">
                                            <span class="text-sm font-medium text-gray-700">{{ $statusLabels[$status] ?? ucfirst($status) }}</span>
                                            <span class="text-sm font-bold text-gray-900">{{ $data['count'] }} ({{ number_format($percentage, 1) }}%)</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-3">
                                            <div class="bg-[var(--color-agro-green)] h-3 rounded-full" style="width: {{ $percentage }}%"></div>
                                        </div>
                                        <div class="flex gap-4 mt-1 text-xs text-gray-500">
                                            <span>Superficie: {{ number_format($data['surface'], 2) }} ha</span>
                                            <span>Activas: {{ $data['active'] }}</span>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-gray-500 text-center py-4">No hay datos de estados</p>
                                @endforelse
                            </div>
                        </div>

                        {{-- Top 10 Variedades --}}
                        <div class="bg-white rounded-xl p-6 border border-gray-200">
                            <h3 class="text-lg font-bold text-gray-900 mb-4">🍇 Top 10 Variedades</h3>
                            <div class="space-y-3">
                                @forelse(($advancedStats['varietyStats'] ?? []) as $variety)
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                        <div>
                                            <p class="font-semibold text-gray-900">{{ $variety['name'] }}</p>
                                            @if($variety['code'])
                                                <p class="text-xs text-gray-500">{{ $variety['code'] }}</p>
                                            @endif
                                        </div>
                                        <div class="text-right">
                                            <p class="font-bold text-[var(--color-agro-green-dark)]">{{ number_format($variety['surface'], 2) }} ha</p>
                                            <p class="text-xs text-gray-500">{{ $variety['count'] }} plantaciones</p>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-gray-500 text-center py-4">No hay datos de variedades</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    {{-- Riego y Autorización --}}
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="bg-white rounded-xl p-6 border border-gray-200">
                            <h3 class="text-lg font-bold text-gray-900 mb-4">💧 Riego</h3>
                            <div class="space-y-4">
                                <div>
                                    <div class="flex justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-700">Con Riego</span>
                                        <span class="text-sm font-bold text-gray-900">{{ $advancedStats['irrigated'] ?? 0 }}</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-3">
                                        @php
                                            $irrigatedPct = $stats['total'] > 0 ? (($advancedStats['irrigated'] ?? 0) / $stats['total']) * 100 : 0;
                                        @endphp
                                        <div class="bg-blue-500 h-3 rounded-full" style="width: {{ $irrigatedPct }}%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-700">Sin Riego</span>
                                        <span class="text-sm font-bold text-gray-900">{{ $advancedStats['nonIrrigated'] ?? 0 }}</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-3">
                                        @php
                                            $nonIrrigatedPct = $stats['total'] > 0 ? (($advancedStats['nonIrrigated'] ?? 0) / $stats['total']) * 100 : 0;
                                        @endphp
                                        <div class="bg-gray-500 h-3 rounded-full" style="width: {{ $nonIrrigatedPct }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl p-6 border border-gray-200">
                            <h3 class="text-lg font-bold text-gray-900 mb-4">📄 Autorización</h3>
                            <div class="space-y-4">
                                <div>
                                    <div class="flex justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-700">Con Autorización</span>
                                        <span class="text-sm font-bold text-gray-900">{{ $advancedStats['withAuthorization'] ?? 0 }}</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-3">
                                        @php
                                            $withAuthPct = $stats['total'] > 0 ? (($advancedStats['withAuthorization'] ?? 0) / $stats['total']) * 100 : 0;
                                        @endphp
                                        <div class="bg-green-500 h-3 rounded-full" style="width: {{ $withAuthPct }}%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-700">Sin Autorización</span>
                                        <span class="text-sm font-bold text-gray-900">{{ $advancedStats['withoutAuthorization'] ?? 0 }}</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-3">
                                        @php
                                            $withoutAuthPct = $stats['total'] > 0 ? (($advancedStats['withoutAuthorization'] ?? 0) / $stats['total']) * 100 : 0;
                                        @endphp
                                        <div class="bg-orange-500 h-3 rounded-full" style="width: {{ $withoutAuthPct }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Distribución por Año de Plantación --}}
                    <div class="bg-white rounded-xl p-6 border border-gray-200">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">📅 Distribución por Año de Plantación</h3>
                        <div class="space-y-3">
                            @forelse(($advancedStats['yearStats'] ?? []) as $yearData)
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                    <span class="font-semibold text-gray-900">{{ $yearData['year'] }}</span>
                                    <div class="text-right">
                                        <span class="font-bold text-[var(--color-agro-green-dark)]">{{ number_format($yearData['surface'], 2) }} ha</span>
                                        <span class="text-xs text-gray-500 ml-2">({{ $yearData['count'] }} plantaciones)</span>
                                    </div>
                                </div>
                            @empty
                                <p class="text-gray-500 text-center py-4">No hay datos de años</p>
                            @endforelse
                        </div>
                    </div>

                    {{-- Nuevas Plantaciones por Mes --}}
                    <div class="bg-white rounded-xl p-6 border border-gray-200">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">📈 Nuevas Plantaciones (Últimos 12 meses)</h3>
                        <div class="h-64 flex items-end justify-between gap-2">
                            @foreach(($advancedStats['newPlantingsByMonth'] ?? []) as $month)
                                <div class="flex-1 flex flex-col items-center">
                                    <div class="w-full bg-[var(--color-agro-green)] rounded-t-lg transition-all hover:bg-[var(--color-agro-green-dark)]" 
                                        style="height: {{ $month['count'] > 0 ? ($month['count'] / max(collect($advancedStats['newPlantingsByMonth'] ?? [])->pluck('count')->max(), 1)) * 100 : 5 }}%"
                                        title="{{ $month['count'] }} plantaciones"></div>
                                    <span class="text-xs text-gray-600 mt-2">{{ $month['month'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>


