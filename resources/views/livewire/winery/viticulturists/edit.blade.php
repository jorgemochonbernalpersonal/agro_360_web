<div>
    <x-agro.form-card
        title="Editar Viticultor"
        description="Datos del viticultor ghost gestionado por tu bodega"
        :back-url="route('winery.viticulturists.show', $viticulturistId)"
    >
        <form wire:submit="save" class="space-y-8">
            <x-agro.form-section title="Datos del Viticultor">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:field class="md:col-span-2">
                        <flux:label>Nombre completo <span class="text-red-500">*</span></flux:label>
                        <flux:input wire:model="name" id="name" required />
                        <flux:error name="name" />
                    </flux:field>

                    <flux:field>
                        <flux:label>DNI / NIE</flux:label>
                        <flux:input wire:model="dni" id="dni" placeholder="12345678A" />
                        <flux:description>Clave de fusión con la cuenta pública del viticultor.</flux:description>
                        <flux:error name="dni" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Teléfono</flux:label>
                        <flux:input wire:model="phone" id="phone" placeholder="+34 600 000 000" />
                        <flux:error name="phone" />
                    </flux:field>

                    <flux:field class="md:col-span-2">
                        <flux:label>Email (opcional)</flux:label>
                        <flux:input wire:model="email" id="email" type="email" placeholder="viticultor@ejemplo.com" />
                        <flux:error name="email" />
                    </flux:field>
                </div>
            </x-agro.form-section>

            <x-agro.form-actions
                :cancel-url="route('winery.viticulturists.show', $viticulturistId)"
                submit-label="Guardar cambios"
            />
        </form>
    </x-agro.form-card>
</div>
