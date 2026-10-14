@php
$items = [
    ['route' => 'winery.harvest-summary.index',   'label' => 'Mando',       'icon' => 'chart-bar'],
    ['route' => 'winery.harvest-forecasts.index', 'label' => 'Previsiones', 'icon' => 'clipboard-document-list'],
    ['route' => 'winery.grape-reception.index',   'label' => 'Recepciones', 'icon' => 'archive-box-arrow-down'],
    ['route' => 'winery.harvest-quality.index',   'label' => 'Calidad',     'icon' => 'beaker'],
    ['route' => 'winery.vitic-estimates.index',   'label' => 'Aforos',      'icon' => 'calculator'],
];

// Los aforos son de viticultores externos: el producer solo los tiene si compra
// uva a terceros (EnsureProducerBuysExternalGrape gatea la ruta con 403).
if (auth()->user()?->isProducer() && ! auth()->user()->compra_uva_externa) {
    $items = array_values(array_filter(
        $items,
        fn ($item) => $item['route'] !== 'winery.vitic-estimates.index'
    ));
}
@endphp

<nav class="flex items-center gap-1 p-1 bg-zinc-100 rounded-xl w-fit overflow-x-auto max-w-full">
    @foreach($items as $item)
        <a href="{{ roleRoute($item['route']) }}" wire:navigate
            class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-all whitespace-nowrap
                {{ roleRouteIs($item['route'])
                    ? 'bg-white text-zinc-900 shadow-sm font-semibold'
                    : 'text-zinc-500 hover:text-zinc-800 hover:bg-white/60' }}">
            <flux:icon icon="{{ $item['icon'] }}" class="size-3.5 shrink-0" />
            <span>{{ $item['label'] }}</span>
        </a>
    @endforeach
</nav>
