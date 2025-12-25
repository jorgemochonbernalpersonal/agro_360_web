@php
    $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>';
@endphp

<x-form-card
    title="Registrar Tratamiento Fitosanitario"
    description="Registra un nuevo tratamiento fitosanitario en el cuaderno digital"
    :icon="$icon"
    icon-color="from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]"
    :back-url="route('viticulturist.digital-notebook')"
>
    <form wire:submit="save" class="space-y-8" data-cy="treatment-form">
        <x-form-section title="Información Básica" color="green">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Parcela -->
                    <div>
                        <x-label for="plot_id" required>Parcela</x-label>
                        <x-select 
                            wire:model.live="plot_id" 
                            id="plot_id"
                            data-cy="plot-select"
                            :error="$errors->first('plot_id')"
                            required
                        >
                            <option value="">Selecciona una parcela</option>
                            @foreach($plots as $plot)
                                <option value="{{ $plot->id }}">{{ $plot->name }}</option>
                            @endforeach
                        </x-select>
                    </div>

                    <!-- Plantación -->
                    @if($plot_id)
                        <div>
                            <x-label for="plot_planting_id" :required="count($availablePlantings) > 0">
                                Plantación
                                @if(count($availablePlantings) > 0)
                                    <span class="text-red-500">*</span>
                                @else
                                    <span class="text-gray-500 text-sm">(Opcional - Parcela sin plantaciones activas)</span>
                                @endif
                            </x-label>
                            <x-select 
                                wire:model="plot_planting_id" 
                                id="plot_planting_id"
                                data-cy="plot-planting-select"
                                :error="$errors->first('plot_planting_id')"
                                :required="count($availablePlantings) > 0"
                            >
                                <option value="">-- Selecciona una plantación --</option>
                                @foreach($availablePlantings as $planting)
                                    <option value="{{ $planting->id }}">
                                        {{ $planting->name }}
                                        @if($planting->grapeVariety)
                                            - {{ $planting->grapeVariety->name }}
                                        @endif
                                        @if($planting->area_planted)
                                            ({{ number_format($planting->area_planted, 2) }} ha)
                                        @endif
                                    </option>
                                @endforeach
                            </x-select>
                            @if(count($availablePlantings) === 0)
                                <p class="text-sm text-gray-500 mt-1">
                                    Esta parcela no tiene plantaciones activas. Puedes crear una desde la gestión de parcelas.
                                </p>
                            @endif
                        </div>
                    @endif

                    <!-- Fecha -->
                    <div>
                        <x-label for="activity_date" required>Fecha del Tratamiento</x-label>
                        <x-input 
                            wire:model="activity_date" 
                            type="date" 
                            id="activity_date"
                            data-cy="activity-date-input"
                            :error="$errors->first('activity_date')"
                            required
                        />
                    </div>

                    {{-- Estadio Fenológico --}}
                    <div>
                        <x-label for="phenological_stage">Estadio Fenológico</x-label>
                        <x-select 
                            wire:model="phenological_stage" 
                            id="phenological_stage" 
                            data-cy="phenological-stage-select"
                            :error="$errors->first('phenological_stage')"
                        >
                            <option value="">Selecciona un estadio</option>
                            <option value="Brotación">Brotación</option>
                            <option value="Desarrollo vegetativo">Desarrollo vegetativo</option>
                            <option value="Floración">Floración</option>
                            <option value="Cuajado">Cuajado</option>
                            <option value="Envero">Envero</option>
                            <option value="Maduración">Maduración</option>
                            <option value="Vendimia">Vendimia</option>
                            <option value="Caída de hoja">Caída de hoja</option>
                            <option value="Reposo invernal">Reposo invernal</option>
                        </x-select>
                        <p class="text-xs text-gray-500 mt-1">Recomendado para trazabilidad PAC</p>
                    </div>
                </div>
        </x-form-section>

        <x-form-section title="Producto Fitosanitario" color="green">
                <div class="flex flex-col md:flex-row md:items-end gap-6">
                    <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Producto -->
                        <div>
                            <x-label for="product_id" required>Producto</x-label>
                            <x-select 
                                wire:model="product_id" 
                                id="product_id"
                                data-cy="product-select"
                                :error="$errors->first('product_id')"
                                required
                            >
                                <option value="">Selecciona un producto</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}">
                                        {{ $product->name }}
                                        @if($product->active_ingredient)
                                            ({{ $product->active_ingredient }})
                                        @endif
                                    </option>
                                @endforeach
                            </x-select>
                        </div>

                        <!-- Plaga/Enfermedad Objetivo -->
                        <div>
                            <x-label for="pest_id">Plaga/Enfermedad Objetivo</x-label>
                            <x-select 
                                wire:model="pest_id" 
                                id="pest_id"
                                data-cy="pest-select"
                                :error="$errors->first('pest_id')"
                            >
                                <option value="">Selecciona una plaga/enfermedad</option>
                                @foreach($pests as $pest)
                                    <option value="{{ $pest->id }}">
                                        {{ $pest->name }}
                                        @if($pest->scientific_name)
                                            ({{ $pest->scientific_name }})
                                        @endif
                                    </option>
                                @endforeach
                            </x-select>
                            <p class="text-xs text-gray-500 mt-1">Selecciona la plaga o enfermedad que estás tratando</p>
                        </div>
                    </div>

                    <div class="md:w-auto">
                        <a href="{{ route('viticulturist.phytosanitary-products.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-gray-300 text-gray-700 hover:bg-gray-50 transition-all text-sm font-semibold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Nuevo producto
                        </a>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                    <!-- Dosis por Hectárea -->
                    <div>
                        <x-label for="dose_per_hectare">Dosis por Hectárea (L/ha o kg/ha)</x-label>
                        <x-input 
                            wire:model.live="dose_per_hectare" 
                            type="number" 
                            step="0.001"
                            id="dose_per_hectare"
                            data-cy="dose-per-hectare-input"
                            placeholder="0.000"
                            :error="$errors->first('dose_per_hectare')"
                        />
                    </div>

                    <!-- Área Tratada -->
                    <div>
                        <x-label for="area_treated">Área Tratada (ha)</x-label>
                        <x-input 
                            wire:model.live="area_treated" 
                            type="number" 
                            step="0.001"
                            id="area_treated"
                            data-cy="area-treated-input"
                            placeholder="0.000"
                            :error="$errors->first('area_treated')"
                        />
                    </div>

                    <!-- Dosis Total (calculada) -->
                    <div>
                        <x-label for="total_dose">Dosis Total (calculada)</x-label>
                        <x-input 
                            wire:model="total_dose" 
                            type="number" 
                            step="0.001"
                            id="total_dose"
                            placeholder="0.000"
                            class="bg-gray-50"
                            readonly
                        />
                        <p class="mt-1 text-xs text-gray-500">Se calcula automáticamente</p>
                    </div>
                </div>

                <!-- Método de Aplicación -->
                <div class="mt-6">
                    <x-label for="application_method">Método de Aplicación</x-label>
                        <x-select 
                            wire:model="application_method" 
                            id="application_method"
                            data-cy="application-method-select"
                            :error="$errors->first('application_method')"
                        >
                        <option value="">Selecciona un método</option>
                        <option value="pulverización">Pulverización</option>
                        <option value="aplicación foliar">Aplicación Foliar</option>
                        <option value="aplicación al suelo">Aplicación al Suelo</option>
                        <option value="inyección">Inyección</option>
                        <option value="otro">Otro</option>
                    </x-select>
                </div>
                
                {{-- Safety Interval Information --}}
                @if($this->selectedProduct && $this->selectedProduct->withdrawal_period_days)
                    <div class="mt-6 p-4 bg-amber-50 border-l-4 border-amber-500 rounded-r-lg">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            <div class="flex-1">
                                <h4 class="text-sm font-semibold text-amber-900">Plazo de Seguridad</h4>
                                <p class="text-sm text-amber-800 mt-1">
                                    <span class="font-semibold">{{ $this->selectedProduct->withdrawal_period_days }} días</span> entre aplicación y cosecha
                                </p>
                                @if($activity_date)
                                    @php
                                        $safeDate = \Carbon\Carbon::parse($activity_date)->addDays($this->selectedProduct->withdrawal_period_days);
                                    @endphp
                                    <p class="text-xs text-amber-700 mt-2">
                                        🍇 Podrá cosechar a partir del: <span class="font-semibold">{{ $safeDate->format('d/m/Y') }}</span>
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                @elseif($this->selectedProduct && !$this->selectedProduct->withdrawal_period_days)
                    <div class="mt-6 p-4 bg-gray-50 border-l-4 border-gray-400 rounded-r-lg">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-gray-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <div class="flex-1">
                                <h4 class="text-sm font-semibold text-gray-700">Sin Plazo Definido</h4>
                                <p class="text-xs text-gray-600 mt-1">
                                    Este producto no tiene plazo de seguridad registrado. Consulta la etiqueta del producto o actualiza la información.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
        </x-form-section>

        {{-- Sección PAC Obligatoria --}}
        <x-form-section title="Cumplimiento PAC (Obligatorio)" color="amber">
            <div class="space-y-6">
                {{-- Justificación del Tratamiento --}}
                <div>
                    <x-label for="treatment_justification" required>
                        Justificación del Tratamiento
                        <span class="text-xs text-gray-500">(Plaga o enfermedad detectada)</span>
                    </x-label>
                    <x-textarea 
                        wire:model="treatment_justification" 
                        id="treatment_justification"
                        rows="3"
                        placeholder="Ej: Detección de mildiu en las hojas de la parte superior. Presencia de manchas amarillentas..."
                        :error="$errors->first('treatment_justification')"
                        required
                    />
                    <p class="mt-1 text-xs text-gray-500">
                        ⚠️ <strong>Campo PAC obligatorio:</strong> Describe la plaga o enfermedad que motiva el tratamiento.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- Número ROPO del Aplicador --}}
                    <div>
                        <x-label for="applicator_ropo_number">
                            Número ROPO del Aplicador
                            <span class="text-xs text-gray-500">(Recomendado)</span>
                        </x-label>
                        <x-input 
                            wire:model="applicator_ropo_number" 
                            type="text" 
                            id="applicator_ropo_number"
                            placeholder="Ej: ES12345678"
                            maxlength="50"
                            :error="$errors->first('applicator_ropo_number')"
                        />
                        <p class="mt-1 text-xs text-gray-500">
                            Registro Oficial de Productores y Operadores
                        </p>
                    </div>

                    {{-- Plazo de Reentrada --}}
                    <div>
                        <x-label for="reentry_period_days" required>
                            Plazo de Reentrada (días)
                        </x-label>
                        <x-input 
                            wire:model="reentry_period_days" 
                            type="number" 
                            id="reentry_period_days"
                            placeholder="Ej: 3"
                            min="0"
                            step="1"
                            :error="$errors->first('reentry_period_days')"
                            required
                        />
                        <p class="mt-1 text-xs text-gray-500">
                            Días sin acceso a la parcela tras la aplicación
                        </p>
                    </div>

                    {{-- Volumen de Caldo --}}
                    <div>
                        <x-label for="spray_volume" required>
                            Volumen de Caldo (L)
                        </x-label>
                        <x-input 
                            wire:model="spray_volume" 
                            type="number" 
                            id="spray_volume"
                            placeholder="Ej: 500.00"
                            min="0.01"
                            step="0.01"
                            :error="$errors->first('spray_volume')"
                            required
                        />
                        <p class="mt-1 text-xs text-gray-500">
                            Litros totales de caldo aplicados
                        </p>
                    </div>
                </div>

                {{-- Info box PAC --}}
                <div class="p-4 bg-amber-50 border-l-4 border-amber-500 rounded-r-lg">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <div class="flex-1">
                            <h4 class="text-sm font-semibold text-amber-900">Información PAC</h4>
                            <p class="text-sm text-amber-800 mt-1">
                                Estos campos son obligatorios según la normativa de la Política Agraria Común (PAC) y el RD 1311/2012 
                                sobre uso sostenible de productos fitosanitarios.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </x-form-section>

        <x-form-section title="Condiciones Meteorológicas" color="green">
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Temperatura -->
                    <div>
                        <x-label for="temperature">Temperatura (°C)</x-label>
                        <x-input 
                            wire:model="temperature" 
                            type="number" 
                            step="0.1"
                            id="temperature"
                            placeholder="20.0"
                            :error="$errors->first('temperature')"
                        />
                    </div>

                    <!-- Velocidad del Viento -->
                    <div>
                        <x-label for="wind_speed">Velocidad del Viento (km/h)</x-label>
                        <x-input 
                            wire:model="wind_speed" 
                            type="number" 
                            step="0.1"
                            id="wind_speed"
                            placeholder="0.0"
                            :error="$errors->first('wind_speed')"
                        />
                    </div>

                    <!-- Humedad -->
                    <div>
                        <x-label for="humidity">Humedad Relativa (%)</x-label>
                        <x-input 
                            wire:model="humidity" 
                            type="number" 
                            step="0.1"
                            min="0"
                            max="100"
                            id="humidity"
                            placeholder="0.0"
                            :error="$errors->first('humidity')"
                        />
                    </div>
                </div>

                <!-- Condiciones Generales -->
                <div class="mt-6">
                    <x-label for="weather_conditions">Condiciones Meteorológicas Generales</x-label>
                    <x-input 
                        wire:model="weather_conditions" 
                        type="text" 
                        id="weather_conditions"
                        placeholder="Ej: Soleado, nublado, etc."
                        :error="$errors->first('weather_conditions')"
                    />
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
                    <x-select 
                        wire:model="machinery_id" 
                        id="machinery_id"
                        :error="$errors->first('machinery_id')"
                    >
                        <option value="">Sin maquinaria asignada</option>
                        @foreach($machinery as $item)
                            <option value="{{ $item->id }}">{{ $item->name }} ({{ $item->type }})</option>
                        @endforeach
                    </x-select>
                </div>

                <!-- Notas -->
                <div class="mt-6">
                    <x-label for="notes">Notas Adicionales</x-label>
                    <x-textarea 
                        wire:model="notes" 
                        id="notes"
                        rows="4"
                        placeholder="Observaciones, comentarios, etc."
                        :error="$errors->first('notes')"
                    />
                </div>
        </x-form-section>

        <x-form-actions 
            :cancel-url="route('viticulturist.digital-notebook')"
            submit-label="Registrar Tratamiento"
        />
    </form>
</x-form-card>

