<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Fenología"
        description="Registro de estadios fenológicos por plantación y campaña"
        icon="sun"
    >
        @unless($filter_planting_id)
            <x-slot:actions>
                <flux:button href="{{ route('viticulturist.phenology.create') }}" variant="primary" icon="plus">
                    Registrar Estadio
                </flux:button>
            </x-slot:actions>
        @endunless
    </x-agro.page-header>

    {{-- Contexto de plantación cuando se filtra por ella --}}
    @if($filter_planting_id && $filteredPlanting)
        <div class="flex items-center justify-between px-4 py-3 bg-zinc-50 border border-zinc-200 rounded-xl">
            <div class="flex items-center gap-3">
                <flux:icon icon="scissors" class="size-4 text-zinc-400 shrink-0" />
                <div>
                    <span class="text-sm font-medium text-zinc-900">{{ $filteredPlanting->plot->name }}</span>
                    <span class="text-sm text-zinc-500"> — {{ $filteredPlanting->grapeVariety->name ?? $filteredPlanting->name ?? 'Sin nombre' }}</span>
                </div>
            </div>
            <flux:button href="{{ route('plots.plantings.index') }}" variant="ghost" size="sm" icon="arrow-left">
                Volver a plantaciones
            </flux:button>
        </div>
    @endif

    {{-- Filtro de campaña: solo en vista general --}}
    @unless($filter_planting_id)
        <div class="flex items-center gap-3">
            <flux:select wire:model.live="filter_campaign_id" class="w-48">
                <flux:select.option value="">Todas las campañas</flux:select.option>
                @foreach($campaigns as $campaign)
                    <flux:select.option value="{{ $campaign->id }}">Campaña {{ $campaign->year }}</flux:select.option>
                @endforeach
            </flux:select>
            @if($filter_campaign_id)
                <flux:button wire:click="$set('filter_campaign_id', '')" variant="ghost" size="sm" icon="x-mark">Limpiar</flux:button>
            @endif
        </div>
    @endunless

    {{-- Grid de cards --}}
    @if($observations->isEmpty())
        <x-agro.empty-state
            icon="sun"
            title="Sin observaciones fenológicas"
            description="Registra los estadios fenológicos de tus plantaciones para hacer seguimiento de la campaña."
        >
            @if($filter_planting_id)
                <x-slot:action>
                    <flux:button href="{{ route('viticulturist.phenology.create', ['planting_id' => $filter_planting_id]) }}" variant="primary" icon="plus">
                        Registrar primer estadio
                    </flux:button>
                </x-slot:action>
            @endif
        </x-agro.empty-state>
    @else
        @php
            $eventColors = [
                'budbreak'    => ['badge' => 'green',  'bg' => 'bg-green-100',  'icon' => 'text-green-600'],
                'shoot_growth'=> ['badge' => 'green',  'bg' => 'bg-green-100',  'icon' => 'text-green-600'],
                'flowering'   => ['badge' => 'yellow', 'bg' => 'bg-yellow-100', 'icon' => 'text-yellow-600'],
                'fruit_set'   => ['badge' => 'yellow', 'bg' => 'bg-yellow-100', 'icon' => 'text-yellow-600'],
                'veraison'    => ['badge' => 'purple', 'bg' => 'bg-purple-100', 'icon' => 'text-purple-600'],
                'pre_harvest' => ['badge' => 'orange', 'bg' => 'bg-orange-100', 'icon' => 'text-orange-600'],
                'harvest'     => ['badge' => 'red',    'bg' => 'bg-red-100',    'icon' => 'text-red-600'],
            ];
        @endphp
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($observations as $obs)
                @php
                    $delay = min($loop->index * 50, 300);
                    $config = $eventColors[$obs->event] ?? ['badge' => 'blue', 'bg' => 'bg-blue-100', 'icon' => 'text-blue-600'];
                    $confidenceColor = $obs->confidence >= 80 ? 'text-agro-700' : ($obs->confidence >= 50 ? 'text-amber-700' : 'text-red-700');
                @endphp
                <x-agro.card
                    class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                    style="animation-delay: {{ $delay }}ms;"
                    wire:key="pheno-{{ $obs->id }}"
                >
                    <x-slot:header>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 {{ $config['bg'] }}">
                                <flux:icon icon="sun" class="size-5 {{ $config['icon'] }}" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-bold text-zinc-900 truncate">{{ $obs->plotPlanting->plot->name ?? '—' }}</h3>
                                <p class="text-xs text-zinc-400">{{ $obs->obs_date->format('d/m/Y') }}</p>
                            </div>
                            <flux:badge color="{{ $config['badge'] }}" size="sm" class="shrink-0">{{ $obs->event_label }}</flux:badge>
                        </div>
                    </x-slot:header>

                    <div class="flex-1 space-y-3">
                        @if($obs->plotPlanting->grapeVariety)
                            <div class="flex items-center gap-2 text-xs text-zinc-500">
                                <flux:icon icon="scissors" class="size-3.5 text-zinc-400 shrink-0" />
                                <span>{{ $obs->plotPlanting->grapeVariety->name }}</span>
                            </div>
                        @endif

                        <div class="grid grid-cols-2 gap-2">
                            <div class="bg-zinc-50 rounded-xl p-3">
                                <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest mb-0.5">Confianza</p>
                                <p class="text-xl font-bold {{ $confidenceColor }} leading-none">{{ $obs->confidence }}<span class="text-xs font-normal text-zinc-400 ml-0.5">%</span></p>
                            </div>
                            @if($obs->degree_days_accumulated)
                                <div class="bg-zinc-50 rounded-xl p-3">
                                    <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest mb-0.5">GD Acum.</p>
                                    <p class="text-xl font-bold text-zinc-700 leading-none">{{ number_format($obs->degree_days_accumulated, 0) }}</p>
                                </div>
                            @endif
                        </div>

                        <div class="flex items-center gap-2 flex-wrap">
                            <flux:badge color="zinc" size="sm">{{ $obs->source_label }}</flux:badge>
                            @if($obs->bbch_code)
                                <code class="text-xs bg-zinc-100 px-2 py-0.5 rounded font-mono">BBCH: {{ $obs->bbch_code }}</code>
                            @endif
                        </div>
                    </div>

                    <x-slot:footer>
                        <div class="flex items-center justify-end gap-0.5">
                            <a href="{{ route('viticulturist.phenology.edit', $obs->id) }}"
                               title="Editar"
                               class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors">
                                <flux:icon icon="pencil-square" class="size-4" />
                            </a>
                            <button
                                wire:click="delete({{ $obs->id }})"
                                wire:confirm="¿Eliminar esta observación?"
                                title="Eliminar"
                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-red-500 hover:bg-red-50 transition-colors">
                                <flux:icon icon="trash" class="size-4" />
                            </button>
                        </div>
                    </x-slot:footer>
                </x-agro.card>
            @endforeach
        </div>
    @endif

</div>
