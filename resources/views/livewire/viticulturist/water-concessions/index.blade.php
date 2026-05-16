<div class="space-y-6 animate-fade-in">

    {{-- Header --}}
    <x-agro.page-header
        title="Concesiones de Riego"
        description="Registro de concesiones y derechos de agua para riego"
        icon="beaker"
    >
        <x-slot:actions>
            <flux:button href="{{ roleRoute('viticulturist.water-concessions.create') }}" variant="primary" icon="plus">
                Nueva Concesión
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Stats colapsables --}}
    <x-agro.stats-section key="water-concessions">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <x-agro.stat-card
                label="Concesiones activas"
                :value="$stats['active']"
                icon="beaker"
                color="blue"
            />
            <x-agro.stat-card
                label="M³ autorizados"
                :value="number_format($stats['total_m3'], 0, ',', '.')"
                description="Volumen total concedido"
                icon="beaker"
                color="agro"
            />
            <x-agro.stat-card
                label="M³ utilizados"
                :value="number_format($stats['used_m3'] ?? 0, 0, ',', '.')"
                description="Consumo registrado"
                icon="beaker"
                color="green"
            />
            <x-agro.stat-card
                label="Próximas a vencer"
                :value="$stats['expiring_soon']"
                description="En los próximos 90 días"
                icon="clock"
                color="amber"
            />
        </div>
    </x-agro.stats-section>

    {{-- Tabs --}}
    <x-agro.tabs
        :tabs="[
            'active'   => ['label' => 'Activas',    'count' => $stats['active']],
            'archived' => ['label' => 'Archivadas', 'count' => $stats['archived']],
        ]"
        :active="$currentTab"
        wireMethod="switchTab"
    />

    {{-- Toolbar: búsqueda + filtros --}}
    <div class="flex items-center gap-3 flex-wrap">
        <div class="relative flex-1 min-w-48 max-w-sm">
            <flux:input
                wire:model.live.debounce.300ms="search"
                placeholder="Buscar por masa de agua, organismo, nº concesión…"
                icon="magnifying-glass"
                class="w-full"
            />
        </div>

        {{-- Botón filtros con badge --}}
        @php $filterCount = ($filterConcessionType ? 1 : 0); @endphp
        <flux:button
            x-on:click="$dispatch('open-modal', 'water-concessions-filters')"
            variant="outline"
            icon="funnel"
        >
            Filtros
            @if($filterCount > 0)
                <flux:badge color="blue" size="sm" class="ml-1">{{ $filterCount }}</flux:badge>
            @endif
        </flux:button>

        @if($search || $filterConcessionType)
            <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">Limpiar</flux:button>
        @endif
    </div>

    {{-- Chips de filtros activos --}}
    @if($filterConcessionType)
        <div class="flex flex-wrap items-center gap-2">
            <x-agro.filter-chip icon="tag" :label="$concessionTypes[$filterConcessionType] ?? $filterConcessionType" wireRemove="$set('filterConcessionType', '')" />
        </div>
    @endif

    {{-- Contenido principal --}}
    <div wire:loading.class="opacity-60 pointer-events-none" wire:target="search, filterConcessionType, switchTab, clearFilters">

        {{-- Skeleton de carga --}}
        <x-agro.loading-grid target="search, filterConcessionType, switchTab, clearFilters" />

        {{-- Estado vacío --}}
        <div wire:loading.remove wire:target="search, filterConcessionType, switchTab, clearFilters">
            @if($entries->isEmpty())
                <x-agro.empty-state
                    icon="beaker"
                    title="{{ $currentTab === 'active' ? 'Sin concesiones registradas' : 'Sin concesiones archivadas' }}"
                    description="{{ $search || $filterConcessionType ? 'Ninguna concesión coincide con los filtros aplicados.' : 'Registra las concesiones de riego y derechos de agua de tu explotación.' }}"
                >
                    @if($search || $filterConcessionType)
                        <x-slot:action>
                            <flux:button wire:click="clearFilters" variant="outline" icon="x-mark">Limpiar filtros</flux:button>
                        </x-slot:action>
                    @elseif($currentTab === 'active')
                        <x-slot:action>
                            <flux:button href="{{ roleRoute('viticulturist.water-concessions.create') }}" variant="primary" icon="plus">
                                Nueva Concesión
                            </flux:button>
                        </x-slot:action>
                    @endif
                </x-agro.empty-state>
            @else
                {{-- Grid de cards --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    @foreach($entries as $entry)
                        @php
                            $delay = min($loop->index * 50, 300);
                            $typeColors = [
                                'superficial'        => ['bg' => 'bg-blue-100',   'text' => 'text-blue-600'],
                                'subterranea'        => ['bg' => 'bg-indigo-100', 'text' => 'text-indigo-600'],
                                'comunidad_regantes' => ['bg' => 'bg-teal-100',   'text' => 'text-teal-600'],
                                'otro'               => ['bg' => 'bg-zinc-100',   'text' => 'text-zinc-400'],
                            ];
                            $colors = $typeColors[$entry->concession_type] ?? ['bg' => 'bg-zinc-100', 'text' => 'text-zinc-400'];
                        @endphp
                        <x-agro.card
                            class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                            style="animation-delay: {{ $delay }}ms;"
                            wire:key="concession-{{ $entry->id }}"
                        >
                            <x-slot:header>
                                <x-agro.card-item-header
                                    icon="beaker"
                                    :title="$entry->water_body"
                                    :subtitle="$entry->authority"
                                    :iconBg="$colors['bg']"
                                    :iconColor="$colors['text']"
                                    size="md"
                                    radius="xl"
                                >
                                    <flux:badge color="blue" size="sm">{{ $entry->concession_type_label }}</flux:badge>
                                </x-agro.card-item-header>
                            </x-slot:header>

                            <div class="flex-1 space-y-3">
                                {{-- Volúmenes --}}
                                <div class="grid grid-cols-2 gap-2">
                                    <div class="bg-blue-50 rounded-xl p-3">
                                        <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest mb-0.5">Autorizado</p>
                                        <p class="text-base font-bold text-blue-700 leading-none">
                                            {{ number_format($entry->max_volume_m3, 0, ',', '.') }}<span class="text-xs font-normal text-zinc-400 ml-0.5">m³</span>
                                        </p>
                                    </div>
                                    @if($entry->used_volume_m3 !== null)
                                        <div class="bg-agro-50 rounded-xl p-3">
                                            <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest mb-0.5">Utilizado</p>
                                            <p class="text-base font-bold text-agro-700 leading-none">
                                                {{ number_format($entry->used_volume_m3, 0, ',', '.') }}<span class="text-xs font-normal text-zinc-400 ml-0.5">m³</span>
                                            </p>
                                        </div>
                                    @else
                                        <div class="bg-zinc-50 rounded-xl p-3">
                                            <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest mb-0.5">Utilizado</p>
                                            <p class="text-base font-bold text-zinc-400 leading-none">—</p>
                                        </div>
                                    @endif
                                </div>

                                {{-- Nº concesión --}}
                                @if($entry->concession_number)
                                    <div class="flex items-center gap-2 text-xs text-zinc-500">
                                        <flux:icon icon="hashtag" class="size-3.5 text-zinc-400 shrink-0" />
                                        <span>{{ $entry->concession_number }}</span>
                                    </div>
                                @endif

                                {{-- Fechas --}}
                                <div class="space-y-1">
                                    @if($entry->concession_date)
                                        <div class="flex items-center gap-2 text-xs text-zinc-500">
                                            <flux:icon icon="calendar" class="size-3.5 text-zinc-400 shrink-0" />
                                            <span>Concedida: {{ $entry->concession_date->format('d/m/Y') }}</span>
                                        </div>
                                    @endif
                                    @if($entry->expiry_date)
                                        <div class="flex items-center gap-2 text-xs {{ $entry->is_expired ? 'text-red-500' : ($entry->is_expiring_soon ? 'text-amber-500' : 'text-zinc-500') }}">
                                            <flux:icon icon="clock" class="size-3.5 shrink-0" />
                                            <span>Vence: {{ $entry->expiry_date->format('d/m/Y') }}</span>
                                        </div>
                                    @endif
                                </div>

                                {{-- Badges de estado de vigencia --}}
                                @if($entry->is_expired)
                                    <flux:badge color="red" size="sm">Vencida</flux:badge>
                                @elseif($entry->is_expiring_soon)
                                    <flux:badge color="amber" size="sm">Próxima a vencer</flux:badge>
                                @endif
                            </div>

                            <x-slot:footer>
                                <div class="flex items-center justify-end gap-0.5">
                                    @if($currentTab === 'active')
                                        <x-agro.action-button
                                            variant="edit"
                                            href="{{ roleRoute('viticulturist.water-concessions.edit', $entry) }}"
                                            title="Editar"
                                        />
                                        <x-agro.action-button
                                            variant="archive"
                                            wire:click="archive({{ $entry->id }})"
                                            wire:confirm="¿Archivar esta concesión?"
                                            title="Archivar"
                                        />
                                    @else
                                        <x-agro.action-button
                                            variant="restore"
                                            icon="arrow-uturn-left"
                                            wire:click="unarchive({{ $entry->id }})"
                                            title="Restaurar"
                                        />
                                        <x-agro.action-button
                                            variant="delete"
                                            wire:click="delete({{ $entry->id }})"
                                            wire:confirm="¿Eliminar esta concesión? Esta acción no se puede deshacer."
                                            title="Eliminar"
                                        />
                                    @endif
                                </div>
                            </x-slot:footer>
                        </x-agro.card>
                    @endforeach
                </div>

                @if($entries->hasPages())
                    <div class="mt-6">{{ $entries->links() }}</div>
                @endif
            @endif
        </div>

    </div>

    {{-- Modal de filtros --}}
    <x-agro.modal name="water-concessions-filters" title="Filtrar Concesiones">
        <div class="space-y-4">
            <flux:field>
                <flux:label>Tipo de concesión</flux:label>
                <flux:select wire:model.live="filterConcessionType">
                    <flux:select.option value="">Todos los tipos</flux:select.option>
                    @foreach($concessionTypes as $key => $label)
                        <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>
            </flux:field>
        </div>

        <x-slot:footer>
            <flux:button wire:click="clearFilters" variant="ghost" x-on:click="$dispatch('close-modal', 'water-concessions-filters')">
                Limpiar filtros
            </flux:button>
            <flux:button variant="primary" x-on:click="$dispatch('close-modal', 'water-concessions-filters')">
                Aplicar
            </flux:button>
        </x-slot:footer>
    </x-agro.modal>

</div>
