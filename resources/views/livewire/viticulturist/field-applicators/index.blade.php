<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Aplicadores Fitosanitarios"
        subtitle="Registro oficial de aplicadores con número ROPO (obligatorio PAC)"
        icon="user-group"
    >
        <x-slot:actions>
            <flux:button href="{{ route('viticulturist.field-applicators.create') }}" variant="primary" icon="plus">
                Añadir Aplicador
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    <flux:callout variant="info" icon="information-circle">
        Los aplicadores de productos fitosanitarios deben estar en posesión del <strong>carné ROPO</strong> (RD 1702/2011). Es obligatorio registrarlos en el cuaderno de campo para cumplimiento PAC.
    </flux:callout>

    <x-agro.card>
        @if($applicators->isEmpty())
            <x-agro.empty-state
                icon="user-group"
                title="Sin aplicadores registrados"
                description="Añade los aplicadores que realizan tratamientos fitosanitarios en tu explotación."
            >
                <x-slot:action>
                    <flux:button href="{{ route('viticulturist.field-applicators.create') }}" variant="primary" icon="plus">
                        Añadir Aplicador
                    </flux:button>
                </x-slot:action>
            </x-agro.empty-state>
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
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('viticulturist.field-applicators.edit', $applicator) }}"
                                   class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors"
                                   title="Editar">
                                    <flux:icon icon="pencil-square" class="size-4" />
                                </a>
                                <button
                                    wire:click="deactivate({{ $applicator->id }})"
                                    wire:confirm="¿Dar de baja este aplicador?"
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
