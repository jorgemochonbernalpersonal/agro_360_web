<div>
<x-agro.form-card
    :title="'Editar Plantacion en ' . $planting->plot->name"
    description="Actualiza los datos de una plantacion de variedad de uva en esta parcela"
    :back-url="route('plots.plantings.index')"
>
    <form wire:submit.prevent="update" class="space-y-8">
        <x-agro.form-section title="Datos de la Plantacion">
            <!-- Nombre de la plantacion -->
            <div class="mb-6">
                <flux:field>
                    <flux:label for="name">Nombre de la plantacion (Opcional)</flux:label>
                    <flux:input wire:model="name" type="text" id="name"
                        placeholder="Ej: Parcela Norte - Tempranillo, Bloque A, etc." />
                    <flux:error name="name" />
                </flux:field>
                <p class="mt-1 text-xs text-zinc-500">Util para diferenciar multiples plantaciones en la misma parcela</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Variedad de uva -->
                <flux:field>
                    <flux:label for="grape_variety_id">Variedad de uva *</flux:label>
                    <flux:select wire:model="grape_variety_id" id="grape_variety_id">
                        <option value="">Seleccionar...</option>
                        @foreach ($grapeVarieties as $variety)
                            <option value="{{ $variety->id }}">
                                {{ $variety->name }} @if($variety->code) ({{ $variety->code }}) @endif
                            </option>
                        @endforeach
                    </flux:select>
                    <flux:error name="grape_variety_id" />
                </flux:field>

                <!-- Superficie plantada -->
                <flux:field>
                    <flux:label for="area_planted">Superficie plantada (ha) *</flux:label>
                    <flux:input wire:model.live="area_planted" type="number" step="0.001" id="area_planted" required />
                    <flux:error name="area_planted" />
                </flux:field>
            </div>

            @if(!auth()->user()->isWinery())
            <!-- Limite de cosecha -->
            <div class="mt-6">
                <flux:field>
                    <flux:label for="harvest_limit_kg">Limite maximo de cosecha (kg)</flux:label>
                    <flux:input wire:model="harvest_limit_kg" type="number" step="0.001" id="harvest_limit_kg" placeholder="Ej: 10000" />
                    <flux:error name="harvest_limit_kg" />
                    @if($planting->plot->maximum_yield_kg_ha)
                        <flux:description>Auto-calculado desde {{ number_format($planting->plot->maximum_yield_kg_ha, 0) }} kg/ha de la parcela. Puedes ajustarlo manualmente.</flux:description>
                    @else
                        <flux:description>Deja vacio si no hay limite establecido</flux:description>
                    @endif
                </flux:field>
            </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <!-- Ano plantacion -->
                <flux:field>
                    <flux:label for="planting_year">Ano de plantacion</flux:label>
                    <flux:input wire:model="planting_year" type="number" id="planting_year" />
                    <flux:error name="planting_year" />
                </flux:field>

                <!-- Riego -->
                <div class="flex items-center mt-6 md:mt-0">
                    <flux:checkbox wire:model.live="irrigated" id="irrigated" label="Con riego" />
                    @error('irrigated')
                        <flux:error name="irrigated" />
                    @enderror
                </div>
            </div>
        </x-agro.form-section>

        <x-agro.form-section title="Densidad y Marco de Plantacion">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <flux:field>
                    <flux:label for="vine_count">Numero de cepas</flux:label>
                    <flux:input wire:model="vine_count" type="number" id="vine_count" />
                    <flux:error name="vine_count" />
                </flux:field>
                <flux:field>
                    <flux:label for="density">Densidad (cepas/ha)</flux:label>
                    <flux:input wire:model="density" type="number" id="density" />
                    <flux:error name="density" />
                </flux:field>
                <flux:field>
                    <flux:label for="row_spacing">Distancia entre filas (m)</flux:label>
                    <flux:input wire:model="row_spacing" type="number" step="0.01" id="row_spacing" />
                    <flux:error name="row_spacing" />
                </flux:field>
                <flux:field>
                    <flux:label for="vine_spacing">Distancia entre cepas (m)</flux:label>
                    <flux:input wire:model="vine_spacing" type="number" step="0.01" id="vine_spacing" />
                    <flux:error name="vine_spacing" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-section title="Caracteristicas Tecnicas">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:field>
                    <flux:label for="rootstock">Portainjerto</flux:label>
                    <flux:input wire:model="rootstock" type="text" id="rootstock" />
                    <flux:error name="rootstock" />
                </flux:field>
                <flux:field>
                    <flux:label for="training_system_id">Sistema de conduccion</flux:label>
                    <flux:select wire:model="training_system_id" id="training_system_id">
                        <option value="">Seleccionar...</option>
                        @foreach($trainingSystems as $system)
                            <option value="{{ $system->id }}">{{ $system->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="training_system_id" />
                </flux:field>
            </div>

            <div class="mt-6">
                <flux:field>
                    <flux:label for="status">Estado de la plantacion *</flux:label>
                    <flux:select wire:model="status" id="status" required>
                        <option value="active">Activa</option>
                        <option value="removed">Arrancada</option>
                        <option value="experimental">Experimental</option>
                        <option value="replanting">Replantacion</option>
                    </flux:select>
                    <flux:error name="status" />
                </flux:field>
            </div>

            <div class="mt-6">
                <flux:field>
                    <flux:label for="notes">Observaciones</flux:label>
                    <flux:textarea wire:model="notes" id="notes" rows="3"
                        placeholder="Notas sobre la plantacion, clones, etc." />
                    <flux:error name="notes" />
                </flux:field>
            </div>
        </x-agro.form-section>

        {{-- Autorizacion de Plantacion PAC --}}
        @if(!auth()->user()->isWinery())
        <x-agro.form-section title="Autorizacion de Plantacion PAC">
            <flux:callout variant="info" icon="information-circle">
                <flux:callout.heading>Autorizacion de Plantacion</flux:callout.heading>
                <flux:callout.text>
                    Desde 2016, todas las nuevas plantaciones de vinedo requieren autorizacion administrativa.
                    Registra aqui los datos de la autorizacion concedida.
                </flux:callout.text>
            </flux:callout>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <flux:field>
                    <flux:label for="planting_authorization">Numero de Autorizacion</flux:label>
                    <flux:input wire:model="planting_authorization" type="text" id="planting_authorization"
                        placeholder="Ej: PAC-2024-001" />
                    <flux:error name="planting_authorization" />
                    <flux:description>Numero de expediente de la autorizacion</flux:description>
                </flux:field>

                <flux:field>
                    <flux:label for="authorization_date">Fecha de Autorizacion</flux:label>
                    <flux:input wire:model="authorization_date" type="date" id="authorization_date" />
                    <flux:error name="authorization_date" />
                </flux:field>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <flux:field>
                    <flux:label for="right_type">Tipo de Derecho</flux:label>
                    <flux:select wire:model="right_type" id="right_type">
                        <option value="">Seleccionar...</option>
                        <option value="nueva">Nueva Plantacion</option>
                        <option value="replantacion">Replantacion</option>
                        <option value="conversion">Conversion</option>
                        <option value="transferencia">Transferencia</option>
                    </flux:select>
                    <flux:error name="right_type" />
                    <flux:description>Tipo de derecho de plantacion utilizado</flux:description>
                </flux:field>

                <flux:field>
                    <flux:label for="uprooting_date">Fecha de Arranque (si aplica)</flux:label>
                    <flux:input wire:model="uprooting_date" type="date" id="uprooting_date" />
                    <flux:error name="uprooting_date" />
                    <flux:description>Solo para replantaciones</flux:description>
                </flux:field>
            </div>

            <div class="mt-6">
                <flux:field>
                    <flux:label for="designation_of_origin">Denominacion de Origen</flux:label>
                    <flux:input wire:model="designation_of_origin" type="text" id="designation_of_origin"
                        placeholder="Ej: DO Rioja, DOCa Priorat, IGP Castilla" />
                    <flux:error name="designation_of_origin" />
                    <flux:description>Si la plantacion esta amparada por una DO/DOCa/IGP</flux:description>
                </flux:field>
            </div>
        </x-agro.form-section>
        @endif

        <x-agro.form-actions :cancel-url="route('plots.plantings.index')" submit-label="Actualizar Plantacion" />
    </form>
</x-agro.form-card>
</div>
