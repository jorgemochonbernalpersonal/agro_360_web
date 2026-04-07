<x-agro.form-card
    title="Nuevo Análisis de Residuos"
    description="Registra un análisis de laboratorio para control de LMR (Límites Máximos de Residuos)"
    :back-url="roleRoute('viticulturist.residue-analyses.index')"
>
    <form wire:submit="save" class="space-y-8">

        <x-agro.form-section title="Datos del Análisis">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <flux:field>
                    <flux:label required>Campaña</flux:label>
                    <flux:select wire:model="campaign_id">
                        <option value="">Seleccionar...</option>
                        @foreach($campaigns as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="campaign_id" />
                </flux:field>

                <flux:field>
                    <flux:label>Plantación (opcional)</flux:label>
                    <flux:select wire:model="plot_planting_id">
                        <option value="">Global campaña</option>
                        @foreach($plantings as $p)
                            <option value="{{ $p->id }}">{{ $p->plot->name }} — {{ $p->grapeVariety->name ?? '' }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="plot_planting_id" />
                </flux:field>

                <flux:field>
                    <flux:label required>Fecha del análisis</flux:label>
                    <flux:input wire:model="analysis_date" type="date" />
                    <flux:error name="analysis_date" />
                </flux:field>

                <flux:field>
                    <flux:label>Fecha de toma de muestra</flux:label>
                    <flux:input wire:model="sample_date" type="date" />
                    <flux:error name="sample_date" />
                </flux:field>

                <flux:field>
                    <flux:label required>Laboratorio</flux:label>
                    <flux:input wire:model="laboratory_name" type="text" placeholder="Nombre del laboratorio" />
                    <flux:error name="laboratory_name" />
                </flux:field>

                <flux:field>
                    <flux:label>Acreditación ENAC</flux:label>
                    <flux:input wire:model="laboratory_accreditation" type="text" placeholder="Nº acreditación" />
                    <flux:error name="laboratory_accreditation" />
                </flux:field>

                <flux:field>
                    <flux:label>Tipo de muestra</flux:label>
                    <flux:input wire:model="sample_type" type="text" placeholder="Ej: uva, mosto, vino" />
                    <flux:error name="sample_type" />
                </flux:field>

                <flux:field>
                    <flux:label>Ruta certificado PDF</flux:label>
                    <flux:input wire:model="certificate_file" type="text" placeholder="Ruta o URL del certificado" />
                    <flux:error name="certificate_file" />
                </flux:field>

                <flux:field class="md:col-span-2">
                    <flux:checkbox wire:model="overall_compliant" label="Todos los resultados cumplen los LMR" />
                    <flux:error name="overall_compliant" />
                </flux:field>

                <flux:field class="md:col-span-2">
                    <flux:label>Notas</flux:label>
                    <flux:textarea wire:model="notes" rows="3" />
                    <flux:error name="notes" />
                </flux:field>

            </div>
        </x-agro.form-section>

        <x-agro.form-actions
            :cancel-url="roleRoute('viticulturist.residue-analyses.index')"
            submit-label="Registrar Análisis"
        />
    </form>
</x-agro.form-card>
