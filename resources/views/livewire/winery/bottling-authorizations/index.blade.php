<div class="space-y-6 animate-fade-in">

    {{-- Header --}}
    <x-agro.page-header
        title="{{ __('Autorizaciones de Embotellado') }}"
        :description="__('Gestión de autorizaciones oficiales para el embotellado de vinos')"
        icon="identification"
    >
        <x-slot:actions>
            <flux:button variant="primary" icon="plus" href="{{ roleRoute('bottling-authorizations.create') }}" wire:navigate>
                Nueva autorización
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- KPIs --}}
    <x-agro.stats-section key="bottling-authorizations">
        <x-agro.stat-card
            :label="__('Total autorizaciones')"
            :value="$stats['total']"
            icon="identification"
            color="zinc"
        />
        <x-agro.stat-card
            :label="__('Activas')"
            :value="$stats['active']"
            icon="check-circle"
            color="agro"
        />
        <x-agro.stat-card
            :label="__('Próximas a vencer')"
            :value="$stats['expiring']"
            icon="clock"
            color="amber"
        />
        <x-agro.stat-card
            :label="__('Caducadas')"
            :value="$stats['expired']"
            icon="x-circle"
            color="zinc"
        />
    </x-agro.stats-section>

    {{-- Toolbar --}}
    <div class="flex items-center gap-3">
        <x-agro.search-input wire:model.live.debounce.300ms="search" :placeholder="__('Buscar por número, organismo...')" />

        <flux:select wire:model.live="typeFilter" class="w-44">
            <flux:select.option value="">{{ __('Todos los tipos') }}</flux:select.option>
            @foreach($types as $key => $label)
                <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select wire:model.live="statusFilter" class="w-40">
            <flux:select.option value="">{{ __('Todos los estados') }}</flux:select.option>
            @foreach($statuses as $key => $label)
                <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
            @endforeach
        </flux:select>

        @if($search || $typeFilter || $statusFilter)
            <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">{{ __('Limpiar') }}</flux:button>
        @endif
    </div>

    {{-- Loading skeleton --}}
    <x-agro.loading-grid target="search, typeFilter, statusFilter, clearFilters, nextPage, previousPage" :cols="3" :count="6" />

    {{-- Grid de cards --}}
    <div wire:loading.remove wire:target="search, typeFilter, statusFilter, clearFilters, nextPage, previousPage">
        @if($authorizations->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($authorizations as $auth)
                    @php
                        $delay = min($loop->index * 50, 300);
                        $statusConfig = match($auth->status) {
                            'active'    => ['badge' => 'green',  'bg' => 'bg-agro-100',  'icon' => 'text-agro-600'],
                            'used'      => ['badge' => 'blue',   'bg' => 'bg-blue-100',  'icon' => 'text-blue-600'],
                            'expired'   => ['badge' => 'red',    'bg' => 'bg-red-100',    'icon' => 'text-red-600'],
                            'cancelled' => ['badge' => 'zinc',   'bg' => 'bg-zinc-100',   'icon' => 'text-zinc-400'],
                            default     => ['badge' => 'zinc',   'bg' => 'bg-zinc-100',   'icon' => 'text-zinc-400'],
                        };
                    @endphp
                    <x-agro.card
                        class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                        style="animation-delay: {{ $delay }}ms;"
                        wire:key="auth-{{ $auth->id }}"
                    >
                        <x-slot:header>
                            <x-agro.card-item-header
                                icon="identification"
                                :title="$auth->authorization_number"
                                :subtitle="$auth->issuing_authority ?? null"
                                :iconBg="$statusConfig['bg']"
                                :iconColor="$statusConfig['icon']"
                                size="md"
                                radius="xl"
                            >
                                <flux:badge color="{{ $statusConfig['badge'] }}" size="sm">
                                    {{ $auth->status_label }}
                                </flux:badge>
                            </x-agro.card-item-header>
                        </x-slot:header>

                        <div class="flex-1 space-y-3">
                            {{-- Tipo --}}
                            <flux:badge color="blue" size="sm">{{ $auth->type_label }}</flux:badge>

                            {{-- Vino --}}
                            @if($auth->wine)
                                <div class="flex items-center gap-2 text-sm text-zinc-600">
                                    <flux:icon icon="beaker" class="size-4 text-zinc-400 shrink-0" />
                                    <span class="truncate font-medium">{{ $auth->wine->name }}</span>
                                </div>
                            @endif

                            {{-- Volumen autorizado --}}
                            @if($auth->authorized_volume_liters !== null)
                                <div class="bg-zinc-50 rounded-xl p-3">
                                    <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest mb-0.5">{{ __('Volumen autorizado') }}</p>
                                    <p class="text-xl font-bold text-zinc-700 leading-none">
                                        {{ number_format($auth->authorized_volume_liters, 2) }}<span class="text-sm font-normal text-zinc-400 ml-1">L</span>
                                    </p>
                                </div>
                            @endif

                            {{-- Válido hasta --}}
                            @if($auth->valid_until)
                                <div class="flex items-center gap-2 text-xs {{ $auth->isExpiringSoon() ? 'text-amber-600 font-medium' : 'text-zinc-500' }}">
                                    <flux:icon icon="clock" class="size-3.5 {{ $auth->isExpiringSoon() ? 'text-amber-500' : 'text-zinc-400' }}" />
                                    <span>Válido hasta: {{ $auth->valid_until->format('d/m/Y') }}</span>
                                    @if($auth->isExpiringSoon())
                                        <flux:badge color="amber" size="sm">{{ __('Próximo') }}</flux:badge>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <x-slot:footer>
                            <div class="flex items-center justify-end gap-0.5">
                                <x-agro.action-button variant="edit" href="{{ roleRoute('bottling-authorizations.edit', $auth) }}" wire:navigate title="{{ __('Editar autorización') }}" />
                                <x-agro.action-button variant="delete" wire:click="delete({{ $auth->id }})" wire:confirm="{{ __('¿Eliminar esta autorización de embotellado?') }}" wire:loading.attr="disabled" title="{{ __('Eliminar autorización') }}" />
                            </div>
                        </x-slot:footer>
                    </x-agro.card>
                @endforeach
            </div>

            <x-agro-pagination :paginator="$authorizations" />
        @else
            <x-agro.empty-state
                icon="identification"
                title="{{ $search || $typeFilter || $statusFilter ? 'Ninguna autorización coincide con los filtros' : 'Sin autorizaciones registradas' }}"
                description="{{ $search || $typeFilter || $statusFilter ? 'Prueba a cambiar o limpiar los filtros aplicados.' : 'Añade las autorizaciones de embotellado de la bodega.' }}"
            >
                @if($search || $typeFilter || $statusFilter)
                    <x-slot:action>
                        <flux:button wire:click="clearFilters" variant="outline" icon="x-mark">{{ __('Limpiar filtros') }}</flux:button>
                    </x-slot:action>
                @else
                    <x-slot:action>
                        <flux:button href="{{ roleRoute('bottling-authorizations.create') }}" wire:navigate variant="primary" icon="plus">
                            Nueva autorización
                        </flux:button>
                    </x-slot:action>
                @endif
            </x-agro.empty-state>
        @endif
    </div>

</div>

