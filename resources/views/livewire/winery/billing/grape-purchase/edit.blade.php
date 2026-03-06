<div>
    <x-agro.form-card
        title="Editar Liquidación de Vendimia"
        description="{{ $invoice->invoice_number }} — {{ $invoice->viticulturist?->name }}"
        :back-url="route('winery.invoices.grape-purchase.index')">

        @if ($isLocked)
            <flux:callout variant="warning" icon="exclamation-triangle">
                Esta liquidación no se puede editar porque
                @if ($invoice->status === 'cancelled') está cancelada.
                @else ya está pagada. @endif
            </flux:callout>
        @else

        <form wire:submit.prevent="save" class="space-y-8">

            <!-- Cabecera -->
            <x-agro.form-section title="Datos de la Liquidación">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <flux:field>
                        <flux:label>Viticultor</flux:label>
                        <flux:input value="{{ $invoice->viticulturist?->name }}" readonly />
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

                <!-- Números (solo lectura) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                    <flux:field>
                        <flux:label>Nº Liquidación</flux:label>
                        <flux:input value="{{ $invoice->invoice_number }}" readonly />
                    </flux:field>
                    <flux:field>
                        <flux:label>Nº Referencia</flux:label>
                        <flux:input value="{{ $invoice->delivery_note_code }}" readonly />
                    </flux:field>
                </div>
            </x-agro.form-section>

            <!-- Selección de recepciones -->
            <x-agro.form-section title="Recepciones de Uva">
                @if ($availableHarvests->isEmpty())
                    <p class="text-zinc-500 text-sm">No hay recepciones disponibles para este viticultor en tus campañas.</p>
                @else
                    <p class="text-sm text-zinc-500 mb-3">Selecciona las recepciones a incluir en la liquidación:</p>
                    <div class="space-y-2">
                        @foreach ($availableHarvests as $harvest)
                            @php $selected = in_array($harvest->id, $selectedIds); @endphp
                            <div class="flex items-center gap-3 p-3 rounded-lg border {{ $selected ? 'border-primary-400 bg-primary-50 dark:bg-primary-900/20' : 'border-zinc-200 dark:border-zinc-700' }}">
                                <flux:checkbox wire:click="toggleHarvest({{ $harvest->id }})" :checked="$selected" />
                                <div class="flex-1 grid grid-cols-4 gap-2 text-sm">
                                    <span class="font-medium">Recepción #{{ $harvest->id }}</span>
                                    <span class="text-zinc-600 dark:text-zinc-400">
                                        {{ $harvest->harvest_date ? \Carbon\Carbon::parse($harvest->harvest_date)->format('d/m/Y') : '—' }}
                                    </span>
                                    <span>{{ $harvest->variety ?? '—' }}</span>
                                    <span class="font-medium text-right">
                                        {{ number_format($harvest->net_weight ?? $harvest->gross_weight ?? 0, 0) }} kg
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-agro.form-section>

            <!-- Líneas seleccionadas (edición de precios) -->
            @if (!empty($lines))
                <x-agro.form-section title="Detalle de Líneas">
                    <div class="space-y-4">
                        @foreach ($lines as $i => $line)
                            <div class="grid grid-cols-12 gap-3 items-end p-4 bg-zinc-50 dark:bg-zinc-800/50 rounded-lg" wire:key="gp-line-{{ $i }}">
                                <div class="col-span-12 md:col-span-4">
                                    <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                        Recepción #{{ $line['harvest_id'] }}
                                    </p>
                                    <p class="text-xs text-zinc-500">{{ $line['quantity'] }} kg</p>
                                </div>

                                <div class="col-span-4 md:col-span-2">
                                    <flux:field>
                                        <flux:label>Kg *</flux:label>
                                        <flux:input wire:model="lines.{{ $i }}.quantity" type="number" step="0.001" min="0.001" />
                                        <flux:error name="lines.{{ $i }}.quantity" />
                                    </flux:field>
                                </div>

                                <div class="col-span-4 md:col-span-2">
                                    <flux:field>
                                        <flux:label>€/kg *</flux:label>
                                        <flux:input wire:model="lines.{{ $i }}.unit_price" type="number" step="0.001" min="0" placeholder="0.000" />
                                        <flux:error name="lines.{{ $i }}.unit_price" />
                                    </flux:field>
                                </div>

                                <div class="col-span-4 md:col-span-1">
                                    <flux:field>
                                        <flux:label>IRPF %</flux:label>
                                        <flux:input wire:model="lines.{{ $i }}.tax_rate" type="number" step="0.01" min="0" max="100" />
                                    </flux:field>
                                </div>

                                <div class="col-span-10 md:col-span-2">
                                    <flux:field>
                                        <flux:label>Descripción</flux:label>
                                        <flux:input wire:model="lines.{{ $i }}.description" type="text" placeholder="Opcional" />
                                    </flux:field>
                                </div>

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
                                    <flux:button wire:click="removeLine({{ $i }})" size="xs" variant="ghost" icon="trash" class="text-red-500" />
                                </div>
                            </div>
                        @endforeach
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
                                <span class="text-zinc-500">Base:</span>
                                <span>{{ number_format($grandSubtotal, 2) }} €</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-zinc-500">Retención:</span>
                                <span>-{{ number_format($grandTax, 2) }} €</span>
                            </div>
                            <div class="flex justify-between font-bold text-base border-t pt-1">
                                <span>A pagar:</span>
                                <span>{{ number_format($grandSubtotal + $grandTax, 2) }} €</span>
                            </div>
                        </div>
                    </div>
                    <flux:error name="lines" />
                </x-agro.form-section>
            @endif

            <!-- Observaciones -->
            <x-agro.form-section title="Observaciones">
                <flux:field>
                    <flux:textarea wire:model="observations" rows="3" placeholder="Observaciones para la liquidación..." />
                </flux:field>
            </x-agro.form-section>

            <x-agro.form-actions :cancel-url="route('winery.invoices.grape-purchase.index')" submit-label="Guardar Cambios" />
        </form>

        @endif
    </x-agro.form-card>
</div>
