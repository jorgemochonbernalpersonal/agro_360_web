<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Embotellado"
        description="Registro de operaciones de embotellado y materiales utilizados"
    >
        <x-slot:actions>
            <flux:button href="{{ route('winery.bottling.create') }}" variant="primary" icon="plus">
                Nuevo Embotellado
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    <x-agro.filter-bar>
        <x-agro.filter-input
            wire:model.live.debounce.300ms="search"
            placeholder="Buscar por vino o nº lote..."
        />
        <flux:select wire:model.live="wineFilter" size="sm" class="w-56">
            <flux:select.option value="">Todos los vinos</flux:select.option>
            @foreach($wines as $wine)
                <flux:select.option value="{{ $wine->id }}">{{ $wine->name }}{{ $wine->vintage ? ' ' . $wine->vintage : '' }}</flux:select.option>
            @endforeach
        </flux:select>
        @if($search || $wineFilter)
            <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">
                Limpiar
            </flux:button>
        @endif
    </x-agro.filter-bar>

    @if($bottlings->count() > 0)
        <div class="bg-white rounded-2xl border border-zinc-200 overflow-hidden shadow-sm"
            wire:loading.class="opacity-60 pointer-events-none"
            wire:target="search, wineFilter, clearFilters">

            <table class="w-full text-sm">
                <thead class="bg-zinc-50 border-b border-zinc-200">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wide">Fecha</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wide">Vino</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wide">Formato</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-zinc-500 uppercase tracking-wide">Botellas</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-zinc-500 uppercase tracking-wide">Litros</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wide">Nº Lote</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wide">Enólogo</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wide">Lote producto</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @foreach($bottlings as $bottling)
                        <tr class="hover:bg-zinc-50 transition-colors" wire:key="row-{{ $bottling->id }}">
                            <td class="px-4 py-3 text-zinc-700 whitespace-nowrap">
                                {{ $bottling->bottling_date->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="font-medium text-zinc-900">{{ $bottling->wine->name }}</span>
                                @if($bottling->wine->vintage)
                                    <span class="text-xs text-zinc-400 ml-1">{{ $bottling->wine->vintage }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-zinc-600 whitespace-nowrap">
                                {{ $bottling->format_label }}
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-zinc-900 whitespace-nowrap">
                                {{ number_format($bottling->quantity_bottles) }}
                            </td>
                            <td class="px-4 py-3 text-right text-zinc-600 whitespace-nowrap">
                                {{ number_format($bottling->quantity_liters, 1) }} L
                            </td>
                            <td class="px-4 py-3 text-zinc-500">
                                {{ $bottling->lot_number ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-zinc-500">
                                @if($bottling->oenologist)
                                    {{ $bottling->oenologist->surname }}, {{ $bottling->oenologist->name }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($bottling->productLot)
                                    <a href="{{ route('winery.product-lots.edit', $bottling->product_lot_id) }}"
                                        class="text-xs text-agro-600 hover:text-agro-800 underline underline-offset-2">
                                        {{ $bottling->productLot->name }}
                                    </a>
                                @else
                                    <span class="text-zinc-300">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('winery.bottling.edit', $bottling) }}" title="Editar">
                                        <button class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors">
                                            <flux:icon icon="pencil" class="size-4" />
                                        </button>
                                    </a>
                                    @if(! $bottling->product_lot_id)
                                        <button
                                            wire:click="delete({{ $bottling->id }})"
                                            wire:confirm="¿Eliminar este registro de embotellado?"
                                            wire:loading.attr="disabled"
                                            title="Eliminar"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-red-600 hover:bg-red-50 transition-colors">
                                            <flux:icon icon="trash" class="size-4" />
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-2">
            {{ $bottlings->links() }}
        </div>
    @else
        <x-agro.empty-state
            icon="archive-box-arrow-down"
            title="No hay registros de embotellado"
            description="{{ $search || $wineFilter ? 'Ningún embotellado coincide con los filtros.' : 'Registra tu primera operación de embotellado.' }}"
        >
            @if($search || $wineFilter)
                <x-slot:action>
                    <flux:button wire:click="clearFilters" variant="outline" icon="x-mark">Limpiar filtros</flux:button>
                </x-slot:action>
            @else
                <x-slot:action>
                    <flux:button href="{{ route('winery.bottling.create') }}" variant="primary" icon="plus">
                        Nuevo Embotellado
                    </flux:button>
                </x-slot:action>
            @endif
        </x-agro.empty-state>
    @endif
</div>
