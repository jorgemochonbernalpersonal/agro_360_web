<div class="space-y-6 animate-fade-in">
<x-agro.page-header
    title="Trasvases y Coupage"
    description="Registro de trasiegos, mezclas y movimientos de vino entre depósitos."
    icon="arrows-right-left"
>
    <x-slot:actions>
        <flux:button variant="primary" icon="plus" href="{{ roleRoute('wine-transfers.create') }}" wire:navigate>
            Nuevo trasvase
        </flux:button>
    </x-slot:actions>
</x-agro.page-header>

<x-agro.card>
    <x-agro.filter-bar>
        <x-agro.filter-input wire:model.live="search" placeholder="Buscar por vino..." />
        <x-agro.filter-select wire:model.live="wineFilter" placeholder="Todos los vinos">
            @foreach($wines as $wine)
                <option value="{{ $wine->id }}">{{ $wine->name }}{{ $wine->vintage ? ' (' . $wine->vintage . ')' : '' }}</option>
            @endforeach
        </x-agro.filter-select>
        <x-agro.filter-select wire:model.live="typeFilter" placeholder="Todos los tipos">
            @foreach($types as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </x-agro.filter-select>
    </x-agro.filter-bar>

    <x-agro.data-table :headers="['Fecha', 'Vino', 'Tipo', 'Origen', 'Destino', 'Cantidad', 'Acciones']">
        @forelse($transfers as $transfer)
            <x-agro.table-row>
                <x-agro.table-cell>
                    {{ $transfer->transfer_date->format('d/m/Y') }}
                </x-agro.table-cell>

                <x-agro.table-cell>
                    @if($transfer->wine)
                        <a href="{{ roleRoute('wines.show', $transfer->wine) }}" wire:navigate
                           class="text-sm font-medium text-zinc-900 dark:text-zinc-100 hover:underline">
                            {{ $transfer->wine->name }}
                        </a>
                        @if($transfer->wine->vintage)
                            <div class="text-xs text-zinc-500">{{ $transfer->wine->vintage }}</div>
                        @endif
                    @else
                        <span class="text-zinc-400">—</span>
                    @endif
                </x-agro.table-cell>

                <x-agro.table-cell>
                    @php
                        $typeColor = match($transfer->transfer_type) {
                            'racking'  => 'blue',
                            'blending' => 'violet',
                            'top_up'   => 'green',
                            default    => 'zinc',
                        };
                    @endphp
                    <x-agro.status-badge :label="$transfer->transfer_type_label" :color="$typeColor" />
                </x-agro.table-cell>

                <x-agro.table-cell>
                    <span class="text-sm text-zinc-700 dark:text-zinc-300">
                        {{ $transfer->fromContainer?->name ?? '—' }}
                    </span>
                </x-agro.table-cell>

                <x-agro.table-cell>
                    <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                        {{ $transfer->toContainer?->name ?? '—' }}
                    </span>
                </x-agro.table-cell>

                <x-agro.table-cell>
                    <span class="text-sm font-semibold text-agro-700">
                        {{ number_format($transfer->quantity, 2) }}
                        {{ $transfer->unitOfMeasurement?->symbol ?? '' }}
                    </span>
                </x-agro.table-cell>

                <x-agro.table-cell align="right">
                    <div class="flex justify-end gap-2">
                        <flux:button size="sm" variant="ghost" icon="pencil"
                            href="{{ roleRoute('wine-transfers.edit', $transfer) }}" wire:navigate />
                        <flux:button size="sm" variant="ghost" icon="trash"
                            wire:click="delete({{ $transfer->id }})"
                            wire:loading.attr="disabled"
                            wire:confirm="¿Eliminar este trasvase?" />
                    </div>
                </x-agro.table-cell>
            </x-agro.table-row>
        @empty
            <x-agro.empty-state
                icon="arrows-right-left"
                title="Sin trasvases registrados"
                description="Registra los trasiegos, mezclas y movimientos de vino entre depósitos." />
        @endforelse
    </x-agro.data-table>

    {{ $transfers->links() }}
</x-agro.card>
</div>
