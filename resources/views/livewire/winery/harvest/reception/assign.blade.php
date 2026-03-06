<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Asignar a Contenedor"
        description="Asigna esta recepción de uva a un depósito o barrica"
    >
        <x-slot:actions>
            <flux:button href="{{ route('winery.grape-reception.index') }}" variant="ghost" icon="arrow-left">
                Volver
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Resumen de la recepción --}}
    @php
        $planting = $harvest->plotPlanting;
        $variety  = $planting?->grapeVariety?->name ?? $planting?->name ?? '—';
        $plotName = $planting?->plot?->name ?? '—';
        $vitic    = $harvest->activity?->viticulturist;
        $campaign = $harvest->activity?->campaign;
    @endphp

    <x-agro.card>
        <x-slot:header>
            <div class="flex items-center gap-2">
                <div class="p-1.5 rounded-lg bg-agro-50">
                    <flux:icon icon="scale" class="size-4 text-agro-600" />
                </div>
                <span class="font-semibold text-zinc-900 text-sm">Recepción</span>
            </div>
        </x-slot:header>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div>
                <p class="text-xs text-zinc-500 mb-0.5">Fecha</p>
                <p class="font-medium text-zinc-900">{{ $harvest->harvest_start_date?->format('d/m/Y') ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-zinc-500 mb-0.5">Viticultor</p>
                <p class="font-medium text-zinc-900">{{ $vitic?->name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-zinc-500 mb-0.5">Parcela / Variedad</p>
                <p class="font-medium text-zinc-900">{{ $variety }}</p>
                <p class="text-xs text-zinc-500">{{ $plotName }}</p>
            </div>
            <div>
                <p class="text-xs text-zinc-500 mb-0.5">Kg recibidos</p>
                <p class="font-bold text-agro-700 text-lg">{{ number_format($harvest->total_weight, 0) }} kg</p>
            </div>
        </div>

        @if($harvest->container_id)
            <div class="mt-4 p-3 bg-amber-50 border border-amber-200 rounded-lg">
                <p class="text-xs text-amber-700 font-medium">
                    Esta recepción ya está asignada al contenedor <strong>{{ $harvest->container?->name }}</strong>.
                    Puedes reasignarla seleccionando otro contenedor.
                </p>
            </div>
        @endif
    </x-agro.card>

    {{-- Selector de contenedor --}}
    <form wire:submit="save" class="space-y-6">
        <x-agro.card>
            <x-slot:header>
                <div class="flex items-center gap-2">
                    <div class="p-1.5 rounded-lg bg-agro-50">
                        <flux:icon icon="cube" class="size-4 text-agro-600" />
                    </div>
                    <span class="font-semibold text-zinc-900 text-sm">Seleccionar Contenedor</span>
                </div>
            </x-slot:header>

            @if($availableContainers->isEmpty())
                <div class="text-center py-6">
                    <p class="text-sm text-zinc-500 mb-2">No tienes contenedores activos.</p>
                    <flux:button href="{{ route('winery.containers.create') }}" variant="ghost" size="sm" icon="plus">
                        Crear contenedor
                    </flux:button>
                </div>
            @else
                <flux:field>
                    <flux:label>Contenedor destino</flux:label>
                    <flux:select wire:model="container_id">
                        <flux:select.option value="">Selecciona un contenedor...</flux:select.option>
                        @foreach($availableContainers as $c)
                            @php
                                $available = $c->getAvailableCapacity();
                                $pct       = $c->getOccupancyPercentage();
                                $typeName  = $typesById[$c->type_id]->name ?? '';
                                $hasRoom   = $available >= $harvest->total_weight;
                            @endphp
                            <flux:select.option
                                value="{{ $c->id }}"
                                :disabled="!$hasRoom && $c->id !== $harvest->container_id"
                            >
                                {{ $c->name }}
                                ({{ $typeName }}) —
                                Disponible: {{ number_format($available, 0) }} kg
                                ({{ number_format($pct, 0) }}% ocupado)
                                @if(!$hasRoom && $c->id !== $harvest->container_id) — SIN CAPACIDAD @endif
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="container_id" />
                </flux:field>

                {{-- Vista previa del contenedor seleccionado --}}
                @if($container_id)
                    @php
                        $selected = $availableContainers->firstWhere('id', (int) $container_id);
                    @endphp
                    @if($selected)
                        <div class="mt-4 p-4 bg-zinc-50 border border-zinc-200 rounded-lg space-y-2">
                            <p class="text-sm font-semibold text-zinc-800">{{ $selected->name }}</p>
                            <x-agro.progress-bar
                                :percentage="$selected->getOccupancyPercentage()"
                                :currentValue="$selected->used_capacity"
                                :maxValue="$selected->capacity"
                                unit="kg"
                            />
                            @php
                                $newUsed    = $selected->used_capacity + $harvest->total_weight;
                                $newPct     = $selected->capacity > 0 ? round($newUsed / $selected->capacity * 100, 1) : 0;
                                $willExceed = $newUsed > $selected->capacity;
                            @endphp
                            <div class="flex items-center gap-2 text-xs">
                                <span class="text-zinc-500">Tras asignar esta recepción:</span>
                                <span class="font-bold {{ $willExceed ? 'text-red-700' : 'text-agro-700' }}">
                                    {{ number_format($newUsed, 0) }} / {{ number_format($selected->capacity, 0) }} kg
                                    ({{ $newPct }}%)
                                </span>
                                @if($willExceed)
                                    <flux:badge color="red" size="sm">Excede capacidad</flux:badge>
                                @endif
                            </div>
                        </div>
                    @endif
                @endif
            @endif
        </x-agro.card>

        <div class="flex justify-end gap-3">
            <flux:button href="{{ route('winery.grape-reception.index') }}" variant="ghost">
                Cancelar
            </flux:button>
            <flux:button type="submit" variant="primary" icon="check" :disabled="$availableContainers->isEmpty()">
                Asignar Contenedor
            </flux:button>
        </div>
    </form>
</div>
