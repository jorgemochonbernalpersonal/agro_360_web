<x-agro.form-card
    title="{{ __('Editar Registro Sanitario') }}"
    :description="__('Modifica los datos del registro sanitario.')"
    icon="shield-check"
    icon-color="from-teal-500 to-teal-700"
    :back-url="roleRoute('sanitary-registrations.index')"
>
    <form wire:submit="save" class="space-y-8">
        <x-agro.form-section title="{{ __('Identificación') }}" color="teal">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <flux:field>
                    <flux:label required>{{ __('N.º de registro') }}</flux:label>
                    <flux:input wire:model="registration_number" type="text" required />
                    <flux:error name="registration_number" />
                </flux:field>

                <flux:field>
                    <flux:label required>{{ __('Tipo de registro') }}</flux:label>
                    <flux:select wire:model="registration_type" required>
                        @foreach($types as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="registration_type" />
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

                <flux:field class="md:col-span-2">
                    <flux:label>{{ __('Descripción de la actividad') }}</flux:label>
                    <flux:input wire:model="activity_description" type="text" />
                    <flux:error name="activity_description" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Organismo emisor') }}</flux:label>
                    <flux:input wire:model="issuing_authority" type="text" />
                    <flux:error name="issuing_authority" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-section title="{{ __('Fechas') }}" color="teal">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:field>
                    <flux:label>{{ __('Fecha de registro') }}</flux:label>
                    <flux:input wire:model="registration_date" type="date" />
                    <flux:error name="registration_date" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Fecha de renovación') }}</flux:label>
                    <flux:input wire:model="renewal_date" type="date" />
                    <flux:error name="renewal_date" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-section title="{{ __('Notas') }}" color="teal">
            <flux:field>
                <flux:label>{{ __('Observaciones') }}</flux:label>
                <flux:textarea wire:model="notes" rows="2" />
                <flux:error name="notes" />
            </flux:field>
        </x-agro.form-section>

        <x-agro.form-actions :back-url="roleRoute('sanitary-registrations.index')" submit-:label="__('Actualizar registro')" />
    </form>
</x-agro.form-card>
