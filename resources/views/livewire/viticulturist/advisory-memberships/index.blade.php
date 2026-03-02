<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Asesores Técnicos"
        subtitle="Registro de asesores fitosanitarios y técnicos (obligatorio RD 1311/2012)"
        icon="user-group"
    >
        <x-slot:actions>
            <flux:button href="{{ route('viticulturist.advisory-memberships.create') }}" variant="primary" icon="plus">
                Nuevo Asesor
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    <flux:callout variant="info" icon="information-circle">
        El RD 1311/2012 exige contar con un asesor fitosanitario cualificado para la gestión integrada de plagas (GIP). Su nº de colegiado debe figurar en el cuaderno de campo.
    </flux:callout>

    <x-agro.filter-bar>
        <x-agro.filter-select wire:model.live="filterSpecialty" label="Especialidad">
            <option value="">Todas</option>
            @foreach($specialties as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </x-agro.filter-select>
    </x-agro.filter-bar>

    <x-agro.card>
        @if($entries->isEmpty())
            <x-agro.empty-state
                icon="user-group"
                title="Sin asesores registrados"
                description="Añade tus asesores técnicos y fitosanitarios para cumplir con la normativa de producción integrada."
            >
                <x-slot:action>
                    <flux:button href="{{ route('viticulturist.advisory-memberships.create') }}" variant="primary" icon="plus">
                        Nuevo Asesor
                    </flux:button>
                </x-slot:action>
            </x-agro.empty-state>
        @else
            <x-agro.data-table :headers="['Asesor', 'Nº Colegiado', 'Especialidad', 'Empresa', 'Contacto', 'Campaña', 'Acciones']">
                @foreach($entries as $entry)
                    <x-agro.table-row>
                        <x-agro.table-cell class="font-medium">{{ $entry->advisor_name }}</x-agro.table-cell>
                        <x-agro.table-cell>
                            <span class="font-mono text-sm bg-zinc-100 px-2 py-0.5 rounded">{{ $entry->license_number }}</span>
                        </x-agro.table-cell>
                        <x-agro.table-cell>
                            <x-agro.status-badge :status="$entry->specialty" :label="$entry->specialty_label" />
                        </x-agro.table-cell>
                        <x-agro.table-cell>{{ $entry->company_name ?? '-' }}</x-agro.table-cell>
                        <x-agro.table-cell>
                            @if($entry->phone)
                                <span class="text-sm block flex items-center gap-1">
                                    <flux:icon icon="phone" class="size-3 text-zinc-400" />
                                    {{ $entry->phone }}
                                </span>
                            @endif
                            @if($entry->email)
                                <span class="text-sm block text-zinc-500">{{ $entry->email }}</span>
                            @endif
                        </x-agro.table-cell>
                        <x-agro.table-cell>{{ $entry->campaign?->name ?? 'Permanente' }}</x-agro.table-cell>
                        <x-agro.table-cell align="right">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('viticulturist.advisory-memberships.edit', $entry) }}"
                                   class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors"
                                   title="Editar">
                                    <flux:icon icon="pencil-square" class="size-4" />
                                </a>
                                <button
                                    wire:click="deactivate({{ $entry->id }})"
                                    wire:confirm="¿Desactivar este asesor?"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-amber-600 hover:bg-amber-50 transition-colors"
                                    title="Desactivar">
                                    <flux:icon icon="user-minus" class="size-4" />
                                </button>
                            </div>
                        </x-agro.table-cell>
                    </x-agro.table-row>
                @endforeach
            </x-agro.data-table>
            @if($entries->hasPages())
                <div class="mt-4">{{ $entries->links() }}</div>
            @endif
        @endif
    </x-agro.card>

</div>
