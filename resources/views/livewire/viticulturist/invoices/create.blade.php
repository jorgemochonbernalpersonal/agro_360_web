<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Nueva Factura"
        description="Crea una nueva factura"
    >
        <x-slot:actions>
            <flux:button href="{{ route('viticulturist.invoices.index') }}" variant="outline" icon="arrow-left">
                Volver
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    <x-agro.card>
        <form wire:submit="save" class="space-y-8" data-cy="invoice-create-form">
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
                            <flux:label>Direccion de facturacion</flux:label>
                            <flux:select wire:model="client_address_id" id="client_address_id" data-cy="client-address-id">
                                <option value="">Selecciona una direccion</option>
                                @foreach($availableAddresses as $address)
                                    <option value="{{ $address->id }}">
                                        {{ $address->full_address }}
                                        @if($address->is_default)
                                            (Por defecto)
                                        @endif
                                    </option>
                                @endforeach
                            </flux:select>
                            <flux:error name="client_address_id" />
                        </flux:field>
                    @endif
                </div>
            </x-agro.form-section>

            <x-agro.form-section title="Codigo de Albaran">
                <div class="max-w-md">
                    <flux:field>
                        <flux:label required>Codigo de Albaran</flux:label>
                        <flux:input
                            wire:model="delivery_note_code"
                            id="delivery_note_code"
                            data-cy="delivery-note-code"
                            type="text"
                            required
                            disabled
                            placeholder="Ej: ALB-2025-0001"
                            class="bg-zinc-100 cursor-not-allowed"
                        />
                        <flux:error name="delivery_note_code" />
                    </flux:field>
                    <p class="mt-2 text-xs text-zinc-500">
                        El codigo de albaran se genera automaticamente al crear la factura de forma secuencial. No se puede modificar.
                    </p>
                </div>
            </x-agro.form-section>

            <x-agro.form-section title="Fecha de Factura">
                <div class="max-w-md">
                    <flux:field>
                        <flux:label required>Fecha de factura</flux:label>
                        <flux:input wire:model="invoice_date" id="invoice_date" data-cy="invoice-date" type="date" required />
                        <flux:error name="invoice_date" />
                    </flux:field>
                </div>
            </x-agro.form-section>

            <x-agro.form-section title="Cosechas para Facturar">
                @if($fromHarvestRoute)
                    <flux:callout variant="warning" class="mb-4">
                        <strong>Obligatorio:</strong> Debes seleccionar al menos una cosecha para crear la factura.
                    </flux:callout>
                @endif
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <flux:field>
                            <flux:label>Filtrar por Campana</flux:label>
                            <flux:select wire:model.live="selectedCampaign" id="selectedCampaign" data-cy="selected-campaign">
                                <option value="">Todas las campanas</option>
                                @foreach($campaigns as $campaign)
                                    <option value="{{ $campaign->id }}">{{ $campaign->year }}</option>
                                @endforeach
                            </flux:select>
                        </flux:field>
                        <div class="md:col-span-2">
                            <flux:field>
                                <flux:label :required="$fromHarvestRoute">Selecciona una cosecha para anadir</flux:label>
                                <flux:select
                                    wire:model.live="selectedHarvestId"
                                    wire:change="addHarvestToInvoice"
                                    id="selectedHarvestId"
                                    data-cy="selected-harvest-id"
                                    :required="$fromHarvestRoute"
                                >
                                    <option value="">-- Selecciona una cosecha sin facturar --</option>
                                    @foreach($availableHarvests as $harvest)
                                        <option value="{{ $harvest->id }}">
                                            {{ $harvest->plotPlanting->grapeVariety->name ?? 'Sin variedad' }} -
                                            {{ $harvest->activity->plot->name ?? 'Sin parcela' }} -
                                            {{ $harvest->harvest_start_date->format('d/m/Y') }} -
                                            {{ number_format($harvest->total_weight, 2) }} kg
                                            @if($harvest->price_per_kg)
                                                ({{ number_format($harvest->price_per_kg, 4) }} €/kg)
                                            @endif
                                        </option>
                                    @endforeach
                                </flux:select>
                                @if($fromHarvestRoute && $errors->has('items'))
                                    <flux:error name="items" />
                                @endif
                            </flux:field>
                            <p class="mt-2 text-xs text-zinc-500">
                                La cosecha se anadira automaticamente como item al seleccionarla
                            </p>
                        </div>
                    </div>
                    @if($availableHarvests->isEmpty())
                        <flux:callout variant="info">
                            <strong>No hay cosechas disponibles para facturar.</strong><br>
                            Todas las cosechas ya han sido facturadas o no hay cosechas registradas.
                        </flux:callout>
                    @endif
                </div>
            </x-agro.form-section>

            <x-agro.form-section title="Items de la Factura">
                @if(!$fromHarvestRoute)
                    <flux:callout variant="info" class="mb-4">
                        <strong>Tip:</strong> Puedes anadir items manualmente o seleccionar una cosecha arriba para pre-llenar automaticamente.
                    </flux:callout>
                @else
                    <flux:callout variant="info" class="mb-4">
                        <strong>Facturacion de Cosecha:</strong> Los items deben estar vinculados a cosechas. Puedes anadir mas cosechas si lo necesitas.
                    </flux:callout>
                @endif
                <div class="space-y-4">
                @forelse($items as $index => $item)
                        <div class="border-2 border-zinc-200 rounded-lg p-4 bg-white hover:border-agro-300 transition-colors shadow-xs" data-cy="invoice-item" data-cy-item-index="{{ $index }}">
                            <div class="flex justify-between items-start mb-3">
                                <div class="flex items-center gap-2">
                                    <h4 class="text-base font-bold text-zinc-900">Item #{{ $index + 1 }}</h4>
                                    @if(isset($item['harvest_id']))
                                        <flux:badge color="purple" size="sm">Cosecha</flux:badge>
                                    @endif
                                </div>
                                @if(count($items) > 1)
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
                                <!-- Nombre del concepto - Full width -->
                                <div class="md:col-span-12">
                                    <flux:field>
                                        <flux:label class="text-xs">Nombre del concepto <span class="text-red-500">*</span></flux:label>
                                        <flux:input
                                            wire:model="items.{{ $index }}.name"
                                            placeholder="Ej: Uva Tempranillo, Servicio de recoleccion..."
                                            class="text-sm"
                                            data-cy="item-name"
                                            data-cy-item-index="{{ $index }}"
                                        />
                                        <flux:error name="items.{{ $index }}.name" />
                                    </flux:field>
                                </div>

                                <!-- Descripcion y SKU -->
                                <div class="md:col-span-8">
                                    <flux:field>
                                        <flux:label class="text-xs">Descripcion</flux:label>
                                        <flux:textarea
                                            wire:model="items.{{ $index }}.description"
                                            rows="2"
                                            placeholder="Descripcion detallada..."
                                            class="text-sm"
                                            data-cy="item-description"
                                            data-cy-item-index="{{ $index }}"
                                        />
                                    </flux:field>
                                </div>
                                <div class="md:col-span-4">
                                    <flux:field>
                                        <flux:label class="text-xs">SKU / Codigo</flux:label>
                                        <flux:input
                                            wire:model="items.{{ $index }}.sku"
                                            placeholder="Codigo"
                                            class="text-sm"
                                            data-cy="item-sku"
                                            data-cy-item-index="{{ $index }}"
                                        />
                                    </flux:field>
                                    <div class="mt-2">
                                        <flux:field>
                                            <flux:label class="text-xs">Tipo</flux:label>
                                            <flux:select wire:model="items.{{ $index }}.concept_type" class="text-sm" data-cy="item-concept-type" data-cy-item-index="{{ $index }}">
                                                <option value="harvest">Cosecha</option>
                                                <option value="service">Servicio</option>
                                                <option value="product">Producto</option>
                                                <option value="other">Otro</option>
                                            </flux:select>
                                        </flux:field>
                                    </div>
                                </div>

                                <!-- Cantidad, Precio, Descuento, Impuesto -->
                                <div class="md:col-span-3">
                                    <flux:field>
                                        <flux:label class="text-xs">Cantidad <span class="text-red-500">*</span></flux:label>
                                        <flux:input
                                            wire:model.live="items.{{ $index }}.quantity"
                                            type="number"
                                            step="0.001"
                                            min="0.001"
                                            placeholder="0.000"
                                            class="text-sm"
                                            data-cy="item-quantity"
                                            data-cy-item-index="{{ $index }}"
                                        />
                                        <flux:error name="items.{{ $index }}.quantity" />
                                    </flux:field>
                                </div>
                                <div class="md:col-span-3">
                                    <flux:field>
                                        <flux:label class="text-xs">Precio €/ud <span class="text-red-500">*</span></flux:label>
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

                            @php
                                $itemQuantity = (float)($item['quantity'] ?? 0);
                                $itemUnitPrice = (float)($item['unit_price'] ?? 0);
                                $itemDiscount = (float)($item['discount_percentage'] ?? 0);
                                $itemSubtotal = $itemQuantity * $itemUnitPrice;
                                $itemDiscountAmount = $itemSubtotal * ($itemDiscount / 100);
                                $itemSubtotalAfterDiscount = $itemSubtotal - $itemDiscountAmount;

                                $selectedTax = $item['tax_id'] ? $availableTaxes->firstWhere('id', $item['tax_id']) : null;
                                $taxRate = $selectedTax ? $selectedTax->rate : 0;
                                $itemTaxAmount = $itemSubtotalAfterDiscount * ($taxRate / 100);
                                $itemTotal = $itemSubtotalAfterDiscount + $itemTaxAmount;
                            @endphp

                            <div class="mt-3 pt-3 border-t border-zinc-200 bg-zinc-50 -mx-4 -mb-4 px-4 py-2 rounded-b-lg">
                                <div class="flex flex-wrap gap-x-6 gap-y-1 text-xs">
                                    <div class="flex items-center gap-1">
                                        <span class="text-zinc-500">Subtotal:</span>
                                        <span class="font-semibold text-zinc-900">{{ number_format($itemSubtotal, 2) }} €</span>
                                    </div>
                                    @if($itemDiscount > 0)
                                        <div class="flex items-center gap-1">
                                            <span class="text-zinc-500">Dto:</span>
                                            <span class="font-semibold text-red-600">-{{ number_format($itemDiscountAmount, 2) }} €</span>
                                        </div>
                                    @endif
                                    <div class="flex items-center gap-1">
                                        <span class="text-zinc-500">Base:</span>
                                        <span class="font-semibold text-zinc-900">{{ number_format($itemSubtotalAfterDiscount, 2) }} €</span>
                                    </div>
                                    @if($selectedTax)
                                        <div class="flex items-center gap-1">
                                            <span class="text-zinc-500">IVA ({{ number_format($taxRate, 2) }}%):</span>
                                            <span class="font-semibold text-zinc-900">{{ number_format($itemTaxAmount, 2) }} €</span>
                                        </div>
                                    @endif
                                    <div class="flex items-center gap-1 ml-auto">
                                        <span class="text-zinc-500 font-semibold">Total:</span>
                                        <span class="text-base font-bold text-green-600">{{ number_format($itemTotal, 2) }} €</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 border-2 border-dashed border-zinc-300 rounded-xl">
                            <p class="text-zinc-500 mb-4">
                                No hay items en la factura.
                            </p>
                            <p class="text-sm text-zinc-400">
                                Selecciona una cosecha arriba o anade un concepto manual
                            </p>
                        </div>
                    @endforelse

                    <!-- Boton para anadir conceptos manuales (no cosechas) -->
                    <div class="flex justify-center pt-4 border-t border-zinc-200 mt-6">
                        <flux:button
                            type="button"
                            wire:click="addItem"
                            variant="outline"
                            icon="plus"
                            data-cy="add-item-button"
                        >
                            Anadir Concepto Manual
                        </flux:button>
                    </div>
                    <p class="text-xs text-center text-zinc-500 mt-2">
                        Para servicios, productos u otros conceptos que no sean cosechas
                    </p>
                </div>
            </x-agro.form-section>

            <x-agro.form-section title="Observaciones">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:field>
                        <flux:label>Observaciones generales</flux:label>
                        <flux:textarea wire:model="observations" id="observations" data-cy="observations" rows="3" placeholder="Notas internas..." />
                        <flux:error name="observations" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Observaciones en factura</flux:label>
                        <flux:textarea wire:model="observations_invoice" id="observations_invoice" data-cy="observations-invoice" rows="3" placeholder="Texto que aparecera en la factura..." />
                        <flux:error name="observations_invoice" />
                    </flux:field>
                </div>
            </x-agro.form-section>

            <x-agro.form-actions :cancel-url="route('viticulturist.invoices.index')" submit-label="Crear Factura" />
        </form>
    </x-agro.card>
</div>
