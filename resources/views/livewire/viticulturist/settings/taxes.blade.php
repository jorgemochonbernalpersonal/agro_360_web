<div class="space-y-6 animate-fade-in">
    @php
        $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>';
    @endphp

    <x-agro.page-header
        :icon="$icon"
        title="Configuración de Impuestos"
        description="Activa los impuestos que usas y define cuál se aplica por defecto en tus facturas"
        icon-color="from-agro-500 to-agro-700"
    />

    {{-- Resumen del estado actual --}}
    @if(count($enabledTaxIds) === 0)
        <flux:callout variant="warning" icon="exclamation-triangle">
            <flux:callout.heading>Sin impuesto configurado</flux:callout.heading>
            <flux:callout.text>
                Activa al menos un impuesto para poder emitir facturas correctamente.
            </flux:callout.text>
        </flux:callout>
    @else
        @php $defaultTax = $taxes->firstWhere('id', $defaultTaxId); @endphp
        @if($defaultTax)
            <flux:callout variant="success" icon="check-circle">
                <flux:callout.heading>Impuesto por defecto activo</flux:callout.heading>
                <flux:callout.text>
                    <strong>{{ $defaultTax->name }}</strong> ({{ $defaultTax->formatted_rate }})
                    se aplicará automáticamente en los nuevos ítems de factura.
                    Tienes {{ count($enabledTaxIds) }} {{ count($enabledTaxIds) === 1 ? 'impuesto habilitado' : 'impuestos habilitados' }}.
                </flux:callout.text>
            </flux:callout>
        @endif
    @endif

    {{-- Grid de impuestos --}}
    <x-agro.form-card>
        <x-slot:header>
            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-amber-100 to-amber-200 flex items-center justify-center">
                <flux:icon icon="receipt-percent" class="size-5 text-amber-600" />
            </div>
            <span>Impuestos disponibles</span>
        </x-slot:header>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($taxes as $tax)
                @php
                    $isEnabled = in_array($tax->id, $enabledTaxIds);
                    $isDefault = $defaultTaxId === $tax->id;
                @endphp

                <div @class([
                    'relative rounded-xl border-2 p-5 transition-all duration-200',
                    'border-agro-500 bg-agro-50 shadow-md'   => $isDefault,
                    'border-zinc-300 bg-white hover:border-zinc-400 hover:shadow-sm' => $isEnabled && !$isDefault,
                    'border-zinc-200 bg-zinc-50 opacity-60'  => !$isEnabled,
                ])>
                    {{-- Badge defecto --}}
                    @if($isDefault)
                        <span class="absolute top-3 right-3 inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-agro-500 text-white">
                            <flux:icon icon="check-circle" variant="solid" class="size-3" />
                            Defecto
                        </span>
                    @endif

                    {{-- Nombre y tasa --}}
                    <div class="mb-4 pr-16">
                        <p class="text-xl font-extrabold text-zinc-900">{{ $tax->formatted_rate }}</p>
                        <p class="text-sm font-semibold text-zinc-700">{{ $tax->name }}</p>
                        @if($tax->region)
                            <p class="text-xs text-zinc-500 mt-0.5">{{ $tax->region }}</p>
                        @endif
                    </div>

                    @if($tax->description)
                        <p class="text-xs text-zinc-500 mb-4">{{ $tax->description }}</p>
                    @endif

                    {{-- Acciones --}}
                    <div class="flex items-center gap-2 pt-3 border-t border-zinc-100">
                        {{-- Toggle activar/desactivar --}}
                        <button
                            wire:click="toggleTax({{ $tax->id }})"
                            wire:loading.attr="disabled"
                            @class([
                                'flex-1 text-xs font-medium px-3 py-1.5 rounded-lg border transition-colors',
                                'bg-red-50 text-red-700 border-red-200 hover:bg-red-100' => $isEnabled,
                                'bg-zinc-100 text-zinc-700 border-zinc-200 hover:bg-zinc-200' => !$isEnabled,
                            ])
                        >
                            {{ $isEnabled ? 'Desactivar' : 'Activar' }}
                        </button>

                        {{-- Botón defecto (solo si está habilitado y no es ya el defecto) --}}
                        @if($isEnabled && !$isDefault)
                            <button
                                wire:click="setDefault({{ $tax->id }})"
                                wire:loading.attr="disabled"
                                class="flex-1 text-xs font-medium px-3 py-1.5 rounded-lg border bg-agro-50 text-agro-700 border-agro-200 hover:bg-agro-100 transition-colors"
                            >
                                Usar por defecto
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        @if($taxes->isEmpty())
            <x-agro.empty-state
                icon="document-text"
                title="No hay impuestos configurados en el sistema"
                description="Contacta con el administrador para que añada los tipos de impuesto disponibles."
            />
        @endif
    </x-agro.form-card>

    {{-- Información --}}
    <flux:callout variant="info" icon="information-circle">
        <flux:callout.heading>¿Cómo funciona?</flux:callout.heading>
        <flux:callout.text>
            <ul class="mt-1 space-y-1 text-sm">
                <li><strong>Activa</strong> los impuestos que necesitas usar en tus facturas.</li>
                <li><strong>Defecto</strong>: el impuesto marcado se pre-selecciona automáticamente en cada nuevo ítem de factura. Puedes cambiarlo manualmente en la factura.</li>
                <li>Puedes tener varios activos — por ejemplo IVA 21% para servicios e IVA 10% para productos alimenticios.</li>
            </ul>
        </flux:callout.text>
    </flux:callout>

</div>
