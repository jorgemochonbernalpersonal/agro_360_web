<div class="space-y-6 animate-fade-in">
    @php
        $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>';
    @endphp
    <x-page-header
        :icon="$icon"
        title="Facturar Cosechas"
        description="Gestiona la facturación de tus cosechas y análisis"
        icon-color="from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]"
    />

    {{-- Tabs Navigation --}}
    <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
        <div class="border-b border-gray-200">
            <nav class="flex -mb-px">
                <button 
                    wire:click="switchTab('list')"
                    class="group inline-flex items-center gap-2 px-6 py-4 border-b-2 font-medium text-sm transition-colors
                        {{ $currentTab === 'list' ? 'border-[var(--color-agro-green-dark)] text-[var(--color-agro-green-dark)]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Cosechas Pendientes</span>
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

        {{-- Tab Content --}}
        <div class="p-6">
            {{-- LIST TAB --}}
            @if($currentTab === 'list')
                <div class="space-y-6">
                    {{-- Filtros --}}
                    <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-input wire:model.live="search" type="text" placeholder="Buscar por parcela o variedad..." />
                        </div>
                    </div>

                    {{-- Tabla --}}
                    <div class="bg-white rounded-xl overflow-hidden border border-gray-200">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Parcela</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Variedad</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Peso (kg)</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Valor</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($harvests as $harvest)
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $harvest->activity->plot->name ?? 'N/A' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $harvest->plotPlanting->grapeVariety->name ?? 'N/A' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $harvest->harvest_start_date->format('d/m/Y') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($harvest->total_weight, 2) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">{{ $harvest->total_value ? number_format($harvest->total_value, 2) . ' €' : 'N/A' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="{{ route('viticulturist.invoices.create', ['harvest_id' => $harvest->id]) }}" 
                                                   wire:navigate
                                                   class="inline-flex items-center gap-1 px-4 py-2 bg-[var(--color-agro-green)] text-white rounded-lg hover:bg-[var(--color-agro-green-dark)] transition font-medium">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                    </svg>
                                                    Facturar
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-6 py-8 text-center">
                                                <p class="text-gray-500">No hay cosechas disponibles para facturar</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                            {{ $harvests->links() }}
                        </div>
                    </div>
                </div>
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
                            <p class="text-sm font-medium text-blue-700">Total Cosechado</p>
                            <p class="text-3xl font-bold text-blue-900 mt-1">{{ number_format($advancedStats['totalHarvested'] ?? 0, 0) }} kg</p>
                            <p class="text-xs text-blue-600 mt-2">En {{ $yearFilter }}</p>
                        </div>
                        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6 border border-green-200">
                            <p class="text-sm font-medium text-green-700">Facturado</p>
                            <p class="text-3xl font-bold text-green-900 mt-1">{{ number_format($advancedStats['totalInvoiced'] ?? 0, 0) }} kg</p>
                            <p class="text-xs text-green-600 mt-2">{{ number_format($advancedStats['invoicedPercentage'] ?? 0, 1) }}% del total</p>
                        </div>
                        <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl p-6 border border-orange-200">
                            <p class="text-sm font-medium text-orange-700">Pendiente</p>
                            <p class="text-3xl font-bold text-orange-900 mt-1">{{ number_format($advancedStats['pendingToInvoice'] ?? 0, 0) }} kg</p>
                            <p class="text-xs text-orange-600 mt-2">Sin facturar</p>
                        </div>
                        <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-6 border border-purple-200">
                            <p class="text-sm font-medium text-purple-700">Precio Medio</p>
                            <p class="text-3xl font-bold text-purple-900 mt-1">{{ number_format($advancedStats['avgPricePerKg'] ?? 0, 2) }} €</p>
                            <p class="text-xs text-purple-600 mt-2">Por kilogramo</p>
                        </div>
                    </div>

                    {{-- Ingresos --}}
                    <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-xl p-6 border border-indigo-200">
                        <p class="text-sm font-medium text-indigo-700">Ingresos por Cosechas</p>
                        <p class="text-4xl font-bold text-indigo-900 mt-2">{{ number_format($advancedStats['harvestRevenue'] ?? 0, 2) }} €</p>
                        <p class="text-xs text-indigo-600 mt-2">Facturación total en {{ $yearFilter }}</p>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        {{-- Por Variedad --}}
                        <div class="bg-white rounded-xl p-6 border border-gray-200">
                            <h3 class="text-lg font-bold text-gray-900 mb-4">🍇 Análisis por Variedad</h3>
                            <div class="space-y-3">
                                @forelse(($advancedStats['byVariety'] ?? []) as $variety)
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <div class="flex justify-between mb-2">
                                            <span class="font-semibold text-gray-900">{{ $variety['variety'] }}</span>
                                            <span class="text-sm text-gray-600">{{ number_format($variety['total'], 0) }} kg</span>
                                        </div>
                                        <div class="grid grid-cols-3 gap-2 text-xs">
                                            <div>
                                                <p class="text-gray-500">Facturado</p>
                                                <p class="font-bold text-green-700">{{ number_format($variety['invoiced'], 0) }} kg</p>
                                            </div>
                                            <div>
                                                <p class="text-gray-500">Pendiente</p>
                                                <p class="font-bold text-orange-700">{{ number_format($variety['pending'], 0) }} kg</p>
                                            </div>
                                            <div>
                                                <p class="text-gray-500">% Fac.</p>
                                                <p class="font-bold text-blue-700">{{ number_format($variety['percentage'], 1) }}%</p>
                                            </div>
                                        </div>
                                        <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
                                            <div class="bg-[var(--color-agro-green)] h-2 rounded-full" style="width: {{ $variety['percentage'] }}%"></div>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-gray-500 text-center py-4">No hay datos</p>
                                @endforelse
                            </div>
                        </div>

                        {{-- Top Parcelas --}}
                        <div class="bg-white rounded-xl p-6 border border-gray-200">
                            <h3 class="text-lg font-bold text-gray-900 mb-4">🏆 Top Parcelas por Rendimiento</h3>
                            <div class="space-y-3">
                                @forelse(($advancedStats['topPlots'] ?? []) as $index => $plot)
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                        <div class="flex items-center gap-3">
                                            <span class="flex-shrink-0 w-8 h-8 rounded-full bg-[var(--color-agro-green)] text-white flex items-center justify-center font-bold text-sm">
                                                {{ $index + 1 }}
                                            </span>
                                            <div>
                                                <p class="font-semibold text-gray-900">{{ $plot['plot'] }}</p>
                                                <p class="text-xs text-gray-500">{{ $plot['harvests_count'] }} cosechas</p>
                                            </div>
                                        </div>
                                        <span class="font-bold text-[var(--color-agro-green-dark)]">{{ number_format($plot['total_weight'], 0) }} kg</span>
                                    </div>
                                @empty
                                    <p class="text-gray-500 text-center py-4">No hay datos</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    {{-- Cosechas Mensuales --}}
                    <div class="bg-white rounded-xl p-6 border border-gray-200">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">📈 Cosechas Mensuales (Últimos 12 meses)</h3>
                        <div class="h-64 flex items-end justify-between gap-2">
                            @foreach(($advancedStats['harvestsByMonth'] ?? []) as $month)
                                @php
                                    $maxWeight = collect($advancedStats['harvestsByMonth'] ?? [])->pluck('weight')->max();
                                    $height = $maxWeight > 0 ? ($month['weight'] / $maxWeight) * 100 : 5;
                                @endphp
                                <div class="flex-1 flex flex-col items-center">
                                    <div class="w-full bg-[var(--color-agro-green)] rounded-t-lg transition-all hover:bg-[var(--color-agro-green-dark)]" 
                                        style="height: {{ $height }}%"
                                        title="{{ number_format($month['weight'], 0) }} kg"></div>
                                    <span class="text-xs text-gray-600 mt-2">{{ $month['month'] }}</span>
                                    <span class="text-xs text-gray-400">{{ number_format($month['weight'], 0) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
