<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Nueva Factura"
        description="Crea una nueva factura de venta de productos a un cliente"
    >
        <x-slot:actions>
            <flux:button href="{{ route('winery.invoices.products.index') }}" variant="outline" icon="arrow-left">
                Volver
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    <x-agro.card>
        <form wire:submit="save" class="space-y-8">

            {{-- Cliente --}}
            <x-agro.form-section title="Cliente">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:field>
                        <flux:label required>Cliente</flux:label>
                        <flux:select wire:model.live="client_id" id="client_id" required>
                            <option value="">Selecciona un cliente</option>
                            @foreach ($clients as $client)
                                <option value="{{ $client->id }}">{{ $client->full_name }}</option>
                            @endforeach
                        </flux:select>
                        <flux:error name="client_id" />
                        @if ($clients->isEmpty())
                            <p class="mt-1 text-xs text-zinc-400">
                                <a href="{{ route('winery.clients.create') }}" class="text-blue-600 underline">Crea tu primer cliente</a>
                            </p>
                        @endif
                    </flux:field>

                    @if ($client_id)
                        <flux:field>
                            <flux:label required>Dirección de facturación</flux:label>
                            <flux:select wire:model="client_address_id" id="client_address_id">
                                <option value="">Selecciona una dirección</option>
                                @foreach ($availableAddresses as $address)
                                    <option value="{{ $address->id }}">
                                        {{ $address->full_address }}
                                        @if ($address->is_default) (Por defecto) @endif
                                    </option>
                                @endforeach
                            </flux:select>
                            <flux:error name="client_address_id" />
                        </flux:field>
                    @endif
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <flux:field>
                        <flux:label>Forma de pago</flux:label>
                        <flux:select wire:model="payment_type" id="payment_type">
                            <option value="">Sin especificar</option>
                            <option value="cash">Efectivo</option>
                            <option value="transfer">Transferencia</option>
                            <option value="check">Cheque</option>
                            <option value="other">Otro</option>
                        </flux:select>
                    </flux:field>

                </div>
            </x-agro.form-section>

            {{-- Documento y Fechas --}}
            <x-agro.form-section title="Documento">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <flux:field>
                        <flux:label required>Código de albarán</flux:label>
                        <flux:input
                            wire:model="delivery_note_code"
                            type="text"
                            disabled
                            class="bg-zinc-100 cursor-not-allowed font-mono font-semibold"
                        />
                        <flux:error name="delivery_note_code" />
                        <p class="mt-1 text-xs text-zinc-400">Secuencial automático</p>
                    </flux:field>

                    <flux:field>
                        <flux:label required>Fecha de albarán</flux:label>
                        <flux:input wire:model="delivery_note_date" type="date" required />
                        <flux:error name="delivery_note_date" />
                    </flux:field>

                    <flux:field>
                        <flux:label required>Fecha de factura</flux:label>
                        <flux:input wire:model="invoice_date" type="date" required />
                        <flux:error name="invoice_date" />
                    </flux:field>
                    <div class="mt-6">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <flux:checkbox wire:model.live="is_gift" id="is_gift" />
                            <span class="text-sm font-medium text-zinc-700">Factura regalo <span class="text-xs text-zinc-400">(importes = 0, stock se deduce igualmente)</span></span>
                        </label>
                    </div>
                </div>
            </x-agro.form-section>

            {{-- Añadir Producto --}}
            <x-agro.form-section title="Productos">
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-3">
                            <flux:field>
                                <flux:label>Añadir producto</flux:label>
                                <flux:select
                                    wire:model.live="selectedLotId"
                                    wire:change="addProductToInvoice"
                                    id="selectedLotId"
                                >
                                    <option value="">-- Selecciona un lote de vino --</option>
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
                                @if ($errors->has('items'))
                                    <flux:error name="items" />
                                @endif
                                <p class="mt-1 text-xs text-zinc-400">
                                    Solo se muestran lotes con stock disponible. La cantidad se puede ajustar en las líneas.
                                </p>
                            </flux:field>
                        </div>
                    </div>

                    @if ($wineLots->isEmpty())
                        <flux:callout variant="info">
                            <strong>No hay lotes con stock disponible.</strong><br>
                            Todos los lotes están agotados o no hay lotes registrados.
                        </flux:callout>
                    @endif
                </div>
            </x-agro.form-section>

            {{-- Líneas de la factura --}}
            <x-agro.form-section title="Líneas de la factura">
                <div class="space-y-4">
                    @forelse ($items as $index => $item)
                        @php
                            $isWineItem   = !empty($item['wine_lot_id']);
                            $availableQty = isset($item['available_qty']) ? (float) $item['available_qty'] : null;

                            $qty      = (float)($item['quantity']            ?? 0);
                            $price    = (float)($item['unit_price']          ?? 0);
                            $discPct  = (float)($item['discount_percentage'] ?? 0);
                            $taxObj   = $item['tax_id'] ? $availableTaxes->firstWhere('id', $item['tax_id']) : null;
                            $taxRate  = $taxObj ? (float) $taxObj->rate : 0;
                            $sub      = $qty * $price;
                            $discAmt  = $sub * ($discPct / 100);
                            $base     = $sub - $discAmt;
                            $taxAmt   = $base * ($taxRate / 100);
                            $total    = $base + $taxAmt;
                        @endphp

                        <div class="border-2 border-zinc-200 rounded-lg p-4 bg-white hover:border-agro-300 transition-colors shadow-xs"
                             data-cy="invoice-item">

                            {{-- Cabecera ítem --}}
                            <div class="flex justify-between items-start mb-3">
                                <div class="flex items-center gap-2">
                                    <h4 class="text-base font-bold text-zinc-900">Línea #{{ $index + 1 }}</h4>
                                    @if ($isWineItem)
                                        <flux:badge color="purple" size="sm">Vino</flux:badge>
                                    @endif
                                </div>
                                <flux:button type="button" wire:click="removeItem({{ $index }})"
                                    variant="danger" size="sm" icon="trash">
                                    Eliminar
                                </flux:button>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                                {{-- Nombre / Concepto --}}
                                <div class="md:col-span-12">
                                    <flux:field>
                                        <flux:label class="text-xs">Concepto <span class="text-red-500">*</span></flux:label>
                                        <flux:input
                                            wire:model="items.{{ $index }}.name"
                                            placeholder="Ej: Rioja Reserva 2021, Rosado Garnacha..."
                                            class="text-sm"
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
                                            placeholder="Descripción detallada..."
                                            class="text-sm"
                                        />
                                    </flux:field>
                                </div>

                                {{-- SKU --}}
                                <div class="md:col-span-4">
                                    <flux:field>
                                        <flux:label class="text-xs">SKU / Código</flux:label>
                                        <flux:input
                                            wire:model="items.{{ $index }}.sku"
                                            placeholder="Código"
                                            class="text-sm"
                                        />
                                    </flux:field>
                                </div>

                                {{-- Cantidad --}}
                                <div class="md:col-span-3">
                                    <flux:field>
                                        <flux:label class="text-xs">
                                            Cantidad <span class="text-red-500">*</span>
                                        </flux:label>
                                        <flux:input
                                            wire:model.live="items.{{ $index }}.quantity"
                                            type="number"
                                            step="0.001"
                                            min="0.001"
                                            @if ($isWineItem && $availableQty !== null)
                                                max="{{ $availableQty }}"
                                            @endif
                                            placeholder="0.000"
                                            class="text-sm"
                                        />
                                        <flux:error name="items.{{ $index }}.quantity" />

                                        @if ($isWineItem && $availableQty !== null)
                                            @php $exceedsStock = $qty > $availableQty; @endphp
                                            <p class="mt-1 text-xs {{ $exceedsStock ? 'text-red-600 font-semibold' : 'text-zinc-500' }}">
                                                Disponible: {{ number_format($availableQty, 0) }} ud
                                                @if ($exceedsStock) — ¡Supera el stock disponible! @endif
                                            </p>
                                        @endif
                                    </flux:field>
                                </div>

                                {{-- Precio --}}
                                <div class="md:col-span-3">
                                    <flux:field>
                                        <flux:label class="text-xs">Precio/ud (€) <span class="text-red-500">*</span></flux:label>
                                        <flux:input
                                            wire:model.live="items.{{ $index }}.unit_price"
                                            type="number"
                                            step="0.0001"
                                            min="0"
                                            placeholder="0.0000"
                                            class="text-sm"
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
                                            placeholder="0.00"
                                            class="text-sm"
                                        />
                                    </flux:field>
                                </div>

                                {{-- Impuesto --}}
                                <div class="md:col-span-3">
                                    <flux:field>
                                        <flux:label class="text-xs">Impuesto</flux:label>
                                        <flux:select wire:model.live="items.{{ $index }}.tax_id" class="text-sm">
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
                                    <div class="flex items-center gap-1">
                                        <span class="text-zinc-500">Subtotal:</span>
                                        <span class="font-semibold text-zinc-900">{{ number_format($sub, 2) }} €</span>
                                    </div>
                                    @if ($discPct > 0)
                                        <div class="flex items-center gap-1">
                                            <span class="text-zinc-500">Dto ({{ number_format($discPct, 0) }}%):</span>
                                            <span class="font-semibold text-red-600">-{{ number_format($discAmt, 2) }} €</span>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <span class="text-zinc-500">Base:</span>
                                            <span class="font-semibold text-zinc-900">{{ number_format($base, 2) }} €</span>
                                        </div>
                                    @endif
                                    @if ($taxObj)
                                        <div class="flex items-center gap-1">
                                            <span class="text-zinc-500">{{ $taxObj->name }} ({{ number_format($taxRate, 2) }}%):</span>
                                            <span class="font-semibold text-zinc-900">{{ number_format($taxAmt, 2) }} €</span>
                                        </div>
                                    @endif
                                    <div class="flex items-center gap-1 ml-auto">
                                        <span class="text-zinc-600 font-semibold">Total línea:</span>
                                        <span class="text-base font-bold text-green-600">{{ number_format($total, 2) }} €</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-10 border-2 border-dashed border-zinc-300 rounded-xl">
                            <p class="text-zinc-500 mb-2">No hay líneas en la factura.</p>
                            <p class="text-sm text-zinc-400">Selecciona un lote arriba o añade un concepto manual.</p>
                        </div>
                    @endforelse

                    <div class="flex justify-center pt-4 border-t border-zinc-200 mt-6">
                        <flux:button type="button" wire:click="addItem" variant="outline" icon="plus">
                            Añadir concepto manual
                        </flux:button>
                    </div>
                </div>

            </x-agro.form-section>

            {{-- Observaciones --}}
            <x-agro.form-section title="Observaciones">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:field>
                        <flux:label>Observaciones internas</flux:label>
                        <flux:textarea wire:model="observations" rows="3"
                            placeholder="Notas internas (no aparecen en el documento)..." />
                        <flux:error name="observations" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Observaciones en documento</flux:label>
                        <flux:textarea wire:model="observations_invoice" rows="3"
                            placeholder="Texto que aparecerá en el albarán y la factura..." />
                        <flux:error name="observations_invoice" />
                    </flux:field>
                </div>
            </x-agro.form-section>

            <x-agro.form-actions :cancel-url="route('winery.invoices.products.index')" submit-label="Crear Factura" />
        </form>
    </x-agro.card>
</div>
