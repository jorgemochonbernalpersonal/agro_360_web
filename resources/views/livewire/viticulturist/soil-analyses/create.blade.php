<x-agro.form-card
    :title="__('Nuevo Análisis de Suelo')"
    :description="__('Registra los resultados de un análisis edafológico')"
    :back-url="roleRoute('viticulturist.soil-analyses.index')"
>
    <form wire:submit="save" class="space-y-8">

        {{-- Sección 1: Datos Generales --}}
        <x-agro.form-section :title="__('Datos Generales')">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <flux:field>
                    <flux:label required>{{ __('Parcela') }}</flux:label>
                    <flux:select wire:model="plot_id">
                        <option value="">{{ __('Seleccionar parcela') }}</option>
                        @foreach ($plots as $plot)
                            <option value="{{ $plot->id }}">{{ $plot->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="plot_id" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Campaña') }}</flux:label>
                    <flux:select wire:model="campaign_id">
                        <option value="">{{ __('Sin campaña') }}</option>
                        @foreach ($campaigns as $campaign)
                            <option value="{{ $campaign->id }}">{{ $campaign->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="campaign_id" />
                </flux:field>

                <flux:field>
                    <flux:label required>{{ __('Fecha de análisis') }}</flux:label>
                    <flux:input wire:model="analysis_date" type="date" />
                    <flux:error name="analysis_date" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Laboratorio') }}</flux:label>
                    <flux:input wire:model="laboratory" type="text" :placeholder="__('Ej: Laboratorio Agroalimentario')" />
                    <flux:error name="laboratory" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Profundidad de muestra') }}</flux:label>
                    <flux:input wire:model="sample_depth_cm" type="number" min="0" max="300" :placeholder="__('cm')" />
                    <flux:error name="sample_depth_cm" />
                </flux:field>

            </div>
        </x-agro.form-section>

        {{-- Sección 2: Parámetros Principales --}}
        <x-agro.form-section :title="__('Parámetros Principales')">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <flux:field>
                    <flux:label>{{ __('pH') }}</flux:label>
                    <flux:input wire:model="ph" type="number" step="0.01" min="0" max="14" placeholder="0 - 14" />
                    <flux:error name="ph" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Materia orgánica') }}</flux:label>
                    <flux:input wire:model="organic_matter" type="number" step="0.01" min="0" :placeholder="__('Ej: 2.50')" />
                    <flux:description>%</flux:description>
                    <flux:error name="organic_matter" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Nitrógeno total') }}</flux:label>
                    <flux:input wire:model="nitrogen_total" type="number" step="0.01" min="0" :placeholder="__('Ej: 1.20')" />
                    <flux:description>mg/kg</flux:description>
                    <flux:error name="nitrogen_total" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Fósforo') }}</flux:label>
                    <flux:input wire:model="phosphorus" type="number" step="0.01" min="0" :placeholder="__('Ej: 15.00')" />
                    <flux:description>mg/kg Olsen</flux:description>
                    <flux:error name="phosphorus" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Potasio') }}</flux:label>
                    <flux:input wire:model="potassium" type="number" step="0.01" min="0" :placeholder="__('Ej: 200.00')" />
                    <flux:description>mg/kg</flux:description>
                    <flux:error name="potassium" />
                </flux:field>

            </div>
        </x-agro.form-section>

        {{-- Sección 3: Parámetros Secundarios --}}
        <x-agro.form-section :title="__('Parámetros Secundarios')">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <flux:field>
                    <flux:label>{{ __('Calcio') }}</flux:label>
                    <flux:input wire:model="calcium" type="number" step="0.01" min="0" :placeholder="__('Ej: 3000.00')" />
                    <flux:description>mg/kg</flux:description>
                    <flux:error name="calcium" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Magnesio') }}</flux:label>
                    <flux:input wire:model="magnesium" type="number" step="0.01" min="0" :placeholder="__('Ej: 250.00')" />
                    <flux:description>mg/kg</flux:description>
                    <flux:error name="magnesium" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Textura') }}</flux:label>
                    <flux:select wire:model="texture_class">
                        <option value="">{{ __('Sin especificar') }}</option>
                        @foreach ($textureClasses as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="texture_class" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Conductividad eléctrica') }}</flux:label>
                    <flux:input wire:model="electrical_conductivity" type="number" step="0.01" min="0" :placeholder="__('Ej: 0.85')" />
                    <flux:description>dS/m</flux:description>
                    <flux:error name="electrical_conductivity" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Caliza activa') }}</flux:label>
                    <flux:input wire:model="limestone" type="number" step="0.01" min="0" max="100" :placeholder="__('Ej: 12.50')" />
                    <flux:description>{{ __('% caliza activa') }}</flux:description>
                    <flux:error name="limestone" />
                </flux:field>

            </div>
        </x-agro.form-section>

        {{-- Sección 4: Notas --}}
        <x-agro.form-section :title="__('Notas')">
            <div class="grid grid-cols-1 gap-6">

                <flux:field>
                    <flux:label>{{ __('Notas adicionales') }}</flux:label>
                    <flux:textarea
                        wire:model="notes"
                        rows="3"
                        :placeholder="__('Observaciones, recomendaciones del laboratorio, etc.')"
                    />
                    <flux:error name="notes" />
                </flux:field>

            </div>
        </x-agro.form-section>

        <x-agro.form-actions
            :cancel-url="roleRoute('viticulturist.soil-analyses.index')"
            :submit-label="__('Registrar Análisis')"
        />
    </form>
</x-agro.form-card>
