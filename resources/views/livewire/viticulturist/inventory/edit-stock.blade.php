<div class="space-y-6 animate-fade-in">
    <!-- Header -->
    @php
        $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>';
    @endphp
    <x-page-header
        :icon="$icon"
        title="Editar Stock"
        description="Modifica los datos del registro de stock"
        icon-color="from-blue-500 to-blue-600"
    >
        <x-slot:actionButton>
            <a href="{{ route('viticulturist.inventory.index') }}">
                <button class="px-4 py-2 rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300 transition-colors">
                    Cancelar
                </button>
            </a>
        </x-slot:actionButton>
    </x-page-header>

    <!-- Formulario -->
    <div class="glass-card rounded-xl p-6 border border-gray-200">
        <form wire:submit="save">
            <!-- Información del Producto -->
            <div class="mb-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                <h3 class="font-semibold text-blue-900 mb-2">{{ $product->name }}</h3>
                @if($product->active_ingredient)
                    <p class="text-sm text-blue-700">{{ $product->active_ingredient }}</p>
                @endif
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Cantidad -->
                <div>
                    <x-label for="quantity" class="required">Cantidad Actual</x-label>
                    <div class="flex gap-2">
                        <x-input wire:model="quantity" type="number" step="0.001" id="quantity" class="flex-1" required />
                        <span class="px-3 py-2 bg-gray-100 rounded-lg text-gray-700 font-medium">{{ $unit }}</span>
                    </div>
                    <x-input-error for="quantity" />
                </div>

                <!-- Stock Mínimo -->
                <div>
                    <x-label for="minimum_stock">Stock Mínimo (Alerta)</x-label>
                    <div class="flex gap-2">
                        <x-input wire:model="minimum_stock" type="number" step="0.001" id="minimum_stock" class="flex-1" />
                        <span class="px-3 py-2 bg-gray-100 rounded-lg text-gray-700 font-medium">{{ $unit }}</span>
                    </div>
                    <p class="mt-1 text-sm text-gray-500">Se mostrará alerta cuando el stock sea menor a este valor</p>
                    <x-input-error for="minimum_stock" />
                </div>

                <!-- Precio Unitario -->
                <div>
                    <x-label for="unit_price">Precio Unitario</x-label>
                    <div class="flex gap-2">
                        <x-input wire:model="unit_price" type="number" step="0.01" id="unit_price" class="flex-1" />
                        <span class="px-3 py-2 bg-gray-100 rounded-lg text-gray-700 font-medium">€/{{ $unit }}</span>
                    </div>
                    <x-input-error for="unit_price" />
                </div>

                <!-- Almacén -->
                <div>
                    <x-label for="warehouse_id">Almacén</x-label>
                    <x-select wire:model="warehouse_id" id="warehouse_id">
                        <option value="">Sin almacén asignado</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                        @endforeach
                    </x-select>
                    <x-input-error for="warehouse_id" />
                </div>

                <!-- Número de Lote -->
                <div>
                    <x-label for="batch_number">Número de Lote</x-label>
                    <x-input wire:model="batch_number" type="text" id="batch_number" />
                </div>

                <!-- Proveedor -->
                <div>
                    <x-label for="supplier">Proveedor</x-label>
                    <x-input wire:model="supplier" type="text" id="supplier" />
                </div>

                <!-- Fecha de Fabricación -->
                <div>
                    <x-label for="manufacturing_date">Fecha de Fabricación</x-label>
                    <x-input wire:model="manufacturing_date" type="date" id="manufacturing_date" />
                </div>

                <!-- Fecha de Caducidad -->
                <div>
                    <x-label for="expiry_date">Fecha de Caducidad</x-label>
                    <x-input wire:model="expiry_date" type="date" id="expiry_date" />
                    <x-input-error for="expiry_date" />
                </div>
            </div>

            <!-- Notas -->
            <div class="mt-6">
                <x-label for="notes">Notas</x-label>
                <textarea 
                    wire:model="notes" 
                    id="notes" 
                    rows="3"
                    class="w-full rounded-lg border-gray-300 focus:border-[var(--color-agro-green)] focus:ring focus:ring-[var(--color-agro-green)] focus:ring-opacity-50"
                ></textarea>
            </div>

            <!-- Botones -->
            <div class="mt-6 flex items-center justify-end gap-4">
                <a href="{{ route('viticulturist.inventory.index') }}" 
                   class="px-6 py-2 rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300 transition-colors">
                    Cancelar
                </a>
                <button 
                    type="submit"
                    class="px-6 py-2 rounded-lg bg-gradient-to-r from-blue-500 to-blue-600 text-white hover:from-blue-600 hover:to-blue-700 transition-all shadow-lg hover:shadow-xl font-semibold">
                    Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>
