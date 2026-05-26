<div
    x-data="{}"
    @keydown.window.ctrl.k.prevent="$wire.openModal()"
    @keydown.window.meta.k.prevent="$wire.openModal()"
>
    {{-- Trigger button (visible en la topbar) --}}
    <button
        wire:click="openModal"
        class="hidden lg:flex items-center gap-2 px-3 py-1.5 text-xs text-zinc-400 bg-zinc-100 hover:bg-zinc-200 rounded-lg transition-colors border border-zinc-200"
    >
        <flux:icon icon="magnifying-glass" class="size-3.5" />
        <span>{{ __('Buscar...') }}</span>
        <span class="ml-1 font-mono text-[10px] bg-white border border-zinc-200 rounded px-1 py-0.5">⌘K</span>
    </button>

    {{-- Modal --}}
    @if($open)
    <div
        class="fixed inset-0 z-50 flex items-start justify-center pt-[15vh] px-4"
        wire:click.self="closeModal"
        @keydown.window.escape="$wire.closeModal()"
    >
        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" wire:click="closeModal"></div>

        {{-- Panel --}}
        <div class="relative w-full max-w-xl bg-white rounded-2xl shadow-2xl border border-zinc-200 overflow-hidden">

            {{-- Input --}}
            <div class="flex items-center gap-3 px-4 py-3.5 border-b border-zinc-100">
                <flux:icon icon="magnifying-glass" class="size-5 text-zinc-400 flex-shrink-0" />
                <input
                    type="text"
                    wire:model.live.debounce.200ms="query"
                    placeholder="{{ __('Buscar usuarios, tickets, parcelas...') }}"
                    class="flex-1 text-sm text-zinc-900 placeholder-zinc-400 bg-transparent outline-none"
                    x-init="$el.focus()"
                    @keydown.escape.prevent="$wire.closeModal()"
                />
                @if($query)
                    <button wire:click="$set('query', '')" class="text-zinc-400 hover:text-zinc-600">
                        <flux:icon icon="x-mark" class="size-4" />
                    </button>
                @endif
            </div>

            {{-- Resultados --}}
            <div class="max-h-[400px] overflow-y-auto">
                @if(strlen(trim($query)) >= 2 && count($results) === 0)
                    <div class="py-12 text-center">
                        <flux:icon icon="magnifying-glass" class="size-8 text-zinc-300 mx-auto mb-2" />
                        <p class="text-sm text-zinc-400">Sin resultados para "{{ $query }}"</p>
                    </div>
                @elseif(count($results) > 0)
                    @php
                        $grouped = collect($results)->groupBy('type');
                        $typeLabels = ['usuario' => 'Usuarios', 'ticket' => 'Tickets de soporte', 'parcela' => 'Parcelas'];
                        $colorMap = [
                            'purple' => 'bg-purple-50 text-purple-600',
                            'red'    => 'bg-red-50 text-red-600',
                            'agro'   => 'bg-agro-50 text-agro-600',
                        ];
                    @endphp

                    @foreach($grouped as $type => $items)
                        <div class="px-4 pt-3 pb-1">
                            <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest">
                                {{ $typeLabels[$type] ?? $type }}
                            </p>
                        </div>
                        @foreach($items as $item)
                            <a
                                href="{{ $item['url'] }}"
                                wire:click="closeModal"
                                class="flex items-center gap-3 px-4 py-2.5 hover:bg-zinc-50 transition-colors group"
                            >
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 {{ $colorMap[$item['color']] ?? 'bg-zinc-100 text-zinc-500' }}">
                                    <flux:icon :icon="$item['icon']" class="size-4" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-zinc-900 group-hover:text-agro-600 truncate transition-colors">
                                        {{ $item['title'] }}
                                    </p>
                                    <p class="text-xs text-zinc-400 truncate">{{ $item['subtitle'] }}</p>
                                </div>
                                <flux:icon icon="chevron-right" class="size-4 text-zinc-300 group-hover:text-zinc-400 flex-shrink-0" />
                            </a>
                        @endforeach
                    @endforeach
                @else
                    {{-- Estado inicial --}}
                    <div class="py-8 px-4">
                        <p class="text-xs text-zinc-400 mb-4 text-center">{{ __('Accesos rápidos') }}</p>
                        <div class="grid grid-cols-2 gap-2">
                            <a href="{{ route('admin.users.index') }}" wire:click="closeModal"
                               class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-zinc-50 transition-colors text-sm text-zinc-700 group">
                                <flux:icon icon="users" class="size-4 text-purple-400" />
                                Usuarios
                            </a>
                            <a href="{{ route('admin.support.index') }}" wire:click="closeModal"
                               class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-zinc-50 transition-colors text-sm text-zinc-700">
                                <flux:icon icon="chat-bubble-left-ellipsis" class="size-4 text-red-400" />
                                Soporte
                            </a>
                            <a href="{{ route('admin.plots.index') }}" wire:click="closeModal"
                               class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-zinc-50 transition-colors text-sm text-zinc-700">
                                <flux:icon icon="map" class="size-4 text-agro-400" />
                                Parcelas
                            </a>
                            <a href="{{ route('admin.subscriptions.index') }}" wire:click="closeModal"
                               class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-zinc-50 transition-colors text-sm text-zinc-700">
                                <flux:icon icon="credit-card" class="size-4 text-green-400" />
                                Suscripciones
                            </a>
                            <a href="{{ route('admin.health.index') }}" wire:click="closeModal"
                               class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-zinc-50 transition-colors text-sm text-zinc-700">
                                <flux:icon icon="heart" class="size-4 text-agro-400" />
                                Salud sistema
                            </a>
                            <a href="{{ route('admin.settings.index') }}" wire:click="closeModal"
                               class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-zinc-50 transition-colors text-sm text-zinc-700">
                                <flux:icon icon="cog-6-tooth" class="size-4 text-zinc-400" />
                                Configuración
                            </a>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Footer --}}
            <div class="px-4 py-2 border-t border-zinc-100 bg-zinc-50 flex items-center gap-4 text-[10px] text-zinc-400">
                <span><kbd class="font-mono bg-white border border-zinc-200 rounded px-1">↵</kbd> abrir</span>
                <span><kbd class="font-mono bg-white border border-zinc-200 rounded px-1">ESC</kbd> cerrar</span>
                <span class="ml-auto">{{ __('⌘K para abrir en cualquier momento') }}</span>
            </div>
        </div>
    </div>
    @endif
</div>
