<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Mis Viticultores"
        description="Viticultores vinculados a tu bodega"
    >
        <x-slot:actions>
            <flux:button href="{{ route('winery.viticulturists.invite') }}" variant="ghost" icon="link">
                Invitar existente
            </flux:button>
            <flux:button href="{{ route('winery.viticulturists.create') }}" variant="primary" icon="plus">
                Añadir Viticultor
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    <x-agro.filter-bar>
        <x-agro.filter-input
            wire:model.live.debounce.300ms="search"
            placeholder="Buscar por nombre o email..."
        />
        @if ($search)
            <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">
                Limpiar
            </flux:button>
        @endif
    </x-agro.filter-bar>

    @if ($relations->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($relations as $relation)
                @php
                    $v     = $relation->viticulturist;
                    $delay = min($loop->index * 50, 300);
                    $sourceLabels = [
                        'own'           => ['label' => 'Propio',       'color' => 'blue'],
                        'supervisor'    => ['label' => 'D.O.',          'color' => 'purple'],
                        'viticulturist' => ['label' => 'Viticultor',   'color' => 'zinc'],
                        'self'          => ['label' => 'Autoregistro', 'color' => 'green'],
                    ];
                    $src = $sourceLabels[$relation->source] ?? ['label' => $relation->source, 'color' => 'zinc'];
                @endphp
                <x-agro.card
                    class="animate-fade-in-up flex flex-col"
                    style="animation-delay: {{ $delay }}ms;"
                    wire:key="viticulturist-card-{{ $relation->id }}"
                >
                    <x-slot:header>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-agro-50 flex items-center justify-center shrink-0">
                                <span class="text-sm font-bold text-agro-700">
                                    {{ strtoupper(substr($v->name, 0, 1)) }}
                                </span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-bold text-zinc-900 truncate">{{ $v->name }}</h3>
                                <p class="text-xs text-zinc-500 truncate">{{ $v->email }}</p>
                            </div>
                            <flux:badge
                                :color="$v->can_login ? 'green' : 'zinc'"
                                size="sm"
                                class="shrink-0"
                            >
                                {{ $v->can_login ? 'Con acceso' : 'Sin acceso' }}
                            </flux:badge>
                        </div>
                    </x-slot:header>

                    <div class="flex-1 space-y-3 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-zinc-500">Parcelas</span>
                            <a href="{{ route('winery.plots.index') }}"
                               class="flex items-center gap-1 font-semibold text-agro-700 hover:underline">
                                <flux:icon icon="map" class="size-4" />
                                {{ $v->plots_count }} {{ Str::plural('parcela', $v->plots_count) }}
                            </a>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-zinc-500">Origen</span>
                            <flux:badge :color="$src['color']" size="sm">{{ $src['label'] }}</flux:badge>
                        </div>
                    </div>

                    <x-slot:footer>
                        <div class="flex justify-end">
                            <a href="{{ route('winery.viticulturists.show', $v->id) }}" title="Ver viticultor">
                                <x-agro.action-button variant="view" />
                            </a>
                        </div>
                    </x-slot:footer>
                </x-agro.card>
            @endforeach
        </div>

        <div class="mt-2">
            {{ $relations->links() }}
        </div>
    @else
        <x-agro.empty-state
            icon="users"
            title="No hay viticultores vinculados"
            description="Añade viticultores para gestionar sus parcelas y vendimias"
        >
            <x-slot:action>
                <flux:button href="{{ route('winery.viticulturists.create') }}" variant="primary" icon="plus">
                    Añadir Viticultor
                </flux:button>
            </x-slot:action>
        </x-agro.empty-state>
    @endif
</div>
