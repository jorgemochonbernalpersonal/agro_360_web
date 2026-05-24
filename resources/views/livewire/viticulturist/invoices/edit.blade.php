<div class="space-y-6 animate-fade-in">

    {{-- Cabecera --}}
    <x-agro.page-header
        :title="__('Albarán :code', ['code' => $invoice->delivery_note_code])"
        :description="$invoice->invoice_number ? __('Factura :num', ['num' => $invoice->invoice_number]) : __('Borrador — pendiente de facturar')"
    >
        <x-slot:actions>
            <flux:button href="{{ roleRoute('viticulturist.invoices.index') }}" variant="outline" icon="arrow-left">
                {{ __('Volver') }}
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- ── Card de estados ──────────────────────────────────────────────────── --}}
    <x-agro.card>
        <div class="flex flex-wrap items-end gap-4">

            {{-- Entrega --}}
            <div class="flex-1 min-w-[160px]">
                <flux:field>
                    <flux:label class="text-xs font-semibold uppercase tracking-wide text-zinc-400">{{ __('Entrega') }}</flux:label>
                    <flux:select wire:model="delivery_status" :disabled="$this->isLocked">
                        <option value="pending">{{ __('Pendiente') }}</option>
                        <option value="in_transit">{{ __('En tránsito') }}</option>
                        <option value="delivered">{{ __('Entregada') }}</option>
                        <option value="cancelled">{{ __('Cancelada') }}</option>
                    </flux:select>
                </flux:field>
            </div>

            {{-- Cobro --}}
            <div class="flex-1 min-w-[160px]">
                <flux:field>
                    <flux:label class="text-xs font-semibold uppercase tracking-wide text-zinc-400">{{ __('Estado de cobro') }}</flux:label>
                    <flux:select wire:model="payment_status" :disabled="$invoice->status === 'cancelled'">
                        <option value="unpaid">{{ __('Pendiente de cobro') }}</option>
                        <option value="paid">{{ __('Cobrada') }}</option>
                        <option value="overdue">{{ __('Vencida') }}</option>
                        <option value="refunded">{{ __('Reembolsada') }}</option>
                    </flux:select>
                </flux:field>
            </div>

            {{-- Forma de pago --}}
            <div class="flex-1 min-w-[160px]">
                <flux:field>
                    <flux:label class="text-xs font-semibold uppercase tracking-wide text-zinc-400">{{ __('Forma de pago') }}</flux:label>
                    <flux:select wire:model="payment_type" :disabled="$invoice->status === 'cancelled'">
                        <option value="">{{ __('Sin especificar') }}</option>
                        <option value="cash">{{ __('Efectivo') }}</option>
                        <option value="transfer">{{ __('Transferencia') }}</option>
                        <option value="check">{{ __('Cheque') }}</option>
                        <option value="other">{{ __('Otro') }}</option>
                    </flux:select>
                </flux:field>
            </div>

            {{-- Info factura --}}
            <div class="flex-1 min-w-[140px] space-y-1">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-zinc-400">{{ __('Factura') }}</p>
                @if($invoice->invoice_number)
                    <p class="font-mono font-bold text-zinc-900 text-sm">{{ $invoice->invoice_number }}</p>
                    @if($invoice->invoice_date)
                        <p class="text-xs text-zinc-400">{{ $invoice->invoice_date->format('d/m/Y') }}</p>
                    @endif
                    <flux:badge color="blue" size="sm">{{ __('Emitida') }}</flux:badge>
                @elseif($invoice->status === 'cancelled')
                    <flux:badge color="red" size="sm">{{ __('Cancelada') }}</flux:badge>
                @else
                    <flux:badge color="zinc" size="sm">{{ __('Borrador') }}</flux:badge>
                    <p class="text-xs text-zinc-400">{{ __('Emite desde el listado') }}</p>
                @endif
            </div>

            {{-- Botón guardar estados --}}
            @if($invoice->status !== 'cancelled')
                <div class="flex-shrink-0">
                    <flux:button type="button" variant="primary" size="sm"
                        wire:click="saveStatuses"
                        wire:loading.attr="disabled"
                        wire:target="saveStatuses,confirmPaymentDate,confirmDeliveryStatus">
                        <span wire:loading.remove wire:target="saveStatuses,confirmPaymentDate,confirmDeliveryStatus">{{ __('Guardar estados') }}</span>
                        <span wire:loading wire:target="saveStatuses,confirmPaymentDate,confirmDeliveryStatus">{{ __('Guardando...') }}</span>
                    </flux:button>
                </div>
            @endif
        </div>
    </x-agro.card>

    {{-- Aviso si bloqueada --}}
    @if($this->isLocked)
        <flux:callout variant="{{ $invoice->status === 'cancelled' ? 'danger' : 'warning' }}" icon="lock-closed">
            <strong>{{ $invoice->status === 'cancelled' ? __('Factura cancelada') : ($invoice->delivery_status === 'delivered' ? __('Factura entregada') : __('Factura cerrada')) }}.</strong>
            {{ __('El contenido no puede modificarse. Puedes actualizar el estado de cobro desde la tarjeta de arriba.') }}
        </flux:callout>
    @elseif($invoice->status === 'sent')
        <flux:callout variant="info">
            <strong>{{ __('Factura emitida.') }}</strong>
            {{ __('Las líneas están bloqueadas. Para corregir errores usa una factura rectificativa.') }}
        </flux:callout>
    @endif

    <x-agro.card>
        <form wire:submit="update" class="space-y-8">

            {{-- Documento: albarán + factura + fechas --}}
            <x-agro.form-section :title="__('Documento')">
                <div class="grid grid-cols-2 gap-4">
                    {{-- Código albarán (siempre readonly) --}}
                    <flux:field>
                        <flux:label>{{ __('Código de albarán') }}</flux:label>
                        <flux:input
                            value="{{ $delivery_note_code }}"
                            disabled
                            class="bg-zinc-100 font-mono font-semibold cursor-not-allowed"
                        />
                    </flux:field>

                    {{-- Número de factura (readonly si ya está facturada) --}}
                    <flux:field>
                        <flux:label>{{ __('Número de factura') }}</flux:label>
                        @if($invoice->status === 'draft')
                            <flux:input
                                value="{{ $invoice_number ?: __('—  (se asignará al facturar)') }}"
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
                        <flux:label>{{ __('Fecha albarán') }}</flux:label>
                        <flux:input
                            wire:model="delivery_note_date"
                            type="date"
                            :disabled="$this->isLocked || $invoice->status === 'sent'"
                        />
                        <flux:error name="delivery_note_date" />
                    </flux:field>

                    {{-- Fecha factura --}}
                    <flux:field>
                        <flux:label>{{ __('Fecha factura') }}</flux:label>
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
            <x-agro.form-section :title="__('Cliente')">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:field>
                        <flux:label required>{{ __('Cliente') }}</flux:label>
                        <flux:select
                            wire:model.live="client_id"
                            id="client_id"
                            required
                            :disabled="$this->isLocked || $invoice->status === 'sent'"
                        >
                            <option value="">{{ __('Selecciona un cliente') }}</option>
                            @foreach($availableClients as $client)
                                <option value="{{ $client->id }}">{{ $client->full_name }}</option>
                            @endforeach
                        </flux:select>
                        <flux:error name="client_id" />
                    </flux:field>

                    @if($client_id)
                        <flux:field>
                            <flux:label>{{ __('Dirección de facturación') }}</flux:label>
                            <flux:select
                                wire:model="client_address_id"
                                :disabled="$this->isLocked || $invoice->status === 'sent'"
                            >
                                <option value="">{{ __('Selecciona una dirección') }}</option>
                                @foreach($availableAddresses as $address)
                                    <option value="{{ $address->id }}">
                                        {{ $address->full_address }}
                                        @if($address->is_default) ({{ __('Por defecto') }}) @endif
                                    </option>
                                @endforeach
                            </flux:select>
                            <flux:error name="client_address_id" />
                        </flux:field>
                    @endif
                </div>
            </x-agro.form-section>

            {{-- Añadir cosechas (solo si no está bloqueada ni facturada) --}}
            @if(!$this->isLocked && $invoice->status === 'draft')
                <x-agro.form-section :title="__('Añadir cosecha')">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <flux:field>
                            <flux:label>{{ __('Filtrar por campaña') }}</flux:label>
                            <flux:select wire:model.live="selectedCampaign">
                                <option value="">{{ __('Todas las campañas') }}</option>
                                @foreach($campaigns as $campaign)
                                    <option value="{{ $campaign->id }}">{{ $campaign->year }}</option>
                                @endforeach
                            </flux:select>
                        </flux:field>

                        <div class="md:col-span-2">
                            <flux:field>
                                <flux:label>{{ __('Cosecha') }}</flux:label>
                                <flux:select
                                    wire:model.live="selectedHarvestId"
                                    wire:change="addHarvestToInvoice"
                                >
                                    <option value="">{{ __('-- Selecciona una cosecha con stock disponible --') }}</option>
                                    @foreach($availableHarvests as $harvest)
                                        <option value="{{ $harvest->id }}">
                                            {{ $harvest->plotPlanting->grapeVariety->name ?? __('Sin variedad') }} –
                                            {{ $harvest->activity->plot->name ?? __('Sin parcela') }} –
                                            {{ $harvest->harvest_start_date->format('d/m/Y') }}
                                            @if($harvest->container)
                                                [{{ $harvest->container->name }}]
                                            @endif
                                            – {{ __('Disp:') }} {{ number_format($harvest->available_qty_computed, 0) }} kg
                                            @if($harvest->price_per_kg)
                                                ({{ number_format($harvest->price_per_kg, 3) }} €/kg)
                                            @endif
                                        </option>
                                    @endforeach
                                </flux:select>
                                <p class="mt-1 text-xs text-zinc-400">{{ __('Solo cosechas con stock disponible. La cantidad se puede ajustar en las líneas.') }}</p>
                            </flux:field>
                        </div>
                    </div>

                    @if($availableHarvests->isEmpty())
                        <flux:callout variant="info" class="mt-4">
                            {{ __('No hay cosechas con stock disponible para añadir.') }}
                        </flux:callout>
                    @endif
                </x-agro.form-section>
            @endif

            {{-- Líneas --}}
            <x-agro.form-section :title="__('Líneas del albarán')">
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
                                    <h4 class="text-base font-bold text-zinc-900">{{ __('Línea #:num', ['num' => $index + 1]) }}</h4>
                                    @if($isHarvestItem)
                                        <flux:badge color="purple" size="sm">{{ __('Cosecha') }}</flux:badge>
                                    @endif
                                </div>
                                @if(!$locked && count($items) > 1)
                                    <flux:button
                                        type="button"
                                        wire:click="removeItem({{ $index }})"
                                        variant="danger"
                                        size="sm"
                                        icon="trash"
                                    >{{ __('Eliminar') }}</flux:button>
                                @endif
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                                {{-- Nombre --}}
                                <div class="md:col-span-12">
                                    <flux:field>
                                        <flux:label class="text-xs">{{ __('Concepto') }} <span class="text-red-500">*</span></flux:label>
                                        <flux:input
                                            wire:model="items.{{ $index }}.name"
                                            :placeholder="__('Ej: Uva Tempranillo...')"
                                            class="text-sm"
                                            :disabled="$locked"
                                        />
                                        <flux:error name="items.{{ $index }}.name" />
                                    </flux:field>
                                </div>

                                {{-- Descripción --}}
                                <div class="md:col-span-8">
                                    <flux:field>
                                        <flux:label class="text-xs">{{ __('Descripción') }}</flux:label>
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
                                        <flux:label class="text-xs">{{ __('SKU') }}</flux:label>
                                        <flux:input wire:model="items.{{ $index }}.sku" class="text-sm" :disabled="$locked" />
                                    </flux:field>
                                    <flux:field>
                                        <flux:label class="text-xs">{{ __('Tipo') }}</flux:label>
                                        <flux:select wire:model="items.{{ $index }}.concept_type" class="text-sm" :disabled="$locked">
                                            <option value="harvest">{{ __('Cosecha') }}</option>
                                            <option value="service">{{ __('Servicio') }}</option>
                                            <option value="product">{{ __('Producto') }}</option>
                                            <option value="other">{{ __('Otro') }}</option>
                                        </flux:select>
                                    </flux:field>
                                </div>

                                {{-- Cantidad + Unidad --}}
                                <div class="md:col-span-3">
                                    <flux:field>
                                        <flux:label class="text-xs">{{ __('Cantidad') }} <span class="text-red-500">*</span></flux:label>
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
                                                {{ __('Máx. disponible:') }} {{ number_format($availableQty, 3) }} kg
                                                @if($totalWeight && $totalWeight != $availableQty)
                                                    / {{ __('Total cosecha:') }} {{ number_format($totalWeight, 3) }} kg
                                                @endif
                                                @if($exceedsStock) — {{ __('¡Supera el stock!') }} @endif
                                            </p>
                                        @endif
                                    </flux:field>
                                    <flux:field class="mt-2">
                                        <flux:label class="text-xs">{{ __('Unidad') }}</flux:label>
                                        <flux:select wire:model="items.{{ $index }}.unit" class="text-sm" :disabled="$locked || $isHarvestItem">
                                            <option value="kg">kg</option>
                                            <option value="litros">{{ __('litros') }}</option>
                                            <option value="centilitros">{{ __('centilitros') }}</option>
                                            <option value="botellas">{{ __('botellas') }}</option>
                                            <option value="cajas">{{ __('cajas') }}</option>
                                            <option value="unidades">{{ __('unidades') }}</option>
                                        </flux:select>
                                    </flux:field>
                                </div>

                                {{-- Precio --}}
                                <div class="md:col-span-3">
                                    <flux:field>
                                        <flux:label class="text-xs">{{ __('Precio / unidad') }} <span class="text-red-500">*</span></flux:label>
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
                                        <flux:label class="text-xs">{{ __('Descuento %') }}</flux:label>
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
                                        <flux:label class="text-xs">{{ __('Impuesto') }}</flux:label>
                                        <flux:select wire:model.live="items.{{ $index }}.tax_id" class="text-sm" :disabled="$locked">
                                            <option value="">{{ __('Sin impuesto') }}</option>
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
                                    <span class="text-zinc-500">{{ __('Subtotal:') }} <strong class="text-zinc-900">{{ number_format($itemSubtotal, 2) }} €</strong></span>
                                    @if($itemDiscount > 0)
                                        <span class="text-zinc-500">{{ __('Dto:') }} <strong class="text-red-600">-{{ number_format($itemDiscAmt, 2) }} €</strong></span>
                                    @endif
                                    <span class="text-zinc-500">{{ __('Base:') }} <strong class="text-zinc-900">{{ number_format($itemBase, 2) }} €</strong></span>
                                    @if($selectedTax)
                                        <span class="text-zinc-500">{{ $selectedTax->name }} ({{ number_format($taxRate, 2) }}%): <strong class="text-zinc-900">{{ number_format($itemTaxAmt, 2) }} €</strong></span>
                                    @endif
                                    <span class="ml-auto text-zinc-600 font-semibold">{{ __('Total:') }} <strong class="text-base text-green-600">{{ number_format($itemTotal, 2) }} €</strong></span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-10 border-2 border-dashed border-zinc-300 rounded-xl">
                            <p class="text-zinc-500">{{ __('No hay líneas en el albarán.') }}</p>
                        </div>
                    @endforelse

                    @if(!$this->isLocked && $invoice->status === 'draft')
                        <div class="flex justify-center pt-4 border-t border-zinc-200 mt-4">
                            <flux:button type="button" wire:click="addItem" variant="outline" icon="plus">
                                {{ __('Añadir concepto manual') }}
                            </flux:button>
                        </div>
                    @endif
                </div>
            </x-agro.form-section>

            {{-- Observaciones --}}
            <x-agro.form-section :title="__('Observaciones')">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:field>
                        <flux:label>{{ __('Observaciones internas') }}</flux:label>
                        <flux:textarea wire:model="observations" rows="3" :placeholder="__('Notas internas...')" :disabled="$this->isLocked" />
                        <flux:error name="observations" />
                    </flux:field>
                    <flux:field>
                        <flux:label>{{ __('Observaciones en documento') }}</flux:label>
                        <flux:textarea wire:model="observations_invoice" rows="3" :placeholder="__('Texto que aparecerá en el albarán y factura...')" :disabled="$this->isLocked" />
                        <flux:error name="observations_invoice" />
                    </flux:field>
                </div>
            </x-agro.form-section>

            {{-- Totales --}}
            <x-agro.form-section :title="__('Totales')">
                <div class="bg-zinc-50 rounded-lg p-6">
                    <div class="flex flex-wrap gap-8 items-end">
                        <div>
                            <p class="text-sm text-zinc-500 mb-1">{{ __('Base imponible') }}</p>
                            <p class="text-xl font-bold text-zinc-900">{{ number_format($this->subtotal, 2) }} €</p>
                        </div>
                        @if($this->discountAmount > 0)
                            <div>
                                <p class="text-sm text-zinc-500 mb-1">{{ __('Descuento') }}</p>
                                <p class="text-xl font-bold text-red-600">-{{ number_format($this->discountAmount, 2) }} €</p>
                            </div>
                        @endif
                        <div>
                            <p class="text-sm text-zinc-500 mb-1">{{ __('Impuestos') }}</p>
                            <p class="text-xl font-bold text-zinc-900">{{ number_format($this->taxAmount, 2) }} €</p>
                        </div>
                        <div class="ml-auto text-right">
                            <p class="text-sm text-zinc-500 mb-1">{{ __('Total') }}</p>
                            <p class="text-3xl font-bold text-green-600">{{ number_format($this->totalAmount, 2) }} €</p>
                        </div>
                    </div>
                </div>
            </x-agro.form-section>

            {{-- Acciones --}}
            @if(!$this->isLocked)
                <div class="flex justify-between items-center gap-4 pt-2">
                    <div>
                        @if($invoice->status === 'draft')
                            <flux:button
                                type="button"
                                wire:click="openInvoiceModal"
                                variant="primary"
                                icon="paper-airplane"
                            >
                                {{ __('Emitir factura') }}
                            </flux:button>
                        @endif
                    </div>
                    <div class="flex gap-3">
                        <flux:button
                            type="button"
                            variant="outline"
                            wire:click="cancel"
                            wire:confirm="{{ __('¿Descartar los cambios no guardados?') }}"
                        >
                            {{ __('Descartar cambios') }}
                        </flux:button>
                        <flux:button type="submit" variant="primary">
                            {{ __('Guardar cambios') }}
                        </flux:button>
                    </div>
                </div>
            @else
                <div class="flex justify-end pt-2">
                    <flux:button
                        type="button"
                        variant="outline"
                        href="{{ roleRoute('viticulturist.invoices.index') }}"
                        wire:navigate
                        icon="arrow-left"
                    >
                        {{ __('Volver al listado') }}
                    </flux:button>
                </div>
            @endif
        </form>
    </x-agro.card>

    {{-- Modal: Cambio de estado de entrega (mueve stock) --}}
    @if($showDeliveryModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
             x-data x-on:keydown.escape.window="$wire.closeDeliveryModal()">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md" @click.stop>
                <div class="px-6 py-4 border-b border-zinc-200 flex items-center justify-between">
                    <h3 class="text-xl font-bold text-zinc-900">
                        {{ $pendingDeliveryStatus === 'delivered' ? __('Confirmar entrega') : __('Cancelar entrega') }}
                    </h3>
                    <flux:button wire:click="closeDeliveryModal" variant="ghost" size="sm" icon="x-mark" />
                </div>
                <div class="p-6 space-y-4">
                    <div class="bg-zinc-50 rounded-lg p-4 grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <p class="text-zinc-500">{{ __('Albarán') }}</p>
                            <p class="font-mono font-semibold text-zinc-900">{{ $invoice->delivery_note_code }}</p>
                        </div>
                        <div>
                            <p class="text-zinc-500">{{ __('Cliente') }}</p>
                            <p class="font-semibold text-zinc-900">{{ $invoice->client?->full_name ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-zinc-500">{{ __('Total') }}</p>
                            <p class="font-bold text-green-600 text-base">{{ number_format($this->totalAmount, 2) }} €</p>
                        </div>
                    </div>
                    @if($pendingDeliveryStatus === 'delivered')
                        <flux:callout variant="warning">
                            {{ __('El stock pasará de') }} <strong>{{ __('reservado → vendido') }}</strong>. {{ __('Esta acción bloquea la edición del contenido.') }}
                        </flux:callout>
                    @else
                        <flux:callout variant="danger">
                            {{ __('El stock se restaurará a') }} <strong>{{ __('disponible') }}</strong>. {{ __('El albarán quedará cancelado.') }}
                        </flux:callout>
                    @endif
                </div>
                <div class="flex justify-end gap-3 px-6 py-4 bg-zinc-50 border-t border-zinc-200 rounded-b-2xl">
                    <flux:button wire:click="closeDeliveryModal" variant="outline"
                        wire:loading.attr="disabled" wire:target="confirmDeliveryStatus">{{ __('Cancelar') }}</flux:button>
                    <flux:button wire:click="confirmDeliveryStatus"
                        variant="{{ $pendingDeliveryStatus === 'delivered' ? 'primary' : 'danger' }}"
                        icon="{{ $pendingDeliveryStatus === 'delivered' ? 'check-circle' : 'x-circle' }}"
                        wire:loading.attr="disabled" wire:target="confirmDeliveryStatus">
                        <span wire:loading.remove wire:target="confirmDeliveryStatus">
                            {{ $pendingDeliveryStatus === 'delivered' ? __('Confirmar entrega') : __('Confirmar cancelación') }}
                        </span>
                        <span wire:loading wire:target="confirmDeliveryStatus">{{ __('Guardando...') }}</span>
                    </flux:button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal: Fecha de cobro --}}
    @if($showPaymentDateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
             x-data x-on:keydown.escape.window="$wire.closePaymentDateModal()">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm" @click.stop>
                <div class="px-6 py-4 border-b border-zinc-200 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-zinc-900">{{ __('Fecha de cobro') }}</h3>
                    <flux:button wire:click="closePaymentDateModal" variant="ghost" size="sm" icon="x-mark" />
                </div>
                <div class="p-6 space-y-4">
                    <p class="text-sm text-zinc-600">{{ __('Indica la fecha en que se realizó el cobro.') }}</p>
                    <flux:field>
                        <flux:label required>{{ __('Fecha de cobro') }}</flux:label>
                        <flux:input wire:model="payment_date" type="date" required />
                        <flux:error name="payment_date" />
                    </flux:field>
                </div>
                <div class="flex justify-end gap-3 px-6 py-4 bg-zinc-50 border-t border-zinc-200 rounded-b-2xl">
                    <flux:button wire:click="closePaymentDateModal" variant="outline"
                        wire:loading.attr="disabled" wire:target="confirmPaymentDate">{{ __('Cancelar') }}</flux:button>
                    <flux:button wire:click="confirmPaymentDate" variant="primary" icon="check"
                        wire:loading.attr="disabled" wire:target="confirmPaymentDate">
                        <span wire:loading.remove wire:target="confirmPaymentDate">{{ __('Confirmar') }}</span>
                        <span wire:loading wire:target="confirmPaymentDate">{{ __('Guardando...') }}</span>
                    </flux:button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal: Confirmar emisión de factura --}}
    @if($showInvoiceModal)
        <div class="fixed z-50 inset-0 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-black/50" wire:click="closeInvoiceModal"></div>
                <div class="relative bg-white rounded-xl border border-zinc-200 shadow-xl max-w-md w-full p-6 z-10" wire:click.stop>

                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-bold text-zinc-900">{{ __('Emitir factura') }}</h3>
                        <flux:button wire:click="closeInvoiceModal" variant="ghost" size="sm" icon="x-mark" />
                    </div>

                    {{-- Resumen del documento --}}
                    <div class="bg-zinc-50 rounded-lg p-4 mb-5 grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <p class="text-zinc-500">{{ __('Albarán') }}</p>
                            <p class="font-mono font-semibold text-zinc-900">{{ $invoice->delivery_note_code }}</p>
                        </div>
                        <div>
                            <p class="text-zinc-500">{{ __('Número de factura') }}</p>
                            <p class="font-mono font-semibold text-zinc-900">{{ $invoice->invoice_number }}</p>
                        </div>
                        <div>
                            <p class="text-zinc-500">{{ __('Cliente') }}</p>
                            <p class="font-semibold text-zinc-900">{{ $invoice->client->full_name ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-zinc-500">{{ __('Total') }}</p>
                            <p class="font-bold text-green-600 text-base">{{ number_format($this->totalAmount, 2) }} €</p>
                        </div>
                    </div>

                    {{-- Fecha de factura --}}
                    <flux:field class="mb-4">
                        <flux:label required>{{ __('Fecha de la factura') }}</flux:label>
                        <flux:input
                            wire:model="invoice_date_modal"
                            type="date"
                            required
                        />
                        <flux:error name="invoice_date_modal" />
                    </flux:field>

                    <flux:callout variant="warning" class="mb-5">
                        {{ __('Al emitir, el stock reservado pasa a') }} <strong>{{ __('vendido') }}</strong> {{ __('y el número de factura queda bloqueado.') }}
                    </flux:callout>

                    <div class="flex justify-end gap-3">
                        <flux:button wire:click="closeInvoiceModal" variant="outline">{{ __('Cancelar') }}</flux:button>
                        <flux:button wire:click="markAsSent" variant="primary" icon="paper-airplane">
                            {{ __('Emitir factura') }}
                        </flux:button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
