<div class="space-y-6 animate-fade-in">

<x-agro.page-header
    title="Seguros Agrarios"
    description="Gestión de pólizas de seguro de tu explotación"
    icon="shield-exclamation"
>
    <x-slot:actions>
        <flux:button href="{{ roleRoute('viticulturist.agri-insurance.create') }}" variant="primary" icon="plus">
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
    <x-agro.stats-section key="agri-insurance-stats">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <x-agro.stat-card
                label="Pólizas activas"
                :value="$activeCount"
                icon="shield-check"
                color="green"
            />
            <x-agro.stat-card
                label="Total pólizas"
                :value="$insurances->total()"
                icon="document-text"
                color="zinc"
            />
        </div>
    </x-agro.stats-section>
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

{{-- Cards --}}
<x-agro.loading-grid target="filter_status, filter_coverage_type" />
<div wire:loading.remove wire:target="filter_status, filter_coverage_type">
    @if($insurances->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($insurances as $insurance)
                @php $delay = min($loop->index * 50, 300); @endphp
                <x-agro.card
                    class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                    style="animation-delay: {{ $delay }}ms;"
                    wire:key="ins-{{ $insurance->id }}"
                >
                    <x-slot:header>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-violet-100 flex items-center justify-center shrink-0">
                                <flux:icon icon="shield-exclamation" class="size-5 text-violet-600" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-bold text-zinc-900 truncate">{{ $insurance->insurance_company }}</h3>
                                <p class="text-xs text-zinc-500">{{ $insurance->policy_number ?? 'Sin nº póliza' }}</p>
                            </div>
                            <x-agro.status-badge :label="$insurance->status_label" :color="$insurance->status_color" />
                        </div>
                    </x-slot:header>

                    <div class="flex-1 space-y-4">
                        <div class="grid grid-cols-2 gap-2">
                            <div class="bg-agro-50 rounded-xl p-3">
                                <p class="text-[10px] font-semibold text-agro-400 uppercase tracking-widest mb-0.5">Cobertura</p>
                                <p class="text-sm font-bold text-agro-700 leading-none">{{ $insurance->coverage_type_label }}</p>
                            </div>
                            <div class="bg-agro-50 rounded-xl p-3">
                                <p class="text-[10px] font-semibold text-agro-400 uppercase tracking-widest mb-0.5">Prima</p>
                                @if($insurance->premium)
                                    <p class="text-sm font-bold text-agro-700 leading-none font-mono">{{ number_format($insurance->premium, 2) }} €</p>
                                @else
                                    <p class="text-sm font-bold text-zinc-400 leading-none">—</p>
                                @endif
                            </div>
                        </div>

                        <div class="space-y-2 text-sm">
                            @if($insurance->agent_name)
                                <div class="flex items-center justify-between">
                                    <span class="text-zinc-400">Agente</span>
                                    <span class="text-zinc-700 font-medium truncate ml-2">{{ $insurance->agent_name }}</span>
                                </div>
                            @endif
                            <div class="flex items-center justify-between">
                                <span class="text-zinc-400">Vigencia</span>
                                <span class="text-zinc-700 font-medium text-xs">
                                    {{ $insurance->start_date->format('d/m/Y') }} → {{ $insurance->end_date->format('d/m/Y') }}
                                </span>
                            </div>
                            @if($insurance->isExpiringSoon())
                                <div class="flex items-center gap-1 text-xs text-amber-600 font-medium">
                                    <flux:icon icon="exclamation-triangle" class="size-3" />
                                    Vence pronto
                                </div>
                            @endif
                            @if($insurance->subsidy_amount)
                                <div class="flex items-center justify-between">
                                    <span class="text-zinc-400">Subvención</span>
                                    <span class="text-green-600 font-medium font-mono">-{{ number_format($insurance->subsidy_amount, 2) }} €</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <x-slot:footer>
                        <div class="flex items-center justify-end gap-0.5">
                            <x-agro.action-button
                                variant="edit"
                                href="{{ roleRoute('viticulturist.agri-insurance.edit', $insurance->id) }}"
                                icon="pencil"
                            />
                            <x-agro.action-button
                                variant="delete"
                                wire:click="delete({{ $insurance->id }})"
                                wire:confirm="¿Eliminar esta póliza?"
                            />
                        </div>
                    </x-slot:footer>
                </x-agro.card>
            @endforeach
        </div>
        <div class="mt-6">{{ $insurances->links() }}</div>
    @else
        <x-agro.empty-state
            icon="shield-exclamation"
            title="Sin seguros registrados"
            description="Registra las pólizas de seguro de tu explotación: pedrisco, heladas, multirriesgo y más."
        >
            <x-slot:action>
                <flux:button href="{{ roleRoute('viticulturist.agri-insurance.create') }}" variant="primary" icon="plus">
                    Añadir primera póliza
                </flux:button>
            </x-slot:action>
        </x-agro.empty-state>
    @endif
</div>

</div>
