<div class="space-y-6 animate-fade-in">
    @php
        $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>';
    @endphp
    <x-page-header
        :icon="$icon"
        title="Registrar Fertilización"
        description="Registra una nueva fertilización en el cuaderno digital"
        icon-color="from-blue-600 to-blue-700"
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
                <h3 class="text-lg font-bold text-[var(--color-agro-green-dark)] mb-4">Información del Fertilizante</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="fertilizer_type" class="block text-sm font-semibold text-gray-700 mb-2">Tipo de Fertilizante</label>
                        <input wire:model="fertilizer_type" type="text" id="fertilizer_type" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-transparent transition-all" placeholder="Ej: Orgánico, Mineral, etc.">
                        @error('fertilizer_type') <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="fertilizer_name" class="block text-sm font-semibold text-gray-700 mb-2">Nombre del Fertilizante</label>
                        <input wire:model="fertilizer_name" type="text" id="fertilizer_name" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-transparent transition-all" placeholder="Nombre comercial">
                        @error('fertilizer_name') <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                    <div>
                        <label for="quantity" class="block text-sm font-semibold text-gray-700 mb-2">Cantidad (kg)</label>
                        <input wire:model="quantity" type="number" step="0.001" id="quantity" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-transparent transition-all" placeholder="0.000">
                        @error('quantity') <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="npk_ratio" class="block text-sm font-semibold text-gray-700 mb-2">Relación NPK</label>
                        <input wire:model="npk_ratio" type="text" id="npk_ratio" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-transparent transition-all" placeholder="Ej: 10-10-10">
                        @error('npk_ratio') <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="area_applied" class="block text-sm font-semibold text-gray-700 mb-2">Área Aplicada (ha)</label>
                        <input wire:model="area_applied" type="number" step="0.001" id="area_applied" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-transparent transition-all" placeholder="0.000">
                        @error('area_applied') <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="mt-6">
                    <label for="application_method" class="block text-sm font-semibold text-gray-700 mb-2">Método de Aplicación</label>
                    <select wire:model="application_method" id="application_method" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-transparent transition-all">
                        <option value="">Selecciona un método</option>
                        <option value="aplicación al suelo">Aplicación al Suelo</option>
                        <option value="fertirrigación">Fertirrigación</option>
                        <option value="aplicación foliar">Aplicación Foliar</option>
                        <option value="otro">Otro</option>
                    </select>
                    @error('application_method') <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> @enderror
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
                <button type="submit" class="px-6 py-3 rounded-xl bg-gradient-to-r from-blue-600 to-blue-700 text-white hover:from-blue-700 hover:to-blue-800 transition-all shadow-lg hover:shadow-xl transform hover:scale-105 font-semibold">Registrar Fertilización</button>
            </div>
        </form>
    </div>
</div>

