<div>
    <x-agro.form-card title="Nuevo Lote de Vino" description="Registra un lote de vino para su venta y facturación"
        :back-url="route('winery.wine-lots.index')">

        <form wire:submit.prevent="save" class="space-y-8">

            <x-agro.form-section title="Informacion del Lote">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:field>
                        <flux:label for="name">Nombre del lote *</flux:label>
                        <flux:input wire:model="name" type="text" id="name" placeholder="Ej: Vino Jorge Reserva 2023" required />
                        <flux:error name="name" />
                    </flux:field>

                    <flux:field>
                        <flux:label for="vintage">Añada (año)</flux:label>
                        <flux:input wire:model="vintage" type="number" id="vintage" placeholder="{{ now()->year }}" min="1900" max="{{ now()->year + 1 }}" />
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
                        <flux:input wire:model.live="quantity" type="number" step="0.001" min="0" id="quantity" placeholder="0" required />
                        <flux:error name="quantity" />
                    </flux:field>

                    <flux:field>
                        <flux:label for="available_quantity">Cantidad disponible para venta *</flux:label>
                        <flux:input wire:model="available_quantity" type="number" step="0.001" min="0" id="available_quantity" placeholder="0" required />
                        <flux:description>Por defecto igual a la cantidad total.</flux:description>
                        <flux:error name="available_quantity" />
                    </flux:field>

                    <flux:field>
                        <flux:label for="price_per_unit">Precio por unidad (€)</flux:label>
                        <flux:input wire:model="price_per_unit" type="number" step="0.001" min="0" id="price_per_unit" placeholder="0.000" />
                        <flux:description>Se usará como precio por defecto al facturar.</flux:description>
                        <flux:error name="price_per_unit" />
                    </flux:field>
                </div>

                <div class="mt-6">
                    <flux:field>
                        <flux:label for="notes">Notas</flux:label>
                        <flux:textarea wire:model="notes" id="notes" rows="3" placeholder="Observaciones sobre este lote..." />
                        <flux:error name="notes" />
                    </flux:field>
                </div>
            </x-agro.form-section>

            <x-agro.form-actions :cancel-url="route('winery.wine-lots.index')" submit-label="Crear Lote" />
        </form>
    </x-agro.form-card>
</div>
