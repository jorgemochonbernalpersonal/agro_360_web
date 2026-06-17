<div class="space-y-6 animate-fade-in">

    {{-- Header --}}
    <x-agro.page-header
        :title="__('Alertas Fitosanitarias')"
        :description="__('Gestiona las alertas fitosanitarias oficiales: plagas, enfermedades, clima y normativa')"
        icon="bell-alert"
    >
        <x-slot:actions>
            <flux:button href="{{ roleRoute('viticulturist.phytosanitary-alerts.create') }}" variant="primary" icon="plus">
                {{ __('Nueva Alerta') }}
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Stats (colapsables) --}}
    <x-agro.stats-section key="phyto-alerts" columns="4">
        <x-agro.stat-card
            :label="__('Alertas activas')"
            :value="$stats['active']"
            icon="bell-alert"
            color="agro"
        />
        <x-agro.stat-card
            :label="__('Archivadas')"
            :value="$stats['archived']"
            icon="archive-box"
            color="zinc"
        />
        <x-agro.stat-card
            :label="__('Críticas')"
            :value="$stats['critical']"
            :description="__('Activas con severidad crítica')"
            icon="exclamation-triangle"
            color="red"
        />
        <x-agro.stat-card
            :label="__('Expiradas')"
            :value="$stats['expired']"
            :description="__('Activas con fecha vencida')"
            icon="x-circle"
            color="amber"
        />
    </x-agro.stats-section>

    {{-- Tabs --}}
    <x-agro.tabs :tabs="[
        'active'   => ['label' => __('Activas'),    'count' => $stats['active']],
        'archived' => ['label' => __('Archivadas'), 'count' => $stats['archived']],
    ]" :active="$currentTab" wireMethod="switchTab" />

    {{-- Toolbar --}}
    @php
        $filterCount = (int) !empty($filterAlertType) + (int) !empty($filterSeverity);
    @endphp
    <div class="flex items-center gap-3">

        {{-- Search --}}
        <x-agro.search-input wire:model.live.debounce.300ms="search" :placeholder="__('Buscar por título, descripción o zona afectada...')" />

        {{-- Filtros --}}
        <x-agro.filter-button modal="phyto-alerts-filters" :count="$filterCount" />

        {{-- Separador --}}
        <x-agro.divider-vertical />

        {{-- Nueva Alerta --}}
        <flux:button href="{{ roleRoute('viticulturist.phytosanitary-alerts.create') }}" variant="primary" icon="plus">
            {{ __('Nueva') }}
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
                {{ __('Limpiar todo') }}
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
                :title="$currentTab === 'active' ? __('Sin alertas activas') : __('Sin alertas archivadas')"
                :description="$currentTab === 'active' ? __('Registra alertas fitosanitarias oficiales: plagas, enfermedades, avisos climáticos y normativos.') : __('Las alertas archivadas aparecerán aquí.')"
            >
                @if ($currentTab === 'active')
                    <x-slot:action>
                        <flux:button href="{{ roleRoute('viticulturist.phytosanitary-alerts.create') }}" variant="primary" icon="plus">
                            {{ __('Nueva Alerta') }}
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
                                    <span class="text-zinc-400">{{ __('Fecha:') }}</span>
                                    <span class="font-medium">{{ $entry->alert_date->format('d/m/Y') }}</span>
                                </div>
                                @if ($entry->expiry_date)
                                    <div class="flex items-center gap-2 text-xs">
                                        <flux:icon icon="calendar-days" class="size-3.5 text-zinc-400 shrink-0" />
                                        <span class="text-zinc-400">{{ __('Expira:') }}</span>
                                        <span class="font-medium {{ $entry->is_expired ? 'text-red-600' : 'text-zinc-600' }}">
                                            {{ $entry->expiry_date->format('d/m/Y') }}
                                        </span>
                                    </div>
                                @endif
                                @if ($entry->affected_area)
                                    <div class="flex items-center gap-2 text-xs text-zinc-600">
                                        <flux:icon icon="map-pin" class="size-3.5 text-zinc-400 shrink-0" />
                                        <span class="text-zinc-400">{{ __('Zona:') }}</span>
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
                                        {{ __('Expirada') }}
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
                                    :title="__('Editar')"
                                />
                                @if ($currentTab === 'active')
                                    <x-agro.action-button
                                        variant="archive"
                                        wire:click="archive({{ $entry->id }})"
                                        wire:confirm="{{ __('¿Archivar esta alerta?') }}"
                                        :title="__('Archivar')"
                                    />
                                @else
                                    <x-agro.action-button
                                        variant="restore"
                                        icon="arrow-path"
                                        wire:click="unarchive({{ $entry->id }})"
                                        wire:confirm="{{ __('¿Restaurar esta alerta?') }}"
                                        :title="__('Restaurar')"
                                    />
                                @endif
                                <x-agro.action-button
                                    variant="delete"
                                    wire:click="delete({{ $entry->id }})"
                                    wire:confirm="{{ __('¿Eliminar esta alerta? Esta acción no se puede deshacer.') }}"
                                    :title="__('Eliminar')"
                                />
                            </div>
                        </x-slot:footer>
                    </x-agro.card>
                @endforeach
            </div>

            <x-agro.pagination :paginator="$entries" />
        @endif
    </div>

    {{-- Modal: Filtros --}}
    <x-agro.filter-modal
        name="phyto-alerts-filters"
        :hasActiveFilters="$filterAlertType || $filterSeverity"
        clearAction="clearFilters"
    >
        <div>
            <x-agro.field-label>{{ __('Tipo de Alerta') }}</x-agro.field-label>
            <flux:select wire:model.live="filterAlertType">
                <option value="">{{ __('Todos los tipos') }}</option>
                @foreach ($alertTypes as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </flux:select>
        </div>
        <div>
            <x-agro.field-label>{{ __('Severidad') }}</x-agro.field-label>
            <flux:select wire:model.live="filterSeverity">
                <option value="">{{ __('Todas las severidades') }}</option>
                @foreach ($severities as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </flux:select>
        </div>
    </x-agro.filter-modal>

</div>
