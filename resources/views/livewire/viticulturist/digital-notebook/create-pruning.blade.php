<x-agro.form-card
    title="Registrar Poda"
    description="Registra una operación de poda en el cuaderno digital"
    :back-url="route('viticulturist.digital-notebook.pruning.index')"
>
    <form wire:submit="save" class="space-y-8">
        <x-agro.form-section title="Información Básica">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <flux:label for="plot_id" required>Parcela</flux:label>
                    <flux:select wire:model.live="plot_id" id="plot_id" :error="$errors->first('plot_id')" required>
                        <option value="">Selecciona una parcela</option>
                        @foreach($plots as $plot)
                            <option value="{{ $plot->id }}">{{ $plot->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="plot_id" />
                </div>
                @if($plot_id)
                    <div>
                        <flux:label for="plot_planting_id">Plantación</flux:label>
                        <flux:select wire:model="plot_planting_id" id="plot_planting_id">
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
                    <flux:input wire:model="activity_date" type="date" id="activity_date" required />
                    <flux:error name="activity_date" />
                </div>
                <div>
                    <flux:label for="phenological_stage" required>Estadio Fenológico</flux:label>
                    <flux:select wire:model="phenological_stage" id="phenological_stage" required>
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
                    <flux:select wire:model="pruning_type" id="pruning_type" required>
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
                    <flux:input wire:model="productive_buds_per_hectare" type="number" min="0" id="productive_buds_per_hectare" placeholder="Ej: 40000" />
                    <p class="text-xs text-zinc-500 mt-1">Yemas productivas resultantes de la poda</p>
                    <flux:error name="productive_buds_per_hectare" />
                </div>
                <div>
                    <flux:label for="hours_worked">Horas Trabajadas</flux:label>
                    <flux:input wire:model="hours_worked" type="number" step="0.5" min="0" id="hours_worked" placeholder="0.0" />
                    <flux:error name="hours_worked" />
                </div>
                <div>
                    <flux:label for="workers_count">Número de Trabajadores</flux:label>
                    <flux:input wire:model="workers_count" type="number" min="1" id="workers_count" placeholder="0" />
                    <flux:error name="workers_count" />
                </div>
            </div>
            <div class="mt-6">
                <flux:label for="description" required>Descripción</flux:label>
                <flux:textarea wire:model="description" id="description" rows="4" placeholder="Descripción detallada de la poda realizada..." required />
                <flux:error name="description" />
            </div>
        </x-agro.form-section>

        <x-agro.form-section title="Personal y Maquinaria">
            <div class="mb-6">
                <flux:label class="mb-3 block font-semibold text-zinc-700">¿Quién realizó la poda?</flux:label>
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
                                <flux:label for="crew_id" class="text-sm" required>Selecciona el equipo</flux:label>
                                <flux:select wire:model="crew_id" id="crew_id" class="mt-1">
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
                            <input type="radio" wire:model.live="workType" value="individual" class="w-5 h-5 text-agro-500 focus:ring-agro-500" />
                            <div class="flex-1">
                                <span class="font-semibold text-zinc-900">Viticultor individual</span>
                                <p class="text-sm text-zinc-500 mt-1">Un viticultor específico realizó el trabajo</p>
                            </div>
                        </label>
                        @if($workType === 'individual')
                            <div class="mt-4">
                                <flux:label for="crew_member_id" class="text-sm" required>Selecciona el viticultor</flux:label>
                                <flux:select wire:model="crew_member_id" id="crew_member_id" class="mt-1">
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
                @error('workType') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <flux:label for="machinery_id">Maquinaria</flux:label>
                <flux:select wire:model="machinery_id" id="machinery_id">
                    <option value="">Sin maquinaria asignada</option>
                    @foreach($machinery as $item)
                        <option value="{{ $item->id }}">{{ $item->name }} ({{ $item->type }})</option>
                    @endforeach
                </flux:select>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <div>
                    <flux:label for="weather_conditions">Condiciones Meteorológicas</flux:label>
                    <flux:input wire:model="weather_conditions" type="text" id="weather_conditions" placeholder="Ej: Soleado, nublado" />
                </div>
                <div>
                    <flux:label for="temperature">Temperatura (°C)</flux:label>
                    <flux:input wire:model="temperature" type="number" step="0.1" id="temperature" placeholder="20.0" />
                </div>
            </div>
            <div class="mt-6">
                <flux:label for="notes">Notas</flux:label>
                <flux:textarea wire:model="notes" id="notes" rows="3" placeholder="Observaciones adicionales..." />
            </div>
        </x-agro.form-section>

        <x-agro.form-actions
            :cancel-url="route('viticulturist.digital-notebook.pruning.index')"
            submit-label="Registrar Poda"
        />
    </form>
</x-agro.form-card>
