<x-agro.form-card
    :title="__('Editar Asesor Técnico')"
    :description="__('Modifica los datos de') . ' ' . $advisoryMembership->advisor_name"
    :back-url="roleRoute('viticulturist.advisory-memberships.index')"
>
    <form wire:submit="save" class="space-y-8">
        <x-agro.form-section :title="__('Datos del Asesor')">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <flux:field>
                    <flux:label required>{{ __('Nombre del asesor') }}</flux:label>
                    <flux:input wire:model="advisor_name" type="text" />
                    <flux:error name="advisor_name" />
                </flux:field>

                <flux:field>
                    <flux:label required>{{ __('Número de licencia') }}</flux:label>
                    <flux:input wire:model="license_number" type="text" />
                    <flux:error name="license_number" />
                </flux:field>

                <flux:field>
                    <flux:label required>{{ __('Especialidad') }}</flux:label>
                    <flux:select wire:model="specialty">
                        @foreach($specialties as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="specialty" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Empresa') }}</flux:label>
                    <flux:input wire:model="company_name" type="text" />
                    <flux:error name="company_name" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Teléfono') }}</flux:label>
                    <flux:input wire:model="phone" type="tel" />
                    <flux:error name="phone" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Email') }}</flux:label>
                    <flux:input wire:model="email" type="email" />
                    <flux:error name="email" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Campaña') }}</flux:label>
                    <flux:select wire:model="campaign_id">
                        <option value="">{{ __('Sin campaña específica') }}</option>
                        @foreach($campaigns as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="campaign_id" />
                </flux:field>

            </div>
        </x-agro.form-section>

        <x-agro.form-actions
            :cancel-url="roleRoute('viticulturist.advisory-memberships.index')"
            :submit-label="__('Actualizar Asesor')"
        />
    </form>
</x-agro.form-card>
