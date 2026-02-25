<div class="space-y-6 animate-fade-in">

<x-agro.page-header
    title="Fenología"
    description="Registro de estadios fenológicos por plantación y campaña"
    icon="sun"
>
    @unless($filter_planting_id)
        <x-slot:actions>
            <flux:button href="{{ route('viticulturist.phenology.create') }}" variant="primary" icon="plus">
                Registrar Estadio
            </flux:button>
        </x-slot:actions>
    @endunless
</x-agro.page-header>

{{-- Contexto de plantación cuando se filtra por ella --}}
@if($filter_planting_id && $filteredPlanting)
    <div class="flex items-center justify-between px-4 py-3 bg-zinc-50 border border-zinc-200 rounded-xl">
        <div class="flex items-center gap-3">
            <flux:icon icon="scissors" class="size-4 text-zinc-400 shrink-0" />
            <div>
                <span class="text-sm font-medium text-zinc-900">{{ $filteredPlanting->plot->name }}</span>
                <span class="text-sm text-zinc-500"> — {{ $filteredPlanting->grapeVariety->name ?? $filteredPlanting->name ?? 'Sin nombre' }}</span>
            </div>
        </div>
        <flux:button href="{{ route('plots.plantings.index') }}" variant="ghost" size="sm" icon="arrow-left">
            Volver a plantaciones
        </flux:button>
    </div>
@endif

{{-- Filtro de campaña: solo en vista general --}}
@unless($filter_planting_id)
    <x-agro.filter-bar>
        <x-agro.filter-select wire:model.live="filter_campaign_id" label="Campaña" placeholder="Todas las campañas">
            @foreach($campaigns as $campaign)
                <option value="{{ $campaign->id }}">Campaña {{ $campaign->year }}</option>
            @endforeach
        </x-agro.filter-select>
    </x-agro.filter-bar>
@endunless

{{-- Tabla --}}
<x-agro.card>
    @if($observations->isEmpty())
        <x-agro.empty-state
            icon="sun"
            title="Sin observaciones fenológicas"
            description="Registra los estadios fenológicos de tus plantaciones para hacer seguimiento de la campaña."
        >
            @if($filter_planting_id)
                <x-slot:action>
                    <flux:button href="{{ route('viticulturist.phenology.create', ['planting_id' => $filter_planting_id]) }}" variant="primary" icon="plus">
                        Registrar primer estadio
                    </flux:button>
                </x-slot:action>
            @endif
        </x-agro.empty-state>
    @else
        <x-agro.data-table :headers="['Estadio', 'Fecha', 'Fuente', 'Confianza', 'GD Acum.', 'BBCH', 'Acciones']">
            @foreach($observations as $obs)
                @php
                    $eventColors = [
                        'budbreak' => 'green', 'shoot_growth' => 'green',
                        'flowering' => 'amber', 'fruit_set' => 'amber',
                        'veraison' => 'purple', 'pre_harvest' => 'orange',
                        'harvest' => 'red',
                    ];
                @endphp
                <x-agro.table-row>
                    <x-agro.table-cell>
                        <x-agro.status-badge
                            :label="$obs->event_label"
                            :color="$eventColors[$obs->event] ?? 'blue'"
                        />
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        {{ $obs->obs_date->format('d/m/Y') }}
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <span class="text-sm text-zinc-600">{{ $obs->source_label }}</span>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        @php $color = $obs->confidence >= 80 ? 'text-green-700' : ($obs->confidence >= 50 ? 'text-amber-700' : 'text-red-700'); @endphp
                        <span class="text-sm font-medium {{ $color }}">{{ $obs->confidence }}%</span>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        {{ $obs->degree_days_accumulated ? number_format($obs->degree_days_accumulated, 0) . ' GD' : '—' }}
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        @if($obs->bbch_code)
                            <code class="text-xs bg-zinc-100 px-1.5 py-0.5 rounded">{{ $obs->bbch_code }}</code>
                        @else
                            <span class="text-zinc-400">—</span>
                        @endif
                    </x-agro.table-cell>

                    <x-agro.table-cell align="right">
                        <div class="flex items-center justify-end gap-2">
                            <flux:button href="{{ route('viticulturist.phenology.edit', $obs->id) }}" size="sm" variant="ghost" icon="pencil">Editar</flux:button>
                            <flux:button wire:click="delete({{ $obs->id }})" wire:confirm="¿Eliminar esta observación?" size="sm" variant="ghost" icon="trash">Eliminar</flux:button>
                        </div>
                    </x-agro.table-cell>
                </x-agro.table-row>
            @endforeach
        </x-agro.data-table>
    @endif
</x-agro.card>

</div>
