<div class="space-y-6 animate-fade-in">

    {{-- Header --}}
    <div class="flex items-start justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-sm text-zinc-400 mb-1">
                <a href="{{ route('supervisor.oversight.wineries.index') }}" wire:navigate
                   class="hover:text-zinc-600 transition">Supervisión — Bodegas</a>
                <flux:icon icon="chevron-right" class="size-3" />
                <span class="text-zinc-600">{{ $winery->name }}</span>
            </div>
            <h1 class="text-2xl font-semibold text-zinc-900">{{ $winery->name }}</h1>
            @if($winery->email)
                <p class="text-sm text-zinc-400 mt-0.5">{{ $winery->email }}</p>
            @endif
        </div>

        <div class="flex items-center gap-2 shrink-0">
            @if($winery->can_login)
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-green-50 text-green-700 border border-green-200">
                    <span class="size-1.5 rounded-full bg-green-500"></span>
                    Activa
                </span>
                <flux:button
                    wire:click="toggleAccess"
                    wire:confirm="{{ __('¿Desactivar el acceso a esta bodega? No podrá iniciar sesión hasta que lo reactives.') }}"
                    variant="danger"
                    size="sm"
                    icon="lock-closed"
                >
                    Desactivar acceso
                </flux:button>
            @else
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-zinc-100 text-zinc-500 border border-zinc-200">
                    <span class="size-1.5 rounded-full bg-zinc-400"></span>
                    Sin acceso
                </span>
                <flux:button
                    wire:click="toggleAccess"
                    wire:confirm="{{ __('¿Restaurar el acceso a esta bodega?') }}"
                    variant="primary"
                    size="sm"
                    icon="lock-open"
                >
                    Activar acceso
                </flux:button>
            @endif
        </div>
    </div>

    {{-- Stats de la vendimia actual --}}
    <div class="grid grid-cols-2 gap-4">
        <x-agro.stat-card
            :label="__('Viticultores DO')"
            :value="$viticulturistRelations->count()"
            icon="users"
            color="blue"
        />
        <x-agro.stat-card
            :label="'Uva recibida ' . $currentVintage"
            :value="number_format($vintageStats->total_kg ?? 0, 0, ',', '.') . ' kg'"
            icon="scale"
            color="agro"
        />
        <x-agro.stat-card
            :label="'Recepciones ' . $currentVintage"
            :value="$vintageStats->reception_count ?? 0"
            icon="inbox"
            color="yellow"
        />
        <x-agro.stat-card
            :label="__('Contenedores')"
            :value="$containerCount"
            icon="beaker"
            color="purple"
        />
    </div>

    {{-- Viticultores asignados por la DO --}}
    <x-agro.card>
        <x-slot:header>
            <div class="flex items-center justify-between w-full">
                <div class="flex items-center gap-2">
                    <flux:icon icon="users" class="size-4 text-blue-500" />
                    <span>{{ __('Viticultores asignados por esta DO') }}</span>
                </div>
                <flux:button wire:click="openAssignModal" variant="ghost" size="sm" icon="user-plus">{{ __('Asignar viticultor') }}</flux:button>
            </div>
        </x-slot:header>

        @if($viticulturistRelations->isEmpty())
            <x-agro.empty-state
                icon="users"
                title="{{ __('Sin viticultores asignados') }}"
                :description="__('Esta bodega no tiene viticultores aportados por tu denominación.')"
            />
        @else
            <x-agro.data-table
                :headers="['Viticultor', 'Parcelas activas', 'Superficie (ha)', 'Última actividad', '']"
            >
                @foreach($viticulturistRelations as $rel)
                    @php
                        $vit   = $rel->viticulturist;
                        $stats = $plotStatsByVit[$vit->id] ?? null;
                        $last  = $lastActivityByVit[$vit->id] ?? null;
                    @endphp
                    <x-agro.table-row>
                        <x-agro.table-cell>
                            <div class="flex items-center gap-2">
                                <div class="size-7 rounded-full bg-blue-100 flex items-center justify-center shrink-0">
                                    <flux:icon icon="user" class="size-3.5 text-blue-600" />
                                </div>
                                <div>
                                    <p class="font-medium text-zinc-800 text-sm">{{ $vit->name }}</p>
                                    @if($vit->email && !str_starts_with($vit->email, 'viticultores.'))
                                        <p class="text-xs text-zinc-400">{{ $vit->email }}</p>
                                    @endif
                                </div>
                            </div>
                        </x-agro.table-cell>
                        <x-agro.table-cell>
                            {{ $stats?->plot_count ?? '—' }}
                        </x-agro.table-cell>
                        <x-agro.table-cell>
                            @if($stats?->total_area)
                                {{ number_format($stats->total_area, 2, ',', '.') }} ha
                            @else
                                —
                            @endif
                        </x-agro.table-cell>
                        <x-agro.table-cell>
                            @if($last)
                                <span class="text-zinc-500">
                                    {{ \Carbon\Carbon::parse($last)->translatedFormat('d M Y') }}
                                </span>
                            @else
                                <span class="text-zinc-300">{{ __('Sin actividad') }}</span>
                            @endif
                        </x-agro.table-cell>
                        <x-agro.table-cell align="right">
                            <flux:button
                                wire:click="unassignViticulturist({{ $vit->id }})"
                                wire:confirm="{{ __('¿Retirar a :name de esta bodega?', ['name' => $vit->name]) }}"
                                variant="ghost"
                                size="sm"
                                icon="user-minus"
                            >
                                Retirar
                            </flux:button>
                        </x-agro.table-cell>
                    </x-agro.table-row>
                @endforeach
            </x-agro.data-table>
        @endif
    </x-agro.card>

    {{-- Modal asignar viticultor --}}
    <flux:modal wire:model="showAssignModal" name="assign-viticulturist" class="w-full max-w-lg">
        <div class="p-6 space-y-4">
            <div>
                <h3 class="text-base font-semibold text-zinc-900">{{ __('Asignar viticultor') }}</h3>
                <p class="text-sm text-zinc-500 mt-0.5">{{ __('Viticultores de tu pool disponibles para asignar a esta bodega.') }}</p>
            </div>

            <flux:input wire:model.live="poolSearch" :placeholder="__('Buscar por nombre o email…')" icon="magnifying-glass" />

            @if($poolViticulturists->isEmpty())
                <p class="text-sm text-zinc-400 text-center py-6">
                    {{ $poolSearch ? 'Sin resultados para esta búsqueda.' : 'Todos los viticultores del pool ya están asignados a esta bodega.' }}
                </p>
            @else
                <div class="divide-y divide-zinc-100 max-h-72 overflow-y-auto -mx-6 px-6">
                    @foreach($poolViticulturists as $vit)
                        <div class="flex items-center justify-between py-3">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div class="size-7 rounded-full bg-blue-50 flex items-center justify-center shrink-0">
                                    <flux:icon icon="user" class="size-3.5 text-blue-500" />
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-zinc-800 truncate">{{ $vit->name }}</p>
                                    @if($vit->email && !str_starts_with($vit->email, 'viticultores.'))
                                        <p class="text-xs text-zinc-400 truncate">{{ $vit->email }}</p>
                                    @endif
                                </div>
                            </div>
                            <flux:button
                                wire:click="assignViticulturist({{ $vit->id }})"
                                variant="primary"
                                size="sm"
                                class="shrink-0 ml-3"
                            >
                                Asignar
                            </flux:button>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="flex justify-end pt-2">
                <flux:button wire:click="closeAssignModal" variant="ghost">{{ __('Cerrar') }}</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Recepciones de vendimia --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Últimas recepciones --}}
        <div class="lg:col-span-2">
            <x-agro.card>
                <x-slot:header>
                    <div class="flex items-center justify-between w-full">
                        <div class="flex items-center gap-2">
                            <flux:icon icon="inbox-arrow-down" class="size-4 text-agro-500" />
                            <span>{{ __('Últimas recepciones') }}</span>
                        </div>
                        @if($vintageStats?->avg_baume > 0)
                            <span class="text-xs text-zinc-400">
                                Grado medio: {{ number_format($vintageStats->avg_baume, 1, ',', '.') }} °Bé
                            </span>
                        @endif
                    </div>
                </x-slot:header>

                @if($recentReceptions->isEmpty())
                    <x-agro.empty-state
                        icon="inbox"
                        title="{{ __('Sin recepciones registradas') }}"
                        :description="__('Esta bodega no tiene recepciones de vendimia en el sistema.')"
                    />
                @else
                    <div class="divide-y divide-zinc-100">
                        @foreach($recentReceptions as $reception)
                            <div class="flex items-center justify-between px-4 py-3 text-sm">
                                <div class="flex items-center gap-3">
                                    <div class="text-zinc-400 text-xs w-20 shrink-0">
                                        {{ \Carbon\Carbon::parse($reception->harvest_start_date)->format('d/m/Y') }}
                                    </div>
                                    <div>
                                        <span class="font-medium text-zinc-800">
                                            {{ number_format($reception->total_weight, 0, ',', '.') }} kg
                                        </span>
                                        @if($reception->vintage)
                                            <span class="text-xs text-zinc-400 ml-1">Vend. {{ $reception->vintage }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    @if($reception->baume_degree)
                                        <span class="text-xs text-zinc-500">
                                            {{ number_format($reception->baume_degree, 1, ',', '.') }} °Bé
                                        </span>
                                    @endif
                                    @if($reception->status === 'cancelled')
                                        <x-agro.status-badge status="cancelled" :label="__('Cancelada')" color="red" />
                                    @elseif($reception->status === 'disputed')
                                        <x-agro.status-badge status="disputed" :label="__('En disputa')" color="yellow" />
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-agro.card>
        </div>

        {{-- Desglose por variedad --}}
        <div>
            <x-agro.card>
                <x-slot:header>
                    <div class="flex items-center gap-2">
                        <flux:icon icon="chart-pie" class="size-4 text-purple-500" />
                        <span>Por variedad ({{ $currentVintage }})</span>
                    </div>
                </x-slot:header>

                @if($varietyBreakdown->isEmpty())
                    <p class="text-sm text-zinc-400 px-4 py-6 text-center">{{ __('Sin datos de variedad') }}</p>
                @else
                    @php $totalKg = $varietyBreakdown->sum('total_kg'); @endphp
                    <div class="divide-y divide-zinc-100">
                        @foreach($varietyBreakdown as $row)
                            @php $pct = $totalKg > 0 ? round(($row->total_kg / $totalKg) * 100) : 0; @endphp
                            <div class="px-4 py-3">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm font-medium text-zinc-700">{{ $row->variety }}</span>
                                    <span class="text-xs text-zinc-400">{{ $pct }}%</span>
                                </div>
                                <div class="w-full bg-zinc-100 rounded-full h-1.5">
                                    <div class="bg-agro-500 h-1.5 rounded-full" style="width: {{ $pct }}%"></div>
                                </div>
                                <p class="text-xs text-zinc-400 mt-1">
                                    {{ number_format($row->total_kg, 0, ',', '.') }} kg · {{ $row->receptions }} recep.
                                </p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-agro.card>
        </div>

    </div>

    {{-- Módulos activos (Abilities) ─────────────────────────────────────────── --}}
    <x-agro.card>
        <x-slot:header>
            <div class="flex items-center justify-between w-full">
                <div class="flex items-center gap-2">
                    <flux:icon icon="puzzle-piece" class="size-4 text-violet-500" />
                    <span>{{ __('Módulos activos') }}</span>
                </div>
                @if($grantedAbilityIds->isEmpty())
                    <span class="text-xs text-zinc-400 italic">{{ __('Sin restricciones — acceso total') }}</span>
                @else
                    <span class="text-xs text-zinc-500">{{ $grantedAbilityIds->count() }} de {{ $allAbilities->count() }} módulos habilitados</span>
                @endif
            </div>
        </x-slot:header>

        @if($allAbilities->isEmpty())
            <x-agro.empty-state
                icon="puzzle-piece"
                title="{{ __('Sin módulos configurados') }}"
                :description="__('Ejecuta el AbilitySeeder para cargar los módulos disponibles.')"
            />
        @else
            @php $byModule = $allAbilities->groupBy('module'); @endphp
            <div class="divide-y divide-zinc-100">
                @foreach($byModule as $module => $abilities)
                    <div class="px-4 py-3">
                        <p class="text-xs font-semibold text-zinc-400 uppercase tracking-wide mb-2">{{ $module }}</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @foreach($abilities as $ability)
                                @php $granted = $grantedAbilityIds->contains($ability->id); @endphp
                                <div class="flex items-center justify-between gap-3 py-1.5 px-3 rounded-lg
                                    {{ $granted ? 'bg-violet-50' : 'bg-zinc-50' }}">
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-zinc-800 truncate">{{ $ability->name }}</p>
                                        @if($ability->description)
                                            <p class="text-xs text-zinc-400 truncate">{{ $ability->description }}</p>
                                        @endif
                                    </div>
                                    <button
                                        wire:click="toggleAbility({{ $ability->id }})"
                                        class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent
                                            transition-colors duration-200 focus:outline-none
                                            {{ $granted ? 'bg-violet-500' : 'bg-zinc-300' }}"
                                        role="switch"
                                        aria-checked="{{ $granted ? 'true' : 'false' }}"
                                    >
                                        <span class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow
                                            transition duration-200 {{ $granted ? 'translate-x-4' : 'translate-x-0' }}">
                                        </span>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            @if(! $grantedAbilityIds->isEmpty())
                <div class="px-4 pb-3 pt-1">
                    <flux:callout variant="info" icon="information-circle" class="text-xs">
                        Solo los módulos activados estarán disponibles para esta bodega. Si no hay ninguno marcado, la bodega tiene acceso completo.
                    </flux:callout>
                </div>
            @endif
        @endif
    </x-agro.card>

    {{-- ── Cuaderno DO ──────────────────────────────────────────────────── --}}
    <x-agro.card>
        <x-slot:header>
            <div class="flex items-center justify-between w-full">
                <div class="flex items-center gap-2">
                    <flux:icon icon="book-open" class="size-4 text-indigo-500" />
                    <span>{{ __('Cuaderno DO') }}</span>
                    @if($wineryNotes->count() > 0)
                        <span class="px-1.5 py-0.5 text-[10px] font-bold bg-zinc-100 text-zinc-500 rounded-full">{{ $wineryNotes->count() }}</span>
                    @endif
                </div>
                <button wire:click="openNoteForm"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                    <flux:icon icon="plus" class="size-3.5" />
                    Añadir nota
                </button>
            </div>
        </x-slot:header>

        {{-- Formulario nueva nota --}}
        @if($showNoteForm)
            <div class="mb-4 p-4 bg-zinc-50 border border-zinc-200 rounded-xl space-y-3">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <flux:label>{{ __('Tipo') }}</flux:label>
                        <flux:select wire:model="noteType">
                            @foreach($noteTypeLabels as $val => $label)
                                <flux:select.option value="{{ $val }}">{{ $label }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        @error('noteType') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-zinc-600 mb-1">{{ __('Fecha') }}</label>
                        <input type="date" wire:model="noteDate" class="w-full text-sm border border-zinc-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-300" />
                        @error('noteDate') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-zinc-600 mb-1">{{ __('Contenido') }}</label>
                    <textarea wire:model="noteContent" rows="3" placeholder="{{ __('Observaciones, acuerdos, incidencias…') }}"
                        class="w-full text-sm border border-zinc-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-300 resize-none"></textarea>
                    @error('noteContent') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="flex gap-2">
                    <button wire:click="saveNote" class="px-4 py-1.5 text-sm font-medium bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">{{ __('Guardar') }}</button>
                    <button wire:click="closeNoteForm" class="px-4 py-1.5 text-sm font-medium text-zinc-600 border border-zinc-200 rounded-lg hover:bg-zinc-100 transition">{{ __('Cancelar') }}</button>
                </div>
            </div>
        @endif

        {{-- Lista de notas --}}
        @forelse($wineryNotes as $note)
            @php
                $nColor = $noteTypeColors[$note->type] ?? 'zinc';
                $nIcon  = $noteTypeIcons[$note->type] ?? 'pencil-square';
            @endphp

            @if($editNoteId === $note->id)
                {{-- Formulario edición inline --}}
                <div class="py-3 border-b border-zinc-100 last:border-0">
                    <div class="p-3 bg-indigo-50 border border-indigo-200 rounded-xl space-y-3">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <flux:label>{{ __('Tipo') }}</flux:label>
                                <flux:select wire:model="editNoteType">
                                    @foreach($noteTypeLabels as $val => $label)
                                        <flux:select.option value="{{ $val }}">{{ $label }}</flux:select.option>
                                    @endforeach
                                </flux:select>
                                @error('editNoteType') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-zinc-600 mb-1">{{ __('Fecha') }}</label>
                                <input type="date" wire:model="editNoteDate" class="w-full text-sm border border-zinc-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-300" />
                                @error('editNoteDate') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div>
                            <textarea wire:model="editNoteContent" rows="3"
                                class="w-full text-sm border border-zinc-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-300 resize-none"></textarea>
                            @error('editNoteContent') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="flex gap-2">
                            <button wire:click="updateNote" class="px-3 py-1.5 text-xs font-medium bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">{{ __('Guardar') }}</button>
                            <button wire:click="closeEditNote" class="px-3 py-1.5 text-xs font-medium text-zinc-600 border border-zinc-200 rounded-lg hover:bg-zinc-100 transition">{{ __('Cancelar') }}</button>
                        </div>
                    </div>
                </div>
            @else
                <div class="flex items-start gap-3 py-3 border-b border-zinc-100 last:border-0 group">
                    <div class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center
                        {{ $nColor === 'blue' ? 'bg-blue-100' : ($nColor === 'green' ? 'bg-green-100' : ($nColor === 'amber' ? 'bg-amber-100' : 'bg-zinc-100')) }}">
                        <flux:icon icon="{{ $nIcon }}" class="size-4
                            {{ $nColor === 'blue' ? 'text-blue-600' : ($nColor === 'green' ? 'text-green-600' : ($nColor === 'amber' ? 'text-amber-600' : 'text-zinc-500')) }}" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-0.5">
                            <span class="text-xs font-semibold text-zinc-700">{{ $noteTypeLabels[$note->type] }}</span>
                            <span class="text-xs text-zinc-400">{{ $note->note_date->format('d/m/Y') }}</span>
                        </div>
                        <p class="text-sm text-zinc-600 whitespace-pre-line">{{ $note->content }}</p>
                    </div>
                    <div class="opacity-0 group-hover:opacity-100 flex items-center gap-0.5 transition-all flex-shrink-0">
                        <button wire:click="openEditNote({{ $note->id }})" class="p-1 text-zinc-400 hover:text-indigo-500 transition-colors">
                            <flux:icon icon="pencil" class="size-4" />
                        </button>
                        <button wire:click="deleteNote({{ $note->id }})" wire:confirm="{{ __('¿Eliminar esta nota?') }}"
                            class="p-1 text-zinc-400 hover:text-red-500 transition-colors">
                            <flux:icon icon="trash" class="size-4" />
                        </button>
                    </div>
                </div>
            @endif
        @empty
            <div class="py-8 text-center">
                <flux:icon icon="book-open" class="size-10 mx-auto text-zinc-300 mb-2" />
                <p class="text-sm text-zinc-400">{{ __('Sin notas. Añade observaciones, visitas o llamadas.') }}</p>
            </div>
        @endforelse
    </x-agro.card>

</div>
