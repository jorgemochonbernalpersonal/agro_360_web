<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Facturar Cosechas"
        description="Gestiona la facturacion de tus cosechas y analisis"
    />

    {{-- Tabs Navigation --}}
    <x-agro.card :padding="false">
        <x-agro.tabs
            :tabs="['list' => 'Cosechas Pendientes', 'statistics' => 'Estadisticas']"
            :active="$currentTab"
            wire-method="switchTab"
        />

        {{-- Tab Content --}}
        <div class="p-6">
            {{-- LIST TAB --}}
            @if($currentTab === 'list')
                <div class="space-y-6">
                    {{-- Filtros --}}
                    <x-agro.filter-bar>
                        <x-agro.filter-input wire:model.live="search" placeholder="Buscar por parcela o variedad..." />
                    </x-agro.filter-bar>

                    {{-- Tabla --}}
                    <x-agro.data-table
                        :headers="['Parcela', 'Variedad', 'Fecha', 'Peso (kg)', 'Valor', 'Acciones']"
                        empty-message="No hay cosechas disponibles"
                        empty-description="No hay cosechas pendientes de facturar"
                        empty-icon="beaker"
                    >
                        @if($harvests->count() > 0)
                            @foreach($harvests as $harvest)
                                <x-agro.table-row>
                                    <x-agro.table-cell>
                                        <span class="text-sm font-medium text-zinc-900">{{ $harvest->activity->plot->name ?? 'N/A' }}</span>
                                    </x-agro.table-cell>
                                    <x-agro.table-cell>
                                        <span class="text-sm text-zinc-500">{{ $harvest->plotPlanting->grapeVariety->name ?? 'N/A' }}</span>
                                    </x-agro.table-cell>
                                    <x-agro.table-cell>
                                        <span class="text-sm text-zinc-500">{{ $harvest->harvest_start_date->format('d/m/Y') }}</span>
                                    </x-agro.table-cell>
                                    <x-agro.table-cell>
                                        <span class="text-sm text-zinc-500">{{ number_format($harvest->total_weight, 2) }}</span>
                                    </x-agro.table-cell>
                                    <x-agro.table-cell>
                                        <span class="text-sm font-semibold text-zinc-900">{{ $harvest->total_value ? number_format($harvest->total_value, 2) . ' €' : 'N/A' }}</span>
                                    </x-agro.table-cell>
                                    <x-agro.table-cell align="right">
                                        <flux:button
                                            href="{{ route('viticulturist.invoices.create', ['harvest_id' => $harvest->id]) }}"
                                            variant="primary"
                                            size="sm"
                                            icon="document-text"
                                            wire:navigate
                                        >
                                            Facturar
                                        </flux:button>
                                    </x-agro.table-cell>
                                </x-agro.table-row>
                            @endforeach
                            <x-slot name="pagination">
                                {{ $harvests->links() }}
                            </x-slot>
                        @else
                            <x-slot name="emptyAction">
                                <p class="text-sm text-zinc-400">Registra cosechas en el Cuaderno Digital para poder facturarlas</p>
                            </x-slot>
                        @endif
                    </x-agro.data-table>
                </div>
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
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                        <x-agro.stat-card label="Total Cosechado" :value="number_format($advancedStats['totalHarvested'] ?? 0, 0) . ' kg'" :description="'En ' . $yearFilter"                                                              icon="beaker"       color="blue"   />
                        <x-agro.stat-card label="Facturado"       :value="number_format($advancedStats['totalInvoiced'] ?? 0, 0) . ' kg'"  :description="number_format($advancedStats['invoicedPercentage'] ?? 0, 1) . '% del total'" icon="check-circle" color="agro"   />
                        <x-agro.stat-card label="Pendiente"       :value="number_format($advancedStats['pendingToInvoice'] ?? 0, 0) . ' kg'" description="Sin facturar"                                                               icon="clock"        color="orange" />
                        <x-agro.stat-card label="Precio Medio"    :value="number_format($advancedStats['avgPricePerKg'] ?? 0, 2) . ' €'"   description="Por kilogramo"                                                                icon="banknotes"    color="purple" />
                        <x-agro.stat-card label="Ingresos Cosechas" :value="number_format($advancedStats['harvestRevenue'] ?? 0, 2) . ' €'" :description="'Facturación total en ' . $yearFilter"                                     icon="currency-euro" color="blue"  />
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        {{-- Por Variedad --}}
                        <x-agro.card>
                            <x-slot:header>
                                <div class="flex items-center gap-2">
                                    <div class="p-1.5 rounded-lg bg-agro-50">
                                        <flux:icon icon="beaker" class="size-4 text-agro-600" />
                                    </div>
                                    <span class="font-semibold text-zinc-900 text-sm">Análisis por Variedad</span>
                                </div>
                            </x-slot:header>
                            <div class="space-y-3">
                                @forelse(($advancedStats['byVariety'] ?? []) as $variety)
                                    <div class="p-4 bg-zinc-50 rounded-lg">
                                        <div class="flex justify-between mb-2">
                                            <span class="font-semibold text-zinc-900">{{ $variety['variety'] }}</span>
                                            <span class="text-sm text-zinc-600">{{ number_format($variety['total'], 0) }} kg</span>
                                        </div>
                                        <div class="grid grid-cols-3 gap-2 text-xs">
                                            <div>
                                                <p class="text-zinc-500">Facturado</p>
                                                <p class="font-bold text-green-700">{{ number_format($variety['invoiced'], 0) }} kg</p>
                                            </div>
                                            <div>
                                                <p class="text-zinc-500">Pendiente</p>
                                                <p class="font-bold text-orange-700">{{ number_format($variety['pending'], 0) }} kg</p>
                                            </div>
                                            <div>
                                                <p class="text-zinc-500">% Fac.</p>
                                                <p class="font-bold text-blue-700">{{ number_format($variety['percentage'], 1) }}%</p>
                                            </div>
                                        </div>
                                        <div class="mt-2 w-full bg-zinc-200 rounded-full h-2">
                                            <div class="bg-agro-500 h-2 rounded-full" style="width: {{ $variety['percentage'] }}%"></div>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-zinc-500 text-center py-4">No hay datos</p>
                                @endforelse
                            </div>
                        </x-agro.card>

                        {{-- Top Parcelas --}}
                        <x-agro.card>
                            <x-slot:header>
                                <div class="flex items-center gap-2">
                                    <div class="p-1.5 rounded-lg bg-purple-50">
                                        <flux:icon icon="trophy" class="size-4 text-purple-600" />
                                    </div>
                                    <span class="font-semibold text-zinc-900 text-sm">Top Parcelas por Rendimiento</span>
                                </div>
                            </x-slot:header>
                            <div class="space-y-3">
                                @forelse(($advancedStats['topPlots'] ?? []) as $index => $plot)
                                    <div class="flex items-center justify-between p-3 bg-zinc-50 rounded-lg hover:bg-zinc-100 transition-colors">
                                        <div class="flex items-center gap-3">
                                            <span class="flex-shrink-0 w-8 h-8 rounded-full bg-agro-500 text-white flex items-center justify-center font-bold text-sm">
                                                {{ $index + 1 }}
                                            </span>
                                            <div>
                                                <p class="font-semibold text-zinc-900">{{ $plot['plot'] }}</p>
                                                <p class="text-xs text-zinc-500">{{ $plot['harvests_count'] }} cosechas</p>
                                            </div>
                                        </div>
                                        <span class="font-bold text-agro-700">{{ number_format($plot['total_weight'], 0) }} kg</span>
                                    </div>
                                @empty
                                    <p class="text-zinc-500 text-center py-4">No hay datos</p>
                                @endforelse
                            </div>
                        </x-agro.card>
                    </div>

                    {{-- Cosechas Mensuales --}}
                    <x-agro.card>
                        <x-slot:header>
                            <div class="flex items-center gap-2">
                                <div class="p-1.5 rounded-lg bg-agro-50">
                                    <flux:icon icon="arrow-trending-up" class="size-4 text-agro-600" />
                                </div>
                                <span class="font-semibold text-zinc-900 text-sm">Cosechas Mensuales (Últimos 12 meses)</span>
                            </div>
                        </x-slot:header>
                        <div class="h-64 flex items-end justify-between gap-2">
                            @foreach(($advancedStats['harvestsByMonth'] ?? []) as $month)
                                @php
                                    $maxWeight = collect($advancedStats['harvestsByMonth'] ?? [])->pluck('weight')->max();
                                    $height = $maxWeight > 0 ? ($month['weight'] / $maxWeight) * 100 : 5;
                                @endphp
                                <div class="flex-1 flex flex-col items-center">
                                    <div class="w-full bg-agro-500 rounded-t-lg transition-all hover:bg-agro-700"
                                        style="height: {{ $height }}%"
                                        title="{{ number_format($month['weight'], 0) }} kg"></div>
                                    <span class="text-xs text-zinc-600 mt-2">{{ $month['month'] }}</span>
                                    <span class="text-xs text-zinc-400">{{ number_format($month['weight'], 0) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </x-agro.card>
                </div>
            @endif
        </div>
    </x-agro.card>
</div>
