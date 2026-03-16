<x-agro.form-card
    title="Editar Mantenimiento"
    description="{{ $container->name }} — modifica los datos del mantenimiento."
    icon="wrench-screwdriver"
    icon-color="from-zinc-500 to-zinc-700"
    :back-url="roleRoute('containers.maintenance.index', $container)"
>
    <form wire:submit="save" class="space-y-8">

        <x-agro.form-section title="Tipo y Descripción" color="green">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <flux:field>
                    <flux:label required>Tipo de mantenimiento</flux:label>
                    <flux:select wire:model.live="maintenance_type" required>
                        @foreach($types as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="maintenance_type" />
                </flux:field>

                <flux:field class="md:col-span-2">
                    <flux:label required>Nombre / descripción breve</flux:label>
                    <flux:input wire:model="maintenance_name" type="text" required />
                    <flux:error name="maintenance_name" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-section title="Fechas y Estado" color="blue">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <flux:field>
                    <flux:label required>Fecha programada</flux:label>
                    <flux:input wire:model="scheduled_date" type="date" required />
                    <flux:error name="scheduled_date" />
                </flux:field>

                <flux:field>
                    <flux:label>Fecha de realización</flux:label>
                    <flux:input wire:model="performed_date" type="date" />
                    <flux:error name="performed_date" />
                </flux:field>

                <flux:field>
                    <flux:label>Próximo mantenimiento</flux:label>
                    <flux:input wire:model="next_maintenance_date" type="date" />
                    <flux:error name="next_maintenance_date" />
                </flux:field>

                <flux:field>
                    <flux:label required>Estado</flux:label>
                    <flux:select wire:model="status" required>
                        @foreach($statuses as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="status" />
                </flux:field>

                <flux:field>
                    <flux:label>Coste (€)</flux:label>
                    <flux:input wire:model="cost" type="number" step="0.01" min="0" />
                    <flux:error name="cost" />
                </flux:field>

                <flux:field>
                    <flux:label>Realizado por</flux:label>
                    <flux:input wire:model="performed_by" type="text" />
                    <flux:error name="performed_by" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-section title="Notas" color="green">
            <flux:field>
                <flux:label>Observaciones</flux:label>
                <flux:textarea wire:model="notes" rows="3" />
                <flux:error name="notes" />
            </flux:field>
        </x-agro.form-section>

        {{-- Insumos --}}
        <x-agro.form-section title="Insumos utilizados" color="blue">
            @foreach($supplies as $i => $row)
                <div class="grid grid-cols-1 md:grid-cols-5 gap-3 items-end mb-3 p-3 bg-zinc-50 dark:bg-zinc-800/50 rounded-lg">
                    <flux:field class="md:col-span-2">
                        <flux:label>Insumo</flux:label>
                        <flux:select wire:model="supplies.{{ $i }}.winery_supply_id">
                            <option value="">Seleccionar catálogo...</option>
                            @foreach($supplies as $s)
                                @if(is_object($s))
                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endif
                            @endforeach
                        </flux:select>
                    </flux:field>
                    <flux:field>
                        <flux:label>Nombre libre</flux:label>
                        <flux:input wire:model="supplies.{{ $i }}.supply_name" type="text" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Cantidad</flux:label>
                        <flux:input wire:model="supplies.{{ $i }}.quantity_used" type="number" step="0.001" min="0" />
                    </flux:field>
                    <div class="flex gap-2 items-end">
                        <flux:field class="flex-1">
                            <flux:label>Unidad</flux:label>
                            <flux:select wire:model="supplies.{{ $i }}.unit_of_measurement_id">
                                <option value="">—</option>
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}">{{ $unit->symbol }}</option>
                                @endforeach
                            </flux:select>
                        </flux:field>
                        <flux:button type="button" variant="ghost" icon="trash" size="sm"
                            wire:click="removeSupply({{ $i }})" />
                    </div>
                </div>
            @endforeach
            <flux:button type="button" variant="ghost" icon="plus" size="sm" wire:click="addSupply">
                Añadir insumo
            </flux:button>
        </x-agro.form-section>

        {{-- Residuos --}}
        <x-agro.form-section title="Residuos generados" color="yellow">
            @foreach($wastes as $i => $row)
                <div class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end mb-3 p-3 bg-zinc-50 dark:bg-zinc-800/50 rounded-lg">
                    <flux:field>
                        <flux:label>Tipo de residuo</flux:label>
                        <flux:select wire:model="wastes.{{ $i }}.container_waste_type_id">
                            <option value="">Seleccionar...</option>
                            @foreach($wasteTypes as $wt)
                                <option value="{{ $wt->id }}">{{ $wt->name }}</option>
                            @endforeach
                        </flux:select>
                    </flux:field>
                    <flux:field>
                        <flux:label>Fecha</flux:label>
                        <flux:input wire:model="wastes.{{ $i }}.waste_date" type="date" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Cantidad</flux:label>
                        <flux:input wire:model="wastes.{{ $i }}.quantity" type="number" step="0.001" min="0" />
                    </flux:field>
                    <div class="flex gap-2 items-end">
                        <flux:field class="flex-1">
                            <flux:label>Método eliminación</flux:label>
                            <flux:input wire:model="wastes.{{ $i }}.disposal_method" type="text" />
                        </flux:field>
                        <flux:button type="button" variant="ghost" icon="trash" size="sm"
                            wire:click="removeWaste({{ $i }})" />
                    </div>
                </div>
            @endforeach
            <flux:button type="button" variant="ghost" icon="plus" size="sm" wire:click="addWaste">
                Añadir residuo
            </flux:button>
        </x-agro.form-section>

        <x-agro.form-actions :back-url="roleRoute('containers.maintenance.index', $container)" submit-label="Actualizar mantenimiento" />
    </form>
</x-agro.form-card>
