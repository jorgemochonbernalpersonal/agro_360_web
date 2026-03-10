<x-agro.form-card
    title="Editar Entrega de Cosecha"
    :description="'Modifica el registro del ' . $marketedHarvest->delivery_date->format('d/m/Y')"
    :back-url="route('viticulturist.marketed-harvests.index')"
>
    <form wire:submit="save" class="space-y-8">

        <x-agro.form-section title="Datos de la Entrega">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <flux:field class="md:col-span-2">
                    <flux:label required>Cosecha asociada</flux:label>
                    <flux:select wire:model="harvest_id">
                        <option value="">Seleccionar cosecha...</option>
                        @foreach($harvests as $h)
                            <option value="{{ $h->id }}">
                                {{ $h->plotPlanting->plot->name ?? '?' }} — {{ $h->plotPlanting->grapeVariety->name ?? '?' }}
                                ({{ $h->harvest_start_date->format('d/m/Y') }}, {{ number_format($h->total_weight, 0, ',', '.') }} kg)
                            </option>
                        @endforeach
                    </flux:select>
                    <flux:error name="harvest_id" />
                </flux:field>

                <flux:field>
                    <flux:label required>Fecha de entrega</flux:label>
                    <flux:input wire:model="delivery_date" type="date" />
                    <flux:error name="delivery_date" />
                </flux:field>

                <flux:field>
                    <flux:label required>Cantidad (kg)</flux:label>
                    <flux:input wire:model.live="quantity_kg" type="number" step="0.001" min="0" placeholder="0.000" />
                    <flux:error name="quantity_kg" />
                </flux:field>

                <flux:field>
                    <flux:label required>Destino</flux:label>
                    <flux:select wire:model="destination_type">
                        <option value="">Seleccionar...</option>
                        @foreach($destinations as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="destination_type" />
                </flux:field>

                <flux:field>
                    <flux:label>Comprador / Bodega</flux:label>
                    <flux:input wire:model="buyer_name" type="text" placeholder="Nombre del comprador" />
                    <flux:error name="buyer_name" />
                </flux:field>

                <flux:field>
                    <flux:label>REGA del destino</flux:label>
                    <flux:input wire:model="buyer_rega_code" type="text" placeholder="Código REGA" />
                    <flux:error name="buyer_rega_code" />
                </flux:field>

                <flux:field>
                    <flux:label>Documento de transporte</flux:label>
                    <flux:input wire:model="transport_document" type="text" placeholder="Nº albarán/CMR" />
                    <flux:error name="transport_document" />
                </flux:field>

                <flux:field>
                    <flux:label>Matrícula vehículo</flux:label>
                    <flux:input wire:model="vehicle_plate" type="text" placeholder="0000-AAA" />
                    <flux:error name="vehicle_plate" />
                </flux:field>

            </div>
        </x-agro.form-section>

        <x-agro.form-section title="Precio y Valor">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <flux:field>
                    <flux:label>Precio/kg (€)</flux:label>
                    <flux:input wire:model.live="price_per_kg" type="number" step="0.0001" min="0" placeholder="0.0000" />
                    <flux:error name="price_per_kg" />
                </flux:field>

                <flux:field>
                    <flux:label>Valor total (€)</flux:label>
                    <flux:input wire:model="total_value" type="number" step="0.01" min="0" placeholder="0.00" />
                    <flux:description>Calculado automáticamente</flux:description>
                    <flux:error name="total_value" />
                </flux:field>

            </div>
        </x-agro.form-section>

        <x-agro.form-section title="Notas">
            <div class="grid grid-cols-1 gap-6">

                <flux:field>
                    <flux:label>Notas</flux:label>
                    <flux:textarea wire:model="notes" rows="3" />
                    <flux:error name="notes" />
                </flux:field>

            </div>
        </x-agro.form-section>

        <x-agro.form-actions
            :cancel-url="route('viticulturist.marketed-harvests.index')"
            submit-label="Actualizar Entrega"
        />
    </form>
</x-agro.form-card>
