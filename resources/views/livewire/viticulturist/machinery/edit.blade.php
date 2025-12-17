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
        $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>';
    @endphp
    <x-page-header
        :icon="$icon"
        title="Editar Maquinaria"
        description="Modifica los datos de {{ $machinery->name }}"
        icon-color="from-[var(--color-agro-brown)] to-[var(--color-agro-brown-light)]"
    >
        <x-slot:actionButton>
            <a href="{{ route('viticulturist.machinery.index') }}" class="group">
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
        <form wire:submit="save" class="space-y-8" enctype="multipart/form-data">
            <!-- Información Básica -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-bold text-[var(--color-agro-brown-dark)] mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Información Básica
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nombre -->
                    <div>
                        <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                            Nombre *
                        </label>
                        <input 
                            wire:model="name" 
                            type="text" 
                            id="name"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--color-agro-brown-dark)] focus:border-transparent transition-all"
                            required
                        >
                        @error('name') 
                            <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> 
                        @enderror
                    </div>

                    <!-- Tipo -->
                    <div>
                        <label for="type" class="block text-sm font-semibold text-gray-700 mb-2">
                            Tipo *
                        </label>
                        <input 
                            wire:model="type" 
                            type="text" 
                            id="type"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--color-agro-brown-dark)] focus:border-transparent transition-all"
                            required
                        >
                        @error('type') 
                            <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> 
                        @enderror
                    </div>

                    <!-- Marca -->
                    <div>
                        <label for="brand" class="block text-sm font-semibold text-gray-700 mb-2">
                            Marca
                        </label>
                        <input 
                            wire:model="brand" 
                            type="text" 
                            id="brand"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--color-agro-brown-dark)] focus:border-transparent transition-all"
                        >
                        @error('brand') 
                            <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> 
                        @enderror
                    </div>

                    <!-- Modelo -->
                    <div>
                        <label for="model" class="block text-sm font-semibold text-gray-700 mb-2">
                            Modelo
                        </label>
                        <input 
                            wire:model="model" 
                            type="text" 
                            id="model"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--color-agro-brown-dark)] focus:border-transparent transition-all"
                        >
                        @error('model') 
                            <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> 
                        @enderror
                    </div>

                    <!-- Número de Serie -->
                    <div>
                        <label for="serial_number" class="block text-sm font-semibold text-gray-700 mb-2">
                            Número de Serie
                        </label>
                        <input 
                            wire:model="serial_number" 
                            type="text" 
                            id="serial_number"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--color-agro-brown-dark)] focus:border-transparent transition-all"
                        >
                        @error('serial_number') 
                            <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> 
                        @enderror
                    </div>

                    <!-- Año -->
                    <div>
                        <label for="year" class="block text-sm font-semibold text-gray-700 mb-2">
                            Año
                        </label>
                        <input 
                            wire:model="year" 
                            type="number" 
                            min="1900"
                            max="{{ now()->year + 1 }}"
                            id="year"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--color-agro-brown-dark)] focus:border-transparent transition-all"
                        >
                        @error('year') 
                            <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> 
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Inscripción ROMA y Alquiler -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-bold text-[var(--color-agro-brown-dark)] mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Registro y Propiedad
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Inscripción ROMA -->
                    <div>
                        <label for="roma_registration" class="block text-sm font-semibold text-gray-700 mb-2">
                            Inscripción ROMA
                        </label>
                        <input 
                            wire:model="roma_registration" 
                            type="text" 
                            id="roma_registration"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--color-agro-brown-dark)] focus:border-transparent transition-all"
                        >
                        @error('roma_registration') 
                            <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> 
                        @enderror
                    </div>

                    <!-- Capacidad -->
                    <div>
                        <label for="capacity" class="block text-sm font-semibold text-gray-700 mb-2">
                            Capacidad
                        </label>
                        <input 
                            wire:model="capacity" 
                            type="text" 
                            id="capacity"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--color-agro-brown-dark)] focus:border-transparent transition-all"
                        >
                        @error('capacity') 
                            <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> 
                        @enderror
                    </div>
                </div>

                <!-- Es Alquilada -->
                <div class="mt-6 flex items-center">
                    <input 
                        wire:model="is_rented" 
                        type="checkbox"
                        id="is_rented"
                        class="w-4 h-4 text-[var(--color-agro-brown-dark)] border-gray-300 rounded focus:ring-[var(--color-agro-brown-dark)]"
                    >
                    <label for="is_rented" class="ml-3 text-sm font-semibold text-gray-700">
                        Maquinaria alquilada
                    </label>
                </div>
                @error('is_rented') 
                    <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> 
                @enderror
            </div>

            <!-- Fechas y Precios -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-bold text-[var(--color-agro-brown-dark)] mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Fechas y Valores
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Fecha de Compra -->
                    <div>
                        <label for="purchase_date" class="block text-sm font-semibold text-gray-700 mb-2">
                            Fecha de Compra
                        </label>
                        <input 
                            wire:model="purchase_date" 
                            type="date" 
                            id="purchase_date"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--color-agro-brown-dark)] focus:border-transparent transition-all"
                        >
                        @error('purchase_date') 
                            <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> 
                        @enderror
                    </div>

                    <!-- Fecha de Última Revisión -->
                    <div>
                        <label for="last_revision_date" class="block text-sm font-semibold text-gray-700 mb-2">
                            Fecha de Última Revisión
                        </label>
                        <input 
                            wire:model="last_revision_date" 
                            type="date" 
                            id="last_revision_date"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--color-agro-brown-dark)] focus:border-transparent transition-all"
                        >
                        @error('last_revision_date') 
                            <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> 
                        @enderror
                    </div>

                    <!-- Precio de Compra -->
                    <div>
                        <label for="purchase_price" class="block text-sm font-semibold text-gray-700 mb-2">
                            Precio de Compra (€)
                        </label>
                        <input 
                            wire:model="purchase_price" 
                            type="number" 
                            step="0.01"
                            min="0"
                            id="purchase_price"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--color-agro-brown-dark)] focus:border-transparent transition-all"
                        >
                        @error('purchase_price') 
                            <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> 
                        @enderror
                    </div>

                    <!-- Valor Actual -->
                    <div>
                        <label for="current_value" class="block text-sm font-semibold text-gray-700 mb-2">
                            Valor Actual (€)
                        </label>
                        <input 
                            wire:model="current_value" 
                            type="number" 
                            step="0.01"
                            min="0"
                            id="current_value"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--color-agro-brown-dark)] focus:border-transparent transition-all"
                        >
                        @error('current_value') 
                            <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> 
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Imagen y Notas -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-bold text-[var(--color-agro-brown-dark)] mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Imagen y Notas
                </h3>
                
                <!-- Imagen Actual -->
                @if($current_image)
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Imagen Actual
                        </label>
                        <img src="{{ \Storage::url($current_image) }}" alt="{{ $machinery->name }}" class="max-w-xs rounded-lg border-2 border-gray-200">
                    </div>
                @endif

                <!-- Nueva Imagen -->
                <div class="mb-6">
                    <label for="image" class="block text-sm font-semibold text-gray-700 mb-2">
                        {{ $current_image ? 'Cambiar Imagen' : 'Imagen' }}
                    </label>
                    <input 
                        wire:model="image" 
                        type="file" 
                        accept="image/*"
                        id="image"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--color-agro-brown-dark)] focus:border-transparent transition-all"
                    >
                    @error('image') 
                        <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> 
                    @enderror
                    @if($image)
                        <div class="mt-4">
                            <p class="text-sm text-gray-600 mb-2">Vista previa de la nueva imagen:</p>
                            <img src="{{ $image->temporaryUrl() }}" alt="Vista previa" class="max-w-xs rounded-lg">
                        </div>
                    @endif
                </div>

                <!-- Notas -->
                <div>
                    <label for="notes" class="block text-sm font-semibold text-gray-700 mb-2">
                        Notas
                    </label>
                    <textarea 
                        wire:model="notes" 
                        id="notes"
                        rows="4"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--color-agro-brown-dark)] focus:border-transparent transition-all"
                        placeholder="Notas adicionales sobre la maquinaria..."
                    ></textarea>
                    @error('notes') 
                        <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> 
                    @enderror
                </div>
            </div>

            <!-- Opciones -->
            <div class="pb-6">
                <h3 class="text-lg font-bold text-[var(--color-agro-brown-dark)] mb-4 flex items-center gap-2">
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
                        class="w-4 h-4 text-[var(--color-agro-brown-dark)] border-gray-300 rounded focus:ring-[var(--color-agro-brown-dark)]"
                    >
                    <label for="active" class="ml-3 text-sm font-semibold text-gray-700">
                        Maquinaria activa
                    </label>
                </div>
                @error('active') 
                    <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> 
                @enderror
            </div>

            <!-- Botones -->
            <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-200">
                <a 
                    href="{{ route('viticulturist.machinery.index') }}" 
                    class="px-6 py-3 rounded-xl border-2 border-gray-300 text-gray-700 hover:bg-gray-50 transition-all font-semibold"
                >
                    Cancelar
                </a>
                <button 
                    type="submit"
                    class="px-6 py-3 rounded-xl bg-gradient-to-r from-[var(--color-agro-brown-dark)] to-[var(--color-agro-brown)] text-white hover:from-[var(--color-agro-brown)] hover:to-[var(--color-agro-brown-dark)] transition-all shadow-lg hover:shadow-xl transform hover:scale-105 font-semibold"
                >
                    Actualizar Maquinaria
                </button>
            </div>
        </form>
    </div>
</div>
