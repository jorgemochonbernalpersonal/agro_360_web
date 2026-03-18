<x-agro.form-card
    title="Editar Operación de Bodega"
    description="Modifica los datos de la operación enológica."
    icon="calendar-days"
    icon-color="from-indigo-500 to-indigo-700"
    :back-url="roleRoute('cellar-operations.index')"
>
    <form wire:submit="save" class="space-y-8">
        <x-agro.form-section title="Identificación" color="indigo">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <flux:field>
                    <flux:label required>Tipo de operación</flux:label>
                    <flux:select wire:model="operation_type" required>
                        @foreach($types as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="operation_type" />
                </flux:field>

                <flux:field>
                    <flux:label required>Fecha</flux:label>
                    <flux:input wire:model="operation_date" type="date" required />
                    <flux:error name="operation_date" />
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
            </div>
        </x-agro.form-section>

        <x-agro.form-section title="Contenedores y Volumen" color="blue">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <flux:field>
                    <flux:label>Contenedor origen</flux:label>
                    <flux:select wire:model="source_container_id">
                        <option value="">Sin contenedor</option>
                        @foreach($containers as $container)
                            <option value="{{ $container->id }}">{{ $container->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="source_container_id" />
                </flux:field>

                <flux:field>
                    <flux:label>Contenedor destino</flux:label>
                    <flux:select wire:model="target_container_id">
                        <option value="">Sin contenedor</option>
                        @foreach($containers as $container)
                            <option value="{{ $container->id }}">{{ $container->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="target_container_id" />
                </flux:field>

                <flux:field>
                    <flux:label>Volumen (litros)</flux:label>
                    <flux:input wire:model="volume_liters" type="number" step="0.01" min="0" />
                    <flux:error name="volume_liters" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-section title="Responsable y Notas" color="green">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:field>
                    <flux:label>Responsable</flux:label>
                    <flux:input wire:model="responsible_person" type="text" />
                    <flux:error name="responsible_person" />
                </flux:field>
            </div>
            <div class="mt-4">
                <flux:field>
                    <flux:label>Notas</flux:label>
                    <flux:textarea wire:model="notes" rows="3" />
                    <flux:error name="notes" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-actions :back-url="roleRoute('cellar-operations.index')" submit-label="Actualizar operación" />
    </form>
</x-agro.form-card>
