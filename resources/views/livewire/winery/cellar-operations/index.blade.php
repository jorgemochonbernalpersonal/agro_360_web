<div class="space-y-6 animate-fade-in">
<x-agro.page-header
    title="Operaciones de Bodega"
    description="Trasiegos, clarificaciones, filtraciones, estabilizaciones y otras operaciones enológicas."
    icon="calendar-days"
>
    <x-slot:actions>
        <flux:button variant="primary" icon="plus" href="{{ roleRoute('cellar-operations.create') }}" wire:navigate>
            Nueva operación
        </flux:button>
    </x-slot:actions>
</x-agro.page-header>

<x-agro.card>
    <x-agro.filter-bar>
        <x-agro.filter-input wire:model.live="search" placeholder="Buscar por responsable o notas..." />
        <x-agro.filter-select wire:model.live="typeFilter" placeholder="Todos los tipos">
            @foreach($types as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </x-agro.filter-select>
        <x-agro.filter-select wire:model.live="statusFilter" placeholder="Todos los estados">
            @foreach($statuses as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </x-agro.filter-select>
    </x-agro.filter-bar>

    <x-agro.data-table :headers="['Fecha', 'Tipo', 'Contenedor origen', 'Contenedor destino', 'Volumen (L)', 'Estado', 'Acciones']">
        @forelse($operations as $operation)
            <x-agro.table-row>
                <x-agro.table-cell>
                    <span class="font-medium text-zinc-900 dark:text-zinc-100">
                        {{ $operation->operation_date->format('d/m/Y') }}
                    </span>
                </x-agro.table-cell>
                <x-agro.table-cell>
                    {{ $operation->type_label }}
                </x-agro.table-cell>
                <x-agro.table-cell>
                    {{ $operation->sourceContainer?->name ?? '—' }}
                </x-agro.table-cell>
                <x-agro.table-cell>
                    {{ $operation->targetContainer?->name ?? '—' }}
                </x-agro.table-cell>
                <x-agro.table-cell>
                    @if($operation->volume_liters !== null)
                        {{ number_format($operation->volume_liters, 2) }}
                    @else
                        <span class="text-zinc-400">—</span>
                    @endif
                </x-agro.table-cell>
                <x-agro.table-cell>
                    @php
                        $statusColor = match($operation->status) {
                            'planned'     => 'zinc',
                            'in_progress' => 'yellow',
                            'completed'   => 'green',
                            'cancelled'   => 'red',
                            default       => 'zinc',
                        };
                    @endphp
                    <x-agro.status-badge :label="$operation->status_label" :color="$statusColor" />
                </x-agro.table-cell>
                <x-agro.table-cell align="right">
                    <div class="flex justify-end gap-2">
                        <flux:button size="sm" variant="ghost" icon="pencil"
                            href="{{ roleRoute('cellar-operations.edit', $operation) }}" wire:navigate />
                        <flux:button size="sm" variant="ghost" icon="trash"
                            wire:click="delete({{ $operation->id }})"
                            wire:loading.attr="disabled"
                            wire:confirm="¿Eliminar esta operación?" />
                    </div>
                </x-agro.table-cell>
            </x-agro.table-row>
        @empty
            <x-agro.empty-state icon="calendar-days" title="Sin operaciones"
                description="Registra trasiegos, clarificaciones, filtraciones y otras operaciones de tu bodega." />
        @endforelse
    </x-agro.data-table>

    {{ $operations->links() }}
</x-agro.card>
</div>
