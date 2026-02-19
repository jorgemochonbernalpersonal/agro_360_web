<div class="space-y-4">
    {{-- Header --}}
    <div class="flex items-start justify-between gap-4">
        <div>
            <flux:heading size="lg">Historial de Auditoría</flux:heading>
            <flux:subheading>
                {{ $activity->activity_type }} — {{ $activity->activity_date->format('d/m/Y') }}
            </flux:subheading>
        </div>
        @if($activity->is_locked)
            <x-activity-locked-badge :activity="$activity" />
        @endif
    </div>

    {{-- Filtros --}}
    <x-agro.filter-bar>
        <x-agro.filter-select wire:model.live="filterUser">
            <option value="">Todos los usuarios</option>
            @foreach($users as $user)
                <option value="{{ $user->id }}">{{ $user->name }}</option>
            @endforeach
        </x-agro.filter-select>

        <x-agro.filter-select wire:model.live="filterAction">
            <option value="">Todas las acciones</option>
            @foreach($actions as $action)
                <option value="{{ $action }}">{{ ucfirst($action) }}</option>
            @endforeach
        </x-agro.filter-select>

        <div class="flex flex-col gap-1">
            <label class="text-xs font-medium text-zinc-600">Desde</label>
            <flux:input type="date" wire:model.live="filterDateFrom" size="sm" />
        </div>

        <div class="flex flex-col gap-1">
            <label class="text-xs font-medium text-zinc-600">Hasta</label>
            <flux:input type="date" wire:model.live="filterDateTo" size="sm" />
        </div>

        @if($filterUser || $filterAction || $filterDateFrom || $filterDateTo)
            <div class="flex items-end">
                <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">
                    Limpiar
                </flux:button>
            </div>
        @endif
    </x-agro.filter-bar>

    {{-- Timeline de cambios --}}
    <div class="space-y-3">
        @forelse($logs as $log)
            @php
                $actionConfig = match($log->action) {
                    'created' => ['icon' => 'plus',          'bg' => 'bg-agro-50',  'color' => 'text-agro-600'],
                    'updated' => ['icon' => 'pencil-square', 'bg' => 'bg-blue-50',  'color' => 'text-blue-600'],
                    'deleted' => ['icon' => 'trash',         'bg' => 'bg-red-50',   'color' => 'text-red-600'],
                    'locked'  => ['icon' => 'lock-closed',   'bg' => 'bg-zinc-100', 'color' => 'text-zinc-500'],
                    default   => ['icon' => 'information-circle', 'bg' => 'bg-zinc-100', 'color' => 'text-zinc-500'],
                };
            @endphp

            <x-agro.card>
                <div class="flex items-start gap-4">
                    {{-- Icono de acción --}}
                    <div class="flex-shrink-0 w-9 h-9 rounded-full {{ $actionConfig['bg'] }} flex items-center justify-center">
                        <flux:icon :icon="$actionConfig['icon']" class="size-4 {{ $actionConfig['color'] }}" />
                    </div>

                    {{-- Contenido principal --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2">
                            <p class="text-sm font-semibold text-zinc-900">{{ $log->action_description }}</p>
                            <span class="text-xs text-zinc-400 flex-shrink-0 flex items-center gap-1" title="Dirección IP">
                                <flux:icon icon="globe-alt" class="size-3" />
                                {{ $log->ip_address }}
                            </span>
                        </div>

                        <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5 mt-1 text-xs text-zinc-500">
                            <span class="font-medium text-zinc-700">{{ $log->user->name ?? 'Sistema' }}</span>
                            <span>·</span>
                            <span>{{ $log->created_at->format('d/m/Y H:i:s') }}</span>
                            <span>·</span>
                            <span>{{ $log->created_at->diffForHumans() }}</span>
                        </div>

                        {{-- Diff de cambios --}}
                        @php $diff = $this->getChangeDiff($log); @endphp
                        @if(!empty($diff))
                            <div class="mt-3 pt-3 border-t border-zinc-100 space-y-2">
                                <p class="text-xs font-semibold text-zinc-500 uppercase tracking-wide">Cambios realizados</p>
                                @foreach($diff as $change)
                                    <div class="text-xs">
                                        <p class="font-semibold text-zinc-700 mb-1">{{ $change['field'] }}</p>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                            <div class="bg-red-50 border border-red-100 rounded-lg px-3 py-2">
                                                <p class="text-red-500 font-medium mb-0.5">Antes</p>
                                                <p class="text-zinc-700">{!! $change['old'] !!}</p>
                                            </div>
                                            <div class="bg-agro-50 border border-agro-100 rounded-lg px-3 py-2">
                                                <p class="text-agro-600 font-medium mb-0.5">Después</p>
                                                <p class="text-zinc-700">{!! $change['new'] !!}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </x-agro.card>
        @empty
            <x-agro.empty-state
                icon="document-text"
                message="Sin registros de auditoría"
                description="No hay registros de auditoría para esta actividad con los filtros aplicados"
            />
        @endforelse
    </div>

    {{-- Paginación --}}
    <x-agro.pagination :paginator="$logs" />
</div>
