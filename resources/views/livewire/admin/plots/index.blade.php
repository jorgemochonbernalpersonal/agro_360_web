<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Parcelas del Sistema"
        description="Visualiza todas las parcelas registradas por todos los usuarios"
    />

    {{-- Estadísticas --}}
    <div x-data="{
        open: localStorage.getItem('admin-plots-stats-open') !== 'false',
        toggle() {
            this.open = !this.open;
            localStorage.setItem('admin-plots-stats-open', String(this.open));
        }
    }">
        <button
            @click="toggle()"
            class="flex items-center gap-1.5 text-[11px] font-semibold text-zinc-400 uppercase tracking-widest hover:text-zinc-600 transition-colors mb-3"
        >
            <span>Estadísticas</span>
            <flux:icon icon="chevron-up" class="size-3.5 transition-transform duration-200" ::class="{ 'rotate-180': !open }" />
        </button>
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-1"
        >
        <div class="grid grid-cols-2 gap-4">
            <x-agro.stat-card
                label="Total Parcelas"
                :value="$stats['total']"
                icon="map"
                color="agro"
            />
            <x-agro.stat-card
                label="Parcelas Activas"
                :value="$stats['active']"
                icon="check-circle"
                color="blue"
            />
            <x-agro.stat-card
                label="Área Total"
                :value="number_format($stats['total_area'], 2) . ' ha'"
                icon="squares-2x2"
                color="purple"
            />
            <x-agro.stat-card
                label="Por Viticultores"
                :value="$stats['by_role']['viticulturist']"
                icon="users"
                color="orange"
            />
        </div>
        </div>
    </div>

    {{-- Filtros --}}
    <x-agro.filter-bar>
        <x-agro.filter-input
            wire:model.live="search"
            placeholder="Buscar por nombre, descripción o usuario..."
        />
        <x-agro.filter-select wire:model.live="activeFilter">
            <option value="">Todas las parcelas</option>
            <option value="1">Activas</option>
            <option value="0">Inactivas</option>
        </x-agro.filter-select>
        <x-agro.filter-select wire:model.live="roleFilter">
            <option value="all">Todos los roles</option>
            <option value="viticulturist">Viticultores</option>
            <option value="winery">Bodegas</option>
            <option value="supervisor">Supervisores</option>
        </x-agro.filter-select>
    </x-agro.filter-bar>

    {{-- Tabla --}}
    <x-agro.data-table
        :headers="['Nombre', 'Viticultor', 'Ubicación', 'Área', 'Estado', 'Registro', 'Acciones']"
        empty-message="No se encontraron parcelas"
        empty-description="No hay parcelas que coincidan con los filtros seleccionados"
        empty-icon="map"
    >
        @if($plots->count() > 0)
            @foreach($plots as $plot)
                @php
                    $roleMap = [
                        'admin'         => ['label' => 'Admin',      'color' => 'purple'],
                        'supervisor'    => ['label' => 'Supervisor', 'color' => 'blue'],
                        'winery'        => ['label' => 'Bodega',     'color' => 'violet'],
                        'viticulturist' => ['label' => 'Viticultor', 'color' => 'green'],
                    ];
                    $ownerRole = $plot->viticulturist?->role;
                    $roleInfo = $ownerRole ? ($roleMap[$ownerRole] ?? ['label' => ucfirst($ownerRole), 'color' => null]) : null;
                @endphp

                <x-agro.table-row>
                    <x-agro.table-cell>
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-agro-50 flex items-center justify-center flex-shrink-0">
                                <flux:icon icon="map" class="size-4 text-agro-600" />
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-zinc-900">{{ $plot->name }}</p>
                                @if($plot->description)
                                    <p class="text-xs text-zinc-400 mt-0.5 truncate">{{ Str::limit($plot->description, 50) }}</p>
                                @endif
                            </div>
                        </div>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        @if($plot->viticulturist)
                            <p class="text-sm font-semibold text-zinc-900">{{ $plot->viticulturist->name }}</p>
                            <p class="text-xs text-zinc-400">{{ $plot->viticulturist->email }}</p>
                            @if($roleInfo)
                                <div class="mt-1">
                                    <flux:badge :color="$roleInfo['color']" size="sm">{{ $roleInfo['label'] }}</flux:badge>
                                </div>
                            @endif
                        @else
                            <span class="text-sm text-zinc-400">N/A</span>
                        @endif
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        @if($plot->municipality)
                            <p class="text-sm text-zinc-700">{{ $plot->municipality->name }}</p>
                            @if($plot->municipality->province)
                                <p class="text-xs text-zinc-400">{{ $plot->municipality->province->name }}</p>
                            @endif
                        @else
                            <span class="text-zinc-400">—</span>
                        @endif
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <span class="text-sm font-semibold text-zinc-900">{{ number_format($plot->area, 2) }} ha</span>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <x-agro.status-badge :active="$plot->active" />
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <p class="text-sm text-zinc-700">{{ $plot->created_at->format('d/m/Y') }}</p>
                        <p class="text-xs text-zinc-400">{{ $plot->created_at->diffForHumans() }}</p>
                    </x-agro.table-cell>

                    <x-agro.table-cell align="right">
                        <x-agro.action-button
                            variant="view"
                            href="{{ route('plots.show', $plot->id) }}"
                        />
                    </x-agro.table-cell>
                </x-agro.table-row>
            @endforeach

            <x-slot name="pagination">
                {{ $plots->links() }}
            </x-slot>
        @else
            <x-slot name="emptyAction">
                <p class="text-sm text-zinc-400">Intenta ajustar los filtros de búsqueda</p>
            </x-slot>
        @endif
    </x-agro.data-table>
</div>

