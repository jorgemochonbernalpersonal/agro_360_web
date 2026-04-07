<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        :title="$container->name"
        :description="($container->containerType?->name ?? 'Contenedor') . ($container->serial_number ? ' · S/N: ' . $container->serial_number : '')"
    >
        <x-slot:actions>
            @if($container->wine_volume_liters > 0)
                <flux:button wire:click="openAdjustModal" variant="ghost" icon="adjustments-horizontal">
                    Ajustar stock
                </flux:button>
                <flux:button
                    wire:click="emptyWine"
                    wire:confirm="¿Vaciar todo el vino elaborado de este contenedor? Se registrará en el historial."
                    variant="ghost"
                    icon="trash"
                    class="text-red-500 hover:text-red-700"
                >
                    Vaciar vino
                </flux:button>
            @endif
            <flux:button href="{{ roleRoute('containers.edit', $container) }}" variant="ghost" icon="pencil">
                Editar
            </flux:button>
            <flux:button href="{{ roleRoute('containers.maintenance.index', $container) }}" variant="ghost" icon="wrench-screwdriver">
                Mantenimientos
            </flux:button>
            <flux:button href="{{ roleRoute('containers.additives.index', $container) }}" variant="outline" icon="beaker">
                Aditivos
            </flux:button>
            @if($fromVisual)
                <flux:button href="{{ roleRoute('visual') }}" variant="outline" icon="map">
                    Volver al Mapa
                </flux:button>
            @else
                <flux:button href="{{ roleRoute('containers.index') }}" variant="outline" icon="arrow-left">
                    Volver
                </flux:button>
            @endif
        </x-slot:actions>
    </x-agro.page-header>

    {{-- ── KPIs superiores ─────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 gap-4">
        <x-agro.stat-card
            label="Capacidad total"
            :value="number_format($container->capacity, 0) . ' kg'"
            icon="cube"
            color="zinc"
        />
        <x-agro.stat-card
            label="Uva / cosecha (kg)"
            :value="number_format($container->used_capacity, 0)"
            icon="scale"
            color="amber"
        />
        <x-agro.stat-card
            label="Vino elaborado (L)"
            :value="number_format($container->wine_volume_liters, 1)"
            icon="beaker"
            color="violet"
        />
        <x-agro.stat-card
            label="Capacidad libre"
            :value="number_format(max(0, $container->capacity - $container->used_capacity - $container->wine_volume_liters), 0) . ' kg'"
            icon="arrow-trending-down"
            color="agro"
        />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ── Columna izquierda: info + estado actual ─────────────────── --}}
        <div class="space-y-6">

            {{-- Info del contenedor --}}
            <x-agro.card title="Información">
                <dl class="space-y-3 text-sm">
                    @if($container->containerRoom)
                    <div class="flex justify-between">
                        <dt class="text-zinc-400">Sala / Ubicación</dt>
                        <dd class="text-zinc-700 font-medium">{{ $container->containerRoom->name }}</dd>
                    </div>
                    @endif
                    @if($container->containerMaterial)
                    <div class="flex justify-between">
                        <dt class="text-zinc-400">Material</dt>
                        <dd class="text-zinc-700">{{ $container->containerMaterial->name }}</dd>
                    </div>
                    @endif
                    @if($container->oak_type)
                    <div class="flex justify-between">
                        <dt class="text-zinc-400">Tipo roble</dt>
                        <dd class="text-zinc-700">{{ $container->oak_type }}</dd>
                    </div>
                    @endif
                    @if($container->purchase_date)
                    <div class="flex justify-between">
                        <dt class="text-zinc-400">Fecha compra</dt>
                        <dd class="text-zinc-700">{{ $container->purchase_date->format('d/m/Y') }}</dd>
                    </div>
                    @endif
                    @if($container->next_maintenance_date)
                    <div class="flex justify-between">
                        <dt class="text-zinc-400">Próx. mantenimiento</dt>
                        <dd class="text-zinc-700 {{ $container->next_maintenance_date->isPast() ? 'text-red-600 font-semibold' : '' }}">
                            {{ $container->next_maintenance_date->format('d/m/Y') }}
                        </dd>
                    </div>
                    @endif
                    <div class="flex justify-between">
                        <dt class="text-zinc-400">Mantenimientos</dt>
                        <dd class="text-zinc-700">{{ $maintenanceCount }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-zinc-400">Aditivos aplicados</dt>
                        <dd class="text-zinc-700">{{ $additiveCount }}</dd>
                    </div>
                </dl>
            </x-agro.card>

            {{-- Barras de ocupación --}}
            <x-agro.card title="Ocupación">
                <div class="space-y-4">
                    <div>
                        <div class="flex justify-between text-xs text-zinc-500 mb-1">
                            <span class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-amber-400 inline-block"></span>
                                Uva / cosecha
                            </span>
                            <span>{{ number_format($container->used_capacity, 0) }} kg · {{ $harvestPct }}%</span>
                        </div>
                        <x-agro.progress-bar :percent="$harvestPct" color="amber" />
                    </div>
                    <div>
                        <div class="flex justify-between text-xs text-zinc-500 mb-1">
                            <span class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-violet-500 inline-block"></span>
                                Vino elaborado
                            </span>
                            <span>{{ number_format($container->wine_volume_liters, 1) }} L · {{ $winePct }}%</span>
                        </div>
                        <x-agro.progress-bar :percent="$winePct" color="violet" />
                    </div>
                </div>
            </x-agro.card>

            {{-- Estado actual --}}
            @if($container->currentState)
            <x-agro.card title="Estado actual">
                <dl class="space-y-3 text-sm">
                    @if($container->currentState->harvest)
                    <div>
                        <dt class="text-zinc-400 text-xs mb-1">Cosecha activa</dt>
                        <dd class="font-medium text-zinc-800">
                            {{ $container->currentState->harvest->batch?->grapeVariety?->name ?? '—' }}
                        </dd>
                        <dd class="text-xs text-zinc-400">{{ $container->currentState->harvest->batch?->viticulturist?->name ?? '' }}</dd>
                    </div>
                    @endif
                    @if($container->currentState->wine)
                    <div>
                        <dt class="text-zinc-400 text-xs mb-1">Vino</dt>
                        <dd class="font-medium text-zinc-800">{{ $container->currentState->wine->name }}</dd>
                        <dd class="text-xs text-zinc-400">{{ $container->currentState->wine->vintage_year ?? '' }}</dd>
                    </div>
                    @endif
                    @if($container->currentState->last_movement_at)
                    <div class="flex justify-between">
                        <dt class="text-zinc-400">Último movimiento</dt>
                        <dd class="text-zinc-600 text-xs">{{ $container->currentState->last_movement_at->diffForHumans() }}</dd>
                    </div>
                    @endif
                </dl>
            </x-agro.card>
            @endif

        </div>

        {{-- ── Columna central: historial ──────────────────────────────── --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Últimos trasvases --}}
            @if($recentTransfers->count())
            <x-agro.card title="Trasvases recientes">
                <div class="space-y-2">
                    @foreach($recentTransfers as $t)
                    @php
                        $isIn  = $t->to_container_id   === $container->id;
                        $isOut = $t->from_container_id === $container->id;
                    @endphp
                    <div class="flex items-center gap-3 p-3 rounded-lg bg-zinc-50 text-sm">
                        <div class="w-7 h-7 rounded-full flex items-center justify-center shrink-0
                            {{ $isIn ? 'bg-green-100' : 'bg-red-100' }}">
                            <flux:icon icon="{{ $isIn ? 'arrow-down' : 'arrow-up' }}"
                                class="size-3.5 {{ $isIn ? 'text-green-600' : 'text-red-500' }}" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-zinc-800 truncate">{{ $t->wine?->name ?? '—' }}</p>
                            <p class="text-xs text-zinc-400">
                                {{ $isIn ? 'Entrada desde ' . ($t->fromContainer?->name ?? 'exterior') : 'Salida hacia ' . ($t->toContainer?->name ?? '?') }}
                            </p>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="font-semibold {{ $isIn ? 'text-green-600' : 'text-red-500' }}">
                                {{ $isIn ? '+' : '-' }}{{ number_format($t->quantity, 1) }} L
                            </p>
                            <p class="text-xs text-zinc-400">{{ $t->transfer_date instanceof \Carbon\Carbon ? $t->transfer_date->format('d/m/Y') : $t->transfer_date }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="mt-3">
                    <flux:button href="{{ roleRoute('wine-transfers.index') }}" variant="ghost" size="sm" icon="arrows-right-left">
                        Ver todos los trasvases
                    </flux:button>
                </div>
            </x-agro.card>
            @endif

            {{-- Historial de movimientos --}}
            <x-agro.card title="Historial de movimientos">
                @if($history->count())
                <div class="space-y-1">
                    @foreach($history as $entry)
                    @php
                        $positive = $entry->quantity >= 0;
                        $typeColors = [
                            'wine_transfer_in'        => 'text-violet-600 bg-violet-50',
                            'wine_transfer_out'       => 'text-violet-400 bg-violet-50',
                            'wine_transfer_revert_in' => 'text-zinc-500 bg-zinc-50',
                            'wine_transfer_revert_out'=> 'text-zinc-500 bg-zinc-50',
                            'wine_loss'               => 'text-red-500 bg-red-50',
                            'wine_loss_revert'        => 'text-zinc-400 bg-zinc-50',
                            'bottling'                => 'text-blue-600 bg-blue-50',
                            'bottling_revert'         => 'text-zinc-400 bg-zinc-50',
                            'fill'                    => 'text-amber-600 bg-amber-50',
                            'empty'                   => 'text-amber-400 bg-amber-50',
                            'transfer'                => 'text-amber-500 bg-amber-50',
                            'sale'                    => 'text-green-600 bg-green-50',
                            'adjustment'              => 'text-zinc-600 bg-zinc-50',
                            'maintenance'             => 'text-zinc-500 bg-zinc-50',
                        ];
                        $typeLabels = [
                            'wine_transfer_in'        => 'Trasvase entrada',
                            'wine_transfer_out'       => 'Trasvase salida',
                            'wine_transfer_revert_in' => 'Reversión trasvase +',
                            'wine_transfer_revert_out'=> 'Reversión trasvase −',
                            'wine_loss'               => 'Merma / pérdida',
                            'wine_loss_revert'        => 'Reversión merma',
                            'bottling'                => 'Embotellado',
                            'bottling_revert'         => 'Reversión embotellado',
                            'fill'                    => 'Llenado (uva)',
                            'empty'                   => 'Vaciamiento',
                            'transfer'                => 'Transferencia uva',
                            'sale'                    => 'Venta',
                            'adjustment'              => 'Ajuste',
                            'maintenance'             => 'Mantenimiento',
                        ];
                        $colorClass = $typeColors[$entry->operation_type] ?? 'text-zinc-500 bg-zinc-50';
                        $label      = $typeLabels[$entry->operation_type] ?? $entry->operation_type;
                    @endphp
                    <div class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-zinc-50 text-sm">
                        <span class="text-xs font-medium px-2 py-0.5 rounded-full shrink-0 {{ $colorClass }}">
                            {{ $label }}
                        </span>
                        <span class="flex-1 text-zinc-500 text-xs truncate">
                            {{ $entry->wine?->name ?? '' }}
                        </span>
                        <span class="font-mono text-xs font-semibold {{ $positive ? 'text-green-600' : 'text-red-500' }} shrink-0">
                            {{ $positive ? '+' : '' }}{{ number_format($entry->quantity, 1) }}
                        </span>
                        <span class="text-xs text-zinc-300 shrink-0 w-16 text-right">
                            {{ $entry->start_date instanceof \Carbon\Carbon ? $entry->start_date->format('d/m') : '' }}
                        </span>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-sm text-zinc-400 text-center py-6">Sin movimientos registrados</p>
                @endif
            </x-agro.card>

        </div>
    </div>

    {{-- ── Análisis y fermentaciones ───────────────────────────────────────── --}}
    @if($recentAnalyses->count() || $recentFermentations->count())
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Últimos análisis --}}
        @if($recentAnalyses->count())
        <x-agro.card title="Análisis recientes">
            <div class="space-y-2">
                @foreach($recentAnalyses as $a)
                <div class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-zinc-50 text-sm">
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-zinc-800 truncate">{{ \App\Models\WineAnalysis::ANALYSIS_TYPES[$a->analysis_type] ?? $a->analysis_type }}</p>
                        <p class="text-xs text-zinc-400">{{ $a->wine?->name ?? '—' }} · {{ $a->laboratory ?? '' }}</p>
                    </div>
                    <div class="text-right shrink-0 ml-3">
                        @php $rc = match($a->result ?? '') { 'pass' => 'text-agro-600', 'fail' => 'text-red-500', default => 'text-zinc-400' }; @endphp
                        <p class="text-xs font-semibold {{ $rc }}">{{ \App\Models\WineAnalysis::RESULTS[$a->result] ?? '—' }}</p>
                        <p class="text-xs text-zinc-300">{{ $a->analysis_date instanceof \Carbon\Carbon ? $a->analysis_date->format('d/m/Y') : $a->analysis_date }}</p>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="mt-3">
                <flux:button href="{{ roleRoute('wine-analysis.index', [], ['containerFilter' => $container->id]) }}" variant="ghost" size="sm" icon="beaker">
                    Ver todos los análisis
                </flux:button>
            </div>
        </x-agro.card>
        @endif

        {{-- Últimos controles de fermentación --}}
        @if($recentFermentations->count())
        <x-agro.card title="Controles de fermentación recientes">
            <div class="space-y-2">
                @foreach($recentFermentations as $f)
                <div class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-zinc-50 text-sm">
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-zinc-800 truncate">{{ $f->wine?->name ?? '—' }}</p>
                        <p class="text-xs text-zinc-400">
                            @if($f->density) Den: {{ number_format($f->density, 3) }} @endif
                            @if($f->brix_degree) · Brix: {{ number_format($f->brix_degree, 1) }}° @endif
                            @if($f->temperature) · {{ number_format($f->temperature, 1) }}°C @endif
                        </p>
                    </div>
                    <p class="text-xs text-zinc-300 shrink-0 ml-3">
                        {{ $f->control_date instanceof \Carbon\Carbon ? $f->control_date->format('d/m/Y') : $f->control_date }}
                    </p>
                </div>
                @endforeach
            </div>
            <div class="mt-3">
                <flux:button href="{{ roleRoute('fermentation-controls.index', [], ['containerFilter' => $container->id]) }}" variant="ghost" size="sm" icon="chart-bar">
                    Ver todos los controles
                </flux:button>
            </div>
        </x-agro.card>
        @endif

    </div>
    @endif

