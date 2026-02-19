<div class="space-y-6 animate-fade-in">
    @php
        $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>';
    @endphp
    
    <x-agro.page-header
        :icon="$icon"
        title="Configuración de Numeración"
        description="Personaliza cómo se generan los números de facturas y albaranes"
        icon-color="from-agro-500 to-agro-700"
    />

    <form wire:submit="save" class="space-y-6">
        {{-- Facturas --}}
        <div class="bg-white rounded-xl border border-zinc-200 shadow-sm p-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-blue-100 to-blue-200 flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-zinc-900">?? Facturas</h3>
                    <p class="text-sm text-zinc-500">Configuración de numeración de facturas</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Prefijo --}}
                <div>
                    <label class="block text-sm font-medium text-zinc-700 mb-2">
                        Prefijo
                    </label>
                    <input 
                        type="text" 
                        wire:model.live="invoice_prefix"
                        class="w-full px-4 py-2 border border-zinc-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="FAC-{YEAR}-"
                    >
                    <p class="mt-1 text-xs text-zinc-500">
                        Variables: {YEAR}, {MONTH}, {DAY}
                    </p>
                    @error('invoice_prefix') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                {{-- Dígitos --}}
                <div>
                    <label class="block text-sm font-medium text-zinc-700 mb-2">
                        Número de Dígitos
                    </label>
                    <flux:select wire:model.live="invoice_padding">
                        <option value="2">2 dígitos (01, 02, ...)</option>
                        <option value="3">3 dígitos (001, 002, ...)</option>
                        <option value="4">4 dígitos (0001, 0002, ...)</option>
                        <option value="5">5 dígitos (00001, 00002, ...)</option>
                        <option value="6">6 dígitos (000001, 000002, ...)</option>
                    </flux:select>
                    @error('invoice_padding') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                {{-- Contador Actual --}}
                <div>
                    <label class="block text-sm font-medium text-zinc-700 mb-2">
                        Contador Actual
                    </label>
                    <input 
                        type="number" 
                        wire:model.live="invoice_counter"
                        min="1"
                        class="w-full px-4 py-2 border border-zinc-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                    <p class="mt-1 text-xs text-zinc-500">
                        Próxima factura usará este número
                    </p>
                    @error('invoice_counter') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                {{-- Reseteo Anual --}}
                <div>
                    <label class="block text-sm font-medium text-zinc-700 mb-2">
                        Reseteo Automático
                    </label>
                    <label class="flex items-center gap-3 p-3 border border-zinc-300 rounded-lg cursor-pointer hover:bg-zinc-50">
                        <input 
                            type="checkbox" 
                            wire:model="invoice_year_reset"
                            class="w-5 h-5 text-blue-600 rounded focus:ring-2 focus:ring-blue-500"
                        >
                        <div class="flex-1">
                            <p class="text-sm font-medium text-zinc-900">Resetear cada año</p>
                            <p class="text-xs text-zinc-500">El contador vuelve a 1 el 1 de enero</p>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Vista Previa --}}
            <div class="mt-6 p-4 bg-blue-50 border-2 border-blue-200 rounded-lg">
                <p class="text-sm font-medium text-blue-800 mb-2">Vista Previa:</p>
                <p class="text-3xl font-bold text-blue-900 font-mono">{{ $invoicePreview }}</p>
                <p class="mt-2 text-xs text-blue-600">Próxima factura que se generará</p>
            </div>

            {{-- Botón Resetear --}}
            <div class="mt-4 flex justify-end">
                <button 
                    type="button"
                    wire:click="resetInvoiceCounter"
                    wire:confirm="¿Estás seguro de resetear el contador de facturas a 1?"
                    class="px-4 py-2 bg-red-50 text-red-700 border border-red-200 rounded-lg hover:bg-red-100 transition-colors text-sm font-medium"
                >
                    ?? Resetear Contador
                </button>
            </div>
        </div>

        {{-- Albaranes --}}
        <div class="bg-white rounded-xl border border-zinc-200 shadow-sm p-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-green-100 to-green-200 flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-zinc-900">?? Albaranes</h3>
                    <p class="text-sm text-zinc-500">Configuración de numeración de albaranes</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Prefijo --}}
                <div>
                    <label class="block text-sm font-medium text-zinc-700 mb-2">
                        Prefijo
                    </label>
                    <input 
                        type="text" 
                        wire:model.live="delivery_note_prefix"
                        class="w-full px-4 py-2 border border-zinc-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                        placeholder="ALB-{YEAR}-"
                    >
                    <p class="mt-1 text-xs text-zinc-500">
                        Variables: {YEAR}, {MONTH}, {DAY}
                    </p>
                    @error('delivery_note_prefix') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                {{-- Dígitos --}}
                <div>
                    <label class="block text-sm font-medium text-zinc-700 mb-2">
                        Número de Dígitos
                    </label>
                    <flux:select wire:model.live="delivery_note_padding">
                        <option value="2">2 dígitos (01, 02, ...)</option>
                        <option value="3">3 dígitos (001, 002, ...)</option>
                        <option value="4">4 dígitos (0001, 0002, ...)</option>
                        <option value="5">5 dígitos (00001, 00002, ...)</option>
                        <option value="6">6 dígitos (000001, 000002, ...)</option>
                    </flux:select>
                    @error('delivery_note_padding') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                {{-- Contador Actual --}}
                <div>
                    <label class="block text-sm font-medium text-zinc-700 mb-2">
                        Contador Actual
                    </label>
                    <input 
                        type="number" 
                        wire:model.live="delivery_note_counter"
                        min="1"
                        class="w-full px-4 py-2 border border-zinc-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                    >
                    <p class="mt-1 text-xs text-zinc-500">
                        Próximo albarán usará este número
                    </p>
                    @error('delivery_note_counter') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                {{-- Reseteo Anual --}}
                <div>
                    <label class="block text-sm font-medium text-zinc-700 mb-2">
                        Reseteo Automático
                    </label>
                    <label class="flex items-center gap-3 p-3 border border-zinc-300 rounded-lg cursor-pointer hover:bg-zinc-50">
                        <input 
                            type="checkbox" 
                            wire:model="delivery_note_year_reset"
                            class="w-5 h-5 text-green-600 rounded focus:ring-2 focus:ring-green-500"
                        >
                        <div class="flex-1">
                            <p class="text-sm font-medium text-zinc-900">Resetear cada año</p>
                            <p class="text-xs text-zinc-500">El contador vuelve a 1 el 1 de enero</p>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Vista Previa --}}
            <div class="mt-6 p-4 bg-green-50 border-2 border-green-200 rounded-lg">
                <p class="text-sm font-medium text-green-800 mb-2">Vista Previa:</p>
                <p class="text-3xl font-bold text-green-900 font-mono">{{ $deliveryNotePreview }}</p>
                <p class="mt-2 text-xs text-green-600">Próximo albarán que se generará</p>
            </div>

            {{-- Botón Resetear --}}
            <div class="mt-4 flex justify-end">
                <button 
                    type="button"
                    wire:click="resetDeliveryNoteCounter"
                    wire:confirm="¿Estás seguro de resetear el contador de albaranes a 1?"
                    class="px-4 py-2 bg-red-50 text-red-700 border border-red-200 rounded-lg hover:bg-red-100 transition-colors text-sm font-medium"
                >
                    ?? Resetear Contador
                </button>
            </div>
        </div>

        {{-- Información Adicional --}}
        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <div class="flex-1">
                    <h4 class="text-sm font-semibold text-blue-900 mb-1">
                        ?? Variables disponibles
                    </h4>
                    <ul class="text-xs text-blue-800 space-y-1">
                        <li>• <code class="bg-blue-100 px-1 rounded">{YEAR}</code> - Año actual (2025)</li>
                        <li>• <code class="bg-blue-100 px-1 rounded">{MONTH}</code> - Mes actual (01-12)</li>
                        <li>• <code class="bg-blue-100 px-1 rounded">{DAY}</code> - Día actual (01-31)</li>
                        <li class="mt-2">• <strong>Ejemplo:</strong> "FAC-{YEAR}-" se convierte en "FAC-2025-"</li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Botón Guardar --}}
        <div class="flex justify-end">
            <flux:button type="submit" variant="primary">
                ?? Guardar Configuración
            </button>
        </div>
    </form>
</div>
