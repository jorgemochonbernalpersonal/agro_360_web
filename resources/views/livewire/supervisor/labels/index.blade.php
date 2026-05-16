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
            <flux:select wire:model.live="vintageFilter">
                <flux:select.option value="">Todas las añadas</flux:select.option>
                @foreach($availableVintages as $v)
                    <flux:select.option value="{{ $v }}">{{ $v }}</flux:select.option>
                @endforeach
            </flux:select>
        @endif
    </div>

    {{-- Tabs + Cards --}}
    <div>
        <x-agro.tabs :tabs="$tabs" :active="$currentTab" wireMethod="switchTab" />

        {{-- Loading skeleton --}}
        <x-agro.loading-grid target="switchTab, vintageFilter, nextPage, previousPage" />

        <div wire:loading.remove wire:target="switchTab, vintageFilter, nextPage, previousPage">
            @if($labels->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    @foreach($labels as $label)
                        @php $delay = min($loop->index * 50, 300); @endphp
                        <x-agro.card
                            class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                            style="animation-delay: {{ $delay }}ms;"
                            wire:key="label-{{ $label->id }}"
                        >
                            <x-slot:header>
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center shrink-0">
                                        <flux:icon icon="tag" class="size-5 text-indigo-600" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="font-bold text-zinc-900 truncate">{{ $label->winery?->name ?? '---' }}</h3>
                                        <p class="text-xs text-zinc-500">Añada {{ $label->vintage }}</p>
                                    </div>
                                    <x-agro.status-badge :status="$label->status" :labels="\App\Models\DoLabel::STATUS_LABELS" class="shrink-0" />
                                </div>
                            </x-slot:header>

                            <div class="flex-1 space-y-4">
                                <div class="grid grid-cols-2 gap-2">
                                    <div class="bg-indigo-50 rounded-xl p-3">
                                        <p class="text-[10px] font-semibold text-indigo-400 uppercase tracking-widest mb-0.5">Solicitadas</p>
                                        <p class="text-2xl font-bold text-indigo-700 leading-none">{{ number_format($label->quantity_requested, 0, ',', '.') }}</p>
                                    </div>
                                    <div class="bg-agro-50 rounded-xl p-3">
                                        <p class="text-[10px] font-semibold text-agro-400 uppercase tracking-widest mb-0.5">Emitidas</p>
                                        <p class="text-2xl font-bold text-agro-700 leading-none">{{ $label->quantity_issued > 0 ? number_format($label->quantity_issued, 0, ',', '.') : '---' }}</p>
                                    </div>
                                </div>

                                <div class="space-y-2 text-sm">
                                    <div class="flex items-center justify-between">
                                        <span class="text-zinc-400">Lote</span>
                                        <span class="text-zinc-700 font-medium">{{ $label->batch_number ?? '---' }}</span>
                                    </div>
                                </div>
                            </div>

                            <x-slot:footer>
                                @php
                                    $btnBase = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors';
                                    $btnSuccess = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-blue-600 hover:bg-blue-50 transition-colors';
                                    $btnAgro = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-agro-600 hover:bg-agro-50 transition-colors';
                                    $btnDanger = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-red-500 hover:bg-red-50 transition-colors';
                                @endphp
                                <div class="flex items-center justify-end gap-0.5">
                                    @if($label->status === 'pending')
                                        <button wire:click="approve({{ $label->id }})" class="{{ $btnSuccess }}" title="Aprobar">
                                            <flux:icon icon="check-circle" class="size-4" />
                                        </button>
                                    @endif
                                    @if(in_array($label->status, ['pending', 'approved']))
                                        <button wire:click="issue({{ $label->id }})" class="{{ $btnAgro }}" title="Emitir">
                                            <flux:icon icon="printer" class="size-4" />
                                        </button>
                                        <button wire:click="cancel({{ $label->id }})" class="{{ $btnDanger }}" title="Cancelar">
                                            <flux:icon icon="x-mark" class="size-4" />
                                        </button>
                                    @endif
                                </div>
                            </x-slot:footer>
                        </x-agro.card>
                    @endforeach
                </div>

                <div class="mt-6">{{ $labels->links() }}</div>
            @else
                <x-agro.empty-state icon="tag" title="No hay solicitudes" description="No hay solicitudes de contraetiquetas." />
            @endif
        </div>
    </div>

</div>
