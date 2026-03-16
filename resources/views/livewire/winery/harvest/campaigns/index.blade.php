<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Campañas de Vendimia"
        description="Las campañas se crean automáticamente al registrar una recepción de uva"
    >
        <x-slot:actions>
            <flux:button href="{{ roleRoute('grape-reception.create') }}" wire:navigate variant="primary" icon="plus">
                Nueva Recepción
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    <x-agro.filter-bar :active-count="collect([$search, $yearFilter])->filter()->count()">
        <x-agro.filter-input
            wire:model.live.debounce.300ms="search"
            placeholder="Buscar campaña..."
        />
        <x-agro.filter-select wire:model.live="yearFilter" label="Año">
            <option value="">Todos los años</option>
            @foreach($years as $year)
                <option value="{{ $year }}">{{ $year }}</option>
            @endforeach
        </x-agro.filter-select>
    </x-agro.filter-bar>

    <x-agro.card>
        @if($campaigns->isEmpty())
            <x-agro.empty-state
                icon="calendar"
                title="Sin campañas creadas"
                description="Crea una campaña para empezar a registrar recepciones de uva."
            >
                <x-slot:action>
                    <flux:button href="{{ roleRoute('campaigns.create') }}" variant="primary" icon="plus">
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
                            <a href="{{ roleRoute('grape-reception.index', ['campaignFilter' => $campaign->id]) }}"
                               wire:navigate
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
                                @if(!$campaign->locked_at)
                                    <flux:button size="sm" variant="ghost" icon="pencil-square"
                                        href="{{ roleRoute('campaigns.edit', $campaign) }}"
                                        wire:navigate
                                        title="Editar campaña"
                                    />
                                @endif
                                <flux:button size="sm" variant="ghost"
                                    :icon="$campaign->active ? 'lock-closed' : 'lock-open'"
                                    wire:click="toggleActive({{ $campaign->id }})"
                                    wire:loading.attr="disabled"
                                    title="{{ $campaign->active ? 'Cerrar campaña' : 'Activar campaña' }}"
                                />
                                @if($campaign->activities_count === 0)
                                    <flux:button size="sm" variant="ghost" icon="trash"
                                        class="text-red-500"
                                        wire:click="delete({{ $campaign->id }})"
                                        wire:loading.attr="disabled"
                                        wire:confirm="¿Eliminar esta campaña?"
                                        title="Eliminar campaña"
                                    />
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
