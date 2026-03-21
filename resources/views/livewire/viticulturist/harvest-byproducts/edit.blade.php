<x-agro.form-card
    title="Editar Subproducto"
    description="Modifica los datos de la salida de subproductos (orujos, raspones, lías)"
    :back-url="route('viticulturist.harvest-byproducts.index')"
>
    <form wire:submit="save" class="space-y-8">

        <x-agro.form-section title="Datos del Subproducto">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <flux:field>
                    <flux:label required>Campaña</flux:label>
                    <flux:select wire:model="campaign_id">
                        @foreach($campaigns as $c)
                            <flux:select.option value="{{ $c->id }}">{{ $c->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="campaign_id" />
                </flux:field>

                <flux:field>
                    <flux:label required>Fecha de entrega</flux:label>
                    <flux:input wire:model="date" type="date" />
                    <flux:error name="date" />
                </flux:field>

                <flux:field>
                    <flux:label required>Tipo de subproducto</flux:label>
                    <flux:select wire:model="byproduct_type">
                        @foreach($byproductTypes as $key => $label)
                            <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="byproduct_type" />
                </flux:field>

                <flux:field>
                    <flux:label required>Cantidad (kg)</flux:label>
                    <flux:input wire:model="quantity_kg" type="number" step="0.001" min="0.001" placeholder="Ej: 5000.000" />
                    <flux:error name="quantity_kg" />
                </flux:field>

            </div>
        </x-agro.form-section>

        <x-agro.form-section title="Destino">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <flux:field>
                    <flux:label required>Tipo de destino</flux:label>
                    <flux:select wire:model="destination_type">
                        @foreach($destinationTypes as $key => $label)
                            <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="destination_type" />
                </flux:field>

                <flux:field>
                    <flux:label required>Nombre del destino</flux:label>
                    <flux:input wire:model="destination_name" type="text" placeholder="Ej: Destilería García S.L." />
                    <flux:error name="destination_name" />
                </flux:field>

                <flux:field>
                    <flux:label>Nº documento / albarán</flux:label>
                    <flux:input wire:model="document_reference" type="text" placeholder="Ej: ALB-2025-00123" />
                    <flux:description>Albarán de entrega o número de documento acreditativo</flux:description>
                    <flux:error name="document_reference" />
                </flux:field>

                <flux:field>
                    <flux:label>Observaciones</flux:label>
                    <flux:textarea wire:model="notes" rows="3" />
                    <flux:error name="notes" />
                </flux:field>

            </div>
        </x-agro.form-section>

        <x-agro.form-actions
            :cancel-url="route('viticulturist.harvest-byproducts.index')"
            submit-label="Guardar Cambios"
        />
    </form>
</x-agro.form-card>
