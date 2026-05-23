<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Soporte Técnico"
        description="Reporta bugs, solicita mejoras o haz preguntas"
    >
        <x-slot:actions>
            <flux:button href="{{ roleRoute('viticulturist.support.create') }}" variant="primary" icon="plus">
                Nuevo Ticket
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Stats Cards --}}
    <x-agro.stats-section key="viticulturist-support" columns="4">
        <x-agro.stat-card label="Total" :value="$stats['total']" icon="ticket" color="agro" />
        <x-agro.stat-card label="Abiertos" :value="$stats['open']" icon="envelope-open" color="blue" />
        <x-agro.stat-card label="En Progreso" :value="$stats['in_progress']" icon="arrow-path" color="yellow" />
        <x-agro.stat-card label="Resueltos" :value="$stats['resolved']" icon="check-circle" color="green" />
    </x-agro.stats-section>

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

    {{-- Cards de Tickets --}}
    <x-agro.loading-grid target="search, filterStatus, filterType" />
    <div wire:loading.remove wire:target="search, filterStatus, filterType">
        @if($tickets->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($tickets as $ticket)
                    @php $delay = min($loop->index * 50, 300); @endphp
                    <x-agro.card
                        class="animate-fade-in-up flex flex-col hover:-translate-y-1 cursor-pointer"
                        style="animation-delay: {{ $delay }}ms;"
                        wire:key="ticket-{{ $ticket->id }}"
                        wire:click="selectTicket({{ $ticket->id }})"
                    >
                        <x-slot:header>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center shrink-0">
                                    <flux:icon icon="ticket" class="size-5 text-blue-600" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-zinc-900 truncate">{{ $ticket->title }}</h3>
                                    <p class="text-xs text-zinc-500">{{ $ticket->created_at->diffForHumans() }}</p>
                                </div>
                                <flux:badge :color="$ticket->statusColor" size="sm" class="shrink-0">{{ $ticket->getStatusLabel() }}</flux:badge>
                            </div>
                        </x-slot:header>

                        <div class="flex-1 space-y-4">
                            <p class="text-sm text-zinc-600 line-clamp-2">{{ Str::limit($ticket->description, 100) }}</p>

                            <div class="flex items-center gap-2 flex-wrap">
                                <flux:badge :color="$ticket->priorityColor" size="sm">{{ $ticket->getPriorityLabel() }}</flux:badge>
                                <span class="text-xs text-zinc-500">{{ $ticket->getTypeLabel() }}</span>
                            </div>

                            @if($ticket->comments_count > 0)
                                <div class="flex items-center gap-1 text-xs text-zinc-400">
                                    <flux:icon icon="chat-bubble-left" class="size-3" />
                                    {{ $ticket->comments_count }} {{ $ticket->comments_count === 1 ? 'comentario' : 'comentarios' }}
                                </div>
                            @endif
                        </div>

                        <x-slot:footer>
                            <div class="flex items-center justify-end gap-0.5">
                                <x-agro.action-button
                                    variant="view"
                                    wire:click.stop="selectTicket({{ $ticket->id }})"
                                    title="Ver detalles"
                                />
                            </div>
                        </x-slot:footer>
                    </x-agro.card>
                @endforeach
            </div>
            <x-agro-pagination :paginator="$tickets" />
        @else
            <x-agro.empty-state
                icon="ticket"
                title="No hay tickets que mostrar"
                description="Comienza creando tu primer ticket de soporte"
            >
                <x-slot:action>
                    <flux:button href="{{ roleRoute('viticulturist.support.create') }}" variant="primary" icon="plus">
                        Crear mi primer ticket
                    </flux:button>
                </x-slot:action>
            </x-agro.empty-state>
        @endif
    </div>

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
