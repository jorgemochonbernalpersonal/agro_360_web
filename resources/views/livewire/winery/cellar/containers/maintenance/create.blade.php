<x-agro.form-card
    title="Nuevo Mantenimiento"
    description="Registra o programa un mantenimiento para {{ $container->name }}."
    icon="wrench-screwdriver"
    icon-color="from-zinc-500 to-zinc-700"
    :back-url="route('winery.containers.maintenance.index', $container)"
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
                    <flux:input wire:model="maintenance_name" type="text" required placeholder="ej. Limpieza de vendimia 2026" />
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
                    <flux:description>Dejar vacío si aún no se ha realizado</flux:description>
                    <flux:error name="performed_date" />
                </flux:field>

                <flux:field>
                    <flux:label>Próximo mantenimiento</flux:label>
                    <flux:input wire:model="next_maintenance_date" type="date" />
                    <flux:description>Se actualizará en el contenedor al completar</flux:description>
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
                    <flux:input wire:model="cost" type="number" step="0.01" min="0" placeholder="0.00" />
                    <flux:error name="cost" />
                </flux:field>

                <flux:field>
                    <flux:label>Realizado por</flux:label>
                    <flux:input wire:model="performed_by" type="text" placeholder="Nombre del técnico o empresa" />
                    <flux:error name="performed_by" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-section title="Notas" color="green">
            <flux:field>
                <flux:label>Observaciones</flux:label>
                <flux:textarea wire:model="notes" rows="3" placeholder="Detalles del mantenimiento..." />
                <flux:error name="notes" />
            </flux:field>
        </x-agro.form-section>

        <x-agro.form-actions :back-url="route('winery.containers.maintenance.index', $container)" submit-label="Guardar mantenimiento" />
    </form>
</x-agro.form-card>
