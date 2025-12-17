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
        $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>';
    @endphp
    <x-page-header
        :icon="$icon"
        title="Crear Viticultor"
        description="Crea un nuevo viticultor para gestionar en tus cuadrillas"
        icon-color="from-[var(--color-agro-blue)] to-blue-700"
    >
        <x-slot:actionButton>
            <x-button href="{{ route('viticulturist.personal.index') }}" variant="secondary">
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
            <!-- Información del Viticultor -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-bold text-[var(--color-agro-blue)] mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Información del Viticultor
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nombre -->
                    <div>
                        <x-label for="name" required>Nombre Completo</x-label>
                        <x-input 
                            wire:model="name" 
                            type="text" 
                            id="name"
                            placeholder="Ej: Juan Pérez"
                            :error="$errors->first('name')"
                            required
                        />
                    </div>

                    <!-- Email -->
                    <div>
                        <x-label for="email" required>Email</x-label>
                        <x-input 
                            wire:model="email" 
                            type="email" 
                            id="email"
                            placeholder="correo@ejemplo.com"
                            :error="$errors->first('email')"
                            required
                        />
                    </div>
                </div>

                <!-- Bodega (opcional) -->
                @if($wineries->isNotEmpty())
                <div class="mt-6">
                    <x-label for="winery_id">Bodega <span class="text-gray-500 font-normal">(opcional)</span></x-label>
                    <x-select 
                        wire:model="winery_id" 
                        id="winery_id"
                        :error="$errors->first('winery_id')"
                    >
                        <option value="">Sin bodega</option>
                        @foreach($wineries as $winery)
                            <option value="{{ $winery->id }}">{{ $winery->name }}</option>
                        @endforeach
                    </x-select>
                </div>
                @endif
            </div>

            <!-- Información -->
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded-lg">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <p class="text-sm font-semibold text-blue-800 mb-1">Nota importante:</p>
                        <p class="text-sm text-blue-800">
                            Se generará una contraseña aleatoria y un PDF con las credenciales. 
                            El viticultor podrá hacer login inmediatamente sin verificación de email.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Botones -->
            <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-200">
                <x-button href="{{ route('viticulturist.personal.index') }}" variant="secondary">Cancelar</x-button>
                <x-button type="submit" variant="primary">Crear Viticultor</x-button>
            </div>
        </form>
    </div>
</div>

