<div class="space-y-6">
    {{-- Header --}}
    <x-agro-page-header title="Historial de Auditoría" :description="'Parcela: '.$plot->name.' ('.$plot->surface_area.' ha)'" />

    {{-- Filtros --}}
    <x-agro-filter-bar>
        <x-agro-filter-select label="Usuario" wire:model.live="filterUser" placeholder="Todos">
            @foreach($users as $user)
                <flux:select.option value="{{ $user->id }}">{{ $user->name }}</flux:select.option>
            @endforeach
        </x-agro-filter-select>

        <x-agro-filter-select label="Acción" wire:model.live="filterAction" placeholder="Todas">
            @foreach($actions as $action)
                <flux:select.option value="{{ $action }}">{{ ucfirst($action) }}</flux:select.option>
            @endforeach
        </x-agro-filter-select>

        <div class="flex flex-col gap-1">
            <x-agro-field-label>Desde</x-agro-field-label>
            <flux:input type="date" wire:model.live="filterDateFrom" />
        </div>

        <div class="flex flex-col gap-1">
            <x-agro-field-label>Hasta</x-agro-field-label>
            <flux:input type="date" wire:model.live="filterDateTo" />
        </div>

        @if($filterUser || $filterAction || $filterDateFrom || $filterDateTo)
            <div class="flex items-end">
                <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">
                    Limpiar filtros
                </flux:button>
            </div>
        @endif
    </x-agro-filter-bar>

    {{-- Timeline de cambios --}}
    <div class="space-y-4">
        @forelse($logs as $log)
            <x-agro-card>
                {{-- Header del log --}}
                <div class="flex items-start justify-between">
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
                            <div class="flex items-center gap-2 mt-1 text-xs text-zinc-500">
                                <span class="font-medium">{{ $log->user->name ?? 'Sistema' }}</span>
                                <span>•</span>
                                <span>{{ $log->created_at->format('d/m/Y H:i:s') }}</span>
                                <span>•</span>
                                <span>{{ $log->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- IP --}}
                    <span class="text-xs text-zinc-400" title="Dirección IP">🌐 {{ $log->ip_address }}</span>
                </div>

                {{-- Cambios realizados --}}
                @php $diff = $this->getChangeDiff($log); @endphp

                @if(!empty($diff))
                    <div class="mt-4 pt-4 border-t border-zinc-100">
                        <p class="text-xs font-medium text-zinc-600 mb-2">Cambios realizados:</p>
                        <div class="space-y-2">
                            @foreach($diff as $change)
                                <div class="bg-zinc-50 rounded-lg p-3 text-xs">
                                    <p class="font-medium text-zinc-700 mb-2">{{ $change['field'] }}</p>
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
            </x-agro-card>
        @empty
            <x-agro-empty-state icon="document-text" title="Sin registros" description="No hay registros de auditoría para esta parcela" />
        @endforelse
    </div>

    {{-- Paginación --}}
    <x-agro-pagination :paginator="$logs" />
</div>
