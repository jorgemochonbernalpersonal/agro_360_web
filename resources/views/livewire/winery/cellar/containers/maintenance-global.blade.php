<div class="space-y-6 animate-fade-in">

    {{-- Header --}}
    <x-agro.page-header
        title="{{ __('Mantenimientos de Contenedores') }}"
        :description="__('Todos los mantenimientos programados y realizados en depósitos y barricas')"
        icon="wrench-screwdriver"
    >
        <x-slot:actions>
            <flux:button href="{{ roleRoute('containers.index') }}" wire:navigate variant="ghost" icon="arrow-left">
                {{ __('Contenedores') }}
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- KPIs --}}
    <div x-data="{
        open: localStorage.getItem('maintenance-global-stats-open') !== 'false',
        toggle() { this.open = !this.open; localStorage.setItem('maintenance-global-stats-open', String(this.open)); }
    }">
        <button @click="toggle()"
            class="flex items-center gap-1.5 text-[11px] font-semibold text-zinc-400 uppercase tracking-widest hover:text-zinc-600 transition-colors mb-3">
            <span>{{ __('Estadísticas') }}</span>
            <flux:icon icon="chevron-up" class="size-3.5 transition-transform duration-200" ::class="{ 'rotate-180': !open }" />
        </button>
        <div x-show="open"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <x-agro.stat-card :label="__('Total')"       :value="$stats['total']"     icon="wrench-screwdriver" color="zinc" />
                <x-agro.stat-card :label="__('Programados')"  :value="$stats['scheduled']" icon="clock"              color="amber" />
                <x-agro.stat-card :label="__('Completados')"  :value="$stats['completed']" icon="check-circle"       color="agro" />
                <x-agro.stat-card :label="__('Coste total')"  :value="number_format($stats['total_cost'], 2) . ' €'" icon="currency-euro" color="zinc" />
            </div>
        </div>
    </div>

    {{-- Toolbar --}}
    @php $filterCount = (int) !empty($statusFilter) + (int) !empty($typeFilter) + (int) !empty($containerFilter); @endphp
    <div class="flex items-center gap-3">
        <div class="flex-1 relative">
            <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                <flux:icon icon="magnifying-glass" class="size-4 text-zinc-400" />
            </div>
            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                placeholder="{{ __('Buscar por descripción o contenedor...') }}"
                class="w-full pl-9 pr-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm placeholder:text-zinc-400 shadow-sm focus:outline-none focus:ring-2 focus:ring-agro-500 focus:border-transparent transition"
            />
        </div>

        <flux:select wire:model.live="containerFilter" class="w-48">
            <flux:select.option value="">{{ __('Todos los contenedores') }}</flux:select.option>
            @foreach($containers as $c)
                <flux:select.option value="{{ $c->id }}">{{ $c->name }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select wire:model.live="statusFilter" class="w-40">
            <flux:select.option value="">{{ __('Todos los estados') }}</flux:select.option>
            @foreach($statuses as $key => $label)
                <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select wire:model.live="typeFilter" class="w-40">
            <flux:select.option value="">{{ __('Todos los tipos') }}</flux:select.option>
            @foreach($types as $key => $label)
                <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
            @endforeach
        </flux:select>

        @if($search || $filterCount)
            <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">{{ __('Limpiar') }}</flux:button>
        @endif
    </div>

    {{-- Loading --}}
    <div wire:loading wire:target="search, statusFilter, typeFilter, containerFilter, clearFilters, nextPage, previousPage">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @for($i = 0; $i < 6; $i++)<x-agro.skeleton-card />@endfor
        </div>
    </div>

    {{-- Grid --}}
    <div wire:loading.remove wire:target="search, statusFilter, typeFilter, containerFilter, clearFilters, nextPage, previousPage">
        @if($maintenances->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($maintenances as $m)
                    @php
                        $delay = min($loop->index * 50, 300);
                        $statusColors = [
                            'scheduled'   => ['icon' => 'text-amber-600',  'bg' => 'bg-amber-100',  'badge' => 'amber'],
                            'in_progress' => ['icon' => 'text-blue-600',   'bg' => 'bg-blue-100',   'badge' => 'blue'],
                            'in_review'   => ['icon' => 'text-purple-600', 'bg' => 'bg-purple-100', 'badge' => 'purple'],
                            'completed'   => ['icon' => 'text-green-600',  'bg' => 'bg-green-100',  'badge' => 'green'],
                            'cancelled'   => ['icon' => 'text-zinc-400',   'bg' => 'bg-zinc-100',   'badge' => 'zinc'],
                        ];
                        $sc = $statusColors[$m->status] ?? $statusColors['scheduled'];
                    @endphp
                    <x-agro.card
                        class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                        style="animation-delay: {{ $delay }}ms;"
                        wire:key="maint-{{ $m->id }}"
                    >
                        <x-slot:header>
                            <x-agro.card-item-header
                                icon="wrench-screwdriver"
                                :title="$m->container?->name ?? '—'"
                                :subtitle="$types[$m->maintenance_type] ?? $m->maintenance_type"
                                :iconBg="$sc['bg']"
                                :iconColor="$sc['icon']"
                                size="md"
                                radius="xl"
                            >
                                <flux:badge color="{{ $sc['badge'] }}" size="sm">{{ $statuses[$m->status] ?? $m->status }}</flux:badge>
                            </x-agro.card-item-header>
                        </x-slot:header>

                        <div class="flex-1 space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-zinc-500">{{ __('Programado') }}</span>
                                <span class="font-medium text-zinc-800">{{ $m->scheduled_date?->format('d/m/Y') ?? '—' }}</span>
                            </div>
                            @if($m->performed_date)
                            <div class="flex justify-between">
                                <span class="text-zinc-500">{{ __('Realizado') }}</span>
                                <span class="font-medium text-zinc-800">{{ $m->performed_date->format('d/m/Y') }}</span>
                            </div>
                            @endif
                            @if($m->cost)
                            <div class="flex justify-between">
                                <span class="text-zinc-500">{{ __('Coste') }}</span>
                                <span class="font-medium text-zinc-800">{{ number_format($m->cost, 2) }} €</span>
                            </div>
                            @endif
                            @if($m->description)
                            <p class="text-xs text-zinc-400 italic pt-1">{{ Str::limit($m->description, 80) }}</p>
                            @endif
                        </div>

                        <x-slot:footer>
                            <div class="flex gap-2 justify-end">
                                @if($m->status === 'scheduled')
                                    <flux:button wire:click="transition({{ $m->id }}, 'in_progress')" variant="ghost" size="sm" icon="play">
                                        Iniciar
                                    </flux:button>
                                @endif
                                @if(in_array($m->status, ['in_progress', 'in_review']))
                                    <flux:button wire:click="transition({{ $m->id }}, 'completed')" variant="ghost" size="sm" icon="check" class="text-green-600">
                                        Completar
                                    </flux:button>
                                @endif
                                @if($m->status !== 'completed' && $m->status !== 'cancelled')
                                    <flux:button wire:click="transition({{ $m->id }}, 'cancelled')" wire:confirm="{{ __('¿Cancelar este mantenimiento?') }}" variant="ghost" size="sm" icon="x-mark" class="text-red-500" />
                                @endif
                            </div>
                        </x-slot:footer>
                    </x-agro.card>
                @endforeach
            </div>

            <x-agro-pagination :paginator="$maintenances" />
        @else
            <x-agro.empty-state
                icon="wrench-screwdriver"
                title="{{ __('Sin mantenimientos registrados') }}"
                description="{{ $search || $filterCount ? 'No hay mantenimientos que coincidan con los filtros.' : 'Los mantenimientos se programan desde el detalle de cada contenedor.' }}"
            />
        @endif
    </div>

</div>
