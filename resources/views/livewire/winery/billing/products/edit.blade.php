<div class="space-y-6 animate-fade-in">

    {{-- Cabecera --}}
    <x-agro.page-header
        title="{{ $invoice->invoice_number ? 'Factura ' . $invoice->invoice_number : 'Albarán ' . ($invoice->delivery_note_code ?? '') }}"
        :description="($invoice->invoice_number ? '' : 'Borrador — pendiente de facturar') . ' · ' . ($invoice->client?->full_name ?? '')"
    >
        <x-slot:actions>
            <flux:button href="{{ route('winery.invoices.products.index') }}" variant="outline" icon="arrow-left">
                Volver
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Avisos de estado --}}
    @if ($isLocked)
        <flux:callout variant="{{ $invoice->status === 'cancelled' ? 'danger' : 'warning' }}" icon="lock-closed">
            <strong>Factura {{ $invoice->status === 'cancelled' ? 'cancelada' : 'entregada' }}.</strong>
            {{ $invoice->status === 'cancelled' ? 'No se puede modificar.' : 'Solo puedes actualizar el estado de cobro.' }}
        </flux:callout>
    @elseif ($isInvoiced)
        <flux:callout variant="info">
            <strong>Factura emitida.</strong>
            El número de factura y las líneas están bloqueados. Para anular necesitas una factura rectificativa.
        </flux:callout>
    @endif

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
                        @if ($invoice->status === 'draft')
                            <flux:input value="— (se asignará al emitir)" disabled
                                class="bg-zinc-50 font-mono text-zinc-400 cursor-not-allowed" />
                        @else
                            <flux:input value="{{ $invoice->invoice_number }}" disabled
                                class="bg-zinc-100 font-mono font-semibold cursor-not-allowed" />
                        @endif
                    </flux:field>

                    <flux:field>
                        <flux:label>Fecha albarán</flux:label>
                        <flux:input wire:model="delivery_note_date" type="date"
                            :disabled="$isLocked || $isInvoiced" />
                        <flux:error name="delivery_note_date" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Fecha factura</flux:label>
                        <flux:input wire:model="invoice_date" type="date"
                            :disabled="$isLocked" />
                        <flux:error name="invoice_date" />
                    </flux:field>
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

            {{-- Estados --}}
            <x-agro.form-section title="Estados">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:field>
                        <flux:label required>Estado de cobro</flux:label>
                        <flux:select wire:model="payment_status" required :disabled="$invoice->status === 'cancelled'">
                            <option value="unpaid">Pendiente de cobro</option>
                            <option value="partial">Pago parcial</option>
                            <option value="paid">Cobrada</option>
                        </flux:select>
                        <flux:error name="payment_status" />
                        <p class="mt-1 text-xs text-zinc-400">El estado de pago no afecta al stock.</p>
                    </flux:field>

                    <flux:field>
                        <flux:label>Forma de pago</flux:label>
                        <flux:select wire:model="payment_type" :disabled="$invoice->status === 'cancelled'">
                            <option value="">Sin especificar</option>
                            <option value="cash">Efectivo</option>
                            <option value="transfer">Transferencia</option>
                            <option value="check">Cheque</option>
                            <option value="other">Otro</option>
                        </flux:select>
                    </flux:field>
                </div>
            </x-agro.form-section>

            {{-- Añadir producto (solo borrador, no bloqueado) --}}
            @if (!$isLocked && $invoice->status === 'draft')
                <x-agro.form-section title="Añadir producto">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-3">
                            <flux:field>
                                <flux:label>Lote de vino</flux:label>
                                <flux:select
                                    wire:model.live="selectedLotId"
                                    wire:change="addProductToInvoice"
                                >
                                    <option value="">-- Selecciona un lote con stock disponible --</option>
                                    @foreach ($wineLots as $lot)
                                        <option value="{{ $lot->id }}">
                                            {{ $lot->name }}
                                            @if ($lot->vintage) ({{ $lot->vintage }}) @endif
                                            – Disp: {{ number_format($lot->available_quantity, 0) }} {{ $lot->unit ?? 'ud' }}
                                            @if ($lot->price_per_unit)
                                                ({{ number_format($lot->price_per_unit, 2) }} €/ud)
                                            @endif
                                        </option>
                                    @endforeach
                                </flux:select>
                                <p class="mt-1 text-xs text-zinc-400">Solo lotes con stock disponible. La cantidad se puede ajustar en las líneas.</p>
                            </flux:field>
                        </div>
                    </div>

                    @if ($wineLots->isEmpty())
                        <flux:callout variant="info" class="mt-4">
                            No hay lotes con stock disponible para añadir.
                        </flux:callout>
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

                            {{-- Cabecera ítem --}}
                            <div class="flex justify-between items-start mb-3">
                                <div class="flex items-center gap-2">
                                    <h4 class="text-base font-bold text-zinc-900">Línea #{{ $index + 1 }}</h4>
                                    @if ($isWineItem)
                                        <flux:badge color="purple" size="sm">Vino</flux:badge>
                                    @endif
                                </div>
                                @if (!$locked)
                                    <flux:button type="button" wire:click="removeItem({{ $index }})"
                                        variant="danger" size="sm" icon="trash">
                                        Eliminar
                                    </flux:button>
                                @endif
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                                {{-- Nombre / Concepto --}}
                                <div class="md:col-span-12">
                                    <flux:field>
                                        <flux:label class="text-xs">Concepto <span class="text-red-500">*</span></flux:label>
                                        <flux:input
                                            wire:model="items.{{ $index }}.name"
                                            placeholder="Ej: Rioja Reserva 2021..."
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
                                        <flux:textarea wire:model="items.{{ $index }}.description"
                                            rows="2" class="text-sm" :disabled="$locked" />
                                    </flux:field>
                                </div>

                                {{-- SKU --}}
                                <div class="md:col-span-4">
                                    <flux:field>
                                        <flux:label class="text-xs">SKU / Código</flux:label>
                                        <flux:input wire:model="items.{{ $index }}.sku"
                                            class="text-sm" :disabled="$locked" />
                                    </flux:field>
                                </div>

                                {{-- Cantidad --}}
                                <div class="md:col-span-3">
                                    <flux:field>
                                        <flux:label class="text-xs">Cantidad <span class="text-red-500">*</span></flux:label>
                                        <flux:input
                                            wire:model.live="items.{{ $index }}.quantity"
                                            type="number" step="0.001" min="0.001"
                                            @if ($isWineItem && $availableQty !== null && !$locked)
                                                max="{{ $availableQty }}"
                                            @endif
                                            class="text-sm"
                                            :disabled="$locked"
                                        />
                                        <flux:error name="items.{{ $index }}.quantity" />

                                        @if ($isWineItem && $availableQty !== null && !$locked)
                                            @php $exceedsStock = $qty > $availableQty; @endphp
                                            <p class="mt-1 text-xs {{ $exceedsStock ? 'text-red-600 font-semibold' : 'text-zinc-500' }}">
                                                Disponible: {{ number_format($availableQty, 0) }} ud
                                                @if ($exceedsStock) — ¡Supera el stock! @endif
                                            </p>
                                        @endif
                                    </flux:field>
                                </div>

                                {{-- Precio --}}
                                <div class="md:col-span-3">
                                    <flux:field>
                                        <flux:label class="text-xs">Precio/ud (€) <span class="text-red-500">*</span></flux:label>
                                        <flux:input wire:model.live="items.{{ $index }}.unit_price"
                                            type="number" step="0.0001" min="0" class="text-sm" :disabled="$locked" />
                                        <flux:error name="items.{{ $index }}.unit_price" />
                                    </flux:field>
                                </div>

                                {{-- Descuento --}}
                                <div class="md:col-span-3">
                                    <flux:field>
                                        <flux:label class="text-xs">Descuento %</flux:label>
                                        <flux:input wire:model.live="items.{{ $index }}.discount_percentage"
                                            type="number" step="0.01" min="0" max="100" class="text-sm" :disabled="$locked" />
                                    </flux:field>
                                </div>

                                {{-- Impuesto --}}
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

                            {{-- Totales de línea --}}
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

            {{-- Acciones --}}
            <div class="flex justify-between items-center gap-4 pt-2">
                <div>
                    @if ($invoice->status === 'draft' && !$isLocked)
                        <flux:button type="button" wire:click="openEmitirModal" variant="primary" icon="paper-airplane">
                            Emitir factura
                        </flux:button>
                    @elseif ($isInvoiced)
                        <flux:badge color="blue" size="lg">Factura emitida</flux:badge>
                    @endif
                </div>

                <div class="flex gap-3">
                    @if ($invoice->status !== 'cancelled')
                        <flux:button type="button" variant="outline" href="{{ route('winery.invoices.products.index') }}">
                            Cancelar
                        </flux:button>
                        <flux:button type="submit" variant="primary">
                            {{ $isLocked ? 'Guardar estado de cobro' : 'Guardar cambios' }}
                        </flux:button>
                    @else
                        <flux:button type="button" variant="outline" href="{{ route('winery.invoices.products.index') }}">
                            Volver al listado
                        </flux:button>
                    @endif
                </div>
            </div>
        </form>
    </x-agro.card>

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
                        Al emitir se asignará el <strong>número de factura</strong> secuencial y el documento quedará bloqueado.
                    </flux:callout>
                </div>

                <div class="flex justify-end gap-3 px-6 py-4 bg-zinc-50 border-t border-zinc-200 rounded-b-2xl">
                    <flux:button wire:click="closeEmitirModal" variant="outline">Cancelar</flux:button>
                    <flux:button wire:click="markAsSent" variant="primary" icon="paper-airplane">
                        Emitir factura
                    </flux:button>
                </div>
            </div>
        </div>
    @endif
</div>
