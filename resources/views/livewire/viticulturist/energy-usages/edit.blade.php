<x-agro.form-card
    :title="__('Editar Consumo Energético')"
    :description="__('Modifica el registro del') . ' ' . $energyUsage->date->format('d/m/Y')"
    :back-url="roleRoute('viticulturist.energy-usages.index')"
>
    <form wire:submit="save" class="space-y-8">

        <x-agro.form-section :title="__('Datos del Consumo')">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <flux:field>
                    <flux:label required>{{ __('Campaña') }}</flux:label>
                    <flux:select wire:model="campaign_id">
                        @foreach($campaigns as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="campaign_id" />
                </flux:field>

                <flux:field>
                    <flux:label required>{{ __('Fecha') }}</flux:label>
                    <flux:input wire:model="date" type="date" />
                    <flux:error name="date" />
                </flux:field>

                <flux:field>
                    <flux:label required>{{ __('Tipo de energía') }}</flux:label>
                    <flux:select wire:model.live="energy_type">
                        @foreach($energyTypes as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="energy_type" />
                </flux:field>

                <flux:field>
                    <flux:label required>{{ __('Unidad') }}</flux:label>
                    <flux:select wire:model="unit">
                        @foreach($units as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="unit" />
                </flux:field>

                <flux:field>
                    <flux:label required>{{ __('Cantidad') }}</flux:label>
                    <flux:input wire:model.live="quantity" type="number" step="0.001" min="0" placeholder="0.000" />
                    <flux:error name="quantity" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Precio/unidad (€)') }}</flux:label>
                    <flux:input wire:model.live="cost_per_unit" type="number" step="0.0001" min="0" placeholder="0.0000" />
                    <flux:error name="cost_per_unit" />
                </flux:field>

            </div>
        </x-agro.form-section>

        <x-agro.form-section :title="__('Cálculos Automáticos')">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <flux:field>
                    <flux:label>{{ __('Coste total (€)') }}</flux:label>
                    <flux:input wire:model="total_cost" type="number" step="0.01" placeholder="0.00" readonly />
                    <flux:description>{{ __('Calculado automáticamente') }}</flux:description>
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('CO₂ equivalente (kg)') }}</flux:label>
                    <flux:input wire:model="co2_kg_equivalent" type="number" step="0.001" placeholder="0.000" readonly />
                    <flux:description>{{ __('Calculado según factor de emisión') }}</flux:description>
                </flux:field>

            </div>
        </x-agro.form-section>

        <x-agro.form-section :title="__('Información Adicional')">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <flux:field>
                    <flux:label>{{ __('Maquinaria asociada') }}</flux:label>
                    <flux:select wire:model="machinery_id">
                        <option value="">{{ __('Sin especificar') }}</option>
                        @foreach($machinery as $m)
                            <option value="{{ $m->id }}">{{ $m->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="machinery_id" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Descripción de la operación') }}</flux:label>
                    <flux:input wire:model="usage_description" type="text" :placeholder="__('Ej: Tratamiento fitosanitario parcela norte')" />
                    <flux:error name="usage_description" />
                </flux:field>

                <flux:field class="md:col-span-2">
                    <flux:label>{{ __('Notas') }}</flux:label>
                    <flux:textarea wire:model="notes" rows="3" />
                    <flux:error name="notes" />
                </flux:field>

            </div>
        </x-agro.form-section>

        <x-agro.form-actions
            :cancel-url="roleRoute('viticulturist.energy-usages.index')"
            :submit-label="__('Actualizar Consumo')"
        />
    </form>
</x-agro.form-card>
