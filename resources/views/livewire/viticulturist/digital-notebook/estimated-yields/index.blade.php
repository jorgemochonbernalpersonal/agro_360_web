<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        :title="__('Rendimientos Estimados')"
        :description="__('Gestiona las estimaciones de rendimiento por plantación y campaña')"
    />

    {{-- Tabs --}}
    <x-agro.tabs
        :tabs="[
            'active'   => ['label' => __('Activas'),   'count' => $stats['active']],
            'inactive' => ['label' => __('Inactivas'), 'count' => $stats['inactive']],
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
                :placeholder="__('Buscar por parcela, variedad, notas...')"
                icon="magnifying-glass"
            />
        </div>

        <x-agro.filter-button modal="yield-filters" :count="$filterCount" />

        <div class="w-px h-8 bg-zinc-200 shrink-0"></div>

        <flux:button href="{{ roleRoute('viticulturist.digital-notebook.estimated-yields.create') }}" variant="primary" icon="plus">
            {{ __('Nueva Estimación') }}
        </flux:button>

    </div>

    {{-- Chips de filtros activos --}}
    @if($search || $selectedCampaign || $filterStatus)
        <div class="flex flex-wrap items-center gap-2">
            @if($search)
                <x-agro.filter-chip icon="magnifying-glass" :label="'&quot;' . $search . '&quot;'" wireRemove="$set('search', '')" />
            @endif
            @if($filterStatus)
                @php $statusLabels = ['draft' => __('Borrador'), 'confirmed' => __('Confirmada')]; @endphp
                <x-agro.filter-chip :label="$statusLabels[$filterStatus] ?? $filterStatus" wireRemove="$set('filterStatus', '')" />
            @endif
            <button wire:click="clearFilters" class="text-xs text-zinc-400 hover:text-zinc-600 transition-colors">
                {{ __('Limpiar todo') }}
            </button>
        </div>
    @endif

    {{-- Card grid --}}
    @if($estimatedYields->count() > 0)
        @php
            $statusMap = [
                'draft'    => ['label' => __('Borrador'),   'color' => null],
                'confirmed'=> ['label' => __('Confirmada'), 'color' => 'green'],
                'archived' => ['label' => __('Archivada'),  'color' => 'blue'],
            ];
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
                        <x-agro.card-item-header
                            icon="chart-bar"
                            :title="$yield->plotPlanting->plot->name ?? __('Sin parcela')"
                            :subtitle="$yield->plotPlanting->grapeVariety?->name ?? null"
                            iconBg="bg-agro-50"
                            iconColor="text-agro-600"
                        >
                            <flux:badge :color="$statusInfo['color']" size="sm">
                                {{ $statusInfo['label'] }}
                            </flux:badge>
                        </x-agro.card-item-header>
                    </x-slot:header>

                    {{-- Campaña + fecha --}}
                    <div class="flex items-center gap-2 mb-3">
                        <flux:icon icon="calendar-days" class="size-3.5 text-zinc-400 shrink-0" />
                        <span class="text-xs text-zinc-600">
                            {{ $yield->campaign->name ?? __('Sin campaña') }} · {{ $yield->estimation_date->format('d/m/Y') }}
                        </span>
                    </div>

                    {{-- Rendimientos --}}
                    <div class="bg-zinc-50 rounded-xl p-2.5 mb-3 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-zinc-500">{{ __('Estimado') }}</span>
                            <span class="text-xs font-bold text-zinc-900">
                                {{ number_format($yield->estimated_total_yield, 0) }} kg
                                <span class="font-normal text-zinc-400">({{ number_format($yield->estimated_yield_per_hectare, 0) }} kg/ha)</span>
                            </span>
                        </div>
                        @if($yield->hasActualYield())
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-zinc-500">{{ __('Real') }}</span>
                                <span class="text-xs font-bold text-green-700">
                                    {{ number_format($yield->actual_total_yield, 0) }} kg
                                    <span class="font-normal text-zinc-400">({{ number_format($yield->actual_yield_per_hectare, 0) }} kg/ha)</span>
                                </span>
                            </div>
                            @if($yield->variance_percentage !== null)
                                <div class="flex items-center justify-between border-t border-zinc-200 pt-1.5">
                                    <span class="text-xs text-zinc-500">{{ __('Diferencia') }}</span>
                                    <span class="text-xs font-bold {{ $yield->variance_percentage >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $yield->variance_percentage >= 0 ? '+' : '' }}{{ number_format($yield->variance_percentage, 1) }}%
                                    </span>
                                </div>
                            @endif
                        @else
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-zinc-500">{{ __('Real') }}</span>
                                <span class="text-xs text-zinc-400 italic">{{ __('Sin datos reales') }}</span>
                            </div>
                        @endif
                    </div>

                    <x-slot:footer>
                        <div class="flex items-center justify-end gap-1">
                            <x-agro.action-button
                                variant="edit"
                                href="{{ roleRoute('viticulturist.digital-notebook.estimated-yields.edit', $yield->id) }}"
                                :title="__('Editar')"
                            />
                            <x-agro.action-button
                                variant="{{ $yield->active ? 'deactivate' : 'activate' }}"
                                wire:click="toggleActive({{ $yield->id }})"
                                :title="$yield->active ? __('Desactivar') : __('Activar')"
                            />
                        </div>
                    </x-slot:footer>
                </x-agro.card>
            @endforeach
        </div>

        <x-agro.pagination :paginator="$estimatedYields" />

    @else
        <x-agro.empty-state
            icon="chart-bar"
            :message="__('No hay estimaciones registradas')"
            :description="($search || $selectedCampaign || $filterStatus) ? __('No se encontraron estimaciones con los filtros seleccionados.') : __('Comienza creando tu primera estimación de rendimiento.')"
        >
            @if($search || $selectedCampaign || $filterStatus)
                <x-slot:action>
                    <flux:button wire:click="clearFilters" variant="outline" icon="x-mark">{{ __('Limpiar filtros') }}</flux:button>
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
                    <h3 class="text-base font-semibold text-zinc-900">{{ __('Filtros') }}</h3>
                </div>
                <flux:button x-on:click="$dispatch('close-modal', 'yield-filters')" variant="ghost" size="sm" icon="x-mark" />
            </div>
        </div>

        <div class="px-6 py-5 space-y-4">
            <flux:field>
                <flux:label>{{ __('Campaña') }}</flux:label>
                <flux:select wire:model.live="selectedCampaign">
                    <option value="">{{ __('Todas las campañas') }}</option>
                    @foreach($campaigns as $campaign)
                        <option value="{{ $campaign->id }}">{{ $campaign->name }} ({{ $campaign->year }})</option>
                    @endforeach
                </flux:select>
            </flux:field>
            <flux:field>
                <flux:label>{{ __('Estado') }}</flux:label>
                <flux:select wire:model.live="filterStatus">
                    <option value="">{{ __('Todos los estados') }}</option>
                    <option value="draft">{{ __('Borrador') }}</option>
                    <option value="confirmed">{{ __('Confirmada') }}</option>
                </flux:select>
            </flux:field>
        </div>

        <div class="px-6 py-4 bg-zinc-50 border-t border-zinc-200 flex items-center justify-between rounded-b-2xl">
            <button wire:click="clearFilters" x-on:click="$dispatch('close-modal', 'yield-filters')"
                    class="text-sm text-zinc-500 hover:text-zinc-700 transition-colors">
                {{ __('Limpiar filtros') }}
            </button>
            <flux:button x-on:click="$dispatch('close-modal', 'yield-filters')" variant="primary" size="sm">
                {{ __('Aplicar') }}
            </flux:button>
        </div>
    </x-agro.modal>

</div>
