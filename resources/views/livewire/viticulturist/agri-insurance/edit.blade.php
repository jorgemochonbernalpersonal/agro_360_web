<div>
<x-agro.form-card
    :title="__('Editar Póliza de Seguro')"
    :description="__('Modifica los datos de la póliza de seguro.')"
    :back-url="roleRoute('viticulturist.agri-insurance.index')"
>
    <form wire:submit.prevent="update" class="space-y-8">
        <x-agro.form-section :title="__('Datos de la Póliza')">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <flux:field>
                    <flux:label required>{{ __('Aseguradora') }}</flux:label>
                    <flux:input wire:model="insurance_company" :placeholder="__('Nombre de la compañía aseguradora')" />
                    <flux:error name="insurance_company" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Nº de póliza') }}</flux:label>
                    <flux:input wire:model="policy_number" :placeholder="__('Número o referencia de la póliza')" />
                    <flux:error name="policy_number" />
                </flux:field>

                <flux:field>
                    <flux:label required>{{ __('Tipo de cobertura') }}</flux:label>
                    <flux:select wire:model="coverage_type">
                        @foreach($coverageTypes as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="coverage_type" />
                </flux:field>

                <flux:field>
                    <flux:label required>{{ __('Estado') }}</flux:label>
                    <flux:select wire:model="status">
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="status" />
                </flux:field>

                <flux:field>
                    <flux:label required>{{ __('Fecha de inicio') }}</flux:label>
                    <flux:input wire:model="start_date" type="date" />
                    <flux:error name="start_date" />
                </flux:field>

                <flux:field>
                    <flux:label required>{{ __('Fecha de vencimiento') }}</flux:label>
                    <flux:input wire:model="end_date" type="date" />
                    <flux:error name="end_date" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Capital asegurado (€)') }}</flux:label>
                    <flux:input wire:model="insured_amount" type="number" step="0.01" min="0" placeholder="0.00" />
                    <flux:error name="insured_amount" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Prima anual (€)') }}</flux:label>
                    <flux:input wire:model="premium" type="number" step="0.01" min="0" placeholder="0.00" />
                    <flux:error name="premium" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Subvención ENESA (€)') }}</flux:label>
                    <flux:input wire:model="subsidy_amount" type="number" step="0.01" min="0" placeholder="0.00" />
                    <flux:error name="subsidy_amount" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Agente / Corredor') }}</flux:label>
                    <flux:input wire:model="agent_name" :placeholder="__('Nombre del agente de seguros')" />
                    <flux:error name="agent_name" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Teléfono del agente') }}</flux:label>
                    <flux:input wire:model="agent_phone" type="tel" placeholder="666 000 000" />
                    <flux:error name="agent_phone" />
                </flux:field>

            </div>

            <div class="mt-6 space-y-4">
                <flux:field>
                    <flux:label>{{ __('Parcelas cubiertas') }}</flux:label>
                    <flux:textarea wire:model="covered_plots" rows="2" :placeholder="__('Lista de parcelas o referencia catastral...')" />
                    <flux:error name="covered_plots" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Notas') }}</flux:label>
                    <flux:textarea wire:model="notes" rows="2" :placeholder="__('Condiciones especiales, franquicias...')" />
                    <flux:error name="notes" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-actions :cancel-url="roleRoute('viticulturist.agri-insurance.index')" :submit-label="__('Actualizar Póliza')" />
    </form>
</x-agro.form-card>
</div>
