<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Verificación de Eco-regímenes"
        description="Comprueba si las prácticas de tu cuaderno de campo justifican los eco-regímenes declarados en la PAC."
    />

    {{-- Selector de año --}}
    <div class="flex items-center gap-3">
        <flux:label class="text-sm font-medium">Año:</flux:label>
        <flux:select wire:model.live="yearFilter" class="w-32">
            @foreach($availableYears as $y)
                <option value="{{ $y }}">{{ $y }}</option>
            @endforeach
        </flux:select>
        @if($declaration)
            <x-agro.status-badge :color="$declaration->statusColor()" :label="'Declaración ' . $declaration->statusLabel()" />
        @else
            <x-agro.status-badge color="zinc" label="Sin declaración para {{ $year }}" />
        @endif
    </div>

    @if(!$declaration)
        <flux:callout variant="warning" icon="exclamation-triangle">
            <flux:callout.heading>No hay declaración PAC para {{ $year }}</flux:callout.heading>
            <flux:callout.text>
                Crea una declaración PAC para poder verificar los eco-regímenes.
                <a href="{{ roleRoute('viticulturist.pac.declarations.create') }}" wire:navigate class="underline ml-1">Crear declaración →</a>
            </flux:callout.text>
        </flux:callout>
    @endif

    {{-- Cards por eco-régimen --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach($ecoSchemes as $key => $label)
            @php
                $declared = isset($declaredSchemes[$key]);
                $ev       = $evidence[$key] ?? ['status' => 'info', 'detail' => '—', 'count' => null];
                $status   = $ev['status'];
                $borderColor = match($status) {
                    'ok'      => 'border-green-300',
                    'warning' => 'border-amber-300',
                    'missing' => 'border-red-300',
                    default   => 'border-zinc-200',
                };
                $bgColor = match($status) {
                    'ok'      => 'bg-green-50/40',
                    'warning' => 'bg-amber-50/40',
                    'missing' => 'bg-red-50/40',
                    default   => 'bg-zinc-50/40',
                };
                $icon = match($status) {
                    'ok'      => 'check-circle',
                    'warning' => 'exclamation-triangle',
                    'missing' => 'x-circle',
                    default   => 'information-circle',
                };
                $iconColor = match($status) {
                    'ok'      => 'text-green-500',
                    'warning' => 'text-amber-500',
                    'missing' => 'text-red-500',
                    default   => 'text-zinc-400',
                };
            @endphp
            <div class="border rounded-xl p-4 {{ $borderColor }} {{ $bgColor }}">
                <div class="flex items-start gap-3">
                    <flux:icon icon="{{ $icon }}" class="size-5 {{ $iconColor }} shrink-0 mt-0.5" />
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="font-semibold text-zinc-900 text-sm">{{ $label }}</span>
                            @if($declared)
                                <flux:badge color="green" size="sm">Declarado</flux:badge>
                            @else
                                <flux:badge color="zinc" size="sm">No declarado</flux:badge>
                            @endif
                        </div>
                        <p class="text-sm text-zinc-600">{{ $ev['detail'] }}</p>
                        @if($declared && $status === 'missing')
                            <p class="text-xs text-red-600 mt-1 font-medium">
                                ⚠ Declarado pero sin evidencia en el cuaderno — riesgo de penalización
                            </p>
                        @elseif(!$declared && $status === 'ok')
                            <p class="text-xs text-zinc-400 mt-1">
                                Prácticas compatibles pero no declarado en la PAC
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Resumen --}}
    @if($declaration)
        <x-agro.card>
            <x-slot:header>
                <div class="flex items-center gap-2">
                    <flux:icon icon="clipboard-document-check" class="size-4 text-zinc-500" />
                    <span class="font-semibold text-zinc-900 text-sm">Resumen de la verificación</span>
                </div>
            </x-slot:header>
            @php
                $ok       = collect($evidence)->where('status', 'ok')->count();
                $warnings = collect($evidence)->where('status', 'warning')->count();
                $missing  = collect($evidence)->where('status', 'missing')->count();
                $declared = count($declaredSchemes);
            @endphp
            <div class="grid grid-cols-4 gap-4 text-center text-sm py-2">
                <div>
                    <p class="text-2xl font-bold text-zinc-900">{{ $declared }}</p>
                    <p class="text-zinc-500 mt-1">Eco-regímenes declarados</p>
                </div>
                <div>
                    <p class="text-2xl font-bold text-green-600">{{ $ok }}</p>
                    <p class="text-zinc-500 mt-1">Con evidencia</p>
                </div>
                <div>
                    <p class="text-2xl font-bold text-amber-500">{{ $warnings }}</p>
                    <p class="text-zinc-500 mt-1">Con advertencias</p>
                </div>
                <div>
                    <p class="text-2xl font-bold text-red-500">{{ $missing }}</p>
                    <p class="text-zinc-500 mt-1">Sin evidencia</p>
                </div>
            </div>
            @if($missing > 0)
                <div class="mt-3 pt-3 border-t border-zinc-100">
                    <p class="text-sm text-red-600">
                        <strong>Atención:</strong> Tienes {{ $missing }} eco-régimen(es) declarado(s) sin evidencia en el cuaderno.
                        Registra las prácticas correspondientes antes de presentar la declaración.
                    </p>
                </div>
            @endif
        </x-agro.card>
    @endif

</div>
