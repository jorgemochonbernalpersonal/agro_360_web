<x-agro.form-card
    title="{{ __('Nueva Certificación Ecológica') }}"
    :description="__('Registra una certificación ecológica, biodinámica o de sostenibilidad.')"
    icon="sparkles"
    icon-color="from-green-500 to-green-700"
    :back-url="roleRoute('eco-certifications.index')"
>
    <form wire:submit="save" class="space-y-8">
        <x-agro.form-section title="{{ __('Identificación') }}" color="green">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <flux:field class="md:col-span-2">
                    <flux:label required>{{ __('Nombre') }}</flux:label>
                    <flux:input wire:model="name" type="text" :placeholder="__('Ej. Certificación Ecológica UE')" required />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label required>{{ __('Tipo') }}</flux:label>
                    <flux:select wire:model="certification_type" required>
                        @foreach($types as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="certification_type" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Organismo certificador') }}</flux:label>
                    <flux:input wire:model="certifying_body" type="text" :placeholder="__('Ej. CCPAE, Caae...')" />
                    <flux:error name="certifying_body" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('N.º de certificado') }}</flux:label>
                    <flux:input wire:model="certificate_number" type="text" :placeholder="__('Opcional')" />
                    <flux:error name="certificate_number" />
                </flux:field>

                <flux:field>
                    <flux:label required>{{ __('Estado') }}</flux:label>
                    <flux:select wire:model="status" required>
                        @foreach($statuses as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="status" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-section title="{{ __('Vigencia') }}" color="green">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:field>
                    <flux:label>{{ __('Válido desde') }}</flux:label>
                    <flux:input wire:model="valid_from" type="date" />
                    <flux:error name="valid_from" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Válido hasta') }}</flux:label>
                    <flux:input wire:model="valid_until" type="date" />
                    <flux:error name="valid_until" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-section title="{{ __('Notas') }}" color="green">
            <flux:field>
                <flux:label>{{ __('Observaciones') }}</flux:label>
                <flux:textarea wire:model="notes" rows="2" :placeholder="__('Condiciones, alcance, observaciones...')" />
                <flux:error name="notes" />
            </flux:field>
        </x-agro.form-section>

        <x-agro.form-actions :cancel-url="roleRoute('eco-certifications.index')" :submit-label="__('Guardar certificación')" />
    </form>
</x-agro.form-card>
