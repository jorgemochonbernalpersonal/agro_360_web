<div wire:poll.30s="loadNotifications" x-data="{ open: @entangle('showDropdown') }" @click.away="open = false" class="relative">
    {{-- Notification Bell Button --}}
    <button
        @click="open = !open"
        class="relative p-2 rounded-lg text-zinc-600 hover:bg-agro-50 transition-all duration-200"
        aria-label="Notificaciones"
    >
        <flux:icon icon="bell" class="size-6" />

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
        class="absolute right-0 mt-2 w-96 bg-white rounded-xl shadow-2xl border border-zinc-200 z-50 max-h-[80vh] overflow-hidden flex flex-col"
        style="display: none;"
    >
        {{-- Header --}}
        <div class="px-4 py-3 border-b border-zinc-200 flex items-center justify-between bg-zinc-50">
            <div class="flex items-center gap-2">
                <h3 class="text-sm font-semibold text-zinc-900">Notificaciones</h3>
                @if($unreadCount > 0)
                    <span class="px-2 py-0.5 bg-red-500 text-white text-xs font-bold rounded-full">
                        {{ $unreadCount }}
                    </span>
                @endif
            </div>

            @if($unreadCount > 0)
                <button
                    wire:click="markAllAsRead"
                    class="text-xs text-agro-600 hover:text-agro-700 font-medium"
                >
                    Marcar todo como leído
                </button>
            @endif
        </div>

        {{-- Notifications List --}}
        <div class="overflow-y-auto flex-1">
            {{-- Dashboard Alerts Section --}}
            @if(count($dashboardAlerts) > 0)
                <div class="px-3 py-2 bg-amber-50 border-b border-amber-200">
                    <span class="text-xs font-semibold text-amber-700">Alertas del sistema</span>
                </div>
                @foreach($dashboardAlerts as $alert)
                    <div class="px-4 py-3 border-b border-zinc-100 bg-{{ $alert['type'] === 'danger' ? 'red' : ($alert['type'] === 'warning' ? 'amber' : 'blue') }}-50/50">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-{{ $alert['type'] === 'danger' ? 'red' : ($alert['type'] === 'warning' ? 'amber' : 'blue') }}-100 flex items-center justify-center text-xl">
                                {{ $alert['icon'] }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-zinc-900">{{ $alert['title'] }}</p>
                                <p class="text-xs text-zinc-600 mt-0.5">{{ $alert['message'] }}</p>
                                @if(isset($alert['action_url']))
                                    <a href="{{ $alert['action_url'] }}" wire:navigate @click="open = false"
                                       class="inline-block mt-2 text-xs font-medium text-agro-600 hover:text-agro-700">
                                        {{ $alert['action_text'] ?? 'Ver m��s' }} ��
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif

            {{-- Database Notifications --}}
            @if(count($notifications) > 0 && count($dashboardAlerts) > 0)
                <div class="px-3 py-2 bg-zinc-50 border-b border-zinc-200">
                    <span class="text-xs font-semibold text-zinc-500">Notificaciones</span>
                </div>
            @endif
            @forelse($notifications as $notification)
                @php
                    $data = $notification->data;
                    $icon = $data['icon'] ?? $data['report_icon'] ?? $this->guessIcon($data);
                    $iconColor = $data['color'] ?? $this->guessIconColor($data);
                @endphp
                <div
                    wire:key="notification-{{ $notification->id }}"
                    class="px-4 py-3 border-b border-zinc-100 hover:bg-zinc-50 transition-colors duration-150 {{ is_null($notification->read_at) ? 'bg-blue-50/30' : '' }}"
                >
                    <div class="flex items-start gap-3">
                        {{-- Icon --}}
                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-agro-50 flex items-center justify-center text-xl {{ $iconColor }}">
                            {{ $icon }}
                        </div>

                        {{-- Content --}}
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-zinc-900">
                                {{ $data['message'] ?? $data['title'] ?? 'Nueva notificación' }}
                            </p>

                            @if(isset($data['report_type_name']) || isset($data['period']) || isset($data['export_type_name']))
                                <p class="text-xs text-zinc-600 mt-0.5">
                                    {{ $data['report_type_name'] ?? $data['export_type_name'] ?? '' }}
                                    @if(isset($data['period']))
                                        <span class="text-zinc-400">{{ $data['period'] }}</span>
                                    @endif
                                    @if(isset($data['format']))
                                        <span class="text-zinc-400">{{ strtoupper($data['format']) }}</span>
                                    @endif
                                </p>
                            @endif

                            @if(isset($data['error_message']))
                                <p class="text-xs text-red-600 mt-1 line-clamp-2">
                                    {{ $data['error_message'] }}
                                </p>
                            @endif

                            {{-- Multi-action buttons --}}
                            <div class="flex items-center gap-3 mt-2">
                                @if(isset($data['action_url']))
                                    <a
                                        href="{{ $data['action_url'] }}"
                                        wire:navigate
                                        wire:click="markAsRead('{{ $notification->id }}')"
                                        @click="open = false"
                                        class="text-xs font-medium text-agro-600 hover:text-agro-700"
                                    >
                                        {{ $data['action_text'] ?? 'Ver' }}
                                    </a>
                                @endif

                                @if(isset($data['download_url']))
                                    <a
                                        href="{{ $data['download_url'] }}"
                                        target="_blank"
                                        class="inline-flex items-center gap-1 text-xs font-medium text-zinc-600 hover:text-zinc-900"
                                    >
                                        <flux:icon icon="arrow-down-tray" class="size-3" />
                                        Descargar
                                    </a>
                                @endif

                                @if(isset($data['secondary_action_url']))
                                    <a
                                        href="{{ $data['secondary_action_url'] }}"
                                        wire:navigate
                                        @click="open = false"
                                        class="text-xs font-medium text-zinc-500 hover:text-zinc-700"
                                    >
                                        {{ $data['secondary_action_text'] ?? 'Más' }}
                                    </a>
                                @endif

                                <span class="text-xs text-zinc-400 ml-auto">
                                    {{ $notification->created_at->diffForHumans() }}
                                </span>
                            </div>
                        </div>

                        {{-- Mark Read / Delete --}}
                        <div class="flex-shrink-0 flex flex-col gap-1">
                            @if(is_null($notification->read_at))
                                <button
                                    wire:click="markAsRead('{{ $notification->id }}')"
                                    class="p-1 text-zinc-400 hover:text-agro-600 transition-colors"
                                    title="Marcar como leída"
                                >
                                    <flux:icon icon="check" class="size-4" />
                                </button>
                            @endif

                            <button
                                wire:click="deleteNotification('{{ $notification->id }}')"
                                class="p-1 text-zinc-400 hover:text-red-600 transition-colors"
                                title="Eliminar"
                            >
                                <flux:icon icon="x-mark" class="size-4" />
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-4 py-8 text-center">
                    <flux:icon icon="bell" class="size-12 mx-auto text-zinc-300 mb-2" />
                    <p class="text-sm text-zinc-500">No tienes notificaciones</p>
                </div>
            @endforelse
        </div>

        {{-- Footer --}}
        @if($notifications->count() > 0)
            <div class="px-4 py-2 border-t border-zinc-200 bg-zinc-50">
                <a
                    href="{{ roleRoute('official-reports.index') }}"
                    wire:navigate
                    @click="open = false"
                    class="block text-center text-sm font-medium text-agro-600 hover:text-agro-700"
                >
                    Ver todos los informes
                </a>
            </div>
        @endif
    </div>
</div>
