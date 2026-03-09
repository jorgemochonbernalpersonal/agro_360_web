<div class="space-y-6 animate-fade-in">
<x-agro.page-header
    title="Vinos en elaboración"
    description="Pipeline de vinificación — seguimiento de vinos desde recepción hasta embotellado."
    icon="beaker"
>
    <x-slot:actions>
        <flux:button variant="primary" icon="plus" href="{{ route('winery.wines.create') }}" wire:navigate>
            Nuevo vino
        </flux:button>
    </x-slot:actions>
</x-agro.page-header>

<x-agro.card>
    <x-agro.filter-bar>
        <x-agro.filter-input wire:model.live="search" placeholder="Buscar por nombre, variedad..." />
        <x-agro.filter-select wire:model.live="vintageFilter" placeholder="Todas las añadas">
            @foreach($vintages as $v)
                <option value="{{ $v }}">{{ $v }}</option>
            @endforeach
        </x-agro.filter-select>
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

    <x-agro.data-table :headers="['Vino', 'Añada', 'Tipo', 'Estado', 'Volumen (L)', 'Operaciones', 'Acciones']">
        @forelse($wines as $wine)
            <x-agro.table-row>
                <x-agro.table-cell>
                    <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $wine->name }}</div>
                    @if($wine->variety)
                        <div class="text-xs text-zinc-500">{{ $wine->variety }}</div>
                    @endif
                    @if($wine->internal_code)
                        <div class="text-xs text-zinc-400 font-mono">{{ $wine->internal_code }}</div>
                    @endif
                </x-agro.table-cell>
                <x-agro.table-cell>{{ $wine->vintage ?? '—' }}</x-agro.table-cell>
                <x-agro.table-cell>
                    <x-agro.status-badge :label="$wine->type_label" color="purple" />
                </x-agro.table-cell>
                <x-agro.table-cell>
                    @php
                        $statusColor = match($wine->status) {
                            'in_progress' => 'blue',
                            'aged'        => 'yellow',
                            'bottled'     => 'green',
                            'sold'        => 'zinc',
                            default       => 'red',
                        };
                    @endphp
                    <x-agro.status-badge :label="$wine->status_label" :color="$statusColor" />
                </x-agro.table-cell>
                <x-agro.table-cell>
                    {{ $wine->volume_liters ? number_format($wine->volume_liters, 0) : '—' }}
                </x-agro.table-cell>
                <x-agro.table-cell>{{ $wine->process_details_count }}</x-agro.table-cell>
                <x-agro.table-cell align="right">
                    <div class="flex justify-end gap-2">
                        <flux:button size="sm" variant="ghost" icon="eye"
                            href="{{ route('winery.wines.show', $wine) }}" wire:navigate
                            title="Ver detalle" />
                        <flux:button size="sm" variant="ghost" icon="pencil"
                            href="{{ route('winery.wines.edit', $wine) }}" wire:navigate />
                        <flux:button size="sm" variant="ghost" icon="trash"
                            wire:click="delete({{ $wine->id }})"
                            wire:confirm="¿Eliminar este vino y todas sus operaciones?" />
                    </div>
                </x-agro.table-cell>
            </x-agro.table-row>
        @empty
            <x-agro.empty-state icon="beaker" title="Sin vinos registrados"
                description="Crea un vino para comenzar el seguimiento de su proceso de elaboración." />
        @endforelse
    </x-agro.data-table>
    {{ $wines->links() }}
</x-agro.card>
</div>
