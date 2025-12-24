@php
    $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>';
@endphp

<x-form-card
    title="Crear Contenedor"
    description="Registra un nuevo contenedor para una cosecha"
    :icon="$icon"
    icon-color="from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]"
    :back-url="route('viticulturist.digital-notebook.containers.index')"
>
    <form wire:submit="save" class="space-y-8">
        
        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg mb-6">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <h4 class="text-sm font-semibold text-blue-900">Contenedor Independiente</h4>
                    <p class="text-xs text-blue-800 mt-1">
                        Este contenedor se creará sin asignar a ninguna cosecha. Podrás asignarlo cuando crees o edites una cosecha.
                    </p>
                </div>
            </div>
        </div>

        {{-- Información del Contenedor --}}
        <x-form-section title="Información del Contenedor" color="green">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Nombre del contenedor --}}
                <div>
                    <x-label for="name" required>Nombre del Contenedor</x-label>
                    <x-input 
                        wire:model="name" 
                        type="text" 
                        id="name"
                        placeholder="Ej: Contenedor Principal, Cuba 1, Depósito A"
                        :error="$errors->first('name')"
                        required
                    />
                </div>

                {{-- Número de serie --}}
                <div>
                    <x-label for="serial_number">Número de Serie/Identificador</x-label>
                    <x-input 
                        wire:model="serial_number" 
                        type="text" 
                        id="serial_number"
                        placeholder="Ej: CONT-001, SER-12345"
                        :error="$errors->first('serial_number')"
                    />
                </div>

                {{-- Descripción --}}
                <div class="md:col-span-2">
                    <x-label for="description">Descripción</x-label>
                    <x-textarea 
                        wire:model="description" 
                        id="description"
                        rows="3"
                        placeholder="Descripción adicional del contenedor..."
                        :error="$errors->first('description')"
                    />
                </div>

                {{-- Cantidad --}}
                <div>
                    <x-label for="quantity" required>Cantidad</x-label>
                    <x-input 
                        wire:model="quantity" 
                        type="number" 
                        min="1"
                        step="1"
                        id="quantity"
                        placeholder="1"
                        :error="$errors->first('quantity')"
                        required
                    />
                </div>

                {{-- Capacidad total --}}
                <div>
                    <x-label for="capacity" required>Capacidad Total (kg)</x-label>
                    <x-input 
                        wire:model="capacity" 
                        type="number" 
                        step="0.01"
                        min="0.01"
                        id="capacity"
                        placeholder="0.00"
                        :error="$errors->first('capacity')"
                        required
                    />
                    <p class="mt-1 text-xs text-gray-500">Capacidad máxima que puede almacenar el contenedor</p>
                </div>

                {{-- Archivado --}}
                <div class="md:col-span-2">
                    <label class="flex items-center gap-2">
                        <input 
                            type="checkbox" 
                            wire:model="archived"
                            class="rounded border-gray-300 text-[var(--color-agro-green)] focus:ring-[var(--color-agro-green)]"
                        />
                        <span class="text-sm text-gray-700">Contenedor archivado (no aparecerá en listados activos)</span>
                    </label>
                </div>
            </div>
        </x-form-section>

        {{-- Fechas --}}
        <x-form-section title="Fechas" color="green">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Fecha de compra --}}
                <div>
                    <x-label for="purchase_date">Fecha de Compra/Adquisición</x-label>
                    <x-input 
                        wire:model="purchase_date" 
                        type="date" 
                        id="purchase_date"
                        :error="$errors->first('purchase_date')"
                    />
                </div>

                {{-- Próximo mantenimiento --}}
                <div>
                    <x-label for="next_maintenance_date">Próximo Mantenimiento</x-label>
                    <x-input 
                        wire:model="next_maintenance_date" 
                        type="date" 
                        id="next_maintenance_date"
                        :error="$errors->first('next_maintenance_date')"
                    />
                </div>
            </div>
        </x-form-section>

        {{-- Botones de acción --}}
        <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-200">
            <a 
                href="{{ route('viticulturist.digital-notebook.containers.index') }}"
                class="px-6 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition font-semibold"
            >
                Cancelar
            </a>
            <button 
                type="submit"
                class="px-6 py-2 rounded-lg bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:from-[var(--color-agro-green)] hover:to-[var(--color-agro-green-dark)] transition font-semibold"
            >
                Guardar Contenedor
            </button>
        </div>
    </form>
</x-form-card>

