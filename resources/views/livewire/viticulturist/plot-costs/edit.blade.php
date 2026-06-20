<div>
<x-agro.form-card
    :title="__('Editar Coste')"
    :description="__('Modifica los datos del coste registrado.')"
    :back-url="roleRoute('viticulturist.plot-costs.index')"
>
    <form wire:submit.prevent="save" class="space-y-8">
        <x-agro.form-section :title="__('Datos del Coste')">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <flux:field class="md:col-span-2">
                    <flux:label required>{{ __('Descripción') }}</flux:label>
                    <flux:input wire:model="description" :placeholder="__('Ej. Tratamiento fitosanitario mildiu')" />
                    <flux:error name="description" />
                </flux:field>

                <flux:field>
                    <flux:label required>{{ __('Categoría') }}</flux:label>
                    <flux:select wire:model="category">
                        @foreach($categories as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="category" />
                </flux:field>

                <flux:field>
                    <flux:label required>{{ __('Importe (€)') }}</flux:label>
                    <flux:input wire:model="amount" type="number" step="0.01" min="0" placeholder="0.00" />
                    <flux:error name="amount" />
                </flux:field>

                <flux:field>
                    <flux:label required>{{ __('Fecha') }}</flux:label>
                    <flux:input wire:model="cost_date" type="date" />
                    <flux:error name="cost_date" />
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
                    <flux:label>{{ __('Proveedor') }}</flux:label>
                    <flux:input wire:model="supplier" :placeholder="__('Nombre del proveedor o empresa')" />
                    <flux:error name="supplier" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Referencia de factura') }}</flux:label>
                    <flux:input wire:model="invoice_reference" :placeholder="__('Nº de factura o albarán')" />
                    <flux:error name="invoice_reference" />
                </flux:field>

            </div>

            <div class="mt-6">
                <flux:field>
                    <flux:label>{{ __('Notas') }}</flux:label>
                    <flux:textarea wire:model="notes" rows="3" :placeholder="__('Observaciones adicionales...')" />
                    <flux:error name="notes" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-actions :cancel-url="roleRoute('viticulturist.plot-costs.index')" :submit-label="__('Actualizar Coste')" />
    </form>
</x-agro.form-card>
</div>
