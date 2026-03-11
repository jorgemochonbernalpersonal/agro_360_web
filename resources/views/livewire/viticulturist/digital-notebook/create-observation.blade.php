<x-agro.form-card
    title="Registrar Observación"
    description="Registra una nueva observación en el cuaderno digital"
    :back-url="route('viticulturist.digital-notebook.observation.index')"
>
    <form wire:submit="save" class="space-y-8" data-cy="observation-form">
        @if($selectedPest)
            <div class="mb-6 p-4 bg-blue-50 border-l-4 border-blue-500 rounded-r-lg shadow-sm">
                <div class="flex items-start gap-4">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <span class="text-2xl">{{ $selectedPest->icon }}</span>
                    </div>
                    <div class="flex-1">
                        <h4 class="text-lg font-bold text-blue-900 leading-tight">Observación para: {{ $selectedPest->name }}</h4>
                        @if($selectedPest->scientific_name)
                            <p class="text-sm text-blue-700 italic mt-0.5">{{ $selectedPest->scientific_name }}</p>
                        @endif
                        <p class="text-sm text-blue-800 mt-2">
                            Vinculando automáticamente esta observación a la ficha de la plaga/enfermedad.
                        </p>
                    </div>
                    <button type="button" wire:click="$set('pest_id', '')" class="text-blue-400 hover:text-blue-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        @endif

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

                    {{-- Estadio Fenológico --}}
                    <div>
                        <flux:label for="phenological_stage" required>Estadio Fenológico</flux:label>
                        <flux:select
                            wire:model="phenological_stage"
                            id="phenological_stage"
                            data-cy="phenological-stage-select"
                            :error="$errors->first('phenological_stage')"
                            required
                        >
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

        <x-agro.form-section title="Detalles de la Observación">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <flux:label for="observation_type">Tipo de Observación</flux:label>
                        <flux:select wire:model="observation_type" id="observation_type" data-cy="observation-type-select" :error="$errors->first('observation_type')">
                            <option value="">Selecciona un tipo</option>
                            <option value="plaga">Plaga</option>
                            <option value="enfermedad">Enfermedad</option>
                            <option value="fenología">Fenología</option>
                            <option value="climatología">Climatología</option>
                            <option value="suelo">Suelo</option>
                            <option value="otro">Otro</option>
                        </flux:select>
                    </div>
                    <div>
                        <flux:label for="severity">Severidad</flux:label>
                        <flux:select wire:model="severity" id="severity" :error="$errors->first('severity')">
                            <option value="">Sin severidad</option>
                            <option value="leve">Leve</option>
                            <option value="moderada">Moderada</option>
                            <option value="grave">Grave</option>
                        </flux:select>
                    </div>
                </div>
                <div class="mt-6">
                    <flux:label for="description" required>Descripción</flux:label>
                    <flux:textarea wire:model="description" id="description" data-cy="description-textarea" rows="6" placeholder="Describe detalladamente la observación..." :error="$errors->first('description')" required />
                </div>
                <div class="mt-6">
                    <flux:label for="action_taken">Acción Tomada</flux:label>
                    <flux:textarea wire:model="action_taken" id="action_taken" rows="4" placeholder="Describe las acciones tomadas o previstas..." :error="$errors->first('action_taken')" />
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
                    <flux:select wire:model="machinery_id" id="machinery_id" :error="$errors->first('machinery_id')">
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
                    <flux:textarea wire:model="notes" id="notes" rows="4" placeholder="Observaciones adicionales, comentarios, etc." :error="$errors->first('notes')" />
                </div>
        </x-agro.form-section>

        <x-agro.form-actions
            :cancel-url="route('viticulturist.digital-notebook.observation.index')"
            submit-label="Registrar Observación"
        />
    </form>
</x-agro.form-card>
