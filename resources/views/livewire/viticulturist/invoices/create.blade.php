<div>
    <x-form-card
        title="Nueva Factura"
        description="Crea una nueva factura"
        icon="🧾"
        icon-color="from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]"
        :back-url="route('viticulturist.invoices.index')"
    >
        <form wire:submit="save" class="space-y-8">
            <x-form-section title="Cliente" color="green">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-label for="client_id" required>Cliente</x-label>
                        <x-select wire:model.live="client_id" id="client_id" required>
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
                            <x-select wire:model="client_address_id" id="client_address_id">
                                <option value="">Selecciona una dirección</option>
                                @foreach($availableAddresses as $address)
                                    <option value="{{ $address->id }}">
                                        {{ $address->name ?? 'Dirección #' . $address->id }}
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

            <x-form-section title="Fechas" color="green">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-label for="invoice_date" required>Fecha de factura</x-label>
                        <x-input wire:model="invoice_date" id="invoice_date" type="date" required />
                        @error('invoice_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <x-label for="due_date">Fecha de vencimiento</x-label>
                        <x-input wire:model="due_date" id="due_date" type="date" />
                        @error('due_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </x-form-section>

            <x-form-section title="Código de Albarán" color="blue">
                <div class="max-w-md">
                    <x-label for="delivery_note_code" required>Código de Albarán</x-label>
                    <x-input 
                        wire:model.live="delivery_note_code" 
                        id="delivery_note_code" 
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

            <x-form-section title="Seleccionar Cosecha para Facturar" color="purple">
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
                            <x-select wire:model.live="selectedCampaign" id="selectedCampaign">
                                <option value="">Todas las campañas</option>
                                @foreach($campaigns as $campaign)
                                    <option value="{{ $campaign->id }}">{{ $campaign->year }}</option>
                                @endforeach
                            </x-select>
                        </div>
                        <div class="md:col-span-2">
                            <x-label for="selectedHarvestId" :required="$fromHarvestRoute">Cosecha</x-label>
                            <div class="flex gap-2">
                                <x-select wire:model="selectedHarvestId" id="selectedHarvestId" class="flex-1" :required="$fromHarvestRoute">
                                    <option value="">Selecciona una cosecha sin facturar</option>
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
                                <x-button 
                                    type="button" 
                                    wire:click="addHarvestToInvoice"
                                    variant="primary"
                                    :disabled="!$selectedHarvestId"
                                >
                                    Añadir
                                </x-button>
                            </div>
                            @if($fromHarvestRoute && $errors->has('items'))
                                <p class="mt-1 text-sm text-red-600 font-medium">{{ $errors->first('items') }}</p>
                            @endif
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
                        <div class="border-2 border-gray-200 rounded-xl p-6 bg-gray-50 hover:border-blue-300 transition-colors">
                            <div class="flex justify-between items-center mb-4">
                                <div class="flex items-center gap-2">
                                    <h4 class="text-lg font-semibold text-gray-900">Item #{{ $index + 1 }}</h4>
                                    @if(isset($item['harvest_id']))
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                                            🍇 Cosecha
                                        </span>
                                    @endif
                                </div>
                                @if(count($items) > 1)
                                    <button 
                                        type="button" 
                                        wire:click="removeItem({{ $index }})" 
                                        class="text-red-600 hover:text-red-800 font-medium text-sm flex items-center gap-1"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Eliminar
                                    </button>
                                @endif
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div class="md:col-span-2">
                                    <x-label>Nombre del concepto <span class="text-red-500">*</span></x-label>
                                    <x-input 
                                        wire:model="items.{{ $index }}.name" 
                                        placeholder="Ej: Uva Tempranillo, Servicio de recolección..."
                                        :error="$errors->first('items.' . $index . '.name')"
                                    />
                                </div>
                                <div>
                                    <x-label>Descripción</x-label>
                                    <x-textarea 
                                        wire:model="items.{{ $index }}.description" 
                                        rows="2"
                                        placeholder="Descripción detallada del item..."
                                    />
                                </div>
                                <div>
                                    <x-label>SKU / Código</x-label>
                                    <x-input 
                                        wire:model="items.{{ $index }}.sku" 
                                        placeholder="Código interno o SKU"
                                    />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-4">
                                <div>
                                    <x-label>Tipo de concepto</x-label>
                                    <x-select wire:model="items.{{ $index }}.concept_type">
                                        <option value="harvest">Cosecha</option>
                                        <option value="service">Servicio</option>
                                        <option value="product">Producto</option>
                                        <option value="other">Otro</option>
                                    </x-select>
                                </div>
                                <div>
                                    <x-label>Cantidad <span class="text-red-500">*</span></x-label>
                                    <x-input 
                                        wire:model.live="items.{{ $index }}.quantity" 
                                        type="number" 
                                        step="0.001"
                                        min="0.001"
                                        placeholder="0.000"
                                        :error="$errors->first('items.' . $index . '.quantity')"
                                    />
                                </div>
                                <div>
                                    <x-label>Precio unitario (€) <span class="text-red-500">*</span></x-label>
                                    <x-input 
                                        wire:model.live="items.{{ $index }}.unit_price" 
                                        type="number" 
                                        step="0.0001"
                                        min="0"
                                        placeholder="0.0000"
                                        :error="$errors->first('items.' . $index . '.unit_price')"
                                    />
                                </div>
                                <div>
                                    <x-label>Descuento (%)</x-label>
                                    <x-input 
                                        wire:model.live="items.{{ $index }}.discount_percentage" 
                                        type="number" 
                                        step="0.01"
                                        min="0"
                                        max="100"
                                        placeholder="0.00"
                                    />
                                </div>
                                <div>
                                    <x-label>Impuesto</x-label>
                                    <x-select wire:model.live="items.{{ $index }}.tax_id">
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

                            <div class="mt-4 pt-4 border-t border-gray-300">
                                <div class="grid grid-cols-2 md:grid-cols-5 gap-4 text-sm">
                                    <div>
                                        <p class="text-gray-500">Subtotal:</p>
                                        <p class="font-semibold text-gray-900">{{ number_format($itemSubtotal, 2) }} €</p>
                                    </div>
                                    @if($itemDiscount > 0)
                                        <div>
                                            <p class="text-gray-500">Descuento:</p>
                                            <p class="font-semibold text-red-600">-{{ number_format($itemDiscountAmount, 2) }} €</p>
                                        </div>
                                    @endif
                                    <div>
                                        <p class="text-gray-500">Base imponible:</p>
                                        <p class="font-semibold text-gray-900">{{ number_format($itemSubtotalAfterDiscount, 2) }} €</p>
                                    </div>
                                    @if($selectedTax)
                                        <div>
                                            <p class="text-gray-500">Impuesto ({{ number_format($taxRate, 2) }}%):</p>
                                            <p class="font-semibold text-gray-900">{{ number_format($itemTaxAmount, 2) }} €</p>
                                        </div>
                                    @endif
                                    <div class="md:col-span-1">
                                        <p class="text-gray-500 font-semibold">Total:</p>
                                        <p class="text-lg font-bold text-blue-600">{{ number_format($itemTotal, 2) }} €</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 border-2 border-dashed border-gray-300 rounded-xl">
                            <p class="text-gray-500 mb-4">
                                @if($fromHarvestRoute)
                                    No hay cosechas seleccionadas. Por favor, selecciona una cosecha arriba.
                                @else
                                    No hay items en la factura
                                @endif
                            </p>
                            @if(!$fromHarvestRoute)
                                <button type="button" wire:click="addItem" class="text-blue-600 hover:text-blue-800 font-medium">
                                    + Añadir primer item
                                </button>
                            @endif
                        </div>
                    @endforelse

                    @if(!$fromHarvestRoute)
                        <div class="flex justify-center pt-4">
                            <button 
                                type="button" 
                                wire:click="addItem" 
                                class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Añadir Item
                            </button>
                        </div>
                    @endif
                </div>
            </x-form-section>

            <x-form-section title="Observaciones" color="green">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-label for="observations">Observaciones generales</x-label>
                        <x-textarea wire:model="observations" id="observations" rows="3" placeholder="Notas internas..." />
                        @error('observations')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <x-label for="observations_invoice">Observaciones en factura</x-label>
                        <x-textarea wire:model="observations_invoice" id="observations_invoice" rows="3" placeholder="Texto que aparecerá en la factura..." />
                        @error('observations_invoice')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </x-form-section>

            <div class="flex justify-end gap-4">
                <x-button type="button" variant="ghost" href="{{ route('viticulturist.invoices.index') }}">Cancelar</x-button>
                <x-button type="submit" variant="primary">Crear Factura</x-button>
            </div>
        </form>
    </x-form-card>
</div>
