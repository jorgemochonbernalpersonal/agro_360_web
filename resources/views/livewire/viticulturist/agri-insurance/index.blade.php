<div class="space-y-6 animate-fade-in">

<x-agro.page-header
    title="Seguros Agrarios"
    description="Gestión de pólizas de seguro de tu explotación"
    icon="shield-exclamation"
>
    <x-slot:actions>
        <flux:button href="{{ route('viticulturist.agri-insurance.create') }}" variant="primary" icon="plus">
            Añadir Póliza
        </flux:button>
    </x-slot:actions>
</x-agro.page-header>

{{-- Alerta vencimientos --}}
@if($expiringSoon > 0)
    <flux:callout variant="warning" icon="exclamation-triangle">
        <strong>{{ $expiringSoon }} {{ $expiringSoon === 1 ? 'seguro vence' : 'seguros vencen' }}</strong> en los próximos 30 días. Revisa las renovaciones.
    </flux:callout>
@endif

{{-- Stats --}}
@if($insurances->total() > 0)
    <div x-data="{
        open: localStorage.getItem('agri-insurance-stats-open') !== 'false',
        toggle() {
            this.open = !this.open;
            localStorage.setItem('agri-insurance-stats-open', String(this.open));
        }
    }">
        <button
            @click="toggle()"
            class="flex items-center gap-1.5 text-[11px] font-semibold text-zinc-400 uppercase tracking-widest hover:text-zinc-600 transition-colors mb-3"
        >
            <span>Estadísticas</span>
            <flux:icon icon="chevron-up" class="size-3.5 transition-transform duration-200" ::class="{ 'rotate-180': !open }" />
        </button>
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-1"
        >
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <x-agro.stat-card
                title="Pólizas activas"
                :value="$activeCount"
                icon="shield-check"
                color="green"
            />
            <x-agro.stat-card
                title="Total pólizas"
                :value="$insurances->total()"
                icon="document-text"
                color="zinc"
            />
        </div>
        </div>
    </div>
@endif

{{-- Filtros --}}
<x-agro.filter-bar>
    <x-agro.filter-select wire:model.live="filter_status" label="Estado" placeholder="Todos los estados">
        @foreach($statuses as $value => $label)
            <option value="{{ $value }}">{{ $label }}</option>
        @endforeach
    </x-agro.filter-select>

    <x-agro.filter-select wire:model.live="filter_coverage_type" label="Cobertura" placeholder="Todos los tipos">
        @foreach($coverageTypes as $value => $label)
            <option value="{{ $value }}">{{ $label }}</option>
        @endforeach
    </x-agro.filter-select>
</x-agro.filter-bar>

{{-- Tabla --}}
<x-agro.card>
    @if($insurances->count() === 0)
        <x-agro.empty-state
            icon="shield-exclamation"
            title="Sin seguros registrados"
            description="Registra las pólizas de seguro de tu explotación: pedrisco, heladas, multirriesgo y más."
        >
            <x-slot:action>
                <flux:button href="{{ route('viticulturist.agri-insurance.create') }}" variant="primary" icon="plus">
                    Añadir primera póliza
                </flux:button>
            </x-slot:action>
        </x-agro.empty-state>
    @else
        <x-agro.data-table :headers="['Aseguradora', 'Póliza', 'Cobertura', 'Vigencia', 'Prima', 'Estado', 'Acciones']">
            @foreach($insurances as $insurance)
                <x-agro.table-row>
                    <x-agro.table-cell>
                        <div>
                            <p class="text-sm font-medium text-zinc-900">{{ $insurance->insurance_company }}</p>
                            @if($insurance->agent_name)
                                <p class="text-xs text-zinc-500">{{ $insurance->agent_name }}</p>
                            @endif
                        </div>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <span class="text-sm text-zinc-600">{{ $insurance->policy_number ?? '—' }}</span>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <x-agro.status-badge :label="$insurance->coverage_type_label" color="blue" />
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <div class="text-sm text-zinc-600">
                            <span>{{ $insurance->start_date->format('d/m/Y') }}</span>
                            <span class="text-zinc-400 mx-1">→</span>
                            <span class="{{ $insurance->isExpiringSoon() ? 'text-amber-600 font-medium' : '' }}">
                                {{ $insurance->end_date->format('d/m/Y') }}
                            </span>
                            @if($insurance->isExpiringSoon())
                                <span class="block text-xs text-amber-600">Vence pronto</span>
                            @endif
                        </div>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        @if($insurance->premium)
                            <span class="text-sm font-semibold text-zinc-700">{{ number_format($insurance->premium, 2) }} €</span>
                            @if($insurance->subsidy_amount)
                                <span class="text-xs text-green-600 block">-{{ number_format($insurance->subsidy_amount, 2) }} € subv.</span>
                            @endif
                        @else
                            <span class="text-zinc-400">—</span>
                        @endif
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <x-agro.status-badge :label="$insurance->status_label" :color="$insurance->status_color" />
                    </x-agro.table-cell>

                    <x-agro.table-cell align="right">
                        <div class="flex items-center justify-end gap-2">
                            <flux:button href="{{ route('viticulturist.agri-insurance.edit', $insurance->id) }}" size="sm" variant="ghost" icon="pencil">Editar</flux:button>
                            <flux:button wire:click="delete({{ $insurance->id }})" wire:confirm="¿Eliminar esta póliza?" size="sm" variant="ghost" icon="trash">Eliminar</flux:button>
                        </div>
                    </x-agro.table-cell>
                </x-agro.table-row>
            @endforeach
        </x-agro.data-table>

        <div class="mt-4">{{ $insurances->links() }}</div>
    @endif
</x-agro.card>

</div>

