<x-agro.form-card
    title="Editar Cosecha"
    description="Modifica la información de la cosecha (vendimia)"
    icon="archive-box-arrow-down"
    icon-color="from-agro-500 to-agro-700"
    :back-url="roleRoute('viticulturist.digital-notebook.harvest.show', $harvest->id)"
>
    @if($harvest->wasEdited())
        <flux:callout variant="warning" icon="pencil-square" class="mb-6">
            <flux:callout.heading>Esta cosecha fue editada anteriormente</flux:callout.heading>
            <flux:callout.text>
                Editada el {{ $harvest->edited_at->format('d/m/Y H:i') }}
                @if($harvest->editor) por {{ $harvest->editor->name }} @endif
                @if($harvest->edit_notes) · Motivo: {{ $harvest->edit_notes }} @endif
            </flux:callout.text>
        </flux:callout>
    @endif

    <form wire:submit="update" class="space-y-8" data-cy="harvest-edit-form">
        
        {{-- Alerta de Plazo de Seguridad --}}
        @if($hasActiveWithdrawal)
            <flux:callout variant="danger" icon="exclamation-triangle">
                <flux:callout.heading>ADVERTENCIA: Plazo de Seguridad Activo</flux:callout.heading>
                <flux:callout.text>
                    Esta parcela tiene tratamientos fitosanitarios con plazo de seguridad activo.
                    <strong>No se recomienda cosechar hasta que finalicen los plazos</strong>.
                </flux:callout.text>
            </flux:callout>

            <x-agro.card class="mt-4">
                <x-slot:header>
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-red-50 flex items-center justify-center">
                            <flux:icon icon="beaker" class="size-4 text-red-600" />
                        </div>
                        <h3 class="font-semibold text-zinc-900">Tratamientos activos</h3>
                    </div>
                </x-slot:header>

                <div class="space-y-2">
                    @foreach($activeWithdrawalTreatments as $treatment)
                        <div class="flex items-start gap-3 text-sm">
                            <flux:icon icon="x-circle" class="size-4 text-red-500 flex-shrink-0 mt-0.5" />
                            <div>
                                <p class="font-semibold text-zinc-900">{{ $treatment['product_name'] }}</p>
                                <p class="text-zinc-600">
                                    Aplicado: {{ $treatment['application_date'] }} |
                                    Plazo: {{ $treatment['withdrawal_days'] }} días |
                                    <span class="font-semibold text-red-700">Seguro desde: {{ $treatment['safe_date'] }}</span>
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4">
                    <flux:callout variant="warning">
                        <flux:callout.text>
                            <strong>Si decides cosechar de todos modos</strong>, debes confirmar que entiendes los riesgos y proporcionar un motivo.
                        </flux:callout.text>
                    </flux:callout>

                    <div class="mt-3">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input
                                type="checkbox"
                                wire:model="withdrawalAcknowledged"
                                class="w-5 h-5 text-red-600 focus:ring-red-500 border-red-300 rounded"
                            />
                            <span class="text-sm font-semibold text-zinc-900">
                                Entiendo los riesgos y asumo la responsabilidad de cosechar con plazo de seguridad activo
                            </span>
                        </label>
                        @error('withdrawalAcknowledged')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    @if($withdrawalAcknowledged)
                        <div class="mt-3">
                            <flux:field>
                                <flux:label required>Motivo de la cosecha anticipada</flux:label>
                                <flux:textarea wire:model="withdrawalReason" id="withdrawalReason" rows="3" placeholder="Ej: Emergencia por previsión de granizo, daños por helada que requieren cosecha urgente, etc. (mínimo 20 caracteres)" />
                                <flux:description>Este motivo quedará registrado en el sistema.</flux:description>
                                <flux:error name="withdrawalReason" />
                            </flux:field>
                        </div>
                    @endif
                </div>
            </x-agro.card>
        @endif

        <x-agro.form-section title="Información Básica" color="green">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:field>
                    <flux:label required>Parcela</flux:label>
                    <flux:select wire:model.live="plot_id" id="plot_id" data-cy="plot-select" required>
                        <option value="">Selecciona una parcela</option>
                        @foreach($plots as $plot)
                            <option value="{{ $plot->id }}">{{ $plot->name }} ({{ $plot->area }} ha)</option>
                        @endforeach
                    </flux:select>
                    <flux:description>Solo se muestran parcelas con plantaciones activas</flux:description>
                    <flux:error name="plot_id" />
                </flux:field>

                <flux:field>
                    <flux:label required>Plantación / Variedad</flux:label>
                    <flux:select wire:model.live="plot_planting_id" id="plot_planting_id" data-cy="plot-planting-select" :disabled="!$plot_id || count($availablePlantings) === 0" required>
                        <option value="">Selecciona una plantación</option>
                        @foreach($availablePlantings as $planting)
                            <option value="{{ $planting->id }}">
                                @if($planting->name) {{ $planting->name }} - @endif
                                {{ $planting->grapeVariety->name ?? 'Sin variedad' }} ({{ $planting->area_planted }} ha)
                            </option>
                        @endforeach
                    </flux:select>
                    @if($plot_id && count($availablePlantings) === 0)
                        <flux:description class="text-amber-600">Esta parcela no tiene plantaciones activas</flux:description>
                    @endif
                    <flux:error name="plot_planting_id" />
                </flux:field>
            </div>

            <div class="mt-6">
                <flux:field>
                    <flux:label required>Contenedor</flux:label>
                    <flux:select wire:model.live="container_id" id="container_id" required>
                        <option value="">Selecciona un contenedor</option>
                        @foreach($availableContainers as $container)
                            <option value="{{ $container->id }}">
                                {{ $container->name }}
                                @if($container->serial_number) #{{ $container->serial_number }} @endif
                                - {{ number_format($container->getAvailableCapacity(), 2) }} / {{ number_format($container->capacity, 2) }} kg disponible
                                @if($container->id == $original_container_id) (Actual) @endif
                            </option>
                        @endforeach
                    </flux:select>
                    @if($availableContainers->isEmpty())
                        <flux:description class="text-amber-600">
                            No hay contenedores disponibles.
                            <a href="{{ roleRoute('viticulturist.containers.create') }}" class="text-blue-600 hover:underline">Crea uno primero</a>
                        </flux:description>
                    @else
                        <flux:description>Solo se muestran contenedores disponibles (sin asignar a otra cosecha) y el contenedor actual</flux:description>
                    @endif
                    <flux:error name="container_id" />
                @if($container_id && $container_id != $original_container_id)
                    <flux:callout variant="warning" icon="arrow-path" class="mt-2">
                        <flux:callout.text>
                            <strong>Cambio de contenedor:</strong> Asegúrate de que el contenedor tenga capacidad suficiente para el peso de la cosecha.
                        </flux:callout.text>
                    </flux:callout>
                @endif
                @if($container_id)
                    @php
                        $selectedContainer = $availableContainers->firstWhere('id', $container_id);
                    @endphp
                    @if($selectedContainer)
                        <flux:callout variant="info" icon="archive-box" class="mt-2">
                            <flux:callout.text>
                                <strong>Capacidad disponible:</strong> {{ number_format($selectedContainer->getAvailableCapacity(), 2) }} / {{ number_format($selectedContainer->capacity, 2) }} kg
                                · Ocupación actual: {{ number_format($selectedContainer->getOccupancyPercentage(), 1) }}%
                            </flux:callout.text>
                        </flux:callout>
                    @endif
                @endif
                </flux:field>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                <flux:field>
                    <flux:label required>Fecha de Registro</flux:label>
                    <flux:input wire:model="activity_date" type="date" id="activity_date" required />
                    <flux:error name="activity_date" />
                </flux:field>
                <flux:field>
                    <flux:label required>Fecha Inicio Vendimia</flux:label>
                    <flux:input wire:model="harvest_start_date" type="date" id="harvest_start_date" required />
                    <flux:error name="harvest_start_date" />
                </flux:field>
                <flux:field>
                    <flux:label>Fecha Fin Vendimia (Opcional)</flux:label>
                    <flux:input wire:model="harvest_end_date" type="date" id="harvest_end_date" />
                    <flux:error name="harvest_end_date" />
                </flux:field>
            </div>
        </x-agro.form-section>

        {{-- Panel de Control: Límite y Rendimiento Estimado --}}
        @if($selectedPlanting && ($harvestLimitInfo || $estimatedYield))
            <x-agro.form-section title="Control de Cosecha" color="blue">
                <div class="space-y-4">

                    {{-- Recibido por bodega --}}
                    @if($wineryReceivedKg > 0)
                        <div class="flex items-center gap-3 p-3 rounded-lg bg-blue-50 border border-blue-200 text-sm">
                            <flux:icon icon="archive-box-arrow-down" class="size-5 text-blue-600 shrink-0" />
                            <div>
                                <span class="text-blue-700 font-medium">Recibido por bodega esta añada:</span>
                                <span class="text-blue-800 font-bold ml-1">{{ number_format($wineryReceivedKg, 0) }} kg</span>
                            </div>
                            <a href="{{ roleRoute('viticulturist.harvests.index') }}" wire:navigate class="ml-auto text-xs text-blue-600 hover:underline">Ver detalle →</a>
                        </div>
                    @endif

                    {{-- Límite de Plantación --}}
                    @if($harvestLimitInfo)
                        <x-agro.card>
                            <x-slot:header>
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-lg bg-blue-50 flex items-center justify-center">
                                        <flux:icon icon="check-circle" class="size-4 text-blue-600" />
                                    </div>
                                    <h3 class="font-semibold text-zinc-900">Límite de Plantación</h3>
                                </div>
                            </x-slot:header>

                            <div class="grid grid-cols-2 {{ $total_weight && $total_weight > 0 ? 'md:grid-cols-4' : 'md:grid-cols-3' }} gap-4 text-sm">
                                <div>
                                    <p class="text-xs text-zinc-600">Límite máximo</p>
                                    <p class="font-bold text-zinc-900">{{ number_format($harvestLimitInfo['limit'], 2) }} kg</p>
                                </div>
                                <div>
                                    <p class="text-xs text-zinc-600">Cosechas registradas</p>
                                    <p class="font-bold text-zinc-900">{{ number_format($harvestLimitInfo['harvested'], 2) }} kg</p>
                                </div>
                                <div>
                                    <p class="text-xs text-zinc-600">Nueva cosecha</p>
                                    <p class="font-bold text-purple-700">
                                        @if($total_weight && $total_weight > 0)
                                            {{ number_format($total_weight, 2) }} kg
                                        @else
                                            <span class="text-zinc-400">Ingresa el peso</span>
                                        @endif
                                    </p>
                                </div>
                                @if($total_weight && $total_weight > 0)
                                    <div>
                                        <p class="text-xs text-zinc-600">Total después</p>
                                        <p class="font-bold {{ $harvestLimitInfo['exceeds'] ?? false ? 'text-red-600' : 'text-zinc-900' }}">
                                            {{ number_format($harvestLimitInfo['new_total'] ?? $harvestLimitInfo['harvested'], 2) }} kg
                                        </p>
                                    </div>
                                @endif
                            </div>

                            @if($total_weight && $total_weight > 0)
                                <div class="mt-3 pt-3 border-t border-zinc-100">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-zinc-600">Disponible restante</span>
                                        <span class="font-bold {{ ($harvestLimitInfo['new_remaining'] ?? $harvestLimitInfo['remaining']) < 0 ? 'text-red-600' : 'text-green-600' }}">
                                            {{ number_format($harvestLimitInfo['new_remaining'] ?? $harvestLimitInfo['remaining'], 2) }} kg
                                            ({{ number_format($harvestLimitInfo['new_percentage'] ?? $harvestLimitInfo['percentage'], 1) }}% usado)
                                        </span>
                                    </div>
                                    @if($harvestLimitInfo['exceeds'] ?? false)
                                        <flux:callout variant="danger" icon="exclamation-triangle" class="mt-2">
                                            <flux:callout.text>Esta cosecha excede el límite de la plantación.</flux:callout.text>
                                        </flux:callout>
                                    @endif
                                </div>
                            @else
                                <p class="text-xs text-zinc-400 italic mt-3">Ingresa el peso de la cosecha para ver el análisis del límite.</p>
                            @endif
                        </x-agro.card>
                    @endif

                    {{-- Rendimiento Estimado --}}
                    @if($estimatedYield)
                        <x-agro.card>
                            <x-slot:header>
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-lg bg-amber-50 flex items-center justify-center">
                                        <flux:icon icon="chart-bar" class="size-4 text-amber-600" />
                                    </div>
                                    <h3 class="font-semibold text-zinc-900">Rendimiento Estimado</h3>
                                </div>
                            </x-slot:header>

                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
                                <div>
                                    <p class="text-xs text-zinc-600">Estimado</p>
                                    <p class="font-bold text-zinc-900">{{ number_format($estimatedYield->estimated_total_yield, 2) }} kg</p>
                                    <p class="text-xs text-zinc-500">{{ number_format($estimatedYield->estimated_yield_per_hectare, 2) }} kg/ha</p>
                                </div>
                                <div>
                                    <p class="text-xs text-zinc-600">Cosechas registradas</p>
                                    <p class="font-bold text-zinc-900">{{ number_format($totalHarvestedInCampaign, 2) }} kg</p>
                                </div>
                                <div>
                                    <p class="text-xs text-zinc-600">Nueva cosecha</p>
                                    <p class="font-bold text-purple-700">
                                        @if($total_weight && $total_weight > 0)
                                            {{ number_format($total_weight, 2) }} kg
                                        @else
                                            <span class="text-zinc-400">Ingresa el peso</span>
                                        @endif
                                    </p>
                                </div>
                            </div>

                            @if($total_weight && $total_weight > 0 && $yieldVarianceInfo)
                                <div class="mt-3 pt-3 border-t border-zinc-100">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-zinc-600">Total después</span>
                                        <span class="font-bold text-zinc-900">{{ number_format($yieldVarianceInfo['actual'], 2) }} kg</span>
                                    </div>
                                    <div class="flex items-center justify-between mt-2">
                                        <span class="text-xs text-zinc-600">vs Estimado</span>
                                        <span class="font-bold {{ $yieldVarianceInfo['is_over_yield'] ? 'text-green-600' : ($yieldVarianceInfo['is_under_yield'] ? 'text-orange-600' : 'text-zinc-600') }}">
                                            {{ $yieldVarianceInfo['variance'] > 0 ? '+' : '' }}{{ number_format($yieldVarianceInfo['variance'], 2) }} kg
                                            @if($yieldVarianceInfo['variance_percentage'])
                                                ({{ $yieldVarianceInfo['variance_percentage'] > 0 ? '+' : '' }}{{ number_format($yieldVarianceInfo['variance_percentage'], 1) }}%)
                                            @endif
                                        </span>
                                    </div>
                                    @if($yieldVarianceInfo['is_over_yield'])
                                        <flux:callout variant="success" icon="check-circle" class="mt-2">
                                            <flux:callout.text>Sobrerendimiento: La cosecha supera la estimación.</flux:callout.text>
                                        </flux:callout>
                                    @elseif($yieldVarianceInfo['is_under_yield'])
                                        <flux:callout variant="warning" icon="exclamation-triangle" class="mt-2">
                                            <flux:callout.text>Subrendimiento: La cosecha está por debajo de la estimación.</flux:callout.text>
                                        </flux:callout>
                                    @endif
                                </div>
                            @elseif(!$total_weight || $total_weight == 0)
                                <p class="text-xs text-zinc-400 italic mt-3">Ingresa el peso de la cosecha para comparar con la estimación.</p>
                            @endif
                        </x-agro.card>
                    @endif
                </div>
            </x-agro.form-section>
        @endif

        <x-agro.form-section title="Cantidad Cosechada" color="purple">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:field>
                    <flux:label required>Peso Total Cosechado (kg)</flux:label>
                    <flux:input wire:model.live="total_weight" type="number" step="0.001" min="0" id="total_weight" data-cy="total-weight-input" placeholder="0.00" required />
                    <flux:error name="total_weight" />
                </flux:field>
                <flux:field>
                    <flux:label>Rendimiento (kg/ha) — Calculado</flux:label>
                    <flux:input wire:model="yield_per_hectare" type="number" step="0.001" id="yield_per_hectare" placeholder="0.00" readonly class="bg-zinc-50 font-semibold" />
                    <flux:description>Se calcula automáticamente al introducir el peso</flux:description>
                </flux:field>
            </div>
        </x-agro.form-section>

        {{-- Calidad de la Uva (Opcional pero Recomendado) --}}
        <x-agro.form-section title="Calidad de la Uva (Recomendado)" color="amber">
            <flux:callout variant="info" icon="light-bulb" class="mb-6">
                <flux:callout.heading>Tip Profesional</flux:callout.heading>
                <flux:callout.text>
                    Registrar la calidad te permite comparar entre campañas, negociar mejores precios y obtener certificaciones Premium.
                </flux:callout.text>
            </flux:callout>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <flux:field>
                    <flux:label>Grados Baumé (°Bé)</flux:label>
                    <flux:input wire:model="baume_degree" type="number" step="0.001" min="0" max="20" id="baume_degree" placeholder="0.00" />
                    <flux:error name="baume_degree" />
                </flux:field>
                <flux:field>
                    <flux:label>Grados Brix (°Bx)</flux:label>
                    <flux:input wire:model="brix_degree" type="number" step="0.001" min="0" max="40" id="brix_degree" placeholder="0.00" />
                    <flux:error name="brix_degree" />
                </flux:field>
                <flux:field>
                    <flux:label>Acidez Total (g/L)</flux:label>
                    <flux:input wire:model="acidity_level" type="number" step="0.001" min="0" max="20" id="acidity_level" placeholder="0.00" />
                    <flux:error name="acidity_level" />
                </flux:field>
                <flux:field>
                    <flux:label>pH</flux:label>
                    <flux:input wire:model="ph_level" type="number" step="0.001" min="0" max="14" id="ph_level" data-cy="ph-level-input" placeholder="0.00" />
                    <flux:error name="ph_level" />
                </flux:field>
                <flux:field>
                    <flux:label>Alcohol potencial (%vol)</flux:label>
                    <flux:input wire:model="potential_alcohol" type="number" step="0.01" min="0" max="25" id="potential_alcohol" data-cy="potential-alcohol-input" placeholder="0.00" />
                    <flux:error name="potential_alcohol" />
                </flux:field>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                <flux:field>
                    <flux:label>Color</flux:label>
                    <flux:select wire:model="color_rating" id="color_rating">
                        <option value="">Sin evaluar</option>
                        <option value="excelente">Excelente</option>
                        <option value="bueno">Bueno</option>
                        <option value="aceptable">Aceptable</option>
                        <option value="deficiente">Deficiente</option>
                    </flux:select>
                    <flux:error name="color_rating" />
                </flux:field>
                <flux:field>
                    <flux:label>Aroma</flux:label>
                    <flux:select wire:model="aroma_rating" id="aroma_rating">
                        <option value="">Sin evaluar</option>
                        <option value="excelente">Excelente</option>
                        <option value="bueno">Bueno</option>
                        <option value="aceptable">Aceptable</option>
                        <option value="deficiente">Deficiente</option>
                    </flux:select>
                    <flux:error name="aroma_rating" />
                </flux:field>
                <flux:field>
                    <flux:label>Estado Sanitario</flux:label>
                    <flux:select wire:model="health_status" id="health_status">
                        <option value="">Sin evaluar</option>
                        <option value="sano">Sano</option>
                        <option value="daño_leve">Daño Leve</option>
                        <option value="daño_moderado">Daño Moderado</option>
                        <option value="daño_grave">Daño Grave</option>
                    </flux:select>
                    <flux:error name="health_status" />
                </flux:field>
            </div>
        </x-agro.form-section>

        {{-- Estado Sanitario Detallado --}}
        <x-agro.form-section title="Estado Sanitario Detallado (Opcional)" color="rose">
            <flux:callout variant="info" icon="information-circle">
                Porcentaje de uvas afectadas por cada patología. Obligatorio para trazabilidad PAC avanzada.
            </flux:callout>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-4">
                <flux:field>
                    <flux:label>Nº Ticket Vendimia</flux:label>
                    <flux:input wire:model="harvest_ticket_number" type="text" placeholder="Ej: TK-2026-001" />
                    <flux:description>Albarán o ticket de pesada en bodega</flux:description>
                    <flux:error name="harvest_ticket_number" />
                </flux:field>
                <flux:field>
                    <flux:label>% Uvas sanas</flux:label>
                    <flux:input wire:model="sanitary_state_grapes" type="number" step="0.01" min="0" max="100" placeholder="0–100" />
                    <flux:error name="sanitary_state_grapes" />
                </flux:field>
                <flux:field>
                    <flux:label>% Agraces</flux:label>
                    <flux:input wire:model="sanitary_state_agraces" type="number" step="0.01" min="0" max="100" placeholder="0–100" />
                    <flux:error name="sanitary_state_agraces" />
                </flux:field>
                <flux:field>
                    <flux:label>% Botrytis</flux:label>
                    <flux:input wire:model="sanitary_state_botrytis" type="number" step="0.01" min="0" max="100" placeholder="0–100" />
                    <flux:error name="sanitary_state_botrytis" />
                </flux:field>
                <flux:field>
                    <flux:label>% Oídio</flux:label>
                    <flux:input wire:model="sanitary_state_oidium" type="number" step="0.01" min="0" max="100" placeholder="0–100" />
                    <flux:error name="sanitary_state_oidium" />
                </flux:field>
                <flux:field>
                    <flux:label>% Mildiu</flux:label>
                    <flux:input wire:model="sanitary_state_mildew" type="number" step="0.01" min="0" max="100" placeholder="0–100" />
                    <flux:error name="sanitary_state_mildew" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-section title="Destino y Valor Económico" color="green">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <flux:field>
                    <flux:label>Tipo de Destino</flux:label>
                    <flux:select wire:model="destination_type" id="destination_type">
                        <option value="">Selecciona un tipo</option>
                        <option value="winery">Bodega</option>
                        <option value="direct_sale">Venta Directa</option>
                        <option value="cooperative">Cooperativa</option>
                        <option value="self_consumption">Autoconsumo</option>
                        <option value="other">Otro</option>
                    </flux:select>
                    <flux:error name="destination_type" />
                </flux:field>
                <flux:field>
                    <flux:label>Nombre del Destino</flux:label>
                    <flux:input wire:model="destination" type="text" id="destination" placeholder="Ej: Bodegas Rioja, Cooperativa Local" />
                    <flux:error name="destination" />
                </flux:field>
                <flux:field>
                    <flux:label>Comprador</flux:label>
                    <flux:input wire:model="buyer_name" type="text" id="buyer_name" placeholder="Nombre del comprador" />
                    <flux:error name="buyer_name" />
                </flux:field>
            </div>

            <div class="mt-6 border-t pt-6">
                <h4 class="text-sm font-semibold text-zinc-900 mb-4 flex items-center gap-2">
                    <flux:icon icon="truck" class="text-agro-600 w-4 h-4" />
                    Trazabilidad y Transporte (SIEX)
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <flux:field>
                        <flux:label>Nº Documento Transporte</flux:label>
                        <flux:input wire:model="transport_document_number" type="text" id="transport_document_number" data-cy="transport-document-input" placeholder="Ej: GUIA-2023-001" />
                        <flux:error name="transport_document_number" />
                    </flux:field>
                    <flux:field>
                        <flux:label>REGA Destino</flux:label>
                        <flux:input wire:model="destination_rega_code" type="text" id="destination_rega_code" data-cy="destination-rega-input" placeholder="Ej: ES000000000000" />
                        <flux:error name="destination_rega_code" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Matrícula Vehículo</flux:label>
                        <flux:input wire:model="vehicle_plate" type="text" id="vehicle_plate" data-cy="vehicle-plate-input" placeholder="Ej: 1234 ABC" />
                        <flux:error name="vehicle_plate" />
                    </flux:field>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <flux:field>
                    <flux:label>Precio por Kilogramo (€/kg)</flux:label>
                    <flux:input wire:model.live="price_per_kg" type="number" step="0.001" min="0" id="price_per_kg" placeholder="0.000" />
                    <flux:error name="price_per_kg" />
                </flux:field>
                <flux:field>
                    <flux:label>Valor Total (€) — Calculado</flux:label>
                    <flux:input wire:model="total_value" type="number" step="0.001" id="total_value" placeholder="0.00" readonly class="bg-zinc-50 font-semibold" />
                    <flux:description>Se calcula automáticamente: Peso × Precio/kg</flux:description>
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-section title="Información Adicional" color="green" class="pb-6">
            <div class="mb-6">
                <flux:label class="mb-3 block font-semibold">¿Quién realizó el trabajo?</flux:label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="border-2 rounded-lg p-4 transition-all {{ $workType === 'crew' ? 'border-agro-500 bg-agro-50' : 'border-zinc-200 hover:border-zinc-300' }}">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="radio" wire:model.live="workType" value="crew" class="w-5 h-5 text-agro-600 focus:ring-agro-500" />
                            <div class="flex-1">
                                <span class="font-semibold text-zinc-900">Equipo completo</span>
                                <p class="text-sm text-zinc-500 mt-1">Todo el equipo trabajó en esta actividad</p>
                            </div>
                        </label>
                        @if($workType === 'crew')
                            <div class="mt-4">
                                <flux:field>
                                    <flux:label>Selecciona el equipo</flux:label>
                                    <flux:select wire:model="crew_id" id="crew_id">
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
                            <input type="radio" wire:model.live="workType" value="individual" class="w-5 h-5 text-agro-600 focus:ring-agro-500" />
                            <div class="flex-1">
                                <span class="font-semibold text-zinc-900">Viticultor individual</span>
                                <p class="text-sm text-zinc-500 mt-1">Un viticultor específico realizó el trabajo</p>
                            </div>
                        </label>
                        @if($workType === 'individual')
                            <div class="mt-4">
                                <flux:field>
                                    <flux:label>Selecciona el viticultor</flux:label>
                                    <flux:select wire:model="crew_member_id" id="crew_member_id">
                                        <option value="">Selecciona un viticultor</option>
                                        @if(isset($allViticulturists))
                                            @foreach($allViticulturists as $viticulturist)
                                                <option value="{{ $viticulturist->id }}">
                                                    {{ $viticulturist->name }}@if($viticulturist->id === auth()->id()) (Yo)@endif
                                                </option>
                                            @endforeach
                                        @endif
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
                <flux:label>Maquinaria</flux:label>
                <flux:select wire:model="machinery_id" id="machinery_id">
                    <option value="">Sin maquinaria asignada</option>
                    @foreach($machinery as $item)
                        <option value="{{ $item->id }}">{{ $item->name }} ({{ $item->type }})</option>
                    @endforeach
                </flux:select>
                <flux:error name="machinery_id" />
            </flux:field>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <flux:field>
                    <flux:label>Condiciones Meteorológicas</flux:label>
                    <flux:input wire:model="weather_conditions" type="text" id="weather_conditions" placeholder="Ej: Soleado, nublado, lluvia ligera" />
                    <flux:error name="weather_conditions" />
                </flux:field>
                <flux:field>
                    <flux:label>Temperatura (°C)</flux:label>
                    <flux:input wire:model="temperature" type="number" step="0.1" id="temperature" placeholder="20.0" />
                    <flux:error name="temperature" />
                </flux:field>
            </div>

            <div class="mt-6">
                <flux:field>
                    <flux:label>Notas Adicionales</flux:label>
                    <flux:textarea wire:model="notes" id="notes" rows="4" placeholder="Observaciones, estado del viñedo, incidencias, etc." />
                    <flux:error name="notes" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-section title="Notas de Edición" color="amber">
            <flux:callout variant="info" icon="information-circle" class="mb-4">
                <flux:callout.text>Si estás realizando cambios importantes, explica el motivo de la edición.</flux:callout.text>
            </flux:callout>
            <flux:field>
                <flux:label>Motivo de la edición (opcional pero recomendado)</flux:label>
                <flux:textarea wire:model="edit_notes" id="edit_notes" rows="3" placeholder="Ej: Corrección de peso, actualización de calidad, cambio de destino..." />
                <flux:description>Este motivo quedará registrado en el historial de ediciones.</flux:description>
                <flux:error name="edit_notes" />
            </flux:field>
        </x-agro.form-section>

        <x-agro.form-actions 
            :cancel-url="roleRoute('viticulturist.digital-notebook.harvest.show', $harvest->id)"
            submit-label="Actualizar Cosecha"
        />
    </form>
</x-agro.form-card>
