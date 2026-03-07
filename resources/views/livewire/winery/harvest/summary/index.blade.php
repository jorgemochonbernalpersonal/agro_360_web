<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Cuadro de Mando — Vendimia {{ $vintageYear }}"
        description="Comparativa en tiempo real: aforo viticultor · previsión bodega · recibido real."
    >
        <x-slot:actions>
            <flux:button variant="ghost" icon="calculator" href="{{ route('winery.vitic-estimates.index') }}" wire:navigate>
                Aforos viticultores
            </flux:button>
            <flux:button variant="ghost" icon="clipboard-document-list" href="{{ route('winery.harvest-forecasts.index') }}" wire:navigate>
                Previsiones
            </flux:button>
            <flux:button variant="primary" icon="archive-box-arrow-down" href="{{ route('winery.grape-reception.index') }}" wire:navigate>
                Recepciones
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <x-agro.stat-card
            label="Total recibido"
            :value="number_format($stats['total_received_kg'], 0) . ' kg'"
            icon="scale"
            color="agro"
        />
        <x-agro.stat-card
            label="Previsión bodega"
            :value="$stats['total_forecast_kg'] ? number_format($stats['total_forecast_kg'], 0) . ' kg' : '—'"
            icon="clipboard-document-list"
            color="zinc"
        />
        <x-agro.stat-card
            label="Alertas superado"
            :value="$stats['exceeded_count']"
            icon="exclamation-triangle"
            color="{{ $stats['exceeded_count'] > 0 ? 'red' : 'zinc' }}"
        />
        <x-agro.stat-card
            label="En riesgo (≥80%)"
            :value="$stats['at_risk_count']"
            icon="bell-alert"
            color="{{ $stats['at_risk_count'] > 0 ? 'amber' : 'zinc' }}"
        />
    </div>

    {{-- Filtros --}}
    <x-agro.filter-bar :active-count="collect([$campaignFilter, $viticulturistFilter, $varietyFilter, $alertFilter])->filter()->count()">
        <x-agro.filter-select wire:model.live="campaignFilter" label="Campaña">
            <option value="">Todas las campañas</option>
            @foreach($campaigns as $c)
                <option value="{{ $c->id }}">{{ $c->year }}</option>
            @endforeach
        </x-agro.filter-select>
        <x-agro.filter-select wire:model.live="viticulturistFilter" label="Viticultor">
            <option value="">Todos</option>
            @foreach($linkedViticulturists as $v)
                <option value="{{ $v->id }}">{{ $v->name }}</option>
            @endforeach
        </x-agro.filter-select>
        <x-agro.filter-select wire:model.live="varietyFilter" label="Variedad">
            <option value="">Todas</option>
            @foreach($varieties as $variety)
                <option value="{{ $variety }}">{{ $variety }}</option>
            @endforeach
        </x-agro.filter-select>
        <x-agro.filter-select wire:model.live="alertFilter" label="Alertas">
            <option value="">Todas</option>
            <option value="exceeded">Superados</option>
            <option value="at_risk">En riesgo (≥80%)</option>
        </x-agro.filter-select>
    </x-agro.filter-bar>

    {{-- Tabla comparativa --}}
    @if($rows->isEmpty())
        <x-agro.empty-state
            icon="chart-bar"
            title="Sin datos para esta campaña"
            description="Crea previsiones o registra recepciones para ver el cuadro de mando."
        >
            <flux:button variant="primary" icon="plus" href="{{ route('winery.harvest-forecasts.create') }}" wire:navigate>
                Nueva previsión
            </flux:button>
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

                        {{-- Viticultor --}}
                        <x-agro.table-cell>
                            <span class="font-medium text-zinc-900">
                                {{ $row['viticulturist']?->name ?? '—' }}
                            </span>
                        </x-agro.table-cell>

                        {{-- Variedad / Parcela --}}
                        <x-agro.table-cell>
                            <div class="font-medium text-zinc-900">{{ $row['variety'] }}</div>
                            <div class="text-xs text-zinc-400">
                                {{ $row['plot'] }}
                                @if($row['area']) · {{ number_format($row['area'], 2) }} ha @endif
                            </div>
                        </x-agro.table-cell>

                        {{-- PAC límite --}}
                        <x-agro.table-cell>
                            @if($row['pac_limit'] !== null)
                                <span class="text-blue-700 font-medium">{{ number_format($row['pac_limit'], 0) }} kg</span>
                            @else
                                <span class="text-zinc-400">Sin límite</span>
                            @endif
                        </x-agro.table-cell>

                        {{-- Aforo viticultor --}}
                        <x-agro.table-cell>
                            @if($row['vitic_estimate'] !== null)
                                <span class="text-violet-700 font-medium">{{ number_format($row['vitic_estimate'], 0) }} kg</span>
                            @else
                                <span class="text-zinc-300">—</span>
                            @endif
                        </x-agro.table-cell>

                        {{-- Previsión bodega --}}
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

                        {{-- Recibido --}}
                        <x-agro.table-cell>
                            <span class="font-semibold {{ $row['exceeded'] || $row['exceeded_pac'] ? 'text-red-600' : 'text-zinc-900' }}">
                                {{ number_format($row['received_kg'], 0) }} kg
                            </span>
                            @if($row['remaining'] !== null && $row['received_kg'] > 0)
                                <div class="text-xs text-zinc-400">
                                    Resta: {{ number_format($row['remaining'], 0) }} kg
                                </div>
                            @endif
                        </x-agro.table-cell>

                        {{-- Ejecución --}}
                        <x-agro.table-cell>
                            @if($row['pct_op_limit'] !== null)
                                <div class="flex items-center gap-2">
                                    <x-agro.progress-bar
                                        :percentage="min($row['pct_op_limit'], 100)"
                                        :color="$color"
                                        class="w-20"
                                    />
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

                        {{-- Estado --}}
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
