<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Análisis de Residuos Fitosanitarios"
        subtitle="Control de LMR (Límites Máximos de Residuos)"
        icon="beaker"
    >
        <x-slot:actions>
            <flux:button href="{{ route('viticulturist.residue-analyses.create') }}" variant="primary" icon="plus">
                Nuevo Análisis
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    <x-agro.filter-bar>
        <x-agro.filter-select wire:model.live="filterCampaign" label="Campaña">
            <option value="">Todas las campañas</option>
            @foreach($campaigns as $c)
                <option value="{{ $c->id }}">{{ $c->name }}</option>
            @endforeach
        </x-agro.filter-select>
        <x-agro.filter-select wire:model.live="filterCompliant" label="Resultado">
            <option value="">Todos</option>
            <option value="1">Conforme</option>
            <option value="0">No conforme</option>
        </x-agro.filter-select>
        @if($filterCampaign || $filterCompliant !== '')
            <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">Limpiar</flux:button>
        @endif
    </x-agro.filter-bar>

    <x-agro.card>
        @if($entries->isEmpty())
            <x-agro.empty-state
                icon="beaker"
                title="Sin análisis registrados"
                description="Registra los análisis de residuos de laboratorio para verificar el cumplimiento de los LMR."
            >
                <x-slot:action>
                    <flux:button href="{{ route('viticulturist.residue-analyses.create') }}" variant="primary" icon="plus">
                        Nuevo Análisis
                    </flux:button>
                </x-slot:action>
            </x-agro.empty-state>
        @else
            <x-agro.data-table :headers="['Fecha', 'Laboratorio', 'Plantación', 'Muestra', 'Resultado', 'Acciones']">
                @foreach($entries as $entry)
                    <x-agro.table-row>
                        <x-agro.table-cell>{{ $entry->analysis_date->format('d/m/Y') }}</x-agro.table-cell>
                        <x-agro.table-cell>
                            {{ $entry->laboratory_name }}
                            @if($entry->laboratory_accreditation)
                                <span class="text-zinc-400 text-xs block">ENAC: {{ $entry->laboratory_accreditation }}</span>
                            @endif
                        </x-agro.table-cell>
                        <x-agro.table-cell>{{ $entry->plotPlanting?->plot->name ?? 'Global campaña' }}</x-agro.table-cell>
                        <x-agro.table-cell>{{ $entry->sample_type ?? '-' }}</x-agro.table-cell>
                        <x-agro.table-cell>
                            @if($entry->overall_compliant)
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                    <flux:icon icon="check-circle" class="w-3 h-3" /> Conforme
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <flux:icon icon="x-circle" class="w-3 h-3" /> No conforme
                                </span>
                            @endif
                        </x-agro.table-cell>
                        <x-agro.table-cell align="right">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('viticulturist.residue-analyses.edit', $entry) }}"
                                   class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors"
                                   title="Editar">
                                    <flux:icon icon="pencil-square" class="size-4" />
                                </a>
                                <button
                                    wire:click="deactivate({{ $entry->id }})"
                                    wire:confirm="¿Archivar este análisis?"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-amber-600 hover:bg-amber-50 transition-colors"
                                    title="Archivar">
                                    <flux:icon icon="archive-box" class="size-4" />
                                </button>
                            </div>
                        </x-agro.table-cell>
                    </x-agro.table-row>
                @endforeach
            </x-agro.data-table>
            @if($entries->hasPages())
                <div class="mt-4">{{ $entries->links() }}</div>
            @endif
        @endif
    </x-agro.card>

</div>
