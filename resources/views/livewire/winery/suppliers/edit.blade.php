<x-agro.form-card
    title="Editar Proveedor"
    description="Modifica los datos del proveedor."
    icon="truck"
    icon-color="from-amber-500 to-amber-700"
    :back-url="roleRoute('suppliers.index')"
>
    <form wire:submit="save" class="space-y-8">
        <x-agro.form-section title="Datos de identificación" color="amber">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <flux:field class="md:col-span-2">
                    <flux:label required>Nombre</flux:label>
                    <flux:input wire:model="name" type="text" required />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>NIF / CIF</flux:label>
                    <flux:input wire:model="vat_number" type="text" />
                    <flux:error name="vat_number" />
                </flux:field>

                <flux:field>
                    <flux:label>Persona de contacto</flux:label>
                    <flux:input wire:model="contact_person" type="text" />
                    <flux:error name="contact_person" />
                </flux:field>

                <flux:field>
                    <flux:label>Email</flux:label>
                    <flux:input wire:model="email" type="email" />
                    <flux:error name="email" />
                </flux:field>

                <flux:field>
                    <flux:label>Teléfono</flux:label>
                    <flux:input wire:model="phone" type="text" />
                    <flux:error name="phone" />
                </flux:field>

                <flux:field class="lg:col-span-2">
                    <flux:label>Dirección</flux:label>
                    <flux:input wire:model="address" type="text" />
                    <flux:error name="address" />
                </flux:field>

                <flux:field>
                    <flux:label required>Categoría</flux:label>
                    <flux:select wire:model="category" required>
                        @foreach($categories as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="category" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-section title="Notas" color="amber">
            <flux:field>
                <flux:label>Observaciones</flux:label>
                <flux:textarea wire:model="notes" rows="3" />
                <flux:error name="notes" />
            </flux:field>
        </x-agro.form-section>

        <x-agro.form-actions :back-url="roleRoute('suppliers.index')" submit-label="Actualizar proveedor" />
    </form>
</x-agro.form-card>
