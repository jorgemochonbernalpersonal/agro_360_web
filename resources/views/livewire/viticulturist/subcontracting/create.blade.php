<div>
<x-agro.form-card
    :title="__('Registrar Servicio Subcontratado')"
    :description="__('Añade un nuevo servicio externo contratado para tu explotación.')"
    :back-url="roleRoute('viticulturist.subcontracting.index')"
>
    <form wire:submit.prevent="save" class="space-y-8">
        <x-agro.form-section :title="__('Datos del Servicio')">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <flux:field>
                    <flux:label required>{{ __('Empresa / Contratista') }}</flux:label>
                    <flux:input wire:model="company_name" :placeholder="__('Nombre de la empresa o autónomo')" />
                    <flux:error name="company_name" />
                </flux:field>

                <flux:field>
                    <flux:label required>{{ __('Tipo de servicio') }}</flux:label>
                    <flux:select wire:model="service_type">
                        @foreach($serviceTypes as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="service_type" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Persona de contacto') }}</flux:label>
                    <flux:input wire:model="contact_person" :placeholder="__('Nombre del responsable')" />
                    <flux:error name="contact_person" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Teléfono de contacto') }}</flux:label>
                    <flux:input wire:model="contact_phone" type="tel" placeholder="666 000 000" />
                    <flux:error name="contact_phone" />
                </flux:field>

                <flux:field>
                    <flux:label required>{{ __('Fecha de inicio') }}</flux:label>
                    <flux:input wire:model="service_date" type="date" />
                    <flux:error name="service_date" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Fecha de fin') }}</flux:label>
                    <flux:input wire:model="service_end_date" type="date" />
                    <flux:description>{{ __('Opcional, para servicios de varios días') }}</flux:description>
                    <flux:error name="service_end_date" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Importe (€)') }}</flux:label>
                    <flux:input wire:model="amount" type="number" step="0.01" min="0" placeholder="0.00" />
                    <flux:error name="amount" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Parcela') }}</flux:label>
                    <flux:select wire:model="plot_id">
                        <option value="">{{ __('Sin parcela concreta') }}</option>
                        @foreach($plots as $plot)
                            <option value="{{ $plot->id }}">{{ $plot->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="plot_id" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Campaña') }}</flux:label>
                    <flux:select wire:model="campaign_id">
                        <option value="">{{ __('Sin campaña') }}</option>
                        @foreach($campaigns as $campaign)
                            <option value="{{ $campaign->id }}">{{ __('Campaña') }} {{ $campaign->year }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="campaign_id" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Nº de factura') }}</flux:label>
                    <flux:input wire:model="invoice_number" :placeholder="__('Referencia de la factura')" />
                    <flux:error name="invoice_number" />
                </flux:field>

            </div>

            <div class="mt-6 space-y-4">
                <flux:field>
                    <flux:label>{{ __('Descripción del trabajo') }}</flux:label>
                    <flux:textarea wire:model="description" rows="2" :placeholder="__('Describe brevemente el trabajo realizado...')" />
                    <flux:error name="description" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Notas') }}</flux:label>
                    <flux:textarea wire:model="notes" rows="2" :placeholder="__('Observaciones adicionales...')" />
                    <flux:error name="notes" />
                </flux:field>

                <flux:checkbox wire:model="invoiced" :label="__('Servicio ya facturado')" />
            </div>
        </x-agro.form-section>

        <x-agro.form-actions :cancel-url="roleRoute('viticulturist.subcontracting.index')" :submit-label="__('Registrar Servicio')" />
    </form>
</x-agro.form-card>
</div>
