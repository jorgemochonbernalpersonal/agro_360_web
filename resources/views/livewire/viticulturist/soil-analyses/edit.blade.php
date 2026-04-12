<x-agro.form-card
    title="Editar Análisis de Suelo"
    :description="'Modifica los datos del análisis de ' . ($soilAnalysis->plot->name ?? 'parcela')"
    :back-url="roleRoute('viticulturist.soil-analyses.index')"
>
    <form wire:submit="save" class="space-y-8">

        {{-- Sección 1: Datos Generales --}}
        <x-agro.form-section title="Datos Generales">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <flux:field>
                    <flux:label required>Parcela</flux:label>
                    <flux:select wire:model="plot_id">
                        <option value="">Seleccionar parcela</option>
                        @foreach ($plots as $plot)
                            <option value="{{ $plot->id }}">{{ $plot->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="plot_id" />
                </flux:field>

                <flux:field>
                    <flux:label>Campaña</flux:label>
                    <flux:select wire:model="campaign_id">
                        <option value="">Sin campaña</option>
                        @foreach ($campaigns as $campaign)
                            <option value="{{ $campaign->id }}">{{ $campaign->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="campaign_id" />
                </flux:field>

                <flux:field>
                    <flux:label required>Fecha de análisis</flux:label>
                    <flux:input wire:model="analysis_date" type="date" />
                    <flux:error name="analysis_date" />
                </flux:field>

                <flux:field>
                    <flux:label>Laboratorio</flux:label>
                    <flux:input wire:model="laboratory" type="text" />
                    <flux:error name="laboratory" />
                </flux:field>

                <flux:field>
                    <flux:label>Profundidad de muestra</flux:label>
                    <flux:input wire:model="sample_depth_cm" type="number" min="0" max="300" placeholder="cm" />
                    <flux:error name="sample_depth_cm" />
                </flux:field>

            </div>
        </x-agro.form-section>

        {{-- Sección 2: Parámetros Principales --}}
        <x-agro.form-section title="Parámetros Principales">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <flux:field>
                    <flux:label>pH</flux:label>
                    <flux:input wire:model="ph" type="number" step="0.01" min="0" max="14" />
                    <flux:error name="ph" />
                </flux:field>

                <flux:field>
                    <flux:label>Materia orgánica</flux:label>
                    <flux:input wire:model="organic_matter" type="number" step="0.01" min="0" />
                    <flux:description>%</flux:description>
                    <flux:error name="organic_matter" />
                </flux:field>

                <flux:field>
                    <flux:label>Nitrógeno total</flux:label>
                    <flux:input wire:model="nitrogen_total" type="number" step="0.01" min="0" />
                    <flux:description>mg/kg</flux:description>
                    <flux:error name="nitrogen_total" />
                </flux:field>

                <flux:field>
                    <flux:label>Fósforo</flux:label>
                    <flux:input wire:model="phosphorus" type="number" step="0.01" min="0" />
                    <flux:description>mg/kg Olsen</flux:description>
                    <flux:error name="phosphorus" />
                </flux:field>

                <flux:field>
                    <flux:label>Potasio</flux:label>
                    <flux:input wire:model="potassium" type="number" step="0.01" min="0" />
                    <flux:description>mg/kg</flux:description>
                    <flux:error name="potassium" />
                </flux:field>

            </div>
        </x-agro.form-section>

        {{-- Sección 3: Parámetros Secundarios --}}
        <x-agro.form-section title="Parámetros Secundarios">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <flux:field>
                    <flux:label>Calcio</flux:label>
                    <flux:input wire:model="calcium" type="number" step="0.01" min="0" />
                    <flux:description>mg/kg</flux:description>
                    <flux:error name="calcium" />
                </flux:field>

                <flux:field>
                    <flux:label>Magnesio</flux:label>
                    <flux:input wire:model="magnesium" type="number" step="0.01" min="0" />
                    <flux:description>mg/kg</flux:description>
                    <flux:error name="magnesium" />
                </flux:field>

                <flux:field>
                    <flux:label>Textura</flux:label>
                    <flux:select wire:model="texture_class">
                        <option value="">Sin especificar</option>
                        @foreach ($textureClasses as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="texture_class" />
                </flux:field>

                <flux:field>
                    <flux:label>Conductividad eléctrica</flux:label>
                    <flux:input wire:model="electrical_conductivity" type="number" step="0.01" min="0" />
                    <flux:description>dS/m</flux:description>
                    <flux:error name="electrical_conductivity" />
                </flux:field>

                <flux:field>
                    <flux:label>Caliza activa</flux:label>
                    <flux:input wire:model="limestone" type="number" step="0.01" min="0" max="100" />
                    <flux:description>% caliza activa</flux:description>
                    <flux:error name="limestone" />
                </flux:field>

            </div>
        </x-agro.form-section>

        {{-- Sección 4: Notas --}}
        <x-agro.form-section title="Notas">
            <div class="grid grid-cols-1 gap-6">

                <flux:field>
                    <flux:label>Notas adicionales</flux:label>
                    <flux:textarea
                        wire:model="notes"
                        rows="3"
                    />
                    <flux:error name="notes" />
                </flux:field>

            </div>
        </x-agro.form-section>

        <x-agro.form-actions
            :cancel-url="roleRoute('viticulturist.soil-analyses.index')"
            submit-label="Guardar Cambios"
        />
    </form>
</x-agro.form-card>
