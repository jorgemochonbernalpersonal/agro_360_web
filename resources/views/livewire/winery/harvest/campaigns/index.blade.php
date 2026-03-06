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

    <x-agro.card>
        @if($campaigns->isEmpty())
            <x-agro.empty-state
                icon="calendar"
                title="Sin campañas creadas"
                description="Crea una campaña para empezar a registrar recepciones de uva."
            >
                <x-slot:action>
                    <flux:button href="{{ route('winery.campaigns.create') }}" variant="primary" icon="plus">
                        Nueva Campaña
                    </flux:button>
                </x-slot:action>
            </x-agro.empty-state>
        @else
            <x-agro.data-table
                :headers="['Campaña', 'Año', 'Periodo', 'Recepciones', 'Estado', 'Acciones']"
            >
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

                        <x-agro.table-cell align="right">
                            <div class="flex items-center justify-end gap-1">
                                <button
                                    wire:click="toggleActive({{ $campaign->id }})"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-agro-700 hover:bg-agro-50 transition-colors"
                                    title="{{ $campaign->active ? 'Cerrar campaña' : 'Activar campaña' }}"
                                >
                                    <flux:icon :icon="$campaign->active ? 'lock-closed' : 'lock-open'" class="size-4" />
                                </button>
                                @if($campaign->activities_count === 0)
                                    <button
                                        wire:click="delete({{ $campaign->id }})"
                                        wire:confirm="¿Eliminar esta campaña?"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-red-600 hover:bg-red-50 transition-colors"
                                        title="Eliminar campaña"
                                    >
                                        <flux:icon icon="trash" class="size-4" />
                                    </button>
                                @endif
                            </div>
                        </x-agro.table-cell>
                    </x-agro.table-row>
                @endforeach

                <x-slot name="pagination">
                    {{ $campaigns->links() }}
                </x-slot>
            </x-agro.data-table>
        @endif
    </x-agro.card>

</div>
