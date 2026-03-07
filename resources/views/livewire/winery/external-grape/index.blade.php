<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Uva / Mosto / Vino Externo"
        description="Partidas de uva, mosto o vino a granel de proveedores externos."
    >
        <x-slot:actions>
            <flux:button variant="primary" icon="plus" href="{{ route('winery.external-grape.create') }}" wire:navigate>
                Nueva partida
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
        <x-agro.stat-card
            label="Partidas disponibles"
            :value="$stats['partidas']"
            icon="archive-box"
            color="agro"
        />
        <x-agro.stat-card
            label="Kg disponibles"
            :value="$stats['available_kg'] ? number_format($stats['available_kg'], 0) . ' kg' : '—'"
            icon="scale"
            color="agro"
        />
        <x-agro.stat-card
            label="Kg total en stock"
            :value="$stats['total_kg'] ? number_format($stats['total_kg'], 0) . ' kg' : '—'"
            icon="archive-box-arrow-down"
            color="zinc"
        />
    </div>

    {{-- Filtros --}}
    <x-agro.filter-bar :active-count="collect([$typeFilter, $statusFilter, $vintageFilter, $colorFilter])->filter()->count()">
        <x-agro.filter-select wire:model.live="typeFilter" label="Tipo">
            <option value="">Todos</option>
            @foreach($types as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </x-agro.filter-select>
        <x-agro.filter-select wire:model.live="colorFilter" label="Color">
            <option value="">Todos</option>
            @foreach($colors as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </x-agro.filter-select>
        <x-agro.filter-select wire:model.live="vintageFilter" label="Añada">
            <option value="">Todas</option>
            @foreach($vintages as $year)
                <option value="{{ $year }}">{{ $year }}</option>
            @endforeach
        </x-agro.filter-select>
        <x-agro.filter-select wire:model.live="statusFilter" label="Estado">
            <option value="">Todos</option>
            @foreach($statuses as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </x-agro.filter-select>
    </x-agro.filter-bar>

    {{-- Tabla --}}
    @if($grapes->isEmpty())
        <x-agro.empty-state
            icon="archive-box"
            title="Sin partidas registradas"
            description="Registra uva, mosto o vino a granel de proveedores externos para controlar tu stock."
        >
            <flux:button variant="primary" icon="plus" href="{{ route('winery.external-grape.create') }}" wire:navigate>
                Nueva partida
            </flux:button>
        </x-agro.empty-state>
    @else
        <x-agro.card>
            <x-agro.data-table :headers="['Tipo', 'Proveedor', 'Variedad / Color', 'Procedencia', 'Añada', 'Alcohol', 'Total kg', 'Disponible', 'Entrada', 'Contenedor', 'Estado']">
                @foreach($grapes as $grape)
                    <x-agro.table-row wire:key="grape-{{ $grape->id }}">

                        <x-agro.table-cell>
                            @php
                                $typeColor = match($grape->grape_type) {
                                    'grapes'    => 'agro',
                                    'must'      => 'amber',
                                    'bulk_wine' => 'violet',
                                    default     => 'zinc',
                                };
                            @endphp
                            <x-agro.status-badge :color="$typeColor" :label="$grape->getTypeLabel()" />
                        </x-agro.table-cell>

                        <x-agro.table-cell>
                            <span class="font-medium text-zinc-900">{{ $grape->supplier_name }}</span>
                            @if($grape->protection_level)
                                <div class="text-xs text-blue-600">{{ $grape->protection_level }}</div>
                            @endif
                        </x-agro.table-cell>

                        <x-agro.table-cell>
                            <div class="font-medium text-zinc-900">{{ $grape->grapeVariety?->name ?? '—' }}</div>
                            @if($grape->color)
                                <div class="text-xs text-zinc-400">{{ $grape->getColorLabel() }}</div>
                            @endif
                        </x-agro.table-cell>

                        <x-agro.table-cell>
                            <span class="text-zinc-600 text-sm">{{ $grape->geographic_origin ?? '—' }}</span>
                        </x-agro.table-cell>

                        <x-agro.table-cell>
                            <span class="text-zinc-700">{{ $grape->vintage_year ?? '—' }}</span>
                        </x-agro.table-cell>

                        <x-agro.table-cell>
                            @if($grape->alcohol_pct)
                                <span class="text-zinc-700 text-sm">{{ number_format($grape->alcohol_pct, 1) }}°</span>
                            @else
                                <span class="text-zinc-300">—</span>
                            @endif
                        </x-agro.table-cell>

                        <x-agro.table-cell>
                            <span class="font-medium text-zinc-900">{{ number_format($grape->total_weight_kg, 0) }} kg</span>
                        </x-agro.table-cell>

                        <x-agro.table-cell>
                            @php $avail = $grape->getAvailableWeightKg(); @endphp
                            <span class="{{ $avail > 0 ? 'text-agro-700 font-medium' : 'text-zinc-400' }}">
                                {{ number_format($avail, 0) }} kg
                            </span>
                        </x-agro.table-cell>

                        <x-agro.table-cell>
                            <span class="text-zinc-600 text-sm">{{ $grape->entry_date->format('d/m/Y') }}</span>
                        </x-agro.table-cell>

                        <x-agro.table-cell>
                            <span class="text-zinc-600 text-sm">{{ $grape->container?->name ?? '—' }}</span>
                        </x-agro.table-cell>

                        <x-agro.table-cell>
                            @php
                                $sc = match($grape->status) { 'available' => 'agro', 'used' => 'zinc', 'archived' => 'zinc', default => 'zinc' };
                                $sl = \App\Models\ExternalGrape::STATUSES[$grape->status] ?? $grape->status;
                            @endphp
                            <x-agro.status-badge :color="$sc" :label="$sl" />
                            @if($grape->expiration_date && $grape->expiration_date->isPast() && $grape->status === 'available')
                                <div class="text-xs text-red-500 mt-0.5">Caducado</div>
                            @endif
                        </x-agro.table-cell>

                    </x-agro.table-row>
                @endforeach
            </x-agro.data-table>
        </x-agro.card>

        <x-agro.pagination :paginator="$grapes" />
    @endif

</div>
