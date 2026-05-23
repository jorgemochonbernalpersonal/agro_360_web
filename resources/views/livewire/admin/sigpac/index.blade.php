<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Códigos SIGPAC del Sistema"
        description="Visualiza todos los códigos SIGPAC registrados por todos los usuarios"
    >
        <x-slot:actions>
            @if($stats['orphaned'] > 0)
                <flux:button
                    wire:click="deleteOrphaned"
                    wire:confirm="¿Eliminar {{ $stats['orphaned'] }} código(s) SIGPAC sin parcelas asociadas?"
                    variant="danger"
                    icon="trash"
                >
                    Limpiar huérfanos ({{ $stats['orphaned'] }})
                </flux:button>
            @endif
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Estadísticas --}}
    <div class="grid grid-cols-2 gap-4">
        <x-agro.stat-card label="Total Códigos"      :value="$stats['total']"                    icon="hashtag"        color="blue"   />
        <x-agro.stat-card label="Huérfanos"          :value="$stats['orphaned']"                 icon="exclamation-circle" color="{{ $stats['orphaned'] > 0 ? 'red' : 'agro' }}" />
        <x-agro.stat-card label="Por Viticultores"   :value="$stats['by_role']['viticulturist']" icon="map"            color="agro"   />
        <x-agro.stat-card label="Por Bodegas"        :value="$stats['by_role']['winery']"        icon="building-office" color="purple" />
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
        <flux:button
            wire:click="toggleInternal"
            variant="ghost"
            size="sm"
            icon="bug-ant"
            tooltip="{{ $showInternal ? 'Ocultar SIGPACs internos' : 'Mostrar SIGPACs internos (demo/test)' }}"
            @class(['text-amber-500 bg-amber-50' => $showInternal])
        >
            Internos
        </flux:button>
    </x-agro.filter-bar>

    {{-- Banner modo interno --}}
    @if($showInternal)
    <div class="flex items-center gap-2 rounded-lg bg-amber-50 border border-amber-200 px-4 py-2.5 text-sm text-amber-800">
        <flux:icon icon="bug-ant" class="size-4 flex-shrink-0" />
        <span>Mostrando también SIGPACs de cuentas internas (demo / test / maestro). Las estadísticas siempre reflejan solo datos reales.</span>
        <button wire:click="toggleInternal" class="ml-auto text-amber-600 hover:text-amber-800 font-medium text-xs">Ocultar</button>
    </div>
    @endif

    {{-- Tabla --}}
    <x-agro.data-table
        :headers="['Código SIGPAC', 'Parcelas', 'Usuario', 'Registro', '']"
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

                    <x-agro.table-cell align="right">
                        @if($sigpac->plots_count === 0)
                            <flux:button
                                variant="ghost"
                                size="sm"
                                icon="trash"
                                class="text-red-400 hover:text-red-600"
                                wire:click="deleteSigpac({{ $sigpac->id }})"
                                wire:confirm="¿Eliminar el código SIGPAC {{ $sigpac->code }}?"
                                tooltip="Eliminar (huérfano)"
                            />
                        @endif
                    </x-agro.table-cell>
                </x-agro.table-row>
            @endforeach

            <x-slot name="pagination">
                <x-agro-pagination :paginator="$sigpacs" />
            </x-slot>
        @endif
    </x-agro.data-table>
</div>
