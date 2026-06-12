<div class="space-y-6 animate-fade-in">

    {{-- Header --}}
    <x-agro.page-header
        title="{{ __('Actividades de Campo') }}"
        :description="$isViticulturistOnly
            ? 'Registro consolidado de todas tus actividades agrícolas'
            : 'Registro de actividades agrícolas de tus viticultores vinculados (solo lectura)'"
    />

    {{-- KPIs --}}
    <x-agro.stats-section key="field-activities" :columns="3">
        <x-agro.stat-card
            :label="__('Total actividades')"
            :value="$stats['total']"
            icon="clipboard-document-list"
            color="zinc"
        />
        <x-agro.stat-card
            :label="__('Vendimias')"
            :value="$stats['harvest']"
            icon="sparkles"
            color="agro"
        />
        <x-agro.stat-card
            :label="__('Fitosanitarios')"
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

        @if(auth()->user()->hasWineryAccess())
        <flux:button variant="ghost" icon="chart-bar" href="{{ roleRoute('harvest-summary.index') }}" wire:navigate size="sm">
            Cuadro de mando
        </flux:button>
        @endif
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
                class="text-xs text-zinc-400 hover:text-zinc-600 transition-colors">{{ __('Limpiar todo') }}</button>
        </div>
    @endif

    {{-- Loading skeleton --}}
    <x-agro.loading-grid target="viticulturistFilter, activityTypeFilter, campaignFilter, plotFilter, gotoPage, previousPage, nextPage" :count="6" :cols="3" />

    {{-- Grid de cards --}}
    <div wire:loading.remove wire:target="viticulturistFilter, activityTypeFilter, campaignFilter, plotFilter, gotoPage, previousPage, nextPage">
        @if($activities->isEmpty())
            <x-agro.empty-state
                icon="clipboard-document-list"
                title="{{ __('Sin actividades registradas') }}"
                :description="$isViticulturistOnly
                    ? 'Aún no has registrado actividades en tu cuaderno de campo.'
                    : 'Tus viticultores vinculados aún no han registrado actividades que coincidan con los filtros aplicados.'"
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
                            <x-agro.card-item-header
                                icon="clipboard-document-list"
                                :title="$act->viticulturist?->name ?? '—'"
                                :subtitle="$act->activity_date?->format('d/m/Y') ?? '—'"
                                :iconBg="$tc['bg']"
                                :iconColor="$tc['icon']"
                                size="md"
                                radius="xl"
                            >
                                <flux:badge color="{{ $tc['badge'] }}" size="sm">{{ $tl }}</flux:badge>
                            </x-agro.card-item-header>
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
    <x-agro.filter-modal
        name="field-activities-filters"
        :hasActiveFilters="$filterCount > 0"
        clearAction="$set('viticulturistFilter', ''); $set('activityTypeFilter', ''); $set('campaignFilter', ''); $set('plotFilter', '')"
    >
        @if(!$isViticulturistOnly)
        <div>
            <x-agro.field-label>{{ __('Viticultor') }}</x-agro.field-label>
            <flux:select wire:model.live="viticulturistFilter">
                <option value="">{{ __('Todos') }}</option>
                @foreach($linkedViticulturists as $v)
                    <option value="{{ $v->id }}">{{ $v->name }}</option>
                @endforeach
            </flux:select>
        </div>
        @endif
        <div>
            <x-agro.field-label>{{ __('Tipo de actividad') }}</x-agro.field-label>
            <flux:select wire:model.live="activityTypeFilter">
                <option value="">{{ __('Todos los tipos') }}</option>
                @foreach($activityTypes as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </flux:select>
        </div>
        <div>
            <x-agro.field-label>{{ __('Campaña') }}</x-agro.field-label>
            <flux:select wire:model.live="campaignFilter">
                <option value="">{{ __('Todas') }}</option>
                @foreach($campaigns as $c)
                    <option value="{{ $c->id }}">{{ $c->year }}</option>
                @endforeach
            </flux:select>
        </div>
        <div>
            <x-agro.field-label>{{ __('Parcela') }}</x-agro.field-label>
            <flux:select wire:model.live="plotFilter">
                <option value="">{{ __('Todas') }}</option>
                @foreach($plots as $plot)
                    <option value="{{ $plot->id }}">{{ $plot->name }}</option>
                @endforeach
            </flux:select>
        </div>
    </x-agro.filter-modal>

</div>

