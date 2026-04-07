<x-agro.form-card
    title="Registrar Entrega de Envases"
    description="Documenta la entrega de envases vacíos de fitosanitarios en punto de recogida autorizado (SIGFITO / FIELD)"
    :back-url="roleRoute('viticulturist.container-returns.index')"
>
    <form wire:submit="save" class="space-y-8">

        <x-agro.form-section title="Datos de la Entrega">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <flux:field>
                    <flux:label required>Campaña</flux:label>
                    <flux:select wire:model="campaign_id">
                        @foreach($campaigns as $c)
                            <flux:select.option value="{{ $c->id }}">{{ $c->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="campaign_id" />
                </flux:field>

                <flux:field>
                    <flux:label required>Fecha de entrega</flux:label>
                    <flux:input wire:model="date" type="date" />
                    <flux:error name="date" />
                </flux:field>

                <flux:field>
                    <flux:label>Producto del catálogo (opcional)</flux:label>
                    <flux:select wire:model.live="phytosanitary_product_id">
                        <flux:select.option value="">Seleccionar producto...</flux:select.option>
                        @foreach($products as $p)
                            <flux:select.option value="{{ $p->id }}">{{ $p->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:description>Seleccionar rellena automáticamente nombre y nº registro</flux:description>
                </flux:field>

                <flux:field>
                    <flux:label required>Nombre del producto</flux:label>
                    <flux:input wire:model="product_name" type="text" placeholder="Ej: Mancozeb 80 WP" />
                    <flux:error name="product_name" />
                </flux:field>

                <flux:field>
                    <flux:label>Nº Registro MAPA</flux:label>
                    <flux:input wire:model="registration_number" type="text" placeholder="ES-XXXXXXXX" />
                    <flux:error name="registration_number" />
                </flux:field>

            </div>
        </x-agro.form-section>

        <x-agro.form-section title="Descripción de los Envases">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                <flux:field>
                    <flux:label required>Tipo de envase</flux:label>
                    <flux:select wire:model="container_type">
                        @foreach($containerTypes as $key => $label)
                            <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="container_type" />
                </flux:field>

                <flux:field>
                    <flux:label>Capacidad unitaria (L)</flux:label>
                    <flux:input wire:model="container_size_liters" type="number" step="0.001" min="0" placeholder="Ej: 5.000" />
                    <flux:error name="container_size_liters" />
                </flux:field>

                <flux:field>
                    <flux:label required>Número de envases</flux:label>
                    <flux:input wire:model="containers_quantity" type="number" min="1" placeholder="Ej: 10" />
                    <flux:error name="containers_quantity" />
                </flux:field>

                <flux:field>
                    <flux:label>Peso total envases vacíos (kg)</flux:label>
                    <flux:input wire:model="total_weight_kg" type="number" step="0.001" min="0" placeholder="Ej: 2.500" />
                    <flux:error name="total_weight_kg" />
                </flux:field>

            </div>
        </x-agro.form-section>

        <x-agro.form-section title="Punto de Recogida">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <flux:field>
                    <flux:label required>Sistema de gestión</flux:label>
                    <flux:select wire:model="collection_system">
                        @foreach($collectionSystems as $key => $label)
                            <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="collection_system" />
                </flux:field>

                <flux:field>
                    <flux:label required>Punto de recogida</flux:label>
                    <flux:input wire:model="collection_point" type="text" placeholder="Ej: Almacén Agrícola García - Valladolid" />
                    <flux:error name="collection_point" />
                </flux:field>

                <flux:field>
                    <flux:label>Nº albarán / documento</flux:label>
                    <flux:input wire:model="transport_document" type="text" placeholder="Ej: ALB-2025-001234" />
                    <flux:description>Número del albarán de entrega emitido por el punto de recogida</flux:description>
                    <flux:error name="transport_document" />
                </flux:field>

                <flux:field>
                    <flux:label>Observaciones</flux:label>
                    <flux:textarea wire:model="notes" rows="3" />
                    <flux:error name="notes" />
                </flux:field>

            </div>
        </x-agro.form-section>

        <x-agro.form-actions
            :cancel-url="roleRoute('viticulturist.container-returns.index')"
            submit-label="Registrar Entrega"
        />
    </form>
</x-agro.form-card>
