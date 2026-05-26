<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="{{ __('Cuaderno de campo — DO') }}"
        :description="__('Actividades registradas por los viticultores con acceso de lectura concedido.')"
    />

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <x-agro.stat-card
            :label="__('Viticultores con acceso')"
            :value="$totalWithAccess"
            icon="book-open"
            color="agro"
        />
        <x-agro.stat-card
            :label="__('Registros mostrados')"
            :value="$activities->total()"
            icon="clipboard-document-list"
            color="blue"
        />
        <div class="rounded-xl border border-zinc-100 bg-white p-4 flex flex-col gap-1">
            <p class="text-xs text-zinc-400">{{ __('Tipos de actividad') }}</p>
            <p class="text-lg font-semibold text-zinc-800">{{ count($activityTypes) }}</p>
            <p class="text-xs text-zinc-400">{{ __('Tratamientos, riegos, podas…') }}</p>
        </div>
    </div>

    @if($totalWithAccess === 0)
        <x-agro.empty-state
            icon="book-open"
            title="{{ __('Sin acceso al cuaderno') }}"
            :description="__('Ningún viticultor tiene acceso al cuaderno concedido. Ve a gestión de accesos para habilitarlo.')"
        >
            <x-slot name="action">
                <a href="{{ route('supervisor.notebook.index') }}" wire:navigate>
                    <flux:button variant="primary" size="sm" icon="key">{{ __('Gestionar accesos') }}</flux:button>
                </a>
            </x-slot>
        </x-agro.empty-state>
    @else

        {{-- Filtros --}}
        <x-agro.filter-bar>
            <x-agro.filter-select wire:model.live="filterVit" :label="__('Viticultor')">
                <option value="">{{ __('Todos') }}</option>
                @foreach($viticulturists as $vit)
                    <option value="{{ $vit->id }}">{{ $vit->name }}</option>
                @endforeach
            </x-agro.filter-select>

            @if($filterVit && $plots->isNotEmpty())
                <x-agro.filter-select wire:model.live="filterPlot" :label="__('Parcela')">
                    <option value="">{{ __('Todas las parcelas') }}</option>
                    @foreach($plots as $plot)
                        <option value="{{ $plot->id }}">{{ $plot->name }}</option>
                    @endforeach
                </x-agro.filter-select>
            @endif

            <x-agro.filter-select wire:model.live="filterType" :label="__('Tipo')">
                <option value="">{{ __('Todos los tipos') }}</option>
                @foreach($activityTypes as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </x-agro.filter-select>

            <div class="flex items-center gap-2">
                <input type="date" wire:model.live="filterFrom"
                    class="text-sm border border-zinc-200 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-emerald-300" />
                <span class="text-xs text-zinc-400">—</span>
                <input type="date" wire:model.live="filterTo"
                    class="text-sm border border-zinc-200 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-emerald-300" />
            </div>

            <button wire:click="clearFilters" class="text-xs text-zinc-400 hover:text-zinc-600 transition px-2 py-1.5">{{ __('Limpiar') }}</button>
        </x-agro.filter-bar>

        {{-- Card Grid --}}
        @php
            $typeColors = [
                'phytosanitary' => ['bg' => 'bg-red-50', 'text' => 'text-red-700', 'border' => 'border-red-200', 'icon' => 'beaker', 'iconBg' => 'bg-red-100', 'iconText' => 'text-red-600'],
                'fertilization' => ['bg' => 'bg-yellow-50', 'text' => 'text-yellow-700', 'border' => 'border-yellow-200', 'icon' => 'sparkles', 'iconBg' => 'bg-yellow-100', 'iconText' => 'text-yellow-600'],
                'irrigation'    => ['bg' => 'bg-blue-50', 'text' => 'text-blue-700', 'border' => 'border-blue-200', 'icon' => 'cloud', 'iconBg' => 'bg-blue-100', 'iconText' => 'text-blue-600'],
                'cultural'      => ['bg' => 'bg-zinc-50', 'text' => 'text-zinc-700', 'border' => 'border-zinc-200', 'icon' => 'wrench', 'iconBg' => 'bg-zinc-100', 'iconText' => 'text-zinc-600'],
                'observation'   => ['bg' => 'bg-violet-50', 'text' => 'text-violet-700', 'border' => 'border-violet-200', 'icon' => 'eye', 'iconBg' => 'bg-violet-100', 'iconText' => 'text-violet-600'],
                'harvest'       => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200', 'icon' => 'scissors', 'iconBg' => 'bg-emerald-100', 'iconText' => 'text-emerald-600'],
                'pruning'       => ['bg' => 'bg-orange-50', 'text' => 'text-orange-700', 'border' => 'border-orange-200', 'icon' => 'scissors', 'iconBg' => 'bg-orange-100', 'iconText' => 'text-orange-600'],
                'post_harvest'  => ['bg' => 'bg-teal-50', 'text' => 'text-teal-700', 'border' => 'border-teal-200', 'icon' => 'archive-box', 'iconBg' => 'bg-teal-100', 'iconText' => 'text-teal-600'],
                'phenology'     => ['bg' => 'bg-indigo-50', 'text' => 'text-indigo-700', 'border' => 'border-indigo-200', 'icon' => 'sun', 'iconBg' => 'bg-indigo-100', 'iconText' => 'text-indigo-600'],
            ];
            $defaultTypeStyle = ['bg' => 'bg-zinc-50', 'text' => 'text-zinc-700', 'border' => 'border-zinc-200', 'icon' => 'clipboard-document-list', 'iconBg' => 'bg-zinc-100', 'iconText' => 'text-zinc-600'];
        @endphp

        @if($activities->count() > 0)
            <div
                class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
                wire:loading.class="opacity-60 pointer-events-none"
                wire:target="filterVit, filterPlot, filterType, filterFrom, filterTo, clearFilters"
            >
                @foreach($activities as $activity)
                    @php
                        $style = $typeColors[$activity->activity_type] ?? $defaultTypeStyle;
                        $delay = min($loop->index * 50, 300);
                    @endphp
                    <x-agro.card
                        class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                        style="animation-delay: {{ $delay }}ms;"
                        wire:key="activity-{{ $activity->id }}"
                    >
                        <x-slot:header>
                            <x-agro.card-item-header
                                :icon="$style['icon']"
                                :title="$activityTypes[$activity->activity_type] ?? $activity->activity_type"
                                :subtitle="$activity->activity_date->format('d/m/Y')"
                                :iconBg="$style['iconBg']"
                                :iconColor="$style['iconText']"
                                size="md"
                                radius="xl"
                            >
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs border {{ $style['bg'] }} {{ $style['text'] }} {{ $style['border'] }} shrink-0">
                                    {{ $activityTypes[$activity->activity_type] ?? $activity->activity_type }}
                                </span>
                            </x-agro.card-item-header>
                        </x-slot:header>

                        <div class="flex-1 space-y-3">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-zinc-400">{{ __('Viticultor') }}</span>
                                <span class="text-zinc-700 font-medium truncate ml-2">{{ $activity->viticulturist?->name ?? '—' }}</span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-zinc-400">{{ __('Parcela') }}</span>
                                <span class="text-zinc-600 truncate ml-2">{{ $activity->plot?->name ?? '—' }}</span>
                            </div>
                            @if($activity->notes)
                                <p class="text-xs text-zinc-400 line-clamp-2">{{ $activity->notes }}</p>
                            @endif
                        </div>
                    </x-agro.card>
                @endforeach
            </div>

            <x-agro-pagination :paginator="$activities" />
        @else
            <x-agro.empty-state
                icon="clipboard-document-list"
                title="{{ __('Sin actividades') }}"
                :description="__('No hay actividades con los filtros seleccionados.')"
            />
        @endif

    @endif

</div>
