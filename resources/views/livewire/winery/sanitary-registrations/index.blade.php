<div class="space-y-6 animate-fade-in">

    {{-- Header --}}
    <x-agro.page-header
        title="Registros Sanitarios"
        description="Gestión de registros RGSEAA, RESA, RPO y otros registros sanitarios oficiales"
        icon="shield-check"
    >
        <x-slot:actions>
            <flux:button variant="primary" icon="plus" href="{{ roleRoute('sanitary-registrations.create') }}" wire:navigate>
                Nuevo registro
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- KPIs --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <x-agro.stat-card
            label="Total registros"
            :value="$stats['total']"
            icon="shield-check"
            color="zinc"
        />
        <x-agro.stat-card
            label="Activos"
            :value="$stats['active']"
            icon="check-circle"
            color="agro"
        />
        <x-agro.stat-card
            label="Próximos a vencer"
            :value="$stats['expiring']"
            icon="clock"
            color="amber"
        />
        <x-agro.stat-card
            label="Caducados"
            :value="$stats['expired']"
            icon="x-circle"
            color="zinc"
        />
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
                placeholder="Buscar por número, descripción..."
                class="w-full pl-9 pr-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm placeholder:text-zinc-400 shadow-sm focus:outline-none focus:ring-2 focus:ring-agro-500 focus:border-transparent transition"
            />
        </div>

        <flux:select wire:model.live="typeFilter" class="w-44">
            <flux:select.option value="">Todos los tipos</flux:select.option>
            @foreach($types as $key => $label)
                <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select wire:model.live="statusFilter" class="w-40">
            <flux:select.option value="">Todos los estados</flux:select.option>
            @foreach($statuses as $key => $label)
                <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
            @endforeach
        </flux:select>

        @if($search || $typeFilter || $statusFilter)
            <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">
                Limpiar
            </flux:button>
        @endif
    </div>

    {{-- Loading skeleton --}}
    <div wire:loading wire:target="search, typeFilter, statusFilter, clearFilters, nextPage, previousPage">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @for($i = 0; $i < 6; $i++)
                <x-agro.skeleton-card />
            @endfor
        </div>
    </div>

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
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 {{ $statusConfig['bg'] }}">
                                    <flux:icon icon="shield-check" class="size-5 {{ $statusConfig['icon'] }}" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-zinc-900 font-mono truncate">{{ $registration->registration_number }}</h3>
                                    @if($registration->issuing_authority)
                                        <p class="text-xs text-zinc-400 truncate">{{ $registration->issuing_authority }}</p>
                                    @endif
                                </div>
                                <flux:badge color="{{ $statusConfig['badge'] }}" size="sm" class="shrink-0">
                                    {{ $registration->status_label }}
                                </flux:badge>
                            </div>
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
                                            <flux:badge color="amber" size="sm">Próximo</flux:badge>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>

                        <x-slot:footer>
                            <div class="flex items-center justify-end gap-0.5">
                                <a href="{{ roleRoute('sanitary-registrations.edit', $registration) }}"
                                   wire:navigate
                                   title="Editar registro"
                                   class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors">
                                    <flux:icon icon="pencil-square" class="size-4" />
                                </a>
                                <button
                                    wire:click="delete({{ $registration->id }})"
                                    wire:confirm="¿Eliminar este registro sanitario?"
                                    wire:loading.attr="disabled"
                                    title="Eliminar registro"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-red-500 hover:bg-red-50 transition-colors">
                                    <flux:icon icon="trash" class="size-4" />
                                </button>
                            </div>
                        </x-slot:footer>
                    </x-agro.card>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $registrations->links() }}
            </div>
        @else
            <x-agro.empty-state
                icon="shield-check"
                title="{{ $search || $typeFilter || $statusFilter ? 'Ningún registro coincide con los filtros' : 'Sin registros sanitarios' }}"
                description="{{ $search || $typeFilter || $statusFilter ? 'Prueba a cambiar o limpiar los filtros aplicados.' : 'Añade los registros sanitarios de la bodega (RGSEAA, RESA, RPO, etc.).' }}"
            >
                @if($search || $typeFilter || $statusFilter)
                    <x-slot:action>
                        <flux:button wire:click="clearFilters" variant="outline" icon="x-mark">Limpiar filtros</flux:button>
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
