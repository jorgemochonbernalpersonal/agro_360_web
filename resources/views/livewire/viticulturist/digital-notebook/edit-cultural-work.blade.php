<x-agro.form-card
    title="Editar Labor Cultural"
    description="Modifica los datos de la labor cultural"
    :back-url="roleRoute('viticulturist.digital-notebook.cultural.index')"
>
    <form wire:submit="update" class="space-y-8" data-cy="cultural-work-form">
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

        <x-agro.form-section title="Información de la Labor">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <flux:label for="work_type">Tipo de Labor</flux:label>
                        <flux:select wire:model.live="work_type" id="work_type" data-cy="work-type-select" :error="$errors->first('work_type')">
                            <option value="">Selecciona un tipo</option>
                            <option value="poda">Poda</option>
                            <option value="deshojado">Deshojado</option>
                            <option value="despuntado">Despuntado</option>
                            <option value="laboreo">Laboreo</option>
                            <option value="desbroce">Desbroce</option>
                            <option value="otro">Otro</option>
                        </flux:select>
                    </div>
                    <div>
                        <flux:label for="workers_count">Número de Trabajadores</flux:label>
                        <flux:input wire:model="workers_count" type="number" min="1" id="workers_count" placeholder="0" :error="$errors->first('workers_count')" />
                    </div>
                </div>
                @if($work_type === 'poda')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <div>
                        <flux:label for="pruning_type">Tipo de Poda</flux:label>
                        <flux:select wire:model="pruning_type" id="pruning_type" data-cy="pruning-type-select" :error="$errors->first('pruning_type')">
                            <option value="">Selecciona un tipo</option>
                            <option value="guyot">Guyot</option>
                            <option value="doble_guyot">Doble Guyot</option>
                            <option value="vaso">Vaso</option>
                            <option value="cordon">Cordón</option>
                            <option value="other">Otro</option>
                        </flux:select>
                    </div>
                    <div>
                        <flux:label for="productive_buds_per_hectare">Yemas Productivas / ha</flux:label>
                        <flux:input wire:model="productive_buds_per_hectare" type="number" min="0" id="productive_buds_per_hectare" placeholder="Ej: 40000" :error="$errors->first('productive_buds_per_hectare')" />
                        <p class="text-xs text-zinc-500 mt-1">Yemas productivas resultantes de la poda</p>
                    </div>
                </div>
                @endif
                @if($work_type === 'deshojado')
                <div class="mt-6 md:w-1/2">
                    <flux:label for="defoliation_face">Cara de la Cepa</flux:label>
                    <flux:select wire:model="defoliation_face" id="defoliation_face" data-cy="defoliation-face-select" :error="$errors->first('defoliation_face')">
                        <option value="">Selecciona la cara</option>
                        <option value="norte">Cara norte</option>
                        <option value="sur">Cara sur</option>
                        <option value="ambas">Ambas caras</option>
                    </flux:select>
                    <p class="text-xs text-zinc-500 mt-1">Orientación del deshojado para control de podredumbre y maduración</p>
                </div>
                @endif
                @if($work_type === 'despuntado')
                <div class="mt-6 md:w-1/2">
                    <flux:label for="topping_height_cm">Altura de Despunte (cm)</flux:label>
                    <flux:input wire:model="topping_height_cm" type="number" min="1" max="300" id="topping_height_cm" data-cy="topping-height-input" placeholder="Ej: 180" :error="$errors->first('topping_height_cm')" />
                    <p class="text-xs text-zinc-500 mt-1">Altura desde el suelo a la que se realiza el despuntado</p>
                </div>
                @endif
                <div class="mt-6">
                    <flux:label for="hours_worked">Horas Trabajadas</flux:label>
                    <flux:input wire:model="hours_worked" type="number" step="0.5" min="0" id="hours_worked" placeholder="0.0" :error="$errors->first('hours_worked')" />
                </div>
                <div class="mt-6">
                    <flux:label for="description">Descripción</flux:label>
                    <flux:textarea wire:model="description" id="description" rows="4" placeholder="Descripción detallada de la labor realizada..." :error="$errors->first('description')" />
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
                                    <flux:label for="crew_id" class="text-sm">Selecciona el equipo</flux:label>
                                    <flux:select wire:model="crew_id" id="crew_id" class="mt-1" :error="$errors->first('crew_id')">
                                        <option value="">Selecciona un equipo</option>
                                        @foreach($crews as $crew)
                                            <option value="{{ $crew->id }}">{{ $crew->name }}</option>
                                        @endforeach
                                    </flux:select>
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
                                    <flux:label for="crew_member_id" class="text-sm">Selecciona el viticultor</flux:label>
                                    <flux:select wire:model="crew_member_id" id="crew_member_id" class="mt-1" :error="$errors->first('crew_member_id')">
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
                    <flux:textarea wire:model="notes" id="notes" rows="4" placeholder="Observaciones, comentarios, etc." :error="$errors->first('notes')" />
                </div>
        </x-agro.form-section>

        <x-agro.form-actions
            :cancel-url="roleRoute('viticulturist.digital-notebook.cultural.index')"
            submit-label="Actualizar Labor"
        />
    </form>
</x-agro.form-card>
