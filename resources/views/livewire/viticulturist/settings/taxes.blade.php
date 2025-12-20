<div class="space-y-6 animate-fade-in">
    @php
        $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>';
    @endphp
    <x-page-header
        :icon="$icon"
        title="Configuración de Impuestos"
        description="Selecciona el impuesto que se aplicará por defecto en tus facturas"
        icon-color="from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]"
    />

    <div class="glass-card rounded-xl p-8">
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">
                Impuesto activo
            </h3>
            <p class="text-sm text-gray-600">
                Solo puedes tener un tipo de impuesto activo. Selecciona el que corresponda según tu ubicación.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($taxes as $tax)
                @php
                    $isActive = $activeTaxId == $tax->id;
                    $colors = [
                        'EXENTO' => ['border' => 'border-gray-300', 'bg' => 'bg-gray-50', 'text' => 'text-gray-900', 'active-border' => 'border-gray-600', 'active-bg' => 'bg-gray-100'],
                        'IVA' => ['border' => 'border-blue-300', 'bg' => 'bg-blue-50', 'text' => 'text-blue-900', 'active-border' => 'border-blue-600', 'active-bg' => 'bg-blue-100'],
                        'IGIC' => ['border' => 'border-green-300', 'bg' => 'bg-green-50', 'text' => 'text-green-900', 'active-border' => 'border-green-600', 'active-bg' => 'bg-green-100'],
                    ];
                    $color = $colors[$tax->code] ?? $colors['EXENTO'];
                @endphp

                <button
                    wire:click="selectTax({{ $tax->id }})"
                    class="relative p-6 border-2 rounded-xl transition-all duration-200 hover:shadow-lg
                        {{ $isActive ? $color['active-border'] . ' ' . $color['active-bg'] . ' shadow-md' : $color['border'] . ' bg-white hover:' . $color['bg'] }}"
                >
                    {{-- Badge de selección --}}
                    @if($isActive)
                        <div class="absolute top-3 right-3">
                            <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    @endif

                    <div class="text-center">
                        {{-- Icono --}}
                        <div class="mb-4 flex justify-center">
                            @if($tax->code === 'EXENTO')
                                <svg class="w-16 h-16 {{ $color['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                </svg>
                            @elseif($tax->code === 'IVA')
                                <svg class="w-16 h-16 {{ $color['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            @else
                                <svg class="w-16 h-16 {{ $color['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            @endif
                        </div>

                        {{-- Nombre --}}
                        <h4 class="text-xl font-bold {{ $color['text'] }} mb-2">
                            {{ $tax->name }}
                        </h4>

                        {{-- Tasa --}}
                        <p class="text-3xl font-extrabold {{ $color['text'] }} mb-3">
                            {{ number_format($tax->rate, 0) }}%
                        </p>

                        {{-- Región --}}
                        <p class="text-sm text-gray-600 mb-3">
                            {{ $tax->region }}
                        </p>

                        {{-- Descripción --}}
                        <p class="text-xs text-gray-500">
                            {{ $tax->description }}
                        </p>

                        {{-- Estado --}}
                        @if($isActive)
                            <div class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-green-100 text-green-800 rounded-full text-sm font-semibold">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                Activo
                            </div>
                        @else
                            <div class="mt-4 text-sm text-gray-500">
                                Click para activar
                            </div>
                        @endif
                    </div>
                </button>
            @endforeach
        </div>

        {{-- Información adicional --}}
        <div class="mt-8 p-4 bg-blue-50 border-l-4 border-blue-500 rounded-r-lg">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <div class="flex-1">
                    <h4 class="text-sm font-semibold text-blue-900 mb-1">
                        ℹ️ Información importante
                    </h4>
                    <ul class="text-xs text-blue-800 space-y-1">
                        <li>• El impuesto seleccionado se aplicará automáticamente en todas tus facturas</li>
                        <li>• <strong>Exento</strong>: Sin impuestos (0%)</li>
                        <li>• <strong>IVA</strong>: Para España Peninsular y Baleares (21%)</li>
                        <li>• <strong>IGIC</strong>: Para Islas Canarias (7%)</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
