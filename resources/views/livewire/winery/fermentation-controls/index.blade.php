<div class="space-y-6 animate-fade-in">

    {{-- Header --}}
    <x-agro.page-header
        title="Controles de Fermentación"
        description="Seguimiento periódico de parámetros de fermentación por vino y depósito"
        icon="beaker"
    >
        <x-slot:actions>
            <flux:button variant="primary" icon="plus" href="{{ roleRoute('fermentation-controls.create') }}" wire:navigate>
                Nuevo control
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- KPIs --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <x-agro.stat-card
            label="Total controles"
            :value="$stats['total']"
            icon="beaker"
            color="zinc"
        />
        <x-agro.stat-card
            label="Este año"
            :value="$stats['this_year']"
            icon="calendar-days"
            color="agro"
        />
        <x-agro.stat-card
            label="En fermentación"
            :value="$stats['fermenting']"
            icon="fire"
            color="amber"
        />
        <x-agro.stat-card
            label="Completados"
            :value="$stats['done']"
            icon="check-circle"
            color="zinc"
        />
    </div>

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center gap-3">
        <div class="flex-1 min-w-48 relative">
            <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                <flux:icon icon="magnifying-glass" class="size-4 text-zinc-400" />
            </div>
            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                placeholder="Buscar por vino..."
                class="w-full pl-9 pr-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm placeholder:text-zinc-400 shadow-sm focus:outline-none focus:ring-2 focus:ring-agro-500 focus:border-transparent transition"
            />
        </div>

        <flux:select wire:model.live="wineFilter" class="w-40">
            <flux:select.option value="">Todos los vinos</flux:select.option>
            @foreach($wines as $wine)
                <flux:select.option value="{{ $wine->id }}">{{ $wine->name }}{{ $wine->vintage ? ' (' . $wine->vintage . ')' : '' }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select wire:model.live="containerFilter" class="w-40">
            <flux:select.option value="">Todos los depósitos</flux:select.option>
            @foreach($containers as $c)
                <flux:select.option value="{{ $c->id }}">{{ $c->name }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select wire:model.live="statusFilter" class="w-44">
            <flux:select.option value="">Todos los estados</flux:select.option>
            <flux:select.option value="fermenting">En fermentación</flux:select.option>
            <flux:select.option value="done">Fermentación completada</flux:select.option>
        </flux:select>

        @if($search || $wineFilter || $statusFilter || $containerFilter)
            <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">
                Limpiar
            </flux:button>
        @endif
    </div>

    {{-- Loading skeleton --}}
    <div wire:loading wire:target="search, wineFilter, statusFilter, containerFilter, clearFilters, nextPage, previousPage">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @for($i = 0; $i < 6; $i++)
                <x-agro.skeleton-card />
            @endfor
        </div>
    </div>

    {{-- Grid de cards --}}
    <div wire:loading.remove wire:target="search, wineFilter, statusFilter, containerFilter, clearFilters, nextPage, previousPage">
        @if($controls->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($controls as $control)
                    @php
                        $delay = min($loop->index * 50, 300);
                        $isFermenting = $control->isFermenting();
                    @endphp
                    <x-agro.card
                        class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                        style="animation-delay: {{ $delay }}ms;"
                        wire:key="control-{{ $control->id }}"
                    >
                        <x-slot:header>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0
                                    {{ $isFermenting ? 'bg-amber-100' : 'bg-agro-100' }}">
                                    <flux:icon icon="beaker" class="size-5 {{ $isFermenting ? 'text-amber-600' : 'text-agro-600' }}" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-zinc-900 truncate">
                                        @if($control->wine)
                                            <a href="{{ roleRoute('wines.show', $control->wine) }}" wire:navigate class="hover:text-agro-700">
                                                {{ $control->wine->name }}
                                            </a>
                                        @else
                                            —
                                        @endif
                                    </h3>
                                    <p class="text-xs text-zinc-400">
                                        {{ \Carbon\Carbon::parse($control->control_date)->format('d/m/Y H:i') }}
                                    </p>
                                </div>
                                @if($isFermenting)
                                    <flux:badge color="amber" size="sm" class="shrink-0">Fermentando</flux:badge>
                                @else
                                    <flux:badge color="green" size="sm" class="shrink-0">Completada</flux:badge>
                                @endif
                            </div>
                        </x-slot:header>

                        <div class="flex-1 space-y-3">
                            {{-- Depósito --}}
                            @if($control->container)
                                <div class="flex items-center gap-2 text-sm text-zinc-600">
                                    <flux:icon icon="cube" class="size-4 text-zinc-400 shrink-0" />
                                    <span class="text-sm truncate">{{ $control->container->name }}</span>
                                </div>
                            @endif

                            {{-- Parámetros en grid --}}
                            <div class="grid grid-cols-2 gap-2">
                                @if($control->temperature !== null)
                                    <div class="bg-orange-50 rounded-xl p-3">
                                        <p class="text-[10px] font-semibold text-orange-400 uppercase tracking-widest mb-0.5">Temperatura</p>
                                        <p class="text-xl font-bold text-orange-700 leading-none">
                                            {{ number_format($control->temperature, 1) }}<span class="text-sm font-normal"> °C</span>
                                        </p>
                                    </div>
                                @endif
                                @if($control->density !== null)
                                    <div class="bg-zinc-50 rounded-xl p-3">
                                        <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest mb-0.5">Densidad</p>
                                        <p class="text-xl font-bold text-zinc-700 leading-none">
                                            {{ number_format($control->density, 4) }}
                                        </p>
                                    </div>
                                @endif
                                @if($control->brix_degree !== null)
                                    <div class="bg-zinc-50 rounded-xl p-3">
                                        <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest mb-0.5">Brix</p>
                                        <p class="text-xl font-bold text-zinc-700 leading-none">
                                            {{ number_format($control->brix_degree, 1) }}<span class="text-sm font-normal"> °Bx</span>
                                        </p>
                                    </div>
                                @endif
                                @if($control->ph !== null)
                                    <div class="bg-zinc-50 rounded-xl p-3">
                                        <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest mb-0.5">pH</p>
                                        <p class="text-xl font-bold text-zinc-700 leading-none">
                                            {{ number_format($control->ph, 2) }}
                                        </p>
                                    </div>
                                @endif
                            </div>

                            {{-- Acidez volátil --}}
                            @if($control->volatile_acidity !== null)
                                <div class="flex items-center gap-2 text-xs text-zinc-500">
                                    <span class="font-semibold text-zinc-600">Ac. Volátil:</span>
                                    {{ number_format($control->volatile_acidity, 2) }} g/L
                                </div>
                            @endif
                        </div>

                        <x-slot:footer>
                            <div class="flex items-center justify-end gap-0.5">
                                <a href="{{ roleRoute('fermentation-controls.edit', $control) }}"
                                   wire:navigate
                                   title="Editar control"
                                   class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors">
                                    <flux:icon icon="pencil-square" class="size-4" />
                                </a>
                                <button
                                    wire:click="delete({{ $control->id }})"
                                    wire:confirm="¿Eliminar este control de fermentación?"
                                    wire:loading.attr="disabled"
                                    title="Eliminar control"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-red-500 hover:bg-red-50 transition-colors">
                                    <flux:icon icon="trash" class="size-4" />
                                </button>
                            </div>
                        </x-slot:footer>
                    </x-agro.card>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $controls->links() }}
            </div>
        @else
            <x-agro.empty-state
                icon="beaker"
                title="{{ $search || $wineFilter || $statusFilter || $containerFilter ? 'Ningún control coincide con los filtros' : 'Sin controles registrados' }}"
                description="{{ $search || $wineFilter || $statusFilter || $containerFilter ? 'Prueba a cambiar o limpiar los filtros aplicados.' : 'Registra los controles periódicos de fermentación de tus vinos.' }}"
            >
                @if($search || $wineFilter || $statusFilter || $containerFilter)
                    <x-slot:action>
                        <flux:button wire:click="clearFilters" variant="outline" icon="x-mark">
                            Limpiar filtros
                        </flux:button>
                    </x-slot:action>
                @else
                    <x-slot:action>
                        <flux:button href="{{ roleRoute('fermentation-controls.create') }}" wire:navigate variant="primary" icon="plus">
                            Nuevo control
                        </flux:button>
                    </x-slot:action>
                @endif
            </x-agro.empty-state>
        @endif
    </div>

</div>
