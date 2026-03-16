<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        :title="'Mermas — ' . $labelBatch->name"
        description="Etiquetas inutilizadas, dañadas o destruidas de este lote"
    >
        <x-slot:actions>
            <flux:button :href="roleRoute('label-batches.waste.create', $labelBatch)" wire:navigate variant="primary" icon="plus">
                Registrar merma
            </flux:button>
            <flux:button :href="roleRoute('label-batches.index')" wire:navigate variant="ghost" icon="arrow-left">
                Volver a lotes
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- KPIs del lote --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <x-agro.stat-card
            label="Total etiquetas"
            :value="number_format($labelBatch->total_quantity)"
            icon="tag"
            color="zinc"
        />
        <x-agro.stat-card
            label="Usadas"
            :value="number_format($labelBatch->used_quantity)"
            icon="check-circle"
            color="green"
        />
        <x-agro.stat-card
            label="Merma total"
            :value="number_format($labelBatch->wasted_quantity)"
            icon="exclamation-triangle"
            color="red"
        />
        <x-agro.stat-card
            label="Disponibles"
            :value="number_format($labelBatch->total_quantity - $labelBatch->used_quantity - $labelBatch->wasted_quantity)"
            icon="archive-box"
            color="blue"
        />
    </div>

    @if($wastes->isEmpty())
        <x-agro.empty-state
            icon="exclamation-triangle"
            title="Sin mermas registradas"
            description="Registra las etiquetas inutilizadas o destruidas de este lote."
        >
            <x-slot:action>
                <flux:button :href="roleRoute('label-batches.waste.create', $labelBatch)" wire:navigate variant="primary" icon="plus">
                    Registrar merma
                </flux:button>
            </x-slot:action>
        </x-agro.empty-state>
    @else
        <x-agro.card>
            <x-agro.data-table>
                <x-slot:head>
                    <x-agro.table-cell header>Fecha</x-agro.table-cell>
                    <x-agro.table-cell header align="right">Cantidad</x-agro.table-cell>
                    <x-agro.table-cell header>Numeración</x-agro.table-cell>
                    <x-agro.table-cell header>Motivo</x-agro.table-cell>
                    <x-agro.table-cell header align="center">Acciones</x-agro.table-cell>
                </x-slot:head>

                @foreach($wastes as $waste)
                    <x-agro.table-row>
                        <x-agro.table-cell>
                            {{ $waste->waste_date->format('d/m/Y') }}
                        </x-agro.table-cell>

                        <x-agro.table-cell align="right">
                            <span class="font-semibold text-red-600">{{ number_format($waste->quantity) }}</span>
                        </x-agro.table-cell>

                        <x-agro.table-cell>
                            @if($waste->from_number || $waste->to_number)
                                <span class="font-mono text-xs text-zinc-500">
                                    {{ $waste->from_number ?? '?' }} – {{ $waste->to_number ?? '?' }}
                                </span>
                            @else
                                <span class="text-zinc-300">—</span>
                            @endif
                        </x-agro.table-cell>

                        <x-agro.table-cell>
                            {{ $waste->reason ?: '—' }}
                        </x-agro.table-cell>

                        <x-agro.table-cell align="center">
                            <button
                                wire:click="delete({{ $waste->id }})"
                                wire:confirm="¿Eliminar esta merma? Se restaurarán las {{ $waste->quantity }} etiquetas al disponible."
                                wire:loading.attr="disabled"
                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-red-500 hover:bg-red-50 transition-colors"
                                title="Eliminar"
                            >
                                <flux:icon icon="trash" class="size-4" />
                            </button>
                        </x-agro.table-cell>
                    </x-agro.table-row>
                @endforeach
            </x-agro.data-table>
        </x-agro.card>

        <x-agro.pagination :paginator="$wastes" />
    @endif

</div>
