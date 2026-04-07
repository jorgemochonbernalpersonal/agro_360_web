<x-agro.form-card
    title="Editar Tratamiento Post-Vendimia"
    description="Modifica los datos del tratamiento post-vendimia"
    :back-url="roleRoute('viticulturist.digital-notebook.post-harvest.index')"
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
                    <flux:label for="activity_date" required>Fecha del tratamiento</flux:label>
                    <flux:input wire:model="activity_date" type="date" id="activity_date" data-cy="activity-date-input" required />
                    <flux:error name="activity_date" />
                </div>
                <div>
                    <flux:label for="phenological_stage" required>Estadio Fenológico</flux:label>
                    <flux:select wire:model="phenological_stage" id="phenological_stage" data-cy="phenological-stage-select" required>
                        <option value="">Selecciona un estadio</option>
                        <option value="Caída de hoja">Caída de hoja</option>
                        <option value="Reposo invernal">Reposo invernal</option>
                        <option value="Vendimia">Post-vendimia / Vendimia</option>
                    </flux:select>
                    <flux:error name="phenological_stage" />
                </div>
            </div>
        </x-agro.form-section>

        <x-agro.form-section title="Datos del Tratamiento">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <flux:label for="application_type" required>Tipo de Aplicación</flux:label>
                    <flux:select wire:model="application_type" id="application_type" data-cy="application-type-select" required>
                        <option value="">Selecciona un tipo</option>
                        @foreach($applicationTypes as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="application_type" />
                </div>
                <div>
                    <flux:label for="product_id">Producto Fitosanitario</flux:label>
                    <flux:select wire:model="product_id" id="product_id" data-cy="product-select">
                        <option value="">Sin producto específico</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </flux:select>
                </div>
                <div>
                    <flux:label for="treated_area_ha" required>Superficie Tratada (ha)</flux:label>
                    <flux:input wire:model="treated_area_ha" type="number" step="0.001" min="0.001" id="treated_area_ha" data-cy="treated-area-input" required />
                    <flux:error name="treated_area_ha" />
                </div>
                <div>
                    <flux:label for="water_volume_liters">Volumen de Caldo (L)</flux:label>
                    <flux:input wire:model="water_volume_liters" type="number" step="0.1" min="0" id="water_volume_liters" data-cy="water-volume-input" />
                </div>
                <div>
                    <flux:label for="dose_per_hectare">Dosis / ha</flux:label>
                    <flux:input wire:model="dose_per_hectare" type="number" step="0.001" min="0" id="dose_per_hectare" data-cy="dose-per-hectare-input" />
                </div>
                <div>
                    <flux:label for="dose_unit">Unidad de Dosis</flux:label>
                    <flux:select wire:model="dose_unit" id="dose_unit" data-cy="dose-unit-select">
                        <option value="kg/ha">kg/ha</option>
                        <option value="L/ha">L/ha</option>
                        <option value="g/ha">g/ha</option>
                        <option value="mL/ha">mL/ha</option>
                    </flux:select>
                </div>
            </div>

            {{-- Plazo de reentrada — Seguridad laboral PAC --}}
            <div class="mt-6 border-t pt-6">
                <h4 class="text-sm font-semibold text-zinc-700 mb-4">Seguridad Laboral (PAC)</h4>
                <div class="max-w-xs">
                    <flux:label for="reentry_interval_hours">Plazo de reentrada (horas)</flux:label>
                    <flux:input wire:model="reentry_interval_hours" type="number" min="0" max="168" id="reentry_interval_hours" data-cy="reentry-interval-input" placeholder="Ej: 24" />
                    <p class="text-xs text-zinc-500 mt-1">Horas que no se puede acceder a la parcela tras la aplicación (0 = sin restricción)</p>
                    <flux:error name="reentry_interval_hours" />
                </div>
            </div>
        </x-agro.form-section>

        <x-agro.form-section title="Personal y Maquinaria">
            <div class="mb-6">
                <flux:label class="mb-3 block font-semibold text-zinc-700">¿Quién realizó el tratamiento?</flux:label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="border-2 rounded-lg p-4 transition-all {{ $workType === 'crew' ? 'border-agro-500 bg-agro-50' : 'border-zinc-200 hover:border-zinc-300' }}">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="radio" wire:model.live="workType" value="crew" data-cy="work-type-crew" class="w-5 h-5 text-agro-500 focus:ring-agro-500" />
                            <div class="flex-1">
                                <span class="font-semibold text-zinc-900">Equipo completo</span>
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
            :cancel-url="roleRoute('viticulturist.digital-notebook.post-harvest.index')"
            submit-label="Actualizar Tratamiento"
            submit-method="update"
        />
    </form>
</x-agro.form-card>
