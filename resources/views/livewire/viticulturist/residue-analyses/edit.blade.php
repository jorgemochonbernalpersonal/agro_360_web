<x-agro.form-card
    :title="__('Editar Análisis de Residuos')"
    :description="__('Modifica el análisis del') . ' ' . $residueAnalysis->analysis_date->format('d/m/Y')"
    :back-url="roleRoute('viticulturist.residue-analyses.index')"
>
    <form wire:submit="save" class="space-y-8">

        <x-agro.form-section :title="__('Datos del Análisis')">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <flux:field>
                    <flux:label required>{{ __('Campaña') }}</flux:label>
                    <flux:select wire:model="campaign_id">
                        <option value="">{{ __('Seleccionar...') }}</option>
                        @foreach($campaigns as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="campaign_id" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Plantación (opcional)') }}</flux:label>
                    <flux:select wire:model="plot_planting_id">
                        <option value="">{{ __('Global campaña') }}</option>
                        @foreach($plantings as $p)
                            <option value="{{ $p->id }}">{{ $p->plot->name }} — {{ $p->grapeVariety->name ?? '' }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="plot_planting_id" />
                </flux:field>

                <flux:field>
                    <flux:label required>{{ __('Fecha del análisis') }}</flux:label>
                    <flux:input wire:model="analysis_date" type="date" />
                    <flux:error name="analysis_date" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Fecha de toma de muestra') }}</flux:label>
                    <flux:input wire:model="sample_date" type="date" />
                    <flux:error name="sample_date" />
                </flux:field>

                <flux:field>
                    <flux:label required>{{ __('Laboratorio') }}</flux:label>
                    <flux:input wire:model="laboratory_name" type="text" :placeholder="__('Nombre del laboratorio')" />
                    <flux:error name="laboratory_name" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Acreditación ENAC') }}</flux:label>
                    <flux:input wire:model="laboratory_accreditation" type="text" :placeholder="__('Nº acreditación')" />
                    <flux:error name="laboratory_accreditation" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Tipo de muestra') }}</flux:label>
                    <flux:input wire:model="sample_type" type="text" :placeholder="__('Ej: uva, mosto, vino')" />
                    <flux:error name="sample_type" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Ruta certificado PDF') }}</flux:label>
                    <flux:input wire:model="certificate_file" type="text" :placeholder="__('Ruta o URL del certificado')" />
                    <flux:error name="certificate_file" />
                </flux:field>

                <flux:field class="md:col-span-2">
                    <flux:checkbox wire:model="overall_compliant" :label="__('Todos los resultados cumplen los LMR')" />
                    <flux:error name="overall_compliant" />
                </flux:field>

                <flux:field class="md:col-span-2">
                    <flux:label>{{ __('Notas') }}</flux:label>
                    <flux:textarea wire:model="notes" rows="3" />
                    <flux:error name="notes" />
                </flux:field>

            </div>
        </x-agro.form-section>

        <x-agro.form-actions
            :cancel-url="roleRoute('viticulturist.residue-analyses.index')"
            :submit-label="__('Actualizar Análisis')"
        />
    </form>
</x-agro.form-card>
