<x-agro.form-card
    title="Registrar Riego"
    description="Registra un nuevo riego en el cuaderno digital"
    :back-url="route('viticulturist.digital-notebook.irrigation.index')"
>
    <form wire:submit="save" class="space-y-8" data-cy="irrigation-form">
        <x-agro.form-section title="Información Básica">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <flux:label for="plot_id" required>Parcela</flux:label>
                        <flux:select wire:model.live="plot_id" id="plot_id" data-cy="plot-select" :error="$errors->first('plot_id')" required>
                            <option value="">Selecciona una parcela</option>
                            @foreach($plots as $plot)
                                <option value="{{ $plot->id }}">{{ $plot->name }}</option>
                            @endforeach
                        </flux:select>
                    </div>
                    @if($plot_id)
                        <div>
                            <flux:label for="plot_planting_id" :required="count($availablePlantings) > 0">
                                Plantación
                                @if(count($availablePlantings) > 0)
                                    <span class="text-red-500">*</span>
                                @else
                                    <span class="text-zinc-500 text-sm">(Opcional)</span>
                                @endif
                            </flux:label>
                            <flux:select wire:model="plot_planting_id" id="plot_planting_id" data-cy="plot-planting-select" :error="$errors->first('plot_planting_id')" :required="count($availablePlantings) > 0">
                                <option value="">-- Selecciona una plantación --</option>
                                @foreach($availablePlantings as $planting)
                                    <option value="{{ $planting->id }}">
                                        {{ $planting->name }}
                                        @if($planting->grapeVariety)
                                            - {{ $planting->grapeVariety->name }}
                                        @endif
                                    </option>
                                @endforeach
                            </flux:select>
                        </div>
                    @endif
                    <div>
                        <flux:label for="activity_date" required>Fecha</flux:label>
                        <flux:input wire:model="activity_date" type="date" id="activity_date" data-cy="activity-date-input" :error="$errors->first('activity_date')" required />
                    </div>
                    <div>
                        <flux:label for="phenological_stage">Estadio Fenológico</flux:label>
                        <flux:select wire:model="phenological_stage" id="phenological_stage" data-cy="phenological-stage-select" :error="$errors->first('phenological_stage')">
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
                        <p class="text-xs text-zinc-500 mt-1">Recomendado para trazabilidad PAC</p>
                    </div>
                </div>
        </x-agro.form-section>

        <x-agro.form-section title="Información del Riego">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <flux:label for="water_volume">Volumen de Agua (L)</flux:label>
                        <flux:input wire:model="water_volume" type="number" step="0.001" id="water_volume" data-cy="water-volume-input" placeholder="0.000" :error="$errors->first('water_volume')" />
                    </div>
                    <div>
                        <flux:label for="irrigation_method">Método de Riego</flux:label>
                        <flux:select wire:model="irrigation_method" id="irrigation_method" data-cy="irrigation-method-select" :error="$errors->first('irrigation_method')">
                            <option value="">Selecciona un método</option>
                            <option value="goteo">Goteo</option>
                            <option value="aspersión">Aspersión</option>
                            <option value="superficie">Superficie</option>
                            <option value="subterráneo">Subterráneo</option>
                            <option value="otro">Otro</option>
                        </flux:select>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                    <div>
                        <flux:label for="duration_minutes">Duración (minutos)</flux:label>
                        <flux:input wire:model="duration_minutes" type="number" id="duration_minutes" placeholder="0" :error="$errors->first('duration_minutes')" />
                    </div>
                    <div>
                        <flux:label for="soil_moisture_before">Humedad del Suelo Antes (%)</flux:label>
                        <flux:input wire:model="soil_moisture_before" type="number" step="0.1" min="0" max="100" id="soil_moisture_before" placeholder="0.0" :error="$errors->first('soil_moisture_before')" />
                    </div>
                    <div>
                        <flux:label for="soil_moisture_after">Humedad del Suelo Después (%)</flux:label>
                        <flux:input wire:model="soil_moisture_after" type="number" step="0.1" min="0" max="100" id="soil_moisture_after" placeholder="0.0" :error="$errors->first('soil_moisture_after')" />
                    </div>
                </div>
        </x-agro.form-section>

        {{-- Sección PAC Obligatoria --}}
        <x-agro.form-section title="Cumplimiento PAC (Obligatorio)">
            <div class="space-y-6">
                {{-- Info box PAC --}}
                <div class="p-4 bg-amber-50 border-l-4 border-amber-500 rounded-r-lg">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <div class="flex-1">
                            <h4 class="text-sm font-semibold text-amber-900">Información PAC</h4>
                            <p class="text-sm text-amber-800 mt-1">
                                Es obligatorio identificar el origen del agua y la concesión para cumplir con la condicionalidad reforzada de la PAC.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Origen del Agua --}}
                    <div>
                        <flux:label for="water_source">Origen del Agua</flux:label>
                        <flux:select
                            wire:model="water_source"
                            id="water_source"
                            data-cy="water-source-select"
                            :error="$errors->first('water_source')"
                        >
                            <option value="">Selecciona el origen</option>
                            <option value="Pozo legalizado">Pozo legalizado</option>
                            <option value="Comunidad de regantes">Comunidad de regantes</option>
                            <option value="Embalse propio">Embalse propio</option>
                            <option value="Cauce público (río/arroyo)">Cauce público (río/arroyo)</option>
                            <option value="Aguas regeneradas">Aguas regeneradas</option>
                            <option value="Otro">Otro</option>
                        </flux:select>
                    </div>

                    {{-- Nº Concesión --}}
                    <div>
                        <flux:label for="water_concession">Nº Concesión / Autorización</flux:label>
                        <flux:input
                            wire:model="water_concession"
                            type="text"
                            id="water_concession"
                            placeholder="Ej: 2023/CONF/1234"
                            :error="$errors->first('water_concession')"
                        />
                        <p class="mt-1 text-xs text-zinc-500">
                            Número de expediente de la Confederación Hidrográfica
                        </p>
                    </div>
                </div>

                {{-- Caudal --}}
                <div class="md:w-1/2">
                    <flux:label for="flow_rate">Caudal de Riego (L/h)</flux:label>
                    <flux:input
                        wire:model="flow_rate"
                        type="number"
                        step="0.01"
                        id="flow_rate"
                        placeholder="Ej: 2000.00"
                        min="0"
                        :error="$errors->first('flow_rate')"
                    />
                </div>
            </div>
        </x-agro.form-section>

        <x-agro.form-section title="Información Adicional">
                <!-- ¿Quién realizó el trabajo? -->
                <div class="mb-6">
                    <flux:label class="mb-3 block font-semibold text-zinc-700">¿Quién realizó el trabajo?</flux:label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Opción: Equipo completo -->
                        <div class="border-2 rounded-lg p-4 transition-all {{ $workType === 'crew' ? 'border-agro-500 bg-agro-50' : 'border-zinc-200 hover:border-zinc-300' }}">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input
                                    type="radio"
                                    wire:model.live="workType"
                                    value="crew"
                                    data-cy="work-type-crew-radio"
                                    class="w-5 h-5 text-agro-500 focus:ring-agro-500"
                                />
                                <div class="flex-1">
                                    <span class="font-semibold text-zinc-900">Equipo completo</span>
                                    <p class="text-sm text-zinc-500 mt-1">Todo el equipo trabajó en esta actividad</p>
                                </div>
                            </label>
                            @if($workType === 'crew')
                                <div class="mt-4">
                                    <flux:label for="crew_id" class="text-sm">Selecciona el equipo</flux:label>
                                    <flux:select
                                        wire:model="crew_id"
                                        id="crew_id"
                                        class="mt-1"
                                        :error="$errors->first('crew_id')"
                                    >
                                        <option value="">Selecciona un equipo</option>
                                        @foreach($crews as $crew)
                                            <option value="{{ $crew->id }}">{{ $crew->name }}</option>
                                        @endforeach
                                    </flux:select>
                                </div>
                            @endif
                        </div>

                        <!-- Opción: Viticultor individual -->
                        <div class="border-2 rounded-lg p-4 transition-all {{ $workType === 'individual' ? 'border-agro-500 bg-agro-50' : 'border-zinc-200 hover:border-zinc-300' }}">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input
                                    type="radio"
                                    wire:model.live="workType"
                                    value="individual"
                                    data-cy="work-type-individual-radio"
                                    class="w-5 h-5 text-agro-500 focus:ring-agro-500"
                                />
                                <div class="flex-1">
                                    <span class="font-semibold text-zinc-900">Viticultor individual</span>
                                    <p class="text-sm text-zinc-500 mt-1">Un viticultor específico realizó el trabajo</p>
                                </div>
                            </label>
                            @if($workType === 'individual')
                                <div class="mt-4">
                                    <flux:label for="crew_member_id" class="text-sm">Selecciona el viticultor</flux:label>
                                    <flux:select
                                        wire:model="crew_member_id"
                                        id="crew_member_id"
                                        class="mt-1"
                                        :error="$errors->first('crew_member_id')"
                                    >
                                        <option value="">Selecciona un viticultor</option>
                                        @if(isset($allViticulturists))
                                            @foreach($allViticulturists as $viticulturist)
                                                <option value="{{ $viticulturist->id }}">
                                                    {{ $viticulturist->name }}@if($viticulturist->id === auth()->id()) (Yo)@endif
                                                </option>
                                            @endforeach
                                        @endif
                                    </flux:select>
                                </div>
                            @endif
                        </div>
                    </div>
                    @error('workType')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Maquinaria -->
                <div>
                    <flux:label for="machinery_id">Maquinaria</flux:label>
                    <flux:select wire:model="machinery_id" id="machinery_id" data-cy="machinery-select" :error="$errors->first('machinery_id')">
                        <option value="">Sin maquinaria asignada</option>
                        @foreach($machinery as $item)
                            <option value="{{ $item->id }}">{{ $item->name }} ({{ $item->type }})</option>
                        @endforeach
                    </flux:select>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <div>
                        <flux:label for="weather_conditions">Condiciones Meteorológicas</flux:label>
                        <flux:input wire:model="weather_conditions" type="text" id="weather_conditions" placeholder="Ej: Soleado, nublado" :error="$errors->first('weather_conditions')" />
                    </div>
                    <div>
                        <flux:label for="temperature">Temperatura (°C)</flux:label>
                        <flux:input wire:model="temperature" type="number" step="0.1" id="temperature" placeholder="20.0" :error="$errors->first('temperature')" />
                    </div>
                </div>
                <div class="mt-6">
                    <flux:label for="notes">Notas</flux:label>
                    <flux:textarea wire:model="notes" id="notes" rows="4" placeholder="Observaciones, comentarios, etc." :error="$errors->first('notes')" />
                </div>
        </x-agro.form-section>

        <x-agro.form-actions
            :cancel-url="route('viticulturist.digital-notebook.irrigation.index')"
            submit-label="Registrar Riego"
        />
    </form>
</x-agro.form-card>
