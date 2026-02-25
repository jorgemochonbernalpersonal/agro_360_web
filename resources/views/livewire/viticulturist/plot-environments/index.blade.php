<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Entorno de Parcelas"
        subtitle="Zonas protegidas, captaciones de agua y condiciones ambientales"
        icon="map"
    >
        <x-slot:actions>
            <flux:button variant="primary" icon="plus" wire:click="openCreate">Nueva Ficha</flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    <flux:callout variant="info" icon="information-circle">
        El registro de zonas protegidas y captaciones de agua es obligatorio en programas de Producción Integrada y para el uso de fitosanitarios cerca de masas de agua (RD 1311/2012).
    </flux:callout>

    <x-agro.filter-bar>
        <x-agro.filter-select wire:model.live="filterCampaign" label="Campaña">
            <option value="">Todas</option>
            @foreach($campaigns as $c)
                <option value="{{ $c->id }}">{{ $c->name }}</option>
            @endforeach
        </x-agro.filter-select>
    </x-agro.filter-bar>

    <x-agro.card>
        @if($entries->isEmpty())
            <x-agro.empty-state
                icon="map"
                title="Sin fichas de entorno"
                description="Registra las condiciones ambientales de cada parcela: zonas protegidas, distancia a captaciones de agua, pendiente..."
            />
        @else
            <x-agro.data-table :headers="['Parcela', 'Variedad', 'Captación agua', 'Zona protegida', 'Pendiente', 'Riesgo erosión', 'Acciones']">
                @foreach($entries as $entry)
                    <x-agro.table-row>
                        <x-agro.table-cell class="font-medium">{{ $entry->plot->name ?? '-' }}</x-agro.table-cell>
                        <x-agro.table-cell>{{ $entry->plotPlanting?->grape?->name ?? 'Global' }}</x-agro.table-cell>
                        <x-agro.table-cell>
                            @if($entry->water_intake_nearby)
                                <span class="text-amber-600 font-medium text-sm">⚠️ Sí</span>
                                @if($entry->water_intake_distance_m)
                                    <span class="text-zinc-400 text-xs block">a {{ $entry->water_intake_distance_m }} m</span>
                                @endif
                            @else
                                <span class="text-zinc-400 text-sm">No</span>
                            @endif
                        </x-agro.table-cell>
                        <x-agro.table-cell>
                            @if($entry->protected_zone_total)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-700">Total</span>
                            @elseif($entry->protected_zone_partial)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-700">Parcial</span>
                                @if($entry->protection_zone_type)
                                    <span class="text-zinc-400 text-xs block">{{ $entry->protection_zone_type }}</span>
                                @endif
                            @else
                                <span class="text-zinc-400 text-sm">No</span>
                            @endif
                        </x-agro.table-cell>
                        <x-agro.table-cell>{{ $entry->slope_pct ? $entry->slope_pct . '%' : '-' }}</x-agro.table-cell>
                        <x-agro.table-cell>
                            @if($entry->erosion_risk)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-700">⚠️ Sí</span>
                            @else
                                <span class="text-zinc-400 text-sm">No</span>
                            @endif
                        </x-agro.table-cell>
                        <x-agro.table-cell align="right">
                            <div class="flex items-center justify-end gap-2">
                                <flux:button size="sm" variant="ghost" icon="pencil" wire:click="openEdit({{ $entry->id }})">Editar</flux:button>
                                <flux:button size="sm" variant="ghost" icon="trash" wire:click="delete({{ $entry->id }})" wire:confirm="¿Eliminar esta ficha?">Eliminar</flux:button>
                            </div>
                        </x-agro.table-cell>
                    </x-agro.table-row>
                @endforeach
            </x-agro.data-table>
            <div class="mt-4">{{ $entries->links() }}</div>
        @endif
    </x-agro.card>

    <flux:modal wire:model="showModal" class="max-w-2xl">
        <div class="p-6 space-y-6">
            <h2 class="text-lg font-semibold text-zinc-900">{{ $editingId ? 'Editar Entorno' : 'Nueva Ficha de Entorno' }}</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:field>
                    <flux:label required>Campaña</flux:label>
                    <flux:select wire:model="campaign_id">
                        @foreach($campaigns as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="campaign_id" />
                </flux:field>
                <flux:field>
                    <flux:label required>Parcela</flux:label>
                    <flux:select wire:model="plot_id">
                        <option value="">Seleccionar...</option>
                        @foreach($plots as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="plot_id" />
                </flux:field>

                <flux:field class="md:col-span-2">
                    <flux:label>Plantación específica (opcional)</flux:label>
                    <flux:select wire:model="plot_planting_id">
                        <option value="">Global parcela</option>
                        @foreach($plantings as $p)
                            <option value="{{ $p->id }}">{{ $p->plot->name }} — {{ $p->grape->name ?? '' }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="plot_planting_id" />
                </flux:field>
            </div>

            <div class="space-y-4 bg-zinc-50 rounded-lg p-4">
                <h3 class="text-sm font-semibold text-zinc-700">Captación de agua</h3>
                <flux:checkbox wire:model.live="water_intake_nearby" label="¿Hay captación de agua cercana (río, pozo, embalse)?" />
                @if($water_intake_nearby)
                    <flux:field>
                        <flux:label>Distancia a la captación (m)</flux:label>
                        <flux:input wire:model="water_intake_distance_m" type="number" step="0.01" min="0" placeholder="0.00" />
                        <flux:error name="water_intake_distance_m" />
                    </flux:field>
                @endif
            </div>

            <div class="space-y-4 bg-zinc-50 rounded-lg p-4">
                <h3 class="text-sm font-semibold text-zinc-700">Zonas protegidas</h3>
                <div class="flex flex-col gap-2">
                    <flux:checkbox wire:model.live="protected_zone_total" label="Zona de exclusión total (no se puede tratar)" />
                    <flux:checkbox wire:model.live="protected_zone_partial" label="Zona de exclusión parcial (restricciones de uso)" />
                </div>
                @if($protected_zone_total || $protected_zone_partial)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <flux:field>
                            <flux:label>Tipo de zona</flux:label>
                            <flux:input wire:model="protection_zone_type" type="text" placeholder="N2000, LIC, ZEPA, ZEC..." />
                            <flux:error name="protection_zone_type" />
                        </flux:field>
                        <flux:field>
                            <flux:label>Zona tampón (m)</flux:label>
                            <flux:input wire:model="buffer_zone_m" type="number" step="0.01" min="0" placeholder="0.00" />
                            <flux:error name="buffer_zone_m" />
                        </flux:field>
                    </div>
                @endif
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>Pendiente media (%)</flux:label>
                    <flux:input wire:model="slope_pct" type="number" step="0.01" min="0" max="100" placeholder="0.00" />
                    <flux:error name="slope_pct" />
                </flux:field>
                <flux:field class="flex items-center pt-6">
                    <flux:checkbox wire:model="erosion_risk" label="⚠️ Riesgo significativo de erosión" />
                    <flux:error name="erosion_risk" />
                </flux:field>
            </div>

            <flux:field>
                <flux:label>Notas</flux:label>
                <flux:textarea wire:model="notes" rows="2" />
                <flux:error name="notes" />
            </flux:field>

            <div class="flex justify-end gap-3 pt-4 border-t border-zinc-200">
                <flux:button variant="ghost" wire:click="$set('showModal', false)">Cancelar</flux:button>
                <flux:button variant="primary" wire:click="save">{{ $editingId ? 'Actualizar' : 'Registrar' }}</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
