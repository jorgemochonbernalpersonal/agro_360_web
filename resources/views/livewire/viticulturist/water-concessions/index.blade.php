<div class="space-y-6 animate-fade-in">

    {{-- Header --}}
    <x-agro.page-header
        title="Concesiones de Riego"
        description="Registro de concesiones y derechos de agua para riego"
        icon="beaker"
    >
        <x-slot:actions>
            <flux:button href="{{ route('viticulturist.water-concessions.create') }}" variant="primary" icon="plus">
                Nueva Concesión
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Stats colapsables --}}
    <div x-data="{
        open: localStorage.getItem('water-concessions-stats-open') !== 'false',
        toggle() {
            this.open = !this.open;
            localStorage.setItem('water-concessions-stats-open', String(this.open));
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
                    title="Concesiones activas"
                    :value="$stats['active']"
                    icon="beaker"
                    color="blue"
                />
                <x-agro.stat-card
                    title="M³ autorizados"
                    :value="number_format($stats['total_m3'], 0, ',', '.')"
                    description="Volumen total concedido"
                    icon="beaker"
                    color="agro"
                />
                <x-agro.stat-card
                    title="M³ utilizados"
                    :value="number_format($stats['used_m3'] ?? 0, 0, ',', '.')"
                    description="Consumo registrado"
                    icon="beaker"
                    color="green"
                />
                <x-agro.stat-card
                    title="Próximas a vencer"
                    :value="$stats['expiring_soon']"
                    description="En los próximos 90 días"
                    icon="clock"
                    color="amber"
                />
            </div>
        </div>
    </div>

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
            <span class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                <flux:icon icon="tag" class="size-3" />
                {{ $concessionTypes[$filterConcessionType] ?? $filterConcessionType }}
                <button wire:click="$set('filterConcessionType', '')" class="ml-0.5 p-0.5 rounded-full hover:bg-agro-200 transition-colors">
                    <flux:icon icon="x-mark" class="size-3" />
                </button>
            </span>
        </div>
    @endif

    {{-- Contenido principal --}}
    <div wire:loading.class="opacity-60 pointer-events-none" wire:target="search, filterConcessionType, switchTab, clearFilters">

        {{-- Skeleton de carga --}}
        <div wire:loading wire:target="search, filterConcessionType, switchTab, clearFilters">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @for($i = 0; $i < 8; $i++)
                    <div class="bg-white rounded-2xl border border-zinc-100 shadow-sm p-5 animate-pulse">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-xl bg-zinc-100 shrink-0"></div>
                            <div class="flex-1 space-y-2">
                                <div class="h-3.5 bg-zinc-100 rounded w-3/4"></div>
                                <div class="h-3 bg-zinc-100 rounded w-1/2"></div>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="h-8 bg-zinc-100 rounded w-1/2"></div>
                            <div class="h-3 bg-zinc-100 rounded w-full"></div>
                            <div class="h-3 bg-zinc-100 rounded w-2/3"></div>
                        </div>
                    </div>
                @endfor
            </div>
        </div>

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
                            <flux:button href="{{ route('viticulturist.water-concessions.create') }}" variant="primary" icon="plus">
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
                                <div class="flex items-start gap-3">
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 {{ $colors['bg'] }}">
                                        <flux:icon icon="beaker" class="size-5 {{ $colors['text'] }}" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="font-bold text-zinc-900 truncate">{{ $entry->water_body }}</h3>
                                        <p class="text-xs text-zinc-400 truncate">{{ $entry->authority }}</p>
                                    </div>
                                    <flux:badge color="blue" size="sm" class="shrink-0">{{ $entry->concession_type_label }}</flux:badge>
                                </div>
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
                                        <a href="{{ route('viticulturist.water-concessions.edit', $entry) }}"
                                           title="Editar"
                                           class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors">
                                            <flux:icon icon="pencil-square" class="size-4" />
                                        </a>
                                        <button
                                            wire:click="archive({{ $entry->id }})"
                                            wire:confirm="¿Archivar esta concesión?"
                                            title="Archivar"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-amber-600 hover:bg-amber-50 transition-colors">
                                            <flux:icon icon="archive-box" class="size-4" />
                                        </button>
                                    @else
                                        <button
                                            wire:click="unarchive({{ $entry->id }})"
                                            title="Restaurar"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-agro-600 hover:bg-agro-50 transition-colors">
                                            <flux:icon icon="arrow-uturn-left" class="size-4" />
                                        </button>
                                        <button
                                            wire:click="delete({{ $entry->id }})"
                                            wire:confirm="¿Eliminar esta concesión? Esta acción no se puede deshacer."
                                            title="Eliminar"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-red-600 hover:bg-red-50 transition-colors">
                                            <flux:icon icon="trash" class="size-4" />
                                        </button>
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
