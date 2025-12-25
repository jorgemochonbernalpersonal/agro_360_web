<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-lg font-semibold text-gray-900">
                📋 Historial de Auditoría
            </h3>
            <p class="text-sm text-gray-600 mt-1">
                Actividad: {{ $activity->activity_type }} - {{ $activity->activity_date->format('d/m/Y') }}
            </p>
        </div>
        @if($activity->is_locked)
            <x-activity-locked-badge :activity="$activity" />
        @endif
    </div>

    {{-- Filtros --}}
    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Usuario</label>
                <select wire:model.live="filterUser" class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 text-sm">
                    <option value="">Todos</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Acción</label>
                <select wire:model.live="filterAction" class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 text-sm">
                    <option value="">Todas</option>
                    @foreach($actions as $action)
                        <option value="{{ $action }}">{{ ucfirst($action) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Desde</label>
                <input type="date" wire:model.live="filterDateFrom" class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 text-sm">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Hasta</label>
                <input type="date" wire:model.live="filterDateTo" class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 text-sm">
            </div>
        </div>

        @if($filterUser || $filterAction || $filterDateFrom || $filterDateTo)
            <div class="mt-3">
                <button wire:click="clearFilters" class="text-xs text-gray-600 hover:text-gray-900 underline">
                    Limpiar filtros
                </button>
            </div>
        @endif
    </div>

    {{-- Timeline de cambios --}}
    <div class="space-y-4">
        @forelse($logs as $log)
            <div class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                {{-- Header del log --}}
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center gap-3">
                        {{-- Icono de acción --}}
                        <div class="flex-shrink-0">
                            @if($log->action === 'created')
                                <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                </div>
                            @elseif($log->action === 'updated')
                                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </div>
                            @elseif($log->action === 'deleted')
                                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </div>
                            @elseif($log->action === 'locked')
                                <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            @else
                                <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                            @endif
                        </div>

                        {{-- Información del log --}}
                        <div>
                            <p class="text-sm font-semibold text-gray-900">
                                {{ $log->action_description }}
                            </p>
                            <div class="flex items-center gap-2 mt-1 text-xs text-gray-600">
                                <span class="font-medium">{{ $log->user->name ?? 'Sistema' }}</span>
                                <span>•</span>
                                <span>{{ $log->created_at->format('d/m/Y H:i:s') }}</span>
                                <span>•</span>
                                <span class="text-gray-500">{{ $log->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Metadata --}}
                    <div class="text-xs text-gray-500">
                        <div title="Dirección IP">🌐 {{ $log->ip_address }}</div>
                    </div>
                </div>

                {{-- Cambios realizados --}}
                @php
                    $diff = $this->getChangeDiff($log);
                @endphp

                @if(!empty($diff))
                    <div class="mt-3 pt-3 border-t border-gray-100">
                        <p class="text-xs font-medium text-gray-700 mb-2">Cambios realizados:</p>
                        <div class="space-y-2">
                            @foreach($diff as $change)
                                <div class="bg-gray-50 rounded p-2 text-xs">
                                    <p class="font-medium text-gray-700 mb-1">{{ $change['field'] }}</p>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div class="bg-red-50 border border-red-200 rounded p-2">
                                            <p class="text-red-600 font-medium mb-1">Antes:</p>
                                            <p class="text-gray-700">{!! $change['old'] !!}</p>
                                        </div>
                                        <div class="bg-green-50 border border-green-200 rounded p-2">
                                            <p class="text-green-600 font-medium mb-1">Después:</p>
                                            <p class="text-gray-700">{!! $change['new'] !!}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @empty
            <div class="text-center py-12 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="mt-2 text-sm text-gray-600">No hay registros de auditoría para esta actividad</p>
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
