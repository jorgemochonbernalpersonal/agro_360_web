<div class="space-y-6 animate-fade-in">

    <x-agro.page-header title="Calificación" description="Registro de catas y calificación de vinos por añada.">
        <x-slot name="actions">
            <flux:button wire:click="toggleCreate" variant="primary" icon="plus">Registrar vino</flux:button>
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
                <flux:error name="winery_id" />
            </div>
            <div>
                <flux:label>Nombre del vino</flux:label>
                <flux:input type="text" wire:model="wine_name" />
                <flux:error name="wine_name" />
            </div>
            <div>
                <flux:label>Añada</flux:label>
                <flux:input type="number" wire:model="vintage" min="1990" max="2100" />
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
                <flux:label>Grado alcohólico (%)</flux:label>
                <flux:input type="number" wire:model="alcohol_percentage" step="0.01" min="0" max="25" />
            </div>
            <div>
                <flux:label>Fecha de cata</flux:label>
                <flux:input type="date" wire:model="qualification_date" />
            </div>
            <div class="sm:col-span-2">
                <flux:label>Notas de cata</flux:label>
                <flux:textarea wire:model="tasting_notes" rows="2" />
            </div>
        </div>
        <div class="flex gap-3 mt-4">
            <flux:button wire:click="saveQualification" variant="primary">Guardar</flux:button>
            <flux:button wire:click="toggleCreate" variant="ghost">Cancelar</flux:button>
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
                                <x-agro.card-item-header
                                    icon="beaker"
                                    :title="$q->wine_name"
                                    :subtitle="$q->winery?->name ?? '—'"
                                    :iconBg="$colorIcon['bg']"
                                    :iconColor="$colorIcon['text']"
                                    size="md"
                                    radius="xl"
                                >
                                    <x-agro.status-badge :status="$q->result" :labels="$resultLabels" />
                                </x-agro.card-item-header>
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
                                    <x-agro.action-button icon="pencil" variant="default" wire:click="openEdit({{ $q->id }})" title="Editar" />
                                </div>
                            </x-slot:footer>
                        </x-agro.card>
                    @endforeach
                </div>
                <x-agro-pagination :paginator="$qualifications" />
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
                <flux:button wire:click="closeEdit" variant="ghost" size="sm" icon="x-mark" />
            </div>
            <div class="p-6 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <flux:label>Nombre del vino</flux:label>
                        <flux:input type="text" wire:model="editWineName" />
                        <flux:error name="editWineName" />
                    </div>
                    <div>
                        <flux:label>Añada</flux:label>
                        <flux:input type="number" wire:model="editVintage" min="1990" max="2100" />
                        <flux:error name="editVintage" />
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
                        <flux:label>Fecha de cata</flux:label>
                        <flux:input type="date" wire:model="editQualificationDate" />
                        <flux:error name="editQualificationDate" />
                    </div>
                </div>

                <p class="text-xs font-semibold text-zinc-400 uppercase tracking-wide pt-2">Parámetros analíticos</p>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div>
                        <flux:label>Alcohol (%)</flux:label>
                        <flux:input type="number" wire:model="editAlcohol" step="0.01" min="0" max="25" />
                        <flux:error name="editAlcohol" />
                    </div>
                    <div>
                        <flux:label>Brix (°Bx)</flux:label>
                        <flux:input type="number" wire:model="editBrix" step="0.01" min="0" max="50" />
                        <flux:error name="editBrix" />
                    </div>
                    <div>
                        <flux:label>Acidez (g/L)</flux:label>
                        <flux:input type="number" wire:model="editAcidity" step="0.01" min="0" max="30" />
                        <flux:error name="editAcidity" />
                    </div>
                    <div>
                        <flux:label>pH</flux:label>
                        <flux:input type="number" wire:model="editPh" step="0.01" min="2" max="5" />
                        <flux:error name="editPh" />
                    </div>
                </div>

                <p class="text-xs font-semibold text-zinc-400 uppercase tracking-wide pt-2">Puntuaciones de cata (0–10)</p>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div>
                        <flux:label>Visual</flux:label>
                        <flux:input type="number" wire:model="editVisualScore" step="0.1" min="0" max="10" />
                        <flux:error name="editVisualScore" />
                    </div>
                    <div>
                        <flux:label>Aroma</flux:label>
                        <flux:input type="number" wire:model="editAromaScore" step="0.1" min="0" max="10" />
                        <flux:error name="editAromaScore" />
                    </div>
                    <div>
                        <flux:label>Gusto</flux:label>
                        <flux:input type="number" wire:model="editTasteScore" step="0.1" min="0" max="10" />
                        <flux:error name="editTasteScore" />
                    </div>
                    <div>
                        <flux:label>Global (0–100)</flux:label>
                        <flux:input type="number" wire:model="editOverallScore" step="0.1" min="0" max="100" />
                        <flux:error name="editOverallScore" />
                    </div>
                </div>

                <div>
                    <flux:label>Notas de cata</flux:label>
                    <flux:textarea wire:model="editTastingNotes" rows="3" />
                </div>
            </div>
            <div class="flex justify-end gap-3 px-6 py-4 border-t border-zinc-100">
                <flux:button wire:click="closeEdit" variant="ghost">Cancelar</flux:button>
                <flux:button wire:click="updateQualification" variant="primary">Guardar cambios</flux:button>
            </div>
        </div>
    </div>
    @endif

</div>
