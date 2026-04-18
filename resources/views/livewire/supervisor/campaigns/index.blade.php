<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Campañas"
        description="Resumen de campañas de vendimia y actividad por viticultor en la denominación."
    />

    {{-- Tabs --}}
    <div>
        <x-agro.tabs :tabs="$tabs" :active="$activeTab" wireMethod="setTab" />

        {{-- Loading skeleton --}}
        <x-agro.loading-grid target="setTab, nextPage, previousPage" />

        <div wire:loading.remove wire:target="setTab, nextPage, previousPage">
            @if($activeTab === 'resumen')

                {{-- Summary by year - card grid --}}
                @if(count($summaryRows) > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        @foreach($summaryRows as $row)
                            @php $delay = min($loop->index * 50, 300); @endphp
                            <x-agro.card
                                class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                                style="animation-delay: {{ $delay }}ms;"
                                wire:key="summary-{{ $row->year }}"
                            >
                                <x-slot:header>
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center shrink-0">
                                            <flux:icon icon="calendar-days" class="size-5 text-amber-600" />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h3 class="font-bold text-zinc-900">{{ $row->year }}</h3>
                                            <p class="text-xs text-zinc-500">Campaña de vendimia</p>
                                        </div>
                                        @if($row->year === $currentYear)
                                            <flux:badge color="green" size="sm" class="shrink-0">Actual</flux:badge>
                                        @endif
                                    </div>
                                </x-slot:header>

                                <div class="flex-1 space-y-4">
                                    <div class="grid grid-cols-2 gap-2">
                                        <div class="bg-blue-50 rounded-xl p-3">
                                            <p class="text-[10px] font-semibold text-blue-400 uppercase tracking-widest mb-0.5">Campañas</p>
                                            <p class="text-2xl font-bold text-blue-700 leading-none">{{ $row->campaign_count }}</p>
                                        </div>
                                        <div class="bg-agro-50 rounded-xl p-3">
                                            <p class="text-[10px] font-semibold text-agro-400 uppercase tracking-widest mb-0.5">Activas</p>
                                            <p class="text-2xl font-bold text-agro-700 leading-none">{{ $row->active_count }}</p>
                                        </div>
                                    </div>

                                    <div class="space-y-2 text-sm">
                                        <div class="flex items-center justify-between">
                                            <span class="text-zinc-400">Recepciones</span>
                                            <span class="text-zinc-700 font-medium">{{ $row->reception_count > 0 ? $row->reception_count : '---' }}</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-zinc-400">Total uva</span>
                                            <span class="text-zinc-700 font-medium">
                                                @if($row->total_kg > 0)
                                                    {{ number_format($row->total_kg, 0, ',', '.') }} kg
                                                @else
                                                    ---
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </x-agro.card>
                        @endforeach
                    </div>
                @else
                    <x-agro.empty-state icon="calendar-days" title="No hay campañas" description="No hay datos de campañas aún." />
                @endif

            @else

                {{-- Per viticulturist - card grid --}}
                @if($viticulturistList->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        @foreach($viticulturistList as $vit)
                            @php
                                $delay = min($loop->index * 50, 300);
                                $cs = $campaignSummaryByVit[$vit->id] ?? null;
                            @endphp
                            <x-agro.card
                                class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                                style="animation-delay: {{ $delay }}ms;"
                                wire:key="vit-{{ $vit->id }}"
                            >
                                <x-slot:header>
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-agro-100 flex items-center justify-center shrink-0">
                                            <flux:icon icon="user" class="size-5 text-agro-600" />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h3 class="font-bold text-zinc-900 truncate">{{ $vit->name }}</h3>
                                            <p class="text-xs text-zinc-500 truncate">{{ $vit->email }}</p>
                                        </div>
                                        @if($cs?->has_active)
                                            <span class="inline-flex items-center gap-1 text-xs font-medium text-agro-700 bg-agro-50 px-2 py-0.5 rounded-full shrink-0">
                                                <span class="w-1.5 h-1.5 rounded-full bg-agro-500 flex-shrink-0"></span>
                                                Activa
                                            </span>
                                        @else
                                            <flux:badge color="zinc" size="sm" class="shrink-0">Sin activa</flux:badge>
                                        @endif
                                    </div>
                                </x-slot:header>

                                <div class="flex-1 space-y-4">
                                    <div class="grid grid-cols-2 gap-2">
                                        <div class="bg-blue-50 rounded-xl p-3">
                                            <p class="text-[10px] font-semibold text-blue-400 uppercase tracking-widest mb-0.5">Campañas</p>
                                            <p class="text-2xl font-bold text-blue-700 leading-none">{{ $cs?->total_campaigns ?? 0 }}</p>
                                        </div>
                                        <div class="bg-amber-50 rounded-xl p-3">
                                            <p class="text-[10px] font-semibold text-amber-400 uppercase tracking-widest mb-0.5">Última</p>
                                            <p class="text-2xl font-bold text-amber-700 leading-none">{{ $cs?->latest_year ?? '---' }}</p>
                                        </div>
                                    </div>

                                    <div class="space-y-2 text-sm">
                                        <div class="flex items-center justify-between">
                                            <span class="text-zinc-400">Kg entregados {{ $currentYear }}</span>
                                            <span class="text-zinc-700 font-medium">
                                                @if(isset($kgByVit[$vit->id]) && $kgByVit[$vit->id] > 0)
                                                    {{ number_format($kgByVit[$vit->id], 0, ',', '.') }} kg
                                                @else
                                                    ---
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </x-agro.card>
                        @endforeach
                    </div>

                    <div class="mt-6">{{ $viticulturistList->links() }}</div>
                @else
                    <x-agro.empty-state icon="users" title="No hay viticultores" description="No hay viticultores adscritos a esta denominación." />
                @endif

            @endif
        </div>
    </div>

</div>
