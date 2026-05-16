<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Clientes"
        description="Gestiona tus clientes y analiza tu cartera"
    />

    {{-- Stats --}}
    <div x-data="{
        open: localStorage.getItem('winery-clients-stats-open') !== 'false',
        toggle() {
            this.open = !this.open;
            localStorage.setItem('winery-clients-stats-open', String(this.open));
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
                    label="Total clientes"
                    :value="$stats['total']"
                    description="'Cartera total'"
                    icon="user-group"
                    color="agro"
                />
                <x-agro.stat-card
                    label="Activos"
                    :value="$stats['active']"
                    description="'Clientes activos'"
                    icon="check-circle"
                    color="agro"
                />
                <x-agro.stat-card
                    label="Inactivos"
                    :value="$stats['inactive']"
                    description="$stats['inactive'] > 0 ? 'Clientes archivados' : 'Todos activos'"
                    icon="archive-box"
                    color="zinc"
                />
                <x-agro.stat-card
                    label="Alta este año"
                    :value="\App\Models\Client::where('user_id', auth()->id())->whereYear('created_at', date('Y'))->count()"
                    description="'Nuevos en 2026'"
                    icon="user-plus"
                    color="blue"
                />
            </div>
        </div>
    </div>
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

            <div class="flex-1 relative">
                <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                    <flux:icon icon="magnifying-glass" class="size-4 text-zinc-400" />
                </div>
                <input
                    wire:model.live.debounce.300ms="search"
                    type="text"
                    placeholder="Buscar por nombre, email, teléfono o documento..."
                    class="w-full pl-9 pr-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm placeholder:text-zinc-400 shadow-sm focus:outline-none focus:ring-2 focus:ring-agro-500 focus:border-transparent transition"
                />
            </div>

            @php $filterCount = $filterType ? 1 : 0; @endphp
            <button
                x-on:click="$dispatch('open-modal', 'client-filters')"
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
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                        <flux:icon icon="magnifying-glass" class="size-3" />
                        "{{ $search }}"
                        <button wire:click="$set('search', '')" class="hover:text-agro-900 ml-0.5">
                            <flux:icon icon="x-mark" class="size-3" />
                        </button>
                    </span>
                @endif

                @if($filterType)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                        {{ $filterType === 'individual' ? 'Particular' : 'Empresa' }}
                        <button wire:click="$set('filterType', '')" class="hover:text-agro-900 ml-0.5">
                            <flux:icon icon="x-mark" class="size-3" />
                        </button>
                    </span>
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
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 {{ $isCompany ? 'bg-blue-50' : 'bg-agro-50' }} rounded-full flex items-center justify-center shrink-0">
                                <flux:icon icon="{{ $isCompany ? 'building-office' : 'user' }}" class="size-4 {{ $isCompany ? 'text-blue-500' : 'text-agro-600' }}" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-zinc-900 text-sm truncate leading-tight">{{ $client->full_name }}</p>
                                @if($isCompany && $client->company_name)
                                    <p class="text-xs text-zinc-400 leading-tight mt-0.5 truncate">{{ $client->company_name }}</p>
                                @endif
                            </div>
                            <flux:badge :color="$isCompany ? 'blue' : null" size="sm" class="shrink-0">
                                {{ $isCompany ? 'Empresa' : 'Particular' }}
                            </flux:badge>
                        </div>
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
