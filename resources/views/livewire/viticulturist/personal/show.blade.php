<div class="space-y-6 animate-fade-in">
    <!-- Header -->
    <x-agro.page-header
        :title="$crew->name"
        :description="$crew->description ?? 'Detalles de la cuadrilla'"
    >
        <x-slot:actions>
            @can('update', $crew)
                <flux:button href="{{ route('viticulturist.personal.edit', $crew) }}" data-cy="edit-crew-button" variant="primary">
                    Editar
                </flux:button>
            @endcan
            <flux:button href="{{ route('viticulturist.personal.index') }}" data-cy="back-button" variant="outline">
                Volver
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    <!-- Estadisticas -->
    <x-agro.card data-cy="crew-statistics">
        <x-slot:header>
            <div class="flex items-center gap-2">
                <div class="p-1.5 rounded-lg bg-agro-50">
                    <flux:icon icon="chart-bar" class="size-4 text-agro-600" />
                </div>
                <span class="font-semibold text-zinc-900 text-sm">Estadisticas de la Cuadrilla</span>
            </div>
        </x-slot:header>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4" data-cy="crew-stats-grid">
            <div class="text-center p-4 bg-agro-50 rounded-lg">
                <div class="text-3xl font-bold text-agro-700">{{ $stats['members_count'] }}</div>
                <div class="text-sm text-zinc-600 mt-1">Miembros</div>
            </div>
            <div class="text-center p-4 bg-green-50 rounded-lg">
                <div class="text-3xl font-bold text-green-600">{{ $stats['activities_count'] }}</div>
                <div class="text-sm text-zinc-600 mt-1">Actividades</div>
            </div>
        </div>
    </x-agro.card>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Informacion General -->
        <x-agro.card>
            <x-slot:header>
                <div class="flex items-center gap-2">
                    <div class="p-1.5 rounded-lg bg-agro-50">
                        <flux:icon icon="information-circle" class="size-4 text-agro-600" />
                    </div>
                    <span class="font-semibold text-zinc-900 text-sm">Informacion General</span>
                </div>
            </x-slot:header>
            <div class="space-y-4">
                <div>
                    <span class="text-sm font-semibold text-zinc-600">Bodega:</span>
                    <span class="ml-2 text-zinc-900">{{ $crew->winery->name ?? 'Sin bodega' }}</span>
                </div>
                <div>
                    <span class="text-sm font-semibold text-zinc-600">Lider:</span>
                    <span class="ml-2 text-zinc-900">{{ $crew->viticulturist->name }}</span>
                </div>
                @if($crew->description)
                <div>
                    <span class="text-sm font-semibold text-zinc-600">Descripcion:</span>
                    <p class="mt-1 text-zinc-900">{{ $crew->description }}</p>
                </div>
                @endif
                <div>
                    <span class="text-sm font-semibold text-zinc-600">Creada:</span>
                    <span class="ml-2 text-zinc-900">{{ $crew->created_at->format('d/m/Y H:i') }}</span>
                </div>
            </div>
        </x-agro.card>

        <!-- Gestion de Miembros -->
        <x-agro.card id="miembros" data-cy="crew-members-section">
            <x-slot:header>
                <div class="flex items-center gap-2">
                    <div class="p-1.5 rounded-lg bg-agro-50">
                        <flux:icon icon="users" class="size-4 text-agro-600" />
                    </div>
                    <span class="font-semibold text-zinc-900 text-sm">Miembros de la Cuadrilla</span>
                </div>
            </x-slot:header>
            <p class="text-xs text-zinc-500 mb-4">
                Para anadir nuevos miembros, usa la pantalla de
                <a href="{{ route('viticulturist.personal.index', ['viewMode' => 'personal']) }}" class="text-agro-700 underline">
                    Equipos y Personal
                </a>.
            </p>

            <!-- Lista de Miembros -->
            <div class="space-y-2">
                @if($crew->members->count() > 0)
                    @foreach($crew->members as $member)
                        <div class="flex items-center justify-between p-3 bg-white rounded-lg border border-zinc-200 hover:border-agro-500 transition-colors">
                            <div class="flex items-center gap-3 flex-1">
                                <div class="w-10 h-10 rounded-full bg-agro-500 flex items-center justify-center text-white font-bold text-sm">
                                    {{ substr($member->viticulturist->name, 0, 1) }}
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <div class="font-semibold text-zinc-900">{{ $member->viticulturist->name }}</div>
                                        @if($member->phytosanitary_license_number)
                                            @php
                                                $status = $member->license_status;
                                                $licColor = match($status) {
                                                    'Vigente'            => 'green',
                                                    'Proximo a caducar'  => 'yellow',
                                                    'Caducado'           => 'red',
                                                    default              => null,
                                                };
                                            @endphp
                                            <flux:badge :color="$licColor" size="sm">{{ $status }}</flux:badge>
                                        @else
                                            <flux:badge size="sm">Sin carnet</flux:badge>
                                        @endif
                                    </div>
                                    <div class="text-sm text-zinc-500">{{ $member->viticulturist->email }}</div>
                                    @if($member->phytosanitary_license_number)
                                        <div class="text-xs text-zinc-600 mt-1">
                                            <span class="font-medium">Carnet:</span> {{ $member->phytosanitary_license_number }}
                                            @if($member->license_expiry_date)
                                                · <span class="font-medium">Vence:</span> {{ \Carbon\Carbon::parse($member->license_expiry_date)->format('d/m/Y') }}
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <flux:button wire:click="removeMember({{ $member->id }})" wire:confirm="Estas seguro de remover este miembro?" variant="ghost" size="sm" icon="x-mark" class="text-red-600 hover:bg-red-50" title="Remover" />
                        </div>
                    @endforeach
                @else
                    <x-agro.empty-state icon="users" message="No hay miembros en esta cuadrilla" />
                @endif
            </div>
        </x-agro.card>
    </div>

    <!-- Actividades Recientes -->
    @if($crew->activities->count() > 0)
    <x-agro.data-table :headers="['Fecha', 'Tipo', 'Parcela']" empty-message="Sin actividades">
        <x-slot:header>
            <div class="flex items-center gap-2">
                <div class="p-1.5 rounded-lg bg-agro-50">
                    <flux:icon icon="clipboard-document-list" class="size-4 text-agro-600" />
                </div>
                <span class="font-semibold text-zinc-900 text-sm">Actividades Recientes</span>
            </div>
        </x-slot:header>
        @foreach($crew->activities->take(10) as $activity)
            <x-agro.table-row>
                <x-agro.table-cell>{{ $activity->activity_date->format('d/m/Y') }}</x-agro.table-cell>
                <x-agro.table-cell>
                    <flux:badge color="green" size="sm">{{ ucfirst($activity->activity_type) }}</flux:badge>
                </x-agro.table-cell>
                <x-agro.table-cell>{{ $activity->plot->name ?? 'N/A' }}</x-agro.table-cell>
            </x-agro.table-row>
        @endforeach
    </x-agro.data-table>
    @endif
</div>
