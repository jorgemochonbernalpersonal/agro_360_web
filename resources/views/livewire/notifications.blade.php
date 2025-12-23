<div x-data="{ open: @entangle('showDropdown') }" @click.away="open = false" class="relative">
    {{-- Notification Bell Button --}}
    <button 
        @click="open = !open" 
        class="relative p-2 rounded-lg text-gray-600 hover:bg-[var(--color-agro-green-bg)] transition-all duration-200"
        aria-label="Notificaciones"
    >
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        
        {{-- Unread Badge --}}
        @if($unreadCount > 0)
            <span class="absolute top-0 right-0 flex items-center justify-center min-w-[18px] h-[18px] px-1 bg-red-500 text-white text-xs font-bold rounded-full border-2 border-white">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
        @endif
    </button>

    {{-- Dropdown --}}
    <div 
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 transform scale-95"
        x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95"
        class="absolute right-0 mt-2 w-96 bg-white rounded-xl shadow-2xl border border-gray-200 z-50 max-h-[80vh] overflow-hidden flex flex-col"
        style="display: none;"
    >
        {{-- Header --}}
        <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between bg-gray-50">
            <div class="flex items-center gap-2">
                <h3 class="text-sm font-semibold text-gray-900">Notificaciones</h3>
                @if($unreadCount > 0)
                    <span class="px-2 py-0.5 bg-red-500 text-white text-xs font-bold rounded-full">
                        {{ $unreadCount }}
                    </span>
                @endif
            </div>
            
            @if($unreadCount > 0)
                <button 
                    wire:click="markAllAsRead" 
                    class="text-xs text-[var(--color-agro-green)] hover:text-[var(--color-agro-green-dark)] font-medium"
                >
                    Marcar todo como leído
                </button>
            @endif
        </div>

        {{-- Notifications List --}}
        <div class="overflow-y-auto flex-1">
            @forelse($notifications as $notification)
                <div 
                    wire:key="notification-{{ $notification->id }}"
                    class="px-4 py-3 border-b border-gray-100 hover:bg-gray-50 transition-colors duration-150 {{ is_null($notification->read_at) ? 'bg-blue-50/30' : '' }}"
                >
                    <div class="flex items-start gap-3">
                        {{-- Icon --}}
                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-[var(--color-agro-green-bg)] flex items-center justify-center text-xl">
                            {{ $notification->data['report_icon'] ?? '📄' }}
                        </div>

                        {{-- Content --}}
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900">
                                {{ $notification->data['message'] ?? 'Nueva notificación' }}
                            </p>
                            
                            <p class="text-xs text-gray-600 mt-0.5">
                                {{ $notification->data['report_type_name'] ?? '' }}
                                @if(isset($notification->data['period']))
                                    <span class="text-gray-400">• {{ $notification->data['period'] }}</span>
                                @endif
                            </p>

                            @if(isset($notification->data['error_message']))
                                <p class="text-xs text-red-600 mt-1">
                                    {{ Str::limit($notification->data['error_message'], 100) }}
                                </p>
                            @endif

                            {{-- Actions --}}
                            <div class="flex items-center gap-2 mt-2">
                                @if(isset($notification->data['action_url']))
                                    <a 
                                        href="{{ $notification->data['action_url'] }}" 
                                        wire:navigate
                                        wire:click="markAsRead('{{ $notification->id }}')"
                                        @click="open = false"
                                        class="text-xs font-medium text-[var(--color-agro-green)] hover:text-[var(--color-agro-green-dark)]"
                                    >
                                        {{ $notification->data['action_text'] ?? 'Ver más' }}
                                    </a>
                                @endif

                                @if(isset($notification->data['download_url']))
                                    <a 
                                        href="{{ $notification->data['download_url'] }}" 
                                        target="_blank"
                                        class="text-xs font-medium text-gray-600 hover:text-gray-900"
                                    >
                                        Descargar
                                    </a>
                                @endif

                                <span class="text-xs text-gray-400 ml-auto">
                                    {{ $notification->created_at->diffForHumans() }}
                                </span>
                            </div>
                        </div>

                        {{-- Mark Read / Delete --}}
                        <div class="flex-shrink-0 flex flex-col gap-1">
                            @if(is_null($notification->read_at))
                                <button 
                                    wire:click="markAsRead('{{ $notification->id }}')"
                                    class="p-1 text-gray-400 hover:text-[var(--color-agro-green)] transition-colors"
                                    title="Marcar como leída"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </button>
                            @endif

                            <button 
                                wire:click="deleteNotification('{{ $notification->id }}')"
                                class="p-1 text-gray-400 hover:text-red-600 transition-colors"
                                title="Eliminar"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-4 py-12 text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <p class="mt-2 text-sm text-gray-500">No tienes notificaciones</p>
                </div>
            @endforelse
        </div>

        {{-- Footer con Ver todas --}}
        @if($notifications->count() > 0)
            <div class="px-4 py-2 border-t border-gray-200 bg-gray-50">
                <a 
                    href="{{ route('viticulturist.official-reports.index') }}" 
                    wire:navigate
                    @click="open = false"
                    class="block text-center text-sm font-medium text-[var(--color-agro-green)] hover:text-[var(--color-agro-green-dark)]"
                >
                    Ver todos los informes
                </a>
            </div>
        @endif
    </div>
</div>
