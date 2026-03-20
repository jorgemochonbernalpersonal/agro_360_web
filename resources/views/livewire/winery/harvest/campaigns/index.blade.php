<div class="space-y-6 animate-fade-in">

    {{-- Header --}}
    <x-agro.page-header
        title="Campañas de Vendimia"
        description="Gestiona las campañas de recepción de uva de tu bodega"
    >
        <x-slot:actions>
            <flux:button href="{{ roleRoute('campaigns.create') }}" wire:navigate variant="primary" icon="plus">
                Nueva Campaña
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- KPIs --}}
    <div x-data="{
        open: localStorage.getItem('harvest-campaigns-stats-open') !== 'false',
        toggle() {
            this.open = !this.open;
            localStorage.setItem('harvest-campaigns-stats-open', String(this.open));
        }
    }">
        <button
            @click="toggle()"
            class="flex items-center gap-1.5 text-[11px] font-semibold text-zinc-400 uppercase tracking-widest hover:text-zinc-600 transition-colors mb-3"
        >
            <span>Estadísticas</span>
            <flux:icon icon="chevron-up" class="size-3.5 transition-transform duration-200" ::class="{ 'rotate-180': !open }" />
        </button>
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-1"
        >
        <div class="grid grid-cols-2 gap-4">
            <x-agro.stat-card
                label="Total campañas"
                :value="$stats['total']"
                icon="clipboard-document-list"
                color="zinc"
            />
            <x-agro.stat-card
                label="Campaña activa"
                :value="$stats['active']"
                icon="play-circle"
                color="agro"
            />
            <x-agro.stat-card
                label="Cerradas"
                :value="$stats['cerradas']"
                icon="lock-closed"
                color="zinc"
            />
            <x-agro.stat-card
                label="Bloqueadas"
                :value="$stats['locked']"
                icon="shield-check"
                color="amber"
            />
        </div>
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="flex items-center gap-3">
        <div class="flex-1 relative">
            <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                <flux:icon icon="magnifying-glass" class="size-4 text-zinc-400" />
            </div>
            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                placeholder="Buscar campaña por nombre..."
                class="w-full pl-9 pr-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm placeholder:text-zinc-400 shadow-sm focus:outline-none focus:ring-2 focus:ring-agro-500 focus:border-transparent transition"
            />
        </div>

        <flux:select wire:model.live="yearFilter" class="w-36">
            <flux:select.option value="">Todos los años</flux:select.option>
            @foreach($years as $year)
                <flux:select.option value="{{ $year }}">{{ $year }}</flux:select.option>
            @endforeach
        </flux:select>

        @if($search || $yearFilter)
            <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">
                Limpiar
            </flux:button>
        @endif
    </div>

    {{-- Loading skeleton --}}
    <div wire:loading wire:target="search, yearFilter, clearFilters, nextPage, previousPage">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @for($i = 0; $i < 6; $i++)
                <x-agro.skeleton-card />
            @endfor
        </div>
    </div>

    {{-- Grid de cards --}}
    <div wire:loading.remove wire:target="search, yearFilter, clearFilters, nextPage, previousPage">
        @if($campaigns->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($campaigns as $campaign)
                    @php
                        $delay   = min($loop->index * 50, 300);
                        $isActive = $campaign->active;
                        $isLocked = (bool) $campaign->locked_at;
                    @endphp
                    <x-agro.card
                        class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                        style="animation-delay: {{ $delay }}ms;"
                        wire:key="campaign-{{ $campaign->id }}"
                    >
                        <x-slot:header>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0
                                    {{ $isActive ? 'bg-agro-100' : 'bg-zinc-100' }}">
                                    <flux:icon icon="clipboard-document-list"
                                        class="size-5 {{ $isActive ? 'text-agro-600' : 'text-zinc-400' }}" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-zinc-900 truncate">{{ $campaign->name }}</h3>
                                    <p class="text-xs text-zinc-400">Vendimia {{ $campaign->year }}</p>
                                </div>
                                @if($isActive)
                                    <flux:badge color="green" size="sm" class="shrink-0">Activa</flux:badge>
                                @elseif($isLocked)
                                    <flux:badge color="indigo" size="sm" class="shrink-0">Bloqueada</flux:badge>
                                @else
                                    <flux:badge color="zinc" size="sm" class="shrink-0">Cerrada</flux:badge>
                                @endif
                            </div>
                        </x-slot:header>

                        <div class="flex-1 space-y-4">
                            {{-- Stats --}}
                            <div class="grid grid-cols-2 gap-2">
                                <div class="bg-agro-50 rounded-xl p-3">
                                    <p class="text-[10px] font-semibold text-agro-400 uppercase tracking-widest mb-0.5">Recepciones</p>
                                    <p class="text-2xl font-bold text-agro-700 leading-none">
                                        {{ $campaign->activities_count }}
                                    </p>
                                </div>
                                <div class="bg-zinc-50 rounded-xl p-3">
                                    <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest mb-0.5">Año</p>
                                    <p class="text-2xl font-bold text-zinc-700 leading-none">
                                        {{ $campaign->year }}
                                    </p>
                                </div>
                            </div>

                            {{-- Periodo --}}
                            @if($campaign->start_date || $campaign->end_date)
                                <div class="flex items-center gap-2 text-sm text-zinc-600">
                                    <flux:icon icon="calendar-days" class="size-4 text-zinc-400 shrink-0" />
                                    <span>
                                        {{ $campaign->start_date?->format('d/m/Y') ?? '—' }}
                                        —
                                        {{ $campaign->end_date?->format('d/m/Y') ?? '—' }}
                                    </span>
                                </div>
                            @endif

                            {{-- Descripción --}}
                            @if($campaign->description)
                                <p class="text-xs text-zinc-400 truncate">{{ $campaign->description }}</p>
                            @endif

                            {{-- Validaciones --}}
                            @if($campaign->mid_validation_signed || $campaign->final_validation_signed)
                                <div class="flex items-center gap-2 flex-wrap">
                                    @if($campaign->mid_validation_signed)
                                        <span class="inline-flex items-center gap-1 text-[10px] font-medium text-agro-700 bg-agro-50 px-2 py-0.5 rounded-full border border-agro-200">
                                            <flux:icon icon="check-circle" class="size-3" />
                                            Val. intermedia
                                        </span>
                                    @endif
                                    @if($campaign->final_validation_signed)
                                        <span class="inline-flex items-center gap-1 text-[10px] font-medium text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-full border border-indigo-200">
                                            <flux:icon icon="shield-check" class="size-3" />
                                            Val. final
                                        </span>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <x-slot:footer>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-0.5">
                                    <a href="{{ roleRoute('grape-reception.index', ['campaignFilter' => $campaign->id]) }}"
                                       wire:navigate
                                       title="Ver recepciones"
                                       class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-agro-600 hover:bg-agro-50 transition-colors">
                                        <flux:icon icon="archive-box-arrow-down" class="size-4" />
                                    </a>
                                </div>

                                <div class="w-px h-5 bg-zinc-200 mx-1"></div>

                                <div class="flex items-center gap-0.5">
                                    @if(!$isLocked)
                                        <a href="{{ roleRoute('campaigns.edit', $campaign) }}"
                                           wire:navigate
                                           title="Editar campaña"
                                           class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors">
                                            <flux:icon icon="pencil-square" class="size-4" />
                                        </a>
                                    @endif

                                    <button
                                        wire:click="toggleActive({{ $campaign->id }})"
                                        wire:loading.attr="disabled"
                                        title="{{ $isActive ? 'Cerrar campaña' : 'Activar campaña' }}"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg transition-colors
                                            {{ $isActive
                                                ? 'text-zinc-400 hover:text-amber-600 hover:bg-amber-50'
                                                : 'text-zinc-400 hover:text-agro-600 hover:bg-agro-50' }}">
                                        <flux:icon icon="{{ $isActive ? 'lock-closed' : 'lock-open' }}" class="size-4" />
                                    </button>

                                    @if($campaign->activities_count === 0)
                                        <button
                                            wire:click="delete({{ $campaign->id }})"
                                            wire:loading.attr="disabled"
                                            wire:confirm="¿Eliminar esta campaña permanentemente?"
                                            title="Eliminar campaña"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-red-500 hover:bg-red-50 transition-colors">
                                            <flux:icon icon="trash" class="size-4" />
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </x-slot:footer>
                    </x-agro.card>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $campaigns->links() }}
            </div>
        @else
            <x-agro.empty-state
                icon="clipboard-document-list"
                title="{{ $search || $yearFilter ? 'Ninguna campaña coincide con los filtros' : 'Sin campañas registradas' }}"
                description="{{ $search || $yearFilter ? 'Prueba a cambiar o limpiar los filtros aplicados.' : 'Crea tu primera campaña de vendimia para empezar a registrar recepciones.' }}"
            >
                @if($search || $yearFilter)
                    <x-slot:action>
                        <flux:button wire:click="clearFilters" variant="outline" icon="x-mark">
                            Limpiar filtros
                        </flux:button>
                    </x-slot:action>
                @else
                    <x-slot:action>
                        <flux:button href="{{ roleRoute('campaigns.create') }}" wire:navigate variant="primary" icon="plus">
                            Nueva Campaña
                        </flux:button>
                    </x-slot:action>
                @endif
            </x-agro.empty-state>
        @endif
    </div>

</div>

