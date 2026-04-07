<x-agro.form-card
    title="Nuevo Producto Fitosanitario"
    description="Añade un producto para poder seleccionarlo al registrar tratamientos"
    :back-url="roleRoute('viticulturist.phytosanitary-products.index')"
>
    <form wire:submit="save" class="space-y-8" data-cy="product-form">
        {{-- Alerta PAC --}}
        <flux:callout variant="warning" icon="exclamation-triangle">
            <strong>Campos obligatorios PAC:</strong> El número de registro MAPA y el plazo de seguridad son obligatorios según el Real Decreto 1311/2012.
        </flux:callout>

        <x-agro.form-section title="Datos Básicos" color="green">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:field>
                        <flux:label for="name" badge="Obligatorio">Nombre comercial</flux:label>
                        <flux:input
                            wire:model="name"
                            type="text"
                            id="name"
                            data-cy="product-name-input"
                            placeholder="Ej: Fungicida X 25 SC"
                            required
                        />
                        <flux:error name="name" />
                    </flux:field>
                    <flux:field>
                        <flux:label for="active_ingredient">Materia activa</flux:label>
                        <flux:input
                            wire:model="active_ingredient"
                            type="text"
                            id="active_ingredient"
                            data-cy="product-active-ingredient-input"
                            placeholder="Ej: Azoxistrobina"
                        />
                        <flux:error name="active_ingredient" />
                    </flux:field>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                    <flux:field>
                        <flux:label for="type">Tipo</flux:label>
                        <flux:select
                            wire:model="type"
                            id="type"
                            data-cy="product-type-select"
                        >
                            <option value="">Seleccionar...</option>
                            <option value="fungicida">Fungicida</option>
                            <option value="herbicida">Herbicida</option>
                            <option value="insecticida">Insecticida</option>
                            <option value="acaricida">Acaricida</option>
                            <option value="nematicida">Nematicida</option>
                            <option value="otro">Otro</option>
                        </flux:select>
                        <flux:error name="type" />
                    </flux:field>
                    <flux:field>
                        <flux:label for="toxicity_class">Clase toxicológica</flux:label>
                        <flux:select
                            wire:model="toxicity_class"
                            id="toxicity_class"
                            data-cy="product-toxicity-class-select"
                        >
                            <option value="">Seleccionar...</option>
                            <option value="I">I (Muy tóxico)</option>
                            <option value="II">II (Tóxico)</option>
                            <option value="III">III (Nocivo)</option>
                            <option value="IV">IV (Poco tóxico)</option>
                        </flux:select>
                        <flux:error name="toxicity_class" />
                    </flux:field>
                    <flux:field>
                        <flux:label for="manufacturer">Fabricante</flux:label>
                        <flux:input
                            wire:model="manufacturer"
                            type="text"
                            id="manufacturer"
                            data-cy="product-manufacturer-input"
                            placeholder="Nombre del fabricante"
                        />
                        <flux:error name="manufacturer" />
                    </flux:field>
                </div>
        </x-agro.form-section>

        <x-agro.form-section title="Información Regulatoria (PAC)" color="amber">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:field>
                        <flux:label for="registration_number" badge="Obligatorio">
                            <span class="flex items-center gap-2">
                                Nº de Registro MAPA
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                    Obligatorio
                                </span>
                            </span>
                        </flux:label>
                        <flux:input
                            wire:model="registration_number"
                            type="text"
                            id="registration_number"
                            data-cy="product-registration-number-input"
                            placeholder="ES-12345678"
                            required
                        />
                        <flux:error name="registration_number" />
                        <p class="mt-1 text-xs text-zinc-500">Formato: ES-00000000 (ES seguido de 8 dígitos)</p>
                    </flux:field>
                    <flux:field>
                        <flux:label for="withdrawal_period_days" badge="Obligatorio">
                            <span class="flex items-center gap-2">
                                Plazo de Seguridad (días)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                    Obligatorio
                                </span>
                            </span>
                        </flux:label>
                        <flux:input
                            wire:model="withdrawal_period_days"
                            type="number"
                            min="0"
                            id="withdrawal_period_days"
                            data-cy="product-withdrawal-period-input"
                            placeholder="Ej: 21"
                            required
                        />
                        <flux:error name="withdrawal_period_days" />
                        <p class="mt-1 text-xs text-zinc-500">Días mínimos entre aplicación y cosecha</p>
                    </flux:field>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <flux:field>
                        <flux:label for="registration_status" badge="Obligatorio">Estado del Registro</flux:label>
                        <flux:select
                            wire:model="registration_status"
                            id="registration_status"
                            data-cy="product-registration-status-select"
                            required
                        >
                            <option value="active">Activo</option>
                            <option value="expired">Caducado</option>
                            <option value="revoked">Revocado</option>
                        </flux:select>
                        <flux:error name="registration_status" />
                    </flux:field>
                    <flux:field>
                        <flux:label for="registration_expiry_date">Fecha de Caducidad del Registro</flux:label>
                        <flux:input
                            wire:model="registration_expiry_date"
                            type="date"
                            id="registration_expiry_date"
                            data-cy="product-registration-expiry-date-input"
                        />
                        <flux:error name="registration_expiry_date" />
                        <p class="mt-1 text-xs text-zinc-500">Opcional: fecha de vencimiento del registro</p>
                    </flux:field>
                </div>
        </x-agro.form-section>

        <x-agro.form-section title="Información Adicional" color="green" class="pb-6">
                <div class="mt-6">
                    <flux:field>
                        <flux:label for="description">Descripción / Notas</flux:label>
                        <flux:textarea
                            wire:model="description"
                            id="description"
                            data-cy="product-description-input"
                            rows="4"
                            placeholder="Detalles, recomendaciones de uso, observaciones..."
                        />
                        <flux:error name="description" />
                    </flux:field>
                </div>
        </x-agro.form-section>

        <x-agro.form-actions
            :cancel-url="roleRoute('viticulturist.phytosanitary-products.index')"
            submit-label="Crear Producto"
        />
    </form>
</x-agro.form-card>
