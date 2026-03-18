<div class="space-y-6 animate-fade-in">
<x-agro.page-header
    title="Proveedores"
    description="Gestiona los proveedores de tu bodega."
    icon="truck"
>
    <x-slot:actions>
        <flux:button variant="primary" icon="plus" href="{{ roleRoute('suppliers.create') }}" wire:navigate>
            Nuevo proveedor
        </flux:button>
    </x-slot:actions>
</x-agro.page-header>

<x-agro.card>
    <x-agro.filter-bar>
        <x-agro.filter-input wire:model.live="search" placeholder="Buscar por nombre o contacto..." />
        <x-agro.filter-select wire:model.live="categoryFilter" placeholder="Todas las categorías">
            @foreach($categories as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </x-agro.filter-select>
    </x-agro.filter-bar>

    <x-agro.data-table :headers="['Nombre', 'Contacto', 'Email', 'Teléfono', 'Categoría', 'Acciones']">
        @forelse($suppliers as $supplier)
            <x-agro.table-row>
                <x-agro.table-cell>
                    <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $supplier->name }}</div>
                    @if($supplier->vat_number)
                        <div class="text-xs text-zinc-500">{{ $supplier->vat_number }}</div>
                    @endif
                </x-agro.table-cell>
                <x-agro.table-cell>
                    {{ $supplier->contact_person ?? '—' }}
                </x-agro.table-cell>
                <x-agro.table-cell>
                    @if($supplier->email)
                        <a href="mailto:{{ $supplier->email }}" class="text-amber-600 hover:underline">
                            {{ $supplier->email }}
                        </a>
                    @else
                        <span class="text-zinc-400">—</span>
                    @endif
                </x-agro.table-cell>
                <x-agro.table-cell>
                    {{ $supplier->phone ?? '—' }}
                </x-agro.table-cell>
                <x-agro.table-cell>
                    <x-agro.status-badge :label="$supplier->category_label" color="amber" />
                </x-agro.table-cell>
                <x-agro.table-cell align="right">
                    <div class="flex justify-end gap-2">
                        <flux:button size="sm" variant="ghost" icon="pencil"
                            href="{{ roleRoute('suppliers.edit', $supplier) }}" wire:navigate />
                        <flux:button size="sm" variant="ghost" icon="trash"
                            wire:click="delete({{ $supplier->id }})"
                            wire:loading.attr="disabled"
                            wire:confirm="¿Eliminar este proveedor?" />
                    </div>
                </x-agro.table-cell>
            </x-agro.table-row>
        @empty
            <x-agro.empty-state icon="truck" title="Sin proveedores registrados"
                description="Añade los proveedores de tu bodega para tenerlos siempre a mano." />
        @endforelse
    </x-agro.data-table>

    {{ $suppliers->links() }}
</x-agro.card>
</div>
