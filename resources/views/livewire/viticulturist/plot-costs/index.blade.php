<div class="space-y-6 animate-fade-in">

<x-agro.page-header
    title="Costes por Parcela"
    description="Registro y análisis de costes por parcela y campaña"
    icon="banknotes"
>
    <x-slot:actions>
        <flux:button href="{{ route('viticulturist.plot-costs.create') }}" variant="primary" icon="plus">
            Registrar Coste
        </flux:button>
    </x-slot:actions>
</x-agro.page-header>

{{-- Filtros --}}
<x-agro.filter-bar>
    <x-agro.filter-select wire:model.live="filter_campaign_id" label="Campaña" placeholder="Todas las campañas">
        @foreach($campaigns as $campaign)
            <option value="{{ $campaign->id }}">Campaña {{ $campaign->year }}</option>
        @endforeach
    </x-agro.filter-select>

    <x-agro.filter-select wire:model.live="filter_plot_id" label="Parcela" placeholder="Todas las parcelas">
        @foreach($plots as $plot)
            <option value="{{ $plot->id }}">{{ $plot->name }}</option>
        @endforeach
    </x-agro.filter-select>

    <x-agro.filter-select wire:model.live="filter_category" label="Categoría" placeholder="Todas las categorías">
        @foreach($categories as $value => $label)
            <option value="{{ $value }}">{{ $label }}</option>
        @endforeach
    </x-agro.filter-select>
</x-agro.filter-bar>

{{-- Total --}}
@if($costs->total() > 0)
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <x-agro.stat-card
            title="Total costes (filtro)"
            :value="number_format($totalAmount, 2) . ' €'"
            icon="banknotes"
            color="red"
        />
        <x-agro.stat-card
            title="Registros"
            :value="$costs->total()"
            icon="document-text"
            color="zinc"
        />
    </div>
@endif

{{-- Tabla --}}
<x-agro.card>
    @if($costs->count() === 0)
        <x-agro.empty-state
            icon="banknotes"
            title="Sin costes registrados"
            description="Registra los costes de tus parcelas: mano de obra, fitosanitarios, maquinaria y más."
        >
            <x-slot:action>
                <flux:button href="{{ route('viticulturist.plot-costs.create') }}" variant="primary" icon="plus">
                    Registrar primer coste
                </flux:button>
            </x-slot:action>
        </x-agro.empty-state>
    @else
        <x-agro.data-table :headers="['Fecha', 'Descripción', 'Categoría', 'Parcela', 'Campaña', 'Importe', 'Acciones']">
            @foreach($costs as $cost)
                <x-agro.table-row>
                    <x-agro.table-cell>
                        <span class="text-sm text-zinc-600">{{ $cost->cost_date->format('d/m/Y') }}</span>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <div>
                            <p class="text-sm font-medium text-zinc-900">{{ $cost->description }}</p>
                            @if($cost->supplier)
                                <p class="text-xs text-zinc-500">{{ $cost->supplier }}</p>
                            @endif
                        </div>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <x-agro.status-badge :label="$cost->category_label" color="blue" />
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <span class="text-sm text-zinc-600">{{ $cost->plot?->name ?? '—' }}</span>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <span class="text-sm text-zinc-600">{{ $cost->campaign ? 'Campaña ' . $cost->campaign->year : '—' }}</span>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <span class="text-sm font-semibold text-red-700">{{ number_format($cost->amount, 2) }} €</span>
                    </x-agro.table-cell>

                    <x-agro.table-cell align="right">
                        <div class="flex items-center justify-end gap-2">
                            <flux:button href="{{ route('viticulturist.plot-costs.edit', $cost->id) }}" size="sm" variant="ghost" icon="pencil">Editar</flux:button>
                            <flux:button wire:click="delete({{ $cost->id }})" wire:confirm="¿Eliminar este coste?" size="sm" variant="ghost" icon="trash">Eliminar</flux:button>
                        </div>
                    </x-agro.table-cell>
                </x-agro.table-row>
            @endforeach
        </x-agro.data-table>

        <div class="mt-4">{{ $costs->links() }}</div>
    @endif
</x-agro.card>

</div>
