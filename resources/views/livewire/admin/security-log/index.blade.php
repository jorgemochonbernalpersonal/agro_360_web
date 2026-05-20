<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Log de Seguridad"
        description="Historial de eventos de seguridad del sistema"
    >
        <x-slot:actions>
            <flux:button wire:click="resetFilters" variant="ghost" icon="arrow-path">
                Resetear filtros
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Stats --}}
    <div class="grid grid-cols-2 gap-4">
        <x-agro.stat-card label="Entradas"  :value="$stats['total']"    icon="list-bullet"          color="blue"   />
        <x-agro.stat-card label="Info"      :value="$stats['info']"     icon="information-circle"   color="agro"   />
        <x-agro.stat-card label="Avisos"    :value="$stats['warnings']" icon="exclamation-triangle" color="orange" />
        <x-agro.stat-card label="Alertas"   :value="$stats['alerts']"   icon="exclamation-circle"   color="red"    />
    </div>

    {{-- Filtros --}}
    <x-agro.filter-bar>
        {{-- Rango de fechas --}}
        <div class="flex items-center gap-2">
            <flux:input
                wire:model.live="filterDateFrom"
                type="date"
                class="text-xs"
            />
            <span class="text-zinc-400 text-xs">—</span>
            <flux:input
                wire:model.live="filterDateTo"
                type="date"
                class="text-xs"
            />
        </div>

        <x-agro.filter-select wire:model.live="filterLevel">
            <option value="">Todos los niveles</option>
            <option value="info">Info</option>
            <option value="notice">Notice</option>
            <option value="warning">Warning</option>
            <option value="alert">Alert</option>
            <option value="error">Error</option>
            <option value="critical">Critical</option>
        </x-agro.filter-select>

        <x-agro.filter-select wire:model.live="filterEvent">
            <option value="">Todos los eventos</option>
            <option value="failed_login">Login fallido</option>
            <option value="rate_limit_reached">Rate limit</option>
            <option value="user_impersonation">Impersonación</option>
            <option value="access_denied">Acceso denegado</option>
            <option value="account_locked">Cuenta bloqueada</option>
            <option value="user_created">Usuario creado</option>
            <option value="user_deleted">Usuario eliminado</option>
            <option value="user_edited">Usuario editado</option>
            <option value="beta_toggled">Beta toggled</option>
            <option value="settings_">Configuración</option>
            <option value="organization_">Organización</option>
        </x-agro.filter-select>

        <x-agro.filter-input wire:model.live="search" placeholder="Buscar IP, email, evento..." />
        <flux:button
            wire:click="toggleInternal"
            variant="ghost"
            size="sm"
            icon="bug-ant"
            tooltip="{{ $showInternal ? 'Ocultar eventos internos' : 'Mostrar eventos de cuentas internas (demo/test)' }}"
            @class(['text-amber-500 bg-amber-50' => $showInternal])
        >
            Internos
        </flux:button>
    </x-agro.filter-bar>

    {{-- Banner modo interno --}}
    @if($showInternal)
    <div class="flex items-center gap-2 rounded-lg bg-amber-50 border border-amber-200 px-4 py-2.5 text-sm text-amber-800">
        <flux:icon icon="bug-ant" class="size-4 flex-shrink-0" />
        <span>Mostrando también eventos de cuentas internas (demo / test). Las estadísticas incluyen estos eventos.</span>
        <button wire:click="toggleInternal" class="ml-auto text-amber-600 hover:text-amber-800 font-medium text-xs">Ocultar</button>
    </div>
    @endif

    {{-- Tabla --}}
    <x-agro.card :padding="false">
        @if($entries->isEmpty())
            <x-agro.empty-state
                icon="list-bullet"
                message="Sin entradas"
                description="No hay eventos de seguridad para los filtros seleccionados"
            />
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200">
                    <thead class="bg-zinc-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-600 uppercase tracking-wider w-40">Timestamp</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-600 uppercase tracking-wider w-20">Nivel</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-600 uppercase tracking-wider">Evento / Mensaje</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-600 uppercase tracking-wider w-32">IP</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-600 uppercase tracking-wider">Contexto</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-zinc-100 text-xs font-mono">
                        @foreach($entries as $entry)
                            @php
                                $levelColors = [
                                    'info'      => 'text-blue-600 bg-blue-50',
                                    'notice'    => 'text-agro-700 bg-agro-50',
                                    'warning'   => 'text-orange-700 bg-orange-50',
                                    'alert'     => 'text-red-700 bg-red-50',
                                    'error'     => 'text-red-800 bg-red-100',
                                    'critical'  => 'text-red-900 bg-red-200',
                                    'emergency' => 'text-white bg-red-600',
                                ];
                                $levelClass = $levelColors[$entry->level] ?? 'text-zinc-600 bg-zinc-100';
                                $context    = $entry->context ?? [];
                            @endphp
                            <tr class="hover:bg-zinc-50 transition-colors">
                                <td class="px-4 py-2.5 text-zinc-500 whitespace-nowrap">
                                    {{ $entry->created_at->format('Y-m-d H:i:s') }}
                                </td>
                                <td class="px-4 py-2.5">
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold {{ $levelClass }}">
                                        {{ strtoupper($entry->level) }}
                                    </span>
                                </td>
                                <td class="px-4 py-2.5">
                                    <p class="text-zinc-900 font-semibold">{{ $entry->event ?: '—' }}</p>
                                    <p class="text-zinc-500 font-normal text-[11px] mt-0.5 truncate max-w-xs">{{ $entry->message }}</p>
                                </td>
                                <td class="px-4 py-2.5 text-zinc-500 whitespace-nowrap">
                                    {{ $entry->ip ?: '—' }}
                                </td>
                                <td class="px-4 py-2.5 text-zinc-500 max-w-xs">
                                    <div class="flex flex-wrap gap-1">
                                        @if($entry->email)
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-zinc-100 text-zinc-700">{{ $entry->email }}</span>
                                        @endif
                                        @if($entry->user_id)
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-zinc-100 text-zinc-700">uid:{{ $entry->user_id }}</span>
                                        @endif
                                        @if($entry->admin_id)
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-purple-100 text-purple-700">admin:{{ $entry->admin_id }}</span>
                                        @endif
                                        @foreach($context as $k => $v)
                                            @if($v !== null && $v !== '')
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-zinc-100 text-zinc-600">{{ $k }}:{{ is_array($v) ? json_encode($v) : $v }}</span>
                                            @endif
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($entries->hasPages())
                <div class="px-6 py-4 border-t border-zinc-200">
                    {{ $entries->links() }}
                </div>
            @endif
        @endif
    </x-agro.card>
</div>
