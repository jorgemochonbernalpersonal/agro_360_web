<div class="space-y-6 animate-fade-in">

    <x-agro.page-header title="Calificación" description="Registro de catas y calificación de vinos por añada.">
        <x-slot name="actions">
            <button wire:click="toggleCreate" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                <flux:icon icon="plus" class="w-4 h-4" />
                Registrar vino
            </button>
        </x-slot>
    </x-agro.page-header>

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <x-agro.stat-card label="Total registrados" :value="$tabs['all']['count']" icon="star" color="blue" />
        <x-agro.stat-card label="Calificados" :value="$tabs['qualified']['count']" icon="check-circle" color="agro" />
        <x-agro.stat-card label="Pendientes" :value="$tabs['pending']['count']" icon="clock" color="yellow" />
    </div>

    {{-- Create form --}}
    @if($showCreate)
    <x-agro.card>
        <h3 class="text-sm font-semibold text-zinc-700 mb-4">Nuevo registro de calificación</h3>
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
                <label class="block text-xs font-medium text-zinc-600 mb-1">Nombre del vino</label>
                <input type="text" wire:model="wine_name" class="w-full text-sm border border-zinc-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-300" />
                @error('wine_name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-zinc-600 mb-1">Añada</label>
                <input type="number" wire:model="vintage" min="1990" max="2100" class="w-full text-sm border border-zinc-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-300" />
            </div>
            <div>
                <label class="block text-xs font-medium text-zinc-600 mb-1">Color</label>
                <select wire:model="color" class="w-full text-sm border border-zinc-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    <option value="">— Sin especificar —</option>
                    @foreach($colorLabels as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-zinc-600 mb-1">Grado alcohólico (%)</label>
                <input type="number" wire:model="alcohol_percentage" step="0.01" min="0" max="25" class="w-full text-sm border border-zinc-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-300" />
            </div>
            <div>
                <label class="block text-xs font-medium text-zinc-600 mb-1">Fecha de cata</label>
                <input type="date" wire:model="qualification_date" class="w-full text-sm border border-zinc-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-300" />
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs font-medium text-zinc-600 mb-1">Notas de cata</label>
                <textarea wire:model="tasting_notes" rows="2" class="w-full text-sm border border-zinc-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-300"></textarea>
            </div>
        </div>
        <div class="flex gap-3 mt-4">
            <button wire:click="saveQualification" class="px-4 py-2 text-sm font-medium bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">Guardar</button>
            <button wire:click="toggleCreate" class="px-4 py-2 text-sm font-medium text-zinc-600 border border-zinc-200 rounded-lg hover:bg-zinc-50 transition">Cancelar</button>
        </div>
    </x-agro.card>
    @endif

    {{-- Filters --}}
    <div class="flex items-center gap-3 flex-wrap">
        @if($availableVintages->isNotEmpty())
            <select wire:model.live="vintageFilter" class="text-sm border border-zinc-200 rounded-lg px-3 py-1.5 focus:outline-none">
                <option value="">Todas las añadas</option>
                @foreach($availableVintages as $v)
                    <option value="{{ $v }}">{{ $v }}</option>
                @endforeach
            </select>
        @endif
        <select wire:model.live="colorFilter" class="text-sm border border-zinc-200 rounded-lg px-3 py-1.5 focus:outline-none">
            <option value="">Todos los colores</option>
            @foreach($colorLabels as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </select>
    </div>

    {{-- Tabs + Table --}}
    <div>
        <x-agro.tabs :tabs="$tabs" :active="$currentTab" wireMethod="switchTab" />

        <x-agro.data-table
            :headers="['Vino', 'Bodega', 'Añada', 'Color', 'Alcohol', 'Fecha cata', 'Resultado', '']"
            emptyMessage="No hay registros de calificación."
        >
            @foreach($qualifications as $q)
                <tr class="hover:bg-zinc-50 transition">
                    <td class="px-6 py-3 text-sm font-medium text-zinc-800">{{ $q->wine_name }}</td>
                    <td class="px-6 py-3 text-sm text-zinc-500">{{ $q->winery?->name ?? '—' }}</td>
                    <td class="px-6 py-3 text-sm text-zinc-500">{{ $q->vintage }}</td>
                    <td class="px-6 py-3 text-sm text-zinc-500 capitalize">{{ $colorLabels[$q->color] ?? '—' }}</td>
                    <td class="px-6 py-3 text-sm text-zinc-500">{{ $q->alcohol_percentage ? $q->alcohol_percentage . '%' : '—' }}</td>
                    <td class="px-6 py-3 text-sm text-zinc-500">{{ $q->qualification_date?->format('d/m/Y') ?? '—' }}</td>
                    <td class="px-6 py-3 text-sm">
                        <x-agro.status-badge :status="$q->result" :labels="$resultLabels" />
                    </td>
                    <td class="px-6 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            @if($q->result === 'pending')
                                <button wire:click="qualify({{ $q->id }})" class="text-xs text-agro-600 hover:underline">Calificar</button>
                                <button wire:click="disqualify({{ $q->id }})" class="text-xs text-red-500 hover:underline">Descalificar</button>
                            @endif
                            <button wire:click="openEdit({{ $q->id }})" class="text-xs text-zinc-500 hover:text-zinc-700 hover:underline">Editar</button>
                        </div>
                    </td>
                </tr>
            @endforeach

            <x-slot name="pagination">{{ $qualifications->links() }}</x-slot>
        </x-agro.data-table>
    </div>

    {{-- Edit modal --}}
    @if($showEdit)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" wire:key="edit-modal-{{ $editId }}">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-zinc-100">
                <h3 class="text-base font-semibold text-zinc-800">Editar calificación</h3>
                <button wire:click="closeEdit" class="text-zinc-400 hover:text-zinc-600">
                    <flux:icon icon="x-mark" class="w-5 h-5" />
                </button>
            </div>
            <div class="p-6 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-zinc-600 mb-1">Nombre del vino</label>
                        <input type="text" wire:model="editWineName" class="w-full text-sm border border-zinc-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-300" />
                        @error('editWineName') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-zinc-600 mb-1">Añada</label>
                        <input type="number" wire:model="editVintage" min="1990" max="2100" class="w-full text-sm border border-zinc-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-300" />
                        @error('editVintage') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-zinc-600 mb-1">Color</label>
                        <select wire:model="editColor" class="w-full text-sm border border-zinc-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-300">
                            <option value="">— Sin especificar —</option>
                            @foreach($colorLabels as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-zinc-600 mb-1">Fecha de cata</label>
                        <input type="date" wire:model="editQualificationDate" class="w-full text-sm border border-zinc-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-300" />
                        @error('editQualificationDate') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <p class="text-xs font-semibold text-zinc-400 uppercase tracking-wide pt-2">Parámetros analíticos</p>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-zinc-600 mb-1">Alcohol (%)</label>
                        <input type="number" wire:model="editAlcohol" step="0.01" min="0" max="25" class="w-full text-sm border border-zinc-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-300" />
                        @error('editAlcohol') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-zinc-600 mb-1">Brix (°Bx)</label>
                        <input type="number" wire:model="editBrix" step="0.01" min="0" max="50" class="w-full text-sm border border-zinc-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-300" />
                        @error('editBrix') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-zinc-600 mb-1">Acidez (g/L)</label>
                        <input type="number" wire:model="editAcidity" step="0.01" min="0" max="30" class="w-full text-sm border border-zinc-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-300" />
                        @error('editAcidity') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-zinc-600 mb-1">pH</label>
                        <input type="number" wire:model="editPh" step="0.01" min="2" max="5" class="w-full text-sm border border-zinc-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-300" />
                        @error('editPh') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <p class="text-xs font-semibold text-zinc-400 uppercase tracking-wide pt-2">Puntuaciones de cata (0–10)</p>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-zinc-600 mb-1">Visual</label>
                        <input type="number" wire:model="editVisualScore" step="0.1" min="0" max="10" class="w-full text-sm border border-zinc-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-300" />
                        @error('editVisualScore') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-zinc-600 mb-1">Aroma</label>
                        <input type="number" wire:model="editAromaScore" step="0.1" min="0" max="10" class="w-full text-sm border border-zinc-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-300" />
                        @error('editAromaScore') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-zinc-600 mb-1">Gusto</label>
                        <input type="number" wire:model="editTasteScore" step="0.1" min="0" max="10" class="w-full text-sm border border-zinc-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-300" />
                        @error('editTasteScore') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-zinc-600 mb-1">Global (0–100)</label>
                        <input type="number" wire:model="editOverallScore" step="0.1" min="0" max="100" class="w-full text-sm border border-zinc-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-300" />
                        @error('editOverallScore') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-zinc-600 mb-1">Notas de cata</label>
                    <textarea wire:model="editTastingNotes" rows="3" class="w-full text-sm border border-zinc-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-300"></textarea>
                </div>
            </div>
            <div class="flex justify-end gap-3 px-6 py-4 border-t border-zinc-100">
                <button wire:click="closeEdit" class="px-4 py-2 text-sm font-medium text-zinc-600 border border-zinc-200 rounded-lg hover:bg-zinc-50 transition">Cancelar</button>
                <button wire:click="updateQualification" class="px-4 py-2 text-sm font-medium bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">Guardar cambios</button>
            </div>
        </div>
    </div>
    @endif

</div>
