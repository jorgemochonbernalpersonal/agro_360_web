<x-agro.form-card
    title="Editar Poda"
    description="Modifica los datos de la operación de poda"
    :back-url="roleRoute('viticulturist.digital-notebook.pruning.index')"
>
    <form wire:submit="update" class="space-y-8">
        <x-agro.form-section title="Información Básica">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <flux:label for="plot_id" required>Parcela</flux:label>
                    <flux:select wire:model.live="plot_id" id="plot_id" data-cy="plot-select" required>
                        <option value="">Selecciona una parcela</option>
                        @foreach($plots as $plot)
                            <option value="{{ $plot->id }}">{{ $plot->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="plot_id" />
                </div>
                @if($plot_id && count($availablePlantings) > 0)
                    <div>
                        <flux:label for="plot_planting_id">Plantación</flux:label>
                        <flux:select wire:model="plot_planting_id" id="plot_planting_id" data-cy="planting-select">
                            <option value="">-- Selecciona una plantación --</option>
                            @foreach($availablePlantings as $planting)
                                <option value="{{ $planting->id }}">
                                    {{ $planting->name }}@if($planting->grapeVariety) - {{ $planting->grapeVariety->name }}@endif
                                </option>
                            @endforeach
                        </flux:select>
                        <flux:error name="plot_planting_id" />
                    </div>
                @endif
                <div>
                    <flux:label for="activity_date" required>Fecha de poda</flux:label>
                    <flux:input wire:model="activity_date" type="date" id="activity_date" data-cy="activity-date-input" required />
                    <flux:error name="activity_date" />
                </div>
                <div>
                    <flux:label for="phenological_stage" required>Estadio Fenológico</flux:label>
                    <flux:select wire:model="phenological_stage" id="phenological_stage" data-cy="phenological-stage-select" required>
                        <option value="">Selecciona un estadio</option>
                        <option value="Reposo invernal">Reposo invernal</option>
                        <option value="Brotación">Brotación</option>
                        <option value="Desarrollo vegetativo">Desarrollo vegetativo</option>
                        <option value="Caída de hoja">Caída de hoja</option>
                    </flux:select>
                    <flux:error name="phenological_stage" />
                </div>
            </div>
        </x-agro.form-section>

        <x-agro.form-section title="Datos de la Poda">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <flux:label for="pruning_type" required>Tipo de Poda</flux:label>
                    <flux:select wire:model="pruning_type" id="pruning_type" data-cy="pruning-type-select" required>
                        <option value="">Selecciona un tipo</option>
                        <option value="guyot">Guyot</option>
                        <option value="doble_guyot">Doble Guyot</option>
                        <option value="vaso">Vaso</option>
                        <option value="cordon">Cordón</option>
                        <option value="other">Otro</option>
                    </flux:select>
                    <flux:error name="pruning_type" />
                </div>
                <div>
                    <flux:label for="productive_buds_per_hectare">Yemas Productivas / ha</flux:label>
                    <flux:input wire:model="productive_buds_per_hectare" type="number" min="0" id="productive_buds_per_hectare" data-cy="productive-buds-input" placeholder="Ej: 40000" />
                    <flux:error name="productive_buds_per_hectare" />
                </div>
                <div>
                    <flux:label for="hours_worked">Horas Trabajadas</flux:label>
                    <flux:input wire:model="hours_worked" type="number" step="0.5" min="0" id="hours_worked" data-cy="hours-worked-input" placeholder="0.0" />
                </div>
                <div>
                    <flux:label for="workers_count">Número de Trabajadores</flux:label>
                    <flux:input wire:model="workers_count" type="number" min="1" id="workers_count" data-cy="workers-count-input" placeholder="0" />
                </div>
            </div>
            <div class="mt-6">
                <flux:label for="description" required>Descripción</flux:label>
                <flux:textarea wire:model="description" id="description" data-cy="description-textarea" rows="4" required />
                <flux:error name="description" />
            </div>

            {{-- Gestión del ramón — BCAM 6 PAC --}}
            <div class="mt-6 border-t pt-6">
                <h4 class="text-sm font-semibold text-zinc-700 mb-4">Gestión de Restos de Poda (BCAM 6 — PAC)</h4>
                <div>
                    <flux:label for="residue_management">Gestión del ramón de poda</flux:label>
                    <flux:select wire:model="residue_management" id="residue_management" data-cy="residue-management-select">
                        <option value="">-- Sin especificar --</option>
                        <option value="triturado_incorporado">Triturado e incorporado al suelo</option>
                        <option value="triturado_superficie">Triturado en superficie</option>
                        <option value="retirado">Retirado de la parcela</option>
                        <option value="quemado">Quemado (con autorización)</option>
                        <option value="otro">Otro</option>
                    </flux:select>
                    <p class="text-xs text-zinc-500 mt-1">Cumplimiento BCAM 6 — gestión de residuos de poda</p>
                    <flux:error name="residue_management" />
                </div>
            </div>
        </x-agro.form-section>

        <x-agro.form-section title="Personal y Maquinaria">
            <div class="mb-6">
                <flux:label class="mb-3 block font-semibold text-zinc-700">¿Quién realizó la poda?</flux:label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="border-2 rounded-lg p-4 transition-all {{ $workType === 'crew' ? 'border-agro-500 bg-agro-50' : 'border-zinc-200 hover:border-zinc-300' }}">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="radio" wire:model.live="workType" value="crew" data-cy="work-type-crew" class="w-5 h-5 text-agro-500 focus:ring-agro-500" />
                            <div class="flex-1">
                                <span class="font-semibold text-zinc-900">Equipo completo</span>
                                <p class="text-sm text-zinc-500 mt-1">Todo el equipo trabajó en esta actividad</p>
                            </div>
                        </label>
                        @if($workType === 'crew')
                            <div class="mt-4">
                                <flux:label for="crew_id" class="text-sm" required>Selecciona el equipo</flux:label>
                                <flux:select wire:model="crew_id" id="crew_id" data-cy="crew-select" class="mt-1">
                                    <option value="">Selecciona un equipo</option>
                                    @foreach($crews as $crew)
                                        <option value="{{ $crew->id }}">{{ $crew->name }}</option>
                                    @endforeach
                                </flux:select>
                                <flux:error name="crew_id" />
                            </div>
                        @endif
                    </div>
                    <div class="border-2 rounded-lg p-4 transition-all {{ $workType === 'individual' ? 'border-agro-500 bg-agro-50' : 'border-zinc-200 hover:border-zinc-300' }}">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="radio" wire:model.live="workType" value="individual" data-cy="work-type-individual" class="w-5 h-5 text-agro-500 focus:ring-agro-500" />
                            <div class="flex-1">
                                <span class="font-semibold text-zinc-900">Viticultor individual</span>
                            </div>
                        </label>
                        @if($workType === 'individual')
                            <div class="mt-4">
                                <flux:label for="crew_member_id" class="text-sm" required>Selecciona el viticultor</flux:label>
                                <flux:select wire:model="crew_member_id" id="crew_member_id" data-cy="crew-member-select" class="mt-1">
                                    <option value="">Selecciona un viticultor</option>
                                    @foreach($allViticulturists as $v)
                                        <option value="{{ $v->id }}">{{ $v->name }}@if($v->id === auth()->id()) (Yo)@endif</option>
                                    @endforeach
                                </flux:select>
                                <flux:error name="crew_member_id" />
                            </div>
                        @endif
                    </div>
                </div>
                @error('workType') <p class="mt-2 text-sm text-red-600" data-cy="work-type-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <flux:label for="machinery_id">Maquinaria</flux:label>
                <flux:select wire:model="machinery_id" id="machinery_id" data-cy="machinery-select">
                    <option value="">Sin maquinaria asignada</option>
                    @foreach($machinery as $item)
                        <option value="{{ $item->id }}">{{ $item->name }} ({{ $item->type }})</option>
                    @endforeach
                </flux:select>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <div>
                    <flux:label for="weather_conditions">Condiciones Meteorológicas</flux:label>
                    <flux:input wire:model="weather_conditions" type="text" id="weather_conditions" data-cy="weather-conditions-input" />
                </div>
                <div>
                    <flux:label for="temperature">Temperatura (°C)</flux:label>
                    <flux:input wire:model="temperature" type="number" step="0.1" id="temperature" data-cy="temperature-input" />
                </div>
            </div>
            <div class="mt-6">
                <flux:label for="notes">Notas</flux:label>
                <flux:textarea wire:model="notes" id="notes" data-cy="notes-textarea" rows="3" />
            </div>
        </x-agro.form-section>

        <x-agro.form-actions
            :cancel-url="roleRoute('viticulturist.digital-notebook.pruning.index')"
            submit-label="Actualizar Poda"
            submit-method="update"
        />
    </form>
</x-agro.form-card>
