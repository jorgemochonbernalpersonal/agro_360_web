<x-agro.form-card
    title="{{ __('Nuevo Proveedor') }}"
    :description="__('Añade un proveedor al catálogo de tu bodega.')"
    icon="truck"
    icon-color="from-amber-500 to-amber-700"
    :back-url="roleRoute('suppliers.index')"
>
    <form wire:submit="save" class="space-y-8">
        <x-agro.form-section title="{{ __('Datos de identificación') }}" color="amber">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <flux:field class="md:col-span-2">
                    <flux:label required>{{ __('Nombre') }}</flux:label>
                    <flux:input wire:model="name" type="text" :placeholder="__('Ej. Viñedos García S.L.')" required />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('NIF / CIF') }}</flux:label>
                    <flux:input wire:model="vat_number" type="text" :placeholder="__('Ej. B12345678')" />
                    <flux:error name="vat_number" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Persona de contacto') }}</flux:label>
                    <flux:input wire:model="contact_person" type="text" :placeholder="__('Nombre del responsable')" />
                    <flux:error name="contact_person" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Email') }}</flux:label>
                    <flux:input wire:model="email" type="email" placeholder="proveedor@ejemplo.com" />
                    <flux:error name="email" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Teléfono') }}</flux:label>
                    <flux:input wire:model="phone" type="text" :placeholder="__('Ej. 666 123 456')" />
                    <flux:error name="phone" />
                </flux:field>

                <flux:field class="lg:col-span-2">
                    <flux:label>{{ __('Dirección') }}</flux:label>
                    <flux:input wire:model="address" type="text" :placeholder="__('Calle, número, localidad...')" />
                    <flux:error name="address" />
                </flux:field>

                <flux:field>
                    <flux:label required>{{ __('Categoría') }}</flux:label>
                    <flux:select wire:model="category" required>
                        @foreach($categories as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="category" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-section title="{{ __('Notas') }}" color="amber">
            <flux:field>
                <flux:label>{{ __('Observaciones') }}</flux:label>
                <flux:textarea wire:model="notes" rows="3" :placeholder="__('Condiciones de pago, plazos de entrega, notas adicionales...')" />
                <flux:error name="notes" />
            </flux:field>
        </x-agro.form-section>

        <x-agro.form-actions :back-url="roleRoute('suppliers.index')" submit-:label="__('Guardar proveedor')" />
    </form>
</x-agro.form-card>
