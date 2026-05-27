<div class="space-y-6 animate-fade-in">
    @php
        $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>';
    @endphp

    <x-agro.page-header
        :icon="$icon"
        title="{{ __('Configuración de Numeración') }}"
        :description="__('Personaliza cómo se generan los números de facturas y albaranes')"
        icon-color="from-agro-500 to-agro-700"
    />

    <form wire:submit="save" class="space-y-6">

        {{-- Facturas --}}
        <x-agro.form-card>
            <x-slot:header>
                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-100 to-blue-200 flex items-center justify-center">
                    <flux:icon icon="document-text" class="size-5 text-blue-600" />
                </div>
                <span>{{ __('Facturas') }}</span>
            </x-slot:header>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Prefijo --}}
                <div>
                    <flux:label>{{ __('Prefijo') }}</flux:label>
                    <flux:input
                        wire:model.live="invoice_prefix"
                        placeholder="FAC-{YEAR}-"
                        class="mt-1"
                    />
                    <p class="mt-1 text-xs text-zinc-500">
                        Variables: <code class="bg-zinc-100 px-1 rounded">{YEAR}</code>
                        <code class="bg-zinc-100 px-1 rounded">{YY}</code>
                        <code class="bg-zinc-100 px-1 rounded">{MONTH}</code>
                        <code class="bg-zinc-100 px-1 rounded">{DAY}</code>
                    </p>
                    @error('invoice_prefix') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Dígitos --}}
                <div>
                    <flux:label>{{ __('Número de Dígitos') }}</flux:label>
                    <flux:select wire:model.live="invoice_padding" class="mt-1">
                        <flux:select.option value="2">{{ __('2 dígitos — 01, 02 …') }}</flux:select.option>
                        <flux:select.option value="3">{{ __('3 dígitos — 001, 002 …') }}</flux:select.option>
                        <flux:select.option value="4">{{ __('4 dígitos — 0001, 0002 …') }}</flux:select.option>
                        <flux:select.option value="5">{{ __('5 dígitos — 00001, 00002 …') }}</flux:select.option>
                        <flux:select.option value="6">{{ __('6 dígitos — 000001, 000002 …') }}</flux:select.option>
                    </flux:select>
                    @error('invoice_padding') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Contador --}}
                <div>
                    <flux:label>{{ __('Contador Actual') }}</flux:label>
                    <flux:input
                        type="number"
                        wire:model.live="invoice_counter"
                        min="1"
                        class="mt-1"
                    />
                    <p class="mt-1 text-xs text-zinc-500">{{ __('La próxima factura usará este número') }}</p>
                    @error('invoice_counter') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Reseteo anual --}}
                <div class="flex flex-col justify-center">
                    <flux:label>{{ __('Reseteo Automático') }}</flux:label>
                    <label class="mt-1 flex items-center gap-3 p-3 border border-zinc-200 rounded-lg cursor-pointer hover:bg-zinc-50">
                        <flux:checkbox wire:model="invoice_year_reset" />
                        <div>
                            <p class="text-sm font-medium text-zinc-900">{{ __('Resetear cada año') }}</p>
                            <p class="text-xs text-zinc-500">{{ __('El contador vuelve a 1 el 1 de enero') }}</p>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Vista previa --}}
            <div class="mt-6 p-4 bg-blue-50 border-2 border-blue-200 rounded-lg">
                <p class="text-sm font-medium text-blue-700 mb-1">{{ __('Vista previa') }}</p>
                <p class="text-3xl font-bold text-blue-900 font-mono tracking-wide">{{ $invoicePreview }}</p>
                <p class="mt-1 text-xs text-blue-600">{{ __('Próxima factura que se generará') }}</p>
            </div>

            <div class="mt-4 flex justify-end">
                <flux:button
                    type="button"
                    variant="danger"
                    wire:click="resetInvoiceCounter"
                    wire:confirm="{{ __('¿Estás seguro de resetear el contador de facturas a 1?') }}"
                >
                    Resetear contador
                </flux:button>
            </div>
        </x-agro.form-card>

        {{-- Albaranes --}}
        <x-agro.form-card>
            <x-slot:header>
                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-emerald-100 to-emerald-200 flex items-center justify-center">
                    <flux:icon icon="clipboard-document" class="size-5 text-emerald-600" />
                </div>
                <span>{{ __('Albaranes') }}</span>
            </x-slot:header>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Prefijo --}}
                <div>
                    <flux:label>{{ __('Prefijo') }}</flux:label>
                    <flux:input
                        wire:model.live="delivery_note_prefix"
                        placeholder="ALB-{YEAR}-"
                        class="mt-1"
                    />
                    <p class="mt-1 text-xs text-zinc-500">
                        Variables: <code class="bg-zinc-100 px-1 rounded">{YEAR}</code>
                        <code class="bg-zinc-100 px-1 rounded">{YY}</code>
                        <code class="bg-zinc-100 px-1 rounded">{MONTH}</code>
                        <code class="bg-zinc-100 px-1 rounded">{DAY}</code>
                    </p>
                    @error('delivery_note_prefix') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Dígitos --}}
                <div>
                    <flux:label>{{ __('Número de Dígitos') }}</flux:label>
                    <flux:select wire:model.live="delivery_note_padding" class="mt-1">
                        <flux:select.option value="2">{{ __('2 dígitos — 01, 02 …') }}</flux:select.option>
                        <flux:select.option value="3">{{ __('3 dígitos — 001, 002 …') }}</flux:select.option>
                        <flux:select.option value="4">{{ __('4 dígitos — 0001, 0002 …') }}</flux:select.option>
                        <flux:select.option value="5">{{ __('5 dígitos — 00001, 00002 …') }}</flux:select.option>
                        <flux:select.option value="6">{{ __('6 dígitos — 000001, 000002 …') }}</flux:select.option>
                    </flux:select>
                    @error('delivery_note_padding') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Contador --}}
                <div>
                    <flux:label>{{ __('Contador Actual') }}</flux:label>
                    <flux:input
                        type="number"
                        wire:model.live="delivery_note_counter"
                        min="1"
                        class="mt-1"
                    />
                    <p class="mt-1 text-xs text-zinc-500">{{ __('El próximo albarán usará este número') }}</p>
                    @error('delivery_note_counter') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Reseteo anual --}}
                <div class="flex flex-col justify-center">
                    <flux:label>{{ __('Reseteo Automático') }}</flux:label>
                    <label class="mt-1 flex items-center gap-3 p-3 border border-zinc-200 rounded-lg cursor-pointer hover:bg-zinc-50">
                        <flux:checkbox wire:model="delivery_note_year_reset" />
                        <div>
                            <p class="text-sm font-medium text-zinc-900">{{ __('Resetear cada año') }}</p>
                            <p class="text-xs text-zinc-500">{{ __('El contador vuelve a 1 el 1 de enero') }}</p>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Vista previa --}}
            <div class="mt-6 p-4 bg-emerald-50 border-2 border-emerald-200 rounded-lg">
                <p class="text-sm font-medium text-emerald-700 mb-1">{{ __('Vista previa') }}</p>
                <p class="text-3xl font-bold text-emerald-900 font-mono tracking-wide">{{ $deliveryNotePreview }}</p>
                <p class="mt-1 text-xs text-emerald-600">{{ __('Próximo albarán que se generará') }}</p>
            </div>

            <div class="mt-4 flex justify-end">
                <flux:button
                    type="button"
                    variant="danger"
                    wire:click="resetDeliveryNoteCounter"
                    wire:confirm="{{ __('¿Estás seguro de resetear el contador de albaranes a 1?') }}"
                >
                    Resetear contador
                </flux:button>
            </div>
        </x-agro.form-card>

        {{-- Referencia de variables --}}
        <flux:callout variant="info" icon="information-circle">
            <flux:callout.heading>{{ __('Variables disponibles en los prefijos') }}</flux:callout.heading>
            <flux:callout.text>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-2">
                    <div class="bg-white rounded-lg p-3 border border-blue-200">
                        <code class="text-sm font-bold text-blue-800">{YEAR}</code>
                        <p class="text-xs text-zinc-600 mt-1">Año 4 dígitos<br><span class="font-semibold">{{ now()->format('Y') }}</span></p>
                    </div>
                    <div class="bg-white rounded-lg p-3 border border-blue-200">
                        <code class="text-sm font-bold text-blue-800">{YY}</code>
                        <p class="text-xs text-zinc-600 mt-1">Año 2 dígitos<br><span class="font-semibold">{{ now()->format('y') }}</span></p>
                    </div>
                    <div class="bg-white rounded-lg p-3 border border-blue-200">
                        <code class="text-sm font-bold text-blue-800">{MONTH}</code>
                        <p class="text-xs text-zinc-600 mt-1">Mes (01-12)<br><span class="font-semibold">{{ now()->format('m') }}</span></p>
                    </div>
                    <div class="bg-white rounded-lg p-3 border border-blue-200">
                        <code class="text-sm font-bold text-blue-800">{DAY}</code>
                        <p class="text-xs text-zinc-600 mt-1">Día (01-31)<br><span class="font-semibold">{{ now()->format('d') }}</span></p>
                    </div>
                </div>
                <p class="mt-3 text-xs text-zinc-500">
                    Ejemplo: <code class="bg-blue-100 px-1 rounded">FAC-{YEAR}-</code> genera
                    <code class="bg-blue-100 px-1 rounded">FAC-{{ now()->format('Y') }}-0001</code>
                </p>
            </flux:callout.text>
        </flux:callout>

        {{-- Guardar --}}
        <x-agro.form-actions>
            <flux:button type="submit" variant="primary">{{ __('Guardar configuración') }}</flux:button>
        </x-agro.form-actions>

    </form>
</div>
