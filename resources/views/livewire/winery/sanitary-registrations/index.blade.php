<div class="space-y-6 animate-fade-in">

    {{-- Header --}}
    <x-agro.page-header
        title="{{ __('Registros Sanitarios') }}"
        :description="__('Gestión de registros RGSEAA, RESA, RPO y otros registros sanitarios oficiales')"
        icon="shield-check"
    >
        <x-slot:actions>
            <flux:button variant="primary" icon="plus" href="{{ roleRoute('sanitary-registrations.create') }}" wire:navigate>
                Nuevo registro
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- KPIs --}}
    <x-agro.stats-section key="sanitary-registrations">
        <x-agro.stat-card
            :label="__('Total registros')"
            :value="$stats['total']"
            icon="shield-check"
            color="zinc"
        />
        <x-agro.stat-card
            :label="__('Activos')"
            :value="$stats['active']"
            icon="check-circle"
            color="agro"
        />
        <x-agro.stat-card
            :label="__('Próximos a vencer')"
            :value="$stats['expiring']"
            icon="clock"
            color="amber"
        />
        <x-agro.stat-card
            :label="__('Caducados')"
            :value="$stats['expired']"
            icon="x-circle"
            color="zinc"
        />
    </x-agro.stats-section>

    {{-- Toolbar --}}
    <div class="flex items-center gap-3">
        <x-agro.search-input wire:model.live.debounce.300ms="search" :placeholder="__('Buscar por número, descripción...')" />

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
        @if($registrations->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($registrations as $registration)
                    @php
                        $delay = min($loop->index * 50, 300);
                        $statusConfig = match($registration->status) {
                            'active'    => ['badge' => 'green',  'bg' => 'bg-agro-100',  'icon' => 'text-agro-600'],
                            'expired'   => ['badge' => 'red',    'bg' => 'bg-red-100',    'icon' => 'text-red-600'],
                            'suspended' => ['badge' => 'amber',  'bg' => 'bg-amber-100',  'icon' => 'text-amber-600'],
                            'cancelled' => ['badge' => 'zinc',   'bg' => 'bg-zinc-100',   'icon' => 'text-zinc-400'],
                            default     => ['badge' => 'zinc',   'bg' => 'bg-zinc-100',   'icon' => 'text-zinc-400'],
                        };
                    @endphp
                    <x-agro.card
                        class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                        style="animation-delay: {{ $delay }}ms;"
                        wire:key="reg-{{ $registration->id }}"
                    >
                        <x-slot:header>
                            <x-agro.card-item-header
                                icon="shield-check"
                                :title="$registration->registration_number"
                                :subtitle="$registration->issuing_authority ?? null"
                                :iconBg="$statusConfig['bg']"
                                :iconColor="$statusConfig['icon']"
                                size="md"
                                radius="xl"
                            >
                                <flux:badge color="{{ $statusConfig['badge'] }}" size="sm">
                                    {{ $registration->status_label }}
                                </flux:badge>
                            </x-agro.card-item-header>
                        </x-slot:header>

                        <div class="flex-1 space-y-3">
                            {{-- Tipo --}}
                            <flux:badge color="blue" size="sm">{{ $registration->type_label }}</flux:badge>

                            {{-- Descripción --}}
                            @if($registration->activity_description)
                                <p class="text-sm text-zinc-600 line-clamp-2">{{ $registration->activity_description }}</p>
                            @endif

                            {{-- Fechas --}}
                            <div class="space-y-1.5">
                                @if($registration->registration_date)
                                    <div class="flex items-center gap-2 text-xs text-zinc-500">
                                        <flux:icon icon="calendar-days" class="size-3.5 text-zinc-400" />
                                        <span>Registro: {{ $registration->registration_date->format('d/m/Y') }}</span>
                                    </div>
                                @endif
                                @if($registration->renewal_date)
                                    <div class="flex items-center gap-2 text-xs {{ $registration->isExpiringSoon() ? 'text-amber-600 font-medium' : 'text-zinc-500' }}">
                                        <flux:icon icon="clock" class="size-3.5 {{ $registration->isExpiringSoon() ? 'text-amber-500' : 'text-zinc-400' }}" />
                                        <span>Renovación: {{ $registration->renewal_date->format('d/m/Y') }}</span>
                                        @if($registration->isExpiringSoon())
                                            <flux:badge color="amber" size="sm">{{ __('Próximo') }}</flux:badge>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>

                        <x-slot:footer>
                            <div class="flex items-center justify-end gap-0.5">
                                <x-agro.action-button variant="edit" href="{{ roleRoute('sanitary-registrations.edit', $registration) }}" wire:navigate title="{{ __('Editar registro') }}" />
                                <x-agro.action-button variant="delete" wire:click="delete({{ $registration->id }})" wire:confirm="{{ __('¿Eliminar este registro sanitario?') }}" wire:loading.attr="disabled" title="{{ __('Eliminar registro') }}" />
                            </div>
                        </x-slot:footer>
                    </x-agro.card>
                @endforeach
            </div>

            <x-agro-pagination :paginator="$registrations" />
        @else
            <x-agro.empty-state
                icon="shield-check"
                title="{{ $search || $typeFilter || $statusFilter ? 'Ningún registro coincide con los filtros' : 'Sin registros sanitarios' }}"
                description="{{ $search || $typeFilter || $statusFilter ? 'Prueba a cambiar o limpiar los filtros aplicados.' : 'Añade los registros sanitarios de la bodega (RGSEAA, RESA, RPO, etc.).' }}"
            >
                @if($search || $typeFilter || $statusFilter)
                    <x-slot:action>
                        <flux:button wire:click="clearFilters" variant="outline" icon="x-mark">{{ __('Limpiar filtros') }}</flux:button>
                    </x-slot:action>
                @else
                    <x-slot:action>
                        <flux:button href="{{ roleRoute('sanitary-registrations.create') }}" wire:navigate variant="primary" icon="plus">
                            Nuevo registro
                        </flux:button>
                    </x-slot:action>
                @endif
            </x-agro.empty-state>
        @endif
    </div>

</div>

