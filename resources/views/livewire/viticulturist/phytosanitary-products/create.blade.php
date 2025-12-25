@php
    $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>';
@endphp

<x-form-card
    title="Nuevo Producto Fitosanitario"
    description="Añade un producto para poder seleccionarlo al registrar tratamientos"
    :icon="$icon"
    icon-color="from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]"
    :back-url="route('viticulturist.phytosanitary-products.index')"
>
    <form wire:submit="save" class="space-y-8" data-cy="product-form">
        {{-- Alerta PAC --}}
        <div class="bg-amber-50 border-l-4 border-amber-400 p-4 rounded-r-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-amber-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-amber-700">
                        <strong>Campos obligatorios PAC:</strong> El número de registro MAPA y el plazo de seguridad son obligatorios según el Real Decreto 1311/2012.
                    </p>
                </div>
            </div>
        </div>

        <x-form-section title="Datos Básicos" color="green">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-label for="name" required>Nombre comercial</x-label>
                        <x-input 
                            wire:model="name" 
                            type="text" 
                            id="name"
                            data-cy="product-name-input"
                            placeholder="Ej: Fungicida X 25 SC"
                            :error="$errors->first('name')"
                            required
                        />
                    </div>
                    <div>
                        <x-label for="active_ingredient">Materia activa</x-label>
                        <x-input 
                            wire:model="active_ingredient" 
                            type="text" 
                            id="active_ingredient"
                            data-cy="product-active-ingredient-input"
                            placeholder="Ej: Azoxistrobina"
                            :error="$errors->first('active_ingredient')"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                    <div>
                        <x-label for="type">Tipo</x-label>
                        <x-select 
                            wire:model="type" 
                            id="type"
                            data-cy="product-type-select"
                            :error="$errors->first('type')"
                        >
                            <option value="">Seleccionar...</option>
                            <option value="fungicida">Fungicida</option>
                            <option value="herbicida">Herbicida</option>
                            <option value="insecticida">Insecticida</option>
                            <option value="acaricida">Acaricida</option>
                            <option value="nematicida">Nematicida</option>
                            <option value="otro">Otro</option>
                        </x-select>
                    </div>
                    <div>
                        <x-label for="toxicity_class">Clase toxicológica</x-label>
                        <x-select 
                            wire:model="toxicity_class" 
                            id="toxicity_class"
                            data-cy="product-toxicity-class-select"
                            :error="$errors->first('toxicity_class')"
                        >
                            <option value="">Seleccionar...</option>
                            <option value="I">I (Muy tóxico)</option>
                            <option value="II">II (Tóxico)</option>
                            <option value="III">III (Nocivo)</option>
                            <option value="IV">IV (Poco tóxico)</option>
                        </x-select>
                    </div>
                    <div>
                        <x-label for="manufacturer">Fabricante</x-label>
                        <x-input 
                            wire:model="manufacturer" 
                            type="text" 
                            id="manufacturer"
                            data-cy="product-manufacturer-input"
                            placeholder="Nombre del fabricante"
                            :error="$errors->first('manufacturer')"
                        />
                    </div>
                </div>
        </x-form-section>

        <x-form-section title="Información Regulatoria (PAC)" color="amber">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-label for="registration_number" required>
                            <span class="flex items-center gap-2">
                                Nº de Registro MAPA
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                    Obligatorio
                                </span>
                            </span>
                        </x-label>
                        <x-input 
                            wire:model="registration_number" 
                            type="text" 
                            id="registration_number"
                            data-cy="product-registration-number-input"
                            placeholder="ES-12345678"
                            :error="$errors->first('registration_number')"
                            required
                        />
                        <p class="mt-1 text-xs text-gray-500">Formato: ES-00000000 (ES seguido de 8 dígitos)</p>
                    </div>
                    <div>
                        <x-label for="withdrawal_period_days" required>
                            <span class="flex items-center gap-2">
                                Plazo de Seguridad (días)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                    Obligatorio
                                </span>
                            </span>
                        </x-label>
                        <x-input 
                            wire:model="withdrawal_period_days" 
                            type="number" 
                            min="0"
                            id="withdrawal_period_days"
                            data-cy="product-withdrawal-period-input"
                            placeholder="Ej: 21"
                            :error="$errors->first('withdrawal_period_days')"
                            required
                        />
                        <p class="mt-1 text-xs text-gray-500">⚠️ Días mínimos entre aplicación y cosecha</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <div>
                        <x-label for="registration_status" required>Estado del Registro</x-label>
                        <x-select 
                            wire:model="registration_status" 
                            id="registration_status"
                            data-cy="product-registration-status-select"
                            :error="$errors->first('registration_status')"
                            required
                        >
                            <option value="active">✅ Activo</option>
                            <option value="expired">⏰ Caducado</option>
                            <option value="revoked">❌ Revocado</option>
                        </x-select>
                    </div>
                    <div>
                        <x-label for="registration_expiry_date">Fecha de Caducidad del Registro</x-label>
                        <x-input 
                            wire:model="registration_expiry_date" 
                            type="date" 
                            id="registration_expiry_date"
                            data-cy="product-registration-expiry-date-input"
                            :error="$errors->first('registration_expiry_date')"
                        />
                        <p class="mt-1 text-xs text-gray-500">Opcional: fecha de vencimiento del registro</p>
                    </div>
                </div>
        </x-form-section>

        <x-form-section title="Información Adicional" color="green" class="pb-6">
                <div class="mt-6">
                    <x-label for="description">Descripción / Notas</x-label>
                    <x-textarea 
                        wire:model="description" 
                        id="description"
                        data-cy="product-description-input"
                        rows="4"
                        placeholder="Detalles, recomendaciones de uso, observaciones..."
                        :error="$errors->first('description')"
                    />
                </div>
        </x-form-section>

        <x-form-actions 
            :cancel-url="route('viticulturist.phytosanitary-products.index')"
            submit-label="Crear Producto"
        />
    </form>
</x-form-card>


