<div>
    <x-agro.form-card
        title="Nuevo Enólogo"
        description="Registra un técnico enológico en tu bodega"
        :back-url="roleRoute('oenologists.index')"
    >
        <form wire:submit="save" class="space-y-8">

            <x-agro.form-section title="Datos Personales">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:field>
                        <flux:label>Nombre <span class="text-red-500">*</span></flux:label>
                        <flux:input wire:model="name" id="name" required />
                        <flux:error name="name" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Apellidos</flux:label>
                        <flux:input wire:model="surname" id="surname" />
                        <flux:error name="surname" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Nº Colegiado</flux:label>
                        <flux:input wire:model="license_number" id="license_number" placeholder="Ej. OE-12345" />
                        <flux:error name="license_number" />
                    </flux:field>
                </div>
            </x-agro.form-section>

            <x-agro.form-section title="Contacto">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:field>
                        <flux:label>Email</flux:label>
                        <flux:input wire:model="email" id="email" type="email" />
                        <flux:error name="email" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Teléfono</flux:label>
                        <flux:input wire:model="phone" id="phone" type="tel" />
                        <flux:error name="phone" />
                    </flux:field>
                </div>
            </x-agro.form-section>

            <x-agro.form-section title="Notas">
                <flux:field>
                    <flux:label>Observaciones</flux:label>
                    <flux:textarea wire:model="notes" id="notes" rows="3" />
                    <flux:error name="notes" />
                </flux:field>
            </x-agro.form-section>

            <x-agro.form-actions :cancel-url="roleRoute('oenologists.index')" submit-label="Crear Enólogo" />

        </form>
    </x-agro.form-card>
</div>
