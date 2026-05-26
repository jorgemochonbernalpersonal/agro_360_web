<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="{{ __('Supervisión — Bodegas') }}"
        :description="__('Actividad de vendimia y viticultores por bodega adscrita.')"
    >
        <x-slot:actions>
            <flux:button wire:click="openLinkModal" variant="primary" icon="plus">{{ __('Adscribir bodega') }}</flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Stats --}}
    <x-agro.stats-section key="oversight-wineries" columns="3">
        <x-agro.stat-card
            :label="__('Bodegas supervisadas')"
            :value="$totalWineries"
            icon="building-office-2"
            color="blue"
        />
        <x-agro.stat-card
            :label="__('Total uva recibida (kg)')"
            :value="number_format($harvestStats->sum('total_kg'), 0, ',', '.')"
            icon="scale"
            color="agro"
            :description="'Vendimia ' . $vintage"
        />
        <x-agro.stat-card
            :label="__('Recepciones de uva')"
            :value="$harvestStats->sum('reception_count')"
            icon="inbox"
            color="yellow"
            :description="'Vendimia ' . $vintage"
        />
    </x-agro.stats-section>

    {{-- Filters --}}
    <div class="flex flex-wrap items-center gap-3">
        <x-agro.search-input wire:model.live.debounce.300ms="search" :placeholder="__('Buscar bodega...')" />

        @if($availableVintages->isNotEmpty())
            <select
                wire:model.live="vintageFilter"
                class="text-sm border border-zinc-200 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-300"
            >
                @foreach($availableVintages as $v)
                    <option value="{{ $v }}">Vendimia {{ $v }}</option>
                @endforeach
            </select>
        @endif

        @if($search || $vintageFilter)
            <button wire:click="clearFilters" class="text-xs text-zinc-400 hover:text-zinc-600 transition px-2 py-1.5">{{ __('Limpiar filtros') }}</button>
        @endif
    </div>

    {{-- Card Grid --}}
    @if($wineries->count() > 0)
        <div
            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
            wire:loading.class="opacity-60 pointer-events-none"
            wire:target="search, vintageFilter, clearFilters"
        >
            @foreach($wineries as $winery)
                @php
                    $stats = $harvestStats[$winery->id] ?? null;
                    $delay = min($loop->index * 50, 300);
                @endphp
                <x-agro.card
                    class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                    style="animation-delay: {{ $delay }}ms;"
                    wire:key="winery-{{ $winery->id }}"
                >
                    <x-slot:header>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center shrink-0">
                                <flux:icon icon="building-office-2" class="size-5 text-blue-600" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-bold text-zinc-900 truncate">
                                    <a href="{{ route('supervisor.oversight.wineries.show', $winery) }}"
                                       wire:navigate
                                       class="hover:text-blue-600 transition">
                                        {{ $winery->name }}
                                    </a>
                                </h3>
                                <p class="text-xs text-zinc-400 truncate">{{ $winery->email }}</p>
                            </div>
                        </div>
                    </x-slot:header>

                    <div class="flex-1 space-y-4">
                        {{-- Metric boxes --}}
                        <div class="grid grid-cols-3 gap-2">
                            <div class="bg-emerald-50 rounded-lg p-2 text-center">
                                <p class="text-[9px] text-emerald-400 uppercase tracking-wide mb-0.5">{{ __('Viticultores') }}</p>
                                <p class="text-sm font-bold text-emerald-700">
                                    {{ $vitCountByWinery[$winery->id] ?? 0 }}
                                </p>
                            </div>
                            <div class="bg-amber-50 rounded-lg p-2 text-center">
                                <p class="text-[9px] text-amber-400 uppercase tracking-wide mb-0.5">{{ __('Recepciones') }}</p>
                                <p class="text-sm font-bold text-amber-700">
                                    {{ $stats?->reception_count ?? 0 }}
                                </p>
                            </div>
                            <div class="bg-blue-50 rounded-lg p-2 text-center">
                                <p class="text-[9px] text-blue-400 uppercase tracking-wide mb-0.5">{{ __('Uva (kg)') }}</p>
                                <p class="text-sm font-bold text-blue-700">
                                    @if($stats?->total_kg)
                                        {{ number_format($stats->total_kg, 0, ',', '.') }}
                                    @else
                                        0
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <x-slot:footer>
                        <div class="flex items-center justify-between">
                            <x-agro.action-button icon="eye" variant="success" href="{{ route('supervisor.oversight.wineries.show', $winery) }}" wire:navigate title="{{ __('Ver detalle') }}" />
                            <flux:button
                                wire:click="unlinkWinery({{ $winery->id }})"
                                wire:confirm="{{ __('¿Desvincular :name de la denominación? Se eliminarán también las asignaciones de viticultores DO a esta bodega.', ['name' => $winery->name]) }}"
                                variant="ghost"
                                size="sm"
                                icon="x-mark"
                                class="text-zinc-400 hover:text-red-500"
                                title="{{ __('Desvincular') }}"
                            />
                        </div>
                    </x-slot:footer>
                </x-agro.card>
            @endforeach
        </div>

        <x-agro-pagination :paginator="$wineries" />
    @else
        <x-agro.empty-state
            icon="building-office-2"
            title="{{ __('No hay bodegas adscritas') }}"
            :description="__('No hay bodegas adscritas a esta denominación.')"
        />
    @endif

    {{-- Modal: adscribir bodega existente --}}
    <flux:modal wire:model="showLinkModal" class="max-w-lg">
        <div class="space-y-5">
            <div>
                <flux:heading size="lg">{{ __('Adscribir bodega a la denominación') }}</flux:heading>
                <flux:text class="mt-1 text-zinc-500">
                    Busca la bodega por nombre o email y confírmala para incluirla bajo la supervisión de esta DO.
                </flux:text>
            </div>

            <div>
                <flux:label>{{ __('Buscar bodega') }}</flux:label>
                <flux:input
                    wire:model.live.debounce.300ms="linkSearch"
                    placeholder="{{ __('Nombre o email de la bodega...') }}"
                    icon="magnifying-glass"
                    autofocus
                />
                <p class="mt-1 text-xs text-zinc-400">{{ __('Introduce al menos 2 caracteres para buscar.') }}</p>
            </div>

            @if($linkCandidates->isNotEmpty())
                <div class="border border-zinc-200 rounded-xl divide-y divide-zinc-100 overflow-hidden">
                    @foreach($linkCandidates as $candidate)
                        <button
                            type="button"
                            wire:click="$set('linkWineryId', {{ $candidate->id }})"
                            class="w-full flex items-center justify-between px-4 py-3 text-left text-sm hover:bg-zinc-50 transition {{ $linkWineryId === $candidate->id ? 'bg-blue-50 ring-1 ring-inset ring-blue-200' : '' }}"
                        >
                            <div>
                                <span class="font-medium text-zinc-800">{{ $candidate->name }}</span>
                                <span class="ml-2 text-xs text-zinc-400">{{ $candidate->email }}</span>
                            </div>
                            @if($linkWineryId === $candidate->id)
                                <flux:icon icon="check-circle" class="w-4 h-4 text-blue-600 flex-shrink-0" />
                            @endif
                        </button>
                    @endforeach
                </div>
            @elseif(strlen($linkSearch) >= 2)
                <p class="text-sm text-zinc-400 text-center py-3">{{ __('No se encontraron bodegas con ese nombre o email.') }}</p>
            @endif

            @error('linkWineryId')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror

            <div class="flex justify-end gap-3 pt-1">
                <flux:button wire:click="closeLinkModal" variant="ghost">{{ __('Cancelar') }}</flux:button>
                <flux:button
                    wire:click="linkWinery"
                    variant="primary"
                    :disabled="!$linkWineryId"
                >{{ __('Adscribir bodega') }}</flux:button>
            </div>
        </div>
    </flux:modal>

</div>
