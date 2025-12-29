@php
    $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>';
@endphp

<x-form-card
    title="Crear Almacén"
    description="Registra un nuevo almacén o ubicación para organizar tus productos fitosanitarios"
    :icon="$icon"
    icon-color="from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]"
    :back-url="route('viticulturist.warehouses.index')"
>
    <form wire:submit="save" class="space-y-8">
        <x-form-section title="Información del Almacén" color="green">
            <div class="grid grid-cols-1 gap-6">
                <div>
                    <x-label for="name" required>Nombre del Almacén</x-label>
                    <x-input 
                        wire:model="name" 
                        type="text" 
                        id="name"
                        placeholder="Ej: Almacén Principal, Cobertizo Norte..."
                        :error="$errors->first('name')"
                        required
                    />
                    <p class="mt-1 text-sm text-gray-500">Nombre descriptivo para identificar el almacén</p>
                </div>
                <div>
                    <x-label for="location">Ubicación</x-label>
                    <x-input 
                        wire:model="location" 
                        type="text" 
                        id="location"
                        placeholder="Ej: Edificio A, Planta Baja, Sala 3..."
                        :error="$errors->first('location')"
                    />
                    <p class="mt-1 text-sm text-gray-500">Dirección o descripción de la ubicación física</p>
                </div>
                <div>
                    <x-label for="description">Descripción</x-label>
                    <x-textarea 
                        wire:model="description" 
                        id="description"
                        rows="3"
                        placeholder="Información adicional sobre este almacén..."
                        :error="$errors->first('description')"
                    />
                    <p class="mt-1 text-sm text-gray-500">Notas adicionales sobre el almacén</p>
                </div>
            </div>
        </x-form-section>

        <div class="flex justify-end gap-4">
            <a href="{{ route('viticulturist.warehouses.index') }}" class="px-6 py-3 rounded-xl border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors">
                Cancelar
            </a>
            <button type="submit" class="px-6 py-3 rounded-xl bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:from-[var(--color-agro-green)] hover:to-[var(--color-agro-green-dark)] transition-all duration-300 shadow-lg hover:shadow-xl font-semibold">
                Crear Almacén
            </button>
        </div>
    </form>
</x-form-card>
