<div class="space-y-6 animate-fade-in">
    <x-agro.page-header title="{{ __('Editar Stock') }}" :description="__('Modifica los datos del registro de stock')">
        <x-slot:actions>
            <flux:button href="{{ roleRoute('viticulturist.warehouse.index', ['tab' => 'fitosanitarios']) }}" variant="ghost" icon="arrow-left">
                {{ __('Cancelar') }}
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    <x-agro.form-card title="{{ __('Datos del Stock') }}">
        <form wire:submit="save" class="space-y-6">
            {{-- Info del producto --}}
            <flux:callout variant="info">
                <flux:callout.heading>{{ $product->name }}</flux:callout.heading>
                @if($product->active_ingredient)
                    <flux:callout.text>{{ $product->active_ingredient }}</flux:callout.text>
                @endif
            </flux:callout>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:field>
                    <flux:label required>{{ __('Unidad') }}</flux:label>
                    <flux:select wire:model="unit" id="unit">
                        @foreach($units as $unit)
                            <option value="{{ $unit->symbol }}">{{ __($unit->name) }} ({{ $unit->symbol }})</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="unit" />
                </flux:field>

                <flux:field>
                    <flux:label required>{{ __('Cantidad Actual') }}</flux:label>
                    <flux:input wire:model="quantity" type="number" step="0.001" id="quantity" required />
                    <flux:error name="quantity" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Stock Mínimo (Alerta)') }}</flux:label>
                    <flux:input wire:model="minimum_stock" type="number" step="0.001" id="minimum_stock" />
                    <flux:description>{{ __('Se mostrará alerta cuando el stock sea menor a este valor') }}</flux:description>
                    <flux:error name="minimum_stock" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Precio Unitario (€)') }}</flux:label>
                    <flux:input wire:model="unit_price" type="number" step="0.01" id="unit_price" />
                    <flux:error name="unit_price" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Almacén') }}</flux:label>
                    <flux:select wire:model="warehouse_id" id="warehouse_id">
                        <option value="">{{ __('Sin almacén asignado') }}</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="warehouse_id" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Número de Lote') }}</flux:label>
                    <flux:input wire:model="batch_number" type="text" id="batch_number" />
                    <flux:error name="batch_number" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Proveedor') }}</flux:label>
                    <flux:input wire:model="supplier" type="text" id="supplier" />
                    <flux:error name="supplier" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Número de Factura') }}</flux:label>
                    <flux:input wire:model="invoice_number" type="text" id="invoice_number" :placeholder="__('Nº factura o albarán')" />
                    <flux:error name="invoice_number" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Fecha de Fabricación') }}</flux:label>
                    <flux:input wire:model="manufacturing_date" type="date" id="manufacturing_date" />
                    <flux:error name="manufacturing_date" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Fecha de Caducidad') }}</flux:label>
                    <flux:input wire:model="expiry_date" type="date" id="expiry_date" />
                    <flux:error name="expiry_date" />
                </flux:field>
            </div>

            <flux:field>
                <flux:label>{{ __('Notas') }}</flux:label>
                <flux:textarea wire:model="notes" id="notes" rows="3" />
                <flux:error name="notes" />
            </flux:field>

            <x-agro.form-actions :back-url="roleRoute('viticulturist.warehouse.index', ['tab' => 'fitosanitarios'])" submit-:label="__('Guardar Cambios')" />
        </form>
    </x-agro.form-card>
</div>
