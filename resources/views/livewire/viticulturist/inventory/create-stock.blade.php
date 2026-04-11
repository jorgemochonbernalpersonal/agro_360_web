<x-agro.form-card
    title="Registrar Stock"
    description="Añade productos fitosanitarios a tu inventario"
    :back-url="roleRoute('viticulturist.warehouse.index', ['tab' => 'fitosanitarios'])"
>
    <form wire:submit="save" class="space-y-8">
        <x-agro.form-section title="Datos del Producto" color="green">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:field>
                    <flux:label required>Producto Fitosanitario</flux:label>
                    <flux:select wire:model="product_id" id="product_id" required>
                        <option value="">Seleccionar producto...</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="product_id" />
                </flux:field>
                <flux:field>
                    <flux:label>Almacén</flux:label>
                    <flux:select wire:model="warehouse_id" id="warehouse_id">
                        <option value="">Sin almacén</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="warehouse_id" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-section title="Información del Lote" color="green">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <flux:field>
                    <flux:label>Número de Lote</flux:label>
                    <flux:input wire:model="batch_number" type="text" id="batch_number" placeholder="Ej: LOTE-2024-001" />
                    <flux:error name="batch_number" />
                </flux:field>
                <flux:field>
                    <flux:label>Fecha de Fabricación</flux:label>
                    <flux:input wire:model="manufacturing_date" type="date" id="manufacturing_date" />
                    <flux:error name="manufacturing_date" />
                </flux:field>
                <flux:field>
                    <flux:label>Fecha de Caducidad</flux:label>
                    <flux:input wire:model="expiry_date" type="date" id="expiry_date" />
                    <flux:error name="expiry_date" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-section title="Cantidad y Precio" color="green">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <flux:field>
                    <flux:label required>Cantidad</flux:label>
                    <flux:input wire:model="quantity" type="number" id="quantity" step="0.001" min="0.001" placeholder="0.000" required />
                    <flux:error name="quantity" />
                </flux:field>
                <flux:field>
                    <flux:label>Unidad</flux:label>
                    <flux:select wire:model="unit" id="unit">
                        @foreach($units as $unit)
                            <option value="{{ $unit->symbol }}">{{ $unit->name }} ({{ $unit->symbol }})</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="unit" />
                </flux:field>
                <flux:field>
                    <flux:label>Stock Mínimo (Alerta)</flux:label>
                    <flux:input wire:model="minimum_stock" type="number" id="minimum_stock" step="0.001" min="0" placeholder="0.000" />
                    <flux:description>Alerta cuando el stock sea menor a este valor</flux:description>
                    <flux:error name="minimum_stock" />
                </flux:field>

                <flux:field>
                    <flux:label>Precio por Unidad (€)</flux:label>
                    <flux:input wire:model="unit_price" type="number" id="unit_price" step="0.01" min="0" placeholder="0.00" />
                    <flux:error name="unit_price" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-section title="Información Adicional" color="green">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:field>
                    <flux:label>Proveedor</flux:label>
                    <flux:input wire:model="supplier" type="text" id="supplier" placeholder="Nombre del proveedor" />
                    <flux:error name="supplier" />
                </flux:field>
                <flux:field>
                    <flux:label>Número de Factura</flux:label>
                    <flux:input wire:model="invoice_number" type="text" id="invoice_number" placeholder="Nº factura o albarán" />
                    <flux:error name="invoice_number" />
                </flux:field>
            </div>
            <div class="mt-6">
                <flux:field>
                    <flux:label>Notas</flux:label>
                    <flux:textarea wire:model="notes" id="notes" rows="3" placeholder="Información adicional sobre este stock..." />
                    <flux:error name="notes" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-actions :back-url="roleRoute('viticulturist.warehouse.index', ['tab' => 'fitosanitarios'])" submit-label="Guardar Stock" />
    </form>
</x-agro.form-card>
