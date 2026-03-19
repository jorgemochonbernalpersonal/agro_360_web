<div class="space-y-6 animate-fade-in">
<x-agro.page-header
    title="Controles de Fermentación"
    description="Seguimiento periódico de parámetros de fermentación por vino y depósito."
    icon="beaker"
>
    <x-slot:actions>
        <flux:button variant="primary" icon="plus" href="{{ roleRoute('fermentation-controls.create') }}" wire:navigate>
            Nuevo control
        </flux:button>
    </x-slot:actions>
</x-agro.page-header>

<x-agro.card>
    <x-agro.filter-bar>
        <x-agro.filter-input wire:model.live="search" placeholder="Buscar por vino..." />
        <x-agro.filter-select wire:model.live="wineFilter" placeholder="Todos los vinos">
            @foreach($wines as $wine)
                <option value="{{ $wine->id }}">{{ $wine->name }}{{ $wine->vintage ? ' (' . $wine->vintage . ')' : '' }}</option>
            @endforeach
        </x-agro.filter-select>
        <x-agro.filter-select wire:model.live="statusFilter" placeholder="Todos los estados">
            <option value="fermenting">En fermentación</option>
            <option value="done">Fermentación completada</option>
        </x-agro.filter-select>
        <x-agro.filter-select wire:model.live="containerFilter" placeholder="Todos los depósitos">
            @foreach($containers as $c)
                <option value="{{ $c->id }}">{{ $c->name }}</option>
            @endforeach
        </x-agro.filter-select>
    </x-agro.filter-bar>

    <x-agro.data-table :headers="['Fecha', 'Vino', 'Depósito', 'Tª (°C)', 'Brix', 'Densidad', 'pH', 'Ac. Volátil', 'Estado', 'Acciones']">
        @forelse($controls as $control)
            <x-agro.table-row>
                <x-agro.table-cell>
                    {{ \Carbon\Carbon::parse($control->control_date)->format('d/m/Y H:i') }}
                </x-agro.table-cell>

                <x-agro.table-cell>
                    @if($control->wine)
                        <a href="{{ roleRoute('wines.show', $control->wine) }}" wire:navigate
                           class="text-sm font-medium text-zinc-900 dark:text-zinc-100 hover:underline">
                            {{ $control->wine->name }}
                        </a>
                        @if($control->wine->vintage)
                            <div class="text-xs text-zinc-500">{{ $control->wine->vintage }}</div>
                        @endif
                    @else
                        <span class="text-zinc-400">—</span>
                    @endif
                </x-agro.table-cell>

                <x-agro.table-cell>
                    <span class="text-sm text-zinc-700 dark:text-zinc-300">
                        {{ $control->container?->name ?? '—' }}
                    </span>
                </x-agro.table-cell>

                <x-agro.table-cell>
                    {{ $control->temperature !== null ? number_format($control->temperature, 1) : '—' }}
                </x-agro.table-cell>

                <x-agro.table-cell>
                    {{ $control->brix_degree !== null ? number_format($control->brix_degree, 1) : '—' }}
                </x-agro.table-cell>

                <x-agro.table-cell>
                    {{ $control->density !== null ? number_format($control->density, 4) : '—' }}
                </x-agro.table-cell>

                <x-agro.table-cell>
                    {{ $control->ph !== null ? number_format($control->ph, 2) : '—' }}
                </x-agro.table-cell>

                <x-agro.table-cell>
                    {{ $control->volatile_acidity !== null ? number_format($control->volatile_acidity, 2) . ' g/L' : '—' }}
                </x-agro.table-cell>

                <x-agro.table-cell>
                    @if($control->isFermenting())
                        <x-agro.status-badge label="Fermentando" color="amber" />
                    @else
                        <x-agro.status-badge label="Completada" color="green" />
                    @endif
                </x-agro.table-cell>

                <x-agro.table-cell align="right">
                    <div class="flex justify-end gap-2">
                        <flux:button size="sm" variant="ghost" icon="pencil"
                            href="{{ roleRoute('fermentation-controls.edit', $control) }}" wire:navigate />
                        <flux:button size="sm" variant="ghost" icon="trash"
                            wire:click="delete({{ $control->id }})"
                            wire:loading.attr="disabled"
                            wire:confirm="¿Eliminar este control de fermentación?" />
                    </div>
                </x-agro.table-cell>
            </x-agro.table-row>
        @empty
            <x-agro.empty-state
                icon="beaker"
                title="Sin controles registrados"
                description="Registra los controles periódicos de fermentación de tus vinos." />
        @endforelse
    </x-agro.data-table>

    {{ $controls->links() }}
</x-agro.card>
</div>
