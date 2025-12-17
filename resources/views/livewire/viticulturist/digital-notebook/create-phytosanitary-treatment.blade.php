@php
    $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>';
@endphp

<x-form-card
    title="Registrar Tratamiento Fitosanitario"
    description="Registra un nuevo tratamiento fitosanitario en el cuaderno digital"
    :icon="$icon"
    icon-color="from-red-600 to-red-700"
    :back-url="route('viticulturist.digital-notebook')"
>
    <form wire:submit="save" class="space-y-8">
        <x-form-section title="Información Básica" color="green">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Parcela -->
                    <div>
                        <x-label for="plot_id" required>Parcela</x-label>
                        <x-select 
                            wire:model="plot_id" 
                            id="plot_id"
                            :error="$errors->first('plot_id')"
                            required
                        >
                            <option value="">Selecciona una parcela</option>
                            @foreach($plots as $plot)
                                <option value="{{ $plot->id }}">{{ $plot->name }}</option>
                            @endforeach
                        </x-select>
                    </div>

                    <!-- Fecha -->
                    <div>
                        <x-label for="activity_date" required>Fecha del Tratamiento</x-label>
                        <x-input 
                            wire:model="activity_date" 
                            type="date" 
                            id="activity_date"
                            :error="$errors->first('activity_date')"
                            required
                        />
                    </div>
                </div>
        </x-form-section>

        <x-form-section title="Producto Fitosanitario" color="green">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Producto -->
                    <div>
                        <x-label for="product_id" required>Producto</x-label>
                        <x-select 
                            wire:model="product_id" 
                            id="product_id"
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
                        <x-label for="target_pest">Plaga/Enfermedad Objetivo</x-label>
                        <x-input 
                            wire:model="target_pest" 
                            type="text" 
                            id="target_pest"
                            placeholder="Ej: Mildiu, Oídio, etc."
                            :error="$errors->first('target_pest')"
                        />
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
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Cuadrilla -->
                    <div>
                        <x-label for="crew_id">Cuadrilla</x-label>
                        <x-select 
                            wire:model="crew_id" 
                            id="crew_id"
                            :error="$errors->first('crew_id')"
                        >
                            <option value="">Sin cuadrilla asignada</option>
                            @foreach($crews as $crew)
                                <option value="{{ $crew->id }}">{{ $crew->name }}</option>
                            @endforeach
                        </x-select>
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

