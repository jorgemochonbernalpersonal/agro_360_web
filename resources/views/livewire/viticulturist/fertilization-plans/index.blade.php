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
    <x-agro.stats-section key="fertilization-plans">
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
    </x-agro.stats-section>

    {{-- Toolbar --}}
    @php
        $filterCount = (int)(!empty($filterCampaign)) + (int)(!empty($filterStatus));
    @endphp

    <div class="flex items-center gap-3 flex-wrap">

        <x-agro.search-input wire:model.live.debounce.300ms="search" placeholder="Buscar por preparador o año..." />

        <x-agro.filter-button modal="fertilization-plans-filters" :count="$filterCount" />

    </div>

    {{-- Chips de filtros activos --}}
    @if($search || $filterCampaign || $filterStatus)
        <div class="flex flex-wrap items-center gap-2">
            @if($search)
                <x-agro.filter-chip icon="magnifying-glass" :label="'\"' . $search . '\"'" wireRemove="$set('search', '')" />
            @endif
            @if($filterCampaign)
                @php $selectedCampaign = $campaigns->firstWhere('id', $filterCampaign); @endphp
                <x-agro.filter-chip :label="'Campaña ' . ($selectedCampaign?->year ?? $filterCampaign)" wireRemove="$set('filterCampaign', '')" />
            @endif
            @if($filterStatus)
                <x-agro.filter-chip :label="$statuses[$filterStatus] ?? $filterStatus" wireRemove="$set('filterStatus', '')" />
            @endif
            <button wire:click="clearFilters" class="text-xs text-zinc-400 hover:text-zinc-600 transition-colors">
                Limpiar todo
            </button>
        </div>
    @endif

    {{-- Card grid --}}
    @php
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
                        <x-agro.card-item-header
                            icon="funnel"
                            :title="(string) $plan->plan_year"
                            :subtitle="$plan->prepared_by ?? null"
                            :iconBg="$iconBg"
                            :iconColor="$iconColor"
                            size="md"
                            radius="xl"
                        >
                            <flux:badge color="{{ $plan->status_color }}" size="sm">
                                {{ $plan->status_label }}
                            </flux:badge>
                        </x-agro.card-item-header>
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
                            <x-agro.action-button
                                variant="edit"
                                href="{{ roleRoute('viticulturist.fertilization-plans.edit', $plan) }}"
                                title="Editar"
                            />

                            @if($plan->status === 'draft')
                                <x-agro.action-button
                                    variant="activate"
                                    wire:click="activate({{ $plan->id }})"
                                    wire:loading.attr="disabled"
                                    title="Activar plan"
                                />
                            @endif

                            @if($plan->status === 'active')
                                <x-agro.action-button
                                    variant="archive"
                                    wire:click="archive({{ $plan->id }})"
                                    wire:loading.attr="disabled"
                                    wire:confirm="¿Archivar este plan? Quedará inactivo."
                                    title="Archivar plan"
                                />
                            @endif

                            @if($plan->status === 'draft')
                                <x-agro.action-button
                                    variant="delete"
                                    wire:click="delete({{ $plan->id }})"
                                    wire:loading.attr="disabled"
                                    wire:confirm="¿Eliminar este plan de fertilización? Esta acción no se puede deshacer."
                                    title="Eliminar plan"
                                />
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
            <flux:icon icon="arrow-path" class="animate-spin size-5 text-agro-500" />
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
            <x-agro.filter-select label="Campaña" wire:model.live="filterCampaign" placeholder="Todas las campañas">
                @foreach($campaigns as $campaign)
                    <flux:select.option value="{{ $campaign->id }}">Campaña {{ $campaign->year }}</flux:select.option>
                @endforeach
            </x-agro.filter-select>

            <x-agro.filter-select label="Estado" wire:model.live="filterStatus" placeholder="Todos los estados">
                @foreach($statuses as $value => $label)
                    <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                @endforeach
            </x-agro.filter-select>
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
