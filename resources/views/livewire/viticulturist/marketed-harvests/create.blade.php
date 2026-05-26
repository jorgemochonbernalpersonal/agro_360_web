<x-agro.form-card
    title="{{ __('Registrar Entrega de Cosecha') }}"
    :description="__('Registra una entrega de uva a bodega, cooperativa o venta directa')"
    :back-url="roleRoute('viticulturist.marketed-harvests.index')"
>
    <form wire:submit="save" class="space-y-8">

        <x-agro.form-section title="{{ __('Datos de la Entrega') }}">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <flux:field class="md:col-span-2">
                    <flux:label required>{{ __('Cosecha asociada') }}</flux:label>
                    <flux:select wire:model="harvest_id">
                        <option value="">{{ __('Seleccionar cosecha...') }}</option>
                        @foreach($harvests as $h)
                            <option value="{{ $h->id }}">
                                {{ $h->plotPlanting->plot->name ?? '?' }} — {{ $h->plotPlanting->grapeVariety->name ?? '?' }}
                                ({{ $h->harvest_start_date->format('d/m/Y') }}, {{ number_format($h->total_weight, 0, ',', '.') }} kg)
                            </option>
                        @endforeach
                    </flux:select>
                    <flux:error name="harvest_id" />
                </flux:field>

                <flux:field>
                    <flux:label required>{{ __('Fecha de entrega') }}</flux:label>
                    <flux:input wire:model="delivery_date" type="date" />
                    <flux:error name="delivery_date" />
                </flux:field>

                <flux:field>
                    <flux:label required>{{ __('Cantidad (kg)') }}</flux:label>
                    <flux:input wire:model.live="quantity_kg" type="number" step="0.001" min="0" placeholder="0.000" />
                    <flux:error name="quantity_kg" />
                </flux:field>

                <flux:field>
                    <flux:label required>{{ __('Destino') }}</flux:label>
                    <flux:select wire:model="destination_type">
                        <option value="">{{ __('Seleccionar...') }}</option>
                        @foreach($destinations as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="destination_type" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Comprador / Bodega') }}</flux:label>
                    <flux:input wire:model="buyer_name" type="text" :placeholder="__('Nombre del comprador')" />
                    <flux:error name="buyer_name" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('REGA del destino') }}</flux:label>
                    <flux:input wire:model="buyer_rega_code" type="text" :placeholder="__('Código REGA')" />
                    <flux:error name="buyer_rega_code" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Documento de transporte') }}</flux:label>
                    <flux:input wire:model="transport_document" type="text" :placeholder="__('Nº albarán/CMR')" />
                    <flux:error name="transport_document" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Matrícula vehículo') }}</flux:label>
                    <flux:input wire:model="vehicle_plate" type="text" :placeholder="__('0000-AAA')" />
                    <flux:error name="vehicle_plate" />
                </flux:field>

            </div>
        </x-agro.form-section>

        <x-agro.form-section title="{{ __('Precio y Valor') }}">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <flux:field>
                    <flux:label>{{ __('Precio/kg (€)') }}</flux:label>
                    <flux:input wire:model.live="price_per_kg" type="number" step="0.0001" min="0" placeholder="0.0000" />
                    <flux:error name="price_per_kg" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Valor total (€)') }}</flux:label>
                    <flux:input wire:model="total_value" type="number" step="0.01" min="0" placeholder="0.00" />
                    <flux:description>Calculado automáticamente</flux:description>
                    <flux:error name="total_value" />
                </flux:field>

            </div>
        </x-agro.form-section>

        <x-agro.form-section title="{{ __('Notas') }}">
            <div class="grid grid-cols-1 gap-6">

                <flux:field>
                    <flux:label>{{ __('Notas') }}</flux:label>
                    <flux:textarea wire:model="notes" rows="3" />
                    <flux:error name="notes" />
                </flux:field>

            </div>
        </x-agro.form-section>

        <x-agro.form-actions
            :cancel-url="roleRoute('viticulturist.marketed-harvests.index')"
            submit-:label="__('Registrar Entrega')"
        />
    </form>
</x-agro.form-card>
