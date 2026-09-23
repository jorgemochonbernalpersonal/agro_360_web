@php
    $fmt = fn (float $n, int $d = 0) => number_format($n, $d, ',', '.');
@endphp

<div class="grid lg:grid-cols-2 gap-8">
    {{-- Datos --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 space-y-5">
        <h2 class="text-lg font-bold text-gray-900">1. Parcela</h2>
        <div class="grid sm:grid-cols-3 gap-4">
            <x-tools.number-field model="hectares" label="Superficie" suffix="ha" />
            <x-tools.number-field model="rowSpacing" label="Entre filas" suffix="m" />
            <x-tools.number-field model="vineSpacing" label="Entre cepas" suffix="m" />
        </div>
        <x-tools.number-field model="missingPercent" label="Marras (cepas muertas o improductivas)" suffix="%" />

        <h2 class="text-lg font-bold text-gray-900 pt-2">2. Muestreo</h2>
        <div class="grid sm:grid-cols-2 gap-4">
            <x-tools.number-field model="clustersPerVine" label="Racimos por cepa (media)" />
            <x-tools.number-field model="clusterWeight" label="Peso medio del racimo" suffix="g" />
        </div>

        <h2 class="text-lg font-bold text-gray-900 pt-2">3. Opcional</h2>
        <div class="grid sm:grid-cols-2 gap-4">
            <x-tools.number-field model="litresPer100Kg" label="Rendimiento a vino" suffix="L/100 kg" help="Habitual: 65–75" />
            <x-tools.number-field model="limitKgPerHectare" label="Límite de tu D.O." suffix="kg/ha" placeholder="Ej.: 10000" />
        </div>
    </div>

    {{-- Resultado --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6" aria-live="polite">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Estimación</h2>

        @if ($this->result)
            @php $r = $this->result; @endphp
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div class="rounded-xl bg-[var(--color-agro-green-dark)] text-white p-4">
                    <div class="text-sm text-white/70">Kilos por hectárea</div>
                    <div class="text-2xl font-bold">{{ $fmt($r['kg_per_hectare']) }} kg</div>
                </div>
                <div class="rounded-xl bg-[var(--color-agro-green-dark)] text-white p-4">
                    <div class="text-sm text-white/70">Cosecha total</div>
                    <div class="text-2xl font-bold">{{ $fmt($r['total_kg']) }} kg</div>
                </div>
            </div>

            <dl class="divide-y text-sm">
                <div class="flex justify-between py-2"><dt class="text-gray-600">Cepas por hectárea (marco)</dt><dd class="font-medium">{{ $fmt($r['vines_per_hectare']) }}</dd></div>
                <div class="flex justify-between py-2"><dt class="text-gray-600">Cepas productivas por hectárea</dt><dd class="font-medium">{{ $fmt($r['productive_vines_per_hectare']) }}</dd></div>
                <div class="flex justify-between py-2"><dt class="text-gray-600">Kilos por cepa</dt><dd class="font-medium">{{ $fmt($r['kg_per_vine'], 2) }} kg</dd></div>
                <div class="flex justify-between py-2"><dt class="text-gray-600">Vino estimado</dt><dd class="font-medium">{{ $fmt($r['wine_litres']) }} L</dd></div>
            </dl>

            @if ($r['limit_ratio'] !== null)
                @php $pct = $r['limit_ratio'] * 100; @endphp
                <div @class([
                    'mt-6 rounded-xl p-4 text-sm',
                    'bg-red-50 border border-red-200 text-red-800' => $pct > 100,
                    'bg-amber-50 border border-amber-200 text-amber-800' => $pct > 90 && $pct <= 100,
                    'bg-green-50 border border-green-200 text-green-800' => $pct <= 90,
                ])>
                    @if ($pct > 100)
                        <strong>Superas el límite de tu D.O. en un {{ $fmt($pct - 100, 1) }} %.</strong> Valora un aclareo de racimos o destinar el excedente fuera de la D.O.
                    @elseif ($pct > 90)
                        <strong>Estás al {{ $fmt($pct, 1) }} % del límite.</strong> Vigila el peso final: el racimo puede ganar peso hasta vendimia.
                    @else
                        Estás al {{ $fmt($pct, 1) }} % del límite de producción de tu D.O.
                    @endif
                </div>
            @endif
        @else
            <p class="text-gray-500">Rellena los datos de la parcela y del muestreo para ver la estimación.</p>
        @endif
    </div>
</div>
