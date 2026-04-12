<div class="space-y-6 animate-fade-in">

    {{-- Header --}}
    <x-agro.page-header
        title="Plan de Trabajos"
        description="Planifica y haz seguimiento de las tareas de tu explotación. Vincula cada trabajo con el cuaderno de campo cuando lo ejecutes."
        icon="clipboard-document-list"
    >
        <x-slot:actions>
            <flux:button href="{{ roleRoute('viticulturist.planned-works.create') }}" variant="primary" icon="plus">
                Nuevo Trabajo
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Stats (colapsables) --}}
    <div x-data="{
        open: localStorage.getItem('planned-works-stats-open') !== 'false',
        toggle() {
            this.open = !this.open;
            localStorage.setItem('planned-works-stats-open', String(this.open));
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
            <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
                <x-agro.stat-card
                    label="Pendientes"
                    :value="$stats['pending']"
                    icon="clock"
                    color="amber"
                />
                <x-agro.stat-card
                    label="Vencidas"
                    :value="$stats['overdue']"
                    description="Sin completar a fecha"
                    icon="exclamation-triangle"
                    color="red"
                />
                <x-agro.stat-card
                    label="Próximos 7 días"
                    :value="$stats['upcoming']"
                    icon="calendar-days"
                    color="blue"
                />
                <x-agro.stat-card
                    label="Completadas"
                    :value="$stats['completed']"
                    icon="check-circle"
                    color="agro"
                />
                <x-agro.stat-card
                    label="Canceladas"
                    :value="$stats['cancelled']"
                    icon="x-circle"
                    color="zinc"
                />
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <x-agro.tabs :tabs="[
        'pending'   => ['label' => 'Pendientes',  'count' => $stats['pending']],
        'overdue'   => ['label' => 'Vencidas',    'count' => $stats['overdue']],
        'completed' => ['label' => 'Completadas', 'count' => $stats['completed']],
        'cancelled' => ['label' => 'Canceladas',  'count' => $stats['cancelled']],
    ]" :active="$currentTab" wireMethod="switchTab" />

    {{-- Toolbar --}}
    @php
        $filterCount = (int) !empty($filterCategory) + (int) !empty($filterPriority) + (int) !empty($filterPlot) + (int) !empty($filterCampaign);
    @endphp
    <div class="flex items-center gap-3">

        {{-- Search --}}
        <div class="flex-1 relative">
            <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                <flux:icon icon="magnifying-glass" class="size-4 text-zinc-400" />
            </div>
            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                placeholder="Buscar por título o descripción..."
                class="w-full pl-9 pr-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm placeholder:text-zinc-400 shadow-sm focus:outline-none focus:ring-2 focus:ring-agro-500 focus:border-transparent transition"
            />
        </div>

        {{-- Filtros --}}
        <button
            x-on:click="$dispatch('open-modal', 'planned-works-filters')"
            class="relative inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm font-medium text-zinc-700 hover:bg-zinc-50 shadow-sm transition-colors"
        >
            <flux:icon icon="adjustments-horizontal" class="size-4 text-zinc-500" />
            Filtros
            @if ($filterCount > 0)
                <span class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-agro-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center leading-none">
                    {{ $filterCount }}
                </span>
            @endif
        </button>

        {{-- Separador --}}
        <div class="w-px h-8 bg-zinc-200 shrink-0"></div>

        {{-- Nuevo Trabajo --}}
        <flux:button href="{{ roleRoute('viticulturist.planned-works.create') }}" variant="primary" icon="plus">
            Nuevo
        </flux:button>
    </div>

    {{-- Chips de filtros activos --}}
    @if ($filterCategory || $filterPriority || $filterPlot || $filterCampaign)
        <div class="flex flex-wrap items-center gap-2">
            @if ($filterCategory)
                <span class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                    {{ \App\Models\PlannedWork::CATEGORIES[$filterCategory] ?? $filterCategory }}
                    <button wire:click="$set('filterCategory', '')" class="ml-0.5 p-0.5 rounded-full hover:bg-agro-200 transition-colors">
                        <flux:icon icon="x-mark" class="size-3" />
                    </button>
                </span>
            @endif
            @if ($filterPriority)
                <span class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                    {{ \App\Models\PlannedWork::PRIORITIES[$filterPriority] ?? $filterPriority }}
                    <button wire:click="$set('filterPriority', '')" class="ml-0.5 p-0.5 rounded-full hover:bg-agro-200 transition-colors">
                        <flux:icon icon="x-mark" class="size-3" />
                    </button>
                </span>
            @endif
            @if ($filterPlot)
                <span class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                    {{ $plots->find($filterPlot)?->name ?? 'Parcela' }}
                    <button wire:click="$set('filterPlot', '')" class="ml-0.5 p-0.5 rounded-full hover:bg-agro-200 transition-colors">
                        <flux:icon icon="x-mark" class="size-3" />
                    </button>
                </span>
            @endif
            @if ($filterCampaign)
                <span class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                    {{ $campaigns->find($filterCampaign)?->name ?? 'Campaña' }}
                    <button wire:click="$set('filterCampaign', '')" class="ml-0.5 p-0.5 rounded-full hover:bg-agro-200 transition-colors">
                        <flux:icon icon="x-mark" class="size-3" />
                    </button>
                </span>
            @endif
            <button
                wire:click="clearFilters"
                class="text-xs text-zinc-400 hover:text-zinc-600 transition-colors"
            >
                Limpiar todo
            </button>
        </div>
    @endif

    {{-- Skeleton durante carga --}}
    <div
        wire:loading
        wire:target="switchTab, search, filterCategory, filterPriority, filterPlot, filterCampaign, nextPage, previousPage, gotoPage"
    >
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @for ($i = 0; $i < 8; $i++)
                <x-agro.skeleton-card />
            @endfor
        </div>
    </div>

    {{-- Grid de cards --}}
    <div
        wire:loading.remove
        wire:target="switchTab, search, filterCategory, filterPriority, filterPlot, filterCampaign, nextPage, previousPage, gotoPage"
    >
        @if ($entries->isEmpty())
            <x-agro.empty-state
                icon="clipboard-document-list"
                title="{{ match($currentTab) {
                    'pending'   => 'Sin trabajos pendientes',
                    'overdue'   => 'Sin trabajos vencidos',
                    'completed' => 'Sin trabajos completados',
                    'cancelled' => 'Sin trabajos cancelados',
                } }}"
                description="{{ match($currentTab) {
                    'pending'   => 'Planifica tus próximas tareas: tratamientos, podas, riegos, vendimia y más.',
                    'overdue'   => 'No hay tareas pasadas sin completar.',
                    'completed' => 'Los trabajos que marques como completados aparecerán aquí.',
                    'cancelled' => 'Los trabajos que canceles aparecerán aquí.',
                } }}"
            >
                @if ($currentTab === 'pending')
                    <x-slot:action>
                        <flux:button href="{{ roleRoute('viticulturist.planned-works.create') }}" variant="primary" icon="plus">
                            Nuevo Trabajo
                        </flux:button>
                    </x-slot:action>
                @endif
            </x-agro.empty-state>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach ($entries as $entry)
                    @php
                        $delay = min($loop->index * 50, 300);
                        $categoryIcon = $entry->category_icon;
                        $isOverdue = $entry->is_overdue;
                        $iconBg = $isOverdue
                            ? 'bg-red-100 text-red-600'
                            : match($entry->priority) {
                                'urgente' => 'bg-red-100 text-red-600',
                                'alta'    => 'bg-amber-100 text-amber-600',
                                'media'   => 'bg-blue-100 text-blue-600',
                                default   => 'bg-zinc-100 text-zinc-500',
                            };
                    @endphp

                    <x-agro.card
                        class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                        style="animation-delay: {{ $delay }}ms;"
                        wire:key="pw-{{ $entry->id }}"
                    >
                        {{-- Header --}}
                        <x-slot:header>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 {{ $iconBg }}">
                                    <flux:icon :icon="$categoryIcon" class="size-5" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-zinc-900 truncate">{{ $entry->title }}</h3>
                                    <p class="text-xs text-zinc-400 truncate">{{ $entry->category_label }}</p>
                                </div>
                            </div>
                        </x-slot:header>

                        {{-- Body --}}
                        <div class="flex-1 space-y-3">

                            {{-- Fecha y parcela --}}
                            <div class="bg-zinc-50 rounded-xl p-3 space-y-1">
                                <div class="flex items-center gap-2 text-xs text-zinc-600">
                                    <flux:icon icon="calendar-days" class="size-3.5 text-zinc-400 shrink-0" />
                                    <span class="text-zinc-400">Fecha:</span>
                                    <span class="font-medium {{ $isOverdue ? 'text-red-600' : '' }}">
                                        {{ $entry->planned_date->format('d/m/Y') }}
                                        @if ($entry->planned_end_date)
                                            → {{ $entry->planned_end_date->format('d/m/Y') }}
                                        @endif
                                    </span>
                                </div>
                                @if ($entry->plot)
                                    <div class="flex items-center gap-2 text-xs text-zinc-600">
                                        <flux:icon icon="map-pin" class="size-3.5 text-zinc-400 shrink-0" />
                                        <span class="text-zinc-400">Parcela:</span>
                                        <span class="font-medium">{{ $entry->plot->name }}</span>
                                    </div>
                                @endif
                                @if ($entry->campaign)
                                    <div class="flex items-center gap-2 text-xs text-zinc-600">
                                        <flux:icon icon="flag" class="size-3.5 text-zinc-400 shrink-0" />
                                        <span class="text-zinc-400">Campaña:</span>
                                        <span class="font-medium">{{ $entry->campaign->name }}</span>
                                    </div>
                                @endif
                            </div>

                            {{-- Badges --}}
                            <div class="flex flex-wrap items-center gap-1.5">
                                <flux:badge color="{{ $entry->priority_color }}" size="sm">
                                    {{ $entry->priority_label }}
                                </flux:badge>
                                <flux:badge color="{{ $entry->status_color }}" size="sm">
                                    {{ $entry->status_label }}
                                </flux:badge>
                                @if ($isOverdue)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                                        <flux:icon icon="exclamation-triangle" class="size-3" />
                                        Vencida
                                    </span>
                                @endif
                                @if ($entry->is_linked)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                        <flux:icon icon="link" class="size-3" />
                                        Vinculada
                                    </span>
                                @endif
                            </div>

                            {{-- Descripción --}}
                            @if ($entry->description)
                                <p class="text-xs text-zinc-500 line-clamp-2" title="{{ $entry->description }}">
                                    {{ $entry->description }}
                                </p>
                            @endif

                        </div>

                        {{-- Footer acciones --}}
                        <x-slot:footer>
                            <div class="flex items-center justify-end gap-0.5">
                                @if (in_array($entry->status, ['pendiente', 'en_progreso']))
                                    @if ($entry->status === 'pendiente')
                                        <button
                                            wire:click="startWork({{ $entry->id }})"
                                            title="Iniciar"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-blue-600 hover:bg-blue-50 transition-colors"
                                        >
                                            <flux:icon icon="play" class="size-4" />
                                        </button>
                                    @endif
                                    <button
                                        wire:click="completeWork({{ $entry->id }})"
                                        wire:confirm="¿Marcar como completado?"
                                        title="Completar"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-green-600 hover:bg-green-50 transition-colors"
                                    >
                                        <flux:icon icon="check-circle" class="size-4" />
                                    </button>
                                    <a href="{{ roleRoute('viticulturist.planned-works.edit', $entry) }}"
                                       title="Editar"
                                       class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors">
                                        <flux:icon icon="pencil-square" class="size-4" />
                                    </a>
                                    <button
                                        wire:click="cancelWork({{ $entry->id }})"
                                        wire:confirm="¿Cancelar este trabajo?"
                                        title="Cancelar"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-amber-600 hover:bg-amber-50 transition-colors"
                                    >
                                        <flux:icon icon="x-circle" class="size-4" />
                                    </button>
                                @elseif ($entry->status === 'completada' || $entry->status === 'cancelada')
                                    <button
                                        wire:click="reopen({{ $entry->id }})"
                                        wire:confirm="¿Reabrir este trabajo?"
                                        title="Reabrir"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-blue-600 hover:bg-blue-50 transition-colors"
                                    >
                                        <flux:icon icon="arrow-path" class="size-4" />
                                    </button>
                                @endif
                                <button
                                    wire:click="delete({{ $entry->id }})"
                                    wire:confirm="¿Eliminar este trabajo? Esta acción no se puede deshacer."
                                    title="Eliminar"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-red-500 hover:bg-red-50 transition-colors"
                                >
                                    <flux:icon icon="trash" class="size-4" />
                                </button>
                            </div>
                        </x-slot:footer>
                    </x-agro.card>
                @endforeach
            </div>

            @if ($entries->hasPages())
                <div class="mt-6">{{ $entries->links() }}</div>
            @endif
        @endif
    </div>

    {{-- Modal: Filtros --}}
    <x-agro.modal name="planned-works-filters" maxWidth="sm">
        <div class="px-6 py-4 border-b border-zinc-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-agro-100 rounded-lg flex items-center justify-center">
                        <flux:icon icon="adjustments-horizontal" class="size-4 text-agro-600" />
                    </div>
                    <h3 class="text-base font-semibold text-zinc-900">Filtros</h3>
                </div>
                <flux:button x-on:click="$dispatch('close-modal', 'planned-works-filters')" variant="ghost" size="sm" icon="x-mark" />
            </div>
        </div>

        <div class="px-6 py-5 space-y-5">
            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Categoría</label>
                <flux:select wire:model.live="filterCategory">
                    <option value="">Todas</option>
                    @foreach ($categories as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </flux:select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Prioridad</label>
                <flux:select wire:model.live="filterPriority">
                    <option value="">Todas</option>
                    @foreach ($priorities as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </flux:select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Parcela</label>
                <flux:select wire:model.live="filterPlot">
                    <option value="">Todas</option>
                    @foreach ($plots as $plot)
                        <option value="{{ $plot->id }}">{{ $plot->name }}</option>
                    @endforeach
                </flux:select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Campaña</label>
                <flux:select wire:model.live="filterCampaign">
                    <option value="">Todas</option>
                    @foreach ($campaigns as $campaign)
                        <option value="{{ $campaign->id }}">{{ $campaign->name }} ({{ $campaign->year }})</option>
                    @endforeach
                </flux:select>
            </div>
        </div>

        <div class="px-6 py-4 border-t border-zinc-200 flex items-center justify-between">
            @if ($filterCount > 0)
                <button
                    wire:click="clearFilters"
                    class="text-sm text-zinc-400 hover:text-zinc-600 transition-colors"
                >
                    Limpiar filtros
                </button>
            @else
                <span></span>
            @endif
            <flux:button x-on:click="$dispatch('close-modal', 'planned-works-filters')" variant="primary" size="sm">
                Aplicar
            </flux:button>
        </div>
    </x-agro.modal>

</div>
