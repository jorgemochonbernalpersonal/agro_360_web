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
    <x-agro.stats-section key="phyto-alerts" columns="4">
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
    </x-agro.stats-section>

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
        <x-agro.search-input wire:model.live.debounce.300ms="search" placeholder="Buscar por título, descripción o zona afectada..." />

        {{-- Filtros --}}
        <x-agro.filter-button modal="phyto-alerts-filters" :count="$filterCount" />

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
                <x-agro.filter-chip icon="bell-alert" :label="$alertTypes[$filterAlertType] ?? $filterAlertType" wireRemove="$set('filterAlertType', '')" />
            @endif
            @if ($filterSeverity)
                <x-agro.filter-chip icon="exclamation-triangle" :label="$severities[$filterSeverity] ?? $filterSeverity" wireRemove="$set('filterSeverity', '')" />
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
    <x-agro.loading-grid target="switchTab, search, filterAlertType, filterSeverity, nextPage, previousPage, gotoPage" />

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
                                <x-agro.action-button
                                    variant="edit"
                                    href="{{ roleRoute('viticulturist.phytosanitary-alerts.edit', $entry) }}"
                                    title="Editar"
                                />
                                @if ($currentTab === 'active')
                                    <x-agro.action-button
                                        variant="archive"
                                        wire:click="archive({{ $entry->id }})"
                                        wire:confirm="¿Archivar esta alerta?"
                                        title="Archivar"
                                    />
                                @else
                                    <x-agro.action-button
                                        variant="restore"
                                        icon="arrow-path"
                                        wire:click="unarchive({{ $entry->id }})"
                                        wire:confirm="¿Restaurar esta alerta?"
                                        title="Restaurar"
                                    />
                                @endif
                                <x-agro.action-button
                                    variant="delete"
                                    wire:click="delete({{ $entry->id }})"
                                    wire:confirm="¿Eliminar esta alerta? Esta acción no se puede deshacer."
                                    title="Eliminar"
                                />
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