</div>

{{-- ── Modal ajuste manual de stock ─────────────────────────────────────── --}}
@if($showAdjustModal)
<div class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4" wire:click.self="$set('showAdjustModal', false)">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 space-y-5">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-bold text-zinc-800">Ajuste manual de stock</h3>
            <button wire:click="$set('showAdjustModal', false)" class="text-zinc-400 hover:text-zinc-700">
                <flux:icon icon="x-mark" class="size-5" />
            </button>
        </div>
        <p class="text-sm text-zinc-500">
            Ajusta directamente los litros de vino elaborado. Se registra como ajuste en el historial de movimientos.
        </p>
        <div class="space-y-4">
            <div>
                <flux:label required>Litros (nuevo valor)</flux:label>
                <flux:input type="number" step="0.1" min="0" wire:model="adjustLiters" class="mt-1" placeholder="0.0" />
                @error('adjustLiters') <flux:error>{{ $message }}</flux:error> @enderror
            </div>
            <div>
                <flux:label>Vino asociado</flux:label>
                <flux:select wire:model="adjustWineId" class="mt-1">
                    <flux:select.option value="">Sin vino específico</flux:select.option>
                    @foreach($wines as $w)
                        <flux:select.option value="{{ $w->id }}">{{ $w->name }}</flux:select.option>
                    @endforeach
                </flux:select>
                @error('adjustWineId') <flux:error>{{ $message }}</flux:error> @enderror
            </div>
        </div>
        <div class="flex justify-end gap-3 pt-2">
            <flux:button wire:click="$set('showAdjustModal', false)" variant="ghost">Cancelar</flux:button>
            <flux:button wire:click="saveAdjust" variant="primary" wire:loading.attr="disabled">Guardar ajuste</flux:button>
        </div>
    </div>
</div>
@endif
