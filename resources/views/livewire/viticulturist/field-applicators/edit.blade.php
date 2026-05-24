<x-agro.form-card
    :title="__('Editar Aplicador ROPO')"
    :description="__('Modifica los datos de :name', ['name' => $fieldApplicator->name])"
    :back-url="roleRoute('viticulturist.field-applicators.index')"
>
    <form wire:submit="save" class="space-y-8">
        <x-agro.form-section :title="__('Datos del Aplicador')">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <flux:field>
                    <flux:label required>{{ __('Nombre') }}</flux:label>
                    <flux:input wire:model="name" type="text" />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label required>{{ __('Número ROPO') }}</flux:label>
                    <flux:input wire:model="ropo_number" type="text" />
                    <flux:error name="ropo_number" />
                </flux:field>

                <flux:field>
                    <flux:label required>{{ __('Categoría ROPO') }}</flux:label>
                    <flux:select wire:model="ropo_category">
                        @foreach($categories as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="ropo_category" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Fecha de vencimiento ROPO') }}</flux:label>
                    <flux:input wire:model="ropo_expiry_date" type="date" />
                    <flux:error name="ropo_expiry_date" />
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

            </div>

            <div class="mt-6">
                <flux:checkbox wire:model.live="is_advisor" :label="__('Es también asesor fitosanitario')" />
            </div>

            @if($is_advisor)
                <div class="mt-4">
                    <flux:field>
                        <flux:label required>{{ __('Número de licencia de asesor') }}</flux:label>
                        <flux:input wire:model="advisor_license" type="text" />
                        <flux:error name="advisor_license" />
                    </flux:field>
                </div>
            @endif
        </x-agro.form-section>

        <x-agro.form-actions
            :cancel-url="roleRoute('viticulturist.field-applicators.index')"
            :submit-label="__('Actualizar Aplicador')"
        />
    </form>
</x-agro.form-card>
