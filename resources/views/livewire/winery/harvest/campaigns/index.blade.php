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
    <x-agro.stats-section key="harvest-campaigns">
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
    </x-agro.stats-section>

    {{-- Toolbar --}}
    <div class="flex items-center gap-3">
        <x-agro.search-input wire:model.live.debounce.300ms="search" placeholder="Buscar campaña por nombre..." />

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
    <x-agro.loading-grid target="search, yearFilter, clearFilters, nextPage, previousPage" :count="6" :cols="3" />

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
                            <x-agro.card-item-header
                                icon="clipboard-document-list"
                                :title="$campaign->name"
                                :subtitle="'Vendimia ' . $campaign->year"
                                :iconBg="$isActive ? 'bg-agro-100' : 'bg-zinc-100'"
                                :iconColor="$isActive ? 'text-agro-600' : 'text-zinc-400'"
                                size="md"
                                radius="xl"
                            >
                                @if($isActive)
                                    <flux:badge color="green" size="sm">Activa</flux:badge>
                                @elseif($isLocked)
                                    <flux:badge color="indigo" size="sm">Bloqueada</flux:badge>
                                @else
                                    <flux:badge color="zinc" size="sm">Cerrada</flux:badge>
                                @endif
                            </x-agro.card-item-header>
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
                                    <x-agro.action-button icon="archive-box-arrow-down" variant="primary" href="{{ roleRoute('grape-reception.index', ['campaignFilter' => $campaign->id]) }}" wire:navigate title="Ver recepciones" />
                                </div>

                                <div class="w-px h-5 bg-zinc-200 mx-1"></div>

                                <div class="flex items-center gap-0.5">
                                    @if(!$isLocked)
                                        <x-agro.action-button variant="edit" href="{{ roleRoute('campaigns.edit', $campaign) }}" wire:navigate title="Editar campaña" />
                                    @endif

                                    @if($isActive)
                                        <x-agro.action-button icon="lock-closed" variant="warning" wire:click="toggleActive({{ $campaign->id }})" wire:loading.attr="disabled" title="Cerrar campaña" />
                                    @else
                                        <x-agro.action-button icon="lock-open" variant="primary" wire:click="toggleActive({{ $campaign->id }})" wire:loading.attr="disabled" title="Activar campaña" />
                                    @endif

                                    @if($campaign->activities_count === 0)
                                        <x-agro.action-button variant="delete" wire:click="delete({{ $campaign->id }})" wire:loading.attr="disabled" wire:confirm="¿Eliminar esta campaña permanentemente?" title="Eliminar campaña" />
                                    @endif
                                </div>
                            </div>
                        </x-slot:footer>
                    </x-agro.card>
                @endforeach
            </div>

            <x-agro-pagination :paginator="$campaigns" />
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

