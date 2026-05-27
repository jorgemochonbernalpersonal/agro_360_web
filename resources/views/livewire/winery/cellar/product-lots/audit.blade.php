<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="{{ __('Auditoría de stock') }}"
        :description="__('Verificación y reconstrucción de stock por movimientos registrados')"
    >
        <x-slot:actions>
            <flux:button :href="roleRoute('product-lots.index')" icon="arrow-left" variant="ghost" wire:navigate>{{ __('Volver a productos') }}</flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- KPIs --}}
    <div class="grid grid-cols-3 gap-4">
        <x-agro.stat-card
            :label="__('Total lotes')"
            :value="$stats['total']"
            icon="archive-box"
            color="zinc"
        />
        <x-agro.stat-card
            :label="__('Sin discrepancias')"
            :value="$stats['ok']"
            icon="check-circle"
            color="green"
        />
        <x-agro.stat-card
            :label="__('Con discrepancias')"
            :value="$stats['drifted']"
            icon="exclamation-triangle"
            color="{{ $stats['drifted'] > 0 ? 'red' : 'green' }}"
        />
    </div>

    {{-- Toolbar --}}
    <div class="flex items-center justify-between gap-3">
        <label class="flex items-center gap-2 text-sm text-zinc-600 cursor-pointer select-none">
            <input wire:model.live="onlyDrifted" type="checkbox"
                class="rounded border-zinc-300 text-red-500 focus:ring-red-400" />
            Solo lotes con discrepancias
        </label>

        @if($stats['drifted'] > 0)
            <flux:button
                wire:click="recalculateAll"
                wire:loading.attr="disabled"
                wire:confirm="{{ __('¿Recalcular los :count lotes con discrepancias? Se sobreescribirán sus cantidades actuales con los valores reconstruidos desde los movimientos.', ['count' => $stats['drifted']]) }}"
                variant="danger"
                icon="arrow-path"
                size="sm"
            >
                Recalcular todos ({{ $stats['drifted'] }})
            </flux:button>
        @endif
    </div>

    {{-- Tabla --}}
    @if($rows->isEmpty())
        <x-agro.empty-state
            icon="check-circle"
            title="{{ __('Todo en orden') }}"
            :description="__('No hay discrepancias de stock en ningún lote.')"
        />
    @else
        <x-agro.card>
            <x-agro.data-table>
                <x-slot:head>
                    <x-agro.table-cell header>{{ __('Producto') }}</x-agro.table-cell>
                    <x-agro.table-cell header align="center">{{ __('Movimientos') }}</x-agro.table-cell>
                    <x-agro.table-cell header align="right">{{ __('Stock actual') }}</x-agro.table-cell>
                    <x-agro.table-cell header align="right">{{ __('Esperado (movimientos)') }}</x-agro.table-cell>
                    <x-agro.table-cell header align="right">{{ __('Disp. actual / esp.') }}</x-agro.table-cell>
                    <x-agro.table-cell header align="right">{{ __('Res. actual / esp.') }}</x-agro.table-cell>
                    <x-agro.table-cell header align="right">{{ __('Vend. actual / esp.') }}</x-agro.table-cell>
                    <x-agro.table-cell header align="center">{{ __('Estado') }}</x-agro.table-cell>
                    <x-agro.table-cell header align="center">{{ __('Acción') }}</x-agro.table-cell>
                </x-slot:head>

                @foreach($rows as $row)
                    @php
                        $lot      = $row['lot'];
                        $hasDrift = $row['hasDrift'];
                        $rowBg    = $hasDrift ? 'bg-red-50' : '';
                    @endphp
                    <x-agro.table-row class="{{ $rowBg }}">

                        {{-- Nombre --}}
                        <x-agro.table-cell>
                            <div class="font-medium text-zinc-900">{{ $lot->name }}</div>
                            @if($lot->vintage)
                                <div class="text-xs text-zinc-400">{{ $lot->vintage }}</div>
                            @endif
                        </x-agro.table-cell>

                        {{-- Movimientos --}}
                        <x-agro.table-cell align="center">
                            <span class="text-zinc-500 text-sm">{{ $row['movCount'] }}</span>
                        </x-agro.table-cell>

                        {{-- Total actual vs esperado --}}
                        <x-agro.table-cell align="right">
                            <span class="font-semibold text-zinc-800">
                                {{ number_format((float) $lot->quantity, 3) }}
                            </span>
                        </x-agro.table-cell>
                        <x-agro.table-cell align="right">
                            <span class="font-semibold {{ abs($row['driftTotal']) >= 0.001 ? 'text-red-600' : 'text-zinc-800' }}">
                                {{ number_format($row['expTotal'], 3) }}
                                @if(abs($row['driftTotal']) >= 0.001)
                                    <span class="text-xs">({{ $row['driftTotal'] > 0 ? '+' : '' }}{{ number_format($row['driftTotal'], 3) }})</span>
                                @endif
                            </span>
                        </x-agro.table-cell>

                        {{-- Disp --}}
                        <x-agro.table-cell align="right">
                            @php $da = abs($row['driftAvail']) >= 0.001; @endphp
                            <span class="text-sm {{ $da ? 'text-red-600 font-semibold' : 'text-zinc-600' }}">
                                {{ number_format((float) $lot->available_quantity, 3) }}
                                @if($da) / {{ number_format($row['expAvail'], 3) }} @endif
                            </span>
                        </x-agro.table-cell>

                        {{-- Res --}}
                        <x-agro.table-cell align="right">
                            @php $dr = abs($row['driftRes']) >= 0.001; @endphp
                            <span class="text-sm {{ $dr ? 'text-red-600 font-semibold' : 'text-zinc-600' }}">
                                {{ number_format((float) $lot->reserved_quantity, 3) }}
                                @if($dr) / {{ number_format($row['expRes'], 3) }} @endif
                            </span>
                        </x-agro.table-cell>

                        {{-- Sold --}}
                        <x-agro.table-cell align="right">
                            @php $ds = abs($row['driftSold']) >= 0.001; @endphp
                            <span class="text-sm {{ $ds ? 'text-red-600 font-semibold' : 'text-zinc-600' }}">
                                {{ number_format((float) $lot->sold_quantity, 3) }}
                                @if($ds) / {{ number_format($row['expSold'], 3) }} @endif
                            </span>
                        </x-agro.table-cell>

                        {{-- Estado --}}
                        <x-agro.table-cell align="center">
                            @if($hasDrift)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">
                                    <flux:icon icon="exclamation-triangle" class="size-3" />
                                    Discrepancia
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                    <flux:icon icon="check-circle" class="size-3" />
                                    OK
                                </span>
                            @endif
                        </x-agro.table-cell>

                        {{-- Acción --}}
                        <x-agro.table-cell align="center">
                            <div class="flex items-center justify-center gap-1">
                                @if($hasDrift)
                                    <flux:button
                                        wire:click="recalculate({{ $lot->id }})"
                                        wire:loading.attr="disabled"
                                        wire:confirm="{{ __('¿Recalcular stock de «:name»? Se sobreescribirán las cantidades actuales con los valores reconstruidos desde los movimientos.', ['name' => $lot->name]) }}"
                                        variant="ghost"
                                        icon="arrow-path"
                                        size="xs"
                                    >
                                        Recalcular
                                    </flux:button>
                                @endif
                                <flux:button
                                    wire:click="openAdjustModal({{ $lot->id }})"
                                    variant="ghost"
                                    icon="pencil-square"
                                    size="xs"
                                    title="{{ __('Ajuste manual') }}"
                                />
                            </div>
                        </x-agro.table-cell>

                    </x-agro.table-row>
                @endforeach
            </x-agro.data-table>
        </x-agro.card>
    @endif

    {{-- Nota informativa --}}
    <flux:callout variant="info" icon="information-circle">
        <p class="text-sm">
            La columna <strong>{{ __('Esperado') }}</strong> replica todos los movimientos registrados desde el stock inicial del lote.
            Si hay discrepancia, significa que alguna cantidad fue modificada manualmente o que ocurrió un error en una transacción.
            <strong>{{ __('Recalcular') }}</strong> sobreescribe los valores con los reconstruidos desde movimientos.
            <strong>{{ __('Ajuste manual') }}</strong> permite corregir directamente con justificación auditada.
        </p>
    </flux:callout>

    {{-- Modal ajuste manual --}}
    <x-agro.modal name="manual-adjustment" max-width="lg">
        <div class="p-6 space-y-5">
            <div>
                <flux:heading size="lg">{{ __('Ajuste manual de stock') }}</flux:heading>
                <flux:subheading>{{ $adjustingLotName }}</flux:subheading>
            </div>

            <flux:callout variant="warning" icon="exclamation-triangle">
                <flux:callout.text>
                    Este ajuste sobreescribe los valores directamente. Queda registrado en las notas del lote.
                    Úsalo solo si el recálculo automático no refleja la realidad.
                </flux:callout.text>
            </flux:callout>

            <div class="grid grid-cols-3 gap-4">
                <flux:field>
                    <flux:label>{{ __('Disponible') }}</flux:label>
                    <flux:input wire:model="manualAvailable" type="number" step="0.001" min="0" />
                    <flux:error name="manualAvailable" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Reservado') }}</flux:label>
                    <flux:input wire:model="manualReserved" type="number" step="0.001" min="0" />
                    <flux:error name="manualReserved" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Vendido') }}</flux:label>
                    <flux:input wire:model="manualSold" type="number" step="0.001" min="0" />
                    <flux:error name="manualSold" />
                </flux:field>
            </div>

            <flux:field>
                <flux:label>{{ __('Justificación') }} <span class="text-red-500">*</span></flux:label>
                <flux:textarea wire:model="adjustmentNote" rows="3"
                    placeholder="{{ __('Explica el motivo del ajuste (error en transacción, corrección de inventario físico...)') }}" />
                <flux:error name="adjustmentNote" />
            </flux:field>

            <div class="flex justify-end gap-3 pt-2">
                <flux:button x-on:click="$dispatch('close-modal', { name: 'manual-adjustment' })" variant="ghost">{{ __('Cancelar') }}</flux:button>
                <flux:button wire:click="saveManualAdjustment" wire:loading.attr="disabled" variant="primary" icon="check">{{ __('Guardar ajuste') }}</flux:button>
            </div>
        </div>
    </x-agro.modal>

</div>
