<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        :title="__('Configuración')"
        :description="__('Gestiona la configuración de tu cuenta')"
    />

    <x-agro.card :padding="false">
        <div class="px-6 py-5">
            <x-agro.tabs
                :tabs="['taxes' => __('Impuestos'), 'invoicing' => __('Numeración'), 'fiscal' => __('Datos Fiscales'), 'fieldbook' => __('Cuaderno de Campo'), 'signature' => __('Firma Digital')]"
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
                                        ✓ {{ __('Activo') }}
                                    </div>
                                @endif

                                <div class="text-4xl mb-3">{{ $color['icon'] }}</div>

                                <h4 class="text-xl font-bold {{ $isActive ? 'text-agro-700' : $color['text'] }} mb-1">
                                    {{ $tax->name }}
                                </h4>
                                <p class="text-3xl font-bold {{ $isActive ? 'text-agro-700' : 'text-zinc-600' }} mb-2">
                                    {{ number_format($tax->rate, 0) }}%
                                </p>
                                <p class="text-xs {{ $isActive ? 'text-agro-600' : 'text-zinc-500' }}">
                                    @if($tax->name === 'IVA')
                                        {{ __('Península y Baleares') }}
                                    @elseif($tax->name === 'IGIC')
                                        {{ __('Canarias') }}
                                    @else
                                        {{ __('Sin impuestos') }}
                                    @endif
                                </p>
                            </button>
                        @endforeach
                    </div>

                    <flux:callout variant="info" icon="information-circle">
                        <flux:callout.text>
                            <strong>{{ __('Nota') }}:</strong> {{ __('Este impuesto se aplicará por defecto en todas tus nuevas facturas. Puedes cambiar esta configuración en cualquier momento.') }}
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
                                <p class="mt-1 text-xs text-zinc-500">{{ __('Variables') }}: {YEAR}, {MONTH}, {DAY}</p>
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
                            <p class="text-sm font-medium text-blue-800 mb-1">{{ __('Vista Previa') }}:</p>
                            <p class="text-2xl font-bold text-blue-900 font-mono">{{ $invoicePreview }}</p>
                        </div>

                        <div class="mt-3 flex justify-end">
                            <flux:button
                                type="button"
                                variant="danger"
                                size="sm"
                                wire:click="resetInvoiceCounter"
                                wire:confirm="{{ __('¿Resetear contador a 1?') }}"
                            >
                                {{ __('Resetear') }}
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
                                <p class="mt-1 text-xs text-zinc-500">{{ __('Variables') }}: {YEAR}, {MONTH}, {DAY}</p>
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
                            <p class="text-sm font-medium text-agro-800 mb-1">{{ __('Vista Previa') }}:</p>
                            <p class="text-2xl font-bold text-agro-900 font-mono">{{ $deliveryNotePreview }}</p>
                        </div>

                        <div class="mt-3 flex justify-end">
                            <flux:button
                                type="button"
                                variant="danger"
                                size="sm"
                                wire:click="resetDeliveryNoteCounter"
                                wire:confirm="{{ __('¿Resetear contador a 1?') }}"
                            >
                                {{ __('Resetear') }}
                            </flux:button>
                        </div>
                    </x-agro.card>

                    <flux:callout variant="info" icon="information-circle">
                        <flux:callout.text>
                            <strong>{{ __('Variables') }}:</strong> <code>{YEAR}</code> = {{ date('Y') }}, <code>{MONTH}</code> = 01-12, <code>{DAY}</code> = 01-31
                        </flux:callout.text>
                    </flux:callout>

                    <div class="flex justify-end">
                        <flux:button
                            type="submit"
                            variant="primary"
                            wire:loading.attr="disabled"
                            wire:target="saveInvoicing"
                        >
                            {{ __('Guardar Configuración') }}
                        </flux:button>
                    </div>
                </form>
            @endif

            {{-- FISCAL TAB --}}
            @if($currentTab === 'fiscal')
                <form wire:submit="saveFiscal" class="space-y-6">
                    @if(empty($fiscal_nif))
                        <flux:callout variant="warning" icon="exclamation-triangle">
                            <flux:callout.heading>{{ __('NIF/DNI no configurado') }}</flux:callout.heading>
                            <flux:callout.text>
                                {{ __('Tu NIF o DNI es obligatorio para emitir facturas y para el sistema VERI*FACTU (AEAT). Sin este dato no podrás verificar facturas en la Agencia Tributaria.') }}
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
                                <flux:label>{{ __('NIF / DNI') }} <span class="text-red-500">*</span></flux:label>
                                <flux:input wire:model="fiscal_nif" :placeholder="__('Ej: 12345678A')" maxlength="20" />
                                <flux:description>{{ __('Número de Identificación Fiscal. Obligatorio para Verifactu.') }}</flux:description>
                                <flux:error name="fiscal_nif" />
                                <p class="text-[11px] text-zinc-400 mt-1 flex items-center gap-1">
                                    <flux:icon icon="lock-closed" class="size-3 shrink-0" />
                                    {{ __('Dato protegido conforme al RGPD. Uso exclusivo para generación de documentos fiscales.') }}
                                </p>
                            </flux:field>

                            <flux:field>
                                <flux:label>{{ __('Nombre fiscal') }}</flux:label>
                                <flux:input wire:model="fiscal_legal_name" :placeholder="__('Ej: Juan García Pérez')" maxlength="150" />
                                <flux:description>{{ __('Nombre que aparece en facturas. Si se deja vacío se usa el nombre de tu cuenta.') }}</flux:description>
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
                                <flux:input wire:model="fiscal_city" :placeholder="__('Ej: Labastida')" maxlength="100" />
                                <flux:error name="fiscal_city" />
                            </flux:field>

                            <flux:field>
                                <flux:label>{{ __('Código postal') }}</flux:label>
                                <flux:input wire:model="fiscal_postal_code" :placeholder="__('Ej: 01330')" maxlength="10" />
                                <flux:error name="fiscal_postal_code" />
                            </flux:field>
                        </div>
                    </x-agro.card>

                    <flux:callout variant="info" icon="information-circle">
                        <flux:callout.text>
                            {{ __('El') }} <strong>{{ __('NIF/DNI') }}</strong> {{ __('es el dato que la AEAT utiliza para identificarte en el sistema VERI*FACTU. El resto de la dirección aparece en el encabezado de los PDFs de factura.') }}
                        </flux:callout.text>
                    </flux:callout>

                    <div class="flex justify-end">
                        <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="saveFiscal">
                            {{ __('Guardar Datos Fiscales') }}
                        </flux:button>
                    </div>
                </form>

                @include('livewire.partials.verifactu-certificate-card')
            @endif

            {{-- FIELDBOOK TAB --}}
            @if($currentTab === 'fieldbook')
                <form wire:submit="saveFieldbook" class="space-y-6">

                    <x-agro.card>
                        <x-slot:header>
                            <div class="flex items-center gap-3">
                                <flux:icon icon="beaker" class="size-5 text-agro-600" />
                                <div>
                                    <p class="font-bold text-zinc-900">{{ __('Valores por defecto de parcelas') }}</p>
                                    <p class="text-sm text-zinc-500">{{ __('Se aplican al crear nuevas plantaciones si no se especifica un valor') }}</p>
                                </div>
                            </div>
                        </x-slot:header>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <flux:field>
                                <flux:label>{{ __('Límite kg/ha por defecto') }}</flux:label>
                                <flux:input wire:model="default_limit_kg_per_ha" type="number" step="0.01" min="0" :placeholder="__('Ej: 8000')" />
                                <flux:description>{{ __('Rendimiento máximo para nuevas plantaciones (kg/ha)') }}</flux:description>
                                <flux:error name="default_limit_kg_per_ha" />
                            </flux:field>

                            <flux:field>
                                <flux:label>{{ __('Temperatura base grados-día (°C)') }}</flux:label>
                                <flux:input wire:model="degree_day_base" type="number" step="0.1" min="0" max="30" placeholder="10.0" />
                                <flux:description>{{ __('Base para calcular grados-día acumulados (Tbase). Estándar viña: 10 °C') }}</flux:description>
                                <flux:error name="degree_day_base" />
                            </flux:field>
                        </div>
                    </x-agro.card>

                    <x-agro.card>
                        <x-slot:header>
                            <div class="flex items-center gap-3">
                                <flux:icon icon="hashtag" class="size-5 text-blue-600" />
                                <div>
                                    <p class="font-bold text-zinc-900">{{ __('Prefijos de numeración del cuaderno') }}</p>
                                    <p class="text-sm text-zinc-500">{{ __('Solo mayúsculas, números, guión y guión bajo') }}</p>
                                </div>
                            </div>
                        </x-slot:header>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <flux:field>
                                <flux:label>{{ __('Prefijo actividades') }}</flux:label>
                                <flux:input wire:model="document_prefix_activity" type="text" placeholder="ACT" maxlength="20" class="uppercase" />
                                <flux:description>{{ __('Ej: ACT → ACT-2025-001') }}</flux:description>
                                <flux:error name="document_prefix_activity" />
                            </flux:field>

                            <flux:field>
                                <flux:label>{{ __('Prefijo vendimias') }}</flux:label>
                                <flux:input wire:model="document_prefix_harvest" type="text" placeholder="VND" maxlength="20" class="uppercase" />
                                <flux:description>{{ __('Ej: VND → VND-2025-001') }}</flux:description>
                                <flux:error name="document_prefix_harvest" />
                            </flux:field>
                        </div>
                    </x-agro.card>

                    <x-agro.card>
                        <x-slot:header>
                            <div class="flex items-center gap-3">
                                <flux:icon icon="document-text" class="size-5 text-zinc-600" />
                                <div>
                                    <p class="font-bold text-zinc-900">{{ __('Texto legal del cuaderno (PDF)') }}</p>
                                    <p class="text-sm text-zinc-500">{{ __('Aparece al pie de cada PDF generado del cuaderno de campo') }}</p>
                                </div>
                            </div>
                        </x-slot:header>

                        <flux:field>
                            <flux:label>{{ __('Texto legal') }}</flux:label>
                            <flux:textarea wire:model="legal_text_fieldbook" rows="4"
                                :placeholder="__('Ej: El presente cuaderno de campo ha sido generado por [Nombre], NIF [00000000A], conforme al RD 1337/2021...')" />
                            <flux:description>{{ __('Máx. 2000 caracteres. Deja vacío para no incluir pie legal.') }}</flux:description>
                            <flux:error name="legal_text_fieldbook" />
                        </flux:field>
                    </x-agro.card>

                    {{-- Notification Preferences (granular) --}}
                    <livewire:notification-preferences />

                    <div class="flex justify-end">
                        <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="saveFieldbook">
                            {{ __('Guardar Configuración del Cuaderno') }}
                        </flux:button>
                    </div>
                </form>
            @endif

            {{-- SIGNATURE TAB --}}
            @if($currentTab === 'signature')
                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-bold text-zinc-900 mb-2">{{ __('Configuración de Firma Digital') }}</h3>
                        <p class="text-sm text-zinc-600">{{ __('Gestiona la configuración y seguridad de tu firma electrónica') }}</p>
                    </div>

                    <flux:callout variant="info" icon="information-circle">
                        <flux:callout.heading>{{ __('¿Qué es la Firma Digital?') }}</flux:callout.heading>
                        <flux:callout.text>
                            {{ __('La firma digital en Agro365 utiliza una contraseña específica para firmar documentos (diferente a tu contraseña de usuario) para generar un hash SHA-256 único que garantiza la autenticidad e integridad de cada informe oficial. Esta firma es legalmente válida y cumple con los requisitos normativos para documentación agrícola.') }}
                        </flux:callout.text>
                    </flux:callout>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-agro.card>
                            <x-slot:header>
                                <div class="flex items-center gap-2">
                                    <flux:icon icon="check-circle" class="size-5 text-agro-600" />
                                    <span class="font-semibold text-zinc-900">{{ __('Ventajas') }}</span>
                                </div>
                            </x-slot:header>
                            <ul class="text-sm text-zinc-600 space-y-1">
                                <li>✓ {{ __('Autenticidad garantizada') }}</li>
                                <li>✓ {{ __('Integridad del documento') }}</li>
                                <li>✓ {{ __('No repudio (no puedes negar la firma)') }}</li>
                                <li>✓ {{ __('Cumplimiento normativo') }}</li>
                            </ul>
                        </x-agro.card>
                        <x-agro.card>
                            <x-slot:header>
                                <div class="flex items-center gap-2">
                                    <flux:icon icon="shield-check" class="size-5 text-agro-600" />
                                    <span class="font-semibold text-zinc-900">{{ __('Seguridad') }}</span>
                                </div>
                            </x-slot:header>
                            <ul class="text-sm text-zinc-600 space-y-1">
                                <li>✓ Hash SHA-256 {{ __('único') }}</li>
                                <li>✓ {{ __('Contraseña específica para firmar (separada de tu contraseña de usuario)') }}</li>
                                <li>✓ {{ __('Registro de IP y dispositivo') }}</li>
                                <li>✓ {{ __('Código QR de verificación') }}</li>
                            </ul>
                        </x-agro.card>
                    </div>

                    {{-- Configuración de Firma --}}
                    <x-agro.card>
                        <x-slot:header>
                            <div class="flex items-center gap-2">
                                <flux:icon icon="cog-6-tooth" class="size-5 text-zinc-600" />
                                <span class="font-semibold text-zinc-900">{{ $hasDigitalSignature ? __('Actualizar') : __('Crear') }} {{ __('Contraseña de Firma Digital') }}</span>
                            </div>
                        </x-slot:header>

                        @if($hasDigitalSignature)
                            <flux:callout variant="success" icon="check-circle" class="mb-4">
                                <flux:callout.text>
                                    <strong>{{ __('Ya tienes una contraseña de firma digital configurada.') }}</strong>
                                    {{ __('Puedes actualizarla cuando lo desees. Los documentos ya firmados permanecerán válidos.') }}
                                </flux:callout.text>
                            </flux:callout>
                        @else
                            <flux:callout variant="warning" icon="exclamation-triangle" class="mb-4">
                                <flux:callout.text>
                                    <strong>{{ __('No tienes una contraseña de firma digital configurada.') }}</strong>
                                    {{ __('Crea una contraseña específica para firmar tus informes oficiales. Esta contraseña es diferente a tu contraseña de usuario.') }}
                                </flux:callout.text>
                            </flux:callout>
                        @endif

                        <form wire:submit="saveDigitalSignature" class="space-y-4" x-data="{
                            showPassword: false,
                            showPasswordConfirmation: false,
                            password: '',
                            passwordStrength: 0,
                            calculateStrength() {
                                let strength = 0;
                                const pwd = this.password;
                                if (pwd.length >= 8) strength += 25;
                                if (/[a-z]/.test(pwd)) strength += 25;
                                if (/[A-Z]/.test(pwd)) strength += 25;
                                if (/[0-9]/.test(pwd)) strength += 25;
                                this.passwordStrength = strength;
                            }
                        }">
                            {{-- Contraseña de firma --}}
                            <div>
                                <flux:label>{{ $hasDigitalSignature ? __('Nueva') : __('Crear') }} {{ __('Contraseña de Firma Digital') }}</flux:label>
                                <div class="relative mt-1">
                                    <input
                                        wire:model="signaturePassword"
                                        x-bind:type="showPassword ? 'text' : 'password'"
                                        x-model="password"
                                        x-on:input="calculateStrength()"
                                        class="w-full px-4 py-2.5 pr-12 border border-zinc-300 rounded-lg text-sm focus:ring-2 focus:ring-agro-500 focus:border-agro-500"
                                        :placeholder="'{{ __('Mínimo 8 caracteres, 1 mayúscula, 1 minúscula y 1 número') }}'"
                                    >
                                    <button
                                        type="button"
                                        x-on:click="showPassword = !showPassword"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-zinc-600"
                                        tabindex="-1"
                                    >
                                        <flux:icon x-show="!showPassword" icon="eye" class="size-5" />
                                        <flux:icon x-show="showPassword" icon="eye-slash" class="size-5" style="display:none" />
                                    </button>
                                </div>

                                {{-- Indicador de fortaleza --}}
                                <div class="mt-2" x-show="password.length > 0">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 h-2 bg-zinc-200 rounded-full overflow-hidden">
                                            <div
                                                class="h-full transition-all duration-300"
                                                x-bind:class="{
                                                    'bg-red-500': passwordStrength < 50,
                                                    'bg-yellow-500': passwordStrength >= 50 && passwordStrength < 75,
                                                    'bg-green-500': passwordStrength >= 75
                                                }"
                                                x-bind:style="'width: ' + passwordStrength + '%'"
                                            ></div>
                                        </div>
                                        <span
                                            class="text-xs font-semibold"
                                            x-bind:class="{
                                                'text-red-600': passwordStrength < 50,
                                                'text-yellow-600': passwordStrength >= 50 && passwordStrength < 75,
                                                'text-green-600': passwordStrength >= 75
                                            }"
                                            x-text="passwordStrength < 50 ? '{{ __('Débil') }}' : (passwordStrength < 75 ? '{{ __('Media') }}' : '{{ __('Fuerte') }}')"
                                        ></span>
                                    </div>
                                </div>

                                @error('signaturePassword')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </div>

                            {{-- Confirmar contraseña --}}
                            <div>
                                <flux:label>{{ __('Confirmar Contraseña') }}</flux:label>
                                <div class="relative mt-1">
                                    <input
                                        wire:model="signaturePassword_confirmation"
                                        x-bind:type="showPasswordConfirmation ? 'text' : 'password'"
                                        class="w-full px-4 py-2.5 pr-12 border border-zinc-300 rounded-lg text-sm focus:ring-2 focus:ring-agro-500 focus:border-agro-500"
                                        :placeholder="'{{ __('Repite la contraseña') }}'"
                                    >
                                    <button
                                        type="button"
                                        x-on:click="showPasswordConfirmation = !showPasswordConfirmation"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-zinc-600"
                                        tabindex="-1"
                                    >
                                        <flux:icon x-show="!showPasswordConfirmation" icon="eye" class="size-5" />
                                        <flux:icon x-show="showPasswordConfirmation" icon="eye-slash" class="size-5" style="display:none" />
                                    </button>
                                </div>
                            </div>

                            <flux:callout variant="info" icon="information-circle">
                                <flux:callout.text>
                                    <strong>{{ __('Importante') }}:</strong> {{ __('Esta contraseña es diferente a tu contraseña de usuario. Se usa exclusivamente para firmar documentos oficiales. Guárdala en un lugar seguro.') }}
                                </flux:callout.text>
                            </flux:callout>

                            <div class="flex items-center justify-between gap-3">
                                @if($hasDigitalSignature)
                                    <button
                                        type="button"
                                        @click="$dispatch('open-modal', 'reset-signature-password')"
                                        class="text-sm text-red-600 hover:text-red-700 font-semibold underline"
                                    >
                                        {{ __('Olvidé mi contraseña de firma') }}
                                    </button>
                                @else
                                    <div></div>
                                @endif

                                <flux:button
                                    type="submit"
                                    variant="primary"
                                    icon="lock-closed"
                                    wire:loading.attr="disabled"
                                    wire:target="saveDigitalSignature"
                                >
                                    <span wire:loading.remove wire:target="saveDigitalSignature">
                                        {{ $hasDigitalSignature ? __('Actualizar') : __('Crear') }} {{ __('Contraseña de Firma') }}
                                    </span>
                                    <span wire:loading wire:target="saveDigitalSignature">{{ __('Guardando...') }}</span>
                                </flux:button>
                            </div>
                        </form>
                    </x-agro.card>

                    {{-- Modal: Resetear contraseña olvidada --}}
                    <x-agro.modal name="reset-signature-password" max-width="md">
                        <div class="p-6 space-y-4">
                            <h3 class="text-xl font-bold text-zinc-900">{{ __('Resetear Contraseña de Firma') }}</h3>
                            <p class="text-sm text-zinc-600">
                                {{ __('Para resetear tu contraseña de firma olvidada, verifica tu identidad ingresando tu contraseña de login.') }}
                            </p>

                            <div>
                                <flux:label>{{ __('Contraseña de Login (tu contraseña de usuario)') }}</flux:label>
                                <flux:input
                                    type="password"
                                    wire:model="loginPasswordForReset"
                                    wire:keydown.enter="resetForgottenSignaturePassword"
                                    :placeholder="__('Ingresa tu contraseña de login')"
                                    autofocus
                                    class="mt-1"
                                />
                                @error('loginPasswordForReset')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </div>

                            <flux:callout variant="warning" icon="exclamation-triangle">
                                <flux:callout.text>
                                    <strong>{{ __('Advertencia') }}:</strong> {{ __('Esto eliminará tu contraseña de firma actual. Después podrás crear una nueva.') }}
                                </flux:callout.text>
                            </flux:callout>

                            <div class="flex justify-end gap-3">
                                <flux:button
                                    variant="ghost"
                                    @click="$dispatch('close-modal', 'reset-signature-password')"
                                >
                                    {{ __('Cancelar') }}
                                </flux:button>
                                <flux:button
                                    variant="danger"
                                    wire:click="resetForgottenSignaturePassword"
                                    wire:loading.attr="disabled"
                                    wire:target="resetForgottenSignaturePassword"
                                >
                                    <span wire:loading.remove wire:target="resetForgottenSignaturePassword">{{ __('Resetear Contraseña') }}</span>
                                    <span wire:loading wire:target="resetForgottenSignaturePassword">{{ __('Verificando...') }}</span>
                                </flux:button>
                            </div>
                        </div>
                    </x-agro.modal>

                    {{-- Información de seguridad --}}
                    <flux:callout variant="warning" icon="exclamation-triangle">
                        <flux:callout.heading>{{ __('Seguridad de tu Firma') }}</flux:callout.heading>
                        <flux:callout.text>
                            <ul class="space-y-1 mt-1">
                                <li>• {{ __('Tu contraseña de firma digital se verifica en cada firma para garantizar la autenticidad') }}</li>
                                <li>• {{ __('Cada documento genera un hash SHA-256 único e irreversible') }}</li>
                                <li>• {{ __('Registramos la IP y dispositivo de cada firma para auditoría') }}</li>
                                <li>• {{ __('Los documentos firmados no pueden modificarse sin invalidar la firma') }}</li>
                                <li>• {{ __('Cada informe incluye un código QR único para verificación pública') }}</li>
                            </ul>
                            <p class="mt-3 text-xs">
                                <strong>{{ __('Tip:') }}</strong> {{ __('La contraseña de firma digital es diferente a tu contraseña de usuario. Puedes cambiarla cuando quieras sin afectar tu acceso a la plataforma. Para ver tus estadísticas y actividad reciente de firmas, visita la sección de') }}
                                <a href="{{ roleRoute('viticulturist.official-reports.index') }}" class="underline font-semibold">{{ __('Informes Oficiales') }}</a>.
                            </p>
                        </flux:callout.text>
                    </flux:callout>
                </div>
            @endif
        </div>
    </x-agro.card>
</div>
