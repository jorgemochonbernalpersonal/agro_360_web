<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="{{ __('Supervisión — Viticultores') }}"
        :description="__('Parcelas, plantaciones y actividad de los viticultores adscritos a la denominación.')"
    />

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <x-agro.stat-card
            :label="__('Viticultores DO')"
            :value="$totalGrowers"
            icon="users"
            color="agro"
        />
        <x-agro.stat-card
            :label="__('Total parcelas')"
            :value="$growers->sum('plots_count')"
            icon="map"
            color="blue"
        />
    </div>

    {{-- Search --}}
    <div class="flex items-center gap-2">
        <x-agro.search-input wire:model.live.debounce.300ms="search" :placeholder="__('Buscar viticultor...')" />
        @if($search)
            <button wire:click="clearSearch" class="text-xs text-zinc-400 hover:text-zinc-600 transition px-2 py-1.5">{{ __('Limpiar') }}</button>
        @endif
    </div>

    {{-- Card Grid --}}
    @if($growers->count() > 0)
        <div
            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
            wire:loading.class="opacity-60 pointer-events-none"
            wire:target="search, clearSearch"
        >
            @foreach($growers as $grower)
                @php
                    $delay = min($loop->index * 50, 300);
                @endphp
                <x-agro.card
                    class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                    style="animation-delay: {{ $delay }}ms;"
                    wire:key="grower-{{ $grower->id }}"
                >
                    <x-slot:header>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center shrink-0">
                                <flux:icon icon="user" class="size-5 text-emerald-600" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-bold text-zinc-900 truncate">
                                    <a href="{{ route('supervisor.oversight.growers.show', $grower) }}" wire:navigate
                                       class="hover:text-emerald-700 transition">{{ $grower->name }}</a>
                                </h3>
                                <p class="text-xs text-zinc-400 truncate">{{ $grower->email }}</p>
                            </div>
                        </div>
                    </x-slot:header>

                    <div class="flex-1 space-y-4">
                        {{-- Metric boxes --}}
                        <div class="grid grid-cols-3 gap-2">
                            <div class="bg-emerald-50 rounded-lg p-2 text-center">
                                <p class="text-[9px] text-emerald-400 uppercase tracking-wide mb-0.5">{{ __('Parcelas') }}</p>
                                <p class="text-sm font-bold text-emerald-700">{{ $grower->plots_count }}</p>
                            </div>
                            <div class="bg-blue-50 rounded-lg p-2 text-center">
                                <p class="text-[9px] text-blue-400 uppercase tracking-wide mb-0.5">{{ __('Plantaciones') }}</p>
                                <p class="text-sm font-bold text-blue-700">{{ $plantingCountByVit[$grower->id] ?? 0 }}</p>
                            </div>
                            <div class="bg-amber-50 rounded-lg p-2 text-center">
                                <p class="text-[9px] text-amber-400 uppercase tracking-wide mb-0.5">{{ __('Área (ha)') }}</p>
                                <p class="text-sm font-bold text-amber-700">
                                    @if($grower->plots_sum_area)
                                        {{ number_format($grower->plots_sum_area, 2) }}
                                    @else
                                        0
                                    @endif
                                </p>
                            </div>
                        </div>

                        {{-- Last activity --}}
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-zinc-400">{{ __('Ultima actividad') }}</span>
                            <span class="text-zinc-700">
                                @if(isset($lastActivityByVit[$grower->id]))
                                    {{ \Carbon\Carbon::parse($lastActivityByVit[$grower->id])->format('d/m/Y') }}
                                @else
                                    <span class="text-zinc-300">{{ __('Sin actividad') }}</span>
                                @endif
                            </span>
                        </div>
                    </div>

                    <x-slot:footer>
                        <div class="flex items-center justify-end">
                            <x-agro.action-button icon="eye" variant="success" href="{{ route('supervisor.oversight.growers.show', $grower) }}" wire:navigate title="{{ __('Ver detalle') }}" />
                        </div>
                    </x-slot:footer>
                </x-agro.card>
            @endforeach
        </div>

        <x-agro-pagination :paginator="$growers" />
    @else
        <x-agro.empty-state
            icon="users"
            title="{{ __('No hay viticultores') }}"
            :description="__('No hay viticultores adscritos a esta denominación.')"
        />
    @endif

</div>
