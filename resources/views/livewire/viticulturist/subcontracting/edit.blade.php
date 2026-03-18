<div>
<x-agro.form-card
    title="Editar Servicio Subcontratado"
    description="Modifica los datos del servicio subcontratado."
    :back-url="route('viticulturist.subcontracting.index')"
>
    <form wire:submit.prevent="update" class="space-y-8">
        <x-agro.form-section title="Datos del Servicio">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <flux:field>
                    <flux:label required>Empresa / Contratista</flux:label>
                    <flux:input wire:model="company_name" placeholder="Nombre de la empresa o autónomo" />
                    <flux:error name="company_name" />
                </flux:field>

                <flux:field>
                    <flux:label required>Tipo de servicio</flux:label>
                    <flux:select wire:model="service_type">
                        @foreach($serviceTypes as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="service_type" />
                </flux:field>

                <flux:field>
                    <flux:label>Persona de contacto</flux:label>
                    <flux:input wire:model="contact_person" placeholder="Nombre del responsable" />
                    <flux:error name="contact_person" />
                </flux:field>

                <flux:field>
                    <flux:label>Teléfono de contacto</flux:label>
                    <flux:input wire:model="contact_phone" type="tel" placeholder="666 000 000" />
                    <flux:error name="contact_phone" />
                </flux:field>

                <flux:field>
                    <flux:label required>Fecha de inicio</flux:label>
                    <flux:input wire:model="service_date" type="date" />
                    <flux:error name="service_date" />
                </flux:field>

                <flux:field>
                    <flux:label>Fecha de fin</flux:label>
                    <flux:input wire:model="service_end_date" type="date" />
                    <flux:error name="service_end_date" />
                </flux:field>

                <flux:field>
                    <flux:label>Importe (€)</flux:label>
                    <flux:input wire:model="amount" type="number" step="0.01" min="0" placeholder="0.00" />
                    <flux:error name="amount" />
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
                    <flux:label>Nº de factura</flux:label>
                    <flux:input wire:model="invoice_number" placeholder="Referencia de la factura" />
                    <flux:error name="invoice_number" />
                </flux:field>

            </div>

            <div class="mt-6 space-y-4">
                <flux:field>
                    <flux:label>Descripción del trabajo</flux:label>
                    <flux:textarea wire:model="description" rows="2" placeholder="Describe brevemente el trabajo realizado..." />
                    <flux:error name="description" />
                </flux:field>

                <flux:field>
                    <flux:label>Notas</flux:label>
                    <flux:textarea wire:model="notes" rows="2" placeholder="Observaciones adicionales..." />
                    <flux:error name="notes" />
                </flux:field>

                <flux:checkbox wire:model="invoiced" label="Servicio ya facturado" />
            </div>
        </x-agro.form-section>

        <x-agro.form-actions :cancel-url="route('viticulturist.subcontracting.index')" submit-label="Actualizar Servicio" />
    </form>
</x-agro.form-card>
</div>
