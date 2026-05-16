<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Notas de Cata"
        description="Evaluaciones sensoriales de los vinos en elaboración y crianza"
    >
        <x-slot:actions>
            <flux:button href="{{ roleRoute('tasting-notes.create') }}" variant="primary" icon="plus">
                Nueva Cata
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    <x-agro.filter-bar>
        <x-agro.filter-input
            wire:model.live.debounce.300ms="search"
            placeholder="Buscar por vino o catador..."
        />
        <flux:select wire:model.live="wineFilter" size="sm" class="w-52">
            <flux:select.option value="">Todos los vinos</flux:select.option>
            @foreach($wines as $wine)
                <flux:select.option value="{{ $wine->id }}">{{ $wine->name }}{{ $wine->vintage ? ' ' . $wine->vintage : '' }}</flux:select.option>
            @endforeach
        </flux:select>
        @if($search || $wineFilter)
            <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">Limpiar</flux:button>
        @endif
    </x-agro.filter-bar>

    @if($tastingNotes->count() > 0)
        <div class="bg-white rounded-2xl border border-zinc-200 overflow-hidden shadow-sm"
            wire:loading.class="opacity-60 pointer-events-none"
            wire:target="search, wineFilter, clearFilters">

            <table class="w-full text-sm">
                <thead class="bg-zinc-50 border-b border-zinc-200">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wide">Fecha</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wide">Vino</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wide">Catador</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wide">Visual</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wide">Olfativo</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-zinc-500 uppercase tracking-wide">Puntuación</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wide">Conclusión</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @foreach($tastingNotes as $note)
                        <tr class="hover:bg-zinc-50 transition-colors" wire:key="note-{{ $note->id }}">
                            <td class="px-4 py-3 text-zinc-600 whitespace-nowrap">
                                {{ $note->evaluation_date->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="font-medium text-zinc-900">{{ $note->wine->name }}</span>
                                @if($note->wine->vintage)
                                    <span class="text-xs text-zinc-400 ml-1">{{ $note->wine->vintage }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-zinc-500">
                                {{ $note->evaluator_display }}
                            </td>
                            <td class="px-4 py-3">
                                @if($note->visual_color)
                                    <span class="text-zinc-700">{{ $note->visual_color }}</span>
                                @else
                                    <span class="text-zinc-300">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-zinc-500 max-w-xs truncate">
                                {{ $note->aroma_descriptors ? \Illuminate\Support\Str::limit($note->aroma_descriptors, 50) : '—' }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($note->overall_score !== null)
                                    <flux:badge color="{{ $note->score_badge_color }}" size="sm">
                                        {{ number_format($note->overall_score, 1) }}
                                    </flux:badge>
                                @else
                                    <span class="text-zinc-300">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-zinc-500 max-w-xs truncate">
                                {{ $note->overall_conclusion ? \Illuminate\Support\Str::limit($note->overall_conclusion, 60) : '—' }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <x-agro.action-button icon="pencil" variant="default" href="{{ roleRoute('tasting-notes.edit', $note) }}" title="Editar" />
                                    <x-agro.action-button variant="delete" wire:click="delete({{ $note->id }})" wire:confirm="¿Eliminar esta nota de cata?" wire:loading.attr="disabled" title="Eliminar" />
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-2">{{ $tastingNotes->links() }}</div>
    @else
        <x-agro.empty-state
            icon="beaker"
            title="No hay notas de cata"
            description="{{ $search || $wineFilter ? 'Ninguna cata coincide con los filtros.' : 'Registra la primera evaluación sensorial de tus vinos.' }}"
        >
            @if($search || $wineFilter)
                <x-slot:action>
                    <flux:button wire:click="clearFilters" variant="outline" icon="x-mark">Limpiar filtros</flux:button>
                </x-slot:action>
            @else
                <x-slot:action>
                    <flux:button href="{{ roleRoute('tasting-notes.create') }}" variant="primary" icon="plus">
                        Nueva Cata
                    </flux:button>
                </x-slot:action>
            @endif
        </x-agro.empty-state>
    @endif
</div>
