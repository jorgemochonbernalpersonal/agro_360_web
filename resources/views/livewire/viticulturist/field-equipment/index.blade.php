<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Equipos de Aplicación"
        subtitle="Registro de maquinaria y equipos de aplicación (ITB pulverizadores)"
        icon="wrench-screwdriver"
    >
        <x-slot:actions>
            <flux:button href="{{ route('viticulturist.field-equipment.create') }}" variant="primary" icon="plus">
                Añadir Equipo
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    <flux:callout variant="info" icon="information-circle">
        Los pulverizadores deben pasar la <strong>Inspección Técnica de Equipos de Aplicación (ITEA)</strong> cada 3 años (RD 1702/2011). Registra la fecha para recibir alertas de vencimiento.
    </flux:callout>

    <x-agro.card>
        @if($equipment->isEmpty())
            <x-agro.empty-state
                icon="wrench-screwdriver"
                title="Sin equipos registrados"
                description="Registra los equipos de aplicación que utilizas en tu explotación."
            >
                <x-slot:action>
                    <flux:button href="{{ route('viticulturist.field-equipment.create') }}" variant="primary" icon="plus">
                        Añadir Equipo
                    </flux:button>
                </x-slot:action>
            </x-agro.empty-state>
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
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('viticulturist.field-equipment.edit', $item) }}"
                                   class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors"
                                   title="Editar">
                                    <flux:icon icon="pencil-square" class="size-4" />
                                </a>
                                <button
                                    wire:click="deactivate({{ $item->id }})"
                                    wire:confirm="¿Dar de baja este equipo?"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-amber-600 hover:bg-amber-50 transition-colors"
                                    title="Dar de baja">
                                    <flux:icon icon="archive-box" class="size-4" />
                                </button>
                            </div>
                        </x-agro.table-cell>
                    </x-agro.table-row>
                @endforeach
            </x-agro.data-table>
        @endif
    </x-agro.card>

</div>
