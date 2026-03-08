<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Cuadro de Mando — Vendimia {{ $vintageYear }}"
        description="Comparativa en tiempo real: aforo viticultor · previsión bodega · recibido real."
    />

    @php
        $filterCount = (int) !empty($campaignFilter) + (int) !empty($viticulturistFilter) + (int) !empty($varietyFilter) + (int) !empty($alertFilter);
    @endphp

    {{-- Toolbar --}}
    <div class="flex items-center gap-3">

        {{-- Filtros --}}
        <button x-on:click="$dispatch('open-modal', 'summary-filters')"
            class="relative inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm font-medium text-zinc-700 hover:bg-zinc-50 shadow-sm transition-colors">
            <flux:icon icon="adjustments-horizontal" class="size-4 text-zinc-500" />
            Filtros
            @if($filterCount > 0)
                <span class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-agro-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center leading-none">
                    {{ $filterCount }}
                </span>
            @endif
        </button>

        {{-- Separador --}}
        <div class="w-px h-8 bg-zinc-200 shrink-0"></div>

        {{-- Navegación rápida --}}
        <flux:button variant="ghost" icon="calculator" href="{{ route('winery.vitic-estimates.index') }}" wire:navigate size="sm">
            Aforos
        </flux:button>
        <flux:button variant="ghost" icon="clipboard-document-list" href="{{ route('winery.harvest-forecasts.index') }}" wire:navigate size="sm">
            Previsiones
        </flux:button>
        <flux:button variant="primary" icon="archive-box-arrow-down" href="{{ route('winery.grape-reception.index') }}" wire:navigate size="sm">
            Recepciones
        </flux:button>
    </div>

    {{-- Chips de filtros activos --}}
    @if($filterCount > 0)
        <div class="flex flex-wrap items-center gap-2">
            @if($campaignFilter)
                @php $campLabel = $campaigns->firstWhere('id', $campaignFilter)?->year ?? $campaignFilter; @endphp
                <span class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                    <flux:icon icon="calendar" class="size-3" />
                    Añada: {{ $campLabel }}
                    <button wire:click="$set('campaignFilter', '')" class="ml-0.5 p-0.5 rounded-full hover:bg-agro-200 transition-colors">
                        <flux:icon icon="x-mark" class="size-3" />
                    </button>
                </span>
            @endif
            @if($viticulturistFilter)
                @php $viticLabel = $linkedViticulturists->firstWhere('id', $viticulturistFilter)?->name ?? $viticulturistFilter; @endphp
                <span class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                    <flux:icon icon="user" class="size-3" />
                    {{ $viticLabel }}
                    <button wire:click="$set('viticulturistFilter', '')" class="ml-0.5 p-0.5 rounded-full hover:bg-agro-200 transition-colors">
                        <flux:icon icon="x-mark" class="size-3" />
                    </button>
                </span>
            @endif
            @if($varietyFilter)
                <span class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                    <flux:icon icon="tag" class="size-3" />
                    {{ $varietyFilter }}
                    <button wire:click="$set('varietyFilter', '')" class="ml-0.5 p-0.5 rounded-full hover:bg-agro-200 transition-colors">
                        <flux:icon icon="x-mark" class="size-3" />
                    </button>
                </span>
            @endif
            @if($alertFilter)
                @php $alertLabel = $alertFilter === 'exceeded' ? '⚠ Superados' : 'En riesgo (≥80%)'; @endphp
                <span class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 bg-red-50 text-red-700 text-xs font-medium rounded-full border border-red-200">
                    <flux:icon icon="bell-alert" class="size-3" />
                    {{ $alertLabel }}
                    <button wire:click="$set('alertFilter', '')" class="ml-0.5 p-0.5 rounded-full hover:bg-red-100 transition-colors">
                        <flux:icon icon="x-mark" class="size-3" />
                    </button>
                </span>
            @endif
            <button
                wire:click="$set('viticulturistFilter', ''); $set('varietyFilter', ''); $set('alertFilter', '')"
                class="text-xs text-zinc-400 hover:text-zinc-600 transition-colors">
                Limpiar todo
            </button>
        </div>
    @endif

    {{-- Skeleton durante carga --}}
    <div wire:loading wire:target="campaignFilter, viticulturistFilter, varietyFilter, alertFilter">
        <x-agro.card>
            <div class="space-y-3">
                @for($i = 0; $i < 8; $i++)
                    <div class="h-10 bg-zinc-100 rounded-lg animate-pulse"></div>
                @endfor
            </div>
        </x-agro.card>
    </div>

    {{-- Tabla comparativa --}}
    <div wire:loading.remove wire:target="campaignFilter, viticulturistFilter, varietyFilter, alertFilter">
        @if($rows->isEmpty())
            <x-agro.empty-state
                icon="chart-bar"
                title="Sin datos para esta campaña"
                description="Crea previsiones o registra recepciones para ver el cuadro de mando."
            >
                <x-slot:action>
                    <flux:button variant="primary" icon="plus" href="{{ route('winery.harvest-forecasts.create') }}" wire:navigate>
                        Nueva previsión
                    </flux:button>
                </x-slot:action>
            </x-agro.empty-state>
        @else
            <x-agro.card>
                <x-agro.data-table :headers="['Viticultor', 'Variedad / Parcela', 'PAC límite', 'Aforo viticultor', 'Previsión bodega', 'Recibido', 'Ejecución', 'Estado']">
                    @foreach($rows as $row)
                        @php
                            $color = $row['exceeded'] || $row['exceeded_pac']
                                ? 'red'
                                : ($row['at_risk'] ? 'amber' : 'agro');
                        @endphp
                        <x-agro.table-row wire:key="row-{{ $row['key'] }}">

                            <x-agro.table-cell>
                                <span class="font-medium text-zinc-900">{{ $row['viticulturist']?->name ?? '—' }}</span>
                            </x-agro.table-cell>

                            <x-agro.table-cell>
                                <div class="font-medium text-zinc-900">{{ $row['variety'] }}</div>
                                <div class="text-xs text-zinc-400">
                                    {{ $row['plot'] }}
                                    @if($row['area']) · {{ number_format($row['area'], 2) }} ha @endif
                                </div>
                            </x-agro.table-cell>

                            <x-agro.table-cell>
                                @if($row['pac_limit'] !== null)
                                    <span class="text-blue-700 font-medium">{{ number_format($row['pac_limit'], 0) }} kg</span>
                                @else
                                    <span class="text-zinc-400">Sin límite</span>
                                @endif
                            </x-agro.table-cell>

                            <x-agro.table-cell>
                                @if($row['vitic_estimate'] !== null)
                                    <span class="text-violet-700 font-medium">{{ number_format($row['vitic_estimate'], 0) }} kg</span>
                                @else
                                    <span class="text-zinc-300">—</span>
                                @endif
                            </x-agro.table-cell>

                            <x-agro.table-cell>
                                @if($row['forecast_kg'] !== null)
                                    <div class="font-medium {{ $row['forecast_status'] === 'confirmed' ? 'text-agro-700' : 'text-zinc-500' }}">
                                        {{ number_format($row['forecast_kg'], 0) }} kg
                                    </div>
                                    @if($row['forecast_status'] === 'draft')
                                        <div class="text-xs text-amber-500">Borrador</div>
                                    @endif
                                @else
                                    <span class="text-zinc-300">—</span>
                                @endif
                            </x-agro.table-cell>

                            <x-agro.table-cell>
                                <span class="font-semibold {{ $row['exceeded'] || $row['exceeded_pac'] ? 'text-red-600' : 'text-zinc-900' }}">
                                    {{ number_format($row['received_kg'], 0) }} kg
                                </span>
                                @if($row['remaining'] !== null && $row['received_kg'] > 0)
                                    <div class="text-xs text-zinc-400">Resta: {{ number_format($row['remaining'], 0) }} kg</div>
                                @endif
                            </x-agro.table-cell>

                            <x-agro.table-cell>
                                @if($row['pct_op_limit'] !== null)
                                    <div class="flex items-center gap-2">
                                        <x-agro.progress-bar :percentage="min($row['pct_op_limit'], 100)" :color="$color" class="w-20" />
                                        <span class="text-xs font-medium {{ $row['exceeded'] ? 'text-red-600' : ($row['at_risk'] ? 'text-amber-600' : 'text-zinc-600') }}">
                                            {{ $row['pct_op_limit'] }}%
                                        </span>
                                    </div>
                                    @if($row['exceeded_pac'] && !$row['exceeded'])
                                        <div class="text-xs text-orange-500 mt-0.5">PAC: {{ $row['pct_pac'] }}%</div>
                                    @endif
                                @elseif($row['received_kg'] > 0)
                                    <span class="text-xs text-zinc-400">Sin límite definido</span>
                                @else
                                    <span class="text-zinc-300">—</span>
                                @endif
                            </x-agro.table-cell>

                            <x-agro.table-cell>
                                @if($row['exceeded'] || $row['exceeded_pac'])
                                    <x-agro.status-badge color="red" label="⚠ Superado" />
                                @elseif($row['at_risk'])
                                    <x-agro.status-badge color="amber" label="En riesgo" />
                                @elseif($row['received_kg'] > 0)
                                    <x-agro.status-badge color="agro" label="En curso" />
                                @elseif($row['forecast_kg'] !== null)
                                    <x-agro.status-badge color="zinc" label="Pendiente" />
                                @else
                                    <x-agro.status-badge color="zinc" label="Sin actividad" />
                                @endif
                            </x-agro.table-cell>

                        </x-agro.table-row>
                    @endforeach

                    {{-- Fila de totales --}}
                    <x-agro.table-row class="bg-zinc-50 font-semibold border-t-2 border-zinc-200">
                        <x-agro.table-cell>
                            <span class="text-zinc-700">TOTALES</span>
                            <div class="text-xs font-normal text-zinc-400">{{ $stats['viticulturists'] }} viticultores · {{ $stats['total_plantings'] }} plantaciones</div>
                        </x-agro.table-cell>
                        <x-agro.table-cell></x-agro.table-cell>
                        <x-agro.table-cell>
                            @if($stats['total_pac_kg'])
                                <span class="text-blue-700">{{ number_format($stats['total_pac_kg'], 0) }} kg</span>
                            @endif
                        </x-agro.table-cell>
                        <x-agro.table-cell>
                            @if($stats['total_vitic_est_kg'])
                                <span class="text-violet-700">{{ number_format($stats['total_vitic_est_kg'], 0) }} kg</span>
                            @endif
                        </x-agro.table-cell>
                        <x-agro.table-cell>
                            @if($stats['total_forecast_kg'])
                                <span class="text-agro-700">{{ number_format($stats['total_forecast_kg'], 0) }} kg</span>
                            @endif
                        </x-agro.table-cell>
                        <x-agro.table-cell>
                            <span class="text-zinc-900">{{ number_format($stats['total_received_kg'], 0) }} kg</span>
                        </x-agro.table-cell>
                        <x-agro.table-cell>
                            @if($stats['total_forecast_kg'] > 0)
                                @php $totalPct = round(($stats['total_received_kg'] / $stats['total_forecast_kg']) * 100, 1); @endphp
                                <div class="flex items-center gap-2">
                                    <x-agro.progress-bar :percentage="min($totalPct, 100)" color="agro" class="w-20" />
                                    <span class="text-xs font-semibold text-zinc-700">{{ $totalPct }}%</span>
                                </div>
                            @endif
                        </x-agro.table-cell>
                        <x-agro.table-cell></x-agro.table-cell>
                    </x-agro.table-row>

                </x-agro.data-table>
            </x-agro.card>
        @endif
    </div>

    {{-- Modal Filtros --}}
    <x-agro.modal name="summary-filters" maxWidth="sm">
        <div class="px-6 py-4 border-b border-zinc-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-agro-100 rounded-lg flex items-center justify-center">
                        <flux:icon icon="adjustments-horizontal" class="size-4 text-agro-600" />
                    </div>
                    <h3 class="text-base font-semibold text-zinc-900">Filtros</h3>
                </div>
                <flux:button x-on:click="$dispatch('close-modal', 'summary-filters')" variant="ghost" size="sm" icon="x-mark" />
            </div>
        </div>

        <div class="px-6 py-5 space-y-5">
            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Campaña</label>
                <flux:select wire:model.live="campaignFilter">
                    <option value="">Todas las campañas</option>
                    @foreach($campaigns as $c)
                        <option value="{{ $c->id }}">{{ $c->year }}</option>
                    @endforeach
                </flux:select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Viticultor</label>
                <flux:select wire:model.live="viticulturistFilter">
                    <option value="">Todos</option>
                    @foreach($linkedViticulturists as $v)
                        <option value="{{ $v->id }}">{{ $v->name }}</option>
                    @endforeach
                </flux:select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Variedad</label>
                <flux:select wire:model.live="varietyFilter">
                    <option value="">Todas</option>
                    @foreach($varieties as $variety)
                        <option value="{{ $variety }}">{{ $variety }}</option>
                    @endforeach
                </flux:select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Alertas</label>
                <flux:select wire:model.live="alertFilter">
                    <option value="">Todas</option>
                    <option value="exceeded">Superados</option>
                    <option value="at_risk">En riesgo (≥80%)</option>
                </flux:select>
            </div>
        </div>

        <div class="px-6 py-4 border-t border-zinc-200 flex items-center justify-between">
            @if($filterCount > 0)
                <button
                    wire:click="$set('viticulturistFilter', ''); $set('varietyFilter', ''); $set('alertFilter', '')"
                    class="text-sm text-zinc-400 hover:text-zinc-600 transition-colors">
                    Limpiar filtros
                </button>
            @else
                <span></span>
            @endif
            <flux:button x-on:click="$dispatch('close-modal', 'summary-filters')" variant="primary">
                Aplicar
            </flux:button>
        </div>
    </x-agro.modal>

</div>
