<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="{{ __('Tickets de Soporte') }}"
        :description="__('Gestiona todos los tickets de soporte del sistema')"
    >
        <x-slot:actions>
            <flux:button wire:click="exportCsv" variant="ghost" icon="arrow-down-tray">{{ __('Exportar CSV') }}</flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Estadísticas --}}
    <div x-data="{
        open: localStorage.getItem('admin-support-stats-open') !== 'false',
        toggle() {
            this.open = !this.open;
            localStorage.setItem('admin-support-stats-open', String(this.open));
        }
    }">
        <button
            @click="toggle()"
            class="flex items-center gap-1.5 text-[11px] font-semibold text-zinc-400 uppercase tracking-widest hover:text-zinc-600 transition-colors mb-3"
        >
            <span>{{ __('Estadísticas') }}</span>
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
        <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-5 gap-4">
            <x-agro.stat-card :label="__('Total')"       :value="$stats['total']"       icon="inbox"          color="agro"   />
            <x-agro.stat-card :label="__('Abiertos')"    :value="$stats['open']"        icon="envelope-open"  color="blue"   />
            <x-agro.stat-card :label="__('En Progreso')" :value="$stats['in_progress']" icon="clock"          color="yellow" />
            <x-agro.stat-card :label="__('Resueltos')"   :value="$stats['resolved']"    icon="check-circle"   color="agro"   />
            <x-agro.stat-card :label="__('Cerrados')"    :value="$stats['closed']"      icon="archive-box"    color="purple" />
        </div>
        </div>
    </div>

    {{-- Filtros --}}
    <x-agro.filter-bar>
        <x-agro.filter-input
            wire:model.live="search"
            placeholder="{{ __('Buscar por título, descripción, nombre o email...') }}"
        />
        <x-agro.filter-select wire:model.live="filterStatus">
            <option value="all">{{ __('Todos los estados') }}</option>
            <option value="open">{{ __('Abiertos') }}</option>
            <option value="in_progress">{{ __('En Progreso') }}</option>
            <option value="resolved">{{ __('Resueltos') }}</option>
            <option value="closed">{{ __('Cerrados') }}</option>
        </x-agro.filter-select>
        <x-agro.filter-select wire:model.live="filterType">
            <option value="all">{{ __('Todos los tipos') }}</option>
            <option value="bug">{{ __('Bugs') }}</option>
            <option value="feature">{{ __('Nuevas Funcionalidades') }}</option>
            <option value="improvement">{{ __('Mejoras') }}</option>
            <option value="question">{{ __('Preguntas') }}</option>
        </x-agro.filter-select>
        <x-agro.filter-select wire:model.live="filterPriority">
            <option value="all">{{ __('Todas las prioridades') }}</option>
            <option value="low">{{ __('Baja') }}</option>
            <option value="medium">{{ __('Media') }}</option>
            <option value="high">{{ __('Alta') }}</option>
            <option value="critical">{{ __('Crítica') }}</option>
        </x-agro.filter-select>
    </x-agro.filter-bar>

    {{-- Tabla de Tickets --}}
    <x-agro.data-table
        :headers="['Título', 'Usuario', 'Estado', 'Prioridad', 'Tipo', 'Fecha', 'Acciones']"
        empty-:message="__('No hay tickets que mostrar')"
        empty-:description="__('No hay tickets con los filtros seleccionados')"
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
                        <p class="text-sm font-semibold text-zinc-900">{{ $ticket->user?->name ?? 'Usuario eliminado' }}</p>
                        <p class="text-xs text-zinc-400">{{ $ticket->user?->email ?? '' }}</p>
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
                        @if(in_array($ticket->status, ['open', 'in_progress']))
                            @php
                                $slaHours = match($ticket->priority) {
                                    'critical' => 24,
                                    'high'     => 72,
                                    'medium'   => 168,
                                    'low'      => 336,
                                    default    => 168,
                                };
                                $ageHours    = $ticket->created_at->diffInHours(now());
                                $slaBreached = $ageHours > $slaHours;
                                $slaWarning  = !$slaBreached && ($ageHours / $slaHours) >= 0.75;
                                $overDays    = (int) ceil(($ageHours - $slaHours) / 24);
                            @endphp
                            @if($slaBreached)
                                <flux:badge color="red" size="sm">SLA +{{ $overDays }}d</flux:badge>
                            @elseif($slaWarning)
                                <flux:badge color="yellow" size="sm">{{ __('SLA riesgo') }}</flux:badge>
                            @endif
                        @endif
                    </x-agro.table-cell>

                    <x-agro.table-cell align="right">
                        <div class="flex items-center gap-1 justify-end">
                            <flux:button
                                variant="ghost"
                                size="sm"
                                icon="eye"
                                wire:click.stop="selectTicket({{ $ticket->id }})"
                                tooltip="{{ __('Ver detalles') }}"
                            />
                            <flux:button
                                variant="ghost"
                                size="sm"
                                icon="trash"
                                class="text-red-400 hover:text-red-600"
                                wire:click.stop="deleteTicket({{ $ticket->id }})"
                                wire:confirm="{{ __('¿Eliminar el ticket \':title\'? Esta acción no se puede deshacer.', ['title' => $ticket->title]) }}"
                                tooltip="{{ __('Eliminar ticket') }}"
                            />
                        </div>
                    </x-agro.table-cell>
                </x-agro.table-row>
            @endforeach

            <x-slot name="pagination">
                <x-agro-pagination :paginator="$tickets" />
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
                            <span class="text-sm text-zinc-400">por {{ $selectedTicket->user?->name ?? 'Usuario eliminado' }}</span>
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
                        <p class="text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-2">{{ __('Descripción') }}</p>
                        <p class="text-sm text-zinc-700 whitespace-pre-wrap">{{ $selectedTicket->description }}</p>

                        @if(!empty($selectedTicket->image_urls))
                            <div class="mt-4">
                                <p class="text-xs font-medium text-zinc-500 mb-2">Imágenes adjuntas ({{ count($selectedTicket->image_urls) }})</p>
                                <div class="grid grid-cols-2 gap-2">
                                    @foreach($selectedTicket->image_urls as $url)
                                        <a href="{{ $url }}" target="_blank" class="block">
                                            <img
                                                src="{{ $url }}"
                                                alt="Imagen del ticket"
                                                class="w-full h-auto max-h-48 object-cover rounded-lg border border-zinc-200 hover:opacity-90 transition cursor-pointer"
                                            >
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <p class="text-xs text-zinc-400 mt-3">Creado {{ $selectedTicket->created_at->diffForHumans() }}</p>
                    </div>

                    {{-- Asignación --}}
                    <div class="bg-zinc-50 rounded-lg p-4 space-y-3">
                        <p class="text-xs font-semibold text-zinc-500 uppercase tracking-wide">{{ __('Asignación') }}</p>
                        <div class="flex items-center gap-3">
                            <flux:select wire:model="assignTo">
                                <option value="">{{ __('Sin asignar') }}</option>
                                @foreach(\App\Models\User::where('role', 'admin')->get() as $admin)
                                    <option value="{{ $admin->id }}">{{ $admin->name }}</option>
                                @endforeach
                            </flux:select>
                            <flux:button wire:click="assignTicket" variant="primary" size="sm">{{ __('Asignar') }}</flux:button>
                        </div>
                        @if($selectedTicket->assignedTo)
                            <p class="text-sm text-zinc-600">
                                Asignado a: <strong class="text-zinc-900">{{ $selectedTicket->assignedTo->name }}</strong>
                            </p>
                        @endif
                    </div>

                    {{-- Cambiar Estado --}}
                    <div class="bg-zinc-50 rounded-lg p-4 space-y-3">
                        <p class="text-xs font-semibold text-zinc-500 uppercase tracking-wide">{{ __('Cambiar Estado') }}</p>
                        <div class="flex gap-2 flex-wrap">
                            <flux:button wire:click="changeStatus('open')"        variant="outline" size="sm">{{ __('Abrir') }}</flux:button>
                            <flux:button wire:click="changeStatus('in_progress')" variant="outline" size="sm">{{ __('En Progreso') }}</flux:button>
                            <flux:button wire:click="changeStatus('resolved')"    variant="primary" size="sm">{{ __('Resolver') }}</flux:button>
                            <flux:button wire:click="changeStatus('closed')"      variant="outline" size="sm">{{ __('Cerrar') }}</flux:button>
                        </div>
                    </div>

                    {{-- Comentarios --}}
                    @php
                        $publicComments   = $selectedTicket->comments->where('is_internal', false);
                        $internalComments = $selectedTicket->comments->where('is_internal', true);
                        $totalComments    = $selectedTicket->comments->count();
                    @endphp
                    @if($totalComments > 0)
                        <div>
                            <p class="text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-3">
                                Comentarios ({{ $totalComments }})
                                @if($internalComments->count() > 0)
                                    <span class="ml-1 font-normal text-amber-600">· {{ $internalComments->count() }} nota(s) interna(s)</span>
                                @endif
                            </p>
                            <div class="space-y-2">
                                @foreach($selectedTicket->comments->sortBy('created_at') as $comment)
                                    @if($comment->is_internal)
                                        <div class="bg-amber-50 border border-amber-200 rounded-lg px-4 py-3">
                                            <div class="flex items-center justify-between mb-1">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-sm font-semibold text-zinc-900">{{ $comment->user->name }}</span>
                                                    <flux:badge color="yellow" size="sm">{{ __('Nota interna') }}</flux:badge>
                                                </div>
                                                <span class="text-xs text-zinc-400">{{ $comment->created_at->diffForHumans() }}</span>
                                            </div>
                                            <p class="text-sm text-amber-900">{{ $comment->comment }}</p>
                                        </div>
                                    @else
                                        <div class="bg-zinc-50 border border-zinc-100 rounded-lg px-4 py-3">
                                            <div class="flex items-center justify-between mb-1">
                                                <span class="text-sm font-semibold text-zinc-900">{{ $comment->user->name }}</span>
                                                <span class="text-xs text-zinc-400">{{ $comment->created_at->diffForHumans() }}</span>
                                            </div>
                                            <p class="text-sm text-zinc-700">{{ $comment->comment }}</p>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Añadir Comentario --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-xs font-semibold text-zinc-500 uppercase tracking-wide">{{ __('Añadir Comentario') }}</p>
                            @if($cannedResponses->isNotEmpty())
                                <div x-data="{ open: false }" class="relative">
                                    <button @click="open = !open" class="flex items-center gap-1 text-xs text-zinc-500 hover:text-zinc-700 border border-zinc-200 rounded-md px-2 py-1">
                                        <flux:icon icon="chat-bubble-left-right" class="size-3" />
                                        Respuestas rápidas
                                        <flux:icon icon="chevron-down" class="size-3" />
                                    </button>
                                    <div x-show="open" @click.outside="open = false" x-transition
                                         class="absolute right-0 mt-1 w-72 bg-white rounded-xl shadow-lg border border-zinc-200 z-20 overflow-hidden">
                                        @foreach($cannedResponses->groupBy('category') as $cat => $items)
                                            @if($cat)
                                                <div class="px-3 pt-2 pb-1">
                                                    <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-wider">{{ $cat }}</p>
                                                </div>
                                            @endif
                                            @foreach($items as $r)
                                                <button
                                                    wire:click="selectCannedResponse({{ $r->id }})"
                                                    @click="open = false"
                                                    class="w-full text-left px-3 py-2 hover:bg-zinc-50 text-sm text-zinc-700 transition-colors"
                                                >
                                                    {{ $r->title }}
                                                </button>
                                            @endforeach
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                        <flux:textarea
                            wire:model="newComment"
                            rows="3"
                            :placeholder="$isInternal ? __('Nota interna (solo visible para admins)...') : __('Escribe tu comentario...')"
                            @class(['border-amber-300 bg-amber-50 focus:ring-amber-400' => $isInternal])
                        />
                        @error('newComment')
                            <flux:error>{{ $message }}</flux:error>
                        @enderror
                        <div class="mt-2 flex items-center gap-3">
                            <flux:button wire:click="addComment" variant="primary" size="sm"
                                icon="{{ $isInternal ? 'lock-closed' : 'chat-bubble-left' }}"
                            >
                                {{ $isInternal ? __('Añadir Nota Interna') : __('Añadir Comentario') }}
                            </flux:button>
                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <input
                                    type="checkbox"
                                    wire:model.live="isInternal"
                                    class="rounded border-zinc-300 text-amber-500 focus:ring-amber-400"
                                />
                                <span class="text-xs text-zinc-600">Nota interna <span class="text-zinc-400">{{ __('(solo admins)') }}</span></span>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="px-6 py-3 border-t border-zinc-200 bg-zinc-50 flex justify-end">
                    <flux:button wire:click="closeTicketDetail" variant="outline">{{ __('Cerrar') }}</flux:button>
                </div>
            </div>
        </div>
    @endif
</div>

