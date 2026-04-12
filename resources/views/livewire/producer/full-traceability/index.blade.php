<div>
    <x-agro.page-header
        title="Trazabilidad Cepa a Botella"
        description="Flujo completo desde la parcela hasta el producto final embotellado"
        icon="arrow-trending-up"
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
                <option value="">Todas</option>
                @foreach($plots as $p)
                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                @endforeach
            </flux:select>
        </flux:field>

        <flux:field class="min-w-[200px]">
            <flux:label>Vino</flux:label>
            <flux:select wire:model.live="filterWine">
                <option value="">Todos</option>
                @foreach($wines as $w)
                    <option value="{{ $w->id }}">{{ $w->name }} ({{ $w->vintage }})</option>
                @endforeach
            </flux:select>
        </flux:field>
    </div>

    @if(!$filterCampaign)
        <x-agro.empty-state
            icon="arrow-trending-up"
            title="Selecciona una campaña"
            description="Elige una campaña para rastrear el flujo de uva desde la cepa hasta la botella."
        />
    @elseif(!$traceData)
        <x-agro.empty-state icon="arrow-trending-up" title="Sin datos" description="No se encontraron datos de trazabilidad." />
    @else
        {{-- Pipeline visual: 4 etapas --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            {{-- Etapa 1: Campo --}}
            <div class="relative bg-gradient-to-br from-green-50 to-green-100/50 rounded-2xl p-5 ring-1 ring-green-200/60">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-7 h-7 rounded-lg bg-green-500 flex items-center justify-center">
                        <flux:icon icon="sun" class="size-3.5 text-white" />
                    </div>
                    <span class="text-xs font-bold uppercase tracking-wider text-green-700">1. Campo</span>
                </div>
                <p class="text-2xl font-bold text-zinc-900">{{ number_format($traceData['fieldStats']->total_kg ?? 0) }} kg</p>
                <p class="text-xs text-zinc-500 mt-1">{{ $traceData['fieldStats']->entries ?? 0 }} vendimias · {{ $traceData['fieldStats']->plots_count ?? 0 }} parcelas</p>
                <div class="hidden lg:block absolute -right-3 top-1/2 -translate-y-1/2 text-green-300 z-10">
                    <flux:icon icon="chevron-right" class="size-6" />
                </div>
            </div>

            {{-- Etapa 2: Recepción --}}
            <div class="relative bg-gradient-to-br from-amber-50 to-amber-100/50 rounded-2xl p-5 ring-1 ring-amber-200/60">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-7 h-7 rounded-lg bg-amber-500 flex items-center justify-center">
                        <flux:icon icon="archive-box-arrow-down" class="size-3.5 text-white" />
                    </div>
                    <span class="text-xs font-bold uppercase tracking-wider text-amber-700">2. Recepción</span>
                </div>
                <p class="text-2xl font-bold text-zinc-900">{{ number_format($traceData['receptionStats']->total_kg ?? 0) }} kg</p>
                <p class="text-xs text-zinc-500 mt-1">{{ $traceData['receptionStats']->entries ?? 0 }} entradas · {{ $traceData['receptionStats']->batches_count ?? 0 }} lotes</p>
                <div class="hidden lg:block absolute -right-3 top-1/2 -translate-y-1/2 text-amber-300 z-10">
                    <flux:icon icon="chevron-right" class="size-6" />
                </div>
            </div>

            {{-- Etapa 3: Elaboración --}}
            <div class="relative bg-gradient-to-br from-purple-50 to-purple-100/50 rounded-2xl p-5 ring-1 ring-purple-200/60">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-7 h-7 rounded-lg bg-purple-500 flex items-center justify-center">
                        <flux:icon icon="beaker" class="size-3.5 text-white" />
                    </div>
                    <span class="text-xs font-bold uppercase tracking-wider text-purple-700">3. Elaboración</span>
                </div>
                <p class="text-2xl font-bold text-zinc-900">{{ $traceData['wineStats']['total_wines'] }} vinos</p>
                <p class="text-xs text-zinc-500 mt-1">{{ number_format($traceData['wineStats']['total_volume'] ?? 0) }} L en depósitos</p>
                <div class="hidden lg:block absolute -right-3 top-1/2 -translate-y-1/2 text-purple-300 z-10">
                    <flux:icon icon="chevron-right" class="size-6" />
                </div>
            </div>

            {{-- Etapa 4: Producto --}}
            <div class="bg-gradient-to-br from-rose-50 to-rose-100/50 rounded-2xl p-5 ring-1 ring-rose-200/60">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-7 h-7 rounded-lg bg-rose-500 flex items-center justify-center">
                        <flux:icon icon="archive-box" class="size-3.5 text-white" />
                    </div>
                    <span class="text-xs font-bold uppercase tracking-wider text-rose-700">4. Producto</span>
                </div>
                <p class="text-2xl font-bold text-zinc-900">{{ $traceData['productStats']->total_lots ?? 0 }} lotes</p>
                <p class="text-xs text-zinc-500 mt-1">{{ number_format($traceData['productStats']->sold_units ?? 0) }} / {{ number_format($traceData['productStats']->total_units ?? 0) }} uds vendidas</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            {{-- Flujo por parcela y variedad --}}
            <x-agro.card>
                <x-slot:header>
                    <div class="flex items-center gap-2">
                        <flux:icon icon="map" class="size-4 text-green-600" />
                        <span class="text-sm font-semibold text-zinc-800">Origen: parcela y variedad</span>
                    </div>
                </x-slot:header>
                @if($traceData['flowByPlot']->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-zinc-50 text-zinc-500 text-xs uppercase">
                                    <th class="text-left px-3 py-2">Parcela</th>
                                    <th class="text-left px-3 py-2">Variedad</th>
                                    <th class="text-right px-3 py-2">Vendimias</th>
                                    <th class="text-right px-3 py-2">kg</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100">
                                @foreach($traceData['flowByPlot'] as $row)
                                    <tr class="hover:bg-zinc-50">
                                        <td class="px-3 py-2 font-medium text-zinc-700">{{ $row->plot_name }}</td>
                                        <td class="px-3 py-2 text-zinc-600">{{ $row->variety ?? '—' }}</td>
                                        <td class="px-3 py-2 text-right text-zinc-600">{{ $row->harvest_count }}</td>
                                        <td class="px-3 py-2 text-right font-medium text-zinc-800">{{ number_format($row->field_kg) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-sm text-zinc-400">Sin vendimias de campo en esta campaña.</p>
                @endif
            </x-agro.card>

            {{-- Lotes de recepción --}}
            <x-agro.card>
                <x-slot:header>
                    <div class="flex items-center gap-2">
                        <flux:icon icon="archive-box-arrow-down" class="size-4 text-amber-600" />
                        <span class="text-sm font-semibold text-zinc-800">Lotes de recepción</span>
                    </div>
                </x-slot:header>
                @if($traceData['batches']->isNotEmpty())
                    <div class="space-y-1">
                        @foreach($traceData['batches'] as $batch)
                            <div class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-zinc-50">
                                <div class="w-9 h-9 rounded-lg bg-amber-50 flex items-center justify-center flex-shrink-0">
                                    <flux:icon icon="archive-box-arrow-down" class="size-4 text-amber-600" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-zinc-900 truncate">{{ $batch->plotPlanting?->plot?->name ?? 'Sin parcela' }}</p>
                                    <p class="text-xs text-zinc-400 truncate">{{ $batch->plotPlanting?->grapeVariety?->name ?? '—' }} · {{ $batch->receptions_count }} recepciones</p>
                                </div>
                                <div class="text-right shrink-0">
                                    <p class="text-sm font-bold text-zinc-800">{{ number_format($batch->receptions_sum_total_weight ?? 0) }} kg</p>
                                    <flux:badge size="sm" :color="$batch->status === 'closed' ? 'green' : 'amber'">{{ $batch->status === 'closed' ? 'Cerrado' : 'Abierto' }}</flux:badge>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-zinc-400">Sin lotes de recepción.</p>
                @endif
            </x-agro.card>
        </div>

        {{-- Vinos elaborados --}}
        @if($traceData['winesData']->isNotEmpty())
            <x-agro.card class="mb-6">
                <x-slot:header>
                    <div class="flex items-center justify-between w-full">
                        <div class="flex items-center gap-2">
                            <flux:icon icon="beaker" class="size-4 text-purple-600" />
                            <span class="text-sm font-semibold text-zinc-800">Vinos elaborados</span>
                        </div>
                        <div class="flex gap-2">
                            @foreach($traceData['wineStats']['types'] as $type => $count)
                                <flux:badge size="sm" color="purple">{{ ucfirst($type) }}: {{ $count }}</flux:badge>
                            @endforeach
                        </div>
                    </div>
                </x-slot:header>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-zinc-50 text-zinc-500 text-xs uppercase">
                                <th class="text-left px-3 py-2">Vino</th>
                                <th class="text-left px-3 py-2">Tipo</th>
                                <th class="text-left px-3 py-2">Enólogo</th>
                                <th class="text-right px-3 py-2">Vendimias</th>
                                <th class="text-right px-3 py-2">Trasiegos</th>
                                <th class="text-right px-3 py-2">Embotellados</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                            @foreach($traceData['winesData'] as $wine)
                                <tr class="hover:bg-zinc-50">
                                    <td class="px-3 py-2 font-medium text-zinc-700">{{ $wine->name }}</td>
                                    <td class="px-3 py-2">
                                        <flux:badge size="sm" color="purple">{{ ucfirst($wine->wine_type) }}</flux:badge>
                                    </td>
                                    <td class="px-3 py-2 text-zinc-600">{{ $wine->oenologist?->name ?? '—' }}</td>
                                    <td class="px-3 py-2 text-right text-zinc-600">{{ $wine->harvests_count }}</td>
                                    <td class="px-3 py-2 text-right text-zinc-600">{{ $wine->transfers_count }}</td>
                                    <td class="px-3 py-2 text-right text-zinc-600">{{ $wine->bottlings_count }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-agro.card>
        @endif
    @endif
</div>
