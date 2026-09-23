@php
    $fmt = fn (float $n, int $d = 2) => number_format($n, $d, ',', '.');
@endphp

<div class="grid lg:grid-cols-2 gap-8">
    {{-- Datos --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 space-y-5">
        <h2 class="text-lg font-bold text-gray-900">1. Tratamiento</h2>
        <div class="grid sm:grid-cols-3 gap-4">
            <x-tools.number-field model="hectares" label="Superficie" suffix="ha" />
            <x-tools.number-field model="litresPerHectare" label="Caldo" suffix="L/ha" help="Viñedo: 200–1.000 L/ha" />
            <x-tools.number-field model="tankLitres" label="Cuba" suffix="L" />
        </div>

        <h2 class="text-lg font-bold text-gray-900 pt-2">2. Productos (según etiqueta)</h2>
        <div class="space-y-4">
            @foreach ($products as $i => $product)
                <div wire:key="product-{{ $i }}" class="rounded-xl bg-gray-50 border border-gray-200 p-4">
                    <div class="grid grid-cols-6 gap-3">
                        <div class="col-span-6 sm:col-span-6">
                            <label for="products-{{ $i }}-name" class="block text-sm font-medium text-gray-700 mb-1">Producto</label>
                            <input id="products-{{ $i }}-name" type="text" wire:model.live.debounce.500ms="products.{{ $i }}.name" placeholder="Ej.: azufre mojable" class="block w-full rounded-lg border-gray-300 shadow-sm">
                        </div>
                        <div class="col-span-3">
                            <label for="products-{{ $i }}-dose" class="block text-sm font-medium text-gray-700 mb-1">Dosis</label>
                            <input id="products-{{ $i }}-dose" type="number" inputmode="decimal" step="any" min="0" wire:model.live.debounce.300ms="products.{{ $i }}.dose" class="block w-full rounded-lg border-gray-300 shadow-sm">
                        </div>
                        <div class="col-span-3">
                            <label for="products-{{ $i }}-mode" class="block text-sm font-medium text-gray-700 mb-1">Unidad</label>
                            <select id="products-{{ $i }}-mode" wire:model.live="products.{{ $i }}.mode" class="block w-full rounded-lg border-gray-300 shadow-sm">
                                @foreach ($modes as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @error("products.$i.dose") <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    @if (count($products) > 1)
                        <button type="button" wire:click="removeProduct({{ $i }})" class="mt-2 text-sm text-red-600 hover:underline">Quitar producto</button>
                    @endif
                </div>
            @endforeach
        </div>

        @if (count($products) < \App\Livewire\Tools\SprayCalculator::MAX_PRODUCTS)
            <button type="button" wire:click="addProduct" class="text-sm font-semibold text-[var(--color-agro-green-dark)] hover:underline">+ Añadir otro producto a la mezcla</button>
        @endif
    </div>

    {{-- Resultado --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6" aria-live="polite">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Resultado</h2>

        @if ($this->result)
            @php $r = $this->result; @endphp
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div class="rounded-xl bg-[var(--color-agro-green-dark)] text-white p-4">
                    <div class="text-sm text-white/70">Caldo total</div>
                    <div class="text-2xl font-bold">{{ $fmt($r['total_litres'], 0) }} L</div>
                </div>
                <div class="rounded-xl bg-[var(--color-agro-green-dark)] text-white p-4">
                    <div class="text-sm text-white/70">Cubas</div>
                    <div class="text-2xl font-bold">{{ $r['tanks'] }}</div>
                </div>
            </div>

            <p class="text-sm text-gray-600 mb-4">
                {{ $r['full_tanks'] }} {{ $r['full_tanks'] === 1 ? 'cuba llena' : 'cubas llenas' }}
                @if ($r['last_tank_litres'] > 0)
                    + 1 última cuba de <strong>{{ $fmt($r['last_tank_litres'], 0) }} L</strong>
                @endif
                · cada cuba llena cubre {{ $fmt($r['hectares_per_tank']) }} ha.
            </p>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b">
                            <th class="py-2 pr-2 font-medium">Producto</th>
                            <th class="py-2 px-2 font-medium text-right">Por cuba llena</th>
                            @if ($r['last_tank_litres'] > 0)
                                <th class="py-2 px-2 font-medium text-right">Última cuba</th>
                            @endif
                            <th class="py-2 pl-2 font-medium text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($r['products'] as $row)
                            <tr class="border-b last:border-0">
                                <td class="py-2 pr-2 font-medium text-gray-900">{{ $row['name'] }}</td>
                                <td class="py-2 px-2 text-right">{{ $fmt($row['per_tank'], 3) }}</td>
                                @if ($r['last_tank_litres'] > 0)
                                    <td class="py-2 px-2 text-right">{{ $fmt($row['last_tank'], 3) }}</td>
                                @endif
                                <td class="py-2 pl-2 text-right">{{ $fmt($row['total'], 3) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="mt-3 text-xs text-gray-500">Cantidades en L (líquidos) o kg (sólidos), según el formato del producto. Comprueba que la dosis por hectárea resultante no supera la máxima autorizada en la etiqueta.</p>
        @else
            <p class="text-gray-500">Rellena la superficie, el caldo, la cuba y la dosis de al menos un producto para ver el cálculo.</p>
        @endif
    </div>
</div>
