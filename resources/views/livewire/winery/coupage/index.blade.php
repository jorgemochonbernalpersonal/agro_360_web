<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="{{ __('Coupage / Mezclas') }}"
        :description="__('Registro de mezclas y ensamblajes de vinos entre depósitos')"
        icon="funnel"
    >
        <x-slot:actions>
            <flux:button variant="primary" icon="plus" href="{{ roleRoute('coupage.create') }}" wire:navigate>
                Nuevo coupage
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Filtro añada --}}
    <div class="flex items-center gap-3">
        <flux:select wire:model.live="vintageFilter" class="w-44">
            <flux:select.option value="">{{ __('Todas las añadas') }}</flux:select.option>
            @foreach($availableVintages as $vintage)
                <flux:select.option value="{{ $vintage }}">{{ $vintage }}</flux:select.option>
            @endforeach
        </flux:select>

        @if($vintageFilter)
            <flux:button wire:click="$set('vintageFilter', '')" variant="ghost" size="sm" icon="x-mark">{{ __('Limpiar') }}</flux:button>
        @endif
    </div>

    @if($blends->count() > 0)
        <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-zinc-100 bg-zinc-50 text-left text-xs font-semibold text-zinc-400 uppercase tracking-wider">
                        <th class="px-4 py-3">{{ __('Fecha') }}</th>
                        <th class="px-4 py-3">{{ __('Vino origen') }}</th>
                        <th class="px-4 py-3">{{ __('Depósito origen') }}</th>
                        <th class="px-4 py-3">{{ __('Vino resultante') }}</th>
                        <th class="px-4 py-3">{{ __('Depósito destino') }}</th>
                        <th class="px-4 py-3 text-right">{{ __('Cantidad') }}</th>
                        <th class="px-4 py-3">{{ __('Enólogo') }}</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @foreach($blends as $blend)
                        <tr wire:key="blend-{{ $blend->id }}" class="hover:bg-violet-50/40 transition-colors">
                            <td class="px-4 py-3 text-zinc-500 whitespace-nowrap">
                                {{ $blend->transfer_date->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3 text-zinc-600">
                                @if($blend->sourceWine)
                                    {{ $blend->sourceWine->name }}
                                    @if($blend->sourceWine->vintage)
                                        <span class="text-zinc-400 text-xs">({{ $blend->sourceWine->vintage }})</span>
                                    @endif
                                @else
                                    <span class="text-zinc-300">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-zinc-500">{{ $blend->fromContainer?->name ?? '—' }}</td>
                            <td class="px-4 py-3 font-medium text-zinc-900">
                                {{ $blend->wine?->name }}
                                @if($blend->wine?->vintage)
                                    <span class="text-zinc-400 text-xs font-normal">({{ $blend->wine->vintage }})</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-zinc-600 font-medium">{{ $blend->toContainer?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-right font-mono text-zinc-800 whitespace-nowrap">
                                {{ number_format($blend->quantity, 2) }}
                                <span class="text-zinc-400 text-xs font-normal">{{ $blend->unitOfMeasurement?->symbol }}</span>
                            </td>
                            <td class="px-4 py-3 text-zinc-500">{{ $blend->oenologist?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-right">
                                <button
                                    wire:click="delete({{ $blend->id }})"
                                    wire:confirm="{{ __('¿Eliminar este registro de coupage? Se revertirá el movimiento de volumen en los depósitos.') }}"
                                    wire:loading.attr="disabled"
                                    title="{{ __('Eliminar') }}"
                                    class="inline-flex items-center justify-center w-7 h-7 rounded-lg text-zinc-300 hover:text-red-500 hover:bg-red-50 transition-colors"
                                >
                                    <flux:icon icon="trash" class="size-3.5" />
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <x-agro-pagination :paginator="$blends" />
    @else
        <x-agro.empty-state
            icon="funnel"
            title="{{ $vintageFilter ? 'Ningún coupage para la añada seleccionada' : 'Sin coupages registrados' }}"
            description="{{ $vintageFilter ? 'Prueba con otra añada o limpia el filtro.' : 'Registra las mezclas y ensamblajes de vinos de tu bodega.' }}"
        >
            <x-slot:action>
                @if($vintageFilter)
                    <flux:button wire:click="$set('vintageFilter', '')" variant="outline" icon="x-mark">{{ __('Limpiar filtro') }}</flux:button>
                @else
                    <flux:button href="{{ roleRoute('coupage.create') }}" wire:navigate variant="primary" icon="plus">
                        Nuevo coupage
                    </flux:button>
                @endif
            </x-slot:action>
        </x-agro.empty-state>
    @endif

</div>
