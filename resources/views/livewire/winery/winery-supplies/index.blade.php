<x-agro.page-header
    title="Insumos de Bodega"
    description="Catálogo de productos usados en mantenimiento y elaboración."
    icon="beaker"
>
    <x-slot:actions>
        <flux:button variant="primary" icon="plus" href="{{ route('winery.winery-supplies.create') }}" wire:navigate>
            Nuevo insumo
        </flux:button>
    </x-slot:actions>
</x-agro.page-header>

<x-agro.card>
    <x-agro.filter-bar>
        <x-agro.filter-input wire:model.live="search" placeholder="Buscar por nombre..." />
        <x-agro.filter-select wire:model.live="typeFilter" placeholder="Todos los tipos">
            @foreach($types as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </x-agro.filter-select>
    </x-agro.filter-bar>

    <x-agro.data-table :headers="['Nombre', 'Tipo', 'Stock actual', 'Unidad', 'Caducidad', 'Acciones']">
        @forelse($supplies as $supply)
            <x-agro.table-row>
                <x-agro.table-cell>
                    <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $supply->name }}</div>
                    @if($supply->commercial_name)
                        <div class="text-xs text-zinc-500">{{ $supply->commercial_name }}</div>
                    @endif
                </x-agro.table-cell>
                <x-agro.table-cell>
                    <x-agro.status-badge :label="$supply->type_label" color="blue" />
                </x-agro.table-cell>
                <x-agro.table-cell>
                    @if($supply->current_stock !== null)
                        <span class="{{ $supply->isLowStock() ? 'text-red-600 font-semibold' : 'text-zinc-700 dark:text-zinc-300' }}">
                            {{ number_format($supply->current_stock, 3) }}
                        </span>
                        @if($supply->isLowStock())
                            <flux:icon icon="exclamation-triangle" class="inline w-4 h-4 text-red-500 ml-1" />
                        @endif
                    @else
                        <span class="text-zinc-400">—</span>
                    @endif
                </x-agro.table-cell>
                <x-agro.table-cell>{{ $supply->unitOfMeasurement?->symbol ?? '—' }}</x-agro.table-cell>
                <x-agro.table-cell>
                    {{ $supply->expiry_date?->format('d/m/Y') ?? '—' }}
                </x-agro.table-cell>
                <x-agro.table-cell align="right">
                    <div class="flex justify-end gap-2">
                        <flux:button size="sm" variant="ghost" icon="pencil"
                            href="{{ route('winery.winery-supplies.edit', $supply) }}" wire:navigate />
                        <flux:button size="sm" variant="ghost" icon="trash"
                            wire:click="delete({{ $supply->id }})"
                            wire:confirm="¿Eliminar este insumo?" />
                    </div>
                </x-agro.table-cell>
            </x-agro.table-row>
        @empty
            <x-agro.empty-state icon="beaker" title="Sin insumos"
                description="Añade productos de limpieza, aditivos y otros insumos de bodega." />
        @endforelse
    </x-agro.data-table>

    {{ $supplies->links() }}
</x-agro.card>
