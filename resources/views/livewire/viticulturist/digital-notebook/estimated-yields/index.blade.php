<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Rendimientos Estimados"
        description="Gestiona las estimaciones de rendimiento por plantación y campaña"
    />

    {{-- Tabs --}}
    <x-agro.tabs
        :tabs="[
            'active'   => ['label' => 'Activas',   'count' => $stats['active']],
            'inactive' => ['label' => 'Inactivas', 'count' => $stats['inactive']],
        ]"
        :active="$currentTab"
        wireMethod="switchTab"
    />

    {{-- Toolbar --}}
    @php
        $filterCount = (int)(!empty($selectedCampaign)) + (int)(!empty($filterStatus));
    @endphp

    <div class="flex items-center gap-3">

        <div class="flex-1">
            <flux:input
                wire:model.live.debounce.300ms="search"
                placeholder="Buscar por parcela, variedad, notas..."
                icon="magnifying-glass"
            />
        </div>

        <x-agro.filter-button modal="yield-filters" :count="$filterCount" />

        <div class="w-px h-8 bg-zinc-200 shrink-0"></div>

        <flux:button href="{{ roleRoute('viticulturist.digital-notebook.estimated-yields.create') }}" variant="primary" icon="plus">
            Nueva Estimación
        </flux:button>

    </div>

    {{-- Chips de filtros activos --}}
    @if($search || $selectedCampaign || $filterStatus)
        <div class="flex flex-wrap items-center gap-2">
            @if($search)
                <x-agro.filter-chip icon="magnifying-glass" :label="'&quot;' . $search . '&quot;'" wireRemove="$set('search', '')" />
            @endif
            @if($filterStatus)
                @php $statusLabels = ['draft' => 'Borrador', 'confirmed' => 'Confirmada']; @endphp
                <x-agro.filter-chip :label="$statusLabels[$filterStatus] ?? $filterStatus" wireRemove="$set('filterStatus', '')" />
            @endif
            <button wire:click="clearFilters" class="text-xs text-zinc-400 hover:text-zinc-600 transition-colors">
                Limpiar todo
            </button>
        </div>
    @endif

    {{-- Card grid --}}
    @if($estimatedYields->count() > 0)
        @php
            $statusMap = [
                'draft'    => ['label' => 'Borrador',   'color' => null],
                'confirmed'=> ['label' => 'Confirmada', 'color' => 'green'],
                'archived' => ['label' => 'Archivada',  'color' => 'blue'],
            ];
            $btnBase   = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors';
            $btnDanger = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-orange-500 hover:bg-orange-50 transition-colors';
            $btnGreen  = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-green-600 hover:bg-green-50 transition-colors';
        @endphp

        <div
            class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6"
            wire:loading.class="opacity-60 pointer-events-none"
            wire:target="search, selectedCampaign, filterStatus, clearFilters, switchTab"
        >
            @foreach($estimatedYields as $i => $yield)
                @php
                    $statusInfo = $statusMap[$yield->status] ?? ['label' => ucfirst($yield->status), 'color' => null];
                @endphp

                <x-agro.card
                    wire:key="yield-{{ $yield->id }}"
                    class="animate-fade-in-up hover:-translate-y-1"
                    style="animation-delay: {{ min($i * 50, 400) }}ms"
                >
                    <x-slot:header>
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 bg-agro-50 rounded-full flex items-center justify-center shrink-0">
                                <flux:icon icon="chart-bar" class="size-4 text-agro-600" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-zinc-900 text-sm truncate leading-tight">
                                    {{ $yield->plotPlanting->plot->name ?? 'Sin parcela' }}
                                </p>
                                @if($yield->plotPlanting->grapeVariety)
                                    <p class="text-xs text-zinc-400 leading-tight mt-0.5 truncate">
                                        {{ $yield->plotPlanting->grapeVariety->name }}
                                    </p>
                                @endif
                            </div>
                            <flux:badge :color="$statusInfo['color']" size="sm" class="shrink-0">
                                {{ $statusInfo['label'] }}
                            </flux:badge>
                        </div>
                    </x-slot:header>

                    {{-- Campaña + fecha --}}
                    <div class="flex items-center gap-2 mb-3">
                        <flux:icon icon="calendar-days" class="size-3.5 text-zinc-400 shrink-0" />
                        <span class="text-xs text-zinc-600">
                            {{ $yield->campaign->name ?? 'Sin campaña' }} · {{ $yield->estimation_date->format('d/m/Y') }}
                        </span>
                    </div>

                    {{-- Rendimientos --}}
                    <div class="bg-zinc-50 rounded-xl p-2.5 mb-3 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-zinc-500">Estimado</span>
                            <span class="text-xs font-bold text-zinc-900">
                                {{ number_format($yield->estimated_total_yield, 0) }} kg
                                <span class="font-normal text-zinc-400">({{ number_format($yield->estimated_yield_per_hectare, 0) }} kg/ha)</span>
                            </span>
                        </div>
                        @if($yield->hasActualYield())
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-zinc-500">Real</span>
                                <span class="text-xs font-bold text-green-700">
                                    {{ number_format($yield->actual_total_yield, 0) }} kg
                                    <span class="font-normal text-zinc-400">({{ number_format($yield->actual_yield_per_hectare, 0) }} kg/ha)</span>
                                </span>
                            </div>
                            @if($yield->variance_percentage !== null)
                                <div class="flex items-center justify-between border-t border-zinc-200 pt-1.5">
                                    <span class="text-xs text-zinc-500">Diferencia</span>
                                    <span class="text-xs font-bold {{ $yield->variance_percentage >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $yield->variance_percentage >= 0 ? '+' : '' }}{{ number_format($yield->variance_percentage, 1) }}%
                                    </span>
                                </div>
                            @endif
                        @else
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-zinc-500">Real</span>
                                <span class="text-xs text-zinc-400 italic">Sin datos reales</span>
                            </div>
                        @endif
                    </div>

                    <x-slot:footer>
                        <div class="flex items-center justify-end gap-1">
                            <a href="{{ roleRoute('viticulturist.digital-notebook.estimated-yields.edit', $yield->id) }}" class="{{ $btnBase }}" title="Editar">
                                <flux:icon icon="pencil-square" class="size-4" />
                            </a>
                            <button
                                wire:click="toggleActive({{ $yield->id }})"
                                class="{{ $yield->active ? $btnDanger : $btnGreen }}"
                                title="{{ $yield->active ? 'Desactivar' : 'Activar' }}"
                            >
                                <flux:icon icon="{{ $yield->active ? 'x-circle' : 'check-circle' }}" class="size-4" />
                            </button>
                        </div>
                    </x-slot:footer>
                </x-agro.card>
            @endforeach
        </div>

        @if($estimatedYields->hasPages())
            <div class="flex justify-center">{{ $estimatedYields->links() }}</div>
        @endif

    @else
        <x-agro.empty-state
            icon="chart-bar"
            message="No hay estimaciones registradas"
            :description="($search || $selectedCampaign || $filterStatus) ? 'No se encontraron estimaciones con los filtros seleccionados.' : 'Comienza creando tu primera estimación de rendimiento.'"
        >
            @if($search || $selectedCampaign || $filterStatus)
                <x-slot:action>
                    <flux:button wire:click="clearFilters" variant="outline" icon="x-mark">Limpiar filtros</flux:button>
                </x-slot:action>
            @endif
        </x-agro.empty-state>
    @endif

    {{-- Modal Filtros --}}
    <x-agro.modal name="yield-filters" maxWidth="sm">
        <div class="px-6 py-4 border-b border-zinc-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-agro-100 rounded-lg flex items-center justify-center">
                        <flux:icon icon="adjustments-horizontal" class="size-4 text-agro-600" />
                    </div>
                    <h3 class="text-base font-semibold text-zinc-900">Filtros</h3>
                </div>
                <flux:button x-on:click="$dispatch('close-modal', 'yield-filters')" variant="ghost" size="sm" icon="x-mark" />
            </div>
        </div>

        <div class="px-6 py-5 space-y-4">
            <flux:field>
                <flux:label>Campaña</flux:label>
                <flux:select wire:model.live="selectedCampaign">
                    <option value="">Todas las campañas</option>
                    @foreach($campaigns as $campaign)
                        <option value="{{ $campaign->id }}">{{ $campaign->name }} ({{ $campaign->year }})</option>
                    @endforeach
                </flux:select>
            </flux:field>
            <flux:field>
                <flux:label>Estado</flux:label>
                <flux:select wire:model.live="filterStatus">
                    <option value="">Todos los estados</option>
                    <option value="draft">Borrador</option>
                    <option value="confirmed">Confirmada</option>
                </flux:select>
            </flux:field>
        </div>

        <div class="px-6 py-4 bg-zinc-50 border-t border-zinc-200 flex items-center justify-between rounded-b-2xl">
            <button wire:click="clearFilters" x-on:click="$dispatch('close-modal', 'yield-filters')"
                    class="text-sm text-zinc-500 hover:text-zinc-700 transition-colors">
                Limpiar filtros
            </button>
            <flux:button x-on:click="$dispatch('close-modal', 'yield-filters')" variant="primary" size="sm">
                Aplicar
            </flux:button>
        </div>
    </x-agro.modal>

</div>
