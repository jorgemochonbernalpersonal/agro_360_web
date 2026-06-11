<x-agro.form-card
    title="{{ __('Editar Insumo') }}"
    :description="__('Modifica los datos del insumo de bodega.')"
    icon="beaker"
    icon-color="from-blue-500 to-blue-700"
    :back-url="roleRoute('winery-supplies.index')"
>
    <form wire:submit="save" class="space-y-8">
        <x-agro.form-section title="{{ __('Identificación') }}" color="blue">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <flux:field class="md:col-span-2">
                    <flux:label required>{{ __('Nombre') }}</flux:label>
                    <flux:input wire:model="name" type="text" required />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Nombre comercial') }}</flux:label>
                    <flux:input wire:model="commercial_name" type="text" />
                    <flux:error name="commercial_name" />
                </flux:field>

                <flux:field>
                    <flux:label required>{{ __('Tipo') }}</flux:label>
                    <flux:select wire:model="supply_type" required>
                        @foreach($types as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="supply_type" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Unidad de medida') }}</flux:label>
                    <flux:select wire:model="unit_of_measurement_id">
                        <option value="">{{ __('Sin unidad') }}</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}">{{ __($unit->name) }} ({{ $unit->symbol }})</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="unit_of_measurement_id" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-section title="{{ __('Stock') }}" color="green">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <flux:field>
                    <flux:label>{{ __('Stock actual') }}</flux:label>
                    <flux:input wire:model="current_stock" type="number" step="0.001" min="0" />
                    <flux:error name="current_stock" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Alerta stock mínimo') }}</flux:label>
                    <flux:input wire:model="min_stock_alert" type="number" step="0.001" min="0" />
                    <flux:error name="min_stock_alert" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Fecha de caducidad') }}</flux:label>
                    <flux:input wire:model="expiry_date" type="date" />
                    <flux:error name="expiry_date" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-section title="{{ __('Notas') }}" color="green">
            <flux:field>
                <flux:label>{{ __('Observaciones') }}</flux:label>
                <flux:textarea wire:model="notes" rows="2" />
                <flux:error name="notes" />
            </flux:field>
        </x-agro.form-section>

        <x-agro.form-actions :back-url="roleRoute('winery-supplies.index')" submit-:label="__('Actualizar insumo')" />
    </form>
</x-agro.form-card>
