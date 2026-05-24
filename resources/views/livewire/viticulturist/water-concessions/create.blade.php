<x-agro.form-card
    :title="__('Nueva Concesión de Riego')"
    :description="__('Registra una concesión o derecho de agua para riego en tu explotación')"
    :back-url="roleRoute('viticulturist.water-concessions.index')"
>
    <form wire:submit="save" class="space-y-8">

        <x-agro.form-section :title="__('Tipo de Concesión')">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <flux:field>
                    <flux:label required>{{ __('Tipo de concesión') }}</flux:label>
                    <flux:select wire:model="concession_type">
                        @foreach($concessionTypes as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="concession_type" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Nº de concesión') }}</flux:label>
                    <flux:input wire:model="concession_number" type="text" :placeholder="__('Ej: C-12345/2020')" />
                    <flux:error name="concession_number" />
                </flux:field>

                <flux:field>
                    <flux:label required>{{ __('Masa de agua / Acuífero') }}</flux:label>
                    <flux:input wire:model="water_body" type="text" :placeholder="__('Ej: Río Duero, Acuífero 08.29')" />
                    <flux:error name="water_body" />
                </flux:field>

                <flux:field>
                    <flux:label required>{{ __('Organismo otorgante') }}</flux:label>
                    <flux:input wire:model="authority" type="text" :placeholder="__('Ej: Confederación Hidrográfica del Duero')" />
                    <flux:description>{{ __('Confederación Hidrográfica del Duero, CHS, CHE, etc.') }}</flux:description>
                    <flux:error name="authority" />
                </flux:field>

            </div>
        </x-agro.form-section>

        <x-agro.form-section :title="__('Volumen y Superficie')">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <flux:field>
                    <flux:label required>{{ __('Volumen máximo autorizado (m³)') }}</flux:label>
                    <flux:input wire:model="max_volume_m3" type="number" step="0.001" min="0" placeholder="0.000" />
                    <flux:description>{{ __('Caudal o volumen máximo anual autorizado por la confederación hidrográfica') }}</flux:description>
                    <flux:error name="max_volume_m3" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Volumen utilizado (m³)') }}</flux:label>
                    <flux:input wire:model="used_volume_m3" type="number" step="0.001" min="0" placeholder="0.000" />
                    <flux:description>{{ __('Consumo real registrado en la campaña actual') }}</flux:description>
                    <flux:error name="used_volume_m3" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Superficie regada (ha)') }}</flux:label>
                    <flux:input wire:model="surface_ha" type="number" step="0.0001" min="0" placeholder="0.0000" />
                    <flux:error name="surface_ha" />
                </flux:field>

            </div>
        </x-agro.form-section>

        <x-agro.form-section :title="__('Vigencia')">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <flux:field>
                    <flux:label>{{ __('Fecha de concesión') }}</flux:label>
                    <flux:input wire:model="concession_date" type="date" />
                    <flux:error name="concession_date" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Fecha de vencimiento') }}</flux:label>
                    <flux:input wire:model="expiry_date" type="date" />
                    <flux:error name="expiry_date" />
                </flux:field>

            </div>
        </x-agro.form-section>

        <x-agro.form-section :title="__('Notas')">
            <div class="grid grid-cols-1 gap-6">

                <flux:field>
                    <flux:label>{{ __('Observaciones') }}</flux:label>
                    <flux:textarea wire:model="notes" rows="4" :placeholder="__('Condiciones especiales, restricciones estacionales, etc.')" />
                    <flux:error name="notes" />
                </flux:field>

            </div>
        </x-agro.form-section>

        <x-agro.form-actions
            :cancel-url="roleRoute('viticulturist.water-concessions.index')"
            :submit-label="__('Registrar Concesión')"
        />
    </form>
</x-agro.form-card>
