<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Solicitudes y actas"
        description="Gestiona solicitudes enviadas a bodegas adscritas."
        icon="document-text"
    />

    {{-- Stats --}}
    <x-agro.stats-section key="supervisor-requests">
        <x-agro.stat-card label="Pendientes" :value="$pendingCount" icon="clock" color="yellow" />
        <x-agro.stat-card label="En revisión" :value="$inReviewCount" icon="magnifying-glass" color="blue" />
    </x-agro.stats-section>

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

        <x-agro.search-input wire:model.live.debounce.300ms="search" placeholder="Buscar bodega…" />

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

    {{-- Skeleton durante carga --}}
    <x-agro.loading-grid target="search, statusFilter, typeFilter, nextPage, previousPage" />

    {{-- Card grid --}}
    <div wire:loading.remove wire:target="search, statusFilter, typeFilter, nextPage, previousPage">
        @if($requests->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($requests as $req)
                    @php
                        $delay = min($loop->index * 50, 300);
                        $color = $statusColors[$req->status] ?? 'zinc';
                    @endphp
                    <x-agro.card
                        class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                        style="animation-delay: {{ $delay }}ms;"
                        wire:key="req-{{ $req->id }}"
                    >
                        <x-slot:header>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center shrink-0">
                                    <flux:icon icon="document-text" class="size-5 text-indigo-600" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-zinc-900 truncate text-sm">{{ $req->title ?: ($typeLabels[$req->type] ?? $req->type) }}</h3>
                                    <p class="text-xs text-zinc-500">{{ $req->winery->name }}</p>
                                </div>
                                <x-agro.status-badge :status="$req->status" :label="$statusLabels[$req->status] ?? $req->status" :color="$color" />
                            </div>
                        </x-slot:header>

                        <div class="flex-1 space-y-4">
                            {{-- Type badge --}}
                            <div>
                                @if(in_array($req->type, \App\Models\SupervisorRequest::WINERY_INITIATED))
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-blue-50 text-blue-600 border border-blue-200">Bodega</span>
                                @else
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-violet-50 text-violet-600 border border-violet-200">DO</span>
                                @endif
                                <span class="text-xs font-medium text-zinc-600 ml-1">{{ $typeLabels[$req->type] ?? $req->type }}</span>
                            </div>

                            {{-- Details --}}
                            <div class="space-y-2 text-sm">
                                <div class="flex items-center justify-between">
                                    <span class="text-zinc-400">Fecha</span>
                                    <span class="text-zinc-700 font-medium">{{ $req->created_at->format('d/m/Y') }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-zinc-400">Vence</span>
                                    @if($req->due_date)
                                        @if($req->isOverdue())
                                            <span class="inline-flex items-center gap-1 text-xs font-medium text-red-600">
                                                <flux:icon icon="exclamation-circle" class="w-3.5 h-3.5" />
                                                {{ $req->due_date->format('d/m/Y') }}
                                            </span>
                                        @else
                                            <span class="text-zinc-700 font-medium">{{ $req->due_date->format('d/m/Y') }}</span>
                                        @endif
                                    @else
                                        <span class="text-zinc-300">—</span>
                                    @endif
                                </div>
                            </div>

                            {{-- Checkbox for bulk select --}}
                            @if(in_array($req->status, ['approved','rejected']))
                                <label class="flex items-center gap-2 text-xs text-zinc-500 cursor-pointer pt-1">
                                    <input type="checkbox" wire:model="selected" value="{{ $req->id }}"
                                        class="rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer" />
                                    Seleccionar para archivar
                                </label>
                            @endif
                        </div>

                        <x-slot:footer>
                            <div class="flex items-center justify-end gap-1 flex-wrap">
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
                                        Contraetiqueta
                                    </a>
                                @endif
                                @if(in_array($req->status, ['approved','rejected']) && $req->status !== 'archived')
                                    <flux:button wire:click="archive({{ $req->id }})" variant="ghost" size="sm">
                                        Archivar
                                    </flux:button>
                                @endif
                            </div>
                        </x-slot:footer>
                    </x-agro.card>
                @endforeach
            </div>
            <div class="mt-6">{{ $requests->links() }}</div>
        @else
            <x-agro.empty-state icon="document-text" title="Sin solicitudes" description="No hay solicitudes con estos filtros." />
        @endif
    </div>

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
