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

<x-agro.filter-bar>
    <x-agro.filter-input wire:model.live="search" placeholder="Buscar por nombre o contacto..." />
    <x-agro.filter-select wire:model.live="categoryFilter" placeholder="Todas las categorías">
        @foreach($categories as $key => $label)
            <option value="{{ $key }}">{{ $label }}</option>
        @endforeach
    </x-agro.filter-select>
</x-agro.filter-bar>

<x-agro.loading-grid target="search, categoryFilter, nextPage, previousPage" />

<div wire:loading.remove wire:target="search, categoryFilter, nextPage, previousPage">
    @if($suppliers->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($suppliers as $supplier)
                @php $delay = min($loop->index * 50, 300); @endphp
                <x-agro.card
                    class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                    style="animation-delay: {{ $delay }}ms;"
                    wire:key="supplier-{{ $supplier->id }}"
                >
                    <x-slot:header>
                        <x-agro.card-item-header
                            icon="truck"
                            :title="$supplier->name"
                            :subtitle="$supplier->vat_number ?? '—'"
                            iconBg="bg-amber-100"
                            iconColor="text-amber-600"
                            size="md"
                            radius="xl"
                        >
                            <x-agro.status-badge :label="$supplier->category_label" color="amber" />
                        </x-agro.card-item-header>
                    </x-slot:header>

                    <div class="flex-1 space-y-4">
                        <div class="space-y-2 text-sm">
                            <div class="flex items-center justify-between">
                                <span class="text-zinc-400">Contacto</span>
                                <span class="text-zinc-700 font-medium">{{ $supplier->contact_person ?? '—' }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-zinc-400">Email</span>
                                <span class="text-zinc-700 font-medium truncate ml-2">
                                    @if($supplier->email)
                                        <a href="mailto:{{ $supplier->email }}" class="text-amber-600 hover:underline">{{ $supplier->email }}</a>
                                    @else
                                        —
                                    @endif
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-zinc-400">Teléfono</span>
                                <span class="text-zinc-700 font-medium">{{ $supplier->phone ?? '—' }}</span>
                            </div>
                        </div>
                    </div>

                    <x-slot:footer>
                        <div class="flex items-center justify-end gap-0.5">
                            <x-agro.action-button icon="pencil" variant="default" href="{{ roleRoute('suppliers.edit', $supplier) }}" wire:navigate title="Editar" />
                            <x-agro.action-button variant="delete" wire:click="delete({{ $supplier->id }})" wire:loading.attr="disabled" wire:confirm="¿Eliminar este proveedor?" title="Eliminar" />
                        </div>
                    </x-slot:footer>
                </x-agro.card>
            @endforeach
        </div>
        <x-agro-pagination :paginator="$suppliers" />
    @else
        <x-agro.empty-state icon="truck" title="Sin proveedores registrados"
            description="Añade los proveedores de tu bodega para tenerlos siempre a mano." />
    @endif
</div>
</div>
