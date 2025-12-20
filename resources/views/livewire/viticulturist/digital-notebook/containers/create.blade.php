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
                {{-- Tipo de contenedor --}}
                <div>
                    <x-label for="container_type" required>Tipo de Contenedor</x-label>
                    <x-select 
                        wire:model="container_type" 
                        id="container_type"
                        :error="$errors->first('container_type')"
                        required
                    >
                        <option value="caja">Caja</option>
                        <option value="pallet">Pallet</option>
                        <option value="contenedor">Contenedor</option>
                        <option value="saco">Saco</option>
                        <option value="cuba">Cuba</option>
                        <option value="other">Otro</option>
                    </x-select>
                </div>

                {{-- Número de contenedor --}}
                <div>
                    <x-label for="container_number">Número/Identificador</x-label>
                    <x-input 
                        wire:model="container_number" 
                        type="text" 
                        id="container_number"
                        placeholder="Ej: C-001, PALLET-123"
                        :error="$errors->first('container_number')"
                    />
                </div>

                {{-- Cantidad --}}
                <div>
                    <x-label for="quantity" required>Cantidad</x-label>
                    <x-input 
                        wire:model.live="quantity" 
                        type="number" 
                        min="1"
                        step="1"
                        id="quantity"
                        placeholder="1"
                        :error="$errors->first('quantity')"
                        required
                    />
                </div>

                {{-- Peso total --}}
                <div>
                    <x-label for="weight" required>Peso Total (kg)</x-label>
                    <x-input 
                        wire:model.live="weight" 
                        type="number" 
                        step="0.001"
                        min="0.01"
                        id="weight"
                        placeholder="0.00"
                        :error="$errors->first('weight')"
                        required
                    />
                </div>

                {{-- Peso por unidad (calculado) --}}
                @if($weight_per_unit)
                    <div>
                        <x-label for="weight_per_unit">Peso por Unidad (kg)</x-label>
                        <x-input 
                            wire:model="weight_per_unit" 
                            type="number" 
                            step="0.001"
                            id="weight_per_unit"
                            readonly
                            class="bg-gray-100"
                        />
                        <p class="mt-1 text-xs text-gray-500">Calculado automáticamente</p>
                    </div>
                @endif

                {{-- Ubicación --}}
                <div>
                    <x-label for="location">Ubicación</x-label>
                    <x-input 
                        wire:model="location" 
                        type="text" 
                        id="location"
                        placeholder="Ej: Almacén 1, Campo, Bodega"
                        :error="$errors->first('location')"
                    />
                </div>

                {{-- Estado --}}
                <div>
                    <x-label for="status" required>Estado</x-label>
                    <x-select 
                        wire:model="status" 
                        id="status"
                        :error="$errors->first('status')"
                        required
                    >
                        <option value="filled">Llenado</option>
                        <option value="in_transit">En tránsito</option>
                        <option value="delivered">Entregado</option>
                        <option value="stored">Almacenado</option>
                        <option value="empty">Vacío</option>
                    </x-select>
                </div>
            </div>
        </x-form-section>

        {{-- Fechas --}}
        <x-form-section title="Fechas" color="green">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Fecha de llenado --}}
                <div>
                    <x-label for="filled_date">Fecha de Llenado</x-label>
                    <x-input 
                        wire:model="filled_date" 
                        type="date" 
                        id="filled_date"
                        :error="$errors->first('filled_date')"
                    />
                </div>

                {{-- Fecha de entrega --}}
                <div>
                    <x-label for="delivery_date">Fecha de Entrega</x-label>
                    <x-input 
                        wire:model="delivery_date" 
                        type="date" 
                        id="delivery_date"
                        :error="$errors->first('delivery_date')"
                    />
                </div>
            </div>
        </x-form-section>

        {{-- Notas --}}
        <x-form-section title="Notas Adicionales" color="green">
            <div>
                <x-label for="notes">Notas</x-label>
                <x-textarea 
                    wire:model="notes" 
                    id="notes"
                    rows="4"
                    placeholder="Observaciones adicionales sobre el contenedor..."
                    :error="$errors->first('notes')"
                />
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

