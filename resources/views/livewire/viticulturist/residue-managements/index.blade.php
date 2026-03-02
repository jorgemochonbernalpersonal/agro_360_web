<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Gestión de Residuos Agrícolas"
        subtitle="Registro de gestión de podas, orujos y subproductos vitícolas"
        icon="trash"
    >
        <x-slot:actions>
            <flux:button href="{{ route('viticulturist.residue-managements.create') }}" variant="primary" icon="plus">
                Nueva Gestión
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
        <x-agro.filter-select wire:model.live="filterPractice" label="Práctica">
            <option value="">Todas</option>
            @foreach($practiceTypes as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </x-agro.filter-select>
        @if($filterCampaign || $filterPractice)
            <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">Limpiar</flux:button>
        @endif
    </x-agro.filter-bar>

    <x-agro.card>
        @if($entries->isEmpty())
            <x-agro.empty-state
                icon="trash"
                title="Sin registros de gestión de residuos"
                description="Registra cómo gestionas los residuos de poda, orujo y otros subproductos vitícolas."
            >
                <x-slot:action>
                    <flux:button href="{{ route('viticulturist.residue-managements.create') }}" variant="primary" icon="plus">
                        Nueva Gestión
                    </flux:button>
                </x-slot:action>
            </x-agro.empty-state>
        @else
            <x-agro.data-table :headers="['Fecha', 'Parcela', 'Material', 'Práctica', 'Cantidad', 'Acciones']">
                @foreach($entries as $entry)
                    <x-agro.table-row>
                        <x-agro.table-cell>{{ $entry->date->format('d/m/Y') }}</x-agro.table-cell>
                        <x-agro.table-cell>{{ $entry->plot->name ?? ($entry->plotPlanting?->plot->name ?? 'Global campaña') }}</x-agro.table-cell>
                        <x-agro.table-cell>{{ $entry->material_label }}</x-agro.table-cell>
                        <x-agro.table-cell>
                            <x-agro.status-badge :status="$entry->practice_type" :label="$entry->practice_label" />
                        </x-agro.table-cell>
                        <x-agro.table-cell>
                            {{ $entry->estimated_quantity ? number_format($entry->estimated_quantity, 2, ',', '.') . ' ' . $entry->quantity_unit : '-' }}
                        </x-agro.table-cell>
                        <x-agro.table-cell align="right">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('viticulturist.residue-managements.edit', $entry) }}"
                                   class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors"
                                   title="Editar">
                                    <flux:icon icon="pencil-square" class="size-4" />
                                </a>
                                <button
                                    wire:click="deactivate({{ $entry->id }})"
                                    wire:confirm="¿Archivar este registro?"
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
