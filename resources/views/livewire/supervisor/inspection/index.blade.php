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
                <flux:label>Tipo de sujeto</flux:label>
                <flux:select wire:model.live="subject_type">
                    <flux:select.option value="winery">Bodega</flux:select.option>
                    <flux:select.option value="viticulturist">Viticultor</flux:select.option>
                </flux:select>
            </div>
            <div>
                <flux:label>{{ $subject_type === 'winery' ? 'Bodega' : 'Viticultor' }}</flux:label>
                <flux:select wire:model="subject_id">
                    <flux:select.option value="">Selecciona...</flux:select.option>
                    @if($subject_type === 'winery')
                        @foreach($wineries as $w)
                            <flux:select.option value="{{ $w->id }}">{{ $w->name }}</flux:select.option>
                        @endforeach
                    @else
                        @foreach($viticulturists as $v)
                            <flux:select.option value="{{ $v->id }}">{{ $v->name }}</flux:select.option>
                        @endforeach
                    @endif
                </flux:select>
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
        <flux:select wire:model.live="typeFilter">
            <flux:select.option value="">Todos los tipos</flux:select.option>
            <flux:select.option value="winery">Bodegas</flux:select.option>
            <flux:select.option value="viticulturist">Viticultores</flux:select.option>
        </flux:select>
        <x-agro.search-input wire:model.live.debounce.300ms="search" placeholder="Buscar..." />
    </div>

    {{-- Tabs --}}
    <div>
        <x-agro.tabs :tabs="$tabs" :active="$currentTab" wireMethod="switchTab" />

        {{-- Skeleton durante carga --}}
        <x-agro.loading-grid target="search, switchTab, typeFilter, nextPage, previousPage" />

        {{-- Card grid --}}
        <div wire:loading.remove wire:target="search, switchTab, typeFilter, nextPage, previousPage">
            @if($inspections->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    @foreach($inspections as $inspection)
                        @php
                            $delay = min($loop->index * 50, 300);
                            $statusColor = match($inspection->status) {
                                'scheduled'   => 'blue',
                                'in_progress' => 'yellow',
                                'completed'   => 'green',
                                'cancelled'   => 'red',
                                default       => 'zinc',
                            };
                            $statusLabel = \App\Models\DoInspection::STATUS_LABELS[$inspection->status] ?? $inspection->status;
                            $isWinery = $inspection->subject_type === 'winery';
                        @endphp
                        <x-agro.card
                            class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                            style="animation-delay: {{ $delay }}ms;"
                            wire:key="inspection-{{ $inspection->id }}"
                        >
                            <x-slot:header>
                                <x-agro.card-item-header
                                    :icon="$isWinery ? 'building-office' : 'user'"
                                    :title="$inspection->subject?->name ?? '—'"
                                    :subtitle="$isWinery ? 'Bodega' : 'Viticultor'"
                                    :iconBg="$isWinery ? 'bg-blue-100' : 'bg-emerald-100'"
                                    :iconColor="$isWinery ? 'text-blue-600' : 'text-emerald-600'"
                                    size="md"
                                    radius="xl"
                                >
                                    <flux:badge color="{{ $statusColor }}" size="sm">{{ $statusLabel }}</flux:badge>
                                </x-agro.card-item-header>
                            </x-slot:header>

                            <div class="flex-1 space-y-4">
                                <div class="grid grid-cols-2 gap-2">
                                    <div class="bg-agro-50 rounded-xl p-3">
                                        <p class="text-[10px] font-semibold text-agro-400 uppercase tracking-widest mb-0.5">Fecha</p>
                                        <p class="text-lg font-bold text-agro-700 leading-none">{{ $inspection->inspection_date->format('d/m') }}</p>
                                        <p class="text-[10px] text-agro-400 mt-0.5">{{ $inspection->inspection_date->format('Y') }}</p>
                                    </div>
                                    <div class="bg-agro-50 rounded-xl p-3">
                                        <p class="text-[10px] font-semibold text-agro-400 uppercase tracking-widest mb-0.5">Resultado</p>
                                        <p class="text-sm font-bold text-agro-700 leading-none mt-1">{{ \App\Models\DoInspection::RESULT_LABELS[$inspection->result] ?? '—' }}</p>
                                    </div>
                                </div>

                                <div class="space-y-2 text-sm">
                                    @if($inspection->reference_number)
                                        <div class="flex items-center justify-between">
                                            <span class="text-zinc-400">Referencia</span>
                                            <span class="text-zinc-700 font-medium font-mono text-xs">{{ $inspection->reference_number }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <x-slot:footer>
                                <div class="flex items-center justify-end gap-1 flex-wrap">
                                    @if($inspection->status === 'scheduled')
                                        <button wire:click="updateStatus({{ $inspection->id }}, 'in_progress')"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200 rounded-md hover:bg-blue-100 transition-colors">
                                            Iniciar
                                        </button>
                                    @endif
                                    @if($inspection->status === 'in_progress')
                                        <button wire:click="updateStatus({{ $inspection->id }}, 'completed')"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium bg-agro-50 text-agro-700 border border-agro-200 rounded-md hover:bg-agro-100 transition-colors">
                                            Completar
                                        </button>
                                    @endif
                                    @if(in_array($inspection->status, ['scheduled', 'in_progress']))
                                        <button wire:click="updateStatus({{ $inspection->id }}, 'cancelled')"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium bg-red-50 text-red-600 border border-red-200 rounded-md hover:bg-red-100 transition-colors">
                                            Cancelar
                                        </button>
                                    @endif
                                    @if($inspection->result === \App\Models\DoInspection::RESULT_NON_COMPLIANT && $inspection->subject_type === 'winery')
                                        <button wire:click="createNonconformityFromInspection({{ $inspection->id }})"
                                            wire:confirm="¿Generar un acta de no conformidad para esta inspección? Se creará como borrador en Solicitudes."
                                            class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium bg-red-50 text-red-700 border border-red-200 rounded-md hover:bg-red-100 transition-colors">
                                            <flux:icon icon="exclamation-triangle" class="w-3 h-3" />
                                            Acta
                                        </button>
                                    @endif
                                    @php
                                        $btnBase = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors';
                                    @endphp
                                    <button wire:click="openEdit({{ $inspection->id }})" class="{{ $btnBase }}" title="Editar">
                                        <flux:icon icon="pencil" class="size-4" />
                                    </button>
                                </div>
                            </x-slot:footer>
                        </x-agro.card>
                    @endforeach
                </div>
                <div class="mt-6">{{ $inspections->links() }}</div>
            @else
                <x-agro.empty-state icon="clipboard-document-check" title="Sin inspecciones" description="No hay inspecciones registradas con estos filtros." />
            @endif
        </div>
    </div>

    {{-- Edit modal --}}
    @if($showEdit)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" wire:key="edit-inspection-{{ $editInspectionId }}">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4">
            <div class="flex items-center justify-between px-6 py-4 border-b border-zinc-100">
                <h3 class="text-base font-semibold text-zinc-800">Editar inspección</h3>
                <button wire:click="closeEdit" class="text-zinc-400 hover:text-zinc-600">
                    <flux:icon icon="x-mark" class="w-5 h-5" />
                </button>
            </div>
            <div class="p-6 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-zinc-600 mb-1">Fecha inspección</label>
                        <input type="date" wire:model="editInspectionDate" class="w-full text-sm border border-zinc-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-300" />
                        @error('editInspectionDate') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <flux:label>Resultado</flux:label>
                        <flux:select wire:model="editResult">
                            <flux:select.option value="">— Sin resultado —</flux:select.option>
                            @foreach(\App\Models\DoInspection::RESULT_LABELS as $key => $label)
                                <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        @error('editResult') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-zinc-600 mb-1">Nº referencia</label>
                        <input type="text" wire:model="editReferenceNumber" placeholder="Ej: INSP-2026-001" class="w-full text-sm border border-zinc-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-300" />
                        @error('editReferenceNumber') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-zinc-600 mb-1">Hallazgos</label>
                        <textarea wire:model="editFindings" rows="2" class="w-full text-sm border border-zinc-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-300"></textarea>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-zinc-600 mb-1">Notas</label>
                        <textarea wire:model="editNotes" rows="2" class="w-full text-sm border border-zinc-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-300"></textarea>
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-3 px-6 py-4 border-t border-zinc-100">
                <button wire:click="closeEdit" class="px-4 py-2 text-sm font-medium text-zinc-600 border border-zinc-200 rounded-lg hover:bg-zinc-50 transition">Cancelar</button>
                <button wire:click="updateInspection" class="px-4 py-2 text-sm font-medium bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">Guardar cambios</button>
            </div>
        </div>
    </div>
    @endif

</div>
