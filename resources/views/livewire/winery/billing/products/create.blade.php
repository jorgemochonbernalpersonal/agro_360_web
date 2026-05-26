<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="{{ __('Nueva Factura') }}"
        :description="__('Crea una nueva factura de venta de productos a un cliente')"
    >
        <x-slot:actions>
            <flux:button href="{{ roleRoute('invoices.products.index') }}" variant="outline" icon="arrow-left">
                Volver
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    <x-agro.card>
        <form wire:submit="save" class="space-y-8">

            {{-- Cliente --}}
            <x-agro.form-section title="{{ __('Cliente') }}">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:field>
                        <flux:label required>{{ __('Cliente') }}</flux:label>
                        <flux:select wire:model.live="client_id" id="client_id" required>
                            <option value="">{{ __('Selecciona un cliente') }}</option>
                            @foreach ($clients as $client)
                                <option value="{{ $client->id }}">{{ $client->full_name }}</option>
                            @endforeach
                        </flux:select>
                        <flux:error name="client_id" />
                        @if ($clients->isEmpty())
                            <p class="mt-1 text-xs text-zinc-400">
                                <a href="{{ roleRoute('clients.create') }}" class="text-blue-600 underline">Crea tu primer cliente</a>
                            </p>
                        @endif
                    </flux:field>

                    @if ($client_id)
                        <flux:field>
                            <flux:label required>{{ __('Dirección de facturación') }}</flux:label>
                            <flux:select wire:model="client_address_id" id="client_address_id">
                                <option value="">{{ __('Selecciona una dirección') }}</option>
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
                        <flux:label>{{ __('Forma de pago') }}</flux:label>
                        <flux:select wire:model="payment_type" id="payment_type">
                            <option value="">{{ __('Sin especificar') }}</option>
                            <option value="cash">{{ __('Efectivo') }}</option>
                            <option value="transfer">{{ __('Transferencia') }}</option>
                            <option value="check">{{ __('Cheque') }}</option>
                            <option value="other">{{ __('Otro') }}</option>
                        </flux:select>
                    </flux:field>

                </div>
            </x-agro.form-section>

            {{-- Documento y Fechas --}}
            <x-agro.form-section title="{{ __('Documento') }}">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <flux:field>
                        <flux:label required>{{ __('Código de albarán') }}</flux:label>
                        <flux:input
                            wire:model="delivery_note_code"
                            type="text"
                            disabled
                            class="bg-zinc-100 cursor-not-allowed font-mono font-semibold"
                        />
                        <flux:error name="delivery_note_code" />
                        <p class="mt-1 text-xs text-zinc-400">{{ __('Secuencial automático') }}</p>
                    </flux:field>

                    <flux:field>
                        <flux:label required>{{ __('Fecha de pedido') }}</flux:label>
                        <flux:input wire:model="order_date" type="date" required />
                        <flux:error name="order_date" />
                        <flux:description>Fecha en que se realiza el pedido.</flux:description>
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Fecha de albarán') }}</flux:label>
                        <flux:input wire:model="delivery_note_date" type="date" />
                        <flux:error name="delivery_note_date" />
                    </flux:field>

                    <div class="mt-6">
                        <flux:checkbox wire:model.live="is_gift" :label="__('Factura regalo')" :description="__('Importes = 0, stock se deduce igualmente')" />
                    </div>
                </div>
            </x-agro.form-section>

            {{-- Añadir Producto --}}
            <x-agro.form-section title="{{ __('Productos') }}">
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-3">
                            <flux:field>
                                <flux:label>{{ __('Añadir producto') }}</flux:label>
                                <flux:select
                                    wire:model.live="selectedLotId"
                                    wire:change="addProductToInvoice"
                                    id="selectedLotId"
                                >
                                    <option value="">{{ __('-- Selecciona un lote de vino --') }}</option>
                                    @foreach ($wineLots as $lot)
                                        <option value="{{ $lot->id }}">
                                            {{ $lot->name }}
                                            {{ $lot->vintage ? "({$lot->vintage})" : '' }}
                                            – Disp: {{ number_format($lot->available_quantity, 0) }} {{ $lot->unit ?? 'ud' }}
                                            {{ $lot->price_per_unit ? '(' . number_format($lot->price_per_unit, 2) . ' €/ud)' : '' }}
                                        </option>
                                    @endforeach
                                </flux:select>
                                @if ($errors->has('items'))
                                    <flux:error name="items" />
                                @endif
                                <p class="mt-1 text-xs text-zinc-400">{{ __('Solo se muestran lotes con stock disponible. La cantidad se puede ajustar en las líneas.') }}</p>
                            </flux:field>
                        </div>
                    </div>

                    @if ($wineLots->isEmpty())
                        <flux:callout variant="info">
                            <strong>{{ __('No hay lotes con stock disponible.') }}</strong><br>
                            Todos los lotes están agotados o no hay lotes registrados.
                        </flux:callout>
                    @endif
                </div>
            </x-agro.form-section>

            {{-- Líneas de la factura --}}
            <x-agro.form-section title="{{ __('Líneas de la factura') }}">
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
                                        <flux:badge color="purple" size="sm">{{ __('Vino') }}</flux:badge>
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
                                        <flux:label class="text-xs">{{ __('Concepto') }} <span class="text-red-500">*</span></flux:label>
                                        <flux:input
                                            wire:model="items.{{ $index }}.name"
                                            placeholder="{{ __('Ej: Rioja Reserva 2021, Rosado Garnacha...') }}"
                                            class="text-sm"
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
                                            placeholder="{{ __('Descripción detallada...') }}"
                                            class="text-sm"
                                        />
                                    </flux:field>
                                </div>

                                {{-- SKU --}}
                                <div class="md:col-span-4">
                                    <flux:field>
                                        <flux:label class="text-xs">{{ __('SKU / Código') }}</flux:label>
                                        <flux:input
                                            wire:model="items.{{ $index }}.sku"
                                            placeholder="{{ __('Código') }}"
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
                                            placeholder="0.000"
                                            class="text-sm"
                                        />
                                        <flux:error name="items.{{ $index }}.quantity" />

                                        @if ($isWineItem && $availableQty !== null)
                                            @php $exceedsStock = $qty > $availableQty; @endphp
                                            <p class="mt-1 text-xs {{ $exceedsStock ? 'text-red-600 font-semibold' : 'text-zinc-500' }}">
                                                Disponible: {{ number_format($availableQty, 0) }} ud{{ $exceedsStock ? ' — ¡Supera el stock disponible!' : '' }}
                                            </p>
                                        @endif
                                    </flux:field>
                                </div>

                                {{-- Precio --}}
                                <div class="md:col-span-3">
                                    <flux:field>
                                        <flux:label class="text-xs">{{ __('Precio/ud (€)') }} <span class="text-red-500">*</span></flux:label>
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
                                        <flux:label class="text-xs">{{ __('Descuento %') }}</flux:label>
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
                                        <flux:label class="text-xs">{{ __('Impuesto') }}</flux:label>
                                        <flux:select wire:model.live="items.{{ $index }}.tax_id" class="text-sm">
                                            <option value="">{{ __('Sin impuesto') }}</option>
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
                                        <span class="text-zinc-500">{{ __('Subtotal:') }}</span>
                                        <span class="font-semibold text-zinc-900">{{ number_format($sub, 2) }} €</span>
                                    </div>
                                    @if ($discPct > 0)
                                        <div class="flex items-center gap-1">
                                            <span class="text-zinc-500">Dto ({{ number_format($discPct, 0) }}%):</span>
                                            <span class="font-semibold text-red-600">-{{ number_format($discAmt, 2) }} €</span>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <span class="text-zinc-500">{{ __('Base:') }}</span>
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
                                        <span class="text-zinc-600 font-semibold">{{ __('Total línea:') }}</span>
                                        <span class="text-base font-bold text-green-600">{{ number_format($total, 2) }} €</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-10 border-2 border-dashed border-zinc-300 rounded-xl">
                            <p class="text-zinc-500 mb-2">{{ __('No hay líneas en la factura.') }}</p>
                            <p class="text-sm text-zinc-400">{{ __('Selecciona un lote arriba o añade un concepto manual.') }}</p>
                        </div>
                    @endforelse

                    <div class="flex justify-center pt-4 border-t border-zinc-200 mt-6">
                        <flux:button type="button" wire:click="addItem" variant="outline" icon="plus">{{ __('Añadir concepto manual') }}</flux:button>
                    </div>
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
                            placeholder="{{ __('Notas internas (no aparecen en el documento)...') }}" />
                        <flux:error name="observations" />
                    </flux:field>
                    <flux:field>
                        <flux:label>{{ __('Observaciones en documento') }}</flux:label>
                        <flux:textarea wire:model="observations_invoice" rows="3"
                            placeholder="{{ __('Texto que aparecerá en el albarán y la factura...') }}" />
                        <flux:error name="observations_invoice" />
                    </flux:field>
                </div>
            </x-agro.form-section>

            <x-agro.form-actions :cancel-url="roleRoute('invoices.products.index')" submit-:label="__('Crear Factura')" />
        </form>
    </x-agro.card>
</div>
