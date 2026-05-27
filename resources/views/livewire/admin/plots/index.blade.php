<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="{{ __('Parcelas del Sistema') }}"
        :description="__('Visualiza todas las parcelas registradas por todos los usuarios')"
    >
        <x-slot:actions>
            <flux:button wire:click="exportCsv" variant="ghost" icon="arrow-down-tray">{{ __('Exportar CSV') }}</flux:button>
        </x-slot:actions>
    </x-agro.page-header>

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
            <span>{{ __('Estadísticas') }}</span>
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
                :label="__('Total Parcelas')"
                :value="$stats['total']"
                icon="map"
                color="agro"
            />
            <x-agro.stat-card
                :label="__('Parcelas Activas')"
                :value="$stats['active']"
                icon="check-circle"
                color="blue"
            />
            <x-agro.stat-card
                :label="__('Área Total')"
                :value="number_format($stats['total_area'], 2) . ' ha'"
                icon="squares-2x2"
                color="purple"
            />
            <x-agro.stat-card
                :label="__('Por Viticultores')"
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
            placeholder="{{ __('Buscar por nombre, descripción o usuario...') }}"
        />
        <x-agro.filter-select wire:model.live="activeFilter">
            <option value="">{{ __('Todas las parcelas') }}</option>
            <option value="1">{{ __('Activas') }}</option>
            <option value="0">{{ __('Inactivas') }}</option>
        </x-agro.filter-select>
        <x-agro.filter-select wire:model.live="roleFilter">
            <option value="all">{{ __('Todos los roles') }}</option>
            <option value="viticulturist">{{ __('Viticultores') }}</option>
            <option value="winery">{{ __('Bodegas') }}</option>
            <option value="supervisor">{{ __('Supervisores') }}</option>
        </x-agro.filter-select>
        <flux:button
            wire:click="toggleInternal"
            variant="ghost"
            size="sm"
            icon="bug-ant"
            tooltip="{{ $showInternal ? __('Ocultar parcelas internas') : __('Mostrar parcelas internas (demo/test)') }}"
            @class(['text-amber-500 bg-amber-50' => $showInternal])
        >
            Internos
        </flux:button>
    </x-agro.filter-bar>

    {{-- Banner modo interno --}}
    @if($showInternal)
    <div class="flex items-center gap-2 rounded-lg bg-amber-50 border border-amber-200 px-4 py-2.5 text-sm text-amber-800">
        <flux:icon icon="bug-ant" class="size-4 flex-shrink-0" />
        <span>{{ __('Mostrando también parcelas de cuentas internas (demo / test / maestro). Las estadísticas siempre reflejan solo datos reales.') }}</span>
        <button wire:click="toggleInternal" class="ml-auto text-amber-600 hover:text-amber-800 font-medium text-xs">{{ __('Ocultar') }}</button>
    </div>
    @endif

    {{-- Tabla --}}
    <x-agro.data-table
        :headers="['Nombre', 'Viticultor', 'Ubicación', 'Área', 'Estado', 'Registro', 'Acciones']"
        empty-:message="__('No se encontraron parcelas')"
        empty-:description="__('No hay parcelas que coincidan con los filtros seleccionados')"
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
                            <span class="text-sm text-zinc-400">{{ __('N/A') }}</span>
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
                        <div class="flex items-center gap-1 justify-end">
                            <x-agro.action-button
                                variant="view"
                                href="{{ route('plots.show', $plot->id) }}"
                            />
                            <flux:button
                                variant="ghost"
                                size="sm"
                                icon="arrow-path"
                                wire:click="openReassignModal({{ $plot->id }})"
                                tooltip="{{ __('Reasignar viticultor') }}"
                            />
                        </div>
                    </x-agro.table-cell>
                </x-agro.table-row>
            @endforeach

            <x-slot name="pagination">
                <x-agro-pagination :paginator="$plots" />
            </x-slot>
        @else
            <x-slot name="emptyAction">
                <p class="text-sm text-zinc-400">{{ __('Intenta ajustar los filtros de búsqueda') }}</p>
            </x-slot>
        @endif
    </x-agro.data-table>

    {{-- Modal: Reasignar viticultor --}}
    <flux:modal wire:model="showReassignModal" class="w-full max-w-lg">
        <div class="p-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="p-2 rounded-lg bg-agro-50">
                    <flux:icon icon="arrow-path" class="size-5 text-agro-600" />
                </div>
                <div>
                    <h3 class="text-base font-semibold text-zinc-900">{{ __('Reasignar Parcela') }}</h3>
                    <p class="text-xs text-zinc-500">{{ $reassignPlotName }}</p>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-zinc-700 mb-1">{{ __('Buscar usuario') }}</label>
                    <flux:input wire:model.live="reassignSearch" :placeholder="__('Nombre o email...')" />
                </div>

                @if($availableUsers->count() > 0)
                <div>
                    <label class="block text-xs font-medium text-zinc-700 mb-1">{{ __('Seleccionar') }}</label>
                    <div class="space-y-1 max-h-48 overflow-y-auto border border-zinc-200 rounded-lg p-1">
                        @foreach($availableUsers as $u)
                        <label class="flex items-center gap-3 px-3 py-2 rounded-md cursor-pointer hover:bg-zinc-50 transition-colors {{ (string)$reassignViticulturistId === (string)$u->id ? 'bg-agro-50' : '' }}">
                            <input
                                type="radio"
                                wire:model.live="reassignViticulturistId"
                                value="{{ $u->id }}"
                                class="text-agro-600 focus:ring-agro-500"
                            />
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-zinc-900 truncate">{{ $u->name }}</p>
                                <p class="text-xs text-zinc-400 truncate">{{ $u->email }} · {{ $u->role }}</p>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>
                @elseif($reassignSearch)
                    <p class="text-sm text-zinc-400 text-center py-3">{{ __('No se encontraron usuarios') }}</p>
                @else
                    <p class="text-sm text-zinc-400 text-center py-3">{{ __('Escribe para buscar un usuario') }}</p>
                @endif

                @error('reassignViticulturistId') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-zinc-100">
                <flux:button variant="ghost" wire:click="closeReassignModal">{{ __('Cancelar') }}</flux:button>
                <flux:button variant="primary" wire:click="reassignViticulturist">{{ __('Reasignar') }}</flux:button>
            </div>
        </div>
    </flux:modal>
</div>

