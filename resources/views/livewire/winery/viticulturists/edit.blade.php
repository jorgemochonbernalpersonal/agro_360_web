<div>
    <x-agro.form-card
        title="{{ __('Editar Viticultor') }}"
        :description="__('Datos del viticultor ghost gestionado por tu bodega')"
        :back-url="roleRoute('viticulturists.show', $viticulturistId)"
    >
        <form wire:submit="save" class="space-y-8">
            <x-agro.form-section title="{{ __('Datos del Viticultor') }}">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:field class="md:col-span-2">
                        <flux:label>{{ __('Nombre completo') }} <span class="text-red-500">*</span></flux:label>
                        <flux:input wire:model="name" id="name" required />
                        <flux:error name="name" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('DNI / NIE') }}</flux:label>
                        <flux:input wire:model="dni" id="dni" placeholder="12345678A" />
                        <flux:description>Clave de fusión con la cuenta pública del viticultor.</flux:description>
                        <flux:error name="dni" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Teléfono') }}</flux:label>
                        <flux:input wire:model="phone" id="phone" placeholder="+34 600 000 000" />
                        <flux:error name="phone" />
                    </flux:field>

                    <flux:field class="md:col-span-2">
                        <flux:label>{{ __('Email (opcional)') }}</flux:label>
                        <flux:input wire:model="email" id="email" type="email" placeholder="viticultor@ejemplo.com" />
                        <flux:error name="email" />
                    </flux:field>
                </div>
            </x-agro.form-section>

            <x-agro.form-actions
                :cancel-url="roleRoute('viticulturists.show', $viticulturistId)"
                submit-:label="__('Guardar cambios')"
            />
        </form>
    </x-agro.form-card>
</div>
