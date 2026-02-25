<x-agro.form-card
    title="Editar Tratamiento Fitosanitario"
    description="Modifica los datos del tratamiento fitosanitario"
    :back-url="route('viticulturist.digital-notebook')"
>
    <form wire:submit="update" class="space-y-8" data-cy="treatment-form">
        <x-agro.form-section title="Información Básica">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Parcela -->
                    <div>
                        <flux:label for="plot_id" required>Parcela</flux:label>
                        <flux:select
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
                        </flux:select>
                    </div>

                    <!-- Plantación -->
                    @if($plot_id)
                        <div>
                            <flux:label for="plot_planting_id" :required="count($availablePlantings) > 0">
                                Plantación
                                @if(count($availablePlantings) > 0)
                                    <span class="text-red-500">*</span>
                                @else
                                    <span class="text-zinc-500 text-sm">(Opcional - Parcela sin plantaciones activas)</span>
                                @endif
                            </flux:label>
                            <flux:select
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
                            </flux:select>
                            @if(count($availablePlantings) === 0)
                                <p class="text-sm text-zinc-500 mt-1">
                                    Esta parcela no tiene plantaciones activas. Puedes crear una desde la gestión de parcelas.
                                </p>
                            @endif
                        </div>
                    @endif

                    <!-- Fecha -->
                    <div>
                        <flux:label for="activity_date" required>Fecha del Tratamiento</flux:label>
                        <flux:input
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
                        <flux:label for="phenological_stage">Estadio Fenológico</flux:label>
                        <flux:select
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
                        </flux:select>
                        <p class="text-xs text-zinc-500 mt-1">Recomendado para trazabilidad PAC</p>
                    </div>
                </div>
        </x-agro.form-section>

        <x-agro.form-section title="Producto Fitosanitario">
                <div class="flex flex-col md:flex-row md:items-end gap-6">
                    <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Producto -->
                        <div>
                            <flux:label for="product_id" required>Producto</flux:label>
                            <flux:select
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
                            </flux:select>
                        </div>

                        <!-- Plaga/Enfermedad Objetivo -->
                        <div>
                            <flux:label for="pest_id">Plaga/Enfermedad Objetivo</flux:label>
                            <flux:select
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
                            </flux:select>
                            <p class="text-xs text-zinc-500 mt-1">Selecciona la plaga o enfermedad que estás tratando</p>
                        </div>
                    </div>

                    <div class="md:w-auto">
                        <flux:button href="{{ route('viticulturist.phytosanitary-products.create') }}" variant="ghost" icon="plus">
                            Nuevo producto
                        </flux:button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                    <!-- Dosis por Hectárea -->
                    <div>
                        <flux:label for="dose_per_hectare">Dosis por Hectárea (L/ha o kg/ha)</flux:label>
                        <flux:input
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
                        <flux:label for="area_treated">Área Tratada (ha)</flux:label>
                        <flux:input
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
                        <flux:label for="total_dose">Dosis Total (calculada)</flux:label>
                        <flux:input
                            wire:model="total_dose"
                            type="number"
                            step="0.001"
                            id="total_dose"
                            placeholder="0.000"
                            class="bg-zinc-50"
                            readonly
                        />
                        <p class="mt-1 text-xs text-zinc-500">Se calcula automáticamente</p>
                    </div>
                </div>

                <!-- Método de Aplicación -->
                <div class="mt-6">
                    <flux:label for="application_method">Método de Aplicación</flux:label>
                    <flux:select
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
                    </flux:select>
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
                                        Podrá cosechar a partir del: <span class="font-semibold">{{ $safeDate->format('d/m/Y') }}</span>
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                @elseif($this->selectedProduct && !$this->selectedProduct->withdrawal_period_days)
                    <div class="mt-6 p-4 bg-zinc-50 border-l-4 border-zinc-400 rounded-r-lg">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-zinc-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <div class="flex-1">
                                <h4 class="text-sm font-semibold text-zinc-700">Sin Plazo Definido</h4>
                                <p class="text-xs text-zinc-600 mt-1">
                                    Este producto no tiene plazo de seguridad registrado. Consulta la etiqueta del producto o actualiza la información.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
        </x-agro.form-section>

        {{-- Sección PAC Obligatoria --}}
        <x-agro.form-section title="Cumplimiento PAC (Obligatorio)">
            <div class="space-y-6">
                {{-- Justificación del Tratamiento --}}
                <div>
                    <flux:label for="treatment_justification" required>
                        Justificación del Tratamiento
                        <span class="text-xs text-zinc-500">(Plaga o enfermedad detectada)</span>
                    </flux:label>
                    <flux:textarea
                        wire:model="treatment_justification"
                        id="treatment_justification"
                        rows="3"
                        placeholder="Ej: Detección de mildiu en las hojas de la parte superior. Presencia de manchas amarillentas..."
                        :error="$errors->first('treatment_justification')"
                        required
                    />
                    <p class="mt-1 text-xs text-zinc-500">
                        <strong>Campo PAC obligatorio:</strong> Describe la plaga o enfermedad que motiva el tratamiento.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- Número ROPO del Aplicador --}}
                    <div>
                        <flux:label for="applicator_ropo_number">
                            Número ROPO del Aplicador
                            <span class="text-xs text-zinc-500">(Recomendado)</span>
                        </flux:label>
                        <flux:input
                            wire:model="applicator_ropo_number"
                            type="text"
                            id="applicator_ropo_number"
                            placeholder="Ej: ES12345678"
                            maxlength="50"
                            :error="$errors->first('applicator_ropo_number')"
                        />
                        <p class="mt-1 text-xs text-zinc-500">
                            Registro Oficial de Productores y Operadores
                        </p>
                    </div>

                    {{-- Plazo de Reentrada --}}
                    <div>
                        <flux:label for="reentry_period_days" required>
                            Plazo de Reentrada (días)
                        </flux:label>
                        <flux:input
                            wire:model="reentry_period_days"
                            type="number"
                            id="reentry_period_days"
                            placeholder="Ej: 3"
                            min="0"
                            step="1"
                            :error="$errors->first('reentry_period_days')"
                            required
                        />
                        <p class="mt-1 text-xs text-zinc-500">
                            Días sin acceso a la parcela tras la aplicación
                        </p>
                    </div>

                    {{-- Volumen de Caldo --}}
                    <div>
                        <flux:label for="spray_volume" required>
                            Volumen de Caldo (L)
                        </flux:label>
                        <flux:input
                            wire:model="spray_volume"
                            type="number"
                            id="spray_volume"
                            placeholder="Ej: 500.00"
                            min="0.01"
                            step="0.01"
                            :error="$errors->first('spray_volume')"
                            required
                        />
                        <p class="mt-1 text-xs text-zinc-500">
                            Litros totales de caldo aplicados
                        </p>
                    </div>

                    {{-- Volumen de Caldo por Hectárea --}}
                    <div>
                        <flux:label for="water_volume_liters_ha">
                            Caldo por Hectárea (L/ha)
                        </flux:label>
                        <flux:input
                            wire:model="water_volume_liters_ha"
                            type="number"
                            id="water_volume_liters_ha"
                            placeholder="Ej: 200.00"
                            min="0"
                            step="0.01"
                            :error="$errors->first('water_volume_liters_ha')"
                        />
                        <p class="mt-1 text-xs text-zinc-500">
                            Litros de agua por hectárea tratada
                        </p>
                    </div>
                </div>

                {{-- Asesoramiento técnico --}}
                <div class="mt-6 space-y-4">
                    <div class="flex items-center gap-3">
                        <flux:checkbox wire:model.live="under_advisory" id="under_advisory" />
                        <flux:label for="under_advisory" class="cursor-pointer">
                            Tratamiento bajo asesoramiento técnico cualificado
                            <span class="text-xs text-zinc-500 block">Obligatorio en Producción Integrada (RD 1311/2012 Art. 14)</span>
                        </flux:label>
                    </div>
                    @if($under_advisory)
                        <div class="ml-7">
                            <flux:field>
                                <flux:label for="advisory_action_date" required>Fecha de recomendación del asesor</flux:label>
                                <flux:input wire:model="advisory_action_date" type="date" id="advisory_action_date" />
                                <flux:error name="advisory_action_date" />
                            </flux:field>
                        </div>
                    @endif
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
        </x-agro.form-section>

        {{-- Sección IPM — Gestión Integrada de Plagas --}}
        <x-agro.form-section title="Gestión Integrada de Plagas (IPM)" color="green">
            <flux:callout variant="info" icon="information-circle">
                <strong>RD 1311/2012 — Art. 14:</strong> Documenta los métodos alternativos y preventivos aplicados antes del tratamiento químico. Obligatorio para Producción Integrada y certificaciones ecológicas.
            </flux:callout>

            <div class="mt-5 grid grid-cols-1 md:grid-cols-2 gap-4">
                <label class="flex items-start gap-3 p-3 border border-zinc-200 rounded-lg cursor-pointer hover:bg-zinc-50 transition-colors">
                    <flux:checkbox wire:model="plague_monitoring" id="plague_monitoring" class="mt-0.5" />
                    <div>
                        <span class="text-sm font-medium text-zinc-800">Seguimiento y monitoreo de la plaga</span>
                        <p class="text-xs text-zinc-500 mt-0.5">Trampas, conteos, umbrales de acción</p>
                    </div>
                </label>

                <label class="flex items-start gap-3 p-3 border border-zinc-200 rounded-lg cursor-pointer hover:bg-zinc-50 transition-colors">
                    <flux:checkbox wire:model="prior_non_chemical_methods" id="prior_non_chemical_methods" class="mt-0.5" />
                    <div>
                        <span class="text-sm font-medium text-zinc-800">Métodos no químicos previos</span>
                        <p class="text-xs text-zinc-500 mt-0.5">Se intentaron alternativas antes del químico</p>
                    </div>
                </label>

                <label class="flex items-start gap-3 p-3 border border-zinc-200 rounded-lg cursor-pointer hover:bg-zinc-50 transition-colors">
                    <flux:checkbox wire:model="manual_mechanical_control" id="manual_mechanical_control" class="mt-0.5" />
                    <div>
                        <span class="text-sm font-medium text-zinc-800">Control manual o mecánico</span>
                        <p class="text-xs text-zinc-500 mt-0.5">Retirada manual, trituración, labores mecánicas</p>
                    </div>
                </label>

                <label class="flex items-start gap-3 p-3 border border-zinc-200 rounded-lg cursor-pointer hover:bg-zinc-50 transition-colors">
                    <flux:checkbox wire:model="biological_control" id="biological_control" class="mt-0.5" />
                    <div>
                        <span class="text-sm font-medium text-zinc-800">Control biológico</span>
                        <p class="text-xs text-zinc-500 mt-0.5">Depredadores naturales, hongos entomopatógenos, etc.</p>
                    </div>
                </label>

                <label class="flex items-start gap-3 p-3 border border-zinc-200 rounded-lg cursor-pointer hover:bg-zinc-50 transition-colors md:col-span-2">
                    <flux:checkbox wire:model="cultural_preventions" id="cultural_preventions" class="mt-0.5" />
                    <div>
                        <span class="text-sm font-medium text-zinc-800">Medidas culturales preventivas</span>
                        <p class="text-xs text-zinc-500 mt-0.5">Poda de saneamiento, eliminación de focos, aireación del cultivo, cubierta vegetal</p>
                    </div>
                </label>
            </div>
        </x-agro.form-section>

        <x-agro.form-section title="Condiciones Meteorológicas">

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Temperatura -->
                    <div>
                        <flux:label for="temperature">Temperatura (°C)</flux:label>
                        <flux:input
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
                        <flux:label for="wind_speed">Velocidad del Viento (km/h)</flux:label>
                        <flux:input
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
                        <flux:label for="humidity">Humedad Relativa (%)</flux:label>
                        <flux:input
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
                    <flux:label for="weather_conditions">Condiciones Meteorológicas Generales</flux:label>
                    <flux:input
                        wire:model="weather_conditions"
                        type="text"
                        id="weather_conditions"
                        placeholder="Ej: Soleado, nublado, etc."
                        :error="$errors->first('weather_conditions')"
                    />
                </div>
        </x-agro.form-section>

        <x-agro.form-section title="Información Adicional">
                <!-- ¿Quién realizó el trabajo? -->
                <div class="mb-6">
                    <flux:label class="mb-3 block font-semibold text-zinc-700">¿Quién realizó el trabajo?</flux:label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Opción: Equipo completo -->
                        <div class="border-2 rounded-lg p-4 transition-all {{ $workType === 'crew' ? 'border-agro-500 bg-agro-50' : 'border-zinc-200 hover:border-zinc-300' }}">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input
                                    type="radio"
                                    wire:model.live="workType"
                                    value="crew"
                                    class="w-5 h-5 text-agro-500 focus:ring-agro-500"
                                />
                                <div class="flex-1">
                                    <span class="font-semibold text-zinc-900">Equipo completo</span>
                                    <p class="text-sm text-zinc-500 mt-1">Todo el equipo trabajó en esta actividad</p>
                                </div>
                            </label>
                            @if($workType === 'crew')
                                <div class="mt-4">
                                    <flux:label for="crew_id" class="text-sm">Selecciona el equipo</flux:label>
                                    <flux:select
                                        wire:model="crew_id"
                                        id="crew_id"
                                        class="mt-1"
                                        :error="$errors->first('crew_id')"
                                    >
                                        <option value="">Selecciona un equipo</option>
                                        @foreach($crews as $crew)
                                            <option value="{{ $crew->id }}">{{ $crew->name }}</option>
                                        @endforeach
                                    </flux:select>
                                </div>
                            @endif
                        </div>

                        <!-- Opción: Viticultor individual -->
                        <div class="border-2 rounded-lg p-4 transition-all {{ $workType === 'individual' ? 'border-agro-500 bg-agro-50' : 'border-zinc-200 hover:border-zinc-300' }}">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input
                                    type="radio"
                                    wire:model.live="workType"
                                    value="individual"
                                    class="w-5 h-5 text-agro-500 focus:ring-agro-500"
                                />
                                <div class="flex-1">
                                    <span class="font-semibold text-zinc-900">Viticultor individual</span>
                                    <p class="text-sm text-zinc-500 mt-1">Un viticultor específico realizó el trabajo</p>
                                </div>
                            </label>
                            @if($workType === 'individual')
                                <div class="mt-4">
                                    <flux:label for="crew_member_id" class="text-sm">Selecciona el viticultor</flux:label>
                                    <flux:select
                                        wire:model="crew_member_id"
                                        id="crew_member_id"
                                        class="mt-1"
                                        :error="$errors->first('crew_member_id')"
                                    >
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

                <!-- Maquinaria -->
                <div>
                    <flux:label for="machinery_id">Maquinaria</flux:label>
                    <flux:select
                        wire:model="machinery_id"
                        id="machinery_id"
                        :error="$errors->first('machinery_id')"
                    >
                        <option value="">Sin maquinaria asignada</option>
                        @foreach($machinery as $item)
                            <option value="{{ $item->id }}">{{ $item->name }} ({{ $item->type }})</option>
                        @endforeach
                    </flux:select>
                </div>

                <!-- Notas -->
                <div class="mt-6">
                    <flux:label for="notes">Notas Adicionales</flux:label>
                    <flux:textarea
                        wire:model="notes"
                        id="notes"
                        rows="4"
                        placeholder="Observaciones, comentarios, etc."
                        :error="$errors->first('notes')"
                    />
                </div>
        </x-agro.form-section>

        <x-agro.form-actions
            :cancel-url="route('viticulturist.digital-notebook')"
            submit-label="Actualizar Tratamiento"
        />
    </form>
</x-agro.form-card>
