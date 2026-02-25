<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Exportaciones CUE / SIEX"
        subtitle="Historial de envíos del Cuaderno de Explotación Único al MAPA"
        icon="arrow-up-tray"
    >
        <x-slot:actions>
            <flux:button variant="primary" icon="plus" wire:click="openCreate">Nueva Exportación</flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    <flux:callout variant="info" icon="information-circle">
        El <strong>CUE (Cuaderno de Explotación Único)</strong> es de envío obligatorio al MAPA desde 2022 (RD 1337/2021).
        Crea aquí los registros de envío y haz seguimiento de su estado. El envío electrónico directo al SIEX estará disponible próximamente.
    </flux:callout>

    @if($exploitations->isEmpty())
        <x-agro.empty-state
            icon="building-office"
            title="Sin explotaciones registradas"
            description="Antes de crear una exportación CUE, debes registrar al menos una explotación agraria en el módulo Explotación SIEX/REA."
        >
            <x-slot:actions>
                <flux:button variant="primary" :href="route('viticulturist.exploitations.index')" wire:navigate>
                    Ir a Explotaciones
                </flux:button>
            </x-slot:actions>
        </x-agro.empty-state>
    @elseif($exports->isEmpty())
        <x-agro.empty-state
            icon="arrow-up-tray"
            title="Sin exportaciones registradas"
            description="Crea tu primera exportación CUE para registrar el historial de envíos al MAPA."
        />
    @else
        {{-- Tabla de exportaciones --}}
        <x-agro.card>
            <x-slot:header>
                <div class="flex items-center gap-2">
                    <flux:icon icon="arrow-up-tray" class="w-5 h-5 text-agro-600" />
                    <span class="font-semibold text-zinc-900">Historial de Exportaciones</span>
                </div>
            </x-slot:header>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-zinc-100">
                            <th class="text-left py-3 px-4 text-zinc-500 font-medium">Explotación</th>
                            <th class="text-left py-3 px-4 text-zinc-500 font-medium">Campaña</th>
                            <th class="text-left py-3 px-4 text-zinc-500 font-medium">Período</th>
                            <th class="text-left py-3 px-4 text-zinc-500 font-medium">Fechas</th>
                            <th class="text-left py-3 px-4 text-zinc-500 font-medium">Estado</th>
                            <th class="text-left py-3 px-4 text-zinc-500 font-medium">Enviado</th>
                            <th class="text-right py-3 px-4"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($exports as $export)
                            <tr class="border-b border-zinc-50 hover:bg-zinc-50 transition-colors">
                                <td class="py-3 px-4 font-medium text-zinc-900">
                                    {{ $export->exploitation->exploitation_name ?? '-' }}
                                </td>
                                <td class="py-3 px-4">{{ $export->campaign_year }}</td>
                                <td class="py-3 px-4">
                                    {{ $export->period_type === 'annual' ? 'Anual' : 'Trimestral' }}
                                </td>
                                <td class="py-3 px-4 text-zinc-500">
                                    {{ $export->from_date->format('d/m/Y') }} — {{ $export->to_date->format('d/m/Y') }}
                                </td>
                                <td class="py-3 px-4">
                                    <x-agro.status-badge
                                        :label="$export->status_label"
                                        :color="$export->status_color"
                                    />
                                </td>
                                <td class="py-3 px-4 text-zinc-500 text-xs">
                                    @if($export->sent_at)
                                        {{ $export->sent_at->format('d/m/Y H:i') }}
                                    @elseif($export->generated_at)
                                        <span class="text-blue-600">Generado {{ $export->generated_at->format('d/m/Y') }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        @if($export->status === 'draft')
                                            <flux:button size="xs" variant="ghost" icon="pencil"
                                                wire:click="openEdit({{ $export->id }})">
                                                Editar
                                            </flux:button>
                                            <flux:button size="xs" variant="ghost" icon="check-circle"
                                                wire:click="markAsGenerated({{ $export->id }})"
                                                wire:confirm="¿Marcar esta exportación como generada?">
                                                Generado
                                            </flux:button>
                                            <flux:button size="xs" variant="ghost" icon="trash"
                                                wire:click="delete({{ $export->id }})"
                                                wire:confirm="¿Eliminar esta exportación? Esta acción no se puede deshacer.">
                                                Eliminar
                                            </flux:button>
                                        @elseif($export->status === 'generated')
                                            <flux:button size="xs" variant="ghost" icon="paper-airplane"
                                                wire:click="markAsSent({{ $export->id }})"
                                                wire:confirm="¿Marcar esta exportación como enviada al MAPA?">
                                                Marcar Enviado
                                            </flux:button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-agro.card>

        {{-- Resumen por estado --}}
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            @foreach(\App\Models\CueExport::STATUSES as $key => $label)
                @php $count = $exports->where('status', $key)->count(); @endphp
                <x-agro.stat-card
                    :label="$label"
                    :value="$count"
                    :color="\App\Models\CueExport::STATUS_COLORS[$key]"
                />
            @endforeach
        </div>
    @endif

    {{-- Modal nueva/editar exportación --}}
    <flux:modal wire:model="showModal" class="max-w-lg">
        <div class="p-6 space-y-5">
            <h2 class="text-lg font-semibold text-zinc-900">
                {{ $editingId ? 'Editar Exportación CUE' : 'Nueva Exportación CUE' }}
            </h2>

            <flux:field>
                <flux:label required>Explotación</flux:label>
                <flux:select wire:model="exploitation_id">
                    <option value="">Seleccionar explotación...</option>
                    @foreach($exploitations as $exp)
                        <option value="{{ $exp->id }}">
                            {{ $exp->exploitation_name }}
                            @if($exp->rea_code) (REA: {{ $exp->rea_code }}) @endif
                        </option>
                    @endforeach
                </flux:select>
                <flux:error name="exploitation_id" />
            </flux:field>

            <div class="grid grid-cols-2 gap-4">
                <flux:field>
                    <flux:label required>Año de campaña</flux:label>
                    <flux:input wire:model.live="campaign_year" type="number" min="2000" :max="date('Y') + 1" placeholder="{{ date('Y') }}" />
                    <flux:error name="campaign_year" />
                </flux:field>
                <flux:field>
                    <flux:label required>Tipo de período</flux:label>
                    <flux:select wire:model.live="period_type">
                        <option value="annual">Anual</option>
                        <option value="quarterly">Trimestral</option>
                    </flux:select>
                    <flux:error name="period_type" />
                </flux:field>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <flux:field>
                    <flux:label required>Fecha de inicio</flux:label>
                    <flux:input wire:model="from_date" type="date" />
                    <flux:error name="from_date" />
                </flux:field>
                <flux:field>
                    <flux:label required>Fecha de fin</flux:label>
                    <flux:input wire:model="to_date" type="date" />
                    <flux:error name="to_date" />
                </flux:field>
            </div>

            <flux:callout variant="warning" icon="exclamation-triangle" class="text-sm">
                La integración directa con el sistema SIEX del MAPA está en desarrollo.
                Por ahora puedes registrar el historial de envíos manualmente.
            </flux:callout>

            <div class="flex justify-end gap-3 pt-4 border-t border-zinc-200">
                <flux:button variant="ghost" wire:click="$set('showModal', false)">Cancelar</flux:button>
                <flux:button variant="primary" wire:click="save">
                    {{ $editingId ? 'Actualizar' : 'Crear Exportación' }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
