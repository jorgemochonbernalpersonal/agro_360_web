<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="{{ __('Línea de tiempo de vinos') }}"
        :description="__('Estado actual de todos los vinos en proceso, crianza y embotellados.')"
    >
        <flux:button icon="list-bullet" href="{{ roleRoute('wines.index') }}" wire:navigate variant="ghost" size="sm">
            Ver lista
        </flux:button>
        <flux:button icon="plus" href="{{ roleRoute('wines.create') }}" wire:navigate variant="primary" size="sm">
            Nuevo vino
        </flux:button>
    </x-agro.page-header>

    {{-- Filtros --}}
    <x-agro.filter-bar>
        <flux:select wire:model.live="vintageFilter" size="sm" class="min-w-32">
            <option value="">{{ __('Todas las añadas') }}</option>
            @foreach($vintages as $v)
                <option value="{{ $v }}">{{ $v }}</option>
            @endforeach
        </flux:select>

        <flux:select wire:model.live="typeFilter" size="sm" class="min-w-40">
            <option value="">{{ __('Todos los tipos') }}</option>
            @foreach($types as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </flux:select>
    </x-agro.filter-bar>

    @if($grouped->isEmpty())
        <x-agro.empty-state
            icon="beaker"
            title="{{ __('No hay vinos activos') }}"
            :description="__('No hay vinos en proceso, crianza o embotellado con los filtros seleccionados.')"
        />
    @else
        {{-- Columnas kanban por estado --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            @foreach(['in_progress' => 'En Proceso', 'aged' => 'En Crianza', 'bottled' => 'Embotellado'] as $status => $label)
                @php
                    $wines = $grouped->get($status, collect());
                    $headerColor = match($status) {
                        'in_progress' => 'bg-blue-500',
                        'aged'        => 'bg-amber-500',
                        'bottled'     => 'bg-agro-500',
                    };
                    $badgeColor = match($status) {
                        'in_progress' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
                        'aged'        => 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300',
                        'bottled'     => 'bg-agro-100 text-agro-800 dark:bg-agro-900/30 dark:text-agro-300',
                    };
                @endphp

                <div class="flex flex-col gap-3">
                    {{-- Cabecera columna --}}
                    <div class="flex items-center gap-2 pb-2 border-b-2 {{ $headerColor }} border-opacity-30">
                        <span class="w-3 h-3 rounded-full {{ $headerColor }}"></span>
                        <h3 class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $label }}</h3>
                        <span class="ml-auto text-xs font-medium px-2 py-0.5 rounded-full {{ $badgeColor }}">
                            {{ $wines->count() }}
                        </span>
                    </div>

                    @forelse($wines as $wine)
                        @php
                            $typeColors = [
                                'red'       => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
                                'white'     => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
                                'rose'      => 'bg-pink-100 text-pink-800 dark:bg-pink-900/30 dark:text-pink-300',
                                'sparkling' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300',
                                'default'   => 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300',
                            ];
                            $typeColor = $typeColors[$wine->wine_type] ?? $typeColors['default'];
                        @endphp

                        <a href="{{ roleRoute('wines.show', $wine) }}" wire:navigate
                           class="block rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-4 hover:shadow-md hover:border-zinc-300 dark:hover:border-zinc-600 transition-all group">

                            {{-- Nombre + añada --}}
                            <div class="flex items-start justify-between gap-2 mb-2">
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-zinc-900 dark:text-zinc-100 text-sm group-hover:text-agro-700 dark:group-hover:text-agro-400 truncate">
                                        {{ $wine->name }}
                                    </p>
                                    @if($wine->vintage)
                                        <p class="text-xs text-zinc-400">Añada {{ $wine->vintage }}</p>
                                    @endif
                                </div>
                                <span class="text-xs px-1.5 py-0.5 rounded {{ $typeColor }} flex-shrink-0">
                                    {{ $wine->type_label }}
                                </span>
                            </div>

                            {{-- Fase actual --}}
                            <div class="flex items-center gap-1.5 text-xs text-zinc-500 dark:text-zinc-400 mb-3">
                                <flux:icon icon="arrow-path" class="w-3.5 h-3.5 flex-shrink-0" />
                                <span class="truncate">{{ $wine->current_phase_label }}</span>
                            </div>

                            {{-- Días y volumen --}}
                            <div class="grid grid-cols-2 gap-2 text-xs">
                                <div class="bg-zinc-50 dark:bg-zinc-800 rounded-lg px-2 py-1.5 text-center">
                                    <p class="font-bold text-zinc-900 dark:text-zinc-100 text-base leading-tight">
                                        {{ $wine->days_in_phase }}
                                    </p>
                                    <p class="text-zinc-400">{{ __('días en fase') }}</p>
                                </div>
                                <div class="bg-zinc-50 dark:bg-zinc-800 rounded-lg px-2 py-1.5 text-center">
                                    <p class="font-bold text-zinc-900 dark:text-zinc-100 text-base leading-tight">
                                        {{ $wine->days_total }}
                                    </p>
                                    <p class="text-zinc-400">{{ __('días total') }}</p>
                                </div>
                            </div>

                            {{-- Volumen + enólogo --}}
                            <div class="flex items-center justify-between mt-3 pt-3 border-t border-zinc-100 dark:border-zinc-800">
                                @if($wine->volume_liters)
                                    <span class="text-xs text-zinc-400">
                                        <span class="font-medium text-zinc-600 dark:text-zinc-300">{{ number_format($wine->volume_liters, 0) }}</span> L
                                    </span>
                                @endif
                                @if($wine->oenologist)
                                    <span class="text-xs text-zinc-400 truncate ml-auto">
                                        {{ $wine->oenologist->name }}
                                    </span>
                                @endif
                            </div>

                            {{-- Pasos de proceso --}}
                            @if($wine->processDetails->count() > 0)
                                <div class="mt-3 flex items-center gap-1 overflow-hidden">
                                    @foreach($wine->processDetails->take(6) as $i => $step)
                                        <div class="w-3 h-3 rounded-full {{ $i === $wine->processDetails->count() - 1 ? 'bg-agro-500 ring-2 ring-agro-200 dark:ring-agro-800' : 'bg-zinc-200 dark:bg-zinc-700' }}"
                                             title="{{ $step->process_type_label }}">
                                        </div>
                                        @if(!$loop->last)
                                            <div class="flex-1 h-0.5 bg-zinc-100 dark:bg-zinc-800 max-w-4"></div>
                                        @endif
                                    @endforeach
                                    @if($wine->processDetails->count() > 6)
                                        <span class="text-xs text-zinc-400 ml-1">+{{ $wine->processDetails->count() - 6 }}</span>
                                    @endif
                                </div>
                            @endif
                        </a>
                    @empty
                        <div class="rounded-xl border-2 border-dashed border-zinc-200 dark:border-zinc-700 p-6 text-center">
                            <p class="text-sm text-zinc-400">{{ __('Sin vinos en esta fase') }}</p>
                        </div>
                    @endforelse
                </div>
            @endforeach
        </div>
    @endif
</div>
