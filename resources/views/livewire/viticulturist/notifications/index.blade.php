<div class="space-y-6 animate-fade-in">

<x-agro.page-header
    title="Notificaciones"
    description="Centro de notificaciones del sistema"
    icon="bell"
>
    <x-slot:actions>
        <div class="flex items-center gap-2">
            @if($unreadCount > 0)
                <flux:button wire:click="markAllRead" variant="ghost" size="sm" icon="check">
                    Marcar todo leído
                </flux:button>
            @endif
            @if($notifications->total() > 0)
                <flux:button wire:click="clearAll" wire:confirm="¿Eliminar todo el historial de notificaciones?" variant="ghost" size="sm" icon="trash">
                    Limpiar historial
                </flux:button>
            @endif
        </div>
    </x-slot:actions>
</x-agro.page-header>

{{-- Badge no leídas --}}
@if($unreadCount > 0)
    <flux:callout variant="info" icon="bell">
        Tienes <strong>{{ $unreadCount }}</strong> {{ $unreadCount === 1 ? 'notificación sin leer' : 'notificaciones sin leer' }}.
    </flux:callout>
@endif

{{-- Filtros --}}
<x-agro.filter-bar>
    <div class="flex gap-2">
        <flux:button
            wire:click="$set('filter', 'all')"
            :variant="$filter === 'all' ? 'primary' : 'ghost'"
            size="sm"
        >
            Todas
        </flux:button>
        <flux:button
            wire:click="$set('filter', 'unread')"
            :variant="$filter === 'unread' ? 'primary' : 'ghost'"
            size="sm"
        >
            Sin leer @if($unreadCount > 0)<span class="ml-1 inline-flex items-center justify-center w-5 h-5 text-xs font-bold bg-agro-500 text-white rounded-full">{{ $unreadCount }}</span>@endif
        </flux:button>
    </div>
</x-agro.filter-bar>

{{-- Lista de notificaciones --}}
@if($notifications->count() > 0)
    <div class="space-y-2">
        @foreach($notifications as $notification)
            @php
                $data     = $notification->data;
                $isRead   = !is_null($notification->read_at);
                $color    = $data['color'] ?? 'zinc';
                $icon     = $data['icon'] ?? 'bell';
                $bgClass  = match($color) {
                    'green'  => 'bg-green-50',
                    'red'    => 'bg-red-50',
                    'orange' => 'bg-orange-50',
                    'blue'   => 'bg-blue-50',
                    'amber'  => 'bg-amber-50',
                    default  => 'bg-zinc-50',
                };
                $iconClass = match($color) {
                    'green'  => 'text-green-600',
                    'red'    => 'text-red-600',
                    'orange' => 'text-orange-500',
                    'blue'   => 'text-blue-600',
                    'amber'  => 'text-amber-500',
                    default  => 'text-zinc-500',
                };
            @endphp
            <div
                wire:key="notif-{{ $notification->id }}"
                class="flex gap-4 p-4 rounded-xl border {{ $isRead ? 'border-zinc-200 bg-white' : 'border-agro-200 bg-agro-50' }} transition-colors"
            >
                <div class="shrink-0 w-10 h-10 rounded-xl {{ $bgClass }} flex items-center justify-center">
                    <flux:icon :icon="$icon" class="size-5 {{ $iconClass }}" />
                </div>

                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-2">
                        <p class="text-sm font-{{ $isRead ? 'normal' : 'semibold' }} text-zinc-900">
                            {{ $data['title'] ?? 'Notificación' }}
                            @if(!$isRead)
                                <span class="inline-block w-2 h-2 rounded-full bg-agro-500 ml-1 align-middle"></span>
                            @endif
                        </p>
                        <span class="text-xs text-zinc-400 shrink-0 whitespace-nowrap">
                            {{ $notification->created_at->diffForHumans() }}
                        </span>
                    </div>
                    @if(!empty($data['body']))
                        <p class="text-sm text-zinc-600 mt-0.5">{{ $data['body'] }}</p>
                    @endif
                    <div class="flex items-center gap-3 mt-2">
                        @if(!empty($data['link']))
                            <a href="{{ $data['link'] }}" wire:navigate
                               class="text-xs font-medium text-agro-600 hover:text-agro-700">
                                Ver detalle →
                            </a>
                        @endif
                        @if(!$isRead)
                            <button wire:click="markRead('{{ $notification->id }}')"
                                    class="text-xs text-zinc-400 hover:text-zinc-600">
                                Marcar como leído
                            </button>
                        @endif
                        <button wire:click="delete('{{ $notification->id }}')"
                                wire:confirm="¿Eliminar esta notificación?"
                                class="text-xs text-red-400 hover:text-red-600">
                            Eliminar
                        </button>
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
        description="Aquí aparecerán los avisos del sistema: alertas de plagas, vencimientos, comunicaciones de bodega y más."
    />
@endif

</div>
