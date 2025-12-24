<div class="space-y-6 animate-fade-in">
    @php
        $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
    @endphp
    <x-page-header 
        :icon="$icon"
        title="Tickets de Soporte"
        description="Gestiona todos los tickets de soporte del sistema"
        icon-color="from-red-500 to-red-700"
    />

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-6 border border-gray-200">
            <p class="text-sm font-medium text-gray-700">Total</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6 border border-blue-200">
            <p class="text-sm font-medium text-blue-700">Abiertos</p>
            <p class="text-3xl font-bold text-blue-900 mt-1">{{ $stats['open'] }}</p>
        </div>
        <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-xl p-6 border border-yellow-200">
            <p class="text-sm font-medium text-yellow-700">En Progreso</p>
            <p class="text-3xl font-bold text-yellow-900 mt-1">{{ $stats['in_progress'] }}</p>
        </div>
        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6 border border-green-200">
            <p class="text-sm font-medium text-green-700">Resueltos</p>
            <p class="text-3xl font-bold text-green-900 mt-1">{{ $stats['resolved'] }}</p>
        </div>
        <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-6 border border-gray-200">
            <p class="text-sm font-medium text-gray-700">Cerrados</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['closed'] }}</p>
        </div>
    </div>

    {{-- Filtros --}}
    <x-filter-section title="Filtros de Búsqueda" color="red">
        <x-filter-input 
            wire:model.live="search" 
            placeholder="Buscar por título, descripción, nombre o email..."
        />
        <x-filter-select wire:model.live="filterStatus">
            <option value="all">Todos los estados</option>
            <option value="open">Abiertos</option>
            <option value="in_progress">En Progreso</option>
            <option value="resolved">Resueltos</option>
            <option value="closed">Cerrados</option>
        </x-filter-select>
        <x-filter-select wire:model.live="filterType">
            <option value="all">Todos los tipos</option>
            <option value="bug">🐛 Bugs</option>
            <option value="feature">✨ Nuevas Funcionalidades</option>
            <option value="improvement">🚀 Mejoras</option>
            <option value="question">❓ Preguntas</option>
        </x-filter-select>
    </x-filter-section>

    {{-- Tabla de Tickets --}}
    @php
        $headers = [
            ['label' => 'Título', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>'],
            ['label' => 'Usuario', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>'],
            ['label' => 'Estado', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'],
            ['label' => 'Prioridad', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>'],
            ['label' => 'Tipo', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>'],
            ['label' => 'Fecha', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>'],
            'Acciones',
        ];
    @endphp

    <x-data-table :headers="$headers" empty-message="No hay tickets que mostrar">
        @if($tickets->count() > 0)
            @foreach($tickets as $ticket)
                <x-table-row wire:click="selectTicket({{ $ticket->id }})" class="cursor-pointer">
                    <x-table-cell>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-red-400 to-red-600 flex items-center justify-center shadow-sm">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="text-sm font-bold text-gray-900">{{ $ticket->title }}</div>
                                <div class="text-xs text-gray-500 mt-1 line-clamp-1">{{ Str::limit($ticket->description, 80) }}</div>
                            </div>
                        </div>
                    </x-table-cell>
                    <x-table-cell>
                        <div class="text-sm">
                            <div class="font-semibold text-gray-900">{{ $ticket->user->name }}</div>
                            <div class="text-xs text-gray-500">{{ $ticket->user->email }}</div>
                        </div>
                    </x-table-cell>
                    <x-table-cell>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-{{ $ticket->statusColor }}-100 text-{{ $ticket->statusColor }}-800">
                            {{ $ticket->getStatusLabel() }}
                        </span>
                    </x-table-cell>
                    <x-table-cell>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-{{ $ticket->priorityColor }}-100 text-{{ $ticket->priorityColor }}-800">
                            {{ $ticket->getPriorityLabel() }}
                        </span>
                    </x-table-cell>
                    <x-table-cell>
                        <span class="text-sm text-gray-700">{{ $ticket->getTypeLabel() }}</span>
                    </x-table-cell>
                    <x-table-cell>
                        <span class="text-sm text-gray-600">{{ $ticket->created_at->diffForHumans() }}</span>
                    </x-table-cell>
                    <x-table-actions align="right">
                        <button 
                            wire:click.stop="selectTicket({{ $ticket->id }})"
                            class="p-2 rounded-lg transition-all duration-200 text-red-600 hover:bg-red-50"
                            title="Ver detalles"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </x-table-actions>
                </x-table-row>
            @endforeach
            <x-slot name="pagination">
                {{ $tickets->links() }}
            </x-slot>
        @else
            <x-slot name="emptyAction">
                <p class="text-sm text-gray-500">No hay tickets con los filtros seleccionados</p>
            </x-slot>
        @endif
    </x-data-table>

    {{-- Modal de Detalle del Ticket --}}
    @if($selectedTicket)
        <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" wire:click="closeTicketDetail">
            <div class="bg-white rounded-lg max-w-4xl w-full max-h-[90vh] overflow-hidden" wire:click.stop>
                {{-- Header --}}
                <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-red-50 to-red-100">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h2 class="text-xl font-bold text-gray-900 mb-2">{{ $selectedTicket->title }}</h2>
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="px-2 py-1 text-xs rounded bg-{{ $selectedTicket->statusColor }}-100 text-{{ $selectedTicket->statusColor }}-800">
                                    {{ $selectedTicket->getStatusLabel() }}
                                </span>
                                <span class="px-2 py-1 text-xs rounded bg-{{ $selectedTicket->priorityColor }}-100 text-{{ $selectedTicket->priorityColor }}-800">
                                    {{ $selectedTicket->getPriorityLabel() }}
                                </span>
                                <span class="text-sm text-gray-600">{{ $selectedTicket->getTypeLabel() }}</span>
                                <span class="text-sm text-gray-500">por {{ $selectedTicket->user->name }}</span>
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
                            </div>
                        @endif
                        
                        <p class="text-xs text-gray-500 mt-2">Creado {{ $selectedTicket->created_at->diffForHumans() }}</p>
                    </div>

                    {{-- Asignación (solo admin) --}}
                    <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                        <h3 class="font-semibold text-gray-900 mb-2">Asignación</h3>
                        <div class="flex items-center gap-3">
                            <select wire:model="assignTo" class="flex-1 border-gray-300 rounded-lg">
                                <option value="">Sin asignar</option>
                                @foreach(\App\Models\User::where('role', 'admin')->get() as $admin)
                                    <option value="{{ $admin->id }}">{{ $admin->name }}</option>
                                @endforeach
                            </select>
                            <button wire:click="assignTicket" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                Asignar
                            </button>
                        </div>
                        @if($selectedTicket->assignedTo)
                            <p class="text-sm text-gray-600 mt-2">Asignado a: <strong>{{ $selectedTicket->assignedTo->name }}</strong></p>
                        @endif
                    </div>

                    {{-- Cambiar Estado (solo admin) --}}
                    <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                        <h3 class="font-semibold text-gray-900 mb-2">Cambiar Estado</h3>
                        <div class="flex gap-2 flex-wrap">
                            <button wire:click="changeStatus('open')" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
                                Abrir
                            </button>
                            <button wire:click="changeStatus('in_progress')" class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 text-sm">
                                En Progreso
                            </button>
                            <button wire:click="changeStatus('resolved')" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm">
                                Resolver
                            </button>
                            <button wire:click="changeStatus('closed')" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 text-sm">
                                Cerrar
                            </button>
                        </div>
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
                    <div>
                        <h3 class="font-semibold text-gray-900 mb-2">Añadir Comentario</h3>
                        <textarea 
                            wire:model="newComment" 
                            rows="3" 
                            class="w-full border-gray-300 rounded-lg"
                            placeholder="Escribe tu comentario..."
                        ></textarea>
                        @error('newComment') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                        <button wire:click="addComment" class="mt-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                            Añadir Comentario
                        </button>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="p-4 border-t border-gray-200 flex justify-end">
                    <button wire:click="closeTicketDetail" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

