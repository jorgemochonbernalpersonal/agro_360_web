{{-- Partial: datos de una campaña en la comparativa --}}
@php
    $h = $data['harvest'];
    $acts = $data['activityCounts'];
    $actLabels = \App\Models\AgriculturalActivity::ACTIVITY_TYPES;
@endphp

{{-- KPIs de vendimia --}}
<div class="grid grid-cols-2 gap-3">
    <x-agro.stat-card
        label="Producción total"
        :value="number_format($h->total_kg, 0, ',', '.') . ' kg'"
        icon="scale"
        :color="$color"
    />
    <x-agro.stat-card
        label="Rendimiento medio"
        :value="number_format($h->avg_yield, 0, ',', '.') . ' kg/ha'"
        icon="chart-bar"
        :color="$color"
    />
    <x-agro.stat-card
        label="Grado Baumé medio"
        :value="number_format($h->avg_baume, 1, ',', '.') . '°'"
        icon="beaker"
        :color="$color"
    />
    <x-agro.stat-card
        label="Precio medio"
        :value="number_format($h->avg_price_kg, 2, ',', '.') . ' €/kg'"
        icon="banknotes"
        :color="$color"
    />
</div>

{{-- Facturación --}}
<div class="bg-white rounded-xl border border-zinc-200 p-4">
    <div class="flex items-center justify-between">
        <span class="text-sm text-zinc-500">Valor total vendimia</span>
        <span class="text-lg font-bold text-zinc-900">{{ number_format($h->total_value, 2, ',', '.') }} €</span>
    </div>
    <div class="flex items-center justify-between mt-1">
        <span class="text-sm text-zinc-500">Entradas de vendimia</span>
        <span class="font-semibold text-zinc-700">{{ $h->total_harvests }}</span>
    </div>
</div>

{{-- Actividades del cuaderno --}}
<div class="bg-white rounded-xl border border-zinc-200 p-4">
    <h4 class="text-xs font-semibold text-zinc-400 uppercase tracking-wide mb-3">Actividades del Cuaderno ({{ $data['totalActivities'] }})</h4>
    <div class="space-y-2">
        @foreach ($actLabels as $type => $label)
            @php $count = $acts[$type] ?? 0; @endphp
            <div class="flex items-center justify-between text-sm">
                <span class="text-zinc-600">{{ $label }}</span>
                <span class="font-medium {{ $count > 0 ? 'text-zinc-900' : 'text-zinc-300' }}">{{ $count }}</span>
            </div>
        @endforeach
    </div>
</div>
