<div class="space-y-6 animate-fade-in">
<x-agro.page-header
    title="Análisis de Laboratorio"
    description="Registro y seguimiento de análisis fisicoquímicos de vinos."
    icon="beaker"
>
    <x-slot:actions>
        <flux:button variant="primary" icon="plus" href="{{ roleRoute('wine-analysis.create') }}" wire:navigate>
            Nuevo análisis
        </flux:button>
    </x-slot:actions>
</x-agro.page-header>

<x-agro.card>
    <x-agro.filter-bar>
        <x-agro.filter-input wire:model.live="search" placeholder="Buscar por laboratorio o referencia..." />
        <x-agro.filter-select wire:model.live="typeFilter" placeholder="Todos los tipos">
            @foreach($types as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </x-agro.filter-select>
        <x-agro.filter-select wire:model.live="resultFilter" placeholder="Todos los resultados">
            @foreach($results as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </x-agro.filter-select>
        <x-agro.filter-select wire:model.live="containerFilter" placeholder="Todos los depósitos">
            @foreach($containers as $c)
                <option value="{{ $c->id }}">{{ $c->name }}</option>
            @endforeach
        </x-agro.filter-select>
    </x-agro.filter-bar>

    <x-agro.data-table :headers="['Fecha', 'Vino', 'Tipo', 'Laboratorio', 'Graduación', 'pH', 'Resultado', 'Acciones']">
        @forelse($analyses as $analysis)
            <x-agro.table-row>
                <x-agro.table-cell>
                    {{ $analysis->analysis_date->format('d/m/Y') }}
                </x-agro.table-cell>

                <x-agro.table-cell>
                    @if($analysis->wine)
                        <a href="{{ roleRoute('wines.show', $analysis->wine) }}" wire:navigate
                           class="text-sm font-medium text-zinc-900 dark:text-zinc-100 hover:underline">
                            {{ $analysis->wine->name }}
                        </a>
                        @if($analysis->wine->vintage)
                            <div class="text-xs text-zinc-500">{{ $analysis->wine->vintage }}</div>
                        @endif
                    @else
                        <span class="text-zinc-400">—</span>
                    @endif
                </x-agro.table-cell>

                <x-agro.table-cell>
                    <x-agro.status-badge :label="$analysis->type_label" color="blue" />
                </x-agro.table-cell>

                <x-agro.table-cell>
                    <div class="text-sm text-zinc-700 dark:text-zinc-300">
                        {{ $analysis->laboratory ?? '—' }}
                    </div>
                    @if($analysis->sample_reference)
                        <div class="text-xs text-zinc-500">Ref: {{ $analysis->sample_reference }}</div>
                    @endif
                </x-agro.table-cell>

                <x-agro.table-cell>
                    @if($analysis->alcoholic_strength !== null)
                        {{ number_format($analysis->alcoholic_strength, 2) }} % vol
                    @else
                        <span class="text-zinc-400">—</span>
                    @endif
                </x-agro.table-cell>

                <x-agro.table-cell>
                    {{ $analysis->ph !== null ? number_format($analysis->ph, 2) : '—' }}
                </x-agro.table-cell>

                <x-agro.table-cell>
                    @php
                        $resultColor = match($analysis->result) {
                            'passed' => 'green',
                            'failed' => 'red',
                            default  => 'yellow',
                        };
                    @endphp
                    <x-agro.status-badge :label="$analysis->result_label" :color="$resultColor" />
                </x-agro.table-cell>

                <x-agro.table-cell align="right">
                    <div class="flex justify-end gap-2">
                        <flux:button size="sm" variant="ghost" icon="pencil"
                            href="{{ roleRoute('wine-analysis.edit', $analysis) }}" wire:navigate />
                        <flux:button size="sm" variant="ghost" icon="trash"
                            wire:click="delete({{ $analysis->id }})"
                            wire:loading.attr="disabled"
                            wire:confirm="¿Eliminar este análisis de laboratorio?" />
                    </div>
                </x-agro.table-cell>
            </x-agro.table-row>
        @empty
            <x-agro.empty-state
                icon="beaker"
                title="Sin análisis registrados"
                description="Registra los análisis fisicoquímicos de tus vinos para controlar su calidad." />
        @endforelse
    </x-agro.data-table>

    {{ $analyses->links() }}
</x-agro.card>
</div>
