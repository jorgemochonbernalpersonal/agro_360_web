<div class="space-y-6 animate-fade-in">
<x-agro.page-header
    title="Insumos de Bodega"
    description="Catálogo de productos usados en mantenimiento y elaboración."
    icon="beaker"
>
    <x-slot:actions>
        <flux:button variant="primary" icon="plus" href="{{ roleRoute('winery-supplies.create') }}" wire:navigate>
            Nuevo insumo
        </flux:button>
    </x-slot:actions>
</x-agro.page-header>

<x-agro.filter-bar>
    <x-agro.filter-input wire:model.live="search" placeholder="Buscar por nombre..." />
    <x-agro.filter-select wire:model.live="typeFilter" placeholder="Todos los tipos">
        @foreach($types as $key => $label)
            <option value="{{ $key }}">{{ $label }}</option>
        @endforeach
    </x-agro.filter-select>
</x-agro.filter-bar>

<x-agro.loading-grid target="search, typeFilter, nextPage, previousPage" />

<div wire:loading.remove wire:target="search, typeFilter, nextPage, previousPage">
    @if($supplies->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($supplies as $supply)
                @php $delay = min($loop->index * 50, 300); @endphp
                <x-agro.card
                    class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                    style="animation-delay: {{ $delay }}ms;"
                    wire:key="supply-{{ $supply->id }}"
                >
                    <x-slot:header>
                        <x-agro.card-item-header
                            icon="beaker"
                            :title="$supply->name"
                            :subtitle="$supply->commercial_name ?? '—'"
                            iconBg="bg-cyan-100"
                            iconColor="text-cyan-600"
                            size="md"
                            radius="xl"
                        >
                            <x-agro.status-badge :label="$supply->type_label" color="blue" />
                        </x-agro.card-item-header>
                    </x-slot:header>

                    <div class="flex-1 space-y-4">
                        <div class="grid grid-cols-2 gap-2">
                            <div class="bg-agro-50 rounded-xl p-3">
                                <p class="text-[10px] font-semibold text-agro-400 uppercase tracking-widest mb-0.5">Stock</p>
                                <p class="text-2xl font-bold {{ $supply->current_stock !== null && $supply->isLowStock() ? 'text-red-600' : 'text-agro-700' }} leading-none">
                                    @if($supply->current_stock !== null)
                                        {{ number_format($supply->current_stock, 3) }}
                                        @if($supply->isLowStock())
                                            <flux:icon icon="exclamation-triangle" class="inline w-4 h-4 text-red-500 ml-1" />
                                        @endif
                                    @else
                                        —
                                    @endif
                                </p>
                            </div>
                            <div class="bg-agro-50 rounded-xl p-3">
                                <p class="text-[10px] font-semibold text-agro-400 uppercase tracking-widest mb-0.5">Unidad</p>
                                <p class="text-2xl font-bold text-agro-700 leading-none">{{ $supply->unitOfMeasurement?->symbol ?? '—' }}</p>
                            </div>
                        </div>

                        <div class="space-y-2 text-sm">
                            <div class="flex items-center justify-between">
                                <span class="text-zinc-400">Caducidad</span>
                                <span class="text-zinc-700 font-medium">{{ $supply->expiry_date?->format('d/m/Y') ?? '—' }}</span>
                            </div>
                        </div>
                    </div>

                    <x-slot:footer>
                        <div class="flex items-center justify-end gap-0.5">
                            <x-agro.action-button icon="pencil" variant="default" href="{{ roleRoute('winery-supplies.edit', $supply) }}" wire:navigate title="Editar" />
                            <x-agro.action-button variant="delete" wire:click="delete({{ $supply->id }})" wire:loading.attr="disabled" wire:confirm="¿Eliminar este insumo?" title="Eliminar" />
                        </div>
                    </x-slot:footer>
                </x-agro.card>
            @endforeach
        </div>
        <div class="mt-6">{{ $supplies->links() }}</div>
    @else
        <x-agro.empty-state icon="beaker" title="Sin insumos"
            description="Añade productos de limpieza, aditivos y otros insumos de bodega." />
    @endif
</div>
</div>
