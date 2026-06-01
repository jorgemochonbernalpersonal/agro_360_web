<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="VeriFactu"
        :description="__('Registro de facturas en el sistema de la Agencia Tributaria. Verifica y envía tus facturas a la AEAT.')"
    />

    {{-- NIF warning ─────────────────────────────────────────────────────────── --}}
    @if(empty(auth()->user()->dni))
        <flux:callout variant="warning" icon="exclamation-triangle">
            <flux:callout.heading>{{ __('NIF/CIF no configurado') }}</flux:callout.heading>
            <flux:callout.text>
                {{ __('Sin tu NIF/CIF no es posible generar el XML de Verifactu ni enviarlo a la AEAT.') }}
                {{ __('Ve a') }} <a href="{{ route(auth()->user()->role === 'viticulturist' ? 'viticulturist.settings' : 'winery.settings', ['tab' => 'fiscal']) }}" class="font-semibold underline">{{ __('Configuración → Datos Fiscales') }}</a> {{ __('y añade tu NIF antes de verificar facturas.') }}
            </flux:callout.text>
        </flux:callout>
    @endif

    {{-- Stats ─────────────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <button wire:click="$set('tab','pending')"
            class="text-left bg-white border-2 rounded-xl p-4 shadow-sm transition-all
                {{ $tab === 'pending' ? 'border-amber-400 ring-1 ring-amber-300' : 'border-zinc-200 hover:border-amber-300' }}">
            <p class="text-xs font-semibold text-amber-500 uppercase tracking-wide mb-1">{{ __('Pendientes') }}</p>
            <p class="text-3xl font-bold text-zinc-900">{{ $stats['pending'] }}</p>
            <p class="text-xs text-zinc-400 mt-0.5">{{ __('Facturas por verificar') }}</p>
        </button>

        <button wire:click="$set('tab','sent')"
            class="text-left bg-white border-2 rounded-xl p-4 shadow-sm transition-all
                {{ $tab === 'sent' ? 'border-green-400 ring-1 ring-green-300' : 'border-zinc-200 hover:border-green-300' }}">
            <p class="text-xs font-semibold text-green-600 uppercase tracking-wide mb-1">{{ __('Verificadas') }}</p>
            <p class="text-3xl font-bold text-zinc-900">{{ $stats['sent'] }}</p>
            <p class="text-xs text-zinc-400 mt-0.5">{{ __('Aceptadas por la AEAT') }}</p>
        </button>

        <button wire:click="$set('tab','errors')"
            class="text-left bg-white border-2 rounded-xl p-4 shadow-sm transition-all
                {{ $tab === 'errors' ? 'border-red-400 ring-1 ring-red-300' : 'border-zinc-200 hover:border-red-300' }}">
            <p class="text-xs font-semibold text-red-500 uppercase tracking-wide mb-1">{{ __('Errores') }}</p>
            <p class="text-3xl font-bold text-zinc-900">{{ $stats['errors'] }}</p>
            <p class="text-xs text-zinc-400 mt-0.5">{{ __('Requieren atención') }}</p>
        </button>
    </div>

    {{-- Tabs ────────────────────────────────────────────────────────────────── --}}
    <div class="flex items-center gap-1 bg-white border border-zinc-200 rounded-xl p-1 w-fit shadow-sm">
        @foreach([
            'pending' => ['label' => 'Pendientes', 'icon' => 'clock'],
            'sent'    => ['label' => 'Verificadas', 'icon' => 'check-circle'],
            'errors'  => ['label' => 'Errores',     'icon' => 'exclamation-circle'],
            'config'  => ['label' => 'Config',      'icon' => 'cog-6-tooth'],
        ] as $key => $t)
            <button wire:click="$set('tab','{{ $key }}')"
                class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors
                    {{ $tab === $key
                        ? 'bg-agro-500 text-white shadow-sm'
                        : 'text-zinc-500 hover:text-zinc-800 hover:bg-zinc-50' }}">
                <flux:icon icon="{{ $t['icon'] }}" class="size-4" />
                {{ $t['label'] }}
            </button>
        @endforeach
    </div>

    {{-- Loading skeleton ──────────────────────────────────────────────────── --}}
    <div wire:loading wire:target="tab, sendInvoice, sendAll, retryInvoice, cancelInvoice, excludeInvoice, excludeAll">
        <div class="animate-pulse space-y-3">
            @for($i = 0; $i < 4; $i++)
                <div class="h-16 bg-zinc-100 rounded-xl"></div>
            @endfor
        </div>
    </div>

    {{-- ── TAB: PENDIENTES ──────────────────────────────────────────────────── --}}
    @if($tab === 'pending')
    <div wire:loading.remove wire:target="tab, sendInvoice, sendAll">
        @if($pending && $pending->count() > 0)
            <x-agro.card>
                <x-slot:header>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center">
                                <flux:icon icon="clock" class="size-4 text-amber-600" />
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-zinc-800">{{ __('Facturas pendientes de verificar') }}</h3>
                                <p class="text-xs text-zinc-400">{{ $pending->total() }} facturas emitidas sin verificar en AEAT</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <flux:button wire:click="excludeAll"
                                wire:loading.attr="disabled"
                                wire:confirm="{{ __('¿Excluir TODAS las facturas pendientes de Verifactu? Usa esto para facturas anteriores a la activación de Verifactu.') }}"
                                variant="ghost" size="sm" icon="x-circle">
                                Excluir todas
                            </flux:button>
                            <flux:button wire:click="sendAll"
                                wire:loading.attr="disabled"
                                wire:confirm="{{ __('¿Verificar todas las facturas pendientes en la AEAT?') }}"
                                variant="primary" icon="paper-airplane" size="sm">
                                Verificar todas
                            </flux:button>
                        </div>
                    </div>
                </x-slot:header>

                <div class="divide-y divide-zinc-100">
                    @foreach($pending as $invoice)
                        @php
                            $clientName = $invoice->billing_company_name
                                ?? trim(($invoice->billing_first_name ?? '') . ' ' . ($invoice->billing_last_name ?? ''))
                                ?: ($invoice->client?->company_name ?? $invoice->client?->full_name ?? '—');
                        @endphp
                        <div class="flex items-center gap-4 py-3 px-1 hover:bg-zinc-50 rounded-lg transition-colors"
                            wire:key="pending-{{ $invoice->id }}">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold text-sm text-zinc-900">{{ $invoice->invoice_number }}</span>
                                    @if($invoice->corrective)
                                        <flux:badge color="orange" size="sm">{{ __('Rectificativa') }}</flux:badge>
                                    @endif
                                </div>
                                <p class="text-xs text-zinc-500 truncate mt-0.5">
                                    {{ $clientName }} &middot; {{ $invoice->invoice_date?->format('d/m/Y') }}
                                </p>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="text-sm font-bold text-zinc-900">{{ number_format($invoice->total_amount, 2, ',', '.') }} €</p>
                                <p class="text-xs text-zinc-400">IVA {{ number_format($invoice->tax_rate, 0) }}%</p>
                            </div>
                            <div class="flex items-center gap-1">
                                <flux:button wire:click="excludeInvoice({{ $invoice->id }})"
                                    wire:loading.attr="disabled"
                                    wire:confirm="{{ __('¿Excluir esta factura de Verifactu?') }}"
                                    size="sm" variant="ghost" icon="x-mark" title="{{ __('Excluir de Verifactu') }}">
                                </flux:button>
                                <flux:button wire:click="sendInvoice({{ $invoice->id }})"
                                    wire:loading.attr="disabled"
                                    size="sm" variant="primary" icon="check-circle">
                                    Verificar
                                </flux:button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <x-agro.pagination :paginator="$pending" />
            </x-agro.card>
        @else
            <x-agro.empty-state
                icon="check-circle"
                title="{{ __('Sin facturas pendientes') }}"
                :description="__('Todas tus facturas están verificadas en la AEAT.')"
            />
        @endif
    </div>
    @endif

    {{-- ── TAB: VERIFICADAS ─────────────────────────────────────────────────── --}}
    @if($tab === 'sent')
    <div wire:loading.remove wire:target="tab, cancelInvoice">
        @if($sent && $sent->count() > 0)
            <x-agro.card>
                <x-slot:header>
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                            <flux:icon icon="check-circle" class="size-4 text-green-600" />
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-zinc-800">{{ __('Facturas verificadas') }}</h3>
                            <p class="text-xs text-zinc-400">{{ $sent->total() }} facturas aceptadas por la AEAT</p>
                        </div>
                    </div>
                </x-slot:header>

                <div class="divide-y divide-zinc-100">
                    @foreach($sent as $invoice)
                        @php
                            $clientName = $invoice->billing_company_name
                                ?? trim(($invoice->billing_first_name ?? '') . ' ' . ($invoice->billing_last_name ?? ''))
                                ?: ($invoice->client?->company_name ?? $invoice->client?->full_name ?? '—');
                        @endphp
                        <div class="flex items-center gap-4 py-3 px-1 hover:bg-zinc-50 rounded-lg transition-colors"
                            wire:key="sent-{{ $invoice->id }}">
                            <div class="w-2 h-2 rounded-full bg-green-400 shrink-0"></div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold text-sm text-zinc-900">{{ $invoice->invoice_number }}</span>
                                    <flux:badge color="green" size="sm">{{ __('Verificada') }}</flux:badge>
                                </div>
                                <p class="text-xs text-zinc-500 truncate mt-0.5">
                                    {{ $clientName }} &middot; {{ $invoice->invoice_date?->format('d/m/Y') }}
                                </p>
                            </div>
                            <div class="text-right shrink-0 hidden md:block">
                                <p class="text-xs font-mono text-blue-600 bg-blue-50 px-2 py-0.5 rounded border border-blue-200">
                                    {{ $invoice->sif_uuid ?? '—' }}
                                </p>
                                <p class="text-xs text-zinc-400 mt-0.5">CSV &middot; {{ $invoice->sif_sent_at?->format('d/m/Y H:i') }}</p>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="text-sm font-bold text-zinc-900">{{ number_format($invoice->total_amount, 2, ',', '.') }} €</p>
                            </div>
                            <flux:button wire:click="cancelInvoice({{ $invoice->id }})"
                                wire:loading.attr="disabled"
                                wire:confirm="{{ __('¿Anular esta factura en Verifactu? Volverá a estado pendiente.') }}"
                                size="sm" variant="ghost" icon="x-circle" class="text-zinc-400 hover:text-red-500">
                            </flux:button>
                        </div>
                    @endforeach
                </div>

                <x-agro.pagination :paginator="$sent" />
            </x-agro.card>
        @else
            <x-agro.empty-state
                icon="check-circle"
                title="{{ __('Sin facturas verificadas') }}"
                :description="__('Aún no has verificado ninguna factura en la AEAT.')"
            />
        @endif
    </div>
    @endif

    {{-- ── TAB: ERRORES ─────────────────────────────────────────────────────── --}}
    @if($tab === 'errors')
    <div wire:loading.remove wire:target="tab, retryInvoice">
        @if($errors && $errors->count() > 0)
            <x-agro.card>
                <x-slot:header>
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                            <flux:icon icon="exclamation-circle" class="size-4 text-red-500" />
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-zinc-800">{{ __('Facturas con error') }}</h3>
                            <p class="text-xs text-zinc-400">{{ $errors->total() }} facturas con error en el envío a AEAT</p>
                        </div>
                    </div>
                </x-slot:header>

                <div class="divide-y divide-zinc-100">
                    @foreach($errors as $invoice)
                        @php
                            $lastError = $invoice->sifRecords->first()?->error_message ?? 'Error desconocido';
                            $clientName = $invoice->billing_company_name
                                ?? trim(($invoice->billing_first_name ?? '') . ' ' . ($invoice->billing_last_name ?? ''))
                                ?: ($invoice->client?->company_name ?? $invoice->client?->full_name ?? '—');
                        @endphp
                        <div class="flex items-start gap-4 py-3 px-1 hover:bg-zinc-50 rounded-lg transition-colors"
                            wire:key="error-{{ $invoice->id }}">
                            <div class="w-2 h-2 rounded-full bg-red-400 shrink-0 mt-2"></div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold text-sm text-zinc-900">{{ $invoice->invoice_number }}</span>
                                    <flux:badge color="red" size="sm">{{ __('Error') }}</flux:badge>
                                </div>
                                <p class="text-xs text-zinc-500 truncate">{{ $clientName }} &middot; {{ $invoice->invoice_date?->format('d/m/Y') }}</p>
                                <p class="text-xs text-red-500 mt-1 line-clamp-2">{{ $lastError }}</p>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="text-sm font-bold text-zinc-900">{{ number_format($invoice->total_amount, 2, ',', '.') }} €</p>
                            </div>
                            <flux:button wire:click="retryInvoice({{ $invoice->id }})"
                                wire:loading.attr="disabled"
                                size="sm" variant="primary" icon="arrow-path">
                                {{ __('Reintentar') }}
                            </flux:button>
                        </div>
                    @endforeach
                </div>

                <x-agro.pagination :paginator="$errors" />
            </x-agro.card>
        @else
            <x-agro.empty-state
                icon="check-circle"
                title="{{ __('Sin errores') }}"
                :description="__('No hay facturas con errores de verificación.')"
            />
        @endif
    </div>
    @endif

    {{-- ── TAB: CONFIGURACIÓN ───────────────────────────────────────────────── --}}
    @if($tab === 'config' && $config)
    <div wire:loading.remove wire:target="tab">
        <x-agro.card>
            <x-slot:header>
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-zinc-100 rounded-lg flex items-center justify-center">
                        <flux:icon icon="cog-6-tooth" class="size-4 text-zinc-500" />
                    </div>
                    <h3 class="text-sm font-semibold text-zinc-800">{{ __('Configuración Verifactu') }}</h3>
                </div>
            </x-slot:header>

            <div class="space-y-5">
                {{-- Entorno --}}
                <div class="flex items-center justify-between p-4 bg-zinc-50 rounded-xl border border-zinc-200">
                    <div>
                        <p class="text-sm font-semibold text-zinc-800">{{ __('Entorno') }}</p>
                        <p class="text-xs text-zinc-500 mt-0.5">{{ __('Indica si las facturas se envían al entorno real de AEAT o al de pruebas.') }}</p>
                    </div>
                    @if($config['is_production'])
                        <flux:badge color="green">{{ __('Producción') }}</flux:badge>
                    @else
                        <flux:badge color="yellow">{{ __('Pruebas (test)') }}</flux:badge>
                    @endif
                </div>

                {{-- Certificado --}}
                <div class="flex items-center justify-between p-4 bg-zinc-50 rounded-xl border border-zinc-200">
                    <div>
                        <p class="text-sm font-semibold text-zinc-800">{{ __('Certificado digital') }}</p>
                        <p class="text-xs text-zinc-500 mt-0.5">
                            @if($config['cert_configured'])
                                Certificado cargado en: <code class="text-xs bg-white px-1 rounded border border-zinc-200">{{ $config['cert_path'] }}</code>
                            @else
                                No hay certificado configurado. En modo pruebas se envía sin firmar.
                            @endif
                        </p>
                    </div>
                    @if($config['cert_configured'])
                        <flux:badge color="green">{{ __('Configurado') }}</flux:badge>
                    @else
                        <flux:badge color="zinc">{{ __('Sin certificado') }}</flux:badge>
                    @endif
                </div>

                {{-- Endpoint --}}
                <div class="p-4 bg-zinc-50 rounded-xl border border-zinc-200">
                    <p class="text-sm font-semibold text-zinc-800 mb-1">{{ __('Endpoint AEAT') }}</p>
                    <code class="text-xs text-zinc-600 break-all">{{ $config['endpoint'] ?? 'No configurado' }}</code>
                </div>

                {{-- NIF check --}}
                <div class="flex items-center justify-between p-4 bg-amber-50 rounded-xl border border-amber-200">
                    <div>
                        <p class="text-sm font-semibold text-amber-800">{{ __('NIF/DNI del emisor') }}</p>
                        <p class="text-xs text-amber-600 mt-0.5">{{ __('Tu NIF es el campo obligatorio para generar facturas Verifactu.
                            Se configura en tu perfil de usuario.') }}</p>
                    </div>
                    @php $nif = auth()->user()->dni ?? null; @endphp
                    @if($nif)
                        <flux:badge color="green">{{ mask_nif($nif) }}</flux:badge>
                    @else
                        <div class="flex items-center gap-2">
                            <flux:badge color="red">{{ __('No configurado') }}</flux:badge>
                            <flux:button href="{{ roleRoute('settings') }}" wire:navigate size="sm" variant="ghost" icon="arrow-top-right-on-square">
                                Configurar
                            </flux:button>
                        </div>
                    @endif
                </div>

                <div class="text-xs text-zinc-400 flex items-start gap-2 bg-blue-50 border border-blue-200 rounded-xl p-3">
                    <flux:icon icon="information-circle" class="size-4 text-blue-400 shrink-0 mt-0.5" />
                    <span>
                        Para cambiar el entorno o el certificado, actualiza las variables
                        <code>SIF_ENVIRONMENT</code>, <code>SIF_CERT_PATH</code>, <code>SIF_CERT_PASSWORD</code>
                        y <code>SIF_AEAT_ENDPOINT</code> en el fichero <code>.env</code>.
                    </span>
                </div>
            </div>
        </x-agro.card>
    </div>
    @endif

</div>
