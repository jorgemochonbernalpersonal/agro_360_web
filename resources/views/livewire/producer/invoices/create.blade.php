<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        :title="__('Nuevo albarán mixto')"
        :description="__('Añade cosechas y/o lotes de vino en el mismo albarán')"
    >
        <x-slot:actions>
            <flux:button href="{{ route('producer.invoices.mixed.index') }}" wire:navigate variant="outline" icon="arrow-left">
                {{ __('Volver') }}
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    <form wire:submit="save" class="space-y-6" data-cy="mixed-invoice-create-form">

        {{-- ══════════════════════════════════════════════════════════════════════
             Card 1 — Datos generales
        ══════════════════════════════════════════════════════════════════════ --}}
        <x-agro.card>
            <x-agro.form-section :title="__('Datos generales')">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    {{-- Cliente --}}
                    <flux:field>
                        <flux:label required>{{ __('Cliente') }}</flux:label>
                        <flux:select wire:model.live="client_id" id="client_id" required>
                            <option value="">{{ __('Selecciona un cliente') }}</option>
                            @foreach($availableClients as $client)
                                <option value="{{ $client->id }}">{{ $client->full_name }}</option>
                            @endforeach
                        </flux:select>
                        <flux:error name="client_id" />
                    </flux:field>

                    {{-- Dirección --}}
                    @if($client_id)
                        <flux:field>
                            <flux:label>{{ __('Dirección de facturación') }}</flux:label>
                            <flux:select wire:model="client_address_id" id="client_address_id">
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
                    @else
                        <div></div>
                    @endif

                    {{-- Fecha albarán --}}
                    <flux:field>
                        <flux:label required>{{ __('Fecha de albarán') }}</flux:label>
                        <flux:input
                            wire:model="delivery_note_date"
                            id="delivery_note_date"
                            type="date"
                            required
                        />
                        <flux:error name="delivery_note_date" />
                    </flux:field>

                    {{-- Código albarán --}}
                    <flux:field>
                        <flux:label>{{ __('Código de albarán') }}</flux:label>
                        <flux:input
                            wire:model="delivery_note_code"
                            id="delivery_note_code"
                            type="text"
                            :placeholder="__('Se generará automáticamente')"
                            disabled
                            class="bg-zinc-100 cursor-not-allowed font-mono font-semibold"
                        />
                        <flux:error name="delivery_note_code" />
                        <p class="mt-1 text-xs text-zinc-400">{{ __('Secuencial automático') }}</p>
                    </flux:field>

                    {{-- Forma de pago --}}
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
        </x-agro.card>

        {{-- ══════════════════════════════════════════════════════════════════════
             Card 2 — Líneas de factura
        ══════════════════════════════════════════════════════════════════════ --}}
        <x-agro.card>
            <x-agro.form-section :title="__('Líneas del albarán')">

                {{-- ── Subsección: Añadir cosecha ─────────────────────────────── --}}
                <div class="border border-green-200 bg-green-50 rounded-xl p-4 space-y-3">
                    <div class="flex items-center gap-2">
                        <flux:icon icon="archive-box" class="size-4 text-green-600" />
                        <h4 class="text-sm font-semibold text-green-800">{{ __('Añadir cosecha') }}</h4>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <flux:field>
                            <flux:label>{{ __('Campaña') }}</flux:label>
                            <flux:select wire:model.live="selectedCampaign" id="selectedCampaign">
                                <option value="">{{ __('Todas las campañas') }}</option>
                                @foreach($campaigns as $campaign)
                                    <option value="{{ $campaign->id }}">{{ $campaign->year }}</option>
                                @endforeach
                            </flux:select>
                        </flux:field>

                        <div class="md:col-span-2">
                            <flux:field>
                                <flux:label>{{ __('Cosecha disponible') }}</flux:label>
                                <flux:select wire:model.live="selectedHarvestId" id="selectedHarvestId">
                                    <option value="">{{ __('-- Selecciona una cosecha --') }}</option>
                                    @foreach($availableHarvests as $harvest)
                                        <option value="{{ $harvest->id }}">
                                            {{ $harvest->plotPlanting->grapeVariety->name ?? __('Sin variedad') }}
                                            – {{ $harvest->activity->plot->name ?? __('Sin parcela') }}
                                            – {{ $harvest->harvest_start_date->format('d/m/Y') }}
                                            @if($harvest->container) [{{ $harvest->container->name }}] @endif
                                            – {{ __('Disp:') }} {{ number_format($harvest->available_qty_computed, 0) }} kg
                                            @if($harvest->price_per_kg)
                                                ({{ number_format($harvest->price_per_kg, 3) }} €/kg)
                                            @endif
                                        </option>
                                    @endforeach
                                </flux:select>
                                <p class="mt-1 text-xs text-zinc-500">{{ __('Solo cosechas con stock disponible.') }}</p>
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
                            {{ __('Añadir cosecha al albarán') }}
                        </flux:button>
                    </div>

                    @if($availableHarvests->isEmpty())
                        <flux:callout variant="info" class="mt-2">
                            {{ __('No hay cosechas con stock disponible.') }}
                        </flux:callout>
                    @endif
                </div>

                {{-- ── Subsección: Añadir lote de vino ────────────────────────── --}}
                <div class="border border-purple-200 bg-purple-50 rounded-xl p-4 space-y-3 mt-4">
                    <div class="flex items-center gap-2">
                        <flux:icon icon="beaker" class="size-4 text-purple-600" />
                        <h4 class="text-sm font-semibold text-purple-800">{{ __('Añadir lote de vino') }}</h4>
                    </div>
                    <flux:field>
                        <flux:label>{{ __('Lote disponible') }}</flux:label>
                        <flux:select wire:model.live="selectedLotId" id="selectedLotId">
                            <option value="">{{ __('-- Selecciona un lote de vino --') }}</option>
                            @foreach($availableLots as $lot)
                                <option value="{{ $lot->id }}">
                                    {{ $lot->name }}
                                    – {{ __('Disp:') }} {{ number_format($lot->available_bottles ?? 0, 0) }} {{ __('botellas') }}
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
                            {{ __('Añadir lote al albarán') }}
                        </flux:button>
                    </div>
                </div>

                {{-- ── Subsección: Añadir concepto manual ─────────────────────── --}}
                <div class="flex justify-center pt-4 border-t border-zinc-200 mt-4">
                    <flux:button
                        type="button"
                        wire:click="addItem"
                        variant="outline"
                        icon="plus"
                        data-cy="add-item-button"
                    >
                        {{ __('Añadir concepto manual') }}
                    </flux:button>
                </div>

                {{-- ── Tabla de líneas ──────────────────────────────────────────── --}}
                <div class="space-y-4 mt-6">
                    @forelse($items as $index => $item)
                        @php
                            $conceptType   = $item['concept_type'] ?? 'other';
                            $isHarvestItem = $conceptType === 'harvest' && !empty($item['harvest_id']);
                            $isWineItem    = $conceptType === 'wine'    && !empty($item['wine_lot_id']);

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
                            data-cy="invoice-item"
                            data-cy-item-index="{{ $index }}"
                            wire:key="item-{{ $index }}"
                        >
                            {{-- Cabecera de línea --}}
                            <div class="flex justify-between items-start mb-4">
                                <div class="flex items-center gap-2">
                                    <h4 class="text-sm font-bold text-zinc-900">{{ __('Línea #') }}{{ $index + 1 }}</h4>
                                    @if($isHarvestItem)
                                        <flux:badge color="green" size="sm">{{ __('Cosecha') }}</flux:badge>
                                    @elseif($isWineItem)
                                        <flux:badge color="purple" size="sm">{{ __('Vino') }}</flux:badge>
                                    @else
                                        <flux:badge color="zinc" size="sm">{{ __('Otro') }}</flux:badge>
                                    @endif
                                </div>
                                <flux:button
                                    type="button"
                                    wire:click="removeItem({{ $index }})"
                                    variant="danger"
                                    size="sm"
                                    icon="trash"
                                    data-cy="remove-item"
                                    data-cy-item-index="{{ $index }}"
                                >
                                    {{ __('Eliminar') }}
                                </flux:button>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-12 gap-3">

                                {{-- Nombre / Concepto --}}
                                <div class="md:col-span-12">
                                    <flux:field>
                                        <flux:label class="text-xs">{{ __('Concepto') }} <span class="text-red-500">*</span></flux:label>
                                        <flux:input
                                            wire:model="items.{{ $index }}.name"
                                            :placeholder="__('Nombre del concepto...')"
                                            class="text-sm"
                                            data-cy="item-name"
                                            data-cy-item-index="{{ $index }}"
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
                                            :placeholder="__('Descripción detallada...')"
                                            class="text-sm"
                                            data-cy="item-description"
                                            data-cy-item-index="{{ $index }}"
                                        />
                                    </flux:field>
                                </div>

                                {{-- SKU y Tipo --}}
                                <div class="md:col-span-4 space-y-2">
                                    <flux:field>
                                        <flux:label class="text-xs">{{ __('SKU / Referencia') }}</flux:label>
                                        <flux:input
                                            wire:model="items.{{ $index }}.sku"
                                            :placeholder="__('Código')"
                                            class="text-sm"
                                            data-cy="item-sku"
                                            data-cy-item-index="{{ $index }}"
                                        />
                                    </flux:field>
                                    <flux:field>
                                        <flux:label class="text-xs">{{ __('Tipo') }}</flux:label>
                                        <flux:select
                                            wire:model="items.{{ $index }}.concept_type"
                                            class="text-sm"
                                            :disabled="$isHarvestItem || $isWineItem"
                                        >
                                            <option value="harvest">{{ __('Cosecha') }}</option>
                                            <option value="wine">{{ __('Vino') }}</option>
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
                                            @if($isHarvestItem && $availableQty !== null) max="{{ $availableQty }}" @endif
                                            placeholder="0.000"
                                            class="text-sm"
                                            data-cy="item-quantity"
                                            data-cy-item-index="{{ $index }}"
                                        />
                                        <flux:error name="items.{{ $index }}.quantity" />

                                        @if($isHarvestItem && $availableQty !== null)
                                            @php $exceedsStock = $itemQty > $availableQty; @endphp
                                            <p class="mt-1 text-xs {{ $exceedsStock ? 'text-red-600 font-semibold' : 'text-zinc-500' }}">
                                                {{ __('Disponible:') }} {{ number_format($availableQty, 3) }} kg
                                                @if($totalWeight && $totalWeight != $availableQty)
                                                    / {{ __('Total:') }} {{ number_format($totalWeight, 3) }} kg
                                                @endif
                                                @if($exceedsStock) — {{ __('¡Supera el stock!') }} @endif
                                            </p>
                                        @endif
                                    </flux:field>

                                    <flux:field class="mt-2">
                                        <flux:label class="text-xs">{{ __('Unidad') }}</flux:label>
                                        <flux:select
                                            wire:model="items.{{ $index }}.unit"
                                            class="text-sm"
                                            :disabled="$isHarvestItem || $isWineItem"
                                        >
                                            <option value="kg">kg</option>
                                            <option value="botella">{{ __('botella') }}</option>
                                            <option value="litros">{{ __('litros') }}</option>
                                            <option value="centilitros">{{ __('centilitros') }}</option>
                                            <option value="cajas">{{ __('cajas') }}</option>
                                            <option value="unidades">{{ __('unidades') }}</option>
                                        </flux:select>
                                    </flux:field>
                                </div>

                                {{-- Precio unitario --}}
                                <div class="md:col-span-3">
                                    <flux:field>
                                        <flux:label class="text-xs">{{ __('Precio / unidad') }} <span class="text-red-500">*</span></flux:label>
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
                                        <flux:label class="text-xs">{{ __('Descuento %') }}</flux:label>
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
                                        <flux:label class="text-xs">{{ __('Impuesto') }}</flux:label>
                                        <flux:select
                                            wire:model.live="items.{{ $index }}.tax_id"
                                            class="text-sm"
                                            data-cy="item-tax-id"
                                            data-cy-item-index="{{ $index }}"
                                        >
                                            <option value="">{{ __('Sin impuesto') }}</option>
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
                                    <span class="text-zinc-500">{{ __('Subtotal:') }} <strong class="text-zinc-900">{{ number_format($itemSubtotal, 2, ',', '.') }} €</strong></span>
                                    @if($itemDiscount > 0)
                                        <span class="text-zinc-500">{{ __('Dto:') }} <strong class="text-red-600">-{{ number_format($itemDiscAmt, 2, ',', '.') }} €</strong></span>
                                    @endif
                                    <span class="text-zinc-500">{{ __('Base:') }} <strong class="text-zinc-900">{{ number_format($itemBase, 2, ',', '.') }} €</strong></span>
                                    @if($selectedTax)
                                        <span class="text-zinc-500">{{ $selectedTax->name }} ({{ number_format($taxRate, 2) }}%): <strong class="text-zinc-900">{{ number_format($itemTaxAmt, 2, ',', '.') }} €</strong></span>
                                    @endif
                                    <span class="ml-auto text-zinc-600 font-semibold">{{ __('Total línea:') }} <strong class="text-base text-green-600">{{ number_format($itemTotal, 2, ',', '.') }} €</strong></span>
                                </div>
                            </div>

                        </div>
                    @empty
                        <div class="text-center py-12 border-2 border-dashed border-zinc-300 rounded-xl">
                            <flux:icon icon="document-text" class="size-10 text-zinc-300 mx-auto mb-3" />
                            <p class="text-zinc-500 font-medium">{{ __('Sin líneas aún') }}</p>
                            <p class="text-sm text-zinc-400 mt-1">
                                {{ __('Añade una cosecha, un lote de vino o un concepto manual.') }}
                            </p>
                        </div>
                    @endforelse
                </div>

            </x-agro.form-section>
        </x-agro.card>

        {{-- ══════════════════════════════════════════════════════════════════════
             Card 3 — Totales
        ══════════════════════════════════════════════════════════════════════ --}}
        <x-agro.card>
            <x-agro.form-section :title="__('Totales')">
                <div class="bg-zinc-50 rounded-xl p-6">
                    <div class="max-w-xs ml-auto space-y-2">
                        <div class="flex justify-between text-sm text-zinc-600">
                            <span>{{ __('Subtotal') }}</span>
                            <span class="font-semibold text-zinc-900">{{ number_format($this->subtotal, 2, ',', '.') }} €</span>
                        </div>
                        @if($this->discountAmount > 0)
                            <div class="flex justify-between text-sm text-zinc-600">
                                <span>{{ __('Descuentos') }}</span>
                                <span class="font-semibold text-red-600">-{{ number_format($this->discountAmount, 2, ',', '.') }} €</span>
                            </div>
                        @endif
                        <div class="flex justify-between text-sm text-zinc-600">
                            <span>{{ __('IVA') }}</span>
                            <span class="font-semibold text-zinc-900">{{ number_format($this->taxAmount, 2, ',', '.') }} €</span>
                        </div>
                        <div class="flex justify-between pt-3 border-t border-zinc-300">
                            <span class="text-base font-bold text-zinc-900">{{ __('Total') }}</span>
                            <span class="text-2xl font-bold text-green-600">{{ number_format($this->totalAmount, 2, ',', '.') }} €</span>
                        </div>
                    </div>
                </div>
            </x-agro.form-section>
        </x-agro.card>

        {{-- ══════════════════════════════════════════════════════════════════════
             Card 4 — Observaciones
        ══════════════════════════════════════════════════════════════════════ --}}
        <x-agro.card>
            <x-agro.form-section :title="__('Observaciones')">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:field>
                        <flux:label>{{ __('Observaciones internas') }}</flux:label>
                        <flux:textarea
                            wire:model="observations"
                            id="observations"
                            rows="3"
                            :placeholder="__('Notas internas (no aparecen en el documento)...')"
                        />
                        <flux:error name="observations" />
                    </flux:field>
                    <flux:field>
                        <flux:label>{{ __('Observaciones en documento') }}</flux:label>
                        <flux:textarea
                            wire:model="observations_invoice"
                            id="observations_invoice"
                            rows="3"
                            :placeholder="__('Texto que aparecerá en el albarán y la factura...')"
                        />
                        <flux:error name="observations_invoice" />
                    </flux:field>
                </div>
            </x-agro.form-section>
        </x-agro.card>

        {{-- ── Acciones ──────────────────────────────────────────────────────── --}}
        <div class="flex items-center justify-between gap-4 pt-2">
            <flux:button
                href="{{ route('producer.invoices.mixed.index') }}"
                wire:navigate
                variant="outline"
                icon="arrow-left"
            >
                {{ __('Cancelar') }}
            </flux:button>

            <flux:button
                type="submit"
                variant="primary"
                icon="check"
                wire:loading.attr="disabled"
                data-cy="save-invoice"
            >
                <span wire:loading.remove wire:target="save">{{ __('Guardar albarán') }}</span>
                <span wire:loading wire:target="save">{{ __('Guardando...') }}</span>
            </flux:button>
        </div>

    </form>

</div>
