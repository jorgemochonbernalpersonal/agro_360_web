<div>
    <x-agro.form-card
        title="{{ __('Editar Enólogo') }}"
        :description="__('Modifica los datos del técnico enológico')"
        :back-url="roleRoute('oenologists.index')"
    >
        <form wire:submit="update" class="space-y-8">

            <x-agro.form-section title="{{ __('Datos Personales') }}">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:field>
                        <flux:label>{{ __('Nombre') }} <span class="text-red-500">*</span></flux:label>
                        <flux:input wire:model="name" id="name" required />
                        <flux:error name="name" />
                    </flux:field>
                    <flux:field>
                        <flux:label>{{ __('Apellidos') }}</flux:label>
                        <flux:input wire:model="surname" id="surname" />
                        <flux:error name="surname" />
                    </flux:field>
                    <flux:field>
                        <flux:label>{{ __('Nº Colegiado') }}</flux:label>
                        <flux:input wire:model="license_number" id="license_number" :placeholder="__('Ej. OE-12345')" />
                        <flux:error name="license_number" />
                    </flux:field>
                </div>
            </x-agro.form-section>

            <x-agro.form-section title="{{ __('Contacto') }}">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:field>
                        <flux:label>{{ __('Email') }}</flux:label>
                        <flux:input wire:model="email" id="email" type="email" />
                        <flux:error name="email" />
                    </flux:field>
                    <flux:field>
                        <flux:label>{{ __('Teléfono') }}</flux:label>
                        <flux:input wire:model="phone" id="phone" type="tel" />
                        <flux:error name="phone" />
                    </flux:field>
                </div>
            </x-agro.form-section>

            <x-agro.form-section title="{{ __('Notas') }}">
                <flux:field>
                    <flux:label>{{ __('Observaciones') }}</flux:label>
                    <flux:textarea wire:model="notes" id="notes" rows="3" />
                    <flux:error name="notes" />
                </flux:field>
            </x-agro.form-section>

            <x-agro.form-actions :cancel-url="roleRoute('oenologists.index')" submit-:label="__('Guardar Cambios')" />

        </form>
    </x-agro.form-card>
</div>
