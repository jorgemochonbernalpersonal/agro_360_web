<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Consumo Energético"
        subtitle="Huella de carbono y costes energéticos de la explotación"
        icon="bolt"
    >
        <x-slot:actions>
            <flux:button href="{{ route('viticulturist.energy-usages.create') }}" variant="primary" icon="plus">
                Registrar Consumo
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Tabs --}}
    <x-agro.tabs
        :tabs="[
            'active'   => ['label' => 'Activos',    'count' => $stats['active']],
            'archived' => ['label' => 'Archivados',  'count' => $stats['archived']],
        ]"
        :active="$currentTab"
        wireMethod="switchTab"
    />

    {{-- Filtros --}}
    <x-agro.filter-bar>
        <x-agro.filter-select wire:model.live="filterCampaign" label="Campaña">
            <option value="">Todas</option>
            @foreach($campaigns as $c)
                <option value="{{ $c->id }}">{{ $c->name }}</option>
            @endforeach
        </x-agro.filter-select>

        <x-agro.filter-select wire:model.live="filterEnergyType" label="Tipo de energía">
            <option value="">Todos</option>
            @foreach($energyTypes as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </x-agro.filter-select>

        @if($filterCampaign || $filterEnergyType)
            <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">
                Limpiar
            </flux:button>
        @endif
    </x-agro.filter-bar>

    {{-- Chips filtros activos --}}
    @if($filterCampaign || $filterEnergyType)
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-xs text-zinc-400">Filtros activos:</span>
            @if($filterCampaign)
                @php $camp = $campaigns->firstWhere('id', $filterCampaign); @endphp
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                    {{ $camp?->name ?? $filterCampaign }}
                    <button wire:click="$set('filterCampaign', '')" class="hover:text-agro-900 ml-0.5">
                        <flux:icon icon="x-mark" class="size-3" />
                    </button>
                </span>
            @endif
            @if($filterEnergyType)
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                    {{ $energyTypes[$filterEnergyType] ?? $filterEnergyType }}
                    <button wire:click="$set('filterEnergyType', '')" class="hover:text-agro-900 ml-0.5">
                        <flux:icon icon="x-mark" class="size-3" />
                    </button>
                </span>
            @endif
        </div>
    @endif

    {{-- KPI CO₂ --}}
    @if($co2Total > 0)
        <x-agro.card>
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-emerald-100 flex items-center justify-center">
                    <flux:icon icon="globe-europe-africa" class="w-6 h-6 text-emerald-600" />
                </div>
                <div>
                    <p class="text-sm text-zinc-500">CO₂ total campaña seleccionada</p>
                    <p class="text-2xl font-bold text-zinc-900">{{ number_format($co2Total, 1, ',', '.') }} kg CO₂e</p>
                    <p class="text-xs text-zinc-400">{{ number_format($co2Total / 1000, 3, ',', '.') }} toneladas CO₂ equivalente</p>
                </div>
            </div>
        </x-agro.card>
    @endif

    {{-- Tabla --}}
    <x-agro.card>
        @if($entries->isEmpty())
            <x-agro.empty-state
                icon="bolt"
                title="{{ $currentTab === 'active' ? 'Sin registros energéticos' : 'Sin registros archivados' }}"
                description="{{ $filterCampaign || $filterEnergyType ? 'Ningún registro coincide con los filtros aplicados.' : 'Registra el consumo de gasóleo, electricidad y otros combustibles para calcular tu huella de carbono.' }}"
            >
                @if($filterCampaign || $filterEnergyType)
                    <x-slot:action>
                        <flux:button wire:click="clearFilters" variant="outline" icon="x-mark">Limpiar filtros</flux:button>
                    </x-slot:action>
                @elseif($currentTab === 'active')
                    <x-slot:action>
                        <flux:button href="{{ route('viticulturist.energy-usages.create') }}" variant="primary" icon="plus">
                            Registrar Consumo
                        </flux:button>
                    </x-slot:action>
                @endif
            </x-agro.empty-state>
        @else
            <x-agro.data-table :headers="['Fecha', 'Tipo de energía', 'Cantidad', 'Coste', 'CO₂ (kg)', 'Descripción', 'Acciones']">
                @foreach($entries as $entry)
                    <x-agro.table-row>
                        <x-agro.table-cell>{{ $entry->date->format('d/m/Y') }}</x-agro.table-cell>
                        <x-agro.table-cell>
                            <x-agro.status-badge :status="$entry->energy_type" :label="$entry->energy_type_label" />
                        </x-agro.table-cell>
                        <x-agro.table-cell>{{ number_format($entry->quantity, 3, ',', '.') }} {{ $entry->unit_label }}</x-agro.table-cell>
                        <x-agro.table-cell>{{ $entry->total_cost ? number_format($entry->total_cost, 2, ',', '.') . ' €' : '—' }}</x-agro.table-cell>
                        <x-agro.table-cell>
                            <span class="font-mono text-sm">{{ number_format($entry->co2_kg_equivalent ?? 0, 2, ',', '.') }}</span>
                        </x-agro.table-cell>
                        <x-agro.table-cell>{{ $entry->usage_description ?? '—' }}</x-agro.table-cell>
                        <x-agro.table-cell align="right">
                            <div class="flex items-center justify-end gap-1">
                                @if($currentTab === 'active')
                                    <a href="{{ route('viticulturist.energy-usages.edit', $entry) }}"
                                       class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors"
                                       title="Editar">
                                        <flux:icon icon="pencil-square" class="size-4" />
                                    </a>
                                    <button
                                        wire:click="archive({{ $entry->id }})"
                                        wire:confirm="¿Archivar este registro?"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-amber-600 hover:bg-amber-50 transition-colors"
                                        title="Archivar">
                                        <flux:icon icon="archive-box" class="size-4" />
                                    </button>
                                @else
                                    <button
                                        wire:click="unarchive({{ $entry->id }})"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-agro-600 hover:bg-agro-50 transition-colors"
                                        title="Restaurar">
                                        <flux:icon icon="arrow-uturn-left" class="size-4" />
                                    </button>
                                @endif
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
