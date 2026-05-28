<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        :title="__('Mantenimientos') . ' — ' . $container->name"
        :description="__('Historial y programación de mantenimientos del contenedor.')"
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
            :label="__('Programados')"
            :value="$stats['scheduled']"
            icon="calendar"
            color="amber"
        />
        <x-agro.stat-card
            :label="__('Completados')"
            :value="$stats['completed']"
            icon="check-circle"
            color="agro"
        />
        <x-agro.stat-card
            :label="__('Coste total')"
            :value="$stats['total_cost'] ? number_format($stats['total_cost'], 2) . ' €' : '—'"
            icon="currency-euro"
            color="zinc"
        />
        <x-agro.stat-card
            :label="__('Próximo')"
            :value="$container->next_maintenance_date ? $container->next_maintenance_date->format('d/m/Y') : '—'"
            icon="bell-alert"
            color="{{ $container->next_maintenance_date && $container->next_maintenance_date->isPast() ? 'red' : 'zinc' }}"
        />
    </div>

    {{-- Filtros --}}
    <x-agro.filter-bar :active-count="collect([$statusFilter, $typeFilter])->filter()->count()">
        <x-agro.filter-select wire:model.live="statusFilter" :label="__('Estado')">
            <option value="">{{ __('Todos') }}</option>
            @foreach($statuses as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </x-agro.filter-select>
        <x-agro.filter-select wire:model.live="typeFilter" :label="__('Tipo')">
            <option value="">{{ __('Todos') }}</option>
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
                title="{{ __('Sin mantenimientos registrados') }}"
                :description="__('Programa el primer mantenimiento para este contenedor.')"
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
                        $sl = __(\App\Models\ContainerMaintenance::STATUSES[$maint->status] ?? $maint->status);
                    @endphp
                    <x-agro.card
                        class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                        style="animation-delay: {{ $delay }}ms;"
                        wire:key="maint-{{ $maint->id }}"
                    >
                        <x-slot:header>
                            <x-agro.card-item-header
                                icon="wrench-screwdriver"
                                :title="$maint->maintenance_name"
                                :subtitle="__(\App\Models\ContainerMaintenance::TYPES[$maint->maintenance_type] ?? $maint->maintenance_type)"
                                iconBg="bg-orange-100"
                                iconColor="text-orange-600"
                                size="md"
                                radius="xl"
                            >
                                <x-agro.status-badge :color="$sc" :label="$sl" />
                            </x-agro.card-item-header>
                        </x-slot:header>

                        <div class="flex-1 space-y-4">
                            <div class="grid grid-cols-2 gap-2">
                                <div class="bg-agro-50 rounded-xl p-3">
                                    <p class="text-[10px] font-semibold text-agro-400 uppercase tracking-widest mb-0.5">{{ __('Programado') }}</p>
                                    <p class="text-sm font-bold text-agro-700 leading-none">{{ $maint->scheduled_date->format('d/m/Y') }}</p>
                                </div>
                                <div class="bg-agro-50 rounded-xl p-3">
                                    <p class="text-[10px] font-semibold text-agro-400 uppercase tracking-widest mb-0.5">{{ __('Realizado') }}</p>
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
                                    <span class="text-zinc-400">{{ __('Próximo') }}</span>
                                    <span class="font-medium {{ $maint->next_maintenance_date && $maint->next_maintenance_date->isPast() ? 'text-red-600' : 'text-zinc-700' }}">
                                        {{ $maint->next_maintenance_date?->format('d/m/Y') ?? '—' }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-zinc-400">{{ __('Coste') }}</span>
                                    <span class="text-zinc-700 font-medium">
                                        @if($maint->cost)
                                            {{ number_format($maint->cost, 2) }} €
                                        @else
                                            —
                                        @endif
                                    </span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-zinc-400">{{ __('Realizado por') }}</span>
                                    <span class="text-zinc-700 font-medium">{{ $maint->performed_by ?? '—' }}</span>
                                </div>
                            </div>

                            @if($maint->notes)
                                <p class="text-xs text-zinc-400 line-clamp-2">{{ $maint->notes }}</p>
                            @endif
                        </div>

                        <x-slot:footer>
                            <div class="flex items-center justify-end gap-0.5 flex-wrap">
                                {{-- Workflow de estados --}}
                                @if($maint->status === 'scheduled')
                                    <x-agro.action-button icon="play" variant="default" wire:click="transition({{ $maint->id }}, 'in_progress')" title="{{ __('Iniciar') }}" />
                                @endif
                                @if(in_array($maint->status, ['scheduled', 'in_progress']))
                                    <x-agro.action-button icon="eye" variant="default" wire:click="transition({{ $maint->id }}, 'in_review')" title="{{ __('Enviar a revisión') }}" />
                                @endif
                                @if($maint->status === 'in_review')
                                    <x-agro.action-button icon="hand-thumb-up" variant="primary" wire:click="transition({{ $maint->id }}, 'approved')" title="{{ __('Aprobar') }}" />
                                @endif
                                @if(in_array($maint->status, ['in_review', 'approved']))
                                    <x-agro.action-button variant="activate" wire:click="transition({{ $maint->id }}, 'completed')" wire:confirm="{{ __('¿Marcar como completado?') }}" title="{{ __('Completar') }}" />
                                @endif
                                @if(!in_array($maint->status, ['completed', 'cancelled']))
                                    <x-agro.action-button icon="x-circle" variant="danger" wire:click="transition({{ $maint->id }}, 'cancelled')" wire:confirm="{{ __('¿Cancelar este mantenimiento?') }}" title="{{ __('Cancelar') }}" />
                                @endif
                                {{-- Editar siempre --}}
                                <x-agro.action-button icon="pencil" variant="default" href="{{ roleRoute('containers.maintenance.edit', [$container, $maint]) }}" title="{{ __('Editar') }}" />
                            </div>
                        </x-slot:footer>
                    </x-agro.card>
                @endforeach
            </div>

            <div class="mt-6"><x-agro.pagination :paginator="$maintenances" /></div>
        @endif
    </div>

</div>
