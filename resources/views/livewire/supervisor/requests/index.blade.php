<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Solicitudes y actas"
        description="Gestiona solicitudes enviadas a bodegas adscritas."
        icon="document-text"
    />

    {{-- Stats --}}
    <div x-data="{
        open: localStorage.getItem('supervisor-requests-stats-open') !== 'false',
        toggle() {
            this.open = !this.open;
            localStorage.setItem('supervisor-requests-stats-open', String(this.open));
        }
    }">
        <button
            @click="toggle()"
            class="flex items-center gap-1.5 text-[11px] font-semibold text-zinc-400 uppercase tracking-widest hover:text-zinc-600 transition-colors mb-3"
        >
            <span>Estadísticas</span>
            <flux:icon icon="chevron-up" class="size-3.5 transition-transform duration-200" ::class="{ 'rotate-180': !open }" />
        </button>
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-1"
        >
        <div class="grid grid-cols-2 gap-4">
            <x-agro.stat-card label="Pendientes" :value="$pendingCount" icon="clock" color="yellow" />
            <x-agro.stat-card label="En revisión" :value="$inReviewCount" icon="magnifying-glass" color="blue" />
        </div>
        </div>
    </div>

    {{-- Filtros + Nueva --}}
    <div class="flex flex-wrap items-center gap-2">
        <flux:select wire:model.live="statusFilter" class="w-40 text-sm">
            <option value="">Todos los estados</option>
            @foreach($statusLabels as $val => $label)
                <option value="{{ $val }}">{{ $label }}</option>
            @endforeach
        </flux:select>

        <flux:select wire:model.live="typeFilter" class="w-52 text-sm">
            <option value="">Todos los tipos</option>
            @foreach($typeLabels as $val => $label)
                <option value="{{ $val }}">{{ $label }}</option>
            @endforeach
        </flux:select>

        <div class="relative">
            <flux:icon icon="magnifying-glass" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-400 pointer-events-none" />
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar bodega…"
                   class="pl-9 pr-3 py-1.5 text-sm border border-zinc-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-300 w-44" />
        </div>

        <flux:button wire:click="openCreateModal" variant="primary" size="sm" icon="plus" class="ml-auto">
            Nueva solicitud
        </flux:button>
    </div>

    {{-- Bulk action bar --}}
    @if(count($selected) > 0)
        <div class="flex items-center gap-3 px-4 py-2.5 bg-blue-50 border border-blue-200 rounded-xl text-sm">
            <span class="font-medium text-blue-700">{{ count($selected) }} seleccionadas</span>
            <flux:button wire:click="bulkArchive"
                wire:confirm="¿Archivar las {{ count($selected) }} solicitudes seleccionadas? Solo se archivarán las que estén aprobadas o rechazadas."
                variant="primary" size="sm">
                Archivar seleccionadas
            </flux:button>
            <button wire:click="$set('selected', [])" class="text-xs text-blue-500 hover:text-blue-700">Limpiar</button>
        </div>
    @endif

    {{-- Tabla --}}
    <x-agro.data-table :headers="['', 'Tipo', 'Bodega', 'Título', 'Estado', 'Fecha', 'Vence', '']">
        @forelse($requests as $req)
            <x-agro.table-row>
                <x-agro.table-cell>
                    @if(in_array($req->status, ['approved','rejected']))
                        <input type="checkbox" wire:model="selected" value="{{ $req->id }}"
                            class="rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer" />
                    @endif
                </x-agro.table-cell>
                <x-agro.table-cell>
                    <div class="flex items-center gap-1.5">
                        @if(in_array($req->type, \App\Models\SupervisorRequest::WINERY_INITIATED))
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-blue-50 text-blue-600 border border-blue-200">Bodega</span>
                        @else
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-violet-50 text-violet-600 border border-violet-200">DO</span>
                        @endif
                        <span class="text-xs font-medium text-zinc-600">{{ $typeLabels[$req->type] ?? $req->type }}</span>
                    </div>
                </x-agro.table-cell>
                <x-agro.table-cell>
                    <span class="text-sm text-zinc-800">{{ $req->winery->name }}</span>
                </x-agro.table-cell>
                <x-agro.table-cell>
                    <span class="text-sm text-zinc-600">{{ $req->title ?: '—' }}</span>
                </x-agro.table-cell>
                <x-agro.table-cell>
                    @php $color = $statusColors[$req->status] ?? 'zinc'; @endphp
                    <x-agro.status-badge :status="$req->status" :label="$statusLabels[$req->status] ?? $req->status" :color="$color" />
                </x-agro.table-cell>
                <x-agro.table-cell>
                    <span class="text-xs text-zinc-400">{{ $req->created_at->format('d/m/Y') }}</span>
                </x-agro.table-cell>
                <x-agro.table-cell>
                    @if($req->due_date)
                        @if($req->isOverdue())
                            <span class="inline-flex items-center gap-1 text-xs font-medium text-red-600">
                                <flux:icon icon="exclamation-circle" class="w-3.5 h-3.5" />
                                {{ $req->due_date->format('d/m/Y') }}
                            </span>
                        @else
                            <span class="text-xs text-zinc-500">{{ $req->due_date->format('d/m/Y') }}</span>
                        @endif
                    @else
                        <span class="text-xs text-zinc-300">—</span>
                    @endif
                </x-agro.table-cell>
                <x-agro.table-cell align="right">
                    <div class="flex items-center justify-end gap-1">
                        @if($req->canBeSentBySupervisor())
                            <flux:button wire:click="send({{ $req->id }})"
                                wire:confirm="¿Enviar esta solicitud a {{ $req->winery->name }}?"
                                variant="primary" size="sm">
                                Enviar
                            </flux:button>
                            <flux:button wire:click="deleteDraft({{ $req->id }})"
                                wire:confirm="¿Eliminar este borrador? Esta acción no se puede deshacer."
                                variant="ghost" size="sm" icon="trash">
                            </flux:button>
                        @endif
                        @php
                            $canResolve = $req->canBeResolvedBySupervisor()
                                || ($req->status === \App\Models\SupervisorRequest::STATUS_PENDING
                                    && in_array($req->type, \App\Models\SupervisorRequest::WINERY_INITIATED));
                        @endphp
                        @if($canResolve)
                            <flux:button wire:click="approve({{ $req->id }})" variant="primary" size="sm">
                                Aprobar
                            </flux:button>
                            <flux:button wire:click="reject({{ $req->id }})"
                                wire:confirm="¿Rechazar esta solicitud?"
                                variant="danger" size="sm">
                                Rechazar
                            </flux:button>
                        @endif
                        @if($req->type === \App\Models\SupervisorRequest::TYPE_LABEL_REQUEST && $req->status === \App\Models\SupervisorRequest::STATUS_APPROVED)
                            <a href="{{ route('supervisor.labels.index', ['from_winery' => $req->winery_id, 'from_request' => $req->id]) }}"
                               class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium bg-green-50 text-green-700 border border-green-200 rounded-md hover:bg-green-100 transition-colors">
                                <flux:icon icon="tag" class="w-3.5 h-3.5" />
                                Crear contraetiqueta
                            </a>
                        @endif
                        @if(in_array($req->status, ['approved','rejected']) && $req->status !== 'archived')
                            <flux:button wire:click="archive({{ $req->id }})" variant="ghost" size="sm">
                                Archivar
                            </flux:button>
                        @endif
                    </div>
                </x-agro.table-cell>
            </x-agro.table-row>
        @empty
            <x-agro.table-row>
                <td colspan="8" class="px-6 py-10 text-center text-sm text-zinc-400">
                    No hay solicitudes con estos filtros.
                </td>
            </x-agro.table-row>
        @endforelse

        <x-slot name="pagination">{{ $requests->links() }}</x-slot>
    </x-agro.data-table>

    {{-- Modal nueva solicitud --}}
    <flux:modal wire:model="showCreateModal" name="create-request" class="w-full max-w-lg">
        <div class="p-6 space-y-4">
            <h3 class="text-base font-semibold text-zinc-900">Nueva solicitud</h3>

            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-zinc-700 mb-1">Bodega</label>
                    <flux:select wire:model="formWineryId">
                        <option value="">Seleccionar bodega…</option>
                        @foreach($wineries as $w)
                            <option value="{{ $w->id }}">{{ $w->name }}</option>
                        @endforeach
                    </flux:select>
                    @error('formWineryId') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-zinc-700 mb-1">Tipo</label>
                    <flux:select wire:model="formType">
                        <option value="">Seleccionar tipo…</option>
                        @foreach($typeLabels as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    @error('formType') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-zinc-700 mb-1">Título (opcional)</label>
                    <flux:input wire:model="formTitle" placeholder="Ej: Calificación lote R-2026-001" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-zinc-700 mb-1">Fecha límite de respuesta (opcional)</label>
                    <flux:input type="date" wire:model="formDueDate" :min="now()->addDay()->format('Y-m-d')" />
                    @error('formDueDate') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-zinc-700 mb-1">Notas (opcional)</label>
                    <flux:textarea wire:model="formNotes" rows="3" placeholder="Descripción o instrucciones…" />
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <flux:button wire:click="closeCreateModal" variant="ghost">Cancelar</flux:button>
                <flux:button wire:click="create" variant="primary">Crear borrador</flux:button>
            </div>
        </div>
    </flux:modal>

</div>

