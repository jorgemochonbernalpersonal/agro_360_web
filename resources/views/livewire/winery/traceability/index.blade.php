<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Trazabilidad"
        description="Consulta el historial completo de cada vino desde la uva hasta la etiqueta."
    />

    <x-agro.filter-bar>
        <x-agro.filter-input wire:model.live.debounce.300ms="search" placeholder="Buscar vino o código..." />
        <x-agro.filter-select wire:model.live="vintageFilter" size="sm" class="min-w-32">
            <flux:select.option value="">Todas las añadas</flux:select.option>
            @foreach($vintages as $v)
                <flux:select.option value="{{ $v }}">{{ $v }}</flux:select.option>
            @endforeach
        </x-agro.filter-select>
        <x-agro.filter-select wire:model.live="statusFilter" size="sm" class="min-w-40">
            <flux:select.option value="">Todos los estados</flux:select.option>
            @foreach($statuses as $key => $label)
                <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
            @endforeach
        </x-agro.filter-select>
        @if($search || $vintageFilter || $statusFilter)
            <flux:button wire:click="resetFilters" variant="ghost" size="sm" icon="x-mark">Limpiar</flux:button>
        @endif
    </x-agro.filter-bar>

    @if($wines->count() > 0)
        <div wire:loading.class="opacity-60 pointer-events-none" wire:target="search, vintageFilter, statusFilter, resetFilters">
            <x-agro.data-table :headers="['Vino', 'Añada', 'Estado', 'Orígenes', 'Procesos', 'Embotellados', '']">
                @foreach($wines as $wine)
                    <x-agro.table-row wire:key="wine-{{ $wine->id }}">
                        <x-agro.table-cell>
                            <p class="font-medium text-zinc-900">{{ $wine->name }}</p>
                            @if($wine->internal_code)
                                <p class="text-xs text-zinc-400">{{ $wine->internal_code }}</p>
                            @endif
                        </x-agro.table-cell>
                        <x-agro.table-cell>{{ $wine->vintage ?? '—' }}</x-agro.table-cell>
                        <x-agro.table-cell>
                            <x-agro.status-badge
                                :label="$wine->status_label"
                                :type="match($wine->status) {
                                    'in_progress' => 'info',
                                    'aged'        => 'warning',
                                    'bottled'     => 'success',
                                    'sold'        => 'gray',
                                    default       => 'gray',
                                }"
                            />
                        </x-agro.table-cell>
                        <x-agro.table-cell align="center">{{ $wine->wineHarvests->count() }}</x-agro.table-cell>
                        <x-agro.table-cell align="center">{{ $wine->processDetails->count() }}</x-agro.table-cell>
                        <x-agro.table-cell align="center">{{ $wine->bottlings->count() }}</x-agro.table-cell>
                        <x-agro.table-cell align="right">
                            <div class="flex items-center justify-end gap-1">
                                <flux:button size="sm" variant="ghost" icon="eye"
                                    href="{{ roleRoute('wines.show', $wine) }}" wire:navigate>
                                    Ver vino
                                </flux:button>
                                @if($wine->trace_token)
                                    <flux:button size="sm" variant="ghost" icon="arrow-down-tray"
                                        href="{{ route('winery.wines.traceability-pdf', $wine) }}"
                                        target="_blank">
                                        PDF
                                    </flux:button>
                                @endif
                            </div>
                        </x-agro.table-cell>
                    </x-agro.table-row>
                @endforeach
            </x-agro.data-table>
        </div>

        <x-agro.pagination :paginator="$wines" />
    @else
        <x-agro.empty-state
            icon="magnifying-glass-circle"
            title="No se encontraron vinos"
            :description="$search || $vintageFilter || $statusFilter ? 'Ningún vino coincide con los filtros actuales.' : 'Cuando registres vinos aparecerán aquí con su trazabilidad completa.'"
        >
            @if($search || $vintageFilter || $statusFilter)
                <x-slot:action>
                    <flux:button wire:click="resetFilters" variant="outline" icon="x-mark">Limpiar filtros</flux:button>
                </x-slot:action>
            @endif
        </x-agro.empty-state>
    @endif
</div>
