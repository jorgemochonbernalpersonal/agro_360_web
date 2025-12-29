@php
    $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>';
@endphp

<x-form-card
    title="Registrar Stock"
    description="Añade productos fitosanitarios a tu inventario"
    :icon="$icon"
    icon-color="from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]"
    :back-url="route('viticulturist.inventory.index')"
>
    <form wire:submit="save" class="space-y-8">
        <x-form-section title="Datos del Producto" color="green">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <x-label for="product_id" required>Producto Fitosanitario</x-label>
                    <x-select wire:model="product_id" id="product_id" required>
                        <option value="">Seleccionar producto...</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </x-select>
                    @error('product_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <x-label for="warehouse_id">Almacén</x-label>
                    <x-select wire:model="warehouse_id" id="warehouse_id">
                        <option value="">Sin almacén</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                        @endforeach
                    </x-select>
                    @error('warehouse_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>
        </x-form-section>

        <x-form-section title="Información del Lote" color="green">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <x-label for="batch_number">Número de Lote</x-label>
                    <x-input 
                        wire:model="batch_number" 
                        type="text" 
                        id="batch_number"
                        placeholder="Ej: LOTE-2024-001"
                    />
                    @error('batch_number') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <x-label for="manufacturing_date">Fecha de Fabricación</x-label>
                    <x-input 
                        wire:model="manufacturing_date" 
                        type="date" 
                        id="manufacturing_date"
                    />
                    @error('manufacturing_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <x-label for="expiry_date">Fecha de Caducidad</x-label>
                    <x-input 
                        wire:model="expiry_date" 
                        type="date" 
                        id="expiry_date"
                    />
                    @error('expiry_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>
        </x-form-section>

        <x-form-section title="Cantidad y Precio" color="green">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <x-label for="quantity" required>Cantidad</x-label>
                    <x-input 
                        wire:model="quantity" 
                        type="number" 
                        id="quantity"
                        step="0.001"
                        min="0.001"
                        placeholder="0.000"
                        required
                    />
                    @error('quantity') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <x-label for="unit">Unidad</x-label>
                    <x-select wire:model="unit" id="unit">
                        <option value="L">Litros (L)</option>
                        <option value="kg">Kilogramos (kg)</option>
                        <option value="unidades">Unidades</option>
                    </x-select>
                    @error('unit') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <x-label for="unit_price">Precio por Unidad (€)</x-label>
                    <x-input 
                        wire:model="unit_price" 
                        type="number" 
                        id="unit_price"
                        step="0.01"
                        min="0"
                        placeholder="0.00"
                    />
                    @error('unit_price') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>
        </x-form-section>

        <x-form-section title="Información Adicional" color="green">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <x-label for="supplier">Proveedor</x-label>
                    <x-input 
                        wire:model="supplier" 
                        type="text" 
                        id="supplier"
                        placeholder="Nombre del proveedor"
                    />
                    @error('supplier') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <x-label for="invoice_number">Número de Factura</x-label>
                    <x-input 
                        wire:model="invoice_number" 
                        type="text" 
                        id="invoice_number"
                        placeholder="Nº factura o albarán"
                    />
                    @error('invoice_number') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="mt-6">
                <x-label for="notes">Notas</x-label>
                <x-textarea 
                    wire:model="notes" 
                    id="notes"
                    rows="3"
                    placeholder="Información adicional sobre este stock..."
                />
                @error('notes') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
        </x-form-section>

        <div class="flex justify-end gap-4">
            <a href="{{ route('viticulturist.inventory.index') }}" class="px-6 py-3 rounded-xl border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors">
                Cancelar
            </a>
            <button type="submit" class="px-6 py-3 rounded-xl bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:from-[var(--color-agro-green)] hover:to-[var(--color-agro-green-dark)] transition-all duration-300 shadow-lg hover:shadow-xl font-semibold">
                Guardar Stock
            </button>
        </div>
    </form>
</x-form-card>
