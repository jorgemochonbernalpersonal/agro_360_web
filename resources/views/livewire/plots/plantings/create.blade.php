<div>
<x-agro.form-card
    :title="__('Nueva Plantación en') . ' ' . $plot->name"
    :description="__('Registra una plantación de variedad de uva en esta parcela')"
    :back-url="route('plots.plantings.index')"
>
    <form wire:submit.prevent="save" class="space-y-8">
        <x-agro.form-section :title="__('Datos de la Plantación')">
            <!-- Nombre de la plantacion -->
            <div class="mb-6">
                <flux:field>
                    <flux:label for="name">{{ __('Nombre de la plantación (Opcional)') }}</flux:label>
                    <flux:input wire:model="name" type="text" id="name"
                        :placeholder="__('Ej: Parcela Norte - Tempranillo, Bloque A, etc.')" />
                    <flux:error name="name" />
                </flux:field>
                <p class="mt-1 text-xs text-zinc-500">{{ __('Útil para diferenciar múltiples plantaciones en la misma parcela') }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Variedad / Cultivo -->
                <flux:field>
                    <flux:label for="grape_variety_id">{{ $wineryOnly ? __('Variedad de uva') : __('Variedad / Cultivo') }} *</flux:label>
                    <flux:select wire:model="grape_variety_id" id="grape_variety_id">
                        <option value="">{{ __('Seleccionar...') }}</option>
                        @foreach ($grapeVarieties->groupBy('crop_type') as $type => $varieties)
                            @if(!$wineryOnly && count($grapeVarieties->groupBy('crop_type')) > 1)
                                <optgroup label="{{ \App\Models\GrapeVariety::CROP_TYPES[$type] ?? $type }}">
                            @endif
                            @foreach ($varieties as $variety)
                                <option value="{{ $variety->id }}">
                                    {{ $variety->name }} @if($variety->code) ({{ $variety->code }}) @endif
                                </option>
                            @endforeach
                            @if(!$wineryOnly && count($grapeVarieties->groupBy('crop_type')) > 1)
                                </optgroup>
                            @endif
                        @endforeach
                    </flux:select>
                    <flux:error name="grape_variety_id" />
                </flux:field>

                <!-- Superficie plantada -->
                <flux:field>
                    <flux:label for="area_planted">{{ __('Superficie plantada (ha)') }} *</flux:label>
                    <flux:input wire:model.live="area_planted" type="number" step="0.001" id="area_planted" required />
                    <flux:error name="area_planted" />
                </flux:field>
            </div>

            <!-- Limite de cosecha -->
            <div class="mt-6">
                <flux:field>
                    <flux:label for="harvest_limit_kg">{{ __('Límite de cosecha (kg)') }}</flux:label>
                    <flux:input wire:model.live="harvest_limit_kg" type="number" step="0.001" id="harvest_limit_kg" :placeholder="__('Sin límite')" />
                    <flux:error name="harvest_limit_kg" />
                    @if($harvest_limit_kg && $area_planted)
                        <flux:description>
                            {{ number_format($harvest_limit_kg / $area_planted, 0, ',', '.') }} kg/ha × {{ number_format($area_planted, 3, ',', '.') }} ha · {{ __('Editable manualmente') }}
                        </flux:description>
                    @else
                        <flux:description>{{ __('Se calcula automáticamente al introducir la superficie si hay kg/ha por defecto en ajustes.') }}</flux:description>
                    @endif
                </flux:field>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <!-- Año de plantación -->
                <flux:field>
                    <flux:label for="planting_year">{{ __('Año de plantación') }}</flux:label>
                    <flux:input wire:model="planting_year" type="number" id="planting_year" />
                    <flux:error name="planting_year" />
                </flux:field>

                <!-- Riego -->
                <div class="mt-6 md:mt-0">
                    <flux:label>{{ __('Con riego') }}</flux:label>
                    <div class="flex items-center gap-3 mt-1">
                        <flux:switch wire:model.live="irrigated" />
                        <span class="text-sm text-zinc-600">{{ $irrigated ? __('Sí, parcela con riego') : __('No') }}</span>
                    </div>
                </div>
            </div>
        </x-agro.form-section>

        <x-agro.form-section :title="__('Densidad y Marco de Plantación')">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <flux:field>
                    <flux:label for="vine_count">{{ __('Número de cepas') }}</flux:label>
                    <flux:input wire:model="vine_count" type="number" id="vine_count" />
                    <flux:error name="vine_count" />
                </flux:field>
                <flux:field>
                    <flux:label for="density">{{ __('Densidad (cepas/ha)') }}</flux:label>
                    <flux:input wire:model="density" type="number" id="density" />
                    <flux:error name="density" />
                </flux:field>
                <flux:field>
                    <flux:label for="row_spacing">{{ __('Distancia entre filas (m)') }}</flux:label>
                    <flux:input wire:model="row_spacing" type="number" step="0.01" id="row_spacing" />
                    <flux:error name="row_spacing" />
                </flux:field>
                <flux:field>
                    <flux:label for="vine_spacing">{{ __('Distancia entre cepas (m)') }}</flux:label>
                    <flux:input wire:model="vine_spacing" type="number" step="0.01" id="vine_spacing" />
                    <flux:error name="vine_spacing" />
                </flux:field>
            </div>
        </x-agro.form-section>

        <x-agro.form-section :title="__('Características Técnicas')">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:field>
                    <flux:label for="rootstock">{{ __('Portainjerto') }}</flux:label>
                    <flux:input wire:model="rootstock" type="text" id="rootstock" />
                    <flux:error name="rootstock" />
                </flux:field>
                <flux:field>
                    <flux:label for="training_system_id">{{ __('Sistema de conducción') }}</flux:label>
                    <flux:select wire:model="training_system_id" id="training_system_id">
                        <option value="">{{ __('Seleccionar...') }}</option>
                        @foreach($trainingSystems as $system)
                            <option value="{{ $system->id }}">{{ $system->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="training_system_id" />
                </flux:field>
            </div>

            <div class="mt-6">
                <flux:field>
                    <flux:label for="status">{{ __('Estado de la plantación') }} *</flux:label>
                    <flux:select wire:model="status" id="status" required>
                        <option value="active">{{ __('Activa') }}</option>
                        <option value="removed">{{ __('Arrancada') }}</option>
                        <option value="experimental">{{ __('Experimental') }}</option>
                        <option value="replanting">{{ __('Replantación') }}</option>
                    </flux:select>
                    <flux:error name="status" />
                </flux:field>
            </div>

            <div class="mt-6">
                <flux:field>
                    <flux:label for="notes">{{ __('Observaciones') }}</flux:label>
                    <flux:textarea wire:model="notes" id="notes" rows="3"
                        :placeholder="__('Notas sobre la plantación, clones, etc.')" />
                    <flux:error name="notes" />
                </flux:field>
            </div>
        </x-agro.form-section>

        {{-- Autorizacion PAC (solo viticultores vinculados a DO/bodega) --}}
        @if(!auth()->user()->isWinery() && auth()->user()->hasSupervisor())
        <x-agro.form-section :title="__('Autorización de Plantación PAC')">
            <flux:callout variant="warning" icon="exclamation-triangle">
                <flux:callout.heading>{{ __('Obligatorio para plantaciones desde 2016') }}</flux:callout.heading>
                <flux:callout.text>
                    {{ __('Las plantaciones realizadas a partir del 1 de enero de 2016 requieren autorización administrativa.') }}
                </flux:callout.text>
            </flux:callout>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                {{-- Numero de Autorizacion --}}
                <flux:field>
                    <flux:label for="planting_authorization">{{ __('Número de Autorización') }}</flux:label>
                    <flux:input wire:model="planting_authorization" type="text" id="planting_authorization"
                        :placeholder="__('Ej: AUT/2023/12345')" />
                    <flux:error name="planting_authorization" />
                </flux:field>

                {{-- Fecha de Autorizacion --}}
                <flux:field>
                    <flux:label for="authorization_date">{{ __('Fecha de Autorización') }}</flux:label>
                    <flux:input wire:model="authorization_date" type="date" id="authorization_date" />
                    <flux:error name="authorization_date" />
                </flux:field>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                {{-- Tipo de Derecho --}}
                <flux:field>
                    <flux:label for="right_type">{{ __('Tipo de Derecho') }}</flux:label>
                    <flux:select wire:model="right_type" id="right_type">
                        <option value="">{{ __('Seleccionar...') }}</option>
                        <option value="nueva">{{ __('Nueva Plantación') }}</option>
                        <option value="replantacion">{{ __('Replantación') }}</option>
                        <option value="conversion">{{ __('Conversión') }}</option>
                        <option value="transferencia">{{ __('Transferencia') }}</option>
                    </flux:select>
                    <flux:error name="right_type" />
                </flux:field>

                {{-- Fecha de Arranque (solo para replantaciones) --}}
                <flux:field>
                    <flux:label for="uprooting_date">{{ __('Fecha de Arranque (solo replantaciones)') }}</flux:label>
                    <flux:input wire:model="uprooting_date" type="date" id="uprooting_date" />
                    <flux:error name="uprooting_date" />
                    <flux:description>{{ __('Solo aplicable si es una replantación') }}</flux:description>
                </flux:field>
            </div>

            {{-- Denominacion de Origen --}}
            <div class="mt-6">
                <flux:field>
                    <flux:label for="designation_of_origin">{{ __('Denominación de Origen') }}</flux:label>
                    <flux:input wire:model="designation_of_origin" type="text" id="designation_of_origin"
                        :placeholder="__('Ej: DO Rioja, DOCa Priorat, IGP Castilla')" />
                    <flux:error name="designation_of_origin" />
                    <flux:description>{{ __('DO, DOCa o IGP si aplica') }}</flux:description>
                </flux:field>
            </div>
        </x-agro.form-section>
        @endif

        <x-agro.form-actions :cancel-url="route('plots.plantings.index')" :submit-label="__('Guardar Plantación')" />
    </form>
</x-agro.form-card>
</div>
