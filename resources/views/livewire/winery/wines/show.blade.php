<div class="space-y-6 animate-fade-in"
    x-data="{ tab: 'timeline' }">

    {{-- ── Cabecera ──────────────────────────────────────────────────────────── --}}
    <x-agro.page-header
        :title="$wine->name"
        :description="$wine->variety ?? $wine->type_label . ' · Añada ' . ($wine->vintage ?? 'S/A')"
        icon="beaker"
    >
        <x-slot:actions>
            <flux:button variant="ghost" icon="pencil"
                href="{{ roleRoute('wines.edit', $wine) }}" wire:navigate>
                Editar
            </flux:button>
            <flux:button variant="ghost" icon="arrow-left"
                href="{{ roleRoute('wines.index') }}" wire:navigate>
                Volver
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- ── Stat cards ───────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 gap-4">
        @php
            $statusColor = match($wine->status) {
                'in_progress' => 'blue', 'aged' => 'yellow',
                'bottled' => 'green', 'sold' => 'zinc', default => 'red',
            };
            $typeColor = match($wine->wine_type) {
                'red' => 'red', 'rose' => 'pink', 'white' => 'yellow',
                'sparkling' => 'sky', default => 'purple',
            };
        @endphp
        <x-agro.stat-card label="Estado" :value="$wine->status_label" icon="signal" :color="$statusColor" />
        <x-agro.stat-card label="Tipo" :value="$wine->type_label" icon="beaker" :color="$typeColor" />
        <x-agro.stat-card label="Volumen" :value="($wine->volume_liters ? number_format($wine->volume_liters, 0) . ' L' : '—')" icon="cube" color="zinc" />
        <x-agro.stat-card label="Añada" :value="$wine->vintage ?? '—'" icon="calendar" color="zinc" />
    </div>

    {{-- ── Botones de acción ────────────────────────────────────────────────── --}}
    <div class="flex flex-wrap gap-3">
        <flux:modal.trigger name="modal-fermentation">
            <flux:button variant="primary" icon="plus" size="sm">Control fermentación</flux:button>
        </flux:modal.trigger>
        <flux:modal.trigger name="modal-transfer">
            <flux:button variant="filled" icon="arrows-right-left" size="sm">Trasvasar</flux:button>
        </flux:modal.trigger>
        <flux:modal.trigger name="modal-loss">
            <flux:button variant="filled" icon="minus-circle" size="sm">Registrar merma</flux:button>
        </flux:modal.trigger>
        <flux:modal.trigger name="modal-analysis">
            <flux:button variant="filled" icon="document-magnifying-glass" size="sm">Añadir análisis</flux:button>
        </flux:modal.trigger>
        <flux:modal.trigger name="modal-composition">
            <flux:button variant="outline" icon="queue-list" size="sm">Vincular recepción</flux:button>
        </flux:modal.trigger>
        <flux:modal.trigger name="modal-additive">
            <flux:button variant="outline" icon="beaker" size="sm">Añadir aditivo</flux:button>
        </flux:modal.trigger>
    </div>

    {{-- ── Tabs ─────────────────────────────────────────────────────────────── --}}
    <div class="border-b border-zinc-200 dark:border-zinc-700">
        <nav class="flex gap-0 -mb-px overflow-x-auto">
            @foreach([
                ['id' => 'timeline',     'label' => 'Timeline',       'icon' => 'clock'],
                ['id' => 'composition',  'label' => 'Composición',    'icon' => 'queue-list'],
                ['id' => 'additives',    'label' => 'Aditivos',       'icon' => 'beaker'],
                ['id' => 'fermentation', 'label' => 'Fermentación',   'icon' => 'fire'],
                ['id' => 'transfers',    'label' => 'Trasvases',      'icon' => 'arrows-right-left'],
                ['id' => 'losses',       'label' => 'Mermas',         'icon' => 'minus-circle'],
                ['id' => 'analyses',     'label' => 'Análisis',       'icon' => 'document-magnifying-glass'],
                ['id' => 'costs',        'label' => 'Costes',         'icon' => 'currency-euro'],
                ['id' => 'diagram',      'label' => 'Diagrama',       'icon' => 'share'],
                ['id' => 'qr',           'label' => 'QR',             'icon' => 'qr-code'],
            ] as $t)
            <button
                @click="tab = '{{ $t['id'] }}'"
                :class="tab === '{{ $t['id'] }}'
                    ? 'border-b-2 border-violet-600 text-violet-600 dark:text-violet-400 dark:border-violet-400'
                    : 'text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300 border-b-2 border-transparent'"
                class="flex items-center gap-2 px-4 py-3 text-sm font-medium whitespace-nowrap transition-colors">
                <flux:icon icon="{{ $t['icon'] }}" class="w-4 h-4" />
                {{ $t['label'] }}
            </button>
            @endforeach
        </nav>
    </div>

    {{-- ════════════════════════════════════════════════════════════════════════
         TAB: COMPOSICIÓN
    ════════════════════════════════════════════════════════════════════════════ --}}
    <div x-show="tab === 'composition'" x-cloak>
        @if($composition->isEmpty())
            <x-agro.empty-state icon="queue-list" message="Sin uva vinculada"
                description="Vincula recepciones de uva para registrar la composición de este lote.">
                <x-slot:action>
                    <flux:modal.trigger name="modal-composition">
                        <flux:button variant="primary" icon="plus" size="sm">Vincular recepción</flux:button>
                    </flux:modal.trigger>
                </x-slot:action>
            </x-agro.empty-state>
        @else
            @php $totalKg = $composition->sum('quantity_kg'); @endphp

            {{-- Resumen total --}}
            <div class="flex items-center justify-between mb-4">
                <p class="text-sm text-zinc-500">
                    Total vinculado: <span class="font-semibold text-zinc-900">{{ number_format($totalKg, 0) }} kg</span>
                </p>
                <flux:modal.trigger name="modal-composition">
                    <flux:button variant="outline" icon="plus" size="sm">Añadir recepción</flux:button>
                </flux:modal.trigger>
            </div>

            {{-- Barra visual de composición --}}
            @php
                $colors = ['bg-violet-500','bg-blue-500','bg-emerald-500','bg-amber-500','bg-rose-500','bg-sky-500','bg-orange-500','bg-teal-500'];
            @endphp
            <div class="flex rounded-full overflow-hidden h-3 mb-6 gap-px">
                @foreach($composition as $i => $entry)
                    <div
                        class="{{ $colors[$i % count($colors)] }} transition-all"
                        style="width: {{ $entry->percentage ?? 0 }}%"
                        title="{{ $entry->harvest->plotPlanting?->plotVariety?->grapeVariety?->name ?? 'Variedad desconocida' }} · {{ number_format($entry->percentage ?? 0, 1) }}%"
                    ></div>
                @endforeach
            </div>

            {{-- Tabla de recepciones --}}
            <div class="space-y-3">
                @foreach($composition as $i => $entry)
                    @php
                        $harvest  = $entry->harvest;
                        $variety  = $harvest->plotPlanting?->plotVariety?->grapeVariety?->name ?? '—';
                        $plot     = $harvest->plotPlanting?->plot?->name ?? '—';
                    @endphp
                    <div class="flex items-center gap-4 p-3 rounded-xl bg-zinc-50 border border-zinc-100">
                        <div class="w-3 h-3 rounded-full {{ $colors[$i % count($colors)] }} shrink-0"></div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-zinc-900">{{ $variety }}</p>
                            <p class="text-xs text-zinc-500">{{ $plot }} · {{ $harvest->harvest_start_date?->format('d/m/Y') ?? '—' }}</p>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="text-sm font-semibold text-zinc-900">{{ number_format($entry->quantity_kg, 0) }} kg</p>
                            <p class="text-xs text-violet-600 font-medium">{{ number_format($entry->percentage ?? 0, 1) }}%</p>
                        </div>
                        <button
                            wire:click="unlinkHarvest({{ $entry->id }})"
                            wire:confirm="¿Desvincular esta recepción del lote?"
                            class="inline-flex items-center justify-center w-7 h-7 rounded-lg text-zinc-300 hover:text-red-500 hover:bg-red-50 transition-colors shrink-0"
                        >
                            <flux:icon icon="x-mark" class="size-4" />
                        </button>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- ════════════════════════════════════════════════════════════════════════
         TAB: ADITIVOS
    ════════════════════════════════════════════════════════════════════════════ --}}
    <div x-show="tab === 'additives'" x-cloak>
        @if($additives->isEmpty())
            <x-agro.empty-state icon="beaker" message="Sin aditivos registrados"
                description="Registra los aditivos enológicos aplicados en cada etapa del proceso.">
                <x-slot:action>
                    <flux:modal.trigger name="modal-additive">
                        <flux:button variant="primary" icon="plus" size="sm">Añadir aditivo</flux:button>
                    </flux:modal.trigger>
                </x-slot:action>
            </x-agro.empty-state>
        @else
            <div class="flex justify-end mb-4">
                <flux:modal.trigger name="modal-additive">
                    <flux:button variant="outline" icon="plus" size="sm">Añadir aditivo</flux:button>
                </flux:modal.trigger>
            </div>
            <div class="space-y-3">
                @foreach($additives as $ad)
                    <div class="flex items-start gap-4 p-4 rounded-xl bg-zinc-50 border border-zinc-100">
                        <div class="w-8 h-8 bg-amber-50 rounded-lg flex items-center justify-center shrink-0">
                            <flux:icon icon="beaker" class="size-4 text-amber-600" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <p class="text-sm font-semibold text-zinc-900">{{ $ad->additive_name }}</p>
                                @if($ad->processDetail)
                                    <flux:badge color="violet" size="sm">{{ $ad->processDetail->process_type_label }}</flux:badge>
                                @endif
                                @if($ad->supply?->isLowStock())
                                    <flux:badge color="red" size="sm">Stock bajo</flux:badge>
                                @endif
                            </div>
                            <div class="flex flex-wrap gap-3 mt-1 text-xs text-zinc-500">
                                <span>{{ $ad->application_date->format('d/m/Y') }}</span>
                                <span class="font-medium text-zinc-700">
                                    {{ number_format($ad->quantity, 3) }} {{ $ad->unitOfMeasurement?->abbreviation }}
                                </span>
                                @if($ad->oenologist)
                                    <span>{{ $ad->oenologist->full_name }}</span>
                                @endif
                            </div>
                            @if($ad->notes)
                                <p class="text-xs text-zinc-400 mt-1">{{ $ad->notes }}</p>
                            @endif
                        </div>
                        <button
                            wire:click="deleteAdditive({{ $ad->id }})"
                            wire:loading.attr="disabled"
                            wire:confirm="¿Eliminar este aditivo? Se restaurará el stock del insumo si estaba vinculado."
                            class="inline-flex items-center justify-center w-7 h-7 rounded-lg text-zinc-300 hover:text-red-500 hover:bg-red-50 transition-colors shrink-0"
                        >
                            <flux:icon icon="trash" class="size-4" />
                        </button>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- ════════════════════════════════════════════════════════════════════════
         TAB: TIMELINE
    ════════════════════════════════════════════════════════════════════════════ --}}
    <div x-show="tab === 'timeline'" x-cloak>
        @if(empty($timeline))
            <x-agro.empty-state icon="clock" title="Sin actividad registrada"
                description="Usa los botones de acción para registrar controles, trasvases, mermas o análisis." />
        @else
            <div class="space-y-6">
                @foreach($timeline as $date => $events)
                    <div>
                        {{-- Separador de fecha --}}
                        <div class="flex items-center gap-3 mb-3">
                            <div class="h-px flex-1 bg-zinc-200 dark:bg-zinc-700"></div>
                            <span class="text-xs font-semibold text-zinc-500 uppercase tracking-wide px-2">
                                {{ \Carbon\Carbon::parse($date)->translatedFormat('d M Y') }}
                            </span>
                            <div class="h-px flex-1 bg-zinc-200 dark:bg-zinc-700"></div>
                        </div>

                        <div class="space-y-2">
                            @foreach($events as $event)
                                @php $m = $event['model']; @endphp

                                @if($event['type'] === 'fermentation')
                                <div class="flex gap-3 p-3 rounded-lg bg-blue-50 dark:bg-blue-950/30 border border-blue-100 dark:border-blue-900">
                                    <div class="shrink-0 w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
                                        <flux:icon icon="fire" class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-start justify-between gap-2">
                                            <div>
                                                <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Control fermentación</span>
                                                @if($m->container)
                                                    <span class="ml-2 text-xs text-zinc-500">{{ $m->container->name }}</span>
                                                @endif
                                                <span class="ml-2 text-xs text-zinc-400">{{ $m->control_date->format('H:i') }}</span>
                                            </div>
                                            <flux:button size="xs" variant="ghost" icon="trash"
                                                wire:click="deleteFermentationControl({{ $m->id }})"
                                                wire:loading.attr="disabled"
                                                wire:confirm="¿Eliminar este control?" />
                                        </div>
                                        <div class="mt-1 flex flex-wrap gap-x-4 gap-y-1">
                                            @if($m->temperature !== null)
                                                <span class="text-xs text-zinc-600 dark:text-zinc-400">🌡 {{ $m->temperature }}°C</span>
                                            @endif
                                            @if($m->density !== null)
                                                <span class="text-xs text-zinc-600 dark:text-zinc-400">⚖ {{ $m->density }} g/L</span>
                                            @endif
                                            @if($m->brix_degree !== null)
                                                <span class="text-xs text-zinc-600 dark:text-zinc-400">🍬 {{ $m->brix_degree }}°Brix</span>
                                            @endif
                                            @if($m->ph !== null)
                                                <span class="text-xs text-zinc-600 dark:text-zinc-400">pH {{ $m->ph }}</span>
                                            @endif
                                            @if($m->volatile_acidity !== null)
                                                <span class="text-xs text-zinc-600 dark:text-zinc-400">AV {{ $m->volatile_acidity }} g/L</span>
                                            @endif
                                        </div>
                                        @if($m->notes)
                                            <p class="mt-1 text-xs text-zinc-500 italic">{{ $m->notes }}</p>
                                        @endif
                                    </div>
                                </div>

                                @elseif($event['type'] === 'transfer')
                                <div class="flex gap-3 p-3 rounded-lg bg-violet-50 dark:bg-violet-950/30 border border-violet-100 dark:border-violet-900">
                                    <div class="shrink-0 w-8 h-8 rounded-full bg-violet-100 dark:bg-violet-900 flex items-center justify-center">
                                        <flux:icon icon="arrows-right-left" class="w-4 h-4 text-violet-600 dark:text-violet-400" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-start justify-between gap-2">
                                            <div>
                                                <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $m->transfer_type_label }}</span>
                                                <span class="ml-2 text-xs text-zinc-400">{{ number_format($m->quantity, 1) }} {{ $m->unitOfMeasurement?->abbreviation }}</span>
                                            </div>
                                            <flux:button size="xs" variant="ghost" icon="trash"
                                                wire:click="deleteTransfer({{ $m->id }})"
                                                wire:loading.attr="disabled"
                                                wire:confirm="¿Eliminar este trasvase? Se revertirán los cambios de capacidad." />
                                        </div>
                                        <p class="text-xs text-zinc-600 dark:text-zinc-400 mt-1">
                                            {{ $m->fromContainer?->name ?? 'Origen externo' }}
                                            <flux:icon icon="arrow-right" class="inline w-3 h-3 mx-1" />
                                            {{ $m->toContainer?->name }}
                                            @if($m->oenologist)
                                                <span class="ml-2 text-zinc-400">· {{ $m->oenologist->full_name }}</span>
                                            @endif
                                        </p>
                                        @if($m->notes)
                                            <p class="mt-1 text-xs text-zinc-500 italic">{{ $m->notes }}</p>
                                        @endif
                                    </div>
                                </div>

                                @elseif($event['type'] === 'loss')
                                <div class="flex gap-3 p-3 rounded-lg bg-red-50 dark:bg-red-950/30 border border-red-100 dark:border-red-900">
                                    <div class="shrink-0 w-8 h-8 rounded-full bg-red-100 dark:bg-red-900 flex items-center justify-center">
                                        <flux:icon icon="minus-circle" class="w-4 h-4 text-red-600 dark:text-red-400" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-start justify-between gap-2">
                                            <div>
                                                <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Merma — {{ $m->loss_type_label }}</span>
                                                @if($m->container)
                                                    <span class="ml-2 text-xs text-zinc-500">{{ $m->container->name }}</span>
                                                @endif
                                                <span class="ml-2 text-xs font-medium text-red-600">−{{ number_format($m->quantity, 1) }} {{ $m->unitOfMeasurement?->abbreviation }}</span>
                                            </div>
                                            <flux:button size="xs" variant="ghost" icon="trash"
                                                wire:click="deleteLoss({{ $m->id }})"
                                                wire:loading.attr="disabled"
                                                wire:confirm="¿Eliminar esta merma?" />
                                        </div>
                                        @if($m->notes)
                                            <p class="mt-1 text-xs text-zinc-500 italic">{{ $m->notes }}</p>
                                        @endif
                                    </div>
                                </div>

                                @elseif($event['type'] === 'analysis')
                                <div class="flex gap-3 p-3 rounded-lg bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-100 dark:border-emerald-900">
                                    <div class="shrink-0 w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900 flex items-center justify-center">
                                        <flux:icon icon="document-magnifying-glass" class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-start justify-between gap-2">
                                            <div>
                                                <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Análisis — {{ $m->analysis_type_label }}</span>
                                                @if($m->laboratory_name)
                                                    <span class="ml-2 text-xs text-zinc-500">{{ $m->laboratory_name }}</span>
                                                @endif
                                                @if($m->oenologist)
                                                    <span class="ml-2 text-xs text-zinc-400">· {{ $m->oenologist->full_name }}</span>
                                                @endif
                                            </div>
                                            <flux:button size="xs" variant="ghost" icon="trash"
                                                wire:click="deleteAnalysis({{ $m->id }})"
                                                wire:loading.attr="disabled"
                                                wire:confirm="¿Eliminar este análisis?" />
                                        </div>
                                        <div class="mt-1 flex flex-wrap gap-x-4 gap-y-1">
                                            @if($m->alcohol !== null)
                                                <span class="text-xs text-zinc-600 dark:text-zinc-400">🍷 {{ $m->alcohol }}% vol</span>
                                            @endif
                                            @if($m->ph !== null)
                                                <span class="text-xs text-zinc-600 dark:text-zinc-400">pH {{ $m->ph }}</span>
                                            @endif
                                            @if($m->so2_free !== null)
                                                <span class="text-xs {{ $m->isSo2Freelow() ? 'text-red-600 font-medium' : 'text-zinc-600 dark:text-zinc-400' }}">
                                                    SO₂ libre {{ $m->so2_free }} mg/L{{ $m->isSo2Freelow() ? ' ⚠' : '' }}
                                                </span>
                                            @endif
                                            @if($m->total_acidity !== null)
                                                <span class="text-xs text-zinc-600 dark:text-zinc-400">AT {{ $m->total_acidity }} g/L</span>
                                            @endif
                                            @if($m->volatile_acidity !== null)
                                                <span class="text-xs text-zinc-600 dark:text-zinc-400">AV {{ $m->volatile_acidity }} g/L</span>
                                            @endif
                                        </div>
                                        @if($m->notes)
                                            <p class="mt-1 text-xs text-zinc-500 italic">{{ $m->notes }}</p>
                                        @endif
                                    </div>
                                </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- ════════════════════════════════════════════════════════════════════════
         TAB: FERMENTACIÓN
    ════════════════════════════════════════════════════════════════════════════ --}}
    <div x-show="tab === 'fermentation'" x-cloak>
        <x-agro.card>
            <x-agro.data-table :headers="['Fecha / Hora', 'Depósito', 'Temp.', 'Densidad', '°Brix', 'pH', 'AV', 'Notas', '']">
                @forelse($fermentationControls as $fc)
                    <x-agro.table-row>
                        <x-agro.table-cell>{{ $fc->control_date->format('d/m/Y H:i') }}</x-agro.table-cell>
                        <x-agro.table-cell>{{ $fc->container?->name ?? '—' }}</x-agro.table-cell>
                        <x-agro.table-cell>{{ $fc->temperature !== null ? $fc->temperature . '°C' : '—' }}</x-agro.table-cell>
                        <x-agro.table-cell>{{ $fc->density !== null ? $fc->density . ' g/L' : '—' }}</x-agro.table-cell>
                        <x-agro.table-cell>{{ $fc->brix_degree !== null ? $fc->brix_degree . '°' : '—' }}</x-agro.table-cell>
                        <x-agro.table-cell>{{ $fc->ph ?? '—' }}</x-agro.table-cell>
                        <x-agro.table-cell>{{ $fc->volatile_acidity !== null ? $fc->volatile_acidity . ' g/L' : '—' }}</x-agro.table-cell>
                        <x-agro.table-cell>{{ $fc->notes ? \Str::limit($fc->notes, 40) : '—' }}</x-agro.table-cell>
                        <x-agro.table-cell align="right">
                            <flux:button size="sm" variant="ghost" icon="trash"
                                wire:click="deleteFermentationControl({{ $fc->id }})"
                                wire:loading.attr="disabled"
                                wire:confirm="¿Eliminar este control?" />
                        </x-agro.table-cell>
                    </x-agro.table-row>
                @empty
                    <x-agro.empty-state icon="fire" title="Sin controles registrados"
                        description="Registra lecturas diarias de temperatura, densidad y pH." />
                @endforelse
            </x-agro.data-table>
        </x-agro.card>
    </div>

    {{-- ════════════════════════════════════════════════════════════════════════
         TAB: TRASVASES
    ════════════════════════════════════════════════════════════════════════════ --}}
    <div x-show="tab === 'transfers'" x-cloak>
        <x-agro.card>
            <x-agro.data-table :headers="['Fecha', 'Tipo', 'Origen', 'Destino', 'Cantidad', 'Enólogo', 'Notas', '']">
                @forelse($transfers as $tr)
                    <x-agro.table-row>
                        <x-agro.table-cell>{{ $tr->transfer_date->format('d/m/Y') }}</x-agro.table-cell>
                        <x-agro.table-cell>
                            <x-agro.status-badge :label="$tr->transfer_type_label" color="purple" />
                        </x-agro.table-cell>
                        <x-agro.table-cell>{{ $tr->fromContainer?->name ?? 'Externo' }}</x-agro.table-cell>
                        <x-agro.table-cell>{{ $tr->toContainer?->name ?? '—' }}</x-agro.table-cell>
                        <x-agro.table-cell>{{ number_format($tr->quantity, 1) }} {{ $tr->unitOfMeasurement?->abbreviation }}</x-agro.table-cell>
                        <x-agro.table-cell>{{ $tr->oenologist?->full_name ?? '—' }}</x-agro.table-cell>
                        <x-agro.table-cell>{{ $tr->notes ? \Str::limit($tr->notes, 40) : '—' }}</x-agro.table-cell>
                        <x-agro.table-cell align="right">
                            <flux:button size="sm" variant="ghost" icon="trash"
                                wire:click="deleteTransfer({{ $tr->id }})"
                                wire:loading.attr="disabled"
                                wire:confirm="¿Eliminar este trasvase? Se revertirán los cambios de capacidad." />
                        </x-agro.table-cell>
                    </x-agro.table-row>
                @empty
                    <x-agro.empty-state icon="arrows-right-left" title="Sin trasvases registrados"
                        description="Registra los movimientos de vino entre depósitos y barricas." />
                @endforelse
            </x-agro.data-table>
        </x-agro.card>
    </div>

    {{-- ════════════════════════════════════════════════════════════════════════
         TAB: MERMAS
    ════════════════════════════════════════════════════════════════════════════ --}}
    <div x-show="tab === 'losses'" x-cloak>
        <x-agro.card>
            <x-agro.data-table :headers="['Fecha', 'Tipo', 'Depósito', 'Cantidad', 'Notas', '']">
                @forelse($losses as $lo)
                    <x-agro.table-row>
                        <x-agro.table-cell>{{ $lo->loss_date->format('d/m/Y') }}</x-agro.table-cell>
                        <x-agro.table-cell>
                            <x-agro.status-badge :label="$lo->loss_type_label" color="red" />
                        </x-agro.table-cell>
                        <x-agro.table-cell>{{ $lo->container?->name ?? '—' }}</x-agro.table-cell>
                        <x-agro.table-cell class="text-red-600 font-medium">
                            −{{ number_format($lo->quantity, 1) }} {{ $lo->unitOfMeasurement?->abbreviation }}
                        </x-agro.table-cell>
                        <x-agro.table-cell>{{ $lo->notes ? \Str::limit($lo->notes, 40) : '—' }}</x-agro.table-cell>
                        <x-agro.table-cell align="right">
                            <flux:button size="sm" variant="ghost" icon="trash"
                                wire:click="deleteLoss({{ $lo->id }})"
                                wire:loading.attr="disabled"
                                wire:confirm="¿Eliminar esta merma?" />
                        </x-agro.table-cell>
                    </x-agro.table-row>
                @empty
                    <x-agro.empty-state icon="minus-circle" title="Sin mermas registradas"
                        description="Registra pérdidas por evaporación, filtración, muestreo o derrames." />
                @endforelse
            </x-agro.data-table>
        </x-agro.card>
    </div>

    {{-- ════════════════════════════════════════════════════════════════════════
         TAB: ANÁLISIS
    ════════════════════════════════════════════════════════════════════════════ --}}
    <div x-show="tab === 'analyses'" x-cloak>
        <x-agro.card>
            <x-agro.data-table :headers="['Fecha', 'Tipo', 'Enólogo', 'Alcohol', 'pH', 'AT', 'AV', 'SO₂ L', 'SO₂ T', 'Ác. Málico', 'Notas', '']">
                @forelse($analyses as $an)
                    <x-agro.table-row>
                        <x-agro.table-cell>{{ $an->analysis_date->format('d/m/Y') }}</x-agro.table-cell>
                        <x-agro.table-cell>
                            <x-agro.status-badge :label="$an->analysis_type_label" color="emerald" />
                        </x-agro.table-cell>
                        <x-agro.table-cell>{{ $an->oenologist?->full_name ?? '—' }}</x-agro.table-cell>
                        <x-agro.table-cell>{{ $an->alcohol !== null ? $an->alcohol . '%' : '—' }}</x-agro.table-cell>
                        <x-agro.table-cell>{{ $an->ph ?? '—' }}</x-agro.table-cell>
                        <x-agro.table-cell>{{ $an->total_acidity !== null ? $an->total_acidity . ' g/L' : '—' }}</x-agro.table-cell>
                        <x-agro.table-cell>{{ $an->volatile_acidity !== null ? $an->volatile_acidity . ' g/L' : '—' }}</x-agro.table-cell>
                        <x-agro.table-cell>
                            @if($an->so2_free !== null)
                                <span class="{{ $an->isSo2Freelow() ? 'text-red-600 font-medium' : '' }}">
                                    {{ $an->so2_free }}{{ $an->isSo2Freelow() ? ' ⚠' : '' }}
                                </span>
                            @else —
                            @endif
                        </x-agro.table-cell>
                        <x-agro.table-cell>{{ $an->so2_total ?? '—' }}</x-agro.table-cell>
                        <x-agro.table-cell>{{ $an->malic_acid !== null ? $an->malic_acid . ' g/L' : '—' }}</x-agro.table-cell>
                        <x-agro.table-cell>{{ $an->notes ? \Str::limit($an->notes, 30) : '—' }}</x-agro.table-cell>
                        <x-agro.table-cell align="right">
                            <flux:button size="sm" variant="ghost" icon="trash"
                                wire:click="deleteAnalysis({{ $an->id }})"
                                wire:loading.attr="disabled"
                                wire:confirm="¿Eliminar este análisis?" />
                        </x-agro.table-cell>
                    </x-agro.table-row>
                @empty
                    <x-agro.empty-state icon="document-magnifying-glass" title="Sin análisis registrados"
                        description="Añade análisis propios o de laboratorio externo." />
                @endforelse
            </x-agro.data-table>
        </x-agro.card>
    </div>


    {{-- ════════════════════════════════════════════════════════════════════════
         MODALS
    ════════════════════════════════════════════════════════════════════════════ --}}

    {{-- Modal: Control de fermentación --}}
    <flux:modal name="modal-fermentation" class="w-full max-w-lg">
        <div class="p-6 space-y-5">
            <div>
                <flux:heading size="lg">Control de fermentación</flux:heading>
                <flux:subheading>Registra la lectura de parámetros del depósito.</flux:subheading>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <flux:field class="col-span-2">
                    <flux:label required>Depósito</flux:label>
                    <flux:select wire:model="fc_container_id">
                        <option value="">Seleccionar depósito...</option>
                        @foreach($containers as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="fc_container_id" />
                </flux:field>

                <flux:field class="col-span-2">
                    <flux:label required>Fecha y hora</flux:label>
                    <flux:input wire:model="fc_control_date" type="datetime-local" />
                    <flux:error name="fc_control_date" />
                </flux:field>

                <flux:field>
                    <flux:label>Temperatura (°C)</flux:label>
                    <flux:input wire:model="fc_temperature" type="number" step="0.1" placeholder="22.0" />
                    <flux:error name="fc_temperature" />
                </flux:field>

                <flux:field>
                    <flux:label>Densidad (g/L)</flux:label>
                    <flux:input wire:model="fc_density" type="number" step="0.001" placeholder="1.045" />
                    <flux:error name="fc_density" />
                </flux:field>

                <flux:field>
                    <flux:label>°Brix</flux:label>
                    <flux:input wire:model="fc_brix" type="number" step="0.1" placeholder="10.5" />
                    <flux:error name="fc_brix" />
                </flux:field>

                <flux:field>
                    <flux:label>pH</flux:label>
                    <flux:input wire:model="fc_ph" type="number" step="0.01" placeholder="3.50" />
                    <flux:error name="fc_ph" />
                </flux:field>

                <flux:field class="col-span-2">
                    <flux:label>Acidez volátil (g/L)</flux:label>
                    <flux:input wire:model="fc_va" type="number" step="0.01" placeholder="0.30" />
                    <flux:error name="fc_va" />
                </flux:field>

                <flux:field class="col-span-2">
                    <flux:label>Notas</flux:label>
                    <flux:textarea wire:model="fc_notes" rows="2" placeholder="Observaciones..." />
                </flux:field>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <flux:modal.close><flux:button variant="ghost">Cancelar</flux:button></flux:modal.close>
                <flux:button variant="primary" wire:click="saveFermentationControl">Guardar control</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Modal: Trasvase --}}
    <flux:modal name="modal-transfer" class="w-full max-w-lg">
        <div class="p-6 space-y-5">
            <div>
                <flux:heading size="lg">Registrar trasvase</flux:heading>
                <flux:subheading>Movimiento de vino entre contenedores.</flux:subheading>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>Tipo</flux:label>
                    <flux:select wire:model="tr_type">
                        @foreach(\App\Models\WineTransfer::TRANSFER_TYPES as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                </flux:field>

                <flux:field>
                    <flux:label required>Fecha</flux:label>
                    <flux:input wire:model="tr_date" type="date" />
                    <flux:error name="tr_date" />
                </flux:field>

                <flux:field>
                    <flux:label>Origen (vacío = externo)</flux:label>
                    <flux:select wire:model="tr_from_container_id">
                        <option value="">Sin origen / Externo</option>
                        @foreach($containers as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </flux:select>
                </flux:field>

                <flux:field>
                    <flux:label required>Destino</flux:label>
                    <flux:select wire:model="tr_to_container_id">
                        <option value="">Seleccionar destino...</option>
                        @foreach($containers as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="tr_to_container_id" />
                </flux:field>

                <flux:field>
                    <flux:label required>Cantidad</flux:label>
                    <flux:input wire:model="tr_quantity" type="number" step="0.001" min="0" placeholder="0" />
                    <flux:error name="tr_quantity" />
                </flux:field>

                <flux:field>
                    <flux:label required>Unidad</flux:label>
                    <flux:select wire:model="tr_unit_id">
                        <option value="">Seleccionar...</option>
                        @foreach($units as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="tr_unit_id" />
                </flux:field>

                <flux:field class="col-span-2">
                    <flux:label>Enólogo responsable</flux:label>
                    <flux:select wire:model="tr_oenologist_id">
                        <option value="">Sin asignar</option>
                        @foreach($oenologists as $oe)
                            <option value="{{ $oe->id }}">{{ $oe->full_name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="tr_oenologist_id" />
                </flux:field>

                <flux:field class="col-span-2">
                    <flux:label>Notas</flux:label>
                    <flux:textarea wire:model="tr_notes" rows="2" placeholder="Motivo del trasvase..." />
                </flux:field>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <flux:modal.close><flux:button variant="ghost">Cancelar</flux:button></flux:modal.close>
                <flux:button variant="primary" wire:click="saveTransfer">Guardar trasvase</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Modal: Merma --}}
    <flux:modal name="modal-loss" class="w-full max-w-lg">
        <div class="p-6 space-y-5">
            <div>
                <flux:heading size="lg">Registrar merma</flux:heading>
                <flux:subheading>Pérdida de volumen durante la elaboración.</flux:subheading>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <flux:field>
                    <flux:label required>Tipo de merma</flux:label>
                    <flux:select wire:model="lo_type">
                        @foreach(\App\Models\WineLoss::LOSS_TYPES as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                </flux:field>

                <flux:field>
                    <flux:label required>Fecha</flux:label>
                    <flux:input wire:model="lo_date" type="date" />
                    <flux:error name="lo_date" />
                </flux:field>

                <flux:field>
                    <flux:label>Depósito (opcional)</flux:label>
                    <flux:select wire:model="lo_container_id">
                        <option value="">Sin depósito específico</option>
                        @foreach($containers as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </flux:select>
                </flux:field>

                <flux:field>
                    <flux:label required>Cantidad</flux:label>
                    <flux:input wire:model="lo_quantity" type="number" step="0.001" min="0" placeholder="0" />
                    <flux:error name="lo_quantity" />
                </flux:field>

                <flux:field>
                    <flux:label required>Unidad</flux:label>
                    <flux:select wire:model="lo_unit_id">
                        <option value="">Seleccionar...</option>
                        @foreach($units as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="lo_unit_id" />
                </flux:field>

                <flux:field class="col-span-2">
                    <flux:label>Notas</flux:label>
                    <flux:textarea wire:model="lo_notes" rows="2" placeholder="Causa de la merma..." />
                </flux:field>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <flux:modal.close><flux:button variant="ghost">Cancelar</flux:button></flux:modal.close>
                <flux:button variant="primary" wire:click="saveLoss">Guardar merma</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Modal: Análisis --}}
    <flux:modal name="modal-analysis" class="w-full max-w-2xl">
        <div class="p-6 space-y-5">
            <div>
                <flux:heading size="lg">Añadir análisis</flux:heading>
                <flux:subheading>Registra los parámetros fisicoquímicos del vino.</flux:subheading>
            </div>

            <div class="grid grid-cols-2 gap-4">
                {{-- Cabecera --}}
                <flux:field>
                    <flux:label required>Tipo</flux:label>
                    <flux:select wire:model="an_type">
                        <option value="own">Análisis propio</option>
                        <option value="external">Laboratorio externo</option>
                    </flux:select>
                </flux:field>

                <flux:field>
                    <flux:label required>Fecha</flux:label>
                    <flux:input wire:model="an_date" type="date" />
                    <flux:error name="an_date" />
                </flux:field>

                <flux:field>
                    <flux:label>Depósito</flux:label>
                    <flux:select wire:model="an_container_id">
                        <option value="">Sin depósito</option>
                        @foreach($containers as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </flux:select>
                </flux:field>

                <flux:field>
                    <flux:label>Laboratorio</flux:label>
                    <flux:input wire:model="an_laboratory" type="text" placeholder="Nombre del lab." />
                </flux:field>

                <flux:field class="col-span-2 md:col-span-4">
                    <flux:label>Enólogo responsable</flux:label>
                    <flux:select wire:model="an_oenologist_id">
                        <option value="">Sin asignar</option>
                        @foreach($oenologists as $oe)
                            <option value="{{ $oe->id }}">{{ $oe->full_name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="an_oenologist_id" />
                </flux:field>

                {{-- Parámetros fisicoquímicos --}}
                <flux:field>
                    <flux:label>Alcohol (% vol)</flux:label>
                    <flux:input wire:model="an_alcohol" type="number" step="0.01" placeholder="12.50" />
                </flux:field>

                <flux:field>
                    <flux:label>Azúcar res. (g/L)</flux:label>
                    <flux:input wire:model="an_residual_sugar" type="number" step="0.1" placeholder="2.5" />
                </flux:field>

                <flux:field>
                    <flux:label>Acid. total (g/L)</flux:label>
                    <flux:input wire:model="an_total_acidity" type="number" step="0.1" placeholder="5.5" />
                </flux:field>

                <flux:field>
                    <flux:label>Acid. volátil (g/L)</flux:label>
                    <flux:input wire:model="an_volatile_acidity" type="number" step="0.01" placeholder="0.35" />
                </flux:field>

                <flux:field>
                    <flux:label>pH</flux:label>
                    <flux:input wire:model="an_ph" type="number" step="0.01" placeholder="3.50" />
                </flux:field>

                <flux:field>
                    <flux:label>SO₂ libre (mg/L)</flux:label>
                    <flux:input wire:model="an_so2_free" type="number" step="0.1" placeholder="25" />
                </flux:field>

                <flux:field>
                    <flux:label>SO₂ total (mg/L)</flux:label>
                    <flux:input wire:model="an_so2_total" type="number" step="0.1" placeholder="80" />
                </flux:field>

                <flux:field>
                    <flux:label>Densidad (g/cm³)</flux:label>
                    <flux:input wire:model="an_density" type="number" step="0.0001" placeholder="0.9950" />
                </flux:field>

                <flux:field>
                    <flux:label>Turbidez (NTU)</flux:label>
                    <flux:input wire:model="an_turbidity" type="number" step="0.1" placeholder="0.5" />
                </flux:field>

                <flux:field>
                    <flux:label>Intensidad color</flux:label>
                    <flux:input wire:model="an_color_intensity" type="number" step="0.001" placeholder="0.850" />
                </flux:field>

                <flux:field>
                    <flux:label>Ácido málico (g/L)</flux:label>
                    <flux:input wire:model="an_malic_acid" type="number" step="0.01" placeholder="1.20" />
                    <flux:description>Seguimiento FML</flux:description>
                </flux:field>

                <flux:field class="col-span-2 md:col-span-4">
                    <flux:label>Notas</flux:label>
                    <flux:textarea wire:model="an_notes" rows="2" placeholder="Observaciones del análisis..." />
                </flux:field>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <flux:modal.close><flux:button variant="ghost">Cancelar</flux:button></flux:modal.close>
                <flux:button variant="primary" wire:click="saveAnalysis">Guardar análisis</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- ── Modal: aditivo enológico ───────────────────────────────────────── --}}
    <flux:modal name="modal-additive" class="w-full max-w-lg">
        <div class="p-6 space-y-5">
            <div>
                <h3 class="text-base font-semibold text-zinc-900">Registrar aditivo</h3>
                <p class="text-sm text-zinc-500 mt-1">Al guardar se descontará del stock del insumo si está vinculado.</p>
            </div>

            {{-- Insumo de bodega (opcional) --}}
            <flux:field>
                <flux:label>Insumo de bodega</flux:label>
                <flux:select wire:model.live="ad_supply_id">
                    <flux:select.option value="">Introducir manualmente...</flux:select.option>
                    @foreach($supplies as $supply)
                        <flux:select.option value="{{ $supply->id }}">
                            {{ $supply->name }}
                            @if($supply->commercial_name) ({{ $supply->commercial_name }})@endif
                            · Stock: {{ number_format($supply->current_stock, 2) }} {{ $supply->unitOfMeasurement?->abbreviation }}
                        </flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="ad_supply_id" />
            </flux:field>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <flux:field class="sm:col-span-2">
                    <flux:label>Nombre del aditivo <span class="text-red-500">*</span></flux:label>
                    <flux:input wire:model="ad_additive_name" placeholder="Ej. Bentonita, SO₂, Levadura EC1118..." />
                    <flux:error name="ad_additive_name" />
                </flux:field>

                <flux:field>
                    <flux:label>Cantidad <span class="text-red-500">*</span></flux:label>
                    <flux:input wire:model="ad_quantity" type="number" step="0.001" min="0.001" placeholder="0" />
                    <flux:error name="ad_quantity" />
                </flux:field>

                <flux:field>
                    <flux:label>Unidad <span class="text-red-500">*</span></flux:label>
                    <flux:select wire:model="ad_unit_id">
                        <flux:select.option value="">Seleccionar...</flux:select.option>
                        @foreach($units as $unit)
                            <flux:select.option value="{{ $unit->id }}">{{ $unit->abbreviation }} — {{ $unit->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="ad_unit_id" />
                </flux:field>

                <flux:field>
                    <flux:label>Fecha aplicación <span class="text-red-500">*</span></flux:label>
                    <flux:input wire:model="ad_date" type="date" />
                    <flux:error name="ad_date" />
                </flux:field>

                <flux:field>
                    <flux:label>Etapa del proceso</flux:label>
                    <flux:select wire:model="ad_process_detail_id">
                        <flux:select.option value="">Sin etapa específica</flux:select.option>
                        @foreach($processes as $process)
                            <flux:select.option value="{{ $process->id }}">
                                {{ $process->process_type_label }} · {{ $process->start_date?->format('d/m/Y') }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="ad_process_detail_id" />
                </flux:field>

                <flux:field class="sm:col-span-2">
                    <flux:label>Enólogo responsable</flux:label>
                    <flux:select wire:model="ad_oenologist_id">
                        <flux:select.option value="">Sin asignar</flux:select.option>
                        @foreach($oenologists as $oenologist)
                            <flux:select.option value="{{ $oenologist->id }}">{{ $oenologist->full_name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="ad_oenologist_id" />
                </flux:field>

                <flux:field class="sm:col-span-2">
                    <flux:label>Observaciones</flux:label>
                    <flux:textarea wire:model="ad_notes" rows="2" />
                    <flux:error name="ad_notes" />
                </flux:field>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <flux:modal.close><flux:button variant="ghost">Cancelar</flux:button></flux:modal.close>
                <flux:button variant="primary" wire:click="saveAdditive">Registrar aditivo</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- ── Modal: vincular recepción de uva ────────────────────────────────── --}}
    <flux:modal name="modal-composition" class="w-full max-w-lg">
        <div class="p-6 space-y-5">
            <div>
                <h3 class="text-base font-semibold text-zinc-900">Vincular recepción de uva</h3>
                <p class="text-sm text-zinc-500 mt-1">Selecciona una recepción de uva y la cantidad aportada a este lote.</p>
            </div>

            <flux:field>
                <flux:label>Recepción de uva <span class="text-red-500">*</span></flux:label>
                <flux:select wire:model="co_harvest_id">
                    <flux:select.option value="">Seleccionar recepción...</flux:select.option>
                    @foreach($availableHarvests as $h)
                        @php
                            $label = ($h->plotPlanting?->plotVariety?->grapeVariety?->name ?? 'Sin variedad')
                                   . ' · ' . ($h->harvest_start_date?->format('d/m/Y') ?? '—')
                                   . ' · ' . number_format($h->total_weight ?? 0, 0) . ' kg';
                        @endphp
                        <flux:select.option value="{{ $h->id }}">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="co_harvest_id" />
            </flux:field>

            <flux:field>
                <flux:label>Cantidad aportada (kg) <span class="text-red-500">*</span></flux:label>
                <flux:input wire:model="co_quantity_kg" type="number" step="0.001" min="0.001" placeholder="0" />
                <flux:error name="co_quantity_kg" />
            </flux:field>

            <div class="flex justify-end gap-3 pt-2">
                <flux:modal.close><flux:button variant="ghost">Cancelar</flux:button></flux:modal.close>
                <flux:button variant="primary" wire:click="linkHarvest">Vincular</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- ════════════════════════════════════════════════════════════════════════
         TAB: COSTES DE PRODUCCIÓN
    ════════════════════════════════════════════════════════════════════════════ --}}
    <div x-show="tab === 'costs'" x-cloak>
        @php
            $grapeCost  = $wine->grape_cost;
            $manualCost = $costs->sum('amount');
            $totalCost  = $grapeCost + $manualCost;
            $volume     = (float) $wine->volume_liters;
            $costPerL   = $volume > 0 ? $totalCost / $volume : null;
        @endphp

        {{-- KPIs --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <x-agro.card class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-emerald-50 flex items-center justify-center shrink-0">
                    <flux:icon icon="archive-box-arrow-down" class="size-4 text-emerald-600" />
                </div>
                <div>
                    <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest">Coste uva</p>
                    <p class="text-lg font-bold text-zinc-900 leading-none">
                        {{ $grapeCost > 0 ? number_format($grapeCost, 2, ',', '.') . ' €' : '—' }}
                    </p>
                </div>
            </x-agro.card>
            <x-agro.card class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-violet-50 flex items-center justify-center shrink-0">
                    <flux:icon icon="receipt-percent" class="size-4 text-violet-600" />
                </div>
                <div>
                    <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest">Costes extras</p>
                    <p class="text-lg font-bold text-zinc-900 leading-none">
                        {{ $manualCost > 0 ? number_format($manualCost, 2, ',', '.') . ' €' : '—' }}
                    </p>
                </div>
            </x-agro.card>
            <x-agro.card class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-zinc-100 flex items-center justify-center shrink-0">
                    <flux:icon icon="currency-euro" class="size-4 text-zinc-600" />
                </div>
                <div>
                    <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest">Total</p>
                    <p class="text-lg font-bold text-zinc-900 leading-none">
                        {{ $totalCost > 0 ? number_format($totalCost, 2, ',', '.') . ' €' : '—' }}
                    </p>
                </div>
            </x-agro.card>
            <x-agro.card class="flex items-center gap-3">
                @php
                    $plColor = $costPerL === null ? 'zinc'
                        : ($costPerL < 0.5 ? 'emerald' : ($costPerL < 1.5 ? 'amber' : 'red'));
                @endphp
                <div class="w-9 h-9 rounded-lg bg-{{ $plColor }}-50 flex items-center justify-center shrink-0">
                    <flux:icon icon="beaker" class="size-4 text-{{ $plColor }}-600" />
                </div>
                <div>
                    <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest">€ / Litro</p>
                    <p class="text-lg font-bold text-{{ $plColor }}-700 leading-none">
                        {{ $costPerL !== null ? number_format($costPerL, 3, ',', '.') . ' €' : '—' }}
                    </p>
                </div>
            </x-agro.card>
        </div>

        {{-- Tabla de costes manuales --}}
        <x-agro.card>
            <div class="flex items-center justify-between mb-4">
                <p class="text-sm font-semibold text-zinc-700">Costes manuales registrados</p>
                <a href="{{ roleRoute('production-costs.create', ['wineId' => $wine->id]) }}" wire:navigate>
                    <flux:button variant="outline" icon="plus" size="sm">Añadir coste</flux:button>
                </a>
            </div>

            @if($costs->isEmpty())
                <x-agro.empty-state
                    icon="receipt-percent"
                    title="Sin costes manuales"
                    description="Registra aditivos, análisis, embotellado, etiquetado u otros costes asociados a este vino."
                >
                    <x-slot:action>
                        <a href="{{ roleRoute('production-costs.create', ['wineId' => $wine->id]) }}" wire:navigate>
                            <flux:button variant="primary" icon="plus" size="sm">Añadir coste</flux:button>
                        </a>
                    </x-slot:action>
                </x-agro.empty-state>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-zinc-100 text-left text-xs font-semibold text-zinc-400 uppercase tracking-wider">
                                <th class="py-2 px-3">Fecha</th>
                                <th class="py-2 px-3">Categoría</th>
                                <th class="py-2 px-3">Descripción</th>
                                <th class="py-2 px-3">Proveedor</th>
                                <th class="py-2 px-3 text-right">Importe</th>
                                <th class="py-2 px-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-50">
                            @foreach($costs as $cost)
                                @php
                                    $catColor = \App\Models\WineCost::CATEGORY_COLORS[$cost->category] ?? 'zinc';
                                    $catLabel = \App\Models\WineCost::CATEGORIES[$cost->category] ?? $cost->category;
                                @endphp
                                <tr class="hover:bg-zinc-50 transition-colors" wire:key="cost-{{ $cost->id }}">
                                    <td class="py-2 px-3 text-zinc-500 whitespace-nowrap">{{ $cost->cost_date->format('d/m/Y') }}</td>
                                    <td class="py-2 px-3">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-{{ $catColor }}-100 text-{{ $catColor }}-700">
                                            {{ $catLabel }}
                                        </span>
                                    </td>
                                    <td class="py-2 px-3 text-zinc-700">{{ $cost->description }}</td>
                                    <td class="py-2 px-3 text-zinc-500">{{ $cost->supplier ?? '—' }}</td>
                                    <td class="py-2 px-3 text-right font-semibold text-zinc-900 whitespace-nowrap">
                                        {{ number_format($cost->amount, 2, ',', '.') }} €
                                    </td>
                                    <td class="py-2 px-3 text-right">
                                        <a href="{{ roleRoute('production-costs.edit', $cost) }}" wire:navigate
                                           class="inline-flex items-center justify-center w-7 h-7 rounded-lg text-zinc-300 hover:text-violet-600 hover:bg-violet-50 transition-colors">
                                            <flux:icon icon="pencil-square" class="size-3.5" />
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="border-t-2 border-zinc-200">
                                <td colspan="4" class="py-2 px-3 text-xs font-semibold text-zinc-500 uppercase tracking-wide">Total costes manuales</td>
                                <td class="py-2 px-3 text-right font-bold text-zinc-900">
                                    {{ number_format($manualCost, 2, ',', '.') }} €
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="mt-4 pt-3 border-t border-zinc-100 text-right">
                    <a href="{{ roleRoute('production-costs.index') }}" wire:navigate
                       class="text-xs text-violet-600 hover:underline">
                        Ver módulo de costes completo →
                    </a>
                </div>
            @endif
        </x-agro.card>
    </div>

    {{-- ════════════════════════════════════════════════════════════════════════
         TAB: DIAGRAMA
    ════════════════════════════════════════════════════════════════════════════ --}}
    <div x-show="tab === 'diagram'" x-cloak
        x-effect="if (tab === 'diagram') { $nextTick(() => { if (window.mermaid) window.mermaid.run({ nodes: $el.querySelectorAll('.mermaid') }) }) }">

        @if(empty(trim($diagram)))
            <x-agro.empty-state icon="share" title="Sin datos para el diagrama"
                description="Añade etapas de proceso, trasvases o recepciones de uva para ver el diagrama." />
        @else
            <x-agro.card>
                <div class="overflow-x-auto">
                    <div class="mermaid text-center py-4">{{ $diagram }}</div>
                </div>

                {{-- Leyenda --}}
                <div class="mt-4 flex flex-wrap gap-3 text-xs text-zinc-500">
                    <span class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-full bg-emerald-200 border border-emerald-500 inline-block"></span> Uva / Recepción
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-full bg-violet-200 border border-violet-500 inline-block"></span> Vino
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-full bg-blue-200 border border-blue-500 inline-block"></span> Etapa proceso
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-full bg-yellow-200 border border-yellow-500 inline-block"></span> Trasvase
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-full bg-red-200 border border-red-500 inline-block"></span> Merma
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-full bg-amber-100 border border-amber-500 inline-block"></span> Análisis
                    </span>
                </div>
            </x-agro.card>
        @endif
    </div>

    {{-- ════════════════════════════════════════════════════════════════════════
         TAB: QR TRAZABILIDAD
    ════════════════════════════════════════════════════════════════════════════ --}}
    <div x-show="tab === 'qr'" x-cloak>
        <x-agro.card>
            <div class="flex flex-col items-center gap-6 py-4">

                {{-- QR SVG --}}
                <div class="p-3 bg-white rounded-2xl border border-zinc-200 shadow-sm">
                    {!! $qrSvg !!}
                </div>

                {{-- Info --}}
                <div class="text-center space-y-2 max-w-sm">
                    <p class="text-sm font-semibold text-zinc-800">Ficha pública de trazabilidad</p>
                    <p class="text-xs text-zinc-500">
                        Escanea este código para ver la información pública de <strong>{{ $wine->name }}</strong>:
                        composición varietal, análisis enológicos y datos de elaboración.
                    </p>
                    <a href="{{ $traceUrl }}" target="_blank"
                        class="inline-flex items-center gap-1.5 text-xs font-mono text-violet-600 hover:underline break-all">
                        <flux:icon icon="arrow-top-right-on-square" class="size-3.5 shrink-0" />
                        {{ $traceUrl }}
                    </a>
                </div>

                {{-- Acciones --}}
                <div class="flex gap-3">
                    <a href="{{ $traceUrl }}" target="_blank">
                        <flux:button variant="ghost" icon="arrow-top-right-on-square" size="sm">
                            Ver ficha pública
                        </flux:button>
                    </a>
                    <flux:button
                        x-on:click="
                            const svg = $el.closest('[x-show]').querySelector('svg');
                            const blob = new Blob([svg.outerHTML], {type: 'image/svg+xml'});
                            const a = document.createElement('a');
                            a.href = URL.createObjectURL(blob);
                            a.download = '{{ Str::slug($wine->name) }}_qr.svg';
                            a.click();
                        "
                        variant="outline" icon="arrow-down-tray" size="sm">
                        Descargar QR
                    </flux:button>
                </div>

            </div>
        </x-agro.card>
    </div>

    @once
    <script type="module">
        import mermaid from 'https://cdn.jsdelivr.net/npm/mermaid@11/dist/mermaid.esm.min.mjs';
        mermaid.initialize({ startOnLoad: false, theme: 'base', themeVariables: { fontFamily: 'inherit' } });
        window.mermaid = mermaid;
    </script>
    @endonce

</div>
