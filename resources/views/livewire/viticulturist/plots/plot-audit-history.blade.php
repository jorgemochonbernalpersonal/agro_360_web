<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-lg font-semibold text-zinc-900">
                📋 Historial de Auditoría
            </h3>
            <p class="text-sm text-zinc-600 mt-1">
                Parcela: {{ $plot->name }} ({{ $plot->surface_area }} ha)
            </p>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="bg-zinc-50 p-4 rounded-lg border border-zinc-200">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-medium text-zinc-700 mb-1">Usuario</label>
                <flux:select wire:model.live="filterUser" >
                    <option value="">Todos</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </flux:select>
            </div>

            <div>
                <label class="block text-xs font-medium text-zinc-700 mb-1">Acción</label>
                <flux:select wire:model.live="filterAction" >
                    <option value="">Todas</option>
                    @foreach($actions as $action)
                        <option value="{{ $action }}">{{ ucfirst($action) }}</option>
                    @endforeach
                </flux:select>
            </div>

            <div>
                <label class="block text-xs font-medium text-zinc-700 mb-1">Desde</label>
                <input type="date" wire:model.live="filterDateFrom" class="w-full rounded-md border-zinc-300 shadow-sm focus:border-green-500 focus:ring-green-500 text-sm">
            </div>

            <div>
                <label class="block text-xs font-medium text-zinc-700 mb-1">Hasta</label>
                <input type="date" wire:model.live="filterDateTo" class="w-full rounded-md border-zinc-300 shadow-sm focus:border-green-500 focus:ring-green-500 text-sm">
            </div>
        </div>

        @if($filterUser || $filterAction || $filterDateFrom || $filterDateTo)
            <div class="mt-3">
                <button wire:click="clearFilters" class="text-xs text-zinc-600 hover:text-zinc-900 underline">
                    Limpiar filtros
                </button>
            </div>
        @endif
    </div>

    {{-- Timeline de cambios --}}
    <div class="space-y-4">
        @forelse($logs as $log)
            <div class="bg-white border border-zinc-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                {{-- Header del log --}}
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center gap-3">
                        {{-- Icono de acción --}}
                        <div class="flex-shrink-0">
                            @if($log->action === 'created')
                                <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                                    <flux:icon icon="plus" class="size-5 text-green-600" />
                                </div>
                            @elseif($log->action === 'updated')
                                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                                    <flux:icon icon="pencil-square" class="size-5 text-blue-600" />
                                </div>
                            @elseif($log->action === 'deleted')
                                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                                    <flux:icon icon="trash" class="size-5 text-red-600" />
                                </div>
                            @else
                                <div class="w-10 h-10 rounded-full bg-zinc-100 flex items-center justify-center">
                                    <flux:icon icon="information-circle" class="size-5 text-zinc-600" />
                                </div>
                            @endif
                        </div>

                        {{-- Información del log --}}
                        <div>
                            <p class="text-sm font-semibold text-zinc-900">
                                {{ $log->action_description }}
                            </p>
                            <div class="flex items-center gap-2 mt-1 text-xs text-zinc-600">
                                <span class="font-medium">{{ $log->user->name ?? 'Sistema' }}</span>
                                <span>•</span>
                                <span>{{ $log->created_at->format('d/m/Y H:i:s') }}</span>
                                <span>•</span>
                                <span class="text-zinc-500">{{ $log->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Metadata --}}
                    <div class="text-xs text-zinc-500">
                        <div title="Dirección IP">🌐 {{ $log->ip_address }}</div>
                    </div>
                </div>

                {{-- Cambios realizados --}}
                @php
                    $diff = $this->getChangeDiff($log);
                @endphp

                @if(!empty($diff))
                    <div class="mt-3 pt-3 border-t border-zinc-100">
                        <p class="text-xs font-medium text-zinc-700 mb-2">Cambios realizados:</p>
                        <div class="space-y-2">
                            @foreach($diff as $change)
                                <div class="bg-zinc-50 rounded p-2 text-xs">
                                    <p class="font-medium text-zinc-700 mb-1">{{ $change['field'] }}</p>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div class="bg-red-50 border border-red-200 rounded p-2">
                                            <p class="text-red-600 font-medium mb-1">Antes:</p>
                                            <p class="text-zinc-700">{!! $change['old'] !!}</p>
                                        </div>
                                        <div class="bg-green-50 border border-green-200 rounded p-2">
                                            <p class="text-green-600 font-medium mb-1">Después:</p>
                                            <p class="text-zinc-700">{!! $change['new'] !!}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @empty
            <div class="text-center py-12 bg-zinc-50 rounded-lg border-2 border-dashed border-zinc-300">
                <flux:icon icon="document-text" class="mx-auto size-12 text-zinc-400" />
                <p class="mt-2 text-sm text-zinc-600">No hay registros de auditoría para esta parcela</p>
            </div>
        @endforelse
    </div>

    {{-- Paginación --}}
    @if($logs->hasPages())
        <div class="mt-4">
            {{ $logs->links() }}
        </div>
    @endif
</div>
