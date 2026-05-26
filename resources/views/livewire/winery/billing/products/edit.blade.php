<div class="space-y-6 animate-fade-in">

    {{-- Cabecera --}}
    <x-agro.page-header
        title="{{ $invoice->invoice_number ? 'Factura ' . $invoice->invoice_number : 'Albarán ' . ($invoice->delivery_note_code ?? '') }}"
        :description="'Alb: ' . ($invoice->delivery_note_code ?? '—') . ' · ' . ($invoice->client?->full_name ?? '')"
    >
        <x-slot:actions>
            <flux:button href="{{ roleRoute('invoices.products.index') }}" variant="outline" icon="arrow-left" wire:navigate>
                Volver
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
                    <flux:select wire:model="delivery_status" :disabled="$isLocked">
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
                        <option value="partial">{{ __('Pago parcial') }}</option>
                        <option value="paid">{{ __('Cobrada') }}</option>
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
                @if ($invoice->invoice_number)
                    <p class="font-mono font-bold text-zinc-900 text-sm">{{ $invoice->invoice_number }}</p>
                    @if ($invoice->invoice_date)
                        <p class="text-xs text-zinc-400">{{ $invoice->invoice_date->format('d/m/Y') }}</p>
                    @endif
                    <flux:badge color="blue" size="sm">{{ __('Emitida') }}</flux:badge>
                @elseif ($invoice->status === 'cancelled')
                    <flux:badge color="red" size="sm">{{ __('Cancelada') }}</flux:badge>
                @else
                    <flux:badge color="zinc" size="sm">{{ __('Borrador') }}</flux:badge>
                    <p class="text-xs text-zinc-400">{{ __('Emite desde el listado') }}</p>
                @endif
            </div>

            {{-- Botón guardar --}}
            @if ($invoice->status !== 'cancelled')
                <div class="flex-shrink-0">
                    <flux:button type="button" variant="primary" size="sm"
                        wire:click="saveStatuses"
                        wire:loading.attr="disabled" wire:target="saveStatuses,confirmPaymentDate,confirmDeliveryStatus">
                        <span wire:loading.remove wire:target="saveStatuses,confirmPaymentDate,confirmDeliveryStatus">{{ __('Guardar estados') }}</span>
                        <span wire:loading wire:target="saveStatuses,confirmPaymentDate,confirmDeliveryStatus">{{ __('Guardando...') }}</span>
                    </flux:button>
                </div>
            @endif
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
            <strong>{{ __('Factura emitida.') }}</strong>
            Las líneas están bloqueadas. Para corregir errores usa una factura rectificativa.
        </flux:callout>
    @endif

    {{-- ── Formulario de contenido ───────────────────────────────────────────── --}}
    <x-agro.card>
        <form wire:submit="save" class="space-y-8">

            {{-- Documento --}}
            <x-agro.form-section title="{{ __('Documento') }}">
                <div class="grid grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>{{ __('Código de albarán') }}</flux:label>
                        <flux:input value="{{ $invoice->delivery_note_code }}" disabled
                            class="bg-zinc-100 font-mono font-semibold cursor-not-allowed" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Número de factura') }}</flux:label>
                        <flux:input value="{{ $invoice->invoice_number ?? '— (pendiente de emitir)' }}" disabled
                            class="{{ $invoice->invoice_number ? 'bg-zinc-100 font-mono font-semibold' : 'bg-zinc-50 text-zinc-400' }} cursor-not-allowed" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Fecha de pedido') }}</flux:label>
                        <flux:input value="{{ $invoice->order_date ? $invoice->order_date->format('d/m/Y') : '—' }}" disabled
                            class="bg-zinc-100 cursor-not-allowed" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Fecha de albarán') }}</flux:label>
                        <flux:input value="{{ $invoice->delivery_note_date ? $invoice->delivery_note_date->format('d/m/Y') : '—' }}" disabled
                            class="bg-zinc-100 cursor-not-allowed" />
                    </flux:field>
                </div>

                {{-- Factura regalo --}}
                <div class="mt-4">
                    <flux:checkbox wire:model.live="is_gift" :label="__('Factura regalo')" :description="__('Importes = 0, stock se deduce igualmente')" :disabled="$isLocked" />
                </div>
            </x-agro.form-section>

            {{-- Cliente --}}
            <x-agro.form-section title="{{ __('Cliente') }}">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:field>
                        <flux:label required>{{ __('Cliente') }}</flux:label>
                        <flux:select wire:model="client_id" required :disabled="$isLocked || $isInvoiced">
                            <option value="">{{ __('Selecciona un cliente') }}</option>
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
                <x-agro.form-section title="{{ __('Añadir producto') }}">
                    <flux:field>
                        <flux:label>{{ __('Lote de vino') }}</flux:label>
                        <flux:select wire:model.live="selectedLotId" wire:change="addProductToInvoice">
                            <option value="">{{ __('-- Selecciona un lote con stock disponible --') }}</option>
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
            <x-agro.form-section title="{{ __('Líneas del albarán') }}">
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
                                        <flux:badge color="purple" size="sm">{{ __('Vino') }}</flux:badge>
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
                                        <flux:label class="text-xs">{{ __('Concepto') }} <span class="text-red-500">*</span></flux:label>
                                        <flux:input wire:model="items.{{ $index }}.name"
                                            placeholder="{{ __('Ej: Rioja Reserva 2021...') }}" class="text-sm" :disabled="$locked" />
                                        <flux:error name="items.{{ $index }}.name" />
                                    </flux:field>
                                </div>
                                <div class="md:col-span-8">
                                    <flux:field>
                                        <flux:label class="text-xs">{{ __('Descripción') }}</flux:label>
                                        <flux:textarea wire:model="items.{{ $index }}.description"
                                            rows="2" class="text-sm" :disabled="$locked" />
                                    </flux:field>
                                </div>
                                <div class="md:col-span-4">
                                    <flux:field>
                                        <flux:label class="text-xs">{{ __('SKU / Código') }}</flux:label>
                                        <flux:input wire:model="items.{{ $index }}.sku"
                                            class="text-sm" :disabled="$locked" />
                                    </flux:field>
                                </div>
                                <div class="md:col-span-3">
                                    <flux:field>
                                        <flux:label class="text-xs">{{ __('Cantidad') }} <span class="text-red-500">*</span></flux:label>
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
                                        <flux:label class="text-xs">{{ __('Precio/ud (€)') }} <span class="text-red-500">*</span></flux:label>
                                        <flux:input wire:model.live="items.{{ $index }}.unit_price"
                                            type="number" step="0.0001" min="0" class="text-sm" :disabled="$locked" />
                                        <flux:error name="items.{{ $index }}.unit_price" />
                                    </flux:field>
                                </div>
                                <div class="md:col-span-3">
                                    <flux:field>
                                        <flux:label class="text-xs">{{ __('Descuento %') }}</flux:label>
                                        <flux:input wire:model.live="items.{{ $index }}.discount_percentage"
                                            type="number" step="0.01" min="0" max="100" class="text-sm" :disabled="$locked" />
                                    </flux:field>
                                </div>
                                <div class="md:col-span-3">
                                    <flux:field>
                                        <flux:label class="text-xs">{{ __('Impuesto') }}</flux:label>
                                        <flux:select wire:model.live="items.{{ $index }}.tax_id"
                                            class="text-sm" :disabled="$locked">
                                            <option value="">{{ __('Sin impuesto') }}</option>
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
                            <p class="text-zinc-500">{{ __('No hay líneas en el albarán.') }}</p>
                        </div>
                    @endforelse

                    @if (!$isLocked && !$isInvoiced)
                        <div class="flex justify-center pt-4 border-t border-zinc-200 mt-4">
                            <flux:button type="button" wire:click="addItem" variant="outline" icon="plus">{{ __('Añadir concepto manual') }}</flux:button>
                        </div>
                    @endif
                </div>
            </x-agro.form-section>

            {{-- Resumen de totales --}}
            @if(count($items) > 0)
            <x-agro.form-section title="{{ __('Resumen') }}">
                <div class="flex justify-end">
                    <div class="w-full max-w-sm space-y-2 text-sm">
                        <div class="flex justify-between text-zinc-600">
                            <span>{{ __('Subtotal') }}</span>
                            <span class="font-medium">{{ number_format($this->subtotal, 2, ',', '.') }} €</span>
                        </div>
                        @if($this->discountAmount > 0)
                        <div class="flex justify-between text-red-600">
                            <span>{{ __('Descuento') }}</span>
                            <span class="font-medium">-{{ number_format($this->discountAmount, 2, ',', '.') }} €</span>
                        </div>
                        <div class="flex justify-between text-zinc-600">
                            <span>{{ __('Base imponible') }}</span>
                            <span class="font-medium">{{ number_format($this->subtotal - $this->discountAmount, 2, ',', '.') }} €</span>
                        </div>
                        @endif
                        <div class="flex justify-between text-zinc-600">
                            <span>{{ __('IVA') }}</span>
                            <span class="font-medium">{{ number_format($this->taxAmount, 2, ',', '.') }} €</span>
                        </div>
                        <div class="flex justify-between text-base font-bold text-zinc-900 border-t border-zinc-200 pt-2">
                            <span>{{ __('Total') }}</span>
                            @if($is_gift)
                                <span class="text-pink-600">0,00 € <span class="text-xs font-normal">{{ __('(regalo)') }}</span></span>
                            @else
                                <span class="text-agro-700">{{ number_format($this->totalAmount, 2, ',', '.') }} €</span>
                            @endif
                        </div>
                    </div>
                </div>
            </x-agro.form-section>
            @endif

            {{-- Observaciones --}}
            <x-agro.form-section title="{{ __('Observaciones') }}">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:field>
                        <flux:label>{{ __('Observaciones internas') }}</flux:label>
                        <flux:textarea wire:model="observations" rows="3"
                            placeholder="{{ __('Notas internas (no aparecen en el documento)...') }}"
                            :disabled="$invoice->status === 'cancelled'" />
                        <flux:error name="observations" />
                    </flux:field>
                    <flux:field>
                        <flux:label>{{ __('Observaciones en documento') }}</flux:label>
                        <flux:textarea wire:model="observations_invoice" rows="3"
                            placeholder="{{ __('Texto que aparecerá en el albarán y la factura...') }}"
                            :disabled="$invoice->status === 'cancelled'" />
                        <flux:error name="observations_invoice" />
                    </flux:field>
                </div>
            </x-agro.form-section>

            {{-- Acciones del formulario --}}
            @if (!$isLocked)
                <div class="flex justify-end gap-3 pt-2">
                    <flux:button type="button" variant="outline"
                        href="{{ roleRoute('invoices.products.index') }}" wire:navigate>
                        Cancelar
                    </flux:button>
                    <flux:button type="submit" variant="primary">{{ __('Guardar cambios') }}</flux:button>
                </div>
            @else
                <div class="flex justify-end pt-2">
                    <flux:button type="button" variant="outline"
                        href="{{ roleRoute('invoices.products.index') }}" wire:navigate icon="arrow-left">
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
                    @if ($pendingDeliveryStatus === 'delivered')
                        <flux:callout variant="warning">
                            El stock pasará de <strong>{{ __('reservado → vendido') }}</strong>. Esta acción bloquea la edición del contenido.
                        </flux:callout>
                    @else
                        <flux:callout variant="danger">
                            El stock se restaurará a <strong>{{ __('disponible') }}</strong>. El albarán quedará cancelado.
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
                        <span wire:loading.remove wire:target="confirmDeliveryStatus">{{ $pendingDeliveryStatus === 'delivered' ? 'Confirmar entrega' : 'Confirmar cancelación' }}</span>
                        <span wire:loading wire:target="confirmDeliveryStatus">{{ __('Guardando...') }}</span>
                    </flux:button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal: Fecha de pago --}}
    @if ($showPaymentDateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
             x-data x-on:keydown.escape.window="$wire.closePaymentDateModal()">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm" @click.stop>
                <div class="px-6 py-4 border-b border-zinc-200 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-zinc-900">{{ __('Fecha de pago') }}</h3>
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

</div>
