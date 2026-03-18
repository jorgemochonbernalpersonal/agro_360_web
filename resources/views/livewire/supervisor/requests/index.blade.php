<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Solicitudes y actas"
        description="Gestiona solicitudes enviadas a bodegas adscritas."
        icon="document-text"
    />

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <x-agro.stat-card label="Pendientes" :value="$pendingCount" icon="clock" color="yellow" />
        <x-agro.stat-card label="En revisión" :value="$inReviewCount" icon="magnifying-glass" color="blue" />
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

    {{-- Tabla --}}
    <x-agro.data-table :headers="['Tipo', 'Bodega', 'Título', 'Estado', 'Fecha', '']">
        @forelse($requests as $req)
            <x-agro.table-row>
                <x-agro.table-cell>
                    <span class="text-xs font-medium text-zinc-600">{{ $typeLabels[$req->type] ?? $req->type }}</span>
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
                <x-agro.table-cell align="right">
                    <div class="flex items-center justify-end gap-1">
                        @if($req->canBeSentBySupervisor())
                            <flux:button wire:click="send({{ $req->id }})"
                                wire:confirm="¿Enviar esta solicitud a {{ $req->winery->name }}?"
                                variant="primary" size="sm">
                                Enviar
                            </flux:button>
                        @endif
                        @if($req->canBeResolvedBySupervisor())
                            <flux:button wire:click="approve({{ $req->id }})" variant="primary" size="sm">
                                Aprobar
                            </flux:button>
                            <flux:button wire:click="reject({{ $req->id }})"
                                wire:confirm="¿Rechazar esta solicitud?"
                                variant="danger" size="sm">
                                Rechazar
                            </flux:button>
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
                <td colspan="6" class="px-6 py-10 text-center text-sm text-zinc-400">
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
