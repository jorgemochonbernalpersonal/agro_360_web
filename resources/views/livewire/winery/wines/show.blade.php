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
                href="{{ route('winery.wines.edit', $wine) }}" wire:navigate>
                Editar
            </flux:button>
            <flux:button variant="ghost" icon="arrow-left"
                href="{{ route('winery.wines.index') }}" wire:navigate>
                Volver
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- ── Stat cards ───────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
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
    </div>

    {{-- ── Tabs ─────────────────────────────────────────────────────────────── --}}
    <div class="border-b border-zinc-200 dark:border-zinc-700">
        <nav class="flex gap-0 -mb-px overflow-x-auto">
            @foreach([
                ['id' => 'timeline',     'label' => 'Timeline',       'icon' => 'clock'],
                ['id' => 'fermentation', 'label' => 'Fermentación',   'icon' => 'fire'],
                ['id' => 'transfers',    'label' => 'Trasvases',      'icon' => 'arrows-right-left'],
                ['id' => 'losses',       'label' => 'Mermas',         'icon' => 'minus-circle'],
                ['id' => 'analyses',     'label' => 'Análisis',       'icon' => 'document-magnifying-glass'],
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
                                                wire:confirm="¿Eliminar este trasvase? Se revertirán los cambios de capacidad." />
                                        </div>
                                        <p class="text-xs text-zinc-600 dark:text-zinc-400 mt-1">
                                            {{ $m->fromContainer?->name ?? 'Origen externo' }}
                                            <flux:icon icon="arrow-right" class="inline w-3 h-3 mx-1" />
                                            {{ $m->toContainer?->name }}
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
                                            </div>
                                            <flux:button size="xs" variant="ghost" icon="trash"
                                                wire:click="deleteAnalysis({{ $m->id }})"
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
            <x-agro.data-table :headers="['Fecha', 'Tipo', 'Origen', 'Destino', 'Cantidad', 'Notas', '']">
                @forelse($transfers as $tr)
                    <x-agro.table-row>
                        <x-agro.table-cell>{{ $tr->transfer_date->format('d/m/Y') }}</x-agro.table-cell>
                        <x-agro.table-cell>
                            <x-agro.status-badge :label="$tr->transfer_type_label" color="purple" />
                        </x-agro.table-cell>
                        <x-agro.table-cell>{{ $tr->fromContainer?->name ?? 'Externo' }}</x-agro.table-cell>
                        <x-agro.table-cell>{{ $tr->toContainer?->name ?? '—' }}</x-agro.table-cell>
                        <x-agro.table-cell>{{ number_format($tr->quantity, 1) }} {{ $tr->unitOfMeasurement?->abbreviation }}</x-agro.table-cell>
                        <x-agro.table-cell>{{ $tr->notes ? \Str::limit($tr->notes, 40) : '—' }}</x-agro.table-cell>
                        <x-agro.table-cell align="right">
                            <flux:button size="sm" variant="ghost" icon="trash"
                                wire:click="deleteTransfer({{ $tr->id }})"
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
            <x-agro.data-table :headers="['Fecha', 'Tipo', 'Alcohol', 'pH', 'AT', 'AV', 'SO₂ L', 'SO₂ T', 'Notas', '']">
                @forelse($analyses as $an)
                    <x-agro.table-row>
                        <x-agro.table-cell>{{ $an->analysis_date->format('d/m/Y') }}</x-agro.table-cell>
                        <x-agro.table-cell>
                            <x-agro.status-badge :label="$an->analysis_type_label" color="emerald" />
                        </x-agro.table-cell>
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
                        <x-agro.table-cell>{{ $an->notes ? \Str::limit($an->notes, 30) : '—' }}</x-agro.table-cell>
                        <x-agro.table-cell align="right">
                            <flux:button size="sm" variant="ghost" icon="trash"
                                wire:click="deleteAnalysis({{ $an->id }})"
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

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
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

                {{-- Parámetros --}}
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

</div>
