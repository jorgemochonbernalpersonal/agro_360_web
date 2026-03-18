<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Embotellado"
        description="Registro de operaciones de embotellado y materiales utilizados"
    >
        <x-slot:actions>
            <flux:button href="{{ roleRoute('bottling.create') }}" variant="primary" icon="plus">
                Nuevo Embotellado
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    <x-agro.filter-bar>
        <x-agro.filter-input
            wire:model.live.debounce.300ms="search"
            placeholder="Buscar por vino o nº lote..."
        />
        <x-agro.filter-select wire:model.live="wineFilter" size="sm" class="w-56">
            <flux:select.option value="">Todos los vinos</flux:select.option>
            @foreach($wines as $wine)
                <flux:select.option value="{{ $wine->id }}">{{ $wine->name }}{{ $wine->vintage ? ' ' . $wine->vintage : '' }}</flux:select.option>
            @endforeach
        </x-agro.filter-select>
        @if($search || $wineFilter)
            <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">Limpiar</flux:button>
        @endif
    </x-agro.filter-bar>

    @if($bottlings->count() > 0)
        <div wire:loading.class="opacity-60 pointer-events-none" wire:target="search, wineFilter, clearFilters">
            <x-agro.data-table :headers="['Fecha', 'Vino', 'Formato', 'Botellas', 'Litros', 'Nº Lote', 'Enólogo', 'Lote producto', '']">
                @foreach($bottlings as $bottling)
                    <x-agro.table-row wire:key="row-{{ $bottling->id }}">
                        <x-agro.table-cell>{{ $bottling->bottling_date->format('d/m/Y') }}</x-agro.table-cell>
                        <x-agro.table-cell>
                            <span class="font-medium text-zinc-900">{{ $bottling->wine->name }}</span>
                            @if($bottling->wine->vintage)
                                <span class="text-xs text-zinc-400 ml-1">{{ $bottling->wine->vintage }}</span>
                            @endif
                        </x-agro.table-cell>
                        <x-agro.table-cell>{{ $bottling->format_label }}</x-agro.table-cell>
                        <x-agro.table-cell align="right">
                            <span class="font-semibold text-zinc-900">{{ number_format($bottling->quantity_bottles) }}</span>
                        </x-agro.table-cell>
                        <x-agro.table-cell align="right">{{ number_format($bottling->quantity_liters, 1) }} L</x-agro.table-cell>
                        <x-agro.table-cell>{{ $bottling->lot_number ?? '—' }}</x-agro.table-cell>
                        <x-agro.table-cell>
                            @if($bottling->oenologist)
                                {{ $bottling->oenologist->surname }}, {{ $bottling->oenologist->name }}
                            @else
                                —
                            @endif
                        </x-agro.table-cell>
                        <x-agro.table-cell>
                            @if($bottling->productLot)
                                <a href="{{ roleRoute('product-lots.edit', $bottling->product_lot_id) }}"
                                    class="text-xs text-agro-600 hover:text-agro-800 underline underline-offset-2">
                                    {{ $bottling->productLot->name }}
                                </a>
                            @else
                                <span class="text-zinc-300">—</span>
                            @endif
                        </x-agro.table-cell>
                        <x-agro.table-cell align="right">
                            <div class="flex items-center justify-end gap-1">
                                <x-agro.action-button variant="edit" :href="roleRoute('bottling.edit', $bottling)" />
                                @if(! $bottling->product_lot_id)
                                    <x-agro.action-button
                                        variant="delete"
                                        wireClick="delete({{ $bottling->id }})"
                                        wireConfirm="¿Eliminar este registro de embotellado?"
                                    />
                                @endif
                            </div>
                        </x-agro.table-cell>
                    </x-agro.table-row>
                @endforeach
            </x-agro.data-table>
        </div>

        <x-agro.pagination :paginator="$bottlings" />
    @else
        <x-agro.empty-state
            icon="archive-box-arrow-down"
            title="No hay registros de embotellado"
            :description="$search || $wineFilter ? 'Ningún embotellado coincide con los filtros.' : 'Registra tu primera operación de embotellado.'"
        >
            @if($search || $wineFilter)
                <x-slot:action>
                    <flux:button wire:click="clearFilters" variant="outline" icon="x-mark">Limpiar filtros</flux:button>
                </x-slot:action>
            @else
                <x-slot:action>
                    <flux:button href="{{ roleRoute('bottling.create') }}" variant="primary" icon="plus">Nuevo Embotellado</flux:button>
                </x-slot:action>
            @endif
        </x-agro.empty-state>
    @endif
</div>
