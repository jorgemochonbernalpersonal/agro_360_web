<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Clientes"
        description="Gestiona tus clientes y analiza tu cartera"
    />

    {{-- Stats --}}
    <x-agro.stats-section key="winery-clients">
        <div class="grid grid-cols-2 gap-4">
            <x-agro.stat-card
                label="Total clientes"
                :value="$stats['total']"
                description="Cartera total"
                icon="user-group"
                color="agro"
            />
            <x-agro.stat-card
                label="Activos"
                :value="$stats['active']"
                description="Clientes activos"
                icon="check-circle"
                color="agro"
            />
            <x-agro.stat-card
                label="Inactivos"
                :value="$stats['inactive']"
                :description="$stats['inactive'] > 0 ? 'Clientes archivados' : 'Todos activos'"
                icon="archive-box"
                color="zinc"
            />
            <x-agro.stat-card
                label="Alta este año"
                :value="\App\Models\Client::where('user_id', auth()->id())->whereYear('created_at', date('Y'))->count()"
                description="Nuevos este año"
                icon="user-plus"
                color="blue"
            />
        </div>
    </x-agro.stats-section>
    {{-- Tabs --}}
    <x-agro.tabs
        :tabs="[
            'active'   => ['label' => 'Activos',   'count' => $stats['active']],
            'inactive' => ['label' => 'Inactivos', 'count' => $stats['inactive']],
        ]"
        :active="$currentTab"
        wireMethod="switchTab"
    />

    {{-- Toolbar --}}
    <div class="space-y-3">
        <div class="flex items-center gap-3">

            <x-agro.search-input wire:model.live.debounce.300ms="search" placeholder="Buscar por nombre, email, teléfono o documento..." />

            @php $filterCount = $filterType ? 1 : 0; @endphp
            <x-agro.filter-button modal="client-filters" :count="$filterCount" />

            <div class="w-px h-8 bg-zinc-200 shrink-0"></div>

            <flux:button href="{{ roleRoute('clients.insights') }}" wire:navigate variant="ghost" icon="chart-bar">
                Insights
            </flux:button>

            <flux:button href="{{ roleRoute('clients.create') }}" wire:navigate variant="primary" icon="plus">
                Nuevo Cliente
            </flux:button>

        </div>

        {{-- Active filter chips --}}
        @if($search || $filterType)
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs text-zinc-400">Filtros activos:</span>

                @if($search)
                    <x-agro.filter-chip icon="magnifying-glass" :label="'&quot;' . $search . '&quot;'" wireRemove="$set('search', '')" />
                @endif

                @if($filterType)
                    <x-agro.filter-chip :label="$filterType === 'individual' ? 'Particular' : 'Empresa'" wireRemove="$set('filterType', '')" />
                @endif

                <button wire:click="clearFilters" class="text-xs text-zinc-400 hover:text-zinc-600 underline">
                    Limpiar todo
                </button>
            </div>
        @endif
    </div>

    {{-- Card grid --}}
    @if($clients->count() > 0)
        <div
            class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6"
            wire:loading.class="opacity-60 pointer-events-none"
            wire:target="search, filterType, clearFilters, switchTab"
        >
            @foreach($clients as $i => $client)
                @php
                    $isCompany  = $client->client_type === 'company';
                    $defaultAddress = $client->addresses?->where('is_default', true)->first()
                        ?? $client->addresses?->first();
                @endphp

                <x-agro.card
                    wire:key="client-{{ $client->id }}"
                    class="animate-fade-in-up hover:-translate-y-1 {{ !$client->active ? 'opacity-70' : '' }}"
                    style="animation-delay: {{ min($i * 50, 400) }}ms"
                >
                    <x-slot:header>
                        <x-agro.card-item-header
                            :icon="$isCompany ? 'building-office' : 'user'"
                            :title="$client->full_name"
                            :subtitle="($isCompany && $client->company_name) ? $client->company_name : null"
                            :iconBg="$isCompany ? 'bg-blue-50' : 'bg-agro-50'"
                            :iconColor="$isCompany ? 'text-blue-500' : 'text-agro-600'"
                        >
                            <flux:badge :color="$isCompany ? 'blue' : null" size="sm">
                                {{ $isCompany ? 'Empresa' : 'Particular' }}
                            </flux:badge>
                        </x-agro.card-item-header>
                    </x-slot:header>

                    {{-- Contacto --}}
                    <div class="space-y-1.5 mb-3">
                        @if($client->email)
                            <div class="flex items-center gap-2">
                                <flux:icon icon="envelope" class="size-3.5 text-zinc-400 shrink-0" />
                                <span class="text-xs text-zinc-600 truncate">{{ $client->email }}</span>
                            </div>
                        @endif
                        @if($client->phone)
                            <div class="flex items-center gap-2">
                                <flux:icon icon="phone" class="size-3.5 text-zinc-400 shrink-0" />
                                <span class="text-xs text-zinc-600">{{ $client->phone }}</span>
                            </div>
                        @endif
                        @if(!$client->email && !$client->phone)
                            <p class="text-xs text-zinc-400 italic">Sin datos de contacto</p>
                        @endif
                    </div>

                    {{-- Dirección + Facturas --}}
                    <div class="grid grid-cols-2 gap-2">
                        <div class="bg-zinc-50 rounded-xl p-2.5">
                            <p class="text-[10px] text-zinc-500 font-medium uppercase tracking-wide mb-0.5">Ubicación</p>
                            @if($defaultAddress)
                                <p class="text-xs font-semibold text-zinc-700 truncate">
                                    {{ $defaultAddress->municipality?->name ?? $defaultAddress->address }}
                                </p>
                                @if($defaultAddress->province)
                                    <p class="text-[10px] text-zinc-400 truncate">{{ $defaultAddress->province->name }}</p>
                                @endif
                            @else
                                <p class="text-xs text-zinc-400 italic">Sin dirección</p>
                            @endif
                        </div>
                        <div class="bg-agro-50 rounded-xl p-2.5">
                            <p class="text-[10px] text-agro-600 font-medium uppercase tracking-wide mb-0.5">Facturas</p>
                            <p class="text-sm font-bold text-agro-700">{{ $client->invoices->count() }}</p>
                        </div>
                    </div>

                    <x-slot:footer>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-1">
                                <x-agro.action-button variant="edit" href="{{ roleRoute('clients.edit', $client->id) }}" wire:navigate title="Editar" />
                            </div>
                            <div class="flex items-center gap-1">
                                @if($client->active)
                                    <x-agro.action-button variant="deactivate" wire:click="toggleActive({{ $client->id }})" wire:loading.attr="disabled" title="Desactivar" />
                                @else
                                    <x-agro.action-button variant="activate" wire:click="toggleActive({{ $client->id }})" wire:loading.attr="disabled" title="Activar" />
                                @endif
                            </div>
                        </div>
                    </x-slot:footer>
                </x-agro.card>
            @endforeach
        </div>

        @if($clients->hasPages())
            <div class="flex justify-center">{{ $clients->links() }}</div>
        @endif

    @else
        <x-agro.empty-state
            icon="users"
            message="{{ $currentTab === 'active' ? 'No hay clientes activos' : 'No hay clientes inactivos' }}"
            description="{{ $search || $filterType ? 'Ningún cliente coincide con los filtros aplicados.' : ($currentTab === 'active' ? 'Crea tu primer cliente para empezar a gestionar tu cartera.' : 'Los clientes desactivados aparecerán aquí.') }}"
        >
            @if($search || $filterType)
                <x-slot:action>
                    <flux:button wire:click="clearFilters" variant="outline" icon="x-mark">Limpiar filtros</flux:button>
                </x-slot:action>
            @elseif($currentTab === 'active')
                <x-slot:action>
                    <flux:button href="{{ roleRoute('clients.create') }}" wire:navigate variant="primary" icon="plus">
                        Nuevo Cliente
                    </flux:button>
                </x-slot:action>
            @endif
        </x-agro.empty-state>
    @endif

    {{-- Modal Filtros --}}
    <x-agro.modal name="client-filters" maxWidth="sm">
        <div class="px-6 py-4 border-b border-zinc-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-agro-100 rounded-lg flex items-center justify-center">
                        <flux:icon icon="adjustments-horizontal" class="size-4 text-agro-600" />
                    </div>
                    <h3 class="text-base font-semibold text-zinc-900">Filtros</h3>
                </div>
                <flux:button x-on:click="$dispatch('close-modal', 'client-filters')" variant="ghost" size="sm" icon="x-mark" />
            </div>
        </div>

        <div class="px-6 py-5">
            <x-agro.filter-select label="Tipo de cliente" wire:model.live="filterType" placeholder="Todos los tipos">
                <flux:select.option value="individual">Particular</flux:select.option>
                <flux:select.option value="company">Empresa</flux:select.option>
            </x-agro.filter-select>
        </div>

        <div class="px-6 py-4 bg-zinc-50 border-t border-zinc-200 flex items-center justify-between rounded-b-2xl">
            <button wire:click="clearFilters" x-on:click="$dispatch('close-modal', 'client-filters')"
                    class="text-sm text-zinc-500 hover:text-zinc-700 transition-colors">
                Limpiar filtros
            </button>
            <flux:button x-on:click="$dispatch('close-modal', 'client-filters')" variant="primary" size="sm">
                Aplicar
            </flux:button>
        </div>
    </x-agro.modal>

</div>
