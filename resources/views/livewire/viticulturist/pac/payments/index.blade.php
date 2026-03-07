<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Historial de Ayudas PAC"
        description="Registra los pagos recibidos del organismo pagador por año y tipo de ayuda."
    >
        <x-slot:actions>
            <flux:button variant="primary" icon="plus" wire:click="openCreate">
                Registrar pago
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <x-agro.stat-card label="Pagos en {{ $filterYear }}" :value="$stats['count_year']" icon="document-text" color="zinc" />
        <x-agro.stat-card label="Total {{ $filterYear }}" :value="number_format($stats['total_year'], 2) . ' €'" icon="banknotes" color="green" />
        <x-agro.stat-card label="Total histórico" :value="number_format($stats['total_historic'], 2) . ' €'" icon="chart-bar" color="agro" />
        <x-agro.stat-card label="Tipos de ayuda" :value="$stats['by_type']->count()" icon="tag" color="blue" />
    </div>

    {{-- Filtro año --}}
    <div class="flex items-center gap-3">
        <flux:label class="text-sm font-medium">Año:</flux:label>
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
                    <span class="font-semibold text-zinc-900 text-sm">{{ $editingId ? 'Editar pago' : 'Nuevo pago' }}</span>
                </div>
            </x-slot:header>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <flux:field>
                    <flux:label>Año *</flux:label>
                    <flux:input wire:model="year" type="number" min="2000" max="{{ now()->year + 1 }}" />
                    <flux:error name="year" />
                </flux:field>
                <flux:field>
                    <flux:label>Tipo de ayuda *</flux:label>
                    <flux:select wire:model="payment_type">
                        @foreach($paymentTypes as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="payment_type" />
                </flux:field>
                <flux:field>
                    <flux:label>Importe (€) *</flux:label>
                    <flux:input wire:model="amount" type="number" step="0.01" min="0" placeholder="0.00" />
                    <flux:error name="amount" />
                </flux:field>
                <flux:field>
                    <flux:label>Fecha de cobro *</flux:label>
                    <flux:input wire:model="payment_date" type="date" />
                    <flux:error name="payment_date" />
                </flux:field>
                <flux:field>
                    <flux:label>Referencia FEGA</flux:label>
                    <flux:input wire:model="reference" type="text" placeholder="Nº referencia" />
                    <flux:error name="reference" />
                </flux:field>
                <flux:field>
                    <flux:label>Declaración asociada</flux:label>
                    <flux:select wire:model="declaration_id">
                        <option value="">Ninguna</option>
                        @foreach($declarations as $decl)
                            <option value="{{ $decl->id }}">{{ $decl->year }} — {{ $decl->statusLabel() }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="declaration_id" />
                </flux:field>
                <div class="md:col-span-3">
                    <flux:field>
                        <flux:label>Notas</flux:label>
                        <flux:input wire:model="notes" type="text" placeholder="Observaciones opcionales" />
                        <flux:error name="notes" />
                    </flux:field>
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-4 pt-4 border-t border-zinc-100">
                <flux:button variant="ghost" wire:click="cancel">Cancelar</flux:button>
                <flux:button variant="primary" icon="check" wire:click="save">Guardar</flux:button>
            </div>
        </x-agro.card>
    @endif

    {{-- Tabla --}}
    @if($payments->isEmpty())
        <x-agro.empty-state
            icon="banknotes"
            title="Sin pagos en {{ $filterYear }}"
            description="Registra los pagos recibidos del organismo pagador."
        >
            <flux:button variant="primary" icon="plus" wire:click="openCreate">
                Registrar pago
            </flux:button>
        </x-agro.empty-state>
    @else
        <x-agro.card>
            <x-agro.data-table :headers="['Fecha', 'Tipo de ayuda', 'Importe', 'Referencia', 'Declaración', '']">
                @foreach($payments as $payment)
                    <x-agro.table-row wire:key="pay-{{ $payment->id }}">
                        <x-agro.table-cell>
                            {{ $payment->payment_date->format('d/m/Y') }}
                        </x-agro.table-cell>
                        <x-agro.table-cell>
                            <span class="font-medium text-zinc-900">{{ $payment->typeLabel() }}</span>
                        </x-agro.table-cell>
                        <x-agro.table-cell>
                            <span class="font-bold text-green-700 font-mono">{{ number_format($payment->amount, 2) }} €</span>
                        </x-agro.table-cell>
                        <x-agro.table-cell>
                            <span class="font-mono text-xs text-zinc-500">{{ $payment->reference ?? '—' }}</span>
                        </x-agro.table-cell>
                        <x-agro.table-cell>
                            @if($payment->declaration)
                                <span class="text-sm text-zinc-600">PAC {{ $payment->declaration->year }}</span>
                            @else
                                <span class="text-zinc-400">—</span>
                            @endif
                        </x-agro.table-cell>
                        <x-agro.table-cell align="right">
                            <div class="flex items-center justify-end gap-1">
                                <flux:button size="sm" variant="ghost" icon="pencil"
                                    wire:click="openEdit({{ $payment->id }})" />
                                <flux:button size="sm" variant="ghost" icon="trash" class="text-red-500"
                                    wire:click="delete({{ $payment->id }})"
                                    wire:confirm="¿Eliminar este pago?" />
                            </div>
                        </x-agro.table-cell>
                    </x-agro.table-row>
                @endforeach
            </x-agro.data-table>

            {{-- Total --}}
            <div class="border-t border-zinc-200 mt-2 pt-3 px-4 pb-2 flex justify-end">
                <span class="text-sm font-medium text-zinc-500">
                    Total {{ $filterYear }}:
                    <span class="text-green-700 font-bold font-mono ml-2">{{ number_format($stats['total_year'], 2) }} €</span>
                </span>
            </div>
        </x-agro.card>

        {{-- Desglose por tipo --}}
        @if($stats['by_type']->count() > 1)
            <x-agro.card>
                <x-slot:header>
                    <div class="flex items-center gap-2">
                        <flux:icon icon="chart-pie" class="size-4 text-agro-600" />
                        <span class="font-semibold text-zinc-900 text-sm">Desglose por tipo — {{ $filterYear }}</span>
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
    @endif

</div>
