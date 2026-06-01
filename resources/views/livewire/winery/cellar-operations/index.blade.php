<div class="space-y-6 animate-fade-in">
<x-agro.page-header
    title="{{ __('Operaciones de Bodega') }}"
    :description="__('Trasiegos, clarificaciones, filtraciones, estabilizaciones y otras operaciones enológicas.')"
    icon="calendar-days"
>
    <x-slot:actions>
        <flux:button variant="primary" icon="plus" href="{{ roleRoute('cellar-operations.create') }}" wire:navigate>
            Nueva operación
        </flux:button>
    </x-slot:actions>
</x-agro.page-header>

<x-agro.filter-bar>
    <x-agro.filter-input wire:model.live="search" :placeholder="__('Buscar por responsable o notas...')" />
    <x-agro.filter-select wire:model.live="typeFilter" :placeholder="__('Todos los tipos')">
        @foreach($types as $key => $label)
            <option value="{{ $key }}">{{ $label }}</option>
        @endforeach
    </x-agro.filter-select>
    <x-agro.filter-select wire:model.live="statusFilter" :placeholder="__('Todos los estados')">
        @foreach($statuses as $key => $label)
            <option value="{{ $key }}">{{ $label }}</option>
        @endforeach
    </x-agro.filter-select>
</x-agro.filter-bar>

<x-agro.loading-grid target="search, typeFilter, statusFilter, nextPage, previousPage" />

<div wire:loading.remove wire:target="search, typeFilter, statusFilter, nextPage, previousPage">
    @if($operations->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($operations as $operation)
                @php $delay = min($loop->index * 50, 300); @endphp
                @php
                    $statusColor = match($operation->status) {
                        'planned'     => 'zinc',
                        'in_progress' => 'yellow',
                        'completed'   => 'green',
                        'cancelled'   => 'red',
                        default       => 'zinc',
                    };
                @endphp
                <x-agro.card
                    class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                    style="animation-delay: {{ $delay }}ms;"
                    wire:key="operation-{{ $operation->id }}"
                >
                    <x-slot:header>
                        <x-agro.card-item-header
                            icon="beaker"
                            :title="$operation->type_label"
                            :subtitle="$operation->operation_date->format('d/m/Y')"
                            iconBg="bg-emerald-100"
                            iconColor="text-emerald-600"
                            size="md"
                            radius="xl"
                        >
                            <x-agro.status-badge :label="$operation->status_label" :color="$statusColor" />
                        </x-agro.card-item-header>
                    </x-slot:header>

                    <div class="flex-1 space-y-4">
                        <div class="grid grid-cols-1 gap-2">
                            <div class="bg-agro-50 rounded-xl p-3">
                                <p class="text-[10px] font-semibold text-agro-400 uppercase tracking-widest mb-0.5">{{ __('Volumen') }}</p>
                                <p class="text-2xl font-bold text-agro-700 leading-none">
                                    @if($operation->volume_liters !== null)
                                        {{ number_format($operation->volume_liters, 2) }}
                                    @else
                                        —
                                    @endif
                                </p>
                                @if($operation->volume_liters !== null)
                                    <p class="text-[10px] text-agro-400">{{ __('litros') }}</p>
                                @endif
                            </div>
                        </div>

                        <div class="space-y-2 text-sm">
                            <div class="flex items-center justify-between">
                                <span class="text-zinc-400">{{ __('Origen') }}</span>
                                <span class="text-zinc-700 font-medium truncate ml-2">{{ $operation->sourceContainer?->name ?? '—' }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-zinc-400">{{ __('Destino') }}</span>
                                <span class="text-zinc-700 font-medium truncate ml-2">{{ $operation->targetContainer?->name ?? '—' }}</span>
                            </div>
                        </div>
                    </div>

                    <x-slot:footer>
                        <div class="flex items-center justify-end gap-0.5">
                            <x-agro.action-button icon="pencil" variant="default" href="{{ roleRoute('cellar-operations.edit', $operation) }}" wire:navigate title="{{ __('Editar') }}" />
                            <x-agro.action-button variant="delete" wire:click="delete({{ $operation->id }})" wire:loading.attr="disabled" wire:confirm="{{ __('¿Eliminar esta operación?') }}" title="{{ __('Eliminar') }}" />
                        </div>
                    </x-slot:footer>
                </x-agro.card>
            @endforeach
        </div>
        <x-agro.pagination :paginator="$operations" />
    @else
        <x-agro.empty-state icon="calendar-days" title="{{ __('Sin operaciones') }}"
            :description="__('Registra trasiegos, clarificaciones, filtraciones y otras operaciones de tu bodega.')" />
    @endif
</div>
</div>
