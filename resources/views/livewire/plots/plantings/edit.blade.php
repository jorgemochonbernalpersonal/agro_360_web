@php
    $plotIcon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>';
@endphp

<x-form-card
    :title="'Editar Plantación en ' . $planting->plot->name"
    description="Actualiza los datos de una plantación de variedad de uva en esta parcela"
    :icon="$plotIcon"
    icon-color="from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]"
    :back-url="route('plots.plantings.index')"
>
    <form wire:submit.prevent="update" class="space-y-8">
        <x-form-section title="Datos de la Plantación" color="green">
            <!-- Nombre de la plantación -->
            <div class="mb-6">
                <x-label for="name">Nombre de la plantación (Opcional)</x-label>
                <x-input wire:model="name" type="text" id="name"
                    :error="$errors->first('name')" 
                    placeholder="Ej: Parcela Norte - Tempranillo, Bloque A, etc." />
                <p class="mt-1 text-xs text-gray-500">Útil para diferenciar múltiples plantaciones en la misma parcela</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Variedad de uva -->
                <div>
                    <x-label for="grape_variety_id">Variedad de uva</x-label>
                    <x-select wire:model="grape_variety_id" id="grape_variety_id"
                        :error="$errors->first('grape_variety_id')">
                        <option value="">Seleccionar...</option>
                        @foreach ($grapeVarieties as $variety)
                            <option value="{{ $variety->id }}">
                                {{ $variety->name }} @if($variety->code) ({{ $variety->code }}) @endif
                            </option>
                        @endforeach
                    </x-select>
                </div>

                <!-- Superficie plantada -->
                <div>
                    <x-label for="area_planted" required>Superficie plantada (ha)</x-label>
                    <x-input wire:model="area_planted" type="number" step="0.001" id="area_planted"
                        :error="$errors->first('area_planted')" required />
                </div>
            </div>

            <!-- Límite de cosecha -->
            <div class="mt-6">
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg mb-4">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <h4 class="text-sm font-semibold text-blue-900">Límite de Cosecha (Opcional)</h4>
                            <p class="text-xs text-blue-800 mt-1">
                                Establece un límite máximo de cosecha para esta plantación. Útil para controlar cuotas, restricciones legales o planificación comercial.
                            </p>
                        </div>
                    </div>
                </div>
                <x-label for="harvest_limit_kg">Límite máximo de cosecha (kg)</x-label>
                <x-input wire:model="harvest_limit_kg" type="number" step="0.001" id="harvest_limit_kg"
                    :error="$errors->first('harvest_limit_kg')" placeholder="Ej: 10000" />
                <p class="mt-1 text-xs text-gray-500">Deja vacío si no hay límite establecido</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                <!-- Año plantación -->
                <div>
                    <x-label for="planting_year">Año de plantación</x-label>
                    <x-input wire:model="planting_year" type="number" id="planting_year"
                        :error="$errors->first('planting_year')" />
                </div>

                <!-- Fecha exacta -->
                <div>
                    <x-label for="planting_date">Fecha de plantación</x-label>
                    <x-input wire:model="planting_date" type="date" id="planting_date"
                        :error="$errors->first('planting_date')" />
                </div>

                <!-- Riego -->
                <div class="flex items-center mt-6 md:mt-0">
                    <label class="flex items-center">
                        <input wire:model="irrigated" type="checkbox"
                            class="w-4 h-4 text-[var(--color-agro-green-dark)] border-gray-300 rounded focus:ring-[var(--color-agro-green-dark)]">
                        <span class="ml-2 text-sm font-semibold text-gray-700">Con riego</span>
                    </label>
                    @error('irrigated')
                        <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </x-form-section>

        <x-form-section title="Densidad y Marco de Plantación" color="green">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div>
                    <x-label for="vine_count">Número de cepas</x-label>
                    <x-input wire:model="vine_count" type="number" id="vine_count"
                        :error="$errors->first('vine_count')" />
                </div>
                <div>
                    <x-label for="density">Densidad (cepas/ha)</x-label>
                    <x-input wire:model="density" type="number" id="density"
                        :error="$errors->first('density')" />
                </div>
                <div>
                    <x-label for="row_spacing">Distancia entre filas (m)</x-label>
                    <x-input wire:model="row_spacing" type="number" step="0.01" id="row_spacing"
                        :error="$errors->first('row_spacing')" />
                </div>
                <div>
                    <x-label for="vine_spacing">Distancia entre cepas (m)</x-label>
                    <x-input wire:model="vine_spacing" type="number" step="0.01" id="vine_spacing"
                        :error="$errors->first('vine_spacing')" />
                </div>
            </div>
        </x-form-section>

        <x-form-section title="Características Técnicas" color="green">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <x-label for="rootstock">Portainjerto</x-label>
                    <x-input wire:model="rootstock" type="text" id="rootstock"
                        :error="$errors->first('rootstock')" />
                </div>
                <div>
                    <x-label for="training_system_id">Sistema de conducción</x-label>
                    <x-select wire:model="training_system_id" id="training_system_id"
                        :error="$errors->first('training_system_id')">
                        <option value="">Seleccionar...</option>
                        @foreach($trainingSystems as $system)
                            <option value="{{ $system->id }}">{{ $system->name }}</option>
                        @endforeach
                    </x-select>
                </div>
            </div>

            <div class="mt-6">
                <x-label for="status" required>Estado de la plantación</x-label>
                <x-select wire:model="status" id="status" :error="$errors->first('status')" required>
                    <option value="active">Activa</option>
                    <option value="removed">Arrancada</option>
                    <option value="experimental">Experimental</option>
                    <option value="replanting">Replantación</option>
                </x-select>
            </div>

            <div class="mt-6">
                <x-label for="notes">Observaciones</x-label>
                <x-textarea wire:model="notes" id="notes" rows="3"
                    :error="$errors->first('notes')" placeholder="Notas sobre la plantación, clones, etc." />
            </div>
        </x-form-section>

        <x-form-actions :cancel-url="route('plots.plantings.index')" submit-label="Actualizar Plantación" />
    </form>
</x-form-card>


