<x-agro.form-card
    title="Registrar Observación"
    description="Registra una nueva observación en el cuaderno digital"
    :back-url="roleRoute('viticulturist.digital-notebook.observation.index')"
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
                        <flux:icon icon="x-mark" class="size-5" />
                    </button>
                </div>
            </div>
        @endif

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
                    <flux:label required>Estadio Fenológico</flux:label>
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

        <x-agro.form-section title="Detalles de la Observación">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:field>
                    <flux:label>Tipo de Observación</flux:label>
                    <flux:select wire:model="observation_type" data-cy="observation-type-select">
                        <option value="">Selecciona un tipo</option>
                        <option value="plaga">Plaga</option>
                        <option value="enfermedad">Enfermedad</option>
                        <option value="fenología">Fenología</option>
                        <option value="climatología">Climatología</option>
                        <option value="suelo">Suelo</option>
                        <option value="otro">Otro</option>
                    </flux:select>
                    <flux:error name="observation_type" />
                </flux:field>
                <flux:field>
                    <flux:label>Severidad</flux:label>
                    <flux:select wire:model="severity" id="severity">
                        <option value="">Sin severidad</option>
                        <option value="leve">Leve</option>
                        <option value="moderada">Moderada</option>
                        <option value="grave">Grave</option>
                    </flux:select>
                    <flux:error name="severity" />
                </flux:field>
            </div>
            <div class="mt-6">
                <flux:field>
                    <flux:label required>Descripción</flux:label>
                    <flux:textarea wire:model="description" data-cy="description-textarea" rows="6" placeholder="Describe detalladamente la observación..." />
                    <flux:error name="description" />
                </flux:field>
            </div>

            <div class="mt-6 border-t pt-6">
                <h4 class="text-sm font-medium text-zinc-900 mb-4 flex items-center gap-2">
                    <flux:icon icon="shield-check" class="size-4 text-green-600" />
                    Gestión Integrada de Plagas (IPM — PAC)
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:field>
                        <flux:label>% Superficie Afectada</flux:label>
                        <flux:input wire:model="affected_area_percentage" type="number" step="0.01" min="0" max="100" data-cy="affected-area-input" placeholder="Ej: 15.50" />
                        <flux:description>Porcentaje estimado de la parcela afectada (0–100%)</flux:description>
                        <flux:error name="affected_area_percentage" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Fecha de Revisión</flux:label>
                        <flux:input wire:model="follow_up_date" type="date" data-cy="follow-up-date-input" />
                        <flux:description>Cuándo volver a inspeccionar esta parcela</flux:description>
                        <flux:error name="follow_up_date" />
                    </flux:field>
                </div>
                <div class="mt-4">
                    <flux:checkbox wire:model="threshold_exceeded" data-cy="threshold-exceeded-checkbox"
                        label="Umbral de daño económico superado"
                        description="Si se supera el umbral, la intervención fitosanitaria queda justificada en el cuaderno PAC." />
                </div>
            </div>

            <div class="mt-6">
                <flux:field>
                    <flux:label>Acción Tomada</flux:label>
                    <flux:textarea wire:model="action_taken" rows="4" placeholder="Describe las acciones tomadas o previstas..." />
                    <flux:error name="action_taken" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-section title="Información Adicional">
            <div class="mb-6">
                <flux:label class="mb-3 block font-semibold text-zinc-700">¿Quién realizó el trabajo?</flux:label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="border-2 rounded-lg p-4 transition-all {{ $workType === 'crew' ? 'border-agro-500 bg-agro-50' : 'border-zinc-200 hover:border-zinc-300' }}">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="radio" wire:model.live="workType" value="crew" class="w-5 h-5 text-agro-500 focus:ring-agro-500" />
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
                            <input type="radio" wire:model.live="workType" value="individual" class="w-5 h-5 text-agro-500 focus:ring-agro-500" />
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
                <flux:select wire:model="machinery_id">
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
                    <flux:textarea wire:model="notes" rows="4" placeholder="Observaciones adicionales, comentarios, etc." />
                    <flux:error name="notes" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-actions
            :cancel-url="roleRoute('viticulturist.digital-notebook.observation.index')"
            submit-label="Registrar Observación"
        />
    </form>
</x-agro.form-card>
