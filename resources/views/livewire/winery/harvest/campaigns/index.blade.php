<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Campañas de Vendimia"
        description="Organiza tus recepciones de uva por campaña anual"
    >
        <x-slot:actions>
            <flux:button href="{{ route('winery.campaigns.create') }}" variant="primary" icon="plus">
                Nueva Campaña
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Stats --}}
    <div class="grid grid-cols-2 gap-4">
        <x-agro.stat-card label="Campaña activa" :value="$stats['active'] ? 'Sí' : 'No'" icon="check-circle" />
        <x-agro.stat-card label="Total campañas" :value="$stats['total']" icon="calendar" />
    </div>

    {{-- Filtros --}}
    <x-agro.filter-bar>
        <x-agro.filter-input
            wire:model.live.debounce.300ms="search"
            placeholder="Buscar campaña..."
        />
        <flux:select wire:model.live="yearFilter" size="sm" class="w-32">
            <flux:select.option value="">Todos los años</flux:select.option>
            @foreach($years as $year)
                <flux:select.option value="{{ $year }}">{{ $year }}</flux:select.option>
            @endforeach
        </flux:select>
        @if($search || $yearFilter)
            <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">
                Limpiar
            </flux:button>
        @endif
    </x-agro.filter-bar>

    {{-- Lista --}}
    <x-agro.data-table
        :headers="['Campaña', 'Año', 'Periodo', 'Recepciones', 'Estado', 'Acciones']"
        empty-message="No hay campañas creadas"
        empty-description="Crea una campaña para empezar a registrar recepciones de uva"
    >
        @if($campaigns->count() > 0)
            @foreach($campaigns as $campaign)
                <x-agro.table-row>
                    <x-agro.table-cell>
                        <p class="font-semibold text-zinc-900 text-sm">{{ $campaign->name }}</p>
                        @if($campaign->description)
                            <p class="text-xs text-zinc-400 truncate max-w-xs">{{ $campaign->description }}</p>
                        @endif
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <span class="font-bold text-agro-700">{{ $campaign->year }}</span>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <span class="text-sm text-zinc-600">
                            {{ $campaign->start_date?->format('d/m/Y') }} — {{ $campaign->end_date?->format('d/m/Y') }}
                        </span>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <a href="{{ route('winery.grape-reception.index', ['campaignFilter' => $campaign->id]) }}"
                           class="text-sm text-agro-700 hover:underline">
                            {{ $campaign->activities_count }}
                            {{ Str::plural('recepción', $campaign->activities_count) }}
                        </a>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <flux:badge :color="$campaign->active ? 'green' : 'zinc'" size="sm">
                            {{ $campaign->active ? 'Activa' : 'Cerrada' }}
                        </flux:badge>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <div class="flex items-center justify-end gap-1">
                            <flux:button
                                wire:click="toggleActive({{ $campaign->id }})"
                                variant="ghost"
                                size="xs"
                                :icon="$campaign->active ? 'lock-closed' : 'lock-open'"
                                title="{{ $campaign->active ? 'Cerrar campaña' : 'Activar campaña' }}"
                            />
                            @if($campaign->activities_count === 0)
                                <flux:button
                                    wire:click="delete({{ $campaign->id }})"
                                    wire:confirm="¿Eliminar esta campaña?"
                                    variant="ghost"
                                    size="xs"
                                    icon="trash"
                                    class="text-red-500 hover:text-red-700"
                                />
                            @endif
                        </div>
                    </x-agro.table-cell>
                </x-agro.table-row>
            @endforeach

            <x-slot name="pagination">
                {{ $campaigns->links() }}
            </x-slot>
        @endif
    </x-agro.data-table>
</div>
