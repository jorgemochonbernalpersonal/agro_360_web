<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Consumo Energético"
        subtitle="Huella de carbono y costes energéticos de la explotación"
        icon="bolt"
    >
        <x-slot:actions>
            <flux:button variant="primary" icon="plus" wire:click="openCreate">Registrar Consumo</flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    <x-agro.filter-bar>
        <x-agro.filter-select wire:model.live="filterCampaign" label="Campaña">
            <option value="">Todas</option>
            @foreach($campaigns as $c)
                <option value="{{ $c->id }}">{{ $c->name }}</option>
            @endforeach
        </x-agro.filter-select>
        <x-agro.filter-select wire:model.live="filterEnergyType" label="Tipo energía">
            <option value="">Todos</option>
            @foreach($energyTypes as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </x-agro.filter-select>
    </x-agro.filter-bar>

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

    <x-agro.card>
        @if($entries->isEmpty())
            <x-agro.empty-state
                icon="bolt"
                title="Sin registros energéticos"
                description="Registra el consumo de gasóleo, electricidad y otros combustibles para calcular tu huella de carbono."
            />
        @else
            <x-agro.data-table :headers="['Fecha', 'Tipo de energía', 'Cantidad', 'Coste', 'CO₂ (kg)', 'Descripción', 'Acciones']">
                @foreach($entries as $entry)
                    <x-agro.table-row>
                        <x-agro.table-cell>{{ $entry->date->format('d/m/Y') }}</x-agro.table-cell>
                        <x-agro.table-cell>
                            <x-agro.status-badge :status="$entry->energy_type" :label="$entry->energy_type_label" />
                        </x-agro.table-cell>
                        <x-agro.table-cell>{{ number_format($entry->quantity, 3, ',', '.') }} {{ $entry->unit }}</x-agro.table-cell>
                        <x-agro.table-cell>{{ $entry->total_cost ? number_format($entry->total_cost, 2, ',', '.') . ' €' : '-' }}</x-agro.table-cell>
                        <x-agro.table-cell>
                            <span class="font-mono text-sm">{{ number_format($entry->co2_kg_equivalent ?? 0, 2, ',', '.') }}</span>
                        </x-agro.table-cell>
                        <x-agro.table-cell>{{ $entry->usage_description ?? '-' }}</x-agro.table-cell>
                        <x-agro.table-cell align="right">
                            <div class="flex items-center justify-end gap-2">
                                <flux:button size="sm" variant="ghost" icon="pencil" wire:click="openEdit({{ $entry->id }})">Editar</flux:button>
                                <flux:button size="sm" variant="ghost" icon="archive-box" wire:click="deactivate({{ $entry->id }})" wire:confirm="¿Archivar este registro?">Archivar</flux:button>
                            </div>
                        </x-agro.table-cell>
                    </x-agro.table-row>
                @endforeach
            </x-agro.data-table>
            <div class="mt-4">{{ $entries->links() }}</div>
        @endif
    </x-agro.card>

    <flux:modal wire:model="showModal" class="max-w-2xl">
        <div class="p-6 space-y-6">
            <h2 class="text-lg font-semibold text-zinc-900">{{ $editingId ? 'Editar Consumo' : 'Registrar Consumo Energético' }}</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:field>
                    <flux:label required>Campaña</flux:label>
                    <flux:select wire:model="campaign_id">
                        @foreach($campaigns as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="campaign_id" />
                </flux:field>
                <flux:field>
                    <flux:label required>Fecha</flux:label>
                    <flux:input wire:model="date" type="date" />
                    <flux:error name="date" />
                </flux:field>

                <flux:field>
                    <flux:label required>Tipo de energía</flux:label>
                    <flux:select wire:model.live="energy_type">
                        @foreach($energyTypes as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="energy_type" />
                </flux:field>
                <flux:field>
                    <flux:label required>Unidad</flux:label>
                    <flux:select wire:model="unit">
                        @foreach($units as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="unit" />
                </flux:field>

                <flux:field>
                    <flux:label required>Cantidad</flux:label>
                    <flux:input wire:model.live="quantity" type="number" step="0.001" min="0" placeholder="0.000" />
                    <flux:error name="quantity" />
                </flux:field>
                <flux:field>
                    <flux:label>Precio/unidad (€)</flux:label>
                    <flux:input wire:model.live="cost_per_unit" type="number" step="0.0001" min="0" placeholder="0.0000" />
                    <flux:error name="cost_per_unit" />
                </flux:field>

                <flux:field>
                    <flux:label>Coste total (€)</flux:label>
                    <flux:input wire:model="total_cost" type="number" step="0.01" placeholder="0.00" readonly />
                    <flux:description>Calculado automáticamente</flux:description>
                </flux:field>
                <flux:field>
                    <flux:label>CO₂ equivalente (kg)</flux:label>
                    <flux:input wire:model="co2_kg_equivalent" type="number" step="0.001" placeholder="0.000" readonly />
                    <flux:description>Calculado según factor de emisión</flux:description>
                </flux:field>

                <flux:field>
                    <flux:label>Maquinaria asociada</flux:label>
                    <flux:select wire:model="machinery_id">
                        <option value="">Sin especificar</option>
                        @foreach($machinery as $m)
                            <option value="{{ $m->id }}">{{ $m->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="machinery_id" />
                </flux:field>
                <flux:field>
                    <flux:label>Descripción de la operación</flux:label>
                    <flux:input wire:model="usage_description" type="text" placeholder="Ej: Tratamiento fitosanitario parcela norte" />
                    <flux:error name="usage_description" />
                </flux:field>

                <flux:field class="md:col-span-2">
                    <flux:label>Notas</flux:label>
                    <flux:textarea wire:model="notes" rows="2" />
                    <flux:error name="notes" />
                </flux:field>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-zinc-200">
                <flux:button variant="ghost" wire:click="$set('showModal', false)">Cancelar</flux:button>
                <flux:button variant="primary" wire:click="save">{{ $editingId ? 'Actualizar' : 'Registrar' }}</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
