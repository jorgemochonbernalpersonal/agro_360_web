<div class="space-y-6 animate-fade-in">

    {{-- Header --}}
    <x-agro.page-header
        title="Comparativa entre Campañas"
        description="Compara rendimientos, calidad, actividades y costes entre campañas para tomar mejores decisiones."
        icon="chart-bar-square"
    />

    {{-- Selectores de campaña --}}
    <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm p-5">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <flux:field>
                <flux:label>Campaña A</flux:label>
                <flux:select wire:model.live="campaignA">
                    <option value="">Seleccionar...</option>
                    @foreach ($campaigns as $c)
                        <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->year }})</option>
                    @endforeach
                </flux:select>
            </flux:field>
            <flux:field>
                <flux:label>Campaña B</flux:label>
                <flux:select wire:model.live="campaignB">
                    <option value="">Seleccionar...</option>
                    @foreach ($campaigns as $c)
                        <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->year }})</option>
                    @endforeach
                </flux:select>
            </flux:field>
            <flux:field>
                <flux:label>Filtrar por parcela</flux:label>
                <flux:select wire:model.live="filterPlot">
                    <option value="">Todas las parcelas</option>
                    @foreach ($plots as $plot)
                        <option value="{{ $plot->id }}">{{ $plot->name }}</option>
                    @endforeach
                </flux:select>
            </flux:field>
        </div>
    </div>

    @if (!$campaignA && !$campaignB)
        <x-agro.empty-state
            icon="chart-bar-square"
            title="Selecciona al menos una campaña"
            description="Elige dos campañas para comparar sus resultados lado a lado."
        />
    @else
        {{-- Comparativa principal --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- Campaña A --}}
            @if ($dataA && $campaignAModel)
                <div class="space-y-4">
                    <h2 class="text-lg font-bold text-zinc-900 flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-blue-500"></span>
                        {{ $campaignAModel->name }} ({{ $campaignAModel->year }})
                    </h2>
                    @include('livewire.viticulturist.campaign-comparison._campaign-card', ['data' => $dataA, 'color' => 'blue'])
                </div>
            @endif

            {{-- Campaña B --}}
            @if ($dataB && $campaignBModel)
                <div class="space-y-4">
                    <h2 class="text-lg font-bold text-zinc-900 flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                        {{ $campaignBModel->name }} ({{ $campaignBModel->year }})
                    </h2>
                    @include('livewire.viticulturist.campaign-comparison._campaign-card', ['data' => $dataB, 'color' => 'emerald'])
                </div>
            @endif

        </div>

        {{-- Tabla comparativa por parcela (si ambas campañas seleccionadas) --}}
        @if ($dataA && $dataB)
            <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-zinc-100">
                    <h3 class="font-semibold text-zinc-900">Desglose por Parcela</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-zinc-50 text-zinc-500 text-xs uppercase tracking-wide">
                                <th class="text-left px-5 py-3 font-semibold">Parcela</th>
                                <th class="text-right px-3 py-3 font-semibold text-blue-600">Kg (A)</th>
                                <th class="text-right px-3 py-3 font-semibold text-emerald-600">Kg (B)</th>
                                <th class="text-right px-3 py-3 font-semibold">Diferencia</th>
                                <th class="text-right px-3 py-3 font-semibold text-blue-600">Rend. (A)</th>
                                <th class="text-right px-3 py-3 font-semibold text-emerald-600">Rend. (B)</th>
                                <th class="text-right px-3 py-3 font-semibold text-blue-600">Baumé (A)</th>
                                <th class="text-right px-5 py-3 font-semibold text-emerald-600">Baumé (B)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                            @php
                                $allPlotIds = $dataA['perPlot']->pluck('plot_id')->merge($dataB['perPlot']->pluck('plot_id'))->unique();
                            @endphp
                            @forelse ($allPlotIds as $plotId)
                                @php
                                    $a = $dataA['perPlot']->firstWhere('plot_id', $plotId);
                                    $b = $dataB['perPlot']->firstWhere('plot_id', $plotId);
                                    $plotName = $a?->plot_name ?? $b?->plot_name ?? '—';
                                    $kgA = $a?->total_kg ?? 0;
                                    $kgB = $b?->total_kg ?? 0;
                                    $diff = $kgB - $kgA;
                                @endphp
                                <tr class="hover:bg-zinc-50 transition-colors">
                                    <td class="px-5 py-3 font-medium text-zinc-900">{{ $plotName }}</td>
                                    <td class="px-3 py-3 text-right tabular-nums">{{ number_format($kgA, 0, ',', '.') }}</td>
                                    <td class="px-3 py-3 text-right tabular-nums">{{ number_format($kgB, 0, ',', '.') }}</td>
                                    <td class="px-3 py-3 text-right tabular-nums font-medium {{ $diff > 0 ? 'text-green-600' : ($diff < 0 ? 'text-red-600' : 'text-zinc-400') }}">
                                        {{ $diff > 0 ? '+' : '' }}{{ number_format($diff, 0, ',', '.') }}
                                    </td>
                                    <td class="px-3 py-3 text-right tabular-nums">{{ $a ? number_format($a->avg_yield, 0, ',', '.') : '—' }}</td>
                                    <td class="px-3 py-3 text-right tabular-nums">{{ $b ? number_format($b->avg_yield, 0, ',', '.') : '—' }}</td>
                                    <td class="px-3 py-3 text-right tabular-nums">{{ $a && $a->avg_baume ? number_format($a->avg_baume, 1, ',', '.') : '—' }}</td>
                                    <td class="px-5 py-3 text-right tabular-nums">{{ $b && $b->avg_baume ? number_format($b->avg_baume, 1, ',', '.') : '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-5 py-8 text-center text-zinc-400">Sin datos de parcelas para comparar</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @endif
</div>
