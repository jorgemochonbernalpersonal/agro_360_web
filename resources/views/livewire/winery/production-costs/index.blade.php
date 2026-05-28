<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="{{ __('Costes de Producción') }}"
        :description="__('Seguimiento del coste por vino y margen de producción.')"
        icon="currency-euro"
    >
        <x-slot:actions>
            <flux:button variant="primary" icon="plus" href="{{ roleRoute('production-costs.create') }}" wire:navigate>
                Añadir coste
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- KPI --}}
    <div class="grid grid-cols-2 gap-4">
        <x-agro.card class="flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-green-50 flex items-center justify-center shrink-0">
                <flux:icon icon="beaker" class="size-5 text-green-600" />
            </div>
            <div>
                <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest">{{ __('Vinos en seguimiento') }}</p>
                <p class="text-2xl font-bold text-zinc-900 leading-none">{{ $wines->total() }}</p>
            </div>
        </x-agro.card>
        <x-agro.card class="flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-violet-50 flex items-center justify-center shrink-0">
                <flux:icon icon="currency-euro" class="size-5 text-violet-600" />
            </div>
            <div>
                <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest">{{ __('Costes extras registrados') }}</p>
                <p class="text-2xl font-bold text-zinc-900 leading-none">{{ number_format($totalManualCost, 2, ',', '.') }} €</p>
            </div>
        </x-agro.card>
    </div>

    {{-- Filtros --}}
    <div class="flex flex-wrap gap-3">
        <flux:select wire:model.live="vintageFilter">
            <flux:select.option value="">{{ __('Todas las añadas') }}</flux:select.option>
            @foreach($availableVintages as $v)
                <flux:select.option value="{{ $v }}">{{ $v }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select wire:model.live="statusFilter">
            <flux:select.option value="">{{ __('Todos los estados') }}</flux:select.option>
            @foreach($statuses as $key => $label)
                <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    {{-- Tabla de vinos --}}
    <x-agro.card>
        @if($wines->isEmpty())
            <x-agro.empty-state
                icon="beaker"
                title="{{ __('Sin vinos registrados') }}"
                :description="__('Crea vinos en el módulo de Vinificación para ver sus costes aquí.')"
            />
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-zinc-100">
                            <th class="text-left py-3 px-4 text-xs font-semibold text-zinc-400 uppercase tracking-wide">{{ __('Vino') }}</th>
                            <th class="text-right py-3 px-4 text-xs font-semibold text-zinc-400 uppercase tracking-wide">{{ __('Coste uva') }}</th>
                            <th class="text-right py-3 px-4 text-xs font-semibold text-zinc-400 uppercase tracking-wide">{{ __('Costes extras') }}</th>
                            <th class="text-right py-3 px-4 text-xs font-semibold text-zinc-400 uppercase tracking-wide">{{ __('Total') }}</th>
                            <th class="text-right py-3 px-4 text-xs font-semibold text-zinc-400 uppercase tracking-wide">{{ __('Volumen') }}</th>
                            <th class="text-right py-3 px-4 text-xs font-semibold text-zinc-400 uppercase tracking-wide">{{ __('€ / L') }}</th>
                            <th class="text-right py-3 px-4 text-xs font-semibold text-zinc-400 uppercase tracking-wide">{{ __('PVP / ud') }}</th>
                            <th class="py-3 px-4"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-50">
                        @foreach($wines as $wine)
                            @php
                                $grapeCost   = $wine->grape_cost;
                                $manualCost  = $wine->costs->sum('amount');
                                $totalCost   = $grapeCost + $manualCost;
                                $volume      = (float) $wine->volume_liters;
                                $costPerL    = $volume > 0 ? $totalCost / $volume : null;
                                $avgPvp      = $wine->productLots->whereNotNull('price_per_unit')->avg('price_per_unit');
                            @endphp
                            <tr class="hover:bg-zinc-50 transition-colors" wire:key="wine-{{ $wine->id }}">
                                <td class="py-3 px-4">
                                    <div>
                                        <p class="font-medium text-zinc-900">{{ $wine->name }}</p>
                                        <p class="text-xs text-zinc-400">
                                            Añada {{ $wine->vintage }}
                                            · {{ $wine->type_label }}
                                        </p>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    @if($grapeCost > 0)
                                        <span class="font-medium text-zinc-700">{{ number_format($grapeCost, 2, ',', '.') }} €</span>
                                    @else
                                        <span class="text-zinc-300">—</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right">
                                    @if($manualCost > 0)
                                        <span class="font-medium text-zinc-700">{{ number_format($manualCost, 2, ',', '.') }} €</span>
                                    @else
                                        <span class="text-zinc-300">—</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right">
                                    @if($totalCost > 0)
                                        <span class="font-bold text-zinc-900">{{ number_format($totalCost, 2, ',', '.') }} €</span>
                                    @else
                                        <span class="text-zinc-300">—</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right text-zinc-500">
                                    @if($volume > 0)
                                        {{ number_format($volume, 0, ',', '.') }} L
                                    @else
                                        <span class="text-zinc-300">—</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right">
                                    @if($costPerL !== null)
                                        @php
                                            $color = $costPerL < 0.5 ? 'text-green-600'
                                                   : ($costPerL < 1.5 ? 'text-amber-600' : 'text-red-600');
                                        @endphp
                                        <span class="font-bold {{ $color }}">{{ number_format($costPerL, 3, ',', '.') }} €</span>
                                    @else
                                        <span class="text-zinc-300">—</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right text-zinc-500">
                                    @if($avgPvp)
                                        {{ number_format($avgPvp, 2, ',', '.') }} €
                                    @else
                                        <span class="text-zinc-300">—</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ roleRoute('production-costs.create', ['wineId' => $wine->id]) }}"
                                           wire:navigate
                                           class="text-xs text-agro-600 hover:underline whitespace-nowrap">
                                            + Coste
                                        </a>
                                        @if($wine->costs->isNotEmpty())
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-medium bg-zinc-100 text-zinc-600">
                                                {{ $wine->costs->count() }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            {{-- Desglose de costes extras --}}
                            @if($wine->costs->isNotEmpty())
                                @foreach($wine->costs->groupBy('category') as $cat => $items)
                                    @php
                                        $catTotal = $items->sum('amount');
                                        $catColor = \App\Models\WineCost::CATEGORY_COLORS[$cat] ?? 'zinc';
                                        $catLabel = __(\App\Models\WineCost::CATEGORIES[$cat] ?? $cat);
                                    @endphp
                                    <tr class="bg-zinc-50" wire:key="wine-{{ $wine->id }}-cat-{{ $cat }}">
                                        <td class="py-1.5 px-4 pl-10">
                                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-medium bg-{{ $catColor }}-100 text-{{ $catColor }}-700">
                                                {{ $catLabel }}
                                            </span>
                                        </td>
                                        <td></td>
                                        <td class="py-1.5 px-4 text-right text-xs text-zinc-500">
                                            {{ number_format($catTotal, 2, ',', '.') }} €
                                        </td>
                                        <td colspan="4"></td>
                                        <td class="py-1.5 px-4 text-right">
                                            @foreach($items as $costItem)
                                                <a href="{{ roleRoute('production-costs.edit', $costItem) }}"
                                                   wire:navigate
                                                   class="text-[10px] text-zinc-400 hover:text-agro-600 block text-right truncate max-w-[120px] ml-auto">
                                                    {{ $costItem->description }}
                                                </a>
                                            @endforeach
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>

            <x-agro-pagination :paginator="$wines" />
        @endif
    </x-agro.card>

</div>
