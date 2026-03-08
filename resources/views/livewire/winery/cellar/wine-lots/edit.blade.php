<div>
    <x-agro.form-card title="Editar Producto" description="Modifica los datos del producto"
        :back-url="route('winery.wine-lots.index')">

        <form wire:submit.prevent="update" class="space-y-8">

            <x-agro.form-section title="Información del Producto">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:field>
                        <flux:label for="name">Nombre del producto *</flux:label>
                        <flux:input wire:model="name" type="text" id="name" required />
                        <flux:error name="name" />
                    </flux:field>

                    <flux:field>
                        <flux:label for="vintage">Añada (año)</flux:label>
                        <flux:input wire:model="vintage" type="number" id="vintage" min="1900" max="{{ now()->year + 1 }}" />
                        <flux:error name="vintage" />
                    </flux:field>

                    <flux:field>
                        <flux:label for="wine_type">Tipo de vino *</flux:label>
                        <flux:select wire:model="wine_type" id="wine_type" required>
                            <option value="tinto">Tinto</option>
                            <option value="blanco">Blanco</option>
                            <option value="rosado">Rosado</option>
                            <option value="espumoso">Espumoso</option>
                            <option value="otro">Otro</option>
                        </flux:select>
                        <flux:error name="wine_type" />
                    </flux:field>

                    <flux:field>
                        <flux:label for="unit">Unidad *</flux:label>
                        <flux:select wire:model="unit" id="unit" required>
                            <option value="litros">Litros</option>
                            <option value="botellas">Botellas (75 cl)</option>
                            <option value="cajas">Cajas</option>
                        </flux:select>
                        <flux:error name="unit" />
                    </flux:field>

                    <flux:field>
                        <flux:label for="quantity">Cantidad total *</flux:label>
                        <flux:input wire:model="quantity" type="number" step="0.001" min="0" id="quantity" required />
                        <flux:description>Vendido: {{ number_format((float)$lot->quantity - (float)$lot->available_quantity, 3) }} {{ $lot->unit }}</flux:description>
                        <flux:error name="quantity" />
                    </flux:field>

                    <flux:field>
                        <flux:label for="available_quantity">Cantidad disponible *</flux:label>
                        <flux:input wire:model="available_quantity" type="number" step="0.001" min="0" id="available_quantity" required />
                        <flux:error name="available_quantity" />
                    </flux:field>

                    <flux:field>
                        <flux:label for="price_per_unit">Precio por unidad (€)</flux:label>
                        <flux:input wire:model="price_per_unit" type="number" step="0.001" min="0" id="price_per_unit" />
                        <flux:error name="price_per_unit" />
                    </flux:field>
                </div>

                <div class="mt-6">
                    <flux:field>
                        <flux:label for="notes">Notas</flux:label>
                        <flux:textarea wire:model="notes" id="notes" rows="3" />
                        <flux:error name="notes" />
                    </flux:field>
                </div>
            </x-agro.form-section>

            <x-agro.form-actions :cancel-url="route('winery.wine-lots.index')" submit-label="Guardar Cambios" />
        </form>
    </x-agro.form-card>
</div>
