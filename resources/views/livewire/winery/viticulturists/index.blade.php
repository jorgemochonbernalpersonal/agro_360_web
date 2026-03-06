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

    <x-agro.data-table
        :headers="['Viticultor', 'Parcelas', 'Origen', 'Acceso', 'Acciones']"
        empty-message="No hay viticultores vinculados"
        empty-description="Añade viticultores para gestionar sus parcelas y vendimias"
    >
        @if ($relations->count() > 0)
            @foreach ($relations as $relation)
                @php $v = $relation->viticulturist; @endphp
                <x-agro.table-row>
                    <x-agro.table-cell>
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-agro-50 flex items-center justify-center flex-shrink-0">
                                <span class="text-sm font-bold text-agro-700">
                                    {{ strtoupper(substr($v->name, 0, 1)) }}
                                </span>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-zinc-900">{{ $v->name }}</p>
                                <p class="text-xs text-zinc-500">{{ $v->email }}</p>
                            </div>
                        </div>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <a href="{{ route('winery.plots.index') }}"
                           class="inline-flex items-center gap-1 text-sm text-agro-700 hover:underline">
                            <flux:icon icon="map" class="size-4" />
                            {{ $v->plots_count }}
                            {{ Str::plural('parcela', $v->plots_count) }}
                        </a>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        @php
                            $sourceLabels = [
                                'own'          => ['label' => 'Propio',      'color' => 'blue'],
                                'supervisor'   => ['label' => 'D.O.',        'color' => 'purple'],
                                'viticulturist'=> ['label' => 'Viticultor',  'color' => 'zinc'],
                                'self'         => ['label' => 'Autoregistro','color' => 'green'],
                            ];
                            $src = $sourceLabels[$relation->source] ?? ['label' => $relation->source, 'color' => 'zinc'];
                        @endphp
                        <flux:badge :color="$src['color']" size="sm">{{ $src['label'] }}</flux:badge>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <flux:badge
                            :color="$v->can_login ? 'green' : null"
                            :icon="$v->can_login ? 'check' : 'x-mark'"
                            size="sm"
                        >
                            {{ $v->can_login ? 'Con acceso' : 'Sin acceso' }}
                        </flux:badge>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <div class="flex items-center justify-end gap-1">
                            <a href="{{ route('winery.viticulturists.show', $v->id) }}"
                               title="Ver viticultor">
                                <x-agro.action-button variant="view" />
                            </a>
                        </div>
                    </x-agro.table-cell>
                </x-agro.table-row>
            @endforeach

            <x-slot name="pagination">
                {{ $relations->links() }}
            </x-slot>
        @endif
    </x-agro.data-table>
</div>
