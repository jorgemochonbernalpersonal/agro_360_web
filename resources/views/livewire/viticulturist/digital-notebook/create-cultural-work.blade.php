<div class="space-y-6 animate-fade-in">
    @php
        $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>';
    @endphp
    <x-page-header
        :icon="$icon"
        title="Registrar Labor Cultural"
        description="Registra una nueva labor cultural en el cuaderno digital"
        icon-color="from-purple-600 to-purple-700"
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
                <h3 class="text-lg font-bold text-[var(--color-agro-green-dark)] mb-4">Información de la Labor</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="work_type" class="block text-sm font-semibold text-gray-700 mb-2">Tipo de Labor</label>
                        <select wire:model="work_type" id="work_type" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-transparent transition-all">
                            <option value="">Selecciona un tipo</option>
                            <option value="poda">Poda</option>
                            <option value="deshojado">Deshojado</option>
                            <option value="despuntado">Despuntado</option>
                            <option value="vendimia">Vendimia</option>
                            <option value="laboreo">Laboreo</option>
                            <option value="desbroce">Desbroce</option>
                            <option value="otro">Otro</option>
                        </select>
                        @error('work_type') <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="workers_count" class="block text-sm font-semibold text-gray-700 mb-2">Número de Trabajadores</label>
                        <input wire:model="workers_count" type="number" min="1" id="workers_count" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-transparent transition-all" placeholder="0">
                        @error('workers_count') <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="mt-6">
                    <label for="hours_worked" class="block text-sm font-semibold text-gray-700 mb-2">Horas Trabajadas</label>
                    <input wire:model="hours_worked" type="number" step="0.5" min="0" id="hours_worked" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-transparent transition-all" placeholder="0.0">
                    @error('hours_worked') <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> @enderror
                </div>
                <div class="mt-6">
                    <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">Descripción</label>
                    <textarea wire:model="description" id="description" rows="4" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-transparent transition-all" placeholder="Descripción detallada de la labor realizada..."></textarea>
                    @error('description') <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> @enderror
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
                <button type="submit" class="px-6 py-3 rounded-xl bg-gradient-to-r from-purple-600 to-purple-700 text-white hover:from-purple-700 hover:to-purple-800 transition-all shadow-lg hover:shadow-xl transform hover:scale-105 font-semibold">Registrar Labor</button>
            </div>
        </form>
    </div>
</div>

