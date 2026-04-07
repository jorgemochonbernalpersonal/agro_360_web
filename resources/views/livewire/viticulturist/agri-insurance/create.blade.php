<div>
<x-agro.form-card
    title="Añadir Póliza de Seguro"
    description="Registra una nueva póliza de seguro agrario para tu explotación."
    :back-url="roleRoute('viticulturist.agri-insurance.index')"
>
    <form wire:submit.prevent="save" class="space-y-8">
        <x-agro.form-section title="Datos de la Póliza">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <flux:field>
                    <flux:label required>Aseguradora</flux:label>
                    <flux:input wire:model="insurance_company" placeholder="Nombre de la compañía aseguradora" />
                    <flux:error name="insurance_company" />
                </flux:field>

                <flux:field>
                    <flux:label>Nº de póliza</flux:label>
                    <flux:input wire:model="policy_number" placeholder="Número o referencia de la póliza" />
                    <flux:error name="policy_number" />
                </flux:field>

                <flux:field>
                    <flux:label required>Tipo de cobertura</flux:label>
                    <flux:select wire:model="coverage_type">
                        @foreach($coverageTypes as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="coverage_type" />
                </flux:field>

                <flux:field>
                    <flux:label required>Estado</flux:label>
                    <flux:select wire:model="status">
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="status" />
                </flux:field>

                <flux:field>
                    <flux:label required>Fecha de inicio</flux:label>
                    <flux:input wire:model="start_date" type="date" />
                    <flux:error name="start_date" />
                </flux:field>

                <flux:field>
                    <flux:label required>Fecha de vencimiento</flux:label>
                    <flux:input wire:model="end_date" type="date" />
                    <flux:error name="end_date" />
                </flux:field>

                <flux:field>
                    <flux:label>Capital asegurado (€)</flux:label>
                    <flux:input wire:model="insured_amount" type="number" step="0.01" min="0" placeholder="0.00" />
                    <flux:error name="insured_amount" />
                </flux:field>

                <flux:field>
                    <flux:label>Prima anual (€)</flux:label>
                    <flux:input wire:model="premium" type="number" step="0.01" min="0" placeholder="0.00" />
                    <flux:error name="premium" />
                </flux:field>

                <flux:field>
                    <flux:label>Subvención ENESA (€)</flux:label>
                    <flux:input wire:model="subsidy_amount" type="number" step="0.01" min="0" placeholder="0.00" />
                    <flux:description>Importe subsidiado por el Estado</flux:description>
                    <flux:error name="subsidy_amount" />
                </flux:field>

                <flux:field>
                    <flux:label>Agente / Corredor</flux:label>
                    <flux:input wire:model="agent_name" placeholder="Nombre del agente de seguros" />
                    <flux:error name="agent_name" />
                </flux:field>

                <flux:field>
                    <flux:label>Teléfono del agente</flux:label>
                    <flux:input wire:model="agent_phone" type="tel" placeholder="666 000 000" />
                    <flux:error name="agent_phone" />
                </flux:field>

            </div>

            <div class="mt-6 space-y-4">
                <flux:field>
                    <flux:label>Parcelas cubiertas</flux:label>
                    <flux:textarea wire:model="covered_plots" rows="2" placeholder="Lista de parcelas o referencia catastral..." />
                    <flux:error name="covered_plots" />
                </flux:field>

                <flux:field>
                    <flux:label>Notas</flux:label>
                    <flux:textarea wire:model="notes" rows="2" placeholder="Condiciones especiales, franquicias..." />
                    <flux:error name="notes" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-actions :cancel-url="roleRoute('viticulturist.agri-insurance.index')" submit-label="Guardar Póliza" />
    </form>
</x-agro.form-card>
</div>
