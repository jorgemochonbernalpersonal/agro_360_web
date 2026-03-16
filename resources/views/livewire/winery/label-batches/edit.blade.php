<x-agro.form-card
    title="Editar Lote · {{ $batch->name }}"
    description="Modifica los datos y registra mermas del lote."
    icon="tag"
    icon-color="from-violet-500 to-violet-700"
    :back-url="roleRoute('label-batches.index')"
>
    {{-- ── Disponibilidad ──────────────────────────────────────────────────── --}}
    <div class="mb-6 grid grid-cols-2 md:grid-cols-4 gap-4">
        @php
            $pct = $batch->usage_percent;
        @endphp
        <div class="bg-zinc-50 rounded-xl p-4 text-center border border-zinc-200">
            <p class="text-xs text-zinc-400 uppercase tracking-wide mb-1">Total</p>
            <p class="text-2xl font-bold text-zinc-700">{{ number_format($batch->total_quantity) }}</p>
            <p class="text-xs text-zinc-400">{{ number_format($batch->start_number) }} – {{ number_format($batch->end_number) }}</p>
        </div>
        <div class="bg-agro-50 rounded-xl p-4 text-center border border-agro-100">
            <p class="text-xs text-agro-400 uppercase tracking-wide mb-1">Disponibles</p>
            <p class="text-2xl font-bold text-agro-700">{{ number_format($batch->available_quantity) }}</p>
        </div>
        <div class="bg-blue-50 rounded-xl p-4 text-center border border-blue-100">
            <p class="text-xs text-blue-400 uppercase tracking-wide mb-1">Usadas</p>
            <p class="text-2xl font-bold text-blue-700">{{ number_format($batch->used_quantity) }}</p>
        </div>
        <div class="bg-orange-50 rounded-xl p-4 text-center border border-orange-100">
            <p class="text-xs text-orange-400 uppercase tracking-wide mb-1">Mermas</p>
            <p class="text-2xl font-bold text-orange-600">{{ number_format($batch->wasted_quantity) }}</p>
        </div>
    </div>

    <form wire:submit="save" class="space-y-8">

        <x-agro.form-section title="Datos del lote" color="violet">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <flux:field class="lg:col-span-2">
                    <flux:label required>Nombre / descripción</flux:label>
                    <flux:input wire:model="name" required />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>Vino asociado</flux:label>
                    <flux:select wire:model="wine_id">
                        <flux:select.option value="">Sin vino específico</flux:select.option>
                        @foreach($wines as $wine)
                            <flux:select.option value="{{ $wine->id }}">
                                {{ $wine->name }}{{ $wine->vintage ? ' · ' . $wine->vintage : '' }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="wine_id" />
                </flux:field>

                <flux:field>
                    <flux:label required>Origen</flux:label>
                    <flux:select wire:model="source" required>
                        @foreach($sources as $key => $label)
                            <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="source" />
                </flux:field>

            </div>
        </x-agro.form-section>

        <x-agro.form-section title="Notas" color="zinc">
            <flux:field>
                <flux:label>Observaciones</flux:label>
                <flux:textarea wire:model="notes" rows="2" />
                <flux:error name="notes" />
            </flux:field>
        </x-agro.form-section>

        <x-agro.form-actions :back-url="roleRoute('label-batches.index')" submit-label="Guardar cambios" />
    </form>

    {{-- ── Mermas ──────────────────────────────────────────────────────────── --}}
    <div class="mt-10 space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-base font-semibold text-zinc-900">Mermas registradas</h3>
            @if($batch->available_quantity > 0)
                <flux:button wire:click="toggleWasteForm" variant="ghost" icon="plus" size="sm">
                    {{ $showWasteForm ? 'Cancelar' : 'Registrar merma' }}
                </flux:button>
            @endif
        </div>

        @if($showWasteForm)
            <div class="p-5 bg-orange-50 rounded-2xl border border-orange-200 space-y-4">
                <p class="text-sm font-medium text-orange-700">Nueva merma — disponibles: <strong>{{ number_format($batch->available_quantity) }}</strong></p>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <flux:field>
                        <flux:label required>Cantidad</flux:label>
                        <flux:input wire:model="waste_quantity" type="number" min="1" step="1" placeholder="0" />
                        <flux:error name="waste_quantity" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Nº inicial</flux:label>
                        <flux:input wire:model="waste_from" type="number" min="1" step="1" placeholder="Opcional" />
                        <flux:error name="waste_from" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Nº final</flux:label>
                        <flux:input wire:model="waste_to" type="number" min="1" step="1" placeholder="Opcional" />
                        <flux:error name="waste_to" />
                    </flux:field>
                    <flux:field>
                        <flux:label required>Fecha</flux:label>
                        <flux:input wire:model="waste_date" type="date" required />
                        <flux:error name="waste_date" />
                    </flux:field>
                </div>
                <flux:field>
                    <flux:label>Motivo</flux:label>
                    <flux:textarea wire:model="waste_reason" rows="2" placeholder="Daño en almacén, error de impresión..." />
                    <flux:error name="waste_reason" />
                </flux:field>
                <div class="flex gap-3">
                    <flux:button wire:click="saveWaste" variant="primary" size="sm">Guardar merma</flux:button>
                    <flux:button wire:click="toggleWasteForm" variant="ghost" size="sm">Cancelar</flux:button>
                </div>
            </div>
        @endif

        @if($wastes->isNotEmpty())
            <div class="bg-white rounded-2xl border border-zinc-200 overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-zinc-50 border-b border-zinc-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wide">Fecha</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-zinc-500 uppercase tracking-wide">Cantidad</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wide">Rango</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wide">Motivo</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @foreach($wastes as $waste)
                            <tr wire:key="waste-{{ $waste->id }}">
                                <td class="px-4 py-3 text-zinc-600">{{ $waste->waste_date->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-orange-600">{{ number_format($waste->quantity) }}</td>
                                <td class="px-4 py-3 text-zinc-500">
                                    @if($waste->from_number && $waste->to_number)
                                        {{ number_format($waste->from_number) }} – {{ number_format($waste->to_number) }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-zinc-500 max-w-xs truncate">{{ $waste->reason ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <button
                                        wire:click="deleteWaste({{ $waste->id }})"
                                        wire:confirm="¿Eliminar esta merma?"
                                        title="Eliminar"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-red-600 hover:bg-red-50 transition-colors">
                                        <flux:icon icon="trash" class="size-4" />
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-sm text-zinc-400 py-2">No hay mermas registradas en este lote.</p>
        @endif
    </div>
</x-agro.form-card>
