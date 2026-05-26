<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        :title="__('Mermas — :name', ['name' => $labelBatch->name])"
        :description="__('Etiquetas inutilizadas, dañadas o destruidas de este lote')"
    >
        <x-slot:actions>
            <flux:button :href="roleRoute('label-batches.waste.create', $labelBatch)" wire:navigate variant="primary" icon="plus">{{ __('Registrar merma') }}</flux:button>
            <flux:button :href="roleRoute('label-batches.index')" wire:navigate variant="ghost" icon="arrow-left">{{ __('Volver a lotes') }}</flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- KPIs del lote --}}
    <div class="grid grid-cols-2 gap-4">
        <x-agro.stat-card
            :label="__('Total etiquetas')"
            :value="number_format($labelBatch->total_quantity)"
            icon="tag"
            color="zinc"
        />
        <x-agro.stat-card
            :label="__('Usadas')"
            :value="number_format($labelBatch->used_quantity)"
            icon="check-circle"
            color="green"
        />
        <x-agro.stat-card
            :label="__('Merma total')"
            :value="number_format($labelBatch->wasted_quantity)"
            icon="exclamation-triangle"
            color="red"
        />
        <x-agro.stat-card
            :label="__('Disponibles')"
            :value="number_format($labelBatch->total_quantity - $labelBatch->used_quantity - $labelBatch->wasted_quantity)"
            icon="archive-box"
            color="blue"
        />
    </div>

    @if($wastes->isEmpty())
        <x-agro.empty-state
            icon="exclamation-triangle"
            title="{{ __('Sin mermas registradas') }}"
            :description="__('Registra las etiquetas inutilizadas o destruidas de este lote.')"
        >
            <x-slot:action>
                <flux:button :href="roleRoute('label-batches.waste.create', $labelBatch)" wire:navigate variant="primary" icon="plus">{{ __('Registrar merma') }}</flux:button>
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
                            <x-agro.card-item-header
                                icon="exclamation-triangle"
                                :title="$waste->waste_date->format('d/m/Y')"
                                subtitle="Merma registrada"
                                iconBg="bg-red-100"
                                iconColor="text-red-600"
                                size="md"
                                radius="xl"
                            >
                                <flux:badge color="red" size="sm">{{ number_format($waste->quantity) }} uds</flux:badge>
                            </x-agro.card-item-header>
                        </x-slot:header>

                        <div class="flex-1 space-y-4">
                            <div class="grid grid-cols-1 gap-2">
                                <div class="bg-red-50 rounded-xl p-3">
                                    <p class="text-[10px] font-semibold text-red-400 uppercase tracking-widest mb-0.5">{{ __('Cantidad') }}</p>
                                    <p class="text-2xl font-bold text-red-600 leading-none">{{ number_format($waste->quantity) }}</p>
                                </div>
                            </div>

                            <div class="space-y-2 text-sm">
                                <div class="flex items-center justify-between">
                                    <span class="text-zinc-400">{{ __('Numeración') }}</span>
                                    <span class="text-zinc-700 font-medium font-mono text-xs">
                                        @if($waste->from_number || $waste->to_number)
                                            {{ $waste->from_number ?? '?' }} – {{ $waste->to_number ?? '?' }}
                                        @else
                                            —
                                        @endif
                                    </span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-zinc-400">{{ __('Motivo') }}</span>
                                    <span class="text-zinc-700 font-medium">{{ $waste->reason ?: '—' }}</span>
                                </div>
                            </div>
                        </div>

                        <x-slot:footer>
                            <div class="flex items-center justify-end gap-0.5">
                                <x-agro.action-button variant="delete" wire:click="delete({{ $waste->id }})" wire:confirm="{{ __('¿Eliminar esta merma? Se restaurarán las :quantity etiquetas al disponible.', ['quantity' => $waste->quantity]) }}" wire:loading.attr="disabled" title="{{ __('Eliminar') }}" />
                            </div>
                        </x-slot:footer>
                    </x-agro.card>
                @endforeach
            </div>

            <div class="mt-6"><x-agro.pagination :paginator="$wastes" /></div>
        </div>
    @endif

</div>
