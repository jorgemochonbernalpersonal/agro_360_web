<div class="space-y-6 animate-fade-in">
    @php
        $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>';
    @endphp
    
    <x-page-header
        :icon="$icon"
        title="Configuración"
        description="Gestiona la configuración de tu cuenta"
        icon-color="from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]"
    />

    {{-- Tabs Navigation --}}
    <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
        <div class="border-b border-gray-200">
            <nav class="flex -mb-px">
                <button 
                    wire:click="switchTab('taxes')"
                    class="group inline-flex items-center gap-2 px-6 py-4 border-b-2 font-medium text-sm transition-colors
                        {{ $currentTab === 'taxes' ? 'border-[var(--color-agro-green-dark)] text-[var(--color-agro-green-dark)]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    <span>Impuestos</span>
                </button>
                
                <button 
                    wire:click="switchTab('invoicing')"
                    class="group inline-flex items-center gap-2 px-6 py-4 border-b-2 font-medium text-sm transition-colors
                        {{ $currentTab === 'invoicing' ? 'border-[var(--color-agro-green-dark)] text-[var(--color-agro-green-dark)]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                    </svg>
                    <span>Numeración</span>
                </button>
            </nav>
        </div>

        {{-- Tab Content --}}
        <div class="p-6">
            {{-- TAXES TAB --}}
            @if($currentTab === 'taxes')
                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Configuración de Impuestos</h3>
                        <p class="text-sm text-gray-600">Selecciona el impuesto que se aplicará por defecto en tus facturas</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        @foreach($taxes as $tax)
                            @php
                                $isActive = $activeTaxId == $tax->id;
                                $colors = [
                                    'Exento' => ['border' => 'border-gray-300', 'bg' => 'bg-gray-50', 'text' => 'text-gray-700', 'icon' => '🚫'],
                                    'IVA' => ['border' => 'border-blue-300', 'bg' => 'bg-blue-50', 'text' => 'text-blue-700', 'icon' => '🇪🇸'],
                                    'IGIC' => ['border' => 'border-yellow-300', 'bg' => 'bg-yellow-50', 'text' => 'text-yellow-700', 'icon' => '🏝️']
                                ];
                                $color = $colors[$tax->name] ?? $colors['Exento'];
                            @endphp
                            
                            <button 
                                wire:click="selectTax({{ $tax->id }})"
                                class="group relative p-6 rounded-xl border-2 transition-all duration-300 text-left
                                    {{ $isActive 
                                        ? 'border-[var(--color-agro-green-dark)] bg-[var(--color-agro-green-bg)] shadow-lg scale-105' 
                                        : $color['border'] . ' ' . $color['bg'] . ' hover:shadow-md hover:scale-102' }}"
                            >
                                {{-- Active Badge --}}
                                @if($isActive)
                                    <div class="absolute -top-3 -right-3 bg-[var(--color-agro-green-dark)] text-white text-xs font-bold px-3 py-1 rounded-full shadow-lg">
                                        ✓ Activo
                                    </div>
                                @endif

                                {{-- Icon --}}
                                <div class="text-4xl mb-3">{{ $color['icon'] }}</div>

                                {{-- Content --}}
                                <h4 class="text-xl font-bold {{ $isActive ? 'text-[var(--color-agro-green-dark)]' : $color['text'] }} mb-1">
                                    {{ $tax->name }}
                                </h4>
                                <p class="text-3xl font-bold {{ $isActive ? 'text-[var(--color-agro-green-dark)]' : 'text-gray-600' }} mb-2">
                                    {{ number_format($tax->rate, 0) }}%
                                </p>
                                <p class="text-xs {{ $isActive ? 'text-[var(--color-agro-green)]' : 'text-gray-500' }}">
                                    @if($tax->name === 'IVA')
                                        Península y Baleares
                                    @elseif($tax->name === 'IGIC')
                                        Canarias
                                    @else
                                        Sin impuestos
                                    @endif
                                </p>
                            </button>
                        @endforeach
                    </div>

                    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            <div class="flex-1">
                                <p class="text-sm text-blue-800">
                                    <strong>Nota:</strong> Este impuesto se aplicará por defecto en todas tus nuevas facturas. Puedes cambiar esta configuración en cualquier momento.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- INVOICING TAB --}}
            @if($currentTab === 'invoicing')
                <form wire:submit="saveInvoicing" class="space-y-6">
                    {{-- Facturas --}}
                    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-6 border border-blue-200">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">📄 Facturas</h3>
                                <p class="text-sm text-gray-600">Configuración de numeración de facturas</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- Prefijo --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Prefijo</label>
                                <input 
                                    type="text" 
                                    wire:model.live="invoice_prefix"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                    placeholder="FAC-{YEAR}-"
                                >
                                <p class="mt-1 text-xs text-gray-500">Variables: {YEAR}, {MONTH}, {DAY}</p>
                                @error('invoice_prefix') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                            </div>

                            {{-- Dígitos --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Dígitos</label>
                                <select wire:model.live="invoice_padding" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    <option value="2">2 (01, 02, ...)</option>
                                    <option value="3">3 (001, 002, ...)</option>
                                    <option value="4">4 (0001, 0002, ...)</option>
                                    <option value="5">5 (00001, 00002, ...)</option>
                                    <option value="6">6 (000001, 000002, ...)</option>
                                </select>
                            </div>

                            {{-- Contador --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Contador Actual</label>
                                <input 
                                    type="number" 
                                    wire:model.live="invoice_counter"
                                    min="1"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                >
                            </div>

                            {{-- Reseteo --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Reseteo Automático</label>
                                <label class="flex items-center gap-3 p-3 bg-white border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                                    <input type="checkbox" wire:model="invoice_year_reset" class="w-5 h-5 text-blue-600 rounded">
                                    <span class="text-sm text-gray-900">Resetear cada año</span>
                                </label>
                            </div>
                        </div>

                        {{-- Preview --}}
                        <div class="mt-4 p-4 bg-blue-100 border-2 border-blue-300 rounded-lg">
                            <p class="text-sm font-medium text-blue-800 mb-1">Vista Previa:</p>
                            <p class="text-2xl font-bold text-blue-900 font-mono">{{ $invoicePreview }}</p>
                        </div>

                        <div class="mt-3 flex justify-end">
                            <button 
                                type="button"
                                wire:click="resetInvoiceCounter"
                                wire:confirm="¿Resetear contador a 1?"
                                class="px-4 py-2 bg-red-50 text-red-700 border border-red-200 rounded-lg hover:bg-red-100 text-sm font-medium"
                            >
                                🔄 Resetear
                            </button>
                        </div>
                    </div>

                    {{-- Albaranes --}}
                    <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl p-6 border border-green-200">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-green-500 to-green-600 flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">📦 Albaranes</h3>
                                <p class="text-sm text-gray-600">Configuración de numeración de albaranes</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Prefijo</label>
                                <input 
                                    type="text" 
                                    wire:model.live="delivery_note_prefix"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                                    placeholder="ALB-{YEAR}-"
                                >
                                <p class="mt-1 text-xs text-gray-500">Variables: {YEAR}, {MONTH}, {DAY}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Dígitos</label>
                                <select wire:model.live="delivery_note_padding" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                                    <option value="2">2 (01, 02, ...)</option>
                                    <option value="3">3 (001, 002, ...)</option>
                                    <option value="4">4 (0001, 0002, ...)</option>
                                    <option value="5">5 (00001, 00002, ...)</option>
                                    <option value="6">6 (000001, 000002, ...)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Contador Actual</label>
                                <input 
                                    type="number" 
                                    wire:model.live="delivery_note_counter"
                                    min="1"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                                >
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Reseteo Automático</label>
                                <label class="flex items-center gap-3 p-3 bg-white border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                                    <input type="checkbox" wire:model="delivery_note_year_reset" class="w-5 h-5 text-green-600 rounded">
                                    <span class="text-sm text-gray-900">Resetear cada año</span>
                                </label>
                            </div>
                        </div>

                        <div class="mt-4 p-4 bg-green-100 border-2 border-green-300 rounded-lg">
                            <p class="text-sm font-medium text-green-800 mb-1">Vista Previa:</p>
                            <p class="text-2xl font-bold text-green-900 font-mono">{{ $deliveryNotePreview }}</p>
                        </div>

                        <div class="mt-3 flex justify-end">
                            <button 
                                type="button"
                                wire:click="resetDeliveryNoteCounter"
                                wire:confirm="¿Resetear contador a 1?"
                                class="px-4 py-2 bg-red-50 text-red-700 border border-red-200 rounded-lg hover:bg-red-100 text-sm font-medium"
                            >
                                🔄 Resetear
                            </button>
                        </div>
                    </div>

                    {{-- Info --}}
                    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg">
                        <p class="text-xs text-blue-800">
                            <strong>Variables:</strong> <code>{YEAR}</code> = 2025, <code>{MONTH}</code> = 01-12, <code>{DAY}</code> = 01-31
                        </p>
                    </div>

                    {{-- Guardar --}}
                    <div class="flex justify-end">
                        <button 
                            type="submit"
                            class="px-6 py-3 bg-gradient-to-r from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)] text-white rounded-lg hover:shadow-lg transition-all font-semibold"
                        >
                            💾 Guardar Configuración
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>
