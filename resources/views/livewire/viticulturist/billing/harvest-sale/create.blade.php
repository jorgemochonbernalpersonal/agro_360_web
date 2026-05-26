<div>
    <x-agro.form-card
        title="{{ __('Nueva Factura de Vendimia') }}"
        :description="__('Emite una factura por la uva cosechada')"
        :back-url="roleRoute('viticulturist.invoices.harvest-sale.index')">

        <form wire:submit.prevent="save" class="space-y-8">

            <!-- Datos de la Factura -->
            <x-agro.form-section title="{{ __('Datos de la Factura') }}">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <flux:field>
                        <flux:label for="buyer_name">{{ __('Comprador *') }}</flux:label>
                        <flux:input
                            wire:model.live="buyer_name"
                            id="buyer_name"
                            list="buyers-list"
                            placeholder="{{ __('Nombre del comprador') }}"
                            required />
                        <datalist id="buyers-list">
                            @foreach ($buyers as $b)
                                <option value="{{ $b }}">{{ $b }}</option>
                            @endforeach
                        </datalist>
                        <flux:error name="buyer_name" />
                    </flux:field>

                    <flux:field>
                        <flux:label for="destination_type">{{ __('Destino *') }}</flux:label>
                        <flux:select wire:model="destination_type" id="destination_type">
                            <option value="cooperative">{{ __('Cooperativa') }}</option>
                            <option value="third_party">{{ __('Terceros (bodega compradora)') }}</option>
                            <option value="own_winery">{{ __('Bodega propia') }}</option>
                            <option value="other">{{ __('Otro') }}</option>
                        </flux:select>
                    </flux:field>

                    <flux:field>
                        <flux:label for="invoice_date">{{ __('Fecha Factura *') }}</flux:label>
                        <flux:input wire:model="invoice_date" type="date" id="invoice_date" required />
                        <flux:error name="invoice_date" />
                    </flux:field>

                    <flux:field>
                        <flux:label for="delivery_date">{{ __('Fecha Entrega *') }}</flux:label>
                        <flux:input wire:model="delivery_date" type="date" id="delivery_date" required />
                        <flux:error name="delivery_date" />
                    </flux:field>

                    <flux:field>
                        <flux:label for="payment_type">{{ __('Forma de pago') }}</flux:label>
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

            <!-- Datos del Albarán -->
            <x-agro.form-section title="{{ __('Datos del Albarán (opcional)') }}">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <flux:field>
                        <flux:label for="buyer_rega_code">{{ __('Código REGA') }}</flux:label>
                        <flux:input wire:model="buyer_rega_code" id="buyer_rega_code" :placeholder="__('ES/...')" />
                    </flux:field>
                    <flux:field>
                        <flux:label for="transport_document">{{ __('Nº Albarán Transporte') }}</flux:label>
                        <flux:input wire:model="transport_document" id="transport_document" />
                    </flux:field>
                    <flux:field>
                        <flux:label for="vehicle_plate">{{ __('Matrícula') }}</flux:label>
                        <flux:input wire:model="vehicle_plate" id="vehicle_plate" :placeholder="__('1234-ABC')" />
                    </flux:field>
                </div>
            </x-agro.form-section>

            <!-- Cosechas disponibles -->
            <x-agro.form-section title="{{ __('Cosechas Disponibles') }}">
                @if ($availableHarvests->isEmpty())
                    <p class="text-zinc-500 text-sm">{{ __('No tienes cosechas con stock disponible.') }}</p>
                @else
                    <p class="text-sm text-zinc-500 mb-3">{{ __('Selecciona las cosechas a incluir en la factura:') }}</p>
                    <div class="space-y-2">
                        @foreach ($availableHarvests as $harvest)
                            @php $selected = in_array($harvest->id, $selectedIds); @endphp
                            <div class="flex items-center gap-3 p-3 rounded-lg border {{ $selected ? 'border-agro-400 bg-agro-50' : 'border-zinc-200' }}">
                                <flux:checkbox wire:click="toggleHarvest({{ $harvest->id }})" :checked="$selected" />
                                <div class="flex-1 grid grid-cols-2 md:grid-cols-4 gap-2 text-sm">
                                    <span class="font-medium">
                                        Cosecha #{{ $harvest->id }}
                                        @if($harvest->activity?->campaign)
                                            <span class="text-zinc-400 font-normal text-xs ml-1">{{ $harvest->activity->campaign->name }}</span>
                                        @endif
                                    </span>
                                    <span class="text-zinc-600">
                                        {{ $harvest->harvest_start_date?->format('d/m/Y') ?? '—' }}
                                    </span>
                                    <span>
                                        {{ $harvest->activity?->plotPlanting?->plotVariety?->grapeVariety?->name ?? '—' }}
                                    </span>
                                    <div class="text-right space-y-0.5">
                                        <p class="font-semibold text-agro-700">{{ number_format($harvest->available_qty, 0) }} kg disponibles</p>
                                        @if($harvest->reserved_qty > 0)
                                            <p class="text-xs text-amber-600">{{ number_format($harvest->reserved_qty, 0) }} kg reservados</p>
                                        @endif
                                        @if($harvest->sold_qty > 0)
                                            <p class="text-xs text-zinc-400">{{ number_format($harvest->sold_qty, 0) }} kg vendidos</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-agro.form-section>

            <!-- Detalle de líneas -->
            @if (!empty($lines))
                <x-agro.form-section title="{{ __('Detalle de Líneas') }}">
                    <div class="space-y-4">
                        @foreach ($lines as $i => $line)
                            <div class="grid grid-cols-12 gap-3 items-end p-4 bg-zinc-50 rounded-lg" wire:key="hs-create-line-{{ $i }}">
                                <div class="col-span-12 md:col-span-4">
                                    <p class="text-sm font-medium text-zinc-700">Cosecha #{{ $line['harvest_id'] }}</p>
                                    <p class="text-xs text-zinc-500">{{ $line['quantity'] }} kg</p>
                                </div>

                                <div class="col-span-4 md:col-span-2">
                                    <flux:field>
                                        <flux:label>{{ __('Kg *') }}</flux:label>
                                        <flux:input wire:model.live.debounce.300ms="lines.{{ $i }}.quantity" type="number" step="0.001" min="0.001" />
                                        <flux:error name="lines.{{ $i }}.quantity" />
                                    </flux:field>
                                </div>

                                <div class="col-span-4 md:col-span-2">
                                    <flux:field>
                                        <flux:label>{{ __('€/kg *') }}</flux:label>
                                        <flux:input wire:model.live.debounce.300ms="lines.{{ $i }}.unit_price" type="number" step="0.001" min="0" placeholder="0.000" />
                                        <flux:error name="lines.{{ $i }}.unit_price" />
                                    </flux:field>
                                </div>

                                <div class="col-span-4 md:col-span-1">
                                    <flux:field>
                                        <flux:label>{{ __('IRPF %') }}</flux:label>
                                        <flux:input wire:model.live.debounce.300ms="lines.{{ $i }}.tax_rate" type="number" step="0.01" min="0" max="100" />
                                    </flux:field>
                                </div>

                                <div class="col-span-10 md:col-span-2">
                                    <flux:field>
                                        <flux:label>{{ __('Descripción') }}</flux:label>
                                        <flux:input wire:model="lines.{{ $i }}.description" type="text" :placeholder="__('Opcional')" />
                                    </flux:field>
                                </div>

                                <div class="col-span-2 md:col-span-1 flex flex-col items-end gap-1">
                                    @php
                                        $qty   = (float)($line['quantity'] ?? 0);
                                        $price = (float)($line['unit_price'] ?? 0);
                                        $tax   = (float)($line['tax_rate'] ?? 0);
                                        $sub   = $qty * $price;
                                        $net   = $sub - ($sub * $tax / 100);
                                    @endphp
                                    <span class="text-xs text-zinc-500">{{ __('Total') }}</span>
                                    <span class="font-semibold text-sm">{{ number_format($net, 2) }} €</span>
                                    <flux:button wire:click="removeLine({{ $i }})" size="xs" variant="ghost" icon="trash" class="text-red-500" />
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Totales -->
                    @php
                        $grandSub = 0; $grandTax = 0;
                        foreach ($lines as $line) {
                            $s = (float)($line['quantity'] ?? 0) * (float)($line['unit_price'] ?? 0);
                            $grandSub += $s;
                            $grandTax += $s * ((float)($line['tax_rate'] ?? 0) / 100);
                        }
                    @endphp
                    <div class="flex justify-end mt-4">
                        <div class="w-64 space-y-1 text-sm">
                            <div class="flex justify-between">
                                <span class="text-zinc-500">{{ __('Base:') }}</span>
                                <span>{{ number_format($grandSub, 2) }} €</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-zinc-500">{{ __('Retención IRPF:') }}</span>
                                <span>-{{ number_format($grandTax, 2) }} €</span>
                            </div>
                            <div class="flex justify-between font-bold text-base border-t pt-1">
                                <span>{{ __('A cobrar:') }}</span>
                                <span>{{ number_format($grandSub - $grandTax, 2) }} €</span>
                            </div>
                        </div>
                    </div>
                    <flux:error name="lines" />
                </x-agro.form-section>
            @endif

            <!-- Observaciones -->
            <x-agro.form-section title="{{ __('Observaciones') }}">
                <flux:field>
                    <flux:textarea wire:model="observations" rows="3" :placeholder="__('Observaciones para la factura...')" />
                </flux:field>
            </x-agro.form-section>

            <x-agro.form-actions :cancel-url="roleRoute('viticulturist.invoices.harvest-sale.index')" submit-:label="__('Crear Factura')" />
        </form>
    </x-agro.form-card>
</div>
