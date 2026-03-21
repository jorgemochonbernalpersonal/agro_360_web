<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Gestión de Envases Fitosanitarios"
        description="Registro de entregas de envases vacíos en puntos SIGFITO / FIELD (RD 1311/2012)"
        icon="archive-box-x-mark"
    >
        <x-slot:actions>
            <flux:button href="{{ route('viticulturist.container-returns.create') }}" variant="primary" icon="plus">
                Registrar Entrega
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Tabs --}}
    <x-agro.tabs
        :tabs="[
            'active'   => ['label' => 'Activos',   'count' => $stats['active']],
            'archived' => ['label' => 'Archivados', 'count' => $stats['archived']],
        ]"
        :active="$currentTab"
        wireMethod="switchTab"
    />

    {{-- Filtros --}}
    <div class="flex items-center gap-3 flex-wrap">
        <flux:select wire:model.live="filterCampaign" class="w-48">
            <flux:select.option value="">Todas las campañas</flux:select.option>
            @foreach($campaigns as $c)
                <flux:select.option value="{{ $c->id }}">{{ $c->name }}</flux:select.option>
            @endforeach
        </flux:select>
        <flux:select wire:model.live="filterCollectionSystem" class="w-44">
            <flux:select.option value="">Todos los sistemas</flux:select.option>
            @foreach($collectionSystems as $key => $label)
                <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
            @endforeach
        </flux:select>
        @if($filterCampaign || $filterCollectionSystem)
            <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">Limpiar</flux:button>
        @endif
    </div>

    {{-- KPI --}}
    @if($stats['total_containers'] > 0)
        <x-agro.card>
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center shrink-0">
                    <flux:icon icon="archive-box-x-mark" class="w-6 h-6 text-green-600" />
                </div>
                <div>
                    <p class="text-sm text-zinc-500">Envases entregados en campaña</p>
                    <p class="text-2xl font-bold text-zinc-900">{{ number_format($stats['total_containers'], 0, ',', '.') }} envases</p>
                    <p class="text-xs text-zinc-400">Cumplimiento obligación SIGFITO / FIELD</p>
                </div>
            </div>
        </x-agro.card>
    @endif

    {{-- Tabla --}}
    @if($entries->isEmpty())
        <x-agro.empty-state
            icon="archive-box-x-mark"
            title="{{ $currentTab === 'active' ? 'Sin entregas registradas' : 'Sin registros archivados' }}"
            description="Registra cada entrega de envases vacíos en puntos de recogida autorizados."
        >
            @if($currentTab === 'active')
                <x-slot:action>
                    <flux:button href="{{ route('viticulturist.container-returns.create') }}" variant="primary" icon="plus">
                        Registrar Entrega
                    </flux:button>
                </x-slot:action>
            @endif
        </x-agro.empty-state>
    @else
        <x-agro.data-table>
            <x-slot:header>
                <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">Fecha</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">Producto</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">Tipo envase</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">Uds.</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">Sistema</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">Punto recogida</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">Albarán</th>
                <th class="px-4 py-3"></th>
            </x-slot:header>
            @foreach($entries as $entry)
                <x-agro.table-row wire:key="cr-{{ $entry->id }}">
                    <x-agro.table-cell>{{ $entry->date->format('d/m/Y') }}</x-agro.table-cell>
                    <x-agro.table-cell>
                        <div class="font-medium text-zinc-900">{{ $entry->product_name }}</div>
                        @if($entry->registration_number)
                            <div class="text-xs text-zinc-400">{{ $entry->registration_number }}</div>
                        @endif
                    </x-agro.table-cell>
                    <x-agro.table-cell>{{ $entry->container_type_label }}</x-agro.table-cell>
                    <x-agro.table-cell class="font-semibold">{{ $entry->containers_quantity }}</x-agro.table-cell>
                    <x-agro.table-cell>
                        <x-agro.status-badge :color="$entry->collection_system === 'sigfito' ? 'green' : ($entry->collection_system === 'field' ? 'blue' : 'zinc')">
                            {{ $entry->collection_system_label }}
                        </x-agro.status-badge>
                    </x-agro.table-cell>
                    <x-agro.table-cell>{{ $entry->collection_point }}</x-agro.table-cell>
                    <x-agro.table-cell class="text-zinc-400 text-sm">{{ $entry->transport_document ?? '—' }}</x-agro.table-cell>
                    <x-agro.table-cell class="text-right">
                        <div class="flex items-center justify-end gap-0.5">
                            @if($currentTab === 'active')
                                <a href="{{ route('viticulturist.container-returns.edit', $entry) }}"
                                   class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors"
                                   title="Editar">
                                    <flux:icon icon="pencil-square" class="size-4" />
                                </a>
                                <button wire:click="archive({{ $entry->id }})"
                                        wire:confirm="¿Archivar este registro?"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-amber-600 hover:bg-amber-50 transition-colors"
                                        title="Archivar">
                                    <flux:icon icon="archive-box" class="size-4" />
                                </button>
                            @else
                                <button wire:click="unarchive({{ $entry->id }})"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-agro-600 hover:bg-agro-50 transition-colors"
                                        title="Restaurar">
                                    <flux:icon icon="arrow-uturn-left" class="size-4" />
                                </button>
                                <button wire:click="delete({{ $entry->id }})"
                                        wire:confirm="¿Eliminar este registro permanentemente?"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-red-600 hover:bg-red-50 transition-colors"
                                        title="Eliminar">
                                    <flux:icon icon="trash" class="size-4" />
                                </button>
                            @endif
                        </div>
                    </x-agro.table-cell>
                </x-agro.table-row>
            @endforeach
        </x-agro.data-table>

        @if($entries->hasPages())
            <div class="mt-6">{{ $entries->links() }}</div>
        @endif
    @endif

</div>
