<x-agro.form-card
    :title="__('Editar Labor Cultural')"
    :description="__('Modifica los datos de la labor cultural')"
    :back-url="roleRoute('viticulturist.digital-notebook.cultural.index')"
>
    <form wire:submit="update" class="space-y-8" data-cy="cultural-work-form">

        <x-agro.form-section :title="__('Información Básica')">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <flux:field>
                    <flux:label required>{{ __('Parcela') }}</flux:label>
                    <flux:select wire:model.live="plot_id" data-cy="plot-select">
                        <option value="">{{ __('Selecciona una parcela') }}</option>
                        @foreach($plots as $plot)
                            <option value="{{ $plot->id }}">{{ $plot->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="plot_id" />
                </flux:field>

                @if($plot_id)
                    <flux:field>
                        <flux:label :required="count($availablePlantings) > 0">
                            {{ __('Plantación') }}
                            @if(!count($availablePlantings))
                                <span class="text-zinc-400 text-xs font-normal">({{ __('Sin plantaciones activas') }})</span>
                            @endif
                        </flux:label>
                        <flux:select wire:model="plot_planting_id" data-cy="plot-planting-select">
                            <option value="">{{ __('-- Selecciona una plantación --') }}</option>
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
                    <flux:label required>{{ __('Fecha') }}</flux:label>
                    <flux:input wire:model="activity_date" type="date" data-cy="activity-date-input" />
                    <flux:error name="activity_date" />
                </flux:field>

            </div>
        </x-agro.form-section>

        <x-agro.form-section :title="__('Información de la Labor')">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:field>
                    <flux:label>{{ __('Tipo de Labor') }}</flux:label>
                    <flux:select wire:model.live="work_type" data-cy="work-type-select">
                        <option value="">{{ __('Selecciona un tipo') }}</option>
                        <option value="poda">{{ __('Poda') }}</option>
                        <option value="deshojado">{{ __('Deshojado') }}</option>
                        <option value="despuntado">{{ __('Despuntado') }}</option>
                        <option value="laboreo">{{ __('Laboreo') }}</option>
                        <option value="desbroce">{{ __('Desbroce') }}</option>
                        <option value="otro">{{ __('Otro') }}</option>
                    </flux:select>
                    <flux:error name="work_type" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Número de Trabajadores') }}</flux:label>
                    <flux:input wire:model="workers_count" type="number" min="1" placeholder="0" />
                    <flux:error name="workers_count" />
                </flux:field>
            </div>
            @if($work_type === 'poda')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <flux:field>
                        <flux:label>{{ __('Tipo de Poda') }}</flux:label>
                        <flux:select wire:model="pruning_type" id="pruning_type" data-cy="pruning-type-select">
                            <option value="">{{ __('Selecciona un tipo') }}</option>
                            <option value="guyot">Guyot</option>
                            <option value="doble_guyot">Doble Guyot</option>
                            <option value="vaso">{{ __('Vaso') }}</option>
                            <option value="cordon">{{ __('Cordón') }}</option>
                            <option value="other">{{ __('Otro') }}</option>
                        </flux:select>
                        <flux:error name="pruning_type" />
                    </flux:field>
                    <flux:field>
                        <flux:label>{{ __('Yemas Productivas / ha') }}</flux:label>
                        <flux:input wire:model="productive_buds_per_hectare" type="number" min="0" id="productive_buds_per_hectare" :placeholder="__('Ej: 40000')" />
                        <flux:description>{{ __('Yemas productivas resultantes de la poda') }}</flux:description>
                        <flux:error name="productive_buds_per_hectare" />
                    </flux:field>
                </div>
            @endif
            @if($work_type === 'deshojado')
                <div class="mt-6 md:w-1/2">
                    <flux:field>
                        <flux:label>{{ __('Cara de la Cepa') }}</flux:label>
                        <flux:select wire:model="defoliation_face" data-cy="defoliation-face-select">
                            <option value="">{{ __('Selecciona la cara') }}</option>
                            <option value="norte">{{ __('Cara norte') }}</option>
                            <option value="sur">{{ __('Cara sur') }}</option>
                            <option value="ambas">{{ __('Ambas caras') }}</option>
                        </flux:select>
                        <flux:description>{{ __('Orientación del deshojado para control de podredumbre y maduración') }}</flux:description>
                        <flux:error name="defoliation_face" />
                    </flux:field>
                </div>
            @endif
            @if($work_type === 'despuntado')
                <div class="mt-6 md:w-1/2">
                    <flux:field>
                        <flux:label>{{ __('Altura de Despunte') }}</flux:label>
                        <flux:input wire:model="topping_height_cm" type="number" min="1" max="300" data-cy="topping-height-input" :placeholder="__('Ej: 180')" />
                        <flux:description>{{ __('cm desde el suelo a la que se realiza el despuntado') }}</flux:description>
                        <flux:error name="topping_height_cm" />
                    </flux:field>
                </div>
            @endif
            <div class="mt-6">
                <flux:field>
                    <flux:label>{{ __('Horas Trabajadas') }}</flux:label>
                    <flux:input wire:model="hours_worked" type="number" step="0.5" min="0" id="hours_worked" placeholder="0.0" />
                    <flux:error name="hours_worked" />
                </flux:field>
            </div>
            <div class="mt-6">
                <flux:field>
                    <flux:label>{{ __('Descripción') }}</flux:label>
                    <flux:textarea wire:model="description" id="description" rows="4" :placeholder="__('Descripción detallada de la labor realizada...')" />
                    <flux:error name="description" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-section :title="__('Información Adicional')">
            <div class="mb-6">
                <flux:label class="mb-3 block font-semibold text-zinc-700">{{ __('¿Quién realizó el trabajo?') }}</flux:label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="border-2 rounded-lg p-4 transition-all {{ $workType === 'crew' ? 'border-agro-500 bg-agro-50' : 'border-zinc-200 hover:border-zinc-300' }}">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="radio" wire:model.live="workType" value="crew" data-cy="work-type-crew-radio" class="w-5 h-5 text-agro-500 focus:ring-agro-500" />
                            <div class="flex-1">
                                <span class="font-semibold text-zinc-900">{{ __('Equipo completo') }}</span>
                                <p class="text-sm text-zinc-500 mt-1">{{ __('Todo el equipo trabajó en esta actividad') }}</p>
                            </div>
                        </label>
                        @if($workType === 'crew')
                            <div class="mt-4">
                                <flux:field>
                                    <flux:label>{{ __('Selecciona el equipo') }}</flux:label>
                                    <flux:select wire:model="crew_id" id="crew_id" data-cy="crew-select">
                                        <option value="">{{ __('Selecciona un equipo') }}</option>
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
                                <span class="font-semibold text-zinc-900">{{ __('Viticultor individual') }}</span>
                                <p class="text-sm text-zinc-500 mt-1">{{ __('Un viticultor específico realizó el trabajo') }}</p>
                            </div>
                        </label>
                        @if($workType === 'individual')
                            <div class="mt-4">
                                <flux:field>
                                    <flux:label>{{ __('Selecciona el viticultor') }}</flux:label>
                                    <flux:select wire:model="crew_member_id" id="crew_member_id" data-cy="crew-member-select">
                                        <option value="">{{ __('Selecciona un viticultor') }}</option>
                                        @foreach($allViticulturists as $viticulturist)
                                            <option value="{{ $viticulturist->id }}">
                                                {{ $viticulturist->name }}@if($viticulturist->id === auth()->id()) ({{ __('Yo') }})@endif
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
                <flux:label>{{ __('Maquinaria') }}</flux:label>
                <flux:select wire:model="machinery_id">
                    <option value="">{{ __('Sin maquinaria asignada') }}</option>
                    @foreach($machinery as $item)
                        <option value="{{ $item->id }}">{{ $item->name }} ({{ $item->type }})</option>
                    @endforeach
                </flux:select>
                <flux:error name="machinery_id" />
            </flux:field>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <flux:field>
                    <flux:label>{{ __('Condiciones Meteorológicas') }}</flux:label>
                    <flux:input wire:model="weather_conditions" type="text" :placeholder="__('Ej: Soleado, nublado')" />
                    <flux:error name="weather_conditions" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Temperatura') }}</flux:label>
                    <flux:input wire:model="temperature" type="number" step="0.1" placeholder="20.0" />
                    <flux:description>°C</flux:description>
                    <flux:error name="temperature" />
                </flux:field>
            </div>

            <div class="mt-6">
                <flux:field>
                    <flux:label>{{ __('Notas') }}</flux:label>
                    <flux:textarea wire:model="notes" rows="4" :placeholder="__('Observaciones, comentarios, etc.')" />
                    <flux:error name="notes" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-actions
            :cancel-url="roleRoute('viticulturist.digital-notebook.cultural.index')"
            :submit-label="__('Actualizar Labor')"
        />
    </form>
</x-agro.form-card>
