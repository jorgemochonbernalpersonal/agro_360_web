<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Tickets de Soporte"
        description="Gestiona todos los tickets de soporte del sistema"
    />

    {{-- Estadísticas --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-5 gap-4">
        <x-agro.stat-card label="Total"       :value="$stats['total']"       icon="inbox"          color="agro"   />
        <x-agro.stat-card label="Abiertos"    :value="$stats['open']"        icon="envelope-open"  color="blue"   />
        <x-agro.stat-card label="En Progreso" :value="$stats['in_progress']" icon="clock"          color="yellow" />
        <x-agro.stat-card label="Resueltos"   :value="$stats['resolved']"    icon="check-circle"   color="agro"   />
        <x-agro.stat-card label="Cerrados"    :value="$stats['closed']"      icon="archive-box"    color="purple" />
    </div>

    {{-- Filtros --}}
    <x-agro.filter-bar>
        <x-agro.filter-input
            wire:model.live="search"
            placeholder="Buscar por título, descripción, nombre o email..."
        />
        <x-agro.filter-select wire:model.live="filterStatus">
            <option value="all">Todos los estados</option>
            <option value="open">Abiertos</option>
            <option value="in_progress">En Progreso</option>
            <option value="resolved">Resueltos</option>
            <option value="closed">Cerrados</option>
        </x-agro.filter-select>
        <x-agro.filter-select wire:model.live="filterType">
            <option value="all">Todos los tipos</option>
            <option value="bug">Bugs</option>
            <option value="feature">Nuevas Funcionalidades</option>
            <option value="improvement">Mejoras</option>
            <option value="question">Preguntas</option>
        </x-agro.filter-select>
    </x-agro.filter-bar>

    {{-- Tabla de Tickets --}}
    <x-agro.data-table
        :headers="['Título', 'Usuario', 'Estado', 'Prioridad', 'Tipo', 'Fecha', 'Acciones']"
        empty-message="No hay tickets que mostrar"
        empty-description="No hay tickets con los filtros seleccionados"
        empty-icon="inbox"
    >
        @if($tickets->count() > 0)
            @foreach($tickets as $ticket)
                <x-agro.table-row wire:click="selectTicket({{ $ticket->id }})" class="cursor-pointer">
                    <x-agro.table-cell>
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-red-50 flex items-center justify-center flex-shrink-0">
                                <flux:icon icon="question-mark-circle" class="size-4 text-red-600" />
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-zinc-900 truncate">{{ $ticket->title }}</p>
                                <p class="text-xs text-zinc-400 mt-0.5 line-clamp-1">{{ Str::limit($ticket->description, 80) }}</p>
                            </div>
                        </div>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <p class="text-sm font-semibold text-zinc-900">{{ $ticket->user->name }}</p>
                        <p class="text-xs text-zinc-400">{{ $ticket->user->email }}</p>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <flux:badge :color="$ticket->statusColor" size="sm">
                            {{ $ticket->getStatusLabel() }}
                        </flux:badge>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <flux:badge :color="$ticket->priorityColor" size="sm">
                            {{ $ticket->getPriorityLabel() }}
                        </flux:badge>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <span class="text-sm text-zinc-600">{{ $ticket->getTypeLabel() }}</span>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <span class="text-sm text-zinc-500">{{ $ticket->created_at->diffForHumans() }}</span>
                    </x-agro.table-cell>

                    <x-agro.table-cell align="right">
                        <flux:button
                            variant="ghost"
                            size="sm"
                            icon="eye"
                            wire:click.stop="selectTicket({{ $ticket->id }})"
                            tooltip="Ver detalles"
                        />
                    </x-agro.table-cell>
                </x-agro.table-row>
            @endforeach

            <x-slot name="pagination">
                {{ $tickets->links() }}
            </x-slot>
        @endif
    </x-agro.data-table>

    {{-- Modal de Detalle del Ticket --}}
    @if($selectedTicket)
        <div
            class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
            wire:click="closeTicketDetail"
        >
            <div
                class="bg-white rounded-xl shadow-xl max-w-4xl w-full max-h-[90vh] overflow-hidden flex flex-col"
                wire:click.stop
            >
                {{-- Header del modal --}}
                <div class="px-6 py-4 border-b border-zinc-200 bg-zinc-50 flex items-start justify-between gap-4">
                    <div class="flex-1 min-w-0">
                        <flux:heading size="md" class="truncate">{{ $selectedTicket->title }}</flux:heading>
                        <div class="flex items-center gap-2 flex-wrap mt-2">
                            <flux:badge :color="$selectedTicket->statusColor" size="sm">
                                {{ $selectedTicket->getStatusLabel() }}
                            </flux:badge>
                            <flux:badge :color="$selectedTicket->priorityColor" size="sm">
                                {{ $selectedTicket->getPriorityLabel() }}
                            </flux:badge>
                            <span class="text-sm text-zinc-500">{{ $selectedTicket->getTypeLabel() }}</span>
                            <span class="text-sm text-zinc-400">por {{ $selectedTicket->user->name }}</span>
                        </div>
                    </div>
                    <flux:button
                        wire:click="closeTicketDetail"
                        variant="ghost"
                        size="sm"
                        icon="x-mark"
                    />
                </div>

                {{-- Cuerpo del modal --}}
                <div class="px-6 py-5 overflow-y-auto flex-1 space-y-5">

                    {{-- Descripción --}}
                    <div>
                        <p class="text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-2">Descripción</p>
                        <p class="text-sm text-zinc-700 whitespace-pre-wrap">{{ $selectedTicket->description }}</p>

                        @if($selectedTicket->image)
                            <div class="mt-4">
                                <p class="text-xs font-medium text-zinc-500 mb-2">Imagen adjunta</p>
                                <a href="{{ $selectedTicket->image_url }}" target="_blank" class="block">
                                    <img
                                        src="{{ $selectedTicket->image_url }}"
                                        alt="Imagen del ticket"
                                        class="max-w-full h-auto max-h-80 rounded-lg border border-zinc-200 hover:opacity-90 transition cursor-pointer"
                                    >
                                </a>
                            </div>
                        @endif

                        <p class="text-xs text-zinc-400 mt-3">Creado {{ $selectedTicket->created_at->diffForHumans() }}</p>
                    </div>

                    {{-- Asignación --}}
                    <div class="bg-zinc-50 rounded-lg p-4 space-y-3">
                        <p class="text-xs font-semibold text-zinc-500 uppercase tracking-wide">Asignación</p>
                        <div class="flex items-center gap-3">
                            <flux:select wire:model="assignTo">
                                <option value="">Sin asignar</option>
                                @foreach(\App\Models\User::where('role', 'admin')->get() as $admin)
                                    <option value="{{ $admin->id }}">{{ $admin->name }}</option>
                                @endforeach
                            </flux:select>
                            <flux:button wire:click="assignTicket" variant="primary" size="sm">
                                Asignar
                            </flux:button>
                        </div>
                        @if($selectedTicket->assignedTo)
                            <p class="text-sm text-zinc-600">
                                Asignado a: <strong class="text-zinc-900">{{ $selectedTicket->assignedTo->name }}</strong>
                            </p>
                        @endif
                    </div>

                    {{-- Cambiar Estado --}}
                    <div class="bg-zinc-50 rounded-lg p-4 space-y-3">
                        <p class="text-xs font-semibold text-zinc-500 uppercase tracking-wide">Cambiar Estado</p>
                        <div class="flex gap-2 flex-wrap">
                            <flux:button wire:click="changeStatus('open')"        variant="outline" size="sm">Abrir</flux:button>
                            <flux:button wire:click="changeStatus('in_progress')" variant="outline" size="sm">En Progreso</flux:button>
                            <flux:button wire:click="changeStatus('resolved')"    variant="primary" size="sm">Resolver</flux:button>
                            <flux:button wire:click="changeStatus('closed')"      variant="outline" size="sm">Cerrar</flux:button>
                        </div>
                    </div>

                    {{-- Comentarios --}}
                    @if($selectedTicket->comments->count() > 0)
                        <div>
                            <p class="text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-3">
                                Comentarios ({{ $selectedTicket->comments->count() }})
                            </p>
                            <div class="space-y-2">
                                @foreach($selectedTicket->comments as $comment)
                                    <div class="bg-zinc-50 border border-zinc-100 rounded-lg px-4 py-3">
                                        <div class="flex items-center justify-between mb-1">
                                            <span class="text-sm font-semibold text-zinc-900">{{ $comment->user->name }}</span>
                                            <span class="text-xs text-zinc-400">{{ $comment->created_at->diffForHumans() }}</span>
                                        </div>
                                        <p class="text-sm text-zinc-700">{{ $comment->comment }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Añadir Comentario --}}
                    <div>
                        <p class="text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-2">Añadir Comentario</p>
                        <flux:textarea
                            wire:model="newComment"
                            rows="3"
                            placeholder="Escribe tu comentario..."
                        />
                        @error('newComment')
                            <flux:error>{{ $message }}</flux:error>
                        @enderror
                        <div class="mt-2">
                            <flux:button wire:click="addComment" variant="primary" size="sm" icon="chat-bubble-left">
                                Añadir Comentario
                            </flux:button>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="px-6 py-3 border-t border-zinc-200 bg-zinc-50 flex justify-end">
                    <flux:button wire:click="closeTicketDetail" variant="outline">
                        Cerrar
                    </flux:button>
                </div>
            </div>
        </div>
    @endif
</div>
