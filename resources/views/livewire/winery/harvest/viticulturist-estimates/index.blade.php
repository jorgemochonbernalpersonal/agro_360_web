<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Aforos de Viticultores"
        description="Estimaciones de rendimiento declaradas por tus viticultores vinculados."
    >
        <x-slot:actions>
            <flux:button variant="ghost" icon="chart-bar" href="{{ route('winery.harvest-summary.index') }}" wire:navigate>
                Cuadro de mando
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
        <x-agro.stat-card
            label="Total aforos"
            :value="$stats['total']"
            icon="calculator"
            color="violet"
        />
        <x-agro.stat-card
            label="Confirmados"
            :value="$stats['confirmed']"
            icon="check-circle"
            color="agro"
        />
        <x-agro.stat-card
            label="Kg estimados (confirmados)"
            :value="$stats['total_kg'] ? number_format($stats['total_kg'], 0) . ' kg' : '—'"
            icon="scale"
            color="blue"
        />
    </div>

    {{-- Filtros --}}
    <x-agro.filter-bar :active-count="collect([$viticulturistFilter, $vintageFilter, $statusFilter, $roundFilter])->filter()->count()">
        <x-agro.filter-select wire:model.live="viticulturistFilter" label="Viticultor">
            <option value="">Todos</option>
            @foreach($linkedViticulturists as $v)
                <option value="{{ $v->id }}">{{ $v->name }}</option>
            @endforeach
        </x-agro.filter-select>
        <x-agro.filter-select wire:model.live="vintageFilter" label="Añada">
            <option value="">Todas</option>
            @foreach($vintages as $year)
                <option value="{{ $year }}">{{ $year }}</option>
            @endforeach
        </x-agro.filter-select>
        <x-agro.filter-select wire:model.live="roundFilter" label="Ronda">
            <option value="">Todas las rondas</option>
            @foreach($rounds as $num => $label)
                <option value="{{ $num }}">{{ $label }}</option>
            @endforeach
        </x-agro.filter-select>
        <x-agro.filter-select wire:model.live="statusFilter" label="Estado">
            <option value="">Todos</option>
            <option value="confirmed">Confirmado</option>
            <option value="draft">Borrador</option>
            <option value="archived">Archivado</option>
        </x-agro.filter-select>
    </x-agro.filter-bar>

    {{-- Tabla --}}
    @if($estimates->isEmpty())
        <x-agro.empty-state
            icon="calculator"
            title="Sin aforos para estos filtros"
            description="Tus viticultores aún no han registrado estimaciones de rendimiento confirmadas."
        />
    @else
        <x-agro.card>
            <x-agro.data-table :headers="['Viticultor', 'Variedad / Parcela', 'Añada', 'Ronda', 'Método', 'Kg/ha estimados', 'Total estimado', 'Alcohol', 'Sanidad', 'Estado']">
                @foreach($estimates as $est)
                    <x-agro.table-row wire:key="est-{{ $est->id }}">

                        {{-- Viticultor --}}
                        <x-agro.table-cell>
                            <span class="font-medium text-zinc-900">
                                {{ $est->plotPlanting?->plot?->viticulturist?->name ?? '—' }}
                            </span>
                        </x-agro.table-cell>

                        {{-- Variedad / Parcela --}}
                        <x-agro.table-cell>
                            <div class="font-medium text-zinc-900">
                                {{ $est->plotPlanting?->grapeVariety?->name ?? $est->plotPlanting?->name ?? '—' }}
                            </div>
                            <div class="text-xs text-zinc-400">
                                {{ $est->plotPlanting?->plot?->name ?? '—' }}
                                @if($est->plotPlanting?->area_planted)
                                    · {{ number_format($est->plotPlanting->area_planted, 2) }} ha
                                @endif
                            </div>
                        </x-agro.table-cell>

                        {{-- Añada --}}
                        <x-agro.table-cell>
                            <span class="text-zinc-700">
                                {{ $est->vintage ?? $est->campaign?->year ?? '—' }}
                            </span>
                        </x-agro.table-cell>

                        {{-- Ronda --}}
                        <x-agro.table-cell>
                            <span class="text-violet-700 font-medium text-sm">
                                {{ $est->round_label }}
                            </span>
                            <div class="text-xs text-zinc-400">{{ $est->estimation_date?->format('d/m/Y') }}</div>
                        </x-agro.table-cell>

                        {{-- Método --}}
                        <x-agro.table-cell>
                            <span class="text-zinc-600 text-sm capitalize">
                                {{ match($est->estimation_method) {
                                    'visual'     => 'Visual',
                                    'sampling'   => 'Muestreo',
                                    'historical' => 'Histórico',
                                    'satellite'  => 'Satélite',
                                    default      => $est->estimation_method ?? '—',
                                } }}
                            </span>
                        </x-agro.table-cell>

                        {{-- Kg/ha estimados --}}
                        <x-agro.table-cell>
                            @if($est->estimated_yield_per_hectare)
                                <span class="text-zinc-900">{{ number_format($est->estimated_yield_per_hectare, 0) }} kg/ha</span>
                            @else
                                <span class="text-zinc-300">—</span>
                            @endif
                        </x-agro.table-cell>

                        {{-- Total estimado --}}
                        <x-agro.table-cell>
                            @if($est->estimated_total_yield)
                                <span class="font-semibold text-violet-700">{{ number_format($est->estimated_total_yield, 0) }} kg</span>
                                @if($est->auto_calculated_yield && abs($est->auto_calculated_yield - $est->estimated_total_yield) > 10)
                                    <div class="text-xs text-zinc-400">Auto: {{ number_format($est->auto_calculated_yield, 0) }} kg</div>
                                @endif
                            @else
                                <span class="text-zinc-300">—</span>
                            @endif
                        </x-agro.table-cell>

                        {{-- Alcohol --}}
                        <x-agro.table-cell>
                            @if($est->potential_alcohol)
                                <span class="text-zinc-700 text-sm font-medium">{{ number_format($est->potential_alcohol, 1) }}°</span>
                            @else
                                <span class="text-zinc-300">—</span>
                            @endif
                        </x-agro.table-cell>

                        {{-- Sanidad --}}
                        <x-agro.table-cell>
                            @if($est->health_status)
                                @php
                                    $sanColors = [
                                        'excellent' => 'agro', 'good' => 'agro',
                                        'botrytis_light' => 'amber', 'oidium_light' => 'amber',
                                        'botrytis_moderate' => 'red', 'oidium_moderate' => 'red',
                                        'mixed' => 'amber', 'poor' => 'red',
                                    ];
                                    $sanColor = $sanColors[$est->health_status] ?? 'zinc';
                                    $sanLabel = \App\Models\EstimatedYield::HEALTH_STATUSES[$est->health_status] ?? $est->health_status;
                                @endphp
                                <x-agro.status-badge :color="$sanColor" :label="$sanLabel" />
                            @elseif($est->health_percentage !== null)
                                <span class="text-zinc-500 text-xs">{{ number_format($est->health_percentage, 0) }}%</span>
                            @else
                                <span class="text-zinc-300">—</span>
                            @endif
                            @if($est->other_wineries)
                                <div class="text-xs text-amber-600 mt-0.5">Entrega múltiple</div>
                            @endif
                        </x-agro.table-cell>

                        {{-- Estado --}}
                        <x-agro.table-cell>
                            @if($est->status === 'confirmed')
                                <x-agro.status-badge color="agro" label="Confirmado" />
                            @elseif($est->status === 'draft')
                                <x-agro.status-badge color="amber" label="Borrador" />
                            @else
                                <x-agro.status-badge color="zinc" label="Archivado" />
                            @endif
                        </x-agro.table-cell>

                    </x-agro.table-row>
                @endforeach
            </x-agro.data-table>
        </x-agro.card>

        <x-agro.pagination :paginator="$estimates" />
    @endif

</div>
