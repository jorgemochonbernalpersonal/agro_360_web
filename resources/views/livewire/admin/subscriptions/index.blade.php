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
