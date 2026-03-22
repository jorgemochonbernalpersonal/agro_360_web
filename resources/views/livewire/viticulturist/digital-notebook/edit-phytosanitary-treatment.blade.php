<x-agro.form-card
    title="Editar Tratamiento Fitosanitario"
    description="Modifica los datos del tratamiento fitosanitario registrado"
    :back-url="route('viticulturist.digital-notebook.treatment.index')"
>
    <form wire:submit="update" class="space-y-8" data-cy="treatment-form">

        {{-- ── Información Básica ────────────────────────────────────────────── --}}
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
                            @if(!count($availablePlantings))
                                <span class="text-zinc-400 text-xs font-normal">(Sin plantaciones activas)</span>
                            @endif
                        </flux:label>
                        <flux:select wire:model="plot_planting_id" id="plot_planting_id" data-cy="plot-planting-select" :error="$errors->first('plot_planting_id')" :required="count($availablePlantings) > 0">
                            <option value="">-- Selecciona una plantación --</option>
                            @foreach($availablePlantings as $planting)
                                <option value="{{ $planting->id }}">
                                    {{ $planting->name }}
                                    @if($planting->grapeVariety) — {{ $planting->grapeVariety->name }} @endif
                                    @if($planting->area_planted) ({{ number_format($planting->area_planted, 2) }} ha) @endif
                                </option>
                            @endforeach
                        </flux:select>
                    </div>
                @endif

                <div>
                    <flux:label for="activity_date" required>Fecha del Tratamiento</flux:label>
                    <flux:input wire:model="activity_date" type="date" id="activity_date" data-cy="activity-date-input" :error="$errors->first('activity_date')" required />
                </div>

                <div>
                    <flux:label for="phenological_stage" required>Estadio Fenológico</flux:label>
                    <flux:select wire:model="phenological_stage" id="phenological_stage" data-cy="phenological-stage-select" :error="$errors->first('phenological_stage')">
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

        {{-- ── Producto Fitosanitario ─────────────────────────────────────────── --}}
        <x-agro.form-section title="Producto Fitosanitario">

            <div class="flex flex-col md:flex-row md:items-end gap-6">
                <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <flux:label for="product_id" required>Producto</flux:label>
                        <flux:select wire:model.live="product_id" id="product_id" data-cy="product-select" :error="$errors->first('product_id')" required>
                            <option value="">Selecciona un producto</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">
                                    {{ $product->name }}
                                    @if($product->active_ingredient) ({{ $product->active_ingredient }}) @endif
                                </option>
                            @endforeach
                        </flux:select>
                    </div>

                    <div>
                        <flux:label for="pest_id">Plaga / Enfermedad Objetivo</flux:label>
                        <flux:select wire:model="pest_id" id="pest_id" data-cy="pest-select" :error="$errors->first('pest_id')">
                            <option value="">Selecciona una plaga/enfermedad</option>
                            @foreach($pests as $pest)
                                <option value="{{ $pest->id }}">
                                    {{ $pest->name }}
                                    @if($pest->scientific_name) ({{ $pest->scientific_name }}) @endif
                                </option>
                            @endforeach
                        </flux:select>
                    </div>
                </div>
                <div class="md:w-auto shrink-0">
                    <flux:button href="{{ route('viticulturist.phytosanitary-products.create') }}" variant="ghost" icon="plus">Nuevo producto</flux:button>
                </div>
            </div>

            {{-- Info del producto seleccionado --}}
            @if($this->selectedProduct)
                <div class="mt-4 flex flex-wrap gap-3 text-xs">
                    @if($this->selectedProduct->registration_number)
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-zinc-100 text-zinc-600 rounded-full font-mono">
                            Reg. MAPA: {{ $this->selectedProduct->registration_number }}
                        </span>
                    @endif
                    @if($this->selectedProduct->type)
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-zinc-100 text-zinc-600 rounded-full capitalize">
                            {{ $this->selectedProduct->type }}
                        </span>
                    @endif
                    @if($this->selectedProduct->toxicity_class)
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-red-50 text-red-600 rounded-full font-semibold">
                            Toxicidad clase {{ $this->selectedProduct->toxicity_class }}
                        </span>
                    @endif
                </div>
            @endif

            {{-- Aplicaciones en esta campaña (excluye este tratamiento) --}}
            @if($this->selectedProduct && $this->applicationsThisCampaign > 0)
                <div class="mt-3 flex items-start gap-2.5 p-3 bg-amber-50 border border-amber-200 rounded-xl text-sm text-amber-800">
                    <flux:icon icon="exclamation-triangle" class="size-4 text-amber-500 shrink-0 mt-0.5" />
                    <span>Este producto se ha aplicado <strong>{{ $this->applicationsThisCampaign }} {{ $this->applicationsThisCampaign === 1 ? 'vez' : 'veces' }}</strong> más en esta campaña. Consulta la etiqueta del producto para verificar el número máximo de aplicaciones permitidas.</span>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                <div>
                    <flux:label for="dose_per_hectare" required>Dosis por Hectárea (L/ha o kg/ha)</flux:label>
                    <flux:input wire:model.live="dose_per_hectare" type="number" step="0.001" id="dose_per_hectare" data-cy="dose-per-hectare-input" placeholder="0.000" :error="$errors->first('dose_per_hectare')" />
                </div>
                <div>
                    <flux:label for="area_treated" required>Área Tratada (ha)</flux:label>
                    <flux:input wire:model.live="area_treated" type="number" step="0.001" id="area_treated" data-cy="area-treated-input" placeholder="0.000" :error="$errors->first('area_treated')" />
                </div>
                <div>
                    <flux:label for="total_dose">Dosis Total (calculada)</flux:label>
                    <flux:input wire:model="total_dose" type="number" step="0.001" id="total_dose" placeholder="0.000" class="bg-zinc-50" readonly />
                    <p class="mt-1 text-xs text-zinc-500">Se calcula automáticamente</p>
                </div>
            </div>

            <div class="mt-6">
                <flux:label for="application_method">Método de Aplicación</flux:label>
                <flux:select wire:model="application_method" id="application_method" data-cy="application-method-select" :error="$errors->first('application_method')">
                    <option value="">Selecciona un método</option>
                    <option value="pulverización">Pulverización</option>
                    <option value="aplicación foliar">Aplicación Foliar</option>
                    <option value="aplicación al suelo">Aplicación al Suelo</option>
                    <option value="inyección">Inyección</option>
                    <option value="nebulización">Nebulización</option>
                    <option value="otro">Otro</option>
                </flux:select>
            </div>

            {{-- Plazo de seguridad --}}
            @if($this->selectedProduct && $this->selectedProduct->withdrawal_period_days)
                <div class="mt-6 p-4 bg-amber-50 border-l-4 border-amber-500 rounded-r-lg">
                    <h4 class="text-sm font-semibold text-amber-900">Plazo de Seguridad</h4>
                    <p class="text-sm text-amber-800 mt-1">
                        <strong>{{ $this->selectedProduct->withdrawal_period_days }} días</strong> entre aplicación y cosecha
                    </p>
                    @if($activity_date)
                        @php $safeDate = \Carbon\Carbon::parse($activity_date)->addDays($this->selectedProduct->withdrawal_period_days); @endphp
                        <p class="text-xs text-amber-700 mt-2">Podrá cosechar a partir del: <strong>{{ $safeDate->format('d/m/Y') }}</strong></p>
                    @endif
                </div>
            @elseif($this->selectedProduct)
                <div class="mt-6 p-3 bg-zinc-50 border border-zinc-200 rounded-xl text-xs text-zinc-500">
                    Sin plazo de seguridad registrado para este producto. Consulta la etiqueta o actualiza la ficha del producto.
                </div>
            @endif

        </x-agro.form-section>

        {{-- ── Cumplimiento PAC ───────────────────────────────────────────────── --}}
        <x-agro.form-section title="Cumplimiento PAC (Obligatorio)">
            <div class="space-y-6">

                <div>
                    <flux:label for="treatment_justification" required>
                        Justificación del Tratamiento
                        <span class="text-xs text-zinc-400 font-normal">(Plaga o enfermedad detectada)</span>
                    </flux:label>
                    <flux:textarea wire:model="treatment_justification" id="treatment_justification" rows="3"
                        placeholder="Ej: Detección de mildiu en las hojas de la parte superior. Presencia de manchas amarillentas..."
                        :error="$errors->first('treatment_justification')" required />
                    <p class="mt-1 text-xs text-zinc-500"><strong>PAC obligatorio:</strong> Describe la plaga o enfermedad que motiva el tratamiento (RD 1311/2012).</p>
                </div>

                {{-- Aplicador ROPO --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <flux:label for="field_applicator_id">Aplicador ROPO registrado</flux:label>
                        <flux:select wire:model.live="field_applicator_id" id="field_applicator_id" :error="$errors->first('field_applicator_id')">
                            <option value="">Selecciona un aplicador</option>
                            @foreach($applicators as $applicator)
                                <option value="{{ $applicator->id }}">
                                    {{ $applicator->name }}
                                    @if($applicator->ropo_number) · {{ $applicator->ropo_number }} @endif
                                    @if($applicator->ropo_category) · {{ $applicator->category_label }} @endif
                                    @if($applicator->isRopoExpiringSoon()) ⚠️ @endif
                                    @if($applicator->isRopoExpired()) ❌ @endif
                                </option>
                            @endforeach
                        </flux:select>
                        @if($field_applicator_id)
                            @php $selectedApplicator = $applicators->firstWhere('id', $field_applicator_id); @endphp
                            @if($selectedApplicator)
                                <div class="mt-2 flex flex-wrap gap-2 text-xs">
                                    @if($selectedApplicator->ropo_number)
                                        <span class="px-2 py-0.5 bg-zinc-100 text-zinc-700 rounded-full font-mono">{{ $selectedApplicator->ropo_number }}</span>
                                    @endif
                                    @if($selectedApplicator->ropo_category)
                                        <span class="px-2 py-0.5 bg-blue-50 text-blue-700 rounded-full">{{ $selectedApplicator->category_label }}</span>
                                    @endif
                                    @if($selectedApplicator->ropo_expiry_date)
                                        <span class="px-2 py-0.5 {{ $selectedApplicator->isRopoExpired() ? 'bg-red-50 text-red-700' : ($selectedApplicator->isRopoExpiringSoon() ? 'bg-amber-50 text-amber-700' : 'bg-green-50 text-green-700') }} rounded-full">
                                            Vence {{ $selectedApplicator->ropo_expiry_date->format('d/m/Y') }}
                                        </span>
                                    @endif
                                </div>
                                @if($selectedApplicator->isRopoExpired())
                                    <p class="mt-1 text-xs text-red-600 font-medium">⚠️ El ROPO de este aplicador ha vencido. Actualiza su ficha antes de guardar el tratamiento.</p>
                                @endif
                            @endif
                        @else
                            <p class="mt-1 text-xs text-zinc-500">
                                Selecciona un aplicador registrado o introduce el número ROPO manualmente.
                                <a href="{{ route('viticulturist.field-applicators.index') }}" class="text-agro-600 hover:underline">Gestionar aplicadores →</a>
                            </p>
                        @endif
                    </div>

                    <div>
                        <flux:label for="applicator_ropo_number" :required="!$field_applicator_id">
                            Número ROPO del Aplicador
                            @if($field_applicator_id)
                                <span class="text-xs text-zinc-400 font-normal">(auto-rellenado)</span>
                            @endif
                        </flux:label>
                        <flux:input wire:model="applicator_ropo_number" type="text" id="applicator_ropo_number"
                            placeholder="Ej: ES12345678" maxlength="50"
                            :error="$errors->first('applicator_ropo_number')"
                            :class="$field_applicator_id ? 'bg-zinc-50' : ''" />
                        <p class="mt-1 text-xs text-zinc-500">Registro Oficial de Productores y Operadores</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <flux:label for="reentry_period_days" required>Plazo de Reentrada (días)</flux:label>
                        <flux:input wire:model="reentry_period_days" type="number" id="reentry_period_days" placeholder="Ej: 3" min="0" step="1" :error="$errors->first('reentry_period_days')" required />
                        <p class="mt-1 text-xs text-zinc-500">Días sin acceso a la parcela tras la aplicación</p>
                    </div>
                    <div>
                        <flux:label for="spray_volume" required>Volumen de Caldo Total (L)</flux:label>
                        <flux:input wire:model="spray_volume" type="number" id="spray_volume" placeholder="Ej: 500.00" min="0.01" step="0.01" :error="$errors->first('spray_volume')" required />
                        <p class="mt-1 text-xs text-zinc-500">Litros totales de caldo aplicados</p>
                    </div>
                    <div>
                        <flux:label for="water_volume_liters_ha">Caldo por Hectárea (L/ha)</flux:label>
                        <flux:input wire:model="water_volume_liters_ha" type="number" id="water_volume_liters_ha" placeholder="Ej: 200.00" min="0" step="0.01" :error="$errors->first('water_volume_liters_ha')" />
                        <p class="mt-1 text-xs text-zinc-500">Litros de caldo por hectárea tratada</p>
                    </div>
                </div>

                {{-- Asesoramiento --}}
                <div class="space-y-4">
                    <flux:checkbox wire:model.live="under_advisory" label="Tratamiento bajo asesoramiento técnico cualificado" description="Obligatorio en Producción Integrada (RD 1311/2012 Art. 14)" />
                    @if($under_advisory)
                        <div class="ml-7">
                            <flux:field>
                                <flux:label for="advisory_recommendation_date" required>Fecha de recomendación del asesor</flux:label>
                                <flux:input wire:model="advisory_recommendation_date" type="date" id="advisory_recommendation_date" />
                                <flux:error name="advisory_recommendation_date" />
                            </flux:field>
                        </div>
                    @endif
                </div>

                <div class="p-4 bg-amber-50 border-l-4 border-amber-500 rounded-r-lg text-sm text-amber-800">
                    <strong>Información PAC:</strong> Campos obligatorios según RD 1311/2012 sobre uso sostenible de productos fitosanitarios.
                </div>

            </div>
        </x-agro.form-section>

        {{-- ── Gestión Integrada de Plagas (IPM) ────────────────────────────── --}}
        <x-agro.form-section title="Gestión Integrada de Plagas (IPM)" color="green">
            <flux:callout variant="info" icon="information-circle">
                <strong>RD 1311/2012 — Art. 14:</strong> Documenta los métodos alternativos y preventivos aplicados antes del tratamiento químico.
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

        {{-- ── Zona Tampón Hídrica ────────────────────────────────────────────── --}}
        <x-agro.form-section title="Zona Tampón y Masas de Agua">
            <flux:callout variant="warning" icon="exclamation-triangle">
                Algunos productos requieren distancia mínima a masas de agua superficiales. Consulta la etiqueta del producto.
            </flux:callout>
            <div class="mt-5 space-y-4">
                <flux:checkbox wire:model.live="buffer_zone_respected" id="buffer_zone_respected"
                    label="Se ha respetado la zona tampón exigida por la etiqueta del producto"
                    description="Distancia mínima a ríos, lagos, acequias y otras masas de agua" />
                @if($buffer_zone_respected)
                    <div class="ml-7 max-w-xs">
                        <flux:label for="distance_to_water_m">Distancia a la masa de agua más próxima (m)</flux:label>
                        <flux:input wire:model="distance_to_water_m" type="number" id="distance_to_water_m" placeholder="Ej: 5.00" min="0" step="0.5" :error="$errors->first('distance_to_water_m')" />
                    </div>
                @endif
            </div>
        </x-agro.form-section>

        {{-- ── Condiciones Meteorológicas ─────────────────────────────────────── --}}
        <x-agro.form-section title="Condiciones Meteorológicas">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <flux:label for="temperature">Temperatura (°C)</flux:label>
                    <flux:input wire:model="temperature" type="number" step="0.1" id="temperature" placeholder="20.0" :error="$errors->first('temperature')" />
                </div>
                <div>
                    <flux:label for="wind_speed">Velocidad del Viento (km/h)</flux:label>
                    <flux:input wire:model.live="wind_speed" type="number" step="0.1" id="wind_speed" placeholder="0.0" :error="$errors->first('wind_speed')" />
                    {{-- Alerta viento > 3 m/s (10.8 km/h) --}}
                    <div x-show="$wire.wind_speed !== '' && Number($wire.wind_speed) > 10.8"
                         x-cloak
                         class="mt-2 flex items-start gap-2 p-2.5 bg-red-50 border border-red-200 rounded-lg text-xs text-red-700">
                        <flux:icon icon="exclamation-triangle" class="size-3.5 shrink-0 mt-0.5 text-red-500" />
                        <span><strong>Viento superior a 3 m/s.</strong> Riesgo de deriva. Revisa la etiqueta del producto — la mayoría prohíben la aplicación con viento &gt; 3 m/s.</span>
                    </div>
                </div>
                <div>
                    <flux:label for="humidity">Humedad Relativa (%)</flux:label>
                    <flux:input wire:model="humidity" type="number" step="0.1" min="0" max="100" id="humidity" placeholder="0.0" :error="$errors->first('humidity')" />
                </div>
            </div>
            <div class="mt-6">
                <flux:label for="weather_conditions">Condiciones Meteorológicas Generales</flux:label>
                <flux:input wire:model="weather_conditions" type="text" id="weather_conditions" placeholder="Ej: Soleado, nublado, etc." :error="$errors->first('weather_conditions')" />
            </div>
        </x-agro.form-section>

        {{-- ── Información Adicional ──────────────────────────────────────────── --}}
        <x-agro.form-section title="Información Adicional">

            <div class="mb-6">
                <flux:label class="mb-3 block font-semibold text-zinc-700">¿Quién realizó el trabajo?</flux:label>
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
                            <input type="radio" wire:model.live="workType" value="individual" class="w-5 h-5 text-agro-500 focus:ring-agro-500" />
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
                                    @foreach($allViticulturists as $viticulturist)
                                        <option value="{{ $viticulturist->id }}">
                                            {{ $viticulturist->name }}@if($viticulturist->id === auth()->id()) (Yo)@endif
                                        </option>
                                    @endforeach
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

            <div class="mt-6">
                <flux:label for="notes">Notas Adicionales</flux:label>
                <flux:textarea wire:model="notes" id="notes" rows="4" placeholder="Observaciones, comentarios, etc." :error="$errors->first('notes')" />
            </div>

        </x-agro.form-section>

        <x-agro.form-actions
            :cancel-url="route('viticulturist.digital-notebook.treatment.index')"
            submit-label="Actualizar Tratamiento"
        />

    </form>
</x-agro.form-card>
