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
        <x-agro.filter-select wire:model.live="wineFilter" size="sm" class="w-52">
            <flux:select.option value="">Todos los vinos</flux:select.option>
            @foreach($wines as $wine)
                <flux:select.option value="{{ $wine->id }}">{{ $wine->name }}{{ $wine->vintage ? ' ' . $wine->vintage : '' }}</flux:select.option>
            @endforeach
        </x-agro.filter-select>
        @if($search || $wineFilter)
            <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">Limpiar</flux:button>
        @endif
    </x-agro.filter-bar>

    @if($labelings->count() > 0)
        <div wire:loading.class="opacity-60 pointer-events-none" wire:target="search, wineFilter, clearFilters">
            <x-agro.data-table :headers="['Fecha', 'Vino', 'Botellas', 'Lote etiquetas', 'Rango numérico', 'Embotellado', '']">
                @foreach($labelings as $labeling)
                    <x-agro.table-row wire:key="labeling-{{ $labeling->id }}">
                        <x-agro.table-cell>{{ $labeling->labeling_date->format('d/m/Y') }}</x-agro.table-cell>
                        <x-agro.table-cell>
                            <span class="font-medium text-zinc-900">{{ $labeling->wine->name }}</span>
                            @if($labeling->wine->vintage)
                                <span class="text-xs text-zinc-400 ml-1">{{ $labeling->wine->vintage }}</span>
                            @endif
                        </x-agro.table-cell>
                        <x-agro.table-cell align="right">
                            <span class="font-semibold text-zinc-900">{{ number_format($labeling->quantity_labeled) }}</span>
                        </x-agro.table-cell>
                        <x-agro.table-cell>
                            @if($labeling->labelBatch)
                                <a href="{{ roleRoute('label-batches.edit', $labeling->label_batch_id) }}"
                                    class="text-xs text-violet-600 hover:text-violet-800 underline underline-offset-2">
                                    {{ $labeling->labelBatch->name }}
                                </a>
                            @else
                                <span class="text-zinc-300">—</span>
                            @endif
                        </x-agro.table-cell>
                        <x-agro.table-cell class="font-mono text-xs">{{ $labeling->label_range ?? '—' }}</x-agro.table-cell>
                        <x-agro.table-cell>
                            @if($labeling->bottling)
                                <a href="{{ roleRoute('bottling.edit', $labeling->wine_bottling_id) }}"
                                    class="text-xs text-emerald-600 hover:text-emerald-800 underline underline-offset-2">
                                    {{ $labeling->bottling->bottling_date->format('d/m/Y') }}
                                </a>
                            @else
                                <span class="text-zinc-300">—</span>
                            @endif
                        </x-agro.table-cell>
                        <x-agro.table-cell align="right">
                            <div class="flex items-center justify-end gap-1">
                                <x-agro.action-button variant="edit" :href="roleRoute('labeling.edit', $labeling)" />
                                <x-agro.action-button
                                    variant="delete"
                                    wireClick="delete({{ $labeling->id }})"
                                    wireConfirm="¿Eliminar esta sesión? Se devolverán las etiquetas al lote."
                                />
                            </div>
                        </x-agro.table-cell>
                    </x-agro.table-row>
                @endforeach
            </x-agro.data-table>
        </div>

        <x-agro.pagination :paginator="$labelings" />
    @else
        <x-agro.empty-state
            icon="tag"
            title="No hay sesiones de etiquetado"
            :description="$search || $wineFilter ? 'Ninguna sesión coincide con los filtros.' : 'Registra la primera sesión de etiquetado.'"
        >
            @if($search || $wineFilter)
                <x-slot:action>
                    <flux:button wire:click="clearFilters" variant="outline" icon="x-mark">Limpiar filtros</flux:button>
                </x-slot:action>
            @else
                <x-slot:action>
                    <flux:button href="{{ roleRoute('labeling.create') }}" variant="primary" icon="plus">Nueva Sesión</flux:button>
                </x-slot:action>
            @endif
        </x-agro.empty-state>
    @endif
</div>
