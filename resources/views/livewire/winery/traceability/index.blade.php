<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Trazabilidad"
        description="Consulta el historial completo de cada vino desde la uva hasta la etiqueta."
    />

    {{-- Filtros --}}
    <x-agro.filter-bar>
        <x-agro.filter-input wire:model.live.debounce.300ms="search" placeholder="Buscar vino o código..." />

        <flux:select wire:model.live="vintageFilter" size="sm" class="min-w-32">
            <option value="">Todas las añadas</option>
            @foreach($vintages as $v)
                <option value="{{ $v }}">{{ $v }}</option>
            @endforeach
        </flux:select>

        <flux:select wire:model.live="statusFilter" size="sm" class="min-w-40">
            <option value="">Todos los estados</option>
            @foreach($statuses as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </flux:select>
    </x-agro.filter-bar>

    <x-agro.card :padding="false">
        <table class="min-w-full divide-y divide-zinc-100 dark:divide-zinc-800">
            <thead class="bg-zinc-50 dark:bg-zinc-800">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Vino</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Añada</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Estado</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-zinc-500 uppercase tracking-wider">Orígenes</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-zinc-500 uppercase tracking-wider">Procesos</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-zinc-500 uppercase tracking-wider">Embotellados</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse($wines as $wine)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                        <td class="px-6 py-4">
                            <div>
                                <p class="font-medium text-zinc-900 dark:text-zinc-100 text-sm">{{ $wine->name }}</p>
                                @if($wine->internal_code)
                                    <p class="text-xs text-zinc-400">{{ $wine->internal_code }}</p>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-zinc-600 dark:text-zinc-300">
                            {{ $wine->vintage ?? '—' }}
                        </td>
                        <td class="px-6 py-4">
                            <x-agro.status-badge
                                :label="$wine->status_label"
                                :type="match($wine->status) {
                                    'in_progress' => 'info',
                                    'aged'        => 'warning',
                                    'bottled'     => 'success',
                                    'sold'        => 'gray',
                                    default       => 'gray',
                                }"
                            />
                        </td>
                        <td class="px-6 py-4 text-center text-sm text-zinc-600 dark:text-zinc-300">
                            {{ $wine->wineHarvests->count() }}
                        </td>
                        <td class="px-6 py-4 text-center text-sm text-zinc-600 dark:text-zinc-300">
                            {{ $wine->processDetails->count() }}
                        </td>
                        <td class="px-6 py-4 text-center text-sm text-zinc-600 dark:text-zinc-300">
                            {{ $wine->bottlings->count() }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <flux:button size="sm" variant="ghost" icon="eye"
                                    href="{{ roleRoute('wines.show', $wine) }}" wire:navigate>
                                    Ver vino
                                </flux:button>
                                @if($wine->trace_token)
                                    <flux:button size="sm" variant="ghost" icon="arrow-down-tray"
                                        href="{{ route('winery.wines.traceability-pdf', $wine) }}"
                                        target="_blank">
                                        PDF
                                    </flux:button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-zinc-400 text-sm">
                            No se encontraron vinos con los filtros actuales.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($wines->hasPages())
            <div class="px-6 py-4 border-t border-zinc-100 dark:border-zinc-800">
                <x-agro.pagination :paginator="$wines" />
            </div>
        @endif
    </x-agro.card>
</div>
