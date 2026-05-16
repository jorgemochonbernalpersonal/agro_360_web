<x-agro.form-card
    title="Registrar Riego"
    description="Registra un nuevo riego en el cuaderno digital"
    :back-url="roleRoute('viticulturist.digital-notebook.irrigation.index')"
>
    <form wire:submit="save" class="space-y-8" data-cy="irrigation-form">

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

                <flux:field>
                    <flux:label>Estadio Fenológico</flux:label>
                    <flux:select wire:model="phenological_stage" data-cy="phenological-stage-select">
                        <option value="">Selecciona un estadio</option>
                        <option value="Brotación">Brotación</option>
                        <option value="Desarrollo vegetativo">Desarrollo vegetativo</option>
                        <option value="Floración">Floración</option>
                        <option value="Cuajado">Cuajado</option>
                        <option value="Envero">Envero</option>
                        <option value="Maduración">Maduración</option>
                        <option value="Vendimia">Vendimia</option>
                        <option value="Caída de hoja">Caída de hoja</option>
                        <option value="Reposo invernal">Reposo invernal</option>
                    </flux:select>
                    <flux:description>Recomendado para trazabilidad PAC</flux:description>
                    <flux:error name="phenological_stage" />
                </flux:field>

            </div>
        </x-agro.form-section>

        <x-agro.form-section title="Información del Riego">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <flux:field>
                    <flux:label required>Volumen de Agua</flux:label>
                    <div class="flex gap-2">
                        <flux:input wire:model="water_volume" type="number" step="0.001" data-cy="water-volume-input" placeholder="0.000" class="flex-1" />
                        <flux:select wire:model="water_volume_unit" data-cy="water-volume-unit-select" class="w-20">
                            <option value="L">L</option>
                            <option value="m3">m³</option>
                        </flux:select>
                    </div>
                    <flux:error name="water_volume" />
                </flux:field>

                <flux:field>
                    <flux:label>Método de Riego</flux:label>
                    <flux:select wire:model="irrigation_method" data-cy="irrigation-method-select">
                        <option value="">Selecciona un método</option>
                        <option value="goteo">Goteo</option>
                        <option value="aspersión">Aspersión</option>
                        <option value="superficie">Superficie</option>
                        <option value="subterráneo">Subterráneo</option>
                        <option value="otro">Otro</option>
                    </flux:select>
                    <flux:error name="irrigation_method" />
                </flux:field>

            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                <flux:field>
                    <flux:label>Duración</flux:label>
                    <flux:input wire:model="duration_minutes" type="number" placeholder="0" />
                    <flux:description>minutos</flux:description>
                    <flux:error name="duration_minutes" />
                </flux:field>
                <flux:field>
                    <flux:label>Humedad del Suelo Antes</flux:label>
                    <flux:input wire:model="soil_moisture_before" type="number" step="0.1" min="0" max="100" placeholder="0.0" />
                    <flux:description>%</flux:description>
                    <flux:error name="soil_moisture_before" />
                </flux:field>
                <flux:field>
                    <flux:label>Humedad del Suelo Después</flux:label>
                    <flux:input wire:model="soil_moisture_after" type="number" step="0.1" min="0" max="100" placeholder="0.0" />
                    <flux:description>%</flux:description>
                    <flux:error name="soil_moisture_after" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-section title="Cumplimiento PAC (Obligatorio)">
            <div class="space-y-6">
                <div class="p-4 bg-amber-50 border-l-4 border-amber-500 rounded-r-lg">
                    <div class="flex items-start gap-3">
                        <flux:icon icon="information-circle" class="size-5 text-amber-600 shrink-0 mt-0.5" />
                        <div class="flex-1">
                            <h4 class="text-sm font-semibold text-amber-900">Información PAC</h4>
                            <p class="text-sm text-amber-800 mt-1">
                                Es obligatorio identificar el origen del agua y la concesión para cumplir con la condicionalidad reforzada de la PAC.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:field>
                        <flux:label required>Origen del Agua</flux:label>
                        <flux:select wire:model="water_source" data-cy="water-source-select">
                            <option value="">Selecciona el origen</option>
                            <option value="Pozo legalizado">Pozo legalizado</option>
                            <option value="Comunidad de regantes">Comunidad de regantes</option>
                            <option value="Embalse propio">Embalse propio</option>
                            <option value="Cauce público (río/arroyo)">Cauce público (río/arroyo)</option>
                            <option value="Aguas regeneradas">Aguas regeneradas</option>
                            <option value="Otro">Otro</option>
                        </flux:select>
                        <flux:error name="water_source" />
                    </flux:field>

                    <flux:field>
                        <flux:label required>Nº Concesión / Autorización</flux:label>
                        <flux:input wire:model="water_concession" type="text" id="water_concession" placeholder="Ej: 2023/CONF/1234" />
                        <flux:description>Número de expediente de la Confederación Hidrográfica</flux:description>
                        <flux:error name="water_concession" />
                    </flux:field>
                </div>

                <div class="md:w-1/2">
                    <flux:field>
                        <flux:label required>Caudal de Riego</flux:label>
                        <flux:input wire:model="flow_rate" type="number" step="0.01" id="flow_rate" placeholder="Ej: 2000.00" min="0" />
                        <flux:description>L/h</flux:description>
                        <flux:error name="flow_rate" />
                    </flux:field>
                </div>
            </div>
        </x-agro.form-section>

        <x-agro.form-section title="Fertirrigación">
            <div class="space-y-4">
                <flux:checkbox wire:model.live="is_fertirrigation" data-cy="is-fertirrigation-checkbox"
                    label="Este riego incluye fertirrigación (abonado disuelto en el agua de riego)"
                    description="Si se añaden nutrientes al agua de riego, debe registrarse también en el cuaderno de fertilización." />
                @if($is_fertirrigation)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
                        <flux:field>
                            <flux:label>Producto Fertilizante</flux:label>
                            <flux:input wire:model="fertilizer_product" type="text" id="fertilizer_product" data-cy="fertilizer-product-input" placeholder="Ej: Nitrato amónico 33%" />
                            <flux:error name="fertilizer_product" />
                        </flux:field>
                        <flux:field>
                            <flux:label>Dosis</flux:label>
                            <flux:input wire:model="fertilizer_dose_per_ha" type="number" step="0.01" min="0" id="fertilizer_dose_per_ha" data-cy="fertilizer-dose-input" placeholder="Ej: 50.00" />
                            <flux:description>kg o L / ha</flux:description>
                            <flux:error name="fertilizer_dose_per_ha" />
                        </flux:field>
                    </div>
                @endif
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
                                    <flux:select wire:model="crew_id" id="crew_id">
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
                                    <flux:select wire:model="crew_member_id" id="crew_member_id">
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
                    <flux:input wire:model="weather_conditions" type="text" placeholder="Ej: Soleado, nublado" />
                    <flux:error name="weather_conditions" />
                </flux:field>
                <flux:field>
                    <flux:label>Temperatura</flux:label>
                    <flux:input wire:model="temperature" type="number" step="0.1" placeholder="20.0" />
                    <flux:description>°C</flux:description>
                    <flux:error name="temperature" />
                </flux:field>
            </div>

            <div class="mt-6">
                <flux:field>
                    <flux:label>Notas</flux:label>
                    <flux:textarea wire:model="notes" rows="4" placeholder="Observaciones, comentarios, etc." />
                    <flux:error name="notes" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-actions
            :cancel-url="roleRoute('viticulturist.digital-notebook.irrigation.index')"
            submit-label="Registrar Riego"
        />
    </form>
</x-agro.form-card>
