<x-agro.form-card
    :title="__('Editar Entrega de Envases')"
    :description="__('Modifica los datos del registro de entrega de envases fitosanitarios')"
    :back-url="roleRoute('viticulturist.container-returns.index')"
>
    <form wire:submit="save" class="space-y-8">

        <x-agro.form-section :title="__('Datos de la Entrega')">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <flux:field>
                    <flux:label required>{{ __('Campaña') }}</flux:label>
                    <flux:select wire:model="campaign_id">
                        @foreach($campaigns as $c)
                            <flux:select.option value="{{ $c->id }}">{{ $c->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="campaign_id" />
                </flux:field>

                <flux:field>
                    <flux:label required>{{ __('Fecha de entrega') }}</flux:label>
                    <flux:input wire:model="date" type="date" />
                    <flux:error name="date" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Producto del catálogo (opcional)') }}</flux:label>
                    <flux:select wire:model.live="phytosanitary_product_id">
                        <flux:select.option value="">{{ __('Sin vincular') }}</flux:select.option>
                        @foreach($products as $p)
                            <flux:select.option value="{{ $p->id }}">{{ $p->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>

                <flux:field>
                    <flux:label required>{{ __('Nombre del producto') }}</flux:label>
                    <flux:input wire:model="product_name" type="text" />
                    <flux:error name="product_name" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Nº Registro MAPA') }}</flux:label>
                    <flux:input wire:model="registration_number" type="text" :placeholder="__('ES-XXXXXXXX')" />
                    <flux:error name="registration_number" />
                </flux:field>

            </div>
        </x-agro.form-section>

        <x-agro.form-section :title="__('Descripción de los Envases')">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                <flux:field>
                    <flux:label required>{{ __('Tipo de envase') }}</flux:label>
                    <flux:select wire:model="container_type">
                        @foreach($containerTypes as $key => $label)
                            <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="container_type" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Capacidad unitaria (L)') }}</flux:label>
                    <flux:input wire:model="container_size_liters" type="number" step="0.001" min="0" />
                    <flux:error name="container_size_liters" />
                </flux:field>

                <flux:field>
                    <flux:label required>{{ __('Número de envases') }}</flux:label>
                    <flux:input wire:model="containers_quantity" type="number" min="1" />
                    <flux:error name="containers_quantity" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Peso total vacíos (kg)') }}</flux:label>
                    <flux:input wire:model="total_weight_kg" type="number" step="0.001" min="0" />
                    <flux:error name="total_weight_kg" />
                </flux:field>

            </div>
        </x-agro.form-section>

        <x-agro.form-section :title="__('Punto de Recogida')">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <flux:field>
                    <flux:label required>{{ __('Sistema de gestión') }}</flux:label>
                    <flux:select wire:model="collection_system">
                        @foreach($collectionSystems as $key => $label)
                            <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="collection_system" />
                </flux:field>

                <flux:field>
                    <flux:label required>{{ __('Punto de recogida') }}</flux:label>
                    <flux:input wire:model="collection_point" type="text" />
                    <flux:error name="collection_point" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Nº albarán / documento') }}</flux:label>
                    <flux:input wire:model="transport_document" type="text" />
                    <flux:error name="transport_document" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Observaciones') }}</flux:label>
                    <flux:textarea wire:model="notes" rows="3" />
                    <flux:error name="notes" />
                </flux:field>

            </div>
        </x-agro.form-section>

        <x-agro.form-actions
            :cancel-url="roleRoute('viticulturist.container-returns.index')"
            :submit-label="__('Guardar Cambios')"
        />
    </form>
</x-agro.form-card>
