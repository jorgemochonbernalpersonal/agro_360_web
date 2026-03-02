<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Exportaciones CUE / SIEX"
        subtitle="Historial de envíos del Cuaderno de Explotación Único al MAPA"
        icon="arrow-up-tray"
    >
        <x-slot:actions>
            <flux:button href="{{ route('viticulturist.cue-exports.create') }}" variant="primary" icon="plus">
                Nueva Exportación
            </flux:button>
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
            <x-slot:action>
                <flux:button variant="primary" :href="route('viticulturist.exploitations.index')" wire:navigate>
                    Ir a Explotaciones
                </flux:button>
            </x-slot:action>
        </x-agro.empty-state>
    @elseif($exports->isEmpty())
        <x-agro.empty-state
            icon="arrow-up-tray"
            title="Sin exportaciones registradas"
            description="Crea tu primera exportación CUE para registrar el historial de envíos al MAPA."
        >
            <x-slot:action>
                <flux:button href="{{ route('viticulturist.cue-exports.create') }}" variant="primary" icon="plus">
                    Nueva Exportación
                </flux:button>
            </x-slot:action>
        </x-agro.empty-state>
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
                                            <a href="{{ route('viticulturist.cue-exports.edit', $export) }}"
                                               class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors"
                                               title="Editar">
                                                <flux:icon icon="pencil-square" class="size-4" />
                                            </a>
                                            <button
                                                wire:click="markAsGenerated({{ $export->id }})"
                                                wire:confirm="¿Marcar esta exportación como generada?"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-blue-600 hover:bg-blue-50 transition-colors"
                                                title="Marcar como generado">
                                                <flux:icon icon="check-circle" class="size-4" />
                                            </button>
                                            <button
                                                wire:click="delete({{ $export->id }})"
                                                wire:confirm="¿Eliminar esta exportación? Esta acción no se puede deshacer."
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-red-600 hover:bg-red-50 transition-colors"
                                                title="Eliminar">
                                                <flux:icon icon="trash" class="size-4" />
                                            </button>
                                        @elseif($export->status === 'generated')
                                            <button
                                                wire:click="markAsSent({{ $export->id }})"
                                                wire:confirm="¿Marcar esta exportación como enviada al MAPA?"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-agro-600 hover:bg-agro-50 transition-colors"
                                                title="Marcar como enviado">
                                                <flux:icon icon="paper-airplane" class="size-4" />
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-agro.card>

        {{-- Paginación --}}
        @if($exports->hasPages())
            <div class="px-4 py-3 border-t border-zinc-100">
                {{ $exports->links() }}
            </div>
        @endif

        {{-- Resumen por estado --}}
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            @foreach(\App\Models\CueExport::STATUSES as $key => $label)
                <x-agro.stat-card
                    :label="$label"
                    :value="$statusCounts[$key] ?? 0"
                    :color="\App\Models\CueExport::STATUS_COLORS[$key]"
                />
            @endforeach
        </div>
    @endif

</div>
