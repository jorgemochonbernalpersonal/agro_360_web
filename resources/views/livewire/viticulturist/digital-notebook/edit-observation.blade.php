<x-agro.form-card
    title="Editar Observación"
    description="Modifica los datos de la observación"
    :back-url="route('viticulturist.digital-notebook.observation.index')"
>
    <form wire:submit="update" class="space-y-8" data-cy="observation-form">
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

                {{-- IPM PAC ──────────────────────────────────────────────── --}}
                <div class="mt-6 border-t pt-6">
                    <h4 class="text-sm font-medium text-zinc-900 mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        Gestión Integrada de Plagas (IPM — PAC)
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <flux:label for="affected_area_percentage">% Superficie Afectada</flux:label>
                            <flux:input wire:model="affected_area_percentage" type="number" step="0.01" min="0" max="100" id="affected_area_percentage" data-cy="affected-area-input" placeholder="Ej: 15.50" :error="$errors->first('affected_area_percentage')" />
                            <p class="mt-1 text-xs text-zinc-500">Porcentaje estimado de la parcela afectada (0–100%)</p>
                        </div>
                        <div>
                            <flux:label for="follow_up_date">Fecha de Revisión</flux:label>
                            <flux:input wire:model="follow_up_date" type="date" id="follow_up_date" data-cy="follow-up-date-input" :error="$errors->first('follow_up_date')" />
                            <p class="mt-1 text-xs text-zinc-500">Cuándo volver a inspeccionar esta parcela</p>
                        </div>
                    </div>
                    <div class="mt-4 flex items-start gap-3">
                        <input
                            type="checkbox"
                            wire:model="threshold_exceeded"
                            id="threshold_exceeded"
                            data-cy="threshold-exceeded-checkbox"
                            class="mt-1 w-4 h-4 rounded border-zinc-300 text-agro-600 focus:ring-agro-500"
                        />
                        <label for="threshold_exceeded" class="text-sm font-medium text-zinc-700 cursor-pointer">
                            Umbral de daño económico superado
                            <p class="text-xs text-zinc-500 font-normal mt-0.5">Si se supera el umbral, la intervención fitosanitaria queda justificada en el cuaderno PAC.</p>
                        </label>
                    </div>
                    @error('affected_area_percentage')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
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
