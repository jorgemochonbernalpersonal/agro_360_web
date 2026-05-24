<x-agro.form-card
    :title="__('Editar Poda')"
    :description="__('Modifica los datos de la operación de poda')"
    :back-url="roleRoute('viticulturist.digital-notebook.pruning.index')"
>
    <form wire:submit="update" class="space-y-8">
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
                @if($plot_id && count($availablePlantings) > 0)
                    <flux:field>
                        <flux:label>{{ __('Plantación') }}</flux:label>
                        <flux:select wire:model="plot_planting_id" data-cy="planting-select">
                            <option value="">{{ __('-- Selecciona una plantación --') }}</option>
                            @foreach($availablePlantings as $planting)
                                <option value="{{ $planting->id }}">
                                    {{ $planting->name }}@if($planting->grapeVariety) - {{ $planting->grapeVariety->name }}@endif
                                </option>
                            @endforeach
                        </flux:select>
                        <flux:error name="plot_planting_id" />
                    </flux:field>
                @endif
                <flux:field>
                    <flux:label required>{{ __('Fecha de poda') }}</flux:label>
                    <flux:input wire:model="activity_date" type="date" data-cy="activity-date-input" />
                    <flux:error name="activity_date" />
                </flux:field>
                <flux:field>
                    <flux:label required>{{ __('Estadio Fenológico') }}</flux:label>
                    <flux:select wire:model="phenological_stage" data-cy="phenological-stage-select">
                        <option value="">{{ __('Selecciona un estadio') }}</option>
                        <option value="Reposo invernal">{{ __('Reposo invernal') }}</option>
                        <option value="Brotación">{{ __('Brotación') }}</option>
                        <option value="Desarrollo vegetativo">{{ __('Desarrollo vegetativo') }}</option>
                        <option value="Caída de hoja">{{ __('Caída de hoja') }}</option>
                    </flux:select>
                    <flux:error name="phenological_stage" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-section :title="__('Datos de la Poda')">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:field>
                    <flux:label required>{{ __('Tipo de Poda') }}</flux:label>
                    <flux:select wire:model="pruning_type" data-cy="pruning-type-select">
                        <option value="">{{ __('Selecciona un tipo') }}</option>
                        <option value="guyot">Guyot</option>
                        <option value="doble_guyot">Doble Guyot</option>
                        <option value="vaso">{{ __('Vaso') }}</option>
                        <option value="cordon">{{ __('Cordón') }}</option>
                        <option value="otro">{{ __('Otro') }}</option>
                    </flux:select>
                    <flux:error name="pruning_type" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Yemas Productivas / ha') }}</flux:label>
                    <flux:input wire:model="productive_buds_per_hectare" type="number" min="0" data-cy="productive-buds-input" :placeholder="__('Ej: 40000')" />
                    <flux:error name="productive_buds_per_hectare" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Horas Trabajadas') }}</flux:label>
                    <flux:input wire:model="hours_worked" type="number" step="0.5" min="0" data-cy="hours-worked-input" placeholder="0.0" />
                    <flux:error name="hours_worked" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Número de Trabajadores') }}</flux:label>
                    <flux:input wire:model="workers_count" type="number" min="1" data-cy="workers-count-input" placeholder="0" />
                    <flux:error name="workers_count" />
                </flux:field>
            </div>
            <div class="mt-6">
                <flux:field>
                    <flux:label required>{{ __('Descripción') }}</flux:label>
                    <flux:textarea wire:model="description" data-cy="description-textarea" rows="4" />
                    <flux:error name="description" />
                </flux:field>
            </div>

            <div class="mt-6 border-t pt-6">
                <h4 class="text-sm font-semibold text-zinc-700 mb-4">{{ __('Gestión de Restos de Poda (BCAM 6 — PAC)') }}</h4>
                <flux:field>
                    <flux:label>{{ __('Gestión del ramón de poda') }}</flux:label>
                    <flux:select wire:model="residue_management" data-cy="residue-management-select">
                        <option value="">{{ __('-- Sin especificar --') }}</option>
                        <option value="triturado_incorporado">{{ __('Triturado e incorporado al suelo') }}</option>
                        <option value="triturado_superficie">{{ __('Triturado en superficie') }}</option>
                        <option value="retirado">{{ __('Retirado de la parcela') }}</option>
                        <option value="quemado">{{ __('Quemado (con autorización)') }}</option>
                        <option value="otro">{{ __('Otro') }}</option>
                    </flux:select>
                    <flux:description>{{ __('Cumplimiento BCAM 6 — gestión de residuos de poda') }}</flux:description>
                    <flux:error name="residue_management" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-section :title="__('Personal y Maquinaria')">
            <div class="mb-6">
                <flux:label class="mb-3 block font-semibold text-zinc-700">{{ __('¿Quién realizó la poda?') }}</flux:label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="border-2 rounded-lg p-4 transition-all {{ $workType === 'crew' ? 'border-agro-500 bg-agro-50' : 'border-zinc-200 hover:border-zinc-300' }}">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="radio" wire:model.live="workType" value="crew" data-cy="work-type-crew" class="w-5 h-5 text-agro-500 focus:ring-agro-500" />
                            <div class="flex-1">
                                <span class="font-semibold text-zinc-900">{{ __('Equipo completo') }}</span>
                                <p class="text-sm text-zinc-500 mt-1">{{ __('Todo el equipo trabajó en esta actividad') }}</p>
                            </div>
                        </label>
                        @if($workType === 'crew')
                            <div class="mt-4">
                                <flux:field>
                                    <flux:label>{{ __('Selecciona el equipo') }}</flux:label>
                                    <flux:select wire:model="crew_id" data-cy="crew-select">
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
                            <input type="radio" wire:model.live="workType" value="individual" data-cy="work-type-individual" class="w-5 h-5 text-agro-500 focus:ring-agro-500" />
                            <div class="flex-1">
                                <span class="font-semibold text-zinc-900">{{ __('Viticultor individual') }}</span>
                            </div>
                        </label>
                        @if($workType === 'individual')
                            <div class="mt-4">
                                <flux:field>
                                    <flux:label>{{ __('Selecciona el viticultor') }}</flux:label>
                                    <flux:select wire:model="crew_member_id" data-cy="crew-member-select">
                                        <option value="">{{ __('Selecciona un viticultor') }}</option>
                                        @foreach($allViticulturists as $v)
                                            <option value="{{ $v->id }}">{{ $v->name }}@if($v->id === auth()->id()) ({{ __('Yo') }})@endif</option>
                                        @endforeach
                                    </flux:select>
                                    <flux:error name="crew_member_id" />
                                </flux:field>
                            </div>
                        @endif
                    </div>
                </div>
                @error('workType') <p class="mt-2 text-sm text-red-600" data-cy="work-type-error">{{ $message }}</p> @enderror
            </div>

            <flux:field>
                <flux:label>{{ __('Maquinaria') }}</flux:label>
                <flux:select wire:model="machinery_id" data-cy="machinery-select">
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
                    <flux:input wire:model="weather_conditions" type="text" data-cy="weather-conditions-input" />
                    <flux:error name="weather_conditions" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Temperatura') }}</flux:label>
                    <flux:input wire:model="temperature" type="number" step="0.1" data-cy="temperature-input" />
                    <flux:description>°C</flux:description>
                    <flux:error name="temperature" />
                </flux:field>
            </div>
            <div class="mt-6">
                <flux:field>
                    <flux:label>{{ __('Notas') }}</flux:label>
                    <flux:textarea wire:model="notes" data-cy="notes-textarea" rows="3" />
                    <flux:error name="notes" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-actions
            :cancel-url="roleRoute('viticulturist.digital-notebook.pruning.index')"
            :submit-label="__('Actualizar Poda')"
            submit-method="update"
        />
    </form>
</x-agro.form-card>
