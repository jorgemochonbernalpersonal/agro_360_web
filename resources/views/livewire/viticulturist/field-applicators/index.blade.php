<x-agro.page-header
    title="Aplicadores Fitosanitarios"
    description="Registro oficial de aplicadores con número ROPO (obligatorio PAC)"
    icon="user-group"
>
    <x-slot:actions>
        <flux:button wire:click="openCreate" variant="primary" icon="plus">
            Añadir Aplicador
        </flux:button>
    </x-slot:actions>
</x-agro.page-header>

<flux:callout variant="info" icon="information-circle" class="mb-6">
    Los aplicadores de productos fitosanitarios deben estar en posesión del <strong>carné ROPO</strong> (RD 1702/2011). Es obligatorio registrarlos en el cuaderno de campo para cumplimiento PAC.
</flux:callout>

{{-- Listado --}}
<x-agro.card>
    @if($applicators->isEmpty())
        <x-agro.empty-state
            icon="user-group"
            title="Sin aplicadores registrados"
            description="Añade los aplicadores que realizan tratamientos fitosanitarios en tu explotación."
        />
    @else
        <x-agro.data-table :headers="['Nombre', 'Nº ROPO', 'Categoría', 'Caducidad ROPO', 'Asesor', 'Acciones']">
            @foreach($applicators as $applicator)
                <x-agro.table-row>
                    <x-agro.table-cell>
                        <span class="font-medium text-zinc-900">{{ $applicator->name }}</span>
                        @if($applicator->email)
                            <p class="text-xs text-zinc-500">{{ $applicator->email }}</p>
                        @endif
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <code class="text-sm bg-zinc-100 px-2 py-0.5 rounded">{{ $applicator->ropo_number }}</code>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <x-agro.status-badge :label="$applicator->category_label" color="blue" />
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        @if($applicator->ropo_expiry_date)
                            @if($applicator->isRopoExpired())
                                <x-agro.status-badge :label="$applicator->ropo_expiry_date->format('d/m/Y')" color="red" />
                            @elseif($applicator->isRopoExpiringSoon())
                                <x-agro.status-badge :label="$applicator->ropo_expiry_date->format('d/m/Y')" color="amber" />
                            @else
                                <span class="text-sm text-zinc-700">{{ $applicator->ropo_expiry_date->format('d/m/Y') }}</span>
                            @endif
                        @else
                            <span class="text-zinc-400 text-sm">—</span>
                        @endif
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        @if($applicator->is_advisor)
                            <x-agro.status-badge label="Sí" color="green" />
                        @else
                            <span class="text-zinc-400 text-sm">No</span>
                        @endif
                    </x-agro.table-cell>

                    <x-agro.table-cell align="right">
                        <div class="flex items-center justify-end gap-2">
                            <flux:button wire:click="openEdit({{ $applicator->id }})" size="sm" variant="ghost" icon="pencil">Editar</flux:button>
                            <flux:button wire:click="deactivate({{ $applicator->id }})" wire:confirm="¿Dar de baja este aplicador?" size="sm" variant="ghost" icon="archive-box">Baja</flux:button>
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
            <h3 class="text-lg font-semibold text-zinc-900">Nuevo Aplicador</h3>
            <p class="text-sm text-zinc-500 mt-1">Registra un aplicador con carné ROPO</p>
        </div>

        @include('livewire.viticulturist.field-applicators._form')

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
            <h3 class="text-lg font-semibold text-zinc-900">Editar Aplicador</h3>
        </div>

        @include('livewire.viticulturist.field-applicators._form')

        <div class="flex justify-end gap-3 pt-4 border-t border-zinc-100">
            <flux:button wire:click="$set('showEditModal', false)" variant="ghost">Cancelar</flux:button>
            <flux:button wire:click="update" variant="primary">Actualizar</flux:button>
        </div>
    </div>
</flux:modal>
