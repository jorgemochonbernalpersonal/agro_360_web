<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Subproductos"
        description="Registro de orujo, lías, vinaza y otros subproductos de elaboración"
    >
        <x-slot:actions>
            <flux:button href="{{ route('winery.subproducts.create') }}" variant="primary" icon="plus">
                Nuevo subproducto
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    <x-agro.filter-bar>
        <x-agro.filter-input
            wire:model.live.debounce.300ms="search"
            placeholder="Buscar por vino, destino o lote..."
        />
        <flux:select wire:model.live="typeFilter" size="sm" class="w-44">
            <flux:select.option value="">Todos los tipos</flux:select.option>
            @foreach($types as $key => $label)
                <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
            @endforeach
        </flux:select>
        <flux:select wire:model.live="wineFilter" size="sm" class="w-52">
            <flux:select.option value="">Todos los vinos</flux:select.option>
            @foreach($wines as $wine)
                <flux:select.option value="{{ $wine->id }}">{{ $wine->name }}{{ $wine->vintage ? ' ' . $wine->vintage : '' }}</flux:select.option>
            @endforeach
        </flux:select>
        @if($search || $typeFilter || $wineFilter)
            <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">Limpiar</flux:button>
        @endif
    </x-agro.filter-bar>

    @if($subproducts->count() > 0)
        <div class="bg-white rounded-2xl border border-zinc-200 overflow-hidden shadow-sm"
            wire:loading.class="opacity-60 pointer-events-none"
            wire:target="search, typeFilter, wineFilter, clearFilters">

            <table class="w-full text-sm">
                <thead class="bg-zinc-50 border-b border-zinc-200">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wide">Fecha</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wide">Tipo</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wide">Vino origen</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-zinc-500 uppercase tracking-wide">Cantidad</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wide">Destino</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wide">Lote</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @foreach($subproducts as $sp)
                        <tr class="hover:bg-zinc-50 transition-colors" wire:key="sp-{{ $sp->id }}">
                            <td class="px-4 py-3 text-zinc-600 whitespace-nowrap">
                                {{ $sp->subproduct_date->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3">
                                <flux:badge color="{{ $sp->type_badge_color }}" size="sm">{{ $sp->type_label }}</flux:badge>
                            </td>
                            <td class="px-4 py-3">
                                @if($sp->wine)
                                    <span class="font-medium text-zinc-900">{{ $sp->wine->name }}</span>
                                    @if($sp->wine->vintage)
                                        <span class="text-xs text-zinc-400 ml-1">{{ $sp->wine->vintage }}</span>
                                    @endif
                                @else
                                    <span class="text-zinc-300">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right font-medium text-zinc-700 whitespace-nowrap">
                                {{ number_format($sp->quantity, 3) }}
                                @if($sp->unit)
                                    <span class="text-xs text-zinc-400 font-normal ml-0.5">{{ $sp->unit->abbreviation ?? $sp->unit->name }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-zinc-700">{{ $sp->destination_label }}</span>
                                @if($sp->destination_name)
                                    <span class="text-xs text-zinc-400 block">{{ $sp->destination_name }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-zinc-500 font-mono text-xs">
                                {{ $sp->lot_number ?: '—' }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('winery.subproducts.edit', $sp) }}" title="Editar">
                                        <button class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors">
                                            <flux:icon icon="pencil" class="size-4" />
                                        </button>
                                    </a>
                                    <button
                                        wire:click="delete({{ $sp->id }})"
                                        wire:confirm="¿Eliminar este registro de subproducto?"
                                        wire:loading.attr="disabled"
                                        title="Eliminar"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-red-600 hover:bg-red-50 transition-colors">
                                        <flux:icon icon="trash" class="size-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-2">{{ $subproducts->links() }}</div>
    @else
        <x-agro.empty-state
            icon="archive-box"
            title="No hay subproductos"
            description="{{ $search || $typeFilter || $wineFilter ? 'Ningún subproducto coincide con los filtros.' : 'Registra orujo, lías, vinaza y otros subproductos de elaboración.' }}"
        >
            @if($search || $typeFilter || $wineFilter)
                <x-slot:action>
                    <flux:button wire:click="clearFilters" variant="outline" icon="x-mark">Limpiar filtros</flux:button>
                </x-slot:action>
            @else
                <x-slot:action>
                    <flux:button href="{{ route('winery.subproducts.create') }}" variant="primary" icon="plus">Nuevo subproducto</flux:button>
                </x-slot:action>
            @endif
        </x-agro.empty-state>
    @endif
</div>
