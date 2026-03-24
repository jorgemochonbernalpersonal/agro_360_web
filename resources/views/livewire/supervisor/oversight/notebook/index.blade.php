<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Cuaderno de campo — DO"
        description="Actividades registradas por los viticultores con acceso de lectura concedido."
    />

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <x-agro.stat-card
            label="Viticultores con acceso"
            :value="$totalWithAccess"
            icon="book-open"
            color="agro"
        />
        <x-agro.stat-card
            label="Registros mostrados"
            :value="$activities->total()"
            icon="clipboard-document-list"
            color="blue"
        />
        <div class="rounded-xl border border-zinc-100 bg-white p-4 flex flex-col gap-1">
            <p class="text-xs text-zinc-400">Tipos de actividad</p>
            <p class="text-lg font-semibold text-zinc-800">{{ count($activityTypes) }}</p>
            <p class="text-xs text-zinc-400">Tratamientos, riegos, podas…</p>
        </div>
    </div>

    @if($totalWithAccess === 0)
        <x-agro.empty-state
            icon="book-open"
            title="Sin acceso al cuaderno"
            description="Ningún viticultor tiene acceso al cuaderno concedido. Ve a gestión de accesos para habilitarlo."
        >
            <x-slot name="action">
                <a href="{{ route('supervisor.notebook.index') }}" wire:navigate>
                    <flux:button variant="primary" size="sm" icon="key">Gestionar accesos</flux:button>
                </a>
            </x-slot>
        </x-agro.empty-state>
    @else

        {{-- Filtros --}}
        <x-agro.filter-bar>
            <x-agro.filter-select wire:model.live="filterVit" label="Viticultor">
                <option value="">Todos</option>
                @foreach($viticulturists as $vit)
                    <option value="{{ $vit->id }}">{{ $vit->name }}</option>
                @endforeach
            </x-agro.filter-select>

            @if($filterVit && $plots->isNotEmpty())
                <x-agro.filter-select wire:model.live="filterPlot" label="Parcela">
                    <option value="">Todas las parcelas</option>
                    @foreach($plots as $plot)
                        <option value="{{ $plot->id }}">{{ $plot->name }}</option>
                    @endforeach
                </x-agro.filter-select>
            @endif

            <x-agro.filter-select wire:model.live="filterType" label="Tipo">
                <option value="">Todos los tipos</option>
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

            <button wire:click="clearFilters" class="text-xs text-zinc-400 hover:text-zinc-600 transition px-2 py-1.5">
                Limpiar
            </button>
        </x-agro.filter-bar>

        {{-- Tabla --}}
        <x-agro.data-table
            :headers="['Fecha', 'Viticultor', 'Parcela', 'Tipo de actividad', 'Notas']"
            emptyMessage="No hay actividades con los filtros seleccionados."
        >
            @foreach($activities as $activity)
                <tr class="hover:bg-zinc-50 transition">
                    <td class="px-6 py-3 text-sm text-zinc-600 whitespace-nowrap">
                        {{ $activity->activity_date->format('d/m/Y') }}
                    </td>
                    <td class="px-6 py-3 text-sm text-zinc-700">
                        {{ $activity->viticulturist?->name ?? '—' }}
                    </td>
                    <td class="px-6 py-3 text-sm text-zinc-500">
                        {{ $activity->plot?->name ?? '—' }}
                    </td>
                    <td class="px-6 py-3">
                        @php
                            $typeColors = [
                                'phytosanitary' => 'bg-red-50 text-red-700 border-red-200',
                                'fertilization' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                                'irrigation'    => 'bg-blue-50 text-blue-700 border-blue-200',
                                'cultural'      => 'bg-zinc-50 text-zinc-700 border-zinc-200',
                                'observation'   => 'bg-violet-50 text-violet-700 border-violet-200',
                                'harvest'       => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                'pruning'       => 'bg-orange-50 text-orange-700 border-orange-200',
                                'post_harvest'  => 'bg-teal-50 text-teal-700 border-teal-200',
                                'phenology'     => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                            ];
                            $colorClass = $typeColors[$activity->activity_type] ?? 'bg-zinc-50 text-zinc-700 border-zinc-200';
                        @endphp
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs border {{ $colorClass }}">
                            {{ $activityTypes[$activity->activity_type] ?? $activity->activity_type }}
                        </span>
                    </td>
                    <td class="px-6 py-3 text-sm text-zinc-400 max-w-xs truncate">
                        {{ $activity->notes ?? '—' }}
                    </td>
                </tr>
            @endforeach

            <x-slot name="pagination">
                {{ $activities->links() }}
            </x-slot>
        </x-agro.data-table>

    @endif

</div>
