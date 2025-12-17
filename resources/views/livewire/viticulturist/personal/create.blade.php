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
        $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>';
    @endphp
    <x-page-header
        :icon="$icon"
        title="Nueva Cuadrilla"
        description="Crea un nuevo equipo de trabajo"
        icon-color="from-[var(--color-agro-blue)] to-blue-700"
    >
        <x-slot:actionButton>
            <a href="{{ route('viticulturist.personal.index') }}" class="group">
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
                <h3 class="text-lg font-bold text-[var(--color-agro-blue)] mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Información Básica
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nombre -->
                    <div>
                        <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                            Nombre de la Cuadrilla *
                        </label>
                        <input 
                            wire:model="name" 
                            type="text" 
                            id="name"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-[var(--color-agro-blue)] focus:border-[var(--color-agro-blue)] transition"
                            placeholder="Ej: Cuadrilla Norte"
                            required
                        >
                        @error('name') 
                            <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> 
                        @enderror
                    </div>

                    <!-- Bodega -->
                    @if($wineries->isNotEmpty())
                        <div>
                            <label for="winery_id" class="block text-sm font-semibold text-gray-700 mb-2">
                                Bodega <span class="text-gray-500 font-normal">(opcional)</span>
                            </label>
                            <select 
                                wire:model="winery_id" 
                                id="winery_id"
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-[var(--color-agro-blue)] focus:border-[var(--color-agro-blue)] transition"
                            >
                                <option value="">Sin bodega (cuadrilla independiente)</option>
                                @foreach($wineries as $winery)
                                    <option value="{{ $winery->id }}">{{ $winery->name }}</option>
                                @endforeach
                            </select>
                            @error('winery_id') 
                                <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> 
                            @enderror
                        </div>
                    @endif
                </div>

                <!-- Descripción -->
                <div class="mt-6">
                    <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">
                        Descripción
                    </label>
                    <textarea 
                        wire:model="description" 
                        id="description"
                        rows="4"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-[var(--color-agro-blue)] focus:border-[var(--color-agro-blue)] transition"
                        placeholder="Describe el propósito o función de esta cuadrilla..."
                    ></textarea>
                    @error('description') 
                        <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> 
                    @enderror
                </div>
            </div>

            <!-- Botones -->
            <div class="flex items-center justify-end gap-4 pt-6">
                <a href="{{ route('viticulturist.personal.index') }}">
                    <button type="button" class="px-6 py-3 rounded-xl bg-gray-200 text-gray-700 hover:bg-gray-300 transition-all duration-300 font-semibold">
                        Cancelar
                    </button>
                </a>
                <button type="submit" class="px-6 py-3 rounded-xl bg-gradient-to-r from-[var(--color-agro-blue)] to-blue-700 text-white hover:from-blue-700 hover:to-[var(--color-agro-blue)] transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105 font-semibold">
                    Crear Cuadrilla
                </button>
            </div>
        </form>
    </div>
</div>

