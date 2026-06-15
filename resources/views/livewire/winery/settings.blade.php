<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="{{ __('Configuración') }}"
        :description="__('Gestiona la configuración de tu cuenta de bodega')"
    />

    <x-agro.card :padding="false">
        <div class="px-6 py-5">
            <x-agro.tabs
                :tabs="['taxes' => 'Impuestos', 'invoicing' => 'Numeración', 'plots' => 'Parcelas y Vendimia', 'fiscal' => 'Datos Fiscales', 'infovi' => 'INFOVI / SILICIE']"
                :active="$currentTab"
                wireMethod="switchTab"
            />

            {{-- TAXES TAB --}}
            @if($currentTab === 'taxes')
                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-bold text-zinc-900 mb-2">{{ __('Configuración de Impuestos') }}</h3>
                        <p class="text-sm text-zinc-600">{{ __('Selecciona el impuesto que se aplicará por defecto en tus facturas') }}</p>
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
                            <strong>{{ __('Nota:') }}</strong> Este impuesto se aplicará por defecto en todas tus nuevas facturas. Puedes cambiarlo en cualquier momento.
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
                                    <p class="font-bold text-zinc-900">{{ __('Facturas') }}</p>
                                    <p class="text-sm text-zinc-500">{{ __('Configuración de numeración de facturas') }}</p>
                                </div>
                            </div>
                        </x-slot:header>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <flux:label>{{ __('Prefijo') }}</flux:label>
                                <flux:input wire:model.live="invoice_prefix" placeholder="FAC-{YEAR}-" class="mt-1" />
                                <p class="mt-1 text-xs text-zinc-500">{{ __('Variables: {YEAR}, {MONTH}, {DAY}') }}</p>
                                @error('invoice_prefix') <flux:error>{{ $message }}</flux:error> @enderror
                            </div>
                            <div>
                                <flux:label>{{ __('Dígitos') }}</flux:label>
                                <flux:select wire:model.live="invoice_padding" class="mt-1">
                                    <option value="2">2 (01, 02, ...)</option>
                                    <option value="3">3 (001, 002, ...)</option>
                                    <option value="4">4 (0001, 0002, ...)</option>
                                    <option value="5">5 (00001, 00002, ...)</option>
                                    <option value="6">6 (000001, 000002, ...)</option>
                                </flux:select>
                            </div>
                            <div>
                                <flux:label>{{ __('Contador Actual') }}</flux:label>
                                <flux:input type="number" wire:model.live="invoice_counter" min="1" class="mt-1" />
                            </div>
                            <div class="flex items-end">
                                <flux:checkbox wire:model="invoice_year_reset" :label="__('Resetear cada año')" />
                            </div>
                        </div>

                        <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <p class="text-sm font-medium text-blue-800 mb-1">{{ __('Vista Previa:') }}</p>
                            <p class="text-2xl font-bold text-blue-900 font-mono">{{ $invoicePreview }}</p>
                        </div>
                        <div class="mt-3 flex justify-end">
                            <flux:button type="button" variant="danger" size="sm" wire:click="resetInvoiceCounter" wire:confirm="{{ __('¿Resetear contador a 1?') }}">
                                Resetear
                            </flux:button>
                        </div>
                    </x-agro.card>

                    {{-- Albaranes --}}
                    <x-agro.card>
                        <x-slot:header>
                            <div class="flex items-center gap-3">
                                <flux:icon icon="cube" class="size-5 text-agro-600" />
                                <div>
                                    <p class="font-bold text-zinc-900">{{ __('Albaranes') }}</p>
                                    <p class="text-sm text-zinc-500">{{ __('Configuración de numeración de albaranes') }}</p>
                                </div>
                            </div>
                        </x-slot:header>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <flux:label>{{ __('Prefijo') }}</flux:label>
                                <flux:input wire:model.live="delivery_note_prefix" placeholder="ALB-{YEAR}-" class="mt-1" />
                                <p class="mt-1 text-xs text-zinc-500">{{ __('Variables: {YEAR}, {MONTH}, {DAY}') }}</p>
                            </div>
                            <div>
                                <flux:label>{{ __('Dígitos') }}</flux:label>
                                <flux:select wire:model.live="delivery_note_padding" class="mt-1">
                                    <option value="2">2 (01, 02, ...)</option>
                                    <option value="3">3 (001, 002, ...)</option>
                                    <option value="4">4 (0001, 0002, ...)</option>
                                    <option value="5">5 (00001, 00002, ...)</option>
                                    <option value="6">6 (000001, 000002, ...)</option>
                                </flux:select>
                            </div>
                            <div>
                                <flux:label>{{ __('Contador Actual') }}</flux:label>
                                <flux:input type="number" wire:model.live="delivery_note_counter" min="1" class="mt-1" />
                            </div>
                            <div class="flex items-end">
                                <flux:checkbox wire:model="delivery_note_year_reset" :label="__('Resetear cada año')" />
                            </div>
                        </div>

                        <div class="mt-4 p-4 bg-agro-50 border border-agro-200 rounded-lg">
                            <p class="text-sm font-medium text-agro-800 mb-1">{{ __('Vista Previa:') }}</p>
                            <p class="text-2xl font-bold text-agro-900 font-mono">{{ $deliveryNotePreview }}</p>
                        </div>
                        <div class="mt-3 flex justify-end">
                            <flux:button type="button" variant="danger" size="sm" wire:click="resetDeliveryNoteCounter" wire:confirm="{{ __('¿Resetear contador a 1?') }}">
                                Resetear
                            </flux:button>
                        </div>
                    </x-agro.card>

                    <flux:callout variant="info" icon="information-circle">
                        <flux:callout.text>
                            <strong>{{ __('Variables:') }}</strong> <code>{YEAR}</code> = {{ date('Y') }}, <code>{MONTH}</code> = 01-12, <code>{DAY}</code> = 01-31
                        </flux:callout.text>
                    </flux:callout>

                    <div class="flex justify-end">
                        <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="saveInvoicing">{{ __('Guardar Configuración') }}</flux:button>
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
                                    <p class="font-bold text-zinc-900">{{ __('Valores por defecto de plantaciones') }}</p>
                                    <p class="text-sm text-zinc-500">{{ __('Se aplica al crear nuevas plantaciones si no se especifica un valor') }}</p>
                                </div>
                            </div>
                        </x-slot:header>

                        <div class="max-w-sm">
                            <flux:field>
                                <flux:label>{{ __('Límite kg/ha por defecto') }}</flux:label>
                                <flux:input wire:model="default_limit_kg_per_ha" type="number" step="0.01" min="0" :placeholder="__('Ej: 8000')" />
                                <flux:description>{{ __('Rendimiento máximo para nuevas plantaciones (kg/ha). Se auto-rellena al introducir el área plantada.') }}</flux:description>
                                <flux:error name="default_limit_kg_per_ha" />
                            </flux:field>
                        </div>
                    </x-agro.card>

                    <div class="flex justify-end">
                        <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="savePlots">{{ __('Guardar Configuración') }}</flux:button>
                    </div>
                </form>
            @endif
            {{-- FISCAL TAB --}}
            @if($currentTab === 'fiscal')
                <form wire:submit="saveFiscal" class="space-y-6">
                    @if(empty($fiscal_nif))
                        <flux:callout variant="warning" icon="exclamation-triangle">
                            <flux:callout.heading>{{ __('NIF/CIF no configurado') }}</flux:callout.heading>
                            <flux:callout.text>
                                {{ __('Tu NIF o CIF es obligatorio para emitir facturas y para el sistema VERI*FACTU (AEAT). Sin este dato no podrás verificar facturas en la Agencia Tributaria.') }}
                            </flux:callout.text>
                        </flux:callout>
                    @endif

                    {{-- Identificación fiscal --}}
                    <x-agro.card>
                        <x-slot:header>
                            <div class="flex items-center gap-3">
                                <flux:icon icon="identification" class="size-5 text-agro-600" />
                                <div>
                                    <p class="font-bold text-zinc-900">{{ __('Identificación fiscal') }}</p>
                                    <p class="text-sm text-zinc-500">{{ __('Datos que aparecerán en tus facturas como emisor') }}</p>
                                </div>
                            </div>
                        </x-slot:header>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <flux:field>
                                <flux:label>{{ __('NIF / CIF') }} <span class="text-red-500">*</span></flux:label>
                                <flux:input wire:model="fiscal_nif" :placeholder="__('Ej: B12345678')" maxlength="20" />
                                <flux:description>{{ __('Número de Identificación Fiscal de la empresa o autónomo. Obligatorio para Verifactu.') }}</flux:description>
                                <flux:error name="fiscal_nif" />
                                <p class="text-[11px] text-zinc-400 mt-1 flex items-center gap-1">
                                    <flux:icon icon="lock-closed" class="size-3 shrink-0" />
                                    {{ __('Dato protegido conforme al RGPD. Uso exclusivo para generación de documentos fiscales.') }}
                                </p>
                            </flux:field>

                            <flux:field>
                                <flux:label>{{ __('Nombre / Razón social fiscal') }}</flux:label>
                                <flux:input wire:model="fiscal_legal_name" :placeholder="__('Ej: Bodegas Ejemplo, S.L.')" maxlength="150" />
                                <flux:description>{{ __('Nombre legal que aparece en facturas. Si se deja vacío se usa el nombre de tu cuenta.') }}</flux:description>
                                <flux:error name="fiscal_legal_name" />
                            </flux:field>

                            <flux:field>
                                <flux:label>{{ __('Teléfono de contacto') }}</flux:label>
                                <flux:input wire:model="fiscal_phone" :placeholder="__('Ej: +34 600 000 000')" maxlength="20" />
                                <flux:error name="fiscal_phone" />
                            </flux:field>
                        </div>
                    </x-agro.card>

                    {{-- Dirección fiscal --}}
                    <x-agro.card>
                        <x-slot:header>
                            <div class="flex items-center gap-3">
                                <flux:icon icon="map-pin" class="size-5 text-blue-600" />
                                <div>
                                    <p class="font-bold text-zinc-900">{{ __('Dirección fiscal') }}</p>
                                    <p class="text-sm text-zinc-500">{{ __('Aparece en el encabezado de las facturas emitidas') }}</p>
                                </div>
                            </div>
                        </x-slot:header>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <flux:field>
                                    <flux:label>{{ __('Dirección') }}</flux:label>
                                    <flux:input wire:model="fiscal_address" :placeholder="__('Ej: Calle Mayor, 12')" maxlength="255" />
                                    <flux:error name="fiscal_address" />
                                </flux:field>
                            </div>

                            <flux:field>
                                <flux:label>{{ __('Población') }}</flux:label>
                                <flux:input wire:model="fiscal_city" :placeholder="__('Ej: Haro')" maxlength="100" />
                                <flux:error name="fiscal_city" />
                            </flux:field>

                            <flux:field>
                                <flux:label>{{ __('Código postal') }}</flux:label>
                                <flux:input wire:model="fiscal_postal_code" :placeholder="__('Ej: 26200')" maxlength="10" />
                                <flux:error name="fiscal_postal_code" />
                            </flux:field>
                        </div>
                    </x-agro.card>

                    <flux:callout variant="info" icon="information-circle">
                        <flux:callout.text>
                            El <strong>{{ __('NIF/CIF') }}</strong> y el <strong>{{ __('nombre fiscal') }}</strong> son los datos que la AEAT utiliza para identificarte
                            en el sistema VERI*FACTU. El resto de la dirección aparece en el encabezado de los PDFs de factura.
                        </flux:callout.text>
                    </flux:callout>

                    <div class="flex justify-end">
                        <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="saveFiscal">{{ __('Guardar Datos Fiscales') }}</flux:button>
                    </div>
                </form>
            @endif

            {{-- INFOVI TAB --}}
            @if($currentTab === 'infovi')
                <form wire:submit="saveInfovi" class="space-y-6">
                    <div>
                        <h3 class="text-lg font-bold text-zinc-900 mb-1">{{ __('Identificación INFOVI / SILICIE') }}</h3>
                        <p class="text-sm text-zinc-500">
                            Números de registro obligatorios para generar las declaraciones ante AICA
                            (Real Decreto 739/2015). Están disponibles en el portal <strong>{{ __('mapa.gob.es/infovi') }}</strong>
                            y en la resolución de inscripción en el REOVI de tu comunidad autónoma.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div class="space-y-1">
                            <flux:label for="reovi_number">{{ __('Número REOVI') }}</flux:label>
                            <flux:input
                                id="reovi_number"
                                wire:model="reovi_number"
                                placeholder="{{ __('Ej: ES-AN-0001234') }}"
                                maxlength="50"
                            />
                            <p class="text-xs text-zinc-400">{{ __('Registro de Operadores Vitivinícolas. Asignado por tu comunidad autónoma.') }}</p>
                            @error('reovi_number') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-1">
                            <flux:label for="nidpb">{{ __('NIDPB — Código de instalación') }}</flux:label>
                            <flux:input
                                id="nidpb"
                                wire:model="nidpb"
                                placeholder="{{ __('Ej: E12345678') }}"
                                maxlength="50"
                            />
                            <p class="text-xs text-zinc-400">{{ __('Número de Identificación del Depósito o Punto de Bodega. Aparece en el nombre
                                del fichero XML de la declaración INFOVI.') }}</p>
                            @error('nidpb') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <flux:callout variant="info" icon="information-circle">
                        <flux:callout.text>
                            El <strong>{{ __('número REOVI') }}</strong> y el <strong>{{ __('NIDPB') }}</strong> identifican tu instalación
                            en el sistema de declaraciones INFOVI de la AICA. Son necesarios para que Agro365 genere
                            los cuadros de declaración correctamente y para que puedas cumplimentar el portal oficial.
                            Si aún no estás registrado, consulta la
                            <strong>{{ __('sección de INFOVI del MAPA') }}</strong> o la consejería de agricultura de tu comunidad autónoma.
                        </flux:callout.text>
                    </flux:callout>

                    <div class="flex justify-end">
                        <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="saveInfovi">{{ __('Guardar configuración INFOVI') }}</flux:button>
                    </div>
                </form>
            @endif
        </div>
    </x-agro.card>
</div>
