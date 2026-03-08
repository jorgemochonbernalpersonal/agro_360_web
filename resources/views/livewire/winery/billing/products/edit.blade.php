<div class="space-y-6 animate-fade-in">

    {{-- Cabecera --}}
    <x-agro.page-header
        title="{{ $invoice->invoice_number ? 'Factura ' . $invoice->invoice_number : 'Albarán ' . ($invoice->delivery_note_code ?? '') }}"
        :description="'Alb: ' . ($invoice->delivery_note_code ?? '—') . ' · ' . ($invoice->client?->full_name ?? '')"
    >
        <x-slot:actions>
            <flux:button href="{{ route('winery.invoices.products.index') }}" variant="outline" icon="arrow-left" wire:navigate>
                Volver
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- ── Card de estados rápidos (fuera del form) ─────────────────────────── --}}
    @php
        $deliveryLabels = [
            'pending'    => ['label' => 'Pendiente',   'color' => 'zinc'],
            'in_transit' => ['label' => 'En tránsito', 'color' => 'sky'],
            'delivered'  => ['label' => 'Entregada',   'color' => 'green'],
            'cancelled'  => ['label' => 'Cancelada',   'color' => 'red'],
        ];
        $dl = $deliveryLabels[$invoice->delivery_status] ?? ['label' => ucfirst($invoice->delivery_status), 'color' => 'zinc'];
    @endphp

    <x-agro.card>
        <div class="grid grid-cols-1 md:grid-cols-3 divide-y md:divide-y-0 md:divide-x divide-zinc-100 gap-0">

            {{-- Col 1: Entrega --}}
            <div class="pb-5 md:pb-0 md:pr-6 space-y-3">
                <p class="text-[11px] font-bold uppercase tracking-widest text-zinc-400">Entrega</p>
                <div class="flex items-center gap-2 flex-wrap">
                    <flux:badge color="{{ $dl['color'] }}" size="sm">{{ $dl['label'] }}</flux:badge>
                    @if ($invoice->delivery_status === 'delivered' && $invoice->updated_at)
                        <span class="text-xs text-zinc-400">{{ $invoice->updated_at->format('d/m/Y') }}</span>
                    @endif
                </div>
                @if (!$isLocked && in_array($invoice->delivery_status, ['pending', 'in_transit']))
                    <div class="flex gap-2 flex-wrap">
                        <flux:button type="button" size="sm" variant="primary" icon="check-circle"
                            wire:click="openDeliveryModal('delivered')">
                            Entregar
                        </flux:button>
                        <flux:button type="button" size="sm" variant="danger" icon="x-circle"
                            wire:click="openDeliveryModal('cancelled')">
                            Cancelar
                        </flux:button>
                    </div>
                @endif
            </div>

            {{-- Col 2: Cobro --}}
            <div class="py-5 md:py-0 md:px-6 space-y-3">
                <p class="text-[11px] font-bold uppercase tracking-widest text-zinc-400">Cobro</p>
                @if ($invoice->status !== 'cancelled')
                    <div class="space-y-2">
                        <flux:select wire:model.live="payment_status" class="text-sm">
                            <option value="unpaid">Pendiente de cobro</option>
                            <option value="partial">Pago parcial</option>
                            <option value="paid">Cobrada</option>
                        </flux:select>
                        <flux:select wire:model="payment_type" class="text-sm">
                            <option value="">Sin especificar</option>
                            <option value="cash">Efectivo</option>
                            <option value="transfer">Transferencia</option>
                            <option value="check">Cheque</option>
                            <option value="other">Otro</option>
                        </flux:select>
                        <div x-data x-show="$wire.payment_status === 'paid'" x-cloak>
                            <flux:input wire:model="payment_date" type="date" class="text-sm"
                                placeholder="Fecha de cobro" />
                        </div>
                        <flux:button type="button" size="sm" variant="filled"
                            wire:click="updatePaymentStatus"
                            wire:loading.attr="disabled" wire:target="updatePaymentStatus">
                            <span wire:loading.remove wire:target="updatePaymentStatus">Guardar cobro</span>
                            <span wire:loading wire:target="updatePaymentStatus">Guardando...</span>
                        </flux:button>
                    </div>
                @else
                    <flux:badge color="red" size="sm">Cancelada</flux:badge>
                @endif
            </div>

            {{-- Col 3: Factura --}}
            <div class="pt-5 md:pt-0 md:pl-6 space-y-3">
                <p class="text-[11px] font-bold uppercase tracking-widest text-zinc-400">Factura</p>
                @if ($invoice->status === 'draft')
                    <div class="flex items-center gap-2">
                        <flux:badge color="zinc" size="sm">Borrador</flux:badge>
                        <span class="text-xs text-zinc-400">Nº pendiente</span>
                    </div>
                    @if (!$isLocked)
                        <flux:button type="button" size="sm" variant="primary" icon="paper-airplane"
                            wire:click="openEmitirModal">
                            Emitir factura
                        </flux:button>
                    @endif
                @elseif ($invoice->status === 'sent')
                    <div class="space-y-1">
                        <p class="text-sm font-mono font-bold text-zinc-900">{{ $invoice->invoice_number }}</p>
                        @if ($invoice->invoice_date)
                            <p class="text-xs text-zinc-400">{{ $invoice->invoice_date->format('d/m/Y') }}</p>
                        @endif
                    </div>
                    <flux:badge color="blue" size="sm">Emitida</flux:badge>
                @elseif ($invoice->status === 'cancelled')
                    <flux:badge color="red" size="sm">Cancelada</flux:badge>
                @endif
            </div>
        </div>
    </x-agro.card>

    {{-- Aviso si bloqueada --}}
    @if ($isLocked)
        <flux:callout variant="{{ $invoice->status === 'cancelled' ? 'danger' : 'warning' }}" icon="lock-closed">
            <strong>Factura {{ $invoice->status === 'cancelled' ? 'cancelada' : ($invoice->delivery_status === 'delivered' ? 'entregada' : 'cerrada') }}.</strong>
            El contenido del albarán no puede modificarse. Puedes actualizar el estado de cobro desde la tarjeta de arriba.
        </flux:callout>
    @elseif ($isInvoiced)
        <flux:callout variant="info">
            <strong>Factura emitida.</strong>
            Las líneas están bloqueadas. Para corregir errores usa una factura rectificativa.
        </flux:callout>
    @endif

    {{-- ── Formulario de contenido ───────────────────────────────────────────── --}}
    <x-agro.card>
        <form wire:submit="save" class="space-y-8">

            {{-- Documento --}}
            <x-agro.form-section title="Documento">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <flux:field>
                        <flux:label>Código de albarán</flux:label>
                        <flux:input value="{{ $invoice->delivery_note_code }}" disabled
                            class="bg-zinc-100 font-mono font-semibold cursor-not-allowed" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Número de factura</flux:label>
                        <flux:input value="{{ $invoice->invoice_number ?? '— (pendiente de emitir)' }}" disabled
                            class="{{ $invoice->invoice_number ? 'bg-zinc-100 font-mono font-semibold' : 'bg-zinc-50 text-zinc-400' }} cursor-not-allowed" />
                    </flux:field>

                    <flux:field>
                        <flux:label required>Fecha de pedido</flux:label>
                        <flux:input wire:model="order_date" type="date"
                            :disabled="$isLocked" />
                        <flux:error name="order_date" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Fecha de albarán</flux:label>
                        <flux:input wire:model="delivery_note_date" type="date"
                            :disabled="$isLocked || $isInvoiced" />
                        <flux:error name="delivery_note_date" />
                    </flux:field>
                </div>

                {{-- Factura regalo --}}
                <div class="mt-4">
                    <label class="flex items-center gap-3 cursor-pointer w-fit">
                        <flux:checkbox wire:model.live="is_gift" id="is_gift" :disabled="$isLocked" />
                        <span class="text-sm font-medium text-zinc-700">Factura regalo
                            <span class="text-xs font-normal text-zinc-400">(importes = 0, stock se deduce igualmente)</span>
                        </span>
                    </label>
                </div>
            </x-agro.form-section>

            {{-- Cliente --}}
            <x-agro.form-section title="Cliente">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:field>
                        <flux:label required>Cliente</flux:label>
                        <flux:select wire:model="client_id" required :disabled="$isLocked || $isInvoiced">
                            <option value="">Selecciona un cliente</option>
                            @foreach ($clients as $client)
                                <option value="{{ $client->id }}">{{ $client->full_name }}</option>
                            @endforeach
                        </flux:select>
                        <flux:error name="client_id" />
                    </flux:field>
                </div>
            </x-agro.form-section>

            {{-- Añadir producto (solo borrador, no bloqueado) --}}
            @if (!$isLocked && $invoice->status === 'draft')
                <x-agro.form-section title="Añadir producto">
                    <flux:field>
                        <flux:label>Lote de vino</flux:label>
                        <flux:select wire:model.live="selectedLotId" wire:change="addProductToInvoice">
                            <option value="">-- Selecciona un lote con stock disponible --</option>
                            @foreach ($wineLots as $lot)
                                <option value="{{ $lot->id }}">
                                    {{ $lot->name }}
                                    {{ $lot->vintage ? "({$lot->vintage})" : '' }}
                                    – Disp: {{ number_format($lot->available_quantity, 0) }} {{ $lot->unit ?? 'ud' }}
                                    {{ $lot->price_per_unit ? '(' . number_format($lot->price_per_unit, 2) . ' €/ud)' : '' }}
                                </option>
                            @endforeach
                        </flux:select>
                        <flux:description>Solo lotes con stock disponible. Ajusta la cantidad en las líneas.</flux:description>
                    </flux:field>

                    @if ($wineLots->isEmpty())
                        <flux:callout variant="info" class="mt-2">No hay lotes con stock disponible.</flux:callout>
                    @endif
                </x-agro.form-section>
            @endif

            {{-- Líneas del albarán --}}
            <x-agro.form-section title="Líneas del albarán">
                <div class="space-y-4">
                    @forelse ($items as $index => $item)
                        @php
                            $isWineItem   = !empty($item['wine_lot_id']);
                            $availableQty = isset($item['available_qty']) ? (float) $item['available_qty'] : null;
                            $locked       = $isLocked || $isInvoiced;

                            $qty     = (float)($item['quantity']            ?? 0);
                            $price   = (float)($item['unit_price']          ?? 0);
                            $discPct = (float)($item['discount_percentage'] ?? 0);
                            $taxObj  = $item['tax_id'] ? $availableTaxes->firstWhere('id', $item['tax_id']) : null;
                            $taxRate = $taxObj ? (float) $taxObj->rate : 0;
                            $sub     = $qty * $price;
                            $discAmt = $sub * ($discPct / 100);
                            $base    = $sub - $discAmt;
                            $taxAmt  = $base * ($taxRate / 100);
                            $total   = $base + $taxAmt;
                        @endphp

                        <div class="border-2 border-zinc-200 rounded-lg p-4 bg-white hover:border-agro-300 transition-colors shadow-xs">
                            <div class="flex justify-between items-start mb-3">
                                <div class="flex items-center gap-2">
                                    <h4 class="text-base font-bold text-zinc-900">Línea #{{ $index + 1 }}</h4>
                                    @if ($isWineItem)
                                        <flux:badge color="purple" size="sm">Vino</flux:badge>
                                    @endif
                                </div>
                                @if (!$locked)
                                    <flux:button type="button" wire:click="removeItem({{ $index }})"
                                        variant="danger" size="sm" icon="trash">Eliminar</flux:button>
                                @endif
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                                <div class="md:col-span-12">
                                    <flux:field>
                                        <flux:label class="text-xs">Concepto <span class="text-red-500">*</span></flux:label>
                                        <flux:input wire:model="items.{{ $index }}.name"
                                            placeholder="Ej: Rioja Reserva 2021..." class="text-sm" :disabled="$locked" />
                                        <flux:error name="items.{{ $index }}.name" />
                                    </flux:field>
                                </div>
                                <div class="md:col-span-8">
                                    <flux:field>
                                        <flux:label class="text-xs">Descripción</flux:label>
                                        <flux:textarea wire:model="items.{{ $index }}.description"
                                            rows="2" class="text-sm" :disabled="$locked" />
                                    </flux:field>
                                </div>
                                <div class="md:col-span-4">
                                    <flux:field>
                                        <flux:label class="text-xs">SKU / Código</flux:label>
                                        <flux:input wire:model="items.{{ $index }}.sku"
                                            class="text-sm" :disabled="$locked" />
                                    </flux:field>
                                </div>
                                <div class="md:col-span-3">
                                    <flux:field>
                                        <flux:label class="text-xs">Cantidad <span class="text-red-500">*</span></flux:label>
                                        <flux:input wire:model.live="items.{{ $index }}.quantity"
                                            type="number" step="0.001" min="0.001"
                                            class="text-sm" :disabled="$locked" />
                                        <flux:error name="items.{{ $index }}.quantity" />
                                        @if ($isWineItem && $availableQty !== null && !$locked)
                                            @php $exceedsStock = $qty > $availableQty; @endphp
                                            <p class="mt-1 text-xs {{ $exceedsStock ? 'text-red-600 font-semibold' : 'text-zinc-500' }}">
                                                Disp: {{ number_format($availableQty, 0) }} ud{{ $exceedsStock ? ' — ¡Supera el stock!' : '' }}
                                            </p>
                                        @endif
                                    </flux:field>
                                </div>
                                <div class="md:col-span-3">
                                    <flux:field>
                                        <flux:label class="text-xs">Precio/ud (€) <span class="text-red-500">*</span></flux:label>
                                        <flux:input wire:model.live="items.{{ $index }}.unit_price"
                                            type="number" step="0.0001" min="0" class="text-sm" :disabled="$locked" />
                                        <flux:error name="items.{{ $index }}.unit_price" />
                                    </flux:field>
                                </div>
                                <div class="md:col-span-3">
                                    <flux:field>
                                        <flux:label class="text-xs">Descuento %</flux:label>
                                        <flux:input wire:model.live="items.{{ $index }}.discount_percentage"
                                            type="number" step="0.01" min="0" max="100" class="text-sm" :disabled="$locked" />
                                    </flux:field>
                                </div>
                                <div class="md:col-span-3">
                                    <flux:field>
                                        <flux:label class="text-xs">Impuesto</flux:label>
                                        <flux:select wire:model.live="items.{{ $index }}.tax_id"
                                            class="text-sm" :disabled="$locked">
                                            <option value="">Sin impuesto</option>
                                            @foreach ($availableTaxes as $tax)
                                                <option value="{{ $tax->id }}">{{ $tax->name }} ({{ number_format($tax->rate, 2) }}%)</option>
                                            @endforeach
                                        </flux:select>
                                    </flux:field>
                                </div>
                            </div>

                            <div class="mt-3 pt-3 border-t border-zinc-200 bg-zinc-50 -mx-4 -mb-4 px-4 py-2 rounded-b-lg">
                                <div class="flex flex-wrap gap-x-6 gap-y-1 text-xs">
                                    <span class="text-zinc-500">Subtotal: <strong class="text-zinc-900">{{ number_format($sub, 2) }} €</strong></span>
                                    @if ($discPct > 0)
                                        <span class="text-zinc-500">Dto: <strong class="text-red-600">-{{ number_format($discAmt, 2) }} €</strong></span>
                                        <span class="text-zinc-500">Base: <strong class="text-zinc-900">{{ number_format($base, 2) }} €</strong></span>
                                    @endif
                                    @if ($taxObj)
                                        <span class="text-zinc-500">{{ $taxObj->name }} ({{ number_format($taxRate, 2) }}%): <strong class="text-zinc-900">{{ number_format($taxAmt, 2) }} €</strong></span>
                                    @endif
                                    <span class="ml-auto text-zinc-600 font-semibold">Total: <strong class="text-base text-green-600">{{ number_format($total, 2) }} €</strong></span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-10 border-2 border-dashed border-zinc-300 rounded-xl">
                            <p class="text-zinc-500">No hay líneas en el albarán.</p>
                        </div>
                    @endforelse

                    @if (!$isLocked && !$isInvoiced)
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
                        <flux:textarea wire:model="observations" rows="3"
                            placeholder="Notas internas (no aparecen en el documento)..."
                            :disabled="$invoice->status === 'cancelled'" />
                        <flux:error name="observations" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Observaciones en documento</flux:label>
                        <flux:textarea wire:model="observations_invoice" rows="3"
                            placeholder="Texto que aparecerá en el albarán y la factura..."
                            :disabled="$invoice->status === 'cancelled'" />
                        <flux:error name="observations_invoice" />
                    </flux:field>
                </div>
            </x-agro.form-section>

            {{-- Acciones del formulario --}}
            @if (!$isLocked)
                <div class="flex justify-end gap-3 pt-2">
                    <flux:button type="button" variant="outline"
                        href="{{ route('winery.invoices.products.index') }}" wire:navigate>
                        Cancelar
                    </flux:button>
                    <flux:button type="submit" variant="primary">
                        Guardar cambios
                    </flux:button>
                </div>
            @else
                <div class="flex justify-end pt-2">
                    <flux:button type="button" variant="outline"
                        href="{{ route('winery.invoices.products.index') }}" wire:navigate icon="arrow-left">
                        Volver al listado
                    </flux:button>
                </div>
            @endif

        </form>
    </x-agro.card>

    {{-- Modal: Cambio de estado de entrega --}}
    @if ($showDeliveryModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
             x-data x-on:keydown.escape.window="$wire.closeDeliveryModal()">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md" @click.stop>
                <div class="px-6 py-4 border-b border-zinc-200 flex items-center justify-between">
                    <h3 class="text-xl font-bold text-zinc-900">
                        {{ $pendingDeliveryStatus === 'delivered' ? 'Marcar como entregada' : 'Cancelar entrega' }}
                    </h3>
                    <flux:button wire:click="closeDeliveryModal" variant="ghost" size="sm" icon="x-mark" />
                </div>
                <div class="p-6 space-y-4">
                    <div class="bg-zinc-50 rounded-lg p-4 grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <p class="text-zinc-500">Albarán</p>
                            <p class="font-mono font-semibold text-zinc-900">{{ $invoice->delivery_note_code }}</p>
                        </div>
                        <div>
                            <p class="text-zinc-500">Cliente</p>
                            <p class="font-semibold text-zinc-900">{{ $invoice->client?->full_name ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-zinc-500">Total</p>
                            <p class="font-bold text-green-600 text-base">{{ number_format($this->totalAmount, 2) }} €</p>
                        </div>
                    </div>
                    @if ($pendingDeliveryStatus === 'delivered')
                        <flux:callout variant="warning">
                            El stock pasará de <strong>reservado → vendido</strong>. Esta acción bloquea la edición del contenido.
                        </flux:callout>
                    @else
                        <flux:callout variant="danger">
                            El stock se restaurará a <strong>disponible</strong>. El albarán quedará cancelado.
                        </flux:callout>
                    @endif
                </div>
                <div class="flex justify-end gap-3 px-6 py-4 bg-zinc-50 border-t border-zinc-200 rounded-b-2xl">
                    <flux:button wire:click="closeDeliveryModal" variant="outline">Cancelar</flux:button>
                    <flux:button wire:click="confirmDeliveryStatus"
                        variant="{{ $pendingDeliveryStatus === 'delivered' ? 'primary' : 'danger' }}"
                        icon="{{ $pendingDeliveryStatus === 'delivered' ? 'check-circle' : 'x-circle' }}">
                        {{ $pendingDeliveryStatus === 'delivered' ? 'Confirmar entrega' : 'Confirmar cancelación' }}
                    </flux:button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal: Emitir Factura --}}
    @if ($showEmitirModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
             x-data x-on:keydown.escape.window="$wire.closeEmitirModal()">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md" @click.stop>
                <div class="px-6 py-4 border-b border-zinc-200 flex items-center justify-between">
                    <h3 class="text-xl font-bold text-zinc-900">Emitir factura</h3>
                    <flux:button wire:click="closeEmitirModal" variant="ghost" size="sm" icon="x-mark" />
                </div>
                <div class="p-6 space-y-4">
                    <div class="bg-zinc-50 rounded-lg p-4 grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <p class="text-zinc-500">Albarán</p>
                            <p class="font-mono font-semibold text-zinc-900">{{ $invoice->delivery_note_code }}</p>
                        </div>
                        <div>
                            <p class="text-zinc-500">Cliente</p>
                            <p class="font-semibold text-zinc-900">{{ $invoice->client?->full_name ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-zinc-500">Total</p>
                            <p class="font-bold text-green-600 text-base">{{ number_format($this->totalAmount, 2) }} €</p>
                        </div>
                    </div>
                    <flux:field>
                        <flux:label required>Fecha de la factura</flux:label>
                        <flux:input wire:model="emitirDate" type="date" required />
                        <flux:error name="emitirDate" />
                    </flux:field>
                    <flux:callout variant="warning">
                        Se asignará el <strong>número de factura</strong> secuencial y el documento quedará bloqueado para edición.
                    </flux:callout>
                </div>
                <div class="flex justify-end gap-3 px-6 py-4 bg-zinc-50 border-t border-zinc-200 rounded-b-2xl">
                    <flux:button wire:click="closeEmitirModal" variant="outline"
                        wire:loading.attr="disabled" wire:target="markAsSent">Cancelar</flux:button>
                    <flux:button wire:click="markAsSent" variant="primary" icon="paper-airplane"
                        wire:loading.attr="disabled" wire:target="markAsSent">
                        <span wire:loading.remove wire:target="markAsSent">Emitir factura</span>
                        <span wire:loading wire:target="markAsSent">Emitiendo...</span>
                    </flux:button>
                </div>
            </div>
        </div>
    @endif
</div>
