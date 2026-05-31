<div class="space-y-6 animate-fade-in">
    <x-agro.page-header :title="__('Liquidaciones de Vendimia')" :description="__('Liquidaciones emitidas por tu bodega por la uva entregada')" />

    {{-- Resumen KPIs --}}
    @if ($stats['total_amount'] > 0)
        <x-agro.stats-section key="grape-purchase-invoices" columns="3">
            <x-agro.stat-card
                :label="__('Total liquidado')"
                :value="number_format($stats['total_amount'], 2) . ' €'"
                icon="banknotes"
                color="green"
            />
            <x-agro.stat-card
                :label="__('Cobrado')"
                :value="number_format($stats['paid_amount'], 2) . ' €'"
                icon="check-circle"
                color="blue"
            />
            <x-agro.stat-card
                :label="__('Pendiente de pago')"
                :value="$stats['pending_count'] . ' ' . ($stats['pending_count'] === 1 ? __('liquidación') : __('liquidaciones'))"
                icon="clock"
                color="orange"
            />
        </x-agro.stats-section>
    @endif

    <x-agro.filter-bar>
        <x-agro.filter-input
            wire:model.live.debounce.300ms="search"
            :placeholder="__('Buscar por nº, ref. o bodega...')"
        />
        @if ($wineries->count() > 1)
            <flux:select wire:model.live="wineryFilter" size="sm" class="w-48">
                <flux:select.option value="">{{ __('Todas las bodegas') }}</flux:select.option>
                @foreach ($wineries as $w)
                    <flux:select.option value="{{ $w->id }}">{{ $w->name }}</flux:select.option>
                @endforeach
            </flux:select>
        @endif
        <flux:select wire:model.live="paymentFilter" size="sm" class="w-44">
            <flux:select.option value="">{{ __('Todos los pagos') }}</flux:select.option>
            <flux:select.option value="unpaid">{{ __('Pendiente') }}</flux:select.option>
            <flux:select.option value="paid">{{ __('Cobrada') }}</flux:select.option>
        </flux:select>
        @if ($search || $wineryFilter || $paymentFilter)
            <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">
                {{ __('Limpiar') }}
            </flux:button>
        @endif
    </x-agro.filter-bar>

    @if ($invoices->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach ($invoices as $invoice)
                @php $delay = min($loop->index * 50, 300); @endphp
                <x-agro.card
                    class="animate-fade-in-up flex flex-col {{ $invoice->status === 'cancelled' ? 'opacity-60' : '' }}"
                    style="animation-delay: {{ $delay }}ms;"
                    wire:key="gp-invoice-{{ $invoice->id }}"
                >
                    <x-slot:header>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-agro-50 flex items-center justify-center shrink-0">
                                <flux:icon icon="document-text" class="size-5 text-agro-600" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-mono font-bold text-zinc-900 truncate dark:text-zinc-100">
                                    {{ $invoice->invoice_number ?? '—' }}
                                </h3>
                                @if ($invoice->delivery_note_code)
                                    <p class="text-xs text-zinc-400 font-mono truncate">{{ $invoice->delivery_note_code }}</p>
                                @else
                                    <p class="text-xs text-zinc-400">
                                        {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y') }}
                                    </p>
                                @endif
                            </div>
                            @if ($invoice->status === 'cancelled')
                                <flux:badge color="red" size="sm" class="shrink-0">{{ __('Cancelada') }}</flux:badge>
                            @elseif ($invoice->payment_status === 'paid')
                                <flux:badge color="green" size="sm" class="shrink-0">{{ __('Cobrada') }}</flux:badge>
                            @else
                                <flux:badge color="orange" size="sm" class="shrink-0">{{ __('Pendiente') }}</flux:badge>
                            @endif
                        </div>
                    </x-slot:header>

                    <div class="flex-1 space-y-3 text-sm">
                        <div class="flex items-center gap-2 text-zinc-600 dark:text-zinc-300">
                            <flux:icon icon="building-office-2" class="size-4 text-zinc-400 shrink-0" />
                            <span class="truncate font-medium">{{ $invoice->user?->name ?? '—' }}</span>
                        </div>

                        <div class="flex items-center justify-between text-zinc-600 dark:text-zinc-300">
                            <span class="text-zinc-400">{{ __('Fecha') }}</span>
                            <span>{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y') }}</span>
                        </div>

                        <div class="flex items-center justify-between text-zinc-600 dark:text-zinc-300">
                            <span class="text-zinc-400">{{ __('Base') }}</span>
                            <span>{{ number_format($invoice->subtotal, 2) }} €</span>
                        </div>

                        @if ($invoice->tax_amount > 0)
                            <div class="flex items-center justify-between text-zinc-600 dark:text-zinc-300">
                                <span class="text-zinc-400">{{ __('Retención IRPF') }}</span>
                                <span class="text-red-500">-{{ number_format($invoice->tax_amount, 2) }} €</span>
                            </div>
                        @endif

                        <div class="flex items-center justify-between pt-1 border-t border-zinc-100 dark:border-zinc-700">
                            <span class="font-medium text-zinc-500">{{ __('A cobrar') }}</span>
                            <span class="text-lg font-bold text-zinc-900 dark:text-zinc-100">
                                {{ number_format($invoice->total_amount, 2) }} €
                            </span>
                        </div>
                    </div>

                    @if ($invoice->status !== 'cancelled')
                        <x-slot:footer>
                            <div class="flex items-center justify-between text-xs text-zinc-400">
                                @if ($invoice->payment_status === 'paid' && $invoice->payment_date)
                                    <span class="flex items-center gap-1">
                                        <flux:icon icon="check-circle" class="size-3 text-green-500" />
                                        {{ __('Cobrado el :date', ['date' => \Carbon\Carbon::parse($invoice->payment_date)->format('d/m/Y')]) }}
                                    </span>
                                @elseif ($invoice->payment_status === 'paid')
                                    <span class="flex items-center gap-1 text-green-600">
                                        <flux:icon icon="check-circle" class="size-3" />
                                        {{ __('Liquidación cobrada') }}
                                    </span>
                                @else
                                    <span class="flex items-center gap-1">
                                        <flux:icon icon="clock" class="size-3 text-orange-400" />
                                        {{ __('Pendiente de cobro') }}
                                    </span>
                                @endif
                            </div>
                        </x-slot:footer>
                    @endif
                </x-agro.card>
            @endforeach
        </div>

        <x-agro.pagination :paginator="$invoices" />
    @else
        <x-agro.empty-state
            icon="document-text"
            :title="__('No hay liquidaciones de vendimia')"
            :description="__('Cuando tu bodega emita una liquidación por la uva entregada, aparecerá aquí')"
        />
    @endif
</div>
