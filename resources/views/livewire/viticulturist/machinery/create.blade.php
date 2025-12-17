@php
    $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>';
@endphp

<x-form-card
    title="Nueva Maquinaria"
    description="Registra una nueva maquinaria o equipo agrícola"
    :icon="$icon"
    icon-color="from-[var(--color-agro-brown)] to-[var(--color-agro-brown-light)]"
    :back-url="route('viticulturist.machinery.index')"
>
    <form wire:submit="save" class="space-y-8" enctype="multipart/form-data">
        <x-form-section title="Información Básica" color="brown">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nombre -->
                    <div>
                        <x-label for="name" required>Nombre</x-label>
                        <x-input 
                            wire:model="name" 
                            type="text" 
                            id="name"
                            placeholder="Ej: Tractor John Deere"
                            :error="$errors->first('name')"
                            required
                        />
                    </div>

                    <!-- Tipo -->
                    <div>
                        <x-label for="type" required>Tipo</x-label>
                        <x-input 
                            wire:model="type" 
                            type="text" 
                            id="type"
                            placeholder="Ej: Tractor, Pulverizador, Vendimiadora"
                            :error="$errors->first('type')"
                            required
                        />
                    </div>

                    <!-- Marca -->
                    <div>
                        <x-label for="brand">Marca</x-label>
                        <x-input 
                            wire:model="brand" 
                            type="text" 
                            id="brand"
                            placeholder="Ej: John Deere"
                            :error="$errors->first('brand')"
                        />
                    </div>

                    <!-- Modelo -->
                    <div>
                        <x-label for="model">Modelo</x-label>
                        <x-input 
                            wire:model="model" 
                            type="text" 
                            id="model"
                            placeholder="Ej: 6120M"
                            :error="$errors->first('model')"
                        />
                    </div>

                    <!-- Número de Serie -->
                    <div>
                        <x-label for="serial_number">Número de Serie</x-label>
                        <x-input 
                            wire:model="serial_number" 
                            type="text" 
                            id="serial_number"
                            placeholder="Ej: JD123456"
                            :error="$errors->first('serial_number')"
                        />
                    </div>

                    <!-- Año -->
                    <div>
                        <x-label for="year">Año</x-label>
                        <x-input 
                            wire:model="year" 
                            type="number" 
                            min="1900"
                            max="{{ now()->year + 1 }}"
                            id="year"
                            :error="$errors->first('year')"
                        />
                    </div>
                </div>
        </x-form-section>

        <x-form-section title="Registro y Propiedad" color="brown">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Inscripción ROMA -->
                    <div>
                        <x-label for="roma_registration">Inscripción ROMA</x-label>
                        <x-input 
                            wire:model="roma_registration" 
                            type="text" 
                            id="roma_registration"
                            placeholder="Ej: ROMA-12345"
                            :error="$errors->first('roma_registration')"
                        />
                    </div>

                    <!-- Capacidad -->
                    <div>
                        <x-label for="capacity">Capacidad</x-label>
                        <x-input 
                            wire:model="capacity" 
                            type="text" 
                            id="capacity"
                            placeholder="Ej: 1000L, 5m³"
                            :error="$errors->first('capacity')"
                        />
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
        </x-form-section>

        <x-form-section title="Fechas y Valores" color="brown">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Fecha de Compra -->
                    <div>
                        <x-label for="purchase_date">Fecha de Compra</x-label>
                        <x-input 
                            wire:model="purchase_date" 
                            type="date" 
                            id="purchase_date"
                            :error="$errors->first('purchase_date')"
                        />
                    </div>

                    <!-- Fecha de Última Revisión -->
                    <div>
                        <x-label for="last_revision_date">Fecha de Última Revisión</x-label>
                        <x-input 
                            wire:model="last_revision_date" 
                            type="date" 
                            id="last_revision_date"
                            :error="$errors->first('last_revision_date')"
                        />
                    </div>

                    <!-- Precio de Compra -->
                    <div>
                        <x-label for="purchase_price">Precio de Compra (€)</x-label>
                        <x-input 
                            wire:model="purchase_price" 
                            type="number" 
                            step="0.01"
                            min="0"
                            id="purchase_price"
                            placeholder="0.00"
                            :error="$errors->first('purchase_price')"
                        />
                    </div>

                    <!-- Valor Actual -->
                    <div>
                        <x-label for="current_value">Valor Actual (€)</x-label>
                        <x-input 
                            wire:model="current_value" 
                            type="number" 
                            step="0.01"
                            min="0"
                            id="current_value"
                            placeholder="0.00"
                            :error="$errors->first('current_value')"
                        />
                    </div>
                </div>
        </x-form-section>

        <x-form-section title="Imagen y Notas" color="brown">
                
                <!-- Imagen -->
                <div class="mb-6">
                    <label for="image" class="block text-sm font-semibold text-gray-700 mb-2">
                        Imagen
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
                            <img src="{{ $image->temporaryUrl() }}" alt="Vista previa" class="max-w-xs rounded-lg">
                        </div>
                    @endif
                </div>

                <!-- Notas -->
                <div>
                    <x-label for="notes">Notas</x-label>
                    <x-textarea 
                        wire:model="notes" 
                        id="notes"
                        rows="4"
                        placeholder="Notas adicionales sobre la maquinaria..."
                        :error="$errors->first('notes')"
                    />
                </div>
        </x-form-section>

        <x-form-section title="Opciones" color="brown" class="pb-6">
                
                <div class="flex items-center">
                    <input 
                        wire:model="active" 
                        type="checkbox"
                        id="active"
                        class="w-4 h-4 text-[var(--color-agro-brown-dark)] border-gray-300 rounded focus:ring-[var(--color-agro-brown-dark)]"
                        checked
                    >
                    <label for="active" class="ml-3 text-sm font-semibold text-gray-700">
                        Maquinaria activa
                    </label>
                </div>
                @error('active') 
                    <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> 
                @enderror
        </x-form-section>

        <x-form-actions 
            :cancel-url="route('viticulturist.machinery.index')"
            submit-label="Crear Maquinaria"
        />
    </form>
</x-form-card>
