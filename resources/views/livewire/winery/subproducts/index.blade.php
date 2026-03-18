<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Subproductos"
        description="Registro de orujo, lías, vinaza y otros subproductos de elaboración"
    >
        <x-slot:actions>
            <flux:button href="{{ roleRoute('subproducts.create') }}" variant="primary" icon="plus">
                Nuevo subproducto
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    <x-agro.filter-bar>
        <x-agro.filter-input
            wire:model.live.debounce.300ms="search"
            placeholder="Buscar por vino, destino o lote..."
        />
        <x-agro.filter-select wire:model.live="typeFilter" size="sm" class="w-44">
            <flux:select.option value="">Todos los tipos</flux:select.option>
            @foreach($types as $key => $label)
                <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
            @endforeach
        </x-agro.filter-select>
        <x-agro.filter-select wire:model.live="wineFilter" size="sm" class="w-52">
            <flux:select.option value="">Todos los vinos</flux:select.option>
            @foreach($wines as $wine)
                <flux:select.option value="{{ $wine->id }}">{{ $wine->name }}{{ $wine->vintage ? ' ' . $wine->vintage : '' }}</flux:select.option>
            @endforeach
        </x-agro.filter-select>
        @if($search || $typeFilter || $wineFilter)
            <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">Limpiar</flux:button>
        @endif
    </x-agro.filter-bar>

    @if($subproducts->count() > 0)
        <div wire:loading.class="opacity-60 pointer-events-none" wire:target="search, typeFilter, wineFilter, clearFilters">
            <x-agro.data-table :headers="['Fecha', 'Tipo', 'Vino origen', 'Cantidad', 'Destino', 'Lote', '']">
                @foreach($subproducts as $sp)
                    <x-agro.table-row wire:key="sp-{{ $sp->id }}">
                        <x-agro.table-cell>{{ $sp->subproduct_date->format('d/m/Y') }}</x-agro.table-cell>
                        <x-agro.table-cell>
                            <flux:badge color="{{ $sp->type_badge_color }}" size="sm">{{ $sp->type_label }}</flux:badge>
                        </x-agro.table-cell>
                        <x-agro.table-cell>
                            @if($sp->wine)
                                <span class="font-medium text-zinc-900">{{ $sp->wine->name }}</span>
                                @if($sp->wine->vintage)
                                    <span class="text-xs text-zinc-400 ml-1">{{ $sp->wine->vintage }}</span>
                                @endif
                            @else
                                <span class="text-zinc-300">—</span>
                            @endif
                        </x-agro.table-cell>
                        <x-agro.table-cell align="right">
                            <span class="font-medium">{{ number_format($sp->quantity, 3) }}</span>
                            @if($sp->unit)
                                <span class="text-xs text-zinc-400 font-normal ml-0.5">{{ $sp->unit->abbreviation ?? $sp->unit->name }}</span>
                            @endif
                        </x-agro.table-cell>
                        <x-agro.table-cell>
                            <span class="text-zinc-700">{{ $sp->destination_label }}</span>
                            @if($sp->destination_name)
                                <span class="text-xs text-zinc-400 block">{{ $sp->destination_name }}</span>
                            @endif
                        </x-agro.table-cell>
                        <x-agro.table-cell class="font-mono text-xs">{{ $sp->lot_number ?: '—' }}</x-agro.table-cell>
                        <x-agro.table-cell align="right">
                            <div class="flex items-center justify-end gap-1">
                                <x-agro.action-button variant="edit" :href="roleRoute('subproducts.edit', $sp)" />
                                <x-agro.action-button
                                    variant="delete"
                                    wireClick="delete({{ $sp->id }})"
                                    wireConfirm="¿Eliminar este registro de subproducto?"
                                />
                            </div>
                        </x-agro.table-cell>
                    </x-agro.table-row>
                @endforeach
            </x-agro.data-table>
        </div>

        <x-agro.pagination :paginator="$subproducts" />
    @else
        <x-agro.empty-state
            icon="archive-box"
            title="No hay subproductos"
            :description="$search || $typeFilter || $wineFilter ? 'Ningún subproducto coincide con los filtros.' : 'Registra orujo, lías, vinaza y otros subproductos de elaboración.'"
        >
            @if($search || $typeFilter || $wineFilter)
                <x-slot:action>
                    <flux:button wire:click="clearFilters" variant="outline" icon="x-mark">Limpiar filtros</flux:button>
                </x-slot:action>
            @else
                <x-slot:action>
                    <flux:button href="{{ roleRoute('subproducts.create') }}" variant="primary" icon="plus">Nuevo subproducto</flux:button>
                </x-slot:action>
            @endif
        </x-agro.empty-state>
    @endif
</div>
