<div>
    <x-agro.form-card
        title="Editar Factura {{ $invoice->invoice_number ? '#' . $invoice->invoice_number : '' }}"
        description="Modifica los datos de la factura"
        :back-url="route('viticulturist.invoices.index')"
    >
        <form wire:submit="update" class="space-y-8">
            @if($this->isLocked)
                <flux:callout variant="warning" class="mb-6">
                    <strong>Factura bloqueada:</strong> Esta factura esta {{ $invoice->delivery_status === 'delivered' ? 'entregada' : 'cancelada' }}. Solo puedes modificar el estado de pago.
                </flux:callout>
            @endif

            <x-agro.form-section title="Cliente">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:field>
                        <flux:label required>Cliente</flux:label>
                        <flux:select
                            wire:model.live="client_id"
                            id="client_id"
                            required
                            :disabled="$this->isLocked"
                        >
                            <option value="">Selecciona un cliente</option>
                            @foreach($availableClients as $client)
                                <option value="{{ $client->id }}">{{ $client->full_name }}</option>
                            @endforeach
                        </flux:select>
                        <flux:error name="client_id" />
                    </flux:field>
                    @if($client_id)
                        <flux:field>
                            <flux:label>Direccion de facturacion</flux:label>
                            <flux:select
                                wire:model="client_address_id"
                                id="client_address_id"
                                :disabled="$this->isLocked"
                            >
                                <option value="">Selecciona una direccion</option>
                                @foreach($availableAddresses as $address)
                                    <option value="{{ $address->id }}">
                                        {{ $address->full_address }}
                                        @if($address->is_default)
                                            (Por defecto)
                                        @endif
                                    </option>
                                @endforeach
                            </flux:select>
                            <flux:error name="client_address_id" />
                        </flux:field>
                    @endif
                </div>
            </x-agro.form-section>

            <x-agro.form-section title="Estados">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:field>
                        <flux:label required>Estado de Entrega</flux:label>
                        <flux:select
                            wire:model="delivery_status"
                            id="delivery_status"
                            required
                            :disabled="$this->isLocked"
                        >
                            <option value="pending">Pendiente</option>
                            <option value="in_transit">En Transito</option>
                            <option value="delivered">Entregado</option>
                            <option value="cancelled">Cancelado</option>
                        </flux:select>
                        <flux:error name="delivery_status" />
                        @if(!$this->isLocked)
                            <p class="mt-2 text-xs text-zinc-500">
                                Cambiar a "Entregado" convertira stock reservado en vendido
                            </p>
                        @endif
                    </flux:field>

                    <flux:field>
                        <flux:label required>Estado de Pago</flux:label>
                        <flux:select wire:model="payment_status" id="payment_status" required>
                            <option value="unpaid">No Pagado</option>
                            <option value="paid">Pagado</option>
                            <option value="overdue">Vencido</option>
                            <option value="refunded">Reembolsado</option>
                        </flux:select>
                        <flux:error name="payment_status" />
                        <p class="mt-2 text-xs text-zinc-500">
                            El estado de pago no afecta al stock
                        </p>
                    </flux:field>
                </div>
            </x-agro.form-section>

            @if(!$this->isLocked)
                <x-agro.form-section title="Cosechas para Facturar">
                    <flux:callout variant="info" class="mb-4">
                        <strong>Tip:</strong> Puedes anadir mas cosechas a esta factura seleccionandolas del dropdown.
                    </flux:callout>
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <flux:field>
                                <flux:label>Filtrar por Campana</flux:label>
                                <flux:select wire:model.live="selectedCampaign" id="selectedCampaign">
                                    <option value="">Todas las campanas</option>
                                    @foreach($campaigns as $campaign)
                                        <option value="{{ $campaign->id }}">{{ $campaign->year }}</option>
                                    @endforeach
                                </flux:select>
                            </flux:field>
                            <div class="md:col-span-2">
                                <flux:field>
                                    <flux:label>Selecciona una cosecha para anadir</flux:label>
                                    <flux:select
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
                                    </flux:select>
                                </flux:field>
                                <p class="mt-2 text-xs text-zinc-500">
                                    La cosecha se anadira automaticamente como item al seleccionarla
                                </p>
                            </div>
                        </div>
                        @if($availableHarvests->isEmpty())
                            <flux:callout variant="info">
                                <strong>No hay cosechas disponibles para facturar.</strong><br>
                                Todas las cosechas ya han sido facturadas o no hay cosechas registradas.
                            </flux:callout>
                        @endif
                    </div>
                </x-agro.form-section>
            @endif

            <x-agro.form-section title="Items de la Factura">
                <div class="space-y-4">
                    @forelse($items as $index => $item)
                        <div class="border-2 border-zinc-200 rounded-lg p-4 bg-white hover:border-agro-300 transition-colors shadow-xs">
                            <div class="flex justify-between items-start mb-3">
                                <div class="flex items-center gap-2">
                                    <h4 class="text-base font-bold text-zinc-900">Item #{{ $index + 1 }}</h4>
                                    @if(isset($item['harvest_id']) && $item['harvest_id'])
                                        <flux:badge color="purple" size="sm">Cosecha</flux:badge>
                                    @endif
                                </div>
                                @if(count($items) > 1)
                                    <flux:button
                                        type="button"
                                        wire:click="removeItem({{ $index }})"
                                        variant="danger"
                                        size="sm"
                                        icon="trash"
                                    >
                                        Eliminar
                                    </flux:button>
                                @endif
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div class="md:col-span-2">
                                    <flux:field>
                                        <flux:label>Nombre del concepto <span class="text-red-500">*</span></flux:label>
                                        <flux:input
                                            wire:model="items.{{ $index }}.name"
                                            placeholder="Ej: Uva Tempranillo, Servicio de recoleccion..."
                                            :disabled="$this->isLocked"
                                        />
                                        <flux:error name="items.{{ $index }}.name" />
                                    </flux:field>
                                </div>
                                <flux:field>
                                    <flux:label>Descripcion</flux:label>
                                    <flux:textarea
                                        wire:model="items.{{ $index }}.description"
                                        rows="2"
                                        placeholder="Descripcion detallada del item..."
                                        :disabled="$this->isLocked"
                                    />
                                </flux:field>
                                <flux:field>
                                    <flux:label>SKU / Codigo</flux:label>
                                    <flux:input
                                        wire:model="items.{{ $index }}.sku"
                                        placeholder="Codigo interno o SKU"
                                        :disabled="$this->isLocked"
                                    />
                                </flux:field>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-4">
                                <flux:field>
                                    <flux:label>Tipo de concepto</flux:label>
                                    <flux:select
                                        wire:model="items.{{ $index }}.concept_type"
                                        :disabled="$this->isLocked"
                                    >
                                        <option value="harvest">Cosecha</option>
                                        <option value="service">Servicio</option>
                                        <option value="product">Producto</option>
                                        <option value="other">Otro</option>
                                    </flux:select>
                                </flux:field>
                                <flux:field>
                                    <flux:label>Cantidad <span class="text-red-500">*</span></flux:label>
                                    <flux:input
                                        wire:model.live="items.{{ $index }}.quantity"
                                        type="number"
                                        step="0.001"
                                        min="0.001"
                                        placeholder="0.000"
                                        :disabled="$this->isLocked"
                                    />
                                    <flux:error name="items.{{ $index }}.quantity" />
                                </flux:field>
                                <flux:field>
                                    <flux:label>Precio unitario (€) <span class="text-red-500">*</span></flux:label>
                                    <flux:input
                                        wire:model.live="items.{{ $index }}.unit_price"
                                        type="number"
                                        step="0.0001"
                                        min="0"
                                        placeholder="0.0000"
                                        :disabled="$this->isLocked"
                                    />
                                    <flux:error name="items.{{ $index }}.unit_price" />
                                </flux:field>
                                <flux:field>
                                    <flux:label>Descuento (%)</flux:label>
                                    <flux:input
                                        wire:model.live="items.{{ $index }}.discount_percentage"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        max="100"
                                        placeholder="0.00"
                                        :disabled="$this->isLocked"
                                    />
                                </flux:field>
                                <flux:field>
                                    <flux:label>Impuesto</flux:label>
                                    <flux:select
                                        wire:model.live="items.{{ $index }}.tax_id"
                                        :disabled="$this->isLocked"
                                    >
                                        <option value="">Sin impuesto</option>
                                        @foreach($availableTaxes as $tax)
                                            <option value="{{ $tax->id }}">{{ $tax->name }} ({{ number_format($tax->rate, 2) }}%)</option>
                                        @endforeach
                                    </flux:select>
                                </flux:field>
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

                            <div class="mt-4 pt-4 border-t border-zinc-300">
                                <div class="grid grid-cols-2 md:grid-cols-5 gap-4 text-sm">
                                    <div>
                                        <p class="text-zinc-500">Subtotal:</p>
                                        <p class="font-semibold text-zinc-900">{{ number_format($itemSubtotal, 2) }} €</p>
                                    </div>
                                    @if($itemDiscount > 0)
                                        <div>
                                            <p class="text-zinc-500">Descuento:</p>
                                            <p class="font-semibold text-red-600">-{{ number_format($itemDiscountAmount, 2) }} €</p>
                                        </div>
                                    @endif
                                    <div>
                                        <p class="text-zinc-500">Base imponible:</p>
                                        <p class="font-semibold text-zinc-900">{{ number_format($itemSubtotalAfterDiscount, 2) }} €</p>
                                    </div>
                                    @if($selectedTax)
                                        <div>
                                            <p class="text-zinc-500">Impuesto ({{ number_format($taxRate, 2) }}%):</p>
                                            <p class="font-semibold text-zinc-900">{{ number_format($itemTaxAmount, 2) }} €</p>
                                        </div>
                                    @endif
                                    <div class="md:col-span-1">
                                        <p class="text-zinc-500 font-semibold">Total:</p>
                                        <p class="text-lg font-bold text-blue-600">{{ number_format($itemTotal, 2) }} €</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 border-2 border-dashed border-zinc-300 rounded-xl">
                            <p class="text-zinc-500 mb-4">
                                No hay items en la factura.
                            </p>
                            <p class="text-sm text-zinc-400">
                                Selecciona una cosecha arriba o anade un concepto manual
                            </p>
                        </div>
                    @endforelse

                    <!-- Boton para anadir conceptos manuales (no cosechas) -->
                    @if(!$this->isLocked)
                        <div class="flex justify-center pt-4 border-t border-zinc-200 mt-6">
                            <flux:button
                                type="button"
                                wire:click="addItem"
                                variant="outline"
                                icon="plus"
                            >
                                Anadir Concepto Manual
                            </flux:button>
                        </div>
                        <p class="text-xs text-center text-zinc-500 mt-2">
                            Para servicios, productos u otros conceptos que no sean cosechas
                        </p>
                    @endif
                </div>
            </x-agro.form-section>

            <x-agro.form-section title="Observaciones">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:field>
                        <flux:label>Observaciones generales</flux:label>
                        <flux:textarea
                            wire:model="observations"
                            id="observations"
                            rows="3"
                            placeholder="Notas internas..."
                            :disabled="$this->isLocked"
                        />
                        <flux:error name="observations" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Observaciones en factura</flux:label>
                        <flux:textarea
                            wire:model="observations_invoice"
                            id="observations_invoice"
                            rows="3"
                            placeholder="Texto que aparecera en la factura..."
                            :disabled="$this->isLocked"
                        />
                        <flux:error name="observations_invoice" />
                    </flux:field>
                </div>
            </x-agro.form-section>

            {{-- Resumen de Totales --}}
            <x-agro.form-section title="Resumen de Totales">
                <div class="bg-zinc-50 rounded-lg p-6">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <p class="text-sm text-zinc-500 mb-1">Subtotal:</p>
                            <p class="text-lg font-bold text-zinc-900">{{ number_format($this->subtotal, 2) }} €</p>
                        </div>
                        @if($this->discountAmount > 0)
                            <div>
                                <p class="text-sm text-zinc-500 mb-1">Descuento:</p>
                                <p class="text-lg font-bold text-red-600">-{{ number_format($this->discountAmount, 2) }} €</p>
                            </div>
                        @endif
                        <div>
                            <p class="text-sm text-zinc-500 mb-1">Impuestos:</p>
                            <p class="text-lg font-bold text-zinc-900">{{ number_format($this->taxAmount, 2) }} €</p>
                        </div>
                        <div class="md:col-span-1">
                            <p class="text-sm text-zinc-500 mb-1">Total:</p>
                            <p class="text-2xl font-bold text-blue-600">{{ number_format($this->totalAmount, 2) }} €</p>
                        </div>
                    </div>
                </div>
            </x-agro.form-section>

            <div class="flex justify-between items-center gap-4">
                <div>
                    @if($invoice->status === 'draft' && !$this->isLocked)
                        <flux:button
                            wire:click="openInvoiceModal"
                            variant="primary"
                            icon="paper-airplane"
                        >
                            Facturar/Enviar
                        </flux:button>
                    @endif
                </div>
                <div class="flex gap-4">
                    <flux:button
                        type="button"
                        variant="outline"
                        wire:click="cancel"
                        wire:confirm="Estas seguro de cancelar? Se restauraran todos los valores originales."
                    >
                        Cancelar
                    </flux:button>
                    <flux:button type="submit" variant="primary">Guardar Cambios</flux:button>
                </div>
            </div>
        </form>
    </x-agro.form-card>

    {{-- Modal para Facturar --}}
    @if($showInvoiceModal)
        <div class="fixed z-50 inset-0 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                {{-- Fondo oscuro --}}
                <div class="fixed inset-0 bg-black/50 transition-opacity" wire:click="closeInvoiceModal"></div>

                {{-- Modal --}}
                <div class="relative bg-white rounded-xl border border-zinc-200 shadow-xl max-w-md w-full p-6 z-10" wire:click.stop>
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-2xl font-bold text-zinc-900">Facturar Factura</h3>
                        <flux:button
                            wire:click="closeInvoiceModal"
                            variant="ghost"
                            size="sm"
                            icon="x-mark"
                        />
                    </div>

                    <p class="text-zinc-600 mb-6">
                        Completa los datos para facturar esta factura. Una vez facturada, el codigo no se podra modificar.
                    </p>

                    <div class="space-y-4">
                        {{-- Fecha de Factura --}}
                        <flux:field>
                            <flux:label required>Fecha de Factura</flux:label>
                            <flux:input
                                wire:model="invoice_date_modal"
                                id="invoice_date_modal"
                                type="date"
                                required
                            />
                            <flux:error name="invoice_date_modal" />
                        </flux:field>

                        {{-- Codigo de Factura --}}
                        <flux:field>
                            <flux:label required>Codigo de Factura</flux:label>
                            <flux:input
                                wire:model="invoice_number_modal"
                                id="invoice_number_modal"
                                type="text"
                                placeholder="Ej: FAC-2025-0001"
                                required
                            />
                            <flux:error name="invoice_number_modal" />
                        </flux:field>
                        <p class="text-xs text-zinc-500">
                            El codigo se genera automaticamente. Puedes modificarlo si lo necesitas.
                        </p>

                        {{-- Advertencia --}}
                        <flux:callout variant="warning">
                            <strong>Importante:</strong> Al facturar, el stock reservado se convertira en vendido y el codigo de factura quedara bloqueado.
                        </flux:callout>
                    </div>

                    {{-- Botones --}}
                    <div class="flex justify-end gap-4 mt-6">
                        <flux:button
                            wire:click="closeInvoiceModal"
                            variant="outline"
                        >
                            Cancelar
                        </flux:button>
                        <flux:button
                            wire:click="markAsSent"
                            variant="primary"
                            icon="paper-airplane"
                        >
                            Facturar
                        </flux:button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
