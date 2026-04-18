<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Mantenimientos — {{ $container->name }}"
        description="Historial y programación de mantenimientos del contenedor."
    >
        <x-slot:actions>
            <flux:button variant="ghost" icon="arrow-left" href="{{ roleRoute('containers.index') }}" wire:navigate>
                Volver a contenedores
            </flux:button>
            <flux:button variant="primary" icon="plus" href="{{ roleRoute('containers.maintenance.create', $container) }}" wire:navigate>
                Nuevo mantenimiento
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Info del contenedor --}}
    <div class="grid grid-cols-2 gap-4">
        <x-agro.stat-card
            label="Programados"
            :value="$stats['scheduled']"
            icon="calendar"
            color="amber"
        />
        <x-agro.stat-card
            label="Completados"
            :value="$stats['completed']"
            icon="check-circle"
            color="agro"
        />
        <x-agro.stat-card
            label="Coste total"
            :value="$stats['total_cost'] ? number_format($stats['total_cost'], 2) . ' €' : '—'"
            icon="currency-euro"
            color="zinc"
        />
        <x-agro.stat-card
            label="Próximo"
            :value="$container->next_maintenance_date ? $container->next_maintenance_date->format('d/m/Y') : '—'"
            icon="bell-alert"
            color="{{ $container->next_maintenance_date && $container->next_maintenance_date->isPast() ? 'red' : 'zinc' }}"
        />
    </div>

    {{-- Filtros --}}
    <x-agro.filter-bar :active-count="collect([$statusFilter, $typeFilter])->filter()->count()">
        <x-agro.filter-select wire:model.live="statusFilter" label="Estado">
            <option value="">Todos</option>
            @foreach($statuses as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </x-agro.filter-select>
        <x-agro.filter-select wire:model.live="typeFilter" label="Tipo">
            <option value="">Todos</option>
            @foreach($types as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </x-agro.filter-select>
    </x-agro.filter-bar>

    <x-agro.loading-grid target="statusFilter, typeFilter, nextPage, previousPage" />

    <div wire:loading.remove wire:target="statusFilter, typeFilter, nextPage, previousPage">
        @if($maintenances->isEmpty())
            <x-agro.empty-state
                icon="wrench-screwdriver"
                title="Sin mantenimientos registrados"
                description="Programa el primer mantenimiento para este contenedor."
            >
                <flux:button variant="primary" icon="plus" href="{{ roleRoute('containers.maintenance.create', $container) }}" wire:navigate>
                    Nuevo mantenimiento
                </flux:button>
            </x-agro.empty-state>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($maintenances as $maint)
                    @php $delay = min($loop->index * 50, 300); @endphp
                    @php
                        $sc = match($maint->status) {
                            'scheduled'   => 'amber',
                            'in_progress' => 'blue',
                            'in_review'   => 'violet',
                            'approved'    => 'indigo',
                            'completed'   => 'agro',
                            'cancelled'   => 'zinc',
                            default       => 'zinc'
                        };
                        $sl = \App\Models\ContainerMaintenance::STATUSES[$maint->status] ?? $maint->status;
                    @endphp
                    <x-agro.card
                        class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                        style="animation-delay: {{ $delay }}ms;"
                        wire:key="maint-{{ $maint->id }}"
                    >
                        <x-slot:header>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-orange-100 flex items-center justify-center shrink-0">
                                    <flux:icon icon="wrench-screwdriver" class="size-5 text-orange-600" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-zinc-900 truncate">{{ $maint->maintenance_name }}</h3>
                                    <p class="text-xs text-zinc-500">{{ \App\Models\ContainerMaintenance::TYPES[$maint->maintenance_type] ?? $maint->maintenance_type }}</p>
                                </div>
                                <x-agro.status-badge :color="$sc" :label="$sl" class="shrink-0" />
                            </div>
                        </x-slot:header>

                        <div class="flex-1 space-y-4">
                            <div class="grid grid-cols-2 gap-2">
                                <div class="bg-agro-50 rounded-xl p-3">
                                    <p class="text-[10px] font-semibold text-agro-400 uppercase tracking-widest mb-0.5">Programado</p>
                                    <p class="text-sm font-bold text-agro-700 leading-none">{{ $maint->scheduled_date->format('d/m/Y') }}</p>
                                </div>
                                <div class="bg-agro-50 rounded-xl p-3">
                                    <p class="text-[10px] font-semibold text-agro-400 uppercase tracking-widest mb-0.5">Realizado</p>
                                    <p class="text-sm font-bold text-agro-700 leading-none">
                                        @if($maint->performed_date)
                                            {{ $maint->performed_date->format('d/m/Y') }}
                                        @else
                                            —
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-2 text-sm">
                                <div class="flex items-center justify-between">
                                    <span class="text-zinc-400">Próximo</span>
                                    <span class="font-medium {{ $maint->next_maintenance_date && $maint->next_maintenance_date->isPast() ? 'text-red-600' : 'text-zinc-700' }}">
                                        {{ $maint->next_maintenance_date?->format('d/m/Y') ?? '—' }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-zinc-400">Coste</span>
                                    <span class="text-zinc-700 font-medium">
                                        @if($maint->cost)
                                            {{ number_format($maint->cost, 2) }} €
                                        @else
                                            —
                                        @endif
                                    </span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-zinc-400">Realizado por</span>
                                    <span class="text-zinc-700 font-medium">{{ $maint->performed_by ?? '—' }}</span>
                                </div>
                            </div>

                            @if($maint->notes)
                                <p class="text-xs text-zinc-400 line-clamp-2">{{ $maint->notes }}</p>
                            @endif
                        </div>

                        <x-slot:footer>
                            @php $btnBase = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors'; @endphp
                            <div class="flex items-center justify-end gap-0.5 flex-wrap">
                                {{-- Workflow de estados --}}
                                @if($maint->status === 'scheduled')
                                    <button wire:click="transition({{ $maint->id }}, 'in_progress')" class="{{ $btnBase }}" title="Iniciar">
                                        <flux:icon icon="play" class="size-4" />
                                    </button>
                                @endif
                                @if(in_array($maint->status, ['scheduled', 'in_progress']))
                                    <button wire:click="transition({{ $maint->id }}, 'in_review')" class="{{ $btnBase }}" title="Enviar a revisión">
                                        <flux:icon icon="eye" class="size-4" />
                                    </button>
                                @endif
                                @if($maint->status === 'in_review')
                                    <button wire:click="transition({{ $maint->id }}, 'approved')" class="{{ $btnBase }} !text-agro-600" title="Aprobar">
                                        <flux:icon icon="hand-thumb-up" class="size-4" />
                                    </button>
                                @endif
                                @if(in_array($maint->status, ['in_review', 'approved']))
                                    <button wire:click="transition({{ $maint->id }}, 'completed')" wire:confirm="¿Marcar como completado?" class="{{ $btnBase }}" title="Completar">
                                        <flux:icon icon="check-circle" class="size-4" />
                                    </button>
                                @endif
                                @if(!in_array($maint->status, ['completed', 'cancelled']))
                                    <button wire:click="transition({{ $maint->id }}, 'cancelled')" wire:confirm="¿Cancelar este mantenimiento?" class="{{ $btnBase }} hover:!text-red-500 hover:!bg-red-50" title="Cancelar">
                                        <flux:icon icon="x-circle" class="size-4" />
                                    </button>
                                @endif
                                {{-- Editar siempre --}}
                                <a href="{{ roleRoute('containers.maintenance.edit', [$container, $maint]) }}" class="{{ $btnBase }}" title="Editar">
                                    <flux:icon icon="pencil" class="size-4" />
                                </a>
                            </div>
                        </x-slot:footer>
                    </x-agro.card>
                @endforeach
            </div>

            <div class="mt-6"><x-agro.pagination :paginator="$maintenances" /></div>
        @endif
    </div>

</div>
