<div class="space-y-6 animate-fade-in">

    {{-- Header --}}
    <x-agro.page-header
        title="Mermas y Pérdidas"
        description="Registro de mermas, filtraciones y pérdidas de vino elaborado"
    >
        <x-slot:actions>
            <flux:button href="{{ route('winery.wine-losses.create') }}" variant="primary" icon="plus">
                Nueva Merma
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- KPIs --}}
    <div x-data="{
        open: localStorage.getItem('wine-losses-stats-open') !== 'false',
        toggle() {
            this.open = !this.open;
            localStorage.setItem('wine-losses-stats-open', String(this.open));
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
        <div class="grid grid-cols-2 gap-4">
            <x-agro.stat-card
                label="Total mermas"
                :value="$stats['total']"
                icon="exclamation-triangle"
                color="zinc"
            />
            <x-agro.stat-card
                label="Este año"
                :value="$stats['this_year']"
                icon="calendar-days"
                color="agro"
            />
            <x-agro.stat-card
                label="Naturales"
                :value="$stats['natural']"
                icon="beaker"
                color="zinc"
            />
            <x-agro.stat-card
                label="Accidentales"
                :value="$stats['accidental']"
                icon="fire"
                color="amber"
            />
        </div>
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="flex items-center gap-3">
        <div class="flex-1 relative">
            <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                <flux:icon icon="magnifying-glass" class="size-4 text-zinc-400" />
            </div>
            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                placeholder="Buscar por vino o contenedor..."
                class="w-full pl-9 pr-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm placeholder:text-zinc-400 shadow-sm focus:outline-none focus:ring-2 focus:ring-agro-500 focus:border-transparent transition"
            />
        </div>

        <flux:select wire:model.live="wineFilter" class="w-44">
            <flux:select.option value="">Todos los vinos</flux:select.option>
            @foreach($wines as $wine)
                <flux:select.option value="{{ $wine->id }}">{{ $wine->name }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select wire:model.live="typeFilter" class="w-48">
            <flux:select.option value="">Todos los tipos</flux:select.option>
            @foreach($lossTypes as $key => $label)
                <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
            @endforeach
        </flux:select>

        @if($search || $wineFilter || $typeFilter)
            <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">
                Limpiar
            </flux:button>
        @endif
    </div>

    {{-- Loading skeleton --}}
    <div wire:loading wire:target="search, wineFilter, typeFilter, clearFilters, nextPage, previousPage">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @for($i = 0; $i < 6; $i++)
                <x-agro.skeleton-card />
            @endfor
        </div>
    </div>

    {{-- Grid de cards --}}
    <div wire:loading.remove wire:target="search, wineFilter, typeFilter, clearFilters, nextPage, previousPage">
        @if($losses->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($losses as $loss)
                    @php
                        $delay = min($loop->index * 50, 300);
                        $typeColors = [
                            'evaporation' => ['icon' => 'text-yellow-600', 'bg' => 'bg-yellow-100', 'badge' => 'yellow'],
                            'filtration'  => ['icon' => 'text-blue-600',   'bg' => 'bg-blue-100',   'badge' => 'blue'],
                            'sampling'    => ['icon' => 'text-zinc-500',   'bg' => 'bg-zinc-100',   'badge' => 'zinc'],
                            'spillage'    => ['icon' => 'text-red-600',    'bg' => 'bg-red-100',    'badge' => 'red'],
                            'other'       => ['icon' => 'text-zinc-500',   'bg' => 'bg-zinc-100',   'badge' => 'zinc'],
                        ];
                        $tc = $typeColors[$loss->loss_type] ?? $typeColors['other'];
                    @endphp
                    <x-agro.card
                        class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                        style="animation-delay: {{ $delay }}ms;"
                        wire:key="loss-{{ $loss->id }}"
                    >
                        <x-slot:header>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 {{ $tc['bg'] }}">
                                    <flux:icon icon="exclamation-triangle" class="size-5 {{ $tc['icon'] }}" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-zinc-900 truncate">{{ $loss->wine?->name ?? '—' }}</h3>
                                    <p class="text-xs text-zinc-400">
                                        {{ $loss->loss_date instanceof \Carbon\Carbon ? $loss->loss_date->format('d/m/Y') : $loss->loss_date }}
                                    </p>
                                </div>
                                <flux:badge color="{{ $tc['badge'] }}" size="sm" class="shrink-0">
                                    {{ $lossTypes[$loss->loss_type] ?? $loss->loss_type }}
                                </flux:badge>
                            </div>
                        </x-slot:header>

                        <div class="flex-1 space-y-4">
                            {{-- Stats --}}
                            <div class="grid grid-cols-2 gap-2">
                                <div class="bg-red-50 rounded-xl p-3">
                                    <p class="text-[10px] font-semibold text-red-400 uppercase tracking-widest mb-0.5">Cantidad</p>
                                    <p class="text-2xl font-bold text-red-700 leading-none">
                                        −{{ number_format($loss->quantity, 2) }}
                                    </p>
                                    <p class="text-xs text-red-400 mt-0.5">
                                        {{ $loss->unitOfMeasurement?->symbol ?? $loss->unitOfMeasurement?->name ?? '' }}
                                    </p>
                                </div>
                                <div class="bg-zinc-50 rounded-xl p-3">
                                    <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest mb-0.5">Contenedor</p>
                                    <p class="text-sm font-semibold text-zinc-700 leading-snug truncate">
                                        {{ $loss->container?->name ?? '—' }}
                                    </p>
                                </div>
                            </div>

                            {{-- Autorización --}}
                            @if($loss->loss_authorization)
                                <div class="flex items-center gap-2 text-sm text-zinc-600">
                                    <flux:icon icon="shield-check" class="size-4 text-zinc-400 shrink-0" />
                                    <span class="text-xs">{{ $loss->loss_authorization_label }}</span>
                                </div>
                            @endif

                            {{-- Notas --}}
                            @if($loss->notes)
                                <p class="text-xs text-zinc-400 line-clamp-2">{{ $loss->notes }}</p>
                            @endif
                        </div>

                        <x-slot:footer>
                            <div class="flex items-center justify-end gap-0.5">
                                <a href="{{ route('winery.wine-losses.edit', $loss) }}"
                                   title="Editar merma"
                                   class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors">
                                    <flux:icon icon="pencil-square" class="size-4" />
                                </a>
                                <button
                                    wire:click="delete({{ $loss->id }})"
                                    wire:confirm="¿Eliminar esta merma? Se restaurarán los litros al contenedor."
                                    wire:loading.attr="disabled"
                                    title="Eliminar merma"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-red-500 hover:bg-red-50 transition-colors">
                                    <flux:icon icon="trash" class="size-4" />
                                </button>
                            </div>
                        </x-slot:footer>
                    </x-agro.card>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $losses->links() }}
            </div>
        @else
            <x-agro.empty-state
                icon="exclamation-triangle"
                title="{{ $search || $wineFilter || $typeFilter ? 'Ninguna merma coincide con los filtros' : 'Sin mermas registradas' }}"
                description="{{ $search || $wineFilter || $typeFilter ? 'Prueba a cambiar o limpiar los filtros aplicados.' : 'Registra evaporaciones, filtraciones o pérdidas accidentales de vino.' }}"
            >
                @if($search || $wineFilter || $typeFilter)
                    <x-slot:action>
                        <flux:button wire:click="clearFilters" variant="outline" icon="x-mark">
                            Limpiar filtros
                        </flux:button>
                    </x-slot:action>
                @else
                    <x-slot:action>
                        <flux:button href="{{ route('winery.wine-losses.create') }}" variant="primary" icon="plus">
                            Nueva Merma
                        </flux:button>
                    </x-slot:action>
                @endif
            </x-agro.empty-state>
        @endif
    </div>

</div>

