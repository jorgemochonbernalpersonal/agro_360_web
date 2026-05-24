<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        :title="__('Facturar Cosechas')"
        :description="__('Gestiona la facturación de tus cosechas y análisis')"
        icon="document-text"
    />

    {{-- Tabs --}}
    <x-agro.tabs
        :tabs="['list' => __('Cosechas Pendientes'), 'statistics' => __('Estadísticas')]"
        :active="$currentTab"
        wire-method="switchTab"
    />

    {{-- LIST TAB --}}
    @if($currentTab === 'list')
        <div class="space-y-6">
            {{-- Búsqueda --}}
            <x-agro.search-input wire:model.live="search" :placeholder="__('Buscar por parcela o variedad...')" />

            {{-- Cards --}}
            @if($harvests->count() === 0)
                <x-agro.empty-state
                    icon="beaker"
                    :title="$search ? __('Ninguna cosecha coincide con la búsqueda') : __('No hay cosechas pendientes')"
                    :description="$search ? __('Prueba a cambiar los términos de búsqueda.') : __('Registra cosechas en el Cuaderno Digital para poder facturarlas.')"
                >
                    @if($search)
                        <x-slot:action>
                            <flux:button wire:click="$set('search', '')" variant="outline" icon="x-mark">{{ __('Limpiar búsqueda') }}</flux:button>
                        </x-slot:action>
                    @endif
                </x-agro.empty-state>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($harvests as $harvest)
                        @php $delay = min($loop->index * 50, 300); @endphp
                        <x-agro.card
                            class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                            style="animation-delay: {{ $delay }}ms;"
                            wire:key="harvest-{{ $harvest->id }}"
                        >
                            <x-slot:header>
                                <x-agro.card-item-header
                                    icon="beaker"
                                    :title="$harvest->activity->plot->name ?? 'N/A'"
                                    :subtitle="$harvest->harvest_start_date->format('d/m/Y')"
                                    iconBg="bg-agro-100"
                                    iconColor="text-agro-600"
                                    size="md"
                                    radius="xl"
                                />
                            </x-slot:header>

                            <div class="flex-1 space-y-3">
                                @if($harvest->plotPlanting->grapeVariety ?? null)
                                    <div class="flex items-center gap-2 text-xs text-zinc-500">
                                        <flux:icon icon="scissors" class="size-3.5 text-zinc-400 shrink-0" />
                                        <span>{{ $harvest->plotPlanting->grapeVariety->name }}</span>
                                    </div>
                                @endif

                                <div class="grid grid-cols-2 gap-2">
                                    <div class="bg-zinc-50 rounded-xl p-3">
                                        <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest mb-0.5">{{ __('Peso') }}</p>
                                        <p class="text-xl font-bold text-zinc-700 leading-none">
                                            {{ number_format($harvest->total_weight, 0) }}<span class="text-xs font-normal text-zinc-400 ml-0.5">kg</span>
                                        </p>
                                    </div>
                                    @if($harvest->total_value)
                                        <div class="bg-agro-50 rounded-xl p-3">
                                            <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest mb-0.5">{{ __('Valor') }}</p>
                                            <p class="text-xl font-bold text-agro-700 leading-none">
                                                {{ number_format($harvest->total_value, 2) }}<span class="text-xs font-normal text-zinc-400 ml-0.5">€</span>
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <x-slot:footer>
                                <div class="flex items-center justify-end">
                                    <flux:button
                                        href="{{ roleRoute('viticulturist.invoices.create', ['harvest_id' => $harvest->id]) }}"
                                        variant="primary"
                                        size="sm"
                                        icon="document-text"
                                        wire:navigate
                                    >
                                        {{ __('Facturar') }}
                                    </flux:button>
                                </div>
                            </x-slot:footer>
                        </x-agro.card>
                    @endforeach
                </div>

                <x-agro-pagination :paginator="$harvests" />
            @endif
        </div>
    @endif

    {{-- STATISTICS TAB --}}
    @if($currentTab === 'statistics')
        <div class="space-y-6">
            {{-- Filtro de Año --}}
            <div class="flex justify-end">
                <flux:select wire:model.live="yearFilter" class="w-auto">
                    @for($year = now()->year; $year >= now()->year - 5; $year--)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endfor
                </flux:select>
            </div>

            {{-- KPIs --}}
            <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
                <x-agro.stat-card :label="__('Total Cosechado')" :value="number_format($advancedStats['totalHarvested'] ?? 0, 0) . ' kg'" :description="__('En :year', ['year' => $yearFilter])" icon="beaker" color="blue" />
                <x-agro.stat-card :label="__('Facturado')" :value="number_format($advancedStats['totalInvoiced'] ?? 0, 0) . ' kg'" :description="number_format($advancedStats['invoicedPercentage'] ?? 0, 1) . __('% del total')" icon="check-circle" color="agro" />
                <x-agro.stat-card :label="__('Pendiente')" :value="number_format($advancedStats['pendingToInvoice'] ?? 0, 0) . ' kg'" :description="__('Sin facturar')" icon="clock" color="amber" />
                <x-agro.stat-card :label="__('Precio Medio')" :value="number_format($advancedStats['avgPricePerKg'] ?? 0, 2) . ' €'" :description="__('Por kilogramo')" icon="banknotes" color="agro" />
                <x-agro.stat-card :label="__('Ingresos Cosechas')" :value="number_format($advancedStats['harvestRevenue'] ?? 0, 2) . ' €'" :description="__('Facturación total en :year', ['year' => $yearFilter])" icon="currency-euro" color="blue" />
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Por Variedad --}}
                <x-agro.card>
                    <x-slot:header>
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 rounded-lg bg-agro-50">
                                <flux:icon icon="beaker" class="size-4 text-agro-600" />
                            </div>
                            <span class="font-semibold text-zinc-900 text-sm">{{ __('Análisis por Variedad') }}</span>
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
                                        <p class="text-zinc-500">{{ __('Facturado') }}</p>
                                        <p class="font-bold text-green-700">{{ number_format($variety['invoiced'], 0) }} kg</p>
                                    </div>
                                    <div>
                                        <p class="text-zinc-500">{{ __('Pendiente') }}</p>
                                        <p class="font-bold text-orange-700">{{ number_format($variety['pending'], 0) }} kg</p>
                                    </div>
                                    <div>
                                        <p class="text-zinc-500">{{ __('% Fac.') }}</p>
                                        <p class="font-bold text-blue-700">{{ number_format($variety['percentage'], 1) }}%</p>
                                    </div>
                                </div>
                                <div class="mt-2 w-full bg-zinc-200 rounded-full h-2">
                                    <div class="bg-agro-500 h-2 rounded-full" style="width: {{ $variety['percentage'] }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-zinc-500 text-center py-4">{{ __('No hay datos') }}</p>
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
                            <span class="font-semibold text-zinc-900 text-sm">{{ __('Top Parcelas por Rendimiento') }}</span>
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
                                        <p class="text-xs text-zinc-500">{{ $plot['harvests_count'] }} {{ __('cosechas') }}</p>
                                    </div>
                                </div>
                                <span class="font-bold text-agro-700">{{ number_format($plot['total_weight'], 0) }} kg</span>
                            </div>
                        @empty
                            <p class="text-zinc-500 text-center py-4">{{ __('No hay datos') }}</p>
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
                        <span class="font-semibold text-zinc-900 text-sm">{{ __('Cosechas Mensuales (Últimos 12 meses)') }}</span>
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
