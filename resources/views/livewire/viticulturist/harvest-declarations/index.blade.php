<div class="space-y-6 animate-fade-in">

    {{-- Header --}}
    <x-agro.page-header
        title="Declaraciones de Vendimia"
        description="Declaraciones oficiales de cosecha ante CCAA / DO (Reglamento UE 2018/273)"
        icon="document-arrow-up"
    >
        <x-slot:actions>
            <flux:button href="{{ roleRoute('viticulturist.harvest-declarations.create') }}" variant="primary" icon="plus">
                Nueva Declaración
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Stats (colapsables) --}}
    <x-agro.stats-section key="harvest-declarations">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <x-agro.stat-card
                label="Borradores"
                :value="$stats['draft']"
                description="Pendientes de presentar"
                icon="document"
                color="zinc"
            />
            <x-agro.stat-card
                label="Presentadas"
                :value="$stats['submitted']"
                description="Enviadas al organismo"
                icon="paper-airplane"
                color="blue"
            />
            <x-agro.stat-card
                label="Aceptadas"
                :value="$stats['accepted']"
                description="Confirmadas oficialmente"
                icon="check-circle"
                color="green"
            />
            <x-agro.stat-card
                label="Rechazadas"
                :value="$stats['rejected']"
                :description="$stats['rejected'] > 0 ? 'Requieren corrección' : 'Sin rechazos'"
                icon="x-circle"
                color="red"
            />
        </div>
    </x-agro.stats-section>

    {{-- Toolbar --}}
    @php
        $filterCount = (int) !empty($filterCampaign) + (int) !empty($filterStatus);
    @endphp
    <div class="flex items-center gap-3">
        <x-agro.search-input wire:model.live.debounce.300ms="search" placeholder="Buscar por organismo, referencia, año..." />
        <x-agro.filter-button modal="harvest-declarations-filters" :count="$filterCount" />
    </div>

    {{-- Chips filtros activos --}}
    @if($filterCampaign || $filterStatus)
        <div class="flex flex-wrap items-center gap-2">
            @if($filterCampaign)
                @php $camp = $campaigns->firstWhere('id', $filterCampaign); @endphp
                <x-agro.filter-chip icon="calendar-days" :label="$camp?->name ?? $filterCampaign" wireRemove="$set('filterCampaign', '')" />
            @endif
            @if($filterStatus)
                <x-agro.filter-chip icon="document-arrow-up" :label="$statuses[$filterStatus] ?? $filterStatus" wireRemove="$set('filterStatus', '')" />
            @endif
            <button wire:click="clearFilters" class="text-xs text-zinc-400 hover:text-zinc-600 transition-colors">Limpiar todo</button>
        </div>
    @endif

    {{-- Skeleton carga --}}
    <x-agro.loading-grid target="search, filterCampaign, filterStatus, nextPage, previousPage, gotoPage" />

    {{-- Grid de cards --}}
    <div wire:loading.remove wire:target="search, filterCampaign, filterStatus, nextPage, previousPage, gotoPage">
        @if($entries->isEmpty())
            <x-agro.empty-state
                icon="document-arrow-up"
                title="{{ $search || $filterCampaign || $filterStatus ? 'Sin resultados' : 'Sin declaraciones registradas' }}"
                description="{{ $search || $filterCampaign || $filterStatus ? 'Ninguna declaración coincide con los filtros aplicados.' : 'Crea la declaración de vendimia anual obligatoria ante la CCAA o Denominación de Origen correspondiente.' }}"
            >
                @if(!$search && !$filterCampaign && !$filterStatus)
                    <x-slot:action>
                        <flux:button href="{{ roleRoute('viticulturist.harvest-declarations.create') }}" variant="primary" icon="plus">
                            Nueva Declaración
                        </flux:button>
                    </x-slot:action>
                @endif
            </x-agro.empty-state>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($entries as $entry)
                    @php
                        $delay = min($loop->index * 50, 300);
                        $statusColor = match($entry->status) {
                            'draft'     => ['bg' => 'bg-zinc-100',  'icon' => 'text-zinc-500'],
                            'submitted' => ['bg' => 'bg-blue-100',  'icon' => 'text-blue-600'],
                            'accepted'  => ['bg' => 'bg-green-100', 'icon' => 'text-green-600'],
                            'rejected'  => ['bg' => 'bg-red-100',   'icon' => 'text-red-600'],
                            default     => ['bg' => 'bg-zinc-100',  'icon' => 'text-zinc-400'],
                        };
                    @endphp
                    <x-agro.card
                        class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                        style="animation-delay: {{ $delay }}ms;"
                        wire:key="hd-{{ $entry->id }}"
                    >
                        <x-slot:header>
                            <x-agro.card-item-header
                                icon="document-arrow-up"
                                :title="(string) $entry->declaration_year"
                                :subtitle="$entry->authority"
                                :iconBg="$statusColor['bg']"
                                :iconColor="$statusColor['icon']"
                                size="md"
                                radius="xl"
                            >
                                <x-agro.status-badge :color="$entry->status_color">
                                    {{ $entry->status_label }}
                                </x-agro.status-badge>
                            </x-agro.card-item-header>
                        </x-slot:header>

                        <div class="flex-1 space-y-4">
                            <div class="grid grid-cols-2 gap-3">
                                <div class="bg-agro-50 rounded-xl p-3">
                                    <p class="text-[10px] font-semibold text-agro-400 uppercase tracking-widest mb-1">Total kg</p>
                                    <p class="text-xl font-bold text-agro-700 leading-none">
                                        {{ $entry->total_kg ? number_format($entry->total_kg, 0, ',', '.') : '—' }}
                                        @if($entry->total_kg)
                                            <span class="text-xs font-medium text-zinc-400 ml-0.5">kg</span>
                                        @endif
                                    </p>
                                </div>
                                <div class="bg-zinc-50 rounded-xl p-3">
                                    <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest mb-1">Sup. (ha)</p>
                                    <p class="text-xl font-bold text-zinc-600 leading-none">
                                        {{ $entry->total_surface_ha ? number_format($entry->total_surface_ha, 2, ',', '.') : '—' }}
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-2 text-sm">
                                @if($entry->declaration_date)
                                    <div class="flex items-center gap-2 text-zinc-600">
                                        <flux:icon icon="calendar-days" class="size-4 text-zinc-400 shrink-0" />
                                        <span class="truncate">{{ $entry->declaration_date->format('d/m/Y') }}</span>
                                    </div>
                                @endif
                                @if($entry->submission_date)
                                    <div class="flex items-center gap-2 text-zinc-500">
                                        <flux:icon icon="paper-airplane" class="size-4 text-zinc-400 shrink-0" />
                                        <span class="truncate text-xs">Pres. {{ $entry->submission_date->format('d/m/Y') }}</span>
                                    </div>
                                @endif
                                @if($entry->reference_number)
                                    <div class="flex items-center gap-2 text-zinc-500">
                                        <flux:icon icon="identification" class="size-4 text-zinc-400 shrink-0" />
                                        <span class="truncate font-mono text-xs">{{ $entry->reference_number }}</span>
                                    </div>
                                @endif
                                @if($entry->status === 'rejected' && $entry->rejection_reason)
                                    <div class="mt-2 p-2 bg-red-50 rounded-lg">
                                        <p class="text-xs text-red-600 leading-snug">{{ Str::limit($entry->rejection_reason, 60) }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <x-slot:footer>
                            <div class="flex items-center justify-end gap-0.5">
                                <x-agro.action-button
                                    variant="edit"
                                    href="{{ roleRoute('viticulturist.harvest-declarations.edit', $entry) }}"
                                    title="Editar"
                                />
                                @if($entry->status === 'draft')
                                    <x-agro.action-button
                                        icon="paper-airplane"
                                        variant="primary"
                                        wire:click="markSubmitted({{ $entry->id }})"
                                        wire:confirm="¿Marcar como presentada ante {{ $entry->authority }}?"
                                        title="Marcar como presentada"
                                    />
                                    <x-agro.action-button
                                        variant="delete"
                                        wire:click="delete({{ $entry->id }})"
                                        wire:confirm="¿Eliminar este borrador permanentemente?"
                                        title="Eliminar borrador"
                                    />
                                @elseif($entry->status === 'submitted')
                                    <x-agro.action-button
                                        variant="activate"
                                        wire:click="markAccepted({{ $entry->id }})"
                                        wire:confirm="¿Confirmar aceptación de la declaración?"
                                        title="Marcar como aceptada"
                                    />
                                @endif
                            </div>
                        </x-slot:footer>
                    </x-agro.card>
                @endforeach
            </div>

            @if($entries->hasPages())
                <div class="mt-6">{{ $entries->links() }}</div>
            @endif
        @endif
    </div>

    {{-- Modal: Filtros --}}
    <x-agro.modal name="harvest-declarations-filters" maxWidth="sm">
        <div class="px-6 py-4 border-b border-zinc-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-agro-100 rounded-lg flex items-center justify-center">
                        <flux:icon icon="adjustments-horizontal" class="size-4 text-agro-600" />
                    </div>
                    <h3 class="text-base font-semibold text-zinc-900">Filtros</h3>
                </div>
                <flux:button x-on:click="$dispatch('close-modal', 'harvest-declarations-filters')" variant="ghost" size="sm" icon="x-mark" />
            </div>
        </div>
        <div class="px-6 py-5 space-y-5">
            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Campaña</label>
                <flux:select wire:model.live="filterCampaign">
                    <flux:select.option value="">Todas las campañas</flux:select.option>
                    @foreach($campaigns as $c)
                        <flux:select.option value="{{ $c->id }}">{{ $c->name }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Estado</label>
                <flux:select wire:model.live="filterStatus">
                    <flux:select.option value="">Todos los estados</flux:select.option>
                    @foreach($statuses as $key => $label)
                        <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-zinc-200 flex items-center justify-between">
            @if($filterCampaign || $filterStatus)
                <button wire:click="clearFilters" class="text-sm text-zinc-400 hover:text-zinc-600 transition-colors">Limpiar filtros</button>
            @else
                <span></span>
            @endif
            <flux:button x-on:click="$dispatch('close-modal', 'harvest-declarations-filters')" variant="primary">Aplicar</flux:button>
        </div>
    </x-agro.modal>

</div>
