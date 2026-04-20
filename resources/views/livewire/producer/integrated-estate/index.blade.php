<div>
    <x-agro.page-header
        title="Panel de Finca Integral"
        description="Vista unificada de parcelas, plantaciones, fenología y actividades de campo"
        icon="map"
    />

    {{-- Filtros --}}
    <div class="flex flex-wrap items-end gap-4 mb-6">
        <flux:field class="min-w-[200px]">
            <flux:label>Campaña</flux:label>
            <flux:select wire:model.live="filterCampaign">
                <option value="">Seleccionar campaña...</option>
                @foreach($campaigns as $c)
                    <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->year }})</option>
                @endforeach
            </flux:select>
        </flux:field>

        <flux:field class="min-w-[200px]">
            <flux:label>Parcela</flux:label>
            <flux:select wire:model.live="filterPlot">
                <option value="">Todas las parcelas</option>
                @foreach($plots as $p)
                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                @endforeach
            </flux:select>
        </flux:field>
    </div>

    @if(!$filterCampaign)
        <x-agro.empty-state
            icon="map"
            title="Selecciona una campaña"
            description="Elige una campaña para ver el panel integral de tu finca."
        />
    @elseif(!$data)
        <x-agro.empty-state icon="map" title="Sin datos" description="No se encontraron datos para esta campaña." />
    @else
        {{-- KPIs principales --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
            <x-agro.stat-card label="Parcelas" :value="$data['plots']->count()" icon="map" color="green" />
            <x-agro.stat-card label="Superficie total" :value="number_format($data['totalArea'], 2) . ' ha'" icon="globe-europe-africa" color="blue" />
            <x-agro.stat-card label="Plantaciones activas" :value="$data['totalPlantings']" icon="book-open" color="purple" />
            <x-agro.stat-card label="Actividades campaña" :value="array_sum($data['activityCounts'])" icon="pencil-square" color="orange" />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            {{-- Variedades plantadas --}}
            <x-agro.card>
                <x-slot:header>
                    <div class="flex items-center gap-2">
                        <flux:icon icon="swatch" class="size-4 text-purple-600" />
                        <span class="text-sm font-semibold text-zinc-800">Variedades plantadas</span>
                    </div>
                </x-slot:header>
                <div class="space-y-2">
                    @forelse($data['varietyCounts'] as $variety => $count)
                        <div class="flex items-center justify-between px-1">
                            <span class="text-sm text-zinc-700">{{ $variety }}</span>
                            <div class="flex items-center gap-2">
                                <div class="w-24 h-2 bg-zinc-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-purple-400 rounded-full" style="width: {{ $data['totalPlantings'] > 0 ? round($count / $data['totalPlantings'] * 100) : 0 }}%"></div>
                                </div>
                                <span class="text-xs font-medium text-zinc-500 w-8 text-right">{{ $count }}</span>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-zinc-400">Sin plantaciones registradas.</p>
                    @endforelse
                </div>
            </x-agro.card>

            {{-- Ciclo de vida de plantaciones --}}
            <x-agro.card>
                <x-slot:header>
                    <div class="flex items-center gap-2">
                        <flux:icon icon="clock" class="size-4 text-green-600" />
                        <span class="text-sm font-semibold text-zinc-800">Ciclo de vida</span>
                    </div>
                </x-slot:header>
                @php
                    $stageColors = ['joven' => 'bg-emerald-400', 'desarrollo' => 'bg-blue-400', 'productiva' => 'bg-green-500', 'madura' => 'bg-amber-400', 'vieja' => 'bg-red-400', 'desconocido' => 'bg-zinc-300'];
                    $stageLabels = ['joven' => 'Joven', 'desarrollo' => 'En desarrollo', 'productiva' => 'Productiva', 'madura' => 'Madura', 'vieja' => 'Vieja', 'desconocido' => 'Desconocido'];
                @endphp
                <div class="space-y-2">
                    @forelse($data['lifeCycleStages'] as $stage => $count)
                        <div class="flex items-center justify-between px-1">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full {{ $stageColors[$stage] ?? 'bg-zinc-300' }}"></span>
                                <span class="text-sm text-zinc-700">{{ $stageLabels[$stage] ?? ucfirst($stage) }}</span>
                            </div>
                            <span class="text-xs font-medium text-zinc-500">{{ $count }} plantaciones</span>
                        </div>
                    @empty
                        <p class="text-sm text-zinc-400">Sin datos de ciclo de vida.</p>
                    @endforelse
                </div>
            </x-agro.card>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            {{-- Actividades por tipo --}}
            <x-agro.card>
                <x-slot:header>
                    <div class="flex items-center gap-2">
                        <flux:icon icon="chart-bar" class="size-4 text-orange-600" />
                        <span class="text-sm font-semibold text-zinc-800">Actividades por tipo</span>
                    </div>
                </x-slot:header>
                @php
                    $actLabels = [
                        'phytosanitary' => 'Tratamientos', 'fertilization' => 'Fertilizaciones', 'irrigation' => 'Riegos',
                        'cultural' => 'Labores', 'observation' => 'Observaciones', 'harvest' => 'Vendimia',
                        'pruning' => 'Podas', 'phenology' => 'Fenología', 'post_harvest' => 'Post-vendimia',
                    ];
                @endphp
                <div class="space-y-1.5">
                    @forelse($data['activityCounts'] as $type => $count)
                        <div class="flex items-center justify-between px-1">
                            <span class="text-sm text-zinc-700">{{ $actLabels[$type] ?? ucfirst($type) }}</span>
                            <flux:badge size="sm" color="zinc">{{ $count }}</flux:badge>
                        </div>
                    @empty
                        <p class="text-sm text-zinc-400">Sin actividades en esta campaña.</p>
                    @endforelse
                </div>
            </x-agro.card>

            {{-- Rendimiento estimado vs real --}}
            <x-agro.card>
                <x-slot:header>
                    <div class="flex items-center gap-2">
                        <flux:icon icon="scale" class="size-4 text-blue-600" />
                        <span class="text-sm font-semibold text-zinc-800">Rendimiento: estimado vs real</span>
                    </div>
                </x-slot:header>
                @if(count($data['yieldPerPlot']) > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-zinc-50 text-zinc-500 text-xs uppercase">
                                    <th class="text-left px-3 py-2">Parcela</th>
                                    <th class="text-right px-3 py-2">Estimado (kg)</th>
                                    <th class="text-right px-3 py-2">Real (kg)</th>
                                    <th class="text-right px-3 py-2">Variación</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100">
                                @foreach($data['yieldPerPlot'] as $row)
                                    <tr class="hover:bg-zinc-50">
                                        <td class="px-3 py-2 font-medium text-zinc-700">{{ $row['plot_name'] }}</td>
                                        <td class="px-3 py-2 text-right text-zinc-600">{{ number_format($row['estimated'], 0) }}</td>
                                        <td class="px-3 py-2 text-right text-zinc-600">{{ number_format($row['actual'], 0) }}</td>
                                        <td class="px-3 py-2 text-right">
                                            @if($row['variance_pct'] !== null)
                                                <span class="{{ $row['variance_pct'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                                    {{ $row['variance_pct'] > 0 ? '+' : '' }}{{ $row['variance_pct'] }}%
                                                </span>
                                            @else
                                                <span class="text-zinc-400">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-sm text-zinc-400 px-1">Sin datos de rendimiento para esta campaña.</p>
                @endif
            </x-agro.card>
        </div>

        {{-- Actividades recientes --}}
        @if($data['recentActivities']->isNotEmpty())
            <x-agro.card class="mb-6">
                <x-slot:header>
                    <div class="flex items-center gap-2">
                        <flux:icon icon="clock" class="size-4 text-agro-600" />
                        <span class="text-sm font-semibold text-zinc-800">Últimas actividades</span>
                    </div>
                </x-slot:header>
                <div class="space-y-1">
                    @foreach($data['recentActivities'] as $act)
                        <div class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-zinc-50">
                            <div class="w-9 h-9 rounded-lg bg-agro-50 flex items-center justify-center flex-shrink-0">
                                <flux:icon icon="pencil-square" class="size-4 text-agro-600" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-zinc-900 truncate">{{ $actLabels[$act->activity_type] ?? ucfirst($act->activity_type) }}</p>
                                <p class="text-xs text-zinc-400 truncate">{{ $act->plot?->name ?? 'Sin parcela' }}</p>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="text-xs text-zinc-500">{{ $act->activity_date?->format('d/m/Y') }}</p>
                                @if($act->phenological_stage)
                                    <p class="text-xs text-zinc-400">{{ $act->phenological_stage }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-agro.card>
        @endif

        {{-- Detalle por parcela: paginado y lazy ──────────────────────── --}}
        <div>
            <h3 class="text-sm font-bold text-zinc-700 uppercase tracking-wider mb-4">Detalle por parcela</h3>
            <livewire:producer.integrated-estate.plot-table
                :campaign-id="(int) $filterCampaign"
                :plot-filter="(int) $filterPlot"
                wire:key="plot-table-{{ $filterCampaign }}-{{ $filterPlot }}"
            />
        </div>
    @endif
</div>
