<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Salas de Bodega"
        description="Gestiona las zonas y salas donde se ubican los depósitos"
    />

    <div class="flex items-center gap-3">
        <div class="flex-1 relative">
            <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                <flux:icon icon="magnifying-glass" class="size-4 text-zinc-400" />
            </div>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar por nombre..."
                class="w-full pl-9 pr-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm placeholder:text-zinc-400 shadow-sm focus:outline-none focus:ring-2 focus:ring-agro-500 focus:border-transparent transition" />
        </div>

        <flux:button href="{{ route('winery.container-rooms.create') }}" wire:navigate variant="primary" icon="plus">
            Nueva sala
        </flux:button>
    </div>

    @if($rooms->isEmpty())
        <x-agro.empty-state
            icon="building-office"
            title="Sin salas registradas"
            description="Crea las zonas de tu bodega para organizar los depósitos."
        >
            <x-slot:action>
                <flux:button href="{{ route('winery.container-rooms.create') }}" wire:navigate variant="primary" icon="plus">
                    Nueva sala
                </flux:button>
            </x-slot:action>
        </x-agro.empty-state>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($rooms as $room)
                <x-agro.card class="flex flex-col hover:-translate-y-1 animate-fade-in-up">
                    <x-slot:header>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-teal-100 flex items-center justify-center shrink-0">
                                <flux:icon icon="building-office" class="size-5 text-teal-600" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-bold text-zinc-900 truncate">{{ $room->name }}</h3>
                                @if($room->description)
                                    <p class="text-xs text-zinc-500 truncate">{{ $room->description }}</p>
                                @endif
                            </div>
                        </div>
                    </x-slot:header>

                    <div class="flex-1 space-y-2 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-zinc-400">Depósitos</span>
                            <span class="font-medium text-zinc-700">{{ $room->containers_count }}</span>
                        </div>
                        @if($room->capacity)
                            <div class="flex items-center justify-between">
                                <span class="text-zinc-400">Capacidad</span>
                                <span class="font-medium text-zinc-700">{{ number_format($room->capacity) }} uds.</span>
                            </div>
                        @endif
                        @if($room->temperature !== null)
                            <div class="flex items-center justify-between">
                                <span class="text-zinc-400">Temperatura</span>
                                <span class="font-medium text-zinc-700">{{ $room->temperature }}°C</span>
                            </div>
                        @endif
                        @if($room->humidity !== null)
                            <div class="flex items-center justify-between">
                                <span class="text-zinc-400">Humedad</span>
                                <span class="font-medium text-zinc-700">{{ $room->humidity }}%</span>
                            </div>
                        @endif
                    </div>

                    <x-slot:footer>
                        <div class="flex items-center gap-1">
                            <a href="{{ route('winery.container-rooms.edit', $room) }}" wire:navigate
                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors" title="Editar">
                                <flux:icon icon="pencil-square" class="size-4" />
                            </a>
                            <button
                                wire:click="delete({{ $room->id }})"
                                wire:confirm="¿Eliminar la sala «{{ $room->name }}»?"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-red-500 hover:bg-red-50 transition-colors" title="Eliminar">
                                <flux:icon icon="trash" class="size-4" />
                            </button>
                        </div>
                    </x-slot:footer>
                </x-agro.card>
            @endforeach
        </div>

        <div class="mt-6">{{ $rooms->links() }}</div>
    @endif

</div>
