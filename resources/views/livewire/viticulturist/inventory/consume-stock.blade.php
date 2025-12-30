<div class="space-y-6 animate-fade-in">
    <!-- Header -->
    @php
        $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>';
    @endphp
    <x-page-header
        :icon="$icon"
        title="Registrar Consumo Manual"
        description="Registra consumo de stock sin tratamiento asociado"
        icon-color="from-orange-500 to-orange-600"
    >
        <x-slot:actionButton>
            <a href="{{ route('viticulturist.inventory.index') }}">
                <button class="px-4 py-2 rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300 transition-colors">
                    Cancelar
                </button>
            </a>
        </x-slot:actionButton>
    </x-page-header>

    <!-- Información del Stock -->
    <div class="glass-card rounded-xl p-6 border border-gray-200">
        <div class="flex items-start justify-between mb-6">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">{{ $stock->product->name }}</h3>
                @if($stock->product->active_ingredient)
                    <p class="text-sm text-gray-600">{{ $stock->product->active_ingredient }}</p>
                @endif
                @if($stock->batch_number)
                    <p class="text-sm text-gray-500 mt-1">Lote: {{ $stock->batch_number }}</p>
                @endif
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-600">Stock Disponible</p>
                <p class="text-2xl font-bold text-[var(--color-agro-green)]">
                    {{ number_format($availableQuantity, 3) }} {{ $stock->unit }}
                </p>
            </div>
        </div>

        <!-- Formulario -->
        <form wire:submit="consume">
            <div class="space-y-6">
                <!-- Cantidad a Consumir -->
                <div>
                    <x-label for="quantity" class="required">Cantidad a Consumir</x-label>
                    <div class="flex gap-2">
                        <x-input 
                            wire:model="quantity" 
                            type="number" 
                            step="0.001" 
                            id="quantity" 
                            class="flex-1"
                            max="{{ $availableQuantity }}"
                            required 
                        />
                        <span class="px-3 py-2 bg-gray-100 rounded-lg text-gray-700 font-medium">{{ $stock->unit }}</span>
                    </div>
                    <p class="mt-1 text-sm text-gray-500">Máximo disponible: {{ number_format($availableQuantity, 3) }} {{ $stock->unit }}</p>
                    <x-input-error for="quantity" />
                </div>

                <!-- Motivo -->
                <div>
                    <x-label for="reason" class="required">Motivo del Consumo</x-label>
                    <x-select wire:model.live="reason" id="reason" required>
                        <option value="loss">Pérdida/Derrame</option>
                        <option value="expired">Producto Caducado</option>
                        <option value="donation">Donación</option>
                        <option value="adjustment">Ajuste de Inventario</option>
                        <option value="other">Otro (especificar)</option>
                    </x-select>
                    <x-input-error for="reason" />
                </div>

                <!-- Notas -->
                <div>
                    <x-label for="notes" :class="$reason === 'other' ? 'required' : ''">
                        {{ $reason === 'other' ? 'Especifica el motivo' : 'Notas adicionales (opcional)' }}
                    </x-label>
                    <textarea 
                        wire:model="notes" 
                        id="notes" 
                        rows="3"
                        class="w-full rounded-lg border-gray-300 focus:border-orange-500 focus:ring focus:ring-orange-500 focus:ring-opacity-50"
                        placeholder="{{ $reason === 'other' ? 'Describe el motivo del consumo...' : 'Añade información adicional si es necesario...' }}"
                        @if($reason === 'other') required @endif
                    ></textarea>
                    <x-input-error for="notes" />
                </div>

                <!-- Advertencia -->
                @if($quantity > 0)
                    <div class="p-4 bg-orange-50 border border-orange-200 rounded-lg">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-orange-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-orange-900">Confirma la operación</p>
                                <p class="text-sm text-orange-700 mt-1">
                                    Se descontarán <strong>{{ number_format($quantity, 3) }} {{ $stock->unit }}</strong> del stock.
                                    Stock restante: <strong>{{ number_format($availableQuantity - $quantity, 3) }} {{ $stock->unit }}</strong>
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Botones -->
                <div class="flex items-center justify-end gap-4 pt-4 border-t">
                    <a href="{{ route('viticulturist.inventory.index') }}" 
                       class="px-6 py-2 rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300 transition-colors">
                        Cancelar
                    </a>
                    <button 
                        type="submit"
                        class="px-6 py-2 rounded-lg bg-gradient-to-r from-orange-500 to-orange-600 text-white hover:from-orange-600 hover:to-orange-700 transition-all shadow-lg hover:shadow-xl font-semibold"
                        @if($quantity <= 0 || $quantity > $availableQuantity) disabled @endif
                    >
                        Registrar Consumo
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
