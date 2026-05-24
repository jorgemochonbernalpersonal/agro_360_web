<div class="space-y-6 animate-fade-in">

    {{-- Header + selector de campaña --}}
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
        <x-agro.page-header
            :title="__('Cuaderno de Campo')"
            :description="$currentCampaign ? $currentCampaign->name : __('Selecciona una campaña para ver las actividades')"
        />

        @if($campaigns->count() > 1)
            <div class="flex items-center gap-2 self-start shrink-0">
                <flux:icon icon="calendar-days" class="size-4 text-zinc-400 shrink-0" />
                <select wire:model.live="selectedCampaign"
                        class="text-sm text-zinc-700 bg-white border border-zinc-200 rounded-lg px-2 py-1.5 pr-7 focus:outline-none focus:ring-1 focus:ring-agro-400 focus:border-agro-400 appearance-none cursor-pointer">
                    @foreach($campaigns as $campaign)
                        <option value="{{ $campaign->id }}">
                            {{ $campaign->name }}{{ $campaign->active ? '' : ' ('.__('inactiva').')' }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif
    </div>

    @if($currentCampaign)

        {{-- Stats de la campaña --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <x-agro.stat-card
                :label="__('Total actividades')"
                :value="$stats['total']"
                icon="book-open"
                color="agro"
            />
            <x-agro.stat-card
                :label="__('Tratamientos')"
                :value="$stats['phytosanitary']"
                icon="shield-exclamation"
                color="red"
            />
            <x-agro.stat-card
                :label="__('Fertilizaciones')"
                :value="$stats['fertilization']"
                icon="beaker"
                color="blue"
            />
            <x-agro.stat-card
                :label="__('Riegos')"
                :value="$stats['irrigation']"
                icon="cloud"
                color="cyan"
            />
        </div>

        {{-- Secciones del cuaderno --}}
        <x-agro.card>
            <x-slot:header>
                <div class="flex items-center gap-2">
                    <flux:icon icon="squares-2x2" class="size-4 text-agro-600" />
                    <span class="text-sm font-semibold text-zinc-900">{{ __('Secciones del cuaderno') }}</span>
                </div>
            </x-slot:header>

            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3">

                {{-- Tratamientos fitosanitarios --}}
                <a href="{{ roleRoute('viticulturist.digital-notebook.treatment.index') }}" wire:navigate
                   class="group flex items-center gap-4 p-4 rounded-xl border border-zinc-100 hover:border-red-200 hover:bg-red-50 transition-all">
                    <div class="w-10 h-10 bg-red-100 rounded-xl flex items-center justify-center shrink-0 group-hover:bg-red-200 transition-colors">
                        <flux:icon icon="shield-exclamation" class="size-5 text-red-600" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-zinc-900">{{ __('Tratamientos Fitosanitarios') }}</p>
                        <p class="text-xs text-zinc-500 mt-0.5">{{ $stats['phytosanitary'] }} {{ __('registros') }}</p>
                    </div>
                    <flux:icon icon="chevron-right" class="size-4 text-zinc-300 group-hover:text-red-400 shrink-0 transition-colors" />
                </a>

                {{-- Fertilización --}}
                <a href="{{ roleRoute('viticulturist.digital-notebook.fertilization.index') }}" wire:navigate
                   class="group flex items-center gap-4 p-4 rounded-xl border border-zinc-100 hover:border-blue-200 hover:bg-blue-50 transition-all">
                    <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center shrink-0 group-hover:bg-blue-200 transition-colors">
                        <flux:icon icon="beaker" class="size-5 text-blue-600" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-zinc-900">{{ __('Fertilización') }}</p>
                        <p class="text-xs text-zinc-500 mt-0.5">{{ $stats['fertilization'] }} {{ __('registros') }}</p>
                    </div>
                    <flux:icon icon="chevron-right" class="size-4 text-zinc-300 group-hover:text-blue-400 shrink-0 transition-colors" />
                </a>

                {{-- Riego --}}
                <a href="{{ roleRoute('viticulturist.digital-notebook.irrigation.index') }}" wire:navigate
                   class="group flex items-center gap-4 p-4 rounded-xl border border-zinc-100 hover:border-cyan-200 hover:bg-cyan-50 transition-all">
                    <div class="w-10 h-10 bg-cyan-100 rounded-xl flex items-center justify-center shrink-0 group-hover:bg-cyan-200 transition-colors">
                        <flux:icon icon="cloud" class="size-5 text-cyan-600" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-zinc-900">{{ __('Riego') }}</p>
                        <p class="text-xs text-zinc-500 mt-0.5">{{ $stats['irrigation'] }} {{ __('registros') }}</p>
                    </div>
                    <flux:icon icon="chevron-right" class="size-4 text-zinc-300 group-hover:text-cyan-400 shrink-0 transition-colors" />
                </a>

                {{-- Labores culturales --}}
                <a href="{{ roleRoute('viticulturist.digital-notebook.cultural.index') }}" wire:navigate
                   class="group flex items-center gap-4 p-4 rounded-xl border border-zinc-100 hover:border-amber-200 hover:bg-amber-50 transition-all">
                    <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center shrink-0 group-hover:bg-amber-200 transition-colors">
                        <flux:icon icon="wrench-screwdriver" class="size-5 text-amber-600" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-zinc-900">{{ __('Labores Culturales') }}</p>
                        <p class="text-xs text-zinc-500 mt-0.5">{{ $stats['cultural'] }} {{ __('registros') }}</p>
                    </div>
                    <flux:icon icon="chevron-right" class="size-4 text-zinc-300 group-hover:text-amber-400 shrink-0 transition-colors" />
                </a>

                {{-- Observaciones --}}
                <a href="{{ roleRoute('viticulturist.digital-notebook.observation.index') }}" wire:navigate
                   class="group flex items-center gap-4 p-4 rounded-xl border border-zinc-100 hover:border-zinc-300 hover:bg-zinc-50 transition-all">
                    <div class="w-10 h-10 bg-zinc-100 rounded-xl flex items-center justify-center shrink-0 group-hover:bg-zinc-200 transition-colors">
                        <flux:icon icon="eye" class="size-5 text-zinc-600" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-zinc-900">{{ __('Observaciones') }}</p>
                        <p class="text-xs text-zinc-500 mt-0.5">{{ $stats['observation'] }} {{ __('registros') }}</p>
                    </div>
                    <flux:icon icon="chevron-right" class="size-4 text-zinc-300 group-hover:text-zinc-500 shrink-0 transition-colors" />
                </a>

                {{-- Cosecha --}}
                <a href="{{ roleRoute('viticulturist.digital-notebook.harvest.create') }}" wire:navigate
                   class="group flex items-center gap-4 p-4 rounded-xl border border-zinc-100 hover:border-purple-200 hover:bg-purple-50 transition-all">
                    <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center shrink-0 group-hover:bg-purple-200 transition-colors">
                        <flux:icon icon="scissors" class="size-5 text-purple-600" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-zinc-900">{{ __('Cosecha / Vendimia') }}</p>
                        <p class="text-xs text-zinc-500 mt-0.5">{{ $stats['harvest'] }} {{ __('registros') }}</p>
                    </div>
                    <flux:icon icon="chevron-right" class="size-4 text-zinc-300 group-hover:text-purple-400 shrink-0 transition-colors" />
                </a>

                {{-- Podas --}}
                <a href="{{ roleRoute('viticulturist.digital-notebook.pruning.index') }}" wire:navigate
                   class="group flex items-center gap-4 p-4 rounded-xl border border-zinc-100 hover:border-lime-200 hover:bg-lime-50 transition-all">
                    <div class="w-10 h-10 bg-lime-100 rounded-xl flex items-center justify-center shrink-0 group-hover:bg-lime-200 transition-colors">
                        <flux:icon icon="scissors" class="size-5 text-lime-600" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-zinc-900">{{ __('Podas') }}</p>
                        <p class="text-xs text-zinc-500 mt-0.5">{{ __('Labores de poda') }}</p>
                    </div>
                    <flux:icon icon="chevron-right" class="size-4 text-zinc-300 group-hover:text-lime-500 shrink-0 transition-colors" />
                </a>

                {{-- Post-vendimia --}}
                <a href="{{ roleRoute('viticulturist.digital-notebook.post-harvest.index') }}" wire:navigate
                   class="group flex items-center gap-4 p-4 rounded-xl border border-zinc-100 hover:border-indigo-200 hover:bg-indigo-50 transition-all">
                    <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center shrink-0 group-hover:bg-indigo-200 transition-colors">
                        <flux:icon icon="beaker" class="size-5 text-indigo-600" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-zinc-900">{{ __('Tratamientos Post-Vendimia') }}</p>
                        <p class="text-xs text-zinc-500 mt-0.5">{{ __('Tratamientos tras la cosecha') }}</p>
                    </div>
                    <flux:icon icon="chevron-right" class="size-4 text-zinc-300 group-hover:text-indigo-400 shrink-0 transition-colors" />
                </a>

                {{-- Rendimientos estimados --}}
                <a href="{{ roleRoute('viticulturist.digital-notebook.estimated-yields.index') }}" wire:navigate
                   class="group flex items-center gap-4 p-4 rounded-xl border border-zinc-100 hover:border-emerald-200 hover:bg-emerald-50 transition-all">
                    <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center shrink-0 group-hover:bg-emerald-200 transition-colors">
                        <flux:icon icon="chart-bar" class="size-5 text-emerald-600" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-zinc-900">{{ __('Rendimientos Estimados') }}</p>
                        <p class="text-xs text-zinc-500 mt-0.5">{{ __('Previsión de producción') }}</p>
                    </div>
                    <flux:icon icon="chevron-right" class="size-4 text-zinc-300 group-hover:text-emerald-500 shrink-0 transition-colors" />
                </a>

            </div>
        </x-agro.card>

    @else
        <x-agro.empty-state
            icon="book-open"
            :message="__('Sin campaña activa')"
            :description="__('No se encontró ninguna campaña activa. Crea una para empezar a registrar actividades.')"
        >
            <x-slot:action>
                <flux:button href="{{ roleRoute('viticulturist.campaign.index') }}" wire:navigate variant="primary" icon="plus">
                    {{ __('Gestionar campañas') }}
                </flux:button>
            </x-slot:action>
        </x-agro.empty-state>
    @endif

</div>
