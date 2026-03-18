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

        {{-- Viticultores asignados por la DO ────────────────────────────── --}}
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
                <x-agro.data-table
                    :headers="['Viticultor', 'Parcelas activas', 'Superficie (ha)', 'Última actividad']"
                >
                    @foreach($doViticulturists as $row)
                        <x-agro.table-row>
                            <x-agro.table-cell>
                                <div class="flex items-center gap-2">
                                    <div class="size-7 rounded-full bg-blue-100 flex items-center justify-center shrink-0">
                                        <flux:icon icon="user" class="size-3.5 text-blue-600" />
                                    </div>
                                    <div>
                                        <p class="font-medium text-zinc-800 text-sm">{{ $row->viticulturist->name }}</p>
                                        @if($row->viticulturist->email && !str_starts_with($row->viticulturist->email, 'viticultores.'))
                                            <p class="text-xs text-zinc-400">{{ $row->viticulturist->email }}</p>
                                        @endif
                                    </div>
                                </div>
                            </x-agro.table-cell>
                            <x-agro.table-cell>
                                {{ $row->plot_count ?: '—' }}
                            </x-agro.table-cell>
                            <x-agro.table-cell>
                                @if($row->total_area)
                                    {{ number_format($row->total_area, 2, ',', '.') }} ha
                                @else
                                    —
                                @endif
                            </x-agro.table-cell>
                            <x-agro.table-cell>
                                @if($row->last_activity)
                                    <span class="text-zinc-500">
                                        {{ \Carbon\Carbon::parse($row->last_activity)->translatedFormat('d M Y') }}
                                    </span>
                                @else
                                    <span class="text-zinc-300">Sin actividad</span>
                                @endif
                            </x-agro.table-cell>
                        </x-agro.table-row>
                    @endforeach
                </x-agro.data-table>
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

    @endif

</div>
