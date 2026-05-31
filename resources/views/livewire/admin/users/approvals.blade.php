<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="{{ __('Cola de Aprobación') }}"
        :description="__('Usuarios con email verificado pendientes de activación manual')"
    >
        <x-slot:actions>
            @if($total > 0)
                <flux:badge color="red">{{ $total }} pendiente{{ $total > 1 ? 's' : '' }}</flux:badge>
            @endif
        </x-slot:actions>
    </x-agro.page-header>

    <x-agro.filter-bar>
        <x-agro.filter-input wire:model.live="search" :placeholder="__('Buscar por nombre o email...')" />
    </x-agro.filter-bar>

    <x-agro.card :padding="false">
        @if($pending->isEmpty())
            <x-agro.empty-state
                icon="check-circle"
                :message="__('Cola vacía')"
                :description="__('No hay usuarios verificados pendientes de activación')"
            />
        @else
            <div class="divide-y divide-zinc-100 dark:divide-zinc-700/50">
                @foreach($pending as $user)
                    <div class="px-5 py-4 flex items-center gap-4">
                        <div class="w-9 h-9 rounded-full bg-zinc-100 flex items-center justify-center flex-shrink-0">
                            <span class="text-sm font-bold text-zinc-500">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-zinc-900">{{ $user->name }}</p>
                            <p class="text-xs text-zinc-400">{{ $user->email }} · {{ $user->role }} · Verificado {{ $user->email_verified_at->diffForHumans() }}</p>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <flux:button wire:click="approve({{ $user->id }})" variant="primary" size="sm" icon="check">{{ __('Aprobar') }}</flux:button>
                            <flux:button
                                wire:click="reject({{ $user->id }})"
                                wire:confirm="{{ __('¿Rechazar y eliminar la cuenta de \':name\'?', ['name' => $user->name]) }}"
                                variant="danger"
                                size="sm"
                                icon="x-mark"
                            >
                                Rechazar
                            </flux:button>
                        </div>
                    </div>
                @endforeach
            </div>
            <x-agro.pagination :paginator="$pending" />
        @endif
    </x-agro.card>
</div>
