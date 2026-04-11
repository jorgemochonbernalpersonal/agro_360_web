<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Registrar Consumo Manual"
        description="Registra consumo de stock sin tratamiento asociado"
    >
        <x-slot:actions>
            <flux:button href="{{ roleRoute('viticulturist.warehouse.index', ['tab' => 'fitosanitarios']) }}" variant="ghost" icon="arrow-left">
                Cancelar
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    <x-agro.card>
        {{-- Info del stock --}}
        <div class="flex items-start justify-between mb-6 pb-6 border-b border-zinc-100">
            <div>
                <h3 class="text-lg font-semibold text-zinc-900">{{ $stock->product->name }}</h3>
                @if($stock->product->active_ingredient)
                    <p class="text-sm text-zinc-600 mt-0.5">{{ $stock->product->active_ingredient }}</p>
                @endif
                @if($stock->batch_number)
                    <p class="text-sm text-zinc-500 mt-1">Lote: {{ $stock->batch_number }}</p>
                @endif
            </div>
            <div class="text-right">
                <p class="text-xs text-zinc-500 uppercase tracking-wide font-medium">
                    {{ $reason === 'expired' ? 'Cantidad Caducada' : 'Stock Disponible' }}
                </p>
                <p class="text-2xl font-bold {{ $reason === 'expired' ? 'text-red-600' : 'text-agro-600' }} mt-1">
                    {{ number_format($maxQuantity, 3) }} {{ $stock->unit }}
                </p>
            </div>
        </div>

        <form wire:submit="consume" class="space-y-6">
            <flux:field>
                <flux:label required>Cantidad a Consumir</flux:label>
                <div class="flex gap-2">
                    <flux:input
                        wire:model="quantity"
                        type="number"
                        step="0.001"
                        id="quantity"
                        max="{{ $maxQuantity }}"
                        required
                    />
                    <span class="px-3 py-2 bg-zinc-100 rounded-lg text-zinc-700 font-medium text-sm self-center">{{ $stock->unit }}</span>
                </div>
                <flux:description>Máximo: {{ number_format($maxQuantity, 3) }} {{ $stock->unit }}</flux:description>
                <flux:error name="quantity" />
            </flux:field>

            <flux:field>
                <flux:label required>Motivo del Consumo</flux:label>
                <flux:select wire:model.live="reason" id="reason" required>
                    <option value="loss">Pérdida/Derrame</option>
                    <option value="expired">Producto Caducado</option>
                    <option value="donation">Donación</option>
                    <option value="adjustment">Ajuste de Inventario</option>
                    <option value="other">Otro (especificar)</option>
                </flux:select>
                <flux:error name="reason" />
            </flux:field>

            <flux:field>
                <flux:label :required="$reason === 'other'">
                    {{ $reason === 'other' ? 'Especifica el motivo' : 'Notas adicionales (opcional)' }}
                </flux:label>
                <flux:textarea
                    wire:model="notes"
                    id="notes"
                    rows="3"
                    :placeholder="$reason === 'other' ? 'Describe el motivo del consumo...' : 'Añade información adicional si es necesario...'"
                    :required="$reason === 'other'"
                />
                <flux:error name="notes" />
            </flux:field>

            @if($quantity > 0)
                <div class="p-4 bg-orange-50 border border-orange-200 rounded-xl">
                    <div class="flex items-start gap-3">
                        <flux:icon icon="exclamation-triangle" class="text-orange-600 mt-0.5 flex-shrink-0" />
                        <div>
                            <p class="text-sm font-semibold text-orange-900">Confirma la operación</p>
                            <p class="text-sm text-orange-700 mt-1">
                                Se darán de baja <strong>{{ number_format($quantity, 3) }} {{ $stock->unit }}</strong>.
                                Stock resultante: <strong>{{ number_format(max(0, $maxQuantity - $quantity), 3) }} {{ $stock->unit }}</strong>
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-zinc-100">
                <flux:button href="{{ roleRoute('viticulturist.warehouse.index', ['tab' => 'fitosanitarios']) }}" variant="ghost">Cancelar</flux:button>
                <flux:button
                    type="submit"
                    variant="primary"
                    :disabled="$quantity <= 0 || $quantity > $maxQuantity"
                >
                    Registrar Consumo
                </flux:button>
            </div>
        </form>
    </x-agro.card>
</div>
