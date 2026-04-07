<x-agro.form-card
    title="Nueva Maquinaria"
    description="Registra una nueva maquinaria o equipo agricola"
    :back-url="roleRoute('viticulturist.machinery.index')"
>
    <form wire:submit="save" class="space-y-8" enctype="multipart/form-data" data-cy="machinery-create-form">
        <x-agro.form-section title="Informacion Basica">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nombre -->
                    <flux:field>
                        <flux:label>Nombre *</flux:label>
                        <flux:input
                            wire:model="name"
                            type="text"
                            id="name"
                            data-cy="machinery-name"
                            placeholder="Ej: Tractor John Deere"
                            required
                        />
                        <flux:error name="name" />
                    </flux:field>

                    <!-- Tipo -->
                    <flux:field>
                        <flux:label>Tipo *</flux:label>
                        <flux:select
                            wire:model="machinery_type_id"
                            id="machinery_type_id"
                            data-cy="machinery-type-id"
                            required
                        >
                            <option value="">Selecciona un tipo</option>
                            @foreach($machineryTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </flux:select>
                        <flux:error name="machinery_type_id" />
                    </flux:field>

                    <!-- Marca -->
                    <flux:field>
                        <flux:label>Marca</flux:label>
                        <flux:input
                            wire:model="brand"
                            type="text"
                            id="brand"
                            data-cy="machinery-brand"
                            placeholder="Ej: John Deere"
                        />
                        <flux:error name="brand" />
                    </flux:field>

                    <!-- Modelo -->
                    <flux:field>
                        <flux:label>Modelo</flux:label>
                        <flux:input
                            wire:model="model"
                            type="text"
                            id="model"
                            data-cy="machinery-model"
                            placeholder="Ej: 6120M"
                        />
                        <flux:error name="model" />
                    </flux:field>

                    <!-- Numero de Serie -->
                    <flux:field>
                        <flux:label>Numero de Serie</flux:label>
                        <flux:input
                            wire:model="serial_number"
                            type="text"
                            id="serial_number"
                            data-cy="machinery-serial-number"
                            placeholder="Ej: JD123456"
                        />
                        <flux:error name="serial_number" />
                    </flux:field>

                    <!-- Ano -->
                    <flux:field>
                        <flux:label>Ano</flux:label>
                        <flux:input
                            wire:model="year"
                            type="number"
                            min="1900"
                            max="{{ now()->year + 1 }}"
                            id="year"
                            data-cy="machinery-year"
                        />
                        <flux:error name="year" />
                    </flux:field>
                </div>
        </x-agro.form-section>

        <x-agro.form-section title="Registro y Propiedad">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Inscripcion ROMA -->
                    <flux:field>
                        <flux:label>Inscripcion ROMA</flux:label>
                        <flux:input
                            wire:model="roma_registration"
                            type="text"
                            id="roma_registration"
                            placeholder="Ej: ROMA-12345"
                        />
                        <flux:error name="roma_registration" />
                    </flux:field>

                    <!-- Capacidad -->
                    <flux:field>
                        <flux:label>Capacidad</flux:label>
                        <flux:input
                            wire:model="capacity"
                            type="text"
                            id="capacity"
                            placeholder="Ej: 1000L, 5m3"
                        />
                        <flux:error name="capacity" />
                    </flux:field>
                </div>

                <!-- Es Alquilada -->
                <div class="mt-6 flex items-center">
                    <input
                        wire:model="is_rented"
                        type="checkbox"
                        id="is_rented"
                        data-cy="machinery-is-rented"
                        class="w-4 h-4 text-agro-700 border-zinc-300 rounded focus:ring-agro-700"
                    >
                    <label for="is_rented" class="ml-3 text-sm font-semibold text-zinc-700">
                        Maquinaria alquilada
                    </label>
                </div>
                <flux:error name="is_rented" />
        </x-agro.form-section>

        <x-agro.form-section title="Fechas y Valores">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Fecha de Compra -->
                    <flux:field>
                        <flux:label>Fecha de Compra</flux:label>
                        <flux:input
                            wire:model="purchase_date"
                            type="date"
                            id="purchase_date"
                        />
                        <flux:error name="purchase_date" />
                    </flux:field>

                    <!-- Fecha de Ultima Revision -->
                    <flux:field>
                        <flux:label>Fecha de Ultima Revision</flux:label>
                        <flux:input
                            wire:model="last_revision_date"
                            type="date"
                            id="last_revision_date"
                        />
                        <flux:error name="last_revision_date" />
                    </flux:field>

                    <!-- Precio de Compra -->
                    <flux:field>
                        <flux:label>Precio de Compra (EUR)</flux:label>
                        <flux:input
                            wire:model="purchase_price"
                            type="number"
                            step="0.01"
                            min="0"
                            id="purchase_price"
                            placeholder="0.00"
                        />
                        <flux:error name="purchase_price" />
                    </flux:field>

                    <!-- Valor Actual -->
                    <flux:field>
                        <flux:label>Valor Actual (EUR)</flux:label>
                        <flux:input
                            wire:model="current_value"
                            type="number"
                            step="0.01"
                            min="0"
                            id="current_value"
                            placeholder="0.00"
                        />
                        <flux:error name="current_value" />
                    </flux:field>
                </div>
        </x-agro.form-section>

        <x-agro.form-section title="Imagen y Notas">

                <!-- Imagen -->
                <div class="mb-6">
                    <label for="image" class="block text-sm font-semibold text-zinc-700 mb-2">
                        Imagen
                    </label>
                    <input
                        wire:model="image"
                        type="file"
                        accept="image/*"
                        id="image"
                        onchange="
                            const file = this.files[0];
                            if (file) {
                                const reader = new FileReader();
                                reader.onload = function(e) {
                                    const previewImg = document.getElementById('machinery-image-preview');
                                    const previewContainer = document.getElementById('machinery-image-preview-container');
                                    if (previewImg) {
                                        previewImg.src = e.target.result;
                                        previewImg.classList.remove('hidden');
                                        previewImg.style.display = 'block';
                                    }
                                    if (previewContainer) {
                                        previewContainer.classList.remove('hidden');
                                        previewContainer.style.display = 'block';
                                    }
                                };
                                reader.onerror = function() {
                                    console.error('Error al leer el archivo');
                                    const previewContainer = document.getElementById('machinery-image-preview-container');
                                    if (previewContainer) {
                                        previewContainer.classList.add('hidden');
                                        previewContainer.style.display = 'none';
                                    }
                                };
                                reader.readAsDataURL(file);
                            } else {
                                const previewContainer = document.getElementById('machinery-image-preview-container');
                                if (previewContainer) {
                                    previewContainer.classList.add('hidden');
                                    previewContainer.style.display = 'none';
                                }
                            }
                        "
                        class="w-full px-4 py-3 border-2 border-zinc-200 rounded-xl focus:ring-2 focus:ring-agro-700 focus:border-transparent transition-all"
                    >
                    <flux:error name="image" />
                    <div id="machinery-image-preview-container" wire:ignore class="mt-4 hidden">
                        <p class="text-sm text-zinc-600 mb-2 font-semibold">Vista previa:</p>
                        <img
                            id="machinery-image-preview"
                            src=""
                            alt="Vista previa"
                            class="max-w-xs rounded-lg border-2 border-zinc-200 hidden"
                            style="max-height: 300px; object-fit: contain;"
                            onerror="this.style.display='none'; document.getElementById('machinery-image-preview-container').style.display='none'; document.getElementById('machinery-image-preview-container').classList.add('hidden');"
                        >
                    </div>
                </div>

                <!-- Notas -->
                <flux:field>
                    <flux:label>Notas</flux:label>
                    <flux:textarea
                        wire:model="notes"
                        id="notes"
                        data-cy="machinery-notes"
                        rows="4"
                        placeholder="Notas adicionales sobre la maquinaria..."
                    />
                    <flux:error name="notes" />
                </flux:field>
        </x-agro.form-section>

        <x-agro.form-actions
            :cancel-url="roleRoute('viticulturist.machinery.index')"
            submit-label="Crear Maquinaria"
        />
    </form>
</x-agro.form-card>
