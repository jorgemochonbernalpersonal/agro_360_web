<div class="space-y-6 animate-fade-in">

    {{-- Header --}}
    <x-agro.page-header
        :title="__('Análisis de Suelo')"
        :description="__('Registro de análisis edafológicos: pH, materia orgánica, nutrientes y textura de tus parcelas')"
        icon="beaker"
    />

    {{-- Stats (colapsables) --}}
    <x-agro.stats-section key="soil-analyses" columns="4">
        <x-agro.stat-card
            :label="__('Total análisis')"
            :value="$stats['total']"
            icon="beaker"
            color="agro"
        />
        <x-agro.stat-card
            :label="__('Parcelas analizadas')"
            :value="$stats['plots_analyzed']"
            icon="map"
            color="blue"
        />
        <x-agro.stat-card
            :label="__('pH medio')"
            :value="$stats['avg_ph'] ?: '—'"
            :description="$stats['avg_ph_label']"
            icon="scale"
            :color="$stats['avg_ph_color']"
        />
        <x-agro.stat-card
            :label="__('MO media')"
            :value="$stats['avg_organic_matter'] ? $stats['avg_organic_matter'] . '%' : '—'"
            :description="__('Materia orgánica')"
            icon="sparkles"
            color="amber"
        />
    </x-agro.stats-section>

    {{-- Toolbar --}}
    @php
        $filterCount = (int) !empty($filterPlot) + (int) !empty($filterCampaign) + (int) !empty($filterTexture);
    @endphp
    <div class="flex items-center gap-3">

        {{-- Search --}}
        <x-agro.search-input wire:model.live.debounce.300ms="search" :placeholder="__('Buscar por laboratorio o notas...')" />

        {{-- Filtros --}}
        <x-agro.filter-button modal="soil-analyses-filters" :count="$filterCount" />

        {{-- Separador --}}
        <div class="w-px h-8 bg-zinc-200 shrink-0"></div>

        {{-- Nuevo Análisis --}}
        <flux:button href="{{ roleRoute('viticulturist.soil-analyses.create') }}" variant="primary" icon="plus">
            {{ __('Nuevo') }}
        </flux:button>

    </div>

    {{-- Chips de filtros activos --}}
    @if ($filterPlot || $filterCampaign || $filterTexture)
        <div class="flex flex-wrap items-center gap-2">
            @if ($filterPlot)
                <x-agro.filter-chip icon="map" :label="$plots->firstWhere('id', (int) $filterPlot)?->name ?? $filterPlot" wireRemove="$set('filterPlot', '')" />
            @endif
            @if ($filterCampaign)
                <x-agro.filter-chip icon="calendar" :label="$campaigns->firstWhere('id', (int) $filterCampaign)?->name ?? $filterCampaign" wireRemove="$set('filterCampaign', '')" />
            @endif
            @if ($filterTexture)
                <x-agro.filter-chip icon="beaker" :label="$textureClasses[$filterTexture] ?? $filterTexture" wireRemove="$set('filterTexture', '')" />
            @endif
            <button
                wire:click="clearFilters"
                class="text-xs text-zinc-400 hover:text-zinc-600 transition-colors"
            >
                {{ __('Limpiar todo') }}
            </button>
        </div>
    @endif

    {{-- Skeleton durante carga --}}
    <x-agro.loading-grid target="search, filterPlot, filterCampaign, filterTexture, nextPage, previousPage, gotoPage" />

    {{-- Grid de cards --}}
    <div
        wire:loading.remove
        wire:target="search, filterPlot, filterCampaign, filterTexture, nextPage, previousPage, gotoPage"
    >
        @if ($entries->isEmpty())
            <x-agro.empty-state
                icon="beaker"
                :title="__('Sin análisis de suelo')"
                :description="__('Registra los resultados de tus análisis edafológicos para llevar un control de la fertilidad de tus parcelas.')"
            >
                <x-slot:action>
                    <flux:button href="{{ roleRoute('viticulturist.soil-analyses.create') }}" variant="primary" icon="plus">
                        {{ __('Nuevo Análisis') }}
                    </flux:button>
                </x-slot:action>
            </x-agro.empty-state>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach ($entries as $entry)
                    @php
                        $delay = min($loop->index * 50, 300);
                        $phColor = $entry->ph_color;
                        $phBgClasses = match($phColor) {
                            'red'    => 'bg-red-100 text-red-600',
                            'amber'  => 'bg-amber-100 text-amber-600',
                            'green'  => 'bg-green-100 text-green-600',
                            'blue'   => 'bg-blue-100 text-blue-600',
                            'violet' => 'bg-violet-100 text-violet-600',
                            default  => 'bg-zinc-100 text-zinc-400',
                        };
                    @endphp

                    <x-agro.card
                        class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                        style="animation-delay: {{ $delay }}ms;"
                        wire:key="soil-{{ $entry->id }}"
                    >
                        {{-- Header --}}
                        <x-slot:header>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 {{ $phBgClasses }}">
                                    <flux:icon icon="beaker" class="size-5" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-zinc-900 truncate">{{ $entry->plot->name ?? '—' }}</h3>
                                    <p class="text-xs text-zinc-400">{{ $entry->analysis_date->format('d/m/Y') }}</p>
                                </div>
                                @if ($entry->campaign)
                                    <flux:badge color="zinc" size="sm" class="shrink-0">{{ $entry->campaign->year }}</flux:badge>
                                @endif
                            </div>
                        </x-slot:header>

                        {{-- Body --}}
                        <div class="flex-1 space-y-3">

                            {{-- pH --}}
                            <div class="bg-zinc-50 rounded-xl p-3 space-y-1">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-zinc-400">pH</span>
                                    @if ($entry->ph !== null)
                                        <span class="font-bold {{ match($phColor) {
                                            'red'    => 'text-red-600',
                                            'amber'  => 'text-amber-600',
                                            'green'  => 'text-green-600',
                                            'blue'   => 'text-blue-600',
                                            'violet' => 'text-violet-600',
                                            default  => 'text-zinc-600',
                                        } }}">
                                            {{ $entry->ph }} ({{ $entry->ph_label }})
                                        </span>
                                    @else
                                        <span class="text-zinc-300">—</span>
                                    @endif
                                </div>
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-zinc-400">{{ __('Materia orgánica') }}</span>
                                    <span class="font-medium text-zinc-600">{{ $entry->organic_matter !== null ? $entry->organic_matter . '%' : '—' }}</span>
                                </div>
                            </div>

                            {{-- N-P-K --}}
                            @if ($entry->nitrogen_total !== null || $entry->phosphorus !== null || $entry->potassium !== null)
                                <div class="flex items-center gap-2 text-xs">
                                    <span class="text-zinc-400">{{ __('N-P-K:') }}</span>
                                    <span class="font-medium text-zinc-600">
                                        {{ $entry->nitrogen_total ?? '—' }} /
                                        {{ $entry->phosphorus ?? '—' }} /
                                        {{ $entry->potassium ?? '—' }}
                                    </span>
                                    <span class="text-zinc-300">{{ __('mg/kg') }}</span>
                                </div>
                            @endif

                            {{-- Textura --}}
                            @if ($entry->texture_class)
                                <div class="flex items-center gap-2 text-xs text-zinc-600">
                                    <flux:icon icon="squares-2x2" class="size-3.5 text-zinc-400 shrink-0" />
                                    <span class="text-zinc-400">{{ __('Textura') }}:</span>
                                    <span class="font-medium">{{ $entry->texture_label }}</span>
                                </div>
                            @endif

                            {{-- Laboratorio --}}
                            @if ($entry->laboratory)
                                <div class="flex items-center gap-2 text-xs text-zinc-500">
                                    <flux:icon icon="building-office" class="size-3.5 text-zinc-400 shrink-0" />
                                    <span class="truncate">{{ $entry->laboratory }}</span>
                                </div>
                            @endif

                            {{-- Notas --}}
                            @if ($entry->notes)
                                <p class="text-xs text-zinc-400 italic leading-relaxed line-clamp-2">{{ $entry->notes }}</p>
                            @endif

                        </div>

                        {{-- Footer acciones --}}
                        <x-slot:footer>
                            <div class="flex items-center justify-end gap-0.5">
                                <x-agro.action-button
                                    variant="edit"
                                    href="{{ roleRoute('viticulturist.soil-analyses.edit', $entry) }}"
                                    :title="__('Editar')"
                                />
                                <x-agro.action-button
                                    variant="delete"
                                    wire:click="delete({{ $entry->id }})"
                                    wire:confirm="{{ __('¿Eliminar este análisis de suelo? Esta acción no se puede deshacer.') }}"
                                    :title="__('Eliminar')"
                                />
                            </div>
                        </x-slot:footer>
                    </x-agro.card>
                @endforeach
            </div>

            <div class="mt-6">
                <x-agro.pagination :paginator="$entries" />
            </div>
        @endif
    </div>

    {{-- Modal: Filtros --}}
    <x-agro.filter-modal name="soil-analyses-filters" :hasActiveFilters="$filterPlot || $filterCampaign || $filterTexture" clearAction="clearFilters">
        <div>
            <x-agro.field-label>{{ __('Parcela') }}</x-agro.field-label>
            <flux:select wire:model.live="filterPlot">
                <option value="">{{ __('Todas las parcelas') }}</option>
                @foreach ($plots as $plot)
                    <option value="{{ $plot->id }}">{{ $plot->name }}</option>
                @endforeach
            </flux:select>
        </div>

        <div>
            <x-agro.field-label>{{ __('Campaña') }}</x-agro.field-label>
            <flux:select wire:model.live="filterCampaign">
                <option value="">{{ __('Todas las campañas') }}</option>
                @foreach ($campaigns as $campaign)
                    <option value="{{ $campaign->id }}">{{ $campaign->name }}</option>
                @endforeach
            </flux:select>
        </div>

        <div>
            <x-agro.field-label>{{ __('Textura') }}</x-agro.field-label>
            <flux:select wire:model.live="filterTexture">
                <option value="">{{ __('Todas las texturas') }}</option>
                @foreach ($textureClasses as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </flux:select>
        </div>
    </x-agro.filter-modal>

</div>
