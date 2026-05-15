<x-agro.form-card
    title="Editar Registro de Biodiversidad"
    description="Modifica los datos del registro de biodiversidad o cubierta vegetal"
    :back-url="roleRoute('viticulturist.biodiversity-records.index')"
>
    <form wire:submit="save" class="space-y-8">

        <x-agro.form-section title="Tipo y Ubicaci&oacute;n">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <flux:field>
                    <flux:label required>Tipo de registro</flux:label>
                    <flux:select wire:model="record_type">
                        @foreach($recordTypes as $key => $label)
                            <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="record_type" />
                </flux:field>

                <flux:field>
                    <flux:label required>Parcela</flux:label>
                    <flux:select wire:model="plot_id">
                        <flux:select.option value="">Seleccionar parcela...</flux:select.option>
                        @foreach($plots as $plot)
                            <flux:select.option value="{{ $plot->id }}">{{ $plot->name }} @if($plot->municipality?->name)&middot; {{ $plot->municipality->name }}@endif</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="plot_id" />
                </flux:field>

                <flux:field>
                    <flux:label>Campa&ntilde;a</flux:label>
                    <flux:select wire:model="campaign_id">
                        <flux:select.option value="">Sin campa&ntilde;a</flux:select.option>
                        @foreach($campaigns as $c)
                            <flux:select.option value="{{ $c->id }}">{{ $c->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="campaign_id" />
                </flux:field>

                <flux:field>
                    <flux:label required>Fecha de registro</flux:label>
                    <flux:input wire:model="record_date" type="date" />
                    <flux:error name="record_date" />
                </flux:field>

            </div>
        </x-agro.form-section>

        <x-agro.form-section title="Detalle">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <flux:field class="md:col-span-2">
                    <flux:label>Descripci&oacute;n</flux:label>
                    <flux:textarea wire:model="description" rows="3" />
                    <flux:error name="description" />
                </flux:field>

                <flux:field>
                    <flux:label>Superficie</flux:label>
                    <flux:input wire:model="area_m2" type="number" step="0.01" min="0" />
                    <flux:description>m&sup2;</flux:description>
                    <flux:error name="area_m2" />
                </flux:field>

                <flux:field>
                    <flux:label>Especies</flux:label>
                    <flux:textarea wire:model="species" rows="2" placeholder="Ej: Festuca, Tr&eacute;bol, Veza..." />
                    <flux:error name="species" />
                </flux:field>

            </div>
        </x-agro.form-section>

        <x-agro.form-section title="Notas">
            <flux:field>
                <flux:textarea wire:model="notes" rows="3" />
                <flux:error name="notes" />
            </flux:field>
        </x-agro.form-section>

        <x-agro.form-actions
            :cancel-url="roleRoute('viticulturist.biodiversity-records.index')"
            submit-label="Guardar Cambios"
        />
    </form>
</x-agro.form-card>
