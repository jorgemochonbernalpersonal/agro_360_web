<div>
    @php
        $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>';
    @endphp
    <x-form-card
        title="Editar Factura {{ $invoice->invoice_number ? '#' . $invoice->invoice_number : '' }}"
        description="Modifica los datos de la factura"
        :icon="$icon"
        icon-color="from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]"
        :back-url="route('viticulturist.invoices.index')"
    >
        <form wire:submit="update" class="space-y-8">
            @if($this->isLocked)
                <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded-r-lg mb-6">
                    <p class="text-sm text-yellow-800">
                        <strong>🔒 Factura bloqueada:</strong> Esta factura está {{ $invoice->delivery_status === 'delivered' ? 'entregada' : 'cancelada' }}. Solo puedes modificar el estado de pago.
                    </p>
                </div>
            @endif

            <x-form-section title="Cliente" color="green">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-label for="client_id" required>Cliente</x-label>
                        <x-select 
                            wire:model.live="client_id" 
                            id="client_id" 
                            required
                            :disabled="$this->isLocked"
                            class="{{ $this->isLocked ? 'bg-gray-100 cursor-not-allowed' : '' }}"
                        >
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
                            <x-select 
                                wire:model="client_address_id" 
                                id="client_address_id"
                                :disabled="$this->isLocked"
                                class="{{ $this->isLocked ? 'bg-gray-100 cursor-not-allowed' : '' }}"
                            >
                                <option value="">Selecciona una dirección</option>
                                @foreach($availableAddresses as $address)
                                    <option value="{{ $address->id }}">
                                        {{ $address->full_address }}
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

            <x-form-section title="Estados" color="orange">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-label for="delivery_status" required>Estado de Entrega</x-label>
                        <x-select 
                            wire:model="delivery_status" 
                            id="delivery_status" 
                            required
                            :disabled="$this->isLocked"
                            class="{{ $this->isLocked ? 'bg-gray-100 cursor-not-allowed' : '' }}"
                        >
                            <option value="pending">⏳ Pendiente</option>
                            <option value="in_transit">🚚 En Tránsito</option>
                            <option value="delivered">✅ Entregado</option>
                            <option value="cancelled">❌ Cancelado</option>
                        </x-select>
                        @error('delivery_status')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        @if(!$this->isLocked)
                            <p class="mt-2 text-xs text-gray-500">
                                ⚠️ Cambiar a "Entregado" convertirá stock reservado en vendido
                            </p>
                        @endif
                    </div>

                    <div>
                        <x-label for="payment_status" required>Estado de Pago</x-label>
                        <x-select wire:model="payment_status" id="payment_status" required>
                            <option value="unpaid">⏳ No Pagado</option>
                            <option value="paid">✅ Pagado</option>
                            <option value="overdue">⚠️ Vencido</option>
                            <option value="refunded">↩️ Reembolsado</option>
                        </x-select>
                        @error('payment_status')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-2 text-xs text-gray-500">
                            ℹ️ El estado de pago no afecta al stock
                        </p>
                    </div>
                </div>
            </x-form-section>

            @if(!$this->isLocked)
                <x-form-section title="Cosechas para Facturar" color="purple">
                    <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <p class="text-sm text-blue-800">
                            <strong>💡 Tip:</strong> Puedes añadir más cosechas a esta factura seleccionándolas del dropdown.
                        </p>
                    </div>
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
                                <x-label for="selectedHarvestId">Selecciona una cosecha para añadir</x-label>
                                <x-select 
                                    wire:model.live="selectedHarvestId" 
                                    wire:change="addHarvestToInvoice"
                                    id="selectedHarvestId"
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
            @endif

            <x-form-section title="Items de la Factura" color="green">
                <div class="space-y-4">
                    @forelse($items as $index => $item)
                        <div class="border-2 border-gray-200 rounded-lg p-4 bg-white hover:border-green-300 transition-colors shadow-sm">
                            <div class="flex justify-between items-start mb-3">
                                <div class="flex items-center gap-2">
                                    <h4 class="text-base font-bold text-gray-900">Item #{{ $index + 1 }}</h4>
                                    @if(isset($item['harvest_id']) && $item['harvest_id'])
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
                                        :disabled="$this->isLocked"
                                        class="{{ $this->isLocked ? 'bg-gray-100 cursor-not-allowed' : '' }}"
                                    />
                                </div>
                                <div>
                                    <x-label>Descripción</x-label>
                                    <x-textarea 
                                        wire:model="items.{{ $index }}.description" 
                                        rows="2"
                                        placeholder="Descripción detallada del item..."
                                        :disabled="$this->isLocked"
                                        class="{{ $this->isLocked ? 'bg-gray-100 cursor-not-allowed' : '' }}"
                                    />
                                </div>
                                <div>
                                    <x-label>SKU / Código</x-label>
                                    <x-input 
                                        wire:model="items.{{ $index }}.sku" 
                                        placeholder="Código interno o SKU"
                                        :disabled="$this->isLocked"
                                        class="{{ $this->isLocked ? 'bg-gray-100 cursor-not-allowed' : '' }}"
                                    />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-4">
                                <div>
                                    <x-label>Tipo de concepto</x-label>
                                    <x-select 
                                        wire:model="items.{{ $index }}.concept_type"
                                        :disabled="$this->isLocked"
                                        class="{{ $this->isLocked ? 'bg-gray-100 cursor-not-allowed' : '' }}"
                                    >
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
                                        :disabled="$this->isLocked"
                                        class="{{ $this->isLocked ? 'bg-gray-100 cursor-not-allowed' : '' }}"
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
                                        :disabled="$this->isLocked"
                                        class="{{ $this->isLocked ? 'bg-gray-100 cursor-not-allowed' : '' }}"
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
                                        :disabled="$this->isLocked"
                                        class="{{ $this->isLocked ? 'bg-gray-100 cursor-not-allowed' : '' }}"
                                    />
                                </div>
                                <div>
                                    <x-label>Impuesto</x-label>
                                    <x-select 
                                        wire:model.live="items.{{ $index }}.tax_id"
                                        :disabled="$this->isLocked"
                                        class="{{ $this->isLocked ? 'bg-gray-100 cursor-not-allowed' : '' }}"
                                    >
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
                                No hay items en la factura.
                            </p>
                            <p class="text-sm text-gray-400">
                                Selecciona una cosecha arriba o añade un concepto manual
                            </p>
                        </div>
                    @endforelse

                    <!-- Botón para añadir conceptos manuales (no cosechas) -->
                    @if(!$this->isLocked)
                        <div class="flex justify-center pt-4 border-t border-gray-200 mt-6">
                            <button 
                                type="button" 
                                wire:click="addItem" 
                                class="inline-flex items-center gap-2 px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition font-semibold shadow-sm"
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
                    @endif
                </div>
            </x-form-section>

            <x-form-section title="Observaciones" color="green">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-label for="observations">Observaciones generales</x-label>
                        <x-textarea 
                            wire:model="observations" 
                            id="observations" 
                            rows="3" 
                            placeholder="Notas internas..."
                            :disabled="$this->isLocked"
                            class="{{ $this->isLocked ? 'bg-gray-100 cursor-not-allowed' : '' }}"
                        />
                        @error('observations')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <x-label for="observations_invoice">Observaciones en factura</x-label>
                        <x-textarea 
                            wire:model="observations_invoice" 
                            id="observations_invoice" 
                            rows="3" 
                            placeholder="Texto que aparecerá en la factura..."
                            :disabled="$this->isLocked"
                            class="{{ $this->isLocked ? 'bg-gray-100 cursor-not-allowed' : '' }}"
                        />
                        @error('observations_invoice')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </x-form-section>

            <div class="flex justify-between items-center gap-4">
                <div>
                    @if($invoice->status === 'draft' && !$this->isLocked)
                        <x-button 
                            wire:click="openInvoiceModal"
                            variant="primary"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                            Facturar/Enviar
                        </x-button>
                    @endif
                </div>
                <div class="flex gap-4">
                    <x-button type="button" variant="ghost" href="{{ route('viticulturist.invoices.index') }}">Cancelar</x-button>
                    <x-button type="submit" variant="primary">Guardar Cambios</x-button>
                </div>
            </div>
        </form>
    </x-form-card>

    {{-- Modal para Facturar --}}
    @if($showInvoiceModal)
        <div class="fixed z-50 inset-0 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                {{-- Fondo oscuro --}}
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeInvoiceModal"></div>
                
                {{-- Modal --}}
                <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 z-10" wire:click.stop>
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-2xl font-bold text-gray-900">📤 Facturar Factura</h3>
                        <button 
                            wire:click="closeInvoiceModal"
                            class="text-gray-500 hover:text-gray-700"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <p class="text-gray-600 mb-6">
                        Completa los datos para facturar esta factura. Una vez facturada, el código no se podrá modificar.
                    </p>

                    <div class="space-y-4">
                        {{-- Fecha de Factura --}}
                        <div>
                            <x-label for="invoice_date_modal" required>Fecha de Factura</x-label>
                            <x-input 
                                wire:model="invoice_date_modal" 
                                id="invoice_date_modal" 
                                type="date" 
                                required 
                            />
                            @error('invoice_date_modal')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Código de Factura --}}
                        <div>
                            <x-label for="invoice_number_modal" required>Código de Factura</x-label>
                            <x-input 
                                wire:model="invoice_number_modal" 
                                id="invoice_number_modal" 
                                type="text" 
                                placeholder="Ej: FAC-2025-0001"
                                required
                            />
                            @error('invoice_number_modal')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-2 text-xs text-gray-500">
                                💡 El código se genera automáticamente. Puedes modificarlo si lo necesitas.
                            </p>
                        </div>

                        {{-- Advertencia --}}
                        <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded-r-lg">
                            <p class="text-sm text-yellow-800">
                                <strong>⚠️ Importante:</strong> Al facturar, el stock reservado se convertirá en vendido y el código de factura quedará bloqueado.
                            </p>
                        </div>
                    </div>

                    {{-- Botones --}}
                    <div class="flex justify-end gap-4 mt-6">
                        <x-button 
                            wire:click="closeInvoiceModal" 
                            variant="ghost"
                        >
                            Cancelar
                        </x-button>
                        <x-button 
                            wire:click="markAsSent" 
                            variant="primary"
                        >
                            📤 Facturar
                        </x-button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
