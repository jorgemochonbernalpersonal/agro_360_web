<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="{{ __('Solicitudes de vinculación a bodega') }}"
        :description="__('Solicita unirte a una bodega para gestionar tus vendimias conjuntamente')"
    >
        <x-slot:actions>
            <flux:button wire:click="openSearch" variant="primary" icon="plus">
                {{ __('Solicitar vinculación') }}
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Modal búsqueda de bodegas --}}
    <flux:modal wire:model="showSearch" class="w-full max-w-lg">
        <div class="p-6">
            <div class="flex items-center gap-3 mb-5">
                <div class="p-2 rounded-lg bg-agro-50">
                    <flux:icon icon="building-office-2" class="size-5 text-agro-600" />
                </div>
                <div>
                    <h3 class="text-base font-semibold text-zinc-900">{{ __('Buscar bodega') }}</h3>
                    <p class="text-xs text-zinc-500">{{ __('Escribe el nombre de la bodega a la que quieres solicitar vinculación') }}</p>
                </div>
            </div>

            <div class="relative mb-3">
                <flux:icon icon="magnifying-glass" class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-zinc-400 pointer-events-none" />
                <input
                    wire:model.live.debounce.300ms="searchQuery"
                    type="text"
                    placeholder="{{ __('Nombre de la bodega...') }}"
                    class="w-full pl-9 pr-3 py-2 text-sm border border-zinc-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-agro-300"
                    autofocus
                />
            </div>

            {{-- Resultados --}}
            @if(strlen(trim($searchQuery)) >= 2)
                <div class="space-y-1.5 max-h-56 overflow-y-auto mb-4">
                    @forelse($wineries as $winery)
                        <div class="flex items-center justify-between px-3 py-2.5 rounded-lg border border-zinc-100 hover:border-agro-200 hover:bg-agro-50 transition">
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-zinc-800 truncate">{{ $winery['name'] }}</p>
                                <p class="text-xs text-zinc-400 truncate">{{ $winery['email'] }}</p>
                            </div>
                            <flux:button
                                wire:click="sendRequest({{ $winery['id'] }})"
                                wire:loading.attr="disabled"
                                size="xs"
                                variant="primary"
                                icon="paper-airplane"
                            >
                                {{ __('Solicitar') }}
                            </flux:button>
                        </div>
                    @empty
                        <div class="py-6 text-center text-sm text-zinc-400">
                            {{ __('No se encontraron bodegas para "') }}{{ $searchQuery }}"
                        </div>
                    @endforelse
                </div>
            @elseif(strlen(trim($searchQuery)) > 0)
                <p class="text-xs text-zinc-400 mb-4 text-center">{{ __('Escribe al menos 2 caracteres para buscar') }}</p>
            @endif

            {{-- Mensaje opcional --}}
            <flux:field>
                <flux:label>{{ __('Mensaje para la bodega') }} <span class="text-zinc-400">({{ __('opcional') }})</span></flux:label>
                <flux:textarea wire:model="message" rows="2" maxlength="500"
                    placeholder="{{ __('Preséntate brevemente o indica el motivo de tu solicitud...') }}" />
            </flux:field>

            <div class="mt-5 pt-4 border-t border-zinc-100 flex justify-end">
                <flux:button variant="ghost" wire:click="closeSearch">{{ __('Cerrar') }}</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Solicitudes pendientes --}}
    @if($pending->isNotEmpty())
        <x-agro.card>
            <x-slot name="header">
                <div class="flex items-center gap-2">
                    <flux:icon icon="clock" class="size-4 text-amber-400" />
                    <span class="font-medium text-zinc-700">{{ __('Pendientes de respuesta') }}</span>
                    <flux:badge color="amber" size="sm">{{ $pending->count() }}</flux:badge>
                </div>
            </x-slot>

            @foreach($pending as $req)
                <div class="px-4 py-3 border-b border-zinc-100 last:border-0 flex items-center justify-between gap-4"
                     wire:key="pending-{{ $req->id }}">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center shrink-0">
                            <span class="text-xs font-bold text-amber-600">
                                {{ strtoupper(substr($req->winery?->name ?? '?', 0, 1)) }}
                            </span>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-zinc-800 truncate">{{ $req->winery?->name ?? '—' }}</p>
                            <p class="text-xs text-zinc-400">
                                {{ __('Solicitado') }} {{ $req->requested_at?->diffForHumans() ?? '—' }}
                            </p>
                            @if($req->message)
                                <p class="text-xs text-zinc-500 italic mt-0.5 truncate">{{ $req->message }}</p>
                            @endif
                        </div>
                    </div>
                    <flux:button
                        wire:click="cancelRequest({{ $req->id }})"
                        wire:confirm="{{ __('¿Cancelar la solicitud a :name?', ['name' => $req->winery?->name]) }}"
                        variant="ghost" size="sm" icon="x-mark"
                        class="text-zinc-400 hover:text-red-500 shrink-0"
                    >
                        {{ __('Cancelar') }}
                    </flux:button>
                </div>
            @endforeach
        </x-agro.card>
    @endif

    {{-- Solicitudes aprobadas --}}
    @if($approved->isNotEmpty())
        <x-agro.card>
            <x-slot name="header">
                <div class="flex items-center gap-2">
                    <flux:icon icon="check-circle" class="size-4 text-emerald-500" />
                    <span class="font-medium text-zinc-700">{{ __('Aprobadas') }}</span>
                </div>
            </x-slot>

            @foreach($approved as $req)
                <div class="px-4 py-3 border-b border-zinc-100 last:border-0 flex items-center justify-between gap-4"
                     wire:key="approved-{{ $req->id }}">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center shrink-0">
                            <span class="text-xs font-bold text-emerald-600">
                                {{ strtoupper(substr($req->winery?->name ?? '?', 0, 1)) }}
                            </span>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-zinc-800 truncate">{{ $req->winery?->name ?? '—' }}</p>
                            <p class="text-xs text-zinc-400">
                                {{ __('Aprobada') }} {{ $req->responded_at?->diffForHumans() ?? '—' }}
                            </p>
                        </div>
                    </div>
                    <flux:badge color="green" icon="check-circle" size="sm">{{ __('Vinculado') }}</flux:badge>
                </div>
            @endforeach
        </x-agro.card>
    @endif

    {{-- Solicitudes rechazadas --}}
    @if($rejected->isNotEmpty())
        <x-agro.card>
            <x-slot name="header">
                <div class="flex items-center gap-2">
                    <flux:icon icon="x-circle" class="size-4 text-red-400" />
                    <span class="font-medium text-zinc-700">{{ __('Rechazadas') }}</span>
                </div>
            </x-slot>

            @foreach($rejected as $req)
                <div class="px-4 py-3 border-b border-zinc-100 last:border-0 flex items-center gap-3 min-w-0"
                     wire:key="rejected-{{ $req->id }}">
                    <div class="w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center shrink-0">
                        <span class="text-xs font-bold text-red-400">
                            {{ strtoupper(substr($req->winery?->name ?? '?', 0, 1)) }}
                        </span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-zinc-700 truncate">{{ $req->winery?->name ?? '—' }}</p>
                        <p class="text-xs text-zinc-400">{{ __('Rechazada') }} {{ $req->responded_at?->diffForHumans() ?? '—' }}</p>
                    </div>
                    <flux:badge color="red" size="sm">{{ __('Rechazada') }}</flux:badge>
                </div>
            @endforeach
        </x-agro.card>
    @endif

    {{-- Estado vacío --}}
    @if($pending->isEmpty() && $approved->isEmpty() && $rejected->isEmpty())
        <x-agro.empty-state
            icon="building-office-2"
            :message="__('Sin solicitudes de vinculación')"
            :description="__('Busca una bodega y envía tu solicitud para empezar a colaborar')"
        >
            <x-slot:action>
                <flux:button wire:click="openSearch" variant="primary" icon="plus">
                    {{ __('Solicitar vinculación') }}
                </flux:button>
            </x-slot:action>
        </x-agro.empty-state>
    @endif

</div>
