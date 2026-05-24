<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        :title="__('Historial de Ayudas PAC')"
        :description="__('Registra los pagos recibidos del organismo pagador por año y tipo de ayuda.')"
    >
        <x-slot:actions>
            <flux:button variant="primary" icon="plus" wire:click="openCreate">
                {{ __('Registrar pago') }}
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Stats --}}
    <div class="grid grid-cols-2 gap-4">
        <x-agro.stat-card :label="__('Pagos en') . ' ' . $filterYear" :value="$stats['count_year']" icon="document-text" color="zinc" />
        <x-agro.stat-card :label="__('Total') . ' ' . $filterYear" :value="number_format($stats['total_year'], 2) . ' €'" icon="banknotes" color="green" />
        <x-agro.stat-card :label="__('Total histórico')" :value="number_format($stats['total_historic'], 2) . ' €'" icon="chart-bar" color="agro" />
        <x-agro.stat-card :label="__('Tipos de ayuda')" :value="$stats['by_type']->count()" icon="tag" color="blue" />
    </div>

    {{-- Filtro año --}}
    <div class="flex items-center gap-3">
        <flux:label class="text-sm font-medium">{{ __('Año') }}:</flux:label>
        <flux:select wire:model.live="yearFilter" class="w-32">
            @foreach($availableYears as $y)
                <option value="{{ $y }}">{{ $y }}</option>
            @endforeach
        </flux:select>
    </div>

    {{-- Formulario inline --}}
    @if($showForm)
        <x-agro.card>
            <x-slot:header>
                <div class="flex items-center gap-2">
                    <flux:icon icon="{{ $editingId ? 'pencil' : 'plus' }}" class="size-4 text-agro-600" />
                    <span class="font-semibold text-zinc-900 text-sm">{{ $editingId ? __('Editar pago') : __('Nuevo pago') }}</span>
                </div>
            </x-slot:header>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <flux:field>
                    <flux:label>{{ __('Año') }} *</flux:label>
                    <flux:input wire:model="year" type="number" min="2000" max="{{ now()->year + 1 }}" />
                    <flux:error name="year" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Tipo de ayuda') }} *</flux:label>
                    <flux:select wire:model="payment_type">
                        @foreach($paymentTypes as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="payment_type" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Importe (€)') }} *</flux:label>
                    <flux:input wire:model="amount" type="number" step="0.01" min="0" placeholder="0.00" />
                    <flux:error name="amount" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Fecha de cobro') }} *</flux:label>
                    <flux:input wire:model="payment_date" type="date" />
                    <flux:error name="payment_date" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Referencia FEGA') }}</flux:label>
                    <flux:input wire:model="reference" type="text" :placeholder="__('Nº referencia')" />
                    <flux:error name="reference" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Declaración asociada') }}</flux:label>
                    <flux:select wire:model="declaration_id">
                        <option value="">{{ __('Ninguna') }}</option>
                        @foreach($declarations as $decl)
                            <option value="{{ $decl->id }}">{{ $decl->year }} — {{ $decl->statusLabel() }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="declaration_id" />
                </flux:field>
                <div class="md:col-span-3">
                    <flux:field>
                        <flux:label>{{ __('Notas') }}</flux:label>
                        <flux:input wire:model="notes" type="text" :placeholder="__('Observaciones opcionales')" />
                        <flux:error name="notes" />
                    </flux:field>
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-4 pt-4 border-t border-zinc-100">
                <flux:button variant="ghost" wire:click="cancel">{{ __('Cancelar') }}</flux:button>
                <flux:button variant="primary" icon="check" wire:click="save">{{ __('Guardar') }}</flux:button>
            </div>
        </x-agro.card>
    @endif

    {{-- Cards --}}
    <x-agro.loading-grid target="yearFilter" />
    <div wire:loading.remove wire:target="yearFilter">
        @if($payments->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($payments as $payment)
                    @php $delay = min($loop->index * 50, 300); @endphp
                    <x-agro.card
                        class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                        style="animation-delay: {{ $delay }}ms;"
                        wire:key="pay-{{ $payment->id }}"
                    >
                        <x-slot:header>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center shrink-0">
                                    <flux:icon icon="banknotes" class="size-5 text-green-600" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-zinc-900 truncate">{{ $payment->typeLabel() }}</h3>
                                    <p class="text-xs text-zinc-500">{{ $payment->payment_date->format('d/m/Y') }}</p>
                                </div>
                            </div>
                        </x-slot:header>

                        <div class="flex-1 space-y-4">
                            <div class="bg-agro-50 rounded-xl p-3 text-center">
                                <p class="text-[10px] font-semibold text-agro-400 uppercase tracking-widest mb-0.5">{{ __('Importe') }}</p>
                                <p class="text-2xl font-bold text-green-700 leading-none font-mono">{{ number_format($payment->amount, 2) }} €</p>
                            </div>

                            <div class="space-y-2 text-sm">
                                <div class="flex items-center justify-between">
                                    <span class="text-zinc-400">{{ __('Referencia') }}</span>
                                    <span class="text-zinc-700 font-medium font-mono text-xs">{{ $payment->reference ?? '—' }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-zinc-400">{{ __('Declaración') }}</span>
                                    <span class="text-zinc-700 font-medium">
                                        @if($payment->declaration)
                                            PAC {{ $payment->declaration->year }}
                                        @else
                                            —
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>

                        <x-slot:footer>
                            <div class="flex items-center justify-end gap-0.5">
                                <x-agro.action-button
                                    icon="pencil"
                                    variant="edit"
                                    wire:click="openEdit({{ $payment->id }})"
                                    :title="__('Editar')"
                                />
                                <x-agro.action-button
                                    variant="delete"
                                    wire:click="delete({{ $payment->id }})"
                                    wire:confirm="{{ __('¿Eliminar este pago?') }}"
                                    :title="__('Eliminar')"
                                />
                            </div>
                        </x-slot:footer>
                    </x-agro.card>
                @endforeach
            </div>

            {{-- Total --}}
            <div class="mt-4 flex justify-end">
                <span class="text-sm font-medium text-zinc-500">
                    {{ __('Total') }} {{ $filterYear }}:
                    <span class="text-green-700 font-bold font-mono ml-2">{{ number_format($stats['total_year'], 2) }} €</span>
                </span>
            </div>

            {{-- Desglose por tipo --}}
            @if($stats['by_type']->count() > 1)
                <x-agro.card>
                    <x-slot:header>
                        <div class="flex items-center gap-2">
                            <flux:icon icon="chart-pie" class="size-4 text-agro-600" />
                            <span class="font-semibold text-zinc-900 text-sm">{{ __('Desglose por tipo') }} — {{ $filterYear }}</span>
                        </div>
                    </x-slot:header>
                    <div class="space-y-2">
                        @foreach($stats['by_type'] as $type => $total)
                            @php
                                $pct = $stats['total_year'] > 0 ? round($total / $stats['total_year'] * 100) : 0;
                            @endphp
                            <div>
                                <div class="flex justify-between text-xs mb-1">
                                    <span class="text-zinc-700">{{ $paymentTypes[$type] ?? $type }}</span>
                                    <span class="font-medium text-zinc-900">{{ number_format($total, 2) }} € ({{ $pct }}%)</span>
                                </div>
                                <div class="w-full bg-zinc-100 rounded-full h-1.5">
                                    <div class="bg-agro-500 h-1.5 rounded-full" style="width: {{ $pct }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-agro.card>
            @endif
        @else
            <x-agro.empty-state
                icon="banknotes"
                :title="__('Sin pagos en') . ' ' . $filterYear"
                :description="__('Registra los pagos recibidos del organismo pagador.')"
            >
                <flux:button variant="primary" icon="plus" wire:click="openCreate">
                    {{ __('Registrar pago') }}
                </flux:button>
            </x-agro.empty-state>
        @endif
    </div>

</div>
