<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Gestión de Residuos Agrícolas"
        subtitle="Registro de gestión de podas, orujos y subproductos vitícolas"
        icon="trash"
    >
        <x-slot:actions>
            <flux:button variant="primary" icon="plus" wire:click="openCreate">Nueva Gestión</flux:button>
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
    </x-agro.filter-bar>

    <x-agro.card>
        @if($entries->isEmpty())
            <x-agro.empty-state
                icon="trash"
                title="Sin registros de gestión de residuos"
                description="Registra cómo gestionas los residuos de poda, orujo y otros subproductos vitícolas."
            />
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
            <h2 class="text-lg font-semibold text-zinc-900">
                {{ $editingId ? 'Editar Gestión de Residuos' : 'Nueva Gestión de Residuos' }}
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:field>
                    <flux:label required>Campaña</flux:label>
                    <flux:select wire:model="campaign_id">
                        <option value="">Seleccionar...</option>
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
                    <flux:label>Parcela (opcional)</flux:label>
                    <flux:select wire:model="plot_id">
                        <option value="">Global campaña</option>
                        @foreach($plots as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="plot_id" />
                </flux:field>
                <flux:field>
                    <flux:label>Plantación (opcional)</flux:label>
                    <flux:select wire:model="plot_planting_id">
                        <option value="">Sin especificar</option>
                        @foreach($plantings as $p)
                            <option value="{{ $p->id }}">{{ $p->plot->name }} — {{ $p->grape->name ?? '' }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="plot_planting_id" />
                </flux:field>

                <flux:field>
                    <flux:label required>Tipo de material</flux:label>
                    <flux:select wire:model="material_type">
                        <option value="">Seleccionar...</option>
                        @foreach($materialTypes as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="material_type" />
                </flux:field>
                <flux:field>
                    <flux:label required>Práctica aplicada</flux:label>
                    <flux:select wire:model.live="practice_type">
                        <option value="">Seleccionar...</option>
                        @foreach($practiceTypes as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="practice_type" />
                </flux:field>

                <flux:field>
                    <flux:label>Cantidad estimada</flux:label>
                    <flux:input wire:model="estimated_quantity" type="number" step="0.01" min="0" placeholder="0.00" />
                    <flux:error name="estimated_quantity" />
                </flux:field>
                <flux:field>
                    <flux:label>Unidad</flux:label>
                    <flux:select wire:model="quantity_unit">
                        <option value="kg">kg</option>
                        <option value="t">Toneladas (t)</option>
                    </flux:select>
                    <flux:error name="quantity_unit" />
                </flux:field>

                @if($practice_type === 'burning')
                    <flux:field class="md:col-span-2">
                        <flux:label required>Justificación (obligatoria para quema)</flux:label>
                        <flux:textarea wire:model="justification" rows="3" placeholder="Justifica la necesidad de la quema..." />
                        <flux:error name="justification" />
                    </flux:field>
                @endif

                <flux:field class="md:col-span-2">
                    <flux:label>Notas</flux:label>
                    <flux:textarea wire:model="notes" rows="2" />
                    <flux:error name="notes" />
                </flux:field>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-zinc-200">
                <flux:button variant="ghost" wire:click="$set('showModal', false)">Cancelar</flux:button>
                <flux:button variant="primary" wire:click="save">
                    {{ $editingId ? 'Actualizar' : 'Registrar Gestión' }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
