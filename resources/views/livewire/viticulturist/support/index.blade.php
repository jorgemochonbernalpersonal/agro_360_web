<div>
    <x-page-header 
        title="💬 Soporte Técnico"
        subtitle="Reporta bugs, solicita mejoras o haz preguntas"
    >
        <x-slot name="actionButton">
            <a href="{{ route('viticulturist.support.create') }}" class="btn-primary">
                + Nuevo Ticket
            </a>
        </x-slot>
    </x-page-header>

    <div class="max-w-7xl mx-auto mt-6">
        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow-sm p-4">
                <div class="text-sm text-gray-600">Total</div>
                <div class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</div>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-4">
                <div class="text-sm text-gray-600">🔵 Abiertos</div>
                <div class="text-2xl font-bold text-blue-600">{{ $stats['open'] }}</div>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-4">
                <div class="text-sm text-gray-600">🟡 En Progreso</div>
                <div class="text-2xl font-bold text-yellow-600">{{ $stats['in_progress'] }}</div>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-4">
                <div class="text-sm text-gray-600">✅ Resueltos</div>
                <div class="text-2xl font-bold text-green-600">{{ $stats['resolved'] }}</div>
            </div>
        </div>

        {{-- Filtros --}}
        <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <input 
                        type="text" 
                        wire:model.live="search" 
                        placeholder="🔍 Buscar tickets..." 
                        class="form-input w-full"
                    >
                </div>
                <div>
                    <select wire:model.live="filterStatus" class="form-select w-full">
                        <option value="all">Todos los estados</option>
                        <option value="open">Abiertos</option>
                        <option value="in_progress">En Progreso</option>
                        <option value="resolved">Resueltos</option>
                        <option value="closed">Cerrados</option>
                    </select>
                </div>
                <div>
                    <select wire:model.live="filterType" class="form-select w-full">
                        <option value="all">Todos los tipos</option>
                        <option value="bug">🐛 Bugs</option>
                        <option value="feature">✨ Nuevas Funcionalidades</option>
                        <option value="improvement">🚀 Mejoras</option>
                        <option value="question">❓ Preguntas</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Lista de Tickets --}}
        <div class="bg-white rounded-lg shadow-sm">
            @forelse($tickets as $ticket)
                <div 
                    wire:click="selectTicket({{ $ticket->id }})" 
                    class="p-4 border-b border-gray-200 hover:bg-gray-50 cursor-pointer transition"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="font-semibold text-gray-900">{{ $ticket->title }}</span>
                                <span class="px-2 py-1 text-xs rounded bg-{{ $ticket->statusColor }}-100 text-{{ $ticket->statusColor }}-800">
                                    {{ $ticket->getStatusLabel() }}
                                </span>
                                <span class="px-2 py-1 text-xs rounded bg-{{ $ticket->priorityColor }}-100 text-{{ $ticket->priorityColor }}-800">
                                    {{ $ticket->getPriorityLabel() }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-600 line-clamp-2">{{ Str::limit($ticket->description, 150) }}</p>
                            <div class="flex items-center gap-4 mt-2 text-xs text-gray-500">
                                <span>{{ $ticket->getTypeLabel() }}</span>
                                <span>•</span>
                                <span>{{ $ticket->created_at->diffForHumans() }}</span>
                                @if($ticket->comments_count > 0)
                                    <span>•</span>
                                    <span>💬 {{ $ticket->comments_count }} comentarios</span>
                                @endif
                            </div>
                        </div>
                        <div class="ml-4">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-gray-500">
                    <p>No hay tickets que mostrar.</p>
                    <a href="{{ route('viticulturist.support.create') }}" class="text-green-600 hover:underline mt-2 inline-block">
                        Crear mi primer ticket
                    </a>
                </div>
            @endforelse

            {{-- Paginación --}}
            @if($tickets->hasPages())
                <div class="p-4">
                    {{ $tickets->links() }}
                </div>
            @endif
        </div>

        {{-- Modal de Detalle del Ticket --}}
        @if($selectedTicket)
            <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" wire:click="closeTicketDetail">
                <div class="bg-white rounded-lg max-w-3xl w-full max-h-[90vh] overflow-hidden" wire:click.stop>
                    {{-- Header --}}
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h2 class="text-xl font-bold text-gray-900 mb-2">{{ $selectedTicket->title }}</h2>
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-1 text-xs rounded bg-{{ $selectedTicket->statusColor }}-100 text-{{ $selectedTicket->statusColor }}-800">
                                        {{ $selectedTicket->getStatusLabel() }}
                                    </span>
                                    <span class="px-2 py-1 text-xs rounded bg-{{ $selectedTicket->priorityColor }}-100 text-{{ $selectedTicket->priorityColor }}-800">
                                        {{ $selectedTicket->getPriorityLabel() }}
                                    </span>
                                    <span class="text-sm text-gray-600">{{ $selectedTicket->getTypeLabel() }}</span>
                                </div>
                            </div>
                            <button wire:click="closeTicketDetail" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Body --}}
                    <div class="p-6 overflow-y-auto max-h-[60vh]">
                        {{-- Descripción --}}
                        <div class="mb-6">
                            <h3 class="font-semibold text-gray-900 mb-2">Descripción</h3>
                            <p class="text-gray-700 whitespace-pre-wrap">{{ $selectedTicket->description }}</p>
                            
                            {{-- Imagen si existe --}}
                            @if($selectedTicket->image)
                                <div class="mt-4">
                                    <h4 class="text-sm font-medium text-gray-700 mb-2">Imagen adjunta:</h4>
                                    <a href="{{ $selectedTicket->image_url }}" target="_blank" class="block">
                                        <img 
                                            src="{{ $selectedTicket->image_url }}" 
                                            alt="Imagen del ticket" 
                                            class="max-w-full h-auto max-h-96 rounded-lg border border-gray-300 hover:opacity-90 transition cursor-pointer"
                                        >
                                    </a>
                                    <p class="text-xs text-gray-500 mt-1">Haz clic en la imagen para verla en tamaño completo</p>
                                </div>
                            @endif
                            
                            <p class="text-xs text-gray-500 mt-2">Creado {{ $selectedTicket->created_at->diffForHumans() }}</p>
                        </div>

                        {{-- Comentarios --}}
                        @if($selectedTicket->comments->count() > 0)
                            <div class="mb-6">
                                <h3 class="font-semibold text-gray-900 mb-3">Comentarios</h3>
                                <div class="space-y-3">
                                    @foreach($selectedTicket->comments as $comment)
                                        <div class="bg-gray-50 rounded-lg p-3">
                                            <div class="flex items-center justify-between mb-1">
                                                <span class="font-medium text-sm text-gray-900">{{ $comment->user->name }}</span>
                                                <span class="text-xs text-gray-500">{{ $comment->created_at->diffForHumans() }}</span>
                                            </div>
                                            <p class="text-sm text-gray-700">{{ $comment->comment }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Añadir Comentario --}}
                        @if($selectedTicket->isOpen() || $selectedTicket->status === 'in_progress')
                            <div>
                                <h3 class="font-semibold text-gray-900 mb-2">Añadir Comentario</h3>
                                <textarea 
                                    wire:model="newComment" 
                                    rows="3" 
                                    class="form-textarea w-full"
                                    placeholder="Escribe tu comentario..."
                                ></textarea>
                                @error('newComment') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                                <button wire:click="addComment" class="btn-primary mt-2">
                                    Añadir Comentario
                                </button>
                            </div>
                        @endif
                    </div>

                    {{-- Footer --}}
                    <div class="p-4 border-t border-gray-200 flex justify-between">
                        <div>
                            @if($selectedTicket->isClosed())
                                <button wire:click="reopenTicket({{ $selectedTicket->id }})" class="btn-secondary text-sm">
                                    Reabrir Ticket
                                </button>
                            @else
                                <button wire:click="closeTicket({{ $selectedTicket->id }})" class="btn-secondary text-sm">
                                    Cerrar Ticket
                                </button>
                            @endif
                        </div>
                        <button wire:click="closeTicketDetail" class="btn-secondary">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
