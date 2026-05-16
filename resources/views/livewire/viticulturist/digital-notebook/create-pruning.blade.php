<x-agro.form-card
    title="Registrar Poda"
    description="Registra una operación de poda en el cuaderno digital"
    :back-url="roleRoute('viticulturist.digital-notebook.pruning.index')"
>
    <form wire:submit="save" class="space-y-8">
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
                        <flux:label>Plantación</flux:label>
                        <flux:select wire:model="plot_planting_id" data-cy="planting-select">
                            <option value="">-- Selecciona una plantación --</option>
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
                    <flux:label required>Fecha de poda</flux:label>
                    <flux:input wire:model="activity_date" type="date" data-cy="activity-date-input" />
                    <flux:error name="activity_date" />
                </flux:field>
                <flux:field>
                    <flux:label required>Estadio Fenológico</flux:label>
                    <flux:select wire:model="phenological_stage" data-cy="phenological-stage-select">
                        <option value="">Selecciona un estadio</option>
                        <option value="Reposo invernal">Reposo invernal</option>
                        <option value="Brotación">Brotación</option>
                        <option value="Desarrollo vegetativo">Desarrollo vegetativo</option>
                        <option value="Caída de hoja">Caída de hoja</option>
                    </flux:select>
                    <flux:error name="phenological_stage" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-section title="Datos de la Poda">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:field>
                    <flux:label required>Tipo de Poda</flux:label>
                    <flux:select wire:model="pruning_type" data-cy="pruning-type-select">
                        <option value="">Selecciona un tipo</option>
                        <option value="guyot">Guyot</option>
                        <option value="doble_guyot">Doble Guyot</option>
                        <option value="vaso">Vaso</option>
                        <option value="cordon">Cordón</option>
                        <option value="otro">Otro</option>
                    </flux:select>
                    <flux:error name="pruning_type" />
                </flux:field>
                <flux:field>
                    <flux:label>Yemas Productivas / ha</flux:label>
                    <flux:input wire:model="productive_buds_per_hectare" type="number" min="0" data-cy="productive-buds-input" placeholder="Ej: 40000" />
                    <flux:description>Yemas productivas resultantes de la poda</flux:description>
                    <flux:error name="productive_buds_per_hectare" />
                </flux:field>
                <flux:field>
                    <flux:label>Horas Trabajadas</flux:label>
                    <flux:input wire:model="hours_worked" type="number" step="0.5" min="0" data-cy="hours-worked-input" placeholder="0.0" />
                    <flux:error name="hours_worked" />
                </flux:field>
                <flux:field>
                    <flux:label>Número de Trabajadores</flux:label>
                    <flux:input wire:model="workers_count" type="number" min="1" data-cy="workers-count-input" placeholder="0" />
                    <flux:error name="workers_count" />
                </flux:field>
            </div>
            <div class="mt-6">
                <flux:field>
                    <flux:label required>Descripción</flux:label>
                    <flux:textarea wire:model="description" data-cy="description-textarea" rows="4" placeholder="Descripción detallada de la poda realizada..." />
                    <flux:error name="description" />
                </flux:field>
            </div>

            <div class="mt-6 border-t pt-6">
                <h4 class="text-sm font-semibold text-zinc-700 mb-4">Gestión de Restos de Poda (BCAM 6 — PAC)</h4>
                <flux:field>
                    <flux:label>Gestión del ramón de poda</flux:label>
                    <flux:select wire:model="residue_management" data-cy="residue-management-select">
                        <option value="">-- Sin especificar --</option>
                        <option value="triturado_incorporado">Triturado e incorporado al suelo</option>
                        <option value="triturado_superficie">Triturado en superficie</option>
                        <option value="retirado">Retirado de la parcela</option>
                        <option value="quemado">Quemado (con autorización)</option>
                        <option value="otro">Otro</option>
                    </flux:select>
                    <flux:description>Cumplimiento BCAM 6 — gestión de residuos de poda</flux:description>
                    <flux:error name="residue_management" />
                </flux:field>
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
                                <flux:field>
                                    <flux:label>Selecciona el equipo</flux:label>
                                    <flux:select wire:model="crew_id" data-cy="crew-select">
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
                            <input type="radio" wire:model.live="workType" value="individual" data-cy="work-type-individual" class="w-5 h-5 text-agro-500 focus:ring-agro-500" />
                            <div class="flex-1">
                                <span class="font-semibold text-zinc-900">Viticultor individual</span>
                                <p class="text-sm text-zinc-500 mt-1">Un viticultor específico realizó el trabajo</p>
                            </div>
                        </label>
                        @if($workType === 'individual')
                            <div class="mt-4">
                                <flux:field>
                                    <flux:label>Selecciona el viticultor</flux:label>
                                    <flux:select wire:model="crew_member_id" data-cy="crew-member-select">
                                        <option value="">Selecciona un viticultor</option>
                                        @foreach($allViticulturists as $v)
                                            <option value="{{ $v->id }}">{{ $v->name }}@if($v->id === auth()->id()) (Yo)@endif</option>
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
                <flux:label>Maquinaria</flux:label>
                <flux:select wire:model="machinery_id" data-cy="machinery-select">
                    <option value="">Sin maquinaria asignada</option>
                    @foreach($machinery as $item)
                        <option value="{{ $item->id }}">{{ $item->name }} ({{ $item->type }})</option>
                    @endforeach
                </flux:select>
            </flux:field>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <flux:field>
                    <flux:label>Condiciones Meteorológicas</flux:label>
                    <flux:input wire:model="weather_conditions" type="text" data-cy="weather-conditions-input" placeholder="Ej: Soleado, nublado" />
                </flux:field>
                <flux:field>
                    <flux:label>Temperatura</flux:label>
                    <flux:input wire:model="temperature" type="number" step="0.1" data-cy="temperature-input" placeholder="20.0" />
                    <flux:description>°C</flux:description>
                </flux:field>
            </div>
            <div class="mt-6">
                <flux:field>
                    <flux:label>Notas</flux:label>
                    <flux:textarea wire:model="notes" data-cy="notes-textarea" rows="3" placeholder="Observaciones adicionales..." />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-actions
            :cancel-url="roleRoute('viticulturist.digital-notebook.pruning.index')"
            submit-label="Registrar Poda"
        />
    </form>
</x-agro.form-card>
