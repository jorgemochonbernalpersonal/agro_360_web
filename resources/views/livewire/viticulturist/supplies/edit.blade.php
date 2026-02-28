<x-agro.form-card
    title="Edit Supply"
    description="Update the details of this supply"
    :back-url="route('viticulturist.almacen.index', ['tab' => 'insumos'])"
>
    <form wire:submit="save" class="space-y-6">

        <x-agro.form-section title="Supply Information">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:field class="md:col-span-2">
                    <flux:label required>Name</flux:label>
                    <flux:input wire:model="name" type="text" required />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>Commercial name</flux:label>
                    <flux:input wire:model="commercial_name" type="text" placeholder="Manufacturer name" />
                    <flux:error name="commercial_name" />
                </flux:field>

                <flux:field>
                    <flux:label>MAPA registration no.</flux:label>
                    <flux:input wire:model="registration_number" type="text" placeholder="ES-00000" />
                    <flux:error name="registration_number" />
                </flux:field>

                <flux:field>
                    <flux:label required>Supply type</flux:label>
                    <flux:select wire:model="supply_type">
                        @foreach($supplyTypes as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="supply_type" />
                </flux:field>

                <flux:field>
                    <flux:label required>Unit of measurement</flux:label>
                    <flux:select wire:model="unit_of_measurement">
                        @foreach($units as $unit)
                            <option value="{{ $unit->symbol }}">{{ $unit->name }} ({{ $unit->symbol }})</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="unit_of_measurement" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-section title="Stock & Storage">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:field>
                    <flux:label>Current stock</flux:label>
                    <flux:input wire:model="current_stock" type="number" step="0.001" min="0" placeholder="0.000" />
                    <flux:description>Use "Register purchase" to increase stock</flux:description>
                    <flux:error name="current_stock" />
                </flux:field>

                <flux:field>
                    <flux:label>Minimum stock alert</flux:label>
                    <flux:input wire:model="min_stock_alert" type="number" step="0.001" min="0" placeholder="0.000" />
                    <flux:description>Alert when stock drops below this value</flux:description>
                    <flux:error name="min_stock_alert" />
                </flux:field>

                <flux:field>
                    <flux:label>Warehouse</flux:label>
                    <flux:select wire:model="warehouse_id">
                        <option value="">No warehouse assigned</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="warehouse_id" />
                </flux:field>

                <flux:field>
                    <flux:label>Expiry date</flux:label>
                    <flux:input wire:model="expiry_date" type="date" />
                    <flux:error name="expiry_date" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-section title="Notes">
            <flux:field>
                <flux:textarea wire:model="notes" rows="3" />
                <flux:error name="notes" />
            </flux:field>
        </x-agro.form-section>

        <x-agro.form-actions
            :cancel-url="route('viticulturist.almacen.index', ['tab' => 'insumos'])"
            submit-label="Save Changes"
        />
    </form>
</x-agro.form-card>
