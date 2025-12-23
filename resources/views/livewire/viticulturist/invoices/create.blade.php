<div>
    <x-form-card
        title="Nueva Factura"
        description="Crea una nueva factura"
        icon="🧾"
        icon-color="from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]"
        :back-url="route('viticulturist.invoices.index')"
    >
        <form wire:submit="save" class="space-y-8" data-cy="invoice-create-form">
            <x-form-section title="Cliente" color="green">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-label for="client_id" required>Cliente</x-label>
                        <x-select wire:model.live="client_id" id="client_id" data-cy="client-id" required>
                            <option value="">Selecciona un cliente</option>
                            @foreach($availableClients as $client)
                                <option value="{{ $client->id }}">{{ $client->full_name }}</option>
                            @endforeach
                        </x-select>
                        @error('client_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    @if($client_id)
                        <div>
                            <x-label for="client_address_id">Dirección de facturación</x-label>
                            <x-select wire:model="client_address_id" id="client_address_id" data-cy="client-address-id">
                                <option value="">Selecciona una dirección</option>
                                @foreach($availableAddresses as $address)
                                    <option value="{{ $address->id }}">
                                        Dirección #{{ $address->id }}
                                        @if($address->is_default)
                                            (Por defecto)
                                        @endif
                                    </option>
                                @endforeach
                            </x-select>
                            @error('client_address_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif
                </div>
            </x-form-section>

            <x-form-section title="Código de Albarán" color="blue">
                <div class="max-w-md">
                    <x-label for="delivery_note_code" required>Código de Albarán</x-label>
                    <x-input 
                        wire:model.live="delivery_note_code" 
                        id="delivery_note_code" 
                        data-cy="delivery-note-code"
                        type="text" 
                        required
                        placeholder="Ej: ALB-2025-0001"
                    />
                    @error('delivery_note_code')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-2 text-xs text-gray-500">
                        💡 El código se genera automáticamente de forma secuencial. Puedes modificarlo si lo necesitas.
                        @if($delivery_note_code_modified)
                            <span class="text-orange-600 font-semibold">⚠️ Has modificado el código automático.</span>
                        @endif
                    </p>
                </div>
            </x-form-section>

            <x-form-section title="Fechas" color="green">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-label for="invoice_date" required>Fecha de factura</x-label>
                        <x-input wire:model="invoice_date" id="invoice_date" data-cy="invoice-date" type="date" required />
                        @error('invoice_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <x-label for="due_date">Fecha de vencimiento</x-label>
                        <x-input wire:model="due_date" id="due_date" data-cy="due-date" type="date" />
                        @error('due_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </x-form-section>

            <x-form-section title="Cosechas para Facturar" color="purple">
                @if($fromHarvestRoute)
                    <div class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <p class="text-sm text-yellow-800 font-semibold">
                            ⚠️ <strong>Obligatorio:</strong> Debes seleccionar al menos una cosecha para crear la factura.
                        </p>
                    </div>
                @endif
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <x-label for="selectedCampaign">Filtrar por Campaña</x-label>
                            <x-select wire:model.live="selectedCampaign" id="selectedCampaign" data-cy="selected-campaign">
                                <option value="">Todas las campañas</option>
                                @foreach($campaigns as $campaign)
                                    <option value="{{ $campaign->id }}">{{ $campaign->year }}</option>
                                @endforeach
                            </x-select>
                        </div>
                        <div class="md:col-span-2">
                            <x-label for="selectedHarvestId" :required="$fromHarvestRoute">Selecciona una cosecha para añadir</x-label>
                            <x-select 
                                wire:model.live="selectedHarvestId" 
                                wire:change="addHarvestToInvoice"
                                id="selectedHarvestId"
                                data-cy="selected-harvest-id"
                                :required="$fromHarvestRoute"
                            >
                                <option value="">-- Selecciona una cosecha sin facturar --</option>
                                @foreach($availableHarvests as $harvest)
                                    <option value="{{ $harvest->id }}">
                                        {{ $harvest->plotPlanting->grapeVariety->name ?? 'Sin variedad' }} - 
                                        {{ $harvest->activity->plot->name ?? 'Sin parcela' }} - 
                                        {{ $harvest->harvest_start_date->format('d/m/Y') }} - 
                                        {{ number_format($harvest->total_weight, 2) }} kg
                                        @if($harvest->price_per_kg)
                                            ({{ number_format($harvest->price_per_kg, 4) }} €/kg)
                                        @endif
                                    </option>
                                @endforeach
                            </x-select>
                            @if($fromHarvestRoute && $errors->has('items'))
                                <p class="mt-1 text-sm text-red-600 font-medium">{{ $errors->first('items') }}</p>
                            @endif
                            <p class="mt-2 text-xs text-gray-500">
                                💡 La cosecha se añadirá automáticamente como item al seleccionarla
                            </p>
                        </div>
                    </div>
                    @if($availableHarvests->isEmpty())
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <p class="text-sm text-blue-800">
                                <strong>ℹ️ No hay cosechas disponibles para facturar.</strong><br>
                                Todas las cosechas ya han sido facturadas o no hay cosechas registradas.
                            </p>
                        </div>
                    @endif
                </div>
            </x-form-section>

            <x-form-section title="Items de la Factura" color="green">
                @if(!$fromHarvestRoute)
                    <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <p class="text-sm text-blue-800">
                            <strong>💡 Tip:</strong> Puedes añadir items manualmente o seleccionar una cosecha arriba para pre-llenar automáticamente.
                        </p>
                    </div>
                @else
                    <div class="mb-4 p-4 bg-purple-50 border border-purple-200 rounded-lg">
                        <p class="text-sm text-purple-800">
                            <strong>📋 Facturación de Cosecha:</strong> Los items deben estar vinculados a cosechas. Puedes añadir más cosechas si lo necesitas.
                        </p>
                    </div>
                @endif
                <div class="space-y-4">
                @forelse($items as $index => $item)
                        <div class="border-2 border-gray-200 rounded-lg p-4 bg-white hover:border-green-300 transition-colors shadow-sm" data-cy="invoice-item" data-cy-item-index="{{ $index }}">
                            <div class="flex justify-between items-start mb-3">
                                <div class="flex items-center gap-2">
                                    <h4 class="text-base font-bold text-gray-900">Item #{{ $index + 1 }}</h4>
                                    @if(isset($item['harvest_id']))
                                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-purple-100 text-purple-700">
                                            🍇 Cosecha
                                        </span>
                                    @endif
                                </div>
                                @if(count($items) > 1)
                                    <button 
                                        type="button" 
                                        wire:click="removeItem({{ $index }})" 
                                        class="text-red-600 hover:text-red-800 font-medium text-xs flex items-center gap-1"
                                        data-cy="remove-item"
                                        data-cy-item-index="{{ $index }}"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Eliminar
                                    </button>
                                @endif
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                                <!-- Nombre del concepto - Full width -->
                                <div class="md:col-span-12">
                                    <x-label class="text-xs">Nombre del concepto <span class="text-red-500">*</span></x-label>
                                    <x-input 
                                        wire:model="items.{{ $index }}.name" 
                                        placeholder="Ej: Uva Tempranillo, Servicio de recolección..."
                                        :error="$errors->first('items.' . $index . '.name')"
                                        class="text-sm"
                                        data-cy="item-name"
                                        data-cy-item-index="{{ $index }}"
                                    />
                                </div>
                                
                                <!-- Descripción y SKU -->
                                <div class="md:col-span-8">
                                    <x-label class="text-xs">Descripción</x-label>
                                    <x-textarea 
                                        wire:model="items.{{ $index }}.description" 
                                        rows="2"
                                        placeholder="Descripción detallada..."
                                        class="text-sm"
                                        data-cy="item-description"
                                        data-cy-item-index="{{ $index }}"
                                    />
                                </div>
                                <div class="md:col-span-4">
                                    <x-label class="text-xs">SKU / Código</x-label>
                                    <x-input 
                                        wire:model="items.{{ $index }}.sku" 
                                        placeholder="Código"
                                        class="text-sm"
                                        data-cy="item-sku"
                                        data-cy-item-index="{{ $index }}"
                                    />
                                    <div class="mt-2">
                                        <x-label class="text-xs">Tipo</x-label>
                                        <x-select wire:model="items.{{ $index }}.concept_type" class="text-sm" data-cy="item-concept-type" data-cy-item-index="{{ $index }}">
                                            <option value="harvest">Cosecha</option>
                                            <option value="service">Servicio</option>
                                            <option value="product">Producto</option>
                                            <option value="other">Otro</option>
                                        </x-select>
                                    </div>
                                </div>

                                <!-- Cantidad, Precio, Descuento, Impuesto -->
                                <div class="md:col-span-3">
                                    <x-label class="text-xs">Cantidad <span class="text-red-500">*</span></x-label>
                                    <x-input 
                                        wire:model.live="items.{{ $index }}.quantity" 
                                        type="number" 
                                        step="0.001"
                                        min="0.001"
                                        placeholder="0.000"
                                        :error="$errors->first('items.' . $index . '.quantity')"
                                        class="text-sm"
                                        data-cy="item-quantity"
                                        data-cy-item-index="{{ $index }}"
                                    />
                                </div>
                                <div class="md:col-span-3">
                                    <x-label class="text-xs">Precio €/ud <span class="text-red-500">*</span></x-label>
                                    <x-input 
                                        wire:model.live="items.{{ $index }}.unit_price" 
                                        type="number" 
                                        step="0.0001"
                                        min="0"
                                        placeholder="0.0000"
                                        :error="$errors->first('items.' . $index . '.unit_price')"
                                        class="text-sm"
                                        data-cy="item-unit-price"
                                        data-cy-item-index="{{ $index }}"
                                    />
                                </div>
                                <div class="md:col-span-3">
                                    <x-label class="text-xs">Descuento %</x-label>
                                    <x-input 
                                        wire:model.live="items.{{ $index }}.discount_percentage" 
                                        type="number" 
                                        step="0.01"
                                        min="0"
                                        max="100"
                                        placeholder="0.00"
                                        class="text-sm"
                                        data-cy="item-discount"
                                        data-cy-item-index="{{ $index }}"
                                    />
                                </div>
                                <div class="md:col-span-3">
                                    <x-label class="text-xs">Impuesto</x-label>
                                    <x-select wire:model.live="items.{{ $index }}.tax_id" class="text-sm" data-cy="item-tax-id" data-cy-item-index="{{ $index }}">
                                        <option value="">Sin impuesto</option>
                                        @foreach($availableTaxes as $tax)
                                            <option value="{{ $tax->id }}">{{ $tax->name }} ({{ number_format($tax->rate, 2) }}%)</option>
                                        @endforeach
                                    </x-select>
                                </div>
                            </div>

                            @php
                                $itemQuantity = (float)($item['quantity'] ?? 0);
                                $itemUnitPrice = (float)($item['unit_price'] ?? 0);
                                $itemDiscount = (float)($item['discount_percentage'] ?? 0);
                                $itemSubtotal = $itemQuantity * $itemUnitPrice;
                                $itemDiscountAmount = $itemSubtotal * ($itemDiscount / 100);
                                $itemSubtotalAfterDiscount = $itemSubtotal - $itemDiscountAmount;
                                
                                $selectedTax = $item['tax_id'] ? $availableTaxes->firstWhere('id', $item['tax_id']) : null;
                                $taxRate = $selectedTax ? $selectedTax->rate : 0;
                                $itemTaxAmount = $itemSubtotalAfterDiscount * ($taxRate / 100);
                                $itemTotal = $itemSubtotalAfterDiscount + $itemTaxAmount;
                            @endphp

                            <div class="mt-3 pt-3 border-t border-gray-200 bg-gray-50 -mx-4 -mb-4 px-4 py-2 rounded-b-lg">
                                <div class="flex flex-wrap gap-x-6 gap-y-1 text-xs">
                                    <div class="flex items-center gap-1">
                                        <span class="text-gray-500">Subtotal:</span>
                                        <span class="font-semibold text-gray-900">{{ number_format($itemSubtotal, 2) }} €</span>
                                    </div>
                                    @if($itemDiscount > 0)
                                        <div class="flex items-center gap-1">
                                            <span class="text-gray-500">Dto:</span>
                                            <span class="font-semibold text-red-600">-{{ number_format($itemDiscountAmount, 2) }} €</span>
                                        </div>
                                    @endif
                                    <div class="flex items-center gap-1">
                                        <span class="text-gray-500">Base:</span>
                                        <span class="font-semibold text-gray-900">{{ number_format($itemSubtotalAfterDiscount, 2) }} €</span>
                                    </div>
                                    @if($selectedTax)
                                        <div class="flex items-center gap-1">
                                            <span class="text-gray-500">IVA ({{ number_format($taxRate, 2) }}%):</span>
                                            <span class="font-semibold text-gray-900">{{ number_format($itemTaxAmount, 2) }} €</span>
                                        </div>
                                    @endif
                                    <div class="flex items-center gap-1 ml-auto">
                                        <span class="text-gray-500 font-semibold">Total:</span>
                                        <span class="text-base font-bold text-green-600">{{ number_format($itemTotal, 2) }} €</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 border-2 border-dashed border-gray-300 rounded-xl">
                            <p class="text-gray-500 mb-4">
                                No hay items en la factura.
                            </p>
                            <p class="text-sm text-gray-400">
                                Selecciona una cosecha arriba o añade un concepto manual
                            </p>
                        </div>
                    @endforelse

                    <!-- Botón para añadir conceptos manuales (no cosechas) -->
                    <div class="flex justify-center pt-4 border-t border-gray-200 mt-6">
                        <button 
                            type="button" 
                            wire:click="addItem" 
                            class="inline-flex items-center gap-2 px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition font-semibold shadow-sm"
                            data-cy="add-item-button"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Añadir Concepto Manual
                        </button>
                    </div>
                    <p class="text-xs text-center text-gray-500 mt-2">
                        Para servicios, productos u otros conceptos que no sean cosechas
                    </p>
                </div>
            </x-form-section>

            <x-form-section title="Observaciones" color="green">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-label for="observations">Observaciones generales</x-label>
                        <x-textarea wire:model="observations" id="observations" data-cy="observations" rows="3" placeholder="Notas internas..." />
                        @error('observations')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <x-label for="observations_invoice">Observaciones en factura</x-label>
                        <x-textarea wire:model="observations_invoice" id="observations_invoice" data-cy="observations-invoice" rows="3" placeholder="Texto que aparecerá en la factura..." />
                        @error('observations_invoice')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </x-form-section>

            <div class="flex justify-end gap-4">
                <x-button type="button" variant="ghost" href="{{ route('viticulturist.invoices.index') }}" data-cy="cancel-button">Cancelar</x-button>
                <x-button type="submit" variant="primary" data-cy="submit-button">Crear Factura</x-button>
            </div>
        </form>
    </x-form-card>
</div>
