<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Análisis de Residuos Fitosanitarios"
        subtitle="Control de LMR (Límites Máximos de Residuos)"
        icon="beaker"
    >
        <x-slot:actions>
            <flux:button variant="primary" icon="plus" wire:click="openCreate">Nuevo Análisis</flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    <x-agro.filter-bar>
        <x-agro.filter-select wire:model.live="filterCampaign" label="Campaña">
            <option value="">Todas las campañas</option>
            @foreach($campaigns as $c)
                <option value="{{ $c->id }}">{{ $c->name }}</option>
            @endforeach
        </x-agro.filter-select>
        <x-agro.filter-select wire:model.live="filterCompliant" label="Resultado">
            <option value="">Todos</option>
            <option value="1">✅ Conforme</option>
            <option value="0">❌ No conforme</option>
        </x-agro.filter-select>
    </x-agro.filter-bar>

    <x-agro.card>
        @if($entries->isEmpty())
            <x-agro.empty-state
                icon="beaker"
                title="Sin análisis registrados"
                description="Registra los análisis de residuos de laboratorio para verificar el cumplimiento de los LMR."
            />
        @else
            <x-agro.data-table :headers="['Fecha', 'Laboratorio', 'Plantación', 'Muestra', 'Resultado', 'Acciones']">
                @foreach($entries as $entry)
                    <x-agro.table-row>
                        <x-agro.table-cell>{{ $entry->analysis_date->format('d/m/Y') }}</x-agro.table-cell>
                        <x-agro.table-cell>
                            {{ $entry->laboratory_name }}
                            @if($entry->laboratory_accreditation)
                                <span class="text-zinc-400 text-xs block">ENAC: {{ $entry->laboratory_accreditation }}</span>
                            @endif
                        </x-agro.table-cell>
                        <x-agro.table-cell>{{ $entry->plotPlanting?->plot->name ?? 'Global campaña' }}</x-agro.table-cell>
                        <x-agro.table-cell>{{ $entry->sample_type ?? '-' }}</x-agro.table-cell>
                        <x-agro.table-cell>
                            @if($entry->overall_compliant)
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                    <flux:icon icon="check-circle" class="w-3 h-3" /> Conforme
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <flux:icon icon="x-circle" class="w-3 h-3" /> No conforme
                                </span>
                            @endif
                        </x-agro.table-cell>
                        <x-agro.table-cell align="right">
                            <div class="flex items-center justify-end gap-2">
                                <flux:button size="sm" variant="ghost" icon="pencil" wire:click="openEdit({{ $entry->id }})">Editar</flux:button>
                                <flux:button size="sm" variant="ghost" icon="archive-box" wire:click="deactivate({{ $entry->id }})" wire:confirm="¿Archivar este análisis?">Archivar</flux:button>
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
            <h2 class="text-lg font-semibold text-zinc-900">
                {{ $editingId ? 'Editar Análisis' : 'Nuevo Análisis de Residuos' }}
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:field>
                    <flux:label required>Campaña</flux:label>
                    <flux:select wire:model="campaign_id">
                        <option value="">Seleccionar...</option>
                        @foreach($campaigns as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="campaign_id" />
                </flux:field>
                <flux:field>
                    <flux:label>Plantación (opcional)</flux:label>
                    <flux:select wire:model="plot_planting_id">
                        <option value="">Global campaña</option>
                        @foreach($plantings as $p)
                            <option value="{{ $p->id }}">{{ $p->plot->name }} — {{ $p->grape->name ?? '' }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="plot_planting_id" />
                </flux:field>

                <flux:field>
                    <flux:label required>Fecha del análisis</flux:label>
                    <flux:input wire:model="analysis_date" type="date" />
                    <flux:error name="analysis_date" />
                </flux:field>
                <flux:field>
                    <flux:label>Fecha de toma de muestra</flux:label>
                    <flux:input wire:model="sample_date" type="date" />
                    <flux:error name="sample_date" />
                </flux:field>

                <flux:field>
                    <flux:label required>Laboratorio</flux:label>
                    <flux:input wire:model="laboratory_name" type="text" placeholder="Nombre del laboratorio" />
                    <flux:error name="laboratory_name" />
                </flux:field>
                <flux:field>
                    <flux:label>Acreditación ENAC</flux:label>
                    <flux:input wire:model="laboratory_accreditation" type="text" placeholder="Nº acreditación" />
                    <flux:error name="laboratory_accreditation" />
                </flux:field>

                <flux:field>
                    <flux:label>Tipo de muestra</flux:label>
                    <flux:input wire:model="sample_type" type="text" placeholder="Ej: uva, mosto, vino" />
                    <flux:error name="sample_type" />
                </flux:field>
                <flux:field>
                    <flux:label>Ruta certificado PDF</flux:label>
                    <flux:input wire:model="certificate_file" type="text" placeholder="Ruta o URL del certificado" />
                    <flux:error name="certificate_file" />
                </flux:field>

                <flux:field class="md:col-span-2">
                    <flux:checkbox wire:model="overall_compliant" label="✅ Todos los resultados cumplen los LMR" />
                    <flux:error name="overall_compliant" />
                </flux:field>

                <flux:field class="md:col-span-2">
                    <flux:label>Notas</flux:label>
                    <flux:textarea wire:model="notes" rows="2" />
                    <flux:error name="notes" />
                </flux:field>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-zinc-200">
                <flux:button variant="ghost" wire:click="$set('showModal', false)">Cancelar</flux:button>
                <flux:button variant="primary" wire:click="save">
                    {{ $editingId ? 'Actualizar' : 'Registrar Análisis' }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
