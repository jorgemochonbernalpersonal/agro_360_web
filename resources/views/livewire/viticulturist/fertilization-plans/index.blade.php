<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Plan de Fertilización"
        description="Gestión de planes de fertilización y nitrógenos por campaña"
        icon="funnel"
    >
        <x-slot:actions>
            <flux:button href="{{ roleRoute('viticulturist.fertilization-plans.create') }}" variant="primary" icon="plus">
                Nuevo Plan
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Stats colapsables --}}
    <div x-data="{
        open: localStorage.getItem('fertilization-plans-stats-open') !== 'false',
        toggle() {
            this.open = !this.open;
            localStorage.setItem('fertilization-plans-stats-open', String(this.open));
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
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <x-agro.stat-card
                    label="Borradores"
                    :value="$stats['draft']"
                    icon="document"
                    color="zinc"
                />
                <x-agro.stat-card
                    label="Activos"
                    :value="$stats['active']"
                    icon="check-circle"
                    color="green"
                />
                <x-agro.stat-card
                    label="Archivados"
                    :value="$stats['archived']"
                    icon="archive-box"
                    color="amber"
                />
                <x-agro.stat-card
                    label="Zona vulnerable nitratos"
                    :value="$stats['nitrate_zone']"
                    description="Planes en zona VN"
                    icon="exclamation-triangle"
                    color="red"
                />
            </div>
        </div>
    </div>

    {{-- Toolbar --}}
    @php
        $filterCount = (int)(!empty($filterCampaign)) + (int)(!empty($filterStatus));
    @endphp

    <div class="flex items-center gap-3 flex-wrap">

        <div class="flex-1 relative min-w-48">
            <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                <flux:icon icon="magnifying-glass" class="size-4 text-zinc-400" />
            </div>
            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                placeholder="Buscar por preparador o año..."
                class="w-full pl-9 pr-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm placeholder:text-zinc-400 shadow-sm focus:outline-none focus:ring-2 focus:ring-agro-500 focus:border-transparent transition"
            />
        </div>

        <button
            x-on:click="$dispatch('open-modal', 'fertilization-plans-filters')"
            class="relative inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm font-medium text-zinc-700 hover:bg-zinc-50 shadow-sm transition-colors"
        >
            <flux:icon icon="adjustments-horizontal" class="size-4 text-zinc-500" />
            Filtros
            @if($filterCount > 0)
                <span class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-agro-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center leading-none">
                    {{ $filterCount }}
                </span>
            @endif
        </button>

    </div>

    {{-- Chips de filtros activos --}}
    @if($search || $filterCampaign || $filterStatus)
        <div class="flex flex-wrap items-center gap-2">
            @if($search)
                <span class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                    <flux:icon icon="magnifying-glass" class="size-3" />
                    "{{ $search }}"
                    <button wire:click="$set('search', '')" class="ml-0.5 p-0.5 rounded-full hover:bg-agro-200 transition-colors">
                        <flux:icon icon="x-mark" class="size-3" />
                    </button>
                </span>
            @endif
            @if($filterCampaign)
                @php $selectedCampaign = $campaigns->firstWhere('id', $filterCampaign); @endphp
                <span class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                    Campaña {{ $selectedCampaign?->year ?? $filterCampaign }}
                    <button wire:click="$set('filterCampaign', '')" class="ml-0.5 p-0.5 rounded-full hover:bg-agro-200 transition-colors">
                        <flux:icon icon="x-mark" class="size-3" />
                    </button>
                </span>
            @endif
            @if($filterStatus)
                <span class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                    {{ $statuses[$filterStatus] ?? $filterStatus }}
                    <button wire:click="$set('filterStatus', '')" class="ml-0.5 p-0.5 rounded-full hover:bg-agro-200 transition-colors">
                        <flux:icon icon="x-mark" class="size-3" />
                    </button>
                </span>
            @endif
            <button wire:click="clearFilters" class="text-xs text-zinc-400 hover:text-zinc-600 transition-colors">
                Limpiar todo
            </button>
        </div>
    @endif

    {{-- Card grid --}}
    @php
        $btnBase   = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors';
        $btnDanger = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-red-500 hover:bg-red-50 transition-colors';
        $btnGreen  = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-green-600 hover:bg-green-50 transition-colors';
        $btnAmber  = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-amber-600 hover:bg-amber-50 transition-colors';
        $statusIconBg = [
            'draft'    => 'bg-zinc-100',
            'active'   => 'bg-green-100',
            'archived' => 'bg-amber-100',
        ];
        $statusIconColor = [
            'draft'    => 'text-zinc-500',
            'active'   => 'text-green-600',
            'archived' => 'text-amber-600',
        ];
    @endphp

    @if($entries->count() > 0)

        <div
            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6"
            wire:loading.class="opacity-60 pointer-events-none"
            wire:target="search, filterCampaign, filterStatus, clearFilters, activate, archive, delete"
        >
            @foreach($entries as $i => $plan)
                @php
                    $iconBg    = $statusIconBg[$plan->status]    ?? 'bg-zinc-100';
                    $iconColor = $statusIconColor[$plan->status] ?? 'text-zinc-500';
                @endphp
                <x-agro.card
                    wire:key="plan-{{ $plan->id }}"
                    class="animate-fade-in-up hover:-translate-y-1 flex flex-col"
                    style="animation-delay: {{ min($i * 50, 400) }}ms"
                >
                    <x-slot:header>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 {{ $iconBg }} rounded-xl flex items-center justify-center shrink-0">
                                <flux:icon icon="funnel" class="size-5 {{ $iconColor }}" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-bold text-zinc-900 leading-tight">{{ $plan->plan_year }}</p>
                                @if($plan->prepared_by)
                                    <p class="text-xs text-zinc-400 leading-tight mt-0.5 truncate">{{ $plan->prepared_by }}</p>
                                @endif
                            </div>
                            <flux:badge color="{{ $plan->status_color }}" size="sm" class="shrink-0">
                                {{ $plan->status_label }}
                            </flux:badge>
                        </div>
                    </x-slot:header>

                    <div class="flex-1 space-y-3">

                        {{-- Zona VN --}}
                        @if($plan->nitrate_zone)
                            <div class="inline-flex items-center gap-1.5 px-2 py-1 bg-red-50 border border-red-200 rounded-lg">
                                <flux:icon icon="exclamation-triangle" class="size-3.5 text-red-500 shrink-0" />
                                <span class="text-xs font-semibold text-red-700">Zona VN</span>
                            </div>
                        @endif

                        {{-- Campaña --}}
                        @if($plan->campaign)
                            <div class="flex items-center gap-2 text-xs text-zinc-500">
                                <flux:icon icon="calendar-days" class="size-3.5 text-zinc-400 shrink-0" />
                                <span>Campaña {{ $plan->campaign->year }}</span>
                            </div>
                        @endif

                        {{-- Fecha de aprobación --}}
                        @if($plan->approval_date)
                            <div class="flex items-center gap-2 text-xs text-zinc-500">
                                <flux:icon icon="check-badge" class="size-3.5 text-zinc-400 shrink-0" />
                                <span>Aprobado: {{ $plan->approval_date->format('d/m/Y') }}</span>
                            </div>
                        @endif

                        {{-- Superficie --}}
                        @if($plan->total_surface_ha !== null)
                            <div class="flex items-center gap-2 text-xs text-zinc-500">
                                <flux:icon icon="map" class="size-3.5 text-zinc-400 shrink-0" />
                                <span>{{ number_format($plan->total_surface_ha, 2) }} ha</span>
                            </div>
                        @endif

                        {{-- N / P / K grid --}}
                        @if($plan->total_n_kg_ha !== null || $plan->total_p_kg_ha !== null || $plan->total_k_kg_ha !== null)
                            <div class="bg-zinc-50 rounded-xl p-2.5 grid grid-cols-3 gap-2">
                                <div class="text-center">
                                    <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest">N</p>
                                    <p class="text-sm font-bold text-zinc-800">
                                        {{ $plan->total_n_kg_ha !== null ? number_format($plan->total_n_kg_ha, 1) : '—' }}
                                    </p>
                                    <p class="text-[10px] text-zinc-400">kg/ha</p>
                                </div>
                                <div class="text-center border-x border-zinc-200">
                                    <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest">P</p>
                                    <p class="text-sm font-bold text-zinc-800">
                                        {{ $plan->total_p_kg_ha !== null ? number_format($plan->total_p_kg_ha, 1) : '—' }}
                                    </p>
                                    <p class="text-[10px] text-zinc-400">kg/ha</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest">K</p>
                                    <p class="text-sm font-bold text-zinc-800">
                                        {{ $plan->total_k_kg_ha !== null ? number_format($plan->total_k_kg_ha, 1) : '—' }}
                                    </p>
                                    <p class="text-[10px] text-zinc-400">kg/ha</p>
                                </div>
                            </div>
                        @endif

                        {{-- Parcelas --}}
                        @if(!empty($plan->plan_lines))
                            <div class="flex items-center gap-2 text-xs text-zinc-400">
                                <flux:icon icon="list-bullet" class="size-3.5 shrink-0" />
                                <span>{{ count($plan->plan_lines) }} parcela(s)</span>
                            </div>
                        @endif

                    </div>

                    <x-slot:footer>
                        <div class="flex items-center justify-end gap-0.5">
                            <a href="{{ roleRoute('viticulturist.fertilization-plans.edit', $plan) }}"
                               title="Editar"
                               class="{{ $btnBase }}">
                                <flux:icon icon="pencil-square" class="size-4" />
                            </a>

                            @if($plan->status === 'draft')
                                <button
                                    wire:click="activate({{ $plan->id }})"
                                    wire:loading.attr="disabled"
                                    wire:target="activate({{ $plan->id }})"
                                    title="Activar plan"
                                    class="{{ $btnGreen }}">
                                    <flux:icon icon="check-circle" class="size-4" />
                                </button>
                            @endif

                            @if($plan->status === 'active')
                                <button
                                    wire:click="archive({{ $plan->id }})"
                                    wire:loading.attr="disabled"
                                    wire:target="archive({{ $plan->id }})"
                                    wire:confirm="¿Archivar este plan? Quedará inactivo."
                                    title="Archivar plan"
                                    class="{{ $btnAmber }}">
                                    <flux:icon icon="archive-box" class="size-4" />
                                </button>
                            @endif

                            @if($plan->status === 'draft')
                                <button
                                    wire:click="delete({{ $plan->id }})"
                                    wire:loading.attr="disabled"
                                    wire:target="delete({{ $plan->id }})"
                                    wire:confirm="¿Eliminar este plan de fertilización? Esta acción no se puede deshacer."
                                    title="Eliminar plan"
                                    class="{{ $btnDanger }}">
                                    <flux:icon icon="trash" class="size-4" />
                                </button>
                            @endif
                        </div>
                    </x-slot:footer>
                </x-agro.card>
            @endforeach
        </div>

        @if($entries->hasPages())
            <div class="flex justify-center">{{ $entries->links() }}</div>
        @endif

    @else

        <x-agro.empty-state
            icon="funnel"
            message="No hay planes de fertilización"
            :description="($search || $filterCampaign || $filterStatus) ? 'Ningún plan coincide con los filtros aplicados.' : 'Crea tu primer plan de fertilización para esta campaña.'"
        >
            @if($search || $filterCampaign || $filterStatus)
                <x-slot:action>
                    <flux:button wire:click="clearFilters" variant="outline" icon="x-mark">Limpiar filtros</flux:button>
                </x-slot:action>
            @else
                <x-slot:action>
                    <flux:button href="{{ roleRoute('viticulturist.fertilization-plans.create') }}" variant="primary" icon="plus">
                        Crear primer plan
                    </flux:button>
                </x-slot:action>
            @endif
        </x-agro.empty-state>

    @endif

    {{-- Wire loading skeleton --}}
    <div
        wire:loading.flex
        wire:target="search, filterCampaign, filterStatus, clearFilters"
        class="hidden fixed inset-0 z-20 items-center justify-center bg-white/40 backdrop-blur-sm"
    >
        <div class="flex items-center gap-3 bg-white rounded-2xl shadow-xl px-6 py-4 border border-zinc-100">
            <svg class="animate-spin size-5 text-agro-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <span class="text-sm font-medium text-zinc-600">Cargando...</span>
        </div>
    </div>

    {{-- Modal Filtros --}}
    <x-agro.modal name="fertilization-plans-filters" maxWidth="sm">
        <div class="px-6 py-4 border-b border-zinc-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-agro-100 rounded-lg flex items-center justify-center">
                        <flux:icon icon="adjustments-horizontal" class="size-4 text-agro-600" />
                    </div>
                    <h3 class="text-base font-semibold text-zinc-900">Filtros</h3>
                </div>
                <flux:button x-on:click="$dispatch('close-modal', 'fertilization-plans-filters')" variant="ghost" size="sm" icon="x-mark" />
            </div>
        </div>

        <div class="px-6 py-5 space-y-4">
            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Campaña</label>
                <select wire:model.live="filterCampaign"
                        class="w-full px-3 py-2 text-sm bg-white border border-zinc-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-agro-400 focus:border-transparent">
                    <option value="">Todas las campañas</option>
                    @foreach($campaigns as $campaign)
                        <option value="{{ $campaign->id }}">Campaña {{ $campaign->year }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Estado</label>
                <select wire:model.live="filterStatus"
                        class="w-full px-3 py-2 text-sm bg-white border border-zinc-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-agro-400 focus:border-transparent">
                    <option value="">Todos los estados</option>
                    @foreach($statuses as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="px-6 py-4 bg-zinc-50 border-t border-zinc-200 flex items-center justify-between rounded-b-2xl">
            <button wire:click="clearFilters" x-on:click="$dispatch('close-modal', 'fertilization-plans-filters')"
                    class="text-sm text-zinc-500 hover:text-zinc-700 transition-colors">
                Limpiar filtros
            </button>
            <flux:button x-on:click="$dispatch('close-modal', 'fertilization-plans-filters')" variant="primary" size="sm">
                Aplicar
            </flux:button>
        </div>
    </x-agro.modal>

</div>
