<div>
    <x-agro.page-header
        title="Dashboard Integrado de Campaña"
        description="Visión completa viñedo + bodega: del esfuerzo en campo al resultado en bodega"
        icon="chart-bar-square"
    />

    {{-- Filtro --}}
    <div class="flex flex-wrap items-end gap-4 mb-6">
        <flux:field class="min-w-[250px]">
            <flux:label>Campaña</flux:label>
            <flux:select wire:model.live="filterCampaign">
                <option value="">Seleccionar campaña...</option>
                @foreach($campaigns as $c)
                    <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->year }}) {{ $c->active ? '● Activa' : '' }}</option>
                @endforeach
            </flux:select>
        </flux:field>

        @if($campaign)
            <div class="flex items-center gap-2 pb-1">
                <flux:badge size="sm" :color="$campaign->active ? 'green' : 'zinc'">{{ $campaign->active ? 'Activa' : 'Cerrada' }}</flux:badge>
            </div>
        @endif
    </div>

    @if(!$filterCampaign)
        <x-agro.empty-state
            icon="chart-bar-square"
            title="Selecciona una campaña"
            description="Elige una campaña para ver el dashboard integrado viñedo + bodega."
        />
    @elseif(!$data)
        <x-agro.empty-state icon="chart-bar-square" title="Sin datos" description="No hay datos para esta campaña." />
    @else
        {{-- ═══ KPIs de cruce viñedo↔bodega ═══ --}}
        <div class="bg-gradient-to-r from-green-50 via-amber-50 to-purple-50 rounded-2xl p-5 ring-1 ring-zinc-200/60 mb-6">
            <p class="text-xs font-bold uppercase tracking-wider text-zinc-500 mb-3">Viñedo → Bodega</p>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-6">
                <div>
                    <p class="text-xs text-zinc-500">Campo</p>
                    <p class="text-xl font-bold text-green-700">{{ number_format($data['fieldHarvest']->total_kg ?? 0) }} kg</p>
                </div>
                <div>
                    <p class="text-xs text-zinc-500">Recepción bodega</p>
                    <p class="text-xl font-bold text-amber-700">{{ number_format($data['receptionStats']->total_kg ?? 0) }} kg</p>
                </div>
                <div>
                    <p class="text-xs text-zinc-500">Merma campo→bodega</p>
                    <p class="text-xl font-bold {{ $data['yieldLoss'] > 5 ? 'text-red-600' : 'text-zinc-700' }}">{{ $data['yieldLoss'] }}%</p>
                </div>
                <div>
                    <p class="text-xs text-zinc-500">Multiplicador valor</p>
                    <p class="text-xl font-bold text-purple-700">{{ $data['valueMultiplier'] }}x</p>
                    <p class="text-[10px] text-zinc-400">ventas / valor campo</p>
                </div>
            </div>
        </div>

        {{-- ═══ SECCIÓN VIÑEDO ═══ --}}
        <div class="mb-8">
            <h2 class="flex items-center gap-2 text-sm font-bold text-green-700 uppercase tracking-wider mb-4">
                <flux:icon icon="sun" class="size-4" />
                Viñedo
            </h2>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-4">
                <x-agro.stat-card label="Actividades" :value="$data['totalActivities']" icon="pencil-square" color="green" />
                <x-agro.stat-card label="Vendimias" :value="$data['fieldHarvest']->entries ?? 0" icon="archive-box-arrow-down" color="green" />
                <x-agro.stat-card label="Rend. medio" :value="number_format($data['fieldHarvest']->avg_yield ?? 0) . ' kg/ha'" icon="chart-bar" color="blue" />
                <x-agro.stat-card label="Costes parcela" :value="number_format($data['plotCosts'], 0) . ' €'" icon="banknotes" color="orange" />
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Actividades por tipo --}}
                <x-agro.card>
                    <x-slot:header>
                        <div class="flex items-center gap-2">
                            <flux:icon icon="clipboard-document-list" class="size-4 text-green-600" />
                            <span class="text-sm font-semibold text-zinc-800">Cuaderno de campo</span>
                        </div>
                    </x-slot:header>
                    @php
                        $actLabels = [
                            'phytosanitary'           => 'Tratamientos',
                            'phytosanitary_treatment' => 'Tratamientos',
                            'fertilization'           => 'Fertilizaciones',
                            'irrigation'              => 'Riegos',
                            'cultural'                => 'Labores',
                            'cultural_work'           => 'Labores',
                            'observation'             => 'Observaciones',
                            'harvest'                 => 'Vendimia',
                            'pruning'                 => 'Podas',
                            'phenology'               => 'Fenología',
                            'post_harvest'            => 'Post-vendimia',
                            'post_harvest_treatment'  => 'Post-vendimia',
                        ];
                    @endphp
                    <div class="space-y-1.5">
                        @forelse($data['activityCounts'] as $type => $count)
                            <div class="flex items-center justify-between px-1">
                                <span class="text-sm text-zinc-700">{{ $actLabels[$type] ?? ucfirst($type) }}</span>
                                <div class="flex items-center gap-2">
                                    <div class="w-20 h-1.5 bg-zinc-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-green-400 rounded-full" style="width: {{ $data['totalActivities'] > 0 ? round($count / $data['totalActivities'] * 100) : 0 }}%"></div>
                                    </div>
                                    <span class="text-xs font-medium text-zinc-500 w-6 text-right">{{ $count }}</span>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-zinc-400">Sin actividades registradas.</p>
                        @endforelse
                    </div>
                </x-agro.card>

                {{-- Calidad de vendimia --}}
                <x-agro.card>
                    <x-slot:header>
                        <div class="flex items-center gap-2">
                            <flux:icon icon="beaker" class="size-4 text-green-600" />
                            <span class="text-sm font-semibold text-zinc-800">Calidad media de vendimia</span>
                        </div>
                    </x-slot:header>
                    <div class="grid grid-cols-3 gap-4 text-center">
                        <div class="p-3 bg-zinc-50 rounded-xl">
                            <p class="text-lg font-bold text-zinc-800">{{ number_format($data['fieldHarvest']->avg_baume ?? 0, 1) }}°</p>
                            <p class="text-xs text-zinc-500">Baumé</p>
                        </div>
                        <div class="p-3 bg-zinc-50 rounded-xl">
                            <p class="text-lg font-bold text-zinc-800">{{ number_format($data['fieldHarvest']->avg_brix ?? 0, 1) }}°</p>
                            <p class="text-xs text-zinc-500">Brix</p>
                        </div>
                        <div class="p-3 bg-zinc-50 rounded-xl">
                            <p class="text-lg font-bold text-zinc-800">{{ number_format($data['fieldHarvest']->avg_ph ?? 0, 2) }}</p>
                            <p class="text-xs text-zinc-500">pH</p>
                        </div>
                    </div>
                </x-agro.card>
            </div>

            {{-- Desglose por parcela --}}
            @if($data['perPlot']->isNotEmpty())
                <x-agro.card class="mt-4">
                    <x-slot:header>
                        <div class="flex items-center gap-2">
                            <flux:icon icon="map" class="size-4 text-green-600" />
                            <span class="text-sm font-semibold text-zinc-800">Rendimiento por parcela</span>
                        </div>
                    </x-slot:header>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-zinc-50 text-zinc-500 text-xs uppercase">
                                    <th class="text-left px-3 py-2">Parcela</th>
                                    <th class="text-right px-3 py-2">Vendimias</th>
                                    <th class="text-right px-3 py-2">Total kg</th>
                                    <th class="text-right px-3 py-2">Rend. medio</th>
                                    <th class="text-right px-3 py-2">Baumé</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100">
                                @foreach($data['perPlot'] as $row)
                                    <tr class="hover:bg-zinc-50">
                                        <td class="px-3 py-2 font-medium text-zinc-700">{{ $row->plot_name }}</td>
                                        <td class="px-3 py-2 text-right text-zinc-600">{{ $row->harvest_count }}</td>
                                        <td class="px-3 py-2 text-right font-medium text-zinc-800">{{ number_format($row->total_kg) }}</td>
                                        <td class="px-3 py-2 text-right text-zinc-600">{{ number_format($row->avg_yield, 0) }} kg/ha</td>
                                        <td class="px-3 py-2 text-right text-zinc-600">{{ number_format($row->avg_baume, 1) }}°</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </x-agro.card>
            @endif
        </div>

        {{-- ═══ SECCIÓN BODEGA ═══ --}}
        <div>
            <h2 class="flex items-center gap-2 text-sm font-bold text-purple-700 uppercase tracking-wider mb-4">
                <flux:icon icon="beaker" class="size-4" />
                Bodega
            </h2>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-4">
                <x-agro.stat-card label="Vinos" :value="count($data['wines'])" icon="beaker" color="purple" />
                <x-agro.stat-card label="Volumen en depósito" :value="number_format($data['wineVolume']) . ' L'" icon="arrows-right-left" color="blue" />
                <x-agro.stat-card label="Uso contenedores" :value="$data['containerPct'] . '%'" icon="cube" color="orange" />
                <x-agro.stat-card label="Lotes producto" :value="$data['productStats']->lots ?? 0" icon="archive-box" color="green" />
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Vinos por tipo --}}
                <x-agro.card>
                    <x-slot:header>
                        <div class="flex items-center gap-2">
                            <flux:icon icon="swatch" class="size-4 text-purple-600" />
                            <span class="text-sm font-semibold text-zinc-800">Vinos por tipo</span>
                        </div>
                    </x-slot:header>
                    @php
                        $typeColors = ['tinto' => 'bg-red-400', 'blanco' => 'bg-yellow-300', 'rosado' => 'bg-pink-300', 'espumoso' => 'bg-cyan-300', 'otro' => 'bg-zinc-300'];
                    @endphp
                    <div class="space-y-2">
                        @forelse($data['winesByType'] as $type => $count)
                            <div class="flex items-center justify-between px-1">
                                <div class="flex items-center gap-2">
                                    <span class="w-3 h-3 rounded-full {{ $typeColors[$type] ?? 'bg-zinc-300' }}"></span>
                                    <span class="text-sm text-zinc-700">{{ ucfirst($type) }}</span>
                                </div>
                                <span class="text-sm font-medium text-zinc-600">{{ $count }}</span>
                            </div>
                        @empty
                            <p class="text-sm text-zinc-400">Sin vinos registrados.</p>
                        @endforelse
                    </div>
                </x-agro.card>

                {{-- Vinos por estado --}}
                <x-agro.card>
                    <x-slot:header>
                        <div class="flex items-center gap-2">
                            <flux:icon icon="signal" class="size-4 text-purple-600" />
                            <span class="text-sm font-semibold text-zinc-800">Estado de vinos</span>
                        </div>
                    </x-slot:header>
                    @php
                        $statusLabels = ['in_progress' => 'En elaboración', 'aging' => 'Crianza', 'ready' => 'Listo', 'bottled' => 'Embotellado', 'archived' => 'Archivado'];
                        $statusColors = ['in_progress' => 'amber', 'aging' => 'purple', 'ready' => 'green', 'bottled' => 'blue', 'archived' => 'zinc'];
                    @endphp
                    <div class="space-y-2">
                        @forelse($data['winesByStatus'] as $status => $count)
                            <div class="flex items-center justify-between px-1">
                                <flux:badge size="sm" :color="$statusColors[$status] ?? 'zinc'">{{ $statusLabels[$status] ?? ucfirst($status) }}</flux:badge>
                                <span class="text-sm font-medium text-zinc-600">{{ $count }}</span>
                            </div>
                        @empty
                            <p class="text-sm text-zinc-400">Sin datos de estado.</p>
                        @endforelse
                    </div>
                </x-agro.card>
            </div>

            {{-- Capacidad de bodega --}}
            <x-agro.card class="mt-4">
                <x-slot:header>
                    <div class="flex items-center gap-2">
                        <flux:icon icon="cube" class="size-4 text-orange-600" />
                        <span class="text-sm font-semibold text-zinc-800">Capacidad de bodega</span>
                    </div>
                </x-slot:header>
                <div class="flex items-center gap-4">
                    <div class="flex-1">
                        <div class="h-4 bg-zinc-100 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all {{ $data['containerPct'] > 90 ? 'bg-red-400' : ($data['containerPct'] > 70 ? 'bg-amber-400' : 'bg-green-400') }}"
                                 style="width: {{ min($data['containerPct'], 100) }}%"></div>
                        </div>
                    </div>
                    <div class="text-right shrink-0">
                        <p class="text-sm font-bold text-zinc-800">{{ number_format($data['containerUsed']) }} / {{ number_format($data['containerCapacity']) }} L</p>
                        <p class="text-xs text-zinc-500">{{ $data['containerPct'] }}% ocupado</p>
                    </div>
                </div>
            </x-agro.card>

            {{-- Resumen económico bodega --}}
            @if(($data['productStats']->lots ?? 0) > 0)
                <x-agro.card class="mt-4">
                    <x-slot:header>
                        <div class="flex items-center gap-2">
                            <flux:icon icon="banknotes" class="size-4 text-green-600" />
                            <span class="text-sm font-semibold text-zinc-800">Producto y ventas</span>
                        </div>
                    </x-slot:header>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-center">
                        <div class="p-3 bg-zinc-50 rounded-xl">
                            <p class="text-lg font-bold text-zinc-800">{{ $data['productStats']->lots }}</p>
                            <p class="text-xs text-zinc-500">Lotes</p>
                        </div>
                        <div class="p-3 bg-zinc-50 rounded-xl">
                            <p class="text-lg font-bold text-zinc-800">{{ number_format($data['productStats']->units) }}</p>
                            <p class="text-xs text-zinc-500">Unidades</p>
                        </div>
                        <div class="p-3 bg-zinc-50 rounded-xl">
                            <p class="text-lg font-bold text-green-700">{{ number_format($data['productStats']->sold) }}</p>
                            <p class="text-xs text-zinc-500">Vendidas</p>
                        </div>
                        <div class="p-3 bg-zinc-50 rounded-xl">
                            <p class="text-lg font-bold text-green-700">{{ number_format($data['productStats']->revenue, 0) }} €</p>
                            <p class="text-xs text-zinc-500">Ingresos</p>
                        </div>
                    </div>
                </x-agro.card>
            @endif
        </div>
    @endif
</div>
