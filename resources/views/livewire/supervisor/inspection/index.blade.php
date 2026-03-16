<div class="space-y-6 animate-fade-in">

    <x-agro.page-header title="Control e Inspección" description="Gestión de inspecciones programadas a bodegas y viticultores.">
        <x-slot name="actions">
            <button wire:click="toggleCreate" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                <flux:icon icon="plus" class="w-4 h-4" />
                Nueva inspección
            </button>
        </x-slot>
    </x-agro.page-header>

    {{-- Create form --}}
    @if($showCreate)
    <x-agro.card>
        <h3 class="text-sm font-semibold text-zinc-700 mb-4">Nueva inspección programada</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-zinc-600 mb-1">Tipo de sujeto</label>
                <select wire:model.live="subject_type" class="w-full text-sm border border-zinc-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    <option value="winery">Bodega</option>
                    <option value="viticulturist">Viticultor</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-zinc-600 mb-1">
                    {{ $subject_type === 'winery' ? 'Bodega' : 'Viticultor' }}
                </label>
                <select wire:model="subject_id" class="w-full text-sm border border-zinc-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    <option value="">Selecciona...</option>
                    @if($subject_type === 'winery')
                        @foreach($wineries as $w)
                            <option value="{{ $w->id }}">{{ $w->name }}</option>
                        @endforeach
                    @else
                        @foreach($viticulturists as $v)
                            <option value="{{ $v->id }}">{{ $v->name }}</option>
                        @endforeach
                    @endif
                </select>
                @error('subject_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-zinc-600 mb-1">Fecha inspección</label>
                <input type="date" wire:model="inspection_date" class="w-full text-sm border border-zinc-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-300" />
                @error('inspection_date') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-zinc-600 mb-1">Nº referencia</label>
                <input type="text" wire:model="reference_number" placeholder="Ej: INSP-2026-001" class="w-full text-sm border border-zinc-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-300" />
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs font-medium text-zinc-600 mb-1">Notas</label>
                <textarea wire:model="notes" rows="2" class="w-full text-sm border border-zinc-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-300"></textarea>
            </div>
        </div>
        <div class="flex gap-3 mt-4">
            <button wire:click="saveInspection" class="px-4 py-2 text-sm font-medium bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">Programar</button>
            <button wire:click="toggleCreate" class="px-4 py-2 text-sm font-medium text-zinc-600 border border-zinc-200 rounded-lg hover:bg-zinc-50 transition">Cancelar</button>
        </div>
    </x-agro.card>
    @endif

    {{-- Filter by type --}}
    <div class="flex items-center gap-3">
        <select wire:model.live="typeFilter" class="text-sm border border-zinc-200 rounded-lg px-3 py-1.5 focus:outline-none">
            <option value="">Todos los tipos</option>
            <option value="winery">Bodegas</option>
            <option value="viticulturist">Viticultores</option>
        </select>
        <div class="relative">
            <flux:icon icon="magnifying-glass" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-400 pointer-events-none" />
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar..." class="pl-9 pr-3 py-1.5 text-sm border border-zinc-200 rounded-lg focus:outline-none w-48" />
        </div>
    </div>

    {{-- Tabs + Table --}}
    <div>
        <x-agro.tabs :tabs="$tabs" :active="$currentTab" wireMethod="switchTab" />

        <x-agro.data-table
            :headers="['Sujeto', 'Tipo', 'Fecha', 'Estado', 'Resultado', 'Referencia', '']"
            emptyMessage="No hay inspecciones registradas."
        >
            @foreach($inspections as $inspection)
                <tr class="hover:bg-zinc-50 transition">
                    <td class="px-6 py-3 text-sm font-medium text-zinc-800">{{ $inspection->subject?->name ?? '—' }}</td>
                    <td class="px-6 py-3 text-sm text-zinc-500 capitalize">
                        {{ $inspection->subject_type === 'winery' ? 'Bodega' : 'Viticultor' }}
                    </td>
                    <td class="px-6 py-3 text-sm text-zinc-500">{{ $inspection->inspection_date->format('d/m/Y') }}</td>
                    <td class="px-6 py-3 text-sm">
                        <x-agro.status-badge :status="$inspection->status" :labels="\App\Models\DoInspection::STATUS_LABELS" />
                    </td>
                    <td class="px-6 py-3 text-sm text-zinc-500">
                        {{ \App\Models\DoInspection::RESULT_LABELS[$inspection->result] ?? '—' }}
                    </td>
                    <td class="px-6 py-3 text-sm text-zinc-400">{{ $inspection->reference_number ?? '—' }}</td>
                    <td class="px-6 py-3 text-right">
                        <div class="flex items-center justify-end gap-1">
                            @if($inspection->status === 'scheduled')
                                <button wire:click="updateStatus({{ $inspection->id }}, 'in_progress')"
                                    class="text-xs text-blue-600 hover:underline">Iniciar</button>
                            @endif
                            @if($inspection->status === 'in_progress')
                                <button wire:click="updateStatus({{ $inspection->id }}, 'completed')"
                                    class="text-xs text-agro-600 hover:underline">Completar</button>
                            @endif
                            @if(in_array($inspection->status, ['scheduled', 'in_progress']))
                                <button wire:click="updateStatus({{ $inspection->id }}, 'cancelled')"
                                    class="text-xs text-red-500 hover:underline ml-1">Cancelar</button>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach

            <x-slot name="pagination">{{ $inspections->links() }}</x-slot>
        </x-agro.data-table>
    </div>

</div>
