<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Mermas y Pérdidas"
        description="Registro de mermas, filtraciones y pérdidas de vino elaborado"
    >
        <x-slot:actions>
            <flux:button href="{{ route('winery.wine-losses.create') }}" variant="primary" icon="plus">
                Nueva Merma
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    <x-agro.filter-bar>
        <x-agro.filter-input
            wire:model.live.debounce.300ms="search"
            placeholder="Buscar por vino o contenedor..."
        />
        <flux:select wire:model.live="wineFilter" size="sm" class="w-44">
            <flux:select.option value="">Todos los vinos</flux:select.option>
            @foreach($wines as $wine)
                <flux:select.option value="{{ $wine->id }}">{{ $wine->name }}</flux:select.option>
            @endforeach
        </flux:select>
        <flux:select wire:model.live="typeFilter" size="sm" class="w-48">
            <flux:select.option value="">Todos los tipos</flux:select.option>
            @foreach($lossTypes as $key => $label)
                <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
            @endforeach
        </flux:select>
        @if($search || $wineFilter || $typeFilter)
            <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">Limpiar</flux:button>
        @endif
    </x-agro.filter-bar>

    @if($losses->count())
        <x-agro.data-table
            :headers="['Fecha', 'Vino', 'Contenedor', 'Tipo', 'Cantidad', 'Notas', '']"
        >
            @foreach($losses as $loss)
                <x-agro.table-row wire:key="loss-{{ $loss->id }}">
                    <x-agro.table-cell>
                        <span class="text-sm text-zinc-600">
                            {{ $loss->loss_date instanceof \Carbon\Carbon ? $loss->loss_date->format('d/m/Y') : $loss->loss_date }}
                        </span>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <span class="font-medium text-zinc-800">{{ $loss->wine?->name ?? '—' }}</span>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <span class="text-zinc-500 text-sm">{{ $loss->container?->name ?? '—' }}</span>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        @php
                            $typeColors = [
                                'evaporation' => 'yellow',
                                'filtration'  => 'blue',
                                'sampling'    => 'zinc',
                                'spillage'    => 'red',
                                'other'       => 'zinc',
                            ];
                        @endphp
                        <flux:badge color="{{ $typeColors[$loss->loss_type] ?? 'zinc' }}" size="sm">
                            {{ $lossTypes[$loss->loss_type] ?? $loss->loss_type }}
                        </flux:badge>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <span class="font-mono text-red-600 font-semibold">
                            −{{ number_format($loss->quantity, 2) }}
                            {{ $loss->unitOfMeasurement?->symbol ?? $loss->unitOfMeasurement?->name ?? '' }}
                        </span>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <span class="text-xs text-zinc-400 truncate max-w-xs">{{ Str::limit($loss->notes ?? '', 60) }}</span>
                    </x-agro.table-cell>

                    <x-agro.table-cell class="text-right">
                        <div class="flex items-center justify-end gap-1">
                            <a href="{{ route('winery.wine-losses.edit', $loss) }}" title="Editar">
                                <button class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors">
                                    <flux:icon icon="pencil" class="size-4" />
                                </button>
                            </a>
                            <button
                                wire:click="delete({{ $loss->id }})"
                                wire:confirm="¿Eliminar esta merma? Se restaurarán los litros al contenedor."
                                wire:loading.attr="disabled"
                                title="Eliminar"
                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-red-600 hover:bg-red-50 transition-colors"
                            >
                                <flux:icon icon="trash" class="size-4" />
                            </button>
                        </div>
                    </x-agro.table-cell>
                </x-agro.table-row>
            @endforeach
        </x-agro.data-table>

        <div class="mt-2">{{ $losses->links() }}</div>
    @else
        <x-agro.empty-state
            icon="exclamation-triangle"
            title="Sin mermas registradas"
            :description="$search || $wineFilter || $typeFilter ? 'Ninguna merma coincide con los filtros aplicados.' : 'Registra evaporaciones, filtraciones o pérdidas accidentales de vino.'"
        >
            @if($search || $wineFilter || $typeFilter)
                <x-slot:action>
                    <flux:button wire:click="clearFilters" variant="outline" icon="x-mark">Limpiar filtros</flux:button>
                </x-slot:action>
            @else
                <x-slot:action>
                    <flux:button href="{{ route('winery.wine-losses.create') }}" variant="primary" icon="plus">
                        Nueva Merma
                    </flux:button>
                </x-slot:action>
            @endif
        </x-agro.empty-state>
    @endif

</div>
