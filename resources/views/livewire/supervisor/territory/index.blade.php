<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Territorio"
        description="Distribución geográfica de parcelas y variedades en la denominación de origen."
    />

    {{-- Stats --}}
    <x-agro.stats-section key="supervisor-territory" columns="3">
        <x-agro.stat-card label="Parcelas activas" :value="$totalPlots" icon="map" color="blue" />
        <x-agro.stat-card label="Superficie total (ha)" :value="number_format($totalArea, 2)" icon="square-3-stack-3d" color="agro" />
        <x-agro.stat-card label="Parcelas ecológicas" :value="$organicPlots" icon="sparkles" color="agro" description="Certificadas ecológico" />
    </x-agro.stats-section>

    {{-- Tabs --}}
    <div>
        <x-agro.tabs :tabs="$tabs" :active="$activeTab" wireMethod="setTab" />

        @if($activeTab === 'provinces')
            @if($byProvince->count() > 0)
                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-4"
                    wire:loading.class="opacity-60 pointer-events-none"
                    wire:target="setTab"
                >
                    @foreach($byProvince as $row)
                        @php $delay = min($loop->index * 50, 300); @endphp
                        <x-agro.card
                            class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                            style="animation-delay: {{ $delay }}ms;"
                            wire:key="prov-{{ $loop->index }}"
                        >
                            <x-slot:header>
                                <x-agro.card-item-header
                                    icon="map-pin"
                                    :title="$row->province_name ?? '—'"
                                    subtitle="Provincia"
                                    iconBg="bg-blue-100"
                                    iconColor="text-blue-600"
                                    size="md"
                                    radius="xl"
                                >
                                    @if($totalArea > 0)
                                        <flux:badge color="blue" size="sm">
                                            {{ number_format(($row->total_area / $totalArea) * 100, 1) }}%
                                        </flux:badge>
                                    @endif
                                </x-agro.card-item-header>
                            </x-slot:header>

                            <div class="flex-1 space-y-3">
                                <div class="grid grid-cols-3 gap-2">
                                    <div class="bg-blue-50 rounded-lg p-2 text-center">
                                        <p class="text-[9px] text-blue-400 uppercase tracking-wide mb-0.5">Parcelas</p>
                                        <p class="text-sm font-bold text-blue-700">{{ $row->plot_count }}</p>
                                    </div>
                                    <div class="bg-emerald-50 rounded-lg p-2 text-center">
                                        <p class="text-[9px] text-emerald-400 uppercase tracking-wide mb-0.5">Superficie</p>
                                        <p class="text-sm font-bold text-emerald-700">
                                            {{ number_format($row->total_area, 2) }}
                                            <span class="text-[9px] font-normal text-emerald-400">ha</span>
                                        </p>
                                    </div>
                                    <div class="bg-green-50 rounded-lg p-2 text-center">
                                        <p class="text-[9px] text-green-400 uppercase tracking-wide mb-0.5">Ecológicas</p>
                                        <p class="text-sm font-bold text-green-700">{{ $row->organic_count }}</p>
                                    </div>
                                </div>
                            </div>
                        </x-agro.card>
                    @endforeach
                </div>
            @else
                <x-agro.empty-state icon="map-pin" title="Sin datos" description="No hay parcelas registradas." />
            @endif

        @elseif($activeTab === 'varieties')
            @php $totalPlanted = $byVariety->sum('planted_area'); @endphp
            @if($byVariety->count() > 0)
                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-4"
                    wire:loading.class="opacity-60 pointer-events-none"
                    wire:target="setTab"
                >
                    @foreach($byVariety as $row)
                        @php $delay = min($loop->index * 50, 300); @endphp
                        <x-agro.card
                            class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                            style="animation-delay: {{ $delay }}ms;"
                            wire:key="var-{{ $loop->index }}"
                        >
                            <x-slot:header>
                                <x-agro.card-item-header
                                    icon="sparkles"
                                    :title="$row->variety_name"
                                    :subtitle="ucfirst($row->variety_color ?? '—')"
                                    iconBg="bg-violet-100"
                                    iconColor="text-violet-600"
                                    size="md"
                                    radius="xl"
                                >
                                    @if($totalPlanted > 0)
                                        <flux:badge color="violet" size="sm">
                                            {{ number_format(($row->planted_area / $totalPlanted) * 100, 1) }}%
                                        </flux:badge>
                                    @endif
                                </x-agro.card-item-header>
                            </x-slot:header>

                            <div class="flex-1 space-y-3">
                                <div class="grid grid-cols-2 gap-2">
                                    <div class="bg-violet-50 rounded-lg p-2 text-center">
                                        <p class="text-[9px] text-violet-400 uppercase tracking-wide mb-0.5">Parcelas</p>
                                        <p class="text-sm font-bold text-violet-700">{{ $row->plot_count }}</p>
                                    </div>
                                    <div class="bg-emerald-50 rounded-lg p-2 text-center">
                                        <p class="text-[9px] text-emerald-400 uppercase tracking-wide mb-0.5">Superficie</p>
                                        <p class="text-sm font-bold text-emerald-700">
                                            {{ number_format($row->planted_area, 2) }}
                                            <span class="text-[9px] font-normal text-emerald-400">ha</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </x-agro.card>
                    @endforeach
                </div>
            @else
                <x-agro.empty-state icon="sparkles" title="Sin plantaciones" description="No hay plantaciones activas." />
            @endif

        @else
            {{-- Municipalities --}}
            @if($byMunicipality->count() > 0)
                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-4"
                    wire:loading.class="opacity-60 pointer-events-none"
                    wire:target="setTab"
                >
                    @foreach($byMunicipality as $row)
                        @php $delay = min($loop->index * 50, 300); @endphp
                        <x-agro.card
                            class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                            style="animation-delay: {{ $delay }}ms;"
                            wire:key="mun-{{ $loop->index }}"
                        >
                            <x-slot:header>
                                <x-agro.card-item-header
                                    icon="building-office"
                                    :title="$row->municipality_name ?? '—'"
                                    subtitle="Municipio"
                                    iconBg="bg-amber-100"
                                    iconColor="text-amber-600"
                                    size="md"
                                    radius="xl"
                                />
                            </x-slot:header>

                            <div class="flex-1 space-y-3">
                                <div class="grid grid-cols-2 gap-2">
                                    <div class="bg-amber-50 rounded-lg p-2 text-center">
                                        <p class="text-[9px] text-amber-400 uppercase tracking-wide mb-0.5">Parcelas</p>
                                        <p class="text-sm font-bold text-amber-700">{{ $row->plot_count }}</p>
                                    </div>
                                    <div class="bg-emerald-50 rounded-lg p-2 text-center">
                                        <p class="text-[9px] text-emerald-400 uppercase tracking-wide mb-0.5">Superficie</p>
                                        <p class="text-sm font-bold text-emerald-700">
                                            {{ number_format($row->total_area, 2) }}
                                            <span class="text-[9px] font-normal text-emerald-400">ha</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </x-agro.card>
                    @endforeach
                </div>
            @else
                <x-agro.empty-state icon="building-office" title="Sin municipios" description="No hay municipios con parcelas." />
            @endif
        @endif
    </div>

</div>
