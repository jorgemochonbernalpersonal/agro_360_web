<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Configuración"
        description="Gestiona la configuración de tu cuenta de bodega"
    />

    <x-agro.card :padding="false">
        <div class="px-6 py-5">
            <x-agro.tabs
                :tabs="['taxes' => 'Impuestos', 'invoicing' => 'Numeración', 'plots' => 'Parcelas y Vendimia']"
                :active="$currentTab"
                wireMethod="switchTab"
            />

            {{-- TAXES TAB --}}
            @if($currentTab === 'taxes')
                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-bold text-zinc-900 mb-2">Configuración de Impuestos</h3>
                        <p class="text-sm text-zinc-600">Selecciona el impuesto que se aplicará por defecto en tus facturas</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        @foreach($taxes as $tax)
                            @php
                                $isActive = $activeTaxId == $tax->id;
                                $colors = [
                                    'Exento' => ['border' => 'border-zinc-300', 'bg' => 'bg-zinc-50', 'text' => 'text-zinc-700', 'icon' => '🛡️'],
                                    'IVA'    => ['border' => 'border-blue-300',  'bg' => 'bg-blue-50',  'text' => 'text-blue-700',  'icon' => '🇪🇸'],
                                    'IGIC'   => ['border' => 'border-yellow-300','bg' => 'bg-yellow-50','text' => 'text-yellow-700','icon' => '🌴'],
                                ];
                                $color = $colors[$tax->name] ?? $colors['Exento'];
                            @endphp
                            <button
                                wire:click="selectTax({{ $tax->id }})"
                                class="group relative p-6 rounded-xl border-2 transition-all duration-300 text-left
                                    {{ $isActive
                                        ? 'border-agro-700 bg-agro-50 shadow-lg scale-105'
                                        : $color['border'] . ' ' . $color['bg'] . ' hover:shadow-md hover:scale-102' }}"
                            >
                                @if($isActive)
                                    <div class="absolute -top-3 -right-3 bg-agro-700 text-white text-xs font-bold px-3 py-1 rounded-full shadow-lg">
                                        ✓ Activo
                                    </div>
                                @endif
                                <div class="text-4xl mb-3">{{ $color['icon'] }}</div>
                                <h4 class="text-xl font-bold {{ $isActive ? 'text-agro-700' : $color['text'] }} mb-1">{{ $tax->name }}</h4>
                                <p class="text-3xl font-bold {{ $isActive ? 'text-agro-700' : 'text-zinc-600' }} mb-2">{{ number_format($tax->rate, 0) }}%</p>
                                <p class="text-xs {{ $isActive ? 'text-agro-600' : 'text-zinc-500' }}">
                                    @if($tax->name === 'IVA') Península y Baleares
                                    @elseif($tax->name === 'IGIC') Canarias
                                    @else Sin impuestos
                                    @endif
                                </p>
                            </button>
                        @endforeach
                    </div>

                    <flux:callout variant="info" icon="information-circle">
                        <flux:callout.text>
                            <strong>Nota:</strong> Este impuesto se aplicará por defecto en todas tus nuevas facturas. Puedes cambiarlo en cualquier momento.
                        </flux:callout.text>
                    </flux:callout>
                </div>
            @endif

            {{-- INVOICING TAB --}}
            @if($currentTab === 'invoicing')
                <form wire:submit="saveInvoicing" class="space-y-6">
                    {{-- Facturas --}}
                    <x-agro.card>
                        <x-slot:header>
                            <div class="flex items-center gap-3">
                                <flux:icon icon="document-text" class="size-5 text-blue-600" />
                                <div>
                                    <p class="font-bold text-zinc-900">Facturas</p>
                                    <p class="text-sm text-zinc-500">Configuración de numeración de facturas</p>
                                </div>
                            </div>
                        </x-slot:header>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <flux:label>Prefijo</flux:label>
                                <flux:input wire:model.live="invoice_prefix" placeholder="FAC-{YEAR}-" class="mt-1" />
                                <p class="mt-1 text-xs text-zinc-500">Variables: {YEAR}, {MONTH}, {DAY}</p>
                                @error('invoice_prefix') <flux:error>{{ $message }}</flux:error> @enderror
                            </div>
                            <div>
                                <flux:label>Dígitos</flux:label>
                                <flux:select wire:model.live="invoice_padding" class="mt-1">
                                    <option value="2">2 (01, 02, ...)</option>
                                    <option value="3">3 (001, 002, ...)</option>
                                    <option value="4">4 (0001, 0002, ...)</option>
                                    <option value="5">5 (00001, 00002, ...)</option>
                                    <option value="6">6 (000001, 000002, ...)</option>
                                </flux:select>
                            </div>
                            <div>
                                <flux:label>Contador Actual</flux:label>
                                <flux:input type="number" wire:model.live="invoice_counter" min="1" class="mt-1" />
                            </div>
                            <div class="flex items-end">
                                <flux:checkbox wire:model="invoice_year_reset" label="Resetear cada año" />
                            </div>
                        </div>

                        <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <p class="text-sm font-medium text-blue-800 mb-1">Vista Previa:</p>
                            <p class="text-2xl font-bold text-blue-900 font-mono">{{ $invoicePreview }}</p>
                        </div>
                        <div class="mt-3 flex justify-end">
                            <flux:button type="button" variant="danger" size="sm" wire:click="resetInvoiceCounter" wire:confirm="¿Resetear contador a 1?">
                                Resetear
                            </flux:button>
                        </div>
                    </x-agro.card>

                    {{-- Albaranes --}}
                    <x-agro.card>
                        <x-slot:header>
                            <div class="flex items-center gap-3">
                                <flux:icon icon="cube" class="size-5 text-green-600" />
                                <div>
                                    <p class="font-bold text-zinc-900">Albaranes</p>
                                    <p class="text-sm text-zinc-500">Configuración de numeración de albaranes</p>
                                </div>
                            </div>
                        </x-slot:header>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <flux:label>Prefijo</flux:label>
                                <flux:input wire:model.live="delivery_note_prefix" placeholder="ALB-{YEAR}-" class="mt-1" />
                                <p class="mt-1 text-xs text-zinc-500">Variables: {YEAR}, {MONTH}, {DAY}</p>
                            </div>
                            <div>
                                <flux:label>Dígitos</flux:label>
                                <flux:select wire:model.live="delivery_note_padding" class="mt-1">
                                    <option value="2">2 (01, 02, ...)</option>
                                    <option value="3">3 (001, 002, ...)</option>
                                    <option value="4">4 (0001, 0002, ...)</option>
                                    <option value="5">5 (00001, 00002, ...)</option>
                                    <option value="6">6 (000001, 000002, ...)</option>
                                </flux:select>
                            </div>
                            <div>
                                <flux:label>Contador Actual</flux:label>
                                <flux:input type="number" wire:model.live="delivery_note_counter" min="1" class="mt-1" />
                            </div>
                            <div class="flex items-end">
                                <flux:checkbox wire:model="delivery_note_year_reset" label="Resetear cada año" />
                            </div>
                        </div>

                        <div class="mt-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                            <p class="text-sm font-medium text-green-800 mb-1">Vista Previa:</p>
                            <p class="text-2xl font-bold text-green-900 font-mono">{{ $deliveryNotePreview }}</p>
                        </div>
                        <div class="mt-3 flex justify-end">
                            <flux:button type="button" variant="danger" size="sm" wire:click="resetDeliveryNoteCounter" wire:confirm="¿Resetear contador a 1?">
                                Resetear
                            </flux:button>
                        </div>
                    </x-agro.card>

                    <flux:callout variant="info" icon="information-circle">
                        <flux:callout.text>
                            <strong>Variables:</strong> <code>{YEAR}</code> = {{ date('Y') }}, <code>{MONTH}</code> = 01-12, <code>{DAY}</code> = 01-31
                        </flux:callout.text>
                    </flux:callout>

                    <div class="flex justify-end">
                        <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="saveInvoicing">
                            Guardar Configuración
                        </flux:button>
                    </div>
                </form>
            @endif

            {{-- PLOTS TAB --}}
            @if($currentTab === 'plots')
                <form wire:submit="savePlots" class="space-y-6">
                    <x-agro.card>
                        <x-slot:header>
                            <div class="flex items-center gap-3">
                                <flux:icon icon="map" class="size-5 text-agro-600" />
                                <div>
                                    <p class="font-bold text-zinc-900">Valores por defecto de plantaciones</p>
                                    <p class="text-sm text-zinc-500">Se aplica al crear nuevas plantaciones si no se especifica un valor</p>
                                </div>
                            </div>
                        </x-slot:header>

                        <div class="max-w-sm">
                            <flux:field>
                                <flux:label>Límite kg/ha por defecto</flux:label>
                                <flux:input wire:model="default_limit_kg_per_ha" type="number" step="0.01" min="0" placeholder="Ej: 8000" />
                                <flux:description>Rendimiento máximo para nuevas plantaciones (kg/ha). Se auto-rellena al introducir el área plantada.</flux:description>
                                <flux:error name="default_limit_kg_per_ha" />
                            </flux:field>
                        </div>
                    </x-agro.card>

                    <div class="flex justify-end">
                        <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="savePlots">
                            Guardar Configuración
                        </flux:button>
                    </div>
                </form>
            @endif
        </div>
    </x-agro.card>
</div>
