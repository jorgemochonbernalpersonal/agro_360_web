<div class="space-y-6 animate-fade-in">

<x-agro.page-header
    title="Subcontratación"
    description="Gestión de servicios subcontratados a empresas externas"
    icon="user-plus"
>
    <x-slot:actions>
        <flux:button href="{{ route('viticulturist.subcontracting.create') }}" variant="primary" icon="plus">
            Registrar Servicio
        </flux:button>
    </x-slot:actions>
</x-agro.page-header>

<x-agro.filter-bar>
    <x-agro.filter-select wire:model.live="filter_campaign_id" label="Campaña" placeholder="Todas las campañas">
        @foreach($campaigns as $campaign)
            <option value="{{ $campaign->id }}">Campaña {{ $campaign->year }}</option>
        @endforeach
    </x-agro.filter-select>

    <x-agro.filter-select wire:model.live="filter_plot_id" label="Parcela" placeholder="Todas las parcelas">
        @foreach($plots as $plot)
            <option value="{{ $plot->id }}">{{ $plot->name }}</option>
        @endforeach
    </x-agro.filter-select>

    <x-agro.filter-select wire:model.live="filter_service_type" label="Tipo" placeholder="Todos los tipos">
        @foreach($serviceTypes as $value => $label)
            <option value="{{ $value }}">{{ $label }}</option>
        @endforeach
    </x-agro.filter-select>

    <x-agro.filter-select wire:model.live="filter_invoiced" label="Facturación" placeholder="Todos">
        <option value="1">Facturado</option>
        <option value="0">Pendiente</option>
    </x-agro.filter-select>
</x-agro.filter-bar>

@if($records->total() > 0 && $totalAmount > 0)
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <x-agro.stat-card
            title="Total subcontratado"
            :value="number_format($totalAmount, 2) . ' €'"
            icon="banknotes"
            color="orange"
        />
        <x-agro.stat-card
            title="Servicios registrados"
            :value="$records->total()"
            icon="user-plus"
            color="zinc"
        />
    </div>
@endif

<x-agro.card>
    @if($records->count() === 0)
        <x-agro.empty-state
            icon="user-plus"
            title="Sin servicios subcontratados"
            description="Registra los servicios que contratas externamente: vendimia mecanizada, tratamientos, transporte, etc."
        >
            <x-slot:action>
                <flux:button href="{{ route('viticulturist.subcontracting.create') }}" variant="primary" icon="plus">
                    Registrar primer servicio
                </flux:button>
            </x-slot:action>
        </x-agro.empty-state>
    @else
        <x-agro.data-table :headers="['Fecha', 'Empresa', 'Tipo de Servicio', 'Parcela', 'Importe', 'Facturado', 'Acciones']">
            @foreach($records as $record)
                <x-agro.table-row>
                    <x-agro.table-cell>
                        <span class="text-sm text-zinc-600">{{ $record->service_date->format('d/m/Y') }}</span>
                        @if($record->service_end_date)
                            <span class="text-xs text-zinc-400 block">hasta {{ $record->service_end_date->format('d/m/Y') }}</span>
                        @endif
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <div>
                            <p class="text-sm font-medium text-zinc-900">{{ $record->company_name }}</p>
                            @if($record->contact_person)
                                <p class="text-xs text-zinc-500">{{ $record->contact_person }}</p>
                            @endif
                        </div>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <x-agro.status-badge :label="$record->service_type_label" color="orange" />
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <span class="text-sm text-zinc-600">{{ $record->plot?->name ?? '—' }}</span>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        @if($record->amount)
                            <span class="text-sm font-semibold text-orange-700">{{ number_format($record->amount, 2) }} €</span>
                        @else
                            <span class="text-zinc-400">—</span>
                        @endif
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <button wire:click="toggleInvoiced({{ $record->id }})" class="inline-flex">
                            @if($record->invoiced)
                                <x-agro.status-badge label="Facturado" color="green" />
                            @else
                                <x-agro.status-badge label="Pendiente" color="amber" />
                            @endif
                        </button>
                    </x-agro.table-cell>

                    <x-agro.table-cell align="right">
                        <div class="flex items-center justify-end gap-2">
                            <flux:button href="{{ route('viticulturist.subcontracting.edit', $record->id) }}" size="sm" variant="ghost" icon="pencil">Editar</flux:button>
                            <flux:button wire:click="delete({{ $record->id }})" wire:confirm="¿Eliminar este registro?" size="sm" variant="ghost" icon="trash">Eliminar</flux:button>
                        </div>
                    </x-agro.table-cell>
                </x-agro.table-row>
            @endforeach
        </x-agro.data-table>

        <div class="mt-4">{{ $records->links() }}</div>
    @endif
</x-agro.card>

</div>
