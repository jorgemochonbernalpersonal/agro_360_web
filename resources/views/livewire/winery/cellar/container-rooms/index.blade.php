<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="{{ __('Salas de Bodega') }}"
        :description="__('Gestiona las zonas y salas donde se ubican los depósitos')"
    />

    <div class="flex items-center gap-3">
        <x-agro.search-input wire:model.live.debounce.300ms="search" :placeholder="__('Buscar por nombre...')" />

        <flux:button href="{{ roleRoute('container-rooms.create') }}" wire:navigate variant="primary" icon="plus">
            Nueva sala
        </flux:button>
    </div>

    @if($rooms->isEmpty())
        <x-agro.empty-state
            icon="building-office"
            title="{{ __('Sin salas registradas') }}"
            :description="__('Crea las zonas de tu bodega para organizar los depósitos.')"
        >
            <x-slot:action>
                <flux:button href="{{ roleRoute('container-rooms.create') }}" wire:navigate variant="primary" icon="plus">
                    Nueva sala
                </flux:button>
            </x-slot:action>
        </x-agro.empty-state>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($rooms as $room)
                <x-agro.card class="flex flex-col hover:-translate-y-1 animate-fade-in-up">
                    <x-slot:header>
                        <x-agro.card-item-header
                            icon="building-office"
                            :title="$room->name"
                            :subtitle="$room->description ?? null"
                            iconBg="bg-teal-100"
                            iconColor="text-teal-600"
                            size="md"
                            radius="xl"
                        />
                    </x-slot:header>

                    <div class="flex-1 space-y-2 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-zinc-400">{{ __('Depósitos') }}</span>
                            <span class="font-medium text-zinc-700">{{ $room->containers_count }}</span>
                        </div>
                        @if($room->capacity)
                            <div class="flex items-center justify-between">
                                <span class="text-zinc-400">{{ __('Capacidad') }}</span>
                                <span class="font-medium text-zinc-700">{{ number_format($room->capacity) }} uds.</span>
                            </div>
                        @endif
                        @if($room->temperature !== null)
                            <div class="flex items-center justify-between">
                                <span class="text-zinc-400">{{ __('Temperatura') }}</span>
                                <span class="font-medium text-zinc-700">{{ $room->temperature }}°C</span>
                            </div>
                        @endif
                        @if($room->humidity !== null)
                            <div class="flex items-center justify-between">
                                <span class="text-zinc-400">{{ __('Humedad') }}</span>
                                <span class="font-medium text-zinc-700">{{ $room->humidity }}%</span>
                            </div>
                        @endif
                    </div>

                    <x-slot:footer>
                        <div class="flex items-center gap-1">
                            <x-agro.action-button variant="edit" href="{{ roleRoute('container-rooms.edit', $room) }}" wire:navigate title="{{ __('Editar') }}" />
                            <x-agro.action-button variant="delete" wire:click="delete({{ $room->id }})" wire:confirm="{{ __('¿Eliminar la sala «:name»?', ['name' => $room->name]) }}" wire:loading.attr="disabled" title="{{ __('Eliminar') }}" />
                        </div>
                    </x-slot:footer>
                </x-agro.card>
            @endforeach
        </div>

        <x-agro.pagination :paginator="$rooms" />
    @endif

</div>
