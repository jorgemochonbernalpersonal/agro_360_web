<div>
    <x-agro.form-card
        title="{{ __('Editar Recepción de Uva') }}"
        :description="__('Modifica los datos de la recepción registrada')"
        :back-url="roleRoute('grape-reception.index')"
    >
        <form wire:submit.prevent="save" class="space-y-8">

        {{-- Sección 1: Datos esenciales --}}
        <x-agro.form-section title="{{ __('Datos Esenciales') }}">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <flux:field>
                    <flux:label>{{ __('Añada') }}</flux:label>
                    <flux:input value="{{ $vintageYear }}" disabled />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Kg recibidos *') }}</flux:label>
                    <flux:input wire:model.live="total_weight" type="number" step="0.001" min="0" placeholder="0.000" />
                    <flux:error name="total_weight" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Depósito destino *') }}</flux:label>
                    <flux:select wire:model="container_id">
                        <flux:select.option value="">{{ __('Selecciona un depósito...') }}</flux:select.option>
                        @foreach($availableContainers as $container)
                            @php
                                $isCurrent   = $container->id == $harvest->container_id;
                                $available   = max(0, $container->capacity - $container->used_capacity);
                                $freeForThis = $isCurrent ? $available + (float) $harvest->total_weight : $available;
                            @endphp
                            <flux:select.option value="{{ $container->id }}">
                                {{ $container->name }} ({{ number_format($freeForThis, 0) }} kg disp.)
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="container_id" />
                </flux:field>
            </div>
        </x-agro.form-section>

        {{-- Sección 2: Viticultor y Parcela (solo lectura) --}}
        <x-agro.form-section title="{{ __('Viticultor y Parcela') }}">
            <div class="space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                    <div class="p-3 bg-zinc-50 rounded-lg border border-zinc-200">
                        <p class="text-xs text-zinc-500 mb-1">{{ __('Viticultor') }}</p>
                        <p class="font-medium text-zinc-900">{{ $viticulturistName }}</p>
                    </div>
                    <div class="p-3 bg-zinc-50 rounded-lg border border-zinc-200">
                        <p class="text-xs text-zinc-500 mb-1">{{ __('Parcela') }}</p>
                        <p class="font-medium text-zinc-900">{{ $plotName }}</p>
                    </div>
                    <div class="p-3 bg-zinc-50 rounded-lg border border-zinc-200">
                        <p class="text-xs text-zinc-500 mb-1">{{ __('Plantación / Variedad') }}</p>
                        <p class="font-medium text-zinc-900">{{ $plantingLabel }}</p>
                    </div>
                </div>

                {{-- Control de límite de cosecha --}}
                @if($harvestLimitInfo)
                    @php $info = $harvestLimitInfo; @endphp
                    <div class="p-3 rounded-lg border {{ $info['exceeds'] ? 'bg-red-50 border-red-200' : 'bg-agro-50 border-agro-200' }}">
                        <p class="text-xs font-semibold {{ $info['exceeds'] ? 'text-red-700' : 'text-agro-700' }} mb-2">
                            Control de límite de cosecha
                        </p>
                        <div class="grid grid-cols-2 gap-x-4 gap-y-1 text-xs">
                            <span class="text-zinc-600">{{ __('Límite PAC base:') }}</span>
                            <span class="font-medium">{{ number_format($info['raw_limit'], 0) }} kg</span>
                            @if($info['age_factor'] < 100)
                                <span class="text-zinc-600">{{ __('Factor edad:') }}</span>
                                <span class="font-medium text-amber-700">{{ $info['age_factor'] }}%</span>
                            @endif
                            <span class="text-zinc-600">{{ __('Límite PAC efectivo:') }}</span>
                            <span class="font-medium text-blue-700">{{ number_format($info['pac_limit'], 0) }} kg</span>
                            @if($info['has_forecast'])
                                <span class="text-zinc-600">{{ __('Previsión bodega:') }}</span>
                                <span class="font-medium text-agro-700">{{ number_format($info['forecast_kg'], 0) }} kg</span>
                                <span class="text-zinc-600 font-semibold">{{ __('Límite operativo:') }}</span>
                                <span class="font-bold text-agro-700">{{ number_format($info['limit'], 0) }} kg</span>
                            @endif
                            <span class="text-zinc-600">{{ __('Resto recepciones (añada):') }}</span>
                            <span class="font-medium">{{ number_format($info['harvested'], 0) }} kg</span>
                            @if($info['adding'] > 0)
                                <span class="text-zinc-600">{{ __('Esta recepción:') }}</span>
                                <span class="font-medium">{{ number_format($info['adding'], 0) }} kg</span>
                                <span class="text-zinc-600">{{ __('Total:') }}</span>
                                <span class="font-bold {{ $info['exceeds'] ? 'text-red-700' : 'text-agro-700' }}">
                                    {{ number_format($info['new_total'], 0) }} kg ({{ $info['percentage'] }}%)
                                </span>
                            @endif
                        </div>
                        @if($info['age_factor'] < 100)
                            <p class="mt-2 text-xs text-amber-700">{{ __('Plantación joven — límite PAC reducido por factor de edad.') }}</p>
                        @endif
                        @if($info['exceeds'])
                            <p class="mt-2 text-xs text-red-700 font-medium">
                                ⚠ Esta recepción supera el límite {{ $info['has_forecast'] ? 'de la previsión' : 'PAC' }}.
                            </p>
                        @endif
                        @if(!$info['exceeds'] && isset($info['exceeds_pac']) && $info['exceeds_pac'])
                            <p class="mt-2 text-xs text-orange-600 font-medium">{{ __('⚠ Se supera el límite PAC aunque esté dentro de la previsión.') }}</p>
                        @endif
                    </div>
                @endif
            </div>
        </x-agro.form-section>

        {{-- Sección 3: Datos de la recepción --}}
        <x-agro.form-section title="{{ __('Datos de la Recepción') }}">
            <div class="space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <flux:field>
                        <flux:label>{{ __('Fecha de recepción *') }}</flux:label>
                        <flux:input wire:model="harvest_start_date" type="date" />
                        <flux:error name="harvest_start_date" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Hora de descarga') }}</flux:label>
                        <flux:input wire:model="harvest_time" type="time" />
                        <flux:error name="harvest_time" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Nº de ticket/Vendimia') }}</flux:label>
                        <flux:input wire:model="harvest_ticket_number" :placeholder="__('Ej: VND-2026-001')" />
                        <flux:error name="harvest_ticket_number" />
                    </flux:field>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <flux:field>
                        <flux:label>{{ __('Precio / kg (€)') }}</flux:label>
                        <flux:input wire:model="price_per_kg" type="number" step="0.001" min="0" placeholder="0.000" />
                        <flux:error name="price_per_kg" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Vehículo (matrícula)') }}</flux:label>
                        <flux:input wire:model="vehicle_plate" :placeholder="__('Ej: 1234 ABC')" />
                        <flux:error name="vehicle_plate" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Doc. transporte') }}</flux:label>
                        <flux:input wire:model="transport_document_number" :placeholder="__('Nº documento')" />
                        <flux:error name="transport_document_number" />
                    </flux:field>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <flux:field>
                        <flux:label>{{ __('Código REGA destino') }}</flux:label>
                        <flux:input wire:model="destination_rega_code" :placeholder="__('Ej: ES010000001234')" />
                        <flux:description>Código REGA de tu bodega como destino de la uva.</flux:description>
                        <flux:error name="destination_rega_code" />
                    </flux:field>
                </div>
            </div>
        </x-agro.form-section>

        {{-- Sección 4: Calidad --}}
        <x-agro.form-section title="{{ __('Parámetros de Calidad') }}" :description="__('Opcional')">
            <div class="space-y-5">
                <div class="grid grid-cols-2 md:grid-cols-5 gap-5">
                    <flux:field>
                        <flux:label>{{ __('Grado Baumé (°Bé)') }}</flux:label>
                        <flux:input wire:model="baume_degree" type="number" step="0.1" min="0" max="20" :placeholder="__('—')" />
                        <flux:error name="baume_degree" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Brix (°Bx)') }}</flux:label>
                        <flux:input wire:model="brix_degree" type="number" step="0.1" min="0" max="40" :placeholder="__('—')" />
                        <flux:error name="brix_degree" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Alcohol potencial (%)') }}</flux:label>
                        <flux:input wire:model="potential_alcohol" type="number" step="0.1" min="0" max="25" :placeholder="__('—')" />
                        <flux:error name="potential_alcohol" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Acidez total (g/L)') }}</flux:label>
                        <flux:input wire:model="acidity_level" type="number" step="0.1" min="0" max="20" :placeholder="__('—')" />
                        <flux:error name="acidity_level" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('pH') }}</flux:label>
                        <flux:input wire:model="ph_level" type="number" step="0.01" min="0" max="14" :placeholder="__('—')" />
                        <flux:error name="ph_level" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>{{ __('Estado sanitario general') }}</flux:label>
                    <flux:select wire:model="health_status" class="max-w-xs">
                        <flux:select.option value="">{{ __('Sin especificar') }}</flux:select.option>
                        <flux:select.option value="sano">{{ __('Sano') }}</flux:select.option>
                        <flux:select.option value="daño_leve">{{ __('Daño leve') }}</flux:select.option>
                        <flux:select.option value="daño_moderado">{{ __('Daño moderado') }}</flux:select.option>
                        <flux:select.option value="daño_grave">{{ __('Daño grave') }}</flux:select.option>
                    </flux:select>
                    <flux:error name="health_status" />
                </flux:field>

                <div>
                    <p class="text-sm font-medium text-zinc-700 mb-3">{{ __('Estado sanitario detallado (%)') }}</p>
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                        <flux:field>
                            <flux:label class="text-xs">{{ __('Granos sanos') }}</flux:label>
                            <flux:input wire:model="sanitary_state_grapes" type="number" step="0.1" min="0" max="100" placeholder="%" />
                            <flux:error name="sanitary_state_grapes" />
                        </flux:field>
                        <flux:field>
                            <flux:label class="text-xs">{{ __('Agraces') }}</flux:label>
                            <flux:input wire:model="sanitary_state_agraces" type="number" step="0.1" min="0" max="100" placeholder="%" />
                            <flux:error name="sanitary_state_agraces" />
                        </flux:field>
                        <flux:field>
                            <flux:label class="text-xs">{{ __('Botrytis') }}</flux:label>
                            <flux:input wire:model="sanitary_state_botrytis" type="number" step="0.1" min="0" max="100" placeholder="%" />
                            <flux:error name="sanitary_state_botrytis" />
                        </flux:field>
                        <flux:field>
                            <flux:label class="text-xs">{{ __('Oidio') }}</flux:label>
                            <flux:input wire:model="sanitary_state_oidium" type="number" step="0.1" min="0" max="100" placeholder="%" />
                            <flux:error name="sanitary_state_oidium" />
                        </flux:field>
                        <flux:field>
                            <flux:label class="text-xs">{{ __('Mildiu') }}</flux:label>
                            <flux:input wire:model="sanitary_state_mildew" type="number" step="0.1" min="0" max="100" placeholder="%" />
                            <flux:error name="sanitary_state_mildew" />
                        </flux:field>
                    </div>
                </div>
            </div>
        </x-agro.form-section>

        {{-- Sección 5: Descarte --}}
        <x-agro.form-section title="{{ __('Descarte') }}">
            <div class="space-y-4">
                <flux:field>
                    <flux:checkbox wire:model.live="disqualified" :label="__('Uva descartada / no apta para vinificación')" />
                    <flux:description>Marca esta opción si la uva ha sido rechazada total o parcialmente.</flux:description>
                </flux:field>

                @if($disqualified)
                    <flux:field>
                        <flux:label>{{ __('Motivo del descarte') }}</flux:label>
                        <flux:input wire:model="disqualified_reason" :placeholder="__('Ej: Exceso de botrytis, contaminación, etc.')" />
                        <flux:error name="disqualified_reason" />
                    </flux:field>
                @endif
            </div>
        </x-agro.form-section>

        {{-- Sección 6: Notas --}}
        <x-agro.form-section title="{{ __('Notas') }}">
            <flux:field>
                <flux:label>{{ __('Observaciones (opcional)') }}</flux:label>
                <flux:textarea wire:model="notes" rows="3" :placeholder="__('Observaciones sobre esta recepción...')" />
                <flux:error name="notes" />
            </flux:field>
        </x-agro.form-section>

        <x-agro.form-actions
            :cancel-url="roleRoute('grape-reception.index')"
            submit-:label="__('Guardar Cambios')"
        />

        </form>
    </x-agro.form-card>
</div>
