<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Declaraciones de Vendimia"
        description="Declaraciones oficiales de cosecha ante CCAA / DO (Reglamento UE 2018/273)"
        icon="document-arrow-up"
    >
        <x-slot:actions>
            <flux:button href="{{ route('viticulturist.harvest-declarations.create') }}" variant="primary" icon="plus">
                Nueva Declaración
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <x-agro.stat-card label="Borradores"  :value="$stats['draft']"     color="zinc" icon="document" />
        <x-agro.stat-card label="Presentadas" :value="$stats['submitted']" color="blue" icon="paper-airplane" />
        <x-agro.stat-card label="Aceptadas"   :value="$stats['accepted']"  color="green" icon="check-circle" />
        <x-agro.stat-card label="Rechazadas"  :value="$stats['rejected']"  color="red" icon="x-circle" />
    </div>

    {{-- Filtros --}}
    <div class="flex items-center gap-3 flex-wrap">
        <flux:select wire:model.live="filterCampaign" class="w-48">
            <flux:select.option value="">Todas las campañas</flux:select.option>
            @foreach($campaigns as $c)
                <flux:select.option value="{{ $c->id }}">{{ $c->name }}</flux:select.option>
            @endforeach
        </flux:select>
        <flux:select wire:model.live="filterStatus" class="w-44">
            <flux:select.option value="">Todos los estados</flux:select.option>
            @foreach($statuses as $key => $label)
                <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
            @endforeach
        </flux:select>
        @if($filterCampaign || $filterStatus)
            <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">Limpiar</flux:button>
        @endif
    </div>

    {{-- Tabla --}}
    @if($entries->isEmpty())
        <x-agro.empty-state
            icon="document-arrow-up"
            title="Sin declaraciones"
            description="Crea la declaración de vendimia anual obligatoria ante la CCAA o Denominación de Origen correspondiente."
        >
            <x-slot:action>
                <flux:button href="{{ route('viticulturist.harvest-declarations.create') }}" variant="primary" icon="plus">
                    Nueva Declaración
                </flux:button>
            </x-slot:action>
        </x-agro.empty-state>
    @else
        <x-agro.data-table>
            <x-slot:header>
                <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">Año</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">Organismo</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">Sup. (ha)</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">Total (kg)</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">Estado</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">Presentación</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">Referencia</th>
                <th class="px-4 py-3"></th>
            </x-slot:header>
            @foreach($entries as $entry)
                <x-agro.table-row wire:key="hd-{{ $entry->id }}">
                    <x-agro.table-cell class="font-bold text-zinc-900">{{ $entry->declaration_year }}</x-agro.table-cell>
                    <x-agro.table-cell>{{ $entry->authority }}</x-agro.table-cell>
                    <x-agro.table-cell>{{ $entry->total_surface_ha ? number_format($entry->total_surface_ha, 4, ',', '.') : '—' }}</x-agro.table-cell>
                    <x-agro.table-cell class="font-semibold">
                        {{ $entry->total_kg ? number_format($entry->total_kg, 0, ',', '.') . ' kg' : '—' }}
                    </x-agro.table-cell>
                    <x-agro.table-cell>
                        <x-agro.status-badge :color="$entry->status_color">
                            {{ $entry->status_label }}
                        </x-agro.status-badge>
                    </x-agro.table-cell>
                    <x-agro.table-cell class="text-sm text-zinc-500">
                        {{ $entry->submission_date?->format('d/m/Y') ?? '—' }}
                    </x-agro.table-cell>
                    <x-agro.table-cell class="text-sm text-zinc-400">
                        {{ $entry->reference_number ?? '—' }}
                    </x-agro.table-cell>
                    <x-agro.table-cell class="text-right">
                        <div class="flex items-center justify-end gap-0.5">
                            <a href="{{ route('viticulturist.harvest-declarations.edit', $entry) }}"
                               class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors"
                               title="Editar">
                                <flux:icon icon="pencil-square" class="size-4" />
                            </a>
                            @if($entry->status === 'draft')
                                <button wire:click="markSubmitted({{ $entry->id }})"
                                        wire:confirm="¿Marcar como presentada ante {{ $entry->authority }}?"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-blue-600 hover:bg-blue-50 transition-colors"
                                        title="Marcar como presentada">
                                    <flux:icon icon="paper-airplane" class="size-4" />
                                </button>
                                <button wire:click="delete({{ $entry->id }})"
                                        wire:confirm="¿Eliminar este borrador?"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-red-600 hover:bg-red-50 transition-colors"
                                        title="Eliminar borrador">
                                    <flux:icon icon="trash" class="size-4" />
                                </button>
                            @elseif($entry->status === 'submitted')
                                <button wire:click="markAccepted({{ $entry->id }})"
                                        wire:confirm="¿Confirmar aceptación de la declaración?"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-green-600 hover:bg-green-50 transition-colors"
                                        title="Marcar como aceptada">
                                    <flux:icon icon="check-circle" class="size-4" />
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
