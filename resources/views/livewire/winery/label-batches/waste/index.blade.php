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
    <div class="grid grid-cols-2 gap-4">
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
        <x-agro.loading-grid target="nextPage, previousPage" />

        <div wire:loading.remove wire:target="nextPage, previousPage">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($wastes as $waste)
                    @php $delay = min($loop->index * 50, 300); @endphp
                    <x-agro.card
                        class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                        style="animation-delay: {{ $delay }}ms;"
                        wire:key="waste-{{ $waste->id }}"
                    >
                        <x-slot:header>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center shrink-0">
                                    <flux:icon icon="exclamation-triangle" class="size-5 text-red-600" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-zinc-900 truncate">{{ $waste->waste_date->format('d/m/Y') }}</h3>
                                    <p class="text-xs text-zinc-500">Merma registrada</p>
                                </div>
                                <flux:badge color="red" size="sm" class="shrink-0">{{ number_format($waste->quantity) }} uds</flux:badge>
                            </div>
                        </x-slot:header>

                        <div class="flex-1 space-y-4">
                            <div class="grid grid-cols-1 gap-2">
                                <div class="bg-red-50 rounded-xl p-3">
                                    <p class="text-[10px] font-semibold text-red-400 uppercase tracking-widest mb-0.5">Cantidad</p>
                                    <p class="text-2xl font-bold text-red-600 leading-none">{{ number_format($waste->quantity) }}</p>
                                </div>
                            </div>

                            <div class="space-y-2 text-sm">
                                <div class="flex items-center justify-between">
                                    <span class="text-zinc-400">Numeración</span>
                                    <span class="text-zinc-700 font-medium font-mono text-xs">
                                        @if($waste->from_number || $waste->to_number)
                                            {{ $waste->from_number ?? '?' }} – {{ $waste->to_number ?? '?' }}
                                        @else
                                            —
                                        @endif
                                    </span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-zinc-400">Motivo</span>
                                    <span class="text-zinc-700 font-medium">{{ $waste->reason ?: '—' }}</span>
                                </div>
                            </div>
                        </div>

                        <x-slot:footer>
                            @php $btnBase = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors'; @endphp
                            <div class="flex items-center justify-end gap-0.5">
                                <button
                                    wire:click="delete({{ $waste->id }})"
                                    wire:confirm="¿Eliminar esta merma? Se restaurarán las {{ $waste->quantity }} etiquetas al disponible."
                                    wire:loading.attr="disabled"
                                    class="{{ $btnBase }} hover:!text-red-500 hover:!bg-red-50"
                                    title="Eliminar"
                                >
                                    <flux:icon icon="trash" class="size-4" />
                                </button>
                            </div>
                        </x-slot:footer>
                    </x-agro.card>
                @endforeach
            </div>

            <div class="mt-6"><x-agro.pagination :paginator="$wastes" /></div>
        </div>
    @endif

</div>
