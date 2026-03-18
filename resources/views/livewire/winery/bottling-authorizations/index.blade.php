<div class="space-y-6 animate-fade-in">
<x-agro.page-header
    title="Autorizaciones de Embotellado"
    description="Gestión de autorizaciones oficiales para el embotellado de vinos."
    icon="identification"
>
    <x-slot:actions>
        <flux:button variant="primary" icon="plus" href="{{ roleRoute('bottling-authorizations.create') }}" wire:navigate>
            Nueva autorización
        </flux:button>
    </x-slot:actions>
</x-agro.page-header>

<x-agro.card>
    <x-agro.filter-bar>
        <x-agro.filter-input wire:model.live="search" placeholder="Buscar por número, organismo..." />
        <x-agro.filter-select wire:model.live="typeFilter" placeholder="Todos los tipos">
            @foreach($types as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </x-agro.filter-select>
        <x-agro.filter-select wire:model.live="statusFilter" placeholder="Todos los estados">
            @foreach($statuses as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </x-agro.filter-select>
    </x-agro.filter-bar>

    <x-agro.data-table :headers="['N.º Autorización', 'Tipo', 'Vino', 'Volumen (L)', 'Válido hasta', 'Estado', 'Acciones']">
        @forelse($authorizations as $auth)
            <x-agro.table-row>
                <x-agro.table-cell>
                    <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $auth->authorization_number }}</div>
                    @if($auth->issuing_authority)
                        <div class="text-xs text-zinc-500">{{ $auth->issuing_authority }}</div>
                    @endif
                </x-agro.table-cell>
                <x-agro.table-cell>
                    <x-agro.status-badge :label="$auth->type_label" color="blue" />
                </x-agro.table-cell>
                <x-agro.table-cell>
                    {{ $auth->wine?->name ?? '—' }}
                </x-agro.table-cell>
                <x-agro.table-cell>
                    @if($auth->authorized_volume_liters !== null)
                        {{ number_format($auth->authorized_volume_liters, 2) }}
                    @else
                        <span class="text-zinc-400">—</span>
                    @endif
                </x-agro.table-cell>
                <x-agro.table-cell>
                    @if($auth->valid_until)
                        <span class="inline-flex items-center gap-1">
                            {{ $auth->valid_until->format('d/m/Y') }}
                            @if($auth->isExpiringSoon())
                                <x-agro.status-badge label="Próximo" color="amber" />
                            @endif
                        </span>
                    @else
                        <span class="text-zinc-400">—</span>
                    @endif
                </x-agro.table-cell>
                <x-agro.table-cell>
                    @php
                        $statusColor = match($auth->status) {
                            'active'    => 'green',
                            'used'      => 'blue',
                            'expired'   => 'red',
                            'cancelled' => 'zinc',
                            default     => 'zinc',
                        };
                    @endphp
                    <x-agro.status-badge :label="$auth->status_label" :color="$statusColor" />
                </x-agro.table-cell>
                <x-agro.table-cell align="right">
                    <div class="flex justify-end gap-2">
                        <flux:button size="sm" variant="ghost" icon="pencil"
                            href="{{ roleRoute('bottling-authorizations.edit', $auth) }}" wire:navigate />
                        <flux:button size="sm" variant="ghost" icon="trash"
                            wire:click="delete({{ $auth->id }})"
                            wire:loading.attr="disabled"
                            wire:confirm="¿Eliminar esta autorización de embotellado?" />
                    </div>
                </x-agro.table-cell>
            </x-agro.table-row>
        @empty
            <x-agro.empty-state icon="identification" title="Sin autorizaciones registradas"
                description="Añade las autorizaciones de embotellado de la bodega." />
        @endforelse
    </x-agro.data-table>

    {{ $authorizations->links() }}
</x-agro.card>
</div>
