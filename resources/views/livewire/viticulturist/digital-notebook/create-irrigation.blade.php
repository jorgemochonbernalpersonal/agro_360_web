<div class="space-y-6 animate-fade-in">
    @php
        $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>';
    @endphp
    <x-page-header
        :icon="$icon"
        title="Registrar Riego"
        description="Registra un nuevo riego en el cuaderno digital"
        icon-color="from-cyan-600 to-cyan-700"
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

    <div class="glass-card rounded-2xl p-8">
        <form wire:submit="save" class="space-y-8">
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-bold text-[var(--color-agro-green-dark)] mb-4">Información Básica</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="plot_id" class="block text-sm font-semibold text-gray-700 mb-2">Parcela *</label>
                        <select wire:model="plot_id" id="plot_id" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-transparent transition-all" required>
                            <option value="">Selecciona una parcela</option>
                            @foreach($plots as $plot)
                                <option value="{{ $plot->id }}">{{ $plot->name }}</option>
                            @endforeach
                        </select>
                        @error('plot_id') <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="activity_date" class="block text-sm font-semibold text-gray-700 mb-2">Fecha *</label>
                        <input wire:model="activity_date" type="date" id="activity_date" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-transparent transition-all" required>
                        @error('activity_date') <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-bold text-[var(--color-agro-green-dark)] mb-4">Información del Riego</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="water_volume" class="block text-sm font-semibold text-gray-700 mb-2">Volumen de Agua (L)</label>
                        <input wire:model="water_volume" type="number" step="0.001" id="water_volume" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-transparent transition-all" placeholder="0.000">
                        @error('water_volume') <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="irrigation_method" class="block text-sm font-semibold text-gray-700 mb-2">Método de Riego</label>
                        <select wire:model="irrigation_method" id="irrigation_method" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-transparent transition-all">
                            <option value="">Selecciona un método</option>
                            <option value="goteo">Goteo</option>
                            <option value="aspersión">Aspersión</option>
                            <option value="superficie">Superficie</option>
                            <option value="subterráneo">Subterráneo</option>
                            <option value="otro">Otro</option>
                        </select>
                        @error('irrigation_method') <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                    <div>
                        <label for="duration_minutes" class="block text-sm font-semibold text-gray-700 mb-2">Duración (minutos)</label>
                        <input wire:model="duration_minutes" type="number" id="duration_minutes" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-transparent transition-all" placeholder="0">
                        @error('duration_minutes') <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="soil_moisture_before" class="block text-sm font-semibold text-gray-700 mb-2">Humedad del Suelo Antes (%)</label>
                        <input wire:model="soil_moisture_before" type="number" step="0.1" min="0" max="100" id="soil_moisture_before" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-transparent transition-all" placeholder="0.0">
                        @error('soil_moisture_before') <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="soil_moisture_after" class="block text-sm font-semibold text-gray-700 mb-2">Humedad del Suelo Después (%)</label>
                        <input wire:model="soil_moisture_after" type="number" step="0.1" min="0" max="100" id="soil_moisture_after" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-transparent transition-all" placeholder="0.0">
                        @error('soil_moisture_after') <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="pb-6">
                <h3 class="text-lg font-bold text-[var(--color-agro-green-dark)] mb-4">Información Adicional</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="crew_id" class="block text-sm font-semibold text-gray-700 mb-2">Cuadrilla</label>
                        <select wire:model="crew_id" id="crew_id" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-transparent transition-all">
                            <option value="">Sin cuadrilla asignada</option>
                            @foreach($crews as $crew)
                                <option value="{{ $crew->id }}">{{ $crew->name }}</option>
                            @endforeach
                        </select>
                        @error('crew_id') <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="machinery_id" class="block text-sm font-semibold text-gray-700 mb-2">Maquinaria</label>
                        <select wire:model="machinery_id" id="machinery_id" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-transparent transition-all">
                            <option value="">Sin maquinaria asignada</option>
                            @foreach($machinery as $item)
                                <option value="{{ $item->id }}">{{ $item->name }} ({{ $item->type }})</option>
                            @endforeach
                        </select>
                        @error('machinery_id') <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="weather_conditions" class="block text-sm font-semibold text-gray-700 mb-2">Condiciones Meteorológicas</label>
                        <input wire:model="weather_conditions" type="text" id="weather_conditions" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-transparent transition-all" placeholder="Ej: Soleado, nublado">
                        @error('weather_conditions') <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="mt-6">
                    <label for="temperature" class="block text-sm font-semibold text-gray-700 mb-2">Temperatura (°C)</label>
                    <input wire:model="temperature" type="number" step="0.1" id="temperature" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-transparent transition-all" placeholder="20.0">
                    @error('temperature') <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> @enderror
                </div>
                <div class="mt-6">
                    <label for="notes" class="block text-sm font-semibold text-gray-700 mb-2">Notas</label>
                    <textarea wire:model="notes" id="notes" rows="4" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-transparent transition-all" placeholder="Observaciones, comentarios, etc."></textarea>
                    @error('notes') <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-200">
                <a href="{{ route('viticulturist.digital-notebook') }}" class="px-6 py-3 rounded-xl border-2 border-gray-300 text-gray-700 hover:bg-gray-50 transition-all font-semibold">Cancelar</a>
                <button type="submit" class="px-6 py-3 rounded-xl bg-gradient-to-r from-cyan-600 to-cyan-700 text-white hover:from-cyan-700 hover:to-cyan-800 transition-all shadow-lg hover:shadow-xl transform hover:scale-105 font-semibold">Registrar Riego</button>
            </div>
        </form>
    </div>
</div>

