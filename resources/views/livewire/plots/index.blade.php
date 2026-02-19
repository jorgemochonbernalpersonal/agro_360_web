<div class="space-y-6 animate-fade-in" x-data="{ showPlotAuditModal: false, currentPlotId: null }"
     @open-plot-audit-modal.window="showPlotAuditModal = true; currentPlotId = $event.detail.plotId"
     @close-plot-audit-modal.window="showPlotAuditModal = false; currentPlotId = null">

    <x-agro.page-header
        title="Gestion de Parcelas"
        description="Administra y visualiza todas tus parcelas agricolas"
    >
        <x-slot:actions>
            @can('create', \App\Models\Plot::class)
                <flux:button href="{{ route('plots.create') }}" variant="primary" icon="plus">
                    Nueva Parcela
                </flux:button>
            @endcan

            <flux:button href="{{ route('plots.plantings.index') }}" variant="outline" icon="squares-2x2">
                Ver plantaciones
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Tabs --}}
    <x-agro.tabs
        :tabs="[
            'active' => ['label' => 'Activas', 'count' => $stats['active']],
            'inactive' => ['label' => 'Inactivas', 'count' => $stats['inactive']],
            'statistics' => ['label' => 'Estadisticas'],
        ]"
        :active="$currentTab"
        wireMethod="switchTab"
    />

            {{-- ACTIVE/INACTIVE TABS --}}
            @if($currentTab === 'active' || $currentTab === 'inactive')
                <!-- Filtros -->
                <x-agro.card>
                    <x-agro.filter-bar>
                        <x-agro.filter-input
                            wire:model.live.debounce.300ms="search"
                            placeholder="Buscar por nombre de parcela..."
                        />
                        <x-agro.filter-select wire:model.live="filterAutonomousCommunity">
                            <option value="">Todas las comunidades</option>
                            @foreach($this->autonomousCommunities as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </x-agro.filter-select>
                        @if($filterAutonomousCommunity)
                            <x-agro.filter-select wire:model.live="filterProvince">
                                <option value="">Todas las provincias</option>
                                @foreach($this->provinces as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </x-agro.filter-select>
                        @endif
                        @if($filterProvince)
                            <x-agro.filter-select wire:model.live="filterMunicipality">
                                <option value="">Todos los municipios</option>
                                @foreach($this->municipalities as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </x-agro.filter-select>
                        @endif
                        @if($search || $filterAutonomousCommunity || $filterProvince || $filterMunicipality)
                            <flux:button wire:click="$set('search', ''); $set('filterAutonomousCommunity', ''); $set('filterProvince', ''); $set('filterMunicipality', '')"
                                    variant="ghost" size="sm">
                                Limpiar Filtros
                            </flux:button>
                        @endif
                    </x-agro.filter-bar>
                </x-agro.card>

                <!-- Acciones Masivas para Municipio -->
                @if($filterAutonomousCommunity && $filterProvince && $filterMunicipality && $this->municipalityHasSigpacCodes)
                    <x-agro.card>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-lg bg-agro-50 flex items-center justify-center">
                                    <flux:icon icon="map" class="size-6 text-agro-600" />
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-zinc-900">
                                        Acciones para {{ $this->municipalities[$filterMunicipality] ?? 'Municipio' }}
                                    </h3>
                                    <p class="text-sm text-zinc-600">
                                        {{ $this->provinces[$filterProvince] ?? '' }}, {{ $this->autonomousCommunities[$filterAutonomousCommunity] ?? '' }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <!-- Boton Generar Todos los Mapas -->
                                <flux:button
                                    wire:click="generateAllMapsForMunicipality"
                                    wire:loading.attr="disabled"
                                    variant="primary"
                                    icon="plus"
                                >
                                    <span wire:loading.remove wire:target="generateAllMapsForMunicipality">Generar Todos los Mapas</span>
                                    <span wire:loading wire:target="generateAllMapsForMunicipality">Generando...</span>
                                </flux:button>

                                <!-- Boton Ver Todos los Mapas -->
                                @php
                                    // Obtener primera parcela del municipio para la URL
                                    $firstPlotForMap = \App\Models\Plot::forUser(auth()->user())
                                        ->where('municipality_id', $filterMunicipality)
                                        ->first();
                                @endphp

                                @if($firstPlotForMap)
                                    <flux:button
                                        href="{{ route('map', ['id' => $firstPlotForMap->id, 'municipality' => $filterMunicipality, 'return' => 'plots']) }}"
                                        variant="primary"
                                        icon="eye"
                                    >
                                        Ver Todos los Mapas
                                    </flux:button>
                                @endif
                            </div>
                        </div>
                    </x-agro.card>
                @endif

    {{-- Grid de Parcelas --}}
    @if($plots->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-6">
            @foreach($plots as $plot)
                @php
                    $wineryName = '-';
                    if ($plot->viticulturist && $plot->viticulturist->wineries->isNotEmpty()) {
                        $wineryName = $plot->viticulturist->wineries->first()->name;
                    }

                    $hasMap = \App\Models\MultipartPlotSigpac::where('plot_id', $plot->id)
                        ->whereNotNull('plot_geometry_id')
                        ->exists();
                @endphp

                <x-agro.card>
                    {{-- Header --}}
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex-1">
                            <h3 class="font-semibold text-zinc-900 text-lg">{{ $plot->name }}</h3>
                            <p class="text-xs text-zinc-500 mt-1">{{ ($plot->province ? $plot->province->name : '') . ($plot->municipality ? ', ' . $plot->municipality->name : '') }}</p>
                        </div>
                        <x-agro.status-badge :active="$plot->active" />
                    </div>

                    {{-- Informacion de Recintos SIGPAC --}}
                    <div class="mb-3">
                        @if($plot->sigpacCodes->isNotEmpty())
                            <p class="text-xs text-zinc-600">
                                <span class="font-semibold">{{ $plot->sigpacCodes->count() }}</span> recinto(s) SIGPAC
                            </p>
                        @else
                            <p class="text-xs text-zinc-400 italic">Sin recintos SIGPAC</p>
                        @endif
                        @if($plot->description)
                            <p class="text-xs text-zinc-500 mt-1">{{ \Illuminate\Support\Str::limit($plot->description, 60) }}</p>
                        @endif
                    </div>

                    {{-- Informacion de Gestion --}}
                    <div class="space-y-2 mb-4">
                        <div class="flex items-center gap-2 text-sm">
                            <flux:icon icon="building-office" class="size-4 text-zinc-400" />
                            <span class="text-zinc-600">{{ $wineryName }}</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <flux:icon icon="user" class="size-4 text-zinc-400" />
                            <span class="text-zinc-600">
                                {{ $plot->viticulturist?->name }}
                                @if($plot->viticulturist && $plot->viticulturist->id === auth()->id())
                                    <span class="text-agro-700 font-semibold">(Yo)</span>
                                @endif
                            </span>
                        </div>
                    </div>

                    {{-- Barra de Area --}}
                    <x-agro.progress-bar
                        :percentage="$plot->area && $maxPlotArea > 0 ? ($plot->area / $maxPlotArea) * 100 : 0"
                        label="Area"
                        :currentValue="$plot->area"
                        :maxValue="$maxPlotArea"
                        unit="ha"
                    />

                    {{-- Actions --}}
                    <div class="pt-3 mt-3 border-t border-zinc-100">
                        <div class="flex flex-wrap gap-1 justify-center">
                            {{-- Ver Parcela --}}
                            <x-agro.action-button
                                variant="view"
                                href="{{ route('plots.show', $plot) }}"
                            />

                            {{-- Ver/Generar Mapa --}}
                            @if($hasMap)
                                <x-agro.action-button
                                    variant="map"
                                    href="{{ route('map', ['id' => $plot->id, 'return' => 'plots']) }}"
                                />
                            @elseif($plot->sigpacCodes->isNotEmpty())
                                @can('update', $plot)
                                    <x-agro.action-button
                                        variant="generate"
                                        wireClick="generateMap(null, {{ $plot->id }})"
                                    />
                                @endcan
                            @endif

                            {{-- Historial --}}
                            <x-agro.action-button
                                variant="history"
                                @click="$dispatch('open-plot-audit-modal', { plotId: {{ $plot->id }} })"
                            />

                            @can('update', $plot)
                                {{-- Anadir Plantacion --}}
                                <x-agro.action-button
                                    variant="planting"
                                    href="{{ route('plots.plantings.create', $plot) }}"
                                />

                                {{-- Editar --}}
                                <x-agro.action-button
                                    variant="edit"
                                    href="{{ route('plots.edit', $plot) }}"
                                />

                                {{-- Activar/Desactivar --}}
                                <x-agro.action-button
                                    :variant="$plot->active ? 'deactivate' : 'activate'"
                                    wireClick="toggleActive({{ $plot->id }})"
                                />
                            @endcan
                        </div>
                    </div>
                </x-agro.card>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $plots->links() }}
        </div>
    @else
        <x-agro.empty-state
            message="No hay parcelas registradas"
            description="Comienza agregando tu primera parcela al sistema"
            icon="inbox"
        >
            @can('create', \App\Models\Plot::class)
                <x-slot name="action">
                    <flux:button href="{{ route('plots.create') }}" variant="primary" icon="plus">
                        Crear mi primera parcela
                    </flux:button>
                </x-slot>
            @endcan
        </x-agro.empty-state>
    @endif
    @endif

    {{-- STATISTICS TAB --}}
            @if($currentTab === 'statistics')
                <div class="space-y-6">
                    {{-- Filtro de Ano --}}
                    <div class="flex justify-end">
                        <flux:select wire:model.live="yearFilter" class="w-auto">
                            @for($year = now()->year; $year >= now()->year - 5; $year--)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endfor
                        </flux:select>
                    </div>

                    {{-- KPIs --}}
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <x-agro.stat-card
                            label="Superficie Total"
                            :value="number_format($advancedStats['totalSurface'] ?? 0, 2) . ' ha'"
                            description="Todas las parcelas"
                            icon="map"
                            color="agro"
                        />
                        <x-agro.stat-card
                            label="Superficie Elegible PAC"
                            :value="number_format($advancedStats['eligibleSurface'] ?? 0, 2) . ' ha'"
                            :description="number_format($advancedStats['eligibilityPercentage'] ?? 0, 1) . '% del total'"
                            icon="check-circle"
                            color="blue"
                        />
                        <x-agro.stat-card
                            label="Parcelas Activas"
                            :value="$stats['active']"
                            :description="'De ' . $stats['total'] . ' totales'"
                            icon="squares-2x2"
                            color="purple"
                        />
                        <x-agro.stat-card
                            label="Superficie Media"
                            :value="number_format($advancedStats['avgSurfacePerPlot'] ?? 0, 2) . ' ha'"
                            description="Por parcela"
                            icon="chart-bar"
                            color="orange"
                        />
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        {{-- Distribucion por Regimen de Tenencia --}}
                        <x-agro.card>
                            <h3 class="text-lg font-bold text-zinc-900 mb-4">Distribucion por Regimen de Tenencia</h3>
                            <div class="space-y-4">
                                @forelse(($advancedStats['tenureStats'] ?? []) as $regime => $data)
                                    @php
                                        $total = ($advancedStats['tenureStats'] ?? [])->sum('count');
                                        $percentage = $total > 0 ? ($data['count'] / $total) * 100 : 0;
                                        $regimeName = match($regime) {
                                            'owned' => 'Propiedad',
                                            'leased' => 'Arrendamiento',
                                            'shared' => 'Compartida',
                                            default => ucfirst($regime),
                                        };
                                    @endphp
                                    <div>
                                        <div class="flex justify-between mb-2">
                                            <span class="text-sm font-medium text-zinc-700">{{ $regimeName }}</span>
                                            <span class="text-sm font-bold text-zinc-900">{{ $data['count'] }} ({{ number_format($percentage, 1) }}%)</span>
                                        </div>
                                        <div class="w-full bg-zinc-200 rounded-full h-3">
                                            <div class="bg-agro-500 h-3 rounded-full" style="width: {{ $percentage }}%"></div>
                                        </div>
                                        <p class="text-xs text-zinc-500 mt-1">{{ number_format($data['surface'], 2) }} ha</p>
                                    </div>
                                @empty
                                    <p class="text-zinc-500 text-center py-4">No hay datos de regimen de tenencia</p>
                                @endforelse
                            </div>
                        </x-agro.card>

                        {{-- Estado de Parcelas --}}
                        <x-agro.card>
                            <h3 class="text-lg font-bold text-zinc-900 mb-4">Estado de Parcelas</h3>
                            <div class="space-y-4">
                                <div>
                                    <div class="flex justify-between mb-2">
                                        <span class="text-sm font-medium text-zinc-700">Bloqueadas</span>
                                        <span class="text-sm font-bold text-zinc-900">{{ $advancedStats['lockedPlots'] ?? 0 }}</span>
                                    </div>
                                    <div class="w-full bg-zinc-200 rounded-full h-3">
                                        @php
                                            $lockedPct = $stats['total'] > 0 ? (($advancedStats['lockedPlots'] ?? 0) / $stats['total']) * 100 : 0;
                                        @endphp
                                        <div class="bg-red-500 h-3 rounded-full" style="width: {{ $lockedPct }}%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between mb-2">
                                        <span class="text-sm font-medium text-zinc-700">Desbloqueadas</span>
                                        <span class="text-sm font-bold text-zinc-900">{{ $advancedStats['unlockedPlots'] ?? 0 }}</span>
                                    </div>
                                    <div class="w-full bg-zinc-200 rounded-full h-3">
                                        @php
                                            $unlockedPct = $stats['total'] > 0 ? (($advancedStats['unlockedPlots'] ?? 0) / $stats['total']) * 100 : 0;
                                        @endphp
                                        <div class="bg-green-500 h-3 rounded-full" style="width: {{ $unlockedPct }}%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between mb-2">
                                        <span class="text-sm font-medium text-zinc-700">Con SIGPAC</span>
                                        <span class="text-sm font-bold text-zinc-900">{{ $advancedStats['withSigpac'] ?? 0 }}</span>
                                    </div>
                                    <div class="w-full bg-zinc-200 rounded-full h-3">
                                        @php
                                            $sigpacPct = $stats['total'] > 0 ? (($advancedStats['withSigpac'] ?? 0) / $stats['total']) * 100 : 0;
                                        @endphp
                                        <div class="bg-blue-500 h-3 rounded-full" style="width: {{ $sigpacPct }}%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between mb-2">
                                        <span class="text-sm font-medium text-zinc-700">Con Plantaciones</span>
                                        <span class="text-sm font-bold text-zinc-900">{{ $advancedStats['withPlantings'] ?? 0 }}</span>
                                    </div>
                                    <div class="w-full bg-zinc-200 rounded-full h-3">
                                        @php
                                            $plantingsPct = $stats['total'] > 0 ? (($advancedStats['withPlantings'] ?? 0) / $stats['total']) * 100 : 0;
                                        @endphp
                                        <div class="bg-purple-500 h-3 rounded-full" style="width: {{ $plantingsPct }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </x-agro.card>
                    </div>

                    {{-- Top Provincias --}}
                    <x-agro.card>
                        <h3 class="text-lg font-bold text-zinc-900 mb-4">Top 10 Provincias por Superficie</h3>
                        <div class="space-y-3">
                            @forelse(($advancedStats['provinceStats'] ?? []) as $index => $province)
                                <div class="flex items-center justify-between p-3 bg-zinc-50 rounded-lg hover:bg-zinc-100 transition-colors">
                                    <div class="flex items-center gap-3">
                                        <span class="flex-shrink-0 w-8 h-8 rounded-full bg-agro-500 text-white flex items-center justify-center font-bold text-sm">
                                            {{ $index + 1 }}
                                        </span>
                                        <div>
                                            <p class="font-semibold text-zinc-900">{{ $province['province_name'] }}</p>
                                            <p class="text-xs text-zinc-500">{{ $province['count'] }} parcelas</p>
                                        </div>
                                    </div>
                                    <span class="font-bold text-agro-700">{{ number_format($province['surface'], 2) }} ha</span>
                                </div>
                            @empty
                                <p class="text-zinc-500 text-center py-4">No hay datos de provincias</p>
                            @endforelse
                        </div>
                    </x-agro.card>

                    {{-- Nuevas Parcelas --}}
                    <x-agro.card>
                        <h3 class="text-lg font-bold text-zinc-900 mb-4">Nuevas Parcelas (Ultimos 12 meses)</h3>
                        <div class="h-64 flex items-end justify-between gap-2">
                            @foreach(($advancedStats['newPlotsByMonth'] ?? []) as $month)
                                <div class="flex-1 flex flex-col items-center">
                                    <div class="w-full bg-agro-500 rounded-t-lg transition-all hover:bg-agro-700"
                                        style="height: {{ $month['count'] > 0 ? ($month['count'] / max(collect($advancedStats['newPlotsByMonth'] ?? [])->pluck('count')->max(), 1)) * 100 : 5 }}%"
                                        title="{{ $month['count'] }} parcelas"></div>
                                    <span class="text-xs text-zinc-600 mt-2">{{ $month['month'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </x-agro.card>
                </div>
            @endif

    {{-- Modal de Historial de Auditoria (dentro del div raiz) --}}
    {{-- Overlay --}}
        <div x-show="showPlotAuditModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black/50 transition-opacity z-40"
             @click="showPlotAuditModal = false"
             style="display: none;">
        </div>

        {{-- Modal --}}
        <div x-show="showPlotAuditModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="fixed inset-0 z-50 overflow-y-auto"
             style="display: none;">

            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-4xl">
                    {{-- Header --}}
                    <div class="bg-zinc-50 px-6 py-4 border-b border-zinc-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-zinc-900">
                                Historial de Auditoria
                            </h3>
                            <flux:button @click="showPlotAuditModal = false" variant="ghost" size="sm" icon="x-mark" />
                        </div>
                    </div>

                    {{-- Content --}}
                    <div class="px-6 py-4 max-h-[70vh] overflow-y-auto">
                        <div x-show="currentPlotId">
                            <template x-if="currentPlotId">
                                <div>
                                    @foreach($plots as $plot)
                                        <div x-show="currentPlotId == {{ $plot->id }}"
                                             x-transition:enter="transition ease-out duration-200"
                                             x-transition:enter-start="opacity-0"
                                             x-transition:enter-end="opacity-100"
                                             style="display: none;">
                                            @livewire('viticulturist.plots.plot-audit-history', ['plot' => $plot], key($plot->id))
                                        </div>
                                    @endforeach
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="bg-zinc-50 px-6 py-3 border-t border-zinc-200 flex justify-end">
                        <flux:button @click="showPlotAuditModal = false" variant="outline">
                            Cerrar
                        </flux:button>
                    </div>
                </div>
            </div>
        </div>
</div>
