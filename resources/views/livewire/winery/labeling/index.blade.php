<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Etiquetado"
        description="Registro de sesiones de etiquetado y consumo de etiquetas"
    >
        <x-slot:actions>
            <flux:button href="{{ roleRoute('label-batches.index') }}" variant="ghost" icon="tag" size="sm">
                Gestionar lotes
            </flux:button>
            <flux:button href="{{ roleRoute('labeling.create') }}" variant="primary" icon="plus">
                Nueva Sesión
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    <x-agro.filter-bar>
        <x-agro.filter-input
            wire:model.live.debounce.300ms="search"
            placeholder="Buscar por vino..."
        />
        <flux:select wire:model.live="wineFilter" size="sm" class="w-52">
            <flux:select.option value="">Todos los vinos</flux:select.option>
            @foreach($wines as $wine)
                <flux:select.option value="{{ $wine->id }}">{{ $wine->name }}{{ $wine->vintage ? ' ' . $wine->vintage : '' }}</flux:select.option>
            @endforeach
        </flux:select>
        @if($search || $wineFilter)
            <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">Limpiar</flux:button>
        @endif
    </x-agro.filter-bar>

    @if($labelings->count() > 0)
        <div class="bg-white rounded-2xl border border-zinc-200 overflow-hidden shadow-sm"
            wire:loading.class="opacity-60 pointer-events-none"
            wire:target="search, wineFilter, clearFilters">

            <table class="w-full text-sm">
                <thead class="bg-zinc-50 border-b border-zinc-200">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wide">Fecha</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wide">Vino</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-zinc-500 uppercase tracking-wide">Botellas</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wide">Lote etiquetas</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wide">Rango numérico</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wide">Embotellado</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @foreach($labelings as $labeling)
                        <tr class="hover:bg-zinc-50 transition-colors" wire:key="labeling-{{ $labeling->id }}">
                            <td class="px-4 py-3 text-zinc-700 whitespace-nowrap">
                                {{ $labeling->labeling_date->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="font-medium text-zinc-900">{{ $labeling->wine->name }}</span>
                                @if($labeling->wine->vintage)
                                    <span class="text-xs text-zinc-400 ml-1">{{ $labeling->wine->vintage }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-zinc-900">
                                {{ number_format($labeling->quantity_labeled) }}
                            </td>
                            <td class="px-4 py-3">
                                @if($labeling->labelBatch)
                                    <a href="{{ roleRoute('label-batches.edit', $labeling->label_batch_id) }}"
                                        class="text-xs text-violet-600 hover:text-violet-800 underline underline-offset-2">
                                        {{ $labeling->labelBatch->name }}
                                    </a>
                                @else
                                    <span class="text-zinc-300">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-zinc-500 font-mono text-xs">
                                {{ $labeling->label_range ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-zinc-500">
                                @if($labeling->bottling)
                                    <a href="{{ roleRoute('bottling.edit', $labeling->wine_bottling_id) }}"
                                        class="text-xs text-emerald-600 hover:text-emerald-800 underline underline-offset-2">
                                        {{ $labeling->bottling->bottling_date->format('d/m/Y') }}
                                    </a>
                                @else
                                    <span class="text-zinc-300">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ roleRoute('labeling.edit', $labeling) }}" title="Editar">
                                        <button class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors">
                                            <flux:icon icon="pencil" class="size-4" />
                                        </button>
                                    </a>
                                    <button
                                        wire:click="delete({{ $labeling->id }})"
                                        wire:confirm="¿Eliminar esta sesión? Se devolverán las etiquetas al lote."
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

        <div class="mt-2">{{ $labelings->links() }}</div>
    @else
        <x-agro.empty-state
            icon="tag"
            title="No hay sesiones de etiquetado"
            description="{{ $search || $wineFilter ? 'Ninguna sesión coincide con los filtros.' : 'Registra la primera sesión de etiquetado.' }}"
        >
            @if($search || $wineFilter)
                <x-slot:action>
                    <flux:button wire:click="clearFilters" variant="outline" icon="x-mark">Limpiar filtros</flux:button>
                </x-slot:action>
            @else
                <x-slot:action>
                    <flux:button href="{{ roleRoute('labeling.create') }}" variant="primary" icon="plus">
                        Nueva Sesión
                    </flux:button>
                </x-slot:action>
            @endif
        </x-agro.empty-state>
    @endif
</div>
