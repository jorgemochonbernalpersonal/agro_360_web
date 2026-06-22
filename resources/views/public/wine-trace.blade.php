<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $wine->name }} — Trazabilidad</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-zinc-50 min-h-screen font-sans">

    {{-- Header --}}
    <div class="bg-white border-b border-zinc-200 px-4 py-4">
        <div class="max-w-2xl mx-auto flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-violet-100 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-violet-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15M14.25 3.104c.251.023.501.05.75.082M19.8 15l-1.575 1.573c-.453.453-.925.876-1.416 1.264M4.82 14.842l1.343 1.343c.453.453.925.876 1.416 1.264m0 0 .754.565A9.01 9.01 0 0 0 12 20.25a9.01 9.01 0 0 0 4.174-2.236l.754-.565m-9.852 0a18.03 18.03 0 0 1-1.958-1.487L4.82 14.842" />
                </svg>
            </div>
            <div>
                <h1 class="font-bold text-zinc-900 text-lg leading-tight">{{ $wine->name }}</h1>
                <p class="text-sm text-zinc-500">{{ __('Ficha de trazabilidad') }}</p>
            </div>
        </div>
    </div>

    <div class="max-w-2xl mx-auto px-4 py-6 space-y-6">

        {{-- Datos básicos --}}
        <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm p-5">
            <h2 class="font-semibold text-zinc-800 mb-3 text-sm uppercase tracking-wide">{{ __('Información del vino') }}</h2>
            <dl class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                @if($wine->vintage)
                    <div>
                        <dt class="text-zinc-400">{{ __('Añada') }}</dt>
                        <dd class="font-medium text-zinc-800">{{ $wine->vintage }}</dd>
                    </div>
                @endif
                @php
                    $typeLabels = ['red'=>'Tinto','white'=>'Blanco','rose'=>'Rosado','sparkling'=>'Espumoso','fortified'=>'Generoso','sweet'=>'Dulce','semi_sweet'=>'Semidulce','other'=>'Otro'];
                    $agingLabels = ['joven'=>'Joven','roble'=>'Roble','crianza'=>'Crianza','reserva'=>'Reserva','gran_reserva'=>'Gran Reserva','other'=>'Otro'];
                    $statusLabels = ['in_progress'=>'En elaboración','aged'=>'En crianza','bottled'=>'Embotellado','sold'=>'Vendido','cancelled'=>'Cancelado'];
                @endphp
                <div>
                    <dt class="text-zinc-400">{{ __('Tipo') }}</dt>
                    <dd class="font-medium text-zinc-800">{{ $typeLabels[$wine->wine_type] ?? $wine->wine_type }}</dd>
                </div>
                @if($wine->aging_type)
                    <div>
                        <dt class="text-zinc-400">{{ __('Crianza') }}</dt>
                        <dd class="font-medium text-zinc-800">{{ $agingLabels[$wine->aging_type] ?? $wine->aging_type }}</dd>
                    </div>
                @endif
                @if($wine->category)
                    <div>
                        <dt class="text-zinc-400">{{ __('Categoría') }}</dt>
                        <dd class="font-medium text-zinc-800">{{ $wine->category }}</dd>
                    </div>
                @endif
                @if($wine->volume_liters)
                    <div>
                        <dt class="text-zinc-400">{{ __('Volumen') }}</dt>
                        <dd class="font-medium text-zinc-800">{{ number_format($wine->volume_liters, 0) }} L</dd>
                    </div>
                @endif
                @if($wine->is_organic)
                    <div>
                        <dt class="text-zinc-400">{{ __('Certificación') }}</dt>
                        <dd class="font-medium text-green-700">{{ __('🌱 Ecológico') }}</dd>
                    </div>
                @endif
            </dl>
        </div>

        {{-- Composición varietal --}}
        @if($composition->isNotEmpty())
            <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm p-5">
                <h2 class="font-semibold text-zinc-800 mb-3 text-sm uppercase tracking-wide">{{ __('Composición varietal') }}</h2>
                @php $colors = ['bg-violet-500','bg-blue-500','bg-emerald-500','bg-amber-500','bg-rose-500','bg-sky-500']; @endphp
                <div class="flex rounded-full overflow-hidden h-2.5 mb-4 gap-px">
                    @foreach($composition as $i => $entry)
                        <div class="{{ $colors[$i % count($colors)] }}" style="width: {{ $entry->percentage ?? 0 }}%"></div>
                    @endforeach
                </div>
                <ul class="space-y-2">
                    @foreach($composition as $i => $entry)
                        @php $variety = $entry->harvest->plotPlanting?->grapeVariety?->name ?? 'Variedad desconocida'; @endphp
                        <li class="flex items-center justify-between text-sm">
                            <span class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full {{ $colors[$i % count($colors)] }} inline-block"></span>
                                <span class="text-zinc-700">{{ $variety }}</span>
                            </span>
                            <span class="font-medium text-zinc-500">{{ number_format($entry->quantity_kg ?? 0, 0) }} kg
                                @if($entry->percentage)<span class="text-zinc-400">({{ number_format($entry->percentage, 1) }}%)</span>@endif
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Último análisis --}}
        @if($lastAnalysis)
            <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm p-5">
                <h2 class="font-semibold text-zinc-800 mb-1 text-sm uppercase tracking-wide">{{ __('Análisis enológico') }}</h2>
                <p class="text-xs text-zinc-400 mb-3">{{ $lastAnalysis->analysis_date?->format('d/m/Y') }}</p>
                <dl class="grid grid-cols-3 gap-3 text-sm">
                    @if($lastAnalysis->alcohol)
                        <div class="bg-zinc-50 rounded-xl p-2 text-center">
                            <dt class="text-[10px] text-zinc-400 uppercase tracking-wide">{{ __('Alcohol') }}</dt>
                            <dd class="font-bold text-zinc-800">{{ $lastAnalysis->alcohol }}°</dd>
                        </div>
                    @endif
                    @if($lastAnalysis->ph)
                        <div class="bg-zinc-50 rounded-xl p-2 text-center">
                            <dt class="text-[10px] text-zinc-400 uppercase tracking-wide">pH</dt>
                            <dd class="font-bold text-zinc-800">{{ $lastAnalysis->ph }}</dd>
                        </div>
                    @endif
                    @if($lastAnalysis->total_acidity)
                        <div class="bg-zinc-50 rounded-xl p-2 text-center">
                            <dt class="text-[10px] text-zinc-400 uppercase tracking-wide">{{ __('Acidez total') }}</dt>
                            <dd class="font-bold text-zinc-800">{{ $lastAnalysis->total_acidity }} g/L</dd>
                        </div>
                    @endif
                    @if($lastAnalysis->residual_sugar)
                        <div class="bg-zinc-50 rounded-xl p-2 text-center">
                            <dt class="text-[10px] text-zinc-400 uppercase tracking-wide">{{ __('Azúcar res.') }}</dt>
                            <dd class="font-bold text-zinc-800">{{ $lastAnalysis->residual_sugar }} g/L</dd>
                        </div>
                    @endif
                    @if($lastAnalysis->so2_free)
                        <div class="bg-zinc-50 rounded-xl p-2 text-center">
                            <dt class="text-[10px] text-zinc-400 uppercase tracking-wide">{{ __('SO₂ libre') }}</dt>
                            <dd class="font-bold text-zinc-800">{{ $lastAnalysis->so2_free }} mg/L</dd>
                        </div>
                    @endif
                    @if($lastAnalysis->so2_total)
                        <div class="bg-zinc-50 rounded-xl p-2 text-center">
                            <dt class="text-[10px] text-zinc-400 uppercase tracking-wide">{{ __('SO₂ total') }}</dt>
                            <dd class="font-bold text-zinc-800">{{ $lastAnalysis->so2_total }} mg/L</dd>
                        </div>
                    @endif
                </dl>
            </div>
        @endif

        {{-- Footer --}}
        <p class="text-center text-xs text-zinc-400 pb-4">
            Información generada por Agro365 · {{ now()->format('Y') }}
        </p>
    </div>
</body>
</html>
