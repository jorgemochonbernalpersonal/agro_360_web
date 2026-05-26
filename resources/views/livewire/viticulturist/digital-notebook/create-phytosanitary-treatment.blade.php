<x-agro.form-card
    :title="__('Registrar Tratamiento Fitosanitario')"
    :description="__('Registra un nuevo tratamiento fitosanitario en el cuaderno digital')"
    :back-url="roleRoute('viticulturist.digital-notebook.treatment.index')"
>
    <form wire:submit="save" class="space-y-8" data-cy="treatment-form">

        {{-- ── Información Básica ────────────────────────────────────────────── --}}
        <x-agro.form-section :title="__('Información Básica')">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <flux:field>
                    <flux:label required>{{ __('Parcela') }}</flux:label>
                    <flux:select wire:model.live="plot_id" id="plot_id" data-cy="plot-select">
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
                                    @if($planting->area_planted) ({{ number_format($planting->area_planted, 2) }} ha) @endif
                                </option>
                            @endforeach
                        </flux:select>
                        <flux:error name="plot_planting_id" />
                    </flux:field>
                @endif

                <flux:field>
                    <flux:label required>{{ __('Fecha del Tratamiento') }}</flux:label>
                    <flux:input wire:model="activity_date" id="activity_date" type="date" data-cy="activity-date-input" />
                    <flux:error name="activity_date" />
                </flux:field>

                <flux:field>
                    <flux:label required>{{ __('Estadio Fenológico') }}</flux:label>
                    <flux:select wire:model="phenological_stage" id="phenological_stage" data-cy="phenological-stage-select">
                        <option value="">{{ __('Selecciona un estadio') }}</option>
                        <option value="Brotación">{{ __('Brotación') }}</option>
                        <option value="Desarrollo vegetativo">{{ __('Desarrollo vegetativo') }}</option>
                        <option value="Floración">{{ __('Floración') }}</option>
                        <option value="Cuajado">{{ __('Cuajado') }}</option>
                        <option value="Envero">{{ __('Envero') }}</option>
                        <option value="Maduración">{{ __('Maduración') }}</option>
                        <option value="Vendimia">{{ __('Vendimia') }}</option>
                        <option value="Caída de hoja">{{ __('Caída de hoja') }}</option>
                        <option value="Reposo invernal">{{ __('Reposo invernal') }}</option>
                    </flux:select>
                    <flux:description>{{ __('Recomendado para trazabilidad PAC') }}</flux:description>
                    <flux:error name="phenological_stage" />
                </flux:field>

            </div>
        </x-agro.form-section>

        {{-- ── Producto Fitosanitario ─────────────────────────────────────────── --}}
        <x-agro.form-section :title="__('Producto Fitosanitario')">

            <div class="flex flex-col md:flex-row md:items-end gap-6">
                <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:field>
                        <flux:label required>{{ __('Producto') }}</flux:label>
                        <flux:select wire:model.live="product_id" id="product_id" data-cy="product-select">
                            <option value="">{{ __('Selecciona un producto') }}</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">
                                    {{ $product->name }}
                                    @if($product->active_ingredient) ({{ $product->active_ingredient }}) @endif
                                </option>
                            @endforeach
                        </flux:select>
                        <flux:error name="product_id" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Plaga / Enfermedad Objetivo') }}</flux:label>
                        <flux:select wire:model="pest_id" data-cy="pest-select">
                            <option value="">{{ __('Selecciona una plaga/enfermedad') }}</option>
                            @foreach($pests as $pest)
                                <option value="{{ $pest->id }}">
                                    {{ $pest->name }}
                                    @if($pest->scientific_name) ({{ $pest->scientific_name }}) @endif
                                </option>
                            @endforeach
                        </flux:select>
                        <flux:error name="pest_id" />
                    </flux:field>
                </div>
                <div class="md:w-auto shrink-0">
                    <flux:button href="{{ roleRoute('viticulturist.phytosanitary-products.create') }}" variant="ghost" icon="plus">{{ __('Nuevo producto') }}</flux:button>
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
                            {{ __('Toxicidad clase :class', ['class' => $this->selectedProduct->toxicity_class]) }}
                        </span>
                    @endif
                </div>
            @endif

            {{-- Aplicaciones en esta campaña --}}
            @if($this->selectedProduct && $this->applicationsThisCampaign > 0)
                <div class="mt-3 flex items-start gap-2.5 p-3 bg-amber-50 border border-amber-200 rounded-xl text-sm text-amber-800">
                    <flux:icon icon="exclamation-triangle" class="size-4 text-amber-500 shrink-0 mt-0.5" />
                    <span>{{ __('Este producto ya se ha aplicado') }} <strong>{{ $this->applicationsThisCampaign }} {{ $this->applicationsThisCampaign === 1 ? __('vez') : __('veces') }}</strong> {{ __('en esta campaña. Consulta la etiqueta del producto para verificar el número máximo de aplicaciones permitidas.') }}</span>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                <flux:field>
                    <flux:label required>{{ __('Dosis por Hectárea') }}</flux:label>
                    <flux:input wire:model.live="dose_per_hectare" id="dose_per_hectare" type="number" step="0.001" data-cy="dose-per-hectare-input" placeholder="0.000" />
                    <flux:description>L/ha o kg/ha</flux:description>
                    <flux:error name="dose_per_hectare" />
                </flux:field>
                <flux:field>
                    <flux:label required>{{ __('Área Tratada') }}</flux:label>
                    <flux:input wire:model.live="area_treated" id="area_treated" type="number" step="0.001" data-cy="area-treated-input" placeholder="0.000" />
                    <flux:description>ha</flux:description>
                    <flux:error name="area_treated" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Dosis Total (calculada)') }}</flux:label>
                    <flux:input wire:model="total_dose" id="total_dose" type="number" step="0.001" placeholder="0.000" class="bg-zinc-50" readonly />
                    <flux:description>{{ __('Se calcula automáticamente') }}</flux:description>
                </flux:field>
            </div>

            <div class="mt-6">
                <flux:field>
                    <flux:label>{{ __('Método de Aplicación') }}</flux:label>
                    <flux:select wire:model="application_method" data-cy="application-method-select">
                        <option value="">{{ __('Selecciona un método') }}</option>
                        <option value="pulverización">{{ __('Pulverización') }}</option>
                        <option value="aplicación foliar">{{ __('Aplicación Foliar') }}</option>
                        <option value="aplicación al suelo">{{ __('Aplicación al Suelo') }}</option>
                        <option value="inyección">{{ __('Inyección') }}</option>
                        <option value="nebulización">{{ __('Nebulización') }}</option>
                        <option value="otro">{{ __('Otro') }}</option>
                    </flux:select>
                    <flux:error name="application_method" />
                </flux:field>
            </div>

            {{-- Plazo de seguridad --}}
            @if($this->selectedProduct && $this->selectedProduct->withdrawal_period_days)
                <div class="mt-6 p-4 bg-amber-50 border-l-4 border-amber-500 rounded-r-lg">
                    <h4 class="text-sm font-semibold text-amber-900">{{ __('Plazo de Seguridad') }}</h4>
                    <p class="text-sm text-amber-800 mt-1">
                        <strong>{{ $this->selectedProduct->withdrawal_period_days }} {{ __('días') }}</strong> {{ __('entre aplicación y cosecha') }}
                    </p>
                    @if($activity_date)
                        @php $safeDate = \Carbon\Carbon::parse($activity_date)->addDays($this->selectedProduct->withdrawal_period_days); @endphp
                        <p class="text-xs text-amber-700 mt-2">{{ __('Podrá cosechar a partir del:') }} <strong>{{ $safeDate->format('d/m/Y') }}</strong></p>
                    @endif
                </div>
            @elseif($this->selectedProduct)
                <div class="mt-6 p-3 bg-zinc-50 border border-zinc-200 rounded-xl text-xs text-zinc-500">
                    {{ __('Sin plazo de seguridad registrado para este producto. Consulta la etiqueta o actualiza la ficha del producto.') }}
                </div>
            @endif

        </x-agro.form-section>

        {{-- ── Cumplimiento PAC ───────────────────────────────────────────────── --}}
        <x-agro.form-section :title="__('Cumplimiento PAC (Obligatorio)')">
            <div class="space-y-6">

                <flux:field>
                    <flux:label required>
                        {{ __('Justificación del Tratamiento') }}
                        <span class="text-xs text-zinc-400 font-normal">({{ __('Plaga o enfermedad detectada') }})</span>
                    </flux:label>
                    <flux:textarea wire:model="treatment_justification" id="treatment_justification" rows="3"
                        :placeholder="__('Ej: Detección de mildiu en las hojas de la parte superior. Presencia de manchas amarillentas...')" />
                    <flux:error name="treatment_justification" />
                </flux:field>
                <p class="text-xs text-zinc-500 -mt-4"><strong>{{ __('PAC obligatorio:') }}</strong> {{ __('Describe la plaga o enfermedad que motiva el tratamiento (RD 1311/2012).') }}</p>

                {{-- Aplicador ROPO --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <flux:field>
                            <flux:label>{{ __('Aplicador ROPO registrado') }}</flux:label>
                            <flux:select wire:model.live="field_applicator_id" id="field_applicator_id">
                                <option value="">{{ __('Selecciona un aplicador') }}</option>
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
                            <flux:error name="field_applicator_id" />
                        </flux:field>
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
                                            {{ __('Vence') }} {{ $selectedApplicator->ropo_expiry_date->format('d/m/Y') }}
                                        </span>
                                    @endif
                                </div>
                                @if($selectedApplicator->isRopoExpired())
                                    <p class="mt-1 text-xs text-red-600 font-medium">⚠️ {{ __('El ROPO de este aplicador ha vencido. Actualiza su ficha antes de registrar el tratamiento.') }}</p>
                                @endif
                            @endif
                        @else
                            <p class="mt-1 text-xs text-zinc-500">
                                {{ __('Selecciona un aplicador registrado o introduce el número ROPO manualmente.') }}
                                <a href="{{ roleRoute('viticulturist.field-applicators.index') }}" class="text-agro-600 hover:underline">{{ __('Gestionar aplicadores') }} →</a>
                            </p>
                        @endif
                    </div>

                    <flux:field>
                        <flux:label required>
                            {{ __('Número ROPO del Aplicador') }}
                            @if($field_applicator_id)
                                <span class="text-xs text-zinc-400 font-normal">({{ __('auto-rellenado') }})</span>
                            @endif
                        </flux:label>
                        <flux:input wire:model="applicator_ropo_number" id="applicator_ropo_number" type="text"
                            :placeholder="__('Ej: ES12345678')" maxlength="50"
                            :class="$field_applicator_id ? 'bg-zinc-50' : ''" />
                        <flux:description>{{ __('Registro Oficial de Productores y Operadores') }}</flux:description>
                        <flux:error name="applicator_ropo_number" />
                    </flux:field>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <flux:field>
                        <flux:label required>{{ __('Plazo de Reentrada') }}</flux:label>
                        <flux:input wire:model="reentry_period_days" id="reentry_period_days" type="number" :placeholder="__('Ej: 3')" min="0" step="1" />
                        <flux:description>{{ __('Días sin acceso a la parcela tras la aplicación') }}</flux:description>
                        <flux:error name="reentry_period_days" />
                    </flux:field>
                    <flux:field>
                        <flux:label required>{{ __('Volumen de Caldo Total') }}</flux:label>
                        <flux:input wire:model="spray_volume" id="spray_volume" type="number" :placeholder="__('Ej: 500.00')" min="0.01" step="0.01" />
                        <flux:description>{{ __('Litros totales de caldo aplicados') }}</flux:description>
                        <flux:error name="spray_volume" />
                    </flux:field>
                    <flux:field>
                        <flux:label>{{ __('Caldo por Hectárea') }}</flux:label>
                        <flux:input wire:model="water_volume_liters_ha" type="number" :placeholder="__('Ej: 200.00')" min="0" step="0.01" />
                        <flux:description>L/ha {{ __('tratada') }}</flux:description>
                        <flux:error name="water_volume_liters_ha" />
                    </flux:field>
                </div>

                {{-- Asesoramiento --}}
                <div class="space-y-4">
                    <flux:checkbox wire:model.live="under_advisory" id="under_advisory" :label="__('Tratamiento bajo asesoramiento técnico cualificado')" :description="__('Obligatorio en Producción Integrada (RD 1311/2012 Art. 14)')" />
                    @if($under_advisory)
                        <div class="ml-7">
                            <flux:field>
                                <flux:label required>{{ __('Fecha de recomendación del asesor') }}</flux:label>
                                <flux:input wire:model="advisory_recommendation_date" id="advisory_recommendation_date" type="date" />
                                <flux:error name="advisory_recommendation_date" />
                            </flux:field>
                        </div>
                    @endif
                </div>

                <div class="p-4 bg-amber-50 border-l-4 border-amber-500 rounded-r-lg text-sm text-amber-800">
                    <strong>{{ __('Información PAC:') }}</strong> {{ __('Campos obligatorios según RD 1311/2012 sobre uso sostenible de productos fitosanitarios.') }}
                </div>

            </div>
        </x-agro.form-section>

        {{-- ── Gestión Integrada de Plagas (IPM) ────────────────────────────── --}}
        <x-agro.form-section :title="__('Gestión Integrada de Plagas (IPM)')" color="green">
            <flux:callout variant="info" icon="information-circle">
                <strong>{{ __('RD 1311/2012 — Art. 14:') }}</strong> {{ __('Documenta los métodos alternativos y preventivos aplicados antes del tratamiento químico.') }}
            </flux:callout>
            <div class="mt-5 grid grid-cols-1 md:grid-cols-2 gap-4">
                <label class="flex items-start gap-3 p-3 border border-zinc-200 rounded-lg cursor-pointer hover:bg-zinc-50 transition-colors">
                    <flux:checkbox wire:model="plague_monitoring" id="plague_monitoring" class="mt-0.5" />
                    <div>
                        <span class="text-sm font-medium text-zinc-800">{{ __('Seguimiento y monitoreo de la plaga') }}</span>
                        <p class="text-xs text-zinc-500 mt-0.5">{{ __('Trampas, conteos, umbrales de acción') }}</p>
                    </div>
                </label>
                <label class="flex items-start gap-3 p-3 border border-zinc-200 rounded-lg cursor-pointer hover:bg-zinc-50 transition-colors">
                    <flux:checkbox wire:model="prior_non_chemical_methods" id="prior_non_chemical_methods" class="mt-0.5" />
                    <div>
                        <span class="text-sm font-medium text-zinc-800">{{ __('Métodos no químicos previos') }}</span>
                        <p class="text-xs text-zinc-500 mt-0.5">{{ __('Se intentaron alternativas antes del químico') }}</p>
                    </div>
                </label>
                <label class="flex items-start gap-3 p-3 border border-zinc-200 rounded-lg cursor-pointer hover:bg-zinc-50 transition-colors">
                    <flux:checkbox wire:model="manual_mechanical_control" id="manual_mechanical_control" class="mt-0.5" />
                    <div>
                        <span class="text-sm font-medium text-zinc-800">{{ __('Control manual o mecánico') }}</span>
                        <p class="text-xs text-zinc-500 mt-0.5">{{ __('Retirada manual, trituración, labores mecánicas') }}</p>
                    </div>
                </label>
                <label class="flex items-start gap-3 p-3 border border-zinc-200 rounded-lg cursor-pointer hover:bg-zinc-50 transition-colors">
                    <flux:checkbox wire:model="biological_control" id="biological_control" class="mt-0.5" />
                    <div>
                        <span class="text-sm font-medium text-zinc-800">{{ __('Control biológico') }}</span>
                        <p class="text-xs text-zinc-500 mt-0.5">{{ __('Depredadores naturales, hongos entomopatógenos, etc.') }}</p>
                    </div>
                </label>
                <label class="flex items-start gap-3 p-3 border border-zinc-200 rounded-lg cursor-pointer hover:bg-zinc-50 transition-colors md:col-span-2">
                    <flux:checkbox wire:model="cultural_preventions" id="cultural_preventions" class="mt-0.5" />
                    <div>
                        <span class="text-sm font-medium text-zinc-800">{{ __('Medidas culturales preventivas') }}</span>
                        <p class="text-xs text-zinc-500 mt-0.5">{{ __('Poda de saneamiento, eliminación de focos, aireación del cultivo, cubierta vegetal') }}</p>
                    </div>
                </label>
            </div>
        </x-agro.form-section>

        {{-- ── Zona Tampón Hídrica ────────────────────────────────────────────── --}}
        <x-agro.form-section :title="__('Zona Tampón y Masas de Agua')">
            <flux:callout variant="warning" icon="exclamation-triangle">
                {{ __('Algunos productos requieren distancia mínima a masas de agua superficiales. Consulta la etiqueta del producto.') }}
            </flux:callout>
            <div class="mt-5 space-y-4">
                <flux:checkbox wire:model.live="buffer_zone_respected" id="buffer_zone_respected"
                    :label="__('Se ha respetado la zona tampón exigida por la etiqueta del producto')"
                    :description="__('Distancia mínima a ríos, lagos, acequias y otras masas de agua')" />
                @if($buffer_zone_respected)
                    <div class="ml-7 max-w-xs">
                        <flux:field>
                            <flux:label>{{ __('Distancia a la masa de agua más próxima') }}</flux:label>
                            <flux:input wire:model="distance_to_water_m" id="distance_to_water_m" type="number" :placeholder="__('Ej: 5.00')" min="0" step="0.5" />
                            <flux:description>{{ __('metros') }}</flux:description>
                            <flux:error name="distance_to_water_m" />
                        </flux:field>
                    </div>
                @endif
            </div>
        </x-agro.form-section>

        {{-- ── Condiciones Meteorológicas ─────────────────────────────────────── --}}
        <x-agro.form-section :title="__('Condiciones Meteorológicas')">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <flux:field>
                    <flux:label>{{ __('Temperatura') }}</flux:label>
                    <flux:input wire:model="temperature" id="temperature" type="number" step="0.1" placeholder="20.0" />
                    <flux:description>°C</flux:description>
                    <flux:error name="temperature" />
                </flux:field>
                <div>
                    <flux:field>
                        <flux:label>{{ __('Velocidad del Viento') }}</flux:label>
                        <flux:input wire:model.live="wind_speed" id="wind_speed" type="number" step="0.1" placeholder="0.0" />
                        <flux:description>km/h</flux:description>
                        <flux:error name="wind_speed" />
                    </flux:field>
                    <div x-show="$wire.wind_speed !== '' && Number($wire.wind_speed) > 10.8"
                         x-cloak
                         class="mt-2 flex items-start gap-2 p-2.5 bg-red-50 border border-red-200 rounded-lg text-xs text-red-700">
                        <flux:icon icon="exclamation-triangle" class="size-3.5 shrink-0 mt-0.5 text-red-500" />
                        <span><strong>{{ __('Viento superior a 3 m/s.') }}</strong> {{ __('Riesgo de deriva. Revisa la etiqueta del producto — la mayoría prohíben la aplicación con viento > 3 m/s.') }}</span>
                    </div>
                </div>
                <flux:field>
                    <flux:label>{{ __('Humedad Relativa') }}</flux:label>
                    <flux:input wire:model="humidity" id="humidity" type="number" step="0.1" min="0" max="100" placeholder="0.0" />
                    <flux:description>%</flux:description>
                    <flux:error name="humidity" />
                </flux:field>
            </div>
            <div class="mt-6">
                <flux:field>
                    <flux:label>{{ __('Condiciones Meteorológicas Generales') }}</flux:label>
                    <flux:input wire:model="weather_conditions" type="text" :placeholder="__('Ej: Soleado, nublado, etc.')" />
                    <flux:error name="weather_conditions" />
                </flux:field>
            </div>
        </x-agro.form-section>

        {{-- ── Información Adicional ──────────────────────────────────────────── --}}
        <x-agro.form-section :title="__('Información Adicional')">

            <div class="mb-6">
                <flux:label class="mb-3 block font-semibold text-zinc-700">{{ __('¿Quién realizó el trabajo?') }}</flux:label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="border-2 rounded-lg p-4 transition-all {{ $workType === 'crew' ? 'border-agro-500 bg-agro-50' : 'border-zinc-200 hover:border-zinc-300' }}">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="radio" wire:model.live="workType" value="crew" class="w-5 h-5 text-agro-500 focus:ring-agro-500" />
                            <div class="flex-1">
                                <span class="font-semibold text-zinc-900">{{ __('Equipo completo') }}</span>
                                <p class="text-sm text-zinc-500 mt-1">{{ __('Todo el equipo trabajó en esta actividad') }}</p>
                            </div>
                        </label>
                        @if($workType === 'crew')
                            <div class="mt-4">
                                <flux:field>
                                    <flux:label>{{ __('Selecciona el equipo') }}</flux:label>
                                    <flux:select wire:model="crew_id" id="crew_id">
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
                            <input type="radio" wire:model.live="workType" value="individual" class="w-5 h-5 text-agro-500 focus:ring-agro-500" />
                            <div class="flex-1">
                                <span class="font-semibold text-zinc-900">{{ __('Viticultor individual') }}</span>
                                <p class="text-sm text-zinc-500 mt-1">{{ __('Un viticultor específico realizó el trabajo') }}</p>
                            </div>
                        </label>
                        @if($workType === 'individual')
                            <div class="mt-4">
                                <flux:field>
                                    <flux:label>{{ __('Selecciona el viticultor') }}</flux:label>
                                    <flux:select wire:model="crew_member_id" id="crew_member_id">
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

            <div class="mt-6">
                <flux:field>
                    <flux:label>{{ __('Notas Adicionales') }}</flux:label>
                    <flux:textarea wire:model="notes" rows="4" :placeholder="__('Observaciones, comentarios, etc.')" />
                    <flux:error name="notes" />
                </flux:field>
            </div>

        </x-agro.form-section>

        <x-agro.form-actions
            :cancel-url="roleRoute('viticulturist.digital-notebook.treatment.index')"
            :submit-label="__('Registrar Tratamiento')"
        />

    </form>
</x-agro.form-card>
