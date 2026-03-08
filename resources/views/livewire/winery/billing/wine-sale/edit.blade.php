<div>
    <x-agro.form-card title="Editar Factura de Vino"
        description="{{ $invoice->invoice_number }} — {{ $invoice->client?->full_name }}"
        :back-url="route('winery.invoices.wine-sale.index')">

        @if ($isLocked)
            <flux:callout variant="warning" icon="exclamation-triangle">
                Esta factura no se puede editar porque
                @if ($invoice->status === 'cancelled') está cancelada.
                @else está marcada como entregada. @endif
            </flux:callout>
        @else

        <form wire:submit.prevent="save" class="space-y-8">

            <!-- Cabecera -->
            <x-agro.form-section title="Datos de la Factura">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <flux:field>
                        <flux:label for="client_id">Cliente *</flux:label>
                        <flux:select wire:model="client_id" id="client_id" required>
                            <option value="">Seleccionar cliente...</option>
                            @foreach ($clients as $client)
                                <option value="{{ $client->id }}">{{ $client->full_name }}</option>
                            @endforeach
                        </flux:select>
                        <flux:error name="client_id" />
                    </flux:field>

                    <flux:field>
                        <flux:label for="invoice_date">Fecha *</flux:label>
                        <flux:input wire:model="invoice_date" type="date" id="invoice_date" required />
                        <flux:error name="invoice_date" />
                    </flux:field>

                    <flux:field>
                        <flux:label for="payment_type">Forma de pago</flux:label>
                        <flux:select wire:model="payment_type" id="payment_type">
                            <option value="">Sin especificar</option>
                            <option value="cash">Efectivo</option>
                            <option value="transfer">Transferencia</option>
                            <option value="check">Cheque</option>
                            <option value="other">Otro</option>
                        </flux:select>
                    </flux:field>
                </div>

                <!-- Info de números (solo lectura) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                    <flux:field>
                        <flux:label>Nº Factura</flux:label>
                        <flux:input value="{{ $invoice->invoice_number }}" readonly />
                    </flux:field>
                    <flux:field>
                        <flux:label>Nº Albarán</flux:label>
                        <flux:input value="{{ $invoice->delivery_note_code }}" readonly />
                    </flux:field>
                </div>
            </x-agro.form-section>

            <!-- Líneas -->
            <x-agro.form-section title="Líneas de Vino">
                <div class="space-y-4">
                    @foreach ($lines as $i => $line)
                        <div class="grid grid-cols-12 gap-3 items-end p-4 bg-zinc-50 dark:bg-zinc-800/50 rounded-lg" wire:key="line-{{ $i }}">
                            <!-- Lote -->
                            <div class="col-span-12 md:col-span-4">
                                <flux:field>
                                    <flux:label>Producto *</flux:label>
                                    <flux:select wire:model.live="lines.{{ $i }}.wine_lot_id">
                                        <option value="">Seleccionar producto...</option>
                                        @foreach ($wineLots as $lot)
                                            <option value="{{ $lot->id }}">
                                                {{ $lot->name }}
                                                @if ($lot->vintage) ({{ $lot->vintage }}) @endif
                                                — Disp: {{ number_format($lot->available_quantity, 0) }} {{ $lot->unit }}
                                            </option>
                                        @endforeach
                                    </flux:select>
                                    <flux:error name="lines.{{ $i }}.wine_lot_id" />
                                </flux:field>
                            </div>

                            <!-- Cantidad -->
                            <div class="col-span-4 md:col-span-2">
                                <flux:field>
                                    <flux:label>Cantidad *</flux:label>
                                    <flux:input wire:model="lines.{{ $i }}.quantity" type="number" step="0.001" min="0.001" />
                                    <flux:error name="lines.{{ $i }}.quantity" />
                                </flux:field>
                            </div>

                            <!-- Precio unitario -->
                            <div class="col-span-4 md:col-span-2">
                                <flux:field>
                                    <flux:label>Precio/ud (€) *</flux:label>
                                    <flux:input wire:model="lines.{{ $i }}.unit_price" type="number" step="0.001" min="0" />
                                    <flux:error name="lines.{{ $i }}.unit_price" />
                                </flux:field>
                            </div>

                            <!-- IVA -->
                            <div class="col-span-4 md:col-span-1">
                                <flux:field>
                                    <flux:label>IVA %</flux:label>
                                    <flux:input wire:model="lines.{{ $i }}.tax_rate" type="number" step="0.01" min="0" max="100" />
                                    <flux:error name="lines.{{ $i }}.tax_rate" />
                                </flux:field>
                            </div>

                            <!-- Descripción -->
                            <div class="col-span-10 md:col-span-2">
                                <flux:field>
                                    <flux:label>Descripción</flux:label>
                                    <flux:input wire:model="lines.{{ $i }}.description" type="text" placeholder="Opcional" />
                                </flux:field>
                            </div>

                            <!-- Subtotal + eliminar -->
                            <div class="col-span-2 md:col-span-1 flex flex-col items-end gap-1">
                                @php
                                    $qty   = (float)($line['quantity'] ?? 0);
                                    $price = (float)($line['unit_price'] ?? 0);
                                    $tax   = (float)($line['tax_rate'] ?? 0);
                                    $sub   = $qty * $price;
                                    $total = $sub * (1 + $tax / 100);
                                @endphp
                                <span class="text-xs text-zinc-500">Total</span>
                                <span class="font-semibold text-sm">{{ number_format($total, 2) }} €</span>
                                @if (count($lines) > 1)
                                    <flux:button wire:click="removeLine({{ $i }})" size="xs" variant="ghost" icon="trash" class="text-red-500" />
                                @endif
                            </div>
                        </div>
                    @endforeach

                    <flux:button wire:click="addLine" variant="ghost" icon="plus" size="sm">
                        Añadir línea
                    </flux:button>
                </div>

                <!-- Totales -->
                @php
                    $grandSubtotal = 0;
                    $grandTax = 0;
                    foreach ($lines as $line) {
                        $sub = (float)($line['quantity'] ?? 0) * (float)($line['unit_price'] ?? 0);
                        $grandSubtotal += $sub;
                        $grandTax += $sub * ((float)($line['tax_rate'] ?? 0) / 100);
                    }
                @endphp
                <div class="flex justify-end mt-4">
                    <div class="w-64 space-y-1 text-sm">
                        <div class="flex justify-between">
                            <span class="text-zinc-500">Base imponible:</span>
                            <span>{{ number_format($grandSubtotal, 2) }} €</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-zinc-500">IVA:</span>
                            <span>{{ number_format($grandTax, 2) }} €</span>
                        </div>
                        <div class="flex justify-between font-bold text-base border-t pt-1">
                            <span>Total:</span>
                            <span>{{ number_format($grandSubtotal + $grandTax, 2) }} €</span>
                        </div>
                    </div>
                </div>
            </x-agro.form-section>

            <!-- Observaciones -->
            <x-agro.form-section title="Observaciones">
                <flux:field>
                    <flux:textarea wire:model="observations" rows="3" placeholder="Observaciones para la factura..." />
                </flux:field>
            </x-agro.form-section>

            <x-agro.form-actions :cancel-url="route('winery.invoices.wine-sale.index')" submit-label="Guardar Cambios" />
        </form>

        @endif
    </x-agro.form-card>
</div>
