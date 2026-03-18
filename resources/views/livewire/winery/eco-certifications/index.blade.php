<div class="space-y-6 animate-fade-in">
<x-agro.page-header
    title="Certificaciones Ecológicas"
    description="Gestión de certificaciones de producción ecológica, biodinámica y sostenible."
    icon="sparkles"
>
    <x-slot:actions>
        <flux:button variant="primary" icon="plus" href="{{ roleRoute('eco-certifications.create') }}" wire:navigate>
            Nueva certificación
        </flux:button>
    </x-slot:actions>
</x-agro.page-header>

<x-agro.card>
    <x-agro.filter-bar>
        <x-agro.filter-input wire:model.live="search" placeholder="Buscar por nombre, organismo..." />
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

    <x-agro.data-table :headers="['Nombre', 'Tipo', 'Organismo certificador', 'N.º certificado', 'Válido hasta', 'Estado', 'Acciones']">
        @forelse($certifications as $cert)
            <x-agro.table-row>
                <x-agro.table-cell>
                    <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $cert->name }}</div>
                </x-agro.table-cell>
                <x-agro.table-cell>
                    <x-agro.status-badge :label="$cert->type_label" color="green" />
                </x-agro.table-cell>
                <x-agro.table-cell>
                    {{ $cert->certifying_body ?? '—' }}
                </x-agro.table-cell>
                <x-agro.table-cell>
                    {{ $cert->certificate_number ?? '—' }}
                </x-agro.table-cell>
                <x-agro.table-cell>
                    @if($cert->valid_until)
                        <span class="inline-flex items-center gap-1">
                            {{ $cert->valid_until->format('d/m/Y') }}
                            @if($cert->isExpiringSoon())
                                <x-agro.status-badge label="Próximo" color="amber" />
                            @endif
                        </span>
                    @else
                        <span class="text-zinc-400">—</span>
                    @endif
                </x-agro.table-cell>
                <x-agro.table-cell>
                    @php
                        $statusColor = match($cert->status) {
                            'active'    => 'green',
                            'expired'   => 'red',
                            'pending'   => 'yellow',
                            'suspended' => 'zinc',
                            default     => 'zinc',
                        };
                    @endphp
                    <x-agro.status-badge :label="$cert->status_label" :color="$statusColor" />
                </x-agro.table-cell>
                <x-agro.table-cell align="right">
                    <div class="flex justify-end gap-2">
                        <flux:button size="sm" variant="ghost" icon="pencil"
                            href="{{ roleRoute('eco-certifications.edit', $cert) }}" wire:navigate />
                        <flux:button size="sm" variant="ghost" icon="trash"
                            wire:click="delete({{ $cert->id }})"
                            wire:loading.attr="disabled"
                            wire:confirm="¿Eliminar esta certificación?" />
                    </div>
                </x-agro.table-cell>
            </x-agro.table-row>
        @empty
            <x-agro.empty-state icon="sparkles" title="Sin certificaciones registradas"
                description="Añade tus certificaciones ecológicas, biodinámicas o de sostenibilidad." />
        @endforelse
    </x-agro.data-table>

    {{ $certifications->links() }}
</x-agro.card>
</div>
