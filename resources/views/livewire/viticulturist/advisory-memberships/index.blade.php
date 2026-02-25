<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Asesores Técnicos"
        subtitle="Registro de asesores fitosanitarios y técnicos (obligatorio RD 1311/2012)"
        icon="user-group"
    >
        <x-slot:actions>
            <flux:button variant="primary" icon="plus" wire:click="openCreate">Nuevo Asesor</flux:button>
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
            />
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
                                <span class="text-sm block">📞 {{ $entry->phone }}</span>
                            @endif
                            @if($entry->email)
                                <span class="text-sm block text-zinc-500">{{ $entry->email }}</span>
                            @endif
                        </x-agro.table-cell>
                        <x-agro.table-cell>{{ $entry->campaign?->name ?? 'Permanente' }}</x-agro.table-cell>
                        <x-agro.table-cell align="right">
                            <div class="flex items-center justify-end gap-2">
                                <flux:button size="sm" variant="ghost" icon="pencil" wire:click="openEdit({{ $entry->id }})">Editar</flux:button>
                                <flux:button size="sm" variant="ghost" icon="user-minus" wire:click="deactivate({{ $entry->id }})" wire:confirm="¿Desactivar este asesor?">Desactivar</flux:button>
                            </div>
                        </x-agro.table-cell>
                    </x-agro.table-row>
                @endforeach
            </x-agro.data-table>
            <div class="mt-4">{{ $entries->links() }}</div>
        @endif
    </x-agro.card>

    <flux:modal wire:model="showModal" class="max-w-xl">
        <div class="p-6 space-y-6">
            <h2 class="text-lg font-semibold text-zinc-900">
                {{ $editingId ? 'Editar Asesor' : 'Nuevo Asesor Técnico' }}
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:field class="md:col-span-2">
                    <flux:label required>Nombre del asesor</flux:label>
                    <flux:input wire:model="advisor_name" type="text" placeholder="Nombre completo" />
                    <flux:error name="advisor_name" />
                </flux:field>

                <flux:field>
                    <flux:label required>Nº Colegiado / Licencia</flux:label>
                    <flux:input wire:model="license_number" type="text" placeholder="Nº colegiado" />
                    <flux:error name="license_number" />
                </flux:field>
                <flux:field>
                    <flux:label required>Especialidad</flux:label>
                    <flux:select wire:model="specialty">
                        @foreach($specialties as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="specialty" />
                </flux:field>

                <flux:field class="md:col-span-2">
                    <flux:label>Empresa / Asesoría</flux:label>
                    <flux:input wire:model="company_name" type="text" placeholder="Nombre de la empresa" />
                    <flux:error name="company_name" />
                </flux:field>

                <flux:field>
                    <flux:label>Teléfono</flux:label>
                    <flux:input wire:model="phone" type="tel" placeholder="+34 000 000 000" />
                    <flux:error name="phone" />
                </flux:field>
                <flux:field>
                    <flux:label>Email</flux:label>
                    <flux:input wire:model="email" type="email" placeholder="asesor@ejemplo.com" />
                    <flux:error name="email" />
                </flux:field>

                <flux:field class="md:col-span-2">
                    <flux:label>Vincular a campaña específica</flux:label>
                    <flux:select wire:model="campaign_id">
                        <option value="">Permanente (todas las campañas)</option>
                        @foreach($campaigns as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:description>Deja vacío si el asesor trabaja de forma permanente</flux:description>
                    <flux:error name="campaign_id" />
                </flux:field>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-zinc-200">
                <flux:button variant="ghost" wire:click="$set('showModal', false)">Cancelar</flux:button>
                <flux:button variant="primary" wire:click="save">
                    {{ $editingId ? 'Actualizar' : 'Registrar Asesor' }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
