<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="{{ __('Log de Seguridad') }}"
        :description="__('Historial de eventos de seguridad del sistema')"
    >
        <x-slot:actions>
            <flux:button wire:click="resetFilters" variant="ghost" icon="arrow-path">{{ __('Resetear filtros') }}</flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Stats --}}
    <div class="grid grid-cols-2 gap-4">
        <x-agro.stat-card :label="__('Entradas')"  :value="$stats['total']"    icon="list-bullet"          color="blue"   />
        <x-agro.stat-card :label="__('Info')"      :value="$stats['info']"     icon="information-circle"   color="agro"   />
        <x-agro.stat-card :label="__('Avisos')"    :value="$stats['warnings']" icon="exclamation-triangle" color="orange" />
        <x-agro.stat-card :label="__('Alertas')"   :value="$stats['alerts']"   icon="exclamation-circle"   color="red"    />
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
            <option value="">{{ __('Todos los niveles') }}</option>
            <option value="info">{{ __('Info') }}</option>
            <option value="notice">{{ __('Notice') }}</option>
            <option value="warning">{{ __('Warning') }}</option>
            <option value="alert">{{ __('Alert') }}</option>
            <option value="error">{{ __('Error') }}</option>
            <option value="critical">{{ __('Critical') }}</option>
        </x-agro.filter-select>

        <x-agro.filter-select wire:model.live="filterEvent">
            <option value="">{{ __('Todos los eventos') }}</option>
            <option value="failed_login">{{ __('Login fallido') }}</option>
            <option value="rate_limit_reached">{{ __('Rate limit') }}</option>
            <option value="user_impersonation">{{ __('Impersonación') }}</option>
            <option value="access_denied">{{ __('Acceso denegado') }}</option>
            <option value="account_locked">{{ __('Cuenta bloqueada') }}</option>
            <option value="user_created">{{ __('Usuario creado') }}</option>
            <option value="user_deleted">{{ __('Usuario eliminado') }}</option>
            <option value="user_edited">{{ __('Usuario editado') }}</option>
            <option value="beta_toggled">{{ __('Beta toggled') }}</option>
            <option value="settings_">{{ __('Configuración') }}</option>
            <option value="organization_">{{ __('Organización') }}</option>
        </x-agro.filter-select>

        <x-agro.filter-input wire:model.live="search" :placeholder="__('Buscar IP, email, evento...')" />
    </x-agro.filter-bar>

    {{-- Tabla --}}
    <x-agro.card :padding="false">
        @if($entries->isEmpty())
            <x-agro.empty-state
                icon="list-bullet"
                :message="__('Sin entradas')"
                :description="__('No hay eventos de seguridad para los filtros seleccionados')"
            />
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200">
                    <thead class="bg-zinc-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-600 uppercase tracking-wider w-40">{{ __('Timestamp') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-600 uppercase tracking-wider w-20">{{ __('Nivel') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-600 uppercase tracking-wider">{{ __('Evento / Mensaje') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-600 uppercase tracking-wider w-32">{{ __('IP') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-600 uppercase tracking-wider">{{ __('Contexto') }}</th>
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

            <x-agro.pagination :paginator="$entries" />
        @endif
    </x-agro.card>
</div>
