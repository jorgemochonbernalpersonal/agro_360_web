<div class="space-y-6 animate-fade-in">

    {{-- Header --}}
    <x-agro.page-header
        title="Alertas Fitosanitarias"
        description="Gestiona las alertas fitosanitarias oficiales: plagas, enfermedades, clima y normativa"
        icon="bell-alert"
    >
        <x-slot:actions>
            <flux:button href="{{ roleRoute('viticulturist.phytosanitary-alerts.create') }}" variant="primary" icon="plus">
                Nueva Alerta
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Stats (colapsables) --}}
    <div x-data="{
        open: localStorage.getItem('phyto-alerts-stats-open') !== 'false',
        toggle() {
            this.open = !this.open;
            localStorage.setItem('phyto-alerts-stats-open', String(this.open));
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
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <x-agro.stat-card
                    label="Alertas activas"
                    :value="$stats['active']"
                    icon="bell-alert"
                    color="agro"
                />
                <x-agro.stat-card
                    label="Archivadas"
                    :value="$stats['archived']"
                    icon="archive-box"
                    color="zinc"
                />
                <x-agro.stat-card
                    label="Críticas"
                    :value="$stats['critical']"
                    description="Activas con severidad crítica"
                    icon="exclamation-triangle"
                    color="red"
                />
                <x-agro.stat-card
                    label="Expiradas"
                    :value="$stats['expired']"
                    description="Activas con fecha vencida"
                    icon="x-circle"
                    color="amber"
                />
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <x-agro.tabs :tabs="[
        'active'   => ['label' => 'Activas',    'count' => $stats['active']],
        'archived' => ['label' => 'Archivadas', 'count' => $stats['archived']],
    ]" :active="$currentTab" wireMethod="switchTab" />

    {{-- Toolbar --}}
    @php
        $filterCount = (int) !empty($filterAlertType) + (int) !empty($filterSeverity);
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
                placeholder="Buscar por título, descripción o zona afectada..."
                class="w-full pl-9 pr-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm placeholder:text-zinc-400 shadow-sm focus:outline-none focus:ring-2 focus:ring-agro-500 focus:border-transparent transition"
            />
        </div>

        {{-- Filtros --}}
        <button
            x-on:click="$dispatch('open-modal', 'phyto-alerts-filters')"
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

        {{-- Nueva Alerta --}}
        <flux:button href="{{ roleRoute('viticulturist.phytosanitary-alerts.create') }}" variant="primary" icon="plus">
            Nueva
        </flux:button>

    </div>

    {{-- Chips de filtros activos --}}
    @if ($filterAlertType || $filterSeverity)
        <div class="flex flex-wrap items-center gap-2">
            @if ($filterAlertType)
                <span class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                    <flux:icon icon="bell-alert" class="size-3" />
                    {{ $alertTypes[$filterAlertType] ?? $filterAlertType }}
                    <button
                        wire:click="$set('filterAlertType', '')"
                        class="ml-0.5 p-0.5 rounded-full hover:bg-agro-200 transition-colors"
                    >
                        <flux:icon icon="x-mark" class="size-3" />
                    </button>
                </span>
            @endif
            @if ($filterSeverity)
                <span class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                    <flux:icon icon="exclamation-triangle" class="size-3" />
                    {{ $severities[$filterSeverity] ?? $filterSeverity }}
                    <button
                        wire:click="$set('filterSeverity', '')"
                        class="ml-0.5 p-0.5 rounded-full hover:bg-agro-200 transition-colors"
                    >
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
        wire:target="switchTab, search, filterAlertType, filterSeverity, nextPage, previousPage, gotoPage"
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
        wire:target="switchTab, search, filterAlertType, filterSeverity, nextPage, previousPage, gotoPage"
    >
        @if ($entries->isEmpty())
            <x-agro.empty-state
                icon="bell-alert"
                title="{{ $currentTab === 'active' ? 'Sin alertas activas' : 'Sin alertas archivadas' }}"
                description="{{ $currentTab === 'active' ? 'Registra alertas fitosanitarias oficiales: plagas, enfermedades, avisos climáticos y normativos.' : 'Las alertas archivadas aparecerán aquí.' }}"
            >
                @if ($currentTab === 'active')
                    <x-slot:action>
                        <flux:button href="{{ roleRoute('viticulturist.phytosanitary-alerts.create') }}" variant="primary" icon="plus">
                            Nueva Alerta
                        </flux:button>
                    </x-slot:action>
                @endif
            </x-agro.empty-state>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach ($entries as $entry)
                    @php
                        $delay = min($loop->index * 50, 300);
                        $severityBgClasses = match($entry->severity) {
                            'baja'    => 'bg-zinc-100 text-zinc-600',
                            'media'   => 'bg-blue-100 text-blue-600',
                            'alta'    => 'bg-amber-100 text-amber-600',
                            'critica' => 'bg-red-100 text-red-600',
                            default   => 'bg-zinc-100 text-zinc-400',
                        };
                    @endphp

                    <x-agro.card
                        class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                        style="animation-delay: {{ $delay }}ms;"
                        wire:key="alert-{{ $entry->id }}"
                    >
                        {{-- Header --}}
                        <x-slot:header>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 {{ $severityBgClasses }}">
                                    <flux:icon icon="{{ $entry->alert_type_icon }}" class="size-5" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-zinc-900 truncate">{{ $entry->title }}</h3>
                                    <p class="text-xs text-zinc-400 truncate">{{ $entry->source_label }}</p>
                                </div>
                                <flux:badge color="{{ $entry->severity_color }}" size="sm" class="shrink-0">
                                    {{ $entry->alert_type_label }}
                                </flux:badge>
                            </div>
                        </x-slot:header>

                        {{-- Body --}}
                        <div class="flex-1 space-y-3">

                            {{-- Fechas --}}
                            <div class="bg-zinc-50 rounded-xl p-3 space-y-1">
                                <div class="flex items-center gap-2 text-xs text-zinc-600">
                                    <flux:icon icon="calendar-days" class="size-3.5 text-zinc-400 shrink-0" />
                                    <span class="text-zinc-400">Fecha:</span>
                                    <span class="font-medium">{{ $entry->alert_date->format('d/m/Y') }}</span>
                                </div>
                                @if ($entry->expiry_date)
                                    <div class="flex items-center gap-2 text-xs">
                                        <flux:icon icon="calendar-days" class="size-3.5 text-zinc-400 shrink-0" />
                                        <span class="text-zinc-400">Expira:</span>
                                        <span class="font-medium {{ $entry->is_expired ? 'text-red-600' : 'text-zinc-600' }}">
                                            {{ $entry->expiry_date->format('d/m/Y') }}
                                        </span>
                                    </div>
                                @endif
                                @if ($entry->affected_area)
                                    <div class="flex items-center gap-2 text-xs text-zinc-600">
                                        <flux:icon icon="map-pin" class="size-3.5 text-zinc-400 shrink-0" />
                                        <span class="text-zinc-400">Zona:</span>
                                        <span class="font-medium truncate">{{ $entry->affected_area }}</span>
                                    </div>
                                @endif
                            </div>

                            {{-- Badges de estado --}}
                            <div class="flex flex-wrap items-center gap-1.5">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold {{ match($entry->severity) {
                                    'baja'    => 'bg-zinc-100 text-zinc-700',
                                    'media'   => 'bg-blue-100 text-blue-700',
                                    'alta'    => 'bg-amber-100 text-amber-700',
                                    'critica' => 'bg-red-100 text-red-700',
                                    default   => 'bg-zinc-100 text-zinc-700',
                                } }}">
                                    {{ $entry->severity_label }}
                                </span>
                                @if ($entry->is_expired)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                                        <flux:icon icon="x-circle" class="size-3" />
                                        Expirada
                                    </span>
                                @endif
                            </div>

                            {{-- Descripción truncada --}}
                            @if ($entry->description)
                                <p class="text-xs text-zinc-500 line-clamp-2" title="{{ $entry->description }}">
                                    {{ Str::limit($entry->description, 120) }}
                                </p>
                            @endif

                        </div>

                        {{-- Footer acciones --}}
                        <x-slot:footer>
                            <div class="flex items-center justify-end gap-0.5">
                                <a href="{{ roleRoute('viticulturist.phytosanitary-alerts.edit', $entry) }}"
                                   title="Editar"
                                   class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors">
                                    <flux:icon icon="pencil-square" class="size-4" />
                                </a>
                                @if ($currentTab === 'active')
                                    <button
                                        wire:click="archive({{ $entry->id }})"
                                        wire:confirm="¿Archivar esta alerta?"
                                        title="Archivar"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-amber-600 hover:bg-amber-50 transition-colors">
                                        <flux:icon icon="archive-box" class="size-4" />
                                    </button>
                                @else
                                    <button
                                        wire:click="unarchive({{ $entry->id }})"
                                        wire:confirm="¿Restaurar esta alerta?"
                                        title="Restaurar"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-agro-600 hover:bg-agro-50 transition-colors">
                                        <flux:icon icon="arrow-path" class="size-4" />
                                    </button>
                                @endif
                                <button
                                    wire:click="delete({{ $entry->id }})"
                                    wire:confirm="¿Eliminar esta alerta? Esta acción no se puede deshacer."
                                    title="Eliminar"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-red-500 hover:bg-red-50 transition-colors">
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
    <x-agro.modal name="phyto-alerts-filters" maxWidth="sm">
        <div class="px-6 py-4 border-b border-zinc-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-agro-100 rounded-lg flex items-center justify-center">
                        <flux:icon icon="adjustments-horizontal" class="size-4 text-agro-600" />
                    </div>
                    <h3 class="text-base font-semibold text-zinc-900">Filtros</h3>
                </div>
                <flux:button x-on:click="$dispatch('close-modal', 'phyto-alerts-filters')" variant="ghost" size="sm" icon="x-mark" />
            </div>
        </div>

        <div class="px-6 py-5 space-y-5">
            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Tipo de Alerta</label>
                <flux:select wire:model.live="filterAlertType">
                    <option value="">Todos los tipos</option>
                    @foreach ($alertTypes as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </flux:select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Severidad</label>
                <flux:select wire:model.live="filterSeverity">
                    <option value="">Todas las severidades</option>
                    @foreach ($severities as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </flux:select>
            </div>
        </div>

        <div class="px-6 py-4 border-t border-zinc-200 flex items-center justify-between">
            @if ($filterAlertType || $filterSeverity)
                <button
                    wire:click="clearFilters"
                    class="text-sm text-zinc-400 hover:text-zinc-600 transition-colors"
                >
                    Limpiar filtros
                </button>
            @else
                <span></span>
            @endif
            <flux:button x-on:click="$dispatch('close-modal', 'phyto-alerts-filters')" variant="primary" size="sm">
                Aplicar
            </flux:button>
        </div>
    </x-agro.modal>

</div>
