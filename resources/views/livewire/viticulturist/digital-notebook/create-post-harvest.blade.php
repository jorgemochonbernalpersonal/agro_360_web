<x-agro.form-card
    :title="__('Registrar Tratamiento Post-Vendimia')"
    :description="__('Tratamientos aplicados después de la cosecha (cobre, azufre, sellado de heridas...)')"
    :back-url="roleRoute('viticulturist.digital-notebook.post-harvest.index')"
>
    <form wire:submit="save" class="space-y-8">
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
                    <flux:label required>{{ __('Fecha del tratamiento') }}</flux:label>
                    <flux:input wire:model="activity_date" type="date" data-cy="activity-date-input" />
                    <flux:error name="activity_date" />
                </flux:field>
                <flux:field>
                    <flux:label required>{{ __('Estadio Fenológico') }}</flux:label>
                    <flux:select wire:model="phenological_stage" data-cy="phenological-stage-select">
                        <option value="">{{ __('Selecciona un estadio') }}</option>
                        <option value="Caída de hoja">{{ __('Caída de hoja') }}</option>
                        <option value="Reposo invernal">{{ __('Reposo invernal') }}</option>
                        <option value="Vendimia">{{ __('Post-vendimia / Vendimia') }}</option>
                    </flux:select>
                    <flux:error name="phenological_stage" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-section :title="__('Datos del Tratamiento')">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:field>
                    <flux:label required>{{ __('Tipo de Aplicación') }}</flux:label>
                    <flux:select wire:model="application_type" data-cy="application-type-select">
                        <option value="">{{ __('Selecciona un tipo') }}</option>
                        @foreach($applicationTypes as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="application_type" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Producto Fitosanitario') }}</flux:label>
                    <flux:select wire:model="product_id" data-cy="product-select">
                        <option value="">{{ __('Sin producto específico') }}</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="product_id" />
                </flux:field>
                <flux:field>
                    <flux:label required>{{ __('Superficie Tratada') }}</flux:label>
                    <flux:input wire:model="treated_area_ha" type="number" step="0.001" min="0.001" data-cy="treated-area-input" placeholder="0.000" />
                    <flux:description>ha</flux:description>
                    <flux:error name="treated_area_ha" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Volumen de Caldo') }}</flux:label>
                    <flux:input wire:model="water_volume_liters" type="number" step="0.1" min="0" data-cy="water-volume-input" placeholder="0.0" />
                    <flux:description>L</flux:description>
                    <flux:error name="water_volume_liters" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Dosis / ha') }}</flux:label>
                    <flux:input wire:model="dose_per_hectare" type="number" step="0.001" min="0" data-cy="dose-per-hectare-input" placeholder="0.000" />
                    <flux:error name="dose_per_hectare" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Unidad de Dosis') }}</flux:label>
                    <flux:select wire:model="dose_unit" data-cy="dose-unit-select">
                        <option value="kg/ha">kg/ha</option>
                        <option value="L/ha">L/ha</option>
                        <option value="g/ha">g/ha</option>
                        <option value="mL/ha">mL/ha</option>
                    </flux:select>
                </flux:field>
            </div>

            <div class="mt-6 border-t pt-6">
                <h4 class="text-sm font-semibold text-zinc-700 mb-4">{{ __('Seguridad Laboral (PAC)') }}</h4>
                <div class="max-w-xs">
                    <flux:field>
                        <flux:label>{{ __('Plazo de reentrada') }}</flux:label>
                        <flux:input wire:model="reentry_interval_hours" type="number" min="0" max="168" data-cy="reentry-interval-input" :placeholder="__('Ej: 24')" />
                        <flux:description>{{ __('horas — 0 = sin restricción; máx. 168 h tras la aplicación') }}</flux:description>
                        <flux:error name="reentry_interval_hours" />
                    </flux:field>
                </div>
            </div>
        </x-agro.form-section>

        <x-agro.form-section :title="__('Personal y Maquinaria')">
            <div class="mb-6">
                <flux:label class="mb-3 block font-semibold text-zinc-700">{{ __('¿Quién realizó el tratamiento?') }}</flux:label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="border-2 rounded-lg p-4 transition-all {{ $workType === 'crew' ? 'border-agro-500 bg-agro-50' : 'border-zinc-200 hover:border-zinc-300' }}">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="radio" wire:model.live="workType" value="crew" data-cy="work-type-crew" class="w-5 h-5 text-agro-500 focus:ring-agro-500" />
                            <div class="flex-1">
                                <span class="font-semibold text-zinc-900">{{ __('Equipo completo') }}</span>
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
            </flux:field>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <flux:field>
                    <flux:label>{{ __('Condiciones Meteorológicas') }}</flux:label>
                    <flux:input wire:model="weather_conditions" type="text" data-cy="weather-conditions-input" :placeholder="__('Ej: Soleado, nublado')" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Temperatura') }}</flux:label>
                    <flux:input wire:model="temperature" type="number" step="0.1" data-cy="temperature-input" placeholder="20.0" />
                    <flux:description>°C</flux:description>
                </flux:field>
            </div>
            <div class="mt-6">
                <flux:field>
                    <flux:label>{{ __('Notas') }}</flux:label>
                    <flux:textarea wire:model="notes" data-cy="notes-textarea" rows="3" :placeholder="__('Observaciones adicionales...')" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-actions
            :cancel-url="roleRoute('viticulturist.digital-notebook.post-harvest.index')"
            :submit-label="__('Registrar Tratamiento')"
        />
    </form>
</x-agro.form-card>
