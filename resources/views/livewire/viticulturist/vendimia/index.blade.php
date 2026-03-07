<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Mis Entregas a Bodega — {{ $vintageYear }}"
        description="Compara lo que registraste en tu cuaderno con lo que la bodega ha recibido de ti."
    >
        <x-slot:actions>
            <flux:button variant="ghost" icon="pencil-square" href="{{ route('viticulturist.digital-notebook') }}" wire:navigate>
                Cuaderno Digital
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
        <x-agro.stat-card
            label="Aforo estimado"
            :value="$stats['total_estimated'] ? number_format($stats['total_estimated'], 0) . ' kg' : '—'"
            icon="calculator"
            color="violet"
        />
        <x-agro.stat-card
            label="Mi cosecha (cuaderno)"
            :value="number_format($stats['total_my_harvest'], 0) . ' kg'"
            icon="pencil-square"
            color="agro"
        />
        <x-agro.stat-card
            label="Recibido por bodega"
            :value="number_format($stats['total_received'], 0) . ' kg'"
            icon="archive-box-arrow-down"
            color="blue"
        />
        <x-agro.stat-card
            label="Coinciden"
            :value="$stats['ok_count']"
            icon="check-circle"
            color="{{ $stats['ok_count'] > 0 ? 'agro' : 'zinc' }}"
        />
        <x-agro.stat-card
            label="Con diferencia"
            :value="$stats['discrepancy_count'] + $stats['not_received_count']"
            icon="exclamation-triangle"
            color="{{ ($stats['discrepancy_count'] + $stats['not_received_count']) > 0 ? 'amber' : 'zinc' }}"
        />
    </div>

    {{-- Filtro de añada --}}
    <x-agro.filter-bar :active-count="0">
        <x-agro.filter-select wire:model.live="vintageFilter" label="Añada">
            @foreach($campaigns as $c)
                <option value="{{ $c->year }}">{{ $c->year }}</option>
            @endforeach
        </x-agro.filter-select>
    </x-agro.filter-bar>

    {{-- Tabla --}}
    @if($rows->isEmpty())
        <x-agro.empty-state
            icon="archive-box-arrow-down"
            title="Sin entregas registradas para {{ $vintageYear }}"
            description="Cuando una bodega registre la recepción de tu uva o cree una previsión para tus plantaciones, aparecerá aquí."
        >
            <flux:button variant="primary" icon="pencil-square" href="{{ route('viticulturist.digital-notebook') }}" wire:navigate>
                Ir al Cuaderno Digital
            </flux:button>
        </x-agro.empty-state>
    @else
        <x-agro.card>
            <x-agro.data-table :headers="['Variedad / Parcela', 'Bodega', 'Aforo estimado', 'Previsión bodega', 'Mi cuaderno', 'Recibido bodega', 'Diferencia', 'Estado']">
                @foreach($rows as $row)
                    <x-agro.table-row wire:key="row-{{ $row['key'] }}">

                        {{-- Variedad / Parcela --}}
                        <x-agro.table-cell>
                            <div class="font-medium text-zinc-900">{{ $row['variety'] }}</div>
                            <div class="text-xs text-zinc-400">
                                {{ $row['plot'] }}
                                @if($row['area']) · {{ number_format($row['area'], 2) }} ha @endif
                            </div>
                        </x-agro.table-cell>

                        {{-- Bodega --}}
                        <x-agro.table-cell>
                            <span class="text-zinc-700">{{ $row['winery']?->name ?? '—' }}</span>
                        </x-agro.table-cell>

                        {{-- Aforo estimado propio --}}
                        <x-agro.table-cell>
                            @if($row['estimated_kg'] !== null)
                                <span class="text-violet-700 font-medium">{{ number_format($row['estimated_kg'], 0) }} kg</span>
                            @else
                                <span class="text-zinc-300">—</span>
                            @endif
                        </x-agro.table-cell>

                        {{-- Previsión bodega --}}
                        <x-agro.table-cell>
                            @if($row['forecast_kg'] !== null)
                                <span class="text-blue-700 font-medium">{{ number_format($row['forecast_kg'], 0) }} kg</span>
                                @if($row['forecast_status'] === 'draft')
                                    <div class="text-xs text-amber-500">Borrador</div>
                                @endif
                            @else
                                <span class="text-zinc-300">—</span>
                            @endif
                        </x-agro.table-cell>

                        {{-- Mi cuaderno --}}
                        <x-agro.table-cell>
                            @if($row['my_harvest_kg'] > 0)
                                <span class="font-semibold text-agro-700">{{ number_format($row['my_harvest_kg'], 0) }} kg</span>
                            @else
                                <span class="text-zinc-300">—</span>
                            @endif
                        </x-agro.table-cell>

                        {{-- Recibido bodega --}}
                        <x-agro.table-cell>
                            @if($row['received_kg'] > 0)
                                <span class="font-semibold text-blue-700">{{ number_format($row['received_kg'], 0) }} kg</span>
                            @else
                                <span class="text-zinc-300">—</span>
                            @endif
                        </x-agro.table-cell>

                        {{-- Diferencia --}}
                        <x-agro.table-cell>
                            @if($row['discrepancy_kg'] !== null && $row['discrepancy_kg'] > 0)
                                <span class="font-medium {{ $row['discrepancy_pct'] > 5 ? 'text-amber-600' : 'text-zinc-500' }}">
                                    {{ number_format($row['discrepancy_kg'], 0) }} kg
                                </span>
                                @if($row['discrepancy_pct'] !== null)
                                    <div class="text-xs text-zinc-400">{{ $row['discrepancy_pct'] }}%</div>
                                @endif
                            @elseif($row['status'] === 'ok')
                                <span class="text-agro-600 text-sm">✓ Coincide</span>
                            @else
                                <span class="text-zinc-300">—</span>
                            @endif
                        </x-agro.table-cell>

                        {{-- Estado --}}
                        <x-agro.table-cell>
                            @if($row['status'] === 'ok')
                                <x-agro.status-badge color="agro" label="Coincide" />
                            @elseif($row['status'] === 'discrepancy')
                                <x-agro.status-badge color="amber" label="Diferencia" />
                            @elseif($row['status'] === 'not_received')
                                <x-agro.status-badge color="zinc" label="Sin recibir" />
                            @elseif($row['status'] === 'received_only')
                                <x-agro.status-badge color="blue" label="Solo bodega" />
                            @else
                                <x-agro.status-badge color="zinc" label="Pendiente" />
                            @endif
                        </x-agro.table-cell>

                    </x-agro.table-row>
                @endforeach

                {{-- Fila totales --}}
                <x-agro.table-row class="bg-zinc-50 font-semibold border-t-2 border-zinc-200">
                    <x-agro.table-cell>
                        <span class="text-zinc-700">TOTALES</span>
                        <div class="text-xs font-normal text-zinc-400">{{ $stats['total_plantings'] }} plantaciones</div>
                    </x-agro.table-cell>
                    <x-agro.table-cell></x-agro.table-cell>
                    <x-agro.table-cell>
                        @if($stats['total_estimated'])
                            <span class="text-violet-700">{{ number_format($stats['total_estimated'], 0) }} kg</span>
                        @endif
                    </x-agro.table-cell>
                    <x-agro.table-cell>
                        @if($stats['total_forecast'])
                            <span class="text-blue-700">{{ number_format($stats['total_forecast'], 0) }} kg</span>
                        @endif
                    </x-agro.table-cell>
                    <x-agro.table-cell>
                        <span class="text-agro-700">{{ number_format($stats['total_my_harvest'], 0) }} kg</span>
                    </x-agro.table-cell>
                    <x-agro.table-cell>
                        <span class="text-blue-700">{{ number_format($stats['total_received'], 0) }} kg</span>
                    </x-agro.table-cell>
                    <x-agro.table-cell>
                        @php
                            $totalDiff = abs($stats['total_my_harvest'] - $stats['total_received']);
                        @endphp
                        @if($totalDiff > 0)
                            <span class="text-amber-600">{{ number_format($totalDiff, 0) }} kg</span>
                        @else
                            <span class="text-agro-600">✓</span>
                        @endif
                    </x-agro.table-cell>
                    <x-agro.table-cell></x-agro.table-cell>
                </x-agro.table-row>

            </x-agro.data-table>
        </x-agro.card>

        {{-- Leyenda --}}
        <div class="flex flex-wrap gap-4 text-xs text-zinc-500 px-1">
            <span><x-agro.status-badge color="agro" label="Coincide" /> Mi cuaderno y la bodega registran cantidades similares (diferencia &lt;5%)</span>
            <span><x-agro.status-badge color="amber" label="Diferencia" /> Discrepancia &gt;5% entre cuaderno y bodega</span>
            <span><x-agro.status-badge color="zinc" label="Sin recibir" /> Tienes entrada en cuaderno pero la bodega aún no ha recibido</span>
            <span><x-agro.status-badge color="blue" label="Solo bodega" /> La bodega recibió pero no tienes entrada en cuaderno</span>
        </div>
    @endif

</div>
