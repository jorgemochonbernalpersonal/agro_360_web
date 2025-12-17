<div class="space-y-6 animate-fade-in">
    <!-- Header -->
    @php
        $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>';
    @endphp
    <x-page-header
        :icon="$icon"
        title="Registrar Tratamiento Fitosanitario"
        description="Registra un nuevo tratamiento fitosanitario en el cuaderno digital"
        icon-color="from-red-600 to-red-700"
    >
        <x-slot:actionButton>
            <a href="{{ route('viticulturist.digital-notebook') }}" class="group">
                <button class="flex items-center gap-2 px-6 py-3 rounded-xl bg-gray-600 text-white hover:bg-gray-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105 font-semibold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Volver
                </button>
            </a>
        </x-slot:actionButton>
    </x-page-header>

    <!-- Formulario -->
    <div class="glass-card rounded-2xl p-8">
        <form wire:submit="save" class="space-y-8">
            <!-- Información Básica -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-bold text-[var(--color-agro-green-dark)] mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Información Básica
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Parcela -->
                    <div>
                        <label for="plot_id" class="block text-sm font-semibold text-gray-700 mb-2">
                            Parcela *
                        </label>
                        <select 
                            wire:model="plot_id" 
                            id="plot_id"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-transparent transition-all"
                            required
                        >
                            <option value="">Selecciona una parcela</option>
                            @foreach($plots as $plot)
                                <option value="{{ $plot->id }}">{{ $plot->name }}</option>
                            @endforeach
                        </select>
                        @error('plot_id') 
                            <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> 
                        @enderror
                    </div>

                    <!-- Fecha -->
                    <div>
                        <label for="activity_date" class="block text-sm font-semibold text-gray-700 mb-2">
                            Fecha del Tratamiento *
                        </label>
                        <input 
                            wire:model="activity_date" 
                            type="date" 
                            id="activity_date"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-transparent transition-all"
                            required
                        >
                        @error('activity_date') 
                            <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> 
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Información del Producto -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-bold text-[var(--color-agro-green-dark)] mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                    </svg>
                    Producto Fitosanitario
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Producto -->
                    <div>
                        <label for="product_id" class="block text-sm font-semibold text-gray-700 mb-2">
                            Producto *
                        </label>
                        <select 
                            wire:model="product_id" 
                            id="product_id"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-transparent transition-all"
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
                        </select>
                        @error('product_id') 
                            <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> 
                        @enderror
                    </div>

                    <!-- Plaga/Enfermedad Objetivo -->
                    <div>
                        <label for="target_pest" class="block text-sm font-semibold text-gray-700 mb-2">
                            Plaga/Enfermedad Objetivo
                        </label>
                        <input 
                            wire:model="target_pest" 
                            type="text" 
                            id="target_pest"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-transparent transition-all"
                            placeholder="Ej: Mildiu, Oídio, etc."
                        >
                        @error('target_pest') 
                            <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> 
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                    <!-- Dosis por Hectárea -->
                    <div>
                        <label for="dose_per_hectare" class="block text-sm font-semibold text-gray-700 mb-2">
                            Dosis por Hectárea (L/ha o kg/ha)
                        </label>
                        <input 
                            wire:model.live="dose_per_hectare" 
                            type="number" 
                            step="0.001"
                            id="dose_per_hectare"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-transparent transition-all"
                            placeholder="0.000"
                        >
                        @error('dose_per_hectare') 
                            <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> 
                        @enderror
                    </div>

                    <!-- Área Tratada -->
                    <div>
                        <label for="area_treated" class="block text-sm font-semibold text-gray-700 mb-2">
                            Área Tratada (ha)
                        </label>
                        <input 
                            wire:model.live="area_treated" 
                            type="number" 
                            step="0.001"
                            id="area_treated"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-transparent transition-all"
                            placeholder="0.000"
                        >
                        @error('area_treated') 
                            <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> 
                        @enderror
                    </div>

                    <!-- Dosis Total (calculada) -->
                    <div>
                        <label for="total_dose" class="block text-sm font-semibold text-gray-700 mb-2">
                            Dosis Total (calculada)
                        </label>
                        <input 
                            wire:model="total_dose" 
                            type="number" 
                            step="0.001"
                            id="total_dose"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl bg-gray-50"
                            placeholder="0.000"
                            readonly
                        >
                        <p class="mt-1 text-xs text-gray-500">Se calcula automáticamente</p>
                    </div>
                </div>

                <!-- Método de Aplicación -->
                <div class="mt-6">
                    <label for="application_method" class="block text-sm font-semibold text-gray-700 mb-2">
                        Método de Aplicación
                    </label>
                    <select 
                        wire:model="application_method" 
                        id="application_method"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-transparent transition-all"
                    >
                        <option value="">Selecciona un método</option>
                        <option value="pulverización">Pulverización</option>
                        <option value="aplicación foliar">Aplicación Foliar</option>
                        <option value="aplicación al suelo">Aplicación al Suelo</option>
                        <option value="inyección">Inyección</option>
                        <option value="otro">Otro</option>
                    </select>
                    @error('application_method') 
                        <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> 
                    @enderror
                </div>
            </div>

            <!-- Condiciones Meteorológicas -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-bold text-[var(--color-agro-green-dark)] mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/>
                    </svg>
                    Condiciones Meteorológicas
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Temperatura -->
                    <div>
                        <label for="temperature" class="block text-sm font-semibold text-gray-700 mb-2">
                            Temperatura (°C)
                        </label>
                        <input 
                            wire:model="temperature" 
                            type="number" 
                            step="0.1"
                            id="temperature"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-transparent transition-all"
                            placeholder="20.0"
                        >
                        @error('temperature') 
                            <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> 
                        @enderror
                    </div>

                    <!-- Velocidad del Viento -->
                    <div>
                        <label for="wind_speed" class="block text-sm font-semibold text-gray-700 mb-2">
                            Velocidad del Viento (km/h)
                        </label>
                        <input 
                            wire:model="wind_speed" 
                            type="number" 
                            step="0.1"
                            id="wind_speed"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-transparent transition-all"
                            placeholder="0.0"
                        >
                        @error('wind_speed') 
                            <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> 
                        @enderror
                    </div>

                    <!-- Humedad -->
                    <div>
                        <label for="humidity" class="block text-sm font-semibold text-gray-700 mb-2">
                            Humedad Relativa (%)
                        </label>
                        <input 
                            wire:model="humidity" 
                            type="number" 
                            step="0.1"
                            min="0"
                            max="100"
                            id="humidity"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-transparent transition-all"
                            placeholder="0.0"
                        >
                        @error('humidity') 
                            <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> 
                        @enderror
                    </div>
                </div>

                <!-- Condiciones Generales -->
                <div class="mt-6">
                    <label for="weather_conditions" class="block text-sm font-semibold text-gray-700 mb-2">
                        Condiciones Meteorológicas Generales
                    </label>
                    <input 
                        wire:model="weather_conditions" 
                        type="text" 
                        id="weather_conditions"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-transparent transition-all"
                        placeholder="Ej: Soleado, nublado, etc."
                    >
                    @error('weather_conditions') 
                        <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> 
                    @enderror
                </div>
            </div>

            <!-- Información Adicional -->
            <div class="pb-6">
                <h3 class="text-lg font-bold text-[var(--color-agro-green-dark)] mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    Información Adicional
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Cuadrilla -->
                    <div>
                        <label for="crew_id" class="block text-sm font-semibold text-gray-700 mb-2">
                            Cuadrilla
                        </label>
                        <select 
                            wire:model="crew_id" 
                            id="crew_id"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-transparent transition-all"
                        >
                            <option value="">Sin cuadrilla asignada</option>
                            @foreach($crews as $crew)
                                <option value="{{ $crew->id }}">{{ $crew->name }}</option>
                            @endforeach
                        </select>
                        @error('crew_id') 
                            <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> 
                        @enderror
                    </div>

                    <!-- Maquinaria -->
                    <div>
                        <label for="machinery_id" class="block text-sm font-semibold text-gray-700 mb-2">
                            Maquinaria
                        </label>
                        <select 
                            wire:model="machinery_id" 
                            id="machinery_id"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-transparent transition-all"
                        >
                            <option value="">Sin maquinaria asignada</option>
                            @foreach($machinery as $item)
                                <option value="{{ $item->id }}">{{ $item->name }} ({{ $item->type }})</option>
                            @endforeach
                        </select>
                        @error('machinery_id') 
                            <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> 
                        @enderror
                    </div>
                </div>

                <!-- Notas -->
                <div class="mt-6">
                    <label for="notes" class="block text-sm font-semibold text-gray-700 mb-2">
                        Notas Adicionales
                    </label>
                    <textarea 
                        wire:model="notes" 
                        id="notes"
                        rows="4"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-transparent transition-all"
                        placeholder="Observaciones, comentarios, etc."
                    ></textarea>
                    @error('notes') 
                        <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> 
                    @enderror
                </div>
            </div>

            <!-- Botones -->
            <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-200">
                <a 
                    href="{{ route('viticulturist.digital-notebook') }}" 
                    class="px-6 py-3 rounded-xl border-2 border-gray-300 text-gray-700 hover:bg-gray-50 transition-all font-semibold"
                >
                    Cancelar
                </a>
                <button 
                    type="submit"
                    class="px-6 py-3 rounded-xl bg-gradient-to-r from-red-600 to-red-700 text-white hover:from-red-700 hover:to-red-800 transition-all shadow-lg hover:shadow-xl transform hover:scale-105 font-semibold"
                >
                    Registrar Tratamiento
                </button>
            </div>
        </form>
    </div>
</div>

