<x-agro.form-card
    title="Editar Fertilización"
    description="Modifica los datos de la fertilización"
    :back-url="roleRoute('viticulturist.digital-notebook.fertilization.index')"
>
    <form wire:submit="update" class="space-y-8" data-cy="fertilization-form">

        <x-agro.form-section title="Información Básica">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <flux:field>
                    <flux:label required>Parcela</flux:label>
                    <flux:select wire:model.live="plot_id" data-cy="plot-select">
                        <option value="">Selecciona una parcela</option>
                        @foreach($plots as $plot)
                            <option value="{{ $plot->id }}">{{ $plot->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="plot_id" />
                </flux:field>

                @if($plot_id)
                    <flux:field>
                        <flux:label :required="count($availablePlantings) > 0">
                            Plantación
                            @if(!count($availablePlantings))
                                <span class="text-zinc-400 text-xs font-normal">(Sin plantaciones activas)</span>
                            @endif
                        </flux:label>
                        <flux:select wire:model="plot_planting_id" data-cy="plot-planting-select">
                            <option value="">-- Selecciona una plantación --</option>
                            @foreach($availablePlantings as $planting)
                                <option value="{{ $planting->id }}">
                                    {{ $planting->name }}
                                    @if($planting->grapeVariety) — {{ $planting->grapeVariety->name }} @endif
                                </option>
                            @endforeach
                        </flux:select>
                        <flux:error name="plot_planting_id" />
                    </flux:field>
                @endif

                <flux:field>
                    <flux:label required>Fecha</flux:label>
                    <flux:input wire:model="activity_date" type="date" data-cy="activity-date-input" />
                    <flux:error name="activity_date" />
                </flux:field>

            </div>
        </x-agro.form-section>

        <x-agro.form-section title="Información del Fertilizante">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:field>
                    <flux:label required>Tipo de Fertilizante</flux:label>
                    <flux:input wire:model="fertilizer_type" type="text" data-cy="fertilizer-type-input" placeholder="Ej: Orgánico, Mineral, etc." />
                    <flux:error name="fertilizer_type" />
                </flux:field>
                <flux:field>
                    <flux:label>Nombre del Fertilizante</flux:label>
                    <flux:input wire:model="fertilizer_name" type="text" data-cy="fertilizer-name-input" placeholder="Nombre comercial" />
                    <flux:error name="fertilizer_name" />
                </flux:field>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                <flux:field>
                    <flux:label required>Cantidad</flux:label>
                    <flux:input wire:model="quantity" id="quantity" type="number" step="0.001" data-cy="quantity-input" placeholder="0.000" />
                    <flux:description>kg</flux:description>
                    <flux:error name="quantity" />
                </flux:field>
                <flux:field>
                    <flux:label>Relación NPK</flux:label>
                    <flux:input wire:model="npk_ratio" id="npk_ratio" type="text" data-cy="npk-ratio-input" placeholder="Ej: 10-10-10" />
                    <flux:error name="npk_ratio" />
                </flux:field>
                <flux:field>
                    <flux:label required>Área Aplicada</flux:label>
                    <flux:input wire:model="area_applied" id="area_applied" type="number" step="0.001" data-cy="area-applied-input" placeholder="0.000" />
                    <flux:description>ha</flux:description>
                    <flux:error name="area_applied" />
                </flux:field>
            </div>
            <div class="mt-6">
                <flux:field>
                    <flux:label>Método de Aplicación</flux:label>
                    <flux:select wire:model="application_method" data-cy="application-method-select">
                        <option value="">Selecciona un método</option>
                        <option value="aplicación al suelo">Aplicación al Suelo</option>
                        <option value="fertirrigación">Fertirrigación</option>
                        <option value="aplicación foliar">Aplicación Foliar</option>
                        <option value="otro">Otro</option>
                    </flux:select>
                    <flux:error name="application_method" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-section title="Nutrición Sostenible (PAC)">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <flux:field>
                    <flux:label>Unidades N</flux:label>
                    <flux:input wire:model="nitrogen_uf" id="nitrogen_uf" type="number" step="0.001" placeholder="0.000" />
                    <flux:description>kg/ha — Nitrógeno</flux:description>
                    <flux:error name="nitrogen_uf" />
                </flux:field>
                <flux:field>
                    <flux:label>Unidades P₂O₅</flux:label>
                    <flux:input wire:model="phosphorus_uf" id="phosphorus_uf" type="number" step="0.001" placeholder="0.000" />
                    <flux:description>kg/ha — Fósforo</flux:description>
                    <flux:error name="phosphorus_uf" />
                </flux:field>
                <flux:field>
                    <flux:label>Unidades K₂O</flux:label>
                    <flux:input wire:model="potassium_uf" id="potassium_uf" type="number" step="0.001" placeholder="0.000" />
                    <flux:description>kg/ha — Potasio</flux:description>
                    <flux:error name="potassium_uf" />
                </flux:field>
            </div>

            <div class="mt-6 border-t pt-6">
                <h4 class="text-sm font-medium text-zinc-900 mb-4 flex items-center gap-2">
                    <flux:icon icon="globe-alt" class="size-4 text-amber-600" />
                    Fertilizantes Orgánicos / Estiércoles
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <flux:field>
                        <flux:label>Tipo de Estiércol</flux:label>
                        <flux:input wire:model="manure_type" id="manure_type" type="text" placeholder="Ej: Bovino, Porcino..." />
                        <flux:error name="manure_type" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Fecha de Enterrado</flux:label>
                        <flux:input wire:model="burial_date" id="burial_date" type="date" />
                        <flux:error name="burial_date" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Método Reducción Emisiones</flux:label>
                        <flux:select wire:model="emission_reduction_method" id="emission_reduction_method">
                            <option value="">Selecciona un método</option>
                            <option value="inyección">Inyección directa</option>
                            <option value="platos">Platos deflectores</option>
                            <option value="tubos">Tubos colgantes</option>
                            <option value="enterrado_inmediato">Enterrado inmediato (&lt; 4h)</option>
                            <option value="otro">Otro</option>
                        </flux:select>
                        <flux:error name="emission_reduction_method" />
                    </flux:field>
                </div>
            </div>
        </x-agro.form-section>

        <x-agro.form-section title="Información Adicional">
            <div class="mb-6">
                <flux:label class="mb-3 block font-semibold text-zinc-700">¿Quién realizó el trabajo?</flux:label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="border-2 rounded-lg p-4 transition-all {{ $workType === 'crew' ? 'border-agro-500 bg-agro-50' : 'border-zinc-200 hover:border-zinc-300' }}">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="radio" wire:model.live="workType" value="crew" data-cy="work-type-crew-radio" class="w-5 h-5 text-agro-500 focus:ring-agro-500" />
                            <div class="flex-1">
                                <span class="font-semibold text-zinc-900">Equipo completo</span>
                                <p class="text-sm text-zinc-500 mt-1">Todo el equipo trabajó en esta actividad</p>
                            </div>
                        </label>
                        @if($workType === 'crew')
                            <div class="mt-4">
                                <flux:field>
                                    <flux:label>Selecciona el equipo</flux:label>
                                    <flux:select wire:model="crew_id" id="crew_id" data-cy="crew-select">
                                        <option value="">Selecciona un equipo</option>
                                        @foreach($crews as $crew)
                                            <option value="{{ $crew->id }}">{{ $crew->name }}</option>
                                        @endforeach
                                    </flux:select>
                                    <flux:error name="crew_id" />
                                </flux:field>
                            </div>
                        @endif
                    </div>
                    <div class="border-2 rounded-lg p-4 transition-all {{ $workType === 'individual' ? 'border-agro-500 bg-agro-50' : 'border-zinc-200 hover:border-zinc-300' }}">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="radio" wire:model.live="workType" value="individual" data-cy="work-type-individual-radio" class="w-5 h-5 text-agro-500 focus:ring-agro-500" />
                            <div class="flex-1">
                                <span class="font-semibold text-zinc-900">Viticultor individual</span>
                                <p class="text-sm text-zinc-500 mt-1">Un viticultor específico realizó el trabajo</p>
                            </div>
                        </label>
                        @if($workType === 'individual')
                            <div class="mt-4">
                                <flux:field>
                                    <flux:label>Selecciona el viticultor</flux:label>
                                    <flux:select wire:model="crew_member_id" id="crew_member_id" data-cy="crew-member-select">
                                        <option value="">Selecciona un viticultor</option>
                                        @foreach($allViticulturists as $viticulturist)
                                            <option value="{{ $viticulturist->id }}">
                                                {{ $viticulturist->name }}@if($viticulturist->id === auth()->id()) (Yo)@endif
                                            </option>
                                        @endforeach
                                    </flux:select>
                                    <flux:error name="crew_member_id" />
                                </flux:field>
                            </div>
                        @endif
                    </div>
                </div>
                @error('workType')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <flux:field>
                <flux:label>Maquinaria</flux:label>
                <flux:select wire:model="machinery_id" data-cy="machinery-select">
                    <option value="">Sin maquinaria asignada</option>
                    @foreach($machinery as $item)
                        <option value="{{ $item->id }}">{{ $item->name }} ({{ $item->type }})</option>
                    @endforeach
                </flux:select>
                <flux:error name="machinery_id" />
            </flux:field>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <flux:field>
                    <flux:label>Condiciones Meteorológicas</flux:label>
                    <flux:input wire:model="weather_conditions" type="text" data-cy="weather-conditions-input" placeholder="Ej: Soleado, nublado" />
                    <flux:error name="weather_conditions" />
                </flux:field>
                <flux:field>
                    <flux:label>Temperatura</flux:label>
                    <flux:input wire:model="temperature" type="number" step="0.1" data-cy="temperature-input" placeholder="20.0" />
                    <flux:description>°C</flux:description>
                    <flux:error name="temperature" />
                </flux:field>
            </div>

            <div class="mt-6">
                <flux:field>
                    <flux:label>Notas</flux:label>
                    <flux:textarea wire:model="notes" data-cy="notes-textarea" rows="4" placeholder="Observaciones, comentarios, etc." />
                    <flux:error name="notes" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-actions
            :cancel-url="roleRoute('viticulturist.digital-notebook.fertilization.index')"
            submit-label="Actualizar Fertilización"
        />
    </form>
</x-agro.form-card>
