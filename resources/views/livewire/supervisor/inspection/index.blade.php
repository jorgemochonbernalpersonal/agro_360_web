<div class="space-y-6 animate-fade-in">

    <x-agro.page-header title="{{ __('Control e Inspección') }}" :description="__('Gestión de inspecciones programadas a bodegas y viticultores.')">
        <x-slot name="actions">
            <flux:button wire:click="toggleCreate" variant="primary" icon="plus">{{ __('Nueva inspección') }}</flux:button>
        </x-slot>
    </x-agro.page-header>

    {{-- Create form --}}
    @if($showCreate)
    <x-agro.card>
        <h3 class="text-sm font-semibold text-zinc-700 mb-4">{{ __('Nueva inspección programada') }}</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <flux:label>{{ __('Tipo de sujeto') }}</flux:label>
                <flux:select wire:model.live="subject_type">
                    <flux:select.option value="winery">{{ __('Bodega') }}</flux:select.option>
                    <flux:select.option value="viticulturist">{{ __('Viticultor') }}</flux:select.option>
                </flux:select>
            </div>
            <div>
                <flux:label>{{ $subject_type === 'winery' ? 'Bodega' : 'Viticultor' }}</flux:label>
                <flux:select wire:model="subject_id">
                    <flux:select.option value="">{{ __('Selecciona...') }}</flux:select.option>
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
                <flux:error name="subject_id" />
            </div>
            <div>
                <flux:label>{{ __('Fecha inspección') }}</flux:label>
                <flux:input type="date" wire:model="inspection_date" />
                <flux:error name="inspection_date" />
            </div>
            <div>
                <flux:label>{{ __('Nº referencia') }}</flux:label>
                <flux:input type="text" wire:model="reference_number" :placeholder="__('Ej: INSP-2026-001')" />
            </div>
            <div class="sm:col-span-2">
                <flux:label>{{ __('Notas') }}</flux:label>
                <flux:textarea wire:model="notes" rows="2" />
            </div>
        </div>
        <div class="flex gap-3 mt-4">
            <flux:button wire:click="saveInspection" variant="primary">{{ __('Programar') }}</flux:button>
            <flux:button wire:click="toggleCreate" variant="ghost">{{ __('Cancelar') }}</flux:button>
        </div>
    </x-agro.card>
    @endif

    {{-- Filter by type --}}
    <div class="flex items-center gap-3">
        <flux:select wire:model.live="typeFilter">
            <flux:select.option value="">{{ __('Todos los tipos') }}</flux:select.option>
            <flux:select.option value="winery">{{ __('Bodegas') }}</flux:select.option>
            <flux:select.option value="viticulturist">{{ __('Viticultores') }}</flux:select.option>
        </flux:select>
        <x-agro.search-input wire:model.live.debounce.300ms="search" :placeholder="__('Buscar...')" />
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
                                        <p class="text-[10px] font-semibold text-agro-400 uppercase tracking-widest mb-0.5">{{ __('Fecha') }}</p>
                                        <p class="text-lg font-bold text-agro-700 leading-none">{{ $inspection->inspection_date->format('d/m') }}</p>
                                        <p class="text-[10px] text-agro-400 mt-0.5">{{ $inspection->inspection_date->format('Y') }}</p>
                                    </div>
                                    <div class="bg-agro-50 rounded-xl p-3">
                                        <p class="text-[10px] font-semibold text-agro-400 uppercase tracking-widest mb-0.5">{{ __('Resultado') }}</p>
                                        <p class="text-sm font-bold text-agro-700 leading-none mt-1">{{ \App\Models\DoInspection::RESULT_LABELS[$inspection->result] ?? '—' }}</p>
                                    </div>
                                </div>

                                <div class="space-y-2 text-sm">
                                    @if($inspection->reference_number)
                                        <div class="flex items-center justify-between">
                                            <span class="text-zinc-400">{{ __('Referencia') }}</span>
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
                                            {{ __('Cancelar') }}
                                        </button>
                                    @endif
                                    @if($inspection->result === \App\Models\DoInspection::RESULT_NON_COMPLIANT && $inspection->subject_type === 'winery')
                                        <button wire:click="createNonconformityFromInspection({{ $inspection->id }})"
                                            wire:confirm="{{ __('¿Generar un acta de no conformidad para esta inspección? Se creará como borrador en Solicitudes.') }}"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium bg-red-50 text-red-700 border border-red-200 rounded-md hover:bg-red-100 transition-colors">
                                            <flux:icon icon="exclamation-triangle" class="w-3 h-3" />
                                            Acta
                                        </button>
                                    @endif
                                    <x-agro.action-button icon="pencil" variant="default" wire:click="openEdit({{ $inspection->id }})" title="{{ __('Editar') }}" />
                                </div>
                            </x-slot:footer>
                        </x-agro.card>
                    @endforeach
                </div>
                <x-agro.pagination :paginator="$inspections" />
            @else
                <x-agro.empty-state icon="clipboard-document-check" title="{{ __('Sin inspecciones') }}" :description="__('No hay inspecciones registradas con estos filtros.')" />
            @endif
        </div>
    </div>

    {{-- Edit modal --}}
    @if($showEdit)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" wire:key="edit-inspection-{{ $editInspectionId }}">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4">
            <div class="flex items-center justify-between px-6 py-4 border-b border-zinc-100">
                <h3 class="text-base font-semibold text-zinc-800">{{ __('Editar inspección') }}</h3>
                <flux:button wire:click="closeEdit" variant="ghost" size="sm" icon="x-mark" />
            </div>
            <div class="p-6 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <flux:label>{{ __('Fecha inspección') }}</flux:label>
                        <flux:input type="date" wire:model="editInspectionDate" />
                        <flux:error name="editInspectionDate" />
                    </div>
                    <div>
                        <flux:label>{{ __('Resultado') }}</flux:label>
                        <flux:select wire:model="editResult">
                            <flux:select.option value="">{{ __('— Sin resultado —') }}</flux:select.option>
                            @foreach(\App\Models\DoInspection::RESULT_LABELS as $key => $label)
                                <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="editResult" />
                    </div>
                    <div class="sm:col-span-2">
                        <flux:label>{{ __('Nº referencia') }}</flux:label>
                        <flux:input type="text" wire:model="editReferenceNumber" :placeholder="__('Ej: INSP-2026-001')" />
                        <flux:error name="editReferenceNumber" />
                    </div>
                    <div class="sm:col-span-2">
                        <flux:label>{{ __('Hallazgos') }}</flux:label>
                        <flux:textarea wire:model="editFindings" rows="2" />
                    </div>
                    <div class="sm:col-span-2">
                        <flux:label>{{ __('Notas') }}</flux:label>
                        <flux:textarea wire:model="editNotes" rows="2" />
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-3 px-6 py-4 border-t border-zinc-100">
                <flux:button wire:click="closeEdit" variant="ghost">{{ __('Cancelar') }}</flux:button>
                <flux:button wire:click="updateInspection" variant="primary">{{ __('Guardar cambios') }}</flux:button>
            </div>
        </div>
    </div>
    @endif

</div>
