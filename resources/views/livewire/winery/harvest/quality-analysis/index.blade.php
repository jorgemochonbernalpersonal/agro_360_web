<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="{{ __('Análisis de Calidad — Vendimia') }}"
        :description="__('Comparativa de parámetros enológicos por viticultor y variedad.')"
    />

    {{-- Nav vendimia --}}
    <x-agro.harvest-nav />

    @php
        $filterCount = (int) !empty($campaignFilter) + (int) !empty($viticulturistFilter);
        $healthLabels = ['sano' => 'Sano', 'daño_leve' => 'Daño leve', 'daño_moderado' => 'Daño moderado', 'daño_grave' => 'Daño grave'];
        $healthColors = ['sano' => 'green', 'daño_leve' => 'amber', 'daño_moderado' => 'orange', 'daño_grave' => 'red'];
    @endphp

    {{-- Toolbar + filtros inline --}}
    <div x-data="{ filtersOpen: {{ $filterCount > 0 ? 'true' : 'false' }} }" class="space-y-3">

        <div class="flex items-center gap-3">
            <button @click="filtersOpen = !filtersOpen"
                class="relative inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm font-medium text-zinc-700 hover:bg-zinc-50 shadow-sm transition-colors">
                <flux:icon icon="adjustments-horizontal" class="size-4 text-zinc-500" />
                Filtros
                @if($filterCount > 0)
                    <span class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-agro-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center leading-none">
                        {{ $filterCount }}
                    </span>
                @endif
                <flux:icon icon="chevron-down" class="size-3.5 text-zinc-400 transition-transform duration-200" x-bind:class="filtersOpen ? 'rotate-180' : ''" />
            </button>

            <x-agro.divider-vertical />

            <flux:button href="{{ roleRoute('harvest-quality.export-pdf', array_filter(['campaign' => $campaignFilter, 'viticulturist' => $viticulturistFilter])) }}" target="_blank" icon="document-arrow-down">PDF</flux:button>
        </div>

        {{-- Panel inline --}}
        <div x-show="filtersOpen" x-transition.duration.150ms
            class="bg-white border border-zinc-200 rounded-2xl p-4 shadow-sm">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">{{ __('Campaña / Añada') }}</label>
                    <flux:select wire:model.live="campaignFilter">
                        <option value="">{{ __('Todas las añadas') }}</option>
                        @foreach($campaigns as $c)
                            <option value="{{ $c->id }}">{{ $c->year }}</option>
                        @endforeach
                    </flux:select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">{{ __('Viticultor') }}</label>
                    <flux:select wire:model.live="viticulturistFilter">
                        <option value="">{{ __('Todos') }}</option>
                        @foreach($linkedViticulturists as $v)
                            <option value="{{ $v->id }}">{{ $v->name }}</option>
                        @endforeach
                    </flux:select>
                </div>
            </div>
            @if($filterCount > 0)
                <div class="mt-3 pt-3 border-t border-zinc-100">
                    <button wire:click="$set('campaignFilter', ''); $set('viticulturistFilter', '')"
                        class="text-xs text-zinc-400 hover:text-zinc-600 transition-colors">{{ __('Limpiar filtros') }}</button>
                </div>
            @endif
        </div>
    </div>

    {{-- Chips de filtros activos --}}
    @if($filterCount > 0)
        <div class="flex flex-wrap items-center gap-2">
            @if($campaignFilter)
                @php $campLabel = $campaigns->firstWhere('id', $campaignFilter)?->year ?? $campaignFilter; @endphp
                <x-agro.filter-chip icon="calendar" :label="'Añada: ' . $campLabel" wireRemove="$set('campaignFilter', '')" />
            @endif
            @if($viticulturistFilter)
                @php $viticLabel = $linkedViticulturists->firstWhere('id', $viticulturistFilter)?->name ?? $viticulturistFilter; @endphp
                <x-agro.filter-chip icon="user" :label="$viticLabel" wireRemove="$set('viticulturistFilter', '')" />
            @endif
            <button wire:click="$set('campaignFilter', ''); $set('viticulturistFilter', '')"
                class="text-xs text-zinc-400 hover:text-zinc-600 transition-colors">{{ __('Limpiar todo') }}</button>
        </div>
    @endif

    {{-- Skeleton --}}
    <x-agro.loading-grid target="campaignFilter, viticulturistFilter" :count="6" />

    <div wire:loading.remove wire:target="campaignFilter, viticulturistFilter">

        @if($globalStats['total_entries'] === 0)
            <x-agro.empty-state
                icon="beaker"
                title="{{ __('Sin datos de calidad') }}"
                :description="__('Registra recepciones con parámetros de calidad (Baumé, Brix, alcohol…) para ver el análisis.')"
            >
                <x-slot:action>
                    <flux:button variant="primary" icon="plus" href="{{ roleRoute('grape-reception.create') }}" wire:navigate>
                        Nueva recepción
                    </flux:button>
                </x-slot:action>
            </x-agro.empty-state>
        @else

            {{-- Resumen global --}}
            <div class="grid grid-cols-2 gap-4">
                <x-agro.stat-card
                    :label="__('Total recibido')"
                    :value="number_format($globalStats['total_kg'], 0) . ' kg'"
                    color="agro"
                />
                @if($globalStats['avg_alcohol'] !== null)
                <x-agro.stat-card
                    :label="__('Media alcohol pot.')"
                    :value="$globalStats['avg_alcohol'] . ' %'"
                    color="blue"
                />
                @endif
                @if($globalStats['avg_baume'] !== null)
                <x-agro.stat-card
                    :label="__('Media Baumé')"
                    :value="$globalStats['avg_baume'] . ' °Bé'"
                    color="purple"
                />
                @endif
                @if($globalStats['avg_acidity'] !== null)
                <x-agro.stat-card
                    :label="__('Media acidez')"
                    :value="$globalStats['avg_acidity'] . ' g/L'"
                    color="amber"
                />
                @endif
            </div>

            {{-- Por viticultor --}}
            @if($byViticulturist->isNotEmpty())
                <div>
                    <h2 class="text-sm font-semibold text-zinc-500 uppercase tracking-wide mb-3">{{ __('Por viticultor') }}</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        @foreach($byViticulturist as $idx => $item)
                            @php $delay = min($idx * 50, 300); @endphp
                            <x-agro.card class="animate-fade-in-up hover:-translate-y-1" style="animation-delay: {{ $delay }}ms;">
                                <x-slot:header>
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-agro-50 flex items-center justify-center shrink-0">
                                            <span class="text-sm font-bold text-agro-700">{{ strtoupper(substr($item['label'], 0, 1)) }}</span>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h3 class="font-bold text-zinc-900 truncate">{{ $item['label'] }}</h3>
                                            <p class="text-xs text-zinc-500">{{ $item['count'] }} recepciones · {{ number_format($item['total_kg'], 0) }} kg</p>
                                        </div>
                                    </div>
                                </x-slot:header>
                                <div class="space-y-3">
                                    <div class="grid grid-cols-2 gap-3">
                                        @if($item['avg_alcohol'] !== null)
                                        <div class="bg-blue-50 rounded-xl p-3">
                                            <p class="text-[10px] font-semibold text-blue-400 uppercase tracking-widest mb-1">{{ __('Alc. pot.') }}</p>
                                            <p class="text-xl font-bold text-blue-700">{{ $item['avg_alcohol'] }} <span class="text-xs text-blue-400">%</span></p>
                                        </div>
                                        @else
                                        <div class="bg-zinc-50 rounded-xl p-3">
                                            <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest mb-1">{{ __('Alc. pot.') }}</p>
                                            <p class="text-base font-medium text-zinc-300 italic">—</p>
                                        </div>
                                        @endif
                                        @if($item['avg_baume'] !== null)
                                        <div class="bg-violet-50 rounded-xl p-3">
                                            <p class="text-[10px] font-semibold text-violet-400 uppercase tracking-widest mb-1">{{ __('Baumé') }}</p>
                                            <p class="text-xl font-bold text-violet-700">{{ $item['avg_baume'] }} <span class="text-xs text-violet-400">{{ __('°Bé') }}</span></p>
                                        </div>
                                        @else
                                        <div class="bg-zinc-50 rounded-xl p-3">
                                            <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest mb-1">{{ __('Baumé') }}</p>
                                            <p class="text-base font-medium text-zinc-300 italic">—</p>
                                        </div>
                                        @endif
                                    </div>
                                    <div class="space-y-1.5 text-sm">
                                        @if($item['avg_brix'] !== null)
                                        <div class="flex justify-between">
                                            <span class="text-zinc-400">{{ __('Brix medio') }}</span>
                                            <span class="font-medium text-zinc-700">{{ $item['avg_brix'] }} °Bx</span>
                                        </div>
                                        @endif
                                        @if($item['avg_acidity'] !== null)
                                        <div class="flex justify-between">
                                            <span class="text-zinc-400">{{ __('Acidez media') }}</span>
                                            <span class="font-medium text-amber-700">{{ $item['avg_acidity'] }} g/L</span>
                                        </div>
                                        @endif
                                        @if($item['avg_ph'] !== null)
                                        <div class="flex justify-between">
                                            <span class="text-zinc-400">{{ __('pH medio') }}</span>
                                            <span class="font-medium text-zinc-700">{{ $item['avg_ph'] }}</span>
                                        </div>
                                        @endif
                                    </div>
                                    @if($item['health_dist']->isNotEmpty())
                                    <div class="flex flex-wrap gap-1.5 border-t border-zinc-100 pt-3">
                                        @foreach($item['health_dist'] as $status => $count)
                                            <x-agro.status-badge
                                                :color="$healthColors[$status] ?? 'zinc'"
                                                :label="($healthLabels[$status] ?? $status) . ' (' . $count . ')'"
                                            />
                                        @endforeach
                                    </div>
                                    @endif
                                </div>
                            </x-agro.card>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Por variedad (tabla) --}}
            @if($byVariety->isNotEmpty())
                <div>
                    <h2 class="text-sm font-semibold text-zinc-500 uppercase tracking-wide mb-3">{{ __('Por variedad') }}</h2>
                    <x-agro.card>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-zinc-100">
                                        <th class="text-left py-2 px-3 font-semibold text-zinc-500 text-xs uppercase tracking-wide">{{ __('Variedad') }}</th>
                                        <th class="text-right py-2 px-3 font-semibold text-zinc-500 text-xs uppercase tracking-wide">{{ __('Total kg') }}</th>
                                        <th class="text-right py-2 px-3 font-semibold text-zinc-500 text-xs uppercase tracking-wide">{{ __('Alc. %') }}</th>
                                        <th class="text-right py-2 px-3 font-semibold text-zinc-500 text-xs uppercase tracking-wide">{{ __('Baumé °Bé') }}</th>
                                        <th class="text-right py-2 px-3 font-semibold text-zinc-500 text-xs uppercase tracking-wide">{{ __('Brix °Bx') }}</th>
                                        <th class="text-right py-2 px-3 font-semibold text-zinc-500 text-xs uppercase tracking-wide">{{ __('Acidez g/L') }}</th>
                                        <th class="text-right py-2 px-3 font-semibold text-zinc-500 text-xs uppercase tracking-wide">pH</th>
                                        <th class="text-right py-2 px-3 font-semibold text-zinc-500 text-xs uppercase tracking-wide">{{ __('Entradas') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($byVariety as $item)
                                        <tr class="border-b border-zinc-50 hover:bg-zinc-50 transition-colors">
                                            <td class="py-2.5 px-3 font-semibold text-zinc-900">{{ $item['label'] }}</td>
                                            <td class="py-2.5 px-3 text-right font-bold text-agro-700">{{ number_format($item['total_kg'], 0) }} kg</td>
                                            <td class="py-2.5 px-3 text-right {{ $item['avg_alcohol'] ? 'text-blue-700 font-semibold' : 'text-zinc-300' }}">
                                                {{ $item['avg_alcohol'] !== null ? $item['avg_alcohol'] . '%' : '—' }}
                                            </td>
                                            <td class="py-2.5 px-3 text-right {{ $item['avg_baume'] ? 'text-zinc-700 font-medium' : 'text-zinc-300' }}">
                                                {{ $item['avg_baume'] !== null ? $item['avg_baume'] . '°' : '—' }}
                                            </td>
                                            <td class="py-2.5 px-3 text-right {{ $item['avg_brix'] ? 'text-zinc-700 font-medium' : 'text-zinc-300' }}">
                                                {{ $item['avg_brix'] !== null ? $item['avg_brix'] . '°' : '—' }}
                                            </td>
                                            <td class="py-2.5 px-3 text-right {{ $item['avg_acidity'] ? 'text-amber-700 font-medium' : 'text-zinc-300' }}">
                                                {{ $item['avg_acidity'] !== null ? $item['avg_acidity'] . ' g/L' : '—' }}
                                            </td>
                                            <td class="py-2.5 px-3 text-right {{ $item['avg_ph'] ? 'text-zinc-700 font-medium' : 'text-zinc-300' }}">
                                                {{ $item['avg_ph'] ?? '—' }}
                                            </td>
                                            <td class="py-2.5 px-3 text-right text-zinc-500">{{ $item['count'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </x-agro.card>
                </div>
            @endif

        @endif
    </div>

    {{-- Comparativa multi-añada --}}
    @if($comparison->isNotEmpty() && $compYears->count() >= 2)
        <div x-data="{ open: false }">
            <button @click="open = !open"
                class="w-full flex items-center justify-between px-5 py-3 bg-white border border-zinc-200 rounded-xl shadow-sm hover:bg-zinc-50 transition-colors text-sm font-medium text-zinc-700">
                <div class="flex items-center gap-2">
                    <flux:icon icon="chart-bar-square" class="size-4 text-zinc-400" />
                    Comparativa multi-añada — alcohol potencial
                    <span class="text-xs text-zinc-400 font-normal">({{ $compYears->first() }} – {{ $compYears->last() }})</span>
                </div>
                <flux:icon icon="chevron-down" class="size-4 text-zinc-400 transition-transform duration-200" x-bind:class="open ? 'rotate-180' : ''" />
            </button>

            <div x-show="open" x-transition class="mt-3">
                <x-agro.card>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-zinc-100">
                                    <th class="text-left py-2 px-3 font-semibold text-zinc-500 text-xs uppercase tracking-wide">{{ __('Viticultor') }}</th>
                                    @foreach($compYears as $cy)
                                        <th colspan="3" class="text-center py-2 px-3 font-semibold text-zinc-500 text-xs uppercase tracking-wide border-l border-zinc-100">
                                            {{ $cy }}
                                        </th>
                                    @endforeach
                                    <th class="text-right py-2 px-3 font-semibold text-zinc-500 text-xs uppercase tracking-wide">{{ __('Tendencia alc.') }}</th>
                                </tr>
                                <tr class="border-b border-zinc-100 bg-zinc-50">
                                    <th class="py-1 px-3"></th>
                                    @foreach($compYears as $cy)
                                        <th class="text-right py-1 px-2 text-[10px] font-medium text-zinc-400 border-l border-zinc-100">{{ __('Alc.%') }}</th>
                                        <th class="text-right py-1 px-2 text-[10px] font-medium text-zinc-400">{{ __('°Bé') }}</th>
                                        <th class="text-right py-1 px-2 text-[10px] font-medium text-zinc-400">{{ __('kg') }}</th>
                                    @endforeach
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($comparison as $row)
                                    @php
                                        $alcoholVals = $compYears->map(fn($y) => $row['years'][$y]['alcohol'])->filter();
                                        $trend = $alcoholVals->count() >= 2
                                            ? ($alcoholVals->last() > $alcoholVals->first() ? 'up'
                                                : ($alcoholVals->last() < $alcoholVals->first() ? 'down' : 'flat'))
                                            : 'flat';
                                    @endphp
                                    <tr class="border-b border-zinc-50 hover:bg-zinc-50 transition-colors">
                                        <td class="py-2.5 px-3 font-semibold text-zinc-900">{{ $row['name'] }}</td>
                                        @foreach($compYears as $cy)
                                            @php $yd = $row['years'][$cy]; @endphp
                                            <td class="py-2.5 px-2 text-right border-l border-zinc-100 {{ $yd['alcohol'] ? 'text-blue-700 font-semibold' : 'text-zinc-300' }}">
                                                {{ $yd['alcohol'] !== null ? $yd['alcohol'] . '%' : '—' }}
                                            </td>
                                            <td class="py-2.5 px-2 text-right {{ $yd['baume'] ? 'text-violet-700 font-medium' : 'text-zinc-300' }}">
                                                {{ $yd['baume'] !== null ? $yd['baume'] . '°' : '—' }}
                                            </td>
                                            <td class="py-2.5 px-2 text-right text-zinc-500">
                                                {{ $yd['kg'] > 0 ? number_format($yd['kg'], 0) : '—' }}
                                            </td>
                                        @endforeach
                                        <td class="py-2.5 px-3 text-right">
                                            @if($trend === 'up')
                                                <span class="text-agro-600 font-semibold flex items-center justify-end gap-1">
                                                    <flux:icon icon="arrow-trending-up" class="size-4" /> Sube
                                                </span>
                                            @elseif($trend === 'down')
                                                <span class="text-red-500 font-semibold flex items-center justify-end gap-1">
                                                    <flux:icon icon="arrow-trending-down" class="size-4" /> Baja
                                                </span>
                                            @else
                                                <span class="text-zinc-400 flex items-center justify-end gap-1">
                                                    <flux:icon icon="minus" class="size-4" /> Estable
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <p class="text-xs text-zinc-400 mt-3">{{ __('Solo se incluyen recepciones con alcohol potencial registrado. La comparativa es independiente del filtro de añada activo.') }}</p>
                </x-agro.card>
            </div>
        </div>
    @endif

</div>
