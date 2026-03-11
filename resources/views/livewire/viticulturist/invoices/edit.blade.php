<div class="space-y-6 animate-fade-in">

    {{-- Cabecera --}}
    <x-agro.page-header
        title="Albarán {{ $invoice->delivery_note_code }}"
        :description="$invoice->invoice_number ? 'Factura ' . $invoice->invoice_number : 'Borrador — pendiente de facturar'"
    >
        <x-slot:actions>
            <flux:button href="{{ route('viticulturist.invoices.index') }}" variant="outline" icon="arrow-left">
                Volver
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Aviso estado bloqueado --}}
    @if($this->isLocked)
        <flux:callout variant="warning">
            <strong>Albarán bloqueado:</strong>
            {{ $invoice->delivery_status === 'delivered' ? 'Ya entregado.' : 'Cancelado.' }}
            Solo puedes modificar el estado de pago.
        </flux:callout>
    @endif

    {{-- Aviso factura enviada --}}
    @if($invoice->status === 'sent' && !$this->isLocked)
        <flux:callout variant="info">
            <strong>Factura emitida.</strong>
            El número de factura y el código de albarán están bloqueados. Para anular necesitas una factura rectificativa.
        </flux:callout>
    @endif

    <x-agro.card>
        <form wire:submit="update" class="space-y-8">

            {{-- Documento: albarán + factura + fechas --}}
            <x-agro.form-section title="Documento">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    {{-- Código albarán (siempre readonly) --}}
                    <flux:field>
                        <flux:label>Código de albarán</flux:label>
                        <flux:input
                            value="{{ $delivery_note_code }}"
                            disabled
                            class="bg-zinc-100 font-mono font-semibold cursor-not-allowed"
                        />
                    </flux:field>

                    {{-- Número de factura (readonly si ya está facturada) --}}
                    <flux:field>
                        <flux:label>Número de factura</flux:label>
                        @if($invoice->status === 'draft')
                            <flux:input
                                value="{{ $invoice_number ?: '—  (se asignará al facturar)' }}"
                                disabled
                                class="bg-zinc-50 font-mono text-zinc-400 cursor-not-allowed"
                            />
                        @else
                            <flux:input
                                value="{{ $invoice_number }}"
                                disabled
                                class="bg-zinc-100 font-mono font-semibold cursor-not-allowed"
                            />
                        @endif
                    </flux:field>

                    {{-- Fecha albarán --}}
                    <flux:field>
                        <flux:label>Fecha albarán</flux:label>
                        <flux:input
                            wire:model="delivery_note_date"
                            type="date"
                            :disabled="$this->isLocked || $invoice->status === 'sent'"
                        />
                        <flux:error name="delivery_note_date" />
                    </flux:field>

                    {{-- Fecha factura --}}
                    <flux:field>
                        <flux:label>Fecha factura</flux:label>
                        <flux:input
                            wire:model="invoice_date"
                            type="date"
                            :disabled="$this->isLocked"
                        />
                        <flux:error name="invoice_date" />
                    </flux:field>
                </div>
            </x-agro.form-section>

            {{-- Cliente --}}
            <x-agro.form-section title="Cliente">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:field>
                        <flux:label required>Cliente</flux:label>
                        <flux:select
                            wire:model.live="client_id"
                            id="client_id"
                            required
                            :disabled="$this->isLocked || $invoice->status === 'sent'"
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
                            <flux:label>Dirección de facturación</flux:label>
                            <flux:select
                                wire:model="client_address_id"
                                :disabled="$this->isLocked || $invoice->status === 'sent'"
                            >
                                <option value="">Selecciona una dirección</option>
                                @foreach($availableAddresses as $address)
                                    <option value="{{ $address->id }}">
                                        {{ $address->full_address }}
                                        @if($address->is_default) (Por defecto) @endif
                                    </option>
                                @endforeach
                            </flux:select>
                            <flux:error name="client_address_id" />
                        </flux:field>
                    @endif
                </div>
            </x-agro.form-section>

            {{-- Estados --}}
            <x-agro.form-section title="Estados">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:field>
                        <flux:label required>Estado de entrega</flux:label>
                        <flux:select
                            wire:model="delivery_status"
                            required
                            :disabled="$this->isLocked"
                        >
                            <option value="pending">Pendiente</option>
                            <option value="in_transit">En tránsito</option>
                            <option value="delivered">Entregado</option>
                            <option value="cancelled">Cancelado</option>
                        </flux:select>
                        <flux:error name="delivery_status" />
                        <p class="mt-1 text-xs text-zinc-400">
                            Solo informativo — no afecta al stock ni al número de factura.
                        </p>
                    </flux:field>

                    <flux:field>
                        <flux:label required>Estado de pago</flux:label>
                        <flux:select wire:model="payment_status" required>
                            <option value="unpaid">No pagado</option>
                            <option value="paid">Pagado</option>
                            <option value="overdue">Vencido</option>
                            <option value="refunded">Reembolsado</option>
                        </flux:select>
                        <flux:error name="payment_status" />
                        <p class="mt-1 text-xs text-zinc-400">
                            El estado de pago no afecta al stock.
                        </p>
                    </flux:field>
                </div>
            </x-agro.form-section>

            {{-- Añadir cosechas (solo si no está bloqueada ni facturada) --}}
            @if(!$this->isLocked && $invoice->status === 'draft')
                <x-agro.form-section title="Añadir cosecha">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <flux:field>
                            <flux:label>Filtrar por campaña</flux:label>
                            <flux:select wire:model.live="selectedCampaign">
                                <option value="">Todas las campañas</option>
                                @foreach($campaigns as $campaign)
                                    <option value="{{ $campaign->id }}">{{ $campaign->year }}</option>
                                @endforeach
                            </flux:select>
                        </flux:field>

                        <div class="md:col-span-2">
                            <flux:field>
                                <flux:label>Cosecha</flux:label>
                                <flux:select
                                    wire:model.live="selectedHarvestId"
                                    wire:change="addHarvestToInvoice"
                                >
                                    <option value="">-- Selecciona una cosecha con stock disponible --</option>
                                    @foreach($availableHarvests as $harvest)
                                        <option value="{{ $harvest->id }}">
                                            {{ $harvest->plotPlanting->grapeVariety->name ?? 'Sin variedad' }} –
                                            {{ $harvest->activity->plot->name ?? 'Sin parcela' }} –
                                            {{ $harvest->harvest_start_date->format('d/m/Y') }}
                                            @if($harvest->container)
                                                [{{ $harvest->container->name }}]
                                            @endif
                                            – Disp: {{ number_format($harvest->available_qty_computed, 0) }} kg
                                            @if($harvest->price_per_kg)
                                                ({{ number_format($harvest->price_per_kg, 3) }} €/kg)
                                            @endif
                                        </option>
                                    @endforeach
                                </flux:select>
                                <p class="mt-1 text-xs text-zinc-400">Solo cosechas con stock disponible. La cantidad se puede ajustar en las líneas.</p>
                            </flux:field>
                        </div>
                    </div>

                    @if($availableHarvests->isEmpty())
                        <flux:callout variant="info" class="mt-4">
                            No hay cosechas con stock disponible para añadir.
                        </flux:callout>
                    @endif
                </x-agro.form-section>
            @endif

            {{-- Líneas --}}
            <x-agro.form-section title="Líneas del albarán">
                <div class="space-y-4">
                    @forelse($items as $index => $item)
                        @php
                            $isHarvestItem = isset($item['harvest_id']) && $item['harvest_id'];
                            $availableQty  = isset($item['available_qty']) ? (float)$item['available_qty'] : null;
                            $totalWeight   = isset($item['total_weight'])  ? (float)$item['total_weight']  : null;
                            $locked        = $this->isLocked || $invoice->status === 'sent';

                            $itemQty      = (float)($item['quantity'] ?? 0);
                            $itemPrice    = (float)($item['unit_price'] ?? 0);
                            $itemDiscount = (float)($item['discount_percentage'] ?? 0);
                            $itemSubtotal = $itemQty * $itemPrice;
                            $itemDiscAmt  = $itemSubtotal * ($itemDiscount / 100);
                            $itemBase     = $itemSubtotal - $itemDiscAmt;
                            $selectedTax  = $item['tax_id'] ? $availableTaxes->firstWhere('id', $item['tax_id']) : null;
                            $taxRate      = $selectedTax ? $selectedTax->rate : 0;
                            $itemTaxAmt   = $itemBase * ($taxRate / 100);
                            $itemTotal    = $itemBase + $itemTaxAmt;
                        @endphp

                        <div class="border-2 border-zinc-200 rounded-lg p-4 bg-white hover:border-agro-300 transition-colors shadow-xs">

                            {{-- Cabecera línea --}}
                            <div class="flex justify-between items-start mb-3">
                                <div class="flex items-center gap-2">
                                    <h4 class="text-base font-bold text-zinc-900">Línea #{{ $index + 1 }}</h4>
                                    @if($isHarvestItem)
                                        <flux:badge color="purple" size="sm">Cosecha</flux:badge>
                                    @endif
                                </div>
                                @if(!$locked && count($items) > 1)
                                    <flux:button
                                        type="button"
                                        wire:click="removeItem({{ $index }})"
                                        variant="danger"
                                        size="sm"
                                        icon="trash"
                                    >Eliminar</flux:button>
                                @endif
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                                {{-- Nombre --}}
                                <div class="md:col-span-12">
                                    <flux:field>
                                        <flux:label class="text-xs">Concepto <span class="text-red-500">*</span></flux:label>
                                        <flux:input
                                            wire:model="items.{{ $index }}.name"
                                            placeholder="Ej: Uva Tempranillo..."
                                            class="text-sm"
                                            :disabled="$locked"
                                        />
                                        <flux:error name="items.{{ $index }}.name" />
                                    </flux:field>
                                </div>

                                {{-- Descripción --}}
                                <div class="md:col-span-8">
                                    <flux:field>
                                        <flux:label class="text-xs">Descripción</flux:label>
                                        <flux:textarea
                                            wire:model="items.{{ $index }}.description"
                                            rows="2"
                                            class="text-sm"
                                            :disabled="$locked"
                                        />
                                    </flux:field>
                                </div>

                                {{-- SKU y tipo --}}
                                <div class="md:col-span-4 space-y-2">
                                    <flux:field>
                                        <flux:label class="text-xs">SKU</flux:label>
                                        <flux:input wire:model="items.{{ $index }}.sku" class="text-sm" :disabled="$locked" />
                                    </flux:field>
                                    <flux:field>
                                        <flux:label class="text-xs">Tipo</flux:label>
                                        <flux:select wire:model="items.{{ $index }}.concept_type" class="text-sm" :disabled="$locked">
                                            <option value="harvest">Cosecha</option>
                                            <option value="service">Servicio</option>
                                            <option value="product">Producto</option>
                                            <option value="other">Otro</option>
                                        </flux:select>
                                    </flux:field>
                                </div>

                                {{-- Cantidad + Unidad --}}
                                <div class="md:col-span-3">
                                    <flux:field>
                                        <flux:label class="text-xs">Cantidad <span class="text-red-500">*</span></flux:label>
                                        <flux:input
                                            wire:model.live="items.{{ $index }}.quantity"
                                            type="number"
                                            step="0.001"
                                            min="0.001"
                                            @if($isHarvestItem && $availableQty !== null && !$locked)
                                                max="{{ $availableQty }}"
                                            @endif
                                            class="text-sm"
                                            :disabled="$locked"
                                        />
                                        <flux:error name="items.{{ $index }}.quantity" />

                                        @if($isHarvestItem && $availableQty !== null && !$locked)
                                            @php $exceedsStock = $itemQty > $availableQty; @endphp
                                            <p class="mt-1 text-xs {{ $exceedsStock ? 'text-red-600 font-semibold' : 'text-zinc-500' }}">
                                                Máx. disponible: {{ number_format($availableQty, 3) }} kg
                                                @if($totalWeight && $totalWeight != $availableQty)
                                                    / Total cosecha: {{ number_format($totalWeight, 3) }} kg
                                                @endif
                                                @if($exceedsStock) — ¡Supera el stock! @endif
                                            </p>
                                        @endif
                                    </flux:field>
                                    <flux:field class="mt-2">
                                        <flux:label class="text-xs">Unidad</flux:label>
                                        <flux:select wire:model="items.{{ $index }}.unit" class="text-sm" :disabled="$locked || $isHarvestItem">
                                            <option value="kg">kg</option>
                                            <option value="litros">litros</option>
                                            <option value="centilitros">centilitros</option>
                                            <option value="botellas">botellas</option>
                                            <option value="cajas">cajas</option>
                                            <option value="unidades">unidades</option>
                                        </flux:select>
                                    </flux:field>
                                </div>

                                {{-- Precio --}}
                                <div class="md:col-span-3">
                                    <flux:field>
                                        <flux:label class="text-xs">Precio / unidad <span class="text-red-500">*</span></flux:label>
                                        <flux:input
                                            wire:model.live="items.{{ $index }}.unit_price"
                                            type="number"
                                            step="0.0001"
                                            min="0"
                                            class="text-sm"
                                            :disabled="$locked"
                                        />
                                        <flux:error name="items.{{ $index }}.unit_price" />
                                    </flux:field>
                                </div>

                                {{-- Descuento --}}
                                <div class="md:col-span-3">
                                    <flux:field>
                                        <flux:label class="text-xs">Descuento %</flux:label>
                                        <flux:input
                                            wire:model.live="items.{{ $index }}.discount_percentage"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            max="100"
                                            class="text-sm"
                                            :disabled="$locked"
                                        />
                                    </flux:field>
                                </div>

                                {{-- Impuesto --}}
                                <div class="md:col-span-3">
                                    <flux:field>
                                        <flux:label class="text-xs">Impuesto</flux:label>
                                        <flux:select wire:model.live="items.{{ $index }}.tax_id" class="text-sm" :disabled="$locked">
                                            <option value="">Sin impuesto</option>
                                            @foreach($availableTaxes as $tax)
                                                <option value="{{ $tax->id }}">{{ $tax->name }} ({{ number_format($tax->rate, 2) }}%)</option>
                                            @endforeach
                                        </flux:select>
                                    </flux:field>
                                </div>
                            </div>

                            {{-- Totales de línea --}}
                            <div class="mt-3 pt-3 border-t border-zinc-200 bg-zinc-50 -mx-4 -mb-4 px-4 py-2 rounded-b-lg">
                                <div class="flex flex-wrap gap-x-6 gap-y-1 text-xs">
                                    <span class="text-zinc-500">Subtotal: <strong class="text-zinc-900">{{ number_format($itemSubtotal, 2) }} €</strong></span>
                                    @if($itemDiscount > 0)
                                        <span class="text-zinc-500">Dto: <strong class="text-red-600">-{{ number_format($itemDiscAmt, 2) }} €</strong></span>
                                    @endif
                                    <span class="text-zinc-500">Base: <strong class="text-zinc-900">{{ number_format($itemBase, 2) }} €</strong></span>
                                    @if($selectedTax)
                                        <span class="text-zinc-500">{{ $selectedTax->name }} ({{ number_format($taxRate, 2) }}%): <strong class="text-zinc-900">{{ number_format($itemTaxAmt, 2) }} €</strong></span>
                                    @endif
                                    <span class="ml-auto text-zinc-600 font-semibold">Total: <strong class="text-base text-green-600">{{ number_format($itemTotal, 2) }} €</strong></span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-10 border-2 border-dashed border-zinc-300 rounded-xl">
                            <p class="text-zinc-500">No hay líneas en el albarán.</p>
                        </div>
                    @endforelse

                    @if(!$this->isLocked && $invoice->status === 'draft')
                        <div class="flex justify-center pt-4 border-t border-zinc-200 mt-4">
                            <flux:button type="button" wire:click="addItem" variant="outline" icon="plus">
                                Añadir concepto manual
                            </flux:button>
                        </div>
                    @endif
                </div>
            </x-agro.form-section>

            {{-- Observaciones --}}
            <x-agro.form-section title="Observaciones">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:field>
                        <flux:label>Observaciones internas</flux:label>
                        <flux:textarea wire:model="observations" rows="3" placeholder="Notas internas..." :disabled="$this->isLocked" />
                        <flux:error name="observations" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Observaciones en documento</flux:label>
                        <flux:textarea wire:model="observations_invoice" rows="3" placeholder="Texto que aparecerá en el albarán y factura..." :disabled="$this->isLocked" />
                        <flux:error name="observations_invoice" />
                    </flux:field>
                </div>
            </x-agro.form-section>

            {{-- Totales --}}
            <x-agro.form-section title="Totales">
                <div class="bg-zinc-50 rounded-lg p-6">
                    <div class="flex flex-wrap gap-8 items-end">
                        <div>
                            <p class="text-sm text-zinc-500 mb-1">Base imponible</p>
                            <p class="text-xl font-bold text-zinc-900">{{ number_format($this->subtotal, 2) }} €</p>
                        </div>
                        @if($this->discountAmount > 0)
                            <div>
                                <p class="text-sm text-zinc-500 mb-1">Descuento</p>
                                <p class="text-xl font-bold text-red-600">-{{ number_format($this->discountAmount, 2) }} €</p>
                            </div>
                        @endif
                        <div>
                            <p class="text-sm text-zinc-500 mb-1">Impuestos</p>
                            <p class="text-xl font-bold text-zinc-900">{{ number_format($this->taxAmount, 2) }} €</p>
                        </div>
                        <div class="ml-auto text-right">
                            <p class="text-sm text-zinc-500 mb-1">Total</p>
                            <p class="text-3xl font-bold text-green-600">{{ number_format($this->totalAmount, 2) }} €</p>
                        </div>
                    </div>
                </div>
            </x-agro.form-section>

            {{-- Acciones --}}
            <div class="flex justify-between items-center gap-4 pt-2">
                {{-- Botón facturar (solo en draft) --}}
                <div>
                    @if($invoice->status === 'draft' && !$this->isLocked)
                        <flux:button
                            type="button"
                            wire:click="openInvoiceModal"
                            variant="primary"
                            icon="paper-airplane"
                        >
                            Emitir factura
                        </flux:button>
                    @elseif($invoice->status === 'sent')
                        <flux:badge color="green" size="lg">Factura emitida</flux:badge>
                    @endif
                </div>

                <div class="flex gap-3">
                    <flux:button
                        type="button"
                        variant="outline"
                        wire:click="cancel"
                        wire:confirm="¿Descartar los cambios no guardados?"
                    >
                        Descartar cambios
                    </flux:button>
                    <flux:button type="submit" variant="primary">
                        Guardar cambios
                    </flux:button>
                </div>
            </div>
        </form>
    </x-agro.card>

    {{-- Modal: Confirmar emisión de factura --}}
    @if($showInvoiceModal)
        <div class="fixed z-50 inset-0 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-black/50" wire:click="closeInvoiceModal"></div>
                <div class="relative bg-white rounded-xl border border-zinc-200 shadow-xl max-w-md w-full p-6 z-10" wire:click.stop>

                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-bold text-zinc-900">Emitir factura</h3>
                        <flux:button wire:click="closeInvoiceModal" variant="ghost" size="sm" icon="x-mark" />
                    </div>

                    {{-- Resumen del documento --}}
                    <div class="bg-zinc-50 rounded-lg p-4 mb-5 grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <p class="text-zinc-500">Albarán</p>
                            <p class="font-mono font-semibold text-zinc-900">{{ $invoice->delivery_note_code }}</p>
                        </div>
                        <div>
                            <p class="text-zinc-500">Número de factura</p>
                            <p class="font-mono font-semibold text-zinc-900">{{ $invoice->invoice_number }}</p>
                        </div>
                        <div>
                            <p class="text-zinc-500">Cliente</p>
                            <p class="font-semibold text-zinc-900">{{ $invoice->client->full_name ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-zinc-500">Total</p>
                            <p class="font-bold text-green-600 text-base">{{ number_format($this->totalAmount, 2) }} €</p>
                        </div>
                    </div>

                    {{-- Fecha de factura --}}
                    <flux:field class="mb-4">
                        <flux:label required>Fecha de la factura</flux:label>
                        <flux:input
                            wire:model="invoice_date_modal"
                            type="date"
                            required
                        />
                        <flux:error name="invoice_date_modal" />
                    </flux:field>

                    <flux:callout variant="warning" class="mb-5">
                        Al emitir, el stock reservado pasa a <strong>vendido</strong> y el número de factura queda bloqueado.
                    </flux:callout>

                    <div class="flex justify-end gap-3">
                        <flux:button wire:click="closeInvoiceModal" variant="outline">Cancelar</flux:button>
                        <flux:button wire:click="markAsSent" variant="primary" icon="paper-airplane">
                            Emitir factura
                        </flux:button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
