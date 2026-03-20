<div class="space-y-6 animate-fade-in">

    {{-- Cabecera --}}
    <x-agro.page-header
        title="Albarán {{ $invoice->delivery_note_code ?? '#' . $invoice->id }}"
        :description="$invoice->invoice_number ? 'Factura ' . $invoice->invoice_number : 'Borrador — pendiente de facturar'"
    >
        <x-slot:actions>
            <flux:button href="{{ route('producer.invoices.mixed.index') }}" wire:navigate variant="outline" icon="arrow-left">
                Volver
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- ══════════════════════════════════════════════════════════════════════
         Card de estados (siempre visible)
    ══════════════════════════════════════════════════════════════════════ --}}
    <x-agro.card>
        <div class="flex flex-wrap items-end gap-4">

            {{-- Estado de entrega --}}
            <div class="flex-1 min-w-[160px]">
                <flux:field>
                    <flux:label class="text-xs font-semibold uppercase tracking-wide text-zinc-400">Entrega</flux:label>
                    <flux:select wire:model="delivery_status" :disabled="$this->isLocked">
                        <option value="pending">Pendiente</option>
                        <option value="delivered">Entregada</option>
                        <option value="cancelled">Cancelada</option>
                    </flux:select>
                </flux:field>
            </div>

            {{-- Estado de cobro --}}
            <div class="flex-1 min-w-[160px]">
                <flux:field>
                    <flux:label class="text-xs font-semibold uppercase tracking-wide text-zinc-400">Estado de cobro</flux:label>
                    <flux:select wire:model="payment_status" :disabled="$invoice->status === 'cancelled'">
                        <option value="unpaid">Pendiente de cobro</option>
                        <option value="paid">Cobrada</option>
                        <option value="overdue">Vencida</option>
                        <option value="refunded">Reembolsada</option>
                    </flux:select>
                </flux:field>
            </div>

            {{-- Forma de pago --}}
            <div class="flex-1 min-w-[160px]">
                <flux:field>
                    <flux:label class="text-xs font-semibold uppercase tracking-wide text-zinc-400">Forma de pago</flux:label>
                    <flux:select wire:model="payment_type" :disabled="$invoice->status === 'cancelled'">
                        <option value="">Sin especificar</option>
                        <option value="cash">Efectivo</option>
                        <option value="transfer">Transferencia</option>
                        <option value="check">Cheque</option>
                        <option value="other">Otro</option>
                    </flux:select>
                </flux:field>
            </div>

            {{-- Info de factura --}}
            <div class="flex-1 min-w-[140px] space-y-1">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-zinc-400">Factura</p>
                @if($invoice->invoice_number)
                    <p class="font-mono font-bold text-zinc-900 text-sm">{{ $invoice->invoice_number }}</p>
                    @if($invoice->invoice_date)
                        <p class="text-xs text-zinc-400">{{ $invoice->invoice_date->format('d/m/Y') }}</p>
                    @endif
                    <flux:badge color="blue" size="sm">Emitida</flux:badge>
                @elseif($invoice->status === 'cancelled')
                    <flux:badge color="red" size="sm">Cancelada</flux:badge>
                @else
                    <flux:badge color="zinc" size="sm">Borrador</flux:badge>
                    <p class="text-xs text-zinc-400">Pendiente de emitir</p>
                @endif
            </div>

            {{-- Botón guardar estados --}}
            @if($invoice->status !== 'cancelled')
                <div class="flex-shrink-0">
                    <flux:button
                        type="button"
                        variant="primary"
                        size="sm"
                        wire:click="saveStatuses"
                        wire:loading.attr="disabled"
                        wire:target="saveStatuses,confirmPaymentDate,confirmDeliveryStatus"
                    >
                        <span wire:loading.remove wire:target="saveStatuses,confirmPaymentDate,confirmDeliveryStatus">Guardar estados</span>
                        <span wire:loading wire:target="saveStatuses,confirmPaymentDate,confirmDeliveryStatus">Guardando...</span>
                    </flux:button>
                </div>
            @endif

        </div>
    </x-agro.card>

    {{-- Aviso bloqueado --}}
    @if($this->isLocked)
        <flux:callout variant="{{ $invoice->status === 'cancelled' ? 'danger' : 'warning' }}" icon="lock-closed">
            <strong>Albarán {{ $invoice->status === 'cancelled' ? 'cancelado' : ($invoice->delivery_status === 'delivered' ? 'entregado' : 'cerrado') }}.</strong>
            El contenido no puede modificarse. Puedes actualizar los estados de cobro y entrega desde la tarjeta superior.
        </flux:callout>
    @elseif($invoice->status === 'sent')
        <flux:callout variant="info">
            <strong>Factura emitida.</strong>
            Las líneas están bloqueadas. Para corregir errores crea una factura rectificativa.
        </flux:callout>
    @endif

    {{-- ══════════════════════════════════════════════════════════════════════
         Formulario principal
    ══════════════════════════════════════════════════════════════════════ --}}
    <form wire:submit="update" class="space-y-6">

        {{-- ── Documento ──────────────────────────────────────────────────── --}}
        <x-agro.card>
            <x-agro.form-section title="Documento">
                <div class="grid grid-cols-2 gap-4">

                    {{-- Código albarán (siempre readonly) --}}
                    <flux:field>
                        <flux:label>Código de albarán</flux:label>
                        <flux:input
                            value="{{ $delivery_note_code }}"
                            disabled
                            class="bg-zinc-100 font-mono font-semibold cursor-not-allowed"
                        />
                    </flux:field>

                    {{-- Número de factura (readonly si ya emitida) --}}
                    <flux:field>
                        <flux:label>Número de factura</flux:label>
                        @if($invoice->status === 'draft')
                            <flux:input
                                value="{{ $invoice_number ?: '— (se asignará al facturar)' }}"
                                disabled
                                class="bg-zinc-50 font-mono text-zinc-400 cursor-not-allowed"
                            />
                        @else
                            <flux:input
                                value="{{ $invoice_number }}"
                                disabled
                                class="bg-zinc-100 font-mono font-semibold cursor-not-allowed"
                            />
                            <flux:badge color="blue" size="sm" class="mt-1">Emitida</flux:badge>
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
        </x-agro.card>

        {{-- ── Cliente ─────────────────────────────────────────────────────── --}}
        <x-agro.card>
            <x-agro.form-section title="Cliente">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <flux:field>
                        <flux:label required>Cliente</flux:label>
                        <flux:select
                            wire:model.live="client_id"
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
        </x-agro.card>

        {{-- ── Añadir líneas (solo si borrador y no bloqueado) ─────────────── --}}
        @if(!$this->isLocked && $invoice->status === 'draft')
            <x-agro.card>
                <x-agro.form-section title="Añadir líneas">

                    {{-- Añadir cosecha --}}
                    <div class="border border-green-200 bg-green-50 rounded-xl p-4 space-y-3">
                        <div class="flex items-center gap-2">
                            <flux:icon icon="archive-box" class="size-4 text-green-600" />
                            <h4 class="text-sm font-semibold text-green-800">Añadir cosecha</h4>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <flux:field>
                                <flux:label>Campaña</flux:label>
                                <flux:select wire:model.live="selectedCampaign">
                                    <option value="">Todas las campañas</option>
                                    @foreach($campaigns as $campaign)
                                        <option value="{{ $campaign->id }}">{{ $campaign->year }}</option>
                                    @endforeach
                                </flux:select>
                            </flux:field>
                            <div class="md:col-span-2">
                                <flux:field>
                                    <flux:label>Cosecha disponible</flux:label>
                                    <flux:select wire:model.live="selectedHarvestId">
                                        <option value="">-- Selecciona una cosecha --</option>
                                        @foreach($availableHarvests as $harvest)
                                            <option value="{{ $harvest->id }}">
                                                {{ $harvest->plotPlanting->grapeVariety->name ?? 'Sin variedad' }}
                                                – {{ $harvest->activity->plot->name ?? 'Sin parcela' }}
                                                – {{ $harvest->harvest_start_date->format('d/m/Y') }}
                                                @if($harvest->container) [{{ $harvest->container->name }}] @endif
                                                – Disp: {{ number_format($harvest->available_qty_computed, 0) }} kg
                                                @if($harvest->price_per_kg)
                                                    ({{ number_format($harvest->price_per_kg, 3) }} €/kg)
                                                @endif
                                            </option>
                                        @endforeach
                                    </flux:select>
                                    <p class="mt-1 text-xs text-zinc-500">Solo cosechas con stock disponible.</p>
                                </flux:field>
                            </div>
                        </div>
                        <div class="flex justify-end">
                            <flux:button
                                type="button"
                                wire:click="addHarvestToInvoice"
                                variant="outline"
                                size="sm"
                                icon="plus"
                                class="border-green-300 text-green-700 hover:bg-green-100"
                            >
                                Añadir cosecha
                            </flux:button>
                        </div>
                        @if($availableHarvests->isEmpty())
                            <flux:callout variant="info" class="mt-2">
                                No hay cosechas con stock disponible.
                            </flux:callout>
                        @endif
                    </div>

                    {{-- Añadir lote de vino --}}
                    <div class="border border-purple-200 bg-purple-50 rounded-xl p-4 space-y-3 mt-4">
                        <div class="flex items-center gap-2">
                            <flux:icon icon="beaker" class="size-4 text-purple-600" />
                            <h4 class="text-sm font-semibold text-purple-800">Añadir lote de vino</h4>
                        </div>
                        <flux:field>
                            <flux:label>Lote disponible</flux:label>
                            <flux:select wire:model.live="selectedLotId">
                                <option value="">-- Selecciona un lote de vino --</option>
                                @foreach($availableLots as $lot)
                                    <option value="{{ $lot->id }}">
                                        {{ $lot->name }}
                                        – Disp: {{ number_format($lot->available_bottles ?? 0, 0) }} botellas
                                    </option>
                                @endforeach
                            </flux:select>
                        </flux:field>
                        <div class="flex justify-end">
                            <flux:button
                                type="button"
                                wire:click="addWineToInvoice"
                                variant="outline"
                                size="sm"
                                icon="plus"
                                class="border-purple-300 text-purple-700 hover:bg-purple-100"
                            >
                                Añadir lote
                            </flux:button>
                        </div>
                    </div>

                    {{-- Añadir concepto manual --}}
                    <div class="flex justify-center pt-4 border-t border-zinc-200 mt-4">
                        <flux:button type="button" wire:click="addItem" variant="outline" icon="plus">
                            Añadir concepto manual
                        </flux:button>
                    </div>

                </x-agro.form-section>
            </x-agro.card>
        @endif

        {{-- ── Líneas del albarán ───────────────────────────────────────────── --}}
        <x-agro.card>
            <x-agro.form-section title="Líneas del albarán">
                <div class="space-y-4">

                    @forelse($items as $index => $item)
                        @php
                            $conceptType   = $item['concept_type'] ?? 'other';
                            $isHarvestItem = $conceptType === 'harvest' && !empty($item['harvest_id']);
                            $isWineItem    = $conceptType === 'wine'    && !empty($item['wine_lot_id']);
                            $locked        = $this->isLocked || $invoice->status === 'sent';

                            $availableQty = isset($item['available_qty']) ? (float)$item['available_qty'] : null;
                            $totalWeight  = isset($item['total_weight'])  ? (float)$item['total_weight']  : null;

                            $itemQty      = (float)($item['quantity']            ?? 0);
                            $itemPrice    = (float)($item['unit_price']          ?? 0);
                            $itemDiscount = (float)($item['discount_percentage'] ?? 0);
                            $itemSubtotal = $itemQty * $itemPrice;
                            $itemDiscAmt  = $itemSubtotal * ($itemDiscount / 100);
                            $itemBase     = $itemSubtotal - $itemDiscAmt;
                            $selectedTax  = (!empty($item['tax_id']) && $availableTaxes)
                                ? $availableTaxes->firstWhere('id', $item['tax_id'])
                                : null;
                            $taxRate      = $selectedTax ? $selectedTax->rate : 0;
                            $itemTaxAmt   = $itemBase * ($taxRate / 100);
                            $itemTotal    = $itemBase + $itemTaxAmt;
                        @endphp

                        <div
                            class="border-2 rounded-xl p-4 bg-white shadow-xs transition-colors
                                {{ $isHarvestItem ? 'border-green-200 hover:border-green-300'
                                    : ($isWineItem ? 'border-purple-200 hover:border-purple-300'
                                        : 'border-zinc-200 hover:border-zinc-300') }}"
                            wire:key="edit-item-{{ $index }}"
                        >
                            {{-- Cabecera de línea --}}
                            <div class="flex justify-between items-start mb-4">
                                <div class="flex items-center gap-2">
                                    <h4 class="text-sm font-bold text-zinc-900">Línea #{{ $index + 1 }}</h4>
                                    @if($isHarvestItem)
                                        <flux:badge color="green" size="sm">Cosecha</flux:badge>
                                    @elseif($isWineItem)
                                        <flux:badge color="purple" size="sm">Vino</flux:badge>
                                    @else
                                        <flux:badge color="zinc" size="sm">Otro</flux:badge>
                                    @endif
                                </div>
                                @if(!$locked)
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

                            <div class="grid grid-cols-1 md:grid-cols-12 gap-3">

                                {{-- Nombre --}}
                                <div class="md:col-span-12">
                                    <flux:field>
                                        <flux:label class="text-xs">Concepto <span class="text-red-500">*</span></flux:label>
                                        <flux:input
                                            wire:model="items.{{ $index }}.name"
                                            placeholder="Nombre del concepto..."
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
                                        <flux:input
                                            wire:model="items.{{ $index }}.sku"
                                            class="text-sm"
                                            :disabled="$locked"
                                        />
                                    </flux:field>
                                    <flux:field>
                                        <flux:label class="text-xs">Tipo</flux:label>
                                        <flux:select
                                            wire:model="items.{{ $index }}.concept_type"
                                            class="text-sm"
                                            :disabled="$locked || $isHarvestItem || $isWineItem"
                                        >
                                            <option value="harvest">Cosecha</option>
                                            <option value="wine">Vino</option>
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
                                            @if($isHarvestItem && $availableQty !== null && !$locked) max="{{ $availableQty }}" @endif
                                            class="text-sm"
                                            :disabled="$locked"
                                        />
                                        <flux:error name="items.{{ $index }}.quantity" />

                                        @if($isHarvestItem && $availableQty !== null && !$locked)
                                            @php $exceedsStock = $itemQty > $availableQty; @endphp
                                            <p class="mt-1 text-xs {{ $exceedsStock ? 'text-red-600 font-semibold' : 'text-zinc-500' }}">
                                                Máx. disponible: {{ number_format($availableQty, 3) }} kg
                                                @if($totalWeight && $totalWeight != $availableQty)
                                                    / Total: {{ number_format($totalWeight, 3) }} kg
                                                @endif
                                                @if($exceedsStock) — ¡Supera el stock! @endif
                                            </p>
                                        @endif
                                    </flux:field>
                                    <flux:field class="mt-2">
                                        <flux:label class="text-xs">Unidad</flux:label>
                                        <flux:select
                                            wire:model="items.{{ $index }}.unit"
                                            class="text-sm"
                                            :disabled="$locked || $isHarvestItem || $isWineItem"
                                        >
                                            <option value="kg">kg</option>
                                            <option value="botella">botella</option>
                                            <option value="litros">litros</option>
                                            <option value="centilitros">centilitros</option>
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
                                        <flux:select
                                            wire:model.live="items.{{ $index }}.tax_id"
                                            class="text-sm"
                                            :disabled="$locked"
                                        >
                                            <option value="">Sin impuesto</option>
                                            @foreach($availableTaxes as $tax)
                                                <option value="{{ $tax->id }}">{{ $tax->name }} ({{ number_format($tax->rate, 2) }}%)</option>
                                            @endforeach
                                        </flux:select>
                                    </flux:field>
                                </div>

                            </div>

                            {{-- Totales de línea --}}
                            <div class="mt-3 pt-3 border-t border-zinc-200 bg-zinc-50 -mx-4 -mb-4 px-4 py-2 rounded-b-xl">
                                <div class="flex flex-wrap gap-x-6 gap-y-1 text-xs">
                                    <span class="text-zinc-500">Subtotal: <strong class="text-zinc-900">{{ number_format($itemSubtotal, 2, ',', '.') }} €</strong></span>
                                    @if($itemDiscount > 0)
                                        <span class="text-zinc-500">Dto: <strong class="text-red-600">-{{ number_format($itemDiscAmt, 2, ',', '.') }} €</strong></span>
                                    @endif
                                    <span class="text-zinc-500">Base: <strong class="text-zinc-900">{{ number_format($itemBase, 2, ',', '.') }} €</strong></span>
                                    @if($selectedTax)
                                        <span class="text-zinc-500">{{ $selectedTax->name }} ({{ number_format($taxRate, 2) }}%): <strong class="text-zinc-900">{{ number_format($itemTaxAmt, 2, ',', '.') }} €</strong></span>
                                    @endif
                                    <span class="ml-auto text-zinc-600 font-semibold">Total: <strong class="text-base text-green-600">{{ number_format($itemTotal, 2, ',', '.') }} €</strong></span>
                                </div>
                            </div>

                        </div>
                    @empty
                        <div class="text-center py-12 border-2 border-dashed border-zinc-300 rounded-xl">
                            <flux:icon icon="document-text" class="size-10 text-zinc-300 mx-auto mb-3" />
                            <p class="text-zinc-500">No hay líneas en el albarán.</p>
                        </div>
                    @endforelse

                </div>
            </x-agro.form-section>
        </x-agro.card>

        {{-- ── Observaciones ────────────────────────────────────────────────── --}}
        <x-agro.card>
            <x-agro.form-section title="Observaciones">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:field>
                        <flux:label>Observaciones internas</flux:label>
                        <flux:textarea
                            wire:model="observations"
                            rows="3"
                            placeholder="Notas internas..."
                            :disabled="$this->isLocked"
                        />
                        <flux:error name="observations" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Observaciones en documento</flux:label>
                        <flux:textarea
                            wire:model="observations_invoice"
                            rows="3"
                            placeholder="Texto que aparecerá en el albarán y factura..."
                            :disabled="$this->isLocked"
                        />
                        <flux:error name="observations_invoice" />
                    </flux:field>
                </div>
            </x-agro.form-section>
        </x-agro.card>

        {{-- ── Totales ───────────────────────────────────────────────────────── --}}
        <x-agro.card>
            <x-agro.form-section title="Totales">
                <div class="bg-zinc-50 rounded-xl p-6">
                    <div class="max-w-xs ml-auto space-y-2">
                        <div class="flex justify-between text-sm text-zinc-600">
                            <span>Subtotal</span>
                            <span class="font-semibold text-zinc-900">{{ number_format($this->subtotal, 2, ',', '.') }} €</span>
                        </div>
                        @if($this->discountAmount > 0)
                            <div class="flex justify-between text-sm text-zinc-600">
                                <span>Descuentos</span>
                                <span class="font-semibold text-red-600">-{{ number_format($this->discountAmount, 2, ',', '.') }} €</span>
                            </div>
                        @endif
                        <div class="flex justify-between text-sm text-zinc-600">
                            <span>IVA</span>
                            <span class="font-semibold text-zinc-900">{{ number_format($this->taxAmount, 2, ',', '.') }} €</span>
                        </div>
                        <div class="flex justify-between pt-3 border-t border-zinc-300">
                            <span class="text-base font-bold text-zinc-900">Total</span>
                            <span class="text-2xl font-bold text-green-600">{{ number_format($this->totalAmount, 2, ',', '.') }} €</span>
                        </div>
                    </div>
                </div>
            </x-agro.form-section>
        </x-agro.card>

        {{-- ── Acciones ──────────────────────────────────────────────────────── --}}
        @if(!$this->isLocked)
            <div class="flex items-center justify-between gap-4 pt-2">
                <div>
                    @if($invoice->status === 'draft')
                        <flux:button
                            type="button"
                            wire:click="openInvoiceModal"
                            variant="primary"
                            icon="paper-airplane"
                        >
                            Emitir factura
                        </flux:button>
                    @endif
                </div>
                <div class="flex items-center gap-3">
                    <flux:button
                        href="{{ route('producer.invoices.mixed.index') }}"
                        wire:navigate
                        variant="outline"
                        icon="arrow-left"
                    >
                        Cancelar
                    </flux:button>
                    <flux:button
                        type="submit"
                        variant="primary"
                        wire:loading.attr="disabled"
                        wire:target="update"
                    >
                        <span wire:loading.remove wire:target="update">Guardar cambios</span>
                        <span wire:loading wire:target="update">Guardando...</span>
                    </flux:button>
                </div>
            </div>
        @else
            <div class="flex justify-end pt-2">
                <flux:button
                    href="{{ route('producer.invoices.mixed.index') }}"
                    wire:navigate
                    variant="outline"
                    icon="arrow-left"
                >
                    Volver al listado
                </flux:button>
            </div>
        @endif

    </form>

    {{-- ══════════════════════════════════════════════════════════════════════
         Modal: Emitir factura
    ══════════════════════════════════════════════════════════════════════ --}}
    @if($showInvoiceModal)
        <div
            class="fixed z-50 inset-0 flex items-center justify-center p-4 bg-black/50"
            x-data
            x-on:keydown.escape.window="$wire.closeInvoiceModal()"
        >
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md" @click.stop>

                <div class="px-6 py-4 border-b border-zinc-200 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-agro-100 rounded-lg flex items-center justify-center">
                            <flux:icon icon="paper-airplane" class="size-4 text-agro-600" />
                        </div>
                        <h3 class="text-base font-semibold text-zinc-900">Emitir factura</h3>
                    </div>
                    <flux:button wire:click="closeInvoiceModal" variant="ghost" size="sm" icon="x-mark" />
                </div>

                <div class="px-6 py-5 space-y-4">
                    {{-- Resumen --}}
                    <div class="bg-zinc-50 rounded-lg p-4 grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <p class="text-zinc-500">Albarán</p>
                            <p class="font-mono font-semibold text-zinc-900">{{ $invoice->delivery_note_code ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-zinc-500">Cliente</p>
                            <p class="font-semibold text-zinc-900">{{ $invoice->client?->full_name ?? '—' }}</p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-zinc-500">Total</p>
                            <p class="font-bold text-green-600 text-base">{{ number_format($this->totalAmount, 2, ',', '.') }} €</p>
                        </div>
                    </div>

                    {{-- Fecha de factura --}}
                    <flux:field>
                        <flux:label required>Fecha de la factura</flux:label>
                        <flux:input
                            wire:model="invoice_date_modal"
                            type="date"
                            required
                        />
                        <flux:error name="invoice_date_modal" />
                    </flux:field>

                    <flux:callout variant="warning">
                        Al emitir, el stock reservado pasa a <strong>vendido</strong> y el número de factura queda bloqueado. Esta acción es irreversible.
                    </flux:callout>
                </div>

                <div class="px-6 py-4 bg-zinc-50 border-t border-zinc-200 rounded-b-2xl flex items-center justify-end gap-3">
                    <flux:button wire:click="closeInvoiceModal" variant="outline">
                        Cancelar
                    </flux:button>
                    <flux:button
                        wire:click="markAsSent"
                        variant="primary"
                        icon="paper-airplane"
                        wire:loading.attr="disabled"
                        wire:target="markAsSent"
                    >
                        <span wire:loading.remove wire:target="markAsSent">Emitir factura</span>
                        <span wire:loading wire:target="markAsSent">Emitiendo...</span>
                    </flux:button>
                </div>
            </div>
        </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════════════════
         Modal: Confirmar cambio de estado de entrega
    ══════════════════════════════════════════════════════════════════════ --}}
    @if($showDeliveryModal)
        <div
            class="fixed z-50 inset-0 flex items-center justify-center p-4 bg-black/50"
            x-data
            x-on:keydown.escape.window="$wire.closeDeliveryModal()"
        >
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md" @click.stop>

                <div class="px-6 py-4 border-b border-zinc-200 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-zinc-900">
                        Confirmar cambio de estado de entrega
                    </h3>
                    <flux:button wire:click="closeDeliveryModal" variant="ghost" size="sm" icon="x-mark" />
                </div>

                <div class="p-6 space-y-4">
                    <div class="bg-zinc-50 rounded-lg p-4 grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <p class="text-zinc-500">Albarán</p>
                            <p class="font-mono font-semibold text-zinc-900">{{ $invoice->delivery_note_code ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-zinc-500">Cliente</p>
                            <p class="font-semibold text-zinc-900">{{ $invoice->client?->full_name ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-zinc-500">Nuevo estado</p>
                            <p class="font-semibold text-zinc-900">
                                {{ $pendingDeliveryStatus === 'delivered' ? 'Entregada' : 'Cancelada' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-zinc-500">Total</p>
                            <p class="font-bold text-green-600">{{ number_format($this->totalAmount, 2, ',', '.') }} €</p>
                        </div>
                    </div>

                    @if($pendingDeliveryStatus === 'delivered')
                        <flux:callout variant="warning">
                            El stock de cosechas y lotes pasará de <strong>reservado → vendido</strong>.
                            Esta acción bloqueará la edición del contenido del albarán.
                        </flux:callout>
                    @else
                        <flux:callout variant="danger">
                            El stock se restaurará a <strong>disponible</strong>. El albarán quedará cancelado y no se podrá recuperar.
                        </flux:callout>
                    @endif
                </div>

                <div class="flex justify-end gap-3 px-6 py-4 bg-zinc-50 border-t border-zinc-200 rounded-b-2xl">
                    <flux:button
                        wire:click="closeDeliveryModal"
                        variant="outline"
                        wire:loading.attr="disabled"
                        wire:target="confirmDeliveryStatus"
                    >
                        Cancelar
                    </flux:button>
                    <flux:button
                        wire:click="confirmDeliveryStatus"
                        variant="{{ $pendingDeliveryStatus === 'delivered' ? 'primary' : 'danger' }}"
                        icon="{{ $pendingDeliveryStatus === 'delivered' ? 'check-circle' : 'x-circle' }}"
                        wire:loading.attr="disabled"
                        wire:target="confirmDeliveryStatus"
                    >
                        <span wire:loading.remove wire:target="confirmDeliveryStatus">
                            {{ $pendingDeliveryStatus === 'delivered' ? 'Confirmar entrega' : 'Confirmar cancelación' }}
                        </span>
                        <span wire:loading wire:target="confirmDeliveryStatus">Guardando...</span>
                    </flux:button>
                </div>
            </div>
        </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════════════════
         Modal: Fecha de cobro
    ══════════════════════════════════════════════════════════════════════ --}}
    @if($showPaymentDateModal)
        <div
            class="fixed z-50 inset-0 flex items-center justify-center p-4 bg-black/50"
            x-data
            x-on:keydown.escape.window="$wire.closePaymentDateModal()"
        >
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm" @click.stop>

                <div class="px-6 py-4 border-b border-zinc-200 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-zinc-900">Fecha de cobro</h3>
                    <flux:button wire:click="closePaymentDateModal" variant="ghost" size="sm" icon="x-mark" />
                </div>

                <div class="p-6 space-y-4">
                    <p class="text-sm text-zinc-600">Indica la fecha en que se realizó el cobro de este albarán.</p>
                    <flux:field>
                        <flux:label required>Fecha de cobro</flux:label>
                        <flux:input wire:model="payment_date" type="date" required />
                        <flux:error name="payment_date" />
                    </flux:field>
                </div>

                <div class="flex justify-end gap-3 px-6 py-4 bg-zinc-50 border-t border-zinc-200 rounded-b-2xl">
                    <flux:button
                        wire:click="closePaymentDateModal"
                        variant="outline"
                        wire:loading.attr="disabled"
                        wire:target="confirmPaymentDate"
                    >
                        Cancelar
                    </flux:button>
                    <flux:button
                        wire:click="confirmPaymentDate"
                        variant="primary"
                        icon="check"
                        wire:loading.attr="disabled"
                        wire:target="confirmPaymentDate"
                    >
                        <span wire:loading.remove wire:target="confirmPaymentDate">Confirmar</span>
                        <span wire:loading wire:target="confirmPaymentDate">Guardando...</span>
                    </flux:button>
                </div>
            </div>
        </div>
    @endif

</div>
