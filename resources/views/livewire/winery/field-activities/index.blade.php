<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Actividades de Campo"
        description="Registro de actividades agrícolas de tus viticultores vinculados (solo lectura)."
    />

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

        {{-- Filtros --}}
        <button x-on:click="$dispatch('open-modal', 'field-activities-filters')"
            class="relative inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm font-medium text-zinc-700 hover:bg-zinc-50 shadow-sm transition-colors">
            <flux:icon icon="adjustments-horizontal" class="size-4 text-zinc-500" />
            Filtros
            @if($filterCount > 0)
                <span class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-agro-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center leading-none">
                    {{ $filterCount }}
                </span>
            @endif
        </button>

        {{-- Separador --}}
        <div class="w-px h-8 bg-zinc-200 shrink-0"></div>

        {{-- Navegación --}}
        <flux:button variant="ghost" icon="chart-bar" href="{{ roleRoute('harvest-summary.index') }}" wire:navigate size="sm">
            Cuadro de mando
        </flux:button>
    </div>

    {{-- Chips de filtros activos --}}
    @if($filterCount > 0)
        <div class="flex flex-wrap items-center gap-2">
            @if($viticulturistFilter)
                @php $viticLabel = $linkedViticulturists->firstWhere('id', $viticulturistFilter)?->name ?? $viticulturistFilter; @endphp
                <span class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                    <flux:icon icon="user" class="size-3" />
                    {{ $viticLabel }}
                    <button wire:click="$set('viticulturistFilter', '')" class="ml-0.5 p-0.5 rounded-full hover:bg-agro-200 transition-colors">
                        <flux:icon icon="x-mark" class="size-3" />
                    </button>
                </span>
            @endif
            @if($activityTypeFilter)
                @php $typeLabel = $activityTypes[$activityTypeFilter] ?? $activityTypeFilter; @endphp
                <span class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                    <flux:icon icon="tag" class="size-3" />
                    {{ $typeLabel }}
                    <button wire:click="$set('activityTypeFilter', '')" class="ml-0.5 p-0.5 rounded-full hover:bg-agro-200 transition-colors">
                        <flux:icon icon="x-mark" class="size-3" />
                    </button>
                </span>
            @endif
            @if($campaignFilter)
                @php $campLabel = $campaigns->firstWhere('id', $campaignFilter)?->year ?? $campaignFilter; @endphp
                <span class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                    <flux:icon icon="calendar" class="size-3" />
                    Campaña: {{ $campLabel }}
                    <button wire:click="$set('campaignFilter', '')" class="ml-0.5 p-0.5 rounded-full hover:bg-agro-200 transition-colors">
                        <flux:icon icon="x-mark" class="size-3" />
                    </button>
                </span>
            @endif
            @if($plotFilter)
                @php $plotLabel = $plots->firstWhere('id', $plotFilter)?->name ?? $plotFilter; @endphp
                <span class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                    <flux:icon icon="map" class="size-3" />
                    {{ $plotLabel }}
                    <button wire:click="$set('plotFilter', '')" class="ml-0.5 p-0.5 rounded-full hover:bg-agro-200 transition-colors">
                        <flux:icon icon="x-mark" class="size-3" />
                    </button>
                </span>
            @endif
            <button wire:click="$set('viticulturistFilter', ''); $set('activityTypeFilter', ''); $set('campaignFilter', ''); $set('plotFilter', '')"
                class="text-xs text-zinc-400 hover:text-zinc-600 transition-colors">
                Limpiar todo
            </button>
        </div>
    @endif

    {{-- Skeleton --}}
    <div wire:loading wire:target="viticulturistFilter, activityTypeFilter, campaignFilter, plotFilter, gotoPage, previousPage, nextPage">
        <x-agro.card>
            <div class="space-y-3">
                @for($i = 0; $i < 8; $i++)
                    <div class="h-10 bg-zinc-100 rounded-lg animate-pulse"></div>
                @endfor
            </div>
        </x-agro.card>
    </div>

    {{-- Contenido --}}
    <div wire:loading.remove wire:target="viticulturistFilter, activityTypeFilter, campaignFilter, plotFilter, gotoPage, previousPage, nextPage">
        @if($activities->isEmpty())
            <x-agro.empty-state
                icon="clipboard-document-list"
                title="Sin actividades registradas"
                description="Tus viticultores vinculados aún no han registrado actividades que coincidan con los filtros aplicados."
            />
        @else
            <x-agro.card>
                <x-agro.data-table :headers="['Viticultor', 'Tipo', 'Parcela / Plantación', 'Campaña', 'Fecha', 'Condiciones', 'Notas']">
                    @foreach($activities as $act)
                        <x-agro.table-row wire:key="act-{{ $act->id }}">

                            <x-agro.table-cell>
                                <span class="font-medium text-zinc-900">{{ $act->viticulturist?->name ?? '—' }}</span>
                            </x-agro.table-cell>

                            <x-agro.table-cell>
                                @php
                                    $typeColors = [
                                        'harvest'       => 'agro',
                                        'phytosanitary' => 'amber',
                                        'fertilization' => 'blue',
                                        'irrigation'    => 'blue',
                                        'pruning'       => 'violet',
                                        'cultural'      => 'zinc',
                                        'observation'   => 'zinc',
                                        'phenology'     => 'violet',
                                        'post_harvest'  => 'zinc',
                                    ];
                                    $tc = $typeColors[$act->activity_type] ?? 'zinc';
                                    $tl = $activityTypes[$act->activity_type] ?? $act->activity_type;
                                @endphp
                                <x-agro.status-badge :color="$tc" :label="$tl" />
                            </x-agro.table-cell>

                            <x-agro.table-cell>
                                <div class="font-medium text-zinc-900">{{ $act->plot?->name ?? '—' }}</div>
                                @if($act->plotPlanting?->grapeVariety)
                                    <div class="text-xs text-zinc-400">{{ $act->plotPlanting->grapeVariety->name }}</div>
                                @endif
                            </x-agro.table-cell>

                            <x-agro.table-cell>
                                <span class="text-zinc-600">{{ $act->campaign?->year ?? '—' }}</span>
                            </x-agro.table-cell>

                            <x-agro.table-cell>
                                <span class="text-zinc-700">{{ $act->activity_date?->format('d/m/Y') ?? '—' }}</span>
                            </x-agro.table-cell>

                            <x-agro.table-cell>
                                @if($act->weather_conditions || $act->temperature)
                                    <div class="text-sm text-zinc-600">
                                        @if($act->weather_conditions) {{ $act->weather_conditions }} @endif
                                        @if($act->temperature) · {{ $act->temperature }}°C @endif
                                    </div>
                                @else
                                    <span class="text-zinc-300">—</span>
                                @endif
                            </x-agro.table-cell>

                            <x-agro.table-cell>
                                @if($act->notes)
                                    <span class="text-xs text-zinc-500 line-clamp-2">{{ $act->notes }}</span>
                                @else
                                    <span class="text-zinc-300">—</span>
                                @endif
                            </x-agro.table-cell>

                        </x-agro.table-row>
                    @endforeach
                </x-agro.data-table>
            </x-agro.card>

            <x-agro.pagination :paginator="$activities" />
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
