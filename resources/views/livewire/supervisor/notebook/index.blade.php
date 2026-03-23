<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Acceso al Cuaderno de Campo"
        description="Gestiona las solicitudes de acceso al cuaderno de campo de los viticultores de la DO"
    >
        <x-slot:actions>
            <flux:button wire:click="openRequestModal" variant="primary" icon="plus">
                Solicitar acceso
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Estadísticas --}}
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
        <x-agro.stat-card label="Total solicitudes" :value="$stats['total']"    icon="document-text"   color="blue"   />
        <x-agro.stat-card label="Pendientes"         :value="$stats['pending']"  icon="clock"           color="yellow" />
        <x-agro.stat-card label="Aprobadas"          :value="$stats['approved']" icon="check-circle"    color="agro"   />
        <x-agro.stat-card label="Rechazadas"         :value="$stats['rejected']" icon="x-circle"        color="red"    />
    </div>

    {{-- Filtros --}}
    <x-agro.filter-bar>
        <x-agro.filter-input
            wire:model.live="search"
            placeholder="Buscar por viticultor o email..."
        />
        <x-agro.filter-select wire:model.live="filterStatus">
            <option value="all">Todos los estados</option>
            <option value="pending">Pendientes</option>
            <option value="approved">Aprobadas</option>
            <option value="rejected">Rechazadas</option>
        </x-agro.filter-select>
    </x-agro.filter-bar>

    {{-- Tabla --}}
    <x-agro.data-table
        :headers="['Viticultor', 'Estado', 'Solicitado', 'Respondido', '']"
        empty-message="No hay solicitudes de acceso"
        empty-description="Solicita acceso al cuaderno de campo de tus viticultores"
        empty-icon="book-open"
    >
        @if($requests->count() > 0)
            @foreach($requests as $req)
                <x-agro.table-row>
                    <x-agro.table-cell>
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-indigo-50 flex items-center justify-center flex-shrink-0">
                                <flux:icon icon="user" class="size-4 text-indigo-600" />
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-zinc-900">{{ $req->viticulturist?->name ?? '—' }}</p>
                                <p class="text-xs text-zinc-400">{{ $req->viticulturist?->email }}</p>
                            </div>
                        </div>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        @php
                            $statusMap = [
                                'pending'  => ['label' => 'Pendiente',  'color' => 'yellow'],
                                'approved' => ['label' => 'Aprobada',   'color' => 'green'],
                                'rejected' => ['label' => 'Rechazada',  'color' => 'red'],
                            ];
                            $s = $statusMap[$req->status] ?? ['label' => $req->status, 'color' => null];
                        @endphp
                        <flux:badge :color="$s['color']" size="sm">{{ $s['label'] }}</flux:badge>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        @if($req->requested_at)
                            <p class="text-sm text-zinc-700">{{ $req->requested_at->format('d/m/Y') }}</p>
                            <p class="text-xs text-zinc-400">{{ $req->requested_at->diffForHumans() }}</p>
                        @else
                            <span class="text-zinc-400">—</span>
                        @endif
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        @if($req->responded_at)
                            <p class="text-sm text-zinc-700">{{ $req->responded_at->format('d/m/Y') }}</p>
                            <p class="text-xs text-zinc-400">{{ $req->responded_at->diffForHumans() }}</p>
                        @else
                            <span class="text-xs text-zinc-400">Sin respuesta</span>
                        @endif
                    </x-agro.table-cell>

                    <x-agro.table-cell align="right">
                        @if($req->status !== 'rejected')
                            <flux:button
                                variant="ghost"
                                size="sm"
                                icon="trash"
                                class="text-red-400 hover:text-red-600"
                                wire:click="revokeAccess({{ $req->id }})"
                                wire:confirm="¿Revocar la solicitud de acceso al cuaderno de {{ $req->viticulturist?->name }}?"
                                tooltip="Revocar acceso"
                            />
                        @endif
                    </x-agro.table-cell>
                </x-agro.table-row>
            @endforeach

            <x-slot name="pagination">
                {{ $requests->links() }}
            </x-slot>
        @endif
    </x-agro.data-table>

    {{-- Modal: Nueva solicitud --}}
    <flux:modal wire:model="showRequestModal" class="w-full max-w-lg">
        <div class="p-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="p-2 rounded-lg bg-indigo-50">
                    <flux:icon icon="book-open" class="size-5 text-indigo-600" />
                </div>
                <div>
                    <h3 class="text-base font-semibold text-zinc-900">Solicitar acceso al cuaderno</h3>
                    <p class="text-xs text-zinc-500">El viticultor deberá aprobar tu solicitud</p>
                </div>
            </div>

            @if($availableForRequest->count() > 0)
                <div class="space-y-1 max-h-64 overflow-y-auto border border-zinc-200 rounded-lg p-1">
                    @foreach($availableForRequest as $v)
                        <label class="flex items-center gap-3 px-3 py-2.5 rounded-md cursor-pointer hover:bg-zinc-50 transition-colors {{ (string)$targetViticulturistId === (string)$v->id ? 'bg-indigo-50' : '' }}">
                            <input
                                type="radio"
                                wire:model.live="targetViticulturistId"
                                value="{{ $v->id }}"
                                class="text-indigo-600 focus:ring-indigo-500"
                            />
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-zinc-900 truncate">{{ $v->name }}</p>
                                <p class="text-xs text-zinc-400 truncate">{{ $v->email }}</p>
                            </div>
                        </label>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-zinc-400">
                    <flux:icon icon="check-circle" class="size-10 mx-auto mb-2 text-green-300" />
                    <p class="text-sm">Todos los viticultores ya tienen solicitud activa</p>
                </div>
            @endif

            @error('targetViticulturistId')
                <p class="text-xs text-red-500 mt-2">{{ $message }}</p>
            @enderror

            <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-zinc-100">
                <flux:button variant="ghost" wire:click="closeRequestModal">Cancelar</flux:button>
                <flux:button
                    variant="primary"
                    wire:click="requestAccess"
                    :disabled="!$targetViticulturistId"
                >
                    Enviar solicitud
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
