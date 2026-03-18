<div class="space-y-6 animate-fade-in">
<x-agro.page-header
    title="Registros Sanitarios"
    description="Gestión de registros RGSEAA, RESA, RPO y otros registros sanitarios oficiales."
    icon="shield-check"
>
    <x-slot:actions>
        <flux:button variant="primary" icon="plus" href="{{ roleRoute('sanitary-registrations.create') }}" wire:navigate>
            Nuevo registro
        </flux:button>
    </x-slot:actions>
</x-agro.page-header>

<x-agro.card>
    <x-agro.filter-bar>
        <x-agro.filter-input wire:model.live="search" placeholder="Buscar por número, descripción..." />
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

    <x-agro.data-table :headers="['N.º Registro', 'Tipo', 'Descripción', 'Fecha registro', 'Renovación', 'Estado', 'Acciones']">
        @forelse($registrations as $registration)
            <x-agro.table-row>
                <x-agro.table-cell>
                    <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $registration->registration_number }}</div>
                    @if($registration->issuing_authority)
                        <div class="text-xs text-zinc-500">{{ $registration->issuing_authority }}</div>
                    @endif
                </x-agro.table-cell>
                <x-agro.table-cell>
                    <x-agro.status-badge :label="$registration->type_label" color="blue" />
                </x-agro.table-cell>
                <x-agro.table-cell>
                    <span class="text-sm text-zinc-700 dark:text-zinc-300">
                        {{ $registration->activity_description ?? '—' }}
                    </span>
                </x-agro.table-cell>
                <x-agro.table-cell>
                    {{ $registration->registration_date?->format('d/m/Y') ?? '—' }}
                </x-agro.table-cell>
                <x-agro.table-cell>
                    @if($registration->renewal_date)
                        <span class="inline-flex items-center gap-1">
                            {{ $registration->renewal_date->format('d/m/Y') }}
                            @if($registration->isExpiringSoon())
                                <x-agro.status-badge label="Próximo" color="amber" />
                            @endif
                        </span>
                    @else
                        <span class="text-zinc-400">—</span>
                    @endif
                </x-agro.table-cell>
                <x-agro.table-cell>
                    @php
                        $statusColor = match($registration->status) {
                            'active'    => 'green',
                            'expired'   => 'red',
                            'suspended' => 'amber',
                            'cancelled' => 'zinc',
                            default     => 'zinc',
                        };
                    @endphp
                    <x-agro.status-badge :label="$registration->status_label" :color="$statusColor" />
                </x-agro.table-cell>
                <x-agro.table-cell align="right">
                    <div class="flex justify-end gap-2">
                        <flux:button size="sm" variant="ghost" icon="pencil"
                            href="{{ roleRoute('sanitary-registrations.edit', $registration) }}" wire:navigate />
                        <flux:button size="sm" variant="ghost" icon="trash"
                            wire:click="delete({{ $registration->id }})"
                            wire:loading.attr="disabled"
                            wire:confirm="¿Eliminar este registro sanitario?" />
                    </div>
                </x-agro.table-cell>
            </x-agro.table-row>
        @empty
            <x-agro.empty-state icon="shield-check" title="Sin registros sanitarios"
                description="Añade los registros sanitarios de la bodega (RGSEAA, RESA, RPO, etc.)." />
        @endforelse
    </x-agro.data-table>

    {{ $registrations->links() }}
</x-agro.card>
</div>
