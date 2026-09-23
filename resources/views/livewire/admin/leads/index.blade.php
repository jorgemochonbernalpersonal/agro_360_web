<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Contactos de herramientas"
        description="Emails captados en las herramientas públicas gratuitas (/herramientas)"
    >
        <x-slot:actions>
            <flux:button wire:click="resetFilters" variant="ghost" icon="arrow-path">Resetear filtros</flux:button>
            <flux:button wire:click="export" icon="arrow-down-tray">Exportar CSV</flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
        <x-agro.stat-card label="Total" :value="$stats['total']" icon="users" color="blue" />
        <x-agro.stat-card label="Sin contactar" :value="$stats['pending']" icon="envelope" color="orange" />
        <x-agro.stat-card label="Últimos 7 días" :value="$stats['last7']" icon="calendar" color="agro" />
    </div>

    <x-agro.filter-bar>
        <x-agro.filter-select wire:model.live="filterStatus">
            <option value="">Todos</option>
            <option value="pending">Sin contactar</option>
            <option value="contacted">Contactados</option>
        </x-agro.filter-select>

        <x-agro.filter-select wire:model.live="filterTool">
            <option value="">Todas las herramientas</option>
            @foreach ($tools as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </x-agro.filter-select>

        <x-agro.filter-select wire:model.live="filterProfile">
            <option value="">Todos los perfiles</option>
            @foreach ($profiles as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </x-agro.filter-select>

        <x-agro.filter-input wire:model.live.debounce.300ms="search" placeholder="Buscar email o nombre..." />
    </x-agro.filter-bar>

    <x-agro.card :padding="false">
        @if ($leads->isEmpty())
            <x-agro.empty-state
                icon="envelope"
                message="Sin contactos"
                description="Todavía no ha dejado nadie su email con estos filtros."
            />
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200">
                    <thead class="bg-zinc-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-600 uppercase tracking-wider">Fecha</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-600 uppercase tracking-wider">Contacto</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-600 uppercase tracking-wider">Perfil</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-600 uppercase tracking-wider">Herramienta</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-600 uppercase tracking-wider">Nota test</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-600 uppercase tracking-wider">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-zinc-100 text-sm">
                        @foreach ($leads as $lead)
                            <tr wire:key="lead-{{ $lead->id }}" class="hover:bg-zinc-50">
                                <td class="px-4 py-3 text-zinc-500 whitespace-nowrap">{{ $lead->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-3">
                                    <a href="mailto:{{ $lead->email }}" class="font-medium text-zinc-900 hover:underline">{{ $lead->email }}</a>
                                    @if ($lead->name)
                                        <p class="text-xs text-zinc-500">{{ $lead->name }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-zinc-700">{{ $lead->profileLabel() }}</td>
                                <td class="px-4 py-3 text-zinc-700">{{ $this->toolTitle($lead->tool) }}</td>
                                <td class="px-4 py-3 text-zinc-700">{{ isset($lead->context['score']) ? $lead->context['score'].' %' : '—' }}</td>
                                <td class="px-4 py-3">
                                    <button type="button" wire:click="toggleContacted({{ $lead->id }})" @class([
                                        'inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold',
                                        'bg-agro-50 text-agro-700' => $lead->contacted_at,
                                        'bg-orange-50 text-orange-700' => ! $lead->contacted_at,
                                    ])>
                                        {{ $lead->contacted_at ? 'Contactado '.$lead->contacted_at->format('d/m') : 'Sin contactar' }}
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3">{{ $leads->links() }}</div>
        @endif
    </x-agro.card>
</div>
