<x-agro.form-card
    title="Editar Equipo ITB/ITEA"
    :description="'Modifica los datos de ' . $fieldEquipment->name"
    :back-url="roleRoute('viticulturist.field-equipment.index')"
>
    <form wire:submit="save" class="space-y-8">
        <x-agro.form-section title="Datos del Equipo">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <flux:field>
                    <flux:label required>Nombre</flux:label>
                    <flux:input wire:model="name" type="text" />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label required>Tipo de equipo</flux:label>
                    <flux:select wire:model="equipment_type">
                        @foreach($types as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="equipment_type" />
                </flux:field>

                <flux:field>
                    <flux:label>Número de matrícula/registro</flux:label>
                    <flux:input wire:model="registration_number" type="text" />
                    <flux:error name="registration_number" />
                </flux:field>

                <flux:field>
                    <flux:label>Fecha de compra</flux:label>
                    <flux:input wire:model="purchase_date" type="date" />
                    <flux:error name="purchase_date" />
                </flux:field>

            </div>
        </x-agro.form-section>

        <x-agro.form-section title="Inspecciones">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <flux:field>
                    <flux:label>Última inspección</flux:label>
                    <flux:input wire:model="last_inspection_date" type="date" />
                    <flux:error name="last_inspection_date" />
                </flux:field>

                <flux:field>
                    <flux:label>Próxima inspección</flux:label>
                    <flux:input wire:model="next_inspection_date" type="date" />
                    <flux:error name="next_inspection_date" />
                </flux:field>

                <flux:field class="md:col-span-2">
                    <flux:label>Entidad inspectora</flux:label>
                    <flux:input wire:model="inspection_entity" type="text" />
                    <flux:error name="inspection_entity" />
                </flux:field>

                <flux:field class="md:col-span-2">
                    <flux:label>Notas</flux:label>
                    <flux:textarea wire:model="notes" rows="3" />
                    <flux:error name="notes" />
                </flux:field>

            </div>
        </x-agro.form-section>

        <x-agro.form-actions
            :cancel-url="roleRoute('viticulturist.field-equipment.index')"
            submit-label="Actualizar Equipo"
        />
    </form>
</x-agro.form-card>
