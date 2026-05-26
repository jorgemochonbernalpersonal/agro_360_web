<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="{{ __('Campañas') }}"
        :description="__('Resumen de campañas de vendimia y actividad por viticultor en la denominación.')"
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
                                    <x-agro.card-item-header
                                        icon="calendar-days"
                                        :title="(string) $row->year"
                                        subtitle="Campaña de vendimia"
                                        iconBg="bg-amber-100"
                                        iconColor="text-amber-600"
                                        size="md"
                                        radius="xl"
                                    >
                                        @if($row->year === $currentYear)
                                            <flux:badge color="green" size="sm">{{ __('Actual') }}</flux:badge>
                                        @endif
                                    </x-agro.card-item-header>
                                </x-slot:header>

                                <div class="flex-1 space-y-4">
                                    <div class="grid grid-cols-2 gap-2">
                                        <div class="bg-blue-50 rounded-xl p-3">
                                            <p class="text-[10px] font-semibold text-blue-400 uppercase tracking-widest mb-0.5">{{ __('Campañas') }}</p>
                                            <p class="text-2xl font-bold text-blue-700 leading-none">{{ $row->campaign_count }}</p>
                                        </div>
                                        <div class="bg-agro-50 rounded-xl p-3">
                                            <p class="text-[10px] font-semibold text-agro-400 uppercase tracking-widest mb-0.5">{{ __('Activas') }}</p>
                                            <p class="text-2xl font-bold text-agro-700 leading-none">{{ $row->active_count }}</p>
                                        </div>
                                    </div>

                                    <div class="space-y-2 text-sm">
                                        <div class="flex items-center justify-between">
                                            <span class="text-zinc-400">{{ __('Recepciones') }}</span>
                                            <span class="text-zinc-700 font-medium">{{ $row->reception_count > 0 ? $row->reception_count : '---' }}</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-zinc-400">{{ __('Total uva') }}</span>
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
                    <x-agro.empty-state icon="calendar-days" title="{{ __('No hay campañas') }}" :description="__('No hay datos de campañas aún.')" />
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
                                    <x-agro.card-item-header
                                        icon="user"
                                        :title="$vit->name"
                                        :subtitle="$vit->email"
                                        iconBg="bg-agro-100"
                                        iconColor="text-agro-600"
                                        size="md"
                                        radius="xl"
                                    >
                                        @if($cs?->has_active)
                                            <span class="inline-flex items-center gap-1 text-xs font-medium text-agro-700 bg-agro-50 px-2 py-0.5 rounded-full">
                                                <span class="w-1.5 h-1.5 rounded-full bg-agro-500 flex-shrink-0"></span>
                                                Activa
                                            </span>
                                        @else
                                            <flux:badge color="zinc" size="sm">{{ __('Sin activa') }}</flux:badge>
                                        @endif
                                    </x-agro.card-item-header>
                                </x-slot:header>

                                <div class="flex-1 space-y-4">
                                    <div class="grid grid-cols-2 gap-2">
                                        <div class="bg-blue-50 rounded-xl p-3">
                                            <p class="text-[10px] font-semibold text-blue-400 uppercase tracking-widest mb-0.5">{{ __('Campañas') }}</p>
                                            <p class="text-2xl font-bold text-blue-700 leading-none">{{ $cs?->total_campaigns ?? 0 }}</p>
                                        </div>
                                        <div class="bg-amber-50 rounded-xl p-3">
                                            <p class="text-[10px] font-semibold text-amber-400 uppercase tracking-widest mb-0.5">{{ __('Última') }}</p>
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

                    <x-agro-pagination :paginator="$viticulturistList" />
                @else
                    <x-agro.empty-state icon="users" title="{{ __('No hay viticultores') }}" :description="__('No hay viticultores adscritos a esta denominación.')" />
                @endif

            @endif
        </div>
    </div>

</div>
