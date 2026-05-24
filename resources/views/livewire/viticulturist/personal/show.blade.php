<div class="space-y-6 animate-fade-in">
    <!-- Header -->
    <x-agro.page-header
        :title="$crew->name"
        :description="$crew->description ?? __('Detalles de la cuadrilla')"
    >
        <x-slot:actions>
            @can('update', $crew)
                <flux:button href="{{ roleRoute('viticulturist.personal.edit', $crew) }}" data-cy="edit-crew-button" variant="primary">
                    {{ __('Editar') }}
                </flux:button>
            @endcan
            <flux:button href="{{ roleRoute('viticulturist.personal.index') }}" data-cy="back-button" variant="outline">
                {{ __('Volver') }}
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
                <span class="font-semibold text-zinc-900 text-sm">{{ __('Estadísticas de la Cuadrilla') }}</span>
            </div>
        </x-slot:header>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4" data-cy="crew-stats-grid">
            <div class="text-center p-4 bg-agro-50 rounded-lg">
                <div class="text-3xl font-bold text-agro-700">{{ $stats['members_count'] }}</div>
                <div class="text-sm text-zinc-600 mt-1">{{ __('Miembros') }}</div>
            </div>
            <div class="text-center p-4 bg-green-50 rounded-lg">
                <div class="text-3xl font-bold text-green-600">{{ $stats['activities_count'] }}</div>
                <div class="text-sm text-zinc-600 mt-1">{{ __('Actividades') }}</div>
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
                    <span class="font-semibold text-zinc-900 text-sm">{{ __('Información General') }}</span>
                </div>
            </x-slot:header>
            <div class="space-y-4">
                <div>
                    <span class="text-sm font-semibold text-zinc-600">{{ __('Bodega:') }}</span>
                    <span class="ml-2 text-zinc-900">{{ $crew->winery->name ?? __('Sin bodega') }}</span>
                </div>
                <div>
                    <span class="text-sm font-semibold text-zinc-600">{{ __('Líder:') }}</span>
                    <span class="ml-2 text-zinc-900">{{ $crew->viticulturist->name }}</span>
                </div>
                @if($crew->description)
                <div>
                    <span class="text-sm font-semibold text-zinc-600">{{ __('Descripción:') }}</span>
                    <p class="mt-1 text-zinc-900">{{ $crew->description }}</p>
                </div>
                @endif
                <div>
                    <span class="text-sm font-semibold text-zinc-600">{{ __('Creada:') }}</span>
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
                    <span class="font-semibold text-zinc-900 text-sm">{{ __('Miembros de la Cuadrilla') }}</span>
                </div>
            </x-slot:header>
            <p class="text-xs text-zinc-500 mb-4">
                {{ __('Para añadir nuevos miembros, usa la pantalla de') }}
                <a href="{{ roleRoute('viticulturist.personal.index', ['viewMode' => 'personal']) }}" class="text-agro-700 underline">
                    {{ __('Equipos y Personal') }}
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
                                            <flux:badge size="sm">{{ __('Sin carnet') }}</flux:badge>
                                        @endif
                                    </div>
                                    <div class="text-sm text-zinc-500">{{ $member->viticulturist->email }}</div>
                                    @if($member->phytosanitary_license_number)
                                        <div class="text-xs text-zinc-600 mt-1">
                                            <span class="font-medium">{{ __('Carnet:') }}</span> {{ $member->phytosanitary_license_number }}
                                            @if($member->license_expiry_date)
                                                · <span class="font-medium">{{ __('Vence:') }}</span> {{ \Carbon\Carbon::parse($member->license_expiry_date)->format('d/m/Y') }}
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <flux:button wire:click="removeMember({{ $member->id }})" wire:confirm="{{ __('¿Estás seguro de remover este miembro?') }}" variant="ghost" size="sm" icon="x-mark" class="text-red-600 hover:bg-red-50" :title="__('Remover')" />
                        </div>
                    @endforeach
                @else
                    <x-agro.empty-state icon="users" :message="__('No hay miembros en esta cuadrilla')" />
                @endif
            </div>
        </x-agro.card>
    </div>

    <!-- Actividades Recientes -->
    @if($crew->activities->count() > 0)
    <x-agro.data-table :headers="[__('Fecha'), __('Tipo'), __('Parcela')]" :empty-message="__('Sin actividades')">
        <x-slot:header>
            <div class="flex items-center gap-2">
                <div class="p-1.5 rounded-lg bg-agro-50">
                    <flux:icon icon="clipboard-document-list" class="size-4 text-agro-600" />
                </div>
                <span class="font-semibold text-zinc-900 text-sm">{{ __('Actividades Recientes') }}</span>
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
