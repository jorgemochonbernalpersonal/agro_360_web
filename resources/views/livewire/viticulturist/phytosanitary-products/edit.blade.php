@php
    $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>';
@endphp

<x-form-card
    title="Editar Producto Fitosanitario"
    description="Modifica los datos de {{ $product->name }}"
    :icon="$icon"
    icon-color="from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]"
    :back-url="route('viticulturist.phytosanitary-products.index')"
>
    <form wire:submit="save" class="space-y-8">
        <x-form-section title="Datos Básicos" color="green">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-label for="name" required>Nombre comercial</x-label>
                        <x-input 
                            wire:model="name" 
                            type="text" 
                            id="name"
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
                            :error="$errors->first('type')"
                        >
                            <option value="">Seleccionar...</option>
                            <option value="fungicida">Fungicida</option>
                            <option value="herbicida">Herbicida</option>
                            <option value="insecticida">Insecticida</option>
                            <option value="acaricida">Acaricida</option>
                            <option value="regulador del crecimiento">Regulador del crecimiento</option>
                            <option value="otro">Otro</option>
                        </x-select>
                    </div>
                    <div>
                        <x-label for="toxicity_class">Clase toxicológica</x-label>
                        <x-select 
                            wire:model="toxicity_class" 
                            id="toxicity_class"
                            :error="$errors->first('toxicity_class')"
                        >
                            <option value="">Seleccionar...</option>
                            <option value="I">I</option>
                            <option value="II">II</option>
                            <option value="III">III</option>
                            <option value="IV">IV</option>
                        </x-select>
                    </div>
                    <div>
                        <x-label for="withdrawal_period_days">Plazo de seguridad (días)</x-label>
                        <x-input 
                            wire:model="withdrawal_period_days" 
                            type="number" 
                            min="0"
                            id="withdrawal_period_days"
                            :error="$errors->first('withdrawal_period_days')"
                        />
                    </div>
                </div>
        </x-form-section>

        <x-form-section title="Información Adicional" color="green" class="pb-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-label for="registration_number">Nº de registro</x-label>
                        <x-input 
                            wire:model="registration_number" 
                            type="text" 
                            id="registration_number"
                            :error="$errors->first('registration_number')"
                        />
                    </div>
                    <div>
                        <x-label for="manufacturer">Fabricante</x-label>
                        <x-input 
                            wire:model="manufacturer" 
                            type="text" 
                            id="manufacturer"
                            :error="$errors->first('manufacturer')"
                        />
                    </div>
                </div>

                <div class="mt-6">
                    <x-label for="description">Descripción / Notas</x-label>
                    <x-textarea 
                        wire:model="description" 
                        id="description"
                        rows="4"
                        :error="$errors->first('description')"
                    />
                </div>
        </x-form-section>

        <x-form-actions 
            :cancel-url="route('viticulturist.phytosanitary-products.index')"
            submit-label="Actualizar Producto"
        />
    </form>
</x-form-card>


