<div class="space-y-6 animate-fade-in">
    <!-- Mensajes Flash -->
    @if(session('message'))
        <div class="glass-card rounded-xl p-4 bg-green-50 border-l-4 border-green-600">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm font-semibold text-green-800">{{ session('message') }}</p>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="glass-card rounded-xl p-4 bg-red-50 border-l-4 border-red-600">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm font-semibold text-red-800">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    <!-- Header -->
    @php
        $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>';
    @endphp
    <x-page-header
        :icon="$icon"
        title="Nueva Campaña"
        description="Crea una nueva campaña vitícola"
        icon-color="from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]"
    >
        <x-slot:actionButton>
            <x-button href="{{ route('viticulturist.campaign.index') }}" variant="secondary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Volver
            </x-button>
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
                    <!-- Nombre -->
                    <div>
                        <x-label for="name" required>Nombre de la Campaña</x-label>
                        <x-input 
                            wire:model="name" 
                            type="text" 
                            id="name"
                            placeholder="Ej: Campaña 2025"
                            :error="$errors->first('name')"
                            required
                        />
                    </div>

                    <!-- Año -->
                    <div>
                        <x-label for="year" required>Año</x-label>
                        <x-input 
                            wire:model="year" 
                            type="number" 
                            min="2000"
                            max="{{ now()->year + 5 }}"
                            id="year"
                            :error="$errors->first('year')"
                            required
                        />
                    </div>
                </div>

                <!-- Descripción -->
                <div class="mt-6">
                    <x-label for="description">Descripción</x-label>
                    <x-textarea 
                        wire:model="description" 
                        id="description"
                        rows="3"
                        placeholder="Descripción de la campaña..."
                        :error="$errors->first('description')"
                    />
                </div>
            </div>

            <!-- Período -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-bold text-[var(--color-agro-green-dark)] mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Período de la Campaña
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Fecha Inicio -->
                    <div>
                        <x-label for="start_date">Fecha de Inicio</x-label>
                        <x-input 
                            wire:model="start_date" 
                            type="date" 
                            id="start_date"
                            :error="$errors->first('start_date')"
                        />
                    </div>

                    <!-- Fecha Fin -->
                    <div>
                        <x-label for="end_date">Fecha de Fin</x-label>
                        <x-input 
                            wire:model="end_date" 
                            type="date" 
                            id="end_date"
                            :error="$errors->first('end_date')"
                        />
                    </div>
                </div>
            </div>

            <!-- Opciones -->
            <div class="pb-6">
                <h3 class="text-lg font-bold text-[var(--color-agro-green-dark)] mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Opciones
                </h3>
                
                <div class="flex items-center">
                    <input 
                        wire:model="active" 
                        type="checkbox"
                        id="active"
                        class="w-4 h-4 text-[var(--color-agro-green-dark)] border-gray-300 rounded focus:ring-[var(--color-agro-green-dark)]"
                    >
                    <label for="active" class="ml-3 text-sm font-semibold text-gray-700">
                        Activar esta campaña automáticamente
                    </label>
                </div>
                <p class="mt-2 text-xs text-gray-500">
                    Si se activa, se desactivarán automáticamente las demás campañas.
                </p>
                @error('active') 
                    <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> 
                @enderror
            </div>

            <!-- Botones -->
            <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-200">
                <x-button href="{{ route('viticulturist.campaign.index') }}" variant="secondary">
                    Cancelar
                </x-button>
                <x-button type="submit" variant="primary">
                    Crear Campaña
                </x-button>
            </div>
        </form>
    </div>
</div>
