<x-agro.form-card
    title="Editar Exportación CUE"
    :description="'Exportación ' . $cueExport->campaign_year . ' — ' . $cueExport->exploitation->exploitation_name"
    :back-url="roleRoute('viticulturist.cue-exports.index')"
>
    <form wire:submit="save" class="space-y-8">
        <x-agro.form-section title="Datos de la Exportación">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <flux:field>
                    <flux:label required>Explotación</flux:label>
                    <flux:select wire:model="exploitation_id">
                        <option value="">Seleccionar explotación...</option>
                        @foreach($exploitations as $e)
                            <option value="{{ $e->id }}">{{ $e->exploitation_name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="exploitation_id" />
                </flux:field>

                <flux:field>
                    <flux:label required>Año de campaña</flux:label>
                    <flux:input wire:model.live="campaign_year" type="number" min="2000" :max="now()->year + 1" />
                    <flux:error name="campaign_year" />
                </flux:field>

                <flux:field>
                    <flux:label required>Tipo de período</flux:label>
                    <flux:select wire:model.live="period_type">
                        <option value="annual">Anual</option>
                        <option value="quarterly">Trimestral</option>
                    </flux:select>
                    <flux:error name="period_type" />
                </flux:field>

                <div></div>

                <flux:field>
                    <flux:label required>Fecha de inicio</flux:label>
                    <flux:input wire:model="from_date" type="date" />
                    <flux:error name="from_date" />
                </flux:field>

                <flux:field>
                    <flux:label required>Fecha de fin</flux:label>
                    <flux:input wire:model="to_date" type="date" />
                    <flux:error name="to_date" />
                </flux:field>

            </div>
        </x-agro.form-section>

        <x-agro.form-actions
            :cancel-url="roleRoute('viticulturist.cue-exports.index')"
            submit-label="Actualizar Exportación"
        />
    </form>
</x-agro.form-card>
