<x-agro.page-header
    title="Equipos de Aplicación"
    description="Registro de maquinaria y equipos de aplicación (ITB pulverizadores)"
    icon="wrench-screwdriver"
>
    <x-slot:actions>
        <flux:button wire:click="openCreate" variant="primary" icon="plus">
            Añadir Equipo
        </flux:button>
    </x-slot:actions>
</x-agro.page-header>

<flux:callout variant="info" icon="information-circle" class="mb-6">
    Los pulverizadores deben pasar la <strong>Inspección Técnica de Equipos de Aplicación (ITEA)</strong> cada 3 años (RD 1702/2011). Registra la fecha para recibir alertas de vencimiento.
</flux:callout>

<x-agro.card>
    @if($equipment->isEmpty())
        <x-agro.empty-state
            icon="wrench-screwdriver"
            title="Sin equipos registrados"
            description="Registra los equipos de aplicación que utilizas en tu explotación."
        />
    @else
        <x-agro.data-table :headers="['Nombre', 'Tipo', 'Matrícula/Serie', 'Última inspección', 'Próxima inspección', 'Acciones']">
            @foreach($equipment as $item)
                <x-agro.table-row>
                    <x-agro.table-cell>
                        <span class="font-medium text-zinc-900">{{ $item->name }}</span>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <x-agro.status-badge :label="$item->type_label" color="blue" />
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        {{ $item->registration_number ?? '—' }}
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        {{ $item->last_inspection_date?->format('d/m/Y') ?? '—' }}
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        @if($item->next_inspection_date)
                            @if($item->isInspectionOverdue())
                                <x-agro.status-badge :label="$item->next_inspection_date->format('d/m/Y') . ' VENCIDA'" color="red" />
                            @elseif($item->isInspectionDue())
                                <x-agro.status-badge :label="$item->next_inspection_date->format('d/m/Y')" color="amber" />
                            @else
                                <span class="text-sm text-zinc-700">{{ $item->next_inspection_date->format('d/m/Y') }}</span>
                            @endif
                        @else
                            <span class="text-zinc-400 text-sm">—</span>
                        @endif
                    </x-agro.table-cell>

                    <x-agro.table-cell align="right">
                        <div class="flex items-center justify-end gap-2">
                            <flux:button wire:click="openEdit({{ $item->id }})" size="sm" variant="ghost" icon="pencil">Editar</flux:button>
                            <flux:button wire:click="deactivate({{ $item->id }})" wire:confirm="¿Dar de baja este equipo?" size="sm" variant="ghost" icon="archive-box">Baja</flux:button>
                        </div>
                    </x-agro.table-cell>
                </x-agro.table-row>
            @endforeach
        </x-agro.data-table>
    @endif
</x-agro.card>

{{-- Modal Crear --}}
<flux:modal wire:model="showCreateModal" max-width="lg">
    <div class="p-6 space-y-6">
        <div>
            <h3 class="text-lg font-semibold text-zinc-900">Nuevo Equipo</h3>
        </div>
        @include('livewire.viticulturist.field-equipment._form')
        <div class="flex justify-end gap-3 pt-4 border-t border-zinc-100">
            <flux:button wire:click="$set('showCreateModal', false)" variant="ghost">Cancelar</flux:button>
            <flux:button wire:click="save" variant="primary">Guardar</flux:button>
        </div>
    </div>
</flux:modal>

{{-- Modal Editar --}}
<flux:modal wire:model="showEditModal" max-width="lg">
    <div class="p-6 space-y-6">
        <div>
            <h3 class="text-lg font-semibold text-zinc-900">Editar Equipo</h3>
        </div>
        @include('livewire.viticulturist.field-equipment._form')
        <div class="flex justify-end gap-3 pt-4 border-t border-zinc-100">
            <flux:button wire:click="$set('showEditModal', false)" variant="ghost">Cancelar</flux:button>
            <flux:button wire:click="update" variant="primary">Actualizar</flux:button>
        </div>
    </div>
</flux:modal>
