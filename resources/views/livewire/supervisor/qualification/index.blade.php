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
                <flux:label>Bodega</flux:label>
                <flux:select wire:model="winery_id">
                    <flux:select.option value="">Selecciona bodega...</flux:select.option>
                    @foreach($wineries as $w)
                        <flux:select.option value="{{ $w->id }}">{{ $w->name }}</flux:select.option>
                    @endforeach
                </flux:select>
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
                <flux:label>Color</flux:label>
                <flux:select wire:model="color">
                    <flux:select.option value="">— Sin especificar —</flux:select.option>
                    @foreach($colorLabels as $key => $label)
                        <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>
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
            <flux:select wire:model.live="vintageFilter">
                <flux:select.option value="">Todas las añadas</flux:select.option>
                @foreach($availableVintages as $v)
                    <flux:select.option value="{{ $v }}">{{ $v }}</flux:select.option>
                @endforeach
            </flux:select>
        @endif
        <flux:select wire:model.live="colorFilter">
            <flux:select.option value="">Todos los colores</flux:select.option>
            @foreach($colorLabels as $key => $label)
                <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    {{-- Tabs + Card grid --}}
    <div>
        <x-agro.tabs :tabs="$tabs" :active="$currentTab" wireMethod="switchTab" />

        {{-- Skeleton durante carga --}}
        <x-agro.loading-grid target="search, switchTab, vintageFilter, colorFilter, nextPage, previousPage" />

        {{-- Card grid --}}
        <div wire:loading.remove wire:target="search, switchTab, vintageFilter, colorFilter, nextPage, previousPage">
            @if($qualifications->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    @foreach($qualifications as $q)
                        @php
                            $delay = min($loop->index * 50, 300);
                            $resultColor = match($q->result) {
                                'qualified'    => 'green',
                                'disqualified' => 'red',
                                'pending'      => 'yellow',
                                default        => 'zinc',
                            };
                            $colorIcon = match($q->color) {
                                'red'   => ['bg' => 'bg-red-100',   'text' => 'text-red-600'],
                                'white' => ['bg' => 'bg-amber-100', 'text' => 'text-amber-600'],
                                'rose'  => ['bg' => 'bg-pink-100',  'text' => 'text-pink-600'],
                                default => ['bg' => 'bg-purple-100', 'text' => 'text-purple-600'],
                            };
                        @endphp
                        <x-agro.card
                            class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                            style="animation-delay: {{ $delay }}ms;"
                            wire:key="qual-{{ $q->id }}"
                        >
                            <x-slot:header>
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl {{ $colorIcon['bg'] }} flex items-center justify-center shrink-0">
                                        <flux:icon icon="beaker" class="size-5 {{ $colorIcon['text'] }}" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="font-bold text-zinc-900 truncate text-sm">{{ $q->wine_name }}</h3>
                                        <p class="text-xs text-zinc-500">{{ $q->winery?->name ?? '—' }}</p>
                                    </div>
                                    <x-agro.status-badge :status="$q->result" :labels="$resultLabels" />
                                </div>
                            </x-slot:header>

                            <div class="flex-1 space-y-4">
                                <div class="grid grid-cols-2 gap-2">
                                    <div class="bg-agro-50 rounded-xl p-3">
                                        <p class="text-[10px] font-semibold text-agro-400 uppercase tracking-widest mb-0.5">Añada</p>
                                        <p class="text-2xl font-bold text-agro-700 leading-none">{{ $q->vintage }}</p>
                                    </div>
                                    <div class="bg-agro-50 rounded-xl p-3">
                                        <p class="text-[10px] font-semibold text-agro-400 uppercase tracking-widest mb-0.5">Alcohol</p>
                                        <p class="text-2xl font-bold text-agro-700 leading-none">{{ $q->alcohol_percentage ? $q->alcohol_percentage . '%' : '—' }}</p>
                                    </div>
                                </div>

                                <div class="space-y-2 text-sm">
                                    <div class="flex items-center justify-between">
                                        <span class="text-zinc-400">Color</span>
                                        <span class="text-zinc-700 font-medium capitalize">{{ $colorLabels[$q->color] ?? '—' }}</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-zinc-400">Fecha cata</span>
                                        <span class="text-zinc-700 font-medium">{{ $q->qualification_date?->format('d/m/Y') ?? '—' }}</span>
                                    </div>
                                </div>
                            </div>

                            <x-slot:footer>
                                <div class="flex items-center justify-end gap-1 flex-wrap">
                                    @if($q->result === 'pending')
                                        <button wire:click="qualify({{ $q->id }})"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium bg-agro-50 text-agro-700 border border-agro-200 rounded-md hover:bg-agro-100 transition-colors">
                                            Calificar
                                        </button>
                                        <button wire:click="disqualify({{ $q->id }})"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium bg-red-50 text-red-600 border border-red-200 rounded-md hover:bg-red-100 transition-colors">
                                            Descalificar
                                        </button>
                                    @endif
                                    @php
                                        $btnBase = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors';
                                    @endphp
                                    <button wire:click="openEdit({{ $q->id }})" class="{{ $btnBase }}" title="Editar">
                                        <flux:icon icon="pencil" class="size-4" />
                                    </button>
                                </div>
                            </x-slot:footer>
                        </x-agro.card>
                    @endforeach
                </div>
                <div class="mt-6">{{ $qualifications->links() }}</div>
            @else
                <x-agro.empty-state icon="star" title="Sin calificaciones" description="No hay registros de calificación con estos filtros." />
            @endif
        </div>
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
                        <flux:label>Color</flux:label>
                        <flux:select wire:model="editColor">
                            <flux:select.option value="">— Sin especificar —</flux:select.option>
                            @foreach($colorLabels as $key => $label)
                                <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
                            @endforeach
                        </flux:select>
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
