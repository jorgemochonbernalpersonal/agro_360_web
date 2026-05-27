<x-agro.form-card
    title="Editar Aditivo — {{ $container->name }}"
    :description="__('Modifica el aditivo enológico registrado.')"
    icon="beaker"
    icon-color="from-purple-500 to-purple-700"
    :back-url="roleRoute('containers.additives.index', $container)"
>
    <form wire:submit="save" class="space-y-8">
        <x-agro.form-section title="{{ __('Aditivo') }}" color="purple">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <flux:field>
                    <flux:label>{{ __('Insumo del catálogo') }}</flux:label>
                    <flux:select wire:model="winery_supply_id">
                        <option value="">{{ __('Seleccionar insumo...') }}</option>
                        @foreach($winerySupplies as $supply)
                            <option value="{{ $supply->id }}">{{ $supply->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:description>{{ __('O escribe el nombre libre abajo') }}</flux:description>
                    <flux:error name="winery_supply_id" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Nombre del aditivo (libre)') }}</flux:label>
                    <flux:input wire:model="additive_name" type="text" :placeholder="__('Ej. SO₂, Bentonita...')" />
                    <flux:error name="additive_name" />
                </flux:field>

                <flux:field>
                    <flux:label required>{{ __('Fecha de aplicación') }}</flux:label>
                    <flux:input wire:model="additive_date" type="date" required />
                    <flux:error name="additive_date" />
                </flux:field>

                <flux:field>
                    <flux:label required>{{ __('Cantidad') }}</flux:label>
                    <flux:input wire:model="quantity" type="number" step="0.001" min="0.001" required />
                    <flux:error name="quantity" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Unidad') }}</flux:label>
                    <flux:select wire:model="unit_of_measurement_id">
                        <option value="">{{ __('Sin unidad') }}</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->name }} ({{ $unit->symbol }})</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="unit_of_measurement_id" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-section title="{{ __('Notas') }}" color="green">
            <flux:field>
                <flux:label>{{ __('Observaciones') }}</flux:label>
                <flux:textarea wire:model="notes" rows="2" :placeholder="__('Motivo, dosis, condiciones...')" />
                <flux:error name="notes" />
            </flux:field>
        </x-agro.form-section>

        <x-agro.form-actions :back-url="roleRoute('containers.additives.index', $container)" submit-:label="__('Guardar cambios')" />
    </form>
</x-agro.form-card>
