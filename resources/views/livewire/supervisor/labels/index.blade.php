<div class="space-y-6 animate-fade-in">

    <x-agro.page-header title="{{ __('Contraetiquetas') }}" :description="__('Solicitudes de contraetiquetas por bodega y añada.')">
        <x-slot name="actions">
            <flux:button wire:click="toggleCreate" variant="primary" icon="plus">{{ __('Nueva solicitud') }}</flux:button>
        </x-slot>
    </x-agro.page-header>

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <x-agro.stat-card :label="__('Total emitidas')" :value="number_format($totalIssued, 0, ',', '.')" icon="tag" color="agro"
            :description="$vintageFilter ? 'Añada ' . $vintageFilter : 'Todas las añadas'" />
        <x-agro.stat-card :label="__('Solicitudes pendientes')" :value="$tabs['pending']['count']" icon="inbox" color="yellow" />
    </div>

    {{-- Create form --}}
    @if($showCreate)
    <x-agro.card>
        <h3 class="text-sm font-semibold text-zinc-700 mb-4">{{ __('Nueva solicitud de contraetiquetas') }}</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <flux:label>{{ __('Bodega') }}</flux:label>
                <flux:select wire:model="winery_id">
                    <flux:select.option value="">{{ __('Selecciona bodega...') }}</flux:select.option>
                    @foreach($wineries as $w)
                        <flux:select.option value="{{ $w->id }}">{{ $w->name }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="winery_id" />
            </div>
            <div>
                <flux:label>{{ __('Añada') }}</flux:label>
                <flux:input type="number" wire:model="vintage" min="1990" max="2100" />
                <flux:error name="vintage" />
            </div>
            <div>
                <flux:label>{{ __('Nº de lote') }}</flux:label>
                <flux:input type="text" wire:model="batch_number" :placeholder="__('Opcional')" />
            </div>
            <div>
                <flux:label>{{ __('Cantidad solicitada') }}</flux:label>
                <flux:input type="number" wire:model="quantity_requested" min="1" />
                <flux:error name="quantity_requested" />
            </div>
            <div class="sm:col-span-2">
                <flux:label>{{ __('Notas') }}</flux:label>
                <flux:textarea wire:model="notes" rows="2" />
            </div>
        </div>
        <div class="flex gap-3 mt-4">
            <flux:button wire:click="saveLabel" variant="primary">{{ __('Registrar solicitud') }}</flux:button>
            <flux:button wire:click="toggleCreate" variant="ghost">{{ __('Cancelar') }}</flux:button>
        </div>
    </x-agro.card>
    @endif

    {{-- Filters --}}
    <div class="flex items-center gap-3">
        @if($availableVintages->isNotEmpty())
            <flux:select wire:model.live="vintageFilter">
                <flux:select.option value="">{{ __('Todas las añadas') }}</flux:select.option>
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
                                <x-agro.card-item-header
                                    icon="tag"
                                    :title="$label->winery?->name ?? '---'"
                                    :subtitle="'Añada ' . $label->vintage"
                                    iconBg="bg-indigo-100"
                                    iconColor="text-indigo-600"
                                    size="md"
                                    radius="xl"
                                >
                                    <x-agro.status-badge :status="$label->status" :labels="\App\Models\DoLabel::STATUS_LABELS" />
                                </x-agro.card-item-header>
                            </x-slot:header>

                            <div class="flex-1 space-y-4">
                                <div class="grid grid-cols-2 gap-2">
                                    <div class="bg-indigo-50 rounded-xl p-3">
                                        <p class="text-[10px] font-semibold text-indigo-400 uppercase tracking-widest mb-0.5">{{ __('Solicitadas') }}</p>
                                        <p class="text-2xl font-bold text-indigo-700 leading-none">{{ number_format($label->quantity_requested, 0, ',', '.') }}</p>
                                    </div>
                                    <div class="bg-agro-50 rounded-xl p-3">
                                        <p class="text-[10px] font-semibold text-agro-400 uppercase tracking-widest mb-0.5">{{ __('Emitidas') }}</p>
                                        <p class="text-2xl font-bold text-agro-700 leading-none">{{ $label->quantity_issued > 0 ? number_format($label->quantity_issued, 0, ',', '.') : '---' }}</p>
                                    </div>
                                </div>

                                <div class="space-y-2 text-sm">
                                    <div class="flex items-center justify-between">
                                        <span class="text-zinc-400">{{ __('Lote') }}</span>
                                        <span class="text-zinc-700 font-medium">{{ $label->batch_number ?? '---' }}</span>
                                    </div>
                                </div>
                            </div>

                            <x-slot:footer>
                                <div class="flex items-center justify-end gap-0.5">
                                    @if($label->status === 'pending')
                                        <x-agro.action-button icon="check-circle" variant="success" wire:click="approve({{ $label->id }})" title="{{ __('Aprobar') }}" />
                                    @endif
                                    @if(in_array($label->status, ['pending', 'approved']))
                                        <x-agro.action-button icon="printer" variant="primary" wire:click="issue({{ $label->id }})" title="{{ __('Emitir') }}" />
                                        <x-agro.action-button icon="x-mark" variant="danger" wire:click="cancel({{ $label->id }})" title="{{ __('Cancelar') }}" />
                                    @endif
                                </div>
                            </x-slot:footer>
                        </x-agro.card>
                    @endforeach
                </div>

                <x-agro-pagination :paginator="$labels" />
            @else
                <x-agro.empty-state icon="tag" title="{{ __('No hay solicitudes') }}" :description="__('No hay solicitudes de contraetiquetas.')" />
            @endif
        </div>
    </div>

</div>
