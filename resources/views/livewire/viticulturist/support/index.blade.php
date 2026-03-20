<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Soporte Técnico"
        description="Reporta bugs, solicita mejoras o haz preguntas"
    >
        <x-slot:actions>
            <flux:button href="{{ route('viticulturist.support.create') }}" variant="primary" icon="plus">
                Nuevo Ticket
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Stats Cards --}}
    <div x-data="{
        open: localStorage.getItem('viticulturist-support-stats-open') !== 'false',
        toggle() {
            this.open = !this.open;
            localStorage.setItem('viticulturist-support-stats-open', String(this.open));
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
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <x-agro.stat-card label="Total" :value="$stats['total']" icon="ticket" color="agro" />
            <x-agro.stat-card label="Abiertos" :value="$stats['open']" icon="envelope-open" color="blue" />
            <x-agro.stat-card label="En Progreso" :value="$stats['in_progress']" icon="arrow-path" color="yellow" />
            <x-agro.stat-card label="Resueltos" :value="$stats['resolved']" icon="check-circle" color="green" />
        </div>
        </div>
    </div>

    {{-- Filtros --}}
    <x-agro.filter-bar>
        <x-agro.filter-input
            wire:model.live="search"
            placeholder="Buscar tickets..."
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
    <x-agro.data-table :headers="['Título', 'Estado', 'Prioridad', 'Tipo', 'Fecha', 'Acciones']" empty-message="No hay tickets que mostrar" empty-description="Comienza creando tu primer ticket de soporte">
        @if($tickets->count() > 0)
            @foreach($tickets as $ticket)
                <x-agro.table-row wire:click="selectTicket({{ $ticket->id }})" class="cursor-pointer">
                    <x-agro.table-cell>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-agro-50 flex items-center justify-center">
                                <flux:icon icon="question-mark-circle" class="size-5 text-agro-600" />
                            </div>
                            <div>
                                <div class="text-sm font-bold text-zinc-900">{{ $ticket->title }}</div>
                                <div class="text-xs text-zinc-500 mt-1 line-clamp-1">{{ Str::limit($ticket->description, 80) }}</div>
                                @if($ticket->comments_count > 0)
                                    <div class="text-xs text-zinc-400 mt-1 flex items-center gap-1">
                                        <flux:icon icon="chat-bubble-left" class="size-3" />
                                        {{ $ticket->comments_count }} {{ $ticket->comments_count === 1 ? 'comentario' : 'comentarios' }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </x-agro.table-cell>
                    <x-agro.table-cell>
                        <flux:badge :color="$ticket->statusColor" size="sm">{{ $ticket->getStatusLabel() }}</flux:badge>
                    </x-agro.table-cell>
                    <x-agro.table-cell>
                        <flux:badge :color="$ticket->priorityColor" size="sm">{{ $ticket->getPriorityLabel() }}</flux:badge>
                    </x-agro.table-cell>
                    <x-agro.table-cell>
                        <span class="text-sm text-zinc-700">{{ $ticket->getTypeLabel() }}</span>
                    </x-agro.table-cell>
                    <x-agro.table-cell>
                        <span class="text-sm text-zinc-600">{{ $ticket->created_at->diffForHumans() }}</span>
                    </x-agro.table-cell>
                    <x-agro.table-cell align="right">
                        <flux:button
                            wire:click.stop="selectTicket({{ $ticket->id }})"
                            variant="ghost"
                            size="sm"
                            icon="eye"
                            title="Ver detalles"
                        />
                    </x-agro.table-cell>
                </x-agro.table-row>
            @endforeach
            <x-slot name="pagination">
                {{ $tickets->links() }}
            </x-slot>
        @else
            <x-slot name="emptyAction">
                <flux:button href="{{ route('viticulturist.support.create') }}" variant="primary" icon="plus">
                    Crear mi primer ticket
                </flux:button>
            </x-slot>
        @endif
    </x-agro.data-table>

    {{-- Modal de Detalle del Ticket --}}
        @if($selectedTicket)
            <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" wire:click="closeTicketDetail">
                <div class="bg-white rounded-lg max-w-3xl w-full max-h-[90vh] overflow-hidden" wire:click.stop>
                    {{-- Header --}}
                    <div class="p-6 border-b border-zinc-200">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="text-xl font-bold text-zinc-900 mb-2">{{ $selectedTicket->title }}</div>
                                <div class="flex items-center gap-2">
                                    <flux:badge :color="$selectedTicket->statusColor" size="sm">{{ $selectedTicket->getStatusLabel() }}</flux:badge>
                                    <flux:badge :color="$selectedTicket->priorityColor" size="sm">{{ $selectedTicket->getPriorityLabel() }}</flux:badge>
                                    <span class="text-sm text-zinc-600">{{ $selectedTicket->getTypeLabel() }}</span>
                                </div>
                            </div>
                            <flux:button wire:click="closeTicketDetail" variant="ghost" size="sm" icon="x-mark" />
                        </div>
                    </div>

                    {{-- Body --}}
                    <div class="p-6 overflow-y-auto max-h-[60vh]">
                        {{-- Descripción --}}
                        <div class="mb-6">
                            <div class="font-semibold text-zinc-900 mb-2">Descripción</div>
                            <p class="text-zinc-700 whitespace-pre-wrap">{{ $selectedTicket->description }}</p>

                            {{-- Imágenes adjuntas --}}
                            @if(!empty($selectedTicket->image_urls))
                                <div class="mt-4">
                                    <div class="text-sm font-medium text-zinc-700 mb-2">Imágenes adjuntas ({{ count($selectedTicket->image_urls) }}):</div>
                                    <div class="grid grid-cols-2 gap-2">
                                        @foreach($selectedTicket->image_urls as $url)
                                            <a href="{{ $url }}" target="_blank" class="block">
                                                <img
                                                    src="{{ $url }}"
                                                    alt="Imagen del ticket"
                                                    loading="lazy"
                                                    class="w-full h-40 object-cover rounded-lg border border-zinc-300 hover:opacity-90 transition cursor-pointer"
                                                >
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <p class="text-xs text-zinc-500 mt-2">Creado {{ $selectedTicket->created_at->diffForHumans() }}</p>
                        </div>

                        {{-- Comentarios --}}
                        @if($selectedTicket->comments->count() > 0)
                            <div class="mb-6">
                                <div class="font-semibold text-zinc-900 mb-3">Comentarios</div>
                                <div class="space-y-3">
                                    @foreach($selectedTicket->comments as $comment)
                                        <div class="bg-zinc-50 rounded-lg p-3">
                                            <div class="flex items-center justify-between mb-1">
                                                <span class="font-medium text-sm text-zinc-900">{{ $comment->user->name }}</span>
                                                <span class="text-xs text-zinc-500">{{ $comment->created_at->diffForHumans() }}</span>
                                            </div>
                                            <p class="text-sm text-zinc-700">{{ $comment->comment }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Añadir Comentario --}}
                        @if($selectedTicket->isOpen() || $selectedTicket->status === 'in_progress')
                            <div>
                                <div class="font-semibold text-zinc-900 mb-2">Añadir Comentario</div>
                                <flux:textarea
                                    wire:model="newComment"
                                    rows="3"
                                    placeholder="Escribe tu comentario..."
                                />
                                @error('newComment') <flux:error>{{ $message }}</flux:error> @enderror
                                <flux:button wire:click="addComment" variant="primary" size="sm" class="mt-2">
                                    Añadir Comentario
                                </flux:button>
                            </div>
                        @endif
                    </div>

                    {{-- Footer --}}
                    <div class="p-4 border-t border-zinc-200 flex justify-end">
                        <flux:button wire:click="closeTicketDetail" variant="outline">
                            Cerrar
                        </flux:button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

