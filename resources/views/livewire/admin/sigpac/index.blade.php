<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Códigos SIGPAC del Sistema"
        description="Visualiza todos los códigos SIGPAC registrados por todos los usuarios"
    />

    {{-- Estadísticas --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-agro.stat-card label="Total Códigos"      :value="$stats['total']"                    icon="hashtag"    color="blue"   />
        <x-agro.stat-card label="Por Viticultores"   :value="$stats['by_role']['viticulturist']" icon="map"        color="agro"   />
        <x-agro.stat-card label="Por Bodegas"        :value="$stats['by_role']['winery']"        icon="building-office" color="purple" />
        <x-agro.stat-card label="Por Supervisores"   :value="$stats['by_role']['supervisor']"    icon="shield-check"    color="blue"   />
    </div>

    {{-- Filtros --}}
    <x-agro.filter-bar>
        <x-agro.filter-input
            wire:model.live="search"
            placeholder="Buscar por código o usuario..."
        />
        <x-agro.filter-select wire:model.live="roleFilter">
            <option value="all">Todos los roles</option>
            <option value="viticulturist">Viticultores</option>
            <option value="winery">Bodegas</option>
            <option value="supervisor">Supervisores</option>
        </x-agro.filter-select>
    </x-agro.filter-bar>

    {{-- Tabla --}}
    <x-agro.data-table
        :headers="['Código SIGPAC', 'Parcelas', 'Usuario', 'Registro']"
        empty-message="No se encontraron códigos SIGPAC"
        empty-description="No hay códigos que coincidan con los filtros seleccionados"
        empty-icon="hashtag"
    >
        @if($sigpacs->count() > 0)
            @foreach($sigpacs as $sigpac)
                @php
                    $firstPlot = $sigpac->plots->first();
                    $user = $firstPlot ? $firstPlot->viticulturist : null;
                    $roleMap = [
                        'admin'         => ['label' => 'Admin',      'color' => 'purple'],
                        'supervisor'    => ['label' => 'Supervisor', 'color' => 'blue'],
                        'winery'        => ['label' => 'Bodega',     'color' => 'violet'],
                        'viticulturist' => ['label' => 'Viticultor', 'color' => 'green'],
                    ];
                    $roleInfo = $user ? ($roleMap[$user->role] ?? ['label' => ucfirst($user->role), 'color' => null]) : null;
                @endphp
                <x-agro.table-row>
                    <x-agro.table-cell>
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0">
                                <flux:icon icon="hashtag" class="size-4 text-blue-600" />
                            </div>
                            <div>
                                <p class="text-sm font-bold text-zinc-900 font-mono">{{ $sigpac->code }}</p>
                                <p class="text-xs text-zinc-400 mt-0.5">{{ $sigpac->full_code }}</p>
                            </div>
                        </div>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <flux:badge color="blue" size="sm">
                            {{ $sigpac->plots_count }} parcela{{ $sigpac->plots_count !== 1 ? 's' : '' }}
                        </flux:badge>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        @if($user)
                            <p class="text-sm font-semibold text-zinc-900">{{ $user->name }}</p>
                            <p class="text-xs text-zinc-400">{{ $user->email }}</p>
                            @if($roleInfo)
                                <div class="mt-1">
                                    <flux:badge :color="$roleInfo['color']" size="sm">{{ $roleInfo['label'] }}</flux:badge>
                                </div>
                            @endif
                        @else
                            <span class="text-sm text-zinc-400">Sin usuario</span>
                        @endif
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <p class="text-sm text-zinc-700">{{ $sigpac->created_at->format('d/m/Y') }}</p>
                        <p class="text-xs text-zinc-400">{{ $sigpac->created_at->diffForHumans() }}</p>
                    </x-agro.table-cell>
                </x-agro.table-row>
            @endforeach

            <x-slot name="pagination">
                {{ $sigpacs->links() }}
            </x-slot>
        @endif
    </x-agro.data-table>
</div>
