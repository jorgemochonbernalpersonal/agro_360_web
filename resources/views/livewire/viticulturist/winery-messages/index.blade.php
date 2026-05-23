<div class="space-y-6 animate-fade-in">
    <x-agro.page-header title="Comunicación con Bodega" description="Notificaciones sobre tus entregas y liquidaciones">
        @if ($unreadCount > 0)
            <x-slot:actions>
                <flux:button wire:click="markAllRead" variant="ghost" size="sm" icon="check">
                    Marcar todo como leído
                </flux:button>
            </x-slot:actions>
        @endif
    </x-agro.page-header>

    @if ($unreadCount > 0)
        <flux:callout variant="info" icon="bell">
            Tienes {{ $unreadCount }} {{ $unreadCount === 1 ? 'notificación sin leer' : 'notificaciones sin leer' }}.
        </flux:callout>
    @endif

    @if ($notifications->count() > 0)
        <div class="space-y-3">
            @foreach ($notifications as $notification)
                @php
                    $data    = $notification->data;
                    $isRead  = !is_null($notification->read_at);
                    $color   = $data['color'] ?? 'zinc';
                    $icon    = $data['icon'] ?? 'bell';
                    $bgClass = match($color) {
                        'green'  => 'bg-green-50 dark:bg-green-900/20',
                        'orange' => 'bg-orange-50 dark:bg-orange-900/20',
                        'blue'   => 'bg-blue-50 dark:bg-blue-900/20',
                        default  => 'bg-zinc-50 dark:bg-zinc-800',
                    };
                    $iconClass = match($color) {
                        'green'  => 'text-green-600',
                        'orange' => 'text-orange-500',
                        'blue'   => 'text-blue-600',
                        default  => 'text-zinc-500',
                    };
                @endphp
                <div
                    wire:key="notif-{{ $notification->id }}"
                    class="flex gap-4 p-4 rounded-xl border {{ $isRead ? 'border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900' : 'border-agro-300 dark:border-agro-700 bg-agro-50 dark:bg-agro-900/20' }} transition-colors"
                >
                    {{-- Icon --}}
                    <div class="shrink-0 w-10 h-10 rounded-xl {{ $bgClass }} flex items-center justify-center">
                        <flux:icon :icon="$icon" class="size-5 {{ $iconClass }}" />
                    </div>

                    {{-- Content --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2">
                            <p class="font-semibold text-zinc-900 dark:text-zinc-100 text-sm {{ $isRead ? 'font-normal' : '' }}">
                                {{ $data['title'] ?? '—' }}
                                @if (!$isRead)
                                    <span class="inline-block w-2 h-2 rounded-full bg-agro-500 ml-1 align-middle"></span>
                                @endif
                            </p>
                            <span class="text-xs text-zinc-400 shrink-0 whitespace-nowrap">
                                {{ $notification->created_at->diffForHumans() }}
                            </span>
                        </div>
                        <p class="text-sm text-zinc-600 dark:text-zinc-300 mt-0.5">
                            {{ $data['body'] ?? '' }}
                        </p>
                        <div class="flex items-center gap-3 mt-2">
                            @if (!empty($data['link']))
                                <a href="{{ $data['link'] }}" wire:navigate
                                   class="text-xs font-medium text-agro-600 hover:text-agro-700 dark:text-agro-400">
                                    Ver detalle →
                                </a>
                            @endif
                            @if (!$isRead)
                                <button
                                    wire:click="markRead('{{ $notification->id }}')"
                                    class="text-xs text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200"
                                >
                                    Marcar como leído
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <x-agro-pagination :paginator="$notifications" />
    @else
        <x-agro.empty-state
            icon="bell"
            title="Sin notificaciones"
            description="Aquí aparecerán los mensajes de tu bodega: confirmaciones de entrega, liquidaciones y resoluciones de disputas"
        />
    @endif
</div>
