<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Nuevo Albarán"
        description="Crea un nuevo albarán de venta"
    >
        <x-slot:actions>
            <flux:button href="{{ route('viticulturist.invoices.index') }}" variant="outline" icon="arrow-left">
                Volver
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    <x-agro.card>
        <form wire:submit="save" class="space-y-8" data-cy="invoice-create-form">

            {{-- Cliente --}}
            <x-agro.form-section title="Cliente">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:field>
                        <flux:label required>Cliente</flux:label>
                        <flux:select wire:model.live="client_id" id="client_id" data-cy="client-id" required>
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
                            <flux:select wire:model="client_address_id" id="client_address_id" data-cy="client-address-id">
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

            {{-- Albarán y Fechas --}}
            <x-agro.form-section title="Albarán">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <flux:field>
                        <flux:label required>Código de albarán</flux:label>
                        <flux:input
                            wire:model="delivery_note_code"
                            id="delivery_note_code"
                            data-cy="delivery-note-code"
                            type="text"
                            required
                            disabled
                            class="bg-zinc-100 cursor-not-allowed font-mono font-semibold"
                        />
                        <flux:error name="delivery_note_code" />
                        <p class="mt-1 text-xs text-zinc-400">Secuencial automático</p>
                    </flux:field>

                    <flux:field>
                        <flux:label required>Fecha de albarán</flux:label>
                        <flux:input
                            wire:model="delivery_note_date"
                            id="delivery_note_date"
                            type="date"
                            required
                        />
                        <flux:error name="delivery_note_date" />
                    </flux:field>

                    <flux:field>
                        <flux:label required>Fecha de factura</flux:label>
                        <flux:input
                            wire:model="invoice_date"
                            id="invoice_date"
                            data-cy="invoice-date"
                            type="date"
                            required
                        />
                        <flux:error name="invoice_date" />
                    </flux:field>
                </div>
            </x-agro.form-section>

            {{-- Cosechas para Facturar --}}
            <x-agro.form-section title="Cosechas">
                @if($fromHarvestRoute)
                    <flux:callout variant="warning" class="mb-4">
                        <strong>Obligatorio:</strong> Debes seleccionar al menos una cosecha para crear el albarán.
                    </flux:callout>
                @endif

                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <flux:field>
                            <flux:label>Filtrar por campaña</flux:label>
                            <flux:select wire:model.live="selectedCampaign" id="selectedCampaign" data-cy="selected-campaign">
                                <option value="">Todas las campañas</option>
                                @foreach($campaigns as $campaign)
                                    <option value="{{ $campaign->id }}">{{ $campaign->year }}</option>
                                @endforeach
                            </flux:select>
                        </flux:field>

                        <div class="md:col-span-2">
                            <flux:field>
                                <flux:label :required="$fromHarvestRoute">Añadir cosecha</flux:label>
                                <flux:select
                                    wire:model.live="selectedHarvestId"
                                    wire:change="addHarvestToInvoice"
                                    id="selectedHarvestId"
                                    data-cy="selected-harvest-id"
                                    :required="$fromHarvestRoute"
                                >
                                    <option value="">-- Selecciona una cosecha --</option>
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
                                                ({{ number_format($harvest->price_per_kg, 4) }} €/kg)
                                            @endif
                                        </option>
                                    @endforeach
                                </flux:select>
                                @if($errors->has('items'))
                                    <flux:error name="items" />
                                @endif
                                <p class="mt-1 text-xs text-zinc-400">
                                    Solo se muestran cosechas con stock disponible. La cantidad se puede ajustar en los items.
                                </p>
                            </flux:field>
                        </div>
                    </div>

                    @if($availableHarvests->isEmpty())
                        <flux:callout variant="info">
                            <strong>No hay cosechas con stock disponible.</strong><br>
                            Todas las cosechas están completamente vendidas o no hay cosechas registradas.
                        </flux:callout>
                    @endif
                </div>
            </x-agro.form-section>

            {{-- Items --}}
            <x-agro.form-section title="Líneas del albarán">
                @if(!$fromHarvestRoute)
                    <flux:callout variant="info" class="mb-4">
                        Selecciona una cosecha arriba para añadirla automáticamente, o usa el botón de abajo para añadir conceptos manuales.
                    </flux:callout>
                @endif

                <div class="space-y-4">
                    @forelse($items as $index => $item)
                        @php
                            $isHarvestItem = isset($item['harvest_id']) && $item['harvest_id'];
                            $availableQty  = isset($item['available_qty']) ? (float)$item['available_qty'] : null;
                            $totalWeight   = isset($item['total_weight'])  ? (float)$item['total_weight']  : null;

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

                        <div class="border-2 border-zinc-200 rounded-lg p-4 bg-white hover:border-agro-300 transition-colors shadow-xs"
                             data-cy="invoice-item"
                             data-cy-item-index="{{ $index }}">

                            {{-- Cabecera item --}}
                            <div class="flex justify-between items-start mb-3">
                                <div class="flex items-center gap-2">
                                    <h4 class="text-base font-bold text-zinc-900">Línea #{{ $index + 1 }}</h4>
                                    @if($isHarvestItem)
                                        <flux:badge color="purple" size="sm">Cosecha</flux:badge>
                                    @endif
                                </div>
                                @if(count($items) > 1 || !$fromHarvestRoute)
                                    <flux:button
                                        type="button"
                                        wire:click="removeItem({{ $index }})"
                                        variant="danger"
                                        size="sm"
                                        icon="trash"
                                        data-cy="remove-item"
                                        data-cy-item-index="{{ $index }}"
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
                                            placeholder="Ej: Uva Tempranillo, Servicio de recolección..."
                                            class="text-sm"
                                            data-cy="item-name"
                                            data-cy-item-index="{{ $index }}"
                                        />
                                        <flux:error name="items.{{ $index }}.name" />
                                    </flux:field>
                                </div>

                                {{-- Descripción y tipo --}}
                                <div class="md:col-span-8">
                                    <flux:field>
                                        <flux:label class="text-xs">Descripción</flux:label>
                                        <flux:textarea
                                            wire:model="items.{{ $index }}.description"
                                            rows="2"
                                            placeholder="Descripción detallada..."
                                            class="text-sm"
                                            data-cy="item-description"
                                            data-cy-item-index="{{ $index }}"
                                        />
                                    </flux:field>
                                </div>
                                <div class="md:col-span-4 space-y-2">
                                    <flux:field>
                                        <flux:label class="text-xs">SKU / Código</flux:label>
                                        <flux:input
                                            wire:model="items.{{ $index }}.sku"
                                            placeholder="Código"
                                            class="text-sm"
                                            data-cy="item-sku"
                                            data-cy-item-index="{{ $index }}"
                                        />
                                    </flux:field>
                                    <flux:field>
                                        <flux:label class="text-xs">Tipo</flux:label>
                                        <flux:select wire:model="items.{{ $index }}.concept_type" class="text-sm">
                                            <option value="harvest">Cosecha</option>
                                            <option value="service">Servicio</option>
                                            <option value="product">Producto</option>
                                            <option value="other">Otro</option>
                                        </flux:select>
                                    </flux:field>
                                </div>

                                {{-- Cantidad --}}
                                <div class="md:col-span-3">
                                    <flux:field>
                                        <flux:label class="text-xs">
                                            Cantidad (kg) <span class="text-red-500">*</span>
                                        </flux:label>
                                        <flux:input
                                            wire:model.live="items.{{ $index }}.quantity"
                                            type="number"
                                            step="0.001"
                                            min="0.001"
                                            @if($isHarvestItem && $availableQty !== null)
                                                max="{{ $availableQty }}"
                                            @endif
                                            placeholder="0.000"
                                            class="text-sm"
                                            data-cy="item-quantity"
                                            data-cy-item-index="{{ $index }}"
                                        />
                                        <flux:error name="items.{{ $index }}.quantity" />

                                        @if($isHarvestItem && $availableQty !== null)
                                            @php $exceedsStock = $itemQty > $availableQty; @endphp
                                            <p class="mt-1 text-xs {{ $exceedsStock ? 'text-red-600 font-semibold' : 'text-zinc-500' }}">
                                                Disponible: {{ number_format($availableQty, 3) }} kg
                                                @if($totalWeight && $totalWeight != $availableQty)
                                                    / Cosecha total: {{ number_format($totalWeight, 3) }} kg
                                                @endif
                                                @if($exceedsStock)
                                                    — ¡Supera el stock disponible!
                                                @endif
                                            </p>
                                        @endif
                                    </flux:field>
                                </div>

                                {{-- Precio --}}
                                <div class="md:col-span-3">
                                    <flux:field>
                                        <flux:label class="text-xs">Precio €/kg <span class="text-red-500">*</span></flux:label>
                                        <flux:input
                                            wire:model.live="items.{{ $index }}.unit_price"
                                            type="number"
                                            step="0.0001"
                                            min="0"
                                            placeholder="0.0000"
                                            class="text-sm"
                                            data-cy="item-unit-price"
                                            data-cy-item-index="{{ $index }}"
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
                                            data-cy="item-discount"
                                            data-cy-item-index="{{ $index }}"
                                        />
                                    </flux:field>
                                </div>

                                {{-- Impuesto --}}
                                <div class="md:col-span-3">
                                    <flux:field>
                                        <flux:label class="text-xs">Impuesto</flux:label>
                                        <flux:select wire:model.live="items.{{ $index }}.tax_id" class="text-sm" data-cy="item-tax-id" data-cy-item-index="{{ $index }}">
                                            <option value="">Sin impuesto</option>
                                            @foreach($availableTaxes as $tax)
                                                <option value="{{ $tax->id }}">{{ $tax->name }} ({{ number_format($tax->rate, 2) }}%)</option>
                                            @endforeach
                                        </flux:select>
                                    </flux:field>
                                </div>
                            </div>

                            {{-- Resumen de totales del item --}}
                            <div class="mt-3 pt-3 border-t border-zinc-200 bg-zinc-50 -mx-4 -mb-4 px-4 py-2 rounded-b-lg">
                                <div class="flex flex-wrap gap-x-6 gap-y-1 text-xs">
                                    <div class="flex items-center gap-1">
                                        <span class="text-zinc-500">Subtotal:</span>
                                        <span class="font-semibold text-zinc-900">{{ number_format($itemSubtotal, 2) }} €</span>
                                    </div>
                                    @if($itemDiscount > 0)
                                        <div class="flex items-center gap-1">
                                            <span class="text-zinc-500">Dto:</span>
                                            <span class="font-semibold text-red-600">-{{ number_format($itemDiscAmt, 2) }} €</span>
                                        </div>
                                    @endif
                                    <div class="flex items-center gap-1">
                                        <span class="text-zinc-500">Base:</span>
                                        <span class="font-semibold text-zinc-900">{{ number_format($itemBase, 2) }} €</span>
                                    </div>
                                    @if($selectedTax)
                                        <div class="flex items-center gap-1">
                                            <span class="text-zinc-500">{{ $selectedTax->name }} ({{ number_format($taxRate, 2) }}%):</span>
                                            <span class="font-semibold text-zinc-900">{{ number_format($itemTaxAmt, 2) }} €</span>
                                        </div>
                                    @endif
                                    <div class="flex items-center gap-1 ml-auto">
                                        <span class="text-zinc-600 font-semibold">Total línea:</span>
                                        <span class="text-base font-bold text-green-600">{{ number_format($itemTotal, 2) }} €</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-10 border-2 border-dashed border-zinc-300 rounded-xl">
                            <p class="text-zinc-500 mb-2">No hay líneas en el albarán.</p>
                            <p class="text-sm text-zinc-400">Selecciona una cosecha arriba o añade un concepto manual.</p>
                        </div>
                    @endforelse

                    <div class="flex justify-center pt-4 border-t border-zinc-200 mt-6">
                        <flux:button
                            type="button"
                            wire:click="addItem"
                            variant="outline"
                            icon="plus"
                            data-cy="add-item-button"
                        >
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
                        <flux:textarea wire:model="observations" id="observations" data-cy="observations" rows="3" placeholder="Notas internas (no aparecen en el documento)..." />
                        <flux:error name="observations" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Observaciones en documento</flux:label>
                        <flux:textarea wire:model="observations_invoice" id="observations_invoice" data-cy="observations-invoice" rows="3" placeholder="Texto que aparecerá en el albarán y la factura..." />
                        <flux:error name="observations_invoice" />
                    </flux:field>
                </div>
            </x-agro.form-section>

            <x-agro.form-actions :cancel-url="route('viticulturist.invoices.index')" submit-label="Crear Albarán" />
        </form>
    </x-agro.card>
</div>
