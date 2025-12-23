@php
    $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>';
@endphp

<x-form-card
    title="Registrar Observación"
    description="Registra una nueva observación en el cuaderno digital"
    :icon="$icon"
    icon-color="from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]"
    :back-url="route('viticulturist.digital-notebook')"
>
    <form wire:submit="save" class="space-y-8" data-cy="observation-form">
        <x-form-section title="Información Básica" color="green">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-label for="plot_id" required>Parcela</x-label>
                        <x-select wire:model.live="plot_id" id="plot_id" data-cy="plot-select" :error="$errors->first('plot_id')" required>
                            <option value="">Selecciona una parcela</option>
                            @foreach($plots as $plot)
                                <option value="{{ $plot->id }}">{{ $plot->name }}</option>
                            @endforeach
                        </x-select>
                    </div>
                    @if($plot_id)
                        <div>
                            <x-label for="plot_planting_id" :required="count($availablePlantings) > 0">
                                Plantación
                                @if(count($availablePlantings) > 0)
                                    <span class="text-red-500">*</span>
                                @else
                                    <span class="text-gray-500 text-sm">(Opcional)</span>
                                @endif
                            </x-label>
                            <x-select wire:model="plot_planting_id" id="plot_planting_id" data-cy="plot-planting-select" :error="$errors->first('plot_planting_id')" :required="count($availablePlantings) > 0">
                                <option value="">-- Selecciona una plantación --</option>
                                @foreach($availablePlantings as $planting)
                                    <option value="{{ $planting->id }}">
                                        {{ $planting->name }}
                                        @if($planting->grapeVariety)
                                            - {{ $planting->grapeVariety->name }}
                                        @endif
                                    </option>
                                @endforeach
                            </x-select>
                        </div>
                    @endif
                    <div>
                        <x-label for="activity_date" required>Fecha</x-label>
                        <x-input wire:model="activity_date" type="date" id="activity_date" data-cy="activity-date-input" :error="$errors->first('activity_date')" required />
                    </div>
                </div>
        </x-form-section>

        <x-form-section title="Detalles de la Observación" color="green">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-label for="observation_type">Tipo de Observación</x-label>
                        <x-select wire:model="observation_type" id="observation_type" data-cy="observation-type-select" :error="$errors->first('observation_type')">
                            <option value="">Selecciona un tipo</option>
                            <option value="plaga">Plaga</option>
                            <option value="enfermedad">Enfermedad</option>
                            <option value="fenología">Fenología</option>
                            <option value="climatología">Climatología</option>
                            <option value="suelo">Suelo</option>
                            <option value="otro">Otro</option>
                        </x-select>
                    </div>
                    <div>
                        <x-label for="severity">Severidad</x-label>
                        <x-select wire:model="severity" id="severity" :error="$errors->first('severity')">
                            <option value="">Sin severidad</option>
                            <option value="leve">Leve</option>
                            <option value="moderada">Moderada</option>
                            <option value="grave">Grave</option>
                        </x-select>
                    </div>
                </div>
                <div class="mt-6">
                    <x-label for="description" required>Descripción</x-label>
                    <x-textarea wire:model="description" id="description" data-cy="description-textarea" rows="6" placeholder="Describe detalladamente la observación..." :error="$errors->first('description')" required />
                </div>
                <div class="mt-6">
                    <x-label for="action_taken">Acción Tomada</x-label>
                    <x-textarea wire:model="action_taken" id="action_taken" rows="4" placeholder="Describe las acciones tomadas o previstas..." :error="$errors->first('action_taken')" />
                </div>
        </x-form-section>

        <x-form-section title="Información Adicional" color="green" class="pb-6">
                <!-- ¿Quién realizó el trabajo? -->
                <div class="mb-6">
                    <x-label class="mb-3 block font-semibold text-gray-700">¿Quién realizó el trabajo?</x-label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Opción: Equipo completo -->
                        <div class="border-2 rounded-lg p-4 transition-all {{ $workType === 'crew' ? 'border-[var(--color-agro-green)] bg-[var(--color-agro-green-bg)]' : 'border-gray-200 hover:border-gray-300' }}">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input 
                                    type="radio" 
                                    wire:model.live="workType" 
                                    value="crew" 
                                    class="w-5 h-5 text-[var(--color-agro-green)] focus:ring-[var(--color-agro-green)]"
                                />
                                <div class="flex-1">
                                    <span class="font-semibold text-gray-900">Equipo completo</span>
                                    <p class="text-sm text-gray-500 mt-1">Todo el equipo trabajó en esta actividad</p>
                                </div>
                            </label>
                            @if($workType === 'crew')
                                <div class="mt-4">
                                    <x-label for="crew_id" class="text-sm">Selecciona el equipo</x-label>
                                    <x-select 
                                        wire:model="crew_id" 
                                        id="crew_id"
                                        class="mt-1"
                                        :error="$errors->first('crew_id')"
                                    >
                                        <option value="">Selecciona un equipo</option>
                                        @foreach($crews as $crew)
                                            <option value="{{ $crew->id }}">{{ $crew->name }}</option>
                                        @endforeach
                                    </x-select>
                                </div>
                            @endif
                        </div>

                        <!-- Opción: Viticultor individual -->
                        <div class="border-2 rounded-lg p-4 transition-all {{ $workType === 'individual' ? 'border-[var(--color-agro-green)] bg-[var(--color-agro-green-bg)]' : 'border-gray-200 hover:border-gray-300' }}">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input 
                                    type="radio" 
                                    wire:model.live="workType" 
                                    value="individual" 
                                    class="w-5 h-5 text-[var(--color-agro-green)] focus:ring-[var(--color-agro-green)]"
                                />
                                <div class="flex-1">
                                    <span class="font-semibold text-gray-900">Viticultor individual</span>
                                    <p class="text-sm text-gray-500 mt-1">Un viticultor específico realizó el trabajo</p>
                                </div>
                            </label>
                            @if($workType === 'individual')
                                <div class="mt-4">
                                    <x-label for="crew_member_id" class="text-sm">Selecciona el viticultor</x-label>
                                    <x-select 
                                        wire:model="crew_member_id" 
                                        id="crew_member_id"
                                        class="mt-1"
                                        :error="$errors->first('crew_member_id')"
                                    >
                                        <option value="">Selecciona un viticultor</option>
                                        @if(isset($allViticulturists))
                                            @foreach($allViticulturists as $viticulturist)
                                                <option value="{{ $viticulturist->id }}">
                                                    {{ $viticulturist->name }} ({{ $viticulturist->email }})
                                                </option>
                                            @endforeach
                                        @endif
                                    </x-select>
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
                    <x-label for="machinery_id">Maquinaria</x-label>
                    <x-select wire:model="machinery_id" id="machinery_id" :error="$errors->first('machinery_id')">
                        <option value="">Sin maquinaria asignada</option>
                        @foreach($machinery as $item)
                            <option value="{{ $item->id }}">{{ $item->name }} ({{ $item->type }})</option>
                        @endforeach
                    </x-select>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <div>
                        <x-label for="weather_conditions">Condiciones Meteorológicas</x-label>
                        <x-input wire:model="weather_conditions" type="text" id="weather_conditions" placeholder="Ej: Soleado, nublado" :error="$errors->first('weather_conditions')" />
                    </div>
                    <div>
                        <x-label for="temperature">Temperatura (°C)</x-label>
                        <x-input wire:model="temperature" type="number" step="0.1" id="temperature" placeholder="20.0" :error="$errors->first('temperature')" />
                    </div>
                </div>
                <div class="mt-6">
                    <x-label for="notes">Notas</x-label>
                    <x-textarea wire:model="notes" id="notes" rows="4" placeholder="Observaciones adicionales, comentarios, etc." :error="$errors->first('notes')" />
                </div>
        </x-form-section>

        <x-form-actions 
            :cancel-url="route('viticulturist.digital-notebook')"
            submit-label="Registrar Observación"
        />
    </form>
</x-form-card>

