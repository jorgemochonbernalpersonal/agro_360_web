<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Cuadro de Mando — Vendimia {{ $vintageYear }}"
        :description="__('Comparativa en tiempo real: aforo viticultor · previsión bodega · recibido real.')"
    />

    {{-- Nav vendimia --}}
    <x-agro.harvest-nav />

    {{-- Stat-bar --}}
    <div class="grid grid-cols-2 gap-4">
        <x-agro.stat-card
            :label="__('Total recibido')"
            :value="number_format($stats['total_received_kg'], 0) . ' kg'"
            color="agro"
        />
        <x-agro.stat-card
            :label="__('Viticultores')"
            :value="$stats['viticulturists']"
            :description="$stats['total_plantings'] . ' plantaciones'"
            color="zinc"
        />
        <div class="rounded-2xl p-4 shadow-sm border {{ $stats['exceeded_count'] > 0 ? 'bg-red-50 border-red-200' : 'bg-white border-zinc-200' }}">
            <p class="text-[10px] font-semibold uppercase tracking-widest mb-1 {{ $stats['exceeded_count'] > 0 ? 'text-red-400' : 'text-zinc-400' }}">⚠ Superados</p>
            <p class="text-2xl font-bold leading-none {{ $stats['exceeded_count'] > 0 ? 'text-red-600' : 'text-zinc-300' }}">
                {{ $stats['exceeded_count'] }}
            </p>
            @if($stats['exceeded_count'] > 0)
                <button wire:click="$set('alertFilter', 'exceeded')" class="text-xs text-red-500 hover:underline mt-0.5">{{ __('Ver afectados') }}</button>
            @endif
        </div>
        <div class="rounded-2xl p-4 shadow-sm border {{ $stats['at_risk_count'] > 0 ? 'bg-amber-50 border-amber-200' : 'bg-white border-zinc-200' }}">
            <p class="text-[10px] font-semibold uppercase tracking-widest mb-1 {{ $stats['at_risk_count'] > 0 ? 'text-amber-500' : 'text-zinc-400' }}">{{ __('En riesgo') }}</p>
            <p class="text-2xl font-bold leading-none {{ $stats['at_risk_count'] > 0 ? 'text-amber-600' : 'text-zinc-300' }}">
                {{ $stats['at_risk_count'] }}
            </p>
            @if($stats['at_risk_count'] > 0)
                <button wire:click="$set('alertFilter', 'at_risk')" class="text-xs text-amber-500 hover:underline mt-0.5">{{ __('Ver en riesgo') }}</button>
            @endif
        </div>
    </div>

    @php
        $filterCount = (int) !empty($search) + (int) !empty($campaignFilter) + (int) !empty($viticulturistFilter) + (int) !empty($varietyFilter) + (int) !empty($alertFilter);
    @endphp

    {{-- Toolbar --}}
    <div class="flex items-center gap-3">

        {{-- Search --}}
        <x-agro.search-input wire:model.live.debounce.300ms="search" :placeholder="__('Buscar viticultor, variedad, parcela...')" />

        {{-- Filtros --}}
        <x-agro.filter-button modal="summary-filters" :count="$filterCount" />
    </div>

    {{-- Chips de filtros activos --}}
    @if($filterCount > 0)
        <div class="flex flex-wrap items-center gap-2">
            @if($search)
                <x-agro.filter-chip icon="magnifying-glass" :label="$search" wireRemove="$set('search', '')" />
            @endif
            @if($campaignFilter)
                @php $campLabel = $campaigns->firstWhere('id', $campaignFilter)?->year ?? $campaignFilter; @endphp
                <x-agro.filter-chip icon="calendar" :label="'Añada: ' . $campLabel" wireRemove="$set('campaignFilter', '')" />
            @endif
            @if($viticulturistFilter)
                @php $viticLabel = $linkedViticulturists->firstWhere('id', $viticulturistFilter)?->name ?? $viticulturistFilter; @endphp
                <x-agro.filter-chip icon="user" :label="$viticLabel" wireRemove="$set('viticulturistFilter', '')" />
            @endif
            @if($varietyFilter)
                <x-agro.filter-chip icon="tag" :label="$varietyFilter" wireRemove="$set('varietyFilter', '')" />
            @endif
            @if($alertFilter)
                @php $alertLabel = $alertFilter === 'exceeded' ? '⚠ Superados' : 'En riesgo (≥80%)'; @endphp
                <x-agro.filter-chip icon="bell-alert" :label="$alertLabel" wireRemove="$set('alertFilter', '')" />
            @endif
            <button
                wire:click="$set('search', ''); $set('campaignFilter', ''); $set('viticulturistFilter', ''); $set('varietyFilter', ''); $set('alertFilter', '')"
                class="text-xs text-zinc-400 hover:text-zinc-600 transition-colors">{{ __('Limpiar todo') }}</button>
        </div>
    @endif

    {{-- Skeleton durante carga --}}
    <x-agro.loading-grid target="search, campaignFilter, viticulturistFilter, varietyFilter, alertFilter" :count="6" />

    {{-- Grid de cards --}}
    <div wire:loading.remove wire:target="search, campaignFilter, viticulturistFilter, varietyFilter, alertFilter">
        @if($rows->isEmpty())
            <x-agro.empty-state
                icon="chart-bar"
                title="{{ __('Sin datos para esta campaña') }}"
                :description="__('Sigue estos pasos para comenzar la vendimia.')"
            >
                <x-slot:action>
                    <div class="mt-4 flex flex-col gap-2 text-left max-w-xs mx-auto">
                        <a href="{{ roleRoute('harvest-forecasts.create') }}" wire:navigate
                            class="flex items-center gap-3 p-3 bg-agro-50 border border-agro-200 rounded-xl hover:bg-agro-100 transition-colors">
                            <div class="w-7 h-7 rounded-lg bg-agro-600 text-white font-bold text-xs flex items-center justify-center shrink-0">1</div>
                            <div>
                                <p class="text-sm font-semibold text-zinc-900">{{ __('Crear previsión de uva') }}</p>
                                <p class="text-xs text-zinc-500">{{ __('Define el aforo esperado por plantación') }}</p>
                            </div>
                        </a>
                        <a href="{{ roleRoute('grape-reception.create') }}" wire:navigate
                            class="flex items-center gap-3 p-3 bg-white border border-zinc-200 rounded-xl hover:bg-zinc-50 transition-colors">
                            <div class="w-7 h-7 rounded-lg bg-zinc-200 text-zinc-500 font-bold text-xs flex items-center justify-center shrink-0">2</div>
                            <div>
                                <p class="text-sm font-semibold text-zinc-900">{{ __('Registrar recepciones') }}</p>
                                <p class="text-xs text-zinc-500">{{ __('Anota cada entrada de uva durante la vendimia') }}</p>
                            </div>
                        </a>
                    </div>
                </x-slot:action>
            </x-agro.empty-state>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($rows as $row)
                    @php
                        $delay         = min($loop->index * 50, 300);
                        $initials      = strtoupper(substr($row['viticulturist']?->name ?? '?', 0, 1));
                        $progressColor = $row['exceeded'] || $row['exceeded_pac'] ? 'red' : ($row['at_risk'] ? 'amber' : 'agro');
                        $statusColor   = $row['exceeded'] || $row['exceeded_pac'] ? 'red' : ($row['at_risk'] ? 'amber' : ($row['received_kg'] > 0 ? 'agro' : 'zinc'));
                        $statusLabel   = $row['exceeded'] || $row['exceeded_pac'] ? __('⚠ Superado') : ($row['at_risk'] ? __('En riesgo') : ($row['received_kg'] > 0 ? __('En curso') : ($row['forecast_kg'] !== null ? __('Pendiente') : __('Sin actividad'))));
                    @endphp
                    <x-agro.card
                        class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                        style="animation-delay: {{ $delay }}ms;"
                        wire:key="summary-card-{{ $row['key'] }}"
                    >
                        <x-slot:header>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-agro-50 flex items-center justify-center shrink-0">
                                    <span class="text-sm font-bold text-agro-700">{{ $initials }}</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-zinc-900 truncate">{{ $row['viticulturist']?->name ?? '—' }}</h3>
                                    <p class="text-xs text-zinc-500 truncate">{{ $row['variety'] }}</p>
                                </div>
                                <x-agro.status-badge :color="$statusColor" :label="$statusLabel" class="shrink-0" />
                            </div>
                        </x-slot:header>

                        <div class="flex-1 space-y-4">

                            {{-- Parcela + área --}}
                            <div class="flex items-center gap-2 text-sm text-zinc-600">
                                <flux:icon icon="map" class="size-4 text-zinc-400 shrink-0" />
                                <span class="truncate">{{ $row['plot'] }}</span>
                                @if($row['area'])
                                    <span class="text-zinc-400 shrink-0">· {{ number_format($row['area'], 2) }} ha</span>
                                @endif
                            </div>

                            {{-- Mini stats: Recibido + Previsión --}}
                            <div class="grid grid-cols-2 gap-3">
                                <div class="bg-agro-50 rounded-xl p-3">
                                    <p class="text-[10px] font-semibold text-agro-400 uppercase tracking-widest mb-1">{{ __('Recibido') }}</p>
                                    <p class="text-xl font-bold text-agro-700 leading-none">
                                        {{ number_format($row['received_kg'], 0) }}
                                        <span class="text-xs font-medium text-agro-400 ml-0.5">kg</span>
                                    </p>
                                </div>
                                <div class="bg-zinc-50 rounded-xl p-3">
                                    <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest mb-1">{{ __('Previsión') }}</p>
                                    @if($row['forecast_kg'] !== null)
                                        <p class="text-xl font-bold text-zinc-700 leading-none">
                                            {{ number_format($row['forecast_kg'], 0) }}
                                            <span class="text-xs font-medium text-zinc-400 ml-0.5">kg</span>
                                        </p>
                                        @if($row['forecast_status'] === 'draft')
                                            <p class="text-[10px] text-amber-500 mt-0.5">{{ __('Borrador') }}</p>
                                        @endif
                                    @else
                                        <p class="text-base font-medium text-zinc-300 italic leading-none">—</p>
                                    @endif
                                </div>
                            </div>

                            {{-- Barra de ejecución --}}
                            @if($row['pct_op_limit'] !== null)
                                <div>
                                    <div class="flex justify-between text-xs text-zinc-500 mb-1">
                                        <span>{{ __('Ejecución') }}</span>
                                        <span class="{{ $row['exceeded'] ? 'text-red-600 font-semibold' : ($row['at_risk'] ? 'text-amber-600 font-semibold' : 'text-zinc-700') }}">
                                            {{ $row['pct_op_limit'] }}%
                                            @if($row['exceeded']) ⚠ @endif
                                        </span>
                                    </div>
                                    <x-agro.progress-bar :percentage="min($row['pct_op_limit'], 100)" :color="$progressColor" />
                                </div>
                            @endif

                            {{-- PAC + Aforo viticultor --}}
                            @if($row['pac_limit'] !== null || $row['vitic_estimate'] !== null)
                                <div class="space-y-1.5 text-sm border-t border-zinc-100 pt-3">
                                    @if($row['pac_limit'] !== null)
                                        <div class="flex items-center justify-between">
                                            <span class="text-zinc-400">{{ __('PAC límite') }}</span>
                                            <span class="text-blue-700 font-medium">{{ number_format($row['pac_limit'], 0) }} kg</span>
                                        </div>
                                    @endif
                                    @if($row['vitic_estimate'] !== null)
                                        <div class="flex items-center justify-between">
                                            <span class="text-zinc-400">{{ __('Aforo viticultor') }}</span>
                                            <span class="text-violet-700 font-medium">{{ number_format($row['vitic_estimate'], 0) }} kg</span>
                                        </div>
                                    @endif
                                </div>
                            @endif

                        </div>

                        {{-- Footer: estado del lote --}}
                        @if($row['batch_id'])
                        <x-slot:footer>
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-zinc-400">
                                    Lote:
                                    @if($row['batch_status'] === 'closed')
                                        <span class="font-semibold text-zinc-500">{{ __('Cerrado') }}</span>
                                    @elseif($row['batch_status'] === 'invoiced')
                                        <span class="font-semibold text-agro-600">{{ __('Facturado') }}</span>
                                    @else
                                        <span class="font-semibold text-green-600">{{ __('Abierto') }}</span>
                                    @endif
                                </span>
                                @if($row['batch_status'] === 'open')
                                    <button
                                        wire:click="closeBatch({{ $row['batch_id'] }})"
                                        wire:confirm="{{ __('¿Cerrar este lote? No se podrán añadir más recepciones hasta reabrirlo.') }}"
                                        class="text-xs text-zinc-400 hover:text-zinc-700 transition-colors flex items-center gap-1"
                                        title="{{ __('Cerrar lote') }}"
                                    >
                                        <flux:icon icon="lock-closed" class="size-3.5" />
                                        Cerrar lote
                                    </button>
                                @elseif($row['batch_status'] === 'closed')
                                    <button
                                        wire:click="reopenBatch({{ $row['batch_id'] }})"
                                        class="text-xs text-agro-500 hover:text-agro-700 transition-colors flex items-center gap-1"
                                        title="{{ __('Reabrir lote') }}"
                                    >
                                        <flux:icon icon="lock-open" class="size-3.5" />
                                        Reabrir
                                    </button>
                                @endif
                            </div>
                        </x-slot:footer>
                        @endif

                    </x-agro.card>
                @endforeach
            </div>

        @endif
    </div>

    {{-- Histórico multi-añada --}}
    @if($historicalRows->isNotEmpty() && $historicalYears->count() > 1)
        <div x-data="{ open: false }">
            <button @click="open = !open"
                class="w-full flex items-center justify-between px-5 py-3 bg-white border border-zinc-200 rounded-xl shadow-sm hover:bg-zinc-50 transition-colors text-sm font-medium text-zinc-700">
                <div class="flex items-center gap-2">
                    <flux:icon icon="chart-bar" class="size-4 text-zinc-400" />
                    Histórico multi-añada
                    <span class="text-xs text-zinc-400 font-normal">({{ $historicalYears->first() }} – {{ $historicalYears->last() }})</span>
                </div>
                <flux:icon icon="chevron-down" class="size-4 text-zinc-400 transition-transform" x-bind:class="open ? 'rotate-180' : ''" />
            </button>

            <div x-show="open" x-transition class="mt-3">
                <x-agro.card>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-zinc-100">
                                    <th class="text-left py-2 px-3 font-semibold text-zinc-500 text-xs uppercase tracking-wide">{{ __('Viticultor') }}</th>
                                    <th class="text-left py-2 px-3 font-semibold text-zinc-500 text-xs uppercase tracking-wide">{{ __('Variedad') }}</th>
                                    @foreach($historicalYears as $year)
                                        <th class="text-right py-2 px-3 font-semibold text-zinc-500 text-xs uppercase tracking-wide">{{ $year }}</th>
                                    @endforeach
                                    <th class="text-right py-2 px-3 font-semibold text-zinc-500 text-xs uppercase tracking-wide">{{ __('Tendencia') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($historicalRows as $hRow)
                                    @php
                                        $vals = $hRow['years']->values()->filter(fn($v) => $v > 0);
                                        $trend = $vals->count() >= 2
                                            ? ($vals->last() > $vals->first() ? 'up' : ($vals->last() < $vals->first() ? 'down' : 'flat'))
                                            : 'flat';
                                    @endphp
                                    <tr class="border-b border-zinc-50 hover:bg-zinc-50 transition-colors">
                                        <td class="py-2.5 px-3 font-medium text-zinc-800">{{ $hRow['viticulturist_name'] }}</td>
                                        <td class="py-2.5 px-3 text-zinc-500">{{ $hRow['variety'] }}</td>
                                        @foreach($historicalYears as $year)
                                            @php $kg = $hRow['years'][$year] ?? 0; @endphp
                                            <td class="py-2.5 px-3 text-right {{ $kg > 0 ? 'text-zinc-800 font-medium' : 'text-zinc-300' }}">
                                                {{ $kg > 0 ? number_format($kg, 0) . ' kg' : '—' }}
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
                </x-agro.card>
            </div>
        </div>
    @endif

    {{-- Modal Filtros --}}
    <x-agro.modal name="summary-filters" maxWidth="sm">
        <div class="px-6 py-4 border-b border-zinc-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-agro-100 rounded-lg flex items-center justify-center">
                        <flux:icon icon="adjustments-horizontal" class="size-4 text-agro-600" />
                    </div>
                    <h3 class="text-base font-semibold text-zinc-900">{{ __('Filtros') }}</h3>
                </div>
                <flux:button x-on:click="$dispatch('close-modal', 'summary-filters')" variant="ghost" size="sm" icon="x-mark" />
            </div>
        </div>

        <div class="px-6 py-5 space-y-5">
            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">{{ __('Campaña') }}</label>
                <flux:select wire:model.live="campaignFilter">
                    <option value="">{{ __('Todas las campañas') }}</option>
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
            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">{{ __('Variedad') }}</label>
                <flux:select wire:model.live="varietyFilter">
                    <option value="">{{ __('Todas') }}</option>
                    @foreach($varieties as $variety)
                        <option value="{{ $variety }}">{{ $variety }}</option>
                    @endforeach
                </flux:select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">{{ __('Alertas') }}</label>
                <flux:select wire:model.live="alertFilter">
                    <option value="">{{ __('Todas') }}</option>
                    <option value="exceeded">{{ __('Superados') }}</option>
                    <option value="at_risk">{{ __('En riesgo (≥80%)') }}</option>
                </flux:select>
            </div>
        </div>

        <div class="px-6 py-4 border-t border-zinc-200 flex items-center justify-between">
            @if($filterCount > 0)
                <button
                    wire:click="$set('search', ''); $set('campaignFilter', ''); $set('viticulturistFilter', ''); $set('varietyFilter', ''); $set('alertFilter', '')"
                    class="text-sm text-zinc-400 hover:text-zinc-600 transition-colors">{{ __('Limpiar filtros') }}</button>
            @else
                <span></span>
            @endif
            <flux:button x-on:click="$dispatch('close-modal', 'summary-filters')" variant="primary">{{ __('Aplicar') }}</flux:button>
        </div>
    </x-agro.modal>

</div>
