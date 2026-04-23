<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Mi Denominación de Origen"
        description="Información sobre tu adscripción a una DO y los viticultores asignados por ella."
        icon="building-office-2"
    />

    @if(! $supervisor)
        {{-- Sin DO ──────────────────────────────────────────────────────── --}}
        <x-agro.card>
            <x-agro.empty-state
                icon="building-office-2"
                title="Sin Denominación de Origen asignada"
                description="Esta bodega aún no está adscrita a ninguna Denominación de Origen. Contacta con tu DO para que te asigne desde su panel de supervisión."
            />
        </x-agro.card>

    @else
        {{-- Header DO ───────────────────────────────────────────────────── --}}
        <x-agro.card>
            <div class="flex items-start justify-between gap-4 p-1">
                <div class="flex items-center gap-4">
                    <div class="size-12 rounded-xl bg-violet-100 flex items-center justify-center shrink-0">
                        <flux:icon icon="building-office-2" class="size-6 text-violet-600" />
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-zinc-900">{{ $supervisor->name }}</h2>
                        @if($supervisor->email)
                            <p class="text-sm text-zinc-400">{{ $supervisor->email }}</p>
                        @endif
                        @if($supervisorJoined)
                            <p class="text-xs text-zinc-400 mt-0.5">
                                Adscrita desde {{ $supervisorJoined->translatedFormat('d \d\e F \d\e Y') }}
                            </p>
                        @endif
                    </div>
                </div>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-violet-50 text-violet-700 border border-violet-200 shrink-0">
                    <span class="size-1.5 rounded-full bg-violet-500"></span>
                    Adscrita
                </span>
            </div>
        </x-agro.card>

        {{-- Tabs ────────────────────────────────────────────────────────── --}}
        <div class="border-b border-zinc-200">
            <nav class="-mb-px flex gap-1">
                @foreach([
                    ['key' => 'general',        'label' => 'General',         'icon' => 'building-office-2'],
                    ['key' => 'qualifications', 'label' => 'Calificaciones',  'icon' => 'star'],
                    ['key' => 'inspections',    'label' => 'Inspecciones',    'icon' => 'shield-check'],
                    ['key' => 'labels',         'label' => 'Contraetiquetas', 'icon' => 'tag'],
                ] as $t)
                    <button
                        wire:click="$set('tab', '{{ $t['key'] }}')"
                        class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium border-b-2 transition-colors whitespace-nowrap
                            {{ $tab === $t['key']
                                ? 'border-violet-500 text-violet-700'
                                : 'border-transparent text-zinc-500 hover:text-zinc-700 hover:border-zinc-300' }}"
                    >
                        <flux:icon icon="{{ $t['icon'] }}" class="size-4" />
                        {{ $t['label'] }}
                    </button>
                @endforeach
            </nav>
        </div>

        {{-- Tab: General ────────────────────────────────────────────────── --}}
        @if($tab === 'general')

            {{-- Viticultores asignados por la DO --}}
            <x-agro.card>
                <x-slot:header>
                    <div class="flex items-center gap-2">
                        <flux:icon icon="users" class="size-4 text-blue-500" />
                        <span>Viticultores asignados por la DO</span>
                        <span class="ml-1 text-xs text-zinc-400">({{ $doViticulturists->count() }})</span>
                    </div>
                </x-slot:header>

                @if($doViticulturists->isEmpty())
                    <x-agro.empty-state
                        icon="users"
                        title="Sin viticultores asignados"
                        description="La DO no ha asignado aún viticultores a tu bodega."
                    />
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 p-1">
                        @foreach($doViticulturists as $row)
                            @php $delay = min($loop->index * 50, 300); @endphp
                            <x-agro.card
                                class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                                style="animation-delay: {{ $delay }}ms;"
                                wire:key="vit-{{ $row->viticulturist->id }}"
                            >
                                <x-slot:header>
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center shrink-0">
                                            <flux:icon icon="user" class="size-5 text-blue-600" />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h3 class="font-bold text-zinc-900 truncate">{{ $row->viticulturist->name }}</h3>
                                            @if($row->viticulturist->email && !str_starts_with($row->viticulturist->email, 'viticultores.'))
                                                <p class="text-xs text-zinc-500">{{ $row->viticulturist->email }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </x-slot:header>

                                <div class="flex-1 space-y-4">
                                    <div class="grid grid-cols-2 gap-2">
                                        <div class="bg-agro-50 rounded-xl p-3">
                                            <p class="text-[10px] font-semibold text-agro-400 uppercase tracking-widest mb-0.5">Parcelas</p>
                                            <p class="text-2xl font-bold text-agro-700 leading-none">{{ $row->plot_count ?: '—' }}</p>
                                        </div>
                                        <div class="bg-agro-50 rounded-xl p-3">
                                            <p class="text-[10px] font-semibold text-agro-400 uppercase tracking-widest mb-0.5">Superficie</p>
                                            <p class="text-2xl font-bold text-agro-700 leading-none">
                                                @if($row->total_area)
                                                    {{ number_format($row->total_area, 2, ',', '.') }}
                                                @else
                                                    —
                                                @endif
                                            </p>
                                            @if($row->total_area)
                                                <p class="text-[10px] text-agro-400">ha</p>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="space-y-2 text-sm">
                                        <div class="flex items-center justify-between">
                                            <span class="text-zinc-400">Última actividad</span>
                                            <span class="text-zinc-700 font-medium">
                                                @if($row->last_activity)
                                                    {{ \Carbon\Carbon::parse($row->last_activity)->translatedFormat('d M Y') }}
                                                @else
                                                    Sin actividad
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </x-agro.card>
                        @endforeach
                    </div>
                @endif
            </x-agro.card>

            {{-- Módulos activos habilitados por la DO --}}
            @if($grantedAbilities->isNotEmpty())
                <x-agro.card>
                    <x-slot:header>
                        <div class="flex items-center gap-2">
                            <flux:icon icon="puzzle-piece" class="size-4 text-violet-500" />
                            <span>Módulos habilitados por la DO</span>
                        </div>
                    </x-slot:header>
                    @php $byModule = $grantedAbilities->groupBy('module'); @endphp
                    <div class="divide-y divide-zinc-100">
                        @foreach($byModule as $module => $abilities)
                            <div class="px-4 py-3">
                                <p class="text-xs font-semibold text-zinc-400 uppercase tracking-wide mb-2">{{ $module }}</p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($abilities as $ability)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-violet-50 text-violet-700 border border-violet-200">
                                            <span class="size-1.5 rounded-full bg-violet-400"></span>
                                            {{ $ability->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-agro.card>
            @endif

        @elseif($tab === 'qualifications')
            <livewire:winery.denomination.qualifications.index :embedded="true" />

        @elseif($tab === 'inspections')
            <livewire:winery.denomination.inspections.index :embedded="true" />

        @elseif($tab === 'labels')
            <livewire:winery.denomination.labels.index :embedded="true" />

        @endif

    @endif

</div>
