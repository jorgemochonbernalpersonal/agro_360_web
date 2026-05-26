<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="{{ __('Detección de Duplicados') }}"
        :description="__('Cuentas con email o nombre idéntico — posibles registros duplicados')"
    >
        <x-slot:actions>
            <div class="flex gap-2">
                <flux:button wire:click="$set('mode','email')" :variant="$mode === 'email' ? 'primary' : 'ghost'" size="sm">{{ __('Por email') }}</flux:button>
                <flux:button wire:click="$set('mode','name')"  :variant="$mode === 'name'  ? 'primary' : 'ghost'" size="sm">{{ __('Por nombre') }}</flux:button>
            </div>
        </x-slot:actions>
    </x-agro.page-header>

    @if($groups->isEmpty())
        <x-agro.empty-state
            icon="check-circle"
            :message="__('Sin duplicados detectados')"
            description="No hay cuentas con {{ $mode === 'email' ? 'el mismo email normalizado' : 'el mismo nombre' }}"
        />
    @else
        <div class="space-y-4">
            @foreach($groups as $group)
                <x-agro.card>
                    <div class="flex items-center gap-2 mb-3">
                        <flux:badge color="red" size="sm">{{ count($group) }} cuentas</flux:badge>
                        <span class="text-sm font-semibold text-zinc-700">
                            {{ $mode === 'email' ? $group[0]->email : $group[0]->name }}
                        </span>
                    </div>
                    <div class="space-y-2">
                        @foreach($group as $i => $u)
                            <div class="flex items-center gap-3 p-3 rounded-lg {{ $i === 0 ? 'bg-agro-50 border border-agro-200' : 'bg-zinc-50 border border-zinc-200' }}">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <a href="{{ route('admin.users.show', $u->id) }}" class="text-sm font-semibold text-zinc-900 hover:text-agro-600">{{ $u->name }}</a>
                                        <flux:badge color="{{ $u->can_login ? 'green' : 'zinc' }}" size="sm">{{ $u->can_login ? 'Activo' : 'Inactivo' }}</flux:badge>
                                        @if($u->email_verified_at) <flux:badge color="blue" size="sm">{{ __('Verificado') }}</flux:badge> @endif
                                        @if($i === 0) <flux:badge color="agro" size="sm">{{ __('Conservar') }}</flux:badge> @endif
                                    </div>
                                    <p class="text-xs text-zinc-400 mt-0.5">{{ $u->email }} · {{ $u->role }} · Registro: {{ $u->created_at->format('d/m/Y') }}</p>
                                </div>
                                @if($i > 0)
                                    <flux:button
                                        wire:click="merge({{ $group[0]->id }}, {{ $u->id }})"
                                        wire:confirm="{{ __('¿Fusionar \':name\' en \':target\'? Se reasignarán sus parcelas, tickets e historial. La cuenta \':name\' será eliminada.', ['name' => $u->name, 'target' => $group[0]->name]) }}"
                                        variant="danger"
                                        size="sm"
                                        icon="arrow-path"
                                    >
                                        Fusionar
                                    </flux:button>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </x-agro.card>
            @endforeach
        </div>
    @endif
</div>
