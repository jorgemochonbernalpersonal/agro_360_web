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
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
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
        <x-agro.card>
            <x-agro.data-table :headers="['Tipo', 'Nombre', 'Programado', 'Realizado', 'Próximo', 'Estado', 'Coste', 'Realizado por', 'Acciones']">
                @foreach($maintenances as $maint)
                    <x-agro.table-row wire:key="maint-{{ $maint->id }}">

                        <x-agro.table-cell>
                            <span class="text-zinc-700 text-sm">{{ \App\Models\ContainerMaintenance::TYPES[$maint->maintenance_type] ?? $maint->maintenance_type }}</span>
                        </x-agro.table-cell>

                        <x-agro.table-cell>
                            <span class="font-medium text-zinc-900">{{ $maint->maintenance_name }}</span>
                            @if($maint->notes)
                                <div class="text-xs text-zinc-400 line-clamp-1">{{ $maint->notes }}</div>
                            @endif
                        </x-agro.table-cell>

                        <x-agro.table-cell>
                            <span class="text-zinc-700">{{ $maint->scheduled_date->format('d/m/Y') }}</span>
                        </x-agro.table-cell>

                        <x-agro.table-cell>
                            @if($maint->performed_date)
                                <span class="text-agro-700">{{ $maint->performed_date->format('d/m/Y') }}</span>
                            @else
                                <span class="text-zinc-300">—</span>
                            @endif
                        </x-agro.table-cell>

                        <x-agro.table-cell>
                            @if($maint->next_maintenance_date)
                                <span class="{{ $maint->next_maintenance_date->isPast() ? 'text-red-600 font-medium' : 'text-zinc-600' }}">
                                    {{ $maint->next_maintenance_date->format('d/m/Y') }}
                                </span>
                            @else
                                <span class="text-zinc-300">—</span>
                            @endif
                        </x-agro.table-cell>

                        <x-agro.table-cell>
                            @php
                                $sc = match($maint->status) { 'scheduled' => 'amber', 'completed' => 'agro', 'cancelled' => 'zinc', default => 'zinc' };
                                $sl = \App\Models\ContainerMaintenance::STATUSES[$maint->status] ?? $maint->status;
                            @endphp
                            <x-agro.status-badge :color="$sc" :label="$sl" />
                        </x-agro.table-cell>

                        <x-agro.table-cell>
                            @if($maint->cost)
                                <span class="text-zinc-700">{{ number_format($maint->cost, 2) }} €</span>
                            @else
                                <span class="text-zinc-300">—</span>
                            @endif
                        </x-agro.table-cell>

                        <x-agro.table-cell>
                            <span class="text-zinc-600 text-sm">{{ $maint->performed_by ?? '—' }}</span>
                        </x-agro.table-cell>

                        <x-agro.table-cell align="right">
                            <div class="flex items-center justify-end gap-1">
                                @if($maint->status === 'scheduled')
                                    <flux:button
                                        wire:click="markCompleted({{ $maint->id }})"
                                        wire:confirm="¿Marcar este mantenimiento como completado?"
                                        variant="ghost"
                                        size="sm"
                                        icon="check-circle"
                                        title="Marcar completado"
                                    />
                                    <flux:button
                                        wire:click="cancel({{ $maint->id }})"
                                        wire:confirm="¿Cancelar este mantenimiento?"
                                        variant="ghost"
                                        size="sm"
                                        icon="x-circle"
                                        title="Cancelar"
                                    />
                                @endif
                            </div>
                        </x-agro.table-cell>

                    </x-agro.table-row>
                @endforeach
            </x-agro.data-table>
        </x-agro.card>

        <x-agro.pagination :paginator="$maintenances" />
    @endif

</div>
