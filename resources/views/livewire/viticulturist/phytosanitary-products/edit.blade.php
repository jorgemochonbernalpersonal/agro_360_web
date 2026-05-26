<x-agro.form-card
    :title="__('Editar Producto Fitosanitario')"
    :description="__('Modifica los datos de') . ' ' . $product->name"
    :back-url="roleRoute('viticulturist.phytosanitary-products.index')"
>
    <form wire:submit="save" class="space-y-8" data-cy="product-form">
        {{-- Alerta PAC --}}
        <flux:callout variant="warning" icon="exclamation-triangle">
            <strong>{{ __('Campos obligatorios PAC:') }}</strong> {{ __('El número de registro MAPA y el plazo de seguridad son obligatorios según el Real Decreto 1311/2012.') }}
        </flux:callout>

        <x-agro.form-section :title="__('Datos Básicos')" color="green">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:field>
                        <flux:label for="name" :badge="__('Obligatorio')">{{ __('Nombre comercial') }}</flux:label>
                        <flux:input
                            wire:model="name"
                            type="text"
                            id="name"
                            data-cy="product-name-input"
                            :placeholder="__('Ej: Fungicida X 25 SC')"
                            required
                        />
                        <flux:error name="name" />
                    </flux:field>
                    <flux:field>
                        <flux:label for="active_ingredient">{{ __('Materia activa') }}</flux:label>
                        <flux:input
                            wire:model="active_ingredient"
                            type="text"
                            id="active_ingredient"
                            data-cy="product-active-ingredient-input"
                            :placeholder="__('Ej: Azoxistrobina')"
                        />
                        <flux:error name="active_ingredient" />
                    </flux:field>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                    <flux:field>
                        <flux:label for="type">{{ __('Tipo') }}</flux:label>
                        <flux:select
                            wire:model="type"
                            id="type"
                            data-cy="product-type-select"
                        >
                            <option value="">{{ __('Seleccionar...') }}</option>
                            <option value="fungicida">{{ __('Fungicida') }}</option>
                            <option value="herbicida">{{ __('Herbicida') }}</option>
                            <option value="insecticida">{{ __('Insecticida') }}</option>
                            <option value="acaricida">{{ __('Acaricida') }}</option>
                            <option value="nematicida">{{ __('Nematicida') }}</option>
                            <option value="otro">{{ __('Otro') }}</option>
                        </flux:select>
                        <flux:error name="type" />
                    </flux:field>
                    <flux:field>
                        <flux:label for="toxicity_class">{{ __('Clase toxicológica') }}</flux:label>
                        <flux:select
                            wire:model="toxicity_class"
                            id="toxicity_class"
                            data-cy="product-toxicity-class-select"
                        >
                            <option value="">{{ __('Seleccionar...') }}</option>
                            <option value="I">{{ __('I (Muy tóxico)') }}</option>
                            <option value="II">{{ __('II (Tóxico)') }}</option>
                            <option value="III">{{ __('III (Nocivo)') }}</option>
                            <option value="IV">{{ __('IV (Poco tóxico)') }}</option>
                        </flux:select>
                        <flux:error name="toxicity_class" />
                    </flux:field>
                    <flux:field>
                        <flux:label for="manufacturer">{{ __('Fabricante') }}</flux:label>
                        <flux:input
                            wire:model="manufacturer"
                            type="text"
                            id="manufacturer"
                            data-cy="product-manufacturer-input"
                            :placeholder="__('Nombre del fabricante')"
                        />
                        <flux:error name="manufacturer" />
                    </flux:field>
                </div>
        </x-agro.form-section>

        <x-agro.form-section :title="__('Información Regulatoria (PAC)')" color="amber">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:field>
                        <flux:label for="registration_number" :badge="__('Obligatorio')">
                            <span class="flex items-center gap-2">
                                {{ __('Nº de Registro MAPA') }}
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                    {{ __('Obligatorio') }}
                                </span>
                            </span>
                        </flux:label>
                        <flux:input
                            wire:model="registration_number"
                            type="text"
                            id="registration_number"
                            data-cy="product-registration-number-input"
                            placeholder="{{ __('ES-12345678') }}"
                            required
                        />
                        <flux:error name="registration_number" />
                        <p class="mt-1 text-xs text-zinc-500">{{ __('Formato: ES-00000000 (ES seguido de 8 dígitos)') }}</p>
                    </flux:field>
                    <flux:field>
                        <flux:label for="withdrawal_period_days" :badge="__('Obligatorio')">
                            <span class="flex items-center gap-2">
                                {{ __('Plazo de Seguridad (días)') }}
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                    {{ __('Obligatorio') }}
                                </span>
                            </span>
                        </flux:label>
                        <flux:input
                            wire:model="withdrawal_period_days"
                            type="number"
                            min="0"
                            id="withdrawal_period_days"
                            data-cy="product-withdrawal-period-input"
                            :placeholder="__('Ej: 21')"
                            required
                        />
                        <flux:error name="withdrawal_period_days" />
                        <p class="mt-1 text-xs text-zinc-500">{{ __('Días mínimos entre aplicación y cosecha') }}</p>
                    </flux:field>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <flux:field>
                        <flux:label for="registration_status" :badge="__('Obligatorio')">{{ __('Estado del Registro') }}</flux:label>
                        <flux:select
                            wire:model="registration_status"
                            id="registration_status"
                            data-cy="product-registration-status-select"
                            required
                        >
                            <option value="active">{{ __('Activo') }}</option>
                            <option value="expired">{{ __('Caducado') }}</option>
                            <option value="revoked">{{ __('Revocado') }}</option>
                        </flux:select>
                        <flux:error name="registration_status" />
                    </flux:field>
                    <flux:field>
                        <flux:label for="registration_expiry_date">{{ __('Fecha de Caducidad del Registro') }}</flux:label>
                        <flux:input
                            wire:model="registration_expiry_date"
                            type="date"
                            id="registration_expiry_date"
                            data-cy="product-registration-expiry-date-input"
                        />
                        <flux:error name="registration_expiry_date" />
                        <p class="mt-1 text-xs text-zinc-500">{{ __('Opcional: fecha de vencimiento del registro') }}</p>
                    </flux:field>
                </div>
        </x-agro.form-section>

        <x-agro.form-section :title="__('Información Adicional')" color="green" class="pb-6">
                <div class="mt-6">
                    <flux:field>
                        <flux:label for="description">{{ __('Descripción / Notas') }}</flux:label>
                        <flux:textarea
                            wire:model="description"
                            id="description"
                            data-cy="product-description-input"
                            rows="4"
                            :placeholder="__('Detalles, recomendaciones de uso, observaciones...')"
                        />
                        <flux:error name="description" />
                    </flux:field>
                </div>
        </x-agro.form-section>

        <x-agro.form-actions
            :cancel-url="roleRoute('viticulturist.phytosanitary-products.index')"
            :submit-label="__('Actualizar Producto')"
        />
    </form>
</x-agro.form-card>
