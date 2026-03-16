<div class="space-y-6 animate-fade-in">

    <x-agro.page-header title="Contraetiquetas" description="Solicitudes de contraetiquetas por bodega y añada.">
        <x-slot name="actions">
            <button wire:click="toggleCreate" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                <flux:icon icon="plus" class="w-4 h-4" />
                Nueva solicitud
            </button>
        </x-slot>
    </x-agro.page-header>

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <x-agro.stat-card label="Total emitidas" :value="number_format($totalIssued, 0, ',', '.')" icon="tag" color="agro"
            :description="$vintageFilter ? 'Añada ' . $vintageFilter : 'Todas las añadas'" />
        <x-agro.stat-card label="Solicitudes pendientes" :value="$tabs['pending']['count']" icon="inbox" color="yellow" />
    </div>

    {{-- Create form --}}
    @if($showCreate)
    <x-agro.card>
        <h3 class="text-sm font-semibold text-zinc-700 mb-4">Nueva solicitud de contraetiquetas</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-zinc-600 mb-1">Bodega</label>
                <select wire:model="winery_id" class="w-full text-sm border border-zinc-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    <option value="">Selecciona bodega...</option>
                    @foreach($wineries as $w)
                        <option value="{{ $w->id }}">{{ $w->name }}</option>
                    @endforeach
                </select>
                @error('winery_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-zinc-600 mb-1">Añada</label>
                <input type="number" wire:model="vintage" min="1990" max="2100" class="w-full text-sm border border-zinc-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-300" />
                @error('vintage') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-zinc-600 mb-1">Nº de lote</label>
                <input type="text" wire:model="batch_number" placeholder="Opcional" class="w-full text-sm border border-zinc-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-300" />
            </div>
            <div>
                <label class="block text-xs font-medium text-zinc-600 mb-1">Cantidad solicitada</label>
                <input type="number" wire:model="quantity_requested" min="1" class="w-full text-sm border border-zinc-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-300" />
                @error('quantity_requested') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs font-medium text-zinc-600 mb-1">Notas</label>
                <textarea wire:model="notes" rows="2" class="w-full text-sm border border-zinc-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-300"></textarea>
            </div>
        </div>
        <div class="flex gap-3 mt-4">
            <button wire:click="saveLabel" class="px-4 py-2 text-sm font-medium bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">Registrar solicitud</button>
            <button wire:click="toggleCreate" class="px-4 py-2 text-sm font-medium text-zinc-600 border border-zinc-200 rounded-lg hover:bg-zinc-50 transition">Cancelar</button>
        </div>
    </x-agro.card>
    @endif

    {{-- Filters --}}
    <div class="flex items-center gap-3">
        @if($availableVintages->isNotEmpty())
            <select wire:model.live="vintageFilter" class="text-sm border border-zinc-200 rounded-lg px-3 py-1.5 focus:outline-none">
                <option value="">Todas las añadas</option>
                @foreach($availableVintages as $v)
                    <option value="{{ $v }}">{{ $v }}</option>
                @endforeach
            </select>
        @endif
    </div>

    {{-- Tabs + Table --}}
    <div>
        <x-agro.tabs :tabs="$tabs" :active="$currentTab" wireMethod="switchTab" />

        <x-agro.data-table
            :headers="['Bodega', 'Añada', 'Lote', 'Solicitadas', 'Emitidas', 'Estado', '']"
            emptyMessage="No hay solicitudes de contraetiquetas."
        >
            @foreach($labels as $label)
                <tr class="hover:bg-zinc-50 transition">
                    <td class="px-6 py-3 text-sm font-medium text-zinc-800">{{ $label->winery?->name ?? '—' }}</td>
                    <td class="px-6 py-3 text-sm text-zinc-500">{{ $label->vintage }}</td>
                    <td class="px-6 py-3 text-sm text-zinc-400">{{ $label->batch_number ?? '—' }}</td>
                    <td class="px-6 py-3 text-sm text-zinc-500">{{ number_format($label->quantity_requested, 0, ',', '.') }}</td>
                    <td class="px-6 py-3 text-sm text-zinc-500">{{ $label->quantity_issued > 0 ? number_format($label->quantity_issued, 0, ',', '.') : '—' }}</td>
                    <td class="px-6 py-3 text-sm">
                        <x-agro.status-badge :status="$label->status" :labels="\App\Models\DoLabel::STATUS_LABELS" />
                    </td>
                    <td class="px-6 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            @if($label->status === 'pending')
                                <button wire:click="approve({{ $label->id }})" class="text-xs text-blue-600 hover:underline">Aprobar</button>
                            @endif
                            @if(in_array($label->status, ['pending', 'approved']))
                                <button wire:click="issue({{ $label->id }})" class="text-xs text-agro-600 hover:underline">Emitir</button>
                                <button wire:click="cancel({{ $label->id }})" class="text-xs text-red-500 hover:underline">Cancelar</button>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach

            <x-slot name="pagination">{{ $labels->links() }}</x-slot>
        </x-agro.data-table>
    </div>

</div>
