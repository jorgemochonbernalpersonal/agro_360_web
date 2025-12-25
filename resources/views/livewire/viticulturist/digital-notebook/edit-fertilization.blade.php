@php
    $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>';
@endphp

<x-form-card
    title="Editar Fertilización"
    description="Modifica los datos de la fertilización"
    :icon="$icon"
    icon-color="from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]"
    :back-url="route('viticulturist.digital-notebook')"
>
    <form wire:submit="update" class="space-y-8" data-cy="fertilization-form">
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

        <x-form-section title="Información del Fertilizante" color="green">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-label for="fertilizer_type">Tipo de Fertilizante</x-label>
                        <x-input wire:model="fertilizer_type" type="text" id="fertilizer_type" data-cy="fertilizer-type-input" placeholder="Ej: Orgánico, Mineral, etc." :error="$errors->first('fertilizer_type')" />
                    </div>
                    <div>
                        <x-label for="fertilizer_name">Nombre del Fertilizante</x-label>
                        <x-input wire:model="fertilizer_name" type="text" id="fertilizer_name" data-cy="fertilizer-name-input" placeholder="Nombre comercial" :error="$errors->first('fertilizer_name')" />
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                    <div>
                        <x-label for="quantity">Cantidad (kg)</x-label>
                        <x-input wire:model="quantity" type="number" step="0.001" id="quantity" data-cy="quantity-input" placeholder="0.000" :error="$errors->first('quantity')" />
                    </div>
                    <div>
                        <x-label for="npk_ratio">Relación NPK</x-label>
                        <x-input wire:model="npk_ratio" type="text" id="npk_ratio" data-cy="npk-ratio-input" placeholder="Ej: 10-10-10" :error="$errors->first('npk_ratio')" />
                    </div>
                    <div>
                        <x-label for="area_applied">Área Aplicada (ha)</x-label>
                        <x-input wire:model="area_applied" type="number" step="0.001" id="area_applied" data-cy="area-applied-input" placeholder="0.000" :error="$errors->first('area_applied')" />
                    </div>
                </div>
                <div class="mt-6">
                    <x-label for="application_method">Método de Aplicación</x-label>
                    <x-select wire:model="application_method" id="application_method" data-cy="application-method-select" :error="$errors->first('application_method')">
                        <option value="">Selecciona un método</option>
                        <option value="aplicación al suelo">Aplicación al Suelo</option>
                        <option value="fertirrigación">Fertirrigación</option>
                        <option value="aplicación foliar">Aplicación Foliar</option>
                        <option value="otro">Otro</option>
                    </x-select>
                </div>
        </x-form-section>

        <x-form-section title="Nutrición Sostenible (PAC)" color="amber">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <x-label for="nitrogen_uf">Unidades N (kg/ha)</x-label>
                    <x-input wire:model="nitrogen_uf" type="number" step="0.001" id="nitrogen_uf" placeholder="0.000" :error="$errors->first('nitrogen_uf')" />
                    <p class="mt-1 text-xs text-gray-500">Unidades Fertilizantes de Nitrógeno</p>
                </div>
                <div>
                    <x-label for="phosphorus_uf">Unidades P2O5 (kg/ha)</x-label>
                    <x-input wire:model="phosphorus_uf" type="number" step="0.001" id="phosphorus_uf" placeholder="0.000" :error="$errors->first('phosphorus_uf')" />
                    <p class="mt-1 text-xs text-gray-500">Unidades Fertilizantes de Fósforo</p>
                </div>
                <div>
                    <x-label for="potassium_uf">Unidades K2O (kg/ha)</x-label>
                    <x-input wire:model="potassium_uf" type="number" step="0.001" id="potassium_uf" placeholder="0.000" :error="$errors->first('potassium_uf')" />
                    <p class="mt-1 text-xs text-gray-500">Unidades Fertilizantes de Potasio</p>
                </div>
            </div>
            
            <div class="mt-6 border-t pt-6">
                <h4 class="text-sm font-medium text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Fertilizantes Orgánicos / Estiércoles
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <x-label for="manure_type">Tipo de Estiércol</x-label>
                        <x-input wire:model="manure_type" type="text" id="manure_type" placeholder="Ej: Bovino, Porcino..." :error="$errors->first('manure_type')" />
                    </div>
                    <div>
                        <x-label for="burial_date">Fecha de Enterrado</x-label>
                        <x-input wire:model="burial_date" type="date" id="burial_date" :error="$errors->first('burial_date')" />
                    </div>
                    <div>
                        <x-label for="emission_reduction_method">Método Reducción Emisiones</x-label>
                        <x-select wire:model="emission_reduction_method" id="emission_reduction_method" :error="$errors->first('emission_reduction_method')">
                            <option value="">Selecciona un método</option>
                            <option value="inyección">Inyección directa</option>
                            <option value="platos">Platos deflectores</option>
                            <option value="tubos">Tubos colgantes</option>
                            <option value="enterrado_inmediato">Enterrado inmediato (< 4h)</option>
                            <option value="otro">Otro</option>
                        </x-select>
                    </div>
                </div>
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
                                    data-cy="work-type-crew-radio"
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
                                        data-cy="crew-select"
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
                                    data-cy="work-type-individual-radio"
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
                                        data-cy="crew-member-select"
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
                        <x-label for="weather_conditions">Condiciones Meteorológicas</x-label>
                        <x-input wire:model="weather_conditions" type="text" id="weather_conditions" data-cy="weather-conditions-input" placeholder="Ej: Soleado, nublado" :error="$errors->first('weather_conditions')" />
                    </div>
                    <div>
                        <x-label for="temperature">Temperatura (°C)</x-label>
                        <x-input wire:model="temperature" type="number" step="0.1" id="temperature" data-cy="temperature-input" placeholder="20.0" :error="$errors->first('temperature')" />
                    </div>
                </div>
                <div class="mt-6">
                    <x-label for="notes">Notas</x-label>
                    <x-textarea wire:model="notes" id="notes" data-cy="notes-textarea" rows="4" placeholder="Observaciones, comentarios, etc." :error="$errors->first('notes')" />
                </div>
        </x-form-section>

        <x-form-actions 
            :cancel-url="route('viticulturist.digital-notebook')"
            submit-label="Actualizar Fertilización"
        />
    </form>
</x-form-card>

