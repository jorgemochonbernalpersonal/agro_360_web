<div class="space-y-6 animate-fade-in">

    {{-- Header --}}
    <x-agro.page-header
        title="Actividades de Campo"
        description="Registro de actividades agrícolas de tus viticultores vinculados (solo lectura)"
    />

    {{-- KPIs --}}
    <x-agro.stats-section key="field-activities" :columns="3">
        <x-agro.stat-card
            label="Total actividades"
            :value="$stats['total']"
            icon="clipboard-document-list"
            color="zinc"
        />
        <x-agro.stat-card
            label="Vendimias"
            :value="$stats['harvest']"
            icon="sparkles"
            color="agro"
        />
        <x-agro.stat-card
            label="Fitosanitarios"
            :value="$stats['phyto']"
            icon="beaker"
            color="amber"
        />
    </x-agro.stats-section>

    {{-- Cuaderno access warning --}}
    @if($withoutCuadernoAccess->isNotEmpty())
        <div class="flex items-start gap-3 p-4 bg-amber-50 border border-amber-200 rounded-xl">
            <flux:icon icon="lock-closed" class="size-5 text-amber-600 shrink-0 mt-0.5" />
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-amber-800">
                    {{ $withoutCuadernoAccess->count() === 1 ? '1 viticultor' : $withoutCuadernoAccess->count() . ' viticultores' }}
                    sin acceso al cuaderno de campo
                </p>
                <p class="text-xs text-amber-700 mt-0.5">
                    {{ $withoutCuadernoAccess->pluck('name')->join(', ') }}
                    — Sus actividades no se muestran hasta que concedas acceso desde
                    <a href="{{ roleRoute('viticulturists.index') }}" wire:navigate class="font-medium underline">Mis Viticultores</a>.
                </p>
            </div>
        </div>
    @endif

    @php
        $filterCount = (int) !empty($viticulturistFilter) + (int) !empty($activityTypeFilter) + (int) !empty($campaignFilter) + (int) !empty($plotFilter);
    @endphp

    {{-- Toolbar --}}
    <div class="flex items-center gap-3">
        <x-agro.filter-button modal="field-activities-filters" :count="$filterCount" />

        <div class="w-px h-8 bg-zinc-200 shrink-0"></div>

        <flux:button variant="ghost" icon="chart-bar" href="{{ roleRoute('harvest-summary.index') }}" wire:navigate size="sm">
            Cuadro de mando
        </flux:button>
    </div>

    {{-- Chips de filtros activos --}}
    @if($filterCount > 0)
        <div class="flex flex-wrap items-center gap-2">
            @if($viticulturistFilter)
                @php $viticLabel = $linkedViticulturists->firstWhere('id', $viticulturistFilter)?->name ?? $viticulturistFilter; @endphp
                <x-agro.filter-chip icon="user" :label="$viticLabel" wireRemove="$set('viticulturistFilter', '')" />
            @endif
            @if($activityTypeFilter)
                @php $typeLabel = $activityTypes[$activityTypeFilter] ?? $activityTypeFilter; @endphp
                <x-agro.filter-chip icon="tag" :label="$typeLabel" wireRemove="$set('activityTypeFilter', '')" />
            @endif
            @if($campaignFilter)
                @php $campLabel = $campaigns->firstWhere('id', $campaignFilter)?->year ?? $campaignFilter; @endphp
                <x-agro.filter-chip icon="calendar" :label="'Campaña: ' . $campLabel" wireRemove="$set('campaignFilter', '')" />
            @endif
            @if($plotFilter)
                @php $plotLabel = $plots->firstWhere('id', $plotFilter)?->name ?? $plotFilter; @endphp
                <x-agro.filter-chip icon="map" :label="$plotLabel" wireRemove="$set('plotFilter', '')" />
            @endif
            <button wire:click="$set('viticulturistFilter', ''); $set('activityTypeFilter', ''); $set('campaignFilter', ''); $set('plotFilter', '')"
                class="text-xs text-zinc-400 hover:text-zinc-600 transition-colors">
                Limpiar todo
            </button>
        </div>
    @endif

    {{-- Loading skeleton --}}
    <x-agro.loading-grid target="viticulturistFilter, activityTypeFilter, campaignFilter, plotFilter, gotoPage, previousPage, nextPage" :count="6" :cols="3" />

    {{-- Grid de cards --}}
    <div wire:loading.remove wire:target="viticulturistFilter, activityTypeFilter, campaignFilter, plotFilter, gotoPage, previousPage, nextPage">
        @if($activities->isEmpty())
            <x-agro.empty-state
                icon="clipboard-document-list"
                title="Sin actividades registradas"
                description="Tus viticultores vinculados aún no han registrado actividades que coincidan con los filtros aplicados."
            />
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($activities as $act)
                    @php
                        $delay = min($loop->index * 50, 300);
                        $typeColors = [
                            'harvest'       => ['badge' => 'agro',   'bg' => 'bg-agro-100',    'icon' => 'text-agro-600'],
                            'phytosanitary' => ['badge' => 'amber',  'bg' => 'bg-amber-100',   'icon' => 'text-amber-600'],
                            'fertilization' => ['badge' => 'blue',   'bg' => 'bg-blue-100',    'icon' => 'text-blue-600'],
                            'irrigation'    => ['badge' => 'blue',   'bg' => 'bg-blue-100',    'icon' => 'text-blue-600'],
                            'pruning'       => ['badge' => 'violet', 'bg' => 'bg-violet-100',  'icon' => 'text-violet-600'],
                            'cultural'      => ['badge' => 'zinc',   'bg' => 'bg-zinc-100',    'icon' => 'text-zinc-500'],
                            'observation'   => ['badge' => 'zinc',   'bg' => 'bg-zinc-100',    'icon' => 'text-zinc-500'],
                            'phenology'     => ['badge' => 'violet', 'bg' => 'bg-violet-100',  'icon' => 'text-violet-600'],
                            'post_harvest'  => ['badge' => 'zinc',   'bg' => 'bg-zinc-100',    'icon' => 'text-zinc-500'],
                        ];
                        $tc = $typeColors[$act->activity_type] ?? $typeColors['cultural'];
                        $tl = $activityTypes[$act->activity_type] ?? $act->activity_type;
                    @endphp
                    <x-agro.card
                        class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                        style="animation-delay: {{ $delay }}ms;"
                        wire:key="act-{{ $act->id }}"
                    >
                        <x-slot:header>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 {{ $tc['bg'] }}">
                                    <flux:icon icon="clipboard-document-list" class="size-5 {{ $tc['icon'] }}" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-zinc-900 truncate">{{ $act->viticulturist?->name ?? '—' }}</h3>
                                    <p class="text-xs text-zinc-400">{{ $act->activity_date?->format('d/m/Y') ?? '—' }}</p>
                                </div>
                                <flux:badge color="{{ $tc['badge'] }}" size="sm" class="shrink-0">{{ $tl }}</flux:badge>
                            </div>
                        </x-slot:header>

                        <div class="flex-1 space-y-3">
                            {{-- Parcela --}}
                            @if($act->plot)
                                <div class="flex items-center gap-2 text-sm text-zinc-600">
                                    <flux:icon icon="map" class="size-4 text-zinc-400 shrink-0" />
                                    <div class="min-w-0">
                                        <span class="font-medium truncate">{{ $act->plot->name }}</span>
                                        @if($act->plotPlanting?->grapeVariety)
                                            <span class="text-xs text-zinc-400 ml-1">· {{ $act->plotPlanting->grapeVariety->name }}</span>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            {{-- Campaña --}}
                            @if($act->campaign)
                                <div class="flex items-center gap-2 text-sm text-zinc-600">
                                    <flux:icon icon="calendar" class="size-4 text-zinc-400 shrink-0" />
                                    <span class="text-xs">Campaña {{ $act->campaign->year }}</span>
                                </div>
                            @endif

                            {{-- Condiciones --}}
                            @if($act->weather_conditions || $act->temperature)
                                <div class="flex items-center gap-2 text-xs text-zinc-500 bg-zinc-50 rounded-lg px-3 py-2">
                                    <flux:icon icon="sun" class="size-4 text-zinc-400 shrink-0" />
                                    <span>
                                        @if($act->weather_conditions)
                                            {{ $act->weather_conditions }}
                                        @endif
                                        @if($act->temperature)
                                            · {{ $act->temperature }}°C
                                        @endif
                                    </span>
                                </div>
                            @endif

                            {{-- Notas --}}
                            @if($act->notes)
                                <p class="text-xs text-zinc-400 line-clamp-2">{{ $act->notes }}</p>
                            @endif
                        </div>
                    </x-agro.card>
                @endforeach
            </div>

            <div class="mt-6">
                <x-agro.pagination :paginator="$activities" />
            </div>
        @endif
    </div>

    {{-- Modal Filtros --}}
    <x-agro.modal name="field-activities-filters" maxWidth="sm">
        <div class="px-6 py-4 border-b border-zinc-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-agro-100 rounded-lg flex items-center justify-center">
                        <flux:icon icon="adjustments-horizontal" class="size-4 text-agro-600" />
                    </div>
                    <h3 class="text-base font-semibold text-zinc-900">Filtros</h3>
                </div>
                <flux:button x-on:click="$dispatch('close-modal', 'field-activities-filters')" variant="ghost" size="sm" icon="x-mark" />
            </div>
        </div>

        <div class="px-6 py-5 space-y-5">
            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Viticultor</label>
                <flux:select wire:model.live="viticulturistFilter">
                    <option value="">Todos</option>
                    @foreach($linkedViticulturists as $v)
                        <option value="{{ $v->id }}">{{ $v->name }}</option>
                    @endforeach
                </flux:select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Tipo de actividad</label>
                <flux:select wire:model.live="activityTypeFilter">
                    <option value="">Todos los tipos</option>
                    @foreach($activityTypes as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </flux:select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Campaña</label>
                <flux:select wire:model.live="campaignFilter">
                    <option value="">Todas</option>
                    @foreach($campaigns as $c)
                        <option value="{{ $c->id }}">{{ $c->year }}</option>
                    @endforeach
                </flux:select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Parcela</label>
                <flux:select wire:model.live="plotFilter">
                    <option value="">Todas</option>
                    @foreach($plots as $plot)
                        <option value="{{ $plot->id }}">{{ $plot->name }}</option>
                    @endforeach
                </flux:select>
            </div>
        </div>

        <div class="px-6 py-4 border-t border-zinc-200 flex items-center justify-between">
            @if($filterCount > 0)
                <button wire:click="$set('viticulturistFilter', ''); $set('activityTypeFilter', ''); $set('campaignFilter', ''); $set('plotFilter', '')"
                    class="text-sm text-zinc-400 hover:text-zinc-600 transition-colors">
                    Limpiar filtros
                </button>
            @else
                <span></span>
            @endif
            <flux:button x-on:click="$dispatch('close-modal', 'field-activities-filters')" variant="primary">
                Aplicar
            </flux:button>
        </div>
    </x-agro.modal>

</div>

