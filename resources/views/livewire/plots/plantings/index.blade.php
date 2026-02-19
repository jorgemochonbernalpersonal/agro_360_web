<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Plantaciones"
        description="Listado global de plantaciones de variedades en tus parcelas"
    >
        <x-slot:actions>
            <flux:button href="{{ route('plots.index') }}" variant="outline" icon="arrow-left">
                Ver parcelas
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    <!-- Tabs Navigation -->
    <x-agro.card :padding="false">
        <div class="border-b border-zinc-200">
            <nav class="flex -mb-px">
                <button
                    wire:click="switchTab('active')"
                    @class([
                        'group inline-flex items-center gap-2 px-6 py-4 border-b-2 font-medium text-sm transition-colors',
                        'border-agro-500 text-agro-700' => $currentTab === 'active',
                        'border-transparent text-zinc-500 hover:text-zinc-700 hover:border-zinc-300' => $currentTab !== 'active',
                    ])
                >
                    <flux:icon icon="check-circle" class="size-5" />
                    <span>Activas</span>
                    @if($stats['active'] > 0)
                        <span @class([
                            'px-2 py-0.5 text-xs font-semibold rounded-full',
                            'bg-agro-500 text-white' => $currentTab === 'active',
                            'bg-zinc-200 text-zinc-700' => $currentTab !== 'active',
                        ])>
                            {{ $stats['active'] }}
                        </span>
                    @endif
                </button>

                <button
                    wire:click="switchTab('inactive')"
                    @class([
                        'group inline-flex items-center gap-2 px-6 py-4 border-b-2 font-medium text-sm transition-colors',
                        'border-agro-500 text-agro-700' => $currentTab === 'inactive',
                        'border-transparent text-zinc-500 hover:text-zinc-700 hover:border-zinc-300' => $currentTab !== 'inactive',
                    ])
                >
                    <flux:icon icon="x-circle" class="size-5" />
                    <span>Inactivas</span>
                    @if($stats['inactive'] > 0)
                        <span @class([
                            'px-2 py-0.5 text-xs font-semibold rounded-full',
                            'bg-agro-500 text-white' => $currentTab === 'inactive',
                            'bg-zinc-200 text-zinc-700' => $currentTab !== 'inactive',
                        ])>
                            {{ $stats['inactive'] }}
                        </span>
                    @endif
                </button>

                <button
                    wire:click="switchTab('statistics')"
                    @class([
                        'group inline-flex items-center gap-2 px-6 py-4 border-b-2 font-medium text-sm transition-colors',
                        'border-agro-500 text-agro-700' => $currentTab === 'statistics',
                        'border-transparent text-zinc-500 hover:text-zinc-700 hover:border-zinc-300' => $currentTab !== 'statistics',
                    ])
                >
                    <flux:icon icon="chart-bar" class="size-5" />
                    <span>Estadisticas</span>
                </button>
            </nav>
        </div>

        <div class="p-6">
            {{-- ACTIVE/INACTIVE TABS --}}
            @if($currentTab === 'active' || $currentTab === 'inactive')
    <x-agro.filter-bar>
        <x-agro.filter-input
            wire:model.live="search"
            placeholder="Buscar por parcela o variedad..."
        />
        <x-agro.filter-select wire:model.live="status">
            <option value="">Todos los estados</option>
            <option value="active">Activa</option>
            <option value="removed">Arrancada</option>
            <option value="experimental">Experimental</option>
            <option value="replanting">Replantacion</option>
        </x-agro.filter-select>
        <x-agro.filter-select wire:model.live="year">
            <option value="">Todos los anos</option>
            @foreach ($years as $yearOption)
                <option value="{{ $yearOption }}">{{ $yearOption }}</option>
            @endforeach
        </x-agro.filter-select>
        @if($search || $status !== '' || $year !== '')
            <flux:button wire:click="clearFilters" variant="ghost" size="sm">
                Limpiar Filtros
            </flux:button>
        @endif
    </x-agro.filter-bar>

    @php
        $headers = ['Nombre', 'Parcela', 'Variedad', 'Superficie (ha)', 'Año', 'Estado', 'Viticultor', 'Acciones'];
    @endphp

    <x-agro.data-table :headers="$headers" empty-message="No hay plantaciones registradas" empty-description="Comienza creando una plantacion sobre una parcela">
        @if($plantings->count() > 0)
            @foreach($plantings as $planting)
                <x-agro.table-row wire:key="planting-{{ $planting->id }}">
                    <x-agro.table-cell>
                        @if($planting->name)
                            <span class="text-sm font-semibold text-purple-700">
                                {{ $planting->name }}
                            </span>
                        @else
                            <span class="text-sm text-zinc-400 italic">--</span>
                        @endif
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <div class="flex flex-col">
                            <a href="{{ route('plots.show', $planting->plot) }}"
                               class="text-sm font-bold text-agro-700 hover:underline">
                                {{ $planting->plot->name }}
                            </a>
                            <span class="text-xs text-zinc-500">
                                {{ $planting->plot->municipality?->name ?? '' }}
                            </span>
                        </div>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        @if($planting->grapeVariety)
                            <div class="flex flex-col">
                                <span class="text-sm font-medium text-zinc-900">
                                    {{ $planting->grapeVariety->name }}
                                    @if($planting->grapeVariety->code)
                                        ({{ $planting->grapeVariety->code }})
                                    @endif
                                </span>
                                @php
                                    $colorMap = [
                                        'red'   => ['Tinto',  'red'],
                                        'white' => ['Blanco', 'yellow'],
                                        'rose'  => ['Rosado', 'pink'],
                                    ];
                                    $colorInfo = $planting->grapeVariety->color ? ($colorMap[$planting->grapeVariety->color] ?? null) : null;
                                @endphp
                                @if($colorInfo)
                                    <flux:badge :color="$colorInfo[1]" size="sm" class="mt-1">{{ $colorInfo[0] }}</flux:badge>
                                @endif
                            </div>
                        @else
                            <span class="text-sm text-zinc-500">Sin variedad</span>
                        @endif
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <span class="text-sm font-medium text-zinc-900">
                            {{ number_format($planting->area_planted, 3) }} ha
                        </span>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <div class="flex flex-col">
                            <span class="text-sm text-zinc-700">
                                {{ $planting->planting_year ?? '--' }}
                            </span>
                            @if($planting->planting_date)
                                <span class="text-xs text-zinc-500">
                                    {{ $planting->planting_date->format('d/m/Y') }}
                                </span>
                            @endif
                        </div>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        @php
                            $statusColors = [
                                'active'       => 'green',
                                'removed'      => 'red',
                                'experimental' => 'purple',
                                'replanting'   => 'yellow',
                            ];
                            $label = [
                                'active'       => 'Activa',
                                'removed'      => 'Arrancada',
                                'experimental' => 'Experimental',
                                'replanting'   => 'Replantacion',
                            ][$planting->status] ?? $planting->status;
                        @endphp
                        <div class="flex flex-col gap-1">
                            <flux:badge :color="$statusColors[$planting->status] ?? null" size="sm">{{ $label }}</flux:badge>
                            @if($planting->irrigated)
                                <span class="inline-flex items-center gap-1 text-[11px] text-blue-600">
                                    <flux:icon icon="cloud" class="size-3" />
                                    Con riego
                                </span>
                            @endif
                        </div>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <span class="text-sm text-zinc-700">
                            {{ $planting->plot->viticulturist?->name ?? 'Sin asignar' }}
                        </span>
                    </x-agro.table-cell>

                    <x-agro.table-cell align="right">
                        <div class="flex items-center justify-end gap-2">
                            <x-agro.action-button variant="view" href="{{ route('plots.show', $planting->plot) }}" />
                            @can('update', $planting->plot)
                                <x-agro.action-button variant="edit" href="{{ route('plots.plantings.edit', $planting) }}" />
                                <button
                                    wire:click="toggleActive({{ $planting->id }})"
                                    wire:loading.attr="disabled"
                                    wire:target="toggleActive({{ $planting->id }})"
                                    class="p-2 rounded-lg transition-all duration-200 group/btn {{ $planting->active ? 'text-orange-600 hover:bg-orange-50' : 'text-green-600 hover:bg-green-50' }} disabled:opacity-50 disabled:cursor-not-allowed"
                                    title="{{ $planting->active ? 'Desactivar plantacion' : 'Activar plantacion' }}"
                                >
                                    <span wire:loading.remove wire:target="toggleActive({{ $planting->id }})">
                                        @if($planting->active)
                                            <flux:icon icon="x-circle" class="size-5 group-hover/btn:scale-110 transition-transform" />
                                        @else
                                            <flux:icon icon="check-circle" class="size-5 group-hover/btn:scale-110 transition-transform" />
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
                        </div>
                    </x-agro.table-cell>
                </x-agro.table-row>
            @endforeach

            <x-slot name="pagination">
                {{ $plantings->links() }}
            </x-slot>
        @endif
    </x-agro.data-table>
            @endif

            {{-- STATISTICS TAB --}}
            @if($currentTab === 'statistics')
                <div class="space-y-6">
                    {{-- Filtro de Ano --}}
                    <div class="flex justify-end">
                        <flux:select wire:model.live="yearFilter" class="w-auto">
                            @for($year = now()->year; $year >= now()->year - 5; $year--)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endfor
                        </flux:select>
                    </div>

                    {{-- KPIs --}}
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <x-agro.stat-card label="Total Plantaciones"  :value="$stats['total']"                                                    description="Todas las plantaciones"                       icon="clipboard-document-list" color="blue"   />
                        <x-agro.stat-card label="Superficie Total"    :value="number_format($advancedStats['totalSurface'] ?? 0, 2) . ' ha'"       description="Hectáreas plantadas"                          icon="map"                     color="agro"   />
                        <x-agro.stat-card label="Plantaciones Activas" :value="$stats['active']"                                                   :description="'De ' . $stats['total'] . ' totales'"        icon="check-circle"            color="purple" />
                        <x-agro.stat-card label="Con Riego"           :value="$advancedStats['irrigated'] ?? 0"                                    description="Plantaciones irrigadas"                       icon="cloud"                   color="orange" />
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        {{-- Distribucion por Estado --}}
                        <x-agro.card>
                            <x-slot:header>
                                <div class="flex items-center gap-2">
                                    <div class="p-1.5 rounded-lg bg-agro-50">
                                        <flux:icon icon="chart-pie" class="size-4 text-agro-600" />
                                    </div>
                                    <span class="font-semibold text-zinc-900 text-sm">Distribución por Estado</span>
                                </div>
                            </x-slot:header>
                            <div class="space-y-4">
                                @php
                                    $statusLabels = [
                                        'active' => 'Activa',
                                        'removed' => 'Arrancada',
                                        'experimental' => 'Experimental',
                                        'replanting' => 'Replantacion',
                                    ];
                                    $total = ($advancedStats['statusStats'] ?? collect())->sum('count');
                                @endphp
                                @forelse(($advancedStats['statusStats'] ?? []) as $status => $data)
                                    @php
                                        $percentage = $total > 0 ? ($data['count'] / $total) * 100 : 0;
                                    @endphp
                                    <div>
                                        <div class="flex justify-between mb-2">
                                            <span class="text-sm font-medium text-zinc-700">{{ $statusLabels[$status] ?? ucfirst($status) }}</span>
                                            <span class="text-sm font-bold text-zinc-900">{{ $data['count'] }} ({{ number_format($percentage, 1) }}%)</span>
                                        </div>
                                        <div class="w-full bg-zinc-200 rounded-full h-3">
                                            <div class="bg-agro-500 h-3 rounded-full" style="width: {{ $percentage }}%"></div>
                                        </div>
                                        <div class="flex gap-4 mt-1 text-xs text-zinc-500">
                                            <span>Superficie: {{ number_format($data['surface'], 2) }} ha</span>
                                            <span>Activas: {{ $data['active'] }}</span>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-zinc-500 text-center py-4">No hay datos de estados</p>
                                @endforelse
                            </div>
                        </x-agro.card>

                        {{-- Top 10 Variedades --}}
                        <x-agro.card>
                            <x-slot:header>
                                <div class="flex items-center gap-2">
                                    <div class="p-1.5 rounded-lg bg-purple-50">
                                        <flux:icon icon="trophy" class="size-4 text-purple-600" />
                                    </div>
                                    <span class="font-semibold text-zinc-900 text-sm">Top 10 Variedades</span>
                                </div>
                            </x-slot:header>
                            <div class="space-y-3">
                                @forelse(($advancedStats['varietyStats'] ?? []) as $variety)
                                    <div class="flex items-center justify-between p-3 bg-zinc-50 rounded-lg hover:bg-zinc-100 transition-colors">
                                        <div>
                                            <p class="font-semibold text-zinc-900">{{ $variety['name'] }}</p>
                                            @if($variety['code'])
                                                <p class="text-xs text-zinc-500">{{ $variety['code'] }}</p>
                                            @endif
                                        </div>
                                        <div class="text-right">
                                            <p class="font-bold text-agro-700">{{ number_format($variety['surface'], 2) }} ha</p>
                                            <p class="text-xs text-zinc-500">{{ $variety['count'] }} plantaciones</p>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-zinc-500 text-center py-4">No hay datos de variedades</p>
                                @endforelse
                            </div>
                        </x-agro.card>
                    </div>

                    {{-- Riego y Autorizacion --}}
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <x-agro.card>
                            <x-slot:header>
                                <div class="flex items-center gap-2">
                                    <div class="p-1.5 rounded-lg bg-blue-50">
                                        <flux:icon icon="cloud" class="size-4 text-blue-600" />
                                    </div>
                                    <span class="font-semibold text-zinc-900 text-sm">Riego</span>
                                </div>
                            </x-slot:header>
                            <div class="space-y-4">
                                <div>
                                    <div class="flex justify-between mb-2">
                                        <span class="text-sm font-medium text-zinc-700">Con Riego</span>
                                        <span class="text-sm font-bold text-zinc-900">{{ $advancedStats['irrigated'] ?? 0 }}</span>
                                    </div>
                                    <div class="w-full bg-zinc-200 rounded-full h-3">
                                        @php
                                            $irrigatedPct = $stats['total'] > 0 ? (($advancedStats['irrigated'] ?? 0) / $stats['total']) * 100 : 0;
                                        @endphp
                                        <div class="bg-blue-500 h-3 rounded-full" style="width: {{ $irrigatedPct }}%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between mb-2">
                                        <span class="text-sm font-medium text-zinc-700">Sin Riego</span>
                                        <span class="text-sm font-bold text-zinc-900">{{ $advancedStats['nonIrrigated'] ?? 0 }}</span>
                                    </div>
                                    <div class="w-full bg-zinc-200 rounded-full h-3">
                                        @php
                                            $nonIrrigatedPct = $stats['total'] > 0 ? (($advancedStats['nonIrrigated'] ?? 0) / $stats['total']) * 100 : 0;
                                        @endphp
                                        <div class="bg-zinc-500 h-3 rounded-full" style="width: {{ $nonIrrigatedPct }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </x-agro.card>

                        <x-agro.card>
                            <x-slot:header>
                                <div class="flex items-center gap-2">
                                    <div class="p-1.5 rounded-lg bg-agro-50">
                                        <flux:icon icon="document-check" class="size-4 text-agro-600" />
                                    </div>
                                    <span class="font-semibold text-zinc-900 text-sm">Autorización</span>
                                </div>
                            </x-slot:header>
                            <div class="space-y-4">
                                <div>
                                    <div class="flex justify-between mb-2">
                                        <span class="text-sm font-medium text-zinc-700">Con Autorizacion</span>
                                        <span class="text-sm font-bold text-zinc-900">{{ $advancedStats['withAuthorization'] ?? 0 }}</span>
                                    </div>
                                    <div class="w-full bg-zinc-200 rounded-full h-3">
                                        @php
                                            $withAuthPct = $stats['total'] > 0 ? (($advancedStats['withAuthorization'] ?? 0) / $stats['total']) * 100 : 0;
                                        @endphp
                                        <div class="bg-green-500 h-3 rounded-full" style="width: {{ $withAuthPct }}%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between mb-2">
                                        <span class="text-sm font-medium text-zinc-700">Sin Autorizacion</span>
                                        <span class="text-sm font-bold text-zinc-900">{{ $advancedStats['withoutAuthorization'] ?? 0 }}</span>
                                    </div>
                                    <div class="w-full bg-zinc-200 rounded-full h-3">
                                        @php
                                            $withoutAuthPct = $stats['total'] > 0 ? (($advancedStats['withoutAuthorization'] ?? 0) / $stats['total']) * 100 : 0;
                                        @endphp
                                        <div class="bg-orange-500 h-3 rounded-full" style="width: {{ $withoutAuthPct }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </x-agro.card>
                    </div>

                    {{-- Distribucion por Ano de Plantacion --}}
                    <x-agro.card>
                        <x-slot:header>
                            <div class="flex items-center gap-2">
                                <div class="p-1.5 rounded-lg bg-blue-50">
                                    <flux:icon icon="calendar" class="size-4 text-blue-600" />
                                </div>
                                <span class="font-semibold text-zinc-900 text-sm">Distribución por Año de Plantación</span>
                            </div>
                        </x-slot:header>
                        <div class="space-y-3">
                            @forelse(($advancedStats['yearStats'] ?? []) as $yearData)
                                <div class="flex items-center justify-between p-3 bg-zinc-50 rounded-lg hover:bg-zinc-100 transition-colors">
                                    <span class="font-semibold text-zinc-900">{{ $yearData['year'] }}</span>
                                    <div class="text-right">
                                        <span class="font-bold text-agro-700">{{ number_format($yearData['surface'], 2) }} ha</span>
                                        <span class="text-xs text-zinc-500 ml-2">({{ $yearData['count'] }} plantaciones)</span>
                                    </div>
                                </div>
                            @empty
                                <p class="text-zinc-500 text-center py-4">No hay datos de anos</p>
                            @endforelse
                        </div>
                    </x-agro.card>

                    {{-- Nuevas Plantaciones por Mes --}}
                    <x-agro.card>
                        <x-slot:header>
                            <div class="flex items-center gap-2">
                                <div class="p-1.5 rounded-lg bg-agro-50">
                                    <flux:icon icon="arrow-trending-up" class="size-4 text-agro-600" />
                                </div>
                                <span class="font-semibold text-zinc-900 text-sm">Nuevas Plantaciones (Últimos 12 meses)</span>
                            </div>
                        </x-slot:header>
                        <div class="h-64 flex items-end justify-between gap-2">
                            @foreach(($advancedStats['newPlantingsByMonth'] ?? []) as $month)
                                <div class="flex-1 flex flex-col items-center">
                                    <div class="w-full bg-agro-500 rounded-t-lg transition-all hover:bg-agro-700"
                                        style="height: {{ $month['count'] > 0 ? ($month['count'] / max(collect($advancedStats['newPlantingsByMonth'] ?? [])->pluck('count')->max(), 1)) * 100 : 5 }}%"
                                        title="{{ $month['count'] }} plantaciones"></div>
                                    <span class="text-xs text-zinc-600 mt-2">{{ $month['month'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </x-agro.card>
                </div>
            @endif
        </div>
    </x-agro.card>
</div>
