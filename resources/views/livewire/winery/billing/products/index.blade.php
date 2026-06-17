<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="{{ __('Venta de Productos') }}"
        :description="__('Facturas y albaranes de venta de productos a clientes')"
    />

    {{-- Toolbar --}}
    <div class="space-y-3">
        <div class="flex items-center gap-3">

            <x-agro.search-input wire:model.live.debounce.300ms="search" :placeholder="__('Buscar por nº factura, albarán o cliente...')" />

            @php $filterCount = ($filterStatus ? 1 : 0) + ($filterPaymentStatus ? 1 : 0) + ($filterDeliveryStatus ? 1 : 0) + ($filterGift ? 1 : 0); @endphp
            <x-agro.filter-button modal="products-invoice-filters" :count="$filterCount" />

            <flux:button wire:click="openExportModal" variant="outline" icon="arrow-down-tray">{{ __('Exportar') }}</flux:button>

            <x-agro.divider-vertical />

            <flux:button wire:click="openQuickModal" variant="outline" icon="bolt">{{ __('Rápida') }}</flux:button>

            <flux:button href="{{ roleRoute('invoices.products.create') }}" wire:navigate variant="primary" icon="plus">
                Nueva Factura
            </flux:button>

        </div>

        {{-- Active filter chips --}}
        @if($search || $filterStatus || $filterPaymentStatus || $filterDeliveryStatus || $filterGift)
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs text-zinc-400">{{ __('Filtros activos:') }}</span>

                @if($search)
                    <x-agro.filter-chip icon="magnifying-glass" :label="'&quot;' . $search . '&quot;'" wireRemove="$set('search', '')" />
                @endif

                @if($filterStatus)
                    @php $statusLabels = ['draft' => 'Borrador', 'sent' => 'Emitida', 'cancelled' => 'Cancelada']; @endphp
                    <x-agro.filter-chip :label="'Factura: ' . ($statusLabels[$filterStatus] ?? $filterStatus)" wireRemove="$set('filterStatus', '')" />
                @endif

                @if($filterPaymentStatus)
                    @php $payLabels = ['unpaid' => 'Pendiente', 'partial' => 'Parcial', 'paid' => 'Cobrada']; @endphp
                    <x-agro.filter-chip :label="'Cobro: ' . ($payLabels[$filterPaymentStatus] ?? $filterPaymentStatus)" wireRemove="$set('filterPaymentStatus', '')" />
                @endif

                @if($filterDeliveryStatus)
                    @php $delivLabels = ['pending' => 'Pendiente', 'delivered' => 'Entregada', 'cancelled' => 'Cancelada']; @endphp
                    <x-agro.filter-chip :label="'Entrega: ' . ($delivLabels[$filterDeliveryStatus] ?? $filterDeliveryStatus)" wireRemove="$set('filterDeliveryStatus', '')" />
                @endif

                @if($filterGift)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-pink-50 text-pink-700 text-xs font-medium rounded-full border border-pink-200">
                        Solo regalos
                        <button wire:click="$set('filterGift', false)" class="hover:text-pink-900 ml-0.5"><flux:icon icon="x-mark" class="size-3" /></button>
                    </span>
                @endif

                <flux:button wire:click="clearFilters" variant="ghost" size="sm">{{ __('Limpiar todo') }}</flux:button>
            </div>
        @endif
    </div>

    {{-- Gift counter --}}
    @if($giftCount > 0 && !$filterGift)
        <flux:button wire:click="$set('filterGift', true)" variant="ghost" size="sm" icon="gift"
            class="bg-pink-50 border border-pink-200 text-pink-700 hover:bg-pink-100 rounded-full">
            {{ $giftCount }} {{ $giftCount === 1 ? 'factura regalo' : 'facturas regalo' }}
            <span class="text-pink-400 ml-1">{{ __('· ver solo estas') }}</span>
        </flux:button>
    @endif

    {{-- Card grid --}}
    @if($invoices->count() > 0)
        <div
            class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6"
            wire:loading.class="opacity-60 pointer-events-none"
            wire:target="search, filterStatus, filterPaymentStatus, filterDeliveryStatus, clearFilters"
        >
            @foreach($invoices as $i => $invoice)
                @php
                    [$deliveryLabel, $deliveryColor] = match($invoice->delivery_status) {
                        'pending'    => ['Pendiente',   'yellow'],
                        'in_transit' => ['En tránsito', 'blue'],
                        'delivered'  => ['Entregada',   'green'],
                        'cancelled'  => ['Cancelada',   'red'],
                        default      => [ucfirst($invoice->delivery_status ?? ''), null],
                    };

                    [$paymentLabel, $paymentColor] = match($invoice->payment_status) {
                        'paid'    => ['Cobrada',   'green'],
                        'partial' => ['Parcial',   'blue'],
                        'unpaid'  => ['Pendiente', 'yellow'],
                        default   => [ucfirst($invoice->payment_status ?? ''), null],
                    };

                    $totalUnits = $invoice->items->sum('quantity');
                    $isLocked   = $invoice->delivery_status === 'delivered' || $invoice->status === 'cancelled';
                @endphp

                <x-agro.card
                    wire:key="pi-{{ $invoice->id }}"
                    class="animate-fade-in-up hover:-translate-y-1 flex flex-col {{ $invoice->status === 'cancelled' ? 'opacity-60' : '' }}"
                    style="animation-delay: {{ min($i * 50, 400) }}ms"
                >
                    <x-slot:header>
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 bg-zinc-100 rounded-full flex items-center justify-center shrink-0">
                                <flux:icon icon="document-text" class="size-4 text-zinc-500" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-1.5">
                                    <p class="font-semibold text-zinc-900 text-sm font-mono truncate leading-tight">
                                        {{ $invoice->invoice_number ?? __('Sin código de factura') }}
                                    </p>
                                    @if($invoice->corrective)
                                        <span class="text-[10px] font-bold text-orange-600 bg-orange-50 px-1.5 py-0.5 rounded shrink-0">R/</span>
                                    @endif
                                    @if($invoice->gift)
                                        <span class="text-[10px] font-bold text-pink-600 bg-pink-50 px-1.5 py-0.5 rounded shrink-0">{{ __('REGALO') }}</span>
                                    @endif
                                </div>
                                @if($invoice->delivery_note_code)
                                    <p class="text-xs text-zinc-400 font-mono leading-tight mt-0.5">Alb: {{ $invoice->delivery_note_code }}</p>
                                @endif
                            </div>
                            <flux:badge :color="$paymentColor" size="sm" class="shrink-0">{{ $paymentLabel }}</flux:badge>
                        </div>
                    </x-slot:header>

                    <div class="flex-1 space-y-3">
                        <div class="flex items-center gap-2">
                            <flux:icon icon="user" class="size-3.5 text-zinc-400 shrink-0" />
                            <span class="text-xs text-zinc-600 truncate">{{ $invoice->client?->full_name ?? '—' }}</span>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div class="bg-agro-50 rounded-xl p-2.5">
                                <p class="text-[10px] text-agro-600 font-medium uppercase tracking-wide mb-0.5">{{ __('Total') }}</p>
                                <p class="text-sm font-bold text-agro-700">{{ number_format($invoice->total_amount, 2) }} €</p>
                            </div>
                            <div class="bg-zinc-50 rounded-xl p-2.5">
                                <p class="text-[10px] text-zinc-500 font-medium uppercase tracking-wide mb-0.5">{{ __('Unidades') }}</p>
                                <p class="text-sm font-bold text-zinc-700">{{ $totalUnits > 0 ? number_format($totalUnits, 0) : '—' }}</p>
                            </div>
                        </div>

                        <div class="text-xs text-zinc-500 space-x-2">
                            @if($invoice->delivery_note_date)
                                <span>Alb: {{ \Carbon\Carbon::parse($invoice->delivery_note_date)->format('d/m/Y') }}</span>
                            @endif
                            @if($invoice->invoice_date)
                                <span>Fac: {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y') }}</span>
                            @endif
                        </div>
                    </div>

                    <x-slot:footer>
                        <div class="flex items-center justify-between gap-2">
                            <flux:badge :color="$deliveryColor" size="sm" class="shrink-0">{{ $deliveryLabel }}</flux:badge>
                            <div class="flex items-center gap-0.5 flex-wrap justify-end">

                                @if(!$isLocked)
                                    <x-agro.action-button variant="edit" href="{{ roleRoute('invoices.products.edit', $invoice->id) }}" wire:navigate title="{{ __('Editar') }}" />
                                @endif

                                @if($invoice->status !== 'cancelled')
                                    <x-agro.action-button icon="document-duplicate" variant="default" wire:click="duplicate({{ $invoice->id }})" wire:confirm="{{ __('¿Duplicar esta factura? Se creará una nueva en borrador con los mismos productos y cantidades.') }}" title="{{ __('Duplicar factura') }}" />
                                @endif

                                <x-agro.action-button icon="document-text" variant="default" href="{{ roleRoute('invoices.products.pdf', $invoice->id) }}" target="_blank" title="{{ __('Descargar Factura') }}" />

                                <x-agro.action-button icon="document-arrow-down" variant="default" href="{{ roleRoute('invoices.products.delivery-note-pdf', $invoice->id) }}" target="_blank" title="{{ __('Descargar Albarán') }}" />

                                <x-agro.action-button icon="currency-euro" variant="default" href="{{ roleRoute('invoices.products.valorado-pdf', $invoice->id) }}" target="_blank" title="{{ __('Descargar Albarán Valorado') }}" />

                                @if($invoice->status === 'draft')
                                    <x-agro.action-button icon="paper-airplane" variant="primary" wire:click="openEmitirModal({{ $invoice->id }})" title="{{ __('Emitir factura') }}" />
                                @endif

@if($invoice->status === 'sent' && ($invoice->billing_email ?: $invoice->client?->email))
                                    <x-agro.action-button icon="envelope" variant="default" wire:click="sendEmail({{ $invoice->id }})" wire:loading.attr="disabled" wire:target="sendEmail({{ $invoice->id }})" title="{{ __('Enviar por email') }}" />
                                @endif

                                @if($invoice->status === 'sent' && !$invoice->corrective && $invoice->correctives_count === 0)
                                    <x-agro.action-button icon="arrow-uturn-left" variant="warning" wire:click="openCorrectiveModal({{ $invoice->id }})" title="{{ __('Crear rectificativa') }}" />
                                @endif

                                @if($invoice->correctives_count > 0)
                                    <span class="text-[10px] font-medium text-orange-600 bg-orange-50 px-1.5 py-0.5 rounded">{{ __('RECT') }}</span>
                                @endif


                            </div>
                        </div>
                    </x-slot:footer>
                </x-agro.card>
            @endforeach
        </div>

        <x-agro.pagination :paginator="$invoices" />

    @else
        <x-agro.empty-state
            icon="document-text"
            title="{{ __('No hay facturas de productos') }}"
            :description="$search || $filterStatus || $filterPaymentStatus || $filterDeliveryStatus
                ? 'Ninguna factura coincide con los filtros aplicados.'
                : 'Crea tu primera factura para empezar a gestionar la venta de productos.'"
        >
            @if($search || $filterStatus || $filterPaymentStatus || $filterDeliveryStatus)
                <x-slot:action>
                    <flux:button wire:click="clearFilters" variant="outline" icon="x-mark">{{ __('Limpiar filtros') }}</flux:button>
                </x-slot:action>
            @else
                <x-slot:action>
                    <flux:button href="{{ roleRoute('invoices.products.create') }}" wire:navigate variant="primary" icon="plus">
                        Nueva Factura
                    </flux:button>
                </x-slot:action>
            @endif
        </x-agro.empty-state>
    @endif

    {{-- Modal: Emitir Factura --}}
    @if($emitirModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
             x-data x-on:keydown.escape.window="$wire.closeEmitirModal()">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm" @click.stop>
                <div class="px-6 py-4 border-b border-zinc-200 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-agro-100 rounded-lg flex items-center justify-center">
                            <flux:icon icon="paper-airplane" class="size-4 text-agro-600" />
                        </div>
                        <h3 class="text-base font-semibold text-zinc-900">{{ __('Emitir Factura') }}</h3>
                    </div>
                    <flux:button wire:click="closeEmitirModal" variant="ghost" size="sm" icon="x-mark" />
                </div>
                <div class="px-6 py-5 space-y-4">
                    <p class="text-sm text-zinc-500">
                        Se generará un número de factura secuencial. El stock permanece <strong>{{ __('reservado') }}</strong> hasta confirmar la entrega.
                    </p>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1.5">{{ __('Fecha de factura') }} <span class="text-red-500">*</span></label>
                        <input wire:model="emitirDate" type="date"
                            class="w-full px-3 py-2 text-sm bg-white border border-zinc-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-agro-400 focus:border-transparent" />
                        @error('emitirDate') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="px-6 py-4 bg-zinc-50 border-t border-zinc-200 rounded-b-2xl flex justify-end gap-3">
                    <flux:button wire:click="closeEmitirModal" variant="ghost" size="sm">{{ __('Cancelar') }}</flux:button>
                    <flux:button wire:click="confirmEmitir" wire:loading.attr="disabled" variant="primary" size="sm" icon="paper-airplane">
                        <span wire:loading.remove wire:target="confirmEmitir">{{ __('Emitir Factura') }}</span>
                        <span wire:loading wire:target="confirmEmitir">{{ __('Emitiendo...') }}</span>
                    </flux:button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal: Rectificativa --}}
    @if($correctiveModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
             x-data x-on:keydown.escape.window="$wire.closeCorrectiveModal()">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm" @click.stop>
                <div class="px-6 py-4 border-b border-zinc-200 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                            <flux:icon icon="arrow-uturn-left" class="size-4 text-orange-600" />
                        </div>
                        <h3 class="text-base font-semibold text-zinc-900">{{ __('Crear Rectificativa') }}</h3>
                    </div>
                    <flux:button wire:click="closeCorrectiveModal" variant="ghost" size="sm" icon="x-mark" />
                </div>
                <div class="px-6 py-5 space-y-4">
                    <p class="text-sm text-zinc-500">{{ __('Se creará una factura rectificativa con importes negativos. El stock quedará restaurado como disponible.') }}</p>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1.5">{{ __('Fecha') }} <span class="text-red-500">*</span></label>
                        <input wire:model="correctiveDate" type="date"
                            class="w-full px-3 py-2 text-sm bg-white border border-zinc-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-transparent" />
                        @error('correctiveDate') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1.5">{{ __('Motivo') }} <span class="text-zinc-400 font-normal">{{ __('(opcional)') }}</span></label>
                        <textarea wire:model="correctiveReason" rows="2" placeholder="{{ __('Error en precio, devolución, etc.') }}"
                            class="w-full px-3 py-2 text-sm bg-white border border-zinc-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-transparent resize-none"></textarea>
                        @error('correctiveReason') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="px-6 py-4 bg-zinc-50 border-t border-zinc-200 rounded-b-2xl flex justify-end gap-3">
                    <flux:button wire:click="closeCorrectiveModal" variant="ghost" size="sm">{{ __('Cancelar') }}</flux:button>
                    <flux:button wire:click="confirmCorrective" wire:loading.attr="disabled" variant="primary" size="sm" icon="arrow-uturn-left">
                        <span wire:loading.remove wire:target="confirmCorrective">{{ __('Emitir Rectificativa') }}</span>
                        <span wire:loading wire:target="confirmCorrective">{{ __('Generando...') }}</span>
                    </flux:button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal: Factura Rápida --}}
    @if($quickModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
             x-data x-on:keydown.escape.window="$wire.closeQuickModal()">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg" @click.stop>
                <div class="px-6 py-4 border-b border-zinc-200 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                            <flux:icon icon="bolt" class="size-4 text-blue-600" />
                        </div>
                        <h3 class="text-base font-semibold text-zinc-900">{{ __('Albarán Rápido') }}</h3>
                    </div>
                    <flux:button wire:click="closeQuickModal" variant="ghost" size="sm" icon="x-mark" />
                </div>
                <div class="px-6 py-5 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <flux:label>{{ __('Cliente') }} <span class="text-red-500">*</span></flux:label>
                            <flux:select wire:model.live="quickClientId">
                                <flux:select.option value="">{{ __('Selecciona cliente') }}</flux:select.option>
                                @foreach(\App\Models\Client::where('user_id', Auth::id())->where('active', true)->orderBy('first_name')->get() as $c)
                                    <flux:select.option value="{{ $c->id }}">{{ $c->full_name }}</flux:select.option>
                                @endforeach
                            </flux:select>
                            @error('quickClientId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <flux:label>{{ __('Dirección') }} <span class="text-red-500">*</span></flux:label>
                            <flux:select wire:model="quickClientAddressId">
                                <flux:select.option value="">{{ __('Selecciona dirección') }}</flux:select.option>
                                @foreach($quickAvailableAddresses as $addr)
                                    <flux:select.option value="{{ $addr->id }}">{{ $addr->full_address ?? $addr->address ?? '' }}</flux:select.option>
                                @endforeach
                            </flux:select>
                            @error('quickClientAddressId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <flux:label>{{ __('Producto') }} <span class="text-red-500">*</span></flux:label>
                        <flux:select wire:model.live="quickLotId">
                            <flux:select.option value="">{{ __('Selecciona lote con stock') }}</flux:select.option>
                            @foreach(\App\Models\ProductLot::where('user_id', Auth::id())->where('archived', false)->where('available_quantity', '>', 0)->orderByDesc('vintage')->orderBy('name')->get() as $wl)
                                <flux:select.option value="{{ $wl->id }}">{{ $wl->name }}@if($wl->vintage) ({{ $wl->vintage }})@endif – {{ number_format($wl->available_quantity, 0) }} {{ $wl->unit }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        @error('quickLotId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1.5">{{ __('Concepto') }} <span class="text-red-500">*</span></label>
                        <input wire:model="quickConceptName" type="text" placeholder="{{ __('Nombre del producto') }}"
                            class="w-full px-3 py-2 text-sm bg-white border border-zinc-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-agro-400" />
                        @error('quickConceptName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 mb-1.5">{{ __('Cantidad') }} <span class="text-red-500">*</span></label>
                            <input wire:model="quickQty" type="number" step="0.001" min="0.001"
                                @if($quickAvailableQty > 0) max="{{ $quickAvailableQty }}" @endif
                                placeholder="0.000"
                                class="w-full px-3 py-2 text-sm bg-white border border-zinc-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-agro-400" />
                            @if($quickAvailableQty > 0)
                                <p class="mt-1 text-xs text-zinc-400">Disponible: {{ number_format($quickAvailableQty, 0) }}</p>
                            @endif
                            @error('quickQty') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 mb-1.5">{{ __('Precio/ud (€)') }} <span class="text-red-500">*</span></label>
                            <input wire:model="quickPrice" type="number" step="0.0001" min="0" placeholder="0.0000"
                                class="w-full px-3 py-2 text-sm bg-white border border-zinc-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-agro-400" />
                            @error('quickPrice') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <flux:label>{{ __('Impuesto') }}</flux:label>
                            <flux:select wire:model="quickTaxId">
                                <flux:select.option value="">{{ __('Sin impuesto') }}</flux:select.option>
                                @foreach(\App\Models\Tax::where('active', true)->orderBy('rate')->get() as $t)
                                    <flux:select.option value="{{ $t->id }}">{{ $t->name }} ({{ number_format($t->rate, 2) }}%)</flux:select.option>
                                @endforeach
                            </flux:select>
                        </div>
                        <div>
                            <flux:label>{{ __('Forma de pago') }}</flux:label>
                            <flux:select wire:model="quickPaymentType">
                                <flux:select.option value="">{{ __('Sin especificar') }}</flux:select.option>
                                <flux:select.option value="cash">{{ __('Efectivo') }}</flux:select.option>
                                <flux:select.option value="transfer">{{ __('Transferencia') }}</flux:select.option>
                                <flux:select.option value="check">{{ __('Cheque') }}</flux:select.option>
                                <flux:select.option value="other">{{ __('Otro') }}</flux:select.option>
                            </flux:select>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 bg-zinc-50 border-t border-zinc-200 rounded-b-2xl flex justify-end gap-3">
                    <flux:button wire:click="closeQuickModal" variant="ghost" size="sm">{{ __('Cancelar') }}</flux:button>
                    <flux:button wire:click="confirmQuick" wire:loading.attr="disabled" variant="primary" size="sm" icon="bolt">
                        <span wire:loading.remove wire:target="confirmQuick">{{ __('Crear Albarán') }}</span>
                        <span wire:loading wire:target="confirmQuick">{{ __('Creando...') }}</span>
                    </flux:button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal: Exportar --}}
    @if($exportModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
             x-data x-on:keydown.escape.window="$wire.closeExportModal()">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm" @click.stop>
                <div class="px-6 py-4 border-b border-zinc-200 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                            <flux:icon icon="arrow-down-tray" class="size-4 text-green-600" />
                        </div>
                        <h3 class="text-base font-semibold text-zinc-900">{{ __('Exportar Facturas') }}</h3>
                    </div>
                    <flux:button wire:click="closeExportModal" variant="ghost" size="sm" icon="x-mark" />
                </div>
                <div class="px-6 py-5 space-y-4">
                    <p class="text-sm text-zinc-500">{{ __('Exporta a Excel las facturas emitidas en el rango de fechas seleccionado.') }}</p>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1.5">{{ __('Desde') }} <span class="text-red-500">*</span></label>
                        <input wire:model="exportDateFrom" type="date"
                            class="w-full px-3 py-2 text-sm bg-white border border-zinc-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-agro-400" />
                        @error('exportDateFrom') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1.5">{{ __('Hasta') }} <span class="text-red-500">*</span></label>
                        <input wire:model="exportDateTo" type="date"
                            class="w-full px-3 py-2 text-sm bg-white border border-zinc-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-agro-400" />
                        @error('exportDateTo') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="px-6 py-4 bg-zinc-50 border-t border-zinc-200 rounded-b-2xl flex justify-end gap-3">
                    <flux:button wire:click="closeExportModal" variant="ghost" size="sm">{{ __('Cancelar') }}</flux:button>
                    <flux:button wire:click="export" wire:loading.attr="disabled" variant="primary" size="sm" icon="arrow-down-tray">
                        <span wire:loading.remove wire:target="export">{{ __('Descargar Excel') }}</span>
                        <span wire:loading wire:target="export">{{ __('Generando...') }}</span>
                    </flux:button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal: Filtros --}}
    <x-agro.filter-modal name="products-invoice-filters" :hasActiveFilters="$filterCount > 0" clearAction="clearFilters">
        <x-agro.filter-select :label="__('Estado de factura')" wire:model.live="filterStatus" :placeholder="__('Todos')">
            <flux:select.option value="draft">{{ __('Borrador') }}</flux:select.option>
            <flux:select.option value="sent">{{ __('Emitida') }}</flux:select.option>
            <flux:select.option value="cancelled">{{ __('Cancelada') }}</flux:select.option>
        </x-agro.filter-select>
        <x-agro.filter-select :label="__('Estado de cobro')" wire:model.live="filterPaymentStatus" :placeholder="__('Todos')">
            <flux:select.option value="unpaid">{{ __('Pendiente') }}</flux:select.option>
            <flux:select.option value="partial">{{ __('Parcial') }}</flux:select.option>
            <flux:select.option value="paid">{{ __('Cobrada') }}</flux:select.option>
        </x-agro.filter-select>
        <x-agro.filter-select :label="__('Estado de entrega')" wire:model.live="filterDeliveryStatus" :placeholder="__('Todos')">
            <flux:select.option value="pending">{{ __('Pendiente') }}</flux:select.option>
            <flux:select.option value="delivered">{{ __('Entregada') }}</flux:select.option>
            <flux:select.option value="cancelled">{{ __('Cancelada') }}</flux:select.option>
        </x-agro.filter-select>
        <div class="flex items-center justify-between py-2 px-3 bg-pink-50 rounded-xl border border-pink-100">
            <div class="flex items-center gap-2">
                <flux:icon icon="gift" class="size-4 text-pink-500" />
                <span class="text-sm font-medium text-pink-700">{{ __('Solo facturas regalo') }}</span>
            </div>
            <flux:checkbox wire:model.live="filterGift" />
        </div>
    </x-agro.filter-modal>

</div>
