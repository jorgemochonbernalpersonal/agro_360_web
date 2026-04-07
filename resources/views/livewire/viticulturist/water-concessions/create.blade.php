<x-agro.form-card
    title="Nueva Concesión de Riego"
    description="Registra una concesión o derecho de agua para riego en tu explotación"
    :back-url="roleRoute('viticulturist.water-concessions.index')"
>
    <form wire:submit="save" class="space-y-8">

        <x-agro.form-section title="Tipo de Concesión">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <flux:field>
                    <flux:label required>Tipo de concesión</flux:label>
                    <flux:select wire:model="concession_type">
                        @foreach($concessionTypes as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="concession_type" />
                </flux:field>

                <flux:field>
                    <flux:label>Nº de concesión</flux:label>
                    <flux:input wire:model="concession_number" type="text" placeholder="Ej: C-12345/2020" />
                    <flux:error name="concession_number" />
                </flux:field>

                <flux:field>
                    <flux:label required>Masa de agua / Acuífero</flux:label>
                    <flux:input wire:model="water_body" type="text" placeholder="Ej: Río Duero, Acuífero 08.29" />
                    <flux:error name="water_body" />
                </flux:field>

                <flux:field>
                    <flux:label required>Organismo otorgante</flux:label>
                    <flux:input wire:model="authority" type="text" placeholder="Ej: Confederación Hidrográfica del Duero" />
                    <flux:description>Confederación Hidrográfica del Duero, CHS, CHE, etc.</flux:description>
                    <flux:error name="authority" />
                </flux:field>

            </div>
        </x-agro.form-section>

        <x-agro.form-section title="Volumen y Superficie">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <flux:field>
                    <flux:label required>Volumen máximo autorizado (m³)</flux:label>
                    <flux:input wire:model="max_volume_m3" type="number" step="0.001" min="0" placeholder="0.000" />
                    <flux:description>Caudal o volumen máximo anual autorizado por la confederación hidrográfica</flux:description>
                    <flux:error name="max_volume_m3" />
                </flux:field>

                <flux:field>
                    <flux:label>Volumen utilizado (m³)</flux:label>
                    <flux:input wire:model="used_volume_m3" type="number" step="0.001" min="0" placeholder="0.000" />
                    <flux:description>Consumo real registrado en la campaña actual</flux:description>
                    <flux:error name="used_volume_m3" />
                </flux:field>

                <flux:field>
                    <flux:label>Superficie regada (ha)</flux:label>
                    <flux:input wire:model="surface_ha" type="number" step="0.0001" min="0" placeholder="0.0000" />
                    <flux:error name="surface_ha" />
                </flux:field>

            </div>
        </x-agro.form-section>

        <x-agro.form-section title="Vigencia">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <flux:field>
                    <flux:label>Fecha de concesión</flux:label>
                    <flux:input wire:model="concession_date" type="date" />
                    <flux:error name="concession_date" />
                </flux:field>

                <flux:field>
                    <flux:label>Fecha de vencimiento</flux:label>
                    <flux:input wire:model="expiry_date" type="date" />
                    <flux:error name="expiry_date" />
                </flux:field>

            </div>
        </x-agro.form-section>

        <x-agro.form-section title="Notas">
            <div class="grid grid-cols-1 gap-6">

                <flux:field>
                    <flux:label>Observaciones</flux:label>
                    <flux:textarea wire:model="notes" rows="4" placeholder="Condiciones especiales, restricciones estacionales, etc." />
                    <flux:error name="notes" />
                </flux:field>

            </div>
        </x-agro.form-section>

        <x-agro.form-actions
            :cancel-url="roleRoute('viticulturist.water-concessions.index')"
            submit-label="Registrar Concesión"
        />
    </form>
</x-agro.form-card>
