@php
    $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>';
@endphp

<x-form-card
    title="Editar Riego"
    description="Modifica los datos del riego"
    :icon="$icon"
    icon-color="from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]"
    :back-url="route('viticulturist.digital-notebook')"
>
    <form wire:submit="update" class="space-y-8" data-cy="irrigation-form">
        <x-form-section title="InformaciÃ³n BÃ¡sica" color="green">
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
                                PlantaciÃ³n
                                @if(count($availablePlantings) > 0)
                                    <span class="text-red-500">*</span>
                                @else
                                    <span class="text-gray-500 text-sm">(Opcional)</span>
                                @endif
                            </x-label>
                            <x-select wire:model="plot_planting_id" id="plot_planting_id" data-cy="plot-planting-select" :error="$errors->first('plot_planting_id')" :required="count($availablePlantings) > 0">
                                <option value="">-- Selecciona una plantaciÃ³n --</option>
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
                    <div>
                        <x-label for="phenological_stage">Estadio FenolÃ³gico</x-label>
                        <x-select wire:model="phenological_stage" id="phenological_stage" data-cy="phenological-stage-select" :error="$errors->first('phenological_stage')">
                            <option value="">Selecciona un estadio</option>
                            <option value="BrotaciÃ³n">BrotaciÃ³n</option>
                            <option value="Desarrollo vegetativo">Desarrollo vegetativo</option>
                            <option value="FloraciÃ³n">FloraciÃ³n</option>
                            <option value="Cuajado">Cuajado</option>
                            <option value="Envero">Envero</option>
                            <option value="MaduraciÃ³n">MaduraciÃ³n</option>
                            <option value="Vendimia">Vendimia</option>
                            <option value="CaÃ­da de hoja">CaÃ­da de hoja</option>
                            <option value="Reposo invernal">Reposo invernal</option>
                        </x-select>
                        <p class="text-xs text-gray-500 mt-1">Recomendado para trazabilidad PAC</p>
                    </div>
                </div>
        </x-form-section>

        <x-form-section title="InformaciÃ³n del Riego" color="green">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-label for="water_volume">Volumen de Agua (L)</x-label>
                        <x-input wire:model="water_volume" type="number" step="0.001" id="water_volume" data-cy="water-volume-input" placeholder="0.000" :error="$errors->first('water_volume')" />
                    </div>
                    <div>
                        <x-label for="irrigation_method">MÃ©todo de Riego</x-label>
                        <x-select wire:model="irrigation_method" id="irrigation_method" data-cy="irrigation-method-select" :error="$errors->first('irrigation_method')">
                            <option value="">Selecciona un mÃ©todo</option>
                            <option value="goteo">Goteo</option>
                            <option value="aspersiÃ³n">AspersiÃ³n</option>
                            <option value="superficie">Superficie</option>
                            <option value="subterrÃ¡neo">SubterrÃ¡neo</option>
                            <option value="otro">Otro</option>
                        </x-select>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                    <div>
                        <x-label for="duration_minutes">DuraciÃ³n (minutos)</x-label>
                        <x-input wire:model="duration_minutes" type="number" id="duration_minutes" placeholder="0" :error="$errors->first('duration_minutes')" />
                    </div>
                    <div>
                        <x-label for="soil_moisture_before">Humedad del Suelo Antes (%)</x-label>
                        <x-input wire:model="soil_moisture_before" type="number" step="0.1" min="0" max="100" id="soil_moisture_before" placeholder="0.0" :error="$errors->first('soil_moisture_before')" />
                    </div>
                    <div>
                        <x-label for="soil_moisture_after">Humedad del Suelo DespuÃ©s (%)</x-label>
                        <x-input wire:model="soil_moisture_after" type="number" step="0.1" min="0" max="100" id="soil_moisture_after" placeholder="0.0" :error="$errors->first('soil_moisture_after')" />
                    </div>
                </div>
        </x-form-section>

        {{-- SecciÃ³n PAC Obligatoria --}}
        <x-form-section title="Cumplimiento PAC (Obligatorio)" color="amber">
            <div class="space-y-6">
                {{-- Info box PAC --}}
                <div class="p-4 bg-amber-50 border-l-4 border-amber-500 rounded-r-lg">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <div class="flex-1">
                            <h4 class="text-sm font-semibold text-amber-900">InformaciÃ³n PAC</h4>
                            <p class="text-sm text-amber-800 mt-1">
                                Es obligatorio identificar el origen del agua y la concesiÃ³n para cumplir con la condicionalidad reforzada de la PAC.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Origen del Agua --}}
                    <div>
                        <x-label for="water_source">Origen del Agua</x-label>
                        <x-select 
                            wire:model="water_source" 
                            id="water_source"
                            data-cy="water-source-select"
                            :error="$errors->first('water_source')"
                        >
                            <option value="">Selecciona el origen</option>
                            <option value="Pozo legalizado">Pozo legalizado</option>
                            <option value="Comunidad de regantes">Comunidad de regantes</option>
                            <option value="Embalse propio">Embalse propio</option>
                            <option value="Cauce pÃºblico (rÃ­o/arroyo)">Cauce pÃºblico (rÃ­o/arroyo)</option>
                            <option value="Aguas regeneradas">Aguas regeneradas</option>
                            <option value="Otro">Otro</option>
                        </x-select>
                    </div>

                    {{-- NÂº ConcesiÃ³n --}}
                    <div>
                        <x-label for="water_concession">NÂº ConcesiÃ³n / AutorizaciÃ³n</x-label>
                        <x-input 
                            wire:model="water_concession" 
                            type="text" 
                            id="water_concession"
                            placeholder="Ej: 2023/CONF/1234"
                            :error="$errors->first('water_concession')"
                        />
                        <p class="mt-1 text-xs text-gray-500">
                            NÃºmero de expediente de la ConfederaciÃ³n HidrogrÃ¡fica
                        </p>
                    </div>
                </div>

                {{-- Caudal --}}
                <div class="md:w-1/2">
                    <x-label for="flow_rate">Caudal de Riego (L/h)</x-label>
                    <x-input 
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
        </x-form-section>

        <x-form-section title="InformaciÃ³n Adicional" color="green" class="pb-6">
                <!-- Â¿QuiÃ©n realizÃ³ el trabajo? -->
                <div class="mb-6">
                    <x-label class="mb-3 block font-semibold text-gray-700">Â¿QuiÃ©n realizÃ³ el trabajo?</x-label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- OpciÃ³n: Equipo completo -->
                        <div class="border-2 rounded-lg p-4 transition-all {{ $workType === 'crew' ? 'border-[var(--color-agro-green)] bg-[var(--color-agro-green-bg)]' : 'border-gray-200 hover:border-gray-300' }}">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input 
                                    type="radio" 
                                    wire:model.live="workType" 
                                    value="crew" 
                                    data-cy="work-type-crew-radio"
                                    class="w-5 h-5 text-[var(--color-agro-green)] focus:ring-[var(--color-agro-green)]"
                                />
                                <div class="flex-1">
                                    <span class="font-semibold text-gray-900">Equipo completo</span>
                                    <p class="text-sm text-gray-500 mt-1">Todo el equipo trabajÃ³ en esta actividad</p>
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

                        <!-- OpciÃ³n: Viticultor individual -->
                        <div class="border-2 rounded-lg p-4 transition-all {{ $workType === 'individual' ? 'border-[var(--color-agro-green)] bg-[var(--color-agro-green-bg)]' : 'border-gray-200 hover:border-gray-300' }}">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input 
                                    type="radio" 
                                    wire:model.live="workType" 
                                    value="individual" 
                                    data-cy="work-type-individual-radio"
                                    class="w-5 h-5 text-[var(--color-agro-green)] focus:ring-[var(--color-agro-green)]"
                                />
                                <div class="flex-1">
                                    <span class="font-semibold text-gray-900">Viticultor individual</span>
                                    <p class="text-sm text-gray-500 mt-1">Un viticultor especÃ­fico realizÃ³ el trabajo</p>
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
                    <x-select wire:model="machinery_id" id="machinery_id" data-cy="machinery-select" :error="$errors->first('machinery_id')">
                        <option value="">Sin maquinaria asignada</option>
                        @foreach($machinery as $item)
                            <option value="{{ $item->id }}">{{ $item->name }} ({{ $item->type }})</option>
                        @endforeach
                    </x-select>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <div>
                        <x-label for="weather_conditions">Condiciones MeteorolÃ³gicas</x-label>
                        <x-input wire:model="weather_conditions" type="text" id="weather_conditions" placeholder="Ej: Soleado, nublado" :error="$errors->first('weather_conditions')" />
                    </div>
                    <div>
                        <x-label for="temperature">Temperatura (Â°C)</x-label>
                        <x-input wire:model="temperature" type="number" step="0.1" id="temperature" placeholder="20.0" :error="$errors->first('temperature')" />
                    </div>
                </div>
                <div class="mt-6">
                    <x-label for="notes">Notas</x-label>
                    <x-textarea wire:model="notes" id="notes" rows="4" placeholder="Observaciones, comentarios, etc." :error="$errors->first('notes')" />
                </div>
        </x-form-section>

        <x-form-actions 
            :cancel-url="route('viticulturist.digital-notebook')"
            submit-label="Actualizar Riego"
        />
    </form>
</x-form-card>

