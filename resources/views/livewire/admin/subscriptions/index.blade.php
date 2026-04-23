<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Suscripciones"
        description="Gestión de suscripciones y pagos de usuarios del sistema"
    >
        <x-slot:actions>
            <flux:button wire:click="exportCsv" variant="ghost" icon="arrow-down-tray">
                Exportar CSV
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Estadísticas --}}
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
        <x-agro.stat-card label="Total"            :value="$stats['total']"             icon="credit-card"  color="blue"   />
        <x-agro.stat-card label="Activas"          :value="$stats['active']"            icon="check-circle" color="agro"   />
        <x-agro.stat-card label="Canceladas"       :value="$stats['cancelled']"         icon="x-circle"     color="red"    />
        <x-agro.stat-card label="Expiradas"        :value="$stats['expired']"           icon="clock"        color="yellow" />
        <x-agro.stat-card
            label="Ingresos este año"
            :value="number_format($stats['revenue_this_year'], 2) . ' €'"
            icon="banknotes"
            color="agro"
        />
        <x-agro.stat-card
            label="Ingresos totales"
            :value="number_format($stats['revenue_total'], 2) . ' €'"
            icon="banknotes"
            color="purple"
        />
    </div>

    {{-- Evolución mensual --}}
    @if($monthlyStats->count() > 0)
    <x-agro.card>
        <x-slot:header>
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-agro-50 rounded-xl flex items-center justify-center">
                    <flux:icon icon="chart-bar" class="size-5 text-agro-600" />
                </div>
                <div>
                    <h3 class="font-semibold text-zinc-900">Evolución mensual {{ now()->year }}</h3>
                    <p class="text-xs text-zinc-400">Ingresos, nuevas suscripciones y cancelaciones por mes</p>
                </div>
            </div>
        </x-slot:header>

        {{-- Bar chart --}}
        <div class="flex items-end gap-2 h-32 px-1 mb-4">
            @foreach($monthlyStats as $row)
                @php $pct = $maxRevenue > 0 ? round(($row['revenue'] / $maxRevenue) * 100) : 0; @endphp
                <div class="flex-1 flex flex-col items-center gap-1 group relative">
                    {{-- Tooltip --}}
                    <div class="absolute bottom-full mb-2 left-1/2 -translate-x-1/2 hidden group-hover:flex flex-col items-center z-10 pointer-events-none">
                        <div class="bg-zinc-900 text-white text-xs rounded-lg px-2.5 py-2 whitespace-nowrap shadow-lg">
                            <p class="font-semibold">{{ $row['label'] }}</p>
                            <p>Ingresos: {{ number_format($row['revenue'], 2) }} €</p>
                            <p>Nuevas: {{ $row['new_subs'] }} · Bajas: {{ $row['cancelled'] }}</p>
                        </div>
                        <div class="w-2 h-2 bg-zinc-900 rotate-45 -mt-1"></div>
                    </div>
                    {{-- Bar --}}
                    <div
                        class="w-full rounded-t-md bg-agro-400 transition-all"
                        style="height: {{ max($pct, 4) }}%"
                    ></div>
                    {{-- Label --}}
                    <span class="text-[10px] text-zinc-400">{{ $row['label'] }}</span>
                </div>
            @endforeach
        </div>

        {{-- Monthly breakdown table --}}
        <div class="overflow-x-auto -mx-5 sm:-mx-6">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-t border-zinc-100 bg-zinc-50">
                        <th class="text-left text-xs font-medium text-zinc-500 px-5 sm:px-6 py-2.5">Mes</th>
                        <th class="text-right text-xs font-medium text-zinc-500 px-4 py-2.5">Ingresos</th>
                        <th class="text-right text-xs font-medium text-zinc-500 px-4 py-2.5">Pagos</th>
                        <th class="text-right text-xs font-medium text-zinc-500 px-4 py-2.5">Nuevas</th>
                        <th class="text-right text-xs font-medium text-zinc-500 px-5 sm:px-6 py-2.5">Canceladas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @foreach($monthlyStats as $row)
                        <tr class="hover:bg-zinc-50 transition-colors">
                            <td class="px-5 sm:px-6 py-2.5 font-medium text-zinc-700">{{ $row['label'] }}</td>
                            <td class="px-4 py-2.5 text-right font-semibold text-zinc-900">
                                {{ $row['revenue'] > 0 ? number_format($row['revenue'], 2) . ' €' : '—' }}
                            </td>
                            <td class="px-4 py-2.5 text-right text-zinc-500">{{ $row['payments'] ?: '—' }}</td>
                            <td class="px-4 py-2.5 text-right">
                                @if($row['new_subs'] > 0)
                                    <span class="text-agro-600 font-medium">+{{ $row['new_subs'] }}</span>
                                @else
                                    <span class="text-zinc-400">—</span>
                                @endif
                            </td>
                            <td class="px-5 sm:px-6 py-2.5 text-right">
                                @if($row['cancelled'] > 0)
                                    <span class="text-red-500 font-medium">{{ $row['cancelled'] }}</span>
                                @else
                                    <span class="text-zinc-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-zinc-200 bg-zinc-50">
                        <td class="px-5 sm:px-6 py-2.5 font-semibold text-zinc-700">Total {{ now()->year }}</td>
                        <td class="px-4 py-2.5 text-right font-bold text-zinc-900">
                            {{ number_format($monthlyStats->sum('revenue'), 2) }} €
                        </td>
                        <td class="px-4 py-2.5 text-right font-semibold text-zinc-700">{{ $monthlyStats->sum('payments') }}</td>
                        <td class="px-4 py-2.5 text-right font-semibold text-agro-600">+{{ $monthlyStats->sum('new_subs') }}</td>
                        <td class="px-5 sm:px-6 py-2.5 text-right font-semibold text-red-500">{{ $monthlyStats->sum('cancelled') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </x-agro.card>
    @endif

    {{-- Filtros --}}
    <x-agro.filter-bar>
        <x-agro.filter-input
            wire:model.live="search"
            placeholder="Buscar por nombre o email..."
        />
        <x-agro.filter-select wire:model.live="filterStatus">
            <option value="all">Todos los estados</option>
            <option value="active">Activas</option>
            <option value="cancelled">Canceladas</option>
            <option value="expired">Expiradas</option>
        </x-agro.filter-select>
        <x-agro.filter-select wire:model.live="filterPlan">
            <option value="all">Todos los planes</option>
            <option value="monthly">Mensual</option>
            <option value="yearly">Anual</option>
        </x-agro.filter-select>
    </x-agro.filter-bar>

    {{-- Tabla --}}
    <x-agro.data-table
        :headers="['Usuario', 'Plan', 'Estado', 'Importe', 'Vigencia', 'PayPal', '']"
        empty-message="No hay suscripciones"
        empty-description="No se encontraron suscripciones con los filtros seleccionados"
        empty-icon="credit-card"
    >
        @if($subscriptions->count() > 0)
            @foreach($subscriptions as $sub)
                <x-agro.table-row>
                    <x-agro.table-cell>
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0">
                                <flux:icon icon="user" class="size-4 text-blue-600" />
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-zinc-900">{{ $sub->user?->name ?? '—' }}</p>
                                <p class="text-xs text-zinc-400">{{ $sub->user?->email }}</p>
                                @if($sub->user?->role)
                                    <p class="text-xs text-zinc-400 capitalize">{{ $sub->user->role }}</p>
                                @endif
                            </div>
                        </div>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <flux:badge color="{{ $sub->plan_type === 'yearly' ? 'purple' : 'blue' }}" size="sm">
                            {{ $sub->plan_type === 'yearly' ? 'Anual' : 'Mensual' }}
                        </flux:badge>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        @php
                            $statusMap = [
                                'active'    => ['label' => 'Activa',    'color' => 'green'],
                                'cancelled' => ['label' => 'Cancelada', 'color' => 'red'],
                                'expired'   => ['label' => 'Expirada',  'color' => 'zinc'],
                            ];
                            $s = $statusMap[$sub->status] ?? ['label' => $sub->status, 'color' => null];
                        @endphp
                        <flux:badge :color="$s['color']" size="sm">{{ $s['label'] }}</flux:badge>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <span class="text-sm font-semibold text-zinc-900">{{ number_format($sub->amount, 2) }} €</span>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        @if($sub->starts_at && $sub->ends_at)
                            <p class="text-sm text-zinc-700">{{ $sub->starts_at->format('d/m/Y') }}</p>
                            <p class="text-xs text-zinc-400">hasta {{ $sub->ends_at->format('d/m/Y') }}</p>
                            @if($sub->ends_at->isPast() && $sub->status === 'active')
                                <flux:badge color="red" size="sm">Vencida</flux:badge>
                            @elseif($sub->ends_at->diffInDays(now()) < 0 && $sub->ends_at->diffInDays(now()) > -15)
                                <flux:badge color="yellow" size="sm">Próx. vencimiento</flux:badge>
                            @endif
                        @else
                            <span class="text-zinc-400">—</span>
                        @endif
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        @if($sub->paypal_subscription_id)
                            <p class="text-xs font-mono text-zinc-500 truncate max-w-[120px]">{{ $sub->paypal_subscription_id }}</p>
                        @else
                            <span class="text-zinc-400">—</span>
                        @endif
                    </x-agro.table-cell>

                    <x-agro.table-cell align="right">
                        @if($sub->status !== 'cancelled')
                            <flux:button
                                variant="ghost"
                                size="sm"
                                icon="x-circle"
                                class="text-red-400 hover:text-red-600"
                                wire:click="cancelSubscription({{ $sub->id }})"
                                wire:confirm="¿Cancelar la suscripción de {{ $sub->user?->name }}?"
                                tooltip="Cancelar suscripción"
                            />
                        @endif
                    </x-agro.table-cell>
                </x-agro.table-row>
            @endforeach

            <x-slot name="pagination">
                {{ $subscriptions->links() }}
            </x-slot>
        @endif
    </x-agro.data-table>
</div>
