@props([
    'icon' => null,
    'title',
    'description' => null,
    'badgeIcon' => null,
    'badgeColor' => 'bg-[var(--color-agro-yellow)]',
])

@php
    // Auto-detectar capítulo activo y aplicar su color de acento
    $chapterAccents = [
        'campana'   => ['color' => '#4ade80', 'bg' => 'rgba(74,222,128,0.12)',  'border' => 'rgba(74,222,128,0.4)'],
        'parcelas'  => ['color' => '#60a5fa', 'bg' => 'rgba(96,165,250,0.12)',  'border' => 'rgba(96,165,250,0.4)'],
        'recursos'  => ['color' => '#fb923c', 'bg' => 'rgba(251,146,60,0.12)',  'border' => 'rgba(251,146,60,0.4)'],
        'normativa' => ['color' => '#c084fc', 'bg' => 'rgba(192,132,252,0.12)', 'border' => 'rgba(192,132,252,0.4)'],
        'negocio'   => ['color' => '#2dd4bf', 'bg' => 'rgba(45,212,191,0.12)',  'border' => 'rgba(45,212,191,0.4)'],
        'sistema'   => ['color' => '#94a3b8', 'bg' => 'rgba(148,163,184,0.12)', 'border' => 'rgba(148,163,184,0.4)'],
    ];

    $detectedChapter = 'campana'; // default

    if (request()->routeIs('plots.*', 'sigpac.*', 'remote-sensing.*', 'winery.plots.*')) {
        $detectedChapter = 'parcelas';
    } elseif (request()->routeIs(
        'viticulturist.personal*', 'viticulturist.machinery*',
        'viticulturist.containers*', 'viticulturist.almacen*',
        'viticulturist.phytosanitary*', 'winery.containers*',
        'winery.winery-supplies*', 'winery.suppliers*'
    )) {
        $detectedChapter = 'recursos';
    } elseif (request()->routeIs(
        'viticulturist.exploitations*', 'viticulturist.commercial-authorizations*',
        'viticulturist.advisory*', 'viticulturist.field-applicators*',
        'viticulturist.field-equipment*', 'viticulturist.residue*',
        'viticulturist.energy*', 'viticulturist.cue*',
        'viticulturist.pac*', 'viticulturist.official*',
        'viticulturist.pac-compliance*', 'winery.silicie*', 'winery.documents*'
    )) {
        $detectedChapter = 'normativa';
    } elseif (request()->routeIs(
        'viticulturist.invoices*', 'viticulturist.marketed*',
        'viticulturist.clients*', 'viticulturist.financial*',
        'winery.invoices*', 'winery.clients*'
    )) {
        $detectedChapter = 'negocio';
    } elseif (request()->routeIs('viticulturist.settings*', 'viticulturist.support*', 'winery.settings*')) {
        $detectedChapter = 'sistema';
    }

    $accent = $chapterAccents[$detectedChapter];
@endphp

<div class="rounded-2xl p-6 hover-lift border border-zinc-200/60 bg-white/70 backdrop-blur-sm shadow-sm"
     style="border-left: 3px solid {{ $accent['color'] }};">
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div class="flex items-center gap-5">

            @if($icon)
                <div class="relative flex-shrink-0">
                    <div class="w-14 h-14 rounded-xl flex items-center justify-center shadow-sm animate-scale-in"
                         style="background: {{ $accent['bg'] }}; box-shadow: 0 0 0 1px {{ $accent['border'] }};">
                        @if(str_starts_with($icon, '<svg'))
                            {!! $icon !!}
                        @elseif(str_starts_with($icon, '<flux') || str_contains($icon, ':'))
                            {!! $icon !!}
                        @else
                            <span class="text-2xl">{{ $icon }}</span>
                        @endif
                    </div>
                    @if($badgeIcon)
                        <div class="absolute -top-2 -right-2 w-7 h-7 rounded-full {{ $badgeColor }} flex items-center justify-center shadow-md">
                            <span class="text-xs">{{ $badgeIcon }}</span>
                        </div>
                    @endif
                </div>
            @endif

            <div class="flex-1">
                <h1 class="text-2xl font-bold text-zinc-800 leading-tight">
                    {{ $title }}
                </h1>
                @if($description)
                    <p class="text-sm text-zinc-500 mt-0.5">
                        {{ $description }}
                    </p>
                @endif
            </div>
        </div>

        @isset($actionButton)
            <div class="flex-shrink-0">
                {{ $actionButton }}
            </div>
        @endisset
    </div>
</div>
