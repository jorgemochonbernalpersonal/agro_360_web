<div>
<x-agro.form-card
    title="Editar Coste"
    description="Modifica los datos del coste registrado."
    :back-url="route('viticulturist.plot-costs.index')"
>
    <form wire:submit.prevent="update" class="space-y-8">
        <x-agro.form-section title="Datos del Coste">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <flux:field class="md:col-span-2">
                    <flux:label required>Descripción</flux:label>
                    <flux:input wire:model="description" placeholder="Ej. Tratamiento fitosanitario mildiu" />
                    <flux:error name="description" />
                </flux:field>

                <flux:field>
                    <flux:label required>Categoría</flux:label>
                    <flux:select wire:model="category">
                        @foreach($categories as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="category" />
                </flux:field>

                <flux:field>
                    <flux:label required>Importe (€)</flux:label>
                    <flux:input wire:model="amount" type="number" step="0.01" min="0" placeholder="0.00" />
                    <flux:error name="amount" />
                </flux:field>

                <flux:field>
                    <flux:label required>Fecha</flux:label>
                    <flux:input wire:model="cost_date" type="date" />
                    <flux:error name="cost_date" />
                </flux:field>

                <flux:field>
                    <flux:label>Parcela</flux:label>
                    <flux:select wire:model="plot_id">
                        <option value="">Sin parcela concreta</option>
                        @foreach($plots as $plot)
                            <option value="{{ $plot->id }}">{{ $plot->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="plot_id" />
                </flux:field>

                <flux:field>
                    <flux:label>Campaña</flux:label>
                    <flux:select wire:model="campaign_id">
                        <option value="">Sin campaña</option>
                        @foreach($campaigns as $campaign)
                            <option value="{{ $campaign->id }}">Campaña {{ $campaign->year }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="campaign_id" />
                </flux:field>

                <flux:field>
                    <flux:label>Proveedor</flux:label>
                    <flux:input wire:model="supplier" placeholder="Nombre del proveedor o empresa" />
                    <flux:error name="supplier" />
                </flux:field>

                <flux:field>
                    <flux:label>Referencia de factura</flux:label>
                    <flux:input wire:model="invoice_reference" placeholder="Nº de factura o albarán" />
                    <flux:error name="invoice_reference" />
                </flux:field>

            </div>

            <div class="mt-6">
                <flux:field>
                    <flux:label>Notas</flux:label>
                    <flux:textarea wire:model="notes" rows="3" placeholder="Observaciones adicionales..." />
                    <flux:error name="notes" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-actions :cancel-url="route('viticulturist.plot-costs.index')" submit-label="Actualizar Coste" />
    </form>
</x-agro.form-card>
</div>
