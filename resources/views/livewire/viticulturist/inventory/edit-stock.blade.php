<div class="space-y-6 animate-fade-in">
    <x-agro.page-header title="Editar Stock" description="Modifica los datos del registro de stock">
        <x-slot:actions>
            <flux:button href="{{ route('viticulturist.inventory.index') }}" variant="ghost" icon="arrow-left">
                Cancelar
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    <x-agro.form-card title="Datos del Stock">
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
                    <flux:label required>Cantidad Actual</flux:label>
                    <div class="flex gap-2">
                        <flux:input wire:model="quantity" type="number" step="0.001" id="quantity" class="flex-1" required />
                        <span class="px-3 py-2 bg-zinc-100 rounded-lg text-zinc-700 font-medium text-sm self-center">{{ $unit }}</span>
                    </div>
                    <flux:error name="quantity" />
                </flux:field>

                <flux:field>
                    <flux:label>Stock Mínimo (Alerta)</flux:label>
                    <div class="flex gap-2">
                        <flux:input wire:model="minimum_stock" type="number" step="0.001" id="minimum_stock" class="flex-1" />
                        <span class="px-3 py-2 bg-zinc-100 rounded-lg text-zinc-700 font-medium text-sm self-center">{{ $unit }}</span>
                    </div>
                    <flux:description>Se mostrará alerta cuando el stock sea menor a este valor</flux:description>
                    <flux:error name="minimum_stock" />
                </flux:field>

                <flux:field>
                    <flux:label>Precio Unitario</flux:label>
                    <div class="flex gap-2">
                        <flux:input wire:model="unit_price" type="number" step="0.01" id="unit_price" class="flex-1" />
                        <span class="px-3 py-2 bg-zinc-100 rounded-lg text-zinc-700 font-medium text-sm self-center">€/{{ $unit }}</span>
                    </div>
                    <flux:error name="unit_price" />
                </flux:field>

                <flux:field>
                    <flux:label>Almacén</flux:label>
                    <flux:select wire:model="warehouse_id" id="warehouse_id">
                        <option value="">Sin almacén asignado</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="warehouse_id" />
                </flux:field>

                <flux:field>
                    <flux:label>Número de Lote</flux:label>
                    <flux:input wire:model="batch_number" type="text" id="batch_number" />
                    <flux:error name="batch_number" />
                </flux:field>

                <flux:field>
                    <flux:label>Proveedor</flux:label>
                    <flux:input wire:model="supplier" type="text" id="supplier" />
                    <flux:error name="supplier" />
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

            <flux:field>
                <flux:label>Notas</flux:label>
                <flux:textarea wire:model="notes" id="notes" rows="3" />
                <flux:error name="notes" />
            </flux:field>

            <x-agro.form-actions :back-url="route('viticulturist.inventory.index')" submit-label="Guardar Cambios" />
        </form>
    </x-agro.form-card>
</div>
