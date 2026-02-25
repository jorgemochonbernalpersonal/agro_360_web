<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Autorizaciones Comerciales"
        subtitle="DO, certificaciones ecológicas, derechos de plantación y replantación"
        icon="shield-check"
    >
        <x-slot:actions>
            <flux:button variant="primary" icon="plus" wire:click="openCreate">Nueva Autorización</flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    @if($expiring > 0)
        <flux:callout variant="warning" icon="exclamation-triangle">
            Tienes <strong>{{ $expiring }}</strong> {{ $expiring === 1 ? 'autorización que vence' : 'autorizaciones que vencen' }} en los próximos 60 días. Revisa y renuévalas a tiempo.
        </flux:callout>
    @endif

    <x-agro.filter-bar>
        <x-agro.filter-select wire:model.live="filterType" label="Tipo">
            <option value="">Todos</option>
            @foreach($authTypes as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </x-agro.filter-select>
    </x-agro.filter-bar>

    <x-agro.card>
        @if($entries->isEmpty())
            <x-agro.empty-state
                icon="shield-check"
                title="Sin autorizaciones registradas"
                description="Registra tus inscripciones en Denominaciones de Origen, certificaciones ecológicas y derechos de plantación."
            />
        @else
            <x-agro.data-table :headers="['Tipo', 'Código / Expediente', 'Organismo', 'Emisión', 'Caducidad', 'Estado', 'Acciones']">
                @foreach($entries as $entry)
                    <x-agro.table-row>
                        <x-agro.table-cell>
                            <x-agro.status-badge :status="$entry->authorization_type" :label="$entry->authorization_type_label" />
                            @if($entry->description)
                                <span class="text-zinc-400 text-xs block">{{ $entry->description }}</span>
                            @endif
                        </x-agro.table-cell>
                        <x-agro.table-cell class="font-mono text-sm">{{ $entry->authorization_code ?? '-' }}</x-agro.table-cell>
                        <x-agro.table-cell>{{ $entry->issuing_body ?? '-' }}</x-agro.table-cell>
                        <x-agro.table-cell>{{ $entry->issue_date->format('d/m/Y') }}</x-agro.table-cell>
                        <x-agro.table-cell>
                            @if($entry->expiry_date)
                                <span class="{{ $entry->isExpired() ? 'text-red-600 font-semibold' : ($entry->isExpiringSoon() ? 'text-amber-600 font-medium' : 'text-zinc-600') }}">
                                    {{ $entry->expiry_date->format('d/m/Y') }}
                                </span>
                            @else
                                <span class="text-zinc-400">Indefinida</span>
                            @endif
                        </x-agro.table-cell>
                        <x-agro.table-cell>
                            @if($entry->isExpired())
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">❌ Vencida</span>
                            @elseif($entry->isExpiringSoon())
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-700">⚠️ Por vencer</span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">✅ Vigente</span>
                            @endif
                        </x-agro.table-cell>
                        <x-agro.table-cell align="right">
                            <div class="flex items-center justify-end gap-2">
                                <flux:button size="sm" variant="ghost" icon="pencil" wire:click="openEdit({{ $entry->id }})">Editar</flux:button>
                                <flux:button size="sm" variant="ghost" icon="archive-box" wire:click="deactivate({{ $entry->id }})" wire:confirm="¿Archivar esta autorización?">Archivar</flux:button>
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
            <h2 class="text-lg font-semibold text-zinc-900">{{ $editingId ? 'Editar Autorización' : 'Nueva Autorización' }}</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:field>
                    <flux:label required>Tipo de autorización</flux:label>
                    <flux:select wire:model="authorization_type">
                        @foreach($authTypes as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="authorization_type" />
                </flux:field>
                <flux:field>
                    <flux:label>Explotación asociada</flux:label>
                    <flux:select wire:model="exploitation_id">
                        <option value="">Sin vincular</option>
                        @foreach($exploitations as $exp)
                            <option value="{{ $exp->id }}">{{ $exp->exploitation_name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="exploitation_id" />
                </flux:field>

                <flux:field>
                    <flux:label>Código / Nº Expediente</flux:label>
                    <flux:input wire:model="authorization_code" type="text" placeholder="Código o nº de expediente" />
                    <flux:error name="authorization_code" />
                </flux:field>
                <flux:field>
                    <flux:label>Organismo emisor</flux:label>
                    <flux:input wire:model="issuing_body" type="text" placeholder="MAPA, Consejo Regulador, CCAA..." />
                    <flux:error name="issuing_body" />
                </flux:field>

                <flux:field class="md:col-span-2">
                    <flux:label>Descripción</flux:label>
                    <flux:input wire:model="description" type="text" placeholder="Descripción adicional" />
                    <flux:error name="description" />
                </flux:field>

                <flux:field>
                    <flux:label required>Fecha de emisión</flux:label>
                    <flux:input wire:model="issue_date" type="date" />
                    <flux:error name="issue_date" />
                </flux:field>
                <flux:field>
                    <flux:label>Fecha de caducidad</flux:label>
                    <flux:input wire:model="expiry_date" type="date" />
                    <flux:description>Deja vacío si es indefinida</flux:description>
                    <flux:error name="expiry_date" />
                </flux:field>

                <flux:field class="md:col-span-2">
                    <flux:label>Documento (ruta o referencia)</flux:label>
                    <flux:input wire:model="document_file" type="text" placeholder="Ruta del documento o URL de referencia" />
                    <flux:error name="document_file" />
                </flux:field>

                <flux:field class="md:col-span-2">
                    <flux:label>Notas</flux:label>
                    <flux:textarea wire:model="notes" rows="2" />
                    <flux:error name="notes" />
                </flux:field>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-zinc-200">
                <flux:button variant="ghost" wire:click="$set('showModal', false)">Cancelar</flux:button>
                <flux:button variant="primary" wire:click="save">{{ $editingId ? 'Actualizar' : 'Registrar' }}</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
